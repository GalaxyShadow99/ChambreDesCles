#!/bin/bash
# dropDB.sh - Supprimer la base de données locale (volume persistant)

echo "Attention : Cette opération va supprimer toutes les données de la base locale !"
read -p "Voulez-vous continuer ? (y/N) " -n 1 -r
echo
if [ "$REPLY" = "y" ] || [ "$REPLY" = "Y" ]
then
    echo "Arrêt et suppression du volume de base de données..."
    docker compose down -v
    echo "⚠️  Vous allez maintenant supprimer TOUTES les images Docker et tous les volumes. Cette opération est irréversible et supprimera toutes les données de L'ENTIERETE des conteneurs."
    echo "C'est à ne faire que pour réinitialiser un environnement de développement !!"
    read -p "Confirmez la purge complète Docker (y/N) " -n 1 -r
    echo
    if [ "$REPLY" = "y" ] || [ "$REPLY" = "Y" ]; then
        echo "Purge complète des images et volumes Docker en cours..."
        docker system prune -a --volumes -f
    else
        echo "Purge Docker annulée."
    fi
else
    echo "Opération annulée."
fi
