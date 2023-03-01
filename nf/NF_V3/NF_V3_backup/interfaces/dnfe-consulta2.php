<?php 
    /*
        Programa:  dnfe-consulta.php
        Descricão: 
        Autor:     
    */ 

    require_once( "../funcoes/flog.php"               ) ; // para gravar log                                     // 
    require_once( "../funcoes/fdebug.php"             ) ; // Para realizar debug                                 // 
    require_once( "../funcoes/freplace.php"           ) ; // Replace de dados                                    // 
    require_once( "../classes/CAssinaturaDigital.php" ) ; // Usado para assinar o xml                            // 
    require_once( "../classes/validaXml.php"          ) ; // Usado para validar o xml                            // 
    require_once( "../classes/buscaWebService.php"    ) ; // Usado para buscar os dados do arquivo de webService // 
    require_once( "../classes/soapWebService.php"     ) ; // Usado para enviar envelope soap                     // 

    // Carrega as configurações de clientes e sistema
    $config = parse_ini_file("../config/config.ini");

    $temp=$config['temp']."dnfe/";
    // Dados que são passados pelo programa chamador (cobol)
    $arquivo_txt = explode("|", file_get_contents($argv[1]));
    // Se não for passado o parametro, buscar estado RS
    $estado = (isset($argv[2]) ? $argv[2]:'BR');

    // Nome arquivo de saida
    $nome_arquivo_saida = explode("/",$argv[1]);
    $nome_arquivo_saida = end($nome_arquivo_saida);
    $nome_arquivo_saida = str_replace("DNFE", "DNFER", $nome_arquivo_saida);
    
    
    
    
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

        return "002|".$nsu."|".$chave."|".$emit_cpf_cnpj."|".$data_hora_evento."|".$tp_evento."|".$seq_evento."|".$desc_evento."|".$data_hora_recebimento."|".$nProt."|\n";
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
    
    
    // Cria diretorios de trabalho
    function cria_diretorios($dir)
    {
        // echo "*$dir";
        exec('php ../ferramentas/cria_diretorios.php '.$dir);
    }

    // main
    cria_diretorios($temp);

    $cnpj = trim($arquivo_txt[0]);
    $cpf = trim($arquivo_txt[1]);
    $uf = trim($arquivo_txt[2]);
    $tipo_ambiente = trim($arquivo_txt[3]);
    $ultimo_nsu = trim($arquivo_txt[4]);
    $seq_nsu = trim($arquivo_txt[5]);
    $chaveNFe = str_replace("\n", "", trim($arquivo_txt[6]));

    $ambiente = ($tipo_ambiente == "1" ? "producao" : "homologacao");

    $dados = parse_ini_file($config['dados']."/config_cliente/".$cnpj.".ini",true);

    // [22/06/2017] - Fernando H. Crozetta: cria arquivos pem, se for necessario
    exec("php ../ferramentas/cria_pem.php ".$dados['certificado']['arquivo_certificado']." ".$dados['certificado']['senha']);

    $dados_ws = new BuscaWebService($estado,'dnfe',$ambiente);
    $array_webservice = $dados_ws->buscarServico("consulta",$dados['dnfe']['versao']);
    // print_r($dados_ws);exit();

    // montagem de arquivo soap para envio
    $template_soap = file_get_contents($config['servicos']."/template_soap.xml");

    // Carregar cabecalho e corpo do tipo de servico
    $array_tmp =array(
        '<soap12:Header>ALTERAR_TAG_CABECALHO</soap12:Header>' => "",
        'ALTERAR_TAG_CORPO' => $array_webservice->tag_corpo
    );
    $template_soap = freplace($template_soap,$array_tmp);

    // Carregar os dados da mdfe para o xml
    $array_substituicao= array(
        'ALTERAR_DADOS_NAMESPACE'   => $array_webservice->namespace , 
        'ALTERAR_DADOS_UF'          => $uf                          , 
        'ALTERAR_DADOS_URL'         => $array_webservice->url       , 
        'ALTERAR_DADOS_VERSAO_DADOS'=> $array_webservice->versao    , 
        'ALTERAR_TIPO_AMBIENTE'     => $tipo_ambiente               , 
        'ALTERAR_CUF_AUTOR'         => "41"                         , 
        'ALTERAR_CNPJ'              => $cnpj                        , 
        'ALTERAR_CPF'               => ""                           , 
        'ALTERAR_ULT_NSU'           => $ultimo_nsu                  , 
        'ALTERAR_NSU'               => ""                           , 
    	'ALTERAR_CH_NFE'            => ""                           , 
        "\n"                        => ''                           , 
        "\r"                        => ''                           , 
        "\t"                        => ''                           , 
    );

    $template_soap = freplace($template_soap,$array_substituicao);
    $arquivo_xml   = $dados['dnfe']['dir_retorno']."dnfe.xml";
    
    $soap = new SoapWebService($arquivo_xml,$cnpj,$array_webservice);
    
    $xml_webservice = $soap->comunicar();
    
    $xml_webservice = explode("<nfeDistDFeInteresseResult>" ,  $xml_webservice)[1];
    $xml_webservice = explode("</nfeDistDFeInteresseResult>",  $xml_webservice)[0];
    
    
    $xml_retorno = simplexml_load_string($xml_webservice);
    
    $retorno_cobol   =               array() ; 
    $retorno_cobol[] =                 "000" ; 
    $retorno_cobol[] =                 $cnpj ; 
    $retorno_cobol[] =                   $uf ; 
    $retorno_cobol[] =        $tipo_ambiente ; 
    $retorno_cobol[] =   $xml_retorno->cStat ; 
    $retorno_cobol[] = $xml_retorno->xMotivo ; 
    $retorno_cobol[] =        date('YmdHis') ; 
    $retorno_cobol[] =  $xml_retorno->ultNSU ; 
    $retorno_cobol[] =  $xml_retorno->maxNSU ; 
    
    $retorno_cobol = implode('|', $retorno_cobol);
    // echo ">$retorno_cobol<" ; exit() ; 

    $retorno_cobol_tipo_001 = "";
    $retorno_cobol_tipo_002 = "";

    /**
     * $criar_arquivo controle para um bug da SEFAZ
     * Para criar apenas notas referentes ao CNPJ solicitado de destinadas
     * Caso contrario nao cria os arquivos.
     */
    $criar_arquivo = true;
    
    // $dadosDnfe = $dados['dnfe'];
    $dadosDnfe = $dados['dnfe'];
    
    $zips = $xml_retorno->loteDistDFeInt->docZip;
    
    $contagemZips = $zips;
    
    $dadosDnfe['dir_procNFe'] = "/user/nfe/$cnpj/DEST/proc/" ;
    
    for($i=0; $i < count($contagemZips); $i++)
    {
        $docZip        =      $zips[$i] ; 
        $caminho       =             "" ; 
        $sufixo        =             "" ; 
        $prefixo       =             "" ; 
        $criar_arquivo =           true ; 
        $docZipNSU     = $docZip["NSU"] ; 
        
        // cria o arquivo zip
        file_put_contents("$caminho{$docZip["NSU"]}.gz", base64_decode($docZip));
        
        // descompacta e remove o  arquivo zip
        $retorno = shell_exec("gunzip -f -c $caminho$docZipNSU.gz ; rm $caminho$docZipNSU.gz");
        
        $tmp = simplexml_load_string($retorno);
        
        switch ($docZip["schema"]) {

            case 'resNFe_v1.00.xsd':
                $caminho = $dadosDnfe['dir_resNFe'];
                $prefixo = $tmp->chNFe;
                $sufixo  = "-resumo.xml";
                cria_diretorios($dadosDnfe['dir_resNFe']);
                $retorno_cobol_tipo_001 .= monta_reNFe($tmp, $docZipNSU);
                break;

            case 'resEvento_v1.00.xsd':
                $caminho = $dadosDnfe['dir_resEvento'];
                $prefixo = $tmp->chNFe;
                $sufixo  = "-evento.xml";
                cria_diretorios($dadosDnfe['dir_resEvento']);
                $retorno_cobol_tipo_002 .= monta_resEvento($tmp, $docZipNSU);
                break;

            case 'procNFe_v4.00.xsd':
            case 'procNFe_v3.10.xsd':
                $caminho = $dadosDnfe['dir_procNFe'];
                $prefixo = $tmp->protNFe->infProt->chNFe;
                $sufixo  = "-procNFe.xml";
                cria_diretorios($dadosDnfe['dir_procNFe']);

                if ($tmp->NFe->infNFe->dest->CNPJ != $cnpj){
                    $criar_arquivo = false;
                }
                break;

            case 'procEventoNFe_v1.00.xsd':
                $caminho = $dadosDnfe['dir_procEventoNFe'];
                $prefixo = $tmp->evento->infEvento->chNFe;
                $sufixo  = "-procEventoNFe.xml";
                cria_diretorios($dadosDnfe['dir_procEventoNFe']);
                $retorno_cobol_tipo_002 .= monta_procEventoNFe($tmp, $docZipNSU);
                break;
            
            default:
                echo ("\n".$docZip["schema"]);
                $caminho = $dadosDnfe['dir_naoImplementado'];
                $prefixo = $docZipNSU.$docZip["schema"];
                $sufixo  = "-nao_implementado.xml";
                cria_diretorios($dadosDnfe['dir_naoImplementado']);
                break;
        }
        
        
        if ($criar_arquivo)
        {
            // echo ("\n~>{$dadosDnfe['dir_saida_cobol']}$nome_arquivo_saida<~");
            // echo ("\n~>$retorno_cobol$retorno_cobol_tipo_001$retorno_cobol_tipo_002<~\n\n");
            echo ("\n~>$caminho$prefixo$sufixo<~");
            // echo ("\n~>/var/www/nfe/recebe/$prefixo$sufixo<~");
            // echo ("\n~>$retorno<~\n\n");
            // echo ("\n~>-------------------<~\n\n");
            
            file_put_contents("{$dadosDnfe['dir_saida_cobol']}$nome_arquivo_saida", "$retorno_cobol$retorno_cobol_tipo_001$retorno_cobol_tipo_002");
            file_put_contents("$caminho$prefixo$sufixo", $retorno);
            
            /* Eduardo - Precisa criar diretorio no CNPJ.ini */
            file_put_contents("/var/www/nfe/recebe/$prefixo$sufixo", $retorno);
        } else {
            $criar_arquivo = true;
        }
        
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
    
    
    
    
    
    