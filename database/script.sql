-- CREATE DATABASE IF NOT EXISTS `chambres_hotes_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `chambres_hotes_db`;
DROP TABLE IF EXISTS `reservation`;
DROP TABLE IF EXISTS `client`;


-- Création de la table client
CREATE TABLE client (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    avis TEXT
);

-- Création de la table réservation
CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    valide BOOLEAN NOT NULL DEFAULT FALSE,
    plateforme ENUM('A', 'B') NOT NULL,
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE CASCADE
);