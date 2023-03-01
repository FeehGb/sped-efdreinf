'use-strict';
    
    // if (process.argv.length <= 2) {
        console.log(`
            ####################################################################
            ## Consulta o status de uma nota fiscal atraves de sua chave;     ##
            ##                                                                ##
            ##   ENTRADA:                                                     ##
            ##       argv[1] = chave_nfe                                      ##
            ##       argv[2] = cnpj                                           ##
            ##       argv[3] = saida                                          ##
            ##       argv[4]*= ambiente_entrada | 2 ;                         ##
            ##                                                                ##
            ##   SAIDA:                                                       ##
            ##       WS-SEFAZ-RET-CNPJ        //                              ##
            ##       WS-SEFAZ-RET-UF-IBGE     //                              ##
            ##       WS-SEFAZ-RET-TP-EMISSAO  //                              ##
            ##       WS-SEFAZ-RET-AMBIENTE    //                              ##
            ##       WS-SEFAZ-RET-ID-NFE      //                              ##
            ##       WS-SEFAZ-RET-STATUS      //                              ##
            ##       WS-SEFAZ-RET-DESC-STATUS //                              ##
            ##       WS-SEFAZ-RET-PROTOCOLO   //                              ##
            ##       WS-SEFAZ-RET-DT-HORA     //                              ##
            ##       WS-SEFAZ-RET-PROTO-CAN   //                              ##
            ##       WS-SEFAZ-RET-DT-HORA-CAN //                              ##
            ##       WS-SEFAZ-RET-PROTO-EVE   //                              ##
            ##       WS-SEFAZ-RET-TIPO-EVE    //                              ##
            ##       WS-SEFAZ-RET-DT-HORA-EVE //                              ##
            ##       WS-SEFAZ-RET-XML         //                              ##
            ##                                                                ##
            ####################################################################
        `);
        // return ; 
    // }
    
    
    fs = require('fs');
    comunicador = require('../class/class.comunicador.js');
    let urls = JSON.parse(fs.readFileSync(`../../servicos/nfe/urls/urls.json`, 'utf8'));
    
    
    // No caso da consulta de nota, temos que buscar o estado em que a nota foi emitida, para usar o webservice correto
    
    cnpj = '01956679000150'; 
    // chave_nfe = '52180206142539000161550010001447721006613991'; // Unhandled rejection TypeError [ERR_INVALID_PROTOCOL]: Protocol "http:" not supported. Expected "https:"
    // chave_nfe = '43180892664028002608550010019937041139049875'; // OK php
    // chave_nfe = '42170901066048000165550100000577391636405376'; // OK php
    // chave_nfe = '41180895433215000102550010000236331003437168'; // OK
    // chave_nfe = '35180891448977000474550030000753501754222962'; // ok php
    // chave_nfe = '33171033352881000169550010001287111002052265'; // OK PHP
    chave_nfe = '31170919636984000120550010000048741000029085'; // Unhandled rejection Error: soap:Server: Fault occurred while processing.
    // chave_nfe = '29180706043069000189550010001303591104250040'; // OK PHP
    // chave_nfe = '26180316701716003686550250004310771149854537'; // ok 
    // chave_nfe = '23180202500041000172550010000309581000279826'; // Unhandled rejection TypeError [ERR_INVALID_PROTOCOL]: Protocol "http:" not supported. Expected "https:"
    // cnpj = "82295817000107";
    // chave_nfe = "29171176635689001245550010002743011017822066"; // OK PHP
    // chave_nfe = "31171016509700000146550010000159951000159958"; // 400 
    // chave_nfe = "32180805570714000825550010038568431107279150"; // 
    // chave_nfe = "33180813059737000121550010000093451000046966"; // 
    // chave_nfe = "35170900614324000110550040000050321938634151"; // 
    // chave_nfe = "41180885014793000150550110003043071081650230"; // 
    // chave_nfe = "42170983109579000152550010003575761002716630"; // 
    // chave_nfe = "43180842150391003781550010009700511762226444"; // 
    // chave_nfe = "52180720532709000145550010000116281566903616"; // 
    
    // chave_nfe = '15170901066048000165550100000577391636405376'; // estado
    
    // let cnpj      = '01956679000150'; 
    let saida     = 'debug'; 
    let ambiente  = '1' ; 
    let nmEstado  = urls['number_uf'][chave_nfe.slice(0, 2)];
    
    // let chave_nfe = process.argv[2] ; 
    // let cnpj      = process.argv[3] ; 
    // let saida     = process.argv[4] || 'debug'; 
    // let ambiente  = process.argv[5] ||    '2' ; 
    // let nmEstado  = urls['number_uf'][chave_nfe.slice(0, 2)];
    
    // console.log (typeof(process.argv[4]));
    console.log([cnpj, nmEstado, ambiente, 'consulta_nota', saida]);
    let cunsultanota  = new comunicador(cnpj, nmEstado, ambiente, 'consulta_nota', dados={
        "ALTERAR_TIPO_AMBIENTE" :  ambiente , 
        "ALTERAR_CHAVE_NFE"     : chave_nfe , 
    });
    
    cunsultanota.sucess = (retorno) => {
        
        return console.log(retorno);
        let retorno_obj  = retorno[0]['retConsSitNFe'];
        let xml_retorno  = retorno[1]  ;
        let retornoCobol = new Array() ;
        
        //*
        retornoCobol.push(                    cnpj ) // WS-SEFAZ-RET-CNPJ        // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-UF-IBGE     // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-TP-EMISSAO  // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["tpAmb"   ] ) // WS-SEFAZ-RET-AMBIENTE    // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["chNFe"   ] ) // WS-SEFAZ-RET-ID-NFE      // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["cStat"   ] ) // WS-SEFAZ-RET-STATUS      // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["xMotivo" ] ) // WS-SEFAZ-RET-DESC-STATUS // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["nProt"   ] ) // WS-SEFAZ-RET-PROTOCOLO   // 
        retornoCobol.push( retorno_obj['protNFe']['infProt']["dhRecbto"] ) // WS-SEFAZ-RET-DT-HORA     // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-PROTO-CAN   // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-DT-HORA-CAN // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-PROTO-EVE   // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-TIPO-EVE    // 
        retornoCobol.push(                     " " ) // WS-SEFAZ-RET-DT-HORA-EVE // 
        retornoCobol.push(             xml_retorno ) // WS-SEFAZ-RET-XML         // 
        
        retornoCobol = retornoCobol.join('|');
        
        if (saida == 'debug'){
            console.log(retornoCobol);
            // console.log(retorno);
        } else {
            let path='./testes/nodeSaida.txt';
            fs.writeFile(path, retornoCobol, (err)=>{});
        }
    }
    
    cunsultanota.comunicar();
    
    