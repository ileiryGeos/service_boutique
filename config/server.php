<?php
    // Configuration UNIQUE de la base de données pour tout le projet
    // (utilisée par PDO dans functions/db.php et par mysqli dans connexion.php / profil.php)
    //
    // EN LIGNE (InfinityFree, autre hébergeur) : ne pas modifier ce fichier.
    // Copier config/server.local.exemple.php en config/server.local.php et y mettre
    // les identifiants de l'hébergeur. S'il existe, ce fichier est utilisé à la place
    // des valeurs ci-dessous. Il n'est pas envoyé sur GitHub (voir .gitignore),
    // pour ne pas publier le mot de passe de la base.

    if (file_exists(__DIR__ . "/server.local.php")) {
        require_once __DIR__ . "/server.local.php";
    }

    // valeurs pour WampServer (ordinateur de développement),
    // utilisées seulement si server.local.php ne les a pas déjà définies
    if (!defined("DB_NAME"))     { define("DB_NAME", "service_boutique"); }
    if (!defined("DB_HOST"))     { define("DB_HOST", "localhost"); }
    if (!defined("DB_USERNAME")) { define("DB_USERNAME", "root"); }
    if (!defined("DB_PASSWORD")) { define("DB_PASSWORD", ""); }
    if (!defined("DB_PORT"))     { define("DB_PORT", 3306); }   // 3306 = MySQL de WAMP (3307 = MariaDB)

?>