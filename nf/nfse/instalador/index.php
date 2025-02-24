<?php
error_reporting(E_ALL);
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
    <link rel="stylesheet" href="../view/jquery/css/cupertino/jquery-ui-1.8.17.custom.css" />
    <script src="../view/jquery/js/jquery-1.7.1.min.js" type="text/javascript"></script>
    <script src="../view/jquery/js/jquery-ui-1.8.17.custom.min.js" type="text/javascript"></script>
    <title>NFS-e Softdib (v&beta;)</title>
  <link href="../../SpryAssets/SpryAccordion.css" rel="stylesheet" type="text/css" />
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
	img{ cursor:pointer; }

  </style>

<body style="position: relative; height:100%;">
<!---------------->
<div id="aguarde" style="position:fixed; left:0px; top:0px; padding:10px; background-color:#F90; width:100%"><b>AGUARDE ...</b></div>
<!---------------->
<div style="float:left; width:50%;"><p style="font-size:35px; color:#999">Instalador NFS-e da Softdib (v&beta;)</p></div>
<div align="right"><img src="../imagens/logoSoftdib50x60.png" height="100" /></div>
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
        <p>Abaixo ser&atilde;o apresentados t&oacute;picos verifica&ccedil;&atilde;o do sistema para a correta instala&ccedil;&atilde;o, verifique se h&aacute; ajuste necess&aacute;rio e preencha todos os campos quando solicitado solicitados!</p>
	</div>
</div>

    <p class="titulo">PHP<hr /></p>
    <div class="corpo">
        <table>
        <tr>
        <td>PHP Vers&atilde;o:</td>
        <?php
            if(substr(phpversion(),0,1) >= "5"){
              echo '<td>'.phpversion().'</td><td><span class="ui-icon ui-icon-check" style="float: left; margin-right: .3em;"></span></td>';
            }else{
              echo '<td>'.phpversion().'</td><td>';
              echo '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
                    <strong>Alerta:</strong> Para prosseguir com a instala&ccedil;&atilde;o o PHP deve ser 5+. Contate seu Suporte Tecnico!</p>
                </div>';
              echo '</td>';
            }
        ?>
        </tr>
		<tr>
        <td>Apache Vers&atilde;o:</td>
        <?php
				if(substr(apache_get_version(),7,1) >= "2"){
				  echo '<td>'.apache_get_version().'</td><td><span class="ui-icon ui-icon-check" style="float: left; margin-right: .3em;"></span></td>';
				}else{
				  echo '<td>'.apache_get_version().'</td><td>';
				  echo '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
						<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
						<strong>Alerta:</strong> Para prosseguir com a instala&ccedil;&atilde;o o Servidor Apache deve ser 2+. Contate seu Suporte Tecnico!</p>
					</div>';
				  echo '</td>';
				}
        ?>
        </tr>
        </table>        
    </div>
<!-------------------------------------------------------------------------------------------------------->
    <p class="titulo">MySql<hr /></p>
    <div class="corpo" id="divConexaoMySql">
        <table>
        <tr>
        <td>Conex&atilde;o:</td>
        <?php
            if(@mysqli_connect_errno()){
              echo '<td></td><td>';
              echo '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
                    <strong>Alerta:</strong> Erro de conex&atilde;o com o MySql. Contate seu Suporte Tecnico!</p>
                </div>';
              echo '</td>';
            }else{
              echo '<td>OK</td><td><span class="ui-icon ui-icon-check" style="float: left; margin-right: .3em;"></span></td>';
            }
        ?>
        </tr>

        <tr>
        <td>Cliente Vers&atilde;o:</td>
        <?php
			require_once('../model/adodb/adodb.inc.php');
			$ponteiro=NewADOConnection("mysqli");
			if(!$ponteiro){
              echo '<td>'.mysqli_get_client_version().'</td><td>';
              echo '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
                    <strong>Alerta:</strong> N&atilde;o foi poss&iacute;vel encontrar o Drive do MySql instalado. Contate seu Suporte Tecnico!</p>
                </div>';
              echo '</td>';

			}
            if(substr(mysqli_get_client_version(),0,1) >= "5"){
              echo '<td>'.mysqli_get_client_version().'</td><td><span class="ui-icon ui-icon-check" style="float: left; margin-right: .3em;"></span></td>';
            }else{
              echo '<td>'.mysqli_get_client_version().'</td><td>';
              echo '<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;"> 
                    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> 
                    <strong>Alerta:</strong> Para prosseguir com a instala&ccedil;&atilde;o o PHP deve ser 5+. Contate seu Suporte Tecnico!</p>
                </div>';
              echo '</td>';
            }
        ?>
        </tr>
        </table>
    </div>

