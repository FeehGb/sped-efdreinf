<?php
	require_once ("../funcoes/carrega_config.ini.php"); // Carrega as configuracoes
	
	/**
	 * Autor: Fernando H. Crozetta
	 * Data : 31/03/2017
	 * Função para gravar o log de execução. 
	 * Este log NÃO deve ser usado para depuração de erro.
	 */
	function flog($mensagem)
	{
		$config = parse_ini_file("../config/config.ini");
		$arquivo_log = $config['log'];
		$trace = debug_backtrace();
		$stack = $trace[0];
		$data = date("[d/m/Y - G:i:s]");
		$mensagem = "\n".$data."\t".$stack['file'].":".$stack['line']."\n\t[Log] - ".$stack['args'][0];
		
		$saida = fopen($arquivo_log, "a") or die("Nao foi possivel abrir o arquivo de log");
		fwrite($saida, $mensagem);
		fclose($saida);
	}

	function addLog($tag, $log)
	{
        global $config;
        
		$date     = date("d/m/Y H:i:s")  ; 
		$tag      = strtoupper($tag)     ; 
		$caller   = debug_backtrace()[0] ; 
		$function = $caller['function']  ; 
		$line     = $caller['line']      ; 
		$file     = $caller['file']      ; 
		
		$log = "\n[:NF_V3][:$tag] $date $log (In function $function $file:$line)";
		
		$myfile = file_put_contents(
			$config['log']        , 
			$log . PHP_EOL        , 
			FILE_APPEND | LOCK_EX
		);
	}
    
    
    
    
    
    
    