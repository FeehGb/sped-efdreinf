<?php 
    
    
    
    // Chamada 
    // cd /var/www/html/nf/NF_V3/NF_V3/interfaces/; php dcte-envio-evento.php 72071541000200 debug 1 BR
    /*
        PR - OK
        AM - ??
        BA - ??
        CE - ??
        MG - ??
        MS - ??
        PE - ??
        RJ - ??
        SC - ??
        SP - ??
    */
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = arquivo_txt   ; 
                argv[2] = retorno_cobol ; 
                argv[3]*= estado      | BR; 
                
            arquivo_txt:
                ARQUIVO[0] =          cnpj | 
                ARQUIVO[1] =           cpf | 
                ARQUIVO[2] =            uf | 
                ARQUIVO[3] = tipo_ambiente | 
                ARQUIVO[4] =    ultimo_nsu | 
                ARQUIVO[5] =       seq_nsu | 
                ARQUIVO[6] =      chaveNFe | 
                
            SAIDA:
                reNFe:
                    001|chave|nsu|emit_cpf_cnpj|emit_nome|ie|data_emissao|tipo_nota|valor_nf|digest_value|data_hora_recebimento|protocolo|situacao_nfe|
                resEvento:
                    002|chave|nsu|emit_cpf_cnpj|data_hora_evento|tp_evento|seq_evento|desc_evento|data_hora_recebimento|nProt|
                procEventoNFe:
                    002|nsu|chave|emit_cpf_cnpj|data_hora_evento|tp_evento|seq_evento|desc_evento|data_hora_recebimento|nProt|
        \n"; exit() ;
    }
    
    require_once("../funcoes/flog.php"                ) ; // para gravar log
    require_once("../funcoes/fdebug.php"              ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"            ) ; // Replace de dados
    require_once("../classes/CAssinaturaDigital.php"  ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"           ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"     ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"      ) ; // Usado para enviar envelope soap
    require_once("../ferramentas/formatXML.php"       ) ; // 
    require_once("../funcoes/httpStatus.php"          ) ; // 
    require_once("../classes/class.loadConfigs.inc"   ) ; // 
    require_once("../classes/codigosUf.php"           ) ; // Usado para retornar o código uf, baseado na sigla
    
    
    
    // $wsConfigs['v3_config']
    // Carrega as configurações de clientes e sistema
    // $config = parse_ini_file("../config/config.ini");
    
    
    // $temp=$config['temp']."dcte/";
    // Dados que são passados pelo programa chamador (cobol)
    //
    
    
    
    //     
    // echo ">>>";
    // echo file_get_contents($argv[1]);
    // echo "<<<";
    //     
    
    $cnpj = $argv[1];
    $retorno_cobol = $argv[2];
    // Se não for passado o parametro, buscar estado BR
    $nAmbiente    = (isset($argv[3]) ? $argv[3]:  '2' )     ; 
    $estado       = (isset($argv[4]) ? $argv[4]:  'BR')     ; 
    $autorizadora = (isset($argv[5]) ? $argv[5]:  $estado)  ; 
    
    //print($autorizadora);exit();
    $debug = false;
    if ($retorno_cobol == 'debug'){
        $debug = true ;
    }
    
    $converteUF = new CodigosUf();
    // print("\n\n>>>$estado<<<\n\n");
    $uf = $converteUF->paraNum($autorizadora);
    
    // cd /var/www/html/nf/NF_V3/NF_V3/interfaces/; php dcte-envio-evento.php 72071541000200 debug 2 PR
    $class_ws  = new loadConfigs($cnpj, "dcte", $estado);
    $wsConfigs = $class_ws->getJson();
    
    // Cria diretorios de trabalho
    $temp = $wsConfigs['cli_config']['dcte']['dir_retorno'] ; 
    function cria_diretorios($dir) { exec('php ../ferramentas/cria_diretorios.php '.$dir); }
    cria_diretorios($temp);
    
    $ambiente = ($nAmbiente == "1" ? "producao" : "homologacao");
    
    // $wsConfigs['cli_config']
    // $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    
    exec("php ../ferramentas/cria_pem.php ".
        $wsConfigs['cli_config']['certificado']['arquivo_certificado']." ".
        $wsConfigs['cli_config']['certificado']['senha']
    );
    
    
    // $dados_ws = new BuscaWebService($estado,'dcte',$ambiente);
    // $array_webservice = $dados_ws->buscarServico("evento", $dados['dcte']['versao']);
    //  echo ">\n";
    
    // montagem de arquivo soap para envio
    
    
    $array_webservice = (object) $wsConfigs['ws_config']['webservices']['CteRecepcaoEvento'];
    $path_arquivo_xml = $wsConfigs['cli_config']['dcte']['dir_retorno']."dcte-evento-" . date("YmdHms") . ".xml";
    $use_cter = $wsConfigs["ws_config"]["extra"]["use_cter"] ;
    
    
    // print(formatXML(file_get_contents($path_arquivo_xml))); exit(); 
    
    $xml_envio = $array_webservice->tag_corpo ;
    
    
    
    
    
    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        // 'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_DADOS_UF'  => $uf , 
        'ALTERAR_CUF_AUTOR' => $uf , 
        // 'ALTERAR_DADOS_URL'=>$array_webservice->url,
        // 'ALTERAR_DADOS_VERSAO_DADOS' => $array_webservice->versao,
        'ALTERAR_TIPO_AMBIENTE'=> $nAmbiente,
        // 'ALTERAR_CUF_AUTOR'=> "41",
        'ALTERAR_CNPJ'=> $cnpj,
        'ALTERAR_CHAVE'=> "41170100000000000191570010000000011065100453",
        'ALTERAR_DATA' => '2017-01-26T22:14:29-02:00',
        'ALTERAR_DESCEVENTO' => 'Prestacao do Servico em Desacordo',
        'ALTERAR_INDDESACORDOOPER' => '1',
        'ALTERAR_XOBS' => 'CT-e emitido com dados incorretos',
        
        
        // 'ALTERAR_CPF'=> "",
        // 'ALTERAR_ULT_NSU'=> $ultimo_nsu,
        // 'ALTERAR_NSU'=> "",
        // 'ALTERAR_CH_NFE'=> "",
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
        '<?xml version="1.0" encoding="utf-8"?>' => '',
        '<?xml version="1.0" encoding="utf-8"?>' => '',
        '<?xml version="1.0"?>' => '',
        '<?xml version="1.0"?>' => '', 
        
        // 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">' => 'xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap/">',
        // 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">' => 'xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap/">',
    );
    
    $xml_envio = freplace($xml_envio, $array_substituicao);
    
    
    
    
    // Assinatura
    file_put_contents($path_arquivo_xml, $xml_envio);
    $assinatura = new CAssinaturaDigital($path_arquivo_xml, $cnpj);
    $assinatura->assinarXml('infEvento', 'eventoCTe');
    $assinatura->salvar();
    $xml_envio = file_get_contents($path_arquivo_xml);
    
    
    $template_soap      = file_get_contents($wsConfigs['v3_config']['servicos']."/template_soap.xml");
    $template_cabecalho = "<cteCabecMsg xmlns=\"http://www.portalfiscal.inf.br/cte\"><cUF>$uf</cUF><versaoDados>3.00</versaoDados></cteCabecMsg>" ;
    
    if ( $use_cter ) {
        
        $template_soap      = "<soap12:Envelope xmlns:soap=\"http://www.w3.org/2003/05/soap-envelope\" xmlns:cter=\"http://www.portalfiscal.inf.br/cte/wsdl/CteRecepcaoEvento\"><soap12:Header>ALTERAR_TAG_CABECALHO</soap12:Header><soap12:Body>ALTERAR_TAG_CORPO</soap12:Body></soap12:Envelope>";
        $template_cabecalho = "<cter:cteCabecMsg xmlns=\"http://www.portalfiscal.inf.br/cte\"><cter:versaoDados>3.00</cter:versaoDados><cter:cUF>$uf</cter:cUF></cter:cteCabecMsg>";
        
    }
    
    
    //print($template_cabecalho);exit();
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $template_cabecalho ,//'ALTERAR_TAG_CABECALHO' => "<cteCabecMsg xmlns=\"http://www.portalfiscal.inf.br/cte\"><cUF>$uf</cUF><versaoDados>3.00</versaoDados></cteCabecMsg>",
        'ALTERAR_TAG_CORPO' => $xml_envio,
        'cteDadosMsg' => $use_cter ? "cter:cteDadosMsg": "cteDadosMsg",
        'soap12' => 'soap',
        #'<cteDadosMsg>' => "",
        #'</cteDadosMsg>' =>"",
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
        // "cteCabecMsg"=>"cter:cteCabecMsg",
    );
    $xml_envio = freplace($template_soap, $array_tmp);
    
    
    
    // print($xml_envio); exit(); 
    
    if ($debug){ print( ">>>$xml_envio<<<"    ); } 
    if ($debug){ print( formatXML($xml_envio) ); } 
    // $path_arquivo_xml = $wsConfigs['cli_config']['dcte']['dir_retorno']."dcte-evento-" . date("YmdHms") . ".xml";
    //exit();
    file_put_contents($path_arquivo_xml, $xml_envio);
    
    $soap = new SoapWebService($path_arquivo_xml, $cnpj, $array_webservice, $estado);
    $soap->array_dados_cliente = (array) $wsConfigs['cli_config'];
    $xml_webservice = $soap->comunicar($debug);
    
    if ($debug){ print( formatXML($xml_webservice) ); } 
    
    exit();
    