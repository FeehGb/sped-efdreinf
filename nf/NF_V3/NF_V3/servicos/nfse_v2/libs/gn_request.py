import requests
import time
import json


class Request():
    
    def __init__(self,  **kwargs):
        self.kwargs         = kwargs
        
    
    def __handleParams(self, **params):
        self.merge(self.kwargs, params)
        
        if 'base_url' not in params and 'rote' in params and not 'url' in params :
            exit("""##### gn_request:error ##### - Eh Necessario definir uma url base""")
            
        
        if 'rote' in params :
            params['url'] = params['base_url'] + params['rote']
            del params['rote']
        # always remove base_url
        if 'base_url' in params :
            del params['base_url']
        
        #!deprecated
        """ if hasattr(self,'headers') and not 'headers' in params :
            params['headers'] = self.headers """
            
        #!deprecated
        """ cache_params = self.args_params
        if "params" in params :
            cache_params.update(params['params']) """
            
        
        #params['params'] = cache_params
        
        
        if 'method' not in params :
            params['method' ] = 'GET'
            #params['method' ] = params['method']
            
        
        return params
        
    
    def doRequest(self, **params):
        
        params = self.__handleParams(**params)
        
        try:
            self.req = requests.request(**params)
        except requests.exceptions.RequestException as e:
            print(e)
            exit()
        
        self.params = {}
        return self.req
        
        
        
    def processResponse(self, req) :
        req.headers
        pass
        
        
    def merge(self,source, destination):
        """
        run me with nosetests --with-doctest file.py

        >>> a = { 'first' : { 'all_rows' : { 'pass' : 'dog', 'number' : '1' } } }
        >>> b = { 'first' : { 'all_rows' : { 'fail' : 'cat', 'number' : '5' } } }
        >>> merge(b, a) == { 'first' : { 'all_rows' : { 'pass' : 'dog', 'fail' : 'cat', 'number' : '5' } } }
        True
        """
        for key, value in source.items():
            if isinstance(value, dict):
                # get node or create one
                node = destination.setdefault(key, {})
                self.merge(value, node)
            else:
                destination[key] = value

        return destination
        
    
    def __del__(self):
        pass
        
        
        
if __name__ == "__main__":
    
    mercos = Request(
        base_url ='https://sandbox.mercos.com/api/v1/',
        headers =
        {
            "CompanyToken"      : "40e7fd68-6aad-11e9-8c04-a29009f9c38a",
            "ApplicationToken"  : "bd361d76-6aaf-11e9-a936-a29009f9c38a",
            "Content-Type"      : "application/json"
        }) 
            
    #user     = mercos.doRequest(url="usuarios")
    clientes = mercos.doRequest(url="produtos")
    
    print(clientes)
    
    """ 
    requestSd = Request(
        base_url ='http://189.123.238.86:3000/api/pedido/',
        headers = 
        {
            "Content-Type"  : "application/json"
        ,   "accept"        : "application/json"
        }
    )
    
    auth = requestSd.doRequest(
        url     =   "authentication", 
        method  =   "post", 
        json    =
        {
                "user"  : "SAC"
            ,   "pass"  : "5680"
            ,   "lkgp"  : "ebs"
        })
    
    print(auth.text.replace('"',''))
    usuarios = requestSd.doRequest(url="tabela/TBUSUARIO",
        headers = 
        {
            "Content-Type"     : "application/json"
        ,   "x-access-token"   : auth.text.replace('"','')
        }
    )
    
    #print(usuarios.json())
    data = {
            "pedido": [
                {
                "VLPEDIDOSISTEMA": "1218.00",
                "DTPEDIDO": "2019-08-29",
                "HRPEDIDO": "14:09:18",
                "CDCLIENTE": "C18421",
                "CDTRANSPORTADOR": "T00002",
                "CDTRASNPORTADORREDESP": "T00001",
                "CDUSUARIO": "U00002",
                "CDVENDEDOR": "V00002",
                "CDCONDPGTO": "999",
                "VLTOTAL": "1218.00",
                "CDFRETE": "99",
                "DSOBSPEDIDO": "Pedido",
                "VLPERCDESCONTO": 10,
                "VLDESCONTO": "0.00"
                }
            ],


        "itenscarrinho":[ 
            {
            "CDPEDIDO": 4,
            "CDPRODUTO": "35",
            "VLSEQUENCIA": 1,
            "VLQUANTIDADE": 12,
            "VLPRECOUNITARIO": "100.00",
            "VLPRECOFINAL": "1200",
            "VLPERCDESCONTO": 0,
            "VLDESCONTO": 0
            },
            {
            "CDPEDIDO": 4,
            "CDPRODUTO": "24",
            "VLSEQUENCIA": 1,
            "VLQUANTIDADE": 1,
            "VLPRECOUNITARIO": "18.00",
            "VLPRECOFINAL": "18",
            "VLPERCDESCONTO": 0,
            "VLDESCONTO": 0
            }   ]
        }
    gerarPedido = requestSd.doRequest(url="pedido/",
        headers = 
        {
            "Content-Type"     : "application/json"
        ,   "x-access-token"   : auth.text.replace('"','')
        },
        method="POST",
        data=json.dumps(data))
    
    print(gerarPedido)
    #pedido = json.loads(ped)
     """
    
    
    
    
    


