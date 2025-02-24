from libs               import gn_utils     as _utils
from libs.gn_jsonpath   import gn_jsonpath  as jxp
from libs.gn_request    import Request 
from OpenSSL            import crypto
#from tempfile           import NamedTemporaryFile
import os, json, datetime, inspect, re
from xml.etree.ElementTree import fromstring
from xml.etree import ElementTree as ET
from lxml import etree
from html import unescape

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902 consultar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.txt /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902  recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/ctba.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug


class NfseDecorator(object):
    """
    Classe de decorators, usado apenas para NFSE
        
    Methods
    -------
        lifecycle(args, kwargs)
        error(deco_kwargs)
        logs(base_text)
        template(deco_kwargs)
        
    """
    def lifecycle(func):
        """Cria um ciclo de vida para a funcao
        -------
            @lifecycle
            def method() :
                pass
        `Se existir os metodos before_method or after_method, ira chamalo na ordem`
            `before_method `
            `method`
            `after_method`
        """
        def wrap( self, *args, **kwargs ) :
            self.call("before_{}".format(func.__name__), *args, **kwargs)
            func(self, *args, **kwargs)
            self.call("after_{}".format(func.__name__), *args, **kwargs)
        return wrap
        
        
    def error(**deco_kwargs):
        """Pega uma excecao e gera um erro padrao para saida COBOL
        -------
            @error
            def method() :
                pass
        `Se o codigo falhar irar jogar um erro a saida para o cobol`
            
        """
        #import traceback
        def decorator(func):
            def wrap( self, *args, **kwargs ) :
                try:
                    return func(self, *args, **kwargs)
                except  Exception as error:
                    #print(''.join(traceback.format_stack()));exit()
                    #__trackback = traceback.format_exc()
                    #__trackback = __trackback.replace("\n","")
                    
                    res_mensagem_retorno = "{Codigo} - {Mensagem} - {error}".format(
                        **{
                            **deco_kwargs, **{"error":str(error)}
                        })
                    self.handle_error({ "res_mensagem_retorno":res_mensagem_retorno })
                    
                
            return wrap
        return decorator
        
    """ def handle_error(self, data) :
        default = self.default_output_map()
        self.cob_output({**default,**data}, True) """
        
    
    def logs( base_text ):
        """Registra um log automatico baseado na funcao 
        -------
            @logs
            def method() :
                pass
        `Cria um log de entrada e saida da funcao executada`
            
        """
        def decorator(func):
            def wrap( self, *args, **kwargs ) :
                self.log( content = "Starting: {}".format(base_text), caller = func.__name__)
                func(self, *args, **kwargs)
                self.log( _type = "out",content = "Finish: {}".format(base_text), caller = func.__name__)
            return wrap
        return decorator
        
    
    def template(**deco_kwargs):
        """Gera um template de um arquivo ou de string.
        Usa o template string para formatar dos dados de retorno da funcao
        
        Args:
            source (str): Pode ser o caminho de um arquivo ou o template literal
            
        -------
            @template(source="Hello {text}")
            def method() :
                return {"text":"world"}
        `Retornara uma sting com o template montado`
            
        """
        import re
        input_template = deco_kwargs['source'].strip()
        
        def get_template_item( template , attr) :
            regex = re.compile(r"({})\[(.*?)\]".format(attr), re.DOTALL)
            matches, = re.findall(regex, template)
            template = re.sub( regex, r"\1", template )
            return matches[1], template
        
        
        def decorator(func):
            def wrap( self, *args, **kwargs ) :
                #Verifica se o template é um path ou um template pronto
                is_file = "/" in input_template and ( input_template.split(".")[-1] in ["txt","xml"])
                
                if is_file :
                    path = self.nfse_v2_path + "/webservices/" + self.ws_name 
                    template = _utils.read_file(
                        path = input_template.format(
                            root = path# caminho
                        )
                    )
                    
                else :
                    template = input_template
                    
                
                data = func(self, *args, **kwargs )
                # percorre todos os valores do objeto e faz a substituicao
                # Não usei o format aqui porque pode existir um array dentro do objeto
                #print(data)
                
                return template.format(**data)
                
                """ for key in data:
                    value = ''
                    regex = r'{\s*'+re.escape(key)+ r'\s*}'
                    if isinstance(data[key], list) :
                        #template e criado apartir do nomedata tag + [] array
                        template_item, template = get_template_item(template, key)
                        for sub in data[key] :
                            value += template_item.format(**sub)
                            
                            
                    else :
                        value = str(data[key])
                    template = re.sub(regex, value, template)
                    #_utils.eprint(template)
                    
                    
                
                return template """
                
                
            return wrap
        return decorator
    
    

