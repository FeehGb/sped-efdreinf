
<?php
	require_once("UnConvertNFePHP.class.php");
	//$diretorioEnt = './xml/';
	//$diretorioEnt = '../../'.$_POST['tCaminhoXmlEntrada'].'/';
	$diretorioEnt = '../../../nfe/recebe/';
	//$diretorioEnt = '../../recebe/nfe/';
	//$diretorioSaida = '../../../'.$_POST['hCaminhoXmlSaida'].'/'; gjps 16/10/2014
	$diretorioSaida = $_POST['hCaminhoXmlSaida'].'/';

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

						$strSemQuebras = preg_replace('/[\n|\r|\n\r|\r\n]{2,}/',' ', $conteudo_file );

						$xml = @simplexml_load_string($strSemQuebras);

						//file_put_contents("/home/eduardo/saida_xml.xml", $strSemQuebras);

						if(!$xml)
						{
							//echo $d[$i];

							//exit();
						}

						//echo "cnpj:#".$xml->CTe->infCte->rem->CNPJ."#";

						//echo "***".$xml->NFe->infNFe->ide->dEmi."***";
						if($cnpj_fornecedor != "" && $cnpj_empresa != "")
						{
							//echo "cnpj1:#".$xml->CTe->infCte->rem->CNPJ."#";

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

							if($cnpj_fornecedor == $xml->CTe->infCte->rem->CNPJ)
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

							

							if($cnpj_fornecedor == $xml->CTe->infCte->rem->CNPJ)
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
							//echo "cnpj3:#".$xml->CTe->infCte->rem->CNPJ."#";

							if($cnpj_empresa == $xml->NFe->infNFe->dest->CNPJ)
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

							if($cnpj_empresa == $xml->CTe->infCte->rem->CNPJ)
							{
								$retorno[0]["qtde_arquivos"] = $qtdeFile;
								$retorno[$linha]["nome_arquivo"] = $diretorioEnt.$d[$i];


								if($xml->CTe->infCte->ide->dEmi != "")
									$retorno[$linha]["ide_dEmi"] = $xml->CTe->infCte->ide->dEmi;
								else if($xml->CTe->infCte->ide->dhEmi != "")
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


				//echo $xml->NFe->infNFe->det[$iDet]->prod->cProd[0];
				
				do
				{
					$retorno[0]["quantidade_itens"] = $iDet;
					$retorno[0][$iDet]["det_prod_cProd"] = $xml->NFe->infNFe->det[$iDet]->prod->cProd[0];
					$retorno[0][$iDet]["det_prod_cProd"] = $xml->NFe->infNFe->det[$iDet]->prod->cProd[0];
					$retorno[0][$iDet]["det_prod_xProd"] = $xml->NFe->infNFe->det[$iDet]->prod->xProd[0];
					$retorno[0][$iDet]["det_prod_NCM"] =  $xml->NFe->infNFe->det[$iDet]->prod->NCM[0];
					$retorno[0][$iDet]["det_prod_CFOP"] =  $xml->NFe->infNFe->det[$iDet]->prod->CFOP[0];
					$retorno[0][$iDet]["det_prod_uCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->uCom[0];
					$retorno[0][$iDet]["det_prod_qCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->qCom[0];
					$retorno[0][$iDet]["det_prod_vUnCom"] =  $xml->NFe->infNFe->det[$iDet]->prod->vUnCom[0];
					$retorno[0][$iDet]["det_prod_vProd"] =  $xml->NFe->infNFe->det[$iDet]->prod->vProd[0];
					$retorno[0][$iDet]["det_prod_cEANTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->cEANTrib[0];
					$retorno[0][$iDet]["det_prod_uTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->uTrib[0];
					$retorno[0][$iDet]["det_prod_qTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->qTrib[0];
					$retorno[0][$iDet]["det_prod_vUnTrib"] =  $xml->NFe->infNFe->det[$iDet]->prod->vUnTrib[0];
					$retorno[0][$iDet]["det_prod_indTot"] =  $xml->NFe->infNFe->det[$iDet]->prod->indTot[0];
					$retorno[0][$iDet]["det_prod_xPed"] =  $xml->NFe->infNFe->det[$iDet]->prod->xPed[0];
					$retorno[0][$iDet]["det_prod_nItemPed"] =  $xml->NFe->infNFe->det[$iDet]->prod->nItemPed[0];
					$retorno[0][$iDet]["det_imposto_vTotTrib"] =  $xml->NFe->infNFe->det[$iDet]->imposto->vTotTrib;

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS00_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS00->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS10_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS10->vICMS[0];




					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS20_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS20->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS30_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS30->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS40_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS40->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS51_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS51->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS60_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS60->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS70_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS70->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMS90_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMS90->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSPart_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSPart->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSST_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSST->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN101_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN101->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN102_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN102->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN201_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN201->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN202_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN202->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN500_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN500->vICMS[0];

					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_orig"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->orig[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->CST[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_ModBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->ModBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vBC[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_pICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->pICMS[0];
					$retorno[0][$iDet]["det_imposto_ICMS_ICMSSN900_vICMS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->ICMS->ICMSSN900->vICMS[0];





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


					


					$retorno[0][$iDet]["det_imposto_IPI_CNPJProd"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->CNPJProd[0];
					$retorno[0][$iDet]["det_imposto_IPI_qSelo"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->qSelo[0];
					$retorno[0][$iDet]["det_imposto_IPI_cEnq"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->cEnq[0];
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->CST[0];
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->vBC[0];
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_pIPI"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->pIPI[0];
					$retorno[0][$iDet]["det_imposto_IPI_IPITrib_vIPI"] =  $xml->NFe->infNFe->det[$iDet]->imposto->IPI->IPITrib->vIPI[0];
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->CST[0];
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->vBC[0];
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_pPIS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->pPIS[0];
					$retorno[0][$iDet]["det_imposto_PIS_PISAliq_vPIS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->PIS->PISAliq->vPIS[0];
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_CST"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->CST[0];
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_vBC"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->vBC[0];
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_pCOFINS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->pCOFINS[0];
					$retorno[0][$iDet]["det_imposto_COFINS_COFINSAliq_vCOFINS"] =  $xml->NFe->infNFe->det[$iDet]->imposto->COFINS->COFINSAliq->vCOFINS[0];
					$retorno[0][$iDet]["det_infAdProd"] =  $xml->NFe->infNFe->det[$iDet]->infAdProd[0];

					$iDet++;


				} while($xml->NFe->infNFe->det[$iDet] != NULL);
				

				$retorno[0]["total_ICMSTot_vBC"] =  $xml->NFe->infNFe->total->ICMSTot->vBC;
				$retorno[0]["total_ICMSTot_vICMS"] =  $xml->NFe->infNFe->total->ICMSTot->vICMS;
				$retorno[0]["total_ICMSTot_vBCST"] =  $xml->NFe->infNFe->total->ICMSTot->vBCST;
				$retorno[0]["total_ICMSTot_vICMSST"] =  $xml->NFe->infNFe->total->ICMSTot->vICMSST;
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

		$json = json_encode($retorno);
		echo($json);

	}
	else
	{
		echo 'Este diretorio nao existe.';
	}

	//var_dump($objNF);

	

?>
