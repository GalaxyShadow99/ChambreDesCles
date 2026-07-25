<?php
// Définit le fuseau horaire en se basant sur la variable TZ de Docker (ou Europe/Paris par défaut)
date_default_timezone_set(getenv('TZ') ?: 'Europe/Paris');

// Tente de charger l'autoloader depuis le chemin hôte ou container
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php'; // Hôte (Thomas/Bureau)
} else {
    require_once __DIR__ . '/../vendor/autoload.php';   // Conteneur Docker (/var/www/html)
}
?>
