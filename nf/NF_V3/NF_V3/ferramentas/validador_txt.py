
import sys
from pprint import pprint
sys.path.insert(0, "/var/www/html/nf/NF_V3/NF_V3/testes/nfe/")

import nfe_teste



if __name__ == "__main__":
    txt = sys.argv[1]
    
    artxt = txt.split('/')
    file = artxt.pop()
    path = '/'.join(artxt) + '/'
    
    pprint(nfe_teste.validarTXT(path, file))
    


