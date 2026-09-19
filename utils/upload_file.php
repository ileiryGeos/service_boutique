<?php

    function upload_file($file){

        // aucun fichier choisi : pas d'image (image par défaut à l'affichage, voir utils/image.php)
        if($file === null || $file["error"] !== UPLOAD_ERR_OK){
            return "" ;
        }

        $type = $file["type"] ;
        // echo $type ;

        $tab_type = explode("/" , $type) ;
        // var_dump($tab_type);
        $extension = $tab_type[1] ?? "jpg" ;

        $rand = rand(100000000 , 1000000000) ;

        $filename = uniqid("IMG_") . "_" . $rand . "." . $extension;

        // __DIR__ : fonctionne depuis api/ (Steve) et depuis la racine (inscription.php de Zara)
        move_uploaded_file($file["tmp_name"] , __DIR__."/../images/$filename") ;

        return $filename ;
    }
?>