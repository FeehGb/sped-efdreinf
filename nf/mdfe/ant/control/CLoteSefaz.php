<?php
/**
* @name      	CLoteSefaz
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para obter as notas fiscais da base e gerar lote de envio para SEFAZ
 * @TODO 		Testar o código com todas as alternativas
*/

/**
 * @import Importação de Classes de comunicação
 */
 require_once(__ROOT__."/model/MMDFe.php");
 require_once(__ROOT__."/model/MLote.php");
 require_once(__ROOT__."/model/MLog.php");
 require_once(__ROOT__."/model/MCritica.php");
 require_once(__ROOT__."/model/MContribuinte.php");
 require_once(__ROOT__."/libs/ConvertMDFePHP.class.php");
 require_once(__ROOT__."/libs/MDFeNFePHP.class.php");
 require_once(__ROOT__."/control/CIntegracaoERP.php");
 require_once(__ROOT__."/control/CBackup.php");

/**
 * @class CLoteSefaz
 */ 
class CLoteSefaz{

/*
 * Atributos da Classe
 */
	public $cnpj;
	public $versao;
	public $ambiente;
	public $contingencia;

	public $numero;
	public $serie;
	public $status;
	public $status_nota;
	public $statusSefaz;
	public $motivoSefaz;
	public $motivo_nota;
	public $nReciboSefaz;

	public $chave;
	public $modelo="55";
	public $uf_emitente;

	public $tipo_emissao;

	public $motivoServer;
	public $dhRecebimentoSefaz;

	private $lote;
	private $xml;
	private $xmlProtocolo;
	private $numProtocolo;

	public $mensagemErro = "";
	
	private $grupo;
	private $CLog;
	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
		$this->CLog = new CLog("CLoteSefaz");
	}


