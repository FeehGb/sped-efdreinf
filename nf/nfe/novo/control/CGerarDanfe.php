<?php
/**
 * @name      	CGerarDanfe
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para gerar a DANFE a partir do XML informado
 * @TODO 		Colocar mascaras nos campos que irao para o ireport (ex CNPJ)
*/

/**
 * Classe CGerarDanfe
 */
//error_reporting(0); // N�O MOSTRAR ERROS NO PHP
include("../libs/xmlgen.php");
require_once("../model/MContribuinte.php");
require_once("../model/MNotaFiscal.php");
 
class CGerarDanfe{
	// Mensagem de Erro
	public $mensagemErro='';
	// XML completa da NFE
	public $xmlNfe='';
	// Informacoes Aidicionais
	public $infAdic='';
	// Identificacao Nota com ISUF (Zona Franca de Manaus)
	private $zonaFranca=false;
	// Caminho da Danfe Gerada
	public $danfePdf='';
	// Chave da NFE
	private $chaveNFE;
	// Cnpj contribuinte
	private $pathLayout;
	// XML de parametro para chamada do JUGIRXML
	private $xmlJUGIRXML='';
	// Orientacao R (retrato) P (paisagem)
	private $orientacao="R";
	
	private $grupo;
	
	private $mascaraMilhar=false;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
		//Utiliza Mascara de milhar para a Eckisil
		switch(strtolower($pGrupo)){
			case 'eckisil':
			case 'tratoraco':
			case 'x_import':
			case 'cia_x':
			case 'songhe':
			case 'orion':
			case 'berlanda':
			case 'natuphitus':
			case 'leogap':
                        case 'vianmaq':
                        case 'tomasoni':
				$this->mascaraMilhar=true;
			break;
		}
	}

	// M�todo publico responsavel por gerar a DANFE
	public function gerarDanfe(){
		// Verificar se atributo foi setado corretamente pelo programa chamador
		if(empty($this->xmlNfe)){
			$this->mensagemErro = "XML nao definido, chamada de classe incorreta"; return false;
		}
		
		// Chamar para gerar XML de chamada ao JUGIRXML
		if(!$this->gerarXmlJUGIRXML()){ return false; }

		// Gravar em arquivo temporario no /user/relatorios/ apenas para chamar JUGIRXML
		$this->xmlJUGIRXML = utf8_encode($this->xmlJUGIRXML);
		if(!file_put_contents("/var/www/html/relatorios/NFE_".$this->chaveNFE.".xml",$this->xmlJUGIRXML)){
			$this->mensagemErro = "Impossivel criar arquivo temporario"; return false;
		}
		// Chamar JUIRXML para gerar relatorio
		
		@system("cd /var/www/html/relatorios/; rm -rf /var/www/html/relatorios/NFE_".$this->chaveNFE."*.pdf");
		
		if(system("cd /var/www/html/relatorios/; php -q /user/objetos/JUGIRXML.php /var/www/html/relatorios/NFE_".$this->chaveNFE.".xml ".$this->pathLayout." /var/www/html/relatorios/NFE_".$this->chaveNFE.".pdf") != ""){
			$this->mensagemErro = "Nao foi possivel criar o PDF da DANFE";
			return false;
		}
		@system("chmod 777 /var/www/html/relatorios/NFE_".$this->chaveNFE.".pdf");
		@system("chmod 777 /var/www/html/relatorios/NFE_".$this->chaveNFE.".xml");
		
		// Remove o XML temporario do /user/relatorio que fora criado apenas para converter em PDF
		/*if(!unlink("/var/www/html/relatorios/NFE_".$this->chaveNFE.".xml")){
			$this->mensagemErro = "Nao foi possivel remover o XML temporario";
		}*/
		
		$this->danfePdf = "NFE_".$this->chaveNFE.".pdf";
		return true;
	}
	
	// M�todo publico responsavel por gerar a DANFE Automaticamente quando chamada pelo programa do CRONTAB
	public function gerarDanfeAutomatica(){
		// Seleciona as Notas Fiscais pendentes de emiss�o autom�tica da DANFE
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$sql = "SELECT 	cnpj_emitente,
						numero_nota,
						serie_nota,
						ambiente,
						cod_empresa_filial_softdib,
						nome_emissor,
						cnpj_destinatario,
						nome_destinatario,
						cod_destinatario,
						status,
						tipo_emissao,
						data_emissao,
						uf_webservice,
						layout_danfe,
						valor_total_nfe,
						data_entrada_saida,
						chave,
						numero_protocolo,
						tipo_operacao,
						xml,
						danfe_impressa,
						email_enviado,
						lote_nfe
						FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` WHERE danfe_impressa = 'N' AND status = '03' LIMIT 0,10";

		$notasFiscais = $MNotaFiscal->selectAllMestre($sql);
		$cont=0;

		while(@is_array($notasFiscais[$cont])){
			$MContribuinte = new MContribuinte($this->grupo);
			$MContribuinte->cnpj		= $notasFiscais[$cont]['cnpj_emitente'];
			$MContribuinte->ambiente	= $notasFiscais[$cont]['ambiente'];
			$rContribuinte = $MContribuinte->selectCNPJAmbiente();
			
			if(!$rContribuinte){
				$this->mensagemErro = $MContribuinte->mensagemErro;
				return false;
			}

			// Verifica se o Contribuinte � requer impress�o da Danfe Autom�tica, no teste abaixo ele n�o requer, e continua para pr�xima NF
			if($rContribuinte[0]['danfe_automatica'] != "1"){
				$cont++;
				continue;
			}

			// Verifica se o contribuinte est� em contig�ncia FS (02) ou FS-da (05), caso sim desconsidera e l� a pr�xima nota
			if($rContribuinte[0]['contigencia'] == "02" || $rContribuinte[0]['contigencia'] == "05"){
				$cont++;
				continue;
			}
			// Verificar se a a Danfe Autom�tica de Nota est� com status "N" (n�o foi impressa)
			if($notasFiscais[$cont]['danfe_impressa'] == "N"){
				// Verificar se a NFe est� autorizada o uso (status = 03)
				if($notasFiscais[$cont]['status'] == "03"){
					// Gerar a Danfe
					$this->xmlNfe = $notasFiscais[$cont]['xml'];
					$this->infAdic = $notasFiscais[$cont]['observacao'];
					if(!$this->gerarDanfe()){
						return false;
					}
					
					// Impress�o Autom�tica da DANFE
					$qtdeVias = $rContribuinte[0]['danfe_qtde_vias'];
					// Caso for da Zona Franca de Manaus imprimir +1 via
					if($this->zonaFranca == true){
						$qtdeVias++;
					}
					for($qtde=1;$qtde <= $qtdeVias;$qtde++){
						$this->impressaoAutomatica($rContribuinte[0]['server_impressao']);
					}
					
					// Apagar os PDF dos relatorios para nao dar problema na visualizacao da Danfe
					@system("cd /var/www/html/relatorios/; rm -rf /var/www/html/relatorios/NFE_".$notasFiscais[$cont]['chave']."*.pdf");
					
					// Update da NFe, campo danfe_impressa = "S"
					$NfeUpdate = new MNotaFiscal($this->grupo);
					$NfeUpdate->cnpj_emitente	= $notasFiscais[$cont]['cnpj_emitente'];
					$NfeUpdate->ambiente		= $notasFiscais[$cont]['ambiente'];
					$NfeUpdate->numero_nota		= $notasFiscais[$cont]['numero_nota'];
					$NfeUpdate->serie_nota		= $notasFiscais[$cont]['serie_nota'];
					$NfeUpdate->danfe_impressa 	= "S";
					if(!$NfeUpdate->update()){
						$this->mensagemErro = $NfeUpdate->mensagemErro;
						return false;
					}
				// Danfe n�o h� autoriza��o de Uso pela SEFAZ (status != 03)
				}else{
					// Danfe com autoriza��o DPEC (status = 04)
					if($notasFiscais[$cont]['tipo_emissao'] == "4"){
						if($notasFiscais[$cont]['lote_nfe'] != null){
									//
									// IMPRESS�O DA NOTA FISCAL EM DPEC
									//
									
							// Update da NFe, campo danfe_impressa = "S"
							$NfeUpdate = new MNotaFiscal($this->grupo);
							$NfeUpdate->cnpj_emitente	= $notasFiscais[$cont]['cnpj_emitente'];
							$NfeUpdate->ambiente		= $notasFiscais[$cont]['ambiente'];
							$NfeUpdate->numero_nota		= $notasFiscais[$cont]['numero_nota'];
							$NfeUpdate->serie_nota		= $notasFiscais[$cont]['serie_nota'];
							$NfeUpdate->danfe_impressa 	= "C";
							if(!$NfeUpdate->update()){
								$this->mensagemErro = $NfeUpdate->mensagemErro;
								return false;
							}
						}else{
							$cont++;
							continue;
						}
					}else{
						$cont++;
						continue;
					}
				}
			}else{
				if($notasFiscais[$cont]['status'] == "03" && $notasFiscais[$cont]['danfe_impressa'] == "C"){
					// Gerar Danfe no diretorio padrao
					$this->xmlNfe = $notasFiscais[$cont]['xml'];
					$this->infAdic = $notasFiscais[$cont]['observacao'];
					if(!$this->gerarDanfe()){
						return false;
					}
					
					// Impress�o Autom�tica da DANFE
					for($qtde=1;$qtde <= $rContribuinte[0]['danfe_qtde_vias'];$qtde++){
						$this->impressaoAutomatica($rContribuinte[0]['server_impressao']);
					}
					
					// Apagar os PDF dos relatorios para nao dar problema na visualizacao da Danfe
					@system("cd /var/www/html/relatorios/; rm -rf /var/www/html/relatorios/NFE_".$notasFiscais[$cont]['chave']."*.pdf");
					
					// Update da NFe, campo danfe_impressa = "S"
					$NfeUpdate = new MNotaFiscal($this->grupo);
					$NfeUpdate->cnpj_emitente	= $notasFiscais[$cont]['cnpj_emitente'];
					$NfeUpdate->ambiente		= $notasFiscais[$cont]['ambiente'];
					$NfeUpdate->numero_nota		= $notasFiscais[$cont]['numero_nota'];
					$NfeUpdate->serie_nota		= $notasFiscais[$cont]['serie_nota'];
					$NfeUpdate->danfe_impressa 	= "S";
					if(!$NfeUpdate->update()){
						$this->mensagemErro = $NfeUpdate->mensagemErro;
						return false;
					}
				}else{ $cont++; continue; }
			}
		$cont++;
		}
	}
	
	// Metodo privado para gerar XML de comunicacao com JUGIRXML a partir do XML da NFE
	private function gerarXmlJUGIRXML(){
		// Instanciar o XML completo da NFE
		$dom = new DOMDocument('1.0', 'utf-8');
        $dom->preservWhiteSpace	= false; //elimina espa�os em branco
        $dom->formatOutput 		= false;
        $dom->loadXML(base64_decode($this->xmlNfe));

		// Variavel XML Local de comunicacao com o JURIXML
		$arrayIRXML = "";
		$nfeProc    = $dom->getElementsByTagName("nfeProc")->item(0);
        $infNFe     = $dom->getElementsByTagName("infNFe")->item(0);
        $ide        = $dom->getElementsByTagName("ide")->item(0);
        $entrega    = $dom->getElementsByTagName("entrega")->item(0);
        $retirada   = $dom->getElementsByTagName("retirada")->item(0);
        $emit       = $dom->getElementsByTagName("emit")->item(0);
        $dest       = $dom->getElementsByTagName("dest")->item(0);
        $enderEmit  = $dom->getElementsByTagName("enderEmit")->item(0);
        $enderDest  = $dom->getElementsByTagName("enderDest")->item(0);
        $det        = $dom->getElementsByTagName("det");
        $cobr       = $dom->getElementsByTagName("cobr")->item(0);
        $dup        = $dom->getElementsByTagName('dup');
        $ICMSTot    = $dom->getElementsByTagName("ICMSTot")->item(0);
        $ISSQNtot   = $dom->getElementsByTagName("ISSQNtot")->item(0);
        $transp     = $dom->getElementsByTagName("transp")->item(0);
        $transporta = $dom->getElementsByTagName("transporta")->item(0);
        $veicTransp = $dom->getElementsByTagName("veicTransp")->item(0);
		$reboque    = $dom->getElementsByTagName("reboque")->item(0);
		$vol   		= $dom->getElementsByTagName("vol")->item(0);
        $infAdic    = $dom->getElementsByTagName("infAdic")->item(0);
			$arrayInfAdic = str_split($infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue,80);
			$contInfAdic = ceil(count($arrayInfAdic)/13);
			$indiceDadosAdicionais=0;
		$obscont    = $infAdic->getElementsByTagName("obsCont");
			foreach($obscont as $item => $conteudo){
				$arrayTemp[] = $infAdic->getElementsByTagName("obsCont")->item($item)->getAttribute("xCampo")." ".$infAdic->getElementsByTagName("obsCont")->item($item)->nodeValue;
			}
			if(is_array($arrayTemp)){
				$arrayInfAdic = array_merge($arrayInfAdic, $arrayTemp);
			}

        $compra     = $dom->getElementsByTagName("compra")->item(0);
        $tpEmis     = $ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
        $tpImp      = $ide->getElementsByTagName("tpImp")->item(0)->nodeValue;
        $infProt    = $dom->getElementsByTagName("infProt")->item(0);
		if(!isset($infProt)){
			$this->mensagemErro = "Problemas de integridade do XML. (".$ide->getElementsByTagName("nNF")->item(0)->nodeValue.")\nClique com o botao direito sobre a nota e atualize a mesma para corrigir!";
			return false;
		}
		
		$ISUF = $dest->getElementsByTagName("ISUF")->item(0);
		if(isset($ISUF)){
			$this->zonaFranca = true;
		}else{ 
			$this->zonaFranca = false; 
		}

	// Variavel de Sequencia dos records
		$seq = 0;
	// Montar cabecalho do JUGIRXML
		$arrayIRXML['header'] = " ";
	// Montar dados do JUGIRXML
		$arrayIRXML['detail'][$seq]['record']['det1-recebemos'] = "RECEBEMOS DE ".$emit->getElementsByTagName("xNome")->item(0)->nodeValue." OS PRODUTOS E/OU SERVICOS CONSTANTES DA NOTA FISCAL INDICADA AO LADO.";
		$arrayIRXML['detail'][$seq]['record']['det1-nNF'] 		= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-serie'] 	= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-destinatario'] 	= $dest->getElementsByTagName("xNome")->item(0)->nodeValue;		
		$seqDet1=$seq;
		
	// Adiciona sequencia de record
		$seq++;
		
		// Obtem a logomarca do cadastro do usuario
		$CContribuinte = new CContribuinte($this->grupo);
		$CContribuinte->cnpj = $emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
		$retorno = $CContribuinte->mObterContribuinte();
		if(trim($retorno[0]['danfe_logo_caminho']) != ""){
			$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = trim($retorno[0]['danfe_logo_caminho']);
		}else{
			$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = "SOFTDIB.jpg";
		}
		
		if(trim($retorno[0]['danfe_layout_caminho']) != ""){
			$this->pathLayout = trim($retorno[0]['danfe_layout_caminho']);
		}else{
			$this->pathLayout = "/user/imagens/xml/NF-RH-MODELO.jrxml";
		}

		// Verifica Orientacao (retrato/paisagem)
		$this->verificaOrientacao();

		$arrayIRXML['detail'][$seq]['record']['det2-emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xLgr-nro'] = $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xCpl'] = !empty($enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue) ? $enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue : '';		
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue."/".$enderEmit->getElementsByTagName("UF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-CEP'] 		= "CEP: ".$this->mask($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
		$arrayIRXML['detail'][$seq]['record']['det2-emit-fone']		= "Fone: ".ltrim($this->mask($enderEmit->getElementsByTagName("fone")->item(0)->nodeValue, "(##) ####-#####"),0);
		$arrayIRXML['detail'][$seq]['record']['det2-tpEmis'] 		= $ide->getElementsByTagName("tpNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		$this->chaveNFE = str_replace('NFe', '', $infNFe->getAttribute("Id"));
		$arrayIRXML['detail'][$seq]['record']['det2-id'] 			= implode(" ",str_split($this->chaveNFE,4));
		$arrayIRXML['detail'][$seq]['record']['det2-codbar'] 		= $this->chaveNFE;
		$arrayIRXML['detail'][$seq]['record']['det2-natOp'] 		= $ide->getElementsByTagName("natOp")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-protNFe'] 		= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".date("d/m/Y H:i:s", strtotime($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue));
		$arrayIRXML['detail'][$seq]['record']['det2-IE'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
		if($emit->getElementsByTagName("IEST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det2-IEST'] = $emit->getElementsByTagName("IEST")->item(0)->nodeValue;}
		$arrayIRXML['detail'][$seq]['record']['det2-emit-CNPJ'] 	= $this->mask($emit->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
	// Adiciona sequencia de record
		$seq++;

		$arrayIRXML['detail'][$seq]['record']['det3-dest-xNome'] 	= $dest->getElementsByTagName("xNome")->item(0)->nodeValue;
		
		if(@$dest->getElementsByTagName("CNPJ")->item(0)->nodeValue != "" && $dest->getElementsByTagName("CNPJ")->item(0)->nodeValue != null){
			$arrayIRXML['detail'][$seq]['record']['det3-dest-CNPJ'] 	= $this->mask($dest->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
		}else{
			$arrayIRXML['detail'][$seq]['record']['det3-dest-CNPJ'] 	= $this->mask($dest->getElementsByTagName("CPF")->item(0)->nodeValue,"###.###.###-##");
		}
		
		$arrayIRXML['detail'][$seq]['record']['det3-dEmi'] 			= $ide->getElementsByTagName("dEmi")->item(0) ? date("d/m/Y", strtotime($ide->getElementsByTagName("dEmi")->item(0)->nodeValue)) : date("d/m/Y", strtotime($ide->getElementsByTagName("dhEmi")->item(0)->nodeValue));
		$arrayIRXML['detail'][$seq]['record']['det3-dest-xLgr']		= $enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderDest->getElementsByTagName("nro")->item(0)->nodeValue." ".@$enderDest->getElementsByTagName("xCpl")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-xBairro']	= $enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-CEP']		= $this->mask($enderDest->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");

		$arrayIRXML['detail'][$seq]['record']['det3-dSaiEnt']	= $ide->getElementsByTagName("dSaiEnt")->item(0) ? date("d/m/Y", strtotime($ide->getElementsByTagName("dSaiEnt")->item(0)->nodeValue)) : date("d/m/Y", strtotime($ide->getElementsByTagName("dhSaiEnt")->item(0)->nodeValue));
		$arrayIRXML['detail'][$seq]['record']['det3-hSaiEnt']	= $ide->getElementsByTagName("hSaiEnt")->item(0) ? $ide->getElementsByTagName("hSaiEnt")->item(0)->nodeValue : date("H:i:s", strtotime($ide->getElementsByTagName("dhSaiEnt")->item(0)->nodeValue));	
		
		if($arrayIRXML['detail'][$seq]['record']['det3-dSaiEnt'] == "31/12/1969"){
			$arrayIRXML['detail'][$seq]['record']['det3-dSaiEnt'] = "";
			$arrayIRXML['detail'][$seq]['record']['det3-hSaiEnt'] = "";
		}

		$arrayIRXML['detail'][$seq]['record']['det3-dest-cMun']		= $enderDest->getElementsByTagName("xMun")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-fone']		= ltrim($this->mask(ltrim(@$enderDest->getElementsByTagName("fone")->item(0)->nodeValue,0), "(##) ####-#####"),0);
		$arrayIRXML['detail'][$seq]['record']['det3-dest-UF']		= $enderDest->getElementsByTagName("UF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-IE']		= $dest->getElementsByTagName("IE")->item(0)->nodeValue;
		
	
	// Secao de Fatura / Duplicata
		$seqDup = 1;
		foreach( $dup as $indiceDuplicata => $itensDup ){
			$arrayIRXML['detail'][$seq]['record']['det3-dup-nro'.$seqDup] 	= $dup->item($indiceDuplicata)->getElementsByTagName('nDup')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det3-dup-data'.$seqDup]	= date("d/m/Y", strtotime($dup->item($indiceDuplicata)->getElementsByTagName('dVenc')->item(0)->nodeValue));
			if($this->mascaraMilhar){
				$arrayIRXML['detail'][$seq]['record']['det3-dup-valor'.$seqDup]	= number_format($dup->item($indiceDuplicata)->getElementsByTagName('vDup')->item(0)->nodeValue, 2, ',', '.');
			}else{
				$arrayIRXML['detail'][$seq]['record']['det3-dup-valor'.$seqDup]	= str_replace(".",",",$dup->item($indiceDuplicata)->getElementsByTagName('vDup')->item(0)->nodeValue);
			}
			$seqDup++;		}
	// Secao de Calculo de Impostos (Totalizacao do ICMS)
		if($ICMSTot){
			// Alterado de mascara apenas decimal para decimal e milhar
/*			if($ICMSTot->getElementsByTagName("vBC")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBC']			= str_replace(".",",",$ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vICMS")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vICMS']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vBCST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBCST']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vST']			= str_replace(".",",",$ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vIPI")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vIPI']			= str_replace(".",",",$ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vProd")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vProd']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vFrete")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vFrete']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vSeg")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vSeg']			= str_replace(".",",",$ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vDesc")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vDesc']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vOutro")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vOutro']		= str_replace(".",",",$ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vTotTrib")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vTotTrib']	= str_replace(".",",",$ICMSTot->getElementsByTagName("vTotTrib")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vNF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vNF']			= str_replace(".",",",$ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue);}
			if($ICMSTot->getElementsByTagName("vNF")->item(0)){$arrayIRXML['detail'][$seqDet1]['record']['det1-vNF'] 		= str_replace(".",",",$ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue);}
*/
			if($ICMSTot->getElementsByTagName("vBC")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBC']			= number_format($ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vICMS")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vICMS']		= number_format($ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vBCST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBCST']		= number_format($ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vST']			= number_format($ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vIPI")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vIPI']			= number_format($ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vProd")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vProd']		= number_format($ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vFrete")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vFrete']		= number_format($ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vSeg")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vSeg']			= number_format($ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vDesc")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vDesc']		= number_format($ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vOutro")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vOutro']		= number_format($ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vTotTrib")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vTotTrib']	= number_format($ICMSTot->getElementsByTagName("vTotTrib")->item(0)->nodeValue, 2, ',', '.');}
			if($ICMSTot->getElementsByTagName("vNF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vNF']			= number_format($ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue, 2, ',','.');}
			if($ICMSTot->getElementsByTagName("vNF")->item(0)){$arrayIRXML['detail'][$seqDet1]['record']['det1-vNF'] 		= number_format($ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue, 2, ',','.');}
		}
		
	// Secao de modalidade do frente
		if($transp){
			if($transp->getElementsByTagName("modFrete")->item(0)){
				switch(trim($transp->getElementsByTagName("modFrete")->item(0)->nodeValue)){
					case "0":
						$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete'] = "0 - Emitente";
					break;
					case "1":
						$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete'] = "1 - Destinatario";
						//$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete'] = "1 - Dest/Rem";
						// Alterado conforme ticket 28325 em desacordo com a norma do SEFAZ.
					break;
					case "2":
						$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete'] = "2 - Terceiros";
					break;
					case "9":
						$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete'] = "9 - Sem Frete";
					break;
				}
			}
		}

	// Secao de dados do veiculo do Transportador
		if($veicTransp){
			if($veicTransp->getElementsByTagName("RNTC")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-RNTC'] 	= $veicTransp->getElementsByTagName("RNTC")->item(0)->nodeValue;}
			if($veicTransp->getElementsByTagName("placa")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-placa']	= $veicTransp->getElementsByTagName("placa")->item(0)->nodeValue;}
			if($veicTransp->getElementsByTagName("UF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-UF'] 		= $veicTransp->getElementsByTagName("UF")->item(0)->nodeValue;}
		}
		
	// Secao de dados da transportadora
		if($transporta){
			if($transporta->getElementsByTagName("xNome")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-xNome']		= $transporta->getElementsByTagName("xNome")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("CNPJ")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-CNPJ']		= $this->mask($transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");}
			if($transporta->getElementsByTagName("xEnder")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-xEnder']	= $transporta->getElementsByTagName("xEnder")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("xMun")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-xMun']		= $transporta->getElementsByTagName("xMun")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("UF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-UF']			= $transporta->getElementsByTagName("UF")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("IE")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-IE']			= $transporta->getElementsByTagName("IE")->item(0)->nodeValue;}
		}
		
	// Secao de informacoes do Volume transportado
		if($vol){
			if($vol->getElementsByTagName("qVol")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-qVol']	= ltrim($vol->getElementsByTagName("qVol")->item(0)->nodeValue,0);}
			if($vol->getElementsByTagName("esp")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-esp']		= $vol->getElementsByTagName("esp")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("marca")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-marca']	= $vol->getElementsByTagName("marca")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("nVol")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-nVol']	= $vol->getElementsByTagName("nVol")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("pesoB")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-pesoB']	= str_replace(".",",",$vol->getElementsByTagName("pesoB")->item(0)->nodeValue);}
			if($vol->getElementsByTagName("pesoL")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-pesoL']	= str_replace(".",",",$vol->getElementsByTagName("pesoL")->item(0)->nodeValue);}
		}
	// Adiciona sequencia de record
		$seq++;
		$arrayIRXML['detail'][$seq]['record']['field4'] =  ".";

	// No iReport constaram 37 registros na primeira pagina e 38 registro na segunda.
		// ira fazer um loop por todos os itens (det) caso.
		// posteriormente ira calcular o quanto que falta para fechar a pagina e completara com espacoes em branco (<field6>).

	// A quantidade de itens da nota fiscal (length)
		$indRegistro = $indRegistroAux = 1;
	// Numeracao da pagina para saber em qual esta
		$indPagina = 1;

	// Loop por todos os itens (det) da NF
		foreach( $det as $indiceItem => $itensNF ){
			unset($descricao);
			unset($descricao2);
			unset($descricao3);
			$seq++;
			// Instancia TAG de impostos
			$descricao = $det->item($indiceItem)->getElementsByTagName('xProd')->item(0)->nodeValue;
			if($det->item($indiceItem)->getElementsByTagName('infAdProd')->item(0)){$descricao .= $det->item($indiceItem)->getElementsByTagName('infAdProd')->item(0)->nodeValue;}

			$descPart=0;
			$nomeDet = "det5";
			$icms = $det->item($indiceItem)->getElementsByTagName('ICMS');
			$ipi = $det->item($indiceItem)->getElementsByTagName('IPI');
			$vTotTrib = $det->item($indiceItem)->getElementsByTagName('vTotTrib');
			
			// testar se eh paisagem
			if($this->orientacao == "P"){
				// Descricao do Produto nao dividido em partes (linhas)
				$descPart=ceil(strlen($descricao)/53);
				$caracteresQuebra = 53;
			}else{ // Eh retrato
				// Descricao do Produto nao dividido em partes (linhas)
				$descPart=ceil(strlen($descricao)/34);
				$caracteresQuebra = 34;
			}
			
			if($this->grupo == "fitax" || $this->grupo == "fitaspack" || $this->grupo == "fnc"){ // para retrato apenas
				$descPart=ceil(strlen($descricao)/28);
				$caracteresQuebra = 28;
				
				if($descPart > 1){
					$descricao2 = substr($descricao,0,28);
					$nomeDet = "det8";
				}else{
					$descricao2 = $descricao;
				}
			}else{
				if($descPart > 1){
					$descricao2 = substr($descricao,0,34);
					$nomeDet = "det8";
				}else{
					$descricao2 = $descricao;
				}
			}
	
			
			// Para Retrato: Acima de 37 registros/itens na p�gina 1 ou 76 da segunda em diante, ira quebrar pagina e comecar nova.
			// Para Paisagem: Acima de XX registros/itens na p�gina 1 ou XX da segunda em diante, ira quebrar pagina e comecar nova.
			if( $this->orientacao == "R" && ((($indRegistroAux+$descPart) > 37 && $indPagina == 1) || (($indRegistroAux+$descPart) > 76 && $indPagina > 1))
			||	$this->orientacao == "P" && ((($indRegistroAux+$descPart) > 9 && $indPagina == 1) || (($indRegistroAux+$descPart) > 36 && $indPagina > 1)) ){
				// Finaliza pagina Dados adicionais e breakpage
				$seq++;

				if($contInfAdic>0){
					// Secao dos dados adicionais
				//	if(trim($infAdic) != ""){
					//$tmpInfAdic = $arrayInfAdic;
					for($i=1;$i<=13;$i++){
						$arrayIRXML['detail'][$seq]['record']['det7-infAdic-'.$i] = $arrayInfAdic[$indiceDadosAdicionais];
						$indiceDadosAdicionais++;
					}	
					$contInfAdic--;
				}else{
					$arrayIRXML['detail'][$seq]['record']['det7-infAdic-1'] = ".";
				}
                                $arrayIRXML['detail'][$seq]['record']['det7-reservado-fisco'] = $infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['breakPage'] = " ";

				$indRegistro=$indRegistroAux=1;
				$indPagina++;
				$seq++;
				
				if(trim($retorno[0]['danfe_logo_caminho']) != ""){
					$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = trim($retorno[0]['danfe_logo_caminho']);
				}else{
					$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = "";
				}
				
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xLgr-nro'] = $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xCpl'] 	= @$enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue."/".$enderEmit->getElementsByTagName("UF")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-CEP'] 		= "CEP: ".$this->mask($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
				$arrayIRXML['detail'][$seq]['record']['det2-emit-fone']		= "Fone: ".ltrim($this->mask($enderEmit->getElementsByTagName("fone")->item(0)->nodeValue, "(##) ####-#####"),0);
				$arrayIRXML['detail'][$seq]['record']['det2-tpEmis'] 		= $ide->getElementsByTagName("tpNF")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
				$this->chaveNFE = str_replace('NFe', '', $infNFe->getAttribute("Id"));
				$arrayIRXML['detail'][$seq]['record']['det2-id'] 			= implode(" ",str_split($this->chaveNFE,4));
				$arrayIRXML['detail'][$seq]['record']['det2-codbar'] 		= $this->chaveNFE;
				$arrayIRXML['detail'][$seq]['record']['det2-natOp'] 		= $ide->getElementsByTagName("natOp")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-protNFe'] 		= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".date("d/m/Y h:i:s", strtotime($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue));
				$arrayIRXML['detail'][$seq]['record']['det2-IE'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
				if($emit->getElementsByTagName("IEST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det2-IEST'] = $emit->getElementsByTagName("IEST")->item(0)->nodeValue;}
				$arrayIRXML['detail'][$seq]['record']['det2-emit-CNPJ'] 	= $this->mask($emit->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
				$seq++;
				$arrayIRXML['detail'][$seq]['record']['field4'] =  ".";
				$seq++;
			}
			
			/* IF abaixo colocado pois na quebra da descri��o esta dando problema de quebra de linha involuntaria */
/*			if( $this->orientacao == "R" && (($indRegistroAux+$descPart > 37 && $indPagina == 1) || ($indRegistroAux+$descPart > 76 && $indPagina > 1))
			||	$this->orientacao == "P" && (($indRegistroAux+$descPart > 9 && $indPagina == 1) || ($indRegistroAux+$descPart > 36 && $indPagina > 1)) ){
				$indRegistroAux += $descPart;
			}else{*/
				//$arrayIRXML['detail'][$seq]['record']['field4'] = $itensNF->nodeValue;
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-cProd'] = $det->item($indiceItem)->getElementsByTagName('cProd')->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-xProd'] = $descricao2;
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-NCM'] = $det->item($indiceItem)->getElementsByTagName('NCM')->item(0)->nodeValue;
					if(trim(@$icms->item(0)->getElementsByTagName("CST")->item(0)->nodeValue) != ""){
						$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-CST'] = $icms->item(0)->getElementsByTagName("orig")->item(0)->nodeValue.$icms->item(0)->getElementsByTagName("CST")->item(0)->nodeValue;
					}else if(trim(@$icms->item(0)->getElementsByTagName("CSOSN")->item(0)->nodeValue) != ""){
						$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-CST'] = $icms->item(0)->getElementsByTagName("orig")->item(0)->nodeValue.$icms->item(0)->getElementsByTagName("CSOSN")->item(0)->nodeValue;
					}
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-CFOP'] = $det->item($indiceItem)->getElementsByTagName('CFOP')->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-uCom'] = $det->item($indiceItem)->getElementsByTagName('uCom')->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-qCom'] = $this->maskDecimal($det->item($indiceItem)->getElementsByTagName('qCom')->item(0)->nodeValue);
				if($this->mascaraMilhar){
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-qCom'] = number_format($det->item($indiceItem)->getElementsByTagName('qCom')->item(0)->nodeValue, 2, ',', '.');
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vUnCom'] = number_format($det->item($indiceItem)->getElementsByTagName('vUnCom')->item(0)->nodeValue, 2, ',', '.'); // Limitar os zeros a direito com min de ,00 gjps
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vDesc'] = @number_format($det->item($indiceItem)->getElementsByTagName('vDesc')->item(0)->nodeValue, 2, ',', '.');
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vProd'] = number_format($det->item($indiceItem)->getElementsByTagName('vProd')->item(0)->nodeValue, 2, ',', '.');
					//impostos
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vBC'] = @number_format($icms->item(0)->getElementsByTagName("vBC")->item(0)->nodeValue, 2, ',', '.');
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vBCST'] = @number_format($icms->item(0)->getElementsByTagName("vBCST")->item(0)->nodeValue, 2, ',', '.');
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vICMS'] = @number_format($icms->item(0)->getElementsByTagName("vICMS")->item(0)->nodeValue, 2, ',', '.');
					
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vICMSST'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vBCST")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vICMSST'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vICMSST")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vIPI'] = str_replace(".",",",@$ipi->item(0)->getElementsByTagName("vIPI")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vTotTrib'] = str_replace(".",",",@$vTotTrib->item(0)->nodeValue);
				}else{
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-qCom'] = $this->maskDecimal($det->item($indiceItem)->getElementsByTagName('qCom')->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vUnCom'] = $this->maskDecimal($det->item($indiceItem)->getElementsByTagName('vUnCom')->item(0)->nodeValue); // Limitar os zeros a direito com min de ,00 gjps
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vDesc'] = @$det->item($indiceItem)->getElementsByTagName('vDesc')->item(0)->nodeValue;
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vProd'] = $this->maskDecimal($det->item($indiceItem)->getElementsByTagName('vProd')->item(0)->nodeValue);
					//impostos
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vBC'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vBC")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vBCST'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vBCST")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vICMS'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vICMS")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vICMSST'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("vICMSST")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vIPI'] = str_replace(".",",",@$ipi->item(0)->getElementsByTagName("vIPI")->item(0)->nodeValue);
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-vTotTrib'] = str_replace(".",",",@$vTotTrib->item(0)->nodeValue);
				}
				//Impostos
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-pICMS'] = str_replace(".",",",@$icms->item(0)->getElementsByTagName("pICMS")->item(0)->nodeValue);
				$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-pIPI'] = str_replace(".",",",@$ipi->item(0)->getElementsByTagName("pIPI")->item(0)->nodeValue);
				
				for($i=1;$i<$descPart;$i++){
					$indRegistroAux++;
					$seq++;
					$descricao3 = substr($descricao,$caracteresQuebra*$i,$caracteresQuebra);
					$nomeDet = "det8";
					if(($i+1) == $descPart){
						$nomeDet = "det9";
					}
					$arrayIRXML['detail'][$seq]['record'][$nomeDet.'-xProd'] = $descricao3;
				}
//			}
			$indRegistro++;
			$indRegistroAux++;
			
        } // Fim do Loop de Itens
	
			

		if($this->orientacao == "R"){
			if($indRegistroAux < 36 && $indPagina == 1){
				for($indRegistroAux; $indRegistroAux<=36;$indRegistroAux++){
					$seq++;
					$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
				}
			}

			if($indRegistroAux < 76 && $indPagina > 1){
				for($indRegistroAux; $indRegistroAux<=76;$indRegistroAux++){
					$seq++;
					$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
				}
			}
		}else{
			if($indRegistroAux < 10 && $indPagina == 1){
				for($indRegistroAux; $indRegistroAux<=10;$indRegistroAux++){
					$seq++;
					$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
				}
			}
			if($indRegistroAux < 35 && $indPagina > 1){
				for($indRegistroAux; $indRegistroAux<=35;$indRegistroAux++){
					$seq++;
					$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
				}
			}
		}
		
		

		// Secao dos dados adicionais
		$seq++;
		$seqInfAdic=1;
//		if(trim($infAdic) != ""){
			//$arrayInfAdic = str_split($infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue,80);
			if($contInfAdic<=0){
				$arrayIRXML['detail'][$seq]['record']['det7-infAdic-1'] = ".";
			}else{
				while($contInfAdic>0){
					// Secao dos dados adicionais
					for($x=1;$x<=13;$x++){
						$arrayIRXML['detail'][$seq]['record']['det7-infAdic-'.$x] = $arrayInfAdic[$indiceDadosAdicionais];
						$indiceDadosAdicionais++;
					}
                                        $arrayIRXML['detail'][$seq]['record']['det7-reservado-fisco'] = $infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue;
					$contInfAdic--;
					//Caso nao finalizado Quebra de Pagina
					if($contInfAdic>0){
						//BreakPage
						$arrayIRXML['detail'][$seq]['record']['breakPage'] = " ";
						$indRegistro=$indRegistroAux=1;
						$indPagina++;
						$seq++;
						if(trim($retorno[0]['danfe_logo_caminho']) != ""){
							$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = trim($retorno[0]['danfe_logo_caminho']);
						}else{
							$arrayIRXML['detail'][$seq]['record']['det2-logomarca'] = "";
						}
						$arrayIRXML['detail'][$seq]['record']['det2-emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-emit-xLgr-nro'] = $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-emit-xCpl'] 	= @$enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue."/".$enderEmit->getElementsByTagName("UF")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-emit-CEP'] 		= "CEP: ".$this->mask($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
						$arrayIRXML['detail'][$seq]['record']['det2-emit-fone']		= "Fone: ".ltrim($this->mask($enderEmit->getElementsByTagName("fone")->item(0)->nodeValue, "(##) ####-#####"),0);
						$arrayIRXML['detail'][$seq]['record']['det2-tpEmis'] 		= $ide->getElementsByTagName("tpNF")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
						$this->chaveNFE = str_replace('NFe', '', $infNFe->getAttribute("Id"));
						$arrayIRXML['detail'][$seq]['record']['det2-id'] 			= implode(" ",str_split($this->chaveNFE,4));
						$arrayIRXML['detail'][$seq]['record']['det2-codbar'] 		= $this->chaveNFE;
						$arrayIRXML['detail'][$seq]['record']['det2-natOp'] 		= $ide->getElementsByTagName("natOp")->item(0)->nodeValue;
						$arrayIRXML['detail'][$seq]['record']['det2-protNFe'] 		= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".date("d/m/Y h:i:s", strtotime($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue));
						$arrayIRXML['detail'][$seq]['record']['det2-IE'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
						if($emit->getElementsByTagName("IEST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det2-IEST'] = $emit->getElementsByTagName("IEST")->item(0)->nodeValue;}
						$arrayIRXML['detail'][$seq]['record']['det2-emit-CNPJ'] 	= $this->mask($emit->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
						$seq++;
						$arrayIRXML['detail'][$seq]['record']['field4'] =  ".";
						if($this->orientacao == "R"){
							if($indRegistroAux < 36 && $indPagina == 1){
								for($indRegistroAux; $indRegistroAux<=36;$indRegistroAux++){
									$seq++;
									$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
								}
							}

							if($indRegistroAux < 76 && $indPagina > 1){
								for($indRegistroAux; $indRegistroAux<=76;$indRegistroAux++){
									$seq++;
									$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
								}
							}
						}else{
							if($indRegistroAux < 10 && $indPagina == 1){
								for($indRegistroAux; $indRegistroAux<=10;$indRegistroAux++){
									$seq++;
									$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
								}
							}
							if($indRegistroAux < 35 && $indPagina > 1){
								for($indRegistroAux; $indRegistroAux<=35;$indRegistroAux++){
									$seq++;
									$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
								}
							}
						}
					}
				}
			}
			
		/*}else{
			$arrayIRXML['detail'][$seq]['record']['field7'] = ".";
			$arrayIRXML['detail'][$seq]['record']['det7-infAdic-1'] = ".";
		}*/
		
		/*if(trim($this->infAdic) != ""){
			//$arrayInfAdic = str_split($infAdic->nodeValue, "80");
			$arrayInfAdic = str_split($this->infAdic, "80");
			$seqInfAdic=1;
			foreach($arrayInfAdic as $contInfAdic){
				$arrayIRXML['detail'][$seq]['record']['det7-infAdic-'.$seqInfAdic] = $contInfAdic;
				$seqInfAdic++;
			}
		}else{
			$arrayIRXML['detail'][$seq]['record']['field7'] = ".";
		}*/

		// Footer do relatorio
		$arrayIRXML['footer'] = " ";
		
//		print_r($arrayIRXML);
		
		$xmlgen = new xmlgen();
		$this->xmlJUGIRXML = $xmlgen->generate('relatorio',$arrayIRXML);
		return true;
	}
	
	// Metodo para verificar se a orientacao eh retrato ou paisagem
	private function verificaOrientacao(){
		if(is_file($this->pathLayout)){
			$arquivoTemp = file_get_contents($this->pathLayout);
			if( strpos($arquivoTemp,'orientation="Landscape"') === false){
				$this->orientacao = "R";
			}else{
				$this->orientacao = "P";
			}
			unset($arquivoTemp);	
		}
	}
	
	// Metodo privado para imprimir a DANFE automaticamente na impressora de servidor, � necess�rio ter CUPS instalado e cadastrado
	// 	o nome correto da impressora no cadastro do contribuinte
	public function impressaoAutomatica($pImpressora, $pComando=""){
		$arquivo = $this->danfePdf;
		
		if($pComando != ""){
			$pComando = str_replace("_ARQUIVO_",$arquivo,$pComando);
			$pComando = str_replace("_IMPRESSORA_",$pImpressora,$pComando);

			if(!($retorno = exec($pComando))){
				$this->mensagemErro = "Houve erro no envio automatico da DANFE para impressora (".$pImpressora.") via Comando:\n".$retorno;
				return false;
			}
		}else{
			if(!($retorno = exec("cd /var/www/html/relatorios/; lp -d ".$pImpressora." ".$arquivo))){
				$this->mensagemErro = "Houve erro no envio automatico da DANFE para impressora (".$pImpressora."):\n".$retorno;
				return false;
			}
		}
		return true;
	}
	
	function mask($val, $mask){
		$maskared = '';
		$k = 0;
		for($i = 0; $i<=strlen($mask)-1; $i++)
		{
		if($mask[$i] == '#')
		{
		if(isset($val[$k]))
		$maskared .= $val[$k++];
		}
		else
		{
		if(isset($mask[$i]))
		$maskared .= $mask[$i];
		}
		}
		return $maskared;
	}

	function maskDecimal($val){
		$valor = explode(".",rtrim($val,0));
		if(strlen($valor[1]) == 0){
			return $valor[0].",00";
		}elseif(strlen($valor[1]) == 1){
			return $valor[0].",".$valor[1]."0";
		}else{
			return $valor[0].",".$valor[1];
		}
		
	}

}
