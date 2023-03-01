<?php 
    // /var/www/nf/NF_V3/NF_V3/interfaces/nfe-consulta-nota.php
    // /var/www/nf/NF_V3/NF_V3/interfaces/dnfe-consulta.php
    /*
        Programa:  dnfe-consulta.php
        Descricão: 
        Autor:     
        /user/nfe/00040518558991/CaixaEntrada/Processar
    */ 
    
    if (!isset($argv[1])){
        echo "
            ENTRADA:
                argv[1] = arquivo_txt ; 
                argv[2]*= estado      | BR; 
                
            arquivo_txt:
                ARQUIVO[0] =          cnpj | 
                ARQUIVO[1] =           cpf | 
                ARQUIVO[2] =            uf | 
                ARQUIVO[3] = tipo_ambiente | 
                ARQUIVO[4] =    ultimo_nsu | 
                ARQUIVO[5] =       seq_nsu | 
                ARQUIVO[6] =      chaveNFe | 
                
            SAIDA:
                reNFe:
                    001|chave|nsu|emit_cpf_cnpj|emit_nome|ie|data_emissao|tipo_nota|valor_nf|digest_value|data_hora_recebimento|protocolo|situacao_nfe|
                resEvento:
                    002|chave|nsu|emit_cpf_cnpj|data_hora_evento|tp_evento|seq_evento|desc_evento|data_hora_recebimento|nProt|
                procEventoNFe:
                    002|nsu|chave|emit_cpf_cnpj|data_hora_evento|tp_evento|seq_evento|desc_evento|data_hora_recebimento|nProt|
        \n"; exit() ;
    }
    
    require_once("../funcoes/flog.php"); // para gravar log
    require_once("../funcoes/fdebug.php"); // Para realizar debug
    require_once("../funcoes/freplace.php"); // Replace de dados
    require_once("../classes/CAssinaturaDigital.php"); //Usado para assinar o xml
    require_once("../classes/validaXml.php"); // Usado para validar o xml
    require_once("../classes/buscaWebService.php"); // Usado para buscar os dados do arquivo de webService
    require_once("../classes/soapWebService.php"); // Usado para enviar envelope soap
    require_once("../ferramentas/formatXML.php"      ) ; //   
    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");
    
    $temp=$config['temp']."dnfe/";
    // Dados que são passados pelo programa chamador (cobol)
    //
    
    
    if (!file_exists($argv[1])){
        echo "\nARQUIVO NAO EXISTE\n";
        exit();
    }
    //     
    // echo ">>>";
    // echo file_get_contents($argv[1]);
    // echo "<<<";
    //     
    
    $arquivo_txt = explode("|", file_get_contents($argv[1]));
    // Se não for passado o parametro, buscar estado RS
    $estado = (isset($argv[2]) ? $argv[2]:'BR');
    
    // Nome arquivo de saida
    $nome_arquivo_saida = explode("/",$argv[1]);
    $nome_arquivo_saida = end($nome_arquivo_saida);
    $nome_arquivo_saida = str_replace("DNFE", "DNFER", $nome_arquivo_saida);
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }
    
    // main
    cria_diretorios($temp);
    
    $cnpj          = trim($arquivo_txt[0]);
    $cpf           = trim($arquivo_txt[1]);
    $uf            = trim($arquivo_txt[2]);
    $tipo_ambiente = trim($arquivo_txt[3]);
    $ultimo_nsu    = trim($arquivo_txt[4]);
    $seq_nsu       = trim($arquivo_txt[5]);
    $chaveNFe      = str_replace("\n", "", trim($arquivo_txt[6]));
    
    $caminho_cliente = $numero_cliente = $cnpj;
    $tipo_cliente    = "CNPJ";
    
    if(!(int)$numero_cliente) {
        $tipo_cliente       = "CPF";
        $numero_cliente     = $cpf;
        $caminho_cliente    = str_pad($cpf, 14 , "0" , STR_PAD_LEFT);
    } 
    
    $ambiente = ($tipo_ambiente == "1" ? "producao" : "homologacao");
    $dados = parse_ini_file($config['dados']."/config_cliente/".$caminho_cliente.".ini",true);
    
    // [22/06/2017] - Fernando H. Crozetta: cria arquivos pem, se for necessario
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);
    
    $dados_ws = new BuscaWebService($estado,'dnfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("consulta",$dados['dnfe']['versao']);
    //  echo ">\n";
    
    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");
    
    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        '<soap12:Header>ALTERAR_TAG_CABECALHO</soap12:Header>' => "",
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);

    $alterar_tag_busca = 
        (int)$ultimo_nsu   ?"<distNSU><ultNSU>$ultimo_nsu</ultNSU></distNSU>" : 
        ((int)$seq_nsu     ?"<consNSU><NSU>$seq_nsu</NSU></consNSU>" :
        ((int)$chaveNFe    ?"<consChNFe><chNFe>$chaveNFe</chNFe></consChNFe>": "<distNSU><ultNSU>$ultimo_nsu</ultNSU></distNSU>"));
    
    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE' => $array_webservice->namespace,
        'ALTERAR_DADOS_UF' => $uf,
        'ALTERAR_DADOS_URL'=>$array_webservice->url,
        'ALTERAR_DADOS_VERSAO_DADOS' => $array_webservice->versao,
        'ALTERAR_TIPO_AMBIENTE'=> $tipo_ambiente,
        'ALTERAR_CUF_AUTOR'=> "41",
        'ALTERAR_CNPJ'=> $cnpj,
        'ALTERAR_CPF'=> $cpf,
        'ALTERAR_TIPO_CLIENTE' => $tipo_cliente,
        'ALTERAR_NUMERO_CLIENTE' => $numero_cliente,
        'ALTERAR_ULT_NSU'=> $ultimo_nsu,
        'ALTERAR_NSU'=> "",
        'ALTERAR_CH_NFE'=> "",
        'ALTERAR_TAG_BUSCA'=> $alterar_tag_busca,
        "\n"=>'',
        "\r"=>'',
        "\t"=>'',
    );
    $template_soap =  freplace($template_soap,$array_substituicao);
    $arquivo_xml = $dados['dnfe']['dir_retorno']."dnfe-" . date("YmdHms") . ".xml";
    file_put_contents($arquivo_xml, $template_soap);
    
    #print_r(formatXML($template_soap));exit();
    
    // goto teste; //para efeito de teste
    $soap = new SoapWebService($arquivo_xml,$caminho_cliente,$array_webservice);
    $xml_webservice = $soap->comunicar();
    
    /* teste: //para efeito de teste
    $xml_webservice = '
    <soap:Body>
        <nfeDistDFeInteresseResponse xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeDistribuicaoDFe">
            <nfeDistDFeInteresseResult>
                <retDistDFeInt xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.portalfiscal.inf.br/nfe" versao="1.01">
                <tpAmb>1</tpAmb>
                <verAplic>1.5.11</verAplic>
                <cStat>138</cStat>
                <xMotivo>Documento localizado</xMotivo>
                <dhResp>2022-07-13T11:30:27-03:00</dhResp>
                <loteDistDFeInt>
                    <docZip NSU="000000000001561" schema="resNFe_v1.01.xsd">H4sIAAAAAAAEAIVSXWvCMBT9K6XvNjepTW25BjZ1Y8MV2Ydsj7FGDbSJNJn68xftPhgMlofck8M591wuwU656kZFp7Yxrjy59Tjeeb8vCTkej8kxTWy3JQyAkteH+VO9U62Mv8X6f/FAG+elqVUcHVTnpB3HNAH62eOXf287L5uNdrVsEm02yaojZqNigfUujCiGlDHIRwVL84JSYBBQlkF/0oJTRtNRVsCIFzmS3oOTanEvfnuQXEg8VbZV4raxK9lEU+18p1fvupY2mqroWhl3rhNr3Htro7lfSyS9Be9mogDOCzqko9AuPHG9m7VaMGBsAPkA+DPl5ZCXDAaQlgBIegH6fXUjKJJLxUO40gySs+CMca23S9mIfDl/5tX2alG/TK7uZ9tJZ6Zvh9slY3YcWvWikPmo6pW3f8QWP7GfGjSLznpBL1ukWT7kGfAwSE9j/aT9eWGB+YJI+r8hPgB4WigwJAIAAA==</docZip>
                </loteDistDFeInt>
                </retDistDFeInt>
            </nfeDistDFeInteresseResult>
            </nfeDistDFeInteresseResponse>
        </soap:Body>
    </soap:Envelope>
    
    ';  */
    
    
    #print_r(formatXML($xml_webservice)) ; 
    
    
    $array_retorno = explode("<retDistDFeInt", $xml_webservice);
    $array_retorno2 = explode("</retDistDFeInt>", $array_retorno[1]);
    
    $xml_retorno = simplexml_load_string("<retDistDFeInt ".$array_retorno2[0]."</retDistDFeInt>");
    
    #print_r(($xml_retorno)) ; 
    // cd /var/www/html/nf/NF_V3/NF_V3/interfaces php dnfe-consulta.php /user/nfe/00040518558991/CaixaEntrada/Processar/DNFE2022.07.13-10.38.31.txt
    file_put_contents($temp."retorno".date('YmdHis').".xml", $xml_retorno);
    
    $retorno_cobol = "000|".$numero_cliente."|".$uf."|".$tipo_ambiente."|".$xml_retorno->cStat."|".$xml_retorno->xMotivo."|".date('YmdHis')."|".$xml_retorno->ultNSU."|".$xml_retorno->maxNSU."|\n";
    
    $retorno_cobol_tipo_001 = "";
    $retorno_cobol_tipo_002 = "";
    
    /**
     * $criar_arquivo controle para um bug da SEFAZ
     * Para criar apenas notas referentes ao CNPJ solicitado de destinadas
     * Caso contrario nao cria os arquivos.
     */
    $criar_arquivo = true;
    
    if($xml_retorno->loteDistDFeInt->docZip === NULL){
        file_put_contents($dados['dnfe']['dir_saida_cobol'].$nome_arquivo_saida, $retorno_cobol.$retorno_cobol_tipo_001.$retorno_cobol_tipo_002);
        return;
    }
    
    
    for($i=0; $i < count($xml_retorno->loteDistDFeInt->docZip); $i++)
    {
        $caminho = "";
        $sufixo = "";
        $prefixo = "";
        $criar_arquivo = true;
        
        if (strpos($xml_retorno->loteDistDFeInt->docZip[$i]["schema"], 'procNFe') !== false) {
            $caminho = $dados['dnfe']['dir_procNFe'];
            $sufixo = "-procNFe.xml";
            cria_diretorios($dados['dnfe']['dir_procNFe']);
        }
        else if (strpos($xml_retorno->loteDistDFeInt->docZip[$i]["schema"], 'resEvento') !== false) {
            $caminho = $dados['dnfe']['dir_resEvento'];
            $sufixo = "-evento.xml";
            cria_diretorios($dados['dnfe']['dir_resEvento']);
        }
        else if (strpos($xml_retorno->loteDistDFeInt->docZip[$i]["schema"], 'resNFe') !== false) {
            $caminho = $dados['dnfe']['dir_resNFe'];
            $sufixo = "-resumo.xml";
            cria_diretorios($dados['dnfe']['dir_resNFe']);
    
        }
        else if (strpos($xml_retorno->loteDistDFeInt->docZip[$i]["schema"], 'procEventoNFe') !== false) {
            $caminho = $dados['dnfe']['dir_procEventoNFe'];
            $sufixo = "-procEventoNFe.xml";
            cria_diretorios($dados['dnfe']['dir_procEventoNFe']);
        }
        else {
            $caminho = $dados['dnfe']['dir_naoImplementado'];
            $sufixo = "-nao_implementado.xml";
            cria_diretorios($dados['dnfe']['dir_naoImplementado']);
        }
        
        file_put_contents($caminho.$xml_retorno->loteDistDFeInt->docZip[$i]["NSU"].".gz", base64_decode($xml_retorno->loteDistDFeInt->docZip[$i]));
        //file_put_contents("/user/transf/dcte/".$xml_retorno->loteDistDFeInt->docZip[$i]["NSU"].".gz", base64_decode($xml_retorno->loteDistDFeInt->docZip[$i]));
        //file_put_contents("/user/transf/dcte/schema.txt", $xml_retorno->loteDistDFeInt->docZip[$i]["schema"]);
        
        $retorno = shell_exec("gunzip -f -c ".$caminho.$xml_retorno->loteDistDFeInt->docZip[$i]["NSU"].".gz ; rm ".$caminho.$xml_retorno->loteDistDFeInt->docZip[$i]["NSU"].".gz");
        
        $tmp = simplexml_load_string($retorno);
        
        switch ($xml_retorno->loteDistDFeInt->docZip[$i]["schema"]) {
            
            case 'resNFe_v1.01.xsd':
                $criar_arquivo = true;
                $prefixo = $tmp->chNFe;
                $retorno_cobol_tipo_001 .= monta_reNFe($tmp, $xml_retorno->loteDistDFeInt->docZip[$i]["NSU"]);
                break;
            
            case 'resEvento_v1.01.xsd':
                $criar_arquivo = true;
                $prefixo = $tmp->chNFe;
                $retorno_cobol_tipo_002 .= monta_resEvento($tmp, $xml_retorno->loteDistDFeInt->docZip[$i]["NSU"]);
                break;
            
            case 'procNFe_v3.10.xsd':
            case 'procNFe_v4.00.xsd':
                if ($tmp->NFe->infNFe->dest->CNPJ != $cnpj){
                    $criar_arquivo = false;
                }
                $prefixo = $tmp->protNFe->infProt->chNFe;
                break;
                
            case 'procEventoNFe_v1.01.xsd':
                $criar_arquivo = true;
                $prefixo = $tmp->evento->infEvento->chNFe;
                $retorno_cobol_tipo_002 .= monta_procEventoNFe($tmp, $xml_retorno->loteDistDFeInt->docZip[$i]["NSU"]);
                break;
            
            default:
                $criar_arquivo = true;
                $prefixo = $xml_retorno->loteDistDFeInt->docZip[$i]["NSU"].$xml_retorno->loteDistDFeInt->docZip[$i]["schema"];
                break;
        }
        
        // 41190307358761004822550020002068191095991225
        if ($criar_arquivo)
        {
            echo("S->$caminho$prefixo$sufixo\n");
            
            echo $retorno_cobol.$retorno_cobol_tipo_001.$retorno_cobol_tipo_002;
            file_put_contents($dados['dnfe']['dir_saida_cobol'].$nome_arquivo_saida, $retorno_cobol.$retorno_cobol_tipo_001.$retorno_cobol_tipo_002);
            file_put_contents($caminho.$prefixo.$sufixo, $retorno);
            
            /* Eduardo - Precisa criar diretorio no CNPJ.ini */
            file_put_contents("/var/www/nfe/recebe/".$prefixo.$sufixo, $retorno);
        }
        else{
            echo("N->$caminho$prefixo$sufixo\n");
        }
        
    }
    
    
    function monta_reNFe($tmp_xml, $nsu)
    {
        $chave                  = $tmp_xml->chNFe;
        $emit_cpf_cnpj          = $tmp_xml->CNPJ;
        $emit_nome              = $tmp_xml->xNome;
        $ie                     = $tmp_xml->IE;
        $array_data_emissao     = explode("T", $tmp_xml->dhEmi);
        $data_emissao           = str_replace("-", "", $array_data_emissao[0]);
        $data_emissao           = str_replace("-", "", $data_emissao);
        $tipo_nota              = $tmp_xml->tpNF;
        $valor_nf               = $tmp_xml->vNF;
        $digest_value           = $tmp_xml->digVal;
        $data_hora_recebimento  = formatar_data_hora($tmp_xml->dhRecbto);
        $situacao_nfe           = $tmp_xml->cSitNFe;
        $protocolo              = $tmp_xml->nProt;
        // $confirmacao            = "";
        
        return "001|".$chave."|".$nsu."|".$emit_cpf_cnpj."|".$emit_nome."|".$ie."|".$data_emissao."|".$tipo_nota."|".$valor_nf."|".$digest_value."|".$data_hora_recebimento."|".$protocolo."|".$situacao_nfe."|\n";
    }
    
    
    function monta_resEvento($tmp_xml, $nsu)
    {
        $chave                  = $tmp_xml->chNFe;
        $data_hora_recebimento  = formatar_data_hora($tmp_xml->dhRecbto);
        $emit_cpf_cnpj          = $tmp_xml->CNPJ;
        $data_hora_evento       = formatar_data_hora($tmp_xml->dhEvento);
        $tp_evento              = $tmp_xml->tpEvento;
        $seq_evento             = $tmp_xml->nSeqEvento;
        $desc_evento            = $tmp_xml->xEvento;
        $nProt                  = $tmp_xml->nProt;
        
        return "002|".$chave."|".$nsu."|".$emit_cpf_cnpj."|".$data_hora_evento."|".$tp_evento."|".$seq_evento."|".$desc_evento."|".$data_hora_recebimento."|".$nProt."|\n";
    }
    
    function monta_procEventoNFe($tmp_xml, $nsu)
    {
        $chave                  = $tmp_xml->evento->infEvento->chNFe;
        $data_hora_recebimento  = formatar_data_hora($tmp_xml->retEvento->infEvento->dhRegEvento);
        $emit_cpf_cnpj          = $tmp_xml->CNPJ;
        $data_hora_evento       = formatar_data_hora($tmp_xml->evento->infEvento->dhEvento);
        $tp_evento              = $tmp_xml->evento->infEvento->tpEvento;
        $seq_evento             = $tmp_xml->evento->infEvento->nSeqEvento;
        $desc_evento            = $tmp_xml->evento->infEvento->detEvento->descEvento." - ".$tmp_xml->evento->infEvento->detEvento->xJust;
        $nProt                  = $tmp_xml->evento->infEvento->detEvento->nProt;
        
        return "002|".$chave."|".$nsu."|".$emit_cpf_cnpj."|".$data_hora_evento."|".$tp_evento."|".$seq_evento."|".$desc_evento."|".$data_hora_recebimento."|".$nProt."|\n";
    }
    
    
    function formatar_data_hora($data_hora)
    {
        $array_data_hora_evento = explode("T", $data_hora);
        $data_evento            = $array_data_hora_evento[0];
        $data_evento            = str_replace("-", "", $data_evento);
        $data_evento            = str_replace("-", "", $data_evento);
        
        $hora_evento            = explode("-", $array_data_hora_evento[1]);
        $hora_evento            = str_replace(":", "", $hora_evento[0]);
        $hora_evento            = str_replace(":", "", $hora_evento);
        
        return $data_evento.$hora_evento;
    }
    
    
    
    
    
    // Formata o retorno para um txt: código retorno|motivo
    //$retorno  = exec("php ../ferramentas/f.texto.tag.php cStat '".$xml_retorno."'");
    //$retorno .= "|";
    //$retorno .= exec("php ../ferramentas/f.texto.tag.php xMotivo '".$xml_retorno."'");
    //$retorno .= "|";
    //$retorno .= exec("php ../ferramentas/f.texto.tag.php nRec '".$xml_retorno."'");
    //$retorno .= "|";
    //$retorno .= exec("php ../ferramentas/f.texto.tag.php dhRecbto '".$xml_retorno."'");
    
    
    // grava o retorno do envio de mdfe
    //file_put_contents($dados['mdfe']['dir_retorno'].$nome_raiz."-retorno.txt",$retorno);
    
    
    
    
    
    
    
    
