<?php 
    /*
        Programa:  nfe-consulta-nota.php
        Descric?o: Programa responsavel por realizar a consulta de nfe
        Autor:     Fernando H. Crozetta (30/05/2017)
        Modo de Uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php <chave_nfe> <ambiente> <uf>
    */
    
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = chave_nfe               
                argv[2] = cnpj                    
                argv[3] = saida                   
                argv[4]*= ambiente_entrada | 2 ;  
            
            SAIDA:
                WS-SEFAZ-RET-CNPJ        | 
                WS-SEFAZ-RET-UF-IBGE     | 
                WS-SEFAZ-RET-TP-EMISSAO  | 
                WS-SEFAZ-RET-AMBIENTE    | 
                WS-SEFAZ-RET-ID-NFE      | 
                WS-SEFAZ-RET-STATUS      | 
                WS-SEFAZ-RET-DESC-STATUS | 
                WS-SEFAZ-RET-PROTOCOLO   | 
                WS-SEFAZ-RET-DT-HORA     | 
                WS-SEFAZ-RET-PROTO-CAN   | 
                WS-SEFAZ-RET-DT-HORA-CAN | 
                WS-SEFAZ-RET-PROTO-EVE   | 
                WS-SEFAZ-RET-TIPO-EVE    | 
                WS-SEFAZ-RET-DT-HORA-EVE | 
                WS-SEFAZ-RET-XML         | 
            
        \n"; exit() ;
    }
    
    
    chdir(__DIR__); //Este comando ? necess?rio para ir at? o diret?rio do programa 
    require_once("../funcoes/flog.php"               ) ; // para gravar log
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados
    require_once("../funcoes/carrega_config.ini.php" ) ; // Carrega as configuracoes
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"          ) ; // Converte c?digos UF para numero e vice versa
    require_once("../ferramentas/formatXML.php"      ) ; //
    require_once("../funcoes/chaveDecode.php"        ) ; // 
    
    
    // php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php 41180803246792000177550010001786751003462453
    
    // 41180803246792000177550010001786751003462453
    
    // /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php 43180873694119000185550000000714341182290533 81067860000144 debug
    //*
    $chave_nfe         = $argv[1] ; //chave da nota
    $cnpj              = $argv[2] ; //
    $arquivo_txt_saida = $argv[3] ; //
    $ambiente_entrada  = isset($argv[4]) ? $argv[4] : '2' ; 
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";
    
    
    
    /*
    // */
    /*
    41190182295817000107550010000479041093037628 82295817000107 debug
    NFe p/ testar consulta sefaz:
    CNPJ          : 82295817000107                               ; 
    C/evento CC-e : 41190182295817000107550010000481311154127913 ; 
    Cancelada     : 41190182295817000107550010000481191100202726 ; 
    Normal        : 41190182295817000107550010000479041093037628 ; 
    
    
    
    
    // $cnpj = "01956679000150"; 
    $cnpj = "82206004000195"; 
    $ambiente_entrada = "1";
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";
    
    $chave_nfe = "32181105193785000341550030000012341002223455" ; // OK 
    $chave_nfe = "43181129506474002305550020000805141637249997" ; // ER 
    $chave_nfe = "42181100159958000120550010000042351000047170" ; // OK 
    $chave_nfe = "35181101560653000198550010000094281000112310" ; // ER 
    $chave_nfe = "43181129506474002305550020000807461477953351" ; // ER 
    $chave_nfe = "43181104270312000176550010000041241000000293" ; // ER 
    
    
    $chave_nfe = "43190100066130000127550010000154981218387606" ; // er ?
    $chave_nfe = "42181079687588000153550050000900071984303773" ; // er ?
    $chave_nfe = "35181101560653000198550010000094281000112310" ; // er ?
    $chave_nfe = "33181007209611000193550010000012011000100831" ; // er ?
    $chave_nfe = "32181105193785000341550030000012341002223455" ; // OK 
    
    
    82206.004/0001-95
    
    */
    
    // $cnpj              =                                 "82206004000195" ; 
    // $chave_nfe         =   "42181079687588000153550050000900111984303776" ; 
    // $arquivo_txt_saida =                                          'debug' ; 
    // $ambiente_entrada  =                                              "1" ; 
    
    // $chave_nfe = "43180892664028002608550010019937041139049875";
    // $ambiente_entrada = "1";
    // $cnpj              = "01956679000150";
    // $ambiente_entrada = (isset($argv[2])?$argv[2]:'2');// 
        
    // $uf = (isset($argv[3])?$argv[3]:'PR');
    /*
    $chave_nfe         = "43180873694119000185550000000714341182290533";
    // $chave_nfe         = "41180909593770000240550010000310051114239505";
    $ambiente_entrada  = '2'     ;
    $ufParametro       = 'PR'    ;
    $arquivo_txt_saida = 'debug' ;
    // */
    
    
    
    $debug = false;
    if ($arquivo_txt_saida == 'debug') { 
        $debug = true;
    }
    
    $chave_decoded = chaveDecode($chave_nfe) ; 
    $autorizadora  = $chave_decoded['uf']    ; 
    
    // print($autorizadora);
    
    // exit();
    
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";
    
    $str  = file_get_contents('../servicos/nfe/urls/urls.json');
    $urls = json_decode($str, true);
    $uf   = $urls['number_uf'][$autorizadora] ; 
    
    if ($debug) {
        print_r(chaveDecode($chave_nfe)) ; 
    }
    //exit();
    // Carrega as configura??es de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/$cnpj.ini",true);
    $temp=$config['temp']."nfe/";
    
    $arquivo_xml = "$temp$chave_nfe-consulta-nota.xml";
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }
    
    // main
    cria_diretorios($temp);
    
    
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // echo "\n\n$uf\n\n";
    
    // $dados_ws = new BuscaWebService($uf,'mdfe',$ambiente);
    
    
    
    // echo ">>>>>$uf<<<<<" ; 
    // $uf = "SVAN" ; 
    $dados_ws = new BuscaWebService($uf,'nfe',$ambiente); //S? existe RS
    
    $array_webservice = $dados_ws->buscarServico("consulta_nota",$dados['nfe']['versao']);
    
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
                            "ALTERAR_TIPO_AMBIENTE" => $ambiente_entrada 
        ,                       "ALTERAR_CHAVE_NFE" => $chave_nfe        
        ,  "<?xml version='1.0' encoding='UTF-8'?>" => ''                
        ,                                      "\n" => ''                
        ,                                      "\r" => ''                
        ,                                      "\t" => ''                
    );
    
    
    $template_soap =  freplace($template_soap,$array_substituicao);
    
    if ($debug){
        echo formatXML($template_soap, 'template_soap');    
    }
    
    file_put_contents($arquivo_xml, $template_soap);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice,$autorizadora);
    
    
    // echo "$autorizadora";exit();
    /*
    if ($autorizadora == 'SP'){
        $soap->array_dados_cliente['curl']["sslversion"] = "1";
    }
    if ($autorizadora == '42'){
        $soap->array_dados_cliente['curl']["sslversion"] = "1";
    }
    */
    
    // $soap->array_dados_cliente['curl']["sslversion"] = "1";
    
    
    // $soap->array_webservice->url = $urlEstado;
    $xml_retorno = $soap->comunicar($debug);
    
    $xml_original = $xml_retorno;
    
    if ($debug) {
        echo ($xml_retorno);
        echo formatXML($xml_retorno, 'xml_retorno');
    }
    
    
    
    
    // pega os dados principais da nota
    $xml_dados = explode("<protNFe "  , $xml_retorno )[0] 
        . "</retConsSitNFe>"  
        .  "</nfeResultMsg>"  
        .      "</env:Body>"  
        .  "</env:Envelope>" 
    ; 
    
    
    function corte($tag, $xml)
    {
        $xmlOriginal = $xml;
        
        $xml = preg_replace("/^.*\<$tag\>/m"   , '', $xml);
        $xml = preg_replace("/\<\/$tag\>.*$/m" , '', $xml);
        
        return $xml!=$xmlOriginal?$xml:"";
    }
    /*
    //82295817000107 //Stat
    $retorno     = array() ; 
    $retorno_dbg = array() ; 
    
    //print(corte("cStat"     , $xml_retorno ));
    //print($xml_retorno); 
    //print('antes');
    
    $dhRegEventoCan  = ' ';
    $nProtCan        = ' ';
    
    if(corte("cStat"     , $xml_dados ) == '101'){
        $nProtCan        = corte("nProt"           , $xml_dados );
        $dhRegEventoCan  = corte("dhRegEvento"     , $xml_dados );
    }
    
    $nProtEve        = ' ';
    $tpEventoEve     = ' ';
    $dhRegEventoEve  = ' ';
    
    if(corte("cStat"     , $xml_dados ) == '100'){
        $nProtEve        = corte("nProt"           , $xml_original );
        $tpEventoEve     = corte("tpEvento"        , $xml_original );
        $dhRegEventoEve  = corte("dhRegEvento"     , $xml_original );
    }
    */
    
    $wsSefazRetCnpj        = " " ; 
    $wsSefazRetUfIbge      = " " ; 
    $wsSefazRetTpEmissao   = " " ; 
    $wsSefazRetAmbiente    = " " ; 
    $wsSefazRetIdNfe       = " " ; 
    $wsSefazRetStatus      = " " ; 
    $wsSefazRetDescStatus  = " " ; 
    $wsSefazRetProtocolo   = " " ; 
    $wsSefazRetDtHora      = " " ; 
    $wsSefazRetProtoCan    = " " ; 
    $wsSefazRetDtHoraCan   = " " ; 
    $wsSefazRetProtoEve    = " " ; 
    $wsSefazRetTipoEve     = " " ; 
    $wsSefazRetDtHoraEve   = " " ; 
    $wsSefazRetXml         = " " ; 
    
    
    
    
    $wsSefazRetCnpj       = $cnpj ;
    $wsSefazRetStatus     = corte("cStat"    , $xml_dados ) ; 
    $wsSefazRetUfIbge     = corte("cUF"      , $xml_dados ) ; 
    $wsSefazRetAmbiente   = corte("tpAmb"    , $xml_dados ) ; 
    $wsSefazRetIdNfe      = corte("chNFe"    , $xml_dados ) ; 
    $wsSefazRetDescStatus = corte("xMotivo"  , $xml_dados ) ; 
    $wsSefazRetDtHora     = corte("dhRecbto" , $xml_dados ) ; 
    $wsSefazRetProtocolo  = corte("nProt"    , split("</protNFe>", $xml_original )[0] ) ; 
    $wsSefazRetTpEmissao  = $chave_nfe[34] ; 
    
    
    
    if($wsSefazRetStatus == '101')
    {
        $eventoCancelamento  = explode("<evento " ,        $xml_retorno )[1] ; 
        $eventoCancelamento  = explode("</evento>", $eventoCancelamento )[0] ; 
        $eventoCancelamento  = "<evento $eventoCancelamento</evento>"        ; 
        
        $wsSefazRetProtoCan  = corte("nProt"    , $eventoCancelamento );
        $wsSefazRetDtHoraCan = corte("dhEvento" , $eventoCancelamento );
    }
    
    if($wsSefazRetStatus == '100')
    {
        $eventoCortado   = explode("<evento " ,        $xml_retorno )[1] ; 
        $eventoCortado   = explode("</evento>", $eventoCortado )[0] ; 
        $eventoCortado   = "<evento $eventoCortado</evento>"        ; 
        
        $wsSefazRetTipoEve    = corte("tpEvento" , $eventoCortado ) ; 
        $wsSefazRetProtoEve   = corte("nProt"    , $eventoCortado ) ; 
        $wsSefazRetDtHoraEve  = corte("dhEvento" , $eventoCortado ) ; 
    }
    
    
    
    $wsSefazRetXml = $xml_original;
    
    $retorno   = array()               ; 
    $retorno[] = $wsSefazRetCnpj       ; //  0 WS-SEFAZ-RET-CNPJ        // 
    $retorno[] = $wsSefazRetUfIbge     ; //  1 WS-SEFAZ-RET-UF-IBGE     // 
    $retorno[] = $wsSefazRetTpEmissao  ; //  2 WS-SEFAZ-RET-TP-EMISSAO  // 
    $retorno[] = $wsSefazRetAmbiente   ; //  3 WS-SEFAZ-RET-AMBIENTE    // 
    $retorno[] = $wsSefazRetIdNfe      ; //  4 WS-SEFAZ-RET-ID-NFE      // 
    $retorno[] = $wsSefazRetStatus     ; //  5 WS-SEFAZ-RET-STATUS      // 
    $retorno[] = $wsSefazRetDescStatus ; //  6 WS-SEFAZ-RET-DESC-STATUS // 
    $retorno[] = $wsSefazRetProtocolo  ; //  7 WS-SEFAZ-RET-PROTOCOLO   // 
    $retorno[] = $wsSefazRetDtHora     ; //  8 WS-SEFAZ-RET-DT-HORA     // 
    $retorno[] = $wsSefazRetProtoCan   ; //  9 WS-SEFAZ-RET-PROTO-CAN   // 
    $retorno[] = $wsSefazRetDtHoraCan  ; // 10 WS-SEFAZ-RET-DT-HORA-CAN // 
    $retorno[] = $wsSefazRetProtoEve   ; // 11 WS-SEFAZ-RET-PROTO-EVE   // 
    $retorno[] = $wsSefazRetTipoEve    ; // 12 WS-SEFAZ-RET-TIPO-EVE    // 
    $retorno[] = $wsSefazRetDtHoraEve  ; // 13 WS-SEFAZ-RET-DT-HORA-EVE // 
    $retorno[] = $wsSefazRetXml        ; // 14 WS-SEFAZ-RET-XML         // 
    // $retorno = implode('|', $retorno);
    
    
    /*
    $contador = 0 ; 
    $retorno_dbg[ "WS-SEFAZ-RET-CNPJ"        ] =  "        => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-UF-IBGE"     ] =     "     => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-TP-EMISSAO"  ] =        "  => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-AMBIENTE"    ] =      "    => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-ID-NFE"      ] =    "      => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-STATUS"      ] =    "      => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-DESC-STATUS" ] =         " => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-PROTOCOLO"   ] =       "   => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-DT-HORA"     ] =     "     => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-PROTO-CAN"   ] =       "   => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-DT-HORA-CAN" ] =         " => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-PROTO-EVE"   ] =       "   => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-TIPO-EVE"    ] =      "    => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-DT-HORA-EVE" ] =         " => " . $retorno[$contador++] ; 
    $retorno_dbg[ "WS-SEFAZ-RET-XML"         ] = "         => " . $retorno[$contador++] ; 
    */
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    if (!$debug){
        $retorno = implode('|', $retorno) ; 
        file_put_contents($arquivo_txt_saida, $retorno);   
    } 
    // ~ 
    else {
        echo formatXML($xml_retorno, 'xml_retorno cortado');
        // print_r($retorno_dbg);
        echo "\n$retorno\n\n" ; 
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
