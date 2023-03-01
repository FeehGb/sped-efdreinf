<?php
/**
 * @name      	MNotaFiscal
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela NOTA_FISCAL do Banco de Dados
 * @TODO 		Testear Classe
*/
	require_once("MBd.php");
/**
 * Classe MNotaFiscal
 */

class MNotaFiscal{
	/*
     *	 Atributos (campos) da tabela Nota_Fiscal
     */
	public $cnpj_emitente;
	public $numero_nota;
	public $serie_nota;
	public $ambiente;
	public $cod_empresa_filial_softdib;
	public $nome_emissor;
	public $cnpj_destinatario;
	public $nome_destinatario;
	public $cod_destinatario;
	public $email_destinatario;
	public $status;
	public $tipo_emissao;
	public $data_emissao;
	public $uf_webservice;
	public $layout_danfe;
	public $tipo_operacao;
	public $danfe_impressa;
	public $lote_nfe;
	public $observacao;
	public $chave;
	public $email_enviado;
	public $numero_protocolo;
	public $xml;
	public $periodo_ini;
	public $periodo_fim;

	public $notaInicial;
	public $notaFinal;

	/*
     *	 Atributos locais para comunicação com banco de dados
     */
	private $ponteiro 		= "";
	public  $mensagemErro 	= "";
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Função para Consultar o primeiro e ultimo valor da NF referente a determinado emitente ambiente e série
	 *	@autor Guilherme Silva
	 */
	public function selectNFMinMax(){
		if(trim($this->serie_nota) == "" || trim($this->cnpj_emitente) == "" || trim($this->ambiente) == ""){
			$this->mensagemErro = " MNotaFiscal -> selectNFMinMax() {para esta opcao parametros obrigatorios: Serie Nota, Cnpj emitente, Ambiente} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT MIN( `numero_nota` ) as min, MAX( `numero_nota` ) as max
				FROM `nfe_".$this->grupo."`.`NOTA_FISCAL`
				WHERE `serie_nota` = '".$this->serie_nota."'
				AND `cnpj_emitente` = '".$this->cnpj_emitente."'
				AND `ambiente` = '".$this->ambiente."'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> selectNFMinMax() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	 *	@function Função para Consultar apenas os campos nota e Status da Nota Fiscal
	 *	@autor Guilherme Silva
	 */
	public function selectNFStatus(){
		if(trim($this->serie_nota) == "" || trim($this->cnpj_emitente) == "" || trim($this->ambiente) == "" || trim($this->notaInicial) == "" || trim($this->notaFinal) == ""){
			$this->mensagemErro = " MNotaFiscal -> selectNFStatus() {para esta opcao parametros obrigatorios: Serie Nota, Cnpj emitente, Ambiente, Nota Inicial, Nota Final} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$sql = "SELECT `numero_nota`, `status` 
				FROM `nfe_".$this->grupo."`.`NOTA_FISCAL`
				WHERE `serie_nota` = '".$this->serie_nota."'
				AND `cnpj_emitente` = '".$this->cnpj_emitente."'
				AND `ambiente` = '".$this->ambiente."'
				AND `numero_nota` BETWEEN '".$this->notaInicial."' AND '".$this->notaFinal."'";
				
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> selectNFStatus() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	 *	@function Função para Consultar todas as notas fiscais da base de acordo com o parametro informado
	 *	@autor Guilherme Silva
	 */
	public function selectAllMestre($pSql=""){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT 	cnpj_emitente,
						numero_nota,
						serie_nota,
						ambiente,
						cod_empresa_filial_softdib,
						nome_emissor,
						cnpj_destinatario,
						nome_destinatario,
						cod_destinatario,
						email_destinatario,
						status,
						tipo_emissao,
						data_emissao,
						uf_webservice,
						layout_danfe,
						valor_total_nfe,
						data_entrada_saida,
						chave,
						numero_protocolo,
						tipo_operacao,
						danfe_impressa,
						email_enviado,
						lote_nfe,
						observacao,
						xml
						FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` ";
		
		$where = "";
		
		if($this->cnpj_emitente != ""){ 			$where[] = "cnpj_emitente = '".$this->cnpj_emitente."'";	}
		if($this->numero_nota != ""){ 				$where[] = "numero_nota = '".$this->numero_nota."'";	}
		if($this->serie_nota != ""){ 				$where[] = "serie_nota = '".$this->serie_nota."'";	}
		if($this->ambiente != ""){ 					$where[] = "ambiente = '".$this->ambiente."'";	}
		if($this->cod_empresa_filial_softdib != ""){$where[] = "cod_empresa_filial_softdib = '".$this->cod_empresa_filial_softdib."'";	}
		if($this->nome_emissor != ""){ 				$where[] = "nome_emissor LIKE '".$this->nome_emissor."'";	}
		if($this->cnpj_destinatario != ""){ 		$where[] = "cnpj_destinatario = '".$this->cnpj_destinatario."'";	}
		if($this->nome_destinatario != ""){ 		$where[] = "nome_destinatario LIKE '".$this->nome_destinatario."'";	}
		if($this->cod_destinatario != ""){ 			$where[] = "cod_destinatario = '".$this->cod_destinatario."'";	}
			if(is_array($this->status)){
				foreach($this->status as $valor){ $where2[] = "status = '".$valor."'"; }
				$where[] = "(".implode(" OR ", $where2).")";
			}else{
				if($this->status != ""){ 			$where[] = "status = '".$this->status."'";	}
			}
		if($this->tipo_emissao != ""){ 				$where[] = "tipo_emissao = '".$this->tipo_emissao."'";	}
		if($this->data_emissao != ""){ 				$where[] = "data_emissao = '".date("Y-m-d",$this->data_emissao)."'"; }
		if($this->uf_webservice != ""){ 			$where[] = "uf_webservice = '".strtoupper($this->uf)."'"; }
		if($this->chave != ""){ 					$where[] = "chave = '".strtoupper($this->chave)."'"; }
		if($this->layout_danfe != ""){ 				$where[] = "layout_danfe = '".$this->layout_danfe."'";	}
		if($this->tipo_operacao != ""){ 			$where[] = "tipo_operacao = '".$this->tipo_operacao."'";	}
		if($this->email_enviado != ""){ 			$where[] = "email_enviado = '".$this->email_enviado."'";	}
		if($this->danfe_impressa != ""){ 			$where[] = "danfe_impressa = '".$this->danfe_impressa."'";	}
		if($this->lote_nfe != ""){ 					$where[] = "lote_nfe = '".$this->lote_nfe."'";	}
		if($this->observacao != ""){ 				$where[] = "observacao = '".$this->observacao."'";	}
		if($this->periodo_ini != ""){ 				$where[] = "data_emissao >= '".$this->periodo_ini."'";	}
		if($this->periodo_fim != ""){ 				$where[] = "data_emissao <= '".$this->periodo_fim."'";	}

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		$sql .= " ORDER BY data_emissao DESC, numero_nota DESC";

		if($pSql!=""){
			$sql = $pSql;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> selectAllMestre() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			$resultado;
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(@is_array($resultado)){
				return $resultado;
			}else{ return true; }
		}
	}
	
	/*
	 *	@function Função para Consultar todas as notas fiscais da base de acordo com o parametro informado
	 *	@autor Guilherme Silva
	 */
	public function selectAllMestreNXml($pSql=""){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT 	cnpj_emitente,
						numero_nota,
						serie_nota,
						ambiente,
						cod_empresa_filial_softdib,
						nome_emissor,
						cnpj_destinatario,
						nome_destinatario,
						cod_destinatario,
						status,
						tipo_emissao,
						data_emissao,
						uf_webservice,
						layout_danfe,
						valor_total_nfe,
						data_entrada_saida,
						chave,
						numero_protocolo,
						tipo_operacao,
						danfe_impressa,
						email_enviado,
						lote_nfe,
						observacao
						FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` ";
		
		$where = "";
		
		if($this->cnpj_emitente != ""){ 			$where[] = "cnpj_emitente = '".$this->cnpj_emitente."'";	}
		if($this->numero_nota != ""){ 				$where[] = "numero_nota = '".$this->numero_nota."'";	}
		if($this->serie_nota != ""){ 				$where[] = "serie_nota = '".$this->serie_nota."'";	}
		if($this->ambiente != ""){ 					$where[] = "ambiente = '".$this->ambiente."'";	}
		if($this->cod_empresa_filial_softdib != ""){$where[] = "cod_empresa_filial_softdib = '".$this->cod_empresa_filial_softdib."'";	}
		if($this->nome_emissor != ""){ 				$where[] = "nome_emissor LIKE '".$this->nome_emissor."'";	}
		if($this->cnpj_destinatario != ""){ 		$where[] = "cnpj_destinatario = '".$this->cnpj_destinatario."'";	}
		if($this->nome_destinatario != ""){ 		$where[] = "nome_destinatario LIKE '".$this->nome_destinatario."'";	}
		if($this->cod_destinatario != ""){ 			$where[] = "cod_destinatario = '".$this->cod_destinatario."'";	}
			if(is_array($this->status)){
				foreach($this->status as $valor){ $where2[] = "status = '".$valor."'"; }
				$where[] = "(".implode(" OR ", $where2).")";
			}else{
				if($this->status != ""){ 			$where[] = "status = '".$this->status."'";	}
			}
		if($this->tipo_emissao != ""){ 				$where[] = "tipo_emissao = '".$this->tipo_emissao."'";	}
		if($this->data_emissao != ""){ 				$where[] = "data_emissao = '".date("Y-m-d",$this->data_emissao)."'"; }
		if($this->uf_webservice != ""){ 			$where[] = "uf_webservice = '".strtoupper($this->uf)."'"; }
		if($this->chave != ""){ 					$where[] = "chave = '".strtoupper($this->chave)."'"; }
		if($this->layout_danfe != ""){ 				$where[] = "layout_danfe = '".$this->layout_danfe."'";	}
		if($this->tipo_operacao != ""){ 			$where[] = "tipo_operacao = '".$this->tipo_operacao."'";	}
		if($this->email_enviado != ""){ 			$where[] = "email_enviado = '".$this->email_enviado."'";	}
		if($this->danfe_impressa != ""){ 			$where[] = "danfe_impressa = '".$this->danfe_impressa."'";	}
		if($this->lote_nfe != ""){ 					$where[] = "lote_nfe = '".$this->lote_nfe."'";	}
		if($this->observacao != ""){ 				$where[] = "observacao = '".$this->observacao."'";	}
		if($this->periodo_ini != ""){ 				$where[] = "data_emissao >= '".$this->periodo_ini."'";	}
		if($this->periodo_fim != ""){ 				$where[] = "data_emissao <= '".$this->periodo_fim."'";	}

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		$sql .= " ORDER BY data_emissao DESC, numero_nota DESC";

		if($pSql!=""){
			$sql = $pSql;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> selectAllMestre() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			$resultado;
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(@is_array($resultado)){
				return $resultado;
			}else{ return true; }
		}
	}
	
	/*
	 *	@function Função para Consultar todas as notas fiscais que foram recebidas e nao foram processadas.
	 *	@autor Guilherme Silva
	 */
	public function selectRecebidas(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT 	cnpj_emitente,
						numero_nota,
						serie_nota,
						ambiente,
						status,
						versao,
						tipo_emissao,
						chave,
						xml,
						uf_webservice
						FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` WHERE `status` = '01'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> selectRecebidas() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$resultado = "";
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}

			return $resultado;
		}
	}
	
	public function update(){
		$fieldUpdate = "";
		
		// Este comando faz o update apenas dos campos abaixo
		if($this->ambiente != ""){ 			$fieldUpdate[] = "ambiente = '".$this->ambiente."'";					}
		if($this->status != ""){ 			$fieldUpdate[] = "status = '".$this->status."'";						}
		if($this->tipo_emissao != ""){ 		$fieldUpdate[] = "tipo_emissao = '".$this->tipo_emissao."'";			}
		if($this->layout_danfe != ""){ 		$fieldUpdate[] = "layout_danfe = '".$this->layout_danfe."'";			}
		if($this->lote_nfe != ""){ 			$fieldUpdate[] = "lote_nfe = '".$this->lote_nfe."'";					}
		if($this->xml != ""){ 				$fieldUpdate[] = "xml = '".base64_encode($this->xml)."'";				}
		if($this->chave != ""){ 			$fieldUpdate[] = "chave = '".$this->chave."'";							}
		if($this->danfe_impressa != ""){ 	$fieldUpdate[] = "danfe_impressa = '".$this->danfe_impressa."'";		}
		if($this->email_enviado != ""){ 	$fieldUpdate[] = "email_enviado = '".$this->email_enviado."'";			}
		if($this->numero_protocolo != ""){ 	$fieldUpdate[] = "numero_protocolo = '".$this->numero_protocolo."'";	}

		if($fieldUpdate == ""){
			$this->mensagemErro = "Nenhum campo atualizado na tabela de Nota Fiscal";
			return false;
		}

		$fieldUpdate = implode(",", $fieldUpdate);

		$CBd = MBd::singleton($this->grupo);

		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		if($this->chave != ""){
		$sql = "UPDATE `nfe_".$this->grupo."`.`NOTA_FISCAL` SET ".$fieldUpdate.
				"WHERE".
					"`chave` = '".$this->chave."';";
		}else{
			$sql = "UPDATE `nfe_".$this->grupo."`.`NOTA_FISCAL` SET ".$fieldUpdate.
					"WHERE".
						"`cnpj_emitente` = '".$this->cnpj_emitente."' AND ".
						"`numero_nota` = '".$this->numero_nota."' AND ".
						"`serie_nota` = '".$this->serie_nota."' AND ".
						"`ambiente` = '".$this->ambiente."';";
		}
				
		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> update() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}
	
		/*
	 *	@function Função para Deletar notas fiscais da base
	 *	@autor Guilherme Silva
	 */
	public function delete(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "DELETE FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` WHERE cnpj_emitente 	= '".$this->cnpj_emitente."' AND
												numero_nota 	= '".$this->numero_nota."' AND
												serie_nota 		= '".$this->serie_nota."' AND
												ambiente 		= '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> delete() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}

	}
	
	
	/*
	 *	@function Função para Inserir registro da nota fiscal
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
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`NOTA_FISCAL` (
				`cnpj_emitente` ,
				`numero_nota` ,
				`serie_nota` ,
				`ambiente` ,
				`versao` ,
				`cod_empresa_filial_softdib` ,
				`nome_emissor` ,
				`cnpj_destinatario` ,
				`nome_destinatario` ,
				`cod_destinatario` ,
				`email_destinatario` ,
				`status` ,
				`tipo_emissao` ,
				`data_emissao` ,
				`uf_webservice` ,
				`layout_danfe` ,
				`valor_total_nfe` ,
				`data_entrada_saida` ,
				`chave` ,
				`numero_protocolo` ,
				`tipo_operacao` ,
				`xml` ,
				`danfe_impressa` ,
				`email_enviado` ,
				`lote_nfe` ,
				`observacao` ,
				`CONTRIBUINTE_cnpj`
				)
				VALUES (".
				"'".$this->cnpj_emitente."',".
				"'".$this->numero_nota."',".
				"'".$this->serie_nota."',".
				"'".$this->ambiente."',".
				"'".$this->versao."',".
				"'".$this->cod_empresa_filial_softdib."',".
				"'".$this->nome_emissor."',".
				"'".$this->cnpj_destinatario."',".
				"'".addslashes($this->nome_destinatario)."',".
				"'".$this->cod_destinatario."',".
				"'".addslashes($this->email_destinatario)."',".
				"'".$this->status."',".
				"'".$this->tipo_emissao."',".
				"'".$this->data_emissao."',".
				"'".$this->uf_webservice."',".
				"'".addslashes($this->layout_danfe)."',".
				"'".$this->valor_total_nfe."',".
				"'".$this->data_entrada_saida."',".
				"'".$this->chave."',".
				"'".$this->numero_protocolo."',".
				"'".$this->tipo_operacao."',".
				"'".base64_encode($this->xml)."',".
				"'".$this->danfe_impressa."',".
				"'".$this->email_enviado."',".
				"'".$this->lote_nfe."',".
				"'".addslashes($this->observacao)."',".
				"'".$this->CONTRIBUINTE_cnpj."'".
				")";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MNotaFiscal -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
}
?>