<!---FORMULARIO DE CONFIGURACAO MYSQL----------------------------------------------------------------------------------->
<br /><br />
  <form action="" method="post" name="formTesteConexaoMySql" id="formTesteConexaoMySql">
  <table border="0" width="100%">
  	<tr>
    <td width="50%">
    <div class="corpo ui-widget-content" style="margin-left:30px;">
        <table cellpadding="5">
        <tr>
        	<td colspan="3" class="subtitulo">Configura&ccedil;&otilde;es de Conex&atilde;o</td>
        </tr>
        <tr>
          <td>Servidor</td>
          <td><input type="text" name="servidor" id="servidor" size="30" value="localhost"/></td>
          <td class="exemplo">Se estiver no mesmo servidor ser&aacute; <b>localhost</b></td>
        </tr>
        <tr>
          <td>Bando de Dados</td>
          <td><input type="text" name="banco" id="banco" size="30" value=""/><!-- mudar para nfse de default--></td>
          <td class="exemplo">Banco de Dados default <b>nfse</b></td>
        </tr>
        <tr>
          <td>Usu&aacute;rio</td>
          <td><input type="text" name="usuario" id="usuario" size="30" /></td>
          <td class="exemplo">Usu&aacute;rio default <b>root</b></td>
        </tr>
        <tr>
          <td>Senha</td>
          <td><input type="password" name="senha" id="senha" size="30" /></td>
          <td class="exemplo"></td>
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
    	<div style="margin-left:30px;" align="center"><button id="bTestarConexao">1. Testar Conex&atilde;o</button></div>
     </td>
     <td>
	    <span style="margin-left:30px;" align="center"><button id="bSalvarConexao">2. Salvar Conex&atilde;o</button></span>
		<span style="margin-left:30px;" align="center"><button id="bCriarTabelas">3. Criar Tabelas</button></span>
    </td>
    </tr>
    </table>
    
</form>


<!-------------------------------------------------------------------------------------------------------->
<br />
    <p class="titulo" id="pEmail">E-mail<hr /></p>
<div class="corpo">
        <table>
        <tr>
			<td colspan="3">
			<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0.7em;"> 
				Antes de efetuar os testes de email ceritifique-se que a fun&ccedil;&atilde;o MAIL ou SENDMAIL do PHP est&aacute; instalada <a href="info.php" target="_blank">PhpInfo</a>
			</div>
			</td>
		</tr>
        </tr>
        </table>
		
<!---FORMULARIO DE CONFIGURACAO EMAIL----------------------------------------------------------------------------------->
<br /><br />
<div id="divConfiguracaoEmail">
  <form action="" method="post" name="formTesteEmail" id="formTesteEmail">
  <table border="0" width="100%">
  	<tr>
    <td width="50%">
    <div class="corpo ui-widget-content" style="margin-left:30px;">
        <table cellpadding="5">
        <tr>
        	<td colspan="3" class="subtitulo">Configura&ccedil;&otilde;es de E-mail</td>
        </tr>
        <tr>
          <td>Servidor SMTP</td>
          <td><input type="text" name="smtp" id="smtp" size="30" value=""/></td>
          <td class="exemplo">Nome do servidor disponibilizado por seu administrador de email. Exemplo:<b>smpt.softdib.com.br</b></td>
        </tr>
		<tr>
          <td>Porta</td>
          <td><input type="text" name="porta" id="porta" size="5" value="587"/></td>
          <td class="exemplo">Porta Default: <b>587</b>. Antigos:<b> 25 </b><br />Verifique se o proveder exige alguma criptografia.</td>
        </tr>
        <tr>
          <td>Criptografia</td>
          <td><input type="radio" id="tls" name="tls" value="tls" />TLS
              <input type="radio" id="tls" name="tls" value="ssl" />SSL
              <input type="radio" id="tls" name="tls" value="" checked="checked"/>Nenhum
          </td>
          <td class="exemplo"></td>
        </tr>

        <tr>
          <td>Email</td>
          <td><input type="text" name="email" id="email" size="30"/></td>
          <td class="exemplo">Remetente do e-mail autom&aacute;tico. Exemplo:<b> noreply@softdib.com.br </b></td>
        </tr>
        <tr>
          <td>Senha</td>
          <td><input type="password" name="senhaEmail" id="senhaEmail" size="30" /></td>
          <td class="exemplo"></td>
        </tr>
        </table>
    </div>
    </td>
    <td>
    <div id="errosEmail">
    </div>
    </td>
    </tr>
    <tr>    <td>
    	<div style="margin-left:30px;" align="center"><button id="bTestarEmail">1. Testar Email</button></div>
     </td>
     <td>
	    <span style="margin-left:30px;" align="center"><button id="bSalvarEmail">2. Salvar Configura&ccedil;&otilde;es</button></span>
    </td>
    </tr>
    </table>
    
