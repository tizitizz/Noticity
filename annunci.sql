USE noticity;

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