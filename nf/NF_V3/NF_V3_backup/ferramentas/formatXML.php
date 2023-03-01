<?php
    /**
     * 
     */
    function formatXML($xml, $title='')
    {
        $dom = new DOMDocument() ; 
        $dom->preserveWhiteSpace = false ; 
        $dom->formatOutput =  true ; 
        $dom->loadXML($xml) ; 
        
        $limpar = array();
        
        foreach($dom->getElementsByTagName('X509Certificate' ) as $node ) { $limpar[] = $node ; } 
        foreach($dom->getElementsByTagName('SignatureValue'  ) as $node ) { $limpar[] = $node ; } 
        
        foreach($limpar as $node) { 
            $node->nodeValue = "@@LIMPO PARA DEBUG@@" ; 
        }
        
        $xml = $dom->saveXML(
            // $dom->documentElement
        );
        
        $ln1  = "\n------ INI $title ------\n\n" ; 
        $ln2  = "\n------ END $title ------\n\n" ; 
        return "{$ln1}{$xml}{$ln2}" ;
    }
    
    
    /*
    if (isset($argv[1])){
        
        $format = formatXML(file_get_contents($argv[1]));
        
        $format = str_replace('<', "\e[95m<", $format);
        $format = str_replace('>',  ">\e[0m", $format);
        
        // $format = str_replace('="',  "=\"\e[96m", $format);
        $format = preg_replace("/\s(\w*\:*\w*)\=/", " \e[96m\$1\e[0m=", $format);
        $format = preg_replace("/\=\"(.*)\"/", "=\"\e[94m\$1\e[0m\"", $format);
        
        $format = preg_replace("/\"(\s|\/>)/", "\"\e[0m\$1", $format);
        
        print($format);
    }
    */
    