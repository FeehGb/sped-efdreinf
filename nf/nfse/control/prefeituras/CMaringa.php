<?php
	/*
		Classe:					CMaringa.php
		Autor:					Guilherme Silva
		Data:					15/04/2014
		Finalidade: 			Classe responsavel pela comunicacao com o WebService de Maringa/PR
		Programas chamadores:
		Programas chamados:
	*/
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	require_once("/var/www/html/nf/nfse/model/CLote.php");

	class CMaringa{
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
		public $nroRps;
		public $keyPassCertificado;
		
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
		
		public function enviarRPS($pCnpj, $pNumeroControle, $pChamada="", $pDadosTXT="", $xml_tve_cobol=""){
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;

			// Obter configuracoes do arquivo config.ini
			$this->obterConfiguracoesWS($pCnpj);

			$CXml = new CXml($this->grupo);
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument();

			if($pChamada == "COBOL")
			{
				$CXml->xml = $this->xmlEnvioMaringa($xml_tve_cobol, "");
			}
			else
			{
				/* Obter XML para comunicar com webservice */
				if(!$CXml->xmlMaringa($this->prestadorCnpj, $this->numeroControle)){
				  $this->mensagemErro = $CXml->mensagemErro;
				  return false;
				}
			}
			

			if(!$this->executarWS('EnviarLoteRpsSincrono', $CXml->xml, "S", 'LoteRps', '')){
				return false;
			}
			
			$DDoc->loadXML($this->xmlRetornoWS);
			$return = $DDoc->getElementsByTagName("return");
			$xml = htmlspecialchars_decode($return->item(0)->nodeValue);
			
			$DDoc->loadXML($xml);
			$numeroLote = $DDoc->getElementsByTagName("NumeroLote");
			$dataRecebimento = $DDoc->getElementsByTagName("DataRecebimento");
			$protocolo = $DDoc->getElementsByTagName("Protocolo");
			
			$this->retornoWS['numeroLote'] = $numeroLote->item(0)->nodeValue;
			$this->retornoWS['dataRecebimento'] = $dataRecebimento->item(0)->nodeValue;
			$this->protocolo = $this->retornoWS['protocolo'] = $protocolo->item(0)->nodeValue;
			
			// Não é necessário pois o retorno é SÍNCRONO (UFA ESTAMOS QUASE LÁ NO SINCRONISMO) 10-06-2014 - GJPS - a 2 dias para a copa do mundo da Fifa no Brasil
			// Que bom! Já pra mim falta 1 ano e meio para a copa! Porém, faltam apenas 2 dias para as Olimpíadas! 02-08-2016 - J. Eduardo N. Lino
			//sleep(10);

			// Mensagem
			$mensagem = $DDoc->getElementsByTagName("Mensagem");
			$this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;

			if($this->retornoWS['mensagem'] == ""){
			  // Numero Nota
			  $numeroNf = $DDoc->getElementsByTagName("Numero");
			  $this->retornoWS['numeroNF'] = $this->numeroNota = $numeroNf->item(0)->nodeValue;
			  
			  // Serie
			  $serieNf = $DDoc->getElementsByTagName("Serie");
			  $this->retornoWS['serieNF'] = $this->serieNota = $serieNf->item(0)->nodeValue;
			  
			  // Codigo Verificacao
			  $codigoVerificacao = $DDoc->getElementsByTagName("CodigoVerificacao");
			  $this->retornoWS['codVerificacao'] = $this->codigoVerificacao = $codigoVerificacao->item(0)->nodeValue;
			  
			  // Link [https://isseteste.maringa.pr.gov.br] + [/print/nfse/cnpj/] + CNPJ + [/codval/] + CODIGO VERIFICACAO + [/numnfe/] + NUMERO NF + [/]
			  $this->link = '/print/nfse/cnpj/'.$this->prestadorCnpj.'/codval/'.$this->retornoWS['codVerificacao'].'/numnfe/'.$this->retornoWS['numeroNF'].'/';
			  if($this->ConfigWs[0]['flag_producao'] == "H"){
				$this->link = 'https://isseteste.maringa.pr.gov.br'.$this->link;
			  }elseif($this->ConfigWs[0]['flag_producao'] == "P"){
				$this->link = 'https://isse.maringa.pr.gov.br'.$this->link;
			  }else{
				$this->link = "";
			  }
			}

			if($this->retornoWS['codVerificacao'] != "" && $this->retornoWS['numeroNF'] != ""){
				$CLote = new CLote($this->grupo);
				if(!$CLote->incrementarRps($pCnpj)){
					$this->mensagemErro = $CLote->mensagemErro;
					return false;
				}
				if(!$this->gravarRetorno("Sucesso","N")){
					echo "\n\n ".$this->mensagemErro." \n\n";
					return false;
				}
			}else if($this->retornoWS['mensagem'] != ""){
				if(!$this->gravarRetorno($this->retornoWS['mensagem'],"S")){
				  return false;
				}
			}else{
				if(!$this->gravarRetorno("Erro nao catalogado, entre em contato com o suporte tecnico.","S")){
				  return false;
				}
			}
			return true;

		}
		
		public function getkeyPassCertificadoDigital($pCnpj){
		
		    $arquivoIni = parse_ini_file("/var/www/html/nf/nfe/config/config.ini");
		    if(!$arquivoIni){
		      echo "Erro ao abrir o arquivo config.ini";
		    }
		    return $arquivoIni[trim($pCnpj)];
		}
		
		public function cancelarRPS($pCnpj, $pNumeroControle, $pChamada="", $pDadosTXT="", $xml_tve_cobol=""){
			$this->prestadorCnpj = $pCnpj;
			$this->numeroControle = $pNumeroControle;
    
            if($pChamada == "COBOL")
            {
              $this->keyPassCertificado = $this->getkeyPassCertificadoDigital($pCnpj);
            }
            else
            {
              $this->obterConfiguracoesWS($pCnpj);
            } 
                   
			$CXml = new CXml($this->grupo);
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument();


			if($pChamada == "COBOL") 
			{
				$dados_txt_cnpj = trim($pDadosTXT[0]);
				$dados_txt_uf = trim($pDadosTXT[1]);
				$dados_txt_tipo_emissao = trim($pDadosTXT[2]);
				$dados_txt_chave = trim($pDadosTXT[3]);
				$dados_txt_rps = trim($pDadosTXT[4]);
				$dados_txt_justificativa = trim($pDadosTXT[5]);
				$dados_txt_ambiente = trim($pDadosTXT[6]);
				$dados_txt_ibge = trim($pDadosTXT[7]);
				$dados_txt_insc_municip = trim($pDadosTXT[8]);
				$dados_txt_protocolo = trim($pDadosTXT[9]);
				$dados_txt_usuarioPrefeitura = trim($pDadosTXT[10]);
				$dados_txt_senhaPrefeitura = trim($pDadosTXT[11]);
				$dados_txt_cod_tom = str_replace("\n", "", trim($pDadosTXT[12]));

				$this->cancelamento=true;
				$xmlCancelamento = '<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.abrasf.org.br/nfse.xsd nfse_v2.01.xsd ">';
				$xmlCancelamento .= '<Pedido>';
	            $xmlCancelamento .= '<InfPedidoCancelamento Id="L1">';
	            $xmlCancelamento .= '<IdentificacaoNfse>';
	            $xmlCancelamento .= '<Numero>'.$dados_txt_chave.'</Numero>';
	            $xmlCancelamento .= '<CpfCnpj><Cnpj>'.$dados_txt_cnpj.'</Cnpj></CpfCnpj>';
	            $xmlCancelamento .= '<InscricaoMunicipal>'.$dados_txt_insc_municip.'</InscricaoMunicipal>';
	            $xmlCancelamento .= '<CodigoMunicipio>'.$dados_txt_cod_tom.'</CodigoMunicipio>';
	            $xmlCancelamento .= '</IdentificacaoNfse>';
	            $xmlCancelamento .= '<CodigoCancelamento>1</CodigoCancelamento>';
	            $xmlCancelamento .= '</InfPedidoCancelamento>';
	            $xmlCancelamento .= '</Pedido>';
				$xmlCancelamento .= '</CancelarNfseEnvio>';

				$this->numeroNota = $dados_txt_chave;
				$this->serieNota = "0";
			}
			else
			{
				/* Obter XML para comunicar com webservice */
				if(!($tabelaNf = $CNotaFiscal->obterNotaFiscal($this->prestadorCnpj, $this->numeroControle))){
				  $this->mensagemErro = $CNotaFiscal->mensagemErro;
				  return false;
				}

				$this->cancelamento=true;
				$xmlCancelamento = '<CancelarNfseEnvio xmlns="http://www.abrasf.org.br/nfse.xsd" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.abrasf.org.br/nfse.xsd nfse_v2.01.xsd ">';
				$xmlCancelamento .= '<Pedido>';
	            $xmlCancelamento .= '<InfPedidoCancelamento Id="L1">';
	            $xmlCancelamento .= '<IdentificacaoNfse>';
	            $xmlCancelamento .= '<Numero>'.$tabelaNf->fields['nf_numero'].'</Numero>';
	            $xmlCancelamento .= '<CpfCnpj><Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj></CpfCnpj>';
	            $xmlCancelamento .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
	            $xmlCancelamento .= '<CodigoMunicipio>'.$tabelaNf->fields['prestador_cidade'].'</CodigoMunicipio>';
	            $xmlCancelamento .= '</IdentificacaoNfse>';
	            $xmlCancelamento .= '<CodigoCancelamento>1</CodigoCancelamento>';
	            $xmlCancelamento .= '</InfPedidoCancelamento>';
	            $xmlCancelamento .= '</Pedido>';
				$xmlCancelamento .= '</CancelarNfseEnvio>';

				$this->numeroNota = $tabelaNf->fields['nf_numero'];
				$this->serieNota = $tabelaNf->fields['nf_serie'];
			}

			if(!$this->executarWS('CancelarNfse', $xmlCancelamento, "S", 'InfPedidoCancelamento', "Pedido")){
			  return false;
			}

			$DDoc->loadXML($this->xmlRetornoWS);
			$return = $DDoc->getElementsByTagName("return");
			$xml = htmlspecialchars_decode($return->item(0)->nodeValue);

			$DDoc->loadXML($xml);
			$mensagem = $DDoc->getElementsByTagName("Mensagem");
			$this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;
			
			$codigo = $DDoc->getElementsByTagName("CodigoCancelamento");

			if($this->retornoWS['mensagem'] != ""){
				$this->mensagemErro = $this->retornoWS['mensagem'];
				$this->status = "N";
				return true;
			}elseif($codigo->item(0)->nodeValue != ""){
				$this->mensagemErro = "NF Cancelada com Sucesso!";
				$this->status = "S";
				return true;
				if(!$this->gravarRetorno("NF Cancelada com Sucesso!", "N", "S")){
				  return true;
				}
			}else{
				$this->mensagemErro = "Erro inesperado no cancelamento!";
				return true;
			}

			return true;
		}


		public function xmlEnvioMaringa($xml_tve_cobol, $pStatus="")
		{
			$DomXml = new DOMDocument();

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = 0.00;
			$valorIncondicionado = 0.00;
			$valorIssRf = "";
			$valorTributavel = "";
			$descritivo = "";

			$iten = 0;
			do{
				if($situacao_tributaria != "" && $situacao_tributaria != $xml_tve_cobol->itens->lista[$iten]->situacao_tributaria){
					$this->mensagemErro = "CXml->CMaringa{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
					file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Maringa) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
					return false;
				}

				$situacao_tributaria = $xml_tve_cobol->itens->lista[$iten]->situacao_tributaria;
				//$valorCsll += str_replace(",",".",$value['valor_csll']);
				$valorCsll += "";
				$valorIss += str_replace(",",".",$xml_tve_cobol->itens->lista[$iten]->valor_iss);
				$valorCondicionado += str_replace(",",".",$xml_tve_cobol->itens->lista[$iten]->desconto_cond);
				$valorIncondicionado += str_replace(",",".",$xml_tve_cobol->itens->lista[$iten]->desconto_incond);
				$valorIssRf += str_replace(",",".",$xml_tve_cobol->itens->lista[$iten]->valor_issrf);
				$valorTributavel += str_replace(",",".",$xml_tve_cobol->itens->lista[$iten]->valor_tributavel);
				$aliquotaItemServico = $xml_tve_cobol->itens->lista[$iten]->aliquota_item_lista_servico;
				$codigoLista = ltrim($xml_tve_cobol->itens->lista[$iten]->codigo_item_lista_servico,0);
				$codigoLista = substr($codigoLista,0,2)."".substr($codigoLista,2);
				$descritivo .= $xml_tve_cobol->itens->lista[$iten]->descritivo.' \r\n ';

				$codigoLocalPrestacaoServico = $xml_tve_cobol->itens->lista[$iten]->codigo_local_prestacao_servico;
				$tributa_municipio_prestador = $xml_tve_cobol->itens->lista[$iten]->tributa_municipio_prestador;

				$iten++;
			} while($xml_tve_cobol->itens->lista[$iten] != NULL);
 
			$EnviarLoteRpsEnvio = $DomXml->createElement("EnviarLoteRpsSincronoEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns","http://www.abrasf.org.br/nfse.xsd");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:ds","http://www.w3.org/2000/09/xmldsig#");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xsi:schemaLocation","http://www.abrasf.org.br/nfse.xsd nfse_v2.01.xsd");
			//$EnviarLoteRpsEnvio->setAttribute("soapenv:encodingStyle","http://schemas.xmlsoap.org/soap/encoding/");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			
			$LoteRps->setAttribute("versao","2.01");
			$LoteRps->setAttribute("Id","L1");
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $xml_tve_cobol->nf->numero); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $LoteRps->appendChild($CpfCnpj);
				if(strlen($xml_tve_cobol->prestador->cpfcnpj)<=11){
					$Cnpj = $DomXml->createElement("Cpf", $xml_tve_cobol->prestador->cpfcnpj); $CpfCnpj->appendChild($Cnpj);
				}else{
					$Cnpj = $DomXml->createElement("Cnpj", $xml_tve_cobol->prestador->cpfcnpj); $CpfCnpj->appendChild($Cnpj);
				}
			$this->prestadorCnpj = $xml_tve_cobol->prestador->cpfcnpj;
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $xml_tve_cobol->prestador->inscricaomunicipal); $LoteRps->appendChild($InscricaoMunicipal);
			/* Criar a tag <QuantidadeRps>	*/
			$QuantidadeRps = $DomXml->createElement("QuantidadeRps", "1"); $LoteRps->appendChild($QuantidadeRps);
			/* Criar a tag <ListaRps>	*/
			$ListaRps = $DomXml->createElement("ListaRps"); $LoteRps->appendChild($ListaRps);
			/* Criar a tag <Rps>	*/
			$Rps = $DomXml->createElement("Rps"); $ListaRps->appendChild($Rps);
			/* Criar a tag <InfRps>	*/
			$InfRps = $DomXml->createElement("InfDeclaracaoPrestacaoServico"); $Rps->appendChild($InfRps);
			/* Criar a tag <Rps>	*/
			$Rps = $DomXml->createElement("Rps"); $InfRps->appendChild($Rps);
			$Rps->setAttribute("Id","rps");
			/* Criar a tag <IdentificacaoRps>	*/
			$IdentificacaoRps = $DomXml->createElement("IdentificacaoRps"); $Rps->appendChild($IdentificacaoRps);
			/* Criar a tag <Numero>	*/
			// SOMENTE PARA CURITIBA O NUMERO DA NOTA SERA IDENTIFICADO PELO NUMERO DO RPS DA TABELA LOTE E NAO PELO
			// NUMERO PROVENIENTE DO SISTEMA
			$Numero = $DomXml->createElement("Numero", (string) $xml_tve_cobol->nf->controle); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $xml_tve_cobol->nf->serie); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $xml_tve_cobol->prestador->tipoRps); $IdentificacaoRps->appendChild($Tipo); //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ **
			/* Criar a tag <DataEmissao>	*/
			$DataEmissao = $DomXml->createElement("DataEmissao", date("Y-m-d")); $Rps->appendChild($DataEmissao);
			/* Criar a tag <NaturezaOperacao>	*/
				//$NaturezaOperacao = $DomXml->createElement("NaturezaOperacao", $situacao_tributaria); $InfRps->appendChild($NaturezaOperacao);
			/* Criar a tag <Status>	*/
			if($pStatus == "C"){
			  $Status = $DomXml->createElement("Status", "2");
			}else{
			  $Status = $DomXml->createElement("Status", "1");
			}
			$Rps->appendChild($Status);

			$Competencia = $DomXml->createElement("Competencia",date("Y-m-d")); $InfRps->appendChild($Competencia);
			/* CRIADO A TAG DOS ITENS DO SERVI�O */
				  /* Criar a tag <Servico>	*/
				  $Servico = $DomXml->createElement("Servico"); $InfRps->appendChild($Servico);
				  /* Criar a tag <Valores>	*/
				  $Valores = $DomXml->createElement("Valores"); $Servico->appendChild($Valores);
				  /* Criar a tag <ValorServicos>	*/
				  $servicosValor = $xml_tve_cobol->nf->valor_total;
				  $ValorServicos = $DomXml->createElement("ValorServicos", str_replace(",",".",$servicosValor)); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorDeducoes>	*/
				  $valorDeducoes = "0.00";
				  $ValorDeducoes = $DomXml->createElement("ValorDeducoes", $valorDeducoes);
				  $Valores->appendChild($ValorDeducoes);
				  /* Criar a tag <ValorPis>	*/
				  $valorPis = str_replace(",",".",$xml_tve_cobol->nf->valor_pis);
				  $ValorPis = $DomXml->createElement("ValorPis", $valorPis);
				  $Valores->appendChild($ValorPis);
				  /* Criar a tag <ValorCofins>	*/
				  $valorCofins = str_replace(",",".",$xml_tve_cobol->nf->valor_cofins);
				  $ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorInss = str_replace(",",".",$xml_tve_cobol->nf->valor_inss);
				  $ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  $Valores->appendChild($ValorInss);
                                  
				  /* Criar a tag <ValorIr>	*/
				  $valorIr = str_replace(",",".",$xml_tve_cobol->nf->valor_ir);
				  $ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  $Valores->appendChild($ValorIr);
				  
				  $valorContribuicaoSocial = str_replace(",",".",$xml_tve_cobol->nf->valor_contribuicao_social);
				  $ValorCsll = $DomXml->createElement("ValorCsll", $valorContribuicaoSocial);     
				  $Valores->appendChild($ValorCsll);                                              
				  
				  /* Criar a tag <ValorCsll>	Este ja esta somado a cima pois vem de cada item */
