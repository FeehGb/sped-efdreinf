<?php 
    /*
        Programa:  mdfe-status.php
        Descricão: Programa responsavel por verificar o status do serviço
        Autor:     Fernando H. Crozetta (19/06/2017)
        Modo de uso: 
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-status.php <cnpj> <arquivo_saida> <ambeinte> <uf>
    */ 
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../classes/codigosUf.php"); //Converte códigos UF para numero e vice versa

    // Dados que são passados pelo programa chamador (cobol)    
    $cnpj = $argv[1];
    $arquivo_saida = $argv[2];
    $ambiente_entrada = (isset($argv[3])?$argv[3]:'2');
    $uf_ws = (isset($argv[4])?$argv[4]:'RS');//backup

    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."mdfe/";
    $data = date('ymd-His');
    $arquivo_xml=$temp.$data.'-status.xml';

    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    // main
    cria_diretorios($temp);

    // $dados_ws = new BuscaWebService($uf,'mdfe',$ambiente);
    $dados_ws = new BuscaWebService($uf_ws,'mdfe',$ambiente); //Só existe RS
    $array_webservice = $dados_ws->buscarServico("status",$dados['mdfe']['versao']);
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);

    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    $converteUf = new CodigosUf();
    $cUf = $converteUf->paraNum($uf_ws);



    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $cUf,
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    file_put_contents($arquivo_xml, $template_soap);

    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();

    // print_r($xml_retorno);

    // Formata o retorno para um txt
    $retorno = exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php dhRecbto '".$xml_retorno."'");
	$retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php tMed '".$xml_retorno."'");
    $retorno .= "|";
    if (strpos($xml_retorno, "xObs") !== false ) {
        $retorno .= exec("php ../ferramentas/f.texto.tag.php xObs '".$xml_retorno."'");
    }
    $retorno .= "|";
    $retorno .= $xml_retorno;
    
    
    // 81067860000144
    // 09041943000137
    // 82574062000252

    
    // grava o retorno do status mdfe
    // print "\n\n".print_r($retorno, true)."\n\n\n";exit();
    
    file_put_contents($dados['mdfe']['dir_saida_cobol'].$arquivo_saida,$retorno);

 ?>
