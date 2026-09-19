-- ============================================================
-- Mise à jour d'une base service_boutique DÉJÀ importée : ajoute les FAVORIS
-- (après mise_a_jour_panier.sql et mise_a_jour_commande.sql)
-- Les données existantes sont conservées.
-- ============================================================
USE `service_boutique`;

-- ------------------------------------------------------------
-- Table `favori` : les FAVORIS de chaque acheteur (étoile ★ sur un produit)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `favori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_produit` int NOT NULL,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_produit` (`id_user`, `id_produit`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `fk_favori_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_favori_produit`
    FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
