<?php
	/*
		Classe:					CXml.php
		Autor:					Guilherme Silva
		Data:					01/02/2012
		Finalidade: 			Verificar se o padrao XML esta correto
		Programas chamadores: 	CArquivoComunicacao.php
		Programas chamados: 	
	*/
	require_once("/var/www/html/nf/nfse/control/xmlgen.php");
	require_once("/var/www/html/nf/nfse/model/CBd.php");
	require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");
	require_once("/var/www/html/nf/nfse/model/CItem.php");
	require_once("/var/www/html/nf/nfse/model/CCodigoTom.php");
	require_once("/var/www/html/nf/nfse/model/CLote.php");	
	require_once("/var/www/html/nf/nfse/model/CGenerico.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	
	class CXml{
		//Atributos
		private $grupo;

		public $xml;
		public $mensagemErro;
		public $xmlTmp;
		public $prestadorCnpj;
		//Metodos
		
		/* Metodos publicos chamados por programas externos */

		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}

		//Validar se o XML de entrada esta correto
		public function validarXmlEntrada($pXml=""){
			//Verificar se xml exite no parametro
			if($pXml==""){
				throw new Exception( " CXml -> validarXmlEntrada() {parametro nao e opcional} " );
				return false;
			}
			
			//----------------------------------------
			//Verificar tag <nf>
			if(!$pXml->nf){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][nf] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//Verificar tag <nf><valor_total>
			if(!$pXml->nf->valor_total || trim($pXml->nf->valor_total) == ""){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][nf][valor_total] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//-------------------------------------------------
			//Verificar tag <prestador>
			if(!$pXml->prestador){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][prestador] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//Verificar tag <prestador><cpfcnpj>
			if(!$pXml->prestador->cpfcnpj || trim($pXml->prestador->cpfcnpj) == ""){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][prestador][cpfcnpj] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//-------------------------------------------------			
			//Verificar tag <tomador>
			if(!$pXml->tomador){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][tomador] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//Verificar tag <tomador><tipo>
			if(!$pXml->tomador->tipo || trim($pXml->tomador->tipo) == ""){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][tomador][tipo] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//-------------------------------------------------
			//Verificar tag <itens>
			if(!$pXml->itens){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens] nao foi encontrado ou esta em branco} " );
				return false;
			}
			//Verificar tag <itens><lista>
			if(!$pXml->itens->lista){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista] nao foi encontrado ou esta em branco} " );
				return false;
			}

			//Verificar tag dos itens que podem ser de 1 a N tags
			$passou=0;
			foreach($pXml->itens->lista as $x){
				$passou++;
				//Verificar tag <itens><lista><tributa_municipio_prestador>
				if(!$x->tributa_municipio_prestador|| trim($x->tributa_municipio_prestador) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][tributa_municipio_prestador] nao foi encontrado ou esta em branco} " );
					return false;
				}
				//Verificar tag <itens><lista><codigo_local_prestacao_servico>
				if(!$x->codigo_local_prestacao_servico || trim($x->codigo_local_prestacao_servico) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][codigo_local_prestacao_servico] nao foi encontrado ou esta em branco} " );
					return false;
				}
				//Verificar tag <itens><lista><codigo_item_lista_servico>
				if(!$x->codigo_item_lista_servico || trim($x->codigo_item_lista_servico) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][codigo_item_lista_servico] nao foi encontrado ou esta em branco} " );
					return false;
				}
				//Verificar tag <itens><lista><descritivo>
				if(!$x->descritivo || trim($x->descritivo) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][descritivo] nao foi encontrado ou esta em branco} " );
					return false;
				}
				//Verificar tag <itens><lista><aliquota_item_lista_servico>
				/*if(!$x->aliquota_item_lista_servico || trim($x->aliquota_item_lista_servico) == ""){ 
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][aliquota_item_lista_servico] nao foi encontrado ou esta em branco} " );
					return false;
				}*/
				//Verificar tag <itens><lista><situacao_tributaria>
				if(!$x->situacao_tributaria || trim($x->situacao_tributaria) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][situacao_tributaria] nao foi encontrado ou esta em branco} " );
					return false;
				}
				//Verificar tag <itens><lista><valor_tributavel>
				if(!$x->valor_tributavel || trim($x->valor_tributavel) == ""){
					throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista][".$passou++."][valor_tributavel] nao foi encontrado ou esta em branco} " );
					return false;
				}
			}
			if($passou=0){
				throw new Exception( " CXml -> validarXmlEntrada() {A tag [nfse][itens][lista] deve conter pelo menos um item} " );
				return false;
			}
		}
		//Cadastrar no banco de dados o XML
		public function cadastrarXML($pXml=""){
		  if($pXml==""){
			$this->mensagemErro = " CXml -> cadastrarXml() {parametro inicial nao e opcional} ";
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php -> parametro inicial nao e opcional \n\n", FILE_APPEND);
			return false;
		  }
		  
		  $CNotaFiscal = new CNotaFiscal($this->grupo);
		  $CItem = new CItem($this->grupo);
		  $CGenerico = new CGenerico($this->grupo);
		  
		  //Verifica se Nota ja esta cadastrada
		  if(!$CNotaFiscal->verificarNF($pXml)){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		  }
		  //Cadastrar XML da NF na base de dados;
		  if(!$CNotaFiscal->inserirNF($pXml)){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		  }	

		  //Excluir XML dos Itens na base de dados; NAO SEI PQ TEM Q CANCELAR ANTES
		  /*if(!$CItem->excluirItem($pXml->prestador->cpfcnpj, $pXml->nf->controle)){
			$this->mensagemErro = $CItem->mensagemErro;
		  }*/

		  //Cadastrar XML dos Itens na base de dados;
		  foreach($pXml->itens->lista as $x){
			if(!$CItem->inserirItem($pXml->prestador->cpfcnpj, $pXml->nf->controle, $x)){
				$this->mensagemErro = $CItem->mensagemErro;
			}
		  }
		  //Cadastrar XML dos Genericos na base de dados;
		  if($pXml->genericos->linha){
			foreach($pXml->genericos->linha as $x){
			  if(!$CGenerico->inserirGenerico($pXml->prestador->cpfcnpj, $pXml->nf->controle, $x)){
				  $this->mensagemErro = $CGenerico->mensagemErro;
			  }
			}
		  }
		  return true;
		}
		
		// Metodos para retornar o XML conforme cada municipio e metodos em ordem alfabetica
		public function xmlPinhais($pCnpj, $pNumeroControle, $pStatus=""){
		/*
			$array = array(  
			"first_element"=>"element_value",  
			"second_element"=>array(  
			"second_element_value1"=>"value1_subvalue")  
			);  
			echo $xmlgen->generate('root',$array);
		*/
			//$arrayXml;
		
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pCnpj, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}
			
			$CItem = new CItem($this->grupo);
			$tabelaItem = $CItem->obterItem($pCnpj, $pNumeroControle);
			
			$CGenerico = new CGenerico($this->grupo);
			$tabelaGenerico = $CGenerico->obterGenerico($this->prestadorCnpj, $pNumeroControle="");
			
			$CCodigoTom = new CCodigoTom($this->grupo);
			
			if($pStatus == "C"){
				$arrayXml['nf']['numero'] = $tabelaNf->fields['nf_numero'];
			}
			$arrayXml['nf']['situacao'] = $pStatus;
			$arrayXml['nf']['valor_total'] = str_replace(".",",",$tabelaNf->fields['nf_valor_total']);
			$arrayXml['nf']['valor_desconto'] = str_replace(".",",",$tabelaNf->fields['nf_valor_desconto']);
			$arrayXml['nf']['valor_ir'] = str_replace(".",",",$tabelaNf->fields['nf_valor_ir']);
			$arrayXml['nf']['valor_inss'] = str_replace(".",",",$tabelaNf->fields['nf_valor_inss']);
			$arrayXml['nf']['valor_contribuicao_social'] = str_replace(".",",",$tabelaNf->fields['nf_valor_contribuicao_social']);
			$arrayXml['nf']['valor_rps'] = str_replace(".",",",$tabelaNf->fields['nf_valor_rps']);
			$arrayXml['nf']['valor_pis'] = str_replace(".",",",$tabelaNf->fields['nf_valor_pis']);
			$arrayXml['nf']['valor_cofins'] = str_replace(".",",",$tabelaNf->fields['nf_valor_cofins']);
			$arrayXml['nf']['observacao'] = $tabelaNf->fields['nf_observacao'];
			$arrayXml['prestador']['cpfcnpj'] = $tabelaNf->fields['prestador_cpf_cnpj'];
			$arrayXml['prestador']['cidade'] = "54534";
			$arrayXml['tomador']['tipo'] = $tabelaNf->fields['tomador_tipo'];
			$arrayXml['tomador']['identificador'] = $tabelaNf->fields['tomador_identificador'];
			$arrayXml['tomador']['estado'] = $tabelaNf->fields['tomador_estado'];
			$arrayXml['tomador']['pais'] = $tabelaNf->fields['tomador_pais'];
			$arrayXml['tomador']['cpfcnpj'] = $tabelaNf->fields['tomador_cpf_cnpj'];
			$arrayXml['tomador']['ie'] = $tabelaNf->fields['tomador_ie'];
			$arrayXml['tomador']['nome_razao_social'] = $tabelaNf->fields['tomador_nome_razao_social'];
			$arrayXml['tomador']['sobrenome_nome_fantasia'] = $tabelaNf->fields['tomador_sobrenome_nome_fantasia'];
			$arrayXml['tomador']['logradouro'] = $tabelaNf->fields['tomador_logradouro'];
			$arrayXml['tomador']['email'] = $tabelaNf->fields['tomador_email'];
			$arrayXml['tomador']['numero_residencia'] = $tabelaNf->fields['tomador_numero_residencia'];
			$arrayXml['tomador']['complemento'] = $tabelaNf->fields['tomador_complemento'];
			$arrayXml['tomador']['ponto_referencia'] = $tabelaNf->fields['tomador_ponto_referencia'];
			$arrayXml['tomador']['bairro'] = $tabelaNf->fields['tomador_bairro'];
			/* ESPECIFICAMENTE PARA PINNHAS O C�DIGO DA CIDADE N�O PODE SER O IBGE, TEM QUE SER TOM  DA RECEITA FEDERAL*/
			$codigoTom = substr(ltrim($CCodigoTom->obterCodigoTom($tabelaNf->fields['tomador_cidade']),0),0,-1); // GJPS 07/03/2014

			$arrayXml['tomador']['cidade'] = $codigoTom; // GJPS 04/02/2014
			$arrayXml['tomador']['cep'] = $tabelaNf->fields['tomador_cep'];
			$arrayXml['tomador']['ddd_fone_comercial'] = $tabelaNf->fields['tomador_ddd_fone_comercial'];
			$arrayXml['tomador']['fone_comercial'] = substr($tabelaNf->fields['tomador_fone_comercial'],-9); // GJPS 17-3-14  ticket  22918
			$arrayXml['tomador']['ddd_fone_residencial'] = $tabelaNf->fields['tomador_ddd_fone_residencial'];
			$arrayXml['tomador']['fone_residencial'] = substr($tabelaNf->fields['tomador_fone_residencial'],-9); // GJPS 17-3-14  ticket 22918
			$arrayXml['tomador']['ddd_fax'] = $tabelaNf->fields['tomador_ddd_fax'];
			$arrayXml['tomador']['fone_fax'] = substr($tabelaNf->fields['tomador_fone_fax'],-9); // GJPS 17-3-14  ticket 22918
			$arrayXml['produtos']['descricao'] = $tabelaNf->fields['produtos_descricao'];
			$arrayXml['produtos']['valor'] = $tabelaNf->fields['produtos_valor_total'];
			
			if(is_array($tabelaItem)){
				foreach($tabelaItem as $key=>$value){
					$arrayXml['itens'][$key]['lista']['tributa_municipio_prestador'] = $value['tributa_municipio_prestador'];
					// Converter o Codigo IBGE em Codigo TOM
					$codigoTom = substr($CCodigoTom->obterCodigoTom($value['codigo_local_prestacao_servico']),0,-1);
					$arrayXml['itens'][$key]['lista']['codigo_local_prestacao_servico'] = ltrim($codigoTom,0);
					$arrayXml['itens'][$key]['lista']['unidade_codigo'] = $value['unidade_codigo'];
					$arrayXml['itens'][$key]['lista']['unidade_quantidade'] = $value['unidade_quantidade'];
					$arrayXml['itens'][$key]['lista']['unidade_valor_unitario'] = str_replace(".",",",$value['unidade_valor_unitario']);
					$arrayXml['itens'][$key]['lista']['codigo_item_lista_servico'] = $value['codigo_item_lista_servico'];
					$arrayXml['itens'][$key]['lista']['descritivo'] = $value['descritivo'];
					$arrayXml['itens'][$key]['lista']['aliquota_item_lista_servico'] = str_replace(".",",",$value['aliquota_item_servico']);
					$arrayXml['itens'][$key]['lista']['situacao_tributaria'] = str_pad($value['situacao_tributaria'],1,0,STR_PAD_LEFT);
					$arrayXml['itens'][$key]['lista']['valor_tributavel'] = str_replace(".",",",$value['valor_tributavel']);
					$arrayXml['itens'][$key]['lista']['valor_deducao'] = str_replace(".",",",$value['valor_deducao']);
					$arrayXml['itens'][$key]['lista']['valor_issrf'] = str_replace(".",",",$value['valor_issrf']);
				}
			}

			if(is_array($tabelaGenerico)){
				foreach($tabelaGenerico as $key=>$value){
					$arrayXml['genericos'][$key]['linha']['titulo'] = $value['titulo'];
					$arrayXml['genericos'][$key]['linha']['descricao'] = $value['descricao'];
				}
			}
			
			$xmlgen = new xmlgen();
			$this->xml = $xmlgen->generate('nfse',$arrayXml);
			return true;

		}
		
		// Metodos para retornar o XML conforme cada municipio e metodos em ordem alfabetica
		public function xmlFozDoIguacu($pEmpresa, $pFilial, $pNumeroControle, $pStatus=""){
			$CNotaFiscal = new CNotaFiscal();
			$DomXml = new DOMDocument("1.0","utf-8");
			
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pEmpresa, $pFilial, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}
			
			$CItem = new CItem();
			$tabelaItem = $CItem->obterItem($pEmpresa, $pFilial, $pNumeroControle);

			$CLote = new CLote();
			if(!$CLote->incrementarLote($pEmpresa, $pFilial)){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}else{
				$tabelaLote = $CLote->obterLote($pEmpresa, $pFilial);
			}
			
			$CGenerico = new CGenerico();
			$tabelaGenerico = $CGenerico->obterGenerico($pEmpresa, $pFilial, $pNumeroControle="");

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = "";
			$valorIncondicionado = "";
			$valorIssRf = "";
			$valorTributavel = "";
			foreach($tabelaItem as $key=>$value){
			  if($situacao_tributaria != "" && $situacao_tributaria != $value['situacao_tributaria']){
				$this->mensagemErro = "CXml->CFozDoIguacu{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (CFozDoIguacu) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
				return false;
			  }
			  $situacao_tributaria = $value['situacao_tributaria'];
			  $valorCsll += (float) $value['valor_csll'];
			  $valorIss += (float) $value['valor_iss'];
			  $valorCondicionado += $value['desconto_cond'];
			  $valorIncondicionado += $value['desconto_incond'];
			  $valorIssRf = (float) $value['valor_issrf'];
			  $valorTributavel += (float) $value['valor_tributavel'];
			  $aliquotaItemServico = (float) $value['aliquota_item_servico'];
				  $codigoLista = ltrim($value['codigo_item_lista_servico'],0);
				  $codigoLista = substr($codigoLista,0,2).".".substr($codigoLista,2);
			  $descritivo .= $value['descritivo']."   |   ";
			}
			if(trim(str_replace(",","", $aliquotaItemServico),0) != ""){
					$aliquotaItemServico = ltrim(str_replace(",",".", $aliquotaItemServico),0);
			}
			if(trim(str_replace(",","", $valorTributavel),0) != ""){
				$valorTributavel = ltrim(str_replace(",",".", $valorTributavel),0);
			}
			if(trim(str_replace(",","", $valorIssRf),0) != ""){
				$valorIssRf = ltrim(str_replace(",",".", $valorIssRf),0);
			}else{
				$valorIssRf = "";
			}
			if(trim(str_replace(",","", $valorIss),0) != ""){
				$valorIss = ltrim(str_replace(",",".", $valorIss),0);
			}else{
				$valorIss = "";
			}
			if(trim(str_replace(",","", $valorCsll),0) != ""){
				$valorCsll = ltrim(str_replace(",",".", $valorCsll),0);
			}

			
			/* Criar a tag
				<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd"
									xmlns:xsd="http://www.w3.org/2001/XMLSchema"
									xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> */
			$EnviarLoteRpsEnvio = $DomXml->createElement("EnviarLoteRpsEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsd","http://www.w3.org/2001/XMLSchema");
			$EnviarLoteRpsEnvio->setAttribute("xmlns","http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			/* Criar a tag LoteRps>	*/
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $tabelaLote->fields['lote']); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $LoteRps->appendChild($Cnpj);
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $LoteRps->appendChild($InscricaoMunicipal);
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
			$Numero = $DomXml->createElement("Numero", $tabelaNf->fields['nf_numero']); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $tabelaNf->fields['nf_serie']); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $tabelaNf->fields['nf_tipo']); $IdentificacaoRps->appendChild($Tipo);
			/* Criar a tag <DataEmissao>	*/
			$DataEmissao = $DomXml->createElement("DataEmissao", substr(date("c"),0,19)); $InfRps->appendChild($DataEmissao);
			/* Criar a tag <NaturezaOperacao>	*/
			$NaturezaOperacao = $DomXml->createElement("NaturezaOperacao", $situacao_tributaria); $InfRps->appendChild($NaturezaOperacao);
			/* Criar a tag <RegimeEspecialTributacao>	*/
			$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", $tabelaNf->fields['nf_regime_especial']);
			$InfRps->appendChild($RegimeEspecialTributacao);
			/* Criar a tag <OptanteSimplesNacional>	*/
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $tabelaNf->fields['prestador_optante_simples']);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivadorCultural>	*/
			$IncentivadorCultural = $DomXml->createElement("IncentivadorCultural", $tabelaNf->fields['prestador_incentivador_cultural']);
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
				  $servicosValor = str_replace(",",".",$tabelaNf->fields['nf_valor_total']);
				  $ValorServicos = $DomXml->createElement("ValorServicos", ltrim($servicosValor,0)); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorCofins>	*/
				  $valorSemSeparador = ltrim(str_replace(",","",$tabelaNf->fields['nf_valor_cofins']),0);
				  $valorCofins = ltrim(str_replace(",",".",$tabelaNf->fields['nf_valor_cofins']),0);
				  if($valorSemSeparador != ""){
					$ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  }else{
					$ValorCofins = $DomXml->createElement("ValorCofins", "0");
				  }
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorSemSeparador = trim(str_replace(",","",$tabelaNf->fields['nf_valor_inss']),0);
				  $valorInss = ltrim(str_replace(",",".",$tabelaNf->fields['nf_valor_inss']),0);
				  if($valorSemSeparador != ""){
					$ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  }else{
					$ValorInss = $DomXml->createElement("ValorInss", "0");
				  }
				  $Valores->appendChild($ValorInss);
				  /* Criar a tag <ValorIr>	*/
				  $valorSemSeparador = trim(str_replace(",","",$tabelaNf->fields['nf_valor_ir']),0);
				  $valorIr = ltrim(str_replace(",",".",$tabelaNf->fields['nf_valor_ir']),0);
				  if($valorSemSeparador != ""){
					$ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  }else{
					$ValorIr = $DomXml->createElement("ValorIr", "0");
				  }
				  $Valores->appendChild($ValorIr);
				  /* Criar a tag <ValorCsll>	*/
				  if($valorCsll != ""){
					$ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
				  }else{
					$ValorCsll = $DomXml->createElement("ValorCsll", "0");
				  }
				  $Valores->appendChild($ValorCsll);
				  /* Criar a tag <IssRetido>	*/
				  if($valorIssRf != ""){
					$IssRetido = $DomXml->createElement("IssRetido", "1");
				  }else{
					$IssRetido = $DomXml->createElement("IssRetido", "2");
				  }
				  $Valores->appendChild($IssRetido);
	
				  /* Criar a tag <ValorIss>	*/
				  if($valorIssRf != ""){
					$ValorIss = $DomXml->createElement("ValorIss", $valorIssRf);
					$Valores->appendChild($ValorIss);
				  }
				  if($valorIss != ""){
					$ValorIss = $DomXml->createElement("ValorIss", $valorIss);
					$Valores->appendChild($ValorIss);
				  }
				  /* Criar a tag <BaseCalculo>	*/
				  if($valorTributavel != ""){
					$BaseCalculo = $DomXml->createElement("BaseCalculo", $valorTributavel);
				  }else{
					$BaseCalculo = $DomXml->createElement("BaseCalculo", "0");
				  }
				  $Valores->appendChild($BaseCalculo);
				  /* Criar a tag <Aliquota>	*/
				  if($aliquotaItemServico != ""){
					$Aliquota = $DomXml->createElement("Aliquota", $aliquotaItemServico); $Valores->appendChild($Aliquota);
				  }
				  /* Criar a tag <ValorLiquidoNfse>	*/
				  $valorSemSeparador = trim(str_replace(",","",$tabelaNf->fields['nf_valor_total']),0); 
				  $valorLiquidoNfse = ltrim(str_replace(",",".",$tabelaNf->fields['nf_valor_total']),0);
				  if($valorSemSeparador != ""){
					$ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse", $valorLiquidoNfse); $Valores->appendChild($ValorLiquidoNfse);
				  }
 				  /* Criar a tag <ValorCondicionado> <Valorincondicionado>	*/
				  if($valorIncondicionado != ""){
					$incondicionado = $DomXml->createElement("DescontoIncondicionado", $valorIncondicionado); $Valores->appendChild($incondicionado);
				  }
				  
				  if($valorCondicionado != ""){
					$condicionado = $DomXml->createElement("DescontoCondicionado", $valorCondicionado); $Valores->appendChild($condicionado);
				  }
				  
				  /* Criar a tag <ItemListaServico>	*/
				  $ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
				  /* Criar a tag <CodigoCnae>	*/
				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $tabelaNf->fields['prestador_cnae']); $Servico->appendChild($CodigoCnae);
				  /* Criar a tag <CodigoTributacaoMunicipio>	*/
				  $CodigoTributacaoMunicipio = $DomXml->createElement("CodigoTributacaoMunicipio", $codigoLista); $Servico->appendChild($CodigoTributacaoMunicipio);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo."   |   ".$tabelaNf->fields['nf_observacao']); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['prestador_cidade'])); $Servico->appendChild($CodigoMunicipio);
			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $Prestador->appendChild($Cnpj);
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			/* Criar a tag <IdentificacaoTomador>	*/
			$IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
			/* Criar a tag <CpfCnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);

			if($tabelaNf->fields['tomador_tipo'] == "F"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			}elseif($tabelaNf->fields['tomador_tipo'] == "J"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $tabelaNf->fields['tomador_cpf_cnpj']);
			}else{
			  $Cpf = $DomXml->createElement("Cpf", "99999999999");
			}
			$CpfCnpj->appendChild($Cpf);

			if(trim($tabelaNf->fields['tomador_cidade']) == "4108304"){
			  /* Criar a tag <InscricaoMunicipal>	*/
			  $InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['tomador_inscricao_municipal']);
			  $IdentificacaoTomador->appendChild($InscricaoMunicipal);
			}

			/* Criar a tag <RazaoSocial>	*/
			if($tabelaNf->fields['tomador_nome_razao_social'] == "" && $tabelaNf->fields['tomador_sobrenome_nome_fantasia'] == ""){
			  $RazaoSocial = "Tomador nao informado";
			}else{
			  $RazaoSocial = $DomXml->createElement("RazaoSocial", $tabelaNf->fields['tomador_nome_razao_social'].$tabelaNf->fields['tomador_sobrenome_nome_fantasia']);
			}
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Logradouro = $DomXml->createElement("Endereco", "nao informado");
			if($tabelaNf->fields['tomador_logradouro'] != ""){
			  $Logradouro = $DomXml->createElement("Endereco", $tabelaNf->fields['tomador_logradouro']);
			}
			$Endereco->appendChild($Logradouro);
			/* Criar a tag <Numero>	*/
			if($tabelaNf->fields['tomador_numero_residencia'] == ""){
			  $Numero = "0";
			}else{
			  $Numero = $DomXml->createElement("Numero", $tabelaNf->fields['tomador_numero_residencia']);
			}
			$Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($tabelaNf->fields['tomador_complemento']) == ""){
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}else{
			  $Complemento = $DomXml->createElement("Complemento", trim($tabelaNf->fields['tomador_complemento']));
			}
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			if($tabelaNf->fields['tomador_bairro'] == ""){
			  $Bairro = "nao informado";
			}else{
			  $Bairro = $DomXml->createElement("Bairro", $tabelaNf->fields['tomador_bairro']);
			}
			$Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = "000000000";
			if($tabelaNf->fields['tomador_cidade'] == ""){
			}else{
			  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade']));
			}
			$Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
			if($tabelaNf->fields['tomador_estado'] == ""){
			  $Uf = "--";
			}else{
			  $Uf = $DomXml->createElement("Uf", $tabelaNf->fields['tomador_estado']);
			}
			 $Endereco->appendChild($Uf);
			/* Criar a tag <Cep>	*/
			if($tabelaNf->fields['tomador_cep'] == ""){
			  $Cep = "00000000";
			}else{
			  $Cep = $DomXml->createElement("Cep", $tabelaNf->fields['tomador_cep']);
			}
			$Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($tabelaNf->fields['tomador_fone_residencial'],0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($tabelaNf->fields['tomador_ddd_fone_residencial'],0).ltrim($tabelaNf->fields['tomador_fone_residencial'],0));
			  $Contato->appendChild($Telefone);
			}
			/* Criar a tag <Email>	*/
			$Email = $DomXml->createElement("Email", $tabelaNf->fields['tomador_email']); $Contato->appendChild($Email);
 
			$this->xmlTmp = $DomXml->saveXML();
			$nomeArquivo = time();
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (CFozDoIguacu) -> XML enviado: \n ".$this->xmlTmp." \n\n", FILE_APPEND);
			file_put_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml", $this->xmlTmp);
			$this->xml = file_get_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			$this->xml = substr($this->xml,39); // desprezar as 39 primeiras posicoes que esta a tag de abertura do XML
			return true;
		}
		
		public function xmlCuritiba($pCnpj, $pNumeroControle, $pStatus=""){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$DomXml = new DOMDocument();
			
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pCnpj, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}
			
			$CItem = new CItem($this->grupo);
			$tabelaItem = $CItem->obterItem($pCnpj, $pNumeroControle);

			$CLote = new CLote($this->grupo);
			if(!$CLote->incrementarLote($pCnpj)){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}else{
				$tabelaLote = $CLote->obterLote($pCnpj);
			}
			
			$CGenerico = new CGenerico($this->grupo);
			$tabelaGenerico = $CGenerico->obterGenerico($pCnpj, $pNumeroControle="");

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = "";
			$valorIncondicionado = "";
			$valorIssRf = "";
			$valorTributavel = "";

			foreach($tabelaItem as $key=>$value){
			  if($situacao_tributaria != "" && $situacao_tributaria != $value['situacao_tributaria']){
				$this->mensagemErro = "CXml->CCuritiba{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Curitiba) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
				return false;
			  }
			  
			  $situacao_tributaria = $value['situacao_tributaria'];
			  $valorCsll += str_replace(",",".",$value['valor_csll']);
			  $valorIss += str_replace(",",".",$value['valor_iss']);
			  $valorCondicionado += str_replace(",",".",$value['desconto_cond']);
			  $valorIncondicionado += str_replace(",",".",$value['desconto_incond']);
			  $valorIssRf += str_replace(",",".",$value['valor_issrf']);
			  $valorTributavel += str_replace(",",".",$value['valor_tributavel']);
			  $aliquotaItemServico = $value['aliquota_item_servico'];
				  $codigoLista = ltrim($value['codigo_item_lista_servico'],0);
				  $codigoLista = substr($codigoLista,0,2)."".substr($codigoLista,2);
			  $descritivo .= $value['descritivo'].' \r\n ';
			}
			/* Criar a tag
				<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd"
									xmlns:xsd="http://www.w3.org/2001/XMLSchema"
									xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> */
			$EnviarLoteRpsEnvio = $DomXml->createElement("EnviarLoteRpsEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns","http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xsi:schemaLocation","http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			/* Criar a tag LoteRps>	*/
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $tabelaLote->fields['lote']); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $LoteRps->appendChild($Cnpj);
			$this->prestadorCnpj = $tabelaNf->fields['prestador_cpf_cnpj'];
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $LoteRps->appendChild($InscricaoMunicipal);
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
			$Numero = $DomXml->createElement("Numero", (string) $tabelaLote->fields['rps']); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $tabelaNf->fields['nf_serie']); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $tabelaNf->fields['nf_tipo']); $IdentificacaoRps->appendChild($Tipo);
			/* Criar a tag <DataEmissao>	*/
			$DataEmissao = $DomXml->createElement("DataEmissao", substr(date("c"),0,19)); $InfRps->appendChild($DataEmissao);
			/* Criar a tag <NaturezaOperacao>	*/
			$NaturezaOperacao = $DomXml->createElement("NaturezaOperacao", $situacao_tributaria); $InfRps->appendChild($NaturezaOperacao);
			/* Criar a tag <RegimeEspecialTributacao>	*/
			$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", $tabelaNf->fields['nf_regime_especial']);
			$InfRps->appendChild($RegimeEspecialTributacao);
			/* Criar a tag <OptanteSimplesNacional>	*/
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $tabelaNf->fields['prestador_optante_simples']);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivadorCultural>	*/
			$IncentivadorCultural = $DomXml->createElement("IncentivadorCultural", $tabelaNf->fields['prestador_incentivador_cultural']);
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
				  $servicosValor = str_replace(",",".",$tabelaNf->fields['nf_valor_total']);
				  $ValorServicos = $DomXml->createElement("ValorServicos", $servicosValor); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorDeducoes>	*/
				  if($tabelaNf->fields['nf_valor_deducoes'] != ""){
					$ValorDeducoes = $DomXml->createElement("ValorDeducoes", $tabelaNf->fields['nf_valor_deducoes']); $Valores->appendChild($ValorDeducoes);
				  }
				  /* Criar a tag <ValorPis>	*/
				  $valorPis = str_replace(",",".",$tabelaNf->fields['nf_valor_pis']);
				  $ValorPis = $DomXml->createElement("ValorPis", $valorPis);
				  $Valores->appendChild($ValorPis);
				  /* Criar a tag <ValorCofins>	*/
				  $valorCofins = str_replace(",",".",$tabelaNf->fields['nf_valor_cofins']);
				  $ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorInss = str_replace(",",".",$tabelaNf->fields['nf_valor_inss']);
				  $ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  $Valores->appendChild($ValorInss);
				  /* Criar a tag <ValorIr>	*/
				  $valorIr = str_replace(",",".",$tabelaNf->fields['nf_valor_ir']);
				  $ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  $Valores->appendChild($ValorIr);
				  /* Criar a tag <ValorCsll>	Este ja esta somado a cima pois vem de cada item */
