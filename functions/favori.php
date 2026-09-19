<?php
// FAVORIS de l'acheteur connecté : étoile ★ sur les produits (suit_vue2.php)
// et liste "favorite" de la barre de gauche (vue2.php / suit_vue2.php)
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/../utils/image.php";

$db = connection_db_bou();


// Ajouter le produit aux favoris, ou le retirer s'il y est déjà.
// Renvoie true si le produit est maintenant en favori.
function basculer_favori(int $id_user, int $id_produit): bool
{
    global $db;

    $req = $db->prepare("DELETE FROM favori WHERE id_user = ? AND id_produit = ?");
    $req->execute([$id_user, $id_produit]);

    if ($req->rowCount() > 0) {
        return false;   // il y était : retiré
    }

    // n'ajouter que si le produit existe
    $req = $db->prepare("
        INSERT INTO favori (id_user, id_produit)
        SELECT ?, id FROM produits WHERE id = ?
    ");
    $req->execute([$id_user, $id_produit]);

    return $req->rowCount() > 0;
}


// Favoris avec le produit et sa boutique (le plus récent en premier)
function get_favoris(int $id_user): array
{
    global $db;

    $req = $db->prepare("
        SELECT f.id_produit, p.nom, p.photo, p.prix, b.id AS id_boutique, b.boutname
        FROM favori f
        JOIN produits p ON p.id = f.id_produit
        JOIN inscription_vendeur b ON b.id = p.id_boutique
        WHERE f.id_user = ?
        ORDER BY f.date_ajout DESC, f.id DESC
    ");
    $req->execute([$id_user]);

    return $req->fetchAll(PDO::FETCH_ASSOC);
}


// id des produits en favori (pour colorer les étoiles)
function ids_favoris(int $id_user): array
{
    return array_map("intval", array_column(get_favoris($id_user), "id_produit"));
}


// Contenu de la liste "favorite" (utilisé par les pages et par api/favori_api.php)
function afficher_favoris(array $favoris): void
{
    if (empty($favoris)) {
        echo '<p class="fav_vide">Aucun favori pour le moment.<br>Cliquez sur l\'étoile ★ d\'un produit pour l\'ajouter.</p>';
        return;
    }

    foreach ($favoris as $f) {
        $lien = "suit_vue2.php?id=" . (int) $f["id_boutique"];
        ?>
        <div class="boit_fav">
            <a href="<?= $lien ?>"><img src="images/<?= htmlspecialchars(image_ou($f["photo"], "produit.jpg")) ?>" alt="" class="fav_img"></a>
            <h2 class="fav_nom"><a href="<?= $lien ?>"><?= htmlspecialchars($f["boutname"]) ?></a></h2>
            <h3 class="fav_prod"><?= htmlspecialchars($f["nom"]) ?></h3>
            <p class="fav_px"><?= number_format($f["prix"], 0, ',', ' ') ?> Ar</p>
            <button type="button" class="fav" data-produit="<?= (int) $f["id_produit"] ?>" title="retirer des favoris"></button>
        </div>
        <?php
    }
}
?>