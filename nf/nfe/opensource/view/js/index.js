/* Mudar toda a rotina do (loading) no padrao*/
$(document).ready(function(){
	$('#aguarde').show();
	$('#tableNfs tr:odd').addClass('zebraUm');
	$('#tableNfs tr:even').addClass('zebraDois');
	fLocalTela();
	fLocalEventoBotoes();
	fChamarAjax("INICIAR");
});
/*
	Função que trata todas as funções dos botões, button, img e outros que tiverem ações semelhante a botões.
*/
function fLocalEventoBotoes(){

  $('#bAtualizar')
	.click(function(){
	  fChamarAjax("INICIAR");
	  return false;
  });
  
  $('#aBackup')
	.click(function(){
	fChamarAjax("VER-BACKUP");
	$('#divBackUp').dialog('open'); 
	  return false;
  });
  
  $('#bGerar')
	.click(function(){
	$('#hNomeBackup').val($('#tNomeBackup').val());
	fChamarAjax("BACKUP");
	return false;
  });

  $('#imgLimpar')
	.click(function(){
	  $('#pesquisar').val("");
	  $('#pesquisar').trigger('keydown');
  });
  
  $('#imgPesquisar')
	.click(function(){
	  $('#pesquisar').trigger('keydown');
  });

  $('#bEnviarEmail')
	.click(function(){
	  $('#hEmail').val($('#tEmail').val());
	  fChamarAjax("ENVIAR-EMAIL");
	  return false;
	})
	.keydown(function(event){
	  if(event.keyCode == 13 || event.keyCode == 9){
		$('#bEnviarEmail').trigger('click');
		return false;
	  }
	});
  
}
	
/*
	Função para limpar a Tela
*/
function FLocalLimparTela(){
  $('#tValorDoc').val("");
  $('#tEmitente').focus();
}

function fLocalTela(){
  
  $('#set').buttonset();
  $('button').button();
  $('#divXml').dialog({
    autoOpen  : false,
    modal     : true,
    resizable : false,
    width     : ($(window).width()) * 0.75,
    height    : ($(window).height()) * 0.95
  });

  $('#divCriticas').dialog({
    autoOpen  : false,
    modal     : true,
    resizable : false,
    width     : ($(window).width()) * 0.60,
    height    : ($(window).height()) * 0.70
  });
  
  $('#divVisualizarNota').dialog({
    autoOpen  : false,
    modal     : true,
    resizable : false,
    width     : ($(window).width()) * 0.90,
    height    : ($(window).height()) * 0.90
  });
   

$('#divBackUp').dialog({
    autoOpen  : false,
    modal     : true,
    resizable : false,
    width     : ($(window).width()) * 0.60,
    height    : ($(window).height()) * 0.80
  });

  $('#divEmail').dialog({
    autoOpen  : false,
    modal     : true,
    resizable : false,
    width     : ($(window).width()) * 0.50,
    height    : ($(window).height()) * 0.25
  });

}

function fLocalXml(pEmpresa, pFilial, pNumControle, pIBGE){
  $('#divXml').dialog('open');
  $('#hEmpresa').val(pEmpresa);
  $('#hFilial').val(pFilial);
  $('#hControle').val(pNumControle);
  $('#hIBGE').val(pIBGE);
  fChamarAjax("VER-XML");
}

function fLocalCancelar(pEmpresa, pFilial, pNumControle, pIBGE){
  if(confirm("Deseja realmente cancelar esta Nota Fiscal?")){
	$('#hEmpresa').val(pEmpresa);
	$('#hFilial').val(pFilial);
	$('#hControle').val(pNumControle);
	$('#hIBGE').val(pIBGE);
	fChamarAjax("CANCELAR-NF");
  }
}