//				  $ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
				  $ValorCsll = $DomXml->createElement("ValorCsll", str_replace(",",".",$tabelaNf->fields['nf_valor_contribuicao_social'])); // alterado 06-05-14 a pedido do dib para tratar o csll pela contribuicao social
				  $Valores->appendChild($ValorCsll);
				  /* Criar a tag <IssRetido> e <ValorIss> Este ja esta somado a cima pois vem de cada item */
				  if($valorIssRf != 0){
					$IssRetido = $DomXml->createElement("IssRetido", "1");
				  }else{
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
				  $ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",str_replace(",",".",$tabelaNf->fields['nf_valor_total']));$Valores->appendChild($ValorLiquidoNfse);
				  /* Criar a tag <DescontoIncondicionado>	
				  $DescontoIncondicionado=$DomXml->createElement("DescontoIncondicionado",$tabelaNf->fields['nf_valor_desconto_incondicional']);
				  $Valores->appendChild($DescontoIncondicionado);
				  /* Criar a tag <DescontoCondicionado>	
				  $DescontoCondicionado = $DomXml->createElement("DescontoCondicionado", $tabelaNf->fields['nf_valor_desconto_condicional']);
				  $Valores->appendChild($DescontoCondicionado);
				  /* Criar a tag <ItemListaServico>	*/
				  $ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
				  /* Criar a tag <CodigoCnae>	*/
				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $tabelaNf->fields['prestador_cnae']); $Servico->appendChild($CodigoCnae);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo.' \r\n '.$tabelaNf->fields['nf_observacao']); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/
				  
				  #t68809 //$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['prestador_cidade'])); $Servico->appendChild($CodigoMunicipio);
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade'])); $Servico->appendChild($CodigoMunicipio);
			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $Prestador->appendChild($Cnpj);
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			//if(trim($tabelaNf->fields['tomador_inscricao_municipal'],0) != ""){
				$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			//}
			if($tabelaNf->fields['tomador_tipo'] == "F"){
                            /* Criar a tag <IdentificacaoTomador>	*/
                            $IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
                            /* Criar a tag <CpfCnpj>	*/
                            $CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);
			  /* Criar a tag <Cpf>	*/
			  //$Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			  $Cpf = $DomXml->createElement("Cpf",substr($tabelaNf->fields['tomador_cpf_cnpj'],3));
                          $CpfCnpj->appendChild($Cpf);
			}elseif($tabelaNf->fields['tomador_tipo'] == "J"){
                            /* Criar a tag <IdentificacaoTomador>	*/
                            $IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
                            /* Criar a tag <CpfCnpj>	*/
                            $CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $tabelaNf->fields['tomador_cpf_cnpj']);
                          $CpfCnpj->appendChild($Cpf);
			}
			
			
			/* Criar a tag <RazaoSocial>	*/
			$RazaoSocial = $DomXml->createElement("RazaoSocial", $tabelaNf->fields['tomador_nome_razao_social'].$tabelaNf->fields['tomador_sobrenome_nome_fantasia']);
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Endereco2 = $DomXml->createElement("Endereco", $tabelaNf->fields['tomador_logradouro']); $Endereco->appendChild($Endereco2);
			/* Criar a tag <Numero>	*/
			$Numero = $DomXml->createElement("Numero", $tabelaNf->fields['tomador_numero_residencia']); $Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($tabelaNf->fields['tomador_complemento']) != ""){
			  $Complemento = $DomXml->createElement("Complemento", trim($tabelaNf->fields['tomador_complemento']));
			}else{
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $tabelaNf->fields['tomador_bairro']); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade'])); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
                        if($tabelaNf->fields['tomador_estado'] != "EX"){
                            $Uf = $DomXml->createElement("Uf", $tabelaNf->fields['tomador_estado']); $Endereco->appendChild($Uf);
						}
			
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $tabelaNf->fields['tomador_cep']); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($tabelaNf->fields['tomador_fone_residencial'],0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($tabelaNf->fields['tomador_ddd_fone_residencial'],0).ltrim($tabelaNf->fields['tomador_fone_residencial'],0));
			  $Contato->appendChild($Telefone);
			}

			/* Criar a tag <Email>	*/
			$Email = $DomXml->createElement("Email", $tabelaNf->fields['tomador_email']); $Contato->appendChild($Email);

			$this->xmlTmp = $DomXml->saveXML();
			
			$nomeArquivo = time();

			
			file_put_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml", $this->xmlTmp);
			$this->xml = file_get_contents("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			$DomXml->load("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			


			/*
			$numero_nota .= $tabelaNf->fields['nf_numero'];

			file_put_contents("/user/nfse/".$pCnpj."/RET//".$numero_nota."-procNFSe.xml", $this->xmlTmp);
			$this->xml = file_get_contents("/user/nfse/".$pCnpj."/RET//".$numero_nota."-procNFSe.xml");
			$DomXml->load("/user/nfse/".$pCnpj."/RET//".$numero_nota."-procNFSe.xml");
			*/

				
/*				if(!$DomXml->schemaValidate("curitiba.xsd")){
					echo " \n\n O ESQUEMA EH INVALIDO \n\n";
				}else{
					echo " \n\n O ESQUEMA ESTA OKKK!! \n\n";
				}*/

//			unlink("/var/www/html/nf/nfse/enviados/".$nomeArquivo.".xml");
			return true;
		}

		public function xmlMaringa($pCnpj, $pNumeroControle, $pStatus=""){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$DomXml = new DOMDocument();

			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pCnpj, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}

			$CItem = new CItem($this->grupo);
			$tabelaItem = $CItem->obterItem($pCnpj, $pNumeroControle);

			$CLote = new CLote($this->grupo);
			if(!$CLote->incrementarLote($pCnpj, false)){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}else{
				$tabelaLote = $CLote->obterLote($pCnpj);
			}
			
			$CGenerico = new CGenerico($this->grupo);
			$tabelaGenerico = $CGenerico->obterGenerico($pCnpj, $pNumeroControle="");

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = 0.00;
			$valorIncondicionado = 0.00;
			$valorIssRf = "";
			$valorTributavel = "";
			$descritivo = "";

			foreach($tabelaItem as $key=>$value){
			  if($situacao_tributaria != "" && $situacao_tributaria != $value['situacao_tributaria']){
				$this->mensagemErro = "CXml->CMaringa{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Maringa) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
				return false;
			  }
			  
			  $situacao_tributaria = $value['situacao_tributaria'];
			  $valorCsll += (float) $value['valor_csll'];
			  $valorIss += (float) $value['valor_iss'];
			  $valorCondicionado += (float) $value['desconto_cond'];
			  $valorIncondicionado += (float) $value['desconto_incond'];
			  $valorIssRf += (float) $value['valor_issrf'];
			  $valorTributavel += (float) $value['valor_tributavel'];
			  $aliquotaItemServico = $value['aliquota_item_servico'];
				  $codigoLista = ltrim($value['codigo_item_lista_servico'],0);
				  $codigoLista = substr($codigoLista,0,2).".".substr($codigoLista,2);
			  $descritivo .= $value['descritivo']."   |   ";
			  $codigoLocalPrestacaoServico = $value['codigo_local_prestacao_servico'];
			  $tributa_municipio_prestador = $value['tributa_municipio_prestador'];
			}
			/* Criar a tag
				<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd"
									xmlns:xsd="http://www.w3.org/2001/XMLSchema"
									xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> */
			$EnviarLoteRpsEnvio = $DomXml->createElement("EnviarLoteRpsSincronoEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns","http://www.abrasf.org.br/nfse.xsd");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:ds","http://www.w3.org/2000/09/xmldsig#");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xsi:schemaLocation","http://www.abrasf.org.br/nfse.xsd nfse_v2.01.xsd");
			//$EnviarLoteRpsEnvio->setAttribute("soapenv:encodingStyle","http://schemas.xmlsoap.org/soap/encoding/");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			/* Criar a tag LoteRps>	*/
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			
			$LoteRps->setAttribute("versao","2.01");
			$LoteRps->setAttribute("Id","L1");
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $tabelaLote->fields['lote']); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $LoteRps->appendChild($CpfCnpj);
				if(strlen($tabelaNf->fields['prestador_cpf_cnpj'])<=11){
					$Cnpj = $DomXml->createElement("Cpf", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}else{
					$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}
			$this->prestadorCnpj = $tabelaNf->fields['prestador_cpf_cnpj'];
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $LoteRps->appendChild($InscricaoMunicipal);
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
			$Numero = $DomXml->createElement("Numero", (string) $tabelaLote->fields['rps']); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $tabelaNf->fields['nf_serie']); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $tabelaNf->fields['nf_tipo']); $IdentificacaoRps->appendChild($Tipo);
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
				  $servicosValor = $tabelaNf->fields['nf_valor_total'];
				  $ValorServicos = $DomXml->createElement("ValorServicos", str_replace(",",".",$servicosValor)); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorDeducoes>	*/
				  $valorDeducoes = "0.00";
				  $ValorDeducoes = $DomXml->createElement("ValorDeducoes", $valorDeducoes);
				  $Valores->appendChild($ValorDeducoes);
				  /* Criar a tag <ValorPis>	*/
				  $valorPis = str_replace(",",".",$tabelaNf->fields['nf_valor_pis']);
				  $ValorPis = $DomXml->createElement("ValorPis", $valorPis);
				  $Valores->appendChild($ValorPis);
				  /* Criar a tag <ValorCofins>	*/
				  $valorCofins = str_replace(",",".",$tabelaNf->fields['nf_valor_cofins']);
				  $ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorInss = str_replace(",",".",$tabelaNf->fields['nf_valor_inss']);
				  $ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  $Valores->appendChild($ValorInss);
                                  
				  /* Criar a tag <ValorIr>	*/
				  $valorIr = str_replace(",",".",$tabelaNf->fields['nf_valor_ir']);
				  $ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  $Valores->appendChild($ValorIr);
				  
				  $valorContribuicaoSocial = str_replace(",",".",$tabelaNf->fields['nf_valor_contribuicao_social']);
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
    			  $Aliquota = $DomXml->createElement("Aliquota", number_format($aliquotaItemServico, 2, '.', '')); $Valores->appendChild($Aliquota); // N�o divide-se por 100% 

					/* Criar a tag <ValorLiquidoNfse>	*/
					//$ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",$tabelaNf->fields['nf_valor_total']);$Valores->appendChild($ValorLiquidoNfse);
				  /* Criar a tag <DescontoIncondicionado>	*/
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
				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $tabelaNf->fields['prestador_cnae']); $Servico->appendChild($CodigoCnae);
				  /* Criar a tag <CodigoTributacaoMunicipio>	FIXO 0 sem codigo tributacao do municipio */
				  $CodigoTributacaoMunicipio = $DomXml->createElement("CodigoTributacaoMunicipio", "0"); $Servico->appendChild($CodigoTributacaoMunicipio);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo."   |   ".$tabelaNf->fields['nf_observacao']); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/ 
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($codigoLocalPrestacaoServico)); $Servico->appendChild($CodigoMunicipio);
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
				if(strlen($tabelaNf->fields['prestador_cpf_cnpj'])<=11){
					$Cnpj = $DomXml->createElement("Cpf", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}else{
					$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			/* Criar a tag <IdentificacaoTomador>	*/
			$IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
			/* Criar a tag <CpfCnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);

			if($tabelaNf->fields['tomador_tipo'] == "F"){
			  /* Criar a tag <Cpf>	*/
			  //$Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			  $Cpf = $DomXml->createElement("Cpf",substr($tabelaNf->fields['tomador_cpf_cnpj'],3));
			}elseif($tabelaNf->fields['tomador_tipo'] == "J"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $tabelaNf->fields['tomador_cpf_cnpj']);
			}else{
			  $Cpf = $DomXml->createElement("Cpf", "99999999999");
			}
			$CpfCnpj->appendChild($Cpf);
			
			/* Criar a tag <RazaoSocial>	*/
			$RazaoSocial = $DomXml->createElement("RazaoSocial", $tabelaNf->fields['tomador_nome_razao_social'].$tabelaNf->fields['tomador_sobrenome_nome_fantasia']);
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Endereco2 = $DomXml->createElement("Endereco", $tabelaNf->fields['tomador_logradouro']); $Endereco->appendChild($Endereco2);
			/* Criar a tag <Numero>	*/
			$Numero = $DomXml->createElement("Numero", $tabelaNf->fields['tomador_numero_residencia']); $Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($tabelaNf->fields['tomador_complemento']) != ""){
			  $Complemento = $DomXml->createElement("Complemento", trim($tabelaNf->fields['tomador_complemento']));
			}else{
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $tabelaNf->fields['tomador_bairro']); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade'])); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
			$Uf = $DomXml->createElement("Uf", $tabelaNf->fields['tomador_estado']); $Endereco->appendChild($Uf);
			/* Criar a tag <CodigoPais>	*/
			$CodigoPais = $DomXml->createElement("CodigoPais", "1058"); $Endereco->appendChild($CodigoPais);
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $tabelaNf->fields['tomador_cep']); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($tabelaNf->fields['tomador_fone_residencial'],0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($tabelaNf->fields['tomador_ddd_fone_residencial'],0).ltrim($tabelaNf->fields['tomador_fone_residencial'],0));
			  $Contato->appendChild($Telefone);
			}

			/* Criar a tag <Email>	*/
                        if(trim($tabelaNf->fields['tomador_email']) != ""){
                            $Email = $DomXml->createElement("Email", $tabelaNf->fields['tomador_email']); $Contato->appendChild($Email);
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
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $tabelaNf->fields['prestador_optante_simples']);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivoFiscal>	*/
			$IncentivoFiscal = $DomXml->createElement("IncentivoFiscal", $tabelaNf->fields['prestador_incentivador_cultural']);
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
			return true;
		}
		
		public function xmlMaringa2($pCnpj, $pNumeroControle, $pStatus=""){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$DomXml = new DOMDocument();

			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pCnpj, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}

			$CItem = new CItem($this->grupo);
			$tabelaItem = $CItem->obterItem($pCnpj, $pNumeroControle);

			$CLote = new CLote($this->grupo);
			if(!$CLote->incrementarLote($pCnpj)){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}else{
				$tabelaLote = $CLote->obterLote($pCnpj);
			}
			
			$CGenerico = new CGenerico($this->grupo);
			$tabelaGenerico = $CGenerico->obterGenerico($pCnpj, $pNumeroControle="");

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = 0.00;
			$valorIncondicionado = 0.00;
			$valorIssRf = "";
			$valorTributavel = "";
			$descritivo = "";

			foreach($tabelaItem as $key=>$value){
			  if($situacao_tributaria != "" && $situacao_tributaria != $value['situacao_tributaria']){
				$this->mensagemErro = "CXml->CMaringa{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Maringa) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
				return false;
			  }
			  
			  $situacao_tributaria = $value['situacao_tributaria'];
			  $valorCsll += (float) $value['valor_csll'];
			  $valorIss += (float) $value['valor_iss'];
			  $valorCondicionado += (float) $value['desconto_cond'];
			  $valorIncondicionado += (float) $value['desconto_incond'];
			  $valorIssRf += (float) $value['valor_issrf'];
			  $valorTributavel += (float) $value['valor_tributavel'];
			  $aliquotaItemServico = $value['aliquota_item_servico'];
				  $codigoLista = ltrim($value['codigo_item_lista_servico'],0);
				  $codigoLista = substr($codigoLista,0,2).".".substr($codigoLista,2);
			  $descritivo .= $value['descritivo']."   |   ";
			  $codigoLocalPrestacaoServico = $value['codigo_local_prestacao_servico'];
			}
			/* Criar a tag
				<GerarNfseEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd"
									xmlns:xsd="http://www.w3.org/2001/XMLSchema"
									xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> */
			$GerarNfseEnvio = $DomXml->createElement("GerarNfseEnvio");
			$GerarNfseEnvio->setAttribute("xmlns","http://www.abrasf.org.br/nfse.xsd");
			$GerarNfseEnvio->setAttribute("xmlns:ds","http://www.w3.org/2000/09/xmldsig#");
			$GerarNfseEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$GerarNfseEnvio->setAttribute("xsi:schemaLocation","http://www.abrasf.org.br/nfse.xsd nfse_v2.01.xsd");

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($GerarNfseEnvio);
			/* Criar a tag <Rps>	*/
			$Rps = $DomXml->createElement("Rps"); $GerarNfseEnvio->appendChild($Rps);
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
			$Numero = $DomXml->createElement("Numero", (string) $tabelaLote->fields['rps']); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $tabelaNf->fields['nf_serie']); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $tabelaNf->fields['nf_tipo']); $IdentificacaoRps->appendChild($Tipo);
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
				  $servicosValor = $tabelaNf->fields['nf_valor_total'];
				  $ValorServicos = $DomXml->createElement("ValorServicos", $servicosValor); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorDeducoes>	*/
				  $valorDeducoes = "0.00";
				  $ValorDeducoes = $DomXml->createElement("ValorDeducoes", $valorDeducoes);
				  $Valores->appendChild($ValorDeducoes);
				  /* Criar a tag <ValorPis>	*/
				  $valorPis = $tabelaNf->fields['nf_valor_pis'];
				  $ValorPis = $DomXml->createElement("ValorPis", $valorPis);
				  $Valores->appendChild($ValorPis);
				  /* Criar a tag <ValorCofins>	*/
				  $valorCofins = $tabelaNf->fields['nf_valor_cofins'];
				  $ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorInss = $tabelaNf->fields['nf_valor_inss'];
				  $ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  $Valores->appendChild($ValorInss);
				  /* Criar a tag <ValorIr>	*/
				  $valorIr = $tabelaNf->fields['nf_valor_ir'];
				  $ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  $Valores->appendChild($ValorIr);
				  /* Criar a tag <ValorCsll>	Este ja esta somado a cima pois vem de cada item */
				  $ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
				  $Valores->appendChild($ValorCsll);
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
     			  $Aliquota = $DomXml->createElement("Aliquota", number_format($aliquotaItemServico, 2, '.', '')); $Valores->appendChild($Aliquota); // N�o divide-se por 100% 
					/* Criar a tag <ValorLiquidoNfse>	*/
					//$ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",$tabelaNf->fields['nf_valor_total']);$Valores->appendChild($ValorLiquidoNfse);
				  /* Criar a tag <DescontoIncondicionado>	*/
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
				  $ResponsavelRetencao = $DomXml->createElement("ResponsavelRetencao", "1"); $Servico->appendChild($ResponsavelRetencao);
				  /* Criar a tag <ItemListaServico>	*/
				  $codigoLista = str_replace(".","",$codigoLista);
				  $ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
				  /* Criar a tag <CodigoCnae>	*/
				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $tabelaNf->fields['prestador_cnae']); $Servico->appendChild($CodigoCnae);
				  /* Criar a tag <CodigoTributacaoMunicipio>	FIXO 0 sem codigo tributacao do municipio */
				  $CodigoTributacaoMunicipio = $DomXml->createElement("CodigoTributacaoMunicipio", "0"); $Servico->appendChild($CodigoTributacaoMunicipio);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo."   |   ".$tabelaNf->fields['nf_observacao']); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/ 
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($codigoLocalPrestacaoServico,0)); $Servico->appendChild($CodigoMunicipio);
				  /* Criar a tag <CodigoPais>	*/
				  $CodigoPais = $DomXml->createElement("CodigoPais", "1058"); $Servico->appendChild($CodigoPais);
				  /* Criar a tag <ExigibilidadeISS>	*/
				  $ExigibilidadeISS = $DomXml->createElement("ExigibilidadeISS", "1"); $Servico->appendChild($ExigibilidadeISS);
				  /* Criar a tag <MunicipioIncidencia>	*/
				  $MunicipioIncidencia = $DomXml->createElement("MunicipioIncidencia", ltrim($codigoLocalPrestacaoServico,0)); $Servico->appendChild($MunicipioIncidencia);
				  /* Criar a tag <NumeroProcesso>	*/
				  //$NumeroProcesso = $DomXml->createElement("NumeroProcesso", ""); $Servico->appendChild($NumeroProcesso);

			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $Prestador->appendChild($CpfCnpj);
				if(strlen($tabelaNf->fields['prestador_cpf_cnpj'])<=11){
					$Cnpj = $DomXml->createElement("Cpf", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}else{
					$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $CpfCnpj->appendChild($Cnpj);
				}
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			/* Criar a tag <IdentificacaoTomador>	*/
			$IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
			/* Criar a tag <CpfCnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);

			if($tabelaNf->fields['tomador_tipo'] == "F"){
			  /* Criar a tag <Cpf>	*/
			  //$Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			  $Cpf = $DomXml->createElement("Cpf",substr($tabelaNf->fields['tomador_cpf_cnpj'],3));
			}elseif($tabelaNf->fields['tomador_tipo'] == "J"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $tabelaNf->fields['tomador_cpf_cnpj']);
			}else{
			  $Cpf = $DomXml->createElement("Cpf", "99999999999");
			}
			$CpfCnpj->appendChild($Cpf);
			
			/* Criar a tag <RazaoSocial>	*/
			$RazaoSocial = $DomXml->createElement("RazaoSocial", $tabelaNf->fields['tomador_nome_razao_social'].$tabelaNf->fields['tomador_sobrenome_nome_fantasia']);
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Endereco2 = $DomXml->createElement("Endereco", $tabelaNf->fields['tomador_logradouro']); $Endereco->appendChild($Endereco2);
			/* Criar a tag <Numero>	*/
			$Numero = $DomXml->createElement("Numero", $tabelaNf->fields['tomador_numero_residencia']); $Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($tabelaNf->fields['tomador_complemento']) != ""){
			  $Complemento = $DomXml->createElement("Complemento", trim($tabelaNf->fields['tomador_complemento']));
			}else{
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $tabelaNf->fields['tomador_bairro']); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade'])); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
			$Uf = $DomXml->createElement("Uf", $tabelaNf->fields['tomador_estado']); $Endereco->appendChild($Uf);
			/* Criar a tag <CodigoPais>	*/
			$CodigoPais = $DomXml->createElement("CodigoPais", "1058"); $Endereco->appendChild($CodigoPais);
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $tabelaNf->fields['tomador_cep']); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($tabelaNf->fields['tomador_fone_residencial'],0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($tabelaNf->fields['tomador_ddd_fone_residencial'],0).ltrim($tabelaNf->fields['tomador_fone_residencial'],0));
			  $Contato->appendChild($Telefone);
			}

			/* Criar a tag <Email>	*/
			$Email = $DomXml->createElement("Email", $tabelaNf->fields['tomador_email']); $Contato->appendChild($Email);

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
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $tabelaNf->fields['prestador_optante_simples']);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivoFiscal>	*/
			$IncentivoFiscal = $DomXml->createElement("IncentivoFiscal", $tabelaNf->fields['prestador_incentivador_cultural']);
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
			return true;
		}
		
		// Montar XML para envio no municipio de Telamaco Borba / PR
		public function xmlTelemacoBorba($pCnpj, $pNumeroControle, $pStatus=""){
			$CNotaFiscal = new CNotaFiscal($this->grupo);
			$DomXml = new DOMDocument();
			
			$tabelaNf = $CNotaFiscal->obterNotaFiscal($pCnpj, $pNumeroControle);
			if($tabelaNf == false){
			  $this->mensagemErro = $CNotaFiscal->mensagemErro;
			}
			
			$CItem = new CItem($this->grupo);
			$tabelaItem = $CItem->obterItem($pCnpj, $pNumeroControle);

			$CLote = new CLote($this->grupo);
			if(!$CLote->incrementarLote($pCnpj)){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}else{
				$tabelaLote = $CLote->obterLote($pCnpj);
			}
			
			$CGenerico = new CGenerico($this->grupo);
			$tabelaGenerico = $CGenerico->obterGenerico($pCnpj, $pNumeroControle="");

			/* Percorrer todos os itens vinculados a nota fiscal */
			$situacao_tributaria = "";
			$valorCsll = "";
			$valorIss = "";
			$valorCondicionado = "";
			$valorIncondicionado = "";
			$valorIssRf = "";
			$valorTributavel = "";

			foreach($tabelaItem as $key=>$value){
			  if($situacao_tributaria != "" && $situacao_tributaria != $value['situacao_tributaria']){
				$this->mensagemErro = "CXml->CCuritiba{ Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CXml.php (Curitiba) -> Situacao tributaria divergente dentre os itens, devem ser com a mesma situacao tributaria \n\n", FILE_APPEND);
				return false;
			  }
			  
			  $situacao_tributaria = $value['situacao_tributaria'];
			  $valorCsll += (float) $value['valor_csll'];
			  $valorIss += (float) $value['valor_iss'];
			  $valorCondicionado += (float) $value['desconto_cond'];
			  $valorIncondicionado += (float) $value['desconto_incond'];
			  $valorIssRf += (float) $value['valor_issrf'];
			  $valorTributavel += (float) $value['valor_tributavel'];
			  $aliquotaItemServico = $value['aliquota_item_servico'];
				  $codigoLista = ltrim($value['codigo_item_lista_servico'],0);
				  //$codigoLista = substr($codigoLista,0,2).".".substr($codigoLista,2);
			  $descritivo .= $value['descritivo']."   |   ";
			}
			/* Criar a tag
				<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/ABRASF/arquivos/nfse.xsd"
									xmlns:xsd="http://www.w3.org/2001/XMLSchema"
									xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> */
			$EnviarLoteRpsEnvio = $DomXml->createElement("e:EnviarLoteRpsEnvio");
			$EnviarLoteRpsEnvio->setAttribute("xmlns:e","http://www.betha.com.br/e-nota-contribuinte-ws");

			/*$EnviarLoteRpsEnvio->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$EnviarLoteRpsEnvio->setAttribute("xsi:schemaLocation","http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd"); */
			

			/* Adiciona a TAG a tag principal <xml></xml> */
			$DomXml->appendChild($EnviarLoteRpsEnvio);
			/* Criar a tag LoteRps>	*/
			$LoteRps = $DomXml->createElement("LoteRps"); $EnviarLoteRpsEnvio->appendChild($LoteRps);
			$LoteRps->setAttribute("Id","LOTE_".$tabelaLote->fields['lote']);			
			/* Criar a tag <NumeroLote>	*/
			$NumeroLote = $DomXml->createElement("NumeroLote", (string) $tabelaLote->fields['lote']); $LoteRps->appendChild($NumeroLote);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $LoteRps->appendChild($Cnpj);
			$this->prestadorCnpj = $tabelaNf->fields['prestador_cpf_cnpj'];
			
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $LoteRps->appendChild($InscricaoMunicipal);
			/* Criar a tag <QuantidadeRps>	*/
			$QuantidadeRps = $DomXml->createElement("QuantidadeRps", "1"); $LoteRps->appendChild($QuantidadeRps);
			/* Criar a tag <ListaRps>	*/
			$ListaRps = $DomXml->createElement("ListaRps"); $LoteRps->appendChild($ListaRps);
			/* Criar a tag <Rps>	*/
			$Rps = $DomXml->createElement("Rps"); $ListaRps->appendChild($Rps);
			/* Criar a tag <InfRps>	*/
			$InfRps = $DomXml->createElement("InfRps"); $Rps->appendChild($InfRps);
			$InfRps->setAttribute("Id","RPS_".$tabelaLote->fields['lote']);
			/* Criar a tag <IdentificacaoRps>	*/
			$IdentificacaoRps = $DomXml->createElement("IdentificacaoRps"); $InfRps->appendChild($IdentificacaoRps);
			/* Criar a tag <Numero>	*/
			// SOMENTE PARA CURITIBA O NUMERO DA NOTA SERA IDENTIFICADO PELO NUMERO DO RPS DA TABELA LOTE E NAO PELO
			// NUMERO PROVENIENTE DO SISTEMA
			$Numero = $DomXml->createElement("Numero", (string) $tabelaLote->fields['rps']); $IdentificacaoRps->appendChild($Numero);
			/* Criar a tag <Serie>	*/
			$Serie = $DomXml->createElement("Serie", $tabelaNf->fields['nf_serie']); $IdentificacaoRps->appendChild($Serie);
			/* Criar a tag <Tipo>	*/
			$Tipo = $DomXml->createElement("Tipo", $tabelaNf->fields['nf_tipo']); $IdentificacaoRps->appendChild($Tipo);
			/* Criar a tag <DataEmissao>	*/
			$DataEmissao = $DomXml->createElement("DataEmissao", substr(date("c"),0,19)); $InfRps->appendChild($DataEmissao);
			/* Criar a tag <NaturezaOperacao>	*/
			$NaturezaOperacao = $DomXml->createElement("NaturezaOperacao", $situacao_tributaria); $InfRps->appendChild($NaturezaOperacao);
			/* Criar a tag <RegimeEspecialTributacao>	*/
			$RegimeEspecialTributacao = $DomXml->createElement("RegimeEspecialTributacao", $tabelaNf->fields['nf_regime_especial']);
			$InfRps->appendChild($RegimeEspecialTributacao);
			/* Criar a tag <OptanteSimplesNacional>	*/
			$OptanteSimplesNacional = $DomXml->createElement("OptanteSimplesNacional", $tabelaNf->fields['prestador_optante_simples']);
			$InfRps->appendChild($OptanteSimplesNacional);
			/* Criar a tag <IncentivadorCultural>	*/
			$IncentivadorCultural = $DomXml->createElement("IncentivadorCultural", $tabelaNf->fields['prestador_incentivador_cultural']);
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
				  $servicosValor = $tabelaNf->fields['nf_valor_total'];
				  $ValorServicos = $DomXml->createElement("ValorServicos", $servicosValor); $Valores->appendChild($ValorServicos);
				  /* Criar a tag <ValorDeducoes>	*/
				  $ValorDeducoes = $DomXml->createElement("ValorDeducoes", $tabelaNf->fields['nf_valor_deducoes']); $Valores->appendChild($ValorDeducoes);
				  /* Criar a tag <ValorPis>	*/
				  $valorPis = $tabelaNf->fields['nf_valor_pis'];
				  $ValorPis = $DomXml->createElement("ValorPis", $valorPis);
				  $Valores->appendChild($ValorPis);
				  /* Criar a tag <ValorCofins>	*/
				  $valorCofins = $tabelaNf->fields['nf_valor_cofins'];
				  $ValorCofins = $DomXml->createElement("ValorCofins", $valorCofins);
				  $Valores->appendChild($ValorCofins);
				  /* Criar a tag <ValorInss>	*/
				  $valorInss = $tabelaNf->fields['nf_valor_inss'];
				  $ValorInss = $DomXml->createElement("ValorInss", $valorInss);
				  $Valores->appendChild($ValorInss);
				  /* Criar a tag <ValorIr>	*/
				  $valorIr = $tabelaNf->fields['nf_valor_ir'];
				  $ValorIr = $DomXml->createElement("ValorIr", $valorIr);
				  $Valores->appendChild($ValorIr);
				  /* Criar a tag <ValorCsll>	Este ja esta somado a cima pois vem de cada item */
				  $ValorCsll = $DomXml->createElement("ValorCsll", $valorCsll);
				  $Valores->appendChild($ValorCsll);
				  /* Criar a tag <IssRetido> e <ValorIss> Este ja esta somado a cima pois vem de cada item */
				  if($valorIssRf != 0){
					$IssRetido = $DomXml->createElement("IssRetido", "1");
				  }else{
					$IssRetido = $DomXml->createElement("IssRetido", "2");
				  }
				  $Valores->appendChild($IssRetido);
				  
  				  /* Criar a tag <ValorIss>	*/
				  $ValorIss = $DomXml->createElement("ValorIss", $valorIss);
				  $Valores->appendChild($ValorIss);
				  /* Criar a tag <OutrasRetencoes>	*/
				  $outrasRetencoes = "0.00";
				  $OutrasRetencoes = $DomXml->createElement("OutrasRetencoes", $outrasRetencoes);
				  $Valores->appendChild($OutrasRetencoes);
  				  /* Criar a tag <ValorIssRf>	*/
				  $ValorIssRf = $DomXml->createElement("ValorIssRetido", $valorIssRf);
				  $Valores->appendChild($ValorIssRf);

				  /* Criar a tag <BaseCalculo>	*/
				  $BaseCalculo = $DomXml->createElement("BaseCalculo", $valorTributavel); $Valores->appendChild($BaseCalculo);
				  /* Criar a tag <Aliquota>	*/
    			  $Aliquota = $DomXml->createElement("Aliquota", ((float) $aliquotaItemServico/100)); $Valores->appendChild($Aliquota); // Divide-se por 100% pois no sistema � transmitido em Percentual e no XML deve ser transmitido em decimal.

				  /* Criar a tag <ValorLiquidoNfse>	*/
				  $ValorLiquidoNfse = $DomXml->createElement("ValorLiquidoNfse",$tabelaNf->fields['nf_valor_total']);$Valores->appendChild($ValorLiquidoNfse);
				  /* Criar a tag <DescontoIncondicionado>	*/
				  $DescontoIncondicionado=$DomXml->createElement("DescontoIncondicionado",$tabelaNf->fields['nf_valor_desconto_incondicional']);
				  $Valores->appendChild($DescontoIncondicionado);
				  /* Criar a tag <DescontoCondicionado>	*/
				  $DescontoCondicionado = $DomXml->createElement("DescontoCondicionado", $tabelaNf->fields['nf_valor_desconto_condicional']);
				  $Valores->appendChild($DescontoCondicionado);
				  /* Criar a tag <ItemListaServico>	*/
				  $ItemListaServico = $DomXml->createElement("ItemListaServico", $codigoLista); $Servico->appendChild($ItemListaServico);
				  /* Criar a tag <CodigoCnae>	*/
