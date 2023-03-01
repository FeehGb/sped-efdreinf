<?php

/* 
    /*
        Programa:  gnre-envio.php
        Descricão: Programa responsabel para o envio de Gnre
        Autor:     Felipe Basilio
        Modo de uso: 
            php /var/www/html/nf/NF_V3/NF_V3/interfaces/gnre-consulta.php 27404236000140 2229548557 /temp/lixo.txt.txt 1 true 
            00257348000169
            27440377000118
            

    */


if (!isset($argv[1])) {
    echo "
        ENTRADA:
            argv[1] = cnpj                             ; 
            argv[2] = recibo                           ; 
            argv[3] = arquivo_saida                    ; 
            argv[4]*= ambiente              |        2 ; 
            argv[5]*= debug                 |     true ; 
            
        
        SAIDA:
            WE-RET-CODIGO         // codigo            ; 
            WE-RET-DESCRICAO      // descricao         ; 
            WE-RET-NUMERO         // numeroRecibo      ; 
            WE-RET-RESPOSTA       // str_cobol         ;
                                  // c01_UfFavorecida
                                  // c02_receita
                                  // c25_detalhamentoReceita
                                  // c26_produto
                                  // c27_tipoIdentificacaoEmitente
                                  // c03_idContribuinteEmitente
                                  // c28_tipoDocOrigem
                                  // c04_docOrigem
                                  // c06_valorPrincipal
                                  // c10_valorTotal
                                  // c14_dataVencimento
                                  // c15_convenio
                                  // c16_razaoSocialEmitente
                                  // c17_inscricaoEstadualEmitente
                                  // c18_enderecoEmitente
                                  // c19_municipioEmitente
                                  // c20_ufEnderecoEmitente
                                  // c21_cepEmitente
                                  // c22_telefoneEmitente
                                  // c34_tipoIdentificacaoDestinatario
                                  // c35_idContribuinteDestinatario
                                  // c36_inscricaoEstadualDestinatario
                                  // c37_razaoSocialDestinatario
                                  // c38_municipioDestinatario
                                  // c33_dataPagamento
                                  // periodo
                                  // mes
                                  // ano
                                  // parcela
                                  // c39_camposExtras
                                  // c42_identificadorGuia
                                  // retornoInformacoesComplementares
                                  // retornoAtualizacaoMonetaria
                                  // retornoJuros
                                  // retornoMulta
                                  // retornoRepresentacaoNumerica
                                  // retornoCodigoDeBarras
                                  // retornoSituacaoGuia
                                  // retornoSequencialGuia
                                  // retornoErrosDeValidacaoCampo
                                  // retornoErrosDeValidacaoCodigo
                                  // retornoErrosDeValidacaoDescricao
                                  // retornoNumeroDeControle
        
    \n";
    exit();
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


$config = parse_ini_file("../config/config.ini");
$temp = $config['temp'] . "gnre/";
// Dados que são passados pelo programa chamador (cobol)
$cnpj            = $argv[1];
$recibo          = $argv[2];
$arquivo_saida   = $argv[3];
$ambiente        = (isset($argv[4]) ? $argv[4] : 2);
$debug           = (isset($argv[5]) ? $argv[5] : false);

$pdf_saida = $arquivo_saida . ".pdf";

$cli_config = parse_ini_file("{$config['dados']}/config_cliente/" . $cnpj . ".ini", true);
$certificados_path = $cli_config['certificado']['caminho_certificado'];

#print($cli_config['certificado']['arquivo_certificado']);exit();

#namespace Exemplo;

require "../servicos/gnre/vendor/autoload.php";

// use Sped\Gnre\Parser\SefazRetorno;

// /var/www/html/nf/NF_V3/NF_V3/servicos/gnre
class MySetup extends Sped\Gnre\Configuration\Setup
{

    function __construct($args)
    {

        #var_dump($args);
        $this->args = $args;
        $this->setDefault();

        $this->BaseUrl              = $this->args['BaseUrl'];
        $this->CertificateCnpj      = $this->args['CertificateCnpj'];
        $this->CertificateDirectory = $this->args['CertificateDirectory'];
        $this->CertificateName      = $this->args['CertificateName'];
        $this->CertificatePassword  = $this->args['CertificatePassword'];
        $this->CertificatePemFile   = $this->args['CertificatePemFile'];
        $this->Environment          = $this->args['Environment'];
        $this->PrivateKey           = $this->args['PrivateKey'];
        $this->ProxyIp              = $this->args['ProxyIp'];
        $this->ProxyPass            = $this->args['ProxyPass'];
        $this->ProxyPort            = $this->args['ProxyPort'];
        $this->ProxyUser            = $this->args['ProxyUser'];
    }

    private function setDefault()
    {
        $default =
            [
                'BaseUrl'               => false 
            ,   'CertificateCnpj'       => false   
            ,   'CertificateDirectory'  => false  
            ,   'CertificateName'       => false   
            ,   'CertificatePassword'   => false   
            ,   'CertificatePemFile'    => false   
            ,   'Environment'           => false   
            ,   'PrivateKey'            => false   
            ,   'ProxyIp'               => false   
            ,   'ProxyPass'             => false   
            ,   'ProxyPort'             => false   
            ,   'ProxyUser'             => false
            ];

        foreach ($default as $key => $value) {
            if (!array_key_exists($key, $this->args)) {
                $this->args[$key] = $value;
            }
        }
    }

    public function getBaseUrl()
    {
        if ($this->BaseUrl) {
            return $this->BaseUrl;
        }
    }

    public function getCertificateCnpj()
    {
        return $this->CertificateCnpj; #01631022000112

    }

    public function getCertificateDirectory()
    {
        return $this->CertificateDirectory;
    }

    public function getCertificateName()
    {
        return $this->CertificateName;
    }

    public function getCertificatePassword()
    {

        return $this->CertificatePassword; #"kbiciber";
    }

    public function getCertificatePemFile()
    {
        return $this->CertificatePemFile;
    }

    public function getEnvironment()
    {
        return $this->Environment; #        return 2;

    }

    public function getPrivateKey()
    {
        return $this->PrivateKey;
        #return "/var/www/html/nf/nfse/certificados/01631022000112_priKey.pem";
        #return "/var/www/html/webservice/sefaz/gnre2/certs/metadata/privatekey.pem";
    }

    public function getProxyIp()
    {
        if ($this->ProxyIp) {
            return $this->ProxyIp;
        }
    }

    public function getProxyPass()
    {
        if ($this->ProxyPass) {
            return $this->ProxyPass;
        }
    }

    public function getProxyPort()
    {
        if ($this->ProxyPort) {
            return $this->ProxyPort;
        }
    }

    public function getProxyUser()
    {
        if ($this->ProxyUser) {
            return $this->ProxyUser;
        }
    }

    public function getDebug()
    {
        return true;
    }
}


function mask($val, $mask)
{
    $maskared = '';
    
    //print(strlen($val));
    
    if(!strlen($val)){
        return '';
    }
    
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; $i++) {
        if ($mask[$i] == '#') {
            if (isset($val[$k]))
                $maskared .= $val[$k++];
        } else {
            if (isset($mask[$i]))
                $maskared .= $mask[$i];
        }
    }
    return $maskared;
}


