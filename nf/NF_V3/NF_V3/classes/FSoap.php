<?php 

/**
* Classe Soap de comunicação.
* Os dados de soap que estão fixos, são padrão de toda comuinicação soap.
* Caso seja necessário alterar algum parametro que esta fixo:
* 1 - Manter usar o valor default em forma de parametro.
* 2 - Criar get/set do parametro
* 3 - Usar set do parametro onde for necessario
* Nenhum parametro extra deve ser passado no construtor da classe
*/

require_once("../funcoes/flog.php"); // para gerar log
require_once("../funcoes/fdebug.php"); //para gerar debug
class FSoap
{	
	
	private $parametros_webservice;
	private $parametros_cliente;

	private $soap;
	function __construct($parametros_webservice,$parametros_cliente)
	{
		$this->parametros_webservice = $parametros_webservice;
		$this->parametros_cliente = $parametros_cliente;


		/* Construtor Soap */
		// Dados de certificado
		$this->arquivo_certificado = $this->parametros_cliente['certificado']['caminho_certificado'].$this->parametros_cliente['empresa']['cnpj']."_certKey.pem";
		$this->senha_certificado = $this->parametros_cliente['certificado']['senha'];
		// Dados do soap
		$this->config_soap = Array(
			'local_cert'=>$this->arquivo_certificado,
			'passphrase'=>$this->senha_certificado,
			'soap_version'=>SOAP_1_2,
			'encoding'=> 'UTF-8',
			'verifypeer'=> false,
            'verifyhost'=> false,
            'style'=> SOAP_DOCUMENT,
            'use'=> SOAP_LITERAL,
            'trace'=> true,
            'compression'=> 0,
            'exceptions'=> false,
            'cache_wsdl'=> WSDL_CACHE_NONE
		);
		$this->soap = new NFeSOAP2Client($this->parametros_webservice['url']."?wsdl",$this->config_soap);
	}

	// monta o cabecalho do soap (modo raw)
	public function set_cabecalho($tag_cabecalho,$cabecalho)
	{
		$namespace = $this->parametros_webservice['url'] . '/wsdl/' . $this->parametros_webservice['servico'];
		$temp = new SoapVar($cabecalho,XSD_ANYXML);
		$header = new SoapHeader($namespace,$tag_cabecalho,$temp);
		$this->soap->__setSoapHeaders($header);
	}

	public function enviar($corpo)
	{
		$temp_corpo = new SoapVar($corpo,XSD_ANYXML);
		$resp = $this->soap->__soapCall($this->parametros_webservice['metodo'],array($temp_corpo)); //Sempre passar o corpo como array
		if (is_soap_fault($resp)) {
           $soapFault = "SOAP Fault: (faultcode: {$resp->faultcode}, faultstring: {$resp->faultstring})";
           flog("SOAP FAULT:\n".$soapFault);
        }
        $resposta = $this->soap->__getLastResponse();
        print_r($resposta);
        
	}

}

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
            // $request = str_replace(':ns1', '', $request);
            // $request = str_replace('ns1:', '', $request);
            $request = str_replace('xmlns:ns1="http://www.portalfiscal.inf.br/mdfe/wsdl/MDFeRecepcao"', '', $request);
            $request = str_replace(':ns2', '', $request);
            $request = str_replace('ns2:', '', $request);
            $request = str_replace("\n", '', $request);
            $request = str_replace("\r", '', $request);

            flog("DO_REQUEST:\n".$request."\n");
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