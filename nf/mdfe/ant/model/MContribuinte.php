<?php
/**
 * @name      	MContribuinte
 * @version   	alfa
 * @copyright	2014 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para comunicar com Tabela CONTRIBUINTE do Banco de Dados
 * @TODO 		
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
	public $damdfe_layout_caminho = "";
	public $damdfe_logo_caminho = "";
	public $damdfe_qtde_vias = "";
	public $damdfe_automatica = "";
	public $server_impressao = "";
	public $ativo = "";

	/*
     *	 Atributos locais para comunicaзгo com banco de dados
     */
	private $ponteiro 		= "";
	public $mensagemErro 	= "";
	
	
	// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo=""){
    	$this->grupo = $pGrupo;
	}
	
	/*
	 *	@function Funзгo para Criar a tabela Contribuinte
	 *	@autor Guilherme Silva
	 */
	public function createTable(){
		$CBd = CBd::singleton($this->grupo);
		if(!$CBd){ return false; }
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "CREATE  TABLE IF NOT EXISTS `mdfe_".$this->grupo."`.`CONTRIBUINTE` (
				  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ do Contribuinte, Emissor da MDFE.',
				  `ambiente` varchar(1) NOT NULL COMMENT 'Tipo de Ambiente: 1-Produзгo; 2-Homologaзгo; 3-Desativado para emissгo.',
				  `uf` varchar(2) NOT NULL COMMENT 'Unidade da Federaзгo',
				  `cod_emp_fil_softdib` int(6) DEFAULT NULL COMMENT 'Cуdigo da Empresa e Filial que consta no Softdib.',
				  `razao_social` varchar(100) NOT NULL COMMENT 'Razгo Social da Empresa.',
				  `certificado_tipo` varchar(2) NOT NULL COMMENT 'A1; A3; Ainda nгo foi encontrada nenhum programa que autentique com sucesso no certificado A3, apenas A1.',
				  `certificado_caminho` text NOT NULL COMMENT 'Caminho do Certificado Digital (PFX). ( ./nfe/nfse/certificados)',
				  `certificado_senha` varchar(50) NOT NULL COMMENT 'Senha do certificado digital utilizado para emissгo da NF.',
				  `contigencia` varchar(2) NOT NULL COMMENT '01 - Normal (padrгo) sem contigкncia; 02 - Contingкncia.',
				  `data_hora_contingencia` datetime DEFAULT NULL COMMENT 'Data e Hora da entrada em Contingкncia AAAA:MM:DDTHH:MM:SS',
				  `justificativa_contingencia` varchar(255) DEFAULT NULL COMMENT 'Justificativa de entrar em contingкncia',
				  `pacote_xsd` varchar(10) NOT NULL COMMENT 'Nome do Pacote XSD (pasta).',
				  `email_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome de usuбrio do emitente da MDFE.',
				  `email_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do Email do emitente da MDFe.',
				  `email_remetente` varchar(100) DEFAULT NULL,
				  `email_smtp` varchar(100) DEFAULT NULL COMMENT 'Caminho do Servidor SMTP do email do emitente da MDFe.',
				  `email_porta` varchar(4) DEFAULT NULL COMMENT 'Cуdigo da Porta do Servidor de email do emitente da MDFe.',
				  `email_ssl` varchar(1) DEFAULT NULL COMMENT 'Flag se a conexгo й SSL ou nгo. 0-Nгo; 1-Sim.',
				  `email_conf_recebimento` varchar(1) DEFAULT NULL COMMENT 'Flag se habilita a confirmaзгo de recebimento do Proxy.',
				  `proxy_servidor` varchar(100) DEFAULT NULL COMMENT 'Servidor Proxy para conexгo com Internet.',
				  `proxy_porta` varchar(4) DEFAULT NULL COMMENT 'Porta do Servidor Proxy: Ex.: 4403 ',
				  `proxy_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome do usuбrio do Proxy, caso o servidor exija um proxy para conexгo com a Internet.',
				  `proxy_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do usuбrio Proxy para conexгo com Internet.',
				  `diretorio_integracao` text COMMENT 'Caminho do diretуrio onde farб a integraзгo.',
				  `diretorio_backup` text NOT NULL COMMENT 'Diretorio backup das MDFe',
				  `diretorio_importacao` text NOT NULL COMMENT 'Diretorio de integracao com terceiros',
				  `diretorio_base` text NOT NULL COMMENT 'Diretorio base no servidor softdib. Ex.: /user/nomecliente',
				  `damdfe_layout_caminho` text COMMENT 'Caminho do Layout da DAMDFe a ser impresso.',
				  `damdfe_logo_caminho` text COMMENT 'Caminho da Logo da DAMDFe.',
				  `damdfe_qtde_vias` int(11) DEFAULT NULL COMMENT 'Quantidade de vias que serгo impressas da DAMDFe quando submitida a impressгo automбtica.',
				  `damdfe_automatica` varchar(1) DEFAULT NULL COMMENT 'Seleciona se a impressгo da DAMDFe й automбtica. 0 - Nгo. 1 - Sim.',
				  `server_impressao` text COMMENT 'Caminho do Servidor de impressгo onde serб impressa a DAMDFe automбticamente. Ex.: \\\\192.168.1.24\\HPLaserJet',
				  PRIMARY KEY (`cnpj`,`ambiente`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		if ($this->ponteiro->Execute($sql) === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> createTable() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
		return false;
		}else{
			$this->ponteiro->CommitTrans();
			return true;
		}
	}

	/*
	 *	@function Funзгo para Gravar os registro os atributos a tabela CONTRIBUINTE (caso nгo exista insere, caso exista atualiza)
	 *	@autor Guilherme Silva
	 */
	public function record(){
		  $CBd = MBd::singleton($this->grupo);
		  if(!$CBd){ return false; }
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  $sql = "INSERT INTO `mdfe_".$this->grupo."`.`CONTRIBUINTE` (
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
					`damdfe_layout_caminho` ,
					`damdfe_logo_caminho` ,
					`damdfe_qtde_vias` ,
					`damdfe_automatica` ,
					`server_impressao`
					)
					VALUES (";
		$sql .=   " '".$this->cnpj."',".
				  " '".$this->ambiente."',".
				  " '".$this->uf."',".
				  " '".$this->cod_emp_fil_softdib."',".
				  " '".$this->razao_social."',".
				  " '".$this->certificado_tipo."',".
				  " '".$this->certificado_caminho."',".
				  " '".$this->certificado_senha."',".
				  " '01',".
				  " '".$this->data_hora_contingencia."',".
				  " '".$this->justificativa_contingencia."',".
				  " '".$this->pacote_xsd."',".
				  " '".$this->email_usuario."',".
				  " '".$this->email_senha."',".
				  " '".$this->email_remetente."',".
				  " '".$this->email_smtp."',".
				  " '".$this->email_porta."',".
				  " '".$this->email_ssl."',".
				  " '".$this->email_conf_recebimento."',".
				  " '".$this->proxy_servidor."',".
				  " '".$this->proxy_porta."',".
				  " '".$this->proxy_usuario."',".
				  " '".$this->proxy_senha."',".
				  " '".$this->diretorio_integracao."',".
				  " '".$this->diretorio_backup."',".
				  " '".$this->diretorio_importacao."',".
				  " '".$this->diretorio_base."',".
				  " '".$this->damdfe_layout_caminho."',".
				  " '".$this->damdfe_logo_caminho."',".
				  " '".$this->damdfe_qtde_vias."',".
				  " '".$this->damdfe_automatica."',".
				  " '".$this->server_impressao."')
				  ON DUPLICATE KEY UPDATE
				  `cnpj` = '".$this->cnpj."',
				  `ambiente` 					= '".$this->ambiente."',
				  `uf`							= '".$this->uf."',
				  `cod_emp_fil_softdib` 		= '".$this->cod_emp_fil_softdib."',
				  `razao_social` 				= '".$this->razao_social."',
				  `certificado_tipo` 			= '".$this->certificado_tipo."',
				  `certificado_caminho` 		= '".$this->certificado_caminho."',
				  `certificado_senha` 			= '".$this->certificado_senha."',
				  `contigencia` 				= '".$this->contigencia."',
				  `data_hora_contingencia` 		= NOW(),
				  `justificativa_contingencia` 	= '".$this->justificativa_contingencia."',
				  `pacote_xsd` 					= '".$this->pacote_xsd."',
				  `email_usuario` 				= '".$this->email_usuario."',
				  `email_senha` 				= '".$this->email_senha."',
				  `email_remetente` 			= '".$this->email_remetente."',
				  `email_smtp` 					= '".$this->email_smtp."',
				  `email_porta`	 				= '".$this->email_porta."',
				  `email_ssl` 					= '".$this->email_ssl."',
				  `email_conf_recebimento` 		= '".$this->email_conf_recebimento."',
				  `proxy_servidor` 				= '".$this->proxy_servidor."',
				  `proxy_porta` 				= '".$this->proxy_porta."',
				  `proxy_usuario` 				= '".$this->proxy_usuario."',
				  `proxy_senha` 				= '".$this->proxy_senha."',
				  `diretorio_integracao` 		= '".$this->diretorio_integracao."',
				  `diretorio_backup` 			= '".$this->diretorio_backup."',
				  `diretorio_importacao` 		= '".$this->diretorio_importacao."',
				  `diretorio_base` 				= '".$this->diretorio_base."',
				  `damdfe_layout_caminho` 		= '".$this->damdfe_layout_caminho."',
				  `damdfe_logo_caminho` 		= '".$this->damdfe_logo_caminho."',
				  `damdfe_qtde_vias` 			= '".$this->damdfe_qtde_vias."',
				  `damdfe_automatica` 			= '".$this->damdfe_automatica."',
				  `server_impressao` 			= '".$this->server_impressao."'";

		  if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> inserir() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funзгo para Gravar os registro os atributos a tabela CONTRIBUINTE (caso nгo exista insere, caso exista atualiza)
	 *	@autor Guilherme Silva
	 */
	public function update(){
		  $CBd = MBd::singleton($this->grupo);
		  if(!$CBd){ return false; }
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  $sql = "UPDATE `mdfe_".$this->grupo."`.`CONTRIBUINTE` SET
				  `ambiente` 					= '".$this->ambiente."',
				  `uf`							= '".$this->uf."',
				  `cod_emp_fil_softdib` 		= '".$this->cod_emp_fil_softdib."',
				  `razao_social` 				= '".$this->razao_social."',
				  `certificado_tipo` 			= '".$this->certificado_tipo."',
				  `certificado_caminho` 		= '".$this->certificado_caminho."',
				  `certificado_senha` 			= '".$this->certificado_senha."',
				  `contigencia` 				= '".$this->contigencia."',
				  `data_hora_contingencia` 		= NOW(),
				  `justificativa_contingencia` 	= '".$this->justificativa_contingencia."',
				  `pacote_xsd` 					= '".$this->pacote_xsd."',
				  `email_usuario` 				= '".$this->email_usuario."',
				  `email_senha` 				= '".$this->email_senha."',
				  `email_remetente` 			= '".$this->email_remetente."',
				  `email_smtp` 					= '".$this->email_smtp."',
				  `email_porta`	 				= '".$this->email_porta."',
				  `email_ssl` 					= '".$this->email_ssl."',
				  `email_conf_recebimento` 		= '".$this->email_conf_recebimento."',
				  `proxy_servidor` 				= '".$this->proxy_servidor."',
				  `proxy_porta` 				= '".$this->proxy_porta."',
				  `proxy_usuario` 				= '".$this->proxy_usuario."',
				  `proxy_senha` 				= '".$this->proxy_senha."',
				  `diretorio_integracao` 		= '".$this->diretorio_integracao."',
				  `diretorio_backup` 			= '".$this->diretorio_backup."',
				  `diretorio_importacao` 		= '".$this->diretorio_importacao."',
				  `diretorio_base` 				= '".$this->diretorio_base."',
				  `damdfe_layout_caminho` 		= '".$this->damdfe_layout_caminho."',
				  `damdfe_logo_caminho` 		= '".$this->damdfe_logo_caminho."',
				  `damdfe_qtde_vias` 			= '".$this->damdfe_qtde_vias."',
				  `damdfe_automatica` 			= '".$this->damdfe_automatica."',
				  `server_impressao` 			= '".$this->server_impressao."'
				  WHERE `cnpj` = '".$this->cnpj."'";

		  if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> update() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funзгo para Consultar dados da tabela contribuintes (necessario ter carregado CNPJ e Ambiente)
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

		$sql = "SELECT * FROM `mdfe_".$this->grupo."`.`CONTRIBUINTE`
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";
		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);
		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
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
	 *	@function Funзгo para Remover o certificado digital do registro
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

		$sql = "UPDATE `mdfe_".$this->grupo."`.`CONTRIBUINTE` SET `certificado_caminho` = '".$this->certificado_caminho."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
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
		
		$sql = "UPDATE `mdfe_".$this->grupo."`.`CONTRIBUINTE` SET 
				`contigencia` = '".$this->contigencia."',
				`data_hora_contingencia` = NOW(),
				`justificativa_contingencia` = '".$this->justificativa_contingencia."'
				WHERE `cnpj` = '".$this->cnpj."'
				AND `ambiente` = '".$this->ambiente."';";

		if ($this->ponteiro->Execute($sql) === false) {
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> updateContingencia() {nao foi possivel executar codigo ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		  }else{
			$this->ponteiro->CommitTrans();
			return true;
		  }
	}
	
	/*
	 *	@function Funзгo para Retornar todos os contribuintes cadastrados
	 *	@autor Guilherme Silva
	 */
	public function selectAll($pSql=""){
		$CBd = MBd::singleton($this->grupo);
		
		if(!$CBd){ return false; }
		
		$this->ponteiro = $CBd->getPonteiro();
		$this->ponteiro->BeginTrans();

		$sql = "SELECT * FROM `mdfe_".$this->grupo."`.`CONTRIBUINTE` ";
		
		$where = "";
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

		if($this->ativo== "S"){ $where[] = "ambiente != '03'"; } // Diferente de 03-Desativado
		if($this->ativo== "N"){ $where[] = "ambiente = '03'"; } // Igual 03-Desativado
		
		if($where != ""){
			$where = implode(" AND ", $where);
			$sql .= "WHERE ".$where;
		}
		
		if($pSql != ""){
			$sql = $pSql;
		}

		$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
		$recordSet = $this->ponteiro->Execute($sql);

		if ($recordSet === false){
			$this->ponteiro->RollbackTrans();
			$this->mensagemErro = " MContribuinte -> selectAll() {nao foi possivel executar codigo: ".$this->ponteiro->ErrorMsg()."} ";
			return false;
		}else{
			$resultado = "";
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