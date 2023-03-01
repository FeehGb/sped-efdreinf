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
	require_once("/var/www/html/nfse/control/COperacoesPortal.php");
	
// funчуo a ser executada
	switch(trim($_POST['funcao'])){
		case "INICIAR":
		  if(!file_exists("/var/www/html/nfse/configuracoes/config.ini")){
			$retorno['mensagemErro'] = "O Sistema ainda nao foi instalado, voce sera redirecionado para a tela de instacao!";
			echo json_encode($retorno);
			exit();
		  }
		  $COperacoesPortal = new COperacoesPortal();
		  $notas = $COperacoesPortal->retornarNotasFiscais();
		  echo json_encode($notas);
		  exit();
		break;
		case "ENVIAR-EMAIL":
		  $COperacoesPortal = new COperacoesPortal();
		  $email = $COperacoesPortal->enviarEmail($_POST['hEmpresa'],$_POST['hFilial'],$_POST['hControle'],$_POST['hEmail']);
		  echo json_encode($email);
		  exit();
		break;
		case "VER-XML":
		  $COperacoesPortal = new COperacoesPortal();
		  $xml = $COperacoesPortal->retornarXml($_POST['hEmpresa'],$_POST['hFilial'],$_POST['hControle'], $_POST['hIBGE']);
		  echo json_encode($xml);
		  exit();
		break;
		case "CANCELAR-NF":
		  $COperacoesPortal = new COperacoesPortal();
		  $cancelado = $COperacoesPortal->cancelarNotaFiscal($_POST['hEmpresa'],$_POST['hFilial'],$_POST['hControle'], $_POST['hIBGE']);
		  echo json_encode($cancelado);
		  exit();
		break;
		case "VER-CRITICAS":
		  $COperacoesPortal = new COperacoesPortal();
		  $criticas = $COperacoesPortal->retornarCriticas($_POST['hEmpresa'],$_POST['hFilial'],$_POST['hControle']);
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
		  $COperacoesPortal = new COperacoesPortal();
		  $verBackup = $COperacoesPortal->verBackup();
		  echo json_encode($verBackup);
		  exit();
		break;
	}
?>