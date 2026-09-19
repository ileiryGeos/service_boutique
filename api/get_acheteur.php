<?php

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger la fonction
require_once __DIR__ . "/../functions/acheteur.php";

// Indiquer que la réponse est du JSON
header("Content-Type: application/json; charset=UTF-8");

// Récupérer l'acheteur connecté
$info_acheteur = get_acheteur();

// Envoyer la réponse JSON
echo json_encode(
    $info_acheteur,
    JSON_UNESCAPED_UNICODE
);
?>
