<?php
// Inscription d'un ACHETEUR (partie de Daddy)
require_once __DIR__ . "/config/server.php";

// ==============================
// CONNEXION À MYSQL
// ==============================

$serveur = DB_HOST;
$utilisateur = DB_USERNAME;
$mot_de_passe_bdd = DB_PASSWORD;
$base_de_donnees = DB_NAME;

$connexion = new mysqli(
    $serveur,
    $utilisateur,
    $mot_de_passe_bdd,
    $base_de_donnees,
    DB_PORT
);

// Vérifier la connexion
if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données : " . $connexion->connect_error);
}


// ==============================
// TRAITEMENT DU FORMULAIRE
// ==============================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupération des données
    $nom = trim($_POST["nom"]);
    $telephone = trim($_POST["telephone"]);
    $email = trim($_POST["email"]);
    $date_naissance = $_POST["date_naissance"];
    $adresse = trim($_POST["adresse"]);
    $mot_de_passe = $_POST["mot_de_passe"];
    $confirmation = $_POST["confirmation"];

    // ==============================
    // VÉRIFICATION DU MOT DE PASSE
    // ==============================

    if ($mot_de_passe !== $confirmation) {
        die("Erreur : les deux mots de passe ne sont pas identiques. <a href=\"profil.php\">retour</a>");
    }


    // ==============================
    // VÉRIFIER SI L'EMAIL EXISTE
    // ==============================

    $verification = $connexion->prepare(
        "SELECT id FROM users WHERE email = ?"
    );

    $verification->bind_param("s", $email);
    $verification->execute();
    $verification->store_result();

    if ($verification->num_rows > 0) {
        die("Erreur : cette adresse email existe déjà. <a href=\"profil.php\">retour</a>");
    }

    $verification->close();


    // ==============================
    // MOT DE PASSE SÉCURISÉ
    // ==============================

    $mot_de_passe_hash = password_hash(
        $mot_de_passe,
        PASSWORD_DEFAULT
    );


    // ==============================
    // PHOTO
    // ==============================

    $photo = "";

    if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] == 0) {

        // même dossier que toutes les autres images du projet
        $dossier = "images/";

        // Créer le dossier s'il n'existe pas
        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }

        $nom_photo = basename($_FILES["photo"]["name"]);

        $extension = strtolower(
            pathinfo($nom_photo, PATHINFO_EXTENSION)
        );

        $extensions_autorisees = [
            "jpg",
            "jpeg",
            "png",
            "gif"
        ];

        if (!in_array($extension, $extensions_autorisees)) {
            die("Erreur : format de photo non autorisé. <a href=\"profil.php\">retour</a>");
        }

        $nouveau_nom = uniqid() . "." . $extension;

        $chemin_photo = $dossier . $nouveau_nom;

        if (move_uploaded_file(
            $_FILES["photo"]["tmp_name"],
            $chemin_photo
        )) {

            // on enregistre seulement le nom du fichier (comme les autres parties)
            $photo = $nouveau_nom;

        } else {

            die("Erreur lors de l'enregistrement de la photo.");
        }
    }


    // ==============================
    // ENREGISTRER L'UTILISATEUR
    // ==============================

    $sql = "INSERT INTO users
            (nom, telephone, email, date_naissance, adresse, photo, mot_de_pass)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $requete = $connexion->prepare($sql);

    if (!$requete) {
        die("Erreur SQL : " . $connexion->error);
    }

    $requete->bind_param(
        "sssssss",
        $nom,
        $telephone,
        $email,
        $date_naissance,
        $adresse,
        $photo,
        $mot_de_passe_hash
    );


    // ==============================
    // RÉSULTAT
    // ==============================

    if ($requete->execute()) {

        echo "<h2>Inscription réussie !</h2>";
        echo "<p>Votre compte a été créé avec succès.</p>";

        echo '<a href="connection.php">Se connecter</a>';

    } else {

        echo "Erreur lors de l'inscription : "
             . $requete->error;
    }

    $requete->close();
}

$connexion->close();

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>créer mon compte — V-STORE</title>
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png">
    <link rel="apple-touch-icon" href="images/favicon-180.png">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css/profil.css?v=<?= filemtime("css/profil.css") ?>">

</head>


<body>


<section class="sect-one">

    <img src="images/logo.svg" alt="V-STORE" class="marque_form">
    <h2>Inscrivez-vous</h2>


    <!-- FORMULAIRE -->

    <form
        action="profil.php"
        method="POST"
        enctype="multipart/form-data"
    >


        <!-- NOM -->

        <label for="nom">
            Nom :
        </label>

        <input
            type="text"
            name="nom"
            id="nom"
            required
        >


        <!-- TELEPHONE -->

        <label for="telephone">
            Téléphone :
        </label>

        <input
            type="tel"
            name="telephone"
            id="telephone"
            required
        >


        <!-- EMAIL -->

        <label for="email">
            Email :
        </label>

        <input
            type="email"
            name="email"
            id="email"
            required
        >


        <!-- DATE DE NAISSANCE -->

        <label for="date_naissance">
            Date de naissance :
        </label>

        <input
            type="date"
            name="date_naissance"
            id="date_naissance"
            required
        >


        <!-- ADRESSE -->

        <label for="adresse">
            Adresse :
        </label>

        <input
            type="text"
            name="adresse"
            id="adresse"
            required
        >


        <!-- PHOTO -->

        <label for="photo">
            Photo de profil :
        </label>

        <input
            type="file"
            name="photo"
            id="photo"
            accept=".jpg,.jpeg,.png,.gif"
        >


        <!-- MOT DE PASSE -->

        <label for="mot_de_passe">
            Mot de passe :
        </label>

        <input
            type="password"
            name="mot_de_passe"
            id="mot_de_passe"
            required
        >


        <!-- CONFIRMATION -->

        <label for="confirmation">
            Confirmer le mot de passe :
        </label>

        <input
            type="password"
            name="confirmation"
            id="confirmation"
            required
        >


        <!-- BOUTON -->

        <button type="submit">
            Envoyer
        </button>

        <p><a href="connection.php">déjà un compte ? se connecter</a></p>


    </form>

</section>


</body>

</html>
