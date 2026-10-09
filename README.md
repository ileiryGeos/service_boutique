# V-STORE — projet final (fusion)

Ce dossier réunit en **un seul projet** le travail de chaque membre du groupe.
L'application s'appelle **V-STORE** ; le dossier, la base et le dépôt gardent
leur nom technique `service_boutique`.
Les dossiers d'origine n'ont pas été modifiés.

## Installation (WampServer)

1. Cloner le dépôt dans `C:\wamp64\www\` (le dossier doit s'appeler `service_boutique`) :
   `git clone <url-du-dépôt> C:\wamp64\www\service_boutique`
2. Dans phpMyAdmin, importer **`service_boutique.sql`**. Il crée la base `service_boutique`.
3. Si besoin, changer les identifiants MySQL dans **`config/server.php`** (c'est le seul fichier de configuration).
4. Ouvrir http://localhost/service_boutique/

Les fichiers du modèle HTML/CSS (`css/`, `css_v1/`, `css_v2/`, `css_v3/`, `images/`, `photo/`) viennent du dossier `tdgroup`.
`css_v1/` est utilisé par les pages publiques `vue1.php` et `suit_vue1.php`.
Les photos envoyées par les formulaires sont aussi enregistrées dans `images/`. Elles ne sont pas mises dans le dépôt Git (voir `.gitignore`) : seules les images utilisées par le site et les données de départ y sont.

### Comptes (mot de passe `1234` pour tous)

| Rôle | Identifiants |
|---|---|
| Acheteur | email `acheteur@demo.mg` |
| Vendeur | nom de boutique `Demo` (connexion : nom de la boutique + mot de passe) |
| Administrateur | email `admin@demo.mg` (page `admin_connexion.php`) |

Les autres comptes de départ utilisent aussi `1234` : acheteurs `rakoto@exemple.mg`, `miia@exemple.mg`, `alderson@exemple.mg`, `bozyy@exemple.mg` ; boutiques `Zara` et `KOTO`.
Les emails et téléphones de départ sont fictifs.

## Parcours

```
index.php → connection.php   (bouton « ignore » → vue1.php → suit_vue1.php?id=… : visite sans compte)
   ├─ Acheteur : connexion.php ─────→ vue2.php (profil + liste des boutiques)
   │   (inscription : profil.php)        └→ suit_vue2.php?id=… (boutique, produits, commentaires)
   └─ Vendeur  : connecter() ───────→ vue3.php (profil, ajout / suppression de produits)
       (inscription : inscription.php)   └→ suit_vue3.php (ma boutique + commentaires des clients)
   └─ Administrateur : admin_connexion.php → admin.php (tableau de bord)
