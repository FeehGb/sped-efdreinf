<?php
    
    require_once("../funcoes/qrcode.php");
    
    
    /**
     * function nfe_qrcode_gerar
     * 
     * Cria uma url e uma imagem de qrcode
     * 
     *
     * @param String $xml_path  caminho do xml
     * @param String $urlBase   url do qrcode, muda confome o estado e ambiente
     * @return void
     */
    function nfe_qrcode_gerar($xml_path, $urlBase, $tempDIr, $appendOnXml=false)
    {
        // Abre o arquivo XML e cria o objeto cloobal nfe_qrcode_xml;
        nfe_qrcode_openXml($xml_path);
        
        // Cria a url com base nos dados do xml
        $url = nfe_qrcode_createUrl($urlBase, $tempDIr);
        
        // se for para anexar no xml
        if ($appendOnXml)
        {
            // insere a tag de qrcode no xml 
            nfe_qrcode_appendOnXml($url);
            
            // salva o xml
            nfe_qrcode_salvar($xml_path);
        }
        
        // se nao anexar no xml, retorna a url
        else
        {
            // remove as tags que o cobol envia o token
            nfe_qrcode_removerInfNFeSupl();
            
            // salva o xml
            nfe_qrcode_salvar($xml_path);
            
            // retorna
            return $url;
        }
    }
    
    
    /**
     * function nfe_qrcode_openXml
     * 
     * Abre o arquivo XML e cria o objeto cloobal nfe_qrcode_xml
     *
     * @param String $xml_path caminho do xml
     * @return void
     */
    function nfe_qrcode_openXml($xml_path)
    {
        global $nfe_qrcode_xml;
        
        $xml = file_get_contents($xml_path);
        
        $nfe_qrcode_xml = new DOMDocument('1.0', 'utf-8');
        $nfe_qrcode_xml->preservWhiteSpace = false; 
        $nfe_qrcode_xml->loadXML($xml);
    }
    
    
    /**
     * function nfe_qrcode_createUrl
     * 
     * extrai as informacoes do xml para criar a url.
     * 
     * @param  String $urlBase url do qrcode, muda confome o estado e ambiente
     * @return String $url url do qrcode
     */
    function nfe_qrcode_createUrl($urlBase, $tempDIr)
    {
        // extrai os dados do xml
        $idToken     = nfe_qrcode_xml_vl("idToken")      ; 
        $token       = nfe_qrcode_xml_vl("token")        ; 
        $chave       = nfe_qrcode_xml_vl("infNFe", "Id") ; // ## remover NFe?
        $tpAmb       = nfe_qrcode_xml_vl("tpAmb")        ; 
        $CNPJdest    = nfe_qrcode_xml_vl("CNPJ")         ; 
        $dhEmis      = nfe_qrcode_xml_vl("dhEmis")       ; 
        $vNF         = nfe_qrcode_xml_vl("vNF")          ; 
        $vICMS       = nfe_qrcode_xml_vl("vICMS")        ; 
        $DigestValue = nfe_qrcode_xml_vl("DigestValue")  ; 
        
        // cria os parametros 
        $parametros = array();
        
        $parametros[] = "chNFe=".$chave                                          ; 
        $parametros[] = "nVersao=100"                                            ; 
        $parametros[] = "tpAmb=".$tpAmb                                          ; 
        $parametros[] = ($CNPJdest!=="") ? ("cDest=".$CNPJdest) : ""             ; 
        $parametros[] = "dhEmi=".nfe_qrcode_String2Hex($dhEmis)                  ; 
        $parametros[] = "vNF=".$vNF                                              ; 
        $parametros[] = "vICMS=".$vICMS                                          ; 
        $parametros[] = "digVal=".nfe_qrcode_String2Hex($DigestValue)            ; 
        $parametros[] = "cIdToken=".str_pad($idToken,6,"0", STR_PAD_LEFT).$token ; // ZX02 do txt
        
        // Monta a url, cria um hash e coloca no fim da url.
        $urlEncoded   = implode('&', $parametros);
        $cHashQRCode  = sha1($urlEncoded); 
        $parametros   = "$urlEncoded&cHashQRCode=".strtoupper($cHashQRCode);
        
        $url = "$urlBase?$parametros";
        
        $fileName = "{$tempDIr}{$chave}.png";
        
        
        
        $_qrcode = qrcode_gerarImagem($url, $fileName);
        
        // print_r($_qrcode);
        
        return $_qrcode['url'];
    }
    
    
    /**
     * function nfe_qrcode_appendOnXml
     * 
     * coloca o qrcode no xml
     *
     * @param String $url url do qrcode
     * @return void
     */
    function nfe_qrcode_appendOnXml($url)
    {
        global $nfe_qrcode_xml;
        
        $stringQrCode = $nfe_qrcode_xml->createCDATASection($url);
        
        $qrCode = $nfe_qrcode_xml->createElement("qrCode");
        $qrCode->appendChild($stringQrCode);
        
        nfe_qrcode_removerInfNFeSupl();
        
        $infNFeSupl = $nfe_qrcode_xml->createElement("infNFeSupl");
        $infNFeSupl->appendChild($qrCode);
        
        $NFe = $nfe_qrcode_xml->getElementsByTagName("NFe")->item(0);
        $Signature = $nfe_qrcode_xml->getElementsByTagName("Signature")->item(0);
        $NFe->insertBefore($infNFeSupl,$Signature);
    }
    
    
    /**
     * function nfe_qrcode_salvar
     * 
     * salva o xml com o qrcode
     *
     * @param String $xml_path caminho do xml
     * @return void
     */
    function nfe_qrcode_salvar($xml_path)
    {
        global $nfe_qrcode_xml;
        file_put_contents($xml_path, $nfe_qrcode_xml->saveXML());
    }
    
    
    
    // Remove as tags ja usadas
    function nfe_qrcode_removerInfNFeSupl()
    {
        global $nfe_qrcode_xml;
        
        $infNFeSupl = $nfe_qrcode_xml->getElementsByTagName("infNFeSupl")->item(0);
        $infNFeSupl->parentNode->removeChild($infNFeSupl);
    }
    
    // converte string para hexadecimal
    function nfe_qrcode_String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }    
    
    // retorna um elemento pelo nopme da tag
    function nfe_qrcode_getElement($tagName)
    {
        global $nfe_qrcode_xml;
        
        $elm = $nfe_qrcode_xml->getElementsByTagName($tagName)->item(0) ; 
        
        return $elm;
    }
    
    // retorna o valor de uma tag ou atributo
    function nfe_qrcode_xml_vl($tagName, $attr=false)
    {
        if ($attr === false) {
            if ( isset( nfe_qrcode_getElement($tagName)->nodeValue ) ) {
                $value = nfe_qrcode_getElement($tagName)->nodeValue;
            }else{
                $value = "";
            }
        } else {
            $value = nfe_qrcode_getElement($tagName)->getAttribute($attr) ;
        }
        
        return $value;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    