</form>
</div>
</div>



<!-------------------------------------------------------------------------------------------------------->
<br />
    <p class="titulo" id="pRelacionamento">Relacionamento Empresas<hr /></p>
<div class="corpo">

<!---FORMULARIO DE RELACIONAMENTO----------------------------------------------------------------------------------->
<br /><br />
  <form action="" method="post" name="formRelacionamento" id="formRelacionamento">
  	<input type="hidden" name="hIndice" id="hIndice" />
  	<input type="hidden" name="hIndiceExcluir" id="hIndiceExcluir" />
    <div class="corpo ui-widget-content" style="margin-left:30px;width:60%">
        <table cellpadding="5" width="100%" id="tableEmpresas">
        <tr>
        	<td colspan="5" class="subtitulo">Manuten&ccedil;&atilde;o dos Relacionamentos</td>
        </tr>
        <tr>
          <td align="center">Empresa Sistema</td>
          <td align="center">Filial Sistema</td>
          <td align="center"></td>
          <td align="center">Empresa Web <span class="exemplo">(cgi-bin)</span></td>
          <td align="center">Filial Web <span class="exemplo">(cgi-bin)</span></td>
          <td>&nbsp;</td>
        </tr>
        </table>
    </div>
    <div id="errosRelacionamento">
    </div>
</form>
</div>
<!-------------------------------------------------------------------------------------------------------->
<br />
    <p class="titulo">Web Service<hr /></p>
<div class="corpo" id="divConfiguracaoWebService">
        <table cellpadding="5">
        <tr>
			<td>
				Selecione sua Cidade
			</td>
			<td>

				<select id="cidade" name="cidade">
					<option id=""></option>
<?php
  $arquivos;
  if ($handle = opendir('configCidade/')) {
	/* Esta � a forma correta de varrer o diret�rio */
	while (false !== ($file = readdir($handle))) {
	  if($file != "." && $file != ".."){
		$file = explode(".",$file);
		$arquivos[] = $file[0];
	  }
	}
	sort($arquivos);
	foreach($arquivos as $val){
	  echo '<option value="'.$val.'">'.$val.'</option>';
	}
  }
?>
				</select>
			</td>
		</tr>
        </table>
	Caso n&atilde;o identifique sua cidade a vers&atilde;o do aplicativo n&atilde;o d&aacute; suporte para sua Cidade, contate a Softdib Inform&aacute;tica (41) 3276 6457
