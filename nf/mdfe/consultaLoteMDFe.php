<?php
	/*
		Programa:  consultaLote.php
		Descrição: Programa responsavel por consultar a resposta do Lote Sefaz atraves do diretório mdfe/pendentes/
		Autor:     J. Eduardo Lino (21/06/2016)
	*/

	/* Classes instanciadas */
	require_once("libs/MDFeNFePHP.class.php");
	
	$retorno = mLerArquivosPendentes();

	if(!$retorno){
		//echo "consultaLoteMDFe->Erro ao fazer a leitura dos arquivos pendentes.";
		exit();
	}

	foreach($retorno as $pendente)
	{
		$arrayConfig['ambiente'] = $pendente["ambiente"];
        $arrayConfig['uf'] = $pendente["cUF"];
        $arrayConfig['cnpj'] = $pendente["cnpj"];
     
        $MDFeNFePHP = new MDFeNFePHP($arrayConfig);

		$aRetorno="";

		//$retorno = $MDFeNFePHP->getProtocol('', $pendente['chave'], $pendente['ambiente'], $aRetorno);   
		$retorno = $MDFeNFePHP->getProtocol($pendente['recibo'], '', $pendente['ambiente'], '', $aRetorno);   

		switch($retorno['cStat']){
			/* Nota Fiscal Autorizada */
			case '104':
				rename($pendente['caminho'], $pendente['caminho'].".processado");

				$mensagem = $pendente['cnpj']."|".$pendente['cUF']."|".$pendente['anomes']."|".$pendente['modelo']."|".$pendente['serie']."|".$pendente['numero']."|".$pendente['chave']."|".$pendente['ambiente']."|".$retorno["cUF"]."|".$retorno["dhRecbto"]."|".$retorno["nProt"]."|".$retorno["protcStat"]."|".$retorno["protxMotivo"]."|".$retorno['xmlRetorno'];

            	file_put_contents("/user/mdfe/".$pendente["cnpj"]."/CaixaSaida/Sefaz/MDFER/MDFER-".$pendente['numero']."-".$pendente['serie']."-".date("Ymd")."-".date("his").".TXT", $mensagem);

            	// tirei o recprot -> olhar no MDFeNFePHP
            	
		        if(!is_dir("/var/www/html/nf/mdfe_cobol/temp/")){
		            mkdir("/var/www/html/nf/mdfe_cobol/temp/");
		        }
				$xml = file_get_contents("/var/www/html/nf/mdfe_cobol/temp/".$pendente['chave']."-pendMDFe.xml");
				$xml = $MDFeNFePHP->addProt($xml, $retorno['xmlRetorno']);
		               
            	mGuardarXmlRET($xml, $pendente['chave'], $pendente['cnpj']);
				break;

			/*
			case '108':
			case '109':
			default:
				rename($pendente['caminho'], $pendente['caminho'].".processado.erro");

				$mensagem = $pendente['cnpj']."|".$pendente['cUF']."|".$pendente['anomes']."|".$pendente['modelo']."|".$pendente['serie']."|".$pendente['numero']."|".$pendente['chave']."|".$pendente['ambiente']."|".$retorno["cUF"]."|".$retorno["dhRecbto"]."|".$retorno["protcStat"]."|".$retorno["cStat"]."|".$retorno["protxMotivo"]."|";

		        file_put_contents("/user/mdfe/".$pendente["cnpj"]."/CaixaSaida/Sefaz/MDFER/MDFER-".$pendente['numero']."-".$pendente['serie']."-".date("Ymd")."-".date("his").".TXT", $mensagem);
				break;
			*/
		}

	}
	
	function mLerArquivosPendentes()
	{
		$arrayRetorno = false;
		$caminho = "/var/www/html/nf/mdfe_cobol/pendentes/";
		
		$diretorio = dir($caminho);
		while($arquivo = $diretorio->read()){
			$pos = strripos($arquivo, ".processado");
			$con = strripos($arquivo, ".consLote");
			if($arquivo != '..' && $arquivo != '.' && $pos === false && $con !== false){
				// Cria um Arrquivo com todos os Arquivos encontrados
				$explode = explode("|",file_get_contents($caminho.$arquivo));

                $arrayTemp['nomeArquivo'] = $explode[0];
				$arrayTemp['cnpj']        = $explode[1];
				$arrayTemp['ambiente']    = $explode[2];
				$arrayTemp['tpEmis']      = $explode[3];
				$arrayTemp['cUF']         = $explode[4];
				$arrayTemp['recibo']      = $explode[5];
				$arrayTemp['chave']       = $explode[6];
				$arrayTemp['anomes']      = substr($explode[6],2,4);
				$arrayTemp['modelo']      = substr($explode[6],20,2);
				$arrayTemp['numero']      = substr($explode[6],25,9);
				$arrayTemp['serie']       = substr($explode[6],22,3);
				$arrayTemp['caminho']     = $caminho.$arquivo; 

				$arrayRetorno[] = $arrayTemp;
			}
		}
		return $arrayRetorno;
	}

	function mGuardarXmlRET($xml, $chave, $cnpj)
	{
		$xmlMDFe = new DOMDocument('1.0', 'utf-8');
        $xmlMDFe->formatOutput = false;
        $xmlMDFe->preserveWhiteSpace = false;
        $xmlMDFe->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

		$infProt = $xmlMDFe->getElementsByTagName("infProt")->item(0);        
        $dhRecbto = !empty($infProt->getElementsByTagName('dhRecbto')->item(0)->nodeValue) ? $infProt->getElementsByTagName('dhRecbto')->item(0)->nodeValue : '';

		$data = explode("T", $dhRecbto);
		$aData = explode("-", $data[0]);
		$localAno = $aData[0];
		$localMes = $aData[1];
		$localDia = $aData[2];

		$diretorioBackup = "/user/mdfe/".$cnpj."/RET/";

		// Verifica se o diretorio backup existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup)){
			if(!mkdir($diretorioBackup)){
				echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup." crie manualmente ou verifique as permissoes";
				return false;
			}
		}

		// Verifica se o diretorio backup + ANO existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/")){
			if(!mkdir($diretorioBackup.$localAno."/")){
				echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/".$localMes."/")){
			if(!mkdir($diretorioBackup.$localAno."/".$localMes."/")){
				echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/".$localMes."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES + DIA existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/".$localMes."/".$localDia."/")){
			if(!mkdir($diretorioBackup.$localAno."/".$localMes."/".$localDia."/")){
				echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}

		if(!file_put_contents($diretorioBackup.$localAno."/".$localMes."/".$localDia."/".$chave."-procMDFe.xml",$xml)){
			echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o arquivo ".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
			return false;
		}
	}
?>