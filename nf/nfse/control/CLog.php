<?php

	/*
		Classe:					CLog.php
		Autor:					Guilherme Silva
		Data:					27-01-2012
		Finalidade: 			Efetuar a gração de LOG para rastrabilidade das operações da aplicação PHP.
		Programas chamadores: 	Linha de Comando
		Programas chamados: 	CArquivoComunicacao, CComunicadorWebService
	*/
	class CLog{
		
		//Atributos
		
		/*	$flagLog:
				0 = Não gravar nenhum LOG
				1 = Gravar LOG apenas em erros
				2 = Gravar LOG em todas as operações sucessos ou erros */
		private $flagLog;
		// caminho do arquivo
		private $caminhoLog;		
		
		//Metodos
		
		//Getters e Setters
		public function setFlagLog($pFlagLog){
		  $this->flagLog = $pFlagLog;
		}
	
		public function getLog(){
		  return $this->flagLog;
		}

		//Gravar Log	
		public function gravarLog($pMensagem, $pFlagErro, $pClasse){
		  $linhaLog = "";
		  
		  switch($this->flagLog){
			  case 0:
			  	return true;
			  case 1:
			  	if ($pFlagErro == false){
				  $linhaLog="";
				}
			  case 2:
		  }
		  
		}
	}

?>