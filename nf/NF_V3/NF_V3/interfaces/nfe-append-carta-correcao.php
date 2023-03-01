<?php
    /*
        Programa:  nfe-append_carta-correcao.php
        Descricão: Programa responsavel por fazer o append do carta-correcao
        O programa faz a juncao de duas partes de xml para realizar a criacao do arquivo de retorno.
        As duas partes estao dentro do NF_V3_dados/temp/nfe, gravados pelo nfe-carta-correcao.php
        Autor:     Fernando H. Crozetta (02/03/2018)
        Modo de uso:
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-append-carta-correcao.php <cnpj> <arquivoSaida> <nota> <ambiente> <uf> <autorizadora>
    */
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/codigosUf.php"); //Converte códigos UF para numero e vice versa

    $cnpj = $argv[1]; 
    $xml_saida = $argv[2]; 
    $chave = $argv[3];
    $ambiente_entrada=$argv[4]; 
    $uf=$argv[5]; 
    $autorizadora = (isset($argv[6])?$argv[6]:$uf);
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."nfe/";

    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    // main
    cria_diretorios($temp);
    $diretorio_saida = dirname($xml_saida);
    cria_diretorios($diretorio_saida);

    $dados_ws = new BuscaWebService($autorizadora,'nfe',$ambiente); 
    $array_webservice = $dados_ws->buscarServico("append_evento",$dados['nfe']['versao']);

    $parte1 = file_get_contents($temp."/".$chave.".carta_correcao_parte1.xml");
    $parte2 = file_get_contents($temp."/".$chave.".carta_correcao_parte2.xml");

    $conteudo = $array_webservice->tag_inicio . $parte1 .$array_webservice->tag_retEvento_inicio . $parte2 . $array_webservice->tag_retEvento_fim . $array_webservice->tag_fim;
    $conteudo = preg_replace("/\n/","",$conteudo);
    file_put_contents($xml_saida,$conteudo);
