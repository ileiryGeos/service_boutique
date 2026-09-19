<?php
    session_start() ;
    require __DIR__."/../functions/func_prod.php" ;

    $rep = create_product() ;

    echo json_encode($rep) ;
?>