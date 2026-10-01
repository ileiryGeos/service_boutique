const formProduit = document.querySelector("#form-produit")
const container = document.querySelector("#recu")

function getProduct (){
    fetch("api/list_prod_inscri_api.php")
    .then(rep=>rep.json())
    .then(data=>{
        console.log(data);

        let contenu = ""

        data.forEach((produit , index) => {
            // console.log(index);
            // console.log(produit);
            contenu += `
                <div class="recu_pro">
                    <img src="images/${produit.photo}" alt="" onerror="this.onerror=null;this.src='images/produit.jpg'">
                    <h3>${produit.type}</h3>
                    <h4>${produit.nom}</h4>
                    <p class="etat_recu ${produit.statut_validation}">${produit.statut_validation === "approuve" ? "en ligne" : produit.statut_validation === "refuse" ? "refusé" : "en attente de validation"}</p>
                    <p>${produit.description}</p>
                </div>
            `
        })
        container.innerHTML = contenu

    })
}
getProduct()



formProduit.addEventListener("submit" , (e)=>{
    e.preventDefault()

    let donneProd = new FormData(formProduit)

    fetch("api/add_prod_api.php" , {
        method: "POST" ,
        body: donneProd
    })
    .then(rep=>rep.json())
    .then(donne=>{
        console.log(donne) ;
        if (donne.statut !== 201) {
            alert(donne.message)
            return
        }
        getProduct()
        formProduit.reset()

    })
    .catch(error=>{
        console.log(error) ;
    })
})