class nfse(NfseDecorator):
    
    def __init__(self, args,**kwargs) :
        super(nfse, self)
        
        self.initParams(args, kwargs)
        
        self.factory()
        
        
    # !Nao colocar log na declaracao inicial de variaveis, o log usa elas
    def initParams(self, args, kwargs) :
        
        self.nfse_v2_path   = os.path.dirname(os.path.abspath(__file__))
        self.cert_path      = "/var/www/html/nf/nfse/certificados"
        #self.file_data       = {}
        self.ws_name        = args[0] 
        self.action         = args[1] 
        self.input_file     = args[2]
        self.output_file    = args[3]
        self.debug          = args[-1] == "debug"
        
        self.provider       = kwargs.get('provider', False)
        
        self.log(
            content = "\nIniciado em: {} ".format(datetime.datetime.now().strftime("%d/%m/%Y %H:%M:%S"))
        )
        self.log(
            content = "Parametros: {} ".format(" ".join(args))
        )
        
    
    @NfseDecorator.logs("Fabrica")
    def factory(self) :
        #self.xml_file_out   =  params[3]
        
        #Carrega o Mapa com as configuracoes do Web Service
        self.load_map()
        
        #Carrega arquivos de entrada que o COBOL gera, podendo ser xml, csv, txt, json
        self.load_input_file()
        
        # Carregar configuracoes do clientes arquivo .ini
        self.load_config_ini()
        
        # Renderia o XML de acordo com o typ
        self.render_xml_rps()
        
        # Create Certificate
        self.make_certificate()
        # Faz a assinatura caso necessario
        # Assinatura padrao
        self.handle_sign()
        
        # Monta o XML para Comunicacao
        self.xml_soap()
        
        # Salva o arquivo para deixalo armazenado
        self.save_xml()
        
        # set_headers é obrigatorio
        #self.set_headers()
        #self.validate()
        # Envia o arquivo 
        self.comunication()
        #exit()
        
        self.handle_response()
        
    
    @NfseDecorator.error(Codigo = "ESD001", Mensagem ="Nao foi possivel carregar o map")
    @NfseDecorator.logs("Carregamento das configuracoes do webservice map.json")
    def load_map(self) :
        
        file = _utils.read_file(
            path= "{0}/webservices/{1}/map.json".format(
                    self.nfse_v2_path # caminho
                ,   self.ws_name
                )
        )
        webservice_props        = json.loads(file)
        provider_props          = {}
        
        if self.provider:
            provider_file = _utils.read_file(
            path= "{0}/provedores/{1}/map.json".format(
                    self.nfse_v2_path # caminho
                ,   self.provider
                )
            )
            provider_props = json.loads(provider_file)
        
        
        self.wsProps = _utils.deepMerge(provider_props, webservice_props)
        self.set_quick_atrr()
        
        
    def set_quick_atrr(self):
        self.wsServiceProps   = self.wsProps.get(self.action)
        self.ws_version       = self.wsProps.get("version")
        #self.ws_send         = {**self.wsProps.get("send",{}), **self.wsServiceProps.get("send",{})}
        self.ws_send          = _utils.deepMerge(self.wsProps.get("send",{}), self.wsServiceProps.get("send",{}))
        
        self.ws_response      = self.wsProps.get("response")
        self.service_response = self.wsServiceProps.get("response")
        
    
    
    """ #@NfseDecorator.error(Codigo = "ESD002", Mensagem ="Nao foi possivel carregar o template")
    @NfseDecorator.logs("Carregando template")
    def load_template(self) :
        
        self.template = self.call(self.action, )
        
        
        return 
        self.template = _utils.read_file(
            path= "{0}/webservices/{1}/templates/{2}".format(
                    self.nfse_v2_path # caminho
                ,   self.ws_name
                ,   "template_{}.txt".format(self.action)
            )
        ) """
        
        
    
    @NfseDecorator.error(Codigo = "ESD002", Mensagem ="Nao foi possivel carregar o arquivo de entrada")
    @NfseDecorator.logs("Manipulacao de arquivo de entrada")
    def load_input_file(self) :
        from pathlib import Path
        data    = ''
        suffix  = Path(self.input_file).suffix
        
        if suffix:
            data = _utils.read_file(path=self.input_file)
            self.input_file_suffix = suffix[1:]
        else:
            data = self.input_file
            self.input_file_suffix = 'command_line'
            
        # Carrega arquivo apartir de sua extensao
        # Podendo ser .xml .json .csv .txt ou linha de argumento
        
        self.call("load_data_{}".format(self.input_file_suffix), data)
        
        """ #!REMOVER ISSO DEPOIS NAO DEIXAR FIXO """
        #self.environment  = "homologacao"
        
    
    # Chamada diamicamente por load input file
    @NfseDecorator.logs("caputra de dados do XML")
    def load_data_xml(self, data) :
        #bf_str = BadgerFish()   # Keep XML values as strings
        #input_file_json = bf_str.data(fromstring(data))
        self.file_data = self.do_badgerfish( data, xml_fromstring=False )
        #self.set_data_from_xml()
        # Executa e caputra os dados e o traduz para montar o XML de envio
        self.set_xml_data()
        
        
    def set_xml_data(self):
        self.environment        = "producao" if self.file_data['nfse']['nf']['ambiente']['$'] == '1' else 'homologacao'
        self.prestador_cpf_cnpj =  self.file_data['nfse']['prestador']['cpfcnpj']['$']
        self.nf_controle        =  self.file_data['nfse']['nf']['controle']['$']
        self.usuarioprefeitura  =  self.file_data['nfse']['nf']['usuarioPrefeitura']['$']
        self.senhaprefeitura    =  self.file_data['nfse']['nf']['senhaPrefeitura']['$']
        self.codtom             =  self.file_data['nfse']['prestador']['codTom']['$']
        
        #_utils.eprint(self.file_data['nfse']['prestador']['cpfcnpj'])
    
    
    def do_badgerfish(self, data, **kwargs) :
        from xmljson import BadgerFish
        bf_str = BadgerFish(**kwargs)   # Keep XML values as strings
        parser = etree.XMLParser(recover=True)
        return  bf_str.data(fromstring(data, parser=parser))
        
    
    @NfseDecorator.logs("caputra de dados do json")
    def load_data_json(self, data) :
        self.file_data = json.loads(data)
        if not self.call("set_data_from_json"):
            self.set_default_data()
            
        
    @NfseDecorator.logs("caputra de dados do txt")
    def load_data_txt(self, data):
        lines   = data.split("\n")
        props,  values   = lines
        props = props.split("|")
        values = values.split("|")
        
        self.file_data = dict(zip(props, values))
        
        if not self.call("set_data_from_txt"):
            self.set_default_data()
            
        
    @NfseDecorator.logs("caputra de dados do csv")
    def load_data_csv(self, data):
        self.load_data_txt(data)
        
        
        
    @NfseDecorator.logs("caputra de dados por linha de comando")
    def load_data_command_line(self, data) :
        
        # cpfcnpj=20042802000171&inscricaomunicipal=&ambiente=2&controle=4559&protocolo=637298143366160922
        _list = data.split("&")
        self.file_data = {
        #   KEY                             : VALUE
        _utils.get_index(value.split("="),0):(_utils.get_index(value.split("="),1) or "").strip()
            for value in _list
        }
        self.set_default_data()
        
    
    def set_default_data(self) :
        
        self.environment        = "producao" if self.file_data.get("ambiente") == "1" else 'homologacao'
        self.prestador_cpf_cnpj = self.file_data.get("cpfcnpj")
        self.nf_controle        = self.file_data.get("controle")
        self.usuarioprefeitura  = self.file_data.get("usuarioprefeitura")
        self.senhaprefeitura    = self.file_data.get("senhaprefeitura")
        self.codtom             = self.file_data.get("codtom")
        
        
        
        
    def build_xpath(self, node, path, value = False, attrs = {}) :
        """ 
            Monta o XML baseado em Xpath
        """
        components  = re.split(r'''/(?=(?:[^'"]|'[^']*'|"[^"]*")*$)''',path)
        
        def append_value(node, value):
            try:
                node.append((value))
            except :
                node.text = value
            
            
        
        if components[0] == node.tag:
            components.pop(0)
        while components:
            # take in account positional  indexes in the form /path/para[3] or /path/para[location()=3]
            if "[" in components[0]:
                component, trail = components[0].split("[",1)
                target_index = int(trail.split("=")[-1].strip("]"))
            else:
                component = components[0]
                target_index = 0
                if '@' in component :
                    attrs = component.split('@')
                    component  = attrs[0]
                    attrs = dict([
                        attr.replace("'","").split('=')
                        for attr in attrs[1:] 
                    ])
                
                
            components.pop(0)
            last  = not len(components)
            found_index = -1
            for child in list(node):
                if child.tag == component:
                    found_index += 1
                    if found_index == target_index:
                        node = child
                        break
            else:
                
                for i in range(target_index - found_index):
                    new_node = ET.Element(component)              
                    node.append(new_node)
                node = new_node
                
                for attr in attrs :
                    node.set(attr, attrs[attr] )
                attrs = []
                
                if value and last:
                    if isinstance(value, list):
                        for val in value :
                            append_value(node, val)
                    else :
                        append_value(node, value)
                    
                    
                
            
    # self.handle_data(self.wsServiceProps["output"], data, return_xml=False)
    def handle_data(self, mapItens, json, root={}) :
        """ 
            Manipulacao de dados do map
            Faz o de para dos dados de entrada para os dados de saida,
            podendo ser a saida como um XML ou um JSON
        """
        tag       = root.get('tag')
        attrs     = root.get('attrs') or {}
        element   = ET.Element(tag) if tag else {}
        
        for attr in attrs:
            element.set(attr, attrs[attr])
            
        
        for item in mapItens :
            
            value = self.handle_item(item, json)
            if 'to' in item:
                isRequired = "required" not in item or item.get('required') == True
                acceptFalsy = "acceptFalsy" in item or item.get('acceptFalsy') == True
                if not acceptFalsy and (not isRequired and not value):
                    continue
                elif isRequired and not value  :
                    self.handle_error({
                        "res_mensagem_retorno": "O campo {} é requerido ".format(item['to'])
                    })
                    
                #_utils.eprint(item)
                
                cpath   =  item.get('custom_path_to')
                item_to =  self.call(item['custom_path_to'], item['to'], value) if cpath else item['to']
                if item_to:
                    if not tag :
                        element[item_to] = value
                    else :
                        self.build_xpath(element, item_to, value, item.get("attrs", {}))
                
        return element
        
    
    def handle_item(self, item, json) :
        """ 
            Manipulacao e tratamento de cada item 
        """
        value = False
        
        if "from" in item :
            cpath =  item.get('custom_path_from')
            _from = self.call(item['custom_path_from'], item['from'], value) if cpath else item['from']
            value = self.jsonxpath(_from, json, item.get('trim', False))
        if "default" in item and not value:
            value = item['default']
            
        if "custom" in item :
            value = self.call(item['custom'], value)
        if "custom_value" in item :
            value = self.call(item['custom_value'], value, item)
        if "by_type" in item:
            value = self.call(item['by_type'], value, item)
            
        if 'list' in item :
            value = self.handle_list(item, value)
            #_utils.eprint(value)
        if 'child' in item :
            value = self.handle_data(item['child'], value)
        if "globalThis" in item and item['globalThis']:
            varName = item['globalThis'] if isinstance(item['globalThis'], str) else item['to']
            setattr(self,varName, value )
            
        #if isinstance(value, str):
        #    value = value.strip()
        
        return value
        
    
    def handle_list(self, item, value):
        data = []
        root = item.get("root", {})
        
        if isinstance(value, dict) :
            data = [self.handle_data(item['list'], value, root)]
        elif isinstance(value, list) :
            data = [self.handle_data(item['list'], sub_item, root) for sub_item in value ]
            
        return data
        
        
    
    @NfseDecorator.error(Codigo = "ESD003", Mensagem ="Não foi possivel carregar configuracao do cliente")
    @NfseDecorator.logs("Caputra de configuracoes do ciente, arquivo ini")
    def load_config_ini(self) :
        import configparser 
        config = configparser.ConfigParser()
        config.read("/var/www/html/nf/NF_V3/NF_V3_dados/config_cliente/{}.ini".format(
            self.prestador_cpf_cnpj
        ))
        try:
            config.get('empresa', 'cnpj')
            self.cfg_client_ini  = config
        except :
            raise Exception("Erro ao carregar dados do arquivo .ini")
            
        
    
    #@NfseDecorator.error(Codigo = "ESD004", Mensagem ="Houve um Erro durante a renderizacao do XML")
    @NfseDecorator.logs("Renderizando XML")
    def render_xml_rps(self) :
        """ 
            Renderiza o XML baseado no seu tipo
            Tipos aceitos: Recepcionar, Cancelar, Consultar
        """
        # renderiza o XML ou pelo template ou pleo gerador
        xml  = self.call(self.action)
        #xml = """<pedRegEvento xmlns="http://www.sped.fazenda.gov.br/nfse" versao="1.00"> <infPedReg xmlns="http://www.sped.fazenda.gov.br/nfse" Id="EVT14001591284818889000109000000000000022090462154867101101001"> <chNFSe>14001591284818889000109000000000000022090462154867</chNFSe> <CNPJAutor>84818889000109</CNPJAutor> <dhEvento>2022-09-27T14:03:00-03:00</dhEvento> <tpAmb>2</tpAmb> <verAplic>Testes_0.1.0</verAplic> <nPedRegEvento>001</nPedRegEvento> <e101101> <xDesc>Cancelamento de NFS-e em homologacao</xDesc> <cMotivo>1</cMotivo> <xMotivo>Motivo teste de cancelamento</xMotivo> </e101101> </infPedReg> </pedRegEvento>"""
        if xml :
            self.xml_send = self.parseXML(xml, remove_blank_text=True)
        else :
            self.handle_error({
                    "Codigo" : "ESD004"
                ,   "Mensagem" : self.action + " ainda nao criado"}
            )
        
        #_utils.eprint(self.xml_send)
        
    
    def parseXML(self, xml, **kwargs) -> str :
        parser      =  etree.XMLParser(**kwargs)
        return etree.XML(xml, parser=parser)
        
    
    def recepcionar(self) :
        """
            Por padrao O metodo recepcionar é criado a partir das definicoes do map
            os metodos consultar e cancelar, sao baseado em templates.
        """
        xml  = self.ws_send.get('xml',{})
        
        if self.input_file_suffix == "xml" and  xml:
            root = xml.get('root',{})
            xml_handle = self.handle_data(xml.get("template",{}), self.file_data, root)
            #_utils.eprint(ET.tostring(handle,  short_empty_elements=False))
            # Para previnir shorttags
            return ET.tostring(xml_handle, short_empty_elements=False).decode('utf-8')
        
        
    @NfseDecorator.error(Codigo = "ESD005", Mensagem ="Erro ao salvar XML")
    @NfseDecorator.lifecycle
    def save_xml(self):
        #Todo : change local, talvez colocar em um temp
        from random import randint
        self.saved_xml_path = "/user/nfse/{cnpj}/CaixaEntrada/Processado/{city}_{action}_{now}_{random}.xml".format(
                cnpj    = self.prestador_cpf_cnpj
            ,   city    = self.ws_name
            ,   action  = self.action
            ,   now     = datetime.datetime.now().strftime("%Y%m%d%H%M%S%f")
            ,   random  = randint(1,10000)
        )
        #78758
        data = self.xml_send
        if isinstance(self.xml_send, bytes):
            data = self.xml_send.decode()
            
        
        _utils.write_file( path=self.saved_xml_path , data=data)
        
        
    def validate(self) -> bool:
        from lxml import etree, objectify
        from lxml.etree import XMLSyntaxError
        
        xmlschema_doc = etree.parse("/var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/webservices/{city}/schemas/nfse.xsd".format(city=self.ws_name,action=self.action))
        xmlschema = etree.XMLSchema(xmlschema_doc)
        xml_doc = etree.fromstring(self.xml_send.encode())
        result = xmlschema.validate(xml_doc)
        return result
    
    
    
    @NfseDecorator.error(Codigo = "ESD100", Mensagem ="Erro ao assinar Rps")
    @NfseDecorator.logs("Assinatura XML")
    def handle_sign(self):
        
        self.sign   = self.ws_send.get('sign',{})
        #action_sign = self.wsServiceProps.get("send",{}).get('sign',{})
        #_utils.eprint(self.sign)
        
        if self.sign  :
            self.sign_rps()
        else :
            self.xml_send = etree.tostring(
                self.xml_send
            ,   encoding = str
        ) 
            
        #_utils.eprint(self.xml_send)
        
    # TODO: Criar um classe apenas para tratar assinaturas
    def sign_rps(self) :
        
        from signxml import XMLSigner, XMLVerifier
        import signxml
        
        #xml_to_sign = self.xml_send#.decode()
        #root        = etree.fromstring(xml_to_sign)
            
        cert_options = {
                "key"   : self.key
            ,   "cert"  : self.cert_ca
        }
        options         = self.sign.get("options",{})
        sing_opt        = {**cert_options,**options}
        XMLSigner_opt   = self.sign.get("XMLSigner",{})
        
        signer = XMLSigner(**XMLSigner_opt, method=signxml.methods.enveloped)
        
        ns = {}
        ns[None] = signer.namespaces['ds']
        signer.namespaces = ns
        
        signed_root   = signer.sign(self.xml_send, **sing_opt)
        
        self.xml_send = etree.tostring(
                signed_root
            ,   encoding = str
        )
        #_utils.eprint(self.xml_send)
        
        
        if (self.debug) :
            pass
            #verified_data = XMLVerifier().verify(signed_root, x509_cert=self.cert_ca).signed_xml
            
            
        #verified_data = XMLVerifier().verify(signed_root).signed_xml
        
        """ if self.sign.get("removeDS") :
            self.xml_send = _utils.multi_replace(self.xml_send,[["ds:",""],[":ds",""]]) """
        #self.xml_send = b"""<EnviarLoteRpsEnvio xmlns="http://nfe.sjp.pr.gov.br/servico_enviar_lote_rps_envio_v03.xsd"><LoteRps Id="2"><NumeroLote xmlns="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">78</NumeroLote><Cnpj xmlns="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">10703580000641</Cnpj><InscricaoMunicipal xmlns="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">83990</InscricaoMunicipal><QuantidadeRps xmlns="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">1</QuantidadeRps><ListaRps xmlns="http://nfe.sjp.pr.gov.br/tipos_v03.xsd"><Rps><InfRps Id="1"><IdentificacaoRps><Numero>78</Numero><Serie>0</Serie><Tipo>1</Tipo></IdentificacaoRps><DataEmissao>2020-07-22T10:22:13</DataEmissao><NaturezaOperacao>1</NaturezaOperacao><RegimeEspecialTributacao>1</RegimeEspecialTributacao><OptanteSimplesNacional>2</OptanteSimplesNacional><IncentivadorCultural>2</IncentivadorCultural><Status>1</Status><Servico><Valores><ValorServicos>104.95</ValorServicos><ValorCsll>0.00</ValorCsll><IssRetido>2</IssRetido><ValorIss>5.25</ValorIss><ValorIssRetido>0.00</ValorIssRetido><OutrasRetencoes>0</OutrasRetencoes><BaseCalculo>104.95</BaseCalculo><Aliquota>0.05</Aliquota><ValorLiquidoNfse>104.95</ValorLiquidoNfse></Valores><ItemListaServico>1401</ItemListaServico><CodigoTributacaoMunicipio>452000101</CodigoTributacaoMunicipio><Discriminacao>V00130 - ARTHUR VALASKI       Pedido:   T 000027/00                   Fatura: 01-15/08/2020-           26.23  Fatura: 02-14/09/2020-           26.24  Fatura: 03-14/10/2020-           26.24  Fatura: 04-13/11/2020-           26.24</Discriminacao><CodigoMunicipio>4125506</CodigoMunicipio></Servico><Prestador><Cnpj>10703580000641</Cnpj><InscricaoMunicipal>83990</InscricaoMunicipal></Prestador><Tomador><IdentificacaoTomador><CpfCnpj><Cnpj>03938055000135</Cnpj></CpfCnpj></IdentificacaoTomador><RazaoSocial>SOFT SISTEMAS ELETRONICOS LTDA(C23693)</RazaoSocial><Endereco><Endereco>RUA FARRAPOS</Endereco><Numero>485</Numero><Bairro>CENTRO</Bairro><CodigoMunicipio>4118501</CodigoMunicipio><Uf>PR</Uf><Cep>85501340</Cep></Endereco><Contato><Telefone>4135448500</Telefone><Email>taciane.ricci@softeletronica.com.br</Email></Contato></Tomador></InfRps></Rps></ListaRps></LoteRps><Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2006/12/xml-c14n11"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI="#2"><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/><Transform Algorithm="http://www.w3.org/2006/12/xml-c14n11"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>KySN5x8bjHtVTMLwuM9fjz/wYThNzMx/WeqL6Vv3l3w=</DigestValue></Reference></SignedInfo><SignatureValue>igpE5vAiHLkrB89YvP3m8K675sDPDnzGdTa0WCLVO0/ESJeYhW+7wfNdEXOeYOJshhp1KJ8//7jZzoi9UHm0ZLBYZ8d3IS999J4MMsFLx4SBLIwJ1NWI877uxItYGeTt3oNoTufI6KLB/O52oqvXXAuA1qv6uOXFmg5bJ69S9+5eCe6hox5cbRoflwl5g0GaOQrEFIRxttKnkth/aPhlNwRlLj+rVJfjzHxui7au9s5t42aYtpMb2HSLXo0HDgQvNiulprwQFvDFuUGn4mH0vLZGTg3ZN5xVEp5tQB9ohMaei5jnfW6bpUXQTRMwTwMcErGMbp6Jxt568r5L0cTFNg==</SignatureValue><KeyInfo><X509Data><X509Certificate>MIIHqTCCBZGgAwIBAgIIdrxq0yZ+96cwDQYJKoZIhvcNAQELBQAwdjELMAkGA1UE\nBhMCQlIxEzARBgNVBAoTCklDUC1CcmFzaWwxNjA0BgNVBAsTLVNlY3JldGFyaWEg\nZGEgUmVjZWl0YSBGZWRlcmFsIGRvIEJyYXNpbCAtIFJGQjEaMBgGA1UEAxMRQUMg\nU0FGRVdFQiBSRkIgdjUwHhcNMjAwNjEyMTEzODM4WhcNMjEwNjEyMTEzODM4WjCB\n4jELMAkGA1UEBhMCQlIxEzARBgNVBAoTCklDUC1CcmFzaWwxCzAJBgNVBAgTAlBS\nMREwDwYDVQQHEwhDVVJJVElCQTE2MDQGA1UECxMtU2VjcmV0YXJpYSBkYSBSZWNl\naXRhIEZlZGVyYWwgZG8gQnJhc2lsIC0gUkZCMRYwFAYDVQQLEw1SRkIgZS1DTlBK\nIEExMRcwFQYDVQQLEw4yMDA4NTEwNTAwMDEwNjE1MDMGA1UEAxMsVkdSIFNFUlZJ\nQ09TIEFVVE9NT1RJVk9TIExUREE6MTA3MDM1ODAwMDAxMzcwggEiMA0GCSqGSIb3\nDQEBAQUAA4IBDwAwggEKAoIBAQC22aNwlliVI+JQvk90r4g002eAAA1e4ab1IINM\nUXpP+qkUVi+OmMIykvaJquiDTZfqIjbZTRFrmJhU/rL3hIE16bt/GYQADD1MRbd3\ncZ6IkaHzolKxEubM8PHIMV1oJmvsk8IsWeVXM3+ytYuUAQ1zvw1t345LkhgGG4ZE\n0YqyS8Rd9bBjJmBg8dKTBy0eBlOflQQeroHoo+yBfXx6TSCdnxV0TjSwwEaSUnR/\n77dE9dEEOg2XFNL6oITdjjoFJx0MW7MpNlFwsxjVpwswbCgjzTiR9avJS3ULZAh8\nSlBSXhbe6Rq55kgfGgIBgtkyfEKzs58Hordka319nKWdP+yjAgMBAAGjggLMMIIC\nyDAfBgNVHSMEGDAWgBQpXkvVRky7/hanY8EdxCby3djzBTAOBgNVHQ8BAf8EBAMC\nBeAwbQYDVR0gBGYwZDBiBgZgTAECATMwWDBWBggrBgEFBQcCARZKaHR0cDovL3Jl\ncG9zaXRvcmlvLmFjc2FmZXdlYi5jb20uYnIvYWMtc2FmZXdlYnJmYi9hYy1zYWZl\nd2ViLXJmYi1wYy1hMS5wZGYwga4GA1UdHwSBpjCBozBPoE2gS4ZJaHR0cDovL3Jl\ncG9zaXRvcmlvLmFjc2FmZXdlYi5jb20uYnIvYWMtc2FmZXdlYnJmYi9sY3ItYWMt\nc2FmZXdlYnJmYnY1LmNybDBQoE6gTIZKaHR0cDovL3JlcG9zaXRvcmlvMi5hY3Nh\nZmV3ZWIuY29tLmJyL2FjLXNhZmV3ZWJyZmIvbGNyLWFjLXNhZmV3ZWJyZmJ2NS5j\ncmwwgYsGCCsGAQUFBwEBBH8wfTBRBggrBgEFBQcwAoZFaHR0cDovL3JlcG9zaXRv\ncmlvLmFjc2FmZXdlYi5jb20uYnIvYWMtc2FmZXdlYnJmYi9hYy1zYWZld2VicmZi\ndjUucDdiMCgGCCsGAQUFBzABhhxodHRwOi8vb2NzcC5hY3NhZmV3ZWIuY29tLmJy\nMIG8BgNVHREEgbQwgbGBIkxVSVpAR0xJTlRFTElHRU5DSUFDT05UQUJJTC5DT00u\nQlKgHQYFYEwBAwKgFBMSUkVKQU5FIEdVU0UgR1JBU0VMoBkGBWBMAQMDoBATDjEw\nNzAzNTgwMDAwMTM3oDgGBWBMAQMEoC8TLTI3MDgxOTczODk2NDU2MTg5OTEwMDAw\nMDAwMDAwMDAwMDAwMDAwMDAwMDAwMKAXBgVgTAEDB6AOEwwwMDAwMDAwMDAwMDAw\nHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUFBwMEMAkGA1UdEwQCMAAwDQYJKoZI\nhvcNAQELBQADggIBAGxflIA3ZeiNBv68dAOwuIib0Yr2uiyK/3gD99Wc7z/N4uOB\noyTm1hkTfWhYkRw+5p4oyQE9IQkwX5buB/8ic6BKlUWLkhDE+TyfoyjwYVjngkYZ\n29dvbWNurYXiJjXlIyULfaod777l0kjpaaLDJTiRIUCytirigEDUzS+1SxHBU6Mk\nH+OXvpvGpQYrlhoLkc4X9nYsbdyYWXptSwumR3wswU0aZHxOAnHc3piVN1iooQ/m\ntCBCDQ53R/ynB37o0AUQvzf9sB8nv7saRAVh11O2hE88AgllgfkzQBuMpNYcTQgp\nYZSmRjAsAoROIwZXVriXtWt+Mh5OehOrKLaz007efcdPUUmjdHBmQQPQ7WEg9jnC\n+AoQer1MeD5ZsT5hQCBHr0+1FeBgKIC3bHD0uT/M3yxC8mOA9WsiorhvhWZRwGWn\nGGzoXyFBGsNHE0AumWNw8gDme0ZCpfecl9ltMPNbLKsitppjkG8wyylPKUyYaW/j\njIROHRp2lTxzs9iI75wdQ36oXqyfEGcG6s/jWyHfIe+dBRIboKEvrXG9w+1Ym2zp\ntgNJk5KQGJqiClaumo+URtU7YU9FyiGkbbH8gQMB2DxgwncC/FMC+sy6iUv8ALIH\nU1ss2FEo/y406i8Dy0gx8H0R44VhEEfhrUZ5i80H5ve2fqljoDLjdSsTwJ8q\n</X509Certificate></X509Data></KeyInfo></Signature></EnviarLoteRpsEnvio>'"""
        
    
    
    @NfseDecorator.error(Codigo = "ESD008", Mensagem ="Erro durante a Montagem do SOAP")
    @NfseDecorator.logs("Montagem SOAP")
    def xml_soap(self) :
        """ Montagem do layout SOAP se nao definido manda o XML como foi renderizado"""
        soap = self.call("soap")
        
        if soap :
            self.xml_send = soap
            
        #_utils.eprint(self.xml_send)
        
    @NfseDecorator.error(Codigo = "ESD009", Mensagem ="Houve um erro durante a comunicacao")
    @NfseDecorator.logs("Comunicao")
    def comunication(self) :
        """ Configuracoes previas antes de enviar o NFS-e """
        self.call("set_headers", is_required= True)
        
        url = self.wsProps.get(self.environment)
        opt = {
            "method"  : "POST",
            "url"     : url,
            "data"    : self.xml_send,
            "timeout" : 60
        }
        if self.ws_send.get('type') == 'file' :
            del opt['data']
            
        #_utils.eprint(self.xml_send)
        self.req_response = self.request.doRequest(**opt)
        
    
    
    #@NfseDecorator.error(Codigo = "ESD0010", Mensagem ="Houve um erro no tratamento de resposta")
    @NfseDecorator.logs("Manipulacao retorno da comunicacao")
    def handle_response(self) :
        
        status_code      =  self.req_response.status_code
        self.log(content = "Tratando codigo de resposta:{} ".format(status_code)) 
        
        if status_code == 200:
            
            self.xml_response = unescape(self.req_response.content.decode(self.req_response.encoding or self.req_response.apparent_encoding))
            #self.xml_response = unescape(self.xml_response)
            self.response_json()
            self.data_output()
            
            
        else :
            
            res = self.call( "handle_response_{}".format(status_code) )
            if not res: 
                self.log(
                        content =   "Tratamento para o status code:{} não foi implementada ainda".format(status_code)
                    ,   _type   =   "out"
                )
            
            res_mensagem_retorno = "{Codigo} - {Mensagem} - {Correcao}".format(
                    Codigo   =  status_code
                ,   Mensagem = "Um erro ocorreu na sua requisicao"
                ,   Correcao = "https://httpstatuses.com/"
            )
            self.handle_error({
                    "res_mensagem_retorno"      :   res_mensagem_retorno
                ,   "res_conteudo_retorno"      :   self.req_response.content,
            })
            
            
        
    # Ira transformar a resposta em xml para json
    @NfseDecorator.error(Codigo = "ESD010", Mensagem ="Houve um erro no tratamento de Reposta")
    @NfseDecorator.lifecycle
    def response_json(self) :
        # Pegar o que esta dentro do Body padrao é "soap:Body"
        response_wrapped = self.service_response.get('response_wrapped', self.ws_response.get('response_wrapped', ''))
        within = True
        
        if type(response_wrapped) == dict:
            within           = response_wrapped.get("within","")
            response_wrapped = response_wrapped.get("tag","")
            
        # Pega a tag baseado na definicao do map
        wraped = self.get_tag( response_wrapped , self.xml_response, within)
        # Replace customizados
        custom_replacements = self.ws_response.get("custom_replacements",[])
        normalize_xml = _utils.multi_replace(wraped,[ *custom_replacements ])
        
        # Remover atributos desnecessarios
        xml = re.sub(r"<([\w:]+).*?(\/)?>",r"<\1\2>",normalize_xml)
        
        self.response_dict =  self.do_badgerfish( xml , xml_fromstring=False)
        
        
        
    @NfseDecorator.lifecycle
    def data_output(self):
        
        default_data         = self.default_output_map()
        ws_template_out      = self.ws_response.get('template',[]) 
        service_template_out = self.service_response.get("template",[])
        #_utils.eprint(data)
        res_data             = self.handle_data(ws_template_out + service_template_out, self.response_dict)
        self.cob_output({**default_data,**res_data, **{"res_conteudo_retorno":self.minify_xml(self.xml_response)}})
        
        
        
    def handle_error(self, data) :
        default = self.default_output_map()
        self.cob_output({**default,**data}, True)
        
        
        
    
    def cob_output(self, data, error=False):
        output = \
        "{prestador_cpf_cnpj}"          "|"\
        "{nf_ambiente}"                 "|"\
        "{nf_controle}"                 "|"\
        "{res_numero_lote}"             "|"\
        "{res_protocolo}"               "|"\
        "{res_data_recebimento}"        "|"\
        "{res_consulta_numero}"         "|"\
        "{res_consulta_verificacao}"    "|"\
        "{res_consulta_data_emisao}"    "|"\
        "{res_data_cancelamento}"       "|"\
        "{res_mensagem_retorno}"        "|"\
        "{res_conteudo_retorno}"        
        
        #print(output.format(**data))
        output = output.format(**data)
        _utils.write_file(data=output, path=self.output_file, encoding='utf-8' )
        self.log(content= "[COBOL] {}".format(output), _type="out")
        
        if (error) :
            exit()
        
    
    def default_output_map(self):
        return {
                "prestador_cpf_cnpj"        : self.prestador_cpf_cnpj if self.hasAttr('prestador_cpf_cnpj',self) else ''
            ,   "nf_ambiente"               : self.environment        if self.hasAttr('environment'       ,self) else ''
            ,   "nf_controle"               : self.nf_controle        if self.hasAttr('nf_controle'       ,self) else ''
            ,   "res_numero_lote"           : ""
            ,   "res_protocolo"             : ""
            ,   "res_data_recebimento"      : ""
            ,   "res_consulta_numero"       : ""
            ,   "res_consulta_verificacao"  : ""
            ,   "res_consulta_data_emisao"  : ""
            ,   "res_data_cancelamento"     : ""
            ,   "res_mensagem_retorno"      : ""
            ,   "res_conteudo_retorno"      : ""
        }
        
        
            
        
    """
    ..######..##.....##..######..########..#######..##.....##
    .##....##.##.....##.##....##....##....##.....##.###...###
    .##.......##.....##.##..........##....##.....##.####.####
    .##.......##.....##..######.....##....##.....##.##.###.##
    .##.......##.....##.......##....##....##.....##.##.....##
    .##....##.##.....##.##....##....##....##.....##.##.....##
    ..######...#######...######.....##.....#######..##.....##
    """
    
    def response_from(self, path, value) :
        
        service_response =  self.service_response.get('result_tag','')
        return path.format(service_response=service_response)
        
        
    
    def mensagem_retorno(self, value):
        
        #_utils.eprint(value)
        if not value: 
            return ''
        
        message_list = []
        create_message = lambda data:  " - ".join([data[key]['$'] for key in data.keys()])
        
        if isinstance(value, list) :
            message_list = [create_message(item) for item in value]
        else :
            message_list.append(create_message(value))
            
        return "&".join(message_list)
        
        
    
    
    def get_value_by_ref(self, value, props):
        if not value: 
            return ''
            
        ref = props.get('ref')
        #_utils.eprint(props)
        return value.get(ref,{}).get('$', '')
        
        
    
    
    
    """from lxml import etree
        sing = Assinatura("3332","3423432")
        
        print(sing.assina_xml(etree.fromstring(self.xml_send), "LoteRps"))
        from lxml import etree as lxml_ET
        from signxml import XMLSigner, XMLVerifier
        from xml.etree import ElementTree as ET
        
        base = "/var/www/html/nf/nfse/certificados/"
        
        ET.register_namespace("ds", "http://www.w3.org/2000/09/xmldsig#")

        cert, key = [open(f, "rb").read() for f in (
                            "{}28839121000140_certKey.pem".format(base)
                        ,   "{}28839121000140_priKey.pem" .format(base)
                        )
                    ]
        
        
        #root = etree.fromstring(self.xml_send)
        data = ET.fromstring("<Test><ds:Signature xmlns:ds=\"http://www.w3.org/2000/09/xmldsig#\" Id=\"placeholder\"></ds:Signature></Test>")

        #signed_root = XMLSigner().sign(data, key=key )
        #self.signed_rps = XMLVerifier().verify(signed_root, x509_cert=cert)
        signed_root = XMLSigner().sign(data, key=key)
        data_serialized = lxml_ET.tostring(signed_root)
        
        print(data_serialized) """
        
        
        
    """
    ....###....########...######..########.########.....###.....######..########
    ...##.##...##.....##.##....##....##....##.....##...##.##...##....##....##...
    ..##...##..##.....##.##..........##....##.....##..##...##..##..........##...
    .##.....##.########...######.....##....########..##.....##.##..........##...
    .#########.##.....##.......##....##....##...##...#########.##..........##...
    .##.....##.##.....##.##....##....##....##....##..##.....##.##....##....##...
    .##.....##.########...######.....##....##.....##.##.....##..######.....##...
    """
    
    #@NfseDecorator.error(Codigo = "ESD007", Mensagem ="Metodo Set_headers nao Implementado no Webserivce")
    #def set_headers(self):
    #    raise NotImplementedError("Metodo 'set_headers' deve ser implementado")
    #    exit()
        
    
    
    """
    ..######..##.....##..######..########..#######..##.....##....########..##....##....########.##....##.########..########
    .##....##.##.....##.##....##....##....##.....##.###...###....##.....##..##..##........##.....##..##..##.....##.##......
    .##.......##.....##.##..........##....##.....##.####.####....##.....##...####.........##......####...##.....##.##......
    .##.......##.....##..######.....##....##.....##.##.###.##....########.....##..........##.......##....########..######..
    .##.......##.....##.......##....##....##.....##.##.....##....##.....##....##..........##.......##....##........##......
    .##....##.##.....##.##....##....##....##.....##.##.....##....##.....##....##..........##.......##....##........##......
    ..######...#######...######.....##.....#######..##.....##....########.....##..........##.......##....##........########
    """
    
    def datetime_now(self, value) :
        return datetime.datetime.now().replace(microsecond=0).isoformat()
        
        
    def cpfcnpj(self, value) :
        return "Cnpj" if value == "J" else "Cpf"
        
        
    def date_now(self, value, props) :
        if 'mask' not in props:
            return datetime.datetime.now().replace(microsecond=0).isoformat()
        else :
            return datetime.datetime.now().strftime(props['mask'])
        
        
    def break_line(self, value, props) :
        pattern =  props.get("pattern", "\r\n") # if "pattern" not in props else 
        return value.format(lineBreak = pattern)
        
    
    def toInt(self, value):
        return str(int(value))

    
    
    
    """
    .########.##.....##.########.########.########..##....##....###....##......
    .##........##...##.....##....##.......##.....##.###...##...##.##...##......
    .##.........##.##......##....##.......##.....##.####..##..##...##..##......
    .######......###.......##....######...########..##.##.##.##.....##.##......
    .##.........##.##......##....##.......##...##...##..####.#########.##......
    .##........##...##.....##....##.......##....##..##...###.##.....##.##......
    .########.##.....##....##....########.##.....##.##....##.##.....##.########
    """
    

    def make_certificate(self) :
        cfx_certificate_filepath   = self.fix_str_ini(self.cfg_client_ini['certificado']['arquivo_certificado'])
        password                   = self.fix_str_ini(self.cfg_client_ini['certificado']['senha'])
        
        
        def _create_file( content, alias ):
            path =  "{0}/{1}_{2}.pem".format(
                    self.cert_path
                ,   self.prestador_cpf_cnpj
                ,   alias
            )
            _utils.write_file(path=path, data=content.decode('utf8'))
            return path
        
        with open(cfx_certificate_filepath, "rb") as cert_pfx:
            pkcs12 = crypto.load_pkcs12(cert_pfx.read(), password.encode())

        self.cert = crypto.dump_certificate(crypto.FILETYPE_PEM, pkcs12.get_certificate())
        self.key = crypto.dump_privatekey(crypto.FILETYPE_PEM, pkcs12.get_privatekey())
        
        cert_ca = b""
        if pkcs12.get_ca_certificates():
            for ca in pkcs12.get_ca_certificates():
                cert_ca = crypto.dump_certificate(crypto.FILETYPE_PEM, ca) + cert_ca
                
        self.cert_ca = self.cert + cert_ca
        
        self.cert_file = _create_file(self.cert_ca, "certKey")
        self.key_file = _create_file(self.key, "priKey")
        
        
        #return cert_ca, cert_file, key, key_file
    
    
    
    """
    .########..#######...#######..##........######.
    ....##....##.....##.##.....##.##.......##....##
    ....##....##.....##.##.....##.##.......##......
    ....##....##.....##.##.....##.##........######.
    ....##....##.....##.##.....##.##.............##
    ....##....##.....##.##.....##.##.......##....##
    ....##.....#######...#######..########..######.
    """
    def get_tag(self, tag, xml, within = True) :
        #xml       = xml.replace("\n","")
        tagStart  = "<{}>".format(tag)
        tagEnd    = "</{}>".format(tag)
        
        start     = xml.find(tagStart)
        end       = xml.find(tagEnd)
        
        
        return xml[start+len(tagStart):end] if within else xml[start:end+len(tagEnd)]
        #return  re.findall(r"<{tag}>(.*)</{tag}>".format(tag = tag), xml)
    
    
    def log(self, content, _type = "in", caller = False) :
        
        #Controle para que seja recriado o log a cada envio
        path = "/var/tmp/nfseV2_{0}_{1}.log".format(
            self.ws_name,
            datetime.datetime.now().strftime("%Y_%m_%d")
        )
        self.path_exists = os.path.exists(path)
        #self.log_started = self.hasAttr("log_started", self)
        
        
        log = "{type}[{ws} |> {fn}] {content}{bn}".format(
                type        = "<" if _type == "out" else ">"
            ,   ws          = self.ws_name
            ,   fn          =  caller or inspect.stack()[1].function
            ,   content     = content
            ,   bn          = "\n" if not self.debug else ""
            
        )
        
        
        
        if self.debug:
            print(log)
        else :
            _utils.write_file(
                    data=log
                ,   path=path
                ,   flag= "w" if not self.path_exists else "a+"
                ,   encoding='utf8')
    

    def jsonxpath(self, path ,json, trim) :
        
        result = jxp(path=path, json=json, trim=trim)
        
        if result.value and isinstance(result.value, dict) and  "$" in result.value:
            return result.value['$'].strip()
            
        if (isinstance(result.value, str)) :
            result.value.strip() 
            
        
        return result.value
        """ JSON = jxp(json=json,path=path)
        
        if (isinstance(JSON.value, str)) :
            return JSON.value.strip()
        else :
            return JSON.value """
        
    
    def hasAttr(self, attribute, _object) :
        return attribute in dir(_object)
        
    
    def call(self, *args, **kwargs) :
        """ 
            Parameters
            ----------

            `` args[0]`` str
                The name's funtion to be called.
                
            ``args[1:]`` parameter's function
                
            ``kwargs`` object params.
            
            ``is_required`` bool
                To determine if this function is required or not
                ``default`` None.
                
            Raises
            ------
            ``Exception``
                If args[0] not defined will raise Exception
        """
        required = kwargs.get("is_required")
        if args :
            func     = args[0]
        else:
            raise Exception("call() missing 1 required positional argument")
        
        if self.hasAttr(func, self ) :
            if required : del kwargs['is_required']
            try:
                return getattr(self, func)(*args[1:], **kwargs)
            except  :
                raise
            
        
        else :
            if required :
                raise Exception("name '{}' is not defined, this function is required".format(func))
            else :
                return False
        
    
    def fix_str_ini(self, value) :
        return value.replace('"', "")
        
    
    def minify_xml (self, xml) :
        mini_xml = xml
        regexs = [
            [r'\n', ''],
            [r'(\s\s+)', " "],
            [r'>\s<', "><"]
        ]
        for regex in regexs :
            mini_xml = re.sub(regex[0],regex[1],mini_xml)
            
            
        #mini_xml = re.sub(r'(?<=\>)(\n+)|(\n+)(?=\<)','',xml)
        #mini_xml = re.sub(r'(\s\s+)',' ',xml)
        return mini_xml
        #(\s\s+)
    
    # ! NÃO IMPLEMENTADO
    def valid_xml_schema(self) :
        import xmlschema
        my_schema = xmlschema.XMLSchema(
            '/var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/webservices/{webservice}/schemas/{type}.xsd'
            .format(
                webservice ="Curitiba",
                type="envio"
            )
        )
        my_schema.is_valid(self.xml_send)
    


