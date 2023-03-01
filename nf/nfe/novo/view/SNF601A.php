<?php
//error_reporting(0);
/**
 * @name      	SNF
 * @version   	alfa
 * @copyright	2013 &copy; Softdib
 * @author    	Guilherme Silva
 * @description Programa elaborado para efetuar comunica��o do HTML com as classes control.
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
	
	// Defini��o das vari�veis
	$funcao = trim($_POST['hFuncao']);
	$filialGrupo = str_replace(" ","",strtolower($_POST['hFilialGrupo']));
	
	// Verifica��o qual grupo de chamadas ir� carregar
	switch($funcao){
			case "NFE-LISTAR-CONTRIBUINTES":
				$CContribuinte = new CContribuinte($filialGrupo);
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
				$CContribuinte = new CContribuinte($filialGrupo);
				$CContribuinte->cnpj 		= $_POST['hCnpj'];
				$CContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return = $CContribuinte->mObterContribuinte();

				$certificado = fLocalVerificarCertificado($return[0]['certificado_caminho'],$return[0]['certificado_senha']);
				
				if(substr($certificado,0,1) == "F"){
					$retornoJson['retorno']['fatal'] = substr($certificado,1);
					$retornoJson['retorno']['warning'] = "";
				}elseif(substr($certificado,0,1) == "W"){
					$retornoJson['retorno']['warning'] = substr($certificado,1);
					$retornoJson['retorno']['fatal'] = "";
				}else{
					$retornoJson['retorno']['warning'] = "";
					$retornoJson['retorno']['fatal'] = "";
					$retornoJson['retorno']['success'] = substr($certificado,1);
				}
				
				$retornoJson['retorno']['contigencia']	= $return[0]['contigencia'];
				$retornoJson['retorno']['ambiente']		= $return[0]['ambiente'];
				$retornoJson['mensagem']				= $CContribuinte->mensagemErro;
				echo json_encode($retornoJson);
				exit();
			break;
			case "NFE-LISTAR-DESTINADAS":
				$MDestinadas = new MDestinadas($filialGrupo);
				$MDestinadas->ambiente 	= $_POST['hAmbiente'];
				$sql = "";
				
				if(trim($_POST['tPeriodoIni']) != "" && trim($_POST['tPeriodoFin']) != ""){
					$periodoIni = substr($_POST['tPeriodoIni'],6,4)."-".substr($_POST['tPeriodoIni'],3,2)."-".substr($_POST['tPeriodoIni'],0,2);
					$periodoFim = substr($_POST['tPeriodoFin'],6,4)."-".substr($_POST['tPeriodoFin'],3,2)."-".substr($_POST['tPeriodoFin'],0,2);
					$sql[] = " ( data_emissao >= '$periodoIni' AND data_emissao <= '$periodoFim' ) ";
				}
				if(trim($_POST['tCNPJ']) != ""){
					$sql[] = " emit_cpf_cnpj LIKE '%".$_POST['tCNPJ']."%' ";
				}
				if(trim($_POST['tRazaoSocial']) != ""){
					$sql[] = " emit_nome LIKE '%".$_POST['tRazaoSocial']."%' ";
				}
				if(trim($_POST['tSituacao']) != ""){
					$sql[] = " situacao_nfe LIKE '".$_POST['tSituacao']."' ";
				}
				if(trim($_POST['tManifestacao']) != ""){
					$sql[] = " confirmacao LIKE '".$_POST['tManifestacao']."' ";
				}
				
				if($sql != ""){
					$sql = implode(" AND ",$sql);
					$sql = "SELECT * FROM nfe_".$filialGrupo.".NF_DESTINADAS WHERE ".$sql;
				}
				
				// Instanciar a classe MDestinadas para trabalhar com o contribuinte
				$return = $MDestinadas->select($sql);
				if(!$return){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}
				if(is_array($return)){
					$i=0;
					foreach($return as $conteudo){
						$retornoJson['retorno'][$i]['situacao_nfe']				= $conteudo['situacao_nfe'];
						$retornoJson['retorno'][$i]['confirmacao']				= $conteudo['confirmacao'];
						$retornoJson['retorno'][$i]['emit_cpf_cnpj']			= $conteudo['emit_cpf_cnpj'];
						$retornoJson['retorno'][$i]['emit_nome'] 				= $conteudo['emit_nome'];
						$retornoJson['retorno'][$i]['dest_cpf_cnpj']			= $conteudo['dest_cpf_cnpj'];
						$date = new DateTime($conteudo['data_hora_recebimento']);
						$retornoJson['retorno'][$i]['data_hora_recebimento']	= $date->format('d/m/Y H:i:s');
						$retornoJson['retorno'][$i]['valor_nf']					= $conteudo['valor_nf'];
						$retornoJson['retorno'][$i]['chave']					= $conteudo['chave'];
						$retornoJson['retorno'][$i]['numero']					= substr($conteudo['chave'],22,3);
						$retornoJson['retorno'][$i]['serie']					= ltrim(substr($conteudo['chave'],25,9),0);
						$i++;
					}
				}else{
					$retornoJson['retorno'] = "F";
					$retornoJson['mensagem'] = "N�o h� registros a listar";
				}

				echo json_encode($retornoJson);
				exit();
			break;
			case "NFE-ATUALIZAR-SEFAZ":
				// Verifica se eh possivel efetuar a chamada ao SEFAZ
				$CContribuinte = 			new CContribuinte($filialGrupo);
				$CContribuinte->cnpj 		= $_POST['hCnpj'];
				$CContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return 					= $CContribuinte->mObterContribuinte();

				if($return[0]['consulta_destinatario'] == "N"){
					// Verificar se faz mais de 1 hora da ultima chamada, caso sim permite chamar novamente. (regra sefaz)
					$dataContribuinte = strtotime($return[0]['n']);
					$dataAtual = strtotime(date('Y-m-d H:i:s'));
					$qtdeHoras = (($dataAtual - $dataContribuinte) / 60 ) / 60;
					if($qtdeHoras < 1){
						$retornoJson['retorno'] = false;
						$retornoJson['mensagem'] = "Aguarde 1 hora para nova consulta. Exigencia SEFAZ.";
						echo json_encode($retornoJson);
						exit();
					}
				}
				
				$MDestinadas = new MDestinadas($filialGrupo);
				// Obter o ultimo NSU consultado no SEFAZ
				$sql = "SELECT MAX(nsu) as ultNsu FROM nfe_".$filialGrupo.".NF_DESTINADAS WHERE dest_cpf_cnpj ='".$_POST['hCnpj']."' AND ambiente='".$_POST['hAmbiente']."'";
				$return = $MDestinadas->select($sql);
				if(!$return){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$ultNSU = $return[0]['ultNsu'] == "NULL" || $return[0]['ultNsu'] == "" ? "" : $return[0]['ultNsu'];
				if($_POST['hNSU'] != ""){
					$ultNSU = $_POST['hNSU'];
				}

				// Instanciar o ToolsNFePHP para chamar o SEFAZ
				$ToolsNFePHP = new ToolsNFePHP($_POST['hCnpj'], $_POST['hAmbiente'],$filialGrupo, 2);
				if(!$ToolsNFePHP){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}

				$indCont=1;
				$aRetorno="";
				unset($aRetorno);
				unset($retorno);

				$retorno = $ToolsNFePHP->getListNFe(true,'0','0',$ultNSU,$tpAmb,'2',$aRetorno);
				if(!$retorno){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}
				$indCont = $aRetorno['indCont'];
				$ultNSU = $aRetorno['nsu'];
				
				if(is_array($aRetorno['NFe'])){
					foreach($aRetorno['NFe'] as $conteudo){
						isset($MDestinadas);
						$MDestinadas = new MDestinadas($filialGrupo);
						$MDestinadas->nsu					= $conteudo['NSU'];
						$MDestinadas->ambiente				= $_POST['hAmbiente'];
						$MDestinadas->tipo					= "A";
						$MDestinadas->chave					= $conteudo['chNFe'];
						$MDestinadas->emit_cpf_cnpj			= $conteudo['CNPJ'];
						$MDestinadas->emit_nome				= $conteudo['xNome'];
						$MDestinadas->dest_cpf_cnpj			= $_POST['hCnpj'];
						$MDestinadas->data_emissao			= $conteudo['dEmi'];
						$MDestinadas->tipo_nota				= $conteudo['tpNF'];
						$MDestinadas->valor_nf				= $conteudo['vNF'];
						$MDestinadas->digest_value			= $conteudo['digVal'];
						$MDestinadas->data_hora_recebimento	= $conteudo['dhRecbto'];
						$MDestinadas->situacao_nfe			= $conteudo['cSitNFe'];
						$MDestinadas->confirmacao			= $conteudo['cSitConf'];
						$MDestinadas->data_hora_evento		= "";
						$MDestinadas->tp_evento				= "";
						$MDestinadas->seq_evento			= "0";
						$MDestinadas->desc_evento			= "";
						$MDestinadas->correcao				= "";
						if(!$MDestinadas->insert()){
							$retornoJson['retorno'] = false;
							$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}
					}
				}
				
				if(is_array($aRetorno['Canc'])){
					foreach($aRetorno['Canc'] as $conteudo){
						isset($MDestinadas);
						$MDestinadas = new MDestinadas($filialGrupo);
						$MDestinadas->nsu					= $conteudo['NSU'];
						$MDestinadas->ambiente				= $_POST['hAmbiente'];
						$MDestinadas->tipo					= "C";
						$MDestinadas->chave					= $conteudo['chNFe'];
						$MDestinadas->emit_cpf_cnpj			= $conteudo['CNPJ'];
						$MDestinadas->emit_nome				= $conteudo['xNome'];
						$MDestinadas->dest_cpf_cnpj			= $_POST['hCnpj'];
						$MDestinadas->data_emissao			= $conteudo['dEmi'];
						$MDestinadas->tipo_nota				= $conteudo['tpNF'];
						$MDestinadas->valor_nf				= $conteudo['vNF'];
						$MDestinadas->digest_value			= $conteudo['digVal'];
						$MDestinadas->data_hora_recebimento	= $conteudo['dhRecbto'];
						$MDestinadas->situacao_nfe			= $conteudo['cSitNFe'];
						$MDestinadas->confirmacao			= $conteudo['cSitConf'];
						$MDestinadas->data_hora_evento		= "";
						$MDestinadas->tp_evento				= "";
						$MDestinadas->seq_evento			= "0";
						$MDestinadas->desc_evento			= "";
						$MDestinadas->correcao				= "";
						if(!$MDestinadas->insert()){
							$retornoJson['retorno'] = false;
							$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}
					}
				}
				
				if(is_array($aRetorno['CCe'])){
					foreach($aRetorno['CCe'] as $conteudo){
						isset($MDestinadas);
						$MDestinadas = new MDestinadas($filialGrupo);
						$MDestinadas->nsu					= $conteudo['NSU'];
						$MDestinadas->ambiente				= $_POST['hAmbiente'];
						$MDestinadas->tipo					= "CC";
						$MDestinadas->chave					= $conteudo['chNFe'];
						$MDestinadas->emit_cpf_cnpj			= "";//$conteudo['CNPJ'];
						$MDestinadas->emit_nome				= "";//$conteudo['xNome'];
						$MDestinadas->dest_cpf_cnpj			= $_POST['hCnpj'];
						$MDestinadas->data_emissao			= "";//$conteudo['dEmi'];
						$MDestinadas->tipo_nota				= $conteudo['tpNF'];
						$MDestinadas->valor_nf				= "";//$conteudo['vNF'];
						$MDestinadas->digest_value			= "";//$conteudo['digVal'];
						$MDestinadas->data_hora_recebimento	= $conteudo['dhRecbto'];
						$MDestinadas->situacao_nfe			= $conteudo['cSitNFe'];
						$MDestinadas->confirmacao			= $conteudo['cSitConf'];
						$MDestinadas->data_hora_evento		= $conteudo['dhEvento'];
						$MDestinadas->tp_evento				= $conteudo['tpEvento'];
						$MDestinadas->seq_evento			= $conteudo['nSeqEvento'];
						$MDestinadas->desc_evento			= $conteudo['descEvento'];
						$MDestinadas->correcao				= $conteudo['certificadosxCorrecao'];
						if(!$MDestinadas->insert()){
							$retornoJson['retorno'] = false;
							$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
							echo json_encode($retornoJson);
							exit();
						}
					}
				}
				
				if(strpos($aRetorno['retorno'], "Nenhum documento localizado") === false){
					if($indCont==1){
						$retornoJson['continua'] = "S";
						$retornoJson['nsu'] = $aRetorno['nsu'];
					}else{
						$retornoJson['continua'] = "N";
					}      
				}else{
					$retornoJson['continua'] = "N";
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = "Nenhum documento localizado para o destinatario";
				}
				echo json_encode($retornoJson);
				exit();
			break;
			case "NFE-DOWNLOAD-XML":
				// Verifica se eh possivel efetuar a chamada ao SEFAZ
				$CContribuinte 				= new CContribuinte($filialGrupo);
				$CContribuinte->cnpj 		= $_POST['hCnpj'];
				$CContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return 					= $CContribuinte->mObterContribuinte();
				
				$chNFe = (string) $_POST['hChave'];

	/*			if($return[0]['consulta_destinatario'] == "N"){
					// Verificar se faz mais de 1 hora da ultima chamada, caso sim permite chamar novamente. (regra sefaz)
					$dataContribuinte = strtotime($return[0]['consulta_destinatario_hora']);
					$dataAtual = strtotime(date('Y-m-d H:i:s'));
					$qtdeHoras = (($dataAtual - $dataContribuinte) / 60 ) / 60;
					if($qtdeHoras < 1){
						$retornoJson['retorno'] = false;
						$retornoJson['mensagem'] = "Aguarde 1 hora para nova consulta. Exigencia SEFAZ.";
						echo json_encode($retornoJson);
						exit();
					}
				}
				
				$MDestinadas = new MDestinadas($filialGrupo);
				// Obter o ultimo NSU consultado no SEFAZ
				$sql = "SELECT MAX(nsu) as ultNsu FROM nfe_".$filialGrupo.".NF_DESTINADAS WHERE dest_cpf_cnpj ='".$_POST['hCnpj']."' AND ambiente='".$_POST['hAmbiente']."'";
				$return = $MDestinadas->select($sql);
				if(!$return){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
					echo json_encode($retornoJson);
					exit();
				}

				$ultNSU = $return[0]['ultNsu'] == "NULL" || $return[0]['ultNsu'] == "" ? "" : $return[0]['ultNsu'];
				if($_POST['hNSU'] != ""){
					$ultNSU = $_POST['hNSU'];
				}
*/
				// Instanciar o ToolsNFePHP para chamar o SEFAZ
				$ToolsNFePHP = new ToolsNFePHP($_POST['hCnpj'], $_POST['hAmbiente'],$filialGrupo, 2,false,false,true);
				if(!$ToolsNFePHP){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}

				$aRetorno="";
				unset($aRetorno);
				unset($retorno);

				$retorno = $ToolsNFePHP->getNFe(true,$chNFe, '','2',$aRetorno);
				
				if(!$retorno){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}
				
				$caminho = "/var/www/nfe/recebe/";
				//$caminho2 = "/user/relaltorios/xml/";
				if(file_put_contents($caminho.$aRetorno['nome'],$aRetorno['xml'])){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = "Nao foi possivel criar o arquivo [".$caminho."]\nVerifique a permissao do diretorio!";
				}
				/*if(file_put_contents($caminho2.$aRetorno['nome'],$aRetorno['xml'])){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = "Nao foi possivel criar o arquivo [".$caminho2."]\nVerifique a permissao do diretorio!";
				}*/
				
				$retornoJson['retorno'] = true;
				$retornoJson['mensagem'] = "Arquivo criado em [/".$caminho."]\nPara continuar a importacao para o sistema acesse o programa SGM875.";
				echo json_encode($retornoJson);
				exit();
			break;
			case "NFE-MANIFESTAR":
				if(trim($_POST['rManifestar']) == "" || trim($_POST['rManifestar']) == "undefined"){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = "Selecione um tipo de Manifestacao!";
					echo json_encode($retornoJson);
					exit();
				}
				if($_POST['rManifestar'] == "210240" && trim($_POST['hMotivoOperacao']) == ""){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = "O campo Motivo da Operacao deve ser preechida quando selecionado Operacao nao Realizada!";
					echo json_encode($retornoJson);
					exit();
				}

				// Verifica se eh possivel efetuar a chamada ao SEFAZ
				$CContribuinte 				= new CContribuinte($filialGrupo);
				$CContribuinte->cnpj 		= $_POST['hCnpj'];
				$CContribuinte->ambiente 	= $_POST['hAmbiente'];
				$return 					= $CContribuinte->mObterContribuinte();
				
				$chNFe 		= (string) $_POST['hChave'];
				$tpEvento 	= $_POST['rManifestar'];
				$xJust 		= trim($_POST['hMotivoOperacao']);

				// Instanciar o ToolsNFePHP para chamar o SEFAZ
				$ToolsNFePHP = new ToolsNFePHP($_POST['hCnpj'], $_POST['hAmbiente'],$filialGrupo, 2,false,false, true);
				if(!$ToolsNFePHP){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}

				$aResp="";
				unset($aResp);
				unset($retorno);

				$retorno = $ToolsNFePHP->manifDest($chNFe,$tpEvento,$xJust,$_POST['hAmbiente'],'2',$aResp);

				if(!$retorno){
					$retornoJson['retorno'] = false;
					$retornoJson['mensagem'] = $ToolsNFePHP->errMsg;
					echo json_encode($retornoJson);
					exit();
				}

				if($aResp['nProt'] != ""){
					//update nProt na tabela de Destinadas
					$MDestinadas = new MDestinadas($filialGrupo);
					$MDestinadas->chave = $chNFe;
					switch($tpEvento){
						case "210200": //Confirma
							$MDestinadas->confirmacao = "1";
						break;
						case "210220": //Desconhece
							$MDestinadas->confirmacao = "2";
						break;
						case "210240": // N�o realizada
							$MDestinadas->confirmacao = "3";
						break;
						case "210210": // Ci�ncia
							$MDestinadas->confirmacao = "4";
						break;
					}
					
					$MDestinadas->data_hora_confirmacao = $aResp['dhRegEvento'];
					$MDestinadas->protocolo_confirmacao = $aResp['nProt'];
					
					// Inserir para atualizar status de retorno
					$return = $MDestinadas->update();
					if(!$return){
						$retornoJson['retorno'] = false;
						$retornoJson['mensagem'] = $MDestinadas->mensagemErro;
						echo json_encode($retornoJson);
						exit();
					}
				}
				$retornoJson['retorno'] = true;
				$retornoJson['mensagem'] = $aResp['xMotivo'];
				echo json_encode($retornoJson);
				exit();
			break;
	}
	
	function fLocalVerificarCertificado($pCertificadoCaminho, $pCertificadoSenha){
		global $filialGrupo;
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
//				return "WAten&ccedil;&atilde;o o certificado digital expirar&aacute; em ".$dataCertificadoForm.".";
			}else{
				return "";
			}
		}
	}
?>