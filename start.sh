#!/bin/bash

echo "Préparation des répertoires et des permissions..."
mkdir -p backups
chmod 777 backups
# chmod 777 pour que le conteneur MariaDB puisse écrire dans le dossier de sauvegardes

echo "Démarrage des conteneurs Chambre des Clés..."
docker compose up --build -d
