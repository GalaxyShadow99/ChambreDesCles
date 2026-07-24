<?php
// évite les conneries de headers already sent
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

require_once __DIR__ . '/includes/composer_autoload.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';

try {
    $reservations = getReservations($pdo);
} catch (Exception $e) {
    die("Erreur lors de la récupération des réservations : " . $e->getMessage());
}

$filename = 'ChambreDesCles - Reservation ' . date('Y-m-d') . '.xlsx';
header('Content-Disposition: attachment; filename="'.\XLSXWriter::sanitize_filename($filename).'"');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Transfer-Encoding: binary');

$writer = new \XLSXWriter();
$writer->setAuthor('Chambre des Clés');
$writer->setTitle('Export des Réservations');

$sheet_name = 'Réservations';
$header_types = [
    'ID'          => 'string',
    'Client'      => 'string',
    'Arrivée'     => 'date',
    'Départ'      => 'date',
    'Nuits'       => 'integer',
    'Prix Total'  => 'euro',
    'Plateforme'  => 'string',
    'Statut'      => 'string'
];

$col_options = [
    'suppress_row'   => false,
    'widths'         => [10, 25, 14, 14, 10, 15, 20, 15],
    'freeze_rows'    => 1,
    'auto_filter'    => true
];

// Style de la ligne d'en-tête
$header_style = [
    'font'       => 'Segoe UI',
    'font-size'  => 11,
    'font-style' => 'bold',
    'color'      => '#ffffff',
    'fill'       => '#2f5597',
    'halign'     => 'center',
    'valign'     => 'center',
    'border'     => 'left,right,top,bottom'
];

$writer->writeSheetHeader($sheet_name, $header_types, $col_options);

$writer = new \XLSXWriter();
$writer->setAuthor('Chambre des Clés Admin');
$writer->setTitle('Export des Réservations');
$writer->writeSheetHeader($sheet_name, $header_types, array_merge($col_options, $header_style));

$row_index = 0;
foreach ($reservations as $res) {
    $fill = ($row_index % 2 === 0) ? '#ffffff' : '#f9fafb';
    
    // Calcul dynamique du nombre de nuits
    $start = new DateTime($res['date_debut']);
    $end = new DateTime($res['date_fin']);
    $nights = $start->diff($end)->days;
    
    $client_name = trim(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? ''));
    
    if ($res['valide'] == 1) {
        $status_str = 'Validée';
        $status_style = [
            'font'       => 'Segoe UI',
            'font-style' => 'bold',
            'fill'       => '#e2f0d9', // Vert pastel
            'color'      => '#385723', // Vert foncé
            'halign'     => 'center',
            'border'     => 'left,right,top,bottom'
        ];
    } else {
        $status_str = 'En attente';
        $status_style = [
            'font'       => 'Segoe UI',
            'font-style' => 'bold',
            'fill'       => '#fff2cc', // Jaune pastel
            'color'      => '#7f6000', // Marron doré
            'halign'     => 'center',
            'border'     => 'left,right,top,bottom'
        ];
    }
    
    $row_data = [
        (string)$res['id_reservation'],
        $client_name,
        $res['date_debut'],
        $res['date_fin'],
        $nights,
        (float)$res['prix'],
        $res['plateforme'],
        $status_str
    ];
    
    $row_styles = [
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'center', 'border' => 'left,right,top,bottom', 'color' => '#595959'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'left',   'border' => 'left,right,top,bottom', 'font-style' => 'bold'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'center', 'border' => 'left,right,top,bottom'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'center', 'border' => 'left,right,top,bottom'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'right',  'border' => 'left,right,top,bottom'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'right',  'border' => 'left,right,top,bottom'],
        ['font' => 'Segoe UI', 'fill' => $fill, 'halign' => 'left',   'border' => 'left,right,top,bottom'],
        $status_style
    ];
    
    $writer->writeSheetRow($sheet_name, $row_data, $row_styles, ['height' => 20]);
    $row_index++;
}

$data_end_row = 1 + $row_index;
$total_data = [
    "TOTAL",
    "",
    "",
    "",
    "=SUM(E2:E" . $data_end_row . ")",
    "=SUM(F2:F" . $data_end_row . ")",
    "",
    ""
];

$total_style_cell = [
    'font'       => 'Segoe UI',
    'font-style' => 'bold',
    'fill'       => '#b4c6e7', // Bleu-gris pour démarquer le total
    'border'     => 'left,right,top,bottom',
    'halign'     => 'right'
];

$total_styles = [
    ['font' => 'Segoe UI', 'font-style' => 'bold', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom', 'halign' => 'center'],
    ['font' => 'Segoe UI', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom'],
    ['font' => 'Segoe UI', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom'],
    ['font' => 'Segoe UI', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom'],
    $total_style_cell,
    $total_style_cell,
    ['font' => 'Segoe UI', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom'],
    ['font' => 'Segoe UI', 'fill' => '#b4c6e7', 'border' => 'left,right,top,bottom']
];

$writer->writeSheetRow($sheet_name, $total_data, $total_styles, ['height' => 24]);
$writer->markMergedCell($sheet_name, 1 + $row_index, 0, 1 + $row_index, 3);

// envoie directement le fichier Excel au navigateur sans le sauvegarder sur le serveur
$writer->writeToStdOut();
exit(0);
