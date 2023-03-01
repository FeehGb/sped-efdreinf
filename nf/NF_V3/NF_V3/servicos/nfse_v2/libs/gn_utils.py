def read_file(path ="",flag = "r", encoding='utf8', _exit=True) :
    return handle_file(handle="read", **locals())
    
def write_file( data="", path="", flag = "w+", encoding='utf8', _exit=True) :
    return handle_file(handle = "write", **locals())
    
def open_file( data="", path="", flag = "r", encoding='utf8', _exit=True) :
    return handle_file(handle = "open", **locals())
    
    
def get_index(arr:list, index:int) :
    try:
        return arr[index]
    except :
        return None
        
    
def deepMerge(d1, d2):
    def merge(dict1, dict2):
        for k in set(dict1.keys()).union(dict2.keys()):
            if k in dict1 and k in dict2:
                if isinstance(dict1[k], dict) and isinstance(dict2[k], dict):
                    yield (k, dict(deepMerge(dict1[k], dict2[k])))
                else:
                    # If one of the values is not a dict, you can't continue merging it.
                    # Value from second dict overrides one in first and we move on.
                    yield (k, dict2[k])
                    # Alternatively, replace this with exception raiser to alert you of value conflicts
            elif k in dict1:
                yield (k, dict1[k])
            else:
                yield (k, dict2[k])
    return dict(merge(d1,d2))
    
        
def handle_file(**kwargs) :
    
    try:
        with open(kwargs['path'] , kwargs['flag'], encoding=kwargs['encoding']) as file:
            if kwargs['handle'] == 'read':
                return file.read() 
            elif kwargs['handle'] == 'write':
                return file.write(kwargs['data'])
            else:
                return file
                
    except IOError as error:
        _exit = kwargs.get('_exit')
        if exit:
            print(error)
            exit()
        else:
            return False
    
def eprint(toPrint) :
    print(toPrint);exit()
    

def translate(value, _from, _to, ) :
    
    #_from       = "ÀÁÂÃÄÅÆàáâãäåæÇçÈÉÊËèéêëÌÍÎÏìíîïÑñÒÓÔÕÖØòóôõöøÙÚÛÜùúûüÝÝŸýýÿ \n"
    #_to         = "AAAAAAAaaaaaaaCcEEEEeeeeIIIIiiiiNnOOOOOOooooooUUUUuuuuYYYyyy_\0"
    translation =  value.maketrans(_from, _to)
    return value.translate(translation)
    

def multi_replace(value, replacements) :
    for replacement in replacements :
        _from, _to =  replacement
        value = value.replace(_from,_to)
    
    return value
    


def dict2xml(d, root_node=None):
    wrap          =  False if None == root_node or isinstance(d, list) else True
    root          = 'objects' if None == root_node else root_node
    root_singular = root[:-1] if 's' == root[-1] and None == root_node else root
    xml           = ''
    attr          = ''
    children      = []

    if isinstance(d, dict):
        # print(d)
        for key, value in dict.items(d):
            if isinstance(value, dict):
                children.append(dict2xml(value, key))
            elif isinstance(value, list):
                children.append(dict2xml(value, key))
            elif key[0] == '$':
                children.append(value)
            elif key[0] == '@':
                attr = attr + ' ' + key[1::] + '="' + str(value) + '"'
            else:
                xml = '<' + key + ">" + str(value) + '</' + key + '>' 
                children.append(xml)

    else:
        #if list
        for value in d:
            children.append(dict2xml(value, root_singular))

    end_tag = '>' if 0 < len(children) else '/>'
    
    if wrap or isinstance(d, dict):
        xml = '<' + root + attr + end_tag

    if 0 < len(children):
        for child in children:
            xml = xml + child

        if wrap or isinstance(d, dict):
            xml = xml + '</' + root + '>'

    return xml
    
    
    
    
    