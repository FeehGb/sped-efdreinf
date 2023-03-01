	<?php
/**
 * @name      	CIntegrarTerceiros.php
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para tratar a comunicação do envio e retorno para o erp
*/

/**
 * @import 
 */ 
	require_once("../model/MContribuinte.php");
	require_once("../model/MNotaFiscal.php");
	require_once("../model/MInutilizacao.php");
	require_once("../model/MEvento.php");
	require_once("../model/MLog.php");
	require_once("../model/MCritica.php");
	require_once("../libs/ConvertNFePHP.class.php");
	require_once("../libs/ToolsNFePHP.class.php");

/**
 * @class CIntegracaoTerceiros
 */ 
class CIntegrarTerceiros{

/*
 * Atributos da Classe
 */
	
	public $mensagemErro = "";	
	
	private $grupo;
	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
/**
 * @method 	mIntegrarTerceiros
 * @autor 	Guilherme Silva
 * @TODO  	Fazer de tudo
 */
	public function mIntegrarTerceiros(){

		// Selecionar Todos os Contribuintes para obter os caminhos de integracao
		$MContribuinte = new MContribuinte($this->grupo);
		$MContribuinte->ativo = "S";
		$arrayCanc 			= array();
		$arrayCC 			= array();
		$arrayCancEvento	= array();

		$retorno = $MContribuinte->selectAll();
		if(!$retorno){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}

		// Varrer todos os contribuintes da respectiva base (grupo)
		foreach($retorno as $contribuintes){
			// Obter o diretorio de integração
			if($contribuintes['diretorio_importacao'] != ""){
				if(substr($contribuintes['diretorio_importacao'],-1) != "/"){
					$contribuintes['diretorio_importacao'] = $contribuintes['diretorio_importacao']."/";
				}
				$arrayArquivos = $this->__NormalizarDiretorios($contribuintes['diretorio_importacao']);
				// Ler todos os Arquivos do Diretório de Integração
				if(!file_exists($contribuintes['diretorio_backup']."importadas/")){
					mkdir($contribuintes['diretorio_backup']."importadas/",0777, true);
				}

				foreach($arrayArquivos as $arquivo){
					$stringXML = file_get_contents($contribuintes['diretorio_importacao'].$arquivo);

					switch($this->__TipoArquivo($stringXML)){
						case "nfeProc";
							if(!$this->__InserirNFe($stringXML, $contribuintes)){
								//gravar log de erro e continuar
							}
						break;
						case "procInutNFe";
							if(!$this->__InserirInutilizacao($stringXML, $contribuintes)){
								//gravar log de erro e continuar
							}
						break;
						case "procCancNFe";
							// Fazer depois de importar todas as notas pois pode haver cancelamento de notas ainda não importadas
							$arrayCanc[] = array("string" => $stringXML, "contribuinte" => $contribuintes);
						break;
						// Pode ser inutilizacao / carta de correcao / cancelamento
						case "procEventoCC";
							// Fazer depois de importar todas as notas pois pode haver cancelamento de notas ainda não importadass
							$arrayCC[] = array("string" => $stringXML, "contribuinte" => $contribuintes);
						break;
						case "procEventoCanc":
							// Fazer depois de importar todas as notas pois pode haver cancelamento de notas ainda não importadas
							$arrayCancEvento[] = array("string" => $stringXML, "contribuinte" => $contribuintes);
						break;
						case false:
							//gravar log de erro de integracao é outro arquivo estranho
						break;
					}
					rename($contribuintes['diretorio_importacao'].$arquivo, $contribuintes['diretorio_backup']."importadas/".$arquivo);
				}
			}
		}

		// Agora fazer a Carta de Correção das notas
		foreach($arrayCC as $regCC){
			if(!$this->__InserirEventoCC($regCC['string'],$regCC['contribuinte'])){
				//gravar log de erro e continuar
			}
		}
		// Agora fazer o cancelamento das notas
		foreach($arrayCanc as $regCanc){
			if(!$this->__InserirCancNFe($regCanc['string'],$regCanc['contribuinte'])){
				//gravar log de erro e continuar
			}
		}

		// Agora fazer o cancelamento das notas
		foreach($arrayCancEvento as $regCanc){
			if(!$this->__InserirCancEventoNFe($regCanc['string'],$regCanc['contribuinte'])){
				//gravar log de erro e continuar
			}
		}

	}

