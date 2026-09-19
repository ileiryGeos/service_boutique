// Bouton "buy" : ajoute le produit au panier sans recharger la page
// et met à jour le nombre d'articles dans le lien "panier" du menu
const compteurPanier = document.querySelector(".nb_panier")

document.querySelectorAll(".butbay").forEach(bouton => {
    bouton.addEventListener("click", () => {
        const donnees = new FormData()
        donnees.append("id_produit", bouton.dataset.produit)

        fetch("api/panier_api.php", {
            method: "POST",
            body: donnees
        })
        .then(rep => rep.json())
        .then(rep => {
            if (!rep.success) {
                alert(rep.message)
                return
            }

            if (compteurPanier) {
                compteurPanier.textContent = rep.nombre
            }

            // petit retour visuel sur le bouton
            bouton.textContent = "ajouté ✓"
            setTimeout(() => { bouton.textContent = "buy" }, 1200)
        })
        .catch(error => {
            console.log(error)
            alert("Erreur pendant l'ajout au panier")
        })
    })
})
