<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/utils/db.php';

$backupDir = '/var/backups';
$message = '';
$error = '';

// Déclenchement de la restauration via URL (GET) -> stockage en session -> redirection
if (isset($_GET['action']) && $_GET['action'] === 'restore' && isset($_GET['file'])) {
    $_SESSION['restore_file'] = $_GET['file'];
    header('Location: backup.php');
    exit;
}

// Traitement de la restauration si la session contient un fichier
if (!empty($_SESSION['restore_file'])) {
    $fileToRestore = $_SESSION['restore_file'];
    unset($_SESSION['restore_file']); // suppr la var de session 
    
    $selectedFile = basename($fileToRestore);
    $filePath = $backupDir . '/' . $selectedFile;

    if (!file_exists($filePath)) {
        $error = "Le fichier de sauvegarde spécifié n'existe pas.";
    } else {
        $cmd = sprintf(
            "mariadb --skip-ssl -h %s -u %s -p%s %s < %s 2>&1",
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($filePath)
        );
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        if ($returnVar === 0) {
            $message = "La base de données a été restaurée avec succès à partir de : " . htmlspecialchars($selectedFile);
        } else {
            $error = "Erreur lors de la restauration (code $returnVar) : " . print_r($output, true) ;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'backup'){
    $_SESSION['newBackupFileName'] = $_GET['newBackupFileName'] ?? date('YmdHi') . '.' . $db . '.sql';
    header('Location: backup.php');
    exit;
}

// Traitement de la sauvegarde si la session contient un nom de fichier
if (!empty($_SESSION['newBackupFileName'])) {
    $fileToBackup = $_SESSION['newBackupFileName'];
    unset($_SESSION['newBackupFileName']); // suppr la var de session 
    
    $selectedFile = basename($fileToBackup);
    $filePath = $backupDir . '/' . $selectedFile;

    if (file_exists($filePath)) {
        $error = "Le fichier de sauvegarde spécifié existe déjà.";
    } else {
        // Correction : utilise $selectedFile au lieu de la variable de session qui vient d'être unset
        $cmd = sprintf(
            "mariadb-dump -h %s -u %s -p%s %s > %s 2>&1",
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($db),
            escapeshellarg($filePath)
        );
        $output = [];
        $returnVar = 0;
        exec($cmd, $output, $returnVar);

        if ($returnVar === 0) {
            $message = "La base de données a été sauvegardée avec succès sous le nom : " . htmlspecialchars($selectedFile);
            try{
                if(file_exists("/var/backups/latest.chambredescles_db.sql")) {
                    unlink("/var/backups/latest.chambredescles_db.sql"); // Supprime le lien symbolique existant
                }
                symlink('/var/backups/' . $selectedFile, '/var/backups/latest.chambredescles_db.sql'); // Crée un nouveau lien symbolique vers la dernière sauvegarde
            } catch (Exception $e) {
                $error = "Erreur lors de la mise à jour du lien symbolique vers la dernière sauvegarde : " . $e->getMessage();
            }   
        } 
        elseif ($returnVar === 2) {
            $error = "Dossier monté en lecture seule, impossible de créer la sauvegarde.";
        }
        else {
            $error = "Erreur lors de la sauvegarde (code $returnVar) : " . print_r($output, true);
        }
    }
}

// Lecture et listing des fichiers de sauvegarde
$data = [];
$dir = new DirectoryIterator($backupDir);
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $data[] = [
            'filename' => $fileinfo->getFilename(),
            'date' => $fileinfo->getMTime()
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<?php include_once __DIR__ . '/includes/head.php'; ?>
<body class="bg-light">

<?php include_once __DIR__ . '/includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h5 mb-0 fw-bold">Gestion des sauvegardes BDD</h2>
        <a href="index.php" class="btn btn-secondary btn-sm fw-medium">Retour à l'accueil</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><?= nl2br($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm" style="border: 1px solid #d0d7de !important; border-radius: 6px; background-color: #ffffff;">
        <div class="card-header bg-light border-bottom py-3">
            <h3 class="h6 mb-0 fw-bold text-secondary">Fichiers de sauvegarde disponibles</h3>
        </div>
        <div class="card-body p-0">
            <?php if (empty($data)): ?>
                <div class="p-4 text-center text-muted">Aucune sauvegarde trouvée dans <code><?= htmlspecialchars($backupDir) ?></code>.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle sortable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 text-start">Nom du fichier</th>
                                <th class="text-center">Date de création</th>
                                <th class="text-center pe-4" style="width: 150px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $backup): ?>
                                <tr>
                                    <td class="ps-4 text-start font-monospace fw-semibold text-dark"><?= htmlspecialchars($backup['filename']) ?></td>
                                    <td class="text-center text-secondary"><?= date('d/m/Y H:i:s', $backup['date']) ?></td>
                                    <td class="text-center pe-4">
                                        <a href="backup.php?action=restore&file=<?= urlencode($backup['filename']) ?>" 
                                           class="btn btn-sm btn-outline-danger fw-semibold w-100" 
                                           onclick="return confirm('Êtes-vous sûr de vouloir écraser la base de données par cette sauvegarde ?');">
                                            Restaurer
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                </div>
            

            <?php endif; ?>
        </div>
        <div class="p-3 border-top d-flex justify-content-end">
                <a href="backup.php?action=backup&newBackupFileName=<?= date('YmdHi') . '.' . $db ?>.sql" 
                    class="btn btn-sm btn-outline-primary fw-semibold" 
                    onclick="return confirm('Êtes-vous sûr de vouloir créer une nouvelle sauvegarde ?');">
                    Créer une nouvelle sauvegarde
                </a>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
