
<?php 
    /*
        Programa:  nfse-consulta.php
        Descricão: Programa responsavel por realizar a consulta do nfse
        Autor:     Felipe Basilio (05/05/2020)
        Modo de uso: */
        // php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfse-envio.php 07854151903 4106902 saida.txt saida.txt
        if (!isset($argv[1])){
            echo "
                ENTRADA:
                    argv[1] = cnpj   ; 
                    argv[2] = cidade ; 
                    argv[3] = arquivo_entrada;
                    argv[4] = arquivo_saida;
                    
                    
                arquivo_txt:
                    
                    
                SAIDA:
                
                \n"; exit() ;
        };
        
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
    require_once("../classes/class.loadConfigs.inc"   ) ; // 
    require_once("../classes/Cidades.php"); // Usado para retornar o código uf, baseado na sigla
    require_once("../ferramentas/formatXML.php" ) ; //
    
    
    $cnpj             = $argv[1];
    $codigo_tom       = $argv[2];
    $arquivo_entrada  = $argv[3];
    $arquivo_saida    = $argv[4];
    $ambiente         = (isset($argv[5]) ? $argv[5] : 2);
    $debug            = (isset($argv[6]) ? $argv[6] : false);
    $config            = parse_ini_file("../config/config.ini");
    $pathNFSe         = $config['servicos']."/nfse_v2";
    
    //$WS  = new loadConfigs($cnpj, "nfse");
    //$wsConfigs = $WS->getJson();
    
    // recupera o nome da cidade baseado no codigo tom
    $cidade = new Cidades();
    $nomeCidade = $cidade->getName($codigo_tom);
    
    $pathXmlFromCobol   = $pathNFSe . "/src/.ignore/NFSE.xml";
    $pythonArgs         = [$nomeCidade, 'envio',$pathXmlFromCobol];
    $fileToExec         = "python3 $pathNFSe/src/bootstrap_nfse.py ";
    
    /**
     * @params NomeCidade -> 
     * O python tem por finalidade ger o XML RPS -> para envio
     */
    $comandoPython = $fileToExec. implode(" ",$pythonArgs);
    
    // retorna o nome do arquivo gerado
    $return = shell_exec($comandoPython);
    
    
    echo $comandoPython;
    
    
    
    
    
    