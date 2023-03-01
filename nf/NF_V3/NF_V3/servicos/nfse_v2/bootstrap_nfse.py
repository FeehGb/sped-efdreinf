##############################################################################
#                                                                            #
#  Copyright (C) 2020 softdib (<http://softdib.com.br>).                     #
#                                                                            #
#  Felipe Augusto Goncalves Basilio <felipe@softdib.com.br>                  #
#         Modulos necessarios                                                #
#           pip3 install pyOpenSSL                                           #
#           pip3 install lxml                                                #
#           pip3 install xmljson                                             #
#           pip3 install signxml                                             #
#                                                                            #
##############################################################################


if __name__ == "__main__":
    """ Inicia NFSe apartir dos parametros passado
        
        Parameters
        ----------
        cidade : str, Required
            Nome da cidade que sera a base para o websevice
            
        operacao : str, Required
            Tipo de operacao que sera executada envio, cancelamento, consulta
            
        xml_entrada : str, Required
            Caminho do arquivo xml com os dados para montar o rps
            
        arquivo_saida : str, Required
            Caminho do arquivo onde sera gravado os dados da requisicao
            
        
    """
    import sys, json
    from importlib import import_module
    from libs      import gn_utils     as _utils
    
    
    def get_city(code) :
        file = _utils.read_file(
            path="/var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/cidades.json",
        )
        cidades = json.loads(file)
        
        cidade, = list(filter(lambda item: item['Codigo'] == code, cidades['Municipio']))
        
        return _utils.translate(
            cidade['Nome_municipio'],
            "ÀÁÂÃÄÅÆàáâãäåæÇçÈÉÊËèéêëÌÍÎÏìíîïÑñÒÓÔÕÖØòóôõöøÙÚÛÜùúûüÝÝŸýýÿ \n",
            "AAAAAAAaaaaaaaCcEEEEeeeeIIIIiiiiNnOOOOOOooooooUUUUuuuuYYYyyy_\0"
            )
        
    # not enough values to unpack (expected 1, got 0) provavelmente o numero esta errado
        
    try:
        #Pega o COdigo IBGE da cidade e traduz para o nome do municipio
        _ws   = get_city(sys.argv[1])
        # coloca como primeiro argumento o nome traduzido
        sys.argv[1] = _ws
        #Path do webservice
        _path   = "webservices.{0}.{0}".format(_ws) 
        #Modulo dentro do webservice
        _module = import_module(_path)
        # Classe do Modulo
        _class  = getattr(_module, _ws)
    except Exception as error :
        print(str(error))
        exit()
        
        
    #Executa a class baseado no primeiro argumento que tem que ser a cidade que quer executar o nfse
    _class(sys.argv[1:])
        
    
    
    
    
    