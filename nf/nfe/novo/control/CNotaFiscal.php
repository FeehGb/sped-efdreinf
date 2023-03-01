<?php
/**
 * @name      	CNotaFiscal
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer a manutenчуo do cadastro de Notas Fiscais
 * @TODO 		Fazer tudo
*/

/**
 * @import Importaчуo de Classes de comunicaчуo
 */ 
 require_once("../model/MNotaFiscal.php");
require_once("../model/MEvento.php"); 
 require_once("../model/MContribuinte.php");
 require_once("CIntegracaoERP.php");

/**
 * @class CWebSerice
 */ 
class CNotaFiscal{

/*
 * Atributos da Classe
 */	

	public $cnpj_emitente;
	public $numero_nota;
	public $serie_nota;
	public $ambiente;
	public $status_nota;
	public $motivo_nota;
	public $cod_empresa_filial_softdib;
	public $nome_emissor;
	public $cnpj_destinatario;
	public $nome_destinatario;
	public $cod_destinatario;
	public $status;
	public $tipo_emissao;
	public $uf_webservice;
	public $layout_danfe;
	public $valor_total_nfe;
	public $tipo_operacao;
	public $periodo_ini;
	public $periodo_fim;
	public $motivo;
	public $nRecibo;
	public $xmlProtocolo;
	public $chave_nota;
	public $dhRecebimento;
	public $uf_nota;
	public $numProtocolo;
	public $modelo_nota="55";
	public $uf_emitente;

