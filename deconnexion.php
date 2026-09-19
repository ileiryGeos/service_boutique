<?php
// Déconnexion (acheteur et vendeur) : lien "deconnection" de vue2.php, suit_vue2.php et vue3.php
session_start();
session_destroy();

header("Location: connection.php");
exit;
