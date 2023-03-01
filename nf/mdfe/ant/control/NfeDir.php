<?php

	$tFiliaisNfeIntegracao = "../../../../../../../".$_POST['tFiliaisNfeIntegracao'];

	$tFiliaisNfeDiretorioImportacao = "../../../../../../..".$_POST['tFiliaisNfeDiretorioImportacao'];

	$tFiliaisNfeDiretorioBackup = "../../../../../../..".$_POST['tFiliaisNfeDiretorioBackup'];

	$tFiliaisNfeBaseCliente = "../../../../../../..".$_POST['tFiliaisNfeBaseCliente'];

	$retorno = "";

	if(!is_dir($tFiliaisNfeDiretorioImportacao)) 
	{
		mkdir ($tFiliaisNfeDiretorioImportacao, 0777);
		$retorno = $retorno."/importacao:Criado\n";
	}
	else
	{	
		$retorno = $retorno."/importacao:Existente\n";
	}

	if(!is_dir($tFiliaisNfeDiretorioBackup)) 
	{
		mkdir ($tFiliaisNfeDiretorioBackup, 0777);
		$retorno = $retorno."/backup:Criado\n";
	}
	else
	{	
		$retorno = $retorno."/backup:Existente\n";
	}

	if(!is_dir($tFiliaisNfeBaseCliente)) 
	{
		$retorno = $retorno."/".substr($tFiliaisNfeBaseCliente, 26).":Nao Existe\n\n";
	}
	else
	{	
		$retorno = $retorno."/".$tFiliaisNfeBaseCliente.":Existente\n\n";
	}


	if(is_dir($tFiliaisNfeIntegracao)) 
	{
		if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada")) 
		{
			mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada", 0777);
			$retorno = $retorno."/CaixaEntrada:Criado\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Log")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Log", 0777);
				$retorno = $retorno."/CaixaEntrada/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Log:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Preparar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Preparar", 0777);
				$retorno = $retorno."/CaixaEntrada/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Preparar:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Processado")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processado", 0777);
				$retorno = $retorno."/CaixaEntrada/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processado:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Processar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processar", 0777);
				$retorno = $retorno."/CaixaEntrada/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processar:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Temporario")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Temporario", 0777);
				$retorno = $retorno."/CaixaEntrada/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Temporario:Existente\n";
			
		}
		else
		{
			$retorno = $retorno."/CaixaEntrada:Existente\n";
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Log")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Log", 0777);
				$retorno = $retorno."/CaixaEntrada/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Log:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Preparar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Preparar", 0777);
				$retorno = $retorno."/CaixaEntrada/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Preparar:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Processado")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processado", 0777);
				$retorno = $retorno."/CaixaEntrada/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processado:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Processar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processar", 0777);
				$retorno = $retorno."/CaixaEntrada/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processar:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaEntrada/Temporario")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Temporario", 0777);
				$retorno = $retorno."/CaixaEntrada/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Temporario:Existente\n";
		}

		if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida")) 
		{
			mkdir ($tFiliaisNfeIntegracao."/CaixaSaida", 0777);
			$retorno = $retorno."\n/CaixaSaida:Criado\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Log")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Log", 0777);
				$retorno = $retorno."/CaixaSaida/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Log:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Preparar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Preparar", 0777);
				$retorno = $retorno."/CaixaSaida/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Preparar:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Processado")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processado", 0777);
				$retorno = $retorno."/CaixaSaida/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processado:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Processar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processar", 0777);
				$retorno = $retorno."/CaixaSaida/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processar:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Temporario")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Temporario", 0777);
				$retorno = $retorno."/CaixaSaida/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Temporario:Existente\n";
			
		}
		else
		{
			$retorno = $retorno."\n/CaixaSaida:Existente\n";
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Log")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Log", 0777);
				$retorno = $retorno."/CaixaSaida/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Log:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Preparar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Preparar", 0777);
				$retorno = $retorno."/CaixaSaida/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Preparar:Existente\n";

			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Processado")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processado", 0777);
				$retorno = $retorno."/CaixaSaida/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processado:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Processar")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processar", 0777);
				$retorno = $retorno."/CaixaSaida/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processar:Existente\n";
			
			if(!is_dir($tFiliaisNfeIntegracao."/CaixaSaida/Temporario")) 
			{
				mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Temporario", 0777);
				$retorno = $retorno."/CaixaSaida/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Temporario:Existente\n";
		}

	}
	else
	{
		mkdir ($tFiliaisNfeIntegracao, 0777);

		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida", 0777);

		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Log", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Preparar", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processado", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Processar", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaEntrada/Temporario", 0777);

		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Log", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Preparar", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processado", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Processar", 0777);
		mkdir ($tFiliaisNfeIntegracao."/CaixaSaida/Temporario", 0777);

		$retorno = $retorno."/CaixaEntrada:Criado\n";
		$retorno = $retorno."/CaixaEntrada/Log:Criado\n";
		$retorno = $retorno."/CaixaEntrada/Preparar:Criado\n";
		$retorno = $retorno."/CaixaEntrada/Processado:Criado\n";
		$retorno = $retorno."/CaixaEntrada/Processar:Criado\n";
		$retorno = $retorno."/CaixaEntrada/Temporario:Criado\n";
		$retorno = $retorno."\n/Caixa de Saida:Criado\n";
		$retorno = $retorno."/CaixaSaida/Log:Criado\n";
		$retorno = $retorno."/CaixaSaida/Preparar:Criado\n";
		$retorno = $retorno."/CaixaSaida/Processado:Criado\n";
		$retorno = $retorno."/CaixaSaida/Processar:Criado\n";
		$retorno = $retorno."/CaixaSaida/Temporario:Criado\n";

	}

	$json = json_encode($retorno);

	echo($json);






?>