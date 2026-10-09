<?php
// TABLEAU DE BORD DE L'ADMINISTRATEUR
//   - produits à valider (ils ne sont visibles par les acheteurs qu'après validation)
//   - ventes de toutes les boutiques
//   - frais de mise en vente gagnés, mois par mois
//   - comptes : boutiques et acheteurs
require_once "functions/admin.php";

verifier_admin();

// --- actions (boutons du tableau de bord) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = (int) ($_POST["id"] ?? 0);
    $resultat = null;

    if (isset($_POST["approuver"]))              { $resultat = approuver_produit($id); }
    elseif (isset($_POST["refuser"]))            { $resultat = refuser_produit($id); }
    elseif (isset($_POST["supprimer_produit"]))  { $resultat = supprimer_produit_admin($id); }
    elseif (isset($_POST["supprimer_boutique"])) { $resultat = supprimer_boutique($id); }
    elseif (isset($_POST["supprimer_acheteur"])) { $resultat = supprimer_acheteur($id); }
    elseif (isset($_POST["frais_payes"]))        { $resultat = marquer_frais_payes($id, true); }
    elseif (isset($_POST["frais_dus"]))          { $resultat = marquer_frais_payes($id, false); }
    elseif (isset($_POST["enregistrer_frais"]))  {
        $montant = $_POST["montant"] ?? "";
        if (is_numeric($montant) && $montant >= 0) {
            set_parametre("frais_mise_en_vente", (string) (float) $montant);
            $resultat = ["success" => true, "message" => "Frais de mise en vente enregistrés"];
        } else {
            $resultat = ["success" => false, "message" => "Montant invalide"];
        }
    }

    $_SESSION["message_admin"] = $resultat;

    header("Location: admin.php" . (isset($_POST["ancre"]) ? "#" . $_POST["ancre"] : ""));
    exit;
}

$message = $_SESSION["message_admin"] ?? null;
unset($_SESSION["message_admin"]);

$resume = get_resume_admin();
$a_valider = get_produits_par_statut("en_attente");
$refuses = get_produits_par_statut("refuse");
$en_ligne = get_produits_par_statut("approuve");
$boutiques = get_boutiques_admin();
$acheteurs = get_acheteurs_admin();
$ventes = get_ventes_admin();
$revenus = get_revenus_par_mois();
$frais = get_frais_admin();
$montant_frais = frais_mise_en_vente();

function ar($montant) {
    return number_format((float) $montant, 0, ',', ' ') . " Ar";
}

