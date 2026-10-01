<?php
    session_start() ;
    require __DIR__."/../functions/db.php" ;

    $db = connection_db_bou() ;

    // seulement les produits de la boutique du vendeur connecté
    $select = $db->prepare("SELECT type , nom , description , photo , statut_validation FROM produits WHERE id_boutique = ?") ;
    $select->execute([$_SESSION["id_boutique"] ?? 0]) ;
    $product = $select->fetchAll() ;

    echo json_encode($product) ;
?>