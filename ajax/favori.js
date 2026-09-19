// FAVORIS : clic sur l'étoile ★ d'un produit (ou sur l'étoile d'un favori de la barre de gauche)
// → ajoute / retire le favori sans recharger la page
const listeFavoris = document.querySelector("#liste_favoris")

document.addEventListener("click", (e) => {
    const etoile = e.target.closest(".fv[data-produit], .fav[data-produit]")

    if (!etoile) {
        return
    }

    e.preventDefault()

    const idProduit = etoile.dataset.produit
    const donnees = new FormData()
    donnees.append("id_produit", idProduit)

    fetch("api/favori_api.php", {
        method: "POST",
        body: donnees
    })
    .then(rep => rep.json())
    .then(rep => {
        if (!rep.success) {
            alert(rep.message)
            return
        }

        // étoiles de ce produit sur la page : dorée = en favori
        document.querySelectorAll(`.fv[data-produit="${idProduit}"]`).forEach(fv => {
            fv.classList.toggle("en_favori", rep.favori)
            fv.title = rep.favori ? "retirer des favoris" : "ajouter aux favoris"
        })

        // nouvelle liste des favoris (construite par PHP : functions/favori.php)
        if (listeFavoris) {
            listeFavoris.innerHTML = rep.liste
        }
    })
    .catch(error => {
        console.log(error)
        alert("Erreur pendant la mise à jour des favoris")
    })
})
