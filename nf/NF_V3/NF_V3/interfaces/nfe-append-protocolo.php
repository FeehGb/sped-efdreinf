<?php
    /*
        Programa:  nfe-consulta-lote.php
        Descricão: Programa responsavel por fazer o append do protocolo no xml
        Autor:     Fernando H. Crozetta (02/02/2018)
        Modo de uso:
(REVER ESTE COMANDO)        	php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-append-protocolo.php <cnpj> <arquivoSaida> <recibo> <ambiente> <uf> <autorizadora>
    */
    
    if (!isset($argv[1])){
        echo "
            argv[1] = cnpj             = \$argv[1]                         ; 
            argv[2] = xml_entrada      = \$argv[2]                         ; 
            argv[3] = xml_saida        = \$argv[3]                         ; 
            argv[4] = protocolo        = \$argv[4]                         ; 
            argv[5] = ambiente_entrada = \$argv[5]                         ; 
            argv[6] = uf               = \$argv[6]                         ; 
            argv[7] = autorizadora     = (isset(\$argv[7])?\$argv[7]:\$uf) ; 
            
        \n"; exit() ;
    }
    
    chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa
    require_once("../funcoes/flog.php"               ) ; // para gravar log                                    // 
    require_once("../funcoes/fdebug.php"             ) ; // Para realizar debug                                // 
    require_once("../funcoes/freplace.php"           ) ; // Replace de dados                                   // 
    require_once("../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml                           // 
    require_once("../classes/validaXml.php"          ) ; // Usado para validar o xml                           // 
    require_once("../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService// 
    require_once("../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap                    // 
    require_once("../classes/codigosUf.php"          ) ; // Converte códigos UF para numero e vice versa      // 

    $cnpj             = $argv[1] ; 
    $xml_entrada      = $argv[2] ; 
    $xml_saida        = $argv[3] ; 
    $protocolo        = $argv[4] ; 
    $ambiente_entrada = $argv[5] ; 
    $uf               = $argv[6] ; 
    $autorizadora     = ( isset( $argv[7] ) ? $argv[7] : $uf ) ; 
    
    
    $ambiente = ($ambiente_entrada == "1")?"producao":"homologacao";

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    $dados = parse_ini_file($config['dados']."config_cliente/".$cnpj.".ini",true);
    $temp=$config['temp']."nfe/";

    // Cria diretorios de trabalho
    function cria_diretorios($dir) { 
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    //$diretorio_entrada = dirname($xml_entrada);
    $diretorio_saida = dirname($xml_saida);
    cria_diretorios($diretorio_saida);

    // main
    cria_diretorios($temp);

    $dados_ws = new BuscaWebService($autorizadora,'nfe',$ambiente); 
    $array_webservice = $dados_ws->buscarServico("append_protocolo",$dados['nfe']['versao']);

    $conteudo = file_get_contents($xml_entrada);
    $conteudo = preg_replace("/<\?xml.*\?>/","",$conteudo);
    $conteudo = $array_webservice->tag_inicio . $conteudo . $protocolo . $array_webservice->tag_final;
    $conteudo = preg_replace("/\n/","",$conteudo);
    $conteudo = preg_replace("/versao=".$array_webservice->versao."/", "versao='".$array_webservice->versao."'", $conteudo);
    
    
    
    if (preg_match("/\'\'\"/",$conteudo,$x) == 1) {
        $remover    = "''\"" ; 
        $substituir = "\""   ;
        str_replace($remover, $substituir, $conteudo );
    }
    
    // Criado para PE, onde o retorno vem sem aspas em atributo
    $x;
    if (preg_match("/Id=NFe\d+/",$conteudo,$x) == 1) {
        $tmpId = $x[0];
        $tmpId = preg_replace("/Id=/","",$tmpId);
        $conteudo = preg_replace("/Id=".$tmpId.">/","Id='".$tmpId."'>",$conteudo);
    }
    
    $x;
    if (preg_match("/Id=ID\d+/",$conteudo,$x) == 1) {
        $tmpId = $x[0];
        $tmpId = preg_replace("/Id=/","",$tmpId);
        $conteudo = preg_replace("/Id=".$tmpId.">/","Id='".$tmpId."'>",$conteudo);
    }
    
    // para a bahia ; 
    if (strpos($conteudo, '<infProt xmlns=http://www.portalfiscal.inf.br/nfe Id=')) { 
        $conteudo = str_replace('<infProt xmlns=http://www.portalfiscal.inf.br/nfe Id=' , '<infProt xmlns="http://www.portalfiscal.inf.br/nfe" Id=', $conteudo);
    }
    
    //print(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>".$conteudo."<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");exit();
    file_put_contents($xml_saida,$conteudo);
    
    
    
    