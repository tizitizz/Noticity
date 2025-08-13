USE noticity;

CREATE TABLE dirette (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255),
    descrizione TEXT,
    embed_url TEXT,
    data_creazione DATETIME
);