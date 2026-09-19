-- ============================================================
-- Mise à jour d'une base service_boutique DÉJÀ importée : HISTORIQUE d'achats
-- (après mise_a_jour_commande.sql)
--   commande_ligne.dans_historique : 1 = affiché dans l'historique de l'acheteur,
--                                     0 = retiré par l'acheteur (bouton ×)
-- Les données existantes sont conservées.
-- ============================================================
USE `service_boutique`;

ALTER TABLE `commande_ligne`
  ADD COLUMN `dans_historique` tinyint(1) NOT NULL DEFAULT 1 AFTER `quantite`;
