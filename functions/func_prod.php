<?php
    require_once __DIR__."/db.php";
    require_once __DIR__."/../utils/upload_file.php" ;

    $db = connection_db_bou() ;

    function create_product(){
        global $db ;

        // le produit appartient à la boutique du vendeur connecté (session créée par Zara)
        if(!isset($_SESSION["id_boutique"])){
            return [
                "statut" => 401 ,
                "message" => "connectez-vous en tant que vendeur"
            ] ;
        }
        $id_boutique = $_SESSION["id_boutique"] ;

        $type = trim($_POST["type"] ?? "") ;
        $nom = trim($_POST["nom"] ?? "") ;
        $description = trim($_POST["description"] ?? "") ;
        $stock = $_POST["stock"] ?? "" ;
        $prix = $_POST["prix"] ?? "" ;
        $prix_gros = $_POST["prix_gros"] ?? "" ;

        // stock et prix doivent être des nombres (sinon MySQL refuse l'insertion)
        if($type === "" || $nom === "" || !is_numeric($stock) || !is_numeric($prix) || !is_numeric($prix_gros)){
            return [
                "statut" => 400 ,
                "message" => "remplissez tous les champs (stock et prix en chiffres)"
            ] ;
        }

        $photo = upload_file($_FILES["photo"] ?? null) ;

        $insert = $db->prepare("INSERT INTO produits (id_boutique , type , nom , description , photo ,stock , prix , prix_gros) VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)") ;
        $insert->execute([$id_boutique , $type , $nom , $description , $photo , $stock , $prix , $prix_gros]) ;

        return [
            "statut" => 201 ,
            "message" => "« $nom » ajouté : en attente de validation par l'administrateur"
        ] ;
    }

    // produits d'une boutique (liste "suprimer ce produit" de vue3.php / suit_vue3.php)
    function get_produits_boutique($id_boutique){
        global $db ;

        $select = $db->prepare("SELECT * FROM produits WHERE id_boutique = ? ORDER BY id DESC") ;
        $select->execute([$id_boutique]) ;

        return $select->fetchAll() ;
    }

    // bouton "suprimer ce produit" : seulement un produit de la boutique connectée
    function supprimer_produit(){
        global $db ;

        $id = (int) ($_POST["id_produit"] ?? 0) ;

        $delete = $db->prepare("DELETE FROM produits WHERE id = ? AND id_boutique = ?") ;
        $delete->execute([$id , $_SESSION["id_boutique"]]) ;
    }

?>