/**
 * @method mSubmeterLote
 * @autor Guilherme Silva
 * @TODO  testar tudo
 */
	public function mSubmeterLote(){
		$this->CLog->mMensagem("inicio mSubmeterLote");
		// Declarando variaveis
		$cont=0;

		// Busca notas fiscais recebidas e pendentes de envio
		$MMDFe = new MMDFe($this->grupo);

		$retorno = $MMDFe->selectRecebidas();

		if(!$retorno){
		  $this->CLog->mMensagem("impossivel selecionar recebidas: ".$MMDFe->mensagemErro);
		  $this->mensagemErro = $MMDFe->mensagemErro;
		  return false;
		}
		// Fazer TODA ROTINA enquanto houver notas a serem submetidas ao SEFAZ
		while(is_array($retorno[$cont])){
			  $this->cnpj 			= $retorno[$cont]['cnpj'];
			  $this->numero 		= $retorno[$cont]['numero'];
			  $this->serie 			= $retorno[$cont]['serie'];
			  $this->ambiente 		= $retorno[$cont]['ambiente'];
			  $this->status 		= $retorno[$cont]['status'];
			  $this->versao 		= $retorno[$cont]['versao'];
			  $this->tipo_emissao 	= $retorno[$cont]['tipo_emissao'];
			  $this->chave			= $retorno[$cont]['chave'];
			  
			  $this->CLog->mMensagem("Seleciona nota recebida, cnpj:".$this->cnpj." numero:".$this->numero." serie:".$this->serie." ambiente:".$this->ambiente." status:".$this->status." versao:".$this->versao." tipo_emis:".$this->tipo_emissao." chave:".$this->chave);

			  $this->xml 			= base64_decode($retorno[$cont]['xml']);
			  /* Seleciona contribuinte para obter o tipo de contingência */
			  $MContribuinte 			= new MContribuinte($this->grupo);
			  $MContribuinte->cnpj	 	= $this->cnpj;
			  $MContribuinte->ambiente 	= $this->ambiente;
			  $retornoContribuinte 		= $MContribuinte->selectCNPJAmbiente();

			  $this->contingencia 	= $retornoContribuinte[0]['contigencia'];
			  $this->uf_emitente  	= $retornoContribuinte[0]['uf'];
			  
			  if($this->contingencia == "02" 	/* Ver como que faz esta contingencia*/){
				// danfe_impressa? ""-Nota nova não informado ainda "N"-Ainda não impressa "S"-já impressa
				// "C"-impressa em contingência (falta emitir em homologação)
				/*if($retornoContribuinte[0]['danfe_impressa'] != "C"){
					// Não continua com emissão, ir para o próximo registro do while
					$cont++;
					continue;
				}*/
				$this->CLog->mMensagem("Selecionada a contigencia 02");
			  }else{
				$this->CLog->mMensagem("Nao foi selecionada contigencia 02");
				/* Compor a chave da NFE (chamar o converte NFE apenas para compor a chave novamente e atualizar no XML) */
				$ConvertMDFePHP = new ConvertMDFePHP();
				$xmlRetorno = $ConvertMDFePHP->montaChaveXMLExterno($this->xml);
				if(!$xmlRetorno){
					$this->CLog->mMensagem("Erro ao montar chave de XML Externo: ".$ConvertNFePHP->mensagemErro);
					// Consultar proxima nota recebida.
					$cont++;
					continue;
				}else{
					$this->CLog->mMensagem("Chave montada com sucesso!");
					$MMDFe->cnpj 		= $this->cnpj;
					$MMDFe->numero		= $this->numero;
					$MMDFe->serie		= $this->serie;
					$MMDFe->ambiente	= $this->ambiente;
					$MMDFe->xml			= $this->xml = $xmlRetorno;
					$MMDFe->update();
				}
			  }

			  if(!$retornoContribuinte){
				$this->CLog->mMensagem("Erro no retorno dos contribuintes:");
			    $this->mensagemErro = $MContribuinte->mensagemErro;
				return false;
			  }
			$this->CLog->mMensagem("Cadastrar novo Lote na base de dados e recupera numero automaticamente!");
			  // Cadastrar novo Lote na base de dados e recupera numero gerado automaticamente
			  $MLote = new MLote($this->grupo);
			  
			  $MLote->cnpj = $this->cnpj;
			  $MLote->versao = $this->versao;
			  $MLote->recibo = "";
			  $MLote->status = "";
			  $MLote->ambiente = $this->ambiente;
			  $MLote->contingencia =  $this->contingencia;
			  $MLote->data_hora =  date('Y-m-d H:i:s');

			  $return = $MLote->insert();
			  if(!$return || $return == null){
				  $this->CLog->mMensagem("Falha ao inserir o Lote: ".$MLote->mensagemErro);
				  $this->mensagemErro = $MLote->mensagemErro;
				  return false;
			  }
	  
			  $this->lote = $return[0]['ult_id'];

			  // Atualizar status da Nota / Lote / XML
			  $MMDFe->cnpj		= $this->cnpj;
			  $MMDFe->numero	= $this->numero;
			  $MMDFe->serie		= $this->serie;
			  $MMDFe->ambiente	= $this->ambiente;
			  $MMDFe->id_lote	= $this->lote;

			  // Gravar LOG de registro de novo Lote
			  $MLog = new MLog($this->grupo);
			  $MLog->cnpj		= $this->cnpj;
			  $MLog->numero		= $this->numero;
			  $MLog->serie		= $this->serie;
			  $MLog->ambiente	= $this->ambiente;
			  $MLog->data_hora	= "";
			  $MLog->usuario	= "AUTOMATICO"; // Obter o lk-usuario
			  $MLog->evento		= "Lote SEFAZ";
			  $MLog->descricao	= "LOTE ".$this->lote." gerado e pronto para transmitir";
			  $MLog->insert();

			  $MDFeNFePHP = new MDFeNFePHP($retornoContribuinte[0]);
			  if(!$MDFeNFePHP){
				$this->CLog->mMensagem("Erro ao instanciar MDFeNFePHP");
			  	return false;
			  }

			  //Selecionar o tipo de contingencia
/*			  switch($this->contingencia){
				case "03":
					$ToolsNFePHP = new ToolsNFePHP($this->cnpj, $this->ambiente,$this->grupo, 2,false,true);
					if(!$ToolsNFePHP){
						return false;
					}
				break;
				case "06":
				case "07":
					$ToolsNFePHP = new ToolsNFePHP($this->cnpj, $this->ambiente, $this->grupo,2,false,"SVC");
					if(!$ToolsNFePHP){
						return false;
					}
				break;
				default:
					$MDFeNFePHP = new MDFeNFePHP($retornoContribuinte[0]);
					if(!$MDFeNFePHP){
						ECHO "nhacaaaaaa";
						return false;
						
					}
					echo "ateh aqui foi ";exit();
				break;
			  }*/
			  //TODO VOLTAR DAQUI

			  $this->xml = $MDFeNFePHP->signXML($this->xml, 'infMDFe');

			  if(!$this->xml){
				  $this->CLog->mMensagem("Erro ao Assinar o XML");
				  $this->mensagemErro = $MDFeNFePHP->errMsg;
				  return false;
			  }
			  $this->CLog->mMensagem("Assinado XML com sucesso");
			  
			  // Valida XML
			  $retoronErro = '';
			  //se der erro no validador gravar uma critica e criar um novo status
			  $retorno = $MDFeNFePHP->validXML($this->xml,'',$retornoErro); // Não há necessidade de passar o nome do xsd pois ele pega no cadastro do contribuinte
			  if(!$retorno){
				  $this->CLog->mMensagem("Erro ao validar o XML, ira gerar critica");
				// Gravar Log com Erro
				  $MLog->data_hora	= date("Y-m-d H:i:s");
				  $MLog->usuario	= "AUTOMATICO";
				  $MLog->evento		= "Validar XML";
			    // Gravar Crítica com o Erro
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->cnpj				= $this->cnpj;
				  $MCritica->ambiente			= $this->ambiente;
				  $MCritica->id_lote			= $this->lote;
				  $MCritica->numero				= $this->numero;
				  $MCritica->serie				= $this->serie;
				  $MCritica->sequencia			= "";
				  $MCritica->codigo_referencia	= "";
				  $MCritica->data_hora_critica	= date("Y-m-d H:i:s");

				  // TODO GRAVAR LOG E CRITICA
				  foreach ($retornoErro as $er){
					echo $er."\n";
					  $MCritica->descricao .= $er." <br>";
					  $MLog->descricao .= $er." <br>";
				  }
				  // Mover erro de integracao
				  $MMDFe->status = "04";

				  $MCritica->insert();
				  $MLog->insert();
				  $MMDFe->update();
				  return true;
			  }
			  $this->CLog->mMensagem("XML validado com sucesso");
			  
			  // Submete Lote no SEFAZ
			  $retornoSefaz = $MDFeNFePHP->sendLot($this->xml,$this->lote);

			  $this->statusSefaz		= $retornoSefaz['cStat'];
			  $this->motivoSefaz 		= $retornoSefaz['xMotivo'];
			  $this->dhRecebimentoSefaz	= str_replace("T"," ",$retornoSefaz['dhRecbto']);
			  $this->nReciboSefaz		= $retornoSefaz['nRec'];
	  
			  if (!$retornoSefaz){
				  $this->CLog->mMensagem("Erro ao enviar o Lote: ".$MDFeNFePHP->errMsg);
				  $this->mensagemErro = $MDFeNFePHP->errMsg;
				  $MLog->descricao = $this->mensagemErro;
				  $MLog->insert();
				  return false;
			  }
			  $this->CLog->mMensagem("Lote enviado com sucesso, status:".$this->statusSefaz." motivo:".$this->motivoSefaz." dhRecebimentoSefaz:".$this->dhRecebimentoSefaz." nRecebimentoSefaz:".$this->nReciboSefaz);

			  // Atualiza o Status do Lote, idependente do retorno.
			  $this->__mAtualizarLote();
			  
			  // 103 - SUCESSO
			  if($this->statusSefaz == "103"){
				  $this->CLog->mMensagem("Atualizar lote: 103");
				  $MMDFe->damdfe_impressa 	= "N";	// Danfe ainda não impressa
				  $MMDFe->status			= "02"; // Nota aguardando retorno SEFAZ
				  $MMDFe->xml				= $this->xml;
				  if(!$MMDFe->update()){
					  $this->CLog->mMensagem("Erro ao efetuar update da MMDFe");
					  $this->mensagemErro = $MMDFe->mensagemErro;
					  return false;
				  }

				  // Gravar LOG de sucesso do envio da nota
				  $MLog->data_hora	= date('Y-m-d h:i:s');
				  $MLog->usuario	= "AUTOMATICO"; // Obter o lk-usuario
				  $MLog->evento		= "LOTE ".$this->lote." recebido com sucesso (103)";
				  $MLog->insert();				  
				  $cont++;
				  continue;
			  }
			  
			  // diferente de nao comunicou 108 e 109 (214 - tamanho acima de 500kb, 243 - XML dados mal formatado)
			  if($this->statusSefaz != "108" && $this->statusSefaz != "109"){
				  $this->CLog->mMensagem("Atualizado lote:".$this->statusSefaz);
				  $MCritica = new MCritica($this->grupo);
				  $MCritica->cnpj 				= $this->cnpj;
				  $MCritica->numero				= $this->numero;
				  $MCritica->serie				= $this->serie;
				  $MCritica->ambiente			= $this->ambiente;
				  $MCritica->codigo_referencia	= $this->statusSefaz;
				  $MCritica->descricao			= $this->motivoSefaz;
				  $MCritica->data_hora_critica	= $this->dhRecebimentoSefaz;
				  
				  $retorno = $MCritica->insert();
				  // Não trata caso de erro na iserção

				  $MMDFe->status	= "05";
				  if(!$MMDFe->update()){
					$this->CLog->mMensagem("Erro ao atualizar MMDFe");
					$this->mensagemErro = $MMDFe->mensagemErro;
					return false;
				  }

			// Gerar arquivo de integração de ERP
			/*	 $CIntegracaoERP = new CIntegracaoERP($this->grupo);
				 $pArray = array();
				 $pArray['cnpj'] 			= $this->cnpj;
				 $pArray['uf_emitente'] 	= $this->uf_emitente ;
			 	 $pArray['ano_mes'] 		= substr($this->dhRecebimentoSefaz,2,2).substr($this->dhRecebimentoSefaz,5,2);
				 $pArray['modelo_nota'] 	= $this->model;
				 $pArray['serie'] 			= $this->serie;
				 $pArray['numero'] 			= $this->numero;
				 $pArray['status'] 			= "7";
				 $pArray['chave'] 			= $this->chave;

				 $this->dhRecebimentoSefaz = str_replace(" ","T",str_replace("/","-",$this->dhRecebimentoSefaz));
				 $CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
				 $retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimentoSefaz, $pArray);
				 if(!$retorno){
					$this->mensagemErro = $CIntegracaoERP->mensagemErro;
					return false;
				 }*/
			  }
			  $cont++;
			  $this->CLog->mMensagem("continua proxima nota");
		}
		$this->CLog->mMensagem("Encerra envio de recebidas");
	}
	
