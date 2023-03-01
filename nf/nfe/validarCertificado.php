<?php

/*
        Programa:  validarCertificado.php
        Descricão: Programa responsável por validar o certificado e atualizar o arquivo config.ini com o nome do certificado e a nova senha.
        Autor:     J. Eduardo Lino (05/09/2016)
    */

// Inclui classe CAssinaturaDigital para validação do certificado
require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");

// Obtem variavels recebidas do SNF810.js

$cnpj 			= isset($_POST['cnpj'])? $_POST["cnpj"]: $argv[1];
$senha 			= isset($_POST["senha"]) ?$_POST["senha"]: $argv[2];
$opcao 			= isset($_POST["opcao"]) ?$_POST["opcao"]: $argv[3];
$razao_social 	= isset($_POST["razao_social"]) ?$_POST["razao_social"]: $argv[4];
$retorno =[];
$retorno['opcao'] = $opcao;

switch ($opcao) {
	
	case 'VALIDAR-SENHA-CERTIFICADO':


		$CAssinaturaDigital = new CAssinaturaDigital();
		if (!file_exists("/var/www/html/nf/nfse/certificados/" . $cnpj . ".pfx")) {
			$retorno['mensagem'] = "O certificado digital nao foi encontrado no diretorio:\n\n" . "/var/www/html/nf/nfse/certificados/" . $cnpj . ".pfx";
		} else {
			$CAssinaturaDigital->cnpj = $cnpj;
			if (!openssl_pkcs12_read(file_get_contents("/var/www/html/nf/nfse/certificados/" . $cnpj . ".pfx"), $x509certdata, $senha)) {
				$retorno['mensagem'] = "Certificado digital nao pode ser lido, verifique se a senha está correta.";
				break;
			}

			if (!$CAssinaturaDigital->validaCertificado($x509certdata['cert'])) {
				$retorno['mensagem'] = $CAssinaturaDigital->mensagemErro;
			} else {
				$retorno['mensagem'] = "Certificado valido com Sucesso!";
				$retorno['dataValidade']   = "20" . $CAssinaturaDigital->validadeAno . "-" . $CAssinaturaDigital->validadeMes . "-" . $CAssinaturaDigital->validadeDia;
				//$retorno['dataValidadeBR'] = $CAssinaturaDigital->validadeDia . "/" . $CAssinaturaDigital->validadeMes ."/" . "20".$CAssinaturaDigital->validadeAno;
				$retorno['dataValidadeBR'] = $CAssinaturaDigital->validadeReal;
				//$retorno['horaValidade']   = $CAssinaturaDigital->validadeHor.":".$CAssinaturaDigital->validadeMin.":".$CAssinaturaDigital->validadeSeg;
				$retorno['horaValidade']   = '';
			}
		}
		break;

	case 'ATUALIZAR-CONFIG-INI':
		$conteudo = '';
		
		$file_read = fopen("/var/www/html/nf/nfe/config/config.ini", "r");
		$existe = false;
		
		
		if (!$file_read) {
			$retorno["mensagem"] = "Falha na leitura do arquivo! ../config.ini";
			break;
		}
		
		while (!feof($file_read)) {
			$linha = fgets($file_read);
			$array = explode("=", $linha);

			if ($array[0] == $cnpj) {
				$conteudo .= $cnpj . "=" . $senha . "\n";
				$existe = true;
			} else {
				$conteudo .= $linha;
			}
		}
		
		if(!$existe){
			$conteudo .= "\n\n;" . $razao_social;
			$conteudo .= "\n" . $cnpj . "=" . $senha;
			
		}
		
		$retorno["mensagem"] = "Dados gravados ou atualizado com sucesso!";
		
		fclose($file_read);
		file_put_contents("/var/www/html/nf/nfe/config/config.ini", $conteudo);

		array_map('unlink', glob("/var/www/html/nf/nfse/certificados/" . $cnpj . "_*.pem"));


		

		break;
}

echo json_encode($retorno);
