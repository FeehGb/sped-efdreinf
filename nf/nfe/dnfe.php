<?php

	require_once("/var/www/html/nf/nfe/libs/ToolsNFePHP.class.php");

	$conteudo = file_get_contents($argv[1]);
	$nomeArquivo = explode("/",$argv[1]);
    $nomeArquivo = end($nomeArquivo);
    $nomeArquivoSaida = $argv[1];
    $nomeArquivoSaida = str_replace("DNFE", "DNFER", $nomeArquivoSaida);
    $nomeArquivoSaida = str_replace("MNFE", "MNFER", $nomeArquivoSaida);
    $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/WNFE", "CaixaSaida/Sefaz/WNFER", $nomeArquivoSaida);
    $nomeArquivoSaida = str_replace("CaixaEntrada/Processar", "CaixaSaida/Sefaz", $nomeArquivoSaida);
	$DadosTXT = explode("|", $conteudo);

	if (strpos($nomeArquivo, 'DNFE') !== false)
	{
		$opcao = "DNFE";
	}
	else if (strpos($nomeArquivo, 'MNFE') !== false)
	{
		$opcao = "MNFE";
	}
	else if (strpos($nomeArquivo, 'WNFE') !== false)
	{
		$opcao = "WNFE";
	}

	switch($opcao)
	{
		case "DNFE":

			$cnpj = trim($DadosTXT[0]);
			$uf = trim($DadosTXT[1]);
			$tipo_ambiente = trim($DadosTXT[2]);
			$servico = trim($DadosTXT[3]);
			$indicador_nfe = trim($DadosTXT[4]);
			$indicador_emissor = trim($DadosTXT[5]);
			$ultimo_nsu = str_replace("\n", "", trim($DadosTXT[6]));

			$ultNSU = $ultimo_nsu;

			// Instanciar o ToolsNFePHP para chamar o SEFAZ
			$ToolsNFePHP = new ToolsNFePHP($cnpj, $tipo_ambiente, $uf, "01");
			
			if(!$ToolsNFePHP){
				$retornoJson['retorno'] = false;
				$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
				echo json_encode($retornoJson);
				exit();
			}

			$indCont=1;

			$existeNota = false;
			$xMotivoExisteNota = "";

			$fileNsu = fopen("/var/www/html/eduardo/nsu.txt", "w+");

			$primeira = true;


			while($indCont == 1)
			{
				$aRetorno="";
				unset($aRetorno);
				unset($retorno);
				$retorno = $ToolsNFePHP->getListNFe(true,'0','0',$ultNSU,$tipo_ambiente,'2',$aRetorno);
				
				$indCont = $aRetorno['indCont'];
				$ultNSU = $aRetorno['nsu'];
				$xMotivo = $aRetorno['Motivo'];
				$cStat = $aRetorno['Status'];

				if($primeira == true)
				{
					$primeira = false;
					fwrite($fileNsu, $aRetorno["retorno"]);
				}

				if(!$retorno){
					$file = fopen($nomeArquivoSaida, "w");
					$conteudoSaidaCabecalho = "";

					if (strpos($ToolsNFePHP->errMsg, '656') !== false) 
					{
					    $conteudoSaidaCabecalho = "000|".$cnpj."|".$uf."|".$tipo_ambiente."|".$servico."|".$indicador_nfe."|".$indicador_emissor."|656|".str_replace("\n","", trim($ToolsNFePHP->errMsg))."|".date('YmdHis')."|".$indCont."|".$ultNSU."|\n";
					}
					else
					{
						$conteudoSaidaCabecalho = "000|".$cnpj."|".$uf."|".$tipo_ambiente."|".$servico."|".$indicador_nfe."|".$indicador_emissor."|108|".str_replace("\n","", trim($ToolsNFePHP->errMsg))."|".date('YmdHis')."|".$indCont."|".$ultNSU."|\n";
					}
					
					fwrite($file, $conteudoSaidaCabecalho);
					fclose($file);

					exit();
				}

				

				if(is_array($aRetorno['NFe'])){
					foreach($aRetorno['NFe'] as $conteudo){
						$existeNota = true;
						
						$xMotivoExisteNota = $conteudo['xMotivo'];
						$nsu					= $conteudo['NSU'];
						$ambiente				= $tipo_ambiente;
						$tipo					= "A";
						$chave					= $conteudo['chNFe'];
						$emit_cpf_cnpj			= $conteudo['CNPJ'];
						$emit_nome				= $conteudo['xNome'];
						$ie						= $conteudo['IE'];
						$dest_cpf_cnpj			= $cnpj;
						$data_emissao			= $conteudo['dEmi'];
						$data_emissao = str_replace("-", "", $data_emissao);
						$data_emissao = str_replace("-", "", $data_emissao);
						$tipo_nota				= $conteudo['tpNF'];
						$valor_nf				= $conteudo['vNF'];
						$digest_value			= $conteudo['digVal'];
						$data_hora_recebimento	= $conteudo['dhRecbto'];
						$situacao_nfe			= $conteudo['cSitNFe'];
						$confirmacao			= $conteudo['cSitConf'];
						$motivo					= $conteudo['xMotivo'];
						$stat					= $conteudo['cStat'];
						$data_hora_evento		= "";
						$tp_evento				= "";
						$seq_evento				= "0";
						$desc_evento			= "";
						$correcao				= "";
						$conteudoSaida .= "001|".$nsu."|".$chave."|".$emit_cpf_cnpj."|".$emit_nome."|".$ie."|".$data_emissao."|".$tipo_nota."|".$valor_nf."|".$digest_value."|".$data_hora_recebimento."|".$situacao_nfe."|".$confirmacao."|\n";
					}
				}
				
				if(is_array($aRetorno['Canc'])){
					foreach($aRetorno['Canc'] as $conteudo){
						$existeNota = true;
						$xMotivoExisteNota = $conteudo['xMotivo'];
						$nsu					= $conteudo['NSU'];
						$ambiente				= $tipo_ambiente;
						$tipo					= "C";
						$chave					= $conteudo['chNFe'];
						$emit_cpf_cnpj			= $conteudo['CNPJ'];
						$emit_nome				= $conteudo['xNome'];
						$ie						= $conteudo['IE'];
						$dest_cpf_cnpj			= $cnpj;
						$data_emissao			= $conteudo['dEmi'];
						$data_emissao = str_replace("-", "", $data_emissao);
						$data_emissao = str_replace("-", "", $data_emissao);
						$tipo_nota				= $conteudo['tpNF'];
						$valor_nf				= $conteudo['vNF'];
						$digest_value			= $conteudo['digVal'];
						$data_hora_recebimento	= $conteudo['dhRecbto'];
						$situacao_nfe			= $conteudo['cSitNFe'];
						$confirmacao			= $conteudo['cSitConf'];
						$data_hora_evento		= "";
						$tp_evento				= "";
						$seq_evento				= "0";
						$desc_evento			= "";
						$correcao				= "";
						$conteudoSaida .= "002|".$nsu."|".$chave."|".$emit_cpf_cnpj."|".$emit_nome."|".$ie."|".$data_emissao."|".$tipo_nota."|".$valor_nf."|".$digest_value."|".$data_hora_recebimento."|".$situacao_nfe."|".$confirmacao."|\n";
						
					}
				}
				
				if(is_array($aRetorno['CCe'])){
					foreach($aRetorno['CCe'] as $conteudo){
						$existeNota = true;
						$xMotivoExisteNota = $conteudo['xMotivo'];
						$nsu					= $conteudo['NSU'];
						$ambiente				= $tipo_ambiente;
						$tipo					= "CC";
						$chave					= $conteudo['chNFe'];
						$emit_cpf_cnpj			= "";//$conteudo['CNPJ'];
						$emit_nome				= "";//$conteudo['xNome'];
						$dest_cpf_cnpj			= $cnpj;
						$data_emissao			= "";//$conteudo['dEmi'];
						$tipo_nota				= $conteudo['tpNF'];
						$valor_nf				= "";//$conteudo['vNF'];
						$digest_value			= "";//$conteudo['digVal'];
						$data_hora_recebimento	= $conteudo['dhRecbto'];
						$ie						= $conteudo['IE'];      
						$situacao_nfe			= $conteudo['cSitNFe']; 
						$confirmacao			= $conteudo['cSitConf'];
						$data_hora_evento		= $conteudo['dhEvento'];
						$tp_evento				= $conteudo['tpEvento'];
						$seq_evento				= $conteudo['nSeqEvento'];
						$desc_evento			= $conteudo['descEvento'];
						$correcao				= $conteudo['certificadosxCorrecao'];
						$conteudoSaida .= "003|".$nsu."|".$chave."|".$data_hora_evento."|".$tp_evento."|".$seq_evento."|".$desc_evento."|".$correcao."|".$tipo_nota."|".$data_hora_recebimento."|\n";
					}
				}

				if($indCont == 0)
				{
					$file = fopen($nomeArquivoSaida, "w");
					$conteudoSaidaCabecalho = "";

					if($existeNota == true)
						$cStat = "138";
					else
					{
						$xMotivoExisteNota = $xMotivo;
						$cStat = "137";
					}
		
					$conteudoSaidaCabecalho = "000|".$cnpj."|".$uf."|".$tipo_ambiente."|".$servico."|".$indicador_nfe."|".$indicador_emissor."|".$cStat."|".$xMotivoExisteNota."|".date('YmdHis')."|".$indCont."|".$ultNSU."|\n";
					$conteudo = $conteudoSaidaCabecalho.$conteudoSaida;
					fwrite($file, $conteudo);
					fclose($file);
				}	
			}
		break;

		case "MNFE":

			$cnpj = trim($DadosTXT[0]);
			$uf = trim($DadosTXT[1]);
			$tipo_ambiente = trim($DadosTXT[2]);
			$tpEvento = trim($DadosTXT[3]);
			$chNFe = trim($DadosTXT[4]);
			$xJust = str_replace("\n", "", trim($DadosTXT[5]));

			// Instanciar o ToolsNFePHP para chamar o SEFAZ
			$ToolsNFePHP = new ToolsNFePHP($cnpj, $tipo_ambiente, $uf, "01", 0, false, false, true);

			if(!$ToolsNFePHP){

				$file = fopen($nomeArquivoSaida, "w");
				$conteudoSaidaCabecalho = "";
				$conteudoSaidaCabecalho = $cnpj."|".$orgao."|".$tipo_ambiente."|".$tpEvento."|".$xEvento."|".$chNFe."|".$xJust."|".$cStat."|".$xMotivo."|".$emailDest."|".$dhRegEvento."|".$nProt."|".$xml."|\n";
				fwrite($file, $conteudoSaidaCabecalho);
				fclose($file);
				exit();
			}


			$aResp="";
			unset($aResp);
			unset($retorno);

			$retorno = $ToolsNFePHP->manifDest($chNFe,$tpEvento,$xJust,$tipo_ambiente,'2',$aResp);
			

			//print_r($retorno);
			//print_r($aResp);

			$orgao = $aResp['cOrgao'];
			$xEvento = $aResp['xEvento'];
			$cStat = $aResp['cStat'];
			$xMotivo = $aResp['xMotivo'];
			//$emailDest = $aResp['emailDest'];
			$dhRegEvento = $aResp['dhRegEvento'];
			$nProt = $aResp['nProt'];
			$xml = $aResp['xml'];

			if(!$retorno){
				$file = fopen($nomeArquivoSaida, "w");
				$conteudoSaidaCabecalho = "";
				$conteudoSaidaCabecalho = $cnpj."|".$orgao."|".$tipo_ambiente."|".$tpEvento."|".$xEvento."|".$chNFe."|".$xJust."|".$cStat."|".str_replace("\n","", trim($ToolsNFePHP->errMsg))."|".$emailDest."|".$dhRegEvento."|".$nProt."|".$xml."|\n";
				fwrite($file, $conteudoSaidaCabecalho);
				
				fclose($file);
				exit();
			}

			if($nProt != ""){
		
				$file = fopen($nomeArquivoSaida, "w");
				$conteudoSaidaCabecalho = "";
				$conteudoSaidaCabecalho = $cnpj."|".$orgao."|".$tipo_ambiente."|".$tpEvento."|".$xEvento."|".$chNFe."|".$xJust."|".$cStat."|".str_replace("\n","", trim($xMotivo))."||".$dhRegEvento."|".$nProt."|".$xml."|\n";
				fwrite($file, $conteudoSaidaCabecalho);
				fclose($file);
				exit();
			}

			exit();
		break;

		case "WNFE":
				$cnpj = trim($DadosTXT[0]);
				$uf = trim($DadosTXT[1]);
				$tipo_ambiente = trim($DadosTXT[2]);
				$tpServico = trim($DadosTXT[3]);
				$chNFe = str_replace("\n", "", trim($DadosTXT[4]));

				// Instanciar o ToolsNFePHP para chamar o SEFAZ
				$ToolsNFePHP = new ToolsNFePHP($cnpj, $tipo_ambiente, $uf, "01", 0, false, false, true);
				if(!$ToolsNFePHP){
					$file = fopen($nomeArquivoSaida, "w");
					$conteudoSaidaCabecalho = "";
					$conteudoSaidaCabecalho = $cnpj."|".$uf."|".$tipo_ambiente."|".$tpServico."|".$chNFe."|".$cStat."|".str_replace("\n","", trim($ToolsNFePHP->errMsg))."|".$dhResp."|".$xml."|\n";
					fwrite($file, $conteudoSaidaCabecalho);
					fclose($file);
					exit();
				}

				$aRetorno="";
				unset($aRetorno);
				unset($retorno);

				$retorno = $ToolsNFePHP->getNFe(true,$chNFe, '','2',$aRetorno);

				if(!$retorno){
					$file = fopen($nomeArquivoSaida, "w");
					$conteudoSaidaCabecalho = "";
					$conteudoSaidaCabecalho = $cnpj."|".$uf."|".$tipo_ambiente."|".$tpServico."|".$chNFe."|".$cStat."|".str_replace("\n","", trim($ToolsNFePHP->errMsg))."|".$dhResp."|".$xml."|\n";
					fwrite($file, $conteudoSaidaCabecalho);
					fclose($file);
					exit();
				}

				$cStat = $aRetorno["cStat"];
				$xMotivo = $aRetorno["xMotivo"];
				$dhResp = $aRetorno["dhResp"];
				$xml = $aRetorno["xml"];
				$xmlSaida = $aRetorno["nome"];
				$dataProc = $aRetorno["dhRecbto"];

				$data = explode("T", $dataProc);
				$aData = explode("-", $data[0]);
				$ano = $aData[0];
				$mes = $aData[1];
				$dia = $aData[2];

				/*
				echo "ano:".$ano."\n";
				echo "ano:".$mes."\n";
				echo "ano:".$dia."\n";
				*/

				mkdir("/user/nfe/".$cnpj."/DEST/".$ano."/".$mes."/".$dia."/", 0777, true);

				file_put_contents("/user/nfe/".$cnpj."/DEST/".$ano."/".$mes."/".$dia."/".$xmlSaida, $xml);
				file_put_contents("/var/www/nfe/recebe/".$xmlSaida, $xml);

				$file = fopen($nomeArquivoSaida, "w");
				$conteudoSaidaCabecalho = "";
				$conteudoSaidaCabecalho = $cnpj."|".$uf."|".$tipo_ambiente."|".$tpServico."|".$chNFe."|".$cStat."|".$xMotivo."|".$dataProc."|".$xml."|\n";
				fwrite($file, $conteudoSaidaCabecalho);
				fclose($file);
				
				exit();
			break;

		
	}
?>