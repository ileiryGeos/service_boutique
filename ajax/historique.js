// HISTORIQUE d'achats : bouton × d'un achat → le retire de l'historique sans recharger la page
const listeHistorique = document.querySelector("#liste_historique")

if (listeHistorique) {
    listeHistorique.addEventListener("click", (e) => {
        const bouton = e.target.closest(".hist_sup[data-ligne]")

        if (!bouton) {
            return
        }

        const donnees = new FormData()
        donnees.append("id_ligne", bouton.dataset.ligne)

        fetch("api/historique_api.php", {
            method: "POST",
            body: donnees
        })
        .then(rep => rep.json())
        .then(rep => {
            if (!rep.success) {
                alert(rep.message)
                return
            }

            // nouvelle liste construite par PHP (functions/historique.php)
            listeHistorique.innerHTML = rep.liste
        })
        .catch(error => {
            console.log(error)
            alert("Erreur pendant la mise à jour de l'historique")
        })
    })
}
