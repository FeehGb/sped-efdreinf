<?php 
// Autor : Fernando H. Crozetta
// Data  : 20/10/2017
// Descricao : Atuaiza a senha do certificado,
//              sendo passado o cnpj e senha nova
$cnpj = $argv[1];
$senha_nova = $argv[2];

chdir(__DIR__);

$config = parse_ini_file("../config/config.ini");
$arquivo_ini = $config['dados']."/config_cliente/".$cnpj.".ini";

$config_cli = parse_ini_file($arquivo_ini,true);
$senha_antiga = $config_cli['certificado']['senha'];

$str_ini = file_get_contents($arquivo_ini);
$str_ini = str_replace("senha=\"".$senha_antiga,"senha=\"".$senha_nova,$str_ini);
file_put_contents($arquivo_ini,$str_ini);
