<?php
    
    function chaveDecode($chave)
    {
        $ufs = array("11"=>"RO", "12"=>"AC", "13"=>"AM", "14"=>"RR", "15"=>"PA", "16"=>"AP", "17"=>"TO", "21"=>"MA", "22"=>"PI", "23"=>"CE", "24"=>"RN", "25"=>"PB", "26"=>"PE", "27"=>"AL", "28"=>"SE", "29"=>"BA", "31"=>"MG", "32"=>"ES", "33"=>"RJ", "35"=>"SP", "41"=>"PR", "42"=>"SC", "43"=>"RS", "50"=>"MS", "51"=>"MT", "52"=>"GO", "53"=>"DF");
        
        /*
            ex        : 51080701212344000127550010000000981364112281 
            Resultado : 51 * 0807 * 01212344000127 * 55 * 001 * 000000098 * 136411228 * 1 
            
            ._______________________________________________________________________________.
            | Posicoes | Parte do Codigo |  Significado                                     | 
            |----------|-----------------|--------------------------------------------------|
            |    02    |              51 |  Codigo do Estado (UF) do emitente               |                                                                     | 
            |    04    |            0807 |  Ano e mes da emissao da NF-e (no formato AAMM)  |                                                                                 | 
            |    14    |  01212344000127 |  CNPJ do emitente da NF-e (CNPJ da sua Empresa)  |                                                                                 | 
            |    02    |              55 |  Modelo do NF-e                                  |                                                 | 
            |    03    |             001 |  Série do NF-e                                   |                                                 | 
            |    09    |       000000098 |  Numero da NF-e                                  |                                                 | 
            |    09    |       136411778 |  Codigo da NF-e - Numero gerado pelo sistema     |                                                                             | 
            |    01    |               1 |  Digito verificador - DV (Calculo no modulo 11). |                                                                                 | 
            '-------------------------------------------------------------------------------'
        */
        
        $dados=array();
        
        $dados[    'uf'] = substr($chave,  0,  2 ) ; 
        $dados['anomes'] = substr($chave,  2,  4 ) ; 
        $dados[  'cnpj'] = substr($chave,  6, 14 ) ; 
        $dados['modelo'] = substr($chave, 20,  2 ) ; 
        $dados[ 'serie'] = substr($chave, 22,  3 ) ; 
        $dados['numero'] = substr($chave, 25,  9 ) ; 
        $dados['codigo'] = substr($chave, 34,  9 ) ; 
        $dados[    'dv'] = substr($chave, 43,  1 ) ; 
        
        $dados['extra'] = array(
            'ufname'=> $ufs[$dados['uf']],
        );
        
        return $dados;
    }
    
    
    
