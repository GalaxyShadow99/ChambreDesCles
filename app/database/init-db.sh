#!/bin/bash
# init-db.sh - Initialisation de la BDD conditionnelle (Restauration Backup ou Schema/Demo)

DB_CMD="mariadb -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE}"
BACKUP_FILE="/var/backups/latest.${MYSQL_DATABASE}.sql.gz"

if [ -f "$BACKUP_FILE" ]; then
    echo "Une sauvegarde récente a été détectée dans les volumes : $BACKUP_FILE"
    echo "Restauration de la base de données à partir de la sauvegarde..."
    gunzip -c "$BACKUP_FILE" | $DB_CMD
    echo "Restauration terminée avec succès !"
else
    echo "Aucune sauvegarde détectée ($BACKUP_FILE)."
    echo "Initialisation de la structure de la base de données de zéro..."
    $DB_CMD < /app/database/script.sql

    if [ "$DEMO_DATA" = "true" ]; then
        echo "Importation des données de démo..."
        $DB_CMD < /app/database/demo.sql
    else
        echo "DEMO_DATA n'est pas défini à true. Aucune donnée de démo importée."
    fi
fi
