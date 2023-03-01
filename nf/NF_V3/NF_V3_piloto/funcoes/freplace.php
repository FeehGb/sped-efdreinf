<?php 
/**
	Autor: Fernando H. Crozetta
	Data: 03/04/2017
	Funcao de substituições de dados na string passada

*/
function freplace($string,$array_troca)
{
	foreach ($array_troca as $key => $value) {
		$string = str_replace($key, $value, $string);
	}
	return $string;
}

 ?>