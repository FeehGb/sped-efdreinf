<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * PARAMETRO 1 = xml de envio a partir da inutNFe BASE64
 * PARAMETRO 2 = xml de retorno completo
 */

require_once("/var/www/html/nf/nfe/novo/model/MInutilizacao.php");
/*BACKUP*/
$ini = parse_ini_file("/var/www/html/nf/nfse/config.ini");
system("mysqldump -u ".$ini['usuario']." -p".$ini['senha']." -h ".$ini['servidor']." nfe_".strtolower($argv[1])." INUTILIZACAO > /var/www/html/nf/nfe/novo/INUTILIZACAO.sql");

/*FAZER OS COMANDOS DE CORRECAO DA INUTILIZACAO*/
$MInutilizacao = new MInutilizacao(strtolower($argv[1]));
$sql = "SELECT CONTRIBUINTE_cnpj, CONTRIBUINTE_ambiente, serie_nota, numero_nota_inicial, numero_nota_final, ano, justificativa, xml_env, xml_ret, xml, modelo_nota, protocolo, data_hora, status, status_motivo, uf_responsavel FROM nfe_".strtolower($argv[1]).".INUTILIZACAO";
$returnInut = $MInutilizacao->select($sql);

if(is_array($returnInut)){
    foreach($returnInut as $conteudo){
        if($conteudo['xml'] == "" || $conteudo['xml'] == 'NULL'){
            $MInutilizacao->xml = adicionaProc($conteudo['xml_env'],$conteudo['xml_ret']);
            $MInutilizacao->CONTRIBUINTE_cnpj = $conteudo['CONTRIBUINTE_cnpj'];
            $MInutilizacao->CONTRIBUINTE_ambiente = $conteudo['CONTRIBUINTE_ambiente'];
            $MInutilizacao->serie_nota = $conteudo['serie_nota'];
            $MInutilizacao->numero_nota_inicial = $conteudo['numero_nota_inicial'];
            $MInutilizacao->numero_nota_final = $conteudo['numero_nota_final'];
            $MInutilizacao->ano = $conteudo['ano'];
            $MInutilizacao->justificativa = $conteudo['justificativa'];
            $MInutilizacao->xml_env = $conteudo['xml_env'];
            $MInutilizacao->xml_ret = $conteudo['xml_ret'];
            $MInutilizacao->modelo_nota = $conteudo['modelo_nota'];
            $MInutilizacao->protocolo = $conteudo['protocolo'];
            $MInutilizacao->data_hora = $conteudo['data_hora'];
            $MInutilizacao->status = $conteudo['status'];
            $MInutilizacao->status_motivo = $conteudo['status_motivo'];
            $MInutilizacao->uf_responsavel = $conteudo['uf_responsavel'];
            $MInutilizacao->insert();
        }
    }
}
       exit();
       
       
function adicionaProc($enviado, $retornado){
    $dXML = base64_decode($enviado);
    $retorno = base64_decode($retornado);

 //tratar dados de retorno
        $doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($retorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        
        $retInutNFe = $doc->getElementsByTagName("retInutNFe")->item(0);
        
        //preparar o processo de inutilização
        $inut = new DOMDocument('1.0', 'utf-8');
        $inut->formatOutput = false;
        $inut->preserveWhiteSpace = false;
        $inut->loadXML($dXML, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $inutNFe = $inut->getElementsByTagName("inutNFe")->item(0);
        
        //Processo completo solicitação + protocolo
        $procInut = new DOMDocument('1.0', 'utf-8');
        $procInut->formatOutput = false;
        $procInut->preserveWhiteSpace = false;
        //cria a tag procInutNFe
        $procInutNFe = $procInut->createElement('procInutNFe');
        $procInut->appendChild($procInutNFe);
        //estabele o atributo de versão
        $inutProc_att1 = $procInutNFe->appendChild($procInut->createAttribute('versao'));
        $inutProc_att1->appendChild($procInut->createTextNode('3.10'));
        //estabelece o atributo xmlns
        $inutProc_att2 = $procInutNFe->appendChild($procInut->createAttribute('xmlns'));
        $inutProc_att2->appendChild($procInut->createTextNode('http://www.portalfiscal.inf.br/nfe'));
        //carrega o node cancNFe
        $node1 = $procInut->importNode($inutNFe, true);
        $procInutNFe->appendChild($node1);
        //carrega o node retEvento
        $node2 = $procInut->importNode($retInutNFe, true);
        $procInutNFe->appendChild($node2);
        //salva o xml como string em uma variável
        $procXML = $procInut->saveXML();
        //remove as informações indesejadas
        $procXML  = pClearXml($procXML, false);
        
        return $procXML;
}
        
        
    function pClearXml($xml = '', $remEnc = false)
    {
        $retXml = $xml;
        if ($remEnc) {
            $retXml = str_replace('<?xml version="1.0"?>', '', $retXml);
            $retXml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $retXml);
            $retXml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $retXml);
        }
        $retXml = str_replace("xmlns:default=\"http://www.w3.org/2000/09/xmldsig#\"", '', $retXml);
        $retXml = str_replace('default:', '', $retXml);
        $retXml = str_replace(':default', '', $retXml);
        $retXml = str_replace("\n", '', $retXml);
        $retXml = str_replace("\r", '', $retXml);
        $retXml = str_replace("\s", '', $retXml);
        $retXml = str_replace("\t", '', $retXml);
        return $retXml;
    }
    
?>