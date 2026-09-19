<?php
// Bouton × de l'historique (ajax/historique.js) : retire un achat de l'historique
// et renvoie la nouvelle liste "histior" de la barre de gauche
session_start();

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../functions/historique.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Connectez-vous"]);
    exit;
}

$id_user = (int) $_SESSION["user_id"];

retirer_historique($id_user, (int) ($_POST["id_ligne"] ?? 0));

ob_start();
afficher_historique(get_historique($id_user));
$liste = ob_get_clean();

echo json_encode(["success" => true, "liste" => $liste], JSON_UNESCAPED_UNICODE);
?>