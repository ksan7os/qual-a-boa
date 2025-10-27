USE qualaboa;
SELECT * FROM usuario;

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
DROP TABLE IF EXISTS avaliacoes;
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

-- Marcar "Estou indo"
DROP TABLE IF EXISTS estou_indo;
CREATE TABLE estou_indo (
  id_estou_indo INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_local INT NOT NULL,
  data_marcacao DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_estou_indo_usuario FOREIGN KEY (id_usuario)
    REFERENCES usuario(id_usuario) ON DELETE CASCADE,
  CONSTRAINT fk_estou_indo_local FOREIGN KEY (id_local)
    REFERENCES locais(id_local) ON DELETE CASCADE,
  UNIQUE KEY unq_usuario_local (id_usuario, id_local) -- evita duplicar
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserindo locais para teste
INSERT INTO locais (id_local, nome, tipo, descricao, endereco, faixa_preco, horario_funcionamento, site, telefone, email_contato, redes_sociais, servicos, imagem_capa, avaliacao_media, criado_em, atualizado_em) VALUES
(1, 'Fortunata', 'Restaurante', 'Inspirado em um local para compartilhar bons momentos com amigos e familiares, nasceu a ideia da Fortunata. Inicialmente, a Fortunata era uma pizzaria conhecida por ser a casa dos amigos, pois cada amigo era prestigiado ao ter o seu nome no sabor de pizza preferido. Percebendo a necessidade de oferecer algo a mais, a Fortunata começou a elaborar pratos em sua cozinha – foi nesse momento que surgiu o restaurante Fortunata. Buscando o melhor para atender os amigos e clientes, a cozinha foi se especializando até que hoje roubam a cena da cidade. Atualmente, a casa conta com diversos pratos de risotos, massas, carnes e frutos do mar, além das famosas pizzas. Com cara nova, mas com a mesma tradição há 20 anos, a Fortunata é o local ideal para se reunir com os amigos e familiares. Na Fortunata os clientes são nossos amigos e transformamos o espaço em uma verdadeira extensão de sua casa. Nosso ambiente é tradicional e familiar e conta com uma música de fundo calma.', 'Shis Qi 09 Bloco C, Loja 6 - Lago Sul, Brasília, Distrito Federal 71625-009 Brasil', 'Alto', 'Seg-Sex 12:00 – 15:00 / 18:00 – 00:00; Sáb-Dom 12:00 – 16:00 / 18:00 – 00:00', 'https://www.fortunata.com.br/', '+55 61 3364-6111', 'fortunata@terra.com.br', 'https://www.instagram.com/fortunatarestaurante/', 'Almoço, Jantar, Dietas adequada para vegetarianos, Aceita cartão de crédito, Acesso para cadeirantes, American Express, Bar completo, Cadeiras para bebês, Entrega, Estacionamento disponível, Estacionamento na rua, Lugares para sentar, Mastercard, Mesas ao ar livre, Para levar, Reservas, Serve bebida alcoólica, Serviço de mesa, Vinho e cerveja, Visa, Wi-fi gratuito', 'img_68f63b505e344.jpg', 0, '2025-10-13 13:49:54', '2025-10-20 13:38:24'),
(2, 'Vert Café', 'Cafeteria', 'Um café feito para momentos. Somos um café de comida funcional abertos em 2019 e buscamos encantar nossos clientes com uma culinária que faz bem para o corpo e surpreende no sabor. Aqui todos os nossos ingredientes são orgânicos e sem glúten, temos várias opções veganas, vegetarianas e só usamos carnes com selo de sustentabilidade e não crueldade aos animais.', 'CLS 403 bl B - Asa Sul Loja 34, Brasília, Distrito Federal 70237-520 Brasil', 'Econômico', 'Seg-Dom: 09:00 – 22:00', 'http://www.facebook.com/vertcafebr/?ref=page_internal', '+55 61 99113-6424', 'vertcafeteria@gmail.com', '', 'Café, Café da manhã, Almoço, Jantar, Brunch, Drinks, Lugares para sentar, Para levar, Reservas, Serve bebida alcoólica, Serviço de mesa', 'img_68f63c74ddcac.jpg', 0, '2025-10-20 13:43:16', '2025-10-20 13:43:16'),
(3, 'Ordinário', 'Bar', 'Um boteco musical com muito samba e pagode, cerveja gelada, petiscos deliciosos e aquele clima animado. Gostamos de te dar liberdade para pedir: Comanda individual, atendimento na mesa ou no balcão, sempre simpático e ágil. Um dica final: Faz sua reserva no Ordi, que não tem erro.', 'Sbs, Scs Q. 2 Bl Q Lojas 5/6, Asa Sul, Brasília, Distrito Federal 70070-120 Brasil', 'Econômico', 'Seg-Dom: 17:00 – 02:00', 'https://www.ordinariobar.com/', '(61) 99184-4421', '', '', 'Brasileira, Restaurante com bar, Jantar, Serviço de mesa', 'img_68f63d253a18f.jpg', 0, '2025-10-20 13:46:13', '2025-10-20 13:46:13'),
(4, 'LONDON STREET', 'Bar', 'A London Street Pub foi fundada em março de 2016 pela Família Pires Mesquita sua história com Londres não era somente pela cidade maravilhosa, cercada de tradição da rainha e princesa tinha mais glamour dentro dos tradicionais pubs inglês, queríamos trazer para a capital toda aquele magnetismo inglês que tem quando se pede um pint em seu pub favorito, além do amor junto a Londres também iniciava um aprendizado de cervejas artesanais nacionais e internacionais, durante uma viagem de 20 dias a cidade eles puderam conhecer muita Ale e bitter dentro de um pub tradicionais . A história desse bar era trazer toda a tradição de um bar estilo inglês e um local onde as pessoas pudessem curtir toda a decoração que foi inspiradas em vários pub inglês, como Old Bank, Princess Luiza, after e outros lindos e famosos pubs localizado em grandes bairros de Londres.', 'Quadra Cln 214 Bloco D, 23 e 25 Comercial da 214 Norte - Virada para quadra residencial, Brasília, Distrito Federal 70873-540 Brasil', 'Médio', 'Seg-Dom: 17:00 - 22:00', 'http://www.facebook.com/londonstreetpub/', '+55 6191192482', 'londonstreetcervejaria@gmail.com', '', 'Pub com cerveja artesanal, Bar, Internacional, Europeia, Pub com restaurante, Restaurantes que servem cerveja, Aberto até tarde, Drinks', 'img_68f63e4105938.jpg', 0, '2025-10-20 13:50:57', '2025-10-20 14:47:04'),
(5, 'New Mercadito', 'Restaurante', 'O Mercadito surgiu com a proposta de atender ao público da cidade de forma leve e transada, unindo delícias, cultura e muita animação. A casa, que leva a assinatura do @beefeaterbrasil e @stellaartoisbrasil, aposta no diferencial de drinks incrementados e autorais, além de grandes clássicos da coquetelaria. O cardápio não fica atrás, com opções de aperitivos e pratos assinados por chefes de renome. Instalado em uma área de 250 m², o Mercadito foi projetado com referências cosmopolitas, inspirado em tendências industriais e modernas na concepção do espaço, considerando a natureza multiuso da casa. #MercaditoBSB #NewMercadito', 'Quadra Cls 201 Bloco a Loja 01 - Asa Sul, Brasília, Distrito Federal 70232-510 Brasil', 'Econômico', 'Seg-Dom: 16:00 – 01:00', 'http://www.newmercadito.com.br/', '(61) 90227504', 'adm.mercadito@gmail.com', '', 'Brasileira, Bar, Pub com restaurante, Restaurante com bar, Restaurantes que servem cerveja', 'img_68f63e9c4266b.jpg', 0, '2025-10-20 13:52:28', '2025-10-20 13:52:28'),
(6, 'Vila Tarêgo', 'Restaurante', 'Art. Burger. Bar. Wine New gastronomic, collective and cultural space of the city. Rustic design and reutilization in an incredible area of ​​2500 mt!', 'SMPW Quadra 05 Conjunto 12 Lote 5 Parte C Parkway Águas Claras, Brasília, Distrito Federal 71735-512 Brasil', 'Econômico', 'Seg-Dom: 17:00 – 23:00', 'https://vilatarego.negocio.site/', '+55 61 3053-3317', 'contato@vilatarego.com.br', '', 'Americana, Brasileira, Bar, Lanchonete', 'img_68f63f0837246.jpg', 0, '2025-10-20 13:54:16', '2025-10-20 13:54:16');

-- Inserindo usuários fictícios
INSERT INTO usuario (nome, email, senha) VALUES
('Usuário 1', 'user1@email.com', '12345678910'),
('Usuário 2', 'user2@email.com', '12345678910'),
('Usuário 3', 'user3@email.com', '12345678910'),
('Usuário 4', 'user4@email.com', '12345678910'),
('Usuário 5', 'user5@email.com', '12345678910'),
('Usuário 6', 'user6@email.com', '12345678910'),
('Usuário 7', 'user7@email.com', '12345678910'),
('Usuário 8', 'user8@email.com', '12345678910'),
('Usuário 9', 'user9@email.com', '12345678910'),
('Usuário 10', 'user10@email.com', '12345678910'),
('Usuário 11', 'user11@email.com', '12345678910'),
('Usuário 12', 'user12@email.com', '12345678910'),
('Usuário 13', 'user13@email.com', '12345678910'),
('Usuário 14', 'user14@email.com', '12345678910'),
('Usuário 15', 'user15@email.com', '12345678910'),
('Usuário 16', 'user16@email.com', '12345678910'),
('Usuário 17', 'user17@email.com', '12345678910'),
('Usuário 18', 'user18@email.com', '12345678910'),
('Usuário 19', 'user19@email.com', '12345678910'),
('Usuário 20', 'user20@email.com', '12345678910'),
('Usuário 21', 'user21@email.com', '12345678910'),
('Usuário 22', 'user22@email.com', '12345678910'),
('Usuário 23', 'user23@email.com', '12345678910'),
('Usuário 24', 'user24@email.com', '12345678910'),
('Usuário 25', 'user25@email.com', '12345678910'),
('Usuário 26', 'user26@email.com', '12345678910'),
('Usuário 27', 'user27@email.com', '12345678910'),
('Usuário 28', 'user28@email.com', '12345678910'),
('Usuário 29', 'user29@email.com', '12345678910'),
('Usuário 30', 'user30@email.com', '12345678910');

-- Inserindo avaliações fictícias
DELETE FROM avaliacoes WHERE id_avaliacao > 0;
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- Fortunata – 3 avaliações, todas 5⭐ (nota perfeita, pouca popularidade)
(1,1,5);
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- Vert Café – 30 avaliações, maioria 4–5⭐ (muito popular, nota alta)
(1,2,5),(2,2,5),(3,2,5),(4,2,4),(5,2,4),(6,2,5),(7,2,5),(8,2,4),(9,2,5),(10,2,5),
(11,2,4),(12,2,5),(13,2,4),(14,2,5),(15,2,4),(16,2,5),(17,2,5),(18,2,4),(19,2,5),(20,2,4),
(21,2,5),(22,2,4),(23,2,5),(24,2,5),(25,2,4),(26,2,5),(27,2,4),(28,2,5),(29,2,4),(30,2,5);
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- Ordinário – 15 avaliações medianas (popularidade intermediária, nota média)
(1,3,3),(2,3,4),(3,3,3),(4,3,4),(5,3,3),(6,3,3),(7,3,4),(8,3,3),(9,3,4),(10,3,3),
(11,3,4),(12,3,3),(13,3,4),(14,3,3),(15,3,4),(16,3,4),(17,3,5),(18,3,4),(19,3,5);
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- London Street – 8 avaliações boas, nota consistente (nota 4.3, pouca popularidade)
(1,4,4),(2,4,4),(3,4,5),(4,4,4),(5,4,4),(6,4,5),(7,4,4),(8,4,4);
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- New Mercadito – 25 avaliações, notas variadas (muito popular, média baixa)
(1,5,2),(2,5,3),(3,5,2),(4,5,3),(5,5,2),(6,5,2),(7,5,3),(8,5,2),(9,5,3),(10,5,2),
(11,5,3),(12,5,2),(13,5,3),(14,5,2),(15,5,3),(16,5,2),(17,5,3),(18,5,3),(19,5,2),(20,5,3),
(21,5,2),(22,5,3),(23,5,2),(24,5,3),(25,5,2);
INSERT INTO avaliacoes (id_usuario, id_local, nota) VALUES -- Vila Tarêgo – 10 avaliações, todas 5⭐ (nota máxima, popularidade média)
(1,6,5),(2,6,5),(4,6,5),(5,6,5),(6,6,5),(7,6,5),(8,6,5),(9,6,5),(10,6,5);