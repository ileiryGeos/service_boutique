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
    <title>voyager</title>
</head>

<body>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        #fond1 {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: url(images/fondpp_cap.jpg) wheat;
            background-blend-mode: darken;
            background-size: cover;
        }


        #bvue {
            width: 55%;
            border-radius: 2vw;
            background: white;
            display: flex;
            height: 70%;
            flex-direction: column;
            box-shadow: 0 0 2vw black;
            transition: 2s;

        }

        #acceul{
            z-index: 3;
            padding: 1vw;
            position: absolute;
            top: 1vw;
            right: 1vw;
            border-radius: 1vw;
            border: solid .1vw orangered;
            color: yellow;
            background: rgba(0, 0, 255, 0.507);
            /* font-size: 1vw; */
            text-transform: uppercase;

        }

        .comp {
            width: 100%;
            height: 100%;
            display: flex;
            position: relative;
            transition: 1s;

        }

        .rond {
            z-index: 2;
            background: wheat;
            overflow: hidden;
            width: 45%;
            height: 100%;
            border-radius: 2vw 30% 30% 2vw;
            transition: 1s;
            position: relative;
        }

        .bl {
            background: wheat;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .rcnd {
            z-index: 2;
            background: wheat;
            overflow: hidden;
            width: 45%;
            height: 100%;
            border-radius: 30% 2vw 2vw 30%;
            transition: 1s;
            position: relative;
        }

        .bl2 {

            background: wheat;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .ro:hover {
            transform: translatex(37vw);
            border-radius: 30% 2vw 2vw 30%;
        }

        .rc:hover {
            transform: translateX(-37vw);
            border-radius: 2vw 30% 30% 2vw;

        }

        .inp {
            width: 60%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: 1s;
            border-radius: 2vw;
            background: white;

        }

        .b {
            color: blueviolet;
        }

        .bc {
            color: white;
            text-transform: capitalize;
            text-align: center;

        }

        .cot {
            color: blue;
            text-transform: capitalize;
            font-size: 3vw;
            text-align: center;
            margin-bottom: 1vw;

        }

        .nboc {
            color: aqua;
            font-size: 2.5vw;
        }

        .butdm {
            padding: 1vw;
            font-size: 1vw;
            font-weight: bold;
            text-transform: capitalize;
            background: blue;
            border: solid .1vw white;
            color: white;
            border-radius: 1vw;
            box-shadow: 0 .5vw 1vw grey;
        }

        .fenoi {
            padding: 1vw;
            width: 70%;
            border-radius: .5vw;
            font-size: 1vw;
            margin-bottom: .5vw;
            background: rgba(128, 128, 128, 0.253);
            border: none;
            /* text-transform: capitalize; */
        }

        /* liens vers les pages d'inscription */
        .lien_inscri {
            margin-top: 1vw;
            font-size: 1vw;
        }
    </style>

    <section id="fond1">
        <a href="vue1.php" id="acceul">ignore</a>
        <div id="bvue">
            <div class="comp ouvre">
                <div class="rond">
                    <div class="bl">
                        <h1 class="bc gch">bienvenu sur <br><span class="nboc"><b class="b">s</b>ervice <b
                                    class="b">b</b>outique</span> </h1>
                        <button class="butdm bo" style="margin: 3vw 0;">par compte de vente</button>
                    </div>
                </div>
                <div class="inp rak">
                    <h2 class="cot">acheter dans compte <br><span class="nboc"><b class="b">s</b>ervice <b class="b">b</b>outique</span>
                    </h2>

                    <form action="connexion.php" method="POST"
                        style="width:100%;display:flex;flex-direction:column;align-items:center;">

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
                            class="butdm"
                            style="width: 10vw;font-size: 1.3vw;">
                            connecter
                        </button>

                    </form>
                    <a href="profil.php" class="b lien_inscri">pas encore de compte ? s'inscrire</a>
                </div>
            </div>

            <div class="comp cre" style="display: none;">
                <form class="inp rad" method="POST" >
                    <h2 class="cot">Vendeur dans compte <br><span class="nboc" style="font-size: 2vw;"><b class="b">s</b>ervice
                            <b class="b">b</b>outique</span></h2>
                    <!-- connexion vendeur : nom de la boutique + mot de passe -->
                    <input type="text" placeholder="nom de votre boutique ..." class="fenoi" name="boutname" required>
                    <input type="password" placeholder="votre mot de passe ..." class="fenoi" name="password" required>
                    <button class="butdm" style="width: 10vw;font-size: 1.3vw;" name="connect">connecter</button>
                    <a href="inscription.php" class="b lien_inscri">pas encore de boutique ? créer ma boutique</a>
                </form>
                <div class="rcnd">
                    <div class="bl2">
                        <h1 class="bc">ouvrir le compte <br><span class="nboc"><b class="b">s</b>ervice <b
                                    class="b">b</b>outique</span> </h1>
                        <button class="butdm bcr" style="margin: 3vw 0;">saisir mon compte</button>
                    </div>
                </div>
            </div>
        </div>
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
