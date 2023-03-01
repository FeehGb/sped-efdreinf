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
 
 include("../libs/xmlgen.php");
 
class CGerarDanfe{
	// Mensagem de Erro
	public $mensagemErro='';
	// XML completa da NFE
	public $xmlNfe='';
	// Caminho completo da logomarca
	public $logo='';
	// Caminho da Danfe Gerada
	public $danfePdf='';
	// Chave da NFE
	private $chaveNFE;
	// XML de parametro para chamada do JUGIRXML
	private $xmlJUGIRXML='';
	
	// Método publico responsavel por gerar a DANFE
	public function gerarDanfe(){
		// Verificar se atributo foi setado corretamente pelo programa chamador
		if( empty($this->xmlNfe)){
			$this->mensagemErro = "XML nao definido, chamada de classe incorreta"; return false;
		}

		// Chamar para gerar XML de chamada ao JUGIRXML
		if(!$this->gerarXmlJUGIRXML()){ return false; }
		
		// Gravar em arquivo temporario no /user/relatorios/ apenas para chamar JUGIRXML
		if(!file_put_contents("/user/relatorios/NFE_".$this->chaveNFE.".xml",$this->xmlJUGIRXML)){
			$this->mensagemErro = "Impossivel criar arquivo temporario"; return false;
		}
		// Chamar JUIRXML para gerar relatorio
		
		if(!system("php -q /user/objetos/JUGIRXML.php /user/relatorios/NFE_".$this->chaveNFE.".xml /user/imagens/xml/layoutA4Retrato.jrxml /user/relatorios/NFE_".$this->chaveNFE.".pdf")){
			$this->mensagemErro = "Nao foi possivel criar o PDF da DANFE";
			return false;
		}
		
		// Remove o XML temporario do /user/relatorio que fora criado apenas para converter em PDF
		if(!unlink("/user/relatorios/NFE_".$this->chaveNFE.".xml")){
			$this->mensagemErro = "Nao foi possivel remover o XML temporario";
		}
		
		return true;
	}
	
