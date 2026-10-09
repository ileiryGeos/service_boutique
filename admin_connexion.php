<?php
// Connexion de l'ADMINISTRATEUR (séparée des acheteurs et des vendeurs)
require_once "functions/admin.php";

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (connecter_admin($_POST["email"] ?? "", $_POST["mot_de_passe"] ?? "")) {
        header("Location: admin.php");
        exit;
    }

    $erreur = "Email ou mot de passe incorrect";
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

<body class="page_connexion_admin">

    <form method="POST" class="carte_connexion">
        <img src="images/logo.svg" alt="" class="marque_admin">
        <h1>administration</h1>
        <p class="sous_titre">V-STORE</p>

        <?php if ($erreur !== "") : ?>
            <p class="message erreur"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" required autofocus>

        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required>

        <button type="submit">se connecter</button>

        <a href="connection.php" class="lien_retour">← retour au site</a>
    </form>

</body>

</html>
