<?php
    
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    require_once '../../../bootstrap.php';
    
    use NFePHP\Common\Certificate;
    use JsonSchema\Validator;
    use NFePHP\EFDReinf\Event;
    use NFePHP\EFDReinf\Tools;
    use NFePHP\EFDReinf\Common\FakePretty;
    use NFePHP\EFDReinf\Common\Soap\SoapFake;
    use NFePHP\Common\Signer;
    use NFePHP\Common\Strings;
    
    $__senha       = $_REQUEST['senha']       ;
    $__certificado = $_REQUEST['certificado'] ;
    $__nome        = $_REQUEST['nome']        ;
    $__xml         = $_REQUEST['xml']         ;
    
    $content = base64_decode($__certificado);
    $password = $__senha;
    $certificate = Certificate::readPfx($content, $password);
    
    $__xml = Strings::clearXmlString($__xml);
    
    $__xml = Signer::sign(
        $certificate            ,
        $__xml                  ,
        $__nome                 ,
        'id'                    ,
        OPENSSL_ALGO_SHA256     ,
        [true, false, null, null]
    );
    
    $__xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $__xml);
    
    echo $__xml ;
    
    