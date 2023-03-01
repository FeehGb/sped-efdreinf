<?php
/* 
    /*
        Programa:  gnre-envio.php
        Descricão: Programa responsabel para o envio de Gnre
        Autor:     Felipe Basilio
        Modo de uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/gnre-envio.php 00257348000169 /home/felipe/arquivos-testes/GNRE-SP-0002321912.xml /home/felipe/gnre/envioResultado.txt 2 true 

    */
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = cnpj                             ; 
                argv[2] = xml-gnre                         ; 
                argv[3] = arquivo de saida                 ; 
                argv[4]*= ambiente              |        2 ; 
                argv[5]*= debug                 |     true ; 
                
            
            SAIDA:
                WE-RET-CODIGO         // codigo              ; 
                WE-RET-DESCRICAO      // descricao           ; 
                WE-RET-NUMERO         // numero              ; 
                WE-RET-DHRECIBO       // dataHoraRecibo      ; 
                WE-RET-TMPPROC        // tempoEstimadoProc   ; 
                WE-RET-XML-INTEGRAL   // xml_retorno         ; 
            
        \n"; exit() ;
    }
chdir(__DIR__); //Este comando é necessário para ir até o diretório do programa 
require_once("../funcoes/flog.php"); // para gravar log
require_once("../funcoes/fdebug.php"); // Para realizar debug
require_once("../funcoes/freplace.php"); // Replace de dados
require_once("../funcoes/txt2xml.php"); // para converter mdfe txt para xml
require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
require_once("../classes/validaXml.php"); // Usado para validar o xml
require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
require_once("../classes/codigosUf.php"); // Usado para retornar o código uf, baseado na sigla
require_once("../ferramentas/formatXML.php"); //

// Carrega as configurações de clientes e sistema

$config = parse_ini_file("../config/config.ini");
$temp = $config['temp'] . "gnre/";
// Dados que são passados pelo programa chamador (cobol)
$cnpj          = $argv[1];
$arquivo_xml   = $argv[2];
$arquivo_saida = $argv[3];
$ambiente      = (isset($argv[4]) ? $argv[4]: 2    );
$debug         = (isset($argv[5]) ? true: false);

#! PARA DEBUG
//file_put_contents("/user/transf/gnre-comando.txt", $cnpj. " - ". $arquivo_xml . " - " . $arquivo_saida. " - ". $ambiente);

//$xml = simplexml_load_file($arquivo_xml);
//$xml = simplexml_load_file($arquivo_xml);

//$xml = simplexml_load_file($arquivo_xml);
$xml = simplexml_load_file($arquivo_xml);
if(!$xml){
    echo ">>>>>>>>>>>>>>>>>>>>>>>>>>>>
        Falha ao tentar recuperar XML | Path -> $arquivo_xml";
    exit();
    
}


$gnre_dados = $xml->guias->TDadosGNRE;

$cli_config = parse_ini_file("{$config['dados']}/config_cliente/".$cnpj.".ini", true);
$certificados_path = $cli_config['certificado']['caminho_certificado'];

function cria_diretorios($dir)
{
    exec('php ../ferramentas/cria_diretorios.php ' . $dir);
}


exec("php ../ferramentas/cria_pem.php ".$cli_config['certificado']['arquivo_certificado']." ".$cli_config['certificado']['senha']);
//exec("openssl pkcs12 -in {$cli_config['certificado']['arquivo_certificado']} -nokeys -out {$certificados_path}{$cnpj}_certKey.pem -password pass:{$cli_config['certificado']['senha']}");

#print($cli_config['certificado']['arquivo_certificado']);exit();
require "../servicos/gnre/vendor/autoload.php";
//require "../servicos/gnre_v2/sped-gnre/vendor/autoload.php";
#require '../vendor/autoload.php';

class MySetup extends Sped\Gnre\Configuration\Setup
{
    private $args = array();
    
    
    function __construct($args) {
        
        #var_dump($args);
        $this->args = $args;
        $this->setDefault();
        $this->BaseUrl              = $this->args['BaseUrl'              ] ;
        $this->CertificateCnpj      = $this->args['CertificateCnpj'      ];
        $this->CertificateDirectory = $this->args['CertificateDirectory' ];
        $this->CertificateName      = $this->args['CertificateName'      ];
        $this->CertificatePassword  = $this->args['CertificatePassword'  ];
        $this->CertificatePemFile   = $this->args['CertificatePemFile'   ];
        $this->Environment          = $this->args['Environment'          ];
        $this->PrivateKey           = $this->args['PrivateKey'           ];
        $this->ProxyIp              = $this->args['ProxyIp'              ];
        $this->ProxyPass            = $this->args['ProxyPass'            ];
        $this->ProxyPort            = $this->args['ProxyPort'            ];
        $this->ProxyUser            = $this->args['ProxyUser'            ];
        
        
    }
    
