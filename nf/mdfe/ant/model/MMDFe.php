<?php
/**
 * @name      	MMDFe
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela MDFE do Banco de Dados
 * @TODO 		Testear Classe
*/

	require_once("MBd.php");
/**
 * Classe MMDFe
 */

class MMDFe{
	/*
     *	 Atributos (campos) da tabela MDFE
     */
	public  $cnpj;
	public  $ambiente;
	public  $id_lote;
	public  $numero;
	public  $serie;
	public  $versao;
	public  $tipo_emitente;
	public  $cod_empresa_filial_softdib;
	public  $nome_emissor;
	public  $uf_carregamento;
	public  $uf_descarregamento;
	public  $status;
	public  $tipo_emissao;
	public  $data_emissao;
	public  $valor_total_carga;
	public  $quantidade_nfe;
	public  $unidade_peso_bruto;
	public  $peso_bruto;
	public  $chave;
	public  $numero_protocolo;
	public  $xml_envio;
	public  $xml_retorno;
	public  $xml;
	public  $damdfe_impressa;
	
	public $numeroInicial;
	public $numeroFinal;

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
	 *	@function Funчуo para Consultar o primeiro e ultimo valor da NF referente a determinado emitente ambiente e sщrie
	 *	@autor Guilherme Silva
	 */
	public function selectNFMinMax(){
		$CBd = MBd::singleton($this->grupo);

		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT MIN( `numero` ) as min, MAX( `numero` ) as max
				FROM `mdfe_".$this->grupo."`.`MDFE`
				WHERE `serie` = '".$this->serie."'
				AND `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."'";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> selectNFMinMax() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	 *	@function Funчуo para Consultar apenas os campos mdfe e Status da MDFE
	 *	@autor Guilherme Silva
	 */
	public function selectNFStatus(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$sql = "SELECT `numero`, `status` 
				FROM `mdfe_".$this->grupo."`.`MDFE`
				WHERE `serie` = '".$this->serie."'
				AND `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."'
				AND `numero` BETWEEN '".$this->numeroInicial."' AND '".$this->numeroFinal."'";
				
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> selectNFStatus() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	 *	@function Funчуo para Consultar todas as MDFes da base de acordo com o parametro informado
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
		$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa,
						xml
						FROM `mdfe_".$this->grupo."`.`MDFE` ";
		
		$where = "";
		
		if($this->cnpj != ""){ 							$where[] = "cnpj = '".$this->cnpj."'";	}
		if($this->ambiente != ""){ 						$where[] = "ambiente = '".$this->ambiente."'";	}
		if($this->id_lote != ""){ 						$where[] = "id_lote = '".$this->id_lote."'";	}
		if($this->numero != ""){ 						$where[] = "numero = '".$this->numero."'";	}
		if($this->serie != ""){ 						$where[] = "serie = '".$this->serie."'";	}
		if($this->versao != ""){ 						$where[] = "versao = '".$this->versao."'";	}
		if($this->tipo_emitente != ""){ 				$where[] = "tipo_emitente = '".$this->tipo_emitente."'";	}
		if($this->cod_empresa_filial_softdib != ""){ 	$where[] = "cod_empresa_filial_softdib = '".$this->cod_empresa_filial_softdib."'";	}
		if($this->uf_carregamento != ""){ 				$where[] = "uf_carregamento = '".$this->uf_carregamento."'";	}
		if($this->uf_descarregamento != ""){ 			$where[] = "uf_descarregamento = '".$this->uf_descarregamento."'";	}
		if($this->status != ""){ 					$where[] = "status = '".$this->status."'";	}
		if($this->tipo_emissao != ""){ 					$where[] = "tipo_emissao = '".$this->tipo_emissao."'";	}
		if($this->data_emissao != ""){ 					$where[] = "data_emissao = '".$this->data_emissao."'";	}
		if($this->valor_total_carga != ""){ 			$where[] = "valor_total_carga = '".$this->valor_total_carga."'";	}
		if($this->quantidade_nfe != ""){ 				$where[] = "quantidade_nfe = '".$this->quantidade_nfe."'";	}
		if($this->chave != ""){ 						$where[] = "chave = '".$this->chave."'";	}
		if($this->numero_protocolo != ""){ 				$where[] = "numero_protocolo = '".$this->numero_protocolo."'";	}
		if($this->damdfe_impressa != ""){ 				$where[] = "damdfe_impressa = '".$this->damdfe_impressa."'";	}

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		$sql .= " ORDER BY data_emissao DESC, numero DESC";

		if($pSql!=""){
			$sql = $pSql;
		}
		
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> selectAllMestre() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	
	/*
	 *	@function Funчуo para Consultar todas as mdfe que foram recebidas e nao foram processadas.
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

		$sql = "SELECT 	cnpj,
						numero,
						serie,
						ambiente,
						status,
						versao,
						tipo_emissao,
						chave,
						xml
						FROM `mdfe_".$this->grupo."`.`MDFE` WHERE `status` = '01'";
						

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> selectRecebidas() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
		
		if($this->versao != ""){ 					$fieldUpdate[] = "versao = '".$this->versao."'";					}
		if($this->id_lote != ""){ 					$fieldUpdate[] = "id_lote = '".$this->id_lote."'";					}
		if($this->tipo_emitente != ""){ 			$fieldUpdate[] = "tipo_emitente = '".$this->tipo_emitente."'";					}
		if($this->cod_empresa_filial_softdib != ""){$fieldUpdate[] = "cod_empresa_filial_softdib = '".$this->cod_empresa_filial_softdib."'";					}
		if($this->nome_emissor != ""){ 				$fieldUpdate[] = "nome_emissor = '".$this->nome_emissor."'";					}
		if($this->uf_carregamento != ""){ 			$fieldUpdate[] = "uf_carregamento = '".$this->uf_carregamento."'";					}
		if($this->uf_descarregamento != ""){ 		$fieldUpdate[] = "uf_descarregamento = '".$this->uf_descarregamento."'";					}
		if($this->status != ""){ 					$fieldUpdate[] = "status = '".$this->status."'";					}
		if($this->tipo_emissao != ""){ 				$fieldUpdate[] = "tipo_emissao = '".$this->tipo_emissao."'";					}
		if($this->data_emissao != ""){ 				$fieldUpdate[] = "data_emissao = '".$this->data_emissao."'";					}
		if($this->valor_total_carga != ""){ 		$fieldUpdate[] = "valor_total_carga = '".$this->valor_total_carga."'";					}
		if($this->quantidade_nfe != ""){ 			$fieldUpdate[] = "quantidade_nfe = '".$this->quantidade_nfe."'";					}
		if($this->unidade_peso_bruto != ""){ 		$fieldUpdate[] = "unidade_peso_bruto = '".$this->unidade_peso_bruto."'";					}
		if($this->peso_bruto != ""){ 				$fieldUpdate[] = "peso_bruto = '".$this->peso_bruto."'";					}
		if($this->chave != ""){ 					$fieldUpdate[] = "chave = '".$this->chave."'";					}
		if($this->numero_protocolo != ""){ 			$fieldUpdate[] = "numero_protocolo = '".$this->numero_protocolo."'";					}
		if($this->xml_envio != ""){ 				$fieldUpdate[] = "xml_envio = '".base64_encode($this->xml_envio)."'";					}
		if($this->xml_retorno != ""){ 				$fieldUpdate[] = "xml_retorno = '".base64_encode($this->xml_retorno)."'";					}
		if($this->xml != ""){ 						$fieldUpdate[] = "xml = '".base64_encode($this->xml)."'";					}
		if($this->damdfe_impressa != ""){ 			$fieldUpdate[] = "damdfe_impressa = '".$this->damdfe_impressa."'";					}

		if($fieldUpdate == ""){
			$this->mensagemErro = "Nenhum campo atualizado na tabela MDFE";
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
		
		$sql = "UPDATE `mdfe_".$this->grupo."`.`MDFE` SET ".$fieldUpdate.
				"WHERE".
					"`cnpj` = '".$this->cnpj."' AND ".
					"`numero` = '".$this->numero."' AND ".
					"`serie` = '".$this->serie."' AND ".
					"`ambiente` = '".$this->ambiente."';";
				
		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> update() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}
	
		/*
	 *	@function Funчуo para Deletar MDFes da base
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

		$sql = "DELETE FROM `mdfe_".$this->grupo."`.`MDFE` WHERE cnpj 	= '".$this->cnpj."' AND
												numero 			= '".$this->numero."' AND
												serie 			= '".$this->serie."' AND
												ambiente 		= '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> delete() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}

	}

	/*
	 *	@function Funчуo para Inserir registro da MDFe
	 *	@autor Guilherme Silva
	 */
	public function insert(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		if($this->xml_envio != ""){ $this->xml_envio = base64_encode($this->xml_envio); }
		if($this->xml_retorno != ""){ $this->xml_retorno = base64_encode($this->xml_retorno); }
		if($this->xml != ""){ $this->xml = 	base64_encode($this->xml); }

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "INSERT INTO `mdfe_".$this->grupo."`.`MDFE` (
				`cnpj` ,
				`ambiente` ,
				`id_lote` ,
				`numero` ,
				`serie` ,
				`versao` ,
				`tipo_emitente` ,
				`cod_empresa_filial_softdib` ,
				`nome_emissor` ,
				`uf_carregamento` ,
				`uf_descarregamento` ,
				`status` ,
				`tipo_emissao` ,
				`data_emissao` ,
				`valor_total_carga` ,
				`quantidade_nfe` ,
				`unidade_peso_bruto` ,
				`peso_bruto` ,
				`chave` ,
				`numero_protocolo` ,
				`xml_envio` ,
				`xml_retorno` ,
				`xml` ,
				`damdfe_impressa`
				)
				VALUES (".
				"'".$this->cnpj."',".
				"'".$this->ambiente."',".
				"'".$this->id_lote."',".
				"'".$this->numero."',".
				"'".$this->serie."',".
				"'".$this->versao."',".
				"'".$this->tipo_emitente."',".
				"'".$this->cod_empresa_filial_softdib."',".
				"'".$this->nome_emissor."',".
				"'".$this->uf_carregamento."',".
				"'".$this->uf_descarregamento."',".
				"'".$this->status."',".
				"'".$this->tipo_emissao."',".
				"'".$this->data_emissao."',".
				"'".$this->valor_total_carga."',".
				"'".$this->quantidade_nfe."',".
				"'".$this->unidade_peso_bruto."',".
				"'".$this->peso_bruto."',".
				"'".$this->chave."',".
				"'".$this->numero_protocolo."',".
				"'".$this->xml_envio."',".
				"'".$this->xml_retorno."',".
				"'".$this->xml."',".
				"'".$this->damdfe_impressa."'".
				")";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MMDFe -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
}
?>