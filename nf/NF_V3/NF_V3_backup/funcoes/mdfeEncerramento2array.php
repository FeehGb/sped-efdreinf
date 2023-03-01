<?php
/**
    Autor: Fernando H. Crozetta
    Data: 20/07/2017
    Funcção: Converter arquivo txt de encerramento para array
*/

function mdfeEncerramento2array($arquivo_txt,$arquivo_xml)
{
    // Mapeia os dados do txt de acordo com o array de nomes abaixo:
    $nomes_array = array ("cnpj","uf_contribuinte","uf_ws","ambiente","chave","protocolo","tipo_evento","data_hora","data_encerramento","uf_encerramento","municipio_encerramento");
    $dados = array();
    $array_txt=explode("|", file_get_contents($arquivo_txt));
    foreach ($nomes_array as $idx => $valor) {
        $dados[$valor] = $array_txt[$idx];
    }
    $dados["id"] = "ID".$dados["tipo_evento"].$dados["chave"]."01";

    return $dados;

}