deconnexion.php : déconnexion (acheteur, vendeur et administrateur)
```

## Qui a fait quoi → où c'est maintenant

| Partie | Fichiers du projet final |
|---|---|
| **Daddy** (connexion + profil acheteur) | `connexion.php`, `profil.php` (inscription acheteur), formulaire acheteur de `connection.php`, protection des pages et affichage du profil depuis la session dans `vue2.php` / `suit_vue2.php` |
| **Steve** (produits) | `api/add_prod_api.php`, `api/list_prod_inscri_api.php`, `functions/func_prod.php`, `utils/upload_file.php`, `ajax/ajout_prod_inscri.js`, formulaire produit de `inscription.php`, table `produits` |
| **Hajatiana** (commentaires) | ajout et affichage des commentaires par produit dans `suit_vue2.php`, table `commentaire` |
| **Zara** (inscription + connexion vendeur) | `functions/inscription_boutique.php`, formulaire boutique de `inscription.php`, formulaire vendeur de `connection.php`, `vue3.php`, table `inscription_vendeur` |
| **Karine** (acheteur) | `functions/acheteur.php`, `api/get_acheteur.php`, `api/update_acheteur.php`, `ajax/acheteur.js`, liste des boutiques de `vue2.php`, en-tête et produits de la boutique dans `suit_vue2.php` |

## Base de données unique

Chaque partie avait sa propre base (`prov3`, `boutiques`, `boutique`, `varotra`, `gestion_de_stock`).
Quand deux parties avaient créé une table pour la même chose, on garde la table de la personne responsable de cette partie :

| Table finale | Vient de | Remplace aussi (Karine / Hajatiana) |
|---|---|---|
| `users` (acheteurs) | Daddy | `profil_acheteur` |
| `inscription_vendeur` (boutiques) | Zara | `boutique` |
| `produits` (+ colonne **`id_boutique`**) | Steve | `detail`, `produit` |
| `commentaire` | Hajatiana | `comments` |

Les requêtes de Karine utilisent maintenant ces tables. Par exemple, `boutname AS name` permet de garder le même code HTML.
Le « TYPE » d'une boutique est calculé à partir des types de ses produits.

## Modifications faites pour relier les parties

Les fonctionnalités n'ont pas été changées. Voici seulement ce qui a été modifié pour que tout fonctionne ensemble :

- **Connexion à la base**
  - Une seule configuration (`config/server.php`) et une seule connexion PDO (`functions/db.php`, fonction `connection_db_bou()`).
  - Le mysqli de Daddy utilise la même configuration.
  - Correction `dbhost=` → `host=` dans la connexion PDO.
- **Images** : tous les chemins d'upload pointent vers `images/`, et la base enregistre seulement le nom du fichier.
  Daddy enregistrait avant `uploads/…`.
- **Zara**
  - Après l'inscription, la boutique est connectée : `$_SESSION["id_boutique"]`. Sans ça, `vue3.php` plantait.
  - `vue3.php` renvoie vers la connexion si aucun vendeur n'est connecté.
  - Le formulaire « Ajouter un Nouveau produit » (champs copiés par erreur) est remplacé par celui de la maquette `suit_vue3.html`, branché sur l'API de Steve.
- **Steve**
  - Un produit est lié à la boutique connectée (`id_boutique`).
  - La liste affiche seulement les produits de cette boutique.
  - Ajout du `</div>` qui manquait dans le gabarit JS.
- **Karine**
  - La session s'appelle maintenant `user_id` (celle créée par Daddy).
  - Le téléphone est gardé en texte, pour ne pas perdre le 0 du début.
  - Suppression d'un `<div class="modifier">` en double dans `vue2.php`, qui cassait la mise en page.
- **Hajatiana**
  - Les commentaires sont liés aux produits de Steve.
  - Après un commentaire, retour sur la même boutique (`?id=`).
  - L'auteur est l'acheteur connecté (voir « Panier et commentaires »).
- **Navigation**
  - `index.php` → `connection.php`.
  - Liens « s'inscrire » et « créer ma boutique » sur la page de connexion.
  - `deconnexion.php` créé (le lien existait mais pas le fichier).
  - Les liens `*.html` sont remplacés par les pages `.php`.

## Versions en double non reprises

Elles restent dans les dossiers d'origine :

- `modifier_profil.php`, `vue2.php` et `suit_vue2.php` de Daddy : la modification du profil utilise la version AJAX de Karine, qui fait la même chose.
- `suit_vue2.html` + `ajax/ajout_prod_vue2.js` + `api/list_prod_vue2_api.php` de Steve : l'affichage des produits utilise la version PHP de Hajatiana, qui contient déjà les commentaires.
- `get_comments()` / `add_comment()` de Karine : remplacées par les commentaires de Hajatiana.
- Le formulaire boutique de `inscription.html` (Steve) : c'est celui de Zara qui est branché.

## Corrections (2e passe : liens et fonctionnalités cassés)

- **Pages manquantes** créées à partir des maquettes de `tdgroup` :
  - `vue1.php` : accueil public, lien du bouton « ignore ». On y voit les boutiques sans compte, avec les liens vers la connexion et les deux inscriptions.
  - `suit_vue1.php` : page publique d'une boutique, en lecture seule.
  - `suit_vue3.php` : « ma boutique » du vendeur. On y trouve ses produits, les commentaires des clients, et il peut leur répondre au nom de la boutique.
- **Images cassées**
  - Les données de départ pointaient vers des fichiers qui n'existaient pas. Elles utilisent maintenant des images de `images/`.
  - `utils/image.php` (`image_ou()`) affiche une image par défaut quand un fichier manque : `kara.jpg` pour une boutique, `produit.jpg` pour un produit, `pdp.jpg` pour un profil.
- **Recherche** (`vue1.php`, `vue2.php`) : branchée sur `search_detail()` de Karine. Elle cherche dans le nom et le type des produits, et dans le nom des boutiques.
- **Boutons « type »** (`suit_vue1/2/3.php`) : ils montrent les vrais types de la boutique et filtrent les produits.
- **Vendeur** (`vue3.php` et `suit_vue3.php`)
  - Le formulaire « Ajouter un Nouveau produit » (champs de la maquette + stock) envoie à l'API de Steve (`ajax/produit_vendeur.js`).
  - La liste « supprimer ce produit » affiche les vrais produits. La suppression fonctionne, seulement pour les produits de sa boutique.
  - La carte de la boutique affiche ses vrais produits et ses types.
  - « Modifier » avec un seul champ rempli n'efface plus l'autre.
- **Acheteur**
  - Le bouton photo (« Q ») envoie la nouvelle photo tout de suite.
  - Les commentaires ont un avatar.
- **Formulaires**
  - Champs obligatoires, et stock/prix en nombres. L'API refuse sinon, et le message s'affiche.
  - Inscription d'une boutique possible sans logo.
  - Suppression du `var_dump` qui s'affichait.
  - Le style `.recu` de la liste des produits est maintenant appliqué.
- **Navigation**
  - Le logo ramène à l'accueil de chaque espace.
  - Liens « déjà un compte ? » sur les pages d'inscription.
  - Lien « retour » sur les messages d'erreur de connexion et d'inscription.

⚠️ Pour avoir les nouvelles images de départ, il faut **réimporter `service_boutique.sql`**. Cela efface les données de test déjà saisies.

## Panier et commentaires (3e passe)

- **Bouton « buy »** (avant « bay ») sous chaque produit de `suit_vue2.php`
  - Il ajoute le produit au **panier** de l'acheteur connecté, sans recharger la page (`ajax/panier.js` → `api/panier_api.php`).
  - Cliquer encore ajoute une pièce, sans dépasser le stock.
  - Le lien **« panier (n) »** du menu se met à jour.
- **Page `panier.php`**
  - Produits, boutique, prix, boutons − / + / retirer, sous-totaux et total.
  - Au-delà de 5 pièces, le prix de gros s'applique (« Le prix de plus de 5p est … »).
  - Fonctions dans `functions/panier.php`, table `panier`.
- **Commentaires**
  - Le champ « Votre nom » a disparu. Le commentaire est enregistré avec l'id de l'acheteur connecté (`commentaire.id_user`).
  - Le nom et la photo de l'auteur sont lus dans la table `users`. Si l'acheteur change de nom, ses commentaires suivent.
  - La zone de texte dit « Qu'en pensez-vous ? », avec un vrai bouton **« envoyer »**.
- **Retour à l'accueil**
  - Liens « accueil » et « panier » dans le menu des pages acheteur.
  - Tout le bouton « accueil » de la boutique est cliquable, pas seulement le mot. Il ramène à `vue2.php` avec toutes les boutiques.
- **`config/server.php`** : nouveau réglage `DB_PORT` (3306 = MySQL de WAMP, 3307 = MariaDB).

### Mettre à jour une base déjà importée

Importer **`mise_a_jour_panier.sql`** dans phpMyAdmin. Il ajoute la table `panier` et la colonne `commentaire.id_user`, et relie les anciens commentaires à leur auteur quand le nom correspond.
Les comptes, boutiques, produits et commentaires existants sont conservés.
Pour une nouvelle installation, importer seulement `service_boutique.sql`.

## Commandes (4e passe)

- **Le stock baisse à la commande, pas au clic sur « buy ».**
  - Le panier est une liste d'envies : il vérifie le stock mais ne le bloque pas.
  - Le stock est retiré quand l'acheteur clique sur **« commander »**, et rendu si la commande est annulée.
- **Bouton « commander »** (`panier.php`)
  - L'acheteur indique l'adresse de livraison et le téléphone, pré-remplis depuis son profil.
  - Le panier devient **une commande par boutique**, et le panier est vidé.
  - Tout se passe dans une transaction (`passer_commande()` dans `functions/commande.php`). Si un produit n'a plus assez de stock (un autre acheteur a commandé entre-temps), rien n'est enregistré et le message l'indique.
- **« Mes commandes »** (sous le panier) : l'acheteur suit le statut de chaque commande (en attente, acceptée, livrée, annulée). Il peut l'annuler tant que la boutique ne l'a pas acceptée.
- **Vendeur : « Commandes reçues »** (`vue3.php`, lien « commandes (n) » du menu)
  - Pour chaque commande : client, produits, quantités, prix, adresse de livraison, téléphone et total.
  - Boutons **accepter** → **marquer livrée**, ou **annuler** (le stock est rendu). Un vendeur ne peut traiter que les commandes de sa boutique.
- **`suit_vue3.php`** : la colonne « commandes » de la maquette (à droite de chaque produit) montre les commandes de ce produit.
- **Tables** `commande` et `commande_ligne`. Le nom et le prix sont copiés dans la commande : l'historique ne change pas si le vendeur modifie le produit.
- **Carte produit** (`suit_vue2.php`)
  - Elle affiche maintenant tout, jusqu'aux commentaires. Avant, sa hauteur fixe cachait les commentaires ; règles ajoutées à la fin de `css_v2/detai_vue2.css`.
  - Produit sans stock : bouton « épuisé ».

Base déjà importée : importer **`mise_a_jour_commande.sql`**, après `mise_a_jour_panier.sql`.

## Connexion vendeur simplifiée (5e passe)

- La connexion vendeur (`connection.php`) ne demande plus que le **nom de la boutique** et le **mot de passe**. Avant, il fallait aussi le téléphone et l'email.
- Le nom de la boutique sert d'identifiant. Il reste donc **unique** :
  - l'inscription refusait déjà un nom existant ;
  - la modification du profil refuse maintenant aussi un nom déjà pris (« Ce nom de boutique est déjà utilisé »).
- En cas d'erreur, le même message s'affiche que le nom existe ou non : « Nom de boutique ou mot de passe incorrect ».

## Profil acheteur et favoris (6e passe)

- **« Modifier votre profil »**
  - Le formulaire du profil acheteur (barre de gauche de `vue2.php` et `suit_vue2.php`) ne s'ouvre plus au survol.
  - Il s'ouvre et se ferme au clic sur le bouton « Modifier votre profil » (`ajax/acheteur.js`, classe `.ouvert` dans `css_v2/tete_vue2.css`).
  - Il se referme tout seul après l'enregistrement.
- **Favoris** (partie prévue pour Hajatiana, « mbola tsy vita ny fav »)
  - L'étoile ★ dans le coin de chaque produit (`suit_vue2.php`) est un bouton. Un clic ajoute le produit aux favoris (étoile dorée), un autre clic le retire. Pas de rechargement de page (`ajax/favori.js` → `api/favori_api.php`).
  - La liste **favoris** de la barre de gauche affiche les vrais favoris : photo, boutique, produit et prix. Un clic sur la photo ou la boutique ouvre la boutique ; l'étoile de la liste retire le favori.
  - Chaque acheteur a ses propres favoris (table `favori`). Si un produit est supprimé, il disparaît aussi des favoris.
  - Code dans `functions/favori.php`. La liste est construite par une seule fonction PHP, `afficher_favoris()`, utilisée par les pages et par l'API.

Base déjà importée : importer **`mise_a_jour_favoris.sql`**.

## Historique d'achats (7e passe)

- La liste **« historique d'achats »** de la barre de gauche acheteur (`vue2.php`, `suit_vue2.php`) est placée au-dessus des **favoris**. Elle montre les derniers produits achetés, du plus récent au plus ancien (10 au maximum).
- **Un achat** = un produit d'une commande envoyée avec le bouton « commander ». Les commandes **annulées** n'y figurent pas.
- **Chaque ligne** : photo, produit × quantité, boutique, date et statut de la commande. Un clic ouvre la boutique.
- **Sans achat**, la liste est vide (« Aucun achat pour le moment. »).
- **Bouton ×** : retire l'achat de l'historique, sans recharger la page (`ajax/historique.js` → `api/historique_api.php`). La commande n'est **pas** modifiée (colonne `commande_ligne.dans_historique`).
- Code dans `functions/historique.php`. La liste est construite par une seule fonction PHP, `afficher_historique()`, utilisée par les pages et par l'API.
- Des titres « historique d'achats » et « favoris » séparent les deux listes.

Base déjà importée : importer **`mise_a_jour_historique.sql`**.

## Mettre le site en ligne (InfinityFree ou autre hébergeur PHP/MySQL)

GitHub Pages ne convient pas : il n'exécute pas le PHP et n'a pas de base MySQL.
Il faut un hébergeur avec **PHP** et **MySQL** (InfinityFree est gratuit).

1. **Créer la base** : panneau de contrôle → *MySQL Databases* → créer une base.
   Noter les 4 informations affichées : *MySQL DB Name*, *MySQL User Name*,
   *MySQL Hostname* (ex. `sql123.infinityfree.com`) et le mot de passe du compte.
2. **Importer les tables** : *phpMyAdmin* → choisir cette base → onglet *Importer*
   → envoyer **`service_boutique_hebergement.sql`** (c'est la même base que
   `service_boutique.sql`, sans `CREATE DATABASE` : l'hébergeur l'a déjà créée).
3. **Préparer les identifiants** : copier `config/server.local.exemple.php` en
   `config/server.local.php` et y mettre les 4 informations de l'étape 1.
   Ce fichier ne part jamais sur GitHub (`.gitignore`) : il contient le mot de passe.
4. **Envoyer les fichiers** par FTP (FileZilla, ou le gestionnaire de fichiers du
   panneau) dans le dossier **`htdocs`** : tout le contenu du projet, y compris
   `config/server.local.php` et le dossier `images/`.
5. **Ouvrir l'adresse du site**. Les comptes de départ fonctionnent (mot de passe `1234`).

Remarques :

- Le dossier `images/` doit rester **accessible en écriture** : c'est là que vont les
  photos envoyées par les formulaires (produits, logos, profils).
- Le site en ligne est public : n'importe qui peut créer un compte et commander.
  Pour une démonstration, c'est normal ; pensez à changer les mots de passe `1234`
  si le site reste en ligne longtemps.
- Les photos envoyées en ligne restent sur l'hébergeur : elles ne reviennent pas
  dans le dépôt Git.

## Administrateur (8e passe)

Le site a maintenant **trois types de comptes** : acheteur, vendeur (boutique) et **administrateur**.
L'administrateur se connecte sur **`admin_connexion.php`** (lien « administration » en bas de la page de connexion)
et travaille dans **`admin.php`**.

### Validation des produits

- Un produit ajouté par une boutique est **en attente de validation** : les acheteurs ne le voient pas.
- L'administrateur le **valide** (il passe « en ligne ») ou le **refuse**. Il peut aussi
  **retirer du site** un produit déjà en ligne, ou le **supprimer** définitivement.
- Le vendeur voit l'état de chacun de ses produits dans sa liste (`vue3.php`, `suit_vue3.php`, `inscription.php`).
- Un produit retiré ne peut plus être commandé, même s'il était déjà dans un panier.

### Frais de mise en vente

- À chaque validation, les **frais de mise en vente** sont facturés à la **boutique**.
  **Les acheteurs ne paient aucun frais.**
- Le montant est réglable dans le tableau de bord (table `parametre`, 2 000 Ar au départ)
  et vaut pour les validations suivantes.
- Chaque frais est enregistré dans la table `frais` (boutique, produit, montant, payé ou non).
  L'administrateur peut marquer les frais d'une boutique comme **payés**.
- Les frais d'un produit supprimé sont conservés : l'historique des revenus ne change pas.

### Tableau de bord

- **Chiffres du haut** : produits à valider, produits en ligne, boutiques, acheteurs,
  ventes des boutiques, frais facturés / encaissés / en attente.
- **Produits à valider** : photo, boutique, description, prix et stock, avec les boutons
  valider / refuser / supprimer.
- **Produits en ligne et refusés** : tous les produits du site, avec retrait ou suppression.
- **Ventes de toutes les boutiques** : date, boutique, client, montant et état de chaque commande.
- **Revenus mois par mois** : produits validés, frais facturés, frais encaissés,
  ventes des boutiques et nombre de commandes pour chaque mois, plus le détail des derniers frais.
- **Boutiques** : produits, ventes, frais dus, bouton « frais payés » et suppression du compte.
- **Acheteurs** : commandes, total acheté et suppression du compte.

Le code est dans `functions/admin.php`, les pages `admin.php` et `admin_connexion.php`,
le style dans `css_admin/admin.css`.

Base déjà importée : importer **`mise_a_jour_admin.sql`**. Les produits déjà en ligne
restent visibles (ils passent en « approuve ») et aucun frais n'est créé pour eux.

## Orthographe des textes affichés (9e passe)

Relecture de tout le texte visible du site (menus, boutons, labels, placeholders,
titres des onglets, messages). Seuls les textes ont changé : aucune classe CSS,
aucun nom de champ et aucune requête SQL n'a changé.

Principales corrections :

| avant | après |
| --- | --- |
| aide et suport | aide et support |
| deconnection | déconnexion |
| acceul | accueil |
| Travaller avec nous : Vendreur\|Vendeuse | Travailler avec nous : Vendeur \| Vendeuse |
| Créer une nouveau compte | Créer un nouveau compte |
| On fait reunir plusieurs boutique pour que vous ne perdre plus de temps | Nous réunissons plusieurs boutiques pour que vous ne perdiez plus de temps |
| points fort du box | points forts du box |
| bienvenue sur notre cite | bienvenue sur notre site |
| pieces | pièces |
| Volez-vous modifier votre profil ? | Voulez-vous modifier votre profil ? |
| une seul ou toutes les informations | une seule ou toutes les informations |
| If faut completer tous | Il faut compléter tous les champs |
| suprimer ce produit | supprimer ce produit |
| Nouvel localisation / Nouvel numero / Nouvel adress email | Nouvelle localisation / Nouveau numéro / Nouvelle adresse email |
| Leur description / Leur prix | Sa description / Son prix |
| date naissence / adress | date de naissance / adresse |
| Soyez le premier a commenter | Soyez le premier à commenter |
| si antre 1 à 5 | si entre 1 et 5 |
| une produit / prix de produit | un produit / prix du produit |
| definition ou une style de publicité | définition ou un style de publicité |
| Déjà éxiste | Ce nom de boutique existe déjà |
| titre de l onglet : boite / Document / voyager | boîte / créer ma boutique / connexion |

La classe CSS `.suprimer` garde son nom (seul le texte du bouton est corrigé), et
`id="acceul"` de la page de connexion a été renommé `id="accueil"` avec son style.

Textes volontairement laissés tels quels :

- le bouton « buy » (demande explicite) ;
- les données de démonstration des camarades (`kkkkkkk`, `BOUTIQUE MILAY`,
  `tsy aiko`, `fdhj`...) : ce sont des lignes de la base, pas du texte du site.
  Elles se corrigent dans phpMyAdmin ou dans `service_boutique.sql`.

## Refonte graphique et responsive (10e passe)

Les cinq maquettes de départ avaient chacune ses couleurs (rouge, bleu,
jaune, wheat, blueviolet) et **toutes les tailles étaient en `vw`**, y
compris `* { font-size: 1.2vw }`. Le texte grossissait donc avec la
fenêtre et devenait illisible sur un téléphone ; il n'y avait aucun
`@media`. Tout a été repris sur une seule base.

### `css/design.css` : le système de design

Nouveau fichier chargé **avant** tous les autres sur chaque page. Il
contient les variables, la remise à zéro et ce qui est commun :

| | |
| --- | --- |
| couleurs | une gamme de gris chauds (`--pierre-50` à `--pierre-900`) utilisée sous des noms de rôle : `--fond`, `--surface`, `--bord`, `--texte`, `--texte-2`… |
| accent | une seule couleur, sobre : `--accent` (bleu ardoise) pour les liens, les boutons et les éléments actifs |
| sens | `--succes`, `--attention`, `--danger`, chacun avec sa version douce pour les fonds de pastille |
| texte | `--t-xs` à `--t-3xl` ; les petites tailles sont fixes (en `rem`), les titres fluides avec `clamp()` |
| espacements | `--e-1` (0.25rem) à `--e-8` (4rem) |
| arrondis | `--r-1` à `--r-4` et `--r-rond` |
| ombres | `--o-1`, `--o-2`, `--o-3`, discrètes |
| mise en page | `--h-tete` (barre du haut), `--l-barre` (colonne de gauche), `--l-max` |

Pour changer l'allure du site entier, il suffit de modifier ces
variables : aucune couleur ni taille n'est écrite en dur ailleurs.

### Typographie

Une seule famille, **Inter** (Google Fonts), avec une pile de secours
système si la police ne se charge pas. La hiérarchie se fait à la
graisse et à la taille, plus à la couleur :

- les titres de section sont en capitales, petits, gris et espacés
  (`--ls-maj`) ;
- les titres de page utilisent l'échelle fluide ;
- le texte courant est à 15 px avec un interlignage de 1.55.

Avant, chaque feuille redéfinissait `font-size` en `vw` ; plus aucune
feuille ne le fait.

### Responsive

Seuils : **1200 px**, **960 px**, **760 px**, **640 px**.

- au-dessus de 960 px : barre du haut fixe, colonne de gauche fixe,
  contenu décalé de `--l-barre` ;
- en dessous : la colonne devient un bandeau posé au-dessus du contenu
  (l'ordre du HTML le permettait déjà, rien n'a été déplacé) ;
- les listes de produits sont des grilles `auto-fill` : elles passent
  de quatre colonnes à une sans seuil à écrire ;
- sur `suit_vue3.php`, la rangée « produit / commentaires / commandes »
  passe de trois colonnes à deux puis à une ;
- les tableaux de l'administration défilent horizontalement plutôt que
  d'élargir la page ;
- la page de connexion garde son animation de panneaux sur grand écran ;
  en dessous de 860 px les `transform` écrits par le script sont annulés
  et les deux panneaux se superposent.

Vérifié : **aucun débordement horizontal** sur les 11 pages, de 320 px
à 1600 px de large.

### Autres changements

- Les blocs `<style>` écrits dans les pages sont devenus de vraies
  feuilles : `css/connexion.css`, `css/panier.css`, `css/profil.css`.
- Les `style="…"` en `vw` dans le HTML ont été retirés. Le filtre de
  type sélectionné utilise maintenant une classe `.actif`, et les
  messages « il n'y a rien ici » la classe `.vide`.
- `css/tete.css` et `css_v1/bar_vue1.css` pointaient vers `../photo/…`,
  un dossier qui n'est pas envoyé en ligne : le bandeau utilise
  maintenant une image de `images/`, et la capture d'écran qui servait
  de fond de carte a été remplacée par un aplat.
- Les formulaires du vendeur (modifier le profil, ajouter un produit)
  ne s'ouvrent plus au survol : ils sont toujours visibles. Un survol
  est impossible sur un écran tactile.
- Les champs, boutons, pastilles d'état et messages ont la même allure
  partout, décrite une seule fois dans `css/design.css`.

### Ce qui n'a pas changé

Aucun nom de classe ni d'identifiant n'a été renommé (sauf `#acceul`
devenu `#accueil`), aucune requête ni aucun script n'a été modifié : les
pages PHP n'ont vu que le retrait des `style=` et l'ajout des `<link>`.

