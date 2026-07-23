#  Feuille de Route & ToDo List — Chambre des Clés

Cette liste regroupe les étapes clés pour transformer l'application actuelle (visualisation simple) en un outil complet, robuste et facile à utiliser au quotidien par la mère de ton ami.

---

##  1. Gestion des Données (Création & Édition depuis l'UI)
*Actuellement, les fonctions de création/édition existent en PHP (`db.php`), mais il n'y a pas d'interface utilisateur pour les utiliser.*

- [x] **Formulaire d'ajout de client** :
  - Créer un petit formulaire modal ou une page dédiée pour saisir le Nom, Prénom, et Avis/Notes d'un nouveau client.
- [x] **Formulaire d'ajout de réservation** :
  - Créer un formulaire d'enregistrement de réservation avec sélection du client via un menu déroulant (`<select>`).
  - Validation automatique : s'assurer que la date de départ est bien après la date d'arrivée.
- [x] **Actions d'édition et suppression** :
  - Ajouter un bouton "Modifier" et "Supprimer" sur chaque ligne des tableaux.
  - Demander une confirmation de suppression (ex: *"Voulez-vous vraiment supprimer ce client et toutes ses réservations ?"*).

---

##  2. Statistiques & Vue d'ensemble (Dashboard)
*Donner des indicateurs clés en un clin d'œil dès l'arrivée sur l'application.*

- [x] **Indicateurs financiers et d'activité** :
  - Ajouter 3 petites cartes de résumé au-dessus des onglets :
    - **Revenus totaux** : Somme des prix de toutes les réservations validées.
    - **Réservations en cours** : Nombre de séjours actifs à la date d'aujourd'hui.
    - **Total clients** : Nombre de clients enregistrés.

---

##  3. Sécurité & Robustesse
- [x] **Sécurisation des Sessions PHP** :
  - Configurer les paramètres de session dans `auth.php` pour empêcher le vol de session (activer `session.cookie_httponly` et `session.cookie_secure` si HTTPS).
- [ ] **Variables de production** :
  - Modifier le fichier `.env` de production avec un couple `ADMIN_LOGIN` / `ADMIN_PASSWORD` hautement sécurisé (au lieu de `admin`/`admin`).
- [x] **Protection contre les failles CSRF** :
  - Bloquer les requêtes malveillantes avec le paramètre `SameSite=Strict` sur le cookie de session (alternative moderne et légère).

---

##  4. Déploiement & Sauvegardes
- [ ] **Script de sauvegarde de BDD automatique** :
  - Écrire un petit script cron ou une commande Docker (`docker exec`) pour exporter régulièrement la base MariaDB (`mysqldump`) dans un dossier de backup sur l'hôte, afin de ne jamais perdre les données des clients.
- [ ] **Configuration HTTPS** :
  - Mettre en place un reverse proxy simple (comme Caddy ou Nginx avec Let's Encrypt) pour sécuriser l'accès au site avec un certificat SSL gratuit.
