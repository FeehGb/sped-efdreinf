<?php
	
	error_reporting(E_ALL);
	
	function zulianLog($curl, $header, $xml)
	{
		$log = array();
		
		
		print_r($curl); exit();
		
		
		$infos = curl_getinfo($curl);
		
		// $infos['CURLOPT_SSLCERT'    ] = curl_getinfo($curl, CURLOPT_SSLCERT    ) ; 
		// $infos['CURLOPT_SSLKEY'     ] = curl_getinfo($curl, CURLOPT_SSLKEY     ) ; 
		// $infos['CURLOPT_POST'       ] = curl_getinfo($curl, CURLOPT_POST       ) ; 
		// $infos['CURLOPT_POSTFIELDS' ] = curl_getinfo($curl, CURLOPT_POSTFIELDS ) ; 
		
		$line   = str_repeat("~", 20) ; 
		$header = "$line $header $line" ; 
		
		
		$doc = new DOMDocument('1.0');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($xml);
		$xmlf= $doc->saveXML();
		
		
		
		$log[] =                           $header ; 
		$log[] =              print_r($infos,true) ; 
		$log[] =                             $xmlf ; 
		$log[] = str_repeat("~" , strlen($header)) ; 
		$log[] = str_repeat("~" , strlen($header)) ; 
		$log[] = str_repeat("~" , strlen($header)) ; 
		$log[] = str_repeat("~" , strlen($header)) ; 
		$log[] = str_repeat("~" , strlen($header)) ; 
		$log[] =                                "" ; 
		
		
		
		
		
		$log = implode("\n", $log);
		
		$path = "/var/tmp/logNFSE_CURL_" . date('Y-m-d') . ".txt" ; 
		file_put_contents( 
			$path        , 
			$log         , 
			FILE_APPEND  ) 
		;
		
		
		
		
		
		
		
		
		
		
		
		
		
		$debug     =                                    debug_backtrace() ; 
		$debugPath = "/var/tmp/logNFSE_DEBUG_" . date('Y-m-d') . ".txt" ; 
		
		$debug = print_r($debug, true) ; 
		// $debug = json_encode($debug);
		
		file_put_contents(
			$debugPath    
			,$debug       
			// ,FILE_APPEND  
		) ;
		
		
	}
	
	
	
	
	/*
		Classe:			        CComunicadorWebSerice.php
		Autor:			        Guilherme Silva
		Data:		            07/02/2012
        .                       03/12/2016 - Guilherme Silva - Adicionado Campo Magro
		Finalidade: 		    Verificar se o padrao XML esta correto
		Programas chamadores:   CArquivoComunicacao.php
		Programas chamados:   	
	*/
	
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");	
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");

	/* 
		Importando a classe das cidades
	*/ 

	require_once("/var/www/html/nf/nfse/control/prefeituras/CFozDoIguacu.php");
	require_once("/var/www/html/nf/nfse/control/prefeituras/CCuritiba.php");
	require_once("/var/www/html/nf/nfse/control/prefeituras/CPinhais.php");
	require_once("/var/www/html/nf/nfse/control/prefeituras/CMaringa.php");
	require_once("/var/www/html/nf/nfse/control/prefeituras/CTelemacoBorba.php");
	require_once("/var/www/html/nf/nfse/control/prefeituras/CCampoMagro.php");

	class CComunicadorWebService{

		/*	Atributos internos de comunicacao com o WebService */
		private $grupo;
		private $ConfigWs;
		private $WSUrl;
		private $WSMetodo;
		private $WSNameSpace;
		private $WSUsuario;
		private $WSSenha;
		private $WSConexaoSegura;
		private $WSCodigoTom;
		private $WSArquivoPFX;
		private static $WSCodRetorno = array();
		private $pubKEY;
		private $priKEY;
		
		/* Atributos */	
		public $codEmpresa;
		public $codFilial;
		public $prestadorCnpj;
		public $numeroControle;
		public $codigoIBGE;
		public $numeroNota;
		public $serieNota;
		public $link;
		public $status;
		public $situacao;
		public $criticas;
		public $data;
		public $hora;

		/* Atributos Adicionais (J. Eduardo) */	
		public $nroRps;
		public $protocolo;
		public $codigoVerificacao;
		public $usuarioPrefeitura;
		public $senhaPrefeitura;
		public $codigoTom;
		public $mensagemErro;
		
		//Metodos
		/* Construtor atribuir as mensagens de erros do Web Service */
		public function __construct($pGrupo=""){
			$this->grupo = $pGrupo;

			self::$WSCodRetorno['100'] = "Continue";
			self::$WSCodRetorno['101']="Alteracao de Protocolos!";// Switching Protocols
			//[Successful 2xx]
			self::$WSCodRetorno['200']="Sucesso!";// OK
			self::$WSCodRetorno['201']="Criado!";// Created
			self::$WSCodRetorno['202']="Aceito!";// Accepted
			self::$WSCodRetorno['203']="Informacoes nao autorizadas!";// Non-Authoritative Information
			self::$WSCodRetorno['204']="Nenhum Conteudo!";//No Content
			self::$WSCodRetorno['205']="Redefinir Conteudo";// No Content
			self::$WSCodRetorno['206']="Conteudo Parcial";// Partial Content
			//[Redirection 3xx]
			self::$WSCodRetorno['300']="Multiplas Alternativas";// Multiple Choices
			self::$WSCodRetorno['301']="Movido Permanente";// Moved Permanently
			self::$WSCodRetorno['302']="Encontrado";// Found
			self::$WSCodRetorno['303']="Ver Outro";// See Other
			self::$WSCodRetorno['304']="Nao modificado";// Not Modified
			self::$WSCodRetorno['305']="Utilizar Proxy";// Use Proxy
			self::$WSCodRetorno['306']="(Nao Utilizado)";// unused
			self::$WSCodRetorno['307']="Redirecionamento Temporario";// Temporary Redirect
			//[Client Error 4xx]
			self::$WSCodRetorno['400']="Falha Requisicao";// Bad Request
			self::$WSCodRetorno['401']="Nao Autorizado";// Unauthorized
			self::$WSCodRetorno['402']="Pagamento Requerido";// Payment Required
			self::$WSCodRetorno['403']="Proibido";// Forbidden
			self::$WSCodRetorno['404']="Nao Encontrado";// Not Found
			self::$WSCodRetorno['405']="Metodo nao Permitido";// Method Not Allowed
			self::$WSCodRetorno['406']="Nao Aceito";// Not Acceptable
			self::$WSCodRetorno['407']="Autenticacao de Proxy Requirida";// Proxy Authentication Required
			self::$WSCodRetorno['408']="Excedido Tempo de Requisicao";// Request Timeout
			self::$WSCodRetorno['409']="Conflito";// Conflict
			self::$WSCodRetorno['410']="Enviado";// Gone
			self::$WSCodRetorno['411']="Necessario Tamanho";// Length Required
			self::$WSCodRetorno['412']="Falha na Precondicao";// Precondition Failed
			self::$WSCodRetorno['413']="Entidade Requirida Muito Extensa";// Request Entity Too Large
			self::$WSCodRetorno['414']="URI Requirida Muito Extensa";// Request-URI Too Long
			self::$WSCodRetorno['415']="Tipo de Midia Nao Suportada";// Unsupported Media Type
			self::$WSCodRetorno['416']="Serie Requirida nao Satisfatoria";// Requested Range Not Satisfiable
			self::$WSCodRetorno['417']="Expectativa Falhou";// Expectation Failed
			//[Server Error 5xx]
			self::$WSCodRetorno['500']="Erro de Servidor Interno";//Internal Server Error
			self::$WSCodRetorno['501']="Nao Implementado";// Not Implemented
			self::$WSCodRetorno['502']="Falha no Gateway";// Bad Gateway
			self::$WSCodRetorno['503']="Service Invalido";// Service Unavailable
			self::$WSCodRetorno['504']="Tempo de Conexao com Gateway Excedido";// Gateway Timeout
			self::$WSCodRetorno['505']="Versao HTTP nao Suportada";// HTTP Version Not Supported
		}
		

		//Validar se o XML de entrada esta correto
		public function comunicarWebService($pCancelamento="", $pDiretorio="", $pChamada="", $pDadosTXT="", $ambiente="", $xml=""){

			//file_put_contents("/var/tmp/nfse.log","CComunicadorWebService.php\n  comunicarWebService(".$pCancelamento.") {Cidade envio ".ltrim($this->codigoIBGE,0)." - ", FILE_APPEND);
			
			switch(ltrim($this->codigoIBGE,0)){

				// Ararucária/PR
				case 4101804:
					file_put_contents("/var/tmp/nfse.log","Araucaria} \n\n ", FILE_APPEND);
					if(!$this->wsAraucaria()){ return false; }
				break;

				// Campo Magro/PR
				case 4104253:
					file_put_contents("/var/tmp/nfse.log","Campo Magro} \n\n", FILE_APPEND);
					$CCampoMagro = new CCampoMagro($this->grupo);
					$CCampoMagro->ibge = $this->codigoIBGE;
					$cancelamento = true;
					$envio = true;
					
					if($pCancelamento == "C"){
						$cancelamento = $CCampoMagro->cancelarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente);
					}else{
						$envio = $CCampoMagro->enviarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente, $xml);
					}
					
					$this->codEmpresa = $CCampoMagro->codEmpresa;
					$this->codFilial = $CCampoMagro->codFilial;
					$this->numeroControle = $CCampoMagro->numeroControle;
					$this->numeroNota = $CCampoMagro->numeroNota;
					$this->serieNota = $CCampoMagro->serieNota;
					$this->status = $CCampoMagro->status;
					$this->criticas = $CCampoMagro->criticas;
					$this->protocolo = $CCampoMagro->protocolo;
					$this->codigoVerificacao = $CCampoMagro->codigoVerificacao;
					$this->nroRps = $CCampoMagro->nroRps;
					
					if($pChamada == "COBOL")
					{
						if($pCancelamento == "C")
						{
							$conteudo_arquivo_retorno = "";
							$conteudo_arquivo_retorno .= trim($this->prestadorCnpj)."|";
							$conteudo_arquivo_retorno .= trim($this->codigoIBGE)."|";
							$conteudo_arquivo_retorno .= trim(date("Ym"))."||";
							$conteudo_arquivo_retorno .= trim($this->serieNota)."|";
							$conteudo_arquivo_retorno .= trim($this->numeroNota)."|";
							$conteudo_arquivo_retorno .= trim($this->status)."|";
							$conteudo_arquivo_retorno .= trim($this->criticas)."||||";
							file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
						}
						else
						{
							$saidaArquivo = "";
							$saidaArquivo .= $this->numeroNota."|".$this->numeroControle."|".$this->status."|";
							$saidaArquivo .= $this->nroRps."|".$this->codigoVerificacao."|";
							$saidaArquivo .= $this->criticas."|".$this->protocolo."|";
							file_put_contents($pDiretorio, $saidaArquivo);
						}
	
						return true;
					}
					else
					{
						if($cancelamento == false || $envio == false){
							return false;
						}
					}					

				// Telêmaco Borba/PR
				case 4127106:
				// Pinhais/PR
				case 4119152:				
				// Campo Largo/PR
				case 4104204:
					file_put_contents("/var/tmp/nfse.log","Pinhais} \n\n ", FILE_APPEND);
					$cancelamento = true;
					$envio = true;
					$CPinhias = new CPinhais($this->grupo);

					$CPinhias->usuarioPrefeitura = $this->usuarioPrefeitura;
					$CPinhias->senhaPrefeitura = $this->senhaPrefeitura;
					$CPinhias->codigoTom = ltrim($this->codigoIBGE,0);

					if($pCancelamento == "C")
					{
						$cancelamento = $CPinhias->cancelarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente, $xml);
					}
					else
					{
						$envio = $CPinhias->enviarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente, $xml);
					}

					$this->numeroNota = $CPinhias->numeroNota;
					$this->serieNota = $CPinhias->serieNota;
					$this->status = $CPinhias->status;
					$this->criticas = $CPinhias->mensagemCodigo;

					if($pChamada == "COBOL")
					{
						$mensagem_retorno = explode("-", $CPinhias->mensagemCodigo);
						$status_retorno = "";

						if(trim($mensagem_retorno[0]) == "00001" || trim($mensagem_retorno[0]) == "1")
						{
							$status_retorno = "S";
						}
						else
						{
							$status_retorno = "N";
						}

						if($pCancelamento == "C")
						{
							$conteudo_arquivo_retorno = "";
							$conteudo_arquivo_retorno .= trim($this->prestadorCnpj)."|";
							$conteudo_arquivo_retorno .= trim($this->codigoIBGE)."|";
							$conteudo_arquivo_retorno .= trim(date("Ym"))."||";
							$conteudo_arquivo_retorno .= trim($this->serieNota)."|";
							$conteudo_arquivo_retorno .= trim($this->numeroNota)."|";
							$conteudo_arquivo_retorno .= trim($status_retorno)."|";
							$conteudo_arquivo_retorno .= trim($this->criticas)."||||";

							file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
						}
						else
						{
							$saidaArquivo = "";
							$saidaArquivo .= $CPinhias->numeroNota."|".$this->numeroControle."|".$status_retorno."|".$CPinhias->mensagemCodigo."||";
							$saidaArquivo .= $CPinhias->codigoVerificacao."|";

							file_put_contents($pDiretorio, $saidaArquivo);
						}
						return true;
					}
					else
					{
						if($cancelamento == false || $envio == false){
							return false;
						}
					}

				break;

				// Curitiba/PR
				case 4106902:
					file_put_contents("/var/tmp/nfse.log","Curitiba} \n\n", FILE_APPEND);
					$CCuritiba = new CCuritiba($this->grupo);
					$CCuritiba->ibge = $this->codigoIBGE;
					$cancelamento = true;
					$envio = true;

					if($pCancelamento == "C")
					{
						$cancelamento = $CCuritiba->cancelarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente);
					}
					else
					{
						$envio = $CCuritiba->enviarRPS($this->prestadorCnpj, $this->numeroControle, $pDiretorio, $pChamada, $pDadosTXT, $ambiente, $xml);
					}

					$this->codEmpresa = $CCuritiba->codEmpresa;
					$this->codFilial = $CCuritiba->codFilial;
					$this->numeroControle = $CCuritiba->numeroControle;
					$this->numeroNota = $CCuritiba->numeroNota;
					$this->serieNota = $CCuritiba->serieNota;
					$this->status = $CCuritiba->status;
					$this->criticas = $CCuritiba->criticas;
					$this->protocolo = $CCuritiba->protocolo;
					$this->codigoVerificacao = $CCuritiba->codigoVerificacao;
					$this->nroRps = $CCuritiba->nroRps;

					if($pChamada == "COBOL")
					{
						if($pCancelamento == "C")
						{
							$conteudo_arquivo_retorno = "";
							$conteudo_arquivo_retorno .= trim($this->prestadorCnpj)."|";
							$conteudo_arquivo_retorno .= trim($this->codigoIBGE)."|";
							$conteudo_arquivo_retorno .= trim(date("Ym"))."||";
							$conteudo_arquivo_retorno .= trim($this->serieNota)."|";
							$conteudo_arquivo_retorno .= trim($this->numeroNota)."|";
							$conteudo_arquivo_retorno .= trim($this->status)."|";
							$conteudo_arquivo_retorno .= trim($this->criticas)."||||";
							file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
						}
						else
						{
							$saidaArquivo = "";
							$saidaArquivo .= $CCuritiba->numeroNota."|".$CCuritiba->numeroControle."|".$CCuritiba->status."|";
							$saidaArquivo .= $CCuritiba->criticas."|";
							$saidaArquivo .= $CCuritiba->nroRps."|".$CCuritiba->codigoVerificacao."|".$CCuritiba->protocolo."|";
							file_put_contents($pDiretorio, $saidaArquivo);
						}
	
						return true;
					}
					else
					{
						if($cancelamento == false || $envio == false){
							return false;
						}
					}

				break;

				// Maringá/PR
				case 4115200:
					$CMaringa = new CMaringa($this->grupo);
					$CMaringa->ibge = $this->codigoIBGE;
					$cancelamento = true;
					$envio = true;
					
					if($pCancelamento == "C")
					{
						$cancelamento = $CMaringa->cancelarRPS($this->prestadorCnpj, $this->numeroControle, $pChamada, $pDadosTXT, $xml);
					}
					else
					{
						$envio = $CMaringa->enviarRPS($this->prestadorCnpj, $this->numeroControle, $pChamada, $pDadosTXT, $xml);
					}

					$this->mensagemErro = $CMaringa->mensagemErro;
					$this->codEmpresa = $CMaringa->codEmpresa;
					$this->codFilial = $CMaringa->codFilial;
					$this->numeroControle = $CMaringa->numeroControle;
					$this->numeroNota = $CMaringa->numeroNota;
					$this->serieNota = $CMaringa->serieNota;
					$this->status = $CMaringa->status;
					$this->criticas = $CMaringa->criticas;

					if($pChamada == "COBOL")
					{
						if($pCancelamento == "C")
						{
							$conteudo_arquivo_retorno = "";
							$conteudo_arquivo_retorno .= trim($this->prestadorCnpj)."|";
							$conteudo_arquivo_retorno .= trim($this->codigoIBGE)."|";
							$conteudo_arquivo_retorno .= trim(date("Ym"))."||";
							$conteudo_arquivo_retorno .= trim($this->serieNota)."|";
							$conteudo_arquivo_retorno .= trim($this->numeroNota)."|";
							$conteudo_arquivo_retorno .= trim($this->status)."|";
							$conteudo_arquivo_retorno .= trim($this->mensagemErro)."||||";

							file_put_contents($pDiretorio, $conteudo_arquivo_retorno);
						}
						else
						{
							$saidaArquivo = "";
							$saidaArquivo .= $CMaringa->numeroNota."|".$CMaringa->numeroControle."|".$CMaringa->status."|";
							$saidaArquivo .= $CMaringa->mensagemErro."|";
							$saidaArquivo .= $CMaringa->nroRps."|".$CMaringa->codigoVerificacao."|".$CMaringa->protocolo."|";
							file_put_contents($pDiretorio, $saidaArquivo);
						}
	
						return true;
					}
					else
					{
						if($cancelamento == false || $envio == false){
							return false;
						}
					}
					
				break;
				
				// Telemaco Borba/PR -> Antigo
				case 4127106:
				  file_put_contents("/var/tmp/nfse.log","Telemaco Borba} \n\n", FILE_APPEND);
				  $CTelemacoBorba = new CTelemacoBorba($this->grupo);
				  $CTelemacoBorba->ibge = $this->codigoIBGE;
				  if($pCancelamento == "C"){
					if(!$CTelemacoBorba->cancelarRPS($this->prestadorCnpj, $this->numeroControle)){
					  $this->mensagemErro = $CTelemacoBorba->mensagemErro;
					  $this->codEmpresa = $CTelemacoBorba->codEmpresa;
					  $this->codFilial = $CTelemacoBorba->codFilial;
					  $this->numeroControle = $CTelemacoBorba->numeroControle;
					  $this->numeroNota = $CTelemacoBorba->numeroNota;
					  $this->serieNota = $CTelemacoBorba->serieNota;
					  $this->status = $CTelemacoBorba->status;
					  $this->criticas = $CTelemacoBorba->criticas;
					  return false;
					}
				  }else{
					if(!$CTelemacoBorba->enviarRPS($this->prestadorCnpj, $this->numeroControle)){
					  $this->mensagemErro = $CTelemacoBorba->mensagemErro;
					  $this->codEmpresa = $CTelemacoBorba->codEmpresa;
					  $this->codFilial = $CTelemacoBorba->codFilial;
					  $this->numeroControle = $CTelemacoBorba->numeroControle;
					  $this->numeroNota = $CTelemacoBorba->numeroNota;
					  $this->status = $CTelemacoBorba->status;
					  $this->criticas = $CTelemacoBorba->criticas;
					  return false;
					}
				  }
				  $this->codEmpresa = $CTelemacoBorba->codEmpresa;
				  $this->codFilial = $CTelemacoBorba->codFilial;
				  $this->numeroControle = $CTelemacoBorba->numeroControle;
				  $this->numeroNota = $CTelemacoBorba->numeroNota;
				  $this->serieNota = $CTelemacoBorba->serieNota;
				  $this->status = $CTelemacoBorba->status;
				  $this->criticas = $CTelemacoBorba->criticas;
				break;
				
				// Foz do Iguaçu/PR
				case 4108304:
				  file_put_contents("/var/tmp/nfse.log","Foz do Iguacu} \n\n ", FILE_APPEND);
				  $CFozDoIguacu = new CFozDoIguacu();
				  if($pCancelamento == "C"){
					if(!$CFozDoIguacu->cancelarRPS($this->codEmpresa, $this->codFilial, $this->numeroControle)){
					  $this->mensagemErro = $CFozDoIguacu->mensagemErro;
					  $this->codEmpresa = $CFozDoIguacu->codEmpresa;
					  $this->codFilial = $CFozDoIguacu->codFilial;
					  $this->numeroControle = $CFozDoIguacu->numeroControle;
					  $this->numeroNota = $CFozDoIguacu->numeroNota;
					  $this->serieNota = $CFozDoIguacu->serieNota;
					  $this->status = $CFozDoIguacu->status;
					  $this->criticas = $CFozDoIguacu->criticas;
					  return false;
					}
				  }else{
					if(!$CFozDoIguacu->enviarRPS($this->codEmpresa, $this->codFilial, $this->numeroControle)){
					  $this->mensagemErro = $CFozDoIguacu->mensagemErro;
					  $this->codEmpresa = $CFozDoIguacu->codEmpresa;
					  $this->codFilial = $CFozDoIguacu->codFilial;
					  $this->numeroControle = $CFozDoIguacu->numeroControle;
					  $this->numeroNota = $CFozDoIguacu->numeroNota;
					  $this->status = $CFozDoIguacu->status;
					  $this->criticas = $CFozDoIguacu->criticas;
					  return false;
					}
				  }
				  $this->codEmpresa = $CFozDoIguacu->codEmpresa;
				  $this->codFilial = $CFozDoIguacu->codFilial;
				  $this->numeroControle = $CFozDoIguacu->numeroControle;
				  $this->numeroNota = $CFozDoIguacu->numeroNota;
				  $this->serieNota = $CFozDoIguacu->serieNota;
				  $this->status = $CFozDoIguacu->status;
				  $this->criticas = $CFozDoIguacu->criticas;
				break;

				// Outros municípios não tratados
				default:
				  file_put_contents("/var/tmp/nfse.log"," \n\n ", FILE_APPEND);
				  $this->mensagemErro = "CComunicadorWebService->comunicarWebService {Codigo do IBGE [".$this->codigoIBGE."] nao cadastrado} ";
				  file_put_contents("/var/tmp/nfse.log","CComunicadorWebService.php\n  Codigo do IBGE [".$this->codigoIBGE."] nao cadastrado \n\n", FILE_APPEND);
				  return false;
				break;
			}
			return true;
		}
		
		// Metodos para efetuar a comunicao com os webservices dos municipios
		private function wsPinhais($pCancelamento=""){
			$CXml = new CXml();
			$xmlgen = new xmlgen();
			$CNotaFiscal = new CNotaFiscal();
			$CEmail = new CEmail();
			
			//Retorna codigo XML para comunicar com webservice
			$CXml->xmlPinhais($this->codEmpresa, $this->codFilial, $this->numeroControle, $pCancelamento);
			$arrayXmlEnvio = $CXml->xml;
			if(!file_put_contents("/home/www/html/nf/nfse/enviados/".$this->codEmpresa.$this->codFilial.$this->numeroControle.".xml", $arrayXmlEnvio)){
			  $this->mensagemErro = "CComunicadorWebService\n  wsPinhais {nao eh possivel criar o arquivo [".$this->codEmpresa.$this->codFilial.$this->numeroControle.".xml"."] na pasta enviados }";
			  file_put_contents("/var/tmp/nfse.log","CComunicadorWebService.php\n  wsPinhais {nao eh possivel criar o arquivo [".$this->codEmpresa.$this->codFilial.$this->numeroControle.".xml"."] na pasta enviados } \n\n", FILE_APPEND);
			  return false;
			}
			// Arquivo de Comunicacao
			$arquivoIni = parse_ini_file("/var/www/html/nf/nfse/configuracoes/config.ini");
			
			$ch = curl_init();
			// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
			// curl_setopt($ch, CURLOPT_TIMEOUT, 0); //timeout in seconds The maximum number of seconds to allow cURL functions to execute.
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1"); 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL,$arquivoIni['ws_url']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			$postParams['login'] = $arquivoIni['ws_usuario'];
			$postParams['senha'] = $arquivoIni['ws_senha'];
			$postParams['cidade'] = $arquivoIni['ws_cidadeTom'];
			$postParams['f1'] = "@/home/www/html/nf/nfse/enviados/".$this->codEmpresa.$this->codFilial.$this->numeroControle.".xml";
			
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postParams);
			// $result= curl_exec($ch);
			zulianLog($ch, "3");
			exit();
			
			if (curl_errno($ch)) {
				$this->mensagemErro = "CComunicadorWebService->wsPinhais {erro no funcao cURL [".print curl_error($ch)."]}";
				file_put_contents("/var/tmp/nfse.log","CComunicadorWebService.php\n  wsPinhais {erro no funcao cURL [".print curl_error($ch)."]} \n\n", FILE_APPEND);
				return false;
			}else{
			}
			curl_close ($ch);
			
			$result = simplexml_load_string($result);
			
			/*
				Variaveis de Retorno da NF
				retorno->mensagem->codigo
				retorno->numero_nfse
				retorno->serie_nfse
				retorno->data_nfse
				retorno->hora_nfse
				retorno->arquivo_gerador_nfse
				retorno->link_nfse
				retorno->cod_verificador_atutenticidade
				retorno->codigo_html
			*/

