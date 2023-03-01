<?php
    /*
        Programa:  mdfe.php
        Descricão: Programa responsavel por emitir manifestações de destinatário eletrônica a partir de um arquivo criado na Caixa de Entrada
        Autor:     J. Eduardo Lino (14/06/2016), adaptado de Guilherme Pinto (04/04/2016)

        *** Detalhes do Processo ***
        COBOL gera arquivos SMDFE-, CMDFE-, EMDFE- e MDFE- no diretório: "/user/mdfe/<cnpj>/CaixaEntrada/Processar/"
        PHP lê arquivos gerados pelo COBOL e grava retorno no diretório: "/user/mdfe/<cnpj>/CaixaSaida/Sefaz/"
        Quando for Emissão do Lote, Candelamento ou Encerramento, será gravado o xml de retorno no diretório: "/user/mdfe/<cnpj>/RET/<ano>/<mes>/<dia>/"
        Quando for Emissão do Lote, será gravado o arquivo -consLote no diretório: "/var/www/html/nf/mdfe/pendentes/"
    */

    /* Classes instanciadas */
    require_once("libs/ConvertMDFePHP.class.php");
    require_once("libs/MDFeNFePHP.class.php");
	//require_once("ant/libs/ToolsNFePHP_MDFe.class.php");

	$cnpj="";
	$ambiente="";
	$tpEmis="";
	$cUF="";
	$chave="";

    $nomeArquivo = explode("/",$argv[1]);
    $nomeArquivo = end($nomeArquivo);
    $nomeArquivoSaida = $argv[1];
    $conteudo_arquivo = file_get_contents($argv[1]);

    /* Verifica qual a opção de entrada: Envio, Cancelamento ou Encerramento */
    if (strpos($nomeArquivo, 'SMDFE') !== false)
    {
        $opcao = "SMDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/SMDFE", "CaixaSaida/Sefaz/SMDFER", $nomeArquivoSaida);
    }
    else if (strpos($nomeArquivo, 'CMDFE') !== false)
    {
        $opcao = "CMDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/CMDFE", "CaixaSaida/Sefaz/CMDFER", $nomeArquivoSaida);
    }
    else if (strpos($nomeArquivo, 'RMDFE') !== false)
    {
        $opcao = "RMDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/RMDFE", "CaixaSaida/Sefaz/RMDFER", $nomeArquivoSaida);
    }
    else if (strpos($nomeArquivo, 'EMDFE') !== false)
    {
        $opcao = "EMDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/EMDFE", "CaixaSaida/Sefaz/EMDFER", $nomeArquivoSaida);
    }
    else if (strpos($nomeArquivo, 'NMDFE') !== false)
    {
        $opcao = "NMDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/NMDFE", "CaixaSaida/Sefaz/NMDFER", $nomeArquivoSaida);
    }
    else if (strpos($nomeArquivo, 'MDFE') !== false)
    {
        $opcao = "MDFE";
        $nomeArquivoSaida = str_replace("CaixaEntrada/Processar/MDFE", "CaixaSaida/Sefaz/LMDFER", $nomeArquivoSaida);
    }
    

	switch($opcao)
    {
        /* Verifica o status do web service do MDFe */
        case "SMDFE":
            $arrayTXT = explode("|", $conteudo_arquivo);

            $cnpj = $arrayTXT[0];
            $uf = $arrayTXT[1];
            $tipoEmissao = $arrayTXT[2];
            $ambiente = trim(str_replace("\n", "", $arrayTXT[3]));
                
            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $uf;
            $arrayConfig['cnpj'] = $cnpj;

            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
            if(!$MDFeNFePHP){
                $mensagem = "|||||||Erro ao instanciar a classe MDFeNFePHP||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
            }

            $retorno = $MDFeNFePHP->statusServico($uf, $ambiente, '');
            $mensagem = $retorno["cStat"]."|".$retorno["xMotivo"]."|".$retorno["dhRecbto"]."|".$retorno["tMed"]."|".$retorno['xObs']."|".$retorno['xml'];

            file_put_contents($nomeArquivoSaida, $mensagem);

            break;

        /* Envio do Lote */
        case "MDFE":
    	    /* Converte TXT em XML */
    		$ConvertMDFePHP = new ConvertMDFePHP();
    		$xml = $ConvertMDFePHP->MDFetxt2xml($conteudo_arquivo);

            /* Verifica se deu erro ao converter o TXT em XML ou se o arquivo esta correto */
    		if((!$xml) || $xml == ""){
                $mensagem = "|||||||Erro ao converter o TXT em XML||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
    		}

            /* Inicializa o valor das variaveis que são necessarias para o envio do lote a partir do conteudo do XML convertido */
            $xml_txt = @simplexml_load_string($xml[0]);
    		$cnpj = $xml_txt->infMDFe->emit->CNPJ;
    		$tpEmis = $xml_txt->infMDFe->ide->tpEmis;
    		$chave = str_replace("MDFe", "", $xml_txt->infMDFe["Id"]);
            $modelo = $xml_txt->infMDFe->ide->mod;
            $ambiente = $xml_txt->infMDFe->ide->tpAmb;
            $cUF = $xml_txt->infMDFe->ide->cUF;

            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $cUF;
            $arrayConfig['cnpj'] = $cnpj;

    		/* Instancia a comunição com classe de envio */
            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
    		if(!$MDFeNFePHP){
                $mensagem = "|||||||Erro ao instanciar a classe MDFeNFePHP||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
    		}

            /* Assinatura do XML */
            $xml = $MDFeNFePHP->signXML($xml[0], 'infMDFe');
    		if(!$xml){
                $mensagem = "|||||||".$MDFeNFePHP->errMsg."||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
    		}

    		$retornoErro="";
            $motivo="";
            /* Validação do XML */
    		$retorno = $MDFeNFePHP->validXML($xml,'',$retornoErro);
    		if(!$retorno){
    			foreach ($retornoErro as $er){
    				$motivo .= $er;
                    echo $er;
    			}
                $motivo = utf8_decode(trim(preg_replace('/\s\s+/', ' ', $motivo)));
                $mensagem = $cnpj."|".$ambiente."|".$tpEmis."|".$cUF."||".$chave."|04|".$motivo."||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
    		}
    		
    		/* Grava o XML que será enviado para a Sefaz no diretorio '/var/www/html/nf/mdfe/temp/' */
    		file_put_contents("/var/www/html/nf/mdfe_cobol/temp/".$chave."-pendMDFe.xml",$xml);

            /* Envio do Lote para a Sefaz */
            $retMDFe = $MDFeNFePHP->sendLot($xml);
            
            /* Gravação do TXT de retorno */
            $mensagem = $cnpj."|".$ambiente."|".$tpEmis."|".$cUF."|".$retMDFe['nRec']."|".$chave."|".$retMDFe["cStat"]."|".$retMDFe["xMotivo"]."|".$retMDFe["dhRecbto"]."|".$retMDFe["xml"];
            file_put_contents($nomeArquivoSaida, $mensagem);

            if(trim($retMDFe["nRec"]) != "")
            {
                $mensagem = $nomeArquivo."|".$cnpj."|".$ambiente."|".$tpEmis."|".$cUF."|".$retMDFe["nRec"]."|".$chave;
                file_put_contents("/var/www/html/nf/mdfe_cobol/pendentes/".$retMDFe["lote"].".consLote",$mensagem);
            }
            break;


        /* Cancelamento */
		case "CMDFE":
            $arrayTXT = explode("|", $conteudo_arquivo);

            $cnpj = $arrayTXT[0];
            $uf = $arrayTXT[1];
            $ambiente = $arrayTXT[2];
            $chave = $arrayTXT[3];
            $protocolo = $arrayTXT[4];
            $tipo_evento = $arrayTXT[5];
            $justificativa = $arrayTXT[6];

            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $uf;
            $arrayConfig['cnpj'] = $cnpj;
                
            /* Instancia a comunição com classe de envio */
            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
            if(!$MDFeNFePHP){
                $mensagem = "|||||||Erro ao instanciar a classe MDFeNFePHP||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
            }
            
            /* Comunicação com a Sefaz para a manifestação de cancelamento */
            $retorno = $MDFeNFePHP->manifDest($chave, $tipo_evento, $ambiente, $justificativa, '2', &$aRetorno, $protocolo);

            $modelo = substr($chave,20,2);
            $serieNota = substr($chave,22,3);
            $numeroNota = substr($chave,25,9);

            $mensagem = $cnpj."|".$uf."|".$ambiente."|".$serieNota."|".$numeroNota."|".$tipo_evento."|".$aRetorno["cStat"]."|".$aRetorno["xMotivo"]."|".$chave."|".$aRetorno["dhReceb"]."|".$aRetorno['nProt']."|".$aRetorno['cOrgao']."|".$aRetorno['xml'];

            file_put_contents($nomeArquivoSaida, $mensagem);
  
            mGuardarXmlRET($aRetorno['xml'], $chave, $cnpj, $aRetorno["dhReceb"], "C");
		break;
        
		// Solcitacao de Encerramento do Manifesto
        case "EMDFE":
            $arrayTXT = explode("|", $conteudo_arquivo);

            $cnpj = $arrayTXT[0];
            $uf = $arrayTXT[1];
            $ambiente = $arrayTXT[2];
            $chave = $arrayTXT[3];
            $protocolo = $arrayTXT[4];
            $tipo_evento = $arrayTXT[5];
            $data_encerramento = $arrayTXT[6];
            $uf_encerramento = $arrayTXT[7];
            $cidade_encerramento = $arrayTXT[8];

            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $uf;
            $arrayConfig['cnpj'] = $cnpj;
                
            /* Instancia a comunição com classe de envio */
            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
            if(!$MDFeNFePHP){
                $mensagem = "|||||||Erro ao instanciar a classe MDFeNFePHP||";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
            }

            /* Comunicação com a Sefaz para a manifestação de cancelamento */
            $retorno = $MDFeNFePHP->manifDest($chave, $tipo_evento, $ambiente, $cidade_encerramento, '2', &$aRetorno, $protocolo);

            $modelo = substr($chave,20,2);
            $serieNota = substr($chave,22,3);
            $numeroNota = substr($chave,25,9);

            $mensagem = $cnpj."|".$uf."|".$ambiente."|".$serieNota."|".$numeroNota."|".$tipo_evento."|".$aRetorno["cStat"]."|".$aRetorno["xMotivo"]."|".$chave."|".$aRetorno["dhReceb"]."|".$aRetorno['nProt']."|".$aRetorno['cOrgao']."|".$aRetorno['xml'];

            file_put_contents($nomeArquivoSaida, $mensagem);
  
            mGuardarXmlRET($aRetorno['xml'], $chave, $cnpj, $aRetorno["dhReceb"], "E");

        break;

        // Solcitacao de Manifestos Não-Encerrados
		case "NMDFE":
            $arrayTXT = explode("|", $conteudo_arquivo);

            $cnpj = $arrayTXT[0];
            $uf = $arrayTXT[1];
            $servico = $arrayTXT[2];
            $ambiente = trim(str_replace("\n", "", $arrayTXT[3]));

            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $uf;
            $arrayConfig['cnpj'] = $cnpj;
                
            /* Instancia a comunição com classe de envio */
            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
            if(!$MDFeNFePHP){
                $mensagem = "||||Erro ao instanciar a classe MDFeNFePHP|";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
            }

            /* Comunicação com a Sefaz para o retorno dos manifestos não-encerrados */
            $retorno = $MDFeNFePHP->mdfeConsNaoEnc($cnpj, $uf, $ambiente, '');

            $mensagem = "000|".$cnpj."|".$ambiente."|".$retorno["cStat"]."|".$retorno["xMotivo"]."|".$retorno["cUF"]."|\n";

            for($i=0; $i < intval($retorno['infMDFe']['qtde']); $i++)
            {
                $mensagem .= "001|".$retorno['infMDFe'][$i]["chMDFe"]."|".$retorno['infMDFe'][$i]["nProt"]."|\n";
            }

            file_put_contents($nomeArquivoSaida, $mensagem);
  
            //mGuardarXmlRET($retorno['xml'], $chave, $cnpj, $retorno["dhRecbto"], "N");

		break;

        // Consulta status do manifesto
        case "RMDFE":
            $arrayTXT = explode("|", $conteudo_arquivo);

            $cnpj = $arrayTXT[0];
            $uf = $arrayTXT[1];
            $ambiente = $arrayTXT[2];
            $chave = trim(str_replace("\n", "", $arrayTXT[3]));

            $arrayConfig['ambiente'] = $ambiente;
            $arrayConfig['uf'] = $uf;
            $arrayConfig['cnpj'] = $cnpj;
                
            /* Instancia a comunição com classe de envio */
            $MDFeNFePHP = new MDFeNFePHP($arrayConfig);
            if(!$MDFeNFePHP){
                $mensagem = "||||Erro ao instanciar a classe MDFeNFePHP|";
                file_put_contents($nomeArquivoSaida, $mensagem);
                exit();
            }

            $retorno = $MDFeNFePHP->getProtocol('', $chave, $ambiente, '2', $aRetorno);

            //print_r($aRetorno);

            if($retorno["aEven"] == '')
            {
                $mensagem = $chave."|".$ambiente."|".$retorno["cStat"]."|".$retorno["xMotivo"]."|".$retorno["nProt"]."|".$retorno["dhRecbto2"]."|".$retorno["tpEvento"]."|||".$retorno["xml"];
            }
            else
            {
                $mensagem = $chave."|".$ambiente."|".$retorno["cStat"]."|".$retorno["xMotivo"]."|".$retorno["nProt"]."|".$retorno["dhRecbto2"]."|".$retorno["tpEvento"]."|".$retorno["aEven"][0]["nProt"]."|".$retorno["aEven"][0]["dhRegEvento"]."|".$retorno["xml"];
            }

            //echo $mensagem;

            file_put_contents($nomeArquivoSaida, $mensagem);

        break;
        
		default:
			exit();
		break;
	}


    function mGuardarXmlRET($xml, $chave, $cnpj, $dhRecbto, $tipo_arquivo)
    {
        $data = explode("T", $dhRecbto);
        $aData = explode("-", $data[0]);
        $localAno = $aData[0];
        $localMes = $aData[1];
        $localDia = $aData[2];

        $diretorioBackup = "/user/mdfe/".$cnpj."/RET/";
        $nome_arquivo = "";

        if($tipo_arquivo == "C")
        {
            $nome_arquivo = "-procCaMDFe.xml";
        }
        else if($tipo_arquivo == "E")
        {
            $nome_arquivo = "-procEnMDFe.xml";
        }
        else if($tipo_arquivo == "N")
        {
            $nome_arquivo = "-procNaMDFe.xml";
        }

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

        //echo "\n".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/".$chave."-procCanMDFe.xml\n";
        if(!file_put_contents($diretorioBackup.$localAno."/".$localMes."/".$localDia."/".$chave.$nome_arquivo,$xml)){
            echo "consultaLoteMDFe->mGuardarXml: Falha ao criar o arquivo ".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
            return false;
        }
    }

?>