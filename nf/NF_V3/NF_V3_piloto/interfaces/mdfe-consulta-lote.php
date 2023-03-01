<?php
    /*
        Programa:  mdfe-consulta-lote.php
        Descricão: Programa responsavel por realizar a consulta ddo lote mdfe enviado
        Autor:     Fernando H. Crozetta (19/06/2017)
        Modo de uso:
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-consulta-lote.php <cnpj> <recibo> <ambiente> <uf>
    */
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); //Converte códigos UF para numero e vice versa
    require_once("../ferramentas/formatXML.php"      ) ; //

    // Dados que são passados pelo programa chamador (cobol)
    /*
    modelo de recibo:
    uf  tipo autorizador  sequencia
    41        9              000003194036
    */
    // php -q /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-consulta-lote.php 03662361000191 ~/saida.txt 419000006219836  2 

    //*
    $cnpj = $argv[1];
    $arquivo_saida = $argv[2];
    $recibo = $argv[3]; //recibo
    $ambiente_entrada = (isset($argv[4])?$argv[4]:'2');
    $uf = (substr($recibo, 0,2));
    $uf_ws = (isset($argv[5])?$argv[5]:'RS');
    
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."mdfe/";

    $arquivo_xml=$temp.$recibo.'-consulta-lote.xml';

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
        $xsd = $config['servicos']."/mdfe/schemas/".$dados['mdfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        $motivo='';
        if(!$validacao->validar()){
            $motivo.= $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }

    // main
    cria_diretorios($temp);

    // $dados_ws = new BuscaWebService($uf,'mdfe',$ambiente);
    $dados_ws = new BuscaWebService($uf_ws,'mdfe',$ambiente); //Só existe RS
    $array_webservice = $dados_ws->buscarServico("consulta_lote",$dados['mdfe']['versao']);

    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);

    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        'ALTERAR_RECIBO'=>$recibo,
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    file_put_contents($arquivo_xml, $template_soap);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();

    // echo formatXML($xml_retorno); exit();

    $codigosUf =  new CodigosUf();
    $numUf = $codigosUf->paraNum($uf_ws);

    // Formata o retorno para um txt: código retorno|motivo
    $retorno = $cnpj;
    $retorno .= "|";
    $retorno .= $numUf;
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php chMDFe '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php tpAmb '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php dhRecbto '".$xml_retorno."'");
    $retorno .= "|";
    if (strpos($xml_retorno,"nProt")!== false){
        $retorno .= exec("php ../ferramentas/f.texto.tag.php nProt '".$xml_retorno."'");
    }
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= $xml_retorno;

    //print_r($dados['mdfe']['dir_retorno_consulta_lote'].$arquivo_saida);
    // grava o retorno do envio de mdfe
    file_put_contents($dados['mdfe']['dir_retorno_consulta_lote'].$arquivo_saida,$retorno);

?>
