<?php 
/*
Programa: cria_diretorios.php
Autor   : Fernando H. Crozetta
Data    : 24/05/2017

Funcao  : Programa que cria os diretorios uso Se não for passado um parametro de caminho, irá gerar o padrão de diretórios. talvez seja necessario ser executado como root
*/

function criar($diretorio)
{
	// Cria diretório
	if (!is_dir($diretorio)) {
		if (!mkdir($diretorio,0777,true)) {
			die("[ERRO 0x01]\n\tnao foi possivel criar  o diretorio:".$diretorio);
		};
		// } else{
		// 	echo "Criando ".$diretorio."\n";
		// };
	}
}


// Main
if (isset($argv[1])) {
	$diretorio = $argv[1];
	criar($diretorio);
}else{
	$config = parse_ini_file("../config/config.ini");
	criar($config['temp']);
	exit(0);
}

