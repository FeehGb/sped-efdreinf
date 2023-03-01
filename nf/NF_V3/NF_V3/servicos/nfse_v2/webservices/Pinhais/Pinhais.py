from provedores.ipm.ipm import ipm
from io import StringIO
from libs.gn_request import Request
from requests.auth import HTTPBasicAuth



# https://e-gov.betha.com.br/e-nota-test/ambienteteste.faces
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4107652  recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
class Pinhais(ipm) : # 4119152
    
    def __init__(self, params) :
        super(Pinhais, self).__init__(params)
        
        
        
    def set_headers(self):
        """Tratamento para o novo formato IPM para envio de NFSe
        Ticket : 89631 contem informacoes sobre as mudancas e as cidades 
        que contemplam ja esse novo tratamento.
            
        """
        file = StringIO()
        file.write(self.xml_send)
        file.seek(0)

        files = {
            'xml': file,
        }
        
        self.request = Request(
            files   = files,
            auth    = HTTPBasicAuth(self.usuarioprefeitura , self.senhaprefeitura),
            headers = {}
        )
        
        #print(self.request)
    
    @ipm.template(
        source = """
        <nfse id="nota">
            <nf>
                <numero>{numero}</numero>
                <situacao>C</situacao>
                <serie_nfse>1</serie_nfse>
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
        # Cidade de Pinhais nao aceita mais 5 posicoes no codigo tom
        self.file_data['codtom'] = self.file_data.get('codtom', '')[:-1]
        return self.file_data
    


"""
from nfse import nfse
from libs.gn_request import Request


# 202007042016874443 protocolo de atendimento 
class Pinhais(nfse) : # 4119152
    
    def __init__(self, params) :
        super(Pinhais, self).__init__(params)
        
    
    @nfse.error(Codigo = "ESD200", Mensagem ="Erro ao criar configuracoes do header")
    def set_headers(self) :
        url   = self.wsProps.get(self.environment)
        # Configuracoes do arquivo .ini 01631022000112
        login = self.file_data['nfse']['nf']['usuarioPrefeitura']
        senha = self.file_data['nfse']['nf']['senhaPrefeitura']
        
        self.request = Request(
            url     = url,
            method  = "POST",
            files   = {'f1': open(self.saved_xml_path, 'rb')},
            params = {
                    "login" : login 
                ,   "senha" : senha
                ,   "cidade": self.file_data['nfse']['prestador']['codTom']
            },
            headers =   {
                'User-Agent':"Mozilla/5.0 (Windows NT 5.1; rv:8.0.1) Gecko/20100101 Firefox/8.0.1"
            }
        ) 
        
    def comunication(self) :
        print(self.nfse)
        self.request.doRequest()
        
    
    # Esta sendo assinado em php
    @nfse.logs("Assinatura RPS")
    def __sign_rps(self):
        pass
        
        
    
    @nfse.template(
        source = "{root}/templates/template_enviar.txt"
    )
    def cancelar(self) :
        return data
        
    
    @nfse.template(
        source = "{root}/templates/template_enviar.txt"
    )
    def consultar(self, data) :
        return data
        
"""