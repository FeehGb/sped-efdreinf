<?php
/*
	xml-txt.php
	Programa responsavel por montar o TXT da nota fiscal a partir do XML passado
 *      Chamada: php -q /var/www/html/nf/nfe/xml-txt.php <arqEntrada> <arqSaida>
	Guilherme Pinto
	10/08/2015
*/
        require_once("libs/UnConvertNFePHP.class.php");
    	//require_once("libs/UnConvertCTePHP.class.php");
	
        $arquivo = fLocalEntradaArquivo($argv[1], $argv[2]);
        
        $UnConvertNFePHP = new UnConvertNFePHP();
        $txtRetorno = $UnConvertNFePHP->nfexml2txt($arquivo);
        
        if(!file_put_contents($argv[2], $txtRetorno)){
            echo "Nao foi possivel criar arquivo de saida!";
            exit();
        }
	
        exit();
        
        function fLocalEntradaArquivo($pArquivoEntrada, $pArquivoSaida){
            $msg = "";
            if(!file_exists($pArquivoEntrada)){
                $msg = "O parametro de entrada informado nao existe!";
            }
            if(!is_file($pArquivoEntrada)){
                $msg = "O parametro de entrada nao e um arquivo!";
            }
            if(!is_readable($pArquivoEntrada)){
                $msg = "O arquivo informado nao pode ser lido, verifique as permissoes";
            }
            
            if($msg != ""){
                echo $msg;
                file_put_contents($pArquivoSaida, $msg);
                exit();
            }else{
                return $pArquivoEntrada;
            }
        }
?>