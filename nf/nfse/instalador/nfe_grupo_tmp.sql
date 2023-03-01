-- NÃO APAGAR - Responsável por criar tabelas de instalação do sistema
--
-- Estrutura para Criar Base de Dados nfe_barao para cada cliente e suas tabelas
--
-- Criação Guilherme Silva 24/02/2014
--
-- Histórico de Alterações
--  Descrição                           Responsável e Data
--
-- Banco de Dados: `nfe_barao`
--

CREATE DATABASE IF NOT EXISTS nfe_barao;

-- --------------------------------------------------------

--
-- Estrutura da tabela `CONTRIBUINTE`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`CONTRIBUINTE` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ do Contribuinte, Emissor da Nota Fiscal.',
  `ambiente` varchar(1) NOT NULL COMMENT 'Tipo de Ambiente:\n0-Homologação.\n1-Produção.',
  `uf` varchar(2) NOT NULL,
  `cod_emp_fil_softdib` varchar(6) DEFAULT NULL COMMENT 'Código da Empresa e Filial que consta no Softdib.',
  `razao_social` varchar(100) NOT NULL COMMENT 'Razão Social da Empresa.',
  `certificado_tipo` varchar(2) NOT NULL COMMENT 'A1;\nA3;\nAinda não foi encontrada nenhum programa que autentique com sucesso no certificado A3, apenas A1.',
  `certificado_caminho` text NOT NULL COMMENT 'Caminho do Certificado Digital (PFX).\n( ./nfe/certs)\n',
  `certificado_senha` varchar(50) NOT NULL COMMENT 'Senha do certificado digital utilizado para emissão da NF.',
  `contigencia` varchar(2) NOT NULL COMMENT '01 - Normal    * 02 - FS    * 03 - DPEC    * 04 - SCAN    * 05 - FS-DA	 * 06 - SVC-AN  * 07 - SVC-RS',
  `data_hora_contingencia` datetime NOT NULL COMMENT 'Data e Hora da ultima alteração do status da contigencia',
  `justificativa_contingencia` varchar(255) NOT NULL COMMENT 'Justificativa da entrada em contigência',
  `pacote_xsd` varchar(10) NOT NULL COMMENT 'Nome do Pacote XSD\nÚltima Versão: 6r.\nPL_006S',
  `email_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome de usuário do emitente da Nota Fiscal.',
  `email_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do Email do emitente da NF.',
  `email_remetente` varchar(100) DEFAULT NULL,
  `email_smtp` varchar(100) DEFAULT NULL COMMENT 'Caminho do Servidor SMTP do email do emitente da NF.',
  `email_porta` varchar(4) DEFAULT NULL COMMENT 'Código da Porta do Servidor de email do emitente da NF.',
  `email_ssl` varchar(1) DEFAULT NULL COMMENT 'Flag se a conexão é SSL ou não.\n0-Não;\n1-Sim.',
  `email_conf_recebimento` varchar(1) DEFAULT NULL COMMENT 'Flag se habilita a confirmação de recebimento do Proxy.',
  `proxy_servidor` varchar(100) DEFAULT NULL COMMENT 'Servidor Proxy para conexão com Internet.',
  `proxy_porta` varchar(4) DEFAULT NULL COMMENT 'Porta do Servidor Proxy:\nEx.: 4403 ',
  `proxy_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome do usuário do Proxy, caso o servidor exija um proxy para conexão com a Internet.',
  `proxy_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do usuário Proxy para conexão com Internet.',
  `diretorio_integracao` text NOT NULL COMMENT 'Caminho do diretório onde fará a integração.',
  `diretorio_backup` text NOT NULL COMMENT 'Diretório backup onde serão salvas as notas fiscais autorizadas, canceladas, inutilizadas, etc.',
  `diretorio_importacao` text NOT NULL COMMENT 'Diretório onde serão salvas as notas de terceiros que irão que poderão ser importadas para o sistema.',
  `diretorio_base` text NOT NULL COMMENT 'Caminho do diretório base do cliente /user/base/.',
  `danfe_layout_caminho` text NOT NULL COMMENT 'Caminho do Layout da Danfe a ser impresso.',
  `danfe_layout_fs_da` text,
  `danfe_logo_caminho` text COMMENT 'Caminho da Logo da Danfe.',
  `danfe_qtde_vias` int(11) DEFAULT NULL COMMENT 'Quantidade de vias que serão impressas da DANFE quando submitida a impressão automática.',
  `danfe_automatica` varchar(1) DEFAULT NULL COMMENT 'Seleciona se a impressão da DANFE é automática.\n0 - Não.\n1 - Sim.',
  `server_impressao` text COMMENT 'Caminho do Servidor de impressão onde será impressa a DANFE automáticamente.\nEx.: \\\\192.168.1.24\\HPLaserJet',
  `server_impressao_comando` text COMMENT 'Quando necessitar de um server de impressao',
  `ativo` varchar(1) NOT NULL COMMENT 'Classifica o usuário em Ativo ou inativo (S/N). Para usuários inativos há restrição de importação de Notas Fiscais.',
  `consulta_destinatario` varchar(1)  NULL COMMENT 'Flag para identificar se foi bloqueada a consulta de nfes destinadas',
  `consulta_destinatario_hora` datetime  NULL COMMENT 'Data e hora da ultima consulta de destinatario do SEFAZ',
  PRIMARY KEY (`cnpj`),
  UNIQUE KEY `cnpj` (`cnpj`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `CRITICA`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`CRITICA` (
  `EVENTO_NOTA_FISCAL_cnpj_emitente` varchar(14) NOT NULL,
  `EVENTO_NOTA_FISCAL_numero_nota` varchar(9) NOT NULL,
  `EVENTO_NOTA_FISCAL_serie_nota` varchar(3) NOT NULL,
  `EVENTO_NOTA_FISCAL_ambiente` varchar(1) NOT NULL COMMENT 'Identificação do Ambiente: 1 – Produção / 2 - Homologação',
  `sequencia` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_referencia` varchar(10) DEFAULT NULL COMMENT 'Código de Referância da Crítica.\nEx.: E201, E99, 501, etc.',
  `descricao` text COMMENT 'Descrição da Crítica de Validação.',
  `data_hora_critica` datetime DEFAULT NULL COMMENT 'Data e hora que foi postada a crítica.',
  `status` varchar(1) NOT NULL COMMENT 'Para informar se já foi notificada a Crítica (S/N)',
  PRIMARY KEY (`sequencia`),
  KEY `fk_CRITICA_EVENTO1_idx` (`EVENTO_NOTA_FISCAL_cnpj_emitente`,`EVENTO_NOTA_FISCAL_numero_nota`,`EVENTO_NOTA_FISCAL_serie_nota`,`EVENTO_NOTA_FISCAL_ambiente`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=251 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `EVENTO`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`EVENTO` (
  `NOTA_FISCAL_cnpj_emitente` varchar(14) NOT NULL,
  `NOTA_FISCAL_numero_nota` varchar(9) NOT NULL,
  `NOTA_FISCAL_serie_nota` varchar(3) NOT NULL,
  `NOTA_FISCAL_ambiente` varchar(1) NOT NULL COMMENT 'IdentificaÃ§Ã£o do Ambiente: 1 â€“ ProduÃ§Ã£o / 2 - HomologaÃ§Ã£o',
  `tipo_evento` int(1) NOT NULL COMMENT 'Define o Tipo de Evento junto ao SEFAZ.\n1 - Autorizado;\n2 - Denegado;\n3 - Rejeitado;\n4 - Cancelado;\n5 - Inutilizado;\n6 - Carta CorreÃ§Ã£o;',
  `numero_sequencia` int(2) NOT NULL COMMENT 'Sequencial do evento para o mesmo tipo de evento. Para maioria dos eventos serÃ¡ 1, nos casos em que possa existir mais de um evento, como Ã© o caso da carta de correÃ§Ã£o, o autor do evento deve numerar de forma sequencial.',
  `xml_env` longtext,
  `xml_ret` longtext,
  `xml` longtext,
  `descricao` varchar(1000) DEFAULT NULL,
  `protocolo` varchar(15) DEFAULT NULL COMMENT 'Protocolo de autorizaÃ§Ã£o do SEFAZ.',
  `data_hora` datetime DEFAULT NULL COMMENT 'Data e hora do evento.',
  `status` varchar(3) DEFAULT NULL,
  `email_enviado` varchar(1) DEFAULT NULL COMMENT 'Enviado email ao destinatario',
  PRIMARY KEY (`NOTA_FISCAL_cnpj_emitente`,`NOTA_FISCAL_numero_nota`,`NOTA_FISCAL_serie_nota`,`NOTA_FISCAL_ambiente`,`tipo_evento`,`numero_sequencia`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `INUTILIZACAO`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`INUTILIZACAO` (
  `CONTRIBUINTE_cnpj` varchar(14) NOT NULL,
  `CONTRIBUINTE_ambiente` varchar(1) NOT NULL,
  `serie_nota` varchar(3) NOT NULL,
  `numero_nota_inicial` int(9) NOT NULL,
  `numero_nota_final` int(9) NOT NULL,
  `ano` int(2) DEFAULT NULL COMMENT 'Ano de inutilização da numeração',
  `justificativa` varchar(255) DEFAULT NULL COMMENT 'Jjustificativa do pedido de inutilização',
  `xml_env` longtext COMMENT 'XML de Inutilização enviado ao SEFAZ',
  `xml_ret` longtext COMMENT 'XML de inutilização retornado pelo SEFAZ.',
  `xml` longtext COMMENT 'XML completo de concatenado.',
  `modelo_nota` int(2) DEFAULT NULL,
  `protocolo` varchar(15) DEFAULT NULL COMMENT 'Protocolo de autorização do SEFAZ.',
  `data_hora` datetime DEFAULT NULL COMMENT 'Data e hora de processamento\nFormato = AAAA-MM-DDTHH:MM:SS\n\nSe Homologado, refere-se a data e hora da gravação na SEFAZ.\nEm caso de Rejeição, refere-se a data e hora do recebimento do Pedido.',
  `status` varchar(3) DEFAULT NULL COMMENT 'Código do status da resposta',
  `status_motivo` varchar(255) DEFAULT NULL COMMENT 'Descrição literal do status da resposta.',
  `uf_responsavel` int(2) DEFAULT NULL COMMENT 'Código da UF que atendeu a solicitação.',
  PRIMARY KEY (`CONTRIBUINTE_cnpj`,`CONTRIBUINTE_ambiente`,`serie_nota`,`numero_nota_inicial`),
  KEY `fk_INUTILIZACAO_CONTRIBUINTE1_idx` (`CONTRIBUINTE_cnpj`,`CONTRIBUINTE_ambiente`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `LOG`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`LOG` (
  `NOTA_FISCAL_cnpj_emitente` varchar(14) NOT NULL,
  `NOTA_FISCAL_numero_nota` varchar(9) NOT NULL,
  `NOTA_FISCAL_serie_nota` varchar(3) NOT NULL,
  `NOTA_FISCAL_ambiente` varchar(1) NOT NULL,
  `sequencia` int(11) NOT NULL AUTO_INCREMENT,
  `data_hora` datetime DEFAULT NULL,
  `evento` varchar(100) DEFAULT NULL COMMENT 'Nome do Evento que foi executado.\nEx.: Integração ERP, Enviado Sefaz, Autenticado, etc.',
  `usuario` varchar(50) DEFAULT NULL,
  `descricao` text,
  `detalhes` text,
  PRIMARY KEY (`NOTA_FISCAL_cnpj_emitente`,`NOTA_FISCAL_numero_nota`,`NOTA_FISCAL_serie_nota`,`NOTA_FISCAL_ambiente`,`sequencia`),
  KEY `fk_EVENTO_NOTA_FISCAL_idx` (`NOTA_FISCAL_cnpj_emitente`,`NOTA_FISCAL_numero_nota`,`NOTA_FISCAL_serie_nota`,`NOTA_FISCAL_ambiente`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `LOTE`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`LOTE` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `cnpj_emitente` varchar(14) NOT NULL,
  `versao` decimal(6,2) DEFAULT NULL,
  `recibo` varchar(15) DEFAULT NULL,
  `status` int(3) DEFAULT NULL,
  `ambiente` varchar(1) DEFAULT NULL COMMENT 'Identificação do Ambiente: 1 – Produção / 2 - Homologação',
  `contingencia` varchar(2) DEFAULT NULL COMMENT '01-Normal sem contigência02-DPEC03-SCAN',
  PRIMARY KEY (`id`,`cnpj_emitente`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=631 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `NOTA_FISCAL`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`NOTA_FISCAL` (
  `cnpj_emitente` varchar(14) NOT NULL,
  `numero_nota` int(9) NOT NULL,
  `serie_nota` varchar(3) NOT NULL,
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente em que foi solicitada a nota.\nP - Produção;\nH - Homologação.\n\nIdentificação do Ambiente: 1 – Produção / 2 - Homologação',
  `versao` decimal(5,2) NOT NULL COMMENT 'Versão do XML',
  `cod_empresa_filial_softdib` varchar(6) DEFAULT NULL,
  `nome_emissor` varchar(100) DEFAULT NULL,
  `cnpj_destinatario` varchar(14) NOT NULL,
  `nome_destinatario` varchar(100) DEFAULT NULL,
  `cod_destinatario` varchar(6) DEFAULT NULL COMMENT 'Código do Destinatário no Sistema Softdib',
  `email_destinatario` text NOT NULL COMMENT 'Email do destinatário',
  `status` varchar(2) NOT NULL COMMENT '01 - Recebida (recebida pela aplicação nfe);\n02 - Aguardando Sefaz (aguarda retorno validação no sefaz, não está fora);\n03 - Autorizada (emitida com sucesso);\n04 - Rejeitada (poderá ser corrigida e enviada novamente);\n05 - Denegada (foi negada e emissão p',
  `tipo_emissao` varchar(1) NOT NULL COMMENT 'Tipo de Emissão que foi submetido ao Nota Fiscal:\n\n1 – Normal – emissão normal;\n\n2 – Contingência FS – emissão em contingência com impressão do DANFE em Formulário de Segurança;\n\n3 – Contingência SCAN – emissão em contingência no Sistema de Contingência d',
  `data_emissao` date NOT NULL COMMENT 'Data e Hora da Emissão da Nota (deve ser a mesma contida na DANFE).',
  `uf_webservice` varchar(2) NOT NULL COMMENT 'Unidade Federativa do Web Service referente a nota.',
  `layout_danfe` text NOT NULL COMMENT 'Caminho no servidor do Layout da Danfe.',
  `valor_total_nfe` decimal(15,2) NOT NULL COMMENT 'Valor Total da Nota Fiscal.',
  `data_entrada_saida` datetime DEFAULT NULL COMMENT 'Data da Entrada/Saída.',
  `chave` varchar(44) NOT NULL COMMENT 'Chave de acesso da Nota Fiscal Eletrônica.',
  `numero_protocolo` varchar(15) DEFAULT NULL,
  `tipo_operacao` varchar(1) NOT NULL COMMENT '0 - Entrada\n1 - Saída',
  `xml` longtext NOT NULL COMMENT 'Campo que comporta o XML em sua última composição. Capacidade 4GB.',
  `danfe_impressa` varchar(1) DEFAULT NULL COMMENT 'Danfe impressa',
  `email_enviado` varchar(1) DEFAULT NULL COMMENT 'Enviado email ao destinatario',
  `lote_nfe` varchar(15) DEFAULT NULL,
  `observacao` longtext DEFAULT NULL,
  `CONTRIBUINTE_cnpj` varchar(14) NOT NULL,
  PRIMARY KEY (`cnpj_emitente`,`numero_nota`,`serie_nota`,`ambiente`,`CONTRIBUINTE_cnpj`),
  KEY `lote_idx` (`cnpj_emitente`,`lote_nfe`),
  KEY `cnpj_contribuinte_idx` (`CONTRIBUINTE_cnpj`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `WEB_SERVICE`
--

CREATE TABLE IF NOT EXISTS `nfe_barao`.`WEB_SERVICE` (
  `uf` varchar(4) NOT NULL,
  `versao_xml` decimal(5,2) NOT NULL COMMENT 'Versão do Web Service.Ex.: 00002.00000, 00001.00000, etc.',
  `servico` varchar(45) NOT NULL COMMENT 'Nome do Serviço.\nEx.: NfeRecepcao, NfeInutilizacao, etc.',
  `ambiente` varchar(1) NOT NULL COMMENT 'Tipo de Ambiente.\n0 - Homologação\n1 - Produção.',
  `metodo` varchar(45) NOT NULL COMMENT 'Nom edo Método de conexão apresentado no cadastro do contribuinte',
  `nome` text NOT NULL COMMENT 'Nome amigável do Web Service ou Razão Social da empresa a que pertence o CNPJ.',
  `cnpj_web_service` varchar(14) DEFAULT NULL COMMENT 'CNPJ da empresa detentora do Web Service.',
  `cod_uf_ibge` int(2) NOT NULL COMMENT 'Código da Unidade Federativa segundo o IBGE.',
  `metodo_conexao` int(1) NOT NULL COMMENT 'Método de Conexão:\n1-SOAP1\n2-SOAP2\n3-GET\n4-POST',
  `url_completa` text NOT NULL COMMENT 'URL completo.\nEx.: https://nfe.sefaz.ba.gov.br/webservices/nfenw/NfeRecepcao2.asmx, etc.',
  `situacao` int(1) NOT NULL COMMENT 'Situação em que se encontra este Web Service.\n1 - Ativo;\n2 - Inativo;',
  `xsd` text NOT NULL COMMENT 'Caminho no servidor do arquivo XSD, deve ser informado o caminho completo no servidor',
  PRIMARY KEY (`uf`,`versao_xml`,`servico`,`ambiente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Estrutura da tabela `NF_DESTINADAS`
--
CREATE TABLE IF NOT EXISTS `nfe_barao`.`NF_DESTINADAS` (
  `nsu` text NOT NULL COMMENT 'NÃºmero do NSU Ãºnico no cadastro do SEFAZ',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente produção ou homologação',
  `tipo` varchar(2) NOT NULL COMMENT 'Tipo de Documento (A - nfe autorizada; C - nfe cancelada; CC - carta de correcao)',
  `chave` varchar(44) NOT NULL COMMENT 'Chave 44 posiÃ§Ãµes da NFE',
  `emit_cpf_cnpj` varchar(14) NOT NULL COMMENT 'CPF ou CNPJ do Emitente da Nota',
  `emit_nome` text NOT NULL COMMENT 'Nome ou RazÃ£o Social do Emitente',
  `emit_ie` varchar(14) NOT NULL COMMENT 'InscriÃ§Ã£o Estadual do Emitente',
  `dest_cpf_cnpj` varchar(14) NOT NULL COMMENT 'CNPJ ou CPF do destinatÃ¡rio da Nota',
  `data_emissao` date NOT NULL,
  `tipo_nota` int(1) NOT NULL,
  `valor_nf` decimal(15,2) NOT NULL,
  `digest_value` varchar(28) NOT NULL,
  `data_hora_recebimento` datetime NOT NULL,
  `situacao_nfe` int(1) NOT NULL,
  `confirmacao` int(1) NOT NULL,
  `data_hora_confirmacao` datetime NOT NULL COMMENT 'Data e hora da manifestacao',
  `protocolo_confirmacao` varchar(20) NOT NULL COMMENT 'Protocolo da Confirmacao da Manifestacao',
  `data_hora_evento` datetime NOT NULL,
  `tp_evento` varchar(10) NOT NULL,
  `seq_evento` int(11) NOT NULL,
  `desc_evento` text NOT NULL,
  `correcao` longtext NOT NULL,
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

