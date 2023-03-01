
<?php

	
	require_once("libs/ConvertNFePHP.class.php");
	require_once("libs/ToolsNFePHP.class.php");
	require_once("control/CIntegracaoERP.php");
	require_once("control/CBackup.php");



	$cnpj="";
	$ambiente="";
	$tpEmis="";
	$cUF="";
	$chave="";

	//$file = fopen("411000640320987-procNFe.TXT", "r");
	$conteudo_arquivo = file_get_contents("411000640320987-procNFe.TXT");

	// Converter TXT em XML
	$ConvertNFePHP = new ConvertNFePHP();
	$xml = $ConvertNFePHP->nfetxt2xml($conteudo_arquivo);
	if((!$xml) || $xml == ""){
		$CIntegracaoERP->log("Erro ao converter o TXT em XML, verifique se o arquivo esta correto","nfe.php");
	}
	$cnpj = $ConvertNFePHP->CNPJ;
	//$ambiente = 2;
	$ambiente = $ConvertNFePHP->tpAmb;
	$cUF = $ConvertNFePHP->cUF;
	$tpEmis = $ConvertNFePHP->tpEmis;
	$chave = $ConvertNFePHP->chave;
    $modelo = $ConvertNFePHP->modelo;

	// Instancia comunicao com classe de envio
    if($tpEmis == "7"){
        $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, "SVC", false, $modelo);
    }else{
        $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, false, false, $modelo);
    }

    
	// Assinar o XML
	//$xml = $ToolsNFePHP->signXML($xml[0], 'infNFe');
	//if(!$xml){
	//	$CIntegracaoERP->log($ToolsNFePHP->errMsg,"ToolsNFePHP (signXML)");
	//	// TODO Gravar arquivo de erro na Caixa de Saida
	//	return false;
	//}
	

	echo $tpEmis."\n";
	echo $ambiente."\n";
	echo $cUF."\n\n";

	//$aRetorno = array();

	$ToolsNFePHP->getProtocol('', $chave, $ambiente, 2, $aRetorno);
	//$ToolsNFePHP->statusServico($cUF, 2, &$aRetorno);
	//$aRetorno = $ToolsNFePHP->verifyNFe("411000640320987-procNFe.xml");

	print_r($aRetorno);










?>