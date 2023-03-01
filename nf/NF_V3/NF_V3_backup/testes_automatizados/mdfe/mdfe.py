#!/usr/bin/python
# -*- coding: utf-8 -*-


# python /var/www/html/nf/NF_V3/NF_V3/testes_automatizados/mdfe/mdfe.py   /user/mdfe/72071541000200/CaixaSaida/Sefaz/MDFER/MDFER-000009818-000-20180328-083923.TXT  

import os
import sys
import urllib, urllib2
from subprocess import call
from pprint import pprint

import curses
stdscr = curses.initscr()


def getTextFromHTML(text):
    chars    = list(text)
    isString = False
    strF = ""
    for char in chars:
        if char == '<':
            isString = False
        elif char == '>':
            char = "\n"
            isString = True
        
        if isString:
            strF += char
    
    teste = True
    
    while teste:
        strF = strF.replace('  ', ' ')
        strF = strF.replace("\n ", '\n')
        strF = strF.replace(" \n", '\n')
        strF = strF.replace("\n\n", '\n')
        
        if '  ' not in strF:
            teste = False
    
    strF = unicode(strF, "utf-8")
    
    return strF
    

def cutTxt(txt, ini, end) : 
    txt = txt.split(ini)[1] 
    txt = txt.split(end)[0] 
    txt = ini  + txt  + end 
    return txt
    

def getTxtFromUrl(url, params):
    data = urllib.urlencode(params)
    request = urllib2.Request(url, data)
    contents = urllib2.urlopen(request).read()
    return contents


def validTXT(fileEntrada):
    # Chama o php para montar o XML
    call(["php", "/var/www/html/nf/NF_V3/NF_V3/interfaces/mdfe-envio.php", fileEntrada, "RS"])
    
    # caminho de saida do xml
    xmlPath = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/mdfe/"+str(fileEntrada.split('/')[-1])+".xml"
    
    # chama o validador de XML
    return validXML(xmlPath)
    
    
def validXML(fileEntrada):
    # abre o xml
    with open(fileEntrada) as xml:
        _xml =  xml.read().replace("\n", "")
        # corta nas tags para validar online
        _xml = cutTxt(_xml, "<enviMDFe ", "</enviMDFe>")
    
    contents = getTxtFromUrl('https://mdfe-portal.sefaz.rs.gov.br/site/ValidadorXml', {'txtMDFe': _xml})
    contents = cutTxt(contents, '<span id="txtResult">', '</form>')
    contents = contents.replace('\n', " ")
    contents = contents.replace('\r', '' )
    contents = getTextFromHTML(contents)
    
    return contents
    
    
def validarTestes():
    filePath = os.path.dirname(os.path.abspath(__file__))
    filePath+='/txt/'
    onlyfiles = [f for f in os.listdir(filePath) if os.path.isfile(os.path.join(filePath, f))]
    for txt in onlyfiles:
        retorno = validTXT(filePath + txt)
        
        
        if   "Parser Xml:\nOk" not in retorno:
            msg = "Erro d XML"
            
        elif "Tipo de Mensagem:\nLote MDF-e" not in retorno:
            msg = "Tipo de mensagem nao identificada"
            
        elif "Schema:\nOk" not in retorno:
            msg = "Erro de Schema"
            
        elif "Assinatura Xml:\nAssinatura V" not in retorno:
            msg = "Erro de Assinatura"
        else:
            msg = "Tudo OK"
            
            '''
        else:
            print(retorno)'''
        
        print("############################" + msg + "#################################")
        print(retorno)
    
    
if __name__ == "__main__":
    if len(sys.argv) > 1:
        fileEntrada = sys.argv[1]
        filetype = str(fileEntrada.split('.')[-1]).upper()
        
        if  filetype == 'TXT':
            print(validTXT(fileEntrada))
            
        elif filetype == 'XML':
            print(validXML(fileEntrada))
    else:
        validarTestes()
