<?php
	// programa para converter xml em txt
	require_once("ConvertNfeNFePHP.class.php");
	$ConvertNfeNFePHP = new ConvertNfeNFePHP();
	$retorno = $ConvertNfeNFePHP->XML2TXT($argv[1]);
	print_r($retorno['erros']);
	echo "\n";
	print_r($retorno['avisos']);
	echo "\n";
	file_put_contents($argv[2],$retorno['txt']);
?>