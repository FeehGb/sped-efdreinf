<?php
/**
* @name      	CLog
 * @version   	alfa
 * @copyright	2015 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para apresentar na tela o Log conforme solicitado pelo programa
*/

/**
 * @class CLog
 */ 
class CLog{

/*
 * Atributos da Classe
 */
	private $program;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pProgram, $gravar=true) {
    	$this->program = $pProgram;
	}

/**
 * @method mGravar
 * @autor Guilherme Silva
 */
	public function mMensagem($mensagem){
		echo date("Y-m-d H:i:s")." - ".$this->program." -> ".$mensagem."\n";
	}
}
?>