//			echo "critica :".utf8_decode($result->mensagem->codigo);

			if(trim($result->cod_verificador_autenticidade) != "" && substr($result->mensagem->codigo,0,5) == "00001"){
			  $this->status = "S"; // ocorreu correto
			  $arrayAtualizacao['nf']['status'] = "S";
			  $criticas['descricao'] = utf8_decode($result->mensagem->codigo);
			  if($pCancelamento == "C"){
				$arrayAtualizacao['nf']['status'] = "C";
				$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$result->mensagem->codigo);
			  }
			}else{
			  $this->status = "N"; // ocorreu errado
			  $arrayAtualizacao['nf']['status'] = "E";
			  $criticas['descricao'] = utf8_decode($result->mensagem->codigo);
			}

//		GRAVANDO A CRITICA RETORNADA			
			$criticas['codEmpresa'] = $this->codEmpresa;
			$criticas['codFilial'] = $this->codFilial;
			$criticas['numeroControle'] = $this->numeroControle;
			$criticas['data'] = date("d/m/Y");
			$criticas['hora'] = date("H:i:s");
			
			$CCritica = new CCritica();
			$CCritica->inserirCritica($criticas);

			$this->numeroNota = $result->numero_nfse;
			$this->serieNota = $result->serie_nfse;
			// Para as mensagens que retornam do 
			$this->criticas = utf8_decode($result->mensagem->codigo);
			$this->mensagemErro = utf8_decode($result->mensagem->codigo);

			// Atualizar nota fiscal com o retornado
			$arrayAtualizacao['nf']['empresa']['codigo'] = $this->codEmpresa;
			$arrayAtualizacao['nf']['filial']['codigo'] = $this->codFilial;
			$arrayAtualizacao['nf']['controle'] = $this->numeroControle;
			$arrayAtualizacao['nf']['numero'] = $result->numero_nfse;
			$arrayAtualizacao['nf']['serie'] = $result->serie_nfse;			
			/* campos já adicionados direto na model
			$arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
			$arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
			$arrayAtualizacao['nf']['link'] = $result->link_nfse;
			
			$xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
			$xmlAtualizar = simplexml_load_string($xmlAtualizar);
			if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}

			if($pCancelamento != "C" && $this->status == "S"){
			  if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
				$this->mensagemErro = $CNotaFiscal->mensagemErro;
				return false;
			  }
			}
			return true;
		}
		
		private function wsFozDoIguacu($pCancelamento=""){
			/*
			Para abrir o acesso ao site, para verificar notas:
			site: http://homologa.nfse.pmfi.pr.gov.br/NFSe/Login
			login: 03119648000170
			senha: cedros
			*/
			$CXml = new CXml();
			$CNotaFiscal = new CNotaFiscal();
			$CEmail = new CEmail();
			$CAssinaturaDigital = new CAssinaturaDigital();

			/* Obter XML para comunicar com webservice */
			if(!$CXml->xmlFozDoIguacu($this->codEmpresa, $this->codFilial, $this->numeroControle, $pCancelamento)){
				$this->mensagemErro = $CXml->mensagemErro;
				return false;
			}
			
			$xmlFuncao = $CXml->xml;
			
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			$xmlSoap = '';
			$xmlSoap .= '<?xml version="1.0" encoding="utf-8"?>';
			$xmlSoap .= '<soap12:Envelope ';
			$xmlSoap .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
			$xmlSoap .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
			$xmlSoap .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
			$xmlSoap .= '<soap12:Body>';
			$xmlSoap .= '<RecebeLoteRPS xmlns="http://tempuri.org/"><xml xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
			/* Adicionar o Xml obtido para a função */
			$CAssinaturaDigital->xml = $xmlFuncao;
			if(!$CAssinaturaDigital->assinarXml()){
				$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
				return false;
			}
			$xmlSoap .= $CAssinaturaDigital->xml;
			$xmlSoap .= '</xml></RecebeLoteRPS>';
			$xmlSoap .= '</soap12:Body>';
			$xmlSoap .= '</soap12:Envelope>';

			file_put_contents("/user/objetos/temporario.xml", $xmlSoap);
			$tamanho = strlen($xmlSoap);
			$this->WSMetodo = "RecebeLoteRPS";
			
			/* Setar cabecalhos da comunicacao Web Service */
			$parametrosSoap = Array('Host: nfse.pmfi.pr.gov.br','Content-Type: text/xml;charset=utf-8',"Content-length:$tamanho",'SOAPAction: "http://tempuri.org/'.$this->WSMetodo.'"');
			/* Iniciar comunicacao cUrl */
			$_aspa = '"';
			$oCurl = curl_init();
			// curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		  	// curl_setopt($oCurl, CURLOPT_TIMEOUT, 0); //timeout in seconds The maximum number of seconds to allow cURL functions to execute.

			/* Descomentar abaixo para servidores que tem proxy
			::TODO
			if(is_array($this->aProxy)){
				curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
				curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
				curl_setopt($oCurl, CURLOPT_PROXY, $this->aProxy['IP'].':'.$this->aProxy['PORT']);
				if( $this->aProxy['PASS'] != '' ){
					curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->aProxy['USER'].':'.$this->aProxy['PASS']);
					curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
				} //fim if senha proxy
			}//fim if aProxy
			*/
			
			curl_setopt($oCurl, CURLOPT_URL, $this->WSUrl.'');
			/* Verifica se comunicação via protocolo seguro SSL (HTTPS porta 443 default) ou normal HTTP (porta 80 default) */
