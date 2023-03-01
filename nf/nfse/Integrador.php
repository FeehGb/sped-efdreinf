<?php
	/*
		Programa:				Integrador.php
		Autor:					Guilherme Silva
		Data:					27-01-2012
		Finalidade: 			Efetuar a integracao do COBOL com o Servidor da Prefeitura
		Programas chamadores: 	Linha de Comando
		Programas chamados: 	CArquivoComunicacao, CComunicadorWebService
	*/
	
	//Classes importadas
	require_once("/var/www/html/nfse/control/CArquivoComunicacao.php");
	require_once("/var/www/html/nfse/control/CComunicadorWebService.php");
	require_once("/var/www/html/nfse/control/CXml.php");
	require_once("/var/www/html/nfse/control/CLog.php");
	
	//Instanciando Classes Importadas
	$CArquivoComunicacao = new CArquivoComunicacao();
	$CXml = new CXml();
	$CComunicadorWebService = new CComunicadorWebService();
	$CEmail = new CEmail();
//	$CComunicadorWebService = new CComunicadorWebService();
	
	/* Forma de Chamada do programa
		php -q Integrador.php <XML ENVIO> <XML RETORNO> <FLAG LOG 1=sim 0=nao>
  	*/

	if($argv[1] == "" || $argv[2] == ""){
		echo "Integrador-> Chamada do programa incorreta";
		exit();
	}


	if(!$CArquivoComunicacao->efetuarEntradaArquivo($argv[1])){
		$CArquivoComunicacao->gravarArquivoRetorno($argv[2], "", "", "", $CArquivoComunicacao->mensagemErro, "", "", "");
	}
	
	//Cadastrar XML na NF conforme Array informado
	if(!$CXml->cadastrarXML($CArquivoComunicacao->xml)){
		$xml = $CArquivoComunicacao->xml;
		$CArquivoComunicacao->gravarArquivoRetorno($argv[2], $xml->nf->numero, $xml->nf->controle, "N", $CXml->mensagemErro, $xml->nf->empresa->codigo, $xml->nf->filial->codigo);
		exit();
	}
	
	//Envio das informacoes via Web Service
	$xml = $CArquivoComunicacao->xml;
	//Comunicacao com WebService
	$CComunicadorWebService->codEmpresa = $xml->nf->empresa->codigo;
	$CComunicadorWebService->codFilial = $xml->nf->filial->codigo;
	$CComunicadorWebService->numeroControle = $xml->nf->controle;
	$CComunicadorWebService->codigoIBGE = $xml->prestador->cidade; // Codigo do IBGE
	
	if(!$CComunicadorWebService->comunicarWebService()){
	  $CArquivoComunicacao->gravarArquivoRetorno($argv[2], $CComunicadorWebService->numeroNota, $xml->nf->controle, $CComunicadorWebService->status, $CComunicadorWebService->mensagemErro, $xml->nf->empresa->codigo, $xml->nf->filial->codigo);
	  exit();
	}
	
	//Escrita do XML de retorno
	$CArquivoComunicacao->gravarArquivoRetorno($argv[2], $CComunicadorWebService->numeroNota, $CComunicadorWebService->numeroControle, $CComunicadorWebService->status, $CComunicadorWebService->criticas, $CComunicadorWebService->codEmpresa, $CComunicadorWebService->codFilial);
	
?>