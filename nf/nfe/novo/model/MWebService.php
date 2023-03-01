<?php
/**
 * @name      	MWebService
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela WEB SERVICE do Banco de Dados
 * @TODO 		Testear Classe
*/

	require_once("MBd.php");
/**
 * Classe MWebService
 */

class MWebService{
	/*
     *	 Atributos (campos) da tabela Web Service
     */
	public $uf = "";
	public $versao_xml = "";
	public $servico = "";
	public $ambiente = "";
	public $metodo = "";
	public $nome = "";
	public $cnpj_web_service = "";
	public $cod_uf_ibge = "";
	public $metodo_conexao = "";
	public $url_completa = "";
	public $situacao = "";
	public $xsd = "";

	/*
     *	 Atributos locais para comunicação com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	private $grupo;
	
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Função para Criar a tabela Web Service
	 *	@autor Guilherme Silva
	 */
	/*public function createTable(){
		$CBd = CBd::singleton($this->grupo);
		if(!$CBd){ return false; }
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "CREATE  TABLE IF NOT EXISTS `mydb`.`CONTRIBUINTE` (
				  `cnpj` VARCHAR(14) NOT NULL COMMENT 'CNPJ do Contribuinte, Emissor da Nota Fiscal.' ,
				  `ambiente` VARCHAR(1) NOT NULL COMMENT 'Tipo de Ambiente:\n0-Homologação.\n1-Produção.' ,
				  `uf` VARCHAR(2) NOT NULL ,
				  `cod_emp_fil_softdib` INT(6) NULL COMMENT 'Código da Empresa e Filial que consta no Softdib.' ,
				  `razao_social` VARCHAR(100) NOT NULL COMMENT 'Razão Social da Empresa.' ,
				  `certificado_tipo` VARCHAR(2) NOT NULL COMMENT 'A1;\nA3;\nAinda não foi encontrada nenhum programa que autentique com sucesso no certificado A3, apenas A1.' ,
				  `certificado_caminho` TEXT NOT NULL COMMENT 'Caminho do Certificado Digital (PFX).\n( ./nfe/certs)\n' ,
				  `certificado_senha` VARCHAR(50) NOT NULL COMMENT 'Senha do certificado digital utilizado para emissão da NF.' ,
				  `contigencia` VARCHAR(2) NOT NULL COMMENT '01-Normal sem contigência\n02-DPEC\n03-SCAN' ,
				  `pacote_xsd` VARCHAR(10) NOT NULL COMMENT 'Nome do Pacote XSD\nÚltima Versão: 6r.\nPL_006S' ,
				  `email_usuario` VARCHAR(100) NULL COMMENT 'Nome de usuário do emitente da Nota Fiscal.' ,
				  `email_senha` VARCHAR(100) NULL COMMENT 'Senha do Email do emitente da NF.' ,
				  `email_remetente` VARCHAR(100) NULL ,
				  `email_smtp` VARCHAR(100) NULL COMMENT 'Caminho do Servidor SMTP do email do emitente da NF.' ,
				  `email_porta` VARCHAR(4) NULL COMMENT 'Código da Porta do Servidor de email do emitente da NF.' ,
				  `email_ssl` VARCHAR(1) NULL COMMENT 'Flag se a conexão é SSL ou não.\n0-Não;\n1-Sim.' ,
				  `email_conf_recebimento` VARCHAR(1) NULL COMMENT 'Flag se habilita a confirmação de recebimento do Proxy.' ,
				  `proxy_servidor` VARCHAR(100) NULL COMMENT 'Servidor Proxy para conexão com Internet.' ,
				  `proxy_porta` VARCHAR(4) NULL COMMENT 'Porta do Servidor Proxy:\nEx.: 4403 ' ,
				  `proxy_usuario` VARCHAR(100) NULL COMMENT 'Nome do usuário do Proxy, caso o servidor exija um proxy para conexão com a Internet.' ,
				  `proxy_senha` VARCHAR(100) NULL COMMENT 'Senha do usuário Proxy para conexão com Internet.' ,
				  `diretorio_integracao` TEXT NOT NULL COMMENT 'Caminho do diretório onde fará a integração.' ,
				  `danfe_layout_caminho` TEXT NOT NULL COMMENT 'Caminho do Layout da Danfe a ser impresso.' ,
				  `danfe_layout_fs_da` TEXT NULL ,
				  `danfe_logo_caminho` TEXT NULL COMMENT 'Caminho da Logo da Danfe.' ,
				  `danfe_qtde_vias` INT NULL COMMENT 'Quantidade de vias que serão impressas da DANFE quando submitida a impressão automática.' ,
				  `danfe_automatica` VARCHAR(1) NULL COMMENT 'Seleciona se a impressão da DANFE é automática.\n0 - Não.\n1 - Sim.' ,
				  `server_impressao` TEXT NULL COMMENT 'Caminho do Servidor de impressão onde será impressa a DANFE automáticamente.\nEx.: \\\\192.168.1.24\\HPLaserJet' ,
				  PRIMARY KEY (`cnpj`, `ambiente`) )
				ENGINE = InnoDB;";
		if ($this->ponteiro->Execute($sql) === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> createTable() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
		return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}

	}*/
	
