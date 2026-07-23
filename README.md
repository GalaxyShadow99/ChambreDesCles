# Chambre des Clés

## Présentation

*Chambre des Clés* est une application web légère pour la gestion de réservations de locations courte durée. Elle propose un tableau de bord administratif, des listes de clients et de réservations, ainsi que la génération de factures PDF pour chaque réservation.

Le projet est développé en PHP natif, utilise **Dompdf** pour la génération de PDF, et inclut un environnement Docker minimal pour faciliter le déploiement.

---

## Fonctionnalités

- Tableau de bord affichant les statistiques clés (réservations en cours, futures, etc.)
- Listes CRUD pour les clients et les réservations
- Génération automatique de factures PDF avec un modèle propre et réactif
- Gestion sécurisée des sessions avec les drapeaux `SameSite=Strict`, `HttpOnly` et `Secure`
- Gestion des dépendances via Composer, isolées dans le répertoire `vendor/`
- Image Docker minimale basée sur `php:8.2-apache` avec uniquement les extensions nécessaires

---

## Prérequis

- PHP >= 8.0
- Composer (pour installer les dépendances)
- Docker (optionnel, pour le déploiement en conteneur)
- MariaDB

---

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/yourusername/ChambreDesCles.git
   cd ChambreDesCles
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```
   Cette commande télécharge Dompdf et ses bibliothèques dans le répertoire `vendor/`.

3. **Configurer la base de données**
   - Créez une base MySQL (par ex. `chambre_des_cles`).
   - Mettez à jour les identifiants dans `app/utils/db.php`.

4. **Configurer le serveur web**
   - Sous Apache, pointez le `DocumentRoot` vers le répertoire `app/`.
   - Activez `mod_rewrite` pour permettre le routage via le fichier `.htaccess`.

---

## Utilisation

- Accédez à l'application via `http://localhost:8080` (ou l’hôte approprié).
- Le tableau de bord (`app/index.php`) présente un aperçu des réservations.
- Naviguez vers les pages **Clients** et **Réservations** pour gérer les données.
- Depuis la page de détail d’une réservation, cliquez sur **« Imprimer la facture (PDF) »** pour générer et visualiser la facture.

---

## Déploiement Docker (optionnel)

Une configuration Docker minimale est fournie.

1. **Construire l'image**
   ```bash
   docker compose build
   ```

2. **Lancer le conteneur**
   ```bash
   docker compose up -d
   ```

   Le conteneur mappe le répertoire `app/` vers `/var/www/html` et monte le répertoire `vendor/` afin que les bibliothèques installées via Composer soient disponibles.

--- 

## Licence

Ce projet est sous licence MIT. Consultez le fichier `LICENSE` pour plus de détails.