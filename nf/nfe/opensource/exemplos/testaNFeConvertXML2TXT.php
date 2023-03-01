<?php

require_once('../libs/ConvertNfeNFePHP.class.gui.php');

$arq = '/var/www/nfe/recebe/41140101977555000150550010000157781000157789.xml';

$nfe=new ConvertNfeNFePHP();                                      
$TXT=$nfe->XML2TXT( $arq );
var_dump($TXT);                                                   













?>
