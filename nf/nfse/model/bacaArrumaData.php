<?php

include("CBd.php");

$CBd = CBd::singleton();
$ponteiro = $CBd->getPonteiro();
$ponteiro->BeginTrans();

$sql = "SELECT nf_data_emissao FROM nota_fiscal GROUP BY nf_data_emissao";

$recordSet = $ponteiro->Execute($sql);
$ponteiro->CommitTrans();

while ($array = $recordSet->FetchRow()) {
  $data = substr($array['nf_data_emissao'],6,4)."-".substr($array['nf_data_emissao'],3,2)."-".substr($array['nf_data_emissao'],0,2);
  $sql = "UPDATE nota_fiscal SET nf_data_emissao = '".$data."' WHERE nf_data_emissao='".$array['nf_data_emissao']."';";
  echo($sql."<br>");
  $ponteiro->BeginTrans();
  $rs = $ponteiro->Execute($sql);
  $ponteiro->CommitTrans();
}

/*$sql = "UPDATE nota_fiscal SET ";
*/


?>