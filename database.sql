CREATE DATABASE semfila

USE semfila;

--  tabela: usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(200) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    foto_perfil VARCHAR(500) DEFAULT NULL,
    documento_url VARCHAR(500) DEFAULT NULL,
    tipo ENUM('baladeiro', 'gestor', 'funcionario', 'admin') NOT NULL,
    balada_id INT DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_usuarios_email (email),
    UNIQUE KEY uk_usuarios_cpf (cpf)
);


--  tabela: contratos_gestores
CREATE TABLE contratos_gestores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cnpj VARCHAR(18) NOT NULL,
    razao_social VARCHAR(200) NOT NULL,
    data_inicio DATE NOT NULL,
    data_vencimento DATE NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    observacoes TEXT DEFAULT NULL,

    UNIQUE KEY uk_contratos_cnpj (cnpj),
    CONSTRAINT fk_contratos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


--  tabela: baladas
CREATE TABLE baladas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gestor_id INT NOT NULL,
    nome VARCHAR(200) NOT NULL,
    cnpj VARCHAR(18) NOT NULL,
    endereco VARCHAR(300) NOT NULL,
    cidade VARCHAR(150) NOT NULL,
    capacidade_maxima INT NOT NULL,
    logo_url VARCHAR(500) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_baladas_gestor
        FOREIGN KEY (gestor_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- fk da tabela 'usuarios' na coluna 'balada_id' para tabela 'baladas' na coluna 'id'
ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuarios_balada
        FOREIGN KEY (balada_id) REFERENCES baladas (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL;


--  tabela: pulseiras
CREATE TABLE pulseiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    codigo_rfid VARCHAR(100) NOT NULL,
    saldo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('ativa', 'inativa') NOT NULL DEFAULT 'ativa',
    assinatura_inicio DATE NOT NULL,
    assinatura_fim DATE NOT NULL,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_pulseiras_usuario (usuario_id),
    UNIQUE KEY uk_pulseiras_rfid (codigo_rfid),
    CONSTRAINT fk_pulseiras_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


--  tabela: eventos
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    balada_id INT NOT NULL,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT DEFAULT NULL,
    data_evento DATE NOT NULL,
    horario_abertura TIME NOT NULL,
    idade_minima INT NOT NULL DEFAULT 18,
    capacidade_maxima INT NOT NULL,
    status ENUM('ativo', 'encerrado', 'cancelado') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_eventos_balada
        FOREIGN KEY (balada_id) REFERENCES baladas (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

--  tabela: ingressos_lotes
CREATE TABLE ingressos_lotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    nome_lote VARCHAR(100) NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    taxa_plataforma DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantidade_total INT NOT NULL,
    quantidade_vendida INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lotes_evento
        FOREIGN KEY (evento_id) REFERENCES eventos (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


--  tabela: ingressos
CREATE TABLE ingressos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lote_id INT NOT NULL,
    usuario_id INT NOT NULL,
    qr_code VARCHAR(255) NOT NULL,
    pulseira_id INT DEFAULT NULL,
    status ENUM('disponivel', 'utilizado', 'cancelado') NOT NULL DEFAULT 'disponivel',
    comprado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_ingressos_qrcode (qr_code),
    CONSTRAINT fk_ingressos_lote
        FOREIGN KEY (lote_id) REFERENCES ingressos_lotes (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_ingressos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_ingressos_pulseira
        FOREIGN KEY (pulseira_id) REFERENCES pulseiras (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);


--  tabela: entradas
CREATE TABLE entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    ingresso_id INT NOT NULL,
    funcionario_id INT NOT NULL,
    metodo ENUM('qr_code', 'rfid') NOT NULL,
    registrado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_entradas_evento
        FOREIGN KEY (evento_id) REFERENCES eventos (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_entradas_ingresso
        FOREIGN KEY (ingresso_id) REFERENCES ingressos (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_entradas_funcionario
        FOREIGN KEY (funcionario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


--  tabela: consumos_bar
CREATE TABLE consumos_bar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    pulseira_id INT NOT NULL,
    funcionario_id INT NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    registrado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_consumos_evento
        FOREIGN KEY (evento_id) REFERENCES eventos (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_consumos_pulseira
        FOREIGN KEY (pulseira_id) REFERENCES pulseiras (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_consumos_funcionario
        FOREIGN KEY (funcionario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);


--  tabela: itens_consumo
CREATE TABLE itens_consumo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consumo_id INT NOT NULL,
    produto VARCHAR(150) NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_itens_consumo
        FOREIGN KEY (consumo_id) REFERENCES consumos_bar (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);