	public $mensagemErro = "";

	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
/**
 * @method mObterLista
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mObterLista(){
		// Instancia Classe Model Nota Fiscal
		$MNF = new MNotaFiscal($this->grupo);
		$MEvento = new MEvento($this->grupo);

		// Passagem de parametros para consulta
		$MNF->cnpj_emitente					= $this->cnpj_emitente;
		$MNF->numero_nota					= $this->numero_nota;
		$MNF->serie_nota					= $this->serie_nota;
		$MNF->ambiente						= $this->ambiente;
		$MNF->cod_empresa_filial_softdib	= $this->cod_empresa_filial_softdib;
		$MNF->nome_emissor					= $this->nome_emissor;
		$MNF->cnpj_destinatario				= $this->cnpj_destinatario;
		$MNF->nome_destinatario				= $this->nome_destinatario;
		$MNF->cod_destinatario				= $this->cod_destinatario;
		$MNF->status						= $this->status;
		$MNF->tipo_emissao					= $this->tipo_emissao;
		$MNF->uf_webservice					= $this->uf_webservice;
		$MNF->layout_danfe					= $this->layout_danfe;
		$MNF->tipo_operacao					= $this->tipo_operacao;
		$MNF->periodo_ini					= $this->periodo_ini;
		$MNF->periodo_fim					= $this->periodo_fim;

		// Chamada da funcao selectAll e retorno do erro
		$return = $MNF->selectAllMestre();

		if(is_array($return)){
			foreach($return as $key=>$value){
				if($return[$key]['data_emissao'] == NULL){
					$return[$key]['data_emissao'] = "";
				}else{
					$return[$key]['data_emissao'] = date("d/m/Y", strtotime($return[$key]['data_emissao']));
				}
				
				if($return[$key]['data_entrada_saida'] == NULL){
					$return[$key]['data_entrada_saida'] = "";
				}else{
					$return[$key]['valor_total_nfe'] = str_replace(".",",",$return[$key]['valor_total_nfe']);
				}

				$pSql = "SELECT count(tipo_evento) as contagem FROM `nfe_".$this->grupo."`.`EVENTO` WHERE ".
						" NOTA_FISCAL_cnpj_emitente='".$return[$key]['cnpj_emitente']."' AND ".
						" NOTA_FISCAL_numero_nota='".$return[$key]['numero_nota']."' AND ".
						" NOTA_FISCAL_serie_nota='".$return[$key]['serie_nota']."' AND ".
						" NOTA_FISCAL_ambiente='".$return[$key]['ambiente']."' AND".
						" tipo_evento = '6' ";
				$returnEvento = $MEvento->selectMestre($pSql);
				if($returnEvento){
					if($returnEvento[0]['contagem'] != "0"){
						$return[$key]['cc'] = "1";
					}
				}
				
			}
		}
		
		$this->mensagemErro = $MNF->mensagemErro;
		return $return;
	}

	
	/**
 * @method mObterListaTela
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mObterListaTela(){
		// Instancia Classe Model Nota Fiscal
		$MNF = new MNotaFiscal($this->grupo);
		$MEvento = new MEvento($this->grupo);

		// Passagem de parametros para consulta
		$MNF->cnpj_emitente					= $this->cnpj_emitente;
		$MNF->numero_nota					= $this->numero_nota;
		$MNF->serie_nota					= $this->serie_nota;
		$MNF->ambiente						= $this->ambiente;
		$MNF->cod_empresa_filial_softdib	= $this->cod_empresa_filial_softdib;
		$MNF->nome_emissor					= $this->nome_emissor;
		$MNF->cnpj_destinatario				= $this->cnpj_destinatario;
		$MNF->nome_destinatario				= $this->nome_destinatario;
		$MNF->cod_destinatario				= $this->cod_destinatario;
		$MNF->status						= $this->status;
		$MNF->tipo_emissao					= $this->tipo_emissao;
		$MNF->uf_webservice					= $this->uf_webservice;
		$MNF->layout_danfe					= $this->layout_danfe;
		$MNF->tipo_operacao					= $this->tipo_operacao;
		$MNF->periodo_ini					= $this->periodo_ini;
		$MNF->periodo_fim					= $this->periodo_fim;

		// Chamada da funcao selectAll e retorno do erro
		$return = $MNF->selectAllMestreNXml();

		if(is_array($return)){
			foreach($return as $key=>$value){
				if($return[$key]['data_emissao'] == NULL){
					$return[$key]['data_emissao'] = "";
				}else{
					$return[$key]['data_emissao'] = date("d/m/Y", strtotime($return[$key]['data_emissao']));
				}
				
				if($return[$key]['data_entrada_saida'] == NULL){
					$return[$key]['data_entrada_saida'] = "";
				}else{
					$return[$key]['valor_total_nfe'] = str_replace(".",",",$return[$key]['valor_total_nfe']);
				}

				$pSql = "SELECT count(tipo_evento) as contagem FROM `nfe_".$this->grupo."`.`EVENTO` WHERE ".
						" NOTA_FISCAL_cnpj_emitente='".$return[$key]['cnpj_emitente']."' AND ".
						" NOTA_FISCAL_numero_nota='".$return[$key]['numero_nota']."' AND ".
						" NOTA_FISCAL_serie_nota='".$return[$key]['serie_nota']."' AND ".
						" NOTA_FISCAL_ambiente='".$return[$key]['ambiente']."' AND".
						" tipo_evento = '6' ";
				$returnEvento = $MEvento->selectMestre($pSql);
				if($returnEvento){
					if($returnEvento[0]['contagem'] != "0"){
						$return[$key]['cc'] = "1";
					}
				}
				
			}
		}
		
		$this->mensagemErro = $MNF->mensagemErro;
		return $return;
	}

/**
 * @method mAtualizarNotaSefaz
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mAtualizarNotaSefaz(){
		// Instancia Classes que irс utilizar
		$MEvento 		= new MEvento($this->grupo);
		$MLote 			= new MLote($this->grupo);
		$MContribuinte	= new MContribuinte($this->grupo);
		$MNotaFiscal	= new MNotaFiscal($this->grupo);
		$MCritica 		= new MCritica($this->grupo);
		$MLog 			= new MLog($this->grupo);
		
		// Obtem a chave da nota fiscal, de acordo com os parametros de tela, para consultar no SEFAZ
		$MNotaFiscal->cnpj_emitente	= $this->cnpj_emitente;
		$MNotaFiscal->numero_nota	= $this->numero_nota;
		$MNotaFiscal->serie_nota	= $this->serie_nota;
		$MNotaFiscal->ambiente		= $this->ambiente;

		$return = $MNotaFiscal->selectAllMestre();

		if(!$return){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}

		// Verifica se a Nota Fiscal ja foi Cancelada (com status de cancelamento)
		$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente, $this->grupo);
		$retorno = $ToolsNFePHP->getProtocol3('',$return[0]['chave'],$this->ambiente,$aRetorno);

		$this->status 		 = $aRetorno['cStat'];
		$this->motivo		 = $aRetorno['xMotivo'];
		$this->status_nota	 = $aRetorno['aProt']['cStat'];
		$this->motivo_nota	 = $aRetorno['aProt']['xMotivo'];
		$this->nRecibo 		 = $aRetorno['nRec'];
		$this->xmlProtocolo	 = $aRetorno['xmlRetorno'];
		$this->chave_nota	 = $aRetorno['aProt']['chNFe'];
		//$this->dhRecebimento = str_replace("/","-",$aRetorno['aProt']['dhRecbto']);
		$this->dhRecebimento = str_replace("/","-",$aRetorno['aProt'][0]['dhRecbto']);
		$this->uf_nota		 = $aRetorno['cUF'];
		$this->numProtocolo	 = $aRetorno['aProt']['nProt'];
		$this->uf_emitente	 = $retorno['cUF'];

		if(!$retorno){
			$this->mensagemErro = $ToolsNFePHP->errMsg;
			return false;
		}

		switch($aRetorno['cStat']){
			// Autorizada
			case "100":
				// Atualizar Status da Nota
				// Adicionar Protocolo de Autorizaчуo e atualiza na base
				$xml = base64_decode($return[0]['xml']);
				if($xml = $ToolsNFePHP->addProt($xml, $this->xmlProtocolo)){
					$MNotaFiscal->xml = base64_encode($xml);
				}
				$MNotaFiscal->status 	= "03";
				$MNotaFiscal->xml 		= $xml;
				$retorno = $MNotaFiscal->update();
				if(!$retorno){
					$this->mensagemErro = $MEvento->mensagemErro;
					return false;
				}
				$this->mensagemErro = "Nota Atualizada \n Nota:".$this->numero_nota." Serie:".$this->serie_nota."\nStatus: (".$aRetorno['cStat'].") AUTORIZADA";
			break;
			// Cancelada
			case "101";
				// Criar Evento de Cancelamento
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
				$MEvento->NOTA_FISCAL_numero_nota	= $this->numero_nota;
				$MEvento->NOTA_FISCAL_serie_nota	= $this->serie_nota;
				$MEvento->NOTA_FISCAL_ambiente		= $this->ambiente;
				$MEvento->tipo_evento				= "4";
				$MEvento->numero_sequencia			= "";
			//	$MEvento->xml_env					= $ToolsNFePHP->arrayRetorno['xml_env'];
			//	$MEvento->xml_ret					= $ToolsNFePHP->arrayRetorno['xml_ret'];
				$MEvento->descricao					= $aRetorno['aEventos'][0]['detEvento']['xJust']." [VERIFICACAO DE EVENTO MANUAL]";
				$MEvento->protocolo					= $aRetorno['aEventos'][0]['detEvento']['nProt'];
				$MEvento->data_hora					= $ToolsNFePHP->arrayRetorno['dhReceb'];
				$MEvento->status					= $aRetorno['cStat'];
				$MEvento->email_enviado				= "N";
				$retorno = $MEvento->insert();
				if(!$retorno){
					$this->mensagemErro = $MEvento->mensagemErro;
					return false;
				}

				// Atualizar Status da Nota
				$MNotaFiscal->status = "06";
				$retorno = $MNotaFiscal->update();
				if(!$retorno){
					$this->mensagemErro = $MEvento->mensagemErro;
					return false;
				}
				$this->mensagemErro = "Nota Atualizada \n Nota:".$this->numero_nota." Serie:".$this->serie_nota."\nStatus: (".$aRetorno['cStat'].") CANCELADA";
			break;
			// Inutilizada
			case "102";
			break;
			// Denegada
			case "110";
			break;
		}
			

		// Gerar arquivo de integraчуo de ERP
		$CIntegracaoERP = new CIntegracaoERP($this->grupo);
		$MContribuinte->cnpj	 	= $this->cnpj_emitente;
		$MContribuinte->ambiente 	= $this->ambiente;
		$retornoContribuinte 		= $MContribuinte->selectCNPJAmbiente();

		if(!$retornoContribuinte){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}
		
		$CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
		
		// Autorizada
		if($this->status == "100"){
			$pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
			$pArray['uf_emitente'] 		= $this->uf_nota;
			$pArray['ano_mes'] 			= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
			$pArray['modelo_nota'] 		= $this->modelo_nota;
			$pArray['serie_nota'] 		= trim($this->serie_nota);
			$pArray['numero_nota'] 		= trim($this->numero_nota);
			$pArray['serie_nota_con'] 	= " ";
			$pArray['numero_nota_con']	= " ";
			$pArray['status'] 			= "6";
			$pArray['chave'] 			= $this->chave_nota;
			$retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
			
		// Denegada
		}elseif($this->status == "110"){
			$pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
			$pArray['uf_emitente'] 		= $this->uf_nota;
			$pArray['ano_mes'] 			= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
			$pArray['modelo_nota'] 		= $this->modelo_nota;
			$pArray['serie_nota'] 		= $this->serie_nota;
			$pArray['numero_nota'] 		= $this->numero_nota;
			$pArray['serie_nota_con'] 	= " ";
			$pArray['numero_nota_con']	= " ";
			$pArray['status'] 			= "7";
			$pArray['chave'] 			= $this->chave_nota;
			$retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);

		// Cancelada
		}elseif($this->status == "101"){
			$pArray['cnpj_emitente'] 		= $this->cnpj_emitente;
			$pArray['uf_ibge_emitente'] 	= $aRetorno['cUF'];
			$pArray['ano_mes'] 				= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
			$pArray['modelo_nota'] 			= $this->modelo_nota;
			$pArray['serie_nota'] 			= $this->serie_nota;
			$pArray['numero_nota'] 			= $this->numero_nota;
			$pArray['serie_nota_con'] 		= $this->serie_nota;
			$pArray['numero_nota_con']		= $this->numero_nota;
			$pArray['status'] 				= "2";
			$pArray['descricao_status'] 	= $aRetorno['xMotivo'];
			$pArray['data_hora'] 			= date(('d/m/Y H:i:s'),strtotime($aRetorno['aEventos'][0]['dhEvento']));
			$pArray['protocolo'] 			= $aRetorno['aProt']['nProt'];
			$pArray['uf_ibge_responsavel'] 	= $aRetorno['cUF'];
			$retorno = $CIntegracaoERP->mRetornoCancelamento($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
			
		// Inutilizada
		}elseif($this->status == "102"){
			$pArray['cnpj_emitente'] 		= $this->cnpj_emitente;
			$pArray['uf_emitente'] 			= $this->uf_emitente;
			$pArray['ano'] 					= substr($this->dhRecebimento,2,2);
			$pArray['modelo_nota'] 			= $this->modelo_nota;
			$pArray['serie_nota'] 			= $this->serie_nota;
			$pArray['numero_nota_inicial']	= $this->numero_nota;
			$pArray['numero_nota_final'] 	= $this->numero_nota;
			$pArray['status'] 				= "2";
			$pArray['descricao_status'] 	= $this->motivo;
			$pArray['data_hora'] 			= date('d/m/Y', strtotime($data))." ".substr($data_hora,11,8);
			$pArray['protocolo'] 			= $this->numProtocolo;
			$pArray['uf_ibge_responsavel']	= $this->uf_nota;

			$retorno = $CIntegracaoERP->mRetornoInutilizacao($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
		// Retorno Desconhecido
		}else{
			$pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
			$pArray['uf_emitente'] 		= $this->uf_nota;
			$pArray['ano_mes'] 			= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
			$pArray['modelo_nota'] 		= $this->modelo_nota;
			$pArray['serie_nota'] 		= $this->serie_nota;
			$pArray['numero_nota'] 		= $this->numero_nota;
			$pArray['serie_nota_con'] 	= " ";
			$pArray['numero_nota_con']	= " ";
			$pArray['status'] 			= "8";
			$pArray['chave'] 			= $this->chave_nota;
			$retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
		}
		
		if(!$retorno){
			$this->mensagemErro = $CIntegracaoERP->mensagemErro;
			return false;
		}
		
		return true;

/*
		// Instanciar Classe de LOG
		$MLog->NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
		$MLog->NOTA_FISCAL_numero_nota		= $this->numeroNota;
		$MLog->NOTA_FISCAL_serie_nota		= $this->serieNota;
		$MLog->NOTA_FISCAL_ambiente			= $this->ambiente;
//		$MLog->data_hora					= $this-> $data_hora;
		$MLog->evento						= "ATUALIZACAO";
		$MLog->usuario						= $this->usuario; // Obter o lk-usuario;
		$MLog->descricao = "Efetuada atualizacao do Status da Nota: ".$this->cnpjEmitente.", AMBIENTE: ".$this->ambiente.", SERIE: ".$this->serieNota.", NF: ".$this->numeroNota.", STATUS: ".VER." ";
		$MLog->insert();

*/		
	}
