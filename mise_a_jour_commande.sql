-- ============================================================
-- Mise à jour d'une base service_boutique DÉJÀ importée
-- (après mise_a_jour_panier.sql) : ajoute les COMMANDES
--   - table commande       : commandes envoyées aux boutiques
--   - table commande_ligne : produits de chaque commande
-- Les données existantes sont conservées.
-- Pour une nouvelle installation, importer seulement service_boutique.sql
-- ============================================================
USE `service_boutique`;

-- ------------------------------------------------------------
-- Table `commande` : une COMMANDE envoyée à une boutique (bouton "commander")
--   un panier avec des produits de plusieurs boutiques donne une commande par boutique
--   statut : en_attente -> acceptee -> livree, ou annulee (le stock est rendu)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `commande` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_boutique` int NOT NULL,
  `adresse_livraison` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `telephone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `statut` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en_attente',
  `date_commande` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`),
  KEY `id_boutique` (`id_boutique`),
  CONSTRAINT `fk_commande_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_commande_boutique`
    FOREIGN KEY (`id_boutique`) REFERENCES `inscription_vendeur` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Table `commande_ligne` : les produits d'une commande
--   nom et prix copiés au moment de la commande (l'historique ne change pas
--   si le vendeur modifie ou supprime le produit ensuite)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `commande_ligne` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_commande` int NOT NULL,
  `id_produit` int DEFAULT NULL,
  `nom_produit` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `quantite` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commande` (`id_commande`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `fk_ligne_commande`
    FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_ligne_produit`
    FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
