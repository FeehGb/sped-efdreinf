<?php
	/*
		Classe:					CNotaFiscal.php
		Autor:					Guilherme Silva
		Data:					01/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de NF
		Programas chamadores: 	
		Programas chamados: 	BD{NF}
	*/
	require_once("/var/www/html/nf/nfse/model/CLote.php");
	require_once("/var/www/html/nf/nfse/model/CBd.php");
	
	class CNotaFiscal{

		//Atributos
		private $ponteiro;
		public $resultado;
		private $grupo;
		// Contador limite do 
		public $contLimit=0;
		
		public $mensagemErro;

		public $empresa_codigo;
		public $empresa_descricao;
		public $filial_codigo;
		public $filial_descricao;
		public $nf_numero;
		public $nf_situacao;
		public $nf_valor_total;
		public $nf_valor_desconto;
		public $nf_valor_ir;
		public $nf_valor_inss;
		public $nf_valor_contribuicao_social;
		public $nf_valor_rps;
		public $nf_valor_pis;
		public $nf_valor_cofins;
		public $nf_observacao;
		public $nf_serie;
		public $nf_data_emissao;
		public $nf_hora_emissao;
		public $nf_status;
		public $prestador_cpf_cnpj;
		public $prestador_cidade;
		public $tomador_tipo;
		public $tomador_identificador;
		public $tomador_estado;		
		public $tomador_pais;
		public $tomador_cpf_cnpj;
		public $tomador_ie;
		public $tomador_nome_razao_social;
		public $tomador_sobrenome_nome_fantasia;
		public $tomador_logradouro;
		public $tomador_email;
		public $tomador_numero_residencia;
		public $tomador_complemento;
		public $tomador_ponto_referencia;
		public $tomador_bairro;
		public $tomador_cidade;		
		public $tomador_cep;
		public $tomador_ddd_fone_comercial;
		public $tomador_fone_comercial;		
		public $tomador_ddd_fone_residencial;		
		public $tomador_fone_residencial;
		public $tomador_ddd_fax;
		public $tomador_fone_fax;		
		public $tomador_nf_controle;		
		public $tomador_produto_descricao;		
		public $tomador_valor_total;		
		public $periodoIni;
		public $periodoFim;
		
		//Metodos

		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}

		//Inserir Nota Fiscal, parametros Array do XML
		public function inserirNF($pXml=""){
			if($pXml == ""){
				$this->mensagemErro = " CNotaFiscal -> inserirNF() {parametro inicial nao e opcional} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> parametro inicial nao e opcional \n\n", FILE_APPEND);
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();
			
			$verificar = $this->verificaAlteraInsere($pXml);
			if(!$verificar){ $this->mensagemErro = $verificar; }
			
			if($verificar == "I"){
			  $sql = "INSERT INTO nfse_".$this->grupo.".nota_fiscal (empresa_codigo, empresa_descricao, filial_codigo, filial_descricao, nf_lote, nf_numero, nf_situacao, nf_valor_total, ".
											   "nf_valor_desconto, nf_valor_ir, nf_valor_inss, nf_valor_contribuicao_social, nf_valor_rps, nf_valor_pis, ".
											   "nf_valor_cofins, nf_observacao, prestador_cpf_cnpj, prestador_cidade, prestador_inscricao_municipal, prestador_cnae, ".
											   "prestador_optante_simples, prestador_incentivador_cultural, tomador_tipo, tomador_identificador, ".
											   "tomador_estado, tomador_pais, tomador_cpf_cnpj, tomador_ie, tomador_inscricao_municipal, tomador_nome_razao_social, ".
											   "tomador_sobrenome_nome_fantasia, ".
											   "tomador_logradouro, tomador_email, tomador_numero_residencia, tomador_complemento, tomador_ponto_referencia, ".
											   "tomador_bairro, tomador_cidade, tomador_cep, tomador_ddd_fone_comercial, tomador_fone_comercial, ".
											   "tomador_ddd_fone_residencial, tomador_fone_residencial, tomador_ddd_fax, tomador_fone_fax, nf_controle, ".
											   "produtos_descricao, produtos_valor_total, nf_serie, nf_data_emissao, nf_hora_emissao, nf_status, nf_link, nf_autenticacao, nf_protocolo, nf_regime_especial) VALUES (";
			  $sql .= " '".$pXml->nf->empresa->codigo."',".
					   "'".$pXml->nf->empresa->descricao."',".
					   "'".$pXml->nf->filial->codigo."',".
					   "'".$pXml->nf->filial->descricao."',";

			  $CLote = new CLote($this->grupo);
			  $lote = $CLote->obterLote($pXml->prestador->cpfcnpj);
			  if($lote == false){
				  $this->mensagemErro = $CLote->mensagemErro;
				  return false;
			  }else{
				  $sql .= " '".ltrim($lote->fields['lote'],0)."',";
			  }

			  if($pXml->nf->numero){					$sql .= " '".ltrim($pXml->nf->numero,0)."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->nf->situacao){					$sql .= " '".$pXml->nf->situacao."',";	}
			  	else{ $sql .= " '',";	}
			  if($pXml->nf->valor_total){				$sql .= " '".str_replace(",",".",$pXml->nf->valor_total)."',"; }
			  	else{	$sql .= " '0',";	}
			  if($pXml->nf->valor_desconto){			$sql .= " '".str_replace(",",".",$pXml->nf->valor_desconto)."',";	}
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_ir){ 					$sql .= " '".str_replace(",",".",$pXml->nf->valor_ir)."',";	}
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_inss){ 				$sql .= " '".str_replace(",",".",$pXml->nf->valor_inss)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_contribuicao_social){	$sql .= " '".str_replace(",",".",$pXml->nf->valor_contribuicao_social)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_rps){ 				$sql .= " '".str_replace(",",".",$pXml->nf->valor_rps)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_pis){ 				$sql .= " '".str_replace(",",".",$pXml->nf->valor_pis)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->valor_cofins){ 				$sql .= " '".str_replace(",",".",$pXml->nf->valor_cofins)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->nf->observacao){ 				$sql .= " '".$pXml->nf->observacao."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->prestador->cpfcnpj){ 			$sql .= " '".$pXml->prestador->cpfcnpj."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->prestador->cidade){ 			$sql .= " '".$pXml->prestador->cidade."',"; }
			  	else{ $sql .= " '',"; }
				  if($pXml->prestador->inscricaomunicipal){		$sql .= " '".$pXml->prestador->inscricaomunicipal."',"; }
				  	else{ $sql .= " '',"; }
				  if($pXml->prestador->cnae){ 					$sql .= " '".$pXml->prestador->cnae."',"; }
				  	else{ $sql .= " '',"; }
				  if($pXml->prestador->optantesimples){ 		$sql .= " '".$pXml->prestador->optantesimples."',"; }
				  	else{ $sql .= " '',"; }
				  if($pXml->prestador->incentivadorcultural){	$sql .= " '".$pXml->prestador->incentivadorcultural."',"; }
				  	else{ $sql .= " '',"; }
			  if($pXml->tomador->tipo){ 		$sql .= " '".$pXml->tomador->tipo."',"; }
			  	else{ $sql .= " '',";}
			  if($pXml->tomador->identificador){$sql .= " '".$pXml->tomador->identificador."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->tomador->estado){ 		$sql .= " '".$pXml->tomador->estado."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->tomador->pais){ 		$sql .= " '".$pXml->tomador->pais."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->tomador->cpfcnpj){ 		$sql .= " '".$pXml->tomador->cpfcnpj."',"; }
			  	else{ $sql .= " '',";}
			  if($pXml->tomador->ie){ 			$sql .= " '".$pXml->tomador->ie."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->inscricaomunicipal){	$sql .= " '".$pXml->tomador->inscricaomunicipal."',"; }else{ $sql .= " '',"; } // incluido novo 05-03 para IGU
			  if($pXml->tomador->nome_razao_social){ $sql .= " '".$pXml->tomador->nome_razao_social."',"; }else{ $sql .= " '',";}
			  if($pXml->tomador->sobrenome_nome_fantasia){ $sql .= " '".$pXml->tomador->sobrenome_nome_fantasia."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->logradouro){ $sql .= " '".$pXml->tomador->logradouro."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->email){ $sql .= " '".$pXml->tomador->email."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->numero_residencia){ $sql .= " '".$pXml->tomador->numero_residencia."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->complemento){ $sql .= " '".$pXml->tomador->complemento."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->ponto_referencial){ $sql .= " '".$pXml->tomador->ponto_referencia."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->bairro){ $sql .= " '".$pXml->tomador->bairro."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->cidade){ $sql .= " '".$pXml->tomador->cidade."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->cep){ $sql .= " '".$pXml->tomador->cep."',"; }else{ 	$sql .= " '',";	}
			  if($pXml->tomador->ddd_fone_comercial){ $sql .= " '".$pXml->tomador->ddd_fone_comercial."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->fone_comercial){ $sql .= " '".$pXml->tomador->fone_comercial."',"; }else{ $sql .= " '',";}
			  if($pXml->tomador->ddd_fone_residencial){ $sql .= " '".$pXml->tomador->ddd_fone_residencial."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->fone_residencial){ $sql .= " '".$pXml->tomador->fone_residencial."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->ddd_fax){ $sql .= " '".$pXml->tomador->ddd_fax."',"; }else{ $sql .= " '',"; }
			  if($pXml->tomador->fone_fax){ $sql .= " '".$pXml->tomador->fone_fax."',"; }else{ $sql .= " '',"; }
			  if($pXml->nf->controle){ $sql .= " '".$pXml->nf->controle."',"; }else{ $sql .= " '',"; }
			  if($pXml->produtos->descricao){ $sql .= " '".$pXml->produtos->descricao."',"; }else{ $sql .= " '',"; }
			  if($pXml->produtos->valor){ $sql .= " '".str_replace(",",".",$pXml->produtos->valor)."',"; }else{ $sql .= " '0',";}
			  if($pXml->nf->serie){ $sql .= " '".$pXml->nf->serie."',"; }else{ $sql .= " '',"; }
			  $sql .= " '".date("Y/m/d")."',"; // data
			  $sql .= " '".date("H:i:s")."',"; // hora
			  $sql .= " 'N',";  // status Nova
			  $sql .= " '',";  // link
			  $sql .= " '',";  // autenticacao			  
			  $sql .= " '',";  // protocolo - Para Curitiba
			  if($pXml->nf->nf_regime_especial){ $sql .= " '".$pXml->nf->nf_regime_especial."',"; }else{ $sql .= " '2'"; }
			  $sql .= ") ";

			  $sql .= "ON DUPLICATE KEY UPDATE
						empresa_codigo= '".$pXml->nf->empresa->codigo."',
						empresa_descricao= '".$pXml->nf->empresa->descricao."',
						filial_codigo= '".$pXml->nf->filial->codigo."',
						filial_descricao= '".$pXml->nf->filial->descricao."',
						nf_lote= '',
						nf_numero= '".$pXml->nf->numero."',
						nf_situacao= '".$pXml->nf->situacao."',
						nf_valor_total= '".$pXml->nf->valor_total."',
						nf_valor_desconto= '".$pXml->nf->valor_desconto."',
						nf_valor_ir= '".$pXml->nf->valor_ir."',
						nf_valor_inss= '".$pXml->nf->valor_inss."',
						nf_valor_contribuicao_social= '".$pXml->nf->valor_contribuicao_social."',
						nf_valor_rps= '".$pXml->nf->valor_rps."',
						nf_valor_pis= '".$pXml->nf->valor_pis."',
						nf_valor_cofins= '".$pXml->nf->valor_cofins."',
						nf_observacao= '".$pXml->nf->observacao."',
						prestador_cpf_cnpj= '".$pXml->prestador->cpfcnpj."',
						prestador_cidade= '".$pXml->prestador->cidade."',
						prestador_inscricao_municipal= '".$pXml->prestador->inscricaomunicipal."',
						prestador_cnae= '".$pXml->prestador->cnae."',
						prestador_optante_simples= '".$pXml->prestador->optantesimples."',
						prestador_incentivador_cultural= '".$pXml->prestador->incentivadorcultural."',
						tomador_tipo= '".$pXml->tomador->tipo."',
						tomador_identificador= '".$pXml->tomador->identificador."',
						tomador_estado= '".$pXml->tomador->estado."',
						tomador_pais= '".$pXml->tomador->pais."',
						tomador_cpf_cnpj= '".$pXml->tomador->cpfcnpj."',
						tomador_ie= '".$pXml->tomador->ie."',
						tomador_inscricao_municipal= '".$pXml->tomador->inscricaomunicipal."',
						tomador_nome_razao_social= '".$pXml->tomador->nome_razao_social."',
						tomador_sobrenome_nome_fantasia= '".$pXml->tomador->sobrenome_nome_fantasia."',
						tomador_logradouro= '".$pXml->tomador->logradouro."',
						tomador_email= '".$pXml->tomador->email."',
						tomador_numero_residencia= '".$pXml->tomador->numero_residencia."',
						tomador_complemento= '".$pXml->tomador->complemento."',
						tomador_ponto_referencia= '".$pXml->tomador->ponto_referencial."',
						tomador_bairro= '".$pXml->tomador->bairro."',
						tomador_cidade= '".$pXml->tomador->cidade."',
						tomador_cep= '".$pXml->tomador->cep."',
						tomador_ddd_fone_comercial= '".$pXml->tomador->ddd_fone_comercial."',
						tomador_fone_comercial= '".$pXml->tomador->fone_comercial."',
						tomador_ddd_fone_residencial= '".$pXml->tomador->ddd_fone_residencial."',
						tomador_fone_residencial= '".$pXml->tomador->fone_residencial."',
						tomador_ddd_fax= '".$pXml->tomador->ddd_fax."',
						tomador_fone_fax= '".$pXml->tomador->fone_fax."',
						nf_controle= '".$pXml->nf->controle."',
						produtos_descricao= '".$pXml->produtos->descricao."',
						produtos_valor_total= '".$pXml->produtos->valor."',
						nf_serie= '".$pXml->nf->serie."',
						nf_data_emissao= '".date("Y/m/d")."',
						nf_hora_emissao= '".date("H:i:s")."',
						nf_status= 'N',
						nf_link= '',
						nf_autenticacao= '',
						nf_protocolo= ''";
			  
			  if ($this->ponteiro->Execute(utf8_decode($sql)) === false) {
	  			$this->ponteiro->RollbackTrans();
				$this->mensagemErro = " CNotaFiscal -> inserirNF() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
				return false;
			  }else{
  	  			$this->ponteiro->CommitTrans();
				return true;
			  }

			}elseif($verificar == "A"){
			  if(!$this->atualizarNF($pXml)){
				return false;
			  }else{
				return true;
			  }
			}
		}
		

		/*
			Verifica se já tem uma NF enviada com o status S-Autorizada ou C-Cancelada
			Alterada 26/02/2014
		*/

		public function verificarNF($pXml=""){
			if($pXml == ""){
				$this->mensagemErro = " CNotaFiscal -> verificarNF() {parametro nao e opcional} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> parametro inicial nao e opcional \n\n", FILE_APPEND);
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();

			$sql = "SELECT COUNT(*) FROM nfse_".$this->grupo.".nota_fiscal WHERE ".
					"prestador_cpf_cnpj=".$pXml->prestador->cpfcnpj." AND ".
					"nf_controle=".$pXml->nf->controle." AND".
					"(nf_status='S' OR nf_status='C')";

			$recordSet = $this->ponteiro->Execute($sql);
			if(!$recordSet){
				$this->mensagemErro = " CNotaFiscal -> verificarNF() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
				return false;
			}
			if($recordSet->fields[0]>0){
				$this->mensagemErro = " CNotaFiscal -> verificarNF() {Esta Nota Fiscal ja foi autorizada/cancelada pela prefeitura altere o Numero de Controle e tente novamente }";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> Esta Nota Fiscal ja foi cadatrada no sistema altere o Numero de Controle e tente novamente \n\n", FILE_APPEND);
				return false;
			}
			return true;
 		}

		public function obterNotaFiscal($pCnpj, $pNumeroControle){
			if($pCnpj == "" || $pNumeroControle == ""){
				$this->mensagemErro = " CNotaFiscal -> obterNotaFiscal() {parametro obrigatorio} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> parametro inicial nao e opcional \n\n", FILE_APPEND);
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();

			$sql = "SELECT *FROM nfse_".$this->grupo.".nota_fiscal WHERE ".
					"prestador_cpf_cnpj='".$pCnpj."' AND ".
					"nf_controle='".ltrim($pNumeroControle,0)."' ".
					"LIMIT 1";

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro = " CNotaFiscal -> obterNotaFiscal() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
				return false;
			}
			$this->resultado = $recordSet;
			return $recordSet;
		}
		
		public function obterContribuintes(){
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$sql = "SELECT empresa_codigo, filial_codigo, filial_descricao, prestador_cpf_cnpj FROM nfse_".$this->grupo.".nota_fiscal GROUP BY prestador_cpf_cnpj;";

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
			  $this->mensagemErro = " CNotaFiscal -> obterContribuintes() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			  return false;
			}

			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}

			return $resultado;
		}
		
		public function obterNotasFiscais(){
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();

			$where = "";		
			if($this->prestador_cpf_cnpj != ""){	$where[] = " prestador_cpf_cnpj = '".$this->prestador_cpf_cnpj."'"; }
			if($this->nf_status != ""){				$where[] = " nf_status = '".$this->nf_status."'"; }
			if($this->periodoIni != "" && $this->periodoFim != ""){
				$dataInicial = substr($this->periodoIni,6,4)."-".substr($this->periodoIni,3,2)."-".substr($this->periodoIni,0,2);
				$dataFinal 	 = substr($this->periodoFim,6,4)."-".substr($this->periodoFim,3,2)."-".substr($this->periodoFim,0,2);
				$where[] = " nf_data_emissao >= '".$dataInicial."' AND nf_data_emissao <= '".$dataFinal."'";
			}
		
			// Verifica se utiliza as condições, caso contrário irá selecionar tudo.
			if($where != ""){
				$where = implode(" AND ", $where);
			}

			if($where != ""){
				$sql = "SELECT * FROM nfse_".$this->grupo.".nota_fiscal WHERE ".$where." ORDER BY nf_data_emissao DESC, nf_hora_emissao DESC LIMIT ".$this->contLimit * 50 .",50";
			}else{
				$sql = "SELECT * FROM nfse_".$this->grupo.".nota_fiscal ORDER BY nf_data_emissao DESC, nf_hora_emissao DESC LIMIT ". $this->contLimit * 50 .",50";
			}
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
			  $this->mensagemErro = " CNotaFiscal -> obterNotasFiscais() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			  return false;
			}
			
//			$resultado; não sei pq tá aqui
			
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}

			return $resultado;
		}
		
		public function verificaAlteraInsere($pXml){
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  
		  $sql = "SELECT COUNT(*) FROM nfse_".$this->grupo.".nota_fiscal WHERE ".
				  "prestador_cpf_cnpj=".$pXml->prestador->cpfcnpj." AND ".
				  "nf_controle=".$pXml->nf->controle;
		  $recordSet = $this->ponteiro->Execute($sql);
		  if(!$recordSet){
			$this->mensagemErro = " CNotaFiscal -> verificarAlteraInsere() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			return false;
		  }
		  if($recordSet->fields[0] > 0){
			return "A";
		  }else{
			return "I";
		  }
		}
		
		public function excluirNF(){
		}
		
		public function atualizarNF($pXml){
		  if(!isset($pXml->nf->controle)){
			$this->mensagemErro = " CNotaFiscal -> atualizarNF() {parametro nao e opcional} ";
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> parametros inicial nao e opcional \n\n", FILE_APPEND);
			return false;
		  }
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  
		  $sql = "UPDATE nfse_".$this->grupo.".nota_fiscal SET ";
			if($pXml->nf->empresa->descricao!=""){ $sql .= "empresa_descricao = '".$pXml->nf->empresa->descricao."',"; }
			if($pXml->nf->filial->descricao!=""){ $sql .= "filial_descricao = '".$pXml->nf->filial->descricao."',"; }
			if($pXml->nf->numero!=""){ $sql .= "nf_numero = '".$pXml->nf->numero."',"; }
			if($pXml->nf->situacao!=""){ $sql .= "nf_situacao = '".$pXml->nf->situacao."',"; }
			if($pXml->nf->valor_total!=""){ $sql .= "nf_valor_total = '".$pXml->nf->valor_total."',"; }
			if($pXml->nf->valor_desconto!=""){ $sql .= "nf_valor_desconto = '".$pXml->nf->valor_desconto."',"; }
			if($pXml->nf->valor_ir!=""){ $sql .= "nf_valor_ir = '".$pXml->nf->valor_ir."',"; }
			if($pXml->nf->valor_inss!=""){ $sql .= "nf_valor_inss = '".$pXml->nf->valor_inss."',"; }
			if($pXml->nf->valor_contribuicao_social!=""){ $sql .= "nf_valor_contribuicao_social = '".$pXml->nf->valor_contribuicao_social."',"; }
			if($pXml->nf->valor_rps!=""){ $sql .= "nf_valor_rps= '".$pXml->nf->valor_rps."',"; }
			if($pXml->nf->valor_pis!=""){ $sql .= "nf_valor_pis= '".$pXml->nf->valor_pis."',"; }
			if($pXml->nf->valor_cofins!=""){ $sql .= "nf_valor_cofins= '".$pXml->nf->valor_cofins."',"; }
			if($pXml->nf->observacao!=""){ $sql .= "nf_observacao= '".$pXml->nf->observacao."',"; }
			if($pXml->prestador->cpfcnpj!=""){ $sql .= "prestador_cpf_cnpj='".$pXml->prestador->cpfcnpj."',"; }
			if($pXml->prestador->cidade!=""){ $sql .= "prestador_cidade='".$pXml->prestador->cidade."',"; }
			if($pXml->tomador->tipo!=""){ $sql .= "tomador_tipo='".$pXml->tomador->tipo."',"; }			
			if($pXml->tomador->identificador!=""){ $sql .= "tomador_identificador='".$pXml->tomador->identificador."',"; }
			if($pXml->tomador->estado!=""){ $sql .= "tomador_estado='".$pXml->tomador->estado."',"; }
			if($pXml->tomador->pais!=""){ $sql .= "tomador_pais='".$pXml->tomador->pais."',"; }
			if($pXml->tomador->cpfcnpj!=""){ $sql .= "tomador_cpf_cnpj='".$pXml->tomador->cpfcnpj."',"; }
			if($pXml->tomador->ie!=""){ $sql .= "tomador_ie='".$pXml->tomador->ie."',"; }
			if($pXml->tomador->nome_razao_social!=""){ $sql .= "tomador_nome_razao_social='".$pXml->tomador->nome_razao_social."',"; }
			if($pXml->tomador->sobrenome_nome_fantasia!=""){ $sql .= "tomador_sobrenome_nome_fantasia='".$pXml->tomador->sobrenome_nome_fantasia."',"; }
			if($pXml->tomador->logradouro!=""){ $sql .= "tomador_logradouro='".$pXml->tomador->logradouro."',"; }
			if($pXml->tomador->email!=""){ $sql .= "tomador_email='".$pXml->tomador->email."',"; }
			if($pXml->tomador->numero_residencia!=""){ $sql .= "tomador_numero_residencia='".$pXml->tomador->numero_residencia."',"; }
			if($pXml->tomador->complemento!=""){ $sql .= "tomador_complemento='".$pXml->tomador->complemento."',"; }
			if($pXml->tomador->ponto_referencia!=""){ $sql .= "tomador_ponto_referencia='".$pXml->tomador->ponto_referencia."',"; }
			if($pXml->tomador->bairro!=""){ $sql .= "tomador_bairro='".$pXml->tomador->bairro."',"; }
			if($pXml->tomador->cidade!=""){ $sql .= "tomador_cidade='".$pXml->tomador->cidade."',"; }
			if($pXml->tomador->cep!=""){ $sql .= "tomador_cep='".$pXml->tomador->cep."',"; }			
			if($pXml->tomador->ddd_fone_comercial!=""){ $sql .= "tomador_ddd_fone_comercial='".$pXml->tomador->ddd_fone_comercial."',"; }
			if($pXml->tomador->fone_comercial!=""){ $sql .= "tomador_fone_comercial='".$pXml->tomador->fone_comercial."',"; }			
			if($pXml->tomador->ddd_fone_residencial!=""){ $sql .= "tomador_ddd_fone_residencial='".$pXml->tomador->ddd_fone_residencial."',"; }
			if($pXml->tomador->fone_residencial!=""){ $sql .= "tomador_fone_residencial='".$pXml->tomador->fone_residencial."',"; }
			if($pXml->tomador->ddd_fax!=""){ $sql .= "tomador_ddd_fax='".$pXml->tomador->ddd_fax."',"; }
			if($pXml->tomador->fone_fax!=""){ $sql .= "tomador_fone_fax='".$pXml->tomador->fone_fax."',"; }
			if($pXml->produtos->descricao!=""){ $sql .= "produtos_descricao='".$pXml->produtos->descricao."',"; }
			if($pXml->produtos->valor_total!=""){ $sql .= "produtos_valor_total='".$pXml->produtos->valor_total."',"; }
			if($pXml->nf->serie!=""){ $sql .= "nf_serie='".$pXml->nf->serie."',"; }
			if($pXml->nf->link!=""){ $sql .= "nf_link='".$pXml->nf->link."',"; }
			if($pXml->nf->autenticacao!=""){ $sql .= "nf_autenticacao='".$pXml->nf->autenticacao."',"; }
			if($pXml->nf->protocolo!=""){ $sql .= "nf_protocolo='".$pXml->nf->protocolo."',"; }
			if($pXml->nf->status!=""){ $sql .= "nf_status='".$pXml->nf->status."',"; }else{ $sql .= "nf_status='N',"; }
			$sql .= "nf_data_emissao='".date("Y/m/d")."',"; // data
			$sql .= "nf_hora_emissao='".date("H:i:s")."' "; // hora
			$sql .= "WHERE prestador_cpf_cnpj = '".$pXml->prestador->cpfcnpj."' AND ";
			$sql .= "nf_controle = '".ltrim($pXml->nf->controle,0)."'";

			if ($this->ponteiro->Execute(utf8_decode($sql)) === false) {
			  $this->ponteiro->RollbackTrans();
			  $this->mensagemErro = " CNotaFiscal -> atualizarNF() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CNotaFiscal.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			  return false;
			}
  			$this->ponteiro->CommitTrans();			
			return true;
		}		
	}
?>