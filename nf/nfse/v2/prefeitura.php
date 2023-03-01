<?php
    
    /*
        Programa:  prefeitura.php
        DescricÃ£o: Classe responsavel por se comunicar com a prefeitura atravez de uma nota fiscal de servico
        Autor:     J. Eduardo Lino | Fernando C. (15/03/2017)
    */

    require_once("/var/www/html/nf/nfse/control/CAssinaturaDigital.php");

    class Prefeitura
    {
        private $xml                   = "" ; 
        private $metodo                = "" ; 
        private $paramentros           = "" ; 
        private $tipo_envio            = "" ; 
        private $nome_retorno          = "" ; 
        private $parametros_assinatura = "" ; 
        private $diretorio_certificado = "" ; 
        private $senha_certificado     = "" ; 
        private $certificado_priKey    = "" ; 
        private $certificado_pubKey    = "" ; 
        private $certificado_certKey   = "" ; 

        function __construct($xml, $parametros_webservice, $metodo, $tipo_envio, $senha_certificado, $parametros_assinatura, $nome_retorno)
        {
            $this->paramentros  = parse_ini_file($parametros_webservice);
            $this->nome_retorno = $nome_retorno;
            $this->metodo       = $metodo;

            $this->xml                   = file_get_contents($xml);
            $this->tipo_envio            = $tipo_envio;
            $this->senha_certificado     = $senha_certificado;
            $this->parametros_assinatura = $parametros_assinatura;
            $this->diretorio_certificado = "/var/www/html/nf/nfse/certificados/".$this->paramentros["cnpj"].".pfx";;

            $this->xml = trim(preg_replace('/\s\s+/', '', $this->xml));
            $this->xml = utf8_encode($this->xml);

            $this->certificado_priKey  = "/var/www/html/nf/nfse/certificados/".$this->paramentros["cnpj"]."_priKey.pem"  ; 
            $this->certificado_pubKey  = "/var/www/html/nf/nfse/certificados/".$this->paramentros["cnpj"]."_pubKey.pem"  ; 
            $this->certificado_certKey = "/var/www/html/nf/nfse/certificados/".$this->paramentros["cnpj"]."_certKey.pem" ; 
        } 

        public function encapsulaXML()
        {
            // Codigo Tom da cidade de Campo Magro
            if($this->paramentros["codigo_tom"] == "78427") 
            {
                $xml_soap = '';
                $xml_soap .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:e="http://www.betha.com.br/e-nota-contribuinte-ws">';
                $xml_soap .= '<soapenv:Header/>';
                $xml_soap .= '<soapenv:Body>';
                $xml_soap .= "<e:$this->metodo>";
                $xml_soap .= '<nfseCabecMsg><![CDATA[<cabecalho xmlns="http://www.betha.com.br/e-nota-contribuinte-ws" versao="2.02"><versaoDados>2.02</versaoDados></cabecalho>]]></nfseCabecMsg>';
                $xml_soap .= '<nfseDadosMsg><![CDATA['.$this->xml.']]></nfseDadosMsg>';
                $xml_soap .= "</e:$this->metodo>";
                $xml_soap .= '</soapenv:Body>';
                $xml_soap .= '</soapenv:Envelope>';
                $this->xml = $xml_soap;
            } 
        }

        public function assinaXML()
        {
            $CAssinaturaDigital = new CAssinaturaDigital();
            $CAssinaturaDigital->xml = $this->xml;
            $CAssinaturaDigital->arquivoPFX = $this->diretorio_certificado;
            $array_paramentros = explode("|", $this->parametros_assinatura);
            
            print_r($array_paramentros);
            
            
            $paramentro_1 = $array_paramentros[0];
            $paramentro_2 = $array_paramentros[1];
            $paramentro_3 = $array_paramentros[2];

            if(!$CAssinaturaDigital->assinarXml($paramentro_1, $paramentro_2, $this->paramentros["cnpj"]))
            {
                echo $CAssinaturaDigital->mensagemErro;
                return false;
            }

            $this->xml = $CAssinaturaDigital->xml;

            if($paramentro_3 != "")
            {
                $CAssinaturaDigital->xml = $this->xml;

                if(!$CAssinaturaDigital->assinarXml($paramentro_3, "", $this->paramentros["cnpj"]))
                {
                    echo $CAssinaturaDigital->mensagemErro;
                    return false;
                }

                $this->xml = $CAssinaturaDigital->xml;
            }
        }


        public function comunicaWebService()
        {
            $tamanho_xml = strlen($this->xml);

            // file_put_contents("/var/www/html/eduardo/cancelamento_novo.txt", $this->xml);

            $parametros_soap = Array("Host: ".$this->paramentros["ip"], 'Content-Type: application/soap+xml;charset=utf-8',"Content-length: $tamanho_xml");

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_FRESH_CONNECT, $this->paramentros["fresh_connect"]);

            
            $url_producao    = str_replace('?wsdlurl_producao'   , '', $this->paramentros["url_producao"]    ) ; 
            $url_homologacao = str_replace('?wsdlurl_homologacao', '', $this->paramentros["url_homologacao"] ) ; 
            
            echo "url_producao    -> " . $url_producao    ; 
            echo "url_homologacao -> " . $url_homologacao ; 
            
            if($this->paramentros["ambiente"] == "1")
            {
                curl_setopt($curl, CURLOPT_URL, $url_producao);
            }
            else
            {
                curl_setopt($curl, CURLOPT_URL, $url_homologacao);
            }

            // Se servidor possui proxy
            if($this->paramentros["porta"] == "S")
            {
                if($this->ConfigWs[0]['proxy'] == "S")
                {
                    curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 1);
                    curl_setopt($curl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
                    curl_setopt($curl, CURLOPT_PROXY, $this->paramentros["servidor"].':'.$this->paramentros["porta"]);

                    if( $this->ConfigWs[0]['proxy_senha'] != '' )
                    {
                        curl_setopt($curl, CURLOPT_PROXYUSERPWD, $this->paramentros["usuario"].':'.$this->paramentros["senha"]);
                        curl_setopt($curl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
                    }
                }
            }	 

            if($this->paramentros["porta"          ] != "" ) { curl_setopt($curl,           CURLOPT_PORT , $this->paramentros["porta"          ] ) ; }
            if($this->paramentros["verbose"        ] != "" ) { curl_setopt($curl,        CURLOPT_VERBOSE , $this->paramentros["verbose"        ] ) ; }
            if($this->paramentros["header"         ] != "" ) { curl_setopt($curl,         CURLOPT_HEADER , $this->paramentros["header"         ] ) ; }
            if($this->paramentros["sslversion"     ] != "" ) { curl_setopt($curl,     CURLOPT_SSLVERSION , $this->paramentros["sslversion"     ] ) ; }
            if($this->paramentros["ssl_verifyhost" ] != "" ) { curl_setopt($curl, CURLOPT_SSL_VERIFYHOST , $this->paramentros["ssl_verifyhost" ] ) ; }
            if($this->paramentros["ssl_verifypeer" ] != "" ) { curl_setopt($curl, CURLOPT_SSL_VERIFYPEER , $this->paramentros["ssl_verifypeer" ] ) ; }
            if($this->paramentros["connecttimeout" ] != "" ) { curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , $this->paramentros["connecttimeout" ] ) ; }
            if($this->paramentros["timeout"        ] != "" ) { curl_setopt($curl,        CURLOPT_TIMEOUT , $this->paramentros["timeout"        ] ) ; }
            if($this->paramentros["maxredirs"      ] != "" ) { curl_setopt($curl,      CURLOPT_MAXREDIRS , $this->paramentros["maxredirs"      ] ) ; }
            if($this->paramentros["followlocation" ] != "" ) { curl_setopt($curl, CURLOPT_FOLLOWLOCATION , $this->paramentros["followlocation" ] ) ; }

            if($this->paramentros["conexao_segura"] == "S")
            {
                curl_setopt($curl, CURLOPT_SSLCERT, $this->certificado_pubKey);
                curl_setopt($curl, CURLOPT_SSLKEY , $this->certificado_priKey);
            }
            
            if($this->paramentros["post"] != "") curl_setopt($curl, CURLOPT_POST, $this->paramentros["post"]);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->xml);
            if($this->paramentros["returntransfer"] != "") curl_setopt($curl, CURLOPT_RETURNTRANSFER, $this->paramentros["returntransfer"]);

            curl_setopt($curl, CURLOPT_HTTPHEADER, $parametros_soap);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

            // Executar chamada o servidor 
            $__xml = curl_exec($curl);
            $info = curl_getinfo($curl);
          
            $txtInfo   = array();
            
            $txtInfo[] =                     "URL=$info[url]"                     ; 
            $txtInfo[] =            "Content type=$info[content_type]"            ; 
            $txtInfo[] =               "Http Code=$info[http_code]"               ; 
            $txtInfo[] =             "Header Size=$info[header_size]"             ; 
            $txtInfo[] =            "Request Size=$info[request_size]"            ; 
            $txtInfo[] =                "Filetime=$info[filetime]"                ; 
            $txtInfo[] =       "SSL Verify Result=$info[ssl_verify_result]"       ; 
            $txtInfo[] =          "Redirect Count=$info[redirect_count]"          ; 
            $txtInfo[] =              "Total Time=$info[total_time]"              ; 
            $txtInfo[] =              "Namelookup=$info[namelookup_time]"         ; 
            $txtInfo[] =            "Connect Time=$info[connect_time]"            ; 
            $txtInfo[] =        "Pretransfer Time=$info[pretransfer_time]"        ; 
            $txtInfo[] =             "Size Upload=$info[size_upload]"             ; 
            $txtInfo[] =           "Size Download=$info[size_download]"           ; 
            $txtInfo[] =          "Speed Download=$info[speed_download]"          ; 
            $txtInfo[] =            "Speed Upload=$info[speed_upload]"            ; 
            $txtInfo[] = "Download Content Length=$info[download_content_length]" ; 
            $txtInfo[] =   "Upload Content Length=$info[upload_content_length]"   ; 
            $txtInfo[] =     "Start Transfer Time=$info[starttransfer_time]"      ; 
            $txtInfo[] =           "Redirect Time=$info[redirect_time]"           ; 
            $txtInfo[] =                "Certinfo=$info[certinfo]"                ; 
            
            $txtInfo = implode("\n", $txtInfo);

            // Retirar espacoes no inicio do retorno do servidor
            $n = strlen($__xml);
            $x = stripos($__xml, "<");
            $xmlRetorno = htmlspecialchars_decode(substr($__xml, $x, $n-$x));
              curl_close($curl);

              //file_put_contents("/var/www/html/eduardo/retorno_cancelamento.txt", $xmlRetorno);

              
              if($this->paramentros["codigo_tom"] == "75353") 
              {
                $this->retornoCuritiba($xmlRetorno);
              }
              
              if($this->paramentros["codigo_tom"] == "78427") 
              {
                  $this->retornoCampoMagro($xmlRetorno);
              }
        }

        private function retornoCampoMagro($xmlRetorno)
        {
            if($this->tipo_envio == "envio")
            {
                if(strpos($xmlRetorno,"EnviarLoteRpsSincronoResposta") === false){
                }else{
                    $xml = explode("EnviarLoteRpsSincronoResposta", $xmlRetorno);
                    $xmlRetorno = "<EnviarLoteRpsSincronoResposta".$xml[1]."EnviarLoteRpsSincronoResposta>";
                }

                $doc = new DOMDocument('1.0', 'utf-8'); 
                $doc->formatOutput = false;
                $doc->preserveWhiteSpace = false;
                $doc->loadXML($xmlRetorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

                $mensagem = @$doc->getElementsByTagName("Mensagem");
                $codigo = @$doc->getElementsByTagName("Codigo");
                $mensagem = @$mensagem->item(0)->nodeValue;
                $codigo = @$codigo->item(0)->nodeValue;
                $status = "";

                if($mensagem != "" && $mensagem != null)
                {
                    $status = "N";
                    $mensagem = utf8_decode($mensagem);
                    $numero_nota = "";

                    $conteudo_arquivo_retorno = "";
                    $conteudo_arquivo_retorno .= trim($status)."|";
                    $conteudo_arquivo_retorno .= trim($mensagem)."||||";
                }
                else 
                {
                    $status = "S";
                    $numero_lote = $doc->getElementsByTagName("NumeroLote");
                    $data_recebimento = $doc->getElementsByTagName("DataRecebimento");
                    $protocolo = $doc->getElementsByTagName("Protocolo");
                    $codigo_verificacao = $doc->getElementsByTagName("CodigoVerificacao");
                    $numero_nota = $doc->getElementsByTagName("Numero");
                    $link_nota = $doc->getElementsByTagName("OutrasInformacoes");
                    
                    $nro_rps = $numero_lote->item(0)->nodeValue;
                    $numero_nota = $numero_nota->item(0)->nodeValue;
                      $codigo_verificacao = $codigo_verificacao->item(0)->nodeValue;
                    $data_recebimento = $data_recebimento->item(0)->nodeValue;
                    $protocolo = $protocolo->item(0)->nodeValue;
                    $mensagem = explode("link=",$link_nota->item(0)->nodeValue);
                    $mensagem = $mensagem[1];

                    $conteudo_arquivo_retorno = "";
                    $conteudo_arquivo_retorno .= trim($status)."||";
                    $conteudo_arquivo_retorno .= trim($numero_nota)."|";
                    $conteudo_arquivo_retorno .= trim($protocolo)."|";
                    $conteudo_arquivo_retorno .= trim($codigo_verificacao)."|";
                    $conteudo_arquivo_retorno .= $mensagem;
                }

                file_put_contents("/user/nfse/".$this->paramentros["cnpj"]."/CaixaSaida/".str_replace(".xml", ".txt", $this->nome_retorno), $conteudo_arquivo_retorno);
            }
            else if($this->tipo_envio == "cancelamento")
            {

                //file_put_contents("/var/www/html/eduardo/retorno_cancelamento1.txt", $xmlRetorno);

                if(strpos($xmlRetorno,"RetCancelamento") === false){
                }else{
                    $xml = explode("RetCancelamento", $xmlRetorno);
                    $xmlRetorno = "<RetCancelamento".$xml[1]."RetCancelamento>";
                }

                //file_put_contents("/var/www/html/eduardo/retorno_cancelamento2.txt", $xmlRetorno);

                $doc = new DOMDocument('1.0', 'utf-8'); 
                $doc->formatOutput = false;
                $doc->preserveWhiteSpace = false;
                $doc->loadXML($xmlRetorno, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

                $numero_nf = $doc->getElementsByTagName("Numero")->item(0)->nodeValue;
                $status = "";
                $mensagem = "";

                if($numero_nf != "")
                {
                    $status = "S";
                    $mensagem = "Sucesso";
                }
                else
                {
                    $status = "N";
                    $mensagem = "Nao houve retorno da prefeitura!";
                }

                $conteudo_arquivo_retorno = "";
                $conteudo_arquivo_retorno .= trim($this->paramentros["cnpj"])."|";
                $conteudo_arquivo_retorno .= trim($this->paramentros["codigo_tom"])."|";
                $conteudo_arquivo_retorno .= trim(date("Ym"))."||";
                $conteudo_arquivo_retorno .= trim("")."|";
                $conteudo_arquivo_retorno .= trim($numero_nf)."|";
                $conteudo_arquivo_retorno .= trim($status)."|";
                $conteudo_arquivo_retorno .= trim($mensagem)."||||";

                file_put_contents("/user/nfse/".$this->paramentros["cnpj"]."/CaixaSaida/".str_replace(".xml", ".txt", $this->nome_retorno), $conteudo_arquivo_retorno);

            }
        }
    }
