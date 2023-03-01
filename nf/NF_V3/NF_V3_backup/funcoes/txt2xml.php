<?php
    // https://www.sefaz.rs.gov.br/nfe/nfe-val.aspx
    
    /**
     * Chamada do conversor
     * 
     * Com a mudanca do programa, talvez seja necessario implementar o envio do tipo de nota como 3 parametro
     *
     * @param string $caminho_txt
     * @param string $caminho_xml
     * @param string $tipo
     * @param boolean $debugger
     * @return void
     */
    function txt2xml($caminho_txt='', $caminho_xml='', $tipo='', $chave="")
    {
        $classe = "class_txt2xml_$tipo"    ; // Decide qual classe sera instanciada.
        $txt2xml = new $classe()           ; // Faz a instancia da classe 
        // $txt2xml->setDebugger($debugger)   ; // Se for modo debugger, liga ele
        $txt2xml->setTXT($caminho_txt)     ; // Caminho do txt de entrada, gerado pelo cobol
        $txt2xml->setChave($chave)         ; // Caminho do txt de entrada, gerado pelo cobol
        $txt2xml->setXML($caminho_xml)     ; // Caminho de saida do XML gerado
        $convercao = $txt2xml->converter() ; // Faz a conversao
        
        if ($convercao===true){
            // Salva o XML no diretorio passado
            $txt2xml->salvar() ; 
            return true;
        } else {
            return $convercao;
        }
    }
    
    
    
    
    
    class class_txt2xml
    {
        var $debugger    = false   ; // Debugger, se true, printa os erros e o xml no console
        var $versao                ; // versao da nota 
        var $caminho_txt           ; // caminho do txt do cobol 
        var $caminho_xml           ; // caminho do xml de saida
        var $txtCobol              ; // TXT do cobol
        var $DOMDocument =      -1 ; // guardar a instancia para evitar instanciar varias vezes
        var $template              ; // templete da nota, contem o mapa das infos e a estrutura de montagem
        var $estrutura             ; // estrutura de montagem da nota 
        var $estrutura_limpa       ; // copia do xml sem valores, usado para clonar nohs
        var $mapa                  ; // mapa com o xpath de cada dado do txt
        var $txtMapeado  = array() ; // array com os dados do txt do cobol mapeados array('linnha'=>array(dado1, dado2, dado3 ...))
        var $crl_linhas  = array() ; // faz o controle de repeticao de linhas
        var $cacheXpaths = array() ; // Salva as consultas xpath para ajudar na performanse
        var $cacheInsert = array() ; // Nohs removidos serao salvos aqui para reinserir novamente
        var $array_xml   = array() ; // array com a estrutura do xml para gerar o xml dinamicamente
        
        // abstract
        var $linhasIgnoradas  = array() ; // Lista de inhas ignoradas 
        var $escopoIni;
        var $escopoFim;
        
        // Chave da nota fiscal
        var $chave = '';
        
        
        
        /**
         * __construct
         * 
         * Construtor
         * 
         */
        function __construct()
        {
            /**/
        }
        
        
        
        /**
         * converter
         * 
         * Faz a conversao
         *
         * @return void
         */
        function converter()
        {
            
            // Carrega o TXT do Cobol
            $this->getTxt();
            
            // Carrega o TxtMapeado
            $this->carregaTxtMapeado();
            
            
            $pathTxtCobol    = $this->caminho_txt                   ; 
            $pathSaidaXML    = $this->caminho_xml                   ; 
            $pathMapa        = $this->getPathMapa()                 ; 
            $versao          = $this->versao                        ; 
            $linhasIgnoradas = implode('|', $this->linhasIgnoradas) ; 
            $sempreRepetir   = implode('|', $this->sempreRepetir  ) ; 
            
            $comandoPython = "python ../funcoes/txt2xml.py $pathTxtCobol $pathSaidaXML $pathMapa '$versao' '$linhasIgnoradas' '$sempreRepetir'" ; 
            
            $return = shell_exec($comandoPython);
            
            
            // print_r($return) ; exit() ; 
            
            
            if (!is_null($return)){
                $return = str_replace("\n", " ", $return);
                return $return;
            }
            
            $this->setEstrutura(file_get_contents($pathSaidaXML));
            
            // Chama as funcoes de legado
            $this->aposCriar();
            
            // limpa o XML
            $this->clearXML();
            
            return true;
        }
        
        function getPathMapa()
        {
            $this->versao = $this->txtMapeado['A'][0][1]             ; // carrega o numero da versao
            $tipo         = strtolower($this->txtMapeado['A'][0][2]) ; // carrega o tipo de nota
            return "../templates/{$tipo}2xml.json"                   ;
        }
        
        
        
        /**
         * carregarTemplate
         * 
         * Carrega o template do Json, cerrega os dados da estrutura e o mapa de valores
         *
         * @return void
         */
        function carregarTemplate()
        {
            // Carrega o template
            $this->template = $this->getTemplate();
            
            // carrega o mapa
            $this->mapa = $this->template->mapa;
            
            // Cria o xml
            $xml = $this->getEstrutura();
            
            // Carrega a estrutura do xml, o segundo parametro, true, indica que devera carregar uma copia do xml e manter ela limpa
            $this->setEstrutura($xml, true);
        }
        
        
        
        /**
         * getEstrutura
         * 
         * carrega a estrutura em XML
         */
        function getEstrutura()
        {
            // Monta a estrutura do xml
            $xml = $this->criarArray_xml();
            
            return $xml;
        }
        
        
        
        /**
         * carregaTxtMapeado
         * 
         * Carrega o a varavel txtMapeado
         *
         * @return void
         */
        function carregaTxtMapeado()
        {
            // percorre cada linha
            foreach (explode("\n", $this->txtCobol) as $linha)
            {
                $dados = explode("|", $linha);
                
                // Controlador de linhas
                $this->crlLinha($dados[0]);
                
                $ln = $dados[0]                ; // Linha
                $rp = $this->crl_linhas[ $ln ] ; // repeticao
                
                // Percorre cada dado da linha
                foreach ($dados as $dado)
                {
                    // Se nao existir a posicao referente a linha entao cria
                    if(!isset($this->txtMapeado[$ln]))
                        $this->txtMapeado[$ln] = array();
                    
                    // Se nao existir a posicao referente a linha entao cria
                    if( !isset( $this->txtMapeado [ $ln ] [ $rp ] ) )
                        $this->txtMapeado [ $ln ] [ $rp ] = array();
                    
                    // adimciona do novo dado na posicao da linha
                    $this->txtMapeado [ $ln ] [ $rp ][] = $dado;
                }
            }
            
            /// limpa o controlador de linhas
            $this->limpar_crlLinha();
        }
        
        
        
        /**
         * processTxt
         * 
         * Processa o txt para criar o XML final 
         *
         * @return void
         */
        function processTxt()
        {
            // percorre cada linha do txtMapeado
            foreach ($this->txtMapeado as $linha => $dados)
            {
                // A primera linha do arquivo txt do cobol sempre vem vazia.
                if (!empty($linha))
                {
                    $this->processRepeticao($linha);
                }
            }
        }
        
        
        
        /**
         * processRepeticao
         * 
         * Processa as repeticoes
         *
         * @param [type] $linha
         * @return void
         */
        function processRepeticao($linha)
        {
            $countFor = count($this->txtMapeado[$linha]) ; // Contagem de dados da linha do Map 
            $countMap = count($this->mapa->$linha)       ; // Contagem de dados da linha do Map 
            
            // Percorre as repeticoes de cada linha
            for ($repeticao=0; $repeticao < $countFor; $repeticao++)
            {
                // $countMap = count($this->mapa->$linha)                     ; 
                $countTxt = count($this->txtMapeado[$linha][$repeticao])-1 ; // Contagem de dados da linha do Txt 
                
                // Se o txtMapeado tiver a mesma qauntidade dados que o mapa
                if ($countMap === $countTxt)
                {
                    // Lista de linhas ignorados
                    if (!in_array($linha, $this->linhasIgnoradas))
                    {
                        // var_dump($linha);
                        $this->processLine($linha, $repeticao);
                    }
                } 
                
                // se nao, dispara um erro
                else {
                    $this->log("Erro na linha $linha foram enviados $countTxt dados, o template espera $countMap dados");
                }
            }
        }
        
        
        
        /**
         * processLine
         * 
         * processa a linha
         *
         * @param string $linha
         * @return void
         */
        function processLine($linha, $repeticao)
        {
            // Contagem de dados da linha, -1 pois o cobol manda um pipe a mais no final
            $count = count($this->txtMapeado[$linha][$repeticao]) - 1 ; 
            
            // percorre os dados ad linha
            for ($indexDado=0 ; $indexDado < $count ; $indexDado++)
            {
                $xpath = $this->mapa->{$linha}[$indexDado]                 ; // busca o xpath no mapa
                $value = $this->txtMapeado[$linha][$repeticao][$indexDado] ; // busca o daod no txtMapeado
                
                $this->addOnXML($xpath, $value, $repeticao, $linha)        ; // adiciona no XML
            }
        }
        
        
        
        /**
         * setEstrutura
         * 
         * Seta a estrutura
         *
         * @param string $strEstrutura
         * @param string $loadLimpo     se vai carregar o xmlLimpo, apenas usado quando gera o XML pela primeira vez
         * @return void
         */
        function setEstrutura($strEstrutura, $loadLimpo=false)
        {
            
            // converte o texto para um objeto XML;
            // seta a estrutura para a classe trabalhar
            $this->estrutura = simplexml_load_string($strEstrutura);
            // print_r("\n estrutura " . debug_backtrace()[1]['function'] . " >>> \n");
            // print_r($this->estrutura->saveXML());
            // print_r("\n <<< estrutura");
            
            // apenas entrara aqui para carregar o xml uma unica vez, ele vai estar limpo, sem dados, usado apenas para clonar
            if ($loadLimpo){
                $this->estrutura_limpa = simplexml_load_string($strEstrutura);
                // print_r("\n estrutura_limpa " . debug_backtrace()[1]['function'] . " >>> \n");
                // print_r($this->estrutura_limpa->saveXML());
                // print_r("\n <<< estrutura_limpa");
            }
        }
        
        
        
        /**
         * getXpath 
         * 
         * retorna o noh que tera o valor inserido
         * 
         * Para a manutencao futura:
         * O objetovo desse metodo eh fazer o minimo possivel de consultas de xpaths
         * para isso o metodo quebra a query em 2 partes, campo e caminho da raiz ateh o parent desse campo. 
         * "/raiz/parent/campo" -> ["/raiz/parent","campo"]
         * sera feito a query xpath apenas do parent do elemento e do retorno dele, o metodo busca pelo campo
         * o array cacheXpaths guarda as querys e os resultados delas
         *
         * @param string  $xpath      qyery xpath da raiz ao campo
         * @param string  $value      valor que sera inserido 
         * @param integer $repeticao  numero da repeticao do noh
         * @param integer $linha      nome da linha atual
         * @return object $node       noh que sera inserido o valor
         */
        function getXpath($xpath='', $value='', $repeticao=0, $linha=0)
        {
            // quebra a query em 2 partes, campo e caminho da raiz ateh o parent desse campo. 
            // "/raiz/parent/campo" -> ["/raiz/parent","campo"]
            // uma alternativa seria com regexp, mas nos meus testes pareceu menos performatitico
            // $parts = preg_split('~\/(?=[^\/]*$)~', $xpath); $xpathp = $parts[0] ; $field  = $parts[1] ; 
            $xpathp = explode('/', $xpath)  ; 
            $field  = array_pop($xpathp)    ; 
            $xpathp = implode('/', $xpathp) ; 
            
            
            // Verifica se o xpath ja foi feito, se nao, faz ele
            if (!isset($this->cacheXpaths[$xpathp])){
                $this->cacheXpaths[$xpathp] = $this->estrutura->xpath($xpathp);
            }
            
            // cacheXpaths guarda o xpath do parent do campo
            $parent = $this->cacheXpaths[$xpathp]   ; 
            
            // se o noh ainda nao existe no xml, cria ele
            if (!isset($parent[$repeticao])){
                return $this->criar_no($xpath, $value, $repeticao, $linha);
            }
            
            // se for atributo 
            if ($field[0] == '@'){
                // nao eh possivel acessar atributos no objeto de retorno, para acesssar eles, precisamos fazer outra consulta xpath
                $node  = $parent[$repeticao]->xpath($field)[0];
            }else{
                // se nao for atributo, pega o campo no objeto do parent dele
                $node = $parent[$repeticao]->{$field} ; 
            }
            
            // retorna o noh do campo
            return $node;
        }
        
        
        
        /**
         * addOnXML
         * 
         * Adiciona um valor a um caminho do XML
         *
         * @param string  $xpath      qyery xpath da raiz ao campo
         * @param string  $value      valor que sera inserido 
         * @param integer $repeticao  numero da repeticao do noh
         * @param integer $linha      nome da linha atual
         * @return void
         */
        function addOnXML($xpath='', $value='', $repeticao=0, $linha=0)
        {
            // Se o xpath for linha, ignora, pois o primeiro dado de cada linha sera o nome da linha
            if ($xpath === 'linha'){
                return  0 ;
            } 
            
            // Se o valor for " " eh nullo pois o Cobol manda espaco para valores vazios
            if ($value === " "){
                $value="" ;
            } 
            
            // Pega o elemento para salvar o valor
            $node = $this->getXpath($xpath, $value, $repeticao, $linha);
            
            // Verifica se o caminho nao existe no XML e dispara um erro se nao existir.
            if (!isset($node->{0})) {
                $this->log("Mapa com caminho incorreto $xpath \n\n");
            }
            
            // se o no existe, coloca o valor nele, se nao, dispara um erro
            $node->{0} = trim($value);
            // $node->{0} = $value;
        }
        
        
        
        /**
         * criar_no
         * 
         * Faz o clone do noh 
         *
         * @param string  $xpath      qyery xpath da raiz ao campo
         * @param string  $value      valor que sera inserido 
         * @param integer $repeticao  numero da repeticao do noh
         * @param integer $linha      nome da linha atual
         * @return void
         */
        function criar_no($xpathQ='', $value='', $repeticao='', $linha)
        {
            // Copia o xpath para retornar no final
            $originalXpath = $xpathQ; 
            
            
            // quebro o xpath na barra 
            $xpathQ = explode('/', $xpathQ);
            // removo o ultimo 
            $trash  = array_pop($xpathQ);
            // e monto o xpath novamente, isso para remover o ultimo nivel do xpath
            $xpathQ = implode('/', $xpathQ);
            
            
            // Carrego o xml no dom, false carrega o xml limpo
            $doc = $this->loadXmlOnDom(false);
            // instancio o comXpath para poder encontrar mais faciil o noh para clonar 
            $xpath = new domXPath($doc);
            
            
            // faz a consulta pelo Xpath 
            $xpathQuery = $xpath->query($xpathQ) ;
            // pega o primeiro noh encontrado pelo xpath 
            $child = $xpathQuery->item(0) ;
            // pega o parent do cara encontrado, para depois poder anexar o clone
            $parent = $child->parentNode ;
            
            
            #$clone = $this->getClearNode($originalXpath);
            
            // $clone = $parent->importNode( $clone, true );
            // $clone = $doc->importNode( $clone, true );
            
            
            // print_r($clone->childNodes);
            
            $clone = $child->cloneNode(true);
            
            #$clone = $this->clearNode($clone);
            
            // exit();
            
            
            
            // pega o proximo noh
            $next = $this->nextElement($child);
            // se existir proximo noh, remove ele e todos os proximos
            if($next){
                $this->remove_Next($parent, $next);
            }
            
            // faz a contagem de quantos nohs serao nescessarios, -1 pois o primeiro noh ja esta criado
            $nosNescesarios = count($this->txtMapeado[$linha]) - 1;
            
            // cria todos os nohs nescessarios
            while($nosNescesarios > 0)
            {
                
                //*
                $parent ->appendChild ( $clone ) ; 
                
                /*
                // clona o noh e coloca o clone na extrutura
                $parent->appendChild(
                    // $child->cloneNode(true)
                    // $this->getClearNode($originalXpath)
                    $clone
                );
                */
                // contador de nos cescessarios
                $nosNescesarios--;
            }
            
            // se tinha um proximo noh
            if($next){
                // recoloca todos os proximos nos
                $this->recoloca_Next($parent);
            }
            
            // seta o xml com o clone na minha classe
            $this->setEstrutura($doc->saveXML());
            
            // refaz a query xpath para ter em cache os novos nohs criados
            $this->cacheXpaths[$xpathQ] = $this->estrutura->xpath($xpathQ);
            
            // insere os dados no novo no
            return $this->getXpath($originalXpath, $value, $repeticao, $linha);
            
            
        }
        
        
        
        
        function clearNode($clonedNode)
        {
            $nodes = $clonedNode->childNodes;
            
            foreach($nodes as $node){
                if ($node->nodeType == 1){
                    // print("\ntagName: "   . $node->tagName   ) ; 
                    $node->nodeValue = '';
                    $this->clearNode($node);
                } else {
                    $node->nodeValue = '';
                    // print("FOI?");
                    /*
                    print_r("--------------");
                    print_r("\n nodeName: "  . $node->nodeName  );
                    print_r("\n nodeType: "  . $node->nodeType  );
                    print_r("\n nodeValue: " . $node->nodeValue );
                    print_r("--------------");
                    */
                }
            }
            
            return $clonedNode;
            
            
        }
        
        
        
        
        
        function getClearNode($xpathQ)
        {
            // quebro o xpath na barra 
            $xpathQ = explode('/', $xpathQ);
            // removo o ultimo 
            $trash  = array_pop($xpathQ);
            // e monto o xpath novamente, isso para remover o ultimo nivel do xpath
            $xpathQ = implode('/', $xpathQ);
            
            // Carrego o xml no dom, false carrega o xml limpo
            $doc = $this->loadXmlOnDom(false, false);
            // instancio o comXpath para poder encontrar mais faciil o noh para clonar 
            $xpath = new domXPath($doc);
            
            
            // faz a consulta pelo Xpath 
            $xpathQuery = $xpath->query($xpathQ) ;
            // pega o primeiro noh encontrado pelo xpath 
            $child = $xpathQuery->item(0) ;
            
            // pega o parent do cara encontrado, para depois poder anexar o clone
            // $parent = $child->parentNode ;
            
            $clone = $child->cloneNode(true);
            // $clone = $child;
            
            return $clone;
        }
        
        
        
        
        /**
         * remove_Next
         * 
         * salva todos os proximos nohs e ermove eles.
         * 
         * @param object $parent reference
         * @param object $elm    reference
         * @return void
         */
        function remove_Next(&$parent, &$elm)
        {
            // salva o noh em um array 
            $this->cacheInsert[] = $elm->cloneNode(true);
            // Pega o proximo
            $next = $this->nextElement($elm);  
            // remove o noh 
            $elm->parentNode->removeChild($elm);
            // Se ele tem proximo
            if($next){
                // chama recursivamente esse mesmo metodo
                return $this->remove_Next($parent, $next);
            } else {
                // se nao tem proximo, encerra a recursividade
                return true;
            }
        }
        
        
        
        /**
         * recoloca_Next
         * 
         * pega o cache dos nohs que foram removidos e recloca eles 
         * 
         * @param object $parent
         * @return void
         */
        function recoloca_Next($parent)
        {
            // percorre o cache dos nos que foram removidos
            foreach($this->cacheInsert as $clone){
                // e recoloca eles
                $parent->appendChild($clone);
            }
            
            //  Limpa o cache de nohs removidos
            $this->cacheInsert = array();
        }
        
        
        
        /**
         * nextElement
         * 
         * busca recursivamente o proximo noh valido e recorna ele
         * 
         * @param object $node noh que sera buscado o vizinho
         * @return void
         */
        function nextElement($node)
        {
            // Se node for false, eh para encerrar a recursividade
            if(!$node){
                return false;
            }
            
            // pega o vijzinho do noh
            $next = $node->nextSibling;
            
            // se nao tem vizinho, retorna false para encerarr a recursividade
            if(!$next){
                return false;
            }
            
            // Se o tipo de noh for 3 (valor ou atributo), ignora e busca o proximo
            if($next->nodeType === 3){
                return $this->nextElement($next);
            }
            
            // retorna o proximo noh do noh passado
            return $next;
        }
        
        
        
        /**
         * crlLinha
         * 
         * Faz o controle de linhas, para saber quantas vezes cada uma repete array('a'=>3, 'b'=>5)
         *
         * @param strint $linha
         * @return void
         */
        function crlLinha($linha)
        {
            if (!isset($this->crl_linhas[$linha])){
                $this->crl_linhas[$linha] = 0;
            }else{
                $this->crl_linhas[$linha]++;
            }
        }
        
        
        
        /**
         * limpar_crlLinha
         * 
         * limpa o contador de linhas para poder usar em outras funcoes
         *
         * @return void
         */
        function limpar_crlLinha()
        {
            $this->crl_linhas=array();
        }
        
        
        
        /**
         * aposCriar
         * 
         * Monta a chave xml
         *
         * @return void
         */
        function aposCriar()
        {
            // pega a instancia do DOM
            $doc = $this->loadXmlOnDom();
            $this->montaChaveXML($doc, $this->versao);
            $this->estrutura = $doc;
        }
        
        
        
        /**
         * clearXML
         * 
         * Limpa o XML, removendo as tags vazias 
         *
         * @return void
         */
        function clearXML()
        {
            // pega o xml
            $doc = $this->loadXmlOnDom(false);
            // instancia o xpath para o xml
            $xpath = new DOMXPath($doc);
            // Remove as tags vazias
            $this->removeTagsVazias($xpath);
            // carrega a estrutura xml para texto
            $xml = $doc->saveXML();
            // limpa os espacos em branco
            // $xml = $this->removeEspacos($xml);
            // salva o retorno da limpeza na estrutura
            $this->setEstrutura($xml);
        }
        
        
        
        /**
         * removeEspacos ##
         *
         * @param string $xml xml que sera limpo
         * 
         * @return void
         */
        function removeEspacos($xml)
        {
            // return $xml;
            // print ($xml);exit();
            
            $F = array();
            $R = array();
            
            $F[]="  ";$R[]=" ";
            
            // $F[]="'";$R[]="";  
            // $F[]='"';$R[]=''; 
            $F[]="&";$R[]=""; 
            $F[]="§";$R[]=""; 
            // $F[]=":";$R[]=""; 
            
            $F[]="> ";$R[]=">";
            $F[]=" >";$R[]=">";
            $F[]="< ";$R[]="<";
            $F[]=" <";$R[]="<";
            $F[]="\n";$R[]=" ";
            
            $F[]="> <";$R[]="><";
            
            
            $F[]="  ";$R[]=" ";
            
            // $find = array("  ", "> ", " >", "< ", " <", "\n") ; // Busca para a troca
            // $repl = array(" " , ">" , ">" , "<" , "<" , " " ) ; // 
            
            $xml = str_replace($F, $R, $xml);
            
            if ( strpos($xml, "  ") !== false ) {
                return $this->removeEspacos($xml);
            }
            
            
            // $this->setEstrutura($xml);
            return $xml ; 
        }
        
        /**
         * removeTagsVazias
         * 
         * Remove as tags vazias do XML
         *
         * @param [object] $xpath
         * @return void
         */
        function removeTagsVazias($xpath)
        {
            // Percorre os nos vazios e remove eles
            foreach( $xpath->query('//*[not(node())]') as $elemento ) {
                $elemento->parentNode->removeChild($elemento);
            }
            
            // Se ainda tiver nos a serem removidos, chama recursivamente o removeTagsVazias 
            if ($xpath->query('//*[not(node())]')->length > 0){
                return $this->removeTagsVazias($xpath);
            }
            // 
            // $this->setEstrutura($doc->saveXML());
        }
        
        
        
        /**
         * loadXmlOnDom
         * 
         * Retorna uma instancis do xml no dom 
         * metodo para guardar a instancia do dom para evitar instanciar varias vezes
         * 
         * @param boolean $preserveWhiteSpace se vai retornar o  xml formatado
         * @return void
         */
        function loadXmlOnDom($preserveWhiteSpace=true, $carregarXmlLimpo=false )
        {
            // se nao existir ainda, faz a instancia do DOM
            if ($this->DOMDocument === -1){
                $this->DOMDocument = new DOMDocument('1.0', 'utf-8');
            }
            
            // se for para preservar a formatacao ou nao
            $this->DOMDocument->preserveWhiteSpace = $preserveWhiteSpace;
            
            // verifica se vai carregar o xml com ou sem dados
            if ($carregarXmlLimpo){
                // carrega em texto o xml
                $xml = $this->estrutura_limpa->saveXML();
                // print $xml;
            }
            // carrega o xml sem dados
            else{
                // carrega em texto o xml limpo
                $xml = $this->estrutura->saveXML();
            }
            
            // pega o xml da classe e joga na instancia do dom
            $this->DOMDocument->loadxml($xml);
            
            // Retorna o dom
            return $this->DOMDocument;
        }
        
        
        
        /**
         * salvar
         * 
         * salva o xml no caminho setado
         *
         * @return void
         */
        function salvar()
        {
            // para as classes que extender poderem trabalhar o xml antes de ser salvo
            $this->before_salvar();
            
            // caminho da saida do xml
            $path = $this->getXML() ; 
            // converte o xml para uma stting para salvar
            $xml  = $this->estrutura->saveXML() ; 
            
            // abre o escopo, removendo a tag <?xml que o php cria
            $xml  = preg_replace("/<\?xml.*>/",$this->escopoIni,$xml) ; 
            // fecha o escopo
            $xml .= $this->escopoFim ; 
            
            // 
            // $xml = $this->removeEspacos($xml);
            
            // Salva em disco o xml gerado
            file_put_contents($path, $xml);
            
            // Se estiver debugando, faz a comparacao de integridade
            if ($this->debugger){
                // print_r($xml) ; // se estiver em modo debugger, printa no console o XML
                /*
                if ($this->compareFiles("/var/www/html/nf/NF_V3/testes_nfe/xml_base.xml", $path)) {
                    print "Comparation OK!\n";
                }else{
                    print "Comparation ERROR!\n";
                }
                */
            }
        }
        
        
        /**
         * before_salvar
         * para as classes que extender poderem trabalhar o xml antes de ser salvo
         *
         * @return void
         */
        function before_salvar()
        {
            /** */
        }
        
        
        
        /**
         * getTemplate
         * 
         * carrega o template 
         *
         * @return void
         */
        function getTemplate()
        {
            $this->versao = $this->txtMapeado['A'][0][1]                       ; // carrega o numero da versao
            $tipo         = strtolower($this->txtMapeado['A'][0][2])           ; // carrega o tipo de nota
            $arquivo      = file_get_contents("../templates/{$tipo}2xml.json") ; // carrega o arquivo .json do tempate
            $template     = (array) json_decode($arquivo)                      ; // carrega o json forcando ele como array
            return $template[$this->versao]                                    ; // retorna o template referente a versao
        }
        
        
        
        /**
         * setTXT
         * 
         * seta o caminho do txt d cobol
         *
         * @param string $caminho_txt
         * @return void
         */
        function setTXT($caminho_txt)
        {
            $this->caminho_txt = $caminho_txt;
        }
        
        
        
        /**
         * getTxt
         * 
         * Carrega o TXT do Cobol
         *
         * @return void
         */
        function getTxt()
        {
            $this->txtCobol =  file_get_contents($this->caminho_txt);
            $this->txtCobol = $this->limpaString($this->txtCobol   ); // Funcao migrada do libs/ConvertMDFePHP.class.php
        }
        
        
        
        /**
         * setXML
         * 
         * seta o caminho do xml de saida
         *
         * @param string $caminho_xml
         * @return void
         */
        function setXML($caminho_xml)
        {
            $this->caminho_xml = $caminho_xml;
        }
        
        
        
        /**
         * getXML
         * 
         * retorna o caminho xml de saida para salvar
         *
         * @return void
         */
        function getXML()
        {
            return $this->caminho_xml;
        }
        
        
        
        /**
         * log
         * 
         * memtodo para controle de logs
         *
         * @param string $log
         * @return void
         */
        function log($log)
        {
            if ($this->debugger){
                print_r($log);
            }else{
                require_once("../funcoes/flog.php");
                flog($log);
                exit();
            }
        }
        
        
        
        /**
         * compareFiles
         * 
         * Metoddo bem doido para verificar a integridade do xml
         *
         * @param string $a path do arquivo a ser comparado ( arquivo base   ) 
         * @param string $b path do arquivo a ser comparado ( arquivo gerado ) 
         * @return void
         */
        function compareFiles($a, $b)
        {
            $bufferSize = 100             ; // tamannho da parte do arquivo a ser comparado
            $result     = true            ; // Resultado da comparacao true=ok false=erro
            $base       = fopen($a, 'rb') ; // abre o arrquivo de base 
            $novo       = fopen($b, 'rb') ; // abre o arrquivo gerado  
            
            // percorre o arquivo 
            while(!feof($base))
            {
                $parteBase = fread($base, $bufferSize) ; // Le parte do arquivo para comparar por partes
                $parteNovo = fread($novo, $bufferSize) ; // Le parte do arquivo para comparar por partes
                
                // se as partes forem diferentes
                if($parteBase !== $parteNovo)
                // if (strcasecmp($parteBase, $parteNovo))
                {
                    // retorno da funcao vira false para indicar que houve erro
                    $result = false ; 
                    
                    // exibe o erro no console
                    return $this->displayComparationError($parteBase, $parteNovo,  $bufferSize);
                    
                }else{
                    // print_r("\n$parteBase\n$parteNovo\n----------------------------------------------------------------------------------------------------\n");
                }
            }
            
            fclose($base) ; // fecha os arquivos
            fclose($novo) ; // fecha os arquivos
            
            // Resultado da comparacao true=ok false=erro
            return $result;
        }
        
        
        
        /**
         * displayComparationError
         * 
         * Monta um layout para comparar a parte do xml com erro
         *
         * @param string $parteBase  Parte do arquivo base
         * @param string $parteNovo  Parte do arquivo gerado
         * @param int    $bufferSize Tamanho da parte 
         * @return void
         */
        function displayComparationError($parteBase, $parteNovo, $bufferSize)
        {
            $bar    = ''    ; // barra que indica o erro
            $ok     = true  ; // para o for, para saber ateh onde o arquivo esta ok, oara controele da barr de erro
            
            // percorre char a char a parte do arquivo com erro
            for ($i = 0; isset($parteBase[$i]); $i++)
            {
                // Se a base e o novo estao iguais e nao houve erro ainda
                if ($parteBase[$i] === $parteNovo[$i] && $ok)
                {
                    // almenta a barra
                    $bar.='~';
                }
                // se houve erro
                else
                {
                    // se compara com ok para saber a primeira entyrada, para posicioanr o ponteirro de erro
                    if ($ok)
                    {
                        $ok  = false ; // marca que houve erro
                        $bar.='|'    ; // posiciona o marcador de erro
                    }
                    // apos colocar o marcador, preenche o resto da barra com espacos
                    $bar.=' ';
                }
            }
            
            // Saida
            $print = array();
            
            $parteBase = str_replace("\n", " ", $parteBase); // limpa as quebras de linha da parte
            $parteNovo = str_replace("\n", " ", $parteNovo); // limpa as quebras de linha da parte
            
            // Monta o layout da saida
            $print[]='.-------'.str_repeat('-', $bufferSize).'--.';
            $print[]="| BASE | $parteBase |";
            $print[]="|------| $bar|";
            $print[]="| NOVO | $parteNovo |";
            $print[]="'-------".str_repeat('-', $bufferSize)."--'";
            
            // converte para string
            $print = implode("\n", $print);
            
            // pmrinta no console o retorno
            print_r("\n$print\n");
        }
        
        
        
        /**
         * pause
         * 
         * Metodo para debugger
         *
         * @param string $P mensagem antes de pausar
         * @return void
         */
        function pause($P=''){
            print_r($P)                                    ; // Printa a mensagem na tela
            $this->parar = fgets(fopen("php://stdin","r")) ; // Pausa a execucao
        }
        
        
        
        /**
         * criarArray_xml
         * 
         * Cria um xml baseado no mapa das informacoes do json
         * 
         * @return XML gerado
         */
        function criarArray_xml()
        {
            // Percorre o mapa, linha a linha
            foreach($this->mapa as $linha => $mapasDaLinha)
            {
                // Percorre todos os paths da linha
                foreach($mapasDaLinha as $ndado => $mapaDaLinha)
                {
                    // Verifica se nao eh zero (linha) e se nao esta na lista de ignorados
                    if (($ndado != 0) && (!in_array($linha, $this->linhasIgnoradas)))
                    {
                        // add o path no array xml
                        $this->addOnArray_xml($mapaDaLinha);
                    }
                }
            }
            
            // instancia da classe do conversor
            $xml = new ArrayToXML();
            
            // Converte o array para xml
            $xml = $xml->buildXML($this->array_xml);
            
            // retorna o xml
            return $xml;
        }
        
        
        
        /**
         * addOnArray_xml
         * 
         * pega um path de um item e insere ele no array de estrutura
         *
         * @param string $mapa path do item
         * @return void
         */
        function addOnArray_xml($mapa)
        {
            $atual      = &$this->array_xml  ; // Pega uma referencia do array de estrutura 
            $paths      = explode('/',$mapa) ; // quebra o path para trabalhar com cada caminho separado
            $totalIndex = count($paths) -1   ; // faz a contagem de niveis
            
            // Percorre os caminhos ateh o dado
            foreach ($paths as $index => $path)
            {
                // o primeiro e o ultimo path podem ser vazios
                if ($path !== '')
                {
                    // Se o caminho  ainda o nao existir
                    if (!isset($atual[$path]))
                    {
                        // Cria o nivel no array xml
                        $atual[$path] = array();
                    }
                    
                    // se for ultimo nivel
                    if ($totalIndex == $index)
                    {
                        // cria uma entrada vazia no array xml
                        $atual[$path] = "";
                    }
                    
                    // Torna o atual o item recem criado, para avancar o nivel dos path no foreath 
                    $atual = &$atual[$path];
                }
            }
        }
        
        
        
        
        
        /**
         * Funcoes migradas do libs/ConvertMDFePHP.class.php
         */
        
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
            $aFind = array( '&', 'Ã¡', 'Ã ', 'Ã£', 'Ã¢', 'Ã©', 'Ãª', 'Ã­', 'Ã³', 
                            'Ã´', 'Ãµ', 'Ãº', 'Ã¼', 'Ã§', 'Ã', 'Ã', 'Ã', 'Ã', 
                            'Ã', 'Ã', 'Ã', 'Ã', 'Ã', 'Ã', 'Ã', 'Ã', 'Ã')
            ;
            
            $aSubs = array( 'e', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 
                            'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A',
                            'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C')
            ;
            
            $novoTexto = str_replace($aFind, $aSubs, $texto);
            $novoTexto = preg_replace("/[^a-zA-Z0-9 \\n|@,-.;:\/_]/", "", $novoTexto);
            /*
            // Remove os espacos vazios
            while(strpos($novoTexto, "  ") !== false){
                $novoTexto = str_replace($novoTexto, $F, $R);
            }
            */
            return $novoTexto;
        } //fim limpaString
        
        /**
         * calculaDV
         * FunÃ§Ã£o para o calculo o digito verificador da chave da MDFe
         * 
         * @name calculaDV
         * @param string $chave43
         * @return string 
         */
        public function calculaDV($chave43)
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
         * Liga o modo debugger.
         *
         * @param boolean $debugger
         * @return void
         */
        function setDebugger($debugger=true)
        {
            $this->debugger = $debugger;
        }
        
        
        function setChave($chave){
            $this->chave = ($chave == "-1") ? "" : $chave ;
        }
        
        
    }
    
    
    
    
    
    
    
    
    
    /**
     * MDFe
     */
    class class_txt2xml_MANIFESTO extends class_txt2xml
    {
        function __construct(){
            parent::__construct();
            $this->linhasIgnoradas = array("MANIFESTO", "A", "F") ;
            $this->sempreRepetir   = array("C", 'G') ;
            
            $this->escopoIni = '<MDFe xmlns="http://www.portalfiscal.inf.br/mdfe">';
            $this->escopoFim = '</MDFe>';
        }
        
        /**
         * montaChaveXML
         * Monta a chave da MDFe de 44 digitos com base em seus dados
         * Isso Ã© Ãºtil no caso da chave formada no txt estar errada
         * 
         * @name montaChaveXML
         * @param object $dom 
         */
        function montaChaveXML($dom,$versao='3.00')
        {
            
            $ide = $dom->getElementsByTagName("ide")->item(0);
            $emit = $dom->getElementsByTagName("emit")->item(0);
            $cUF = $ide->getElementsByTagName('cUF')->item(0)->nodeValue;
            $dEmi = $ide->getElementsByTagName('dhEmi')->item(0)->nodeValue;
            $CNPJ = $emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
            // $CNPJ = $emit->getElementsByTagName('CNPJCPF')->item(0)->nodeValue;
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
            $infMDFe->setAttribute("versao", $versao);
            
            $infMDFe = $dom->getElementsByTagName("infModal")->item(0);
            $infMDFe->setAttribute("versaoModal", $versao);
            
        } //fim calculaChave
        
    }
    
    
    /**
     * NFe
     */
    // class class_txt2xml_NOTAFISCAL extends class_txt2xml
    class class_txt2xml_NOTAFISCAL extends class_txt2xml
    {
        function __construct(){
            parent::__construct();
            $this->linhasIgnoradas = array("NOTAFISCAL", "A", "@"/* , "ZX02" */) ;
            $this->sempreRepetir   = array('Y', 'Z') ;
            // $this->linhasIgnoradas = array("NOTAFISCAL", "A", "@") ;
            
            $this->escopoIni = '<NFe xmlns="http://www.portalfiscal.inf.br/nfe">';
            $this->escopoFim = '</NFe>';
        }
        
        
        /**
         * montaChaveXML
         * Monta a chave da NFe de 44 digitos com base em seus dados
         * Isso Ã© Ãºtil no caso da chave formada no txt estar errada
         * 
         * @name montaChaveXML
         * @param object $dom 
         */
        function montaChaveXML($dom)
        {
            $ide            = $dom  ->getElementsByTagName("ide"    )->item(0)            ; 
            $emit           = $dom  ->getElementsByTagName("emit"   )->item(0)            ; 
            $cUF            = $ide  ->getElementsByTagName('cUF'    )->item(0)->nodeValue ; 
            $dhEmi          = $ide  ->getElementsByTagName('dhEmi'  )->item(0)->nodeValue ; 
            $cnpj           = $emit ->getElementsByTagName('CNPJ'   )->item(0)->nodeValue ; 
            $mod            = $ide  ->getElementsByTagName('mod'    )->item(0)->nodeValue ; 
            $serie          = $ide  ->getElementsByTagName('serie'  )->item(0)->nodeValue ; 
            $nNF            = $ide  ->getElementsByTagName('nNF'    )->item(0)->nodeValue ; 
            $tpEmis         = $ide  ->getElementsByTagName('tpEmis' )->item(0)->nodeValue ; 
            $cNF            = $ide  ->getElementsByTagName('cNF'    )->item(0)->nodeValue ; 
            $cDV            = $ide  ->getElementsByTagName('cDV'    )->item(0)->nodeValue ; 
            
            // echo "\n\n$dhEmi\n\n"; exit();
            
            
            // $tempData       = $dt   = explode("-", $dhEmi);
            // $forma          = "%02d%02d%02d%s%02d%03d%09d%01d%08d";
            // $chaveMontada   = sprintf($forma, $cUF, $tempData[0] - 2000, $tempData[1], $cnpj, $mod, $serie, $nNF, $tpEmis, $cNF);
            // $chaveMontada   .= $this->calculaDV($chaveMontada);
            
            // ## esse if nao tem no mdfe, avaliar se ele eh nescessario, chamar fdebug
            // ## debugar para ver de inde vem a this->chave 
            //caso a chave contida na NFe esteja errada, remontar a chave
            // echo ">{$this->chave}<";
            // if ($this->chave == "") {
                if (strlen($cNF) != 8) {
                    $cNF = $ide->getElementsByTagName('cNF')->item(0)->nodeValue = rand(10000001, 99999999);
                }
                $tempData = explode("-", $dhEmi);
                $forma = "%02d%02d%02d%s%02d%03d%09d%01d%08d";
                $tempChave = sprintf($forma, $cUF, $tempData[0] - 2000, $tempData[1], $cnpj, $mod, $serie, $nNF, $tpEmis, $cNF);
                $cDV = $ide->getElementsByTagName('cDV')->item(0)->nodeValue = $this->calculaDV($tempChave);
                $this->chave = $tempChave .= $cDV;
            // }
            // echo ">{$this->chave}<";
            
            // exit();
            // 41180813505431000151650010000011309162700769
            // 41180813505431000151650010000011309162700769
            $infNFe = $dom->getElementsByTagName("infNFe")->item(0);
            $infNFe->setAttribute("versao", $this->versao);
            $infNFe->setAttribute("Id", "NFe" . $this->chave);
            
        } //fim calculaChave
        
        
    }
    
    class class_txt2xml_REINF extends class_txt2xml
    {
        
        function __construct(){
            parent::__construct();
            $this->linhasIgnoradas = array("REINF", "A") ;
            $this->sempreRepetir   = array("B", "C", "D", "H", "J", "O" ) ;
            
            $this->escopoIni = '';
            $this->escopoFim = '';
        }

        function montaChaveXML($dom){
            
        }
        
            
    }
    
    
