<?php
session_start();
require_once __DIR__ . "/config/server.php";

// ==============================
// CONNEXION À LA BASE DE DONNÉES
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

if ($connexion->connect_error) {
    die("Erreur de connexion à la base de données : " . $connexion->connect_error);
}

// ==============================
// VÉRIFIER LE FORMULAIRE
// ==============================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: connection.php");
    exit;
}

// Récupérer les données envoyées
$email = trim($_POST["email"] ?? "");
$mot_de_pass = $_POST["mot_de_pass"] ?? "";

// Vérifier que les champs sont remplis
if ($email === "" || $mot_de_pass === "") {
    die("Veuillez remplir tous les champs. <a href=\"connection.php\">retour</a>");
}

// ==============================
// CHERCHER L'UTILISATEUR
// ==============================

$sql = "SELECT id, nom, telephone, email, date_naissance, adresse, photo, mot_de_pass
        FROM users
        WHERE email = ?";

$requete = $connexion->prepare($sql);

if (!$requete) {
    die("Erreur SQL : " . $connexion->error);
}

$requete->bind_param("s", $email);
$requete->execute();

$resultat = $requete->get_result();

// Vérifier si l'utilisateur existe
if ($resultat->num_rows === 0) {
    $requete->close();
    $connexion->close();

    die("Email ou mot de passe incorrect. <a href=\"connection.php\">retour</a>");
}

$user = $resultat->fetch_assoc();

// ==============================
// VÉRIFIER LE MOT DE PASSE
// ==============================

if (!password_verify($mot_de_pass, $user["mot_de_pass"])) {
    $requete->close();
    $connexion->close();

    die("Email ou mot de passe incorrect. <a href=\"connection.php\">retour</a>");
}

// ==============================
// CONNEXION RÉUSSIE
// ==============================

// Enregistrer l'utilisateur dans la session
$_SESSION["user_id"] = $user["id"];
$_SESSION["nom"] = $user["nom"];
$_SESSION["email"] = $user["email"];
$_SESSION["telephone"] = $user["telephone"];
$_SESSION["date_naissance"] = $user["date_naissance"];
$_SESSION["adresse"] = $user["adresse"];
$_SESSION["photo"] = $user["photo"];

// Fermer la connexion
$requete->close();
$connexion->close();

// Aller vers la page d'accueil acheteur
header("Location: vue2.php");
exit;
?>
