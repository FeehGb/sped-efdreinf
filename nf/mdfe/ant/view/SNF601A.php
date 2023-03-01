<?php
//error_reporting(0);
/**
 * @name      	SNF
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Programa elaborado para efetuar comunicação do HTML com as classes control.
 * @TODO 		Fazer tudo
*/


	require_once("/var/www/html/nf/nfe/novo/control/CContribuinte.php");
	require_once("/var/www/html/nf/nfe/novo/control/CEmail.php");
	require_once("/var/www/html/nf/nfe/novo/control/CGerarDanfe.php");
	require_once("/var/www/html/nf/nfe/novo/control/CConsultaCadastro.php");
	require_once("/var/www/html/nf/nfe/novo/model/MNotaFiscal.php");
	require_once("/var/www/html/nf/nfe/novo/model/MInutilizacao.php");
	require_once("/var/www/html/nf/nfe/novo/model/MDestinadas.php");
	require_once("/var/www/html/nf/nfe/novo/libs/ToolsNFePHP.class.php");
	
	// Definição das variáveis
	$funcao = trim($_POST['hFuncao']);
	
	
	// Verificação qual grupo de chamadas irá carregar
	switch($funcao){
			case "NFE-LISTAR-CONTRIBUINTES":
				$CContribuinte = new CContribuinte(strtolower($_POST['hFilialGrupo']));
				// Instanciar a classe Contribuinte para trabalhar com o contribuinte
				$return = $CContribuinte->mObterTodos();
				$i=0;
				foreach($return as $conteudo){
					$retornoJson['retorno'][$i]['cnpj'] 			   	= $conteudo['cnpj'];
					$retornoJson['retorno'][$i]['ambiente'] 		 	= $conteudo['ambiente'];
					$retornoJson['retorno'][$i]['cod_emp_fil_softdib'] 	= $conteudo['cod_emp_fil_softdib'];
					$retornoJson['retorno'][$i]['razao_social'] 	   	= $conteudo['razao_social']; 
					$retornoJson['retorno'][$i]['contigencia'] 	   		= $conteudo['contigencia'];
					$i++;
				}

				$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
				
				echo json_encode($retornoJson);
				exit();
			break;
			case "CONTRIBUINTE-ACESSAR-INICIAL":
				$CContribuinte = new CContribuinte(strtolower($_POST['hFilialGrupo']));
				$CContribuinte->cnpj 		= $_POST['hCnpj'];
				$CContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return = $CContribuinte->mObterContribuinte();

				$certificado = fLocalVerificarCertificado($return[0]['certificado_caminho'],$return[0]['certificado_senha']);
				
				if(substr($certificado,0,1) == "F"){
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['fatal'] = $certificado;
					$retornoJson['retorno']['warning'] = "";
				}elseif(substr($certificado,0,1) == "W"){
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['warning'] = $certificado;
					$retornoJson['retorno']['fatal'] = "";
				}else{
					$certificado = substr($certificado,1);
					$retornoJson['retorno']['warning'] = "";
					$retornoJson['retorno']['fatal'] = "";
					$retornoJson['retorno']['success'] = $certificado;
				}

				$retornoJson['retorno']['contigencia']	= $return[0]['contigencia'];
				$retornoJson['retorno']['ambiente']		= $return[0]['ambiente'];
				$retornoJson['mensagem']				= $CContribuinte->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "NFE-LISTAR-DESTINADAS":
				$MDestinadas = new MDestinadas(strtolower($_POST['hFilialGrupo']));
				// Instanciar a classe MDestinadas para trabalhar com o contribuinte
				$return = $MDestinadas->select();
				if(!$return){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				$i=0;
				foreach($return as $conteudo){
					$retornoJson['retorno'][$i]['situacao_nfe']		= $conteudo['situacao_nfe'];
					$retornoJson['retorno'][$i]['emit_cpf_cnpj']	= $conteudo['emit_cpf_cnpj'];
					$retornoJson['retorno'][$i]['emit_nome'] 		= $conteudo['emit_nome'];
					$retornoJson['retorno'][$i]['dest_cpf_cnpj']	= $conteudo['dest_cpf_cnpj'];
					$retornoJson['retorno'][$i]['data_emissao']		= $conteudo['data_emissao'];
					$retornoJson['retorno'][$i]['valor_nf']			= $conteudo['valor_nf'];
					$retornoJson['retorno'][$i]['chave']			= $conteudo['chave'];
					$retornoJson['retorno'][$i]['numero']			= $conteudo['numero'];
					$retornoJson['retorno'][$i]['serie']			= $conteudo['serie'];
					$i++;
				}

				$retornoJson['mensagem'] = $CContribuinte->mensagemErro;
				
				echo json_encode($retornoJson);
				exit();
			break;
	}
	
	function fLocalVerificarCertificado($pCertificadoCaminho, $pCertificadoSenha){
		// Verificar validade do certificado
		$caminho = "/var/www/html/nf/nfse/certificados/";
		if($pCertificadoCaminho != ""){
			$ToolsNFePHP = new ToolsNFePHP();
			$dataCertificadoForm = $ToolsNFePHP->validadeCertificado($caminho.$pCertificadoCaminho,$pCertificadoSenha);
			$dataCertificado = "20".substr($dataCertificadoForm,6,2).substr($dataCertificadoForm,3,2).substr($dataCertificadoForm,0,2);
			$dataAtual = date('Ymd');
			$dataFutura = date('Ymd',strtotime('now + 30 days'));
			// Verifica se jah venceu
			if($dataCertificado <= $dataAtual){
				return "FCertificado Expirou em ".$dataCertificadoForm." vincule um novo certificado para emissao das Notas Fiscais";
			}
			if($dataFutura >= $dataCertificado){
				return "WAten&ccedil;&atilde;o o certificado digital expirar&aacute; em ".$dataCertificadoForm.".";
			}
		}
	}
?>