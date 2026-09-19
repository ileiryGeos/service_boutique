<?php
session_start();

// Page réservée à l'acheteur connecté (Daddy)
if (!isset($_SESSION["user_id"])) {
    header("Location: connection.php");
    exit;
}

require_once "functions/db.php";
require_once "functions/acheteur.php";
require_once "utils/image.php";
require_once "functions/panier.php";
require_once "functions/favori.php";
require_once "functions/historique.php";

// Liste des boutiques et de leurs produits (Karine),
// filtrée par la barre de recherche (?search=...)
$boutique= filtrer_boutiques(get_image_detail());
//var_dump ($boutique);

$recherche = trim($_GET["search"] ?? "");

// Affichage initial du profil depuis la session (Daddy),
// ensuite ajax/acheteur.js recharge le profil depuis la base (Karine)
$nom = $_SESSION["nom"] ?? "";
$nb_panier = nombre_articles_panier((int) $_SESSION["user_id"]);
$favoris = get_favoris((int) $_SESSION["user_id"]);
$historique = get_historique((int) $_SESSION["user_id"]);
$email = $_SESSION["email"] ?? "";
$telephone = $_SESSION["telephone"] ?? "";
$date_naissance = $_SESSION["date_naissance"] ?? "";
$adresse = $_SESSION["adresse"] ?? "";
$photo = $_SESSION["photo"] ?? "";

$photo = image_ou($photo, "pdp.jpg");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boite</title>
    <link rel="stylesheet" href="css/tete.css?v=<?= filemtime("css/tete.css") ?>">
    <link rel="stylesheet" href="css/boutic.css?v=<?= filemtime("css/boutic.css") ?>">
    <link rel="stylesheet" href="css_v2/header_vue2.css?v=<?= filemtime("css_v2/header_vue2.css") ?>">
    <link rel="stylesheet" href="css_v2/tete_vue2.css?v=<?= filemtime("css_v2/tete_vue2.css") ?>">
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue2.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <!-- <li><a href="" class="gar"><i>lanngue</i></a></li> -->
                <li><a href="vue2.php" class="gar"><i>accueil</i></a></li>
                <li><a href="panier.php" class="gar"><i>panier (<span class="nb_panier"><?= $nb_panier ?></span>)</i></a></li>
                <li><a href="" class="gar"><i>aide et suport</i></a></li>
                <!-- afaka manova : entent que ... -->
                <li><a href="deconnexion.php" class="gar"><i>deconnection</i></a></li>
                <li><input type="text" class="gar langue" value="langue"></li>
            </ul>
        </nav>
    </header>

    <section id="tete_vue2">
        <div id="bord">

            <div class="ah_pdp">
                <div class="img_ah">
                    <img src="images/<?php echo e($photo); ?>" alt="Photo de profil" class="img_pdp">
                </div>
                <input type="file" class="img_ah_modi">
            </div>

            <div class="ah_noms">
                <h1 class="ah_nom"><?php echo e($nom); ?></h1>
                <!-- <input type="file" class="ah_but_nom"> -->
            </div>

            <div class="toust">
                <div class="info_acheteur">
                    <h6 class="tou_ve">email: <b><?php echo e($email); ?></b></h6>
                    <h6 class="tou_ve">N°: <b><?php echo e($telephone); ?></b></h6>
                    <h6 class="tou_ve">date naissence: <b><?php echo e($date_naissance); ?></b></h6>
                    <h6 class="tou_ve">adress: <b><?php echo e($adresse); ?></b></h6>
                    <!-- <h6 class="tou_ve">solde: <b>99 000ar</b></h6> -->
                </div>

                <div class="modifier">
<button type="button" class="afir_modi but_ouvrir_modif" aria-expanded="false">Modifier votre profil</button>