function convertMunicipios($estado, $codigoCidade){
    $csv = file_get_contents("/var/www/html/nf/NF_V3/NF_V3/config/cidadeIBGE.csv");
    $regex = "$estado\;$codigoCidade\;(.*)";
    preg_match("/$regex/",$csv,$match);
    
    return count($match) ? $match[1] : $codigoCidade;
    
    
}


//! mudar para arquivo 
function extractFromXml($paths, $xml, $index = [0])
{

    $paths = explode("/", $paths);
    $result = "";
    $key = 0;
    $start  =  $index == "*" ? 0 : $index[0];
    
    while (true) {
        
        $element        = $paths[$key];
        $element_data   = explode("@", $element, 2);
        $tag            = $element_data[0];
        
        #var_dump($element_data);
        array_shift($element_data);
        
        $regex_attr  = count($element_data) >= 1 ? implode("|", $element_data) : ".*?";
        
        
        $regex = "<{$tag}\s?(?:{$regex_attr})>(.*?)<\/$tag>";
        
        preg_match_all("/$regex/s", $xml, $match);
        //print((count($index) == 2 or $index=="*") and $key+1 === count($match[1]));
        
        if ((count($index) == 2 or $index == "*") and $key + 1 === count($paths)) {
            
            $end    = count($index) > 1 ? $index[2] : count($match[1]);
            $result = implode(" & ", array_slice($match[1], $start, $end));
        } else {
            $result = count($match[1]) ? $match[1][$start] : "";
            //var_dump($regex);
        }
        
        $xml = $result;
        if ($key + 1 == count($paths)) {
            break;
        } else {
            $key++;
        }
    }
    
    return $xml;
    
}

