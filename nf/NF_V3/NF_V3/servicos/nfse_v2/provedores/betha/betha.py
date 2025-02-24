from libs               import gn_utils     as _utils
from nfse import nfse
from libs.gn_request import Request
from lxml import etree
#import xmlsec
import os
import re

class betha(nfse):
    def __init__(self, params) :
        
        super(betha, self).__init__(params, provider = 'betha')
        
    def set_headers(self) :
        
        SOAPAction = self.ws_send.get('SOAPAction') or ''
        
        self.request = Request(
            #cert    = (self.cert_file, self.key_file),
            headers =   {
                
                "Content-Type"      : "text/xml;charset=UTF-8",
                "SOAPAction"        : SOAPAction
            }
        )
        
        
    @nfse.template(
        source = """
        <ConsultarLoteRpsEnvio xmlns="http://www.betha.com.br/e-nota-contribuinte-ws">
            <Prestador>
                <CpfCnpj>
                    <Cnpj>{cpfcnpj}</Cnpj>
                </CpfCnpj>
                <InscricaoMunicipal>{inscricaomunicipal}</InscricaoMunicipal>
            </Prestador>
            <Protocolo>{protocolo}</Protocolo>
        </ConsultarLoteRpsEnvio>
        """
    )
    def consultar(self) :
        return self.file_data
        
        
    @nfse.template(
        source = """
        <CancelarNfseEnvio xmlns = "http://www.betha.com.br/e-nota-contribuinte-ws">
            <Pedido>
                <InfPedidoCancelamento Id="1">
                    <IdentificacaoNfse>
                        <Numero>{numero}</Numero>
                        <CpfCnpj>
                            <Cnpj>{cpfcnpj}</Cnpj>
                        </CpfCnpj>
                        <InscricaoMunicipal>{inscricaomunicipal}</InscricaoMunicipal>
                        <CodigoMunicipio>{codigomunicipio}</CodigoMunicipio>
                    </IdentificacaoNfse>
                    <CodigoCancelamento>1</CodigoCancelamento>
                </InfPedidoCancelamento>
            </Pedido>
        </CancelarNfseEnvio>
        """
    )
    def cancelar(self) :
        return self.file_data
        
        
    
    @nfse.template(
        source = """
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:e="http://www.betha.com.br/e-nota-contribuinte-ws">
            <soapenv:Header/>
            <soapenv:Body>
                <e:{SOAPAction}>
                    <nfseCabecMsg>
                        <![CDATA[
                            <cabecalho xmlns="http://www.betha.com.br/e-nota-contribuinte-ws" versao="2.02"><versaoDados>2.02</versaoDados></cabecalho>
                        ]]>
                    </nfseCabecMsg>
                    <nfseDadosMsg>
                        <![CDATA[{mainXML}]]>
                    </nfseDadosMsg>
                </e:{SOAPAction}>
            </soapenv:Body>
        </soapenv:Envelope>
    """
    )
    def soap(self) :
        
        SOAPAction = self.ws_send.get('SOAPAction')
        
        if isinstance(self.xml_send, bytes): 
            self.xml_send = self.xml_send.decode()
        
        return {
            "mainXML" : self.xml_send,
            "SOAPAction" : SOAPAction 
        }
        
        
        
    def identificador_rps(self, path, data ) :
        self.references = str(int(data))
        return path.format(reference= self.references )
        
        
    """
    ..######..##.....##..######..########..#######..##.....##
    .##....##.##.....##.##....##....##....##.....##.###...###
    .##.......##.....##.##..........##....##.....##.####.####
    .##.......##.....##..######.....##....##.....##.##.###.##
    .##.......##.....##.......##....##....##.....##.##.....##
    .##....##.##.....##.##....##....##....##.....##.##.....##
    ..######...#######...######.....##.....#######..##.....##
    """
        
    def tipo_pessoa_tomador_path(self, path,value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj"))
        return path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj")
        
    def cpfcnpj(self, value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(value[::-1][0:11][::-1] if tipo_pessoa == "F" else value)
        return value[::-1][0:11][::-1] if tipo_pessoa == "F" else value
        
        
    def consulta_retorno(self,  value) :
        # se for um link
        if isinstance(value, str) and value.startswith('http') :
            link, = re.findall(r'\?link=(\w+)$', value) or ['']
            return link
        else :
            return super().mensagem_retorno(value)
        
    
    def gerar_numero_lote(self, value):
        import time
        return str(int(round(time.time() * 1000000)))[0:15]
        
        
        #1602187958062534 160218839207186
        
        
    #ASSINATURA

    # TODO: Fazer ir para o generico
    def sign_rps(self) :
        
        """ xml_to_sign = self.xml_send#.decode()
        regex = r"(<Rps>.*<\/Rps>)"
        rps, = re.findall(regex, xml_to_sign.decode() )
        
        a1 = AssinaturaA1SignXML(self.key, self.cert_ca)
        rps_signed = a1.assinar(rps,"rps1",retorna_string=True)
        
        xml = re.sub(regex,rps_signed, xml_to_sign.decode())
        
        a1 = AssinaturaA1SignXML(self.key, self.cert_ca)
        xml_signed = a1.assinar(xml,"lote1",retorna_string=True)
        
        self.xml_send = xml_signed
        
        return  """
        from signxml import XMLSigner, XMLVerifier
        import signxml
        
        #xml_to_sign = self.xml_send#.decode()
        cert_options = {
                "key"   : self.key
            ,   "cert"  : self.cert_ca
        }
        
        def sign(xml, cert_data, reference_uri=None):
            signer = XMLSigner(
                method=signxml.methods.enveloped,
                signature_algorithm='rsa-sha1',
                digest_algorithm='sha1',
                c14n_algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315'
            )
            #xml_root = None
            
            if not isinstance(xml, etree._Element):
                xml = etree.fromstring(xml)
                
            #xml_root = xml.getroot()
            # FIX: Isso eh uma gambiarra para poder tirar o prefixo da tag <Signature>, ja que o webservices da Betha
            # nao consegue achar a assinatura se ela tiver o prefixo do namespace padrao <ds:Signature>...
            ds = signer.namespaces['ds']
            del signer.namespaces['ds']
            signer.namespaces[None] = ds
            signed_root = signer.sign(
                xml,
                key=cert_data['key'],
                cert=cert_data['cert'],
                reference_uri=reference_uri
            )
            return signed_root#etree.tostring(signed_root)
        
        
        #regex = r"(<Rps>.*<\/Rps>)"
        SOAPAction      = self.ws_send.get('SOAPAction')
        reference_uri   = self.ws_send.get('sign',{}).get('options',{}).get('reference_uri',[])
        #xml             = etree.fromstring(xml_to_sign.decode())
        xml             = self.xml_send
        
        if SOAPAction in ['RecepcionarLoteRpsSincrono','RecepcionarLoteRps']:
            rps = xml.find("LoteRps/ListaRps/Rps",namespaces=xml.nsmap)
            rps_signed = sign(rps,cert_options, reference_uri=self.references)
            rps.getparent().replace(rps, rps_signed)
            
        if SOAPAction in ['CancelarNfse']:
            pedido          = xml.find("Pedido",namespaces=xml.nsmap)
            pedido_signed   = sign(pedido,cert_options, reference_uri=reference_uri)
            pedido.getparent().replace(pedido, pedido_signed)
            
        # Isso vai mudar em breve Trocar para len() nas versoes futras
        if xml.find("LoteRps",namespaces=xml.nsmap) :
            xml = sign(xml, cert_options,reference_uri=reference_uri )
        
        xml_to_string = etree.tostring(xml)
        self.xml_send = xml_to_string.decode()#.replace("\n","")
        #print("")
        #_utils.eprint(self.xml_send)
        
"""         
class AssinaturaA1SignXML(object):

    def __init__(self,key, cert):
        self.key = key
        self.cert = cert
        #self.key, self.cert = CertificadoA1(certificado).separar_arquivo(senha)

    def assinar(self, xml, reference, retorna_string=False):
        
        from signxml import XMLSigner, XMLVerifier
        import signxml
        NAMESPACE_SIG = 'http://www.w3.org/2000/09/xmldsig#'
        # busca tag que tem id(reference_uri), logo nao importa se tem namespace
        xml = etree.fromstring(xml)
        #reference = reference

        # retira acentos
        xml_str = etree.tostring(xml, encoding="unicode", pretty_print=False)
        xml = etree.fromstring(xml_str)

        signer = XMLSigner(
            method=signxml.methods.enveloped, signature_algorithm="rsa-sha1",
            digest_algorithm='sha1',
            c14n_algorithm='http://www.w3.org/TR/2001/REC-xml-c14n-20010315')

        ns = {None: signer.namespaces['ds']}
        signer.namespaces = ns

        ref_uri = ('#%s' % reference) if reference else None
        signed_root = signer.sign(
            xml, key=self.key, cert=self.cert, reference_uri=ref_uri)

        ns = {'ns': NAMESPACE_SIG}
        # coloca o certificado na tag X509Data/X509Certificate
        #tagX509Data = signed_root.find('.//ns:X509Data', namespaces=ns)
        #etree.SubElement(tagX509Data, 'X509Certificate').text = self.cert
        if retorna_string:
            return etree.tostring(signed_root, encoding="unicode", pretty_print=False)
        else:
            return signed_root
        
     """