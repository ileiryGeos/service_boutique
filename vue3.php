<?php
    require "functions/db.php";
    require_once "functions/inscription_boutique.php";
    require_once "functions/func_prod.php";
    require_once "utils/image.php";
    require_once "functions/statut_produit.php";
    require_once "functions/commande.php";

    // Page réservée au vendeur connecté
    if(!isset($_SESSION["id_boutique"])){
        header("Location: connection.php");
        exit;
    }

    // boutons "accepter" / "livrée" / "annuler" d'une commande reçue
    if(isset($_POST["statut_commande"])){
        changer_statut_commande((int) $_POST["id_commande"], (int) $_SESSION["id_boutique"], $_POST["statut_commande"]);
        header("Location: vue3.php#commandes");
        exit;
    }

    // bouton "supprimer ce produit"
    if(isset($_POST["supprimer_produit"])){
        supprimer_produit();
        header("Location: vue3.php");
        exit;
    }

    // $boutique = inscription();
    $boutique = get_info();

    if(isset($_POST["modifier_profil"])){
        modifier_profil();
        }

        if(isset($_POST["modifier_securite"])){
            modifier_securite();
        }

    // produits de la boutique (Steve) et leurs types
    $produits = get_produits_boutique($boutique["id"]);
    $types = array_unique(array_column($produits, "type"));
    $logo = image_ou($boutique["logo"], "kara.jpg");

    // commandes envoyées par les acheteurs à cette boutique
    $commandes = get_commandes_boutique($boutique["id"]);
    $nb_en_attente = count(array_filter($commandes, fn($c) => $c["statut"] === "en_attente"));

    // var_dump($boutique);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boîte</title>
    <link rel="stylesheet" href="css/tete.css?v=<?= filemtime("css/tete.css") ?>">
    <link rel="stylesheet" href="css/boutic.css?v=<?= filemtime("css/boutic.css") ?>">
    <link rel="stylesheet" href="css_v3/header_vue3.css?v=<?= filemtime("css_v3/header_vue3.css") ?>">
    <link rel="stylesheet" href="css_v3/tete_vue3.css?v=<?= filemtime("css_v3/tete_vue3.css") ?>">
    <link rel="stylesheet" href="css_v3/commande_vue3.css?v=<?= filemtime("css_v3/commande_vue3.css") ?>">
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue3.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <!-- <li><a href="" class="gar"><i>langue</i></a></li> -->
                <li><a href="#commandes" class="gar"><i>commandes (<?= $nb_en_attente ?>)</i></a></li>
                <li><a href="" class="gar"><i>aide et support</i></a></li>
                <!-- afaka manova : entent que ... -->
                <li><a href="deconnexion.php" class="gar"><i>déconnexion</i></a></li>
                <li><input type="text" class="gar langue" value="langue"></li>
            </ul>
        </nav>
    </header>

    <section id="tete_vue3">
        <div id="bord_v3">
            <div class="ah_pdp">
                <div class="img_ah">
                    <img src="images/<?= htmlspecialchars($logo) ?>" alt="" class="img_pdp">
                    <hr class="v3_hr">
                    <h1 class="v3_nom" style="text-align:center;"><?= htmlspecialchars($boutique["boutname"]) ?></h1>
                </div>
            </div>

            <div class="toust">
                <h6 class="tou_ve">email : <b><?= htmlspecialchars($boutique["email"]) ?></b></h6>
                <h6 class="tou_ve">N° : <b><?= htmlspecialchars($boutique["numTel"]) ?></b></h6>
                <h6 class="tou_ve">localisation : <b><?= htmlspecialchars($boutique["localisation"]) ?></b></h6>
                <h6 class="tou_ve">date de création : <b><?= htmlspecialchars($boutique["date"]) ?></b></h6>
                <!-- <h6 class="tou_ve">solde : <b>99 000ar</b></h6> -->
                <div class="modifier">
                    <h2 class="afir_modi">Voulez-vous modifier votre profil ?</h2>
                    <div action="" class="modi">
                        <form method="POST" class="form_modi">
                            <label >Vous pouvez modifier une seule ou toutes les informations</label>
                            <input type="text" placeholder="Nouveau nom ..." name="boutname" class="inp_modif">
                            <input type="text" placeholder="Nouvelle localisation ..." name="localisation" class="inp_modif">
                            <button class="but_mode" name="modifier_profil">modifier</button>

                        </form>

                        <form method="POST" class="form_modi">
                            <label for="">Pour votre sécurité, il faut compléter tous les champs</label>
                            <input type="text" placeholder="Nouveau numéro ..." name="numTel" id="" class="inp_modif" required>
                            <input type="email" placeholder="Nouvelle adresse email ..." name="email" id="" class="inp_modif" required>
                            <input type="password" placeholder="Ancien mot de passe ..." name="ancien_password" id=""
                                class="inp_modif" required>
                            <input type="password" placeholder="Nouveau mot de passe ..." name="nouveau_password" id=""
                                class="inp_modif" required>
                            <button class="but_mode" name="modifier_securite">modifier</button>
                        </form>
                    </div>
                </div>

                <div class="modifier_px">
                    <div class="modi">
                        <h2 class="afir_modi">Ajouter un nouveau produit</h2>
                        <!-- formulaire de la maquette suit_vue3.html, envoyé à l'API de Steve -->
                        <form class="form_modi" id="form_ajout_prod" enctype="multipart/form-data">
                            <label for="">Il faut compléter tous les champs</label>
                            <input type="text" placeholder="Type de produit ..." name="type" class="inp_modif" required>
                            <input type="text" placeholder="Nom de produit ..." name="nom" class="inp_modif" required>
                            <input type="text" placeholder="Sa description ..." name="description" class="inp_modif" required>
                            <input type="file" name="photo" class="inp_modif" accept="image/*" required>
                            <input type="number" min="0" placeholder="Nombre en stock ..." name="stock" class="inp_modif" required>
                            <input type="number" min="0" placeholder="Son prix ..." name="prix" class="inp_modif" required>
                            <input type="number" min="0" placeholder="Son prix en gros ..." name="prix_gros" class="inp_modif" required>
                            <button class="but_mode">créer</button>
                        </form>
                    </div>

                </div>


            </div>
            <div class="suprim">
                <?php if (empty($produits)) : ?>
                    <p style="width:100%;text-align:center;">Aucun produit pour le moment</p>
                <?php endif; ?>

                <?php foreach ($produits as $p) : ?>
                <div class="boi_sup">
                    <img src="images/<?= htmlspecialchars(image_ou($p["photo"], "produit.jpg")) ?>" alt="" class="img_sup">
                    <div class="prod_sup">
                        <h5 class="prod1"><?= htmlspecialchars($p["type"]) ?>,</h5>
                        <span class="etat_prod <?= htmlspecialchars($p["statut_validation"]) ?>"><?= htmlspecialchars(STATUTS_PRODUIT[$p["statut_validation"]] ?? "") ?></span>
                        <h5 class="prod1"><?= htmlspecialchars($p["nom"]) ?></h5>
                        <h5 class="prod"><?= number_format($p["prix"], 0, ',', ' ') ?> Ar ;</h5>
                        <h5 class="prod"><?= number_format($p["prix_gros"], 0, ',', ' ') ?> Ar</h5>

                    </div>
                    <form method="POST" class="form_sup">
                        <input type="hidden" name="id_produit" value="<?= (int) $p["id"] ?>">
                        <button class="suprimer" name="supprimer_produit">supprimer ce produit</button>
                    </form>
                    <!-- <button class="hist_sup">x</button> -->
                </div>
                <?php endforeach; ?>


            </div>

        </div>
    </section>

    <section id="horo">
        <!-- <div class="tete">
            <h1 class="bout_nom">service box</h1>
            <div class="bar_rech">
                <input type="text" class="inp_rech" placeholder="Recherche ... ">
                <button class="but_rech"><i>recherche</i></button>
            </div>
            <h2 class="bout_b">bienvenue sur notre site</h2>
            <p class="bout_l">Nous réunissons plusieurs boutiques pour que vous ne perdiez plus de temps</p>

        </div> -->

        <a href="suit_vue3.php" id="boite" style="text-decoration: none;">
            <div class="points">
                <div class="nom_img">
                    <!-- anaran'ilay box -->
                    <img class="pdp" src="images/<?= htmlspecialchars($logo) ?>" alt="">
                    <h1 class="nombox"><?= htmlspecialchars($boutique["boutname"]) ?></h1>
                    <!-- sariny mampiavaka azy -->
                </div>

                <!-- famaritana ilay box -->
                <p class="definition">Points forts du box : <br>
                   <?= htmlspecialchars($boutique["definition"]) ?><br><b style="color: aqua;">TYPE :
                        <?= htmlspecialchars(implode(", ", $types)) ?></b>.</p>

            </div>

            <div class="petie">
                <?php foreach ($produits as $p) : ?>
                    <img class="img_pt" src="images/<?= htmlspecialchars(image_ou($p["photo"], "produit.jpg")) ?>" alt="<?= htmlspecialchars($p["nom"]) ?>">
                <?php endforeach; ?>

            </div>
        </a>




        <!-- COMMANDES REÇUES : envoyées par le bouton "commander" du panier des acheteurs -->
        <div class="commandes_recues" id="commandes">
            <h2 class="titre_commandes">commandes reçues
                <?php if ($nb_en_attente > 0) : ?><span class="nouvelles"><?= $nb_en_attente ?> en attente</span><?php endif; ?>
            </h2>

            <?php if (empty($commandes)) : ?>
                <p class="aucune_commande">Aucune commande pour le moment.</p>
            <?php endif; ?>

            <?php foreach ($commandes as $c) : ?>
            <div class="commande commande_<?= htmlspecialchars($c["statut"]) ?>">
                <div class="commande_tete">
                    <img src="images/<?= htmlspecialchars(image_ou($c["photo_client"], "pdp.jpg")) ?>" alt="" class="commande_img">
                    <div class="commande_client">
                        <b><?= htmlspecialchars($c["client"]) ?></b>
                        <span>commande n° <?= (int) $c["id"] ?> — le <?= htmlspecialchars($c["date_commande"]) ?></span>
                    </div>
                    <span class="statut statut_<?= htmlspecialchars($c["statut"]) ?>"><?= htmlspecialchars(STATUTS_COMMANDE[$c["statut"]] ?? $c["statut"]) ?></span>
                </div>

                <ul class="commande_lignes">
                    <?php foreach ($c["lignes"] as $l) : ?>
                        <li><b><?= (int) $l["quantite"] ?></b> × <?= htmlspecialchars($l["nom_produit"]) ?> à <?= number_format($l["prix_unitaire"], 0, ',', ' ') ?> Ar</li>
                    <?php endforeach; ?>
                </ul>

                <div class="commande_pied">
                    <span>à livrer à <i><?= htmlspecialchars($c["adresse_livraison"]) ?></i> — joignable au <b><?= htmlspecialchars($c["telephone"]) ?></b></span>
                    <span class="commande_total"><?= number_format($c["total"], 0, ',', ' ') ?> Ar</span>
                </div>

                <?php if (!empty(SUITE_COMMANDE[$c["statut"]])) : ?>
                <form method="POST" class="commande_actions">
                    <input type="hidden" name="id_commande" value="<?= (int) $c["id"] ?>">
                    <?php if (in_array("acceptee", SUITE_COMMANDE[$c["statut"]])) : ?>
                        <button class="but_statut but_accepter" name="statut_commande" value="acceptee">accepter</button>
                    <?php endif; ?>
                    <?php if (in_array("livree", SUITE_COMMANDE[$c["statut"]])) : ?>
                        <button class="but_statut but_livrer" name="statut_commande" value="livree">marquer livrée</button>
                    <?php endif; ?>
                    <button class="but_statut but_annuler" name="statut_commande" value="annulee"
                        onclick="return confirm('Annuler cette commande ? Le stock sera rendu.')">annuler</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

    </section>

    <script src="ajax/produit_vendeur.js"></script>

</body>

</html>
