<?php
/**
 * Este arquivo é parte do projeto NFePHP - Nota Fiscal eletrônica em PHP.
 *
 * Este programa é um software livre: você pode redistribuir e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU (GPL)como é publicada pela Fundação
 * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior
 * e/ou 
 * sob os termos da Licença Pública Geral Menor GNU (LGPL) como é publicada pela Fundação
 * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
 *
 *
 * Este programa é distribuído na esperança que será útil, mas SEM NENHUMA
 * GARANTIA; nem mesmo a garantia explícita definida por qualquer VALOR COMERCIAL
 * ou de ADEQUAÇÃO PARA UM PROPÓSITO EM PARTICULAR,
 * veja a Licença Pública Geral GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Publica GNU e da 
 * Licença Pública Geral Menor GNU (LGPL) junto com este programa.
 * Caso contrário consulte <http://www.fsfla.org/svnwiki/trad/GPLv3> ou
 * <http://www.fsfla.org/svnwiki/trad/LGPLv3>. 
 *
 * Está atualizada para :
 *      PHP 5.4
 *
 *
 * @package   NFePHP
 * @name      MDFeNFePHP
 * @version   1.0.0
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL v.3
 * @copyright 2009-2014 &copy; NFePHP
 * @link      http://www.nfephp.org/
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @author    Leandro C. Lopez <leandro dot castoldi at gmail dot com>
 *        CONTRIBUIDORES (em ordem alfabetica):
 *
 *
 */
//define o caminho base da instalação do sistema
if (!defined('PATH_ROOT')) {
   define('PATH_ROOT', dirname(dirname( __FILE__ )) . DIRECTORY_SEPARATOR);
}

/**
 * Classe principal "CORE class"
 */
class MDFeNFePHP {
    /**
     * exceptions
     * Ativa ou desativa o uso de exceções para transporte de erros
     * @var boolean
     */
    protected $exceptions = false;

    /////////////////////////////////////////////////
    // CONSTANTES usadas no controle das exceções
    /////////////////////////////////////////////////
    const STOP_MESSAGE  = 0; // apenas um aviso, o processamento continua
    const STOP_CONTINUE = 1; // quationamento ?, perecido com OK para continuar o processamento
    const STOP_CRITICAL = 2; // Erro critico, interrupção total
    
    // propriedades da classe
    /**
     * raizDir
     * Diretorio raiz da API
     * @var string
     */
    public $raizDir = '';


    public $evtDir='';
    /**
     * enableSCAN
     * Habilita o acesso ao serviço SCAN ao invés do webservice estadual
     * @var boolean
     */
    public $enableSCAN = false;
    /**
     * enableSVAN
     * Indica o acesso ao serviço SVAN
     * @var boolean
     */
    public $enableSVAN = false;

    /**
     * xmlURLfile
     * Arquivo xml com as URL do SEFAZ de todos dos Estados
     * @var string
     */
    public $xmlURLfile='';
    /**
     * modSOAP
     * Indica o metodo SOAP a usar 1-SOAP Nativo ou 2-cURL
     * @var string
     */
    public $modSOAP = '2';

    /**
     * tpAmb
     * Tipo de ambiente 1-produção 2-homologação
     * @var string
     */
    public $tpAmb = '';

    /**
     * schemeVer
     * String com o nome do subdiretorio onde se encontram os schemas
     * atenção é case sensitive
     * @var string
     */
    public $mdfeSchemeVer;

    /**
     * aProxy
     * Matriz com as informações sobre o proxy da rede para uso pelo SOAP
     * @var array IP PORT USER PASS
     */
    public $aProxy = '';

    /**
     * aMail
     * Matiz com os dados para envio de emails
     * FROM  HOST USER PASS
     * @var array
     */
    public $aMail = '';
    /**
     * keyPass
     * Senha de acesso a chave privada
     * @var string
     */
    private $keyPass = '';
    /**
     * passPhrase
     * palavra passe para acessar o certificado (normalmente não usada)
     * @var string
     */
    private $passPhrase = '';
    /**
     * certName
     * Nome do certificado digital
     * @var string
     */
    private $certName = '';
    /**
     * certMonthsToExpire
     * Meses que faltam para o certificado expirar
     * @var integer
     */
    public $certMonthsToExpire = 0;
    /**
     * certDaysToExpire
     * Dias que faltam para o certificado expirar
     * @var integer
     */
    public $certDaysToExpire = 0;
    /**
     * priKEY
     * Path completo para a chave privada em formato pem
     * @var string
     */
    private $priKEY = '';
    /**
     * pubKEY
     * Path completo para a chave public em formato pem
     * @var string
     */
    private $pubKEY = '';
    /**
     * certKEY
     * Path completo para o certificado (chave privada e publica) em formato pem
     * @var string
     */
    private $certKEY = '';
    /**
     * empName
     * Razão social da Empresa
     * @var string
     */
    private $empName = '';
    /**
     * cnpj
     * CNPJ do emitente
     * @var string
     */
    private $cnpj = '';
    /**
     * cUF
     * Código da unidade da Federação IBGE
     * @var string
     */
    public $cUF = '';
    /**
     * UF
     * Sigla da Unidade da Federação
     * @var string
     */
    private $UF = '';
     /**
     * timeZone
     * Zona de tempo GMT
     */
    protected $timeZone = '-03:00';
    /**
     * daMDFelogopath
     * Variável que contem o path completo para a logo a ser impressa na DAMDFe
     * @var string $logopath
     */
    public $damdfelogopath = '';
    /**
     * daMDFelogopos
     * Estabelece a posição do logo no DAMDFE
     * L-Esquerda C-Centro e R-Direita
     * @var string
     */
    public $damdfelogopos = 'C';
    /**
     * daMDFeform
     * Estabelece o formato do DAMDFE
     * P-Retrato L-Paisagem (NOTA: somente o formato P é funcional, por ora)
     * @var string P-retrato ou L-Paisagem
     */
    public $damdfeform = 'P';
    /**
     * damdfepaper
     * Estabelece o tamanho da página
     * NOTA: somente o A4 pode ser utilizado de acordo com a ISO
     * @var string
     */
    public $damdfepaper = 'A4';
    /**
     * damdfecanhoto
     * Estabelece se o canhoto será impresso ou não
     * @var boolean
     */
    public $damdfecanhoto = true;
    /**
     * damdfefont
     * Estabelece a fonte padrão a ser utilizada no damdfe
     * de acordo com o Manual da SEFAZ usar somente Times
     * @var string
     */
    public $damdfefont = 'Times';
   /**
     * damdfeprinter
     * Estabelece a printer padrão a ser utilizada na impressão da damdfe
     * @var string
     */
    public $damdfeprinter = '';
    /**
     * anoMes
     * Variável que contem o ano com 4 digitos e o mes com 2 digitos
     * Ex. 201003
     * @var string
     */
    private $anoMes = '';
    /**
     * aURL
     * Array com as url dos webservices
     * @var array
     */
    public $aURL = '';
    /**
     * aCabec
     * @var array
     */
    public $aCabec = '';
    /**
     * errMsg
     * Mensagens de erro do API
     * @var string
     */
    public $errMsg = '';
    /**
     * errStatus
     * Status de erro
     * @var boolean
     */
    public $errStatus = false;
    /**
     * URLbase
     * Base da API
     * @var string
     */
    public $URLbase = '';
    /**
     * soapDebug
     * Mensagens de debug da comunicação SOAP
     * @var string
     */
    public $soapDebug = '';
    /**
     * debugMode
     * Ativa ou desativa as mensagens de debug da classe
     * @var string
     */
    protected $debugMode=2;
    /**
     * classDebug
     * Mensagens de debug da classe
     * @var string
     */
    public $classDebug = '';
    /**
     * URLxsi
     * Instãncia do WebService
     * @var string
     */
    private $URLxsi = 'http://www.w3.org/2001/XMLSchema-instance';
    /**
     * URLxsd
     * Instância do WebService
     * @var string
     */
    private $URLxsd = 'http://www.w3.org/2001/XMLSchema';
    /**
     * URLMDFe
     * Instância do WebService
     * @var string
     */
    private $URLMDFe = 'http://www.portalfiscal.inf.br/mdfe';
    /**
     * URLdsig
     * Instância do WebService
     * @var string
     */
    private $URLdsig = 'http://www.w3.org/2000/09/xmldsig#';
    /**
     * URLCanonMeth
     * Instância do WebService
     * @var string
     */
    private $URLCanonMeth = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    /**
     * URLSigMeth
     * Instância do WebService
     * @var string
     */
    private $URLSigMeth = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';
    /**
     * URLTransfMeth_1
     * Instância do WebService
     * @var string
     */
    private $URLTransfMeth_1 = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
    /**
     * URLTransfMeth_2
     * Instância do WebService
     * @var string
     */
    private $URLTransfMeth_2 = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    /**
     * URLDigestMeth
     * Instância do WebService
     * @var string
     */
    private $URLDigestMeth = 'http://www.w3.org/2000/09/xmldsig#sha1';
    /**
     * URLPortal
     * Instância do WebService
     * @var string
     */
    private $URLPortal = 'http://www.portalfiscal.inf.br/mdfe';
    /**
     * aliaslist
     * Lista dos aliases para os estados que usam o SEFAZ VIRTUAL
     * @var array
     */
    private $aliaslist = array(
            'AC' => 'SVRS',
            'AL' => 'SVRS',
            'AM' => 'SVRS',
            'AP' => 'SVRS',
            'BA' => 'BA',
            'CE' => 'SVAN',
            'DF' => 'SVRS',
            'ES' => 'SVAN',
            'GO' => 'GO',
            'MA' => 'SVAN',
            'MG' => 'MG',
            'MS' => 'SVRS',
            'MT' => 'MT',
            'PA' => 'SVAN',
            'PB' => 'SVRS',
            'PE' => 'PE',
            'PI' => 'SVAN',
            'PR' => 'PR',
            'RJ' => 'SVRS',
            'RN' => 'SVAN',
            'RO' => 'SVRS',
            'RR' => 'SVRS',
            'RS' => 'RS',
            'SC' => 'SVRS',
            'SE' => 'SVRS',
            'SP' => 'SP',
            'TO' => 'SVRS',
            'SCAN' => 'SCAN'
        );