## Liens retirés de la barre du haut (11e passe)

Le champ « langue » et le lien « aide et support » ne menaient nulle part :
ils ont été retirés des six pages plutôt que de laisser des commandes
mortes à l'écran. Le style du champ « langue » a été retiré des trois
fichiers `header_vueN.css` en même temps.

Sur les pages visiteur (`vue1.php`, `suit_vue1.php`) c'était tout ce que
contenait la barre du haut : il n'y reste donc que le logo. Les liens
vers la connexion et les inscriptions sont dans la colonne de gauche.

## V-STORE : nom et logo (12e passe)

L'application s'appelle désormais **V-STORE**. Avant, elle s'appelait
« service boutique » dans les titres et « service box » dans le bandeau
d'accueil — deux noms pour la même chose.

### Ce qui a été renommé

| où | avant | après |
| --- | --- | --- |
| bandeau d'accueil | `service box` | `V-STORE` |
| page de connexion (3 endroits) | `service boutique` | `V-STORE` |
| connexion de l'administrateur | `Service Boutique` | `V-STORE` |
| titres des onglets | `boîte`, `panier`, `connexion`… | `V-STORE`, `mon panier — V-STORE`, `connexion — V-STORE`… |

### Ce qui n'a PAS été renommé, et pourquoi

Le dossier, la base de données, le dépôt GitHub et les fichiers `.sql`
gardent le nom `service_boutique`. Les renommer casserait la base déjà
importée chez l'hébergeur, le fichier `config/server.local.php` et
l'adresse du dépôt, sans rien apporter : ce sont des noms techniques que
personne ne voit sur le site.

