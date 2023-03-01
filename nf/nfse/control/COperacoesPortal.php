<?php
	/*
		Programa:				COperacoesPortal.php
		Autor:					Guilherme Silva
		Data:					10/02/2012
		Finalidade: 			Efetuar a comunicaco da tela do portal com a classe view do portal
		Programas chamadores: 	portal.php (view)
		Programas chamados: 	CComunicadorWebService, CXml
	*/

	ini_set("memory_limit","4048M"); // 2 GB



	require_once("/var/www/html/nf/nfse/control/xmlgen.php");

	
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CErro.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");	


	require_once("/var/www/html/nf/nfse/control/CComunicadorWebService.php");
	require_once("/var/www/html/nf/nfse/model/CBd.php");


	
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CItem.php");


	require_once("/var/www/html/nf/nfse/model/CGenerico.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	require_once("/var/www/html/nf/nfse/model/CEmpresa.php");
	require_once("/var/www/html/nf/nfse/model/CBackup.php");


	

	class COperacoesPortal{

		private $grupo;

		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}


		/*public function retornarContribuintes(){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$retorno = $CNotaFiscal->obterContribuintes();
			if(!$retorno){
				if($CNotaFiscal->mensagemErro == ""){
				  $resposta['mensagemErro'] = "";
				}else{
				  $resposta['mensagemErro'] = $CNotaFiscal->mensagemErro;
				}
			  return $resposta;
			}
			$i=0;
			foreach($retorno as $conteudo){
			  $resposta['lista'][$i]['codigo'] = $conteudo['empresa_codigo']+"/"+$conteudo['filial_codigo'];
			  $resposta['lista'][$i]['descricao'] = $conteudo['filial_descricao'];
			  $resposta['lista'][$i]['cnpj'] = $conteudo['prestador_cpf_cnpj'];
			  $i++;
			}
			return $resposta;
		}*/

		public function retornarContribuintes(){
			$CEmpresa = new CEmpresa($this->grupo);
			$retorno = $CEmpresa->obter();
			if(!$retorno){
				if($CNotaFiscal->mensagemErro == ""){
				  $resposta['mensagemErro'] = "";
				}else{
				  $resposta['mensagemErro'] = $CNotaFiscal->mensagemErro;
				}
			  return $resposta;
			}
			$i=0;
			foreach($retorno as $conteudo){
			  $resposta['lista'][$i]['codigo'] = $conteudo['empresa']+"/"+$conteudo['filial'];
			  $resposta['lista'][$i]['descricao'] = $conteudo['razao_social'];
			  $resposta['lista'][$i]['cnpj'] = $conteudo['cnpj'];
			  $i++;
			}
			return $resposta;
		}
		
		public function retornarNotasFiscais($pCNPJ="",$pContLimit=0,$pStatus="", $periodoIni="", $periodoFim=""){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			if($pCNPJ != ""){ $CNotaFiscal->prestador_cpf_cnpj = $pCNPJ; }
			
			$CNotaFiscal->contLimit = $pContLimit;
			$CNotaFiscal->nf_status = $pStatus;
			$CNotaFiscal->periodoIni = $periodoIni;
			$CNotaFiscal->periodoFim = $periodoFim;

			$retorno = $CNotaFiscal->obterNotasFiscais();
			if(!$retorno){
				if($CNotaFiscal->mensagemErro == ""){
				  $resposta['mensagemErro'] = "";
				}else{
				  $resposta['mensagemErro'] = $CNotaFiscal->mensagemErro;
				}
			  return $resposta;
			}
			$i=0;
			foreach($retorno as $conteudo){
			  $resposta['nf'][$i]['codEmpresa'] = $conteudo['empresa_codigo'];
			  $resposta['nf'][$i]['empresa'] = $conteudo['empresa_descricao'];
			  $resposta['nf'][$i]['cnpj'] = $conteudo['prestador_cpf_cnpj'];
			  $resposta['nf'][$i]['codFilial'] = $conteudo['filial_codigo'];
			  $resposta['nf'][$i]['filial'] = $conteudo['filial_descricao'];
			  $resposta['nf'][$i]['status'] = $conteudo['nf_status'];
			  /*
			  	N - Nova
				S - Sucesso
				E - Falha
				C - Cancelada
			  */
			  $resposta['nf'][$i]['controle'] = $conteudo['nf_controle'];
			  $resposta['nf'][$i]['numero'] = $conteudo['nf_numero'];
			  $resposta['nf'][$i]['serie'] = $conteudo['nf_serie'];
			  $resposta['nf'][$i]['cnpjtomador'] = $conteudo['tomador_cpf_cnpj'];
			  $resposta['nf'][$i]['cnpjprestador'] = $conteudo['prestador_cpf_cnpj'];
			  $resposta['nf'][$i]['autenticacao'] = $conteudo['nf_autenticacao'];
			  $resposta['nf'][$i]['emailtomador'] = $conteudo['tomador_email'];
			  $resposta['nf'][$i]['nomerazaosocialtomador'] = $conteudo['tomador_nome_razao_social'];
			  $resposta['nf'][$i]['data'] = date("d/m/Y", strtotime($conteudo['nf_data_emissao']));
			  $resposta['nf'][$i]['hora'] = $conteudo['nf_hora_emissao'];
			  $resposta['nf'][$i]['link'] = $conteudo['nf_link'];
			  $resposta['nf'][$i]['codIBGE'] = $conteudo['prestador_cidade'];
			  $i++;
			}
			return $resposta;
		}

		public function cancelarNotaFiscal($pCnpj, $pControle, $pIBGE, $pDiretorio="", $pChamada="", $pDadosTXT="", $ambiente="", $usuarioPrefeitura="", $senhaPrefeitura=""){

		  $CComunicadorWebService = new CComunicadorWebService($this->grupo);
		  $CComunicadorWebService->prestadorCnpj = $pCnpj;
		  $CComunicadorWebService->numeroControle = $pControle;
		  $CComunicadorWebService->codigoIBGE = $pIBGE;
		  
		  $CComunicadorWebService->usuarioPrefeitura = $usuarioPrefeitura;
		  $CComunicadorWebService->senhaPrefeitura = $senhaPrefeitura;
		  $CComunicadorWebService->codigoTom = $pIBGE;


	
		  if(!$CComunicadorWebService->comunicarWebService("C", $pDiretorio, $pChamada, $pDadosTXT, $ambiente)){
			$resposta['mensagemErro'] = $CComunicadorWebService->mensagemErro;

			/*
			if($pChamada == "COBOL")
			{
				$conteudo_arquivo_retorno = "";

				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->prestadorCnpj)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->codigoIBGE)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->data)."||";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->serieNota)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->numeroNota)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->status)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->mensagemErro)."||||";

				//@file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
			}
			*/

			return $resposta;
		  }

		  /*
		  if($pChamada == "COBOL")
		  {
				$conteudo_arquivo_retorno = "";

				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->prestadorCnpj)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->codigoIBGE)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->data)."||";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->serieNota)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->numeroNota)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->status)."|";
				$conteudo_arquivo_retorno .= trim($CComunicadorWebService->mensagemErro)."||||";

				//@file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
		  }
		  */


		  if($CComunicadorWebService->status == "N"){
			$resposta['mensagemErro'] = utf8_encode($CComunicadorWebService->mensagemErro);
			return $resposta;
		  }


//		  return $this->atualizarCobol($pCnpj, $pControle, $CComunicadorWebService->numeroNota, $CComunicadorWebService->serieNota, $pChamada);
		  

		  
		}
		
		public function retornarXml($pCnpj, $pControle, $pIBGE){
			$CXml = new CXml($this->grupo);
			switch(ltrim($pIBGE,0)){
				case 4101804:
					if(!$CXml->wsAraucaria()){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				case 4106902:
					if(!$CXml->xmlCuritiba($pCnpj, $pControle)){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				break;
				case 4119152:
				case 4104204:				
					if(!$CXml->xmlPinhais($pCnpj, $pControle)){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				break;
				// Maringa
				case 4115200:
					if(!$CXml->xmlMaringa($pCnpj, $pControle)){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				break;
				// Foz do Iguacu
				case '004108304':
					if(!$CXml->xmlFozDoIguacu($pEmpresa, $pFilial, $pControle)){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				break;
				// Foz do Iguacu
				case 4127106:
					if(!$CXml->xmlTelemacoBorba($pCnpj, $pControle)){
					  $resposta['mensagemErro'] = $CXml->mensagemErro;
					}else{ $resposta['xml'] = $CXml->xml; }
				break;
				default:
					$resposta['mensagemErro'] = " COperacoesPortal->retornarXml {Codigo do IBGE [".$this->codigoIBGE."] nao cadastrado} ";
				break;
			}
			return $resposta;
		}

		public function retornarCriticas($pCnpj, $pControle){
			$CCritica = new CCritica($this->grupo);
			$retorno = $CCritica->obterCritica($pCnpj, $pControle);
			if(!$retorno){
				$resposta['mensagemErro'] = $CNotaFiscal->mensagemErro;
				return $resposta;
			}
			$i=0;
			foreach($retorno as $conteudo){
			  $resposta['cri'][$i]['descricao'] = utf8_decode($conteudo['descricao']);
			  $resposta['cri'][$i]['data'] = $conteudo['data'];
			  $resposta['cri'][$i]['hora'] = $conteudo['hora'];
			  $i++;
			}
			return $resposta;			
		}
		
		public function atualizarCobol($pCnpj, $pControle, $pNumeroNota, $pSerieNota){
		  // Verificar se existe no CGI-BIN uma subpasta com o nome do grupo
		  if(file_exists("/var/www/cgi-bin/".$this->grupo)){
			$caminhoCgi = "/var/www/cgi-bin/".$this->grupo;
		  }else{
			$caminhoCgi = "/var/www/cgi-bin";
		  }
		  
		  putenv("LD_LIBRARY_PATH=/usr/cobol_mf_4.1/coblib");
		  putenv("COBDIR=/usr/cobol_mf_4.1");
		  putenv("COBPATH=/user/objetos");
		  putenv("COBSW=-F-l-Q");
		  putenv("EXTFH=/user/bindib/extfh.cfg");
		  putenv("TERM=linux");
		  putenv("HOME=".$caminhoCgi);

		  $CEmpresa = new CEmpresa($this->grupo);
		  $CEmpresa->cnpj = $pCnpj;
		  $retorno = $CEmpresa->obterEmpresa();
		  if($retorno == false){
			echo "erro ao obter a empresa e filial, emp:".$pEmpresa." filial:".$pFilial;
			//$resposta['mensagemErro'] = $CErro->stringErros;
			return $resposta;
		  }

	      $parametros = "SVE352    ".str_pad("", 361," ").
		  				str_pad($retorno[0]['empresa'], 3,"0", STR_PAD_LEFT).
		  				str_pad($retorno[0]['filial'], 3,"0", STR_PAD_LEFT).
		  				str_pad($retorno[0]['empresa_web'], 3,"0", STR_PAD_LEFT).
		  				str_pad($retorno[0]['filial_web'], 3,"0", STR_PAD_LEFT).
		  				str_pad($pNumeroNota, 7,"0", STR_PAD_LEFT).
		  				str_pad($pSerieNota, 3," ", STR_PAD_RIGHT).
		  				str_pad($pControle, 7,"0", STR_PAD_LEFT);
		  putenv('PARAMETROS='.$parametros);
  
		  @file_put_contents("/var/www/html/web/download/NFSECanc.txt", date('d-m-Y h:i:s').'PARAMETROS='.$parametros."\n");

		  $retornoCobol = shell_exec("TERM=linux export TERM; cd ".$caminhoCgi." ; /usr/cobol_mf/cob41/bin/cobrun -F-l-Q /user/objetos/GERWEB.int web 2>&1");
		  $retornoCobol = str_replace(";", ":", $this->retornoCobol);
		 /* $CErro = new CErro($this->grupo);
		  $retornoCobol = $CErro->MChecarDisplayCobol($retornoCobol);
		  $retornoCobol = $CErro->MLimparErrosComunicador($retornoCobol);
		  $retornoErros = $CErro->stringErros;
			
		  if($CErro->stringErros != ""){
			  echo "retorno erro:".$CErro->stringErros;
			$resposta['mensagemErro'] = $CErro->stringErros;
		  }else{*/
			$resposta['mensagem'] = "Cancelamento efetuado com sucesso!";
		 // }
		  return $resposta;		  
		}
		
		public function enviarEmail($pCnpj, $pControle, $pEmail){
			$CEmail = new CEmail($this->grupo);
			if(!$CEmail->enviarNF($pCnpj, $pControle, $pEmail)){
			  $resposta['mensagemErro'] = $CEmail->mensagemErro;
			}else{
			  $resposta['mensagem'] = "Email enviado com Sucesso!";
			}
			return $resposta;
		}

		public function efetuarBackup($pNome){
			$CConfig = new CConfig();
			$CBackup = new CBackup();
			$data = date("Ymd");
			$hora = date("His");

			if(!$CConfig->lerArquivo()){
			  $resposta['mensagemErro'] = $CEmail->mensagemErro;
			  return $resposta;
			}
			
			$diretorio = shell_exec("pwd");
			$diretorio = substr($diretorio,0,$diretorio.lenght-1);
			$comando = "mysqldump --host=".$CConfig->configWs['servidor']." --user=".$CConfig->configWs['usuario']." --password=".$CConfig->configWs['senha'].
						" --extended-insert --databases ".$CConfig->configWs['base']." > "."/user/nfse/backup/".$pNome."-".$data."-".$hora.".sql";
			if(shell_exec($comando) != ""){
			  $resposta['mensagemErro'] = "COperacoesPortal->efetuarBackup{ foi identificado um erro na geracao do backup, certifique-se que a pasta /user/nfse/backup esta criada e que tem permissao de escrita";
			  return $resposta;
			}
			
			$comando = "cp /user/nfse/backup/".$pNome."-".$data."-".$hora.".sql /var/www/html/nf/nfse/backup/";
						" --extended-insert --databases ".$CConfig->configWs['base']." > "."/user/nfse/backup/".$data."-".$hora.".sql";
			if(shell_exec($comando) != ""){
			  $resposta['mensagemErro'] = "COperacoesPortal->efetuarBackup{ foi identificado um erro na geracao do backup, certifique-se que a pasta /user/nfse/backup esta criada e que tem permissao de escrita";
			  return $resposta;
			}

			$link = "/nfse/backup/".$pNome."-".$data."-".$hora.".sql";

			$ultimoBackup = $CBackup->inserirBackup($pNome, $data, $hora, $link);
			if(!$ultimoBackup){
			  $resposta['mensagemErro'] = $CBackup->mensagemErro;
			}else{
			  $resposta['mensagem'] = "Backup efetuado com sucesso, seu BD foi salvo no /user/nfse/backup/".$pNome."-".$data."-".$hora.".sql";
			}
			return $resposta;
		}

		public function verBackup(){
			$CBackup = new CBackup();
			$retorno = $CBackup->obterBackup();
			if(!$retorno){
				if($CBackup->mensagemErro == ""){
				  $resposta['mensagemErro'] = "";
				}else{
				  $resposta['mensagemErro'] = $CNotaFiscal->mensagemErro;
				}
			  return $resposta;
			}

			$i=0;
			foreach($retorno as $conteudo){
			  $resposta['backup'][$i]['nome'] = $conteudo['nome'];
			  $datahora = substr($conteudo['data'],6,2)."/".substr($conteudo['data'],4,2)."/".substr($conteudo['data'],0,4);
			  $datahora .= " - ".substr($conteudo['hora'],0,2).":".substr($conteudo['hora'],2,2).":".substr($conteudo['hora'],4,2);
			  $resposta['backup'][$i]['datahora'] = $datahora;
			  $resposta['backup'][$i]['link'] = $conteudo['link'];
			  $i++;
			}
			$resposta['dataatual'] = date('d/m/Y - H:m:s');
			return $resposta;
		}
	}
?>