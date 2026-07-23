<?php
// logout.php - Déconnexion de la session utilisateur

session_start();
session_destroy();
header('Location: login.php');
exit;