/*			if($this->WSConexaoSegura == "S"){
			  curl_setopt($oCurl, CURLOPT_PORT , 443); // porta segura HTTPS
			}else{*/
			  curl_setopt($oCurl, CURLOPT_PORT , 80); // porta normal HTTP
//			}

			curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
			curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
//			curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
			/* Para conexoes seguras eh necessario certificado digital*/
			if($this->WSConexaoSegura == "S"){
			  curl_setopt($oCurl, CURLOPT_SSLCERT, $this->pubKEY);
			  curl_setopt($oCurl, CURLOPT_SSLKEY, $this->priKEY);
			}
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
			
			/* Executar chamada o servidor  */
			// $__xml = curl_exec($oCurl);
			zulianLog($oCurl, "1");
			exit();
			
			$info = curl_getinfo($oCurl); //informações da conexão
			$txtInfo ="";
			$txtInfo .= "URL=$info[url]\n";
			$txtInfo .= "Content type=$info[content_type]\n";
			$txtInfo .= "Http Code=$info[http_code]\n";
			$txtInfo .= "Header Size=$info[header_size]\n";
			$txtInfo .= "Request Size=$info[request_size]\n";
			$txtInfo .= "Filetime=$info[filetime]\n";
			$txtInfo .= "SSL Verify Result=$info[ssl_verify_result]\n";
			$txtInfo .= "Redirect Count=$info[redirect_count]\n";
			$txtInfo .= "Total Time=$info[total_time]\n";
			$txtInfo .= "Namelookup=$info[namelookup_time]\n";
			$txtInfo .= "Connect Time=$info[connect_time]\n";
			$txtInfo .= "Pretransfer Time=$info[pretransfer_time]\n";
			$txtInfo .= "Size Upload=$info[size_upload]\n";
			$txtInfo .= "Size Download=$info[size_download]\n";
			$txtInfo .= "Speed Download=$info[speed_download]\n";
			$txtInfo .= "Speed Upload=$info[speed_upload]\n";
			$txtInfo .= "Download Content Length=$info[download_content_length]\n";
			$txtInfo .= "Upload Content Length=$info[upload_content_length]\n";
			$txtInfo .= "Start Transfer Time=$info[starttransfer_time]\n";
			$txtInfo .= "Redirect Time=$info[redirect_time]\n";
			$txtInfo .= "Certinfo=$info[certinfo]\n";
			/* Retirar espacoes no inicio do retorno do servidor*/
			$n = strlen($__xml);
			$x = stripos($__xml, "<");
			$xmlRetorno  = substr($__xml, $x, $n-$x);
			$soapDebug = $xmlSoap."\n\n".$txtInfo."\n".$__xml;
			/* Verificar retorno da conexao com servidor */
			if ($__xml === false){
				//não houve retorno
				$this->mensagemErro = $info['http_code'].$cCode[$info['http_code']].curl_error($oCurl)."\n";
			}
			
			/* Encerrar Conexao cUrl*/
			curl_close($oCurl);