"""
    { pedido: [
        { 
        
        IDTBPEDIDO: mercos[0].id,
        
        NMLKGRUPO: 'VEM DO TOKEN'   ,
        CDEMPRESA: 'VEM DO TOKEN'   ,
        CDFILIAL:  'VEM DO TOKEN'   ,
        
        DTPEDIDO:  ' mercos[0].ultima_alteracao.split(" ")[0]',
        HRPEDIDO:  ' mercos[0].ultima_alteracao.split(" ")[1]',
        CDCLIENTE: ' (
            
            SELECT TBREL.IDSOFTDIB 
            FROM   TBRELACIONAID TBREL 
            WHERE  TBREL.IDAPI = mercos[0].cliente_id (3670607)
        )',
        CDTRANSPORTADOR: ' (
            SELECT TBREL.IDSOFTDIB 
            FROM   TBRELACIONAID TBREL 
            WHERE  TBREL.IDAPI = mercos[0].transportadora_id
        )',
        
        CDTRASNPORTADORREDESP: null
        CDVENDEDOR: ' (
            SELECT TBVEND.CDVENDEDOR 
            FROM   TBVENDEDOR TBVEND 
            WHERE  TBVEND.NMEMAIL = (
                https://sandbox.mercos.com/api/v1/usuarios/
                recuperar email
            )
        )',
        
        
        CDUSUARIO: 'DYFAR',
        
        CDCONDPGTO: ' (
            SELECT TBREL.IDSOFTDIB 
            FROM   TBRELACIONAID TBREL 
            WHERE  TBREL.IDAPI = mercos[0].condicao_pagamento_id
        )',
        VLTOTAL: 'mercos[0].total',
        CDFRETE: 'null',
        DSOBSPEDIDO: 'mercos[0].observacoes',
        VLPERCDESCONTO: 'np.sum(mercos[0].descontos)',
        VLDESCONTO: '0.00',
        VLPEDIDOSISTEMA: null,
        FGENVIADO: '',
        NMDIRETORIO: '/home/user/natuphitus'
        }
    ],
    itenscarrinho: [
        { 
        IDTBPEDIDO: '',
        CDEMPRESA: '',
        NMLKGRUPO: '',
        CDFILIAL: '',
        
        CDPRODUTO: 'mercos[0].items',
        VLSEQUENCIA: '1',
        VLQUANTIDADE: '4',
        VLPRECOUNITARIO: '12.12',
        VLPRECOFINAL: '7.877999999999999',
        VLPERCDESCONTO: '35',
        VLPRECOTABELA: '12.12',
        VLPRECOMINIMO: '0'
        },
        { IDTBPEDIDO: '1',
        CDEMPRESA: '1',
        NMLKGRUPO: 'NATUPHITUS',
        CDFILIAL: '1',
        CDPRODUTO: '00001931',
        VLSEQUENCIA: '2',
        VLQUANTIDADE: '3',
        VLPRECOUNITARIO: '6.93',
        VLPRECOFINAL: '4.5045',
        VLPERCDESCONTO: '35',
        VLPRECOTABELA: '6.93',
        VLPRECOMINIMO: '0'
        }
    ]
}

[
    {
        "cliente_id": 3670607,
        "status": "2",
        "condicao_pagamento": "A VISTA 7 DIAS",
        "forma_pagamento_id": 0,
        "contato_nome": "Lucas da Silva",
        "cliente_nome_fantasia": "Zé Store",
        "cliente_estado": "SC",
        "cliente_complemento": "sala 1402",
        "status_custom_id": null,
        "observacoes": "apenas um teste William",
        "numero": 2,
        "representada_razao_social": "Softdib | Parceiro",
        "endereco_entrega": {
            "bairro": null,
            "complemento": null,
            "cep": null,
            "endereco": null,
            "cidade": null,
            "estado": null,
            "id": null,
            "numero": null
        },
        "data_emissao": "2019-05-30",
        "representada_nome_fantasia": "Softdib | Parceiro",
        "cliente_inscricao_estadual": "ISENTO",
        "status_faturamento": "1",
        "cliente_cep": "89201010",
        "total": 384.3,
        "id": 1734788,
        "criador_id": 38530,
        "representada_id": 45005,
        "tipo_pedido_id": null,
        "transportadora_id": 0,
        "ultima_alteracao": "2019-05-30 17:21:43",
        "cliente_rua": "Rua Abdon Batista",
        "items": [
            {
                "tipo_ipi": "P",
                "quantidade": 2,
                "preco_bruto": 100,
                "descontos_do_vendedor": [
                    5,
                    3
                ],
                "tabela_preco_id": 0,
                "ipi": 0,
                "observacoes": "DOIS DESCONTOS DE 5 E 3 PORCENTO",
                "st": 0,
                "produto_codigo": "WWW",
                "descontos_de_promocoes": [],
                "produto_nome": "PRODUTO TESTE WILLIAM",
                "produto_id": 12204755,
                "quantidade_grades": [],
                "descontos_de_politicas": [],
                "cotacao_moeda": 1,
                "descontos": [
                    5,
                    3
                ],
                "subtotal": 184.3,
                "id": 12986970,
                "preco_liquido": 92.15,
                "excluido": false
            },
            {
                "tipo_ipi": "P",
                "quantidade": 1,
                "preco_bruto": 200,
                "descontos_do_vendedor": [],
                "tabela_preco_id": 0,
                "ipi": 0,
                "observacoes": "",
                "st": 0,
                "produto_codigo": "zzz",
                "descontos_de_promocoes": [],
                "produto_nome": "produto zzz teste",
                "produto_id": 12204756,
                "quantidade_grades": [],
                "descontos_de_politicas": [],
                "cotacao_moeda": 1,
                "descontos": [],
                "subtotal": 200,
                "id": 12986984,
                "preco_liquido": 200,
                "excluido": false
            }
        ],
        "cliente_cidade": "Joinville",
        "cliente_cnpj": "46487899000110",
        "transportadora_nome": "",
        "extras": [],
        "condicao_pagamento_id": 40356,
        "cliente_suframa": "",
        "nome_contato": "Lucas da Silva",
        "cliente_razao_social": "Loja do Zé LTDA",
        "cliente_numero": "121",
        "cliente_bairro": "Centro"
    }
    
]




"""
