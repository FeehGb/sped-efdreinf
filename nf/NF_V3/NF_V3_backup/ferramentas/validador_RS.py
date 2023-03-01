#!/usr/bin/env python
# -*- coding: utf-8 -*-

import urllib
import urllib2
import sys
from pprint import pprint

contador = {}

def validar(file):
    
    with open(file) as _file:
        xml = _file.read().replace("\n", "")
        
    data = urllib.urlencode({'txtxml': xml})
    
    request = urllib2.Request("https://www.sefaz.rs.gov.br/NFE/NFE-VAL.aspx", data)
    contents = urllib2.urlopen(request).read()
    
    # em caso de falha interna do sefaz
    if 'Ocorreu um erro no validador de mensagens' in contents:
        
        if file not in contador:
            contador[file] = 0
        
        contador[file] += 1
        
        pprint("ERRO>>>> " + str(contador[file]) + " " + file)
        
        if contador[file] < 5 :
            return validar(file)

    contents = contents.split("<br /><br />")[1]
    contents = contents.split("\n</span>")[0] + "</span>"
    contents = contents.replace("\n", " ")
    contents = contents.strip()

    chars = list(contents)

    isString = False
    strF = ""
    for char in chars:
        if char == '<':
            isString = False
        elif char == '>':
            isString = True
        
        if isString:
            strF += char
            
    strF = strF.replace('>', "\n")
    
    strF = strF.replace("\n \n", "\n")
    
    while "\n\n" in strF:
        strF = strF.replace("\n\n", "\n")
    while "  " in strF:
        strF = strF.replace("  ", " ")
    
    strF = strF.replace("\n "  , "\n")
    strF = strF.replace("[\n"  , "[")
    strF = strF.replace("\n]"  , "]")
    
    return strF
    
if __name__ == "__main__":
    pprint(validar(sys.argv[1]))
    
    
