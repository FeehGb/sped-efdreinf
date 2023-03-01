<?php 
/* /user/mdfe/82574062000252/CaixaEntrada/Processar/MDFE-000000033-000-20170727-144941.TXT*/
    /*
        Programa:  mdfe-envio.php
        Descricão: Programa responsavel por emitir manifestações de destinatário eletrônica a partir de um arquivo criado na Caixa de Entrada
        Autor:     Fernando H. Crozetta (30/05/2017), adaptado de
                    J. Eduardo Lino (14/06/2016), adaptado de 
                    Guilherme Pinto (04/04/2016)
        Modo de uso: 
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-envio.php <caminho_arquivo.txt>

    */
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../funcoes/txt2xml.php"); // para converter mdfe txt para xml
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); // Usado para retornar o código uf, baseado na sigla
    require_once("../ferramentas/formatXML.php"      ) ; //
    
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."mdfe/";
    // Dados que são passados pelo programa chamador (cobol)
    $arquivo_txt = $argv[1];
    // Se não for passado o parametro, buscar estado RS
    $estado = (isset($argv[2]) ? $argv[2] : 'RS'  );
    // se esta em modo de testes
    $testes = (isset($argv[3]) ? true     : false );

    // Cria o nome raiz do arquivo a ser manipulado
    $nome_raiz = end(explode("/",$arquivo_txt));
    // $nome_raiz = str_replace(".txt", '', $nome_raiz);

    $nome_arquivo_saida = str_replace("MDFE", "LMDFER", $nome_raiz);

    $arquivo_xml = $temp.$nome_raiz.".xml";
    $arquivo_xml = $temp.$nome_raiz.".xml";

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
        if ($assinatura->assinarXml('infMDFe')) {
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
        // $xsd = $config['servicos']."/mdfe/schemas/".$dados['mdfe']['pacote']."/".$ws->schema;
        $xsd = $config['servicos']."/mdfe/schemas/PL_MDFe_300_NT022018/{$ws->schema}";
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        if(!$validacao->validar()){
            $motivo = $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }
    // main
    
    // $arquivo_xml = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/testes/teste.xml";
    cria_diretorios($temp);
    
    
    txt2xml($arquivo_txt,$arquivo_xml, 'MANIFESTO');
    
    
    
    // Carrega o xml e busca dados
    $conteudo_xml = simplexml_load_file($arquivo_xml);
    $cnpj = $conteudo_xml->infMDFe->emit->CNPJ;
    // $cnpj = $conteudo_xml->infMDFe->emit->CNPJCPF;
    $uf = $conteudo_xml->infMDFe->ide->cUF;
    // $chave = substr($conteudo_xml[0]['Id'], 4); //usado para consulta posterior
    
    
    // print_r(substr($conteudo_xml->infMDFe->attributes()->Id, 4)); //usado para consulta posterior
    
    $chave = substr($conteudo_xml->infMDFe->attributes()->Id, 4); //usado para consulta posterior

    $ambiente = ($conteudo_xml->infMDFe->ide->tpAmb == 1?'producao':'homologacao');

    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $dados_ws = new BuscaWebService($estado,'mdfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("recepcao",$dados['mdfe']['versao']);

    // Assinar e validar xml
    assinaXml($arquivo_xml,$cnpj);
    // validaXml($arquivo_xml,$cnpj,$config,$array_webservice);
    
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);

    // [19/07/22017] - f.crozetta: Este arquivo é usado após a consulta, realizando o apendo do protocolo
    $tmp_para_consulta = $array_webservice->tag_corpo;
    $array_para_consulta = array(
        'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_DADOS_URL'=>$array_webservice->url,
        'ALTERAR_DADOS_VERSAO_DADOS' => $array_webservice->versao,
        'ALTERAR_DADOS_VERSAO'=> $array_webservice->versao,
        'ALTERAR_DADOS_XML'=> str_replace('<?xml version="1.0" encoding="UTF-8"?>', '',file_get_contents($arquivo_xml)),
        'ALTERAR_DADOS_ID_LOTE'=>substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15),
        "\n"=>'',
        "\r"=>'',
        "\t"=>''
    );
    $tmp_para_consulta = freplace($tmp_para_consulta,$array_para_consulta);

    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_DADOS_URL'=>$array_webservice->url,
        'ALTERAR_DADOS_VERSAO_DADOS' => $array_webservice->versao,
        'ALTERAR_DADOS_VERSAO'=> $array_webservice->versao,
        'ALTERAR_DADOS_XML'=> str_replace('<?xml version="1.0" encoding="UTF-8"?>', '',file_get_contents($arquivo_xml)),
        'ALTERAR_DADOS_ID_LOTE'=>substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15),
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    file_put_contents($arquivo_xml, $template_soap);
    
    // print_r(file_get_contents($arquivo_xml)); exit();
    
    if ($testes){
        exit();
    }
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();
    
    // echo formatXML($xml_retorno, "retorno");
    // print_r($template_soap);
    // print_r("\n\n--\n\n");
    // print_r($xml_retorno);


    // print_r($xml_retorno);


    $codigosUf = new CodigosUf();
    $numUf = $codigosUf->paraNum($estado);
    // Formata o retorno para o retorno cobol
    $retorno = $cnpj."|";
    // $retorno .= " ";
    $retorno .= $conteudo_xml->infMDFe->ide->tpAmb;
    $retorno .= "|";
    $retorno .= $conteudo_xml->infMDFe->ide->tpEmis;
    $retorno .= "|";
    $retorno .= $numUf;
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php nRec '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= $chave;
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php dhRecbto '".$xml_retorno."'");
    $retorno .= "|";
    
    //  Conversado com o roberto e ele nos indicou a deixar o xml de retorno no fim do arquivo [18/07/2017]
    $retorno .= $xml_retorno;
    
    // grava o retorno do envio de mdfe
    file_put_contents($dados['mdfe']['dir_retorno'].$chave."-pendMDFe.xml",$tmp_para_consulta);
    file_put_contents($dados['mdfe']['dir_saida_cobol'].$nome_arquivo_saida,$retorno);
    
 ?>