function fLocalImprimir(pLink, pCNPJ, pAutenticacao, pCidade){
  var html = "";
  if(pCidade == "004108304"){
	  html = '<br><table>'+
			  '  <tr>'+
			  '	<td>CNPJ do Prestador</td>'+
			  '	<td><b>'+pCNPJ+'</b></td>'+
			  ' </tr><tr>'+
			  '	<td>C&oacute;digo de Verifica&ccedil;&atilde;o</td>'+
			  '	<td><b>'+pAutenticacao+'</b></td>'+
			  '  </tr>'+
			  '</table><br>'+
			  'Informe os dados acima nos campos abaixo para visualizar a Nota Fiscal<br>';
	html += '<iframe id="iframeVisualizarNota" src="'+pLink+'" frameborder="0" width="100%" height="85%"></iframe>';
	$('#divVisualizarNota').html(html);
  }else{
	html = '<iframe id="iframeVisualizarNota" src="'+pLink+'" frameborder="0" width="100%" height="85%"></iframe>';
	$('#divVisualizarNota').html(html);
  }
$('#divVisualizarNota').dialog('open');
}

function fLocalCriticas(pEmpresa, pFilial, pNumControle){
	$('#divCriticas').dialog('open');
	$('#hEmpresa').val(pEmpresa);
	$('#hFilial').val(pFilial);
	$('#hControle').val(pNumControle);
	fChamarAjax("VER-CRITICAS");
}

function fLocalEmail(pEmpresa, pFilial, pControle, pEmail){
  $('#hEmpresa').val(pEmpresa);
  $('#hFilial').val(pFilial);
  $('#hControle').val(pControle);
  $('#tEmail').val(pEmail);
  $('#divEmail').dialog('open');
  $('#tEmail').select();
}

function fChamarAjax(parmFuncao){
$('#aguarde').show();
  $.ajax({
	type: "POST",
	dataType: "json",
	url: "portal.php",
	data: "funcao="+parmFuncao+"&"+$('#formPortal').serialize()
  }).success(function( json ) {
	  $('#aguarde').hide();
	  if(json.mensagemErro != undefined && json.mensagemErro != "" && parmFuncao != "INICIAR"){
		alert("Ops! Ocorreu um erro no PHP ->"+json.mensagemErro);
		return false;
	  }
	  switch(parmFuncao){
		  case "INICIAR":
			if(json.mensagemErro != undefined && json.mensagemErro != ""){
				alert(json.mensagemErro);
				document.location.href = "../instalador/";
				return false;
			}
			fLocalListarNFs(json);
			$('#aguarde').hide();
		  break;
		  case "VER-CRITICAS":
			fLocalRetCriticas(json);
			$('#aguarde').hide();
		  break;
		  case  "VER-XML":
			fLocalRetXml(json);
			$('#aguarde').hide();
		  break;
		  case "ENVIAR-EMAIL":
			alert(json.mensagem);
			FFecharJanela();
			$('#aguarde').hide();
		  break;
		  case "CANCELAR-NF":
			alert(json.mensagem);
			fChamarAjax("INICIAR");
			$('#aguarde').hide();
			return false;
		  break;
		  case "BACKUP":
			alert(json.mensagem);
			fChamarAjax("VER-BACKUP");
		  break;
		  case "VER-BACKUP":
		  	fLitarBackups(json);
		  break;
	  }
  })

}

