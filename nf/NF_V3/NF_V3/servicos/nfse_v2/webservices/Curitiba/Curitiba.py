from nfse import nfse
from libs.gn_request import Request

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902 consultar "20042802000171|06911730|2|4559|637298143366160922" /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902 consultar 'cpfcnpj=20042802000171&inscricaomunicipal=06911730&ambiente=2&controle=4559&protocolo=637298143366160922' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902 consultar 'cpfcnpj=20042802000171&inscricaomunicipal=06911730&ambiente=2&controle=4563&protocolo=637324213549473993' /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug

# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902 cancelar "cpfcnpj|inscricaomunicipal|ambiente|controle|protocolo&20042802000171|06911730|2|4559|637298143366160922" /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug
# python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902  recepcionar /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/ctba.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug


#python3 /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/bootstrap_nfse.py 4106902  recepcionar /user/nfse/84818889000109/CaixaEntrada/TVE436S-20200812-141856.xml  /var/www/html/nf/NF_V3/NF_V3/servicos/nfse_v2/.ignore/saida.txt debug

# 
class Curitiba(nfse) : # 4106902
    
    def __init__(self, params) :
        super(Curitiba, self).__init__(params)
        
        
    @nfse.error(Codigo = "ESD200", Mensagem ="Erro ao criar configuracoes do header")
    def set_headers(self) : # 4106902
        
        """ pfx      = self.fix_str_ini(self.cfg_client_ini['certificado']['arquivo_certificado'])
        password = self.fix_str_ini(self.cfg_client_ini['certificado']['senha'])
        # --
        ca, cert, cakey, key = self.get_certificate(pfx, password)
        """
        SOAPAction = self.ws_send.get('SOAPAction')
        
        # --
        self.request = Request(
            cert    = (self.cert_file, self.key_file),
            headers =   {
                #"POST"              : "/nfse_ws/nfsews.asmx HTTP/1.1",
                #"Host"              : "isscuritiba.curitiba.pr.gov.br",#"200.140.228.224",
                "Content-Type"      : "text/xml;charset=UTF-8",
                "SOAPAction"        : "http://www.e-governeapps2.com.br/{}".format(SOAPAction)
            }
        ) 
        

    
    @nfse.template(
        source = """
        <ConsultarLoteRpsEnvio>
            <Prestador>
                <Cnpj>{cpfcnpj}</Cnpj>
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
        <CancelarLoteRpsEnvio xmlns="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://isscuritiba.curitiba.pr.gov.br/iss/nfse.xsd">
            <LoteRps>
                <Protocolo>{protocolo}</Protocolo>
                <Cnpj>{cpfcnpj}</Cnpj>;
                <InscricaoMunicipal>{inscricaomunicipal}</InscricaoMunicipal>
            </LoteRps>
        </CancelarLoteRpsEnvio>
        """
    )
    def cancelar(self) :
        return self.file_data
        
    
    @nfse.template(
        source = """
        <?xml version="1.0" encoding="utf-8"?>
        <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
            xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
            <soap12:Body>
                <{service} xmlns="http://www.e-governeapps2.com.br/">{mainXML}</{service}>
            </soap12:Body>
        </soap12:Envelope>"""
    )
    def soap(self) :
        
        SOAPAction = self.ws_send.get('SOAPAction')
        return {
            "mainXML" : self.xml_send,
            "service" : SOAPAction 
        }
        
        
    
    # ---- PARA TESTE  ---- #
    #envio retorno falho varios erros
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><RecepcionarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><RecepcionarLoteRpsResult><NumeroLote xsi:nil="true" /><DataRecebimento xsi:nil="true" /><ListaMensagemRetorno><MensagemRetorno><Codigo>E512</Codigo><Mensagem>A lista de RPS encontra-se vazia.</Mensagem><Correcao>Informe ao menos 1 (hum) RPS no item ListaRps e retransmita.</Correcao></MensagemRetorno><MensagemRetorno><Codigo>E513</Codigo><Mensagem>A quantidade de RPS informada no cabe\xc3\xa7alho n\xc3\xa3o confere com a quantidade de RPS enviada.</Mensagem><Correcao>Informe a quantidade correta de RPS para o lote e retransmita.</Correcao></MensagemRetorno><MensagemRetorno><Codigo>E514</Codigo><Mensagem>O lote n\xc2\xba 4558 j\xc3\xa1 foi recebido anteriormente.</Mensagem><Correcao>Informe outro n\xc3\xbamero para o lote e retransmita.</Correcao></MensagemRetorno></ListaMensagemRetorno></RecepcionarLoteRpsResult></RecepcionarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # envio retorno falho
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><RecepcionarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><RecepcionarLoteRpsResult><NumeroLote xsi:nil="true" /><DataRecebimento xsi:nil="true" /><ListaMensagemRetorno><MensagemRetorno><Codigo>E512</Codigo><Mensagem>A lista de RPS encontra-se vazia.</Mensagem><Correcao>Informe ao menos 1 (hum) RPS no item ListaRps e retransmita.</Correcao></MensagemRetorno></ListaMensagemRetorno></RecepcionarLoteRpsResult></RecepcionarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # envio sucesso
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><RecepcionarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><RecepcionarLoteRpsResult><NumeroLote>4558</NumeroLote><DataRecebimento>2020-07-07T17:41:32.4385201-03:00</DataRecebimento><Protocolo>637297404924385201</Protocolo></RecepcionarLoteRpsResult></RecepcionarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # Consulta de nfese com falha
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><ConsultarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><ConsultarLoteRpsResult><ListaMensagemRetorno><MensagemRetorno><Codigo>E36</Codigo><Mensagem>Campo ISSRetido inv\xc3\xa1lido. - POSI\xc3\x87\xc3\x83O RPS - 1</Mensagem><Correcao>Utilize um dos tipos: 1 para ISS Retido ou 2 para ISS n\xc3\xa3o Retido.</Correcao></MensagemRetorno></ListaMensagemRetorno></ConsultarLoteRpsResult></ConsultarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # Consulta de sucesso
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><ConsultarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><ConsultarLoteRpsResult><ListaNfse><CompNfse><tcCompNfse><Nfse><InfNfse><Numero>255</Numero><CodigoVerificacao>W7DLU20R</CodigoVerificacao><DataEmissao>2020-07-08T14:12:16</DataEmissao><IdentificacaoRps><Numero>4559</Numero><Serie>0</Serie><Tipo>1</Tipo></IdentificacaoRps><DataEmissaoRps>2020-07-08T14:12:16</DataEmissaoRps><NaturezaOperacao>1</NaturezaOperacao><RegimeEspecialTributacao>0</RegimeEspecialTributacao><OptanteSimplesNacional>1</OptanteSimplesNacional><IncentivadorCultural>2</IncentivadorCultural><Competencia>0001-01-01T00:00:00</Competencia><NfseSubstituida>0</NfseSubstituida><Servico><Valores><ValorServicos>1000.00</ValorServicos><ValorDeducoes>0.00</ValorDeducoes><ValorPis>0.00</ValorPis><ValorCofins>0.00</ValorCofins><ValorInss>0.00</ValorInss><ValorIr>0.00</ValorIr><ValorCsll>0.00</ValorCsll><IssRetido>2</IssRetido><ValorIss>20.00</ValorIss><ValorIssRetido>0.00</ValorIssRetido><OutrasRetencoes>0.00</OutrasRetencoes><BaseCalculo>1000.00</BaseCalculo><Aliquota>0.02</Aliquota><ValorLiquidoNfse>1000.00</ValorLiquidoNfse><DescontoIncondicionado>0.00</DescontoIncondicionado><DescontoCondicionado>0.00</DescontoCondicionado></Valores><ItemListaServico>1401</ItemListaServico><CodigoCnae>331980000</CodigoCnae><Discriminacao>Pedido:   I 004040/00                                                           \\r\\nFatura: 01-30/06/2020-     1.000,00</Discriminacao><CodigoMunicipio>4106902</CodigoMunicipio></Servico><ValorCredito>0</ValorCredito><PrestadorServico><IdentificacaoPrestador><Cnpj>20042802000171</Cnpj><InscricaoMunicipal>070906911730</InscricaoMunicipal></IdentificacaoPrestador><NomeFantasia>PJ2731374</NomeFantasia><Endereco><Endereco>DOUTOR EGON ARMANDO KRUEGER</Endereco><Numero>144</Numero><Bairro>CIDADE INDUSTRIAL</Bairro><CodigoMunicipio>4106902</CodigoMunicipio><Uf>PR</Uf><Cep>81350020</Cep></Endereco></PrestadorServico><TomadorServico><IdentificacaoTomador><CpfCnpj><Cnpj>84818889000109</Cnpj></CpfCnpj></IdentificacaoTomador><RazaoSocial>SOFTDIB INFORMATICA LTDA(F00228)</RazaoSocial><Endereco><Endereco>RUA EMANUEL KANT</Endereco><Numero>60</Numero><Complemento>H.A.OFICES LINHA VERDE</Complemento><Bairro>CAPAO RASO</Bairro><CodigoMunicipio>4106902</CodigoMunicipio><Uf>PR</Uf><Cep>81020670</Cep></Endereco><Contato><Telefone>4132766457</Telefone><Email>dib@softdib.com.br</Email></Contato></TomadorServico></InfNfse></Nfse></tcCompNfse></CompNfse></ListaNfse><ListaMensagemRetorno /></ConsultarLoteRpsResult></ConsultarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # Cancelamento com erro
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><CancelarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><CancelarLoteRpsResult><DataRecebimento xsi:nil="true" /><Protocolo /><ListaMensagemRetorno><MensagemRetorno><Codigo>E544</Codigo><Mensagem>Para cancelar o lote de RPS \xc3\xa9 necess\xc3\xa1rio que o mesmo j\xc3\xa1 tenha sido processado com sucesso.</Mensagem><Correcao>Aguarde at\xc3\xa9 que o lote seja processado com sucesso, caso o mesmo ainda n\xc3\xa3o tenha sido processado.</Correcao></MensagemRetorno></ListaMensagemRetorno></CancelarLoteRpsResult></CancelarLoteRpsResponse></soap:Body></soap:Envelope>"""
    # Cancelamento sucesso
    #example_content = """<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><CancelarLoteRpsResponse xmlns="http://www.e-governeapps2.com.br/"><CancelarLoteRpsResult><DataRecebimento>2020-07-08T14:32:22.9529742-03:00</DataRecebimento><Protocolo>637298155429529742</Protocolo></CancelarLoteRpsResult></CancelarLoteRpsResponse></soap:Body></soap:Envelope>'"""
        
        
    """
    ..######..##.....##..######..########..#######..##.....##
    .##....##.##.....##.##....##....##....##.....##.###...###
    .##.......##.....##.##..........##....##.....##.####.####
    .##.......##.....##..######.....##....##.....##.##.###.##
    .##.......##.....##.......##....##....##.....##.##.....##
    .##....##.##.....##.##....##....##....##.....##.##.....##
    ..######...#######...######.....##.....#######..##.....##
    """
    
    def deducoes_path(self, path, value):
        return path if self.NumeroDeducao != "0" else ""
        
    
    # php -q /var/www/html/nf/nfse/IntegradorNovo.php /user/nfse/84818889000109/CaixaEntrada/TVE436S-20200812-164019.xml /user/nfse/84818889000109/CaixaSaida/TVE436S-20200812-164019.txt 
    
    def tipo_pessoa_tomador_path(self, path,value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj"))
        return path.format(tipo = "Cpf" if tipo_pessoa == "F" else "Cnpj")
        
        
    def cpfcnpj(self, value) :
        tipo_pessoa = self.file_data['nfse']['tomador']['tipo']['$']
        #_utils.eprint(value[::-1][0:11][::-1] if tipo_pessoa == "F" else value)
        """ if value and int(value):
            return '' """
        
        return value[::-1][0:11][::-1] if tipo_pessoa == "F" else value
        
    
    def estado_path(self, path, value) :
        return path if value and value != 'EX' else ""
        
        
    
    # !Verificar se essa regra não tem que estar no cobol
    def IssRetido(self, value):
        return '1' if value != 0 else '2'
            
        
    def mensagem_retorno_codigo(self, value):
        return self.handle_output(value, "Codigo")
        
    
    def mensagem_retorno_correcao(self, value):
        return self.handle_output(value, "Correcao")
        
    def mensagem_retorno_mensagem(self, value):
        return self.handle_output(value, "Mensagem")
        
        
    def handle_output___(self, value, prop) :
        if not value:
            return ""
            
        if isinstance(value, list) :
            codigos = [item[prop]['$'] for item in value]
            #print(value);exit()
            return "&".join(codigos)
        else :
            return value[prop]['$']
        
        
    
    """ 
        
    @nfse.logs("Craindo arquivos .pem ")
    def create_pem_php(self) :
        " exec("php ../ferramentas/cria_pem.php ".
            $wsConfigs['cli_config']['certificado']['arquivo_certificado']." ".
            $wsConfigs['cli_config']['certificado']['senha']
        ); "
        
        pfx      = "/var/www/html/nf/nfse/certificados/00000000000000.pfx"
        password = '1234'
        
        fileToRun = self.nfse_v2_path + "../../../ferramentas/cria_pem.php"
        argsToRun = [
                'php'
            ,    "{}".format(fileToRun)
            ,    "{}".format(pfx)
            ,    "{}".format(password)
            
        ]
        
        
        #Se retornar qualquer mensagem houve um erro na criacao do certificado
        hasError = subprocess.run(argsToRun , stdout=subprocess.PIPE).stdout.decode('utf-8')
        if hasError :
            print(hasError)
            exit()
    """
    
    
    
    """  def render_xml_rps_before(self) :
        print("init decorator")
        
    def render_xml_rps_after(self) :
        print(self.rps)
        
    def generate_data_rps_before(self):
        print("init generate_data_rps")
        
    def generate_data_rps_after(self):
        print("generated generate_data_rps") """