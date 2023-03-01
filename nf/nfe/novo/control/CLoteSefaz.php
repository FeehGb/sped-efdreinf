<?php
/**
* @name      	CLoteSefaz
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para obter as notas fiscais da base e gerar lote de envio para SEFAZ
 * @TODO 		Testar o c�digo com todas as alternativas
*/

/**
 * @import Importa��o de Classes de comunica��o
 */
 require_once("../model/MLote.php");
 require_once("../model/MNotaFiscal.php");
 require_once("../model/MLog.php");
 require_once("../model/MCritica.php");
 require_once("../model/MContribuinte.php");
 require_once("CIntegracaoERP.php");
 require_once("CBackup.php");

/**
 * @class CLoteSefaz
 */ 
class CLoteSefaz{

/*
 * Atributos da Classe
 */
	public $cnpj_emitente;
	public $versao;
	public $nRecibo;
	public $status;
	public $ambiente;
	public $contingencia;

	public $numero_nota;
	public $serie_nota;
	public $status_nota;
	public $motivo_nota;
	public $chave_nota;
	public $modelo_nota="55";
	public $uf_nota;

	public $tipo_emissao;

	public $motivo;
	public $dhRecebimento;

	private $lote;
	private $xml;
	private $xmlProtocolo;
	private $numProtocolo;

	public $mensagemErro = "";
	
	private $grupo;
	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}


