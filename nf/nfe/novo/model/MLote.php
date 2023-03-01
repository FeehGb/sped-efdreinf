<?php
/**
 * @name      	MLote
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela LOTE do Banco de Dados
 * @TODO 		Testar Classe
*/

	require_once("MBd.php");
/**
 * Classe MLote
 */

class MLote{
	/*
     *	 Atributos (campos) da tabela Evento
     */
	public $id;
	public $cnpj_emitente;
	public $versao;
	public $recibo;
	public $status;
	public $ambiente;
	public $contingencia;

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
	 *	@function Funчуo para Inserir registro de lote na base de dados
	 *	@autor Guilherme Silva
	 */
	public function insert(){
		if($this->cnpj_emitente == "" || $this->ambiente == ""){
			$this->mensagemErro = " MLote -> insert() {para esta opcao parametros obrigatorios: Cnpj, ambiente } ";
			return false;
		}
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`LOTE` (
				`id`,
				`cnpj_emitente`,
				`versao`,
				`recibo`,
				`status`,
				`ambiente`,
				`contingencia`
				) VALUES (".
				"NULL,".
				"'".$this->cnpj_emitente."',".
				"'".$this->versao."',".
				"'".$this->recibo."',".
				"'".$this->status."',".
				"'".$this->ambiente."',".
				"'".$this->contingencia."'".
				");";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLog -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}

		$sql2 = "SELECT MAX(id) as ult_id FROM `nfe_".$this->grupo."`.`LOTE` WHERE `cnpj_emitente` = '".$this->cnpj_emitente."' and `ambiente` = '".$this->ambiente."';";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql2);
		if ($recordSet === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLote -> select max() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		 }else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			return $resultado;
		}
	}
	
	/*
	 *	@function Funчуo para obter o ultimo numero de lote inserido para cada emitente
	 *	@autor Guilherme Silva
	 */
	public function selectMaxId(){
		if(empty($this->cnpj_emitente)){
			$this->mensagemErro = " MLote -> selectMaxId() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}

		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT MAX(`id`) as ult_id FROM `nfe_".$this->grupo."`.`LOTE` WHERE cnpj_emitente = '".$this->cnpj_emitente."'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLote -> selectMaxId() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			return $resultado;
		}
	}
	
	/*
	 *	@function Funчуo para retornar lotes enviado para processamento
	 *	@autor Guilherme Silva
	 */
	public function selectProcessados(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT * FROM `nfe_".$this->grupo."`.`LOTE` WHERE status = '103' OR status = '105'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLote -> selectProcessados() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(isset($resultado)){
				return $resultado;
			}else{ return true; }
		}
	}
	
	/*
	 *	@function Funчуo para Atualizar recibo e status do lote
	 *	@autor Guilherme Silva
	 */
	public function update(){
		if($this->cnpj_emitente == "" || $this->ambiente == "" || $this->id == "" || ( $this->recibo == "" && $this->status == "" )){
			$this->mensagemErro = " MLote -> update() {para esta opcao parametros obrigatorios: num lote(".$this->id."), Cnpj(".$this->cnpj_emitente."), ambiente(".$this->ambiente."). E deve ser atualizado recibo(".$this->recibo.") ou status(".$this->status.")}";
			return false;
		}
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$update = "";
		if($this->recibo != ""){
			$update[] = "`recibo` = '".$this->recibo."'";
		}
		if($this->status != ""){
			$update[] = "`status` = '".$this->status."'";
		}
		$update = implode(",", $update);

		$sql = "UPDATE `nfe_".$this->grupo."`.`LOTE` SET
				".$update."
				WHERE
				`LOTE`.`id` = ".$this->id." AND
				`LOTE`.`cnpj_emitente` = '".$this->cnpj_emitente."'
				LIMIT 1 ;";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLote -> update() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
}
?>