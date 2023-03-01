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
<!---FORMULARIO DE CONFIGURACAO MYSQL----------------------------------------------------------------------------------->
  <form action="" method="post" name="formConfigWS" id="formConfigWS">
  <input type="hidden"  name="campos" id="campos" />
  <table border="0">
  	<tr>
    <td>
    <div class="corpo ui-widget-content" style="margin-left:30px;">
        <table cellpadding="5">
        <tr>
        	<td colspan="3" class="subtitulo">Configura&ccedil;&otilde;s do Web Service</td>
        </tr>
        <tr>
          <td>URL Servidor</td>
          <td colspan="2"><input type="text" name="ws_url" id="ws_url" size="100" value="http://www.atende.net/datacenter/include/nfw/importa_nfw/nfw_import_upload.php?eletron=1"/></td>
        </tr>
        <tr>
          <td>Usu&aacute;rio</td>
          <td><input type="text" name="ws_cnpj" id="ws_cnpj" size="30" value=""/><!-- mudar para nfse de default--></td>
          <td class="exemplo">Para teste <b>74174174000107</b></td>
        </tr>
        <tr>
          <td>Senha</td>
          <td><input type="password" name="ws_senha" id="ws_senha" size="30" /></td>
          <td class="exemplo">Para teste <b>apenasteste</b></td>
        </tr>
        <tr>
          <td>C&oacute;digo TOM</td>
          <td><input type="text" name="ws_codigoTom" id="ws_codigoTom" size="30" /></td>
          <td class="exemplo">C&oacute;digo da Cidade do prestador cadastrado na Refeita Federal. Para teste <b>54534</b></td>
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
    	<div style="margin-left:30px;" align="center"><button id="bSalvarConexao">1. Salvar Conex&atilde;o</button></div>
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

	  $('#bSalvarConexao').click(function(){
			if(	$('#ws_url').val() == ""){
				alert("A URL nao pode ser vazio!");
				return false;
			}
			if( $('#ws_cnpj').val() == ""){
				alert("O usuario nao pode ser vazio!");
				return false;
			}
			if( $('#ws_senha').val() == ""){
				alert("A senha nao pode ser vazio!");
				return false;
			}
			if( $('#ws_codigoTom').val() == ""){
				alert("O codigo TOM nao pode ser vazio!");
				return false;
			}

		  $('#bSalvarConexao').button( "option", "disabled", true );
		  $('#campos').val("ws_url,ws_cnpj,ws_senha,ws_codigoTom");
		  fChamarAjax('salvarConfigCidade', $('#formConfigWS').serialize());
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
	data: "funcao="+parmFuncao+"&"+pData
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
			$('#bSalvarConexao').button( "option", "disabled", false );
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
			$('#bSalvarConexao').button( "option", "disabled", true );
			$('#bPortal').button( "option", "disabled", false );
			$('#ws_url, #ws_cnpj, #ws_senha, #ws_codigoTom').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bSalvarConexao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
}

  </script>
</html>