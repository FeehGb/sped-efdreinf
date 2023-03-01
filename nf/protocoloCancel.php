<?php

    $Ev = base64_decode(base64_decode(file_get_contents("entrada")));
    
    $xmlenvEvento = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
    $xmlenvEvento->formatOutput = false;
    $xmlenvEvento->preserveWhiteSpace = false;
    $xmlenvEvento->loadXML($Ev, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
    $evento = $xmlenvEvento->getElementsByTagName("evento")->item(0);
    //Processo completo solicitação + protocolo
    $xmlprocEvento = new DOMDocument('1.0', 'utf-8');
    $xmlprocEvento->formatOutput = false;
    $xmlprocEvento->preserveWhiteSpace = false;
    //cria a tag procEventoNFe
    $procEventoNFe = $xmlprocEvento->createElement('procEventoNFe');
    $xmlprocEvento->appendChild($procEventoNFe);
    //estabele o atributo de versão
    $eventProc_att1 = $procEventoNFe->appendChild($xmlprocEvento->createAttribute('versao'));
    $eventProc_att1->appendChild($xmlprocEvento->createTextNode($versao));
    //estabelece o atributo xmlns
    $eventProc_att2 = $procEventoNFe->appendChild($xmlprocEvento->createAttribute('xmlns'));
    $eventProc_att2->appendChild($xmlprocEvento->createTextNode($this->URLPortal));
    //carrega o node evento
    $node1 = $xmlprocEvento->importNode($evento, true);
    $procEventoNFe->appendChild($node1);
    //carrega o node retEvento
    $node2 = $xmlprocEvento->importNode($retEvento, true);
    $procEventoNFe->appendChild($node2);
    //salva o xml como string em uma variável
    $procXML = $xmlprocEvento->saveXML();
    //remove as informações indesejadas
    $procXML = $this->pClearXml($procXML, false);
    //salva o arquivo xml
                $aRetorno['xml'] = $procXML;

?>