<?php
/**
 * @name      	CCartaCorrecao
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer a manutenção do cadastro de Carta de Correcao
 * @TODO 		Fazer tudo
*/

/**
 * @import Importação de Classes de comunicação
 */ 
 //require_once("/var/www/html/nf/nfe/novo/libs/xmlgen.php");
 require_once("../model/MLote.php");
 require_once("../model/MNotaFiscal.php");
 require_once("../model/MContribuinte.php");
 require_once("../model/MEvento.php");
 require_once("../model/MLog.php");
 require_once("CEmail.php");
 require_once("CBackup.php");

/**
 * @class CCartaCorrecao
 */ 
class CCartaCorrecao{

/*
 * Atributos da Classe
 */

	public $NOTA_FISCAL_cnpj_emitente;	
	public $NOTA_FISCAL_numero_nota;
	public $NOTA_FISCAL_serie_nota;
	public $NOTA_FISCAL_ambiente;
	public $tipo_evento;
	public $numero_sequencia;
	public $xml_env;
	public $xml_ret;
	public $descricao;
	public $protocolo;
	public $data_hora;
	public $status;
	public $tipo_sequencia_origem;
	public $email_enviado;
	public $usuario;

	public $pdfCC;
	public $email;
	private $chave;
	private $nome_destinatario;

	private $statusLote;
	private $motivoLote;

	public $mensagemErro = "";

// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
/**
 * @method mValidarNota
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mValidarNota(){
		// Instancia Classe Model Nota Fiscal
		$MNotaFiscal = new MNotaFiscal($this->grupo);

		// Passagem de parametros para buscar a nota fiscal
		$MNotaFiscal->cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MNotaFiscal->numero_nota	= $this->NOTA_FISCAL_numero_nota;
		$MNotaFiscal->serie_nota	= $this->NOTA_FISCAL_serie_nota;
		$MNotaFiscal->ambiente		= $this->NOTA_FISCAL_ambiente;

		$return = $MNotaFiscal->selectAllMestre();
		
		if(!$return){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return $return;
		}

		// Verifica se a Nota Fiscal está autorizada
		if($return[0]['status'] != "03"){
			$this->mensagemErro = "NF-e nao esta autorizada, nao pode ser corrigida!";
			return false;
		}

		// Verifica se a Nota Fiscal foi emitida com menos de 30 dias
		// Removido conf. sol. Eliandro
/*		$dataNota = str_replace("-","",substr($return[0]['data_emissao'],0,10));
		$dataM30 = date("Ymd", strtotime("- 30 days"));

		if($dataM30 > $dataNota){
			$this->mensagemErro = "NF-e registrada na SEFAZ a mais de 30 dias, nao pode ser corrigida";
			return false;
		}

		return true;*/
	}
