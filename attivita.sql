USE noticity;

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