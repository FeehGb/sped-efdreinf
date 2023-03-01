<?php 
// Autor: Fernando H. Crozetta
// Data : 05/06/2017
// Funcao: Classe que realiza a conversão de nomes UF para seus códigos, e vice-versa


/**
* Classe para converter sigla de UF para código e vice-versa
*/
class CodigosUf
{
	private $arq;
	
	function __construct()
	{
		$this->dados = json_decode(file_get_contents("../config/uf.json"));
	}
	
	public function paraNum($sigla)
	{
		return $this->dados->$sigla;
	}
	
	
	public function deNum($codigo)
	{
		foreach ($this->dados as $key => $value) {
			if ($codigo == $value) {
				return $key;
			}
		}
	}
}

?>