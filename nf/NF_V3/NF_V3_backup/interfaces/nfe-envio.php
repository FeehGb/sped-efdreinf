<?php 
    /*
        Programa:  nfe-envio.php
        Descricão: Programa responsavel por realizar o envio de nota para a sefaz
        Autor:     Fernando H. Crozetta (18/12/2017)
        Modo de uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php <caminho_arquivo.txt> <caminho-arquivo_saida.xml> <UF> <autorizadora>
    */
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = arquivo_txt                      ; 
                argv[2] = arquivo_txt_saida                ; 
                argv[3]*= estado                |     'PR' ; 
                argv[4]*= autorizadora          |  argv[3] ; 
                argv[5]*= recuperarChave        |     '-1' ; 
                argv[6]*= testesAutomatizados   |      '2' ; 
            
            SAIDA:
                WE-RET-CNPJ            // cnpj         ; 
                WE-RET-RECIBO          // ret_nRec     ; 
                WE-RET-CHAVE-NFE       // chave        ; 
                WE-RET-STATUS          // ret_cStat    ; 
                WE-RET-DESCRICAO       // ret_xMotivo  ; 
                WE-RET-DATA-HORA-SEFAZ // ret_dhRecbto ; 
                WE-RET-QRCODE          // qrCode       ; 
                WE-RET-XML-INTEGRAL    // xml_retorno  ; 
            
        \n"; exit() ;
    }
    
    
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"               ) ; // para gravar log
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados
    require_once("../funcoes/txt2xml.php"            ) ; // para converter txt para xml
    require_once("../funcoes/carrega_config.ini.php" ) ; // Carrega as configuracoes
    require_once("../funcoes/nfe_qrcode2.php"        ) ; // para criar o qrcode
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"          ) ; // Usado para retornar o código uf, baseado na sigla
    require_once("../ferramentas/formatXML.php"      ) ; //
    require_once("../funcoes/httpStatus.php"         ) ; // .

        
    // qr-code, usado apenas na nfc
    $qrCode="";
    
    // Carrega as configurações de clientes e sistema
    // $config = parse_ini_file("../config/config.ini");
    $temp=$config['temp']."nfe/";
    
    // Dados que são passados pelo programa chamador (cobol)
    $arquivo_txt = $argv[1];
    
    $arquivo_txt_saida = $argv[2];
    
    // Se não for passado o parametro, buscar estado RS
    $estado              = (isset($argv[3])?$argv[3]:'PR')    ; 
    $autorizadora        = (isset($argv[4])?$argv[4]:$estado) ; 
    $recuperarChave      = (isset($argv[5])?$argv[5]:'-1')    ; 
    $testesAutomatizados = (isset($argv[6])?$argv[6]:'2')     ; 
    
    $isOff = false ; 
    if (strpos($autorizadora, '-OFF') !== false) {
        $isOff = true ; 
        $autorizadora = str_replace('-OFF', '', $autorizadora);
    }
    
    $isNFC = false;
    if (strpos($autorizadora, 'NFCE-') === 0){
        $isNFC = true;
    }
    
    // Cria o nome raiz do arquivo a ser manipulado
    $nome_raiz = end(explode("/",$arquivo_txt));
    // $nome_raiz = str_replace(".txt", '', $nome_raiz);

    $nome_arquivo_saida = str_replace("NFE", "LNFER", $nome_raiz);

    $arquivo_xml = $temp.$nome_raiz.".xml";
    $arquivo_validacao = $temp.$nome_raiz.".validacao.xml";
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir) {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    /* Assinatura do XML */
    function assinaXml($arquivo,$cnpj)
    {
        $assinatura = new CAssinaturaDigital($arquivo,$cnpj);
        // Grava no arquivo se estiver tudo ok. Retorna o erro se der errado.
        if ($assinatura->assinarXml('infNFe','NFe')) {
        // if ($assinatura->assinarXml('infNFe','NFe')) {
        // if ($assinatura->assinarXml('infNFe')) {
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
        $motivo='';
        $dados = parse_ini_file("{$config['dados']}/config_cliente/$cnpj.ini", true); 
        $xsd = $config['servicos']."/nfe/schemas/".$dados['nfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        if(!$validacao->validar()){
            $motivo.= $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }
    
    cria_diretorios($temp);
    
    // Converte o TXT para XML
    $retornoConversor = txt2xml($arquivo_txt, $arquivo_xml, 'NOTAFISCAL', $recuperarChave);
    // Se houer algum erro, cria o retorno para o cobol informado o erro e sai do PHP
    if ($retornoConversor !== true)
    {
        // Varios dados estao vazios pois eles vem do XML e do sefaz, em caso de erro, o XML nao eh criado, e nem eh enviado ao sefaz
        $retorno   = array();
        $retorno[] = ""                  ; // WE-RET-CNPJ            // 
        $retorno[] = ""                  ; // WE-RET-RECIBO          // 
        $retorno[] = ""                  ; // WE-RET-CHAVE-NFE       // 
        $retorno[] = "-1"                ; // WE-RET-STATUS          // 
        $retorno[] = "$retornoConversor" ; // WE-RET-DESCRICAO       // 
        $retorno[] = ""                  ; // WE-RET-DATA-HORA-SEFAZ // 
        $retorno[] = ""                  ; // WE-RET-QRCODE          // 
        $retorno[] = ""                  ; // WE-RET-XML-INTEGRAL    // 
        
        $retorno = implode('|', $retorno);
        
        if ($arquivo_txt_saida !== 'debug'){
            file_put_contents($arquivo_txt_saida, $retorno);
        } else {
            echo "\n##retornoConversor:\n$retorno\n\n" ; 
        }
        
        exit();
    } 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    //*
    // ? CALCULO DE CST 
    $conteudo_xml = simplexml_load_file($arquivo_xml);  
    
    // para nao criar em producao ainda, so em homologacao
    if ($conteudo_xml->infNFe->infRespTec->hashCSRT->value !== null)
    {
        $conteudo_xml->infNFe->infRespTec->hashCSRT = 
            base64_encode( 
                sha1( 
                    $conteudo_xml->infNFe->infRespTec->hashCSRT
                    . 
                    substr($conteudo_xml->infNFe->attributes()->Id, 3)
                , true )
            ) 
        ; 
        
        $conteudo_xml = str_replace('<?xml version="1.0"?>', '', $conteudo_xml->saveXML());
        file_put_contents ( $arquivo_xml, $conteudo_xml ) ; 
    }
    // ? FIM DO CALCULO DE CST 
    
    
    
    
    
    
    
    
    
    // Carrega o xml e busca dados
    $conteudo_xml     = simplexml_load_file($arquivo_xml)                                   ; 
    $cnpj             = $conteudo_xml->infNFe->emit->CNPJ                                   ; 
    $uf               = $conteudo_xml->infNFe->ide->cUF                                     ; 
    $chave            = $conteudo_xml->infNFe->attributes()->Id                             ; 
    $chave            = substr($conteudo_xml->infNFe->attributes()->Id, 3)                  ; 
    $ambiente         = ($conteudo_xml->infNFe->ide->tpAmb == 1?'producao':'homologacao')   ; 
    $dados            = parse_ini_file("{$config['dados']}/config_cliente/$cnpj.ini", true) ; 
    $dados_ws         = new BuscaWebService($autorizadora,'nfe',$ambiente)                  ; 
    $array_webservice = $dados_ws->buscarServico("recepcao",$dados['nfe']['versao'])        ; 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    // print_r($dados_ws);
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    // [19/07/22017] - f.crozetta: Este arquivo é usado após a consulta, realizando o append do protocolo
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
        'ALTERAR_INDSINC' => $dados['nfe']['envioLoteSincrono'],
        'TAG VAZIA' =>'',
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
    
    
    file_put_contents($arquivo_xml, $template_soap);
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function cortar_assinaXml()
    {
        global $arquivo_xml       ; 
        global $arquivo_validacao ; 
        global $cnpj              ; 
        global $chave             ; 
        global $dados             ; 
        
        // para validação
        $validacao = file_get_contents($arquivo_xml);
        
        $validacao = preg_replace("/.*<NFe/"   , "<NFe"  , $validacao);
        $validacao = preg_replace("/<\/NFe>.*/", "</NFe>", $validacao);
        
        file_put_contents($arquivo_validacao, $validacao);
        
        // Assinar e validar xml
        assinaXml($arquivo_validacao,$cnpj);
        
        $validacao = file_get_contents($arquivo_validacao);
        $xmlFinal  = file_get_contents($arquivo_xml);
        
        $parte1 = explode('<NFe ' , $xmlFinal)[0];
        $parte2 = explode('</NFe>', $xmlFinal)[1];
        
        $xmlFinal = "$parte1$validacao$parte2";
        
        $xmlFinal = str_replace("\n", "", $xmlFinal);
        
        //Grava o arquivo na pasta de temporarios para uso posterior
        file_put_contents($dados['nfe']['temp_nota'].$chave.".xml",$validacao);
        
        file_put_contents($arquivo_xml, $xmlFinal);
    }
        
    /**
     * QRCODE 
     */
    function makeQrcode(){
        // global $autorizadora;
        global $qrCode;
        global $arquivo_xml;
        global $array_webservice;
        global $dados;
        global $token;
        
        $qrCode = nfe_qrcode2_gerar(
            $arquivo_xml                        , 
            $array_webservice->uri_consulta_nfc , 
            $dados['nfe']['dir_retorno']        , 
            true                                , 
            $token                              )
        ;
    }
    
    
    function removerToken($remove)
    {
        global $token ; 
        global $arquivo_xml ; 
        
        $xml = file_get_contents($arquivo_xml);
        
        $nfe_qrcode_xml = new DOMDocument('1.0', 'utf-8');
        $nfe_qrcode_xml->preservWhiteSpace = false; 
        $nfe_qrcode_xml->loadXML($xml);
        
        $token=array(
            "idToken" => $nfe_qrcode_xml->getElementsByTagName('idToken')->item(0)->nodeValue ,
            "token"   => $nfe_qrcode_xml->getElementsByTagName('token'  )->item(0)->nodeValue , 
        );
        
        if ($remove)
        {
            $infNFeSupl = $nfe_qrcode_xml->getElementsByTagName("infNFeSupl")->item(0);
            $infNFeSupl->parentNode->removeChild($infNFeSupl);
            
            $xml = $nfe_qrcode_xml->saveXML();
            
            $xml = str_replace(array(
                '<?xml version="1.0" encoding="utf-8"?>',
            ), "", $xml);
            
            file_put_contents($arquivo_xml, $xml);
            unset($nfe_qrcode_xml);
        }
    }
    
    // Se for NFCE
    if ($isNFC){
        // Se for offline
        if (preg_match("/([0-9]{34}9[0-9]{9})/", $chave) === 1){
            removerToken(true);
            cortar_assinaXml();
            makeQrcode();
        } 
        // se for online
        else {
            removerToken(false);
            makeQrcode();
            cortar_assinaXml();
        }
    } 
    // se for NFE
    else {
        cortar_assinaXml();
    }
    
    
    
    /**
     * Testes automatizados rodam ateh antes de enviar, pois ele usa o arquivo gerado ath aqui
     */
    if ($testesAutomatizados == '1'){ exit(); }
    
    echo "\n\n" . file_get_contents( $arquivo_xml ) ; 
    
    // exit();
    
    $soap = new SoapWebService($arquivo_xml, $cnpj, $array_webservice, $autorizadora);
    
    /*
    //! [03/05/2018] - Crozetta: Este if resolve uma divergencia no servidor do ceará, 
    //!                 que recebe e trata os dados de forma diferente
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
    function getVlTag($tag)
    {
        global $xml_retorno;
        //return exec("php ../ferramentas/f.texto.tag.php $tag '$xml_retorno'");
        
        $regex   = "/^.*<$tag>(.*)<\/$tag>.*$/m";
        $matches = array();
        preg_match_all($regex, $xml_retorno, $matches);
        
        if (isset($matches) && isset($matches[1]) && isset($matches[1][0])){
            return $matches[1][0];
        } else {
            return " ";
        }
    } 
    
    // o qrcode eh separado por pipes, trocamos por ';' para nao ter problemas com a nossa separacao, o cobol volta para pipes apos retornar
    $qrCode = str_replace('|', ';', $qrCode);
    
    
    if ($arquivo_txt_saida === 'debug'){
        // exit();
    }
    
    
    // Online
    if (!$isOff)
    {
        // TODO gravar o XML retorno 
        echo "\n\n--------------------\n\n" ; 
        echo file_get_contents($arquivo_xml); 
        $xml_retorno = $soap->comunicar($arquivo_txt_saida === 'debug')   ; 
        echo "\n\n--------------------\n\n" ; 
        echo                  ($xml_retorno); 
        echo "\n\n--------------------\n\n" ; 
        
        $ret_nRec    = getVlTag("nRec")     ; 
        $ret_cStat   = getVlTag("cStat")    ; 
        $ret_xMotivo = getVlTag("xMotivo")  ; 
        $ret_dhRecbto= getVlTag("dhRecbto") ; 
        $xml_retorno = str_replace("\n","",$xml_retorno) ; 
    } 
    
    // Contingencia
    else {
        $ret_nRec     = " " ;
        $ret_cStat    = " " ;
        $ret_xMotivo  = " " ;
        $ret_dhRecbto = " " ;
        $xml_retorno  = " " ; 
    }
    
    $retorno = array();
        
    $retorno[] = $cnpj         ; // WE-RET-CNPJ            // 
    $retorno[] = $ret_nRec     ; // WE-RET-RECIBO          // 
    $retorno[] = $chave        ; // WE-RET-CHAVE-NFE       // 
    $retorno[] = $ret_cStat    ; // WE-RET-STATUS          // 
    $retorno[] = $ret_xMotivo  ; // WE-RET-DESCRICAO       // 
    $retorno[] = $ret_dhRecbto ; // WE-RET-DATA-HORA-SEFAZ // 
    $retorno[] = $qrCode       ; // WE-RET-QRCODE          // 
    $retorno[] = $xml_retorno  ; // WE-RET-XML-INTEGRAL    // 
    
    $retorno = implode('|', $retorno);
    
    if ($arquivo_txt_saida !== 'debug'){
        file_put_contents($arquivo_txt_saida, $retorno); 
    } else {
        echo formatXML($xml_retorno);
        
        echo "\nret_nRec     =>$ret_nRec<="    ; 
        echo "\nret_cStat    =>$ret_cStat<="   ; 
        echo "\nret_xMotivo  =>$ret_xMotivo<=" ; 
        echo "\nret_dhRecbto =>$ret_dhRecbto<="; 
        echo "\nxml_retorno  =>$xml_retorno<=" ; 
        
        echo "\n$retorno\n\n" ; 
        
        httpStatus( getVlTag($ret_cStat) );
    }
    
    
    
    
    
    
    
    