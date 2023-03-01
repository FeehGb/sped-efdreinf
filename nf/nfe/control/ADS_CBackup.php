<?php
/**
 * @name      	CBackup
 * @version   	alfa
 * @copyright	2015 &copy; Softdib
 * @author    	Guilherme Pinto
 * @description Classe que efetua a guarda do XML em arquivo de backup
*/

/**
 * @class CBackup
 */ 
class CBackup{

/*
 * Atributos da Classe
 */
	public $mensagemErro = "";
	
/**
 * @method mSubmeterLote
 * @autor Guilherme Pinto
 */
	public function mGuardarXml($pXml,$pChave, $pCnpj, $pTipo){
		$localDia = date("d");
		$localMes = date("m");
		$localAno = date("Y");

		// Obter a pasta raiz para a guarda / backup 
		
		$diretorioBackup = "/user/nfe/".$pCnpj."/RET/";
		
		// Verifica se o diretorio backup existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup)){
			if(!mkdir($diretorioBackup)){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$retorno['diretorio_backup']." crie manualmente ou verifique as permissoes";
				return false;
			}
		}

		// Verifica se o diretorio backup + ANO existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/")){
			if(!mkdir($diretorioBackup.$localAno."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/".$localMes."/")){
			if(!mkdir($diretorioBackup.$localAno."/".$localMes."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/".$localMes."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES + DIA existe, caso nao tentar cria-lo
		if(!is_dir($diretorioBackup.$localAno."/".$localMes."/".$localDia."/")){
			if(!mkdir($diretorioBackup.$localAno."/".$localMes."/".$localDia."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}

		switch($pTipo){
			case "nfe";
				$localExtension = "-procNFe.xml";
			break;
			case "cc":
				$localExtension = "-procCCeNFe.xml";
			break;
			case "canc":
				$localExtension = "-procCanNFe.xml";
			break;
			case "inut":
				$localExtension = "-procInutNFe.xml";
			break;
                        case "nfc";
				$localExtension = "-procNFC.xml";
			break;
			case "cancc":
				$localExtension = "-procCanNFC.xml";
			break;
			case "inutc":
				$localExtension = "-procInutNFC.xml";
			break;
			default:
				$localExtension = $pTipo.".xml";
			break;
		}

		if(!file_put_contents($diretorioBackup.$localAno."/".$localMes."/".$localDia."/".$pChave.$localExtension,$pXml)){
			$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o arquivo ".$diretorioBackup.$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
			return false;
		}

	}
}
?>