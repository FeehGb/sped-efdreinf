<?php 
    require_once ("../funcoes/flog.php");
    require_once ("../funcoes/fdebug.php");
    require_once ("../funcoes/carrega_config.ini.php"); // Carrega as configuracoes
    /**
    * Classe que realiza a comunicação com um webservice para envio de Soap
    */
    class SoapWebService
    {
        // Dados entrada da classe
        private $arquivo_soap; //Arquivo xml com envelope soap, pronto para ser enviado
        private $cnpj; //CNPJ do emissor
        
        /**
        *	array de parametros para envio de dados
        *	dados array:
        *	url de envio
        *	servico buscado
        *	versao de dados
        */
        private $array_webservice;
        
        
        // Dados internos de configuração
        private $config;
        private $config_cliente;
        // Dados de uso interno
        private $namespace; //Namespace do soap
        public $curl_header; // Header para curl
        public $array_dados_cliente; // Array que contem os dados do config do cliente
        function __construct($arquivo_soap, $cnpj, $array_webservice, $autorizadora="")
        {
            global $config;
            // construção da classe
            $this->arquivo_soap = $arquivo_soap;
            $this->cnpj = $cnpj;
            $this->array_webservice = $array_webservice;
            $this->autorizadora = $autorizadora;
            
            $this->conteudo_soap = file_get_contents($this->arquivo_soap);
            $this->namespace = $array_webservice->namespace;
            
            // Carregamento de array de dados
            // $this->config = parse_ini_file("../config/config.ini");
            $this->config = $config;
            $this->array_dados_cliente = parse_ini_file($this->config['dados']."/config_cliente/".$cnpj.".ini",true);
            
            
            $this->custonHeader();
        }
        
        
        
        
        function custonHeader()
        {
            $namespace  = $this->array_webservice->namespace ; 
            $servico    = $this->array_webservice->servico   ; 
            $metodo     = $this->array_webservice->metodo    ; 
            $tamanho    = strlen($this->conteudo_soap)       ; 
            
            
            switch($this->autorizadora)
            {
                case "CE" : 
                    $curl_header = Array(
                        "Content-Type: application/soap+xml;charset=utf-8;action=\"$namespace/$metodo\"",
                        "Content-length: $tamanho",
                        "Cache-Control: no-cache", 
                        "Pragma: no-cache",
                        'Connection: Keep-Alive',
                        'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)'
                    );
                break;
                
                case "MG" : 
                    $curl_header = Array(
                        "Content-Type: application/soap+xml;charset=utf-8;action=\"$namespace/$metodo",
                        "Content-length: $tamanho",
                        "Cache-Control: no-cache", 
                        "Pragma: no-cache"
                    );
                break;
                
                case "PE" : 
                    $curl_header = Array(
                        "Content-Type: application/soap+xml;charset=utf-8;action=\"$namespace/$metodo",
                        "Content-length: $tamanho",
                        "Cache-Control: no-cache", 
                        "Pragma: no-cache"
                    );
                break;
                
                case "AM" : 
                    $curl_header = Array(
                        "Content-Type: application/soap+xml;charset=UTF-8;action=\"{$namespace}wsdl/$servico/$metodo",
                        "Content-length: $tamanho",
                        "Cache-Control: no-cache", 
                        "Pragma: no-cache"
                    );
                break;
                
                
                default:
                    $curl_header = Array(
                        "Content-Type: application/soap+xml;charset=utf-8;action=\"$namespace\"",
                        "SOAPAction: \"$namespace/wsdl/$servico$metodo\"",
                        "Content-length: $tamanho",
                        "Cache-Control : no-cache", 
                        "Pragma: no-cache"
                    );
                break;
            }
            
            
            $this->curl_header = $curl_header ; 
        }
        
        
        
        
        
        /* 
            Realiza a comunicação com o webservice
            Original por: Eduardo
        */
        public function comunicar($debug=false)
        {
            // Abre um novo curl para comunicação
            $curl = curl_init();
            
            // Configura uma nova conexão
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, $this->array_dados_cliente['curl']["fresh_connect"]);
            
            // Configuração curl, baseado nos dados do client
            // print($this->array_webservice->url);
            curl_setopt($curl, CURLOPT_URL, $this->array_webservice->url);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->conteudo_soap);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->curl_header);
            
            
            if($this->array_dados_cliente['curl']["conexao_segura"] == "S")
            {
                // Dados certificado Digital
                $certificado_pubKey = $this->array_dados_cliente['certificado']['raiz_certificado']."_pubKey.pem";
                $certificado_priKey = $this->array_dados_cliente['certificado']['raiz_certificado']."_priKey.pem";
                
                curl_setopt($curl, CURLOPT_SSLCERT, $certificado_pubKey);
                curl_setopt($curl, CURLOPT_SSLKEY , $certificado_priKey);
            }
            
            if ($debug){
                $this->array_dados_cliente['curl']["verbose"] = "1";
            }
            
            
            // dados do config
            if($this->array_dados_cliente['curl'][         "porta"] != ""){ curl_setopt($curl,           CURLOPT_PORT , $this->array_dados_cliente['curl'][          "porta"]); } 
            if($this->array_dados_cliente['curl'][       "verbose"] != ""){ curl_setopt($curl,        CURLOPT_VERBOSE , $this->array_dados_cliente['curl'][        "verbose"]); } 
            if($this->array_dados_cliente['curl'][        "header"] != ""){ curl_setopt($curl,         CURLOPT_HEADER , $this->array_dados_cliente['curl'][         "header"]); } 
            if($this->array_dados_cliente['curl'][    "sslversion"] != ""){ curl_setopt($curl,     CURLOPT_SSLVERSION , $this->array_dados_cliente['curl'][     "sslversion"]); } 
            if($this->array_dados_cliente['curl']["ssl_verifyhost"] != ""){ curl_setopt($curl, CURLOPT_SSL_VERIFYHOST , $this->array_dados_cliente['curl'][ "ssl_verifyhost"]); } 
            if($this->array_dados_cliente['curl']["ssl_verifypeer"] != ""){ curl_setopt($curl, CURLOPT_SSL_VERIFYPEER , $this->array_dados_cliente['curl'][ "ssl_verifypeer"]); } 
            if($this->array_dados_cliente['curl']["connecttimeout"] != ""){ curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , $this->array_dados_cliente['curl'][ "connecttimeout"]); } 
            if($this->array_dados_cliente['curl'][       "timeout"] != ""){ curl_setopt($curl,        CURLOPT_TIMEOUT , $this->array_dados_cliente['curl'][        "timeout"]); } 
            if($this->array_dados_cliente['curl'][     "maxredirs"] != ""){ curl_setopt($curl,      CURLOPT_MAXREDIRS , $this->array_dados_cliente['curl'][      "maxredirs"]); } 
            if($this->array_dados_cliente['curl']["followlocation"] != ""){ curl_setopt($curl, CURLOPT_FOLLOWLOCATION , $this->array_dados_cliente['curl'][ "followlocation"]); } 
            if($this->array_dados_cliente['curl'][          "post"] != ""){ curl_setopt($curl,           CURLOPT_POST , $this->array_dados_cliente['curl'][           "post"]); } 
            if($this->array_dados_cliente['curl']["returntransfer"] != ""){ curl_setopt($curl, CURLOPT_RETURNTRANSFER , $this->array_dados_cliente['curl'][ "returntransfer"]); } 
            
            //curl_setopt($curl, URLOPT_FORBID_REUSE,0); 
            //curl_setopt($curl, CURLOPT_REFERER,'nfe.sefaz.ce.gov.br'); 
            
            
            // Dados PROXY
            if ($this->array_dados_cliente['proxy']['servidor']!= "") {
                curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 1);
                curl_setopt($curl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
                curl_setopt($curl, CURLOPT_PROXY, 
                    $this->array_dados_cliente['proxy']["servidor"].':'.$this->array_dados_cliente['proxy']["porta"]);
                
                if( $this->array_dados_cliente['proxy']['senha'])
                {
                    curl_setopt($curl, CURLOPT_PROXYUSERPWD, 
                        $this->array_dados_cliente['proxy']["usuario"].':'.$this->array_dados_cliente['proxy']["senha"]);
                    curl_setopt($curl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
                }
            }
            
            fdebug($curl);
            
            // Executar chamada o servidor 
            $__xml  = curl_exec ($curl) ; 
            $__erro = curl_error($curl) ; 
            
            if($debug){
                // print("~~##~~##~~##~~ curl_header ~~##~~##~~##~~\n"    ) ; 
                // print_r ( $this->curl_header ) ;
                // print("~~##~~##~~##~~ END - curl_header ~~##~~##~~##~~\n\n"    ) ; 
                // print("~~##~~##~~##~~ array_webservice ~~##~~##~~##~~\n"    ) ; 
                // print_r ( $this->array_webservice    ) ; 
                // print("~~##~~##~~##~~ END - array_webservice ~~##~~##~~##~~\n\n"    ) ; 
            }
            
            
            
            
            if (!empty($__erro)){
                if($debug){
                    print_r(curl_getinfo($curl));
                    print("WEBSERVICE_ERROR >>>>>$__erro<<<<<");
                } else {
                    addLog("WEBSERVICE_ERROR", $__erro);
                }
            }
            
            $info = curl_getinfo($curl);
            
            $txtInfo  = "";
            $txtInfo .= "URL=$info[url]\n";
            $txtInfo .= "Content type=$info[content_type]\n";
            $txtInfo .= "Http Code=$info[http_code]\n";
            $txtInfo .= "Header Size=$info[header_size]\n";
            $txtInfo .= "Request Size=$info[request_size]\n";
            $txtInfo .= "Filetime=$info[filetime]\n";
            $txtInfo .= "SSL Verify Result=$info[ssl_verify_result]\n";
            $txtInfo .= "Redirect Count=$info[redirect_count]\n";
            $txtInfo .= "Total Time=$info[total_time]\n";
            $txtInfo .= "Namelookup=$info[namelookup_time]\n";
            $txtInfo .= "Connect Time=$info[connect_time]\n";
            $txtInfo .= "Pretransfer Time=$info[pretransfer_time]\n";
            $txtInfo .= "Size Upload=$info[size_upload]\n";
            $txtInfo .= "Size Download=$info[size_download]\n";
            $txtInfo .= "Speed Download=$info[speed_download]\n";
            $txtInfo .= "Speed Upload=$info[speed_upload]\n";
            $txtInfo .= "Download Content Length=$info[download_content_length]\n";
            $txtInfo .= "Upload Content Length=$info[upload_content_length]\n";
            $txtInfo .= "Start Transfer Time=$info[starttransfer_time]\n";
            $txtInfo .= "Redirect Time=$info[redirect_time]\n";
            //$txtInfo .= "Certinfo=$info[certinfo]\n";
            if($debug){
                // print ($txtInfo);
            }else{
                fdebug($txtInfo);
            }
            // fdebug("CURL INFO " . curl_getinfo($curl, CURLINFO_HTTP_CODE)); 
            
            // print_r($__xml);
            
            // Retirar espacoes no inicio do retorno do servidor
            $n = strlen($__xml);
            $x = stripos($__xml, "<");
            $xmlRetorno = htmlspecialchars_decode(substr($__xml, $x, $n-$x));
            curl_close($curl);
            // return $__xml;
            return $xmlRetorno;
            
        }
    }
    



