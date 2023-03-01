<?php
	if(file_exists("configuracoes/config.ini")){
		header('Location: view/');
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
		<!-- Adicionar neste local arquivos de SCRIPT (javascript js, vb, as) e CSS -->
		<!--
      exemplo de como adicionar um arquivo de css
      <link rel="stylesheet" href="caminho do arquivo" />

      exemplo de como adicionar um arquivo de script
      <script src="caminho do arquivo" type="tipo do script"></script>
    -->
    <link rel="shortcut icon" href="../imagens/softdib.ico" >
    <link rel="stylesheet" href="view/jquery/css/cupertino/jquery-ui-1.8.17.custom.css" />
    <script src="view/jquery/js/jquery-1.7.1.min.js" type="text/javascript"></script>
    <script src="view/jquery/js/jquery-ui-1.8.17.custom.min.js" type="text/javascript"></script>
    <title>NFS-e Softdib (v&beta;)</title>
  </head>
  <style>
	body{ font: 80% "Trebuchet MS", sans-serif; margin: 50px;}
  </style>
  <script>
	$(function() {
	  $( "a").button();
	});
  </script>
<body style="position: relative; height:100%">
<div style="float:left; width:50%;"><p style="font-size:20px; color:#F00;">Nota Fiscal Eletr&ocirc;nica de Servi&ccedil;o da Softdib (NFS-e) (v&beta;)</p></div>
<div align="right"><img src="imagens/logoSoftdib50x60.png" height="100" /></div>
<div class="ui-widget-content" style="padding:50px; margin-left:15%; margin-right:15%; margin-top:10px;">
<p>Seja Bem Vindo ao <b>Sistema de Nota Fiscal Eletr&ocirc;nica de Servi&ccedil;o da Softdib (NFS-e) (v&beta;)</b></p>
<p>Verificamos que voc&ecirc; ainda n&atilde;o instalou o sistema!</p>
<p>O que deseja fazer?</p>
<br /><br />
<div align="center">
<p><a href="instalador/">Instalar ou Reconfigurar Sistema</a>
&nbsp;
<a href="view/">J&aacute; Instalei, desejo ir para a Tela Principal</a></p>
</div>
</div>
</body>
</html>