# import sys
# import json
# #import re
# import txt2xml_logs
from   pprint                import pprint
# from   collections           import OrderedDict
import xml.etree.ElementTree                       as ET
# #from xmljson                 import badgerfish     as bf
# from xml.etree.ElementTree   import fromstring
# from json                    import dumps       
# 


import json

from collections import OrderedDict

pathMap = '/home/zulian/SERVIDOR/var/www/html/nf/NF_V3/NF_V3/templates/nfe2xml.json'
txtBase = '/home/zulian/SERVIDOR/home/luiz/conversorNFE/NFE-000000007-NE-000006-20190702-084424.TXT'
map     = '/home/zulian/SERVIDOR/var/www/html/nf/NF_V3/NF_V3/templates/nfe2xml.json'
xml     = '/home/zulian/SERVIDOR/home/luiz/conversorNFE/NFE-000000007-NE-000006-20190702-084424.TXT\
.validacao.xml'



#  /home/zulian/SERVIDOR/home/luiz/conversorNFE/NFE-000000007-NE-000006-20190702-084424.TXT.validacao.xml

# /home/luiz/conversorNFE/NFE-000000007-NE-000006-20190702-084424.TXT.validacao2.xml
# xml_string = ""
# with open(xml) as file:
#     
#     xml_string = file.read()
#     xml_string = xml_string.strip()
#     
#     # xml_string = '''<NFe xmlns="http://www.portalfiscal.inf.br/nfe"><infNFe versao="4.00" Id="NFe41190782231739000179550010000000071084424650"><ide><cUF>41</cUF></NFe>'''
#     
#     # print(xml_string)
#     import re
# 
# 
# 
#     # xml_string = xml_string.replace(r'^\<\w*\sxmlns=[^>]*>|<\/\w*>$' , '???')
#     # xml_string = xml_string.replace('<NFe xmlns="http://www.portalfiscal.inf.br/nfe">' , '')
#     # xml_string = xml_string.replace('</NFe>' , '')
#     
#     # print("\n\n--------------------\n\n")
#     xml_string = re.sub(
#         r'^<\w*[^>]*>|<\/\w*>$', #TODO pegar dinamico a tag raiz e colocar ela novamente
#         '',
#         xml_string
#     )
#     # print(xml_string)
#     
#     
# 
# mydoc = ET(xml_string)
# for e in mydoc.findall('/foo/bar'):
#     print(e.get('title').text)


#   
import xml.etree.ElementTree as ET
#   root = ET.parse(xml)
#   result = ''
#   #pprint(root)
#   for elem in root.findall('//@prod'):
#       # How to make decisions based on attributes even in 2.6:
#       print(elem.attrib.get('name'))
#       # if elem.attrib.get('name') == 'foo':
#       #     result = elem.text
#       #     break


# e = ET.parse(open(xml,"r")).getroot().findall('.//infNFe/ide/dhEmi')



class class_xml2txt:
    def __init__(self):
        
        self.linhasIgnoradas = ["NOTAFISCAL", "A", "@"]
        self.sempreRepetir   = ['H', 'Y', 'Z']
        
        self.repetidas = {}
        
        self.process()
        # pprint(self.getFromXpath('.//infNFe/ide/dhEmi'))
        self.processMap()
        
    def process(self):
        # load xml
        self.root = ET.parse(open(xml,"r")).getroot()
        #load map
        self.map = json.load(open(map), object_pairs_hook=OrderedDict)
        self.map = self.map['4.00']['mapa']
        
    def getFromXpath(self, xpath, index, root=False):
        if root == False : 
            root = self.root
        
        # print('BUSCANDO -> ./' + xpath)
        if '@' in xpath : 
            print(xpath)
            return ' '
            
        e = root.findall('./' + xpath)
        if e : 
            # print(len(e))
            return e[index].text
        else:
            return ' '
        
    def processMap(self):
        
        self.map
        for linha in self.map:
            
            if linha not in self.repetidas:
                self.repetidas[linha] = 0
                
            else:
                self.repetidas[linha] = self.repetidas[linha] + 1
                
            
            if linha in self.linhasIgnoradas:
                continue
            # elif linha in self.liberado : 
            
            linhaCache = []
            for xpath in self.map[linha]:
                # pprint ( linha + ' -> ' + xpath )
                if (xpath=='linha'):
                    linhaCache.append(linha)
                else:
                    linhaCache.append(self.getFromXpath(xpath, self.repetidas[linha]))
                
            linhaCache.append('')
            linhaCache = '|'.join(linhaCache)
            print(linhaCache)
            
        
        
    
class_xml2txt()


# B|41|08442465|Outras Saidas|55|1|7|2019-07-02T08:44:24-03:00|2019-07-02T08:44:24-03:00|1|1|4106902|1|1|0|2|1|0|9|0|1.0.0| | |
# B|41|08442465|Outras Saidas|55|1|7|2019-07-02T08:44:24-03:00|2019-07-02T08:44:24-03:00|1|1|4106902|1|1|0|2|1|0|9|0|1.0.0| | |
'''
import json
from collections import OrderedDict
mapa = json.load(open(map), object_pairs_hook=OrderedDict)
mapa = mapa['4.00']['mapa']
for linha in mapa:
    for dado in mapa[linha]:
        pprint ( linha + ' -> ' + dado )
# pprint(dumps(bf.data(fromstring(xml_string))))
'''