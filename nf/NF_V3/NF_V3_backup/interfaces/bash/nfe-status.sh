

# echo '{ "Version Number": "1.2.3" }' | jq '."Version Number"'

xmlEnvio='<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"><soap12:Header><nfeCabecMsg xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4"/></soap12:Header><soap12:Body><nfeDadosMsg xmlns="http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4"><consStatServ xmlns="http://www.portalfiscal.inf.br/nfe" versao="4.00"><tpAmb>1</tpAmb><cUF>41</cUF><xServ>STATUS</xServ></consStatServ></nfeDadosMsg></soap12:Body></soap12:Envelope>'
tempXML='./tempXML.xml'
echo $xmlEnvio > $tempXML 
tamanho=$(cat $tempXML | awk '{print length}')





read x

url='https://homologacao.nfe.sefa.pr.gov.br/nfe/NFeStatusServico4'

header1="'Content-Type: application/soap+xml;charset=utf-8;action=\"http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4\"'"
header2="'SOAPAction: \"http://www.portalfiscal.inf.br/nfe/wsdl/NFeStatusServico4/wsdl/nfeStatusServicoNF4nfeStatusServico\"'"
header3="'Content-length: $tamanho'"
header4="'Cache-Control : no-cache'"
header5="'Pragma: no-cache'"

cert='/var/www/html/nf/nfse/certificados/07560310000100.pfx'
senha="1234"
openssl pkcs12 -in $cert -out ./ca.pem     -cacerts -nokeys
openssl pkcs12 -in $cert -out ./client.pem -clcerts -nokeys
openssl pkcs12 -in $cert -out ./key.pem    -nocerts

curl                   \
    --header $header1  \
    --header $header2  \
    --header $header3  \
    --header $header4  \
    --header $header5  \
    -X       POST      \
    --data   $tempXML  \
    –key    key.pem    \
    –cacert ca.pem     \
    –cert   client.pem \
    $url               \
    -k                 \
    -v                 \
;

echo $comando




# rm $tempXML ./ca.pem ./client.pem ./key.pem

