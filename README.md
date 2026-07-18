# Chambre des Cles - API & Frontend PHP Vanilla

Ce projet est une demonstration d'architecture Server-Side Rendered (SSR) simplifiee en PHP Vanilla. Le frontend et l'API communiquent de serveur à serveur de maniere securisee grâce à un jeton d'authentification partage.

---

## 1. Configuration (.env)

Creez un fichier `.env` à la racine du projet avec les variables suivantes :

```ini
DB_HOST=xune.app
DB_PORT=3306
DB_USER=ch
DB_PASSWORD=votre_mot_de_passe
DB_NAME=chambredescles_db

# Token d'authentification secret partage pour l'API
API_SECRET_TOKEN=votre_cle_secrete_ici
API_URL=http://localhost:8081/api
```

---

## 2. Lancement du Projet

Le serveur de developpement de PHP (php -S) etant monothreade, vous devez lancer deux terminaux distincts pour eviter les blocages de requetes en local.

### Etape A : Lancer l'API (Backend - Port 8081)
Ouvrez un premier terminal à la racine du projet et lancez le serveur API :
```bash
php -S localhost:8081
```
L'API repondra sur http://localhost:8081/api/clients.php et http://localhost:8081/api/chambres.php.

### Etape B : Lancer le Site (Frontend - Port 8080)
Ouvrez un second terminal à la racine du projet et lancez le serveur pour le frontend :
```bash
php -S localhost:8080
```
Le site sera accessible sur http://localhost:8080.

---

## 3. Liens Utiles

* Interface utilisateur : http://localhost:8080
* API Clients (Acces securise requis) : http://localhost:8081/api/clients.php
* API Chambres (Acces securise requis) : http://localhost:8081/api/chambres.php
* Script SQL de la base de donnees : database/script.sql