<?php

/**
 * Este arquivo é parte do projeto NFFePHP - Nota Fiscal eletrônica em PHP.
 *
 * Este programa é um software livre: você pode redistribuir e/ou modificá-lo
 * sob os termos da Licença Pública Geral GNU como é publicada pela Fundação
 * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
 * e/ou
 * sob os termos da Licença Pública Geral Menor GNU (LGPL) como é publicada pela
 * Fundação para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
 *
 * Este programa é distribuído na esperança que será útil, mas SEM NENHUMA
 * GARANTIA; nem mesmo a garantia explícita definida por qualquer VALOR COMERCIAL
 * ou de ADEQUAÇÃO PARA UM PROPÓSITO EM PARTICULAR,
 * veja a Licença Pública Geral GNU para mais detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Publica GNU e da
 * Licença Pública Geral Menor GNU (LGPL) junto com este programa.
 * Caso contrário consulte
 * <http://www.fsfla.org/svnwiki/trad/GPLv3>
 * ou
 * <http://www.fsfla.org/svnwiki/trad/LGPLv3>.
 * 
 * Esta classe atende aos critérios estabelecidos no
 * Manual de Importação/Exportação TXT Notas Fiscais eletrônicas versão 2.0.0
 *
 * @package     NFFePHP
 * @name        ConvertMDFePHP
 * @version     1.0.0
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL v.3
 * @license     http://www.gnu.org/licenses/lgpl.html GNU/LGPL v.3
 * @copyright   2009-2014 &copy; MDFePHP
 * @link        http://www.nfephp.org/
 * @author      Roberto L. Machado <linux.rlm at gmail dot com>
 * @author      Leandro C. Lopez <leandro.castoldi at gmail dot com>
 *
 *
 *        CONTRIBUIDORES (em ordem alfabetica):
 *
 */

class ConvertMDFePHP
{

    /**
     * xml
     * XML da MDFe
     * @var string 
     */
    public $xml = '';

    /**
     * chave
     * ID da MDFe 44 digitos
     * @var string 
     */
    public $chave = '';

    /**
     * txt
     * @var string TXT com MDFe
     */
    public $txt = '';

    /**
     * errMsg
     * Mensagens de erro do API
     * @var string
     */
    public $errMsg = '';

    /**
     * errStatus
     * Status de erro
     * @var boolean
     */
    public $errStatus = false;

    /**
     * tpAmb
     * Tipo de ambiente
     * @var string
     */
    public $tpAmb = '';
    
    /**
     * $tpEmis
     * Tipo de emissão
     * @var string
     */
    public $tpEmis = '';
	
    /**
     * $cUF
     * Tipo de emissão
     * @var string
     */
    public $cUF = '';
	
    /**
     * $modelo
     * Tipo de emissão
     * @var string
     */
    public $modelo = '';
    
    /**
     * cnpj, numero, serie
     */
    public $cnpj = '';
    public $numero = '';
	public $serie = '';

	private $limparString = true;
    /**
     * contruct
     * Método contrutor da classe
     *
     * @name contruct
     * @param boolean $limparString Ativa flag para limpar os caracteres especiais e acentos
     * @return none
     */
    public function __construct()
    {
 
    } //fim __contruct

    /**
     * MDFetxt2xml
     * Converte o arquivo txt em um array para ser mais facilmente tratado
     *
     * @name MDFetxt2xml
     * @param mixed $txt Path para o arquivo txt, array ou o conteudo do txt em uma string
     * @return string xml construido
     */
    public function MDFetxt2xml($txt, $tpAmb="", $tipEmiss="")
    {
        if (is_file($txt)) {
            $aDados = file($txt, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES | FILE_TEXT);
        } else {
            if (is_array($txt)) {
                $aDados = $txt;
            } else {
                if (strlen($txt) > 0) {
                    $aDados = explode("\n", $txt);
                }
            }
        }
        return $this->MDFeTxt2XmlArrayComLinhas($aDados, $tpAmb, $tipEmiss);
    } //fim MDFetxt2xml

