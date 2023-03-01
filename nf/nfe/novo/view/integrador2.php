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
	require_once("../control/CEmail.php");

	// Listar base de dados cadastradas no nfse_config e que realmente estão criadas no MySQL
	$MConfig = new MConfig('config');
	$listaBancos = $MConfig->listarBancos();
	if(!$listaBancos){
		echo $MConfig->mensagemErro;
		return false;
	}
	
echo "uscita Mysql\n";
	// Percorrer por todas as bases para verificar se existe processos a serem executados
	foreach($listaBancos as $base){
		$grupo = explode("_",$base['base']);
		$grupo = $grupo[1];

	// Integrar Notas Fiscais do ERP
		$int = new CIntegracaoERP($grupo);

		if(!$int->mIntegrarERP()){
			echo $int->mensagemErro;
		}
		
		// Remover CIntegracaoERP
		unset($int);
                echo "uscita ERP\n";

	// Emitir Notas integradas
		$int = new CLoteSefaz($grupo);

		if(!$int->mSubmeterLote()){
			echo $int->mensagemErro;
		}
		
		// Remover CLoteSefaz
		unset($int);

		$int = new CLoteSefaz($grupo);

		if(!$int->mConsultarLote()){
		   echo $int->mensagemErro;
		}
		
		// Remover CLoteSefaz
                unset($int);
                echo "uscita CLoteSefaz\n";
		
	// Gerar danfe automática
		$inut = new CGerarDanfe($grupo);
		if(!$inut->gerarDanfeAutomatica()){
			echo $inut->mensagemErro;
		}
		
		// Remover CGerarDanfe
                unset($int);
                echo "uscita CGerarDanfe\n";
		
	// Integrar notas externas

	    $inut = new CIntegrarTerceiros($grupo);
		if(!$inut->mIntegrarTerceiros()){
			echo $inut->mensagemErro;
		}
		
		// Remover CIntegrarTerceiros
                unset($int);
                
                echo "uscita CIntegrarTerceiros\n";

				
	// Enviar Emails	
	/*	$email = new CEmail($grupo);
		if(!$email->mEmailsPendentes()){
		   echo $email->mensagemErro;
		}*/
	}

	// Remover instancia do MConfig
	unset($MConfig);
	unset($listaBancos);
        echo "fermato\n" ;
	
	echo "|";
?>
