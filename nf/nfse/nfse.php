<?php
	//error_reporting(0);

	/*
		Programa:  IntegradorNovo.php
		Autor:	   J. Eduardo N. Lino
		Data:	   05/04/2016
	*/

	//Classes importadas
	require_once("/var/www/html/nf/nfse/control/COperacoesPortal.php");


	$conteudo = file_get_contents($argv[1]); 

	$nomeArquivo = explode("/",$argv[1]);
    $nomeArquivo = end($nomeArquivo);

    $diretorio_retorno = "";
    $diretorio_retorno = str_replace("CaixaEntrada/Processar/CNFS", "CaixaSaida/Sefaz/CNFSR", $argv[1]);
	$DadosTXT = explode("|", $conteudo);
	$DadosTXT[8] = str_replace("\n", "", $DadosTXT[8]);
	
	$cnpj = trim($DadosTXT[0]);
	$uf = trim($DadosTXT[1]);
	$tipo_emissao = trim($DadosTXT[2]);
	$chave = trim($DadosTXT[3]);
	$rps = trim($DadosTXT[4]);
	$justificativa = trim(str_replace("\n", "", $DadosTXT[5]));
	$ambiente = trim($DadosTXT[6]);
	$ibge = trim($DadosTXT[7]);
	$insc_municip = trim($DadosTXT[8]);
	$protocolo = trim($DadosTXT[9]);
	$usuarioPrefeitura = trim($DadosTXT[10]);
	$senhaPrefeitura = trim($DadosTXT[11]);
	$codigoTom = str_replace("\n", "", trim($DadosTXT[12]));


	//print_r($DadosTXT);

	switch($nomeArquivo[0]){

		case "C":
		  $grupo = '';
		  $COperacoesPortal = new COperacoesPortal($grupo);

		  
		  echo "\n cnpj:".$cnpj."\n";
		  echo "\n protocolo:".$protocolo."\n";
		  echo "\n ibge:".$ibge."\n";
		  echo "\n diretorio_retorno:".$diretorio_retorno."\n";
		  echo "\n ambiente:".$ambiente."\n";
		  echo "\n usuarioPrefeitura:".$usuarioPrefeitura."\n";
		  echo "\n senhaPrefeitura:".$senhaPrefeitura."\n";

		  $cancelado = $COperacoesPortal->cancelarNotaFiscal($cnpj, $protocolo, $ibge, $diretorio_retorno, "COBOL", $DadosTXT, $ambiente, $usuarioPrefeitura, $senhaPrefeitura);

		  exit();
		break;
	}
?>