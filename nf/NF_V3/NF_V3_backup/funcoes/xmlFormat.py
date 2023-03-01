from pprint import pprint
txt = '''
NOTAFISCAL|001|
A|4.00|NFe|
B|41|08442465|Outras Saidas|55|1|7|2019-07-02T08:44:24-03:00|2019-07-02T08:44:24-03:00|1|1|4106902|1|1|0|2|1|0|9|0|1.0.0| | |
C02|82231739000179|
C|FIENG - SANTA FELICIDADE|FIENG STA FELIC|
C05|ESTR JUSTO MONFROM|000590| |SANTA FELICIDADE|4106902|CURITIBA|PR|82410540|1058|BRASIL|4136578022|
C17|9081236620| | | |3|
E02|76630573000241|
E|NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|
E05|RODOVIA CURITIBA - PONTA GROSSA, BR 277,|000134| |NOVA SERRINHA|4102307|BALSA NOVA|PR|83650000|1058|BRASIL|4132912000|
E16a|1|1330006745| | | |
H|1|
I|298562F|SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000|
I05b| | | |5949|SC|40.0000|16.6500000000|666.00|SEM GTIN|SC|40.0000|16.6500000000| | | | |1|
N|
N06|4|41| | |
O| |82231739000179| | |999|
O08|53|
Q|
Q05|49|
Q07|0.00|0.00|
Q09|0.00|
S|
S05|49|
S07|0.00|0.00|
S11|0.00|
H|2|
I|298562F|SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000|
I05b| | | |5949|SC|40.0000|16.6470000000|665.88|SEM GTIN|SC|40.0000|16.6470000000| | | | |1|
N|
N06|4|41| | |
O| |82231739000179| | |999|
O08|53|
Q|
Q05|49|
Q07|0.00|0.00|
Q09|0.00|
S|
S05|49|
S07|0.00|0.00|
S11|0.00|
H|3|
I|298562F|SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000|
I05b| | | |5949|SC|80.0000|16.6470000000|1331.76|SEM GTIN|SC|80.0000|16.6470000000| | | | |1|
N|
N06|4|41| | |
O| |82231739000179| | |999|
O08|53|
Q|
Q05|49|
Q07|0.00|0.00|
Q09|0.00|
S|
S05|49|
S07|0.00|0.00|
S11|0.00|
W|
W02|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|2663.64|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|2663.64|0.00|
X|0|
Y|
YA|0|90|0.00|
Z| |MATERIAIS APLICADOS NA MANUTENCAO DE REDES E RAMAIS DE AGUA E ESGOTO EM DIVERSASRUAS DE CURITIBA EM ATENDIMENTO AO CONTRATO FIRMADO COM A COMPANHIA DE SANEAMEN TO DO PARANA.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
ZD|84818889000109|DIB DAHER SELOUAN|dib@softdib.com.br|4132766457| |                                    |



























B|41|08442465|Outras Saidas|55|1|7|2019-07-02T08:44:24-03:00|2019-07-02T08:44:24-03:00|1|1|4106902|1|1|0|2|1|0|9|0|1.0.0| | | 
C|FIENG - SANTA FELICIDADE|FIENG STA FELIC| 
C05|ESTR JUSTO MONFROM|000590| |SANTA FELICIDADE|4106902|CURITIBA|PR|82410540|1058|BRASIL|4136578022| 
C| 
C17|9081236620| | | |3| 
E02|76630573000241| 
E|NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL| 
E05|RODOVIA CURITIBA - PONTA GROSSA, BR 277,|000134| |NOVA SERRINHA|4102307|BALSA NOVA|PR|83650000|1058|BRASIL|4132912000| 
E| 
E16a|1|1330006745| | | | 
I| |SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000| 
I| 
I05b| | | |5949|SC|40.0000|16.6500000000|666.00|SEM GTIN|SC|40.0000|16.6500000000| | | | |1| 
N| 
N06|4|41| | | 
O| | | | |999| 
O| 
O08|53| 
Q05|49| 
Q07|0.00|0.00| 
Q| 
Q09|0.00| 
S05|49| 
S07|0.00|0.00| 
S| 
S11|0.00| 
I|298562F|SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000| 
I| 
I05b| | | |5949|SC|40.0000|16.6470000000|665.88|SEM GTIN|SC|40.0000|16.6470000000| | | | |1| 
N| 
N06|4|41| | | 
O| | | | |999| 
O| 
O08|53| 
Q05|49| 
Q07|0.00|0.00| 
Q| 
Q09|0.00| 
S05|49| 
S07|0.00|0.00| 
S| 
S11|0.00| 
I|298562F|SEM GTIN|NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL|00000000| 
I| 
I05b| | | |5949|SC|80.0000|16.6470000000|1331.76|SEM GTIN|SC|80.0000|16.6470000000| | | | |1| 
N| 
N06|4|41| | | 
O| | | | |999| 
O| 
O08|53| 
Q05|49| 
Q07|0.00|0.00| 
Q| 
Q09|0.00| 
S05|49| 
S07|0.00|0.00| 
S| 
S11|0.00| 
W| 
W02|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|2663.64|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|0.00|2663.64|0.00| 
X|0| 
Y| 
YA|0|90|0.00| 
Z| |MATERIAIS APLICADOS NA MANUTENCAO DE REDES E RAMAIS DE AGUA E ESGOTO EM DIVERSASRUAS DE CURITIBA EM ATENDIMENTO AO CONTRATO FIRMADO COM A COMPANHIA DE SANEAMEN TO DO PARANA.| 




'''