</div>
<br /><br />
<iframe id="configCidade" name="configCidade" frameborder="0" width="100%" height="400px;">
</iframe>
</body>
<script>
var flagUtilizarBD=0;
	$(function() {
	  $('#aguarde').hide();
	  $("button, a").button();
	  $('#divConfiguracaoEmail').hide();
	  $('#divConfiguracaoWebService').hide();
	  $('#bSalvarConexao, #bCriarTabelas, #bSalvarEmail').button( "option", "disabled", true );
	  $('#bTestarConexao').click(function(){
		if(	$('#servidor').val() == ""){
			alert("O nome do servidor nao pode ser vazio!");
			return false;
		}
		if( $('#banco').val() == ""){
		  alert("O nome do Bando de Dados nao pode ser vazio!");
		  return false;
		}
		if( $('#usuario').val() == ""){
		  alert("O nome do usuario nao pode ser vazio!");
		  return false;
		}
		if( $('#senha').val() == ""){
		  alert("A senha nao pode ser vazio!");
		  return false;
		}

		$('#bTestarConexao').button( "option", "disabled", true );
		fChamarAjax('testarConexao', $('#formTesteConexaoMySql').serialize());
		return false;
	  });

/*Funcao CLICK botao excluir Banco de Dados Existente*/
	  
	  $('#bSalvarConexao').click(function(){
		  $('#bTestarConexao').button( "option", "disabled", true );
		  if(flagUtilizarBD==0){
			fChamarAjax('salvarConexao', $('#formTesteConexaoMySql').serialize());
		  }else{
			fChamarAjax('salvarConexao2', $('#formTesteConexaoMySql').serialize());
		  }
		  return false;
	  });
	  
	  $('#bCriarTabelas').click(function(){
		  $('#bCriarTabelas').button( "option", "disabled", true );
		  fChamarAjax('criarTabelas', $('#formTesteConexaoMySql').serialize());
		  return false;
	  });
	  
	  $('#bTestarEmail').click(function(){
		  if($('#smtp').val() == ""){
			alert("O SMTP nao pode ser vazio!");
			return false;
		  }
		  if($('#porta').val() == ""){
			alert("A PORTA nao pode ser vazia!");
			return false;
		  }
		  if($('#email').val() == ""){
			alert("A EMAIL nao pode ser vazio!");
			return false;
		  }
		  if($('#senhaEmail').val() == ""){
			alert("A SENHA DO EMAIL nao pode ser vazia!");
			return false;
		  }

		  $('#bTestarEmail').button( "option", "disabled", true );
		  fChamarAjax('testarEmail', $('#formTesteEmail').serialize());
		  return false;
	  });
	});
	
	$('#bSalvarEmail').click(function(){
		$('#bSalvarConexao').button( "option", "disabled", true );
		  fChamarAjax('salvarEmail', $('#formTesteEmail').serialize());
		return false;
	});
	
	$('#cidade').change(function(){
	  if($('#cidade').val() != ""){
		document.getElementById('configCidade').src = "configCidade/"+$('#cidade').val()+".php";
	  }
	});

	
/*Funcao CLICK para Botao UtilizarBD */

function clickBotaoAdicionar(){
  $('#hIndice').val($('#bAdicionar').val());
  if($('#empresa'+$('#hIndice').val()).val() == ""){
	alert("A Empresa deve ser preenchida!");
	return false;
  }
  if($('#filial'+$('#hIndice').val()).val() == ""){
	alert("A Filial deve ser preenchida!");
	return false;
  }
  if($('#empresa_web'+$('#hIndice').val()).val() == ""){
	alert("A Empresa Web deve ser preenchida!");
	return false;
  }
  if($('#filial_web'+$('#hIndice').val()).val() == ""){
	alert("A Filial Web deve ser preenchida!");
	return false;
  }		
  
  $('#bAdicionar').button( "option", "disabled", true );
  fChamarAjax('adicionarRelacionamento', $('#formRelacionamento').serialize());
  return false;
}

function clickBotaoExcluirRelacionamento(parmIndice){
	$('#hIndiceExcluir').val(parmIndice);
	$('#bExcluir'+parmIndice).button( "option", "disabled", true );
	$('#empresa'+parmIndice).removeAttr("disabled");
	$('#filial'+parmIndice).removeAttr("disabled");
	fChamarAjax('excluirRelacionamento', $('#formRelacionamento').serialize());
	return false;
}

function clickBotaoUtilizarBD(){
	$('#bTestarConexao').button( "option", "disabled", true );
	fChamarAjax('utilizarBD', $('#formTesteConexaoMySql').serialize());
	return false;
}

