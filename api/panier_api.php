<?php
// Bouton "buy" (ajax/panier.js) : ajoute un produit au panier de l'acheteur connecté
session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../functions/panier.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Connectez-vous pour acheter"]);
    exit;
}

$id_user = (int) $_SESSION["user_id"];
$id_produit = (int) ($_POST["id_produit"] ?? 0);

$resultat = ajouter_panier($id_user, $id_produit);
$resultat["nombre"] = nombre_articles_panier($id_user);

echo json_encode($resultat, JSON_UNESCAPED_UNICODE);
?>