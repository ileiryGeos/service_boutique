// Formulaire "Ajouter un Nouveau produit" de vue3.php et suit_vue3.php
// (même API que le formulaire de Steve : api/add_prod_api.php)
const formAjout = document.querySelector("#form_ajout_prod")

if (formAjout) {
    formAjout.addEventListener("submit", (e) => {
        e.preventDefault()

        fetch("api/add_prod_api.php", {
            method: "POST",
            body: new FormData(formAjout)
        })
        .then(rep => rep.json())
        .then(donne => {
            console.log(donne)
            if (donne.statut !== 201) {
                alert(donne.message)
                return
            }
            // recharger la page pour voir le produit dans les listes
            location.reload()
        })
        .catch(error => {
            console.log(error)
            alert("Erreur pendant l'ajout du produit")
        })
    })
}

// bouton "suprimer ce produit" : demander confirmation
document.querySelectorAll(".form_sup").forEach(form => {
    form.addEventListener("submit", (e) => {
        if (!confirm("Supprimer ce produit ?")) {
            e.preventDefault()
        }
    })
})
