from provedores.ipm.ipm import ipm
# https://e-gov.betha.com.br/e-nota-test/ambienteteste.faces

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4107652  recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug

class Colombo(ipm) : # 4105805
    
    def __init__(self, params) :
        super(Colombo, self).__init__(params)
        
