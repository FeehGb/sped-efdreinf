<?php
/**
 * testaCancelaEvent
 * 
 * Rotina de teste de cancelamento por evento
 * 
 * Corrija os dados para o cancelamento antes de testar
 */
require_once('../libs/ToolsNFePHP.class.php');

$nfe = new ToolsNFePHP;
$chNFe = "41140181660862000142550010000092661000000008";
$nProt = "141140000032236";
$xJust = "Cancelamento por motivo de teste em ambiente de homologação";
$tpAmb = '2';
$modSOAP = '2';

if ($resp = $nfe->cancelEvent($chNFe,$nProt,$xJust,$tpAmb,$modSOAP)){
    header('Content-type: text/xml; charset=UTF-8');
    echo $resp;
} else {
    header('Content-type: text/html; charset=UTF-8');
    echo '<BR>';
    echo $nfe->errMsg.'<BR>';
    echo '<PRE>';
    echo htmlspecialchars($nfe->soapDebug);
    echo '</PRE><BR>';
}    
?>
