    
    
    
    
    
#! Deprecated trocar para pythn
#@Deco.error(Codigo = "ESD100", Mensagem ="Erro ao assinar Rps")
#@nfse.log("Assinatura XML")
def sign_rps__(self):
    """ Assinatura feita em PHP """
    import subprocess
    
    self.save_xml()
    fileToRun = self.nfse_v2_path + "/../../classes/CAssinaturaDigital.php"
    argsToRun = [
            'php'
        ,    "{}".format(fileToRun)
        ,    "{}".format(self.saved_xml_path)
        ,    "{}".format(self.prestador_cpf_cnpj)
        ,    *self.sign.get("tags",[])
        
    ]
    
    self.xml_send =  subprocess.run(argsToRun , stdout=subprocess.PIPE).stdout.decode('utf-8')
    print(self.xml_send)
    # Se nao retonar o nfse assinado houve um erro entao
    if not self.xml_send :
        self.log(content= "[Error] Houve um erro na assinatura", _type="out")
        raise Exception('[Error] Houve um erro na assinatura')
    
    _utils.eprint(self.xml_send)
    #self.sign_rps2()
    
def sign_rps_(self) :
    
    sign = SignXml(self.key, self.cert_ca)
    self.xml_send = sign.do_sing(self.xml_send, ["lote1","rps1"])
    
    _utils.eprint(self.xml_send)
    
""" import signxml
from lxml import etree
from signxml import XMLSigner """
class SignXml(object):

def __init__(self, key, cert):
    self.key = key
    self.cert = cert
    
def do_sing(self, xml_element, references):
    
    import signxml
    from lxml import etree
    from signxml import XMLSigner
    
    xml_element = etree.fromstring(xml_element)
    
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
    
    
    # = ('#%s' % reference) if reference else None
    signed_root = signer.sign(
        xml_element, key=self.key, cert=self.cert, reference_uri=reference
    )
    
    
    if references:
        for reference in references:
            print(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n")
            print(reference)
            print("\n>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>")
            
            element_signed = signed_root.find(".//*[@Id='%s']" % reference)
            signature = signed_root.find(
                ".//{http://www.w3.org/2000/09/xmldsig#}Signature")
            
            #_utils.eprint(element_signed)
            if element_signed is not None and signature is not None:
                parent = element_signed.getparent()
                parent.append(signature)
            
            
    return etree.tostring(signed_root, encoding=str)
    