USE noticity;

ALTER TABLE post_sponsorizzati ADD COLUMN utente_id INT DEFAULT NULL,
ADD FOREIGN KEY (utente_id) REFERENCES utenti(id);