//			echo "XML:".$__xml;
//			echo "\n\n RETORNO ::".$xmlRetorno;
			sleep(10);
			$this->wsRespostaFozDoIguacu();
			exit();
//		GRAVANDO A CRITICA RETORNADA			
			$criticas['codEmpresa'] = $this->codEmpresa;
			$criticas['codFilial'] = $this->codFilial;
			$criticas['numeroControle'] = $this->numeroControle;
			$criticas['data'] = date("d/m/Y");
			$criticas['hora'] = date("H:i:s");
			
			$CCritica = new CCritica();
			$CCritica->inserirCritica($criticas);

			$this->numeroNota = $result->numero_nfse;
			$this->serieNota = $result->serie_nfse;			
			// Para as mensagens que retornam do 
			$this->criticas = utf8_decode($result->mensagem->codigo);
			$this->mensagemErro = utf8_decode($result->mensagem->codigo);

			// Atualizar nota fiscal com o retornado
			$arrayAtualizacao['nf']['empresa']['codigo'] = $this->codEmpresa;
			$arrayAtualizacao['nf']['filial']['codigo'] = $this->codFilial;
			$arrayAtualizacao['nf']['controle'] = $this->numeroControle;
			$arrayAtualizacao['nf']['numero'] = $result->numero_nfse;
			$arrayAtualizacao['nf']['serie'] = $result->serie_nfse;
			/* campos já adicionados direto na model
			$arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
			$arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
			$arrayAtualizacao['nf']['link'] = $result->link_nfse;
			
			$xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
			$xmlAtualizar = simplexml_load_string($xmlAtualizar);
			if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}

			if($pCancelamento != "C" && $this->status == "S"){
			  if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
				$this->mensagemErro = $CNotaFiscal->mensagemErro;
				return false;
			  }
			}
			return true;
		}

		private function wsRespostaFozDoIguacu(){
			/*
			Para abrir o acesso ao site, para verificar notas:
			site: http://homologa.nfse.pmfi.pr.gov.br/NFSe/Login
			login: 03119648000170
			senha: cedros
			*/
			$xmlFuncao = $CXml->xml;
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			$xmlSoap = '';
			$xmlSoap .= '<?xml version="1.0" encoding="utf-8"?>';
			$xmlSoap .= '<soap12:Envelope ';
			$xmlSoap .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
			$xmlSoap .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
			$xmlSoap .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
			$xmlSoap .= '<soap12:Body>';
			$xmlSoap .= '<ConsultarSituacaoLoteRPS xmlns="http://tempuri.org/">';
			$xmlSoap .= '<?xml version="1.0" encoding="utf-8"?>';
			$xmlSoap .= '<ConsultarNfseEnvio xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd">';
			$xmlSoap .= '  <Prestador>';
			$xmlSoap .= '    <Cnpj>03119648000170</Cnpj>';
			$xmlSoap .= '    <InscricaoMunicipal>27438</InscricaoMunicipal>';
			$xmlSoap .= '  </Prestador>';
			$xmlSoap .= '  <PeriodoEmissao>';
			$xmlSoap .= '    <DataInicial>2012-02-22</DataInicial>';
			$xmlSoap .= '    <DataFinal>2012-03-08</DataFinal>';
			$xmlSoap .= '  </PeriodoEmissao>';
			$xmlSoap .= '  <Tomador>';
			$xmlSoap .= '    <CpfCnpj>';
			$xmlSoap .= '      <Cpf>00952651980</Cpf>';
			$xmlSoap .= '    </CpfCnpj>';
			$xmlSoap .= '    <InscricaoMunicipal>15087</InscricaoMunicipal>';
			$xmlSoap .= '  </Tomador>';
			$xmlSoap .= '</ConsultarNfseEnvio>';
			$xmlSoap .= '</xml>';
    		$xmlSoap .= '</ConsultarSituacaoLoteRPS>';
			$xmlSoap .= '</soap12:Body>';
			$xmlSoap .= '</soap12:Envelope>';

			$tamanho = strlen($xmlSoap);
			$this->WSMetodo = "ConsultaNFSE";
			
			/* Setar cabecalhos da comunicacao Web Service */
			$parametrosSoap = Array('Host: nfse.pmfi.pr.gov.br','Content-Type: text/xml;charset=utf-8',"Content-length:$tamanho",'SOAPAction: "http://tempuri.org/'.$this->WSMetodo.'"');
			/* Iniciar comunicacao cUrl */
			$_aspa = '"';
			$oCurl = curl_init();
			// curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 0); // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
		  	// curl_setopt($oCurl, CURLOPT_TIMEOUT, 0); //timeout in seconds The maximum number of seconds to allow cURL functions to execute.

			/* Descomentar abaixo para servidores que tem proxy
			::TODO
			if(is_array($this->aProxy)){
				curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
				curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
				curl_setopt($oCurl, CURLOPT_PROXY, $this->aProxy['IP'].':'.$this->aProxy['PORT']);
				if( $this->aProxy['PASS'] != '' ){
					curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->aProxy['USER'].':'.$this->aProxy['PASS']);
					curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
				} //fim if senha proxy
			}//fim if aProxy
			*/
			
			curl_setopt($oCurl, CURLOPT_URL, $this->WSUrl.'');
			/* Verifica se comunicação via protocolo seguro SSL (HTTPS porta 443 default) ou normal HTTP (porta 80 default) */
