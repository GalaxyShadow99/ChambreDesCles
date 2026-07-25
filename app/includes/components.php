<?php
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
