-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tempo de Geração: Mar 26, 2012 as 09:49 AM
-- Versão do Servidor: 5.0.51
-- Versão do PHP: 5.2.4-2ubuntu5.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Estrutura da tabela `backup`
--

CREATE TABLE IF NOT EXISTS `backup` (
  `nome` varchar(50) NOT NULL COMMENT 'Nome do Backup',
  `data` int(8) NOT NULL COMMENT 'Data Invertida do Dia em que foi efetuado o último backup do Banco de Dados',
  `hora` varchar(10) NOT NULL COMMENT 'Horário em que foi efetuado o último backup do banco de dados',
  `link` text NOT NULL COMMENT 'Link para o caminho e nome do backup criado'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `critica`
--

CREATE TABLE IF NOT EXISTS `critica` (
  `codigo_empresa` int(3) NOT NULL,
  `codigo_filial` int(3) NOT NULL,
  `numero_controle` bigint(20) NOT NULL,
  `descricao` text NOT NULL,
  `data` varchar(10) NOT NULL,
  `hora` varchar(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `empresa`
--

CREATE TABLE IF NOT EXISTS `empresa` (
  `empresa` int(3) NOT NULL COMMENT 'Empresa para o Sistema',
  `filial` int(3) NOT NULL COMMENT 'Filial para o Sistema',
  `empresa_web` int(3) NOT NULL COMMENT 'Empresa cadastrada na Web',
  `filial_web` int(3) NOT NULL COMMENT 'Filial cadastrada na Web'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tabela de relacionamento das Empresas e Filiais';

-- --------------------------------------------------------

--
-- Estrutura da tabela `generico`
--

CREATE TABLE IF NOT EXISTS `generico` (
  `codigo_empresa` int(3) NOT NULL COMMENT 'Codigo da Empresa eh uma Chave da Tabela junto com filial e número de controle',
  `codigo_filial` int(3) NOT NULL COMMENT 'Codigo da Filial eh uma Chave da Tabela junto com empresa e número de controle',
  `numero_controle` bigint(20) NOT NULL COMMENT 'Numero de Control eh uma Chave da Tabela junto com empresa e filial',
  `titulo` varchar(50) default NULL COMMENT 'titulo do item generico',
  `descricao` varchar(200) default NULL COMMENT 'descricao do item generico'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `item`
--

CREATE TABLE IF NOT EXISTS `item` (
  `codigo_empresa` int(3) NOT NULL COMMENT 'Código da Empresa é uma Chave da Tabela junto com filial e número de controle',
  `codigo_filial` int(3) NOT NULL COMMENT 'Código da Filial é uma Chave da Tabela junto com empresa e número de controle',
  `numero_controle` bigint(20) NOT NULL COMMENT 'Número de Controle é uma Chave da Tabela junto com empresa e filial',
  `tributa_municipio_prestador` char(1) default NULL,
  `codigo_local_prestacao_servico` varchar(9) default NULL,
  `unidade_codigo` varchar(9) default NULL,
  `unidade_quantidade` varchar(16) default NULL,
  `unidade_valor_unitario` varchar(16) default NULL,
  `codigo_item_lista_servico` varchar(9) default NULL,
  `descritivo` varchar(1000) default NULL,
  `aliquota_item_servico` float(16) default NULL,
  `situacao_tributaria` varchar(4) default NULL,
  `valor_tributavel` float default NULL,
  `valor_deducao` float default NULL,
  `valor_iss` float NOT NULL COMMENT 'Valor ISS numérico que não é retido na Fonte',
  `valor_issrf` float default NULL COMMENT 'Valor do ISS que será retido na fonte',
  `desconto_cond` float NOT NULL COMMENT 'Desconto Condicionado',
  `desconto_incond` float NOT NULL COMMENT 'Incondicionado',
  `codigo_cnae` varchar(10) default NULL COMMENT 'cwb',
  `codigo_tributacao_municipio` varchar(20) default NULL COMMENT 'CWB',
  `valor_csll` varchar(11) default NULL COMMENT 'cwb',
  `outras_retencoes` varchar(11) default NULL COMMENT 'cwb',
  `situacaotributaria` int(2) NOT NULL COMMENT 'Situação Tributária do Item',
  FULLTEXT KEY `codigo_tributacao_municipio` (`codigo_tributacao_municipio`),
  FULLTEXT KEY `codigo_tributacao_municipio_2` (`codigo_tributacao_municipio`),
  FULLTEXT KEY `codigo_tributacao_municipio_3` (`codigo_tributacao_municipio`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `lote`
--

CREATE TABLE IF NOT EXISTS `lote` (
  `codigo_empresa` int(3) NOT NULL COMMENT 'Código da Empresa',
  `codigo_filial` int(3) NOT NULL COMMENT 'Código da Filial',
  `lote` int(15) NOT NULL COMMENT 'Último Número de Lote Emitido'
  `rps` int(15) NOT NULL COMMENT 'Número do RPS'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `nota_fiscal`
--

CREATE TABLE IF NOT EXISTS `nota_fiscal` (
  `empresa_codigo` int(3) NOT NULL COMMENT 'Código da Empresa Emitente da NFS-e',
  `empresa_descricao` varchar(100) NOT NULL COMMENT 'Descrição ou Nome da Empresa Emitente da NFS-e',
  `filial_codigo` int(3) NOT NULL COMMENT 'Código da Filial Emitente da NFS-e',
  `filial_descricao` varchar(100) NOT NULL COMMENT 'Descrição ou Nome da Filial Emitente da NFS-e',
  `nf_lote` int(10) NOT NULL COMMENT 'Número do Lote em que foi enviada a NF',
  `nf_numero` varchar(9) NOT NULL,
  `nf_situacao` char(1) NOT NULL default '1',
  `nf_tipo` int(10) NOT NULL default '1' COMMENT 'Tipo da NF',
  `nf_valor_total` varchar(16) NOT NULL default '0',
  `nf_valor_desconto` varchar(16) NOT NULL default '0',
  `nf_valor_ir` varchar(16) NOT NULL default '0',
  `nf_valor_inss` varchar(16) NOT NULL default '0',
  `nf_valor_contribuicao_social` varchar(16) NOT NULL default '0',
  `nf_valor_rps` varchar(16) NOT NULL default '0',
  `nf_valor_pis` varchar(16) NOT NULL default '0',
  `nf_valor_cofins` varchar(16) NOT NULL default '0',
  `nf_observacao` varchar(1000) NOT NULL,
  `prestador_cpf_cnpj` varchar(14) NOT NULL default '0',
  `prestador_cidade` varchar(9) NOT NULL,
  `prestador_inscricao_municipal` varchar(20) NOT NULL COMMENT 'Inscrição Municipal do Prestador do Serviço',
  `prestador_cnae` int(15) NOT NULL COMMENT 'Código CNAE cadastrado para o prestador do serviço',
  `prestador_optante_simples` int(1) NOT NULL default '2' COMMENT 'Optante pelo Simples Naicional (1-Sim, 2-Não)',
  `prestador_incentivador_cultural` int(1) NOT NULL default '2' COMMENT 'Incentivador Cultural (1-Sim, 2-Não)',
  `tomador_tipo` char(1) NOT NULL,
  `tomador_identificador` varchar(20) NOT NULL COMMENT 'Numero do passaporte caso Tipo_Tomador=''E''',
  `tomador_estado` varchar(100) NOT NULL COMMENT 'Apenas deve ser usado se Tipo_Tomador = ''E''',
  `tomador_pais` varchar(100) NOT NULL COMMENT 'Nome do País do Tomador se Tipo_Tomador = ''E'' (estrangeiro)',
  `tomador_cpf_cnpj` varchar(14) NOT NULL,
  `tomador_ie` varchar(16) NOT NULL default '0',
  `tomador_inscricao_municipal` int(20) NOT NULL COMMENT 'Inscrição Municipal do Tomador',
  `tomador_nome_razao_social` varchar(100) NOT NULL,
  `tomador_sobrenome_nome_fantasia` varchar(100) NOT NULL,
  `tomador_logradouro` varchar(70) NOT NULL,
  `tomador_email` varchar(100) NOT NULL,
  `tomador_numero_residencia` varchar(8) NOT NULL default '0',
  `tomador_complemento` varchar(50) NOT NULL,
  `tomador_ponto_referencia` varchar(100) NOT NULL,
  `tomador_bairro` varchar(30) NOT NULL,
  `tomador_cidade` varchar(100) NOT NULL COMMENT 'Preenchido apenas se Tipo_Tomador=''E''',
  `tomador_cep` varchar(8) NOT NULL default '0',
  `tomador_ddd_fone_comercial` varchar(4) NOT NULL default '0',
  `tomador_fone_comercial` varchar(18) NOT NULL default '0',
  `tomador_ddd_fone_residencial` varchar(4) NOT NULL default '0',
  `tomador_fone_residencial` varchar(18) NOT NULL default '0',
  `tomador_ddd_fax` varchar(4) NOT NULL default '0',
  `tomador_fone_fax` varchar(18) NOT NULL default '0',
  `nf_controle` bigint(20) NOT NULL COMMENT 'Controle da Nota Gerado pelo ERP Cobol',
  `produtos_descricao` varchar(200) NOT NULL COMMENT 'Produtos ligados à nota de serviços.Trata-se de uma descrição textual.',
  `produtos_valor_total` varchar(16) NOT NULL default '0',
  `nf_serie` bigint(20) NOT NULL COMMENT 'Série da Nota Fiscal - em Pinhais, por exemplo, será gerado pela prefeitura...Em outras cidades, teremos de preencher este campo',
  `nf_data_emissao` varchar(100) NOT NULL COMMENT 'Em Pinhais, gerado pela prefeitura.Em outras cidades, teremos de preencher manualmente (em Curitiba, por exemplo, este campo tem um formato especial com data e hora), padrão ABRASF',
  `nf_hora_emissao` varchar(100) NOT NULL COMMENT 'Em Pinhais, será gerado pela Prefeitura. Em Curitiba, por exemplo, não será usado, pois a hora estará dentro do campo Data_Nfse',
  `nf_status` varchar(1) default 'N' COMMENT 'Quando uma nova nota fiscal é inserida, o valor deste campo é automaticamente preenchido com1, indicando que esta nota fiscal acabou de ser inserida.Assim, temos como monitorar novas notas conforme elas são incluídas. Após processamento, este campo é at',
  `nf_link` varchar(500) NOT NULL,
  `nf_autenticacao` varchar(100) NOT NULL COMMENT 'Código de Autenticação para visualizar a NF pelo site',
  `nf_protocolo` varchar(50) NOT NULL default '',
  `nf_regime_especial` int(1) NOT NULL default '2' COMMENT 'Regime Especial de Tributação (1-Microempresa, 2-Estimativa, 3-Sociedade de Profissionais, 4-Cooperativa, 5-Microempresário Individual (MEI), 6-Microempresário e empresa de pequeno porte (ME EPP)'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
