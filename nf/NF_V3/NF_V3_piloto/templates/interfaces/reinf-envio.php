<?php 
    /*
        Programa:  reinf-envio.php
        Descricao: Programa responsavel por realizar o envio de nota para a sefaz
        Autor:     Fernando H. Crozetta (28/05/2018)
        Modo de uso: 
    */
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:                                    
            argv[1] = cnpj                          ;   
            argv[2] = arquivo_txt                   ;   
            argv[3] = arquivo_txt_saida             ;   
            argv[4]*= numero_ambiente     | '2'     ;   
            argv[5]*= estado              | 'BR'    ;   
            argv[6]*= autorizadora        | argv[5] ;   
            argv[7]*= testesAutomatizados | '2'     ;   
                                                        
            SAIDA:                                      
                Lote                                    
                    @id            |                    
                    IdTransmissor  |                    
                    cdStatus       |                    
                    descRetorno    |                    
                Evento                                  
                    @id            |                    
                    tpInsc         |                    
                    nrInsc         |                    
                    cdRetorno      |                    
                    descRetorno    |                    
                    tpOcorr        |                    
                    localErroAviso |                    
                    codResp        |                    
                    dscResp        |                    
                    dhProcess      |                    
                    tpEv           |                    
                    idEv           |                    
                    nrProtEntr     |                    
                    nrRecArqBase   |   
                    
        \n"; exit() ;
    }
    
    
    /**
     *  TODO fazer uma tabela de erros.
     *! ERROS : 
     *?     -1  | Erro de conversao do TXT para xml
     *?     -2  | Falha de schema
     *?     -3  | arquivo de vonfiguracao inexistente
     *?     -4  | certificado inexistente
     */
    
    
    
    chdir(__DIR__); //Este comando é necessario para ir até o diretorio do programa 
    require_once("../funcoes/flog.php"               ) ; // para gravar log
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados
    require_once("../funcoes/txt2xml.php"            ) ; // para converter txt para xml
    require_once("../funcoes/nfe_qrcode.php"         ) ; // para criar o qrcode
    require_once("../funcoes/corte.php"              ) ; // para criar o qrcode
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"          ) ; // Usado para retornar o codigo uf, baseado na sigla
    require_once("../ferramentas/formatXML.php"      ) ; //   
    
    flog( "php " . implode ( ' ', $argv ) ) ; 
    
    $REINF_versao = "1.04.00" ; 
    
    // template do retorno do lote
    $templateLote = '@id|IdTransmissor|cdStatus|descRetorno';
    
    // Tempalate do retorno do evento
    $templateEvento = '@id|tpInsc|nrInsc|cdRetorno|descRetorno|tpOcorr|localErroAviso|codResp|dscResp|dhProcess|tpEv|idEv|nrProtEntr|nrRecArqBase';
    
    // 76520758000112
    // Eventos do xml a serem assinados
    $eventos = array(
        /*01*/ "evtInfoContri"    , // R-1000 - Informacoes do Empregador/Contribuinte                               ; 
        /*02*/ "evtTabProcesso"   , // R-1070 - Tabela de Processos Administrativos/Judiciais                        ; 
        /*03*/ "evtServTom"       , // R-2010 – Retencao Contribuicao Previdenciaria - Servicos Tomados              ; 
        /*04*/ "evtServPrest"     , // R-2020 – Retencao Contribuicao Previdenciaria - Servicos Prestados            ; 
        /*05*/ "evtAssocDespRec"  , // R-2030 – Recursos Recebidos por Associacao Desportiva                         ; 
        /*06*/ "evtAssocDespRep"  , // R-2040 – Recursos Repassados para Associacao Desportiva                       ; 
        /*07*/ "evtComProd"       , // R-2050 – Comercializacao da Producao por Produtor Rural PJ/Agroindustria      ; 
        /*08*/ "evtCPRB"          , // R-2060 – Contribuicao Previdenciaria sobre a Receita Bruta - CPRB             ; 
        /*09*/ "evtPgtosDivs"     , // R-2070 – Retencoes na Fonte - IR, CSLL, Cofins, PIS/PASEP                     ; 
        /*10*/ "evtReabreEvPer"   , // R-2098 – Reabertura dos Eventos Periodicos                                    ; 
        /*11*/ "evtFechaEvPer"    , // R-2099 – Fechamento dos Eventos Periodicos                                    ; 
        /*12*/ "evtEspDesportivo" , // R-3010 – Receita de Espetaculo Desportivo                                     ; 
        /*13*/ "evtTotal"         , // R-5001 – Informacoes de bases e tributos por evento                           ; 
        /*14*/ "evtTotalContrib"  , // R-5011 – Informacoes de bases e tributos consolidadas por periodo de apuracao ; 
        /*15*/ "evtExclusao"      , // R-9000 – Exclusao de Eventos                                                  ; 
    );
    
    // Tag container dos eventos
    $container = 'loteEventos';
    
    /**
     * Faz uma requisicao para o servidor do azure para assinar o evento
     * o servidore hospeda um opensource que roda apenas em php7,
     * Nosso php aqui eh o 5, por esse motivo esta em um servidor fora
     * 
     * @nome [string] nome do evento
     * @xml  [string] xml do evento para assinar
     * 
     */
    function assinarEventoAzure($nome, $xml)
    {
        global $config ; // 
        global $cnpj   ; // 
        
        // URL do servidor 
        $server = "https://sd-assina-reinf.azurewebsites.net/";
        // caminho do arquivo
        $url  = "{$server}/examples/Fake/v1_03_02/sd.php"; // ! NUNCA ALTERAR A VERSAO, MANTENHA a v1_03_02
        
        // pega as configs do cliente
        $confgCliente = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
        
        // senha do certificado
        $senha = $confgCliente['certificado']['senha'];
        
        // arquivo de certificado em base 64 para nao ter problemas na hora de ler no servidor
        $certificado = base64_encode(file_get_contents($confgCliente['certificado']['arquivo_certificado']));
        
        // mora os dados da comunicacao
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded", // header 
                'method'  => 'POST', // methodo post, como sera enviado senha e um arquivo
                'content' => http_build_query(array(  // formulario de dados para assinar
                    'senha'       => "$senha"       , // Senha do certificado
                    'certificado' => "$certificado" , // arquivo de certificado em base64
                    'nome'        => "$nome"        , // nome do evento
                    'xml'         => "$xml"         , // xml do evento
                ))
            )
        ));
        
        // faz a comunicacao
        $result = file_get_contents($url, false, $context);
        
        // remtorna o evento assinado
        return  $result;
    }
    
    
    // Carrega as configuracoes de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."reinf/";
    // Dados que sao passados pelo programa chamador (cobol)
    $cnpj               = $argv[1] ; 
    $arquivo_txt        = $argv[2] ; 
    $arquivo_txt_saida  = $argv[3] ; 
    $numero_ambiente    = (isset($argv[4])?$argv[4]:'2');
    $ambiente           = ($numero_ambiente == "1")?'producao':'homologacao';
    
    file_put_contents($arquivo_txt, 
        str_replace("\r", "", 
            file_get_contents($arquivo_txt)
        )
    );
    
    // Se nao for passado o parametro, buscar BR
    $estado              = (isset($argv[5])?$argv[5]:   'BR');
    $autorizadora        = (isset($argv[6])?$argv[6]:$estado);
    $testesAutomatizados = (isset($argv[7])?$argv[7]:    '2');
    
    // Cria o nome raiz do arquivo a ser manipulado
    $nome_raiz = end(explode("/",$arquivo_txt));
    
    $arquivo_xml = $temp.$nome_raiz.".xml";
    $arquivo_validacao = $temp.$nome_raiz.".validacao.xml";
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }
    
    /* Assinatura do XML */
    function assinaXml($arquivo, $cnpj, $tag_raiz, $tag2, $custonId)
    {
        $assinatura = new CAssinaturaDigital($arquivo,$cnpj,$criptografia="sha256");
        // Grava no arquivo se estiver tudo ok. Retorna o erro se der errado.
        if ($assinatura->assinarXml($tag_raiz,$tag2, $custonId)) {
            $assinatura->salvar();
        }else{
            $mensagem = "11|".$assinatura->getErro()."\nErro ao assinar o xml";
            flog("Erro ao assinar o xml");
            fdebug();
            exit(11);
        }
    }
    
    /* Validacao do XML */
    function validaEventoXSD($eventoXml, $eventoNome, $Id)
    {
        global              $cnpj ; 
        global             $dados ; 
        global            $config ; 
        global  $array_webservice ; 
        global $arquivo_txt_saida ; 
        
        $xsd = ""
            .$config['servicos']
            ."reinf/schemas/"
            .$dados['reinf']['pacote']
            ."/"
            .$array_webservice->schema->{$eventoNome}
        ;
        
        $arquivo_xml = $config['temp']."reinf/"
            ."REINF-VALIDACA_"
            ."EVENTO-{$eventoNome}_"
            ."RAND-".(round(microtime(true) * 1000)).".xml"
        ;
        
        file_put_contents($arquivo_xml, $eventoXml);
        
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        
        if(!$validacao->validar())
        {
            $motivo = $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            
            $retorno = array();
            
            $retorno[]= "$Id"                                ; //            @id | WR-02-ID-EVENTO
            $retorno[]= "1"                                  ; //         tpInsc | WR-02-TIPO-INSCRICAO
            $retorno[]= substr($cnpj, 0, 8)                  ; //         nrInsc | WR-02-RAIZ-CNPJ
            $retorno[]= "-1"                                 ; //      cdRetorno | WR-02-COD-RETORNO
            $retorno[]= "Erro de schema"                     ; //    descRetorno | WR-02-DESC-RETORNO
            $retorno[]= "1"                                  ; //        tpOcorr | WR-02-TIPO-OCORRENCIA
            $retorno[]= " "                                  ; // localErroAviso | WR-02-CAMPO-ORIGINAL
            $retorno[]= "-1"                                 ; //        codResp | WR-02-COD-ERRO
            $retorno[]= "$motivo"                            ; //        dscResp | WR-02-DESC-ERRO
            $retorno[]= date('Y-m-d\Th:i:s.u-02:00', time()) ; //      dhProcess | WR-02-DATA-HORA
            $retorno[]= "0000"                               ; //           tpEv | WR-02-TIPO-EVENTO
            $retorno[]= "$Id"                                ; //           idEv | WR-02-ID-EVENTO2
            $retorno[]= substr("$Id", -10);                  ; //     nrProtEntr | WR-02-NRO-RECEBTO
            $retorno[]= ""                                   ; //   nrRecArqBase | WR-02-RECIBO 
            
            
            $retorno = implode ('|', $retorno) ; 
            
            file_put_contents($arquivo_txt_saida, " | |0|SUCESSO|\n$retorno" ) ; 
            exit();
        }
    }
    
    
    cria_diretorios($temp);
    
    // Converte o TXT para XML
    $retornoConversor = txt2xml($arquivo_txt,$arquivo_xml,'REINF', true);
    
    // Se houer algum erro, cria o retorno para o cobol informado o erro e sai do PHP
    if ($retornoConversor !== true)
    {
        // Varios dados estao vazios pois eles vem do XML e do sefaz, em caso de erro, o XML nao eh criado, e nem eh enviado ao sefaz
        $retorno   =             array() ;
        $retorno[] =                  "" ; // WE-RET-CNPJ              
        $retorno[] =                  "" ; // WE-RET-RECIBO            
        $retorno[] =                  "" ; // WE-RET-CHAVE-NFE         
        $retorno[] =                "-1" ; // WE-RET-STATUS            
        $retorno[] = "$retornoConversor" ; // WE-RET-DESCRICAO         
        $retorno[] =                  "" ; // WE-RET-DATA-HORA-SEFAZ   
        $retorno[] =                  "" ; // WE-RET-QRCODE            
        $retorno[] =                  "" ; // WE-RET-XML-INTEGRAL      
        
        $retorno = implode('|', $retorno);
        
        // print_r("\n$retorno\n"); exit();76496199000152
        
        file_put_contents($arquivo_txt_saida, $retorno);
        
        exit();
    } 

    // Carrega o xml e busca dados
    $conteudo_xml     = simplexml_load_file($arquivo_xml); // ??
    // $ambiente         = 'producao';
    
    $dados = parse_ini_file($config['dados']."/config_cliente/$cnpj.ini",true) ; 
    
    $dados['reinf']                              = array()                                          ; 
    $dados['reinf'][                   'versao'] = $REINF_versao                                    ; 
    $dados['reinf'][                   'pacote'] = "1_04_00"                                        ; 
    $dados['reinf'][              'versao_soap'] = "2"                                              ; 
    $dados['reinf'][              'dir_retorno'] = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/reinf/" ; 
    $dados['reinf'][          'dir_saida_cobol'] = "/user/nfe/$cnpj/CaixaSaida/Sefaz/reinf/"        ; 
    $dados['reinf']['dir_retorno_consulta_lote'] = "/user/nfe/$cnpj/CaixaSaida/Sefaz/reinf/"        ; 
    $dados['reinf'][        'envioLoteSincrono'] = "0"                                              ; 
    $dados['reinf'][                'temp_nota'] = "/user/nfe/$cnpj/CaixaSaida/Temporario/"         ; 
    
    $dados_ws         = new BuscaWebService($autorizadora,'reinf',$ambiente);
    $array_webservice = $dados_ws->buscarServico("envioLote",$dados['reinf']['versao']);
    
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    $tmp_dados=file_get_contents($arquivo_xml);
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $tmp_dados
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    file_put_contents($arquivo_xml, $template_soap);
    // para validacao
    
    
    $busca=array();
    $troca=array();
    $remov=array();
    
    $busca[]="soap12"   ;
    $troca[]="soapenv"  ;
    
    $busca[]="<Reinf>"  ;
    $troca[]="<Reinf xmlns='{$array_webservice->namespace}'>";
    
    $busca[]="http://www.w3.org/2003/05/soap-envelope";
    $troca[]='http://schemas.xmlsoap.org/soap/envelope/" xmlns:sped="http://sped.fazenda.gov.br/';
    
    $busca[]="<soapenv:Body>"  ; 
    $troca[]="<soapenv:Body><sped:ReceberLoteEventos><sped:loteEventos>"     ; 
    
    $busca[]="</soapenv:Body>" ; 
    $troca[]="</sped:loteEventos></sped:ReceberLoteEventos></soapenv:Body>"  ; 
    
    $remov[]="\n";
    $remov[]="\t";
    $remov[]="\r";
    $remov[]='xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $remov[]='xmlns:xsd="http://www.w3.org/2001/XMLSchema"';
    
    $validacao = file_get_contents($arquivo_xml);
    
    $validacao = preg_replace('/(\>)\s*(\<)/m', '$1$2', $validacao);
    $validacao = str_replace($busca, $troca, $validacao ) ; 
    $validacao = str_replace($remov, ""    , $validacao ) ; 
    
    // para cada corte de evento, sera assinado por essa funcao
    function assinarEvento($eventoNome, $eventoXml, $Id)
    {
        global $array_webservice,  $cnpj, $config;
        
        $namespaces = $array_webservice->namespaces->{$eventoNome};
        
        $eventoXml = ""
            ."<Reinf xmlns=\"$namespaces\">"
                ."$eventoXml"
            ."</Reinf>"
        ;
        
        $eventoXml = assinarEventoAzure($eventoNome, $eventoXml);
        
        validaEventoXSD($eventoXml, $eventoNome, $Id);
        
        $eventoXml = ""
            ."<evento id=\"$Id\">"
                ."$eventoXml"
            ."</evento>"
        ;
        
        $eventoXml = str_replace("\n", "", $eventoXml ) ; 
        
        return $eventoXml;
    }
    
    $validacao = f_cortar($eventos, $container, $validacao, "assinarEvento"); 
    
    file_put_contents($arquivo_xml, $validacao);
    
    if ($testesAutomatizados == '1'){
        exit();
    }
    
    $soap = new SoapWebService($arquivo_xml, $cnpj, $array_webservice);
    
    $soap->array_dados_cliente['curl']["sslversion"] = '6';
    
    
    $soap->curl_header = Array(
        'Content-Type: text/xml;charset=UTF-8',
        'SOAPAction: http://sped.fazenda.gov.br/RecepcaoLoteReinf/ReceberLoteEventos',
        "Content-length: ".strlen(file_get_contents($arquivo_xml)),
        "Cache-Control : no-cache", 
        "Pragma: no-cache"
    );
    
    // validarXMLCompleto();
    
    // error_log("XML: $arquivo_xml");
    // error_log("TXT: $arquivo_txt");
    
    // print(file_get_contents($arquivo_xml)); exit();
    
    // print(file_get_contents($arquivo_xml)); 
    /*
    echo "
        PASSSOUUUUUU
    \n";
    exit();
    // */
    $xml_retorno = $soap->comunicar();
    
    
    // print($xml_retorno); exit();
    
    if ($arquivo_txt_saida !== 'debug'){
        // file_put_contents($arquivo_txt_saida, $retorno);   
    } else {
        echo formatXML($xml_retorno);
    }
    
    /**
     * Retorna o id de um elemento
     */
    function getId($xml){
        $partes = array();
        //preg_match("\"[A-z]+[0-9]+\"", $xml, $partes) ; 
        preg_match("\"ID\w+\"", $xml, $partes) ; 
        return $partes[0];
    }
    
    /**
     * retorna o valor de uma tag
     */
    function corte($tag, $xml)
    {
        $xmlOriginal=$xml;
        
        $xml = preg_replace('/^.*\<'.$tag.'\>/m'  , '', $xml);
        $xml = preg_replace('/\<\/'.$tag.'\>.*$/m', '', $xml);
        
        return $xml!=$xmlOriginal?$xml:"";
    }
    
    /**
     * Spega o template e busca as tags do template no XML
     */
    function getFromXML($tempalate, $xml)
    {
        $retorno = array();
        $tags    = explode('|', $tempalate);
        
        foreach ($tags as $tag) {
            if ($tag=='@id') {
                $retorno[] = getId($xml);
            } else {
                $retorno[] = corte($tag, $xml);
            }
        }
        
        $retorno = implode('|', $retorno);
        
        return $retorno;
    }
    
    // ? DEBUGAR AQUI
    // echo $xml_retorno ; exit();
    
    // Retorno para o cobol
    $retornoCobol = array();
    
    // separa o xml entre lote e eventos
    $XML_partes    = explode('<retornoEventos>', $xml_retorno);
    
    // Dados do lote
    $dadosLote = $XML_partes[0];
    $retornoCobol[] = getFromXML($templateLote, $dadosLote);
    
    // Dados dos Eventos
    $eventos   = explode('</retornoEventos>', $XML_partes[1])[0];
    $eventos = explode('</evento>', $eventos); 
    
    // Percorre os eventos 
    foreach($eventos as $evento) {
        if (trim($evento) !== '') { // se nao for vasio (o ultimo vem vazio por conta do ripo de corte)
            $retornoCobol[] = getFromXML($templateEvento, $evento);
        }
    }
    
    $retornoCobol = implode("\n", $retornoCobol);
    
    file_put_contents($config['temp']."reinf/REINF-RETORNO-$cnpj-".(round(microtime(true) * 1000)).".xml", $xml_retorno);
    file_put_contents($arquivo_txt_saida, $retornoCobol);
    
