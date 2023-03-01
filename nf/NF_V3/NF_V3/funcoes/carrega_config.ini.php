<?php
    
    
    function carrega_config_ini()
    {
        global $config;
        
        $mypath = __FILE__ ; 
        
        if (strpos($mypath, "/nfcli/") !== false){
            $filename="configcli";
        }else{
            $filename="config";
        }
        
        $config = (array) parse_ini_file("../config/$filename.ini");
        
    }
    
    carrega_config_ini();
    