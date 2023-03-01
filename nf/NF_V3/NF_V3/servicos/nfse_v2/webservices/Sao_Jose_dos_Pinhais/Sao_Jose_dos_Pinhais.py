from libs               import gn_utils     as _utils
from nfse               import nfse
from libs.gn_request    import Request


# 202007042016874443 protocolo de atendimento 
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4125506 consultar 'cpfcnpj|inscricaomunicipal|ambiente|controle|protocolo&10703580000641|83990|2|000000078|71899850' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4125506 consultar 'cpfcnpj|inscricaomunicipal|ambiente|controle|protocolo&10703580000641|83990|2|78|71899850' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4125506 consultar 'cpfcnpj=10703580000641&inscricaomunicipal=83990&ambiente=2&controle=243&protocolo=71900074' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4125506 cancelar 'cpfcnpj=10703580000641&inscricaomunicipal=83990&ambiente=2&controle=243&protocolo=71900074' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4125506  recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/sjp.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug


class Sao_Jose_dos_Pinhais(nfse) : # 4125506
    
    def __init__(self, params) :
        super(Sao_Jose_dos_Pinhais, self).__init__(params)
        
    
    def set_headers(self) :
        
        SOAPAction = self.ws_send.get('SOAPAction','')
        self.request = Request(
            headers =   {
                "Host"              : "nfe.sjp.pr.gov.br",
                "Content-Type"      : "text/xml;charset=UTF-8",
                "SOAPAction"        : SOAPAction
            }
        ) 
        
    
    
    
    @nfse.template(
        source = """
        <ConsultarLoteRpsEnvio xmlns="http://nfe.sjp.pr.gov.br/servico_consultar_lote_rps_envio_v03.xsd" Id="consultar">
            <Prestador xmlns:tipos="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">
                <tipos:Cnpj>{cpfcnpj}</tipos:Cnpj>
                <tipos:InscricaoMunicipal>{inscricaomunicipal}</tipos:InscricaoMunicipal>
            </Prestador>
            <Protocolo>{protocolo}</Protocolo>
        </ConsultarLoteRpsEnvio>
        """
    )
    def consultar(self) :
        return self.file_data
        
    """
        ? Foi criado para atender a possibilidade de consultar se a nota foi cancelada. 
    """
    @nfse.template(
        source = """
        <ConsultarNfseEnvio xmlns="http://nfe.sjp.pr.gov.br/servico_consultar_nfse_envio_v03.xsd" xmlns:tipos="http://nfe.sjp.pr.gov.br/tipos_v03.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <Prestador xmlns:tipos="http://nfe.sjp.pr.gov.br/tipos_v03.xsd">
                <tipos:Cnpj>{cpfcnpj}</tipos:Cnpj>
                <tipos:InscricaoMunicipal>{inscricaomunicipal}</tipos:InscricaoMunicipal>
            </Prestador>
            <NumeroNfse>{numero}</NumeroNfse>
            <PeriodoEmissao>
                <DataInicial>{competencia}</DataInicial>
                <DataFinal>{competencia}</DataFinal>
            </PeriodoEmissao>
        </ConsultarNfseEnvio>
        """
    )
    def consultarNFSE(self) :
        return self.file_data
        
    
    @nfse.template(
        source = """
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfe="http://nfe.sjp.pr.gov.br">
            <soapenv:Header/>
            <soapenv:Body>
                <{SOAPAction}>
                    <arg0>
                        <![CDATA[<ns2:cabecalho versao="3" xmlns:ns2="http://nfe.sjp.pr.gov.br/cabecalho_v03.xsd"><versaoDados>3</versaoDados></ns2:cabecalho>]]>
                    </arg0>
                    <arg1>
                        <![CDATA[{mainXML}]]>
                    </arg1>
                </{SOAPAction}>
            </soapenv:Body>
        </soapenv:Envelope>
    """
    )
    def soap(self) :
        
        SOAPAction = self.ws_send.get('SOAPAction')
        return {
            "mainXML" : self.xml_send,
            "SOAPAction" : SOAPAction 
        }
    
    #example_content = """ <?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:RecepcionarLoteRpsV3Response xmlns:ns1="http://nfe.sjp.pr.gov.br"><return>&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt; &lt;ns1:EnviarLoteRpsResposta xmlns:ns1=&quot;http://nfe.sjp.pr.gov.br/servico_enviar_lote_rps_resposta_v03.xsd&quot; xmlns:ns2=&quot;http://nfe.sjp.pr.gov.br/tipos_v03.xsd&quot; xmlns:ns3=&quot;http://www.w3.org/2000/09/xmldsig#&quot;&gt; &lt;ns1:NumeroLote&gt;78&lt;/ns1:NumeroLote&gt; &lt;ns1:DataRecebimento&gt;2020-07-16T15:47:41&lt;/ns1:DataRecebimento&gt; &lt;ns1:Protocolo&gt;71899850&lt;/ns1:Protocolo&gt; &lt;/ns1:EnviarLoteRpsResposta&gt; </return></ns1:RecepcionarLoteRpsV3Response></SOAP-ENV:Body></SOAP-ENV:Envelope>"""
    #example_content = """<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:ConsultarLoteRpsV3Response xmlns:ns1="http://schemas.xmlsoap.org/soap/envelope/"><return>&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;&lt;ns1:ConsultarLoteRpsResposta xmlns:ns1=&quot;http://nfe.sjp.pr.gov.br/servico_consultar_lote_rps_resposta_v03.xsd&quot; xmlns:ns2=&quot;http://nfe.sjp.pr.gov.br/tipos_v03.xsd&quot; xmlns:ns3=&quot;http://www.w3.org/2000/09/xmldsig#&quot;&gt; &lt;ns1:ListaNfse&gt; &lt;ns1:CompNfse xmlns:default=&quot;http://nfe.sjp.pr.gov.br/tipos_v03.xsd&quot;&gt; &lt;ns2:Nfse xmlns=&quot;http://nfe.sjp.pr.gov.br/tipos_v03.xsd&quot;&gt; &lt;ns2:InfNfse Id=&quot;1&quot;&gt; &lt;ns2:Numero&gt;202000000000001&lt;/ns2:Numero&gt; &lt;ns2:CodigoVerificacao&gt;500113029&lt;/ns2:CodigoVerificacao&gt; &lt;ns2:DataEmissao&gt;2020-07-16T15:47:41&lt;/ns2:DataEmissao&gt; &lt;ns2:IdentificacaoRps&gt; &lt;ns2:Numero&gt;78&lt;/ns2:Numero&gt; &lt;ns2:Serie&gt;0&lt;/ns2:Serie&gt; &lt;ns2:Tipo&gt;1&lt;/ns2:Tipo&gt; &lt;/ns2:IdentificacaoRps&gt; &lt;ns2:NaturezaOperacao&gt;1&lt;/ns2:NaturezaOperacao&gt; &lt;ns2:RegimeEspecialTributacao&gt;1&lt;/ns2:RegimeEspecialTributacao&gt; &lt;ns2:OptanteSimplesNacional&gt;2&lt;/ns2:OptanteSimplesNacional&gt; &lt;ns2:IncentivadorCultural&gt;2&lt;/ns2:IncentivadorCultural&gt; &lt;ns2:Competencia&gt;2020-07-16&lt;/ns2:Competencia&gt; &lt;ns2:Servico&gt; &lt;ns2:Valores&gt; &lt;ns2:ValorServicos&gt;104.95&lt;/ns2:ValorServicos&gt; &lt;ns2:IssRetido&gt;2&lt;/ns2:IssRetido&gt; &lt;ns2:ValorIss&gt;5.25&lt;/ns2:ValorIss&gt; &lt;ns2:BaseCalculo&gt;104.95&lt;/ns2:BaseCalculo&gt; &lt;ns2:Aliquota&gt;0.05&lt;/ns2:Aliquota&gt; &lt;ns2:ValorLiquidoNfse&gt;104.95&lt;/ns2:ValorLiquidoNfse&gt; &lt;/ns2:Valores&gt; &lt;ns2:ItemListaServico&gt;14.01&lt;/ns2:ItemListaServico&gt; &lt;ns2:CodigoCnae&gt;4520001&lt;/ns2:CodigoCnae&gt; &lt;ns2:CodigoTributacaoMunicipio&gt;452000101&lt;/ns2:CodigoTributacaoMunicipio&gt; &lt;ns2:Discriminacao&gt;Vendedor: V00130 - ARTHUR VALASKI Pedido: T 000027/00 Fatura: 01-15/08/2020- 26.23 Fatura: 02-14/09/2020- 26.24 Fatura: 03-14/10/2020- 26.24 Fatura: 04-13/11/2020- 26.24&lt;/ns2:Discriminacao&gt; &lt;ns2:CodigoMunicipio&gt;4125506&lt;/ns2:CodigoMunicipio&gt; &lt;/ns2:Servico&gt; &lt;ns2:PrestadorServico&gt; &lt;ns2:IdentificacaoPrestador&gt; &lt;ns2:Cnpj&gt;10703580000641&lt;/ns2:Cnpj&gt; &lt;ns2:InscricaoMunicipal&gt;83990&lt;/ns2:InscricaoMunicipal&gt; &lt;/ns2:IdentificacaoPrestador&gt; &lt;ns2:RazaoSocial&gt;VGR SERVIÇOS AUTOMOTIVOS LTDA&lt;/ns2:RazaoSocial&gt; &lt;ns2:Endereco&gt; &lt;ns2:Endereco&gt; AVENIDA das Torres&lt;/ns2:Endereco&gt; &lt;ns2:Numero&gt;2100&lt;/ns2:Numero&gt; &lt;ns2:Bairro&gt;São Pedro&lt;/ns2:Bairro&gt; &lt;ns2:CodigoMunicipio&gt;4125506&lt;/ns2:CodigoMunicipio&gt; &lt;ns2:Uf&gt;PR&lt;/ns2:Uf&gt; &lt;ns2:Cep&gt;83005450&lt;/ns2:Cep&gt; &lt;/ns2:Endereco&gt; &lt;ns2:Contato&gt; &lt;ns2:Telefone&gt;04133821848&lt;/ns2:Telefone&gt; &lt;ns2:Email&gt;rogerio@baraopneus.com.br&lt;/ns2:Email&gt; &lt;/ns2:Contato&gt; &lt;/ns2:PrestadorServico&gt; &lt;ns2:TomadorServico&gt; &lt;ns2:IdentificacaoTomador&gt; &lt;ns2:CpfCnpj&gt; &lt;ns2:Cnpj&gt;03938055000135&lt;/ns2:Cnpj&gt; &lt;/ns2:CpfCnpj&gt; &lt;/ns2:IdentificacaoTomador&gt; &lt;ns2:RazaoSocial&gt;SOFT SISTEMAS ELETRONICOS LTDA(C23693)&lt;/ns2:RazaoSocial&gt; &lt;ns2:Endereco&gt; &lt;ns2:Endereco&gt;RUA FARRAPOS&lt;/ns2:Endereco&gt; &lt;ns2:Numero&gt;485&lt;/ns2:Numero&gt; &lt;ns2:Bairro&gt;CENTRO&lt;/ns2:Bairro&gt; &lt;ns2:CodigoMunicipio&gt;4118501&lt;/ns2:CodigoMunicipio&gt; &lt;ns2:Uf&gt;PR&lt;/ns2:Uf&gt; &lt;ns2:Cep&gt;85501340&lt;/ns2:Cep&gt; &lt;/ns2:Endereco&gt; &lt;ns2:Contato&gt; &lt;ns2:Telefone&gt;04135448500&lt;/ns2:Telefone&gt; &lt;ns2:Email&gt;taciane.ricci@softeletronica.com.br&lt;/ns2:Email&gt; &lt;/ns2:Contato&gt; &lt;/ns2:TomadorServico&gt; &lt;ns2:OrgaoGerador&gt; &lt;ns2:CodigoMunicipio&gt;4125506&lt;/ns2:CodigoMunicipio&gt; &lt;ns2:Uf&gt;PR&lt;/ns2:Uf&gt; &lt;/ns2:OrgaoGerador&gt; &lt;/ns2:InfNfse&gt; &lt;/ns2:Nfse&gt; &lt;/ns1:CompNfse&gt; &lt;/ns1:ListaNfse&gt;&lt;/ns1:ConsultarLoteRpsResposta&gt;</return></ns1:ConsultarLoteRpsV3Response></SOAP-ENV:Body></SOAP-ENV:Envelope>"""
        
    """
    ..######..##.....##..######..########..#######..##.....##
    .##....##.##.....##.##....##....##....##.....##.###...###
    .##.......##.....##.##..........##....##.....##.####.####
    .##.......##.....##..######.....##....##.....##.##.###.##
    .##.......##.....##.......##....##....##.....##.##.....##
    .##....##.##.....##.##....##....##....##.....##.##.....##
    ..######...#######...######.....##.....#######..##.....##
    """
    # !Verificar se essa regra não tem que estar no cobol
    #def IssRetido(self, value):
    #    return 1 if value != 0 else 2
        
        
    def valoriss_retido(self, path, value):
        #_utils.eprint(self.IssRetido)
        return "" if self.IssRetido == '2' else path
        
    
        
    def controle_rps(self, path, value ):
        return path.format(id = int(value))
        
        
        
    
    def tipo_pessoa_tomador_path(self, path,value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj"))
        return path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj")
        
    
    def cpfcnpj(self, value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(value[::-1][0:11][::-1] if tipo_pessoa == "F" else value)
        return value[::-1][0:11][::-1] if tipo_pessoa == "F" else value
        
        
    
    def mensagem_retorno_codigo(self, value):
        return self.handle_output(value, "Codigo")
    
    
    def mensagem_retorno_correcao(self, value):
        return self.handle_output(value, "Correcao")
        
        
    
    def mensagem_retorno_mensagem(self, value):
        return self.handle_output(value, "Mensagem")
        
        
    def handle_output(self, value, prop) :
        if not value:
            return ""
            
        if isinstance(value, list) :
            codigos = [item[prop]['$'] for item in value]
            #print(value);exit()
            return "&".join(codigos)
        else :
            return value[prop]['$']
            
            
    