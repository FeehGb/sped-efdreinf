<?php
/**
 * @name      	CInutilizar
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para fazer tratar a Inutilização
 * @TODO 		Fazer tudo
*/

/**
 * @import 
 */ 
 require_once("/var/www/html/nf/nfe/novo/libs/ToolsNFePHP.class.php");
 require_once("/var/www/html/nf/nfe/novo/control/CIntegracaoERP.php");
 require_once("/var/www/html/nf/nfe/novo/model/MNotaFiscal.php");
 require_once("/var/www/html/nf/nfe/novo/model/MInutilizacao.php");
 require_once("/var/www/html/nf/nfe/novo/model/MEvento.php");
 require_once("/var/www/html/nf/nfe/novo/model/MLog.php");
 require_once("CBackup.php");

/**
 * @class CInutilizar
 */ 
class CInutilizar{

/*
 * Atributos da Classe
 */
	public $contribuinteCNPJ;
	public $contribuinteAmbiente;
	public $serie;
	public $numeracaoInicial;
	public $numeracaoFinal;
	public $justificativa;
	public $usuario;
	public $periodo_ini;
	public $periodo_fim;
	public $modelo="55";

	public $mensagemErro = "";

	private $grupo;

// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
/**
 * @class CWebService
 * @autor Guilherme Silva
 * @TODO  Fazer tudo e testar tudo
 */
	public function mConsultaStatusSefaz(){
		// Verifica se todos os campos necessarios estão setados
		$localMensagem = "";
		if(	trim($this->contribuinteCNPJ) == ""){ $localMensagem .= " CNPJ do Contribuinte ";}
		if(	trim($this->contribuinteAmbiente) == ""){ $localMensagem .= " Ambiente ";}
		if($localMensagem != ""){
			$this->mensagemErro = " CInutilizar -> mConsultaStatusSefaz() -> Atributos Obrigatorios ".$localMensagem;
			return false;
		}

		// Instanciar Classe ToolsNFePHP
		$ToolsNFePHP = new ToolsNFePHP($this->contribuinteCNPJ, $this->contribuinteAmbiente, $this->grupo);

		$resp = $ToolsNFePHP->statusServico();
		$this->mensagemErro = $resp['xMotivo'];
		
		if($resp['bStat'] != 1){
			return false;
		}else{
			return true;
		}		
	}
	
	public function mObterLista(){
		// Verifica se todos os campos necessarios estão setados
		$localMensagem = "";
		if(	trim($this->contribuinteCNPJ) == ""){ $localMensagem .= " CNPJ do Contribuinte ";}
		if(	trim($this->contribuinteAmbiente) == ""){ $localMensagem .= " Ambiente ";}
		if($localMensagem != ""){
			$this->mensagemErro = " CInutilizar -> mObterLista() -> Atributos Obrigatorios ".$localMensagem;
			return false;
		}

		// Instanciar Classe ToolsNFePHP
		$MInutilizar = new MInutilizacao($this->grupo);
		$MInutilizar->CONTRIBUINTE_cnpj = $this->contribuinteCNPJ;
		$MInutilizar->CONTRIBUINTE_ambiente = $this->contribuinteAmbiente;
		if($this->periodo_ini != "" && $this->periodo_ini != ""){
			$MInutilizar->periodo_ini = $this->periodo_ini;
			$MInutilizar->periodo_fim = $this->periodo_fim;
		}

		$return = $MInutilizar->select();

		$this->mensagemErro = $MInutilizar->mensagemErro;
		return $return;
	}

