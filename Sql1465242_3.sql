-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 89.46.111.225:3306
-- Creato il: Ago 22, 2025 alle 18:00
-- Versione del server: 5.7.44-48-log
-- Versione PHP: 8.0.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Sql1465242_3`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `amicizie`
--

CREATE TABLE `amicizie` (
  `id` int(11) NOT NULL,
  `utente_richiedente_id` int(11) NOT NULL,
  `utente_destinatario_id` int(11) NOT NULL,
  `stato` enum('pending','accepted','rejected','blocked') NOT NULL DEFAULT 'pending',
  `data_richiesta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_accettazione` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `amicizie`
--

INSERT INTO `amicizie` (`id`, `utente_richiedente_id`, `utente_destinatario_id`, `stato`, `data_richiesta`, `data_accettazione`) VALUES
(2, 25, 26, 'accepted', '2025-08-14 16:46:31', '2025-08-14 16:46:38'),
(3, 26, 35, 'pending', '2025-08-14 17:51:07', NULL),
(4, 26, 27, 'pending', '2025-08-14 20:01:04', NULL),
(5, 26, 31, 'accepted', '2025-08-14 20:36:42', '2025-08-14 20:37:40');

-- --------------------------------------------------------

--
-- Struttura della tabella `annunci`
--

CREATE TABLE `annunci` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `descrizione` text,
  `comune_id` int(11) DEFAULT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `data_pubblicazione` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita`
--

CREATE TABLE `attivita` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `comune` varchar(255) DEFAULT NULL,
  `descrizione` text,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `commenti`
--

CREATE TABLE `commenti` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `commento` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `commenti`
--

INSERT INTO `commenti` (`id`, `post_id`, `utente_id`, `commento`, `parent_id`, `created_at`) VALUES
(136, 271, 26, 'E\' vero, è sempre un problema.', NULL, '2025-08-12 18:52:17'),
(137, 272, 25, 'Si stanno mobilitando per sistemarla', NULL, '2025-08-12 19:00:12'),
(138, 272, 26, '@Diego Cattaneo Ho visto che per adesso hanno messo solo un cartello di pericolo. Speriamo che la sistemino il prima possibile', 137, '2025-08-12 19:01:26'),
(139, 272, 27, '@Diego Cattaneo meno male', 137, '2025-08-12 19:16:05');

-- --------------------------------------------------------

--
-- Struttura della tabella `commenti_reazioni`
--

