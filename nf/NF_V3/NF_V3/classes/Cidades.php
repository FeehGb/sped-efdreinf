<?php 
// Autor: Fernando H. Crozetta
// Data : 05/06/2017
// Funcao: Classe que realiza a conversão de nomes UF para seus códigos, e vice-versa


/**
* Classe para converter sigla de UF para código e vice-versa
*/
class Cidades
{
    
    function __construct() {
        $this->dados = json_decode(file_get_contents("/var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/cidades.json"), true);
    }
    
    public function getData(){
        return $this->dados;
    }
    
    public function getName($codigo) {
        return $this->dados[$codigo]['municipio'];
    }
    
    

}


