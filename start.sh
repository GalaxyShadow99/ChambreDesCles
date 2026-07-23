#!/bin/bash
# start.sh - Démarrer les conteneurs Docker

echo "Démarrage des conteneurs Chambre des Clés..."
docker compose up --build -d
