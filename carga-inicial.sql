-- ================================================================
--  CARGA INICIAL - MÓDULO FINANCEIRO STANDALONE
--  Execute este script no MySQL para criar todas as tabelas
--  e inserir os dados mínimos para o sistema funcionar.
-- ================================================================

SET NAMES utf8;
SET CHARACTER SET utf8;

-- ================================================================
-- 1. TABELAS DE AUTENTICAÇÃO E PERMISSÕES
-- ================================================================

CREATE TABLE IF NOT EXISTS sis_sistemas (
    cod_sistema INT NOT NULL PRIMARY KEY,
    nome_sistema VARCHAR(100) NOT NULL,
    enum_sistema VARCHAR(50),
    status VARCHAR(20) DEFAULT 'ATIVADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_grupos (
    cod_grupo INT AUTO_INCREMENT PRIMARY KEY,
    nome_grupo VARCHAR(100) NOT NULL,
    descricao VARCHAR(255),
    status VARCHAR(20) DEFAULT 'ATIVADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_usuarios (
    cod_usuario INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(100) NOT NULL,
    e_mail VARCHAR(200),
    senha VARCHAR(255) NOT NULL,
    nome_usuario VARCHAR(200),
    status VARCHAR(20) DEFAULT 'ATIVADA',
    dat_incl DATETIME,
    dat_alter DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tab_convidados (
    cod_convidado INT AUTO_INCREMENT PRIMARY KEY,
    cod_usuario INT,
    nome_convidado VARCHAR(200),
    status VARCHAR(20) DEFAULT 'ATIVADA',
    INDEX idx_cod_usuario (cod_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_ligacao_usuarios_eventos (
    cod_usuario INT NOT NULL,
    cod_evento INT NOT NULL,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    PRIMARY KEY (cod_usuario, cod_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_ligacao_usuarios_grupos (
    cod_usuario INT NOT NULL,
    cod_grupo INT NOT NULL,
    cod_evento INT NOT NULL,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    PRIMARY KEY (cod_usuario, cod_grupo, cod_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_direitos_grupos_sistemas (
    cod_grupo INT NOT NULL,
    cod_sistema INT NOT NULL,
    cod_direito INT NOT NULL DEFAULT 5,
    cod_evento INT NOT NULL,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    PRIMARY KEY (cod_grupo, cod_sistema, cod_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sis_gestor (
    cod_gestor INT AUTO_INCREMENT PRIMARY KEY,
    nome_gestor VARCHAR(200),
    status VARCHAR(20) DEFAULT 'ATIVADA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ================================================================
-- 2. TABELAS DO MÓDULO FINANCEIRO
-- ================================================================

CREATE TABLE IF NOT EXISTS fin_plano_contas (
    cod_plano_contas INT AUTO_INCREMENT PRIMARY KEY,
    cod_evento INT NOT NULL,
    cod_plano_contas_pai INT,
    cod_gestor INT,
    codigo_contabil VARCHAR(50),
    nome_plano_contas VARCHAR(200),
    descricao TEXT,
    tipo_conta VARCHAR(50) COMMENT 'RECEITA, DESPESA, ATIVO, PASSIVO, PATRIMONIO_LIQUIDO',
    natureza_saldo VARCHAR(20) COMMENT 'DEBITO ou CREDITO',
    aceita_lancamento TINYINT DEFAULT 1,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    versao INT DEFAULT 1,
    dat_incl DATETIME,
    res_incl INT,
    dat_alter DATETIME,
    res_alter INT,
    ip VARCHAR(50),
    http_user_agent VARCHAR(500),
    INDEX idx_evento (cod_evento),
    INDEX idx_pai (cod_plano_contas_pai),
    INDEX idx_natureza (natureza_saldo, aceita_lancamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS fin_centro_custo (
    cod_centro_custo INT AUTO_INCREMENT PRIMARY KEY,
    cod_evento INT NOT NULL,
    cod_centro_custo_pai INT,
    cod_gestor INT,
    codigo_contabil_cc VARCHAR(50),
    nome_centro_custo VARCHAR(200),
    descricao TEXT,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    versao INT DEFAULT 1,
    dat_incl DATETIME,
    res_incl INT,
    dat_alter DATETIME,
    res_alter INT,
    ip VARCHAR(50),
    http_user_agent VARCHAR(500),
    INDEX idx_evento (cod_evento),
    INDEX idx_pai (cod_centro_custo_pai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS fin_rateios (
    cod_rateio INT AUTO_INCREMENT PRIMARY KEY,
    cod_evento INT NOT NULL,
    cod_rateio_pai INT,
    cod_gestor INT,
    codigo_contabil_rateio VARCHAR(50),
    nome_rateio VARCHAR(200),
    percentual DECIMAL(10,4),
    descricao TEXT,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    versao INT DEFAULT 1,
    dat_incl DATETIME,
    res_incl INT,
    dat_alter DATETIME,
    res_alter INT,
    ip VARCHAR(50),
    http_user_agent VARCHAR(500),
    INDEX idx_evento (cod_evento),
    INDEX idx_pai (cod_rateio_pai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS fin_fontes_recurso (
    cod_fontes_recurso INT AUTO_INCREMENT PRIMARY KEY,
    cod_evento INT NOT NULL,
    nome_fontes_recurso VARCHAR(200),
    tipo_fontes_recurso VARCHAR(100),
    descricao TEXT,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    versao INT DEFAULT 1,
    dat_incl DATETIME,
    res_incl INT,
    dat_alter DATETIME,
    res_alter INT,
    ip VARCHAR(50),
    http_user_agent VARCHAR(500),
    INDEX idx_evento (cod_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS fin_lancamentos_contabeis (
    cod_lancamentos_contabeis INT AUTO_INCREMENT PRIMARY KEY,
    cod_evento INT NOT NULL,
    cod_plano_contas INT,
    flg_lancamento CHAR(1) COMMENT 'D=Debito, C=Credito',
    tipo_lancamento VARCHAR(50) COMMENT 'PAGAMENTO_FORNECEDOR, VENDA, DESPESA_PAGA',
    valor DECIMAL(15,2),
    data_competencia DATE,
    data_financeira DATE,
    origem_lancamento VARCHAR(50) COMMENT 'MANUAL, FINANCEIRO, INTEGRACAO',
    historico TEXT,
    conciliado TINYINT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'ATIVADA',
    versao INT DEFAULT 1,
    dat_incl DATETIME,
    res_incl INT,
    dat_alter DATETIME,
    res_alter INT,
    ip VARCHAR(50),
    http_user_agent VARCHAR(500),
    INDEX idx_evento (cod_evento),
    INDEX idx_plano_contas (cod_plano_contas),
    INDEX idx_tipo (tipo_lancamento),
    INDEX idx_data_comp (data_competencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ================================================================
-- 3. DADOS INICIAIS - SISTEMAS (PERMISSÕES)
-- ================================================================

INSERT INTO sis_sistemas (cod_sistema, nome_sistema, enum_sistema) VALUES
(0,  'Incluir Evento',            'SIS_INCLUIR_EVENTO'),
(1,  'Gerenciar Evento',          'SIS_GERENCIAR_EVENTO'),
(7,  'Controle Financeiro',       'SIS_CONTROLE_FINANCEIRO'),
(999,'Administração Geral',       'SIS_GERENCIAR_ADM_GERAL')
ON DUPLICATE KEY UPDATE nome_sistema = VALUES(nome_sistema);

-- ================================================================
-- 4. GRUPO ADMINISTRADOR FINANCEIRO
-- ================================================================

INSERT INTO sis_grupos (cod_grupo, nome_grupo, descricao) VALUES
(1, 'Administrador Financeiro', 'Grupo com acesso total ao módulo financeiro')
ON DUPLICATE KEY UPDATE nome_grupo = VALUES(nome_grupo);

-- ================================================================
-- 5. PERMISSÃO DO GRUPO NO SISTEMA FINANCEIRO
--    cod_direito = 5 (acesso total)
--    cod_evento = 115 (ajuste conforme seu evento)
-- ================================================================

INSERT INTO sis_direitos_grupos_sistemas (cod_grupo, cod_sistema, cod_direito, cod_evento) VALUES
(1, 7, 5, 115)
ON DUPLICATE KEY UPDATE cod_direito = VALUES(cod_direito);

-- ================================================================
-- 6. USUÁRIO ADMINISTRADOR PADRÃO
--    Login: admin
--    Senha: admin123 (hash bcrypt)
--    IMPORTANTE: Troque a senha após o primeiro acesso!
-- ================================================================

INSERT INTO sis_usuarios (cod_usuario, login, e_mail, senha, nome_usuario, status, dat_incl) VALUES
(1, 'admin', 'admin@sistema.local',
 '$2y$10$TEJXNv9radSlunqA5NaotOfPnXUHO6C/yMCaZ0LTBGAhLoNAZVyDe',
 'Administrador', 'ATIVADA', NOW())
ON DUPLICATE KEY UPDATE login = VALUES(login);

-- Vincula o usuário ao evento 115
INSERT INTO sis_ligacao_usuarios_eventos (cod_usuario, cod_evento) VALUES
(1, 115)
ON DUPLICATE KEY UPDATE cod_evento = VALUES(cod_evento);

-- Vincula o usuário ao grupo administrador financeiro no evento 115
INSERT INTO sis_ligacao_usuarios_grupos (cod_usuario, cod_grupo, cod_evento) VALUES
(1, 1, 115)
ON DUPLICATE KEY UPDATE cod_grupo = VALUES(cod_grupo);

-- Nome de exibição (tab_convidados)
INSERT INTO tab_convidados (cod_usuario, nome_convidado) VALUES
(1, 'Administrador')
ON DUPLICATE KEY UPDATE nome_convidado = VALUES(nome_convidado);

-- ================================================================
-- 7. DADOS EXEMPLO - PLANO DE CONTAS (OPCIONAL)
--    Descomente se quiser ter dados de exemplo
-- ================================================================

-- INSERT INTO fin_plano_contas (cod_evento, codigo_contabil, nome_plano_contas, tipo_conta, natureza_saldo, aceita_lancamento, status, versao, dat_incl, res_incl) VALUES
-- (115, '1',   'ATIVO',                    'ATIVO',    'DEBITO',  0, 'ATIVADA', 1, NOW(), 1),
-- (115, '1.1', 'Caixa',                    'ATIVO',    'DEBITO',  1, 'ATIVADA', 1, NOW(), 1),
-- (115, '1.2', 'Banco Conta Movimento',    'ATIVO',    'DEBITO',  1, 'ATIVADA', 1, NOW(), 1),
-- (115, '2',   'PASSIVO',                  'PASSIVO',  'CREDITO', 0, 'ATIVADA', 1, NOW(), 1),
-- (115, '2.1', 'Fornecedores a Pagar',     'PASSIVO',  'CREDITO', 1, 'ATIVADA', 1, NOW(), 1),
-- (115, '3',   'RECEITAS',                 'RECEITA',  'CREDITO', 0, 'ATIVADA', 1, NOW(), 1),
-- (115, '3.1', 'Receita de Vendas',        'RECEITA',  'CREDITO', 1, 'ATIVADA', 1, NOW(), 1),
-- (115, '3.2', 'Receita de Serviços',      'RECEITA',  'CREDITO', 1, 'ATIVADA', 1, NOW(), 1),
-- (115, '4',   'DESPESAS',                 'DESPESA',  'DEBITO',  0, 'ATIVADA', 1, NOW(), 1),
-- (115, '4.1', 'Despesas Administrativas', 'DESPESA',  'DEBITO',  1, 'ATIVADA', 1, NOW(), 1),
-- (115, '4.2', 'Despesas com Pessoal',     'DESPESA',  'DEBITO',  1, 'ATIVADA', 1, NOW(), 1),
-- (115, '4.3', 'Despesas Operacionais',    'DESPESA',  'DEBITO',  1, 'ATIVADA', 1, NOW(), 1);

-- ================================================================
-- FIM DA CARGA INICIAL
--
-- Resumo:
--   - 8 tabelas de sistema (autenticação/permissões)
--   - 5 tabelas financeiras
--   - 1 usuário admin (login: admin / senha: admin123)
--   - Permissão financeira configurada para evento 115
--
-- Para alterar o evento, substitua '115' pelo código desejado
-- em todos os INSERTs acima e no include/config.php
-- ================================================================
