<?php
	/*
		Classe:					CCritica.php
		Autor:					Guilherme Silva
		Data:					09/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de Critica
		Programas chamadores: 	
		Programas chamados: 	BD{critica}
	*/
	class CCritica{

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
		public function inserirCritica($pXml){

			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$pXml['descricao'] = str_replace("'","",$pXml['descricao']);
			
			$sql = "INSERT INTO nfse_".$this->grupo.".critica (cnpj, ".
										"numero_controle, ".
										"descricao, ".
										"data, ".
										"hora".
										") VALUES (";

			$sql .= "'".$pXml['cnpj']."',".
					" '".$pXml['numeroControle']."',".
					" '".$pXml['descricao']."',".
					" '".$pXml['data']."',".
					" '".$pXml['hora']."');";

					//echo "a inserir [".$sql."]";
					
			if ($this->ponteiro->Execute($sql) === false) {
			  $this->mensagemErro = " CCritica -> inserirCritica() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CCritica.php -> nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()." \n\n", FILE_APPEND);
			  return false;
			}else{
			  return true;
			}
		}
		public function obterCritica($pCnpj, $pNumeroControle){
			if($pCnpj == "" || $pNumeroControle == ""){
				$this->mensagemErro = " CCritica -> obterCritica() { parametro nao e opcional } ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CCritica.php -> obterCritica() { parametro nao e opcional }  \n\n", FILE_APPEND);
				return false;
			}
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();

			$sql = "SELECT * FROM nfse_".$this->grupo.".critica WHERE ".
					"cnpj=".$pCnpj." AND ".
					"numero_controle=".$pNumeroControle;
				
			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro =  " CCritica -> obterCritica() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CCritica.php -> obterCritica() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."}  \n\n", FILE_APPEND);
				return false;
			}
			while (!$recordSet->EOF) {
				$this->resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			
			return $this->resultado;
			exit();
		}
		public function excluirCritica(){
		}
		public function alterarCritica(){
		}		
		
	}
?>