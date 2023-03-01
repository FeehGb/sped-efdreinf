<?php
    
    /**
     * Autor: Luiz Zulian
     * Data: 11/10/2017
     * 
     * Ferramenta para a criacao de xmls
     * utiliza a funcao txt2xml
     * tendo cmo entrada um txt gerado pelo cobol
     * a saida sera um xml no diretorio passado
     * 
     * @param [String] $argv[1] Caminho do txt gerado pelo cobol
     * @param [String] $argv[2] Caminho do xml gerado
     * 
     * @return void xml criado no diretorio do arg2
     * 
     * TESTE
     * php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml
     * 
     */
    
    /*
    
    start_time=`date +%s` &  php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml > ~/errosConversor.txt && echo run time is $(expr `date +%s` - $start_time) s
    
    //place this before any script you want to calculate time
    
    
    //sample script
    for($i=0; $i<1000; $i++){
    //do anything
    }
    
    
    //dividing with 60 will give the execution time in minutes other wise seconds
    
    //execution time of the script
    
date +%s ; 
php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml & php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml ; date +%s


php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &php create_XML_by_txt.php /var/www/html/nf/NF_V3/testes_nfe/nfe_txt.txt /var/www/html/nf/NF_V3/testes_nfe/TESTE_nfe_txt.xml &
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    */
    
    
    error_reporting(E_ALL);
    
    // require_once('../funcoes/txt2xml.php');
    require_once('../funcoes/txt2xml-bkp2.php');
    
    $time_start = microtime(true); 
    
    for ($i=1; $i<=$argv[3];$i++){
        // print_r("->$i\n");
        txt2xml($caminho_txt=$argv[1], $caminho_xml=$argv[2], $tipo='NOTAFISCAL', $debugger=true);
    }
    
    
    
    
    $duration = (microtime(true) - $time_start);
    /*
    
    $mmseconds = (int) ( $duration % 1000);
    $seconds   = (int) ( $duration ) % 60 ;
    $minutes   = (int) (($duration / (60)) % 60);
    $hours     = (int) (($duration / (60*60)) % 24);
    */
    
    
    
    echo "$duration\n";
    
    // echo "Execution Time $hours:$minutes:$seconds:$mmseconds\n";
    // echo 'Total execution time: ' . date("h:i:s.u", (microtime(true) - $time_start) / 1000);
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    