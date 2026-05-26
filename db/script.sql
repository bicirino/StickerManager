-- ====================================================
-- StickerManager - Script de Criação do Banco de Dados
-- ====================================================
-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS sticker_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sticker_manager;

-- ====================================================
-- Tabela: Categoria
-- Descrição: Categorias de figurinhas (Seleções, Mascotes, Legends)
-- ====================================================
CREATE TABLE IF NOT EXISTS Categoria (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nome_categoria VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Tabela: Posicao
-- Descrição: Posições dos jogadores (Goleiro, Defesa, Meio-campo, Atacante)
-- ====================================================
CREATE TABLE IF NOT EXISTS Posicao (
    id_posicao INT PRIMARY KEY AUTO_INCREMENT,
    nome_posicao VARCHAR(50) NOT NULL UNIQUE,
    abreviacao VARCHAR(3),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Tabela: Selecoes
-- Descrição: Países/Seleções participantes da Copa
-- ====================================================
CREATE TABLE IF NOT EXISTS Selecoes (
    id_selecao INT PRIMARY KEY AUTO_INCREMENT,
    nome_selecao VARCHAR(100) NOT NULL UNIQUE,
    sigla VARCHAR(3),
    bandeira_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Tabela: Figurinhas
-- Descrição: Catálogo de todas as figurinhas disponíveis
-- ====================================================
CREATE TABLE IF NOT EXISTS Figurinhas (
    id_figurinha INT PRIMARY KEY AUTO_INCREMENT,
    numero_figurinha INT NOT NULL,
    nome_jogador VARCHAR(150) NOT NULL,
    id_selecao INT NOT NULL,
    id_posicao INT,
    id_categoria INT NOT NULL,
    imagem_url VARCHAR(255),
    raridadade ENUM('comum', 'rara', 'especial') DEFAULT 'comum',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_selecao) REFERENCES Selecoes(id_selecao) ON DELETE CASCADE,
    FOREIGN KEY (id_posicao) REFERENCES Posicao(id_posicao) ON DELETE SET NULL,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id_categoria) ON DELETE CASCADE,
    UNIQUE KEY unique_figurinha (numero_figurinha, id_selecao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Tabela: Usuarios
-- Descrição: Usuários do sistema com autenticação
-- ====================================================
CREATE TABLE IF NOT EXISTS Usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome_usuario VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Tabela: Minha_Colecao
-- Descrição: Controle de figurinhas do usuário (possui/falta/repetidas)
-- ====================================================
CREATE TABLE IF NOT EXISTS Minha_Colecao (
    id_colecao INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_figurinha INT NOT NULL,
    status ENUM('obtida', 'faltante', 'repetida') DEFAULT 'faltante',
    quantidade_obtida INT DEFAULT 0,
    quantidade_repetida INT DEFAULT 0,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_figurinha) REFERENCES Figurinhas(id_figurinha) ON DELETE CASCADE,
    UNIQUE KEY unique_colecao (id_usuario, id_figurinha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================
-- Inserção de Dados Padrão (Seed Data)
-- ====================================================

-- Inserir Categorias
INSERT INTO Categoria (nome_categoria, descricao) VALUES
('Seleções', 'Jogadores das seleções participantes'),
('Mascotes', 'Figurinhas especiais dos mascotes da Copa'),
('Legends', 'Lendas do futebol internacional');

-- Inserir Posições
INSERT INTO Posicao (nome_posicao, abreviacao) VALUES
('Goleiro', 'GOL'),
('Defesa', 'DEF'),
('Meio-campo', 'MCA'),
('Atacante', 'ATA');

-- Inserir Seleções (Exemplo com algumas seleções)
INSERT INTO Selecoes (nome_selecao, sigla, bandeira_url) VALUES
('Brasil', 'BRA', 'https://flagcdn.com/br.svg'),
('Alemanha', 'ALE', 'https://flagcdn.com/de.svg'),
('França', 'FRA', 'https://flagcdn.com/fr.svg'),
('Argentina', 'ARG', 'https://flagcdn.com/ar.svg'),
('Espanha', 'ESP', 'https://flagcdn.com/es.svg'),
('Itália', 'ITA', 'https://flagcdn.com/it.svg'),
('Portugal', 'POR', 'https://flagcdn.com/pt.svg'),
('Holanda', 'HOL', 'https://flagcdn.com/nl.svg'),
('Bélgica', 'BEL', 'https://flagcdn.com/be.svg'),
('Uruguai', 'URU', 'https://flagcdn.com/uy.svg'),
('México', 'MEX', 'https://flagcdn.com/mx.svg'),
('Japão', 'JAP', 'https://flagcdn.com/jp.svg');

-- Inserir algumas Figurinhas de exemplo (Brasil)
INSERT INTO Figurinhas (numero_figurinha, nome_jogador, id_selecao, id_posicao, id_categoria, raridadade) VALUES
(1, 'Alisson', 1, 1, 1, 'comum'),
(2, 'Thiago Silva', 1, 2, 1, 'comum'),
(3, 'Neymar Jr.', 1, 4, 1, 'rara'),
(4, 'Vinícius Júnior', 1, 4, 1, 'rara'),
(5, 'Rodrygo', 1, 4, 1, 'comum'),
(6, 'Casemiro', 1, 3, 1, 'comum');

-- Inserir algumas Figurinhas de exemplo (Alemanha)
INSERT INTO Figurinhas (numero_figurinha, nome_jogador, id_selecao, id_posicao, id_categoria, raridadade) VALUES
(7, 'Manuel Neuer', 2, 1, 1, 'rara'),
(8, 'Antonio Rüdiger', 2, 2, 1, 'comum'),
(9, 'Jamal Musiala', 2, 3, 1, 'rara'),
(10, 'Serge Gnabry', 2, 4, 1, 'comum');

-- Inserir Usuário de teste
INSERT INTO Usuarios (nome_usuario, email, senha_hash) VALUES
('usuario_teste', 'teste@example.com', '$2y$10$Y8bU1qxg8yCsxR1Q8zK8.e0Tz3L0M5N6O7P8Q9R0S1T2U3V4W5X6Y');

-- ====================================================
-- Índices para melhor performance
-- ====================================================
CREATE INDEX idx_figurinha_selecao ON Figurinhas(id_selecao);
CREATE INDEX idx_figurinha_categoria ON Figurinhas(id_categoria);
CREATE INDEX idx_colecao_usuario ON Minha_Colecao(id_usuario);
CREATE INDEX idx_colecao_figurinha ON Minha_Colecao(id_figurinha);
CREATE INDEX idx_colecao_status ON Minha_Colecao(status);
