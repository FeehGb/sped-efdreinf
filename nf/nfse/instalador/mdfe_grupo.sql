-- NÃO APAGAR - Responsável por criar tabelas de instalação do sistema
--
-- Estrutura para Criar Base de Dados mdfe_<grupo> para cada cliente e suas tabelas
--
-- Criação Guilherme Silva 01/10/2014 - 3 days left
--
-- Histórico de Alterações
--  Descrição                           Responsável e Data
--
-- Banco de Dados: `mdfe_<grupo>`
--

CREATE DATABASE IF NOT EXISTS mdfe_<grupo>;

-- --------------------------------------------------------

--
-- Estrutura da tabela `CONTRIBUINTE`
--

CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`CONTRIBUINTE` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ do Contribuinte, Emissor da MDFE.',
  `ambiente` varchar(1) NOT NULL COMMENT 'Tipo de Ambiente: 1-Produção; 2-Homologação; 3-Desativado para emissão.',
  `uf` varchar(2) NOT NULL COMMENT 'Unidade da Federação',
  `cod_emp_fil_softdib` varchar(6) DEFAULT NULL COMMENT 'Código da Empresa e Filial que consta no Softdib.',
  `razao_social` varchar(100) NOT NULL COMMENT 'Razão Social da Empresa.',
  `certificado_tipo` varchar(2) NOT NULL COMMENT 'A1; A3; Ainda não foi encontrada nenhum programa que autentique com sucesso no certificado A3, apenas A1.',
  `certificado_caminho` text NOT NULL COMMENT 'Caminho do Certificado Digital (PFX). ( ./nfe/nfse/certificados)',
  `certificado_senha` varchar(50) NOT NULL COMMENT 'Senha do certificado digital utilizado para emissão da NF.',
  `contigencia` varchar(2) NOT NULL COMMENT '01 - Normal (padrão) sem contigência; 02 - Contingência.',
  `data_hora_contingencia` datetime DEFAULT NULL COMMENT 'Data e Hora da entrada em Contingência AAAA:MM:DDTHH:MM:SS',
  `justificativa_contingencia` varchar(255) DEFAULT NULL COMMENT 'Justificativa de entrar em contingência',
  `pacote_xsd` varchar(100) NOT NULL COMMENT 'Nome do Pacote XSD (pasta).',
  `email_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome de usuário do emitente da MDFE.',
  `email_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do Email do emitente da MDFe.',
  `email_remetente` varchar(100) DEFAULT NULL,
  `email_smtp` varchar(100) DEFAULT NULL COMMENT 'Caminho do Servidor SMTP do email do emitente da MDFe.',
  `email_porta` varchar(4) DEFAULT NULL COMMENT 'Código da Porta do Servidor de email do emitente da MDFe.',
  `email_ssl` varchar(1) DEFAULT NULL COMMENT 'Flag se a conexão é SSL ou não. 0-Não; 1-Sim.',
  `email_conf_recebimento` varchar(1) DEFAULT NULL COMMENT 'Flag se habilita a confirmação de recebimento do Proxy.',
  `proxy_servidor` varchar(100) DEFAULT NULL COMMENT 'Servidor Proxy para conexão com Internet.',
  `proxy_porta` varchar(4) DEFAULT NULL COMMENT 'Porta do Servidor Proxy: Ex.: 4403 ',
  `proxy_usuario` varchar(100) DEFAULT NULL COMMENT 'Nome do usuário do Proxy, caso o servidor exija um proxy para conexão com a Internet.',
  `proxy_senha` varchar(100) DEFAULT NULL COMMENT 'Senha do usuário Proxy para conexão com Internet.',
  `diretorio_integracao` text COMMENT 'Caminho do diretório onde fará a integração.',
  `diretorio_backup` text NOT NULL COMMENT 'Diretorio backup das MDFe',
  `diretorio_importacao` text NOT NULL COMMENT 'Diretorio de integracao com terceiros',
  `diretorio_base` text NOT NULL COMMENT 'Diretorio base no servidor softdib. Ex.: /user/nomecliente',
  `damdfe_layout_caminho` text COMMENT 'Caminho do Layout da DAMDFe a ser impresso.',
  `damdfe_logo_caminho` text COMMENT 'Caminho da Logo da DAMDFe.',
  `damdfe_qtde_vias` int(3) DEFAULT NULL COMMENT 'Quantidade de vias que serão impressas da DAMDFe quando submitida a impressão automática.',
  `damdfe_automatica` varchar(1) DEFAULT NULL COMMENT 'Seleciona se a impressão da DAMDFe é automática. 0 - Não. 1 - Sim.',
  `server_impressao` text COMMENT 'Caminho do Servidor de impressão onde será impressa a DAMDFe automáticamente. Ex.: \\\\192.168.1.24\\HPLaserJet',
  PRIMARY KEY (`cnpj`,`ambiente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Estrutura da tabela `CRITICA`
--

CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`CRITICA` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ (tabela MDFE)',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente (tabela MDFE)',
  `id_lote` varchar(15) NOT NULL COMMENT 'Id do Lote (tabela MDFE)',
  `numero` varchar(9) NOT NULL COMMENT 'Número da MDFe (tabela MDFE)',
  `serie` int(3) NOT NULL COMMENT 'Série da MDFe (tabela MDFE)',
  `sequencia` int(5) NOT NULL AUTO_INCREMENT COMMENT 'Sequência da Crítica de validação, estrutura, qualquer outro erro do lado do sistema ou sefaz.',
  `codigo_referencia` varchar(10) DEFAULT NULL COMMENT 'Código de Referância da Crítica. Ex.: E201, E99, 501, etc. Quando for crítica perante ao sistema denominar SISTEMA.',
  `descricao` TEXT DEFAULT NULL COMMENT 'Descrição da Crítica de Validação.',
  `data_hora_critica` datetime DEFAULT NULL COMMENT 'Data e hora que foi postada a crítica.',
  `notificada` varchar(1) DEFAULT NULL COMMENT 'Se critica foi notificada ao usuario: N - Não; S - Sim.',
  PRIMARY KEY (`sequencia`,`cnpj`,`ambiente`,`id_lote`,`numero`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Estrutura da tabela `EVENTO`
--

CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`EVENTO` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ (tabela MDFE)',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente (tabela MDFE)',
  `numero` varchar(9) NOT NULL COMMENT 'Número do MDFe (tabela MDFE)',
  `serie` varchar(3) NOT NULL COMMENT 'Série da MDFe (tabela MDFE)',
  `numero_sequencia` int(2) NOT NULL AUTO_INCREMENT COMMENT 'Número Sequencial incremental do evento para mais de 1 evento relacionado ao mesmo tipo de evento. Para maioria dos eventos será 1.',
  `tipo_evento` varchar(10) NOT NULL COMMENT 'Código do Evento: 110111 - Cancelamento; 110112 - Encerramento; 310620 - Registro de Passagem.',
  `xml_env` longtext COMMENT 'XML base64 do envio',
  `xml_ret` longtext COMMENT 'XML base64 do retorno',
  `xml` longtext NOT NULL COMMENT 'Ultimo estado do xml do evento (apendado, etc).',
  `descricao` varchar(1000) DEFAULT NULL COMMENT 'Descricao do retorno do evento',
  `protocolo` varchar(15) DEFAULT NULL COMMENT 'Protocolo de autorização do SEFAZ.',
  `data_hora` datetime DEFAULT NULL COMMENT 'Data e hora do evento.',
  `status` varchar(3) DEFAULT NULL COMMENT 'Status do evento.',
  PRIMARY KEY (`numero_sequencia`,`cnpj`,`ambiente`,`numero`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Estrutura da tabela `LOG`
--

CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`LOG` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ (tabela MDFE)',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente (tabela MDFE)',
  `numero` varchar(9) NOT NULL COMMENT 'Número da MDFe (tabela MDFE)',
  `serie` varchar(3) NOT NULL COMMENT 'Série da MDFe (tabela MDFE)',
  `sequencia` int(5) NOT NULL AUTO_INCREMENT COMMENT 'Sequência do Log.',
  `id_lote` varchar(15) NOT NULL COMMENT 'Id do Lote (tabela MDFE)',
  `data_hora` datetime DEFAULT NULL COMMENT 'Data e Hora de registro do log.',
  `evento` varchar(100) DEFAULT NULL COMMENT 'Nome do Evento que foi executado. Ex.: Integração ERP, Enviado Sefaz, Autenticado, etc.',
  `usuario` varchar(50) DEFAULT NULL COMMENT 'Usuário de registro deste evento (INTEGRADOR ou nome do usuário do portal)',
  `descricao` text COMMENT 'Descrição do Log.',
  `detalhes` text COMMENT 'Detalhes adicionais que julgue necessários.',
  PRIMARY KEY (`sequencia`,`cnpj`,`ambiente`,`numero`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

--
-- Estrutura da tabela `LOTE`
--
CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`LOTE` (
  `cnpj` varchar(14) NOT NULL COMMENT 'Cnpj (tabela contribuinte)',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente (tabela contribuinte)',
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Id (tabela contribuinte)',
  `versao` decimal(6,2) NOT NULL COMMENT 'Versão do XML da chamada do lote.',
  `recibo` varchar(15) DEFAULT NULL COMMENT 'Número do Recibo de entrega do Lote de MDFe para processar.',
  `status` int(3) DEFAULT NULL COMMENT 'Status de retorno do lote.',
  `contingencia` varchar(2) NOT NULL COMMENT '01 - Normal sem contigência: 02 - Contingencia;',
  `data_hora` datetime DEFAULT NULL COMMENT 'Data e hora da emissao do Lote',
  PRIMARY KEY (`id`,`cnpj`,`ambiente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Estrutura da tabela `MDFE`
--

CREATE TABLE IF NOT EXISTS `mdfe_<grupo>`.`MDFE` (
  `cnpj` varchar(14) NOT NULL COMMENT 'CNPJ (tabela CONTRIBUINTE)',
  `ambiente` varchar(1) NOT NULL COMMENT 'Ambiente (tabela CONTRIBUINTE)',
  `id_lote` varchar(15) NOT NULL COMMENT 'Id do Lote (tabela CONTRIBUINTE)',
  `numero` varchar(9) NOT NULL COMMENT 'Número da MDFe',
  `serie` varchar(3) NOT NULL COMMENT 'Série da MDFe',
  `versao` decimal(5,2) DEFAULT NULL COMMENT 'Versão do webservice/xml de envio.',
  `tipo_emitente` int(1) DEFAULT NULL COMMENT 'Tipo de Emitente MDFe: 1 - Prestador de serviço de transporte; 2 - Transportador de carga propria; Obs: Deve ser 2  para os emitentes de NFe e pelas transportadoras qdo estiverem fazendo transportede carga propria ',
  `cod_empresa_filial_softdib` varchar(6) DEFAULT NULL COMMENT 'Código da empresa e filial no sistema softdib 001001',
  `nome_emissor` varchar(60) DEFAULT NULL COMMENT 'Razão social do Emissor',
  `uf_carregamento` varchar(2) NOT NULL COMMENT 'UF conf Tabela IBGE. Informar "EX" para operação com o exterior',
  `uf_descarregamento` varchar(2) DEFAULT NULL COMMENT 'UF conf Tabela IBGE. Informar "EX" para operação com o exterior',
  `status` varchar(2) NOT NULL COMMENT '01 - Recebida (recebida pela aplicação MDFe); 02 - Aguardando Sefaz (aguarda retorno validação na sefaz, não está fora); 03 - Autorizada (emitida com sucesso); 04 - Rejeitada (poderá ser corrigida e enviada novamente); 05 - Denegada (foi negada e e',
  `tipo_emissao` varchar(1) NOT NULL COMMENT 'Forma de emissão do Manifesto (MDFe): 1 – Normal – emissão normal; 2 – Contingência',
  `data_emissao` datetime NOT NULL COMMENT 'Data e Hora da Emissão do MDFe (deve ser a mesma contida na DAMDFE).',
  `valor_total_carga` decimal(13,2) NOT NULL COMMENT 'Valor Total da Carga / Mercadorias transportadas',
  `quantidade_nfe` int(4) DEFAULT NULL COMMENT 'Quantidade de NF-e Manifesto',
  `unidade_peso_bruto` int(2) NOT NULL COMMENT 'Codigo da Unidade de medida do Peso Bruto da Carga. 01 - KG; 02 - TON;',
  `peso_bruto` decimal(11,4) DEFAULT NULL COMMENT 'Peso Bruto Total da Carga',
  `chave` varchar(44) NOT NULL COMMENT 'Chave de Acesso do MDF-e composto por Códig',
  `numero_protocolo` varchar(15) DEFAULT NULL COMMENT 'Número do Protocolo da MDF-e',
  `xml_envio` longtext NOT NULL COMMENT 'Campo que comporta o XML de envio em sua última composição. Capacidade 4GB.',
  `xml_retorno` longtext COMMENT 'Campo que comporta o XML de retorno em sua última composição. Capacidade 4GB.',
  `xml` longtext COMMENT 'Campo que comporta o XML de envio em sua última composição (apendado). Capacidade 4GB.',
  `damdfe_impressa` varchar(1) DEFAULT NULL COMMENT 'Damdfe impressa: C = impressa em Contingência; S = impressa Normal; N = não impressa',
  PRIMARY KEY (`cnpj`,`ambiente`,`id_lote`,`numero`,`serie`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
