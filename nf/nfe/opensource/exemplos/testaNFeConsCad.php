<?php
require_once('../libs/ToolsNFePHP.class.php');
$nfe = new ToolsNFePHP('81660862000142','0');
$UF = 'MS';
$CNPJ = '56998701001945';
$IE = '';
$CPF = '';
$tpAmb = '2';
$modSOAP = '2';

if ($resposta = $nfe->consultaCadastro($UF, $CNPJ, $IE, $CPF, $tpAmb, $modSOAP) ){
    print_r($resposta);
    echo '<PRE>';
    echo htmlspecialchars($nfe->soapDebug);
    echo '</PRE><BR>';
} else {
    echo "Houve erro !! $nfe->errMsg";
    echo '<PRE>';
    echo htmlspecialchars($nfe->soapDebug);
    echo '</PRE><BR>';
}    

?>
