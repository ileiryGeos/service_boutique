<?php
require_once "functions/db.php";
require_once "functions/inscription_boutique.php";

if(isset($_POST["enter"])){
    inscription();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>créer ma boutique — V-STORE</title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png">
    <link rel="apple-touch-icon" href="images/favicon-180.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css/inscription.css?v=<?= filemtime("css/inscription.css") ?>">
</head>

<body>
    <section id="inscrit">

        <?php if(!isset($_SESSION["id_boutique"])): ?>

        <!-- Étape 1 : inscription de la boutique (Zara) -->
        <form class="form_inscr" method="POST" enctype="multipart/form-data">
            <img src="images/logo.svg" alt="V-STORE" class="marque_form">
            <label for="" class="nom_box">nom de votre boutique</label>
            <small>(il vous servira pour vous connecter, avec votre mot de passe)</small>
            <input type="text" class="inp_nom" name="boutname" required>

            <label for="" class="pdp">photo ou logo de votre boutique</label>
            <input type="file" class="inp_pdp" name="file" accept="image/*">

            <div class="securite">
                <label for="" class="code_n">numéro et email </label>
                <input type="text" name="numTel" id="" class="inp_n" placeholder="numéro ..." required>
                <input type="email" name="email" id="" class="inp_n" placeholder="email ..." required>

                <label for="" class="code_n">votre nouveau mot de passe</label>
                <input type="password" name="password" id="" class="inp_n" required>
            </div>

            <label for="">localisation</label>
            <input type="text" name="localisation" required>

            <div class="type">
                <label for="" class="lab_type">type de votre produit,</label>
                <input type="text" class="nbr_type" placeholder="type ...">
                <span class="but_type">+</span>
                <!-- <input type="text"   placeholder="lesquel ?"> -->
            </div>

            <label for="">définition ou un style de publicité</label>
            <textarea name="definition" id=""></textarea>

            <button class="suivent" name="enter">suivant</button>
            <a href="connection.php">déjà une boutique ? se connecter</a>
        </form>

        <?php else: ?>

        <!-- Étape 2 : ajout des produits de la boutique connectée (Steve) -->
        <form action="" class="form_inscr prod" id="form-produit">
            <img src="images/logo.svg" alt="V-STORE" class="marque_form">
            <label for="type-prod" class="lab_prod">type de produit</label>
            <input type="text" id="type-prod" class="inp_prod" name="type" required>

            <label for="nom-prod" class="lab_prod">nom d'un produit</label>
            <input type="text" name="nom" id="nom-prod" class="inp_prod" required>

            <label for="desc-prod" class="lab_prod">description de votre produit</label>
            <textarea name="description" id="desc-prod" class="inp_prod" required></textarea>


            <label for="photo-prod" class="lab_prod">photo du produit</label>
            <input type="file" name="photo" id="photo-prod" class="inp_prod" accept="image/*" required>

            <label for="nbr-prod" class="lab_prod">nombre de produits que vous avez dans votre stock</label>
            <input type="number" min="0" name="stock" id="nbr-prod" class="inp_prod" required>

            <div action="" id="prix_prd">
                <h1>prix du produit</h1>
                <label for="prix-prod">un produit</label>
                <input type="number" min="0" name="prix" id="prix-prod" required>

                <label for="prix-gros-prod">si entre 1 et 5</label>
                <input type="number" min="0" name="prix_gros" id="prix-gros-prod" required>


            </div>
            <button id="fini">fini</button>

        </form>
        <div id="recu" class="recu">


        </div>

        <a href="vue3.php" class="suivent">retour à mon espace vendeur</a>

        <script src="ajax/ajout_prod_inscri.js"></script>

        <?php endif; ?>

    </section>
</body>

</html>
