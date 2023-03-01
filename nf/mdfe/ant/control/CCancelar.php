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

 require_once(__ROOT__."/libs/MDFeNFePHP.class.php");
 require_once(__ROOT__."/control/CIntegracaoERP.php");
 require_once(__ROOT__."/model/MMDFe.php");
 require_once(__ROOT__."/model/MEvento.php");
 require_once(__ROOT__."/model/MCritica.php");
 require_once(__ROOT__."/model/MContribuinte.php");
 require_once(__ROOT__."/model/MLote.php");
 require_once(__ROOT__."/model/MLog.php");
 require_once("CBackup.php");
/**
 * @class CCancelar
 */ 
class CCancelar{

/*
 * Atributos da Classe
 */
	public $cnpj;
	public $ambiente;
	public $numero;
	public $serie;
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
 
	public function mCancelarMDFe(){
		$MEvento 		= new MEvento($this->grupo);
		$MLote 			= new MLote($this->grupo);
		$MContribuinte	= new MContribuinte($this->grupo);
		$MMDFe			= new MMDFe($this->grupo);
		$MCritica 		= new MCritica($this->grupo);
		$MLog 			= new MLog($this->grupo);

		// Seleciona as informaчѕes do Contribuinte
		$MContribuinte->cnpj 		= $this->cnpj;
		$MContribuinte->ambiente 	= $this->ambiente;
		$returnContribuinte 		= $MContribuinte->selectCNPJAmbiente();

		if(!$returnContribuinte){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}

		// Seleciona as informaчѕes da Nota Fiscal
		$MMDFe->cnpj		= $this->cnpj;
		$MMDFe->numero		= $this->numero;
		$MMDFe->serie		= $this->serie;
		$MMDFe->ambiente	= $this->ambiente;
		
		$returnMDFe = $MMDFe->selectAllMestre();

		if(!$returnMDFe){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}

		// Verifica se a Nota Fiscal ja foi Cancelada (com status de cancelamento)
		if($returnMDFe[0]['status'] == "06"){
			$this->mensagemErro = "CCancelar -> Esta nota nao pode ser cancelada pois jah encontra-se com status de cancelamento";
			return false;
		}
		
		// Verifica se a Nota Fiscal estс com status de autorizada
		if($returnMDFe[0]['status'] == "03" && $returnMDFe[0]['numero_protocolo'] != NULL){
			// continue;
		}else{
			// Permite ir para o cancelamento notas fiscais que estуo com status 02 (Aguardando Sefaz) e ativada Contigencia 03 (SCAN)
			if($returnMDFe[0]['status'] == "02" && $returnContribuinte[0]['contigencia'] == "03"){
				continue;
			}else{
				$this->mensagemErro = "CCancelar -> Esta nota nao esta habilitada para Cancelamento";
				return false;
			}
		}

		// IR CANCELAR
		//Selecionar o tipo de contingencia
		/*switch($this->contingencia){
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
		}*/
		
		$MDFeNFePHP = new MDFeNFePHP($returnContribuinte[0]);
		if(!$MDFeNFePHP){
			$this->mensagemErro = $MDFeNFePHP->errMsg;
			return false;
		}
		$returnTools = $MDFeNFePHP->manifDest($returnMDFe[0]['chave'],'110111',$this->ambiente,$this->justificativa,'2',&$aRetorno, $returnMDFe[0]['numero_protocolo']);
		if(!$returnTools){
			$MCritica = new MCritica($this->grupo);
			$MCritica->cnpj					= $this->cnpjEmitente;
			$MCritica->numero				= $this->ambiente;
			$MCritica->serie				= $this->numeroNota;
			$MCritica->ambiente				= $this->serieNota;
			$MCritica->codigo_referencia	= $aRetorno['cStat'];
			$MCritica->descricao		    = $this->mensagemErro = $MDFeNFePHP->errMsg;
			$MCritica->insert();
			return false;
		}

		// MONTA EVENTO NA BASE DE DADOS PARA CANCELAMENTO
		$MEvento->cnpj				= $this->cnpj;
		$MEvento->numero			= $this->numero;
		$MEvento->serie				= $this->serie;
		$MEvento->ambiente			= $this->ambiente;
		$MEvento->tipo_evento		= "110111";
		$MEvento->numero_sequencia	= "";
		$MEvento->xml_env			= $aRetorno['xml_env'];
		$MEvento->xml_ret			= $aRetorno['xml_ret'];
		$MEvento->xml				= $aRetorno['xml'];
		$MEvento->descricao			= $aRetorno['xMotivo'];
		$MEvento->protocolo			= $aRetorno['nProt'];
		$MEvento->data_hora			= $aRetorno['dhReceb'];
		$MEvento->status			= $aRetorno['cStat'];
		$MEvento->email_enviado		= "N";
		
		$retornoEvento = $MEvento->insert();
		
		if(!$retornoEvento){
			$MCritica = new MCritica($this->grupo);
			$MCritica->cnpj					= $this->cnpj;
			$MCritica->numero				= $this->ambiente;
			$MCritica->serie				= $this->numero;
			$MCritica->ambiente				= $this->serie;
			$MCritica->codigo_referencia	= $aRetorno['cStat'];
			$MCritica->descricao		    = $this->mensagemErro = $MEvento->mensagemErro;
			$MCritica->insert();
			return false;
		}

		// Atualizar Nota Fiscal para 6 - Status cancelada
		$MMDFe->cnpj 		= $this->cnpj;
		$MMDFe->numero 		= $this->numero;
		$MMDFe->serie		= $this->serie;
		$MMDFe->ambiente	= $this->ambiente;
		$MMDFe->status		= "06";
		$MMDFe->update();

		// Integrar a resposta com o ERP
		// Gerar arquivo de Integraчуo com COBOL
		$CIntegracaoERP = new CIntegracaoERP($this->grupo);

		$arrayIntegracao['cnpj_emitente'] 		= $this->cnpj;
		$arrayIntegracao['uf_ibge_emitente'] 	= $MDFeNFePHP->cUF;
		$arrayIntegracao['ano_mes'] 			= date('ym');
		$arrayIntegracao['modelo_nota'] 		= $this->modelo;
		$arrayIntegracao['serie_nota'] 			= $this->serie;
		$arrayIntegracao['numero_nota'] 		= $this->numero;
		$arrayIntegracao['status'] 				= "2";
		$arrayIntegracao['descricao_status'] 	= $aRetorno['xMotivo'];
		$arrayIntegracao['data_hora'] 			= $aRetorno['dhReceb'];
		$arrayIntegracao['protocolo'] 			= $aRetorno['nProt'];
		$CIntegracaoERP->contribuinteBase 		= $returnContribuinte[0]['diretorio_base'];

		$retorno = $CIntegracaoERP->mRetornoCancelamento($returnContribuinte[0]['diretorio_integracao'],$aRetorno['dhReceb'],$arrayIntegracao);

		if(!$retorno){
			$this->mensagemErro = $CIntegracaoERP->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		// Gravar arquivo backup de nota autorizada
		/*$CBackup = new CBackup($this->grupo);
		$retBkp = $CBackup->mGuardarXml($aRetorno['xml'],$returnMDFe[0]['chave'], $this->cnpj, 'canc');
		if(!$retBkp){
			echo $retBkp->mensagemErro;
		}*/

		// Instanciar Classe de LOG
		$MLog->cnpj	= $this->cnpj;
		$MLog->numero		= $this->numero;
		$MLog->serir		= $this->serie;
		$MLog->ambiente		= $this->ambiente;
//		$MLog->data_hora	= $this-> $data_hora;
		$MLog->evento		= "CANCELAMENTO";
		$MLog->usuario		= $this->usuario; // Obter o lk-usuario;
		$MLog->descricao 	= "Cancelamento efetuado com Sucesso CNPJ: ".$this->cnpj.", AMBIENTE: ".$this->ambiente.", SERIE: ".$this->serie.", NF: ".$this->numero.", JUSTIFICATIVA: ".$this->justificativa." ";
		$MLog->insert();

		// Verificar se envia mail automaticamente

		$this->mensagemErro = "Cancelamento Efetuado com Sucesso!";
		return true;
	}

}
?>