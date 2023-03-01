<?php
/**
 * @name      	CConsultaCadastro
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para Consultar o Cadasro de Contribuinte ICMS conforme UC-NFE025
 * @TODO 		Fazer tudo
*/

/**
 * @import classes
 */ 
 require_once("../libs/ToolsNFePHP.class.php");
 require_once("../model/MContribuinte.php");
 require_once("../model/MLog.php");

/**
 * @class CConsultaCadastro
 */ 
class CConsultaCadastro{

/*
 * Atributos da Classe
 */
	public $cnpj;
	public $cpf;
	public $ie;
	public $uf;
	public $ambiente;
	public $contribuinte;
 	
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
	public function mConsCadContribuinte(){
		// Verifica se todos os campos necessarios estуo setados
		$localMensagem = "";
		if(	trim($this->cnpj) == "" && trim($this->ie) == "" ){ $localMensagem .= " CNPJ ou Inscriчуo Estadual do Contribuinte "; }
		if(	trim($this->ambiente) == ""){ $localMensagem .= " Ambiente "; }
		if(	trim($this->uf) == ""){ $localMensagem .= " UF "; }
		if(	trim($this->contribuinte) == ""){ $localMensagem .= " Contribuinte "; }
		if($localMensagem != ""){
			$this->mensagemErro = " CConsultaCadastro -> mConsCadContribuinte() -> Atributos Obrigatorios: ".$localMensagem;
			return false;
		}
		
		if(strlen($this->cnpj) == 11){
			$this->cpf = $this->cnpj;
			$this->cnpj = "";
		}

		// Instanciar Classe ToolsNFePHP
		$ToolsNFePHP = new ToolsNFePHP($this->contribuinte, $this->ambiente, $this->grupo);

		$resp = $ToolsNFePHP->consultaCadastro($this->uf, $this->cnpj, $this->ie, $this->cpf, $this->ambiente);

		if(!$resp){
			$this->mensagemErro = $ToolsNFePHP->errMsg;
			return false;
		}

		$resp['dhCons'] = date("d/m/Y h:i:s", strtotime($resp['dhCons']));
		$resp['dIniAtiv'] = date("d/m/Y", strtotime($resp['dIniAtiv']));
		$resp['dUltSit'] = date("d/m/Y", strtotime($resp['dUltSit']));
		$resp['dBaixa'] = date("d/m/Y", strtotime($resp['dBaixa']));
		return $resp;
	}

}
?>