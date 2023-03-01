<?php
/* Programa para converter a base de dados antiga para nova */

include("CBd.php");

$pBaseAntiga = "rmk";
$pBaseNova = "nfse_pneuplus";
$pGrupo = "pneuplus";

// FAZ A CONVERSAO DA TABELA DE CRITICAS

$CBd = CBd::singleton($pGrupo);
$ponteiro = $CBd->getPonteiro();
$ponteiro->BeginTrans();

$sql = "SELECT * FROM ".$pBaseAntiga.".critica";

$recordSet = $ponteiro->Execute($sql);
$ponteiro->CommitTrans();

while ($array = $recordSet->FetchRow()) {
	$cnpj = "";
	if($array['codigo_empresa'] == "4" && $array['codigo_filial'] == "1"){
		$cnpj = "07918416000132";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "1"){
		$cnpj = "13779747000131";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "2"){
		$cnpj = "13779747000212";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "3"){
		$cnpj = "13779747000301";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "4"){
		$cnpj = "13779747000484";
	}
	
	$sql2 = "INSERT INTO ".$pBaseNova.".critica VALUES ('".$cnpj."', '".$array['numero_controle']."', '".$array['descricao']."', '".$array['data']."', '".$array['hora']."');";

	$ponteiro->BeginTrans();
	$rs = $ponteiro->Execute($sql2);
	$ponteiro->CommitTrans();
	
}


// FAZ A CONVERSAO DA TABELA DE ITEM

$CBd = CBd::singleton($pGrupo);
$ponteiro = $CBd->getPonteiro();
$ponteiro->BeginTrans();

$sql = "SELECT * FROM ".$pBaseAntiga.".item";

$recordSet = $ponteiro->Execute($sql);
$ponteiro->CommitTrans();

while ($array = $recordSet->FetchRow()) {
	$cnpj = "";
	if($array['codigo_empresa'] == "4" && $array['codigo_filial'] == "1"){
		$cnpj = "07918416000132";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "1"){
		$cnpj = "13779747000131";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "2"){
		$cnpj = "13779747000212";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "3"){
		$cnpj = "13779747000301";
	}elseif($array['codigo_empresa'] == "5" && $array['codigo_filial'] == "4"){
		$cnpj = "13779747000484";
	}
	
	$sql2 = "INSERT INTO ".$pBaseNova.".item VALUES ('".
		"'".$cnpj."',".
		"'".$array['numero_controle']."',".
		"'".$array['tributa_municipio_prestador']."',".
		"'".$array['codigo_local_prestacao_servico']."',".
		"'".$array['unidade_codigo']."',".
		"'".$array['unidade_quantidade']."',".
		"'".$array['unidade_valor_unitario']."',".
		"'".$array['codigo_item_lista_servico']."',".
		"'".$array['descritivo']."',".
		"'".$array['aliquota_item_servico']."',".
		"'".$array['situacao_tributaria']."',".
		"'".$array['valor_tributavel']."',".
		"'".$array['valor_deducao']."',".
		"'".$array['valor_iss']."',".
		"'".$array['valor_issrf']."',".
		"'".$array['desconto_cond']."',".
		"'".$array['desconto_incond']."',".
		"'".$array['codigo_cnae']."',".
		"'".$array['codigo_tributacao_municipio']."',".
		"'".$array['valor_csll']."',".
		"'".$array['outras_retencoes']."',".
		"'".$array['situacaotributaria']."',".
		."');";

	$ponteiro->BeginTrans();
	$rs = $ponteiro->Execute($sql2);
	$ponteiro->CommitTrans();
	
}



// FAZ A CONVERSAO DA TABELA NOTA FISCAL
$CBd = CBd::singleton($pGrupo);
$ponteiro = $CBd->getPonteiro();
$ponteiro->BeginTrans();

$sql = "SELECT * FROM ".$pBaseAntiga.".nota_fiscal";

$recordSet = $ponteiro->Execute($sql);
$ponteiro->CommitTrans();

while ($array = $recordSet->FetchRow()) {

$sql2 = "INSERT INTO ".$pBaseNova.".nota_fiscal VALUES (".
	"'".$array['empresa_codigo']."',".
	"'".$array['empresa_descricao']."',".
	"'".$array['filial_codigo']."',".
	"'".$array['filial_descricao']."',".
	"'".$array['nf_lote']."',".
	"'".$array['nf_numero']."',".
	"'".$array['nf_situacao']."',".
	"'".$array['nf_tipo']."',".
	"'".$array['nf_valor_total']."',".
	"'".$array['nf_valor_desconto']."',".
	"'".$array['nf_valor_ir']."',".
	"'".$array['nf_valor_inss']."',".
	"'".$array['nf_valor_contribuicao_social']."',".
	"'".$array['nf_valor_rps']."',".
	"'".$array['nf_valor_pis']."',".
	"'".$array['nf_valor_cofins']."',".
	"'".$array['nf_observacao']."',".
	"'".$array['prestador_cpf_cnpj']."',".
	"'".$array['prestador_cidade']."',".
	"'".$array['prestador_inscricao_municipal']."',".
	"'".$array['prestador_cnae']."',".
	"'".$array['prestador_optante_simples']."',".
	"'".$array['prestador_incentivador_cultural']."',".
	"'".$array['tomador_tipo']."',".
	"'".$array['tomador_identificador']."',".
	"'".$array['tomador_estado']."',".
	"'".$array['tomador_pais']."',".
	"'".$array['tomador_cpf_cnpj']."',".
	"'".$array['tomador_ie']."',".
	"'".$array['tomador_inscricao_municipal']."',".
	"'".$array['tomador_nome_razao_social']."',".
	"'".$array['tomador_sobrenome_nome_fantasia']."',".
	"'".$array['tomador_logradouro']."',".
	"'".$array['tomador_email']."',".
	"'".$array['tomador_numero_residencia']."',".
	"'".$array['tomador_complemento']."',".
	"'".$array['tomador_ponto_referencia']."',".
	"'".$array['tomador_bairro']."',".
	"'".$array['tomador_cidade']."',".
	"'".$array['tomador_cep']."',".
	"'".$array['tomador_ddd_fone_comercial']."',".
	"'".$array['tomador_fone_comercial']."',".
	"'".$array['tomador_ddd_fone_residencial']."',".
	"'".$array['tomador_fone_residencial']."',".
	"'".$array['tomador_ddd_fax']."',".
	"'".$array['tomador_fone_fax']."',".
	"'".$array['nf_controle']."',".
	"'".$array['produtos_descricao']."',".
	"'".$array['produtos_valor_total']."',".
	"'".$array['nf_serie']."',".
	"'".substr($array['nf_data_emissao'],6,4)."-".substr($array['nf_data_emissao'],3,2)."-".substr($array['nf_data_emissao'],0,2)."',".
	"'".$array['nf_hora_emissao']."',".
	"'".$array['nf_status']."',".
	"'".$array['nf_link']."',".
	"'".$array['nf_autenticacao']."',".
	"'".$array['nf_protocolo']."',".
	"'".$array['nf_regime_especial']."')".


	$ponteiro->BeginTrans();
	$rs = $ponteiro->Execute($sql2);
	$ponteiro->CommitTrans();
	
}



?>