<?php
// COMMANDES : bouton "commander" du panier (acheteur) et
// "Commandes reçues" du tableau de bord vendeur (vue3.php / suit_vue3.php)
require_once __DIR__ . "/panier.php";

$db = connection_db_bou();

// statuts possibles et leur texte à l'écran
const STATUTS_COMMANDE = [
    "en_attente" => "en attente",
    "acceptee"   => "acceptée",
    "livree"     => "livrée",
    "annulee"    => "annulée",
];

// ce que le vendeur peut faire selon le statut actuel
const SUITE_COMMANDE = [
    "en_attente" => ["acceptee", "annulee"],
    "acceptee"   => ["livree", "annulee"],
    "livree"     => [],
    "annulee"    => [],
];


// Transformer le panier en commande(s) : une par boutique.
// Le stock diminue ici (pas au clic sur "buy"), dans une transaction :
// si un produit n'a plus assez de stock, rien n'est enregistré.
function passer_commande(int $id_user, string $adresse, string $telephone): array
{
    global $db;

    $adresse = trim($adresse);
    $telephone = trim($telephone);

    if ($adresse === "" || $telephone === "") {
        return ["success" => false, "message" => "Indiquez l'adresse de livraison et le téléphone"];
    }

    $panier = get_panier($id_user);

    if (empty($panier)) {
        return ["success" => false, "message" => "Votre panier est vide"];
    }

    $db->beginTransaction();

    try {
        // bloquer les produits le temps de la commande (deux acheteurs en même temps)
        $verif = $db->prepare("SELECT stock, statut_validation FROM produits WHERE id = ? FOR UPDATE");

        foreach ($panier as $l) {
            $verif->execute([$l["id_produit"]]);
            $produit = $verif->fetch(PDO::FETCH_ASSOC);
            $stock = (int) $produit["stock"];

            // un produit retiré par l'administrateur ne peut plus être commandé
            if ($produit["statut_validation"] !== "approuve") {
                $db->rollBack();
                return [
                    "success" => false,
                    "message" => "« " . $l["nom"] . " » n'est plus en vente"
                ];
            }

            if ($l["quantite"] > $stock) {
                $db->rollBack();
                return [
                    "success" => false,
                    "message" => "Stock insuffisant pour « " . $l["nom"] . " » (il en reste " . $stock . ")"
                ];
            }
        }

        // regrouper les lignes du panier par boutique
        $par_boutique = [];
        foreach ($panier as $l) {
            $par_boutique[$l["id_boutique"]][] = $l;
        }

        $ajout_commande = $db->prepare("
            INSERT INTO commande (id_user, id_boutique, adresse_livraison, telephone, total)
            VALUES (?, ?, ?, ?, ?)
        ");
        $ajout_ligne = $db->prepare("
            INSERT INTO commande_ligne (id_commande, id_produit, nom_produit, prix_unitaire, quantite)
            VALUES (?, ?, ?, ?, ?)
        ");
        $baisse_stock = $db->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");

        foreach ($par_boutique as $id_boutique => $lignes) {
            $total = array_sum(array_column($lignes, "sous_total"));

            $ajout_commande->execute([$id_user, $id_boutique, $adresse, $telephone, $total]);
            $id_commande = $db->lastInsertId();

            foreach ($lignes as $l) {
                $ajout_ligne->execute([$id_commande, $l["id_produit"], $l["nom"], $l["prix_unitaire"], $l["quantite"]]);
                $baisse_stock->execute([$l["quantite"], $l["id_produit"]]);
            }
        }

        // le panier est vidé
        $db->prepare("DELETE FROM panier WHERE id_user = ?")->execute([$id_user]);

        $db->commit();

    } catch (Exception $erreur) {
        $db->rollBack();
        return ["success" => false, "message" => "Erreur pendant la commande : " . $erreur->getMessage()];
    }

    $nb = count($par_boutique);

    return [
        "success" => true,
        "message" => $nb > 1
            ? "Commande envoyée à $nb boutiques"
            : "Commande envoyée à la boutique"
    ];
}


// Lignes (produits) d'une liste de commandes, rangées par id de commande
function lignes_des_commandes(array $commandes): array
{
    global $db;

    if (empty($commandes)) {
        return $commandes;
    }

    $ids = array_column($commandes, "id");
    $marques = implode(",", array_fill(0, count($ids), "?"));

    $req = $db->prepare("SELECT * FROM commande_ligne WHERE id_commande IN ($marques) ORDER BY id");
    $req->execute($ids);

    $lignes = [];
    foreach ($req->fetchAll(PDO::FETCH_ASSOC) as $l) {
        $lignes[$l["id_commande"]][] = $l;
    }

    foreach ($commandes as &$c) {
        $c["lignes"] = $lignes[$c["id"]] ?? [];
    }

    return $commandes;
}


// Commandes d'un acheteur ("Mes commandes" dans panier.php)
function get_commandes_acheteur(int $id_user): array
{
    global $db;

    $req = $db->prepare("
        SELECT c.*, b.boutname
        FROM commande c
        JOIN inscription_vendeur b ON b.id = c.id_boutique
        WHERE c.id_user = ?
        ORDER BY c.date_commande DESC, c.id DESC
    ");
    $req->execute([$id_user]);

    return lignes_des_commandes($req->fetchAll(PDO::FETCH_ASSOC));
}


// Commandes reçues par une boutique (tableau de bord vendeur)
function get_commandes_boutique(int $id_boutique): array
{
    global $db;

    $req = $db->prepare("
        SELECT c.*, u.nom AS client, u.photo AS photo_client
        FROM commande c
        JOIN users u ON u.id = c.id_user
        WHERE c.id_boutique = ?
        ORDER BY (c.statut = 'en_attente') DESC, c.date_commande DESC, c.id DESC
    ");
    $req->execute([$id_boutique]);

    return lignes_des_commandes($req->fetchAll(PDO::FETCH_ASSOC));
}


// Remettre en stock les produits d'une commande annulée
function rendre_stock(int $id_commande): void
{
    global $db;

    $req = $db->prepare("
        UPDATE produits p
        JOIN commande_ligne l ON l.id_produit = p.id
        SET p.stock = p.stock + l.quantite
        WHERE l.id_commande = ?
    ");
    $req->execute([$id_commande]);
}


// Vendeur : accepter / livrer / annuler une commande de SA boutique
function changer_statut_commande(int $id_commande, int $id_boutique, string $statut): bool
{
    global $db;

    $req = $db->prepare("SELECT statut FROM commande WHERE id = ? AND id_boutique = ?");
    $req->execute([$id_commande, $id_boutique]);
    $actuel = $req->fetchColumn();

    if ($actuel === false || !in_array($statut, SUITE_COMMANDE[$actuel] ?? [], true)) {
        return false;
    }

    $db->beginTransaction();

    $db->prepare("UPDATE commande SET statut = ? WHERE id = ?")->execute([$statut, $id_commande]);

    if ($statut === "annulee") {
        rendre_stock($id_commande);
    }

    $db->commit();

    return true;
}


// Acheteur : annuler sa commande tant que la boutique ne l'a pas acceptée
function annuler_commande_acheteur(int $id_commande, int $id_user): bool
{
    global $db;

    $db->beginTransaction();

    $req = $db->prepare("
        UPDATE commande SET statut = 'annulee'
        WHERE id = ? AND id_user = ? AND statut = 'en_attente'
    ");
    $req->execute([$id_commande, $id_user]);

    if ($req->rowCount() === 0) {
        $db->rollBack();
        return false;
    }

    rendre_stock($id_commande);
    $db->commit();

    return true;
}
?>