function is_valid_xml($xml)
{
    libxml_use_internal_errors(true);
    $doc = new DOMDocument('1.0', 'utf-8');
    $doc->loadXML($xml);
    $errors = libxml_get_errors();
    return empty($errors);
}
$args = array(
    #    "BaseUrl"               => false
    "CertificateCnpj"        => $cnpj   
    ,"CertificateDirectory"  => $certificados_path   
    ,"CertificateName"       => "{$certificados_path}{$cnpj}.pfx"   
    ,"CertificatePassword"   => $cli_config['certificado']['senha']   
    ,"CertificatePemFile"    => "{$certificados_path}{$cnpj}_certKey.pem"   
    ,"Environment"           => $ambiente   
    ,"PrivateKey"            => "{$certificados_path}{$cnpj}_priKey.pem"

);

$minhaConfiguracao = new MySetup($args);



$guia = new Sped\Gnre\Sefaz\Guia();

$consulta = new Sped\Gnre\Sefaz\Consulta();
$consulta->setRecibo($recibo); #1911525291

/**
 * O número que representa em qual ambiente sera realizada a consulta
 * 1 - produção 2 - homologação
 */
$consulta->setEnvironment($ambiente);
//$consulta->utilizarAmbienteDeTeste($ambiente == '2' ? true : false); //Descomente essa linha para utilizar o ambiente de testes

//header('Content-Type: text/xml');
#print $consulta->toXml();exit(); // exibe o XML da consulta



$webService = new Sped\Gnre\Webservice\Connection($minhaConfiguracao, $consulta->getHeaderSoap(), $consulta->toXml());
$respostaSefaz = $webService->doRequest($consulta->soapAction());

// !HABILITAR PARA DEBUG
//$respostaSefaz = file_get_contents("/var/www/html/nf/NF_V3/NF_V3/servicos/gnre/doc/retorno_consulta_lote.txt");


/*
$respostaSefaz_loaded = simplexml_load_string($respostaSefaz);
var_dump($respostaSefaz_loaded); */
//020163102200011219115252910100011SP10008020002336124000178KOMATSU BRASIL INTERNATIONAL LTDAAVENIDA MANUEL BANDEIRA, 291, BLOCO D TE  SAO PAULO  SP053170200110210580000000000000000000  PILHAS, BATERIAS ELETRICAS E ACUMULADORES ELETRICOS000000000000232191ICMS DE SUBSTITUICAO DE MATERI2510201900000000010201900000000000002944400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000300000000000000000000000000020001c01_UfFavorecida  201Esta UF nao gera GNRE online

$is_valid_xml = is_valid_xml($respostaSefaz);



#print($is_valid_xml);exit();