xml="""<infNFe>
    <ide>
        <cUF>41</cUF>
        <cNF>08442465</cNF>
        <natOp>Outras Saidas</natOp>
        <mod>55</mod>
        <serie>1</serie>
        <nNF>7</nNF>
        <dhEmi>2019-07-02T08:44:24-03:00</dhEmi>
        <dhSaiEnt>2019-07-02T08:44:24-03:00</dhSaiEnt>
        <tpNF>1</tpNF>
        <idDest>1</idDest>
        <cMunFG>4106902</cMunFG>
        <tpImp>1</tpImp>
        <tpEmis>1</tpEmis>
        <cDV>0</cDV>
        <tpAmb>2</tpAmb>
        <finNFe>1</finNFe>
        <indFinal>0</indFinal>
        <indPres>9</indPres>
        <procEmi>0</procEmi>
        <verProc>1.0.0</verProc>
    </ide>
    <emit>
        <CNPJ>82231739000179</CNPJ>
        <xNome>FIENG - SANTA FELICIDADE</xNome>
        <xFant>FIENG STA FELIC</xFant>
        <enderEmit>
            <xLgr>ESTR JUSTO MONFROM</xLgr>
            <nro>000590</nro>
            <xBairro>SANTA FELICIDADE</xBairro>
            <cMun>4106902</cMun>
            <xMun>CURITIBA</xMun>
            <UF>PR</UF>
            <CEP>82410540</CEP>
            <cPais>1058</cPais>
            <xPais>BRASIL</xPais>
            <fone>4136578022</fone>
        </enderEmit>
        <IE>9081236620</IE>
        <CRT>3</CRT>
    </emit>
    <dest>
        <CNPJ>76630573000241</CNPJ>
        <xNome>NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL</xNome>
        <enderDest>
            <xLgr>RODOVIA CURITIBA - PONTA GROSSA, BR 277,</xLgr>
            <nro>000134</nro>
            <xBairro>NOVA SERRINHA</xBairro>
            <cMun>4102307</cMun>
            <xMun>BALSA NOVA</xMun>
            <UF>PR</UF>
            <CEP>83650000</CEP>
            <cPais>1058</cPais>
            <xPais>BRASIL</xPais>
            <fone>4132912000</fone>
        </enderDest>
        <indIEDest>1</indIEDest>
        <IE>1330006745</IE>
    </dest>
    <det>
        <prod>
            <cProd>298562F</cProd>
            <cEAN>SEM GTIN</cEAN>
            <xProd>NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL</xProd>
            <NCM>00000000</NCM>
            <CFOP>5949</CFOP>
            <uCom>SC</uCom>
            <qCom>40.0000</qCom>
            <vUnCom>16.6500000000</vUnCom>
            <vProd>666.00</vProd>
            <cEANTrib>SEM GTIN</cEANTrib>
            <uTrib>SC</uTrib>
            <qTrib>40.0000</qTrib>
            <vUnTrib>16.6500000000</vUnTrib>
            <indTot>1</indTot>
        </prod>
        <imposto>
            <ICMS>
                <ICMS40>
                    <orig>4</orig>
                    <CST>41</CST>
                </ICMS40>
            </ICMS>
            <IPI>
                <CNPJProd>82231739000179</CNPJProd>
                <cEnq>999</cEnq>
                <IPINT>
                    <CST>53</CST>
                </IPINT>
            </IPI>
            <PIS>
                <PISOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pPIS>0.00</pPIS>
                    <vPIS>0.00</vPIS>
                </PISOutr>
            </PIS>
            <COFINS>
                <COFINSOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pCOFINS>0.00</pCOFINS>
                    <vCOFINS>0.00</vCOFINS>
                </COFINSOutr>
            </COFINS>
        </imposto>
    </det>
    <det>
        <prod>
            <cProd>298562F</cProd>
            <cEAN>SEM GTIN</cEAN>
            <xProd>NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL</xProd>
            <NCM>00000000</NCM>
            <CFOP>5949</CFOP>
            <uCom>SC</uCom>
            <qCom>40.0000</qCom>
            <vUnCom>16.6470000000</vUnCom>
            <vProd>665.88</vProd>
            <cEANTrib>SEM GTIN</cEANTrib>
            <uTrib>SC</uTrib>
            <qTrib>40.0000</qTrib>
            <vUnTrib>16.6470000000</vUnTrib>
            <indTot>1</indTot>
        </prod>
        <imposto>
            <ICMS>
                <ICMS40>
                    <orig>4</orig>
                    <CST>41</CST>
                </ICMS40>
            </ICMS>
            <IPI>
                <CNPJProd>82231739000179</CNPJProd>
                <cEnq>999</cEnq>
                <IPINT>
                    <CST>53</CST>
                </IPINT>
            </IPI>
            <PIS>
                <PISOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pPIS>0.00</pPIS>
                    <vPIS>0.00</vPIS>
                </PISOutr>
            </PIS>
            <COFINS>
                <COFINSOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pCOFINS>0.00</pCOFINS>
                    <vCOFINS>0.00</vCOFINS>
                </COFINSOutr>
            </COFINS>
        </imposto>
    </det>
    <det>
        <prod>
            <cProd>298562F</cProd>
            <cEAN>SEM GTIN</cEAN>
            <xProd>NOTA FISCAL EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL</xProd>
            <NCM>00000000</NCM>
            <CFOP>5949</CFOP>
            <uCom>SC</uCom>
            <qCom>80.0000</qCom>
            <vUnCom>16.6470000000</vUnCom>
            <vProd>1331.76</vProd>
            <cEANTrib>SEM GTIN</cEANTrib>
            <uTrib>SC</uTrib>
            <qTrib>80.0000</qTrib>
            <vUnTrib>16.6470000000</vUnTrib>
            <indTot>1</indTot>
        </prod>
        <imposto>
            <ICMS>
                <ICMS40>
                    <orig>4</orig>
                    <CST>41</CST>
                </ICMS40>
            </ICMS>
            <IPI>
                <CNPJProd>82231739000179</CNPJProd>
                <cEnq>999</cEnq>
                <IPINT>
                    <CST>53</CST>
                </IPINT>
            </IPI>
            <PIS>
                <PISOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pPIS>0.00</pPIS>
                    <vPIS>0.00</vPIS>
                </PISOutr>
            </PIS>
            <COFINS>
                <COFINSOutr>
                    <CST>49</CST>
                    <vBC>0.00</vBC>
                    <pCOFINS>0.00</pCOFINS>
                    <vCOFINS>0.00</vCOFINS>
                </COFINSOutr>
            </COFINS>
        </imposto>
    </det>
    <total>
        <ICMSTot>
            <vBC>0.00</vBC>
            <vICMS>0.00</vICMS>
            <vICMSDeson>0.00</vICMSDeson>
            <vFCPUFDest>0.00</vFCPUFDest>
            <vICMSUFDest>0.00</vICMSUFDest>
            <vICMSUFRemet>0.00</vICMSUFRemet>
            <vFCP>0.00</vFCP>
            <vBCST>0.00</vBCST>
            <vST>0.00</vST>
            <vFCPST>0.00</vFCPST>
            <vFCPSTRet>0.00</vFCPSTRet>
            <vProd>2663.64</vProd>
            <vFrete>0.00</vFrete>
            <vSeg>0.00</vSeg>
            <vDesc>0.00</vDesc>
            <vII>0.00</vII>
            <vIPI>0.00</vIPI>
            <vIPIDevol>0.00</vIPIDevol>
            <vPIS>0.00</vPIS>
            <vCOFINS>0.00</vCOFINS>
            <vOutro>0.00</vOutro>
            <vNF>2663.64</vNF>
            <vTotTrib>0.00</vTotTrib>
        </ICMSTot>
    </total>
    <transp>
        <modFrete>0</modFrete>
    </transp>
    <pag>
        <detPag>
            <indPag>0</indPag>
            <tPag>90</tPag>
            <vPag>0.00</vPag>
        </detPag>
    </pag>
    <infAdic>
        <infCpl>MATERIAIS APLICADOS NA MANUTENCAO DE REDES E RAMAIS DE AGUA E ESGOTO EM DIVERSASRUAS DE CURITIBA EM ATENDIMENTO AO CONTRATO FIRMADO COM A COMPANHIA DE SANEAMEN TO DO PARANA.</infCpl>
    </infAdic>
    <infRespTec>
        <CNPJ>84818889000109</CNPJ>
        <xContato>DIB DAHER SELOUAN</xContato>
        <email>dib@softdib.com.br</email>
        <fone>4132766457</fone>
    </infRespTec>
</infNFe>"""