/*	
	public function mObterTodos(){
		// Nao ha atributos para pre verificacao

		// Instancia Classe Model Contribuinte
		$MContribuinte = new MContribuinte($this->grupo);

		// Chamada da funcao selectCNPJAmbiente e retorno do erro
		$return = $MContribuinte->selectAll();
		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}
	
	public function mAtualizarCertificado(){
		// Verifica se todos os atributos necessarios para a exclusao do certificado estao setados
		if($this->cnpj == "" || $this->ambiente == ""){
			$this->mensagemErro = " CContribuinte -> mAtualizarCertificado() { Atributos CNPJ e Tipo Ambiente obrigatorios para consulta }";
			return false;
		}

		// Instancia Classe Model Contribuinte
		$MContribuinte = new MContribuinte($this->grupo);

		// Passagem de parametros para exclusao do certificado
		$MContribuinte->cnpj = $this->cnpj;
		$MContribuinte->ambiente = $this->ambiente;
		$MContribuinte->certificado_caminho = $this->certificado_caminho;

		// Chamada da funcao selectCNPJAmbiente e retorno do erro
		$return = $MContribuinte->updateCert();
		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}

	public function mAtualizarContingencia(){
		// Verifica se todos os atributos necessarios para a atualizacao da contingencia foram setados
		if($this->cnpj == "" || $this->ambiente == "" || $this->contigencia == ""){
			$this->mensagemErro = " CContribuinte -> mAtualizarContingencia() { Atributos CNPJ e Tipo Ambiente e Contingencia obrigatorios para consulta }";
			return false;
		}

		// Instancia Classe Model Contribuinte
		$MContribuinte = new MContribuinte($this->grupo);

		// Passagem de parametros para exclusao do certificado
		$MContribuinte->cnpj = $this->cnpj;
		$MContribuinte->ambiente = $this->ambiente;
		$MContribuinte->contigencia = $this->contigencia;

		// Chamada da funcao updateContingencia e retorno do erro
		$return = $MContribuinte->updateContingencia();
		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}*/
	
	public function mObterCriticas(){
	
		$MCritica 		= new MCritica($this->grupo);

		$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente = $this->cnpj_emitente;
		$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $this->numero_nota;
		$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $this->serie_nota;
		$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $this->ambiente;
		
		$retorno = $MCritica->select();
		
		if(!$retorno){
			$this->mensagemErro = $MCritica->mensagemErro;
			return false;
		}
		
		return $retorno;
	}
}
?>