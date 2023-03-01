<?php
    
    
    
    
    function assinar($xml)
    {
        return str_replace("></", "><assinatura>LALALA LA LA LALA</assinatura></", $xml);
    }
    
    
    
    
    
    $xml = '
        <?xml version="1.0" encoding="utf-8"?>
        <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Header></soapenv:Header>
            <soapenv:Body>
                <Reinf xmlns="http://www.reinf.esocial.gov.br/schemas/envioLoteEventos/v1_03_02">
                    <loteEventos>
                        
                        <evtInfoContri id="1"><dados>evtInfoContri1 evtInfoContri1 evtInfoContri1</dados></evtInfoContri>
                        <evtInfoContri id="2"><dados>evtInfoContri2 evtInfoContri2 evtInfoContri2</dados></evtInfoContri>
                        <evtInfoContri id="3"><dados>evtInfoContri3 evtInfoContri3 evtInfoContri3</dados></evtInfoContri>
                        
                        <evtEspDesportivo id="1"><dados>evtEspDesportivo1 evtEspDesportivo1 evtEspDesportivo1</dados></evtEspDesportivo>
                        <evtEspDesportivo id="2"><dados>evtEspDesportivo2 evtEspDesportivo2 evtEspDesportivo2</dados></evtEspDesportivo>
                        
                        <evtTotal id="1"><dados>evtTotal1 evtTotal1 evtTotal1</dados></evtTotal>
                        <evtInfoContri id="4"><dados>evtInfoContri4 evtInfoContri4 evtInfoContri4</dados></evtInfoContri>
                        
                        <evtTotalContrib id="1"><dados>evtTotalContrib1 evtTotalContrib1 evtTotalContrib1</dados></evtTotalContrib>
                        <evtInfoContri id="5"><dados>evtInfoContri5 evtInfoContri5 evtInfoContri5</dados></evtInfoContri>
                        
                        <evtExclusao id="1"><dados>evtExclusao1 evtExclusao1 evtExclusao1</dados></evtExclusao>
                        <evtInfoContri id="5"><dados>evtInfoContri5 evtInfoContri5 evtInfoContri5</dados></evtInfoContri>
                        
                    </loteEventos>
                </Reinf>
            </soapenv:Body>
        </soapenv:Envelope>
    ';
    
    
    
    
    $xml = str_replace( 
        array("\n", "\t", "\r") , 
        array(""  , ""  , ""  ) , 
        $xml
    ) ; 
    $xml = preg_replace("/\s+/", " ", $xml);
    
    
    
    
    
    
    $eventos = array(
        "evtInfoContri"    , 
        "evtTabProcesso"   , 
        "evtServTom"       , 
        "evtServPrest"     , 
        "evtAssocDespRec"  , 
        "evtAssocDespRep"  , 
        "evtComProd"       , 
        "evtCPRB"          , 
        "evtPgtosDivs"     , 
        "evtReabreEvPer"   , 
        "evtFechaEvPer"    , 
        "evtEspDesportivo" , 
        "evtTotal"         , 
        "evtTotalContrib"  , 
        "evtExclusao"      , 
    );
    
    
    foreach($eventos as $evento)
    {
        if (strpos($xml, "<$evento ") !== false
        // ||  strpos($xml, "<$evento>") !== false
        ){
            $xml = str_replace( 
                array(  "<$evento "), 
                array("\n<$evento "),
                $xml
            );
        }
    }
    
    
    $xml = str_replace( 
        array(  "</loteEventos>"), 
        array("\n</loteEventos>"),
        $xml
    );
    
    
    
    
    
    $xmlExploded = explode("\n", $xml);
    
    $first = 0;
    $last  = count($xmlExploded)-1;
    
    foreach($xmlExploded as $index => $evento)
    {
        if (($index != $first) && ($index !== $last))
        {
            $xmlExploded[$index] = assinar($evento);
        }
    }
    
    
    $xml = implode("", $xmlExploded);
    
    
    
    print($xml);
    
    
    
    
    
    
    
    