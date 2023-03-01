<?php
/**
 * @name      	CContribuinte
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer a manuten��o do cadastro de Contribuinte
 * @TODO 		Fazer tudo
*/

/**
 * Classe CContribuinte
 */

/**
 * @import Importa��o de Classes de comunica��o
 */ 
 require_once("../model/MContribuinte.php");

/**
 * @class CContribuinte
 */ 
class CContribuinte{

/*
 * Atributos da Classe contribuinte
 */
	public $cnpj = "";
	public $ambiente = "";
	public $uf = "";
	public $cod_emp_fil_softdib = "";
	public $razao_social = "";
	public $certificado_tipo = "";
	public $certificado_caminho = "";
	public $certificado_senha = "";
	public $contigencia = "";
	public $data_hora_contingencia = "";
	public $justificativa_contingencia = "";
	public $pacote_xsd = "";
	public $email_usuario = "";
	public $email_senha = "";
	public $email_remetente = "";
	public $email_smtp = "";
	public $email_porta = "";
	public $email_ssl = "";
	public $email_conf_recebimento = "";
	public $proxy_servidor = "";
	public $proxy_porta = "";
	public $proxy_usuario = "";
	public $proxy_senha = "";
	public $diretorio_integracao = "";
	public $diretorio_backup = "";
	public $diretorio_importacao = "";
	public $diretorio_base = "";
	public $danfe_layout_caminho = "";
	public $danfe_layout_fs_da = "";
	public $danfe_logo_caminho = "";
	public $danfe_qtde_vias = "";
	public $danfe_automatica = "";
	public $server_impressao = "";
//	public $server_impressao_comando = "";
	public $ativo = "";

	public $mensagemErro = "";
	public $mensagemServico = "";

	private $grupo;

	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
/**
 * @class CContribuinte
 * @autor Guilherme Silva
 * @TODO  Fazer as valida��es dos campos e chamar a Model para cadastrar as informa��es.
 */
	public function fGravar(){
		// Verifica se todos os campos pertinentes est�o setados
		$localMensagem = "";
		if(	trim($this->cnpj) == ""){ $localMensagem .= " CNPJ ";}
		if(	trim($this->ambiente) == ""){ $localMensagem .= " Ambiente ";}

		if(trim($this->danfe_qtde_vias) == "") $this->danfe_qtde_vias=1;
		

		/*if(	trim($this->uf) == ""){ $localMensagem .= " Uf ";}			
		if(	trim($this->razao_social) == ""){ $localMensagem .= " Raz�o Social ";}			
		//if(	trim($this->certificado_tipo) == ""){ $localMensagem .= " Tipo Certificado ";}
		if(	trim($this->certificado_senha) == ""){ $localMensagem .= " Senha Certificado ";}
		if(	trim($this->pacote_xsd) == ""){ $localMensagem .= " Pacote XSD ";}
		if(	trim($this->diretorio_integracao) == ""){ $localMensagem .= " Diretorio de Integracao ";}
		if(	trim($this->danfe_layout_caminho) == ""){ $localMensagem .= " Caminho Danfe ";}*/
		if($localMensagem != ""){
			$this->mensagemErro = " CContribuinte -> fGravar() { Atributos Obrigatorios ".$localMensagem." }";
			return false;
		}

		// Instanciar Classe
		$MContribuinte = new MContribuinte($this->grupo);
		
		// Passagem de parametros para consulta
		$MContribuinte->cnpj 					= $this->cnpj;
		$MContribuinte->ambiente 				= $this->ambiente;
		$MContribuinte->cnpj 					= $this->cnpj;
		$MContribuinte->ambiente 				= $this->ambiente;
		$MContribuinte->uf 						= $this->uf;
		$MContribuinte->cod_emp_fil_softdib 	= $this->cod_emp_fil_softdib;
		$MContribuinte->razao_social 			= $this->razao_social;
		$MContribuinte->certificado_tipo 		= $this->certificado_tipo;
		$MContribuinte->certificado_caminho 	= $this->certificado_caminho;
		$MContribuinte->certificado_senha 		= $this->certificado_senha;
		$MContribuinte->contigencia 			= $this->contigencia;
		$MContribuinte->pacote_xsd 				= $this->pacote_xsd;
		$MContribuinte->email_usuario 			= $this->email_usuario;
		$MContribuinte->email_senha 			= $this->email_senha;
		$MContribuinte->email_remetente 		= $this->email_remetente;
		$MContribuinte->email_smtp 				= $this->email_smtp;
		$MContribuinte->email_porta 			= $this->email_porta;
		$MContribuinte->email_ssl 				= $this->email_ssl;
		$MContribuinte->email_conf_recebimento	= $this->email_conf_recebimento;
		$MContribuinte->proxy_servidor 			= $this->proxy_servidor;
		$MContribuinte->proxy_porta 			= $this->proxy_porta;
		$MContribuinte->proxy_usuario 			= $this->proxy_usuario;
		$MContribuinte->proxy_senha 			= $this->proxy_senha;
		$MContribuinte->diretorio_integracao 	= $this->diretorio_integracao;
		$MContribuinte->diretorio_backup 		= $this->diretorio_backup;
		$MContribuinte->diretorio_importacao 	= $this->diretorio_importacao;
		$MContribuinte->diretorio_base 			= $this->diretorio_base;
		$MContribuinte->danfe_layout_caminho 	= $this->danfe_layout_caminho;
		$MContribuinte->danfe_layout_fs_da 		= $this->danfe_layout_fs_da;
		$MContribuinte->danfe_logo_caminho 		= $this->danfe_logo_caminho;
		$MContribuinte->danfe_qtde_vias 		= $this->danfe_qtde_vias; 		
		$MContribuinte->danfe_automatica 		= $this->danfe_automatica; 		
		$MContribuinte->server_impressao 		= $this->server_impressao;
		//$MContribuinte->server_impressao_comando= $this->server_impressao_comando;
		
		if($this->ambiente == "D"){
			$MContribuinte->ativo = "N";
		}else{
			$MContribuinte->ativo = "S";
		}

		// Chamada da funcao selectCNPJAmbiente e retorno do erro
		$return = $MContribuinte->record();
		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}

