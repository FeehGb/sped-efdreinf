<?php

	//error_reporting(0);
	/*
		consultaLote.php
		Programa responsavel por consultar a resposta do Lote Sefaz atraves do arquivo pendentes/LotePendentes.txt
		Programa startado pelo rc.local e fica lendo continuamente.
		Guilherme Pinto
		18/05/2015
	*/
	//require_once("libs/ConvertNFePHP.class.php");
	require_once("libs/ToolsNFePHP.class.php");
	require_once("control/CIntegracaoERP.php");
	require_once("control/CBackup.php");
	
	$retorno = lerArquivosPendentes();

	if(!$retorno) exit();
	
	foreach($retorno as $pendente)
	{
        if($pendente['tpEmis']=="7")
        {
            $ToolsNFePHP = new ToolsNFePHP($pendente['cnpj'], $pendente['ambiente'], $pendente['cUF'], $pendente['tpEmis'], 2, false, "SVC", false, $pendente['modelo']);
        }
        else
        {
            $ToolsNFePHP = new ToolsNFePHP($pendente['cnpj'], $pendente['ambiente'], $pendente['cUF'], $pendente['tpEmis'], 2, false, false, false, $pendente['modelo']);
        }

		$aRetorno = "";

        if($pendente['status_envio'] == "pendente")
        {
        	if(!$ToolsNFePHP->getProtocol3($pendente['recibo'], '', $pendente['ambiente'], $aRetorno))
			{
				echo "erro na consulta:".$ToolsNFePHP->errMsg;
			}
        }
        else
        {
        	if($pendente['ciclo_envio'] == "erro_sefaz_envio")
       		{
       			$array_data = explode(" ", $pendente['status_envio']);
       			$array_hora = explode(":", $array_data[1]);

       			$hora_agora = intval(date("H"));
       			$minutos_agora = intval(date("i"));

       			if($hora_agora > intval($array_hora[0]) && ($minutos_agora > intval($array_hora[1]) || $minutos_agora == intval($array_hora[1])))
       			{
       				$conteudo = file_get_contents($pendente['caminho'], $conteudo);
       				$conteudo = str_replace("|erro_sefaz_envio", "|erro", $conteudo);
        			file_put_contents($pendente['caminho'], $conteudo);
       			}
       			else if($hora_agora == intval($array_hora[0]) && $minutos_agora > intval($array_hora[1]))
       			{
       				$conteudo = file_get_contents($pendente['caminho'], $conteudo);
       				$conteudo = str_replace("|erro_sefaz_envio", "|erro", $conteudo);
        			file_put_contents($pendente['caminho'], $conteudo);
       			}
       		}
       		else if($pendente['ciclo_envio'] == "erro")
       		{
       			rename($pendente['caminho'],$pendente['caminho'].".processado.erro");
       		}
        }

		switch(trim($aRetorno['cStat']))
		{
			case "104":// Processamento OK
				rename($pendente['caminho'], $pendente['caminho'].".processado");
				break;
            case "105"://Em processamento, chame novamente mais tarde
            case "106"://Erro na consulta (reconsultar)
            case "108"://Erro ao comunicar
            case "109"://Erro ao comunicar
            case "999"://Erro nao catalogado (atribui-se chamar novamente)
            case ""://Erro nao catalogado (atribui-se chamar novamente)
            default:

            	$conteudo = file_get_contents($pendente['caminho']);
            	
            	if($pendente['status_envio'] == "pendente")
        		{
        			$conteudo = str_replace("|pendente|0", "|".date("Y-m-d H:i:s")."|erro_sefaz_envio", $conteudo);
        			file_put_contents($pendente['caminho'], $conteudo);
    			}
    
				continue;
				break;
			/*default:// Qualquer outro erro
				rename($pendente['caminho'],$pendente['caminho'].".processado.erro");
                                file_put_contents("/user/nfe/logerro", "lote:".$pendente['caminho']." status:".$aRetorno['cStat']."\n");
			break;*/
		}
		
		//TODO
		/*
			switch para status do lote
			104 - OK
			105 - em processamento (reenviar)
			106 - reenviar, houve erro na consulta
			108/109 - Erro de comunicao
			outro - retornar para o ERP para ele tomar as devidas decisões
		*/
		if(empty($aRetorno['aProt']))
		{ 
            continue;              
        }                              

		switch($aRetorno['aProt'][0]['cStat'])
		{
			//Autorizada ou Duplicidade de Autorização
			case "128":
			case "539":
			case "204":
			//Cancelada ou Duplicidade de Cancelamento
			case "101":
			case "218":
				$status = "3 ";
				break;
			case "100":
			case "104":
            case "150":
				$status = "6 ";
				break;
			//Denegada
			case "110":
			case "205":
			case "301":
			case "302":
			case "303":
				$status = "7 ";
				break;
			//Rejeitada
			default:
				$status = "8 ";
				break;
		}

		$arrayRetorno['cnpj'] = $pendente['cnpj'];
		$arrayRetorno['cUF'] =	str_pad($pendente['cUF'],2," ", STR_PAD_RIGHT);
		$arrayRetorno['anoMe'] = str_pad( $pendente['anomes'],4,"0", STR_PAD_RIGHT);
		$arrayRetorno['modelo'] = str_pad($pendente['modelo'],2," ", STR_PAD_RIGHT);
		$arrayRetorno['serie'] = str_pad($pendente['serie'],3," ", STR_PAD_LEFT);
		$arrayRetorno['numero'] = str_pad($pendente['numero'],9," ", STR_PAD_LEFT);
		$arrayRetorno['seriecon'] = "000";
		$arrayRetorno['numerocon'] = "000000000";
		$arrayRetorno['status'] = str_pad($status,2, " ", STR_PAD_RIGHT);
		$arrayRetorno['chave'] = str_pad($pendente['chave'],44, " ", STR_PAD_RIGHT);
        $arrayRetorno['ambiente'] = str_pad($pendente['ambiente'],1," ");
        $arrayRetorno['cUF2'] = str_pad($aRetorno['cUF'],2,"0", STR_PAD_LEFT);
        $arrayRetorno['dhRecbto'] = str_pad(substr(str_replace("-","",$aRetorno['aProt'][0]['dhRecbto']),0,8),8," ", STR_PAD_RIGHT);
		$arrayRetorno['nProt'] = str_pad($aRetorno['aProt'][0]['nProt'],15,"0", STR_PAD_RIGHT);
        //$arrayRetorno['statusSefaz'] = str_pad($aRetorno['cStat'],3,"0", STR_PAD_LEFT);
        //$arrayRetorno['motivoNota'] = str_pad($aRetorno['xMotivoXML'],255," ", STR_PAD_RIGHT);
        $arrayRetorno['statusSefaz'] = str_pad($aRetorno['aProt'][0]['cStat'],3,"0", STR_PAD_LEFT);
        $arrayRetorno['motivoNota'] = str_pad($aRetorno['aProt'][0]['xMotivo'],255," ", STR_PAD_RIGHT);
        $arrayRetorno['xml'] = $aRetorno['xmlRetorno'];

		$CIntegracaoERP = new CIntegracaoERP();
        $tempArquivo = str_replace("NFE","NFER/NFER",$pendente['nomeArquivo']);
        $tempArquivo = str_replace("NFC","NFER/NFCR",$tempArquivo);

		$CIntegracaoERP->mRetornoCobol($arrayRetorno,$tempArquivo);
    
		//Adiciona o protocolo a nota fiscal
        if(!is_dir("/var/www/html/nf/nfe/temp/"))
        {
            mkdir("/var/www/html/nf/nfe/temp/");
        }

		$xml = file_get_contents("/var/www/html/nf/nfe/temp/".$pendente['chave']."-pendNFe.xml");
		$xml = $ToolsNFePHP->addProt($xml, $aRetorno['xmlRetorno']);
               
        if($status == 3 || $status == 6 || $status == 7)
        {
            $CBackup = new CBackup();
            if(substr($pendente['nomeArquivo'],0,3) == "NFC")
            {
                $CBackup->mGuardarXml($xml,$pendente['chave'], $pendente['cnpj'], "nfc");
            }
            else
            {
                $CBackup->mGuardarXml($xml,$pendente['chave'], $pendente['cnpj'], "nfe");
            }
        }
	}
	
	function lerArquivosPendentes()
	{
		$arrayRetorno = false;
		$caminho = "/var/www/html/nf/nfe/pendentes/";

                $pos = strripos($arquivo, ".processado");
                $err = strripos($arquivo, ".erro");
                $con = strripos($arquivo, ".consLote");
		$diretorio = dir($caminho);
		while($arquivo = $diretorio->read())
		{
		          //echo $arquivo."\n";
			 if(strpos($arquivo, ".erro") !== false)// '..' && $arquivo != '.' && $err !== false && $pos === false && $con === false)
			 {
			        //echo $arquivo."\n";
				$explode = explode("|",file_get_contents($caminho.$arquivo));

				$arrayTemp['status_envio'] = $explode[7];

				//echo $arrayTemp['status_envio']."\n";
				$data_agora = new DateTime(date("Y-m-d H:i:s"));
				
				
				$data_erro = new DateTime($arrayTemp['status_envio']);
                                
                                
                                $data_erro->modify('+5 minutes');
				    
				    
				
				//$data_erro = new DateTime(date($arrayTemp['status_envio']." + 5 minutes"));


				//echo $data_agora." -> ".$data_erro."\n\n";
				
				var_dump($data_agora > $data_erro);
				//var_dump($data_erro);

				if(var_dump($data_agora > $data_erro) !== false)
				{
				   // echo "maiooorrrrr\n";
					refazConsultaNfe();
				}
			}
		}
		
		$diretorio = dir($caminho);
		while($arquivo = $diretorio->read())
		{
			$pos = strripos($arquivo, ".processado");
			$con = strripos($arquivo, ".consLote");
			if($arquivo != '..' && $arquivo != '.' && $pos === false && $con !== false){
				// Cria um Arrquivo com todos os Arquivos encontrados
				$explode = explode("|",file_get_contents($caminho.$arquivo));

                $arrayTemp['nomeArquivo']  = $explode[0];
				$arrayTemp['cnpj']         = $explode[1];
				$arrayTemp['ambiente']     = $explode[2];
				$arrayTemp['tpEmis']       = $explode[3];
				$arrayTemp['cUF']          = $explode[4];
				$arrayTemp['recibo']       = $explode[5];
				$arrayTemp['chave']        = $explode[6];
				$arrayTemp['status_envio'] = $explode[7];
				$arrayTemp['ciclo_envio']  = $explode[8];
				$arrayTemp['anomes']       = substr($explode[6],2,4);
				$arrayTemp['modelo']       = substr($explode[6],20,2);
				$arrayTemp['numero']       = substr($explode[6],25,9);
				$arrayTemp['serie']        = substr($explode[6],22,3);
				$arrayTemp['caminho']      = $caminho.$arquivo;

				$arrayRetorno[] = $arrayTemp;
			}
		}
		return $arrayRetorno;
	}

	function refazConsultaNfe()
	{		
		foreach (glob("/var/www/html/nf/nfe/pendentes/*.processado.erro") as $filename) 
		{
		    $arq_saida = str_replace(".processado.erro", "", $filename);
		    $conteudo = file_get_contents($filename);
		    // regex para buscar os dois últimos parâmetros (data hora|erro) e alterar para (pendente|0);
		    // Grava o arquivo para ser repsrocessado
		    $conteudo = preg_replace("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\|erro/", "pendente|0", $conteudo);
		    file_put_contents($arq_saida, $conteudo);
		    unlink($filename);
		}
	}

?>