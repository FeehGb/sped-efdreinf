<?php

    // ! [03/05/2018] - Crozetta: Este if resolve uma divergencia no servidor do ceará, 
    // !                que recebe e trata os dados de forma diferente
    if ($autorizadora == "CE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache",
            'Connection: Keep-Alive',
            'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)'
        );
    }
    if ($autorizadora == "MG") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
        );
    }
    
    if ($autorizadora == "PE") {
        $soap->curl_header = Array(
            'Content-Type: application/soap+xml;charset=utf-8;action="'.$array_webservice->namespace."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
        );
    }
    if ($autorizadora == "AM") {
        $soap->curl_header = Array(
            // 'Content-Type: application/soap+xml;charset=UTF-8;action="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4/nfeStatusServicoNF"',
            'Content-Type: application/soap+xml;charset=UTF-8;action="'.$array_webservice->namespace."wsdl/".$array_webservice->servico."/".$array_webservice->metodo.'"',
            "Content-length: ".strlen(file_get_contents($arquivo_xml)),
            "Cache-Control: no-cache", 
            "Pragma: no-cache"
		);
    }
    
    
    