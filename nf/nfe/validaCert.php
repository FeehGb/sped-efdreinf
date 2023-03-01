<?php
/*
	validCert.php
	Programa responsavel por consultar a data que expira o certificado digital
	Programa chamado pelo COBOL
	Guilherme Pinto
	18/05/2015
*/
	require_once("libs/ToolsNFePHP.class.php");
    
    $pCnpj = $argv[1];
    $pArqSaida = $argv[2];
    $aRetorno="";
    
    $arquivoPem = file_get_contents("/var/www/html/nf/nfse/certificados/".$pCnpj."_certKey.pem");
    echo "/var/www/html/nf/nfse/certificados/".$pCnpj."_certKey.pem";
    
    $ToolsNFePHP = new ToolsNFePHP();
    if(!$ToolsNFePHP->__validCerts($arquivoPem,$aRetorno)){
        file_put_contents($pArqSaida, $ToolsNFePHP->errMsg);
    }else{
        
        file_put_contents($pArqSaida, $ToolsNFePHP->validadeReal) ;
    }
        
?>