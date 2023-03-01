<?php 
// Autor: Fernando H. Crozetta, baseado em codigo do nfephp
// Data : 22/06/2017
// Descricao: Programa cria as chaves publica e privada (.pem) para o pfx.

// Modo de uso: Chamar este programa, passando como parametros o certificado e senha
//  php cria_pem.php 12345678901234.pfx senha123

$certificado = $argv[1];
$senha = $argv[2];
$dados_cert = [];
$priKey = explode('.', $certificado)[0]."_priKey.pem";
$pubKey = explode('.', $certificado)[0]."_pubKey.pem";
$certKey = explode('.', $certificado)[0]."_certKey.pem";

if(!file_exists($certificado)){
	echo "O arquivo informado nao existe";
	exit(1);
}
$key = file_get_contents($certificado);

//abre o certificado e carrega no $dados_cert
if (!openssl_pkcs12_read($key,$dados_cert,$senha)){
	echo "O arquivo nao pode ser lido. Verifique se a senha esta correta";
	exit(2);
}

// Verifica a validade do certificado
$data = openssl_x509_read($dados_cert['cert']);
$cert_data = openssl_x509_parse($data);
$ano = substr($cert_data['validTo'],0,2);
$mes = substr($cert_data['validTo'],2,2);
$dia = substr($cert_data['validTo'],4,2);
$validade = gmmktime(0,0,0,$mes,$dia,$ano);
$hoje = gmmktime(0,0,0,date("m"),date("d"),date("Y"));
if ($validade < $hoje) {
	echo "O certificado expirou em ".date("d/m/Y",$validade);
	exit(3);
}

if(file_exists($priKey)){
    //se existir verificar se é o mesmo
	$conteudo = file_get_contents($priKey);
	//comparar os primeiros 100 digitos
	if ( !substr($conteudo,0,100) == substr($dados_cert['pkey'],0,100) ) {
	    //se diferentes gravar o novo
		if (!file_put_contents($priKey,$dados_cert['pkey']) ){
			echo "Nao foi possivel subsituir a chave privada em arquivo. Verifique a permissao do arquivo";
			exit(4);
		}
	}
} else {
	//salva a chave privada no formato pem para uso so SOAP
	if(!file_put_contents($priKey,$dados_cert['pkey'])){
		echo "Nao foi possivel gravar a chave privada em arquivo. Verifique a permissao do arquivo";
			exit(5);
	}
}
//verifica se arquivo com a chave publica já existe
if(file_exists($pubKey)){
    //se existir verificar se é o mesmo atualmente instalado
	$conteudo = file_get_contents($pubKey);
    //comparar os primeiros 100 digitos
	if ( !substr($conteudo,0,100) == substr($dados_cert['cert'],0,100) ) {
		//se diferentes gravar o novo
		$n = file_put_contents($pubKey,$dados_cert['cert']);
        //salva o certificado completo no formato pem
		$n = file_put_contents($certKey,$dados_cert['pkey']."\r\n".$dados_cert['cert']);
	}
} else {
    //se não existir salva a chave publica no formato pem para uso do SOAP
	$n = file_put_contents($pubKey,$dados_cert['cert']);
    //salva o certificado completo no formato pem
	$n = file_put_contents($certKey,$dados_cert['pkey']."\r\n".$dados_cert['cert']);
}

?>