
import json , argparse, re, time

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
        self.set_vars(args)
        self.parse_xml()
        self.get_xml_definition()
        self.read_map()
        ini = time.time()
        self.do_convertion()
        print(time.time() - ini )
        self.append_response()
        self.save()
        
        
    
    def set_vars(self, args) :
        
        valid = lambda file, ext : re.findall(r'\.'+ re.escape(ext) +r'$'.format(ext),file)
        self.debug      = args.debug
        self.file_in    = args.entrada if valid(args.entrada ,'xml') else False
        self.file_out   = args.saida if  valid(args.saida   ,'txt') else False
        self.file_key   = args.type
        
        if not self.file_in or not self.file_out :
            exit('Um ou mais arquivos esta com extensao errada. Para entrada formato .xml para saida formato .txt')
            
        
    def parse_xml(self) :
        import xml.etree.ElementTree as ET
        from xmljson import BadgerFish 
        from xml.etree.ElementTree import fromstring
        
        success ,temp_xml = Util.read_file(path=self.file_in)
        temp_xml = re.sub(r"\S+\s?=\s?(\"|\')https?\S+(\"|\')","",temp_xml)
        temp_xml = temp_xml.replace("|","/")
        bf_str = BadgerFish(xml_fromstring=False) 
        self.xml = bf_str.data(fromstring(temp_xml))
        
        
    
    def get_xml_definition(self) :
        
        chaves_conhecidas   = ['NFe','MDFe','CTe']
        primeira_chave      = list(self.xml.keys())[0]
        source              = self.xml.get(primeira_chave, {})
        
        self.file_key       = self.file_key if self.file_key else list(source.keys())[1]
        self.file_version   = source.get('@versao')
        
        # try to get key if it not exists
        chave_passivel = list(filter(lambda chave: list(filter(lambda chave_arq: chave in chave_arq, source.keys())) , chaves_conhecidas))
        if self.file_key not in chaves_conhecidas and chave_passivel:
            self.file_key = chave_passivel[0]
            
        self.response   = source.get(f'prot{self.file_key}',{})
        self.source = self.find(source, self.file_key)
        
    
    def read_map(self) :
        
        path = "/var/www/html/nf/NF_V3/NF_V3/templates/"
        if self.debug :
            path = ".lixo/"
            
        success, JSON_FILE = Util.read_file(path=f"{path}{self.file_key.lower()}2xml.json")
        
        
        JSON_FILE = json.loads(JSON_FILE)
        
        json_from_version = JSON_FILE.get(self.file_version, {})
        
        # self.map = JSON_FILE["4.00"]['mapa']
        self.map = json_from_version.get('mapa',{})
        self.layout_response = json_from_version.get('protocolo', {})
        #self.layout_response = JSON_FILE["4.00"]['protocolo']
    
    
    def do_convertion(self) :
        
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
                
            data[item], parent = self.read_paths(item)
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
                        validate = self.only_valid_values(children)
                        
                    
                    if validate or (children_len == 1 and data[item][0][index] ) :
                        group_data[group_name].append( "{item}|{data}|".format(item=item, data= "|".join(map(str,children)) or data[item][0][index] ) )
                        
                    
                # Guarda os dados no
                looping_group[group_count]['data'] = group_data
                
            else :
                
                if self.only_valid_values(data[item]) :
                    row_data .append ( "{item}|{data}|".format( item = item, data =  "|".join(map(str,data[item]))))
                    #if not is_first : 
                    start += 1 
                    #    iterations_len = 0
                continue
            
        # Percorre o grupo para posicionar os dados em seus respectivos lugares
        for i in looping_group :
            sorted_group_data = self._sorted(looping_group[i]['data'] ) #sorted(looping_group[i].items(), key=lambda kv: int(kv[0]))
            for group_item in sorted_group_data[::-1]:
                row_data.insert(looping_group[i]['start'] , "\n".join(  group_item[1]   ) )
                
            
        
        row_data.insert(0,"{name}|001|\nA|{version}|{key}|".format(name= self.get_name(),version=self.file_version, key=self.file_key))
            
        self.write_data = row_data 
        
    def get_name(self):
        return list(self.map.items())[0][0]
        """ for key in self.map :
            if list(re.match(r'^\D{3,}$',)) :
                return key """
            
        
    
    def append_response(self) :
        
        data = "PROT|{}|".format(
            "|".join([ self.get_value(path[1:], self.response ) for path in self.layout_response ])
        )
        self.write_data.append(data)
        
    
    def save(self) :
        data = "\n".join( self.write_data  )
        Util.write_file(data=data, path=self.file_out)
        
    
    
    
    def _sorted(self, _object) :
        return sorted(_object.items(), key=lambda kv: int(kv[0]))
    
    def only_valid_values(self, values):
        return [value for value in values if value or value == 0]  
        
    
    def read_paths(self, item) :
        """ Le o caminho e recupera o valor"""
        data    = self.map[item][1:]
        results = list()
        parent  = False
        
        for path in data:
            
            path = "{path}".format(path= path[1:] if "/" in path else path )
            parent = self.get_parent_path(path)
            
            if parent :
                child = list()
                
                for index,value in enumerate(parent['value']) :
                    childPath = "{parentPath}/{index}{rest}".format(
                            parentPath  = parent['path']
                        ,   index       = index 
                        ,   rest        = path.replace(parent['path'], ""))
                    
                    child.append(self.get_value( childPath, self.source ))
                results.append(child)
            else :
                results.append(self.get_value( path, self.source ))
            
        return results, parent
            
        
    
    def get_value(self, path, json) :
        
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
                    
            
        
    
    
    def get_parent_path(self, path):
        
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
        
    
    
    """ def writeFile(self, data="", file="", flag = "w+") :
        with open(file, flag, encoding='utf8') as log :
            log.write(data) 
            
        
    
    def readFile(self, path) :
        try:
            with open(path , 'r', encoding='utf8') as file:
                return file.read() 
        except IOError as error:
            print(error)
            exit() """
        
        
