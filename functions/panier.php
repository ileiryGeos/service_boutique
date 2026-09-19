<?php
// PANIER de l'acheteur connecté (bouton "buy" de suit_vue2.php, page panier.php)
require_once __DIR__ . "/db.php";

$db = connection_db_bou();

// au-delà de 5 pièces, le prix de gros s'applique ("Le prix de plus de 5p est ...")
const QUANTITE_PRIX_GROS = 5;


// Ajouter un produit au panier (ou +1 s'il y est déjà), sans dépasser le stock
function ajouter_panier(int $id_user, int $id_produit): array
{
    global $db;

    $req = $db->prepare("SELECT nom, stock FROM produits WHERE id = ?");
    $req->execute([$id_produit]);
    $produit = $req->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        return ["success" => false, "message" => "Produit introuvable"];
    }

    $req = $db->prepare("SELECT quantite FROM panier WHERE id_user = ? AND id_produit = ?");
    $req->execute([$id_user, $id_produit]);
    $deja = (int) $req->fetchColumn();

    if ($deja + 1 > (int) $produit["stock"]) {
        return ["success" => false, "message" => "Stock insuffisant pour « " . $produit["nom"] . " »"];
    }

    $req = $db->prepare("
        INSERT INTO panier (id_user, id_produit, quantite) VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE quantite = quantite + 1
    ");
    $req->execute([$id_user, $id_produit]);

    return ["success" => true, "message" => "« " . $produit["nom"] . " » ajouté au panier"];
}


// Changer la quantité (boutons - / + de panier.php) ; 0 = retirer
function changer_quantite(int $id_user, int $id_produit, int $quantite): void
{
    global $db;

    if ($quantite <= 0) {
        retirer_panier($id_user, $id_produit);
        return;
    }

    $req = $db->prepare("
        UPDATE panier p JOIN produits pr ON pr.id = p.id_produit
        SET p.quantite = LEAST(?, pr.stock)
        WHERE p.id_user = ? AND p.id_produit = ?
    ");
    $req->execute([$quantite, $id_user, $id_produit]);
}


function retirer_panier(int $id_user, int $id_produit): void
{
    global $db;

    $req = $db->prepare("DELETE FROM panier WHERE id_user = ? AND id_produit = ?");
    $req->execute([$id_user, $id_produit]);
}


// Contenu du panier avec le prix unitaire appliqué et le sous-total
function get_panier(int $id_user): array
{
    global $db;

    $req = $db->prepare("
        SELECT p.id_produit, p.quantite,
               pr.nom, pr.photo, pr.type, pr.stock, pr.prix, pr.prix_gros,
               b.id AS id_boutique, b.boutname
        FROM panier p
        JOIN produits pr ON pr.id = p.id_produit
        JOIN inscription_vendeur b ON b.id = pr.id_boutique
        WHERE p.id_user = ?
        ORDER BY p.date_ajout DESC
    ");
    $req->execute([$id_user]);
    $lignes = $req->fetchAll(PDO::FETCH_ASSOC);

    foreach ($lignes as &$l) {
        $l["prix_unitaire"] = $l["quantite"] > QUANTITE_PRIX_GROS ? (float) $l["prix_gros"] : (float) $l["prix"];
        $l["sous_total"] = $l["prix_unitaire"] * $l["quantite"];
    }

    return $lignes;
}


// Nombre d'articles (affiché dans le lien "panier" du menu)
function nombre_articles_panier(int $id_user): int
{
    global $db;

    $req = $db->prepare("SELECT COALESCE(SUM(quantite), 0) FROM panier WHERE id_user = ?");
    $req->execute([$id_user]);

    return (int) $req->fetchColumn();
}
?>