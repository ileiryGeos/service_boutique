<?php
// Page d'accueil publique (bouton "ignore" de connection.php) : maquette vue1.html
// + liste des boutiques de Karine, sans compte
require_once "functions/db.php";
require_once "functions/acheteur.php";
require_once "utils/image.php";

$boutique = filtrer_boutiques(get_image_detail());
$recherche = trim($_GET["search"] ?? "");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boîte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css/tete.css?v=<?= filemtime("css/tete.css") ?>">
    <link rel="stylesheet" href="css/boutic.css?v=<?= filemtime("css/boutic.css") ?>">
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

    <section id="horo">
        <form class="tete" method="GET" action="vue1.php">
            <h1 class="bout_nom">service box</h1>
            <div class="bar_rech">
                <input type="search" class="inp_rech" name="search" value="<?= htmlspecialchars($recherche) ?>" placeholder="Recherche ... ">
                <button type="submit" class="but_rech"><i>recherche</i></button>
            </div>
            <h2 class="bout_b">bienvenue sur notre site</h2>
            <p class="bout_l">Nous réunissons plusieurs boutiques pour que vous ne perdiez plus de temps</p>

        </form>

        <?php if ($recherche !== ""): ?>
            <p class="vide">
                <?= empty($boutique) ? "Aucune boutique" : count($boutique) . " boutique(s)" ?>
                pour « <?= htmlspecialchars($recherche) ?> » — <a href="vue1.php">voir toutes les boutiques</a>
            </p>
        <?php endif; ?>

        <?php foreach ($boutique as $b): ?>

        <a href="suit_vue1.php?id=<?= (int) $b["id"] ?>" id="boite">
            <div class="points">
                <div class="nom_img">
                    <!-- anaran'ilay box -->
                    <img class="pdp" src="images/<?= rawurlencode(image_ou($b["pdc"], "kara.jpg")) ?>" alt="">
                    <h1 class="nombox"><?= htmlspecialchars($b["name"]) ?></h1>
                    <!-- sariny mampiavaka azy -->
                </div>

                <!-- famaritana ilay box -->
                <p class="definition">points forts du box <br>
                    <?= htmlspecialchars($b["description"]) ?><br><b>TYPE :
                        <?= htmlspecialchars($b["type"] ?? "") ?></b>.</p>

            </div>

            <div class="petie">
                <?php foreach ($b["produits"] as $i): ?>
                    <img class="img_pt" src="images/<?= rawurlencode(image_ou($i["image"], "produit.jpg")) ?>" alt="<?= htmlspecialchars($i["produit"]) ?>">
                <?php endforeach; ?>
            </div>
        </a>

        <?php endforeach; ?>

    </section>


</body>

</html>
