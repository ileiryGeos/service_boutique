<?php
session_start();

// Page réservée à l'acheteur connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: connection.php");
    exit;
}

require_once "functions/panier.php";
require_once "functions/commande.php";
require_once "utils/image.php";

$id_user = (int) $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["commander"])) {
        // bouton "commander" : le panier part vers la ou les boutiques
        $resultat = passer_commande($id_user, $_POST["adresse"] ?? "", $_POST["telephone"] ?? "");
        $_SESSION["message_panier"] = $resultat;

    } elseif (isset($_POST["annuler_commande"])) {
        // l'acheteur annule une commande encore "en attente"
        $ok = annuler_commande_acheteur((int) $_POST["id_commande"], $id_user);
        $_SESSION["message_panier"] = $ok
            ? ["success" => true, "message" => "Commande annulée"]
            : ["success" => false, "message" => "Cette commande ne peut plus être annulée"];

    } else {
        // boutons - / + / retirer
        $id_produit = (int) ($_POST["id_produit"] ?? 0);
        $quantite = (int) ($_POST["quantite"] ?? 0);

        if (isset($_POST["retirer"])) {
            retirer_panier($id_user, $id_produit);
        } else {
            changer_quantite($id_user, $id_produit, $quantite);
        }
    }

    header("Location: panier.php");
    exit;
}

// message affiché une seule fois après une action
$message = $_SESSION["message_panier"] ?? null;
unset($_SESSION["message_panier"]);

$panier = get_panier($id_user);
$total = array_sum(array_column($panier, "sous_total"));
$nb_panier = nombre_articles_panier($id_user);
$commandes = get_commandes_acheteur($id_user);

function prix($montant) {
    return number_format($montant, 0, ',', ' ') . " Ar";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mon panier — V-STORE</title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png">
    <link rel="apple-touch-icon" href="images/favicon-180.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css_v2/header_vue2.css?v=<?= filemtime("css_v2/header_vue2.css") ?>">
    <link rel="stylesheet" href="css/panier.css?v=<?= filemtime("css/panier.css") ?>">
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue2.php" id="nom"><img src="images/logo.svg" alt="V-STORE"></a>
            <ul>
                <li><a href="vue2.php" class="gar"><i>accueil</i></a></li>
                <li><a href="panier.php" class="gar"><i>panier (<span class="nb_panier"><?= $nb_panier ?></span>)</i></a></li>
                <li><a href="deconnexion.php" class="gar"><i>déconnexion</i></a></li>
            </ul>
        </nav>
    </header>

    <section id="panier">
        <h1 class="titre_panier">mon panier</h1>

        <?php if ($message) : ?>
            <p class="pan_message <?= $message["success"] ? "pan_ok" : "pan_erreur" ?>"><?= htmlspecialchars($message["message"]) ?></p>
        <?php endif; ?>

        <?php if (empty($panier)) : ?>
            <p class="pan_vide">Votre panier est vide. Cliquez sur « buy » sous un produit pour l'ajouter.</p>
        <?php endif; ?>

        <?php foreach ($panier as $l) : ?>
        <div class="ligne_panier">
            <img src="images/<?= htmlspecialchars(image_ou($l["photo"], "produit.jpg")) ?>" alt="">

            <div class="pan_info">
                <h2><?= htmlspecialchars($l["nom"]) ?></h2>
                <p>boutique : <a href="suit_vue2.php?id=<?= (int) $l["id_boutique"] ?>"><?= htmlspecialchars($l["boutname"]) ?></a></p>
                <p><?= prix($l["prix_unitaire"]) ?> / pièce
                    <?php if ($l["quantite"] > QUANTITE_PRIX_GROS) : ?><span class="pan_gros">(prix de gros)</span><?php endif; ?>
                </p>
            </div>

            <div class="pan_qte">
                <form method="POST">
                    <input type="hidden" name="id_produit" value="<?= (int) $l["id_produit"] ?>">
                    <input type="hidden" name="quantite" value="<?= $l["quantite"] - 1 ?>">
                    <button class="pan_but" title="un de moins">−</button>
                </form>

                <b><?= (int) $l["quantite"] ?></b>

                <form method="POST">
                    <input type="hidden" name="id_produit" value="<?= (int) $l["id_produit"] ?>">
                    <input type="hidden" name="quantite" value="<?= $l["quantite"] + 1 ?>">
                    <button class="pan_but" title="un de plus" <?= $l["quantite"] >= $l["stock"] ? "disabled" : "" ?>>+</button>
                </form>

                <form method="POST">
                    <input type="hidden" name="id_produit" value="<?= (int) $l["id_produit"] ?>">
                    <button class="pan_but pan_retirer" name="retirer">retirer</button>
                </form>
            </div>

            <p class="pan_prix"><?= prix($l["sous_total"]) ?></p>
        </div>
        <?php endforeach; ?>

        <?php if (!empty($panier)) : ?>
        <div class="pan_total">
            <span>Total</span>
            <b><?= prix($total) ?></b>
        </div>

        <!-- bouton "commander" : envoie la commande à chaque boutique concernée -->
        <form method="POST" class="pan_commander">
            <label>Livraison à :</label>
            <input type="text" name="adresse" value="<?= htmlspecialchars($_SESSION["adresse"] ?? "") ?>" placeholder="adresse de livraison ..." required>
            <label>Téléphone :</label>
            <input type="tel" name="telephone" value="<?= htmlspecialchars($_SESSION["telephone"] ?? "") ?>" placeholder="numéro ..." required>
            <button class="but_commander" name="commander">commander</button>
        </form>
        <?php endif; ?>

        <a href="vue2.php" class="pan_retour">← continuer mes achats (toutes les boutiques)</a>

        <?php if (!empty($commandes)) : ?>
        <h2 class="titre_commandes">mes commandes</h2>

        <?php foreach ($commandes as $c) : ?>
        <div class="commande">
            <div class="commande_tete">
                <span>Commande n° <b><?= (int) $c["id"] ?></b> du <?= htmlspecialchars($c["date_commande"]) ?>
                    — boutique <a href="suit_vue2.php?id=<?= (int) $c["id_boutique"] ?>"><?= htmlspecialchars($c["boutname"]) ?></a></span>
                <span class="statut statut_<?= htmlspecialchars($c["statut"]) ?>"><?= htmlspecialchars(STATUTS_COMMANDE[$c["statut"]] ?? $c["statut"]) ?></span>
            </div>
            <ul>
                <?php foreach ($c["lignes"] as $l) : ?>
                    <li><?= htmlspecialchars($l["nom_produit"]) ?> × <?= (int) $l["quantite"] ?> à <?= prix($l["prix_unitaire"]) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="commande_pied">
                <span>livraison : <?= htmlspecialchars($c["adresse_livraison"]) ?> — <?= htmlspecialchars($c["telephone"]) ?></span>
                <span>
                    total <b><?= prix($c["total"]) ?></b>
                    <?php if ($c["statut"] === "en_attente") : ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Annuler cette commande ?')">
                            <input type="hidden" name="id_commande" value="<?= (int) $c["id"] ?>">
                            <button class="pan_but pan_retirer" name="annuler_commande">annuler</button>
                        </form>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </section>
</body>

</html>
