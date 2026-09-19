<?php
// HISTORIQUE d'achats de l'acheteur connecté : liste "histior" de la barre de gauche
// (vue2.php / suit_vue2.php), au-dessus des favoris.
// Un achat = un produit d'une commande passée avec le bouton "commander" (commandes annulées exclues).
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/commande.php";
require_once __DIR__ . "/../utils/image.php";

$db = connection_db_bou();

// nombre d'achats affichés dans la barre de gauche
const LIMITE_HISTORIQUE = 10;


// Derniers produits achetés (le plus récent en premier)
function get_historique(int $id_user): array
{
    global $db;

    $req = $db->prepare("
        SELECT l.id, l.nom_produit, l.quantite, c.date_commande, c.statut,
               c.id_boutique, b.boutname, p.photo
        FROM commande_ligne l
        JOIN commande c ON c.id = l.id_commande
        JOIN inscription_vendeur b ON b.id = c.id_boutique
        LEFT JOIN produits p ON p.id = l.id_produit
        WHERE c.id_user = ?
          AND c.statut <> 'annulee'
          AND l.dans_historique = 1
        ORDER BY c.date_commande DESC, l.id DESC
        LIMIT " . LIMITE_HISTORIQUE
    );
    $req->execute([$id_user]);

    return $req->fetchAll(PDO::FETCH_ASSOC);
}


// Bouton × : retirer un achat de l'historique (la commande, elle, ne change pas)
function retirer_historique(int $id_user, int $id_ligne): void
{
    global $db;

    $req = $db->prepare("
        UPDATE commande_ligne l
        JOIN commande c ON c.id = l.id_commande
        SET l.dans_historique = 0
        WHERE l.id = ? AND c.id_user = ?
    ");
    $req->execute([$id_ligne, $id_user]);
}


// Contenu de la liste "histior" (utilisé par les pages et par api/historique_api.php)
function afficher_historique(array $historique): void
{
    if (empty($historique)) {
        echo '<p class="hist_vide">Aucun achat pour le moment.</p>';
        return;
    }

    foreach ($historique as $h) {
        $statut = STATUTS_COMMANDE[$h["statut"]] ?? $h["statut"];
        ?>
        <div class="boi_hist">
            <a href="suit_vue2.php?id=<?= (int) $h["id_boutique"] ?>" class="lien_hist" title="voir la boutique <?= htmlspecialchars($h["boutname"]) ?>">
                <img src="images/<?= htmlspecialchars(image_ou($h["photo"], "produit.jpg")) ?>" alt="" class="img_hist">
                <span class="texte_hist">
                    <strong class="prod_hist"><?= htmlspecialchars($h["nom_produit"]) ?> <b>× <?= (int) $h["quantite"] ?></b></strong>
                    <small><?= htmlspecialchars($h["boutname"]) ?> — <?= htmlspecialchars(substr($h["date_commande"], 0, 10)) ?> — <?= htmlspecialchars($statut) ?></small>
                </span>
            </a>
            <button type="button" class="hist_sup" data-ligne="<?= (int) $h["id"] ?>" title="retirer de l'historique">x</button>
        </div>
        <?php
    }
}
?>