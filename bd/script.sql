-- Visualizar as tabelas:
USE qualaboa;
SELECT * FROM nome_tabela; -- Mudar para o nome da tabela

CREATE DATABASE IF NOT EXISTS qualaboa
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE qualaboa;

DROP TABLE IF EXISTS usuario;

CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo_usuario ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  foto_perfil VARCHAR(255) DEFAULT 'default-profile.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO usuario (nome, email, senha, tipo_usuario)
VALUES (
    'Administrador',
    'admin@qualaboa.com',
    '$2y$10$m.yk9gZS7WSrIOKY7bpG9.IK1Sq/FNkwGzsp3j6aVs1kVx0axqFOG', -- Senha: admin
    'admin'
);

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS locais (
    id_local INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('Restaurante','Bar','Parque','Evento','Museu','Outro') NOT NULL,
    regiao VARCHAR(50) NOT NULL,
    faixa_preco ENUM('Econômico','Médio','Alto') NOT NULL,
    servicos TEXT,
    avaliacao_media FLOAT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);