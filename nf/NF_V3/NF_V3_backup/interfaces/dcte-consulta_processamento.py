#!/usr/local/bin/python
# -*- coding: utf-8 -*-

import sys, re, gzip, base64, json.decoder
from collections import deque
from datetime import datetime

def unzip(zip):
    xml = str(gzip.decompress(base64.b64decode(zip)))
    retorno = findFirst(r'^b\'(.*)\'', xml)
    return retorno

def getXpath(xpath, xml):
    xpath     = makeXpath (xpath)
    return findFirst(xpath, xml)

def findFirst(regex, text):
    encontrou = re.findall(regex, text)
    return encontrou[0] if len(encontrou) > 0 else " " 
    

def makeXpath(xpath):
    xpath = xpath.replace('/@', '@')
    xpath = makeXpath_recursive( deque( xpath.split('/') ) )
    return xpath

def makeXpath_recursive(arrXpath):
    if len(arrXpath) > 1:
        tag = arrXpath.popleft()
        #                                                           
        #          ,-----------------------------------------------> Abertura da tag
        #          |    ,------------------------------------------> Seguido de qualquer coisa diferente do fechamento e e depois o fechamento
        #          |    |      ,-----------------------------------> Seguido de qualquer coisa, literal ou nao, usado para pegar qualquer tag com qualquer valor, ateh ateh a que queremos
        #          |    |      |         ,-------------------------> Tag interna pode ser essa regex atual repetida ou a regex que pega o valor desejado
        #          |    |      |         |         ,---------------> Seguido de qualquer coisa, literal ou nao
        #          |    |      |         |         |       ,-------> Fechamento da tag
        #         _|_  _|__  __|__  _____|______  _|__   __|____     
        #        /   \/    \/     \/            \/     \/       \    
        return "<{tag}[^>]*>[\w\W]*{trag_interna}[\w\W]*<\/{tag}>".format( tag=tag, trag_interna=makeXpath_recursive(arrXpath) )
        
    else:
        # se for atributo
        if '@' in arrXpath[0]:
            tag, attr = arrXpath[0].split('@')
            #                                                                       
            #         .------------------------------------------------------------> Tag que iremos buscar o atributo
            #         |     .------------------------------------------------------> Qualquer coisa diferente da tag de abertura, usado para pegar todos os atributos ateh achar o que queremos
            #         |     |      .-----------------------------------------------> Atrubuto desejado no formato atributo="valor"
            #         |     |      |        .--------------------------------------> Valor do atributo, eh o grupo capiturado
            #         |     |      |        |      .-------------------------------> Qualquer coisa ath fechar a tag
            #         |     |      |        |      |      .------------------------> Todo o conteudo da tag
            #         |     |      |        |      |      |   .--------------------> Grupo nao capiturado iniciado para poder buscar por duas coisas
            #         |     |      |        |      |      |   |     .--------------> 1: o fechamento da tag com nome que abriu
            #         |     |      |        |      |      |   |     |        .------> 2: ou qualquer coisa, literal ou nao, para o caso dela ser uma shorttag, sem fechamento
            #        _|__  _|_  ___|____  __|__  __|___  _|_  |  ___|___   __|__ .--> Fechamento do grupo nao capiturado
            #       /    \/   \/        \/     \/      \/   \/ \/        \/     \/\
            return "<{tag}[^<]*{attr}\=\"([^\"]*)\"[^>]*>[^<]*".format(tag=tag, attr=attr)
            
            
        # se nao for atributo
        else:
            #                                           
            #             .----------------------------> Abertura da tag buscada
            #             |        .-------------------> Grupo capiturado do valor da tag, pega tudo ateh a tag de abertura
            #             |        |         .---------> Fechamento da tag buscada
            #        _____|____  __|__  _____|______    
            #       /          \/     \/            \   
            return "<{tag}[^>]*>([^<]*)<\/{tag}[^>]*>".format(tag=arrXpath[0])
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

# se a chamada vem do php
if len(sys.argv) > 2:
    pythonName = sys.argv[0]
    cnpj       = sys.argv[1]
    estado     = sys.argv[2]
    xmlPath    = sys.argv[3]
    retornoCob = sys.argv[4]
    jsonConfig = sys.argv[5]
    

