-- ============================================================
--  Service Boutique - version pour un HÉBERGEUR (InfinityFree, etc.)
--  Identique à service_boutique.sql, sans CREATE DATABASE ni USE :
--  la base est déjà créée dans le panneau de l'hébergeur.
--
--  phpMyAdmin de l'hébergeur -> choisir la base -> onglet Importer
--  -> ce fichier -> Exécuter.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;


SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `favori`;
DROP TABLE IF EXISTS `commande_ligne`;
DROP TABLE IF EXISTS `commande`;
DROP TABLE IF EXISTS `panier`;
DROP TABLE IF EXISTS `commentaire`;
DROP TABLE IF EXISTS `produits`;
DROP TABLE IF EXISTS `inscription_vendeur`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- ------------------------------------------------------------
-- Table `users` : les ACHETEURS (Daddy)
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `telephone` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `date_naissance` date NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,   -- nom du fichier dans images/
  `mot_de_pass` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `nom`, `telephone`, `email`, `date_naissance`, `adresse`, `photo`, `mot_de_pass`) VALUES
(1, 'rakoto', '0340000011', 'rakoto@exemple.mg', '2026-08-19', 'tana', '', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C'),
(2, 'MIIA', '0340000012', 'miia@exemple.mg', '2026-08-10', 'antananrivo', '', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C'),
(3, 'alderson', '0340000013', 'alderson@exemple.mg', '2026-08-26', 'tana', '', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C'),
(4, 'bozyy', '0340000014', 'bozyy@exemple.mg', '2026-09-01', 'antananrivo', '', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C'),
-- tous les comptes de départ ont le mot de passe 1234 ; compte de démonstration : acheteur@demo.mg
(5, 'acheteur demo', '0340000001', 'acheteur@demo.mg', '2000-01-01', 'tana', '', '$2y$10$r00MAh6k5yKPP4iYmM/uKObFoUEWu59ea99c5UDcJpaSYOIWh8f4C');

-- ------------------------------------------------------------
-- Table `inscription_vendeur` : les BOUTIQUES / VENDEURS (Zara)
-- ------------------------------------------------------------
CREATE TABLE `inscription_vendeur` (
  `id` int NOT NULL AUTO_INCREMENT,
  `boutname` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `numTel` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `localisation` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `definition` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `inscription_vendeur` (`id`, `boutname`, `logo`, `numTel`, `email`, `password`, `localisation`, `definition`, `date`) VALUES
(25, 'Zara', 'logo 1.png', '0340000025', 'zara@exemple.mg', '$2y$10$Urx7wDN8tAp4s/OgoaQ4zOSKCB2uH1rjQC/6biyiIBok4tiEw9HKi', 'Tsarahonenana', 'BOUTIQUE MILAY', '2026-09-04 13:24:45'),
(26, 'KOTO', 'logo 2.jpg', '0340000026', 'koto@exemple.mg', '$2y$10$Urx7wDN8tAp4s/OgoaQ4zOSKCB2uH1rjQC/6biyiIBok4tiEw9HKi', 'Tsarahonenana', 'kkkkkkk', '2026-09-04 13:40:14'),
-- toutes les boutiques de départ ont le mot de passe 1234 (connexion : nom de la boutique + mot de passe)
(27, 'Demo', 'kara.jpg', '0340000002', 'vendeur@demo.mg', '$2y$10$Urx7wDN8tAp4s/OgoaQ4zOSKCB2uH1rjQC/6biyiIBok4tiEw9HKi', 'Analakely', 'boutique de demonstration', '2026-09-11 08:00:00');

-- ------------------------------------------------------------
-- Table `produits` : les PRODUITS (Steve)
--   + id_boutique : la boutique (inscription_vendeur) qui vend le produit
-- ------------------------------------------------------------
CREATE TABLE `produits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_boutique` int NOT NULL,
  `type` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `stock` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `prix_gros` decimal(10,2) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_boutique` (`id_boutique`),
  CONSTRAINT `fk_produits_boutique`
    FOREIGN KEY (`id_boutique`) REFERENCES `inscription_vendeur` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `produits` (`id`, `id_boutique`, `type`, `nom`, `description`, `photo`, `stock`, `prix`, `prix_gros`, `date_creation`, `date_modification`) VALUES
(6, 25, 'cosmetique', 'labelo', 'fdhj', '530a84242939663d13643c0fd06c3384.jpg', 6, 6000.00, 700.00, '2026-08-17 17:17:27', '2026-08-17 17:17:27'),
(7, 25, 'type', 'prod', 'tsy aiko', 'dd35bbf03073f5f226e47fa4b1e00cc8.jpg', 5, 2000.00, 100.00, '2026-08-17 17:26:20', '2026-08-17 17:26:20'),
(8, 25, 'teste produit', 'produits', 'tsy aiko', 'ddc38e0410a4bc66a99c6f1ebbb13e2a.jpg', 6, 2000.00, 900.00, '2026-08-17 17:27:16', '2026-08-17 17:27:16'),
(9, 26, 'sports', 'ballon', 'ballon mlay', '589526406_1632793001040725_1757453481169695622_n.png', 5, 1000.00, 900.00, '2026-08-17 17:40:24', '2026-08-17 17:40:24'),
(10, 26, 'sports', 'basket', 'hfgjf', '592153484_873934788721774_5929162429058727677_n.png', 6, 2000.00, 900.00, '2026-08-20 09:48:00', '2026-08-20 09:48:00'),
-- produit de la boutique de démonstration
(11, 27, 'akanjo', 'ensemble bleu', 'ensemble veste et pantalon', '6e81b8c8618fc0cd04d0976e0d24b0cb.jpg', 4, 45000.00, 40000.00, '2026-09-11 08:00:00', '2026-09-11 08:00:00');

-- ------------------------------------------------------------
-- Table `commentaire` : les COMMENTAIRES des produits (Hajatiana)
--   id_produit est INT (et non UNSIGNED) pour correspondre à produits.id
--   id_user   : l'acheteur qui a écrit le commentaire (son nom et sa photo
--               viennent de la table users) ; NULL = réponse d'une boutique,
--               dont le nom est alors dans `nom`
-- ------------------------------------------------------------
CREATE TABLE `commentaire` (
  `id_commentaire` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_produit` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nom` varchar(100) COLLATE utf8mb4_general_ci DEFAULT 'anonyme',
  `contenu` text COLLATE utf8mb4_general_ci NOT NULL,
  `date_commentaire` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_commentaire`),
  KEY `id_produit` (`id_produit`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `fk_commentaire_produit`
    FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_commentaire_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `commentaire` (`id_produit`, `id_user`, `nom`, `contenu`) VALUES
(6, 1, 'rakoto', 'Produit de tres bonne qualite, je recommande !'),
(6, 2, 'MIIA', 'Livraison rapide, merci beaucoup.'),
(7, 3, 'alderson', 'Tres beau sac, conforme a la description.'),
(9, 4, 'bozyy', 'La taille est un peu petite, mais tres confortable.');

-- ------------------------------------------------------------
-- Table `panier` : le PANIER de chaque acheteur (bouton "buy")
--   un produit n'apparaît qu'une fois par acheteur : cliquer encore
--   sur "buy" augmente la quantité
-- ------------------------------------------------------------
CREATE TABLE `panier` (
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
  `dans_historique` tinyint(1) NOT NULL DEFAULT 1,   -- 0 = retiré de l'historique par l'acheteur (bouton ×)
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

COMMIT;
