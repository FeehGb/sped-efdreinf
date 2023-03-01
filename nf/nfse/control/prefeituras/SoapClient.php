<?php 

ini_set('display_errors', 'On');


$wsdl = 'https://isseteste.maringa.pr.gov.br/ws/?wsdl';
//$wsdl = 'maringaWSDL.xml';
$cert = '../../certificados/01631022000201_certKey.pem';//nome do arquivo de certificado
$pass = 'VIANMAQTVCV';//senha do certificado

try {
	$options = array(
		'local_cert' 	=> $cert,
		'passphrase' 	=> $pass,
		'trace' 		=> true,
		'exceptions' 	=> true,
		'uri'      		=> 'https://isseteste.maringa.pr.gov.br/ws/',
		'location' 		=> 'https://isseteste.maringa.pr.gov.br/ws/'
	);
//https://isseteste.maringa.pr.gov.br/ws/#EnviarLoteRpsSincrono
	
	$client = new SoapClient(null,$options); // Instancia o web service
echo "<pre>";
print_r($client->__getFunctions());
echo "</pre>";
	$xml = file_get_contents("/home/guilherme/nfse.xml");

	$response = $client->EnviarLoteRpsSincrono($xml);

	echo "<pre>" . $response . "\n\n</pre>";

} catch(Exception $e) {
	echo "<pre>" . ($e->getMessage() . "\n\n</pre>");
}


?>