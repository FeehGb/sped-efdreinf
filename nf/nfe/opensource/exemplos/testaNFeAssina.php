<?php
require_once('../libs/ToolsNFePHP.class.php');
$nfe = new ToolsNFePHP;

$file = 'xml/NFE.xml';
$arq = file_get_contents($file);

if ($xml = $nfe->signXML($arq, 'infNFe')){
    file_put_contents($file, $xml);
} else {
    echo $nfe->errMsg;
}


?>
