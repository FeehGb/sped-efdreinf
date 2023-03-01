<?php

#namespace Exemplo;

require '../vendor/autoload.php';

class MySetup extends Sped\Gnre\Configuration\Setup
{

    public function getBaseUrl()
    {
    }

    public function getCertificateCnpj()
    {
        return 01631022000112;
    }

    public function getCertificateDirectory()
    {
        #return "/var/www/html/nf/nfse/certificados/";
        return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/";
    }

    public function getCertificateName()
    {
        #return "/var/www/html/nf/nfse/certificados/01631022000112.pfx";
        return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/certHomologa.cer";
    }

    public function getCertificatePassword()
    {
        return "kbiciber";
    }

    public function getCertificatePemFile()
    {
        #return "/var/www/html/nf/nfse/certificados/01631022000112_certKey.pem";
        #return "/var/www/html/nf/nfse/certificados/certFelipe.pem";
        return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/certHomologa.pem";
    }

    public function getEnvironment()
    {
        return 2;
    }

    public function getPrivateKey()
    {
        #return "/var/www/html/nf/nfse/certificados/01631022000112_priKey.pem";
        return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/privatekey.pem";
    }

    public function getProxyIp()
    {
    }

    public function getProxyPass()
    {
    }

    public function getProxyPort()
    {
    }

    public function getProxyUser()
    {
    }

    public function getDebug()
    {
        return true;
    }
}

$xml = file_get_contents('estrutura-lote-completo-gnre.xml');


$minhaConfiguracao = new MySetup();

$guia = new Sped\Gnre\Sefaz\Guia();

$lote = new Sped\Gnre\Sefaz\Lote();
#$lote->utilizarAmbienteDeTeste(true); #Descomente essa linha para utilizar o ambiente de testes

$lote->addGuia($guia);

$webService = new Sped\Gnre\Webservice\Connection($minhaConfiguracao, $lote->getHeaderSoap(), $lote->toXml());
echo $webService->doRequest($lote->soapAction());
