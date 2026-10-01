<?php

require_once __DIR__ . "/db.php";

$db = connection_db_bou();

require_once __DIR__ . "/../utils/upload_file.php";



/*
 * Tables utilisées après la fusion :
 *   profil_acheteur -> users               (Daddy)
 *   boutique        -> inscription_vendeur (Zara)
 *   detail          -> produits            (Steve)
 *   comments        -> commentaire         (Hajatiana, géré dans suit_vue2.php)
 */


   /**
 * Récupérer l'acheteur actuellement connecté
 */
function get_acheteur()
{
    global $db;

    // Démarrer la session
   if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier si un acheteur est connecté (session créée par connexion.php)
    if (!isset($_SESSION["user_id"])) {
        return [
            "success" => false,
            "message" => "Aucun acheteur connecté"
        ];
    }

    // Récupérer l'ID depuis la session
   $id = intval($_SESSION["user_id"]);

    if ($id <= 0) {
        return [
            "success" => false,
            "message" => "ID de l'acheteur invalide"
        ];
    }

    // Requête SQL
    $req = $db->prepare("
        SELECT
            id,
            nom AS name,
            telephone AS number,
            email,
            photo AS pdc,
            date_naissance AS birthdate,
            adresse AS address
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $req->execute([$id]);

    // Récupérer l'acheteur
    $acheteur = $req->fetch(PDO::FETCH_ASSOC);

    // Vérifier si l'acheteur existe
    if (!$acheteur) {
        return [
            "success" => false,
            "message" => "Acheteur introuvable"
        ];
    }

    return [
        "success" => true,
        "acheteur" => $acheteur
    ];
}


/**
 * Modifier le profil de l'acheteur connecté
 */
function modifier_acheteur()
{
    global $db;

    // Démarrer la session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier la connexion
    if (!isset($_SESSION["user_id"])) {
        return [
            "success" => false,
            "message" => "Vous devez être connecté"
        ];
    }

    // ID de l'acheteur connecté
    $id = intval($_SESSION["user_id"]);

    if ($id <= 0) {
        return [
            "success" => false,
            "message" => "ID de l'acheteur invalide"
        ];
    }


    // =====================================
    // RÉCUPÉRER LES DONNÉES DU FORMULAIRE
    // =====================================

    $name = trim($_POST["name"] ?? "");
    $number = trim($_POST["number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $birthdate = trim($_POST["birthdate"] ?? "");
    $address = trim($_POST["address"] ?? "");

    $old_password = $_POST["old_password"] ?? "";
    $new_password = $_POST["new_password"] ?? "";


    // =====================================
    // RÉCUPÉRER L'ANCIEN PROFIL
    // =====================================

    $req = $db->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $req->execute([$id]);

    $acheteur = $req->fetch(PDO::FETCH_ASSOC);

    if (!$acheteur) {
        return [
            "success" => false,
            "message" => "Acheteur introuvable"
        ];
    }


    // =====================================
    // PRÉPARER LES MODIFICATIONS
    // =====================================

    $champs = [];
    $valeurs = [];


    // Nom
    if ($name !== "") {
        $champs[] = "nom = ?";
        $valeurs[] = $name;
    }


    // Numéro
    if ($number !== "") {

        if (!ctype_digit($number)) {
            return [
                "success" => false,
                "message" => "Numéro de téléphone invalide"
            ];
        }

        // telephone est un VARCHAR : on garde le 0 du début
        $champs[] = "telephone = ?";
        $valeurs[] = $number;
    }


    // Email
    if ($email !== "") {

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                "success" => false,
                "message" => "Adresse email invalide"
            ];
        }

        $champs[] = "email = ?";
        $valeurs[] = $email;
    }


    // Date de naissance
    if ($birthdate !== "") {

        $date = DateTime::createFromFormat("Y-m-d", $birthdate);

        if (!$date || $date->format("Y-m-d") !== $birthdate) {
            return [
                "success" => false,
                "message" => "Date de naissance invalide"
            ];
        }

        $champs[] = "date_naissance = ?";
        $valeurs[] = $birthdate;
    }


    // Adresse
    if ($address !== "") {
        $champs[] = "adresse = ?";
        $valeurs[] = $address;
    }


    // =====================================
    // MODIFICATION DU MOT DE PASSE
    // =====================================

    if ($new_password !== "") {

        if ($old_password === "") {
            return [
                "success" => false,
                "message" => "Veuillez entrer votre ancien mot de passe"
            ];
        }

        /*
         * Vérification de l'ancien mot de passe.

         */
        if (!password_verify($old_password, $acheteur["mot_de_pass"])) {
            return [
                "success" => false,
                "message" => "Ancien mot de passe incorrect"
            ];
        }

        if (strlen($new_password) < 4) {
            return [
                "success" => false,
                "message" => "Le nouveau mot de passe est trop court"
            ];
        }

        $password_hash = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );

        $champs[] = "mot_de_pass = ?";
        $valeurs[] = $password_hash;
    }


    // =====================================
    // IMAGE DE PROFIL
    // =====================================

    if (
        isset($_FILES["pdc"]) &&
        $_FILES["pdc"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        // Vérifier l'erreur d'upload
        if ($_FILES["pdc"]["error"] !== UPLOAD_ERR_OK) {
            return [
                "success" => false,
                "message" => "Erreur lors de l'envoi de l'image"
            ];
        }

        $image = $_FILES["pdc"];


        // Taille maximale : 5 Mo
        if ($image["size"] > 5 * 1024 * 1024) {
            return [
                "success" => false,
                "message" => "Image trop grande. Maximum 5 Mo"
            ];
        }


        // Vérifier le vrai type MIME
        $types_autorises = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp"
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $image["tmp_name"]);
        finfo_close($finfo);


        if (!isset($types_autorises[$mime])) {
            return [
                "success" => false,
                "message" => "Format d'image non autorisé"
            ];
        }

        $extension = $types_autorises[$mime];


        // Nouveau nom unique
        $nouveau_nom =
            bin2hex(random_bytes(16))
            . "."
            . $extension;


        // Dossier images
        $dossier_images = __DIR__ . "/../images/";


        // Créer le dossier s'il n'existe pas
        if (!is_dir($dossier_images)) {

            if (!mkdir($dossier_images, 0755, true)) {
                return [
                    "success" => false,
                    "message" => "Impossible de créer le dossier images"
                ];
            }
        }


        // Déplacer l'image
        if (
            !move_uploaded_file(
                $image["tmp_name"],
                $dossier_images . $nouveau_nom
            )
        ) {
            return [
                "success" => false,
                "message" => "Impossible d'enregistrer l'image"
            ];
        }


        // Ajouter l'image aux modifications
        $champs[] = "photo = ?";
        $valeurs[] = $nouveau_nom;
    }


    // =====================================
    // AUCUNE MODIFICATION
    // =====================================

    if (empty($champs)) {
        return [
            "success" => false,
            "message" => "Aucune modification à effectuer"
        ];
    }


    // =====================================
    // UPDATE MYSQL
    // =====================================

    $valeurs[] = $id;

    $sql = "
        UPDATE users
        SET " . implode(", ", $champs) . "
        WHERE id = ?
    ";

    $req = $db->prepare($sql);

    $req->execute($valeurs);


    // Vérifier si la requête a fonctionné
    if ($req->rowCount() === 0) {
        return [
            "success" => true,
            "message" => "Aucune donnée n'a changé"
        ];
    }


    return [
        "success" => true,
        "message" => "Profil modifié avec succès"
    ];

}