# se estiver rodando um teste
else:
    pythonName = './cte-consulta_processamento.py'
    cnpj       = '72071541000200'
    estado     = 'BR'
    xmlPath    = '/home/zulian/SERVIDOR/var/www/html/nf/NF_V3/NF_V3_dados/temp/cte/72071541000200-000000000000000-1565271762.xml'
    jsonConfig = '{"versao":"3.00","dir_retorno":"\/home\/zulian\/SERVIDOR\/var\/www\/html\/nf\/NF_V3\/NF_V3_dados\/temp\/cte\/","dir_procCTe":"\/home\/zulian\/SERVIDOR\/var\/www\/html\/nf\/NF_V3\/NF_V3_dados\/temp\/cte\/procCTe\/","dir_procEventoCTe":"\/home\/zulian\/SERVIDOR\/var\/www\/html\/nf\/NF_V3\/NF_V3_dados\/temp\/cte\/procEventoCTe\/"}'
    
# prepara a configuracao
jsonConfig = json.loads(jsonConfig)

# carregao o xml
with open(xmlPath) as f:
    xml = f.read()

retDistDFeInt = 'soap:Envelope/soap:Body/cteDistDFeInteresseResponse/cteDistDFeInteresseResult/retDistDFeInt'
header = []
header.append(   '000' )                                 # 05  WS-D-R-TIPO-REGISTRO       
header.append(    cnpj )                                 # 05  WS-D-R-CNPJ-DEST           
header.append(  estado )                                 # 05  WS-D-R-UF-IBGE             
header.append(getXpath(retDistDFeInt + '/tpAmb'  , xml)) # 05  WS-D-R-AMBIENTE            
header.append(getXpath(retDistDFeInt + '/cStat'  , xml)) # 05  WS-D-R-STATUS              
header.append(getXpath(retDistDFeInt + '/xMotivo', xml)) # 05  WS-D-R-MOTIVO              
header.append(getXpath(retDistDFeInt + '/dhResp' , xml)) # 05  WS-D-R-DATA-HORA-RESPOSTA  
header.append(getXpath(retDistDFeInt + '/ultNSU' , xml)) # 05  WS-D-R-ULTIMO-NSU          
header.append(getXpath(retDistDFeInt + '/maxNSU' , xml)) # 05  WS-D-R-MAXIMO-NSU          
header.append(' ')                                       
header = '|'.join(header)

#!        GAMBIARRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRRA
# cortado = xml.split('<loteDistDFeInt>')[1].split('</loteDistDFeInt>')[0]
# partes  = cortado.split('</docZip>')

xml    = xml.replace("</docZip><docZip", "</docZip>\n<docZip")
partes = xml.split('\n')

