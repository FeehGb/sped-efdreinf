<?php

	/*
        Programa:  comunicaPrefeitura.php
        Descricão: Programa responsavel por emitir nota fiscal de serviço
        Autor:     J. Eduardo Lino (15/03/2017)
    */

	require_once("/var/www/html/nf/nfse/v2/prefeitura.php");

	$xml                   = $argv[1];
	$parametros_webservice = $argv[2];
	$metodo                = $argv[3];
	$tipo_envio            = $argv[4];
	$senha_certificado     = $argv[5];
	$parametros_assinatura = $argv[6];

	$nome_retorno = explode("/",$argv[1]);
    $nome_retorno = end($nome_retorno);

	$prefeitura = new Prefeitura($xml, $parametros_webservice, $metodo, $tipo_envio, $senha_certificado, $parametros_assinatura, $nome_retorno);

	$prefeitura->assinaXML();
	$prefeitura->encapsulaXML(); 
	$prefeitura->comunicaWebService();

?>