function mois_francais(string $mois): string {
    $noms = ["01" => "janvier", "02" => "février", "03" => "mars", "04" => "avril",
             "05" => "mai", "06" => "juin", "07" => "juillet", "08" => "août",
             "09" => "septembre", "10" => "octobre", "11" => "novembre", "12" => "décembre"];
    [$annee, $m] = explode("-", $mois);
    return ($noms[$m] ?? $m) . " " . $annee;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>administration — V-STORE</title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png">
    <link rel="apple-touch-icon" href="images/favicon-180.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css_admin/admin.css?v=<?= filemtime("css_admin/admin.css") ?>">
</head>

<body>

    <header class="barre_admin">
        <a href="vue1.php" class="marque_barre"><img src="images/logo.svg" alt="V-STORE"></a>
        <h1>administration</h1>
        <nav>
            <a href="#valider">à valider (<?= $resume["a_valider"] ?>)</a>
            <a href="#ventes">ventes</a>
            <a href="#revenus">revenus</a>
            <a href="#boutiques">boutiques</a>
            <a href="#acheteurs">acheteurs</a>
            <a href="vue1.php">voir le site</a>
            <a href="deconnexion.php">déconnexion</a>
        </nav>
        <span class="admin_nom"><?= htmlspecialchars($_SESSION["admin_nom"] ?? "") ?></span>
    </header>

    <main>

        <?php if ($message) : ?>
            <p class="message <?= $message["success"] ? "ok" : "erreur" ?>"><?= htmlspecialchars($message["message"]) ?></p>
        <?php endif; ?>

        <!-- ----------------------------------------------------------- -->
        <section class="chiffres">
            <div class="chiffre <?= $resume["a_valider"] > 0 ? "alerte" : "" ?>">
                <b><?= $resume["a_valider"] ?></b><span>produits à valider</span>
            </div>
            <div class="chiffre"><b><?= $resume["produits"] ?></b><span>produits en ligne</span></div>
            <div class="chiffre"><b><?= $resume["boutiques"] ?></b><span>boutiques</span></div>
            <div class="chiffre"><b><?= $resume["acheteurs"] ?></b><span>acheteurs</span></div>
            <div class="chiffre"><b><?= ar($resume["ventes_total"]) ?></b><span>ventes des boutiques</span></div>
            <div class="chiffre gain"><b><?= ar($resume["frais_total"]) ?></b><span>frais facturés</span></div>
            <div class="chiffre gain"><b><?= ar($resume["frais_encaisse"]) ?></b><span>frais encaissés</span></div>
            <div class="chiffre du"><b><?= ar($resume["frais_dus"]) ?></b><span>frais en attente</span></div>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="valider">
            <h2>produits à valider</h2>
            <p class="aide">Un produit n'est visible par les acheteurs qu'après validation.
               À la validation, les frais de mise en vente sont facturés à la boutique.</p>

            <?php if (empty($a_valider)) : ?>
                <p class="vide">Aucun produit en attente.</p>
            <?php endif; ?>

            <div class="grille">
                <?php foreach ($a_valider as $p) : ?>
                <article class="produit_admin">
                    <img src="images/<?= htmlspecialchars(image_ou($p["photo"], "produit.jpg")) ?>" alt="">
                    <div>
                        <h3><?= htmlspecialchars($p["nom"]) ?></h3>
                        <p class="boutique"><?= htmlspecialchars($p["boutname"]) ?> — <?= htmlspecialchars($p["type"]) ?></p>
                        <p class="desc"><?= nl2br(htmlspecialchars($p["description"])) ?></p>
                        <p class="prix"><?= ar($p["prix"]) ?> — stock <?= (int) $p["stock"] ?>
                           — gros <?= ar($p["prix_gros"]) ?></p>
                        <p class="date">ajouté le <?= htmlspecialchars($p["date_creation"]) ?></p>
                    </div>
                    <form method="POST" class="actions">
                        <input type="hidden" name="id" value="<?= (int) $p["id"] ?>">
                        <input type="hidden" name="ancre" value="valider">
                        <button name="approuver" class="bouton vert">valider et facturer <?= ar($montant_frais) ?></button>
                        <button name="refuser" class="bouton orange">refuser</button>
                        <button name="supprimer_produit" class="bouton rouge"
                            onclick="return confirm('Supprimer définitivement ce produit ?')">supprimer</button>
                    </form>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="produits">
            <h2>produits en ligne (<?= count($en_ligne) ?>) et refusés (<?= count($refuses) ?>)</h2>

            <table>
                <tr><th>produit</th><th>boutique</th><th>prix</th><th>stock</th><th>état</th><th></th></tr>
                <?php foreach (array_merge($en_ligne, $refuses) as $p) : ?>
                <tr>
                    <td><?= htmlspecialchars($p["nom"]) ?></td>
                    <td><?= htmlspecialchars($p["boutname"]) ?></td>
                    <td><?= ar($p["prix"]) ?></td>
                    <td><?= (int) $p["stock"] ?></td>
                    <td><span class="etat <?= htmlspecialchars($p["statut_validation"]) ?>"><?= htmlspecialchars(STATUTS_PRODUIT[$p["statut_validation"]] ?? "") ?></span></td>
                    <td class="actions_ligne">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= (int) $p["id"] ?>">
                            <input type="hidden" name="ancre" value="produits">
                            <?php if ($p["statut_validation"] === "approuve") : ?>
                                <button name="refuser" class="bouton orange petit">retirer du site</button>
                            <?php else : ?>
                                <button name="approuver" class="bouton vert petit">remettre en ligne</button>
                            <?php endif; ?>
                            <button name="supprimer_produit" class="bouton rouge petit"
                                onclick="return confirm('Supprimer définitivement ce produit ?')">supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="ventes">
            <h2>ventes de toutes les boutiques</h2>

            <?php if (empty($ventes)) : ?>
                <p class="vide">Aucune vente pour le moment.</p>
            <?php else : ?>

            <table>
                <tr><th>n°</th><th>date</th><th>boutique</th><th>client</th><th>montant</th><th>état</th></tr>
                <?php foreach ($ventes as $v) : ?>
                <tr>
                    <td><?= (int) $v["id"] ?></td>
                    <td><?= htmlspecialchars($v["date_commande"]) ?></td>
                    <td><?= htmlspecialchars($v["boutname"]) ?></td>
                    <td><?= htmlspecialchars($v["client"]) ?></td>
                    <td><?= ar($v["total"]) ?></td>
                    <td><span class="etat <?= htmlspecialchars($v["statut"]) ?>"><?= htmlspecialchars(STATUTS_COMMANDE[$v["statut"]] ?? $v["statut"]) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="revenus">
            <h2>revenus mois par mois</h2>
            <p class="aide">Les frais sont payés par les boutiques. Les acheteurs ne paient aucun frais.</p>

            <form method="POST" class="reglage">
                <input type="hidden" name="ancre" value="revenus">
                <label for="montant">Frais de mise en vente par produit :</label>
                <input type="number" name="montant" id="montant" min="0" step="100" value="<?= (int) $montant_frais ?>">
                <span>Ar</span>
                <button name="enregistrer_frais" class="bouton bleu">enregistrer</button>
            </form>

            <table>
                <tr><th>mois</th><th>produits validés</th><th>frais facturés</th><th>frais encaissés</th><th>ventes des boutiques</th><th>commandes</th></tr>
                <?php foreach ($revenus as $mois => $r) : ?>
                <tr>
                    <td><?= htmlspecialchars(mois_francais($mois)) ?></td>
                    <td><?= (int) $r["nb_frais"] ?></td>
                    <td class="gain"><?= ar($r["total"]) ?></td>
                    <td><?= ar($r["encaisse"]) ?></td>
                    <td><?= ar($r["ventes"]) ?></td>
                    <td><?= (int) $r["nb_commandes"] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($revenus)) : ?>
                    <tr><td colspan="6" class="vide">Aucun frais ni vente pour le moment.</td></tr>
                <?php endif; ?>
            </table>

            <h3>derniers frais facturés</h3>
            <table>
                <tr><th>date</th><th>boutique</th><th>produit</th><th>montant</th><th>payé</th></tr>
                <?php foreach ($frais as $f) : ?>
                <tr>
                    <td><?= htmlspecialchars($f["date_frais"]) ?></td>
                    <td><?= htmlspecialchars($f["boutname"]) ?></td>
                    <td><?= htmlspecialchars($f["nom_produit"]) ?></td>
                    <td><?= ar($f["montant"]) ?></td>
                    <td><?= $f["paye"] ? "oui" : "non" ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="boutiques">
            <h2>boutiques (vendeurs)</h2>

            <table>
                <tr><th>boutique</th><th>contact</th><th>produits</th><th>ventes</th><th>frais dus</th><th></th></tr>
                <?php foreach ($boutiques as $b) : ?>
                <tr>
                    <td>
                        <img src="images/<?= htmlspecialchars(image_ou($b["logo"], "kara.jpg")) ?>" alt="" class="mini">
                        <?= htmlspecialchars($b["boutname"]) ?>
                    </td>
                    <td><?= htmlspecialchars($b["email"]) ?><br><?= htmlspecialchars($b["numTel"]) ?></td>
                    <td><?= (int) $b["nb_produits"] ?>
                        <?php if ($b["nb_attente"] > 0) : ?><span class="etat en_attente"><?= (int) $b["nb_attente"] ?> à valider</span><?php endif; ?>
                    </td>
                    <td><?= ar($b["ventes"]) ?></td>
                    <td class="<?= $b["frais_dus"] > 0 ? "du" : "" ?>"><?= ar($b["frais_dus"]) ?></td>
                    <td class="actions_ligne">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= (int) $b["id"] ?>">
                            <input type="hidden" name="ancre" value="boutiques">
                            <?php if ($b["frais_dus"] > 0) : ?>
                                <button name="frais_payes" class="bouton vert petit">frais payés</button>
                            <?php else : ?>
                                <button name="frais_dus" class="bouton petit">remettre en attente</button>
                            <?php endif; ?>
                            <button name="supprimer_boutique" class="bouton rouge petit"
                                onclick="return confirm('Supprimer cette boutique ? Ses produits et ses commandes seront supprimés.')">supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- ----------------------------------------------------------- -->
        <section id="acheteurs">
            <h2>acheteurs (clients)</h2>
            <p class="aide">Les acheteurs n'ont aucun frais à payer.</p>

            <table>
                <tr><th>nom</th><th>contact</th><th>commandes</th><th>total acheté</th><th></th></tr>
                <?php foreach ($acheteurs as $a) : ?>
                <tr>
                    <td>
                        <img src="images/<?= htmlspecialchars(image_ou($a["photo"], "pdp.jpg")) ?>" alt="" class="mini ronde">
                        <?= htmlspecialchars($a["nom"]) ?>
                    </td>
                    <td><?= htmlspecialchars($a["email"]) ?><br><?= htmlspecialchars($a["telephone"]) ?></td>
                    <td><?= (int) $a["nb_commandes"] ?></td>
                    <td><?= ar($a["total_achats"]) ?></td>
                    <td class="actions_ligne">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= (int) $a["id"] ?>">
                            <input type="hidden" name="ancre" value="acheteurs">
                            <button name="supprimer_acheteur" class="bouton rouge petit"
                                onclick="return confirm('Supprimer ce compte acheteur ?')">supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

    </main>

</body>

</html>