//     /**
//      * original on: https://github.com/jmarceli/array2xml
//      * Based on: http://stackoverflow.com/questions/99350/passing-php-associative-arrays-to-and-from-xml
//      */
//     class ArrayToXML
//     {
//         private $version;
//         private $encoding;
//         /**
//          * Construct ArrayToXML object with selected version and encoding
//          *
//          * for available values check XmlWriter docs http://www.php.net/manual/en/function.xmlwriter-start-document.php
//          * @param string $xmlVersion XML Version, default 1.0
//          * @param string $xmlEncoding XML Encoding, default UTF-8
//          */
//         public function __construct($xmlVersion = '1.0', $xmlEncoding = 'UTF-8')
//         {
//             $this->version = $xmlVersion;
//             $this->encoding = $xmlEncoding;
//         }
//         /**
//          * Build an XML Data Set
//          *
//          * @param array $data Associative Array containing values to be parsed into an XML Data Set(s)
//          * @param string $startElement Root Opening Tag, default data
//          * @return string XML String containing values
//          * @return mixed Boolean false on failure, string XML result on success
//          */
//         public function buildXML($data, $startElement = '')
//         {
//             if (!is_array($data)) {
//                 $err = 'Invalid variable type supplied, expected array not found on line ' . __LINE__ . ' in Class: ' . __CLASS__ . ' Method: ' . __METHOD__;
//                 trigger_error($err);
//                 return false; //return false error occurred
//             }
//             $xml = new XmlWriter();
//             $xml->openMemory();
//             $xml->startDocument($this->version, $this->encoding);
//             // $xml->startElement($startElement);
//             $data = $this->writeAttr($xml, $data);
//             $this->writeEl($xml, $data);
//             $xml->endElement(); //write end element
//             //returns the XML results
//             return $xml->outputMemory(true);
//         }
//         /**
//          * Write keys in $data prefixed with @ as XML attributes, if $data is an array.
//          * When an @ prefixed key is found, a '%' key is expected to indicate the element itself,
//          * and '#' prefixed key indicates CDATA content
//          *
//          * @param XMLWriter $xml object
//          * @param array $data with attributes filtered out
//          * @return array $data | $nonAttributes
//          */
//         protected function writeAttr(XMLWriter $xml, $data)
//         {
//             if (is_array($data)) {
//                 $nonAttributes = array();
//                 foreach ($data as $key => $val) {
//                     //handle an attribute with elements
//                     if ($key[0] == '@') {
//                         $xml->writeAttribute(substr($key, 1), $val);
//                     } else if ($key[0] == '%') {
//                         if (is_array($val)) $nonAttributes = $val;
//                         else $xml->text($val);
//                     } elseif ($key[0] == '#') {
//                         if (is_array($val)) $nonAttributes = $val;
//                         else {
//                             $xml->startElement(substr($key, 1));
//                             $xml->writeCData($val);
//                             $xml->endElement();
//                         }
//                     }else if($key[0] == "!"){
//                         if (is_array($val)) $nonAttributes = $val;
//                         else $xml->writeCData($val);
//                     } 
//                     //ignore normal elements
//                     else $nonAttributes[$key] = $val;
//                 }
//                 return $nonAttributes;
//             } else return $data;
//         }
//         /**
//          * Write XML as per Associative Array
//          *
//          * @param XMLWriter $xml object
//          * @param array $data Associative Data Array
//          */
//         protected function writeEl(XMLWriter $xml, $data)
//         {
//             foreach ($data as $key => $value) {
//                 if (is_array($value) && !$this->isAssoc($value)) { //numeric array
//                     foreach ($value as $itemValue) {
//                         if (is_array($itemValue)) {
//                             $xml->startElement($key);
//                             $itemValue = $this->writeAttr($xml, $itemValue);
//                             $this->writeEl($xml, $itemValue);
//                             $xml->endElement();
//                         } else {
//                             $itemValue = $this->writeAttr($xml, $itemValue);
//                             $xml->writeElement($key, "$itemValue");
//                         }
//                     }
//                 } else if (is_array($value)) { //associative array
//                     $xml->startElement($key);
//                     $value = $this->writeAttr($xml, $value);
//                     $this->writeEl($xml, $value);
//                     $xml->endElement();
//                 } else { //scalar
//                     $value = $this->writeAttr($xml, $value);
//                     $xml->writeElement($key, "$value");
//                 }
//             }
//         }
//         /**
//          * Check if array is associative with string based keys
//          * FROM: http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-if-an-array-is-associative-or-sequential/4254008#4254008
//          *
//          * @param array $array Array to check
//          * @return bool
//          */
//         protected function isAssoc($array)
//         {
//             return (bool)count(array_filter(array_keys($array), 'is_string'));
//         }
//     }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    