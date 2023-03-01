<?php
    
    
    function mountAdvancedLog($ws){
        return print_r($ws->_curl_getinfo['http_code']);
    }
    
    
    function httpStatus( $status ) {
        
        
        $json = file_get_contents("../config/httpStatus.json");
        $json = json_decode($json, true);
        
        if (isset($json[$status])){
            print("########################################################################\n");
            print("########################## DADOS SOBRE O ERRO ##########################\n");
            print("########################################################################\n");
            print($json[$status]);
            print("########################################################################\n");
            
        } 
            
        
        // return print_r( ">>$status<<" );
    }