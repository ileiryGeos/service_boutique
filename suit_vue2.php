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

$db = connection_db_bou();

// boutique affichée (lien depuis vue2.php : suit_vue2.php?id=...)
$id_boutique = (int) ($_GET["id"] ?? 0);


// --- Ajout d'un commentaire (Hajatiana) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produit = isset($_POST['id_produit']) ? (int)$_POST['id_produit'] : 0;
    $contenu    = trim($_POST['contenu'] ?? '');

    // l'auteur est l'acheteur connecté (plus besoin de taper son nom)
    if ($id_produit > 0 && $contenu !== '') {
        $stmt = $db->prepare(
            "INSERT INTO commentaire (id_produit, id_user, nom, contenu) VALUES (:p, :u, :n, :c)"
        );
        $stmt->execute([
            ':p' => $id_produit,
            ':u' => (int) $_SESSION['user_id'],
            ':n' => $_SESSION['nom'] ?? 'anonyme',
            ':c' => $contenu,
        ]);
    }
    // Redirection pour eviter la re-soumission du formulaire
    header('Location: suit_vue2.php?id=' . $id_boutique);
    exit;
}

// --- Boutique et ses produits (Karine) ---
$boutique = couverture_boutique();
$produits = get_info_detail();

// boutons "type" : tous les types de la boutique, puis filtre ?type=...
$types = array_unique(array_column($produits, "type"));
$type_choisi = $_GET["type"] ?? "";
$produits = filtrer_par_type($produits);

// --- Commentaires des produits (Hajatiana) ---
// nom et photo de l'auteur lus dans la table users (sinon : nom de la boutique qui a répondu)
$req = $db->prepare(
    "SELECT c.*, p.nom AS nom_produit,
            COALESCE(u.nom, c.nom) AS auteur, u.photo AS photo_auteur
     FROM commentaire c
     JOIN produits p ON p.id = c.id_produit
     LEFT JOIN users u ON u.id = c.id_user
     WHERE p.id_boutique = ?
     ORDER BY c.date_commentaire DESC"
);
$req->execute([$id_boutique]);
$commentaires = $req->fetchAll(PDO::FETCH_ASSOC);

$com_by_produit = [];
foreach ($commentaires as $c) {
    $com_by_produit[$c['id_produit']][] = $c;
}

// Affichage initial du profil depuis la session (Daddy)
$nom_acheteur = $_SESSION["nom"] ?? "";
$nb_panier = nombre_articles_panier((int) $_SESSION["user_id"]);
$favoris = get_favoris((int) $_SESSION["user_id"]);
$historique = get_historique((int) $_SESSION["user_id"]);
$ids_favoris = array_map("intval", array_column($favoris, "id_produit"));
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
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>boîte</title>
    <link rel="stylesheet" href="css_v2/detai_vue2.css?v=<?= filemtime("css_v2/detai_vue2.css") ?>">
    <link rel="stylesheet" href="css_v2/header_vue2.css?v=<?= filemtime("css_v2/header_vue2.css") ?>">
    <link rel="stylesheet" href="css_v2/tete_vue2.css?v=<?= filemtime("css_v2/tete_vue2.css") ?>">
    <!-- <link rel="stylesheet" href="css_v2/boutic.css"> -->
</head>

