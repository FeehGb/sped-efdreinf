<?php
/**
 * @name      	CIntegracaoERP
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para tratar a comunicaзгo do envio e retorno para o erp
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
 * @class CIntegracaoERP
 */ 
class CIntegracaoERP{

/*
 * Atributos da Classe
 */
	
	public $mensagemErro = "";	
	public $contribuinteBase = "";
	private $grupo;
	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
/**
 * @method 	mRetornoInutilizacao
 * @autor 	Guilherme Silva
 * @TODO  	Fazer de tudo
 */
	public function mRetornoInutilizacao($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estгo setados
		if($pArray == ""){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoInutilizacao (parametros de entrada obrigatorios)";
			return false;
		}
		
		if(	!isset($pArray['cnpj_emitente']) 		||
			!isset($pArray['uf_emitente']) 			||
			!isset($pArray['ano']) 					||
			!isset($pArray['modelo_nota']) 			||
			!isset($pArray['serie_nota']) 			||
			!isset($pArray['numero_nota_inicial']) 	||
			!isset($pArray['numero_nota_final']) 	||
			!isset($pArray['status']) 				||
			!isset($pArray['descricao_status']) 	||
			!isset($pArray['data_hora']) 			||
			!isset($pArray['protocolo']) 			||
			!isset($pArray['uf_ibge_responsavel']) ){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoInutilizacao (parametros de entrada obrigatorios: cnpj, uf, ano, modelo_nota, serie, numeracao inicial, numeracao final, status, descricao status, data/hora, uf responsavel)";
			return false;
		}

		$data_hora_arq = $dataHora;
		$data_hora_arq = str_replace("-",".",$data_hora_arq); // Trocar string - por .
		$data_hora_arq = str_replace("T","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(" ","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .
		
		$nomeArquivo  = "INFER_";
		$nomeArquivo .= $data_hora_arq;
		$nomeArquivo .= ".txt";
		
		$corpoArquivo = implode("|",$pArray);
		
		if(!file_put_contents($pDiretorio."/CaixaSaida/Processar/".$nomeArquivo, $corpoArquivo)){
			$this->mensagemErro = "Erro ao gravar arquivo de Integracao com ERP, verifique permissoes!";
			return false;
		}

		$this->mChamarCobol();
		return true;
	}
	
	
	public function mRetornoConsulta($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estгo setados
		if($pArray == ""){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios)";
			return false;
		}
		if(	!isset($pArray['cnpj_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: cnpj)";
			return false;
		}
		/*if(	!isset($pArray['uf_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: uf)";
			return false;
		}*/
		if(	!isset($pArray['ano_mes'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Ano/Mes)";
			return false;
		}
		if(	!isset($pArray['modelo_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Modelo da Nota)";
			return false;
		}
		if(	!isset($pArray['serie_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Serie da Nota)";
			return false;
		}
		if(	!isset($pArray['numero_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Numero da Nota)";
			return false;
		}
		if(	!isset($pArray['serie_nota_con'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Serie Nota Contingencia)";
			return false;
		}
		if(	!isset($pArray['numero_nota_con'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Nota Contingencia)";
			return false;
		}
		if(	!isset($pArray['status'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Status)";
			return false;
		}
		if(	!isset($pArray['chave'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: Chave)";
			return false;
		}

		$data_hora_arq = $dataHora;
		$data_hora_arq = str_replace("-",".",$data_hora_arq); // Trocar string - por .
		$data_hora_arq = str_replace("T","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(" ","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .		
		$data_hora_arq = str_replace(" ","_",$data_hora_arq); // Trocar string : por .		
		$nomeArquivo  = "NFER_";
		$nomeArquivo .= $data_hora_arq;
		$nomeArquivo .= ".txt";
		
		$corpoArquivo = implode("|",$pArray);
		if(!file_put_contents($pDiretorio."/CaixaSaida/Processar/".$nomeArquivo, $corpoArquivo)){
			$this->mensagemErro = "Erro ao gravar arquivo de Integracao com ERP, verifique permissoes!";
			return false;
		}
		$this->mChamarCobol();
		return true;
	}
	/*
	* Mйtodo para Retornor para o Cobol o arquivo de cancelamento
	*/
	public function mRetornoCancelamento($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estгo setados
		if($pArray == ""){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios)"; return false;
		}
		if(	!isset($pArray['cnpj_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: cnpj)"; return false;
		}
		if( !isset($pArray['uf_ibge_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: uf)"; return false;
		}
		if( !isset($pArray['ano_mes'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: ano+mes)"; return false;
		}
		if( !isset($pArray['modelo_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: modelo_nota)"; return false;
		}
		if( !isset($pArray['serie_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: serie)"; return false;
		}
		if( !isset($pArray['numero_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: numeracao nota)"; return false;
		}
		if( !isset($pArray['serie_nota_con'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: serie nota con)";return false;
		}
		if( !isset($pArray['numero_nota_con'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: numero nota con)";return false;
		}
		if( !isset($pArray['status'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: status)";return false;
		}
		if( !isset($pArray['descricao_status'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: descricao status)";return false;
		}
		if( !isset($pArray['data_hora'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: data/hora)";	return false;
		}
		if( !isset($pArray['protocolo'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: protocolo)";	return false;
		}
		if( !isset($pArray['uf_ibge_responsavel'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoCancelamento (parametros de entrada obrigatorios: uf responsavel)";return false;
		}

		$data_hora_arq = $dataHora;
		$data_hora_arq = str_replace("-",".",$data_hora_arq); // Trocar string - por .
		$data_hora_arq = str_replace("T","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(" ","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .
		
		$nomeArquivo  = "CNFER_";
		$nomeArquivo .= $data_hora_arq;
		$nomeArquivo .= ".txt";

		$corpoArquivo = implode("|",$pArray);
		if(!file_put_contents($pDiretorio."/CaixaSaida/Processar/".$nomeArquivo, $corpoArquivo)){
			$this->mensagemErro = "Erro ao gravar arquivo de Integracao com ERP, verifique permissoes!";
			return false;
		}
		$this->mChamarCobol();
		return true;
	}

	/*
	* Mйtodo mIntegrarERP (para efetuar integraзгo com ERP)
	* Integraзгo de entrada UC-NFE010
	*/
	public function mIntegrarERP(){
		// Selecionar Todos os Contribuintes
		$MContribuinte = new MContribuinte($this->grupo);
		$MContribuinte->ativo = "S";
		$retorno = $MContribuinte->selectAll();
		if(!$retorno){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}
		// Varrer todos os contribuintes
		foreach($retorno as $contribuintes){
			// Obter o diretorio de integraзгo
			if($contribuintes['diretorio_integracao'] != ""){
				// Vincular base de retorno
				$this->contribuinteBase = $contribuintes['diretorio_integracao'];
				// Selecionar o diretуrio de integraзгo
				$diretorio = @dir($contribuintes['diretorio_integracao']."/CaixaEntrada/Processar/");
				if(is_dir($contribuintes['diretorio_integracao']."/CaixaEntrada/Processar/")){
					// Ler todas as Notas do Diretуrio de Integraзгo
					while($arquivo = $diretorio->read()){
						// Caso for emissгo de nota redireciona para o mйtodo
						if(substr($arquivo,0,4) == "NFE-"){
							$retorno = $this->__IntegrarNota($diretorio->path, $arquivo, $contribuintes);
						}elseif(substr($arquivo,0,4) == "CNFE"){
							$retorno = $this->__IntegrarNotaCancelamento($diretorio->path, $arquivo, $contribuintes);
						}
						//INFE
						//CCNFE
					}
				}
			}
		}
		
	}

	/*
	* Mйtodo privado para Integrar Notas Fiscais
	*/	
	private function __IntegrarNota($pDir="", $pArq="", $pArrayContribuinte=""){
		if($pDir == ""|| $pArq == "" || $pArrayContribuinte == ""){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarNota() { Parametros obrigatorios: arquivo, arrayContribuinte }";
			return false;
		}

		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarNota() { O parametro nгo й um arquivo }";
			return false;
		}

		// Move o arquivo da pasta Processar para Processado
		if(!$this->__MoverProcessarProcessado($pDir,$pArq)){
			return false;
		}

		$ConvertNFePHP = new ConvertNFePHP();
		$ConvertNFePHP->arrayContribuinte = $pArrayContribuinte;
		$this->contribuinteBase = $pArrayContribuinte['diretorio_base'];
		$txtArquivo = utf8_encode(file_get_contents(str_replace("Processar","Processado",$pDir).$pArq)); // GJPS 18092014 - transformar de iso88959 para utf8 - 15 days left go to live
		$xml = $ConvertNFePHP->nfetxt2xml($txtArquivo);
		if($xml == ""){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarNota() { Nao foi possнvel efetuar a leitura do arquivo corretamente }";
			return true;
		}

		// Obtйm nъmero e sйrie da Nota
		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($xml[0],LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
		$numeroNota = $doc->getElementsByTagName('nNF')->item(0)->nodeValue;
		$serieNota = $doc->getElementsByTagName('serie')->item(0)->nodeValue;
		
		// Instanciar LOG  e Critica para posterior inserзгo de registro
		$MLog 		= new MLog($this->grupo);
		$MCritica	= new MCritica($this->grupo);
		$MLog->NOTA_FISCAL_cnpj_emitente= $MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $pArrayContribuinte['cnpj'];
		$MLog->NOTA_FISCAL_ambiente 	= $MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $pArrayContribuinte['ambiente'];
		$MLog->NOTA_FISCAL_serie_nota 	= $MCritica->EVENTO_NOTA_FISCAL_serie_nota 		= $serieNota;
		$MLog->NOTA_FISCAL_numero_nota 	= $MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $numeroNota;

		// Verificar se Nota jб estб catalogada

		$MNotaFiscal = new MNotaFiscal($this->grupo);
		$MNotaFiscal->cnpj_emitente = $pArrayContribuinte['cnpj'];
		$MNotaFiscal->ambiente = $pArrayContribuinte['ambiente'];
		$MNotaFiscal->numero_nota = $numeroNota;
		$MNotaFiscal->serie_nota = $serieNota;
		$retorno = $MNotaFiscal->selectAllMestre();
		if(!$retorno){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}
		
		// Nota ainda nгo catalogada
		if(empty($retorno[0]['status'])){
			// Incluir Nota Fiscal na base de dados
			$this->__mIncluirNota($xml, $pArrayContribuinte, $ConvertNFePHP->emailDestinatario, $ConvertNFePHP->informacaoAdicional);

			// Registrar Log de Importaзгo com Sucesso
			$MLog->data_hora = date("Y-m-d H:i:s");
			$MLog->evento = "Integraзгo ERP";
			$MLog->usuario = "AUTOMATICO";
			$MLog->descricao = "Integraзгo com ERP efetua com sucesso! Nota Fiscal $numeroNota / $serieNota , Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MLog->insert();

		// Nota jб catalogada (04 - Rejeitada ou 94 - Rejeitada com Inutilizacao) com STATUS de Inutilizaзгo
		}elseif($retorno[0]['status'] == "04" || $retorno[0]['status'] == "94"){
			$MInutilizacao = new MInutilizacao($this->grupo);
			$MInutilizacao->CONTRIBUINTE_cnpj = $pArrayContribuinte['cnpj'];
			$MInutilizacao->CONTRIBUINTE_ambiente = $pArrayContribuinte['ambiente'];
			$MInutilizacao->serie_nota = $serieNota;

			$retorno = $MInutilizacao->verificaNotaInut($numeroNota);
			if(!$retorno && $this->mensagemErro != ""){
				$this->mensagemErro = $MInutilizacao->mensagemErro;
				return false;
			}

			// A Nota Fiscal jб estб catalogada Inutilizaзгo
			if($retorno == true){
				// Registrar Log
				$MLog->data_hora = date("Y-m-d H:i:s");
				$MLog->evento = "Integraзгo ERP";
				$MLog->usuario = "AUTOMATICO";
				$MLog->descricao = "Integracao com ERP falhou, Nota Fiscal $numeroNota / $serieNota ja encontrada na base como Inutilizada, Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MLog->insert();
				
				// Registrar Crнtica
				$MCritica->codigo_referencia = "";
				$MCritica->descricao = "Integracao com ERP falhou, Nota Fiscal $numeroNota / $serieNota ja encontrada na base como Inutilizada, Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MCritica->data_hora = date("Y-m-d H:i:s");
				$MCritica->insert();
	
			// A Nota Fical nгo hб referкncia de inutilizaзгo
			}else{
				// Registrar Log
				$MLog->data_hora = date("Y-m-d H:i:s");
				$MLog->evento = "Integracao ERP";
				$MLog->usuario = "AUTOMATICO";
				$MLog->descricao = "Integracao com ERP , Nota Fiscal $numeroNota / $serieNota ja encontrada na base como Inutilizada, Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MLog->insert();

				// Deletar Nota Fiscal Anterior
				$retorno = $MNotaFiscal->delete();
				if(!$retorno){
					$this->mensagemErro = $MNotaFiscal->mensagemErro;
					return false;
				}
				// Incluir Nota Fiscal na base de dados
				$this->__mIncluirNota($xml, $pArrayContribuinte, $ConvertNFePHP->emailDestinatario);

				// Registrar Log de Importaзгo com Sucesso
				$MLog->data_hora = date("Y-m-d H:i:s");
				$MLog->evento = "Integraзгo ERP";
				$MLog->usuario = "AUTOMATICO";
				$MLog->descricao = "Integracao com ERP efetua com sucesso! Nota Fiscal $numeroNota / $serieNota , Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MLog->insert();
			}
		// Nota jб catalogada porйm nгo foi rejeitada
		}else{
			// Registrar Log
			$MLog->data_hora = date("Y-m-d H:i:s");
			$MLog->evento = "Integraзгo ERP";
			$MLog->usuario = "AUTOMATICO";
			$MLog->descricao = "Integracao com ERP falhou, Nota Fiscal $numeroNota / $serieNota ja encontrada na base com status diferente de 04-Rejeitada, Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MLog->insert();
			
			// Registrar Crнtica
			$MCritica->codigo_referencia = "";
			$MCritica->descricao = "Integracao com ERP falhou, Nota Fiscal $numeroNota / $serieNota ja encontrada na base com status diferente de 04-Rejeitada, Emitente: ".$pArrayContribuinte['cnpj']." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MCritica->data_hora = date("Y-m-d H:i:s");
			$MCritica->insert();
			return false;
		}
		return true;
	}

	/*
	* Mйtodo privado para Integrar Notas Fiscais de Cancelamento
	*/
	private function __IntegrarNotaCancelamento($pDir="",$pArq="", $pArrayContribuinte=""){
		if($pDir == ""|| $pArq == "" || $pArrayContribuinte == ""){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarNotaCancelamento() { Parametros obrigatorios: arquivo, arrayContribuinte }";
			return false;
		}

		$this->contribuinteBase = $pArrayContribuinte['diretorio_base'];

		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarNotaCancelamento() { O parametro nгo й um arquivo }";
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
		
		// Verificar se Nota existe / estб cancelada
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
		
		// Nota jб Cancelada
		if($retornoNF[0]['status'] == "06"){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." ja foi cancelada, status 06";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			// GRAVAR LOG DE REGISTRO
			return true;
		// Nota Fiscal nгo existe
		}elseif(empty($retornoNF[0]['status'])){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." nao existe na base de dados";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			return true;
		}
		
		// Verifica se hб evento de cancelamento
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
		
		// Identificado que jб existe evento de cancelamento para esta nota
		if(!empty($retorno)){
			$arquivo['mensagem'] = "Nota Fiscal ".$arquivo['nota'].", Serie ".$arquivo['serie']." ja foi cancelada, verificar Evento";
			$this->__mArqRetornoCanc($arquivo, $localArquivo, false);
			// TODO GRAVAR LOG DE REGISTRO
			return true;
		}

		// Verifica se o SEFAZ estб com o Status Inativo
		if(!__ConsultarStatusSEFAZ($arquivo['cnpj'], $arquivo['ambiente'])){
			// Verifica se com o SEFAZ inativo e nгo estб em modo contingкncia SCAN ou sйrie 900
			if(  !  ($pArrayContribuinte['contigencia'] == "03" ||
				(substr($arquivo['serie'],0,1) == "9" && strlen($arquivo['serie']) == "3"))){
				// TODO GRAVAR ARQUIVO DE RETORNO REJEITADO
				// TODO GRAVAR LOG DE REJEITADO
			}
		}

		// Verifica se estб ativa contingencia sem informar um nъmero de protocolo para a nota
		if( ! ($retornoNF[0]['status'] == "03" && $retornoNF[0]['numero_protocolo'] != "")){
		  // Verifica se o status й diferente de Contingencia SCAN 03 e Status da Nota Aguardando 02
		  if( ! ($pArrayContribuinte['contigencia'] == "03" && $retornoNF[0]['status'] == "02")){
			// TODO grava arquivo de rejeitado e LOG de rejeitado
		  }
		}

		// Verifica se hб evento de cancelamento
		$MEvento = new MEvento($this->grupo);
		$MEvento->NOTA_FISCAL_cnpj_emitente	= $arquivo['cnpj'];
		$MEvento->NOTA_FISCAL_numero_nota	= $arquivo['nota'];
		$MEvento->NOTA_FISCAL_serie_nota	= $arquivo['serie'];
		$MEvento->NOTA_FISCAL_ambiente		= $arquivo['ambiente'];
		$MEvento->tipo_evento				= "4";
		$MEvento->numero_sequencia			= "1";
		$MEvento->xml_env					= "";
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
	* Mйtodo privado para mover o arquivo de nota da pasta Processar para Processado
	*/
	private function __MoverProcessarProcessado($pDir="",$pArq=""){
		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = " CIntegracaoERP -> __MoverProcessarProcessado() { Parametros invalidos }";
			return false;
		}
		
		$arquivoAntigo 	= $pDir.$pArq;
		$arquivoNovo 	= str_replace("Processar","Processado",$pDir).$pArq;
		
		if(!rename($arquivoAntigo, $arquivoNovo)){
			$this->mensagemErro = " CIntegracaoERP -> __MoverProcessarProcessado() { Nгo й possнvel mover o arquivo de Processar para Processar }";
			return false;
		}
		return true;
	}
	
	/*
	* Mйtodo privado para desmantelar xml para cadastrar no banco de dados
	*/
	private function __mIncluirNota($pXml, $pArrayContribuinte, $pEmailDestinatario, $pObservacao=""){
		$this->contribuinteBase = $pArrayContribuinte['diretorio_base'];

		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($pXml[0],LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
		
		$MNotaFiscal = new MNotaFiscal($this->grupo);

		  $emitente = $doc->getElementsByTagName('emit')->item(0);
		$MNotaFiscal->cnpj_emitente		= $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		$MNotaFiscal->numero_nota		= $doc->getElementsByTagName('nNF')->item(0)->nodeValue;
		$MNotaFiscal->serie_nota		= $doc->getElementsByTagName('serie')->item(0)->nodeValue;
		$MNotaFiscal->ambiente			= $pArrayContribuinte['ambiente'];
		$MNotaFiscal->versao			= $doc->getElementsByTagName('infNFe')->item(0)->getAttribute('versao');
		$MNotaFiscal->cod_empresa_filial_softdib = $pArrayContribuinte['cod_emp_fil_softdib'];
		$MNotaFiscal->nome_emissor		= $emitente->getElementsByTagName('xNome')->item(0)->nodeValue;
		  $destinatario = $doc->getElementsByTagName('dest')->item(0);
		  
		if($destinatario->getElementsByTagName('CNPJ')->item(0)->nodeValue != "" && $destinatario->getElementsByTagName('CNPJ')->item(0)->nodeValue != null){
			$MNotaFiscal->cnpj_destinatario	= $destinatario->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		}else{
			$MNotaFiscal->cnpj_destinatario	= $destinatario->getElementsByTagName('CPF')->item(0)->nodeValue;
		}
		
		$MNotaFiscal->nome_destinatario	= $destinatario->getElementsByTagName('xNome')->item(0)->nodeValue;
		$MNotaFiscal->cod_destinatario	= "";
		$MNotaFiscal->email_destinatario = $pEmailDestinatario;
		$MNotaFiscal->status			= "01";
		$MNotaFiscal->tipo_emissao		= $doc->getElementsByTagName('tpEmis')->item(0)->nodeValue;
		$MNotaFiscal->data_emissao		= date('Y-m-d',strtotime($doc->getElementsByTagName('dhEmi')->item(0)->nodeValue));
		$MNotaFiscal->uf_webservice		= $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
		$MNotaFiscal->layout_danfe		= $pArrayContribuinte['danfe_layout_caminho'];
		$MNotaFiscal->valor_total_nfe	= $doc->getElementsByTagName('vNF')->item(0)->nodeValue;
 		$MNotaFiscal->data_entrada_saida= $doc->getElementsByTagName('dhSaiEnt')->item(0)->nodeValue;
		$MNotaFiscal->chave				= str_replace("NFe","",$doc->getElementsByTagName('infNFe')->item(0)->getAttribute('Id'));
		$MNotaFiscal->numero_protocolo	= "";
		$MNotaFiscal->tipo_operacao		= $doc->getElementsByTagName('tpNF')->item(0)->nodeValue; // 0 Entrada - 1 Saida
		$MNotaFiscal->xml				= $pXml[0];
		$MNotaFiscal->danfe_impressa	= "0";
		$MNotaFiscal->email_enviado		= "0";
		$MNotaFiscal->lote_nfe			= "";
		$MNotaFiscal->observacao		= $pObservacao;
		$MNotaFiscal->CONTRIBUINTE_cnpj	= $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		$retorno = $MNotaFiscal->insert();

		if(!$retorno){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return false;
		}
	}
	
	/*
	* Mйtodo privado para retornar o arquivo de cancelamento
	*/
	private function __mArqRetornoCanc($pArquivo="", $pPath="", $pStatus=""){
	  if($pArquivo == "" || $pPath == "" || $pStatus == ""){
		$this->mensagemErro = "CIntegracaoERP -> __mArqRetornoCanc(){ Parametros obrigatorios: Arquivo e Status }";
		return false;
	  }

	  $pPath = str_replace("CNFE","CNFEI",$pPath);
	  
	  if(!file_put_contents($pPath, implode("|",$pArquivo) )){
		$this->mensagemErro = "CIntegracaoERP -> __mArqRetornoCanc(){ Erro ao gravar arquivo retorno }";
		return false;
	  }
	  $this->mChamarCobol();
	}
	
	/*
	* Mйtodo privado para consultar situaзгo do SEFAZ, se estб ativo ou inativo
	*/
	private function __ConsultarStatusSEFAZ($pCnpj, $pAmbiente){
	  $ToolsNFePHP = new ToolsNFePHP($pCnpj, $pAmbiente);

	  $resp = $ToolsNFePHP->statusServico();

	  if(!$resp){
		  $this->mensagemErro = $ToolsNFePHP->errMsg;
		  return false;
	  }

	  if($resp['bStat'] != 1){
		  $this->mensagemErro = "CIntegracaoERP -> __ConsultarStatusSEFAZ() { SEFAZ inoperante }";
		  return false;
	  }else{
		  return true;
	  }	
	}
	
	/*
	* Mйtodo privado para chamar o COBOL
	*/
	private function mChamarCobol(){
		system('COBDIR=/usr/cobol_mf_4.1');
		system('COBPATH=/user/objetos');
		system('TERM=ansi');
		system('export COBDIR COBPATH TERM');
		system('PATH=.:/sbin:/bin:/usr/sbin:/usr/bin:/usr/X11R6/bin:/user/bindib:/root/bin:/usr/cobol_mf/bin');
		system('LD_LIBRARY_PATH="/usr/cobol_mf_4.1/coblib" ; export LD_LIBRARY_PATH');
		system('set LANG=english_us.ascii');
		system('set LC_CTYPE=""');
		system('export LC_CTYPE LANG');
		system('EXTFH="/user/bindib/extfh.cfg"; export EXTFH');
		system('HOME='.$this->contribuinteBase);
		
		putenv("LD_LIBRARY_PATH=/usr/cobol_mf/coblib");
		putenv("COBDIR=/usr/cobol_mf");
		putenv("COBPATH=/user/objetos");
		putenv("COBSW=-F-l-Q");
		putenv("EXTFH=/user/bindib/extfh.cfg");
		putenv("TERM=linux");
		putenv("HOME=".$this->contribuinteBase);

		$sve350 = shell_exec("TERM=linux; export TERM; cd ".$this->contribuinteBase."; /usr/cobol_mf/cob41/bin/cobrun -F-l-Q /user/objetos/SVE350SNF.int  root &");
		echo $sve350;
	}
}
?>