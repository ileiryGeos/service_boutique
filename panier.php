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
    <title>panier</title>
    <link rel="stylesheet" href="css_v2/header_vue2.css?v=<?= filemtime("css_v2/header_vue2.css") ?>">
    <style>
        body { background: wheat; }

        #panier {
            padding: 7vw 10vw 5vw;
        }

        .titre_panier {
            font-size: 3vw;
            color: red;
            text-transform: uppercase;
            text-align: center;
            margin-bottom: 2vw;
        }

        .ligne_panier {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(128, 128, 128, 0.253);
            border: solid .1vw blueviolet;
            border-radius: 1vw;
            padding: 1vw;
            margin-bottom: 1vw;
        }

        .ligne_panier img {
            width: 8vw;
            height: 8vw;
            object-fit: cover;
            border-radius: .5vw;
        }

        .pan_info { width: 30%; }
        .pan_info h2 { color: blue; text-transform: capitalize; font-size: 1.8vw; }
        .pan_info a { color: blueviolet; }
        .pan_gros { color: green; }

        .pan_qte { display: flex; align-items: center; gap: .5vw; }
        .pan_qte form { display: inline; }
        .pan_qte b { font-size: 1.8vw; min-width: 3vw; text-align: center; }

        .pan_but {
            border: solid .1vw blue;
            background: moccasin;
            border-radius: .5vw;
            padding: .3vw .8vw;
            cursor: pointer;
        }

        .pan_retirer { border-color: red; color: red; }

        .pan_prix { width: 15%; text-align: right; color: red; font-size: 1.6vw; font-weight: bold; }

        .pan_total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 2.5vw;
            color: midnightblue;
            border-top: solid .2vw blueviolet;
            padding-top: 1vw;
        }

        .pan_total span, .pan_total b { font-size: 2.5vw; }
        .pan_total b { color: red; }

        .pan_retour {
            display: inline-block;
            margin-top: 2vw;
            padding: .7vw 1.5vw;
            border-radius: .5vw;
            background: blue;
            color: white;
            text-decoration: none;
        }

        .pan_vide { text-align: center; font-size: 2vw; color: grey; margin: 3vw 0; }

        .pan_message { padding: 1vw; border-radius: .5vw; margin-bottom: 1.5vw; text-align: center; font-size: 1.4vw; }
        .pan_ok { background: rgba(0, 128, 0, 0.2); color: darkgreen; border: solid .1vw green; }
        .pan_erreur { background: rgba(255, 0, 0, 0.15); color: darkred; border: solid .1vw red; }

        .pan_commander {
            display: flex;
            flex-wrap: wrap;
            gap: 1vw;
            align-items: center;
            margin-top: 1.5vw;
            padding: 1vw;
            border: solid .1vw blueviolet;
            border-radius: 1vw;
            background: rgba(255, 255, 255, 0.4);
        }

        .pan_commander label { color: midnightblue; }
        .pan_commander input { padding: .5vw; border-radius: .5vw; border: solid .1vw blueviolet; }
        .pan_commander input[name=adresse] { width: 30vw; }

        .but_commander {
            margin-left: auto;
            padding: .8vw 3vw;
            border-radius: .5vw;
            border: none;
            background: red;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
        }

        .but_commander:hover { background: orangered; }

        .titre_commandes { font-size: 2.2vw; color: red; text-transform: uppercase; margin: 4vw 0 1vw; }

        .commande {
            border: solid .1vw brown;
            border-radius: 1vw;
            background: rgba(255, 166, 0, 0.25);
            padding: 1vw;
            margin-bottom: 1vw;
        }

        .commande_tete { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5vw; }
        .commande_tete b { color: blue; }
        .commande ul { margin-left: 2vw; }
        .commande_pied { display: flex; justify-content: space-between; align-items: center; margin-top: .5vw; }
        .commande_pied b { color: red; }

        .statut { padding: .2vw .8vw; border-radius: 1vw; color: white; }
        .statut_en_attente { background: orange; }
        .statut_acceptee { background: blue; }
        .statut_livree { background: green; }
        .statut_annulee { background: grey; }
    </style>
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue2.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <li><a href="vue2.php" class="gar"><i>accueil</i></a></li>
                <li><a href="panier.php" class="gar"><i>panier (<span class="nb_panier"><?= $nb_panier ?></span>)</i></a></li>
                <li><a href="deconnexion.php" class="gar"><i>deconnection</i></a></li>
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
