<?php
//  Autor: Fernando H. Crozetta
//  Data : 18/07/2017
//  Descricao: Script que cria os diretorios de ano/mes/dia sob o
//              diretório passado
//  Modo de uso:
//    php /var/www/html/nf/NF_V3/NF_V3/ferramentas/cria_dir_YYYY-mm-dd <raiz>

$diretorio_raiz = $argv[1];

$data = date("/Y/m/d");
exec("mkdir -p ".$diretorio_raiz.$data);
 ?>