/*				  $ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
				  $Valores->appendChild($ValorCsll);*/
				  /* Criar a tag <OutrasRetencoes>	*/
				  $outrasRetencoes = "0.00";
				  $ValorIss = $DomXml->createElement("OutrasRetencoes", $outrasRetencoes);
				  $Valores->appendChild($ValorIss);
  				  /* Criar a tag <ValorIss>	*/
				  $ValorIss = $DomXml->createElement("ValorIss", $valorIss);
				  $Valores->appendChild($ValorIss);
  				  /* Criar a tag <ValorIssRf>	*/
				  /*$ValorIssRf = $DomXml->createElement("ValorIssRetido", $valorIssRf);
				  $Valores->appendChild($ValorIssRf);*/

					/* Criar a tag <BaseCalculo>	*/
					//$BaseCalculo = $DomXml->createElement("BaseCalculo", $valorTributavel); $Valores->appendChild($BaseCalculo);
				  /* Criar a tag <Aliquota>	*/
				  $Aliquota = $DomXml->createElement("Aliquota", number_format(str_replace(",",".",$aliquotaItemServico), 2, '.', '')); $Valores->appendChild($Aliquota); // N�o divide-se por 100% 
				  /* Criar a tag <ValorLiquidoNfse>	*/
					//$ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",$tabelaNf->fields['nf_valor_total']);$Valores->appendChild($ValorLiquidoNfse);
				  /* Criar a tag <DescontoIncondicionado> */
				  $DescontoIncondicionado=$DomXml->createElement("DescontoIncondicionado",$valorIncondicionado);
				  $Valores->appendChild($DescontoIncondicionado);
				  /* Criar a tag <DescontoCondicionado>	*/
				  $DescontoCondicionado = $DomXml->createElement("DescontoCondicionado", $valorCondicionado);
				  $Valores->appendChild($DescontoCondicionado);
				  
				  /* Criar a tag <IssRetido> */
				  if($valorIssRf != 0){
					$IssRetido = $DomXml->createElement("IssRetido", "1");
				  }else{
					$IssRetido = $DomXml->createElement("IssRetido", "2");
				  } $Servico->appendChild($IssRetido);
				  /* Criar a tag <ResponsavelRetencao>	FIXO SEMPRE TOMADOR*/
				  if($tributa_municipio_prestador == "S"){
					$ResponsavelRetencao = $DomXml->createElement("ResponsavelRetencao", "1"); $Servico->appendChild($ResponsavelRetencao);
				  }else{
				    $ResponsavelRetencao = $DomXml->createElement("ResponsavelRetencao", "2"); $Servico->appendChild($ResponsavelRetencao);
				  }
				  /* Criar a tag <ItemListaServico>	*/
				  $codigoLista = str_replace(".","",$codigoLista);
				  $ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
				  /* Criar a tag <CodigoCnae>	*/
				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $xml_tve_cobol->prestador->cnae); $Servico->appendChild($CodigoCnae);
					/* Criar a tag <CodigoTributacaoMunicipio>	 tributacao do municipio */
					$CodigoTributacaoMunicipio = $DomXml->createElement("CodigoTributacaoMunicipio", $tributa_municipio_prestador == "S" ? "0" : "1"); $Servico->appendChild($CodigoTributacaoMunicipio);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo."   |   ".$xml_tve_cobol->nf->observacao); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/ 
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($xml_tve_cobol->prestador->cidade)); $Servico->appendChild($CodigoMunicipio);
				  /* Criar a tag <CodigoPais>	*/
				  $CodigoPais = $DomXml->createElement("CodigoPais", "1058"); $Servico->appendChild($CodigoPais);
				  /* Criar a tag <ExigibilidadeISS>	*/
				  $ExigibilidadeISS = $DomXml->createElement("ExigibilidadeISS", "1"); $Servico->appendChild($ExigibilidadeISS);
				  /* Criar a tag <MunicipioIncidencia>	*/
				  $MunicipioIncidencia = $DomXml->createElement("MunicipioIncidencia", ltrim($codigoLocalPrestacaoServico)); $Servico->appendChild($MunicipioIncidencia);
				  /* Criar a tag <NumeroProcesso>	*/
				  //$NumeroProcesso = $DomXml->createElement("NumeroProcesso", ""); $Servico->appendChild($NumeroProcesso);

			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $Prestador->appendChild($CpfCnpj);
			
			
			if($xml_tve_cobol->prestador->tipoPessoaPrestador == "F")
			{
                          $Cpf_cnpj = $DomXml->createElement("Cpf",substr($xml_tve_cobol->prestador->cpfcnpj,3));
                        }
                        else if($xml_tve_cobol->prestador->tipoPessoaPrestador == "J")
                        {
                          $Cpf_cnpj = $DomXml->createElement("Cnpj", $xml_tve_cobol->prestador->cpfcnpj);
                        }
                        else
                        {
                          $Cpf_cnpj = $DomXml->createElement("Cpf", "99999999999");
                        }
                        
                        $CpfCnpj->appendChild($Cpf_cnpj);
			
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $xml_tve_cobol->prestador->inscricaomunicipal); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			/* Criar a tag <IdentificacaoTomador>	*/
			$IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
			/* Criar a tag <CpfCnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);

			if($xml_tve_cobol->tomador->tipo == "F"){
			  /* Criar a tag <Cpf>	*/
			  //$Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			  $Cpf = $DomXml->createElement("Cpf",substr($xml_tve_cobol->tomador->cpfcnpj,3));
			}elseif($xml_tve_cobol->tomador->tipo == "J"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $xml_tve_cobol->tomador->cpfcnpj);
			}else{
			  $Cpf = $DomXml->createElement("Cpf", "99999999999");
			}
			$CpfCnpj->appendChild($Cpf);
			
			/* Criar a tag <RazaoSocial>	*/
			$RazaoSocial = $DomXml->createElement("RazaoSocial", $xml_tve_cobol->tomador->nome_razao_social.$xml_tve_cobol->tomador->sobrenome_nome_fantasia);
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Endereco2 = $DomXml->createElement("Endereco", $xml_tve_cobol->tomador->logradouro); $Endereco->appendChild($Endereco2);
			/* Criar a tag <Numero>	*/
			$Numero = $DomXml->createElement("Numero", $xml_tve_cobol->tomador->numero_residencia); $Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($xml_tve_cobol->tomador->complemento) != ""){
			  $Complemento = $DomXml->createElement("Complemento", trim($xml_tve_cobol->tomador->complemento));
			}else{
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $xml_tve_cobol->tomador->bairro); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($xml_tve_cobol->tomador->cidade)); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
			$Uf = $DomXml->createElement("Uf", $xml_tve_cobol->tomador->estado); $Endereco->appendChild($Uf);
			/* Criar a tag <CodigoPais>	*/
			$CodigoPais = $DomXml->createElement("CodigoPais", "1058"); $Endereco->appendChild($CodigoPais);
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $xml_tve_cobol->tomador->cep); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($xml_tve_cobol->tomador->fone_residencial,0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($xml_tve_cobol->tomador->ddd_fone_residencial,0).ltrim($xml_tve_cobol->tomador->fone_residencial,0));
			  $Contato->appendChild($Telefone);
			}

			/* Criar a tag <Email>	*/
                        if(trim($xml_tve_cobol->tomador->email) != ""){
                            $Email = $DomXml->createElement("Email", $xml_tve_cobol->tomador->email); $Contato->appendChild($Email);
                        }

			/* Criar a tag <ConstrucaoCivil> */
			//$ConstrucaoCivil = $DomXml->createElement("ConstrucaoCivil"); $InfRps->appendChild($ConstrucaoCivil);
				/* Criar a tag <CodigoObra>	*/
				//$CodigoObra = $DomXml->createElement("CodigoObra",""); $ConstrucaoCivil->appendChild($CodigoObra);
				/* Criar a tag <Art>	*/
				//$Art = $DomXml->createElement("Art",""); $ConstrucaoCivil->appendChild($Art);
			
			
			/* Criar a tag <RegimeEspecialTributacao>	*/
			//$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", $tabelaNf->fields['nf_regime_especial']);
			//$InfRps->appendChild($RegimeEspecialTributacao);
			/* Criar a tag <OptanteSimplesNacional>	*/
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $xml_tve_cobol->prestador->optantesimples); //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ **
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivoFiscal>	*/
			$IncentivoFiscal = $DomXml->createElement("IncentivoFiscal", $xml_tve_cobol->prestador->incentivadorcultural); //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ **
			$InfRps->appendChild($IncentivoFiscal);
			
			
			$this->xmlTmp = $DomXml->saveXML();
			
			$nomeArquivo = time();
			file_put_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml", $this->xmlTmp);
			$this->xml = file_get_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			
				$DomXml->load("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
				
/*				if(!$DomXml->schemaValidate("curitiba.xsd")){
					echo " \n\n O ESQUEMA EH INVALIDO \n\n";
				}else{
					echo " \n\n O ESQUEMA ESTA OKKK!! \n\n";
				}*/

//			unlink("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			return $this->xml;
		}

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

		  $xmlSoap .= '<soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="https://isseteste.maringa.pr.gov.br/ws/"><soapenv:Body><'.$pMetodo.'><xml><![CDATA[';