class Util:
    @staticmethod
    def read_file(path ="",flag = "r", encoding='utf8') :
        return Util.__handle_file(handle="read", path =path,flag = flag, encoding=encoding)
        
    
    @staticmethod
    def write_file(data="", path="", flag = "w+", encoding='utf8') :
        return Util.__handle_file(handle = "write",data=data, path =path,flag = flag, encoding=encoding)
        
    
    @classmethod
    def __handle_file(cls, **kwargs) :
        try:
            with open(kwargs['path'] , kwargs['flag'], encoding=kwargs['encoding']) as file:
                if kwargs['handle'] == 'read':
                    return True, file.read() 
                elif kwargs['handle'] == 'write':
                    return True, file.write(kwargs['data'])
                else:
                    pass
                    
        except IOError as error:
            return None, error
        
    
    @staticmethod
    def md5(text) :
        import hashlib

        md5 = hashlib.md5(text.encode('utf-8'))
        return md5.hexdigest()


class JsonByPath():

    def __init__(self, **data):

        self.path = re.sub(
            r'[\s\t\r\n](?=(\`[^\`]*\`|[^\`])*$)', "", data['path']
        )
        self.jsonIn = data['json']
        self.value = list()
        self.error_path = list()
        
        self.process()
        
        self.current_path = ''
        # print(self.value)

    def __repr__(self):
        return self.value
        
        
    
    
    def process(self):
        #self.all = self.processPath(self.path, "||")
        #_filter = self.onlyValidValues(self.all)
        
        self.all , _filter = self.processByLogic(self.path, "||")
        # To return only first value valid in ||
        self.value = _filter[0] if _filter else ''
        """ if _filter:
            self.value = 
        else:
            self.value = '' """
        
        
    def processByLogic(self,path, operator) :
        paths_processed = self.processPath(path, operator)
        filter_done     = self.onlyValidValues(paths_processed)
        
        return paths_processed, filter_done
        
    
    def processPath(self, path, separator):
        paths = path.split(separator)
        return list(map(self.execute, paths))

    def onlyValidValues(self, values):
        """ 
            Metodo para retornar valores verdadeiros
        """
        #flt = list(filter(lambda value: value is not None and value != 0, values))
        return [value for value in values if value or value == 0]  
        
    
    def execute(self, path):
        # save path to show log.
        self.current_path = path
        value = ''
        try:
            if "&&" in path:
                #plus = self.processPath(path, "&&")
                #_filter = self.onlyValidValues(plus)
                plus, _filter = self.processByLogic(path, "&&")
                
                value = "".join(map(str, _filter))
            else:
                value = self.goThroughPath(path)

        except ValueError as error:
            print("error: " + error)
        return value

    def goThroughPath(self, path):

        has_quote = re.findall(r'\`(.*?)\`', path)
        if not "/" in path and len(has_quote):
            return has_quote[0]

        path_splited = path.split("/")
        current_value = self.tryPath(self.jsonIn, path_splited[0])

        # if  not isinstance(current_value, dict) and not isinstance(current_value, list):
        #    current_value = current_value

        # else:
        return self.getValue(path_splited, current_value)

        # return current_value

    def isnumber(self, value):
        try:
            float(value)
        except ValueError:
            return False
        return True

    def getValue(self, path_splited, initial_value):

        value = initial_value
        for index, key in enumerate(path_splited[1:]):
            try:
                key = int(key)

            except:
                pass

            if ( key != '' and not isinstance(key, int) and ("*" in key[0] or ("[" in key and "]" in key))) and index != len(path_splited):

                _charToJoin = re.findall(r"\`(.*?)\`", key)
                _charToJoin = _charToJoin[0] if _charToJoin else " "

                key1 = None
                key2 = None

                keyS = re.findall(r'\[(.*?)\]', key)
                if keyS:
                    keyS = keyS[0].split(":")
                    key1, key2 = self.tryInt(keyS[0]), self.tryInt(keyS[1])

                value = self.onlyValidValues(
                    [self.getValue(path_splited[index + 1:], val) for val in value])
                return _charToJoin.join(map(str, value[key1:  key2]))

            else:
                value = self.tryPath(value, key)

        return value

    def tryInt(self, value):
        try:
            return int(value)
        except:
            return None

    def tryPath(self, arr, key):
        try:
            return arr[key]
        except:

            self.error_path.append(f"PATH:{self.current_path} - KEY: {key or None}")

            """print(
            ###### path pode estar errado #######
                PATH : {path}
                KEY  : {key}
            .format(path=self.current_path, key=key))"""

            return ''
    
    

if __name__ == "__main__":
    xml2txt(parser.parse_args())
