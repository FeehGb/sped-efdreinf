<?php 
    /*
        Programa:  nfe-inutilizacao.php
        Descricão: Programa responsavel por realizar o envio de nota para a sefaz
        Autor:     Fernando H. Crozetta (18/12/2017)
        Modo de uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-inutilizacao.php <cnpj> <caminho-arquivo_saida.xml> <ano> <serie> <nota_inicial> <nota_final> '<justificativa>' <caminho_contabil> <tipo_ambiente> <UF> <autorizadora>
    */
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); // Usado para retornar o código uf, baseado na sigla
    
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."nfe/";
    // Dados que são passados pelo programa chamador (cobol)
    $cnpj = $argv[1];
    $arquivo_txt_saida = $argv[2];
    $ano = $argv[3];
    $serie = $argv[4];
    $nota_inicial = $argv[5];
    $nota_final = $argv[6];
    $justificativa = $argv[7];
    $caminho_contabil = $argv[8];
    $ambiente_numerico = (isset($argv[9])?$argv[9]:2);
    $ambiente = ($ambiente_numerico == 1)?'producao':'homologacao';
    
    // Se não for passado o parametro, buscar estado RS
    $estado = (isset($argv[10])?$argv[10]:'PR');
    $autorizadora = (isset($argv[11])?$argv[11]:$estado);
    $converteUF = new CodigosUf();
    $uf = $converteUF->paraNum($estado);

    $arquivo_xml = $temp.$cnpj.".inutiliza.xml";
    $arquivo_validacao = $temp.$cnpj.".inutiliza.validacao.xml";

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
        if ($assinatura->assinarXml('infInut')) {
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
        $erro_validacao='';
        $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
        $xsd = $config['servicos']."/nfe/schemas/".$dados['nfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        if(!$validacao->validar()){
            $erro_validacao.= $validacao->getErros();
            $erro_validacao = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $erro_validacao)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$erro_validacao."|");
            exit();
        }
    }


    // main
    cria_diretorios($temp);
    cria_diretorios($caminho_contabil);
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $dados_ws = new BuscaWebService($autorizadora,'nfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("inutilizacao",$dados['nfe']['versao']);
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_CUF' => $uf,
        'ALTERAR_DADOS_URL'=>$array_webservice->url,
        'ALTERAR_DADOS_VERSAO_DADOS' => $array_webservice->versao,
        'ALTERAR_DADOS_VERSAO'=> $array_webservice->versao,
        'ALTERAR_ID'=>"ID".$uf.$ano.$cnpj."55".sprintf('%03d', $serie).sprintf('%09d', $nota_inicial).sprintf('%09d', $nota_final),
        'ALTERAR_ANO'=> $ano,
        'ALTERAR_CNPJ'=>$cnpj,
        'ALTERAR_SERIE'=>$serie,
        'ALTERAR_NF_INI'=>$nota_inicial,
        'ALTERAR_NF_FIN'=>$nota_final,
        'ALTERAR_JUSTIFICATIVA'=>$justificativa,
        'TAG VAZIA' =>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    
    file_put_contents($arquivo_xml, $template_soap);

    // para validação
    $validacao = file_get_contents($arquivo_xml);
    
    $validacao = preg_replace("/.*<inutNFe/"   , "<inutNFe"  , $validacao);
    $validacao = preg_replace("/<\/inutNFe>.*/", "</inutNFe>", $validacao);
    
    file_put_contents($arquivo_validacao,$validacao);
    
    // Assinar e validar xml
    assinaXml($arquivo_validacao,$cnpj);
    $validacao = file_get_contents($arquivo_validacao);
    
    $xmlFinal  = file_get_contents($arquivo_xml);
    
    $parte1 = explode('<inutNFe ' , $xmlFinal)[0];
    $parte2 = explode('</inutNFe>', $xmlFinal)[1];
    
    $xmlFinal = "$parte1$validacao$parte2";
    
    $xmlFinal = str_replace("\n", "", $xmlFinal);

    file_put_contents($arquivo_xml, $xmlFinal);
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice,$autorizadora);
    /*
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
    $xml_retorno = $soap->comunicar();
    $xml_retorno = str_replace("\n","",$xml_retorno);
    
    function getVlTag($tag){
        global $xml_retorno;
        return exec("php ../ferramentas/f.texto.tag.php $tag '$xml_retorno'");
    }
    
    // Montagem de Arquivo para contabilidade
    $xml_contabil = "<procInutNFe versao='".$array_webservice->versao."'>";
    $xml_contabil .= $validacao;
    $tmp = preg_replace("/.*<retInutNFe.*>/", "", $xml_retorno);
    $tmp = preg_replace("/<\/retInutNFe>.*/", "", $tmp);
    $tmp = preg_replace("/xmlns=http:\/\/www.portalfiscal.inf.br\/nfe\//", "xmlns='http://www.portalfiscal.inf.br/nfe'", $tmp);
    $tmp = preg_replace("/versao=".$array_webservice->versao."/", "versao='".$array_webservice->versao."'", $tmp);
    $xml_contabil .= $tmp;
    $xml_contabil .= "</procInutNFe>";
    $nProt = getVlTag("nProt");
    
    file_put_contents($caminho_contabil.$nProt."-procInutNFe.xml", $xml_contabil);

    $retorno = array();
    
    $retorno[] = getVlTag("cStat")    ; // WE-RET-STATUS            
    $retorno[] = getVlTag("xMotivo")  ; // WE-RET-DESCRICAO         
    $retorno[] = $nProt               ; // WE-RET-STATUS            
    $retorno[] = $xml_retorno         ; // WE-RET-XML-INTEGRAL      
    
    $retorno = implode('|', $retorno);
    
    // print_r("\n$retorno\n"); // exit();
    
    file_put_contents($arquivo_txt_saida, $retorno);
    
?>
