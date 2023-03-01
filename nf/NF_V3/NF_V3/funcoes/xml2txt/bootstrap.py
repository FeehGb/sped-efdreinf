
import json , argparse, re, time
from gn_jsonxpath import JsonByPath
from collections import OrderedDict

__author__      = 'Felipe Basilio'
__email__       = 'felipe@softdib.com.br'
__version__     = '0.1.0'
__description__ = 'Programa transformar arquivos xml no arquivo TXT formatado para o cobol'

parser = argparse.ArgumentParser(description = 'Programa para tranformar dados do xml para o txt - para o COBOL')
parser.add_argument('--entrada' , action = 'store', dest = 'entrada', required = True, help = 'Caminho do arquivo de entrada no formato .xml'   )
parser.add_argument('--saida'   , action = 'store', dest = 'saida'  , required = True, help = 'Caminho do arquivo de saida no format .txt'      )
parser.add_argument('--type'    , action = 'store', dest = 'type'   , required = True, help = 'type do formato do arquivo .txt'      )
parser.add_argument('--debug'   , action = 'store_true', dest = 'debug'  , default= False, help = 'indica se o modo debug esta ativado'      )
    

class xml2txt:
    def __init__(self, args) :
        self.bootstrap(args)
        
        
    
    def bootstrap(self, args) :
        self.setVars(args)
        self.parseXml()
        self.getXmlDefinition()
        self.readMap()
        ini = time.time()
        self.doConvertion()
        print(time.time() - ini )
        self.append_response()
        self.save()
        
        
    
    def setVars(self, args) :
        
        valid = lambda file, ext : re.findall(r'\.'+ re.escape(ext) +r'$'.format(ext),file)
        self.debug      = args.debug
        self.file_in    = args.entrada if valid(args.entrada ,'xml') else False
        self.file_out   = args.saida if  valid(args.saida   ,'txt') else False
        self.file_key   = args.type
        
        if not self.file_in or not self.file_out :
            exit('Um ou mais arquivos esta com extensao errada. Para entrada formato .xml para saida formato .txt')
            
        
    def parseXml(self) :
        import xml.etree.ElementTree as ET
        from xmljson import BadgerFish 
        from xml.etree.ElementTree import fromstring
        
        temp_xml = self.readFile(self.file_in)
        temp_xml = re.sub(r"\S+\s?=\s?(\"|\')https?\S+(\"|\')","",temp_xml)
        temp_xml = temp_xml.replace("|","/")
        bf_str = BadgerFish(xml_fromstring=False) 
        self.xml = bf_str.data(fromstring(temp_xml))
        
    
    def getXmlDefinition(self) :
        
        key     = list(self.xml.keys())[0]
        source  = self.xml[key]
        
        # try to get key if it not exists
        
        self.file_key   = self.file_key if self.file_key != "" else list(source.keys())[1]
        try:
            self.response   = source['prot{}'.format(self.file_key)]
        except :
            exit('impossivel recuperar o tipo do arquivo, tente passar por linha de argumento')
            
        
        self.file_version   = source['@versao']
        self.source = self.find(self.xml[key], self.file_key)
        
    
    def readMap(self) :
        
        path = "/var/www/html/nf/NF_V3/NF_V3/templates/"
        if self.debug :
            path = ".lixo/"
            
        
        JSON_FILE = self.readFile("{path}{key}2xml.json".format(
                key     = self.file_key.lower()
            ,   path    = path
            )
        )
        
        JSON_FILE = json.loads(JSON_FILE)
        self.map = JSON_FILE[self.file_version]['mapa']
        self.layout_response = JSON_FILE[self.file_version]['protocolo']
        
    
    
    def doConvertion(self) :
        
        row_data             = list()
        data                 = dict()
        looping_group        = dict()
        is_first             = False
        last_parent_path     = None
        iterations_len       = 0
        last_iteration_count = 0
        start                = 0
        group_count          = 0
        
        for item in self.map :
            #Ignora item com chaves com @
            if item in ['@'] :
                continue
                
            data[item], parent = self.readPaths(item)
            # Se não existir nenhum dado vai para o proximo
            if not data[item] :
                continue
            
            if  isinstance(data[item][0], list) :
                # Se for o primeiro item como lista é o inicio dos lacos de repeticoes , pega as definiçoes e marca a posicao ao qual fara o insert
                iterations_len =  len(data[item][0]) 
                is_first   = False
                
                if parent['path'] != last_parent_path:
                    # Reinicizaliza as variaveis para o novo grupo de dados
                    is_first                  =  True
                    group_data                = dict()
                    # Guarda o nome do caminho atual para
                    last_parent_path          = parent['path']
                    #
                    group_count              += 1
                    # inicia um novo grupo de dados que terao iteracoes
                    looping_group[group_count]  = dict()
                    
                    start = start if group_count == 1 else  start + last_iteration_count
                    
                    # Guarda a quantidade de iterecoes para posicionar depois no indice correto
                    last_iteration_count = iterations_len
                    
                # Criar a varival de posicionamento do grupo
                looping_group[group_count]['start'] = start
                for index in range(0, iterations_len) :
                    
                    group_name = "{}".format(index)
                    if group_name not in group_data:
                        group_data[group_name] = list()
                        
                    children = list()
                    children_len = len(data[item])
                    validate = False
                    
                    if children_len > 1 :
                        children = [ data[item][i][index] for i in range(0,children_len)]
                        validate = self.onlyValidValues(children)
                        
                    
                    if validate or (children_len == 1 and data[item][0][index] ) :
                        group_data[group_name].append( "{item}|{data}|".format(item=item, data= "|".join(map(str,children)) or data[item][0][index] ) )
                        
                    
                # Guarda os dados no
                looping_group[group_count]['data'] = group_data
                
            else :
                
                if self.onlyValidValues(data[item]) :
                    row_data .append ( "{item}|{data}|".format( item = item, data =  "|".join(map(str,data[item]))))
                    if not is_first : 
                        start += 1 
                        iterations_len = 0
                continue
            
        # Percorre o grupo para posicionar os dados em seus respectivos lugares
        for i in looping_group :
            sorted_group_data = self._sorted(looping_group[i]['data'] ) #sorted(looping_group[i].items(), key=lambda kv: int(kv[0]))
            for group_item in sorted_group_data[::-1]:
                row_data.insert(looping_group[i]['start'] , "\n".join(  group_item[1]   ) )
                
            
        
        row_data.insert(0,"""{name}|001|\nA|{version}|{key}|""".format(name= self.get_name(),version=self.file_version, key=self.file_key))
            
        self.write_data = row_data 
        
    def get_name(self):
        return list(self.map.items())[0][0]
        """ for key in self.map :
            if list(re.match(r'^\D{3,}$',)) :
                return key """
            
        
    
    def append_response(self) :
        
        data = "PROT|{}|".format(
            "|".join([ self.getValue(path[1:], self.response ) for path in self.layout_response ])
        )
        self.write_data.append(data)
        
    
    def save(self) :
        data = "\n".join( self.write_data  )
        self.writeFile(data , self.file_out)
        
    
    
    
    def _sorted(self, _object) :
        return sorted(_object.items(), key=lambda kv: int(kv[0]))
        
    
    def onlyValidValues(self, values):
        return [value for value in values if value or value == 0]  
        
    
    def readPaths(self, item) :
        """ Le o caminho e recupera o valor"""
        data    = self.map[item][1:]
        results = list()
        parent  = False
        
        for path in data:
            
            path = "{path}".format(path= path[1:] if "/" in path else path )
            parent = self.getParentPath(path)
            
            if parent :
                child = list()
                
                for index,value in enumerate(parent['value']) :
                    childPath = "{parentPath}/{index}{rest}".format(
                            parentPath  = parent['path']
                        ,   index       = index 
                        ,   rest        = path.replace(parent['path'], ""))
                    
                    child.append(self.getValue( childPath, self.source ))
                results.append(child)
            else :
                results.append(self.getValue( path, self.source ))
            
        return results, parent
            
        
    
    def getValue(self, path, json) :
        
        result = JsonByPath(path=path, json=json)
        if result.value and isinstance(result.value, dict) and  "$" in result.value:
            return result.value['$']
            
        
        return result.value
        
    
    def find(self,json, key):
        
        if isinstance(json, dict): 
            for item in json:
                if item == key: 
                    return json[item]
                else :
                    deep = self.find(json[item], key)
                    if deep :
                        return deep
                    else:
                        continue
                    
            
        
    
    
    def getParentPath(self, path):
        
        parentPath   = re.findall(r'(.*)\/',path)
        parent      = False
        
        while parentPath :
            
            if parentPath :
                parent = JsonByPath(path=parentPath[0], json=self.source)
                if isinstance(parent.value, list) :
                    parent ={ "path":parentPath[0], "value":parent.value}
                    break
                else : 
                    parentPath = re.findall(r'(.*)\/',parentPath[0])
                    parent = False
                
        return parent
        
    
    
    def writeFile(self, data="", file="", flag = "w+") :
        with open(file, flag, encoding='utf8') as log :
            log.write(data) 
            
        
    
    def readFile(self, path) :
        try:
            with open(path , 'r', encoding='utf8') as file:
                return file.read() 
        except IOError as error:
            print(error)
            exit()
        
if __name__ == "__main__":
    xml2txt(parser.parse_args())
