<?php
/**
 * @name      	MCritica
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
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
	public $cnpj;
	public $ambiente;
	public $id_lote;
	public $numero;
	public $serie;
	public $sequencia;
	public $codigo_referencia;
	public $descricao;
	public $data_hora_critica;
	public $notificada;

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
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$this->descricao = str_replace("'",'"',$this->descricao);
		$sql = "INSERT INTO `mdfe_".$this->grupo."`.`CRITICA` (
										`cnpj`,
										`ambiente`,
										`id_lote`,
										`numero`,
										`serie`,
										`sequencia`,
										`codigo_referencia`,
										`descricao`,
										`data_hora_critica`,
										`notificada`) VALUES(
										'".$this->cnpj."',
										'".$this->ambiente."',
										'".$this->id_lote."',
										'".$this->numero."',
										'".$this->serie."',
										'".$this->sequencia."',
										'".$this->codigo_referencia."',
										'".$this->descricao."',
										'".date('Y-m-d H:i:s')."',
										'".$this->notificada."')";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MCritica -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funчуo para Retornar as criticas de uma MDFe
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

		$sql = "SELECT 	* FROM `mdfe_".$this->grupo."`.`CRITICA` ";

		if($this->cnpj != ""){	$where[] = "cnpj = '".$this->cnpj."'"; }
		if($this->ambiente != ""){	$where[] = "ambiente = '".$this->ambiente."'"; }
		if($this->id_lote != ""){	$where[] = "id_lote = '".$this->id_lote."'"; }
		if($this->numero != ""){	$where[] = "numero = '".$this->numero."'"; }
		if($this->serie != ""){	$where[] = "serie = '".$this->serie."'"; }
		if($this->sequencia != ""){	$where[] = "sequencia = '".$this->sequencia."'"; }
		if($this->codigo_referencia != ""){	$where[] = "codigo_referencia = '".$this->codigo_referencia."'"; }
		if($this->notificada != ""){	$where[] = "notificada = '".$this->notificada."'"; }

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
			$this->ponteiro->RollbackTrans();
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