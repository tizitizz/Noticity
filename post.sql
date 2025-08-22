USE noticity;

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