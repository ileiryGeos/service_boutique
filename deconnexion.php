<?php
// Déconnexion (acheteur et vendeur) : lien "déconnexion" de vue2.php, suit_vue2.php et vue3.php
session_start();
session_destroy();

header("Location: connection.php");
exit;
