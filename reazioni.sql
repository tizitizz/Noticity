USE noticity;

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