	public function mInutilizarNumeracao(){
		// Variveis que serao usadas localmente
		$cnpj 			= $this->contribuinteCNPJ;
		$ambiente 		= $this->contribuinteAmbiente;
		$serie 			= $this->serie;
		$numIni 		= $this->numeracaoInicial;
		$numFim 		= $this->numeracaoFinal;
		$justificativa	= $this->justificativa;
		$ano 			= date('y');
		$modelo 		= $this->modelo;
		$uf				= ""; // Carregado no CContribuinte
		$diretorio		= ""; // Carregado no CContribuinte
		$protocolo 		= ""; // Carregado no retorno do WS
		$data_hora 		= ""; // Carregado no retorno do WS
		$status			= ""; // Carregado no retorno do WS
		$status_motivo	= ""; // Carregado no retorno do WS
		$xml_env		= ""; // Carregado no retorno do WS
		$xml_ret		= ""; // Carregado no retorno do WS 
		$uf_ret			= ""; // Carregado no retorno do WS 
		
		// Instanciar Classe de LOG
		$MLog = new MLog($this->grupo);
		$MLog->NOTA_FISCAL_cnpj_emitente	= $cnpj;
		$MLog->NOTA_FISCAL_numero_nota		= $numIni;
		$MLog->NOTA_FISCAL_serie_nota		= $serie;
		$MLog->NOTA_FISCAL_ambiente			= $ambiente;
		$MLog->sequencia					= "1";
		$MLog->data_hora					= $data_hora;
		$MLog->evento						= "INUTILIZACAO";
		$MLog->usuario						= $this->usuario; // Obter o lk-usuario;

		// Verifica se todos os campos necessarios estão setados
		$localMensagem = "";
		if(	trim($cnpj) == ""){ $localMensagem .= " CNPJ do Contribuinte ";}
		if(	trim($ambiente) == ""){ $localMensagem .= " Ambiente ";}
		if(	trim($serie) == ""){ $localMensagem .= " Serie ";}
		if(	trim($numIni) == ""){ $localMensagem .= " Numeracao Inicial ";}
		if(	trim($numFim) == ""){ $localMensagem .= " Numeracao Final ";}
		if(	trim($justificativa) == ""){ $localMensagem .= " Justificativa ";}
		if($localMensagem != ""){
			$this->mensagemErro = " CInutilizar -> mInutilizarNumeracao() -> Atributos Obrigatorios ".$localMensagem;
			return false;
		}

		// Instanciar Classe MNotaFiscal
		$MNotaFiscal = new MNotaFiscal($this->grupo);
		
		$MNotaFiscal->serie_nota = $serie;
		$MNotaFiscal->cnpj_emitente = $cnpj;
		$MNotaFiscal->ambiente = $ambiente;
		$MNotaFiscal->notaInicial = $numIni;
		$MNotaFiscal->notaFinal = $numFim;
		
		// Verificar se o range de Inutilização se enquadra dentro do range de notas já emitidas
		$retorno = $MNotaFiscal->selectNFMinMax();
		if(!$retorno){
			$this->mensagemErro = $MNotaFiscal->mensagemErro;
			return $retorno;
		}
		if($retorno[0]['min'] == null || $retorno[0]['max'] == null){
			$this->mensagemErro = " CInutilizar -> mInutilizarNumeracao() -> Nao foram encontradas notas cadastradas para este emitente,ambiente,serie nao e possivel inutilizar esta numeracao";
			return false;
		}
		//if( !( ($retorno[0]['min'] <= $numIni) && ($retorno[0]['max'] >= $numFim) ) ){ //COMENTADO 23/05/14 para melhoria solicitado por Roberto
		if( $retorno[0]['max'] < $numFim){
			$this->mensagemErro = "Nao eh permitido inutilizar numeracao acima da ultima nota cadastradas (".$retorno[0]['max'].")";
			return false;
		}

		// Verificar se notas do range são deneganas ou rejeitadas
		$retorno = $MNotaFiscal->selectNFStatus();
		if( ! (empty($retorno) || $retorno == null)){
			if(!$retorno){
				$this->mensagemErro = $MNotaFiscal->mensagemErro;
				return $retorno;
			}
			
			foreach($retorno as $campos){
				if($campos['status'] != "04"){
					$this->mensagemErro = "Foram encontradas notas fiscais (Autenticadas, Canceladas, Inutilizadas, Novas ou Denegadas) no intervalo informado, nao permitindo a inutilizacao.";
					return false;
				}
			}
		}

		// Instanciar Classe MInutilizar para verificar se já não houve inutilização da numeração informada
		$MInutilizacao = new MInutilizacao($this->grupo);
		
		$MInutilizacao->CONTRIBUINTE_cnpj = $cnpj;
		$MInutilizacao->CONTRIBUINTE_ambiente = $ambiente;
		$MInutilizacao->serie_nota = $serie;

		$retorno = $MInutilizacao->select();
		
		if( ! (empty($retorno) || $retorno == null)){
			if(!$retorno){
				$this->mensagemErro = $MInutilizacao->mensagemErro;
				return $retorno;
			}
			// Verificar se o intervalo solicitado já fora inutilizado
			foreach($retorno as $campos){
				if(!( ($numIni < $campos['numero_nota_inicial']) && ($numFim < $campos['numero_nota_inicial'])
					OR
					( ($numIni > $campos['numero_nota_final']) && ($numFim > $campos['numero_nota_final']) )) ){
						$this->mensagemErro = "Foram encontradas notas fiscais ja inutilizadas no intervalo informado, nao permitindo a reinutilizacao.";
						return false;
				}
			}
		}

		// Selecionar contribuinte para obter dados para inutilizar
		$retorno = $this->__mSelecionaContribuinte();
		
		$uf 		= $retorno[0]['uf'];
		$diretorio 	= $retorno[0]['diretorio_integracao'];
		$contribuinteBase = $retorno[0]['diretorio_base'];

		// Chamar ToolsNFE para Inutilização

		/* TODO tratar erro de retorno do ToolsNFE */
		$ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $this->grupo);
		$retorno = $ToolsNFePHP->inutNF($ano,$serie,$numIni,$numFim,$justificativa,$ambiente, $aRetorno);
		if (!$retorno){
			$this->mensagemErro = $ToolsNFePHP->errMsg;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		$protocolo 		= $aRetorno['nProt'];
		$data_hora 		= str_replace("/","-",$aRetorno['dhRecbto']);
		$status 		= $aRetorno['cStat'];
		$status_motivo	= $aRetorno['xMotivo'];
		$uf_env 		= $aRetorno['cUF'];
		$uf_ret 		= $aRetorno['cUF'];
		$xml_ret		= $aRetorno['xml_ret'];
		$xml_env		= $aRetorno['xml_env'];
		$xml			= $aRetorno['xml'];

		// Gravar registro de Inutilizacao na tabela de INUTILIZACAO
		$MInutilizacao = null;
		$MInutilizacao = new MInutilizacao($this->grupo);
		
		$MInutilizacao->CONTRIBUINTE_cnpj 		= $cnpj;
		$MInutilizacao->CONTRIBUINTE_ambiente	= $ambiente;
		$MInutilizacao->serie_nota 				= $serie;
		$MInutilizacao->numero_nota_inicial 	= $numIni;
		$MInutilizacao->numero_nota_final 		= $numFim;
		$MInutilizacao->justificativa 			= $justificativa;
		$MInutilizacao->ano						= $ano;
		$MInutilizacao->modelo_nota 			= $modelo;
		$MInutilizacao->protocolo 				= $protocolo;
		$MInutilizacao->data_hora 				= $data_hora;
		$MInutilizacao->status 					= $status;
		$MInutilizacao->status_motivo 			= $status_motivo;
		$MInutilizacao->uf_responsavel 			= $uf_ret;
		$MInutilizacao->xml_env 				= $xml_env;
		$MInutilizacao->xml_ret 				= $xml_ret;
		$MInutilizacao->xml 					= $xml;
		
		$retorno = $MInutilizacao->insert();
		if(!$retorno){
			$this->mensagemErro = $MInutilizacao->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		// Gerar arquivo de Integração com COBOL
		$CIntegracaoERP = new CIntegracaoERP($this->grupo);

		$arrayIntegracao['cnpj_emitente'] 		= $cnpj;
		$arrayIntegracao['uf_emitente'] 		= $uf_env;
		$arrayIntegracao['ano'] 				= $ano;
		$arrayIntegracao['modelo_nota'] 		= $modelo;
		$arrayIntegracao['serie_nota'] 			= $serie;
		$arrayIntegracao['numero_nota_inicial']	= $numIni;
		$arrayIntegracao['numero_nota_final'] 	= $numFim;
		$arrayIntegracao['status'] 				= "2";
		$arrayIntegracao['descricao_status'] 	= $status_motivo;
		$arrayIntegracao['data_hora'] 			= date('Y.m.d_H.i.s');
		$arrayIntegracao['protocolo'] 			= $protocolo;
		$arrayIntegracao['uf_ibge_responsavel'] = $uf_ret;
		$CIntegracaoERP->contribuinteBase = $contribuinteBase;

		$retorno = $CIntegracaoERP->mRetornoInutilizacao($diretorio,$data_hora,$arrayIntegracao);

		if(!$retorno){
			$this->mensagemErro = $CIntegracaoERP->mensagemErro;
			$MLog->descricao = $this->mensagemErro;
			$MLog->insert();
			return false;
		}

		// Gravar arquivo backup de nota autorizada
		$CBackup = new CBackup($this->grupo);
		$retBkp = $CBackup->mGuardarXml($xml,$protocolo, $cnpj, 'inut');
		if(!$retBkp){
			echo $retBkp->mensagemErro;
		}

		$MLog->descricao = "Inutilização Efetuada com Sucesso CNPJ: ".$this->contribuinteCNPJ.", AMBIENTE: ".$this->contribuinteAmbiente.", SERIE: ".$this->serie.", NF-INICIAL: ".$this->numeracaoInicial.", NF-FINAL: ".$this->numeracaoInicial." ";
		$MLog->insert();

		return true;
	}


/*
 * @method mSelecionaContribuinte (criado para listar informacoes do contribuinte)
 * @autor Guilherme Silva
 */
	public function mInutilizacaoNumeracaoPendente(){

		// VERIFICAR SE HÁ ALGUMA INUTILIZAÇÃO PENDENTE (na tabela de eventos)
		$MEvento 				= new MEvento($this->grupo);
		$MEvento->tipo_evento	= "5"; // Tipo Evento Carta de Correção
		$MEvento->protocolo		= "NULL"; // Não foi gerado protocolo de autorização de Inutilização (a inutilizar)
		$retorno = $MEvento->selectMestre();

		if(!$retorno){
			$this->mensagemErro = $MEvento->mensagemErro;
			return $retorno;
		}
		
		// Instanciar Log para gravar posteriomente
		$MLog = new MLog($this->grupo);
		$MLog->evento	= "INUTILIZACAO";
		$MLog->usuario	= "AUTOMATICO";

		$cont=0; // Contador de registro para NFs processados
		while(is_array($retorno[$cont])){
			$cnpj			= $retorno[$cont]['NOTA_FISCAL_cnpj_emitente'];
			$ambiente		= $retorno[$cont]['NOTA_FISCAL_ambiente'];
			$ano 			= date('y');
			$serie 			= $retorno[$cont]['NOTA_FISCAL_serie_nota'];
			$numIni 		= $retorno[$cont]['NOTA_FISCAL_numero_nota'];
			$numFim 		= $retorno[$cont]['NOTA_FISCAL_numero_nota'];
			$justificativa 	= $retorno[$cont]['descricao'];
			$modelo			= $this->modelo;

			$this->contribuinteCNPJ 	= $cnpj;
			$this->contribuinteAmbiente = $ambiente;
			$retornoContri = $this->__mSelecionaContribuinte();
			$uf 		= $retornoContri[0]['uf'];
			$diretorio 	= $retornoContri[0]['diretorio_integracao'];
			$contribuinteBase = $retornoContri[0]['diretorio_base'];

			$MLog->NOTA_FISCAL_cnpj_emitente	= $cnpj;
			$MLog->NOTA_FISCAL_numero_nota		= $numIni;
			$MLog->NOTA_FISCAL_serie_nota		= $serie;
			$MLog->NOTA_FISCAL_ambiente			= $ambiente;
			$MLog->sequencia					= "0";

			$ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $this->grupo);
			$aRetorno = $ToolsNFePHP->inutNF($ano,$serie,$numIni,$numFim,$justificativa,$ambiente,$aRetorno);
			if (!$aRetorno){
				if($aRetorno['cStat'] != "108" && $aRetorno['cStat'] != "109"){
					// Gravar Crítica de Erro
					$MCritica = new MCritica($this->grupo);
					$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $cnpj;
					$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $numIni;
					$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $serie;
					$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $ambiente;
					$MCritica->codigo_referencia				= $aRetorno['cStat'];
					$MCritica->descricao		                = $ToolsNFePHP->errMsg;
					$MCritica->insert();
				}

				$MLog->descricao = $this->mensagemErro = $ToolsNFePHP->errMsg;
				$MLog->insert();
				return false;
			}
			$data = 	date('Y-m-d h:i:s', strtotime($aRetorno['dhRecbto']));
			$ufEnv = 	$aRetorno['cUF_env'];
			$ufRet = 	$aRetorno['cUF_ret'];

			// Inutilizado com Sucesso
			if($aRetorno['cStat'] == "102"){
				//UPDATE DO EVENTO DE INUTILIZAÇÃO e CRIAR REGISTRO NA TABELA DE INUTILIZAÇÃO
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $retorno[$cont]['NOTA_FISCAL_cnpj_emitente'];
				$MEvento->NOTA_FISCAL_numero_nota	= $retorno[$cont]['NOTA_FISCAL_numero_nota'];
				$MEvento->NOTA_FISCAL_serie_nota	= $retorno[$cont]['NOTA_FISCAL_serie_nota'];
				$MEvento->NOTA_FISCAL_ambiente		= $retorno[$cont]['NOTA_FISCAL_ambiente'];
				$MEvento->tipo_evento				= "5";
				$MEvento->numero_sequencia			= "0";
				$MEvento->xml_env					= $aRetorno['xml_env'];
				$MEvento->xml_ret					= $aRetorno['xml_ret'];
				$MEvento->descricao					= $justificativa;
				$MEvento->protocolo					= $aRetorno['nProt'];
				$MEvento->data_hora					= $data;
				$MEvento->status					= $aRetorno['cStat'];
				
				if(!$MEvento->insert()){
					$this->mensagemErro = $MEvento->mensagemErro;
					$MLog->data_hora 	= $MEvento->data_hora;
					$MLog->descricao 	= $this->mensagemErro;
					$MLog->insert();
					return false;
				}
				
				$protocolo = $aRetorno['nProt'];

				$MInutilizacao = new MInutilizacao($this->grupo);
				$MInutilizacao->CONTRIBUINTE_cnpj 		= $cnpj;
				$MInutilizacao->CONTRIBUINTE_ambiente	= $ambiente;
				$MInutilizacao->serie_nota 				= $serie;
				$MInutilizacao->numero_nota_inicial 	= $numIni;
				$MInutilizacao->numero_nota_final 		= $numFim;
				$MInutilizacao->justificativa 			= $justificativa;
				$MInutilizacao->ano						= $ano;
				$MInutilizacao->modelo_nota 			= $modelo;
				$MInutilizacao->protocolo 				= $protocolo;
				$MInutilizacao->data_hora 				= $data;
				$MInutilizacao->status 					= $aRetorno['cStat'];
				$MInutilizacao->status_motivo 			= $aRetorno['cStat'];
				$MInutilizacao->uf_responsavel 			= $aRetorno['cUF_ret'];
				$MInutilizacao->xml_env 				= $aRetorno['xml_env'];
				$MInutilizacao->xml_ret 				= $aRetorno['xml_ret'];

				if(!$MInutilizacao->insert()){
					$this->mensagemErro = $MInutilizacao->mensagemErro;
					$MLog->data_hora = $MInutilizacao->data_hora;
					$MLog->descricao = $this->mensagemErro;
					$MLog->insert();
					return false;
				}
				
				$MLog->data_hora = "";
				$MLog->descricao = "Inutilização de Numeração efetuada com sucesso,CNPJ:".$cnpj." Ambiente:".$ambiente." Nota:".$numIni." Série:".$serie;
				$MLog->insert();

				// Gerar arquivo de Integração com COBOL
				$CIntegracaoERP = new CIntegracaoERP($this->grupo);

				$arrayIntegracao['cnpj_emitente'] 		= $cnpj;
				$arrayIntegracao['uf_emitente'] 		= $ufEnv;
				$arrayIntegracao['ano'] 				= $ano;
				$arrayIntegracao['modelo_nota'] 		= $this->modelo;
				$arrayIntegracao['serie_nota'] 			= $serie;
				$arrayIntegracao['numero_nota_inicial']	= $numIni;
				$arrayIntegracao['numero_nota_final'] 	= $numFim;
				$arrayIntegracao['status'] 				= "2";
				$arrayIntegracao['descricao_status'] 	= "";
				$arrayIntegracao['data_hora'] 			= $data;
				$arrayIntegracao['protocolo'] 			= $protocolo;
				$arrayIntegracao['uf_ibge_responsavel'] = $ufEnv;
				$CIntegracaoERP->contribuinteBase = $contribuinteBase;

				$retornoIntegra = $CIntegracaoERP->mRetornoInutilizacao($diretorio,$data,$arrayIntegracao);

				if(!$retornoIntegra){
					$this->mensagemErro = $CIntegracaoERP->mensagemErro;
					$MLog->descricao = $this->mensagemErro;
					$MLog->insert();
					return false;
				}
			// SEFAZ inativo para Inutilização
			}
			$cont++;
		}
	}
/*
 * @method mSelecionaContribuinte (criado para listar informacoes do contribuinte)
 * @autor Guilherme Silva
 */
	private function __mSelecionaContribuinte(){
		$CContribuinte = new CContribuinte($this->grupo);
		
		$CContribuinte->cnpj 		= $this->contribuinteCNPJ;
		$CContribuinte->ambiente	= $this->contribuinteAmbiente;
		
		$retorno = $CContribuinte->mObterContribuinte();
		if(empty($retorno) || $retorno == null){
			$this->mensagemErro = "CInutilizar -> mInutilizarNumeracao (nao foi possivel acessar o contribuinte, verifique parametros)";
			return false;
		}
		if(!$retorno){
			$this->mensagemErro = $CContribuinte->mensagemErro;
			return false;
		}
		return $retorno;
	}
	
}
?>