function clickBotaoExcluirBD(){
	if(confirm("Voce tem certeza que deseja excluir o banco de dados e perder todas informacoes contidas nele?")){
		$('#bTestarConexao').button( "option", "disabled", true );
		fChamarAjax('excluirBD', $('#formTesteConexaoMySql').serialize());
		return false;
	}
	return false;
}

function fChamarAjax(parmFuncao, pData){
$('#aguarde').show();
  $.ajax({
	type: "POST",
	dataType: "json",
	url: "funcoes.php",
	data: "funcao="+parmFuncao+"&"+pData
  }).success(function( json ) {
	  $('#aguarde').hide();
	  switch(parmFuncao){
		  case "testarConexao":
			retornoTestarConexao(json);
		  break;
		  case "salvarConexao":
		  case "salvarConexao2":
			retornoSalvarConexao(json);
		  break;
		  case "criarTabelas":
			retornoCriarTabelas(json);
		  break;
		  case "utilizarBD":
			retornoUtilizarBD(json);
		  break;
		  case "excluirBD":
			retornoExcluirBD(json);
		  break;
		  case "testarEmail":
			retornoTestarEmail(json);
		  break;
		  case "salvarEmail":
		  	retornoSalvarEmail(json);
		  break;
		  case "adicionarRelacionamento":
		  	retornoRelacionamento(json);
		  break;
		  case "excluirRelacionamento":
		  	excluirRelacionamento(json);
		  break;
		  case "listarRelacionamento":
		  	retornoListarRelacionamentos(json);
		  break;		  
	  }
  })
  .fail(function(mensagem) {
	  $('#aguarde').hide();
   	  if(parmFuncao == "testarEmail"){
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>N&atilde;o foi poss&iacute;vel efetuar a conex&atilde;o com o servidor de email, verifique se todos os campos est&atilde;o corretos, como Email e Senha!</p></div>';
			$('#errosEmail').html(codigo);
			$('#bSalvarEmail, ').button( "option", "disabled", true );
			$('#bTestarEmail').button( "option", "disabled", false );
	  }else{
		   alert("Ops! ocorreu um erro ao chamar o PHP! \n\n"+mensagem);
	  }
   })

}

function retornoTestarConexao(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Conexao efetuada com sucesso!<br> Agora salve as configura&ccedil;&otilde;es do MySql para criar a base de dados!</p></div>';
			$('#bSalvarConexao').button( "option", "disabled", false );
			$('#servidor, #banco, #usuario, #senha').attr("readonly","readonly");
		break;
		case "01":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Este Banco de Dados '+$('#banco').val()+' ja existe na base! O que posso fazer?</p>'+
					'<p><b>Alterar o nome do BD criado e clicar em [1. Testar Conex&atilde;o]</b> ou</p>'+
					'<p>ou <button id="utilizarBD" onclick="clickBotaoUtilizarBD(); return false;">Utilizar o BD ja cadastrado</button></p>'+
					'<p>ou <button id="excluirBD" onclick="clickBotaoExcluirBD(); return false;">Excluir o BD existente e criar novo</button></p>'+
					'</div>';
					$('#bTestarConexao').button( "option", "disabled", false );
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bTestarConexao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
	$("button").button();
}

function retornoUtilizarBD(json){
	switch(json.codigo){
		case "00":
			flagUtilizarBD=1;
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>OK! Salve as configura&ccedil;&otilde;es do Sistema!</p></div>';
			$('#bSalvarConexao').button( "option", "disabled", false );
			$('#bCriarTabelas, #bTestarConexao').button( "option", "disabled", true );
			$('#servidor, #banco, #usuario, #senha').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
		break;
	}
	$('#errosConxao').html(codigo);
}

function retornoExcluirBD(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Muito bem, BD excluido com sucesso!<br> Salve as configuracoes para criar a nova base!</p></div>';
			$('#bSalvarConexao').button( "option", "disabled", false );
			$('#bCriarTabelas').button( "option", "disabled", true );
			$('#servidor, #banco, #usuario, #senha').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bTestarConexao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
}

function retornoSalvarConexao(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Muito bem, BD criado com sucesso!<br> Agora crie as tabelas do sistema! <br>Se as tabelas j&aacute; estiverem criadas ser&aacute;!</p></div>';
			$('#bSalvarConexao').button( "option", "disabled", true );
			$('#bCriarTabelas').button( "option", "disabled", false );
			$('#servidor, #banco, #usuario, #senha').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bTestarConexao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
}

