"use strict";

const soap = require('soap') ; 
const fs   = require(  'fs') ; 
const util = require('util') ; 
const ini  = require( 'ini') ; 

class comunicador
{
    constructor(cnpj, state, tpAmb, service, params={})
    {
        this.cnpj          =    cnpj ; 
        this.state         =   state ; 
        this.tpAmb         =   tpAmb ; 
        this.service       = service ; 
        this.params        =  params ; 
        this.xml           =      '' ; 
        this.jsonstate     =      {} ; 
        this.configCliente =      {} ; 
        
        this.loadClient();
        this.loadState();
        
        this.mountXML();
        // this.comunicar();
    }
    
    loadState()
    {
        let nmAmb = this.tpAmb==1 ? 'producao' : 'homologacao';
        this.jsonstate = this.loadJson(`../../servicos/nfe/webservices/${this.state}/${nmAmb}.json`);
    }
    
    loadClient()
    {
        this.configCliente = this.loadIni(`../../../NF_V3_dados/config_cliente/${this.cnpj}.ini`);
    }
    
    
    mountXML()            
    {
        this.xml = fs.readFileSync('../../servicos/template_soap.xml').toString();
        
        // this.xml = `<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfec="http://www.portalfiscal.inf.br/nfe/wsdl/NFeConsultaProtocolo4"><soapenv:Header/><soapenv:Body><nfec:nfeDadosMsg>ALTERAR_TAG_CORPO</nfec:nfeDadosMsg></soapenv:Body></soapenv:Envelope>`
        
        
        // <soap12:Header></soap12:Header>
        let ALTERAR_TAG_CABECALHO = this.jsonstate["4.00"][this.service]['tag_cabecalho' ] ; 
        let ALTERAR_TAG_CORPO     = this.jsonstate["4.00"][this.service]['tag_corpo'     ] ; 
        this.xml = this.jsonstate["4.00"][this.service]['tag_corpo'     ] ; 
        
        this.xml = this.xml.replace('ALTERAR_TAG_CABECALHO', ALTERAR_TAG_CABECALHO) ; 
        this.xml = this.xml.replace('ALTERAR_TAG_CORPO'    , ALTERAR_TAG_CORPO    ) ; 
        this.xml =  ALTERAR_TAG_CORPO    ; 
        
        for (let param in this.params) {
            this.xml = this.xml.replace(param, this.params[param]);
        }
    }
    
    getheader(){
        let header = {} ; 
        header = {
            'Content-Type':'application/xml',
            'SOAPAction'  :`${this.jsonstate["4.00"][this.service]['namespace']}`,
        };
        return header;
    }
    
    
    /**
     * Comunicador
     * 
     * Dexscricao maior
     * 
     * @param [int] x inteiro
     */
    comunicar()
    {
        let retorno = '';
        
        let urlWsdl = this.jsonstate["4.00"][this.service]['url'];
        
        if (urlWsdl.indexOf('?wsdl') === -1){
            urlWsdl = `${urlWsdl}?wsdl`;
        }
        
        let Pcert   = `${this.configCliente['certificado']['raiz_certificado']}_pubKey.pem` ; 
        let Pkey    = `${this.configCliente['certificado']['raiz_certificado']}_priKey.pem` ; 
        let Fcert   = fs.readFileSync(Pcert) ; 
        let Fkey    = fs.readFileSync(Pkey)  ; 
        let methodo = this.jsonstate["4.00"][this.service]['metodo'];
        
        // para aceitar a cadeia de certificados
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
        
        
        
        
        // soap.createClient(urlWsdl, {
        soap.createClientAsync(urlWsdl, {
            // forceSoap12Headers: true,
            // envelopeKey:'',
            // escapeXML:true,
            // wsdl_headers:this.getheader(),
            disableCache:true,
            wsdl_options : {
                cert : Fcert , 
                key  :  Fkey , 
            }
        }).then((client) => {
            client.setSecurity(new soap.ClientSSLSecurity(Fkey, Fcert,{}));
            // client.addHttpHeader('Content-Type', 'text/xml');
            // client.addHttpHeader('SOAPAction'  , `${this.jsonstate["4.00"][this.service]['namespace']}`);
            // this.xml = this.xml.split('"').join("'");
            console.log(this.xml);
            console.log(methodo);
            console.log(client);
            
            return client[`${methodo}Async`](this.xml);
        }).then((result) => {   
            console.log(result);
            
            if (typeof(this.sucess) == 'function'){
                this.sucess(result);
            } 
        }).catch(err=>{
            console.log(err);
            // console.log(util.inspect(err, false, null, true /* enable colors */))
        });
        
        // return retorno;
    }
    
    
    
    
    retorno(resultado)
    {
        console.log(util.inspect(resultado, false, null, true))
    }
    
    
    loadJson(file){
        return JSON.parse(
            fs.readFileSync(file, 'utf8')
        );
    }
    
    loadIni(file){
        return ini.parse(
            fs.readFileSync(file, 'utf8')
        );
    }
    
}



module.exports = comunicador;