/**
 * @method mCosultarLote
 * @autor Guilherme Silva
 * @TODO  Revisar código com DFD e testar todas alternativas
 */
	public function mConsultarLote(){
		$this->CLog->mMensagem("Consultar o Lote");
		$MLote = new MLote($this->grupo);

		$retornoLote = $MLote->selectProcessados();

		if(!$retornoLote){
		  $this->CLog->mMensagem("Erro ao selecionar processados: ".$MLote->mensagemErro);
		  $this->mensagemErro = $MLote->mensagemErro;
		  return false;
		}
		$this->CLog->mMensagem("Selecionado processados");

		$cont=0; // Contador de registro para NFs processados
		while(is_array($retornoLote[$cont])){
				$this->lote 		= $MLote->id			= $retornoLote[0]['id'];
				$this->cnpj			= $MLote->cnpj			= $retornoLote[0]['cnpj'];
				$this->versao		= $MLote->versao		= $retornoLote[0]['versao'];
				$this->recibo		= $MLote->recibo		= $retornoLote[0]['recibo']; // 15 posicoes
				$this->status		= $MLote->status		= $retornoLote[0]['status'];
				$this->ambiente		= $MLote->ambiente		= $retornoLote[0]['ambiente'];
				$this->contingencia	= $MLote->contingencia	= $retornoLote[0]['contingencia'];

				$this->CLog->mMensagem("Consultar lote/id:".$this->lote." cnpj:".$this->cnpj." versao:".$this->versao." recibo:".$this->recibo." status:".$this->status." ambiente:".$this->ambiente." contigencia:".$this->contingencia);

				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $this->cnpj;
				$retornoContribuinte = $MContribuinte->selectAll();
				
				$MDFeNFePHP = new MDFeNFePHP($retornoContribuinte[0]);
				if(!$MDFeNFePHP){
					$this->CLog->mMensagem("Erro ao instanciar MDFeNFePHP");
					return false;
				}
				//Selecionar o tipo de contingencia
				/*switch($this->contingencia){
					case "03":
						$ToolsNFePHP = new ToolsNFePHP($this->cnpj, $this->ambiente,$this->grupo, 2,false,true);
						if(!$ToolsNFePHP){
							return false;
						}
					break;
					case "06":
					case "07":
						$ToolsNFePHP = new ToolsNFePHP($this->cnpj, $this->ambiente, $this->grupo,2,false,"SVC");
						if(!$ToolsNFePHP){
							return false;
						}
					break;
					default:
						$MDFeNFePHP = new MDFeNFePHP($this->cnpj, $this->ambiente, $this->grupo);
						if(!$MDFeNFePHP){
							return false;
						}
					break;
				}*/ 
				
				$retornoConsulta = $MDFeNFePHP->getProtocol($MLote->recibo,'',$MLote->ambiente,'2');

				if(!$retornoConsulta){
					$this->CLog->mMensagem("Erro ao chamar o getProtocol");
					$this->mensagemErro = $MDFeNFePHP->errMsg;
					return false;
				}

				// Obter variaveis de retorno
				$this->status 		 		= $retornoConsulta['cStat'];
				$this->motivo		 		= $retornoConsulta['xMotivo'];
				$this->status_nota	 		= $retornoConsulta['protcStat'];
				$this->motivo_nota	 		= $retornoConsulta['protxMotivo'];
				$this->nReciboSefaz	 		= $retornoConsulta['nProt'];
				$this->ambiente		 		= $retornoConsulta['tpAmb'];
				$this->xmlProtocolo	 		= $retornoConsulta['xmlProtocolo'];
				$this->chave	 	 		= $retornoConsulta['chMDFe'];
				$this->dhRecebimentoSefaz 	= str_replace("T"," ",$retornoConsulta['dhRecbto']);
				$this->uf_emitente	 		= $retornoConsulta['cUF'];
				$this->numProtocolo	 		= $retornoConsulta['nProt'];
				$this->CLog->mMensagem("consulta efetuada com sucesso, status:".$this->status." motivo:".$this->motivo." status_nota:".$this->status_motivo." motivo_nota:".$this->motivo_nota." nReciboSefaz:".$this->nReciboSefaz." ambiente:".$this->ambiente." xmlProtocol:".$this->xmlProtocol." chave:".$this->chave." dhRececto:".$this->dhRecebimentoSefaz." uf_emitente:".$this->uf_emitente." num_protocolo:".$this->numProtocolo);
				// Atualiza Status do Lote
				$MLote->recibo	= $this->nReciboSefaz;
				$MLote->status	= $this->status;
				$retorno = $MLote->update();

				if(!$retorno){
					$this->CLog->mMensagem("Erro no update da tabela Lote: ".$MLote->mensagemErro);
					$this->mensagemErro = $MLote->mensagemErro;
				}
				$this->CLog->mMensagem("Tabela lote atualizada com sucesso");

				// Obter dados da Nota do Lote para fazer as devidas modificações.
				$MMDFe = new MMDFe($this->grupo);
				$MMDFe->id_lote = trim($this->lote);
				$retorno = $MMDFe->selectAllMestre();
				if(!$retorno){
					$this->CLog->mMensagem("Erro ao selecionar mestre da nota");
					$this->mensagemErro = $MMDFe->mensagemErro;
					return false;
				}
				$MMDFe->numero 		= $this->numero		= $retorno[0]['numero'];
				$MMDFe->serie		= $this->serie		= $retorno[0]['serie'];
				$MMDFe->cnpj		= $this->cnpj		= $retorno[0]['cnpj'];
				$MMDFe->ambiente	= $this->ambiente	= $retorno[0]['ambiente'];
				$MMDFe->xml			= $this->xml		= base64_decode($retorno[0]['xml']);
				$this->CLog->mMensagem("Selecionada numero:".$this->numero." serie:".$this->serie." cnpj:".$this->cnpj." ambiente:".$this->ambiente);

				// Verifica se houve falta de comunicação com SEFAZ
				switch($this->status){
					// Lote processado com sucesso (104)
					case "104":
						// Notas autorizadas o uso (100) atualizar o status e xml com autorização apendada
						if(trim($this->status_nota) == "100"){
							$xml = $MDFeNFePHP->addProt($this->xml, $this->xmlProtocolo);
							$this->xml = $xml;
							$MMDFe->status 				= "03";
							$MMDFe->xml 				= $this->xml;
							$MMDFe->id_lote				= $this->lote;
							$MMDFe->chave				= $this->chave;
							$MMDFe->numero_protocolo	= $this->numProtocolo;
							$this->CLog->mMensagem("Lote processado com sucesso(104) status_nota:100 status_sys:".$MMDFe->status." id_lote:".$MMDFe->id_lote." chave:".$MMDFe->chave." numero_protocolo:".$MMDFe->numero_protocolo);
							$retorno = $MMDFe->update();
							if(!$retorno){
								$this->CLog->mMensagem("Erro no update do MMDFe: ".$MMDFe->mensagemErro);
								$this->mensagemErro = $MMDFe->mensagemErro;
								return false;
							}
							$this->CLog->mMensagem("Update efetuado com sucesso");
							// Gravar arquivo backup de nota autorizada

							$CBackup = new CBackup($this->grupo);
							$retBkp = $CBackup->mGuardarXml($this->xml,$this->chave, $this->cnpj, 'mdfe');
							if(!$retBkp){
								$this->CLog->mMensagem("Erro ao efetuar backup do mdfe");
								echo $retBkp->mensagemErro;
							}
							$this->CLog->mMensagem("Efetuado backup da nota");
						// 110 - Nota Denegada
						}elseif($this->status_nota == "110" ||
								$this->status_nota == "301" ||
								$this->status_nota == "302"	){
							$MMDFe->status 	= "05";
							$MMDFe->xml 		= $this->xml;
							$MMDFe->id_lote		= $this->lote;
							$MMDFe->chave		= $this->chave;
							$this->CLog->mMensagem("Lote processado com sucesso(104) Nota denegada status_nota:".$this->status_nota." status:".$MMDFe->status." id_lote:".$this->lote." chave:".$this->chave);
							$retorno = $MMDFe->update();
							if(!$retorno){
								$this->CLog->mMensagem("Efetuado ao efetuar update da nota");
								$this->mensagemErro = $MMDFe->mensagemErro;
								return false;
							}
							$this->CLog->mMensagem("Update efetuado com sucesso");
						// Nota Fiscal rejeitada, gravar critica
						}else{
							$MCritica = new MCritica($this->grupo);
							$MCritica->cnpj	= $this->cnpj;
							$MCritica->numero				= $this->numero;
							$MCritica->serie				= $this->serie;
							$MCritica->ambiente				= $this->ambiente;
							$MCritica->codigo_referencia	= $this->status_nota;
							$MCritica->descricao		    = $this->motivo_nota;
							$this->CLog->mMensagem("Lote processado com sucesso(104) Sefaz com critica status_nota:".$this->status_nota." numero:".$this->numero." serie:".$this->serie." ambiente:".$this->ambiente." motivo_nota:".$this->motivo_nota );
							$retorno = $MCritica->insert();
							if(!$retorno){
								$this->CLog->mMensagem("Erro ao inserir critica");
								$this->mensagemErro = $MCritica->mensagemErro;
								return false;
							}
							$this->CLog->mMensagem("Critica inserida");
							// Assume status de rejeitada
							$MMDFe->status 		= "04";
							$MMDFe->xml 		= $this->xml;
							$MMDFe->id_lote	= $this->lote;
							$MMDFe->chave		= $this->chave;
							$retorno = $MMDFe->update();
							if(!$retorno){
								$this->CLog->mMensagem("Erro ao atualizar MDFe");
								$this->mensagemErro = $MMDFe->mensagemErro;
								return false;
							}
							$this->CLog->mMensagem("MDFe atualizada com sucesso");
						}

						// Gerar arquivo de integração de ERP
						$CIntegracaoERP = new CIntegracaoERP($this->grupo);

						$pArray['cnpj_emitente']= $this->cnpj;
						$pArray['uf_emitente'] 	= $this->uf_emitente;
						$pArray['ano_mes'] 		= substr($this->dhRecebimentoSefaz,2,2).substr($this->dhRecebimentoSefaz,5,2);
						$pArray['modelo_nota'] 	= $this->modelo;
						$pArray['serie_nota'] 	= $this->serie;
						$pArray['numero_nota'] 	= $this->numero;
						if($this->status_nota == "100"){
							$pArray['status'] 		= "6";
						}elseif($this->status_nota == "110"){
							$pArray['status'] 		= "7";
						}else{
							$pArray['status'] 		= "8";
						}
						$pArray['chave'] 			= $this->chave;
						
						$MContribuinte 				= new MContribuinte($this->grupo);
						$MContribuinte->cnpj	 	= $this->cnpj;
						$MContribuinte->ambiente 	= $this->ambiente;
						
						$this->CLog->mMensagem("Seleciona contribuinte para montar retorno ao COBOL");
						
						$retornoContribuinte 		= $MContribuinte->selectCNPJAmbiente();
						if(!$retornoContribuinte){
							$this->CLog->mMensagem("Erro ao selecionar contribuinte");
							$this->mensagemErro = $MContribuinte->mensagemErro;
							return false;
						}
						
						$CIntegracaoERP->contribuinteBase = $retornoContribuinte[0]['diretorio_base'];
						$this->CLog->mMensagem("Retornar consulta ao COBOL");
						print_r($pArray);
						
						$retorno = $CIntegracaoERP->mRetornoConsulta($retornoContribuinte[0]['diretorio_integracao'], $this->dhRecebimentoSefaz, $pArray);
						if(!$retorno){
							$this->CLog->mMensagem("Erro ao montar retorno COBOL: ".$CIntegracaoERP->mensagemErro);
							$this->mensagemErro = $CIntegracaoERP->mensagemErro;
							return false;
						}
						
						// Gravar Log
						$MLog = new MLog($this->grupo);
						$MLog->cnpj			= $this->cnpj;
						$MLog->numero		= $this->numero;
						$MLog->serie		= $this->serie;
						$MLog->ambiente		= $this->ambiente;
						$MLog->data_hora	= $this->dhRecebimentoSefaz;
						$MLog->usuario		= "AUTOMATICO"; // Obter o lk-usuario;
						$MLog->evento		= "Consulta Lote"; // Obter o lk-usuario;
						$MLog->descricao	= "Consulta do Lote ".$this->lote.", No. Recibo ".$MLote->recibo." Status Lote (".$this->status.") ".$this->motivo." Status Nota (".$this->status_nota.") ".$this->motivo_nota;
						$MLog->insert();

						return true;
					break;

					// Staus de Falha de comunicação (108,109) ou ainda em processamento (105) abortar
					case "105":
					case "108":
					case "109":
						$this->CLog->mMensagem("Falha de comunicacao: ".$this->motivo);
						$this->mensagemErro = $this->motivo;
						return false;
					break;
					// Status desconhecido
					default:
						$this->CLog->mMensagem("Status retorno nota desconhecido: status:".$this->status." motivo:".$this->motivo);
						// Grava Critica
						$MCritica = new MCritica($this->grupo);
						$MCritica->cnpj					= $this->cnpj;
						$MCritica->numero				= $this->numero;
						$MCritica->serie				= $this->serie;
						$MCritica->ambiente				= $this->ambiente;
						$MCritica->codigo_referencia	= $this->status;
						$MCritica->evento				= "Consulta Lote";
						$MCritica->descricao			= $this->motivo;
						$retorno = $MCritica->insert();
						if(!$retorno){
							$this->mensagemErro = $MCritica->mensagemErro;
							return false;
						}
						
						// Atualiza lote com erro e assume Denegada
						$MMDFe->status = "05";
						$this->CLog->mMensagem("Atualizar lote de erro e assume 05 denegada");
						$retorno = $MMDFe->update();
						if(!$retorno){
							$this->CLog->mMensagem("Erro ao atualizar MDFe");
							$this->mensagemErro = $MMDFe->mensagemErro;
							return false;
						}
						// Finaliza processamento
						return true;
					break;
				}

			$cont++;
			$this->CLog->mMensagem("Seleciona proxima nota");
		}
		$this->CLog->mMensagem("Fim da selecao de notas");
	}

	private function __mAtualizarLote(){
		// Atualizar Lote na base de dados
		$MLote = new MLote($this->grupo);

		$MLote->id 			= $this->lote;
		$MLote->cnpj 		= $this->cnpj;
		$MLote->ambiente 	= $this->ambiente;
		$MLote->recibo 		= $this->nReciboSefaz;
		$MLote->status 		= $this->statusSefaz;

		$this->CLog->mMensagem("Atualizar lote:".$this->lote." cnpj:".$this->cnpj." ambiente:".$this->ambiente." recibo:".$this->nReciboSefaz." statusSefaz:".$this->statusSefaz);

		$return = $MLote->update();
		if(!$return || $return == null){
			$this->CLog->mMensagem("Update da nota fiscal");
			$this->mensagemErro = $MLote->mensagemErro;
			return false;
		}
	}
}
?>