-- Criação do banco
CREATE DATABASE IF NOT EXISTS `qualaboa`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `qualaboa`;

-- ==================================================================================
-- Tabelas-mãe (criadas primeiro)
-- ==================================================================================

-- Usuário
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `id_usuario` INT(11) NOT NULL AUTO_INCREMENT,
  `nome`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(120) NOT NULL,
  `senha`      VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `foto_perfil`  VARCHAR(255) DEFAULT 'default-profile.jpg',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locais
DROP TABLE IF EXISTS `locais`;
CREATE TABLE `locais` (
  `id_local` INT(11) NOT NULL AUTO_INCREMENT,
  `nome`     VARCHAR(120) NOT NULL,
  `tipo`     ENUM(
               'Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria','Pub','Balada',
               'Parque','Trilha','Praça','Museu','Teatro','Cinema','Show','Evento',
               'Feira','Mercado','Centro Cultural','Atração Turística','Outro'
             ) NOT NULL,
  `descricao`              TEXT DEFAULT NULL,
  `endereco`               VARCHAR(255) NOT NULL,
  `faixa_preco`            ENUM('Econômico','Médio','Alto') NOT NULL,
  `horario_funcionamento`  TEXT DEFAULT NULL,
  `site`                   VARCHAR(255) DEFAULT NULL,
  `telefone`               VARCHAR(25)  DEFAULT NULL,
  `email_contato`          VARCHAR(120) DEFAULT NULL,
  `redes_sociais`          TEXT DEFAULT NULL,
  `servicos`               TEXT DEFAULT NULL,
  `imagem_capa`            VARCHAR(255) DEFAULT NULL,
  `avaliacao_media`        FLOAT DEFAULT 0,
  `criado_em`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_local`),
  KEY `idx_locais_tipo` (`tipo`),
  KEY `idx_locais_faixa` (`faixa_preco`),
  KEY `idx_locais_nome`  (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================================================
-- Tabelas-filhas (dependem das de cima)
-- ==================================================================================

-- Avaliações
DROP TABLE IF EXISTS `avaliacoes`;
CREATE TABLE `avaliacoes` (
  `id_avaliacao`  INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario`    INT(11) NOT NULL,
  `id_local`      INT(11) NOT NULL,
  `nota`          TINYINT(4) NOT NULL CHECK (`nota` BETWEEN 1 AND 5),
  `comentario`    TEXT DEFAULT NULL,
  `criado_em`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_avaliacao`),
  UNIQUE KEY `unq_avaliacoes_usuario_local` (`id_usuario`,`id_local`),
  KEY `fk_avaliacoes_local` (`id_local`),
  CONSTRAINT `fk_avaliacoes_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_avaliacoes_local`
    FOREIGN KEY (`id_local`)   REFERENCES `locais`  (`id_local`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estou Indo
DROP TABLE IF EXISTS `estou_indo`;
CREATE TABLE `estou_indo` (
  `id_estou_indo`     INT(11) NOT NULL AUTO_INCREMENT,
  `id_usuario`        INT(11) NOT NULL,
  `id_local`          INT(11) NOT NULL,
  `data_marcacao`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  `desmarcado_em`     DATETIME DEFAULT NULL,
  `desmarcado_motivo` ENUM('auto','manual') DEFAULT NULL,
  PRIMARY KEY (`id_estou_indo`),
  KEY `fk_estou_indo_local` (`id_local`),
  KEY `idx_ei_usuario_ativo` (`id_usuario`,`desmarcado_em`),
  CONSTRAINT `fk_estou_indo_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_estou_indo_local`
    FOREIGN KEY (`id_local`)   REFERENCES `locais`  (`id_local`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feed Feedback
DROP TABLE IF EXISTS `feed_feedback`;
CREATE TABLE `feed_feedback` (
  `id`         BIGINT(20) NOT NULL AUTO_INCREMENT,
  `id_usuario` INT(11)    NOT NULL,
  `id_local`   INT(11)    NOT NULL,
  `acao`       ENUM('view','skip','open','like') NOT NULL,
  `criado_em`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_local` (`id_usuario`,`id_local`),
  KEY `idx_user_time`  (`id_usuario`,`criado_em`),
  CONSTRAINT `fk_ff_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `fk_ff_local`
    FOREIGN KEY (`id_local`)   REFERENCES `locais`  (`id_local`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Preferences
DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id_usuario`   INT(11) NOT NULL,
  `tipos_csv`    VARCHAR(255) DEFAULT NULL,
  `horarios_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
                   CHECK (JSON_VALID(`horarios_json`)),
  PRIMARY KEY (`id_usuario`),
  CONSTRAINT `fk_up_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) NOT NULL,
  `token`      VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_password_resets_usuario` (`user_id`),
  CONSTRAINT `fk_password_resets_usuario`
    FOREIGN KEY (`user_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================================================
