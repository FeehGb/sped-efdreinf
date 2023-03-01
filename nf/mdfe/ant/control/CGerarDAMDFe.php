<?php
/**
 * @name      	CGerarDAMDFe
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para gerar a DANFE a partir do XML informado
 * @TODO 		Colocar mascaras nos campos que irao para o ireport (ex CNPJ)
*/

/**
 * Classe CGerarDAMDFe
 */

include(__ROOT__."/libs/xmlgen.php");
require_once(__ROOT__."/model/MContribuinte.php");
require_once(__ROOT__."/model/MMDFe.php");
 
class CGerarDAMDFe{
	// Mensagem de Erro
	public $mensagemErro='';
	// XML completa da NFE
	public $xmlMDFe='';
	// Informacoes Aidicionais
	public $infAdic='';
	// Caminho da Danfe Gerada
	public $DAMDFePdf='';
	// Chave da NFE
	private $chaveMDFe;
	// Cnpj contribuinte
	private $pathLayout;
	// XML de parametro para chamada do JUGIRXML
	private $xmlJUGIRXML='';
	
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}

	// Método publico responsavel por gerar a DANFE
	public function gerarDAMDFe(){
		// Verificar se atributo foi setado corretamente pelo programa chamador
		if(empty($this->xmlMDFe)){
			$this->mensagemErro = "XML nao definido, chamada de classe incorreta"; return false;
		}

		// Chamar para gerar XML de chamada ao JUGIRXML
		if(!$this->gerarXmlJUGIRXML()){ return false; }

		// Gravar em arquivo temporario no /user/relatorios/ apenas para chamar JUGIRXML
		if(!file_put_contents("/var/www/html/relatorios/MDFE_".$this->chaveMDFe.".xml",$this->xmlJUGIRXML)){
			$this->mensagemErro = "Impossivel criar arquivo temporario"; return false;
		}
		// Chamar JUIRXML para gerar relatorio
		if(system("cd /var/www/html/relatorios/; php -q /user/objetos/JUGIRXML.php /var/www/html/relatorios/MDFE_".$this->chaveMDFe.".xml ".$this->pathLayout." /var/www/html/relatorios/MDFE_".$this->chaveMDFe.".pdf") != ""){
			$this->mensagemErro = "Nao foi possivel criar o PDF da DAMDFE";
			return false;
		}
		
		// Remove o XML temporario do /user/relatorio que fora criado apenas para converter em PDF
		/*if(!unlink("/var/www/html/relatorios/MDFE_".$this->chaveMDFe.".xml")){
			$this->mensagemErro = "Nao foi possivel remover o XML temporario";
		}*/
		
		$this->DAMDFePdf = "MDFE_".$this->chaveMDFe.".pdf";
		return true;
	}
	
	// Método publico responsavel por gerar a DANFE Automaticamente quando chamada pelo programa do CRONTAB
	public function gerarDanfeAutomatica(){
		// Seleciona as Notas Fiscais pendentes de emissão automática da DANFE
		$MMDFe = new MMDFe($this->grupo);
		$sql = "SELECT 	* FROM `mdfe_".$this->grupo."`.`MDFE` WHERE damdfe_impressa <> 'S'";

		$notasFiscais = $MMDFe->selectAllMestre($sql);

		$cont=0;

		while(@is_array($notasFiscais[$cont])){
			$MContribuinte = new MContribuinte($this->grupo);
			$MContribuinte->cnpj		= $notasFiscais[$cont]['cnpj'];
			$MContribuinte->ambiente	= $notasFiscais[$cont]['ambiente'];
			$rContribuinte = $MContribuinte->selectCNPJAmbiente();
			
			if(!$rContribuinte){
				$this->mensagemErro = $MContribuinte->mensagemErro;
				return false;
			}

			// Verifica se o Contribuinte ñ requer impressão da Danfe Automática, no teste abaixo ele não requer, e continua para próxima NF
			if($rContribuinte[0]['damdfe_automatica'] != "1"){
				$cont++;
				continue;
			}
			
			// Verifica se o contribuinte está em contigência FS (02) ou FS-da (05), caso sim desconsidera e lê a próxima nota
			if($rContribuinte[0]['contigencia'] == "02" || $rContribuinte[0]['contigencia'] == "05"){
				$cont++;
				continue;
			}
			
			// Verificar se a a Danfe Automática de Nota está com status "N" (não foi impressa)
			if($notasFiscais[$cont]['damdfe_impressa'] == "N"){
				// Verificar se a NFe está autorizada o uso (status = 03)
				if($notasFiscais[$cont]['status'] == "03"){
					// Gerar a Danfe
					$this->xmlMDFe = $notasFiscais[$cont]['xml'];
					$this->infAdic = "";
					if(!$this->gerarDAMDFe()){
						return false;
					}
					
					// Impressão Automática da DANFE
					$this->impressaoAutomatica($rContribuinte[0]['server_impressao']);
					
					// Update da NFe, campo danfe_impressa = "S"
					$MDFeUpdate = new MMDFe($this->grupo);
					$MDFeUpdate->cnpj				= $notasFiscais[$cont]['cnpj'];
					$MDFeUpdate->ambiente			= $notasFiscais[$cont]['ambiente'];
					$MDFeUpdate->numero				= $notasFiscais[$cont]['numero'];
					$MDFeUpdate->serie				= $notasFiscais[$cont]['serie'];
					$MDFeUpdate->damdfe_impressa 	= "S";
					if(!$MDFeUpdate->update()){
						$this->mensagemErro = $MDFeUpdate->mensagemErro;
						return false;
					}
				// Danfe não há autorização de Uso pela SEFAZ (status != 03)
				}else{
					// Danfe com autorização DPEC (status = 04)
					if($notasFiscais[$cont]['tipo_emissao'] == "4"){
						if($notasFiscais[$cont]['lote_nfe'] != null){
									//
									// IMPRESSÃO DA NOTA FISCAL EM DPEC
									//
									
							// Update da NFe, campo danfe_impressa = "S"
							$MDFeUpdate = new MNotaFiscal($this->grupo);
							$MDFeUpdate->cnpj				= $notasFiscais[$cont]['cnpj'];
							$MDFeUpdate->ambiente			= $notasFiscais[$cont]['ambiente'];
							$MDFeUpdate->numero				= $notasFiscais[$cont]['numero'];
							$MDFeUpdate->serie				= $notasFiscais[$cont]['serie'];
							$MDFeUpdate->damdfe_impressa	= "C";
							if(!$MDFeUpdate->update()){
								$this->mensagemErro = $MDFeUpdate->mensagemErro;
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
				if($notasFiscais[$cont]['status'] == "03" && $notasFiscais[$cont]['damdfe_impressa'] == "C"){

					// Gerar Danfe no diretorio padrao
					$this->xmlMDFe = $notasFiscais[$cont]['xml'];
					$this->infAdic = $notasFiscais[$cont]['observacao'];
					if(!$this->gerarDAMDFe()){
						return false;
					}
					
					// Impressão Automática da DANFE
					$this->impressaoAutomatica($rContribuinte[0]['server_impressao']);

					// Update da NFe, campo danfe_impressa = "S"
					$MDFeUpdate = new MNotaFiscal($this->grupo);
					$MDFeUpdate->cnpj				= $notasFiscais[$cont]['cnpj'];
					$MDFeUpdate->ambiente			= $notasFiscais[$cont]['ambiente'];
					$MDFeUpdate->numero				= $notasFiscais[$cont]['numero'];
					$MDFeUpdate->serie				= $notasFiscais[$cont]['serie'];
					$MDFeUpdate->damdfe_impressa	= "S";
					if(!$MDFeUpdate->update()){
						$this->mensagemErro = $MDFeUpdate->mensagemErro;
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
        $dom->preservWhiteSpace	= false; //elimina espaços em branco
        $dom->formatOutput 		= false;
        $dom->loadXML(base64_decode($this->xmlMDFe));

		// Variavel XML Local de comunicacao com o JURIXML
		$arrayIRXML = "";
		$infMDFe    = $dom->getElementsByTagName("infMDFe")->item(0);
        $ide        = $infMDFe->getElementsByTagName("ide")->item(0);
		$emit       = $infMDFe->getElementsByTagName("emit")->item(0);
			$enderEmit  = $emit->getElementsByTagName("enderEmit")->item(0);
		$infModal   = $infMDFe->getElementsByTagName("infModal")->item(0);
			$rodo   		= $dom->getElementsByTagName("rodo")->item(0);
			$veicTracao 	= $dom->getElementsByTagName("veicTracao")->item(0);
			$veicReboque 	= $dom->getElementsByTagName("veicReboque");
		$infDoc   	= $infMDFe->getElementsByTagName("infDoc")->item(0);
		$tot   		= $infMDFe->getElementsByTagName("tot")->item(0);
		$infAdic	= $infMDFe->getElementsByTagName("infAdic")->item(0);
		$valePed	= $dom->getElementsByTagName("valePed")->item(0);
		
        $infProt    = $dom->getElementsByTagName("infProt")->item(0);

	// Variavel de Sequencia dos records
		$seq = 0;
	// Montar cabecalho do JUGIRXML
		$arrayIRXML['header'] = " ";
	// Montar dados do JUGIRXML		
		// Obtem a logomarca do cadastro do usuario
		$MContribuinte = new MContribuinte($this->grupo);
		$MContribuinte->cnpj = $emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
		$retornoContribuinte = $MContribuinte->selectAll();
		if(trim($retornoContribuinte[0]['damdfe_logo_caminho']) != ""){
			$arrayIRXML['detail'][$seq]['record']['det1-logo-empresa'] = trim($retornoContribuinte[0]['damdfe_logo_caminho']);
		}else{
			$arrayIRXML['detail'][$seq]['record']['det1-logo-empresa'] = "SOFTDIB.jpg";
		}
		
		if(trim($retornoContribuinte[0]['damdfe_layout_caminho']) != ""){
			$this->pathLayout = trim($retornoContribuinte[0]['damdfe_layout_caminho']);
		}else{
			$this->pathLayout = "/user/imagens/xml/MDFE-RH-MODELO.jrxml";
		}

		// IDENTIFICACAO EMITENTE
		$arrayIRXML['detail'][$seq]['record']['det1-nome-empresa'] 	= $emit->getElementsByTagName("xFantasia")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-cnpj']		 	= $this->mask($emit->getElementsByTagName("CNPJ")->item(0)->nodeValue, "##.###.###/####-##");
		$arrayIRXML['detail'][$seq]['record']['det1-ie'] 			= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-rntrc'] 		= $rodo->getElementsByTagName("RNTRC")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-razao-social'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-logradouro'] 	= $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-complemento'] 	= $enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-bairro'] 		= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-municipio-uf'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue." / ".$enderEmit->getElementsByTagName("UF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-cep'] 			= $this->mask($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
		
		$this->chaveMDFe = str_replace('MDFe', '', $infMDFe->getAttribute("Id"));
		$arrayIRXML['detail'][$seq]['record']['det1-chave-acesso']	= implode(" ",str_split($this->chaveMDFe,4));
		$arrayIRXML['detail'][$seq]['record']['det1-cod-barras'] 	= $this->chaveMDFe;
		
		$arrayIRXML['detail'][$seq]['record']['det1-modelo'] 			= $ide->getElementsByTagName("mod")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-serie'] 			= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-numero'] 			= $ide->getElementsByTagName("nMDF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-folha'] 			= "1 / 1";
		$arrayIRXML['detail'][$seq]['record']['det1-data-hora-emissao'] = $ide->getElementsByTagName("dhEmi")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-uf-carreg'] 		= $ide->getElementsByTagName("UFIni")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-uf-descarreg'] 		= $ide->getElementsByTagName("UFFim")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det1-prot-aut'] 			= $infProt->getElementsByTagName("nProt")->item(0)->nodeValue." ".date("d/m/Y h:i:s", strtotime($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue));
	// MODAL RODOVIARIO
		$seq++;

		$arrayIRXML['detail'][$seq]['record']['det2-ciot'] 		= " ";
		$arrayIRXML['detail'][$seq]['record']['det2-qtd-cte']	= $tot->getElementsByTagName("qCTe")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-qtd-ctrc']	= "";
		$arrayIRXML['detail'][$seq]['record']['det2-qtd-nfe']	= $tot->getElementsByTagName("qNFe")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-qtd-nf']	= $tot->getElementsByTagName("qNF")->item(0)->nodeValue;
		if(ltrim($tot->getElementsByTagName("cUnid")->item(0)->nodeValue,0) == "1"){
			$arrayIRXML['detail'][$seq]['record']['det2-unid-peso']	= "(KG)";
		}elseif(ltrim($tot->getElementsByTagName("cUnid")->item(0)->nodeValue,0) == "2"){
			$arrayIRXML['detail'][$seq]['record']['det2-unid-peso']	= "(TON)";
		}
		$arrayIRXML['detail'][$seq]['record']['det2-peso-tot']	= $tot->getElementsByTagName("qCarga")->item(0)->nodeValue;

		$arrayIRXML['detail'][$seq]['record']['det2-placa-1'] = $veicTracao->getElementsByTagName('placa')->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-rntrc-1'] = $veicTracao->getElementsByTagName('RNTRC')->item(0)->nodeValue;

		$indReboque=2;
		foreach($veicReboque as $indiceReboque => $itensReboque){
			$arrayIRXML['detail'][$seq]['record']['det2-placa-'.$indReboque] =
				$veicReboque->item($indiceReboque)->getElementsByTagName('placa')->item(0)->nodeValue;
			$arrayIRXML['detail'][$seq]['record']['det2-rntrc-'.$indReboque] =
				$veicReboque->item($indiceReboque)->getElementsByTagName('RNTRC')->item(0)->nodeValue;
		}
		

		$seqCond=1;
		$condutor = $veicTracao->getElementsByTagName('condutor');
		foreach( $condutor as $indiceCond => $itensCond ){
			$arrayIRXML['detail'][$seq]['record']['det2-cpf-'.$seqCond] = $this->mask($condutor->item($indiceCond)->getElementsByTagName('CPF')->item(0)->nodeValue, "###.###.###-##");
			$arrayIRXML['detail'][$seq]['record']['det2-nome-'.$seqCond] = $condutor->item($indiceCond)->getElementsByTagName('xNome')->item(0)->nodeValue;
			$seqCond++;
		}

		// VER COM ROBERTO SE A PLACA DO REBOQUE EH NECESSARIO
		/*$arrayIRXML['detail'][$seq]['record']['det2-placa-'.$seqCond] = $condutor->item($indiceCond)->getElementsByTagName('CPF')->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-nome-'.$seqCond] = $condutor->item($indiceCond)->getElementsByTagName('xNome')->item(0)->nodeValue;
		*/

		$arrayIRXML['detail'][$seq]['record']['det2-placa-1'] = $veicTracao->getElementsByTagName('placa')->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['det2-rntrc-1'] = $veicTracao->getElementsByTagName('RNTRC')->item(0)->nodeValue;

		$veicTracao 	= $dom->getElementsByTagName("veicTracao")->item(0);
		$veicReboque 	= $dom->getElementsByTagName("veicReboque")->item(0);
		
		$arrayIRXML['detail'][$seq]['record']['det2-resp-cnpj'] 		= ltrim($valePed->getElementsByTagName('CNPJPg')->item(0)->nodeValue,0) == "" ? "" : $this->mask($valePed->getElementsByTagName('CNPJPg')->item(0)->nodeValue, "##.###.###/####-##");
		$arrayIRXML['detail'][$seq]['record']['det2-fornc-cnpj'] 		= ltrim($valePed->getElementsByTagName('CNPJForn')->item(0)->nodeValue,0) == "" ? "" : $this->mask($valePed->getElementsByTagName('CNPJForn')->item(0)->nodeValue, "##.###.###/####-##");
		$arrayIRXML['detail'][$seq]['record']['det2-num-comprovante'] 	= ltrim($valePed->getElementsByTagName('nCompra')->item(0)->nodeValue,0);

	// COMPOSICAO DE CARGA (DET3)
		$seq++;
		$infNFe = $infDoc->getElementsByTagName("infNFe");

		foreach( $infNFe as $indNFe => $itensNFe ){
			$arrayIRXML['detail'][$seq]['record']['det3-docto-fiscal-vinculado']	= "NFE ".$infNFe->item($indNFe)->getElementsByTagName('chNFe')->item(0)->nodeValue;

			$infUnidTransp = $infNFe->item($indNFe)->getElementsByTagName("infUnidTransp");
			foreach( $infUnidTransp as $indUnidTransp => $itensUnidTransp ){
				switch($infUnidTransp->item($indUnidTransp)->getElementsByTagName('tpUnidTransp')->item(0)->nodeValue){
					case "1":
						$tipoUnidade = "Rodoviario Tracao";
					break;
					case "2":
						$tipoUnidade = "Rodoviario Reboque";
					break;
					case "3":
						$tipoUnidade = "Navio";
					break;
					case "4":
						$tipoUnidade = "Balsa";
					break;
					case "5":
						$tipoUnidade = "Aeronave";
					break;
					case "6":
						$tipoUnidade = "Vagao";
					break;
					case "7":
						$tipoUnidade = "Outros";
					break;
				}
				$arrayIRXML['detail'][$seq]['record']['det3-unidade-transporte'] = $tipoUnidade." - ";
				$arrayIRXML['detail'][$seq]['record']['det3-unidade-transporte'] .=
					$infUnidTransp->item($indUnidTransp)->getElementsByTagName('idUnidTransp')->item(0)->nodeValue;
				
				/*if($infUnidTransp->getElementsByTagName("infUnidCarga")){
					$infUnidCarga = $infUnidTransp->getElementsByTagName("infUnidCarga");
					foreach( $infUnidCarga as $indUnidCarga => $itensUnidCarga ){
						switch($infUnidCarga->item($indUnidCarga)->getElementsByTagName('tpUnidCarga')->item(0)->nodeValue){
							case "1":
								$tipoCarga = "Container";
							break;
							case "2":
								$tipoCarga = "ULD";
							break;
							case "3":
								$tipoCarga = "Pallet";
							break;
							case "4":
								$tipoCarga = "Outros";
							break;
						}
						$arrayIRXML['detail'][$seq]['record']['det3-unidade-carga'] = $tipoCarga;
						$arrayIRXML['detail'][$seq]['record']['det3-unidade-carga'] =
							$infUnidCarga->item($indUnidCarga)->getElementsByTagName('idUnidCarga')->item(0)->nodeValue;
						$seq++;
					}
				}*/
				$seq++;
			}
			$seq++;
		}

		//$arrayIRXML['detail'][$seq]['record']['det4-obs-1']	= $valePed->getElementsByTagName('nCompra')->item(0)->nodeValue;

		if(trim($infAdic) != ""){
			$arrayInfAdic = str_split($infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue,80);
			$seqInfAdic=1;
			foreach($arrayInfAdic as $contInfAdic){
				$arrayIRXML['detail'][$seq]['record']['det4-obs-'.$seqInfAdic] = $contInfAdic;
				$seqInfAdic++;
			}

			/*foreach($obscont as $item => $conteudo){
				$arrayIRXML['detail'][$seq]['record']['det7-infAdic-'.$seqInfAdic] = $infAdic->getElementsByTagName("obsCont")->item($item)->getAttribute("xCampo")." ".$infAdic->getElementsByTagName("obsCont")->item($item)->nodeValue;
				$seqInfAdic++;
			}*/
		}else{
			$arrayIRXML['detail'][$seq]['record']['det4-obs-1'] = ".";
		}
		
		// Footer do relatorio
		$arrayIRXML['footer'] = " ";
		
//		print_r($arrayIRXML);
		
		$xmlgen = new xmlgen();
		$this->xmlJUGIRXML = $xmlgen->generate('relatorio',$arrayIRXML);
		return true;
	}
	
	// Metodo privado para imprimir a DANFE automaticamente na impressora de servidor, é necessário ter CUPS instalado e cadastrado
	// 	o nome correto da impressora no cadastro do contribuinte
	public function impressaoAutomatica($pImpressora){
		$arquivo = $this->DAMDFePdf;

		if(!($retorno = exec("cd /var/www/html/relatorios/; lp -d ".$pImpressora." ".$arquivo))){
			$this->mensagemErro = "Houve erro no envio automatico da DAMDFE para impressora (".$pImpressora."):\n".$retorno;
			return false;
		}
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
