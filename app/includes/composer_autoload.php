<?php
// Tente de charger l'autoloader depuis le chemin hôte ou container
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php'; // Hôte (Thomas/Bureau)
} else {
    require_once __DIR__ . '/../vendor/autoload.php';   // Conteneur Docker (/var/www/html)
}
?>
