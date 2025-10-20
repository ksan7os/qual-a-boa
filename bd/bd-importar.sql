-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20-Out-2025 às 17:39
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `qualaboa`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id_avaliacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_local` int(11) NOT NULL,
  `nota` tinyint(4) NOT NULL CHECK (`nota` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id_avaliacao`, `id_usuario`, `id_local`, `nota`, `comentario`, `criado_em`, `atualizado_em`) VALUES
(1182, 1, 1, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1183, 1, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1184, 2, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1185, 3, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1186, 4, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1187, 5, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1188, 6, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1189, 7, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1190, 8, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1191, 9, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1192, 10, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1193, 11, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1194, 12, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1195, 13, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1196, 14, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1197, 15, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1198, 16, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1199, 17, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1200, 18, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1201, 19, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1202, 20, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1203, 21, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1204, 22, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1205, 23, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1206, 24, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1207, 25, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1208, 26, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1209, 27, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1210, 28, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1211, 29, 2, 4, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1212, 30, 2, 5, NULL, '2025-10-20 15:38:43', '2025-10-20 15:38:43'),
(1213, 1, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1214, 2, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1215, 3, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1216, 4, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1217, 5, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1218, 6, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1219, 7, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1220, 8, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1221, 9, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1222, 10, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1223, 11, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1224, 12, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1225, 13, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1226, 14, 3, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1227, 15, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1228, 16, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1229, 17, 3, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1230, 18, 3, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1231, 19, 3, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1232, 1, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1233, 2, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1234, 3, 4, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1235, 4, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1236, 5, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1237, 6, 4, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1238, 7, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1239, 8, 4, 4, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1240, 1, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1241, 2, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1242, 3, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1243, 4, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1244, 5, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1245, 6, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1246, 7, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1247, 8, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1248, 9, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1249, 10, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1250, 11, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1251, 12, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1252, 13, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1253, 14, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1254, 15, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1255, 16, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1256, 17, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1257, 18, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1258, 19, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1259, 20, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1260, 21, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1261, 22, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1262, 23, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1263, 24, 5, 3, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1264, 25, 5, 2, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1265, 1, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1266, 2, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1267, 4, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1268, 5, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1269, 6, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1270, 7, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1271, 8, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1272, 9, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44'),
(1273, 10, 6, 5, NULL, '2025-10-20 15:38:44', '2025-10-20 15:38:44');

-- --------------------------------------------------------

--
-- Estrutura da tabela `locais`
--

CREATE TABLE `locais` (
  `id_local` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `tipo` enum('Restaurante','Bar','Cafeteria','Lanchonete','Pizzaria','Parque','Praça','Museu','Teatro','Show','Evento','Feira','Mercado','Atração Turística','Outro') NOT NULL,
  `descricao` text DEFAULT NULL,
  `endereco` varchar(255) NOT NULL,
  `faixa_preco` enum('Econômico','Médio','Alto') NOT NULL,
  `horario_funcionamento` text DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `telefone` varchar(25) DEFAULT NULL,
  `email_contato` varchar(120) DEFAULT NULL,
  `redes_sociais` text DEFAULT NULL,
  `servicos` text DEFAULT NULL,
  `imagem_capa` varchar(255) DEFAULT NULL,
  `avaliacao_media` float DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `locais`
--

INSERT INTO `locais` (`id_local`, `nome`, `tipo`, `descricao`, `endereco`, `faixa_preco`, `horario_funcionamento`, `site`, `telefone`, `email_contato`, `redes_sociais`, `servicos`, `imagem_capa`, `avaliacao_media`, `criado_em`, `atualizado_em`) VALUES
(1, 'Fortunata', 'Restaurante', 'Inspirado em um local para compartilhar bons momentos com amigos e familiares, nasceu a ideia da Fortunata. Inicialmente, a Fortunata era uma pizzaria conhecida por ser a casa dos amigos, pois cada amigo era prestigiado ao ter o seu nome no sabor de pizza preferido. Percebendo a necessidade de oferecer algo a mais, a Fortunata começou a elaborar pratos em sua cozinha – foi nesse momento que surgiu o restaurante Fortunata. Buscando o melhor para atender os amigos e clientes, a cozinha foi se especializando até que hoje roubam a cena da cidade. Atualmente, a casa conta com diversos pratos de risotos, massas, carnes e frutos do mar, além das famosas pizzas. Com cara nova, mas com a mesma tradição há 20 anos, a Fortunata é o local ideal para se reunir com os amigos e familiares. Na Fortunata os clientes são nossos amigos e transformamos o espaço em uma verdadeira extensão de sua casa. Nosso ambiente é tradicional e familiar e conta com uma música de fundo calma.', 'Shis Qi 09 Bloco C, Loja 6 - Lago Sul, Brasília, Distrito Federal 71625-009 Brasil', 'Alto', 'Seg-Sex 12:00 – 15:00 / 18:00 – 00:00; Sáb-Dom 12:00 – 16:00 / 18:00 – 00:00', 'https://www.fortunata.com.br/', '+55 61 3364-6111', 'fortunata@terra.com.br', 'https://www.instagram.com/fortunatarestaurante/', 'Almoço, Jantar, Dietas adequada para vegetarianos, Aceita cartão de crédito, Acesso para cadeirantes, American Express, Bar completo, Cadeiras para bebês, Entrega, Estacionamento disponível, Estacionamento na rua, Lugares para sentar, Mastercard, Mesas ao ar livre, Para levar, Reservas, Serve bebida alcoólica, Serviço de mesa, Vinho e cerveja, Visa, Wi-fi gratuito', 'img_68f63b505e344.jpg', 0, '2025-10-13 13:49:54', '2025-10-20 13:38:24'),
(2, 'Vert Café', 'Cafeteria', 'Um café feito para momentos. Somos um café de comida funcional abertos em 2019 e buscamos encantar nossos clientes com uma culinária que faz bem para o corpo e surpreende no sabor. Aqui todos os nossos ingredientes são orgânicos e sem glúten, temos várias opções veganas, vegetarianas e só usamos carnes com selo de sustentabilidade e não crueldade aos animais.', 'CLS 403 bl B - Asa Sul Loja 34, Brasília, Distrito Federal 70237-520 Brasil', 'Econômico', 'Seg-Dom: 09:00 – 22:00', 'http://www.facebook.com/vertcafebr/?ref=page_internal', '+55 61 99113-6424', 'vertcafeteria@gmail.com', '', 'Café, Café da manhã, Almoço, Jantar, Brunch, Drinks, Lugares para sentar, Para levar, Reservas, Serve bebida alcoólica, Serviço de mesa', 'img_68f63c74ddcac.jpg', 0, '2025-10-20 13:43:16', '2025-10-20 13:43:16'),
(3, 'Ordinário', 'Bar', 'Um boteco musical com muito samba e pagode, cerveja gelada, petiscos deliciosos e aquele clima animado. Gostamos de te dar liberdade para pedir: Comanda individual, atendimento na mesa ou no balcão, sempre simpático e ágil. Um dica final: Faz sua reserva no Ordi, que não tem erro.', 'Sbs, Scs Q. 2 Bl Q Lojas 5/6, Asa Sul, Brasília, Distrito Federal 70070-120 Brasil', 'Econômico', 'Seg-Dom: 17:00 – 02:00', 'https://www.ordinariobar.com/', '(61) 99184-4421', '', '', 'Brasileira, Restaurante com bar, Jantar, Serviço de mesa', 'img_68f63d253a18f.jpg', 0, '2025-10-20 13:46:13', '2025-10-20 13:46:13'),
(4, 'LONDON STREET', 'Bar', 'A London Street Pub foi fundada em março de 2016 pela Família Pires Mesquita sua história com Londres não era somente pela cidade maravilhosa, cercada de tradição da rainha e princesa tinha mais glamour dentro dos tradicionais pubs inglês, queríamos trazer para a capital toda aquele magnetismo inglês que tem quando se pede um pint em seu pub favorito, além do amor junto a Londres também iniciava um aprendizado de cervejas artesanais nacionais e internacionais, durante uma viagem de 20 dias a cidade eles puderam conhecer muita Ale e bitter dentro de um pub tradicionais . A história desse bar era trazer toda a tradição de um bar estilo inglês e um local onde as pessoas pudessem curtir toda a decoração que foi inspiradas em vários pub inglês, como Old Bank, Princess Luiza, after e outros lindos e famosos pubs localizado em grandes bairros de Londres.', 'Quadra Cln 214 Bloco D, 23 e 25 Comercial da 214 Norte - Virada para quadra residencial, Brasília, Distrito Federal 70873-540 Brasil', 'Médio', 'Seg-Dom: 17:00 - 22:00', 'http://www.facebook.com/londonstreetpub/', '+55 6191192482', 'londonstreetcervejaria@gmail.com', '', 'Pub com cerveja artesanal, Bar, Internacional, Europeia, Pub com restaurante, Restaurantes que servem cerveja, Aberto até tarde, Drinks', 'img_68f63e4105938.jpg', 0, '2025-10-20 13:50:57', '2025-10-20 14:47:04'),
(5, 'New Mercadito', 'Restaurante', 'O Mercadito surgiu com a proposta de atender ao público da cidade de forma leve e transada, unindo delícias, cultura e muita animação. A casa, que leva a assinatura do @beefeaterbrasil e @stellaartoisbrasil, aposta no diferencial de drinks incrementados e autorais, além de grandes clássicos da coquetelaria. O cardápio não fica atrás, com opções de aperitivos e pratos assinados por chefes de renome. Instalado em uma área de 250 m², o Mercadito foi projetado com referências cosmopolitas, inspirado em tendências industriais e modernas na concepção do espaço, considerando a natureza multiuso da casa. #MercaditoBSB #NewMercadito', 'Quadra Cls 201 Bloco a Loja 01 - Asa Sul, Brasília, Distrito Federal 70232-510 Brasil', 'Econômico', 'Seg-Dom: 16:00 – 01:00', 'http://www.newmercadito.com.br/', '(61) 90227504', 'adm.mercadito@gmail.com', '', 'Brasileira, Bar, Pub com restaurante, Restaurante com bar, Restaurantes que servem cerveja', 'img_68f63e9c4266b.jpg', 0, '2025-10-20 13:52:28', '2025-10-20 13:52:28'),
(6, 'Vila Tarêgo', 'Restaurante', 'Art. Burger. Bar. Wine New gastronomic, collective and cultural space of the city. Rustic design and reutilization in an incredible area of ​​2500 mt!', 'SMPW Quadra 05 Conjunto 12 Lote 5 Parte C Parkway Águas Claras, Brasília, Distrito Federal 71735-512 Brasil', 'Econômico', 'Seg-Dom: 17:00 – 23:00', 'https://vilatarego.negocio.site/', '+55 61 3053-3317', 'contato@vilatarego.com.br', '', 'Americana, Brasileira, Bar, Lanchonete', 'img_68f63f0837246.jpg', 0, '2025-10-20 13:54:16', '2025-10-20 13:54:16');

-- --------------------------------------------------------

--
-- Estrutura da tabela `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(1, 3, '25e94e903593af29a9cff9b109d2a8ce8be5473ec1fb6e3e5a3709f0a0fb54fd60e5d7653d17b9e5f9857204aa0e947da2ed', '2025-10-16 20:02:34', '2025-10-17 02:02:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_perfil` varchar(255) DEFAULT 'default-profile.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nome`, `email`, `senha`, `tipo_usuario`, `data_criacao`, `foto_perfil`) VALUES
(1, 'Administrador', 'admin@qualaboa.com', '$2y$10$m.yk9gZS7WSrIOKY7bpG9.IK1Sq/FNkwGzsp3j6aVs1kVx0axqFOG', 'admin', '2025-10-13 13:27:30', 'default-profile.jpg'),
(2, 'Teste', 'teste@gmail.com', '$2y$10$6u6DX5vL.JvaUmY18XB7HudLPK5jDZdH1uwC.TviM7oYr8kiSsr4C', 'usuario', '2025-10-13 14:06:41', 'default-profile.jpg'),
(3, 'kauan', 'kauansan7os@gmail.com', '$2y$10$5GjFtzJB528ZJnhYruen2eTbCvsY/Dsf264nbLyomCJUWiHiy7na.', 'usuario', '2025-10-16 20:02:31', 'default-profile.jpg'),
(4, 'Usuário 1', 'user1@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(5, 'Usuário 2', 'user2@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(6, 'Usuário 3', 'user3@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(7, 'Usuário 4', 'user4@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(8, 'Usuário 5', 'user5@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(9, 'Usuário 6', 'user6@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(10, 'Usuário 7', 'user7@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(11, 'Usuário 8', 'user8@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(12, 'Usuário 9', 'user9@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(13, 'Usuário 10', 'user10@email.com', '123', 'usuario', '2025-10-20 14:39:39', 'default-profile.jpg'),
(14, 'Usuário 14', 'user14@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(15, 'Usuário 15', 'user15@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(16, 'Usuário 16', 'user16@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(17, 'Usuário 17', 'user17@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(18, 'Usuário 18', 'user18@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(19, 'Usuário 19', 'user19@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(20, 'Usuário 20', 'user20@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(21, 'Usuário 21', 'user21@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(22, 'Usuário 22', 'user22@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(23, 'Usuário 23', 'user23@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(24, 'Usuário 24', 'user24@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(25, 'Usuário 25', 'user25@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(26, 'Usuário 26', 'user26@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(27, 'Usuário 27', 'user27@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(28, 'Usuário 28', 'user28@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(29, 'Usuário 29', 'user29@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg'),
(30, 'Usuário 30', 'user30@email.com', '123', 'usuario', '2025-10-20 14:55:34', 'default-profile.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id_avaliacao`),
  ADD UNIQUE KEY `unq_user_local` (`id_usuario`,`id_local`),
  ADD KEY `fk_avaliacoes_local` (`id_local`);

--
-- Índices para tabela `locais`
--
ALTER TABLE `locais`
  ADD PRIMARY KEY (`id_local`),
  ADD KEY `idx_locais_tipo` (`tipo`),
  ADD KEY `idx_locais_faixa` (`faixa_preco`),
  ADD KEY `idx_locais_nome` (`nome`);

--
-- Índices para tabela `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_password_resets_usuario` (`user_id`);

--
-- Índices para tabela `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id_avaliacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1274;

--
-- AUTO_INCREMENT de tabela `locais`
--
ALTER TABLE `locais`
  MODIFY `id_local` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `fk_avaliacoes_local` FOREIGN KEY (`id_local`) REFERENCES `locais` (`id_local`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avaliacoes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
