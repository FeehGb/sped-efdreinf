<?php
	/*
		Classe:					CFozDoIguacu.php
		Autor:					Guilherme Silva
		Data:					01/03/2012
		Finalidade: 			Classe responsavel pela comunicacao com o WebService de Foz do Iguacu
		Programas chamadores: 	
		Programas chamados: 	
	*/
	require_once(getcwd()."/control/CXml.php");
	require_once(getcwd()."/control/CEmail.php");
	require_once(getcwd()."/control/CConfig.php");
	require_once(getcwd()."/control/CAssinaturaDigital.php");
	require_once(getcwd()."/model/CNotaFiscal.php");
	require_once(getcwd()."/model/CCritica.php");

	class CFozDoIguacu{
		/* Atruibutos publicos utilizada por todos */
		public $mensagemErro;		
		public $prestadorCNPJ;
		public $prestadorInscricaoMunicipal;

		public $codEmpresa;
		public $codFilial;
		public $numeroControle;
		public $criticas;
		public $numeroNota;
		public $serieNota;
		public $status;
		
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
		
		/* Metodos publicos chamados por programas externos*/
		public function enviarRPS($pEmpesa, $pFilial, $pNumeroControle){
			/*
			Para abrir o acesso ao site, para verificar notas:
			site: http://homologa.nfse.pmfi.pr.gov.br/NFSe/Login
			login: 03119648000170
			senha: cedros
			*/
			$this->codEmpresa = $pEmpesa;
			$this->codFilial = $pFilial;
			$this->numeroControle = $pNumeroControle;
			
			$CXml = new CXml();
			$CNotaFiscal = new CNotaFiscal();
			$CEmail = new CEmail();
			$CAssinaturaDigital = new CAssinaturaDigital();
			$DDoc = new DOMDocument();

			$this->obterConfiguracoesWS();

			/* Obter XML para comunicar com webservice */
			if(!$CXml->xmlFozDoIguacu($this->codEmpresa, $this->codFilial, $this->numeroControle, $pCancelamento)){
				$this->mensagemErro = $CXml->mensagemErro;
				return false;
			}

			if(!$this->executarWS('RecebeLoteRPS', $CXml->xml, "S", 'InfRps')){
				return false;
			}

			$DDoc->loadXML($this->xmlRetornoWS);
			$numeroLote = $DDoc->getElementsByTagName("NumeroLote");
			$dataRecebimento = $DDoc->getElementsByTagName("DataRecebimento");
			$protocolo = $DDoc->getElementsByTagName("Protocolo");

			$this->retornoWS['numeroLote'] = $numeroLote->item(0)->nodeValue;
			$this->retornoWS['dataRecebimento'] = $dataRecebimento->item(0)->nodeValue;
			$this->retornoWS['protocolo'] = $protocolo->item(0)->nodeValue;
	
			echo "\n\n numero Lote:".$this->retornoWS['numeroLote'];
			echo "\n\n numero data Recebimento:".$this->retornoWS['dataRecebimento'];
			echo "\n\n numero Protocolo:".$this->retornoWS['protocolo'];
			sleep(5);
			if(!$this->wsRespostaFozDoIguacu()){
				return false;
			}
		}
		
		public function cancelarRPS($pEmpesa, $pFilial, $pNumeroControle){
			/*
			Para abrir o acesso ao site, para verificar notas:
			site: http://homologa.nfse.pmfi.pr.gov.br/NFSe/Login
			login: 03119648000170
			senha: cedros
			*/
			$this->codEmpresa = $pEmpesa;
			$this->codFilial = $pFilial;
			$this->numeroControle = $pNumeroControle;
			
			$CXml = new CXml();
			$CNotaFiscal = new CNotaFiscal();
			$CEmail = new CEmail();
			$CAssinaturaDigital = new CAssinaturaDigital();
			$DDoc = new DOMDocument();

			$this->obterConfiguracoesWS();

			/* Obter XML para comunicar com webservice */
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->codEmpresa, $this->codFilial, $this->numeroControle);
			if(!$tabelaNf){
				$this->mensagemErro = $CNotaFiscal->mensagemErro;				
				return false;
			}
			
			$xmlCancelamento = '';
			$xmlCancelamento .= '<CancelarNfseEnvio xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd">';
			$xmlCancelamento .= '<Pedido>';
			$xmlCancelamento .= '<InfPedidoCancelamento id="1">';
			$xmlCancelamento .= '<IdentificacaoNfse>';
			$xmlCancelamento .= '<Numero>'.$tabelaNf->fields['nf_numero'].'</Numero>';
			$xmlCancelamento .= '<Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
			$xmlCancelamento .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
			$xmlCancelamento .= '<CodigoMunicipio>'.$tabelaNf->fields['prestador_cidade'].'</CodigoMunicipio>';
			$xmlCancelamento .= '</IdentificacaoNfse>';
			$xmlCancelamento .= '<CodigoCancelamento>1</CodigoCancelamento>';
			$xmlCancelamento .= '</InfPedidoCancelamento>';
			$xmlCancelamento .= '</Pedido>';
			$xmlCancelamento .= '</CancelarNfseEnvio>';
			
			$this->numeroNota = $tabelaNf->fields['nf_numero'];
			$this->serieNota = $tabelaNf->fields['nf_serie'];
			
			if(!$this->executarWS('CancelamentoNFSE', $xmlCancelamento, "S", "InfPedidoCancelamento", "Pedido")){
			  return false;
			}
			
			if(!$this->gravarRetorno("NF Cancelada com Sucesso!", "N", "S")){
			  return false;
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
			$DDoc = new DOMDocument();
			$CNotaFiscal = new CNotaFiscal();
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->codEmpresa, $this->codFilial, $this->numeroControle);
			
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			$pXmlConsulta = '';
			$pXmlConsulta .= '<ConsultarLoteRpsEnvio xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd">';
			$pXmlConsulta .= '  <Prestador>';
			$pXmlConsulta .= '    <Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
			$pXmlConsulta .= '    <InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
			$pXmlConsulta .= '  </Prestador>';
			$pXmlConsulta .= '  <Protocolo>'.$this->retornoWS['protocolo'].'</Protocolo>';
			$pXmlConsulta .= '</ConsultarLoteRpsEnvio>';
			
			if(!$this->executarWS('ConsultarLoteRPS', $pXmlConsulta, "S", 'Prestador', 'ConsultarLoteRpsEnvio')){
				return false;
			}

			file_put_contents("/user/objetos/outro.xml", $this->xmlRetornoWS);
			$DDoc->loadXML($this->xmlRetornoWS);
			$this->retornoWS['codVerificacao'] = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
			$this->retornoWS['numeroNF'] = $DDoc->getElementsByTagName("Numero")->item(0)->nodeValue;
			$this->retornoWS['serieNF'] = $DDoc->getElementsByTagName("Serie")->item(0)->nodeValue;			
			
			if($this->retornoWS['codVerificacao'] != "" && $this->retornoWS['numeroNF'] != ""){
				if(!$this->gravarRetorno("Sucesso","N")){
					return false;
				}
			}else{
				if(!$this->gravarRetorno("Sucesso","N")){
				  return false;
				}
			}
			return true;
		}
		
		public function consultarNotaFiscal(){
		}
		
		/* Metodos privados chamado internamente pela classe */ 
		private function obterConfiguracoesWS(){
		  $CConfig = new CConfig();
		  if(!$CConfig->lerArquivo()){
			$this->mensagemErro = $CConfig->mensagemErro;
			return false;
		  }else{
			$this->ConfigWs = $CConfig->configWs;
			return true;
		  }
		}

		private function executarWS($pMetodo, $pDados, $assinatura="N", $tagAssinada="", $noAssinar=""){
		  /* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
		  $CAssinaturaDigital = new CAssinaturaDigital();
		  $xmlSoap = '';
		  $xmlSoap .= '<?xml version="1.0" encoding="utf-8"?>';
		  $xmlSoap .= '<soap12:Envelope ';
		  $xmlSoap .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
		  $xmlSoap .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
		  $xmlSoap .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
		  $xmlSoap .= '<soap12:Body>';
		  $xmlSoap .= '<'.$pMetodo.' xmlns="http://tempuri.org/"><xml xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		  /* Adicionar o Xml obtido para a funcao */
		  if($assinatura == "S"){
			$CAssinaturaDigital->xml = $pDados;
			if(!$CAssinaturaDigital->assinarXml($tagAssinada, $noAssinar)){
				$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
				return false;
			}
			$xmlSoap .= substr($CAssinaturaDigital->xml, 1,-1);
			file_put_contents("/user/objetos/xmlTemp.xml",substr($CAssinaturaDigital->xml, 1,-1));
		  }else{
			$xmlSoap .= $pDados;
			file_put_contents("/user/objetos/xmlTemp.xml",$pDados);
		  }

		  $xmlSoap .= '</xml></'.$pMetodo.'>';
		  $xmlSoap .= '</soap12:Body>';
		  $xmlSoap .= '</soap12:Envelope>';

		  file_put_contents("/user/objetos/temporario.xml", $xmlSoap);
		  $tamanho = strlen($xmlSoap);
		  
		  /* Setar cabecalhos da comunicacao Web Service */
		  $parametrosSoap = Array('Host: homologa.nfse.pmfi.pr.gov.br','Content-Type: text/xml;charset=utf-8',"Content-length:$tamanho",'SOAPAction: "http://tempuri.org/'.$pMetodo.'"');
		  /* Iniciar comunicacao cUrl */
		  $_aspa = '"';
		  $oCurl = curl_init();

		  /* Descomentar abaixo para servidores que tem proxy */
		  if($this->ConfigWs['WSProxy'] == "S"){
			  curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
			  curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
			  curl_setopt($oCurl, CURLOPT_PROXY, $this->ConfigWs['WSIPProxy'].':'.$this->ConfigWs['WSPortaProxy']);
			  if( $this->ConfigWs['WSSenhaProxy'] != '' ){
				  curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->ConfigWs['WSUsuarioProxy'].':'.$this->ConfigWs['WSSenhaProxy']);
				  curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
			  } //fim if senha proxy
		  }//fim if aProxy


		  curl_setopt($oCurl, CURLOPT_URL, $this->ConfigWs['WSUrl'].'');
		  /* Verifica se comunicação via protocolo seguro SSL (HTTPS porta 443 default) ou normal HTTP (porta 80 default) */
