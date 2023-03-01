<?php
    /**
     *                                                                            
     * Percorre os eventos rodando uma funcao especifica para cada evento         
     *                                                                            
     * $XML= f_cortar(                      // f_cortar retorna um XML            
     *            $eventos=array(           // array de eventos                   
     *                "evento1",            // array nao mapeado                  
     *                "evento2",            // array de strings                   
     *            ),                        //                                    
     *            $container='eventos' ,    // tag em que os eventos se encontram 
     *            $xml = 'XML',             // xml integral                       
     *            callback                  // funcao para rodar em cada evento   
     *       )                                                                    
     *  ;                                                                         
     *                                                                            
     */
    
    
    
    /**
     * Function f_cortar
     * Busca todos os eventos no XML 
     * e roda uma funcao de callback nele
     *
     * @param  array   $eventos     array de eventos
     * @param  string  $container   container dos eventos no xml
     * @param  string  $xml         xml integral
     * @param  string  $callback    funcao de callback
     * @return string
     */
    function f_cortar($eventos=array(), $container='', $xml='', $callback='')
    {
        $xml         = f_cortar_prepare($xml);
        $xmlExploded = f_cortar_cortar($eventos, $container, $xml);
        $xml         = f_cortar_callback($xmlExploded, $callback);
        
        return $xml;
    }
    
    
    
    /**
     * function f_cortar_prepare
     * remove todos as quebras de linha e limpa todos os espacos 
     *
     * @param string $xml xnl integral 
     * @return $xml xnl integral 
     */
    function f_cortar_prepare($xml='')
    {
        // limpa o xml
        $xml = str_replace( 
            array("\n", "\t", "\r") , // remove os espacos e quabras de linha
            array(""  , ""  , ""  ) , // troca por nada
            $xml
        ) ; 
        
        // remove os espacos sobrando
        $xml = preg_replace("/\s+/", " ", $xml);
        
        return $xml;
    }
    
    
    
    /**
     * function f_cortar_cortar
     * Busca os eventos e corta o xml neles
     *
     * @param array  $eventos
     * @param string $container
     * @param string $xml
     * @return void
     */
    function f_cortar_cortar($eventos=array(), $container='', $xml='')
    {
        // percorre os eventos passados
        foreach($eventos as $evento)
        {
            // quando encontra um evento no XML
            if (strpos($xml, "<$evento ") !== false)
            {
                // faz uma quebra de linha para poder trabalhar separado depos
                $xml = str_replace( 
                    array(  "<$evento "), 
                    array("\n<$evento "),
                    $xml
                );
            }
        }
        
        // quebra no container para nao causar erro
        $xml = str_replace( 
            array(  "</$container>"), 
            array("\n</$container>"),
            $xml
        );
        
        // quebra em um array todos os eventos
        $xmlExploded = explode("\n", $xml);
        
        return $xmlExploded;
    }
    
    
    
    /**
     * function f_cortar_callback
     * percorre os eventos e roda uma funcao neles
     *
     * @param array  $xmlExploded
     * @param string $callback
     * @return void
     */
    function f_cortar_callback($xmlExploded=array(), $callback='')
    {
        // primeira e ultima linha sao do cabecaolho, pula elas
        $first = 0                      ; // 
        $last  = count($xmlExploded)-1  ; // 
        
        // percorre os eventos
        foreach($xmlExploded as $index => $eventoXml)
        {
            // pula a primeira e ultima linha do arquivo
            if (($index != $first) && ($index !== $last))
            {
                // pega o nome do evento
                $eventoNome  = preg_split('/\s/', $eventoXml, 0);
                $eventoNome  = str_replace('<', '', $eventoNome[0]);
                
                // 
                $partes = array();
                preg_match("\"[A-z]+[0-9]+\"", $eventoXml, $partes) ; 
                $id = $partes[0];
                
                // roda a funcao
                $xmlExploded[$index] = $callback($eventoNome, $eventoXml, $id);
            }
        }
        
        // remonta o XML e retorna
        $xml = implode("", $xmlExploded);
        
        return $xml;
    }
    
    
    