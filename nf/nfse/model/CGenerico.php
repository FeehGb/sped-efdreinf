<?php
	/*
		Classe:					CGenerico.php
		Autor:					Guilherme Silva
		Data:					07/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de Item
		Programas chamadores: 	
		Programas chamados: 	BD{generico}
	*/
	class CGenerico{

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
		public function inserirGenerico($pCnpj, $pNumControle, $pXml){
		  if($pCnpj == "" || $pNumControle == "" || $pXml == ""){
			  $this->mensagemErro = " CGenerico -> inserirGenerico() {parametro obrigatorio} ";
			  return false;
		  }
			
		  if(!$this->excluirGenerico($pCnpj, $pNumControle)){ return false; }
		  
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  
		  $sql = "INSERT INTO nfse_".$this->grupo.".generico (cnpj, ".
										"numero_controle, ".
										"titulo, ".
										"descricao ".
										") VALUES (";
										
			$sql .= "'".$pCnpj."',".
					" '".$pNumControle."',";
					
			if($pXml->titulo){ $sql .= " '".$pXml->titulo."',"; }else{ $sql .= " '',"; }
			if($pXml->descricao){ $sql .= " '".$pXml->descricao."');"; }else{ $sql .= " '');"; }
			if ($this->ponteiro->Execute($sql) === false) {
				$this->mensagemErro = " CGenerico -> inserirGenerico() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				return false;
			}
		}
		public function obterGenerico($pCnpj, $pNumeroControle){
			if($pCnpj == "" || $pNumeroControle == ""){
				$this->mensagemErro = " CGenerico -> obterGenerico() {parametro obrigatorio} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();

			$sql = "SELECT * FROM nfse_".$this->grupo.".generico WHERE ".
					"cnpj=".$pCnpj." AND ".
					"numero_controle=".$pNumeroControle;

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro = " CGenerico -> obterGenerico() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				return false;
			}
			while (!$recordSet->EOF) {
				$this->resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			
			return $this->resultado;
			exit();
		}
		
		
		public function excluirGenerico($pCnpj, $pNumControle){
		  if($pCnpj == "" || $pNumControle == ""){
			$this->mensagemErro = " CItem -> excluirItem() {parametro obrigatorio} ";
			return false;
		  }
		  
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
  
		  $sql = "DELETE FROM nfse_".$this->grupo.".generico WHERE cnpj=".$pCnpj." AND ".
										  "numero_controle=".$pNumControle;
  
		  if ($this->ponteiro->Execute($sql) === false) {
			$this->mensagemErro = " CItem -> excluirItem() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }
			
		  return true;
		}
		public function alterarGenerico(){
		}		
		
	}
?>