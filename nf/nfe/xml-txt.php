<?php
        /*
            xml-txt.php
            Programa responsavel por montar o TXT da nota fiscal a partir do XML passado
            * Chamada: php -q /var/www/html/nf/nfe/xml-txt.php <arqEntrada> <arqSaida>
            J. Eduardo Lino 
            13/07/2017
        */
        
        require_once("/var/www/html/nf/enfe/UnConvertNFePHP.class.php");
        //require_once("/var/www/html/nf/nfe/libs/UnConvertNFePHP.class.php");
        require_once("/var/www/html/nf/enfe/UnConvertCTePHP.class.php");
        
        $arquivo = fLocalEntradaArquivo($argv[1], $argv[2]);
        $conteudo_xml = file_get_contents($argv[1]);
        
        if (strpos($conteudo_xml, '<CTe') !== false) 
        {
            $UnConvertCTePHP = new UnConvertCTePHP();
            $txtRetorno = $UnConvertCTePHP->ctexml2txt($arquivo);
        }
        else
        {
            
            $UnConvertNFePHP = new UnConvertNFePHP();
            $txtRetorno = $UnConvertNFePHP->nfexml2txt($arquivo);
        }
        
        if(!file_put_contents($argv[2], $txtRetorno))
        {
            echo "\nNao foi possivel criar arquivo de saida!\n";
        }
        
        
        function fLocalEntradaArquivo($pArquivoEntrada, $pArquivoSaida){
            $msg = "";
            if(!file_exists($pArquivoEntrada)){
                $msg = "ERRO|O parametro de entrada informado nao existe!";
            }
            if(!is_file($pArquivoEntrada)){
                $msg = "ERRO|O parametro de entrada nao e um arquivo!";
            }
            if(!is_readable($pArquivoEntrada)){
                $msg = "ERRO|O arquivo informado nao pode ser lido, verifique as permissoes";
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