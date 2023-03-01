<?php
	/*
		Classe:					CItem.php
		Autor:					Guilherme Silva
		Data:					07/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de Item
		Programas chamadores: 	
		Programas chamados: 	BD{Item}
	*/
	class CItem{

		//Atributos
		private $ponteiro;
		public $mensagemErro;
		private $grupo;
		//Metodos

		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}
		//Inserir Nota Fiscal, parametros Array do XML
		public function inserirItem($pCnpj, $pNumControle, $pXml){
		  if($pXml == "" || $pCnpj == "" || $pNumControle == ""){
			  $this->mensagemErro = " CItem -> inserirItem() {parametro obrgatorio} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> Parametro inicial obrigatorio \n\n", FILE_APPEND);
			  return false;
		  }
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  
		  $sql = "INSERT INTO nfse_".$this->grupo.".item (cnpj, ".
									"numero_controle, ".
									"tributa_municipio_prestador, ".
									"codigo_local_prestacao_servico, ".
									"unidade_codigo, ".
									"unidade_quantidade, ".
									"unidade_valor_unitario, ".
									"codigo_item_lista_servico, ".
									"descritivo, ".
									"aliquota_item_servico, ".
									"situacao_tributaria, ".
									"valor_tributavel, ".
									"valor_deducao, ".
									"valor_iss, ".
									"valor_issrf, ".
									"desconto_cond, ".
									"desconto_incond, ".
									"codigo_cnae, ".
									"codigo_tributacao_municipio, ".
									"valor_csll, ".
									"outras_retencoes, ".
									"situacaotributaria".
									") VALUES (";
										  
			  $sql .= "'".$pCnpj."',".
					  " '".$pNumControle."',";
					  
			  if($pXml->tributa_municipio_prestador){		$sql .= " '".$pXml->tributa_municipio_prestador."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->codigo_local_prestacao_servico){	$sql .= " '".$pXml->codigo_local_prestacao_servico."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->unidade_codigo){					$sql .= " '".$pXml->unidade_codigo."',"; }
			  	else{ $sql .= " '',"; }	
			  if($pXml->unidade_quantidade){				$sql .= " '".$pXml->unidade_quantidade."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->unidade_valor_unitario){			$sql .= " '".str_replace(",",".",$pXml->unidade_valor_unitario)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->codigo_item_lista_servico){			$sql .= " '".$pXml->codigo_item_lista_servico."',"; }
			  	else{ $sql .= " '',";}
			  if($pXml->descritivo){						$sql .= " '".$pXml->descritivo."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->aliquota_item_lista_servico){		$sql .= " '".str_replace(",",".",$pXml->aliquota_item_lista_servico)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->situacao_tributaria){				$sql .= " '".str_replace(",",".",$pXml->situacao_tributaria)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->valor_tributavel){					$sql .= " '".str_replace(",",".",$pXml->valor_tributavel)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->valor_deducao){						$sql .= " '".str_replace(",",".",$pXml->valor_deducao)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->valor_iss){							$sql .= " '".str_replace(",",".",$pXml->valor_iss)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->valor_issrf){						$sql .= " '".str_replace(",",".",$pXml->valor_issrf)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->desconto_cond){						$sql .= " '".str_replace(",",".",$pXml->desconto_cond)."',"; }
			  	else{ $sql .= " '0',"; }
  			  if($pXml->desconto_incond){					$sql .= " '".str_replace(",",".",$pXml->desconto_incond)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->codigo_cnae){						$sql .= " '".$pXml->codigo_cnae."',"; }
			  	else{ $sql .= " '',"; }
			  if($pXml->codigo_tributacao_municipio){		$sql .= " '".$pXml->codigo_tributacao_municipio."',"; }
			  	else{ $sql .= " '',";}
			  if($pXml->valor_csll){						$sql .= " '".str_replace(",",".",$pXml->valor_csll)."',"; }
			  	else{	$sql .= " '0',"; }
			  if($pXml->outras_retencoes){ 					$sql .= " '".str_replace(",",".",$pXml->outras_retencoes)."',"; }
			  	else{ $sql .= " '0',"; }
			  if($pXml->situacaotributaria){ 				$sql .= " '".$pXml->situacaotributaria."');"; }else{ $sql .= " '');"; }

			  if ($this->ponteiro->Execute($sql) === false) {
				$this->ponteiro->RollbackTrans();
				$this->mensagemErro = " CItem -> inserirItem() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
				return false;
			  }else{
  	  			$this->ponteiro->CommitTrans();
				return true;
			  }
		}
		
		public function obterItem($pCnpj, $pNumeroControle){
			if($pCnpj == "" || $pNumeroControle == ""){
				$this->mensagemErro = " CItem -> obterItem() {parametro obrigatorio} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> Parametro inicial obrigatorio \n\n", FILE_APPEND);
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM nfse_".$this->grupo.".item WHERE ".
					"cnpj=".$pCnpj." AND ".
					"numero_controle=".$pNumeroControle;
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
			  $this->mensagemErro =  " CItem -> obterItem() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			  $this->ponteiro->RollbackTrans();
			  return false;
			}
			while (!$recordSet->EOF) {
				$this->resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
  			$this->ponteiro->CommitTrans();
			return $this->resultado;
			exit();
		}

		public function excluirItem($pCnpj, $pNumControle){
		  if($pCnpj == "" || $pNumControle == ""){
			$this->mensagemErro = " CItem -> excluirItem() {parametro obrigatorio} ";
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> Parametro inicial obrigatorio \n\n", FILE_APPEND);
			return false;
		  }
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
  
		  $sql = "DELETE FROM nfse_".$this->grupo.".item WHERE cnpj=".$pCnpj." AND numero_controle=".$pNumControle;
  
		  if ($this->ponteiro->Execute($sql) === false) {
			$this->mensagemErro = " CItem -> excluirItem() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CItem.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			$this->ponteiro->RollbackTrans();
			return false;
		  }
		  $this->ponteiro->CommitTrans();
		  return true;
		}
	}
?>