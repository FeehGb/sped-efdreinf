<?php
	/*
		Classe:					CConfig.php
		Autor:					Guilherme Silva
		Data:					28/02/2012
		Finalidade: 			Manipular o arquivo de configuração
		Programas chamadores: 	
		Programas chamados: 	
	*/
	class CConfig{
		public $configWs;
		
		public $mensagemErro;
		
		public function lerArquivo($cnpj=""){
		  if($cnpj == ""){
			$this->mensagemErro = " CConfig -> lerArquivo() {Parametros CNPJ nao eh opcional} ";
			return false;
		  }
		  if(!file_exists("/var/www/html/nf/nfse/configuracoes/".$cnpj."/config.ini")){
			$this->mensagemErro = " CConfig -> lerArquivo() {Arquivo de configuracao nao encontrado, o sistema nao foi instalado corretamente } ";
			return false;
		  }
		  if(!$arquivo = parse_ini_file("/var/www/html/nf/nfse/configuracoes/".$cnpj."/config.ini")){
			$this->mensagemErro = " CConfig -> lerArquivo() {O Arquivo nao eh um INI valido, o sistema nao foi instalado corretamente} ";
			return false;
		  }

		  $this->configWs['servidor'] = $arquivo['servidor'];
		  $this->configWs['usuario'] = $arquivo['usuario'];
		  $this->configWs['senha'] = $arquivo['senha'];
		  $this->configWs['base'] = $arquivo['base'];
		  $this->configWs['sgbd'] = $arquivo['sgbd'];
		  $this->configWs['smtp'] = $arquivo['smtp'];
		  $this->configWs['porta'] = $arquivo['porta'];
		  $this->configWs['email'] = $arquivo['email'];
		  $this->configWs['senhaEmail'] = $arquivo['senhaEmail'];
		  $this->configWs['WSCodigoTom'] = $arquivo['ws_codigoTom'];		  
		  $this->configWs['WSUrl'] = $arquivo['ws_url'];
		  if(isset($arquivo['ws_porta'])){ $this->configWs['WSPorta'] = $arquivo['ws_porta']; }
		  if(isset($arquivo['ws_conexaoSegura'])){ $this->configWs['WSConexaoSegura'] = $arquivo['ws_conexaoSegura']; }
		  if(isset($arquivo['ws_cnpj'])){ $this->configWs['WSCNPJ'] = $arquivo['ws_cnpj']; }
		  if(isset($arquivo['ws_senha'])){ $this->configWs['WSSenha'] = $arquivo['ws_senha']; }
		  if(isset($arquivo['ws_arquivoPFX'])){ $this->configWs['WSArquivoPFX'] = $arquivo['ws_arquivoPFX']; }
		  if(isset($arquivo['ws_senha'])){ $this->configWs['WSSenhaPFX'] = $arquivo['ws_senha']; }
		  if(isset($arquivo['ws_proxy'])){ $this->configWs['WSProxy'] = $arquivo['ws_proxy']; }
		  if(isset($arquivo['ws_ipProxy'])){ $this->configWs['WSIPProxy'] = $arquivo['ws_ipProxy']; }
		  if(isset($arquivo['ws_portaProxy'])){ $this->configWs['WSPortaProxy'] = $arquivo['ws_portaProxy']; }
		  if(isset($arquivo['ws_usuarioProxy'])){ $this->configWs['WSUsuarioProxy'] = $arquivo['ws_usuarioProxy']; }
		  if(isset($arquivo['ws_senhaProxy'])){ $this->configWs['WSSenhaProxy'] = $arquivo['ws_senhaProxy']; }
		  return true;
		}
	}
?>