	/*
	* Método __NormalizarDiretorios 
	* Irá mover todos os arquivos das subpastas para dentro da pasta original através da recursividade
	* retorno através de Array com os arquivos no diretório raiz
	*/
	private function __NormalizarDiretorios($pDirRaiz="", $pSubDir=""){
		$array = array();

		if($pDirRaiz==""){
			$this->mensagemErro =  "CIntegrarTerceiros -> __NormalizarDiretorios: deve ser informado um diretorio";
			return false;
		}
		if($pSubDir == ""){
			$dir = dir($pDirRaiz);
		}else{
			$dir = dir($pDirRaiz.$pSubDir);
		}

		// Listar arquivos / diretórios da pasta
		while($arquivoDir = $dir->read()){
			if(is_file($pDirRaiz.$pSubDir.$arquivoDir)){
				$array[] = $arquivoDir;
				if($pSubDir != ""){
					if(!rename($pDirRaiz.$pSubDir.$arquivoDir, $pDirRaiz.$arquivoDir)){
						$this->mensagemErro = "CIntegrarTerceiros -> __NormalizarDiretorios: nao foi possivel mover (".$pDirRaiz.$pSubDir.$arquivoDir.") do subdiretorio para pasta raiz, verifique permissoes";
						return false;
					}
				}
			}elseif(is_dir($pDirRaiz.$pSubDir.$arquivoDir)){
				if($arquivoDir == ".." || $arquivoDir == "."){
					continue;
				}
				// Chamar recursivo pra obter os proximos arquivos dos diretorios
				$result = $this->__NormalizarDiretorios($pDirRaiz,$pSubDir.$arquivoDir."/");
				if($result === false){
					return false;
				}
				if(is_array($result)){
					$array = array_merge($array,$result);
				}
				// Apagar os diretorios vazios
				rmdir($pDirRaiz.$pSubDir.$arquivoDir."/");
			}else{
				$this->mensagemErro = "CIntegrarTerceiros -> __NormalizarDiretorios: nao foi possivel verificar a origem do arquivo na pasta de integracao";
				return false;
			}
		}
		$dir->close();
		return $array;
	}

	/*
	* Método privado para Integrar Notas Fiscais de Cancelamento
	*/
	private function __IntegrarNotaCancelamento($pDir="",$pArq="", $pArrayContribuinte=""){
		if($pDir == ""|| $pArq == "" || $pArrayContribuinte == ""){
			$this->mensagemErro = "CIntegracaoTerceiros -> __IntegrarNotaCancelamento() { Parametros obrigatorios: arquivo, arrayContribuinte }";
			return false;
		}
		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = "CIntegracaoTerceiros -> __IntegrarNotaCancelamento() { O parametro não é um arquivo }";
			return false;
		}
		
		// Move o arquivo da pasta Processar para Processado
		if(!$this->__MoverProcessarProcessado($pDir,$pArq)){
			return false;
		}

		$localArquivo = str_replace("Processar","Processado",$pDir).$pArq;

		if(!$arqCanc = file_get_contents($localArquivo)){
			// GRAVAR CRITICA
			// GRAVAR ARQUIVO DE RETORNO CANCELAMENTO
			// GRAVAR LOG
			return false;
		}

		$arqCanc = explode("|",$arqCanc);
		$arquivo['cnpj']		= $arqCanc[0];
		$arquivo['destinatario']= $arqCanc[1];
		$arquivo['dtEmissao'] 	= $arqCanc[2];
		$arquivo['modelo'] 		= $arqCanc[3];
		$arquivo['serie'] 		= $arqCanc[4];
		$arquivo['nota'] 		= $arqCanc[5];
		$arquivo['status']		= $arqCanc[6];
		$arquivo['justficativa']= $arqCanc[7];
		$arquivo['mensagem']	= "";
		