// 210714				  $CodigoCnae = $DomXml->createElement("CodigoCnae", $tabelaNf->fields['prestador_cnae']); $Servico->appendChild($CodigoCnae);
				  /* Criar a tag <CodigoTributacaoMunicipio>	*/
// 210714				  $CodigoTributacaoMunicipio = $DomXml->createElement("CodigoTributacaoMunicipio", $codigoLista); $Servico->appendChild($CodigoTributacaoMunicipio);
				  /* Criar a tag <Discriminacao>	*/
				  $Discriminacao = $DomXml->createElement("Discriminacao", $descritivo."   |   ".$tabelaNf->fields['nf_observacao']); $Servico->appendChild($Discriminacao);
				  /* Criar a tag <CodigoMunicipio>	*/
				  $CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['prestador_cidade'],0)); $Servico->appendChild($CodigoMunicipio);
			/* Criar a tag <Prestador>	*/
			$Prestador = $DomXml->createElement("Prestador"); $InfRps->appendChild($Prestador);
			/* Criar a tag <Cnpj>	*/
			$Cnpj = $DomXml->createElement("Cnpj", $tabelaNf->fields['prestador_cpf_cnpj']); $Prestador->appendChild($Cnpj);
			/* Criar a tag <InscricaoMunicipal>	*/
			$InscricaoMunicipal = $DomXml->createElement("InscricaoMunicipal", $tabelaNf->fields['prestador_inscricao_municipal']); $Prestador->appendChild($InscricaoMunicipal);
			/* Criar a tag <Tomador>	*/
			$Tomador = $DomXml->createElement("Tomador"); $InfRps->appendChild($Tomador);
			/* Criar a tag <IdentificacaoTomador>	*/
			$IdentificacaoTomador = $DomXml->createElement("IdentificacaoTomador"); $Tomador->appendChild($IdentificacaoTomador);
			/* Criar a tag <CpfCnpj>	*/
			$CpfCnpj = $DomXml->createElement("CpfCnpj"); $IdentificacaoTomador->appendChild($CpfCnpj);

			if($tabelaNf->fields['tomador_tipo'] == "F"){
			  /* Criar a tag <Cpf>	*/
			  //$Cpf = $DomXml->createElement("Cpf",$tabelaNf->fields['tomador_cpf_cnpj']);
			  $Cpf = $DomXml->createElement("Cpf",substr($tabelaNf->fields['tomador_cpf_cnpj'],3));
			}elseif($tabelaNf->fields['tomador_tipo'] == "J"){
			  /* Criar a tag <Cpf>	*/
			  $Cpf = $DomXml->createElement("Cnpj", $tabelaNf->fields['tomador_cpf_cnpj']);
			}else{
			  $Cpf = $DomXml->createElement("Cpf", "99999999999");
			}
			$CpfCnpj->appendChild($Cpf);
			
			/* Criar a tag <RazaoSocial>	*/
			$RazaoSocial = $DomXml->createElement("RazaoSocial", $tabelaNf->fields['tomador_nome_razao_social'].$tabelaNf->fields['tomador_sobrenome_nome_fantasia']);
			$Tomador->appendChild($RazaoSocial);
			/* Criar a tag <Endereco>	*/
			$Endereco = $DomXml->createElement("Endereco"); $Tomador->appendChild($Endereco);
			/* Criar a tag <Endereco>	*/
			$Endereco2 = $DomXml->createElement("Endereco", $tabelaNf->fields['tomador_logradouro']); $Endereco->appendChild($Endereco2);
			/* Criar a tag <Numero>	*/
			$Numero = $DomXml->createElement("Numero", $tabelaNf->fields['tomador_numero_residencia']); $Endereco->appendChild($Numero);
			/* Criar a tag <Complemento>	*/
			if(trim($tabelaNf->fields['tomador_complemento']) != ""){
			  $Complemento = $DomXml->createElement("Complemento", trim($tabelaNf->fields['tomador_complemento']));
			}else{
			  $Complemento = $DomXml->createElement("Complemento", ".");
			}
			/* Adiciona a TAG a tag  <Endereco> */
			$Endereco->appendChild($Complemento);
			/* Criar a tag <Bairro>	*/
			$Bairro = $DomXml->createElement("Bairro", $tabelaNf->fields['tomador_bairro']); $Endereco->appendChild($Bairro);
			/* Criar a tag <CodigoMunicipio>	*/
			$CodigoMunicipio = $DomXml->createElement("CodigoMunicipio", ltrim($tabelaNf->fields['tomador_cidade']),0); $Endereco->appendChild($CodigoMunicipio);
			/* Criar a tag <Uf>	*/
			$Uf = $DomXml->createElement("Uf", $tabelaNf->fields['tomador_estado']); $Endereco->appendChild($Uf);
			/* Criar a tag <Cep>	*/
			$Cep = $DomXml->createElement("Cep", $tabelaNf->fields['tomador_cep']); $Endereco->appendChild($Cep);
			/* Criar a tag <Endereco>	*/
			$Contato = $DomXml->createElement("Contato"); $Tomador->appendChild($Contato);
			/* Criar a tag <Telefone>	*/
			if(ltrim($tabelaNf->fields['tomador_fone_residencial'],0) != ""){
			  $Telefone = $DomXml->createElement("Telefone", ltrim($tabelaNf->fields['tomador_ddd_fone_residencial'],0).ltrim($tabelaNf->fields['tomador_fone_residencial'],0));
			  $Contato->appendChild($Telefone);
			}

			/* Criar a tag <Email>	*/
			$Email = $DomXml->createElement("Email", $tabelaNf->fields['tomador_email']); $Contato->appendChild($Email);

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
			return true;
		}
	
		
		public function xmlFernandoDeNoronha($pEmpresa, $pFilial, $pNumeroControle){
		//::TODO
		}
		public function xmlAraucaria($pEmpresa, $pFilial, $pNumeroControle){
		//::TODO
		}
		
		public function xmlCampoMagro($pEmpresa, $pFilial, $pNumeroControle){
		//::TODO
		}
	}
?>