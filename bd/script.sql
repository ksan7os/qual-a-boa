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

-- Usuários e Avaliações para teste
USE qualaboa;

INSERT INTO usuario (nome, email, senha) VALUES
('Usuário 1', 'user1@email.com', '123'),
('Usuário 2', 'user2@email.com', '123'),
('Usuário 3', 'user3@email.com', '123'),
('Usuário 4', 'user4@email.com', '123'),
('Usuário 5', 'user5@email.com', '123'),
('Usuário 6', 'user6@email.com', '123'),
('Usuário 7', 'user7@email.com', '123'),
('Usuário 8', 'user8@email.com', '123'),
('Usuário 9', 'user9@email.com', '123'),
('Usuário 10', 'user10@email.com', '123'),
('Usuário 11', 'user11@email.com', '123'),
('Usuário 12', 'user12@email.com', '123'),
('Usuário 13', 'user13@email.com', '123'),
('Usuário 14', 'user14@email.com', '123'),
('Usuário 15', 'user15@email.com', '123'),
('Usuário 16', 'user16@email.com', '123'),
('Usuário 17', 'user17@email.com', '123'),
('Usuário 18', 'user18@email.com', '123'),
('Usuário 19', 'user19@email.com', '123'),
('Usuário 20', 'user20@email.com', '123'),
('Usuário 21', 'user21@email.com', '123'),
('Usuário 22', 'user22@email.com', '123'),
('Usuário 23', 'user23@email.com', '123'),
('Usuário 24', 'user24@email.com', '123'),
('Usuário 25', 'user25@email.com', '123'),
('Usuário 26', 'user26@email.com', '123'),
('Usuário 27', 'user27@email.com', '123'),
('Usuário 28', 'user28@email.com', '123'),
('Usuário 29', 'user29@email.com', '123'),
('Usuário 30', 'user30@email.com', '123');

DELETE FROM avaliacoes WHERE id_avaliacao > 0;

-- 1️⃣ Fortunata – 5 avaliações, todas 5⭐ (alta média, pouca popularidade)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,1,5),(2,1,5),(3,1,5),(4,1,5),(5,1,5);

-- 2️⃣ Vert Café – 13 avaliações, 4–5⭐ (média alta e muito popular)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,2,4),(2,2,5),(3,2,4),(4,2,5),(5,2,4),(6,2,5),(7,2,4),(8,2,5),(9,2,4),(10,2,5),
(11,2,4),(12,2,5),(13,2,4);

-- 3️⃣ Ordinário – 10 avaliações medianas (popularidade média, nota mediana)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,3,3),(2,3,4),(3,3,3),(4,3,3),(5,3,4),(6,3,3),(7,3,4),(8,3,3),(9,3,4),(10,3,3);

-- 4️⃣ London Street – 8 avaliações boas, mas poucas (nota boa, pouca popularidade)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,4,4),(2,4,4),(3,4,5),(4,4,4),(5,4,5),(6,4,4),(7,4,4),(8,4,5);

-- 5️⃣ New Mercadito – 13 avaliações, notas baixas (popular, mas mal avaliado)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,5,3),(2,5,2),(3,5,3),(4,5,2),(5,5,3),(6,5,2),(7,5,3),(8,5,2),(9,5,3),(10,5,2),
(11,5,3),(12,5,2),(13,5,3);

-- 6️⃣ Vila Tarêgo – 6 avaliações perfeitas (muito bem avaliado, pouco popular)
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES
(1,6,5),(2,6,5),(3,6,5),(4,6,5),(5,6,5),(6,6,5);