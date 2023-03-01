<?php
// Autor: Fernando H. Crozetta
// Data : 19/07/2017
// Descrição: Realiza o append do protocolo ao fim do xml da nota
// Modo de uso:
//    php /var/www/html/nf/NF_V3/NF_V3/ferramentas/mdfe-append-protocolo.php <cnpj> <chave> <arquivo protocolo>

chdir(__DIR__);
// TODO: Conversar com eduardo sobre como realizar este append
$cnpj = $argv[1];
$chave = $argv[2];
$arquivo_com_protocolo = $argv[3];
$dir_arq_saida = $argv[4];


$config = parse_ini_file("../config/config.ini");
$dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);
$dir_temp = $config['temp']."/mdfe/";
$template_append_inicio = $dados['mdfe']['append_protocolo_inicio'];
$template_append_fim = $dados['mdfe']['append_protocolo_final'];

$arquivo_saida = file_get_contents($dir_temp.$chave."-pendMDFe.xml");
$arquivo_saida = preg_replace("/.*<enviMDFe/","<enviMDFe",$arquivo_saida);
$arquivo_saida = preg_replace("/<\/enviMDFe>.*/","</enviMDFe>",$arquivo_saida);
$arquivo_saida = $template_append_inicio.$arquivo_saida.$template_append_fim;

$protocolo = file_get_contents($dados['mdfe']['dir_retorno_consulta_lote'].$arquivo_com_protocolo);
// $protocolo = file_get_contents( "/user/mdfe/82574062000252/CaixaSaida/Processado/".$arquivo_com_protocolo);
$protocolo = preg_replace("/.*<protMDFe/","<protMDFe",$protocolo);
$protocolo = preg_replace("/<\/protMDFe>.*/","</protMDFe>",$protocolo);

$arquivo_saida = preg_replace("/ALTERAR_PROTMDFE/",$protocolo,$arquivo_saida);
$dir_saida = $dados['mdfe']['dir_retorno_append'];
file_put_contents($dir_saida.$dir_arq_saida,$arquivo_saida);

?>