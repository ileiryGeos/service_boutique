<?php
    // Page de connexion commune :
    //   - à gauche  : connexion ACHETEUR (Daddy)  -> connexion.php
    //   - à droite  : connexion VENDEUR  (Zara)   -> connecter() ci-dessous
    require "functions/db.php";
    require "functions/inscription_boutique.php";

    if(isset($_POST["connect"])){
        connecter();
    }

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="css/design.css?v=<?= filemtime("css/design.css") ?>">
    <link rel="stylesheet" href="css/connexion.css?v=<?= filemtime("css/connexion.css") ?>">
</head>

<body>

    <section id="fond1">
        <a href="vue1.php" id="accueil">ignore</a>
        <div id="bvue">
            <div class="comp ouvre">
                <div class="rond">
                    <div class="bl">
                        <h1 class="bc gch">bienvenue sur <br><span class="nboc"><b class="b">s</b>ervice <b
                                    class="b">b</b>outique</span> </h1>
                        <button class="butdm bo">passer au compte vendeur</button>
                    </div>
                </div>
                <div class="inp rak">
                    <h2 class="cot">acheter avec un compte <br><span class="nboc"><b class="b">s</b>ervice <b class="b">b</b>outique</span>
                    </h2>

                    <form action="connexion.php" method="POST">

                        <input type="email"
                            name="email"
                            placeholder="votre adresse email ..."
                            class="fenoi"
                            required>

                        <input type="password"
                            name="mot_de_pass"
                            placeholder="votre mot de passe ..."
                            class="fenoi"
                            required>

                        <button type="submit"
                            class="butdm">
                            connecter
                        </button>

                    </form>
                    <a href="profil.php" class="b lien_inscri">pas encore de compte ? s'inscrire</a>
                </div>
            </div>

            <div class="comp cre" style="display: none;">
                <form class="inp rad" method="POST" >
                    <h2 class="cot">vendre avec un compte <br><span class="nboc"><b class="b">s</b>ervice
                            <b class="b">b</b>outique</span></h2>
                    <!-- connexion vendeur : nom de la boutique + mot de passe -->
                    <input type="text" placeholder="nom de votre boutique ..." class="fenoi" name="boutname" required>
                    <input type="password" placeholder="votre mot de passe ..." class="fenoi" name="password" required>
                    <button class="butdm" name="connect">connecter</button>
                    <a href="inscription.php" class="b lien_inscri">pas encore de boutique ? créer ma boutique</a>
                </form>
                <div class="rcnd">
                    <div class="bl2">
                        <h1 class="bc">ouvrir le compte <br><span class="nboc"><b class="b">s</b>ervice <b
                                    class="b">b</b>outique</span> </h1>
                        <button class="butdm bcr">passer au compte acheteur</button>
                    </div>
                </div>
            </div>
        </div>
        <a href="admin_connexion.php" id="lien_admin">administration</a>
    </section>
    <script>
        const boChang = document.querySelector(".bo")
        const bcrChang = document.querySelector(".bcr")
        const roBlue = document.querySelector(".rond")
        const rcBlue = document.querySelector(".rcnd")
        const ouvreBlanc = document.querySelector(".ouvre")
        const créeBlanc = document.querySelector(".cre")
        const ouBlanc = document.querySelector(".rad")
        const rakBlanc = document.querySelector(".rak")
        boChang.addEventListener("click", () => {
            roBlue.classList.add("ro")
            rakBlanc.style.transform = "translatex(-30vw)"
            setTimeout(() => {
                créeBlanc.style.display = "flex"
                ouvreBlanc.style.display = "none"
                roBlue.classList.remove("ro")
                ouBlanc.style.transform = "none"
            }, 1000)
        })
        bcrChang.addEventListener("click", () => {
            rcBlue.classList.add("rc")
            ouBlanc.style.transform = "translatex(30vw)"
            setTimeout(() => {
                créeBlanc.style.display = "none"
                ouvreBlanc.style.display = "flex"
                rcBlue.classList.remove("rc")
                rakBlanc.style.transform = "none"

            }, 1000)
        })
    </script>
</body>

</html>
