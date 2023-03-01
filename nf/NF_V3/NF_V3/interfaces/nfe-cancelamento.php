<?php 
    /*
        Programa:  nfe-cancelamento.php
        Descricão: Programa responsavel por realizar o cancelamento de nota na a sefaz
        Autor:     Fernando H. Crozetta (21/02/2018)
        Modo de uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-cancelamento.php <cnpj> <protocolo> <chNfe> "<Justificativa>" <dhEvento> <caminho-arquivo_saida.xml>  <tipo_ambiente> <UF> <autorizadora>
    */
    
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = cnpj                        ; 
                argv[2] = protocolo                   ; 
                argv[3] = chave                       ; 
                argv[4] = justificativa               ; 
                argv[5] = dhEvento                    ; 
                argv[6] = arquivo_txt_saida           ; 
                argv[7] = ambiente                    ; 
                argv[8]*= estado            | PR      ; 
                argv[9]*= autorizadora      | argv[8] ; 
                
            
            SAIDA:
                cStat       
                xMotivo     
                nProt       
                xml_retorno 
                
            
        \n"; exit() ;
    }
    
    // echo date('Y-m-d H:i:s'); exit();
    
    // php -q /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-cancelamento.php 02898246000310 123180073481667 23181102898246000310550010000000431094727607 'SOLICITADO PELA BHS' 1 ~/saida.txt
    
    
    
    
    
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"              ); // para gravar log
    require_once("../funcoes/fdebug.php"            ); // Para realizar debug
    require_once("../funcoes/freplace.php"          ); // Replace de dados
    require_once("../funcoes/txt2xml.php"           ); // para converter txt para xml
    require_once("../funcoes/nfe_qrcode.php"        ); // para criar o qrcode
    require_once("../funcoes/cnpjCpfValidator.php"        ); // para validar cnpj ou cpf
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"         ); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"   ); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"    ); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"         ); // Usado para retornar o código uf, baseado na sigla
    
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."nfe/";
    
    // Dados que são passados pelo programa chamador (cobol)
    $cnpj              = $argv[1] ; 
    $protocolo         = $argv[2] ; 
    $chave             = $argv[3] ; 
    $justificativa     = $argv[4] ; 
    $dhEvento          = $argv[5] ; 
    // $dhEvento          = str_replace(' ', 'T', date('Y-m-d H:i:s')) . "-03:00"; // TODO
    // 2018-11-12T22:26:35-02:00
    
    $arquivo_txt_saida = $argv[6] ; 
    $ambiente          = $argv[7] ; 
    
    $estado = (isset($argv[8])?$argv[8]:'PR');
    $autorizadora = (isset($argv[9])?$argv[9]:$estado);
    
    $stringAmbiente = ($ambiente != "1")?'homologacao':'producao';
    $converteUF = new CodigosUf();
    $uf = $converteUF->paraNum($estado);
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
        if ($assinatura->assinarXml('infEvento',"evento")) {
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
        $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
        $xsd = $config['servicos']."/nfe/schemas/".$dados['nfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        if(!$validacao->validar()){
            $motivo.= $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }


    // main
    
    cria_diretorios($temp);
    
    // Carrega o xml e busca dados
    $dados            = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $dados_ws         = new BuscaWebService($autorizadora,'nfe',$stringAmbiente);
    $array_webservice = $dados_ws->buscarServico("cancelamento",$dados['nfe']['versao']);
    
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =  array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE'  => $array_webservice->namespace,
        'ALTERAR_DADOS_URL'        => $array_webservice->url,
        'ALTERAR_ID_LOTE'          => substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15),
        'ALTERAR_CNPJ'             => $cnpj,
        'ALTERAR_CHAVE_NFE'        => $chave,
        'ALTERAR_DATA_HORA_EVENTO' => $dhEvento,
        'ALTERAR_PROTOCOLO'        => $protocolo,
        'ALTERAR_JUSTIFICATIVA'    => $justificativa,
        'ALTERAR_ID_EVENTO'        => "ID110111" . $chave . "01",
        'ALTERAR_DADOS_UF'         => $uf,
        'TAG VAZIA'                => '',
        "\n"                       => '',
        "\r"                       => '',
        "\t"                       => '',
    );
    // ticket 94352	tratamento para CPF ---- espera que tenha 14 caracteres
    $isCPF = !valida_cnpj($cnpj) && valida_cpf($cnpj);
    if($isCPF){
        $array_substituicao['ALTERAR_CNPJ'] = substr( $cnpj, -11 );
        $array_substituicao['CNPJ'] = 'CPF';
    }
    
    
    $template_soap =  freplace($template_soap,$array_substituicao);
    
    // para validação
    $validacao = $template_soap;
    
    $validacao = preg_replace("/.*<envEvento/"   , "<envEvento"  , $validacao);
    $validacao = preg_replace("/<\/envEvento>.*/", "</envEvento>", $validacao);
    
    
    $arquivo_validacao = $temp.$chave."cancelamento.validacao.xml";
    $arquivo_xml = $temp.$chave."cancelamento.xml";
    file_put_contents($arquivo_validacao,$validacao);
    
    // Assinar e validar xml
    assinaXml($arquivo_validacao,$cnpj);
    
    $validacao = file_get_contents($arquivo_validacao);
    $xmlFinal  = $template_soap;
    
    $parte1 = explode('<envEvento ' , $xmlFinal)[0];
    $parte2 = explode('</envEvento>', $xmlFinal)[1];
    
    $xmlFinal = "$parte1$validacao$parte2";
    
    $xmlFinal = str_replace("\n", "", $xmlFinal);
    
    //Grava o arquivo na pasta de temporarios para realizar append posterior
    $t = preg_replace("/.*<evento/"   , "<evento"  , $validacao);
    $t = preg_replace("/<\/evento>.*/", "</evento>", $t);
    
    file_put_contents($temp.$chave.".cancelamento_parte1.xml",$t);
    
    file_put_contents($arquivo_xml, $xmlFinal);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice,$autorizadora);
    //! [03/05/2018] - Crozetta: Este if resolve uma divergencia no servidor do ceará, 
    //!                 que recebe e trata os dados de forma diferente
    
    
    //echo "\n\n".$array_webservice->namespace."/".$array_webservice->metodo."\n\n";
    /*
    if ($autorizadora == "CE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            'SOAPAction: "'.$array_webservice->namespace.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
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
            'Content-Type: application/soap+xml;charset=UTF-8;action="'.$array_webservice->namespace."wsdl/".$array_webservice->servico."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
        );
    }
    */
    // print( "\n" . print_r(file_get_contents($arquivo_xml), true) . "\n" ) ; exit() ; 
    
    $xml_retorno = $soap->comunicar();
    
    // print($xml_retorno); exit();
    
    
    $xml_retorno = str_replace("\n","",$xml_retorno);
    
    $retornoEvento = preg_replace("/.*<infEvento/", "<infEvento", $xml_retorno);
    $retornoEvento = preg_replace("/<\/infEvento>.*/", "</infEvento>", $retornoEvento);
    file_put_contents($temp.$chave.".cancelamento_parte2.xml",$retornoEvento);
    
    $retorno = array();
    
    $retorno[] = exec("php ../ferramentas/f.texto.tag.php cStat '".$retornoEvento."'");
    $retorno[] = exec("php ../ferramentas/f.texto.tag.php xMotivo '".$retornoEvento."'");
    $retorno[] = exec("php ../ferramentas/f.texto.tag.php nProt '".$retornoEvento."'");
    $retorno[] = $xml_retorno         ; // WE-RET-XML-INTEGRAL      
    
    $retorno = implode('|', $retorno);
    file_put_contents($arquivo_txt_saida, $retorno);
    
    
    
    
    
    
    
