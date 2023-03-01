<?php
	/*
		Programa:				portal.php
		Autor:					Guilherme Silva
		Data:					10/02/2012
		Finalidade: 			Efetuar a comunicaco do sistema do portal com a estrutura de classes
		Programas chamadores: 	index.php (view)
		Programas chamados: 	CArquivoComunicacao, CComunicadorWebService
	*/

	//Classes importadas
	require_once("/var/www/html/nf/nfse/control/COperacoesPortal.php");

	$grupo = str_replace(" ","",strtolower($_POST['hGrupo']));
// funчуo a ser executada
	switch(trim($_POST['funcao'])){
		case "INICIAR":
		  if(!file_exists("/var/www/html/nf/nfse/config.ini")){
			$retorno['mensagemErro'] = "O Sistema ainda nao foi instalado, entre em contato com o Suporte Tecnico para efetuar a instalaчуo!";
			echo json_encode($retorno);
			exit();
		  }
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $notas = $COperacoesPortal->retornarContribuintes();
		  echo json_encode($notas);
		  exit();
		break;

		case "LISTAR-NFS-AUTORIZADA":
			// Sucesso (S) = Autorizada
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  if(isset($_POST['sContribuinte']) && $_POST['sContribuinte'] != ""){
			$notas = $COperacoesPortal->retornarNotasFiscais($_POST['sContribuinte'], $_POST['hIndUltNF'],"S",$_POST['tConsultaPeriodoInicial'], $_POST['tConsultaPeriodoFinal']);
			echo json_encode($notas);
		  }else{
			$retorno['mensagemErro'] = "Obrigatorio selecionar um contribuinte para vsualizar as Notas!";
			echo json_encode($retorno);
		  }
		  exit();
		break;

		case "LISTAR-NFS-CANCELADA":
			// Cancelada (C)
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  if(isset($_POST['sContribuinte']) && $_POST['sContribuinte'] != ""){
			$notas = $COperacoesPortal->retornarNotasFiscais($_POST['sContribuinte'], $_POST['hIndUltNF'],"C",$_POST['tConsultaPeriodoInicial'], $_POST['tConsultaPeriodoFinal']);
			echo json_encode($notas);
		  }else{
			$retorno['mensagemErro'] = "Obrigatorio selecionar um contribuinte para vsualizar as Notas!";
			echo json_encode($retorno);
		  }
		  exit();
		break;

		case "LISTAR-NFS-NOVA":
			// Nova (N)
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  if(isset($_POST['sContribuinte']) && $_POST['sContribuinte'] != ""){
			$notas = $COperacoesPortal->retornarNotasFiscais($_POST['sContribuinte'], $_POST['hIndUltNF'], "N",$_POST['tConsultaPeriodoInicial'], $_POST['tConsultaPeriodoFinal']);
			echo json_encode($notas);
		  }else{
			$retorno['mensagemErro'] = "Obrigatorio selecionar um contribuinte para vsualizar as Notas!";
			echo json_encode($retorno);
		  }
		  exit();
		break;

		case "LISTAR-NFS-CRITICA":
			// Critica (E)
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  if(isset($_POST['sContribuinte']) && $_POST['sContribuinte'] != ""){
			$notas = $COperacoesPortal->retornarNotasFiscais($_POST['sContribuinte'], $_POST['hIndUltNF'], "E",$_POST['tConsultaPeriodoInicial'], $_POST['tConsultaPeriodoFinal']);
		    echo json_encode($notas);
		  }else{
			$retorno['mensagemErro'] = "Obrigatorio selecionar um contribuinte para vsualizar as Notas!";
			echo json_encode($retorno);
		  }
		  exit();
		break;

		case "ENVIAR-EMAIL":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $email = $COperacoesPortal->enviarEmail($_POST['sContribuinte'],$_POST['hControle'],$_POST['hEmail']);
		  echo json_encode($email);
		  exit();
		break;
		case "VER-XML":
		  $COperacoesPortal = new COperacoesPortal($grupo);
  		  $xml = $COperacoesPortal->retornarXml($_POST['sContribuinte'],$_POST['hControle'], $_POST['hIBGE']);
		  echo json_encode($xml);
		  exit();
		break;
		case "CANCELAR-NF":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $cancelado = $COperacoesPortal->cancelarNotaFiscal($_POST['sContribuinte'],$_POST['hControle'], $_POST['hIBGE']);
		  echo json_encode($cancelado);
		  exit();
		break;
		case "VER-CRITICAS":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $criticas = $COperacoesPortal->retornarCriticas($_POST['sContribuinte'],$_POST['hControle']);
		  echo json_encode($criticas);
		  exit();
		break;
		case "BACKUP":
		  $COperacoesPortal = new COperacoesPortal();
		  $backup = $COperacoesPortal->efetuarBackup($_POST['hNomeBackup']);
		  echo json_encode($backup);
		  exit();
		break;
		case "VER-BACKUP":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $verBackup = $COperacoesPortal->verBackup();
		  echo json_encode($verBackup);
		  exit();
		break;
		case "REENVIAR-CANC-ERP":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $atualiza = $COperacoesPortal->atualizarCobol($_POST['sContribuinte'],$_POST['hControle'],$_POST['hNumeroNota'], $_POST['hSerieNota']);
		  echo json_encode($atualiza);
		  exit();
		break;
		case "REENVIAR-AUT-ERP":
		  $COperacoesPortal = new COperacoesPortal($grupo);
		  $atualiza = $COperacoesPortal->atualizarCobol($_POST['sContribuinte'],$_POST['hControle'],$_POST['hNumeroNota'], $_POST['hSerieNota']);
		  echo json_encode($atualiza);
		  exit();
		break;
	}
?>