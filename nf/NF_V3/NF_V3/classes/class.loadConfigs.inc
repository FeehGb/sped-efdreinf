<?php

class loadConfigs{
    
    /**
     * __construct
     */
    function __construct($cnpj=false, $servico=false, $estado=false)
    {
        $this->cnpj    = $cnpj    ; 
        $this->servico = $servico ; 
        $this->estado  = $estado  ; 
        
        $this->Configs = array();
        $this->Configs[ 'v3_config'] = $this->get_v3_config();
        $this->Configs['cli_config'] = $this->get_cliconfig();
        $this->Configs[ 'ws_config'] = $this->get_ws_config();
    }
    
    
    /**
     * get_ws_config
     */
    function get_ws_config(){
        $file = file_get_contents("../servicos/{$this->servico}/ws.json");
        $json = json_decode($file, true);
        
        $this->ws_config = $this->combine(
            $json['default'], 
            $json[$this->estado]
        );
        
        if ( isset($this->Configs['cli_config']) ) {
            $this->Configs['cli_config']['curl'] = $this->ws_config['curl'] ; 
        }
        
        return $this->ws_config;
    }
    
    
    /**
     * get_cliconfig
     */
    function get_cliconfig(){
        $this->cliconfig = $this->parse_ini_file(
            $this->v3config['dados']."/config_cliente/{$this->cnpj}.ini"
        );
        return $this->cliconfig;
    }
    
    
    /**
     * get_v3config
     */
    function get_v3_config(){
        $this->v3config = $this->parse_ini_file(
            "../config/config.ini"
        );
        return $this->v3config;
    }
    
    
    /**
     * parse_ini_file
     */
    function parse_ini_file($file){
        return parse_ini_file($file, true);
    }
    
    
    /**
     * combine
     */
    function combine($a1, $a2) {
        foreach($a2 as $k => $v) {
            if(is_array($v)) {
                if(!isset($a1[$k])) {$a1[$k] = null;}
                $a1[$k] = $this->combine($a1[$k], $v);
            } else {
                $a1[$k] = $v;
            }
        }
        return $a1;
    }
    
    
    /**
     * getJson
     */
    function getJson() {
        return $this->Configs ; 
    }
    
}