//DETAILS


        // produits d'une boutique (table produits de Steve)
        function get_info_detail(){
            global $db;

            //data url
            $id = (int) ($_GET["id"] ?? 0);

            $req = $db->prepare("SELECT * FROM produits WHERE id_boutique = ? AND statut_validation = 'approuve' ");
            $req->execute(["$id"]);
            $res = $req->fetchAll();
            return $res;

        }

         // couverture de la boutique (table inscription_vendeur de Zara)
         function couverture_boutique(){
            global $db;

            //data url
            $id = intval($_GET["id"] ?? 0);

            $req = $db->prepare("
                SELECT
                    id,
                    boutname AS name,
                    definition AS description,
                    logo AS pdc,
                    localisation AS lieu,
                    date
                FROM inscription_vendeur
                WHERE id = ?
            ");
            $req->execute(["$id"]);
            $res = $req->fetchAll();
            return $res;

        }

        // (les commentaires sont gérés par la partie de Hajatiana dans suit_vue2.php)

     function search_detail(): array
{
    global $db;

    $input_search = trim($_GET["search"] ?? "");

    if ($input_search === "") {
        return [];
    }

    $req = $db->prepare("
        SELECT
            id,
            nom AS produit,
            photo AS image,
            description AS description1,
            stock AS nombre,
            prix,
            id_boutique
        FROM produits
        WHERE (nom LIKE ? OR type LIKE ?) AND statut_validation = 'approuve'
        ORDER BY id DESC
    ");

    $req->execute(["%" . $input_search . "%", "%" . $input_search . "%"]);

    return $req->fetchAll(PDO::FETCH_ASSOC);
}

// Barre de recherche de vue1.php / vue2.php : garde les boutiques dont le nom
// correspond, ou qui vendent un produit trouvé par search_detail()
function filtrer_boutiques(array $boutiques): array
{
    $input_search = trim($_GET["search"] ?? "");

    if ($input_search === "") {
        return $boutiques;
    }

    $ids = array_column(search_detail(), "id_boutique");

    return array_filter($boutiques, function ($b) use ($ids, $input_search) {
        return in_array($b["id"], $ids)
            || stripos($b["name"], $input_search) !== false;
    });
}

// Boutons "type" de la page d'une boutique : ?type=... ne garde que ce type de produit
function filtrer_par_type(array $produits): array
{
    $type = $_GET["type"] ?? "";

    if ($type === "") {
        return $produits;
    }

    return array_filter($produits, fn($p) => $p["type"] === $type);
}
   function get_image_detail(): array
{
    global $db;

    // TYPE du box = types de ses produits (inscription_vendeur n'a pas de colonne type)
    $sql = "
        SELECT
            b.id AS boutique_id,
            b.boutname AS boutique_name,
            b.definition AS boutique_description,
            b.logo AS boutique_pdc,
            (SELECT GROUP_CONCAT(DISTINCT p.type SEPARATOR ', ')
               FROM produits AS p
              WHERE p.id_boutique = b.id
                AND p.statut_validation = 'approuve') AS boutique_type,
            b.localisation AS boutique_lieu,

            d.id AS produit_id,
            d.nom AS produit_name,
            d.photo AS produit_image,
            d.description AS produit_description1,
            '' AS produit_description2,
            d.stock AS produit_nombre,
            d.prix AS produit_prix,
            5 AS produit_nombre_gros,
            d.prix_gros AS produit_prix_gros

        FROM inscription_vendeur AS b

        LEFT JOIN produits AS d
            ON d.id_boutique = b.id
           AND d.statut_validation = 'approuve'

        ORDER BY b.id DESC, d.id DESC
    ";

    $req = $db->prepare($sql);
    $req->execute();

    $lignes = $req->fetchAll(PDO::FETCH_ASSOC);

    $boutiques = [];

    foreach ($lignes as $ligne) {
        $idBoutique = (int) $ligne["boutique_id"];

        if (!isset($boutiques[$idBoutique])) {
            $boutiques[$idBoutique] = [
                "id" => $idBoutique,
                "name" => $ligne["boutique_name"],
                "description" => $ligne["boutique_description"],
                "pdc" => $ligne["boutique_pdc"],
                "type" => $ligne["boutique_type"],
                "lieu" => $ligne["boutique_lieu"],
                "produits" => []
            ];
        }

        if ($ligne["produit_id"] !== null) {
            $boutiques[$idBoutique]["produits"][] = [
                "id" => (int) $ligne["produit_id"],
                "produit" => $ligne["produit_name"],
                "image" => $ligne["produit_image"],
                "description1" => $ligne["produit_description1"],
                "description2" => $ligne["produit_description2"],
                "nombre" => (int) $ligne["produit_nombre"],
                "prix" => (int) $ligne["produit_prix"],
                "nombre_gros" => (int) $ligne["produit_nombre_gros"],
                "prix_gros" => (int) $ligne["produit_prix_gros"]
            ];
        }
    }

    return $boutiques;
}

function get_boutique(): array
{
    global $db;

    $req = $db->query("
        SELECT
            id,
            boutname AS name,
            definition AS description,
            logo AS pdc,
            localisation AS lieu,
            date
        FROM inscription_vendeur
        ORDER BY id DESC
    ");

    return $req->fetchAll(PDO::FETCH_ASSOC);
}


?>