<?php

    // Nom d'une image du dossier images/ si elle existe, sinon une image par défaut
    // (évite les images cassées quand un fichier a été supprimé ou n'a jamais été envoyé)
    function image_ou($fichier , $defaut){

        if($fichier !== null && $fichier !== "" && is_file(__DIR__."/../images/".$fichier)){
            return $fichier ;
        }

        return $defaut ;
    }
?>