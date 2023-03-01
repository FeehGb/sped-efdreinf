#!/usr/bin/python
# -*- coding: utf-8 -*-

import os
import sys
import urllib, urllib2
from subprocess import call
from pprint import pprint
from os import listdir
from os.path import isfile, join


contador  = {}


dirTestes = '/user/nfe/teste/'


# python /var/www/html/nf/NF_V3/NF_V3/testes_automatizados/nfe/nfe.py user/nfe/02547003000175/CaixaEntrada/Processar/NFE-000003762-NE-001520-20180412-085944.TXT
def validTXT(fileEntrada):
    # Chama o php para montar o XML
    
    # Valida a versao do txt
    txtConteudo = open(fileEntrada).read()
    autorizadora = 'PR'
    
    if '|3.10|' in txtConteudo and '|4.00|' not in txtConteudo:
        return 'TXT NAO ESTA NA 4.00'
    if '|65|' in txtConteudo:
        autorizadora = 'NFE-PR'
    
    comando = 'php /var/www/html/nf/NF_V3/NF_V3/interfaces/nfe-envio.php ' + fileEntrada + ' /home/luiz/nfeLogs/envioNFE.TXT PR ' + autorizadora + ' -1 1'
    print(comando)
    comando = comando.split(' ')
    
    call(comando)
    
    # caminho de saida do xml
    xmlPath = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/nfe/"+str(fileEntrada.split('/')[-1])+".validacao.xml"
    
    if not isfile(xmlPath):
        return validConvertionErrors(fileEntrada)
    
    # chama o validador de XML
    return validXML(xmlPath)



def validConvertionErrors(file):
    import sys
    sys.path.append('/var/www/html/nf/NF_V3/NF_V3/funcoes/')
    import txt2xml_logs
    my_hash = txt2xml_logs.my_md5(file)
    
    file = "/var/www/html/transf/"+my_hash+".html"
    
    if isfile(file):
        hashfile = open(file, 'r').read() 
        return hashfile
    






def validXML(file):
    
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
        
        # if contador[file] < 5 :
        #     return validar(file)
    
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
    
    strF = strF.replace(":\n", ": ")
    strF = strF.replace(": \n", ": ")
    
    strF = colorir(strF)
    strF = extraInfo(strF)
    
    return strF



def colorir(text):
    changedLines = list()
    lines = text.split('\n')
    for line in lines :
        # 
        if "[Simulacao] Rejeicao" in line:
            line = "\033[31m" + line + "\033[0m"
        
        #
        if "Assinatura Digital: " in line:
            partes = line.split(": ")
            if "Inv" in partes[1]:
                line = partes[0] + ": \033[31m" + partes[1] + "\033[0m"
            else:
                line = partes[0] + ": \033[32m" + partes[1] + "\033[0m"
        #
        if "Schema XML: " in line:
            partes = line.split(": ")
            if "Nenhum erro encontrado" in partes[1]:
                line = partes[0] + ": \033[32m" + partes[1] + "\033[0m"
            else:
                line = partes[0] + ": \033[31m" + partes[1] + "\033[0m"
        if "Schema e de Regras" in line:
            line = line.replace("Nenhum erro encontrado", ": \033[32mNenhum erro encontrado\033[0m")
        '''
        if "Schema e de Regras" in line or False:
            partes = line.split(": ")
            if "Nenhum erro encontrado" in partes[1]:
                line = partes[0] + ": \033[32m" + partes[1] + "\033[0m"
            else:
                line = partes[0] + ": \033[31m" + partes[1] + "\033[0m"
        '''
        if "failed." in line :
            line = "\033[31m" + line + "\033[0m"
            
        if "primeiros erros]" in line :
            line = "\033[31m" + line + "\033[0m"
            
        if "Caminho: " in line:
            line = "\033[32m" + line + "\033[0m"
            
                
        changedLines.append(line)
        
    text = '\n'.join(changedLines)
        
    return text


def extraInfo(txt):
    
    return txt
    '''
    if 'Caminho: ' in txt:
        txts = txt.split('Caminho: ')
        
        caminhoErro = txts[1].split('\n')[0]
        
        
        if 'element is invalid - The value' in txt:
            erro = "Valor invalido"
        
        if 'has invalid child element' in txt:
            erro = "Elemento no lugar errado"
        
        
    '''
    
    
    


def validarTodos():
    
    from os import listdir
    from os.path import isfile, join
    import time
    
    files = [f for f in listdir(dirTestes) if isfile(join(dirTestes, f))]
    
    for file in files:
        file = dirTestes + file
        filetype = str(file.split('.')[-1]).upper()
        if  filetype == 'TXT':
            print(validTXT(file))
            
        elif filetype == 'XML':
            print(validXML(file))
        
        time.sleep(5)
        
    



if __name__ == "__main__":
    if len(sys.argv) > 1:
        fileEntrada = sys.argv[1]
        filetype = str(fileEntrada.split('.')[-1]).upper()
        
        if  filetype == 'TXT':
            print(validTXT(fileEntrada))
            
        elif filetype == 'XML':
            print(validXML(fileEntrada))
        else:
            dirTestes = sys.argv[1]
            validarTodos()
    else:
        validarTodos()
    






#/user/nfe/07405353000110/CaixaEntrada/Processar/










