<?php
	/*
		Classe:					CCuritiba.php
		Autor:					Guilherme Silva
		Data:					15/05/2014 - ult alt
		Finalidade: 			Classe responsavel pela comunicacao com o WebService de Curitiba
		Programas chamadores:
		Programas chamados:
	*/
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");

	class CCuritiba{
		/* Atruibutos publicos utilizada por todos */
		private $grupo;
		
		public $mensagemErro;
		public $prestadorCNPJ;
		public $prestadorInscricaoMunicipal;
		public $ibge;

		public $codEmpresa;
		public $codFilial;
		public $numeroControle;
		public $criticas;
		public $numeroNota;
		public $serieNota;
		public $status;
		public $nroRps;
		public $codigoVerificacao;
		public $protocolo;
		public $keyPassCertificado;
		// Guilherme: adicionado para controlar ambiente (14/4/16)		
		public $ambiente;

		
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
			$this->prestadorCNPJ = $pCnpj;
			$this->numeroControle = $pNumeroControle;
			$this->ambiente = $ambiente;

			$CXml = new CXml($this->grupo);
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$CEmail = new CEmail($this->grupo);

			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument();


			if($pChamada == "COBOL")
			{
				$CXml->xml = $this->xmlEnvioCuritiba($xml_tve_cobol, "");
			}
			else
			{
				/* Obter XML para comunicar com webservice */
				if(!$CXml->xmlCuritiba($this->prestadorCNPJ, $this->numeroControle)){
				  $this->mensagemErro = $CXml->mensagemErro;
				  return false;
				}
			}


			if($pChamada == "COBOL")
			{
				$this->keyPassCertificado = $this->getkeyPassCertificadoDigital($this->prestadorCNPJ);
			}
			else
			{
				$this->obterConfiguracoesWS($this->prestadorCNPJ);
			}

			if(!$this->executarWS('RecepcionarLoteRps', $CXml->xml, "S", 'LoteRps', "", "", "COBOL")){
				return false;
			}


			$DDoc->loadXML($this->xmlRetornoWS);
			$numeroLote = $DDoc->getElementsByTagName("NumeroLote");
			$dataRecebimento = $DDoc->getElementsByTagName("DataRecebimento");
			$protocolo = $DDoc->getElementsByTagName("Protocolo");
			$this->retornoWS['numeroLote'] = $numeroLote->item(0)->nodeValue;
			$this->retornoWS['dataRecebimento'] = $dataRecebimento->item(0)->nodeValue;
			$this->retornoWS['protocolo'] = $protocolo->item(0)->nodeValue;
			$this->retornoWS['prestador_inscricao_municipal'] = $xml_tve_cobol->prestador->inscricaomunicipal;
			$this->retornoWS['prestador_cpf_cnpj'] = $xml_tve_cobol->prestador->cpfcnpj;


			//exit();
			sleep(10);
			if(!$this->wsRespostaCuritiba($pChamada)){
				return false;
			}


			


		}

		public function xmlEnvioCuritiba($xml_tve_cobol, $pStatus="")
		{
			$DomXml = new DOMDocument();
			

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = "";
			$valorIncondicionado = "";
			$valorIssRf = "";
			$valorTributavel = "";

			$iten = 0;
			do{
				if($situacao_tributaria != "" && $situacao_tributaria != $xml_tve_cobol->itens->lista[$iten]->situacao_tributaria){
					$this->mensagemErro = "CXml->CCuritiba{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
					file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Curitiba) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
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
				$iten++;
			} while($xml_tve_cobol->itens->lista[$iten] != NULL);

			$EnviarLoteRpsEnvio = $DomXml->createElement("EnviarLoteRpsEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns","http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xsi:schemaLocation","http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			/* Criar a tag LoteRps>	*/
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $xml_tve_cobol->nf->controle); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $xml_tve_cobol->prestador->cpfcnpj); $LoteRps->appendChild($Cnpj);
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
			$InfRps = $DomXml->createElement("InfRps"); $Rps->appendChild($InfRps);
			/* Criar a tag <IdentificacaoRps>	*/
			$IdentificacaoRps = $DomXml->createElement("IdentificacaoRps"); $InfRps->appendChild($IdentificacaoRps);
			/* Criar a tag <Numero>	*/
			// SOMENTE PARA CURITIBA O NUMERO DA NOTA SERA IDENTIFICADO PELO NUMERO DO RPS DA TABELA LOTE E NAO PELO
			// NUMERO PROVENIENTE DO SISTEMA
			$Numero = $DomXml->createElement("Numero", (string) $xml_tve_cobol->nf->controle); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $xml_tve_cobol->nf->serie); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			//$Tipo = $DomXml->createElement("Tipo", $xml_tve_cobol->nf->tipo); $IdentificacaoRps->appendChild($Tipo);
			$Tipo = $DomXml->createElement("Tipo", "1"); $IdentificacaoRps->appendChild($Tipo);
			/* Criar a tag <DataEmissao>	*/
			$DataEmissao = $DomXml->createElement("DataEmissao", substr(date("c"),0,19)); $InfRps->appendChild($DataEmissao);
			/* Criar a tag <NaturezaOperacao>	*/
			$NaturezaOperacao = $DomXml->createElement("NaturezaOperacao", $situacao_tributaria); $InfRps->appendChild($NaturezaOperacao);
			/* Criar a tag <RegimeEspecialTributacao>	*/
			//$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", $xml_tve_cobol->nf->regime_especial);
			$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", "2");
			$InfRps->appendChild($RegimeEspecialTributacao);
			/* Criar a tag <OptanteSimplesNacional>	*/
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $xml_tve_cobol->prestador->optantesimples);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivadorCultural>	*/
			$IncentivadorCultural = $DomXml->createElement("IncentivadorCultural", $xml_tve_cobol->prestador->incentivadorcultural);
			$InfRps->appendChild($IncentivadorCultural);
			/* Criar a tag <Status>	*/
			if($pStatus == "C"){
			  $Status = $DomXml->createElement("Status", "2");
			}else{
			  $Status = $DomXml->createElement("Status", "1");
			}
			$InfRps->appendChild($Status);
			/* CRIADO A TAG DOS ITENS DO SERVI�O */
			/* Criar a tag <Servico>	*/
			$Servico = $DomXml->createElement("Servico"); $InfRps->appendChild($Servico);
			/* Criar a tag <Valores>	*/
			$Valores = $DomXml->createElement("Valores"); $Servico->appendChild($Valores);
			/* Criar a tag <ValorServicos>	*/
			$servicosValor = str_replace(",",".",$xml_tve_cobol->nf->valor_total);
			$ValorServicos = $DomXml->createElement("ValorServicos", $servicosValor); $Valores->appendChild($ValorServicos);
			/* Criar a tag <ValorDeducoes>	*/
			if($xml_tve_cobol->nf->valor_deducoes != ""){
				$ValorDeducoes = $DomXml->createElement("ValorDeducoes", $xml_tve_cobol->nf->valor_deducoes); $Valores->appendChild($ValorDeducoes);
			}
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
			/* Criar a tag <ValorCsll>	Este ja esta somado a cima pois vem de cada item */
			//$ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
			$ValorCsll = $DomXml->createElement("ValorCsll", str_replace(",",".",$xml_tve_cobol->nf->valor_contribuicao_social)); // alterado 06-05-14 a pedido do dib para tratar o csll pela contribuicao social
			$Valores->appendChild($ValorCsll);
			/* Criar a tag <IssRetido> e <ValorIss> Este ja esta somado a cima pois vem de cada item */
			if($valorIssRf != 0)
			{
				$IssRetido = $DomXml->createElement("IssRetido", "1");
			}
			else
			{
				$IssRetido = $DomXml->createElement("IssRetido", "2");
			}
			$Valores->appendChild($IssRetido);
			/* Criar a tag <ValorIss>	*/
			$ValorIss = $DomXml->createElement("ValorIss", $valorIss);
			$Valores->appendChild($ValorIss);
			/* Criar a tag <ValorIssRf>	*/
			$ValorIssRf = $DomXml->createElement("ValorIssRetido", $valorIssRf);
			$Valores->appendChild($ValorIssRf);
			/* Criar a tag <BaseCalculo>	*/
			$BaseCalculo = $DomXml->createElement("BaseCalculo", $valorTributavel); $Valores->appendChild($BaseCalculo);
			/* Criar a tag <Aliquota>	*/
			$Aliquota = $DomXml->createElement("Aliquota", ((float) $aliquotaItemServico/100)); $Valores->appendChild($Aliquota); // Divide-se por 100% pois no sistema � transmitido em Percentual e no XML deve ser transmitido em decimal.
			/* Criar a tag <ValorLiquidoNfse>	*/
			$ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",str_replace(",",".",$xml_tve_cobol->nf->valor_total));$Valores->appendChild($ValorLiquidoNfse);
			$ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
			/* Criar a tag <CodigoCnae>	*/
			$CodigoCnae = $DomXml->createElement("CodigoCnae", $xml_tve_cobol->prestador->cnae); $Servico->appendChild($CodigoCnae);
			/* Criar a tag <Discriminacao>	*/
			$Discriminacao = $DomXml->createElement("Discriminacao", $descritivo.' \r\n '.$xml_tve_cobol->nf->observacao); $Servico->appendChild($Discriminacao);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($xml_tve_cobol->prestador->cidade)); $Servico->appendChild($CodigoMunicipio);
			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $xml_tve_cobol->prestador->cpfcnpj); $Prestador->appendChild($Cnpj);
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $xml_tve_cobol->prestador->inscricaomunicipal); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			//if(trim($xml_tve_cobol->tomador_inscricao_municipal'],0) != ""){
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			//}
			if($xml_tve_cobol->tomador->tipo == "F"){
	            /* Criar a tag <IdentificacaoTomador>	*/
	            $IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
	            /* Criar a tag <CpfCnpj>	*/
	            $CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);
			  	/* Criar a tag <Cpf>	*/
			  	//$Cpf = $DomXml->createElement("Cpf",$xml_tve_cobol->tomador_cpf_cnpj']);
			  	$Cpf = $DomXml->createElement("Cpf",substr($xml_tve_cobol->tomador->cpfcnpj,3));
                $CpfCnpj->appendChild($Cpf);
			}
			else if($xml_tve_cobol->tomador->tipo == "J")
			{
                /* Criar a tag <IdentificacaoTomador>	*/
                $IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
                /* Criar a tag <CpfCnpj>	*/
                $CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);
			  	/* Criar a tag <Cpf>	*/
			  	$Cpf = $DomXml->createElement("Cnpj", $xml_tve_cobol->tomador->cpfcnpj);
                $CpfCnpj->appendChild($Cpf);
			}
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
			if(trim($xml_tve_cobol->tomador->complemento) != "")
			{
			  	$Complemento = $DomXml->createElement("Complemento", trim($xml_tve_cobol->tomador->complemento));
			}
			else
			{
			  	$Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $xml_tve_cobol->tomador->bairro); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($xml_tve_cobol->tomador->cidade)); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
            if($xml_tve_cobol->tomador->estado != "EX")
            {
                $Uf = $DomXml->createElement("Uf", $xml_tve_cobol->tomador->estado); $Endereco->appendChild($Uf);
            }
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $xml_tve_cobol->tomador->cep); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($xml_tve_cobol->tomador->fone_residencial,0) != "")
			{
			  	$Telefone = $DomXml->createElement("Telefone", ltrim($xml_tve_cobol->tomador->ddd_fone_residencial,0).ltrim($xml_tve_cobol->tomador->fone_residencial,0));
			  	$Contato->appendChild($Telefone);
			}
			/* Criar a tag <Email>	*/
			$Email = $DomXml->createElement("Email", $xml_tve_cobol->tomador->email); $Contato->appendChild($Email);
			$this->xmlTmp = $DomXml->saveXML();
			$nomeArquivo = time();
			
			file_put_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml", $this->xmlTmp);
			$this->xml = file_get_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			$DomXml->load("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			
			return $this->xml;
		}		

		

		// Guilherme: alterado parametro pAmbiente para passar o tipo de ambiente a ser enviado (14/4/16)
		public function cancelarRPS($pCnpj, $pNumeroControle, $pDiretorio="", $pChamada="", $pDadosTXT="", $pAmbiente=""){
			$this->prestadorCNPJ = $pCnpj;
   
			// Guilherme: alterado para controlar o tipo de ambiente que ir  cancelar a nota (14/4/216)
			$this->ambiente = $pAmbiente;

			$this->numeroControle = $pNumeroControle;
			
			$CXml = new CXml($this->grupo);

			$CNotaFiscal = new CNotaFiscal($this->grupo);

			$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument($this->grupo);
			
			/* Obter XML para comunicar com webservice */
			if($pChamada == "COBOL")
			{
				$this->keyPassCertificado = $this->getkeyPassCertificadoDigital($this->prestadorCNPJ);

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
				$dados_txt_senhaPrefeitura = str_replace("\n", "", trim($pDadosTXT[11]));

				$xmlCancelamento = '';
				$xmlCancelamento .= '<CancelarLoteRpsEnvio xmlns="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd">';
				$xmlCancelamento .= '<LoteRps>';
				$xmlCancelamento .= '<Protocolo>'.$dados_txt_protocolo.'</Protocolo>';
				$xmlCancelamento .= '<Cnpj>'.$dados_txt_cnpj.'</Cnpj>';
				$xmlCancelamento .= '<InscricaoMunicipal>'.$dados_txt_insc_municip.'</InscricaoMunicipal>';
				$xmlCancelamento .= '</LoteRps>';
				$xmlCancelamento .= '</CancelarLoteRpsEnvio>';

				$this->numeroNota = trim($pDadosTXT[3]);
				$this->serieNota = "0";


				//echo "\n xmlCancelamento:".$xmlCancelamento."\n";
			}
			else
			{
				$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->prestadorCNPJ, $this->numeroControle);
			
				if(!$tabelaNf){
					$this->mensagemErro = $CNotaFiscal->mensagemErro;
					return false;
				}

				// Obter configuracoes do config.ini
				$this->obterConfiguracoesWS($this->prestadorCNPJ);

				/*
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
				*/
				$xmlCancelamento = '';
				$xmlCancelamento .= '<CancelarLoteRpsEnvio xmlns="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd">';
				$xmlCancelamento .= '<LoteRps>';
				$xmlCancelamento .= '<Protocolo>'.$tabelaNf->fields['nf_protocolo'].'</Protocolo>';
				$xmlCancelamento .= '<Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
				$xmlCancelamento .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
				$xmlCancelamento .= '</LoteRps>';
				$xmlCancelamento .= '</CancelarLoteRpsEnvio>';

				$this->prestadorCNPJ = $tabelaNf->fields['prestador_cpf_cnpj'];
				$this->numeroNota = $tabelaNf->fields['nf_numero'];
				$this->serieNota = $tabelaNf->fields['nf_serie'];
			}

			
		
			if(!$this->executarWS('CancelarLoteRps', $xmlCancelamento, "S", "LoteRps", $pChamadaexecutarWS)){
			  return false;
			}

			if(!$this->gravarRetorno("NF Cancelada com Sucesso!", "N", "S")){
			  return false;
			}
			return true;
		}
		

		private function wsRespostaCuritiba($pChamada=""){
			$DDoc = new DOMDocument();

			if($pChamada == "COBOL")
			{
				/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
				$this->prestadorCNPJ = $this->retornoWS['prestador_cpf_cnpj'];
				$pXmlConsulta = '';
				$pXmlConsulta .= '<ConsultarLoteRpsEnvio>';
				$pXmlConsulta .= '<Prestador>';
				$pXmlConsulta .= '<Cnpj>'.$this->retornoWS['prestador_cpf_cnpj'].'</Cnpj>';
				$pXmlConsulta .= '<InscricaoMunicipal>'.$this->retornoWS['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
				$pXmlConsulta .= '</Prestador>';
				$pXmlConsulta .= '<Protocolo>'.$this->retornoWS['protocolo'].'</Protocolo>';
				$pXmlConsulta .= '</ConsultarLoteRpsEnvio>';
				$qtdeConsultas = 0;
			}
			else
			{
				$CNotaFiscal = new CNotaFiscal($this->grupo);
				$tabelaNf = $CNotaFiscal->obterNotaFiscal($this->prestadorCNPJ, $this->numeroControle);

				/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
				$this->prestadorCNPJ = $tabelaNf->fields['prestador_cpf_cnpj'];
				$pXmlConsulta = '';
				$pXmlConsulta .= '<ConsultarLoteRpsEnvio>';
				$pXmlConsulta .= '<Prestador>';
				$pXmlConsulta .= '<Cnpj>'.$tabelaNf->fields['prestador_cpf_cnpj'].'</Cnpj>';
				$pXmlConsulta .= '<InscricaoMunicipal>'.$tabelaNf->fields['prestador_inscricao_municipal'].'</InscricaoMunicipal>';
				$pXmlConsulta .= '</Prestador>';
				$pXmlConsulta .= '<Protocolo>'.$this->retornoWS['protocolo'].'</Protocolo>';
				$pXmlConsulta .= '</ConsultarLoteRpsEnvio>';
				$qtdeConsultas = 0;
			}

			//$this->rps .= $this->retornoWS['protocolo'];


			do{
				sleep(5);
				$qtdeConsultas++;

				echo $pXmlConsulta;

				if(!$this->executarWS('ConsultarLoteRps', $pXmlConsulta, "N", "")){
					return false;
				}

				

	
				$DDoc->loadXML($this->xmlRetornoWS);
				$this->retornoWS['codVerificacao'] = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
				$this->codigoVerificacao = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
				$this->retornoWS['numeroNF'] = $DDoc->getElementsByTagName("Numero")->item(0)->nodeValue;
				$this->retornoWS['serieNF'] = $DDoc->getElementsByTagName("Serie")->item(0)->nodeValue;

				echo "\n xmlRetornoWS: ".$this->xmlRetornoWS."\n";
				echo "\n codVerificacao: ".$this->retornoWS['codVerificacao']."\n";
				echo "\n numeroNF: ".$this->retornoWS['numeroNF']."\n";
				echo "\n serieNF: ".$this->retornoWS['serieNF']."\n";
				echo "\n codigoVerificacao: ".$this->codigoVerificacao."\n";


			}while((trim($this->retornoWS['codVerificacao']) == "" || trim($this->retornoWS['numeroNF']) == "")
						&& ($qtdeConsultas <= 120));
				
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
		
		private function obterConfiguracoesWS($cnpj){
		  $CEmpresa = new CEmpresa($this->grupo);
		  $CEmpresa->cnpj = $this->prestadorCNPJ;
		  $retorno = $CEmpresa->obter();

		  if(!$retorno || $retorno == null){
            $this->mensagemErro = $CEmpresa->mensagemErro;
            return false;
		  }else{
		    $this->ConfigWs = $retorno;
            return true;
		  }
		}

		/**
	     * ABAIXO FUNÃ‡Ã•ES CRIADAS CRIADAS ESPECIFICAMENTE PARA FUNCIONAR COM O SISTEMA NFE SOFTDIB
	     */

		/**
	     * __getkeyPassCertificadoDigital
	     * Obter senha do certificado digital de um cnpj especÃ­fico
	     *  
	     * @name __setConfigurations ($AN)
	     * @param   Ambiente Nacional
	     * @autor Eduardo Nunes Lino
	     * @return  none
	     */
	    public function getkeyPassCertificadoDigital($pCnpj){

			// Abrir o config para obter a senha do certificado
			$arquivoIni = parse_ini_file("/var/www/html/nf/nfe/config/config.ini");
			if(!$arquivoIni){

				echo "Erro ao abrir o arquivo config.ini";
			}

	        return $arquivoIni[trim($pCnpj)];
	    }

		private function executarWS($pMetodo, $pDados, $assinatura="N", $tagAssinada="", $noAssinar="", $tagAssinada2="", $pChamada=""){
			/* Montar Xml do Soap que sera enviado para o Web Service via cUrl */
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$xmlSoap = '';
			$xmlSoap .= '<?xml version="1.0" encoding="utf-8"?>';
			$xmlSoap .= '<soap12:Envelope ';
			$xmlSoap .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
			$xmlSoap .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
			$xmlSoap .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
			$xmlSoap .= '<soap12:Body>';
			$xmlSoap .= '<'.$pMetodo.' xmlns="http://www.e-governeapps2.com.br/">';

		  	// Adicionar o Xml obtido para a funcao
			// $CAssinaturaDigital->xml = $pDados;
			$CAssinaturaDigital->xml = $pDados;//adicionado 25/07
			$CAssinaturaDigital->arquivoPFX = "/var/www/html/nf/nfse/certificados/".$this->prestadorCNPJ.".pfx";


			if($pChamada == "COBOL")
			{
				$CAssinaturaDigital->senhaPFX = $this->keyPassCertificado;
			}
			else
			{
				$CAssinaturaDigital->senhaPFX = $this->ConfigWs[0]['senha_pfx'];
			}

		  	if($assinatura == "S")
		  	{
				if(!$CAssinaturaDigital->assinarXml($tagAssinada, $noAssinar, $this->prestadorCNPJ))
				{
					$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
					echo "here";
					return false;
				}
				$xmlSoap .= $CAssinaturaDigital->xml;
		  	}
		  	else
		  	{
				$CAssinaturaDigital->cnpj = $this->prestadorCNPJ;
				if(!$CAssinaturaDigital->loadCerts())
				{
					$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
					return false;
				}
				$xmlSoap .= $pDados;
			}
		  
		  $xmlSoap .= '</'.$pMetodo.'>';
		  $xmlSoap .= '</soap12:Body>';
		  $xmlSoap .= '</soap12:Envelope>';

		  file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  executarWS(".$pMetodo.") {XML Enviado a enviar para prefeitura: \n ".$xmlSoap." } \n\n", FILE_APPEND);
		  $tamanho = strlen($xmlSoap);

		 
		 echo "\n\n envio:".$xmlSoap."\n\n";
		  
		  // Obeter informacoes do config.ini
		  $this->obterConfiguracoesWS($this->prestadorCNPJ);

		  /* Setar cabecalhos da comunicacao Web Service */
		 // $parametrosSoap = Array('Host: 200.189.192.82', 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
		  $parametrosSoap = Array("Host: 200.140.228.224", 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
		  /* Iniciar comunicacao cUrl */
		  $oCurl = curl_init();
		  
		  curl_setopt($oCurl, CURLOPT_FRESH_CONNECT, TRUE);
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
// Guilherme: alterado para controlar o ambiente por parametro e nao pelo wsConfig (14/4/16)	


		  //echo "\nAmbiente:".$this->ambiente.":Ambiente\n";

		  if($this->ambiente == "0"){
		    curl_setopt($oCurl, CURLOPT_URL, "https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?wsdl");
		  }else{
			curl_setopt($oCurl, CURLOPT_URL, "https://pilotoisscuritiba.curitiba.pr.gov.br/nfse_ws/nfsews.asmx?wsdl");
		  }
		  curl_setopt($oCurl, CURLOPT_PORT , 443); // porta normal HTTP
		  curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
		  curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeÃ§alho de resposta
		  //curl_setopt($oCurl, CURLOPT_SSLVERSION, 3); -- Removido pelo possÃ­vel ataque POODLE para V3 - atualizar para TLS1.0 ou superior
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
		  //curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 500);
		  //curl_setopt($oCurl, CURLOPT_TIMEOUT, 0);
		  //curl_setopt($oCurl, CURLOPT_MAXREDIRS, 15);
		  curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, TRUE);
		  
		  /* Para conexoes seguras eh necessario certificado digital*/
		  curl_setopt($oCurl, CURLOPT_SSLCERT, $CAssinaturaDigital->getPubKey());
		  curl_setopt($oCurl, CURLOPT_SSLKEY, $CAssinaturaDigital->getPriKey());

		  curl_setopt($oCurl, CURLOPT_POST, 1);
		  curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
		  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
		  curl_setopt($oCurl, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		  
		  /* Executar chamada o servidor */

		  $__xml = curl_exec($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  executarWS(".$pMetodo.") { Retorno servidor:\n".
		  					$__xml." }\n\n", FILE_APPEND);

		  $info = curl_getinfo($oCurl); //informaÃ§Ãµes da conexÃ£o
		  
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
		  $xmlRetorno = substr($__xml, $x, $n-$x);

		  file_put_contents("retorno.txt",$xmlRetorno);

		   echo "\n\n retorno:".$xmlRetorno."\n\n";

		  /* Encerrar Conexao cUrl */
		  curl_close($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  executarWS(".$pMetodo.") { XML retorno:\n".$xmlRetorno." }\n\n", FILE_APPEND);
		  $this->xmlRetornoWS = $xmlRetorno;

		  $DDoc = new DOMDocument();	  
		  $DDoc->loadXML($this->xmlRetornoWS);
		  $mensagem = $DDoc->getElementsByTagName("Mensagem");
		  $this->retornoWS['mensagem'] = $mensagem->item(0)->nodeValue;

		  /* Verificar retorno da conexao com servidor */
		  if($info['http_code'] != "200" || $__xml === false){
			$mensagemErro = $DDoc->getElementsByTagName("Text");
			file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  executarWS(".$pMetodo.") { Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue." }\n\n", FILE_APPEND);
			$this->mensagemErro = "Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue."\n";
			return false;
		  }

		  if($this->retornoWS['mensagem'] != ""){
			file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  executarWS(".$pMetodo.") { Mensagem: ".$this->retornoWS['mensagem']." }\n\n", FILE_APPEND);
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
			file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  gravarRetorno() { Falha }\n\n", FILE_APPEND);
		  }else{
			$this->status = "S"; // ocorreu certo
			$arrayAtualizacao['nf']['status'] = "S";
			file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  gravarRetorno() { Sucesso }\n\n", FILE_APPEND);
		  }
		  
		  if($pCancelamento == "S"){
			$arrayAtualizacao['nf']['status'] = "C";
			$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$mensagem);
			file_put_contents("/var/tmp/nfse.log","CCuritiba.php\n  gravarRetorno() { CANCELADO }\n\n", FILE_APPEND);
		  }else{
			$this->numeroNota = $this->retornoWS['numeroNF'];
			$this->serieNota = $this->retornoWS['serieNF'];

			$this->nroRps = $this->retornoWS['numeroLote'];
		  }
		  
		  $criticas['codEmpresa'] = $this->codEmpresa;
		  $criticas['codFilial'] = $this->codFilial;
		  $criticas['cnpj'] = $this->prestadorCNPJ;
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
		  $arrayAtualizacao['prestador']['cpfcnpj'] = $this->prestadorCNPJ;
		  $arrayAtualizacao['nf']['controle'] = $this->numeroControle;
		  $arrayAtualizacao['nf']['numero'] = $this->retornoWS['numeroNF'];
		  $arrayAtualizacao['nf']['serie'] = $this->retornoWS['serieNF'];
		  /* campos jÃ¡ adicionados direto na model
		  $arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao Ã© obtido do retorno do WS pois pode conter divergencias com nosso servidor local
		  $arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;*/
		  $arrayAtualizacao['nf']['autenticacao'] = $this->retornoWS['codVerificacao'];
		  $arrayAtualizacao['nf']['protocolo'] = $this->retornoWS['protocolo'];


		  $this->protocolo = $this->retornoWS['protocolo'];

		  if($pCancelamento != "S"){
			$arrayAtualizacao['nf']['link'] = 'https://isscuritiba.curitiba.pr.gov.br/portalNfse/Default.aspx?doc='.$this->prestadorCNPJ.'&num='.$this->retornoWS['numeroNF'].'&cod='.$this->retornoWS['codVerificacao'];
		  }

		  $xmlAtualizar = $xmlgen->generate('nfse',$arrayAtualizacao);
		  $xmlAtualizar = simplexml_load_string($xmlAtualizar);
		  if(!$CNotaFiscal->atualizarNF($xmlAtualizar)){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		  }

		  if($pCancelamento != "S" && $this->status == "S"){
			if(!$CEmail->enviarNF($this->prestadorCNPJ, $this->numeroControle)){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			  return false;
			}
		  }
		  return true;
		}
	}
?>
