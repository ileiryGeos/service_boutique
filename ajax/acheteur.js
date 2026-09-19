const info_acheteur = document.querySelector(".info_acheteur");
const couverture = document.querySelector(".ah_pdp");
const nom_acheteur = document.querySelector(".ah_nom");

const form_modi = document.querySelector("#form_modi");
const image_modif = document.querySelector(".img_ah_modi");

// Image actuellement affichée
const image_pdp = document.querySelector(".img_pdp");

const boutique= document.querySelector(".points");

const image_list = document.querySelector(".petie")


/* =====================================================
   RÉCUPÉRER L'ACHETEUR
===================================================== */

const getAcheteur = async () => {

    try {

        const response = await fetch(
            "api/get_acheteur.php",
            {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "Accept": "application/json"
                }
            }
        );


        if (!response.ok) {
            throw new Error(
                `Erreur HTTP : ${response.status}`
            );
        }


        const data = await response.json();

        console.log("Réponse API :", data);


        if (!data.success) {
            throw new Error(data.message);
        }


        const profil_acheteur = data.acheteur;


        console.log(
            "Acheteur :",
            profil_acheteur
        );


        /* =========================
           NOM
        ========================= */

        if (nom_acheteur) {

            nom_acheteur.textContent =
                profil_acheteur.name;
        }


        /* =========================
           IMAGE
        ========================= */

        if (
            image_pdp &&
            profil_acheteur.pdc
        ) {

            image_pdp.src =
                `images/${profil_acheteur.pdc}`;

            image_pdp.alt =
                profil_acheteur.name;
        }


        /* =========================
           INFORMATIONS
        ========================= */

        if (info_acheteur) {

            info_acheteur.innerHTML = `

                <h6 class="tou_ve">
                    Email :
                    <b>${profil_acheteur.email}</b>
                </h6>

                <h6 class="tou_ve">
                    N° :
                    <b>${profil_acheteur.number}</b>
                </h6>

                <h6 class="tou_ve">
                    Date de naissance :
                    <b>${profil_acheteur.birthdate}</b>
                </h6>

                <h6 class="tou_ve">
                    Adresse :
                    <b>${profil_acheteur.address}</b>
                </h6>

            `;
        }


    } catch (error) {

        console.error(
            "Erreur récupération acheteur :",
            error
        );


        if (info_acheteur) {

            info_acheteur.innerHTML = `
                <p class="erreur">
                    ${error.message}
                </p>
            `;
        }
    }
};


/* =====================================================
   MODIFICATION DU PROFIL
===================================================== */

if (form_modi) {

    form_modi.addEventListener(
        "submit",
        async (e) => {

            e.preventDefault();


            /* =========================
               CRÉER FORMDATA
            ========================= */

            const formData =
                new FormData(form_modi);


            /* =========================
               AJOUTER L'IMAGE
            ========================= */

            if (
                image_modif &&
                image_modif.files.length > 0
            ) {

                formData.append(
                    "pdc",
                    image_modif.files[0]
                );
            }


            try {

                const response = await fetch(
                    "api/update_acheteur.php",
                    {
                        method: "POST",
                        body: formData,
                        credentials: "same-origin"
                    }
                );


                /* =========================
                   VÉRIFIER HTTP
                ========================= */

                if (!response.ok) {

                    throw new Error(
                        `Erreur HTTP : ${response.status}`
                    );
                }


                /* =========================
                   RÉCUPÉRER JSON
                ========================= */

                const data =
                    await response.json();


                console.log(
                    "Réponse modification :",
                    data
                );


                /* =========================
                   VÉRIFIER SUCCÈS
                ========================= */

                if (!data.success) {

                    alert(data.message);

                    return;
                }


                /* =========================
                   NOUVEAU PROFIL
                ========================= */

                const acheteur =
                    data.acheteur;


                if (!acheteur) {

                    throw new Error(
                        "Les nouvelles données du profil sont absentes."
                    );
                }


                /* =================================================
                   METTRE À JOUR LE NOM
                ================================================= */

                if (nom_acheteur) {

                    nom_acheteur.textContent =
                        acheteur.name;
                }


                /* =================================================
                   METTRE À JOUR LA PHOTO
                ================================================= */

                if (
                    image_pdp &&
                    acheteur.pdc
                ) {

                    image_pdp.src =
                        `images/${acheteur.pdc}?t=${Date.now()}`;

                    image_pdp.alt =
                        acheteur.name;
                }


                /* =================================================
                   METTRE À JOUR LES INFORMATIONS
                ================================================= */

                if (info_acheteur) {

                    info_acheteur.innerHTML = `

                        <h6 class="tou_ve">
                            Email :
                            <b>${acheteur.email}</b>
                        </h6>

                        <h6 class="tou_ve">
                            N° :
                            <b>${acheteur.number}</b>
                        </h6>

                        <h6 class="tou_ve">
                            Date de naissance :
                            <b>${acheteur.birthdate}</b>
                        </h6>

                        <h6 class="tou_ve">
                            Adresse :
                            <b>${acheteur.address}</b>
                        </h6>

                    `;
                }


                /* =================================================
                   NETTOYER LE FORMULAIRE
                ================================================= */

                form_modi.reset();

                // refermer le formulaire après l'enregistrement
                if (bloc_modifier && bouton_modifier) {
                    ouvrir_formulaire(false);
                }


                if (image_modif) {

                    image_modif.value = "";
                }


                /* =================================================
                   MESSAGE
                ================================================= */

                alert(data.message);


            } catch (error) {

                console.error(
                    "Erreur modification :",
                    error
                );

                alert(
                    "Une erreur est survenue : " +
                    error.message
                );
            }

        }
    );
}

/* =====================================================
   BOUTON "Modifier votre profil" : ouvre / ferme le formulaire
   (avant, le formulaire s'ouvrait au survol)
===================================================== */

const bloc_modifier = document.querySelector(".modifier");
const bouton_modifier = document.querySelector(".but_ouvrir_modif");

function ouvrir_formulaire(ouvert) {
    bloc_modifier.classList.toggle("ouvert", ouvert);
    bouton_modifier.setAttribute("aria-expanded", ouvert);
}

if (bloc_modifier && bouton_modifier) {
    bouton_modifier.addEventListener("click", () => {
        ouvrir_formulaire(!bloc_modifier.classList.contains("ouvert"));
    });
}


/* =====================================================
   PHOTO : image par défaut si le fichier n'existe pas,
   et envoi immédiat quand on choisit une nouvelle photo
===================================================== */

if (image_pdp) {
    image_pdp.addEventListener("error", () => {
        if (!image_pdp.src.endsWith("images/pdp.jpg")) {
            image_pdp.src = "images/pdp.jpg";
        }
    });
}

if (image_modif && form_modi) {
    image_modif.addEventListener("change", () => {
        if (image_modif.files.length > 0) {
            form_modi.requestSubmit();
        }
    });
}

getAcheteur();

    
