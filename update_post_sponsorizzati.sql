USE noticity;

ALTER TABLE utenti ADD COLUMN ruolo ENUM('cittadino', 'pa', 'commerciale') DEFAULT 'cittadino';

ALTER TABLE post_sponsorizzati ADD COLUMN target_area VARCHAR(255) DEFAULT 'comune';