CREATE TABLE `commenti_reazioni` (
  `id` int(11) NOT NULL,
  `commento_id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dump dei dati per la tabella `commenti_reazioni`
--

INSERT INTO `commenti_reazioni` (`id`, `commento_id`, `utente_id`, `created_at`) VALUES
(35, 136, 26, '2025-08-12 18:52:23'),
(38, 136, 25, '2025-08-12 19:02:09'),
(39, 137, 27, '2025-08-12 19:16:07'),
(41, 139, 25, '2025-08-14 00:33:57'),
(45, 138, 26, '2025-08-14 20:36:02'),
(46, 137, 26, '2025-08-14 20:36:15'),
(48, 137, 25, '2025-08-15 04:07:29');

-- --------------------------------------------------------

--
-- Struttura della tabella `comuni`
--

CREATE TABLE `comuni` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `provincia` varchar(255) DEFAULT NULL,
  `regione` varchar(255) DEFAULT NULL,
  `cap` varchar(10) DEFAULT NULL,
  `abitanti` int(11) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `numeri_utili` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `comuni`
--

INSERT INTO `comuni` (`id`, `nome`, `provincia`, `regione`, `cap`, `abitanti`, `lat`, `lng`, `numeri_utili`) VALUES
(1, 'Marcallo con Casone', 'MI', 'Lombardia', '20010', 5900, '45.48721990', '8.87327610', NULL),
(2, 'Milano', 'MI', 'Lombardia', '20100', 1350000, '45.46419430', '9.18963460', NULL),
(3, 'Abbiategrasso', 'MI', 'Lombardia', '20081', 32000, '45.39873300', '8.91622990', NULL),
(4, 'Roma', 'RM', 'Lazio', '00100', 2870000, '41.89332030', '12.48293210', NULL),
(5, 'Fiumicino', 'RM', 'Lazio', '00054', 80000, '41.77121450', '12.22788550', NULL),
(6, 'Napoli', 'NA', 'Campania', '80100', 960000, '40.83588460', '14.24876790', NULL),
(7, 'Salerno', 'SA', 'Campania', '84100', 130000, '40.68036010', '14.75945420', NULL),
(8, 'Torino', 'TO', 'Piemonte', '10100', 870000, '45.06775510', '7.68248920', NULL),
(9, 'Firenze', 'FI', 'Toscana', '50100', 370000, '43.76979550', '11.25564040', NULL),
(10, 'Venezia', 'VE', 'Veneto', '30100', 260000, '45.43719080', '12.33458980', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `dirette`
--

CREATE TABLE `dirette` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `descrizione` text,
  `embed_url` text,
  `data_creazione` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `eventi`
--

CREATE TABLE `eventi` (
  `id` int(11) NOT NULL,
  `titolo` varchar(255) DEFAULT NULL,
  `descrizione` text,
  `data_evento` datetime DEFAULT NULL,
  `luogo` varchar(255) DEFAULT NULL,
  `immagine` varchar(255) DEFAULT NULL,
  `comune_id` int(11) DEFAULT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `data_creazione` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `testo` text,
  `data` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche_amici`
--

CREATE TABLE `notifiche_amici` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `da_utente_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `messaggio` varchar(255) NOT NULL,
  `letta` tinyint(1) NOT NULL DEFAULT '0',
  `data_creazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `password_reset_attempts`
--

CREATE TABLE `password_reset_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attempts` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `password_reset_attempts`
--

INSERT INTO `password_reset_attempts` (`id`, `email`, `attempt_time`, `attempts`) VALUES
(1, 'grasso.tiziano@gmail.com', '2025-07-29 18:07:58', 3),
(2, 'grasso.tiziano.bet@gmail.com', '2025-07-29 18:25:53', 3),
(3, 'giorgia.lonati.91@gmail.com', '2025-07-29 19:05:12', 3),
(4, 'evolwood@gmail.com', '2025-07-29 19:23:09', 2),
(5, 'andre90messana@gmail.com', '2025-07-29 20:31:14', 1),
(6, 'marottafrancesco31@gmail.com', '2025-07-30 21:23:21', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `comune_id` int(11) NOT NULL,
  `tipo` enum('proposta','segnalazione') NOT NULL,
  `contenuto` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `nome_utente` varchar(255) NOT NULL,
  `cognome_utente` varchar(255) NOT NULL,
  `image_path_post` varchar(255) DEFAULT NULL,
  `likes` int(11) DEFAULT '0',
  `dislikes` int(11) DEFAULT '0',
  `commenti` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `post`
--

INSERT INTO `post` (`id`, `utente_id`, `comune_id`, `tipo`, `contenuto`, `created_at`, `nome_utente`, `cognome_utente`, `image_path_post`, `likes`, `dislikes`, `commenti`) VALUES
(271, 25, 1, 'proposta', 'Ci vorrebbe un cesto in area cani', '2025-08-12 20:45:08', 'Diego', 'Cattaneo', NULL, 2, 1, 1),
(272, 26, 1, 'segnalazione', 'C\'è una buca molto grande all\'inizio di via clerici', '2025-08-12 20:59:09', 'Tiziano', 'Grasso', 'assets/img/posts/689b8efd88c1d.jpg', 3, 0, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `post_sponsorizzati`
--

CREATE TABLE `post_sponsorizzati` (
  `id` int(11) NOT NULL,
  `comune_id` int(11) DEFAULT NULL,
  `contenuto` text,
  `scadenza` datetime DEFAULT NULL,
  `target_area` varchar(255) DEFAULT 'comune',
  `utente_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `reazioni`
--

CREATE TABLE `reazioni` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `tipo` enum('like','love','haha','wow','sad','angry') DEFAULT NULL,
  `data_reazione` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `reazioni_post`
--

CREATE TABLE `reazioni_post` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `tipo` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `reazioni_post`
--

INSERT INTO `reazioni_post` (`id`, `post_id`, `utente_id`, `tipo`, `created_at`) VALUES
(50, 266, 26, 'like', '2025-08-12 18:32:55'),
(51, 267, 26, 'dislike', '2025-08-12 18:36:56'),
(52, 270, 25, 'dislike', '2025-08-12 18:41:31'),
(54, 271, 25, 'like', '2025-08-12 18:45:12'),
(57, 272, 25, 'like', '2025-08-12 19:00:13'),
(58, 272, 27, 'like', '2025-08-12 19:15:52'),
(60, 271, 27, 'like', '2025-08-12 19:17:23'),
(62, 276, 34, 'like', '2025-08-14 11:52:30'),
(64, 271, 26, 'dislike', '2025-08-14 19:29:27'),
(65, 272, 26, 'like', '2025-08-14 20:36:17');

-- --------------------------------------------------------

--
-- Struttura della tabella `segnalazioni`
--

CREATE TABLE `segnalazioni` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT 'Segnalazione utente',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `segnalazioni_commenti`
--

CREATE TABLE `segnalazioni_commenti` (
  `id` int(11) NOT NULL,
  `commento_id` int(11) NOT NULL,
  `utente_segnalante_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struttura della tabella `sondaggi`
--

CREATE TABLE `sondaggi` (
  `id` int(11) NOT NULL,
  `domanda` text,
  `opzione1` varchar(255) DEFAULT NULL,
  `opzione2` varchar(255) DEFAULT NULL,
  `opzione3` varchar(255) DEFAULT NULL,
  `opzione4` varchar(255) DEFAULT NULL,
  `comune_id` int(11) DEFAULT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `data_creazione` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ruolo` enum('cittadino','pa') DEFAULT NULL,
  `comune_id` int(11) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `tipo_login` enum('email','google','facebook') DEFAULT 'email',
  `data_registrazione` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `foto_profilo` varchar(255) DEFAULT NULL,
  `livello` int(11) DEFAULT '1',
  `punti` int(11) DEFAULT '0',
  `bio` text,
  `social` varchar(255) DEFAULT NULL,
  `copertina` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `cognome` varchar(255) NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `professione` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `nome`, `email`, `password`, `ruolo`, `comune_id`, `lat`, `lng`, `tipo_login`, `data_registrazione`, `foto_profilo`, `livello`, `punti`, `bio`, `social`, `copertina`, `reset_token`, `reset_expires`, `cognome`, `data_nascita`, `professione`) VALUES
(25, 'Diego', 'grasso.tiziano@gmail.com', '$2y$10$h1ZW8VLleLYnUxyba026Fur6Ac2RlN15GkwHjin2Ist1m/ns2CADO', 'cittadino', 1, NULL, NULL, 'email', '2025-07-29 15:31:54', '2b931f38-d7f4-4c5e-bbeb-ab5142ffccbd.jpeg', 1, 0, NULL, NULL, NULL, 'c479ef997772061eae6faa4730312994b12e321d544a7b0557c8d947c33711b2', '2025-07-29 21:07:58', 'Cattaneo', '1992-12-05', 'Elettricista'),
(26, 'Tiziano', 'appoggio@noticity.it', '$2y$10$nG9Bfor9hPHszRjm5d.fI..tHya9iBdWTApGPt7sSmw4K6soizGmK', 'cittadino', 1, NULL, NULL, 'email', '2025-07-29 16:13:00', '5261cdba-80e9-4521-b0cf-95f7d5b01699.jfif', 1, 0, NULL, NULL, NULL, NULL, NULL, 'Grasso', '1992-12-05', 'Bagnino'),
(27, 'Gaia', 'gaia.terragni@gmail.com', '$2y$10$PCulU6WwoiR..cK26Er9D.k0dYpuF/P8H6xgMUJ3Vo27GRtG5VDA.', 'cittadino', 1, NULL, NULL, 'email', '2025-07-29 16:55:33', 'fototessera.jpg', 1, 0, NULL, NULL, NULL, NULL, NULL, 'Terragni', NULL, ''),
(31, 'Andrea', 'andre90messana@gmail.com', '$2y$10$0QMedCyLybJbRFrYg33y8ePGr9.dMWMi8600mogF8WYUvtzw8rJ0m', 'cittadino', 1, NULL, NULL, 'email', '2025-07-29 20:29:52', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, 'Messana', NULL, NULL),
(32, 'Francesco', 'marottafrancesco31@gmail.com', '$2y$10$k6fEWDFNrZ69/qcf2B54l.GjKkMqbph5Kl1rWA5/1Jb3KrKf88JQW', 'cittadino', 1, NULL, NULL, 'email', '2025-07-30 21:18:52', 'IMG-20240829-WA0019.jpg', 1, 0, NULL, NULL, NULL, 'a13d7e63a684b377f15fab40438d98604f7474ccb5a17c89582d5c9b1b494373', '2025-07-31 00:23:21', 'Marotta', '2001-12-31', 'Pubblico impiegato'),
(33, 'Andrea', 'andreavenegoni2@gmail.com', '$2y$10$gTNNpQcgiWyr5E/vL2fCrugmdrgdBvN2EsucxgAkEITwHmRjADhGC', 'cittadino', 1, NULL, NULL, 'email', '2025-08-01 23:20:44', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, 'Venegoni', NULL, NULL),
(35, 'Tiziano', 'grasso.tiziano.bet@gmail.com', '$2y$10$88tDYoAxObmJEXs1erB2OO7SrL/rQRuErSyJ.ykPCqeQjPZJf.w0C', 'cittadino', 7, '40.68036010', '14.75945420', 'email', '2025-08-14 12:10:36', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, 'Grasso', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `voti_sondaggi`
--

CREATE TABLE `voti_sondaggi` (
  `id` int(11) NOT NULL,
  `sondaggio_id` int(11) DEFAULT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `scelta` int(11) DEFAULT NULL,
  `data_voto` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `amicizie`
--
ALTER TABLE `amicizie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `richiesta_unica` (`utente_richiedente_id`,`utente_destinatario_id`),
  ADD UNIQUE KEY `uq_coppia` (`utente_richiedente_id`,`utente_destinatario_id`),
  ADD KEY `utente_destinatario_id` (`utente_destinatario_id`),
  ADD KEY `idx_dest_stato` (`utente_destinatario_id`,`stato`),
  ADD KEY `idx_src_stato` (`utente_richiedente_id`,`stato`);

--
-- Indici per le tabelle `annunci`
--
ALTER TABLE `annunci`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comune_id` (`comune_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `attivita`
--
ALTER TABLE `attivita`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `commenti`
--
ALTER TABLE `commenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `commenti_reazioni`
--
ALTER TABLE `commenti_reazioni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`commento_id`,`utente_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `comuni`
--
ALTER TABLE `comuni`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `dirette`
--
ALTER TABLE `dirette`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `eventi`
--
ALTER TABLE `eventi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comune_id` (`comune_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `notifiche_amici`
--
ALTER TABLE `notifiche_amici`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `notifiche_amici_ibfk_2` (`da_utente_id`);

--
-- Indici per le tabelle `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`),
  ADD KEY `comune_id` (`comune_id`);

--
-- Indici per le tabelle `post_sponsorizzati`
--
ALTER TABLE `post_sponsorizzati`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comune_id` (`comune_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `reazioni`
--
ALTER TABLE `reazioni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reazione` (`utente_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indici per le tabelle `reazioni_post`
--
ALTER TABLE `reazioni_post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unico_reazione` (`post_id`,`utente_id`);

--
-- Indici per le tabelle `segnalazioni`
--
ALTER TABLE `segnalazioni`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `segnalazioni_commenti`
--
ALTER TABLE `segnalazioni_commenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_report` (`commento_id`,`utente_segnalante_id`),
  ADD KEY `utente_segnalante_id` (`utente_segnalante_id`);

--
-- Indici per le tabelle `sondaggi`
--
ALTER TABLE `sondaggi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comune_id` (`comune_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `comune_id` (`comune_id`);

--
-- Indici per le tabelle `voti_sondaggi`
--
ALTER TABLE `voti_sondaggi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`sondaggio_id`,`utente_id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `amicizie`
--
ALTER TABLE `amicizie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `annunci`
--
ALTER TABLE `annunci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `attivita`
--
ALTER TABLE `attivita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `commenti`
--
ALTER TABLE `commenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT per la tabella `commenti_reazioni`
--
ALTER TABLE `commenti_reazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT per la tabella `comuni`
--
ALTER TABLE `comuni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT per la tabella `dirette`
--
ALTER TABLE `dirette`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `eventi`
--
ALTER TABLE `eventi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche_amici`
--
ALTER TABLE `notifiche_amici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `password_reset_attempts`
--
ALTER TABLE `password_reset_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT per la tabella `post_sponsorizzati`
--
ALTER TABLE `post_sponsorizzati`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `reazioni`
--
ALTER TABLE `reazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `reazioni_post`
--
ALTER TABLE `reazioni_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT per la tabella `segnalazioni`
--
ALTER TABLE `segnalazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `segnalazioni_commenti`
--
ALTER TABLE `segnalazioni_commenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `sondaggi`
--
ALTER TABLE `sondaggi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT per la tabella `voti_sondaggi`
--
ALTER TABLE `voti_sondaggi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `amicizie`
--
ALTER TABLE `amicizie`
  ADD CONSTRAINT `amicizie_ibfk_1` FOREIGN KEY (`utente_richiedente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `amicizie_ibfk_2` FOREIGN KEY (`utente_destinatario_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `annunci`
--
ALTER TABLE `annunci`
  ADD CONSTRAINT `annunci_ibfk_1` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`),
  ADD CONSTRAINT `annunci_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `commenti`
--
ALTER TABLE `commenti`
  ADD CONSTRAINT `commenti_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`),
  ADD CONSTRAINT `commenti_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `commenti_reazioni`
--
ALTER TABLE `commenti_reazioni`
  ADD CONSTRAINT `commenti_reazioni_ibfk_1` FOREIGN KEY (`commento_id`) REFERENCES `commenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commenti_reazioni_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `eventi`
--
ALTER TABLE `eventi`
  ADD CONSTRAINT `eventi_ibfk_1` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`),
  ADD CONSTRAINT `eventi_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  ADD CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `notifiche_amici`
--
ALTER TABLE `notifiche_amici`
  ADD CONSTRAINT `notifiche_amici_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifiche_amici_ibfk_2` FOREIGN KEY (`da_utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_ibfk_2` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `post_sponsorizzati`
--
ALTER TABLE `post_sponsorizzati`
  ADD CONSTRAINT `post_sponsorizzati_ibfk_1` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`),
  ADD CONSTRAINT `post_sponsorizzati_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `reazioni`
--
ALTER TABLE `reazioni`
  ADD CONSTRAINT `reazioni_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`),
  ADD CONSTRAINT `reazioni_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`);

--
-- Limiti per la tabella `segnalazioni_commenti`
--
ALTER TABLE `segnalazioni_commenti`
  ADD CONSTRAINT `segnalazioni_commenti_ibfk_1` FOREIGN KEY (`commento_id`) REFERENCES `commenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `segnalazioni_commenti_ibfk_2` FOREIGN KEY (`utente_segnalante_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `sondaggi`
--
ALTER TABLE `sondaggi`
  ADD CONSTRAINT `sondaggi_ibfk_1` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`),
  ADD CONSTRAINT `sondaggi_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);

--
-- Limiti per la tabella `utenti`
--
ALTER TABLE `utenti`
  ADD CONSTRAINT `utenti_ibfk_1` FOREIGN KEY (`comune_id`) REFERENCES `comuni` (`id`);

--
-- Limiti per la tabella `voti_sondaggi`
--
ALTER TABLE `voti_sondaggi`
  ADD CONSTRAINT `voti_sondaggi_ibfk_1` FOREIGN KEY (`sondaggio_id`) REFERENCES `sondaggi` (`id`),
  ADD CONSTRAINT `voti_sondaggi_ibfk_2` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