    /**
     * cUFlist
     * Lista dos numeros identificadores dos estados
     * @var array
     */
    private $cUFlist = array(
            'AC' => '12',
            'AL' => '27',
            'AM' => '13',
            'AP' => '16',
            'BA' => '29',
            'CE' => '23',
            'DF' => '53',
            'ES' => '32',
            'GO' => '52',
            'MA' => '21',
            'MG' => '31',
            'MS' => '50',
            'MT' => '51',
            'PA' => '15',
            'PB' => '25',
            'PE' => '26',
            'PI' => '22',
            'PR' => '41',
            'RJ' => '33',
            'RN' => '24',
            'RO' => '11',
            'RR' => '14',
            'RS' => '43',
            'SC' => '42',
            'SE' => '28',
            'SP' => '35',
            'TO' => '17'
        );

    /**
     * cUFlist
     * Lista dos numeros identificadores dos estados
     * @var array
     */
    private $UFList = array (
            '11'=>'RO',
            '12'=>'AC',
            '13'=>'AM',
            '14'=>'RR',
            '15'=>'PA',
            '16'=>'AP',
            '17'=>'TO',
            '21'=>'MA',
            '22'=>'PI',
            '23'=>'CE',
            '24'=>'RN',
            '25'=>'PB',
            '26'=>'PE',
            '27'=>'AL',
            '28'=>'SE',
            '29'=>'BA',
            '31'=>'MG',
            '32'=>'ES',
            '33'=>'RJ',
            '35'=>'SP',
            '41'=>'PR',
            '42'=>'SC',
            '43'=>'RS',
            '50'=>'MS',
            '51'=>'MT',
            '52'=>'GO',
            '53'=>'DF'
        );

    /**
     * __

     t
     * Método construtor da classe
     * Este método utiliza o arquivo de configuração localizado no diretorio config
     * para montar os diretórios e várias propriedades internas da classe, permitindo
     * automatizar melhor o processo de comunicação com o SEFAZ.
     *
     * Este metodo pode estabelecer as configurações a partir do arquivo config.php ou
     * através de um array passado na instanciação da classe.
     *
     * @param  array
     * @return boolean true sucesso false Erro
     */
    function __construct($aConfig='',$mododebug=0,$exceptions=false) {
        if (is_numeric($mododebug)) {
            $this->debugMode = $mododebug;
        }
        if ($mododebug == 1) {
            //ativar modo debug
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }
        if ($mododebug == 0) {
            //desativar modo debug
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }
        if ($exceptions) {
            $this->exceptions = true;
        }
        
        // Abrir o config para obter a senha do certificado
        $arquivoIni = parse_ini_file("/var/www/html/nf/nfe/config/config.ini");
        if(!$arquivoIni){
            echo "Erro ao abrir o arquivo config.ini";
        }

        // Obtem o path da biblioteca
        $this->raizDir = dirname(dirname( __FILE__ )) . DIRECTORY_SEPARATOR;
        // verifica se foi passado uma matriz de configuração na inicialização da classe
        if(is_array($aConfig)) {
            $this->tpAmb = $aConfig['ambiente'];
            //$this->empName = $aConfig['razao_social'];
            $this->UF = $aConfig['uf'];

            $this->cUF = $aConfig['uf']; //$this->cUFlist[$aConfig['uf']];

            $this->cnpj = $aConfig['cnpj'];
            $this->certName = $aConfig['cnpj'].".pfx";
            $this->keyPass = $arquivoIni[$aConfig['cnpj']];
            $this->keyPass = $arquivoIni[$aConfig['cnpj']];
            //$this->arqDir = $aConfig['diretorio_integracao'];
            $this->mdfeSchemeVer = $arquivoIni['pacote_mdfe'];

            //$this->xmlURLfile = $this->raizDir. DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'mdfe_ws1.xml';
            $this->xmlURLfile = $this->raizDir.'wsdl/mdfe_ws1.xml';
            
        } else {
            // Testa a existencia do arquivo de configuração
            if (is_file($this->raizDir . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
                // Carrega o arquivo de configuração
                include($this->raizDir . 'config' . DIRECTORY_SEPARATOR . 'config.php');
                // Carrega propriedades da classe com os dados de configuração
                // a sring $sAmb será utilizada para a construção dos diretorios
                // dos arquivos de operação do sistema
                $this->tpAmb = $ambiente;
                // Carrega as propriedades da classe com as configurações
                //$this->empName = $empresa;
                $this->UF = $UF;
                $this->cUF = $this->cUFlist[$UF];
                $this->cnpj = $cnpj;
                $this->certName = $certName;
                $this->keyPass = $keyPass;
                $this->passPhrase = $passPhrase;
                //$this->arqDir = $arquivosDirMDFe;
                $this->URLbase = $baseurl;
                $this->damdfelogopath = $damdfeLogo;
                $this->damdfelogopos = $damdfeLogoPos;
                $this->damdfeform = $damdfeFormato;
                $this->damdfepaper = $damdfePapel;
                $this->damdfecanhoto = $damdfeCanhoto;
                $this->damdfefont = $damdfeFonte;
                $this->damdfeprinter = $damdfePrinter;
                $this->mdfeSchemeVer = $schemesMDFe;
                if(isset($arquivoURLxmlMDFe)){
                    $this->xmlURLfile = $arquivoURLxmlMDFe;
                }

            

                if ($proxyIP != '') {

          
                    $this->aProxy = array(
                            'IP' => $proxyIP,
                            'PORT' => $proxyPORT,
                            'USER' => $proxyUSER,
                            'PASS' => $proxyPASS
                        );
                }

                if ($mailFROM != '') {
                    $this->aMail = array(
                            'mailFROM' => $mailFROM,
                            'mailHOST' => $mailHOST,
                            'mailUSER' => $mailUSER,
                            'mailPASS' => $mailPASS,
                            'mailPROTOCOL' => $mailPROTOCOL,
                            'mailFROMmail' => $mailFROMmail,
                            'mailFROMname' => $mailFROMname,
                            'mailREPLYTOmail' => $mailREPLYTOmail,
                            'mailREPLYTOname' => $mailREPLYTOname
                        );
                }
            } else {
                // Caso não exista arquivo de configuração retorna erro
                $this->errMsg = "Não foi localizado o arquivo de configuração.";
                $this->errStatus = true;
                return false;
            }
        }
        //estabelece o ambiente
        $sAmb = ($this->tpAmb == 2) ? 'homologacao' : 'producao';
        //carrega propriedade com ano e mes ex. 200911
        $this->anoMes = date('Ym');
        //carrega o caminho para os schemas
        $this->xsdDir = $this->raizDir . 'schemes'. DIRECTORY_SEPARATOR;
        //carrega o caminho para os certificados
        $this->certsDir =  "/var/www/html/nf/nfse/certificados" . DIRECTORY_SEPARATOR;
        //carrega o caminho para as imagens
        //$this->imgDir =  $this->raizDir . 'images'. DIRECTORY_SEPARATOR; nao ha necessidade de imagens
        // Verifica o ultimo caraMDFer da variável $arqDir
        // se não for um DIRECTORY_SEPARATOR então colocar um
        if (substr($this->arqDir, -1, 1) != DIRECTORY_SEPARATOR){
            $this->arqDir .= DIRECTORY_SEPARATOR;
        }

        return true;
        
        
    }//fim __construct
    
    /**
     * statusServico
     * Verifica o status do servico da SEFAZ
     *
     * $this->cStat = 107 OK
     *        cStat = 108 sistema paralizado momentaneamente, aguardar retorno
     *        cStat = 109 sistema parado sem previsao de retorno, verificar status SCAN
     *                    se SCAN estiver ativado usar, caso contrário aguardar pacientemente.
     * @name statusServico
     * @param string $UF sigla da Unidade da Federação
     * @param integer $tpAmb tipo de ambiente 1-produção e 2-homologação
     * @param integer 1 usa o __sendSOAP e 2 usa o __sendSOAP2
     * @return    mixed false ou array conforme exemplo abaixo:
     * array(10) {
     * ["bStat"]     =>  bool(true),
     * ["cStat"]     =>  string(3)  "107",
     * ["tMed"]      =>  string(1)  "1",
     * ["dhRecbto"]  =>  string(19) "20/02/2012 15:47:34",
     * ["xMotivo"]   =>  string(19) "Servico em Operacao",
     * ["xObs"]      =>  string(0)  "",
     * ["tpAmb"]     =>  string(1)  "1",
     * ["verAplic"]  =>  string(16) "RS20111213141015",
     * ["cUF"]       =>  string(2)  "43",
     * ["dhRetorno"] =>  string(0)  ""
     * }
    **/
    public function statusServico($UF = '', $tpAmb = '', $modSOAP = '2') {
        // Retorno da funçao
        
        echo "\n comeco \n";
        
        $aRetorno = array('bStat' => false,'cStat' => '','tMed'  => '','dhRecbto' => '','xMotivo' => '','xObs' => '','xml' => '');

        // Caso o parametro tpAmb seja vazio
        if ($tpAmb == '') {
            $tpAmb = $this->tpAmb;
        }
        
        echo "\n comeco2 \n";

        $aURL = $this->aURL;
        // Caso a sigla do estado esteja vazia
        if (empty($UF)) {
            $UF = $this->UF;
            echo "\n comeco3 \n";
        } else {
            echo "\n comeco4 \n";
            if ($UF != $this->UF || $tpAmb != $this->tpAmb) {
            echo "\n comeco5 \n";
                // Recarrega as url referentes aos dados passados como parametros para a função
                $aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . "mdfe_ws1.xml", $tpAmb, $UF);
            }
        }


        echo "\n comeco6 \n";
        // Busca o cUF
        //$cUF = $this->cUFlist[$UF];
        /* Eduardo - 27/06/16 */
        $cUF = $UF;
        // Identificação do serviço
        $servico = 'MDFeStatusServico';
        
        echo "\n comeco7 \n";
        // Recuperação da versão
        $versao = $aURL[$servico]['version'];
        
        echo "\n comeco8 \n";
        // Recuperação da url do serviço
        $urlservico = $aURL[$servico]['URL'];
        // Recuperação do método
        echo "\n comeco9 \n";
        $metodo = $aURL[$servico]['method'];
        // Montagem do namespace do serviço
        $namespace = $this->URLPortal . '/wsdl/' . $servico;
        // Montagem do cabeçalho da comunicação SOAP
        
        echo "\n comeco10 \n";
        $cabec = '<mdfeCabecMsg xmlns="'. $namespace . '"><cUF>' . $cUF . '</cUF><versaoDados>' . $versao . '</versaoDados></mdfeCabecMsg>';
        // Montagem dos dados da mensagem SOAP
        
        echo "\n comeco11 \n";
        
        
        echo "\n namespcace: ".$namespace."\n";
        echo "\n url: ".$this->URLPortal."\n";
        echo "\n versao: ".$versao."\n";
        echo "\n amb: ".$tpAmb."\n";        
        
        $dados = '<mdfeDadosMsg xmlns="' . $namespace . '"><consStatServMDFe xmlns="' . $this->URLPortal . '" versao="' . $versao . '"><tpAmb>' . $tpAmb . '</tpAmb><xServ>STATUS</xServ></consStatServMDFe></mdfeDadosMsg>';

        echo "\nantes enviar\n";

        if ($modSOAP == '2') {
        
            echo "modo 2";
            $retorno = $this->__sendSOAP2($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb, $UF);
        } else {
        
            echo "modo != ";
            $retorno = $this->__sendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb, $UF);
        }
        
