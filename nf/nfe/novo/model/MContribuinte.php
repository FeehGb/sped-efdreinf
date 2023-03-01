<?php
/**
 * @name      	MContribuinte
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela CONTRIBUINTE do Banco de Dados
 * @TODO 		Testear Classe
*/


/*
ALTER TABLE `nfe_berlanda`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_bertani`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_bhs`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_cavazzani`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_coldair`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_disomet`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_doneda`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_expalum`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';


ALTER TABLE `nfe_fitaspack`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_fitax`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_flex`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_iporanga`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_kilmer`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_latinex`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_leogap`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_lyke`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_moldespuma`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_multilit`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_natuphitus`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_pase`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_pneuplus`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_polyfit`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_remocarga`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_roadcar`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_songhe`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_summus`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_tratoraco`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

ALTER TABLE `nfe_vianmaq`.`CONTRIBUINTE` CHANGE `consulta_destinatario` `consulta_destinatario` VARCHAR(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas', CHANGE `consulta_destinatario_hora` `consulta_destinatario_hora` DATETIME NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ';

  
  
 
*/


















	require_once("MBd.php");
/**
 * Classe MContribuinte
 */

 class MContribuinte{
 
	private $grupo;
 
	/*
     *	 Atributos (campos) da tabela Contribuintes
     */
	public $cnpj = "";
	public $ambiente = "";
	public $uf = "";
	public $cod_emp_fil_softdib = "";
	public $razao_social = "";
	public $certificado_tipo = "";
	public $certificado_caminho = "";
	public $certificado_senha = "";
	public $contigencia = "";
	public $data_hora_contingencia = "";
	public $justificativa_contingencia = "";
	public $pacote_xsd = "";
	public $email_usuario = "";
	public $email_senha = "";
	public $email_remetente = "";
	public $email_smtp = "";
	public $email_porta = "";
	public $email_ssl = "";
	public $email_conf_recebimento = "";
	public $proxy_servidor = "";
	public $proxy_porta = "";
	public $proxy_usuario = "";
	public $proxy_senha = "";
	public $diretorio_integracao = "";
	public $diretorio_backup = "";
	public $diretorio_importacao = "";
	public $diretorio_base = "";
	public $danfe_layout_caminho = "";
	public $danfe_layout_fs_da = "";
	public $danfe_logo_caminho = "";
	public $danfe_qtde_vias = "";
	public $danfe_automatica = "";
	public $server_impressao = "";
	public $server_impressao_comando = "";
// quando for voltar tem que executar nos clientes: ALTER TABLE `CONTRIBUINTE` ADD `server_impressao_comando` TEXT NOT NULL COMMENT 'Quando necessitar colocar um comando no servico de impressao' AFTER `server_impressao` 
	public $ativo = "";

	/*
     *	 Atributos locais para comunica��o com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	
	
	// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo=""){
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Fun��o para Criar a tabela Contribuinte
	 *	@autor Guilherme Silva
	 */
	public function createTable(){
		$CBd = CBd::singleton($this->grupo);
		if(!$CBd){ return false; }
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "CREATE  TABLE IF NOT EXISTS `nfe_".$this->grupo."`.`CONTRIBUINTE` (
				  `cnpj` VARCHAR(14) NOT NULL COMMENT 'CNPJ do Contribuinte, Emissor da Nota Fiscal.' ,
				  `ambiente` VARCHAR(1) NOT NULL COMMENT 'Tipo de Ambiente:\n0-Homologa��o.\n1-Produ��o.' ,
				  `uf` VARCHAR(2) NOT NULL ,
				  `cod_emp_fil_softdib` INT(6) NULL COMMENT 'C�digo da Empresa e Filial que consta no Softdib.' ,
				  `razao_social` VARCHAR(100) NOT NULL COMMENT 'Raz�o Social da Empresa.' ,
				  `certificado_tipo` VARCHAR(2) NOT NULL COMMENT 'A1;\nA3;\nAinda n�o foi encontrada nenhum programa que autentique com sucesso no certificado A3, apenas A1.' ,
				  `certificado_caminho` TEXT NOT NULL COMMENT 'Caminho do Certificado Digital (PFX).\n( ./nfe/certs)\n' ,
				  `certificado_senha` VARCHAR(50) NOT NULL COMMENT 'Senha do certificado digital utilizado para emiss�o da NF.' ,
				  `contigencia` VARCHAR(2) NOT NULL COMMENT '01-Normal sem contig�ncia\n02-DPEC\n03-SCAN' ,
				  `pacote_xsd` VARCHAR(10) NOT NULL COMMENT 'Nome do Pacote XSD\n�ltima Vers�o: 6r.\nPL_006S' ,
				  `email_usuario` VARCHAR(100) NULL COMMENT 'Nome de usu�rio do emitente da Nota Fiscal.' ,
				  `email_senha` VARCHAR(100) NULL COMMENT 'Senha do Email do emitente da NF.' ,
				  `email_remetente` VARCHAR(100) NULL ,
				  `email_smtp` VARCHAR(100) NULL COMMENT 'Caminho do Servidor SMTP do email do emitente da NF.' ,
				  `email_porta` VARCHAR(4) NULL COMMENT 'C�digo da Porta do Servidor de email do emitente da NF.' ,
				  `email_ssl` VARCHAR(1) NULL COMMENT 'Flag se a conex�o � SSL ou n�o.\n0-N�o;\n1-Sim.' ,
				  `email_conf_recebimento` VARCHAR(1) NULL COMMENT 'Flag se habilita a confirma��o de recebimento do Proxy.' ,
				  `proxy_servidor` VARCHAR(100) NULL COMMENT 'Servidor Proxy para conex�o com Internet.' ,
				  `proxy_porta` VARCHAR(4) NULL COMMENT 'Porta do Servidor Proxy:\nEx.: 4403 ' ,
				  `proxy_usuario` VARCHAR(100) NULL COMMENT 'Nome do usu�rio do Proxy, caso o servidor exija um proxy para conex�o com a Internet.' ,
				  `proxy_senha` VARCHAR(100) NULL COMMENT 'Senha do usu�rio Proxy para conex�o com Internet.' ,
				  `diretorio_integracao` TEXT NOT NULL COMMENT 'Caminho do diret�rio onde far� a integra��o.' ,
				  `diretorio_backup` text NOT NULL COMMENT 'Diret�rio backup onde ser�o salvas as notas fiscais autorizadas, canceladas, inutilizadas, etc.',
				  `diretorio_importacao` text NOT NULL COMMENT 'Diret�rio onde ser�o salvas as notas de terceiros que ir�o que poder�o ser importadas para o sistema.',
				  `diretorio_base` text NOT NULL COMMENT 'Diretorio base do cliente para rodar o SVE350 (/user/cliente).',
				  `danfe_layout_caminho` TEXT NOT NULL COMMENT 'Caminho do Layout da Danfe a ser impresso.' ,
				  `danfe_layout_fs_da` TEXT NULL ,
				  `danfe_logo_caminho` TEXT NULL COMMENT 'Caminho da Logo da Danfe.' ,
				  `danfe_qtde_vias` INT NULL COMMENT 'Quantidade de vias que ser�o impressas da DANFE quando submitida a impress�o autom�tica.' ,
				  `danfe_automatica` VARCHAR(1) NULL COMMENT 'Seleciona se a impress�o da DANFE � autom�tica.\n0 - N�o.\n1 - Sim.' ,
				  `server_impressao` TEXT NULL COMMENT 'Caminho do Servidor de impress�o onde ser� impressa a DANFE autom�ticamente.\nEx.: \\\\192.168.1.24\\HPLaserJet' ,
				  `server_impressao_comando` TEXT NOT NULL COMMENT 'Quando necessitar colocar um comando no servico de impressao',
				  PRIMARY KEY (`cnpj`, `ambiente`) )
				ENGINE = InnoDB;";
		if ($this->ponteiro->Execute($sql) === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> createTable() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
		return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}
	
	/*
	 *	@function Fun��o para Gravar os registro os atributos a tabela CONTRIBUINTE (caso n�o exista insere, caso exista atualiza)
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
		  
		  $sql = "INSERT INTO `nfe_".$this->grupo."`.`CONTRIBUINTE` (
					`cnpj` ,
					`ambiente` ,
					`uf` ,
					`cod_emp_fil_softdib` ,
					`razao_social` ,
					`certificado_tipo` ,
					`certificado_caminho` ,
					`certificado_senha` ,
					`contigencia` ,
					`data_hora_contingencia` ,
					`justificativa_contingencia` ,
					`pacote_xsd` ,
					`email_usuario` ,
					`email_senha` ,
					`email_remetente` ,
					`email_smtp` ,
					`email_porta` ,
					`email_ssl` ,
					`email_conf_recebimento` ,
					`proxy_servidor` ,
					`proxy_porta` ,
					`proxy_usuario` ,
					`proxy_senha` ,
					`diretorio_integracao` ,
					`diretorio_backup` ,
					`diretorio_importacao` ,
					`diretorio_base` ,
					`danfe_layout_caminho` ,
					`danfe_layout_fs_da` ,
					`danfe_logo_caminho` ,
					`danfe_qtde_vias` ,
					`danfe_automatica` ,
					`server_impressao`,
					`server_impressao_comando`,
					`ativo`
					)
					VALUES (";
		  $sql .= " '".$this->cnpj."',".
				  " '".$this->ambiente."',".
				  " '".$this->uf."',".
				  " '".$this->cod_emp_fil_softdib."',".
				  " '".addslashes($this->razao_social)."',".
				  " '".$this->certificado_tipo."',".
				  " '".addslashes($this->certificado_caminho)."',".
				  " '".$this->certificado_senha."',".
				  " '01',". //Atualizado com 01 pois neste momento de cadastro inicial assume-se NORMAL
				  " NOW(),".
				  " '".addslashes($this->justificativa_contingencia)."',".
				  " '".addslashes($this->pacote_xsd)."',".
				  " '".addslashes($this->email_usuario)."',".
				  " '".addslashes($this->email_senha)."',".
				  " '".addslashes($this->email_remetente)."',".
				  " '".addslashes($this->email_smtp)."',".
				  " '".$this->email_porta."',".
				  " '".$this->email_ssl."',".
				  " '".$this->email_conf_recebimento."',".
				  " '".addslashes($this->proxy_servidor)."',".
				  " '".$this->proxy_porta."',".
				  " '".addslashes($this->proxy_usuario)."',".
				  " '".addslashes($this->proxy_senha)."',".
				  " '".addslashes($this->diretorio_integracao)."',".
				  " '".addslashes($this->diretorio_backup)."',".
				  " '".addslashes($this->diretorio_importacao)."',".
				  " '".addslashes($this->diretorio_base)."',".
				  " '".addslashes($this->danfe_layout_caminho)."',".
				  " '".addslashes($this->danfe_layout_fs_da)."',".
				  " '".addslashes($this->danfe_logo_caminho)."',".
				  " '".$this->danfe_qtde_vias."',".
				  " '".$this->danfe_automatica."',".
				  " '".addslashes($this->server_impressao)."',".
				  " '".$this->server_impressao_comando."',".
				  " '".$this->ativo."')
				  ON DUPLICATE KEY UPDATE
				  `cnpj` = '".$this->cnpj."',
				  `ambiente` 					= '".$this->ambiente."',
				  `uf`							= '".$this->uf."',
				  `cod_emp_fil_softdib` 		= '".$this->cod_emp_fil_softdib."',
				  `razao_social` 				= '".addslashes($this->razao_social)."',
				  `certificado_tipo` 			= '".$this->certificado_tipo."',
				  `certificado_caminho` 		= '".addslashes($this->certificado_caminho)."',
				  `certificado_senha` 			= '".addslashes($this->certificado_senha)."',
				  `contigencia` 				= '".$this->contigencia."',
				  `data_hora_contingencia` 		= NOW(),
				  `justificativa_contingencia` 	= '".addslashes($this->justificativa_contingencia)."',
				  `pacote_xsd` 					= '".addslashes($this->pacote_xsd)."',
				  `email_usuario` 				= '".addslashes($this->email_usuario)."',
				  `email_senha` 				= '".addslashes($this->email_senha)."',
				  `email_remetente` 			= '".addslashes($this->email_remetente)."',
				  `email_smtp` 					= '".addslashes($this->email_smtp)."',
				  `email_porta`	 				= '".addslashes($this->email_porta)."',
				  `email_ssl` 					= '".addslashes($this->email_ssl)."',
				  `email_conf_recebimento` 		= '".addslashes($this->email_conf_recebimento)."',
				  `proxy_servidor` 				= '".addslashes($this->proxy_servidor)."',
				  `proxy_porta` 				= '".addslashes($this->proxy_porta)."',
				  `proxy_usuario` 				= '".addslashes($this->proxy_usuario)."',
				  `proxy_senha` 				= '".addslashes($this->proxy_senha)."',
				  `diretorio_integracao` 		= '".addslashes($this->diretorio_integracao)."',
				  `diretorio_backup` 			= '".addslashes($this->diretorio_backup)."',
				  `diretorio_importacao` 		= '".addslashes($this->diretorio_importacao)."',
				  `diretorio_base` 				= '".addslashes($this->diretorio_base)."',
				  `danfe_layout_caminho` 		= '".addslashes($this->danfe_layout_caminho)."',
				  `danfe_layout_fs_da` 			= '".addslashes($this->danfe_layout_fs_da)."',
				  `danfe_logo_caminho` 			= '".addslashes($this->danfe_logo_caminho)."',
				  `danfe_qtde_vias` 			= '".$this->danfe_qtde_vias."',
				  `danfe_automatica` 			= '".$this->danfe_automatica."',
				  `server_impressao` 			= '".addslashes($this->server_impressao)."',
				  `server_impressao_comando` 	= '".$this->server_impressao_comando."',
				  `ativo` 						= '".$this->ativo."'";
		  if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> inserir() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg(). "} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Fun��o para Consultar dados da tabela contribuintes (necessario ter carregado CNPJ e Ambiente)
	 *	@autor Guilherme Silva
	 */
	public function selectCNPJAmbiente(){
		if(trim($this->cnpj) == "" || trim($this->ambiente) == ""){
			$this->mensagemErro = " MContribuinte -> selectCNPJAmbiente() {para esta opcao parametros obrigatorios: CNPJ e Ambiente} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT * FROM `nfe_".$this->grupo."`.`CONTRIBUINTE`
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> selectCNPJAmbiente() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
			if(isset($resultado)){
				return $resultado;
			}else{
				return true;
			}
		}
	}
	
	/*
	 *	@function Fun��o para Remover o certificado digital do registro
	 *	@autor Guilherme Silva
	 */
	public function updateCert(){
		if(trim($this->cnpj) == "" || trim($this->ambiente) == ""){
			$this->mensagemErro = " MContribuinte -> updateCert() {para esta opcao parametros obrigatorios CNPJ e Ambiente} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "UPDATE `nfe_".$this->grupo."`.`CONTRIBUINTE` SET `certificado_caminho` = '".$this->certificado_caminho."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> deleteCert() {nao foi possivel executar codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}

	public function updateContingencia(){
		if(trim($this->cnpj) == "" || trim($this->ambiente) == "" || trim($this->contigencia) == ""){
			$this->mensagemErro = " MContribuinte -> updateContingencia() {para esta opcao parametros obrigatorios CNPJ e Ambiente e Contingencia} ";
			return false;
		}
		
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){
			$this->mensagemErro = $CBd->mensagemErro;
			return false;
		}

		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();
		
		$sql = "UPDATE `nfe_".$this->grupo."`.`CONTRIBUINTE` SET 
				`contigencia` = '".$this->contigencia."',
				`data_hora_contingencia` = NOW(),
				`justificativa_contingencia` = '".addslashes($this->justificativa_contingencia)."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> updateContingencia() {nao foi possivel executar codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Fun��o para Retornar todos os contribuintes cadastrados
	 *	@autor Guilherme Silva
	 */
	public function selectAll(){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){ return false; }
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT * FROM `nfe_".$this->grupo."`.`CONTRIBUINTE` ";
		
		$where = [];
		if($this->cnpj!= ""){ $where[] = "cnpj= '".$this->cnpj."'"; }
		if($this->ambiente!= ""){ $where[] = "ambiente= '".$this->ambiente."'"; }
		if($this->uf!= ""){ $where[] = "uf= '".$this->uf."'"; }
		if($this->cod_emp_fil_softdib!= ""){ $where[] = "cod_emp_fil_softdib= '".$this->cod_emp_fil_softdib."'"; }
		if($this->razao_social!= ""){ $where[] = "razao_social LIKE '".$this->razao_social."'"; }
		if($this->certificado_tipo!= ""){ $where[] = "certificado_tipo = '".$this->certificado_tipo."'"; }
		if($this->certificado_caminho!= ""){ $where[] = "certificado_caminho LIKE '".$this->certificado_caminho."'"; }
		if($this->contigencia!= ""){ $where[] = "contigencia = '".$this->contigencia."'"; }		
		if($this->diretorio_integracao!= ""){ $where[] = "diretorio_integracao LIKE '".$this->diretorio_integracao."'"; }
		if($this->diretorio_backup!= ""){ $where[] = "diretorio_backup LIKE '".$this->diretorio_backup."'"; }
		if($this->diretorio_importacao!= ""){ $where[] = "diretorio_importacao LIKE '".$this->diretorio_importacao."'"; }
		if($this->diretorio_base!= ""){ $where[] = "diretorio_base LIKE '".$this->diretorio_base."'"; }
		if($this->ativo!= ""){ $where[] = "ativo LIKE '".$this->ativo."'"; }
		
		if(count($where)){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			//$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> selectAll() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$resultado = [];
			$this->ponteiro->CommitTrans();
			while (!$recordSet->EOF) {
				$resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}

			return $resultado;
		}
	}

	/*
	 *	@function Fun��o para Consistir os atributos da base de dados antes de inserir
	 *  		  Esta fun��o N�O FAZ valida��o se o campo � vazio, apenas se foi definida corretamente os campos de dom�nio
	 *			  e tamb�m o tamanho m�nimo para cada atributo.
	 *	@autor Guilherme Silva
	 */
	private function consistirAtributos(){
		if(strlen($this->cnpj) < 14){
			$this->mensagemErro = " MContribuinte -> consistirAtributos() -> CNPJ n�o foi informado corretamente ";
			return false;
		}
		if(($this->ambiente != "0" && $this->ambiente != "1")){
			$this->ambiente = "0";
		}
		if(($this->ativo != "S" && $this->ativo != "N")){
			$this->mensagemErro = " MContribuinte -> consistirAtributos() -> Deve ser informada situa��o do cadastro do contribuinte Ativa (S) ou Inativa (N) ";
			return false;
		}
		return true;
	}
}
?>