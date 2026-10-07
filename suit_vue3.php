<?php
    // Page "ma boutique" du vendeur (lien de la carte dans vue3.php) : maquette suit_vue3.html
    // barre de gauche de Zara, produits de Steve, commentaires de Hajatiana
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

    // bouton "supprimer ce produit"
    if(isset($_POST["supprimer_produit"])){
        supprimer_produit();
        header("Location: suit_vue3.php");
        exit;
    }

    $boutique = get_info();

    if(isset($_POST["modifier_profil"])){
        modifier_profil();
    }

    if(isset($_POST["modifier_securite"])){
        modifier_securite();
    }

    // réponse du vendeur à un commentaire (même table que Hajatiana, au nom de la boutique)
    if(isset($_POST["contenu"])){
        $id_produit = (int) ($_POST["id_produit"] ?? 0);
        $contenu = trim($_POST["contenu"]);

        $req = $db->prepare("SELECT id FROM produits WHERE id = ? AND id_boutique = ?");
        $req->execute([$id_produit, $boutique["id"]]);

        if($req->fetch() && $contenu !== ""){
            $insert = $db->prepare("INSERT INTO commentaire (id_produit, nom, contenu) VALUES (?, ?, ?)");
            $insert->execute([$id_produit, $boutique["boutname"], $contenu]);
        }
        header("Location: suit_vue3.php");
        exit;
    }

    $produits = get_produits_boutique($boutique["id"]);
    $types = array_unique(array_column($produits, "type"));
    $logo = image_ou($boutique["logo"], "kara.jpg");

    // boutons "type" : ?type=... ne garde que ce type de produit
    $type_choisi = $_GET["type"] ?? "";
    $produits_affiches = $type_choisi === "" ? $produits : array_filter($produits, fn($p) => $p["type"] === $type_choisi);

    // commentaires des produits de la boutique
    $req = $db->prepare("SELECT c.*, COALESCE(u.nom, c.nom) AS auteur, u.photo AS photo_auteur
                         FROM commentaire c
                         JOIN produits p ON p.id = c.id_produit
                         LEFT JOIN users u ON u.id = c.id_user
                         WHERE p.id_boutique = ?
                         ORDER BY c.date_commentaire DESC");
    $req->execute([$boutique["id"]]);

    $com_by_produit = [];
    foreach ($req->fetchAll() as $c) {
        $com_by_produit[$c["id_produit"]][] = $c;
    }

    // commandes reçues, rangées par produit (colonne de droite de chaque produit)
    $commandes_par_produit = [];
    foreach (get_commandes_boutique($boutique["id"]) as $cmd) {
        foreach ($cmd["lignes"] as $l) {
            $commandes_par_produit[$l["id_produit"]][] = $cmd + ["quantite" => $l["quantite"]];
        }
    }
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
    <link rel="stylesheet" href="css_v3/detai_vue3.css?v=<?= filemtime("css_v3/detai_vue3.css") ?>">
    <link rel="stylesheet" href="css_v3/header_vue3.css?v=<?= filemtime("css_v3/header_vue3.css") ?>">
    <link rel="stylesheet" href="css_v3/tete_vue3.css?v=<?= filemtime("css_v3/tete_vue3.css") ?>">
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue3.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <!-- <li><a href="" class="gar"><i>langue</i></a></li> -->
                <li><a href="vue3.php#commandes" class="gar"><i>commandes</i></a></li>
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
                    <h1 class="v3_nom"><?= htmlspecialchars($boutique["boutname"]) ?></h1>
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
                        <!-- formulaire de la maquette, envoyé à l'API de Steve -->
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
                    <p class="vide">Aucun produit pour le moment</p>
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

    <section class="detail">
        <div class="det1">
            <div class="card_fondImage">
                <h1 class="nom_bout"><?= htmlspecialchars($boutique["boutname"]) ?></h1>
                <p class="desc_box"><?= htmlspecialchars($boutique["definition"]) ?>,<br><em>vous pouvez nous visiter à <b><?= htmlspecialchars($boutique["localisation"]) ?></b></em></p>
                <button class="retour"><a href="vue3.php">accueil</a></button>
                <img src="images/<?= htmlspecialchars($logo) ?>" alt="" class="fond">
                <hr class="fond_hr">

            </div>


            <div class="bar_but">
                <button class="but_type"><a href="suit_vue3.php"<?= $type_choisi === "" ? ' class="actif"' : '' ?>>tous</a></button>
                <?php foreach ($types as $t) : ?>
                    <button class="but_type"><a href="suit_vue3.php?type=<?= urlencode($t) ?>"<?= $type_choisi === $t ? ' class="actif"' : '' ?>><?= htmlspecialchars($t) ?></a></button>
                <?php endforeach; ?>
            </div>


            <div class="produit">

                <?php if (empty($produits_affiches)) : ?>
                    <p class="vide">Aucun produit pour le moment</p>
                <?php endif; ?>

                <?php foreach ($produits_affiches as $p) : ?>
                <div class="boit_pr">
                    <div class="box_prox">
                        <img src="images/<?= htmlspecialchars(image_ou($p["photo"], "produit.jpg")) ?>" alt="" class="img_pr">
                        <h2 class="nom_pr"><?= htmlspecialchars($p["nom"]) ?></h2>
                        <h6 class="desc_pr"><?= nl2br(htmlspecialchars($p["description"])) ?> <b><?= (int) $p["stock"] ?></b> pièces</h6>
                        <div class="bay_pr">
                            <h4 class="prix_pr"><?= number_format($p["prix"], 0, ',', ' ') ?> Ar</h4>
                        </div>

                        <div class="comt">
                            <form method="POST" class="div_com">
                                <input type="hidden" name="id_produit" value="<?= (int) $p["id"] ?>">
                                <textarea name="contenu" class="inp_com" placeholder="Répondre aux clients ..." required></textarea>
                                <button class="but_com" type="submit">></button>
                            </form>

                            <h4 class="pr_gros">Le prix pour plus de <b>5p</b> est <b><?= number_format($p["prix_gros"], 0, ',', ' ') ?> Ar</b>
                            </h4>
                        </div>
                    </div>

                    <!-- commentaires du produit (Hajatiana) -->
                    <div class="box_prox scrol">
                        <?php $liste = $com_by_produit[$p["id"]] ?? []; ?>
                        <?php if (empty($liste)) : ?>
                            <p class="vide">Pas encore de commentaire</p>
                        <?php endif; ?>

                        <?php foreach ($liste as $c) : ?>
                        <div class="com_acht">
                            <img src="images/<?= htmlspecialchars(image_ou($c["photo_auteur"], "pdp.jpg")) ?>" alt="" class="img_acht">
                            <div class="nd_acht">
                                <h3><?= htmlspecialchars($c["auteur"]) ?></h3>
                                <b>le <?= htmlspecialchars($c["date_commentaire"]) ?></b>
                            </div>
                            <p class="com_pra"><?= nl2br(htmlspecialchars($c["contenu"])) ?></p>
                        </div>
                        <?php endforeach; ?>

                    </div>

                    <!-- commandes de ce produit (colonne "comd_boit" de la maquette) -->
                    <div class="box_prox comd_boit scrol">
                        <?php $cmds = $commandes_par_produit[$p["id"]] ?? []; ?>
                        <?php if (empty($cmds)) : ?>
                            <p class="vide">Pas encore de commande</p>
                        <?php endif; ?>

                        <?php foreach ($cmds as $cmd) : ?>
                        <div class="comtts">
                            <div class="comt_noms">
                                <img src="images/<?= htmlspecialchars(image_ou($cmd["photo_client"], "pdp.jpg")) ?>" alt="" class="comt_img">
                                <h6 class="comt_nom"><span><?= htmlspecialchars($cmd["client"]) ?></span><b>le <?= htmlspecialchars($cmd["date_commande"]) ?> — <?= htmlspecialchars(STATUTS_COMMANDE[$cmd["statut"]] ?? $cmd["statut"]) ?></b></h6>
                            </div>
                            <p class="comt_dets">
                                Commande de
                                <b><?= (int) $cmd["quantite"] ?></b>
                                article(s), à livrer à
                                <i><?= htmlspecialchars($cmd["adresse_livraison"]) ?></i>
                                — joignable au
                                <b><?= htmlspecialchars($cmd["telephone"]) ?></b>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>


        </div>
    </section>

    <script src="ajax/produit_vendeur.js"></script>

</body>

</html>
