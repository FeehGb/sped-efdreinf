<?php
	/*
		Classe:					CBackup.php
		Autor:					Guilherme Silva
		Data:					19/03/2012
		Finalidade: 			Responsavel manter a tabela de log do Backup
		Programas chamadores: 	
		Programas chamados: 	BD{backup}
	*/
	class CBackup{

		//Atributos
		private $ponteiro;
		public $mensagemErro;
		//Metodos
		//Inserir Nota Fiscal, parametros Array do XML
		public function inserirBackup($pNome="", $pData="", $pHora="", $pLink=""){
		  if($pNome == "" || $pData == "" || $pHora == "" || $pLink == ""){
			$this->mensagemErro = " CBackup -> inserirBackup() { O parametro nao opcional } ";
			return false;
		  }
		  $CBd = CBd::singleton();
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  
		  $sql = "INSERT INTO backup (nome, data, hora, link) VALUES (";
										  
			  $sql .= "'".$pNome."',".
					  "'".$pData."',".
					  "'".$pHora."',".					  
					  " '".$pLink."');";

			  if ($this->ponteiro->Execute($sql) === false) {
				$this->ponteiro->RollbackTrans();
				$this->mensagemErro = " CBackup -> inserirBackup() { nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." } ";
				return false;
			  }else{
  	  			$this->ponteiro->CommitTrans();
				return true;
			  }
		}
		
		public function obterBackup(){
			$CBd = CBd::singleton();
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM backup ORDER BY data DESC, hora DESC";

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
			  $this->mensagemErro = " CBackup -> obterBackup() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  return false;
			}
			
			while (!$recordSet->EOF) {
			  $resultado[] = $recordSet->fields;
			  $recordSet->MoveNext();
			}

			return $resultado;

		}
		
/*	Não há como excluir este LOG de Backup nem alterar */
	}
?>