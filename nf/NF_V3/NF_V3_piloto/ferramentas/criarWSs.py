#!/bin/python

import json
import os
from  pprint  import pprint


basePath = "/var/www/html/nf/NF_V3/NF_V3_dados/temp/teste/"
outputPath = '/var/www/html/nf/NF_V3/NF_V3/servicos/nfe/webservices_bkp/'



def createFile(dados):
    
    arquivo=[]
    
    if 'NFeAutorizacao' in dados :
        arquivo.append('''
            "recepcao": {
                "url"           : "''' + dados['NFeAutorizacao'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeAutorizacao4",
                "servico"       : "NFeAutorizacao4",
                "metodo"        : "nfeAutorizacaoLote4",
                "versao"        : "4.00",
                "schema"        : "enviNFe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeAutorizacao4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeAutorizacao4'><enviNFe xmlns = 'http://www.portalfiscal.inf.br/nfe' versao='4.00'><idLote>ALTERAR_DADOS_ID_LOTE</idLote><indSinc>ALTERAR_INDSINC</indSinc>ALTERAR_DADOS_XML</enviNFe></nfeDadosMsg>"
            }
        ''')
    
    if 'NFeRetAutorizacao' in dados :
        arquivo.append('''
            "consulta_lote": {
                "url"           : "''' + dados['NFeRetAutorizacao'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeRetAutorizacao4",
                "servico"       : "NFeRetAutorizacao4",
                "metodo"        : "nfeRetAutorizacaoLote4",
                "versao"        : "4.00",
                "schema"        : "consReciNFe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRetAutorizacao4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRetAutorizacao4'><consReciNFe xmlns='http://www.portalfiscal.inf.br/nfe' versao='4.00'><tpAmb>ALTERAR_TIPO_AMBIENTE</tpAmb><nRec>ALTERAR_RECIBO</nRec></consReciNFe></nfeDadosMsg>"
            }
        ''')
        
    if 'NfeConsultaProtocolo' in dados :
        arquivo.append('''
            "consulta_nota": {
                "url"           : "''' + dados['NfeConsultaProtocolo'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeConsultaProtocolo4",
                "servico"       : "NFeConsultaProtocolo4",
                "metodo"        : "nfeConsultaNF",
                "versao"        : "4.00",
                "schema"        : "consReciBFe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeConsultaProtocolo4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeConsultaProtocolo4'><consSitNFe xmlns='http://www.portalfiscal.inf.br/nfe' versao='4.00'><tpAmb>ALTERAR_TIPO_AMBIENTE</tpAmb><xServ>CONSULTAR</xServ><chNFe>ALTERAR_CHAVE_NFE</chNFe></consSitNFe></nfeDadosMsg>"
            }
        ''')
    
    
    if 'NfeStatusServico' in dados :
        arquivo.append('''
            "status": {
                "url"           : "''' + dados['NfeStatusServico'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4",
                "servico"       : "nfeStatusServicoNF4",
                "metodo"        : "nfeStatusServico",
                "versao"        : "4.00",
                "schema"        : "",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4'></nfeCabecMsg > ",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4'><consStatServ xmlns = 'http://www.portalfiscal.inf.br/nfe' versao='4.00'><tpAmb>ALTERAR_TIPO_AMBIENTE</tpAmb><cUF>41</cUF><xServ>STATUS</xServ></consStatServ></nfeDadosMsg>"
            }
        ''')
    
    if 'RecepcaoEvento' in dados :
        arquivo.append('''
            "cancelamento":{
                "url"           : "''' + dados['RecepcaoEvento'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4",
                "servico"       : "NFeRecepcaoEvento4",
                "metodo"        : "nfeAutorizacaoLote4",
                "versao"        : "1.00",
                "schema"        : "envEventoCancNFe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4'><envEvento xmlns = 'http://www.portalfiscal.inf.br/nfe' versao='1.00'><idLote>ALTERAR_ID_LOTE</idLote><evento versao = '1.00'><infEvento Id='ALTERAR_ID_EVENTO'><cOrgao>41</cOrgao><tpAmb>2</tpAmb><CNPJ>ALTERAR_CNPJ</CNPJ><chNFe>ALTERAR_CHAVE_NFE</chNFe><dhEvento>ALTERAR_DATA_HORA_EVENTO</dhEvento><tpEvento>110111</tpEvento><nSeqEvento>1</nSeqEvento><verEvento>1.00</verEvento><detEvento versao='1.00'><descEvento>Cancelamento</descEvento><nProt>ALTERAR_PROTOCOLO</nProt><xJust>ALTERAR_JUSTIFICATIVA</xJust></detEvento></infEvento></evento></envEvento></nfeDadosMsg>"
            }
        ''')
    
    if 'RecepcaoEvento' in dados :
        arquivo.append('''
            "carta_correcao":{
                "url"           : "''' + dados['RecepcaoEvento'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4",
                "servico"       : "NFeRecepcaoEvento4",
                "metodo"        : "nfeAutorizacaoLote4",
                "versao"        : "1.00",
                "schema"        : "envCCe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeRecepcaoEvento4'><envEvento xmlns = 'http://www.portalfiscal.inf.br/nfe' versao='1.00'><idLote>ALTERAR_ID_LOTE</idLote><evento versao = '1.00'><infEvento Id='ALTERAR_ID_EVENTO'><cOrgao>41</cOrgao><tpAmb>2</tpAmb><CNPJ>ALTERAR_CNPJ</CNPJ><chNFe>ALTERAR_CHAVE_NFE</chNFe><dhEvento>ALTERAR_DATA_HORA_EVENTO</dhEvento><tpEvento>110110</tpEvento><nSeqEvento>ALTERAR_SEQUENCIA</nSeqEvento><verEvento>1.00</verEvento><detEvento versao='1.00'><descEvento>Carta de Correcao</descEvento><xCorrecao>ALTERAR_XCORRECAO</xCorrecao><xCondUso>A Carta de Correcao e disciplinada pelo paragrafo 1o-A do art. 7o do Convenio S/N, de 15 de dezembro de 1970 e pode ser utilizada para regularizacao de erro ocorrido na emissao de documento fiscal, desde que o erro nao esteja relacionado com: I - as variaveis que determinam o valor do imposto tais como: base de calculo, aliquota, diferenca de preco, quantidade, valor da operacao ou da prestacao; II - a correcao de dados cadastrais que implique mudanca do remetente ou do destinatario; III - a data de emissao ou de saida.</xCondUso></detEvento></infEvento></evento></envEvento></nfeDadosMsg>"
            }
        ''')
    
    
    if 'NfeInutilizacao' in dados :
        arquivo.append('''
            "inutilizacao":{
                "url"           : "''' + dados['NfeInutilizacao'] + '''",
                "namespace"     : "http://www.portalfiscal.inf.br/nfe/wsdl/NFeInutilizacao4",
                "servico"       : "",
                "metodo"        : "",
                "versao"        : "4.00",
                "schema"        : "inutNFe_v4.00.xsd",
                "tag_cabecalho" : "<nfeCabecMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeInutilizacao4'></nfeCabecMsg>",
                "tag_corpo"     : "<nfeDadosMsg xmlns='http://www.portalfiscal.inf.br/nfe/wsdl/NFeInutilizacao4'><inutNFe xmlns = 'http://www.portalfiscal.inf.br/nfe' versao='4.00'><infInut Id='ALTERAR_ID'><tpAmb>2</tpAmb><xServ>INUTILIZAR</xServ><cUF>ALTERAR_CUF</cUF><ano>ALTERAR_ANO</ano><CNPJ>ALTERAR_CNPJ</CNPJ><mod>55</mod><serie>ALTERAR_SERIE</serie><nNFIni>ALTERAR_NF_INI</nNFIni><nNFFin>ALTERAR_NF_FIN</nNFFin><xJust>ALTERAR_JUSTIFICATIVA</xJust></infInut></inutNFe></nfeDadosMsg>"
            }
        ''')
    
    arquivo.append('''
        "append_protocolo":{
            "tag_inicio" : "<?xml version='1.0' encoding='utf-8'?><nfeProc versao='4.00' xmlns='http://www.portalfiscal.inf.br/nfe'>",
            "tag_final"  : "</nfeProc>",
            "versao"     : "4.00"
        }
    ''')
    
    
    arquivo.append('''
        "append_evento":{
            "tag_inicio"           : "<?xml version='1.0' encoding='utf-8'?><procEventoNFe versao='1.00' xmlns='http://www.portalfiscal.inf.br/nfe'>",
            "tag_fim"              : "</procEventoNFe>",
            "tag_retEvento_inicio" : "<retEvento xmlns='http://www.portalfiscal.inf.br/nfe' versao='1.00'>",
            "tag_retEvento_fim"    : "</retEvento>"
        }
    ''')
    
    arquivo = ', '.join(arquivo)
    
    return '''
    {
        "4.00": {
            ''' + arquivo + '''
        }
    } '''
    
    
    
    
    
    
    
def createFolder(folder):
    if not os.path.isdir(basePath + folder):
        os.makedirs(basePath + folder)

















with open("/var/www/html/nf/NF_V3/NF_V3/servicos/nfe/urls/urls.json") as urls : 
    jsonUrls = json.load(urls)




def abrirArquivo(arq):
    with open(arq) as fj : 
        jsonEstado = json.load(fj)
    return jsonEstado



for estado in jsonUrls["urls"]:
    atualPath = outputPath + estado
    if os.path.isdir(atualPath):
        if os.path.isfile(atualPath + '/homologacao.json'):
            #print ('OK ' + estado + '/homologacao.json')
            pprint(abrirArquivo(atualPath + '/homologacao.json'))
        else:
            print ('~~ ' + estado + '/homologacao.json')
            
        if os.path.isfile(atualPath + '/producao.json'):
            #print ('OK ' + estado + '/producao.json')
            pprint(abrirArquivo(atualPath + '/producao.json'))
        else:
            print ('~~ ' + estado + '/producao.json')
    else:
        print ('~~' + estado)
        



'''
for estado in jsonUrls["urls"]:
    
    createFolder(estado)
    
    with open(basePath + estado + '/homologacao.json', 'w') as arquivo:
        arquivo.write(
            createFile(jsonUrls["urls"][estado]['homologacao'])
        )
    
    with open(basePath + estado + '/producao.json', 'w') as arquivo:
        arquivo.write(
            createFile(jsonUrls["urls"][estado]['producao'])
        )
    
'''













