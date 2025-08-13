USE noticity;

CREATE TABLE notifiche (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT,
    testo TEXT,
    data DATETIME,
    FOREIGN KEY (utente_id) REFERENCES utenti(id)
);