		// Verificar se Nota existe / está cancelada
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->cnpj_emitente = $arquivo['cnpj'];
		$MNotaFiscal->ambiente 		= $arquivo['ambiente'];
		$MNotaFiscal->numero_nota 	= $arquivo['nota'];
		$MNotaFiscal->serie_nota 	= $arquivo['serie'];
		$retornoNF = $MNotaFiscal->selectAllMestre();
		if(!$retornoNF){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			$arquivo['mensagem'] = $MNotaFiscal->mensagemErro;
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			return false;
		}
		
		// Nota já Cancelada
		if($retornoNF[0]['status'] == "06"){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." ja foi cancelada, status 06";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			// GRAVAR LOG DE REGISTRO
			return true;
		// Nota Fiscal não existe
		}elseif(empty($retornoNF[0]['status'])){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." nao existe na base de dados";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			return true;
		}
		
		// Verifica se há evento de cancelamento
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente	= $arquivo['cnpj'];
		$MEvento->NOTA_FISCAL_numero_nota	= $arquivo['nota'];
		$MEvento->NOTA_FISCAL_serie_nota	= $arquivo['serie'];
		$MEvento->NOTA_FISCAL_ambiente		= $arquivo['ambiente'];
		$retorno = $MEvento->selectMestre();
		if(!$retorno){
		  $arquivo['mensagem'] = $this->mensagemErro = $MEvento->mensagemErro;
		  $this->__mArqRetornoCanc($arquivo, $localArquivo, false);
		  return true;
		}
		
		// Identificado que já existe evento de cancelamento para esta nota
		if(!empty($retorno)){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." ja foi cancelada, verificar Evento";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			// TODO GRAVAR LOG DE REGISTRO
			return true;
		}

		// Verifica se o SEFAZ está com o Status Inativo
		if(!__ConsultarStatusSEFAZ($arquivo['cnpj'], $arquivo['ambiente'])){
			// Verifica se com o SEFAZ inativo e não está em modo contingência SCAN ou série 900
			if(  !  ($pArrayContribuinte['contigencia'] == "03" ||
				(substr($arquivo['serie'],0,1) == "9" && strlen($arquivo['serie']) == "3"))){
				// TODO GRAVAR ARQUIVO DE RETORNO REJEITADO
				// TODO GRAVAR LOG DE REJEITADO
			}
		}

		// Verifica se está ativa contingencia sem informar um número de protocolo para a nota
		if( ! ($retornoNF[0]['status'] == "03" && $retornoNF[0]['numero_protocolo'] != "")){
		  // Verifica se o status é diferente de Contingencia SCAN 03 e Status da Nota Aguardando 02
		  if( ! ($pArrayContribuinte['contigencia'] == "03" && $retornoNF[0]['status'] == "02")){
			// TODO grava arquivo de rejeitado e LOG de rejeitado
		  }
		}

