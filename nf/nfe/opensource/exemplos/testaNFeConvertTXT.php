<?php

require_once('../libs/ConvertNFePHP.class.php');

$arq = '/home/guilherme/NFE.txt';

//instancia a classe
$nfe = new ConvertNFePHP();

if ( is_file($arq) ){
    $xml = $nfe->nfetxt2xml($arq);
    if ($xml != ''){
        echo '<PRE>';
        echo htmlspecialchars($xml);
        echo '</PRE><BR>';
        if (!file_put_contents('/home/guilherme/NFE.xml',$xml)){
            echo "ERRO na gravação";
        }
    }
}


?>