	/*
	 *	@function Função para Gravar os registro os atributos a tabela WEB SERVICE (caso não exista insere, caso exista atualiza)
	 *	@autor Guilherme Silva
	 */
	public function record(){
		  if(!$this->consistirAtributos()){
			return false;
		  }

		  $CBd = MBd::singleton($this->grupo);
		  if(!$CBd){ return false; }
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  
		  $sql = "INSERT INTO `nfe_".$this->grupo."`.`WEB_SERVICE` (
					`uf` ,
					`versao_xml` ,
					`servico` ,
					`ambiente` ,
					`metodo` ,
					`nome` ,
					`cnpj_web_service` ,
					`cod_uf_ibge` ,
					`metodo_conexao` ,
					`url_completa` ,
					`situacao` ,
					`xsd`
					)
					VALUES (";
		  $sql .= " '".strtoupper($this->uf)."',".
				  " '".$this->versao_xml."',".
				  " '".$this->servico."',".
				  " '".$this->ambiente."',".
				  " '".$this->metodo."',".
				  " '".$this->nome."',".
				  " '".$this->cnpj_web_service."',".
				  " '".$this->cod_uf_ibge."',".
				  " '".$this->metodo_conexao."',".
				  " '".$this->url_completa."',".
				  " '".$this->situacao."',".
				  " '')
				  ON DUPLICATE KEY UPDATE
				  `uf` 					= '".strtoupper($this->uf)."',
				  `versao_xml` 			= '".$this->versao_xml."',
				  `servico`				= '".$this->servico."',
				  `ambiente` 			= '".$this->ambiente."',
				  `metodo` 				= '".$this->metodo."',
				  `nome` 				= '".$this->nome."',
				  `cnpj_web_service`	= '".$this->cnpj_web_service."',
				  `cod_uf_ibge` 		= '".$this->cod_uf_ibge."',
				  `metodo_conexao` 		= '".$this->metodo_conexao."',
				  `url_completa` 		= '".$this->url_completa."',
				  `situacao` 			= '".$this->situacao."',
				  `xsd` 				= ''";

		  if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> inserir() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Função para Consultar dados da tabela contribuintes (necessario ter carregado CNPJ e Ambiente)
	 *	@autor Guilherme Silva
	 */
	public function mObterWebService(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		$sql = "SELECT * FROM `nfe_".$this->grupo."`.`WEB_SERVICE` ";
		
		$where = "";
		if($this->uf != ""){ 			$where[] = "uf = '".strtoupper($this->uf)."'"; 	}
		if($this->versao_xml != ""){	$where[] = "versao_xml = '".$this->versao_xml."'";}
		if($this->servico != ""){ 		$where[] = "servico = '".$this->servico."'";		}
		if($this->ambiente != ""){ 		$where[] = "ambiente = '".$this->ambiente."'";	}
		
		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> mObterWebService() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
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
	
	/*
	 *	@function Função para Remover o certificado digital do registro
	 *	@autor Guilherme Silva
	 */
	/*public function updateCert(){
		if(trim($this->cnpj) == "" || trim($this->ambiente) == ""){
			$this->mensagemErro = " MWebService -> updateCert() {para esta opcao parametros obrigatorios CNPJ e Ambiente} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "UPDATE `CONTRIBUINTE` SET `certificado_caminho` = '".$this->certificado_caminho."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> deleteCert() {nao foi possivel executar codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}

	
	public function updateContingencia(){
		if(trim($this->cnpj) == "" || trim($this->ambiente) == "" || trim($this->contigencia) == ""){
			$this->mensagemErro = " MWebService -> updateContingencia() {para esta opcao parametros obrigatorios CNPJ e Ambiente e Contingencia} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$sql = "UPDATE `CONTRIBUINTE` SET `contigencia` = '".$this->contigencia."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> updateContingencia() {nao foi possivel executar codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}*/
	
	/*
	 *	@function Função para Retornar todos os contribuintes cadastrados
	 *	@autor Guilherme Silva
	 */
	/*public function selectAll(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){ return false; }
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT * FROM `CONTRIBUINTE`;";
		
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MWebService -> selectAll() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			return $resultado;
		}
	}*/

	/*
	 *	@function Função para Consistir os atributos da base de dados antes de inserir
	 *  		  Esta função NÃO FAZ validação se o campo é vazio, apenas se foi definida corretamente os campos de domínio
	 *			  e também o tamanho mínimo para cada atributo.
	 *	@autor Guilherme Silva
	 */
	private function consistirAtributos(){
		if(strlen($this->uf) < 2){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> UF não foi informado corretamente ";
			return false;
		}
		if($this->versao_xml == ""){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Versao XML não foi informado corretamente ";
			return false;
		}
	    if($this->servico == ""){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Serviço não foi informado corretamente ";
			return false;
		}
		if($this->ambiente != "0" && $this->ambiente != "1"){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Tipo Ambiente não foi informado corretamente, valores aceitos 0-Homologacao 1-Produção ";
			return false;
		}
		if($this->cod_uf_ibge == ""){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Codigo UF IBGE não foi informado corretamente ";
			return false;
		}
		if($this->metodo_conexao != "1" && $this->metodo_conexao != "2" && $this->metodo_conexao != "3" && $this->metodo_conexao != "4"){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Metodo de Conexao não foi informado corretamente, valores aceitos 1-SOAP1 2-SOAP2 3-GET 4-POST ";
			return false;
		}
		if($this->url_completa == ""){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> URL Completa não foi informado corretamente ";
			return false;
		}
		if($this->situacao == ""){
			$this->mensagemErro = " MWebService -> consistirAtributos() -> Situação não foi informado corretamente ";
			return false;
		}
		return true;
	}
}
?>