<?php 
/**
	Autor: Fernando H. Crozetta
	Data: 31/03/2017
	Função para converter mdfe de formato txt para xml. 
*/
	require_once("../libs/ConvertMDFePHP.class.php");
	
	function mdfe2xml($arquivo_txt,$arquivo_xml)
	{
		$conteudo_arquivo = file_get_contents($arquivo_txt);
    	// Conversao de txt para xml
    	$ConvertMDFePHP = new ConvertMDFePHP();
    	$xml = $ConvertMDFePHP->MDFetxt2xml($conteudo_arquivo);
    	// Gravacao do xml em arquivo
    	file_put_contents($arquivo_xml, $xml);

		/* Verifica se deu erro ao converter o TXT em XML ou se o arquivo esta correto */
		if((!$xml) || $xml == ""){
		    $mensagem = "1|Erro ao converter o TXT em XML";
		    file_put_contents($arquivo_xml, $mensagem);
		    return false;
		}else{
			return true;
		}

	}
 ?>