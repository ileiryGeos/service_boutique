<?php
// Libellés des états de validation d'un produit.
// Utilisés par les pages vendeur (vue3.php, suit_vue3.php) et par
// le tableau de bord de l'administrateur (functions/admin.php).

if (!defined("STATUTS_PRODUIT")) {

    define("STATUTS_PRODUIT", [
        "en_attente" => "en attente de validation",
        "approuve"   => "en ligne",
        "refuse"     => "refusé",
    ]);
}
?>