	public function mObterContribuinte(){
		// Verifica se todos os atributos necessarios para consulta estao setados
		if($this->cnpj == ""){
			$this->mensagemErro = " CContribuinte -> mObterContribuinte() { Atributo CNPJ obrigatorios para consulta }";
			return false;
		}

		// Instancia Classe Model Contribuinte
		$MContribuinte = new MContribuinte($this->grupo);

		// Passagem de parametros para consulta
		$MContribuinte->cnpj = $this->cnpj;

		// Chamada da funcao selectCNPJAmbiente e retorno do erro
		$return = $MContribuinte->selectAll();

		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}
	
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

		// Em caso de SCAN, verificar se o mesmo est� ativo para operar
		$contribuinte = explode("-",$_POST['sContribuinte']);

		// Instanciar Classe ToolsNFePHP
		switch($this->contigencia){
			case "03":
				$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$this->grupo,2,false, true);
			break;
			case "06":
			case "07":
				$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$this->grupo,2,false, "SVC");
			break;
			default:
				$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$this->grupo,2,false);
			break;
		}

/*		$resp = $ToolsNFePHP->statusServico();
		
		$this->mensagemServico = $resp['xMotivo'];

		if(!$resp){
			$this->mensagemErro = $ToolsNFePHP->errMsg;
			return false;
		}

		if($resp['cStat'] != "107" && $resp['cStat'] != "113"){
			// Esta desabilitado nao podendo operar ou houve um erro
			$retornoJson['retorno'] = false;
			$retornoJson['mensagem'] = $resp['xMotivo'];
			echo json_encode($retornoJson);
			exit();
		}*/

		if($ToolsNFePHP->enableSVC == "SVC-AN" && ($this->contigencia == "07" || $this->contigencia == "06" )){
			$this->contigencia = "06";
		}elseif($ToolsNFePHP->enableSVC == "SVC-RS" && ($this->contigencia == "07" || $this->contigencia == "06" )){
			$this->contigencia = "07";
		}

		// Instancia Classe Model Contribuinte para atualizar a situa��o
		$MContribuinte = new MContribuinte($this->grupo);

		// Passagem de parametros atualizar a contingencia
		$MContribuinte->cnpj = $this->cnpj;
		$MContribuinte->ambiente = $this->ambiente;
		$MContribuinte->contigencia = $this->contigencia;
		if($this->contigencia == "01"){
			$this->justificativa_contingencia = "";
		}
		$MContribuinte->justificativa_contingencia = $this->justificativa_contingencia;
		// Chamada da funcao updateContingencia e retorno do erro
		$return = $MContribuinte->updateContingencia();
		if(!$return){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}
		$return['mensagemServico'] = $retornoJson['mensagem'];
		return $return;
	}
}
?>