    private function setDefault(){
        $default =
        [
                'BaseUrl'             => NULL 
            ,   'CertificateCnpj'     => NULL 
            ,   'CertificateDirectory'=> NULL 
            ,   'CertificateName'     => NULL 
            ,   'CertificatePassword' => NULL 
            ,   'CertificatePemFile'  => NULL 
            ,   'Environment'         => NULL 
            ,   'PrivateKey'          => NULL 
            ,   'ProxyIp'             => NULL 
            ,   'ProxyPass'           => NULL 
            ,   'ProxyPort'           => NULL 
            ,   'ProxyUser'           => NULL 
        ];
        
        foreach ($default as $key => $value) {
            if (!array_key_exists($key, $this->args)){
                $this->args[$key] = $value;
            }
            
        }
    }
    
    public function getBaseUrl()
    {
        if($this->BaseUrl){
            return $this->BaseUrl;
            
        }
    }

    public function getCertificateCnpj()
    {
        return $this->CertificateCnpj;#01631022000112
        
    }

    public function getCertificateDirectory()
    {
        return $this->CertificateDirectory;
        #return "/var/www/html/nf/nfse/certificados/";
        #return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/";
    }

    public function getCertificateName()
    {
        return $this->CertificateName;
        #return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/certHomologa.cer";
    }

    public function getCertificatePassword()
    {

        return $this->CertificatePassword;#"kbiciber";
    }

    public function getCertificatePemFile()
    {
        return $this->CertificatePemFile;
        #return "/var/www/html/nf/nfse/certificados/01631022000112_certKey.pem";
        #return "/var/www/html/nf/nfse/certificados/certFelipe.pem";
        #return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/certHomologa.pem";
    }

    public function getEnvironment()
    {
        return $this->Environment;#        return 2;
        
    }

    public function getPrivateKey()
    {
        return $this->PrivateKey;
        #return "/var/www/html/nf/nfse/certificados/01631022000112_priKey.pem";
        #return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/privatekey.pem";
    }

    public function getProxyIp()
    {
        if ($this->ProxyIp){
            return $this->ProxyIp;
            
        }
    }

    public function getProxyPass()
    {
        if ($this->ProxyPass){
            return $this->ProxyPass;
            
        }
    }

    public function getProxyPort()
    {
        if ($this->ProxyPort){
            return $this->ProxyPort;
            
        }
    }

    public function getProxyUser()
    {
        if ($this->ProxyUser){
            return $this->ProxyUser;
            
        }
    }

    public function getDebug()
    {
        return true;
        
    }
}
/**
*  Takes XML string and returns a boolean result where valid XML returns true
*/
function is_valid_xml ( $xml ) {
    
    if(!$xml) {
        return 0;
    } 
    libxml_use_internal_errors( true );
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML( $xml );
    $errors = libxml_get_errors();
    return empty( $errors );
}

//! mudar para arquivo 
function extractFromXml($tag, $xml){
    $regex = "<{$tag}>(.*?)<\/$tag>";
    preg_match("/$regex/s", $xml, $match);
    #print_r( $match);
    if (count($match) > 1){
        return $match[1];
        
    }
    
    return "";
    
}




#$xml_to_send = file_get_contents('/home/felipe/arquivos-testes/GNRE-SP-0002321912-send.xml');
$args = array(
    #    "BaseUrl"               => false
        "CertificateCnpj"       => $cnpj
    ,   "CertificateDirectory"  => $certificados_path
    ,   "CertificateName"       => "{$certificados_path}{$cnpj}.pfx"
    ,   "CertificatePassword"   => $cli_config['certificado']['senha']
    ,   "CertificatePemFile"    => "{$certificados_path}{$cnpj}_certKey.pem"
    ,   "Environment"           => $ambiente
    ,   "PrivateKey"            => "{$certificados_path}{$cnpj}_priKey.pem"
    
);



