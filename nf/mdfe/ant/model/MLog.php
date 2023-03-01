<?php
/**
 * @name      	MLog
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela LOG do Banco de Dados
 * @TODO 		Testar Classe
*/
	require_once("MBd.php");
/**
 * Classe MLog
 */

class MLog{
	/*
     *	 Atributos (campos)
     */
	public $cnpj;
	public $ambiente;
	public $id_lote;
	public $numero;
	public $serie;
	public $sequencia;
	public $data_hora;
	public $evento;
	public $usuario;
	public $descricao;
	public $detalhes;

	/*
     *	 Atributos locais para comunicaчуo com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Funчуo para Inserir registro de LOG no sistema
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
		$sql = "INSERT INTO `mdfe_".$this->grupo."`.`LOG` (
				`cnpj` ,
				`ambiente` ,
				`id_lote` ,
				`numero` ,
				`serie` ,
				`sequencia` ,
				`data_hora` ,
				`evento` ,
				`usuario` ,
				`descricao` ,
				`detalhes`
				)
				VALUES (".
				"'".$this->cnpj."',".
				"'".$this->ambiente."',".
				"'".$this->id_lote."',".
				"'".$this->numero."',".
				"'".$this->serie."',";
		if($this->sequencia == ""){
				$sql .=	"NULL,";
		}else{
				$sql .=	"'".$this->sequencia."',";
		}
		$sql .=	"'".$this->data_hora."',".
				"'".$this->evento."',".
				"'".$this->usuario."',".
				"'".$this->descricao."',".
				"'".$this->detalhes."'".
				")";
file_put_contents("/home/guilherme/saida.sql",$sql);
		if ($this->ponteiro->Execute($sql) === false) {
		echo "erro na insercao: ".$this->ponteiro->ErrorMsg();
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLog -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funчуo para Consultar todas os logs conforme parametro informado
	 *	@autor Guilherme Silva
	 */
	public function select($pSql=""){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT 	cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						sequencia,
						data_hora,
						evento,
						usuario,
						descricao,
						detalhes
						FROM `mdfe_".$this->grupo."`.`LOG` ";
		
		$where = "";
		if($this->cnpj != ""){ 		$where[] = "cnpj = '".$this->cnpj."'";	}
		if($this->ambiente != ""){ 	$where[] = "ambiente = '".$this->ambiente."'";	}
		if($this->id_lote != ""){ 	$where[] = "id_lote = '".$this->id_lote."'";	}
		if($this->numero != ""){ 	$where[] = "numero = '".$this->numero."'";	}
		if($this->serie != ""){ 	$where[] = "serie = '".$this->serie."'";	}
		if($this->sequencia != ""){ $where[] = "sequencia = '".$this->sequencia."'";	}

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		$sql .= " ORDER BY data_hora DESC, numero DESC";

		if($pSql!=""){
			$sql = $pSql;
		}
		
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLog -> select() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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