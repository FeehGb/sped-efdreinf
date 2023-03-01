<?php
	/*
		Classe:					CCodigoTom.php
		Autor:					Guilherme Silva
		Data:					07/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de Item
		Programas chamadores: 	
		Programas chamados: 	BD{Item}
	*/
	class CCodigoTom{

		//Atributos
		private $ponteiro;
		public $mensagemErro;
		private $grupo;

		//Metodos
		
		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}

		//Obter o codigo IBGE
		public function obterCodigoIbge($pCodigoTom = ""){
			if($pCodigoTom == ""){
				$this->mensagemErro = " CCodigoTom -> obterCodigoIbge() {parametro nao e opcional} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM nfse_config.tom_cidade WHERE ".
					"codigo_tom=".$pCodigoTom." LIMIT 1";
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) { 
				$this->mensagemErro =  " CCodigoTom -> obterCodigoIbge() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}
			$this->resultado = $recordSet->fields['codigo_ibge'];
  			$this->ponteiro->CommitTrans();
			return $this->resultado;
			exit();
		}


		public function obterCodigoTom($pCodigoIbge = ""){
			if($pCodigoIbge == ""){
				$this->mensagemErro = " CCodigoTom -> obterCodigoTom() {parametro nao e opcional} ";
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM nfse_config.tom_cidade WHERE ".
					"codigo_ibge = ".$pCodigoIbge." LIMIT 1";
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) { 
				$this->mensagemErro =  " CCodigoTom -> obterCodigoTom() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}
			$this->resultado = $recordSet->fields['codigo_tom'];
  			$this->ponteiro->CommitTrans();
			return $this->resultado;
			exit();
		}
	}
?>