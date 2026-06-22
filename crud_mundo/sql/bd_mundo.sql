-- ============================================
-- BANCO DE DADOS: bd_mundo
-- ============================================

CREATE DATABASE IF NOT EXISTS bd_mundo;
USE bd_mundo;

-- ============================================
-- TABELA: continentes
-- ============================================
CREATE TABLE continentes (
    id_continente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    populacao BIGINT NOT NULL,
    area DECIMAL(15,2) NOT NULL,
    total_paises INT NOT NULL DEFAULT 0
);

-- ============================================
-- TABELA: governantes
-- ============================================
CREATE TABLE governantes (
    id_governante INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    partido_politico VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    idade INT NOT NULL,
    data_inicio_mandato DATE NOT NULL,
    data_final_mandato DATE
);

-- ============================================
-- TABELA: paises
-- ============================================
CREATE TABLE paises (
    id_pais INT AUTO_INCREMENT PRIMARY KEY,
    nome_pais VARCHAR(150) NOT NULL,
    id_continente INT NOT NULL,
    populacao_pais BIGINT NOT NULL,
    area_pais DECIMAL(15,2) NOT NULL,
    idioma VARCHAR(50) NOT NULL,
    id_governante INT,
    clima VARCHAR(100) NOT NULL,
    regime_politico VARCHAR(100) NOT NULL,
    moeda VARCHAR(50) NOT NULL,

    FOREIGN KEY (id_continente)
        REFERENCES continentes(id_continente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (id_governante)
        REFERENCES governantes(id_governante)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- ============================================
-- TABELA: cidades
-- ============================================
CREATE TABLE cidades (
    id_cidade INT AUTO_INCREMENT PRIMARY KEY,
    nome_cidade VARCHAR(150) NOT NULL,
    id_pais INT NOT NULL,
    populacao BIGINT NOT NULL,
    area DECIMAL(15,2) NOT NULL,
    clima VARCHAR(100) NOT NULL,
    id_governante INT,
    data_fundacao DATE NOT NULL,

    FOREIGN KEY (id_pais)
        REFERENCES paises(id_pais)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    FOREIGN KEY (id_governante)
        REFERENCES governantes(id_governante)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- ============================================
-- TRIGGERS para manter total_paises em continentes
-- ============================================
DELIMITER //

CREATE TRIGGER trg_pais_insert
AFTER INSERT ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes
    SET total_paises = (SELECT COUNT(*) FROM paises WHERE id_continente = NEW.id_continente)
    WHERE id_continente = NEW.id_continente;
END//

CREATE TRIGGER trg_pais_delete
AFTER DELETE ON paises
FOR EACH ROW
BEGIN
    UPDATE continentes
    SET total_paises = (SELECT COUNT(*) FROM paises WHERE id_continente = OLD.id_continente)
    WHERE id_continente = OLD.id_continente;
END//

CREATE TRIGGER trg_pais_update
AFTER UPDATE ON paises
FOR EACH ROW
BEGIN
    IF OLD.id_continente <> NEW.id_continente THEN
        UPDATE continentes
        SET total_paises = (SELECT COUNT(*) FROM paises WHERE id_continente = OLD.id_continente)
        WHERE id_continente = OLD.id_continente;

        UPDATE continentes
        SET total_paises = (SELECT COUNT(*) FROM paises WHERE id_continente = NEW.id_continente)
        WHERE id_continente = NEW.id_continente;
    END IF;
END//

DELIMITER ;

-- ============================================
-- DADOS DE EXEMPLO
-- ============================================
INSERT INTO continentes (nome, populacao, area, total_paises) VALUES
('América do Sul', 430000000, 17840000, 0),
('Europa', 747000000, 10180000, 0),
('Ásia', 4700000000, 44579000, 0);

INSERT INTO governantes (nome, partido_politico, data_nascimento, idade, data_inicio_mandato, data_final_mandato) VALUES
('Luiz Inácio Lula da Silva', 'PT', '1945-10-27', 80, '2023-01-01', NULL),
('Emmanuel Macron', 'Renaissance', '1977-12-21', 48, '2017-05-14', NULL);

INSERT INTO paises (nome_pais, id_continente, populacao_pais, area_pais, idioma, id_governante, clima, regime_politico, moeda) VALUES
('Brasil', 1, 214000000, 8515767, 'Português', 1, 'Tropical', 'República Federativa', 'Real (BRL)'),
('França', 2, 67000000, 643801, 'Francês', 2, 'Temperado', 'República Semipresidencialista', 'Euro (EUR)');

INSERT INTO cidades (nome_cidade, id_pais, populacao, area, clima, id_governante, data_fundacao) VALUES
('São Paulo', 1, 12300000, 1521, 'Tropical', NULL, '1554-01-25'),
('Brasília', 1, 3055000, 5760, 'Tropical de Altitude', NULL, '1960-04-21'),
('Paris', 2, 2148000, 105, 'Oceânico', NULL, '0508-01-01');
