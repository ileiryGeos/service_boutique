<?php
    require_once __DIR__."/../config/server.php" ;

    // Connexion PDO unique (remplace functions/connection.php de Steve,
    // functions/db.php de Zara et de Karine, et connex/connection.php de Hajatiana)
    function connection_db_bou (){
        try{

            $db_name = DB_NAME ;
            $db_host = DB_HOST ;
            $db_username = DB_USERNAME ;
            $db_password = DB_PASSWORD ;

            // Php Data Object :
            $db_bout = new PDO("mysql:dbname=$db_name;host=$db_host;port=" . DB_PORT , "$db_username" ,"$db_password" );
            $db_bout->exec("SET NAMES utf8mb4");
            return $db_bout;

        }catch(PDOException $error){
            echo "connection failed : " .$error->getMessage();
        }

    }
?>