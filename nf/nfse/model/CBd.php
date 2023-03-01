<?php

	/*
		Classe:					CBd.php (Singleton)
		Autor:					Guilherme Silva
		Data:					02/02/2012
		Finalidade: 			Responsável por estabelecer conexao com o banco de dados
		Programas chamadores: 	
		Programas chamados: 	BD{Produto}
	*/
	require_once('/var/www/html/nf/nfse/model/adodb/adodb.inc.php');
	
	class CBd{

		//Atributos
		//Guardar a primeira instancia da classe
		private static $instance;
		//Nome do servidor onde encontra-se o banco de dados (localhost)
		private static $servidor;
		//Usuario e senha do banco de dados
		private static $usuario;
		private static $senha;
		//Nome da base que esta cadastrada
		private static $base;
		//Nome SGBD (mysql, oracle, access, etc)
		private static $SGBD;
		//Ponteiro de conexao com banco de dados
		public static $ponteiro;
		//Mensagem de Erro de retorno
		public static $mensagemErro;

		//Metodos
		
		// Um construtor privado; previne a criação direta do objeto
	    private function __construct(){ }
		
		//Funcao Singleton, eh responsavel por instanciar apenas uma vez a classe
		public static function singleton($pGrupo)
	    {
			/*
				Aqui dentro deste IF quer ira ficar as instrucoes que serao criadas apenas uma vez.
				Este eh o ouro do singleton, ele retorna a classe criada na primeira execucao.
			*/
			if (!isset(self::$instance)) {
			  $c = __CLASS__;
			  self::$instance = new $c;
			  if(!self::conectar($pGrupo)){
				return false;
			  }
			}
			return self::$instance;
    	}
	
		//Conectar na Base de Dados
		
		private static function conectar($pGrupo){
			if(!self::obterConfiguracoes($pGrupo)){
				//Obter as configuracoes do BD
				return false;
			}
			self::$ponteiro=NewADOConnection(self::$SGBD);
			if(!self::$ponteiro){
				return false;
			}
			if(!self::$ponteiro->Connect(self::$servidor,self::$usuario,self::$senha,self::$base)){
				return false;
			}

/*			$this->ponteiroBD = &ADONewConnection($this->SGBD);
			if(!$this->ponteiroBD){
				throw new Exception( " CBd -> conectarBase() {Nao foi possivel encontrar o drive do SGBD ".$this->SGBD."} " );
				return false;
			}
			if(!$this->ponteiroBD->PConnect($this->servidor,$this->usuario,$this->senha,$this->base)){
				throw new Exception( " CBd -> conectarBase() {Nao foi possivel estabelecer conexao com o SGBD ".$this->SGBD.", verifique as configuracoes do BD} " );
				return false;
			}*/
			return true;
		}

		public function getPonteiro()
		{
			return self::$ponteiro;
		}
		
		/* OBTER CONFIGURACOES ANTIGO EXCLUSIVAMENTE VIA ARQUIVO INI, AGORA EH VIA ARQUIVO INI + GRUPO VIA PARAMETRO
		public function obterConfiguracoes(){
			if(!file_exists("/var/www/html/nfse/config.ini")){
				self::$mensagemErro = " CBd -> obterConfiguracoes() {Nao foi encontrado o arquivo de configuracoes (config.ini) verifique se o sistema foi instalado corretamente}";
				return false;
			}
			if(!$arrayArquivoIni = parse_ini_file("/var/www/html/nfse/config.ini")){
				self::$mensagemErro = " CBd -> obterConfiguracoes() {O arquivo (config.ini) foi modificado e na se trata do original, reinstale o sistema}";
				return false;
			}
			self::$servidor = $arrayArquivoIni["servidor"];
			self::$usuario = $arrayArquivoIni["usuario"];
			self::$senha = $arrayArquivoIni["senha"];
			self::$base = $arrayArquivoIni["base"];
			self::$SGBD = $arrayArquivoIni["sgbd"];
			return true;
		}*/
		
		public function obterConfiguracoes($pGrupo){
			if(!file_exists("/var/www/html/nf/nfse/config.ini")){
				self::$mensagemErro = " CBd -> obterConfiguracoes() {Nao foi encontrado o arquivo de configuracoes (config.ini) verifique se o sistema foi instalado corretamente}";
				return false;
			}
			if(!$arrayArquivoIni = parse_ini_file("/var/www/html/nf/nfse/config.ini")){
				self::$mensagemErro = " CBd -> obterConfiguracoes() {O arquivo (config.ini) foi modificado e na se trata do original, reinstale o sistema}";
				return false;
			}
			self::$servidor = $arrayArquivoIni["servidor"];
			self::$usuario = $arrayArquivoIni["usuario"];
			self::$senha = $arrayArquivoIni["senha"];
			self::$base = "nfse_".$pGrupo;
			self::$SGBD = $arrayArquivoIni["sgbd"];
			return true;
		}
		
	}
?>