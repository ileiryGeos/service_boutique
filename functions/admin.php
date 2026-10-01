<?php
// ADMINISTRATEUR : validation des produits, gestion des comptes,
// frais de mise en vente facturés aux boutiques, suivi des ventes.
// Pages : admin_connexion.php (connexion) et admin.php (tableau de bord).
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/commande.php";        // STATUTS_COMMANDE (état des ventes)
require_once __DIR__ . "/statut_produit.php";  // STATUTS_PRODUIT (validation des produits)
require_once __DIR__ . "/../utils/image.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = connection_db_bou();


// ------------------------------------------------------------
// CONNEXION
// ------------------------------------------------------------

function connecter_admin(string $email, string $mot_de_passe): bool
{
    global $db;

    $req = $db->prepare("SELECT * FROM admin WHERE email = ?");
    $req->execute([trim($email)]);
    $admin = $req->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($mot_de_passe, $admin["mot_de_passe"])) {
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_nom"] = $admin["nom"];
        return true;
    }

    return false;
}


// toutes les pages d'administration commencent par ça
function verifier_admin(): void
{
    if (!isset($_SESSION["admin_id"])) {
        header("Location: admin_connexion.php");
        exit;
    }
}


// ------------------------------------------------------------
// RÉGLAGES (montant des frais)
// ------------------------------------------------------------

function get_parametre(string $cle, string $defaut = ""): string
{
    global $db;

    $req = $db->prepare("SELECT valeur FROM parametre WHERE cle = ?");
    $req->execute([$cle]);
    $valeur = $req->fetchColumn();

    return $valeur === false ? $defaut : $valeur;
}


function set_parametre(string $cle, string $valeur): void
{
    global $db;

    $req = $db->prepare("
        INSERT INTO parametre (cle, valeur) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)
    ");
    $req->execute([$cle, $valeur]);
}


function frais_mise_en_vente(): float
{
    return (float) get_parametre("frais_mise_en_vente", "0");
}


// ------------------------------------------------------------
// VALIDATION DES PRODUITS
// ------------------------------------------------------------