        echo "\nretorno\n";
        print_r($retorno);

        // Verifica o retorno do SOAP
        if (isset($retorno)) {
            // Tratar dados de retorno
            $doc = new DOMDocument();
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = !empty($doc->getElementsByTagName('cStat')->item(0)->nodeValue) ? $doc->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            //certifica que existe o elemento "cStat" no XML de retortno da SEFAZ.
            if ($cStat == ''){
                $this->errStatus = true;
                $this->errMsg = 'Nao existe o elemento "cStat" no XML de retorno da SEFAZ, erro!!';
                return false;
            }

            $aRetorno['xml'] = $doc->saveXML();

            $aRetorno['bStat'] = ($cStat == '107');
            // Tipo de ambiente
            $aRetorno['tpAmb'] = $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
            // Versão do aplicativo da SEFAZ
            $aRetorno['verAplic'] = $doc->getElementsByTagName('verAplic')->item(0)->nodeValue;
            // Status do serviço
            $aRetorno['cStat'] = $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
            // Motivo da resposta
            $aRetorno['xMotivo'] = utf8_encode($doc->getElementsByTagName('xMotivo')->item(0)->nodeValue);
            // Código da UF que atendeu a solicitação
            $aRetorno['cUF'] = $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
            // Data e hora da mensagem
            $aRetorno['dhRecbto'] = date('d/m/Y H:i:s', $this->__convertTime($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue));
            // Tempo médio de resposta, em segundos (opcional)
            $aRetorno['tMed'] = !empty($doc->getElementsByTagName('tMed')->item(0)->nodeValue) ? $doc->getElementsByTagName('tMed')->item(0)->nodeValue : '';
            // Data e hora prevista para o retorno do webservice (opcional)
            $aRetorno['dhRetorno'] = !empty($doc->getElementsByTagName('dhRetorno')->item(0)->nodeValue) ? date('d/m/Y H:i:s', $this->__convertTime($doc->getElementsByTagName('dhRetorno')->item(0)->nodeValue)) : '';
            // Obervações (opcional)
            $aRetorno['xObs'] = !empty($doc->getElementsByTagName('xObs')->item(0)->nodeValue) ? $doc->getElementsByTagName('xObs')->item(0)->nodeValue : '';
        } else {
            $this->errStatus = true;
            $this->errMsg = 'Nao houve retorno Soap verifique a mensagem de erro e o debug!!';
            $aRetorno = false;
        }
        return $aRetorno;
    } // Fim statusServico
    
    /**
     * verifyMDFe
     * Verifica a validade da MDFe recebida de terceiros
     *
     * @name verifyMDFe
     * @param string $file Path completo para o arquivo xml a ser verificado
     * @return boolean false se nao confere e true se confere
     */
    public function verifyMDFe($file) {
        //verifica se o arquivo existe
        if (file_exists($file)) {
            //carrega a MDFe
            $xml = file_get_contents($file);
            //testa a assinatura
            if ($this->verifySignatureXML($xml, 'infMDFe')) {
                //como a ssinatura confere, consultar o SEFAZ para verificar se o MDF não foi cancelado ou é FALSO
                //carrega o documento no DOM
                $xmldoc = new DOMDocument();
                $xmldoc->preservWhiteSpace = false; //elimina espacos em branco
                $xmldoc->formatOutput = false;
                $xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
                $root = $xmldoc->documentElement;
                $infMdfe = $xmldoc->getElementsByTagName('infMDFe')->item(0);
                //extrair a tag com os dados a serem assinados
                $id = trim($infMdfe->getAttribute("Id"));
                $chave = preg_replace('/[^0-9]/', '', $id);
                $digest = $xmldoc->getElementsByTagName('DigestValue')->item(0)->nodeValue;
                //ambiente da MDFe sendo consultada
                $tpAmb = $infMdfe->getElementsByTagName('tpAmb')->item(0)->nodeValue;
                //verifica se existe o protocolo
                $protMDFe = $xmldoc->getElementsByTagName('protMDFe')->item(0);
                if (isset($infMdfe)) {
                    $nProt = $xmldoc->getElementsByTagName('nProt')->item(0)->nodeValue;
                } else {
                    $nProt = '';
                }
                //busca o status da MDFe na SEFAZ do estado do emitente
                $resp = $this->getProtocol('', $chave, $tpAmb, '2');
                if ($resp['cStat'] != '100') {
                    //ERRO! ct não aprovada
                    $this->errStatus = true;
                    $this->errMsg = "MDF não aprovada no SEFAZ!! cStat =" . $resp['cStat'] . ' - ' . $resp['xMotivo'];
                    return false;
                } else {
                    if (is_array($resp['aProt'][0])) {
                        $nProtSefaz = $resp['aProt'][0]['nProt'];
                        $digestSefaz = $resp['aProt'][0]['digVal'];
                        //verificar numero do protocolo
                        if ($nProt != '') {
                            if ($nProtSefaz != $nProt) {
                                //ERRO !!!os numeros de protocolo não combinam
                                $this->errStatus = true;
                                $this->errMsg = "Os numeros dos protocolos não combinam!! nProtMDF = " . $nProt . " <> nProtSefaz = " . $nProtSefaz;
                                return false;
                            } //fim teste do protocolo
                        } else {
                            $this->errStatus = true;
                            $this->errMsg = "A MDFe enviada não comtêm o protocolo de aceitação !!";
                        }
                        //verifica o digest
                        if ($digestSefaz != $digest) {
                            //ERRO !!!os numeros digest não combinam
                            $this->errStatus = true;
                            $this->errMsg = "Os numeros digest não combinam!! digValSEFAZ = " . $digestSefaz . " <> DigestValue = " . $digest;
                            return false;
                        } //fim teste do digest value
                    } else {
                        //o retorno veio como 100 mas por algum motivo sem o protocolo
                        $this->errStatus = true;
                        $this->errMsg = "Falha no retorno dos dados, retornado sem o protocolo !! ";
                        return false;
                    }
                }
            } else {
                $this->errStatus = true;
                $this->errMsg = " Assinatura não confere!!";
                return false;
            } //fim verificação da assinatura
        } else {
            $this->errStatus = true;
            $this->errMsg = "Arquivo não localizado!!";
            return false;
        } //fim file_exists
        return true;
    } //fim verifyMDFe
    
    /**
     * sendLot
     * Envia lote de Conhecimento Eletronico para a SEFAZ.
     * Este método pode enviar uma ou mais MDFe para o SEFAZ, desde que,
     * o tamanho do arquivo de envio não ultrapasse 500kBytes
     * Este processo enviará somente até 50 MDFe em cada Lote
     *
     * @name sendLot
     * @param    array   $aMDFe conhecimento de transporte em xml uma em cada campo do array unidimensional MAX 50
     * @param   integer $id     id do lote e um numero que deve ser gerado pelo sistema
     *                          a cada envio mesmo que seja de apenas uma MDFe
     * @param   integer $modSOAP 1 usa __sendSOP e 2 usa __sendSOAP2
     * @return    mixed    false ou array ['bStat'=>false,'cStat'=>'','xMotivo'=>'','dhRecbto'=>'','nRec'=>'']
     * @todo 
    **/
    public function sendLot($aMDFe, $id="", $modSOAP = '2') {
        // Variavel de retorno do metodo
        $aRetorno = array('bStat'=>false,'cStat'=>'','xMotivo'=>'','dhRecbto'=>'','nRec'=>'');

        // Identificação do serviço
        $servico = 'MDFeRecepcao';
        //var_dump($aURL);
        // Recuperação da versão

        $versao = $aURL[$servico]['version']; 
        // Recuperação da url do serviço
        $urlservico = $aURL[$servico]['URL'];
        // Recuperação do método
        $metodo = $aURL[$servico]['method'];

        // Montagem do namespace do serviço
        $namespace = $this->URLPortal . '/wsdl/' . $servico;

        //echo "\n Envia dados via SOAP ...\n";
        if ($modSOAP == '2'){
            $retorno = $this->__sendSOAP2($urlservico, $namespace, $cabec, $dados, $metodo, $this->tpAmb);
        }

        if ($retorno) {
            // Tratar dados de retorno
            $doc = new DOMDocument();
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = !empty($doc->getElementsByTagName('cStat')->item(0)->nodeValue) ? $doc->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            if ($cStat == ''){
                return false;
            }
            $aRetorno['xml'] = $retorno;
            // XML retornado
            $aRetorno['lote'] = $idLote;
            // XML retornado
            $aRetorno['bStat'] = ($cStat == '103');
            // Status do serviço
            $aRetorno['cStat'] = $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
            // Motivo da resposta (opcional)
            $aRetorno['xMotivo'] = !empty($doc->getElementsByTagName('xMotivo')->item(0)->nodeValue) ? $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            // Data e hora da mensagem (opcional)
            $aRetorno['dhRecbto'] = !empty($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue) ? date("d/m/Y H:i", $this->__convertTime($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue)) : '';
            // Numero do recibo do lote enviado (opcional)
            $aRetorno['nRec'] = !empty($doc->getElementsByTagName('nRec')->item(0)->nodeValue) ? $doc->getElementsByTagName('nRec')->item(0)->nodeValue : '';
            // Grava o retorno na pasta temp
            $nome = $this->temDir . $id . '-rec.xml';
            $nome = $doc->save($nome);
        } else {
            $this->errStatus = true;
            $this->errMsg = 'Nao houve retorno Soap verifique a mensagem de erro e o debug!!';
            $aRetorno = false;
        }
        return $aRetorno;
    } // Fim sendLot
    
    /**
     * getProtocol
     * Solicita resposta do lote de Conhecimentos de Transporte ou o protocolo de
     * autorização da MDFe $tpAmb = $this->tpAmb;
     * Caso $this->cStat == 105 Tentar novamente mais tarde
     *
     * @name getProtocol
     * @param    string   $recibo numero do recibo do envio do lote
     * @param    string   $chave  numero da chave da MDFe de 44 digitos
     * @param   string   $tpAmb  numero do ambiente 1 - producao e 2 - homologação
     * @param   integer   $modSOAP 1 usa __sendSOAP e 2 usa __sendSOAP2
     * @return    mixed     false ou array
    **/
    public function getProtocol($recibo = '', $chave = '', $tpAmb = '', $modSOAP = '2', &$aRetorno = '') {
        // Carrega defaults
        $i = 0;
        $aRetorno = array('bStat' => false,'cStat' => '','xMotivo' => '','aProt' => '','aCanc'=>'');
        $cUF = $this->cUF;
        $UF = $this->UF;

        if ($tpAmb != '1' && $tpAmb != '2' ) {
            $tpAmb = '2';
        }

        $tpAmb = $this->tpAmb;
        $aURL = $this->aURL;
        // Verifica se a chave foi passada
        $scan = '';
        if($chave != '') {
            // Se sim extrair o cUF da chave
            $cUF = substr($chave, 0, 2);
            // Testar para ver se é o mesmo do emitente
            if($cUF != $this->cUF || $tpAmb != $this->tpAmb) {
                // Se não for o mesmo carregar a sigla
                $UF = $this->UFList[$cUF];
                // Recarrega as url referentes aos dados passados como parametros para a função
                $aURL = $this->loadSEFAZ($this->raizDir . '/config' . DIRECTORY_SEPARATOR . "mdfe_ws1.xml", $tpAmb, $UF);
            }
            $scan = substr($chave,34,1);
        }
        //hambiente SCAN
        if($scan == 7 || $scan == 3){
            if($cUF == 35){
                $aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . $this->xmlURLfile,$tpAmb,'SVSP');
            }else{
                $aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . $this->xmlURLfile,$tpAmb,'SVRS');
            }
        }

        if ($recibo == '' && $chave == '') {
            $this->errStatus = true;
            $this->errMsg = 'ERRO. Favor indicar o numero do recibo ou a chave de acesso da MDFe!!';
            return false;
        }
        if ($recibo != '' && $chave != '') {
            $this->errStatus = true;
            $this->errMsg = 'ERRO. Favor indicar somente um dos dois dados ou o numero do recibo ou a chave de acesso da MDFe!!';
            return false;
        }
        // Consulta pelo recibo
        if ($recibo != '' && $chave == '') {
            // Buscar os protocolos pelo numero do recibo do lote
            // Identificação do serviço
            $servico = 'MDFeRetRecepcao';
            // Recuperação da versão
            $versao = $aURL[$servico]['version'];
            // Recuperação da url do serviço
            $urlservico = $aURL[$servico]['URL'];
            // Recuperação do método
            $metodo = $aURL[$servico]['method'];
            // Montagem do namespace do serviço
            $namespace = $this->URLPortal . '/wsdl/' . $servico;
            // Montagem do cabeçalho da comunicação SOAP 
            $cabec = '<mdfeCabecMsg xmlns="' . $namespace . '"><cUF>' . $this->cUF . '</cUF><versaoDados>1.00</versaoDados></mdfeCabecMsg>';
            // Montagem dos dados da mensagem SOAP
            $dados = '<mdfeDadosMsg xmlns="' . $namespace . '"><consReciMDFe xmlns="' . $this->URLPortal . '" versao="1.00"><tpAmb>' .  $tpAmb . '</tpAmb><nRec>' . $recibo . '</nRec></consReciMDFe></mdfeDadosMsg>';
            // Nome do arquivo
            $nomeArq = $recibo . '-protrec.xml';
        }
        // Consulta pela chave
        if ($recibo == '' &&  $chave != '') {
            // Buscar o protocolo pelo numero da chave de acesso
            // Identificação do serviço

            $servico = 'MDFeConsulta';
            // Recuperação da versão

            $versao = $this->aURL[$servico]['version'];
            // Recuperação da url do serviço
            $urlservico = $this->aURL[$servico]['URL'];
            // Recuperação do método
            $metodo = $this->aURL[$servico]['method'];

            // Montagem do namespace do serviço
            $servico = 'MDFeConsulta';
            $namespace = $this->URLPortal . '/wsdl/' . $servico;
            // Montagem do cabeçalho da comunicação SOAP
            $cabec = '<mdfeCabecMsg xmlns="' . $namespace . '"><cUF>' . $this->cUF . '</cUF><versaoDados>1.00</versaoDados></mdfeCabecMsg>';
            // Montagem dos dados da mensagem SOAP
            $dados = '<mdfeDadosMsg xmlns="' . $namespace . '"><consSitMDFe xmlns="' . $this->URLPortal . '" versao="1.00"><tpAmb>' . $tpAmb . '</tpAmb><xServ>CONSULTAR</xServ><chMDFe>' . $chave . '</chMDFe></consSitMDFe></mdfeDadosMsg>';

        }

        // Envia a solicitação via SOAP

        //echo $urlservico."\n\n".$namespace."\n\n".$cabec."\n\n".$dados."\n\n".$metodo."\n\n".$tpAmb."\n\n".$UF."\n\n".$modSOAP."\n\n";


        //print_r($dados);

        if ($modSOAP == 2){
            $retorno = $this->__sendSOAP2($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb,$UF);
        } else {
            $retorno = $this->__sendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb, $UF);
        }

        //print_r($retorno);

        // Verifica o retorno
        if ($retorno) {
            // Tratar dados de retorno
            $doc = new DOMDocument();
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = !empty($doc->getElementsByTagName('cStat')->item(0)->nodeValue) ? $doc->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            if ($cStat == '') {
                return false;
            }
            // O retorno vai variar se for buscado o protocolo ou recibo
            // Retorno nda consulta pela Chave do MDFe
            // retConsSitMDFe 100 aceita 110 denegada 101 cancelada ou outro recusada
            // cStat xMotivo cUF chMDFe protMDFe retCancMDFe
            if ($chave != '') {
                $aRetorno['bStat'] = true;
				$aRetorno['dhRecbto'] = !empty($aProt['dhRecbto']) ? date("Y/m/d H:i:s",$this->__convertTime($aProt['dhRecbto'])) : '';
                $aRetorno['dhRecbto2'] = $doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue;
                $aRetorno['cStat'] = $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
				$aRetorno['nProt'] = $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
                $aRetorno['cUF'] = $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
				$aRetorno['tpEvento'] = $doc->getElementsByTagName('tpEvento')->item(0)->nodeValue;
                $aRetorno['xMotivo'] = !empty($doc->getElementsByTagName('xMotivo')->item(0)->nodeValue) ? $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
                $infProt = $doc->getElementsByTagName('infProt')->item($i);
                $infCanc = $doc->getElementsByTagName('infCanc')->item(0);
                $retEventoMDFe = $doc->getElementsByTagName('retEventoMDFe')->item(0);
                $aProt = '';
                $aEven = '';
                if (isset($infProt)){
                    foreach($infProt->childNodes as $t){
                        $aProt[$i][$t->nodeName] = $t->nodeValue;
                    }
                    $aProt['dhRecbto'] = !empty($aProt['dhRecbto']) ? date("Y/m/d H:i:s",$this->__convertTime($aProt['dhRecbto'])) : '';
                }else {
                    $aProt = '';
                }
                if(isset($infCanc)){
                    foreach($infCanc->childNodes as $t) {
                        $aCanc[$t->nodeName] = $t->nodeValue;
                    }
                    $aCanc['dhRecbto'] = !empty($aCanc['dhRecbto']) ? date("Y/m/d H:i:s",$this->__convertTime($aCanc['dhRecbto'])) : '';
                } else {
                    $aCanc = '';
                }
                if (isset($retEventoMDFe)){
                    $infEvento = $retEventoMDFe->getElementsByTagName('infEvento')->item(0);
                    foreach($infEvento->childNodes  as $t){
                        $aEven[$i][$t->nodeName] = $t->nodeValue;
                    }
                }else {
                    $aEven = '';
                }
                $aRetorno['aEven'] = $aEven;
                $aRetorno['aProt'] = $aProt;
                $aRetorno['aCanc'] = $aCanc;
                $aRetorno['xml'] = $doc->saveXML();
                // Gravar o retorno na pasta temp apenas se a nota foi aprovada, cancelada ou denegada
                if ( $aRetorno['cStat'] == 100 || $aRetorno['cStat'] == 101 || $aRetorno['cStat'] == 110 ) {
                    // Nome do arquivo
                    $nomeArq = $chave . '-prot.xml';
                    $nome = $this->temDir . $nomeArq;
                    $nome = $doc->save($nome);
                    
                }
            }
            // Retorno da consulta pelo recibo
            // MDFeRetRecepcao 104 tem retornos
            // nRec cStat xMotivo cUF cMsg xMsg protMDFe* infProt chMDFe dhRecbto nProt cStat xMotivo
            if ($recibo != '') {
                $aRetorno['bStat'] = true;

                // status do serviço
                $aRetorno['cStat'] = $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
				$aRetorno['tpAmb'] = $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
				$aRetorno['nProt'] = $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
				$aRetorno['chMDFe'] = $doc->getElementsByTagName('chMDFe')->item(0)->nodeValue;
				$aRetorno['cUF'] = $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
                // motivo da resposta (opcional)
                $aRetorno['xMotivo'] = !empty($doc->getElementsByTagName('xMotivo')->item(0)->nodeValue) ? $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
                if ($cStat == '104'){
                    $aProt = '';
                    //aqui podem ter varios retornos dependendo do numero de MDFe enviados no Lote e já processadas
                    $protMDFe = $doc->getElementsByTagName('protMDFe');
                    foreach ($protMDFe as $d){
                        $infProt = $d->getElementsByTagName('infProt')->item($i);
                        $aRetorno['protcStat'] = $protcStat = $infProt->getElementsByTagName('cStat')->item(0)->nodeValue;
						$aRetorno['protxMotivo'] = $infProt->getElementsByTagName('xMotivo')->item(0)->nodeValue;
						$aRetorno['dhRecbto'] = $infProt->getElementsByTagName('dhRecbto')->item(0)->nodeValue;
						//$aRetorno['dhRecbto'] = !empty($aProt['dhRecbto']) ? date("Y/m/d H:i:s",$this->__convertTime($aProt['dhRecbto'])) : '';
                        //pegar os dados do protolo para retornar
                        foreach($infProt->childNodes as $t) {
                            $aProt[$i][$t->nodeName] = $t->nodeValue;
                        }
                        $i++; //incluido increment para controlador de indice do array
                        //salvar o protocolo somente se a nota estiver approvada ou denegada
                        if ( $protcStat == 100 || $protcStat == 110 ){
                            $nomeprot = $this->temDir.$infProt->getElementsByTagName('chMDFe')->item(0)->nodeValue.'-prot.xml';//id da nfe
                            //salvar o protocolo em arquivo
                            $novoprot = new DOMDocument('1.0', 'UTF-8');
                            $novoprot->formatOutput = true;
                            $novoprot->preserveWhiteSpace = false;
                            $pMDFe = $novoprot->createElement("protMDFe");
                            $pMDFe->setAttribute("versao", "1.00");
                            // Importa o node e todo o seu conteudo
                            $node = $novoprot->importNode($infProt, true);
                            // acrescenta ao node principal
                            $pMDFe->appendChild($node);
                            $novoprot->appendChild($pMDFe);
                            $xml = $novoprot->saveXML();
                            $xml = str_replace('<?xml version="1.0" encoding="UTF-8  standalone="no"?>','<?xml version="1.0" encoding="UTF-8"?>',$xml);
                            $xml = str_replace(array("default:",":default"),"",$xml);
                            $xml = str_replace("\n","",$xml);
                            $xml = str_replace("  "," ",$xml);
                            $xml = str_replace("  "," ",$xml);
                            $xml = str_replace("  "," ",$xml);
                            $xml = str_replace("  "," ",$xml);
                            $xml = str_replace("  "," ",$xml);
                            $xml = str_replace("> <","><",$xml);
                            //file_put_contents($nomeprot, $xml);
							$aRetorno['xmlProtocolo'] = $xml;
                        }
                    }
                }

                $envelopeBodyNode = $doc->getElementsByTagNameNS('http://www.w3.org/2003/05/soap-envelope', 'Body')->item(0)->childNodes->item(0);
                $aRetorno['xmlRetorno'] = $doc->saveXML($envelopeBodyNode);


                $aRetorno['aProt'] = $aProt; //passa o valor de $aProt para o array de retorno
                //$nomeArq = $recibo . '-recprot.xml';
                //$nome = $this->temDir . $nomeArq;
                //$nome = $doc->save($nome);
            }
        } else {
            $this->errStatus = true;
            $this->errMsg = 'Nao houve retorno Soap verifique a mensagem de erro e o debug!!';
            $aRetorno = false;
        }
        return $aRetorno;
    } //fim getProtocol
    
    
    /**
     * addProt
     * Este método adiciona a tag do protocolo o MDFe, preparando a mesma
     * para impressão e envio ao destinatário.
     *
     * @name addProt
     * @param   string $ctefile path completo para o arquivo contendo a MDFe
     * @param   string $protfile path completo para o arquivo contendo o protocolo
     * @return  mixed false se erro ou string Retorna a MDFe com o protocolo
     */
    public function addProt($mdfefile='', $protfile='') {
            // Protocolo do lote enviado
            $prot = new DOMDocument();
            $prot->formatOutput = false;
            $prot->preserveWhiteSpace = false;
			$prot->loadXML($protfile, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

            // MDFe enviada
            $docmdfe = new DOMDocument();
            $docmdfe->formatOutput = false;
            $docmdfe->preserveWhiteSpace = false;
			$docmdfe->loadXML($mdfefile, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            // Carrega o arquivo na veriável
            //$xmlmdfe = file_get_contents($mdfefile);
            //$docmdfe->loadXML($xmlmdfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

            $mdfe = $docmdfe->getElementsByTagName("MDFe")->item(0);
            $infMDFe = $docmdfe->getElementsByTagName("infMDFe")->item(0);
            $versao = trim($infMDFe->getAttribute("versao"));
            // Carrega o protocolo e seus dados
            //$xmlprot = file_get_contents($protfile);
            //$prot->loadXML($xmlprot, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
			
            $protMDFe = $prot->getElementsByTagName("protMDFe")->item(0);
            $protver = trim($protMDFe->getAttribute("versao"));
            $tpAmb = $prot->getElementsByTagName("tpAmb")->item(0)->nodeValue;
            $verAplic = $prot->getElementsByTagName("verAplic")->item(0)->nodeValue;
            $chMDFe = $prot->getElementsByTagName("chMDFe")->item(0)->nodeValue;
            $dhRecbto = $prot->getElementsByTagName("dhRecbto")->item(0)->nodeValue;
            $nProt = $prot->getElementsByTagName("nProt")->item(0)->nodeValue;
            $digVal = $prot->getElementsByTagName("digVal")->item(0)->nodeValue;
            $cStat = $prot->getElementsByTagName("cStat")->item(0)->nodeValue;
            $xMotivo = $prot->getElementsByTagName("xMotivo")->item(0)->nodeValue;
            // Cria a MDFe processada com a tag do protocolo
            $procMDFe = new DOMDocument('1.0', 'utf-8');
            $procMDFe->formatOutput = false;
            $procMDFe->preserveWhiteSpace = false;
            // Cria a tag cteProc
            $mdfeProc = $procMDFe->createElement('mdfeProc');
            $procMDFe->appendChild($mdfeProc);
            // Estabele o atributo de versão
            $mdfeProc_att1 = $mdfeProc->appendChild($procMDFe->createAttribute('versao'));
            $mdfeProc_att1->appendChild($procMDFe->createTextNode($protver));
            // Estabelece o atributo xmlns
            $mdfeProc_att2 = $mdfeProc->appendChild($procMDFe->createAttribute('xmlns'));
            $mdfeProc_att2->appendChild($procMDFe->createTextNode($this->URLmdfe));
            // Inclui MDFe
            $node = $procMDFe->importNode($mdfe, true);
            $mdfeProc->appendChild($node);
            // Cria tag protCTe
            $protMDFe = $procMDFe->createElement('protMDFe');
            $mdfeProc->appendChild($protMDFe);
            // Estabele o atributo de versão
            $protMDFe_att1 = $protMDFe->appendChild($procMDFe->createAttribute('versao'));
            $protMDFe_att1->appendChild($procMDFe->createTextNode($versao));
            // Cria tag infProt
            $infProt = $procMDFe->createElement('infProt');
            $infProt_att1 = $infProt->appendChild($procMDFe->createAttribute('Id'));
            $infProt_att1->appendChild($procMDFe->createTextNode('ID'.$nProt));
            $protMDFe->appendChild($infProt);
            $infProt->appendChild($procMDFe->createElement('tpAmb', $tpAmb));
            $infProt->appendChild($procMDFe->createElement('verAplic', $verAplic));
            $infProt->appendChild($procMDFe->createElement('chMDFe', $chMDFe));
            $infProt->appendChild($procMDFe->createElement('dhRecbto', $dhRecbto));
            $infProt->appendChild($procMDFe->createElement('nProt', $nProt));
            $infProt->appendChild($procMDFe->createElement('digVal', $digVal));
            $infProt->appendChild($procMDFe->createElement('cStat', $cStat));
            $infProt->appendChild($procMDFe->createElement('xMotivo', $xMotivo));
            // Salva o xml como string em uma variável
            $procXML = $procMDFe->saveXML();
            // Remove as informações indesejadas
            $procXML = str_replace('default:', '', $procXML);
            $procXML = str_replace(':default', '', $procXML);
            $procXML = str_replace("\n", '', $procXML);
            $procXML = str_replace("\r", '', $procXML);
            $procXML = str_replace("\s", '', $procXML);
            $procXML = str_replace('MDFe xmlns="http://www.portalfiscal.inf.br/mdfe" xmlns="http://www.w3.org/2000/09/xmldsig#"', 'MDFe xmlns="http://www.portalfiscal.inf.br/mdfe"', $procXML);
            return $procXML;
    } // Fim addProt
    
    /**
     * manifDest
     * Manifestação do detinatário NT2012-002.
     *   110111 - Cancelamento
     *   110112 - Encerramento
     *   110114 - Inclusão de Condutor
     *   310620 - Registro de Passagem
     *   510620 - Registro de Passagem BRId
     *
     * @name manifDest
     * @param   string $chMDFe Chave da MDFe
     * @param   string $tpEvento Tipo do evento pode conter 2 ou 6 digitos ex. 00 ou 210200
     * @param   integer $tpAmb Tipo de ambiente
     * @param   integer $modSOAP 1 usa __sendSOP e 2 usa __sendSOAP2
     * @param   mixed  $resp variável passada como referencia e irá conter o retorno da função em um array
     * @return mixed false
     *
     * TODO : terminar o código não funcional e não testado
	 // cMun ou Justificativa de cancelamento
     */
    public function manifDest($chMDFe='',$tpEvento='',$tpAmb='',$cMun='', $modSOAP='2',&$resp='',$nProt=''){
        try {
            if ($chMDFe == ''){
                $msg = "A chave do MDFe recebida é obrigatória.";
                throw new nfephpException($msg);
            }
            if ($tpEvento == ''){
                $msg = "O tipo de evento não pode ser vazio.";
                throw new nfephpException($msg);
            }
            if (strlen($tpEvento) == 2){
                $tpEvento = "1101$tpEvento";
            }
            if (strlen($tpEvento) != 6){
                $msg = "O comprimento do código do tipo de evento está errado.";
                throw new nfephpException($msg);
            }
            $xml_ev = '';
            $cOrgao=$this->cUF;
            switch ($tpEvento){
                case '110111':
                    $descEvento = 'Cancelamento';
					$xml_ev = '<evCancMDFe>';
                    $xml_ev .= '<descEvento>'.$descEvento.'</descEvento>';
                    $xml_ev .= '<nProt>'.$nProt.'</nProt>';
                    $xml_ev .= '<xJust>'.$cMun.'</xJust>';
                    $xml_ev .= '</evCancMDFe>';
                    break;
                case '110112':
                    $descEvento = 'Encerramento';
                    $xml_ev = '<evEncMDFe>';
                    $xml_ev .= '<descEvento>'.$descEvento.'</descEvento>';
                    $xml_ev .= '<nProt>'.$nProt.'</nProt>';
                    $xml_ev .= '<dtEnc>'.date("Y-m-d").'</dtEnc>';
                    $xml_ev .= '<cUF>'.substr($cMun,0,2).'</cUF>';
                    $xml_ev .= '<cMun>'.$cMun.'</cMun>';
                    $xml_ev .= '</evEncMDFe>';
                    break;
                case '110114':
                    $descEvento = 'Inclusão de Condutor';
                    break;
                default:
                    $msg = "O código do tipo de evento informado não corresponde a nenhum evento de manifestação de destinatário.";
                    throw new nfephpException($msg);
            }
            $resp = array('bStat'=>false,'cStat'=>'','xMotivo'=>'','xml_env'=>'','xml_ret'=>'','xml'=>'','nProt'=>'','dhReceb'=>'');

            //ajusta ambiente
            if ($tpAmb == ''){
                $tpAmb = $this->tpAmb;
            }
            
            
            //utilizar AN para enviar o manifesto
            $sigla = 'RS';
            //$aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . $this->xmlURLfile,$tpAmb,$sigla);
            
            $numLote = substr(str_replace(',','',number_format(microtime(true)*1000000,0)),0,15);
            //Data e hora do evento no formato AAAA-MM-DDTHH:MM:SS (UTC)
            $dhEvento = date('Y-m-d').'T'.date('H:i:s');
            //montagem do namespace do serviço
            $servico = 'MDFeRecepcaoEvento';
            //recuperação da versão
            $versao = $this->aURL[$servico]['version'];
            //recuperação da url do serviço
            $urlservico = $this->aURL[$servico]['URL'];
            //recuperação do método
            $metodo = $this->aURL[$servico]['method'];
            //montagem do namespace do serviço
            $namespace = $this->URLPortal.'/wsdl/'.$servico;
            // 2   +    6     +    44         +   2  = 54 digitos
            //“ID” + tpEvento + chave da NF-e + nSeqEvento
            $nSeqEvento = '1';
            $id = "ID".$tpEvento.$chMDFe.'0'.$nSeqEvento;
            //monta mensagem
            $Ev='';
            $Ev .= "<eventoMDFe xmlns=\"$this->URLPortal\" versao=\"1.00\">";
            $Ev .= "<infEvento Id=\"$id\">";
            $Ev .= "<cOrgao>$cOrgao</cOrgao>";
            $Ev .= "<tpAmb>$tpAmb</tpAmb>";
            $Ev .= "<CNPJ>$this->cnpj</CNPJ>";
            $Ev .= "<chMDFe>$chMDFe</chMDFe>";
            $Ev .= "<dhEvento>$dhEvento</dhEvento>";
            $Ev .= "<tpEvento>$tpEvento</tpEvento>";
            $Ev .= "<nSeqEvento>$nSeqEvento</nSeqEvento>";
            $Ev .= "<detEvento versaoEvento=\"1.00\">";
            //$Ev .= "<versaoEvento>$versao</versaoEvento>";
            //$Ev .= "<descEvento>$descEvento</descEvento>";
            $Ev .= $xml_ev;
            $Ev .= "</detEvento></infEvento>";
            $Ev .= "</eventoMDFe>";

            //assinatura dos dados
            /*echo '<pre>';
            var_dump(htmlspecialchars( $Ev, 0, "iso-8859-1")); 
            echo '</pre>';
            */
            $tagid = 'infEvento';
            $Ev = $this->signXML($Ev, $tagid);
            $Ev = str_replace('<?xml version="1.0"?>','', $Ev);
            $Ev = str_replace('<?xml version="1.0" encoding="utf-8"?>','', $Ev);
            $Ev = str_replace('<?xml version="1.0" encoding="UTF-8"?>','', $Ev);
            $Ev = str_replace(array("\r","\n","\s"),"", $Ev);
            //montagem dos dados
            $dados = '';
            //$dados .= "<eventoMDFe xmlns=\"$this->URLPortal\" versao=\"$versao\">";
            //$dados .= "<idLote>$numLote</idLote>";
            $dados .= $Ev;
            //$dados .= "</eventoMDFe>";
            //montagem da mensagem
            $cabec = "<mdfeCabecMsg xmlns=\"$namespace\"><cUF>$this->cUF</cUF><versaoDados>$versao</versaoDados></mdfeCabecMsg>";
            $dados = "<mdfeDadosMsg xmlns=\"$namespace\">$dados</mdfeDadosMsg>";
            //grava solicitação em temp
            /*if (!file_put_contents($this->temDir."$chMDFe-$nSeqEvento-envMDFe.xml",$Ev)){
                $msg = "Falha na gravação do aruqivo envMDFe!!";
                throw new nfephpException($msg);
            }*/
            //envia dados via SOAP 
            if ($modSOAP == '2'){
                $retorno = $this->__sendSOAP2($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb, $this->UF);
            } else {
                $retorno = $this->__sendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb,$this->UF);
            }
            //verifica o retorno
            if (!$retorno){
                //não houve retorno
                $msg = "Nao houve retorno Soap verifique a mensagem de erro e o debug!!";
                throw new nfephpException($msg);
            }
            //tratar dados de retorno
            $xmlMDe = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlMDe->formatOutput = false;
            $xmlMDe->preserveWhiteSpace = false;
            $xmlMDe->loadXML($retorno,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $retEvento = $xmlMDe->getElementsByTagName("retEventoMDFe")->item(0);
            $infEvento = $xmlMDe->getElementsByTagName("infEvento")->item(0);
			$dhReceb = !empty($infEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue) ? $infEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue : '';
			$nProt = !empty($infEvento->getElementsByTagName('nProt')->item(0)->nodeValue) ? $infEvento->getElementsByTagName('nProt')->item(0)->nodeValue : '';
            $cStat = !empty($retEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ? $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $xMotivo = !empty($retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ? $retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            if ($cStat == ''){
                //houve erro
                $msg = "cStat está em branco, houve erro na comunicação Soap verifique a mensagem de erro e o debug!!";
                throw new nfephpException($msg);
            }
            //tratar erro de versão do XML
            if ($cStat == '238' || $cStat == '239'){
                $this->__trata239($retorno, $sigla, $tpAmb, $servico, $versao);
                $msg = "Versão do arquivo XML não suportada no webservice!!";
                throw new nfephpException($msg);
            }
            //erro no processamento
            if ($cStat != '135' && $cStat != '136' ){
                //se cStat <> 135 houve erro e o lote foi rejeitado
                $msg = "O Lote foi rejeitado : $cStat - $xMotivo\n";
                throw new nfephpException($msg);
            }
            if ($cStat == '136'){
                $msg = "O Evento foi registrado mas a MDFe não foi localizada : $cStat - $xMotivo\n";
                throw new nfephpException($msg);
            }
            if ($cStat == '215'){
                $msg = "Erro: $cStat - $xMotivo\n";
                throw new nfephpException($msg);
            }
            //o evento foi aceito
            $xmlenvMDe = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlenvMDe->formatOutput = false;
            $xmlenvMDe->preserveWhiteSpace = false;
            $xmlenvMDe->loadXML($Ev,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $evento = $xmlenvMDe->getElementsByTagName("eventoMDFe")->item(0);
            //Processo completo solicitação + protocolo
            $xmlprocMDe = new DOMDocument('1.0', 'utf-8');; //cria objeto DOM
            $xmlprocMDe->formatOutput = false;
            $xmlprocMDe->preserveWhiteSpace = false;
            //cria a tag procEventoNFe
            $procEventoNFe = $xmlprocMDe->createElement('procEventoMDFe');
            $xmlprocMDe->appendChild($procEventoNFe);
            //estabele o atributo de versão
            $eventProc_att1 = $procEventoNFe->appendChild($xmlprocMDe->createAttribute('versao'));
            $eventProc_att1->appendChild($xmlprocMDe->createTextNode($versao));
            //estabelece o atributo xmlns
            $eventProc_att2 = $procEventoNFe->appendChild($xmlprocMDe->createAttribute('xmlns'));
            $eventProc_att2->appendChild($xmlprocMDe->createTextNode($this->URLPortal));
            //carrega o node evento
            $node1 = $xmlprocMDe->importNode($evento, true);
            $procEventoNFe->appendChild($node1);
            //carrega o node retEvento
            $node2 = $xmlprocMDe->importNode($retEvento, true);
            $procEventoNFe->appendChild($node2);
            //salva o xml como string em uma variável
            $procXML = $xmlprocMDe->saveXML();
            //remove as informações indesejadas
            $procXML = str_replace("xmlns:default=\"http://www.w3.org/2000/09/xmldsig#\"",'',$procXML);
            $procXML = str_replace('default:','',$procXML);
            $procXML = str_replace(':default','',$procXML);
            $procXML = str_replace("\n",'',$procXML);
            $procXML = str_replace("\r",'',$procXML);
            $procXML = str_replace("\s",'',$procXML);
            //$filename = $this->evtDir."$chMDFe-$tpEvento-$nSeqEvento-procMDFe.xml";
            $resp = array('bStat'=>true,'cStat'=>$cStat,'xMotivo'=>$xMotivo,'xml_env'=>$Ev,'xml_ret'=>$retorno,'xml'=>$procXML,'nProt'=>$nProt,'dhReceb'=>$dhReceb, 'cOrgao'=>$cOrgao);
            //salva o arquivo xml
            /*if (!file_put_contents($filename, $procXML)){
                $msg = "Falha na gravação do arquivo procMDFe!!";
                throw new nfephpException($msg);
            }*/
        } catch (nfephpException $e) {
            $this->__setError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            $resp = array('bStat'=>false,'cStat'=>$cStat,'xMotivo'=>$xMotivo,'xml'=>$procXML,'xml_env'=>$Ev,'xml_ret'=>$retorno,'nProt'=>$nProt,'dhReceb'=>$dhReceb);
            return false;
        }
        return $retorno;
    } //fim manifDest


    /**
     * mdfeConsNaoEnc
     * Consulta MDFes não encerrados (NT 2015_001)
     *
     * @name mdfeConsNaoEnc
     * @param   integer $tpAmb Tipo de ambiente
     * @param   integer $modSOAP 1 usa __sendSOP e 2 usa __sendSOAP2
     * @param   mixed  $resp variável passada como referencia e irá conter o retorno da função em um array
     * @return mixed false
     *
     * TODO : terminar o código não funcional e não testado
	 // cMun ou Justificativa de cancelamento
     */
    public function mdfeConsNaoEnc($CNPJ='', $UF = '', $tpAmb='', $modSOAP='2'){
		/*file_put_contents('/var/www/html/nf/mdfe/temp/msg.txt',"foi");
		exit();*/
        // Caso o parametro tpAmb seja vazio
        if ($tpAmb == '') {
            $tpAmb = $this->tpAmb;
        }
        $aURL = $this->aURL;
        // Caso a sigla do estado esteja vazia
        if (empty($UF)) {
            $UF = $this->UF;
        } else {
            if ($UF != $this->UF || $tpAmb != $this->tpAmb) {
                // Recarrega as url referentes aos dados passados como parametros para a função
                $aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . "mdfe_ws1.xml", $tpAmb, 'RS');
				
            }
        }
        // Busca o cUF
        //$cUF = $this->cUFlist[$UF];
        $cUF = $this->UF;
        // Identificação do serviço
        $servico = 'MDFeConsNaoEnc';
        // Recuperação da versão
        $versao = $aURL[$servico]['version'];
        // Recuperação da url do serviço
        $urlservico = $aURL[$servico]['URL'];
        // Recuperação do método
        $metodo = $aURL[$servico]['method'];
        // Montagem do namespace do serviço
        $namespace = $this->URLPortal . '/wsdl/' . $servico;
        // Montagem do cabeçalho da comunicação SOAP
        $cabec = '<mdfeCabecMsg xmlns="'. $namespace. '"><cUF>' . $cUF . '</cUF><versaoDados>' . $versao . '</versaoDados></mdfeCabecMsg>';
        // Montagem dos dados da mensagem SOAP
        $dados = '<mdfeDadosMsg xmlns="' . $namespace . '"><consMDFeNaoEnc xmlns="' . $this->URLPortal . '" versao="' . $versao . '"><tpAmb>' . trim($tpAmb) . '</tpAmb><xServ>CONSULTAR NÃO ENCERRADOS</xServ><CNPJ>'.$CNPJ.'</CNPJ></consMDFeNaoEnc></mdfeDadosMsg>';

        if ($modSOAP == '2') {
            $retorno = $this->__sendSOAP2($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb);

        } else {
            $retorno = $this->__sendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb, $UF);
        }

        // Verifica o retorno do SOAP
        if (isset($retorno)) {
            // Tratar dados de retorno
            $doc = new DOMDocument();
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = !empty($doc->getElementsByTagName('cStat')->item(0)->nodeValue) ? $doc->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            //certifica que existe o elemento "cStat" no XML de retortno da SEFAZ.
            if ($cStat == ''){
                $this->errStatus = true;
                $this->errMsg = 'Nao existe o elemento "cStat" no XML de retorno da SEFAZ, erro!!';
                return false;
            }
            $aRetorno['bStat'] = ($cStat == '107');

            $infMDFe = $doc->getElementsByTagName('infMDFe');

            if (isset($infMDFe))
            {
                $i =0;
                foreach ($infMDFe as $mdfe){
                    $aRetorno['infMDFe'][$i]['chMDFe'] = $mdfe->getElementsByTagName('chMDFe')->item(0)->nodeValue;;
                    $aRetorno['infMDFe'][$i]['nProt'] = $mdfe->getElementsByTagName('nProt')->item(0)->nodeValue;;
                    $i++;
                }

                $aRetorno['infMDFe']['qtde'] = $i;
            }

            $aRetorno['xml'] = $doc->saveXML();

            // Tipo de ambiente
            $aRetorno['tpAmb'] = $doc->getElementsByTagName('tpAmb');
            // Versão do aplicativo da SEFAZ
            $aRetorno['verAplic'] = $doc->getElementsByTagName('verAplic')->item(0)->nodeValue;
            // Status do serviço
            $aRetorno['cStat'] = $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
            // Motivo da resposta
            $aRetorno['xMotivo'] = $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            // Código da UF que atendeu a solicitação
            $aRetorno['cUF'] = $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
            // Data e hora da mensagem
            $aRetorno['dhRecbto'] = date('d/m/Y H:i:s', $this->__convertTime($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue));
            // Tempo médio de resposta, em segundos (opcional)
            $aRetorno['tMed'] = !empty($doc->getElementsByTagName('tMed')->item(0)->nodeValue) ? $doc->getElementsByTagName('tMed')->item(0)->nodeValue : '';
            // Data e hora prevista para o retorno do webservice (opcional)
            $aRetorno['dhRetorno'] = !empty($doc->getElementsByTagName('dhRetorno')->item(0)->nodeValue) ? date('d/m/Y H:i:s', $this->__convertTime($doc->getElementsByTagName('dhRetorno')->item(0)->nodeValue)) : '';
            // Obervações (opcional)
            $aRetorno['xObs'] = !empty($doc->getElementsByTagName('xObs')->item(0)->nodeValue) ? $doc->getElementsByTagName('xObs')->item(0)->nodeValue : '';

            return $aRetorno;

        } else {
            $this->errStatus = true;
            $this->errMsg = 'Nao houve retorno Soap verifique a mensagem de erro e o debug!!';
            $aRetorno = false;
        }
    } //fim manifDest

   /**
    * loadSEFAZ
    * Função para extrair o URL, nome do serviço e versão dos webservices das SEFAZ de
    * todos os Estados da Federação do arquivo urlWebServicesMDFe.xml
    *
    * O arquivo xml é estruturado da seguinte forma :
    * <ws>
    *   <uf>
    *      <sigla>AC</sigla>
    *          <homologacao>
    *              <Recepcao service='CTeRecepcao' versao='1.10'>http:// .....
    *              ....
    *          </homologacao>
    *          <producao>
    *              <Recepcao service='CTeRecepcao' versao='1.10'>http:// ....
    *              ....
    *          </producao>
    *   </uf>
    *   <uf>
    *      ....
    * </ws>
    *
    * @name loadSEFAZ
    * @param  string $spathXML  Caminho completo para o arquivo xml
    * @param  string $tpAmb  Pode ser "2-homologacao" ou "1-producao"
    * @param  string $sUF       Sigla da Unidade da Federação (ex. SP, RS, etc..)
    * @return mixed             false se houve erro ou array com os dado do URLs das SEFAZ
    */
    public function loadSEFAZ($spathXML, $tpAmb = '', $sUF) {
        // Verifica se o arquivo xml pode ser encontrado no caminho indicado
        if (file_exists($spathXML)) {
            // Carrega o xml
            $xml = simplexml_load_file($spathXML);
        } else {
            // Sai caso não possa localizar o xml
            return false;
        }
        $aUrl = null;
        // Testa parametro tpAmb
        if ($tpAmb == '') {
            $tpAmb = $this->tpAmb;
        }
        if ($tpAmb == '1'){
            $sAmbiente = 'producao';
        } else {
            // Força homologação em qualquer outra situação
            $tpAmb = '2';
            $sAmbiente = 'homologacao';
        }
        // Extrai a variável cUF do lista
        $alias = $this->aliaslist[$sUF];
        $this->enableSVAN = ($alias == 'SVAN');
        // Estabelece a expressão xpath de busca
        $xpathExpression = "/WS/UF[sigla='" . $alias . "']/$sAmbiente";
        // Para cada "nó" no xml que atenda aos critérios estabelecidos
        foreach ($xml->xpath( $xpathExpression) as $gUF) {
            // Para cada "nó filho" retonado
            foreach ($gUF->children() as $child) {
                $u = (string) $child[0];
                $aUrl[$child->getName()]['URL'] = $u;
                // Em cada um desses nós pode haver atributos como a identificação
                // do nome do webservice e a sua versão
                foreach ($child->attributes() as $a => $b) {
                    $aUrl[$child->getName()][$a] = (string) $b;
                }
            }
        }
        return $aUrl;
    } // Fim loadSEFAZ

    /**
     * __sendSOAP2
     * Função alternativa para estabelecer comunicaçao com servidor SOAP 1.2 da SEFAZ,
     * usando as chaves publica e privada parametrizadas na contrução da classe.
     * Conforme Manual de Integração Versão 4.0.1 Utilizando cURL e não o SOAP nativo
     *
     * @name __sendSOAP2
     * @param string $urlsefaz
     * @param string $namespace
     * @param string $cabecalho
     * @param string $dados
     * @param string $metodo
     * @param numeric $ambiente
     * @param string $UF sem uso mantido apenas para compatibilidade com __sendSOAP
     * @return mixed false se houve falha ou o retorno em xml do SEFAZ
     */
    protected function __sendSOAP2($urlsefaz,$namespace,$cabecalho,$dados,$metodo,$ambiente,$UF=''){
        if ($urlsefaz == ''){
            //não houve retorno
            $this->errMsg = 'URL do webservice não disponível.';
            $this->errStatus = true;
        }


        $data = '';
        $data .= '<?xml version="1.0" encoding="utf-8"?>';
        $data .= '<soap12:Envelope ';
        $data .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $data .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
        $data .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
        $data .= '<soap12:Header>';
        $data .= $cabecalho;
        $data .= '</soap12:Header>';
        $data .= '<soap12:Body>';
        $data .= $dados;
        $data .= '</soap12:Body>';
        $data .= '</soap12:Envelope>';

        //
        $tamanho = strlen($data);
        if($this->enableSCAN){
            $aURL = $this->loadSEFAZ( $this->raizDir . 'config' . DIRECTORY_SEPARATOR . "mdfe_ws1.xml",$ambiente,'SCAN');
            $urlsefaz = $aURL[$servico]['URL'];
        }
        $parametros = Array('Content-Type: application/soap+xml;charset=utf-8;action="'.$namespace."/".$metodo.'"','SOAPAction: "'.$metodo.'"',"Content-length: $tamanho");

        $oCurl = curl_init();
        
        // PROXY
        if(is_array($this->aProxy)){
            curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
            curl_setopt($oCurl, CURLOPT_PROXY, $this->aProxy['IP'].':'.$this->aProxy['PORT']);
            if( $this->aProxy['PASS'] != '' ){
                curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->aProxy['USER'].':'.$this->aProxy['PASS']);
                curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
            } //fim if senha proxy
        } //fim if aProxy



        curl_setopt($oCurl, CURLOPT_URL, $urlsefaz.'');
        curl_setopt($oCurl, CURLOPT_PORT , 443);
        curl_setopt($oCurl, CURLOPT_VERBOSE, 1); //apresenta informações de conexão na tela
        curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($oCurl, CURLOPT_SSLCERT, $this->pubKEY);
        curl_setopt($oCurl, CURLOPT_SSLKEY, $this->priKEY);
        curl_setopt($oCurl, CURLOPT_POST, 1);

        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER,$parametros);




		$__xml = curl_exec($oCurl);
        $info = curl_getinfo($oCurl); //informações da conexão




        $txtInfo ="";
        $txtInfo .= "URL=$info[url]\n";
        $txtInfo .= "Content type=$info[content_type]\n";
        $txtInfo .= "Http Code=$info[http_code]\n";
        $txtInfo .= "Header Size=$info[header_size]\n";
        $txtInfo .= "Request Size=$info[request_size]\n";
        $txtInfo .= "Filetime=$info[filetime]\n";
        $txtInfo .= "SSL Verify Result=$info[ssl_verify_result]\n";
        $txtInfo .= "Redirect Count=$info[redirect_count]\n";
        $txtInfo .= "Total Time=$info[total_time]\n";
        $txtInfo .= "Namelookup=$info[namelookup_time]\n";
        $txtInfo .= "Connect Time=$info[connect_time]\n";
        $txtInfo .= "Pretransfer Time=$info[pretransfer_time]\n";
        $txtInfo .= "Size Upload=$info[size_upload]\n";
        $txtInfo .= "Size Download=$info[size_download]\n";
        $txtInfo .= "Speed Download=$info[speed_download]\n";
        $txtInfo .= "Speed Upload=$info[speed_upload]\n";
        $txtInfo .= "Download Content Length=$info[download_content_length]\n";
        $txtInfo .= "Upload Content Length=$info[upload_content_length]\n";
        $txtInfo .= "Start Transfer Time=$info[starttransfer_time]\n";
        $txtInfo .= "Redirect Time=$info[redirect_time]\n";


        $n = strlen($__xml);
        $x = stripos($__xml, "<");
        $xml = substr($__xml, $x, $n-$x);
        $this->soapDebug = $data."\n\n".$txtInfo."\n".$__xml;
        if ($__xml === false){
            //não houve retorno
            $this->errMsg = curl_error($oCurl) . $info['http_code'] . $cCode[$info['http_code']];
            $this->errStatus = true;
        } else {
            //houve retorno mas ainda pode ser uma mensagem de erro do webservice
            $this->errMsg = $info['http_code'] . ' ' . $cCode[$info['http_code']];
            $this->errStatus = false;
        }
        curl_close($oCurl);



        return $xml;
    } //fim __sendSOAP2

   /**
    * __convertTime
    * Converte o campo data time retornado pelo webservice
    * em um timestamp unix
    *
    * @name __convertTime
    * @param    string   $DH
    * @return   timestamp
    * @access   private
    **/
    protected function __convertTime($DH){
        if ($DH) {
            $aDH = explode('T', $DH);
            $adDH = explode('-', $aDH[0]);
            $atDH = explode(':', $aDH[1]);
            $timestampDH = mktime($atDH[0], $atDH[1], $atDH[2], $adDH[1], $adDH[2], $adDH[0]);
            return $timestampDH;
        }
    } //fim __convertTime

} //fim classe MDFeNFePHP
/**
 * Classe complementar
 * necessária para a comunicação SOAP 1.2
 * Remove algumas tags para adequar a comunicação
 * ao padrão Ruindows utilizado
 *
 * @version 1.0
 * @package MDFePHP
 * @author  Roberto L. Machado <linux.rlm at gmail dot com>
 *
 */
if(class_exists("SoapClient")){
    class NFeSOAP2Client extends SoapClient {
        function __doRequest($request, $location, $action, $version,$one_way = 0) {
            $request = str_replace(':ns1', '', $request);
            $request = str_replace('ns1:', '', $request);
            $request = str_replace("\n", '', $request);
            $request = str_replace("\r", '', $request);
            return parent::__doRequest($request, $location, $action, $version);
        }
    } //fim NFeSOAP2Client
}

/**
 * Classe complementar
 * necessária para extender a classe base Exception
 * Usada no tratamento de erros da API
 * @version 1.0.0
 * @package NFePHP
 *
 */
if(!class_exists('nfephpException')){
    class nfephpException extends Exception {
        public function errorMessage() {
        $errorMsg = $this->getMessage()."\n";
        return $errorMsg;
        }
    }
}
?>
