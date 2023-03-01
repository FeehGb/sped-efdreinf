<?php 
// Autor: Fernando H. Crozetta
// Data: 30/05/2017
// Funcao: Retornar a o texto dentro da tag solicitada,dentro da string passada
// Este programa deve retornar uma string, que se refere ao texto dentro de uma tag do texto passado

$tag=$argv[1];
$string= $argv[2];
$tmp = preg_replace("/.*<".$tag.">/", "", $string);
#print($tag);
$tmp = preg_replace("/<\/".$tag.">.*/", "", $tmp);
#print($tag);

// Caso a tag nao exista
if ($tmp == $string) {
    echo "";
}else{
    echo $tmp;
}

