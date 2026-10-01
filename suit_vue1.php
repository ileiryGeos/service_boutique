<?php
// Page publique d'une boutique (depuis vue1.php) : maquette suit_Vue1.html
// + boutique et produits de Karine, en lecture seule (sans compte)
require_once "functions/db.php";
require_once "functions/acheteur.php";
require_once "utils/image.php";

$id_boutique = (int) ($_GET["id"] ?? 0);

$boutique = couverture_boutique();
$produits = get_info_detail();

$types = array_unique(array_column($produits, "type"));
$type_choisi = $_GET["type"] ?? "";
$produits = filtrer_par_type($produits);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boîte</title>
    <link rel="stylesheet" href="css/tete.css?v=<?= filemtime("css/tete.css") ?>">
    <link rel="stylesheet" href="css_v1/detail_vue1.css?v=<?= filemtime("css_v1/detail_vue1.css") ?>">
    <link rel="stylesheet" href="css_v1/header_vue1.css?v=<?= filemtime("css_v1/header_vue1.css") ?>">
    <link rel="stylesheet" href="css_v1/bar_vue1.css?v=<?= filemtime("css_v1/bar_vue1.css") ?>">
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue1.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <!-- <li><a href="" class="gar"><i>langue</i></a></li> -->
                <li><a href="" class="gar"><i>aide et support</i></a></li>
                <!-- <li><a href="" class="gar"><i>parametre</i></a></li> -->
                <li><input type="text" class="gar langue" value="langue"></li>
            </ul>
        </nav>
    </header>

    <section id="tete2">
        <div class="bil">
            <a href="connection.php" class="mon_cmp">Ouvrir par mon compte</a>
            <a href="profil.php" class="boxs">
                <div class="label_img">
                    <h6 class="afirm">Créer un nouveau compte</h6>
                </div>
            </a>

            <a href="inscription.php" class="boxs">
                <div class="label_img">
                    <h6 class="afirm">Travailler avec nous :<br>Vendeur | Vendeuse</h6>
                </div>
                <!-- afaka mividy ,indrindra etana betsaka -->
            </a>
        </div>
    </section>

    <section class="detail">
        <div class="det1">
            <?php foreach ($boutique as $b): ?>
            <div class="card_fondImage">
                <h1 class="nom_bout"><?= htmlspecialchars($b["name"]) ?></h1>
                <p class="desc_box"><?= htmlspecialchars($b["description"]) ?>,<br><em>vous pouvez nous visiter à <b><?= htmlspecialchars($b["lieu"]) ?></b></em></p>
                <button class="retour"><a href="vue1.php">accueil</a></button>
                <img src="images/<?= htmlspecialchars(image_ou($b["pdc"], "kara.jpg")) ?>" alt="" class="fond">
                <hr class="fond_hr">

            </div>
            <?php endforeach; ?>


            <div class="bar_but">
                <button class="but_type"><a href="suit_vue1.php?id=<?= $id_boutique ?>"<?= $type_choisi === "" ? ' style="font-weight:bold;"' : '' ?>>tous</a></button>
                <?php foreach ($types as $t) : ?>
                    <button class="but_type"><a href="suit_vue1.php?id=<?= $id_boutique ?>&amp;type=<?= urlencode($t) ?>"<?= $type_choisi === $t ? ' style="font-weight:bold;"' : '' ?>><?= htmlspecialchars($t) ?></a></button>
                <?php endforeach; ?>
            </div>


            <div class="produit">

                <?php if (empty($produits)) : ?>
                    <h2 style="width:100%;text-align:center;color:blueviolet;">Aucun produit pour le moment</h2>
                <?php endif; ?>

                <?php foreach ($produits as $p) : ?>
                <div class="box_prox">
                    <img src="images/<?= htmlspecialchars(image_ou($p["photo"], "produit.jpg")) ?>" alt="" class="img_pr">
                    <h2 class="nom_pr"><?= htmlspecialchars($p["nom"]) ?></h2>
                    <h6 class="desc_pr"><?= nl2br(htmlspecialchars($p["description"])) ?> <b
                            style="color: midnightblue;font-size: 1.5vw;font-weight: bold;"><?= (int) $p["stock"] ?></b> pièces</h6>
                    <div class="bay_pr">
                        <h4 class="prix_pr"><?= number_format($p["prix"], 0, ',', ' ') ?> Ar</h4>
                        <!-- <button class="butbay">bay</button> -->
                    </div>

                    <h4 class="pr_gros">Le prix pour plus de <b
                        style="color: blue;font-weight: bold;font-size: 1.5vw;">5p</b> est <b><?= number_format($p["prix_gros"], 0, ',', ' ') ?> Ar</b></h4>
                </div>
                <?php endforeach; ?>

            </div>


        </div>
    </section>

</body>

</html>
