<?php

/**
 * Este arquivo é parte do projeto NFePHP - Nota Fiscal eletrônica em PHP.
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
 * @package     NFePHP
 * @name        UnConvertNFePHP
 * @version     1.0.2
 * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL v.3
 * @license     http://www.gnu.org/licenses/lgpl.html GNU/LGPL v.3
 * @copyright   2009-2011 &copy; NFePHP
 * @link        http://www.nfephp.org/
 * @author      Roberto L. Machado <linux.rlm at gmail dot com>
 * @author      Daniel Batista Lemes <dlemes at gmail dot com>
 *
 *
 *        CONTRIBUIDORES (em ordem alfabetica):
 *              Alberto  Leal <ees.beto at gmail dot com>
 *              Andre Noel <andrenoel at ubuntu dot com>
 *              Clauber Santos <cload_info at yahoo dot com dot br>
 *              Crercio <crercio at terra dot com dot br>
 *              Diogo Mosela <diego dot caicai at gmail dot com>
 *              Eduardo Gusmão <eduardo dot intrasis at gmail dot com>
 *              Elton Nagai <eltaum at gmail dot com>
 *              Fabio Ananias Silva <binhoouropreto at gmail dot com>
 *              Giovani Paseto <giovaniw2 at gmail dot com>
 *              Giuliano Nascimento <giusoft at hotmail dot com>
 *              Helder Ferreira <helder.mauricicio at gmail dot com>
 *              João Eduardo Silva Corrêa <jscorrea2 at gmail dot com>
 *              Leandro C. Lopez <leandro.castoldi at gmail dot com>
 *              Leandro G. Santana <leandrosantana1 at gmail dot com>
 *              Marcos Diez <marcos at unitron dot com dot br>
 *              Renato Ricci <renatoricci at singlesoftware dot com dot br>
 *              Roberto Spadim <rspadim at gmail dot com>
 *              Rodrigo Rysdyk <rodrigo_rysdyk at hotmail dot com>
 *
 */

class UnConvertNFePHP
{

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
     * nfexml2txt
     * Método de conversão das NFe de xml para txt, conforme
     * especificações do Manual de Importação/Exportação TXT
     * Notas Fiscais eletrônicas Versão 2.0.0
     * Referente ao modelo de NFe contido na versão 4.01
     * do manual de integração da NFe
     *
     * @name nfexml2txt
     * @param mixed string ou array $arq Paths dos arquivos xmls
     * @return mixed boolean ou string
     */
    public function nfexml2txt($arq)
    {
        //verificar se a string passada como parametro é string ou array
        if (is_array($arq)) {
            $matriz = $arq;
        } else {
            $matriz[] = $arq;
        }
        //para cada nf passada na matriz
        $contNotas = 0;
        foreach ($matriz as $file) {
            //carregar o conteúdo do arquivo xml em uma string
            if (is_file($file)) {
                $xml = file_get_contents($file);
            } else {
                $xml = $file;
            }
            //instanciar o ojeto DOM
            $dom = new DOMDocument('1.0', 'utf-8');
            //carregar o xml no objeto DOM
            if (!$dom->loadXML($xml)) {
                $this->errMsg = 'O arquivo indicado como NFe não é um XML!';
                $this->errStatus = true;
                return false;
            }
            //é um xml => verificar se é uma NFe
            $infNFe = $dom->getElementsByTagName("infNFe")->item(0);
            if (!isset($infNFe)) {
                $this->errMsg = 'O arquivo indicado como NFe não é uma NFe!';
                $this->errStatus = true;
                return false;
            }
            // é uma NFe => transformar em txt
            $contNotas++;
            //tansforma no xml => txt
            $txt .= $this->cxtt($dom);

            

        } //fim foreach
        $txt = "NOTA FISCAL|" . str_pad($contNotas, 3, "0", STR_PAD_LEFT). "\r\n" . $txt;
        return $txt;
    } //fim nfexml2txt

