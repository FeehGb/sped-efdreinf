<?php
/**
 * @name      	MCritica
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela CRITICA do banco de dados
 * @TODO 		Testar Classe
*/
	require_once("MBd.php");
/**
 * Classe MCritica
 */

class MCritica{
	/*
     *	 Atributos (campos) da tabela Critica
     */
	public $EVENTO_NOTA_FISCAL_cnpj_emitente;
	public $EVENTO_NOTA_FISCAL_numero_nota;
	public $EVENTO_NOTA_FISCAL_serie_nota;
	public $EVENTO_NOTA_FISCAL_ambiente;
	public $sequencia;
	public $codigo_referencia;
	public $descricao;
	public $data_hora_critica;

	/*
     *	 Atributos locais para comunicaчуo com banco de dados
     */
	private $ponteiro 		= "";
	public  $mensagemErro 	= "";
	private $grupo;
	
	// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Funчуo para Inserir registro de criticas na base de dados
	 *	@autor Guilherme Silva
	 */
	public function insert(){
		if($this->EVENTO_NOTA_FISCAL_cnpj_emitente == "" 	||
		  $this->EVENTO_NOTA_FISCAL_numero_nota == ""		||
		  $this->EVENTO_NOTA_FISCAL_serie_nota == ""		||
		  $this->EVENTO_NOTA_FISCAL_ambiente == ""			){
			$this->mensagemErro = " MCritica -> insert() {para esta opcao parametros obrigatorios: cnpj emitente(".$this->EVENTO_NOTA_FISCAL_cnpj_emitente."), numero nota(".$this->EVENTO_NOTA_FISCAL_numero_nota."), serie nota(".$this->EVENTO_NOTA_FISCAL_serie_nota."), ambiente(".$this->EVENTO_NOTA_FISCAL_ambiente.") } ";
			return false;
		}
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$this->descricao = str_replace("'",'"',$this->descricao);
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`CRITICA` (	`EVENTO_NOTA_FISCAL_cnpj_emitente`,
										`EVENTO_NOTA_FISCAL_numero_nota`,
										`EVENTO_NOTA_FISCAL_serie_nota`,
										`EVENTO_NOTA_FISCAL_ambiente`,
										`sequencia`,
										`codigo_referencia`,
										`descricao`,
										`data_hora_critica`) VALUES(
										'".$this->EVENTO_NOTA_FISCAL_cnpj_emitente."',
										'".$this->EVENTO_NOTA_FISCAL_numero_nota."',
										'".$this->EVENTO_NOTA_FISCAL_serie_nota."',
										'".$this->EVENTO_NOTA_FISCAL_ambiente."',
										NULL,
										'".addslashes($this->codigo_referencia)."',
										'".addslashes($this->descricao)."',
										'".$this->data_hora_critica."')";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MCritica -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funчуo para Retornar as criticas de uma nota fiscal
	 *	@autor Guilherme Silva
	 */
	public function select(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT 	* FROM `nfe_".$this->grupo."`.`CRITICA` ";

		if($this->EVENTO_NOTA_FISCAL_cnpj_emitente != ""){	$where[] = "EVENTO_NOTA_FISCAL_cnpj_emitente = '".$this->EVENTO_NOTA_FISCAL_cnpj_emitente."'"; }
		if($this->EVENTO_NOTA_FISCAL_numero_nota != ""){	$where[] = "EVENTO_NOTA_FISCAL_numero_nota = '".$this->EVENTO_NOTA_FISCAL_numero_nota."'"; }
		if($this->EVENTO_NOTA_FISCAL_serie_nota != ""){		$where[] = "EVENTO_NOTA_FISCAL_serie_nota = '".$this->EVENTO_NOTA_FISCAL_serie_nota."'"; }
		if($this->EVENTO_NOTA_FISCAL_ambiente != ""){		$where[] = "EVENTO_NOTA_FISCAL_ambiente = '".$this->EVENTO_NOTA_FISCAL_ambiente."'"; }
		if($this->sequencia != ""){							$where[] = "sequencia = '".$this->sequencia."'"; }
		if($this->codigo_referencia != ""){					$where[] = "codigo_referencia = '".$this->codigo_referencia."'"; }
		if($this->descricao != ""){							$where[] = "descricao = '".$this->descricao."'"; }
		if($this->data_hora_critica != ""){					$where[] = "data_hora_critica = '".$this->data_hora_critica."'"; }

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		if($pSql!=""){
			$sql = $pSql;
		}
		
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MCritica -> select() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			$resultado;
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(isset($resultado)){
				return $resultado;
			}else{ return true; }
		}
	}

}
?>