### Le logo

Un monogramme **VS** : une tuile arrondie en dégradé (les deux bleus
ardoise de la palette) avec le V et le S tracés en blanc.

Le V et le S sont dessinés **au trait** (`<path>`), pas écrits avec une
police. Le logo est donc identique partout, même si la police Inter ne
se charge pas, et reste net à n'importe quelle taille.

| fichier | rôle |
| --- | --- |
| `images/logo.svg` | le logo affiché dans les pages |
| `images/favicon.svg` | même marque, lettres plus grandes et trait plus épais pour les petites tailles |
| `images/favicon-32.png` | secours pour les navigateurs qui ignorent les favicons SVG |
| `images/favicon-180.png` | icône d'écran d'accueil sur iPhone et iPad |

Les deux PNG sont fabriqués à partir de la même géométrie par le script
`faire_icones.php` (dessin en 4× puis réduction, ce qui lisse les
bords) : ils ne peuvent pas se désynchroniser du SVG.

### Où le logo apparaît

- barre du haut des sept pages qui en ont une ;
- page de connexion, au-dessus du titre de chacun des deux panneaux ;
- carte de connexion de l'administrateur et barre de son tableau de bord ;
- formulaires d'inscription de l'acheteur et de la boutique.

Et dans l'onglet du navigateur, par trois `<link rel="icon">` posés sur
les douze pages.

`images/logo.jpg`, la photo qui servait de logo dans la barre du haut,
n'est plus utilisée : elle est sortie de la liste des images envoyées en
ligne.

## Pas encore fait (fonctionnalités jamais développées)

Les boutons existent dans la maquette mais personne ne les a encore programmés :

- paiement en ligne (les commandes sont payées à la livraison pour le moment) ;
- « like » des commentaires.
