<?php
session_start();
require_once __DIR__."/db.php";
require_once __DIR__."/../utils/upload_file.php";
$db=connection_db_bou();

function inscription(){
    global $db;
    $boutname=$_POST["boutname"];
    $numTel=$_POST["numTel"];
    $email=$_POST["email"];
    $password=$_POST["password"];
    $password_hash=password_hash($password,PASSWORD_BCRYPT);
    $localisation=$_POST["localisation"];
    $definition=$_POST["definition"];

    $req_name=$db->prepare("SELECT boutname FROM inscription_vendeur WHERE boutname=?");
    $req_name->execute([$boutname]);
    $count_name=$req_name->rowCount();
    $logo=upload_file($_FILES["file"] ?? null);

    if($count_name==0){
        $req_num=$db->prepare("SELECT numTel FROM inscription_vendeur WHERE numTel=?");
        $req_num->execute([$numTel]);

        $req_email=$db->prepare("SELECT email FROM inscription_vendeur WHERE email=?");
        $req_email->execute([$email]);

        $req_lieu=$db->prepare("SELECT localisation FROM inscription_vendeur WHERE localisation=?");
        $req_lieu->execute([$localisation]);

        $req_defini=$db->prepare("SELECT definition FROM inscription_vendeur WHERE definition=?");
        $req_defini->execute([$definition]);


        $insert=$db->prepare("INSERT INTO inscription_vendeur(boutname,logo,numTel,
        email,password,localisation,definition) VALUE (?,?,?,?,?,?,?)");
        $insert->execute([$boutname,$logo,$numTel,$email,$password_hash,$localisation,$definition]);

        // connecter directement la nouvelle boutique (sinon vue3.php n'a pas d'id_boutique)
        $_SESSION["id_boutique"] = $db->lastInsertId();

        echo "vous êtes inscrit";
        header("location:vue3.php");

    }else{
        echo "Ce nom de boutique existe déjà";
    }

}

function get_info(){
    global $db;

    $id = $_SESSION["id_boutique"];

    $req = $db->prepare("SELECT * FROM inscription_vendeur WHERE id = ?");
    $req->execute([$id]);

    return $req->fetch();
}


        function connecter(){
            global $db;

            // connexion avec le nom de la boutique (unique) et le mot de passe
            $boutname = trim($_POST["boutname"] ?? "");
            $password = $_POST["password"] ?? "";

            $req = $db->prepare("SELECT * FROM inscription_vendeur WHERE boutname = ?");
            $req->execute([$boutname]);

            $boutique = $req->fetch();

            if($boutique && password_verify($password, $boutique["password"])){

                $_SESSION["id_boutique"] = $boutique["id"];

                header("Location: vue3.php");

            }else{
                // même message dans les deux cas : on ne dit pas si le nom existe
                echo "Nom de boutique ou mot de passe incorrect";
            }
        }

    function modifier_profil(){

    global $db;

    $id = $_SESSION["id_boutique"];

    $boutname = trim($_POST["boutname"]);
    $localisation = trim($_POST["localisation"]);

    // le nom de la boutique sert à se connecter : il doit rester unique
    if($boutname !== ""){
        $req = $db->prepare("SELECT id FROM inscription_vendeur WHERE boutname = ? AND id <> ?");
        $req->execute([$boutname, $id]);

        if($req->fetch()){
            echo "Ce nom de boutique est déjà utilisé";
            return;
        }
    }

    // "une seul ou toutes les informations" : un champ vide garde l'ancienne valeur
    $req = $db->prepare("UPDATE inscription_vendeur
                         SET boutname = IF(? = '', boutname, ?), localisation = IF(? = '', localisation, ?)
                         WHERE id = ?");

    $req->execute([$boutname, $boutname, $localisation, $localisation, $id]);

    header("Location: vue3.php");

    echo "Profil modifié avec succès";
}


    function modifier_securite(){

    global $db;

    $id = $_SESSION["id_boutique"];

    $numTel = $_POST["numTel"];
    $email = $_POST["email"];
    $ancien_password = $_POST["ancien_password"];
    $nouveau_password = $_POST["nouveau_password"];

    $req = $db->prepare("SELECT * FROM inscription_vendeur WHERE id = ?");
    $req->execute([$id]);

    $boutique = $req->fetch();

    if($boutique){

        if(password_verify($ancien_password, $boutique["password"])){

            $password_hash = password_hash($nouveau_password, PASSWORD_BCRYPT);

            $update = $db->prepare("UPDATE inscription_vendeur
                                    SET numTel = ?, email = ?, password = ?
                                    WHERE id = ?");

            $update->execute([
                $numTel,
                $email,
                $password_hash,
                $id
            ]);

             header("Location: vue3.php");

        }else{

            echo "Ancien mot de passe incorrect";

        }

    }
}

?>