// Produits d'un état donné, avec leur boutique
function get_produits_par_statut(string $statut): array
{
    global $db;

    $req = $db->prepare("
        SELECT p.*, b.boutname, b.id AS id_boutique
        FROM produits p
        JOIN inscription_vendeur b ON b.id = p.id_boutique
        WHERE p.statut_validation = ?
        ORDER BY p.date_creation DESC, p.id DESC
    ");
    $req->execute([$statut]);

    return $req->fetchAll(PDO::FETCH_ASSOC);
}


// Valider un produit : il devient visible pour les acheteurs
// et les frais de mise en vente sont facturés à la boutique.
function approuver_produit(int $id_produit): array
{
    global $db;

    $req = $db->prepare("SELECT * FROM produits WHERE id = ?");
    $req->execute([$id_produit]);
    $produit = $req->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        return ["success" => false, "message" => "Produit introuvable"];
    }

    if ($produit["statut_validation"] === "approuve") {
        return ["success" => false, "message" => "Ce produit est déjà en ligne"];
    }

    $montant = frais_mise_en_vente();

    $db->beginTransaction();

    $db->prepare("UPDATE produits SET statut_validation = 'approuve', date_validation = NOW() WHERE id = ?")
       ->execute([$id_produit]);

    // frais facturés une seule fois par produit
    $deja = $db->prepare("SELECT id FROM frais WHERE id_produit = ?");
    $deja->execute([$id_produit]);

    if (!$deja->fetch() && $montant > 0) {
        $db->prepare("
            INSERT INTO frais (id_boutique, id_produit, nom_produit, montant)
            VALUES (?, ?, ?, ?)
        ")->execute([$produit["id_boutique"], $id_produit, $produit["nom"], $montant]);
    }

    $db->commit();

    return [
        "success" => true,
        "message" => "« " . $produit["nom"] . " » est en ligne"
            . ($montant > 0 ? " (frais de " . number_format($montant, 0, ',', ' ') . " Ar facturés à la boutique)" : "")
    ];
}


// Refuser un produit : il reste chez le vendeur mais n'est pas visible,
// et aucun frais n'est facturé.
function refuser_produit(int $id_produit): array
{
    global $db;

    $req = $db->prepare("UPDATE produits SET statut_validation = 'refuse', date_validation = NOW() WHERE id = ?");
    $req->execute([$id_produit]);

    return ["success" => true, "message" => "Produit refusé"];
}


// Supprimer définitivement un produit (commentaires, favoris, paniers suivent)
function supprimer_produit_admin(int $id_produit): array
{
    global $db;

    $req = $db->prepare("DELETE FROM produits WHERE id = ?");
    $req->execute([$id_produit]);

    return ["success" => true, "message" => "Produit supprimé"];
}


// ------------------------------------------------------------
// COMPTES : boutiques et acheteurs
// ------------------------------------------------------------

function get_boutiques_admin(): array
{
    global $db;

    return $db->query("
        SELECT b.*,
               (SELECT COUNT(*) FROM produits p WHERE p.id_boutique = b.id) AS nb_produits,
               (SELECT COUNT(*) FROM produits p WHERE p.id_boutique = b.id AND p.statut_validation = 'en_attente') AS nb_attente,
               (SELECT COALESCE(SUM(f.montant), 0) FROM frais f WHERE f.id_boutique = b.id AND f.paye = 0) AS frais_dus,
               (SELECT COALESCE(SUM(c.total), 0) FROM commande c WHERE c.id_boutique = b.id AND c.statut <> 'annulee') AS ventes
        FROM inscription_vendeur b
        ORDER BY b.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}


function get_acheteurs_admin(): array
{
    global $db;

    return $db->query("
        SELECT u.*,
               (SELECT COUNT(*) FROM commande c WHERE c.id_user = u.id AND c.statut <> 'annulee') AS nb_commandes,
               (SELECT COALESCE(SUM(c.total), 0) FROM commande c WHERE c.id_user = u.id AND c.statut <> 'annulee') AS total_achats
        FROM users u
        ORDER BY u.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}


function supprimer_boutique(int $id_boutique): array
{
    global $db;

    $db->prepare("DELETE FROM inscription_vendeur WHERE id = ?")->execute([$id_boutique]);

    return ["success" => true, "message" => "Boutique supprimée (ses produits et commandes aussi)"];
}


function supprimer_acheteur(int $id_user): array
{
    global $db;

    $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id_user]);

    return ["success" => true, "message" => "Compte acheteur supprimé"];
}


// ------------------------------------------------------------
// VENTES ET FRAIS
// ------------------------------------------------------------

// Toutes les ventes du site (toutes les boutiques)
function get_ventes_admin(int $limite = 50): array
{
    global $db;

    return $db->query("
        SELECT c.*, b.boutname, u.nom AS client
        FROM commande c
        JOIN inscription_vendeur b ON b.id = c.id_boutique
        JOIN users u ON u.id = c.id_user
        ORDER BY c.date_commande DESC, c.id DESC
        LIMIT " . $limite
    )->fetchAll(PDO::FETCH_ASSOC);
}


// Mois par mois : ventes des boutiques et frais gagnés par l'administrateur
function get_revenus_par_mois(): array
{
    global $db;

    $mois = [];

    $frais = $db->query("
        SELECT DATE_FORMAT(date_frais, '%Y-%m') AS mois,
               COUNT(*) AS nb_frais,
               COALESCE(SUM(montant), 0) AS total,
               COALESCE(SUM(CASE WHEN paye = 1 THEN montant ELSE 0 END), 0) AS encaisse
        FROM frais
        GROUP BY mois
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($frais as $f) {
        $mois[$f["mois"]] = $f + ["ventes" => 0, "nb_commandes" => 0];
    }

    $ventes = $db->query("
        SELECT DATE_FORMAT(date_commande, '%Y-%m') AS mois,
               COUNT(*) AS nb_commandes,
               COALESCE(SUM(total), 0) AS ventes
        FROM commande
        WHERE statut <> 'annulee'
        GROUP BY mois
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ventes as $v) {
        if (isset($mois[$v["mois"]])) {
            $mois[$v["mois"]]["ventes"] = $v["ventes"];
            $mois[$v["mois"]]["nb_commandes"] = $v["nb_commandes"];
        } else {
            $mois[$v["mois"]] = $v + ["nb_frais" => 0, "total" => 0, "encaisse" => 0];
        }
    }

    krsort($mois);

    return $mois;
}


// Frais d'une boutique ou de toutes les boutiques
function get_frais_admin(int $limite = 50): array
{
    global $db;

    return $db->query("
        SELECT f.*, b.boutname
        FROM frais f
        JOIN inscription_vendeur b ON b.id = f.id_boutique
        ORDER BY f.date_frais DESC, f.id DESC
        LIMIT " . $limite
    )->fetchAll(PDO::FETCH_ASSOC);
}


// Marquer les frais d'une boutique comme payés (ou non payés)
function marquer_frais_payes(int $id_boutique, bool $paye = true): array
{
    global $db;

    $req = $db->prepare("UPDATE frais SET paye = ? WHERE id_boutique = ?");
    $req->execute([$paye ? 1 : 0, $id_boutique]);

    return [
        "success" => true,
        "message" => $paye ? "Frais marqués comme payés" : "Frais remis en attente de paiement"
    ];
}


// Chiffres du haut du tableau de bord
function get_resume_admin(): array
{
    global $db;

    $un = fn($sql) => $db->query($sql)->fetchColumn();

    return [
        "a_valider"      => (int) $un("SELECT COUNT(*) FROM produits WHERE statut_validation = 'en_attente'"),
        "produits"       => (int) $un("SELECT COUNT(*) FROM produits WHERE statut_validation = 'approuve'"),
        "boutiques"      => (int) $un("SELECT COUNT(*) FROM inscription_vendeur"),
        "acheteurs"      => (int) $un("SELECT COUNT(*) FROM users"),
        "ventes_total"   => (float) $un("SELECT COALESCE(SUM(total), 0) FROM commande WHERE statut <> 'annulee'"),
        "frais_total"    => (float) $un("SELECT COALESCE(SUM(montant), 0) FROM frais"),
        "frais_encaisse" => (float) $un("SELECT COALESCE(SUM(montant), 0) FROM frais WHERE paye = 1"),
        "frais_dus"      => (float) $un("SELECT COALESCE(SUM(montant), 0) FROM frais WHERE paye = 0"),
    ];
}
?>