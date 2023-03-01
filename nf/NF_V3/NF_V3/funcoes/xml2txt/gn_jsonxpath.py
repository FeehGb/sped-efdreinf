import re, json
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
    print("Go to the jsonByPath.test.py")
    pass
    