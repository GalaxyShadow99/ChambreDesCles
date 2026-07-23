#!/bin/bash
# dropDB.sh - Supprimer la base de données locale (volume persistant)

echo "Attention : Cette opération va supprimer toutes les données de la base locale !"
read -p "Voulez-vous continuer ? (y/N) " -n 1 -r
echo
if [ "$REPLY" = "y" ] || [ "$REPLY" = "Y" ]
then
    echo "Arrêt et suppression du volume de base de données..."
    docker compose down -v
else
    echo "Opération annulée."
fi
