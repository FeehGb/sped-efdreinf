<?php
/**
 * @name      	MConfig
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com a base de dados nfse_config
 * 				OBSERVACAO IMPORTANTE: esta classe utiliza do projeto de NFSe instalado e configurado
 * @TODO 		Testear Classe
*/

	require_once("/var/www/html/nf/nfse/model/CBd.php");

/**
 * Classe MConfig
 */

 class MConfig{
 
	private $grupo;
 
	/*
     *	 Atributos (campos) da tabela Contribuintes
     */
	public $cnpj;
	public $nome;
	public $bd;
	public $nome_grupo;
	 
	/*
     *	 Atributos locais para comunicação com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	
	
	// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Função para selecionar as empresas cadastradas
	 *	@autor Guilherme Silva
	 */
	public function listarBancos(){
		$CBd = CBd::singleton($this->grupo);
		
		if(!$CBd){ return false; }
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT SCHEMA_NAME as base FROM INFORMATION_SCHEMA.SCHEMATA JOIN nfse_config.empresas ON SCHEMA_NAME = nfse_config.empresas.bd_mdfe";
		
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MConfig -> listarBancos() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			return $resultado;
		}
	}

}
?>