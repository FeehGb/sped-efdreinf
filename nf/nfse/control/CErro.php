<?php

	/*
		Classe:					CErro.php
		Autor:					Guilherme Silva
		Data:					27-01-2012
		Finalidade: 			Efetuar a gração de LOG para rastrabilidade das operações da aplicação PHP.
		Programas chamadores: 	Linha de Comando
		Programas chamados: 	CArquivoComunicacao, CComunicadorWebService
	*/
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	
	class CErro{
		
		//Atributos
		
		/*	$flagLog:
				0 = Não gravar nenhum LOG
				1 = Gravar LOG apenas em erros
				2 = Gravar LOG em todas as operações sucessos ou erros */
		private $flagLog;
		// caminho do arquivo
		private $caminhoLog;
		
		public $mensagem;
		public $notaFiscal;
		
		//Metodos
		//Construtor
		public function erro(){
		  if($this->mensagem!= ""){
			  /*
			  	Caso for preenchido devera cadastrar o erro na tabela de criticas
				Gravar no arquivo de retorno para o COBOL ler
			  */
			  $CCritica = new CCritica();
			  $CCritica->codigo_empresa = "";
			  $CCritica->codigo_filial = "";
			  $CCritica->numero_controle = "";
			  $CCritica->descricao = $this->mensagem;
			  $CCritica->data = date('d/m/Y');
			  $CCritica->hora = date('H:i:s');

			  $CCritica->inserir();
			  $CArquivoComunicacao = new CArquivoComunicacao();
			  
			  
		  }
		}
		
		//Getters e Setters
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
		
		
		
		
		
		
// IMPORTADA DA ESTRUTURA DO WEB		
//__________________________________________________________________________________________
//	Classe: 		CErro																	|
//	Finalidade:		Trata erro dos programas e grava erro em log de base de dados local		| 
//	Autor:			Guilherme Pinto															|
//	Data:			02/07/2010																|
//	Versao:			0																		|
//	Release:		0																		|
//__________________________________________________________________________________________|

//		Declaração dos Atributos	
		var $programa = "";
		var $metodo = "";
		var $erro = "";
		var $usuario = "";
		var $aplicacao = "";
		var $menu = "";
		var $tarefa = "";
		var $numeroPid = "";
		
		var $stringErros = "";
		var $parametrosGerais = "";


/*______________________________MÉTODOS____________________________________________________*/


//__________________________________________________________________________________________
//Método:		Matriz :: montaParametrosComunicador(String Empresa, String DesEmpresa,		|
//												 String Filial, String DescFilial,			|
//												 String Usuario, String Senha)				|
//Finalidade: 	Monta os parametros com o tamanho											|
//Autor:		Guilherme Pinto																|
//__________________________________________________________________________________________|
	
		public function MTratarErro($parmPrograma, $parmMetodo, $parmErro){
			$localMensagem = "";
			$localMensagem = "Programa:<".$parmPrograma."> Funcao/Metodo:<".$parmMetodo."> ";
			$localMensagem .= "Erro:<".$parmErro."> ";
			$localMensagem .= "Data/Hora:<".time()."> ";
			echo($localMensagem);
			MGravarErroBD($localMensagem);
		}
		
		public function MGravarBaseDados($parmMensagem){
//			$localMensagem
		}
		
		public function MObterUrl(){
			$url = "http://" . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			return $url;
		}
		
//__________________________________________________________________________________________
//Método:		MLimparErros(String $parmString)											|
//Retorno:		Alocado nos atributos AParametrosErros e AListarErros						|
//Finalidade: 	Varrer erros retornados do Cobol com #ERRO#									|
//Autor:		Guilherme Pinto																|
//__________________________________________________________________________________________|
		
		public function MLimparErrosComunicador($parmString){

//			Separa Erros
			while(!(strpos($parmString, "#JUGMEN#") === false)){
				$erro1 = substr($parmString, strpos($parmString, '#JUGMEN#')+8, 80);
				$erro2 = substr($parmString, strpos($parmString, '#JUGMEN#')+88, 80);
				$this->stringErros .= $erro1."\n".$erro2."|";
				$stringARetirar = substr($parmString, strpos($parmString, '#JUGMEN#'), 169);
				$parmString = str_replace($stringARetirar, "", $parmString);
			}
			
//			Retorna Parametros sem Sujeira
			return $parmString;
		}		
		
		public function MChecarDisplayCobol($parmString){

//			Checa ocorrência de Erros do COBOL
			$localCaracterDeDisplay = '';
			$localBusca = strpos($parmString, $localCaracterDeDisplay);

			if ($localBusca !== false) {
				$this->stringErros .= "FATAL: Houve um erro de logica de conversao WEB, contate o suporte responsavel!";
			}			
			
//			Retorna Parametros sem Sujeira
			return $parmString;
		}		

		public function MGravarErroLogArquivo($parmErro){
			$fp = fopen("/var/www/html/web/download/LOG-".date("Ymd"), "a");
			fwrite($fp,$parmErro);
			fclose($fp);
		}
		
		public function MGravarErroLogArquivoPDV($pdv,$parmErro){
			$file = "/var/www/html/web/download/".$pdv.date("Ymd");
			$fp = fopen($file, "a");
			@fwrite($fp,$parmErro);
			@fclose($fp);
		}

	}

?>