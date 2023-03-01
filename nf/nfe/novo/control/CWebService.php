<?php
/**
 * @name      	CWebService
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer a manutenчуo do cadastro de Web Services
 * @TODO 		Fazer tudo
*/

/**
 * Classe CWebService
 */

/**
 * @import Importaчуo de Classes de comunicaчуo
 */ 
 require_once("../model/MWebService.php");

/**
 * @class CWebSerice
 */ 
class CWebService{

/*
 * Atributos da Classe
 */

	public $uf = "";
	public $versao_xml = "";
	public $servico = "";
	public $ambiente = "";
	public $metodo = "";
	public $nome = "";
	public $cnpj_web_service = "";
	public $cod_uf_ibge = "";
	public $metodo_conexao = "";
	public $url_completa = "";
	public $situacao = "";
	public $xsd = "";

	public $mensagemErro = "";
	
/**
 * @class CWebService
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function fGravar(){
		// Verifica se todos os campos necessarios estуo setados
		$localMensagem = "";
		if(	trim($this->uf) == ""){ $localMensagem .= " Uf ";}
		if(	trim($this->versao_xml) == ""){ $localMensagem .= " Versao XML ";}
		if(	trim($this->servico) == ""){ $localMensagem .= " Servico ";}
		if(	trim($this->ambiente) == ""){ $localMensagem .= " Ambiente ";}
		if(	trim($this->metodo) == ""){ $localMensagem .= " Metodo ";}
		if(	trim($this->nome) == ""){ $localMensagem .= " Nome / Descriчуo ";}
		if(	trim($this->cod_uf_ibge) == ""){ $localMensagem .= " Codigo UF IBGE ";}
		if(	trim($this->metodo_conexao) == ""){ $localMensagem .= " Metodo de Conexao ";}
		if(	trim($this->url_completa) == ""){ $localMensagem .= " Url Completa ";}
		if(	trim($this->situacao) == ""){ $localMensagem .= " Situacao ";}
		if($localMensagem != ""){
			$this->mensagemErro = " CWebService -> fGravar() -> Atributos Obrigatorios ".$localMensagem;
			return false;
		}

		// Instanciar Classe
		$MWebService = new MWebService();
		
		// Passagem de parametros para consulta
		$MWebService->uf 				= $this->uf;
		$MWebService->versao_xml		= $this->versao_xml;
		$MWebService->servico			= $this->servico;
		$MWebService->ambiente			= $this->ambiente;
		$MWebService->metodo			= $this->metodo;
		$MWebService->nome				= $this->nome;
		$MWebService->cod_uf_ibge		= $this->cod_uf_ibge;
		$MWebService->metodo_conexao	= $this->metodo_conexao;
		$MWebService->url_completa		= $this->url_completa;
		$MWebService->situacao			= $this->situacao;

		// Chamada da funcao record e retorno do erro
		$return = $MWebService->record();
		$this->mensagemErro = $MWebService->mensagemErro;
		return $return;
	}

	public function mObterWebServiceUf(){
		// Verifica se todos os atributos necessarios para consulta estao setados
		if($this->uf == ""){
			$this->mensagemErro = " CWebService -> mObterWebServiceUf() { Atributos UF obrigatorios para consulta }";
			return false;
		}

		// Instancia Classe Model Contribuinte
		$MWebService = new MWebService();

		// Passagem de parametros para consulta
		$MWebService->uf = $this->uf;
		$MWebService->ambiente = $this->ambiente;
		$MWebService->versao_xml = "";
		$MWebService->servico = "";

		// Chamada da funcao selectCNPJAmbiente e retorno do erro
		$return = $MWebService->mObterWebService();
		$this->mensagemErro = $MWebService->mensagemErro;
		return $return;
	}
	
	public function mObterWebService(){
		// Verifica se todos os atributos necessarios para consulta estao setados

		if($this->uf == "" || $this->versao_xml == "" || $this->servico == "" || $this->ambiente == ""){
			$this->mensagemErro = " CWebService -> mObterWebServiceUf() { Atributos UF, Versao XML, Servico e Ambiente obrigatorios para consulta }";
			return false;
		}

		// Instancia Classe Model WebService
		$MWebService = new MWebService();

		// Passagem de parametros para consulta
		$MWebService->uf = $this->uf;
		$MWebService->versao_xml = $this->versao_xml;
		$MWebService->servico = $this->servico;
		$MWebService->ambiente = $this->ambiente;

		// Chamada da funcao mObterWebService e retorno do erro
		$return = $MWebService->mObterWebService();
		$this->mensagemErro = $MWebService->mensagemErro;
		return $return;
	}
/*	
	public function mObterTodos(){
		// Nao ha atributos para pre verificacao

		// Instancia Classe Model Contribuinte
		$MContribuinte = new MContribuinte();

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
		$MContribuinte = new MContribuinte();

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
		$MContribuinte = new MContribuinte();

		// Passagem de parametros para exclusao do certificado
		$MContribuinte->cnpj = $this->cnpj;
		$MContribuinte->ambiente = $this->ambiente;
		$MContribuinte->contigencia = $this->contigencia;

		// Chamada da funcao updateContingencia e retorno do erro
		$return = $MContribuinte->updateContingencia();
		$this->mensagemErro = $MContribuinte->mensagemErro;
		return $return;
	}*/
}
?>