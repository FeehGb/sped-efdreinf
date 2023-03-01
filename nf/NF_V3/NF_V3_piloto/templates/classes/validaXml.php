<?php
/**
 * Criado por Fernando H. Crozetta
 * Data: 14/03/2017
 * Baseado em artigo de Brian Carey
 * 
 * Modo de Uso:
 * php validaXml.php arquivo.xml schema.xsd
 * arquivo.xml = caminho + arquivo a ser validado
 * schema.xsd = caminho + schema a ser usado para validar
 */

require_once("../funcoes/flog.php"); // Gerar log
require_once("../funcoes/fdebug.php"); // Gerar debug


class ValidaXml
{
    private $arquivo_xml;
    private $arquivo_xsd;
    private $erros;
    
    function __construct($arquivo_xml,$arquivo_xsd)
    {
        $this->arquivo_xml = $arquivo_xml;
        $this->arquivo_xsd = $arquivo_xsd;
        $this->erros = '';
    }

    /**
     * Esta função serve para identificar e mostrar os erros na tela,
     * de acordo com seu tipo.
     * @param $error
     * @return string
     */
    public function libxml_display_error($error)
    {
        $return = "\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "- Aviso $error->code ";                
                break;
            case LIBXML_ERR_ERROR:
                $return .= "! Erro $error->code";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "->Erro Fatal $error->code";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " no $error->file";
        }
        $return .= " na linha $error->line\n";

        return $return;
    }

    //Funcao que faz a impressao dos erros na tela
    function libxml_display_errors() {
        $errors = libxml_get_errors();
        print_r($errors );
        foreach ($errors as $error) {
            $erro = $this->libxml_display_error($error);
            print $erro;
            $this->erros.=$erro;
        }
        libxml_clear_errors();
    }

    // Retorna os erros existentes
    public function getErros()
    {
        return $this->erros;
    }

    public function validar()
    {
        // Permite a manipulação de erros pelo usuário
        libxml_use_internal_errors(true);
        
        //Carrega xml na memoria
        $xml = new DOMDocument();
        $xml->load($this->arquivo_xml);
        
        // libxml_use_internal_errors(false)
        
        //Faz o teste de validacao com base no schema passado
        libxml_use_internal_errors(true);
        
        if (!$xml->schemaValidate($this->arquivo_xsd))
        {
            // echo $this->arquivo_xsd;
            // print_r(libxml_get_errors());
            print "Erros encontrados:\n";
            $this->libxml_display_errors();
            fdebug("erro ao assinar xml");
            return false;
        }
        else {
            flog("arquivo xml valido");
            return true;
        }
        
    }

}

/*
A|1.04.00|REINF|
H01|ID1106508200000002019020813323800001|
H02|1||2019-01|2|1|1.04.00|
H03|1|10650820|
H04|1|10650820000182|100000,00|1000,00|0,00|
H01|ID1106508200000002019020813323800002|
H02|1||2019-01|2|1|1.04.00|
H03|1|10650820|
H04|1|10650820000182|100,00|1000,00|0,00|
H01|ID1106508200000002019020813323800003|
H02|1||2019-01|2|1|1.04.00|
H03|1|10650820|
H05|99990010|100000,00|0,00|0,00|100000,00|1000,00|

*/


