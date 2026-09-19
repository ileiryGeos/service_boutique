-- ============================================================
-- Mise à jour d'une base service_boutique DÉJÀ importée
-- (garde les comptes, boutiques, produits et commentaires existants)
--   - commentaire.id_user : l'acheteur qui a écrit le commentaire
--   - table panier        : le panier des acheteurs (bouton "buy")
-- Pour une nouvelle installation, importer seulement service_boutique.sql
-- ============================================================
USE `service_boutique`;

ALTER TABLE `commentaire`
  ADD COLUMN `id_user` int DEFAULT NULL AFTER `id_produit`,
  ADD KEY `id_user` (`id_user`),
  ADD CONSTRAINT `fk_commentaire_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
    ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `panier` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL DEFAULT 1,
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_produit` (`id_user`, `id_produit`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `fk_panier_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_panier_produit`
    FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- relier les anciens commentaires à leur auteur quand le nom correspond à un acheteur
UPDATE `commentaire` c
  JOIN `users` u ON u.nom = c.nom
SET c.id_user = u.id
WHERE c.id_user IS NULL;
