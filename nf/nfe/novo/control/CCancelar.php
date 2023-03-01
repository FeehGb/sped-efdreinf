<?php
/**
 * @name      	CCancelar
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para efetuar o Cancelamento
 * @TODO 		Fazer tudo
*/

/**
 * @import 
 */

 require_once("/var/www/html/nf/nfe/novo/libs/ToolsNFePHP.class.php");
 require_once("/var/www/html/nf/nfe/novo/control/CIntegracaoERP.php");
 require_once("/var/www/html/nf/nfe/novo/model/MNotaFiscal.php");
 require_once("/var/www/html/nf/nfe/novo/model/MEvento.php");
 require_once("/var/www/html/nf/nfe/novo/model/MCritica.php");
 require_once("/var/www/html/nf/nfe/novo/model/MContribuinte.php");
 require_once("/var/www/html/nf/nfe/novo/model/MLote.php");
 require_once("/var/www/html/nf/nfe/novo/model/MLog.php");
 require_once("CBackup.php");
/**
 * @class CCancelar
 */ 
class CCancelar{

/*
 * Atributos da Classe
 */
	public $cnpjEmitente;
	public $ambiente;
	public $numeroNota;
	public $serieNota;
	public $chave;
	public $protocolo;
	public $justificativa;
	public $contingencia;
	
	public $statusLote;
	public $motivoLote;
	
	public $usuario;
	public $modelo="55";
 	
	public $mensagemErro = "";

	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}