function retornoCriarTabelas(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Perfeito, tabelas criadas com sucesso!<br> Prossiga com a instala&ccedil&atilde;o do Sistema!</p></div>';
			$('#bSalvarConexao, #bCriarTabelas').button( "option", "disabled", true );
			$('#servidor, #banco, #usuario, #senha').attr("readonly","readonly");
			$('#divConfiguracaoEmail').show();
			document.location.href = "./#pEmail";
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bTestarConexao').button( "option", "disabled", false );
		break;
	}
	$('#errosConxao').html(codigo);
}

function retornoTestarEmail(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Email enviado!<br> Para certificar que o envio de e-mail esta funcionando, verifique sua caixa de e-mail!</p></div>';
			$('#bTestarEmail').button( "option", "disabled", true );
			$('#bSalvarEmail, ').button( "option", "disabled", false);
			$('#smtp, #porta, #email, #senhaEmail').attr("readonly","readonly");
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bTestarEmail').button( "option", "disabled", false );
		break;
	}
	$('#errosEmail').html(codigo);
}

function retornoSalvarEmail(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Configura&ccedil;&otilde;es de email salvas!<br> Continue a instala&ccedil;&atilde;o!</p></div>';
			$('#bSalvarEmail').button( "option", "disabled", true );
			$('#smtp, #porta, #email, #senhaEmail').attr("readonly","readonly");
			listarRelacionamentos();
			$('#divConfiguracaoWebService').show();
			document.location.href = "./#pRelacionamento";
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bSalvarEmail').button( "option", "disabled", false );
		break;
	}
	$('#errosEmail').html(codigo);
}

function retornoRelacionamento(json){
	switch(json.codigo){
		case "00":
			codigo = '<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>Relacionamento adicionado!</div>';
			$('#empresa'+$('#hIndice').val()).attr("disabled","disabled");
			$('#filial'+$('#hIndice').val()).attr("disabled","disabled");
			$('#empresa_web'+$('#hIndice').val()).attr("disabled","disabled");
			$('#filial_web'+$('#hIndice').val()).attr("disabled","disabled");
			adicionar = '<button id="bExcluir'+$('#hIndice').val()+'" value="" onclick="javascript: clickBotaoExcluirRelacionamento(';
			adicionar += "'"+$('#hIndice').val()+"'";
			adicionar += '); return false;">Excluir</button>';
			$('#bAdicionar').after(adicionar);
			$('#bAdicionar').remove();
			$('#hIndice').val(Number($('#hIndice').val())+1);
			alert("Incluido com Sucessso!");
			$('#tableEmpresas').append('<tr id="linha'+$('#hIndice').val()+'">'+
			  '<td align="center"><input type="text" name="empresa'+$('#hIndice').val()+'" id="empresa'+$('#hIndice').val()+'" size="10" value=""/></td>'+
			  '<td align="center"><input type="text" name="filial'+$('#hIndice').val()+'" id="filial'+$('#hIndice').val()+'" size="10" value=""/></td>'+
			  '<td align="center"><img src="../imagens/arrow_right.png" /></td>'+
			  '<td align="center"><input type="text" name="empresa_web'+$('#hIndice').val()+'" id="empresa_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
			  '<td align="center"><input type="text" name="filial_web'+$('#hIndice').val()+'" id="filial_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
			  '<td align="center"><button id="bAdicionar" value="'+$('#hIndice').val()+'" onclick="javascript: clickBotaoAdicionar(); return false;">Adicionar</button></td>'+
			  '</tr>');
			$("button, a").button();
			/*
				Adicionar o bot�o de excluir (com value = ao numero da linha)
				Remover o bot�o de adicionar
				Adicionar mais uma linha
				Adicionar o bot�o de adicionar na outroa linha (com value = ao numero da linha)
			*/
		break;
		case "99":
			codigo = '<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;"> '+
        			'<p>'+json.mensagem+'</p></div>';
			$('#bSalvarEmail').button( "option", "disabled", false );
		break;
	}
	$('#errosRelacionamento').html(codigo);
}

