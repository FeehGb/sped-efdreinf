<?php
/**
 * @name      	MEvento
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
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
	public $cnpj;
	public $ambiente;
	public $numero;
	public $serie;
	public $numero_sequencia;
	public $tipo_evento;
	public $xml_env;
	public $xml_ret;
	public $xml;
	public $descricao;
	public $protocolo;
	public $data_hora;
	public $status;

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
		$sql = "SELECT 	cnpj
						ambiente,
						numero,
						serie,
						numero_sequencia,
						tipo_evento,
						xml_env,
						xml_ret,
						xml,
						descricao,
						protocolo,
						data_hora,
						status
						FROM `mdfe_".$this->grupo."`.`EVENTO` ";
						
		$where = "";
		if($this->cnpj != ""){				$where[] = "cnpj = '".$this->cnpj."'";	}
		if($this->ambiente != ""){			$where[] = "ambiente = '".$this->ambiente."'";	}
		if($this->numero != ""){			$where[] = "numero = '".$this->numero."'";	}
		if($this->serie != ""){				$where[] = "serie = '".$this->serie."'";	}
		if($this->numero_sequencia != ""){	$where[] = "numero_sequencia = '".$this->numero_sequencia."'";	}
		if($this->tipo_evento != ""){		$where[] = "tipo_evento = '".$this->tipo_evento."'";	}
		if($this->protocolo != ""){			$where[] = "protocolo = '".$this->protocolo."'";	}
		if($this->data_hora != ""){			$where[] = "data_hora = '".$this->data_hora."'";	}
		if($this->status != ""){			$where[] = "status = '".$this->status."'";	}

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
			$this->ponteiro->RollbackTrans();
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
				FROM `mdfe_".$this->grupo."`.`EVENTO`
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."'
				AND `numero` = '".$this->numero."'
				AND `serie` = '".$this->serie."'
				AND `tipo_evento` = '".$this->tipo_evento."'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
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
		
		$sql = "INSERT INTO `mdfe_".$this->grupo."`.`EVENTO` (	`cnpj`,
																`ambiente`,
																`numero`,
																`serie`,
																`numero_sequencia`,
																`tipo_evento`,
																`xml_env`,
																`xml_ret`,
																`xml`,
																`descricao`,
																`protocolo`,
																`data_hora`,
																`status`) VALUES
									   ('".$this->cnpj."',
										'".$this->ambiente."',
										'".$this->numero."',
										'".$this->serie."',
										'".$this->numero_sequencia."',
										'".$this->tipo_evento."',
										'".base64_encode($this->xml_env)."',
										'".base64_encode($this->xml_ret)."',
										'".base64_encode($this->xml)."',
										'".$this->descricao."',
										'".$this->protocolo."',
										'".$this->data_hora."',
										'".$this->status."') ON DUPLICATE KEY UPDATE
										`cnpj` = '".$this->cnpj."',
										`ambiente` = '".$this->ambiente."',
										`numero` = '".$this->numero."',
										`serie` = '".$this->serie."',
										`tipo_evento` = '".$this->tipo_evento."',
										`numero_sequencia` = '".$this->numero_sequencia."',
										`xml_env` = '".base64_encode($this->xml_env)."',
										`xml_ret` = '".base64_encode($this->xml_ret)."',
										`xml` 	  = '".base64_encode($this->xml)."',
										`descricao` = '".$this->descricao."',
										`protocolo` = '".$this->protocolo."',
										`data_hora` = '".$this->data_hora."',
										`status` = '".$this->status."'";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MEvento -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}
}
?>