<body>
    <header id="tete1">
        <nav>
            <a href="vue2.php" id="nom"><img src="images/logo.jpg" alt=""></a>
            <ul>
                <!-- <li><a href="" class="gar"><i>langue</i></a></li> -->
                <li><a href="vue2.php" class="gar"><i>accueil</i></a></li>
                <li><a href="panier.php" class="gar"><i>panier (<span class="nb_panier"><?= $nb_panier ?></span>)</i></a></li>
                <li><a href="" class="gar"><i>aide et support</i></a></li>
                <li><a href="deconnexion.php" class="gar"><i>déconnexion</i></a></li>
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
                <h1 class="ah_nom"><?php echo e($nom_acheteur); ?></h1>
                <!-- <input type="file" class="ah_but_nom"> -->
            </div>

            <div class="toust">
                <div class="info_acheteur">
                    <h6 class="tou_ve">email : <b><?php echo e($email); ?></b></h6>
                    <h6 class="tou_ve">N° : <b><?php echo e($telephone); ?></b></h6>
                    <h6 class="tou_ve">date de naissance : <b><?php echo e($date_naissance); ?></b></h6>
                    <h6 class="tou_ve">adresse : <b><?php echo e($adresse); ?></b></h6>
                    <!-- <h6 class="tou_ve">solde : <b>99 000ar</b></h6> -->
                </div>

                <div class="modifier">
                    <button type="button" class="afir_modi but_ouvrir_modif" aria-expanded="false">Modifier votre profil</button>
                    <form class="form_modi" id="form_modi">
                        <label>Vous pouvez modifier une ou plusieurs informations</label>
                        <input type="text" placeholder="Nouveau nom ..." name="name" class="inp_modif">
                        <input type="text" placeholder="Nouvelle adresse ..." name="address" class="inp_modif">
                        <input type="date" name="birthdate" class="inp_modif">
                        <label>Votre sécurité</label>
                        <input type="number" placeholder="Nouveau numéro ..." name="number" class="inp_modif">
                        <input type="email" placeholder="Nouvelle adresse email ..." name="email" class="inp_modif">
                        <input type="password" placeholder="Mot de passe actuel ..." name="old_password" class="inp_modif">
                        <input type="password" placeholder="Nouveau mot de passe ..." name="new_password" class="inp_modif">
                        <button type="submit" class="but_mode">Modifier</button>
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

    <section class="detail">
        <div class="det1">
            <?php foreach($boutique as $b): ?>
            <div class="card_fondImage">
                <h1 class="nom_bout"><?= htmlspecialchars($b["name"]) ?></h1>
                <p class="desc_box"><?= htmlspecialchars($b["description"]) ?>,<br><em>vous pouvez nous visiter à <b><?= htmlspecialchars($b["lieu"]) ?></b></em></p>
                <button class="retour" onclick="location.href='vue2.php'"><a href="vue2.php">accueil</a></button>
                <img src="images/<?= htmlspecialchars(image_ou($b["pdc"], "kara.jpg")) ?>" alt="" class="fond">
                <hr class="fond_hr">

            </div>
            <?php endforeach; ?>

            <div class="bar_but">
                <button class="but_type"><a href="suit_vue2.php?id=<?= $id_boutique ?>"<?= $type_choisi === "" ? ' style="font-weight:bold;"' : '' ?>>tous</a></button>
                <?php foreach ($types as $t) : ?>
                    <button class="but_type"><a href="suit_vue2.php?id=<?= $id_boutique ?>&amp;type=<?= urlencode($t) ?>"<?= $type_choisi === $t ? ' style="font-weight:bold;"' : '' ?>><?= htmlspecialchars($t) ?></a></button>
                <?php endforeach; ?>
            </div>

            <div class="produit">

                <?php if (empty($produits)) : ?>
                    <h2 style="width:100%;text-align:center;color:blueviolet;">Aucun produit pour le moment</h2>
                <?php endif; ?>

                <?php foreach ($produits as $p) : ?>
                    <div class="box_prox">
                        <img src="images/<?= htmlspecialchars(image_ou($p['photo'], 'produit.jpg')) ?>" alt="" class="img_pr">
                        <h2 class="nom_pr"><?= htmlspecialchars($p['nom']) ?></h2>
                        <h6 class="desc_pr"><?= nl2br(htmlspecialchars($p['description'])) ?> <b
                                style="color: midnightblue;font-size: 1.5vw;font-weight: bold;"><?= (int)$p['stock'] ?></b> pièces</h6>
                        <div class="bay_pr">
                            <h4 class="prix_pr"><?= number_format($p['prix'], 0, ',', ' ') ?> Ar</h4>
                            <?php if ($p["stock"] > 0) : ?>
                                <button type="button" class="butbay" data-produit="<?= (int)$p['id'] ?>">buy</button>
                            <?php else : ?>
                                <button type="button" class="butbay" disabled>épuisé</button>
                            <?php endif; ?>
                        </div>
                        <?php $en_favori = in_array((int) $p["id"], $ids_favoris, true); ?>
                        <button type="button" class="fv<?= $en_favori ? " en_favori" : "" ?>" data-produit="<?= (int) $p["id"] ?>"
                            title="<?= $en_favori ? "retirer des favoris" : "ajouter aux favoris" ?>"><i class="fvr"></i></button>

                        <div class="comt">
                            <form action="suit_vue2.php?id=<?= $id_boutique ?>" method="POST" class="div_com">
                                <input type="hidden" name="id_produit" value="<?= (int)$p['id'] ?>">

                                <textarea
                                    name="contenu"
                                    class="inp_com"
                                    placeholder="Qu'en pensez-vous ?"
                                    required
                                ></textarea>

                                <button class="but_envoyer" type="submit">envoyer</button>
                            </form>
                            <h4 class="pr_gros">Le prix pour plus de <b>5p</b> est <b><?= number_format($p['prix_gros'], 0, ',', ' ') ?> Ar</b></h4>
                        </div>

                        <div class="comments">
                            <h4 class="coment_af">Commentaires</h4>
                            <div class="boit_coments">

                                <?php $liste = $com_by_produit[$p['id']] ?? []; ?>
                                <?php if (empty($liste)) : ?>
                                    <p style="width:100%;text-align:center;color:grey;">Soyez le premier à commenter</p>
                                <?php endif; ?>

                                <?php foreach ($liste as $c) : ?>
                                    <div class="com_acht">
                                        <img src="images/<?= htmlspecialchars(image_ou($c['photo_auteur'], 'pdp.jpg')) ?>" alt="" class="img_acht">
                                        <div class="nd_acht">
                                            <h3><?= htmlspecialchars($c['auteur']) ?></h3>
                                            <b>le <?= htmlspecialchars($c['date_commentaire']) ?></b>
                                            <button>like</button>
                                        </div>
                                        <p class="com_pra"><?= nl2br(htmlspecialchars($c['contenu'])) ?></p>
                                    </div>
                                <?php endforeach; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

    <script src="ajax/acheteur.js"></script>
    <script src="ajax/favori.js"></script>
    <script src="ajax/historique.js"></script>
    <script src="ajax/panier.js"></script>

</body>

</html>