//         <xml xsi:type="xsd:string">?</xml>

		  //$xmlSoap .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:e="http://www.betha.com.br/e-nota-contribuinte-ws"><soapenv:Header/><soapenv:Body>';
		  $CAssinaturaDigital->arquivoPFX = "/var/www/html/nf/nfse/certificados/".$this->prestadorCnpj.".pfx";
		  $CAssinaturaDigital->senhaPFX = $this->ConfigWs[0]['senha_pfx'];
		  //$CAssinaturaDigital->transform = true;
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

		  //$xmlSoap .= '</soapenv:Body></soapenv:Envelope>';
		  $xmlSoap .= ']]></xml></'.$pMetodo.'></soapenv:Body></soapenv:Envelope>';

		// Remover ^M
		  $xmlSoap = str_replace(chr(13),"",$xmlSoap);
		// Remover quebra de linha
		  $xmlSoap = str_replace("\n","",$xmlSoap);

		  $tamanho = strlen($xmlSoap);
		  
		// Validar nota com o padrao XSD gerado gjps090614
			$domXsd = new DOMDocument();
			$domXsd->loadXML($CAssinaturaDigital->xml);
			$is_valid_xml = $domXsd->schemaValidate("/var/www/html/nf/nfse/configuracoes/xsd/maringa/nfse_v2.01.xsd");

		  /* Setar cabecalhos da comunicacao Web Service */
		  $parametrosSoap = Array('Host: isseteste.maringa.pr.gov.br', 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
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
		  
		  //if($this->ConfigWs[0]['flag_producao'] == "H"){
		  //	curl_setopt($oCurl, CURLOPT_URL, "https://isseteste.maringa.pr.gov.br/ws/");
		  //}elseif($this->ConfigWs[0]['flag_producao'] == "P"){
			curl_setopt($oCurl, CURLOPT_URL, "https://isse.maringa.pr.gov.br/ws/");
		  //}

		  curl_setopt($oCurl, CURLOPT_PORT, 443); // porta HTTPS
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
		  
		  /* Executar chamada o servidor */

		  $__xml = curl_exec($oCurl);
		  
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
		  
		  //file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  executarWS(".$pMetodo.") { XML retorno:\n".
		//					$xmlRetorno." }\n\n", FILE_APPEND);
		  $this->xmlRetornoWS = $xmlRetorno;
		  
		  /* Verificar retorno da conexao com servidor */
		  if($info['http_code'] != "200" || $__xml === false){
			$mensagemErro = $DDoc->getElementsByTagName("Text");
			//file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  executarWS(".$pMetodo.") { Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue." }\n\n", FILE_APPEND);
			$this->mensagemErro = "Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue."\n";
			
			return false;
		  }

		  if($this->retornoWS['mensagem'] != ""){
			//file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  executarWS(".$pMetodo.") { Mensagem: ".$this->retornoWS['mensagem']." }\n\n", FILE_APPEND);
			
			
			$this->gravarRetorno($this->retornoWS['mensagem'], "S");
			$this->mensagemErro = utf8_decode($this->retornoWS['mensagem']);

			return false;
		  }else{
			return true;
		  }
		}

		private function gravarRetorno($mensagem="", $erro="N", $pCancelamento="N"){
		  //$CCritica = new CCritica($this->grupo);
		  //$CNotaFiscal = new CNotaFiscal($this->grupo);
		  //$CEmail = new CEmail($this->grupo);
		  $xmlgen = new xmlgen();

		  if($erro == "S"){
			$this->status = "N"; // ocorreu errado
			$arrayAtualizacao['nf']['status'] = "E";
			file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  gravarRetorno() { Falha }\n\n", FILE_APPEND);
		  }else{
			$this->status = "S"; // ocorreu certo
			$arrayAtualizacao['nf']['status'] = "S";
			file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  gravarRetorno() { Sucesso }\n\n", FILE_APPEND);
		  }

		  if($pCancelamento == "S"){
			$arrayAtualizacao['nf']['status'] = "C";
			$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$mensagem);
			file_put_contents("/var/tmp/nfse.log","CMaringa.php\n  gravarRetorno() { CANCELADO }\n\n", FILE_APPEND);
		  }else{
			$this->numeroNota = $this->retornoWS['numeroNF'];
			$this->serieNota = $this->retornoWS['serieNF'];
			$this->nroRps = $this->retornoWS['numeroLote'];
		  }

		  $criticas['codEmpresa'] = $this->codEmpresa;
		  $criticas['codFilial'] = $this->codFilial;
		  $criticas['cnpj'] = $this->prestadorCnpj;
		  $criticas['numeroControle'] = $this->numeroControle;
		  $criticas['data'] = date("d/m/Y");
		  $criticas['hora'] = date("H:i:s");
		  $criticas['descricao'] = utf8_decode($mensagem);

		  //if(!$CCritica->inserirCritica($criticas)){
		//	  $this->mensagemErro = $CCritica->mensagemErro;
		//	  return false;
		 // }

		  // Para as mensagens que retornam erradas
		  $this->criticas = utf8_decode($mensagem);
		  $this->mensagemErro = utf8_decode($mensagem);

		  // Atualizar nota fiscal com o retornado
		  $arrayAtualizacao['prestador']['cpfcnpj'] = $this->prestadorCnpj;
		  $arrayAtualizacao['nf']['controle'] 		= $this->numeroControle;
		  $arrayAtualizacao['nf']['numero'] 		= $this->numeroNota;
		  $arrayAtualizacao['nf']['serie'] 			= $this->serieNota;
		  /* campos já adicionados direto na model
		  $arrayAtualizacao['nf']['data_emissao'] 	= date("d/m/Y");//$result->data_nfse; // nao é obtido do retorno do WS pois pode conter divergencias com nosso servidor local
		  $arrayAtualizacao['nf']['hora_emissao'] 	= date("H:i:s");//$result->hora_nfse;*/
		  $arrayAtualizacao['nf']['link'] 			= $this->link;
		  $arrayAtualizacao['nf']['autenticacao'] 	= $this->codigoVerificacao;
		  $arrayAtualizacao['nf']['protocolo'] 		= $this->protocolo;
		  
		  $xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
		  $xmlAtualizar = simplexml_load_string($xmlAtualizar);
		  //if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
		//	$this->mensagemErro = $CNotaFiscal->mensagemErro;
		//	return false;
		 // }

		  if($pCancelamento != "S" && $this->status == "S"){
			//if(!$CEmail->enviarNF($this->codEmpresa, $this->codFilial, $this->numeroControle)){
			 // $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  //return false;
			//}
		  }
		  return true;
		}
	}
?>
