#!/bin/bash
cnpjs="$(ls /var/www/html/nf/NF_V3/NF_V3_dados/config_cliente/*.ini | cut -d '/' -f9 | cut -d '.' -f1)"
diretorio="/var/www/html/nf/NF_V3/NF_V3_dados/config_cliente"

for cnpj in $cnpjs; do
        temnfe="$(cat $diretorio/$cnpj.ini | grep '\[nfe\]')"
        if [[ $temnfe == "" ]]; then 
                echo "editando $cnpj"
                sed -ir "/mdfe\]/i [nfe]\nversao=\"4.00\"\npacote=\"PL_009_V4_2017_001\"\nversao_soap=\"2\"\ndir_retorno=\"/var/www/html/nf/NF_V3/NF_V3_dados/temp/nfe/\"\ndir_saida_cobol=\"/user/nfe/$cnpj/CaixaSaida/Sefaz/\"\ndir_retorno_consulta_lote=\"/user/nfe/$cnpj/CaixaSaida/Sefaz/NFER/\"\nenvioLoteSincrono=\"0\"\ntemp_nota=\"/user/nfe/$cnpj/CaixaSaida/Temporario/\"\n" $diretorio/$cnpj.ini
        fi
                
                
done

