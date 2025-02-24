<?php
	/*
		Classe:					CAssinaturaDigital.php
		Autor:					Guilherme Silva
		Data:					06/03/2012
		Finalidade: 			Responsavel por assinar digitalmente o XML
		Programas chamadores: 	
		Programas chamados: 	BD{lote}
	*/
	require_once("/var/www/html/nf/nfse/control/CConfig.php");
	
	class CAssinaturaDigital{
		/* Atribuitos  publicos */
		public $xml;
		public $mensagemErro;
		/* Atribuitos  privados */
		private $priKey;
		private $pubKey;
		private $certKey;
		public $cnpj;
		
		public $validadeDia;
		public $validadeMes;
		public $validadeAno;
		public $validadeHor;
		public $validadeMin;
		public $validadeSeg;

		public $arquivoPFX;
		public $senhaPFX;
		public $transform=true;

		/* Metodos publicos */
		public function assinarXml($tagAssinar, $tagNo='', $pCNPJ){
		  $this->cnpj = $pCNPJ;
		  $this->senhaPFX = $this->getkeyPassCertificadoDigital($pCNPJ);

		  if($this->signXML($this->xml, $tagAssinar, $tagNo)){
			return true;
		  }else{
			return false;
		  }
		}

		/**
	     * ABAIXO FUNÇÕES CRIADAS CRIADAS ESPECIFICAMENTE PARA FUNCIONAR COM O SISTEMA NFE SOFTDIB
	     */

		/**
	     * __getkeyPassCertificadoDigital
	     * Obter senha do certificado digital de um cnpj específico
	     *  
	     * @name __setConfigurations ($AN)
	     * @param   Ambiente Nacional
	     * @autor Eduardo Nunes Lino
	     * @return  none
	     */
	    public function getkeyPassCertificadoDigital($pCnpj){

	
			// Abrir o config para obter a senha do certificado
			$arquivoIni = parse_ini_file("/var/www/html/nf/nfe/config/config.ini");
			if(!$arquivoIni){
				echo "Erro ao abrir o arquivo config.ini";
			}
			
			

	        return $arquivoIni[trim($pCnpj)];
	    }

		public function getPriKey(){
			return $this->priKey;
		}
		public function getPubKey(){
			return $this->pubKey;
		}
		/* Metodos privados */ 
		
		  /**
		 * autoSignNFe
		 * Método para assinatura em lote das NFe em XML
		 * Este método verifica todas as NFe existentes na pasta de ENTRADAS e as assina
		 * após a assinatura ser feita com sucesso o arquivo XML assinado é movido para a pasta
		 * ASSINADAS.
		 * IMPORTANTE : Em ambiente Linux manter os nomes dos arquivos e terminações em LowerCase.
		 *
		 * @version 2.11
		 * @package NFePHP
		 * @author Roberto L. Machado <linux.rlm at gmail dot com>
		 * @param  none
		 * @return boolean true sucesso false Erro
		 */
		 private function signXML($docxml, $tagid='', $adicionarNaTag=''){
			  if(!$this->loadCerts()){
				  return false;
			  }
	              if ( $tagid == '' ){
					  $this->errMsg = "Uma tag deve ser indicada para que seja assinada!!\n";
					  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> signXML(".$docxml.",".$tagid.",".$adicionarNaTag.") {Uma tag deve ser indicada para que seja assinada} \n\n ", FILE_APPEND);
					  $this->errStatus = true;
					  return false;
				  }
				  if ( $docxml == '' ){
					  $this->mensagemErro = "CAssinaturaDigital->signXML{ Envio de parametro nao opcional }";
					  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> signXML(".$docxml.",".$tagid.",".$adicionarNaTag.") {Envio de parametro nao opcional} \n\n ", FILE_APPEND);
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
				  $docxml = str_replace($order, $replace, $docxml);
				  // carrega o documento no DOM
				  $xmldoc = new DOMDocument();
				  $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
				  $xmldoc->formatOutput = false;
				  // muito importante deixar ativadas as opçoes para limpar os espacos em branco
				  // e as tags vazias
				  $xmldoc->loadXML($docxml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
				  $root = $xmldoc->documentElement;
				  //extrair a tag com os dados a serem assinados
				  $node = $xmldoc->getElementsByTagName($tagid)->item(0);
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
				  if($adicionarNaTag == ""){
					$root->appendChild($Signature);
				  }else{
					$tag = $xmldoc->getElementsByTagName($adicionarNaTag)->item(0);
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
				  $docxml = $xmldoc->saveXML();
				  // libera a memoria
				  openssl_free_key($pkeyid);
				  //retorna o documento assinado
				  $docxml = str_replace('<?xml version="1.0"?>', '', $docxml);
				  $docxml = str_replace('<ds:', '<', $docxml); // gjps
				  $docxml = str_replace('</ds:', '</', $docxml); // gjps
				  $this->xml = $docxml;
				  return true;
		  } //fim signXML
		  
	/*
	** Metodo para carregr os certificados
	*/
		public function loadCerts(){
			//monta o path completo com o nome da chave privada
			/*$CConfig = new CConfig();
			$CConfig->lerArquivo($this->cnpj);*/
			//$arquivo = file_get_contents();

			//$this->arquivoPFX = "/var/www/html/nf/nfse/certificados/07817840000190.pfx";



			$caminhoCertificados = str_replace(".pfx", "", $this->arquivoPFX);
			
			$this->priKey = $caminhoCertificados."_priKey.pem";
			//monta o path completo com o nome da chave
			$this->pubKey =  $caminhoCertificados."_pubKey.pem";
			//monta o path completo com o nome do certificado (chave publica e privada) em formato pem
			$this->certKey = $caminhoCertificados."_certKey.pem";
			//verificar se o nome do certificado e
			//o path foram carregados nas variaveis da classe
			
			//monta o caminho completo até o certificado pfx
			$pCert = $this->arquivoPFX;

			


			//verifica se o arquivo existe
			if(!file_exists($pCert)){

			  $this->mensagemErro = "CAssinaturaDigital->loadCerts{ certificado digital nao foi instalado corretamente, favor instale novamente }";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> loadCerts() { Certificado Digital nao foi instalado corretamente, favor instale novamente} \n\n ", FILE_APPEND);
			  return false;
			}


			//carrega o certificado em um string
			$key = file_get_contents($pCert);
			//carrega os certificados e chaves para um array denominado $x509certdata

			


			if (!openssl_pkcs12_read($key,$x509certdata,$this->senhaPFX)){
			  $this->mensagemErro = "CAssinaturaDigital->loadCerts{ Certificado digital nao pode ser lido, verifique se a senha está correta }";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> loadCerts() { Certificado digital nao pode ser lido, verifique se a senha está correta } \n\n ", FILE_APPEND);
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
					  $this->mensagemErro = "CAssinaturaDigital->loadCerts{ Impossivel gravar certificado digital no diretório!!! Permissão negada 1 }";
					  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> loadCerts() { Impossivel gravar certificado digital no diretorio! Permissao Negada! } \n\n ", FILE_APPEND);
					  return false;
					}
				}
			} else {
				//salva a chave privada no formato pem para uso so SOAP
				if(!file_put_contents($this->priKey,$x509certdata['pkey'])){
				  $this->mensagemErro = "CAssinaturaDigital->loadCerts{ Impossivel gravar certificado digital no diretório, Permissão negada 2 } ";
				  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> loadCerts() { Impossivel gravar certificado digital no diretorio! Permissao Negada! } \n\n ", FILE_APPEND);
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
		  $pubKey = file_get_contents($certFile);
		  //inicializa variavel
		  $data = '';
		  //carrega o certificado em um array usando o LF como referencia
		  $arCert = explode("\n", $pubKey);
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
		* @return	array ['status'=>true,'meses'=>8,'dias'=>245]
		*/
		public function validaCertificado($cert){
			
			$flagOK = true;
			$errorMsg = "";
			$data = openssl_x509_read($cert);
			$cert_data = openssl_x509_parse($data);
			// reformata a data de validade;
			$this->validadeAno = $ano = substr($cert_data['validTo'],0,2);
			$this->validadeMes = $mes = substr($cert_data['validTo'],2,2);
			$this->validadeDia = $dia = substr($cert_data['validTo'],4,2);
            $this->validadeHor = $hor = substr($cert_data['validTo'],6,2);
            $this->validadeMin = $min = substr($cert_data['validTo'],8,2);
            $this->validadeSeg = $seg = substr($cert_data['validTo'],10,2);
			
			$this->validadeReal = date("d/m/Y H:i:s",$cert_data['validTo_time_t']) ;
			
			//obtem o timeestamp da data de validade do certificado
			$dValid = gmmktime(0,0,0,$mes,$dia,$ano);
			$hValid = $hor . $min . $seg;

			// obtem o timestamp da data de hoje
			$dHoje = gmmktime(0,0,0,date("m"),date("d"),date("Y"));
			$hHoje = date("His");

			// compara a data de validade com a data atual
			if ($dValid < $dHoje ){
			  $this->mensagemErro = "CAssinaturaDigital->validaCertificado{ A Validade do certificado expirou em ["  . $dia.'/'.$mes.'/'.$ano . ' - ' . $hor .':' . $min . ':' . $seg . "] }";
			  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> validaCertificado(".$cert.") { A Validade do certificado expirou em ["  . $dia.'/'.$mes.'/'.$ano . ' - ' . $hor .':' . $min . ':' . $seg ."] } \n\n ", FILE_APPEND);
			  return false;
			} else {
     			if (($dValid == $dHoje) && ($hValid <= $hHoje) ){
					  $this->mensagemErro = "CAssinaturaDigital->validaCertificado{ A Validade do certificado expirou em ["  . $dia.'/'.$mes.'/'.$ano . ' - ' . $hor .':' . $min . ':' . $seg . " hora_atu: " . $hHoje . "] }";
					  file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n CAssinaturaDigital.php -> validaCertificado(".$cert.") { A Validade do certificado expirou em ["  . $dia.'/'.$mes.'/'.$ano . ' - ' . $hor .':' . $min . ':' . $seg ."] } \n\n ", FILE_APPEND);
					  return false;
				}else{
					return true;
				}
			}
		} //fim __validCerts

	}

?>