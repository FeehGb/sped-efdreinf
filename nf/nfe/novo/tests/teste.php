<?php
/*
	Programa teste para chamar outros programas e testar plataformas.
*/

	include("../control/CGerarDanfe.php");
	$CGerarDanfe = new CGerarDanfe;
	$CGerarDanfe->xmlNfe = file_get_contents("nfe.xml");
	echo $CGerarDanfe->gerarDanfe();
?>