		// Verifica se há evento de cancelamento
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente	= $arquivo['cnpj'];
		$MEvento->NOTA_FISCAL_numero_nota	= $arquivo['nota'];
		$MEvento->NOTA_FISCAL_serie_nota	= $arquivo['serie'];
		$MEvento->NOTA_FISCAL_ambiente		= $arquivo['ambiente'];
		$MEvento->tipo_evento				= "4";
		$MEvento->numero_sequencia			= "1";
		$MEvento->xml_env					= "";
		$MEvento->xml						= "";
		$MEvento->descricao					= "";
		$MEvento->protocolo					= "";
		$MEvento->data_hora					= date("Y-m-d H:i:s");
		$MEvento->status					= "";
		$MEvento->email_enviado				= "N";
		$retorno = $MEvento->insert();
		if(!$retorno){
			$arquivo['mensagem'] = $this->mensagemErro = $MEvento->mensagemErro;
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			return true;
		}
		$arquivo['mensagem'] = "Integracao efetuada com sucesso";
		$this->__mArqRetornoCanc($arquivo, $localArquivo, true);
		return true;
	}
	
	/*
- Considerar apenas arquivos com inicio:
				* <?xml version="1.0" encoding="UTF-8"?><nfeProc - nfe autorizada
				* <?xml version="1.0" encoding="UTF-8"?><procInutNFe - nfe inutilizada (envio normal, SCAN, SVC, DPEC, etc).
				* <?xml version="1.0" encoding="UTF-8"?><procCancNFe - nfe cancelada
				* <?xml version="1.0" encoding="UTF-8"?><procEventoNFe - nfe evento (carta correcao)
				* Se tiver arquivo que comece diferente disto eliminar o arquivo e informar no log que foi eliminado
				Cada integração bem sucedida informar sucesso ou erro no log.
		
*/		
	private function __TipoArquivo($pStringXml){
		if(	strpos($pStringXml, "<nfeProc") > 0 &&
			strpos($pStringXml, "</nfeProc>") > 0 ){
				return "nfeProc";
		}
		
		if(	strpos($pStringXml, "<procInutNFe") > 0 &&
			strpos($pStringXml, "</procInutNFe>") > 0 ){
				return "procInutNFe";
		}
		
/*		if(	strpos($pStringXml, "<retInutNFe") > 0 &&
			strpos($pStringXml, "</retInutNFe>") > 0 ){
				return "procInutNFe";
		}*/
		
		if(	strpos($pStringXml, "<procCancNFe") > 0 &&
			strpos($pStringXml, "</procCancNFe>") > 0 ){
				return "procCancNFe";
		}
		
		if(	strpos($pStringXml, "<procEventoNFe") > 0 &&
			strpos($pStringXml, "</procEventoNFe>") > 0 ){
				if(	strpos($pStringXml, "<tpEvento>110111</tpEvento>") > 0 ){
					return "procEventoCanc";
				}
				return "procEventoCC";
		}
		
		return false;
	}
	
	/*
	* Método privado para importar as notas fiscais da integração por terceiros
	*/
	private function __InserirNFe($pStringXml, $pArrayContribuinte){
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;
		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($pStringXml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirNfe: nao foi possivel ler o XML";
			return false;
		}
		
		$emitente 		= $doc->getElementsByTagName('emit')->item(0);
		$destinatario 	= $doc->getElementsByTagName('dest')->item(0);
		
		if($pArrayContribuinte['cnpj'] != $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirNFe: O CNPJ do emissor não pertence a este contribuinte cadastrado";
			return false;
		}
		// Irá importar apenas as notas emitidas pelo contribuinte da base
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		  
		$MNotaFiscal->cnpj_emitente		= $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		$MNotaFiscal->numero_nota		= $doc->getElementsByTagName('nNF')->item(0)->nodeValue;
		$MNotaFiscal->serie_nota		= $doc->getElementsByTagName('serie')->item(0)->nodeValue;
		if($doc->getElementsByTagName('serie')->item(0)->nodeValue == "2"){
			$MNotaFiscal->ambiente = "0";
		}else{
			$MNotaFiscal->ambiente = $doc->getElementsByTagName('serie')->item(0)->nodeValue;
		}

		$MNotaFiscal->versao			= $doc->getElementsByTagName('infNFe')->item(0)->getAttribute('versao');
		$MNotaFiscal->cod_empresa_filial_softdib = $pArrayContribuinte['cod_emp_fil_softdib'];
		$MNotaFiscal->nome_emissor		= $emitente->getElementsByTagName('xNome')->item(0)->nodeValue;

		if(isset($destinatario->getElementsByTagName('CNPJ')->item(0)->nodeValue)){
			$MNotaFiscal->cnpj_destinatario	= @$destinatario->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		}elseif(isset($destinatario->getElementsByTagName('CPF')->item(0)->nodeValue)){
			$MNotaFiscal->cnpj_destinatario	= @$destinatario->getElementsByTagName('CPF')->item(0)->nodeValue;
		}
		
		$MNotaFiscal->nome_destinatario	= $destinatario->getElementsByTagName('xNome')->item(0)->nodeValue;
		$MNotaFiscal->cod_destinatario	= "";
		@$MNotaFiscal->email_destinatario = @$destinatario->getElementsByTagName('email')->item(0)->nodeValue;
		
		if($doc->getElementsByTagName('cStat')->item(0)->nodeValue == "100"){
			$MNotaFiscal->status = "03";
		}elseif($doc->getElementsByTagName('cStat')->item(0)->nodeValue == "110"){
			$MNotaFiscal->status = "05";
		}elseif($doc->getElementsByTagName('cStat')->item(0)->nodeValue == "101"){
			$MNotaFiscal->status = "06";
		}

		$MNotaFiscal->tipo_emissao		= $doc->getElementsByTagName('tpEmis')->item(0)->nodeValue;
		$MNotaFiscal->data_emissao		= $doc->getElementsByTagName('dEmi')->item(0)->nodeValue;
		$MNotaFiscal->uf_webservice		= $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
		$MNotaFiscal->layout_danfe		= $pArrayContribuinte['danfe_layout_caminho'];
		$MNotaFiscal->valor_total_nfe	= $doc->getElementsByTagName('vNF')->item(0)->nodeValue;
 		$MNotaFiscal->data_entrada_saida= $doc->getElementsByTagName('dSaiEnt')->item(0)->nodeValue." ".$doc->getElementsByTagName('hSaiEnt')->item(0)->nodeValue;
		$MNotaFiscal->chave				= str_replace("NFe","",$doc->getElementsByTagName('infNFe')->item(0)->getAttribute('Id'));
		$MNotaFiscal->numero_protocolo	= $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
		$MNotaFiscal->tipo_operacao		= $doc->getElementsByTagName('tpNF')->item(0)->nodeValue;
		$MNotaFiscal->xml				= $pStringXml;
		$MNotaFiscal->danfe_impressa	= "S";
		$MNotaFiscal->email_enviado		= "S";
		$MNotaFiscal->lote_nfe			= "";
		$MNotaFiscal->CONTRIBUINTE_cnpj	= $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		
		$retorno = $MNotaFiscal->insert();
		
		if(!$retorno){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}
	}

	/*
	* Método privado para importar as inutilizações da integração por terceiros
	*/
	private function __InserirInutilizacao($pStringXml, $pArrayContribuinte){
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;
		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($pStringXml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirNfe: nao foi possivel ler o XML";
			return false;
		}

		if($pArrayContribuinte['cnpj'] != $doc->getElementsByTagName('CNPJ')->item(0)->nodeValue){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirNFe: O CNPJ do emissor não pertence a este contribuinte cadastrado";
			return false;
		}

		$MInutilizacao = new MInutilizacao($this->grupo);

		$MInutilizacao->CONTRIBUINTE_cnpj 		= str_pad($doc->getElementsByTagName('CNPJ')->item(0)->nodeValue,14,'0',STR_PAD_LEFT);
		$MInutilizacao->CONTRIBUINTE_ambiente 	= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
		/*if($doc->getElementsByTagName('CNPJ')->item(0) == "0"){
			$MInutilizacao->CONTRIBUINTE_ambiente	= "2";
		}else{
			$MInutilizacao->CONTRIBUINTE_ambiente = str_pad($doc->getElementsByTagName('CNPJ')->item(0)->nodeValue,14,'0',STR_PAD_LEFT);
		}*/

		$retInutNFe = $doc->getElementsByTagName('retInutNFe')->item(0);

		$MInutilizacao->serie_nota 				= $doc->getElementsByTagName('serie')->item(0)->nodeValue;
		$MInutilizacao->numero_nota_inicial 	= $doc->getElementsByTagName('nNFIni')->item(0)->nodeValue;
		$MInutilizacao->numero_nota_final 		= $doc->getElementsByTagName('nNFFin')->item(0)->nodeValue;
		$MInutilizacao->justificativa 			= $doc->getElementsByTagName('xJust')->item(0)->nodeValue;
		$MInutilizacao->ano						= $doc->getElementsByTagName('ano')->item(0)->nodeValue;
		$MInutilizacao->modelo_nota 			= $doc->getElementsByTagName('mod')->item(0)->nodeValue;
		$MInutilizacao->protocolo 				= $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
		$MInutilizacao->data_hora 				= str_replace("T"," ",$doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue);
		$MInutilizacao->status 					= $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
		$MInutilizacao->status_motivo 			= $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue;
		$MInutilizacao->uf_responsavel 			= $retInutNFe->getElementsByTagName('cUF')->item(0)->nodeValue;
		$MInutilizacao->xml_env 				= $pStringXml;
		$MInutilizacao->xml_ret 				= $pStringXml;

		$retorno = $MInutilizacao->insert();
		if(!$retorno){
			$MLog = new MLog();
			$this->mensagemErro = $MInutilizacao->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}
	}
	
	/*
	* Método privado para importar as inutilizações da integração por terceiros
	*/
	private function __InserirCancNFe($pStringXml, $pArrayContribuinte){
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;

		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($pStringXml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirNfe: nao foi possivel ler o XML";
			return false;
		}

		// Verificar se a chave da nota corresponde a nota já autorizada no banco.
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->chave = $doc->getElementsByTagName('chNFe')->item(0)->nodeValue;
		$return = $MNotaFiscal->selectAllMestre();

		if(!is_array($return)){
			$this->mensagemErro = "CIntegrarTerceiros.php ->__InserirCancNFe: nao foi encontrada nota fiscal a ser cancelada";
			return false;
		}

	// MONTA EVENTO NA BASE DE DADOS PARA CANCELAMENTO
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente		= $return[0]['cnpj_emitente'];
		$MEvento->NOTA_FISCAL_numero_nota		= $return[0]['numero_nota'];
		$MEvento->NOTA_FISCAL_serie_nota		= $return[0]['serie_nota'];
		$MEvento->NOTA_FISCAL_ambiente			= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
		$MEvento->tipo_evento					= "4";
		$MEvento->numero_sequencia				= "";
		$MEvento->xml_env						= $pStringXml;
		$MEvento->xml							= $pStringXml;
		$MEvento->xml_ret						= $pStringXml;
		$MEvento->descricao						= $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue;
		$MEvento->protocolo						= $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
		$MEvento->data_hora						= str_replace("T"," ",$doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue);
		$MEvento->status						= $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
		$MEvento->email_enviado					= "S";
		
		$retornoEvento = $MEvento->insert();
		
		if(!$retornoEvento){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $return[0]['cnpj_emitente'];
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $return[0]['numero_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $return[0]['serie_nota'];
			$MCritica->codigo_referencia				= $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
			$MCritica->descricao		                = $MEvento->mensagemErro;
			$MCritica->insert();
		}

		// Atualizar Nota Fiscal para 6 - Status cancelada
		$MNotaFiscal->cnpj_emitente = $return[0]['cnpj_emitente'];
		$MNotaFiscal->numero_nota	= $return[0]['numero_nota'];
		$MNotaFiscal->serie_nota	= $return[0]['serie_nota'];
		$MNotaFiscal->ambiente		= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
		$MNotaFiscal->status		= "06";
		if(!$MNotaFiscal->update()){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $return[0]['cnpj_emitente'];
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $return[0]['numero_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $return[0]['serie_nota'];
			$MCritica->codigo_referencia				= $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
			$MCritica->descricao		                = $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue;
			$MCritica->insert();
		}
	}
	
	/*
	* Método privado para importar o cancelamento via evento
	*/
	private function __InserirCancEventoNFe($pStringXml, $pArrayContribuinte){
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;

		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($pStringXml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirCancEventoNFe: nao foi possivel ler o XML";
			return false;
		}

		// Verificar se a chave da nota corresponde a nota já autorizada no banco.
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->chave = $doc->getElementsByTagName('chNFe')->item(0)->nodeValue;
		$return = $MNotaFiscal->selectAllMestre();

		if(!is_array($return)){
			$this->mensagemErro = "CIntegrarTerceiros.php ->__InserirCancEventoNFe: nao foi encontrada nota fiscal a ser cancelada";
			return false;
		}

		$retEvento	= $doc->getElementsByTagName("retEvento")->item(0);

	// MONTA EVENTO NA BASE DE DADOS PARA CANCELAMENTO
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente		= $return[0]['cnpj_emitente'];
		$MEvento->NOTA_FISCAL_numero_nota		= $return[0]['numero_nota'];
		$MEvento->NOTA_FISCAL_serie_nota		= $return[0]['serie_nota'];
		$MEvento->NOTA_FISCAL_ambiente			= $retEvento->getElementsByTagName("tpAmb")->item(0)->nodeValue;
		$MEvento->tipo_evento					= "4";
		$MEvento->numero_sequencia				= "1";
		$MEvento->xml_env						= $pStringXml;
		$MEvento->xml							= $pStringXml;
		$MEvento->xml_ret						= $pStringXml;
		$MEvento->descricao						= $doc->getElementsByTagName('xJust')->item(0)->nodeValue;
		$MEvento->protocolo						= $retEvento->getElementsByTagName('nProt')->item(0)->nodeValue;
		$MEvento->data_hora						= str_replace("T"," ",$retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue);
		$MEvento->status						= $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue;
		$MEvento->email_enviado					= "S";
		
		if(!$MEvento->insert()){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $return[0]['cnpj_emitente'];
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $return[0]['numero_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $return[0]['serie_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $retEvento->getElementsByTagName("tpAmb")->item(0)->nodeValue;
			$MCritica->codigo_referencia				= $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue;
			$MCritica->descricao		                = $MEvento->mensagemErro;
			$MCritica->insert();
		}
		// Atualizar Nota Fiscal para 6 - Status cancelada
		$MNotaFiscal->cnpj_emitente = $return[0]['cnpj_emitente'];
		$MNotaFiscal->numero_nota	= $return[0]['numero_nota'];
		$MNotaFiscal->serie_nota	= $return[0]['serie_nota'];
		$MNotaFiscal->ambiente		= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
		$MNotaFiscal->status		= "06";
		if(!$MNotaFiscal->update()){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $return[0]['cnpj_emitente'];
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $return[0]['numero_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $return[0]['serie_nota'];
			$MCritica->codigo_referencia				= $doc->getElementsByTagName('cStat')->item(0)->nodeValue;
			$MCritica->descricao		                = $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue;
			$MCritica->insert();
		}
	}
	
	/*
	* Método privado para importar as inutilizações da integração por terceiros
	*/
	private function __InserirEventoCC($pStringXml, $pArrayContribuinte){
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;

		$doc->preserveWhiteSpace = false;
		if(!$doc->loadXML($pStringXml,LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)){
			$this->mensagemErro = "CIntegrarTerceiros -> __InserirCancEventoNFe: nao foi possivel ler o XML";
			return false;
		}

		// Verificar se a chave da nota corresponde a nota já autorizada no banco.
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->chave = $doc->getElementsByTagName('chNFe')->item(0)->nodeValue;
		$return = $MNotaFiscal->selectAllMestre();

		if(!is_array($return)){
			$this->mensagemErro = "CIntegrarTerceiros.php ->__InserirCancEventoNFe: nao foi encontrada nota fiscal a ser cancelada";
			return false;
		}

		$retEvento	= $doc->getElementsByTagName("retEvento")->item(0);

	// MONTA EVENTO NA BASE DE DADOS PARA CANCELAMENTO
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente		= $return[0]['cnpj_emitente'];
		$MEvento->NOTA_FISCAL_numero_nota		= $return[0]['numero_nota'];
		$MEvento->NOTA_FISCAL_serie_nota		= $return[0]['serie_nota'];
		$MEvento->NOTA_FISCAL_ambiente			= $retEvento->getElementsByTagName("tpAmb")->item(0)->nodeValue;
		$MEvento->tipo_evento					= "6";
		$MEvento->numero_sequencia				= $retEvento->getElementsByTagName("nSeqEvento")->item(0)->nodeValue;
		$MEvento->xml_env						= $pStringXml;
		$MEvento->xml							= $pStringXml;
		$MEvento->xml_ret						= $pStringXml;
		$MEvento->descricao						= $doc->getElementsByTagName('xCorrecao')->item(0)->nodeValue;
		$MEvento->protocolo						= $retEvento->getElementsByTagName('nProt')->item(0)->nodeValue;
		$MEvento->data_hora						= str_replace("T"," ",$retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue);
		$MEvento->status						= $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue;
		$MEvento->email_enviado					= "S";
		
		$retornoEvento = $MEvento->insert();
		
		if(!$retornoEvento){
			$MCritica = new MCritica($this->grupo);
			$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $return[0]['cnpj_emitente'];
			$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $return[0]['numero_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $return[0]['serie_nota'];
			$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $retEvento->getElementsByTagName("tpAmb")->item(0)->nodeValue;
			$MCritica->codigo_referencia				= $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue;
			$MCritica->descricao		                = $MEvento->mensagemErro;
			$MCritica->insert();
		}
		// Nao ha necesidade de atualizacao da nota
	}
	
}
?>