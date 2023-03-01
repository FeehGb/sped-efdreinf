<?php

	/*
		Programa:				IntegradorNovo.php
		Autor:					J. Eduardo N. Lino, adaptado de (Guilherme Silva, 27-01-2012)
		Data:					06/05/2016
		Finalidade: 			Efetuar a integracao do COBOL com o Servidor da Prefeitura
		Programas chamadores: 	Linha de Comando
		Programas chamados: 	CArquivoComunicacao, CComunicadorWebService
		argv[0]:				Arquivo XML de Entrada
		argv[1]:				Arquivo XML de Retorno
	*/
	//Classes importadas
//	echo "teste ";
	require_once("/var/www/html/nf/nfse/control/CArquivoComunicacao.php");
//	echo "teste ";
	require_once("/var/www/html/nf/nfse/control/CComunicadorWebService.php");
//	echo "teste ";
	require_once("/var/www/html/nf/nfse/control/CXml.php");
//	echo "teste ";
	require_once("/var/www/html/nf/nfse/control/CLog.php");
	
//echo "aqui";
	//Instanciando Classes Importadas
	$CArquivoComunicacao = new CArquivoComunicacao();
	
	
	
	if($argv[1] == "" || $argv[2] == "")
	{
		echo "Integrador-> Chamada do programa incorreta";
		exit();
	}

//echo "anteeeees";
	if(!$CArquivoComunicacao->efetuarEntradaArquivo($argv[1]))
	{
		//echo "certo";
		//$pArqRetorno, $pNumeroNota, $pNumeroControle, $pStatus, $pCriticas, $pEmpresa, $pFilial, $pNroRps, $pCodigoVerificacao, $protocolo
		
		$CArquivoComunicacao->gravarArquivoRetorno($argv[2], "", "", "", $CArquivoComunicacao->mensagemErro, "", "", "","","");
	}

//	echo "meio";
	// Informar Grupo
	$grupo = strtolower($CArquivoComunicacao->xml->nf->grupo);

	//Envio das informacoes via Web Service
	$xml = $CArquivoComunicacao->xml;

	//Comunicacao com WebService
	$CComunicadorWebService = new CComunicadorWebService($grupo);
	$CComunicadorWebService->codEmpresa 	   = $xml->nf->empresa->codigo;
	$CComunicadorWebService->codFilial 		   = $xml->nf->filial->codigo;
	$CComunicadorWebService->prestadorCnpj 	   = $xml->prestador->cpfcnpj;
	$CComunicadorWebService->numeroControle    = $xml->nf->controle;
	$CComunicadorWebService->codigoIBGE 	   = $xml->prestador->cidade; // Codigo do IBGE

	$CComunicadorWebService->ambiente 		   = $xml->nf->ambiente;
	$CComunicadorWebService->usuarioPrefeitura = $xml->nf->usuarioPrefeitura;
	$CComunicadorWebService->senhaPrefeitura   = $xml->nf->senhaPrefeitura;

//	echo "antes";

	//SVE436S -> programa que chama esse php no cobol
	$CComunicadorWebService->comunicarWebService("", $argv[2], "COBOL", "", $CComunicadorWebService->ambiente, $xml);
	
	
	
	
	