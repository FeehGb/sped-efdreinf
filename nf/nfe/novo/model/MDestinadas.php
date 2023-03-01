<?php
/**
 * @name      	MDestinadas
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela NF_DESTINADAS do Banco de Dados
 * @TODO 		Testear Classe
*/

	require_once("MBd.php");
/**
 * Classe MDestinadas
 */

class MDestinadas{
	/*
     *	 Atributos (campos)
     */
	public $nsu;
	public $ambiente;
	public $tipo;
	public $chave;
	public $emit_cpf_cnpj;
	public $emit_nome;
	public $emit_ie;
	public $dest_cpf_cnpj;
	public $data_emissao;
	public $tipo_nota;
	public $valor_nf;
	public $digest_value;
	public $data_hora_recebimento;
	public $situacao_nfe;
	public $confirmacao;
	public $data_hora_confirmacao;
	public $protocolo_confirmacao;
	public $data_hora_evento;
	public $tp_evento;
	public $seq_evento;
	public $desc_evento;
	public $correcao;

	/*
     *	 Atributos locais para comunicação com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Função para Consultar o primeiro e ultimo valor da NF referente a determinado emitente ambiente e série
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
		$sql = "SELECT * FROM `nfe_".$this->grupo."`.`NF_DESTINADAS` ";
		
		$where = "";
		if($this->nsu					 != ""){		$where[] = "nsu					 = '".$this->nsu					."'"; 		}
		if($this->ambiente				 != ""){		$where[] = "ambiente			 = '".$this->ambiente				."'"; 		}
		if($this->tipo					 != ""){		$where[] = "tipo				 = '".$this->tipo					."'"; 		}
		if($this->chave					 != ""){		$where[] = "chave				 = '".$this->chave					."'"; 		}
		if($this->emit_cpf_cnpj			 != ""){		$where[] = "emit_cpf_cnpj		 = '".$this->emit_cpf_cnpj			."'"; 		}
		if($this->emit_nome				 != ""){		$where[] = "emit_nome			 = '".$this->emit_nome				."'"; 		}
		if($this->emit_ie				 != ""){		$where[] = "emit_ie				 = '".$this->emit_ie				."'"; 		}
		if($this->dest_cpf_cnpj			 != ""){		$where[] = "dest_cpf_cnpj		 = '".$this->dest_cpf_cnpj			."'"; 		}
		if($this->data_emissao			 != ""){		$where[] = "data_emissao		 = '".$this->data_emissao			."'"; 		}
		if($this->tipo_nota				 != ""){		$where[] = "tipo_nota			 = '".$this->tipo_nota				."'"; 		}
		if($this->valor_nf				 != ""){		$where[] = "valor_nf			 = '".$this->valor_nf				."'"; 		}
		if($this->digest_value			 != ""){		$where[] = "digest_value		 = '".$this->digest_value			."'"; 		}
		if($this->data_hora_recebimento	 != ""){		$where[] = "data_hora_recebimento= '".$this->data_hora_recebimento	."'"; 		} 
		if($this->situacao_nfe			 != ""){		$where[] = "situacao_nfe		 = '".$this->situacao_nfe			."'"; 		}
		if($this->confirmacao			 != ""){		$where[] = "confirmacao			 = '".$this->confirmacao			."'"; 		}	
		if($this->data_hora_confirmacao	 != ""){		$where[] = "data_hora_confirmacao = '".$this->data_hora_confirmacao			."'"; 		}	
		if($this->protocolo_confirmacao	 != ""){		$where[] = "protocolo_confirmacao = '".$this->protocolo_confirmacao			."'"; 		}	
		if($this->data_hora_evento		 != ""){		$where[] = "data_hora_evento	 = '".$this->data_hora_evento		."'"; 		}		
		if($this->tp_evento				 != ""){		$where[] = "tp_evento			 = '".$this->tp_evento				."'"; 		}	
		if($this->seq_evento			 != ""){		$where[] = "seq_evento			 = '".$this->seq_evento			 	."'"; 		}	
		if($this->desc_evento			 != ""){		$where[] = "desc_evento			 = '".$this->desc_evento			."'"; 		}	
		if($this->correcao				 != ""){		$where[] = "correcao			 = '".$this->correcao				."'"; 		}	

		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		if(trim($pSql) != ""){
			$sql = $pSql/*." ORDER BY data_emissao"*/;
		}
		
		$sql = $sql." ORDER BY nsu DESC";

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MDestinadas -> select() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$resultado;
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(is_array($resultado)){
				return $resultado;
			}else{
				return true;
			}
		}
	}

	/*
	 *	@function Função para Inserir registro de destinadas no sisltema
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
		$sql = "INSERT INTO `nfe_".$this->grupo."`.`NF_DESTINADAS` (
				`nsu` ,
				`ambiente` ,
				`tipo` ,
				`chave` ,
				`emit_cpf_cnpj` ,
				`emit_nome` ,
				`emit_ie` ,
				`dest_cpf_cnpj` ,
				`data_emissao` ,
				`tipo_nota` ,
				`valor_nf` ,
				`digest_value` ,
				`data_hora_recebimento` ,
				`situacao_nfe` ,
				`confirmacao` ,
				`data_hora_confirmacao` ,
				`protocolo_confirmacao` ,
				`data_hora_evento` ,
				`tp_evento` ,
				`seq_evento` ,
				`desc_evento` ,
				`correcao` 
				)
				VALUES (".
				"'".$this->nsu."',".
				"'".$this->ambiente."',".
				"'".$this->tipo."',".
				"'".$this->chave."',".
				"'".$this->emit_cpf_cnpj."',".
				"'".str_replace("'","`",$this->emit_nome)."',".
				"'".$this->emit_ie."',".
				"'".$this->dest_cpf_cnpj."',".
				"'".$this->data_emissao."',".
				"'".$this->tipo_nota."',".
				"'".$this->valor_nf."',".
				"'".$this->digest_value."',".
				"'".$this->data_hora_recebimento."',".
				"'".$this->situacao_nfe."',".
				"'".$this->confirmacao."',".
				"'".$this->data_hora_confirmacao."',".
				"'".$this->protocolo_confirmacao."',".
				"'".$this->data_hora_evento."',".
				"'".$this->tp_evento."',".
				"'".$this->seq_evento."',".
				"'".$this->desc_evento."',".
				"'".$this->correcao."'".
				") ON DUPLICATE KEY UPDATE
				  `nsu` 						= '".$this->nsu."',
				  `ambiente` 					= '".$this->ambiente."',
				  `tipo` 						= '".$this->tipo."',
				  `chave`						= '".$this->chave."',
				  `emit_cpf_cnpj` 				= '".$this->emit_cpf_cnpj."',
				  `emit_nome` 					= '".str_replace("'","`",$this->emit_nome)."',
				  `emit_ie` 					= '".$this->emit_ie."',
				  `dest_cpf_cnpj` 				= '".$this->dest_cpf_cnpj."',
				  `data_emissao` 				= '".$this->data_emissao."',
				  `tipo_nota` 					= '".$this->tipo_nota."',
				  `valor_nf` 					= '".$this->valor_nf."',
				  `digest_value` 				= '".$this->digest_value."',
				  `data_hora_recebimento` 		= '".$this->data_hora_recebimento."',
				  `situacao_nfe` 				= '".$this->situacao_nfe."',
				  `confirmacao` 				= '".$this->confirmacao."',
				  `data_hora_confirmacao` 		= '".$this->data_hora_confirmacao."',
				  `protocolo_confirmacao` 		= '".$this->protocolo_confirmacao."',
				  `data_hora_evento` 			= '".$this->data_hora_evento."',
				  `tp_evento` 					= '".$this->tp_evento."',
				  `seq_evento`	 				= '".$this->seq_evento."',
				  `desc_evento` 				= '".$this->desc_evento."',
				  `correcao` 					= '".$this->correcao."'";
		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MDestinadas -> insert() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Função para Confirmacao
	 *	@autor Guilherme Silva
	 */
	public function update(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "UPDATE `nfe_".$this->grupo."`.`NF_DESTINADAS` SET
				confirmacao			= ".$this->confirmacao.",
				data_hora_confirmacao = '".date('Y-m-d H:i:s', strtotime($this->data_hora_confirmacao))."',
				protocolo_confirmacao = '".$this->protocolo_confirmacao."' WHERE
				chave = '".$this->chave."'";
				  
		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MDestinadas -> update() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}

}
?>
