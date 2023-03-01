<?php
/*
	txt-xml.php
	Programa responsavel por montar o XML da nota fiscal a partir do TXT passado
 *      Chamada: php -q /var/www/html/nf/nfe/txt-xml.php <arqEntrada> <arqSaida>
	Guilherme Pinto
	24/09/2015
*/
	require_once("libs/ConvertNFePHP.class.php");
	
        //$arquivo = $argv[1] ; 
        $arquivo = fLocalEntradaArquivo($argv[1], $argv[2]);
        
        $ConvertNFePHP = new ConvertNFePHP();
        $xml = $ConvertNFePHP->nfetxt2xml($arquivo);
        
        if(!file_put_contents($argv[2], $txtRetorno)){
            echo "Nao foi possivel criar arquivo de saida!";
            exit();
        }
        
        exit();
        
        function fLocalEntradaArquivo($pArquivoEntrada, $pArquivoSaida){
            $msg = "";
            
            // chmod($fn, 0644);
            
            //*
            if(!file_exists($pArquivoEntrada)){
                $msg = "O parametro de entrada informado nao existe!";
            }
            if(!is_file($pArquivoEntrada)){
                $msg = "O parametro de entrada nao e um arquivo!";
            }
            if(!is_readable($pArquivoEntrada)){
                $msg = "O arquivo informado nao pode ser lido, verifique as permissoes";
            }
            // */
            if($msg != ""){
                echo $msg;
                /* error_log($msg); */
                file_put_contents($pArquivoSaida, $msg);
                exit();
            }else{
                return $pArquivoEntrada;
            }
        }
        
    
    