$minhaConfiguracao = new MySetup($args);
#var_dump($minhaConfiguracao);exit();

/* $guia = new Sped\Gnre\Sefaz\Guia();
php /var/www/html/nf/NF_V3/NF_V3/interfaces/gnre-envio.php 82206004000195 /user/colonia/GNRE.ARQGNRE-000149314NE.xml /user/colonia/GNRE.ARQGNRE-RET-L-20200312-104827.txt 1    


$guia->c01_UfFavorecida                   =  (string)$gnre_dados->c01_UfFavorecida                     ;
$guia->c02_receita                        =  (string)$gnre_dados->c02_receita                          ;
$guia->c26_produto                        =  (string)$gnre_dados->c26_produto                          ;
$guia->c27_tipoIdentificacaoEmitente      =  (string)$gnre_dados->c27_tipoIdentificacaoEmitente        ;
$guia->c03_idContribuinteEmitente         =  (string)$gnre_dados->c03_idContribuinteEmitente->CNPJ     ;
$guia->c28_tipoDocOrigem                  =  (string)$gnre_dados->c28_tipoDocOrigem                    ;
$guia->c04_docOrigem                      =  (string)$gnre_dados->c04_docOrigem                        ;
$guia->c06_valorPrincipal                 =  (float)$gnre_dados->c06_valorPrincipal                    ;
$guia->c10_valorTotal                     =  (float)$gnre_dados->c10_valorTotal                        ;
$guia->c14_dataVencimento                 =  (string)$gnre_dados->c14_dataVencimento                   ;
$guia->c15_convenio                       =  (string)$gnre_dados->c15_convenio                         ;
$guia->c16_razaoSocialEmitente            =  (string)$gnre_dados->c16_razaoSocialEmitente              ;
$guia->c18_enderecoEmitente               =  (string)$gnre_dados->c18_enderecoEmitente                 ;
$guia->c19_municipioEmitente              =  (string)$gnre_dados->c19_municipioEmitente                ;
$guia->c20_ufEnderecoEmitente             =  (string)$gnre_dados->c20_ufEnderecoEmitente               ;
$guia->c21_cepEmitente                    =  (string)$gnre_dados->c21_cepEmitente                      ;
$guia->c22_telefoneEmitente               =  (string)$gnre_dados->c22_telefoneEmitente                 ;
$guia->c35_idContribuinteDestinatario     =  (string)$gnre_dados->c35_idContribuinteDestinatario->CNPJ ;
$guia->c33_dataPagamento                  =  (string)$gnre_dados->c33_dataPagamento                    ;
$guia->mes                                =  (string)$gnre_dados->c05_referencia->mes                  ;
$guia->ano                                =  (string)$gnre_dados->c05_referencia->ano                  ;
$guia->periodo                            =  (string)$gnre_dados->c05_referencia->periodo              ;
$guia->parcela                            =  (int)$gnre_dados->c05_referencia->parcela                 ;
$guia->c25_detalhamentoReceita            =  (string)$gnre_dados->c25_detalhamentoReceita              ;
$guia->c17_inscricaoEstadualEmitente      =  (int)$gnre_dados->c17_inscricaoEstadualEmitente           ;
$guia->c36_inscricaoEstadualDestinatario  =  (string)$gnre_dados->c36_inscricaoEstadualDestinatario    ;
$guia->c37_razaoSocialDestinatario        =  (string)$gnre_dados->c37_razaoSocialDestinatario          ;
$guia->c38_municipioDestinatario          =  (string)$gnre_dados->c38_municipioDestinatario            ;
$guia->c34_tipoIdentificacaoDestinatario  =  (string)$gnre_dados->c34_tipoIdentificacaoDestinatario    ;
$guia->retornoInformacoesComplementares   =  (string)$gnre_dados->retornoInformacoesComplementares     ;
$guia->retornoAtualizacaoMonetaria        =  (string)$gnre_dados->retornoAtualizacaoMonetaria          ;
$guia->retornoNumeroDeControle            =  (string)$gnre_dados->retornoNumeroDeControle              ;
$guia->retornoCodigoDeBarras              =  (string)$gnre_dados->retornoCodigoDeBarras                ;
$guia->retornoRepresentacaoNumerica       =  (string)$gnre_dados->retornoRepresentacaoNumerica         ;
$guia->retornoJuros                       =  (string)$gnre_dados->retornoJuros                         ;
$guia->retornoMulta                       =  (string)$gnre_dados->retornoMulta                         ; */

