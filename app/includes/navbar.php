<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand font-weight-bold" href="index.php">Chambre des Clés</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'reservations' ? 'active' : '' ?>" href="?tab=reservations">Réservations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'clients' ? 'active' : '' ?>" href="?tab=clients">Clients</a>
                </li>
            </ul>
            <span class="navbar-text text-white-50">
                Se connecter
            </span>
        </div>
    </div>
</nav>
