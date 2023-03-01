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

$guia = new Sped\Gnre\Sefaz\Guia();

$consulta = new Sped\Gnre\Sefaz\Consulta();
$consulta->setRecibo(1911525291);

/**
 * O número que representa em qual ambiente sera realizada a consulta
 * 1 - produção 2 - homologação
 */
$consulta->setEnvironment(1);
#$consulta->utilizarAmbienteDeTeste(true); //Descomente essa linha para utilizar o ambiente de testes

//header('Content-Type: text/xml');
//print $consulta->toXml(); // exibe o XML da consulta

$webService = new Sped\Gnre\Webservice\Connection($minhaConfiguracao, $consulta->getHeaderSoap(), $consulta->toXml());
echo $webService->doRequest($consulta->soapAction());