linhas = []
retorno = []
for parte in partes:
    
    if parte == "":
        continue
    
    schema     = getXpath('docZip/@schema' , parte)
    nsu        = getXpath('docZip/@NSU'    , parte)
    zipxml     = getXpath('docZip'         , parte)
    unzipedXML = unzip(zipxml)
    
    if schema == 'procCTe_v3.00.xsd':
        # chave = getXpath('cteProc/CTe/infCte/infCTeNorm/infDoc/infNFe/chave', unzipedXML)
        chave = getXpath('cteProc/protCTe/infProt/chCTe', unzipedXML)
        retorno = [
            '001'                                                                         , # 05  WS-D-R-123-TIPO-REGISTRO   PIC  9(003).
            chave                                                                         , # 05  WS-D-R-123-ID-NFE          PIC  X(044).
            nsu                                                                           , # 05  WS-D-R-123-NSU             PIC  9(015).
            getXpath('cteProc/CTe/infCte/emit/CNPJ'                          , unzipedXML), # 05  WS-D-R-12-CNPJ-EMIT        PIC  9(014).
            getXpath('cteProc/CTe/infCte/emit/xNome'                         , unzipedXML), # 05  WS-D-R-12-DESC-EMIT        PIC  X(060).
            getXpath('cteProc/CTe/infCte/emit/IE'                            , unzipedXML), # 05  WS-D-R-12-IE-EMIT          PIC  X(014).
            getXpath('cteProc/CTe/infCte/ide/dhEmi'                          , unzipedXML), # 05  WS-D-R-12-DATA-EMISSAO     PIC  9(008).
            getXpath('cteProc/CTe/infCte/ide/tpEmis'                         , unzipedXML), # 05  WS-D-R-12-TIPO-OPERACAO    PIC  9(001).
            getXpath('cteProc/CTe/infCte/vPrest/vTPrest'                     , unzipedXML), # 05  WS-D-R-12-VALOR-NFE        PIC  X(018).
            getXpath('cteProc/CTe/Signature/SignedInfo/Reference/DigestValue', unzipedXML), # 05  WS-D-R-12-IDENT-DCTO-SEFAZ PIC  X(028).
            getXpath('cteProc/protCTe/infProt/dhRecbto'                      , unzipedXML), # 05  WS-D-R-12-DATA-AUTORIZ     PIC  X(014).
            getXpath('cteProc/protCTe/infProt/nProt'                         , unzipedXML), # 05  WS-D-R-12-PROTOCOLO        PIC  X(015).
            getXpath('cteProc/protCTe/infProt/cStat'                         , unzipedXML), # 05  WS-D-R-12-SITUACAO-NFE     PIC  9(001).
            getXpath('cteProc/protCTe/infProt/xMotivo'                       , unzipedXML), # 05  WS-D-R-12-SITUACAO-NFE     PIC  9(001).
            getXpath('cteProc/CTe/infCte/ide/modal'                          , unzipedXML), # 05  WS-D-R-12-MODAL            PIC  9(002).
            getXpath('cteProc/CTe/infCte/ide/UFIni'                          , unzipedXML), # ??
            getXpath('cteProc/CTe/infCte/ide/UFFim'                          , unzipedXML), # ??
            ' '
        ]
        name = jsonConfig['dir_procCTe'] + chave + '-procCTe.xml'
        
        
    elif schema == 'procEventoCTe_v3.00.xsd':
        chave = getXpath('procEventoCTe/eventoCTe/infEvento/chCTe', unzipedXML)
        retorno = [
            '002'                                                                                              , # 05  WS-D-R-123-TIPO-REGISTRO   PIC  9(003). 
            chave                                                                                              , # 05  WS-D-R-123-ID-NFE          PIC  X(044). 
            nsu                                                                                                , # 05  WS-D-R-123-NSU             PIC  9(015). 
            getXpath('procEventoCTe/eventoCTe/infEvento/CNPJ'                                    , unzipedXML) , # 05  WS-D-R-12-CNPJ-EMIT        PIC  9(014). 
            getXpath('procEventoCTe/eventoCTe/infEvento/dhEvento'                                , unzipedXML) , # 05  WS-D-R-3-DATA-HORA-EVENTO  PIC  9(014). 
            getXpath('procEventoCTe/eventoCTe/infEvento/tpEvento'                                , unzipedXML) , # 05  WS-D-R-3-EVENTO            PIC  9(006). 
            getXpath('procEventoCTe/eventoCTe/infEvento/nSeqEvento'                              , unzipedXML) , # 05  WS-D-R-3-SEQ-EVENTO        PIC  9(002). 
            getXpath('procEventoCTe/eventoCTe/infEvento/detEvento/evCTeAutorizadoMDFe/descEvento', unzipedXML) , # 05  WS-D-R-3-DESC-EVENTO       PIC  X(060). 
            getXpath('procEventoCTe/eventoCTe/infEvento/detEvento/evCTeAutorizadoMDFe/MDFe/dhEmi', unzipedXML) , # 05  WS-D-R-3-DH-AUTORIZ        PIC  9(014). 
            getXpath('procEventoCTe/eventoCTe/infEvento/detEvento/evCTeAutorizadoMDFe/MDFe/nProt', unzipedXML) , # 05  WS-D-R-3-PROTOCOLO         PIC  9(015). 
            getXpath('procEventoCTe/eventoCTe/infEvento/detEvento/evCTeAutorizadoMDFe/MDFe/modal', unzipedXML) , # 05   WS-D-R-3-MODAL            PIC  9(002). 
            ' '
        ]
        name = jsonConfig['dir_procEventoCTe'] + chave + '-procEvCTe.xml'
        
    else:
        name = jsonConfig['dir_retorno'] + 'NAO_CATEGORIZADO' + str(datetime.now()).replace(' ', '').replace('.', '').replace('-', '').replace(':', '') + schema + ".xml"
    
    if retornoCob != 'debug':
        with open(name, 'w+') as f:
            f.write(unzipedXML)
        
    linhas.append('|'.join(retorno))
    
arquivo = [header] + linhas
arquivo = '\n'.join(arquivo)



if retornoCob == 'debug':
    # print(arquivo)
    pass
else :
    with open(retornoCob, 'w+') as f:
        f.write(arquivo)
        
# print(arquivo)