    /**
     * MDFeTxt2XmlArrayComLinhas
     * Método de conversão das MDFe de txt para xml, conforme
     * especificações do Manual de Importação/Exportação TXT
     * Notas Fiscais eletrônicas versão 2.0.0 (24/08/2010)
     *
     * @name MDFeTxt2XmlArrayComLinhas
     * @param string $arrayComAsLinhasDoArquivo Array de Strings onde cada elemento é uma linha do arquivo
     * @return string xml construido
     */
    protected function MDFeTxt2XmlArrayComLinhas($arrayComAsLinhasDoArquivo, $tpAmb, $tipEmiss)
    {
        $arquivo = $arrayComAsLinhasDoArquivo;
        $notas = array();
        $currnota = -1;
        
        //lê linha por linha do arquivo txt
        for ($l = 0; $l < count($arquivo); $l++) {
            //separa os elementos do arquivo txt usando o pipe "|"
            $dados = explode("|", $arquivo[$l]);
            //remove todos os espaços adicionais, tabs, linefeed, e CR
            //de todos os campos de dados retirados do TXT
            for ($x = 0; $x < count($dados); $x++) {
                if (!empty($dados[$x])) {
                    $dados[$x] = trim(preg_replace('/\s\s+/', " ", $dados[$x]));
                    if ($this->limparString) {
                        $dados[$x] = $this->limpaString($dados[$x]);
                    }
                } //end if
            } //end for
            //monta o dado conforme o tipo, inicia lendo o primeiro campo da matriz
            switch ($dados[0]) {
                case "MANIFESTO":
                    // primeiro elemento não faz nada, aqui é informado o
                    //número de NF contidas no TXT
                    break;
                case "A":
                    //atributos da MDFe, campos obrigatórios [MDFe]
                    //A|versão do schema|id
                    // cria nota no array
                    $currnota++;
                    unset($dom, $MDFe, $infMDFe);
                    /// limpar todas variaveis utilizadas por cada MDFe....
                    
                    unset($dom, $MDFe, $infMDFe, $ide, $nMDF, $cMDF, $cDV, $dhEmi, $tpEmis, $verProc, $UFIni, $UFFim, $UFPer, $cMunDescarga, $xMunDescarga, $chMDFe, $SegCodBarra, $tpUnidTransp, $idUnidTransp, $placa, $tara, $tpRod, $tpCar, $UF, $nome, $cpf, $placa, $tara, $capKG, $tpCar, $UF, $tpRod, $qMDFe, $vCarga, $cUnid, $qCarga);

                    $this->chave = '';
                    $this->tpAmb = $tpAmb;
                    $this->xml = '';
                    $this->tpEmis = $tipEmiss;

                    $notas[$currnota] = array(
                        'dom' => false,
                        'MDFe' => false,
                        'infMDFe' => false,
                        'chave' => '',
                        'tpAmb' => $this->tpAmb);

                    //cria o objeto DOM para o xml
                    $notas[$currnota]['dom'] = new DOMDocument('1.0', 'UTF-8');
                    $dom = & $notas[$currnota]['dom'];
                    $dom->formatOutput = false;
                    $dom->preserveWhiteSpace = false;
                    $notas[$currnota]['MDFe'] = $dom->createElement("MDFe");
                    $MDFe = & $notas[$currnota]['MDFe'];
                    $MDFe->setAttribute("xmlns", "http://www.portalfiscal.inf.br/mdfe");
                    $notas[$currnota]['infMDFe'] = $dom->createElement("infMDFe");
                    $infMDFe = &$notas[$currnota]['infMDFe'];
                    $infMDFe->setAttribute("Id", $dados[2]);
                    $infMDFe->setAttribute("versao", $dados[1]);
                    //pega a chave de 44 digitos excluindo o a sigla MDFe
                    //$this->chave = substr($dados[2], 3, 44);
                    //$notas[$currnota]['chave'] = $this->chave;
                    break;
                case "B": //identificadores [infMDFe]
                    //ide => B|cUF|tpAmb|tpEmit|mod|serie|nMDF|modal|dhEmi|tpEmis|procEmi|verProc|UFIni|UFFim|
                    $ide = $dom->createElement("ide");
                    $cUF = $dom->createElement("cUF", $dados[1]);
                    $this->cUF = $dados[1];
                    $ide->appendChild($cUF);
                    //$tpAmb = $dom->createElement("tpAmb", $dados[2]);
					$tpAmb = $dom->createElement("tpAmb", $dados[2]); // Ambiente setado conforme cadastro de contribuinte (idependente do COBOL)
                    $ide->appendChild($tpAmb);
                    $tpEmit = $dom->createElement("tpEmit", $dados[3]);
                    $ide->appendChild($tpEmit);
                    $mod = $dom->createElement("mod", $dados[4]);
                    $this->modelo = $dados[4];
                    $ide->appendChild($mod);
                    $serie = $dom->createElement("serie", $dados[5]);
					$this->serie = $dados[5];
                    $ide->appendChild($serie);
                    $nMDF = $dom->createElement("nMDF", ltrim($dados[6],0));
					$this->numero = ltrim($dados[6],0);
                    $ide->appendChild($nMDF);
                    $cMDF = $dom->createElement("cMDF", ""); // VER COMO FAZER ISSO
                    $ide->appendChild($cMDF);
                    $cDV = $dom->createElement("cDV", ""); // VER COMO FAZER ISSO AQUI TB
                    $ide->appendChild($cDV);
                    $modal = $dom->createElement("modal", $dados[9]);		
                    $ide->appendChild($modal);
                    $dhEmi = $dom->createElement("dhEmi", $dados[10]);
                    $ide->appendChild($dhEmi);
                    $tpEmis = $dom->createElement("tpEmis", $dados[11]);
                    $ide->appendChild($tpEmis);
                    $procEmi = $dom->createElement("procEmi", $dados[12]);
                    $ide->appendChild($procEmi);
                    $verProc = $dom->createElement("verProc", $dados[13]);
                    $ide->appendChild($verProc);
                    $UFIni = $dom->createElement("UFIni", $dados[14]);
                    $ide->appendChild($UFIni);
                    $UFFim = $dom->createElement("UFFim", $dados[15]);
                    $ide->appendChild($UFFim);
					$dhIniViagem = $dom->createElement("dhIniViagem", $dados[16]);
                    $ide->appendChild($dhIniViagem);
                    $infMDFe->appendChild($ide);	
					break;
				case "B1":
				// infMDFe => ide => infMunCarrega|cMunCarrega|xMunCarrega
					$infMunCarrega = $dom->createElement("infMunCarrega");
					$ide->insertBefore($ide->appendChild($infMunCarrega), $dhIniViagem);             
					//$ide->appendChild($infMunCarrega);                                           
					$cMunCarrega = $dom->createElement("cMunCarrega", $dados[1]);
					$infMunCarrega->appendChild($cMunCarrega);                                       
					$xMunCarrega = $dom->createElement("xMunCarrega", $dados[2]);                    
					$infMunCarrega->appendChild($xMunCarrega);                                       
                break;
                case "C":
                    // infMDFe => ide => infPercurso|UFPer
                    //if (!isset($infPercurso)) {
                        $infPercurso = $dom->createElement("infPercurso");
                        $ide->insertBefore($ide->appendChild($infPercurso), $dhIniViagem);
                        //$ide->appendChild($infPercurso);
                    //}
                    $UFPer = $dom->createElement("UFPer", $dados[1]);
                    $infPercurso->appendChild($UFPer);
                    break;
                case "D":
                    // D|CNPJ|IE|xNome|xFant|xLgr|nro|xCpl|xBairro|cMun|xMun|CEP|UF|fone|email|
                    $emit = $dom->createElement("emit");
                    $cnpj = $dom->createElement("CNPJ", $dados[1]);
					$this->cnpj = $dados[1];
                    $emit->appendChild($cnpj);
                    $IE = $dom->createElement("IE", $dados[2]);
                    $emit->appendChild($IE);
                    $xNome = $dom->createElement("xNome", $dados[3]);
                    $emit->appendChild($xNome);
					$xFant = $dom->createElement("xFant", $dados[4]);
                    $emit->appendChild($xFant);
                    $infMDFe->appendChild($emit);
					
                    // infMDFe => emit => enderEmit
                    $enderEmi = $dom->createElement("enderEmit");
                    $xLgr = $dom->createElement("xLgr", $dados[5]);
                    $enderEmi->appendChild($xLgr);
                    $dados[6] = abs((int) $dados[6]);
                    $nro = $dom->createElement("nro", $dados[6]);
                    $enderEmi->appendChild($nro);
                    if (!empty($dados[7])) {
                        $xCpl = $dom->createElement("xCpl", $dados[7]);
                        $enderEmi->appendChild($xCpl);
                    }
                    $xBairro = $dom->createElement("xBairro", $dados[8]);
                    $enderEmi->appendChild($xBairro);
                    $cMun = $dom->createElement("cMun", $dados[9]);
                    $enderEmi->appendChild($cMun);
                    $xMun = $dom->createElement("xMun", $dados[10]);
                    $enderEmi->appendChild($xMun);
                    if (!empty($dados[11])) {
                        $CEP = $dom->createElement("CEP", $dados[11]);
                        $enderEmi->appendChild($CEP);
                    }
                    $UF = $dom->createElement("UF", $dados[12]);
                    $enderEmi->appendChild($UF);
					if (!empty($dados[13])) {
                        $fone = $dom->createElement("fone", $dados[13]);
                        $enderEmi->appendChild($fone);
                    }
					if (!empty($dados[14])) {
                        $email = $dom->createElement("email", $dados[14]);
                        $enderEmi->appendChild($email);
                    }

                    //$emit->insertBefore($emit->appendChild($enderEmi), $xNome);
                    $emit->appendChild($enderEmi);
                    break;
                case "F":
                    // infMDFe => infModal|versaoModal
                    $infModal = $dom->createElement("infModal");
                    $infModal->setAttribute("versaoModal", $dados[1]);

                    $infMDFe->appendChild($infModal);
                    break;
                case "R":
                    // infMDFe => infModal => rodo => veicTracao|placa|tara|tpRod|tpCar|UF
                    $rodo = $dom->createElement("rodo");
                    $infModal->appendChild($rodo);
					if(trim($dados[1]) != ""){
						$RNTRC = $dom->createElement("RNTRC", $dados[1]);
						$rodo->appendChild($RNTRC);
					}
					if(trim($dados[2]) != ""){
						$CIOT = $dom->createElement("CIOT", $dados[2]);
						$rodo->appendChild($CIOT);
					}
					
                    $veicTracao = $dom->createElement("veicTracao");
                    $cIntTracao = $dom->createElement("cInt", $dados[3]);
                    $veicTracao->appendChild($cIntTracao);
                    $placa = $dom->createElement("placa", $dados[4]);
                    $veicTracao->appendChild($placa);
					$RENAVAM = $dom->createElement("RENAVAM", $dados[5]);
                    $veicTracao->appendChild($RENAVAM);
                    $tara = $dom->createElement("tara", ltrim($dados[6],0));
                    $veicTracao->appendChild($tara);
					$capKG = $dom->createElement("capKG", ltrim($dados[7],0));
                    $veicTracao->appendChild($capKG);
					$capM3 = $dom->createElement("capM3", ltrim($dados[8],0));
                    $veicTracao->appendChild($capM3);
					
					if(trim($dados[9],0) != "" || trim($dados[10],0) != ""){
						$prop = $dom->createElement("prop");
						if(ltrim($dados[9],0) != ""){
							$CPF = $dom->createElement("CPF", $dados[9]);
							$prop->appendChild($CPF);
						}
						if(ltrim($dados[10],0) != ""){
							$CNPJ = $dom->createElement("CNPJ", $dados[10]);
							$prop->appendChild($CNPJ);
						}
						$RNTRC = $dom->createElement("RNTRC", ltrim($dados[11],0));
						$prop->appendChild($RNTRC);
						$xNome = $dom->createElement("xNome", $dados[12]);
						$prop->appendChild($xNome);
						$IE = $dom->createElement("IE", $dados[13]);
						$prop->appendChild($IE);
						$UF = $dom->createElement("UF", $dados[14]);
						$prop->appendChild($UF);
						$tpProp = $dom->createElement("tpProp", $dados[15]);
						$prop->appendChild($tpProp);

						$veicTracao->appendChild($prop);
					}
					
                    $tpRod = $dom->createElement("tpRod", $dados[16]);
                    $veicTracao->appendChild($tpRod);
                    $tpCarTracao = $dom->createElement("tpCar", $dados[17]);
                    $veicTracao->appendChild($tpCarTracao);
                    $UF = $dom->createElement("UF", $dados[18]);
                    $veicTracao->appendChild($UF);

                    $rodo->appendChild($veicTracao);
/*
                    if ($this->tpAmb == '2') {
                        if ($dados[1] != '') {
                            //operação nacional em ambiente homologação usar 99999999000191
                            $CNPJ = $dom->createElement("CNPJ", '99999999000191');
                        } else {
                            //operação com o exterior CNPJ vazio
                            $CNPJ = $dom->createElement("CNPJ", '');
                        }
                    } else {
                        $CNPJ = $dom->createElement("CNPJ", $dados[1]);
                    }//fim teste ambiente
                    $dest->insertBefore($dest->appendChild($CNPJ), $xNome);
*/
                    break;
                case "R1":
                    // infMDFe => infModal => rodo => veicTracao => condutor|xNome|CPF
                    $condutor = $dom->createElement("condutor");
                    $xNome = $dom->createElement("xNome", $dados[1]);
                    $condutor->appendChild($xNome);
                    $CPF = $dom->createElement("CPF", $dados[2]);
                    $condutor->appendChild($CPF);
                    
                    $veicTracao->insertBefore($veicTracao->appendChild($condutor), $tpRod);
                    break;
                case "R2":
                    // infMDFe => infModal => rodo => veicReboque|placa|tara|capKG|tpCar|UF
                    $veicReboque = $dom->createElement("veicReboque");
                    $cIntReboque = $dom->createElement("cInt", $dados[1]);
                    $veicReboque->appendChild($cIntReboque);
                    $placa = $dom->createElement("placa", $dados[2]);
                    $veicReboque->appendChild($placa);
					$RENAVAM = $dom->createElement("RENAVAM", $dados[3]);
                    $veicReboque->appendChild($RENAVAM);
                    $tara = $dom->createElement("tara", ltrim($dados[4],0));
                    $veicReboque->appendChild($tara);
                    $capKG = $dom->createElement("capKG", ltrim($dados[5],0));
                    $veicReboque->appendChild($capKG);
                    $capM3 = $dom->createElement("capM3", ltrim($dados[6],0));
                    $veicReboque->appendChild($capM3);
					if(trim($dados[7],0) != "" || trim($dados[8],0)){
						$prop = $dom->createElement("prop");
							if(ltrim($dados[7],0) != ""){
								$CPF = $dom->createElement("CPF", $dados[7]);
								$prop->appendChild($CPF);
							}
							if(ltrim($dados[8],0) != ""){
								$CNPJ = $dom->createElement("CNPJ", $dados[8]);
								$prop->appendChild($CNPJ);
							}
							$RNTRC = $dom->createElement("RNTRC", $dados[9]);
							$prop->appendChild($RNTRC);
							$xNome = $dom->createElement("xNome", $dados[10]);
							$prop->appendChild($xNome);
							$IE = $dom->createElement("IE", $dados[11]);
							$prop->appendChild($IE);
							$UF = $dom->createElement("UF", $dados[12]);
							$prop->appendChild($UF);
							$tpProp = $dom->createElement("tpProp", $dados[13]);
							$prop->appendChild($tpProp);
						$veicReboque->appendChild($prop);
					}
					$tpCar = $dom->createElement("tpCar", $dados[14]);
                    $veicReboque->appendChild($tpCar);
                    $UF = $dom->createElement("UF", $dados[15]);
                    $veicReboque->appendChild($UF);

                    $rodo->appendChild($veicReboque);
                    break;
				case "R3":
                    // infMDFe => infModal => rodo => valePed => disp|CNPJForn|CNPJPg|nCompra|codAgPorto
                    $valePed = $dom->createElement("valePed");
						$disp = $dom->createElement("disp");
							$CNPJForn = $dom->createElement("CNPJForn", $dados[1]);
							$disp->appendChild($CNPJForn);
							$CNPJPg = $dom->createElement("CNPJPg", $dados[2]);
							$disp->appendChild($CNPJPg);
							$nCompra = $dom->createElement("nCompra", $dados[3]);
							$disp->appendChild($nCompra);
							if(trim($dados[4]) != ""){
								$codAgPorto = $dom->createElement("codAgPorto", $dados[4]);
								$disp->appendChild($codAgPorto);
							}
						$valePed->appendChild($disp);
					$rodo->appendChild($valePed);
                    break;
                case "G": 
                    // infMDFe => infDoc => infMunDescarga|cMunDescarga|xMunDescarga
                    if (!isset($infDoc)) {
                        $infDoc = $dom->createElement("infDoc");
                        //$infMDFe->insertBefore($infMDFe->appendChild($infDoc), $infModal);
                        $infMDFe->appendChild($infDoc);
                    }
                    $infMunDescarga = $dom->createElement("infMunDescarga");
                    $infDoc->appendChild($infMunDescarga);
                    
                    $cMunDescarga = $dom->createElement("cMunDescarga", $dados[1]);
                    $infMunDescarga->appendChild($cMunDescarga);
                    $xMunDescarga = $dom->createElement("xMunDescarga", $dados[2]);
                    $infMunDescarga->appendChild($xMunDescarga);
                    
                    break;
                case "G1":
                    // infMDFe => infDoc => infMunDescarga => infNFe|chNFe|SegCodBarra
                    $infNFe = $dom->createElement("infNFe");
                    $chNFe = $dom->createElement("chNFe", $dados[1]);
                    $infNFe->appendChild($chNFe);
                    
                    if (trim($dados[2]) != "") {
                        $SegCodBarra = $dom->createElement("SegCodBarra", $dados[2]);
                        $infNFe->appendChild($SegCodBarra);
                    }
					$infMunDescarga->appendChild($infNFe);
                    break;
				case "G11":
                    // infMDFe => infDoc => infMunDescarga => infNFe => infUnidTransp|
                    $infUnidTransp = $dom->createElement("infUnidTransp");
                    $tpUnidTransp = $dom->createElement("tpUnidTransp", $dados[1]);
                    $infUnidTransp->appendChild($tpUnidTransp);
					$idUnidTransp = $dom->createElement("idUnidTransp", $dados[2]);
                    $infUnidTransp->appendChild($idUnidTransp);
						// G11 - G111 - G112 - G1121
					$qtdRat
					 = $dom->createElement("qtdRat", $dados[3]);
                    $infUnidTransp->appendChild($qtdRat);

					$infNFe->appendChild($infUnidTransp);
                    break;
				case "G111":
                    // infMDFe => infDoc => infMunDescarga => infNFe => infUnidTransp|
                    $lacUnidTransp = $dom->createElement("lacUnidTransp");
                    $nLacre = $dom->createElement("nLacre", $dados[1]);
                    $lacUnidTransp->appendChild($nLacre);
					$infUnidTransp->appendChild($lacUnidTransp);
					$infUnidTransp->insertBefore($infUnidTransp->appendChild($lacUnidTransp), $qtdRat);
                    break;
				case "G112":
                    // infMDFe => infDoc => infMunDescarga => infNFe => infUnidTransp|
                    $infUnidCarga = $dom->createElement("infUnidCarga");
                    $tpUnidCarga = $dom->createElement("tpUnidCarga", $dados[1]);
                    $infUnidCarga->appendChild($tpUnidCarga);
					$idUnidCarga = $dom->createElement("idUnidCarga", $dados[2]);
                    $infUnidCarga->appendChild($idUnidCarga);
					$qtdRatCarga = $dom->createElement("qtdRat", $dados[3]);
                    $infUnidCarga->appendChild($qtdRatCarga);

					$infUnidTransp->insertBefore($infUnidTransp->appendChild($infUnidCarga), $qtdRat);
                    break;
				case "G1121":
                    // infMDFe => infDoc => infMunDescarga => infNFe => infUnidTransp|
                    $lacUnidCarga = $dom->createElement("lacUnidCarga");
                    $nLacreCarga = $dom->createElement("nLacre", $dados[1]);
                    $lacUnidCarga->appendChild($nLacreCarga);

					$infUnidCarga->insertBefore($infUnidCarga->appendChild($lacUnidCarga), $qtdRatCarga);
                    break;
                case "W":
                    // infMDFe => tot|qNFe|vCarga|cUnid|qCarga
                    $tot = $dom->createElement("tot");
					if(trim($dados[1]) != "" && ltrim($dados[1],0) != ""){
						$qCTe = $dom->createElement("qCTe", str_replace(",",".",ltrim($dados[1],0)));
						$tot->appendChild($qCTe);
					}
					if(trim($dados[2]) != "" && ltrim($dados[2],0) != ""){
						$qCT = $dom->createElement("qCT", str_replace(",",".",ltrim($dados[2],0)));
						$tot->appendChild($qCT);
					}
					if(trim($dados[3]) != "" && ltrim($dados[3],0) != ""){
						$qNFe = $dom->createElement("qNFe", str_replace(",",".",ltrim($dados[3],0)));
						$tot->appendChild($qNFe);
					}
					if(trim($dados[4]) != "" && ltrim($dados[4],0) != ""){
						$qNF = $dom->createElement("qNF", str_replace(",",".",ltrim($dados[4],0)));
						$tot->appendChild($qNF);
					}
					if(trim($dados[5]) != "" && ltrim($dados[5],0) != ""){
						$qMDFe = $dom->createElement("qMDFe", str_replace(",",".",ltrim($dados[5],0)));
						$tot->appendChild($qMDFe);
					}

                    $vCarga = $dom->createElement("vCarga", str_replace(",",".",$dados[6]));
                    $tot->appendChild($vCarga);
                    $cUnid = $dom->createElement("cUnid", $dados[7]);
                    $tot->appendChild($cUnid);
                    $qCarga = $dom->createElement("qCarga", str_replace(",",".",$dados[8]));
                    $tot->appendChild($qCarga);

                    $infMDFe->appendChild($tot);
                    break;
				case "X":
                    // infMDFe => lacres|nLacre
                    $lacres = $dom->createElement("lacres");
                    $nLacre = $dom->createElement("nLacre", $dados[1]);
                    $lacres->appendChild($nLacre);
                    $infMDFe->appendChild($lacres);
                    break;
				case "Y":
                    // infMDFe => autXML|CPF|CNPJ
                    $autXML = $dom->createElement("autXML");
                    $CPF = $dom->createElement("CPF", $dados[1]);
                    $autXML->appendChild($CPF);
					$CNPJ = $dom->createElement("CNPJ", $dados[2]);
                    $autXML->appendChild($CNPJ);
					
                    $infMDFe->appendChild($autXML);
                    break;
                    case "Z":
                    // infMDFe => infAdic|ifAdFisco|infCpl
                    $infAdic = $dom->createElement("infAdic");
                    $ifAdFisco = $dom->createElement("ifAdFisco", $dados[1]);
                    $infAdic->appendChild($ifAdFisco);
					$infCpl = $dom->createElement("infCpl", $dados[2]);
                    $infAdic->appendChild($infCpl);
					
                    $infMDFe->appendChild($infAdic);
                    break;
            } //end switch
        } //end for
        $arquivos_xml = array();
        foreach ($notas as $nota) {
            unset($dom, $MDFe, $infMDFe);
            $dom = $nota['dom'];
            $MDFe = $nota['MDFe'];
            $infMDFe = $nota['infMDFe'];
            //$this->chave = $nota['chave'];
            //$this->tpAmb = $nota['tpAmb'];
            $this->xml = '';
            //salva o xml na variável se o txt não estiver em branco
            if (!empty($infMDFe)) {
                $MDFe->appendChild($infMDFe);
                $dom->appendChild($MDFe);
                $this->montaChaveXML($dom);
                $xml = $dom->saveXML();
                $this->xml = $dom->saveXML();
                $xml = str_replace('<?xml version="1.0" encoding="UTF-8  standalone="no"?>', '<?xml version="1.0" encoding="UTF-8"?>', $xml);
                //remove linefeed, carriage return, tabs e multiplos espaços
                $xml = preg_replace('/\s\s+/', ' ', $xml);
                $xml = str_replace("> <", "><", $xml);
                $arquivos_xml[] = $xml;
                unset($xml);
            }
        }
        return($arquivos_xml);
    }
    //end function

