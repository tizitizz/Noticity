USE noticity;

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