<?php
/**
 * @name      	CIntegracaoERP
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para tratar a comunicação do envio e retorno para o erp
*/

/**
 * @import 
 */ 
	//define('__ROOT__', dirname(dirname(__FILE__))); 
	require_once(__ROOT__."/model/MContribuinte.php");
	require_once(__ROOT__."/model/MMDFe.php");
	require_once(__ROOT__."/model/MEvento.php");
	require_once(__ROOT__."/model/MLog.php");
	require_once(__ROOT__."/model/MCritica.php");
	require_once(__ROOT__."/libs/ConvertMDFePHP.class.php");
	require_once(__ROOT__."/libs/MDFeNFePHP.class.php");

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

	public function mRetornoConsulta($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estão setados
		if($pArray == ""){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios)";
			return false;
		}
		if(	!isset($pArray['cnpj_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoConsulta (parametros de entrada obrigatorios: cnpj)";
			return false;
		}
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
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .		
		$data_hora_arq = str_replace(" ",".",$data_hora_arq); // Trocar string espa‡os por .		
		$nomeArquivo  = "MDFER_";
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
	* Método para Retornor para o Cobol o arquivo de cancelamento
	*/
	public function mRetornoCancelamento($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estão setados
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

		$data_hora_arq = $dataHora;
		$data_hora_arq = str_replace("-",".",$data_hora_arq); // Trocar string - por .
		$data_hora_arq = str_replace("T","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .
		
		$nomeArquivo  = "CMDFER_";
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
	* Método para Retornor para o Cobol o arquivo de encerramento
	*/
	public function mRetornoEncerramento($pDiretorio, $dataHora, $pArray=""){
		// Verifica se todos os campos necessarios estão setados
		if($pArray == ""){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios)"; return false;
		}
		if(	!isset($pArray['cnpj_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: cnpj)"; return false;
		}
		if( !isset($pArray['uf_ibge_emitente'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: uf)"; return false;
		}
		if( !isset($pArray['ano_mes'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: ano+mes)"; return false;
		}
		if( !isset($pArray['modelo_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: modelo_nota)"; return false;
		}
		if( !isset($pArray['serie_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: serie)"; return false;
		}
		if( !isset($pArray['numero_nota'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: numeracao nota)"; return false;
		}
		if( !isset($pArray['status'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: status)";return false;
		}
		if( !isset($pArray['descricao_status'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: descricao status)";return false;
		}
		if( !isset($pArray['data_hora'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: data/hora)";	return false;
		}
		/*if( !isset($pArray['protocolo'])){
			$this->mensagemErro = "CIntegracaoERP -> mRetornoEncerramento (parametros de entrada obrigatorios: protocolo)";	return false;
		}*/

		$data_hora_arq = $dataHora;
		$data_hora_arq = str_replace("-",".",$data_hora_arq); // Trocar string - por .
		$data_hora_arq = str_replace("T","_",$data_hora_arq); // Trocar string T por _
		$data_hora_arq = str_replace(":",".",$data_hora_arq); // Trocar string : por .
		
		$nomeArquivo  = "EMDFER_";
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
	* Método mIntegrarERP (para efetuar integração com ERP)
	* Integração de entrada UC-NFE010
	*/
	public function mIntegrarERP(){
		// Selecionar Todos os Contribuintes
		$MContribuinte = new MContribuinte($this->grupo);
		$sql = "SELECT * FROM `mdfe_".$this->grupo."`.`CONTRIBUINTE` WHERE ambiente <> '3'";
		$retorno = $MContribuinte->selectAll($sql);
		if(!$retorno){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}

		// Varrer todos os contribuintes
		foreach($retorno as $contribuintes){
			// Obter o diretorio de integração
			if($contribuintes['diretorio_integracao'] != ""){
				// Vincular base de retorno
				$this->contribuinteBase = $contribuintes['diretorio_integracao'];
				// Selecionar o diretório de integração
				$diretorio = dir($contribuintes['diretorio_integracao']."/CaixaEntrada/Processar/");
				// Ler todas as Notas do Diretório de Integração
				while($arquivo = $diretorio->read()){
					// Só ira integrar notas fiscais com inicio 
					if(substr($arquivo,0,5) == "MDFE-"){
						$tpAmbiente = $contribuintes['ambiente'];
						$retorno = $this->__IntegrarMDFe($diretorio->path, $arquivo, $contribuintes, $tpAmbiente);
						if(!$retorno){ return false; }
					}
				}
			}
		}
		
	}

	/*
	* Método privado para Integrar Notas Fiscais
	*/	
	private function __IntegrarMDFe($pDir="", $pArq="", $pArrayContribuinte="", $pAmbiente=""){
		if($pDir == ""|| $pArq == "" || $pArrayContribuinte == ""){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarMDFe() { Parametros obrigatorios: arquivo, arrayContribuinte }";
			return false;
		}

		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarMDFe() { O parametro não é um arquivo }";
			return false;
		}

		// Move o arquivo da pasta Processar para Processado
		if(!$this->__MoverProcessarProcessado($pDir,$pArq)){
			return false;
		}

		$ConvertMDFePHP = new ConvertMDFePHP();
		$ConvertMDFePHP->arrayContribuinte = $pArrayContribuinte;
		$this->contribuinteBase = $pArrayContribuinte['diretorio_base'];
		$txtArquivo = utf8_encode(file_get_contents(str_replace("Processar","Processado",$pDir).$pArq)); // GJPS 18092014 - transformar de iso88959 para utf8 - 1 days left go to live
		$xml = $ConvertMDFePHP->MDFetxt2xml($txtArquivo,$pAmbiente);
		if($xml == ""){
			$this->mensagemErro = "CIntegracaoERP -> __IntegrarMDFe() { Nao foi possível efetuar a leitura do arquivo corretamente }";
			return true;
		}
		// Obtém número e série da Nota
		
		// Instanciar LOG  e Critica para posterior inserção de registro
		$MLog 			= new MLog($this->grupo);
		$MCritica		= new MCritica($this->grupo);
		$MLog->cnpj		= $MCritica->cnpj		= $ConvertMDFePHP->cnpj;
		$MLog->ambiente = $MCritica->ambiente 	= $ConvertMDFePHP->tpAmb;
		$MLog->serie 	= $MCritica->serie 		= $ConvertMDFePHP->serie;
		$MLog->numero 	= $MCritica->numero 	= $ConvertMDFePHP->numero;
		$MLog->id_lote	= 0;


		// Verificar se MDFe já está catalogada
		$MMDFe = new MMDFe($this->grupo);
		$MMDFe->cnpj 		= $ConvertMDFePHP->cnpj;
		$MMDFe->ambiente	= $ConvertMDFePHP->tpAmb;
		$MMDFe->numero 		= $ConvertMDFePHP->numero;
		$MMDFe->serie 		= $ConvertMDFePHP->serie;
		$retorno = $MMDFe->selectAllMestre();
		if(!$retorno){
			$this->mensagemErro = $MMDFe->mensagemErro;
			return false;
		}
		
		// Nota ainda não catalogada
		if(empty($retorno[0]['status'])){
			// Incluir Nota Fiscal na base de dados
			if(!$this->__mIncluirMDFe($xml, $pArrayContribuinte)){
				// Registrar Log
				$MLog->data_hora = date("Y-m-d H:i:s");
				$MLog->evento = "Integração ERP";
				$MLog->usuario = "AUTOMATICO";
				$MLog->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MLog->insert();
				
				// Registrar Crítica
				$MCritica->codigo_referencia = "";
				$MCritica->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MCritica->data_hora = date("Y-m-d H:i:s");
				$MCritica->insert();
			}

			// Registrar Log de Importação com Sucesso
			$MLog->data_hora = date("Y-m-d H:i:s");
			$MLog->evento = "Integração ERP";
			$MLog->usuario = "AUTOMATICO";
			$MLog->descricao = "Integração com ERP efetua com sucesso! MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MLog->insert();

		// Nota já catalogada (04 - Rejeitada ou 94 - Rejeitada com Inutilizacao) com STATUS de Inutilização
		}elseif($retorno[0]['status'] == "04" || $retorno[0]['status'] == "94"){
			// Deletar Nota Fiscal Anterior
			$retorno = $MMDFe->delete();
			if(!$retorno){
				$this->mensagemErro = $MNotaFiscal->mensagemErro;
				return false;
			}
			// Incluir Nota Fiscal na base de dados
			if(!$this->__mIncluirMDFe($xml, $pArrayContribuinte)){
				// Registrar Log
				$MLog->data_hora = date("Y-m-d H:i:s");
				$MLog->evento = "Integração ERP";
				$MLog->usuario = "AUTOMATICO";
				$MLog->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MLog->insert();

				// Registrar Crítica
				$MCritica->codigo_referencia = "";
				$MCritica->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
				$MCritica->data_hora = date("Y-m-d H:i:s");
				$MCritica->insert();
			}

			// Registrar Log de Importação com Sucesso
			$MLog->data_hora = date("Y-m-d H:i:s");
			$MLog->evento = "Integração ERP";
			$MLog->usuario = "AUTOMATICO";
			$MLog->descricao = "Reintegração com ERP efetua com sucesso! MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MLog->insert();

		// Nota já catalogada porém não foi rejeitada
		}else{
			// Registrar Log
			$MLog->data_hora = date("Y-m-d H:i:s");
			$MLog->evento = "Integração ERP";
			$MLog->usuario = "AUTOMATICO";
			$MLog->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MLog->insert();
			
			// Registrar Crítica
			$MCritica->codigo_referencia = "";
			$MCritica->descricao = "Integracao com ERP falhou, MDFe ".$MMDFe->numero." / ".$MMDFe->serie." , Emitente: ".$MMDFe->cnpj." , Ambiente: ".$pArrayContribuinte['ambiente'];
			$MCritica->data_hora = date("Y-m-d H:i:s");
			$MCritica->insert();
		}
	}

	/*
	* Método privado para mover o arquivo de nota da pasta Processar para Processado
	*/
	private function __MoverProcessarProcessado($pDir="",$pArq=""){
		if(!is_file($pDir.$pArq)){
			$this->mensagemErro = " CIntegracaoERP -> __MoverProcessarProcessado() { Parametros invalidos }";
			return false;
		}
		
		$arquivoAntigo 	= $pDir.$pArq;
		$arquivoNovo 	= str_replace("Processar","Processado",$pDir).$pArq;
		
		if(!rename($arquivoAntigo, $arquivoNovo)){
			$this->mensagemErro = " CIntegracaoERP -> __MoverProcessarProcessado() { Não é possível mover o arquivo de Processar para Processar }";
			return false;
		}
		return true;
	}

	/*
	* Método privado para desmantelar xml para cadastrar no banco de dados
	*/
	private function __mIncluirMDFe($pXml, $pArrayContribuinte){
		$this->contribuinteBase = $pArrayContribuinte['diretorio_base'];

		$doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM para ler o XML gerado
		$doc->formatOutput = false;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($pXml[0],LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
		
		$MMDFe = new MMDFe($this->grupo);
		
		  $ide 		= $doc->getElementsByTagName('ide')->item(0);
		  $emitente = $doc->getElementsByTagName('emit')->item(0);
		  $tot 		= $doc->getElementsByTagName('tot')->item(0);
		$MMDFe->cnpj							= $emitente->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		$MMDFe->ambiente						= $ide->getElementsByTagName('tpAmb')->item(0)->nodeValue;
		$MMDFe->numero							= $ide->getElementsByTagName('nMDF')->item(0)->nodeValue;
		$MMDFe->serie							= $ide->getElementsByTagName('serie')->item(0)->nodeValue;
		$MMDFe->versao							= $doc->getElementsByTagName('infMDFe')->item(0)->getAttribute('versao');
		$MMDFe->tipo_emitente					= $ide->getElementsByTagName('tpEmit')->item(0)->nodeValue;
		$MMDFe->cod_empresa_filial_softdib		=""; // nao era pra ter empresa e filial aqui
		$MMDFe->nome_emissor					= $emitente->getElementsByTagName('xNome')->item(0)->nodeValue;
		$MMDFe->uf_carregamento					= $ide->getElementsByTagName('UFIni')->item(0)->nodeValue;
		$MMDFe->uf_descarregamento				= $ide->getElementsByTagName('UFFim')->item(0)->nodeValue;
		$MMDFe->status							= "01";
		$MMDFe->tipo_emissao					= $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
		$MMDFe->data_emissao					= $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
		$MMDFe->valor_total_carga				= $tot->getElementsByTagName('vCarga')->item(0)->nodeValue;
		$MMDFe->quantidade_nfe					= $tot->getElementsByTagName('qNFe')->item(0)->nodeValue;
		$MMDFe->unidade_peso_bruto				= $tot->getElementsByTagName('cUnid')->item(0)->nodeValue;
		$MMDFe->peso_bruto						= $tot->getElementsByTagName('qCarga')->item(0)->nodeValue;
		$MMDFe->chave							= $ide->getElementsByTagName('cMDF')->item(0)->nodeValue;
		$MMDFe->numero_protocolo				= "";
		$MMDFe->xml_envio						= "";
		$MMDFe->xml_retorno						= "";
		$MMDFe->xml								= $pXml[0];
		$MMDFe->damdfe_impressa					= "";

		$retorno = $MMDFe->insert();

		if(!$retorno){
			$this->mensagemErro = $MMDFe->mensagemErro;
			return false;
		}
		return true;
	}
	
	/*
	* Método privado para retornar o arquivo de cancelamento
	*/
	private function __mArqRetornoCanc($pArquivo="", $pPath="", $pStatus=""){
	  if($pArquivo == "" || $pPath == "" || $pStatus == ""){
		$this->mensagemErro = "CIntegracaoERP -> __mArqRetornoCanc(){ Parametros obrigatorios: Arquivo e Status }";
		return false;
	  }

	  $pPath = str_replace("CMDFE","CMDFEI",$pPath);
	  
	  if(!file_put_contents($pPath, implode("|",$pArquivo) )){
		$this->mensagemErro = "CIntegracaoERP -> __mArqRetornoCanc(){ Erro ao gravar arquivo retorno }";
		return false;
	  }
	  $this->mChamarCobol();
	}
	
	/*
	* Método privado para consultar situação do SEFAZ, se está ativo ou inativo
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
	* Método privado para chamar o COBOL
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

		shell_exec("TERM=linux; export TERM; cd ".$this->contribuinteBase."; /usr/cobol_mf/cob41/bin/cobrun -F-l-Q /user/objetos/STC350.int  root &");
	}
}
?>