function fLocalListarNFs(pJson){
	var html = "";
	
	if(pJson.mensagemErro == ""){
	  if(html == ""){
		html += '<tr>';
		html += '<td colspan="9">';
		html += "<b>N&atilde;o h&aacute; NFs a listar!</b>";
		html += '</td>';
		html += '</tr>';
		$('#tbodyNfs').html(html);
	  }
	  return false;
	}
	
	$.each(pJson.nf, function(i, item) {
		html += '<tr>';
		html += '<td>';
		html += fLocalImgSituacao(pJson.nf[i].status);
        html += '</td>';
        html += '<td>';
		html += pJson.nf[i].empresa;
		html += '</td>';
        html += '<td>';
		html += pJson.nf[i].filial;
		html += '</td>';
        html += '<td>';
		html += pJson.nf[i].controle;
		html += '</td>';
		html += '<td>';
		html += pJson.nf[i].numero;
		html += '/'+pJson.nf[i].serie;
		html += '</td>';
		html += '<td>';
		html += pJson.nf[i].cnpjtomador;
		html += '</td>';
		html += '<td>';
		html += '<div align="center">';
		html += pJson.nf[i].nomerazaosocialtomador;
		html += '</div>';
		html += '</td>';
		html += '<td>';
		html += pJson.nf[i].data+' - '+pJson.nf[i].hora;
		html += '</td>';
		html += '<td>';
		html += '<img src="../imagens/page_white.png" alt="Visualizar XML" title="Visualizar XML" onclick="javascript: fLocalXml(';
		html += "'"+pJson.nf[i].codEmpresa+"',";
		html += "'"+pJson.nf[i].codFilial+"',";
		html += "'"+pJson.nf[i].controle+"',";
		html += "'"+pJson.nf[i].codIBGE+"'";
		html += ');" height="15"/>&nbsp; &nbsp;';
		if(pJson.nf[i].status == "S"){
		  html += '<img src="../imagens/cross.png" alt="Cancelar NF" title="Cancelar NF" onclick="javascript: fLocalCancelar(';
		  html += "'"+pJson.nf[i].codEmpresa+"',";
		  html += "'"+pJson.nf[i].codFilial+"',";
		  html += "'"+pJson.nf[i].controle+"',";
		  html += "'"+pJson.nf[i].codIBGE+"'";
		  html += ');" height="15"/>&nbsp; &nbsp;';
		}else{
		  html += '<img src="../imagens/cross-out.png" alt="Cancelar NF indisponivel" title="Cancelar NF indisponivel" height="15"/>&nbsp; &nbsp;';
		}
		if(pJson.nf[i].link != "" && pJson.nf[i].link != undefined){
			localLink = pJson.nf[i].link;
			html += '<img src="../imagens/printer.png" alt="Visualizar/Imprimir" title="Visualizar/Imprimir" onclick="javascript: fLocalImprimir(';
			html += "'"+localLink+"',";
			html += "'"+pJson.nf[i].cnpjprestador+"',";
			html += "'"+pJson.nf[i].autenticacao+"',";
			html += "'"+pJson.nf[i].codIBGE+"'";
			html += ');" height="15"/>&nbsp; &nbsp;';
		}else{
			html += '<img src="../imagens/printer-out.png" alt="Visualizar/Imprimir indisponivel" title="Visualizar/Imprimir indisponivel" height="15"/>&nbsp; &nbsp;';
		}
		html += '<img src="../imagens/flag_yellow.png" alt="Ver Cr&iacute;ticas" title="Ver Cr&iacute;ticas" onclick="javascript: fLocalCriticas(';
		html += "'"+pJson.nf[i].codEmpresa+"',";
		html += "'"+pJson.nf[i].codFilial+"',";
		html += "'"+pJson.nf[i].controle+"'";
		html += ');" height="15"/>&nbsp; &nbsp;';
		if(pJson.nf[i].status == "S" || pJson.nf[i].status == "C"){
		  html += '<img src="../imagens/email.png" alt="Enviar Email" title="Enviar Email" onclick="javascript: fLocalEmail(';
		  html += "'"+pJson.nf[i].codEmpresa+"',";
		  html += "'"+pJson.nf[i].codFilial+"',";
		  html += "'"+pJson.nf[i].controle+"',";
		  html += "'"+pJson.nf[i].emailtomador+"'";
		  html += ');" height="15"/>&nbsp; ';
		}else{
		  html += '<img src="../imagens/email-out.png" alt="Email indisponivel" title="Email indisponivel" height="15"/>&nbsp; ';
		}
        html += '</td>';
        html += '</tr>';
	});
	
	$('#tbodyNfs').html(html);
	
	fLocalTableSorter();
}

function fLocalImgSituacao(pSituacao){
	switch(pSituacao){
		case "N":
		  return '<img src="../imagens/asterisk_orange.png" alt="Nova" title="Nova" height="15"/>';
		  break;
		case "S":
		  return '<img src="../imagens/accept.png" alt="Sucesso" title="Sucesso" height="15"/>';
		  break;
		case "E":
		  return '<img src="../imagens/error.png" alt="Falha" title="Falha" height="15"/>';
		  break;
		case "C":
		  return '<img src="../imagens/exclamation.png" alt="Cancelada" title="Cancelada" height="15"/>';
		  break;
	}
}

