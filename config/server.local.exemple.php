<?php
    // MODÈLE pour mettre le site en ligne.
    //
    // 1. Copier ce fichier sous le nom  config/server.local.php
    // 2. Remplacer les valeurs par celles de l'hébergeur
    //    (InfinityFree : panneau de contrôle -> MySQL Databases)
    // 3. Envoyer server.local.php sur l'hébergeur par FTP
    //
    // Ne jamais mettre server.local.php sur GitHub : il contient le mot de passe.

    const DB_NAME = "epiz_00000000_service_boutique" ;   // "MySQL DB Name"
    const DB_HOST = "sqlXXX.infinityfree.com" ;          // "MySQL Hostname"
    const DB_USERNAME = "epiz_00000000" ;                // "MySQL User Name"
    const DB_PASSWORD = "votre_mot_de_passe" ;           // mot de passe du compte
    const DB_PORT = 3306 ;

?>