/*			if($this->WSConexaoSegura == "S"){
			  curl_setopt($oCurl, CURLOPT_PORT , 443); // porta segura HTTPS
			}else{*/
			  curl_setopt($oCurl, CURLOPT_PORT , 80); // porta normal HTTP
//			}

			curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
			curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
//			curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
			/* Para conexoes seguras eh necessario certificado digital*/
			if($this->WSConexaoSegura == "S"){
			  curl_setopt($oCurl, CURLOPT_SSLCERT, $this->pubKEY);
			  curl_setopt($oCurl, CURLOPT_SSLKEY, $this->priKEY);
			}
			curl_setopt($oCurl, CURLOPT_POST, 1);
			curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
			curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
			
			/* Executar chamada o servidor  */
			// $__xml = curl_exec($oCurl);
			zulianLog($oCurl, "2");
			exit();
			
			$info = curl_getinfo($oCurl); //informações da conexão
			$txtInfo ="";
			$txtInfo .= "URL=$info[url]\n";
			$txtInfo .= "Content type=$info[content_type]\n";
			$txtInfo .= "Http Code=$info[http_code]\n";
			$txtInfo .= "Header Size=$info[header_size]\n";
			$txtInfo .= "Request Size=$info[request_size]\n";
			$txtInfo .= "Filetime=$info[filetime]\n";
			$txtInfo .= "SSL Verify Result=$info[ssl_verify_result]\n";
			$txtInfo .= "Redirect Count=$info[redirect_count]\n";
			$txtInfo .= "Total Time=$info[total_time]\n";
			$txtInfo .= "Namelookup=$info[namelookup_time]\n";
			$txtInfo .= "Connect Time=$info[connect_time]\n";
			$txtInfo .= "Pretransfer Time=$info[pretransfer_time]\n";
			$txtInfo .= "Size Upload=$info[size_upload]\n";
			$txtInfo .= "Size Download=$info[size_download]\n";
			$txtInfo .= "Speed Download=$info[speed_download]\n";
			$txtInfo .= "Speed Upload=$info[speed_upload]\n";
			$txtInfo .= "Download Content Length=$info[download_content_length]\n";
			$txtInfo .= "Upload Content Length=$info[upload_content_length]\n";
			$txtInfo .= "Start Transfer Time=$info[starttransfer_time]\n";
			$txtInfo .= "Redirect Time=$info[redirect_time]\n";
			$txtInfo .= "Certinfo=$info[certinfo]\n";
			/* Retirar espacoes no inicio do retorno do servidor*/
			$n = strlen($__xml);
			$x = stripos($__xml, "<");
			$xmlRetorno  = substr($__xml, $x, $n-$x);
			$soapDebug = $xmlSoap."\n\n".$txtInfo."\n".$__xml;
			/* Verificar retorno da conexao com servidor */
			if ($__xml === false){
				//não houve retorno
				$this->mensagemErro = $info['http_code'].$cCode[$info['http_code']].curl_error($oCurl)."\n";
			}
			
			/* Encerrar Conexao cUrl*/
			curl_close($oCurl);

			exit();
