<?php
    /*
        Programa:  nfe-consulta-lote.php
        Descricão: Programa responsavel por realizar a consulta ddo lote nfe enviado
        Autor:     Fernando H. Crozetta (19/06/2017)
        Modo de uso:
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-consulta-lote.php <cnpj> <arquivoSaida> <recibo> <ambiente> <uf> <autorizadora>
    */
    
    if (!isset($argv[1])){
        echo "
            ENTRADA
            argv[1] = Cnpj             = \$argv[1]                         ; 
            argv[2] = Arquivo_saida    = \$argv[2]                         ; 
            argv[3] = Recibo           = \$argv[3]                         ; 
            argv[4] = Ambiente_entrada = \$argv[4]                         ; 
            argv[5] = Uf               = \$argv[5]                         ; 
            argv[6]*= Autorizadora     = (isset(\$argv[6])?\$argv[6]:\$uf) ; 
            
            SAIDA
            WS-203-CNPJ               //  1
            WS-203-UF                 //  2
            WS-203-CHAVE-NFE          //  3
            WS-203-AMBIENTE           //  4
            WS-203-UF-PROCESSOU       //  5
            WS-203-DATA-PROCESSAMENTO //  6
            WS-203-PROTOCOLO-SEFAZ    //  7
            WS-203-STATUS-SEFAZ       //  8
            WS-203-MOTIVO             //  9
            WS-203-PROTOCOLO          // 10
            WS-203-XML-INTEGRAL       // 11
            
        \n"; exit() ;
    }
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"               ) ; //                                     para gravar log // 
    require_once("../funcoes/fdebug.php"             ) ; //                                 Para realizar debug // 
    require_once("../funcoes/freplace.php"           ) ; //                                    Replace de dados // 
    require_once("../classes/CAssinaturaDigital.php" ) ; //                            Usado para assinar o xml // 
    require_once("../classes/validaXml.php"          ) ; //                            Usado para validar o xml // 
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService // 
    require_once("../classes/soapWebService.php"     ) ; //                     Usado para enviar envelope soap // 
    require_once("../classes/codigosUf.php"          ) ; //       Converte códigos UF para numero e vice versa // 
    require_once("../funcoes/carrega_config.ini.php" ) ; //                            Carrega as configuracoes // 
    require_once("../ferramentas/formatXML.php"      ) ; //
    require_once("../funcoes/httpStatus.php"         ) ; // 
    
    
    $cnpj             = $argv[1] ; 
    $arquivo_saida    = $argv[2] ; 
    $recibo           = $argv[3] ; 
    $ambiente_entrada = $argv[4] ; 
    $uf               = $argv[5] ; 
    $autorizadora     = (isset($argv[6])?$argv[6]:$uf) ; 
    
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    $dados = parse_ini_file("{$config['dados']}/config_cliente/$cnpj.ini", true); 
    $temp=$config['temp']."nfe/";

    $arquivo_xml="{$temp}{$recibo}-consulta-lote.xml";

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
        if ($assinatura->assinarXml('infNFe')) {
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
        $xsd = $config['servicos']."/nfe/schemas/".$dados['nfe']['pacote']."/".$ws->schema;
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
    $dados_ws = new BuscaWebService($autorizadora,'nfe',$ambiente); 
    $array_webservice = $dados_ws->buscarServico("consulta_lote",$dados['nfe']['versao']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    // $converteUf = new CodigosUf();
    // $cUf = $converteUf->paraNum($uf);
    

    
    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $uf, // ## TODO: Verificar neccessidade
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        'ALTERAR_RECIBO'=>$recibo,
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    
    // para validação    
    $arquivo_validacao = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/nfe/tempLote_validacao.xml";
    
    $template_soap =  freplace($template_soap,$array_substituicao);
    
    
    
    
    if ($arquivo_saida == 'debug'){
        echo formatXML($template_soap, "Envio");
    }
    
    
    file_put_contents($arquivo_xml, $template_soap);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice,$autorizadora);
    /*
    //! [03/05/2018] - Crozetta: Este if resolve uma divergencia no servidor do ceará, 
    //!                 que recebe e trata os dados de forma diferente
    if ($autorizadora == "CE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            'SOAPAction: "'.$array_webservice->namespace.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control : no-cache", 
            "Pragma: no-cache"
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
    if ($autorizadora == "AM") {
        $soap->curl_header = Array(
            // 'Content-Type: application/soap+xml;charset=UTF-8;action="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4/nfeStatusServicoNF"',
            'Content-Type: application/soap+xml;charset=UTF-8;action="'.$array_webservice->namespace."wsdl/".$array_webservice->servico."/".$array_webservice->metodo.'"',
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
        */
        // echo "OOOOOOOOOOOOOOOOOOOOOOOOOOOOO";
        
        $xml_retorno = $soap->comunicar($arquivo_saida === 'debug');
        $xml_retorno = str_replace("\n","",$xml_retorno);
    
        
    function getVlTag($tag){
        global $xml_retorno;
        return exec("php ../ferramentas/f.texto.tag.php $tag '$xml_retorno'");
    } 
    
    
    
    
    if ($autorizadora == "MG") {
        // echo "\n\n$xml_retorno\n"; exit();
        $xml_retorno = explode('<infProt'  , $xml_retorno) [1] ; 
        $xml_retorno = explode("</infProt>", $xml_retorno) [0] ; 
        
        $xml_retorno = "<infProt{$xml_retorno}</infProt>";
        $xml_retorno = preg_replace('/>[\s\t\r\n]+</', "><", $xml_retorno);
        
        // echo "\n{$xml_retorno}\n\n"; exit();
    } else {
    }
    // * Busca As tags do protNfe, com suas subtags, para o cobol realizar o append
    $protocolo= preg_replace("/.*<protNFe/","<protNFe",$xml_retorno);
    $protocolo= preg_replace("/protNFe><.*/","protNFe>",$protocolo);
    
    
    
    
    $retorno   = array()              ;
    $retorno[] = "$cnpj"              ; // WS-203-CNPJ       
    $retorno[] = "$uf"                ; // WS-203-UF         
    $retorno[] = getVlTag("chNFe")    ; // WS-203-CHAVE-NFE  
    $retorno[] = getVlTag("tpAmb")    ; // WS-203-AMBIENTE 
    $retorno[] = getVlTag("cUF")      ; // WS-203-UF-PROCESSOU 
    $retorno[] = getVlTag("dhRecbto") ; // WS-203-DATA-PROCESSAMENTO 
    $retorno[] = getVlTag("nProt")    ; // WS-203-PROTOCOLO-SEFAZ 
    $retorno[] = getVlTag("cStat")    ; // WS-203-STATUS-SEFAZ 
    $retorno[] = getVlTag("xMotivo")  ; // WS-203-MOTIVO 
    $retorno[] = $protocolo           ; // WS-203-PROTOCOLO
    $retorno[] = "$xml_retorno"       ; // WS-203-XML-INTEGRAL.
    
    $retorno = implode('|', $retorno);
    
    // print_r("\n$xml_retorno\n");
    //  exit();
    if ($arquivo_saida !== 'debug'){
        file_put_contents($arquivo_saida, $retorno);
    } else {
        echo formatXML($xml_retorno, "Retorno");
        echo "\n$retorno\n\n" ; 
        httpStatus( getVlTag("cStat") );
    }
    
    
    
    
    
    
    