isTag=False
tagName=''

isVal=False
valName=False

xpath=''
cache=""

retorno = []
for leter in xml:
        
    if leter == '>':
        isTag=False
        isVal=True
        
        if '/' in tagName :
            if valName != False:
                valName = valName.replace(r'>(.)*<', '$1')
                retorno.append({
                    "xpath" :   xpath , 
                    "value" : valName   
                })
            
            xpath = xpath.replace(tagName,'')
            
        else:
            xpath += '/' + tagName
            
        valName = False
        
    if isTag:
        #if leter == ' ': ## Atributo
        tagName += leter
        
    if isVal:
        if leter not in ('>', '<'):
            if valName is False:
                valName = ''
            valName += leter
        
    if leter == '<':
        tagName = ''
        isVal=False
        isTag=True
        
        
        




import json
from collections import OrderedDict

map = '/home/zulian/SERVIDOR/var/www/html/nf/NF_V3/NF_V3/templates/nfe2xml.json'
map = json.load(open(map), object_pairs_hook=OrderedDict)
map = map['4.00']['mapa']





def discoveryLinha(xpath):
    for linha in map  : 
        if xpath in map[linha]:
            return linha, map[linha]


txtRetorno=[]
def putOnTxt(linha):
    txtRetorno.append(linha)


