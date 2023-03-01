import urllib2
import os
import sys
import urllib, urllib2
from subprocess import call
from pprint import pprint




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
        strF = strF.replace('  '  ,  ' ')
        strF = strF.replace("\n " , '\n')
        strF = strF.replace(" \n" , '\n')
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
    


def prepare(txt):
    partes   = txt.split('\n')
    arReturn = list()
    
    for parte in partes:
        if 'href=' in parte:
            parte = parte.replace('<li class="frontier-query-infobox-items fq-infobox-items-list">', '')
            parte = parte.replace('</li>', '')
            # parte = getTextFromHTML(parte)
            arReturn.append(parte)
    
    # return '\n'.join(arReturn)
    return arReturn

def criarLayout(links):
    with open('/var/www/html/nf/NF_V3/NF_V3/testes_automatizados/robob/view/novidades.html') as arquivo:
        html = arquivo.read()
    return html.replace('{{links}}',''.join(links))

def getTxtFromUrl(url, params):
    data     = urllib.urlencode(params)
    request  = urllib2.Request(url, data)
    contents = urllib2.urlopen(request).read()
    contents = cutTxt(contents, '<fieldset', 'fieldset>')
    contents = prepare(contents)
    contents = criarLayout(contents)
    # contents = getTextFromHTML(contents)
    return contents








spedbr = 'https://portalspedbrasil.com.br/topicos-da-semana/'


HTMLspedbr = getTxtFromUrl(spedbr, {})


print( HTMLspedbr ) 







