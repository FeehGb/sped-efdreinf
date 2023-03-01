<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
	<?php //include("../../../padrao/head.php"); ?>
		<!-- Adicionar neste local arquivos de SCRIPT (javascript js, vb, as) e CSS -->
		<!--
      exemplo de como adicionar um arquivo de css
      <link rel="stylesheet" href="caminho do arquivo" />

      exemplo de como adicionar um arquivo de script
      <script src="caminho do arquivo" type="tipo do script"></script>
    -->
    <link rel="shortcut icon" href="../imagens/softdib.ico" >
    <link rel="stylesheet" href="jquery/css/cupertino/jquery-ui-1.8.17.custom.css" />
    <script src="jquery/js/jquery-1.7.1.min.js" type="text/javascript"></script>
    <script src="jquery/js/jquery-ui-1.8.17.custom.min.js" type="text/javascript"></script>
    <script src="jquery/js/jquery.tablesorter.min.js" type="text/javascript"></script>
    <script src="jquery/js/jquery.tablesorter.pager.js" type="text/javascript"></script>
    <script src="js/index.js" type="text/javascript"></script>
    <style>

body{
	font-family:Verdana, Geneva, sans-serif;
	font-size:11px;
}
#tableNfs{
  width:100%;
  border:1px solid #ccc;
}

#tableNfs thead th{
  font-weight:bold;
  padding:10px;
  text-align:center;
}

#tableNfs thead th.header{
  cursor:pointer;
}

#tableNfs tbody td{
  padding:6px;
  text-align:center;
  color:#333;
}

#tableNfs tbody tr.odd td{
  background-color:#F9F9F9;
}

#tableNfs tbody tr.hover td{
  background-color:#FFFFAA;
}

.pagedisplay{
  width:40px;
  text-align:center;
  vertical-align:top;
  border:none;
  /*background-color:#f2f2f2;*/
  font-weight:bold;
}

#pager span{
  font-weight:bold;
  display:inline-block;
/*  color:#666;*/
  float:right;
}

#pager{
  text-align:right;
  padding:5px;
  padding-bottom:8px;
}

#divNavegacao{
	padding-right:10px;
}

.statusFalha{
	background:#FD2;
}

img{
	border:0px;
	margin:0px;
}

.classEmpFil{
	width:400px;
	height:20px;
}

#aXML{
	width:99%;
	height:400px;
	font-family:"Courier New", Courier, monospace;
}

input{
  text-transform:none;
}

</style>
  </head>
  <body>
<!---------------->
<div id="aguarde" style="position:fixed; left:0px; top:0px; padding:10px; background-color:#F90; width:100%"><b>AGUARDE ...</b></div>
<!---------------->
  <form name="formPortal" id="formPortal" action="">
	    <input type="hidden" id="hEmpresa" name="hEmpresa" />
	    <input type="hidden" id="hFilial" name="hFilial" />
	    <input type="hidden" id="hControle" name="hControle" />
        <input type="hidden" id="hIBGE" name="hIBGE" />
   	    <input type="hidden" id="hEmail" name="hEmail" />
  </form>
      <div style="margin:0; padding:5px; padding-left:15px; padding-right:15px;">
      <table id="tableNfs" name="tableNfs" summary="Lista Notas Fiscais de Servi&ccedil;o">
      <thead>
      	<tr class="ui-widget-header" style="cursor:pointer">
        	<th width="1%"></th>
        	<th width="12,5%">Empresa</th>
        	<th width="12,5%">Filial</th>
        	<th width="6%">Controle</th>
        	<th width="9%">Nº NF / S&eacute;rie</th>
        	<th width="9%">Cliente CNPJ</th>
        	<th width="17%"><div align="center">Cliente Raz&atilde;o Social</div></th>
        	<th width="12%">Emiss&atilde;o</th>
        	<th width="12%"></th>
        </tr>
       </thead>
       <tbody id="tbodyNfs" name="tbodyNfs">
