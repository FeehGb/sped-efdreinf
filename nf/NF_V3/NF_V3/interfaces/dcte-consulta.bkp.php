<?php 
    // /var/www/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php
    // /var/www/nf/NF_V3/NF_V3/interfaces/dcte-consulta.php
    /*
        Programa:  dcte-consulta.php
        Descricão: 
        Autor:     
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
    chdir(__DIR__);
    require_once("../funcoes/flog.php"               ) ; // para gravar log
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap
    require_once("../ferramentas/formatXML.php"      ) ; //   
    require_once("../funcoes/httpStatus.php"         ) ; // .
    
    require_once("../classes/class.loadConfigs.inc"   ) ; 
    /*
    $class_ws = new loadConfigs("dcte", 'BR');
    $wsConfigs = $class_ws->getJson();
    print_r($wsConfigs);
    exit();
    */
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    
    $temp=$config['temp']."dcte/";
    // Dados que são passados pelo programa chamador (cobol)
    
    // if (!file_exists($argv[1])){
    //     echo "\nARQUIVO NAO EXISTE\n";
    //     exit();
    // }
    
    $arquivo_txt   = explode("|", file_get_contents($argv[1]));
    $retorno_cobol = $argv[2];
    $estado = (isset($argv[3]) ? $argv[3]:'BR');
    
    $debug = false; 
    if ($retorno_cobol == 'debug'){
        $debug = true ;
    }
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }
    
    // main
    cria_diretorios($temp);
    
    if ($debug){
        $cnpj          = "72071541000200";
        $cpf           = "0";
        $uf            = "BR";
        $tipo_ambiente = "1";
        $ultimo_nsu    = "000000000000000";
        $seq_nsu       = "000000000000000";
    } else {
        $cnpj          = trim($arquivo_txt[0]);
        $cpf           = trim($arquivo_txt[1]);
        $uf            = trim($arquivo_txt[2]);
        $tipo_ambiente = trim($arquivo_txt[3]);
        $ultimo_nsu    = trim($arquivo_txt[4]);
        $seq_nsu       = trim($arquivo_txt[5]);
    }
    // 
    // 
    // echo ">>>cnpj:$cnpj<<<<\n";
    // echo ">>>cpf:$cpf<<<<\n";
    // echo ">>>uf:$uf<<<<\n";
    // echo ">>>tipo_ambiente:$tipo_ambiente<<<<\n";
    // echo ">>>ultimo_nsu:$ultimo_nsu<<<<\n";
    // echo ">>>seq_nsu:$seq_nsu<<<<\n";
    // 
    // $debug = true;
    
    
    $chaveNFe      = str_replace("\n", "", trim($arquivo_txt[6]));
    
    $ambiente = ($tipo_ambiente == "1" ? "producao" : "homologacao");
    
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    
    // [22/06/2017] - Fernando H. Crozetta: cria arquivos pem, se for necessario
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    $dados_ws = new BuscaWebService($estado,'dcte',$ambiente);
    $array_webservice = $dados_ws->buscarServico("consulta",$dados['dcte']['versao']);
    //  echo ">\n";
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        '<soap12:Header>ALTERAR_TAG_CABECALHO</soap12:Header>' => "",
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_DADOS_URL'=>$array_webservice->url,
        'ALTERAR_TIPO_AMBIENTE'=> $tipo_ambiente,
        'ALTERAR_CUF_AUTOR'=> "41",
        'ALTERAR_CNPJ'=> $cnpj,
        'ALTERAR_CPF'=> "",
        'ALTERAR_ULT_NSU'=> $ultimo_nsu,
        'ALTERAR_NSU'=> "",
        'ALTERAR_CH_NFE'=> "",
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
        '<?xml version="1.0" encoding="utf-8"?>' => '',
        '<?xml version="1.0" encoding="utf-8"?>' => '',
        '<?xml version="1.0"?>' => '',
        '<?xml version="1.0"?>' => '',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    
    $arquivo_xml = $dados['dcte']['dir_retorno']."dcte-" . date("YmdHms") . ".xml";
    file_put_contents($arquivo_xml, $template_soap);
    
    #echo formatXML($template_soap);
    #exit();
    $soap = new SoapWebService($arquivo_xml, $cnpj, $array_webservice);
    
    // Passamos fixo pois o servidor nascional usa só versao 6
    $soap->array_dados_cliente['curl']["sslversion"] = "6" ; 
    $xml_webservice = $soap->comunicar( $debug ); 
    
    $time = time();
    $tempPath = "$temp$cnpj-$ultimo_nsu-$time.xml";
    
    if ($debug){
        echo formatXML($xml_webservice);
    }
    
    file_put_contents($tempPath, $xml_webservice);
    $JSON_dados_dcte = json_encode($dados['dcte']) ; 
    $chamadaPython = array(
        "python3"                          , 
        "./dcte-consulta_processamento.py" , 
        "'$cnpj'"                          , 
        "'$estado'"                        , 
        "'$tempPath'"                      , 
        "'$retorno_cobol'"                 , 
        "'$JSON_dados_dcte'"               , 
    );
    
    
    $python_retorno = shell_exec(implode(' ', $chamadaPython));
    
    if ($debug){
        print(">>$python_retorno<<");
    }
    #php dcte-consulta.php /user/nfe/10650820000182/CaixaEntrada/Processar/DCTE2020.03.06-16.00.02.txt saida.txt
    # cd /var/www/html/nf/NF_V3/NF_V3/interfaces/;php dcte-consulta.php /user/nfe/82231739000179/CaixaEntrada/Processar/DNFE_CTE.txt /user/nfe/82231739000179/CaixaSaida/Sefaz/RDNFE_CTE.txt