function excluirRelacionamento(json){
  $('#linha'+json.indice).remove();
}

function retornoListarRelacionamentos(json){
	if(json.codigo == "99"){
	  $('#tableEmpresas').append('<tr id="linha1">'+
          '<td align="center"><input type="text" name="empresa'+$('#hIndice').val()+'" id="empresa'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><input type="text" name="filial'+$('#hIndice').val()+'" id="filial'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><img src="../imagens/arrow_right.png" /></td>'+
          '<td align="center"><input type="text" name="empresa_web'+$('#hIndice').val()+'" id="empresa_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><input type="text" name="filial_web'+$('#hIndice').val()+'" id="filial_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><button id="bAdicionar" value="'+$('#hIndice').val()+'" onclick="javascript: clickBotaoAdicionar(); return false;">Adicionar</button></td>'+
        '</tr>');

	  $("button, a").button();
	  return true;
	}

  $('#hIndice').val('1');
  $('#tableEmpresas').html('<tr>'+
        	'<td colspan="5" class="subtitulo">Configura&ccedil;&otilde;es de E-mail</td>'+
        '</tr>'+
        '<tr>'+
          '<td align="center">Empresa Sistema</td>'+
          '<td align="center">Filial Sistema</td>'+
          '<td align="center"></td>'+
          '<td align="center">Empresa Web <span class="exemplo">(cgi-bin)</span></td>'+
          '<td align="center">Filial Web <span class="exemplo">(cgi-bin)</span></td>'+
          '<td>&nbsp;</td>'+
        '</tr>');
if(json.empresas != undefined){
  $.each(json.empresas, function(i, item) {
	  adicionar = '<button id="bExcluir'+$('#hIndice').val()+'" value="" onclick="javascript: clickBotaoExcluirRelacionamento(';
	  adicionar += "'"+$('#hIndice').val()+"'";
	  adicionar += '); return false;">Excluir</button>';

	$('#tableEmpresas').append('<tr id="linha'+$('#hIndice').val()+'">'+
	  '<td align="center"><input type="text" name="empresa'+$('#hIndice').val()+'" id="empresa'+$('#hIndice').val()+'" size="10" value="'+json.empresas[i].empresa+'" disabled="disabled"/></td>'+
	  '<td align="center"><input type="text" name="filial'+$('#hIndice').val()+'" id="filial'+$('#hIndice').val()+'" size="10" value="'+json.empresas[i].filial+'" disabled="disabled"/></td>'+
	  '<td align="center"><img src="../imagens/arrow_right.png" /></td>'+
	  '<td align="center"><input type="text" name="empresa_web'+$('#hIndice').val()+'" id="empresa_web'+$('#hIndice').val()+'" size="10" value="'+json.empresas[i].empresaweb+'" disabled="disabled"/></td>'+
	  '<td align="center"><input type="text" name="filial_web'+$('#hIndice').val()+'" id="filial_web'+$('#hIndice').val()+'" size="10" value="'+json.empresas[i].filialweb+'" disabled="disabled"/></td>'+
	  '<td align="center">'+adicionar+'</td></tr>');
	  
	$('#hIndice').val(Number($('#hIndice').val())+1);
  });
}
  $('#tableEmpresas').append('<tr id="linha1">'+
          '<td align="center"><input type="text" name="empresa'+$('#hIndice').val()+'" id="empresa'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><input type="text" name="filial'+$('#hIndice').val()+'" id="filial'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><img src="../imagens/arrow_right.png" /></td>'+
          '<td align="center"><input type="text" name="empresa_web'+$('#hIndice').val()+'" id="empresa_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><input type="text" name="filial_web'+$('#hIndice').val()+'" id="filial_web'+$('#hIndice').val()+'" size="10" value=""/></td>'+
          '<td align="center"><button id="bAdicionar" value="'+$('#hIndice').val()+'" onclick="javascript: clickBotaoAdicionar(); return false;">Adicionar</button></td>'+
        '</tr>');

  $("button, a").button();
}

function listarRelacionamentos(){
  $('#bAdicionar').button( "option", "disabled", true );
  fChamarAjax('listarRelacionamento', $('#formRelacionamento').serialize());
  return false;
}


  </script>
</html>