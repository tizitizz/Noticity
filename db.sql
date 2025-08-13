CREATE DATABASE IF NOT EXISTS noticity CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE noticity;

CREATE TABLE comuni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    provincia VARCHAR(255),
    regione VARCHAR(255),
    cap VARCHAR(10),
    abitanti INT,
    numeri_utili TEXT
);

CREATE TABLE utenti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    ruolo ENUM('cittadino', 'pa'),
    comune_id INT,
    tipo_login ENUM('email', 'google', 'facebook') DEFAULT 'email',
    data_registrazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comune_id) REFERENCES comuni(id)
);