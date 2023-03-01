<?php 
/**
*	Autor: Fernando H. Crozetta
*	Data : 24/03/2017
*	Funcao: realizar as buscas de dados de webservices
*/

require_once("../funcoes/flog.php"); // Para LOG


/**
* Classe que realiza a busca dos dados do arquivo de webservic
*/
class BuscaWebService
{
	// Dados de uso interno
	private $config;
	private $arquivo_webservices; // Arquivo para leitura dos dados

	function __construct($uf,$tipo_nota,$tipo_ambiente='homologacao')
	{
		$this->config = parse_ini_file("../config/config.ini");
		
		// Define qual o arquivo a ser usado
		$this->arquivo_webservices = $this->config['servicos'].$tipo_nota."/webservices/".$uf."/homologacao.json";
		print(">>>>{$this->arquivo_webservices}<<<<");
	}

	private function arquivo_existe()
	{
		return (file_exists($this->arquivo_webservices));
	}


	/**
	  *	Retorna um array dos dados pesquisados, ou false.
	  *	Array de retorno:
	  *	url,
	  *	metodo,
	  *	versao
	*/
	public function buscarServico($nome_servico,$versao)
	{
		if (!$this->arquivo_existe()) {
			return false;
		};
		
		$servico = json_decode(file_get_contents($this->arquivo_webservices));
		//var_dump($this->arquivo_webservices);exit();
		// Dados buscados
		return $servico->$versao->$nome_servico;
		// $metodo = $servico[0]['method']->__tostring();
		// $versao = $servico[0]['version']->__tostring();
	}
}
