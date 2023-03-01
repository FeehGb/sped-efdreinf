<?php
	/*	TESTE para SUBMETER as NFs
	*	Guilherme ultima alteracao 29/04/14
	*/
	require_once("/var/www/html/nf/nfe/novo/model/MConfig.php");
	require_once("/var/www/html/nf/nfe/novo/control/CIntegracaoERP.php");
	require_once("/var/www/html/nf/nfe/novo/control/CIntegrarTerceiros.php");
	require_once("/var/www/html/nf/nfe/novo/control/CLoteSefaz.php");
	require_once("/var/www/html/nf/nfe/novo/control/CInutilizar.php");
	require_once("/var/www/html/nf/nfe/novo/control/CGerarDanfe.php");
	require_once("/var/www/html/nf/nfe/novo/control/CEmail.php");
//	require_once("/var/www/html/nf/mdfe/view/VIntegradorMDFe.php");

	// Listar base de dados cadastradas no nfse_config e que realmente estão criadas no MySQL
	$MConfig = new MConfig('config');
	$listaBancos = $MConfig->listarBancos();
	if(!$listaBancos){
		echo $MConfig->mensagemErro;
		return false;
	}

	// Percorrer por todas as bases para verificar se existe processos a serem executados
	foreach($listaBancos as $base){
        $grupo = substr($base['base'],strpos($base['base'],"_")+1);
		//$grupo = explode("_",$base['base']);
		//$grupo = $grupo[1];
		
        // Consultar Notas Sefaz
		$int = new CLoteSefaz($grupo);
		if(!$int->mConsultarLote()){
			echo $int->mensagemErro;
		}
		// Remover CLoteSefaz
		unset($int);

        // Integrar Notas Fiscais do ERP
		$int = new CIntegracaoERP($grupo);
		if(!$int->mIntegrarERP()){
			echo $int->mensagemErro;
		}
		// Remover CIntegracaoERP
		unset($int);
		
	// Emitir Notas integradas
		$int = new CLoteSefaz($grupo);
		if(!$int->mSubmeterLote()){
			echo $int->mensagemErro;
		}
		// Remover CLoteSefaz
		unset($int);

	// Enviar Emails
		$email = new CEmail($grupo);
		if(!$email->mEmailsPendentes()){
		   echo $email->mensagemErro;
		}

	// Gerar danfe automática
		$inut = new CGerarDanfe($grupo);
		if(!$inut->gerarDanfeAutomatica()){
			echo $inut->mensagemErro;
		}
		
		// Remover CGerarDanfe
        unset($int);

	// Integrar notas externas
	    $inut = new CIntegrarTerceiros($grupo);
		if(!$inut->mIntegrarTerceiros()){
			echo $inut->mensagemErro;
		}
		
		// Remover CIntegrarTerceiros
                unset($int);
		
	}
	// Remover instancia do MConfig
	unset($MConfig);
	unset($listaBancos);

	echo "\n".date('d/m/Y H:i:s')."finish process \n";	
?>
        