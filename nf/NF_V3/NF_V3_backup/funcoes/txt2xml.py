#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
# python /var/www/html/nf/NF_V3/NF_V3/funcoes/txt2xml.py /user/nfe/05540409000114/CaixaEntrada/Processar/NFE-000059216-NE-032191-20180220-100959.TXT /var/www/html/nf/NF_V3/NF_V3_dados/temp/nfe/NFE-000059216-NE-032191-20180215-141446.TXT.xml /var/www/html/nf/NF_V3/NF_V3/templates/nfe2xml.json 4.00 'NOTAFISCAL|A|@|ZX02'
import json
import sys
from pprint import pprint
from collections import OrderedDict
import txt2xml_logs



'''
    Classe para conversao de TXT para xml
'''
class class_txt2xml:
    def __init__(self):
        self.arLinhasIgnoradas   = [] # Linhas que deverao ser ignoradas, que nao aparacerao no XML
        self.arSempreRepetir     = [] # Linhas que sempre vao repetir
        self.arTagsAbertas       = [] # controle de quais tags estao abertas para fechar
        self.arLinhasProcessadas = [] # controle de linhas do txt do cobol, para saber quando acontece uma repeticao
        #path atual e anterior sao usados para controle de qualis tags devem ser fechadas
        self.pathAtual           = ""
        self.pathAnterior        = ""
        # XML final
        self.xmlFinal            = ""
        
        
    '''
        carrega o mapa
    '''
    def loadMap(self):
        self.mapa = json.load(open(self.pathMapa), object_pairs_hook=OrderedDict)
        self.mapa = self.mapa[self.versao]['mapa']
        
        
    '''
        Processa o TXT
    '''
    def process(self):
        # fleg que informa se houve ou nao um erro
        deuErro = False
        # log de erros
        log = ''
        
        numeroLinha = 0
        # carrega o mapa
        self.loadMap()
        # Abre o txt do cobol
        with open(self.txtCobolPath) as f:
            txtCobol = f.readlines()
        # Percorre as linhas do txt do cobol
        for linha in txtCobol:
            numeroLinha += 1 
            # separa os daodos da linha e limpa os dados 
            linhaDados = [x.strip() for x in linha.split('|')]
            # Nome da linha vem na posicao zero
            nomeLinha = linhaDados[0]
            # se a linha nao estiver em branco e se nao for uma linha ignorada
            if linha != "\n" and nomeLinha not in self.arLinhasIgnoradas:
                # verifica se a linha passada pelo cobol existe no mapa
                if nomeLinha not in self.mapa:
                    deuErro = True
                    # gera um log de erro
                    txt2xml_logs.addLog(nomeLinha, len(linhaDados)-1, ' X ', "", numeroLinha, "Mapa sem ln")
                # verrifica a integridade do txt, se a qyantidade de dados da linha for a mesma esperada no mapa
                elif len(linhaDados) - 1 != len(self.mapa[nomeLinha]):
                    # informa que houve um erro
                    deuErro = True
                    # gera um log de erro
                    txt2xml_logs.addLog(nomeLinha, len(linhaDados)-1, len(self.mapa[nomeLinha]), "", numeroLinha, "ln cob <> mp")
                # se nao houve erro 
                else:
                    # Processa a linha
                    self.processLinha(nomeLinha, linhaDados)
                    
            
        # se deu erro
        if deuErro:
            # Dispara um erro
            self.log(log)
        
        # Apos processar todas as linhas, fecha as ultimas linhas que faltam 
        self.fecharTagsFinal()
        
    
    '''
        Limpa os espacos do XML
    '''
    def limpar(self,txt):
        # enquanto tivrer 2 espacos juntos
        while "  " in txt:
            # Troca dois espacos por um espaco
            txt = txt.replace("  ", ' ')
        # Retorna o texto limpo
        return txt
        
        
        
    '''
        Processa a linha
    '''
    def processLinha(self, linha, dadosLinhaTxt):
        # Se a linha nao estiver no array de linhas processadas
        linhaSimplificada = linha
        #pprint(linhaSimplificada)
        if linhaSimplificada not in self.arLinhasProcessadas:
            # add a linha no array, para controle de repeticao
            self.arLinhasProcessadas.append(linhaSimplificada)
        # Se a linha ja estiver no array de linhas processadas
        elif len(linhaSimplificada) == 1 or linha[0] in self.arSempreRepetir :
            # prepara o xml para a repeticao
            self.repetir(linhaSimplificada)
        # percorre os dados da linha 
        for index, dadoMapa in enumerate(self.mapa[linha]):
            # O indice Zero traz o nome da linha, e se o dado nao for vazio
            if index > 0 and dadosLinhaTxt[index] != '':
                # coloca no XML a informacao
                self.putOnXML(dadoMapa, dadosLinhaTxt[index])
                
            
    
    '''
        Coloca o dado no XML
    '''
    def putOnXML(self, path, dado):
        # quebra o path para trabalhar em niveis
        paths = path.split('/')
        #contagem de nivel para saber qual o ultimo e colocar o dado
        cNiveis = len(paths)
        # path atual eh para saber quais tags ainda estao abertas
        self.pathAtual = path
        # fecha as tags do dado anterior 
        self.fecharTags()
        
        for index, fPath in enumerate(paths):
            # index Zero traz o nome da linha
            if index > 0:
                # Se tiver atributo
                if '@' in path and index == cNiveis - 2:
                    # pega o ultimo path, remove a arroba e monta o atributo
                    atributo = ' ' + paths[-1].replace('@', '') + "=\"" + dado + "\""
                else:
                    atributo = ''
                    
                #se nao tiver atributo
                if '@' not in fPath:
                    # se for o ultimo nivel, eh o nome do dado
                    if index == cNiveis - 1:
                        # Coloca o dado no xml
                        self.addOnXml(
                            "<" + str(fPath) + atributo + ">" +
                            dado + 
                            "</" + str(fPath) + ">"
                        )
                    
                    # Cria a tag se ela nao estiver aberta
                    elif fPath not in self.arTagsAbertas:
                        #abre a tag
                        self.addOnXml("<" + str(fPath) + atributo + ">")
                        #add a tag no array de tags abertas
                        self.arTagsAbertas.append(fPath)
                    
        # path antenrir eh usado para fechar as tags
        self.pathAnterior = self.pathAtual
        
        
    '''
        fecha as tags apos o fim do processamento
    '''
    def fecharTagsFinal(self):
        # enmquanto tiver tags
        while len(self.arTagsAbertas)>0 :
            # fecha a ultima tah do array de tags abertass
            self.addOnXml("</" + self.arTagsAbertas.pop() + '>')
        
        
    '''
        Fecha as tags
    '''
    def fecharTags(self):
        # para primeira execucao desse metodo, o anterior vem vazio
        if self.pathAnterior == "" or len(self.arTagsAbertas) == 0:
            # nao precisa fechar nada
            return False
        
        # ultima tag aberta
        last  = self.arTagsAbertas[-1]
        
        # add barras para o "IN" do python funcionar melhor
        lastT = '/' + last + '/'
        self.pathAtual    += '/'
        self.pathAnterior += '/'
        
        # Se a ulmtima tag tiver no anterior e naoi apartecer no atual, significa que deve ser fechada
        if lastT in self.pathAnterior and lastT not in self.pathAtual:
            # fecha a tag
            self.addOnXml('</' + last + '>')
            # remove das tags abertas
            self.arTagsAbertas.pop()
            # recursividade para fechar todas as que precisa
            self.fecharTags()
        # de nao precisa mais fechar nada
        else:
            # sai da recursividade
            return True
    
    
    '''
        prepara o xml para a repeticao
    '''
    def repetir(self, linha):
        # Se a linha tem dados ale do nome da linha
        if len(self.mapa[linha]) > 1:
            # tag que deve ser repetida
            repet = self.mapa[linha][1].split('/')[-2]
            # enquanto a tag que deve ser repetida estiver apberta
            while repet in self.arTagsAbertas:
                # fecha a tag
                self.addOnXml("</" + self.arTagsAbertas.pop() + '>')
                
            
        
    
    '''
        Set arquivo TXT do cobol
    '''
    def set_txtCobol(self, path):
        # seta o path do txt do cobol na classe
        self.txtCobolPath = path
    
    
    '''
        Set arquivo XML de saida
    '''
    def set_xmlSaida(self, path):
        # seta o path do txt do cobol na classe
        self.pathXmlSaida = path
    
    
    '''
        Set arquivo de mapa
    '''
    def set_pathMapa(self, path):
        # seta o path do txt do cobol na classe
        self.pathMapa = path
    
    
    '''
        seta as tags ignoradas
    '''
    def set_linhasIgnoradas(self, ar):
        # seta o path do txt do cobol na classe
        self.arLinhasIgnoradas = ar.split('|')
    
    
    '''
        seta as tags que sempre vao repetir
    '''
    def set_sempreRepetir(self, ar):
        # seta o path do txt do cobol na classe
        self.arSempreRepetir = ar.split('|')
    
    
    '''
        seta a versao da nota
    '''
    def set_versao(self, v):
        # seta o path do txt do cobol na classe
        self.versao = v
    
    
    def ultimaTentativa(self, xml):
        letras = list(xml)
        
        for letra in letras:
            try:
                self.xmlFinal+=letra
            except:
                self.xmlFinal+=' '
                
        
        
    '''
        adiciona dados no XML
    '''
    def addOnXml(self, xml):
        # adiciona dados no XML
        try:
            self.xmlFinal += xml
        except:
            self.ultimaTentativa(xml)
    
    
    '''
        Salva o xml
    '''
    def save(self):
        # Salva o xml 
        
        self.xmlFinal = self.limpar(self.xmlFinal)
        
        with open(self.pathXmlSaida, "w") as f:
            f.write(self.xmlFinal)
    
    
    def log(self, msg):
        txt2xml_logs.log(msg, self.txtCobolPath)
        
    
if __name__ == "__main__":
    
    notaFiscal = class_txt2xml()
    
    notaFiscal.set_txtCobol        ( sys.argv[1] ) # 
    notaFiscal.set_xmlSaida        ( sys.argv[2] ) # 
    notaFiscal.set_pathMapa        ( sys.argv[3] ) # 
    notaFiscal.set_versao          ( sys.argv[4] ) # 
    notaFiscal.set_linhasIgnoradas ( sys.argv[5] ) # 
    notaFiscal.set_sempreRepetir   ( sys.argv[6] ) # 
    
    notaFiscal.process()
    
    notaFiscal.save()
    
    
    
    
    
    
    