<?php
/**
 * @name      	MInutilizacao
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela INUTILIZACAO do Banco de Dados
 * @TODO 		Testear Classe
*/

	require_once("MBd.php");
/**
 * Classe MInutilizacao
 */

class MInutilizacao{
	/*
     *	 Atributos (campos)
     */
	public $CONTRIBUINTE_cnpj;
	public $CONTRIBUINTE_ambiente;
	public $serie_nota;
	public $numero_nota_inicial;
	public $numero_nota_final;
	public $ano;
	public $justificativa;
	public $xml_env;
	public $xml_ret;
	public $xml;
	public $modelo_nota="55";
	public $protocolo;
	public $data_hora;
	public $status;
	public $status_motivo;
	public $uf_responsavel;
	public $periodo_ini;
	public $periodo_fim;

	/*
     *	 Atributos locais para comunica��o com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Fun��o para Consultar o primeiro e ultimo valor da NF referente a determinado emitente ambiente e s�rie
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
		$sql = "SELECT CONTRIBUINTE_cnpj, CONTRIBUINTE_ambiente, serie_nota, numero_nota_inicial, numero_nota_final, ano, justificativa, modelo_nota, protocolo, data_hora, status, uf_responsavel FROM `nfe_".$this->grupo."`.`INUTILIZACAO` ";
		
		$where = "";
		if($this->CONTRIBUINTE_cnpj != ""){		$where[] = "CONTRIBUINTE_cnpj = '".$this->CONTRIBUINTE_cnpj."'"; 		}
		if($this->CONTRIBUINTE_ambiente != ""){	$where[] = "CONTRIBUINTE_ambiente = '".$this->CONTRIBUINTE_ambiente."'";	}
		if($this->serie_nota != ""){ 			$where[] = "serie_nota = '".$this->serie_nota."'";							}
		if($this->numero_nota_inicial != ""){ 	$where[] = "numero_nota_inicial = '".$this->numero_nota_inicial."'";		}
		if($this->numero_nota_final != ""){ 	$where[] = "numero_nota_final = '".$this->numero_nota_final."'";		}
		if($this->ano != ""){ 					$where[] = "ano = '".$this->ano."'";		}
		if($this->modelo_nota != ""){ 			$where[] = "modelo_nota = '".$this->modelo_nota."'";		}
		if($this->protocolo != ""){ 			$where[] = "protocolo = ".$this->protocolo."";		}
		if($this->status != ""){ 				$where[] = "status = '".$this->status."'";		}
		if($this->status_motivo != ""){ 		$where[] = "status_motivo = '".$this->status_motivo."'";		}
		if($this->uf_responsavel != ""){ 		$where[] = "uf_responsavel = '".$this->uf_responsavel."'";		}
		if($this->periodo_ini != ""){ 			$where[] = "data_hora >= '".$this->periodo_ini."'";		}
		if($this->periodo_fim != ""){ 			$where[] = "data_hora <= '".$this->periodo_fim."'";		}

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		if(trim($pSql) != ""){
			$sql = $pSql;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MInutilizacao -> select() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$resultado;
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			return $resultado;
		}
	}

	/*
	 *	@function Fun��o para Inserir registro de inutiliza��o no sisltema
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
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`INUTILIZACAO` (
				`CONTRIBUINTE_cnpj` ,
				`CONTRIBUINTE_ambiente` ,
				`serie_nota` ,
				`numero_nota_inicial` ,
				`numero_nota_final` ,
				`ano` ,
				`justificativa` ,
				`xml_env` ,
				`xml_ret` ,
                                `xml` ,
				`modelo_nota` ,
				`protocolo` ,
				`data_hora` ,
				`status` ,
				`status_motivo` ,
				`uf_responsavel`
				)
				VALUES (".
				"'".$this->CONTRIBUINTE_cnpj."',".
				"'".$this->CONTRIBUINTE_ambiente."',".
				"'".$this->serie_nota."',".
				"'".$this->numero_nota_inicial."',".
				"'".$this->numero_nota_final."',".
				"'".$this->ano."',".
				"'".$this->justificativa."',".
				"'".base64_encode($this->xml_env)."',".
				"'".base64_encode($this->xml_ret)."',".
                                "'".base64_encode($this->xml)."',".
				"'".$this->modelo_nota."',".
				"'".$this->protocolo."',".
				"'".$this->data_hora."',".
				"'".$this->status."',".
				"'".$this->status_motivo."',".
				"'".$this->uf_responsavel."'".
				") ON DUPLICATE KEY UPDATE
				`CONTRIBUINTE_cnpj`		='".$this->CONTRIBUINTE_cnpj."',
				`CONTRIBUINTE_ambiente`	='".$this->CONTRIBUINTE_ambiente."',
				`serie_nota`			='".$this->serie_nota."',
				`numero_nota_inicial`	='".$this->numero_nota_inicial."',
				`numero_nota_final`		='".$this->numero_nota_final."',
				`ano`					='".$this->ano."',
				`justificativa`			='".$this->justificativa."',
				`xml_env`				='".base64_encode($this->xml_env)."',
				`xml_ret`				='".base64_encode($this->xml_ret)."',
                                `xml`				='".base64_encode($this->xml)."',
				`modelo_nota`			='".$this->modelo_nota."',
				`protocolo`				='".$this->protocolo."',
				`data_hora`				='".$this->data_hora."',
				`status`				='".$this->status."',
				`status_motivo`			='".$this->status_motivo."',
				`uf_responsavel`		='".$this->uf_responsavel."'";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MInutilizacao -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	* Fun��o para verificar se existe a Nota Inutilizada do range de notas informadas
	*/
	public function verificaNotaInut($pNota=""){
		if($this->CONTRIBUINTE_cnpj == "" || $this->CONTRIBUINTE_ambiente == "" || $this->serie_nota == "" || $pNota == ""){
			$this->mensagemErro = " MInutilizacao -> verificaNotaInut() { CNPJ, Ambiente, Serie e Numero Nota s�o campos obrigat�rios } ";
			return false;
		}
		
		$retorno = $this->select();
		if(!$retorno){
			return false;
		}
		
		foreach($retorno as $conteudo){

			if($pNota >= $conteudo['numero_nota_inicial'] && $pNota <= $conteudo['numero_nota_final']){
				return true;
			}else{
				$this->mensagemErro = "";
				return false;
			}
		}
	}

}
?>