/**
 * @class CWebService
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
 
	public function mCancelarNota(){
		$MEvento 		= new MEvento($this->grupo);
		$MLote 			= new MLote($this->grupo);
		$MContribuinte	= new MContribuinte($this->grupo);
		$MNotaFiscal	= new MNotaFiscal($this->grupo);
		$MCritica 		= new MCritica($this->grupo);
		$MLog 			= new MLog($this->grupo);

		// Seleciona as informa��es do Contribuinte
		$MContribuinte->cnpj 		= $this->cnpjEmitente;
		$MContribuinte->ambiente 	= $this->ambiente;
		$returnContribuinte 		= $MContribuinte->selectCNPJAmbiente();

		if(!$returnContribuinte){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}

		// Seleciona as informa��es da Nota Fiscal
		$MNotaFiscal->cnpj_emitente	= $this->cnpjEmitente;
		$MNotaFiscal->numero_nota	= $this->numeroNota;
		$MNotaFiscal->serie_nota	= $this->serieNota;
		$MNotaFiscal->ambiente		= $this->ambiente;
		
		$returnNF = $MNotaFiscal->selectAllMestre();

		if(!$returnNF){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}

		// Verifica se a Nota Fiscal ja foi Cancelada (com status de cancelamento)
		if($returnNF[0]['status'] == "06"){
			$this->mensagemErro = "CCancelar -> Esta nota nao pode ser cancelada pois jah encontra-se com status de cancelamento";
			return false;
		}
		
		// Verifica se a Nota Fiscal est� com status de autorizada
		if($returnNF[0]['status'] == "03" && $returnNF[0]['numero_protocolo'] != NULL){
			// continue;
		}else{
			// Permite ir para o cancelamento notas fiscais que est�o com status 02 (Aguardando Sefaz) e ativada Contigencia 03 (SCAN)
			if($returnNF[0]['status'] == "02" && $returnContribuinte[0]['contigencia'] == "03"){
				//continue;
			}else{
				$this->mensagemErro = "CCancelar -> Esta nota nao esta habilitada para Cancelamento";
				return false;
			}
		}

		// Gerar Nova Numera��o de Lote
		// Cadastrar novo Lote na base de dados
		$MLote = new MLote($this->grupo); 
		
		$MLote->cnpj_emitente = $this->cnpjEmitente;
		$MLote->versao = "";
		$MLote->recibo = "";
		$MLote->status = "";
		$MLote->ambiente = $this->ambiente;
		$MLote->contingencia =  "";

		$returnLote = $MLote->insert();
		if(!$returnLote || $returnLote == null){
			$this->mensagemErro = $MLote->mensagemErro;
			return false;
		}
		$lote = $returnLote[0]['ult_id'];

		// IR CANCELAR
		//Selecionar o tipo de contingencia
		switch($this->contingencia){
			case "03":
				$ToolsNFePHP = new ToolsNFePHP($this->cnpjEmitente, $this->ambiente,$this->grupo, 2,false,true);
				if(!$ToolsNFePHP){
					return false;
				}
			break;
			case "06":
			case "07":
				$ToolsNFePHP = new ToolsNFePHP($this->cnpjEmitente, $this->ambiente, $this->grupo,2,false,"SVC");
				if(!$ToolsNFePHP){
					return false;
				}
			break;
			default:
				$ToolsNFePHP = new ToolsNFePHP($this->cnpjEmitente, $this->ambiente, $this->grupo);
				if(!$ToolsNFePHP){
					return false;
				}
			break;
		}

		$returnTools = $ToolsNFePHP->cancelEvent($this->chave, $returnNF[0]['numero_protocolo'], $this->justificativa, $lote, $this->ambiente, $retorno);
		$this->statusLote	= $retorno['cStat']; // ver se retornar isso
		$this->motivoLote 	= $retorno['xMotivo']; // ver se retorna estes

		$this->__mAtualizarLote();

		if(!$returnTools){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->cnpjEmitente;
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->ambiente;
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->numeroNota;
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->serieNota;
			$MCritica->codigo_referencia				= $aRetorno['cStat'];
			$MCritica->descricao		                = $this->mensagemErro = $ToolsNFePHP->errMsg;
			$MCritica->insert();
			return false;
		}

		// MONTA EVENTO NA BASE DE DADOS PARA CANCELAMENTO
		$MEvento->NOTA_FISCAL_cnpj_emitente		= $this->cnpjEmitente;
		$MEvento->NOTA_FISCAL_numero_nota		= $this->numeroNota;
		$MEvento->NOTA_FISCAL_serie_nota		= $this->serieNota;
		$MEvento->NOTA_FISCAL_ambiente			= $this->ambiente;
		$MEvento->tipo_evento					= "4";
		$MEvento->numero_sequencia				= "";
		$MEvento->xml_env						= $retorno['xml_env'];
		$MEvento->xml_ret						= $retorno['xml_ret'];
		$MEvento->xml							= $retorno['xml'];
		$MEvento->descricao						= $retorno['xMotivoEvento'];
		$MEvento->protocolo						= $retorno['nProt'];
		$MEvento->data_hora						= $retorno['dhReceb'];
		$MEvento->status						= $retorno['cStatEvento'];
		$MEvento->email_enviado					= "N";
		
		$retornoEvento = $MEvento->insert();
		
		if(!$retornoEvento){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->$cnpjEmitente;
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->ambiente;
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->numeroNota;
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->serieNota;
			$MCritica->codigo_referencia				= $retorno['cStatEvento'];
			$MCritica->descricao		                = $this->mensagemErro = $MEvento->mensagemRetorno;
			$MCritica->insert();
			return false;
		}

		// Atualizar Nota Fiscal para 6 - Status cancelada
		$MNotaFiscal->cnpj_emitente = $this->cnpjEmitente;
		$MNotaFiscal->numero_nota	= $this->numeroNota;
		$MNotaFiscal->serie_nota	= $this->serieNota;
		$MNotaFiscal->ambiente		= $this->ambiente;
		$MNotaFiscal->status		= "06";
		$MNotaFiscal->update();

		// Integrar a resposta com o ERP
		// Gerar arquivo de Integra��o com COBOL
		$CIntegracaoERP = new CIntegracaoERP($this->grupo);

		$arrayIntegracao['cnpj_emitente'] 		= $this->cnpjEmitente;
		$arrayIntegracao['uf_ibge_emitente'] 	= $retorno['cOrgao'];
		$arrayIntegracao['ano_mes'] 			= date('ym');
		$arrayIntegracao['modelo_nota'] 		= $this->modelo;
		$arrayIntegracao['serie_nota'] 			= $this->serieNota;
		$arrayIntegracao['numero_nota'] 		= $this->numeroNota;
		$arrayIntegracao['serie_nota_con'] 		= $this->serieNota;
		$arrayIntegracao['numero_nota_con']		= $this->numeroNota;
		$arrayIntegracao['status'] 				= "2";
		$arrayIntegracao['descricao_status'] 	= $retorno['xMotivoEvento'];
		$arrayIntegracao['data_hora'] 			= str_replace("/","-",$retorno['dhRecbto']);
		$arrayIntegracao['protocolo'] 			= $retorno['nProt'];
		$arrayIntegracao['uf_ibge_responsavel'] = $retorno['cOrgao'];
		$CIntegracaoERP->contribuinteBase 		= $returnContribuinte[0]['diretorio_base'];

		$retornoERP = $CIntegracaoERP->mRetornoCancelamento($returnContribuinte[0]['diretorio_integracao'],str_replace("/","-",$retorno['dhRecbto']),$arrayIntegracao);

		if(!$retornoERP){
			$this->mensagemErro = $CIntegracaoERP->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		// Gravar arquivo backup de nota autorizada
		$CBackup = new CBackup($this->grupo);
		$retBkp = $CBackup->mGuardarXml($retorno['xml'],$this->chave, $this->cnpjEmitente, 'canc');
		if(!$retBkp){
			echo $retBkp->mensagemErro;
		}

		// Instanciar Classe de LOG
		$MLog->NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
		$MLog->NOTA_FISCAL_numero_nota		= $this->numeroNota;
		$MLog->NOTA_FISCAL_serie_nota		= $this->serieNota;
		$MLog->NOTA_FISCAL_ambiente			= $this->ambiente;
//		$MLog->data_hora					= $this-> $data_hora;
		$MLog->evento						= "CANCELAMENTO";
		$MLog->usuario						= $this->usuario; // Obter o lk-usuario;
		$MLog->descricao = "Cancelamento efetuado com Sucesso CNPJ: ".$this->cnpjEmitente.", AMBIENTE: ".$this->ambiente.", SERIE: ".$this->serieNota.", NF: ".$this->numeroNota.", JUSTIFICATIVA: ".$this->justificativa." ";
		$MLog->insert();

		// Verificar se envia mail automaticamente

		$this->mensagemErro = "Cancelamento Efetuado com Sucesso!";
		return true;
	}
	
	private function __mAtualizarLote(){
		// Atualizar Lote na base de dados
		$MLote = new MLote($this->grupo); 
		
		$MLote->cnpj_emitente = $this->NOTA_FISCAL_cnpj_emitente;
		$MLote->ambiente = $this->NOTA_FISCAL_ambiente;
		$MLote->recibo = "";
		$MLote->status = $this->statusLote;

		$return = $MLote->update();
		if(!$return || $return == null){
			$this->mensagemErro = $MLote->mensagemErro;
			return false;
		}
	}
	
	
}
?>