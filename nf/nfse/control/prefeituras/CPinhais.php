<?php
	/*
		Classe:					Pinhais.php
		Autor:					Guilherme Silva
		Data:					15/03/2012
		Finalidade: 			Classe responsavel pela comunicacao com o WebService de Pinhais
		Programas chamadores:
		Programas chamados:
		
				
		script usado por 
		Telêmaco Borba/PR
		Pinhais/PR
		Campo Largo/PR
		
	*/
	require_once("/var/www/html/nf/nfse/control/xmlgen.php"); 
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	require_once("/var/www/html/nf/nfse/model/CEmpresa.php");

	class CPinhais{
		/* Atruibutos publicos utilizada por todos */
		private $grupo;

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


		public $usuarioPrefeitura;
		public $senhaPrefeitura;
		public $codigoTom;
		
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
		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}

		public function enviarRPS($pCnpj, $pNumeroControle, $pDiretorio="", $pChamada="", $pDadosTXT="", $ambiente="", $xml_tve_cobol=""){
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;

			$CXml = new CXml($this->grupo);
			$xmlgen = new xmlgen();
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
			//$CAssinaturaDigital = new CAssinaturaDigital(); Em Pinhais nao faz autenticacao com certificado digital, 
			$DDoc = new DOMDocument();


			if($pChamada == "COBOL")
			{
				$CXml->xml = $this->xmlEnvioPinhais($xml_tve_cobol, "");
			}
			else
			{
				//Retorna arquivo XML para comunicar com webservice
				if(!$CXml->xmlPinhais($this->prestadorCnpj, $this->numeroControle)){
					$this->mensagemErro = $CXml->mensagemErro;
					return false;
				}
			}
			$arrayXmlEnvio = $CXml->xml;
			$caminhoXml = "/var/www/html/nf/nfse/enviados/".$this->prestadorCnpj.$this->numeroControle.".xml";
			if(!file_put_contents($caminhoXml, $arrayXmlEnvio)){
			  $this->mensagemErro = "CComunicadorWebService->wsPinhais {nao eh possivel criar o arquivo [".$this->prestadorCnpj.$this->numeroControle.".xml"."] na pasta enviados }";
			  return false;
			}

			if($pChamada == "COBOL")
			{
				$this->ConfigWs[0]['usuario_prefeitura']  = $this->usuarioPrefeitura;
				$this->ConfigWs[0]['senha_prefeitura'  ]  = $this->senhaPrefeitura;
				$this->ConfigWs[0]['codigo_tom_cidade' ]  = $this->codigoTom;
			}
			else
			{
				$this->obterConfiguracoesWS();
			}


			if(!$this->executarWS()){
				return false;
			}

			if($pChamada == "COBOL")
			{
				return true;
			}
			else
			{
				if(trim($this->codigoVerificacao) != "" && substr($this->mensagemCodigo,0,5) == "00001"){
				  if(!$this->gravarRetorno("Sucesso!", "N")){ return false; }
				}else{
				  if(!$this->gravarRetorno($this->mensagemCodigo, "S")){ return false; }
				}
			}
			return true;
		}

		public function xmlEnvioPinhais($xml_tve_cobol, $pDadosTXT, $pStatus="")
		{
			if($pStatus == "C")
			{
				$arrayXml['nf']['numero'] = trim($pDadosTXT[3]);
				$arrayXml['nf']['situacao'] = $pStatus;
				$arrayXml['nf']['valor_total'] = "";
				$arrayXml['nf']['valor_desconto'] = "";
				$arrayXml['nf']['valor_ir'] = "";
				$arrayXml['nf']['valor_inss'] = "";
				$arrayXml['nf']['valor_contribuicao_social'] = "";
				$arrayXml['nf']['valor_rps'] = "";
				$arrayXml['nf']['valor_pis'] = "";
				$arrayXml['nf']['valor_cofins'] = "";
				$arrayXml['nf']['observacao'] = trim(str_replace("\n", "", $pDadosTXT[5]));
				$arrayXml['prestador']['cpfcnpj'] = trim($pDadosTXT[0]);
				$arrayXml['prestador']['cidade'] = substr(ltrim($pDadosTXT[12],0),0,-1);
				$arrayXml['tomador']['tipo'] = "";
				$arrayXml['tomador']['identificador'] = "";
				$arrayXml['tomador']['estado'] = "";
				$arrayXml['tomador']['pais'] = "";
				$arrayXml['tomador']['cpfcnpj'] = "";
				$arrayXml['tomador']['ie'] = "";
				$arrayXml['tomador']['nome_razao_social'] = "";
				$arrayXml['tomador']['sobrenome_nome_fantasia'] = "";
				$arrayXml['tomador']['logradouro'] = "";
				$arrayXml['tomador']['email'] = "";
				$arrayXml['tomador']['numero_residencia'] = "";
				$arrayXml['tomador']['complemento'] = "";
				$arrayXml['tomador']['ponto_referencia'] = "";
				$arrayXml['tomador']['bairro'] = "";
				$arrayXml['tomador']['cidade'] = "";
				$arrayXml['tomador']['cep'] = "";
				$arrayXml['tomador']['ddd_fone_comercial'] = "";
				$arrayXml['tomador']['fone_comercial'] = "";
				$arrayXml['tomador']['ddd_fone_residencial'] = "";
				$arrayXml['tomador']['fone_residencial'] = "";
				$arrayXml['tomador']['ddd_fax'] = "";
				$arrayXml['tomador']['fone_fax'] = "";
				$arrayXml['produtos']['descricao'] = "";
				$arrayXml['produtos']['valor'] = "";
			}
			else
			{
				$arrayXml['nf']['situacao'] = $pStatus;
				$arrayXml['nf']['valor_total'] = str_replace(".",",",$xml_tve_cobol->nf->valor_total);
				$arrayXml['nf']['valor_desconto'] = str_replace(".",",",$xml_tve_cobol->nf->valor_desconto);
				$arrayXml['nf']['valor_ir'] = str_replace(".",",",$xml_tve_cobol->nf->valor_ir);
				$arrayXml['nf']['valor_inss'] = str_replace(".",",",$xml_tve_cobol->nf->valor_inss);
				$arrayXml['nf']['valor_contribuicao_social'] = str_replace(".",",",$xml_tve_cobol->nf->valor_contribuicao_social);
				$arrayXml['nf']['valor_rps'] = str_replace(".",",",$xml_tve_cobol->nf->valor_rps);
				$arrayXml['nf']['valor_pis'] = str_replace(".",",",$xml_tve_cobol->nf->valor_pis);
				$arrayXml['nf']['valor_cofins'] = str_replace(".",",",$xml_tve_cobol->nf->valor_cofins);
				$arrayXml['nf']['observacao'] = $xml_tve_cobol->nf->observacao;
				$arrayXml['prestador']['cpfcnpj'] = $xml_tve_cobol->prestador->cpfcnpj;
				#t69281//$arrayXml['prestador']['cidade'] = substr(ltrim($xml_tve_cobol->prestador->codTom,0),0,-1);
				$arrayXml['prestador']['cidade'] = ltrim($xml_tve_cobol->prestador->codTom,0);
				$arrayXml['tomador']['tipo'] = $xml_tve_cobol->tomador->tipo;
				$arrayXml['tomador']['identificador'] = $xml_tve_cobol->tomador->identificador;
				$arrayXml['tomador']['estado'] = $xml_tve_cobol->tomador->estado;
				$arrayXml['tomador']['pais'] = $xml_tve_cobol->tomador->pais;
				$arrayXml['tomador']['cpfcnpj'] = $xml_tve_cobol->tomador->cpfcnpj;
				$arrayXml['tomador']['ie'] = $xml_tve_cobol->tomador->ie;
				$arrayXml['tomador']['nome_razao_social'] = $xml_tve_cobol->tomador->nome_razao_social;
				$arrayXml['tomador']['sobrenome_nome_fantasia'] = $xml_tve_cobol->tomador->sobrenome_nome_fantasia;
				$arrayXml['tomador']['logradouro'] = $xml_tve_cobol->tomador->logradouro;
				$arrayXml['tomador']['email'] = $xml_tve_cobol->tomador->email;
				$arrayXml['tomador']['numero_residencia'] = $xml_tve_cobol->tomador->numero_residencia;
				$arrayXml['tomador']['complemento'] = $xml_tve_cobol->tomador->complemento;
				$arrayXml['tomador']['ponto_referencia'] = $xml_tve_cobol->tomador->ponto_referencia;
				$arrayXml['tomador']['bairro'] = $xml_tve_cobol->tomador->bairro;
				$arrayXml['tomador']['cidade'] = $xml_tve_cobol->tomador->codTom; // GJPS 04/02/2014
				$arrayXml['tomador']['cep'] = $xml_tve_cobol->tomador->cep;
				$arrayXml['tomador']['ddd_fone_comercial'] = $xml_tve_cobol->tomador->ddd_fone_comercial;
				$arrayXml['tomador']['fone_comercial'] = substr($xml_tve_cobol->tomador->fone_comercial,-9); // GJPS 17-3-14  ticket  22918
				$arrayXml['tomador']['ddd_fone_residencial'] = $xml_tve_cobol->tomador->ddd_fone_residencial;
				$arrayXml['tomador']['fone_residencial'] = substr($xml_tve_cobol->tomador->fone_residencial,-9); // GJPS 17-3-14  ticket 22918
				$arrayXml['tomador']['ddd_fax'] = $xml_tve_cobol->tomador->ddd_fax;
				$arrayXml['tomador']['fone_fax'] = substr($xml_tve_cobol->tomador->fone_fax,-9); // GJPS 17-3-14  ticket 22918
				$arrayXml['produtos']['descricao'] = $xml_tve_cobol->produtos_descricao;
				$arrayXml['produtos']['valor'] = $xml_tve_cobol->produtos_valor_total;

				$iten = 0;
				do{
					$arrayXml['itens'][$iten]['lista']['tributa_municipio_prestador'] = $xml_tve_cobol->itens->lista[$iten]->tributa_municipio_prestador;
					#t69281//$arrayXml['itens'][$iten]['lista']['codigo_local_prestacao_servico'] = substr(ltrim($xml_tve_cobol->itens->lista[$iten]->codigo_tom_prestacao_servico,0),0,-1);
					$arrayXml['itens'][$iten]['lista']['codigo_local_prestacao_servico'] = ltrim($xml_tve_cobol->itens->lista[$iten]->codigo_tom_prestacao_servico,0);
					$arrayXml['itens'][$iten]['lista']['unidade_codigo'] = $xml_tve_cobol->itens->lista[$iten]->unidade_codigo;
					$arrayXml['itens'][$iten]['lista']['unidade_quantidade'] = $xml_tve_cobol->itens->lista[$iten]->unidade_quantidade;
					$arrayXml['itens'][$iten]['lista']['unidade_valor_unitario'] = str_replace(".",",",$xml_tve_cobol->itens->lista[$iten]->unidade_valor_unitario);
					$arrayXml['itens'][$iten]['lista']['codigo_item_lista_servico'] = $xml_tve_cobol->itens->lista[$iten]->codigo_item_lista_servico;
					$arrayXml['itens'][$iten]['lista']['descritivo'] = $xml_tve_cobol->itens->lista[$iten]->descritivo;
					$arrayXml['itens'][$iten]['lista']['aliquota_item_lista_servico'] = str_replace(".",",",$xml_tve_cobol->itens->lista[$iten]->aliquota_item_lista_servico);
					$arrayXml['itens'][$iten]['lista']['situacao_tributaria'] = str_pad($xml_tve_cobol->itens->lista[$iten]->situacao_tributaria,1,0,STR_PAD_LEFT);
					$arrayXml['itens'][$iten]['lista']['valor_tributavel'] = str_replace(".",",",$xml_tve_cobol->itens->lista[$iten]->valor_tributavel);
					$arrayXml['itens'][$iten]['lista']['valor_deducao'] = str_replace(".",",",$xml_tve_cobol->itens->lista[$iten]->valor_deducao);
					$arrayXml['itens'][$iten]['lista']['valor_issrf'] = str_replace(".",",",$xml_tve_cobol->itens->lista[$iten]->valor_issrf);
					$iten++;
				} while($xml_tve_cobol->itens->lista[$iten] != NULL);
			}


			
			$xmlgen = new xmlgen();
			$xml_retorno = $xmlgen->generate('nfse',$arrayXml);
			return $xml_retorno;
		}

		public function cancelarRPS($pCnpj, $pNumeroControle, $pDiretorio="", $pChamada="", $pDadosTXT="", $ambiente="", $xml_tve_cobol=""){
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;


			
			$CXml = new CXml($this->grupo);
			$xmlgen = new xmlgen();
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
//			$CAssinaturaDigital = new CAssinaturaDigital(); Em Pinhais nao faz autenticacao com certificado digital, 
			$DDoc = new DOMDocument();



			//Retorna arquivo XML para comunicar com webservice
			if($pChamada == "COBOL")
			{
				$CXml->xml = $this->xmlEnvioPinhais($xml_tve_cobol, $pDadosTXT, "C");
				//$CXml->xmlPinhais($this->prestadorCnpj, $this->numeroControle, "C");
			}
			else
			{
				$CXml->xmlPinhais($this->prestadorCnpj, $this->numeroControle, "C");
			}


			
			$arrayXmlEnvio = $CXml->xml;
			$caminhoXml = "/var/www/html/nf/nfse/enviados/".$this->prestadorCnpj.$this->numeroControle.".xml";
			if(!file_put_contents($caminhoXml, $arrayXmlEnvio)){
			  $this->mensagemErro = "CComunicadorWebService->wsPinhais {nao eh possivel criar o arquivo [".$this->prestadorCnpj.$this->numeroControle.".xml"."] na pasta enviados }";
			  return false;
			}

			if($pChamada == "COBOL")
			{
				$this->ConfigWs[0]['usuario_prefeitura'] = $this->usuarioPrefeitura;
				$this->ConfigWs[0]['senha_prefeitura']   = $this->senhaPrefeitura;
				$this->ConfigWs[0]['codigo_tom_cidade']  = $this->codigoTom;
			}
			else
			{
				$this->obterConfiguracoesWS();
			}



			if(!$this->executarWS()){
				return false;
			}

			if($pChamada == "COBOL")
			{
				return true;
			}
			else
			{
				if(substr($this->mensagemCodigo,0,5) == "00001"){
				  if(!$this->gravarRetorno("NF Cancelada com Sucesso!", "N", "S")){ return false; }
				}else{
				  if(!$this->gravarRetorno($this->mensagemCodigo, "S", "S")){ return false; }
				}
			}

			
			return true;
		}
		

		private function wsRespostaCuritiba(){
			$DDoc = new DOMDocument();
			$CNotaFiscal = new CNotaFiscal();
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->codEmpresa, $this->codFilial, $this->numeroControle);
			
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			$pXmlConsulta = '';
			$pXmlConsulta .= '<ConsultarLoteRpsEnvio>';
			$pXmlConsulta .= '<Prestador>';
			$pXmlConsulta .= '<Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
			$pXmlConsulta .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
			$pXmlConsulta .= '</Prestador>';
			$pXmlConsulta .= '<Protocolo>'.$this->retornoWS['protocolo'].'</Protocolo>';
			$pXmlConsulta .= '</ConsultarLoteRpsEnvio>';

			if(!$this->executarWS('ConsultarLoteRps', $pXmlConsulta, "N", "")){
				return false;
			}

			file_put_contents("/user/objetos/outro.xml", $this->xmlRetornoWS);
			$DDoc->loadXML($this->xmlRetornoWS);
			$this->retornoWS['codVerificacao'] = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
			$this->retornoWS['numeroNF'] = $DDoc->getEementsByTagName("Numero")->item(0)->nodeValue;
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
		/*private function obterConfiguracoesWS(){
		  $CConfig = new CConfig();
		  if(!$CConfig->lerArquivo()){
			$this->mensagemErro = $CConfig->mensagemErro;
			return false;
		  }else{
			$this->ConfigWs = $CConfig->configWs;
			return true;
		  }
		}*/

		/* Metodos privados chamado internamente pela classe para obter as configuracoes da Empresa */ 
		private function obterConfiguracoesWS(){
		  $CEmpresa = new CEmpresa($this->grupo);
		  $CEmpresa->cnpj = $this->prestadorCnpj;
		  $retorno = $CEmpresa->obter();

		  if(!$retorno || $retorno == null){
            $this->mensagemErro = $CEmpresa->mensagemErro;
            return false;
		  }else{
            $this->ConfigWs = $retorno;
            return true;
		  }
		}

		private function executarWS(){
		  $ch = curl_init();
		  curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1"); 
		  curl_setopt($ch, CURLOPT_POST, 1);
		  //curl_setopt($ch, CURLOPT_URL, "http://nfs-e.net/datacenter/include/nfw/importa_nfw/nfw_import_upload.php?eletron=1"); // Fixo #71139 - personalize
		  curl_setopt($ch, CURLOPT_URL, "http://sync.nfs-e.net/datacenter/include/nfw/importa_nfw/nfw_import_upload.php?eletron=1"); // Fixo
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

		  $postParams['login']  = $this->ConfigWs[0]['usuario_prefeitura'];
		  $postParams['senha']  = $this->ConfigWs[0]['senha_prefeitura'];
		  $postParams['cidade'] = $this->ConfigWs[0]['codigo_tom_cidade'];
		  //$postParams['f1']		= "/var/www/html/nf/nfse/enviados/".$this->prestadorCnpj.$this->numeroControle.".xml";
		  $postParams['f1']     = new cURLFile("/var/www/html/nf/nfse/enviados/".$this->prestadorCnpj.$this->numeroControle.".xml");

		  //file_put_contents("/var/www/html/nf/nfse/enviados/saida.txt", $result);

		  curl_setopt($ch, CURLOPT_POSTFIELDS, $postParams);
		  $result= curl_exec($ch);
		
		  file_put_contents("/var/www/html/nf/nfse/enviados/saida.txt", $result);

		  if (curl_errno($ch)) {
			  $this->mensagemErro = "CPinhais->executarWS {erro no funcao cURL [".curl_error($ch)."]}";
			  return false;
		  }
		  curl_close ($ch);

		  $xmlRetornoWS = simplexml_load_string($result);

		  /*
			Variaveis de Retorno da NF
			retorno->mensagem->codigo (o codigo vem junto com a descricacao)
			retorno->numero_nfse
			retorno->serie_nfse
			retorno->data_nfse
			retorno->hora_nfse
			retorno->arquivo_gerador_nfse
			retorno->link_nfse
			retorno->cod_verificador_autenticidade
			retorno->codigo_html
		  */
//		  if(!is_array($xmlRetornoWS)){ return false; }

		  //print_r($xmlRetornoWS);

		  $this->numeroNota = $xmlRetornoWS->numero_nfse;
		  $this->serieNota = $xmlRetornoWS->serie_nfse;
		  $this->link = $xmlRetornoWS->link_nfse;
		  $this->codigoVerificacao = $xmlRetornoWS->cod_verificador_autenticidade;
		  $this->mensagemCodigo = $xmlRetornoWS->mensagem->codigo;


		  return true;
		}
		
		private function gravarRetorno($mensagem="", $erro="N", $pCancelamento="N"){
		  $CCritica = new CCritica($this->grupo);
		  $CNotaFiscal = new CNotaFiscal($this->grupo);
		  $CEmail = new CEmail($this->grupo);
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
		  
		  $xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
		  $xmlAtualizar = simplexml_load_string($xmlAtualizar);
		  if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		  }

		  if($pCancelamento != "S" && $this->status == "S"){
		/*	if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}*/
		  }
		  return true;
		}
	}
?>