/**
 * @method mSubmeterLote
 * @autor Guilherme Silva
 * @TODO  testar tudo
 */
	public function mSubmeterLote(){
		// Declarando variaveis
//		$cont=0;

		// Busca notas fiscais recebidas e pendentes de envio
		$MNotaFiscal = new MNotaFiscal($this->grupo);

		$retorno = $MNotaFiscal->selectRecebidas();
		$contRetorno = count($retorno);
		
		if(!$retorno){
		  $this->mensagemErro = $MNotaFiscal->mensagemErro;
		  return false;
		}
		// Fazer TODA ROTINA enquanto houver notas a serem submetidas ao SEFAZ // ver pq n�o t� fazendo todo o while
		for($cont=0;$cont<$contRetorno;$cont++){
			  $this->cnpj_emitente 	= $retorno[$cont]['cnpj_emitente'];
			  $this->numero_nota 	= $retorno[$cont]['numero_nota'];
			  $this->serie_nota 	= $retorno[$cont]['serie_nota'];
			  $this->ambiente 		= $retorno[$cont]['ambiente'];
			  $this->status 		= $retorno[$cont]['status'];
			  $this->versao 		= $retorno[$cont]['versao'];
			  $this->tipo_emissao 	= $retorno[$cont]['tipo_emissao'];
			  $this->chave_nota		= $retorno[$cont]['chave'];
			  
			  $this->xml = $xml		= base64_decode($retorno[$cont]['xml']);
			  $this->uf_nota		= $retorno[$cont]['uf_webservice'];
			  /* Seleciona contribuinte para obter o tipo de conting�ncia */
			  $MContribuinte 			= new MContribuinte($this->grupo);
			  $MContribuinte->cnpj	 	= $this->cnpj_emitente;
			  $MContribuinte->ambiente 	= $this->ambiente;
			  $retornoContribuinte 		= $MContribuinte->selectCNPJAmbiente();

			  $this->contingencia = $retornoContribuinte[0]['contigencia'];
			  
			  if($this->contingencia == "02" 	/* FS */
				|| $this->contingencia == "04" 	/* DPEC*/
				|| $this->contingencia == "05" 	/* FS-DA */ ){
				// danfe_impressa? ""-Nota nova n�o informado ainda "N"-Ainda n�o impressa "S"-j� impressa
				// "C"-impressa em conting�ncia (falta emitir em homologa��o)
				if($retornoContribuinte[0]['danfe_impressa'] != "C"){
					// N�o continua com emiss�o, ir para o pr�ximo registro do while
					//$cont++;
					continue;
				}
				// Caso contr�rio: Ok, continua para transmiss�o do Lote
			  }else{
				/* Compor a chave da NFE (chamar o converte NFE apenas para compor a chave novamente e atualizar no XML) */
				$ConvertNFePHP = new ConvertNFePHP();
				$xmlRetorno = $ConvertNFePHP->montaChaveXMLExterno($xml);
				if(!$xmlRetorno){
					echo "Erro ao compor o c�lculo do d�gito verificador:";
					print_r($xml);
					echo $ConvertNFePHP->mensagemErro;
					// Consultar proxima nota recebida.
					//$cont++;
					continue;
				}else{
					$MNotaFiscal->cnpj_emitente = $this->cnpj_emitente;
					$MNotaFiscal->numero_nota	= $this->numero_nota;
					$MNotaFiscal->serie_nota	= $this->serie_nota;
					$MNotaFiscal->ambiente		= $this->ambiente;
					$MNotaFiscal->xml			= $this->xml = $xmlRetorno;
					$MNotaFiscal->update();
				}
			  }
			  if(!$retornoContribuinte){
			    $this->mensagemErro = $CIntegracaoERP->mensagemErro;
				return false;
			  }

			  // Cadastrar novo Lote na base de dados e recupera numero gerado automaticamente
			  $MLote = new MLote($this->grupo);
			  
			  $MLote->cnpj_emitente = $this->cnpj_emitente;
			  $MLote->versao = $this->versao;
			  $MLote->recibo = "";
			  $MLote->status = "";
			  $MLote->ambiente = $this->ambiente;
			  $MLote->contingencia =  $this->contingencia;
	  
			  $return = $MLote->insert();
			  if(!$return || $return == null){
				  $this->mensagemErro = $MLote->mensagemErro;
				  return false;
			  }
	  
			  $this->lote = $return[0]['ult_id'];
			  // Atualizar status da Nota / Lote / XML
			  $MNotaFiscal->cnpj_emitente	= $this->cnpj_emitente;
			  $MNotaFiscal->numero_nota		= $this->numero_nota;
			  $MNotaFiscal->serie_nota		= $this->serie_nota;
			  $MNotaFiscal->ambiente		= $this->ambiente;

			  $MNotaFiscal->lote_nfe		= $this->lote;

			  // Gravar LOG de registro de novo Lote
			  $MLog = new MLog($this->grupo);
			  $MLog->NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
			  $MLog->NOTA_FISCAL_numero_nota	= $this->numero_nota;
			  $MLog->NOTA_FISCAL_serie_nota		= $this->serie_nota;
			  $MLog->NOTA_FISCAL_ambiente		= $this->ambiente;
			  $MLog->data_hora					= "";
			  $MLog->usuario					= "AUTOMATICO"; // Obter o lk-usuario
			  $MLog->evento						= "LOTE ".$this->lote." gerado e pronto para transmitir";
			  $MLog->insert();
	  
			  $ToolsNFePHP = "";
			  //Selecionar o tipo de contingencia
			  switch($this->contingencia){
				case "03":
					$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente,$this->grupo, 2,false,true);
				break;
				case "06":
				case "07":
					$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente, $this->grupo,2,false,"SVC");
				break;
				default:
					$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente, $this->grupo);
				break;
			  }
			  if(!$ToolsNFePHP){
				echo "CLoteSefaz, nao foi possivel instanciar o ToolsNFePHP";					
				continue;
			  }

			  $this->xml = $ToolsNFePHP->signXML($this->xml, 'infNFe');
			  if(!$this->xml){
				  $this->mensagemErro = $nfe->errMsg;
				  return false;
			  }

			  // Valida XML
			  $retoronErro = '';
			  //se der erro no validador gravar uma critica e criar um novo status
 
			  $retorno = $ToolsNFePHP->validXML($this->xml,'',$retornoErro); // N�o h� necessidade de passar o nome do xsd pois ele pega no cadastro do contribuinte
			  //$retorno=true;
			  if(!$retorno){
				// Gravar Log com Erro
				  $MLog->data_hora	= date("Y-m-d H:i:s");
				  $MLog->usuario	= "AUTOMATICO";
				  $MLog->evento		= $retornoErro;
			    // Gravar Cr�tica com o Erro
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
				  $MCritica->EVENTO_NOTA_FISCAL_numero_nota		= $this->numero_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_serie_nota		= $this->serie_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->ambiente;
				  $MCritica->sequencia							= "";
				  $MCritica->codigo_referencia					= "";
				  $MCritica->data_hora_critica					= date("Y-m-d H:i:s");

				  // TODO GRAVAR LOG E CRITICA
				  foreach ($retornoErro as $er){
					echo $er."\n";
					  $MCritica->descricao .= $er." <br>";
					  $MLog->evento .= $er." <br>";
				  }
				  // Mover erro de integracao
				  $MNotaFiscal->status = "04";

				  $retorno = $MCritica->insert();
				  $MLog->insert();
				  $MNotaFiscal->update();
				  return true;
			  }

			  // Submete Lote no SEFAZ
			  //$retorno = $ToolsNFePHP->sendLot($this->xml,$this->lote); Muda na versao 3.10
			  $retTools = $ToolsNFePHP->autoriza($this->xml, $this->lote, $retorno, 0);
			  $this->status			= $retorno['cStat'];
			  $this->motivo 		= $retorno['xMotivo'];
			  $this->dhRecebimento	= $retorno['dhRecbto'];
			  $this->nRecibo		= $retorno['infRec']['nRec'];
	  
			  if (!$retTools){
				  $this->mensagemErro = $ToolsNFePHP->errMsg;
				  $MLog->descricao = $this->mensagemErro;
				  $MLog->insert();
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
				  $MCritica->EVENTO_NOTA_FISCAL_numero_nota		= $this->numero_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_serie_nota		= $this->serie_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->ambiente;
				  $MCritica->sequencia							= "";
				  $MCritica->codigo_referencia					= "";
				  $MCritica->data_hora_critica					= date("Y-m-d H:i:s");
				  $MCritica->descricao							= $ToolsNFePHP->errMsg;
				  continue;
			  }
			  
			  // Atualiza o Status do Lote, idependente do retorno.
			  $this->__mAtualizarLote();
			  
			  // Para envio com sucesso sair da classe.
			  // 103 - Lote Recebido com Sucesso
			  // 100 - Autorizado o uso da NF-e
			  // 107 - Servi�o em Opera��o 
			  // 204 - Duplicidade de NFe (tem que rever este erro)
			  if($this->status == "103" || $this->status == "100" || $this->status == "107" || $this->status == "204"){
				  $MNotaFiscal->danfe_impressa	= "N";	// Danfe ainda n�o impressa
				  $MNotaFiscal->status			= "02"; // Nota aguardando retorno SEFAZ
				  $MNotaFiscal->xml				= $this->xml;
				  if(!$MNotaFiscal->update()){
					  $this->mensagemErro = $MNotaFiscal->mensagemErro;
					  return false;
				  }

				  // Update no lote com recibo e status
				  $MLote->recibo = $this->nRecibo;
				  $MLote->status = $this->status;
				  $MLote->update();
				  if(!$return || $return == null){
					$this->mensagemErro = $MLote->mensagemErro;
					echo "Erro ao Atualizar o Lote!";
				  }

				  // Gravar LOG de sucesso do envio da nota
				  $MLog->data_hora	= date('Y-m-d h:i:s');
				  $MLog->usuario	= "AUTOMATICO"; // Obter o lk-usuario
				  $MLog->evento		= "LOTE ".$this->lote." recebido com sucesso (103)";
				  $MLog->insert();				  
				  //$cont++;
				  continue;
			  }
			  // Caso houver DENEGACAO do LOTE
			  // 110 - Uso Denegado
			  // 205 - NF-e esta denegada na base de dados
			  // 301 - Uso Denegado: Irregularidade fiscal do emitente 
			  // 302 - Uso Denegado: Irregularidade fiscal do destinat�rio
			  elseif($this->status == "110" || $this->status == "205" || $this->status == "301" || $this->status == "302"){
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente = $this->cnpj_emitente;
				  $MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->numero_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->serie_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_ambiente	= $this->ambiente;
				  $MCritica->codigo_referencia				= $this->status;
				  $MCritica->descricao						= $this->motivo;
				  $MCritica->data_hora_critica				= $this->dhRecebimento;
				  
				  $retorno = $MCritica->insert();
				  // Denegada
			  	  $MNotaFiscal->status	= "05";

				  if(!$MNotaFiscal->update()){
					$this->mensagemErro = $MNotaFiscal->mensagemErro;
					return false;
				  }
				  
//GJPS appendar protocolo de dengecao

			// Gerar arquivo de integra��o de ERP
				 $CIntegracaoERP = new CIntegracaoERP($this->grupo);
				 $pArray = array();
				 $pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
				 $pArray['uf_emitente'] 	= $this->uf_nota ;
			 	 $pArray['ano_mes'] 		= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
				 $pArray['modelo_nota'] 	= $this->modelo_nota;
				 $pArray['serie_nota'] 		= $this->serie_nota;
				 $pArray['numero_nota'] 	= $this->numero_nota;
				 $pArray['serie_nota_con'] 	= " ";
				 $pArray['numero_nota_con']	= " ";
				 $pArray['status'] 			= "7";
				 $pArray['chave'] 			= $this->chave_nota;

				 $this->dhRecebimento = str_replace(" ","T",str_replace("/","-",$this->dhRecebimento));
				 $CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
				 $retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
				 if(!$retorno){
					$this->mensagemErro = $CIntegracaoERP->mensagemErro;
					//return false;
				 }
			  }

			  // SEFAZ Fora de Operacao
			  // 108 - Servi�o Paralisado Momentaneamente (curto prazo) 
			  // 109 - Servi�o Paralisado sem Previs�o 
			  //   - Em branco
			  elseif($this->status == "108" || $this->status == "109" || $this->status == "" || $retorno == "" || $this->status == "999"){
				continue;
			  }
			  
			  // Entende-se que qualquer outro erro � de REJEI��O
			  else{
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente = $this->cnpj_emitente;
				  $MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->numero_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->serie_nota;
				  $MCritica->EVENTO_NOTA_FISCAL_ambiente	= $this->ambiente;
				  $MCritica->codigo_referencia				= $this->status;
				  $MCritica->descricao						= $this->motivo;
				  $MCritica->data_hora_critica				= $this->dhRecebimento;
				  
				  $retorno = $MCritica->insert();
				  // N�o trata caso de erro na iser��o
				  
				  if(!$MNotaFiscal->update()){
					$this->mensagemErro = $MNotaFiscal->mensagemErro;
					return false;
				  }

			// Gerar arquivo de integra��o de ERP
				 $CIntegracaoERP = new CIntegracaoERP($this->grupo);
				 $pArray = array();
				 $pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
				 $pArray['uf_emitente'] 	= $this->uf_nota ;
			 	 $pArray['ano_mes'] 		= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
				 $pArray['modelo_nota'] 	= $this->modelo_nota;
				 $pArray['serie_nota'] 		= $this->serie_nota;
				 $pArray['numero_nota'] 	= $this->numero_nota;
				 $pArray['serie_nota_con'] 	= " ";
				 $pArray['numero_nota_con']	= " ";
				 $pArray['status'] 			= "8";
				 $pArray['chave'] 			= $this->chave_nota;

				 $this->dhRecebimento = str_replace(" ","T",str_replace("/","-",$this->dhRecebimento));
				 $CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
				 $retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
				 if(!$retorno){
					$this->mensagemErro = $CIntegracaoERP->mensagemErro;
					//return false;
				 } 
			  }
			  
			  $MLote->status = $this->status;
			  if(!$MLote->update()){
			    $this->mensagemErro = $MLote->mensagemErro;
				//return false;
			  }

			  //$cont++;
		}
	}
	
