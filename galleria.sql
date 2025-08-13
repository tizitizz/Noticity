USE noticity;

CREATE TABLE galleria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    attivita_id INT,
    immagine VARCHAR(255),
    FOREIGN KEY (attivita_id) REFERENCES attivita(id)
);