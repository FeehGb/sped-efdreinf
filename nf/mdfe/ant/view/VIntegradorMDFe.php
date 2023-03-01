<?php
	/*	Classe: VIntegradorMDFe
	*	Guilherme ultima alteracao 30/09/2014
	*	Programa para integrar as MDFe do sistema (chamado pelo integrador da NFe)
	*/
	define('__ROOT__', dirname(dirname(__FILE__))); 
	require_once(__ROOT__."/model/MConfig.php");
	require_once(__ROOT__."/control/CIntegracaoERP.php");
	require_once(__ROOT__."/control/CLoteSefaz.php");
	require_once(__ROOT__."/control/CGerarDAMDFe.php");
	require_once(__ROOT__."/control/CLog.php");
	/*require_once(__ROOT__."/control/CEmail.php");
	require_once(__ROOT__."/control/CIntegrarTerceiros.php");
	*/
	$CLog = new CLog("VIntegradorMDFe");
	$CLog->mMensagem("inicio do programa -----------------------------------------");
	// Listar base de dados cadastradas no nfse_config e que realmente estão criadas no MySQL
	$MConfig = new MConfig('config');
	$listaBancos = $MConfig->listarBancos();
	if(!$listaBancos){
		$CLog->mMensagem("Erro da listagem do Banco: ".$MConfig->mensagemErro);
		return false;
	}

	// Percorrer por todas as bases para verificar se existe processos a serem executados
	foreach($listaBancos as $base){
		$CLog->mMensagem("Listar banco de dados: ".$base);
		$grupo = explode("_",$base['base']);
		$grupo = $grupo[1];
		// Consultar Notas Sefaz
		$int = new CLoteSefaz($grupo);
			$CLog->mMensagem("chamar a consulta de lotes");
		if(!$int->mConsultarLote()){
			$CLog->mMensagem("Erro ao consultar Lote: ".$int->mensagemErro);
		}

	// Integrar MDFe do ERP
		$int = new CIntegracaoERP($grupo);
			$CLog->mMensagem("chamar a integracaoERP");
		if(!$int->mIntegrarERP()){
			$CLog->mMensagem("Erro ao IntegrarERP: ".$int->mensagemErro);
		}
	// Emitir Notas integradas TODO
		$int = new CLoteSefaz($grupo);
			$CLog->mMensagem("submeter envio do lote Sefaz");
		if(!$int->mSubmeterLote()){
			$CLog->mMensagem("Erro ao submeter o Lote: ".$int->mensagemErro);
		}
	// Gerar danfe automática TODO
		$inut = new CGerarDAMDFe($grupo);
			$CLog->mMensagem("gerar a danfe automatica");
		if(!$inut->gerarDanfeAutomatica()){
			$CLog->mMensagem("Erro ao gerar Danfe automatica: ".$int->mensagemErro);
		}
		
	// Integrar notas externas TODO

	    /*$inut = new CIntegrarTerceiros($grupo);
		if(!$inut->mIntegrarTerceiros()){
			echo $inut->mensagemErro;
		}*/
		
	// Enviar Emails	 TODO
	/*	$email = new CEmail($grupo);
		if(!$email->mEmailsPendentes()){
		   echo $email->mensagemErro;
		}*/
	}

?>
