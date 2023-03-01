<?php 
    /*
        Programa:  mdfe-consulta.php
        Descricão: Programa responsavel por realizar a consulta de mdfe
        Autor:     Fernando H. Crozetta (30/05/2017)
        Modo de Uso: 
        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-consulta-nota.php <chave_mdfe> <ambiente> <uf>
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
    /*
    modelo da chave de mdfe
    uf  AAMM      CNPJ      modelo serie   mdfe      emissao  cod num  DV
    41  1703 09593770000240  58       0  00000000002    1     27057310  0
    41  1703 09593770000240  58       0  00000000002    1     42464187  9
    */
    $chave_mdfe = $argv[1]; //chave da nota
    $cnpj = substr($chave_mdfe, 6,14); 

    $ambiente_entrada = (isset($argv[2])?$argv[2]:'2');
    $uf = (substr($chave_mdfe, 0,2));
    $uf_ws = (isset($argv[3])?$argv[3]:'RS');

    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."mdfe/";

    $arquivo_xml=$temp.$chave_mdfe.'-consulta.xml';

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
        if ($assinatura->assinarXml('infMDFe')) {
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
        $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
        $xsd = $config['servicos']."/mdfe/schemas/".$dados['mdfe']['pacote']."/".$ws->schema;
        $validacao = new ValidaXml($arquivo_xml,$xsd);
        $motivo='';
        if(!$validacao->validar()){
            $motivo.= $validacao->getErros();
            $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
            flog("erro ao validar xml:\n\t".$cnpj." - ".$motivo."|");
            exit();
        }
    }

    // main
    cria_diretorios($temp);

    // $dados_ws = new BuscaWebService($uf,'mdfe',$ambiente);
    $dados_ws = new BuscaWebService($uf_ws,'mdfe',$ambiente); //Só existe RS
    $array_webservice = $dados_ws->buscarServico("consulta",$dados['mdfe']['versao']);

    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        'ALTERAR_TAG_CABECALHO' => $array_webservice->tag_cabecalho,
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);
    // $converteUf = new CodigosUf();
    // $cUf = $converteUf->paraNum($uf);



    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_TIPO_AMBIENTE'=>($ambiente == 'producao'?'1':'2'),
        'ALTERAR_CHAVE_MDFE'=>$chave_mdfe,
        "<?xml version='1.0' encoding='UTF-8'?>"=>'',
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    file_put_contents($arquivo_xml, $template_soap);

    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    $xml_retorno = $soap->comunicar();

    print_r($xml_retorno); exit();

    // Formata o retorno para um txt: código retorno|motivo
    $retorno .= exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    $retorno .= "|";
    $retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");

    // grava o retorno do envio de mdfe
    file_put_contents($dados['mdfe']['dir_retorno'].$chave_mdfe."-consulta-retorno.txt",$retorno);

 ?>