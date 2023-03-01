<?php

require '/var/www/html/nf/NF_V3/NF_V3/servicos/gnre/vendor/autoload.php';

use Sped\Gnre\Parser\SefazRetorno;

$resultado = '100011SP10008020002336124000178KOMATSU BRASIL INTERNATIONAL LTDA                           AVENIDA MANUEL BANDEIRA, 291, BLOCO D TE                    SAO PAULO                                         SP053170200110210580000000000000000000   
PILHAS, BATERIAS ELETRICAS E ACUMULADORES ELETRICOS
             000000000000232191ICMS DE SUBSTITUICAO DE MATERI
                                                                                                                       25102019000000000102019000000000000029444000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000003000000000000000000000000000

20001c01_UfFavorecida              201Esta UF nao gera GNRE online.

9191152529100012b8e89800d77733025e03a9991ebd9910237bef36836d5ae08448120e1161cbb';

$parser = new SefazRetorno($resultado);
$lote = $parser->getLote();

$consulta = new Sped\Gnre\Sefaz\Consulta();

#header('Content-Type: text/xml');
echo $lote->toXml();
// /raiz/var/www/html/nf/NF_V3/NF_V3/servicos/gnre/vendor