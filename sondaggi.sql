USE noticity;

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