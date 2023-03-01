<?php
	/*
		Programa: funcoes.php
		Finalidade: Efetua as funcoes de instalacao do sistem NFS-e
		Autor: Guilherme Silva
		Data: 03/02/2012
		Chamador: index.php
		Chamados: adodb.inc.php
	*/
	error_reporting(E_ALL);

	include("/var/www/html/nf/nfse/model/adodb/adodb.inc.php");
//    require("../control/phpmailer/class.smtp.php");
    require("../control/phpmailer/class.phpmailer.php");
	require_once("/var/www/html/nf/nfse/model/CEmpresa.php");
	require_once("/var/www/html/nf/nfse/model/CBd.php");	

	$funcao = $_POST['funcao'];
	$retorno;
	//$mysqli = new mysqli(trim($_POST['servidor']),trim($_POST['usuario']),trim($_POST['senha'], $_POST['banco']));

    //$link = mysqli_connect(trim($_POST['servidor']),trim($_POST['usuario']),trim($_POST['senha'], trim($_POST['banco'])));
    $link = mysqli_connect(trim($_POST['servidor']),trim($_POST['usuario']),trim($_POST['senha'], ''));

	print_r(mysqli_connect_error());

	switch($funcao){
		case "testarConexao":
			//if(!mysqli_connect(trim($_POST['servidor']),trim($_POST['usuario']),trim($_POST['senha']))){
			if (mysqli_connect_errno()) {
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Ops! Nome do Servidor ou BD ou Usuario e Senha incorretos. Se tiver duvida entre em contato com o Suporte Tecnico que efetuou a instalacao do MySql!";

			}else{
				$result = mysqli_query($link,'show databases like "'.$_POST['banco'].'"'); 
				if(!$result){
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Houve um erro ao tentar buscar a base de dados. Contate o Suporte Tecnico que efetuou a instalacao do MySql!";
				}else{
					$array = mysqli_fetch_assoc($result);
					if($array["Database (".$_POST['banco'].")"] == ""){
						$retorno['codigo'] = "00";
						$retorno['mensagem'] = "OK";
					}elseif(trim($array["Database (".$_POST['banco'].")"]) == trim($_POST['banco'])){
						$retorno['codigo'] = "01";
						$retorno['mensagem'] = "Banco de Dados ja existente!";
					}else{
						$retorno['codigo'] = "99";
						$retorno['mensagem'] = "Erro no banco de dados!";
					}
				}
			}
			echo json_encode($retorno);
			exit();
		break;
		case "utilizarBD":
			$retorno['codigo'] = "00";
			$retorno['mensagem'] = "OK";
			echo json_encode($retorno);
			exit();
		break;
		case "excluirBD":
			//$link = mysqli_connect($_POST['servidor'],$_POST['usuario'],$_POST['senha']);
			if(!$link){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Ops! Nome do Servidor ou BD ou Usuario e Senha incorretos. Se tiver duvida entre em contato com o Suporte Tecnico que efetuou a instalacao do MySql!";
			}else{
				if (mysqli_query($link, "DROP DATABASE ".$_POST['banco'])) {
					$retorno['codigo'] = "00";
					$retorno['mensagem'] = "Excluido com sucesso!";
				} else {
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Erro no banco de dados! ".mysqli_error();
				}
			}
			echo json_encode($retorno);
			exit();
		break;
		case "salvarConexao":
			//$link = mysqli_connect($_POST['servidor'],$_POST['usuario'],$_POST['senha']);
			if(!$link){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Ops! Nome do Servidor ou BD ou Usuario e Senha incorretos. Se tiver duvida entre em contato com o Suporte Tecnico que efetuou a instalacao do MySql!";
				echo json_encode($retorno);
				exit();
			}else{
				if (mysqli_query($link, "CREATE DATABASE ".$_POST['banco'])) {
					$retorno['codigo'] = "00";
					$retorno['mensagem'] = "Criado com sucesso!";
				} else {
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Erro ao criar o banco de dados! ".mysqli_error();
					echo json_encode($retorno);
					exit();
					echo json_encode($retorno);
					exit();
				}
			}
		case "salvarConexao2":
			if(!salvarConfiguracoes()){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Erro ao gravar os dados no arquivo de configuracao!";
			}else{
				$retorno['codigo'] = "00";
				$retorno['mensagem'] = "Criado arquivo com sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		break;
		case "criarTabelas":
		
			//$link = mysqli_connect($_POST['servidor'],$_POST['usuario'],$_POST['senha']);
			if(!$link){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Ops! Nome do Servidor ou BD ou Usuario e Senha incorretos. Se tiver duvida entre em contato com o Suporte Tecnico que efetuou a instalacao do MySql!";
			}else{
				if(!file_exists("tabelas.sql")){
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Arquivo tabelas.sql nao foi encontrado no diretorio corrente [".shell_exec("pwd")."] !";
				}
				if(is_readable("tabelas.sql")){
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Nao eh possivel efetuar a leitura do arquivo tabelas.sql verifique as permissoes!";
				}
				if(!importarTabelas()){
					$retorno['codigo'] = "99";
					$retorno['mensagem'] = "Erro ao criar as tabelas!";
				}else{
					$retorno['codigo'] = "00";
					$retorno['mensagem'] = "Criado com sucesso!";
				}
			}
			echo json_encode($retorno);
			exit();
		break;
		case "testarEmail":
			$retFunc = testarConexaoEmail();
			if($retFunc != true){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Erro ao testar conexao de email! ".$retFunc;
			}else{
				$retorno['codigo'] = "00";
				$retorno['mensagem'] = "Conexao efetuada com sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		
		case "salvarEmail":
			if(!salvarConfiguracoesEmail()){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Erro ao testar conexao de email! ".$retFunc;
			}else{
				$retorno['codigo'] = "00";
				$retorno['mensagem'] = "Conexao efetuada com sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		case "salvarConfigCidade":
			if(!salvarConfiguracoesCidade()){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Erro ao gravar os dados no arquivo de configuracao!";
			}else{
				$retorno['codigo'] = "00";
				$retorno['mensagem'] = "Configuracoes Salvas com Sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		case "adicionarRelacionamento":
			if(!adicionarRelacionamento()){
				$retorno['codigo'] = "99";
				$retorno['mensagem'] = "Erro ao adicionar o relacionamento!";
			}else{
				$retorno['codigo'] = "00";
				$retorno['mensagem'] = "Relacionamento adicionado!";
			}
			echo json_encode($retorno);
			exit();
		break;
		case "excluirRelacionamento":
		  $retorno['indice'] = $_POST['hIndiceExcluir'];
		  if(!excluirRelacionamento()){
			  $retorno['codigo'] = "99";
			  $retorno['mensagem'] = "Erro ao excluir o relacionamento!";
		  }else{
			  $retorno['codigo'] = "00";
			  $retorno['mensagem'] = "Relacionamento excluido!";
		  }
		  echo json_encode($retorno);
		  exit();
		break;
		case "listarRelacionamento":
		  $retorno = listarRelacionamento();
		  if($retorno == false){
			  $retorno['codigo'] = "99";
			  $retorno['mensagem'] = "Erro ao obter o relacionamento!";
		  }
		  echo json_encode($retorno);
		  exit();
		break;
		case "anexarCertificado":
			$retorno = anexarCertificado();
			if($retorno == false){
				$retorno['codigo'] == "99";
				$retorno['mensagem'] == "Impossivel anexar o arquivo, verifique se realmente trata-se de um certificado digital PFX com permissao";
			}
		  echo json_encode($retorno);
		  exit();
		break;
	}
	
	function salvarConfiguracoes(){
		// Gravar arquivo de configuracao do sistema (config.ini)
		$quebra = chr(13).chr(10);	//essa � a quebra de linha
		$ponteiro = fopen("../configuracoes/config.ini","w");
		if(!$ponteiro){
			return false;
		}		
		if( !fwrite($ponteiro, ';N�O ALTERAR ESTE ARQUIVO, GERADO AUTOMATICAMENTE'.$quebra )){ return false; }
		if( !fwrite($ponteiro, ';BANCO DE DADOS'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'servidor="'.trim($_POST["servidor"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'usuario="'.trim($_POST["usuario"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'senha="'.trim($_POST["senha"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'base="'.trim($_POST["banco"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'sgbd="mysql"'.$quebra )){ return false; }
		fclose($ponteiro);
		
		// Gravar arquivo de Backup (/user/bindib/f.BKP_MYSQL.sh)
		shell_exec("mkdir /user/nfse/backup");

		if( !($shellBackup = file_get_contents("f.BKP_MYSQL.sh"))){ return false; }
		  $shellBackup = str_replace("<servidor>",trim($_POST["servidor"]),$shellBackup);
		  $shellBackup = str_replace("<usuario>",trim($_POST["usuario"]),$shellBackup);
		  $shellBackup = str_replace("<senha>",trim($_POST["senha"]),$shellBackup);
		  $shellBackup = str_replace("<base>",trim($_POST["banco"]),$shellBackup);
		if( !file_put_contents("/user/bindib/f.BKP_MYSQL.".trim($_POST["banco"]), $shellBackup)){ return false; }

		// Tudo ok
		return true;
	}


	function importarTabelas(){
		$diretorio = shell_exec("pwd");
		$diretorio = substr($diretorio,0,$diretorio.lenght-1);
		$comando = "mysql --host=".$_POST['servidor']." --user=".$_POST['usuario']." --password=".$_POST['senha']." ".$_POST['banco']." < ".$diretorio."/tabelas.sql";
		$result = shell_exec($comando);
		if($result == ""){
			return true;
		}else{
			return false;
		}
	}
	
	function backupBD(){
		$diretorio = shell_exec("pwd");
		$diretorio = substr($diretorio,0,$diretorio.lenght-1);
		$comando = "mysqldump --host=".$_POST['servidor']." --user=".$_POST['usuario']." --password=".$_POST['senha']." --databases ".$_POST['banco']." > ".$diretorio."/teste.sql";
		$result = shell_exec($comando);
	}
	
	function testarConexaoEmail(){
	  $mail = new PHPMailer(true);
      $mail->IsSMTP();
      $mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
	  ##TLS Somente para contas do gmail
	  switch($_POST['tls']){
		  case "tls":
			  $mail->SMTPSecure = "tls";
		  break;
		  case "ssl":
			  $mail->SMTPSecure = "ssl";
		  break;
	  }
      $mail->Port = $_POST['porta']; // Default 25
      $mail->Host = $mail->Hostname = $_POST['smtp'];
      $mail->Username = $_POST['email'];
      $mail->Password = $_POST['senhaEmail'];
      $mail->SMTPDebug = 1; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao

      $mail->AddReplyTo = $_POST['email'];;

      $mail->From = $_POST['email']; // Email de origem
      $mail->FromName = "Teste de Envio Nota Fiscal Eleltronica Softdib"; // Nome de Origem

      $to = $_POST['email'];
      $mail->AddAddress($_POST['email']);

      $mail->Subject = "Instalador NFS-e da Softdib (Envio de teste de email)"; // Assunto
      $mail->WordWrap = 80; // Caracteres por linha
      $body = "O seu teste de envio de e-mail foi efetuado com sucesso, continue a configuracao do sistema!";
      $body = preg_replace('/\\\\/','', $body);
      $mail->MsgHTML($body); //corpo
      $mail->IsHTML(true);
	  if(!$mail->Send()){
		 return "deu erro nesta joca ".$mail->ErrorInfo;
	  }else{
		  return true;
	  }
      /*if($mail->Send()){
	  	  return true;
	  }else{
		  echo " opa deu pau ";
		 return $mail->ErrorInfo;
	  }*/
	}
	
	function salvarConfiguracoesEmail(){
		$quebra = chr(13).chr(10);	//essa � a quebra de linha
		$ponteiro = fopen("../configuracoes/config.ini","a+");
		if(!$ponteiro){
			return false;
		}		
		if( !fwrite($ponteiro, ';EMAIL'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'smtp="'.trim($_POST["smtp"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'porta="'.trim($_POST["porta"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'email="'.trim($_POST["email"]).'"'.$quebra )){ return false; }
		if( !fwrite($ponteiro, 'senhaEmail="'.trim($_POST["senhaEmail"]).'"'.$quebra )){ return false; }
		fclose($ponteiro);
		return true;
	}
	
	function salvarConfiguracoesCidade(){
		$campos = explode(",",$_POST['campos']);

		$quebra = chr(13).chr(10);	//essa � a quebra de linha
		$ponteiro = fopen("../configuracoes/config.ini","a+");
		if(!$ponteiro){
			return false;
		}

		if( !fwrite($ponteiro, ';CONFIGURACAO WEB SERVICE'.$quebra )){ return false; }
		foreach($campos as $conteudo){
		  if( !fwrite($ponteiro, $conteudo.'="'.trim($_POST[$conteudo]).'"'.$quebra )){ return false; }
		}
		
		fclose($ponteiro);
		return true;
	}
	
	function adicionarRelacionamento(){
		$indice = $_POST['hIndice'];
		$CEmpresa = new CEmpresa();
		if(!$CEmpresa->inserir($_POST['empresa'.$indice], $_POST['filial'.$indice], $_POST['empresa_web'.$indice], $_POST['filial_web'.$indice])){
			return $CEmpresa->mensagemErro;
		}else{
			return true;
		}
	}
	
	function excluirRelacionamento(){
	  $indice = $_POST['hIndiceExcluir'];
	  $CEmpresa = new CEmpresa();
	  if(!$CEmpresa->excluir($_POST['empresa'.$indice], $_POST['filial'.$indice])){
		  return $CEmpresa->mensagemErro;
	  }else{
		  return true;
	  }
	}
	
	function listarRelacionamento(){
	  $CEmpresa = new CEmpresa();
	  $dados = $CEmpresa->obter();
	  if($dados == false){
		return false;
	  }
	  $i=0;
	  foreach($dados as $conteudo){
		  $resposta['empresas'][$i]['empresa'] = $conteudo['empresa'];
		  $resposta['empresas'][$i]['filial'] = $conteudo['filial'];
		  $resposta['empresas'][$i]['empresaweb'] = $conteudo['empresa_web'];
		  $resposta['empresas'][$i]['filialweb'] = $conteudo['filial_web'];		  
		  $i++;
	  }
	  return $resposta;
	}

	/* Anexar arquivo no Certificado Digital */	
	function anexarCertificado(){
		echo "o file eh:".$_FILES['file'];

	  /* Pasta onde o arquivo vai ser salvo */
	  $_UP['pasta'] = 'uploads/';
	  /* Tamanho m�ximo do arquivo (em Bytes) */
	  $_UP['tamanho'] = 1024 * 1024 * 1; // 1Mb
	  /* Array com as extens�es permitidas */
	  $_UP['extensoes'] = array('pfx');
	  /* Renomeia o arquivo? (Se true, o arquivo ser� salvo como .jpg e um nome �nico) */
	  
	  /* Array com os tipos de erros de upload do PHP
	  $_UP['erros'][0] = 'N�o houve erro';
	  $_UP['erros'][1] = 'O arquivo no upload � maior do que o limite do PHP';
	  $_UP['erros'][2] = 'O arquivo ultrapassa o limite de tamanho especifiado no HTML';
	  $_UP['erros'][3] = 'O upload do arquivo foi feito parcialmente';
	  $_UP['erros'][4] = 'N�o foi feito o upload do arquivo';
	   
	  /* Verifica se houve algum erro com o upload. Se sim, exibe a mensagem do erro */
	  if($_FILES['file']['error'] != 0) {
		  return "N�o foi poss�vel fazer o upload, erro:<br />" . $_UP['erros'][$_FILES['file']['error']];
	  }
	   
	  /* Faz a verifica��o da extens�o do arquivo */
	  echo "nome certificado:".$_FILES['file']['name'];
	  $extensao = explode('.', $_FILES['file']['name']);
	  echo "extensao:$extensao[0]";
	  if(array_search($extensao[1], $_UP['extensoes']) === false) {
		  return "Por favor, envie arquivos com as seguintes extensao: PFX";
	  }
	  
	  /* Faz a verifica��o do tamanho do arquivo */
	  if ($_UP['tamanho'] < $_FILES['file']['size']) {
		  return "O arquivo enviado � muito grande, envie arquivos de at� 1Mb.";
	  } 
	  
	  /* O arquivo passou em todas as verifica��es, hora de tentar mov�-lo para a pasta */
	  /* Trocar o nome do arquivo */
	  $nome_final = $_POST['ws_cnpj'].'.pfx';
	   
	  /* Depois verifica se � poss�vel mover o arquivo para a pasta escolhida */
	  if (move_uploaded_file($_FILES['file']['tmp_name'], $_UP['pasta'] . $nome_final)) {
	  /* Upload efetuado com sucesso, exibe uma mensagem e um link para o arquivo */
		return true;
	  } else {
	  /* N�o foi poss�vel fazer o upload, provavelmente a pasta est� incorreta */
		return "N�o foi poss�vel enviar o arquivo, tente novamente";
	  }
 	}
?>