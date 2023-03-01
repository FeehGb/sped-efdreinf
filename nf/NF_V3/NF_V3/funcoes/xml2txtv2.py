
import json , argparse, re



parser = argparse.ArgumentParser(description = 'Programa para tranformar dados do xml para o txt - parao COBOL')
parser.add_argument('--entrada' , action = 'store', dest = 'entrada', required = True, help = 'Caminho do arquivo de entrada no formato .xml'   )
parser.add_argument('--saida'   , action = 'store', dest = 'saida'  , required = True, help = 'Caminho do arquivo de saida no format .txt'      )




class xml2txt:
    def __init__(self, args) :
        self.args = args
        
        self.bootstrap()
        
    def bootstrap(self) :
        self.validFiles()
        
        
    def validFiles(self) :
        
        valid = lambda file, ext : re.match(r'/\.'+ re.escape(ext) +r'$/'.format(ext),file)
        
        self.file_in = valid(self.args.entrada, 'xml')
        print(self.args.entrada)
        print(self.file_in)
        
    
    
    
if __name__ == "__main__":
    xml2txt(parser.parse_args())

