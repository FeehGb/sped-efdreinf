<?php
/**
 * @name      	MEvento
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela EVENTO do Banco de Dados
 * @TODO 		Testear Classe
*/

	require_once("MBd.php");
/**
 * Classe MNotaFiscal
 */

class MEvento{
	/*
     *	 Atributos (campos) da tabela Evento
     */
	public $NOTA_FISCAL_cnpj_emitente;
	public $NOTA_FISCAL_numero_nota;
	public $NOTA_FISCAL_serie_nota;
	public $NOTA_FISCAL_ambiente;
	public $tipo_evento;
	public $numero_sequencia;
	public $xml_env;
	public $xml_ret;
	public $xml;
	public $descricao;
	public $protocolo;
	public $data_hora;
	public $status;
	public $email_enviado;

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
	 *	@function Funчуo para Consultar todos os eventos de acordo com os parametros informados
	 *	@autor Guilherme Silva
	 */
	public function selectMestre($pSql=""){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT 	NOTA_FISCAL_cnpj_emitente,
						NOTA_FISCAL_numero_nota,
						NOTA_FISCAL_serie_nota,
						NOTA_FISCAL_ambiente,
						tipo_evento,
						numero_sequencia,
						descricao,
						protocolo,
						data_hora,
						status,
						email_enviado, 
						xml,
						xml_env
						FROM `nfe_".$this->grupo."`.`EVENTO` ";
						
		$where = "";
		
		if($this->NOTA_FISCAL_cnpj_emitente != ""){	$where[] = "NOTA_FISCAL_cnpj_emitente = '".$this->NOTA_FISCAL_cnpj_emitente."'";	}
		if($this->NOTA_FISCAL_numero_nota != ""){ 	$where[] = "NOTA_FISCAL_numero_nota = '".$this->NOTA_FISCAL_numero_nota."'";	}
		if($this->NOTA_FISCAL_serie_nota != ""){ 	$where[] = "NOTA_FISCAL_serie_nota = '".$this->NOTA_FISCAL_serie_nota."'";	}
		if($this->NOTA_FISCAL_ambiente != ""){ 		$where[] = "NOTA_FISCAL_ambiente = '".$this->NOTA_FISCAL_ambiente."'";	}
		if($this->tipo_evento != ""){				$where[] = "tipo_evento = '".$this->tipo_evento."'";	}
		if($this->numero_sequencia != ""){ 			$where[] = "numero_sequencia = '".$this->numero_sequencia."'";	}
		if($this->protocolo != "" && $this->protocolo != "NULL"){
													$where[] = "protocolo = '".$this->protocolo."'";	}
		if($this->protocolo == "NULL"){ 			$where[] = "protocolo IS NULL";	}
		
		if($this->status != ""){ 					$where[] = "status = '".$this->status."'";	}
		
		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		if($pSql != ""){
			$sql = $pSql;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MEvento -> selectMestre() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(isset($resultado)){
				return $resultado;
			}else{
				return true;
			}
		}
	}
	
	/*
	 *	@function Funчуo para Consultar a ultima sequencia do evento
	 *	@autor Guilherme Silva
	 */
	public function selectMaxSeq(){
		if($this->NOTA_FISCAL_cnpj_emitente == "" || $this->NOTA_FISCAL_numero_nota == "" || $this->NOTA_FISCAL_serie_nota == "" || $this->NOTA_FISCAL_ambiente == "" || $this->tipo_evento == ""){
			$this->mensagemErro = " MEvento -> selectMaxSeq() -> para esta opcao parametros obrigatorios: Cnpj, ambiente, nota, serie, tipo de evento ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT Max(numero_sequencia) as ult_sequencia
				FROM `nfe_".$this->grupo."`.`EVENTO`
				WHERE `NOTA_FISCAL_cnpj_emitente` = '".$this->NOTA_FISCAL_cnpj_emitente."'
				AND `NOTA_FISCAL_numero_nota` = '".$this->NOTA_FISCAL_numero_nota."'
				AND `NOTA_FISCAL_serie_nota` = '".$this->NOTA_FISCAL_serie_nota."'
				AND `NOTA_FISCAL_ambiente` = '".$this->NOTA_FISCAL_ambiente."'
				AND `tipo_evento` = '".$this->tipo_evento."'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MEvento -> selectMaxSeq() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	 *	@function Funчуo para Inserir novo Evento na base
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
		
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`EVENTO` (	`NOTA_FISCAL_cnpj_emitente`,
										`NOTA_FISCAL_numero_nota`,
										`NOTA_FISCAL_serie_nota`,
										`NOTA_FISCAL_ambiente`,
										`tipo_evento`,
										`numero_sequencia`,
										`xml_env`,
										`xml_ret`,
										`xml`,
										`descricao`,
										`protocolo`,
										`data_hora`,
										`status`,
										`email_enviado`) VALUES
									   ('".$this->NOTA_FISCAL_cnpj_emitente."',
										'".$this->NOTA_FISCAL_numero_nota."',
										'".$this->NOTA_FISCAL_serie_nota."',
										'".$this->NOTA_FISCAL_ambiente."',
										'".addslashes($this->tipo_evento)."',
										'".$this->numero_sequencia."',
										'".base64_encode($this->xml_env)."',
										'".base64_encode($this->xml_ret)."',
										'".base64_encode($this->xml)."',
										'".addslashes($this->descricao)."',
										'".$this->protocolo."',
										'".$this->data_hora."',
										'".$this->status."',
										'".$this->email_enviado."') ON DUPLICATE KEY UPDATE
										`NOTA_FISCAL_cnpj_emitente` = '".$this->NOTA_FISCAL_cnpj_emitente."',
										`NOTA_FISCAL_numero_nota` = '".$this->NOTA_FISCAL_numero_nota."',
										`NOTA_FISCAL_serie_nota` = '".$this->NOTA_FISCAL_serie_nota."',
										`NOTA_FISCAL_ambiente` = '".$this->NOTA_FISCAL_ambiente."',
										`tipo_evento` = '".addslashes($this->tipo_evento)."',
										`numero_sequencia` = '".$this->numero_sequencia."',
										`xml_env` = '".base64_encode($this->xml_env)."',
										`xml_ret` = '".base64_encode($this->xml_ret)."',
										`xml` 	  = '".base64_encode($this->xml)."',
										`descricao` = '".addslashes($this->descricao)."',
										`protocolo` = '".$this->protocolo."',
										`data_hora` = '".$this->data_hora."',
										`status` = '".$this->status."',
										`email_enviado` = '".$this->email_enviado."'";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MEvento -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}
}
?>