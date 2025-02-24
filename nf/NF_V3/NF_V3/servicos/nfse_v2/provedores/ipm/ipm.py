from libs               import gn_utils     as _utils
from nfse import nfse
from libs.gn_request import Request
from io import StringIO

# 202007042016874443 protocolo de atendimento 
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4101804 consultar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.txt /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4101804 recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# 

"""
    * SANTA CATARINA:

    - Rio do Sul - nao atendemos
    - Timbó - nao atendemos
    - Zortéa - nao atendemos
    - Luiz Alves - nao atendemos
    - Santa Helena - nao atendemos
    - Nova Erechim - nao atendemos
    --------------------
    * PARANÁ:

    - Cascavel  - nao atendemos
    - Guarapuava - nao atendemos
    - Pinhais - Em implementacao
    --------------------
    * MINAS GERAIS:

    - Campo Belo - nao atendemos
"""


class ipm(nfse) : # 4101804
    
    def __init__(self, params) :
        super(ipm, self).__init__(params, provider = 'ipm')
        
    
    def set_headers(self) :
        #from datetime import datetime
        
        #login  = self.file_data['nfse']['nf']['usuarioPrefeitura']['$']
        #senha  = self.file_data['nfse']['nf']['senhaPrefeitura']['$']
        #cidade = self.file_data['nfse']['prestador']['codTom']['$']
        
        arq = StringIO()
        arq.write(self.xml_send)
        arq.seek(0)

        arquivos = {
            'f1': arq,
        }
        
        self.request = Request(
            files = arquivos,
            data = {
                    "login" : self.usuarioprefeitura 
                ,   "senha" : self.senhaprefeitura
                ,   "cidade": self.codtom
            }
        ) 
        """ ,
        headers =   {
            'User-Agent':"Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1",
            'Content-Type': 'multipart/form-data'
        } """
        
        #print(self.request)
        
    
    @nfse.template(
        source = """
        <nfse id="nota">
            <nf>
                <numero>{numero}</numero>
                <situacao>C</situacao>
                <observacao>Cancelamento NFS-e</observacao>
            </nf>
            <prestador>
                <cpfcnpj>{cpfcnpj}</cpfcnpj>
                <cidade>{codtom}</cidade>
            </prestador>
        </nfse>
        """
    )
    def cancelar(self) :
        return self.file_data
        
    
    
    """
    ..######..##.....##..######..########..#######..##.....##
    .##....##.##.....##.##....##....##....##.....##.###...###
    .##.......##.....##.##..........##....##.....##.####.####
    .##.......##.....##..######.....##....##.....##.##.###.##
    .##.......##.....##.......##....##....##.....##.##.....##
    .##....##.##.....##.##....##....##....##.....##.##.....##
    ..######...#######...######.....##.....#######..##.....##
    """
    def homologacao_IPM(self, path,value):
        
        return path.format(
            path= "nfse_teste" 
                if self.file_data['nfse']['nf']['ambiente']['$'] == '2'
                    and self.ws_version == '1.00'
                else ""
        )
        
    def translate_time(self, value, props) :
        
        if self.action != props.get('action'): return ""
        
        if not value: return ""
        from datetime import datetime 
        #01/08/2019 14:28:29
        date = datetime.strptime(value, '%d/%m/%Y %H:%M:%S')
        return datetime.strftime(date, "%Y-%m-%dT%H:%M:%S")
        
        
    
    def data_emissao(self, data) :
        # 2020-08-28
        # import datetime
        data = data.split('-')
        return "{dia}/{mes}/{ano}".format(
                dia = data[2]
            ,   mes = data[1]
            ,   ano = data[0]
            
        )
        
    
    def consulta_retorno(self,  value) :
        
        print(value)
        
    
    """
    .##.......####.########.########..######..##....##..######..##.......########
    .##........##..##.......##.......##....##..##..##..##....##.##.......##......
    .##........##..##.......##.......##.........####...##.......##.......##......
    .##........##..######...######...##..........##....##.......##.......######..
    .##........##..##.......##.......##..........##....##.......##.......##......
    .##........##..##.......##.......##....##....##....##....##.##.......##......
    .########.####.##.......########..######.....##.....######..########.########
    """
    def before_save_xml(self):
        self.xml_send =  """<?xml version="1.0" encoding="ISO-8859-1"?>{}""".format(self.xml_send)
        
        
    def before_response_json(self):
        import re
        self.xml_response = re.sub(r"<codigo_html>.*<\/codigo_html>", "", self.xml_response, flags=re.DOTALL )
        
        
        

        
    
    
    