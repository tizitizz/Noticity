

CREATE TABLE comuni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    provincia VARCHAR(255),
    regione VARCHAR(255),
    cap VARCHAR(10),
    abitanti INT,
    numeri_utili TEXT
);

CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    ruolo ENUM('cittadino', 'pa'),
    comune_id INT,
    tipo_login ENUM('email', 'google', 'facebook') DEFAULT 'email',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comune_id) REFERENCES comuni(id)
);

-- post.sql --

CREATE TABLE post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT,
    comune_id INT,
    tipo ENUM('proposta', 'segnalazione'),
    contenuto TEXT,
    data_creazione DATETIME,
    FOREIGN KEY (utente_id) REFERENCES utenti(id),
    FOREIGN KEY (comune_id) REFERENCES comuni(id)
);

CREATE TABLE post_sponsorizzati (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comune_id INT,
    contenuto TEXT,
    scadenza DATETIME,
    FOREIGN KEY (comune_id) REFERENCES comuni(id)
);

-- attivita.sql --

CREATE TABLE attivita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    categoria VARCHAR(100),
    comune VARCHAR(255),
    descrizione TEXT,
    telefono VARCHAR(20),
    whatsapp VARCHAR(20),
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE utenti ADD COLUMN foto_profilo VARCHAR(255) DEFAULT NULL;

-- notifiche.sql --

CREATE TABLE notifiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT,
    testo TEXT,
    data DATETIME,
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

-- reazioni.sql --

CREATE TABLE reazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT,
    post_id INT,
    tipo ENUM('like', 'love', 'haha', 'wow', 'sad', 'angry'),
    data_reazione DATETIME,
    UNIQUE KEY unique_reazione (utente_id, post_id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id),
    FOREIGN KEY (post_id) REFERENCES post(id)
);

ALTER TABLE utenti ADD COLUMN livello INT DEFAULT 1,
ADD COLUMN punti INT DEFAULT 0;

-- aggiorna_profilo.sql --

ALTER TABLE utenti
ADD COLUMN bio TEXT DEFAULT NULL,
ADD COLUMN social VARCHAR(255) DEFAULT NULL,
ADD COLUMN copertina VARCHAR(255) DEFAULT NULL;

-- dirette.sql --

CREATE TABLE dirette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255),
    descrizione TEXT,
    embed_url TEXT,
    data_creazione DATETIME
);

-- update_post_sponsorizzati.sql --


ALTER TABLE post_sponsorizzati ADD COLUMN target_area VARCHAR(255) DEFAULT 'comune';

-- aggiungi_utente_id.sql --

ALTER TABLE post_sponsorizzati ADD COLUMN utente_id INT DEFAULT NULL,
ADD FOREIGN KEY (utente_id) REFERENCES utenti(id);

-- eventi.sql --

CREATE TABLE eventi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255),
    descrizione TEXT,
    data_evento DATETIME,
    luogo VARCHAR(255),
    immagine VARCHAR(255),
    comune_id INT,
    utente_id INT,
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comune_id) REFERENCES comuni(id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

-- sondaggi.sql --

CREATE TABLE sondaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domanda TEXT,
    opzione1 VARCHAR(255),
    opzione2 VARCHAR(255),
    opzione3 VARCHAR(255),
    opzione4 VARCHAR(255),
    comune_id INT,
    utente_id INT,
    data_creazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comune_id) REFERENCES comuni(id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

CREATE TABLE voti_sondaggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sondaggio_id INT,
    utente_id INT,
    scelta INT,
    data_voto DATETIME,
    UNIQUE KEY unique_vote (sondaggio_id, utente_id),
    FOREIGN KEY (sondaggio_id) REFERENCES sondaggi(id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

-- annunci.sql --

CREATE TABLE annunci (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255),
    descrizione TEXT,
    comune_id INT,
    utente_id INT,
    data_pubblicazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comune_id) REFERENCES comuni(id),
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);

