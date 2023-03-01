<?php 
    /**
     *   Programa:  nfe-status.php
     *   Descricão: Programa responsavel por verificar o status do serviço
     *   Autor:     Fernando H. Crozetta (09/01/2018)
     *   Modo de uso: 
     *   	php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-status.php <cnpj> <arquivo_saida> <ambiente> <uf>
    */ 
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
            argv[1] = cnpj             = argv[1]           ; 
            argv[2] = arquivo_saida    = argv[2]           ; 
            argv[3]*= ambiente_entrada = argv[3] |     '2' ; 
            argv[4]*= uf_ws            = argv[4] |    'PR' ; 
            argv[5]*= autorizadora     = argv[5] | argv[4] ; 
            
            SAIDA:
            // \$cStat       // 
            // \$xMotivo     // 
            // \$dhRecbto    // 
            // \$tMed        // 
            // \$xObs        // 
            // \$xml_retorno // 
            
        \n"; exit() ;
    }
    
    
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"               ) ; // para gravar log
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"          ) ; // Converte códigos UF para numero e vice versa
    require_once("../funcoes/carrega_config.ini.php" ) ; // Carrega as configuracoes
    require_once("../ferramentas/formatXML.php"      ) ; // .
    require_once("../funcoes/httpStatus.php"         ) ; // .
    
    
    // Dados que são passados pelo programa chamador (cobol)    
    $cnpj             = $argv[1]                          ; 
    $arquivo_saida    = $argv[2]                          ; 
    $ambiente_entrada = (isset($argv[3])?$argv[3]:'2')    ; 
    $uf_ws            = (isset($argv[4])?$argv[4]:'PR')   ; 
    $autorizadora     = (isset($argv[5])?$argv[5]:$uf_ws) ; 
    
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";
    
    // Carrega as configurações de clientes e sistema
    // $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."nfe/";
    $data = date('ymd-His');
    $arquivo_xml=$temp.$data.'-status.xml';
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }
    
    // main
    cria_diretorios($config['temp']);
    cria_diretorios($temp);
    
    $dados_ws = new BuscaWebService($autorizadora,'nfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("status",$dados['nfe']['versao']);
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    $converteUf = new CodigosUf();
    
    $cUf = $converteUf->paraNum($uf_ws);
    
    // Carregar os dados da nfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $cUf,
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    
    if ($autorizadora == "AM") {
        $array_substituicao['</soap12:Header>']=""; 
        $array_substituicao['<soap12:Header>']=""; 
        $array_substituicao['<soap12:Header/>']=""; 
    }
    

    $template_soap =  freplace($template_soap,$array_substituicao);
    
    $busca=array();
    $troca=array();
    $remov=array();
    
    $busca[]="soap12"   ;
    $troca[]="soap"  ;
    
    $busca[]='http://www.w3.org/2003/05/soap-envelope';
    $troca[]='http://www.w3.org/2003/05/soap-envelope" xmlns:nfes="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4';
    
    $remov[]="\n";
    $remov[]="\t";
    $remov[]="\r";
    $remov[]='xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $remov[]='xmlns:xsd="http://www.w3.org/2001/XMLSchema"';
    
    $validacao = $template_soap;
    
    $validacao = preg_replace('/(\>)\s*(\<)/m', '$1$2', $validacao);
    $validacao = str_replace($busca, $troca, $validacao ) ; 
    $validacao = str_replace($remov, ""    , $validacao ) ; 
    
    file_put_contents($arquivo_xml, $template_soap);
    
    if ($arquivo_saida == 'debug'){
        echo formatXML($template_soap, 'template_soap');    
    }
    
    // 72071541000200
    // echo ">>>>>>>>>>>". file_get_contents("$arquivo_xml") . "<<<<<<<<<<<";
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice, $autorizadora);
    /*
    // \! [03/05/2018] - Crozetta: Este if resolve uma divergencia no servidor do ceará, 
    // \!                que recebe e trata os dados de forma diferente
    if ($autorizadora == "CE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache",
            'Connection: Keep-Alive',
            'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)'
        );
    }
    if ($autorizadora == "MG") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
        );
    }
    
    if ($autorizadora == "PE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
        );
    }
    if ($autorizadora == "AM") {
        $soap->curl_header = Array(
            // 'Content-Type: application/soap+xml;charset=UTF-8;action="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4/nfeStatusServicoNF"',
            'Content-Type: application/soap+xml;charset=UTF-8;action="'.$array_webservice->namespace."wsdl/".$array_webservice->servico."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
		);
    }
    */
    
    
    
    
    $xml_retorno = $soap->comunicar($arquivo_saida == 'debug');
    
    function getVlTag($tag){
        global $xml_retorno;
        return exec("php ../ferramentas/f.texto.tag.php $tag '$xml_retorno'");
    }  
    
    $xml_retorno = str_replace("\n","",$xml_retorno);
    
    $xObs = '';
    if (strpos($xml_retorno, "xObs") !== false ) {
        $xObs = getVlTag("xObs");
    }
    
    $retorno = array();
    
    $retorno[] = getVlTag(   "cStat") ; 
    $retorno[] = getVlTag( "xMotivo") ; 
    $retorno[] = getVlTag("dhRecbto") ; 
    $retorno[] = getVlTag(    "tMed") ; 
    $retorno[] = $xObs                ; 
    $retorno[] = $xml_retorno         ; 
    
    $retorno = implode('|', $retorno);
    
    if ($arquivo_saida !== 'debug'){
        file_put_contents($arquivo_saida, $retorno);   
    } else {
        echo formatXML($xml_retorno, 'xml_retorno');    
        
        echo "\n$retorno\n\n" ; 
        
        httpStatus( getVlTag("cStat") );
    }
    