//		GRAVANDO A CRITICA RETORNADA			
			$criticas['codEmpresa'] = $this->codEmpresa;
			$criticas['codFilial'] = $this->codFilial;
			$criticas['numeroControle'] = $this->numeroControle;
			$criticas['data'] = date("d/m/Y");
			$criticas['hora'] = date("H:i:s");
			
			$CCritica = new CCritica();
			$CCritica->inserirCritica($criticas);

			$this->numeroNota = $result->numero_nfse;
			$this->serieNota = $result->serie_nfse;			
			// Para as mensagens que retornam do 
			$this->criticas = utf8_decode($result->mensagem->codigo);
			$this->mensagemErro = utf8_decode($result->mensagem->codigo);

			// Atualizar nota fiscal com o retornado
			$arrayAtualizacao['nf']['empresa']['codigo'] = $this->codEmpresa;
			$arrayAtualizacao['nf']['filial']['codigo'] = $this->codFilial;
			$arrayAtualizacao['nf']['controle'] = $this->numeroControle;
			$arrayAtualizacao['nf']['numero'] = $result->numero_nfse;
			$arrayAtualizacao['nf']['serie'] = $result->serie_nfse;
			/* campos já adicionados direto na model
			$arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
			$arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
			$arrayAtualizacao['nf']['link'] = $result->link_nfse;
			
			$xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
			$xmlAtualizar = simplexml_load_string($xmlAtualizar);
			if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}

			if($pCancelamento != "C" && $this->status == "S"){
			  if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
				$this->mensagemErro = $CNotaFiscal->mensagemErro;
				return false;
			  }
			}
			return true;
		}
		
		private function wsCuritiba(){
		//::TODO
		}
		private function wsFernandoDeNoronha(){
		//::TODO
		}
		private function wsAraucaria(){
		//::TODO
		}
		private function wsRioDeJaneiro(){
			//::TODO
		}
		private function wsSaoPaulo(){
		//::TODO
		}
		
		private function obterConfiguracoesWS(){
		  $CConfig = new CConfig();
		  if(!$CConfig->lerArquivo()){
			$this->mensagemErro = $CConfig->mensagemErro;
			return false;
		  }else{
			$this->ConfigWs['Url'] = $CConfig->ws_url;
			$this->ConfigWs['NameSpace'] = $CConfig->ws_url;
			$this->ConfigWs['Usuario'] = $CConfig->ws_usuario;
			$this->ConfigWs['Senha'] = $CConfig->ws_senha;
			$this->ConfigWs['CodigoTom'] = $CConfig->ws_codigoTom;
			$this->ConfigWs['ArquivoPFX'] = $CConfig->ws_arquivoPFX;
			return true;
		  }
		}
	}
	