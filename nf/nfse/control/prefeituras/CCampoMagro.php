  <?php
	/* 
		Classe:					CCampoMagro.php
		Autor:					Guilherme Silva
		Data:					03/12/2016
		Finalidade:  			Classe responsavel pela comunicacao com o WebService de Campo Magro
		Programas chamadores:
		Programas chamados:
	*/
	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/control/CEmail.php");
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");

	class CCampoMagro{
		// Atruibutos publicos utilizada por todos 
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
		public $ambiente;
		
		/// Atributos privados utilizados apenas pela classe internamente 
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

		
		// Metodos publicos chamados por programas externos
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

			$CXml->xml = $this->xmlEnvioCampoMagro($xml_tve_cobol, "");

             $this->keyPassCertificado = $this->getkeyPassCertificadoDigital($this->prestadorCNPJ);

			$this->executarWS('RecepcionarLoteRpsSincrono', $CXml->xml, "S", "InfDeclaracaoPrestacaoServico", "Rps", "LoteRps", "COBOL");

			if($this->mensagemErro != ""){
			    $this->status = "N";
			    return false;
			}
			
			$DDoc = new DOMDocument();
			$DDoc->loadXML($this->xmlRetornoWS);
			$numeroLote = $DDoc->getElementsByTagName("NumeroLote");
			$dataRecebimento = $DDoc->getElementsByTagName("DataRecebimento");
			$protocolo = $DDoc->getElementsByTagName("Protocolo");
			$codigoVerificacao = $DDoc->getElementsByTagName("CodigoVerificacao");
			$numeroNota = $DDoc->getElementsByTagName("Numero");
			$linkNota = $DDoc->getElementsByTagName("OutrasInformacoes");
			
			$this->nroRps = $numeroLote->item(0)->nodeValue;
			$this->numeroNota = $numeroNota->item(0)->nodeValue;
  			$this->codigoVerificacao = $codigoVerificacao->item(0)->nodeValue;
			$this->retornoWS['dataRecebimento'] = $dataRecebimento->item(0)->nodeValue;
			$this->protocolo = $protocolo->item(0)->nodeValue;
			$this->retornoWS['prestador_inscricao_municipal'] = $xml_tve_cobol->prestador->inscricaomunicipal;
			$this->retornoWS['prestador_cpf_cnpj'] = $xml_tve_cobol->prestador->cpfcnpj;
			$this->criticas = explode("link=",$linkNota->item(0)->nodeValue);
			$this->criticas = $this->criticas[1];
			

			$this->status = "S";
			return true;
		}

		public function xmlEnvioCampoMagro($xml_tve_cobol, $pStatus="")
		{		
			// Percorrer todos os itens vinculados a nota fiscal 
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = "";
			$valorIncondicionado = "";
			$valorIssRf = "";
			$valorTributavel = "";
			$descritivo = "";

			$iten = 0;

			do{
				echo $situacao_tributaria." = ".$xml_tve_cobol->itens->lista[$iten]->situacao_tributaria."\n";
				if($situacao_tributaria != "" && trim($situacao_tributaria) != trim($xml_tve_cobol->itens->lista[$iten]->situacao_tributaria)){
					$this->mensagemErro = "CXml->CCampoMagro{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
					echo $this->mensagemErro;
					file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (CampoMagro) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
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

			$xml = "<EnviarLoteRpsSincronoEnvio xmlns=\"http://www.betha.com.br/e-nota-contribuinte-ws\">".
						"<LoteRps  Id=\"lote".ltrim($xml_tve_cobol->nf->controle,0)."\" versao=\"2.02\">".
							"<NumeroLote>".(string) $xml_tve_cobol->nf->controle."</NumeroLote>".
							"<CpfCnpj>".
							"	<Cnpj>".$xml_tve_cobol->prestador->cpfcnpj."</Cnpj>".
							"</CpfCnpj>".
							"<InscricaoMunicipal>".$xml_tve_cobol->prestador->inscricaomunicipal."</InscricaoMunicipal>".
							"<QuantidadeRps>1</QuantidadeRps>".
							"<ListaRps>".
								"<Rps>".
									"<InfDeclaracaoPrestacaoServico  Id=\"rps\">".
										"<Rps>".
											"<IdentificacaoRps>".
												"<Numero>".(string) $xml_tve_cobol->nf->numero."</Numero>".
												"<Serie>".$xml_tve_cobol->nf->serie."</Serie>".
												"<Tipo>1</Tipo>".
											"</IdentificacaoRps>".
											"<DataEmissao>".date("Y-m-d")."</DataEmissao>".
											"<Status>".(($pStatus == "xC")? "2" : "1")."</Status>".
										"</Rps>".
										"<Competencia>".date("Y-m-d")."</Competencia>".
										"<Servico>".
											"<Valores>".
												"<ValorServicos>".str_replace(",",".",$xml_tve_cobol->nf->valor_total)."</ValorServicos>".
										//		"<ValorDeducoes>".str_replace(",",".",$xml_tve_cobol->nf->valor_deducoes)."</ValorDeducoes>".
												"<ValorPis>".str_replace(",",".",$xml_tve_cobol->nf->valor_pis)."</ValorPis>".
												"<ValorCofins>".str_replace(",",".",$xml_tve_cobol->nf->valor_cofins)."</ValorCofins>".
												"<ValorInss>".str_replace(",",".",$xml_tve_cobol->nf->valor_inss)."</ValorInss>".
												"<ValorIr>".str_replace(",",".",$xml_tve_cobol->nf->valor_ir)."</ValorIr>".
												"<ValorCsll>".str_replace(",",".",$xml_tve_cobol->nf->valor_contribuicao_social)."</ValorCsll>".
												"<OutrasRetencoes>".$valorIssRf."</OutrasRetencoes>".
										//		"<ValorIss>".$valorIss."</ValorIss>".
										//		"<Aliquota>".((float) $aliquotaItemServico/100)."</Aliquota>".
												"<DescontoIncondicionado>".$valorIncondicionado."</DescontoIncondicionado>".
												"<DescontoCondicionado>".$valorCondicionado."</DescontoCondicionado>	".
											"</Valores>".
											"<IssRetido>".(($valorIssRf != 0)? "1" : "2")."</IssRetido>".
											//"<ResponsavelRetencao></ResponsavelRetencao>".
											"<ItemListaServico>".$codigoLista."</ItemListaServico>".
											//"<CodigoCnae>".$xml_tve_cobol->prestador->cnae."</CodigoCnae>".
											"<CodigoTributacaoMunicipio>".$xml_tve_cobol->prestador->cnae."</CodigoTributacaoMunicipio>".
											"<Discriminacao>".$descritivo." ".$xml_tve_cobol->nf->observacao."</Discriminacao>".
											"<CodigoMunicipio>".ltrim($xml_tve_cobol->prestador->cidade)."</CodigoMunicipio>".
											"<ExigibilidadeISS>1</ExigibilidadeISS>".
											"<MunicipioIncidencia>".ltrim($xml_tve_cobol->prestador->cidade)."</MunicipioIncidencia>".
											//"<NumeroProcesso></NumeroProcesso>".
										"</Servico>".
										"<Prestador>".
											"<CpfCnpj>".
												"<Cnpj>".$xml_tve_cobol->prestador->cpfcnpj."</Cnpj>".
											"</CpfCnpj>".
											"<InscricaoMunicipal>".$xml_tve_cobol->prestador->inscricaomunicipal."</InscricaoMunicipal>".
										"</Prestador>".
										"<Tomador>".
											"<IdentificacaoTomador>".
												"<CpfCnpj>".
													"<Cnpj>".$xml_tve_cobol->tomador->cpfcnpj."</Cnpj>".
												"</CpfCnpj>".
												//"<InscricaoMunicipal></InscricaoMunicipal>				".
											"</IdentificacaoTomador>".
											"<RazaoSocial>".$xml_tve_cobol->tomador->nome_razao_social."</RazaoSocial>".
											"<Endereco>".
												"<Endereco>".$xml_tve_cobol->tomador->logradouro."</Endereco>".
												"<Numero>".$xml_tve_cobol->tomador->numero_residencia."</Numero>".
											//	"<Complemento>".$xml_tve_cobol->tomador->complemento."</Complemento>".
												"<Bairro>".$xml_tve_cobol->tomador->bairro."</Bairro>".
												"<CodigoMunicipio>".ltrim($xml_tve_cobol->tomador->cidade)."</CodigoMunicipio>".
												"<Uf>".$xml_tve_cobol->tomador->estado."</Uf>".
											//	"<CodigoPais>1058</CodigoPais>".
												"<Cep>".$xml_tve_cobol->tomador->cep."</Cep>".
											"</Endereco>".
											"<Contato>".
											//	"<Telefone>".$xml_tve_cobol->tomador->fone_residencial."</Telefone>".
												"<Email>".$xml_tve_cobol->tomador->email."</Email>".
											"</Contato>".
										"</Tomador>".
										"<RegimeEspecialTributacao>2</RegimeEspecialTributacao>".
										"<OptanteSimplesNacional>".$xml_tve_cobol->prestador->optantesimples."</OptanteSimplesNacional>".
										"<IncentivoFiscal>".$xml_tve_cobol->prestador->incentivadorcultural."</IncentivoFiscal>".
									"</InfDeclaracaoPrestacaoServico>".
								"</Rps>".
							"</ListaRps>".
						"</LoteRps>".
					"</EnviarLoteRpsSincronoEnvio>";
					
			$this->numeroNota = $xml_tve_cobol->nf->numero;
			$this->serieNota = $xml_tve_cobol->nf->serie;
			
			// Gravar XML para fim de homologação
			file_put_contents("/var/www/html/nf/nfse/enviados/".time()."-envio.xml",$xml);
			return $xml;
		}		

		public function cancelarRPS($pCnpj, $pNumeroControle, $pDiretorio="", $pChamada="", $pDadosTXT="", $pAmbiente=""){
			$this->prestadorCNPJ = $pCnpj;
			$this->ambiente = $pAmbiente;
			$this->numeroControle = $pNumeroControle;
			
			$CXml = new CXml($this->grupo);

			//$CNotaFiscal = new CNotaFiscal($this->grupo);

			//$CEmail = new CEmail($this->grupo);
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			$DDoc = new DOMDocument($this->grupo);
			
			// Obter XML para comunicar com webservice  
			if($pChamada == "COBOL") 
			{
				$this->keyPassCertificado = $this->getkeyPassCertificadoDigital($this->prestadorCNPJ);

				$dados_txt_cnpj = trim($pDadosTXT[0]);
				$dados_txt_uf = trim($pDadosTXT[1]);
				$dados_txt_tipo_emissao = trim($pDadosTXT[2]);
				$this->numeroNota = $dados_txt_chave = trim($pDadosTXT[3]);
				$dados_txt_rps = trim($pDadosTXT[4]);
				$dados_txt_justificativa = trim($pDadosTXT[5]);
				$dados_txt_ambiente = trim($pDadosTXT[6]);
				$dados_txt_ibge = trim($pDadosTXT[7]);
				$dados_txt_insc_municip = trim($pDadosTXT[8]);
				$dados_txt_protocolo = trim($pDadosTXT[9]);
				$dados_txt_usuarioPrefeitura = trim($pDadosTXT[10]);
				$dados_txt_senhaPrefeitura = str_replace("\n", "", trim($pDadosTXT[11]));

				$xmlCancelamento = '<CancelarNfseEnvio xmlns="http://www.betha.com.br/e-nota-contribuinte-ws">'
                                                  .'  <Pedido>'
                                                  .'    <InfPedidoCancelamento Id="1">'
                                                  .'      <IdentificacaoNfse>'
                                                  .'        <Numero>'.$dados_txt_chave.'</Numero>'
                                                  .'        <CpfCnpj><Cnpj>'.$dados_txt_cnpj.'</Cnpj></CpfCnpj>'
                                                  .'        <InscricaoMunicipal>'.$dados_txt_insc_municip.'</InscricaoMunicipal>'
                                                  .'        <CodigoMunicipio>'.$dados_txt_ibge.'</CodigoMunicipio>'
                                                  .'      </IdentificacaoNfse>'
                                                  .'      <CodigoCancelamento>1</CodigoCancelamento>'
                                                  .'    </InfPedidoCancelamento>'
                                                  .'  </Pedido>'
                                                  .'</CancelarNfseEnvio>';
                                                  
			    file_put_contents("/var/www/html/nf/nfse/enviados/".time()."-cancelamento.xml",$xmlCancelamento);





				
				$this->serieNota = "2";
			}

			echo "\n\n xml_envio:$xmlCancelamento\n\n";

			if(!$this->executarWS('CancelarNfse', $xmlCancelamento, "S", "InfPedidoCancelamento", "Pedido" , "", $pChamada)){

				echo "\n\n retorno: false\n\n";

			  $this->status = "N";
			  return false;
			}
			
			echo "\n\n retorno: true\n\n";

			$this->status = "S";
			return true;
		}
		

		private function wsRespostaCampoMagro($pChamada=""){
			$DDoc = new DOMDocument();

			if($pChamada == "COBOL")
			{
				// Montar Xml do Soap que sera enviado para o Web Service via cUrl 
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

				// Montar Xml do Soap que sera enviado para o Web Service via cUrl 
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

				//echo "\nxml que vai\n";
		  			//print_r($pXmlConsulta); 

		  		if($pChamada == "COBOL")
				{
					if(!$this->executarWS('ConsultarLoteRps', $pXmlConsulta, "N", '', "", "", "COBOL")){
						echo "\nerro\n";
			  			print_r($this->xmlRetornoWS);
						return false;
					}
					
				}
				else
				{
					if(!$this->executarWS('ConsultarLoteRps', $pXmlConsulta, "N", "")){
						//echo "\nerro\n";
			  			//print_r($this->xmlRetornoWS);
						return false;
					}
				}

				//echo "\ncerto\n";
		  		//print_r($this->xmlRetornoWS);
	
				$DDoc->loadXML($this->xmlRetornoWS);
				$this->retornoWS['codVerificacao'] = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
				$this->codigoVerificacao = $DDoc->getElementsByTagName("CodigoVerificacao")->item(0)->nodeValue;
				$this->retornoWS['numeroNF'] = $DDoc->getElementsByTagName("Numero")->item(0)->nodeValue;
				$this->retornoWS['serieNF'] = $DDoc->getElementsByTagName("Serie")->item(0)->nodeValue;

				/*
				echo "\n xmlRetornoWS: ".$this->xmlRetornoWS."\n";
				echo "\n codVerificacao: ".$this->retornoWS['codVerificacao']."\n";
				echo "\n numeroNF: ".$this->retornoWS['numeroNF']."\n";
				echo "\n serieNF: ".$this->retornoWS['serieNF']."\n";
				echo "\n codigoVerificacao: ".$this->codigoVerificacao."\n";
				*/

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

              
	     // ABAIXO FUNÃƒâ€¡Ãƒâ€¢ES CRIADAS CRIADAS ESPECIFICAMENTE PARA FUNCIONAR COM O SISTEMA NFE SOFTDIB
	    

		/**
	     * __getkeyPassCertificadoDigital
	     * Obter senha do certificado digital de um cnpj especÃƒÂ­fico
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

			//!$this->executarWS('RecepcionarLoteRps', $CXml->xml, "S", 'LoteRps', "", "", "COBOL")){


			//php -q /var/www/html/nf/nfse_novo/assina_xml.php /user/nfse/04103980000109/CaixaEntrada/TVE436S-20170310-092749.xml /var/www/html/nf/nfse/certificados/04103980000109.pfx 1234 04103980000109 InfPedidoCancelamento

			//php -q /var/www/html/nf/nfse_novo/comunicaPrefeitura.php /user/nfse/04103980000109/CaixaEntrada/TVE436S-20170310-092749.xml /user/bindib/config-nfse-04103980000109.ini CancelarNfse Rps cancelamento

			// 'RecepcionarLoteRpsSincrono', $CXml->xml, "S", "InfDeclaracaoPrestacaoServico", "Rps", "LoteRps", "COBOL"
			// 'CancelarNfse', $xmlCancelamento, "S", "InfPedidoCancelamento", "Pedido" , "", $pChamada

			// Montar Xml do Soap que sera enviado para o Web Service via cUrl 
			$CAssinaturaDigital = new CAssinaturaDigital($this->grupo);
			
			$xmlSoap = '';
			$xmlSoap .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:e="http://www.betha.com.br/e-nota-contribuinte-ws">';
			$xmlSoap .= '<soapenv:Header/>';
			$xmlSoap .= '<soapenv:Body>';
			$xmlSoap .= "<e:$pMetodo>";
			$xmlSoap .= '<nfseCabecMsg><![CDATA[<cabecalho xmlns="http://www.betha.com.br/e-nota-contribuinte-ws" versao="2.02"><versaoDados>2.02</versaoDados></cabecalho>]]></nfseCabecMsg>';
			$xmlSoap .= '<nfseDadosMsg><![CDATA[';

		  	// Adicionar o Xml obtido para a funcao
			$CAssinaturaDigital->xml = $pDados;
			$CAssinaturaDigital->arquivoPFX = "/var/www/html/nf/nfse/certificados/".$this->prestadorCNPJ.".pfx";


			if($pChamada == "COBOL"){
                          $CAssinaturaDigital->senhaPFX = $this->getkeyPassCertificadoDigital($this->prestadorCNPJ);
			}
			else{
                          $CAssinaturaDigital->senhaPFX = $this->ConfigWs[0]['senha_pfx'];
			}

			$xmlAssinado = $pDados;
			
		  	if($assinatura == "S")
		  	{
				if(!$CAssinaturaDigital->assinarXml($tagAssinada, $noAssinar, $this->prestadorCNPJ)){
                                  $this->mensagemErro = $CAssinaturaDigital->mensagemErro;
                                  return false;
				}
				$xmlAssinado = $CAssinaturaDigital->xml;
				if($tagAssinada2 != ""){
				  $CAssinaturaDigital->xml = $xmlAssinado;
                                  if(!$CAssinaturaDigital->assinarXml($tagAssinada2, "", $this->prestadorCNPJ)){
                                    $this->mensagemErro = $CAssinaturaDigital->mensagemErro;
                                    return false;
                                  }
                                  $xmlAssinado = $CAssinaturaDigital->xml;
                                }
		  	}  
		  	else
		  	{
				$CAssinaturaDigital->cnpj = $this->prestadorCNPJ;
				if(!$CAssinaturaDigital->loadCerts())
				{
					$this->mensagemErro = $CAssinaturaDigital->mensagemErro;
					//echo "\n\n return false 0\n";
					//echo $this->mensagemErro;

					return false;
				}
			}
			
			
                        
                        $xmlSoap .= $xmlAssinado;
		  
		  $xmlSoap .= ']]></nfseDadosMsg>';
		  $xmlSoap .= "</e:$pMetodo>";
		  $xmlSoap .= '</soapenv:Body>';
		  $xmlSoap .= '</soapenv:Envelope>';

		  file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  executarWS(".$pMetodo.") {XML Enviado a enviar para prefeitura: \n ".$xmlSoap." } \n\n", FILE_APPEND);

		  file_put_contents("/var/www/html/eduardo/cancelamento_antigo.txt", $xmlSoap);

		  $tamanho = strlen($xmlSoap);

		  // Obeter informacoes do config.ini
//		  $this->obterConfiguracoesWS($this->prestadorCNPJ);

		  // Setar cabecalhos da comunicacao Web Service 
		  //$parametrosSoap = Array("Host: 177.43.56.220", 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
		  $parametrosSoap = Array("Host: e-gov.betha.com.br", 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho");
		  // Iniciar comunicacao cUrl 
		  $oCurl = curl_init();
		  
		  curl_setopt($oCurl, CURLOPT_FRESH_CONNECT, TRUE);
		  // Descomentar abaixo para servidores que tem proxy 
/*		  if($this->ConfigWs[0]['proxy'] == "S"){
			  curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
			  curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
			  curl_setopt($oCurl, CURLOPT_PROXY, $this->ConfigWs[0]['proxy_servidor'].':'.$this->ConfigWs[0]['proxy_porta']);
			  if( $this->ConfigWs[0]['proxy_senha'] != '' ){
				  curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->ConfigWs[0]['proxy_usuario'].':'.$this->ConfigWs[0]['proxy_senha']);
				  curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
			  } //fim if senha proxy
		  }//fim if aProxy
*/
		// VERIFICAR POIS EH PARA OBTER FIXO CONFORME IBGE, URL E PORTA PARA EMISSAO
// Guilherme: alterado para controlar o ambiente por parametro e nao pelo wsConfig (14/4/16)	

		  if($this->ambiente == "1"){ // Ambiente 1=Produção
                    curl_setopt($oCurl, CURLOPT_URL, "http://e-gov.betha.com.br/e-nota-contribuinte-ws/nfseWS?wsdl");
		  }else{
                    curl_setopt($oCurl, CURLOPT_URL, "http://e-gov.betha.com.br/e-nota-contribuinte-test-ws/nfseWS?wsdl");
		  }
//		  curl_setopt($oCurl, CURLOPT_PORT , 443); // porta normal HTTP
		  curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
		  curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeÃƒÂ§alho de resposta
		  //curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);// -- Removido pelo possÃƒÂ­vel ataque POODLE para V3 - atualizar para TLS1.0 ou superior
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
		  curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
		  //curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 500);
		  //curl_setopt($oCurl, CURLOPT_TIMEOUT, 0);
		  //curl_setopt($oCurl, CURLOPT_MAXREDIRS, 15);
		  curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, TRUE);
		  
		  // Para conexoes seguras eh necessario certificado digital
//		  curl_setopt($oCurl, CURLOPT_SSLCERT, $CAssinaturaDigital->getPubKey());
//		  curl_setopt($oCurl, CURLOPT_SSLKEY, $CAssinaturaDigital->getPriKey());

		  curl_setopt($oCurl, CURLOPT_POST, 1);
		  curl_setopt($oCurl, CURLOPT_POSTFIELDS, $xmlSoap);
		  curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
		  curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametrosSoap);
		  curl_setopt($oCurl, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		  
		  // Executar chamada o servidor 

		  $__xml = curl_exec($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  executarWS(".$pMetodo.") { Retorno servidor:\n".
		  					$__xml." }\n\n", FILE_APPEND);

		  $info = curl_getinfo($oCurl); //informaÃƒÂ§ÃƒÂµes da conexÃƒÂ£o
		  
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

		  // Retirar espacoes no inicio do retorno do servidor
		  $n = strlen($__xml);
		  $x = stripos($__xml, "<");
		  $xmlRetorno = htmlspecialchars_decode(substr($__xml, $x, $n-$x));


		  echo "\n\n xml_retorno:$xmlRetorno\n\n";

		  // Encerrar Conexao cUrl 
		  curl_close($oCurl);
		  file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  executarWS(".$pMetodo.") { XML retorno:\n".$xmlRetorno." }\n\n", FILE_APPEND);
		  $this->xmlRetornoWS = $xmlRetorno;
		  if(strpos($this->xmlRetornoWS,"EnviarLoteRpsSincronoResposta") === false){
		  }else{
                  $xml = explode("EnviarLoteRpsSincronoResposta", $this->xmlRetornoWS);
                  $this->xmlRetornoWS = "<EnviarLoteRpsSincronoResposta".$xml[1]."EnviarLoteRpsSincronoResposta>";
		  }

		  $DDoc = new DOMDocument();	  
		  $DDoc->loadXML($this->xmlRetornoWS);
		  $mensagem = @$DDoc->getElementsByTagName("Mensagem");
		  $codigo = @$DDoc->getElementsByTagName("Codigo");
		  $mensagem = @$mensagem->item(0)->nodeValue;
		  $codigo = @$codigo->item(0)->nodeValue;

		  // Verificar retorno da conexao com servidor
		  if($info['http_code'] != "200" || $__xml === false){
			$mensagemErro = $DDoc->getElementsByTagName("Text");
			file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  executarWS(".$pMetodo.") { Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue." }\n\n", FILE_APPEND);
			$this->mensagemErro = "Falha de conexao com servidor[".$info['http_code']."] ".$mensagemErro->item(0)->nodeValue."\n";

			//echo "\n\n return false 1\n";
			return false;
		  }

		  if($mensagem != "" && $mensagem != null)
		  {
			file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  executarWS(".$pMetodo.") { Mensagem: ".$this->retornoWS['mensagem']." }\n\n", FILE_APPEND);
			$this->criticas = $this->mensagemErro = utf8_decode($mensagem);
			return false;	
		  }
		  else
		  {
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
			file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  gravarRetorno() { Falha }\n\n", FILE_APPEND);
		  }else{
			$this->status = "S"; // ocorreu certo
			$arrayAtualizacao['nf']['status'] = "S";
			file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  gravarRetorno() { Sucesso }\n\n", FILE_APPEND);
		  }
		  
		  if($pCancelamento == "S"){
			$arrayAtualizacao['nf']['status'] = "C";
			$criticas['descricao'] = utf8_decode("CANCELAMENTO ".$mensagem);
			file_put_contents("/var/tmp/nfse.log","CCampoMagro.php\n  gravarRetorno() { CANCELADO }\n\n", FILE_APPEND);
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
		  // campos jÃƒÂ¡ adicionados direto na model
		  $arrayAtualizacao['nf']['data_emissao'] = date("d/m/Y");//$result->data_nfse; // nao ÃƒÂ© obtido do retorno do WS pois pode conter divergencias com nosso servidor local
		  $arrayAtualizacao['nf']['hora_emissao'] = date("H:i:s");//$result->hora_nfse;
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
