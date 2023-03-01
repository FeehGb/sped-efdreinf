<?php
	/*
		Classe:					CTelemacoBorba.php
		Autor:					Guilherme Silva
		Data:					19/03/2014
		Finalidade: 			Classe responsavel pela comunicacao com o WebService de Telemaco Borba/PR
		Programas chamadores:
		Programas chamados:
	*/
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");

	class CTelemacoBorba{
		/* Atruibutos publicos utilizada por todos */
		public $mensagemErro;
		public $prestadorInscricaoMunicipal;

		public $codEmpresa;
		public $codFilial;
		public $prestadorCnpj;
		public $numeroControle;
		public $criticas;
		public $numeroNota;
		public $serieNota;
		public $status;
		public $link;
		public $codigoVerificacao;
		public $mensagemCodigo;
		public $protocolo;
		
		/* Atributos privados utilizados apenas pela classe internamente */
		
		private $xmlRetornoWS;
		private $retornoWS;

		private $ProxyIP;
		private $ProxyPorta;
		private $ProxyUsuario;
		private $ProxySenha;
		private $ProxyValida;
		
		private $ConfigWs;
		
		private $chavePublica;
		private $chavePrivada;
		private $grupo;
		private $cancelamento=false;
		
		/* Metodos publicos chamados por programas externos*/
		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}
		
		public function enviarRPS($pCnpj, $pNumeroControle){
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;
			
			// Obter configuracoes do arquivo config.ini
			$this->obterConfiguracoesWS($pCnpj);
			
			$CXml = new CXml($this->grupo);
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument();

			
			/* Obter XML para comunicar com webservice */
			if(!$CXml->xmlTelemacoBorba($this->prestadorCnpj, $this->numeroControle)){
			  $this->mensagemErro = $CXml->mensagemErro;
			  return false;
			}

			if(!$this->executarWS('recepcionarLoteRps', $CXml->xml, "S", 'LoteRps', '', 'InfRps')){
				return false;

			}

			$DDoc->loadXML($this->xmlRetornoWS);
			$numeroLote = $DDoc->getElementsByTagName("NumeroLote");
			$dataRecebimento = $DDoc->getElementsByTagName("DataRecebimento");
			$protocolo = $DDoc->getElementsByTagName("Protocolo");
			
			$this->retornoWS['numeroLote'] = $numeroLote->item(0)->nodeValue;
			$this->retornoWS['dataRecebimento'] = $dataRecebimento->item(0)->nodeValue;
			$this->protocolo = $this->retornoWS['protocolo'] = $protocolo->item(0)->nodeValue;

			sleep(10);
			if(!$this->wsRespostaTelemacoBorba()){
				return false;
			}
		}
		
		public function cancelarRPS($pCnpj, $pNumeroControle){
			$this->codEmpresa = $pEmpesa;
			$this->codFilial = $pFilial;
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;

			// Obter configuracoes do arquivo config.ini
			$this->obterConfiguracoesWS($pCnpj);
			
			$CXml = new CXml($this->grupo);
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument();

			/* Obter XML para comunicar com webservice */
			if(!($tabelaNf = $CNotaFiscal->obterNotaFiscal($this->prestadorCnpj, $this->numeroControle))){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}

			$this->cancelamento=true;
			$xmlCancelamento .= '<Pedido>';
            $xmlCancelamento .= '<InfPedidoCancelamento Id="L1">';
            $xmlCancelamento .= '   <IdentificacaoNfse>';
            $xmlCancelamento .= '      <Numero>'.$tabelaNf->fields['nf_numero'].'</Numero>';
            $xmlCancelamento .= '      <Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
            $xmlCancelamento .= '      <InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
            $xmlCancelamento .= '      <CodigoMunicipio>'.$tabelaNf->fields['prestador_cidade'].'</CodigoMunicipio>';
            $xmlCancelamento .= '   </IdentificacaoNfse>';
            $xmlCancelamento .= '   <CodigoCancelamento>1</CodigoCancelamento>';
            $xmlCancelamento .= '</InfPedidoCancelamento>';
            $xmlCancelamento .= '</Pedido>';

			$this->numeroNota = $tabelaNf->fields['nf_numero'];
			$this->serieNota = $tabelaNf->fields['nf_serie'];

			if(!$this->executarWS('cancelarNfse', $xmlCancelamento, "S", "InfPedidoCancelamento")){
			  return false;
			}
			
			$DDoc->loadXML($this->xmlRetornoWS);
			$mensagem = $DDoc->getElementsByTagName("Mensagem");
			$this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;
			
			if($this->retornoWS['mensagem'] != ""){
				$this->mensagemErro = $this->retornoWS['mensagem'];
				return false;
			}
			
			if(!$this->gravarRetorno("NF Cancelada com Sucesso!", "N", "S")){
			  return false;
			}
			return true;
		}
		

		private function wsRespostaTelemacoBorba(){
			$DDoc = new DOMDocument();
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->prestadorCnpj, $this->numeroControle);
			
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			//$this->prestadorCnpj = $tabelaNf->fields['prestador_cpf_cnpj'];
			$pXmlConsulta = '';
			$pXmlConsulta .= '<e:ConsultarLoteRpsEnvio>';
			/*$pXmlConsulta .= '<IdentificacaoRps>';
				$pXmlConsulta .= '<Numero>'.$tabelaNf->fields['nf_lote'].'</Numero>';
				$pXmlConsulta .= '<Serie>1</Serie>';
				$pXmlConsulta .= '<Tipo>1</Tipo>';
			$pXmlConsulta .= '</IdentificacaoRps>';*/
			$pXmlConsulta .= '<Prestador>';
			$pXmlConsulta .= '<Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
			$pXmlConsulta .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
			$pXmlConsulta .= '</Prestador>';
			$pXmlConsulta .= '<Protocolo>'.$this->retornoWS['protocolo'].'</Protocolo>';
			$pXmlConsulta .= '</e:ConsultarLoteRpsEnvio>';
			
			$qtdeConsultas = 0;
			do{
				sleep(5);
				$qtdeConsultas++;
				if(!$this->executarWS('consultarLoteRps', $pXmlConsulta, "N", "")){
					return false;
				}
				
				  $DDoc->loadXML($this->xmlRetornoWS);
				  $mensagem = $DDoc->getElementsByTagName("Mensagem");
				  $this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;
				  
				  // Mensagem
				  $mensagem = $DDoc->getElementsByTagName("Mensagem");
				  $this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;

				  if($this->retornoWS['mensagem'] ==""){
					  // Numero Nota
					  $numeroNf = $DDoc->getElementsByTagName("Numero");
					  $this->retornoWS['numeroNF'] = $this->numeroNota = $numeroNf->item(0)->nodeValue;
					  // Serie
					  $serieNf = $DDoc->getElementsByTagName("Serie");
					  $this->retornoWS['serieNF'] = $this->serieNota = $serieNf->item(0)->nodeValue;
					  // Link
					  $link = $DDoc->getElementsByTagName("OutrasInformacoes");
					  $this->link = $link->item(0)->nodeValue;
					  // Codigo Verificacao
					  $codigoVerificacao = $DDoc->getElementsByTagName("CodigoVerificacao");
					  $this->retornoWS['codVerificacao'] = $this->codigoVerificacao = $codigoVerificacao->item(0)->nodeValue;
				  }

			}while((trim($this->retornoWS['codVerificacao']) == "" || trim($this->retornoWS['numeroNF']) == "")
						&& ($qtdeConsultas <= 60));
				
			if($this->retornoWS['codVerificacao'] != "" && $this->retornoWS['numeroNF'] != ""){
				if(!$this->gravarRetorno("Sucesso","N")){
					return false;
				}
			}else{
				if(!$this->gravarRetorno("Nao houve retorno da prefeitura!","S")){
				  return false;
				}
			}
			return true;
		}
		
		public function consultarNotaFiscal(){
		}
		
		/* Metodos privados chamado internamente pela classe */ 
		/*private function obterConfiguracoesWS($cnpj){
		  $CConfig = new CConfig();
		  if(!$CConfig->lerArquivo($cnpj)){
			$this->mensagemErro = $CConfig->mensagemErro;
			return false;
		  }else{
			$this->ConfigWs = $CConfig->configWs;
			return true;
		  }
		}*/
		
		private function obterConfiguracoesWS($pCnpj){
		  $CEmpresa = new CEmpresa($this->grupo);
		  $CEmpresa->cnpj = $pCnpj;
		  $retorno = $CEmpresa->obterEmpresa();

		  if(!$retorno || $retorno == null){
            $this->mensagemErro = $CEmpresa->mensagemErro;
            return false;
		  }else{
            $this->ConfigWs = $retorno;
			return true;
		  }
		}

		private function executarWS($pMetodo, $pDados, $assinatura="N", $tagAssinada="", $noAssinar="", $tagAssinada2=""){
		  // Obeter informacoes da tabela empresa
		  //$this->obterConfiguracoesWS($this->prestadorCnpj);		  

		  /* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
		  $CAssinaturaDigital = new CAssinaturaDigital();
		  $xmlSoap = '';
		  $xmlSoap .= $teste =  '<?xml version="1.0" encoding="utf-8"?>';
		  $xmlSoap .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:e="http://www.betha.com.br/e-nota-contribuinte-ws">
						<soapenv:Header/>
						<soapenv:Body>';
		  if($this->cancelamento == true){
			$xmlSoap .= '<e:CancelarNfseEnvio>';
		  }

		  $CAssinaturaDigital->arquivoPFX = "/var/www/html/nf/nfse/certificados/".$this->prestadorCnpj.".pfx";
		  $CAssinaturaDigital->senhaPFX = $this->ConfigWs[0]['senha_pfx'];
		  $CAssinaturaDigital->transform = true;
		  if($assinatura == "S"){
			$CAssinaturaDigital->xml = $pDados; //adicionado 25/07
			
			if($tagAssinada2 != ""){
				if(!$CAssinaturaDigital->assinarXml($tagAssinada2, 'Rps', $this->prestadorCnpj)){
					$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
					return false;
				}
			}
			
			if(!$CAssinaturaDigital->assinarXml($tagAssinada, $noAssinar, $this->prestadorCnpj)){
				$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
				return false;
			}
			
			$xmlSoap .= $CAssinaturaDigital->xml;
		  }else{
			$CAssinaturaDigital->cnpj = $this->prestadorCnpj;
			if(!$CAssinaturaDigital->loadCerts()){
				$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
				return false;
			}
			$xmlSoap .= $pDados;
		  }

		  //$xmlSoap .= '</'.$pMetodo.'>';
		  /*$xmlSoap .= '</soap12:Body>';
		  $xmlSoap .= '</soap12:Envelope>';*/
		  if($this->cancelamento == true){
			$xmlSoap .= '</e:CancelarNfseEnvio>';
		  }
		  $xmlSoap .= '</soapenv:Body></soapenv:Envelope>';
		  
		  file_put_contents("/home/guilherme/nfse.xml", $xmlSoap);
		  
		  file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") {XML Enviado a enviar para prefeitura: \n ".$xmlSoap." } \n\n", FILE_APPEND);

		  $tamanho = strlen($xmlSoap);
		  
		  /* Setar cabecalhos da comunicacao Web Service */
		  $parametrosSoap = Array('Host: 177.43.56.220', 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
		  /* Iniciar comunicacao cUrl */
		  $oCurl = curl_init();
		  /* Descomentar abaixo para servidores que tem proxy */
		  if($this->ConfigWs[0]['proxy'] == "S"){
			  curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
			  curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
			  curl_setopt($oCurl, CURLOPT_PROXY, $this->ConfigWs[0]['proxy_servidor'].':'.$this->ConfigWs[0]['proxy_porta']);
			  if( $this->ConfigWs[0]['proxy_senha'] != '' ){
				  curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->ConfigWs[0]['proxy_usuario'].':'.$this->ConfigWs[0]['proxy_senha']);
				  curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
			  } //fim if senha proxy
		  }//fim if aProxy

		// VERIFICAR POIS EH PARA OBTER FIXO CONFORME IBGE, URL E PORTA PARA EMISSAO

		  /*curl_setopt($oCurl, CURLOPT_CERTINFO, 0);
		  curl_setopt($oCurl, CURLOPT_CAINFO, "/var/www/html/nf/nfse/certificados/SERASA_AC_v2.crt");
		  curl_setopt($oCurl, CURLOPT_CAPATH, "/var/www/html/nf/nfse/certificados/AC_SERASA_ACP_v2.cer");*/

		  if($this->ConfigWs[0]['flag_producao'] == "P"){
			curl_setopt($oCurl, CURLOPT_URL, "https://e-gov.betha.com.br/e-nota-contribuinte-ws/$pMetodo?wsdl");
		  }else{
		    curl_setopt($oCurl, CURLOPT_URL, "https://e-gov.betha.com.br/e-nota-contribuinte-test-ws/$pMetodo?wsdl");
		  }
		  curl_setopt($oCurl, CURLOPT_PORT , 443); // porta HTTPS
		  curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
		  curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
		  curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
		  //curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 120);
		  //curl_setopt($oCurl, CURLOPT_TIMEOUT, 120);
		  //curl_setopt($oCurl, CURLOPT_MAXREDIRS, 10);
		  curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, TRUE);
		  //curl_setopt($oCurl, CURLOPT_FAILONERROR, TRUE);
		  
		  /* Para conexoes seguras eh necessario certificado digital*/
		  curl_setopt($oCurl, CURLOPT_SSLCERT, $CAssinaturaDigital->getPubKey());
		  curl_setopt($oCurl, CURLOPT_SSLKEY, $CAssinaturaDigital->getPriKey());

		  curl_setopt($oCurl, CURLOPT_POST, 1);
		  curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
		  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
		  
		  file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") { Parametros de Comunicacao:\n".
		  					"    URL: https://isseteste.maringa.pr.gov.br/ws/:443\n".
		  					"    Header: ".$parametrosSoap."\n".
							"}\n\n", FILE_APPEND);
		  /* Executar chamada o servidor */

		  $__xml = curl_exec($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") { Retorno servidor:\n".
		  					$__xml." }\n\n", FILE_APPEND);

		  $info = curl_getinfo($oCurl); //informações da conexão
		  
		  $txtInfo  = "";
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

		  /* Encerrar Conexao cUrl*/
		  curl_close($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") { XML retorno:\n".
							$xmlRetorno." }\n\n", FILE_APPEND);
		  $this->xmlRetornoWS = $xmlRetorno;
		  
		  /* Verificar retorno da conexao com servidor */
		  if($info['http_code'] != "200" || $__xml === false){
			$mensagemErro = $DDoc->getElementsByTagName("Text");
			file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") { Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue." }\n\n", FILE_APPEND);
			$this->mensagemErro = "Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue."\n";
			return false;
		  }

		  if($this->retornoWS['mensagem'] != ""){
			file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  executarWS(".$pMetodo.") { Mensagem: ".$this->retornoWS['mensagem']." }\n\n", FILE_APPEND);
			$this->gravarRetorno($this->retornoWS['mensagem'], "S");
			$this->mensagemErro = utf8_decode($this->retornoWS['mensagem']);
			return false;
		  }else{
			return true;
		  }
		}

		private function gravarRetorno($mensagem="", $erro="N", $pCancelamento="N"){
		  $CCritica = new CCritica($this->grupo);
		  $CNotaFiscal = new CNotaFiscal($this->grupo);
		  $CEmail = new CEmail($this->grupo);
		  $xmlgen = new xmlgen();

		  if($erro == "S"){
			$this->status = "N"; // ocorreu errado
			$arrayAtualizacao['nf']['status'] = "E";
			file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  gravarRetorno() { Falha }\n\n", FILE_APPEND);
		  }else{
			$this->status = "S"; // ocorreu certo
			$arrayAtualizacao['nf']['status'] = "S";
			file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  gravarRetorno() { Sucesso }\n\n", FILE_APPEND);
		  }
		  
		  if($pCancelamento == "S"){
			$arrayAtualizacao['nf']['status'] = "C";
			$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$mensagem);
			file_put_contents("/var/tmp/nfse.log","CTelemacoBorba.php\n  gravarRetorno() { CANCELADO }\n\n", FILE_APPEND);
		  }else{
			$this->numeroNota = $this->retornoWS['numeroNF'];
			$this->serieNota = $this->retornoWS['serieNF'];
		  }
		  
		  $criticas['codEmpresa'] = $this->codEmpresa;
		  $criticas['codFilial'] = $this->codFilial;
		  $criticas['cnpj'] = $this->prestadorCnpj;
		  $criticas['numeroControle'] = $this->numeroControle;
		  $criticas['data'] = date("d/m/Y");
		  $criticas['hora'] = date("H:i:s");
		  $criticas['descricao'] = utf8_decode($mensagem);

		  if(!$CCritica->inserirCritica($criticas)){
			  $this->mensagemErro = $CCritica->mensagemErro;
			  return false;
		  }

		  // Para as mensagens que retornam erradas
		  $this->criticas = utf8_decode($mensagem);
		  $this->mensagemErro = utf8_decode($mensagem);

		  // Atualizar nota fiscal com o retornado
		  $arrayAtualizacao['prestador']['cpfcnpj'] = $this->prestadorCnpj;
		  $arrayAtualizacao['nf']['controle'] = $this->numeroControle;
		  $arrayAtualizacao['nf']['numero'] = $this->numeroNota;
		  $arrayAtualizacao['nf']['serie'] = $this->serieNota;
		  /* campos já adicionados direto na model
		  $arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
		  $arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
		  $arrayAtualizacao['nf']['link'] = $this->link;
		  $arrayAtualizacao['nf']['autenticacao'] = $this->codigoVerificacao;
		  $arrayAtualizacao['nf']['protocolo'] = $this->protocolo;
		  
		  $xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
		  $xmlAtualizar = simplexml_load_string($xmlAtualizar);
		  if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		  }

		  if($pCancelamento != "S" && $this->status == "S"){
			if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}
		  }
		  return true;
		}
	}
?>