/*			  if($this->WSConexaoSegura == "S"){
				curl_setopt($oCurl, CURLOPT_PORT , 443); // porta segura HTTPS
			  }else{*/
				curl_setopt($oCurl, CURLOPT_PORT , $this->ConfigWs['WSPorta']); // porta normal HTTP
//			  }
//		  }

		  curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
		  curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
//			curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
		  /* Para conexoes seguras eh necessario certificado digital*/
		  if($this->ConfigWs['WSConexaoSegura'] == "S"){
			curl_setopt($oCurl, CURLOPT_SSLCERT, $CAssinaturaDigital->getPubKey());
			curl_setopt($oCurl, CURLOPT_SSLKEY, $CAssinaturaDigital->getPriKey());
		  }
		  curl_setopt($oCurl, CURLOPT_POST, 1);
		  curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
		  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
		  
		  /* Executar chamada o servidor  */
		  $__xml = curl_exec($oCurl);

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
//			$soapDebug = $xmlSoap."\n\n".$txtInfo."\n".$__xml;
		  /* Verificar retorno da conexao com servidor */
		  if ($__xml === false){
			//não houve retorno
			$this->mensagemErro = $info['http_code'].$cCode[$info['http_code']].curl_error($oCurl)."\n";
			return false;
		  }
		  
		  /* Encerrar Conexao cUrl*/
		  curl_close($oCurl);

		  file_put_contents("/user/objetos/temp.txt", $xmlRetorno);
		  $xmlResposta = simplexml_load_file("/user/objetos/temp.txt");

		  $this->xmlRetornoWS = $xmlRetorno;
		  file_put_contents("/user/objetos/retorno.xml", $this->xmlRetornoWS);
		  
		  $DDoc = new DOMDocument();	  
		  $DDoc->loadXML($this->xmlRetornoWS);
		  $mensagem = $DDoc->getElementsByTagName("Mensagem");
		  $this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;

		  if($this->retornoWS['mensagem'] != ""){
			  $this->gravarRetorno($this->retornoWS['mensagem'], "S");
			  $this->mensagemErro = $this->retornoWS['mensagem'];
			  return false;
		  }else{
			  return true;
		  }
		}
		
		private function gravarRetorno($mensagem="", $erro="N", $pCancelamento="N"){
		  $CCritica = new CCritica();
		  $CNotaFiscal = new CNotaFiscal();
		  $CEmail = new CEmail();
		  $xmlgen = new xmlgen();

		  if($erro == "S"){
			$this->status = "N"; // ocorreu errado
			$arrayAtualizacao['nf']['status'] = "E";
		  }else{
			$this->status = "S"; // ocorreu certo
			$arrayAtualizacao['nf']['status'] = "S";
		  }
		  
		  if($pCancelamento == "S"){
			$arrayAtualizacao['nf']['status'] = "C";
			$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$mensagem);
		  }else{
			$this->numeroNota = $this->retornoWS['numeroNF'];
			$this->serieNota = $this->retornoWS['serieNF'];
		  }
		  
		  $criticas['codEmpresa'] = $this->codEmpresa;
		  $criticas['codFilial'] = $this->codFilial;
		  $criticas['numeroControle'] = $this->numeroControle;
		  $criticas['data'] = date("d/m/Y");
		  $criticas['hora'] = date("H:i:s");
		  $criticas['descricao'] = utf8_encode($mensagem);

		  if(!$CCritica->inserirCritica($criticas)){
			  $this->mensagemErro = $CCritica->mensagemErro;
			  return false;
		  }

		  // Para as mensagens que retornam erradas
		  $this->criticas = utf8_encode($mensagem);
		  $this->mensagemErro = utf8_encode($mensagem);

		  // Atualizar nota fiscal com o retornado
		  $arrayAtualizacao['nf']['empresa']['codigo'] = $this->codEmpresa;
		  $arrayAtualizacao['nf']['filial']['codigo'] = $this->codFilial;
		  $arrayAtualizacao['nf']['controle'] = $this->numeroControle;
		  $arrayAtualizacao['nf']['numero'] = $this->retornoWS['numeroNF'];
		  $arrayAtualizacao['nf']['serie'] = $this->retornoWS['serieNF'];
		  /* campos já adicionados direto na model
		  $arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
		  $arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
		  $arrayAtualizacao['nf']['link'] = "https://homologa.nfse.pmfi.pr.gov.br/nfseplacar/placar/VerificarAutenticidade";
		  $arrayAtualizacao['nf']['autenticacao'] = $this->retornoWS['codVerificacao'];
		  
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