-- ============================================================
-- Mise à jour d'une base service_boutique DÉJÀ importée : ADMINISTRATEUR
--   - produits.statut_validation : validation des produits par l'admin
--   - table admin      : comptes administrateurs
--   - table parametre  : montant des frais de mise en vente
--   - table frais      : frais facturés aux boutiques
-- Les produits déjà en ligne restent visibles (ils passent en "approuve").
-- Aucun frais n'est créé pour les produits existants.
-- ============================================================
USE `service_boutique`;

ALTER TABLE `produits`
  ADD COLUMN `statut_validation` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente' AFTER `date_modification`,
  ADD COLUMN `date_validation` datetime DEFAULT NULL AFTER `statut_validation`;

-- les produits déjà en ligne avant la validation restent visibles
UPDATE `produits` SET `statut_validation` = 'approuve', `date_validation` = `date_creation`;

-- ------------------------------------------------------------
-- Table `admin` : les ADMINISTRATEURS du site
--   ils valident les produits, gèrent les comptes et les frais
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- compte administrateur de démonstration : admin@demo.mg / 1234
INSERT INTO `admin` (`id`, `nom`, `email`, `mot_de_passe`) VALUES
(1, 'administrateur', 'admin@demo.mg', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C');

-- ------------------------------------------------------------
-- Table `parametre` : réglages du site modifiables par l'administrateur
--   frais_mise_en_vente = somme facturée à la boutique pour chaque
--   produit validé (les acheteurs ne paient aucun frais)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `parametre` (
  `cle` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `valeur` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`cle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `parametre` (`cle`, `valeur`) VALUES
('frais_mise_en_vente', '2000');

-- ------------------------------------------------------------
-- Table `frais` : FRAIS DE MISE EN VENTE facturés aux boutiques
--   une ligne est créée quand l'administrateur valide un produit
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `frais` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_boutique` int NOT NULL,
  `id_produit` int DEFAULT NULL,
  `nom_produit` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `paye` tinyint(1) NOT NULL DEFAULT 0,
  `date_frais` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_boutique` (`id_boutique`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `fk_frais_boutique`
    FOREIGN KEY (`id_boutique`) REFERENCES `inscription_vendeur` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_frais_produit`
    FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