    /**
     * limpaString
     * Remove todos dos caracteres especiais do texto e os acentos
     * preservando apenas letras de A-Z numeros de 0-9 e os caracteres @ , - ; : / _
     * 
     * @name limpaString
     * @param string $texto String a ser limpa
     * @return  string Texto sem caractere especiais
     */
    private function limpaString($texto)
    {
        $aFind = array('&', 'á', 'à', 'ã', 'â', 'é', 'ê',
            'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â',
            'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç');
        $aSubs = array('e', 'a', 'a', 'a', 'a', 'e', 'e',
            'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A',
            'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C');
        $novoTexto = str_replace($aFind, $aSubs, $texto);
        $novoTexto = preg_replace("/[^a-zA-Z0-9 @,-.;:\/_]/", "", $novoTexto);
        return $novoTexto;
    } //fim limpaString

    /**
     * calculaDV
     * Função para o calculo o digito verificador da chave da MDFe
     * 
     * @name calculaDV
     * @param string $chave43
     * @return string 
     */
    private function calculaDV($chave43)
    {
        $multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);
        $i = 42;
        $soma_ponderada = 0;
        while ($i >= 0) {
            for ($m = 0; $m < count($multiplicadores) && $i >= 0; $m++) {
                $soma_ponderada+= $chave43[$i] * $multiplicadores[$m];
                $i--;
            }
        }
        $resto = $soma_ponderada % 11;
        if ($resto == '0' || $resto == '1') {
            $cDV = 0;
        } else {
            $cDV = 11 - $resto;
        }
        return $cDV;
    } //fim calculaDV

    /**
     * montaChaveXML
     * Monta a chave da MDFe de 44 digitos com base em seus dados
     * Isso é útil no caso da chave formada no txt estar errada
     * 
     * @name montaChaveXML
     * @param object $dom 
     */
    private function montaChaveXML($dom)
    {
        $ide = $dom->getElementsByTagName("ide")->item(0);
        $emit = $dom->getElementsByTagName("emit")->item(0);
        $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $dEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
        $CNPJ = $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        $mod = $ide->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $ide->getElementsByTagName('serie')->item(0)->nodeValue;
        $nMDF = $ide->getElementsByTagName('nMDF')->item(0)->nodeValue;
        //$tpEmis = $this->tpEmis; // $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $tpEmis = $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $cMDF = $ide->getElementsByTagName('cMDF')->item(0)->nodeValue;
        if (strlen($cMDF) != 8) {
            $cMDF = $ide->getElementsByTagName('cMDF')->item(0)->nodeValue = rand(10000001, 99999999);
        }
        $tmpData = explode("T", $dEmi);
        $tempData = $dt = explode("-", $tmpData[0]);
        $forma = "%02d%02d%02d%s%02d%03d%09d%01d%08d";

        //echo "\n\ntpEmis:".$tpEmis."\n\n";
        //echo "\n\ntempChave:".$tempChave."\n\n";

        $tempChave = sprintf($forma, $cUF, $tempData[0] - 2000, $tempData[1], $CNPJ, $mod, $serie, $nMDF, $tpEmis, $cMDF);

        //echo "\n\ntempChave:".$tempChave."\n\n";

        $cDV = $ide->getElementsByTagName('cDV')->item(0)->nodeValue = $this->calculaDV($tempChave);
        $this->chave = $tempChave .= $cDV;
        $infMDFe = $dom->getElementsByTagName("infMDFe")->item(0);
        $infMDFe->setAttribute("Id", "MDFe" . $this->chave);
        $infMDFe->setAttribute("versao", '1.00');
    } //fim calculaChave
	
	/**
     * montaChaveXMLExterno
     * Monta a chave da MDFe de 44 digitos com base em seus dados
     * Isso é útil no caso da chave formada no txt estar errada
     * 
     * @name montaChaveXML
     * @param object $dom 
     */
    public function montaChaveXMLExterno($pXmlString){
		$this->mensagemErro="";

		if($pXmlString==""){
			$this->mensagemErro = "Parametro string contendo XML obrigatório";
			return false;
		}

		$dom = new DOMDocument;
		$dom->loadXML($pXmlString);
        $ide = $dom->getElementsByTagName("ide")->item(0);
        $emit = $dom->getElementsByTagName("emit")->item(0);
        $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $dEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
        $CNPJ = $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        $mod = $ide->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $ide->getElementsByTagName('serie')->item(0)->nodeValue;
        $nMDF = $ide->getElementsByTagName('nMDF')->item(0)->nodeValue;
        $tpEmis = /*$this->tpEmis; */ $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $cMDF = $ide->getElementsByTagName('cMDF')->item(0)->nodeValue;
        if (strlen($cMDF) != 8) {
            $cMDF = $ide->getElementsByTagName('cMDF')->item(0)->nodeValue = rand(10000001, 99999999);
        }
        $tmpData = explode("T", $dEmi);
        $tempData = $dt = explode("-", $tmpData[0]);
        $forma = "%02d%02d%02d%s%02d%03d%09d%01d%08d";
        $tempChave = sprintf($forma, $cUF, $tempData[0] - 2000, $tempData[1], $CNPJ, $mod, $serie, $nMDF, $tpEmis, $cMDF);
        $cDV = $ide->getElementsByTagName('cDV')->item(0)->nodeValue = $this->calculaDV($tempChave);
        $this->chave = $tempChave .= $cDV;
        $infMDFe = $dom->getElementsByTagName("infMDFe")->item(0);
        $infMDFe->setAttribute("Id", "MDFe" . $this->chave);
        $xmlRetorno = $dom->saveXML();
		return $xmlRetorno;
    } //fim calculaChave
	
}//fim da classe
