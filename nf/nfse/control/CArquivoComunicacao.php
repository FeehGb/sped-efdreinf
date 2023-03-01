<?php

	error_reporting(0);

	/*
		Classe:					CArquivoComunicacao.php
		Autor:					Guilherme Silva
		Data:					01/02/2012
		Finalidade: 			Efetuar o tratamento para comunicacao via arquivo
		Programas xpamadores: 	Integrador
		Programas chamados: 	envio.txt, retorno.txt, CEmpresa, CNF, CGenerico, CItens, CProduto
	*/

	require_once("/var/www/html/nf/nfse/control/CXml.php");
	require_once("/var/www/html/nf/nfse/model/CCritica.php");
	
	class CArquivoComunicacao{

		//Atributos
		public $xml;
		public $mensagemErro;
		
		//Obter os dados do arquivo de entrada e gravar no banco de dados para efetuar o tratamento
		public function efetuarEntradaArquivo($pArquivoEntrada=""){
			//Identifica veracidade do arquivo
			if($pArquivoEntrada == ""){
				$this->mensagemErro = "Classe: CArquivoComunicacao.php - efetuarEntradaArquivo() {parametro do metodo nao eh opcional}";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n Classe: CArquivoComunicacao.php -> parametro do metodo nao eh opcional \n\n", FILE_APPEND);
				return false;
			}
			if(!file_exists($pArquivoEntrada)){
				$this->mensagemErro = "O arquivo ".$pArquivoEntrada." nao existe";
				file_put_contents("/var/tmp/nfse.log",date("d/m/Y ; G:i:s ;")." \n Classe: CArquivoComunicacao.php -> O arquivo [".$pArquivoEntrada."] nao existe \n\n", FILE_APPEND);
				return false;
			}
			// Obter o arquivo XML em array de String
			$xml = file_get_contents($pArquivoEntrada);

			// Remover acentos e caracteres especiais // ticket 24114
			//$xml = strtr(utf8_decode($xml),
			//utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ!?'),'SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy  ');
			// Remover aspas duplas e aspas simples
			$xml = str_replace("'","",$xml);
			$xml = str_replace('"','',$xml);

			if(!$xml){
				$this->mensagemErro = "Houve erro na leitura do arquivo ".$pArquivoEntrada." verifique as permissoes do arquivo";
				file_put_contents("/var/tmp/nfse.log", date("d/m/Y ; G:i:s ;")." \n Classe: CArquivoComunicacao.php -> Houve erro na leitura do arquivo ".$pArquivoEntrada." verifique as permissoes do arquivo \n\n", FILE_APPEND);
				return false;
			}/*else{
//			  unlink($pArquivoEntrada);
			}*/

			$xml = simplexml_load_string(utf8_encode($xml), 'SimpleXMLElement', LIBXML_NOCDATA);
			if(!$xml){
			  $this->mensagemErro = "Houve erro na conversao do arquivo ".$pArquivoEntrada." em XML, verifique se o XML esta correto";
				file_put_contents("/var/tmp/nfse.log", date("d/m/Y ; G:i:s ;")." \n Classe: CArquivoComunicacao.php -> Houve erro na conversao do arquivo ".$pArquivoEntrada." em XML, verifique se o XML esta correto \n\n", FILE_APPEND);
			  return false;
			}
			$this->xml = $xml;
			return true;
		}

		//Retornar os dados gerados pela comunicação no arquivo de retorno
		public function gravarArquivoRetorno($pArqRetorno, $pNumeroNota, $pNumeroControle, $pStatus, $pCriticas, $pEmpresa, $pFilial, $pNroRps, $pCodigoVerificacao, $protocolo){
			$saidaArquivo = "";
			$saidaArquivo .= $pNumeroNota."|".$pNumeroControle."|".$pStatus."|";
			if(is_array($pCriticas)){

			}else{
				$saidaArquivo .= $pCriticas;
			}

			$saidaArquivo .= "|".$pNroRps."|".$pCodigoVerificacao."|".$protocolo."|";

			file_put_contents($pArqRetorno, $saidaArquivo);

	
		}

	}
?>