<?php
// Étoile ★ d'un produit (ajax/favori.js) : ajoute ou retire le produit des favoris
// et renvoie la nouvelle liste "favorite" de la barre de gauche
session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../functions/favori.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Connectez-vous pour ajouter des favoris"]);
    exit;
}

$id_user = (int) $_SESSION["user_id"];
$id_produit = (int) ($_POST["id_produit"] ?? 0);

$favori = basculer_favori($id_user, $id_produit);

ob_start();
afficher_favoris(get_favoris($id_user));
$liste = ob_get_clean();

echo json_encode([
    "success" => true,
    "favori" => $favori,
    "message" => $favori ? "Ajouté aux favoris" : "Retiré des favoris",
    "liste" => $liste,
], JSON_UNESCAPED_UNICODE);
?>