CREATE DATABASE IF NOT EXISTS qualaboa
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;
USE qualaboa;

-- Usuários
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

-- Admin padrão (senha: admin)
INSERT INTO usuario (nome, email, senha, tipo_usuario) VALUES
('Administrador','admin@qualaboa.com',
'$2y$10$m.yk9gZS7WSrIOKY7bpG9.IK1Sq/FNkwGzsp3j6aVs1kVx0axqFOG','admin');

-- Resets de senha
DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_usuario
    FOREIGN KEY (user_id) REFERENCES usuario(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Locais
DROP TABLE IF EXISTS locais;
CREATE TABLE locais (
  id_local INT AUTO_INCREMENT PRIMARY KEY,

  -- Nome e classificação geral
  nome VARCHAR(120) NOT NULL,
  tipo ENUM(
    'Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria',
    'Parque','Praça','Museu','Teatro','Show','Evento',
    'Feira','Mercado','Atração Turística','Outro'
  ) NOT NULL,

  -- Sobre
  descricao TEXT,

  -- Localização (endereço completo)
  endereco VARCHAR(255) NOT NULL,

  -- Faixa de preço
  faixa_preco ENUM('Econômico','Médio','Alto') NOT NULL,

  -- Horário de funcionamento (ex.: "Seg–Sex 12h–22h; Sáb–Dom 11h–23h")
  horario_funcionamento TEXT,

  -- Contatos
  site VARCHAR(255),
  telefone VARCHAR(25),
  email_contato VARCHAR(120),
  redes_sociais TEXT,

  -- Serviços
  servicos TEXT,

  -- Mídia e avaliação
  imagem_capa VARCHAR(255),
  avaliacao_media FLOAT DEFAULT 0,

  -- Auditoria
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Índices úteis
  INDEX idx_locais_tipo (tipo),
  INDEX idx_locais_faixa (faixa_preco),
  INDEX idx_locais_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Avaliações
CREATE TABLE avaliacoes (
  id_avaliacao INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario   INT NOT NULL,
  id_local     INT NOT NULL,
  nota         TINYINT NOT NULL CHECK (nota BETWEEN 1 AND 5),
  comentario   TEXT NULL,
  criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_avaliacoes_usuario FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_avaliacoes_local   FOREIGN KEY (id_local)   REFERENCES locais(id_local)   ON DELETE CASCADE,
  UNIQUE KEY unq_user_local (id_usuario, id_local)  -- 1 avaliação por usuário por local
);