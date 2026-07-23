<?php
// app/includes/components.php - Composants de style React en PHP Pur (KISS)

/**
 * Composant de Navigation Tabs - Utilise la syntaxe HEREDOC (pas d'échappement nécessaire)
 */
function NavigationTabs($props) {
    $currentTab = $props['currentTab'] ?? 'reservations';
    $resActive = ($currentTab === 'reservations') ? 'active' : '';
    $cliActive = ($currentTab === 'clients') ? 'active' : '';

    return <<<HTML
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link $resActive" href="?tab=reservations">Réservations</a>
        </li>
        <li class="nav-item">
            <a class="nav-link $cliActive" href="?tab=clients">Clients</a>
        </li>
    </ul>
    HTML;
}

/**
 * Composant Badge de Statut pour les Réservations - Style subtil et moderne
 */
function StatusBadge($props) {
    $isValid = (bool)($props['valide'] ?? false);
    $class = $isValid 
        ? 'bg-success-subtle text-success-emphasis border border-success-subtle' 
        : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
    $label = $isValid ? 'Validée' : 'En attente';

    return <<<HTML
    <span class="badge $class px-2 py-1">$label</span>
    HTML;
}