/**
 * @method mObter
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mObter(){
		// Instancia Classe Model Nota Fiscal
		$MEvento = new MEvento($this->grupo);

		$sql = "SELECT 	NOTA_FISCAL_cnpj_emitente,		
					NOTA_FISCAL_numero_nota,		
					NOTA_FISCAL_serie_nota,		
					NOTA_FISCAL_ambiente,		
					tipo_evento,		
					numero_sequencia,		
					descricao,		
					protocolo,		
					data_hora,		
					status,		
					email_enviado,
					xml_env,
					xml_ret,
					xml
					FROM `nfe_".$this->grupo."`.`EVENTO`
					WHERE 	NOTA_FISCAL_cnpj_emitente = '".$this->NOTA_FISCAL_cnpj_emitente."' AND
							NOTA_FISCAL_numero_nota =  '".$this->NOTA_FISCAL_numero_nota."' AND
							NOTA_FISCAL_serie_nota = '".$this->NOTA_FISCAL_serie_nota."' AND
							NOTA_FISCAL_ambiente = '".$this->NOTA_FISCAL_ambiente."';";
		
		// Passagem de parametros para consulta

		// Chamada da funcao selectAll e retorno do erro
		$return = $MEvento->selectMestre($sql);
		$this->mensagemErro = $MEvento->mensagemErro;
		return $return;
	}
	
	/**
 * @method mObter
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mIncluirCC(){
		// Declarando variaveis
		$sequencia=0;
		
		// Verificar se todos os campos foram preenchidos
		if($this->NOTA_FISCAL_cnpj_emitente == ""){
			$this->mensagemErro = "CCartaCorrecao -> CNPJ do Emitente inconsistente!"; return false;
		}
		if($this->NOTA_FISCAL_numero_nota == ""){
			$this->mensagemErro = "CCartaCorrecao -> Numero da Nota Fiscal inconsistente!"; return false;
		}
		if($this->NOTA_FISCAL_serie_nota == ""){
			$this->mensagemErro = "CCartaCorrecao -> Serie da Nota Fiscal inconsistente!"; return false;
		}
		if($this->NOTA_FISCAL_ambiente == ""){
			$this->mensagemErro = "CCartaCorrecao -> Tipo de Ambiente inconsistente!"; return false;
		}
		if($this->descricao == ""){
			$this->mensagemErro = "CCartaCorrecao -> Descricao inconsistente!"; return false;
		}
		if(strlen($this->descricao) < 15 ||  strlen($this->descricao) > 1000){
			$this->mensagemErro = "A correcao deve ter de 15 a 1000 caraceteres!"; return false;
		}

		// Instancia Classe de LOG
		$MLog = new MLog($this->grupo);
		$MLog->NOTA_FISCAL_cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MLog->NOTA_FISCAL_numero_nota		= $this->NOTA_FISCAL_numero_nota;
		$MLog->NOTA_FISCAL_serie_nota		= $this->NOTA_FISCAL_serie_nota;
		$MLog->NOTA_FISCAL_ambiente			= $this->NOTA_FISCAL_ambiente;
		$MLog->data_hora					= $retorno['dhRegEvento'];
		$MLog->evento						= "CARTA DE CORRECAO";
		$MLog->usuario						= $this->usuario; // Obter o lk-usuario;
		
		// Instancia Classe Model Evento para Obter o ultimo numero da sequencia gravado
		$MEvento = new MEvento($this->grupo);
		
		$MEvento->NOTA_FISCAL_cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MEvento->NOTA_FISCAL_numero_nota	= $this->NOTA_FISCAL_numero_nota;
		$MEvento->NOTA_FISCAL_serie_nota	= $this->NOTA_FISCAL_serie_nota;
		$MEvento->NOTA_FISCAL_ambiente		= $this->NOTA_FISCAL_ambiente;	
		$MEvento->tipo_evento				= "6";

		$return = $MEvento->selectMaxSeq();
		
		if(!$return){
			$this->mensagemErro = $MEvento->mensagemErro;
			return false;
		}
		
		// 	Valida o último número obtido e atribui o proximo da sequencia
		if($return == null || empty($return[0]['ult_sequencia'])){
			$sequencia = 1;
		}else{
			if($return[0]['ult_sequencia'] >= 20){
				$this->mensagemErro = "CCartaCorrecao -> { Limite maximo de 20 correcoes por NF-e excedido }";
				return false;
			}else{
				$sequencia = $return[0]['ult_sequencia'] + 1;
			}
		}

		// Chama classe Nota Fical para obter a chave da NF para vicular a Carta de Correcao
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		
		$MNotaFiscal->cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MNotaFiscal->numero_nota	= $this->NOTA_FISCAL_numero_nota;
		$MNotaFiscal->serie_nota	= $this->NOTA_FISCAL_serie_nota;
		$MNotaFiscal->ambiente		= $this->NOTA_FISCAL_ambiente;
		
		$retorno = $MNotaFiscal->selectAllMestre();
		
		$retornoNota = $retorno[0];
		
		if(!$retorno){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}
		// Recupera a chave da NFe para emissao da Carta de Correcao
		$this->chave 			= $retorno[0]['chave'];
		$tipo_emissao 	= $retorno[0]['tipo_emissao'];
		$ambiente 		= $retorno[0]['ambiente'];
		$this->email 	= $retorno[0]['email_destinatario'];
		
		// Cadastrar novo Lote na base de dados
		$MLote = new MLote($this->grupo); 

		$MLote->cnpj_emitente = $this->NOTA_FISCAL_cnpj_emitente;
		$MLote->versao = "";
		$MLote->recibo = "";
		$MLote->status = "";
		$MLote->ambiente = $this->NOTA_FISCAL_ambiente;
		$MLote->contingencia =  "";

		$return = $MLote->insert();
		if(!$return || $return == null){
			$this->mensagemErro = $MLote->mensagemErro;
			return false;
		}

		$lote = $return[0]['ult_id'];

		// Enviar Carta de Correcao para o SEFAZ
		$ToolsNFePHP = new ToolsNFePHP($this->NOTA_FISCAL_cnpj_emitente, $this->NOTA_FISCAL_ambiente, $this->grupo);
		$retorno = $ToolsNFePHP->envCCe($this->chave, $this->descricao, $sequencia, $lote, $this->NOTA_FISCAL_ambiente);

		$this->statusLote	= $ToolsNFePHP->arrayRetorno['cStatLote'];
		$this->motivoLote 	= $ToolsNFePHP->arrayRetorno['xMotivoLote'];

		$this->__mAtualizarLote($lote);
			
		if (!$retorno){
			$this->mensagemErro = $ToolsNFePHP->errMsg;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		// Inserir o evento de Carta de Correção no caso de sucesso.
		$MEvento->NOTA_FISCAL_cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MEvento->NOTA_FISCAL_numero_nota	= $this->NOTA_FISCAL_numero_nota;
		$MEvento->NOTA_FISCAL_serie_nota	= $this->NOTA_FISCAL_serie_nota;
		$MEvento->NOTA_FISCAL_ambiente		= $this->NOTA_FISCAL_ambiente;	
		$MEvento->tipo_evento				= "6";
		$MEvento->numero_sequencia			= $sequencia;
		$MEvento->xml_env					= $retorno['xml_env'];
		$MEvento->xml_ret					= $retorno['xml_ret'];
		$MEvento->xml						= $retorno['xml'];
		$MEvento->descricao					= $this->descricao;
		$MEvento->protocolo					= $retorno['nProt'];
		$MEvento->data_hora					= substr($retorno['dhRegEvenretCancNFeto'],0,10)." ".substr($retorno['dhRegEvenretCancNFeto'],11,8);
		$MEvento->status					= $retorno['cStat'];
		$MEvento->email_enviado				= "N";

		$return = $MEvento->insert();

		if(!$return){
//			echo "erro ao inserir o evento";
			$this->mensagemErro = $MEvento->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}
		
		// Enviar email para o destinatario sobre a carta de correcao.
		if(!$this->mEnviarEmailCC()){
//			echo $this->mensagemErro;
		}

		// Grava Log de envio efetivação da Carta de Correção
		$MLog->descricao = "Carta de Correcao Efetuada com Sucesso";
		$MLog->insert();
		
		
		// Gravar arquivo backup de nota autorizada
		$CBackup = new CBackup($this->grupo);
		$retBkp = $CBackup->mGuardarXml($retorno['xml'],$this->chave, $this->NOTA_FISCAL_cnpj_emitente, 'cc');
		if(!$retBkp){
//			echo $retBkp->mensagemErro;
		}

	}
	
	/*  */
