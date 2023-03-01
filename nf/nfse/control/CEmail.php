<?php
	/*
		Classe:					CEmail.php
		Autor:					Guilherme Silva
		Data:					09/02/2012
		Finalidade: 			Classe de envio de email
		Programas chamadores: 	
		Programas chamados:
	*/
    require_once("/var/www/html/nf/nfse/control/phpmailer/class.phpmailer.php");
    require_once("/var/www/html/nf/nfse/model/CNotaFiscal.php");	
	
	class CEmail{
	  //Atributos
	  public $xml;
	  public $mensagemErro;
	  
	  private $grupo;
	  
	  private $smtp;
	  private $porta;
	  private $tls;
	  private $usuario;
	  private $senha;
	  private $prestadorCnpj;
	  
	  //Metodos
	  // Construtor inserido par gerar setar o grupo que instancia a classe
	  function __construct($pGrupo="") {                                   
  	    $this->grupo = $pGrupo;                                              
	  }                                                                    

	  public function enviarNF($pCnpj, $pControle, $pEmail=""){
		$CNotaFiscal = new CNotaFiscal($this->grupo);
		$this->prestadorCnpj = $pCnpj;
		if($this->smtp == ""){ $this->lerArquivoConfig(); }
		
		$retorno = $CNotaFiscal->obterNotaFiscal($pCnpj, $pControle);
		if(!$retorno){
			$this->mensagemErro = $CNotaFiscal->mensagemErro;
			return false;
		}
		$numeroNota = $retorno->fields['nf_numero'];
		$numeroAutenticacao = $retorno->fields['nf_autenticacao'];
		$link = $retorno->fields['nf_link'];
		if($pEmail == ""){
			$pEmail = $retorno->fields['tomador_email'];
		}
		if(trim($pEmail) == ""){
		  $this->mensagemErro = "Email nao enviado para tomador!";
		  return false;
		}
		// Corpo da mensagem
		$body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-559" />
<title>Nota Fiscal Eletronica</title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size:11px;">
<table width="100%" border="0" cellpadding="5">
  <tr>
    <td colspan="2">Prezado,</td>
  </tr>
  <tr>
    <td colspan="2"> conforme solicitado envio da Nota Fiscal de Servi&ccedil;o N&uacute;mero '.$numeroNota.' atrav&eacute;s do Sistema Softdib</td>
  </tr>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr style="background-color:#CCC; font-size:12px">
    <td colspan="2"><b>PRESTADOR</b></td>
  </tr>
  <tr>
    <td width="10%">Empresa</td>
    <td>'.$retorno->fields['empresa_descricao'].'</td>
  </tr>
  <tr>
    <td>Filial</td>
    <td>'.$retorno->fields['filial_descricao'].'</td>
  </tr>
  <tr>
    <td>CNPJ</td>
    <td>'.$retorno->fields['prestador_cpf_cnpj'].'</td>
  </tr>
  <tr style="background-color:#CCC; font-size:12px">
    <td colspan="2"><b>TOMADOR</b></td>
  </tr>
  <tr>
    <td>Nome/Raz&atilde;o Social</td>
    <td>'.$retorno->fields['tomador_nome_razao_social'].'</td>
  </tr>
  <tr>
    <td>CPF/CNPJ</td>
    <td>'.$retorno->fields['tomador_cpf_cnpj'].'</td>
  </tr>
  <tr>
    <td>Inscri&ccedil;&atilde;o Estadual</td>
    <td>'.$retorno->fields['tomador_ie'].'</td>
  </tr>
  <tr style="background-color:#CCC; font-size:12px">
    <td colspan="2"><b>NOTA FISCAL DE SERVI&Ccedil;O</b></td>
  </tr>
  <tr>
    <td>N&uacute;mero </td>
    <td>'.$numeroNota.' </td>
  </tr>
  <tr>
  	<td>Autentica&ccedil;&atilde;o</td>
	<td>'.$numeroAutenticacao.' </td>
  </tr>
  <tr>
    <td colspan="2">visualizar NF acesse '.$retorno->fields['nf_link'].'</td>
  </tr>
</table>
</body>
</html>
';

	try{
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->SMTPAuth = true; // "true" para autenticar "false" para nao autenticar
		##TLS Somente para contas do gmail
		if($this->tls == "S"){
			$mail->SMTPSecure = tls;
		}
		$mail->Port = $this->porta; // Default 25
		$mail->Host = $this->smtp;
		$mail->Username = $this->usuario;
		$mail->Password = $this->senha;
		$mail->SMTPDebug = 0; // Nivel de debug : 0-desabilitado, 1-apenas erro, 2-erro e informacao

		$mail->AddReplyTo = $this->usuario;

		$mail->From = $this->usuario; // Email de origem
		$mail->FromName = "Nota Fiscal Eletronica de Servico"; // Nome de Origem

                $to = $pEmail;
                $pEmail = explode(";", $to);
                foreach($pEmail as $email) {
                   @$mail->AddAddress($email);
                }
                  
		$mail->Subject = "Nota Fiscal Eletronica de Servico ($numeroNota)"; // Assunto
		$mail->WordWrap = 80; // Caracteres por linha
		
		$body = preg_replace('/\\\\/','', $body);
		$mail->MsgHTML($body); //corpo
		$mail->IsHTML(true);
		
		if(!$mail->Send()){
		   $this->mensagemErro = "Erro ao tentar enviar email: ".$mail->ErrorInfo;
		}
	}catch (phpmailerException $e){
	  $this->mensagemErro = "Erro ao tentar enviar email: ".$e->errorMessage();
	}catch (Exception $e){
	  $this->mensagemErro = "Erro ao tentar enviar email: ".$e->errorMessage();
	}
		return true;
	  }
	  
/*	  private function lerArquivoConfig(){
		$configuracao = parse_ini_file("/var/www/html/nf/nfse/configuracoes/config.ini");
		$this->smtp = $configuracao['smtp'];
		$this->porta = $configuracao['porta'];
		//$this->tls = $configuracao['tls'];
		$this->usuario = $configuracao['email'];
		$this->senha = $configuracao['senhaEmail'];
	  }*/
	  
    private function lerArquivoConfig(){
	    $CEmpresa = new CEmpresa($this->grupo);                   
  	    $CEmpresa->cnpj = $this->prestadorCnpj;                   
	    $retorno = $CEmpresa->obterEmpresa();                            
	  
	    if(!$retorno || $retorno == null){            
              $this->mensagemErro = $CEmpresa->mensagemErro;
              return false;
            }else{                                        
              $this->smtp = $retorno[0]['email_smtp'];
              $this->porta = $retorno[0]['email_porta'];
              //$this->tls = $retorno[0]['tls'];
              $this->usuario = $retorno[0]['email_usuario'];
              $this->senha = $retorno[0]['email_senha'];
              return true;
            }
          }                                                                               
	}
?>