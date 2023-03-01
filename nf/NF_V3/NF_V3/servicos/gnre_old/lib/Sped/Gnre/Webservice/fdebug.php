<?php 
/**
	Autor: Fernando H. Crozetta
	Data : 31/03/2017
	Faz a depuração da execução do programa.
	caminho do arquivo de debug fica dentro do config.ini
*/

	function fdebug($mensagem='')
	{
		#$config = parse_ini_file("../config/config.ini");
		#$arquivo_debug = $config['arquivo_debug'];
		#$modo_debug = $config['debug'];
		if (true) {
			$trace = debug_backtrace();
			$data = date("[d/m/Y - G:i:s]");
			$mensagem = "\n".$data."\n\t ->".$mensagem."<-\n";
			echo $mensagem;
			var_export($trace);
			
			print_r($trace,true);
			#$saida = fopen($arquivo_debug, "a") or die("Nao foi possivel abrir o arquivo de debug");
			#fwrite($saida, $mensagem);
			#fwrite($saida, print_r($trace,true));
			#fclose($saida);
			
		}

	}


 ?>