-- Inserção dos dados
-- ==================================================================================

-- Admin
INSERT INTO usuario (nome, email, senha, tipo_usuario)
VALUES ('Administrador','admin@qualaboa.com',
'$2y$10$m.yk9gZS7WSrIOKY7bpG9.IK1Sq/FNkwGzsp3j6aVs1kVx0axqFOG','admin'); -- Senha: admin

-- Locais
INSERT INTO locais
(id_local, nome, tipo, descricao, endereco, faixa_preco, horario_funcionamento, site, telefone, email_contato, redes_sociais, servicos, imagem_capa, avaliacao_media, criado_em, atualizado_em)
VALUES
(NULL, 'Varanda do Lago', 'Restaurante',
 'Culinária brasileira com foco em peixes e frutos do mar. Ambiente amplo com vista para o lago e mesas externas.',
 'QI 15 Conjunto 3, Lago Sul, Brasília, DF 71635-000', 'Alto',
 'Seg-Qui 12:00 – 15:30 / 18:00 – 23:00; Sex-Dom 12:00 – 23:30',
 'https://varandadolago.com.br', '+55 61 3540-2200', 'contato@varandadolago.com.br',
 'https://instagram.com/varandadolago',
 'Reservas, Vinho e cerveja, Mesas ao ar livre, Estacionamento, Acesso para cadeirantes, Cartões',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Esquina 209', 'Bar',
 'Drinks autorais e porções para compartilhar. Música ambiente e promoções no happy hour.',
 'CLS 209 Bloco C, Loja 18 - Asa Sul, Brasília, DF 70273-530', 'Médio',
 'Ter-Sáb 17:30 – 01:00; Dom 17:00 – 23:00',
 NULL, '+55 61 3569-0209', 'contato@esquina209.com',
 'https://instagram.com/esquina209',
 'Bar completo, Mesas ao ar livre, Cartões, Pet friendly, Para levar',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Grão Cerrado', 'Cafeteria',
 'Torras próprias e métodos filtrados; cardápio com brunch, sanduíches e doces de produção diária.',
 'CLN 206 Bloco B, Loja 05 - Asa Norte, Brasília, DF 70844-520', 'Econômico',
 'Seg-Sex 08:00 – 20:00; Sáb 09:00 – 19:00; Dom 09:00 – 15:00',
 'https://graocerrado.com', '+55 61 3222-6620', 'oi@graocerrado.com',
 'https://instagram.com/graocerrado',
 'Wi-fi gratuito, Opções veganas, Acesso para cadeirantes, Cartões',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Mama Nona', 'Pizzaria',
 'Pizzas de fermentação lenta com ingredientes importados e sabores autorais. Opções vegetarianas e sem glúten.',
 'AD Sul QD 08, Loja 10 - Águas Claras, Brasília, DF', 'Médio',
 'Ter-Dom 18:00 – 23:30',
 'https://mamanona.com.br', '+55 61 3255-4040', 'pedido@mamanona.com.br',
 'https://instagram.com/pizzeriamamanona',
 'Entrega, Para levar, Cartões, Vinho e cerveja, Mesas ao ar livre',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Parque Veredas', 'Parque',
 'Área verde com pista de cooper, aparelhos de ginástica e espaços para piquenique. Ideal para famílias.',
 'EPIG Área Especial Parque Veredas - Brasília, DF', 'Econômico',
 'Diariamente 06:00 – 20:00',
 NULL, '+55 61 0000-2222', 'parques@df.gov.br',
 'https://instagram.com/parqueveredas',
 'Entrada gratuita, Estacionamento, Banheiros, Segurança, Área infantil',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Praça das Jabuticabeiras', 'Praça',
 'Praça arborizada com bancos, feirinha de artesanato aos sábados e apresentações de artistas locais.',
 'SQN 115 Área Verde - Asa Norte, Brasília, DF', 'Econômico',
 'Aberto 24 horas',
 NULL, '+55 61 0000-3333', 'administracao@bsb.df.gov.br',
 'https://instagram.com/pracajabuticabeiras',
 'Entrada gratuita, Pet friendly, Mesas ao ar livre, Eventos sazonais',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Trilha do Mirante Norte', 'Trilha',
 'Caminhada leve de 3 km até mirante com vista panorâmica. Recomenda-se protetor solar e água.',
 'Parque do Mirante Norte – acesso pela DF-003, Brasília, DF', 'Econômico',
 'Diariamente 06:00 – 18:00',
 NULL, '+55 61 0000-4444', 'contato@mirantenorte.df.gov.br',
 'https://instagram.com/trilhamirantenorte',
 'Entrada gratuita, Guias locais, Estacionamento, Sinalização de trilha',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Mercado da Quadra 112', 'Mercado',
 'Mercado de bairro com bancas de alimentos orgânicos, queijos artesanais e floricultura.',
 'SQS 112 Área Comercial - Asa Sul, Brasília, DF', 'Econômico',
 'Seg-Sáb 08:00 – 20:00; Dom 08:00 – 14:00',
 NULL, '+55 61 3344-0112', 'info@mercado112.com',
 'https://instagram.com/mercado112',
 'Cartões, Estacionamento, Área coberta, Acesso para cadeirantes',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Cine Alameda', 'Cinema',
 'Sala moderna com projeção a laser e programação de filmes independentes e mostras temáticas.',
 'QS 05 Rua 12, Lote 04 - Riacho Fundo I, Brasília, DF', 'Médio',
 'Seg-Dom 14:00 – 23:30',
 'https://cinealameda.com.br', '+55 61 3577-5544', 'contato@cinealameda.com.br',
 'https://instagram.com/cinealameda',
 'Acesso para cadeirantes, Reservas online, Bomboniere, Cartões',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Teatro Solar', 'Teatro',
 'Espaço cênico para peças autorais, stand-up e festivais estudantis. Poltronas confortáveis e boa acústica.',
 'SCS Quadra 7, Conjunto B - Brasília, DF', 'Médio',
 'Qua-Dom 19:00 – 23:00',
 'https://teatrosolar.com.br', '+55 61 3666-7700', 'bilheteria@teatrosolar.com.br',
 'https://instagram.com/teatrosolarbsb',
 'Reservas, Acesso para cadeirantes, Estacionamento, Ar-condicionado, Cartões',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Bistrô Dona Celina', 'Restaurante',
 'Menu sazonal com ingredientes do Cerrado; pratos autorais em ambiente intimista e acolhedor.',
 'CLS 407 Bloco D, Loja 06 - Asa Sul, Brasília, DF', 'Alto',
 'Ter-Sáb 19:00 – 23:30; Dom 12:30 – 16:30',
 'https://bistrodonacelina.com', '+55 61 3999-0407', 'reservas@bistrodonacelina.com',
 'https://instagram.com/bistrodonacelina',
 'Reservas, Vinho e cerveja, Serviço de mesa, Cartões, Acesso para cadeirantes',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'BrewLab 406', 'Pub',
 'Taproom com 12 torneiras rotativas de chope artesanal e carta de burgers smash. DJs aos sábados.',
 'CLS 406 Bloco C, Loja 20 - Asa Sul, Brasília, DF', 'Médio',
 'Qua-Qui 18:00 – 00:00; Sex-Sáb 18:00 – 02:00',
 'https://brewlab406.com', '+55 61 3777-0406', 'contato@brewlab406.com',
 'https://instagram.com/brewlab406',
 'Bar completo, Música ao vivo, Mesas ao ar livre, Cartões, Pet friendly',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL, 'Feira da Praça Central', 'Feira',
 'Feira de artes, gastronomia e designers locais aos domingos, com música e atividades infantis.',
 'Praça Central do Setor Comercial, Brasília, DF', 'Econômico',
 'Dom 09:00 – 15:00',
 NULL, '+55 61 0000-5555', 'feiracentral@bsb.df.gov.br',
 'https://instagram.com/feirapracacentral',
 'Entrada gratuita, Área coberta parcial, Palco cultural, Cartões',
 'default-bg-locais.png', 0, NOW(), NOW()),

