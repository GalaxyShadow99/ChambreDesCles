#!/bin/bash
# init-db.sh - Initialisation de la BDD conditionnelle (DEMO_DATA)

# Détection de la commande mysql ou mariadb disponible
DB_CMD="mariadb -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE}"

echo "Initialisation de la structure de la base de données..."
$DB_CMD < /app/database/script.sql

if [ "$DEMO_DATA" = "true" ]; then
    echo "Importation des données de démo..."
    $DB_CMD < /app/database/demo.sql
else
    echo "DEMO_DATA n'est pas défini à true. Aucune donnée de démo importée."
fi
