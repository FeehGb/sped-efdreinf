<?php
/**
 * @name      	CEmail.php
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Classe elaborada para enviar os e-mails da NFe
 * @TODO 		Fazer tudo
*/

    require_once("/var/www/html/nf/nfse/control/phpmailer/class.phpmailer.php");
	require_once("../control/CGerarDanfe.php");
	require_once("../model/MNotaFiscal.php");
	require_once("../model/MCritica.php");

/*
 * Classe CEmail
 */
	class CEmail{
	/*
	 * Atributos
	 */
	  public $smtp;
	  public $porta;
	  public $ssl;
	  public $usuario;
	  public $senha;
	  public $remetente;
	  public $nomeRemetente;
	  public $destinatario;
	  public $arrayConteudoResult;
	  public $attach;
	  public $attach2;
	  public $attach3;
	  public $attach4;
	  
	  public $mensagemErro;
	  
	  private $grupo;
	  
// Construtor inserido par gerar setar o grupo que instancia a classe
	function __construct($pGrupo="") {
    	$this->grupo = $pGrupo;
	}

	/*
	 *	@function Função para testarEnvio de Email
	 *	@autor Guilherme Silva
	 */
	  public function testarEnvio(){
		// Corpo da mensagem
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html -ns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-559" />
				<title>Nota Fiscal Eletronica</title>
				</head>

				<body style="font-family:Verdana, Geneva, sans-serif; font-size:13px;">
				<table width="100%" border="0" cellpadding="5">
				  <tr>
					<td><img src="http://www.softdib.com.br/imagens/logo1.png" height="100"></td>
					<td><img src="http://www.softdib.com.br/imagens/icone_nfe.jpg" height="100"></td>
				  </tr>
				</table>
				<div style="padding-left:40px;">
					<p style="color:#999; font-size:25px;">Prezado Usu&aacute;rio</p>
					<p>Voc&ecirc; solicitou, atrav&eacute;s do Cadastro de Contribuinte do Sistema da Nota Fiscal Eletr&ocirc;nica, o teste de envio de e-mail.</p>
					<p>Esta mensagem autom&aacute;tica significa que houve sucesso nas credenciais de e-mail.</p>
					<p>N&atilde;o h&aacute; necessidade de responder este e-mail.</p>
				</div>
				</body>
				</html>';

		try{
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
			// Verificar SSL
			if($this->ssl == "S"){
				$mail->SMTPSecure = ssl;
			}
			$mail->Port = $this->porta;
			$mail->Host = $this->smtp;
			$mail->Username = $this->usuario;
			$mail->Password = $this->senha;
			$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
			$mail->AddReplyTo = $this->usuario;
			$mail->From = $this->usuario; // Email de origem
			$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

			if(strpos($this->usuario,";")){
				$emails = explode(";",$this->usuario);
				foreach($emails as $conteudo){
					@$mail->AddAddress($conteudo);
				}
			}else{
				@$mail->AddAddress($this->usuario);
			}

			$mail->Subject = "Nota Fiscal - Configuracao Contribuinte"; // Assunto
			//$mail->WordWrap = 80; // Caracteres por linha
			
			$body = preg_replace('/\\\\/','', $body);
			$mail->MsgHTML($body); //corpo
			$mail->IsHTML(true);
			
			if(!$mail->Send()){
			   $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			   return false;
			}
		}catch (phpmailerException $e){
		  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
		  return false;
		}catch (Exception $e){
		  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
		  return false;
		}
		$this->mensagemErro = "E-mail configurado com sucesso, verifique sua caixa de e-mail";
		return true;
	  }
	  
	/*
	 *	@function Função para selecionar Notas com envio de e-mail pendente e submetê-las
	 *	@autor Guilherme Silva
	 */
	 public function mEmailsPendentes(){
		// Obter NFs de contribuintes ativos e que estejam AUTORIZADAS com SUCESSO e pendentes de envio
		$MContribuinte = new MContribuinte($this->grupo);
		$MNf = new MNotaFiscal($this->grupo);
		$MEvento = new MEvento($this->grupo);
		$MCritica = new MCritica($this->grupo);

		$pSql = "SELECT NF.* FROM nfe_".$this->grupo.".`NOTA_FISCAL` as NF, nfe_".$this->grupo.".`CONTRIBUINTE` as C WHERE C.ativo = 'S' AND NF.status = '03' AND (NF.email_enviado <> 'S' OR NF.email_enviado IS NULL)";
		$resultNF = $MNf->selectAllMestre($pSql);

		if(!$resultNF){
			$this->mensagemErro = $MNf->mensagemErro;
			return false;
		}

		if(is_array($resultNF)){
			foreach($resultNF as $key=>$value){
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultNF[$key]['cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultNF[$key]['ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultNF[$key]['serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultNF[$key]['numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultNF[$key]['email_destinatario'] == NULL || trim($resultNF[$key]['email_destinatario']) == ""){
					//GRAVAR CRITICA de email de destinatario não informado
					$MCritica->descricao = "Não foi possível enviar e-mail de Autorização da Nota Fiscal, email do destinatário não encontrado. ";
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultNF[$key]['cnpj_emitente'];
				$MContribuinte->ambiente = $resultNF[$key]['ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					return false;
				}

				$this->smtp = $returnContribuinte[0]['email_smtp'];
				$this->porta = $returnContribuinte[0]['email_porta'];
				$this->ssl = $returnContribuinte[0]['email_ssl'];
				$this->usuario = $returnContribuinte[0]['email_usuario'];
				$this->senha = $returnContribuinte[0]['email_senha'];
				$this->remetente = $returnContribuinte[0]['email_remetente'];
				$this->nomeRemetente = $returnContribuinte[0]['razao_social'];
				$this->destinatario = $resultNF[$key]['email_destinatario'];
				$this->arrayConteudoResult = $resultNF[$key];				

				file_put_contents("/var/www/html/nf/nfe/novo/temp/".$resultNF[$key]['chave'].".xml", base64_decode($resultNF[$key]['xml']));
				$this->attach = "/var/www/html/nf/nfe/novo/temp/".$resultNF[$key]['chave'].".xml";

				$CGerarDanfe = new CGerarDanfe($this->grupo);
				$CGerarDanfe->xmlNfe = $resultNF[$key]['xml'];
				$CGerarDanfe->infAdic = $resultNF[$key]['observacao'];

				if(!$CGerarDanfe->gerarDanfe()){
					$retornoJson['mensagem'] = $CGerarDanfe->mensagemErro;
				}else{
					$this->attach2 = "/var/www/html/relatorios/".$CGerarDanfe->danfePdf;
				}
				
				// Enviar email de nota Autorizada para Destinatario
				if(!$this->emailAutorizada()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				$MNf->cnpj_emitente = $resultNF[$key]['cnpj_emitente'];
				$MNf->numero_nota = $resultNF[$key]['numero_nota'];
				$MNf->serie_nota = $resultNF[$key]['serie_nota'];
				$MNf->ambiente = $resultNF[$key]['ambiente'];

				$MNf->email_enviado = "S";

				if(!$MNf->update()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
				}
			}
		}


		// Obter Evento de contribuintes ativos para notas fiscais CANCELADAS com SUCESSO pendentes de envio
		// foreach para envio do modelo de email CANCELADAS
		// caso de falha no processo gravar CRITICA
		$pSql = "SELECT E.*, NF.nome_destinatario, NF.valor_total_nfe, NF.chave, NF.email_destinatario
					FROM `CONTRIBUINTE` as C, `EVENTO` as E
						LEFT JOIN `NOTA_FISCAL` as NF
						ON 	E.NOTA_FISCAL_cnpj_emitente = NF.cnpj_emitente 	AND
							E.NOTA_FISCAL_numero_nota = NF.numero_nota 		AND
							E.NOTA_FISCAL_serie_nota = NF.serie_nota 		AND
							E.NOTA_FISCAL_ambiente = NF.ambiente
					WHERE C.ativo = 'S' AND E.tipo_evento = '4' AND E.status = '101' AND (E.email_enviado <> 'S' OR E.email_enviado IS NULL)";
		$resultEvento = $MEvento->selectMestre($pSql);
		if(!$resultEvento){
			$this->mensagemErro = $MEvento->mensagemErro;
			return false;
		}

		if(is_array($resultEvento)){
			foreach($resultEvento as $key=>$value){
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultEvento[$key]['email_destinatario'] == NULL || trim($resultEvento[$key]['email_destinatario']) == ""){
					// GRAVAR CRITICA de email de destinatario não informado
					$MCritica->descricao = "Não foi possível enviar e-mail de Cancelamento da Nota Fiscal, email do destinatário não encontrado. ";
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MContribuinte->ambiente = $resultEvento[$key]['NOTA_FISCAL_ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					return false;
				}

				$this->smtp = $returnContribuinte[0]['email_smtp'];
				$this->porta = $returnContribuinte[0]['email_porta'];
				$this->ssl = $returnContribuinte[0]['email_ssl'];
				$this->usuario = $returnContribuinte[0]['email_usuario'];
				$this->senha = $returnContribuinte[0]['email_senha'];
				$this->remetente = $returnContribuinte[0]['email_remetente'];
				$this->nomeRemetente = $returnContribuinte[0]['razao_social'];
				$this->destinatario = $resultEvento[$key]['email_destinatario'];
				
				$this->arrayConteudoResult = $resultNF[$key];
				
				// Enviar email de nota Autorizada para Destinatario
				if(!$this->emailCancelada()){
					// GRAVAR CRITICA de email de destinatario
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				// Atualiza o evento eviado do status
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MEvento->NOTA_FISCAL_numero_nota   = $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MEvento->NOTA_FISCAL_serie_nota    = $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MEvento->NOTA_FISCAL_ambiente      = $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MEvento->tipo_evento				= $resultEvento[$key]['tipo_evento'];
				$MEvento->numero_sequencia			= $resultEvento[$key]['numero_sequencia'];
				$MEvento->xml_env					= $resultEvento[$key]['xml_env'];
				$MEvento->xml_ret					= $resultEvento[$key]['xml_ret'];
				$MEvento->descricao					= $resultEvento[$key]['descricao'];
				$MEvento->protocolo					= $resultEvento[$key]['protocolo'];
				$MEvento->data_hora					= $resultEvento[$key]['data_hora'];
				$MEvento->status					= $resultEvento[$key]['status'];
				$MEvento->email_enviado				= "S";

				if(!$MEvento->insert()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
				}
			}
		}

		// Obter Evento de contribuintes ativos para notas fiscais com CARTA DE CORREÇÃO emitidas com SUCESSO
		// foreach para envio do modelo de email de CARTA DE CORREÇÃO
		// caso de falha no processo gravar CRITICA
		$pSql = "SELECT E.*, NF.nome_destinatario, NF.valor_total_nfe, NF.chave, NF.email_destinatario
					FROM `CONTRIBUINTE` as C, `EVENTO` as E
						LEFT JOIN `NOTA_FISCAL` as NF
						ON 	E.NOTA_FISCAL_cnpj_emitente = NF.cnpj_emitente 	AND
							E.NOTA_FISCAL_numero_nota = NF.numero_nota 		AND
							E.NOTA_FISCAL_serie_nota = NF.serie_nota 		AND
							E.NOTA_FISCAL_ambiente = NF.ambiente
					WHERE C.ativo = 'S' AND E.tipo_evento = '5' AND E.status = '102' AND (E.email_enviado <> 'S' OR E.email_enviado IS NULL)";
		$result = $MEvento->selectMestre($pSql);
		if(!$result){
			$this->mensagemErro = $MEvento->mensagemErro;
			return false;
		}

		if(is_array($resultEvento)){
			foreach($resultEvento as $key=>$value){
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultEvento[$key]['email_destinatario'] == NULL || trim($resultEvento[$key]['email_destinatario']) == ""){
					// GRAVAR CRITICA de email de destinatario não informado
					$MCritica->descricao = "Não foi possível enviar e-mail de Cancelamento da Nota Fiscal, email do destinatário não encontrado.";
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MContribuinte->ambiente = $resultEvento[$key]['NOTA_FISCAL_ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					return false;
				}

				$this->smtp = $returnContribuinte[0]['email_smtp'];
				$this->porta = $returnContribuinte[0]['email_porta'];
				$this->ssl = $returnContribuinte[0]['email_ssl'];
				$this->usuario = $returnContribuinte[0]['email_usuario'];
				$this->senha = $returnContribuinte[0]['email_senha'];
				$this->remetente = $returnContribuinte[0]['email_remetente'];
				$this->nomeRemetente = $returnContribuinte[0]['razao_social'];
				$this->destinatario = $resultEvento[$key]['email_destinatario'];
				
				$this->arrayConteudoResult = $resultNF[$key];
				
				// Enviar email de nota Autorizada para Destinatario
				if(!$this->emailCC()){
					// GRAVAR CRITICA de email de destinatario
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					continue;
				}

				// Atualiza o evento eviado do status
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MEvento->NOTA_FISCAL_numero_nota   = $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MEvento->NOTA_FISCAL_serie_nota    = $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MEvento->NOTA_FISCAL_ambiente      = $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MEvento->tipo_evento				= $resultEvento[$key]['tipo_evento'];
				$MEvento->numero_sequencia			= $resultEvento[$key]['numero_sequencia'];
				$MEvento->xml_env					= $resultEvento[$key]['xml_env'];
				$MEvento->xml_ret					= $resultEvento[$key]['xml_ret'];
				$MEvento->descricao					= $resultEvento[$key]['descricao'];
				$MEvento->protocolo					= $resultEvento[$key]['protocolo'];
				$MEvento->data_hora					= $resultEvento[$key]['data_hora'];
				$MEvento->status					= $resultEvento[$key]['status'];
				$MEvento->email_enviado				= "S";

				if(!$MEvento->insert()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
				}
			}
		}
	 }
	 
	 /*
	 *	@function Função para enviar E-mail modelo de Nota Autorizada
	 *	@autor Guilherme Silva
	 */
	  public function emailAutorizada(){
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-559" />
				<title>Nota Fiscal Eletronica</title>
				</head>

				<body style="font-family:Verdana, Geneva, sans-serif; font-size:13px;">
				<table width="100%" border="0" cellpadding="5">
				  <tr>
					<td><img src="http://www.softdib.com.br/imagens/logo1.png" height="100"></td>
					<td><img src="http://www.softdib.com.br/imagens/icone_nfe.jpg" height="100"></td>
				  </tr>
				</table>
				<div style="padding-left:40px;">
					<p style="color:#999; font-size:20px;">Prezado '.$this->arrayConteudoResult["nome_destinatario"].',</p>
					<p>Voc&ecirc; est&aacute; recebendo este e-mail contendo os dados da Nota Fiscal Eletr&ocirc;nica emitida atrav&eacute;s do sistema <a href="http://www.softdib.com.br">Softdib<a/></p>
					<p style="background-color:#CCC; font-size:12px;font-weight:bold; padding:5px;">EMITENTE</p>
					<table cellpadding="5">
						<tr>
							<td>Raz&atilde;o Social</td>
							<td><b>'.$this->nomeRemetente.'</b></td>
						</tr>
						<tr>
							<td>CNPJ</td>
							<td><b>'.substr($this->arrayConteudoResult['cnpj_emitente'],0,2).'.'
									.substr($this->arrayConteudoResult['cnpj_emitente'],2,3).'.'
									.substr($this->arrayConteudoResult['cnpj_emitente'],5,3).'/'
									.substr($this->arrayConteudoResult['cnpj_emitente'],8,4).'-'
									.substr($this->arrayConteudoResult['cnpj_emitente'],12,2).
									'</b></td>
						</tr>
					</table>
					<p style="background-color:#CCC; font-size:12px;font-weight:bold; padding:5px;">DESTINAT&Aacute;RIO</p>
					<table cellpadding="5">
						<tr>
							<td>Nome / Raz&atilde;o Social</td>
							<td><b>'.$this->arrayConteudoResult['nome_destinatario'].'</b></td>
						</tr>
						<tr>
							<td>CNPJ</td>
							<td><b>'.substr($this->arrayConteudoResult['cnpj_destinatario'],0,2).'.'
									.substr($this->arrayConteudoResult['cnpj_destinatario'],2,3).'.'
									.substr($this->arrayConteudoResult['cnpj_destinatario'],5,3).'/'
									.substr($this->arrayConteudoResult['cnpj_destinatario'],8,4).'-'
									.substr($this->arrayConteudoResult['cnpj_destinatario'],12,2).
									'</b></td>
						</tr>
					</table>
					<p style="background-color:#CCC; font-size:12px;font-weight:bold; padding:5px;">INFORMA&Ccedil;&Otilde;ES DA NOTA FISCAL</p>
					<table cellpadding="5">
						<tr>
							<td>N&uacute;mero / S&eacute;rie</td>
							<td><b>'.$this->arrayConteudoResult['numero_nota'].'/'.$this->arrayConteudoResult['serie_nota'].'</b></td>
						</tr>
						<tr>
							<td>Chave de Acesso</td>
							<td><b>'.$this->arrayConteudoResult['chave'].'</b></td>
						</tr>
						<tr>
							<td>Autoriza&ccedil;&atilde;o de Uso</td>
							<td><b>'.$this->arrayConteudoResult['numero_protocolo'].'</b></td>
						</tr>
						<tr>
							<td>Valor Total</td>
							<td><b>R$'.str_replace(".",",",$this->arrayConteudoResult['valor_total_nfe']).'</b></td>
						</tr>
					</table>
					<p>Para verificar a autoriza&ccedil;&atilde;o da SEFAZ desta nota acesse o site <a href="http://www.nfe.fazenda.gov.br/portal">http://www.nfe.fazenda.gov.br/portal</a></p>
					<p>Esta mensagem autom&aacute;tica, n&atilde;o h&aacute; necessidade de responder este e-mail.</p>
				</div>
				</body>
				</html>';
				
			try{
				$mail = new PHPMailer(true);
				$mail->IsSMTP();
				$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
				// Verificar SSL
				if($this->ssl == "S"){
					$mail->SMTPSecure = ssl;
				}
				$mail->Port = $this->porta;
				$mail->Host = $this->smtp;
				$mail->Username = $this->usuario;
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem
				
				if(strpos($this->destinatario,";")){
					$emails = explode(";",$this->destinatario);
					foreach($emails as $conteudo){
						@$mail->AddAddress($conteudo);
					}
				}else{
					@$mail->AddAddress($this->destinatario);
				}

				if($this->attach != ""){
					$mail->AddAttachment($this->attach);
				}
				
				if($this->attach2 != ""){
					$mail->AddAttachment($this->attach2);
				}
				
				if($this->attach3 != ""){
					$mail->AddAttachment($this->attach3);
				}
				
				if($this->attach4 != ""){
					$mail->AddAttachment($this->attach4);
				}
				
				$mail->Subject = "Nota Fiscal - No. ".$this->arrayConteudoResult['numero_nota']."/".$this->arrayConteudoResult['serie_nota']; // Assunto
				//$mail->WordWrap = 80; // Caracteres por linha
				
				$body = preg_replace('/\\\\/','', $body);
				$mail->MsgHTML($body); //corpo
				$mail->IsHTML(true);
				
				if(!$mail->Send()){
				   $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
				   return false;
				}
			}catch (phpmailerException $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}catch (Exception $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}
			return true;
	  }

	/*
	 *	@function Função para enviar E-mail modelo de Nota Cancelada
	 *	@autor Guilherme Silva
	 */
	  private function emailCancelada(){
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-559" />
					<title>Nota Fiscal Eletronica</title>
					</head>

					<body style="font-family:Verdana, Geneva, sans-serif; font-size:13px;">
					<table width="100%" border="0" cellpadding="5">
					  <tr>
						<td><img src="http://www.softdib.com.br/imagens/logo1.png" height="100"></td>
						<td><img src="http://www.softdib.com.br/imagens/icone_nfe.jpg" height="100"></td>
					  </tr>
					</table>
					<div style="padding-left:40px;">
						<p style="color:#999; font-size:20px;">Prezado '.$this->arrayConteudoResult["nome_destinatario"].',</p>
						<p>Voc&ecirc; est&aacute; recebendo este e-mail contendo os dados de <b>Cancelamento</b> da Nota Fiscal Eletr&ocirc;nica emitida atrav&eacute;s do sistema <a href="http://www.softdib.com.br">Softdib<a/></p>
						<p style="background-color:#FF4242; font-size:12px;font-weight:bold; padding:5px;">INFORMA&Ccedil;&Otilde;ES DA NOTA FISCAL</p>
						<table cellspacing="5">
							<tr>
								<td>N&uacute;mero/S&eacute;rie</td>
								<td><b>'.$this->arrayConteudoResult["NOTA_FISCAL_numero_nota"].'/'.$this->arrayConteudoResult["NOTA_FISCAL_numero_nota"].'</b></td>
							</tr>
							<tr>
								<td>Chave de Acesso</td>
								<td><b>'.$this->arrayConteudoResult["chave"].'</b></td>
							</tr>
							<tr>
								<td>Protocolo de Cancelamento</td>
								<td><b>'.$this->arrayConteudoResult["protocolo"].'</b></td>
							</tr>
							<tr>
								<td>Valor Total</td>
								<td><b>'.$this->arrayConteudoResult["valor_total_nfe"].'</b></td>
							</tr>
							<tr>
								<td>Motivo Cancelamento</td>
								<td><b>'.$this->arrayConteudoResult["descricao"].'</b></td>
							</tr>
						</table>
						<p>Para verificar a autoriza&ccedil;&atilde;o da SEFAZ desta nota acesse o site <a href="http://www.nfe.fazenda.gov.br/portal">http://www.nfe.fazenda.gov.br/portal</a></p>
						<p>Esta mensagem autom&aacute;tica, n&atilde;o h&aacute; necessidade de responder este e-mail.</p>
					</div>
					</body>
					</html>';
				
			try{
				$mail = new PHPMailer(true);
				$mail->IsSMTP();
				$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
				// Verificar SSL
				if($this->ssl == "S"){
					$mail->SMTPSecure = ssl;
				}
				$mail->Port = $this->porta;
				$mail->Host = $this->smtp;
				$mail->Username = $this->usuario;
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

				if(strpos($this->destinatario,";")){
					$emails = explode(";",$this->destinatario);
					foreach($emails as $conteudo){
						@$mail->AddAddress($conteudo);
					}
				}else{
					@$mail->AddAddress($this->destinatario);
				}

				$mail->Subject = "Nota Fiscal - No. ".$this->arrayConteudoResult['NOTA_FISCAL_numero_nota']."/".$this->arrayConteudoResult['NOTA_FISCAL_serie_nota']; // Assunto
				//$mail->WordWrap = 80; // Caracteres por linha
				
				$body = preg_replace('/\\\\/','', $body);
				$mail->MsgHTML($body); //corpo
				$mail->IsHTML(true);
				
				if(!$mail->Send()){
				   $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
				   return false;
				}
			}catch (phpmailerException $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}catch (Exception $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}
			return true;
	  }

	/*
	 *	@function Função para enviar E-mail modelo de Carta de Correção
	 *	@autor Guilherme Silva
	 */
	  public function emailCC(){
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-559" />
					<title>Nota Fiscal Eletronica</title>
					</head>

					<body style="font-family:Verdana, Geneva, sans-serif; font-size:13px;">
					<table width="100%" border="0" cellpadding="5">
					  <tr>
						<td><img src="http://www.softdib.com.br/imagens/logo1.png" height="100"></td>
						<td><img src="http://www.softdib.com.br/imagens/icone_nfe.jpg" height="100"></td>
					  </tr>
					</table>
					<div style="padding-left:40px;">
						<p style="color:#999; font-size:20px;">Prezado '.$this->arrayConteudoResult["nome_destinatario"].',</p>
						<p>Voc&ecirc; est&aacute; recebendo este e-mail contendo os dados da <b>Carta de Corre&ccedil;&atilde;o</b> da Nota Fiscal Eletr&ocirc;nica emitida atrav&eacute;s do sistema <a href="http://www.softdib.com.br">Softdib<a/></p>
						<p style="background-color:#FFC849; font-size:12px;font-weight:bold; padding:5px;">INFORMA&Ccedil;&Otilde;ES DA NOTA FISCAL</p>
						<table cellspacing="5">
							<tr>
								<td>N&uacute;mero/S&eacute;rie</td>
								<td><b>'.$this->arrayConteudoResult["NOTA_FISCAL_numero_nota"].'/'.$this->arrayConteudoResult["NOTA_FISCAL_serie_nota"].'</b></td>
							</tr>
							<tr>
								<td>Chave de Acesso</td>
								<td><b>'.$this->arrayConteudoResult["chave"].'</b></td>
							</tr>
							<tr>
								<td>Protocolo</td>
								<td><b>'.$this->arrayConteudoResult["protocolo"].'</b></td>
							</tr>
							<tr>
								<td>Valor Total</td>
								<td><b>'.$this->arrayConteudoResult["valor_total"].'</b></td>
							</tr>
							<tr>
								<td>Carta N&uacute;mero</td>
								<td><b>'.$this->arrayConteudoResult["numero_sequencia"].'</b></td>
							</tr>
							<tr>
								<td>Carta de Corre&ccedil;&atilde;o</td>
								<td><b>'.$this->arrayConteudoResult["descricao"].'</b></td>
							</tr>
						</table>
						<p>Para verificar a autoriza&ccedil;&atilde;o da SEFAZ desta nota acesse o site <a href="http://www.nfe.fazenda.gov.br/portal">http://www.nfe.fazenda.gov.br/portal</a></p>
						<p>Esta mensagem autom&aacute;tica, n&atilde;o h&aacute; necessidade de responder este e-mail.</p>
					</div>
					</body>
					</html>';
				
			try{
				$mail = new PHPMailer(true);
				$mail->IsSMTP();
				$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
				// Verificar SSL
				if($this->ssl == "S"){
					$mail->SMTPSecure = ssl;
				}
				$mail->Port = $this->porta;
				$mail->Host = $this->smtp;
				$mail->Username = $this->usuario;
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

				if(strpos($this->destinatario,";")){
					$emails = explode(";",$this->destinatario);
					foreach($emails as $conteudo){
						@$mail->AddAddress($conteudo);
					}
				}else{
					@$mail->AddAddress($this->destinatario);
				}

				if($this->attach != ""){
					$mail->AddAttachment($this->attach);
				}
				
				$mail->Subject = "Nota Fiscal - No. ".$this->arrayConteudoResult['NOTA_FISCAL_numero_nota']."/".$this->arrayConteudoResult['NOTA_FISCAL_serie_nota']; // Assunto
				//$mail->WordWrap = 80; // Caracteres por linha
				
				$body = preg_replace('/\\\\/','', $body);
				$mail->MsgHTML($body); //corpo
				$mail->IsHTML(true);
				
				if(!$mail->Send()){
				   $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
				   return false;
				}
			}catch (phpmailerException $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}catch (Exception $e){
			  $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
			  return false;
			}
			return true;
	  }
	}
?>