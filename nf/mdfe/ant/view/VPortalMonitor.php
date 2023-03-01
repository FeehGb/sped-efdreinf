<?php
//error_reporting(0);
/**
 * @name      	VPortalMonitor
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Programa elaborado para efetuar comunicação do HTML com as classes control.
 * @TODO 		Fazer tudo
*/

	define('__ROOT__', dirname(dirname(__FILE__))); 
	require_once(__ROOT__."/model/MContribuinte.php");
	require_once(__ROOT__."/model/MMDFe.php");
	require_once(__ROOT__."/model/MCritica.php");
	require_once(__ROOT__."/control/CGerarDAMDFe.php");
	require_once(__ROOT__."/control/CCancelar.php");
	require_once(__ROOT__."/control/CEncerrar.php");
	require_once(__ROOT__."/libs/MDFeNFePHP.class.php");
/*	require_once("/var/www/html/nf/nfe/novo/control/CEmail.php");
	require_once("/var/www/html/nf/nfe/novo/control/CGerarDanfe.php");
	require_once("/var/www/html/nf/nfe/novo/control/CConsultaCadastro.php");
	require_once("/var/www/html/nf/nfe/novo/model/MNotaFiscal.php");
	require_once("/var/www/html/nf/nfe/novo/model/MInutilizacao.php");
	require_once("/var/www/html/nf/nfe/novo/libs/CUploadFile.php");
	*/
	
	// Definição das variáveis
	$funcao = trim($_POST['hFuncao']);
	
	$filialGrupo = str_replace(" ","",strtolower($_POST['hFilialGrupo']));
	
	// Verificação qual grupo de chamadas irá carregar
	
	if(substr($funcao,0,5) == "MDFE-"){
		fNfe($funcao);
	}elseif(substr($funcao,0,13) == "CONTRIBUINTE-"){
		fContribuinte($funcao);
	}elseif(substr($funcao,0,12) == "WEB-SERVICE-"){
		fWebService($funcao);
	}elseif(substr($funcao,0,11) == "INUTILIZAR-"){
		fInutilizar($funcao);
	}elseif(substr($funcao,0,9) == "CANCELAR-"){
		fCancelar($funcao);
	}elseif(substr($funcao,0,13) == "ENCERRAMENTO-"){
		fEncerrar($funcao);
	}elseif(substr($funcao,0,8)  == "SERVICO-"){
		fServico($funcao);
	}elseif(substr($funcao,0,3)  == "CC-"){
		fCC($funcao);
	}elseif(substr($funcao,0,7)  == "DAMDFE-"){
		fDanfe($funcao);
	}elseif(substr($funcao,0,8)  == "CADCONT-"){
		fCadCont($funcao);
	}elseif(substr($funcao,0,7) == "CRITICA"){
		fCritica($funcao);
	}
	
	// Tratar as funções que são provenientes das Notas Fiscais
	
	function fNfe($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		$periodo_ini	= substr($_POST['tConsultaPeriodoInicial'],6,4)."-".substr($_POST['tConsultaPeriodoInicial'],3,2)."-".substr($_POST['tConsultaPeriodoInicial'],0,2)." 00:00:00";
		$periodo_fim	= substr($_POST['tConsultaPeriodoFinal'],6,4)."-".substr($_POST['tConsultaPeriodoFinal'],3,2)."-".substr($_POST['tConsultaPeriodoFinal'],0,2)." 23:59:59";

		switch($pFuncao){
			case "MDFE-LISTAR-CONTRIBUINTES":
				// Instanciar a classe Contribuinte para trabalhar com o contribuinte
				$MContribuinte = new MContribuinte($filialGrupo);
				$return = $MContribuinte->selectAll();
				$i=0;
				foreach($return as $conteudo){
					$retornoJson['retorno'][$i]['cnpj'] 			   	= $conteudo['cnpj'];
					$retornoJson['retorno'][$i]['ambiente'] 		 	= $conteudo['ambiente'];
					$retornoJson['retorno'][$i]['cod_emp_fil_softdib'] 	= $conteudo['cod_emp_fil_softdib'];
					$retornoJson['retorno'][$i]['razao_social'] 	   	= $conteudo['razao_social']; 
					$retornoJson['retorno'][$i]['contigencia'] 	   		= $conteudo['contigencia'];
					$i++;
				}

				$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
				
				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-LISTAR-PENDENTES":
				$MMDFe = new MMDFe($filialGrupo);
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

				$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa
						FROM `mdfe_".$filialGrupo."`.`MDFE` ";
				$sql .= "WHERE cnpj = '".$contribuinte[0]."' AND ";
				$sql .= " ambiente = '".$contribuinte[1]."' AND ";
				$sql .= " ( status = '01' OR status = '02' ) AND ";
				$sql .= " data_emissao >= '".$periodo_ini."' AND data_emissao <= '".$periodo_fim."' ";

				$return = $MMDFe->selectAllMestre($sql);

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CNf->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-LISTAR-AUTORIZADAS":
				$MMDFe = new MMDFe($filialGrupo);
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

				$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa
						FROM `mdfe_".$filialGrupo."`.`MDFE` ";
				$sql .= "WHERE cnpj = '".$contribuinte[0]."' AND ";
				$sql .= " ambiente = '".$contribuinte[1]."' AND ";
				$sql .= " status = '03' AND ";
				$sql .= " data_emissao >= '".$periodo_ini."' AND data_emissao <= '".$periodo_fim."' ";

				$return = $MMDFe->selectAllMestre($sql);

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CNf->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-LISTAR-ENCERRADAS":
				$MMDFe = new MMDFe($filialGrupo);
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

				$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa
						FROM `mdfe_".$filialGrupo."`.`MDFE` ";
				$sql .= "WHERE cnpj = '".$contribuinte[0]."' AND ";
				$sql .= " ambiente = '".$contribuinte[1]."' AND ";
				$sql .= " status = '09' AND ";
				$sql .= " data_emissao >= '".$periodo_ini."' AND data_emissao <= '".$periodo_fim."' ";

				$return = $MMDFe->selectAllMestre($sql);

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CNf->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-LISTAR-CANCELADAS":
				$MMDFe = new MMDFe($filialGrupo);
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

				$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa
						FROM `mdfe_".$filialGrupo."`.`MDFE` ";
				$sql .= "WHERE cnpj = '".$contribuinte[0]."' AND ";
				$sql .= " ambiente = '".$contribuinte[1]."' AND ";
				$sql .= " status = '06' AND ";
				$sql .= " data_emissao >= '".$periodo_ini."' AND data_emissao <= '".$periodo_fim."' ";

				$return = $MMDFe->selectAllMestre($sql);

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CNf->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-LISTAR-INCONSISTENTES":
				$MMDFe = new MMDFe($filialGrupo);
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

				$sql = "SELECT 	
						cnpj,
						ambiente,
						id_lote,
						numero,
						serie,
						versao,
						tipo_emitente,
						cod_empresa_filial_softdib,
						nome_emissor,
						uf_carregamento,
						uf_descarregamento,
						status,
						tipo_emissao,
						data_emissao,
						valor_total_carga,
						quantidade_nfe,
						unidade_peso_bruto,
						peso_bruto,
						chave,
						numero_protocolo,
						damdfe_impressa
						FROM `mdfe_".$filialGrupo."`.`MDFE` ";
				$sql .= "WHERE cnpj = '".$contribuinte[0]."' AND ";
				$sql .= " ambiente = '".$contribuinte[1]."' AND ";
				$sql .= " ( status = '04' OR status = '05' ) AND ";
				$sql .= " data_emissao >= '".$periodo_ini."' AND data_emissao <= '".$periodo_fim."' ";

				$return = $MMDFe->selectAllMestre($sql);

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CNf->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-ATUALIZAR-SEFAZ":
				$MContribuinte = new MContribuinte($filialGrupo);
				$MContribuinte->cnpj_emitente 	= $_POST['hCnpj'];
				$retornoContribuinte = $MContribuinte->selectAll();
				if(!$retornoContribuinte){
					$retornoJson['retorno']	 = $return;
					$retornoJson['mensagem'] = $MContribuinte->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$MMDFe = new MMDFe($filialGrupo);
				$MMDFe->cnpj		= trim($_POST['hCnpj']);
				$MMDFe->ambiente 	= trim($_POST['hAmbiente']);
				$MMDFe->numero		= trim($_POST['hAtualizaNF']);
				$MMDFe->serie		= trim($_POST['hAtualizaSerie']);

				$return = $MMDFe->selectAllMestre($sql);

				if(!$return){
					$retornoJson['retorno']	 = $return;
					$retornoJson['mensagem'] = $MMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$MDFeNFePHP = new MDFeNFePHP($retornoContribuinte[0]);
				$retorno = $MDFeNFePHP->getProtocol('', $return[0]['chave'], $return[0]['ambiente'],$aRetorno);

				if(is_numeric($retorno['nProt'])){                   
				        $MMDFe->numero_protocolo = $retorno['nProt'];
			        }                                                    
			        
				switch($retorno['cStat']){
					case '100':
						$MMDFe->status = '03';
						$retornoJson['mensagem'] = "Nota Fiscal Autorizada";
					break;
					case '101':
						$MMDFe->status = '06';
						
						$retornoJson['mensagem'] = "Nota Fiscal Cancelada";
					break;
					case '132':
						$MMDFe->status = '09';
						$MMDFe->numero_protocolo = $retorno['nProt'];
						$retornoJson['mensagem'] = "Nota Fiscal Encerrada";
					break;
				}

				$retorno = $MMDFe->update();
				if(!$retorno){
					$retornoJson['retorno']	 = $return;
					$retornoJson['mensagem'] = $MMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$retornoJson['retorno'] = true;

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-VISUALIZAR":
				$MNf = new MNotaFiscal($filialGrupo);

				$sql = "SELECT xml FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE "
							."cnpj_emitente = '".trim($_POST['hViewCnpj'])."' AND "
							."ambiente = '".trim($_POST['hViewAmbiente'])."' AND "
							."numero_nota = '".trim($_POST['hViewNumero'])."' AND "
							."serie_nota = '".trim($_POST['hViewSerie'])."'";
							
				$return = $MNf->selectAllMestre($sql);

				if(!$return){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MNf->mensagemErro;

					echo json_encode($retornoJson);
					exit();
				}

				$xml = simplexml_load_string(base64_decode($return[0]['xml']));

				$retornoJson['retorno']	 = $xml;
				$retornoJson['mensagem'] = "";

				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-EMAIL":
				$retornoJson['mensagem'] = "Email enviado com sucesso";
				
				$CContribuinte = new CContribuinte($filialGrupo);
				$cnpj = explode("-",$_POST['sContribuinte']);
				$CContribuinte->cnpj 		= $cnpj[0];
				$CContribuinte->ambiente 	= $cnpj[1];

				$return = $CContribuinte->mObterContribuinte();

				if(!$return){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$MNotaFiscal = new MNotaFiscal($filialGrupo);
				$returnNF = $MNotaFiscal->selectAllMestre("SELECT * FROM NOTA_FISCAL
															WHERE cnpj_emitente = '".$cnpj[0]."' AND
															ambiente = '".$cnpj[1]."' AND
															numero_nota = '".trim($_POST['tAutorizadaNF'])."' AND
															serie_nota = '".trim($_POST['tAutorizadaSerie'])."'");
				if(!$returnNF){
					$retornoJson['retorno']		= false;
					$retornoJson['mensagem']	= $MNotaFiscal->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$CEmail = new CEmail($filialGrupo);
				$CEmail->smtp 				 = $return[0]['email_smtp'];
				$CEmail->porta				 = $return[0]['email_porta'];
				$CEmail->ssl				 = $return[0]['email_ssl'];
				$CEmail->usuario			 = $return[0]['email_usuario'];
				$CEmail->senha				 = $return[0]['email_senha'];
				$CEmail->remetente			 = $return[0]['email_remetente'];
				$CEmail->nomeRemetente		 = $return[0]['razao_social'];
				$CEmail->destinatario		 = $_POST['tCCEmail'];

				$CEmail->arrayConteudoResult = $returnNF[0];
				file_put_contents("/var/www/html/nf/nfe/novo/temp/".$returnNF[0]['chave'].".xml", base64_decode($returnNF[0]['xml']));
				$CEmail->attach = "/var/www/html/nf/nfe/novo/temp/".$returnNF[0]['chave'].".xml";
				
				$CGerarDanfe = new CGerarDanfe($filialGrupo);
				$CGerarDanfe->xmlNfe	= $returnNF[0]['xml'];
				$CGerarDanfe->infAdic	= $returnNF[0]['observacao'];
				if(!$CGerarDanfe->gerarDanfe()){
					$retornoJson['mensagem'] = $CGerarDanfe->mensagemErro;
				}else{
					$CEmail->attach2 = "/var/www/html/relatorios/".$CGerarDanfe->danfePdf;
				}
				
				// Verificar se existe Carta de Correcao para Anexar
				$MEvento = new MEvento($filialGrupo);
				$MEvento->NOTA_FISCAL_cnpj_emitente = $cnpj[0];
				$MEvento->NOTA_FISCAL_numero_nota = trim($_POST['tAutorizadaNF']);
				$MEvento->NOTA_FISCAL_serie_nota = trim($_POST['tAutorizadaSerie']);
				$MEvento->NOTA_FISCAL_ambiente = $cnpj[1];
				$MEvento->tipo_evento = "6";
				$retornoEvento = $MEvento->selectMestre();
				if(is_array($retornoEvento)){
					$ind = count($retornoEvento) - 1;
					file_put_contents("/var/www/html/nf/nfe/novo/temp/".$returnNF[0]['chave']."_CC_".$ind.".xml", base64_decode($retornoEvento[$ind]['xml']));
					$CEmail->attach3 = "/var/www/html/nf/nfe/novo/temp/".$returnNF[0]['chave']."_CC_".$ind.".xml";
				}
				
				if(!$CEmail->emailAutorizada()){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $CEmail->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				
				unlink("/var/www/html/nf/nfe/novo/temp/".$returnNF[0]['chave'].".xml");
				
				$retornoJson['retorno']	 = true;
				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-REIMPRIMIR":
				$MMDFe = new MMDFe($filialGrupo);
				$sql = "SELECT xml, chave FROM `mdfe_".$filialGrupo."`.`MDFE` WHERE ".
				" cnpj = '".trim($_POST['hCnpj'])."' AND".
				" numero = '".trim($_POST['hAtualizaNF'])."' AND".
				" serie = '".trim($_POST['hAtualizaSerie'])."' AND".
				" ambiente = '".trim($_POST['hAmbiente'])."'";

				$returnMDFe = $MMDFe->selectAllMestre($sql);

				if(!$returnMDFe){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$CGerarDAMDFe = new CGerarDAMDFe($filialGrupo);

				$CGerarDAMDFe->xmlMDFe = $returnMDFe[0]['xml'];
				$CGerarDAMDFe->infAdic = "";

				if(!$CGerarDAMDFe->gerarDAMDFe()){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $CGerarDAMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$MContribuinte = new MContribuinte($filialGrupo);
				$MContribuinte->cnpj		= $_POST['hCnpj'];
				$MContribuinte->ambiente	= $_POST['hAmbiente'];
				$rContribuinte 				= $MContribuinte->selectAll();

				if(!$rContribuinte){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MContribuinte->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$CGerarDAMDFe->impressaoAutomatica($rContribuinte[0]['server_impressao'],$rContribuinte[0]['server_impressao_comando']);

				$retornoJson['retorno']	 = true;
				$retornoJson['mensagem'] = "Impressao efetuada com sucesso. Verifique sua impressora.";
				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-REIMPRIMIR-RANGE":
				$periodoIni = substr($_POST['tReimprimirPeriodoInicial'],6,4).substr($_POST['tReimprimirPeriodoInicial'],3,2).substr($_POST['tReimprimirPeriodoInicial'],0,2);
				$periodoFim = substr($_POST['tReimprimirPeriodoFinal'],6,4).substr($_POST['tReimprimirPeriodoFinal'],3,2).substr($_POST['tReimprimirPeriodoFinal'],0,2);

				if($_POST['tReimprimirTipo'] == "P"){
					if( !is_numeric($periodoIni) || !is_numeric($periodoFim) ){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "Periodo informado errado, as datas nao sao numericas!";
						echo json_encode($retornoJson);
						exit();
					}
					
					if($periodoIni > $periodoFim){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "A data inicial do periodo nao pode ser maior que a data final!";
						echo json_encode($retornoJson);
						exit();
					}
					
					// Converter periodos para manipular no banco de dados
					$periodoIni = substr($periodoIni,0,4)."-".substr($periodoIni,4,2)."-".substr($periodoIni,6,2);
					$periodoFim = substr($periodoFim,0,4)."-".substr($periodoFim,4,2)."-".substr($periodoFim,6,2);
					

					// Obter informacoes do contribuinte
						$CContribuinte = new CContribuinte($filialGrupo);
						$CContribuinte->cnpj		= $_POST['hCnpj'];
						$CContribuinte->ambiente	= $_POST['hAmbiente'];
						$rContribuinte 				= $CContribuinte->mObterContribuinte();

						if(!$rContribuinte){
							$retornoJson['retorno']	 = false;
							$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}
						
					// Selecionar notas autorizadas que estao no range do periodo informado
						$MNf = new MNotaFiscal($filialGrupo);

						$sql = "SELECT xml, numero_nota, observacao FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE ".
						" status = '03' AND ".
						" cnpj_emitente = '".$_POST['hCnpj']."' AND ".
						" ambiente = '".$_POST['hAmbiente']."' AND ".
						"( data_emissao >= '".$periodoIni."' AND ".
						" data_emissao <= '".$periodoFim."' )";

						$returnNf = $MNf->selectAllMestre($sql);

						if(!$returnNf){
							$retornoJson['retorno']	 = false;
							$retornoJson['mensagem'] = $MNf->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}

						// Loop para gerar todas as DAMDFEs das NFs retornadas no periodo
						$CGerarDanfe = new CGerarDanfe($filialGrupo);
						
						foreach($returnNf as $xmlNfe){
							$CGerarDanfe->xmlNfe = $xmlNfe['xml'];
							$CGerarDanfe->infAdic = $xmlNfe['observacao'];

							if(!$CGerarDanfe->gerarDanfe()){
								$retornoJson['retorno']	 = false;
								$retornoJson['mensagem'] = $CGerarDanfe->mensagemErro;
								echo json_encode($retornoJson);
								exit();
							}
							$CGerarDanfe->impressaoAutomatica($rContribuinte[0]['server_impressao'],$rContribuinte[0]['server_impressao_comando']);
						}

						$retornoJson['retorno']	 = true;
						$retornoJson['mensagem'] = "Range de impressao efetuada com sucesso. Verifique sua impressora.";
						echo json_encode($retornoJson);
						exit();

				}elseif($_POST['tReimprimirTipo'] == "N"){
					if( !is_numeric($_POST['tReimprimirNotaInicial']) || !is_numeric($_POST['tReimprimirNotaFinal']) ){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "Numeracao de notas informada errada, deve ser numerico!";
						echo json_encode($retornoJson);
						exit();
					}

					if($_POST['tReimprimirNotaInicial'] > $_POST['tReimprimirNotaFinal']){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "A numeracao de nota inicial nao pode ser maior que a numeracao final!";
						echo json_encode($retornoJson);
						exit();
					}
					
					$notaIni = $_POST['tReimprimirNotaInicial'];
					$notaFim = $_POST['tReimprimirNotaFinal'];

					// Obter informacoes do contribuinte
						$CContribuinte = new CContribuinte($filialGrupo);
						$CContribuinte->cnpj		= $_POST['hCnpj'];
						$CContribuinte->ambiente	= $_POST['hAmbiente'];
						$rContribuinte 				= $CContribuinte->mObterContribuinte();

						if(!$rContribuinte){
							$retornoJson['retorno']	 = false;
							$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}
						
					// Selecionar notas autorizadas que estao no range do periodo informado
						$MNf = new MNotaFiscal($filialGrupo);

						$sql = "SELECT xml, numero_nota, observacao FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE ".
						" status = '03' AND ".
						" cnpj_emitente = '".$_POST['hCnpj']."' AND ".
						" ambiente = '".$_POST['hAmbiente']."' AND ".
						"( numero_nota >= '".$notaIni."' AND".
						" numero_nota <= '".$notaFim."' )";

						$returnNf = $MNf->selectAllMestre($sql);

						if(!$returnNf){
							$retornoJson['retorno']	 = false;
							$retornoJson['mensagem'] = $MNf->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}

						// Loop para gerar todas as DAMDFEs das NFs retornadas no periodo
						$CGerarDanfe = new CGerarDanfe($filialGrupo);
						
						foreach($returnNf as $xmlNfe){
							$CGerarDanfe->xmlNfe = $xmlNfe['xml'];
							$CGerarDanfe->infAdic = $xmlNfe['observacao'];

							if(!$CGerarDanfe->gerarDanfe()){
								$retornoJson['retorno']	 = false;
								$retornoJson['mensagem'] = $CGerarDanfe->mensagemErro;
								echo json_encode($retornoJson);
								exit();
							}
							$CGerarDanfe->impressaoAutomatica($rContribuinte[0]['server_impressao'],$rContribuinte[0]['server_impressao_comando']);
						}

						$retornoJson['retorno']	 = true;
						$retornoJson['mensagem'] = "Range de impressao efetuada com sucesso. Verifique sua impressora.";
						echo json_encode($retornoJson);
						exit();
				}else{
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = "Nenhum tipo de rage para impressao selecionado!";
					echo json_encode($retornoJson);
					exit();
				}
			break;
			case "MDFE-DOWNLOAD-XML":
				$arrayArquivosZip = "";
				$contArray=0;
			// Obter nota fiscal na tabela de notas
				$MNf = new MNotaFiscal($filialGrupo);

				$sql = "SELECT xml, chave FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE ".
					" cnpj_emitente = '".$_POST['hCnpj']."' AND ".
					" numero_nota = '".$_POST['hAtualizaNF']."' AND ".
					" serie_nota = '".$_POST['hAtualizaSerie']."' AND ".
					" ambiente = '".$_POST['hAmbiente']."'";

				$returnNf = $MNf->selectAllMestre($sql);

				if(!$returnNf){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MNf->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				
				if(is_array($returnNf)){
					foreach($returnNf as $conteudo){
						$arrayArquivosZip[$contArray]['xml'] = $conteudo['xml'];
						$arrayArquivosZip[$contArray]['nome'] = $conteudo['chave']."-procNFe.xml";
						$contArray++;
					}
				}

			// Verificar se tem inutilizadas
				$MInutilizacao = new MInutilizacao($filialGrupo);

				$MInutilizacao->CONTRIBUINTE_cnpj 		= $_POST['hCnpj'];
				$MInutilizacao->numero_nota_inicial 	= $_POST['hAtualizaNF'];
				$MInutilizacao->numero_nota_final 		= $_POST['hAtualizaNF'];
				$MInutilizacao->serie_nota 				= $_POST['hAtualizaSerie'];
				$MInutilizacao->CONTRIBUINTE_ambiente 	= $_POST['hAmbiente'];
				
				$returnInut = $MInutilizacao->select();
				
				if(is_array($returnInut)){
					foreach($returnInut as $conteudo){
						$arrayArquivosZip[$contArray]['xml'] = $conteudo['xml'];
						$arrayArquivosZip[$contArray]['nome'] = $conteudo['numero_nota_inicial']."-".$conteudo['numero_nota_final']."-procInut.xml";
						$contArray++;
					}
				}

				// Verificar se tem eventos (cc, cancelamento)
				$MEvento = new MEvento($filialGrupo);

				$MEvento->NOTA_FISCAL_cnpj_emitente = $_POST['hCnpj'];
				$MEvento->NOTA_FISCAL_numero_nota	= $_POST['hAtualizaNF'];
				$MEvento->NOTA_FISCAL_serie_nota	= $_POST['hAtualizaSerie'];
				$MEvento->NOTA_FISCAL_ambiente		= $_POST['hAmbiente'];

				$pSql = "SELECT e.*, n.chave FROM `EVENTO` as e, NOTA_FISCAL as n
							WHERE
							    e.`NOTA_FISCAL_cnpj_emitente` = ".$_POST['hCnpj']."
							AND e.`NOTA_FISCAL_numero_nota` = ".$_POST['hAtualizaNF']."
							AND e.`NOTA_FISCAL_serie_nota` = ".$_POST['hAtualizaSerie']."
							AND e.`NOTA_FISCAL_ambiente` = ".$_POST['hAmbiente']."
							
							AND e.`NOTA_FISCAL_cnpj_emitente` = n.cnpj_emitente
							AND e.`NOTA_FISCAL_numero_nota` = n.numero_nota
							AND e.`NOTA_FISCAL_serie_nota` = n.serie_nota
							AND e.`NOTA_FISCAL_ambiente` = n.ambiente";

				$returnEvento = $MEvento->selectMestre($pSql);

				if(!$returnEvento){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MEvento->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				
				if(is_array($returnEvento)){
					foreach($returnEvento as $conteudo){
						$arrayArquivosZip[$contArray]['xml'] = $conteudo['xml'];
						if($conteudo['tipo_evento']=="6"){
							$arrayArquivosZip[$contArray]['nome'] = $conteudo['chave']."-110110-procEvento.xml";
						}elseif($conteudo['tipo_evento']=="4"){
							$arrayArquivosZip[$contArray]['nome'] = $conteudo['chave']."-110111-procEvento.xml";
						}else{
							$arrayArquivosZip[$contArray]['nome'] = $conteudo['chave']."-procEvento.xml";
						}
						$contArray++;
					}
				}

				// Gravar XMLs que encontrou na pasta /var/www/html/tmp/ para gerar pacote ZIP, dpois submeter o ZIP para download
				$diretorioTemp = "/var/www/html/relatorios/";
				
				if(!file_exists($diretorioTemp)){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = "Nao foi possivel acessar o diretorio ".$diretorioTemp.", verifique permissao e/ou existencia";
					echo json_encode($retornoJson);
					exit();
				}
				$nomePacote = "NFE_XML_".date("d_m_Y_H_i_s");
				$pastaTemp = $diretorioTemp.$nomePacote."/";
				
				// Apenas 1 Arquivo, fazer do download dele unitariamente
				if(count($arrayArquivosZip)==1){
					if(!file_put_contents($diretorioTemp.$arrayArquivosZip[0]['nome'], base64_decode($arrayArquivosZip[0]['xml']))){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "Nao foi possivel criar o arquivo ".$diretorioTemp.$arrayArquivosZip[0]['nome'].", verifique permissao e/ou existencia do diretorio";
						echo json_encode($retornoJson);
						exit();
					}
					$retornoJson['retorno']	 = true;
					$retornoJson['zip'] = "relatorios/".$arrayArquivosZip[0]['nome'];
					echo json_encode($retornoJson);
					exit();
				}
				
				if(!mkdir($pastaTemp, 0777)){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = "Nao foi possivel criar o diretorio ".$pasta.", verifique permissao e/ou existencia";
					echo json_encode($retornoJson);
					exit();
				}

				$nomeZip = $nomePacote.".zip";

/*				if(!$zip->open($diretorioTemp.$nomeZip, ZIPARCHIVE::CREATE)){
					echo "VPortalMonitor.php: MDFE-DOWNLOAD-XML -> Nao foi possivel criar o arquivo ZIP";
				}
	*/			
				foreach($arrayArquivosZip as $conteudo){
					if(!file_put_contents($pastaTemp.$conteudo['nome'], base64_decode($conteudo['xml']))){
						$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "Nao foi possivel criar o arquivo ".$diretorioTemp.$conteudo['nome'].", verifique permissao e/ou existencia do diretorio";
						echo json_encode($retornoJson);
						exit();
					}
				}
				
				system("cd ".$diretorioTemp." ;zip -r ".$nomeZip." ".$nomePacote."/ > /dev/null");
				system("rm -rf ".$pastaTemp);

				$retornoJson['retorno']	 = true;
				$retornoJson['zip'] = "relatorios/".$nomeZip;
				echo json_encode($retornoJson);
				exit();
			break;
			case "MDFE-XML-RANGE":
				$grupo = $filialGrupo;
				$arrayArquivosZip = "";
			// Obter nota fiscal na tabela de notas
				$MNf = new MNotaFiscal($filialGrupo);

				if($_POST['tReimprimirTipo'] == "P"){
					$periodoIni = substr($_POST['tReimprimirPeriodoInicial'],6,4)."-".substr($_POST['tReimprimirPeriodoInicial'],3,2)."-".substr($_POST['tReimprimirPeriodoInicial'],0,2);
					$periodoFim = substr($_POST['tReimprimirPeriodoFinal'],6,4)."-".substr($_POST['tReimprimirPeriodoFinal'],3,2)."-".substr($_POST['tReimprimirPeriodoFinal'],0,2);
					$sql = "SELECT numero_nota, xml FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE ".
							" (status = '03' OR status = '06') AND ".
							" data_emissao >= '".$periodoIni."' AND ".
							" data_emissao <= '".$periodoFim."' AND ".
							" cnpj_emitente = '".$_POST['hCnpj']."' AND ".
							" ambiente = '".$_POST['hAmbiente']."'";
				}elseif($_POST['tReimprimirTipo'] == "N"){
					$sql = "SELECT numero_nota, xml FROM `nfe_".$filialGrupo."`.`NOTA_FISCAL` WHERE ".
							" (status = '03' OR status = '06') AND ".
							" numero_nota >= '".$_POST['tReimprimirNotaInicial']."' AND ".
							" numero_nota <= '".$_POST['tReimprimirNotaFinal']."' AND ".
							" cnpj_emitente = '".$_POST['hCnpj']."' AND ".
							" ambiente = '".$_POST['hAmbiente']."'";
				}

				$returnNf = $MNf->selectAllMestre($sql);

				if(!$returnNf){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = $MNf->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				
				if(is_array($returnNf)){
					foreach($returnNf as $conteudo){
						$arrayArquivosZip[]['xml'] = $conteudo['xml'];
						$arrayArquivosZip[]['numero'] = $conteudo['numero_nota'];
					}
				}

			// Verificar se tem inutilizadas
				$MInutilizacao = new MInutilizacao($filialGrupo);
				
				if($_POST['tReimprimirTipo'] == "P"){
					$periodoIni = substr($_POST['tReimprimirPeriodoInicial'],6,4)."-".substr($_POST['tReimprimirPeriodoInicial'],3,2)."-".substr($_POST['tReimprimirPeriodoInicial'],0,2);
					$periodoFim = substr($_POST['tReimprimirPeriodoFinal'],6,4)."-".substr($_POST['tReimprimirPeriodoFinal'],3,2)."-".substr($_POST['tReimprimirPeriodoFinal'],0,2);

					$sql = "SELECT numero_nota_inicial, xml FROM `nfe_".$grupo."`.`INUTILIZACAO` WHERE".
							" CONTRIBUINTE_cnpj = '".$_POST['hCnpj']."' AND ".
							" CONTRIBUINTE_ambiente = '".$_POST['hAmbiente']."' AND ".
							" data_hora >= '".$periodoIni."' AND ".
							" data_hora <= '".$periodoFim."' ";
					
				}elseif($_POST['tReimprimirTipo'] == "N"){
				
					$sql = "SELECT numero_nota_inicial, xml FROM nfe_".$grupo.".INUTILIZACAO WHERE".
							" CONTRIBUINTE_cnpj = '".$_POST['hCnpj']."' AND ".
							" CONTRIBUINTE_ambiente = '".$_POST['hAmbiente']."' AND ".
							" NOT ((numero_nota_inicial > '".$_POST['tReimprimirNotaInicial']."' AND ".
							" numero_nota_final > '".$_POST['tReimprimirNotaFinal']."') OR".
							" (numero_nota_inicial < '".$_POST['tReimprimirNotaInicial']."' AND ".
							" numero_nota_final < '".$_POST['tReimprimirNotaFinal']."'))";
				}

				$returnInut = $MInutilizacao->select($sql);

				if(is_array($returnInut)){
					foreach($returnInut as $conteudo){
						$arrayArquivosZip[]['xml'] = $conteudo['xml'];
						$arrayArquivosZip[]['numero'] = $conteudo['numero_nota_inicial'];
					}
				}

				// Verificar se tem eventos (cc, cancelamento)
				$MEvento = new MEvento($filialGrupo);
				
				if($_POST['tReimprimirTipo'] == "P"){
					$periodoIni = substr($_POST['tReimprimirPeriodoInicial'],6,4)."-".substr($_POST['tReimprimirPeriodoInicial'],3,2)."-".substr($_POST['tReimprimirPeriodoInicial'],0,2);
					$periodoFim = substr($_POST['tReimprimirPeriodoFinal'],6,4)."-".substr($_POST['tReimprimirPeriodoFinal'],3,2)."-".substr($_POST['tReimprimirPeriodoFinal'],0,2);

					$sql = "SELECT NOTA_FISCAL_numero_nota,	xml FROM nfe_".$grupo.".EVENTO WHERE ".
							"data_hora >= '".$periodoIni."' AND ".
							"data_hora <= '".$periodoFim."' AND " .
							"NOTA_FISCAL_cnpj_emitente = '".$_POST['hCnpj']."' AND ".
							"NOTA_FISCAL_ambiente = '".$_POST['hAmbiente']."'";
							
				}elseif($_POST['tReimprimirTipo'] == "N"){

					$sql = "SELECT NOTA_FISCAL_numero_nota, xml FROM nfe_".$grupo.".EVENTO WHERE ".
							"NOTA_FISCAL_numero_nota >= '".$_POST['tReimprimirNotaInicial']."' AND ".
							"NOTA_FISCAL_numero_nota <= '".$_POST['tReimprimirNotaFinal']."' AND ".
							"NOTA_FISCAL_cnpj_emitente = '".$_POST['hCnpj']."' AND ".
							"NOTA_FISCAL_ambiente = '".$_POST['hAmbiente']."'";
				}
				
				$returnEvento = $MEvento->selectMestre($sql);

				if(is_array($returnEvento)){
					foreach($returnEvento as $conteudo){
						$arrayArquivosZip[]['xml'] = $conteudo['xml'];
						$arrayArquivosZip[]['numero'] = $conteudo['NOTA_FISCAL_numero_nota'];
					}
				}

				// Gravar XMLs que encontrou na pasta /var/www/html/tmp/ para gerar pacote ZIP, dpois submeter o ZIP para download
				$diretorioTemp = "/var/www/html/relatorios/";
				
				if(!file_exists($diretorioTemp)){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = "Nao foi possivel acessar o diretorio ".$diretorioTemp.", verifique permissao e/ou existencia";
					echo json_encode($retornoJson);
					exit();
				}
				$nomePacote = "NFE_XML_".date("d_m_Y_H_i_s");
				$pastaTemp = $diretorioTemp.$nomePacote."/";
				
				if(!mkdir($pastaTemp, 0777)){
					$retornoJson['retorno']	 = false;
					$retornoJson['mensagem'] = "Nao foi possivel criar o diretorio ".$pasta.", verifique permissao e/ou existencia";
					echo json_encode($retornoJson);
					exit();
				}

				$nomeZip = $nomePacote.".zip";

/*				if(!$zip->open($diretorioTemp.$nomeZip, ZIPARCHIVE::CREATE)){
					echo "VPortalMonitor.php: MDFE-DOWNLOAD-XML -> Nao foi possivel criar o arquivo ZIP";
				}
	*/
				$cont = 1;
				foreach($arrayArquivosZip as $conteudo){
					$nomeArq = trim($_POST['hCnpj'])."_".trim($_POST['hAmbiente'])."_".$cont.".xml";
					if(!file_put_contents($pastaTemp.$nomeArq, base64_decode($conteudo['xml']))){
						/*$retornoJson['retorno']	 = false;
						$retornoJson['mensagem'] = "Nao foi possivel criar o arquivo ".$pastaTemp.$nomeArq.", verifique permissao e/ou existencia do diretorio";
						echo json_encode($retornoJson);
						exit();*/
					}
					$cont++;
				}
				
				system("cd ".$diretorioTemp." ;zip -r ".$nomeZip." ".$nomePacote."/ > /dev/null");
				system("rm -rf ".$pastaTemp);

				$retornoJson['retorno']	 = true;
				$retornoJson['zip'] = "relatorios/".$nomeZip;
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes do Contribuinte
	
	function fContribuinte($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		// Instanciar a classe Contribuinte para trabalhar com o contribuinte
		$MContribuinte = new MContribuinte($filialGrupo);
		if($_POST['hAmbiente'] == "0"){$_POST['hAmbiente'] = "2";}

		switch($pFuncao){
			case "CONTRIBUINTE-ACESSAR-REGISTRO":
				$MContribuinte->cnpj 		= $_POST['tFiliaisCNPJ'];

				$return = $MContribuinte->selectAll();

				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $MContribuinte->mensagemErro;
				
				echo json_encode($retornoJson);
				exit();
			break;
			case "CONTRIBUINTE-GRAVAR-REGISTRO": // REFAZER ISSO AQUI PQ FIZ CACA
				// Seta variaveis do Contribuinte a serem gravadas
				$MContribuinte->cnpj 			= $_POST['tFiliaisCNPJ'];
				$retorno = $MContribuinte->selectAll();
				
				$MContribuinte->ambiente 			= $_POST['tMDFeFlagProducao'];
				$MContribuinte->uf 					= $_POST['tFiliaisNfeUF'];
				$MContribuinte->cod_emp_fil_softdib	= $_POST['tFiliaisEmpresa'].$_POST['tFiliaisFilial'];
				$MContribuinte->razao_social		= $_POST['tFiliaisNome'];
				$MContribuinte->certificado_tipo	= "A1";
				$MContribuinte->certificado_caminho = $_POST['tFiliaisCNPJ'].".pfx";
				$MContribuinte->certificado_senha	= $_POST['tFiliaisWSSenha'];
				$MContribuinte->contigencia			= "01";
				$MContribuinte->justificativa_contingencia = "";
				$MContribuinte->pacote_xsd			= $_POST['tMDFeXSD'];
				$MContribuinte->email_usuario		= $_POST['tFiliaisEmailUsuario'];
				$MContribuinte->email_senha         = $_POST['tFiliaisEmailSenha'];
				$MContribuinte->email_remetente     = $_POST['tFiliaisEmailUsuario'];
				$MContribuinte->email_smtp          = $_POST['tFiliaisEmailSMTP'];
				$MContribuinte->email_porta         = $_POST['tFiliaisEmailPorta'];
				if($_POST['tFiliaisEmailConexao'] == "SSL"){
					$MContribuinte->email_ssl = "1";
				}else{
					$MContribuinte->email_ssl = "0";
				}
				$MContribuinte->email_conf_recebimento = "0";
				$MContribuinte->proxy_servidor 		= $_POST['tFiliaisProxyServidor'];
				$MContribuinte->proxy_porta 		= $_POST['tFiliaisProxyPorta'];
				$MContribuinte->proxy_usuario 		= $_POST['tFiliaisProxyUsuario'];
				$MContribuinte->proxy_senha 		= $_POST['tFiliaisProxySenha'];
				$MContribuinte->diretorio_integracao  = $_POST['tMDFeIntegracao'];
				$MContribuinte->diretorio_backup      = $_POST['tMDFeBackup'];
				$MContribuinte->diretorio_importacao  = $_POST['tMDFeImportacao'];
				$MContribuinte->diretorio_base        = $_POST['tMDFeBaseCliente'];
				$MContribuinte->damdfe_layout_caminho = $_POST['tDAMDFELayout'];
				$MContribuinte->damdfe_logo_caminho   = $_POST['tDAMDFELogomarca'];
				$MContribuinte->damdfe_qtde_vias      = $_POST['tDAMDFEVias'];
				$MContribuinte->damdfe_automatica	  = $_POST['tDAMDFEAuto'];
				$MContribuinte->server_impressao	  = $_POST['tDAMDFEServidor'];

				if(isset($retorno[0]['cnpj'])){
					//update
					$return = $MContribuinte->update();
				}else{
					//insert
					$return = $MContribuinte->record();
				}
				
				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $MContribuinte->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "CONTRIBUINTE-ANEXAR-CERTIFICADO":
			// Rotina para Anexar Certificado
				$caminho = "/var/www/html/nfe/novo/certs/";
				$array = array("pfx");	
				$tamanho = "100KB";
				$pNome = $_POST['tCadCNPJ'].".pfx";
				$CUploadFile = new CUploadFile();
				if($CUploadFile->upload($_FILES["tCadCert"],$caminho, $array, $tamanho, $pNome)){
					$CContribuinte->cnpj 				= $_POST['tCadCNPJ'];
					$CContribuinte->ambiente 			= $_POST['tCadAmbiente'];
					$CContribuinte->certificado_caminho = $pNome;
					if(!$CContribuinte->mAtualizarCertificado()){
						echo '<font color="#555" face="Verdana" size="1">'.$CContribuinte->mensagemErro;
					}else{
						echo '<font color="#555" face="Verdana" size="1">Anexado com sucesso!';
					}
				}else{
					echo '<font color="#555" face="Verdana" size="1">'.$CUploadFile->mensagemErro;
				}
				exit();
			break;
			case "CONTRIBUINTE-EXCLUIR-CERTIFICADO":
				$caminho = "/var/www/html/nfe/novo/certs/";
				if(!@unlink($caminho.$_POST['tCadCert'])){
					$retornoJson['retorno']		= false;
					$retornoJson['mensagem']	= "Erro ao excluir o certificado, verifique as permissoes da Pasta!";
					echo json_encode($retornoJson);
					exit();
				}
				
				$CContribuinte->cnpj 				= $_POST['tCadCNPJ'];
				$CContribuinte->ambiente 			= $_POST['tCadAmbiente'];
				$CContribuinte->certificado_caminho	= "";

				$return = $CContribuinte->mAtualizarCertificado();

				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $CContribuinte->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			// Testar envio de email
			case "CONTRIBUINTE-TESTE-ENVIO-EMAIL":
				$CEmail = new CEmail($filialGrupo);
				$CEmail->smtp 		= $_POST['tCadEmailSMTP'];
				$CEmail->porta		= $_POST['tCadEmailPorta'];
				$CEmail->ssl		= $_POST['cCadEmailSsl'];
				$CEmail->usuario	= $_POST['tCadEmailUsuario'];
				$CEmail->senha		= $_POST['tCadEmailSenha'];
				$CEmail->remetente	= $_POST['tCadEmailRemetente'];
				
				$return = $CEmail->testarEnvio();
				
				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $CEmail->mensagemErro;
				echo json_encode($retornoJson);
			break;
			
			case "CONTRIBUINTE-TESTAR-CONEXAO":
			break;
			
			case "CONTRIBUINTE-ACESSAR-INICIAL":
				$MContribuinte->cnpj 		= $_POST['hCnpj'];
				$MContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return = $MContribuinte->selectCNPJAmbiente();
				/*$certificado = fLocalVerificarCertificado($return[0]['certificado_caminho'],$return[0]['certificado_senha']);
				
				if(substr($certificado,0,1) == "F"){
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['fatal'] = $certificado;
					$retornoJson['retorno']['warning'] = "";
				}elseif(substr($certificado,0,1) == "W"){
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['warning'] = $certificado;
					$retornoJson['retorno']['fatal'] = "";
				}else{
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['warning'] = "";
					$retornoJson['retorno']['fatal'] = "";
				}*/
				$retornoJson['retorno']['warning'] = "";
					$retornoJson['retorno']['fatal'] = "";

				$retornoJson['retorno']['contigencia']	= $return[0]['contigencia'];
				$retornoJson['retorno']['ambiente']		= $return[0]['ambiente'];
				$retornoJson['mensagem']				= $MContribuinte->mensagemErro;
				
				$contribuinte = explode("-",$_POST['sContribuinte']);

/*				$CEncerrar = new CEncerrar();
				$CEncerrar->cnpj		= $return[0]['cnpj'];
				$CEncerrar->ambiente	= $return[0]['ambiente'];
				$CEncerrar->cMun		= $return[0]['uf'];

				$retorno = $CEncerrar->mListarNaoEncerradas();
*/
				
				echo json_encode($retornoJson);
				
				
				exit();
			break;
			case "CONTRIBUINTE-CONTIGENCIA":
				$CContribuinte->cnpj 						= $_POST['hCnpj'];
				$CContribuinte->ambiente 					= $_POST['hAmbiente'];
				$CContribuinte->contigencia					= $_POST['hContingencia'];
				$CContribuinte->justificativa_contingencia 	= $_POST['justificativa_contingencia'];

				$return = $CContribuinte->mAtualizarContingencia();

				$retornoJson['retorno'] 		= $return;
				$retornoJson['contingencia']	= $CContribuinte->contigencia;
				$retornoJson['mensagem']		= $CContribuinte->mensagemErro;
				$retornoJson['mensagemServico']	= $CContribuinte->mensagemServico;

				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes do Web Service
	
	function fWebService($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		$CWebService = new CWebService();
		switch($pFuncao){
			case "WEB-SERVICE-CONSULTAR-UF":
				$CWebService->uf 		= $_POST['tConsultarWSUFSigla'];
				$return = $CWebService->mObterWebServiceUf();
				
				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $CContribuinte->mensagemErro;
				
				echo json_encode($retornoJson);
				
				exit();
			break;
			case "WEB-SERVICE-ACESSAR-REGISTRO":
				$CWebService->uf 			= $_POST['tWSSefazUF'];
				$CWebService->versao_xml 	= $_POST['tWSSefazVersaoXml'];
				$CWebService->servico 		= $_POST['sWSSefazWebService'];
				$CWebService->ambiente 		= $_POST['sWSSefazAmbiente'];

				$return = $CWebService->mObterWebService();

				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $CWebService->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			case "WEB-SERVICE-GRAVAR-REGISTRO":
				// Seta variaveis do WebService a serem gravadas
				$CWebService->uf 				= $_POST['tWSSefazUF'];
				$CWebService->versao_xml 		= $_POST['tWSSefazVersaoXml'];
				$CWebService->servico 			= $_POST['sWSSefazWebService'];
				$CWebService->ambiente 			= $_POST['sWSSefazAmbiente'];
				$CWebService->metodo			= $_POST['tWSSefazMetodo'];
				$CWebService->nome 				= $_POST['tWSSefazNome'];
				$CWebService->cod_uf_ibge 		= $_POST['tWSSefazIBGE'];
				$CWebService->metodo_conexao	= $_POST['sWSSefazMetodoConexao'];
				$CWebService->url_completa 		= $_POST['tWSSefazURL'];
				$CWebService->situacao 			= $_POST['sWSSefazSituacao'];

				$return = $CWebService->fGravar();

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CWebService->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes da Inutilização
	
	function fInutilizar($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		$CInutilizar = new CInutilizar($filialGrupo);
		switch($pFuncao){
			case "INUTILIZAR-NUMERACAO":
				$contribuinte = explode("-",$_POST['sContribuinte']);
				$CInutilizar->contribuinteCNPJ 		= $contribuinte[0];
				$CInutilizar->contribuinteAmbiente 	= $contribuinte[1];
				$CInutilizar->serie 				= $_POST['tInutilizarSerie'];
				$CInutilizar->numeracaoInicial 		= $_POST['tInutilizarInicial'];
				$CInutilizar->numeracaoFinal 		= $_POST['tInutilizarFinal'];
				$CInutilizar->justificativa 		= $_POST['tInutilizarJustificativa'];
				$CInutilizar->usuario				= $_POST['lk_usuario'];
				
				$return = $CInutilizar->mInutilizarNumeracao();

				$retornoJson['retorno'] 	= $return;
				$retornoJson['mensagem']	= $CInutilizar->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
			
			case "INUTILIZAR-LISTAR":
				$periodo_ini	= substr($_POST['tConsultaPeriodoInicial'],6,4)."-".substr($_POST['tConsultaPeriodoInicial'],3,2)."-".substr($_POST['tConsultaPeriodoInicial'],0,2)." 00:00:00";
				$periodo_fim	= substr($_POST['tConsultaPeriodoFinal'],6,4)."-".substr($_POST['tConsultaPeriodoFinal'],3,2)."-".substr($_POST['tConsultaPeriodoFinal'],0,2)." 23:59:59";

				$contribuinte = explode("-",$_POST['sContribuinte']);
				$CInutilizar->contribuinteCNPJ 		= $contribuinte[0];
				$CInutilizar->contribuinteAmbiente 	= $contribuinte[1];
				$CInutilizar->periodo_ini			= $periodo_ini;
				$CInutilizar->periodo_fim			= $periodo_fim;
				

				$return = $CInutilizar->mObterLista();

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CInutilizar->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes do Cancelamento
	
	function fCancelar($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		$CCancelar = new CCancelar($filialGrupo);
		switch($pFuncao){
		  case "CANCELAR-MDFE":
		    $contribuinte = explode("-",$_POST['sContribuinte']);

			$CCancelar->cnpj			= trim($contribuinte[0]);
			$CCancelar->ambiente		= trim($contribuinte[1]);
			$CCancelar->serie			= trim($_POST['tCancelarSerie']);
			$CCancelar->numero			= trim($_POST['tCancelarNumero']);
			$CCancelar->chave			= trim($_POST['tCancelarChave']);
			$CCancelar->protocolo		= trim($_POST['tCancelarProtocolo']);
			$CCancelar->justificativa	= trim($_POST['tCancelarJustificativa']);
			$CCancelar->contingencia	= trim($_POST['hContingencia']);

		    $return = $CCancelar->mCancelarMDFe();

		    $retornoJson['retorno'] 	= $return;
		    $retornoJson['mensagem']	= $CCancelar->mensagemErro;

		    echo json_encode($retornoJson);
		    exit();
		  break;
		}
	}
	
	function fEncerrar($pFuncao){
		global $filialGrupo;
	// Variareis locais
		$retornoJson;

		$CEncerrar = new CEncerrar($filialGrupo);
		switch($pFuncao){
		  case "ENCERRAMENTO-BUSCAR-MUNICIPIOS":
			$return = $CEncerrar->mBuscarMunicipios(trim($_POST['tEncerramentoUF']));

		    $retornoJson['retorno'] 	= $return;
		    $retornoJson['mensagem']	= $CEncerrar->mensagemErro;

		    echo json_encode($retornoJson);
		    exit();
		  break;
		  case "ENCERRAMENTO-MANIFESTO":
			$contribuinte = explode("-",$_POST['sContribuinte']);

			$CEncerrar->cnpj			= trim($contribuinte[0]);
			$CEncerrar->ambiente		= trim($contribuinte[1]);
			$CEncerrar->serie			= trim($_POST['tEncerramentoSerie']);
			$CEncerrar->numero			= trim($_POST['tEncerramentoNumero']);
			$CEncerrar->chave			= trim($_POST['tEncerramentoChave']);
			$CEncerrar->protocolo		= trim($_POST['tEncerramentoProtocolo']);
			$CEncerrar->cMun			= trim($_POST['tEncerramentoMun']);
			$CEncerrar->contingencia	= trim($_POST['hContingencia']);

			$return = $CEncerrar->mEncerrarMDFe();

		    $retornoJson['retorno'] 	= $return;
		    $retornoJson['mensagem']	= $CEncerrar->mensagemErro;

		    echo json_encode($retornoJson);
		    exit();
		  break;
		}
	}
	
	
	// Tratar as funções que são provenientes de qualquer Serviço (genérico para testar status do serviço)
	
	function fServico($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		switch($pFuncao){
			case "SERVICO-STATUS":
				$contribuinte = explode("-",$_POST['sContribuinte']);
				
				// Instanciar Classe ToolsNFePHP
				switch($_POST['hContingencia']){
					case "03":
						$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$filialGrupo,2,false, true); // O ultimo true é referente a contigencia SCAN
					break;
					case "06":
						$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$filialGrupo,2,false, "SVC-AN");
					break;
					case "07":
						$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$filialGrupo,2,false, "SVC-RS");
					break;
					default:
						$ToolsNFePHP = new ToolsNFePHP($contribuinte[0], $contribuinte[1],$filialGrupo);
					break;
				}
				
				$resp = $ToolsNFePHP->statusServico();
				
				if(!$resp){
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
				}else{
					$retornoJson['mensagem'] = $resp['xMotivo'];
				}
				
				if($resp['bStat'] != 1){
					$retornoJson['retorno'] = false;
				}else{
					$retornoJson['retorno'] = true;
				}	
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes da Carta de Correção
	
	function fCC($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		$CCartaCorrecao = new CCartaCorrecao($filialGrupo);
		$CCartaCorrecao->NOTA_FISCAL_cnpj_emitente	= trim($_POST['tCCCnpj']);
		$CCartaCorrecao->NOTA_FISCAL_numero_nota	= trim($_POST['tCCNumero']);
		$CCartaCorrecao->NOTA_FISCAL_serie_nota		= trim($_POST['tCCSerie']);
		$CCartaCorrecao->NOTA_FISCAL_ambiente		= trim($_POST['tCCAmbiente']);
		
		switch($pFuncao){
			case "CC-OBTER-HISTORICOS":
				$return = $CCartaCorrecao->mObter();

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CCartaCorrecao->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "CC-VALIDAR":
				$return = $CCartaCorrecao->mValidarNota();
				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CCartaCorrecao->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "CC-CONFIRMAR":
				$CCartaCorrecao->descricao = trim($_POST['tCCDescricao']);

				$return = $CCartaCorrecao->mIncluirCC();

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CCartaCorrecao->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "CC-IMPRIMIR":
				$return = $CCartaCorrecao->mImprimirCC();
				$retornoJson['retorno']		= $return;
				$retornoJson['arquivo']		= $CCartaCorrecao->pdfCC;
				$retornoJson['mensagem']	= $CCartaCorrecao->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "CC-ENVIAR-EMAIL":
				$CCartaCorrecao->email = trim($_POST['tCCEmail']);
				$return = $CCartaCorrecao->mEnviarEmailCC();
				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $CCartaCorrecao->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	// Tratar as funções que são provenientes da DAMDFE
	
	function fDanfe($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;

		switch($pFuncao){
			case "DAMDFE-IMPRIMIR":
				$MMDFe = new MMDFe($filialGrupo);

				$contribuinte = explode("-",$_POST['sContribuinte']);
				
				$retorno = $MMDFe->selectAllMestre("SELECT xml, chave FROM mdfe_".$filialGrupo.".MDFE 
													WHERE cnpj = '".$contribuinte[0]."' AND
													ambiente = '".$contribuinte[1]."' AND
													numero = '".trim($_POST['tAutorizadaNF'])."' AND
													serie = '".trim($_POST['tAutorizadaSerie'])."'");
													
				if(!$retorno){
					$retornoJson['retorno']		= false;
					$retornoJson['mensagem']	= $MMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$CGerarDAMDFe = new CGerarDAMDFe($filialGrupo);

				$CGerarDAMDFe->xmlMDFe		= $retorno[0]['xml'];
				$CGerarDAMDFe->chaveMDFE	= $retorno[0]['chave'];
				//$CGerarDAMDFe->infAdic	= $retorno[0]['observacao'];

				if(!$CGerarDAMDFe->gerarDAMDFe()){
					$retornoJson['retorno']		= $return;
					$retornoJson['mensagem']	= $CGerarDAMDFe->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				
				$retornoJson['arquivo']	= $CGerarDAMDFe->DAMDFePdf;
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	
	// Tratar as funções que são provenientes da Consulta do Cadastro de Contribuinte ICMS
	
	function fCadCont($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;
		
		switch($pFuncao){
			case "CADCONT-CONSULTAR":
				if( (trim($_POST['tConCadCNPJ']) == "" && trim($_POST['tConCadIE']) == "" )
					|| trim($_POST['sConCadUF']) == "" ){
					$retornoJson['retorno']		= false;
					$retornoJson['mensagem']	= "Para consulta é necessário informar o CNPJ ou IE e UF!";
					echo json_encode($retornoJson);
					exit();
				}
				
				if(trim($_POST['sContribuinte']) == ""){
					$retornoJson['retorno']		= false;
					$retornoJson['mensagem']	= "Para consulta é necessário selecionar um Contribuinte!";
					echo json_encode($retornoJson);
					exit();
				}
				$contribuinte = explode("-",$_POST['sContribuinte']);
				
				$CConsultaCadastro = new CConsultaCadastro($filialGrupo);
				$CConsultaCadastro->contribuinte= $contribuinte[0];
				$CConsultaCadastro->ambiente	= $contribuinte[1];
				$CConsultaCadastro->cnpj		= $_POST['tConCadCNPJ'];
				$CConsultaCadastro->ie 			= $_POST['tConCadIE'];
				$CConsultaCadastro->uf 			= $_POST['sConCadUF'];

				$retorno = $CConsultaCadastro->mConsCadContribuinte();
				
				if(!$retorno){
					$retornoJson['retorno']		= $return;
					$retornoJson['mensagem']	= $CConsultaCadastro->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$retornoJson['retorno']	= $retorno;
				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	function fCritica($pFuncao){
		global $filialGrupo;
		// Variareis locais
		$retornoJson;

		switch($pFuncao){
			case "CRITICA-LISTAR":
				$MCritica = new MCritica($filialGrupo);

				$MCritica->cnpj		= trim($_POST['hCriticaCnpj']);
				$MCritica->numero	= trim($_POST['hCriticaNumero']);
				$MCritica->serie	= trim($_POST['hCriticaSerie']);
				$MCritica->ambiente	= trim($_POST['hCriticaAmbiente']);

				$return = $MCritica->select();

				$retornoJson['retorno']		= $return;
				$retornoJson['mensagem']	= $MCritica->mensagemErro;

				echo json_encode($retornoJson);
				exit();
			break;
		}
	}
	
	function fLocalVerificarCertificado($pCertificadoCaminho, $pCertificadoSenha){
		global $filialGrupo;
		// Verificar validade do certificado
		$caminho = "/var/www/html/nf/nfse/certificados/";
		if($pCertificadoCaminho != ""){
			$ToolsNFePHP = new ToolsNFePHP();
			$dataCertificadoForm = $ToolsNFePHP->validadeCertificado($caminho.$pCertificadoCaminho,$pCertificadoSenha);
			$dataCertificado = "20".substr($dataCertificadoForm,6,2).substr($dataCertificadoForm,3,2).substr($dataCertificadoForm,0,2);
			$dataAtual = date('Ymd');
			$dataFutura = date('Ymd',strtotime('now + 30 days'));
			// Verifica se jah venceu
			if($dataCertificado <= $dataAtual){
				return "FCertificado Expirou em ".$dataCertificadoForm." vincule um novo certificado para emissao das Notas Fiscais";
			}
			if($dataFutura >= $dataCertificado){
				return "WAten&ccedil;&atilde;o o certificado digital expirar&aacute; em ".$dataCertificadoForm.".";
			}
		}
	}
?>