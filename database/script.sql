-- CREATE DATABASE IF NOT EXISTS `chambres_hotes_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `chambres_hotes_db`;
DROP TABLE IF EXISTS `comptabilite`;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `clients`;
DROP TABLE IF EXISTS `chambres`;

CREATE TABLE `chambres` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(50) NOT NULL COMMENT 'Nom ou numéro de la chambre',
    `prix_nuit` DECIMAL(10, 2) NOT NULL COMMENT 'Prix standard par nuit',
    `capacite` INT NOT NULL DEFAULT 2 COMMENT 'Nombre maximum de personnes',
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) UNIQUE NULL,
    `telephone` VARCHAR(20) NULL,
    `notes` TEXT NULL COMMENT 'Infos utiles : allergies, préférences...',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `reservations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `chambre_id` INT NOT NULL,
    `client_id` INT NOT NULL,
    `date_arrivee` DATE NOT NULL,
    `date_depart` DATE NOT NULL,
    `nombre_personnes` INT NOT NULL DEFAULT 1,
    `statut` ENUM('en_attente', 'confirmee', 'annulee') NOT NULL DEFAULT 'en_attente',
    `remarque` TEXT NULL COMMENT 'Demandes particulières du client',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    CONSTRAINT `fk_reservations_chambre` FOREIGN KEY (`chambre_id`) REFERENCES `chambres` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_reservations_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
    -- Sécurité pour éviter que la date de départ soit avant l'arrivée
    CONSTRAINT `chk_dates` CHECK (`date_depart` > `date_arrivee`)
) ENGINE=InnoDB;

CREATE TABLE `comptabilite` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reservation_id` INT NULL COMMENT 'Peut être NULL s''il s''agit d''une dépense ou d''un revenu hors réservation',
    `type_mouvement` ENUM('revenu', 'depense') NOT NULL,
    `montant` DECIMAL(10, 2) NOT NULL,
    `mode_paiement` ENUM('especes', 'carte', 'cheque', 'virement', 'autre') NOT NULL,
    `date_mouvement` DATE NOT NULL COMMENT 'Date réelle de l''encaissement ou du paiement',
    `libelle` VARCHAR(255) NOT NULL COMMENT 'Ex: "Réservation Dupont" ou "Achat draps lit"',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT `fk_compta_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_reservations_dates` ON `reservations` (`date_arrivee`, `date_depart`);
CREATE INDEX `idx_compta_date` ON `comptabilite` (`date_mouvement`);