#print_r($guia);exit();
$lote = new Sped\Gnre\Sefaz\Lote();
//echo $ambiente;exit();
//$lote->utilizarAmbienteDeTeste($ambiente == '2' ? true : false); #Descomente essa linha para utilizar o ambiente de testes

#$lote->addGuia($guia);

#! PARA DEBUG
//file_put_contents("/user/transf/GNRE-ENVIO.txt", $xml);

#echo($lote->toXml2($xml));exit();
$webService = new Sped\Gnre\Webservice\Connection($minhaConfiguracao, $lote->getHeaderSoap(), $lote->toXml2($xml));
$respostaSefaz =  $webService->doRequest($lote->soapAction());
//var_dump( $respostaSefaz);exit();



/**
 * DEBBUG
 */




$respostaSefaz_test = '<?xml version="1.0" encoding="utf-8"?> <soapenv:Envelope xmlns:soapenv="http://www.w3.org/2003/05/soap-envelope" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> <soapenv:Body> <processarResponse xmlns="http://www.gnre.pe.gov.br/webservice/GnreLoteRecepcao"> <ns1:TRetLote_GNRE xmlns="http://www.gnre.pe.gov.br" xmlns:ns1="http://www.gnre.pe.gov.br"> <ns1:ambiente>1</ns1:ambiente> <ns1:situacaoRecepcao> <ns1:codigo>100</ns1:codigo> <ns1:descricao>Este lote j&#xE1; foi enviado e j&#xE1; foi processado com invalida&#xE7;&#xF5;es!</ns1:descricao> </ns1:situacaoRecepcao> <ns1:recibo> <ns1:numero>1911525291</ns1:numero> <ns1:dataHoraRecibo>2019-10-25 17:12:00</ns1:dataHoraRecibo> <ns1:tempoEstimadoProc>0</ns1:tempoEstimadoProc> </ns1:recibo> </ns1:TRetLote_GNRE> </processarResponse> </soapenv:Body> </soapenv:Envelope>';
//print(is_valid_xml($respostaSefaz));exit();

$respostaSefaz_ = str_replace( 
    array("ns1:","soapenv:","\n","\t","> ","   "), 
    array("","","","",">",""),
    $respostaSefaz
);
/*
$respostaSefaz_loaded = simplexml_load_string($respostaSefaz);
var_dump($respostaSefaz_loaded); */


$is_valid_xml = is_valid_xml($respostaSefaz);

if ($is_valid_xml){
    $txtToCobol = array();
    
    $codigo              = extractFromXml("ns1:codigo",$respostaSefaz)             ;//; exec("php ../ferramentas/f.texto.tag.php codigo '".$respostaSefaz."'");
    $descricao           = extractFromXml("ns1:descricao",$respostaSefaz)          ;//; exec("php ../ferramentas/f.texto.tag.php descricao '".$respostaSefaz."'");
    $numero              = extractFromXml("ns1:numero",$respostaSefaz)             ;//; exec("php ../ferramentas/f.texto.tag.php numero '".$respostaSefaz."'");
    $dataHoraRecibo      = extractFromXml("ns1:dataHoraRecibo",$respostaSefaz)     ;//; exec("php ../ferramentas/f.texto.tag.php dataHoraRecibo '".$respostaSefaz."'");
    $tempoEstimadoProc   = extractFromXml("ns1:tempoEstimadoProc",$respostaSefaz)  ;//; exec("php ../ferramentas/f.texto.tag.php tempoEstimadoProc '".$respostaSefaz."'");
    
    
    $txtToCobol[] = $codigo            ;
    $txtToCobol[] = $descricao         ;
    $txtToCobol[] = $numero            ;
    $txtToCobol[] = $dataHoraRecibo    ;
    $txtToCobol[] = $tempoEstimadoProc ;
    $txtToCobol[] = "|"                ;
    #$txtToCobol[] = $respostaSefaz     ;
    
    $txtToCobol = implode('|', $txtToCobol);
    
    
    
}else {
    $txtToCobol = "000|Falha no envio||||$respostaSefaz";
}

if (!$debug) {
    file_put_contents($arquivo_saida,$txtToCobol);
} else {
    print($txtToCobol);
    formatXML($respostaSefaz);
}
