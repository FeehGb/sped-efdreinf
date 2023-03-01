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
	$recibo = "";

	// Instancia Classe de manipulacao de arquivos TXT/XML
	$CIntegracaoERP = new CIntegracaoERP();
	
	if(!$CIntegracaoERP->mIntegracaoERP($argv[1])){
		echo "Nenhum tipo de arquivo identificado";
		exit();
	}
        
        $nomeArquivo = explode("/",$argv[1]);
        $nomeArquivo = end($nomeArquivo);

	switch($CIntegracaoERP->tipoArquivo){
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
			
			//montaChaveXMLExterno
			
			// Instancia comunicao com classe de envio
			$ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis, 2);
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
				// TODO Gravar arquivo de erro na Caixa de Saida
				exit();
			}
			
			// Gravar Nota em XMLs pendentes para apendar posteriormente
			file_put_contents("/var/www/html/nf/nfe/temp/".$chave."-pendNFe.xml",$xml);

			$retTools = $ToolsNFePHP->autoriza($xml, "", $retorno, 0);

			if(!$retTools){
				// TODO Gravar arquivo de erro na Caixa de Saida
                                $pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'], 'xml'=>$retorno['xml'] );
				$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFE","LNFER",$nomeArquivo));
			}else{
				// Gravar arquivo de retorno para Cobol - /CaixaSaida/Sefaz
				$pArray = array('cnpj'=>$cnpj, 'ambiete'=>$ambiente, 'tpEmis'=>$tpEmis, 'cUF'=>$cUF, 'recibo'=>$retorno['infRec']['nRec'], 'chave'=>$chave, 'cStat'=>$retorno['cStat'], 'xMotivo'=>$retorno['xMotivo'], 'dhRecebto'=>$retorno['dhRecebto'], 'xml'=>$retorno['xml'] );
				$CIntegracaoERP->mRetornoCobol($pArray,str_replace("NFE","LNFER",$nomeArquivo));
				// Grava o arquivo para consulta "pedenetes"
				//fazer tratamento pelos status (normal, rejeitada ou denegada)
				$mensagem = $nomeArquivo."|".$cnpj."|".$ambiente."|".$tpEmis."|".$cUF."|".$retorno['infRec']['nRec']."|".$chave;
				file_put_contents("/var/www/html/nf/nfe/pendentes/".$ToolsNFePHP->idLote.".consLote",$mensagem);
			}
		break;
		// Solicitacao de CANCELAMENTO
		case "CNFE":
			$ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis);
			
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

                        $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("CNFE","CNFER",$nomeArquivo));
			//Guarda o Backup da nota
			$CBackup = new CBackup();
			$CBackup->mGuardarXml($aRetorno['xml'], $CIntegracaoERP->chave, $CIntegracaoERP->CNPJ, "canc");

			// Montar Arquivo de retorno para o Cobol Caixa de Saida

		break;
		// Solcitacao de INUTILIZACAO
		case "INFE":
                        $ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis);
			
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

                        $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("INFE","INFER",$nomeArquivo));
			//Guarda o Backup da nota
			$CBackup = new CBackup();
			$CBackup->mGuardarXml($aRetorno['xml'], $CIntegracaoERP->chave, $CIntegracaoERP->CNPJ, "inut");

			// Montar Arquivo de retorno para o Cobol Caixa de Saida                        
		break;
		// Solicitacao de CARTA DE CORRECAO
		case "CCNFE":
		break;
		// Solicitacao de STATUS SEFAZ
		case "SNFE":
            $ToolsNFePHP = new ToolsNFePHP($CIntegracaoERP->CNPJ, $CIntegracaoERP->ambiente, $CIntegracaoERP->cUF, $CIntegracaoERP->tpEmis);
	
            $returnStatus = $ToolsNFePHP->statusServico($CIntegracaoERP->cUF, $CIntegracaoERP->ambiente, $aRetorno);

			$arrayIntegracao['status']          	= str_pad($aRetorno['cStat'], 3, "0", STR_PAD_LEFT);
			$arrayIntegracao['xMotivo']             = str_pad(substr($aRetorno['xMotivo'],0,100), 100, " ", STR_PAD_RIGHT);
            if(!$returnCanc){
				$arrayIntegracao['descricao_status'] = str_pad(substr($ToolsNFePHP->errMsg,0,100), 100, " ", STR_PAD_RIGHT);
                                echo $ToolsNFePHP->errMsg;
			}
			$arrayIntegracao['data_hora']       	= str_pad($aRetorno['dhRecbto'], 19, " ", STR_PAD_RIGHT);
			$arrayIntegracao['tempo_medio']       	= str_pad($aRetorno['tMed'], 4, "0", STR_PAD_LEFT);
			$arrayIntegracao['xObs']                = str_pad($aRetorno['xObs'], 255, " ", STR_PAD_RIGHT);
            $arrayIntegracao['xml']                 = $aRetorno['xml'];
            $arrayIntegracao['cnpj']                = $CIntegracaoERP->CNPJ;

     			// Montar Arquivo de retorno para o Cobol Caixa de Saida
                        $CIntegracaoERP->mRetornoCobol($arrayIntegracao,str_replace("SNFE","SNFER",$nomeArquivo));

		break;
		// Solicitacao de CONSULTA NOTA - Eduardo 19/02/2016
		case "RNFE":

			$dadosArquivo = explode("|", $CIntegracaoERP->arquivoTXT);

		    $cnpj    = $dadosArquivo[0];
		    $cUF    = $dadosArquivo[1];
		    $tpEmis   = $dadosArquivo[2];
		    $recibo   = $dadosArquivo[3];
		    $chave   = $dadosArquivo[4];
		    $ambiente   = substr($dadosArquivo[5],0,1);

		    $ToolsNFePHP = new ToolsNFePHP($cnpj, $ambiente, $cUF, $tpEmis);

			$aRetorno = array();

			$retorno = $ToolsNFePHP->getProtocol3('', $chave, $ambiente, $aRetorno);
			print_r($retorno);
			//print_r($aRetorno);

			echo "\n\neduardo\n\n";

			print_r($aRetorno);

			echo "\n\neduardo\n\n";

		break;

		default:
			exit();
		break;
	}

?>