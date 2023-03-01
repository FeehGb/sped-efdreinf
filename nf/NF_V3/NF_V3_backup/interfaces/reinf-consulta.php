<?php 
    /*
        Programa:  reinf-consulta.php
        Descricão: Programa responsavel por realizar a consulta do reinf
        Autor:     Fernando H. Crozetta (18/06/2018)
        Modo de uso: 
        
    */
    
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../funcoes/txt2xml.php"); // para converter txt para xml
    require_once("../funcoes/nfe_qrcode.php"); // para criar o qrcode
    require_once("../funcoes/corte.php"); // para criar o qrcode
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); // Usado para retornar o código uf, baseado na sigla
    require_once("../ferramentas/formatXML.php" ) ; //
    
    
    $REINF_versao = "1.04.00" ; 
    
    // template do retorno do lote
    $templateLote = '@id|IdTransmissor|cdStatus|descRetorno';
    // Tempalate do retorno do evento
    $templateEvento = '@id|tpInsc|nrInsc|cdRetorno|descRetorno|tpOcorr|localErroAviso|codResp|dscResp|dhProcess|tpEv|idEv|hash';
    
    
    
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."reinf/";
    // Dados que são passados pelo programa chamador (cobol)
    $cnpj              = $argv[1] ; 
    $tipoInscricao     = $argv[2] ; 
    $reciboFechamento  = $argv[3] ; 
    $arquivo_txt_saida = $argv[4] ; 
    $numero_ambiente   = (isset($argv[5])?$argv[5]:'2');
    $ambiente          = ($numero_ambiente == "1")?'producao':'homologacao';
    $cnpjRaiz          = substr($cnpj,0,8);
    // Se não for passado o parametro, buscar BR
    $estado              = (isset($argv[6])? $argv[6] :    'BR' ) ; 
    $autorizadora        = (isset($argv[7])? $argv[7] : $estado ) ; 
    $testesAutomatizados = (isset($argv[8])? $argv[8] :     '2' ) ; 
    
    // Cria o nome raiz do arquivo a ser manipulado
    $nome_raiz = "consulta";
    $arquivo_xml = $temp.$nome_raiz.".xml";
    $arquivo_validacao = $temp.$nome_raiz.".validacao.xml";
    
    
    $debug = false ;  
    if ($arquivo_txt_saida === "debug"){
        $debug = true ; 
    }
    
    
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

    cria_diretorios($temp);

    // Carrega o xml e busca dados
    $dados            = parse_ini_file($config['dados']."/config_cliente/$cnpj.ini",true);
    
    $dados['reinf']                              = array()                                          ; 
    $dados['reinf'][                   'versao'] = $REINF_versao                                    ; 
    $dados['reinf'][                   'pacote'] = ""                                               ; 
    $dados['reinf'][              'versao_soap'] = "2"                                              ; 
    $dados['reinf'][              'dir_retorno'] = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/reinf/" ; 
    $dados['reinf'][          'dir_saida_cobol'] = "/user/nfe/$cnpj/CaixaSaida/Sefaz/reinf/"        ; 
    $dados['reinf']['dir_retorno_consulta_lote'] = "/user/nfe/$cnpj/CaixaSaida/Sefaz/reinf/"        ; 
    $dados['reinf'][        'envioLoteSincrono'] = "0"                                              ; 
    $dados['reinf'][                'temp_nota'] = "/user/nfe/$cnpj/CaixaSaida/Temporario/"         ; 
    
    $dados_ws         = new BuscaWebService($autorizadora,'reinf',$ambiente);
    $array_webservice = $dados_ws->buscarServico("consulta",$dados['reinf']['versao']);
    
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    $tmp_dados=file_get_contents($arquivo_xml);
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo,
    );
    $template_soap = freplace($template_soap,$array_tmp);
    $array_tmp =array(
        'ALTERAR_TP_INSC' => $tipoInscricao,
        'ALTERAR_NR_INSC' => $cnpjRaiz,
        'ALTERAR_NR_RECIBO' => $reciboFechamento
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    
    file_put_contents($arquivo_xml, $template_soap);
    // para validação
    
    
    $busca=array();
    $troca=array();
    $remov=array();
    
    $busca[]="soap12"   ;
    $troca[]="soapenv"  ;
    
    $busca[]="<Reinf>"  ;
    // $troca[]="<Reinf xmlns='http://www.reinf.esocial.gov.br/schemas/RecepcaoLoteReinf/v1_03_02'>";
    $troca[]="<Reinf xmlns='{$array_webservice->namespace}'>";
    
    $busca[]="http://www.w3.org/2003/05/soap-envelope";
    $troca[]='http://schemas.xmlsoap.org/soap/envelope/" xmlns:sped="http://sped.fazenda.gov.br/';
    
    $remov[]="\n";
    $remov[]="\t";
    $remov[]="\r";
    $remov[]='xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
    $remov[]='xmlns:xsd="http://www.w3.org/2001/XMLSchema"';
    
    
    $validacao = file_get_contents($arquivo_xml);
    
    $validacao = preg_replace('/(\>)\s*(\<)/m', '$1$2', $validacao);
    $validacao = str_replace($busca, $troca, $validacao ) ; 
    $validacao = str_replace($remov, ""    , $validacao ) ; 
    
    
    file_put_contents($arquivo_xml, $validacao);
    
    
    if ($testesAutomatizados == '1'){
        exit();
    }
    
    if ($debug){
        echo formatXML(file_get_contents($arquivo_xml)); 
    }
    
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    
    $soap->array_dados_cliente['curl']["sslversion"] = '6';
    
    
    $soap->curl_header = Array(
        'Content-Type: text/xml;charset=UTF-8',
        'SOAPAction: http://sped.fazenda.gov.br/ConsultasReinf/ConsultaInformacoesConsolidadas',
        "Content-length: ".strlen(file_get_contents($arquivo_xml)),
        "Cache-Control : no-cache", 
        "Pragma: no-cache"
    ); 
    $xml_retorno = $soap->comunicar();
    
    
    if ($debug){
        echo formatXML($xml_retorno);
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
    
    
    $cdRetorno   = corte("cdRetorno"   ,$xml_retorno);
    $descRetorno = corte("descRetorno" ,$xml_retorno);
    $codResp     = corte("codResp"     ,$xml_retorno);
    $dscResp     = corte("dscResp"     ,$xml_retorno);
    
    $retornoCobol = $cdRetorno."|".$descRetorno."|".$codResp."|".$dscResp;
    if (!$debug){
        file_put_contents($config['temp']."reinf/REINF-CONSULTA-RETORNO-$cnpj-".(round(microtime(true) * 1000)).".xml", $xml_retorno);
        file_put_contents($arquivo_txt_saida, $retornoCobol);
    } else {
        echo "$retornoCobol\n\n";
    }
    
    