/**
 * @method mObter
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mImprimirCC(){
	// Instancia Classe Model Nota Fiscal - Obter dados da Nota Fiscal
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->cnpj_emitente	= $this->NOTA_FISCAL_cnpj_emitente;
		$MNotaFiscal->numero_nota	= $this->NOTA_FISCAL_numero_nota;
		$MNotaFiscal->serie_nota	= $this->NOTA_FISCAL_serie_nota;
		$MNotaFiscal->ambiente		= $this->NOTA_FISCAL_ambiente;

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
						FROM nfe_".$this->grupo.".`NOTA_FISCAL` WHERE 	cnpj_emitente = '".$this->NOTA_FISCAL_cnpj_emitente."' AND
													numero_nota   = '".$this->NOTA_FISCAL_numero_nota."' AND
													serie_nota    = '".$this->NOTA_FISCAL_serie_nota."' AND
													ambiente      = '".$this->NOTA_FISCAL_ambiente."'";

		$fieldsNF = $MNotaFiscal->selectAllMestre($sql);
		if(!$fieldsNF){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return $fieldsNF;
		}

	// Obter informações da Carta de Correção
		$fieldsCC = $this->mObter();
		$qtde = count($fieldsCC);
		$fieldsCC = $fieldsCC[$qtde-1];
		
		// Instanciar o XML completo da NFE
		$dom = new DOMDocument('1.0', 'utf-8');
        $dom->preservWhiteSpace	= false; //elimina espaços em branco
        $dom->formatOutput 		= false;
        $dom->loadXML(base64_decode($fieldsNF[0]['xml']));

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
        $dup        = $dom->getElementsByTagName("dup");
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
		$CContribuinte = new CContribuinte($this->grupo);
		$CContribuinte->cnpj = $emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
		$fieldsContribuinte = $CContribuinte->mObterContribuinte();

		$arrayIRXML['detail'][$seq]['record']['sequencia'] 	= $qtde;
		$arrayIRXML['detail'][$seq]['record']['nNF'] 		= $ide->getElementsByTagName("nNF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['serie'] 		= $ide->getElementsByTagName("serie")->item(0)->nodeValue;
		$this->chave = str_replace('NFe', '', $infNFe->getAttribute("Id"));
		$arrayIRXML['detail'][$seq]['record']['id'] 		= implode(" ",str_split($this->chave,4));
		$arrayIRXML['detail'][$seq]['record']['codbar'] 	= $this->chave;
		$arrayIRXML['detail'][$seq]['record']['protNFe'] 	= $fieldsCC['protocolo'];
		
		
		// Instanciar o XML completo de retorno da CC
		$domCC = new DOMDocument('1.0', 'utf-8');
        $domCC->preservWhiteSpace	= false; //elimina espaços em branco
        $domCC->formatOutput 		= false;
        $domCC->loadXML(base64_decode($fieldsCC['xml_ret']));
		$infEvento = $domCC->getElementsByTagName("infEvento")->item(0);

		$arrayIRXML['detail'][$seq]['record']['evento-orgao'] 		= $domCC->getElementsByTagName("cOrgao")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['evento-descricao'] 	= $domCC->getElementsByTagName("tpEvento")->item(0)->nodeValue." - ".$domCC->getElementsByTagName("xEvento")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['evento-data-hora'] 	= date("d/m/Y h:i:s", strtotime($domCC->getElementsByTagName("dhRegEvento")->item(0)->nodeValue));
		$arrayIRXML['detail'][$seq]['record']['evento-status'] 		= $infEvento->getElementsByTagName("cStat")->item(0)->nodeValue." - ".$infEvento->getElementsByTagName("xMotivo")->item(0)->nodeValue;

	// Dados do Emitente / Remetente
		if(trim($fieldsContribuinte[0]['danfe_logo_caminho']) != ""){
			$arrayIRXML['detail'][$seq]['record']['logomarca'] = trim($fieldsContribuinte[0]['danfe_logo_caminho']);
		}else{
			$arrayIRXML['detail'][$seq]['record']['logomarca'] = "logoSoftdibDanfe.jpg";
		}
		
		$arrayIRXML['detail'][$seq]['record']['emit-xNome'] 	= $emit->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['emit-xLgr-nro'] 	= $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderEmit->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['emit-xBairro'] 	= $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['emit-xMun-UF'] 	= $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['emit-CEP'] 		= "CEP: ".$this->mask($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
		$arrayIRXML['detail'][$seq]['record']['emit-fone']		= "Fone: ".ltrim($this->mask($enderEmit->getElementsByTagName("fone")->item(0)->nodeValue, "(##) ####-#####"),0);
		$arrayIRXML['detail'][$seq]['record']['emit-IE'] 		= $emit->getElementsByTagName("IE")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['emit-CNPJ'] 		= $this->mask($emit->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
		
	// Dados do Destinatário
		$arrayIRXML['detail'][$seq]['record']['dest-xNome'] 	= $this->nome_destinatario = $dest->getElementsByTagName("xNome")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['dest-CNPJ'] 		= $this->mask($dest->getElementsByTagName("CNPJ")->item(0)->nodeValue,"##.###.###/####-##");
		$arrayIRXML['detail'][$seq]['record']['dest-xLgr']		= $enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue.", ".$enderDest->getElementsByTagName("nro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['dest-xBairro']	= $enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['dest-CEP']		= $this->mask($enderDest->getElementsByTagName("CEP")->item(0)->nodeValue, "##.###-###");
		$arrayIRXML['detail'][$seq]['record']['dest-cMun']		= $enderDest->getElementsByTagName("xMun")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['dest-fone']		= ltrim($this->mask(ltrim($enderDest->getElementsByTagName("fone")->item(0)->nodeValue,0), "(##) ####-#####"),0);
		$arrayIRXML['detail'][$seq]['record']['dest-UF']		= $enderDest->getElementsByTagName("UF")->item(0)->nodeValue;
		$arrayIRXML['detail'][$seq]['record']['dest-IE']		= $dest->getElementsByTagName("IE")->item(0)->nodeValue;

		$arrayIRXML['detail'][$seq]['record']['descricao']		= $fieldsCC['descricao'];

		// Footer do relatorio
		$arrayIRXML['footer'] = " ";

		$xmlgen = new xmlgen();
		$xmlJUGIRXML = $xmlgen->generate('relatorio',$arrayIRXML);
		
		// Gravar em arquivo temporario no /user/relatorios/ apenas para chamar JUGIRXML
		if(!file_put_contents("/var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".xml",$xmlJUGIRXML)){
			$this->mensagemErro = "Impossivel criar arquivo temporario"; return false;
		}
		// Chamar JUIRXML para gerar relatorio
		if(file_exists("/user/imagens/xml/CC-RH-MODELO-".$this->grupo.".jrxml")){
			if(system("cd /var/www/html/relatorios/; php -q /user/objetos/JUGIRXML.php /var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".xml /user/imagens/xml/CC-RH-MODELO-".$this->grupo.".jrxml /var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".pdf") != ""){
				$this->mensagemErro = "Nao foi possivel criar o PDF da DANFE";
				return false;
			}
		}else{
			if(system("cd /var/www/html/relatorios/; php -q /user/objetos/JUGIRXML.php /var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".xml /user/imagens/xml/CC-RH-MODELO.jrxml /var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".pdf") != ""){
				$this->mensagemErro = "Nao foi possivel criar o PDF da DANFE";
				return false;
			}	
		}

		// Remove o XML temporario do /user/relatorio que fora criado apenas para converter em PDF
		/*if(!unlink("/var/www/html/relatorios/NFE_".$this->chave."_CC_".$qtde.".xml")){
			$this->mensagemErro = "Nao foi possivel remover o XML temporario";
		}*/

		$this->pdfCC = "NFE_".$this->chave."_CC_".$qtde.".pdf";
		return true;
	}

	public function mEnviarEmailCC(){
		$CContribuinte = new CContribuinte($this->grupo);
		$CContribuinte->cnpj = $this->NOTA_FISCAL_cnpj_emitente;
		$fieldsCont = $CContribuinte->mObterContribuinte();
		if(!$fieldsCont){
			$this->mensagmErro = $CContribuinte->mensagemErro;
			return false;
		}

		$fieldsCC = $this->mObter();

		if(!$fieldsCC){ return false; }
		$fieldsCC = $fieldsCC[count($fieldsCC)-1];
		
		$CEmail = new CEmail($this->grupo);

		$CEmail->arrayConteudoResult['nome_destinatario'] = $this->email;
		$CEmail->arrayConteudoResult['NOTA_FISCAL_numero_nota'] = $this->NOTA_FISCAL_numero_nota;
		$CEmail->arrayConteudoResult['NOTA_FISCAL_serie_nota'] = $this->NOTA_FISCAL_serie_nota;
		$CEmail->arrayConteudoResult['chave'] = '';
		$CEmail->arrayConteudoResult['protocolo'] = $fieldsCC['protocolo'];
		$CEmail->arrayConteudoResult['valor_total'] = '';
		$CEmail->arrayConteudoResult['numero_sequencia'] = $fieldsCC['numero_sequencia'];
		$CEmail->arrayConteudoResult['descricao'] = $fieldsCC['descricao'];
		$CEmail->ssl 			= $fieldsCont[0]['email_ssl'];
		$CEmail->porta 			= $fieldsCont[0]['email_porta'];
		$CEmail->smtp 			= $fieldsCont[0]['email_smtp'];
		$CEmail->usuario 		= $fieldsCont[0]['email_usuario'];
		$CEmail->senha 			= $fieldsCont[0]['email_senha'];
		$CEmail->destinatario 	= $this->email;
		if($this->mImprimirCC()){
			$CEmail->attach = "/var/www/html/relatorios/".$this->pdfCC;
		}
		
		$numTmp = substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15);
		file_put_contents("/var/www/html/nf/nfe/novo/temp/".$numTmp."-CC.xml",base64_decode($fieldsCC['xml']));
		$CEmail->attach2 = "/var/www/html/nf/nfe/novo/temp/".$numTmp."-CC.xml";

		if(!$CEmail->emailCC()){
			$this->mensagemErro = $CEmail->mensagemErro;
			return false;
		}
		unlink("/var/www/html/nf/nfe/novo/temp/".$numTmp."-CC.xml");
		return true;
	}
	
	private function __mAtualizarLote($pLote){
		// Atualizar Lote na base de dados
		$MLote = new MLote($this->grupo); 
		
		$MLote->cnpj_emitente 	= $this->NOTA_FISCAL_cnpj_emitente;
		$MLote->ambiente 		= $this->NOTA_FISCAL_ambiente;
		$MLote->id 				= $pLote;
		$MLote->recibo 			= "";
		$MLote->status 			= $this->statusLote;

		$return = $MLote->update();
		if(!$return || $return == null){
			$this->mensagemErro = $MLote->mensagemErro;
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
?>