""" import signxml
from lxml import etree
from signxml import XMLSigner """
""" class Assinatura(object):
    
    def __init__(self, arquivo, cnpj):
        self.arquivo = arquivo
        self.cnpj = cnpj
        
        
        
        
    def assina_xml(self, xml_element, reference):

        for element in xml_element.iter("*"):
            if element.text is not None and not element.text.strip():
                element.text = None

        signer = XMLSigner(
            method=signxml.methods.enveloped, signature_algorithm="rsa-sha1",
            digest_algorithm='sha1',
            c14n_algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315')

        ns = {}
        ns[None] = signer.namespaces['ds']
        signer.namespaces = ns
        
        base = "/var/www/html/nf/nfse/certificados/"
        cert, key = [open(f, "rb").read() for f in (
                            "{}28839121000140_certKey.pem".format(base)
                        ,   "{}28839121000140_priKey.pem" .format(base)
                        )
                    ]
        
        
        #ref_uri = ('#%s' % reference) if reference else None
        signed_root = signer.sign(
            xml_element, key=key, cert=cert
        )
        if reference:
            element_signed = signed_root.find(".//*[@NfseDecorator.Id='%s']" % reference)
            signature = signed_root.find(
                ".//{http://www.w3.org/2000/09/xmldsig#}Signature")

            if element_signed is not None and signature is not None:
                parent = element_signed.getparent()
                parent.append(signature)
        return etree.tostring(signed_root, encoding=str) """
        

    
if __name__ == "__main__" :
    
    pass
        
        
    