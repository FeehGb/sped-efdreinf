<?php

require 'class.phpmailer.php';

try {
	$mail = new PHPMailer(true);

	$body             = "Teste de envio."; // Texto do email
	$body             = preg_replace('/\\\\/','', $body);

	$mail->IsSMTP();
	$mail->SMTPAuth   = true; // Habilita autenticação do servidor SMTP
	$mail->Port       = 25; // Porta do servidor SMTP
	$mail->Host       = "smtp.axion.ind.br"; // Servidor SMTP
	$mail->Username   = "contato@axion.ind.br"; // Email usado para envio
	$mail->Password   = "contato123"; // Senha do email usado para envio
	$mail->SMTPDebug  = 1; // Nivel de debug: 0 desabilitado -  1 apenas erro - 2 erro e informacao

	$mail->AddReplyTo = "$Username";

	$mail->From       = "contato@axion.ind.br"; // Email apresentado como remetente
	$mail->FromName   = "claudio"; // Nome do remetente

	$to = "fabio@softdib.com.br"; // Destinatario

	//$mail->AddAttachment("/user/relat/REL..."); // Arquivo anexado ao email

	$mail->AddAddress($to);
	//$mail->AddAddress "segundo_destinatario@dominio.com.br";
	//$mail->AddAddress "terceiro_destinatario@dominio.com.br";

	$mail->Subject  = "Phpmailer"; // Assunto do email

	$mail->WordWrap   = 80;

	$mail->MsgHTML($body);

	$mail->IsHTML(true);

	$mail->Send();
}

catch (phpmailerException $e) {
	echo $e->errorMessage();
}

?>