<!--      	<tr>
        	<td>
             <img src="../../../imagens/icons/asterisk_orange.png" alt="Nova" title="Nova" height="15"/>
            </td>
            <td>
            101520
            </td>
	      	<td>
				102030
            </td>

            <td>
			152030
            </td>
            <td>
<div align="left">Raz&atilde;o Social</div>
            </td>
            <td>
			12/12/2011 - 11:16
            </td>
            <td>
              <img src="../../../imagens/icons/page_white.png" alt="Visualizar XML" title="Visualizar XML" onclick="javascript: fLocalXml('101520');" height="15"/>
              &nbsp;
              <img src="../../../imagens/icons/cross.png" alt="Cancelar NF" title="Cancelar NF" onclick="javascript: fLocalCancelar('101520');" height="15"/>
              &nbsp;
              <img src="../../../imagens/icons/printer.png" alt="Visualizar/Imprimir" title="Visualizar/Imprimir" onclick="javascript: fLocalImprimir('101520');" height="15"/>
              &nbsp;
              <img src="../../../imagens/icons/flag_yellow.png" alt="Ver Cr&iacute;ticas" title="Ver Cr&iacute;ticas" onclick="javascript: fLocalCriticas('101520');" height="15"/>
              &nbsp;              
              <img src="../../../imagens/icons/email.png" alt="Enviar Email" title="Enviar Email" onclick="javascript: fLocalEmail('101520');" height="15"/>
              &nbsp;              
            </td>
        </tr> -->
       </tbody>
      </table>
<div id="pager" class="pager ui-widget-header">
  <div align="left" style="float:left">
    <form method="post" action="index.php" id="frm-filtro">
      <input type="text" id="pesquisar" name="pesquisar" size="30" />
      <img id="imgPesquisar" src="../imagens/find_icon.png" alt="Pesqusar" title="Pesquisar"/>
      <img id="imgLimpar" src="../imagens/arrow_refresh_small.png" alt="Limpar Pesquisa" title="Limpar Pesquisa"/>
    </form>
  </div>
<form>
  <span id="spanQtdePags">
  Exibir
  <select class="pagesize">
  <option selected="selected" value="10">10</option>
  <option value="20">20</option>
  <option value="30">30</option>
  <option value="40">40</option>
  </select> registros
  </span>
  <div id="divNavegacao">
   <img src="../imagens/resultset_first.png" class="first"/>
   <img src="../imagens/resultset_previous.png" class="prev"/>
   <input type="text" class="pagedisplay" readonly="readonly"/>
   <img src="../imagens/resultset_next.png" class="next"/>
   <img src="../imagens/resultset_last.png" class="last"/>
   </div>
 </form>
</div>
<br />
<table>
  <button id="bAtualizar" name="bAtualizar">
  <img id="imgLimpar" src="../imagens/arrow_refresh.png" alt="Limpar Pesquisa" title="Limpar Pesquisa"/>
  Atualizar
  </button>

</div>

<div id="divXml">
  <textarea id="aXML"></textarea>
</div>
      
<div id="divCriticas">
  <br />
  <div align="center">
    <table cellpadding="5" width="100%">
      <tr class="ui-widget-header">
          <td width="80%">
              Cr&iacute;tica
          </td>
          <td width="20%" align="center">
              Data/Hora
          </td>
      </tr>
    </table>
    <div style="overflow:auto; height:250px; width:100%;">
      <table cellpadding="5" width="100%" id="tableCriticas">
      </table>
    </div>
  </div>
</div>

<div id="divEmail">
  <div align="center">
  <br />
  <table cellpadding="5" width="80%">
  	<tr>
    	<td width="20%">
        	Email
        </td>
    	<td>
        	<input type="text" name="tEmail" id="tEmail" size="50"/>
            <button id="bEnviarEmail" name="bEnviarEmail">Enviar</button>
        </td>
    </tr>
  </table>
  </div>
</div>

    <script language="javascript">
//      enterAsTab(document.forms.fSNF801);
    </script>
  </body>
</html>