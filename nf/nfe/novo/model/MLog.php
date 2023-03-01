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
	public $NOTA_FISCAL_cnpj_emitente;
	public $NOTA_FISCAL_numero_nota;
	public $NOTA_FISCAL_serie_nota;
	public $NOTA_FISCAL_ambiente;
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
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`LOG` (
				`NOTA_FISCAL_cnpj_emitente` ,
				`NOTA_FISCAL_numero_nota` ,
				`NOTA_FISCAL_serie_nota` ,
				`NOTA_FISCAL_ambiente` ,
				`sequencia` ,
				`data_hora` ,
				`evento` ,
				`usuario` ,
				`descricao` ,
				`detalhes`
				)
				VALUES (".
				"'".$this->NOTA_FISCAL_cnpj_emitente."',".
				"'".$this->NOTA_FISCAL_numero_nota."',".
				"'".$this->NOTA_FISCAL_serie_nota."',".
				"'".$this->NOTA_FISCAL_ambiente."',";
		if($this->sequencia == ""){
				$sql =	"NULL,";
		}else{
				$sql =	"'".$this->sequencia."',";
		}
		$sql =	"'".$this->data_hora."',".
				"'".addslashes($this->evento)."',".
				"'".addslashes($this->usuario)."',".
				"'".addslashes($this->descricao)."',".
				"'".addslashes($this->detalhes)."'".
				")";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MLog -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}

}
?>