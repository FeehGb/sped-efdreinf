<?php 
    /*
        Programa:  mdfe-nao_enc.php
        Descricão: Programa responsavel por verificar os documentos nao encerrados
        Autor:     Fernando H. Crozetta (19/06/2017)
        Modo de Uso: 
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-nao_enc.php <cnpj> <ambiente> <uf>
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
    $ambiente_entrada = (isset($argv[2])?$argv[2]:'2');
    $uf = $argv[3];
    // $uf_ws = (isset($argv[2])?$argv[2]:'RS');//backup
    $uf_ws = ('RS');//backup
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."mdfe/";
    $data = date('ymd-His');
    $arquivo_xml=$temp.$data.'-nao_enc.xml';

    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    // main
    cria_diretorios($temp);

    // $dados_ws = new BuscaWebService($uf,'mdfe',$ambiente);
    $dados_ws = new BuscaWebService($uf_ws,'mdfe',$ambiente); //Só existe RS
    $array_webservice = $dados_ws->buscarServico("nao_encerrados",$dados['mdfe']['versao']);

    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    $converteUf = new CodigosUf();
    $cUf = $converteUf->paraNum($uf);



    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $cUf,
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        'ALTERAR_CNPJ'=>$cnpj,
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    file_put_contents($arquivo_xml, $template_soap);

    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();

    // Formata o retorno para um txt
    $retorno  = "cStat::";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|xMotivo::";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    // TODO: Verificar quando deve buscar os dados
    // as linhas abaixo nem sempre existem.
    // $retorno .= "|xObs::";
    // $retorno .= exec("php ../ferramentas/f.texto.tag.php xObs '".$xml_retorno."'");

    // grava o retorno do status mdfe
    file_put_contents($dados['mdfe']['dir_retorno'].$data."-nao_enc-retorno.txt",$retorno);

 ?>