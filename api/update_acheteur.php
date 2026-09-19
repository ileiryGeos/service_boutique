<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../functions/acheteur.php";

$resultat = modifier_acheteur();

if ($resultat["success"]) {

    // Récupérer les nouvelles données après modification
    $profil = get_acheteur();

    if ($profil["success"]) {
        $resultat["acheteur"] = $profil["acheteur"];

        // Garder la session à jour (affichage initial de vue2.php / suit_vue2.php)
        $_SESSION["nom"] = $profil["acheteur"]["name"];
        $_SESSION["telephone"] = $profil["acheteur"]["number"];
        $_SESSION["email"] = $profil["acheteur"]["email"];
        $_SESSION["date_naissance"] = $profil["acheteur"]["birthdate"];
        $_SESSION["adresse"] = $profil["acheteur"]["address"];
        $_SESSION["photo"] = $profil["acheteur"]["pdc"];
    }
}

echo json_encode($resultat);

?>