if ($is_valid_xml) {

    $txtToCobol = array();
    $guia_arr = array();


    $codigo              = extractFromXml("ns1:codigo"          , $respostaSefaz);
    $descricao           = extractFromXml("ns1:descricao"       , $respostaSefaz);
    $numeroRecibo        = extractFromXml("ns1:numeroRecibo"    , $respostaSefaz);
    $resultado           = extractFromXml("ns1:resultado"       , $respostaSefaz);
    
    //print(extractFromXml('ns1:valor@tipo="11"',$resultado));exit();
    //print($respostaSefaz);exit();
    //print(">>>>>>>>>>>>>>>>>>>>>>>>>>>\n");
    //print(extractFromXml("ns1:resultado"       , $respostaSefaz));exit();
    //print(extractFromXml("ns1:documentoOrigem", $respostaSefaz));exit();

    if ($codigo == "402") {

        //$consulta = new Sped\Gnre\Sefaz\Consulta();
        //$parser =  new SefazRetorno($resultado);
        /* str_replace( 
            array("ns1:"), 
            array(""),
            $respostaSefaz
        ); */
        //$xmlSefaz = simplexml_load_string($respostaSefaz);
        
        //$guia =  $parser->getLote()->getGuia(0);
        
        $guia = new Sped\Gnre\Sefaz\Guia();
        //print($resultado);exit();
        /* tratamento valor */
        
        // Valor principal
        $valor_tipo11 = (string)  extractFromXml("ns1:itensGNRE/ns1:item/ns1:valor@tipo=\"11\""  , $resultado);
        $valor_tipo12 = (string)  extractFromXml("ns1:itensGNRE/ns1:item/ns1:valor@tipo=\"12\""  , $resultado);
        
        
        // valor total da GNRE
        $valor_GNRE   = (string)  extractFromXml("ns1:valorGNRE"                                 , $resultado);
        //$c10_valorTotal = !empty($valor_tipo21)  ? $valor_tipo21 : $valor_GNRE                                 ;
        
        
        /*  tratamento CNPJ  destinatario emitete */
        $inscricaoE = (string) mask(extractFromXml("ns1:contribuinteEmitente/ns1:identificacao/ns1:IE"        , $resultado),'###.###.###.###');
        $cnpjE      = (string) mask(extractFromXml("ns1:contribuinteEmitente/ns1:identificacao/ns1:CNPJ"      , $resultado),'##.###.###/####-##');
        $cpfE       = (string) mask(extractFromXml("ns1:contribuinteEmitente/ns1:identificacao/ns1:CPF"       , $resultado),'###.###.###-##');
        $c03_idContribuinteEmitente = !empty($inscricaoE)? $inscricaoE : (!empty($cnpjE) ? $cnpjE : $cpfE);
        
        /* tratamento CNPJ  destinatario*/
        $inscricaoD = (string) mask(extractFromXml("ns1:contribuinteDestinatario/ns1:identificacao/ns1:IE"        , $resultado),'###.###.###.###');
        $cnpjD      = (string) mask(extractFromXml("ns1:contribuinteDestinatario/ns1:identificacao/ns1:CNPJ"      , $resultado),'##.###.###/####-##');
        $cpfD       = (string) mask(extractFromXml("ns1:contribuinteDestinatario/ns1:identificacao/ns1:CPF"       , $resultado),'###.###.###-##');
        $c35_idContribuinteDestinatario = !empty($inscricaoD)? $inscricaoD : (!empty($cnpjD) ? $cnpjD : $cpfD);
        
        //echo $c35_idContribuinteDestinatario ;return ;
        
        #require "gerar-gnre-pdf.php";
        $guia_arr[] = $guia->c01_UfFavorecida                   =  (string)     extractFromXml("ns1:ufFavorecida"                                                        , $resultado);
        $guia_arr[] = $guia->c02_receita                        =  (string)     extractFromXml("ns1:item/ns1:receita"                                                     , $resultado);
        $guia_arr[] = $guia->c26_produto                        =  (string)     extractFromXml("ns1:produto"                                                              , $resultado);
        //$guia_arr[] = $guia->c27_tipoIdentificacaoEmitente      =  (string)   1; //revisar
        $guia_arr[] = $guia->c03_idContribuinteEmitente         =  $c03_idContribuinteEmitente;
        $guia_arr[] = $guia->c28_tipoDocOrigem                  =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:documentoOrigem"                                 , $resultado);
        $guia_arr[] = $guia->c04_docOrigem                      =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:documentoOrigem"                                 , $resultado);
        $guia_arr[] = $guia->c06_valorPrincipal                 =  !empty($valor_tipo12)? $valor_GNRE:$valor_tipo11;
        $guia_arr[] = $guia->c10_valorTotal                     =  $valor_GNRE;
        $guia_arr[] = $guia->c14_dataVencimento                 =  (string)   date("d/m/Y", strtotime(extractFromXml("ns1:itensGNRE/ns1:item/ns1:dataVencimento"          , $resultado)));
        $guia_arr[] = $guia->c15_convenio                       =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:convenio"                                        , $resultado);
        $guia_arr[] = $guia->c16_razaoSocialEmitente            =  (string)   extractFromXml("ns1:contribuinteEmitente/ns1:razaoSocial"                                   , $resultado);
        $guia_arr[] = $guia->c18_enderecoEmitente               =  (string)   extractFromXml("ns1:contribuinteEmitente/ns1:endereco"                                      , $resultado);
        $guia_arr[] = $guia->c19_municipioEmitente              =  (string)   convertMunicipios(
                                                                                extractFromXml("ns1:contribuinteEmitente/ns1:uf"                                          , $resultado),
                                                                                extractFromXml("ns1:contribuinteEmitente/ns1:municipio"                                   , $resultado)
                                                                            );
        $guia_arr[] = $guia->c20_ufEnderecoEmitente             =  (string)   extractFromXml("ns1:contribuinteEmitente/ns1:uf"                                            , $resultado);
        $guia_arr[] = $guia->c21_cepEmitente                    =  (string)   mask(extractFromXml("ns1:contribuinteEmitente/ns1:cep"                                      , $resultado),'#####-###');
        $guia_arr[] = $guia->c22_telefoneEmitente               =  (string)   extractFromXml("ns1:contribuinteEmitente/ns1:telefone"                                      , $resultado);
        
        $guia_arr[] = $guia->c35_idContribuinteDestinatario     =  $c35_idContribuinteDestinatario;
        
        $guia_arr[] = $guia->c33_dataPagamento                  =  (string)   date("d/m/Y", strtotime(extractFromXml("ns1:dataPagamento"                                  , $resultado)));
        $guia_arr[] = $guia->mes                                =  (string)   str_pad(extractFromXml("ns1:item/ns1:referencia/ns1:mes"                                    , $resultado),2,"0", STR_PAD_LEFT);
        $guia_arr[] = $guia->ano                                =  (int)      extractFromXml("ns1:item/ns1:referencia/ns1:ano"                                            , $resultado);
        $guia_arr[] = $guia->periodo                            =  (int)      extractFromXml("ns1:item/ns1:referencia/ns1:periodo"                                        , $resultado);
        $guia_arr[] = $guia->parcela                            =  (int)      extractFromXml("ns1:item/ns1:referencia/ns1:parcela"                                        , $resultado); // !talvez mudar para int
        $guia_arr[] = $guia->c25_detalhamentoReceita            =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:detalhamentoReceita"                             , $resultado);
        $guia_arr[] = $guia->c17_inscricaoEstadualEmitente      =  (string)   mask(extractFromXml("ns1:contribuinteEmitente/ns1:identificacao/ns1:IE"                     , $resultado),'###.###.###.###') ;
        $guia_arr[] = $guia->c36_inscricaoEstadualDestinatario  =  (string)   mask(extractFromXml("ns1:contribuinteDestinatario/ns1:identificacao/ns1:IE"                , $resultado),'###.###.###.###') ;
        $guia_arr[] = $guia->c37_razaoSocialDestinatario        =  (string)   extractFromXml("ns1:contribuinteDestinatario/ns1:razaoSocial"                              , $resultado);
        
        
        
        
        //echo  convertMunicipios(".*",extractFromXml("ns1:contribuinteDestinatario/ns1:municipio"              , $resultado)); return ;
        $guia_arr[] = $guia->c38_municipioDestinatario          =  (string)   convertMunicipios(
                                                                                extractFromXml("ns1:ufFavorecida"                                                        , $resultado)
                                                                            ,   extractFromXml("ns1:contribuinteDestinatario/ns1:municipio"                              , $resultado));
        //$guia_arr[] = $guia->c34_tipoIdentificacaoDestinatario  =  1; // revisar
        
        $informacoesComplementares = (string)   extractFromXml("ns1:informacoesComplementares/ns1:informacao"                               , $resultado, '*');
        echo "$informacoesComplementares";
        $guia_arr[] = $guia->retornoInformacoesComplementares   =  str_replace("\\$","$",implode("<br />",explode(" & ",$informacoesComplementares)));
        $guia_arr[] = $guia->retornoAtualizacaoMonetaria        =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:valor@tipo=\"51\""                               , $resultado);
        $guia_arr[] = $guia->retornoNumeroDeControle            =  (string)   extractFromXml("ns1:nossoNumero"                                                                 , $resultado);
        $guia_arr[] = $guia->retornoCodigoDeBarras              =  (string)   extractFromXml("ns1:codigoBarras"                                                           , $resultado);
        $guia_arr[] = $guia->retornoRepresentacaoNumerica       =  (string)   extractFromXml("ns1:linhaDigitavel"                                                         , $resultado);
        $guia_arr[] = $guia->retornoJuros                       =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:valor@tipo=\"41\""                               , $resultado); 
        $guia_arr[] = $guia->retornoMulta                       =  (string)   extractFromXml("ns1:itensGNRE/ns1:item/ns1:valor@tipo=\"31\""                               , $resultado); 
        
        
        //print($resultado);
        //print_r($guia);exit();
        
        $lote = new Sped\Gnre\Sefaz\Lote();
        $lote->addGuia($guia);
        
        $html = new Sped\Gnre\Render\Html();
        $html->create($lote);
        
        $pdf = new Sped\Gnre\Render\Pdf();

        file_put_contents($pdf_saida, $pdf->create($html)->output(array('Attachment' => 1)));
    }
    $str_cobol = "";
    
    #file_put_contents("lixo.txt",implode(";",$pipe_guia));
    
    #print($pipe_guia);
    
    
    
    
    $str_cobol = implode('|', $guia_arr);
    #$txtToCobol[] = $pdf_saida;
    
    $motivo             = []        ;
    $txtToCobol[]       = $codigo   ;
    $motivosRejeicao    = $codigo == "403" ? extractFromXml("ns1:motivosRejeicao" , $resultado ): false;
    
    if ($motivosRejeicao){
        $motivo[]  =  extractFromXml("ns1:codigo"    , $motivosRejeicao );
        $motivo[]  =  extractFromXml("ns1:descricao" , $motivosRejeicao );
        $motivo[]  =  extractFromXml("ns1:campo"     , $motivosRejeicao );
        $descricao .= ' - '.$motivo[1];
    }
    $txtToCobol[] = $descricao;
    $txtToCobol[] = $numeroRecibo;
    
    $txtToCobol[] = implode(";", $motivo);
    // Motivo da rejeição
    //$txtToCobol[] = $codigo == "403" ? extractFromXml("ns1:motivosRejeicao" , $respostaSefaz): "";
    // Motivo da rejeição
    
    $txtToCobol[] = $str_cobol;
    $txtToCobol[] = "|";

    $txtToCobol = implode("|", $txtToCobol);
    #print_r($str_cobol);exit();


    if ($debug) {

        #file_put_contents("lixo.txt");
        print("\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n");
        print_r($txtToCobol);
        //print($respostaSefaz);
        print("\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n");
        print($respostaSefaz);

    }
} else {
    $txtToCobol = "000|Falha no envio||||";
}

if (!$debug) {
    file_put_contents($arquivo_saida, $txtToCobol);
}
