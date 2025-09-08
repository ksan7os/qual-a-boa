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
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  foto_perfil VARCHAR(255) DEFAULT 'default-profile.jpg';
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

-- Visualizando todos os usuários
USE qualaboa;
SELECT * FROM usuario;