(NULL,'Paladar do Cerrado','Restaurante',
 'Cozinha autoral com ingredientes do bioma; menu degustação aos fins de semana.',
 'CLN 309 Bloco B, Loja 04 - Asa Norte, Brasília, DF','Alto',
 'Ter-Sáb 19:00 – 23:30; Dom 12:30 – 16:30',
 'https://paladardocerrado.com.br','+55 61 3555-0309','reservas@paladardocerrado.com.br',
 'https://instagram.com/paladardocerrado',
 'Reservas, Vinho e cerveja, Serviço de mesa, Cartões, Acesso para cadeirantes',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Cantina Vila Itália','Restaurante',
 'Massas artesanais, molhos frescos e carta de vinhos enxuta; ambiente familiar.',
 'CLS 205 Bloco C, Loja 16 - Asa Sul, Brasília, DF','Médio',
 'Seg-Dom 11:30 – 15:30 / 18:30 – 23:00',
 'https://vilaitalia.com.br','+55 61 3344-0205','contato@vilaitalia.com.br',
 'https://instagram.com/cantinavilaitalia',
 'Almoço, Jantar, Cartões, Cadeiras para bebês, Para levar',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Brasa & Lenha','Restaurante',
 'Grelhados na parrilla e acompanhamentos de estação; carta de cervejas especiais.',
 'EQS 110/111 Bloco D, Loja 08 - Asa Sul, Brasília, DF','Médio',
 'Ter-Dom 12:00 – 23:00',
 NULL,'+55 61 3777-0110','contato@brasaelenha.com',
 'https://instagram.com/brasaelenhabsb',
 'Reservas, Para levar, Cartões, Mesas ao ar livre',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Cozinha do Eixo','Restaurante',
 'Menu executivo no almoço e pratos contemporâneos à noite; opções vegetarianas.',
 'SIG Quadra 02, Lote 15 - Brasília, DF','Econômico',
 'Seg-Sáb 11:30 – 22:30',
 NULL,'+55 61 3250-0002','oi@cozinhadoeixo.com.br',
 'https://instagram.com/cozinhadoeixo',
 'Almoço, Jantar, Opções veganas, Wi-fi gratuito, Cartões',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Bar 406 Norte','Bar',
 'Drinks autorais, chope gelado e petiscos; DJs às sextas.',
 'CLN 406 Bloco E, Loja 12 - Asa Norte, Brasília, DF','Médio',
 'Qua-Sáb 18:00 – 02:00; Dom 17:00 – 00:00',
 'https://bar406norte.com','+55 61 3666-0406','atendimento@bar406norte.com',
 'https://instagram.com/bar406norte',
 'Bar completo, Música ao vivo, Mesas ao ar livre, Cartões, Pet friendly',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Boteco da Esplanada','Bar',
 'Clássicos de boteco, choppinho e samba aos sábados.',
 'SCS Quadra 02, Conj. C, Loja 07 - Brasília, DF','Econômico',
 'Ter-Dom 17:00 – 01:00',
 NULL,'+55 61 3212-0202','contato@botecodaesplanada.com.br',
 'https://instagram.com/botecodaesplanada',
 'Para levar, Cartões, Música ao vivo, Pet friendly',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Quintal do Chopp','Bar',
 'Chopes artesanais rotativos e hambúrguer smash; área externa ampla.',
 'QE 19 Conj. L, Lote 04 - Guará II, Brasília, DF','Médio',
 'Qui-Sáb 18:00 – 02:00; Dom 17:00 – 23:30',
 'https://quintaldochopp.com','+55 61 3599-1919','fala@quintaldochopp.com',
 'https://instagram.com/quintaldochopp',
 'Bar completo, Mesas ao ar livre, Cartões, Para levar',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Terraço 215','Bar',
 'Rooftop com vista, carta de coquetéis de autor e tapas.',
 'CLS 215 Bloco A, Cobertura - Asa Sul, Brasília, DF','Alto',
 'Sex-Dom 18:00 – 02:00',
 'https://terraco215.com.br','+55 61 3388-0215','reservas@terraco215.com.br',
 'https://instagram.com/terraco215',
 'Reservas, Bar completo, Mesas ao ar livre, Cartões',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Forno Nobre','Pizzaria',
 'Fermentação natural 48h, forno a lenha e ingredientes selecionados.',
 'QNA 05 Lote 23 - Taguatinga Norte, Brasília, DF','Médio',
 'Ter-Dom 18:00 – 23:30',
 'https://fornonobre.com.br','+55 61 3355-0505','pedido@fornonobre.com.br',
 'https://instagram.com/fornonobre',
 'Entrega, Para levar, Cartões, Vinho e cerveja',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Pizza da Vila 413','Pizzaria',
 'Pizzas clássicas e sabores autorais; opção sem glúten sob encomenda.',
 'CLN 413 Bloco C, Loja 02 - Asa Norte, Brasília, DF','Médio',
 'Qua-Seg 18:00 – 23:00',
 NULL,'+55 61 3444-0413','contato@pizzadavila413.com.br',
 'https://instagram.com/pizzadavila413',
 'Para levar, Entrega, Cartões, Vinho e cerveja',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Dona Margherita','Pizzaria',
 'Estilo napolitano, massa leve e bordas altas; sabores sazonais.',
 'QS 12 Rua 05, Lote 10 - Riacho Fundo I, Brasília, DF','Médio',
 'Ter-Dom 18:30 – 23:30',
 'https://donamargherita.com','+55 61 3570-1212','oi@donamargherita.com',
 'https://instagram.com/donamargheritabsb',
 'Para levar, Cartões, Vinho e cerveja, Mesas ao ar livre',
 'default-bg-locais.png',0,NOW(),NOW()),

(NULL,'Nápoles Asa Norte','Pizzaria',
 'Receitas tradicionais italianas e ingredientes importados; ambiente acolhedor.',
 'CLN 210 Bloco D, Loja 06 - Asa Norte, Brasília, DF','Alto',
 'Ter-Dom 19:00 – 23:45',
 'https://napolesasanorte.com.br','+55 61 3205-0210','reservas@napolesasanorte.com.br',
 'https://instagram.com/napolesasanorte',
 'Reservas, Vinho e cerveja, Serviço de mesa, Cartões',
 'default-bg-locais.png',0,NOW(),NOW());

-- ==================================================================================
-- Visualizar tabelas
-- ==================================================================================

USE qualaboa;
SELECT * FROM usuario;

/* 
-> Inserção de dados para teste do RF12

USE qualaboa;
INSERT INTO estou_indo (id_usuario, id_local, data_marcacao, desmarcado_em, desmarcado_motivo)
VALUES (2, 2, DATE_SUB(NOW(), INTERVAL 36 HOUR), DATE_SUB(NOW(), INTERVAL 24 HOUR), 'auto'); 
*/