
<?php
	/*
		Classe:					CEmpresa.php
		Autor:					Guilherme Silva
		Data:					17/02/2012
		Finalidade: 			Responsavel por manutencoes na tabela de Empresa
		Programas chamadores: 	
		Programas chamados: 	BD{empresa}
		Alterações:				21/02/2014 - Modificar cadastro incluir todos os dados do contribuinte antes situados no config.ini
	*/
	require_once("/var/www/html/nf/nfse/model/CLote.php");
	
	class CEmpresa{
		// Atributos referente aos campos da tabela
		public $empresa="";
		public $empresa_web="";
		public $filial="";
		public $filial_web="";
		public $cnpj="";
		public $razao_social="";
		public $email_smtp="";
		public $email_porta="";
		public $email_usuario="";
		public $email_senha = "";
		public $email_conexao = "";
		public $codigo_tom_cidade = "";
		public $certificado_pfx = "";
		public $senha_pfx = "";
		public $validade_certificado = "";
		public $usuario_prefeitura = "";
		public $senha_prefeitura = "";
		public $proxy = "";
		public $proxy_servidor = "";
		public $proxy_porta = "";
		public $proxy_usuario = "";
		public $proxy_senha = "";
		public $flag_producao = "";

		// Atributos para tratamento diversos
		public $grupo;
		private $ponteiro;
		private $resultado;
		public $mensagemErro;
		//Metodos

		/* Metodos publicos chamados por programas externos*/
		// Construtor inserido par gerar setar o grupo que instancia a classe
		function __construct($pGrupo="") {
       		$this->grupo = $pGrupo;
	   	}

		//Inserir Nota Fiscal, parametros Array do XML
		public function inserir(){
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
		  $sql = "INSERT INTO `nfse_".$this->grupo."`.`empresa`
							(`empresa`,
							`filial`,
							`empresa_web`,
							`filial_web`,
							`cnpj`,
							`razao_social`,
							`email_smtp`,
							`email_porta`,
							`email_usuario`,
							`email_senha`,
							`email_conexao`,
							`codigo_tom_cidade`,
							`certificado_pfx`,
							`senha_pfx`,
							`validade_certificado`,
							`usuario_prefeitura`,
							`senha_prefeitura`,
							`proxy`,
							`proxy_servidor`,
							`proxy_porta`,
							`proxy_usuario`,
							`proxy_senha`,
							`flag_producao`) 
					VALUES ('".$this->empresa."',
					        '".$this->filial."',
					        '".$this->empresa_web."',
					        '".$this->filial_web."',
					        '".$this->cnpj."',
					        '".$this->razao_social."',
					        '".$this->email_smtp."',
					        '".$this->email_porta."',
					        '".$this->email_usuario."',
					        '".$this->email_senha."',
					        '".$this->email_conexao."',
					        '".$this->codigo_tom_cidade."',
					        '".$this->certificado_pfx."',
					        '".$this->senha_pfx."',
					        '".$this->validade_certificado."',
					        '".$this->usuario_prefeitura."',
					        '".$this->senha_prefeitura."',
					        '".$this->proxy."',
					        '".$this->proxy_servidor."',
					        '".$this->proxy_porta."',
					        '".$this->proxy_usuario."',
					        '".$this->proxy_senha."',
							'".$this->flag_producao."')
					ON DUPLICATE KEY UPDATE
						empresa					= '".$this->empresa."',
						filial					= '".$this->filial."',
						empresa_web				= '".$this->empresa_web."',
						filial_web				= '".$this->filial_web."',
						cnpj					= '".$this->cnpj."',
						razao_social			= '".$this->razao_social."',
						email_smtp				= '".$this->email_smtp."',
						email_porta				= '".$this->email_porta."',
						email_usuario			= '".$this->email_usuario."',
						email_senha				= '".$this->email_senha."',
						email_conexao			= '".$this->email_conexao."',
						codigo_tom_cidade		= '".$this->codigo_tom_cidade."',
						certificado_pfx			= '".$this->certificado_pfx."',
						senha_pfx				= '".$this->senha_pfx."',
						validade_certificado	= '".$this->validade_certificado."',	
						usuario_prefeitura		= '".$this->usuario_prefeitura."',
						senha_prefeitura		= '".$this->senha_prefeitura."',
						proxy					= '".$this->proxy."',
						proxy_servidor			= '".$this->proxy_servidor."',
						proxy_porta				= '".$this->proxy_porta."',
						proxy_usuario			= '".$this->proxy_usuario."',
						proxy_senha				= '".$this->proxy_senha."',
						flag_producao			= '".$this->flag_producao."'";

		  if ($this->ponteiro->Execute($sql) === false) {
			$this->mensagemErro = " CEmpresa -> inserir() {nao foi possivel executar o codigo  ".$this->ponteiro->ErrorMsg()."} ";
			$this->ponteiro->RollbackTrans();
			return false;
		  }else{
			$this->ponteiro->CommitTrans();

			$CLote = new CLote($this->grupo);
			if(!$CLote->inserirLote($this->cnpj, "1","0")){
				$this->mensagemErro = $CLote->mensagemErro;
				return false;
			}
			return true;
		  }
		}
		
		public function obter(){
			$CBd = CBd::singleton($this->grupo);
			if(!$CBd){
				$this->mensagemErro = "Nao foi possivel estabelecer conexao com o banco de dados";
				return false;
			}
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();
			
			$sql = "SELECT *,
							`nfse_config`.`tom_cidade`.`codigo_ibge` as codigo_ibge_cidade,
							`nfse_config`.`tom_cidade`.`cidade` FROM `nfse_".$this->grupo."`.`empresa`
					LEFT JOIN `nfse_config`.`tom_cidade`
					ON `nfse_".$this->grupo."`.`empresa`.`codigo_tom_cidade` = `nfse_config`.`tom_cidade`.`codigo_tom`";

			$where = [];
			if($this->cnpj!= ""){ $where[] = "cnpj= '".$this->cnpj."'"; }
			if($this->empresa!= ""){ $where[] = "empresa= '".$this->empresa."'"; }
			if($this->filial!= ""){ $where[] = "filial= '".$this->filial."'"; }
			if($this->empresa_web!= ""){ $where[] = "empresa_web= '".$this->empresa_web."'"; }
			if($this->filial_web!= ""){ $where[] = "filial_web= '".$this->filial_web."'"; }
			
			if(count($where)){
				$where = implode(" AND ", $where);
				$sql .= "WHERE ".$where;
			}

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro =  " CEmpresa -> obter() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}
			while (!$recordSet->EOF) {
				$this->resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
  			$this->ponteiro->CommitTrans();
			return $this->resultado;
		}

		public function obterEmpresa(){
			$CBd = CBd::singleton($this->grupo);
			$this->ponteiro = $CBd->getPonteiro();
			$this->ponteiro->BeginTrans();

			$sql = "SELECT * FROM nfse_".$this->grupo.".empresa WHERE ".
					"cnpj='".$this->cnpj."'";

			$this->ponteiro->SetFetchMode(ADODB_FETCH_ASSOC);
			$recordSet = $this->ponteiro->Execute($sql);
			if (!$recordSet) {
				$this->mensagemErro =  " CEmpresa -> obter() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
				$this->ponteiro->RollbackTrans();
				return false;
			}
			while (!$recordSet->EOF) {
				$this->resultado[] = $recordSet->fields;
				$recordSet->MoveNext();
			}
  			$this->ponteiro->CommitTrans();
			return $this->resultado;
		}
		
		public function excluir(){
		  $CBd = CBd::singleton($this->grupo);
		  $this->ponteiro = $CBd->getPonteiro();
		  $this->ponteiro->BeginTrans();
  
		  $sql = "DELETE FROM empresa WHERE cnpj=".$this->cnpj;
  
		  if ($this->ponteiro->Execute($sql) === false) {
			$this->mensagemErro = " CEmpresa -> excluir() {nao foi possivel executar o codigo ".$this->ponteiro->ErrorMsg()."} ";
			$this->ponteiro->RollbackTrans();
			return false;
		  }
		  $this->ponteiro->CommitTrans();

		  $CLote = new CLote($this->grupo);
		  if(!$CLote->excluirLote($this->cnpj)){
			  $this->mensagemErro = $CLote->mensagemErro;
			  return false;
		  }



		  return true;
		}
	}
?>