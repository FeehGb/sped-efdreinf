#!/bin/bash
# Author: Fernando H. Crozetta
# Data  : 06/06/2013
# ---------------------------------------------------------------------------- #
# Realizar o backup da base de dados do Mysql
# ---------------------------------------------------------------------------- #

# ---{ Definicoes }--- #
SERVIDOR='<servidor>'
USUARIO='<usuario>'
SENHA='<senha>'
BASE='<base>'
DIRBKP="/user/nfse/backup/"
# ---{ Funcoes }--- #
DIA_SEMANA=`date +%a`             
case $DIA_SEMANA in               
                Dom|Sun) DIA=dom;;
                Seg|Mon) DIA=seg;;
                Ter|Tue) DIA=ter;;
                Qua|Wed) DIA=qua;;
                Qui|Thu) DIA=qui;;
                Sex|Fri) DIA=sex;;
                Sab|Sat) DIA=sab;;
esac                              
# ---{ Main }--- #

mysqldump --host=$SERVIDOR --user=$USUARIO --password=$SENHA --extended-insert --database $BASE > $DIRBKP/$BASE-cron-$DIA.sql