/**
 * @method mCosultarLote
 * @autor Guilherme Silva
 * @TODO  Revisar c�digo com DFD e testar todas alternativas
 */
	public function mConsultarLote(){
		$MLote = new MLote($this->grupo);
                $MNotaFiscal = new MNotaFiscal($this->grupo);

		$retorno = $MLote->selectProcessados();
		
		if(!$retorno){
		  $this->mensagemErro = $MLote->mensagemErro;
		  return false;
		}

		$cont=0; // Contador de registro para NFs processados
		while(is_array($retorno[$cont])){
				$this->lote 		= $MLote->id			= $retorno[0]['id'];
				$this->cnpj_emitente= $MLote->cnpj_emitente = $retorno[0]['cnpj_emitente'];
				$this->versao		= $MLote->versao		= $retorno[0]['versao'];
				$this->recibo		= $MLote->recibo		= $retorno[0]['recibo']; // 15 posicoes-
				$this->status		= $MLote->status		= $retorno[0]['status'];
				$this->ambiente		= $MLote->ambiente		= $retorno[0]['ambiente'];
				$this->contingencia	= $MLote->contingencia	= $retorno[0]['contingencia'];

				//Selecionar o tipo de contingencia
				switch($this->contingencia){
					case "03":
						$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente,$this->grupo, 2,false,true);
						if(!$ToolsNFePHP){
							return false;
						}
					break;
					case "06":
					case "07":
						$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente, $this->grupo,2,false,"SVC");
						if(!$ToolsNFePHP){
							return false;
						}
					break;
					default:
						$ToolsNFePHP = new ToolsNFePHP($this->cnpj_emitente, $this->ambiente, $this->grupo);
						if(!$ToolsNFePHP){
							return false;
						}
					break;
				}

				if(!$ToolsNFePHP->getProtocol3($MLote->recibo,'',$MLote->ambiente,$aRetorno)){
					$this->mensagemErro = $ToolsNFePHP->errMsg;
					return false;
				}
				
                                // Houve erro do Sefaz não catalogado (999)
                                if($aRetorno['xMotivo'] == "999"){
                                    // Chamar a consulta, porém pelo número da chave, ao inves do lote
                                    $MNotaFiscal->cnpj_emitente = $MLote->cnpj_emitente;
                                    $MNotaFiscal->lote_nfe = $MLote->id;
                                    $MNotaFiscal->ambiente = $MLote->ambiente;
                                    $retorno = $MNotaFiscal->selectAllMestre();
		
                                    if(!$retorno){
                                      $this->mensagemErro = $MNotaFiscal->mensagemErro;
                                      return false;
                                    }
                                    
                                    // Obter a chave da nota submetida
                                    if(!$ToolsNFePHP->getProtocol3('',$retorno[0]['chave'],$MLote->ambiente,$aRetorno)){
					$this->mensagemErro = $ToolsNFePHP->errMsg;
                                        return false;
                                    }
                                }
				// Obter variaveis de retorno
				$this->status 		 = $aRetorno['cStat'];
				$this->motivo		 = $aRetorno['xMotivo'];
				$this->status_nota	 = $aRetorno['aProt'][0]['cStat'];
				$this->motivo_nota	 = $aRetorno['aProt'][0]['xMotivo'];
				$this->nRecibo 		 = $aRetorno['nRec'];
				$this->ambiente		 = $aRetorno['tpAmb'];
				$this->xmlProtocolo	 = $aRetorno['xmlRetorno'];
				$this->chave_nota	 = $aRetorno['aProt'][0]['chNFe'];
				$this->dhRecebimento = str_replace("/","-",$aRetorno['aProt'][0]['dhRecbto']);
				$this->uf_nota		 = $aRetorno['cUF'];
				$this->numProtocolo	 = $aRetorno['aProt'][0]['nProt'];

				// Atualiza Status do Lote
				$MLote->recibo	= $this->nRecibo;
				$MLote->status	= $this->status;
                                        
                                // Quando for status 999 nao atualizar o lote para que possa consutlar novamente
                                if($this->status != "999" && $this->status != "108" && $this->status != "109"){
                                    $retorno = $MLote->update();
                                }

				if(!$retorno){
					$this->mensagemErro = $MLote->mensagemErro;
				}

				// Obter dados da Nota do Lote para fazer as devidas modifica��es.
				$MNotaFiscal = new MNotaFiscal($this->grupo);
				$sql = "SELECT * FROM `nfe_".$this->grupo."`.`NOTA_FISCAL` WHERE `lote_nfe`=".trim($this->lote);
				$retorno = $MNotaFiscal->selectAllMestre($sql);
				if(!$retorno){
					$this->mensagemErro = $MNotaFiscal->mensagemErro;
					return false;
				}
				$MNotaFiscal->numero_nota 	= $this->numero_nota	= $retorno[0]['numero_nota'];
				$MNotaFiscal->serie_nota	= $this->serie_nota		= $retorno[0]['serie_nota'];
				$MNotaFiscal->cnpj_emitente	= $this->cnpj_emitente	= $retorno[0]['cnpj_emitente'];
				$MNotaFiscal->ambiente		= $this->ambiente 		= $retorno[0]['ambiente'];
				$MNotaFiscal->xml			= $this->xml			= base64_decode($retorno[0]['xml']);

				// Verifica se houve falta de comunica��o com SEFAZ
				switch($this->status){
					// Lote processado com sucesso (104)
					case "104":
						// Notas autorizadas o uso (100) atualizar o status e xml com autoriza��o apendada
						if(trim($this->status_nota) == "100" || trim($this->status_nota) == "204"){
							// Adicionar Protocolo de Autoriza��o e atualiza na base
							if($xml = $ToolsNFePHP->addProt($this->xml, $this->xmlProtocolo)){
								$this->xml = $xml;
							}

							$MNotaFiscal->status 			= "03";
							$MNotaFiscal->xml 				= $this->xml;
							$MNotaFiscal->lote_nfe			= $this->lote;
							$MNotaFiscal->chave				= $this->chave_nota;
							$MNotaFiscal->numero_protocolo	= $this->numProtocolo;
							$retorno = $MNotaFiscal->update();
							if(!$retorno){
								$this->mensagemErro = $MNotaFiscal->mensagemErro;
								return false;
							}

							// Gravar arquivo backup de nota autorizada
							$CBackup = new CBackup($this->grupo);
							$retBkp = $CBackup->mGuardarXml($this->xml,$this->chave_nota, $this->cnpj_emitente, 'nfe');
							if(!$retBkp){
								echo $retBkp->mensagemErro;
							}
						// 110 - Nota Denegada
						}elseif($this->status_nota == "110" ||
								$this->status_nota == "301" ||
								$this->status_nota == "302"	){
							// Adicionar Protocolo de Autoriza��o e atualiza na base
							if($xml = $ToolsNFePHP->addProt($this->xml, $this->xmlProtocolo)){
								$this->xml = $xml;
							}
							
							$MNotaFiscal->status 	= "05";
							$MNotaFiscal->xml 		= $this->xml;
							$MNotaFiscal->lote_nfe	= $this->lote;
							$MNotaFiscal->chave		= $this->chave_nota;
							$retorno = $MNotaFiscal->update();
							if(!$retorno){
								$this->mensagemErro = $MNotaFiscal->mensagemErro;
								return false;
							}
							
							// Gravar arquivo backup de nota autorizada
							$CBackup = new CBackup($this->grupo);
							$retBkp = $CBackup->mGuardarXml($this->xml,$this->chave_nota, $this->cnpj_emitente, 'nfe');
							if(!$retBkp){
								echo $retBkp->mensagemErro;
							}
// APENDAR PROTOCOLO DE DENEGACAO GUARDA ETC
						// Nota Fiscal rejeitada, gravar critica
						}else{
							$MCritica = new MCritica($this->grupo);
							$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
							$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->numero_nota;
							$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->serie_nota;
							$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->ambiente;
							$MCritica->codigo_referencia				= $this->status_nota;
							$MCritica->descricao		                = $this->motivo_nota;
							$retorno = $MCritica->insert();
							if(!$retorno){
								$this->mensagemErro = $MCritica->mensagemErro;
								return false;
							}
							// Assume status de rejeitada
							$MNotaFiscal->status 	= "04";
							$MNotaFiscal->xml 		= $this->xml;
							$MNotaFiscal->lote_nfe	= $this->lote;
							$MNotaFiscal->chave		= $this->chave_nota;
							$retorno = $MNotaFiscal->update();
							if(!$retorno){
								$this->mensagemErro = $MNotaFiscal->mensagemErro;
								return false;
							}
						}

						// Gerar arquivo de integra��o de ERP
						$CIntegracaoERP = new CIntegracaoERP($this->grupo);

						$pArray['cnpj_emitente'] 	= $this->cnpj_emitente;
						$pArray['uf_emitente'] 		= $this->uf_nota;
						$pArray['ano_mes'] 			= substr($this->dhRecebimento,2,2).substr($this->dhRecebimento,5,2);
						$pArray['modelo_nota'] 		= $this->modelo_nota;
						$pArray['serie_nota'] 		= $this->serie_nota;
						$pArray['numero_nota'] 		= $this->numero_nota;
					    $pArray['serie_nota_con'] 	= " ";
						$pArray['numero_nota_con']	= " ";
						if($this->status_nota == "100"){
							$pArray['status'] 		= "6";
						}elseif($this->status_nota == "110"){
							$pArray['status'] 		= "7";
						}else{
							$pArray['status'] 		= "8";
						}
						$pArray['chave'] 			= $this->chave_nota;
						
						$MContribuinte 				= new MContribuinte($this->grupo);
						$MContribuinte->cnpj	 	= $this->cnpj_emitente;
						$MContribuinte->ambiente 	= $this->ambiente;
						$retornoContribuinte 		= $MContribuinte->selectCNPJAmbiente();
						
						if(!$retornoContribuinte){
							$this->mensagemErro = $CIntegracaoERP->mensagemErro;
							return false;
						}
						
						$CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
						$retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimento, $pArray);
						if(!$retorno){
							$this->mensagemErro = $CIntegracaoERP->mensagemErro;
							return false;
						}
						
						// Gravar Log
						$MLog = new MLog($this->grupo);
						$MLog->NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
						$MLog->NOTA_FISCAL_numero_nota		= $this->numero_nota;
						$MLog->NOTA_FISCAL_serie_nota		= $this->serie_nota;
						$MLog->NOTA_FISCAL_ambiente			= $this->ambiente;
						$MLog->data_hora					= $this->dhRecebimento;
						$MLog->usuario						= "AUTOMATICO"; // Obter o lk-usuario;
						$MLog->evento						= "Consulta do Lote ".$this->lote.", No. Recibo ".$this->nRecibo." Status Lote (".$this->status.") ".$this->motivo." Status Nota (".$this->status_nota.") ".$this->motivo_nota;
						$MLog->insert();

						return true;
					break;

					// Staus de Falha de comunica��o (108,109) ou ainda em processamento (105) abortar
					case "105":
					case "108":
					case "109":
                                            // Guilherme:
                                            // Colocado o 999 aqui, quando for falha não fazer nada e voltar para consultar a nota,
                                            // ocorria antes que consultava a nota e dava 999 acabava por denegar no nosso sistema 
                                            // só que ficava autorizado no sefaz. Feito isso para reconsultar e não dar o erro.
                                        case "999":
					case "":
						$this->mensagemErro = $this->motivo;
						return false;
					break;
					// Status desconhecido
					default:
						// Grava Critica
						$MCritica = new MCritica($this->grupo);
						$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $this->cnpj_emitente;
						$MCritica->EVENTO_NOTA_FISCAL_numero_nota	= $this->numero_nota;
						$MCritica->EVENTO_NOTA_FISCAL_serie_nota	= $this->serie_nota;
						$MCritica->EVENTO_NOTA_FISCAL_ambiente		= $this->ambiente;
						$MCritica->codigo_referencia				= $this->status;
						$MCritica->descricao						= $this->motivo;
						$retorno = $MCritica->insert();
						if(!$retorno){
							$this->mensagemErro = $MCritica->mensagemErro;
							return false;
						}
						
						// Atualiza lote com erro e assume Denegada
						$MNotaFiscal->status = "05";
						$retorno = $MNotaFiscal->update();
						if(!$retorno){
							$this->mensagemErro = $MNotaFiscal->mensagemErro;
							return false;
						}
						// Finaliza processamento
						return true;
					break;
				}

			$cont++;
		}
	}

	private function __mAtualizarLote(){
		// Atualizar Lote na base de dados
		$MLote = new MLote($this->grupo);

		$MLote->id 				= $this->lote;
		$MLote->cnpj_emitente 	= $this->cnpj_emitente;
		$MLote->ambiente 		= $this->ambiente;
		$MLote->recibo 			= $this->nRecibo;
		$MLote->status 			= $this->status;

		$return = $MLote->update();
		if(!$return || $return == null){
			$this->mensagemErro = $MLote->mensagemErro;
			return false;
		}
	}
}
?>