	// Metodo privado para gerar XML de comunicacao com JUGIRXML a partir do XML da NFE
	private function gerarXmlJUGIRXML(){
		// Instanciar o XML completo da NFE
		$dom = new DomDocument;
        $dom->loadXML($this->xmlNfe);
		
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
        $compra     = $dom->getElementsByTagName("compra")->item(0);
        $tpEmis     = $ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
        $tpImp      = $ide->getElementsByTagName("tpImp")->item(0)->nodeValue;
        $infProt    = $dom->getElementsByTagName("infProt")->item(0);

	// Variavel de Sequencia dos records
		$seq = 0;
	// Montar cabecalho do JUGIRXML
		$arrayIRXML['header'] = " ";
	// Montar dados do JUGIRXML
		$arrayIRXML['detail'][$seq]['record']['det1-recebemos'] 	= "RECEBEMOS DE ".$emit->getElementsByTagName("xNome")->item(0)->nodeValue." OS PRODUTOS E/OU SERVICOS CONSTANTES DA NOTA FISCAL INDICADA AO LADO.";
		$arrayIRXML['detail'][$seq]['record']['det1-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		
	// Adiciona sequencia de record
		$seq++;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xLgr-nro'] = $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-CEP'] 		= $enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-emit-fone']		= $enderEmit->getElementsByTagName("fone")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-tpEmis'] 		= $ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-id'] 			= $this->chaveNFE = str_replace('NFe', '', $infNFe->getAttribute("Id"));
		$arrayIRXML['detail'][$seq]['record']['det2-natOp'] 		= $ide->getElementsByTagName("natOp")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-protNFe'] 		= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".$infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-IE'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
		if($emit->getElementsByTagName("IEST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det2-IEST'] = $emit->getElementsByTagName("IEST")->item(0)->nodeValue;}
		$arrayIRXML['detail'][$seq]['record']['det2-emit-CNPJ'] 	= $emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
	// Adiciona sequencia de record
		$seq++;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-xNome'] 	= $dest->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-CNPJ'] 	= $dest->getElementsByTagName("CNPJ")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dEmi'] 			= $ide->getElementsByTagName("dEmi")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-xLgr']		= $enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderDest->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-xBairro']	= $enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-CEP']		= $enderDest->getElementsByTagName("CEP")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-dSaiEnt']	= $ide->getElementsByTagName("dSaiEnt")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-cMun']		= $enderDest->getElementsByTagName("xMun")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-fone']		= $enderDest->getElementsByTagName("fone")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-UF']		= $enderDest->getElementsByTagName("UF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-dest-IE']		= $enderDest->getElementsByTagName("IE")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det3-hSaiEnt']		= $dest->getElementsByTagName("xMun")->item(0)->nodeValue;
		
	// Secao de Calculo de Impostos (Totalizacao do ICMS)
		if($ICMSTot){
			if($ICMSTot->getElementsByTagName("vBC")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBC']		= $ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vICMS")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vICMS']	= $ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vBCST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vBCST']	= $ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vST']		= $ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vIPI")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vIPI']		= $ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vProd")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vProd']	= $ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vFrete")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vFrete']	= $ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vSeg")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vSeg']		= $ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vOutro")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vOutro']	= $ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vTotTrib")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vTrib']	= $ICMSTot->getElementsByTagName("vTotTrib")->item(0)->nodeValue;}
			if($ICMSTot->getElementsByTagName("vNF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-vNF']		= $ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue;}
		}
		
	// Secao de modalidade do frente
		if($transp){
			if($transp->getElementsByTagName("modFrete")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-modFrete']	= $transp->getElementsByTagName("modFrete")->item(0)->nodeValue;}
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
			if($transporta->getElementsByTagName("CNPJ")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-CNPJ']		= $transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("xEnder")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-xEnder']	= $transporta->getElementsByTagName("xEnder")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("xMun")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-xMun']		= $transporta->getElementsByTagName("xMun")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("UF")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-UF']			= $transporta->getElementsByTagName("UF")->item(0)->nodeValue;}
			if($transporta->getElementsByTagName("IE")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-IE']			= $transporta->getElementsByTagName("IE")->item(0)->nodeValue;}
		}
		
	// Secao de informacoes do Volume transportado
		if($vol){
			if($vol->getElementsByTagName("qVol")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-qVol']	= $vol->getElementsByTagName("qVol")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("esp")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-esp']		= $vol->getElementsByTagName("esp")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("marca")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-marca']	= $vol->getElementsByTagName("marca")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("nVol")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-nVol']	= $vol->getElementsByTagName("nVol")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("pesoB")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-pesoB']	= $vol->getElementsByTagName("pesoB")->item(0)->nodeValue;}
			if($vol->getElementsByTagName("pesoL")->item(0)){$arrayIRXML['detail'][$seq]['record']['det3-transp-pesoL']	= $vol->getElementsByTagName("pesoL")->item(0)->nodeValue;}
		}
	// Adiciona sequencia de record
		$seq++;
		$arrayIRXML['detail'][$seq]['record']['field4'] =  ".";

	// No iReport constaram 18 registros na primeira pagina e 39 registro na segunda.
		// ira fazer um loop por todos os itens (det) caso.
		// posteriormente ira calcular o quanto que falta para fechar a pagina e completara com espacoes em branco (<field6>).

	// A quantidade de itens da nota fiscal (length)
		$indRegistro = 1;
	// Numeracao da pagina para saber em qual esta
		$indPagina = 1;

	// Loop por todos os itens (det) da NF
		foreach( $det as $indiceItem => $itensNF ){
			$seq++;
			// Instancia TAG de impostos
			$icms = $det->item($indiceItem)->getElementsByTagName('ICMS');
			$ipi = $det->item($indiceItem)->getElementsByTagName('IPI');
			
			//$arrayIRXML['detail'][$seq]['record']['field4'] = $itensNF->nodeValue;
			//echo $itensNF->nodeValue ."<br>";
			$arrayIRXML['detail'][$seq]['record']['det5-cProd'] = $det->item($indiceItem)->getElementsByTagName('cProd')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-xProd'] = $det->item($indiceItem)->getElementsByTagName('xProd')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-NCM'] = $det->item($indiceItem)->getElementsByTagName('NCM')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-CST'] = $icms->item(0)->getElementsByTagName("orig")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-CFOP'] = $det->item($indiceItem)->getElementsByTagName('CFOP')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-uCom'] = $det->item($indiceItem)->getElementsByTagName('uCom')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-qCom'] = $det->item($indiceItem)->getElementsByTagName('qCom')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vUnCom'] = $det->item($indiceItem)->getElementsByTagName('vUnCom')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vDesc'] = $det->item($indiceItem)->getElementsByTagName('vDesc')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vProd'] = $det->item($indiceItem)->getElementsByTagName('vProd')->item(0)->nodeValue;
			//Impostos
			$arrayIRXML['detail'][$seq]['record']['det5-vBC'] = $icms->item(0)->getElementsByTagName("vBC")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vBCST'] = $icms->item(0)->getElementsByTagName("vBCST")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vICMS'] = $icms->item(0)->getElementsByTagName("vICMS")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vICMSST'] = $icms->item(0)->getElementsByTagName("vICMSST")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-vIPI'] = $ipi->item(0)->getElementsByTagName("vIPI")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-pICMS'] = $icms->item(0)->getElementsByTagName("pICMS")->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det5-pIPI'] = $ipi->item(0)->getElementsByTagName("pIPI")->item(0)->nodeValue;
			
			$indRegistro++;
			// Acima de 18 (para primeira pagina) ou 39 (para segunda pagina), ira quebrar linha pagina e comecar nova.
			if( ($indRegistro > 18 && $indPagina == 1) || ($indRegistro > 39 && $indPagina > 1)){
				// Finaliza pagina Dados adicionais e breakpage
				$seq++;
				if($infAdic){
					$arrayIRXML['detail'][$seq]['record']['det7-infAdic'] = $infAdic->nodeValue;
				}
				$arrayIRXML['detail'][$seq]['record']['breakPage'] = " ";

				$indRegistro=1;
				$indPagina++;
				$seq++;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xLgr-nro'] = $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-CEP'] 		= $enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-emit-fone']		= $enderEmit->getElementsByTagName("fone")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-tpEmis'] 		= $ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-nNF'] 			= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-id'] 			= $this->chaveNFE = str_replace('NFe', '', $infNFe->getAttribute("Id"));
				$arrayIRXML['detail'][$seq]['record']['det2-natOp'] 		= $ide->getElementsByTagName("natOp")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-protNFe'] 		= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".$infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue;
				$arrayIRXML['detail'][$seq]['record']['det2-IE'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
				if($emit->getElementsByTagName("IEST")->item(0)){$arrayIRXML['detail'][$seq]['record']['det2-IEST'] = $emit->getElementsByTagName("IEST")->item(0)->nodeValue;}
				$arrayIRXML['detail'][$seq]['record']['det2-emit-CNPJ'] 	= $emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
				
				$seq++;
				$arrayIRXML['detail'][$seq]['record']['field4'] =  ".";
			}
        }
		
		if($indRegistro < 18 && $indPagina == 1){
			for($indRegistro; $indRegistro<=18;$indRegistro++){
				$seq++;
				$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
			}
		}
		
		if($indRegistro < 39 && $indPagina > 1){
			for($indRegistro; $indRegistro<=39;$indRegistro++){
				$seq++;
				$arrayIRXML['detail'][$seq]['record']['field6'] = ".";
			}
		}
		
		// Secao dos dados adicionais
		$seq++;
		if($infAdic){
			$arrayIRXML['detail'][$seq]['record']['det7-infAdic'] = $infAdic->nodeValue;
		}

		// Footer do relatorio
		$arrayIRXML['footer'] = " ";
		
		$xmlgen = new xmlgen();
		$this->xmlJUGIRXML = $xmlgen->generate('relatorio',$arrayIRXML);
		return true;
	}
}
?>