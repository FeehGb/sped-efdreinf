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
        return "/var/www/html/nf/nfse/certificados/";
    }

    public function getCertificateName()
    {
        return "/var/www/html/nf/nfse/certificados/01631022000112.pfx";
    }

    public function getCertificatePassword()
    {
        return "kbiciber";
    }

    public function getCertificatePemFile()
    {
        return "/var/www/html/nf/nfse/certificados/certFelipe.pem";
    }

    public function getEnvironment()
    {
        return 1;
    }

    public function getPrivateKey()
    {
        return "/var/www/html/nf/nfse/certificados/01631022000112_priKey.pem";
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
}

$minhaConfiguracao = new MySetup();

$config = new \Sped\Gnre\Sefaz\ConfigUf;

/**
 * Qual ambiente sera realizada a consulta
 */
$config->setEnvironment(1);
$config->setReceita(100099);
$config->setEstado('PR');


$webService = new Sped\Gnre\Webservice\Connection($minhaConfiguracao, $config->getHeaderSoap(), $config->toXml());

$consulta = $webService->doRequest($config->soapAction());
echo '<pre>';
echo htmlspecialchars($consulta);
