<?php
/**
* @name      	CBackup
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe fazer procedimentos automático de backup e também efetuar a guarda do XML em arquivo.
 * @TODO 		Testar o código com todas as alternativas
*/

/**
 * @import Importação de Classes de comunicação
 */
 require_once(__ROOT__."/model/MContribuinte.php");

/**
 * @class CBackup
 */ 
class CBackup{

/*
 * Atributos da Classe
 */
	public $mensagemErro = "";
	
	private $grupo;
	
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}

/**
 * @method mSubmeterLote
 * @autor Guilherme Silva
 * @TODO  testar tudo
 */
	public function mGuardarXml($pXml,$pChave, $pCnpj, $pTipo){
		$localDia = date("d");
		$localMes = date("m");
		$localAno = date("Y");

		// Obter a pasta raiz para a guarda / backup
		
		// Selecionar Todos os Contribuintes para obter os caminhos de integracao
		$MContribuinte = new MContribuinte($this->grupo);
		$MContribuinte->cnpj = $pCnpj;

		$retorno = $MContribuinte->selectAll();
		
		if(!$retorno){
			$this->mensagemErro = $MContribuinte->mensagemErro;
			return false;
		}
		if($retorno[0]['diretorio_backup'] == ""){
			$this->mensagemErro = "CBackup ->mGuardarXml: Nao foi definido um local para salvar o backup, verifique a instalacao do sistema.";
			return false;
		}

		// Verifica se o diretorio backup existe, caso nao tentar cria-lo
		if(!is_dir($retorno[0]['diretorio_backup'])){
			if(!mkdir($retorno[0]['diretorio_backup'])){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$retorno['diretorio_backup']." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		if(substr($retorno[0]['diretorio_backup'],-1) != "/"){
			$retorno[0]['diretorio_backup'] = $retorno[0]['diretorio_backup']."/";
		}

		// Verifica se o diretorio backup + ANO existe, caso nao tentar cria-lo
		if(!is_dir($retorno[0]['diretorio_backup'].$localAno."/")){
			if(!mkdir($retorno[0]['diretorio_backup'].$localAno."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$retorno[0]['diretorio_backup'].$localAno."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES existe, caso nao tentar cria-lo
		if(!is_dir($retorno[0]['diretorio_backup'].$localAno."/".$localMes."/")){
			if(!mkdir($retorno[0]['diretorio_backup'].$localAno."/".$localMes."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$retorno[0]['diretorio_backup'].$localAno."/".$localMes."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}
		
		// Verifica se o diretorio backup + ANO + MES + DIA existe, caso nao tentar cria-lo
		if(!is_dir($retorno[0]['diretorio_backup'].$localAno."/".$localMes."/".$localDia."/")){
			if(!mkdir($retorno[0]['diretorio_backup'].$localAno."/".$localMes."/".$localDia."/")){
				$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o diretorio ".$retorno[0]['diretorio_backup'].$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
				return false;
			}
		}

		switch($pTipo){
			case "mdfe";
				$localExtension = "-procMDFe.xml";
			break;
			case "evento":
				$localExtension = "-procEventoMDFe.xml";
			break;
			case "canc":
				$localExtension = "-procCanMDFe.xml";
			break;
			default:
				$localExtension = $pTipo.".xml";
			break;
		}

		if(!file_put_contents($retorno[0]['diretorio_backup'].$localAno."/".$localMes."/".$localDia."/".$pChave.$localExtension,$pXml)){
			$this->mensagemErro = "CBackup ->mGuardarXml: Falha ao criar o arquivo ".$retorno[0]['diretorio_backup'].$localAno."/".$localMes."/".$localDia."/"." crie manualmente ou verifique as permissoes";
			return false;
		}

	}
}
?>