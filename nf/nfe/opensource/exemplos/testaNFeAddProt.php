<?php
require_once('../libs/ToolsNFePHP.class.php');
$nfe = new ToolsNFePHP;
$nfefile = $nfe->envDir.'nfe.xml';
$protfile = $nfe->temDir.'nfe-prot.xml';
if ($xml = $nfe->addProt($nfefile, $protfile)){
    file_put_contents($nfe->aprDir.'procNfe.xml', $xml);
}

?>