def mypop(arr):
    return (arr[0], arr[1:])

pilhaAtualNome  = " "
pilhaAtualDados = []
linhaAtual = []
def controlePilha(linha, xpath, dado):
    global pilhaAtualNome
    global pilhaAtualDados
    global linhaAtual
    # inicia linha
    
    '''
    if  pilhaAtualNome != linha  and len(pilhaAtualDados) > 0:
        for _xpathMap in pilhaAtualDados:
            
    '''
    
    if  pilhaAtualNome != linha  and  len(pilhaAtualDados) == 0:
        
        pilhaAtualNome  = linha
        pilhaAtualDados = map[linha]
        linhaAtual.append(' ')
        putOnTxt('|'.join(linhaAtual))
        linhaAtual = [linha]
        _ret, pilhaAtualDados = mypop(pilhaAtualDados)
        
    
    elif len(pilhaAtualDados) == 0 :
        pilhaAtualDados = map[linha]
        _ret, pilhaAtualDados = mypop(pilhaAtualDados)
        
    
    for _xpathMap in pilhaAtualDados:
        xpathAtual, pilhaAtualDados = mypop(pilhaAtualDados)
        
        if xpath == xpathAtual:
            return linhaAtual.append(dado)
        else:
            linhaAtual.append(' ')
            
        
    
    
    
    
    # if dado == 'linha':
        
    
    
    
    

# pprint(map)
txt=''
for item in retorno : 
    
    linha, dadosMapa = discoveryLinha(item['xpath'])
    
    controlePilha(linha, item['xpath'], item['value'])

linhaAtual.append(' ')
putOnTxt('|'.join(linhaAtual))

print( '\n'.join(txtRetorno) )





    





