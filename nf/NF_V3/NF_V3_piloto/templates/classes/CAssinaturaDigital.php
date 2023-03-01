<?php
/*
    Autor:                  Fernando H. Crozetta
    Data:                   21/03/2017
    Finalidade:             Responsavel por assinar digitalmente o XML
    Passar caminho+nome xml, cnpj para criar a classe
    Alterado da classe original de Guilherme Silva
*/
    require_once("../funcoes/flog.php");
    require_once("../funcoes/fdebug.php");
    class CAssinaturaDigital
    {
    // Dados entrada
        private $arquivo_xml;
        private $cnpj;
        private $xml;

    // Dados certificado
        private $raiz_dados;
        private $dados;
        private $arquivoPFX;
        private $priKey;
        private $pubKey;
        private $certKey;
        private $senhaPFX;
    // Dados Debug e Log
        private $mensagemErro;

    // Variaveis para uso das funcoes de assinatura
        private $validadeDia;
        private $validadeMes;
        private $validadeAno;
        private $transform = true;    

        function __construct($arquivo_xml,$cnpj)
        {
            $this->arquivo_xml = $arquivo_xml;
            $this->xml = file_get_contents($this->arquivo_xml);
            $this->cnpj = $cnpj;

        // Debug e log
            $this->mensagemErro = '';
        // Carregamento de dados do certificado
            $this->config = parse_ini_file("../config/config.ini");
            if (file_exists($this->config['dados']."/config_cliente/".$this->cnpj.".ini")) {
                $this->dados = parse_ini_file($this->config['dados']."/config_cliente/".$this->cnpj.".ini",true);
                $this->senhaPFX = $this->dados['certificado']['senha'];
                $this->caminho_certificado = $this->dados['certificado']['caminho_certificado'];
                $this->arquivoPFX = $this->caminho_certificado."/".$this->cnpj.".pfx";
                $this->priKey = $this->caminho_certificado."/".$this->cnpj."_priKey.pem";
                $this->pubKey =  $this->caminho_certificado."/".$this->cnpj."_pubKey.pem";
                $this->certKey = $this->caminho_certificado."/".$this->cnpj."_certKey.pem";
            }else{
                flog("Arquivo de configuracoes do cnpj nao existe. ".$cnpj);
                fdebug("Arquivo de configuracoes do cnpj nao existe.");
            }

        }
       
        // Retorna o conteudo do xml
        public function getXml()
        {
            return $this->xml;
        }

        // Retorna a mensagem de erro
        public function getErro()
        {
            return $this->mensagemErro;
        }

        // salva o conteudo do xml, apos as alteracoes
        public function salvar($destino='')
        {
            // Define o arquivo de saida
            if ($destino == '') {
                $saida = $this->arquivo_xml;
            }else{
                $saida = $destino;
            }
            // Grava os dados no arquivo
            file_put_contents($saida, $this->xml);
        }

        // Funcao que assina o xml, ao serem passados 2 parametros.
        // Esta funcao não teve lógica alterada. Por este motivo, sua documentação pode não estar completa e/ou atualizada
        public function assinarXml($tag_1,$tag_2='')
        {
            // Retorna false se houver algum erro com os certificados
            if(!$this->loadCerts()){
                fdebug("erro loadCerts");
                flog("erro loadCerts");
                return false;
            }

            if ( $tag_1 == '' ){
                $mensagemErro = "Uma tag deve ser indicada para que seja assinada!!\n";
                flog("CAssinaturaDigital.php -> signXML(".$tag_1.",".$tag_2.")\n\t\t{Uma tag deve ser indicada para que seja assinada}");
                fdebug("CAssinaturaDigital.php -> signXML(".$tag_1.",".$tag_2.")\n\t\t{Uma tag deve ser indicada para que seja assinada}");
                $errStatus = true;
                return false;
            }
            
            // obter o chave privada para a ssinatura
            $fp = fopen($this->priKey, "r");
            $priv_key = fread($fp, filesize($this->priKey));
            fclose($fp);
            $pkeyid = openssl_get_privatekey($priv_key);
            
            // limpeza do xml com a retirada dos CR, LF e TAB
            $order = array("\r\n", "\n", "\r", "\t");
            $replace = '';
            $this->xml = str_replace($order, $replace, $this->xml);
            // carrega o documento no DOM
            $xmldoc = new DOMDocument();
            $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
            $xmldoc->formatOutput = false;
            // muito importante deixar ativadas as opçoes para limpar os espacos em branco
            // e as tags vazias
            $xmldoc->loadXML($this->xml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $root = $xmldoc->documentElement;
            //extrair a tag com os dados a serem assinados
            $node = $xmldoc->getElementsByTagName($tag_1)->item(0);
            $id = trim($node->getAttribute("Id"));
            $idnome = preg_replace('/[^0-9]/','', $id);
            //extrai os dados da tag para uma string
            $dados = $node->C14N(false,false,NULL,NULL);
            //calcular o hash dos dados
            $hashValue = hash('sha1',$dados,true);
            //converte o valor para base64 para serem colocados no xml
            $digValue = base64_encode($hashValue);

            //monta a tag da assinatura digital
            $Signature = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#','Signature');
            if($tag_2 == ""){
                $root->appendChild($Signature);
            }else{
                $tag = $xmldoc->getElementsByTagName($tag_2)->item(0);
                $tag->appendChild($Signature);
            }
            $Signature->setAttribute('xmlns', 'http://www.w3.org/2000/09/xmldsig#');

            $SignedInfo = $xmldoc->createElement('SignedInfo');
            $Signature->appendChild($SignedInfo);
            //Cannocalization
            $newNode = $xmldoc->createElement('CanonicalizationMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            //SignatureMethod
            $newNode = $xmldoc->createElement('SignatureMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            //Reference
            $Reference = $xmldoc->createElement('Reference');
            $SignedInfo->appendChild($Reference);
            $Reference->setAttribute('URI', '#'.$id);
            // $Reference->setAttribute('URI', $id);
            if($this->transform == true){
            //Transforms
                $Transforms = $xmldoc->createElement('Transforms');
                $Reference->appendChild($Transforms);
            //Transform
                $newNode = $xmldoc->createElement('Transform');
                $Transforms->appendChild($newNode);
                $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            //Transform
                $newNode = $xmldoc->createElement('Transform');
                $Transforms->appendChild($newNode);
                $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            }
            //DigestMethod
            $newNode = $xmldoc->createElement('DigestMethod');
            $Reference->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            //DigestValue
            $newNode = $xmldoc->createElement('DigestValue',$digValue);
            $Reference->appendChild($newNode);
            // extrai os dados a serem assinados para uma string
            $dados = $SignedInfo->C14N(false,false,NULL,NULL);
            //inicializa a variavel que irá receber a assinatura
            $signature = '';
            //executa a assinatura digital usando o resource da chave privada
            $resp = openssl_sign($dados,$signature,$pkeyid);
            //codifica assinatura para o padrao base64
            $signatureValue = base64_encode($signature);
            //SignatureValue
            $newNode = $xmldoc->createElement('SignatureValue',$signatureValue);
            $Signature->appendChild($newNode);
            //KeyInfo
            $KeyInfo = $xmldoc->createElement('KeyInfo');
            $Signature->appendChild($KeyInfo);
            //X509Data
            $X509Data = $xmldoc->createElement('X509Data');
            $KeyInfo->appendChild($X509Data);
            //carrega o certificado sem as tags de inicio e fim
            $cert = $this->cleanCerts($this->pubKey);
            //X509Certificate
            $newNode = $xmldoc->createElement('X509Certificate',$cert); // 
            $X509Data->appendChild($newNode);
            //grava na string o objeto DOM
            $this->xml = $xmldoc->saveXML();
            // libera a memoria
            openssl_free_key($pkeyid);
            //retorna o documento assinado
            $this->xml = str_replace('<?xml version="1.0"?>', '', $this->xml);
            $this->xml = str_replace('<ds:', '<', $this->xml); // gjps
            $this->xml = str_replace('</ds:', '</', $this->xml); // gjps
            $xml = $this->xml;
            return true;
        }

        /*
        ** Metodo para carregr os certificados
        */
        private function loadCerts(){

            //verifica se o arquivo existe
            if(!file_exists($this->arquivoPFX)){

                $mensagemErro = "CAssinaturaDigital->loadCerts{ certificado digital nao foi instalado corretamente, favor instale novamente }";
                flog("Certificado Digital nao foi instalado corretamente, favor instale novamente");
                fdebug("Certificado Digital nao foi instalado corretamente, favor instale novamente");
                return false;
            }

            //carrega o certificado em um string
            $key = file_get_contents($this->arquivoPFX);

            //carrega os certificados e chaves para um array denominado $x509certdata
            if (!openssl_pkcs12_read($key,$x509certdata,$this->senhaPFX)){
                $mensagemErro = "CAssinaturaDigital->loadCerts{ Certificado digital nao pode ser lido, verifique se a senha está correta }";
                flog("Certificado digital nao pode ser lido, verifique se a senha está correta");
                fdebug("Certificado digital nao pode ser lido, verifique se a senha está correta");
                return false;
            }
            //verifica sua validade
            if(!$this->validaCertificado($x509certdata['cert'])){
                return false;
            }
            //verifica se arquivo já existe
            if(file_exists($this->priKey)){
                //se existir verificar se é o mesmo
                $conteudo = file_get_contents($this->priKey);
                //comparar os primeiros 100 digitos
                if ( !substr($conteudo,0,100) == substr($x509certdata['pkey'],0,100) ) {
                     //se diferentes gravar o novo
                    if (!file_put_contents($this->priKey,$x509certdata['pkey']) ){
                        $mensagemErro = "CAssinaturaDigital->loadCerts{ Impossivel gravar certificado digital no diretório!!! Permissão negada 1 }";
                        flog("Impossivel gravar certificado digital no diretorio! Permissao Negada!");
                        fdebug("Impossivel gravar certificado digital no diretorio! Permissao Negada!");
                        return false;
                    }
                }
            } else {
                //salva a chave privada no formato pem para uso so SOAP
                if(!file_put_contents($this->priKey,$x509certdata['pkey'])){
                    $mensagemErro = "CAssinaturaDigital->loadCerts{ Impossivel gravar certificado digital no diretório, Permissão negada 2 } ";
                    flog("Impossivel gravar certificado digital no diretorio! Permissao Negada!");
                    fdebug("Impossivel gravar certificado digital no diretorio! Permissao Negada!");
                    return false;
                }
            }
            //verifica se arquivo com a chave publica já existe
            if(file_exists($this->pubKey)){
                //se existir verificar se é o mesmo atualmente instalado
                $conteudo = file_get_contents($this->pubKey);
                //comparar os primeiros 100 digitos
                if ( !substr($conteudo,0,100) == substr($x509certdata['cert'],0,100) ) {
                     //se diferentes gravar o novo
                    $n = file_put_contents($this->pubKey,$x509certdata['cert']);
                    //salva o certificado completo no formato pem
                    $n = file_put_contents($this->certKey,$x509certdata['pkey']."\r\n".$x509certdata['cert']);
                }
            } else {
                //se não existir salva a chave publica no formato pem para uso do SOAP
                $n = file_put_contents($this->pubKey,$x509certdata['cert']);
                //salva o certificado completo no formato pem
                $n = file_put_contents($this->certKey,$x509certdata['pkey']."\r\n".$x509certdata['cert']);
            }
            return true;
        } //fim __loadCerts

        /* 
        **Metodo para limpar os certificados
        */
        private function cleanCerts($certFile){
          //carregar a chave publica do arquivo pem
            $temp = file_get_contents($certFile);
          //inicializa variavel
            $data = '';
          //carrega o certificado em um array usando o LF como referencia
            $arCert = explode("\n", $temp);
            foreach ($arCert AS $curData) {
            //remove a tag de inicio e fim do certificado
                if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) != 0 && strncmp($curData, '-----END CERTIFICATE', 20) != 0 ) {
              //carrega o resultado numa string
                    $data .= trim($curData);
                }
            }
            return $data;
        }//fim __cleanCerts  


        /**
        * __validCerts
        * Validaçao do cerificado digital, além de indicar
        * a validade, este metodo carrega a propriedade
        * mesesToexpire da classe que indica o numero de
        * meses que faltam para expirar a validade do mesmo
        * esta informacao pode ser utilizada para a gestao dos
        * certificados de forma a garantir que sempre estejam validos
        *
        * @name __validCerts
        * @version  1.00
        * @package  NFePHP
        * @author Roberto L. Machado <linux.rlm at gmail dot com>
        * @param    string  $cert Certificado digital no formato pem
        * @return   array ['status'=>true,'meses'=>8,'dias'=>245]
        */
        function validaCertificado($cert){
            $flagOK = true;
            $errorMsg = "";
            $data = openssl_x509_read($cert);
            $cert_data = openssl_x509_parse($data);
            // reformata a data de validade;
            $this->validadeAno = $ano = substr($cert_data['validTo'],0,2);
            $this->validadeMes = $mes = substr($cert_data['validTo'],2,2);
            $this->validadeDia = $dia = substr($cert_data['validTo'],4,2);
            //obtem o timeestamp da data de validade do certificado
            $dValid = gmmktime(0,0,0,$mes,$dia,$ano);
            // obtem o timestamp da data de hoje
            $dHoje = gmmktime(0,0,0,date("m"),date("d"),date("Y"));
            // compara a data de validade com a data atual
            if ($dValid < $dHoje ){
                $mensagemErro = "CAssinaturaDigital->validaCertificado{ A Validade do certificado expirou em [".$dia.'/'.$mes.'/'.$ano."] }";
                flog("A Validade do certificado expirou em [ ".$dia.'/'.$mes.'/'.$ano." ]");
                fdebug("A Validade do certificado expirou em [ ".$dia.'/'.$mes.'/'.$ano." ]");
                return false;
            } else {
                return true;
            }
        } //fim __validCerts
}




