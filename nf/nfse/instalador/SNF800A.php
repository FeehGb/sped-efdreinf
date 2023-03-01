<?php
	/*
		Programa: funcoes.php
		Finalidade: Efetua as funcoes de instalacao do sistem NFS-e
		Autor: Guilherme Silva
		Data: 03/02/2012
		Chamador: SNF800.js
		Chamados: adodb.inc.php
	*/
	//error_reporting(0);

	require_once("/var/www/html/nf/nfse/model/adodb/adodb.inc.php");
    require("../control/phpmailer/class.phpmailer.php");
    require_once("/var/www/html/nf/nfse/model/CBd.php");
	require_once("/var/www/html/nf/nfse/model/CEmpresa.php");
	require_once("/var/www/html/nf/nfse/model/CLote.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");

	$funcao 		= $_POST['hFuncao'];
	$sqlServidor	= trim($_POST['tSQLServidor']);
	$sqlUsuario 	= trim($_POST['tSQLUsuario']);
	$sqlSenha 		= trim($_POST['tSQLSenha']);
	$retorno;

	$filialGrupo = str_replace(" ","",strtolower($_POST['hFilialGrupo']));
	$empresaGrupo = str_replace(" ","",strtolower($_POST['tEmpresasGrupo']));
	

	switch($funcao){
		case "ACESSAR-MYSQL":

			$link = @mysqli_connect($sqlServidor, $sqlUsuario, $sqlSenha,'');

			if(!$link || trim($sqlUsuario) == "" || trim($sqlSenha) == ""){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "Ops! Parece que o nome do servidor, usuario ou senha incorretos!";
			}else{
				$result = mysqli_query($link,'show databases like "nfse_config"'); 
				if(!$result){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Ixi! Houve um erro ao tentar buscar a base de dados de configurcao, verifique se o MySQL esta instalado corretamente!";
				}else{
					$array = mysqli_fetch_assoc($result);
					if($array["Database (nfse_config)"] == ""){
						// Banco de Dados padrao nao criado, chamar funcao para criar
						if(!fLocalCriarBDNfseConfig($sqlServidor, $sqlUsuario, $sqlSenha)){
							$retorno['retorno'] = false;
							$retorno['mensagem'] = "Ixi! Houve um erro ao criar a base de dados <nfse_config>\n\n Verifique as pendencias no seu Banco de Dados!";
							echo json_encode($retorno);
							exit();
						}
						if(!fLocalCriaArquivoConfig($sqlServidor, $sqlUsuario, $sqlSenha)){
							$retorno['retorno'] = false;
							$retorno['mensagem'] = "Nao foi possivel criar o arquivo /var/www/html/nf/nfse/config.ini \n\n Verifique as permissoes da pasta. \n\n Sem este arquivo nao sera possivel continuar com a instalacao!";
						}else{
							$retorno['retorno'] = true;
							$retorno['mensagem'] = "Criado o arquivo /var/www/html/nf/nfse/config.ini com as devidas credenciais do MySQL e base nfse_config!";
						}
					}elseif($array["Database (nfse_config)"] == "nfse_config"){
						if(!fLocalCriaArquivoConfig($sqlServidor, $sqlUsuario, $sqlSenha)){
							$retorno['retorno'] = false;
							$retorno['mensagem'] = "Nao foi possivel criar o arquivo /var/www/html/nf/nfse/config.ini \n Verifique as permissoes da pasta. \n\n Sem este arquivo nao sera possivel continuar com a instalacao!";
						}else{
							$retorno['retorno'] = true;
							$retorno['mensagem'] = "Recriado o arquivo /var/www/html/nf/nfse/config.ini com as devidas credenciais do MySQL!";
						}
					}else{
						$retorno['retorno']  = false;
						$retorno['mensagem'] = "Ixi! Houve um erro ao tentar buscar a base de dados de configurcao, verifique se o MySQL esta instalado corretamente!";
					}
				}
			}
			echo json_encode($retorno);
			exit();
		break;
		case "LISTAR-EMPRESAS":
			$link = fLocalConectarViaConfig();
			if(!$link){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "Erro ao conectar no MySQL via arquivo de configuracao. \n\n Verifique a integridade do arquivo /var/www/html/nf/nfse/config.ini e tamb�m a integridade do seu Banco de Dados!";
			}else{ 
				$result = mysqli_query($link,'SELECT * FROM empresas');
				if(!$result){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Erro ao obter os resultados da tabela Empresas!";
					echo json_encode($retorno);
					exit();
				}

				$i=0;
				while ($row = mysqli_fetch_assoc($result)) {
					$array[$i] = $row; $i++;
				}
				$retorno = $array;
				if($i==0){
					$retorno['retorno'] = true;
					$retorno['mensagem'] = "";
				}

			}
			echo json_encode($retorno);
			exit();
		break;
		
		case "ACESSAR-CADASTRO-EMPRESA":
			$link = fLocalConectarViaConfig();
			if(!$link){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "Erro ao conectar no MySQL via arquivo de configuracao. \n\n Verifique a integridade do arquivo /var/www/html/nf/nfse/config.ini e tambem a integridade do seu Banco de Dados!";
			}else{ 
				$result = mysqli_query($link, 'SELECT * FROM empresas WHERE cnpj = "'.$_POST['tEmpresasCNPJ'].'" LIMIT 1');
				
				if(!$result){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Erro ao obter os resultados da tabela Empresas!";
					echo json_encode($retorno);
					exit();
				}

				$i=0;
				$retorno = mysqli_fetch_assoc($result);
				if (count($retorno) == 0) {
					$retorno['retorno']  = false;
					$retorno['mensagem'] =  'Nao foi encontrado nenhum registro do CNPJ '.$_POST['tEmpresasCNPJ'];
				}
			}
			echo json_encode($retorno);
			exit();
		break;

		case "GRAVAR-EMPRESA":
			$link = fLocalConectarViaConfig();
			if(!$link){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "Erro ao conectar no MySQL via arquivo de configura��o. \n\n Verifique a integridade do arquivo /var/www/html/nf/nfse/config.ini e tamb�m a integridade do seu Banco de Dados!";
			}else{ 
				// Criar registro na tabela empresas do BD nfse_config
				$bd = "nfse_".$empresaGrupo;
				$bd_nfe = "nfe_".$empresaGrupo;
				$bd_mdfe = "mdfe_".$empresaGrupo;
				$sql = "INSERT INTO empresas (cnpj, nome, bd, bd_nfe, bd_mdfe, nome_grupo)
									  VALUES ('".$_POST['tEmpresasCNPJ']."','".$_POST['tEmpresasNome']."','".$bd."','".$bd_nfe."','".$bd_mdfe."','".$empresaGrupo."')
						ON DUPLICATE KEY UPDATE
							cnpj='".$_POST['tEmpresasCNPJ']."',
							nome='".$_POST['tEmpresasNome']."',
							bd='".$bd."',
							bd_nfe='".$bd_nfe."',
							bd_mdfe='".$bd_mdfe."',
							nome_grupo='".$empresaGrupo."'";
						
				$result = mysqli_query($link, $sql);
				if(!$result){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Erro ao incluir a empresa:".mysqli_error();
					echo json_encode($retorno);
					exit();
				}

				// Chama a rotina para criar a Database e Tables caso n�o exista

				if(!fLocalCriarBDFiliais($empresaGrupo)){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Erro ao criar o database ".$bd." e as tabelas do mesmo!";
					echo json_encode($retorno);
					exit();
				}

				$retorno['retorno'] = true;
				$retorno['mensagem'] = "Dados atualizados com sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		
		case "EXCLUIR-EMPRESA":
			$link = fLocalConectarViaConfig();
			if(!$link){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "Erro ao conectar no MySQL via arquivo de configura��o. \n\n Verifique a integridade do arquivo /var/www/html/nf/nfse/config.ini e tamb�m a integridade do seu Banco de Dados!";
			}else{ 
				// Deletar o cadastro de empresas na base nfse_config
				$sql = "DELETE FROM empresas WHERE cnpj = '".$_POST['tEmpresasCNPJ']."'";
						
				$result = mysqli_query($link, $sql);
				if(!$result){
					$retorno['retorno']  = false;
					$retorno['mensagem'] = "Erro ao obter os resultados da tabela Empresas!";
					echo json_encode($retorno);
					exit();
				}

				$retorno['retorno'] = true;
				$retorno['mensagem'] = "Dados excluidos com sucesso!";
			}
			echo json_encode($retorno);
			exit();
		break;
		
		
		case "LISTAR-FILIAIS":
			$CEmpresa = new CEmpresa($filialGrupo);
			$resposta = $CEmpresa->obter();

			if(!$resposta){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = $CEmpresa->mensagemErro;
			}else{
				$retorno = $resposta; 
			}

			if(is_null($resposta)){
				$retorno['retorno']=true;
				$retorno['mensagem']="";
			}

			echo json_encode($retorno);
			exit();
		break;

		case "ACESSAR-CADASTRO-FILIAIS":
			$CEmpresa = new CEmpresa($filialGrupo);
			$CEmpresa->grupo = $filialGrupo;

			$CEmpresa->cnpj = $_POST['tFiliaisCNPJ'];
			$resposta = $CEmpresa->obter();

			if(!$resposta){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = $CEmpresa->mensagemErro;
			}else{
				$retorno = $resposta; 
			}

			if(is_null($resposta)){
				$retorno['retorno']=true;
				$retorno['mensagem']="";
			}

			echo json_encode($retorno);
			exit();
		break;

		case "GRAVAR-FILIAL":
			$CEmpresa 	= new CEmpresa($filialGrupo);
			$CLote 		= new CLote($filialGrupo);

			// Anexar certificado no sistema

			// Verificar validade do certificado

			// Passar parametros
			$CEmpresa->grupo 				= $filialGrupo;
			$CEmpresa->empresa				= str_pad($_POST['tFiliaisEmpresa'],3,"0",STR_PAD_LEFT);
			$CEmpresa->filial				= str_pad($_POST['tFiliaisFilial'],3,"0",STR_PAD_LEFT);
			$CEmpresa->empresa_web			= str_pad($_POST['tFiliaisEmpresaWeb'],3,"0",STR_PAD_LEFT);
			$CEmpresa->filial_web			= str_pad($_POST['tFiliaisFilialWeb'],3,"0",STR_PAD_LEFT);
			$CEmpresa->cnpj					= $_POST['tFiliaisCNPJ'];
			$CEmpresa->razao_social			= $_POST['tFiliaisNome'];
			$CEmpresa->email_smtp			= $_POST['tFiliaisEmailSMTP'];
			$CEmpresa->email_porta			= $_POST['tFiliaisEmailPorta'];
			$CEmpresa->email_usuario		= $_POST['tFiliaisEmailUsuario'];
			$CEmpresa->email_senha 			= $_POST['tFiliaisEmailSenha'];
			$CEmpresa->email_conexao 		= $_POST['tFiliaisEmailConexao'];
			$CEmpresa->codigo_tom_cidade 	= $_POST['tFiliaisWSTom'];

			if(file_exists("/var/www/html/nf/nfse/certificados/".$_POST['tFiliaisCNPJ'].".pfx")){
				$CEmpresa->certificado_pfx 	= "/var/www/html/nf/nfse/certificados/".$_POST['tFiliaisCNPJ'].".pfx";
			}else{
				$CEmpresa->certificado_pfx 	= "";
			}

			$CEmpresa->senha_pfx 			= $_POST['tFiliaisWSSenha'];

			$CEmpresa->validade_certificado	= $_POST['tFiliaisWSValidade'] == "" ? "1980-01-01" : $_POST['tFiliaisWSValidade'];
			$CEmpresa->proxy_porta          = $_POST['tFiliaisProxyPorta'] == "" ? "8080" : $_POST['tFiliaisProxyPorta'];


			$CEmpresa->usuario_prefeitura 	= $_POST['tFiliaisWSUsuarioPrefeiura'];
			$CEmpresa->senha_prefeitura 	= $_POST['tFiliaisWSPasswordPrefeiura'];
			$CEmpresa->flag_producao		= $_POST['tFiliaisWSFlagProducao'];
			$CEmpresa->proxy 				= $_POST['tFiliaisProxy'];
			$CEmpresa->proxy_servidor 		= $_POST['tFiliaisProxyServidor'];
			//$CEmpresa->proxy_porta 			= $_POST['tFiliaisProxyPorta'];
			$CEmpresa->proxy_usuario 		= $_POST['tFiliaisProxyUsuario'];
			$CEmpresa->proxy_senha 			= $_POST['tFiliaisProxySenha'];

			$resposta = $CEmpresa->inserir();

			if(!$resposta){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = $CEmpresa->mensagemErro;
				echo json_encode($retorno);
				exit();
			}

			$retorno['retorno']=true;
			$retorno['mensagem']="Dados da Empresa Atualizados com Sucesso!";
			
			// Inserir numeracao do Lote para o contribuinte
			//$CLote->inserirLote($_POST['tFiliaisCNPJ'], "0", "0");

			echo json_encode($retorno);
			exit();
		break;

		case "EXCLUIR-FILIAL":
			$CEmpresa = new CEmpresa($filialGrupo);

			// Passar parametros
			$CEmpresa->grupo = $filialGrupo;
			$CEmpresa->cnpj	 = $_POST['tFiliaisCNPJ'];

			$resposta = $CEmpresa->excluir();

			if(!$resposta){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = $CEmpresa->mensagemErro;
			}

			$retorno['retorno']=true;
			$retorno['mensagem']="Contribuinte excluido com Sucesso!";

			echo json_encode($retorno);
			exit();
		break;

		// FAZER O TESTE DO ENVIO DE EMAIL
		case "TESTAR-EMAIL":
			$retFunc = testarConexaoEmail();
			if(!$retFunc){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = $CEmpresa->mensagemErro;
			}else{
				$retorno['retorno'] = true;
				$retorno['mensagem']="Teste de Email realizado com Sucesso, verifique a caixa de e-mail informada!";
			}

			echo json_encode($retorno);
			exit();
		break;
		
		case "TESTAR-VALIDADE-CERTIFICADO":
			$CAssinaturaDigital = new CAssinaturaDigital();
			if(!file_exists("/var/www/html/nf/nfse/certificados/".$_POST['tFiliaisCNPJ'].".pfx")){
				$retorno['retorno'] = false;
				$retorno['mensagem'] = "O certificado digital nao foi encontrado no diretorio:\n\n"."/var/www/html/nf/nfse/certificados/".$_POST['tFiliaisCNPJ'].".pfx";
			}else{
				$CAssinaturaDigital->cnpj = $_POST['tFiliaisCNPJ'];
				//carrega os certificados e chaves para um array denominado $x509certdata
				if (!openssl_pkcs12_read(file_get_contents("/var/www/html/nf/nfse/certificados/".$_POST['tFiliaisCNPJ'].".pfx"),$x509certdata,$_POST['tFiliaisWSSenha'])){
					$retorno['mensagem'] = "Certificado digital nao pode ser lido, verifique se a senha est� correta";
					$retorno['retorno'] = false;
					echo json_encode($retorno);
					exit();
				}

				if(!$CAssinaturaDigital->validaCertificado($x509certdata['cert'])){
					$retorno['retorno'] = false;
					$retorno['mensagem'] = $CAssinaturaDigital->mensagemErro;
				}else{
					$retorno['retorno'] = true;
					$retorno['mensagem']="Certificado valido com Sucesso!\n\nClique em gravar para salvar a data de validade.";
					$retorno['dataValidade'] = "20".$CAssinaturaDigital->validadeAno."-".$CAssinaturaDigital->validadeMes."-".$CAssinaturaDigital->validadeDia;
				}
			}
			echo json_encode($retorno);
			exit();
		break;
		
		//TODO:: FAZER TAMB�M A QUEST�O DE ANEXAR O CERTIFICADO
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
		global $empresaGrupo;
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
		global $empresaGrupo;
		$diretorio = shell_exec("pwd");
		$diretorio = substr($diretorio,0,$diretorio.lenght-1);
		
		if(substr(phpversion(),0,1) >= "7"){
			$comando = "mysql --defaults-extra-file=/etc/mysql/debian.cnf --database=$_POST[banco] < $diretorio/tabelas.sql ";
		}else{
		    $comando = "mysql --host=".$_POST['servidor']." --user=".$_POST['usuario']." --password=".$_POST['senha']." ".$_POST['banco']." < ".$diretorio."/tabelas.sql";
		}


		$result = shell_exec($comando);
		if($result == ""){
			return true;
		}else{
			return false;
		}
	}
	
	function backupBD(){
		global $empresaGrupo;
		$diretorio = shell_exec("pwd");
		$diretorio = substr($diretorio,0,$diretorio.lenght-1);
		$comando = "mysqldump --host=".$_POST['servidor']." --user=".$_POST['usuario']." --password=".$_POST['senha']." --databases ".$_POST['banco']." > ".$diretorio."/teste.sql";
		$result = shell_exec($comando);
	}
	
	function testarConexaoEmail(){
	  global $empresaGrupo;
	  $mail = new PHPMailer(true);
      $mail->IsSMTP();
      $mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
	  ##TLS Somente para contas do gmail
	  switch($_POST['tFiliaisEmailConexao']){
		  case "TLS":
			  $mail->SMTPSecure = "tls";
		  break;
		  case "SSL":
			  $mail->SMTPSecure = "ssl";
		  break;
	  }
      $mail->Port = $_POST['tFiliaisEmailPorta']; // Default 587
      $mail->Host = $mail->Hostname = $_POST['tFiliaisEmailSMTP'];
	  if($_POST['tFiliaisEmailConexao'] == "EXCHANGE"){
		$usuario = explode("@",$_POST['tFiliaisEmailUsuario']);
		$mail->Username = $usuario[0];
	  }
      $mail->Password = $_POST['tFiliaisEmailSenha'];
      $mail->SMTPDebug = 1; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao

      $mail->AddReplyTo = $_POST['tFiliaisEmailUsuario'];;

      $mail->From = $_POST['tFiliaisEmailUsuario']; // Email de origem
      $mail->FromName = "Teste de Envio Nota Fiscal Eleltronica Softdib"; // Nome de Origem

      $to = $_POST['tFiliaisEmailUsuario'];
      $mail->AddAddress($_POST['tFiliaisEmailUsuario']);

      $mail->Subject = "Instalador NFS-e da Softdib (Envio de teste de email)"; // Assunto
      $mail->WordWrap = 80; // Caracteres por linha
      $body = "O seu teste de envio de e-mail foi efetuado com sucesso, continue a configuracao do sistema!";
      $body = preg_replace('/\\\\/','', $body);
      $mail->MsgHTML($body); //corpo
      $mail->IsHTML(true);
	  if(!$mail->Send()){
		 echo "Houve erro no envio da mensagem!";
	  }else{
		  return true;
	  }
	}
	
	function salvarConfiguracoesEmail(){
		global $empresaGrupo;
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
		global $empresaGrupo;
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
		global $empresaGrupo;
		$indice = $_POST['hIndice'];
		$CEmpresa = new CEmpresa();
		if(!$CEmpresa->inserir($_POST['empresa'.$indice], $_POST['filial'.$indice], $_POST['empresa_web'.$indice], $_POST['filial_web'.$indice])){
			return $CEmpresa->mensagemErro;
		}else{
			return true;
		}
	}
	
	function excluirRelacionamento(){
	  global $empresaGrupo;
	  $indice = $_POST['hIndiceExcluir'];
	  $CEmpresa = new CEmpresa();
	  if(!$CEmpresa->excluir($_POST['empresa'.$indice], $_POST['filial'.$indice])){
		  return $CEmpresa->mensagemErro;
	  }else{
		  return true;
	  }
	}
	
	function listarRelacionamento(){
	  global $empresaGrupo;
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
		global $empresaGrupo;
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
	
	
	function fLocalCriarBDNfseConfig($pServidor, $pUsuario, $pSenha){
		global $empresaGrupo;
		$mysqlImportFilename ='/var/www/html/nf/nfse/instalador/nfse_config.sql';

		//Comando Shell para importar instru��o do nfse_config.sql para dentro do MySQL
		if(substr(phpversion(),0,1) >= "7"){
			$command = "mysql --defaults-extra-file=/etc/mysql/debian.cnf --database=mysql < $mysqlImportFilename; ";
		}else{
			$command = 'mysql --host='.$pServidor.' --user='.$pUsuario.' --password='.$pSenha.' mysql < ' .$mysqlImportFilename;
		}

		$retorno = system($command);
		if($retorno == ""){
			return false;
		}

		return true;
	}
	
	// Cria o arquivo de configuracao config.ini no diretorio /var/www/html/nfse/
	function fLocalCriaArquivoConfig($pServidor, $pUsuario, $pSenha){
		global $empresaGrupo;
		$arq = "; Credenciais do Servidor no Banco de Dados!\n";
		$arq .= "servidor=".$pServidor."\n";
		$arq .= "usuario=".$pUsuario."\n";
		$arq .= "senha=".$pSenha."\n";
		$arq .= "base=nfse_config\n";
		$arq .= "sgbd=mysql\n";

		if(!file_put_contents("/var/www/html/nf/nfse/config.ini",$arq)){
			return false;
		}
		return true;
	}
	
	function fLocalConectarViaConfig(){
		global $empresaGrupo,$link;
		$file = parse_ini_file("/var/www/html/nf/nfse/config.ini");
		if(!$file){
			return false;
		}

		$link = mysqli_connect($file['servidor'], $file['usuario'], $file['senha'],'');
		if(!$link){
			return false;
		}

		if(!mysqli_select_db($link, $file['base'])){
			return $link;
		}

		return $link;
	}

	function fLocalCriarBDFiliais($pBd){
		global $empresaGrupo;
		$file = parse_ini_file("/var/www/html/nf/nfse/config.ini");
		if(!$file){
			return false;
		}

		/*-----------------NOTA FISCAL ELETRONICA DE SERVICO----*/
		
		$mysqlImportFilename = file_get_contents('/var/www/html/nf/nfse/instalador/nfse_grupo.sql');

		$fileString = str_replace("<grupo>", $pBd, $mysqlImportFilename);

		if(!file_put_contents('/var/www/html/nf/nfse/instalador/nfse_grupo_tmp.sql', $fileString)){
			return false;
		}

		$tmpFile = '/var/www/html/nf/nfse/instalador/nfse_grupo_tmp.sql';

		//Comando Shell para importar instru��o do nfse_config.sql para dentro do MySQL
		if(substr(phpversion(),0,1) >= "7"){
			$command = "mysql --defaults-extra-file=/etc/mysql/debian.cnf --database=mysql < $tmpFile; ";
		}else{
			$command = 'mysql --host='.$file['servidor'].' --user='.$file['usuario'].' --password='.$file['senha'].' mysql < ' .$tmpFile;
		}

		$retorno = system($command);
		if($retorno = ""){
			return false;
		}
		
		/*-----------------NOTA FISCAL ELETRONICA----*/

		$mysqlImportFilename = file_get_contents('/var/www/html/nf/nfse/instalador/nfe_grupo.sql');

		$fileString = str_replace("<grupo>", $pBd, $mysqlImportFilename);

		if(!file_put_contents('/var/www/html/nf/nfse/instalador/nfe_grupo_tmp.sql', $fileString)){
			return false;
		}

		$tmpFile = '/var/www/html/nf/nfse/instalador/nfe_grupo_tmp.sql';

		//Comando Shell para importar instru��o do nfse_config.sql para dentro do MySQL
		if(substr(phpversion(),0,1) >= "7"){
			$command = "mysql --defaults-extra-file=/etc/mysql/debian.cnf --database=mysql < $tmpFile; ";
		}else{
			$command = 'mysql --host='.$file['servidor'].' --user='.$file['usuario'].' --password='.$file['senha'].' mysql < ' .$tmpFile;
		}

		$retorno = system($command);
		if($retorno = ""){
			return false;
		}
		
		/*-----------------Manifesto Eletr�nico de Documentos----*/

		$mysqlImportFilename = file_get_contents('/var/www/html/nf/nfse/instalador/mdfe_grupo.sql');

		$fileString = str_replace("<grupo>", $pBd, $mysqlImportFilename);

		if(!file_put_contents('/var/www/html/nf/nfse/instalador/mdfe_grupo_tmp.sql', $fileString)){
			return false;
		}

		$tmpFile = '/var/www/html/nf/nfse/instalador/mdfe_grupo_tmp.sql';

		//Comando Shell para importar instru��o do nfse_config.sql para dentro do MySQL
		if(substr(phpversion(),0,1) >= "7"){
			$command = "mysql --defaults-extra-file=/etc/mysql/debian.cnf --database=mysql < $tmpFile; ";
		}else{
		    $command = 'mysql --host='.$file['servidor'].' --user='.$file['usuario'].' --password='.$file['senha'].' mysql < ' .$tmpFile;
		}
		$retorno = system($command);
		if($retorno = ""){
			return false;
		}

		return true;
	}

?>