<form class="form_modi" id="form_modi">

    <label>
        Vous pouvez modifier une ou plusieurs informations
    </label>

    <!-- NOM -->
    <input
        type="text"
        placeholder="Nouveau nom ..."
        name="name"
        id="name"
        class="inp_modif"
    >

    <!-- ADRESSE -->
    <input
        type="text"
        placeholder="Nouvelle adresse ..."
        name="address"
        id="address"
        class="inp_modif"
    >

    <!-- DATE DE NAISSANCE -->
    <input
        type="date"
        name="birthdate"
        id="birthdate"
        class="inp_modif"
    >

    <label>
        Votre sécurité
    </label>

    <!-- NUMERO -->
    <input
        type="number"
        placeholder="Nouveau numéro ..."
        name="number"
        id="number"
        class="inp_modif"
    >

    <!-- EMAIL -->
    <input
        type="email"
        placeholder="Nouvelle adresse email ..."
        name="email"
        id="email"
        class="inp_modif"
    >

    <!-- MOT DE PASSE ACTUEL -->
    <input
        type="password"
        placeholder="Mot de passe actuel ..."
        name="old_password"
        id="old_password"
        class="inp_modif"
    >

    <!-- NOUVEAU MOT DE PASSE -->
    <input
        type="password"
        placeholder="Nouveau mot de passe ..."
        name="new_password"
        id="new_password"
        class="inp_modif"
    >

    <button
        type="submit"
        class="but_mode"
    >
        Modifier
    </button>

</form>

                </div>
            </div>


            <!-- HISTORIQUE d'achats : produits commandés (functions/historique.php) -->
            <h4 class="titre_barre">historique d'achats</h4>
            <div class="histior" id="liste_historique">
                <?php afficher_historique($historique); ?>
            </div>

            <!-- FAVORIS : produits marqués avec l'étoile ★ (functions/favori.php) -->
            <h4 class="titre_barre">favoris</h4>
            <div class="favorite" id="liste_favoris">
                <?php afficher_favoris($favoris); ?>
            </div>
            <div>


            </div>
        </div>
    </section>

    <section class="boutique" id="horo">


        <form class="tete" method="GET" action="vue2.php">
            <h1 class="bout_nom">service box</h1>
            <div class="bar_rech">
                <input type="search" class="inp_rech" name="search" value="<?= e($recherche) ?>" placeholder="Recherche ... " >
                <button type="submit" class="but_rech"><i>rechercher</i></button>
            </div>
            <h2 class="bout_b">bienvenue sur notre site</h2>
            <p class="bout_l">On fait reunir plusieurs boutique pour que vous ne perdre plus de temps</p>

</form>

    <?php if ($recherche !== ""): ?>
        <p style="width:100%;text-align:center;">
            <?= empty($boutique) ? "Aucune boutique" : count($boutique) . " boutique(s)" ?>
            pour « <?= e($recherche) ?> » — <a href="vue2.php">voir toutes les boutiques</a>
        </p>
    <?php endif; ?>


    <?php foreach ($boutique as $b): ?>

    <a
        href="suit_vue2.php?id=<?= (int) ($b["id"] ?? 0) ?>"
        id="boite"
        style="text-decoration: none;"
    >
        <div id="liste_boutiques" class="points">

            <div class="nom_img">
                <img
                    class="pdp"
                    src="images/<?= rawurlencode(image_ou($b["pdc"] ?? "", "kara.jpg")) ?>"
                    alt="<?= htmlspecialchars($b["name"] ?? "") ?>"
                >

                <h1 class="nombox">
                    <?= htmlspecialchars($b["name"] ?? "") ?>
                </h1>
            </div>

            <p class="definition">
                points fort du box <br>

                <?= htmlspecialchars($b["description"] ?? "") ?><br>

                <b style="color: aqua;">
                    TYPE : <?= htmlspecialchars($b["type"] ?? "") ?>
                </b>.
            </p>
        </div>

        <div class="petie">

            <?php foreach (($b["produits"] ?? []) as $i): ?>

                <img
                    class="img_pt"
                    src="images/<?= rawurlencode(image_ou($i["image"] ?? "", "produit.jpg")) ?>"
                    alt="<?= htmlspecialchars($i["produit"] ?? "") ?>"
                >

            <?php endforeach; ?>

        </div>
    </a>

<?php endforeach; ?>




    </section>

    <script src="ajax/acheteur.js"></script>
    <script src="ajax/favori.js"></script>
    <script src="ajax/historique.js"></script>

</body>

</html>
