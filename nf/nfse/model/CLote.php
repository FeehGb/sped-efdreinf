
<?php
	/*
		Classe:					CLote.php
		Autor:					Guilherme Silva
		Data:					06/03/2012
		Finalidade: 			Responsavel por manutencoes na tabela de lote
		Programas chamadores: 	
		Programas chamados: 	BD{lote}
	*/
	class CLote{

		//Atributos
		private $ponteiro;
		public $mensagemErro;
		public $grupo;

		//Metodos

		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}
	   	
		//Inserir Nota Fiscal, parametros Array do XML
		public function inserirLote($pCnpj, $pLote, $pRPS=""){
		  if($pCnpj == "" || $pLote == "" || $pRPS == ""){
			  $this->mensagemErro = " CLote -> inserirLote() {parametro nao e opcional} ";
			  return false;
		  }
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  
		  $sql = "INSERT INTO nfse_".$this->grupo.".lote (cnpj, ".
									"lote, ".
									"rps".
									") VALUES (";
										  
			  $sql .= "'".$pCnpj."',".
					  " '".$pLote."',".
					  " '".$pRPS."');";

			  //if ($this->ponteiro->Execute($sql) == false) {
			  $recordSet = $this->ponteiro->Execute($sql);
			  if (!$recordSet) {
	 			 $this->mensagemErro = " CLote -> inserirLote() {nao foi possivel executar o codigo sql = $sql  erro=".$this->ponteiro->ErrorMsg(). "} ";
				  $this->ponteiro->RollbackTrans();
				  return false;
			  }else{
  	  			 $this->ponteiro->CommitTrans();
				 return true;
			  }
		}
		
		// Serve para consultar Lote e RPS
		public function obterLote($pCnpj=""){
			if($pCnpj == ""){
				$this->mensagemErro = " CLote -> obterLote() {parametro nao e opcional} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM nfse_".$this->grupo.".lote WHERE cnpj=".$pCnpj." LIMIT 0 , 1";
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro = " CLote -> obterLote() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				return false;
			}
			$this->resultado = $recordSet;
			return $recordSet;
		}
		
		public function excluirLote($pCnpj){
		  if($pCnpj == ""){
			$this->mensagemErro = " CLote -> excluirLote() {parametro nao eh opcional} ";
			return false;
		  }
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
  
		  $sql = "DELETE FROM nfse_".$this->grupo.".lote WHERE cnpj='".$pCnpj."'";
  
		  if ($this->ponteiro->Execute($sql) === false) {
			$this->mensagemErro = " CLote -> excluirLote() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			$this->ponteiro->RollbackTrans();
			return false;
		  }
		  $this->ponteiro->CommitTrans();
		  return true;
		}

		// Serve para incrementar Lote e RPS
		public function incrementarLote($pCnpj, $pFlagIncrementaRps=true){
			if($pCnpj == ""){
				$this->mensagemErro = " CLote -> incrementarLote() {parametro nao e opcional} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			if(!$pFlagIncrementaRps){
				$sql = "UPDATE nfse_".$this->grupo.".lote  SET lote =  (lote+1) ".
						"WHERE cnpj=".$pCnpj;
			}else{
				$sql = "UPDATE nfse_".$this->grupo.".lote  SET lote =  (lote+1), ".
						"rps =  (rps+1) WHERE ".
						"cnpj=".$pCnpj;
			}
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro =  " CLote -> obterLote() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}

  			$this->ponteiro->CommitTrans();
			return true;
		}
		
		// Serve para incrementar Lote e RPS
		public function incrementarRps($pCnpj){
			if($pCnpj == ""){
				$this->mensagemErro = " CLote -> incrementarRps() {parametro nao e opcional} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "UPDATE nfse_".$this->grupo.".lote  SET rps =  (rps+1) ".
					"WHERE cnpj=".$pCnpj;
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				
				$this->mensagemErro =  " CLote -> incrementarRps() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}
			
  			$this->ponteiro->CommitTrans();
			return true;
		}

	}
?>