function fLocalRetCriticas(pJson){
  var html = "";
  $.each(pJson.cri, function(i, item) {
	html += '<tr class="geral_tabela_hover">';
	html += '<td width="80%">';
	html += '<div align="left">';
	html += pJson.cri[i].descricao;
	html += '</div>';
	html += '</td>';
	html += '<td width="20%">';
	html += pJson.cri[i].data;
	html += " - ";
	html += pJson.cri[i].hora;
	html += '</td>';
	html += '</tr>';
  });
  
  if(html == ""){
	html = '<tr><td colspan="2">N&atilde;o ha&aacute; cr&iacute;tica de valida&ccedil;&atilde;o</td></tr>';
  }

  $('#tableCriticas').html(html);
}

function fLocalRetXml(pJson){
	$('#aXML').html(pJson.xml);
}

function fLocalTableSorter(){
  $('#tableNfs > tbody > tr:odd').addClass('odd');
  
  $('#tableNfs > tbody > tr').hover(function(){
	$(this).toggleClass('hover');
  });
  
  $('#marcar-todos').click(function(){
	$('#tableNfs > tbody > tr > td > :checkbox')
	  .attr('checked', $(this).is(':checked'))
	  .trigger('change');
  });
  
  $('#tableNfs > tbody > tr > td > :checkbox').bind('click change', function(){
	var tr = $(this).parent().parent();
	if($(this).is(':checked')) $(tr).addClass('selected');
	else $(tr).removeClass('selected');
  });
  
  $('form').submit(function(e){ e.preventDefault(); });
  
  $('#pesquisar').keydown(function(){
	var encontrou = false;
	var termo = $(this).val().toLowerCase();
	$('#tableNfs > tbody > tr').each(function(){
	  $(this).find('td').each(function(){
		if($(this).text().toLowerCase().indexOf(termo) > -1) encontrou = true;
	  });
	  if(!encontrou) $(this).hide();
	  else $(this).show();
	  encontrou = false;
	});
  });
  
  $("#tableNfs")
	.tablesorter({
	  dateFormat: 'uk',
	  headers: {
/*            0: {
		  sorter: false
		},*/
		6: {
		  sorter: false
		}
	  }
	})
	.tablesorterPager({container: $("#pager")})
	.bind('sortEnd', function(){
	  $('#tableNfs > tbody > tr').removeClass('odd');
	  $('#tableNfs > tbody > tr:odd').addClass('odd');
	});
  
  $('#spanQtdePags').hide();
}

function fLitarBackups(pJson){
  var html="";
  $.each(pJson.backup, function(i, item) {
	  html += '<tr>'+
			  '<td width="34%">'+pJson.backup[i].nome+'</td>'+
			  '<td width="33%">'+pJson.backup[i].datahora+'</td>'+
			  '<td width="33%"><a href="'+pJson.backup[i].link+'" target="_new" style="cursor:pointer" title="Fazer Download">Fazer Download</a></td>'+
			  '</tr>';
  });
  $('#tDataHora').html(pJson.dataatual);
  $('#tableListaBackUp').html(html);
}

function FFecharJanela() {
  if($('#divXml').dialog('isOpen')){
    $('#divXml').dialog('close');
    return;
  }

  if($('#divCriticas').dialog('isOpen')){
    $('#divCriticas').dialog('close');
    return;
  }
  
  if($('#divBackup').dialog('isOpen')){
    $('#divBackup').dialog('close');
    return;
  }
  
  if($('#divEmail').dialog('isOpen')){
    $('#divEmail').dialog('close');
    return;
  }
  
  var localPID = $('#hPID').val();
  // pega a ultima posicao que indica em que aba esta
  var codigoTab = localPID.substr((localPID.length - 1), 1);
  parent.FFecharJanela(codigoTab);
}