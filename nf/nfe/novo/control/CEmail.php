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
	 *	@function Fun��o para testarEnvio de Email
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
			if($this->ssl == "EXCHANGE"){ // Exchange da Leogap
				$nomeUsuario = explode("@",$this->usuario);
				$mail->Username = $nomeUsuario[0];
			}else{
				$mail->Username = $this->usuario;
			}
			$mail->Password = $this->senha;
			$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
			$mail->AddReplyTo = $this->usuario;
			$mail->From = $this->usuario; // Email de origem
			$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

			$exist = strpos($this->usuario,";"); 
			if($exist !== false){                     
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
	 *	@function Fun��o para selecionar Notas com envio de e-mail pendente e submet�-las
	 *	@autor Guilherme Silva
	 */
	 public function mEmailsPendentes(){
		// Obter NFs de contribuintes ativos e que estejam AUTORIZADAS com SUCESSO e pendentes de envio
		$MContribuinte = new MContribuinte($this->grupo);
		$MNf = new MNotaFiscal($this->grupo);
		$MEvento = new MEvento($this->grupo);
		$MCritica = new MCritica($this->grupo);
		$pSql = "SELECT NF.* FROM nfe_".$this->grupo.".`NOTA_FISCAL` as NF, nfe_".$this->grupo.".`CONTRIBUINTE` as C WHERE C.ativo = 'S' AND NF.status = '03' AND ( (NF.email_enviado <> 'S' AND NF.email_enviado <> 'E') OR NF.email_enviado IS NULL) group by NF.numero_nota";
		$resultNF = $MNf->selectAllMestre($pSql);

		if(!$resultNF){
			$this->mensagemErro = $MNf->mensagemErro;
			return false;
		}

		if(is_array($resultNF)){
			foreach($resultNF as $key=>$value){
			// Preparar campos da Nota Fiscal para posterior atualiza��o.
				$MNf->cnpj_emitente = $resultNF[$key]['cnpj_emitente'];
				$MNf->numero_nota = $resultNF[$key]['numero_nota'];
				$MNf->serie_nota = $resultNF[$key]['serie_nota'];
				$MNf->ambiente = $resultNF[$key]['ambiente'];

			// Preparar campos da Critica para posterior atualiza��o.
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultNF[$key]['cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultNF[$key]['ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultNF[$key]['serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultNF[$key]['numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultNF[$key]['email_destinatario'] == NULL || trim($resultNF[$key]['email_destinatario']) == ""){
					//GRAVAR CRITICA de email de destinatario n�o informado
					$MCritica->descricao = "N�o foi poss�vel enviar e-mail de Autoriza��o da Nota Fiscal, email do destinat�rio n�o encontrado. ";
					echo $MCritica->descricao;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Atualizar o staus da nota para E (ERRO)
					$MNf->email_enviado = "E";
					$MNf->update();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultNF[$key]['cnpj_emitente'];
				$MContribuinte->ambiente = $resultNF[$key]['ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					continue;
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
					// Inserir erro nas Criticas
					$MCritica->descricao = $this->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Atualizar o staus da nota para E (ERRO)
					$MNf->email_enviado = "E";
					$MNf->update();
					continue;
				}

				// Remove o PDF do relatorios para nao dar problema ao abrir pelo usuario na web
				@system("cd /var/www/html/relatorios/; rm -rf /var/www/html/relatorios/NFE_".$resultNF[$key]['chave']."*.pdf  /var/www/html/relatorios/NFE_".$resultNF[$key]['chave']."*.xml");

				$MNf->email_enviado = "S";

				if(!$MNf->update()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					echo $this->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
				}
			}
		}


		// Obter Evento de contribuintes ativos para notas fiscais CANCELADAS com SUCESSO pendentes de envio
		// foreach para envio do modelo de email CANCELADAS
		// caso de falha no processo gravar CRITICA
		$pSql = "SELECT E.*, NF.nome_destinatario, NF.valor_total_nfe, NF.chave, NF.email_destinatario
					FROM nfe_".$this->grupo.".`CONTRIBUINTE` as C, nfe_".$this->grupo.".`EVENTO` as E
						LEFT JOIN nfe_".$this->grupo.".`NOTA_FISCAL` as NF
						ON 	E.NOTA_FISCAL_cnpj_emitente = NF.cnpj_emitente 	AND
							E.NOTA_FISCAL_numero_nota = NF.numero_nota 		AND
							E.NOTA_FISCAL_serie_nota = NF.serie_nota 		AND
							E.NOTA_FISCAL_ambiente = NF.ambiente
					WHERE C.ativo = 'S' AND E.tipo_evento = '4' AND 
							(E.status = '101' OR E.status = '128' OR E.status = '135' OR E.status = '136')
						  AND ( ( E.email_enviado <> 'S' AND E.email_enviado <> 'E' )  OR E.email_enviado IS NULL )";
		$resultEvento = $MEvento->selectMestre($pSql);
		if(!$resultEvento){
			$this->mensagemErro = $MEvento->mensagemErro;
			return false;
		}

		if(is_array($resultEvento)){
			foreach($resultEvento as $key=>$value){
				
				// Prepara para Atualizar o Evento
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MEvento->NOTA_FISCAL_numero_nota   = $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MEvento->NOTA_FISCAL_serie_nota    = $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MEvento->NOTA_FISCAL_ambiente      = $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MEvento->tipo_evento				= $resultEvento[$key]['tipo_evento'];
				$MEvento->numero_sequencia			= $resultEvento[$key]['numero_sequencia'];
				$MEvento->xml_env					= $resultEvento[$key]['xml_env'];
				$MEvento->xml_ret					= $resultEvento[$key]['xml_ret'];
                                $MEvento->xml                                           = $resultEvento[$key]['xml'];
				$MEvento->descricao					= $resultEvento[$key]['descricao'];
				$MEvento->protocolo					= $resultEvento[$key]['protocolo'];
				$MEvento->data_hora					= $resultEvento[$key]['data_hora'];
				$MEvento->status					= $resultEvento[$key]['status'];
				
				// Prepara para Atualizar a Critica
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultEvento[$key]['NOTA_FISCAL_ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultEvento[$key]['NOTA_FISCAL_serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultEvento[$key]['NOTA_FISCAL_numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultEvento[$key]['email_destinatario'] == NULL || trim($resultEvento[$key]['email_destinatario']) == ""){
					// GRAVAR CRITICA de email de destinatario n�o informado
					$MCritica->descricao = "N�o foi poss�vel enviar e-mail de Cancelamento da Nota Fiscal, email do destinat�rio n�o encontrado. ";
					echo $MCritica->descricao;
					// Inserir erro nas Criticas
					$MCritica->descricao = $this->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Grava erro no registro de Email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultEvento[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MContribuinte->ambiente = $resultEvento[$key]['NOTA_FISCAL_ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					echo $MContribuinte->mensagemErro;
					// Grava erro no registro de Email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
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
				
				$this->arrayConteudoResult = $resultEvento[$key];
				
				// Enviar email de nota Autorizada para Destinatario
				if(!$this->emailCancelada()){
					echo $this->mensagemErro;
					// Grava erro no registro de Email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
					continue;
				}

				// Atualizar Evento com Sucesso
				$MEvento->email_enviado				= "S";

				if(!$MEvento->insert()){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
				}
			}
		}

		// Obter Evento de contribuintes ativos para notas fiscais com CARTA DE CORRE��O emitidas com SUCESSO
		// foreach para envio do modelo de email de CARTA DE CORRE��O
		// caso de falha no processo gravar CRITICA
		$pSql = "SELECT E.*, NF.nome_destinatario, NF.valor_total_nfe, NF.chave, NF.email_destinatario
					FROM nfe_".$this->grupo.".`CONTRIBUINTE` as C, nfe_".$this->grupo.".`EVENTO` as E
						LEFT JOIN nfe_".$this->grupo.".`NOTA_FISCAL` as NF
						ON 	E.NOTA_FISCAL_cnpj_emitente = NF.cnpj_emitente 	AND
							E.NOTA_FISCAL_numero_nota = NF.numero_nota 		AND
							E.NOTA_FISCAL_serie_nota = NF.serie_nota 		AND
							E.NOTA_FISCAL_ambiente = NF.ambiente
					WHERE C.ativo = 'S' AND E.tipo_evento = '5' AND E.status = '102' AND ( (E.email_enviado <> 'S' AND E.email_enviado <> 'E') OR E.email_enviado IS NULL)";
		$resultCarta = $MEvento->selectMestre($pSql);
		if(!$resultCarta){
			$this->mensagemErro = $MEvento->mensagemErro;
			return false;
		}

		if(is_array($resultCarta)){
			foreach($resultCarta as $key=>$value){
				// Prepara para atualiza��o do Email
				$MEvento->NOTA_FISCAL_cnpj_emitente	= $resultCarta[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MEvento->NOTA_FISCAL_numero_nota   = $resultCarta[$key]['NOTA_FISCAL_numero_nota'];
				$MEvento->NOTA_FISCAL_serie_nota    = $resultCarta[$key]['NOTA_FISCAL_serie_nota'];
				$MEvento->NOTA_FISCAL_ambiente      = $resultCarta[$key]['NOTA_FISCAL_ambiente'];
				$MEvento->tipo_evento				= $resultCarta[$key]['tipo_evento'];
				$MEvento->numero_sequencia			= $resultCarta[$key]['numero_sequencia'];
				$MEvento->xml_env					= $resultCarta[$key]['xml_env'];
				$MEvento->xml_ret					= $resultCarta[$key]['xml_ret'];
				$MEvento->descricao					= $resultCarta[$key]['descricao'];
				$MEvento->protocolo					= $resultCarta[$key]['protocolo'];
				$MEvento->data_hora					= $resultCarta[$key]['data_hora'];
				$MEvento->status					= $resultCarta[$key]['status'];
				
				// Prepara para atualiza��o da Critica
				$MCritica->EVENTO_NOTA_FISCAL_cnpj_emitente	= $resultCarta[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MCritica->EVENTO_NOTA_FISCAL_ambiente 		= $resultCarta[$key]['NOTA_FISCAL_ambiente'];
				$MCritica->EVENTO_NOTA_FISCAL_serie_nota 	= $resultCarta[$key]['NOTA_FISCAL_serie_nota'];
				$MCritica->EVENTO_NOTA_FISCAL_numero_nota 	= $resultCarta[$key]['NOTA_FISCAL_numero_nota'];
				$MCritica->codigo_referencia = "";
				
				if($resultCarta[$key]['email_destinatario'] == NULL || trim($resultCarta[$key]['email_destinatario']) == ""){
					// GRAVAR CRITICA de email de destinatario n�o informado
					$MCritica->descricao = "N�o foi poss�vel enviar e-mail de Cancelamento da Nota Fiscal, email do destinat�rio n�o encontrado.";
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Atualiza erro no email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
					continue;
				}

				// Antes de enviar o e-mail deve obter do cadastro de contribuinte os dados
				$MContribuinte = new MContribuinte($this->grupo);
				$MContribuinte->cnpj = $resultCarta[$key]['NOTA_FISCAL_cnpj_emitente'];
				$MContribuinte->ambiente = $resultCarta[$key]['NOTA_FISCAL_ambiente'];

				$returnContribuinte = $MContribuinte->selectCNPJAmbiente();

				if(!$returnContribuinte){
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Atualiza erro no email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
					return false;
				}

				$this->smtp = $returnContribuinte[0]['email_smtp'];
				$this->porta = $returnContribuinte[0]['email_porta'];
				$this->ssl = $returnContribuinte[0]['email_ssl'];
				$this->usuario = $returnContribuinte[0]['email_usuario'];
				$this->senha = $returnContribuinte[0]['email_senha'];
				$this->remetente = $returnContribuinte[0]['email_remetente'];
				$this->nomeRemetente = $returnContribuinte[0]['razao_social'];
				$this->destinatario = $resultCarta[$key]['email_destinatario'];
				
				$this->arrayConteudoResult = $resultNF[$key];
				
				// Enviar email de nota Autorizada para Destinatario
				if(!$this->emailCC()){
					// GRAVAR CRITICA de email de destinatario
					$MCritica->descricao = $this->mensagemErro = $MContribuinte->mensagemErro;
					$MCritica->data_hora = date("Y-m-d H:i:s");
					$MCritica->insert();
					// Atualiza erro no email enviado
					$MEvento->email_enviado = "E";
					$MEvento->insert();
					continue;
				}

				// Atualizar email enviado com sucesso
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
	 *	@function Fun��o para enviar E-mail modelo de Nota Autorizada
	 *	@autor Guilherme Silva
	 */
	  public function emailAutorizada(){
		$cnpjRemetente = substr($this->arrayConteudoResult['cnpj_emitente'],0,2).'.'
							.substr($this->arrayConteudoResult['cnpj_emitente'],2,3).'.'
							.substr($this->arrayConteudoResult['cnpj_emitente'],5,3).'/'
							.substr($this->arrayConteudoResult['cnpj_emitente'],8,4).'-'
							.substr($this->arrayConteudoResult['cnpj_emitente'],12,2);
		$cnpjDestinatario = substr($this->arrayConteudoResult['cnpj_destinatario'],0,2).'.'
							.substr($this->arrayConteudoResult['cnpj_destinatario'],2,3).'.'
							.substr($this->arrayConteudoResult['cnpj_destinatario'],5,3).'/'
							.substr($this->arrayConteudoResult['cnpj_destinatario'],8,4).'-'
							.substr($this->arrayConteudoResult['cnpj_destinatario'],12,2);
								
		$body = "";
		if(file_exists("/var/www/html/nf/nfe/novo/config/layout_email_".$this->grupo.".html")){
			$body = file_get_contents("/var/www/html/nf/nfe/novo/config/layout_email_".$this->grupo.".html");
		}elseif(file_exists("/var/www/html/nf/nfe/novo/config/layout_email.html")){
			$body = file_get_contents("/var/www/html/nf/nfe/novo/config/layout_email.html");
		}
		
		if($body != ""){
			$body = str_replace('[grupo]', 				$this->grupo, $body);
			$body = str_replace('[nome-destinatario]', 	$this->arrayConteudoResult["nome_destinatario"], $body);
			$body = str_replace('[nome-remetente]', 	$this->nomeRemetente, $body);
			$body = str_replace('[cnpj-remetente]', 	$cnpjRemetente, $body);
			$body = str_replace('[nome-destinatario]', 	$this->arrayConteudoResult['nome_destinatario'], $body);
			$body = str_replace('[cnpj-destinatario]', 	$cnpjDestinatario, $body);
			$body = str_replace('[numero-nota]', 		$this->arrayConteudoResult['numero_nota'], $body);
			$body = str_replace('[serie-nota]', 		$this->arrayConteudoResult['serie_nota'], $body);
			$body = str_replace('[chave]', 				$this->arrayConteudoResult['chave'], $body);
			$body = str_replace('[numero-protocolo]', 	$this->arrayConteudoResult['numero_protocolo'], $body);
			$body = str_replace('[valor-total]', 		str_replace(".",",",$this->arrayConteudoResult['valor_total_nfe']), $body);
		}else{
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
					<td><img src="http://www.softdib.com.br/imagens/clientes/'.$this->grupo.'.jpg" height="100"></td>
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
							<td><b>'.$cnpjRemetente.'</b></td>
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
							<td><b>'.$cnpjDestinatario.'</b></td>
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
			}
			

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
				if($this->ssl == "EXCHANGE"){ // Exchange da Leogap
					$nomeUsuario = explode("@",$this->usuario);
					$mail->Username = $nomeUsuario[0];
				}else{
					$mail->Username = $this->usuario;	
				}
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem
				
				// Substituir a , por ; em caso de informada , como separador
				$this->destinatario = str_replace(",",";",$this->destinatario);	

				$exist = strpos($this->destinatario,";"); 
				if($exist !== false){                     
					$emails = explode(";",$this->destinatario);
					foreach($emails as $conteudo){
						if(trim($conteudo) != ""){
							if($mail->ValidateAddress($conteudo)){
								$mail->AddAddress($conteudo);
							}else{
								$this->mensagemErro = "CEmail-> Email ".$conteudo." invalido \n";
							}
						}
						
					}
				}elseif(trim($this->destinatario) != ""){
					if($mail->ValidateAddress($this->destinatario)){
						$mail->AddAddress($this->destinatario);
					}else{
						$this->mensagemErro = "CEmail-> Email ".$conteudo." invalido \n";
					}
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
	 *	@function Fun��o para enviar E-mail modelo de Nota Cancelada
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
								<td><b>'.$this->arrayConteudoResult["NOTA_FISCAL_numero_nota"].'/'.$this->arrayConteudoResult["NOTA_FISCAL_serie_nota"].'</b></td>
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
				if($this->ssl == "EXCHANGE"){ // Exchange da Leogap
					$nomeUsuario = explode("@",$this->usuario);
					$mail->Username = $nomeUsuario[0];
				}else{
					$mail->Username = $this->usuario;	
				}
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

				$exist = strpos($this->destinatario,";"); 
				if($exist !== false){                     
					$emails = explode(";",$this->destinatario);
					foreach($emails as $conteudo){
						@$mail->AddAddress($conteudo);
					}
				}else{
					@$mail->AddAddress($this->destinatario);
				}
                                
                                if($this->arrayConteudoResult["xml"] != ""){
                                    $mail->AddAttachment(base64_decode($this->arrayConteudoResult["xml"]));
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
	 *	@function Fun��o para enviar E-mail modelo de Carta de Corre��o
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
				if($this->ssl == "EXCHANGE"){ // Exchange da Leogap
					$nomeUsuario = explode("@",$this->usuario);
					$mail->Username = $nomeUsuario[0];
				}else{
					$mail->Username = $this->usuario;	
				}
				$mail->Password = $this->senha;
				$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
				$mail->AddReplyTo = $this->usuario;
				$mail->From = $this->usuario; // Email de origem
				$mail->FromName = "Nota Fiscal Eletronica"; // Nome de Origem

				$exist = strpos($this->destinatario,";"); 
				if($exist !== false){                     
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
	  
	  public function emailLog($mensagem){
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
		// Verificar SSL
		$mail->Port = "587";
		$mail->Host = "mail.softdib.com.br";
		$mail->Username = "softdib@softdib.com.br";
		$mail->Password = "stella";
		$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao
		$mail->AddReplyTo = "softdib@softdib.com.br";
		$mail->From = "softdib@softdib.com.br"; // Email de origem
		$mail->FromName = "Softdib"; // Nome de Origem

		$mail->AddAddress("guilherme@softdib.com.br");
		
		$mail->Subject = "Log da Nota Fiscal Eletronica"; // Assunto
		//$mail->WordWrap = 80; // Caracteres por linha
		
		$mail->MsgHTML($mensagem); //corpo
		
		if(!$mail->Send()){
		   $this->mensagemErro = "CEmail -> ".$mail->ErrorInfo;
		   return false;
		}
		return true;
	  }
	}
?>