<?php 
    /*
        Programa:  nfe-consulta-nota.php
        Descric?o: Programa responsavel por realizar a consulta de nfe
        Autor:     Fernando H. Crozetta (30/05/2017)
        Modo de Uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php <chave_nfe> <ambiente> <uf>
    */

    //require_once("../ferramentas/formatXML.php"      ) ; //
    //$xml_retorno = "<env:Envelope xmlns:env='http://www.w3.org/2003/05/soap-envelope'><env:Body xmlns:env='http://www.w3.org/2003/05/soap-envelope'><nfeResultMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeConsultaProtocolo4'><retConsSitNFe versao='4.00' xmlns='http://www.portalfiscal.inf.br/nfe'><tpAmb>2</tpAmb><verAplic>PR-v4_4_5</verAplic><cStat>101</cStat><xMotivo>Cancelamento de NF-e homologado</xMotivo><cUF>41</cUF><dhRecbto>2019-05-28T08:08:17-03:00</dhRecbto><chNFe>41190511476285000158550010000440821165944325</chNFe><protNFe versao='4.00'><infProt Id='ID141190000393719'><tpAmb>2</tpAmb><verAplic>PR-v4_4_5</verAplic><chNFe>41190511476285000158550010000440821165944325</chNFe><dhRecbto>2019-05-21T17:08:50-03:00</dhRecbto><nProt>141190000393719</nProt><digVal>WfRoAwHTBGaZKKihdbtbzVrcnTI=</digVal><cStat>100</cStat><xMotivo>Autorizado o uso da NF-e</xMotivo></infProt></protNFe><procEventoNFe versao='1.00'><evento versao='1.00'><infEvento Id='ID1101104119051147628500015855001000044082116594432501'><cOrgao>41</cOrgao><tpAmb>2</tpAmb><CNPJ>11476285000158</CNPJ><chNFe>41190511476285000158550010000440821165944325</chNFe><dhEvento>2019-05-24T08:45:58-03:00</dhEvento><tpEvento>110110</tpEvento><nSeqEvento>1</nSeqEvento><verEvento>1.00</verEvento><detEvento versao='1.00'><descEvento>Carta de Correcao</descEvento><xCorrecao>teste de carta de correcao</xCorrecao><xCondUso>A Carta de Correcao e disciplinada pelo paragrafo 1o-A do art. 7o do Convenio S/N, de 15 de dezembro de 1970 e pode ser utilizada para regularizacao de erro ocorrido na emissao de documento fiscal, desde que o erro nao esteja relacionado com: I - as variaveis que determinam o valor do imposto tais como: base de calculo, aliquota, diferenca de preco, quantidade, valor da operacao ou da prestacao; II - a correcao de dados cadastrais que implique mudanca do remetente ou do destinatario; III - a data de emissao ou de saida.</xCondUso></detEvento></infEvento><Signature xmlns='http://www.w3.org/2000/09/xmldsig#'><SignedInfo><CanonicalizationMethod Algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315'/><SignatureMethod Algorithm='http://www.w3.org/2000/09/xmldsig#rsa-sha1'/><Reference URI='#ID1101104119051147628500015855001000044082116594432501'><Transforms><Transform Algorithm='http://www.w3.org/2000/09/xmldsig#enveloped-signature'/><Transform Algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315'/></Transforms><DigestMethod Algorithm='http://www.w3.org/2000/09/xmldsig#sha1'/><DigestValue>fbbVK9grRyPQu9ynCz6032q3x5A=</DigestValue></Reference></SignedInfo><SignatureValue>Ojv/GqIHPGnnQgyxFILYR92xBJxH1B6y0wv+sI3Zn0beGw1uHL90RQySxv+h1GYm500Zu79SMdHDKqO0ha7TkQG1GYLuAulGrleHws7e9xEof6hVedH48+kikiLIHuUCY7RcarL3DvgUukXHqTF+PuVwWmGkWNsXX5Fts4nQT4BT8+Z9vB1QrzZRqb1J6zGdC6JrX/cyXH8wObOrsFJG4wpHlxUPHqd1vsCiet83nhrz2U0SLVLYzmooXlRfCSrTJn/bRpOARoMxPZVpXvNfnSXjLIobvNcXWV5iMw5lkUuPatBMws3foy5L5asr2X2WzhX5FR41xqjOFrhNSelNbQ==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></evento><retEvento versao='1.00' xmlns='http://www.portalfiscal.inf.br/nfe'><infEvento><tpAmb>2</tpAmb><verAplic>PR-v4_4_5</verAplic><cOrgao>41</cOrgao><cStat>135</cStat><xMotivo>Evento registrado e vinculado a NF-e</xMotivo><chNFe>41190511476285000158550010000440821165944325</chNFe><tpEvento>110110</tpEvento><xEvento>Carta de Correcao</xEvento><nSeqEvento>1</nSeqEvento><dhRegEvento>2019-05-24T08:48:16-03:00</dhRegEvento><nProt>141190000405385</nProt></infEvento></retEvento></procEventoNFe><procEventoNFe versao='1.00'><evento versao='1.00'><infEvento Id='ID1101114119051147628500015855001000044082116594432501'><cOrgao>41</cOrgao><tpAmb>2</tpAmb><CNPJ>11476285000158</CNPJ><chNFe>41190511476285000158550010000440821165944325</chNFe><dhEvento>2019-05-24T09:29:24-03:00</dhEvento><tpEvento>110111</tpEvento><nSeqEvento>1</nSeqEvento><verEvento>1.00</verEvento><detEvento versao='1.00'><descEvento>Cancelamento</descEvento><nProt>141190000393719</nProt><xJust>teste de cancelamento- TESTE OBS</xJust></detEvento></infEvento><Signature xmlns='http://www.w3.org/2000/09/xmldsig#'><SignedInfo><CanonicalizationMethod Algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315'/><SignatureMethod Algorithm='http://www.w3.org/2000/09/xmldsig#rsa-sha1'/><Reference URI='#ID1101114119051147628500015855001000044082116594432501'><Transforms><Transform Algorithm='http://www.w3.org/2000/09/xmldsig#enveloped-signature'/><Transform Algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315'/></Transforms><DigestMethod Algorithm='http://www.w3.org/2000/09/xmldsig#sha1'/><DigestValue>h6gQ+RYbss3xrlzREwcw4nfFVrw=</DigestValue></Reference></SignedInfo><SignatureValue>YlNDubpvL61lHnFtKZiGYms6B6d4Cv022HX+fHpGmpdn4I3aV7kKdWpJSbpd+BwmV+eZoWGFuJ3mf+glSEN9uZiUsAQLHsfU2w9KJljhgFP/Is6aeEe8DnbX0yTxpK9kwlbBZYFfv2E+rt6jfK45wAQFjoa8u5zRZfwuEM/DFY7OAEJlYG/8xiQ3tDl2Snx5jw/V7Zba4K8/Zh5d2MfjisTmkAaGzOCY3aNX4QNcfR780MaPmV+dxkopnbpw9gl9rJQI64mQaeh6NY4zNuyHclTA1fyPJ+kUzICjSwfW/BHtKByQgw4uGA/c0jw32137Yi3I5n7UyfgW3L+p/MHZrQ==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></evento><retEvento versao='1.00' xmlns='http://www.portalfiscal.inf.br/nfe'><infEvento><tpAmb>2</tpAmb><verAplic>PR-v4_4_5</verAplic><cOrgao>41</cOrgao><cStat>135</cStat><xMotivo>Evento registrado e vinculado a NF-e</xMotivo><chNFe>41190511476285000158550010000440821165944325</chNFe><tpEvento>110111</tpEvento><xEvento>Cancelamento</xEvento><nSeqEvento>1</nSeqEvento><dhRegEvento>2019-05-24T09:35:41-03:00</dhRegEvento><nProt>141190000405522</nProt></infEvento></retEvento></procEventoNFe></retConsSitNFe></nfeResultMsg></env:Body></env:Envelope>";
    //goto debugger;
    
    
    
    
    
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
    //debugger:
    
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
    
    
    
    
    $corteParaXml = "<retConsSitNFe" . (explode("</retConsSitNFe>", explode("<retConsSitNFe", $xml_original)[1])[0]). "</retConsSitNFe>" ;
    
    // echo "$corteParaXml";
    //echo formatXML($xml_retorno, 'xml_retorno');
    $objxml = simplexml_load_string($corteParaXml);
    
    
    
    //print_r("\ncStat    >" . (string)$objxml->cStat                   ) ; 
    //print_r("\ncUF      >" . (string)$objxml->cUF                     ) ; 
    //print_r("\ntpAmb    >" . (string)$objxml->tpAmb                   ) ; 
    //print_r("\nchNFe    >" . (string)$objxml->chNFe                   ) ; 
    //print_r("\nxMotivo  >" . (string)$objxml->xMotivo                 ) ; 
    //print_r("\ndhRecbto >" . (string)$objxml->dhRecbto                ) ; 
    //print_r("\nnProt     >" . (string)$objxml->protNFe->infProt->nProt ) ; 
    
    
    //echo "\n>\n"; 
    
    
    // echo count($objxml->procEventoNFe);
    
    // percorre os eventos
    for ($x = 0 ; $x < count($objxml->procEventoNFe) ; $x++ ){ 
        $tipo = (string) $objxml->procEventoNFe[$x]->evento->infEvento->detEvento->descEvento ; 
        $procEventoNFe = explode("<procEventoNFe " ,   $xml_retorno )[$x+1] ;
        if ( $tipo == "Carta de Correcao" ){
            //$eventoCorrecao  = explode("<procEventoNFe " ,   $xml_retorno )[$x+1] ;
            $wsSefazRetProtoEve  = corte("nProt"    , $procEventoNFe );
            $wsSefazRetTipoEve   = corte("tpEvento" , $procEventoNFe );
            $wsSefazRetDtHoraEve = corte("dhRegEvento" , $procEventoNFe );
            
            // ~ 
        }
        if ( $tipo == "Cancelamento"      ){
            
            //$eventoCancelamento  = explode("<procEventoNFe " ,   $xml_retorno )[$x+1] ;
            $wsSefazRetProtoCan  = corte("nProt"    , $procEventoNFe );
            //$wsSefazRetTipoEve   = corte("tpEvento"    , $eventoCancelamento );
            $wsSefazRetDtHoraCan = corte("dhRegEvento" , $procEventoNFe );
            // ~ 
        }
        
        
    }
    
    //echo "\n<\n"; 
    
    //exit();
    
    
    $protNFe = $eventoCancelamento  = explode("<protNFe " ,   $xml_retorno )[1] ;
    
    $wsSefazRetCnpj       = $cnpj ;
    $wsSefazRetStatus     = corte("cStat"    , $xml_dados ) ; 
    $wsSefazRetUfIbge     = corte("cUF"      , $xml_dados ) ; 
    $wsSefazRetAmbiente   = corte("tpAmb"    , $xml_dados ) ; 
    $wsSefazRetIdNfe      = corte("chNFe"    , $xml_dados ) ; 
    $wsSefazRetDescStatus = corte("xMotivo"  , $xml_dados ) ; 
    $wsSefazRetDtHora     = corte("dhRecbto" , $protNFe ) ; 
    $wsSefazRetProtocolo  = corte("nProt"    , split("</protNFe>", $xml_original )[0] ) ; 
    $wsSefazRetTpEmissao  = $chave_nfe[34] ; 
    
    
    $procEventoNFeCount = count(explode("<procEventoNFe " , $xml_retorno ));
    
    /* echo "$procEventoNFe @@@@@@@@@@@@@@@@@@@@@@@@@@";
    
    if($wsSefazRetStatus == '101')
    {
        $eventoCancelamento  = explode("<procEventoNFe " , $xml_retorno )[2] ; 
        
        
        
        
        
    }
    
    if($wsSefazRetStatus == '100')
    {
        $eventoCortado   = explode("<evento " ,   $xml_retorno )[1] ; 
        $eventoCortado   = explode("</evento>", $eventoCortado )[0] ; 
        $eventoCortado   = "<evento $eventoCortado</evento>"        ; 
        $wsSefazRetTipoEve    = corte("tpEvento" , $eventoCortado ) ; 
    }
    */
    
    
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
    //$retorno = implode('|', $retorno);
    //echo $retorno;
    
    /* echo"   INTO WS-SEFAZ-RET-CNPJ : $wsSefazRetCnpj   
            WS-SEFAZ-RET-UF-IBGE     : $wsSefazRetUfIbge       
            WS-SEFAZ-RET-TP-EMISSAO  : $wsSefazRetTpEmissao    
            WS-SEFAZ-RET-AMBIENTE    : $wsSefazRetAmbiente     
            WS-SEFAZ-RET-ID-NFE      : $wsSefazRetIdNfe        
            WS-SEFAZ-RET-STATUS      : $wsSefazRetStatus       
            WS-SEFAZ-RET-DESC-STATUS : $wsSefazRetDescStatus   
            WS-SEFAZ-RET-PROTOCOLO   : $wsSefazRetProtocolo    
            WS-SEFAZ-RET-DT-HORA     : $wsSefazRetDtHora       
            WS-SEFAZ-RET-PROTO-CAN   : $wsSefazRetProtoCan     
            WS-SEFAZ-RET-DT-HORA-CAN : $wsSefazRetDtHoraCan    
            WS-SEFAZ-RET-PROTO-EVE   : $wsSefazRetProtoEve     
            WS-SEFAZ-RET-TIPO-EVE    : $wsSefazRetTipoEve      
            WS-SEFAZ-RET-DT-HORA-EVE : $wsSefazRetDtHoraEve "; */
    //echo "WS-SEFAZ-RET-XML.$wsSefazRetXml \n";
    
    
    
    
    
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
        //echo $retorno;
        file_put_contents($arquivo_txt_saida, $retorno);   
    } 
    // ~ 
    else {
        echo formatXML($xml_retorno, 'xml_retorno cortado');
        // print_r($retorno_dbg);
        print_r($retorno) ; 
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
