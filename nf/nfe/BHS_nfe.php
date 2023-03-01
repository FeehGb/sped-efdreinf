<?php
/*
	nfe.php
	Programa responsavel por emitir a nota fiscal eletronica a partir de um arquivo criado na Caixa de Entrada
	Guilherme Pinto
	18/05/2015
*/
	require_once("libs/ConvertNFePHP.class.php");
	require_once("libs/ToolsNFePHP.class.php");
	require_once("control/CIntegracaoERP.php");
	require_once("control/CBackup.php");
	
	$cnpj="";
	$ambiente="";
	$tpEmis="";
	$cUF="";
	$chave="";

	// Instancia Classe de manipulacao de arquivos TXT/XML
	$CIntegracaoERP = new CIntegracaoERP();
	
	if(!$CIntegracaoERP->mIntegracaoERP($argv[1])){
		echo "Nenhum tipo de arquivo identificado";
		exit();
	}
        
        $nomeArquivo = explode("/",$argv[1]);
        $nomeArquivo = end($nomeArquivo);
	switch($CIntegracaoERP->tipoArquivo){
        // Tratamento para Nota Fiscal de Consumidor
        // Solicitação de envio
        case "NFC":
		// Converter TXT em XML
			$ConvertNFePHP = new ConvertNFePHP();
			$xml = $ConvertNFePHP->nfetxt2xml($CIntegracaoERP->arquivoTXT);
			if((!$xml) || $xml == ""){
				$CIntegracaoERP->log("Erro ao converter o TXT em XML, verifique se o arquivo esta correto","nfe.php");
			}
			$cnpj = $ConvertNFePHP->CNPJ;
			$ambiente = $ConvertNFePHP->tpAmb;
			$cUF = $ConvertNFePHP->cUF;
			$tpEmis = $ConvertNFePHP->tpEmis;
			$chave = $ConvertNFePHP->chave;
                        $modelo = $ConvertNFePHP->modelo;

			//montaChaveXMLExterno
			
			// Instancia comunicao com classe de envio
            if($tpEmis == "7"){
                $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, "SVC", false, $modelo);
            }else{
                $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, false, false, $modelo);
            }

			// Assinar o XML
			$xml = $ToolsNFePHP->signXML($xml[0], 'infNFe');
			if(!$xml){
				$CIntegracaoERP->log($ToolsNFePHP->errMsg,"ToolsNFePHP (signXML)");
				// TODO Gravar arquivo de erro na Caixa de Saida
				return false;
			}

            // Inserir a tag infNFeSupl
            $xml = $ConvertNFePHP->incluirTagNFCe($ToolsNFePHP->aURL['NfeConsultaQR']['URL'],$xml,"N");
            $qrCode = $ConvertNFePHP->qrCode;
            // Efetuar alteração apos 
                        
			// Validar XML
			$retornoErro="";
            $mensagem="";
			$retorno = $ToolsNFePHP->validXML($xml,'',$retornoErro);
			if(!$retorno){
				foreach ($retornoErro as $er){
					$mensagem .= $er;
                                        echo $er;
				}
                                $mensagem = trim(preg_replace('/\s\s+/', ' ', $mensagem));
                          // TODO Gravar arquivo de erro na Caixa de Saida
                                $pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>'', 'chave'=>$chave, 'cStat'=>'999', 'xMotivo'=>$mensagem, 'dhRecebto'=>date('c'),'qrCode'=>$qrCode, 'xml'=>'' );
				$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFC","LNFCR",$nomeArquivo));
                                exit();
			}
			
			// Gravar Nota em XMLs pendentes para apendar posteriormente
			file_put_contents("/var/www/html/nf/nfe/temp/".$chave."-pendNFe.xml",$xml);

			$retTools = $ToolsNFePHP->autoriza($xml, "", $retorno, 0);
                        
			if(!$retTools){
				// TODO Gravar arquivo de erro na Caixa de Saida
                        $pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'],'qrCode'=>$qrCode, 'xml'=>$retorno['xml'] );
						$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFC","LNFCR",$nomeArquivo));
			}else{
				// Gravar arquivo de retorno para Cobol - /CaixaSaida/Sefaz
				$pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'],'qrCode'=>$qrCode, 'xml'=>$retorno['xml'] );
				$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFC","LNFCR",$nomeArquivo));
				// Grava o arquivo para consulta "pedenetes"
				//fazer tratamento pelos status (normal, rejeitada ou denegada)



				$mensagem = $nomeArquivo."|".$cnpj."|".$ambiente."|".$tpEmis."|".$cUF."|".$retorno['infRec']['nRec']."|".$chave;

				/* Eduardo 24/05/16 13:54 - Não gravar arquivo do tipo ".consLote" no diretorio "pendentes" caso algum campo esteja em branco (vazio) */
				if(trim($nomeArquivo) != "" && trim($cnpj) != "" && trim($ambiente) != "" && trim($tpEmis) != "" && trim($cUF) != "" && trim($retorno['infRec']['nRec']) != "" && trim($chave) != "")
				{
					file_put_contents("/var/www/html/nf/nfe/pendentes/".$ToolsNFePHP->idLote.".consLote",$mensagem);
				}
				
			}
		break;
		// Solicitacao de ENVIO
		case "NFE":
		// Converter TXT em XML
			$ConvertNFePHP = new ConvertNFePHP();
			$xml = $ConvertNFePHP->nfetxt2xml($CIntegracaoERP->arquivoTXT);
			if((!$xml) || $xml == ""){
				$CIntegracaoERP->log("Erro ao converter o TXT em XML, verifique se o arquivo esta correto","nfe.php");
			}
			$cnpj = $ConvertNFePHP->CNPJ;
			$ambiente = $ConvertNFePHP->tpAmb;
			$cUF = $ConvertNFePHP->cUF;
			$tpEmis = $ConvertNFePHP->tpEmis;
			$chave = $ConvertNFePHP->chave;
                        $modelo = $ConvertNFePHP->modelo;

			//montaChaveXMLExterno

			// Instancia comunicao com classe de envio
            if($tpEmis == "7"){
                $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, "SVC", false, $modelo);
            }else{
                $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2, false, false, false, $modelo);
            }
			// Assinar o XML
			$xml = $ToolsNFePHP->signXML($xml[0], 'infNFe');
			if(!$xml){
				$CIntegracaoERP->log($ToolsNFePHP->errMsg,"ToolsNFePHP (signXML)");
				// TODO Gravar arquivo de erro na Caixa de Saida
				return false;
			}   

			// Validar XML
			$retornoErro="";
			$retorno = $ToolsNFePHP->validXML($xml,'',$retornoErro);
			if(!$retorno){
			        $mensagem="";
				foreach ($retornoErro as $er){
					$mensagem .= $er;
				}
				$mensagem = preg_replace( "/\r|\n/", "", $mensagem);
                                $pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>"999", 'xMotivo'=>$mensagem, 'dhRecebto'=>date("Y-m-dTH:m:s"), 'xml'=>$mensagem );
                                $CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFE","LNFER",$nomeArquivo));
                                //file_put_contents("/var/www/html/nf/nfe/temp/".$chave."-pendNFe.xml.erro",$xml);
				// TODO Gravar arquivo de erro na Caixa de Saida
				exit();
			}

			// Gravar Nota em XMLs pendentes para apendar posteriormente
			file_put_contents("/var/www/html/nf/nfe/temp/".$chave."-pendNFe.xml",$xml);

			$retTools = $ToolsNFePHP->autoriza($xml, "", $retorno, 0);

			if(!$retTools){
				// TODO Gravar arquivo de erro na Caixa de Saida
                                $pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'], 'xml'=>$retorno['xml'] );
				$CIntegracaoERP->mzRetornoCobol($pArray,str_replace("NFE","LNFER",$nomeArquivo));
			}else{
				// Gravar arquivo de retorno para Cobol - /CaixaSaida/Sefaz
				$pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'], 'xml'=>$retorno['xml'] );
				$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFE","LNFER",$nomeArquivo));
				// Grava o arquivo para consulta "pedenetes"
				//fazer tratamento pelos status (normal, rejeitada ou denegada)
				$mensagem = $nomeArquivo."|".$cnpj."|".$ambiente."|".$tpEmis."|".$cUF."|".$retorno['infRec']['nRec']."|".$chave;

				/* Eduardo 16/06/16 11:28 - Não gravar arquivo do tipo ".consLote" no diretorio "pendentes" caso algum campo esteja em branco (vazio) */
				if(trim($nomeArquivo) != "" && trim($cnpj) != "" && trim($ambiente) != "" && trim($tpEmis) != "" && trim($cUF) != "" && trim($retorno['infRec']['nRec']) != "" && trim($chave) != "")
				{
					file_put_contents("/var/www/html/nf/nfe/pendentes/".$ToolsNFePHP->idLote.".consLote",$mensagem);
				}
			}
		break;
		// Solicitacao de CANCELAMENTO
		case "CNFE":
                case "CNFC":
                    echo "efetuar o cancelamento";
			$ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis,0,false,false,false,$CIntegracaoERP->modelo);

			$returnCanc = $ToolsNFePHP->cancelEvent($CIntegracaoERP->chave, $CIntegracaoERP->protocolo, trim($CIntegracaoERP->justificativa), '', $CIntegracaoERP->ambiente, $aRetorno);

	        if($aRetorno['cStat'] == "135" || $aRetorno['cStat'] == "101" || $aRetorno['cStat'] == "128"){
	            $aRetorno['cStat'] = "2";
	        }
	        
	        $modelo = substr($CIntegracaoERP->chave,20,2);
	        $serieNota = substr($CIntegracaoERP->chave,22,3);
	        $numeroNota = substr($CIntegracaoERP->chave,25,9);

			$arrayIntegracao['cnpj']                = str_pad($CIntegracaoERP->CNPJ, 14, "0", STR_PAD_LEFT);
			$arrayIntegracao['uf_ibge_emitente']	= str_pad($CIntegracaoERP->cUF, 2, "0", STR_PAD_LEFT);
			$arrayIntegracao['ano_mes']         	= str_pad(date('ym'), 4, "0", STR_PAD_RIGHT);
			$arrayIntegracao['modelo_nota']     	= str_pad($modelo, 2, "0", STR_PAD_RIGHT);
			$arrayIntegracao['serie_nota']      	= str_pad($serieNota, 3, "0", STR_PAD_LEFT);
			$arrayIntegracao['numero_nota']     	= str_pad($numeroNota, 9, "0", STR_PAD_LEFT);
			$arrayIntegracao['status']          	= str_pad($aRetorno['cStat'], 3, "0", STR_PAD_LEFT);
			$arrayIntegracao['descricao_status']	= str_pad(substr($aRetorno['xMotivoEvento'],0,100), 100, " ", STR_PAD_RIGHT);
                        if(!$returnCanc){
				$arrayIntegracao['descricao_status'] = str_pad(substr($ToolsNFePHP->errMsg,0,100), 100, " ", STR_PAD_RIGHT);
                                echo $ToolsNFePHP->errMsg;
			}
			$arrayIntegracao['data_hora']       	= str_pad($aRetorno['dhRecbto'], 19, " ", STR_PAD_RIGHT);
			$arrayIntegracao['protocolo']       	= str_pad($aRetorno['nProt'], 15, "0", STR_PAD_LEFT);
			$arrayIntegracao['uf_ibge_responsavel'] = str_pad($aRetorno['cOrgao'], 2, "0", STR_PAD_LEFT);
                        if($aRetorno['xml'] != ""){
                            $arrayIntegracao['xml']                 = $aRetorno['xml'];
                        }elseif($aRetorno['xml_ret'] != ""){
                            $arrayIntegracao['xml']                 = $aRetorno['xml_ret'];
                        }else{
                            $ToolsNFePHP->errMsg;
                        }

                        if($CIntegracaoERP->tipoArquivo == "CNFC"){
                            $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("CNFC","CNFCR",$nomeArquivo));
                        }else{
                            $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("CNFE","CNFER",$nomeArquivo));
                        }
			//Guarda o Backup da nota
			$CBackup = new CBackup();
                        if($CIntegracaoERP->tipoArquivo == "CNFE"){
                            $CBackup->mGuardarXml($aRetorno['xml'], $CIntegracaoERP->chave, $CIntegracaoERP->CNPJ, "canc");
                        }elseif($CIntegracaoERP->tipoArquivo == "CNFC"){
                            $CBackup->mGuardarXml($aRetorno['xml'], $CIntegracaoERP->chave, $CIntegracaoERP->CNPJ, "cancc");
                        }

			// Montar Arquivo de retorno para o Cobol Caixa de Saida

		break;
		// Solcitacao de INUTILIZACAO
		case "INFE":
                case "INFC":
                        $ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis,0,false,false,false,$CIntegracaoERP->modelo);

			$returnCanc = $ToolsNFePHP->inutNF($CIntegracaoERP->ano, $CIntegracaoERP->serie, $CIntegracaoERP->numInicial, $CIntegracaoERP->numFinal, $CIntegracaoERP->justificativa, $CIntegracaoERP->ambiente, $aRetorno);

                        if($aRetorno['cStat'] == "102"){
                            $aRetorno['cStat'] = "2";
                        }else{
                            $aRetorno['cStat'] = "3";
                        }
                        
                        $arrayIntegracao['cnpj']                = str_pad($CIntegracaoERP->CNPJ, 14, "0", STR_PAD_LEFT);
						$arrayIntegracao['uf_ibge_emitente']	= str_pad($aRetorno['cUF'], 2, "0", STR_PAD_LEFT);
						$arrayIntegracao['ano_mes']         	= str_pad($aRetorno['ano'], 2, "0", STR_PAD_RIGHT);
						$arrayIntegracao['modelo_nota']     	= str_pad($CIntegracaoERP->modelo, 2, "0", STR_PAD_RIGHT);
						$arrayIntegracao['serie_nota']      	= str_pad($CIntegracaoERP->serie, 3, "0", STR_PAD_LEFT);
						$arrayIntegracao['numero_nota_inicial'] = str_pad($CIntegracaoERP->numInicial, 9, "0", STR_PAD_LEFT);
                        $arrayIntegracao['numero_nota_final']   = str_pad($CIntegracaoERP->numFinal, 9, "0", STR_PAD_LEFT);
						$arrayIntegracao['status']          	= str_pad($aRetorno['cStat'], 1, "0", STR_PAD_LEFT);
						$arrayIntegracao['descricao_status']	= utf8_decode(str_pad(substr($aRetorno['xMotivo'],0,255), 255, " ", STR_PAD_RIGHT));
                        if(!$returnCanc){
								$arrayIntegracao['descricao_status'] = utf8_decode(str_pad(substr($ToolsNFePHP->errMsg,0,255), 255, " ", STR_PAD_RIGHT));
                                echo $ToolsNFePHP->errMsg;
			}
						$arrayIntegracao['data_hora']       	= str_pad($aRetorno['dhRecbto'], 19, " ", STR_PAD_RIGHT);
						$arrayIntegracao['protocolo']       	= str_pad($aRetorno['nProt'], 15, "0", STR_PAD_LEFT);
						$arrayIntegracao['uf_ibge_responsavel'] = str_pad($aRetorno['cUF'], 2, "0", STR_PAD_LEFT);
                        if($aRetorno['xml'] != ""){
                            $arrayIntegracao['xml']                 = $aRetorno['xml'];
                        }elseif($aRetorno['xml_ret'] != ""){
                            $arrayIntegracao['xml']                 = $aRetorno['xml_ret'];
                        }else{
                            $ToolsNFePHP->errMsg;
                        }
                        
                        if($CIntegracaoERP->tipoArquivo == "INFC"){
                            $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("INFC","INFCR",$nomeArquivo));
                        }else{
                            $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("INFE","INFER",$nomeArquivo));
                        }
			//Guarda o Backup da nota
			$CBackup = new CBackup();
                        if($CIntegracaoERP->tipoArquivo == "INFE"){
                            $CBackup->mGuardarXml($aRetorno['xml'], $aRetorno['nProt'], $CIntegracaoERP->CNPJ, "inut");
                        }elseif($CIntegracaoERP->tipoArquivo == "INFC"){
                            $CBackup->mGuardarXml($aRetorno['xml'], $aRetorno['nProt'], $CIntegracaoERP->CNPJ, "inutc");
                        }
			

			// Montar Arquivo de retorno para o Cobol Caixa de Saida
		break;
		// Solicitacao de CARTA DE CORRECAO
		case "CCNFE":
                case "CCNFC":
			$ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis,0,false,false,false,$CIntegracaoERP->modelo);
                        $returnCC = $ToolsNFePHP->envCCe($CIntegracaoERP->chave, $CIntegracaoERP->descCorrecao, $CIntegracaoERP->sequencia);
                        if($ToolsNFePHP->arrayRetorno['cStat'] == "135" || $ToolsNFePHP->arrayRetorno['cStat'] == "136"){
                            $status = "2";
                        }else{
                            $status = "3";
                        }

			$arrayIntegracao['cnpj']                = str_pad($CIntegracaoERP->CNPJ, 14, "0", STR_PAD_LEFT);
			$arrayIntegracao['uf_ibge_emitente']	= str_pad($CIntegracaoERP->cUF, 2, "0", STR_PAD_LEFT);
			$arrayIntegracao['tpEmis']         	= str_pad($CIntegracaoERP->tpEmis, 1, "0", STR_PAD_LEFT);
			$arrayIntegracao['chave']               = str_pad($CIntegracaoERP->chave, 44, "0", STR_PAD_RIGHT);
			$arrayIntegracao['sequencia']      	= str_pad($CIntegracaoERP->sequencia, 2, "0", STR_PAD_LEFT);
			$arrayIntegracao['protocolo']     	= str_pad($ToolsNFePHP->arrayRetorno['nProt'], 15, "0", STR_PAD_LEFT);
			$arrayIntegracao['uf_autorizadora']     = str_pad($CIntegracaoERP->cUF, 2, "0", STR_PAD_LEFT);
			$arrayIntegracao['ambiente']            = str_pad($CIntegracaoERP->ambiente, 1, "0", STR_PAD_LEFT);
                        $arrayIntegracao['status']              = str_pad($status, 3, "0", STR_PAD_LEFT);
                        $arrayIntegracao['descricao_status']	= str_pad(substr($ToolsNFePHP->arrayRetorno['xMotivo'],0,255), 255, " ", STR_PAD_RIGHT);
                        $arrayIntegracao['data_hora']           = str_pad($ToolsNFePHP->arrayRetorno['dhRegEvenretCancNFeto'], 25, "", STR_PAD_RIGHT);
                        $arrayIntegracao['xml']                 = $ToolsNFePHP->arrayRetorno['xml_ret'];

                        $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("CCNFE","CCNFER",$nomeArquivo));
			//Guarda o Backup da nota
			$CBackup = new CBackup();
			$CBackup->mGuardarXml($ToolsNFePHP->arrayRetorno['xml'], $CIntegracaoERP->chave, $CIntegracaoERP->CNPJ, "cc");

			// Montar Arquivo de retorno para o Cobol Caixa de Saida

		break;
		// Solicitacao de STATUS SEFAZ
		case "SNFE":
                case "SNFC":
                    $ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis,0,false,false,false,$CIntegracaoERP->modelo);
			
                        $returnStatus = $ToolsNFePHP->statusServico($CIntegracaoERP->cUF, $CIntegracaoERP->ambiente, $aRetorno);

			$arrayIntegracao['status']          	= str_pad($aRetorno['cStat'], 3, "0", STR_PAD_LEFT);
			$arrayIntegracao['xMotivo']             = str_pad(substr($aRetorno['xMotivo'],0,100), 100, " ", STR_PAD_RIGHT);
			$arrayIntegracao['data_hora']       	= str_pad($aRetorno['dhRecbto'], 25, "0", STR_PAD_RIGHT); 
			$arrayIntegracao['tempo_medio']       	= str_pad($aRetorno['tMed'], 4, "0", STR_PAD_LEFT);
			$arrayIntegracao['xObs']                = str_pad($aRetorno['xObs'], 255, " ", STR_PAD_RIGHT);
                        $arrayIntegracao['xml']                 = $aRetorno['xml'];
                        $arrayIntegracao['cnpj']                = $CIntegracaoERP->CNPJ;

     			// Montar Arquivo de retorno para o Cobol Caixa de Saida
                        $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("SNFE","SNFER",$nomeArquivo));

		break;

		// Criado por: Eduardo - 04/04/2016 - 14h47
		// Solicitacao de CONSULTA NOTA
		case "RNFE":
            $dadosArquivo = explode("|", $CIntegracaoERP->arquivoTXT);

		    $cnpj    = $dadosArquivo[0];
		    $cUF    = $dadosArquivo[1];
		    $tpEmis   = $dadosArquivo[2];
		    $recibo   = $dadosArquivo[3];
		    $chave   = $dadosArquivo[4];
		    $ambiente   = substr($dadosArquivo[5],0,1);

		    $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis);

		    $arquivoloRetorno = $argv[1];
			$arquivoloRetorno = str_replace("RNFE", "RNFER", $arquivoloRetorno);
			$arquivoloRetorno = str_replace("CaixaEntrada", "CaixaSaida", $arquivoloRetorno);
			$arquivoloRetorno = str_replace("Processar", "Sefaz", $arquivoloRetorno);

			echo "\n".$arquivoloRetorno."\n";

			$aRetorno = array();
			$retorno = $ToolsNFePHP->getProtocol3('', $chave, $ambiente, $aRetorno, $arquivoloRetorno, $cnpj, $tpEmis);

		break;



		// Verifica STATUS DA NOTA - Eduardo 18/02/2016
		case "NFEC":
	     
		break;


		default:
			exit();
		break;
	}

?>