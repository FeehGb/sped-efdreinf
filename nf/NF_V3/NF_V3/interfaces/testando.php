<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://pre-reinf.receita.economia.gov.br/recepcao/lotes/',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'
<?xml version="1.0" encoding="UTF-8"?>
<Reinf xmlns="http://www.reinf.esocial.gov.br/schemas/envioLoteEventosAssincrono/v1_00_00">
<envioLoteEventos>
<ideContribuinte>z
<tpInsc>1</tpInsc>
<nrInsc>99999999</nrInsc>
</ideContribuinte>
<eventos>   

    <evento Id="ID1999999990000002018100414565100001">
        <Reinf
            xmlns="http://www.reinf.esocial.gov.br/schemas/evtInfoContribuinte/v1_00_00">
            <evtInfoContri id="ID1999999990000002018100414565100001">
                <ideEvento>
                    <tpAmb>2</tpAmb>
                    <procEmi>1</procEmi>
                    <verProc>0_1_0</verProc>
                </ideEvento>
                <ideContri>
                    <tpInsc>1</tpInsc>
                    <nrInsc>99999999</nrInsc>
                </ideContri>
                <infoContri>
                    <inclusao>
                        <idePeriodo>
                            <iniValid>2017-01</iniValid>
                            <fimValid>2017-12</fimValid>
                        </idePeriodo>
                        <infoCadastro>
                            <classTrib>01</classTrib>
                            <indEscrituracao>0</indEscrituracao>
                            <indDesoneracao>0</indDesoneracao>
                            <indAcordoIsenMulta>0</indAcordoIsenMulta>
                            <indSitPJ>0</indSitPJ>
                            <contato>
                                <nmCtt>Fulano de Tal</nmCtt>
                                <cpfCtt>12345678901</cpfCtt>
                                <foneFixo>115555555</foneFixo>
                                <foneCel>1199999999</foneCel>
                                <email>fulano@email.com</email>
                            </contato>
                            <softHouse>
                                <cnpjSoftHouse>12345678901234</cnpjSoftHouse>
                                <nmRazao>Razao Social</nmRazao>
                                <nmCont>Fulano de Tal</nmCont>
                                <telefone>115555555</telefone>
                                <email>fulano@email.com</email>
                            </softHouse>
                            <infoEFR>
                                <ideEFR>N</ideEFR>
                                <cnpjEFR>12345678901234</cnpjEFR>
                            </infoEFR>
                        </infoCadastro>
                    </inclusao>
                </infoContri>
            </evtInfoContri>
            <Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
                <SignedInfo>
                    <CanonicalizationMethod
                        Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
                    <SignatureMethod
                        Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" />
                    <Reference URI="#ID1999999990000002018100414565100001">
                        <Transforms>
                            <Transform
                                Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" />
                            <Transform
                                Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315" />
                        </Transforms>
                        <DigestMethod
                            Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" />
                        <DigestValue>
                            EACdv5tsNbqpgvOdtVQa+ZQtSxwYmjk/tIq3hg6LZtc=</DigestValue>
                    </Reference>
                </SignedInfo>
                <SignatureValue>
                    andR4VT+wTxBbWyamv3rR1d+Sc/J4CyxS7AFrBf2DLYfRylpJzIcEjJKf6In9/GW+84eA60RS3eUlyuTfhtrWc0RovfQMxIqXgZSz7aul3xjkEvPyMbdlmjiDOgN6OOdQfPahcKmJbrq1i93p278rkNRTGVdTjtwu1LbKBrcP5qRsSmTPcznUDQTGwML4QwyzbwSKKiAvLYmBUhteU2c6pSn6DkvcY+V2I3dJiOujafN694vylxdSQdCBk6zcbPGwr0AxOaD3wZdU0fiuxf/K83nYbTBtISl2QnoKTRldwqZyd0L+750RFiNcoZrirTgeQ8nV50j5N29TRLXJJuLwA==</SignatureValue>
                <KeyInfo>
                    <X509Data>
                        <X509Certificate>
                            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</X509Certificate>
                    </X509Data>
                </KeyInfo>
            </Signature>
        </Reinf>
    </evento>
</eventos>
</envioLoteEventos>
</Reinf>

',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/xml'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
