<?php 
    /*
        Programa:  mdfe-encerramento.php
        Descricão: Programa responsavel por emitir eventos de encerramento de MDFe a partir de um arquivo criado
        Autor:     Fernando H. Crozetta (30/05/2017)
        Modo de uso: 
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-cancelamento.php <caminho_arquivo.txt>

        Template do arquivo:
        cnpj|uf autorizador|ambiente|chave mdfe|num protocolo|tipo evento|data/hora encerramento|uf encer.|municipio encer

    */
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../funcoes/mdfeCancelamento2array.php"); // Usado para converter o txt em xml
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); // Usado para retornar o código uf, baseado na sigla

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."mdfe/";
    // Dados que são passados pelo programa chamador (cobol)
    $arquivo_txt = $argv[1];

    // Cria o nome raiz do arquivo a ser manipulado
    $nome_raiz = end(explode("/",$arquivo_txt));
    $nome_raiz = str_replace(".txt", '', $nome_raiz);
    $arquivo_xml = $temp.$nome_raiz.".xml";
    $arquivo_saida = str_replace("CMDFE","CMDFER",$nome_raiz).".txt";

    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    /* Assinatura do XML */
    function assinaXml($arquivo,$cnpj)
    {
        $assinatura = new CAssinaturaDigital($arquivo,$cnpj);
        // Grava no arquivo se estiver tudo ok. Retorna o erro se der errado.
        if ($assinatura->assinarXml('infEvento')) {
            $assinatura->salvar();
        }else{
            $mensagem = "11|".$assinatura->getErro()."\nErro ao assinar o xml";
            flog("Erro ao assinar o xml");
            fdebug();
            exit(11);
        }
    }

    /* Validação do XML */
    function validaXml($arquivo_xml,$cnpj,$config,$ws)
    {
        $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
        $xsd = $config['servicos']."/mdfe/schemas/".$dados['mdfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        if(!$validacao->validar()){
            $motivo.= $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }


    // main
    cria_diretorios($temp);
    $array_dados = mdfeCancelamento2array($arquivo_txt,$arquivo_xml);


    // Carrega o xml e busca dados
    $cnpj = $array_dados["cnpj"];
    $ambiente_entrada = $array_dados["ambiente"];
    $uf_ws = ($array_dados["uf_ws"] == "") ? "43" : $array_dados["uf_ws"];
    $converteUf = new CodigosUf();
    $estado = $converteUf->deNUm($uf_ws);
    $ambiente = ($ambiente_entrada == '1') ? "producao":"homologacao" ;

    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $dados_ws = new BuscaWebService($estado,'mdfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("cancelamento",$dados['mdfe']['versao']);

    $conteudo_xml = $array_webservice->tag_corpo;
    $array_substituicao= array(
        'ALTERAR_ID'=>$array_dados["id"],
        'ALTERAR_TIPO_AMBIENTE'=>$ambiente_entrada,
        'ALTERAR_CHAVE_MDFE'=>$array_dados['chave'],
        'ALTERAR_DH_EVENTO'=>$array_dados['data_hora'],
        'ALTERAR_TIPO_EVENTO'=>$array_dados['tipo_evento'],
        'ALTERAR_NRO_PROTOCOLO'=>$array_dados['protocolo'],
        'ALTERAR_JUSTIFICATIVA'=>$array_dados['justificativa'],
        'ALTERAR_UF_AUTORIZADOR'=>$array_dados['uf_contribuinte'],
        'ALTERAR_CNPJ'=>$cnpj,
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $conteudo_xml = freplace($conteudo_xml,$array_substituicao);
    file_put_contents($arquivo_xml,$conteudo_xml);
    
    // Assinar e validar xml
    assinaXml($arquivo_xml,$cnpj);
    validaXml($arquivo_xml,$cnpj,$config,$array_webservice);
    $corpo = file_get_contents($arquivo_xml);
    $corpo = $array_webservice->envelope_corpo_superior.$corpo.$array_webservice->envelope_corpo_inferior;


    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    $array_ajuste_uf = array(
        'ALTERAR_UF_AUTORIZADOR'=>$array_dados['uf_contribuinte'],
    );
    $template_soap = freplace($template_soap,$array_ajuste_uf);
    // Carregar os dados da mdfe para o xml
    
    file_put_contents($arquivo_xml, $template_soap);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();

    // print_r($xml_retorno);
    // print_r($xml_retorno);exit();

    // Formata o retorno para o retorno cobol
    $retorno = $cnpj."|";
    $retorno .= $array_dados['ambiente'];
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= $array_dados['chave'];
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php dhRegEvento '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php nProt '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= $uf_ws;
    $retorno .= "|";
    //  Conversado com o roberto e ele nos indicou a deixar o xml de retorno no fim do arquivo [18/07/2017]
    $retorno .= $xml_retorno;


    // grava o retorno do envio de mdfe
    // print_r($dados['mdfe']['dir_saida_cobol'].$arquivo_saida);
    file_put_contents($dados['mdfe']['dir_saida_cobol'].$arquivo_saida,$retorno);

?>