    /**
     *cxtt
     * 
     * @param type $dom 
     */
    private function cxtt($dom)
    {
        
        $txt = '';
        //carregar os grupos de dados possíveis da NFe
        $nfeProc = $dom->getElementsByTagName("nfeProc")->item(0);
        $infNFe = $dom->getElementsByTagName("infNFe")->item(0);
        $ide = $dom->getElementsByTagName("ide")->item(0);
        $refNFe = $dom->getElementsByTagName("refNFe");
        $refNF = $dom->getElementsByTagName("refNF");
        $refNFP = $dom->getElementsByTagName("refNFP");
        $refCTe = $dom->getElementsByTagName("refCTe");
        $refECF = $dom->getElementsByTagName("refECF");
        $emit = $dom->getElementsByTagName("emit")->item(0);
        $avulsa = $dom->getElementsByTagName("avulsa")->item(0);
        $dest = $dom->getElementsByTagName("dest")->item(0);
        $retirada = $dom->getElementsByTagName("retirada")->item(0);
        $entrega = $dom->getElementsByTagName("entrega")->item(0); 
		$autXML = $dom->getElementsByTagName("autXML")->item(0);
        $enderEmit = $dom->getElementsByTagName("enderEmit")->item(0);
        $enderDest = $dom->getElementsByTagName("enderDest")->item(0);
        $det = $dom->getElementsByTagName("det");
        $cobr = $dom->getElementsByTagName("cobr")->item(0);
        $pag = $dom->getElementsByTagName("pag");
        $infIntermed = $dom->getElementsByTagName("infIntermed");
        $ICMSTot = $dom->getElementsByTagName("ICMSTot")->item(0);
        $ISSQNtot = $dom->getElementsByTagName("ISSQNtot")->item(0);
        $retTrib = $dom->getElementsByTagName("retTrib")->item(0);
        $transp = $dom->getElementsByTagName("transp")->item(0);
        $infAdic = $dom->getElementsByTagName("infAdic")->item(0);
        $procRef = $dom->getElementsByTagName("procRef")->item(0);
        $exporta = $dom->getElementsByTagName("exporta")->item(0);
        $compra = $dom->getElementsByTagName("compra")->item(0);
        $cana = $dom->getElementsByTagName("cana")->item(0);
        $infProt = $dom->getElementsByTagName('infProt')->item(0);
        //A|versão do schema|id|
        $id = $infNFe->getAttribute("Id") ? $infNFe->getAttribute("Id") : '';
        $versao = str_pad($infNFe->getAttribute("versao"), 5, "0", STR_PAD_LEFT);
        $txt .= "A|$versao|$id|\r\n";
//B|cUF|cNF|NatOp|indPag|mod|serie|nNF|dhEmi|dhSaiEnt|hSaiEnt|tpNF|cMunFG|TpImp
//|TpEmis|cDV|tpAmb|finNFe|procEmi|VerProc|dhCont|xJust|
        $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $cNF = $ide->getElementsByTagName('cNF')->item(0)->nodeValue;
        $natOp = $ide->getElementsByTagName('natOp')->item(0)->nodeValue;
        $indPag = $ide->getElementsByTagName('indPag')->item(0)->nodeValue;
        $mod = $ide->getElementsByTagName('mod')->item(0)->nodeValue;
        $serie = $ide->getElementsByTagName('serie')->item(0)->nodeValue;
        $nNF = $ide->getElementsByTagName('nNF')->item(0)->nodeValue;
        if($versao == "2.00"){
            $dhEmi = $ide->getElementsByTagName('dEmi')->item(0)->nodeValue."T".$ide->getElementsByTagName('hSaiEnt')->item(0)->nodeValue;
            $dhSaiEnt = $ide->getElementsByTagName('dSaiEnt')->item(0)->nodeValue."T".$ide->getElementsByTagName('hSaiEnt')->item(0)->nodeValue;
        }else{
            $dhEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
            $dhSaiEnt = $ide->getElementsByTagName('dhSaiEnt')->item(0)->nodeValue;
        }
        $tpNF = $ide->getElementsByTagName('tpNF')->item(0)->nodeValue;
		$idDest = $ide->getElementsByTagName('idDest')->item(0)->nodeValue;
        $cMunFG = $ide->getElementsByTagName('cMunFG')->item(0)->nodeValue;
        $tpImp = $ide->getElementsByTagName('tpImp')->item(0)->nodeValue;
        $tpEmis = $ide->getElementsByTagName('tpEmis')->item(0)->nodeValue;
        $cDV = $ide->getElementsByTagName('cDV')->item(0)->nodeValue;
        $tpAmb = $ide->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        $finNFe = $ide->getElementsByTagName('finNFe')->item(0)->nodeValue;
		$indFinal = $ide->getElementsByTagName('indFinal')->item(0)->nodeValue;
		$indPres = $ide->getElementsByTagName('indPres')->item(0)->nodeValue;
        $procEmi = $ide->getElementsByTagName('procEmi')->item(0)->nodeValue;
        $indIntermed = $ide->getElementsByTagName('indIntermed')->item(0)->nodeValue;
        $verProc = $ide->getElementsByTagName('verProc')->item(0)->nodeValue;
        $dhCont = @$ide->getElementsByTagName('dhCont')->item(0)->nodeValue;
        $xJust = @$ide->getElementsByTagName('xJust')->item(0)->nodeValue;
        //$protNFe = $ide->getElementsByTagName('protNFe')->item(0)->nodeValue;
        //$txt .= "B|$cUF|$cNF|$natOp|$indPag|$mod|$serie|$nNF|$dhEmi|$dhSaiEnt|$tpNF|$idDest|$cMunFG|$tpImp|$tpEmis|$cDV|$tpAmb|$finNFe|$indFinal|$indPres|$procEmi|$verProc|$dhCont|$xJust|\r\n";
        $txt .= "B|$cUF|$cNF|$natOp|$mod|$serie|$nNF|$dhEmi|$dhSaiEnt|$tpNF|$idDest|$cMunFG|$tpImp|$tpEmis|$cDV|$tpAmb|$finNFe|$indFinal|$indPres|$indIntermed|$procEmi|$verProc|$dhCont|$xJust|\r\n";

        $dup = $dom->getElementsByTagName('dup');
        
//B13|refNFe|
        if (isset($refNFe)) {
            foreach ($refNFe as $n => $r) {
                $ref = !empty($refNFe->item($n)->nodeValue) ? $refNFe->item($n)->nodeValue : '';
                if ($ref == '') {
                    $txt .= "B13|$ref|\r\n";
                }
            }
        } //fim refNFe
//B14|cUF|AAMM(ano mês)|CNPJ|Mod|serie|nNF|
        if (isset($refNF)) {
            foreach ($refNF as $x => $k) {
                $cUF = !empty($refNF->item($x)->getElementsByTagName('cUF')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('cUF')->nodeValue : '';
                $AAMM = !empty($refNF->item($x)->getElementsByTagName('AAMM')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('AAMM')->nodeValue : '';
                $CNPJ = !empty($refNF->item($x)->getElementsByTagName('CNPJ')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('CNPJ')->nodeValue : '';
                $mod = !empty($refNF->item($x)->getElementsByTagName('mod')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('mod')->nodeValue : '';
                $serie = !empty($refNF->item($x)->getElementsByTagName('serie')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('serie')->nodeValue : '';
                $nNF = !empty($refNF->item($x)->getElementsByTagName('nNF')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('nNF')->nodeValue : '';
                $txt .= "B14|$cUF|$AAMM|$CNPJ|$mod|$serie|$nNF|\r\n";
            }
        } //fim refNF

        if (isset($infProt)) {
            //PROT|XNEmp|XPed|XCont|
            $tpAmbProt = !empty($infProt->getElementsByTagName("tpAmb")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("tpAmb")->item(0)->nodeValue : '';
            
            $chNFeProt = !empty($infProt->getElementsByTagName("chNFe")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("chNFe")->item(0)->nodeValue : '';
            $dhRecbtoProt = !empty($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue : '';
            $nProt = !empty($infProt->getElementsByTagName("nProt")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("nProt")->item(0)->nodeValue : '';
            $cStatProt = !empty($infProt->getElementsByTagName("cStat")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("cStat")->item(0)->nodeValue : '';
            $xMotivoProt = !empty($infProt->getElementsByTagName("xMotivo")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("xMotivo")->item(0)->nodeValue : '';
            
            $txt .= "PROT|$tpAmbProt|$chNFeProt|$dhRecbtoProt|$nProt|$cStatProt|$xMotivoProt\r\n";
        }
//BA02|refNFe|
        if (isset($refNFe)) {
            foreach ($refNFe as $n => $r) {
                $ref = !empty($refNFe->item($n)->nodeValue) ? $refNFe->item($n)->nodeValue : '';
                if ($ref == '') {
                    $txt .= "BA02|$ref|\r\n";
                }
            }
        } //fim refNFe
//BA03|cUF|AAMM(ano mês)|CNPJ|Mod|serie|nNF|
        if (isset($refNF)) {
            foreach ($refNF as $x => $k) {
                $cUF = !empty($refNF->item($x)->getElementsByTagName('cUF')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('cUF')->nodeValue : '';
                $AAMM = !empty($refNF->item($x)->getElementsByTagName('AAMM')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('AAMM')->nodeValue : '';
                $CNPJ = !empty($refNF->item($x)->getElementsByTagName('CNPJ')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('CNPJ')->nodeValue : '';
                $mod = !empty($refNF->item($x)->getElementsByTagName('mod')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('mod')->nodeValue : '';
                $serie = !empty($refNF->item($x)->getElementsByTagName('serie')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('serie')->nodeValue : '';
                $nNF = !empty($refNF->item($x)->getElementsByTagName('nNF')->nodeValue) ?
                        $refNF->item($x)->getElementsByTagName('nNF')->nodeValue : '';
                $txt .= "BA03|$cUF|$AAMM|$CNPJ|$mod|$serie|$nNF|\r\n";
            }
        } //fim refNF

        // BA13|CNPJ|
        // BA14|CPF|
        if (isset($refNFP)) {
            foreach ($refNFP as $x => $k) {
                $cUF = !empty($refNFP->item($x)->getElementsByTagName('cUF')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('cUF')->nodeValue : '';
                $AAMM = !empty($refNFP->item($x)->getElementsByTagName('AAMM')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('AAMM')->nodeValue : '';
                $IE = !empty($refNFP->item($x)->getElementsByTagName('IE')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('IE')->nodeValue : '';
                $mod = !empty($refNFP->item($x)->getElementsByTagName('mod')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('mod')->nodeValue : '';
                $serie = !empty($refNFP->item($x)->getElementsByTagName('serie')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('serie')->nodeValue : '';
                $nNF = !empty($refNFP->item($x)->getElementsByTagName('nNF')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('nNF')->nodeValue : '';
				$refCTe = !empty($refNFP->item($x)->getElementsByTagName('refCTe')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('refCTe')->nodeValue : '';
                $CPF = !empty($refNFP->item($x)->getElementsByTagName('CPF')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('CPF')->nodeValue : '';
                $CNPJ = !empty($refNFP->item($x)->getElementsByTagName('CNPJ')->nodeValue) ?
                        $refNFP->item($x)->getElementsByTagName('CNPJ')->nodeValue : '';
                $txt .= "BA10|$cUF|$AAMM|$IE|$mod|$serie|$nNF|\r\n";
                if ($CPF != '') {
                    $txt .= "BA14|$CPF|\r\n";
                } else {
                    $txt .= "BA13|$CNPJ|\r\n";
                }
            }
        } //fim refNFP
        //B20i|refCTe|
/*        if (isset($refCTe)) {
            foreach ($refCTe as $x => $k) {
                $ref = !empty($refCTe->item($n)->nodeValue) ? $refCTe->item($n)->nodeValue : '';
                $txt .= "B20i|$ref|\r\n";
            }
        } //fim refCTE*/
        //BA20|mod|nECF|nCOO|
        if (isset($refECF)) {
            foreach ($refECF as $x => $k) {
                $mod = !empty($refECF->item($x)->getElementsByTagName('mod')->nodeValue) ?
                        $refECF->item($x)->getElementsByTagName('mod')->nodeValue : '';
                $nECF = !empty($refECF->item($x)->getElementsByTagName('nECF')->nodeValue) ?
                        $refECF->item($x)->getElementsByTagName('nECF')->nodeValue : '';
                $nCOO = !empty($refECF->item($x)->getElementsByTagName('nCOO')->nodeValue) ?
                        $refECF->item($x)->getElementsByTagName('nCOO')->nodeValue : '';
                $txt .= "BA20|$mod|$nECF|$nCOO|\r\n";
            }
        } //fim refECF
        //C|XNome|XFant|IE|IEST|IM|CNAE|CRT|
        // C02|CNPJ|
        // C02a|CPF|
        $xNome = !empty($emit->getElementsByTagName('xNome')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('xNome')->item(0)->nodeValue : '';
        $xFant = !empty($emit->getElementsByTagName('xFant')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('xFant')->item(0)->nodeValue : '';
        $IE = !empty($emit->getElementsByTagName('IE')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('IE')->item(0)->nodeValue : '';
        $IEST = !empty($emit->getElementsByTagName('IEST')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('IEST')->item(0)->nodeValue : '';
        $IM = !empty($emit->getElementsByTagName('IM')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('IM')->item(0)->nodeValue : '';
        $CNAE = !empty($emit->getElementsByTagName('CNAE')->item(0)->nodeValue)
                ? $emit->getElementsByTagName('CNAE')->item(0)->nodeValue : '';
        $CRT = !empty($emit->getElementsByTagName('CRT')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('CRT')->item(0)->nodeValue : '';
        $CNPJ = !empty($emit->getElementsByTagName('CNPJ')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue : '';
        $CPF = !empty($emit->getElementsByTagName('CPF')->item(0)->nodeValue) ?
                $emit->getElementsByTagName('CPF')->item(0)->nodeValue : '';
        $txt .= "C|$xNome|$xFant|$IE|$IEST|$IM|$CNAE|$CRT|\r\n";
        if ($CPF != '') {
            $txt .= "C02a|$CPF|\r\n";
        } else {
            $txt .= "C02|$CNPJ|\r\n";
        }
        //C05|XLgr|Nro|Cpl|Bairro|CMun|XMun|UF|CEP|cPais|xPais|fone|
        $xLgr = !empty($enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
        $nro = !empty($enderEmit->getElementsByTagName("nro")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("nro")->item(0)->nodeValue : '';
        $xCpl = !empty($enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("xCpl")->item(0)->nodeValue : '';
        $xBairro = !empty($enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
        $cMun = !empty($enderEmit->getElementsByTagName("cMun")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("cMun")->item(0)->nodeValue : '';
        $xMun = !empty($enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("xMun")->item(0)->nodeValue : '';
        $UF = !empty($enderEmit->getElementsByTagName("UF")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("UF")->item(0)->nodeValue : '';
        $CEP = !empty($enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("CEP")->item(0)->nodeValue : '';
        $cPais = !empty($enderEmit->getElementsByTagName("cPais")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("cPais")->item(0)->nodeValue : '';
        $xPais = !empty($enderEmit->getElementsByTagName("xPais")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("xPais")->item(0)->nodeValue : '';
        $fone = !empty($enderEmit->getElementsByTagName("fone")->item(0)->nodeValue) ?
                $enderEmit->getElementsByTagName("fone")->item(0)->nodeValue : '';
        $txt .= "C05|$xLgr|$nro|$xCpl|$xBairro|$cMun|$xMun|$UF|$CEP|$cPais|$xPais|$fone|\r\n";

        //D|CNPJ|xOrgao|matr|xAgente|fone|UF|nDAR|dEmi|vDAR|repEmi|dPag|
        if (isset($avulsa)) {
            $CNPJ = !empty($avulsa->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $xOrgao = !empty($avulsa->getElementsByTagName("xOrgao")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("xOrgao")->item(0)->nodeValue : '';
            $matr = !empty($avulsa->getElementsByTagName("matr")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("matr")->item(0)->nodeValue : '';
            $xAgente = !empty($avulsa->getElementsByTagName("xAgente")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("xAgente")->item(0)->nodeValue : '';
            $fone = !empty($avulsa->getElementsByTagName("fone")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("fone")->item(0)->nodeValue : '';
            $UF = !empty($avulsa->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("UF")->item(0)->nodeValue : '';
            $nDAR = !empty($avulsa->getElementsByTagName("nDAR")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("nDAR")->item(0)->nodeValue : '';
            $dEmi = !empty($avulsa->getElementsByTagName("dEmi")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("dEmi")->item(0)->nodeValue : '';
            $vDAR = !empty($avulsa->getElementsByTagName("vDAR")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("vDAR")->item(0)->nodeValue : '';
                    $vDAR = number_format($vDAR,2,",",".");
            $repEmi = !empty($avulsa->getElementsByTagName("repEmi")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("repEmi")->item(0)->nodeValue : '';
            $dPag = !empty($avulsa->getElementsByTagName("dPag")->item(0)->nodeValue) ?
                    $avulsa->getElementsByTagName("dPag")->item(0)->nodeValue : '';
            $txt .= "D|$CNPJ|$xOrgao|$matr|$xAgente|$fone|$UF|$nDAR|$dEmi|$vDAR|$repEmi|$dPag|\r\n";
        } //fim avulsa
        //E|xNome|IE|ISUF|email|
        // E02|CNPJ|
        // E03|CPF|
        if (isset($dest)) {
            $xNome = !empty($dest->getElementsByTagName("xNome")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("xNome")->item(0)->nodeValue : '';
                    $indIEDest = !empty($dest->getElementsByTagName("indIEDest")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("indIEDest")->item(0)->nodeValue : '';
            $IE = !empty($dest->getElementsByTagName("IE")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("IE")->item(0)->nodeValue : '';
            $ISUF = !empty($dest->getElementsByTagName("ISUF")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("ISUF")->item(0)->nodeValue : '';
            $email = !empty($dest->getElementsByTagName("email")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("email")->item(0)->nodeValue : '';
            $CNPJ = !empty($dest->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $CPF = !empty($dest->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("CPF")->item(0)->nodeValue : '';
                    $idEstrangeiro = !empty($dest->getElementsByTagName("idEstrangeiro")->item(0)->nodeValue) ?
                    $dest->getElementsByTagName("idEstrangeiro")->item(0)->nodeValue : '';
            $txt .= "E|$xNome|$indIEDest|$IE|$ISUF|$email|\r\n";
            if ($CPF != '') {
                $txt .= "E03|$CPF|\r\n";
            } elseif ($CNPJ != '') {
                $txt .= "E02|$CNPJ|\r\n";
            } elseif ($idEstrangeiro != '') {
                $txt .= "E02a|$idEstrangeiro|\r\n";
            }
        }
        //E05|xLgr|nro|xCpl|xBairro|cMun|xMun|UF|CEP|cPais|xPais|fone|
        if (isset($enderDest)) {
            $xLgr = !empty($enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
            $nro = !empty($enderDest->getElementsByTagName("nro")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("nro")->item(0)->nodeValue : '';
            $xCpl = !empty($enderDest->getElementsByTagName("xCpl")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("xCpl")->item(0)->nodeValue : '';
            $xBairro = !empty($enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
            $cMun = !empty($enderDest->getElementsByTagName("cMun")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("cMun")->item(0)->nodeValue : '';
            $xMun = !empty($enderDest->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("xMun")->item(0)->nodeValue : '';
            $UF = !empty($enderDest->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("UF")->item(0)->nodeValue : '';
            $CEP = !empty($enderDest->getElementsByTagName("CEP")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("CEP")->item(0)->nodeValue : '';
            $cPais = !empty($enderDest->getElementsByTagName("cPais")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("cPais")->item(0)->nodeValue : '';
            $xPais = !empty($enderDest->getElementsByTagName("xPais")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("xPais")->item(0)->nodeValue : '';
            $fone = !empty($enderDest->getElementsByTagName("fone")->item(0)->nodeValue) ?
                    $enderDest->getElementsByTagName("fone")->item(0)->nodeValue : '';
            $txt .= "E05|$xLgr|$nro|$xCpl|$xBairro|$cMun|$xMun|$UF|$CEP|$cPais|$xPais|$fone|\r\n";
        }
        if (isset($retirada)) {
            $CNPJ = !empty($retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $CPF = !empty($retirada->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("CPF")->item(0)->nodeValue : '';
            $xLgr = !empty($retirada->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
            $nro = !empty($retirada->getElementsByTagName("nro")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("nro")->item(0)->nodeValue : '';
            $xCpl = !empty($retirada->getElementsByTagName("xCpl")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("xCpl")->item(0)->nodeValue : '';
            $xBairro = !empty($retirada->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
            $cMun = !empty($retirada->getElementsByTagName("cMun")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("cMun")->item(0)->nodeValue : '';
            $xMun = !empty($retirada->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("xMun")->item(0)->nodeValue : '';
            $UF = !empty($retirada->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $retirada->getElementsByTagName("UF")->item(0)->nodeValue : '';
            $txt .= "F|$xLgr|$nro|$xCpl|$xBairro|$cMun|$xMun|$UF|\r\n";
            if ($CPF != '') {
                $txt .= "$F02a|$CPF|\r\n";
            } else {
                $txt .= "$F02|$CNPJ|\r\n";
            }
        } //fim da retirada

        if (isset($entrega)) {
            $CNPJ = !empty($entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $CPF = !empty($entrega->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $xLgr = !empty($entrega->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
            $nro = !empty($entrega->getElementsByTagName("nro")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("nro")->item(0)->nodeValue : '';
            $xCpl = !empty($entrega->getElementsByTagName("xCpl")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("xCpl")->item(0)->nodeValue : '';
            $xBairro = !empty($entrega->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
            $cMun = !empty($entrega->getElementsByTagName("cMun")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("cMun")->item(0)->nodeValue : '';
            $xMun = !empty($entrega->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("xMun")->item(0)->nodeValue : '';
            $UF = !empty($entrega->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $entrega->getElementsByTagName("UF")->item(0)->nodeValue : '';
            $txt .= "G|$xLgr|$nro|$xCpl|$xBairro|$cMun|$xMun|$UF|\r\n";
            if ($CPF != '') {
                $txt .= "G02a|$CPF|\r\n";
            } else {
                $txt .= "G02|$CNPJ|\r\n";
            }
        } //fim entrega
		
		if (isset($autXML)) {
            $CNPJ = !empty($autXML->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $autXML->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $CPF = !empty($autXML->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                    $autXML->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $txt .= "GA|\r\n";
            if ($CPF != '') {
                $txt .= "GA03|$CPF|\r\n";
            } else {
                $txt .= "GA02|$CNPJ|\r\n";
            }
        } //fim autXML
		
        //monta dados das duplicatas
            if (isset($dup)) {
                foreach ($dup as $n => $duplicata) {
                    //Y07|NDup|DVenc|VDup|
                    $nDup = !empty($dup->item($n)->getElementsByTagName("nDup")->item(0)->nodeValue) ?
                            $dup->item($n)->getElementsByTagName("nDup")->item(0)->nodeValue : '';
                    $dVenc = !empty($dup->item($n)->getElementsByTagName("dVenc")->item(0)->nodeValue) ?
                            $dup->item($n)->getElementsByTagName("dVenc")->item(0)->nodeValue : '';
                    $vDup = !empty($dup->item($n)->getElementsByTagName("vDup")->item(0)->nodeValue) ?
                            $dup->item($n)->getElementsByTagName("vDup")->item(0)->nodeValue : '';
                            $vDup = number_format($vDup,2,",",".");
                    $txt .= "Y07|$nDup|$dVenc|$vDup|\r\n";
                } //fim foreach
            } //fim dup
            //
            // Guilherme: alterado a pedidos do Anselmo
            
        //monta dados de Transportes
        if (isset($transp)) {
            //instancia sub grupos da tag transp
            $transporta = $dom->getElementsByTagName("transporta")->item(0);
            $retTransp = $dom->getElementsByTagName("retTransp")->item(0);
            $veicTransp = $dom->getElementsByTagName("veicTransp")->item(0);
            $reboque = $dom->getElementsByTagName("reboque");
            $vol = $dom->getElementsByTagName("vol");
            //X|ModFrete|
            $modFrete = !empty($transp->getElementsByTagName("modFrete")->item(0)->nodeValue) ?
            $transp->getElementsByTagName("modFrete")->item(0)->nodeValue : '0';
            $txt .= "X|$modFrete|\r\n";
            if (isset($transporta)) {
                $CNPJ = !empty($transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
                $CPF = !empty($transporta->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("CPF")->item(0)->nodeValue : '';
                $IE = !empty($transporta->getElementsByTagName("IE")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("IE")->item(0)->nodeValue : '';
                $xNome = !empty($transporta->getElementsByTagName("xNome")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("xNome")->item(0)->nodeValue : '';
                $xEnder = !empty($transporta->getElementsByTagName("xEnder")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("xEnder")->item(0)->nodeValue : '';
                $xMun = !empty($transporta->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("xMun")->item(0)->nodeValue : '';
                $UF = !empty($transporta->getElementsByTagName("UF")->item(0)->nodeValue) ?
                        $transporta->getElementsByTagName("UF")->item(0)->nodeValue : '';
                //X03|XNome|IE|XEnder|UF|XMun|
                // X04|CNPJ|
                // X05|CPF|
                $txt .= "X03|$xNome|$IE|$xEnder|$UF|$xMun|\r\n";
                if ($CNPJ != '') {
                    $txt .= "X04|$CNPJ|\r\n";
                } else {
                    $txt .= "X05|$CPF|\r\n";
                }
            } // fim transporta
            //monta dados da retenção tributária de transporte
            if (isset($retTransp)) {
                $vServ = !empty($retTransp->getElementsByTagName("vServ")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("vServ")->item(0)->nodeValue : '';
                        $vServ = number_format($vServ,2,",",".");
                $vBCRet = !empty($retTransp->getElementsByTagName("vBCRet")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("vBCRet")->item(0)->nodeValue : '';
                        $vBCRet = number_format($vBCRet,2,",",".");
                $pICMSRet = !empty($retTransp->getElementsByTagName("pICMSRet")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("pICMSRet")->item(0)->nodeValue : '';
                        $pICMSRet = number_format($vICMSRet,4,",",".");
                $vICMSRet = !empty($retTransp->getElementsByTagName("vICMSRet")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("vICMSRet")->item(0)->nodeValue : '';
                        $vICMSRet = number_format($vICMSRet,2,",",".");
                $CFOP = !empty($retTransp->getElementsByTagName("CFOP")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("CFOP")->item(0)->nodeValue : '';
                $cMunFG = !empty($retTransp->getElementsByTagName("cMunFG")->item(0)->nodeValue) ?
                        $retTransp->getElementsByTagName("cMunFG")->item(0)->nodeValue : '';
                //X11|VServ|VBCRet|PICMSRet|VICMSRet|CFOP|CMunFG|
                $txt .= "X11|$vServ|$vBCRet|$pICMSRet|$vICMSRet|$CFOP|$cMunFG|\r\n";
            } // fim rettransp
            //monta dados de identificação dos veiculos utilizados no transporte
            if (isset($veicTransp)) {
                //X18|Placa|UF|RNTC|
                $placa = !empty($veicTransp->getElementsByTagName("placa")->item(0)->nodeValue) ?
                        $veicTransp->getElementsByTagName("placa")->item(0)->nodeValue : '';
                $UF = !empty($veicTransp->getElementsByTagName("UF")->item(0)->nodeValue) ?
                        $veicTransp->getElementsByTagName("UF")->item(0)->nodeValue : '';
                $RNTC = !empty($veicTransp->getElementsByTagName("RNTC")->item(0)->nodeValue) ?
                        $veicTransp->getElementsByTagName("RNTC")->item(0)->nodeValue : '';
                $txt .= "X18|$placa|$UF|$RNTC|\r\n";
            } //fim veicTransp
            //monta dados de identificação dos reboques utilizados no transporte
            if (isset($reboque)) {
                foreach ($reboque as $n => $reb) {
                    $placa = !empty($reboque->item($n)->getElementsByTagName("placa")->item(0)->nodeValue) ?
                            $reboque->item($n)->getElementsByTagName("placa")->item(0)->nodeValue : '';
                    $UF = !empty($reboque->item($n)->getElementsByTagName("UF")->item(0)->nodeValue) ?
                            $reboque->item($n)->getElementsByTagName("UF")->item(0)->nodeValue : '';
                    $RNTC = !empty($reboque->item($n)->getElementsByTagName("RNTC")->item(0)->nodeValue) ?
                            $reboque->item($n)->getElementsByTagName("RNTC")->item(0)->nodeValue : '';
					$vagao = !empty($reboque->item($n)->getElementsByTagName("vagao")->item(0)->nodeValue) ?
                            $reboque->item($n)->getElementsByTagName("vagao")->item(0)->nodeValue : '';
					$balsa = !empty($reboque->item($n)->getElementsByTagName("balsa")->item(0)->nodeValue) ?
                            $reboque->item($n)->getElementsByTagName("balsa")->item(0)->nodeValue : '';
                    //X22|Placa|UF|RNTC|
                    $txt .= "X22|$placa|$UF|$RNTC|$vagao|$balsa|\r\n";
                } //fim foreach
            } //fim reboque
            //monta dados dos volumes transportados
            if (isset($vol)) {
                foreach ($vol as $n => $volumes) {
                    //X26|QVol|Esp|Marca|NVol|PesoL|PesoB|
                    $qVol = !empty($vol->item($n)->getElementsByTagName("qVol")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("qVol")->item(0)->nodeValue : '';
                    $esp = !empty($vol->item($n)->getElementsByTagName("esp")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("esp")->item(0)->nodeValue : '';
                    $marca = !empty($vol->item($n)->getElementsByTagName("marca")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("marca")->item(0)->nodeValue : '';
                    $nVol = !empty($vol->item($n)->getElementsByTagName("nVol")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("nVol")->item(0)->nodeValue : '';
                    $pesoL = !empty($vol->item($n)->getElementsByTagName("pesoL")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("pesoL")->item(0)->nodeValue : '';
                            $pesoL = number_format($pesoL,3,",",".");
                    $pesoB = !empty($vol->item($n)->getElementsByTagName("pesoB")->item(0)->nodeValue) ?
                            $vol->item($n)->getElementsByTagName("pesoB")->item(0)->nodeValue : '';
                            $pesoB = number_format($pesoB,3,",",".");
                    $lacres = $vol->item($n)->getElementsByTagName("lacres")->item(0);
                    $txt .= "X26|$qVol|$esp|$marca|$nVol|$pesoL|$pesoB|\r\n";
                    //monta dados dos lacres utilizados
                    if (isset($lacres)) {
                        foreach ($lacres as $n => $lac) {
                            $nLacre = !empty($lacres->item($n)->getElementsByTagName("nLacre")->item(0)->nodeValue) ?
                                    $lacres->item($n)->getElementsByTagName("nLacre")->item(0)->nodeValue : '';
                            //X33|NLacre|
                            $txt .= "X33|$nLacre|\r\n";
                        } //fim foreach lacre
                    } //fim lacres
                } //fim foreach volumes
            } //fim vol
        }
        
        
            // Guilherme: alterada posição conforme necessidae Anselmo
        //W|
        $txt .= "W|\r\n";
        $vBC = !empty($ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
        $vICMS = !empty($ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue : '';
                $vICMS = number_format($vICMS,2,",",".");
	$vICMSDeson = !empty($ICMSTot->getElementsByTagName("vICMSDeson")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vICMSDeson")->item(0)->nodeValue : '';
                $vICMSDeson = $vICMSDeson = number_format($vBC,2,",",".");
        $vBCST = !empty($ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue : '';
                $vBCST = number_format($vBCST,2,",",".");
        $vST = !empty($ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue : '';
                $vST = number_format($vST,2,",",".");
        $vProd = !empty($ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue : '';
                $vProd = number_format($vProd,2,",",".");
        $vFrete = !empty($ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue : '';
                $vFrete = number_format($vFrete,2,",",".");
        $vSeg = !empty($ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue : '';
                $vSeg = number_format($vSeg,2,",",".");
        $vDesc = !empty($ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue : '';
                $vDesc = number_format($vDesc,2,",",".");
        $vII = !empty($ICMSTot->getElementsByTagName("vII")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vII")->item(0)->nodeValue : '';
                $vII = number_format($vII,2,",",".");
        $vIPI = !empty($ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue : '';
                $vIPI = number_format($vIPI,2,",",".");
        $vPIS = !empty($ICMSTot->getElementsByTagName("vPIS")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vPIS")->item(0)->nodeValue : '';
                $vPIS = number_format($vPIS,2,",",".");
        $vCOFINS = !empty($ICMSTot->getElementsByTagName("vCOFINS")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vCOFINS")->item(0)->nodeValue : '';
                $vCOFINS = number_format($vCOFINS,2,",",".");
        $vOutro = !empty($ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue : '';
                $vOutro = number_format($vOutro,2,",",".");
        $vNF = !empty($ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue : '';
                $vNF = number_format($vNF,2,",",".");
        //lei da transparencia 12.741/12
        //Nota Técnica 2013/003
        $vTotTrib = !empty($ICMSTot->getElementsByTagName("vTotTrib")->item(0)->nodeValue) ?
                $ICMSTot->getElementsByTagName("vTotTrib")->item(0)->nodeValue : '';
                $vTotTrib = number_format($vTotTrib,2,",",".");
        /*if ($vTotTrib == '') {
            //W02|vBC|vICMS|vBCST|vST|vProd|vFrete|vSeg|vDesc|vII|vIPI|vPIS|vCOFINS|vOutro|vNF|
            $txt .= "W02|$vBC|$vICMS|$vICMSDeson|$vBCST|$vST|$vProd|$vFrete|$vSeg|$vDesc|$vII|$vIPI|$vPIS|$vCOFINS|$vOutro|$vNF|\r\n";
        } else {*/
            //W02|vBC|vICMS|vBCST|vST|vProd|vFrete|vSeg|vDesc|vII|vIPI|vPIS|vCOFINS|vOutro|vNF|
            $txt .= "W02|$vBC|$vICMS|$vICMSDeson|$vBCST|$vST|$vProd|$vFrete|$vSeg|$vDesc|$vII|$vIPI|$vPIS|$vCOFINS|$vOutro|$vNF|$vTotTrib|\r\n";
        //}
        // monta dados do total de ISS
        if (isset($ISSQNtot)) {
            //W17|VServ|VBC|VISS|VPIS|VCOFINS|
            $vServ = !empty($ISSQNtot->getElementsByTagName("vServ")->item(0)->nodeValue) ?
                    $ISSQNtot->getElementsByTagName("vServ")->item(0)->nodeValue : '';
                    $vServ = number_format($vServ,2,",",".");
            $vBC = !empty($ISSQNtot->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                    $ISSQNtot->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                    $vBC = number_format($vBC,2,",",".");
            $vISS = !empty($ISSQNtot->getElementsByTagName("vISS")->item(0)->nodeValue) ?
                    $ISSQNtot->getElementsByTagName("vISS")->item(0)->nodeValue : '';
                    $vISS = number_format($vISS,2,",",".");
            $vPIS = !empty($ISSQNtot->getElementsByTagName("vPIS")->item(0)->nodeValue) ?
                    $ISSQNtot->getElementsByTagName("vPIS")->item(0)->nodeValue : '';
                    $vPIS = number_format($vPIS,2,",",".");
            $vCOFINS = !empty($ISSQNtot->getElementsByTagName("vCOFINS")->item(0)->nodeValue) ?
                    $ISSQNtot->getElementsByTagName("vCOFINS")->item(0)->nodeValue : '';
                    $vCOFINS = number_format($vCOFINS,2,",",".");
			$dCompet = !empty($ISSQNtot->getElementsByTagName("dCompet")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("dCompet")->item(0)->nodeValue : '';
			$vDeducao = !empty($ISSQNtot->getElementsByTagName("vDeducao")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("vDeducao")->item(0)->nodeValue : '';
                            $vDeducao = number_format($vDeducao,2,",",".");
			$vOutro = !empty($ISSQNtot->getElementsByTagName("vOutro")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("vOutro")->item(0)->nodeValue : '';
                            $vOutro = number_format($vOutro,2,",",".");
			$vDescIncond = !empty($ISSQNtot->getElementsByTagName("vDescIncond")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("vDescIncond")->item(0)->nodeValue : '';
                            $vDescIncond = number_format($vDescIncond,2,",",".");
			$vDescCond = !empty($ISSQNtot->getElementsByTagName("vDescCond")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("vDescCond")->item(0)->nodeValue : '';
                            $vDescCond = number_format($vDescCond,2,",",".");
			$vISSRet = !empty($ISSQNtot->getElementsByTagName("vISSRet")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("vISSRet")->item(0)->nodeValue : '';
                            $vISSQNtot = number_format($vISSQNtot,2,",",".");
			$cRegTrib = !empty($ISSQNtot->getElementsByTagName("cRegTrib")->item(0)->nodeValue) ?
                            $ISSQNtot->getElementsByTagName("cRegTrib")->item(0)->nodeValue : '';
            $txt .= "W17|$vServ|$vBC|$vISS|$vPIS|$vCOFINS|$dCompet|$vDeducao|$vOutro|$vDescIncond|$vDescCond|$vISSRet|$cRegTrib\r\n";
        } //fim ISSQNtot
        //monta dados da Retenção de tributos
        if (isset($retTrib)) {
            //W23|VRetPIS|VRetCOFINS|VRetCSLL|VBCIRRF|VIRRF|VBCRetPrev|VRetPrev|
            $vRetPIS = !empty($retTrib->getElementsByTagName("vRetPIS")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vRetPIS")->item(0)->nodeValue : '';
                    $vRetPis = number_format($vRetPis,2,",",".");
            $vRetCOFINS = !empty($retTrib->getElementsByTagName("vRetCOFINS")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vRetCOFINS")->item(0)->nodeValue : '';
                    $vRetCOFINS = number_format($vRetCOFINS,2,",",".");
            $vRetCSLL = !empty($retTrib->getElementsByTagName("vRetCSLL")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vRetCSLL")->item(0)->nodeValue : '';
                    $vRetCSLL = number_format($vRetCSLL,2,",",".");
            $vBCIRRF = !empty($retTrib->getElementsByTagName("vBCIRRF")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vBCIRRF")->item(0)->nodeValue : '';
                    $vBCIRRF = number_format($vBCIRRF,2,",",".");
            $vIRRF = !empty($retTrib->getElementsByTagName("vIRRF")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vIRRF")->item(0)->nodeValue : '';
                    $vIRRF = number_format($vIRRF,2,",",".");
            $vBCRetPrev = !empty($retTrib->getElementsByTagName("vBCRetPrev")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vBCRetPrev")->item(0)->nodeValue : '';
                    $vBCRetPrev = number_format($vBCRetPrev,2,",",".");
            $vRetPrev = !empty($retTrib->getElementsByTagName("vRetPrev")->item(0)->nodeValue) ?
                    $retTrib->getElementsByTagName("vRetPrev")->item(0)->nodeValue : '';
                    $vRetPrev = number_format($vRetPrev,2,",",".");
            $txt .= "W23|$vRetPIS|$vRetCOFINS|$vRetCSLL|$vBCIRRF|$vIRRF|$vBCRetPrev|$vRetPrev|\r\n";
        }


        //carrega dados dos itens
        $txt .= $this->getItens($det);

        
        //monta dados de cobrança
        if (isset($cobr)) {
            //instancia sub grupos da tag cobr
            $fat = $dom->getElementsByTagName('fat')->item(0);
            $txt .= "Y|\r\n";
            //monta dados da fatura
            if (isset($fat)) {
                //Y02|NFat|VOrig|VDesc|VLiq|
                $nFat = !empty($fat->getElementsByTagName("nFat")->item(0)->nodeValue) ?
                        $fat->getElementsByTagName("nFat")->item(0)->nodeValue : '';
                $vOrig = !empty($fat->getElementsByTagName("vOrig")->item(0)->nodeValue) ?
                        $fat->getElementsByTagName("vOrig")->item(0)->nodeValue : '';
                        $vOrig = number_format($vOrig,2,",",".");
                $vDesc = !empty($fat->getElementsByTagName("vDesc")->item(0)->nodeValue) ?
                        $fat->getElementsByTagName("vDesc")->item(0)->nodeValue : '';
                        $vDesc = number_format($vDesc,2,",",".");
                $vLiq = !empty($fat->getElementsByTagName("vLiq")->item(0)->nodeValue) ?
                        $fat->getElementsByTagName("vLiq")->item(0)->nodeValue : '';
                        $vLiq = number_format($vLiq,2,",",".");
                $txt .= "Y02|$nFat|$vOrig|$vDesc|$vLiq|\r\n";
            } //fim fat
            // Y07 MUDADO PARA ANTES DO REGISTRO I
        } //fim cobr
        // formas de pagamento
        if (isset($pag)) {
            //Y02|NFat|VOrig|VDesc|VLiq|

            foreach ($pag as $n => $oC) {

                $tPag = !empty($pag->item($n)->getElementsByTagName("tPag")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("tPag")->item(0)->nodeValue : '';

                $vPag = !empty($pag->item($n)->getElementsByTagName("vPag")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("vPag")->item(0)->nodeValue : '';
                $vPag = number_format($vPag,2,",",".");
                
                $xPag = !empty($pag->item($n)->getElementsByTagName("xPag")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("xPag")->item(0)->nodeValue : '';
                

                $card = $pag->item($n)->getElementsByTagName("card");

                $tpIntegra = !empty($pag->item($n)->getElementsByTagName("tpIntegra")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("tpIntegra")->item(0)->nodeValue : '';
                $CNPJ = !empty($pag->item($n)->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
                $tBand = !empty($pag->item($n)->getElementsByTagName("tBand")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("tBand")->item(0)->nodeValue : '';
                $cAut = !empty($pag->item($n)->getElementsByTagName("cAut")->item(0)->nodeValue) ?
                            $pag->item($n)->getElementsByTagName("cAut")->item(0)->nodeValue : '';

                //$txt .= "YA|$tPag|$vPag|\r\nYA04|$tpIntegra|$CNPJ|$tBand|$cAut|\r\n";
                $txt .= "YA|$indPag|$tPag|$xPag|$vPag|\r\nYA04|$tpIntegra|$CNPJ|$tBand|$cAut|\r\n";
                
                
                
            }//fim foreach

        } //fim cobr
        
        if(isset( $infIntermed )){
                $CNPJ = !empty($pag->item($n)->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                        $pag->item($n)->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
                $idCadIntTran = !empty($pag->item($n)->getElementsByTagName("idCadIntTran")->item(0)->nodeValue) ?
                        $pag->item($n)->getElementsByTagName("idCadIntTran")->item(0)->nodeValue : '';
                
                
                $txt .= "YB01|$CNPJ|$idCadIntTran|\r\n";
        }
        
        
        //monta dados das informações adicionais da NFe
        if (isset($infAdic)) {
            //instancia sub grupos da tag infAdic
            $obsCont = $infAdic->getElementsByTagName('obsCont');
            $obsFisco = $infAdic->getElementsByTagName('obsFisco');
            $procRef = $infAdic->getElementsByTagName('procRef');

            //Z|InfAdFisco|InfCpl|
            $infAdFisco = !empty($infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue) ?
                    $infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue : '';
            $infCpl = !empty($infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue) ?
                    $infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue : '';
            $txt .= "Z|$infAdFisco|".utf8_encode($infCpl)."|\r\n"; 


            // ALTERAÇÃO "urldecode" EDUARDO 23/04/2015
            //Antes: $txt .= "Z|$infAdFisco|$infCpl|\r\n"; 

            //monta dados de observaçoes da NFe
            if (isset($obsCont)) {
                foreach ($obsCont as $n => $oC) {
                    //Z04|XCampo|XTexto|
                    /* vem por atributo 
                    $xCampo = !empty($obsCont->item($n)->getElementsByTagName("xCampo")->item(0)->nodeValue) ?
                    $obsCont->item($n)->getElementsByTagName("xCampo")->item(0)->nodeValue : '';
                    */
                    $xCampo = $obsCont->item($n)->getAttribute("xCampo") ? $obsCont->item($n)->getAttribute("xCampo") : ''; //gjps
                    $xTexto = !empty($obsCont->item($n)->getElementsByTagName("xTexto")->item(0)->nodeValue) ?
                            $obsCont->item($n)->getElementsByTagName("xTexto")->item(0)->nodeValue : '';
                    $txt .= "Z04|$xCampo|$xTexto|\r\n";
                }//fim foreach
            } //fim obsCont
            //monta dados dos processos
            if (isset($obsFisco)) {
                foreach ($obsFisco as $n => $pR) {
                    //Z07|XCampo|XTexto|
                    $xCampo = !empty($obsFisco->item($n)->getElementsByTagName("xCampo")->item(0)->nodeValue) ?
                            $obsFisco->item($n)->getElementsByTagName("xCampo")->item(0)->nodeValue : '';
                    $xTexto = !empty($obsFisco->item($n)->getElementsByTagName("xTexto")->item(0)->nodeValue) ?
                            $obsFisco->item($n)->getElementsByTagName("xTexto")->item(0)->nodeValue : '';
                    $txt .= "Z07|$xCampo|$xTexto|\r\n";
                } //fim foreach
            } //fim procRef
            //monta dados dos processos
            if (isset($procRef)) {
                foreach ($procRef as $n => $pR) {
                    //Z10|NProc|IndProc|
                    $nProc = !empty($procRef->item($n)->getElementsByTagName("nProc")->item(0)->nodeValue) ?
                            $procRef->item($n)->getElementsByTagName("nProc")->item(0)->nodeValue : '';
                    $indProc = !empty($procRef->item($n)->getElementsByTagName("infProc")->item(0)->nodeValue) ?
                            $procRef->item($n)->getElementsByTagName("infProc")->item(0)->nodeValue : '';
                    $txt .= "Z10|$nProc|$indProc|\r\n";
                } //fim foreach
            } //fim procRef
        } //fim infAdic
        //monta dados de exportação
        if (isset($exporta)) {
            //ZA|UFSaidaPais|xLocExporta|xLocDespacho|
            $UFSaidaPais = !empty($exporta->getElementsByTagName("UFSaidaPais")->item(0)->nodeValue) ?
                    $exporta->getElementsByTagName("UFSaidaPais")->item(0)->nodeValue : '';
            $xLocExporta = !empty($exporta->getElementsByTagName("xLocExporta")->item(0)->nodeValue) ?
                    $exporta->getElementsByTagName("xLocExporta")->item(0)->nodeValue : '';
			$xLocDespacho = !empty($exporta->getElementsByTagName("xLocDespacho")->item(0)->nodeValue) ?
                    $exporta->getElementsByTagName("xLocDespacho")->item(0)->nodeValue : '';
            $txt .= "ZA|$UFEmbarq|$xLocEmbarq|$xLocDespacho|\r\n";
        } //fim exporta
        //monta dados de compra
        if (isset($compra)) {
            //ZB|XNEmp|XPed|XCont|
            $xNEmp = !empty($compra->getElementsByTagName("xNEmp")->item(0)->nodeValue) ?
                    $compra->getElementsByTagName("xNEmp")->item(0)->nodeValue : '';
            $xPed = !empty($compra->getElementsByTagName("xPed")->item(0)->nodeValue) ?
                    $compra->getElementsByTagName("xPed")->item(0)->nodeValue : '';
            $xCont = !empty($compra->getElementsByTagName("xCont")->item(0)->nodeValue) ?
                    $compra->getElementsByTagName("xCont")->item(0)->nodeValue : '';
            $txt .= "ZB|$xNEmp|$xPed|$xCont|\r\n";
        } //fim compra
        //monta dados de cana
        if (isset($cana)) {
            //ZC01|safra|ref|qTotMes|qTotAnt|qTotGer|vFor|vTotDed|vLiqFor|
            $forDia = $cana->getElementsByTagName('forDia');
            $deduc = $cana->getElementsByTagName('deduc');
            $safra = !empty($cana->getElementsByTagName("safra")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("safra")->item(0)->nodeValue : '';
            $ref = !empty($cana->getElementsByTagName("ref")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("ref")->item(0)->nodeValue : '';
            $qTotMes = !empty($cana->getElementsByTagName("qTotMes")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("qTotMes")->item(0)->nodeValue : '';
                    $qTotMes = number_format($qTotMes,10,",",".");
            $qTotAnt = !empty($cana->getElementsByTagName("qTotAnt")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("qTotAnt")->item(0)->nodeValue : '';
                    $qTotAnt = number_format($qTotAnt,10,",",".");
            $qTotGer = !empty($cana->getElementsByTagName("qTotGer")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("qTotGer")->item(0)->nodeValue : '';
                    $qTotGer = number_format($qTotGer,10,",",".");
            $vFor = !empty($cana->getElementsByTagName("vFor")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("vFpr")->item(0)->nodeValue : '';
                    $vFor = number_format($vFor,2,",",".");
            $vTotDed = !empty($cana->getElementsByTagName("vTotDed")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("vTotDed")->item(0)->nodeValue : '';
                    $vTotDed = number_format($qTotDed,2,",",".");
            $vLiqFor = !empty($cana->getElementsByTagName("vLiqFor")->item(0)->nodeValue) ?
                    $cana->getElementsByTagName("vLiqFor")->item(0)->nodeValue : '';
                    $vLiqFor = number_format($vLiqFor,2,",",".");
            $txt .= "ZC|$safra|$ref|$qTotMes|$qTotAnt|$qTotGer|$vFor|$vTotDed|$vLiqFor|\r\n";
            //monta dados fornecimento diario
            if (isset($forDia)) {
                foreach ($forDia as $n => $pR) {
                    //ZC04|dia|qtde|
                    $dia = !empty($forDia->item($n)->getElementsByTagName("dia")->item(0)->nodeValue) ?
                            $forDia->item($n)->getElementsByTagName("dia")->item(0)->nodeValue : '';
                    $qtde = !empty($forDia->item($n)->getElementsByTagName("qtde")->item(0)->nodeValue) ?
                            $forDia->item($n)->getElementsByTagName("qtde")->item(0)->nodeValue : '';
                            $qtde = number_format($qtde,10,",",".");
                    $txt .= "ZC04|$dia|$qtde|\r\n";
                } //fim foreach
            } //fim fordia
            //monta dados grupo deduções
            if (isset($deduc)) {
                foreach ($deduc as $n => $pR) {
                    //ZC10|xDed|vDed|
                    $xDed = !empty($deduc->item($n)->getElementsByTagName("xDed")->item(0)->nodeValue) ?
                            $deduc->item($n)->getElementsByTagName("xDed")->item(0)->nodeValue : '';
                    $vDed = !empty($deduc->item($n)->getElementsByTagName("vDed")->item(0)->nodeValue) ?
                            $deduc->item($n)->getElementsByTagName("vDed")->item(0)->nodeValue : '';
                            $vDed = number_format($vDed,2,",",".");
                    $txt .= "ZC10|$xDed|$vDed|\r\n";
                } //fim foreach
            } //fim deduc
        } //fim cana
        /* Prot inicio
        if (isset($infProt)) {
            //PROT|XNEmp|XPed|XCont|
            $tpAmbProt = !empty($infProt->getElementsByTagName("tpAmb")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("tpAmb")->item(0)->nodeValue : '';
            
            $chNFeProt = !empty($infProt->getElementsByTagName("chNFe")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("chNFe")->item(0)->nodeValue : '';
            $dhRecbtoProt = !empty($infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("dhRecbto")->item(0)->nodeValue : '';
            $nProt = !empty($infProt->getElementsByTagName("nProt")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("nProt")->item(0)->nodeValue : '';
            $cStatProt = !empty($infProt->getElementsByTagName("cStat")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("cStat")->item(0)->nodeValue : '';
            $xMotivoProt = !empty($infProt->getElementsByTagName("xMotivo")->item(0)->nodeValue) ?
                    $infProt->getElementsByTagName("xMotivo")->item(0)->nodeValue : '';
            
            $txt .= "PROT|$tpAmbProt|$chNFeProt|$dhRecbtoProt|$nProt|$cStatProt|$xMotivoProt\r\n";
        }
         */
        
        return $txt;
    } //fim cxtt

    /**
     * getItens
     * 
     * @param type $det
     * @return type
     */
    private function getItens($det)
    {
        $txt = '';
        //instanciar uma variável para contagem
            
        $i = 0;
        foreach ($det as $d) {
            $nItem = $det->item($i)->getAttribute("nItem");
            $infAdProd = !empty($det->item($i)->getElementsByTagName("infAdProd")->item(0)->nodeValue) ?
                $det->item($i)->getElementsByTagName("infAdProd")->item(0)->nodeValue : '';
            //instanciar os grupos de dados internos da tag det
            $prod = $det->item($i)->getElementsByTagName("prod")->item(0);
            $imposto = $det->item($i)->getElementsByTagName("imposto")->item(0);
            $ICMS = $imposto->getElementsByTagName("ICMS")->item(0);
            $ICMS00 = $ICMS->getElementsByTagName("ICMS00")->item(0);
            $ICMS10 = $ICMS->getElementsByTagName("ICMS10")->item(0);
            $ICMS20 = $ICMS->getElementsByTagName("ICMS20")->item(0);
            $ICMS30 = $ICMS->getElementsByTagName("ICMS30")->item(0);
            $ICMS40 = $ICMS->getElementsByTagName("ICMS40")->item(0);
            $ICMS51 = $ICMS->getElementsByTagName("ICMS51")->item(0);
            $ICMS60 = $ICMS->getElementsByTagName("ICMS60")->item(0);
            $ICMS70 = $ICMS->getElementsByTagName("ICMS70")->item(0);
            $ICMS90 = $ICMS->getElementsByTagName("ICMS90")->item(0);
            $ICMSSN101 = $ICMS->getElementsByTagName("ICMSSN101")->item(0);
            $ICMSSN102 = $ICMS->getElementsByTagName("ICMSSN102")->item(0);
            $ICMSSN201 = $ICMS->getElementsByTagName("ICMSSN201")->item(0);
            $ICMSSN202 = $ICMS->getElementsByTagName("ICMSSN202")->item(0);
            $ICMSSN500 = $ICMS->getElementsByTagName("ICMSSN500")->item(0);
            $ICMSSN900 = $ICMS->getElementsByTagName("ICMSSN900")->item(0);
            $ICMSPart = $ICMS->getElementsByTagName("ICMSPart")->item(0); // VERIFICAR SE ESTA OK...
            $ICMSST = $ICMS->getElementsByTagName("ICMSST")->item(0); // VERIFICAR SE ESTA OK...
            $IPI = $imposto->getElementsByTagName("IPI")->item(0);
            $II = $imposto->getElementsByTagName("II")->item(0);
            $PIS = $imposto->getElementsByTagName("PIS")->item(0);
            $PISST = $imposto->getElementsByTagName("PISST")->item(0);
            $COFINS = $imposto->getElementsByTagName("COFINS")->item(0);
            $COFINSST = $imposto->getElementsByTagName("COFINSST")->item(0);
            $ISSQN = $imposto->getElementsByTagName("ISSQN")->item(0);
            $DI = $det->item($i)->getElementsByTagName("DI")->item(0);
            $veicProd = $det->item($i)->getElementsByTagName("veicProd")->item(0);
            $med = $det->item($i)->getElementsByTagName("med")->item(0);
            $arma = $det->item($i)->getElementsByTagName("arma")->item(0);
            $comb = $det->item($i)->getElementsByTagName("comb")->item(0);
            $i++;
            //I|CProd|CEAN|XProd|NCM|EXTIPI|CFOP|UCom|QCom|VUnCom|VProd|CEANTrib|UTrib|QTrib
            //|VUnTrib|VFrete|VSeg|VDesc|vOutro|indTot|xPed|nItemPed|
            $cProd = !empty($prod->getElementsByTagName("cProd")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("cProd")->item(0)->nodeValue : '';

            $cEAN = !empty($prod->getElementsByTagName("cEAN")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("cEAN")->item(0)->nodeValue : '';
            $xProd = !empty($prod->getElementsByTagName("xProd")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("xProd")->item(0)->nodeValue : '';
            $NCM = !empty($prod->getElementsByTagName("NCM")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("NCM")->item(0)->nodeValue : '';
            $EXTIPI = !empty($prod->getElementsByTagName("EXTIPI")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("EXTIPI")->item(0)->nodeValue : '';
            $CFOP = !empty($prod->getElementsByTagName("CFOP")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("CFOP")->item(0)->nodeValue : '';
            $uCom = !empty($prod->getElementsByTagName("uCom")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("uCom")->item(0)->nodeValue : '';
            $qCom = !empty($prod->getElementsByTagName("qCom")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("qCom")->item(0)->nodeValue : '';
                    $qCom = number_format($qCom,4,",",".");
            $vUnCom = !empty($prod->getElementsByTagName("vUnCom")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vUnCom")->item(0)->nodeValue : '';
                    $vUnCom = number_format($vUnCom,10,",",".");
            $vProd = !empty($prod->getElementsByTagName("vProd")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vProd")->item(0)->nodeValue : '';
                    $vProd = number_format($vProd,2,",",".");
            $cEANTrib = !empty($prod->getElementsByTagName("cEANTrib")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("cEANTrib")->item(0)->nodeValue : '';
            $uTrib = !empty($prod->getElementsByTagName("uTrib")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("uTrib")->item(0)->nodeValue : '';
            $qTrib = !empty($prod->getElementsByTagName("qTrib")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("qTrib")->item(0)->nodeValue : '';
                    $qTrib = number_format($qTrib,4,",",".");
            $vUnTrib = !empty($prod->getElementsByTagName("vUnTrib")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vUnTrib")->item(0)->nodeValue : '';
                    $vUnTrib = number_format($vUnTrib,10,",",".");
            $vFrete = !empty($prod->getElementsByTagName("vFrete")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vFrete")->item(0)->nodeValue : '';
                    $vFrete = number_format($vFrete,2,",",".");
            $vSeg = !empty($prod->getElementsByTagName("vSeg")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vSeg")->item(0)->nodeValue : '';
                    $vSeg = number_format($vSeg,2,",",".");
            $vDesc = !empty($prod->getElementsByTagName("vDesc")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vDesc")->item(0)->nodeValue : '';
                    $vDesc = number_format($vDesc,2,",",".");
            $vOutro = !empty($prod->getElementsByTagName("vOutro")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("vOutro")->item(0)->nodeValue : '';
                    $vOutro = number_format($vOutro,2,",",".");
            $indTot = !empty($prod->getElementsByTagName("indTot")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("indTot")->item(0)->nodeValue : '';
            $xPed = !empty($prod->getElementsByTagName("xPed")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("xPed")->item(0)->nodeValue : '';
            $nItemPed = !empty($prod->getElementsByTagName("nItemPed")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("nItemPed")->item(0)->nodeValue : '';
			$nFCI = !empty($prod->getElementsByTagName("nFCI")->item(0)->nodeValue) ?
                    $prod->getElementsByTagName("nFCI")->item(0)->nodeValue : '';
            $txt .= "I|$cProd|$cEAN|$xProd|$NCM|$EXTIPI|$CFOP|$uCom|$qCom|$vUnCom|$vProd|$cEANTrib|$uTrib|$qTrib|$vUnTrib|$vFrete|$vSeg|$vDesc|$vOutro|$indTot|$xPed|$nItemPed|$nFCI\r\n";
            //I18|nDI|dDI|xLocDesemb|UFDesemb|dDesemb|cExportador|
            if (isset($DI)) {
                foreach ($DI as $x => $k) {
                    $nDI = !empty($DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue : '';
                    $dDI = !empty($DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue : '';
                    $xLocDesemb = !empty($DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue : '';
                    $UFDesemb = !empty($DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue : '';
                    $dDesemb = !empty($DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("nDI")->item(0)->nodeValue : '';
					$tpViaTransp = !empty($DI->item($x)->getElementsByTagName("tpViaTransp")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("tpViaTransp")->item(0)->nodeValue : '';
					$vAFRMM = !empty($DI->item($x)->getElementsByTagName("vAFRMM")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("vAFRMM")->item(0)->nodeValue : '';
                                        $vAFRMM = number_format($vAFRMM,2,",",".");
					$tpIntermedio = !empty($DI->item($x)->getElementsByTagName("tpIntermedio")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("tpIntermedio")->item(0)->nodeValue : '';
					$CNPJ = !empty($DI->item($x)->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
					$UFTerceiro = !empty($DI->item($x)->getElementsByTagName("UFTerceiro")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("UFTerceiro")->item(0)->nodeValue : '';
                    $cExportador = !empty($DI->item($x)->getElementsByTagName("cExportador")->item(0)->nodeValue) ?
                            $DI->item($x)->getElementsByTagName("cExportador")->item(0)->nodeValue : '';
                    $txt .= "I18|$nDI|$dDI|$xLocDesemb|$UFDesemb|$dDesemb|$tpViaTransp|$vAFRMM|$tpIntermedio|$CNPJ|$UFTerceiro|$cExportador|\r\n";
                    $adi = $DI->item($X)->getElementsByTagName("adi")->item(0);
                    if (isset($adi)) {
                        foreach ($adi as $y => $k) {
                            //I25|nAdicao|nSeqAdic|cFabricante|vDescDI|
                            $nAdicao = !empty($adi->item($y)->getElementsByTagName("nAdicao")->item(0)->nodeValue) ?
                                    $adi->item($y)->getElementsByTagName("nAdicao")->item(0)->nodeValue : '';
                            $nSeqAdic = !empty($adi->item($y)->getElementsByTagName("nSeqAdic")->item(0)->nodeValue) ?
                                    $adi->item($y)->getElementsByTagName("nSeqAdic")->item(0)->nodeValue : '';
                            $cFabricante = !empty($adi->item($y)->getElementsByTagName("cFabricante")->item(0)->nodeValue) ?
                                    $adi->item($y)->getElementsByTagName("cFabricante")->item(0)->nodeValue : '';
                            $vDescDI = !empty($adi->item($y)->getElementsByTagName("vDescDI")->item(0)->nodeValue) ?
                                    $adi->item($y)->getElementsByTagName("vDescDI")->item(0)->nodeValue : '';
                                    $vDescDI = number_format($vDescDI,2,",",".");
							$nDraw = !empty($adi->item($y)->getElementsByTagName("nDraw")->item(0)->nodeValue) ?
                                    $adi->item($y)->getElementsByTagName("nDraw")->item(0)->nodeValue : '';
                            $txt .= "I25|$nAdicao|$nSeqAdic|$cFabricante|$vDescDI|$nDraw|\r\n";
                        } //fim adição
                    }
                }
            } //fim importação
            
            // ALTERADO A PEDIDO ANSELMO
            //H|nItem|infAdProd|
            $txt .= "H|$nItem|$infAdProd|\r\n";
            
            //v2=>JA|TpOp|Chassi|CCor|XCor|Pot|cilin|pesoL|pesoB|NSerie|TpComb|NMotor|CMT|Dist|
            //	anoMod|anoFab|tpPint|tpVeic|espVeic|VIN|condVeic|cMod|cCorDENATRAN|lota|tpRest|
            if (isset($veicProd)) {
                $tpOp = !empty($veicProd->getElementsByTagName("tpOp")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("tpOp")->item(0)->nodeValue : '';
                $chassi = !empty($veicProd->getElementsByTagName("chassi")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("chassi")->item(0)->nodeValue : '';
                $cCor = !empty($veicProd->getElementsByTagName("cCor")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("cCor")->item(0)->nodeValue : '';
                $xCor = !empty($veicProd->getElementsByTagName("xCor")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("xCor")->item(0)->nodeValue : '';
                $pot = !empty($veicProd->getElementsByTagName("pot")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("pot")->item(0)->nodeValue : '';
                $cilin = !empty($veicProd->getElementsByTagName("cilin")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("cilin")->item(0)->nodeValue : '';
                $pesoL = !empty($veicProd->getElementsByTagName("pesoL")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("pesoL")->item(0)->nodeValue : '';
                $pesoB = !empty($veicProd->getElementsByTagName("pesoB")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("pesoB")->item(0)->nodeValue : '';
                $nSerie = !empty($veicProd->getElementsByTagName("nSerie")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("nSerie")->item(0)->nodeValue : '';
                $tpComb = !empty($veicProd->getElementsByTagName("tpComb")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("tpComb")->item(0)->nodeValue : '';
                $nMotor = !empty($veicProd->getElementsByTagName("nMotor")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("nMotor")->item(0)->nodeValue : '';
                $CMT = !empty($veicProd->getElementsByTagName("CMT")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("CMT")->item(0)->nodeValue : '';
                $dist = !empty($veicProd->getElementsByTagName("dist")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("dist")->item(0)->nodeValue : '';
                $anoMod = !empty($veicProd->getElementsByTagName("anoMod")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("anoMod")->item(0)->nodeValue : '';
                $anoFab = !empty($veicProd->getElementsByTagName("anoFab")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("anoFab")->item(0)->nodeValue : '';
                $tpPint = !empty($veicProd->getElementsByTagName("tpPint")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("tpPint")->item(0)->nodeValue : '';
                $tpVeic = !empty($veicProd->getElementsByTagName("tpVeic")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("tpVeic")->item(0)->nodeValue : '';
                $espVeic = !empty($veicProd->getElementsByTagName("espVeic")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("espVeic")->item(0)->nodeValue : '';
                $vIN = !empty($veicProd->getElementsByTagName("vIN")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("vIN")->item(0)->nodeValue : '';
                $condVeic = !empty($veicProd->getElementsByTagName("condVeic")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("condVeic")->item(0)->nodeValue : '';
                $cMod = !empty($veicProd->getElementsByTagName("cMod")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("cMod")->item(0)->nodeValue : '';
                $cCorDENATRAN = !empty($veicProd->getElementsByTagName("cCorDENATRAN")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("cCorDENATRAN")->item(0)->nodeValue : '';
                $lota = !empty($veicProd->getElementsByTagName("lota")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("lota")->item(0)->nodeValue : '';
                $tpRest = !empty($veicProd->getElementsByTagName("tpRest")->item(0)->nodeValue) ?
                        $veicProd->getElementsByTagName("tpRest")->item(0)->nodeValue : '';
                $txt .= "JA|$tpOp|$chassi|$cCor|$xCor|$pot|$cilin|$pesoL|$pesoB|$nSerie|$tpComb|$nMotor|$CMT
                    |$dist|$anoMod|$anoFab|$tpPint|$tpVeic|$espVeic|$vIN|$condVeic|$cMod
                    |$cCorDENATRAN|$lote|$tpRest|\r\n";
				
            } // fim veiculos novos
            //K|nLote|qLote|dFab|dVal|vPMC|
            if (isset($med)) {
                foreach ($med as $x => $k) {
                    $nLote = !empty($med->item($x)->getElementsByTagName("nLote")->item(0)->nodeValue) ?
                            $med->item($x)->getElementsByTagName("nLote")->item(0)->nodeValue : '';
                    $qLote = !empty($med->item($x)->getElementsByTagName("qLote")->item(0)->nodeValue) ?
                            $med->item($x)->getElementsByTagName("qLote")->item(0)->nodeValue : '';
                            $qLote = number_format($qLote,3,",",".");
                    $dFab = !empty($med->item($x)->getElementsByTagName("dFab")->item(0)->nodeValue) ?
                            $med->item($x)->getElementsByTagName("dFab")->item(0)->nodeValue : '';
                    $dVal = !empty($med->item($x)->getElementsByTagName("dVal")->item(0)->nodeValue) ?
                            $med->item($x)->getElementsByTagName("dVal")->item(0)->nodeValue : '';
                    $vPMC = !empty($med->item($x)->getElementsByTagName("vPMC")->item(0)->nodeValue) ?
                            $med->item($x)->getElementsByTagName("vPMC")->item(0)->nodeValue : '';
                            $vPMC = number_format($vPMC,2,",",".");
                    $txt .= "K|$nLote|$qLote|$dFab|$dVal|$vPMC|\r\n";
                }
            } // fim medicamentos
            //L|TpArma|NSerie|NCano|Descr|
            if (isset($arma)) {
                foreach ($arma as $x => $k) {
                    $tpArma = !empty($arma->item($x)->getElementsByTagName("tpArma")->item(0)->nodeValue) ?
                            $arma->item($x)->getElementsByTagName("tpArma")->item(0)->nodeValue : '';
                    $nSerie = !empty($arma->item($x)->getElementsByTagName("nSerie")->item(0)->nodeValue) ?
                            $arma->item($x)->getElementsByTagName("nSerie")->item(0)->nodeValue : '';
                    $nCano = !empty($arma->item($x)->getElementsByTagName("nCano")->item(0)->nodeValue) ?
                            $arma->item($x)->getElementsByTagName("nCano")->item(0)->nodeValue : '';
                    $descr = !empty($arma->item($x)->getElementsByTagName("descr")->item(0)->nodeValue) ?
                            $arma->item($x)->getElementsByTagName("descr")->item(0)->nodeValue : '';
                    $txt .= "L|$tpArma|$nSerie|$nCano|$descr|\r\n";
                }
            } // fim armas
            //combustiveis
            if (isset($comb)) {
                //LA|CProdANP|CODIF|QTemp|UFCons|
                //instanciar sub grups da tag comb
                $CIDE = $comb->getElementsByTagName("CIDE")->item(0);
                $cProdANP = !empty($comb->getElementsByTagName("cProdANP")->item(0)->nodeValue) ?
                        $comb->getElementsByTagName("cProdANP")->item(0)->nodeValue : '';
				$pMixGN = !empty($comb->getElementsByTagName("pMixGN")->item(0)->nodeValue) ?
                        $comb->getElementsByTagName("pMixGN")->item(0)->nodeValue : '';
                                $pMixGN = number_format($pMixGN,4,",",".");
                $CODIF = !empty($comb->getElementsByTagName("CODIF")->item(0)->nodeValue) ?
                        $comb->getElementsByTagName("CODIF")->item(0)->nodeValue : '';
                $qTemp = !empty($comb->getElementsByTagName("qTemp")->item(0)->nodeValue) ?
                        $comb->getElementsByTagName("qTemp")->item(0)->nodeValue : '';
                        $qTemp = number_format($qTemp,4,",",".");
                $UFCons = !empty($comb->getElementsByTagName("UFCons")->item(0)->nodeValue) ?
                        $comb->getElementsByTagName("UFCons")->item(0)->nodeValue : '';
                $txt .= "LA|$cProdANP|$pMixGN|$CODIF|$qTemp|$UFCons|\r\n";
                //grupo CIDE
                if (isset($CIDE)) {
                    //LA07|qBCProd|vAliqProd|vCIDE|
                    $qBCProd = !empty($CIDE->getElementsByTagName("qBCprod")->item(0)->nodeValue) ?
                            $CIDE->getElementsByTagName("qBCprod")->item(0)->nodeValue : '';
                            $qBCProd = number_format($qBCProd,4,",",".");
                    $vAliqProd = !empty($CIDE->getElementsByTagName("vAliqProd")->item(0)->nodeValue) ?
                            $CIDE->getElementsByTagName("vAliqProd")->item(0)->nodeValue : '';
                            $vAliqProd = number_format($vAliqProd,4,",",".");
                    $vCIDE = !empty($CIDE->getElementsByTagName("vCIDE")->item(0)->nodeValue) ?
                            $CIDE->getElementsByTagName("vCIDE")->item(0)->nodeValue : '';
                            $vCIDE = number_format($vCIDE,2,",",".");
                    $txt .= "LA07|$qBCProd|$vAliqProd|$vCIDE|\r\n";
                } // fim grupo CIDE
            } //fim combustiveis
            //M|
            //lei da transparencia 12.741/12
            //Nota Técnica 2013/003
            $vTotTrib = !empty($imposto->getElementsByTagName("vTotTrib")->item(0)->nodeValue) ?
                    $imposto->getElementsByTagName("vTotTrib")->item(0)->nodeValue : '';
                    $vTotTrib =  number_format($vTotTrib,2,",",".");
            if ($vTotTrib == '') {
                $txt .= "M|\r\n";
            } else {
                $txt .= "M|$vTotTrib\r\n";
            }
            //N|
            $txt .= "N|\r\n";
            $orig = !empty($ICMS->getElementsByTagName("orig")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("orig")->item(0)->nodeValue : '';
                    $orig = str_pad($orig, 1,"0",STR_PAD_LEFT);
            $CST = (string) !empty($ICMS->getElementsByTagName("CST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("CST")->item(0)->nodeValue : '';
            $CSOSN = (string) !empty($ICMS->getElementsByTagName("CSOSN")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("CSOSN")->item(0)->nodeValue : '';
            $modBC = !empty($ICMS->getElementsByTagName("modBC")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("modBC")->item(0)->nodeValue : '';
            $vBC = !empty($ICMS->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                    $vBC = number_format($vBC,2,",",".");
            $pICMS = !empty($ICMS->getElementsByTagName("pICMS")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("pICMS")->item(0)->nodeValue : '';
                    $pICMS = number_format($pICMS,4,",",".");
			$vICMSOp = !empty($ICMS->getElementsByTagName("vICMSOp")->item(0)->nodeValue) ?
                            $ICMS->getElementsByTagName("vICMSOp")->item(0)->nodeValue : '';
                            $vICMSOp = number_format($vICMSOp,2,",",".");
			$pDif = !empty($ICMS->getElementsByTagName("pDif")->item(0)->nodeValue) ?
                            $ICMS->getElementsByTagName("pDif")->item(0)->nodeValue : '';
                            $pDif = number_format($qTotDed,4,",",".");
			$vICMSDif = !empty($ICMS->getElementsByTagName("vICMSDif")->item(0)->nodeValue) ?
                            $ICMS->getElementsByTagName("vICMSDif")->item(0)->nodeValue : '';
                            $vICMSDif = number_format($vICMSDif,2,",",".");
			$vICMSDeson = !empty($ICMS->getElementsByTagName("vICMSDeson")->item(0)->nodeValue) ?
                            $ICMS->getElementsByTagName("vICMSDeson")->item(0)->nodeValue : '';
                            $vICMSDeson = number_format($vICMSDeson,2,",",".");
			$motDesICMS = !empty($ICMS->getElementsByTagName("motDesICMS")->item(0)->nodeValue) ?
                            $ICMS->getElementsByTagName("motDesICMS")->item(0)->nodeValue : '';
            $vICMS = !empty($ICMS->getElementsByTagName("vICMS")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("vICMS")->item(0)->nodeValue : '';
                    $vICMS = number_format($vICMS,2,",",".");
            $modBCST = !empty($ICMS->getElementsByTagName("modBCST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("modBCST")->item(0)->nodeValue : '';
            $pMVAST = !empty($ICMS->getElementsByTagName("pMVAST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("pMVAST")->item(0)->nodeValue : '';
                    $pMVAST = number_format($pMVAST,4,",",".");
            $pRedBCST = !empty($ICMS->getElementsByTagName("pRedBCST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("pRedBCST")->item(0)->nodeValue : '';
                    $pRedBCST = number_format($pRedBCST,4,",",".");
            $vBCST = !empty($ICMS->getElementsByTagName("vBCST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("vBCST")->item(0)->nodeValue : '';
                    $vBCST = number_format($vBCST,2,",",".");
            $pICMSST = !empty($ICMS->getElementsByTagName("pICMSST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("pICMSST")->item(0)->nodeValue : '';
                    $pICMSST = number_format($pICMSST,4,",",".");
            $vICMSST = !empty($ICMS->getElementsByTagName("vICMSST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("vICMSST")->item(0)->nodeValue : '';
                    $vICMSST = number_format($vICMSST,2,",",".");
            $pBCOp = !empty($ICMS->getElementsByTagName("pBCOp")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("pBCOp")->item(0)->nodeValue : '';
                    $pBCOp = number_format($pBCOp,4,",",".");
            $UFST = !empty($ICMS->getElementsByTagName("UFST")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("UFST")->item(0)->nodeValue : '';
            $vBCSTRet = !empty($ICMS->getElementsByTagName("vBCSTRet")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("vBCSTRet")->item(0)->nodeValue : '';
                    $vBCSTRet = number_format($vBCSTRet,2,",",".");
            $motDesICMS = !empty($ICMS->getElementsByTagName("motDesICMS")->item(0)->nodeValue) ?
                    $ICMS->getElementsByTagName("motDesICMS")->item(0)->nodeValue : '';
            switch ($CST) {
                // a melhor maneira não é CST... DEPOIS PRECISA PASSAR PARA CADA TAG <ICMSST> por ex.
                case '00': //CST 00 TRIBUTADO INTEGRALMENTE
                    // N02|Orig|CST|ModBC|VBC|PICMS|VICMS|
                    $txt .= "N02|$orig|$CST|$modBC|$vBC|$pICMS|$vICMS|\r\n";
                    break;
                case '10': //CST 10 TRIBUTADO E COM COBRANCA DE ICMS POR SUBSTUICAO TRIBUTARIA
                    // N03|Orig|CST|ModBC|VBC|PICMS|VICMS|ModBCST|PMVAST|PRedBCST|VBCST|PICMSST|VICMSST|
                    $txt .= "N03|$orig|$CST|$modBC|$vBC|$pICMS|$vICMS|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|\r\n";
                    break;
                case '20': //CST 20 COM REDUCAO DE BASE DE CALCULO
                    // N04|Orig|CST|ModBC|PRedBC|VBC|PICMS|VICMS| 
                    $txt .= "N04|$orig|$CST|$modBC|$pRedBC|$vBC|$pICMS|$vICMS|$vICMSDeson|$motDesICMS\r\n";
                    break;
                case '30': //CST 30 ISENTA OU NAO TRIBUTADO E COM COBRANCA DO ICMS POR ST
                    // N05|Orig|CST|ModBCST|PMVAST|PRedBCST|VBCST|PICMSST|VICMSST|
                    $txt .= "N05|$orig|$CST|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|$vICMSDeson|$motDesICMS\r\n";
                    break;
                case '40': //CST 40-ISENTA 41-NAO TRIBUTADO E 50-SUSPENSAO
                case '41': //CST 40-ISENTA 41-NAO TRIBUTADO E 50-SUSPENSAO
                case '50': //CST 40-ISENTA 41-NAO TRIBUTADO E 50-SUSPENSAO
                    // N06|Orig|CST|vICMS|motDesICMS|
                    $txt .= "N06|$orig|$CST|$vICMS|$motDesICMS|\r\n";
                    break;
                case '51': //CST 51 DIFERIMENTO - A EXIGENCIA DO PREECNCHIMENTO DAS INFORMAS DO ICMS DIFERIDO FICA A CRITERIO DE CADA UF
                    // N07|Orig|CST|ModBC|PRedBC|VBC|PICMS|VICMS|
                    $txt .= "N07|$orig|$CST|$modBC|$pRedBC|$vBC|$pICMS|vICMSOp|pDif|vICMSDif|$vICMS|\r\n";
                    break;
                case '60': //CST 60 ICMS COBRADO ANTERIORMENTE POR S
                    // N08|Orig|CST|VBCST|VICMSST|
                    $txt .= "N08|$orig|$CST|$vBCST|$vBCSTRet|$vICMSSTRet|$vICMSST|\r\n";
                    break;
                case '70': //CST 70 - Com redução de base de cálculo e cobrança do ICMS por substituição tributária
                    // N09|Orig|CST|ModBC|PRedBC|VBC|PICMS|VICMS|ModBCST|PMVAST|PRedBCST|VBCST|PICMSST|VICMSST|
                    $txt .= "N09|$orig|$CST|$modBC|$pRedBC|$vBC|$pICMS|$vICMS|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|$vICMSDeson|$motDesICMS\r\n";
                    break;
                case '90': //CST - 90 Outros
                    // N10|Orig|CST|ModBC|PRedBC|VBC|PICMS|VICMS|ModBCST|PMVAST|PRedBCST|VBCST|PICMSST|VICMSST|
                    $txt .= "N10|$orig|$CST|$modBC|$pRedBC|$vBC|$pICMS|$vICMS|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|$vICMSDeson|$motDesICMS|\r\n";
                    break;
                // case '??':	// CST - ???	alguns campos são novos (v2.0)
                // N10a|Orig|CST|ModBC|PRedBC|VBC|PICMS|VICMS|ModBCST|PMVAST|PRedBCST|VBCST|PICMSST|VICMSST|pBCOp|UFST|
                //	$txt .= "N10a|$orig|$CST|$modBC|$pRedBC|$vBC|$pICMS|$vICMS|$modBCST
                //	|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|$pBCOp|$UFST|\r\n";
                //	break;
                // case '??':	// CST - ???	alguns campos são novos (v2.0)
                // N10b|Orig|CST|vBCSTRet|vICMSSTRet|vBCSTDest|vICMSSTDest|
                //	$txt .= "N10b|$orig|$CST|$vBCSTRet|$vICMSSTRet|$vBCSTDest|$vICMSSTDest|\r\n";
                //	break;
            } // fim switch
            switch ($CSOSN) {
                case '101': // CSON - 101
                    // N10c|Orig|CSOSN|pCredSN|vCredICMSSN|
                    $txt .= "N10c|$orig|$CSOSN|$pCredSN|$vCredICMSSN|\r\n";
                    break;
                case '102': // CSOSN=102, 103,300 ou 400 [ICMS]
                case '103': // CSOSN=102, 103,300 ou 400 [ICMS]
                case '300': // CSOSN=102, 103,300 ou 400 [ICMS]
                case '400': // CSOSN=102, 103,300 ou 400 [ICMS]
                    // N10d|Orig|CSOSN|
                    $txt .= "N10d|$orig|$CSOSN|\r\n";
					
                    break;
                case '201': // CSON - 201
                    // N10e|Orig|CSOSN|modBCST|pMVAST|pRedBCST|vBCST|pICMSST|vICMSST|pCredSN|vCredICMSSN|
                    $txt .= "N10e|$orig|$CSOSN|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST
                        |$pCredSN|$vCredICMSSN|\r\n";
						
                    break;
                case '202': // CSOSN=202 ou 203 [ICMS]
                case '203': // CSOSN=202 ou 203 [ICMS]
                    // N10f|Orig|CSOSN|modBCST|pMVAST|pRedBCST|vBCST|pICMSST|vICMSST|
                    $txt .= "N10f|$orig|$CSOSN|$modBCST|$pMVAST|$pRedBCST|$vBCST|$pICMSST
                        |$vICMSST|\r\n";
						
                    break;
                case '500': // CSON - 500
                    // N10g|Orig|CSOSN|modBCST|vBCSTRet|vICMSSTRet|
                    $txt .= "N10g|$orig|$CSOSN|$vBCSTRet|$vICMSSTRet|\r\n";
					
                    break;
                case '900': // CSON - 900
                    // N10h|Orig|CSOSN|modBC|vBC|pRedBC|pICMS|vICMS|modBCST|pMVAST|pRedBCST|vBCST|pICMSST|vICMSST|pCredSN|vCredICMSSN|
                    $txt .= "N10h|$orig|$CSOSN|$modBC|$vBC|$pRedBC|$pICMS|$vICMS|$modBCST
                        |$pMVAST|$pRedBCST|$vBCST|$pICMSST|$vICMSST|$pCredSN|$vCredICMSSN|\r\n";
                    break;
            } // fim switch

            $txtIPI = '';
            if (isset($IPI)) {
                //O|ClEnq|CNPJProd|CSelo|QSelo|CEnq|
                $clEnq = !empty($IPI->getElementsByTagName("clEnq")->item(0)->nodeValue) ?
                        $IPI->getElementsByTagName("clEnq")->item(0)->nodeValue : '';
                $CNPJProd = !empty($IPI->getElementsByTagName("CNPJProd")->item(0)->nodeValue) ?
                        $IPI->getElementsByTagName("CNPJProd")->item(0)->nodeValue : '';
                $cSelo = !empty($IPI->getElementsByTagName("cSelo")->item(0)->nodeValue) ?
                        $IPI->getElementsByTagName("cSelo")->item(0)->nodeValue : '';
                $qSelo = !empty($IPI->getElementsByTagName("qSelo")->item(0)->nodeValue) ?
                        $IPI->getElementsByTagName("qSelo")->item(0)->nodeValue : '';
                $cEnq = !empty($IPI->getElementsByTagName("cEnq")->item(0)->nodeValue) ?
                        $IPI->getElementsByTagName("cEnq")->item(0)->nodeValue : '';
                $txt .= "O|$clEnq|$CNPJProd|$cSelo|$qSelo|$cEnq|\r\n";
				
                //grupo de tributação de IPI NAO TRIBUTADO
                $IPINT = $IPI->getElementsByTagName("IPINT")->item(0);
                if (isset($IPINT)) {
                    // O08|CST|
                    $CST = (string) !empty($IPINT->getElementsByTagName("CST")->item(0)->nodeValue) ?
                            $IPINT->getElementsByTagName("CST")->item(0)->nodeValue : '';
                    $txtIPI = "O08|$CST|\r\n";
                }
                //grupo de tributação de IPI
                $IPITrib = $IPI->getElementsByTagName("IPITrib")->item(0);
                if (isset($IPITrib)) {
                    $CST = (string) !empty($IPITrib->getElementsByTagName("CST")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("CST")->item(0)->nodeValue : '';
                    $vIPI = !empty($IPITrib->getElementsByTagName("vIPI")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("vIPI")->item(0)->nodeValue : '';
                    $vIPI = number_format($vIPI,2,",",".");
                    $vBC = !empty($IPITrib->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                    $vBC = number_format($vBC,2,",",".");
                    $pIPI = !empty($IPITrib->getElementsByTagName("pIPI")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("pIPI")->item(0)->nodeValue : '';
                    $pIPI = number_format($pIPI,2,",",".");
                    $qUnid = !empty($IPITrib->getElementsByTagName("qUnid")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("qUnid")->item(0)->nodeValue : '';
                    $vUnid = !empty($IPITrib->getElementsByTagName("vUnid")->item(0)->nodeValue) ?
                            $IPITrib->getElementsByTagName("vUnid")->item(0)->nodeValue : '';
                    $vUnid = number_format($vUnid,2,",",".");
                    switch ($CST) {
                        case '00': //CST 00, 49, 50 e 99
                            //O07|CST|VIPI|
                            $txtIPI = "O07|$CST|$vIPI|\r\n";
                            break;
                        case '49': //CST 00, 49, 50 e 99
                            //O07|CST|VIPI|
                            $txtIPI = "O07|$CST|$vIPI|\r\n";
                            break;
                        case '50': //CST 00, 49, 50 e 99
                            //O07|CST|VIPI|
                            $txtIPI = "O07|$CST|$vIPI|\r\n";
                            break;
                        case '99': //CST 00, 49, 50 e 99
                            //O07|CST|VIPI|
                            $txtIPI = "O07|$CST|$vIPI|\r\n";
                            break;
                        case '01': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '02': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '03': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '04': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '51': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '52': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '53': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '54': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                        case '55': //CST 01, 02, 03,04, 51, 52, 53, 54 e 55
                            //O08|CST|
                            $txtIPI = "O08|$CST|\r\n";
                            break;
                    } // fim switch
                    if (substr($txtIPI, 0, 3) == 'O07') {
                        if ($pIPI != '') {
                            //O10|VBC|PIPI|
                            $txtIPI .= "O10|$vBC|$pIPI|\r\n";
                        } else {
                            //O11|QUnid|VUnid|
                            $txtIPI .= "O11|$qUnid|$vUnid|$vIPI|\r\n";
                        } //fim if
                    } //fim if
                } //fim ipi trib
            } // fim IPI
            $txt .= $txtIPI;
            //P|vBC|vDespAdu|vII|vIOF|
            if (isset($II)) {
                $vBC = !empty($II->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $II->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $vDespAdu = !empty($II->getElementsByTagName("vDespAdu")->item(0)->nodeValue) ?
                        $II->getElementsByTagName("vDespAdu")->item(0)->nodeValue : '';
                $vDespAdu = number_format($vDespAdu,2,",",".");
                $vII = !empty($II->getElementsByTagName("vII")->item(0)->nodeValue) ?
                        $II->getElementsByTagName("vII")->item(0)->nodeValue : '';
                $vII = number_format($vII,2,",",".");
                $vIOF = !empty($II->getElementsByTagName("vIOF")->item(0)->nodeValue) ?
                        $II->getElementsByTagName("vIOF")->item(0)->nodeValue : '';
                $vIOF = number_format($vIOF,2,",",".");
                $txt .= "P|$vBC|$vDespAdu|$vII|$vIOF|\r\n";
            } // fim II
            //monta dados do PIS
            if (isset($PIS)) {
                //Q|
                $txt .= "Q|\r\n";
                $CST = !empty($PIS->getElementsByTagName("CST")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("CST")->item(0)->nodeValue : '';
                $vBC = !empty($PIS->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $pPIS = !empty($PIS->getElementsByTagName("pPIS")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("pPIS")->item(0)->nodeValue : '';
                $vPIS = !empty($PIS->getElementsByTagName("vPIS")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("vPIS")->item(0)->nodeValue : '';
                $vPIS = number_format($vPIS,2,",",".");
                $qBCProd = !empty($PIS->getElementsByTagName("qBCProd")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("qBCProd")->item(0)->nodeValue : '';
                $vAliqProd = !empty($PIS->getElementsByTagName("vAliqProd")->item(0)->nodeValue) ?
                        $PIS->getElementsByTagName("vAliqProd")->item(0)->nodeValue : '';
                $vAliqProd = number_format($vAliqProd,2,",",".");
                if ($CST == '01' || $CST == '02') {  // PIS TRIBUTADO PELA ALIQUOTA
                    //Q02|CST|VBC|PPIS|VPIS|
                    $txt .= "Q02|$CST|$vBC|$pPIS|$vPIS|\r\n";
                }
                if ($CST == '03') {  //PIS TRIBUTADO POR QTDE
                    //Q03|CST|QBCProd|VAliqProd|VPIS|
                    $txt .= "Q03|$CST|$qBCProd|$vAliqProd|$vPIS|\r\n";
                }
                if ($CST == '04' || $CST == '06' || $CST == '07' || $CST == '08' || $CST == '09') {
                    //PIS não tributado
                    //Q04|CST|
                    $txt .= "Q04|$CST|\r\n";
                }
                if ($CST == '99') {
                    //PIS OUTRAS OPERACOES
                    //Q05|CST|vPIS|
                    $txt .= "Q05|$CST|$vPIS|\r\n";
                    if ($vBC != '' || $pPIS != '') {
                        //Q07|vBC|pPIS|
                        $txt .= "Q07|$vBC|$pPIS|$vPIS|\r\n";
                    } else {
                        //Q10|qBCProd|vAliqProd|
                        $txt .= "Q10|$qBCProd|$vAliqProd|\r\n";
                    }
                }
            } //fim PIS
            //monta dados do PIS em Substituição Tributária
            if (isset($PISST)) {
                $vPIS = !empty($PISST->getElementsByTagName("vPIS")->item(0)->nodeValue) ?
                        $PISST->getElementsByTagName("vPIS")->item(0)->nodeValue : '';
                $vPIS = number_format($vPIS,2,",",".");
                $vBC = !empty($PISST->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $PISST->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $pPIS = !empty($PISST->getElementsByTagName("pPIS")->item(0)->nodeValue) ?
                        $PISST->getElementsByTagName("pPIS")->item(0)->nodeValue : '';
                $qBCProd = !empty($PISST->getElementsByTagName("qBCProd")->item(0)->nodeValue) ?
                        $PISST->getElementsByTagName("qBCProd")->item(0)->nodeValue : '';
                $vAliqProd = !empty($PISST->getElementsByTagName("vAliqProd")->item(0)->nodeValue) ?
                        $PISST->getElementsByTagName("vAliqProd")->item(0)->nodeValue : '';
                $vAliqProd = number_format($vAliqProd,2,",",".");
                //R|vPIS|
                $txt .= "R|$vPIS|\r\n";
                if ($vBC != '' || $pPIS != '') {
                    //R02|vBC|pPIS|
                    $txt .= "R02|$vBC|$pPIS|\r\n";
                } else {
                    //R04|qBCProd|vAliqProd|
                    $txt .= "R04|$qBCProd|$vAliqProd|$vPis|\r\n";
                }
            } //fim PISST
            //monta dados do COFINS
            if (isset($COFINS)) {
                //S|
                $txt .= "S|\r\n";
                $CST = !empty($COFINS->getElementsByTagName("CST")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("CST")->item(0)->nodeValue : '';
                $vBC = !empty($COFINS->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $pCOFINS = !empty($COFINS->getElementsByTagName("pCOFINS")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("pCOFINS")->item(0)->nodeValue : '';
                $vCOFINS = !empty($COFINS->getElementsByTagName("vCOFINS")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("vCOFINS")->item(0)->nodeValue : '';
                $vCOFINS = number_format($vCOFINS,2,",",".");
                $qBCProd = !empty($COFINS->getElementsByTagName("qBCProdC")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("qBCProd")->item(0)->nodeValue : '';
                $vAliqProd = !empty($COFINS->getElementsByTagName("vAliqProd")->item(0)->nodeValue) ?
                        $COFINS->getElementsByTagName("vAliqProd")->item(0)->nodeValue : '';
                $vAliqProd = number_format($vAliqProd,2,",",".");
                if ($CST == '01' || $CST == '02') {
                    //S02|CST|VBC|PCOFINS|VCOFINS|
                    $txt .= "S02|$CST|$vBC|$pCOFINS|$vCOFINS|\r\n";
                }
                if ($CST == '03') {
                    //S03|CST|QBCProd|VAliqProd|VCOFINS|
                    $txt .= "S03|$CST|$qBCProd|$vAliqProd|$vCOFINS|\r\n";
                }
                if ($CST == '04' || $CST == '06' || $CST == '07' || $CST == '08' || $CST == '09') {
                    //S04|CST|
                    $txt .= "S04|$CST|\r\n";
                }
                if ($CST == '99') {
                    //S05|CST|VCOFINS|
                    $txt .= "S05|$CST|$vCOFINS|\r\n";
                    if ($vBC != '' || $pCOFINS != '') {
                        //S07|VBC|PCOFINS|
                        $txt .= "S07|$vBC|$pCOFINS|\r\n";
                    } else {
                        //S09|QBCProd|VAliqProd|
                        $txt .= "S09|$qBCProd|$vAliqProd|\r\n";
                    }
                }
            } //fim COFINS
            //monta dados do COFINS em Substituição Tributária
            if (isset($COFINSST)) {
                $vCOFINS = !empty($COFINSST->getElementsByTagName("vCOFINS")->item(0)->nodeValue) ?
                        $COFINSST->getElementsByTagName("vCOFINS")->item(0)->nodeValue : '';
                $vCOFINS = number_format($vCOFINS,2,",",".");
                $vBC = !empty($COFINSST->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $COFINSST->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $pCOFINS = !empty($COFINSST->getElementsByTagName("pCOFINS")->item(0)->nodeValue) ?
                        $COFINSST->getElementsByTagName("pCOFINS")->item(0)->nodeValue : '';
                $pCOFINS = number_format($pCOFINS,2,",",".");
                $qBCProd = !empty($COFINSST->getElementsByTagName("qBCProd")->item(0)->nodeValue) ?
                        $COFINSST->getElementsByTagName("qBCProd")->item(0)->nodeValue : '';
                $qBCProd = number_format($qBCProd,2,",",".");
                $vAliqProd = !empty($COFINSST->getElementsByTagName("vAliqProd")->item(0)->nodeValue) ?
                        $COFINSST->getElementsByTagName("vAliqProd")->item(0)->nodeValue : '';
                $vAliqProd = number_format($vAliqProd,2,",",".");
                //T|VCOFINS|
                $txt .= "T|$vCOFINS|\r\n";
                if ($vBC != '' || $pCOFINS != '') {
                    //T02|VBC|PCOFINS|
                    $txt .= "T02|$vBC|$pCOFINS|\r\n";
                } else {
                    //T04|QBCProd|VAliqProd|
                    $txt .= "T04|$qBCProd|$vAliqProd|\r\n";
                }
            } //fim COFINSST
			//monta dados do ISS
            if (isset($ISSQN)) {
                //U|VBC|VAliq|VISSQN|CMunFG|CListServ|cSitTrib|
                $vBC = !empty($ISSQN->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vBC")->item(0)->nodeValue : '';
                $vBC = number_format($vBC,2,",",".");
                $vAliq = !empty($ISSQN->getElementsByTagName("vAliq")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vAliq")->item(0)->nodeValue : '';
                $vAliq = number_format($vAliq,2,",",".");
                $vISSQN = !empty($ISSQN->getElementsByTagName("vISSQN")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vISSQN")->item(0)->nodeValue : '';
                $vISSQN = number_format($vISSQN,2,",",".");
                $cMunFG = !empty($ISSQN->getElementsByTagName("cMunFG")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cMunFG")->item(0)->nodeValue : '';
                $cListServ = !empty($ISSQN->getElementsByTagName("cListServ")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cListServ")->item(0)->nodeValue : '';
                $cSitTrib = !empty($ISSQN->getElementsByTagName("cSitTrib")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cSitTrib")->item(0)->nodeValue : '';
				$vDeducao = !empty($ISSQN->getElementsByTagName("vDeducao")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vDeducao")->item(0)->nodeValue : '';
                                $vDeducao = number_format($vDeducao,2,",",".");
				$vOutro = !empty($ISSQN->getElementsByTagName("vOutro")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vOutro")->item(0)->nodeValue : '';
                                $vOutro = number_format($vOutro,2,",",".");
				$vDescIncond = !empty($ISSQN->getElementsByTagName("vDescIncond")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vDescIncond")->item(0)->nodeValue : '';
                                $vDescIncond = number_format($vDescIncond,2,",",".");
				$vDescCond = !empty($ISSQN->getElementsByTagName("vDescCond")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vDescCond")->item(0)->nodeValue : '';
                                $vDescCond = number_format($vDescCond,2,",",".");
				$vISSRet = !empty($ISSQN->getElementsByTagName("vISSRet")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("vISSRet")->item(0)->nodeValue : '';
                                $vISSRet = number_format($vISSRet,2,",",".");
				$indISS = !empty($ISSQN->getElementsByTagName("indISS")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("indISS")->item(0)->nodeValue : '';
				$cServico = !empty($ISSQN->getElementsByTagName("cServico")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cServico")->item(0)->nodeValue : '';
				$cMun = !empty($ISSQN->getElementsByTagName("cMun")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cMun")->item(0)->nodeValue : '';
				$cPais = !empty($ISSQN->getElementsByTagName("cPais")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("cPais")->item(0)->nodeValue : '';
				$nProcesso = !empty($ISSQN->getElementsByTagName("nProcesso")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("nProcesso")->item(0)->nodeValue : '';
				$indIncentivo = !empty($ISSQN->getElementsByTagName("indIncentivo")->item(0)->nodeValue) ?
                        $ISSQN->getElementsByTagName("indIncentivo")->item(0)->nodeValue : '';
                $txt .= "U|$vBC|$vAliq|$vISSQN|$cMunFG|$cListServ|$vDeducao|$vOutro|$vDescIncond|$vDescCond|$vISSRet|$indISS|$cServico|$cMun|$cPais|$nProcesso|$indIncentivo|$cSitTrib|\r\n";
            } //fim ISSQN
        } //fim fopreach itens

        return $txt;
    }//fim getItens
}
//fim da classe