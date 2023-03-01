<?php
// Flag que indica se há erro ou não
$erro = null;
// Quando enviado o formulário
if (isset($_FILES['ws_arquivo']))
{
    // Extensões permitidas
    $extensoes = array(".pfx");
    // Caminho onde ficarão os arquivos
    $caminho = "/var/www/html/nfse/configuracoes/certificados/";
	// TODO Apaga todos certificados que jah cadastrados
    // Recuperando informações do arquivo
    $nome = $_FILES['ws_arquivo']['name'];
    $temp = $_FILES['ws_arquivo']['tmp_name'];
    // Verifica se a extensão é permitida
    if (!in_array(strtolower(strrchr($nome, ".")), $extensoes)) {
		$erro = 'Extensão inválida, o arquivo deverá ser um .pfx';
	}
    // Se não houver erro
    if (!isset($erro)) {
        // Gerando um nome aleatório para o arquivo
        $nomeArquivo = $_POST['ws_cnpj'].".pfx";
        // Movendo arquivo para servidor
        if (!move_uploaded_file($temp, $caminho.$nomeArquivo))
            $erro = 'Não foi possível anexar o arquivo';
    }
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
    <link rel="stylesheet" href="../../view/jquery/css/cupertino/jquery-ui-1.8.17.custom.css" />
    <script src="../../view/jquery/js/jquery-1.7.1.min.js" type="text/javascript"></script>
    <script src="../../view/jquery/js/jquery-ui-1.8.17.custom.min.js" type="text/javascript"></script>
  </head>
  <style>
	body{ font: 80% "Trebuchet MS", sans-serif; margin: 50px;}
	.titulo{
		 font: 30px "Trebuchet MS", sans-serif;
		 color:#666;
	}
	.subtitulo{
		 font: 20px "Trebuchet MS", sans-serif;
		 color:#666;
	}
	.exemplo{
		 font: 11px "Trebuchet MS", sans-serif;
		 color:#666;
	}

  </style>

<body style="position: relative; height:100%;">
<!---------------->
<div id="aguarde" style="position:fixed; left:0px; top:0px; padding:10px; background-color:#F90; width:100%"><b>AGUARDE ...</b></div>
<!---------------->
<!---FORMULARIO DE CONFIGURACAO WEB SERVICE----------------------------------------------------------------------------------->
  <form action="" method="post" name="formConfigWS" id="formConfigWS" enctype="multipart/form-data">
  <input type="hidden"  name="campos" id="campos" value="<?php echo $_POST['campos'];?>" />
  <table border="0">
  	<tr>
    <td>
    <div class="corpo ui-widget-content" style="margin-left:30px;">
        <table cellpadding="5">
        <tr>
        	<td colspan="3" class="subtitulo">Configura&ccedil;&otilde;es do Web Service</td>
        </tr>
		<tr>
          <td>CNPJ Prestador</td>
          <td><input type="text" name="ws_cnpj" id="ws_cnpj" size="30" value="<?php echo $_POST['ws_cnpj'];?>"/></td>
          <td class="exemplo">CNPJ da Empresa que adquiriu o certificado digital.</td>
        </tr>
		<tr>
          <td>Certificado Digital</td>
          <?php
		  if (isset($_FILES['ws_arquivo'])){
	          echo '<td>'.$_POST['ws_cnpj'].'.pfx</td>';
			  echo '<input type="hidden" name="ws_arquivoPFX" id="ws_arquivoPFX" value="'.$caminho.$_POST['ws_cnpj'].'.pfx">';
		  }else{
			  echo '<td><input type="file" name="ws_arquivo" id="ws_arquivo" size="50"/></td>';
		  }
		  ?>
          <td class="exemplo">Arquivo.PFX do Certificado Digital<b></b></td>
        </tr>
        <tr>
          <td width="172">C&oacute;digo TOM</td>
          <td width="180"><input type="text" name="ws_codigoTom" id="ws_codigoTom" size="30" readonly="readonly" value="75353" /></td>
          <td width="301" class="exemplo">C&oacute;digo da Cidade no cadastro da Receita Federal
          </td></tr>
        <tr>
          <td>URL Servidor</td>
          <td><input type="text" name="ws_url" id="ws_url" size="100" value=""/></td>
	  <td class="exemplo">Servidor Piloto: https://pilotoisscuritiba.curitiba.pr.gov.br/nfse_ws/NfseWs.asmx?wsdl<br><br>
			      Servidor Produ&ccedil;&atilde;o: https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx</td>
        </tr>
        <tr>
          <td>Porta</td>
          <td><input type="text" name="ws_porta" id="ws_porta" size="10" value="443"/></td>
          <td class="exemplo">80 - Conex&atilde;o n&atilde;o Segura / 443 - Conex&atilde;o Segura</td>
        </tr>
        <tr>
          <td>Conex&atilde;o Segura</td>
          <td><input type="checkbox" name="ws_checkConexao" id="ws_checkConexao" <?php if($_POST['ws_conexaoSegura'] == "S"){ echo ' checked="checked" '; }else{ echo '';} ?> />
          <input type="hidden" name="ws_conexaoSegura" id="ws_conexaoSegura" /></td>
          
          <td class="exemplo">Para protocolo HTTPS (SSL) utilizar conex&atilde;o segura<b></b></td>
        </tr>
        <tr>
          <td>Senha Certificado</td>
          <td><input type="password" name="ws_senha" id="ws_senha" size="30" value="<?php echo $_POST['ws_senha'];?>"/></td>
          <td class="exemplo">Senha do certificado digital.</td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>&nbsp;</td>
          <td class="exemplo">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3"><span class="subtitulo">Configura&ccedil;&otilde;es do Proxy</span></td>
          </tr>
        <tr>
          <td>Autentica&ccedil;&atilde;o Proxy</td>
          <td><input type="checkbox" name="ws_checkProxy" id="ws_checkProxy" "<?php if($_POST['ws_proxy'] == "S"){ echo ' checked="checked" '; }else{ echo '';} ?>" />
          <input type="hidden" name="ws_proxy" id="ws_proxy" /></td>
          <td class="exemplo">Marque em caso da conex&atilde;o a internet seja intermediada por um Servidor Proxy</td>
          </tr>
        <tr>
          <td>IP Servidor Proxy</td>
          <td><input type="text" name="ws_ipProxy" id="ws_ipProxy" size="30" value="<?php echo $_POST['ws_ipProxy'];?>"/></td>
          <td class="exemplo">IP do Servidor Proxy</td>
          </tr>
        <tr>
          <td>Porta</td>
          <td><input type="text" name="ws_portaProxy" id="ws_portaProxy" size="5" value="<?php echo $_POST['ws_portaProxy'];?>"/></td>
          <td class="exemplo">Porta de Conex&atilde;o do Servidor Proxy</td>
          </tr>
        <tr>
          <td>Usu&aacute;rio</td>
          <td><input type="text" name="ws_usuarioProxy" id="ws_usuarioProxy" size="30" value="<?php echo $_POST['ws_usuarioProxy'];?>"/></td>
          <td class="exemplo">Usu&aacute;rio para autentica&ccedil;&atilde;o no servidor Proxy</td>
          </tr>
        <tr>
          <td>Senha</td>
          <td><input type="password" name="ws_senhaProxy" id="ws_senhaProxy" size="30" value="<?php echo $_POST['ws_senhaProxy'];?>"/></td>
          <td class="exemplo">Senha para autentica&ccedil;&atilde;o no servidor Proxy</td>
          </tr>
        </table>
    </div>
    </td>
    <td>
    <div id="errosConxao">
    </div>
    </td>
    </tr>
    <tr>
    <td>
    	<div style="margin-left:30px;" align="center"><button id="bSalvarConfiguracao">1. Salvar Configura&ccedil;&otilde;es</button></div>
     </td>
    <td>
    	<div style="margin-left:30px;" align="center"><button id="bPortal">FIM. Acessar o Portal NFS-e</button></div>
     </td>
    </tr>
    </table>
    
</form>
</body>
<script>
var flagUtilizarBD=0;
	$(function() {
	  $('#aguarde').hide();
	  $("button, a").button();
	  $('#bPortal').button( "option", "disabled", true );

	  $('#bSalvarConfiguracao').click(function(){
			if(	$('#ws_codigoTom').val() == ""){
				alert("O Codigo TOM nao pode ser vazio!");
				return false;
			}
			if(	$('#ws_url').val() == ""){
				alert("A URL nao pode ser vazio!");
				return false;
			}
			if(	$('#ws_porta').val() == ""){
				alert("A Porta nao pode ser vazio!");
				return false;
			}
			if( $('#ws_cnpj').val() == ""){
				alert("O CNPJ nao pode ser vazio!");
				return false;
			}
			if( $('#ws_arquivoPFX').val() == ""){
				alert("O arquivo nao pode ser vazio!");
				return false;
			}

			if($('#ws_checkConexao').attr('checked')=='checked'){
				$('#ws_conexaoSegura').val("S");
			}else{
				$('#ws_conexaoSegura').val("N");
			}
			
			if($('#ws_checkProxy').attr('checked')==true){
			  $('#ws_proxy').val("S");
			  if( $('#ws_ipProxy').val() == ""){
				alert("O IP do Servidor Proxy nao por ser vazio!");
				return false;
			  }				
			  if( $('#ws_portaProxy').val() == ""){
				alert("A Porta do Servidor Proxy nao por ser vazia!");
				return false;
			  }				
			  if( $('#ws_usuarioProxy').val() == ""){
				alert("O Usuario do Servidor Proxy nao por ser vazio!");
				return false;
			  }				
			  if( $('#ws_senhaProxy').val() == ""){
				alert("A Senha do Servidor Proxy nao por ser vazia!");
				return false;
			  }
			}else{
			  $('#ws_proxy').val("N");
			}
		  $('#bSalvarConfiguracao').button( "option", "disabled", true );
		  $('#campos').val("ws_codigoTom,ws_url,ws_porta,ws_conexaoSegura,ws_cnpj,ws_arquivoPFX,ws_senha,ws_proxy,ws_ipProxy,ws_portaProxy,ws_usuarioProxy,ws_senhaProxy");
		  fChamarAjax('salvarConfigCidade', $('#formConfigWS').serialize());
		  return false;
	  });
	  
	  $('#ws_arquivo').change(function(){
		  if($('#ws_cnpj').val() == ""){
			  alert("Antes de selecionar o Certificado Digital eh necessario informar o CNPJ!");
			  $('#ws_arquivo').val("");
			  return false;
		  }
		  document.getElementById("formConfigWS").action = "Curitiba-PR.php";
		  $('#formConfigWS').submit();
		  return false;
	  });

	  $('#ws_checkConexao').change(function(){
		if($('#ws_checkConexao').attr('checked')=='checked'){
		  $('#ws_conexaoSegura').val("S");
		}else{
		  $('#ws_conexaoSegura').val("N");
		}
		return false;
	  });
	  
  	  $('#bPortal').click(function(){
		  window.parent.location.href = "/nfse/view/";
	  });
});

function fChamarAjax(parmFuncao, pData){
$('#aguarde').show();
  $.ajax({
	type: "POST",
	dataType: "json",
	url: "../funcoes.php",
	enctype: 'multipart/form-data',
	data: "funcao="+parmFuncao+"&"+pData+"&"+$('#file').attr('files')
  }).success(function( json ) {
	  $('#aguarde').hide();
	  switch(parmFuncao){
		  case "salvarConfigCidade":
			retornoSalvarConfigCidade(json);
		  break;
	  }
  })
  .fail(function(mensagem) {
	  $('#aguarde').hide();
   	  if(parmFuncao == "testarEmail"){
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>N&atilde;o foi poss&iacute;vel efetuar a conex&atilde;o com o servidor de email, verifique se todos os campos est&atilde;o corretos, como Email e Senha!</p></div>';
			$('#errosConexao').html(codigo);
			$('#bSalvarConfiguracao').button( "option", "disabled", false );
	  }else{
		   alert("Ops! ocorreu um erro ao chamar o PHP! \n\n"+mensagem);
	  }
   })

}

function retornoSalvarConfigCidade(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Sucesso! Agora voc&ecirc; j&aacute; est&aacute; pronto para usar o sistema!</p></div>';
			$('#bSalvarConfiguracao').button( "option", "disabled", true );
			$('#bPortal').button( "option", "disabled", false );
			$('#ws_url, #ws_usuario, #ws_senha, #ws_codigoTom').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bSalvarConfiguracao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
}

  </script>
</html>