<?php

	$tMDFeIntegracao = "../../../../../../../".$_POST['tMDFeIntegracao'];

	$tMDFeDiretorioImportacao = "../../../../../../..".$_POST['tMDFeImportacao'];

	$tMDFeDiretorioBackup = "../../../../../../..".$_POST['tMDFeBackup'];

	$tMDFeBaseCliente = "../../../../../../..".$_POST['tMDFeBaseCliente'];

	$retorno = "";

	if(!is_dir($tMDFeDiretorioImportacao)) 
	{
		mkdir ($tMDFeDiretorioImportacao, 0777);
		$retorno = $retorno."/importacao:Criado\n";
	}
	else
	{	
		$retorno = $retorno."/importacao:Existente\n";
	}

	if(!is_dir($tMDFeDiretorioBackup)) 
	{
		mkdir ($tMDFeDiretorioBackup, 0777);
		$retorno = $retorno."/backup:Criado\n";
	}
	else
	{	
		$retorno = $retorno."/backup:Existente\n";
	}

	if(!is_dir($tMDFeBaseCliente)) 
	{
		$retorno = $retorno."/".substr($tMDFeBaseCliente, 26).":Nao Existe\n\n";
	}
	else
	{	
		$retorno = $retorno."/".$tMDFeBaseCliente.":Existente\n\n";
	}


	if(is_dir($tMDFeIntegracao)) 
	{
		if(!is_dir($tMDFeIntegracao."/CaixaEntrada")) 
		{
			mkdir ($tMDFeIntegracao."/CaixaEntrada", 0777);
			$retorno = $retorno."/CaixaEntrada:Criado\n";

			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Log")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Log", 0777);
				$retorno = $retorno."/CaixaEntrada/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Log:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Preparar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Preparar", 0777);
				$retorno = $retorno."/CaixaEntrada/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Preparar:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Processado")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Processado", 0777);
				$retorno = $retorno."/CaixaEntrada/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processado:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Processar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Processar", 0777);
				$retorno = $retorno."/CaixaEntrada/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processar:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Temporario")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Temporario", 0777);
				$retorno = $retorno."/CaixaEntrada/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Temporario:Existente\n";
			
		}
		else
		{
			$retorno = $retorno."/CaixaEntrada:Existente\n";
			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Log")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Log", 0777);
				$retorno = $retorno."/CaixaEntrada/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Log:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Preparar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Preparar", 0777);
				$retorno = $retorno."/CaixaEntrada/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Preparar:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Processado")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Processado", 0777);
				$retorno = $retorno."/CaixaEntrada/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processado:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Processar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Processar", 0777);
				$retorno = $retorno."/CaixaEntrada/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Processar:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaEntrada/Temporario")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaEntrada/Temporario", 0777);
				$retorno = $retorno."/CaixaEntrada/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaEntrada/Temporario:Existente\n";
		}

		if(!is_dir($tMDFeIntegracao."/CaixaSaida")) 
		{
			mkdir ($tMDFeIntegracao."/CaixaSaida", 0777);
			$retorno = $retorno."\n/CaixaSaida:Criado\n";

			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Log")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Log", 0777);
				$retorno = $retorno."/CaixaSaida/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Log:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Preparar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Preparar", 0777);
				$retorno = $retorno."/CaixaSaida/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Preparar:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Processado")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Processado", 0777);
				$retorno = $retorno."/CaixaSaida/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processado:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Processar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Processar", 0777);
				$retorno = $retorno."/CaixaSaida/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processar:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Temporario")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Temporario", 0777);
				$retorno = $retorno."/CaixaSaida/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Temporario:Existente\n";
			
		}
		else
		{
			$retorno = $retorno."\n/CaixaSaida:Existente\n";
			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Log")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Log", 0777);
				$retorno = $retorno."/CaixaSaida/Log:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Log:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Preparar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Preparar", 0777);
				$retorno = $retorno."/CaixaSaida/Preparar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Preparar:Existente\n";

			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Processado")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Processado", 0777);
				$retorno = $retorno."/CaixaSaida/Processado:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processado:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Processar")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Processar", 0777);
				$retorno = $retorno."/CaixaSaida/Processar:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Processar:Existente\n";
			
			if(!is_dir($tMDFeIntegracao."/CaixaSaida/Temporario")) 
			{
				mkdir ($tMDFeIntegracao."/CaixaSaida/Temporario", 0777);
				$retorno = $retorno."/CaixaSaida/Temporario:Criado\n";
			}
			else $retorno = $retorno."/CaixaSaida/Temporario:Existente\n";
		}

	}
	else
	{
		mkdir ($tMDFeIntegracao, 0777);

		mkdir ($tMDFeIntegracao."/CaixaEntrada", 0777);
		mkdir ($tMDFeIntegracao."/CaixaSaida", 0777);

		mkdir ($tMDFeIntegracao."/CaixaEntrada/Log", 0777);
		mkdir ($tMDFeIntegracao."/CaixaEntrada/Preparar", 0777);
		mkdir ($tMDFeIntegracao."/CaixaEntrada/Processado", 0777);
		mkdir ($tMDFeIntegracao."/CaixaEntrada/Processar", 0777);
		mkdir ($tMDFeIntegracao."/CaixaEntrada/Temporario", 0777);

		mkdir ($tMDFeIntegracao."/CaixaSaida/Log", 0777);
		mkdir ($tMDFeIntegracao."/CaixaSaida/Preparar", 0777);
		mkdir ($tMDFeIntegracao."/CaixaSaida/Processado", 0777);
		mkdir ($tMDFeIntegracao."/CaixaSaida/Processar", 0777);
		mkdir ($tMDFeIntegracao."/CaixaSaida/Temporario", 0777);

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