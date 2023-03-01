
<?php
	//error_reporting(0);

	require_once("UnConvertNFePHP.class.php");
	require_once("UnConvertCTePHP.class.php");
	//$diretorioEnt = './xml/';
	//$diretorioEnt = '../../'.$_POST['tCaminhoXmlEntrada'].'/';
	$diretorioEnt = '../../../nfe/recebe/';
	//$diretorioEnt = '/user/';
	//$diretorioEnt = '../../recebe/nfe/';
	//$diretorioSaida = '../../../'.$_POST['hCaminhoXmlSaida'].'/'; gjps 16/10/2014
	//$diretorioSaida = $_POST['hCaminhoXmlSaida'].'/';

	if(is_dir($diretorioEnt))
	{
		$arquivos = dir($diretorioEnt);
		$d = scandir($diretorioEnt);

		$funcao = $_POST['hFuncao'];
		$arquivo = $_POST['hNomeArquivo'];
		$data = $_POST['hData'];
		$notaFiscal = $_POST['hNotaFiscal'];
		$serie = $_POST['hSerie'];
		$valor = $_POST['hValorNotaFiscal'];
		$tipo_xml = $_POST['hTipoXML'];

		$cnpj_empresa = $_POST['hCnpjCpfEstabelecimento'];
		$cnpj_fornecedor = $_POST['tCnpjCpfFornecedor'];

		$qtdeFile = 1;

		switch($funcao)
		{
			case "ENFE-LISTAR-NOTAS":

				for($i = 0, $linha = 0; $i < count($d); $i++)
				{
					if($d[$i] != "." && $d[$i] != ".." && $d[$i] != "")
					{
						$conteudo_file = file_get_contents($diretorioEnt.$d[$i]);

						$xml = @simplexml_load_string($conteudo_file);

						//echo $cnpj_fornecedor.":".$cnpj_empresa."\n";

						if($cnpj_fornecedor != "" && $cnpj_empresa != "")
						{
							if($cnpj_fornecedor == $xml->NFe->infNFe->emit->CNPJ && $cnpj_empresa == $xml->NFe->infNFe->dest->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];

								if($xml->NFe->infNFe->ide->dEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dEmi;
								else if($xml->NFe->infNFe->ide->dhEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dhEmi;
								else
									$retorno[$linha]["ide_dEmi"] = "00000000";

								$retorno[$linha]["emit_xNome"] = $xml->NFe->infNFe->emit->xNome;
								$retorno[$linha]["emit_enderEmit_xMun"] = $xml->NFe->infNFe->emit->enderEmit->xMun;
								$retorno[$linha]["ide_nNF"] = $xml->NFe->infNFe->ide->nNF;
								$retorno[$linha]["ide_serie"] = $xml->NFe->infNFe->ide->serie;
								$retorno[$linha]["total_ICMSTot_vNF"] = $xml->NFe->infNFe->total->ICMSTot->vNF;

								$qtdeFile++;
								$linha++;
							}

							if($cnpj_fornecedor == $xml->CTe->infCte->rem->CNPJ || $cnpj_empresa == $xml->CTe->infCte->dest->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								
								$retorno[$linha]["ide_nCT"] = $xml->CTe->infCte->ide->nCT;
								$retorno[$linha]["ide_serie"] = $xml->CTe->infCte->ide->serie;
	
								$qtdeFile++;
								$linha++;
							}
						}
						else if($cnpj_fornecedor != "")
						{

							//echo "cnpj2:#".$xml->CTe->infCte->rem->CNPJ."#";

							if($cnpj_fornecedor == $xml->NFe->infNFe->emit->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];

								if($xml->NFe->infNFe->ide->dEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dEmi;
								else if($xml->NFe->infNFe->ide->dhEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dhEmi;
								else
									$retorno[$linha]["ide_dEmi"] = "00000000";

								$retorno[$linha]["emit_xNome"] = $xml->NFe->infNFe->emit->xNome;
								$retorno[$linha]["emit_enderEmit_xMun"] = $xml->NFe->infNFe->emit->enderEmit->xMun;
								$retorno[$linha]["ide_nNF"] = $xml->NFe->infNFe->ide->nNF;
								$retorno[$linha]["ide_serie"] = $xml->NFe->infNFe->ide->serie;
								$retorno[$linha]["total_ICMSTot_vNF"] = $xml->NFe->infNFe->total->ICMSTot->vNF;

								$qtdeFile++;
								$linha++;
							}

							

							if($cnpj_fornecedor == $xml->CTe->infCte->rem->CNPJ || $cnpj_empresa == $xml->CTe->infCte->dest->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								
								$retorno[$linha]["ide_nCT"] = $xml->CTe->infCte->ide->nCT;
								$retorno[$linha]["ide_serie"] = $xml->CTe->infCte->ide->serie;
	
								$qtdeFile++;
								$linha++;
							}

						}
						else if($cnpj_empresa != "")
						{
							//echo "cnpj:".$cnpj_empresa."=".$xml->CTe->infCte->dest->CNPJ."\n";
							//echo "cnpj:".$cnpj_empresa."=".$xml->CTe->infCte->rem->CNPJ."\n";



							if($cnpj_empresa == $xml->NFe->infNFe->dest->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];
								$retorno[$linha]["tipo_xml"] = "nfe";
								
								if($xml->NFe->infNFe->ide->dEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dEmi;
								else if($xml->NFe->infNFe->ide->dhEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dhEmi;
								else
									$retorno[$linha]["ide_dEmi"] = "00000000";

								$retorno[$linha]["emit_xNome"] = $xml->NFe->infNFe->emit->xNome;
								$retorno[$linha]["emit_enderEmit_xMun"] = $xml->NFe->infNFe->emit->enderEmit->xMun;
								$retorno[$linha]["ide_nNF"] = $xml->NFe->infNFe->ide->nNF;
								$retorno[$linha]["ide_serie"] = $xml->NFe->infNFe->ide->serie;
								$retorno[$linha]["total_ICMSTot_vNF"] = $xml->NFe->infNFe->total->ICMSTot->vNF;

								$qtdeFile++;
								$linha++;
							}

							
							//echo "cnpj:".$cnpj_empresa."=".$xml->CTe->infCte->rem->CNPJ."\n";


							if($cnpj_empresa == $xml->CTe->infCte->rem->CNPJ || $cnpj_empresa == $xml->CTe->infCte->dest->CNPJ)
							{

								//echo $cnpj_empresa.":".$xml->CTe->infCte->dest->CNPJ." dest \n";
								//echo $cnpj_empresa.":".$xml->CTe->infCte->rem->CNPJ."rem \n";

								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];
								$retorno[$linha]["tipo_xml"] = "cte";


		
								//echo "|teste".$xml->NFe->infNFe["Id"][0].$xml->CTe->infCte->ide->dhEmi[0]."teste|";

								if($xml->CTe->infCte->ide->dhEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->CTe->infCte->ide->dhEmi;
								else
									$retorno[$linha]["ide_dEmi"] = "00000000";


								$retorno[$linha]["emit_xNome"] = $xml->CTe->infCte->emit->xNome;

								$retorno[$linha]["emit_enderEmit_xMun"] = $xml->CTe->infCte->emit->enderEmit->xMun;
								$retorno[$linha]["total_ICMSTot_vNF"] = $xml->CTe->infCte->vPrest->vTPrest;

								
								$retorno[$linha]["ide_nNF"] = $xml->CTe->infCte->ide->nCT;
								$retorno[$linha]["ide_serie"] = $xml->CTe->infCte->ide->serie;
	
								$qtdeFile++;
								$linha++;

								//print_r($retorno); 
							}
						}
						else
						{
							$retorno[0]["qtde_arquivos"] = $qtdeFile;
							$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];
							
							if($xml->NFe->infNFe->ide->dEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dEmi;
							else if($xml->NFe->infNFe->ide->dhEmi != "")
								$retorno[$linha]["ide_dEmi"] = $xml->NFe->infNFe->ide->dhEmi;
							else
								$retorno[$linha]["ide_dEmi"] = "00000000";

							$retorno[$linha]["emit_xNome"] = $xml->NFe->infNFe->emit->xNome;
							$retorno[$linha]["emit_enderEmit_xMun"] = $xml->NFe->infNFe->emit->enderEmit->xMun;
							$retorno[$linha]["ide_nNF"] = $xml->NFe->infNFe->ide->nNF;
							$retorno[$linha]["ide_serie"] = $xml->NFe->infNFe->ide->serie;
							$retorno[$linha]["total_ICMSTot_vNF"] = $xml->NFe->infNFe->total->ICMSTot->vNF;

							$qtdeFile++;
							$linha++;
						}
					}
				}



				
				$arquivos->close();
			break;

			case "ENFE-VISUALIZAR-DANFE":

				$xml = simplexml_load_file($arquivo);

				$retorno[0]["nf_id"] = $xml->NFe->infNFe["Id"][0];
				$retorno[0]["ide_dEmi"] = $xml->NFe->infNFe->ide->dhEmi;
				$retorno[0]["ide_nNF"] = $xml->NFe->infNFe->ide->nNF;
				$retorno[0]["ide_serie"] = $xml->NFe->infNFe->ide->serie;
				$retorno[0]["ide_cUF"] = $xml->NFe->infNFe->ide->cUF;
				$retorno[0]["ide_natOp"] = $xml->NFe->infNFe->ide->natOp;
				$retorno[0]["ide_tpNF"] = $xml->NFe->infNFe->ide->tpNF;
				$retorno[0]["ide_dSaiEnt"] = $xml->NFe->infNFe->ide->dhSaiEnt;
				//$retorno[0]["ide_hSaiEnt"] = $xml->NFe->infNFe->ide->hSaiEnt;
				$retorno[0]["emit_CNPJ"] = $xml->NFe->infNFe->emit->CNPJ;
				$retorno[0]["emit_xNome"] = $xml->NFe->infNFe->emit->xNome;
				$retorno[0]["emit_xFant"] = $xml->NFe->infNFe->emit->xFant;
				$retorno[0]["emit_enderEmit_xLgr"] = $xml->NFe->infNFe->emit->enderEmit->xLgr;
				$retorno[0]["emit_enderEmit_nro"] = $xml->NFe->infNFe->emit->enderEmit->nro;
				$retorno[0]["emit_enderEmit_xBairro"] = $xml->NFe->infNFe->emit->enderEmit->xBairro;
				$retorno[0]["emit_enderEmit_cMun"] = $xml->NFe->infNFe->emit->enderEmit->cMun;
				$retorno[0]["emit_enderEmit_xMun"] = $xml->NFe->infNFe->emit->enderEmit->xMun;
				$retorno[0]["emit_enderEmit_UF"] = $xml->NFe->infNFe->emit->enderEmit->UF;
				$retorno[0]["emit_enderEmit_CEP"] = $xml->NFe->infNFe->emit->enderEmit->CEP;
				$retorno[0]["emit_enderEmit_cPais"] = $xml->NFe->infNFe->emit->enderEmit->cPais;
				$retorno[0]["emit_enderEmit_xPais"] = $xml->NFe->infNFe->emit->enderEmit->xPais;
				$retorno[0]["emit_enderEmit_fone"] = $xml->NFe->infNFe->emit->enderEmit->fone;
				$retorno[0]["emit_IE"] = $xml->NFe->infNFe->emit->IE;
				$retorno[0]["emit_CRT"] = $xml->NFe->infNFe->emit->CRT;	
				$retorno[0]["dest_CNPJ"] = $xml->NFe->infNFe->dest->CNPJ;
				$retorno[0]["dest_xNome"] = $xml->NFe->infNFe->dest->xNome;
				$retorno[0]["dest_enderDest_xLgr"] = $xml->NFe->infNFe->dest->enderDest->xLgr;
				$retorno[0]["dest_enderDest_nro"] = $xml->NFe->infNFe->dest->enderDest->nro;
				$retorno[0]["dest_enderDest_xCpl"] = $xml->NFe->infNFe->dest->enderDest->xCpl;
				$retorno[0]["dest_enderDest_xBairro"] = $xml->NFe->infNFe->dest->enderDest->xBairro;
				$retorno[0]["dest_enderDest_cMun"] = $xml->NFe->infNFe->dest->enderDest->cMun;
				$retorno[0]["dest_enderDest_xMun"] = $xml->NFe->infNFe->dest->enderDest->xMun;
				$retorno[0]["dest_enderDest_UF"] = $xml->NFe->infNFe->dest->enderDest->UF;
				$retorno[0]["dest_enderDest_CEP"] = $xml->NFe->infNFe->dest->enderDest->CEP;
				$retorno[0]["dest_enderDest_cPais"] = $xml->NFe->infNFe->dest->enderDest->cPais;
				$retorno[0]["dest_enderDest_xPais"] = $xml->NFe->infNFe->dest->enderDest->xPais;
				$retorno[0]["dest_enderDest_fone"] = $xml->NFe->infNFe->dest->enderDest->fone;
				$retorno[0]["dest_IE"] = $xml->NFe->infNFe->dest->IE;

				$iDet = 0;
				do
				{
					$retorno[0]["quantidade_itens"] = $iDet;
					$retorno[0][$iDet]["det_prod_cProd"] = $xml->NFe->infNFe->det[$iDet]->prod->cProd;
					$retorno[0][$iDet]["det_prod_xProd"] = $xml->NFe->infNFe->det[$iDet]->prod->xProd;

					$retorno["teste"] = $xml->NFe->infNFe->det[$iDet]->prod->cProd;

					$retorno[0][$iDet]["det_prod_NCM"] =  $xml->NFe->infNFe->det[$iDet]->prod->NCM;
					$retorno[0][$iDet]["det_prod_CFOP"] =  $xml->NFe->infNFe->det[$iDet]->prod->CFOP;
					$retorno[0][$iDet]["det_prod_uCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->uCom;
					$retorno[0][$iDet]["det_prod_qCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->qCom;
					$retorno[0][$iDet]["det_prod_vUnCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->vUnCom;
					$retorno[0][$iDet]["det_prod_vProd"] =  $xml->NFe->infNFe->det[$iDet]->prod->vProd;
					$retorno[0][$iDet]["det_prod_cEANTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->cEANTrib;
					$retorno[0][$iDet]["det_prod_uTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->uTrib;
					$retorno[0][$iDet]["det_prod_qTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->qTrib;
					$retorno[0][$iDet]["det_prod_vUnTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->vUnTrib;
					$retorno[0][$iDet]["det_prod_indTot"] =  $xml->NFe->infNFe->det[$iDet]->prod->indTot;
					$retorno[0][$iDet]["det_prod_xPed"] =  $xml->NFe->infNFe->det[$iDet]->prod->xPed;
					$retorno[0][$iDet]["det_prod_nItemPed"] =  $xml->NFe->infNFe->det[$iDet]->prod->nItemPed;
					$retorno[0][$iDet]["det_imposto_vTotTrib"] =  $xml->NFe->infNFe->det[$iDet]->imposto->vTotTrib;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vICMSST;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->orig;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->CST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->ModBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vBC;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->pICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vICMS;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vBCST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vBCST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_pICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->pICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vICMSST;





					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS9O;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500;
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900;


					


					$retorno[0][$iDet]["det_imposto_IPI_CNPJProd"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->CNPJProd;
					$retorno[0][$iDet]["det_imposto_IPI_qSelo"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->qSelo;
					$retorno[0][$iDet]["det_imposto_IPI_cEnq"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->cEnq;
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->CST;
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->vBC;
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_pIPI"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->pIPI;
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_vIPI"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->vIPI;
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->CST;
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->vBC;
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_pPIS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->pPIS;
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_vPIS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->vPIS;
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->CST;
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->vBC;
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_pCOFINS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->pCOFINS;
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_vCOFINS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->vCOFINS;
					$retorno[0][$iDet]["det_infAdProd"] =  $xml->NFe->infNFe->det[$iDet]->infAdProd;

					$iDet++;


				} while($xml->NFe->infNFe->det[$iDet] != NULL);



				


				

				$retorno[0]["total_ICMSTot_vBC"] =  $xml->NFe->infNFe->total->ICMSTot->vBC;
				$retorno[0]["total_ICMSTot_vICMS"] =  $xml->NFe->infNFe->total->ICMSTot->vICMS;
				$retorno[0]["total_ICMSTot_vBCST"] =  $xml->NFe->infNFe->total->ICMSTot->vBCST;
				$retorno[0]["total_ICMSTot_vICMSST"] =  $xml->NFe->infNFe->total->ICMSTot->vST;
				$retorno[0]["total_ICMSTot_vST"] =  $xml->NFe->infNFe->total->ICMSTot->vST;
				$retorno[0]["total_ICMSTot_vProd"] =  $xml->NFe->infNFe->total->ICMSTot->vProd;
				$retorno[0]["total_ICMSTot_vFrete"] =  $xml->NFe->infNFe->total->ICMSTot->vFrete;
				$retorno[0]["total_ICMSTot_vSeg"] =  $xml->NFe->infNFe->total->ICMSTot->vSeg;
				$retorno[0]["total_ICMSTot_vDesc"] =  $xml->NFe->infNFe->total->ICMSTot->vDesc;
				$retorno[0]["total_ICMSTot_vII"] =  $xml->NFe->infNFe->total->ICMSTot->vII;
				$retorno[0]["total_ICMSTot_vIPI"] =  $xml->NFe->infNFe->total->ICMSTot->vIPI;
				$retorno[0]["total_ICMSTot_vPIS"] =  $xml->NFe->infNFe->total->ICMSTot->vPIS;
				$retorno[0]["total_ICMSTot_vCOFINS"] =  $xml->NFe->infNFe->total->ICMSTot->vCOFINS;
				$retorno[0]["total_ICMSTot_vOutro"] =  $xml->NFe->infNFe->total->ICMSTot->vOutro;
				$retorno[0]["total_ICMSTot_vNF"] =  $xml->NFe->infNFe->total->ICMSTot->vNF;

				$retorno[0]["total_ICMSTot_vTotTrib"] =  $xml->NFe->infNFe->total->ICMSTot->vTotTrib;

				$retorno[0]["transp_modFrete"] =  $xml->NFe->infNFe->transp->modFrete;
				$retorno[0]["transp_transporta_CNPJ"] =  $xml->NFe->infNFe->transp->transporta->CNPJ;
				$retorno[0]["transp_transporta_xNome"] =  $xml->NFe->infNFe->transp->transporta->xNome;
				$retorno[0]["transp_transporta_IE"] =  $xml->NFe->infNFe->transp->transporta->IE;
				$retorno[0]["transp_transporta_xEnder"] =  $xml->NFe->infNFe->transp->transporta->xEnder;
				$retorno[0]["transp_transporta_xMun"] =  $xml->NFe->infNFe->transp->transporta->xMun;
				$retorno[0]["transp_transporta_UF"] =  $xml->NFe->infNFe->transp->transporta->UF;


				$iVol = 0;
				$PesoBruto = 0.0;
				$PesoLiquido = 0.0;

				do
				{
					$PesoLiquido = $PesoLiquido + floatval($xml->NFe->infNFe->transp->vol[$iVol]->pesoL[0]);
					$PesoBruto = $PesoBruto + floatval($xml->NFe->infNFe->transp->vol[$iVol]->pesoB[0]);

					$iVol++;

				} while($xml->NFe->infNFe->transp->vol[$iVol] != NULL);

				$retorno[0]["transp_vol_qVol"] =  $xml->NFe->infNFe->transp->vol->qVol;
				$retorno[0]["transp_vol_esp"] =  $xml->NFe->infNFe->transp->vol->esp;
				$retorno[0]["transp_vol_pesoL"] =  strval($PesoLiquido);
				$retorno[0]["transp_vol_pesoB"] =  strval($PesoBruto);
				$retorno[0]["cobr_fat_nFat"] =  $xml->NFe->infNFe->cobr->fat->nFat;
				$retorno[0]["cobr_fat_vOrig"] =  $xml->NFe->infNFe->cobr->fat->vOrig;
				$retorno[0]["cobr_fat_vLiq"] =  $xml->NFe->infNFe->cobr->fat->vLiq;
				$retorno[0]["cobr_dup_nDup"] =  $xml->NFe->infNFe->cobr->dup->nDup;
				$retorno[0]["cobr_dup_dVenc"] =  $xml->NFe->infNFe->cobr->dup->dVenc;
				$retorno[0]["cobr_dup_vDup"] =  $xml->NFe->infNFe->cobr->dup->vDup;
				$retorno[0]["infAdic_infCpl"] =  $xml->NFe->infNFe->infAdic->infCpl;
				$retorno[0]["infAdic_obsCont_xTexto"] =  $xml->NFe->infNFe->infAdic->obsCont->xTexto;




				$arquivos->close();
			break;


			case "ENFE-VISUALIZAR-DACTE":

				$xml = simplexml_load_file($arquivo);

				$retorno[0]["infcte_id"] = $xml->CTe->infCte["Id"][0];
				$retorno[0]["ide_nCT"] = $xml->CTe->infCte->ide->nCT;
				$retorno[0]["ide_serie"] = $xml->CTe->infCte->ide->serie;
				$retorno[0]["ide_mod"] = $xml->CTe->infCte->ide->mod;
				$retorno[0]["ide_dhEmi"] = $xml->CTe->infCte->ide->dhEmi;
				$retorno[0]["ide_CFOP"] = $xml->CTe->infCte->ide->CFOP;
				$retorno[0]["ide_natOp"] = $xml->CTe->infCte->ide->natOp;
				$retorno[0]["infProt_nProt"] = $xml->protCTe->infProt->nProt;
				$retorno[0]["infProt_dhRecbto"] = $xml->protCTe->infProt->dhRecbto;
				$retorno[0]["ide_xMunIni"] = $xml->CTe->infCte->ide->xMunIni;
				$retorno[0]["ide_UFIni"] = $xml->CTe->infCte->ide->UFIni;
				$retorno[0]["ide_xMunFim"] = $xml->CTe->infCte->ide->xMunFim;
				$retorno[0]["ide_UFFim"] = $xml->CTe->infCte->ide->UFFim;
				$retorno[0]["ide_forPag"] = $xml->CTe->infCte->ide->forPag;
				$retorno[0]["ide_tpCTe"] = $xml->CTe->infCte->ide->tpCTe;
				$retorno[0]["ide_toma03_toma"] = $xml->CTe->infCte->ide->toma03->toma;
				$retorno[0]["ide_tpServ"] = $xml->CTe->infCte->ide->tpServ;
				$retorno[0]["ide_modal"] = $xml->CTe->infCte->ide->modal;
				$retorno[0]["compl_xObs"] = $xml->CTe->infCte->compl->xObs;

				$iCount = 0;

				do
				{
					$retorno[0]["compl_ObsCont"][$iCount]["xTexto"] =  $xml->CTe->infCte->compl->ObsCont[$iCount]->xTexto;
					$iCount++;

				} while($xml->CTe->infCte->compl->ObsCont[$iCount] != NULL);

				$retorno[0]["emit_CNPJ"] = $xml->CTe->infCte->emit->CNPJ;
				$retorno[0]["emit_IE"] = $xml->CTe->infCte->emit->IE;
				$retorno[0]["emit_xNome"] = $xml->CTe->infCte->emit->xNome;
				$retorno[0]["emit_enderEmit_xLgr"] = $xml->CTe->infCte->emit->enderEmit->xLgr;
				$retorno[0]["emit_enderEmit_nro"] = $xml->CTe->infCte->emit->enderEmit->nro;
				$retorno[0]["emit_enderEmit_xBairro"] = $xml->CTe->infCte->emit->enderEmit->xBairro;
				$retorno[0]["emit_enderEmit_xMun"] = $xml->CTe->infCte->emit->enderEmit->xMun;
				$retorno[0]["emit_enderEmit_CEP"] = $xml->CTe->infCte->emit->enderEmit->CEP;
				$retorno[0]["emit_enderEmit_UF"] = $xml->CTe->infCte->emit->enderEmit->UF;
				$retorno[0]["emit_enderEmit_fone"] = $xml->CTe->infCte->emit->enderEmit->fone;
				$retorno[0]["rem_CNPJ"] = $xml->CTe->infCte->rem->CNPJ;
				$retorno[0]["rem_IE"] = $xml->CTe->infCte->rem->IE;
				$retorno[0]["rem_xNome"] = $xml->CTe->infCte->rem->xNome;
				$retorno[0]["rem_fone"] = $xml->CTe->infCte->rem->fone;
				$retorno[0]["rem_enderReme_xLgr"] = $xml->CTe->infCte->rem->enderReme->xLgr;
				$retorno[0]["rem_enderReme_nro"] = $xml->CTe->infCte->rem->enderReme->nro;
				$retorno[0]["rem_enderReme_xBairro"] = $xml->CTe->infCte->rem->enderReme->xBairro;
				$retorno[0]["rem_enderReme_xMun"] = $xml->CTe->infCte->rem->enderReme->xMun;
				$retorno[0]["rem_enderReme_CEP"] = $xml->CTe->infCte->rem->enderReme->CEP;
				$retorno[0]["rem_enderReme_UF"] = $xml->CTe->infCte->rem->enderReme->UF;
				$retorno[0]["rem_enderReme_xPais"] = $xml->CTe->infCte->rem->enderReme->xPais;
				$retorno[0]["exped_CNPJ"] = $xml->CTe->infCte->exped->CNPJ;
				$retorno[0]["exped_IE"] = $xml->CTe->infCte->exped->IE;
				$retorno[0]["exped_xNome"] = $xml->CTe->infCte->exped->xNome;
				$retorno[0]["exped_fone"] = $xml->CTe->infCte->exped->fone;
				$retorno[0]["exped_enderExped_xLgr"] = $xml->CTe->infCte->exped->enderExped->xLgr;
				$retorno[0]["exped_enderExped_nro"] = $xml->CTe->infCte->exped->enderExped->nro;
				$retorno[0]["exped_enderExped_xBairro"] = $xml->CTe->infCte->exped->enderExped->xBairro;
				$retorno[0]["exped_enderExped_xMun"] = $xml->CTe->infCte->exped->enderExped->xMun;
				$retorno[0]["exped_enderExped_CEP"] = $xml->CTe->infCte->exped->enderExped->CEP;
				$retorno[0]["exped_enderExped_UF"] = $xml->CTe->infCte->exped->enderExped->UF;
				$retorno[0]["exped_enderExped_xPais"] = $xml->CTe->infCte->exped->enderExped->xPais;
				$retorno[0]["dest_CNPJ"] = $xml->CTe->infCte->dest->CNPJ;
				$retorno[0]["dest_IE"] = $xml->CTe->infCte->dest->IE;
				$retorno[0]["dest_xNome"] = $xml->CTe->infCte->dest->xNome;
				$retorno[0]["dest_fone"] = $xml->CTe->infCte->dest->fone;
				$retorno[0]["dest_enderDest_xLgr"] = $xml->CTe->infCte->dest->enderDest->xLgr;
				$retorno[0]["dest_enderDest_nro"] = $xml->CTe->infCte->dest->enderDest->nro;
				$retorno[0]["dest_enderDest_xBairro"] = $xml->CTe->infCte->dest->enderDest->xBairro;
				$retorno[0]["dest_enderDest_xMun"] = $xml->CTe->infCte->dest->enderDest->xMun;
				$retorno[0]["dest_enderDest_CEP"] = $xml->CTe->infCte->dest->enderDest->CEP;
				$retorno[0]["dest_enderDest_UF"] = $xml->CTe->infCte->dest->enderDest->UF;
				$retorno[0]["dest_enderDest_xPais"] = $xml->CTe->infCte->dest->enderDest->xPais;
				$retorno[0]["infCte_vPrest_vTPrest"] = $xml->CTe->infCte->vPrest->vTPrest;
				$retorno[0]["infCte_vPrest_vRec"] = $xml->CTe->infCte->vPrest->vRec;
				$retorno[0]["infCTeNorm_seg_respSeg"] = $xml->CTe->infCte->infCTeNorm->seg->respSeg;
				$retorno[0]["infCTeNorm_seg_xSeg"] = $xml->CTe->infCte->infCTeNorm->seg->xSeg;
				$retorno[0]["infCTeNorm_seg_nApol"] = $xml->CTe->infCte->infCTeNorm->seg->nApol;
				$retorno[0]["infCTeNorm_seg_vCarga"] = $xml->CTe->infCte->infCTeNorm->seg->vCarga;

				if($xml->CTe->infCte->imp->ICMS->ICMS00->CST != "")
				{
					$retorno[0]["imp_ICMS_ICMS00_CST"] = $xml->CTe->infCte->imp->ICMS->ICMS00->CST;
					$retorno[0]["imp_ICMS_ICMS00_vBC"] = $xml->CTe->infCte->imp->ICMS->ICMS00->vBC;
					$retorno[0]["imp_ICMS_ICMS00_pICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS00->pICMS;
					$retorno[0]["imp_ICMS_ICMS00_vICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS00->vICMS;
				}
				else if($xml->CTe->infCte->imp->ICMS->ICMS20->CST!= "")
				{
					$retorno[0]["imp_ICMS_ICMS20_CST"] = $xml->CTe->infCte->imp->ICMS->ICMS20->CST;
					$retorno[0]["imp_ICMS_ICMS20_vBC"] = $xml->CTe->infCte->imp->ICMS->ICMS20->vBC;
					$retorno[0]["imp_ICMS_ICMS20_pICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS20->pICMS;
					$retorno[0]["imp_ICMS_ICMS20_vICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS20->vICMS;
				}
				else if($xml->CTe->infCte->imp->ICMS->ICMS45->CST != "")
				{
					$retorno[0]["imp_ICMS_ICMS45_CST"] = $xml->CTe->infCte->imp->ICMS->ICMS45->CST;
					//$retorno[0]["imp_ICMS_ICMS45_vBC"] = $xml->CTe->infCte->imp->ICMS->ICMS45->vBC;
					//$retorno[0]["imp_ICMS_ICMS45_pICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS45->pICMS;
					//$retorno[0]["imp_ICMS_ICMS45_vICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS45->vICMS;
				}
				else if($xml->CTe->infCte->imp->ICMS->ICMS60->CST != "")
				{
					$retorno[0]["imp_ICMS_ICMS60_CST"] = $xml->CTe->infCte->imp->ICMS->ICMS60->CST;
					$retorno[0]["imp_ICMS_ICMS60_vBCSTRet"] = $xml->CTe->infCte->imp->ICMS->ICMS60->vBCSTRet;
					$retorno[0]["imp_ICMS_ICMS60_pICMSSTRet"] = $xml->CTe->infCte->imp->ICMS->ICMS60->pICMSSTRet;
					$retorno[0]["imp_ICMS_ICMS60_vICMSSTRet"] = $xml->CTe->infCte->imp->ICMS->ICMS60->vICMSSTRet;
				}
				else if($xml->CTe->infCte->imp->ICMS->ICMS90->CST != "")
				{
					$retorno[0]["imp_ICMS_ICMS90_CST"] = $xml->CTe->infCte->imp->ICMS->ICMS90->CST;
					$retorno[0]["imp_ICMS_ICMS90_vBC"] = $xml->CTe->infCte->imp->ICMS->ICMS90->vBC;
					$retorno[0]["imp_ICMS_ICMS90_pICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS90->pICMS;
					$retorno[0]["imp_ICMS_ICMS90_vICMS"] = $xml->CTe->infCte->imp->ICMS->ICMS90->vICMS;
				}
				else if($xml->CTe->infCte->imp->ICMS->ICMSOutraUF->CST != "")
				{
					$retorno[0]["imp_ICMS_ICMSOutraUF_CST"] = $xml->CTe->infCte->imp->ICMS->ICMSOutraUF->CST;
					$retorno[0]["imp_ICMS_ICMSOutraUF_vBCOutraUF"] = $xml->CTe->infCte->imp->ICMS->ICMSOutraUF->vBCOutraUF;
					$retorno[0]["imp_ICMS_ICMSOutraUF_pICMSOutraUF"] = $xml->CTe->infCte->imp->ICMS->ICMSOutraUF->pICMSOutraUF;
					$retorno[0]["imp_ICMS_ICMSOutraUF_vICMSOutraUF"] = $xml->CTe->infCte->imp->ICMS->ICMSOutraUF->vICMSOutraUF;
				}

				$retorno[0]["infCTeNorm_infCarga_vCarga"] = $xml->CTe->infCte->infCTeNorm->infCarga->vCarga;
				$retorno[0]["infCTeNorm_infCarga_proPred"] = $xml->CTe->infCte->infCTeNorm->infCarga->proPred;
				$retorno[0]["infCTeNorm_infCarga_infQ"] = $xml->CTe->infCte->infCTeNorm->infCarga->infQ;

				$iCount = 0;

				do
				{
					$retorno[0][$iCount]["infCTeNorm_infCarga_infQ_cUnid"] =  $xml->CTe->infCte->infCTeNorm->infCarga->infQ[$iCount]->cUnid;
					$retorno[0][$iCount]["infCTeNorm_infCarga_infQ_tpMed"] =  $xml->CTe->infCte->infCTeNorm->infCarga->infQ[$iCount]->tpMed;
					$retorno[0][$iCount]["infCTeNorm_infCarga_infQ_qCarga"] =  $xml->CTe->infCte->infCTeNorm->infCarga->infQ[$iCount]->qCarga;
					$iCount++;

				} while($xml->CTe->infCte->infCTeNorm->infCarga->infQ[$iCount] != NULL);


				$iCount = 0;

				do
				{
					$retorno[1][$iCount]["vPrest_Comp_xNome"] =  $xml->CTe->infCte->vPrest->Comp[$iCount]->xNome;
					$retorno[1][$iCount]["vPrest_Comp_vComp"] =  $xml->CTe->infCte->vPrest->Comp[$iCount]->vComp;
					$iCount++;
					$retorno[1]["qtde_comp"] =  $iCount;

				} while($xml->CTe->infCte->vPrest->Comp[$iCount] != NULL);

				$iCount = 0;

				do
				{
					$retorno[0]["infCte_infCTeNorm_infDoc_infNFe"][$iCount] =  $xml->CTe->infCte->infCTeNorm->infDoc->infNFe[$iCount]->chave;
					$iCount++;

				} while($xml->CTe->infCte->infCTeNorm->infDoc->infNFe[$iCount] != NULL);

				$arquivos->close();
			break;

			case "ENFE-CONVERT-XML-TXT":
				$UnConvertNFePHP = new UnConvertNFePHP();
				$arquivo_txt = $UnConvertNFePHP->nfexml2txt($arquivo);

				$arquivo_saida = $arquivo;
				$arquivo_saida = str_replace("recebe", "processadas", $arquivo_saida);
				$arquivo_saida = str_replace(".xml", "", $arquivo_saida);

				$nome_arquivo_saida = $arquivo_saida;
				$nome_arquivo_saida = str_replace("../../../", "", $nome_arquivo_saida);
				$nome_arquivo_saida = $nome_arquivo_saida.".txt";

				$splitArquivo = explode("|", $arquivo_txt);
			
				for($i = 0; $i < count($splitArquivo); $i++)
				{
					if(is_numeric($splitArquivo[$i]))
					{
						if($splitArquivo[$i-1] != "\r\nI")
							$splitArquivo[$i] = str_replace(".", ",", $splitArquivo[$i]);
					}
				}

				$arquivo_txt = implode("|", $splitArquivo);
				file_put_contents($arquivo_saida.".TXT", $arquivo_txt);
				$retorno[0]["caminho_retorno"] = $nome_arquivo_saida;
			break;

			case "ECTE-CONVERT-XML-TXT":
				$UnConvertCTePHP = new UnConvertCTePHP();
				$arquivo_txt = $UnConvertCTePHP->ctexml2txt($arquivo);

				$arquivo_saida = $arquivo;
				$arquivo_saida = str_replace("recebe", "processadas", $arquivo_saida);
				$arquivo_saida = str_replace(".xml", "", $arquivo_saida);

				$nome_arquivo_saida = $arquivo_saida;
				$nome_arquivo_saida = str_replace("../../../", "", $nome_arquivo_saida);
				$nome_arquivo_saida = $nome_arquivo_saida.".txt";

				$splitArquivo = explode("|", $arquivo_txt);
			
				for($i = 0; $i < count($splitArquivo); $i++)
				{
					if(is_numeric($splitArquivo[$i]))
					{
						$splitArquivo[$i] = str_replace(".", ",", $splitArquivo[$i]);
					}
				}

				$arquivo_txt = implode("|", $splitArquivo);
				file_put_contents($arquivo_saida.".TXT", $arquivo_txt);
				$retorno[0]["caminho_retorno"] = $nome_arquivo_saida;
			break;

			case "ENFE-IMPORTACAO-CONCLUIDA":
				//$arquivo1 = "arquivoxml.xml";
				//$arquivo2 = "arquivoxml.xml";
				$name_file_copy_entrada = $_POST['hCopyArquivoDirEntrada'];
				$name_file_copy_saida = $_POST['hCopyArquivoDirSaida'];
				//$file = $arquivo.".txt";
				//$newfile = 'example.txt.bak';

				copy($name_file_copy_entrada, $name_file_copy_saida);

				unlink($name_file_copy_entrada);
				
			break;

		}


		//echo "Produtoooo >>>>".$retorno["teste"]."<<<<\n";


		$json = json_encode($retorno);
		echo($json);

	}
	else
	{
		echo 'Este diretorio nao existe.';
	}

?>
