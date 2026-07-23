<?php
// Désactiver l'affichage des avertissements Deprecated pour ne pas corrompre le flux PDF
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 0);

require_once __DIR__ . '/includes/composer_autoload.php';
require_once __DIR__ . '/utils/db.php';
require_once __DIR__ . '/utils/reservationUtils.php';

use Dompdf\Dompdf;

// CONFIGURATION DE LA FACTURE

$cfg_brand_title       = "Chambre des Clés";
$cfg_brand_subtitle    = "Chambres d'hôtes à Caen";

$cfg_emitter_name      = "Chambre des Clés SAS";
$cfg_emitter_address   = "123 Avenue des Logements\n75000 Paris, France";
$cfg_emitter_siret     = "SIRET : 123 456 789 00012";

$cfg_invoice_label     = "Chambre d'hôtes"; // Affiché sous "Facturé à"
$cfg_main_service_name = "Hébergement en chambre d'hôtes"; 
$cfg_tax_name          = "Taxes de séjour de l'établissement";
$cfg_tax_details       = "Incluses dans le tarif";

$cfg_notes_title       = "Remarques";
$cfg_tva_label         = "TVA ?? ";

$cfg_footer_thanks     = "Merci pour votre confiance, passez un excellent séjour !";
$cfg_footer_legal      = "Chambre des Clés SAS — Capital de 10 000 € — RCS Paris B 123 456 789";

// Récupération de la réservation
$id_reservation = isset($_GET['id_reservation']) ? (int)$_GET['id_reservation'] : 0;
$res = getReservation($pdo, $id_reservation);

if (!$res) {
    // Si aucune réservation n'est fournie, on prend la dernière pour test/démo
    $all = getReservations($pdo);
    if (!empty($all)) {
        $res = end($all);
    } else {
        die("Aucune réservation trouvée dans la base de données.");
    }
}

// Calculs et formatage
$start = new DateTime($res['date_debut']);
$end = new DateTime($res['date_fin']);
$days = $start->diff($end)->days;
if ($days <= 0) {
    $days = 1;
}

$date_debut = $start->format('d/m/Y');
$date_fin = $end->format('d/m/Y');
$today = date('d/m/Y');
$prix_formate = number_format($res['prix'], 2, ',', ' ');
$nom_client = htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['nom'] ?? ''));
$plateforme = htmlspecialchars($res['plateforme']);

// Style de couleur dynamique pour le statut
$status_color = $res['valide'] ? '#047857' : '#b45309';
$status_bg = $res['valide'] ? '#d1fae5' : '#fef3c7';
$statut = $res['valide'] ? 'Validée & Confirmée' : 'En attente de paiement';

$notes_content = !empty($res['avis']) ? htmlspecialchars($res['avis']) : 'Aucune note particulière pour ce séjour.';

// Formatage de l'adresse de l'émetteur
$emitter_address_html = nl2br(htmlspecialchars($cfg_emitter_address));

// Génération du code HTML du logo si présent et existant
$logo_html = '';
if (!empty($cfg_logo_path) && file_exists($cfg_logo_path)) {
    $logo_html = '<td style="width: 70px; padding-right: 15px; vertical-align: middle;">'
               . '<img src="' . htmlspecialchars($cfg_logo_path) . '" style="width: 60px; height: 60px; border-radius: 8px;" alt="Logo" />'
               . '</td>';
}

$html = <<<HTML
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Facture #{$res['id_reservation']}</title>
        <style>
            @page {
                size: A4 portrait;
                margin: 0;
            }
            body {
                font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
				color: #1e293b;
                margin: 0;
                padding: 0;
                background-color: #ffffff;
                font-size: 13px;
                line-height: 1.5;
            }
            
            /* Top Decorative Accent Bar */
            .top-bar {
                height: 8px;
                background-color: #1e1b4b; /* Deep Indigo */
                width: 100%;
            }

            .wrapper-invoice {
                padding: 35px 45px;
            }

            /* Header Section */
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 35px;
            }
            .header-table td {
                vertical-align: top;
            }
            .brand-title {
                font-size: 24px;
                font-weight: 800;
                color: #1e1b4b;
                margin: 0;
                letter-spacing: -0.5px;
                text-transform: uppercase;
            }
            .brand-subtitle {
                font-size: 10px;
                color: #6366f1;
                font-weight: 700;
                margin: 3px 0 0 0;
                text-transform: uppercase;
                letter-spacing: 1.5px;
            }
            .invoice-tag {
                text-align: right;
            }
            .invoice-title {
                font-size: 28px;
                font-weight: 900;
                color: #0f172a;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .invoice-number {
                font-size: 13px;
                color: #64748b;
                margin: 4px 0 0 0;
                font-weight: 600;
            }

            /* Meta Card (Dates, Status) */
            .meta-card {
                width: 100%;
                border-collapse: collapse;
                background-color: #f8fafc;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                margin-bottom: 30px;
            }
            .meta-card td {
                padding: 12px 18px;
                width: 33.33%;
                border-right: 1px solid #e2e8f0;
            }
            .meta-card td:last-child {
                border-right: none;
            }
            .meta-label {
                font-size: 10px;
                text-transform: uppercase;
                color: #64748b;
                font-weight: 700;
                letter-spacing: 0.5px;
                margin-bottom: 2px;
            }
            .meta-value {
                font-size: 13px;
                font-weight: 700;
                color: #0f172a;
            }
            .status-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 700;
            }

            /* Parties Section (Émetteur / Client) */
            .parties-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 35px;
            }
            .parties-table td {
                width: 50%;
                vertical-align: top;
            }
            .party-box-left {
                padding-right: 20px;
            }
            .party-box-right {
                padding-left: 20px;
            }
            .party-title {
                font-size: 11px;
                text-transform: uppercase;
                color: #6366f1;
                font-weight: 800;
                letter-spacing: 0.8px;
                margin-bottom: 8px;
                border-bottom: 2px solid #e0e7ff;
                padding-bottom: 4px;
            }
            .party-name {
                font-size: 14px;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 4px;
            }
            .party-details {
                font-size: 12px;
                color: #475569;
                line-height: 1.6;
            }

            /* Main Table */
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 25px;
            }
            .items-table th {
                background-color: #1e1b4b;
                color: #ffffff;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 10px 14px;
                text-align: left;
            }
            .items-table th.amount-col {
                text-align: right;
            }
            .items-table td {
                padding: 14px;
                border-bottom: 1px solid #e2e8f0;
                color: #334155;
            }
            .items-table td.amount-col {
                text-align: right;
                font-weight: 600;
                color: #0f172a;
            }
            .item-desc {
                font-weight: 600;
                color: #0f172a;
                font-size: 13px;
            }
            .item-subtext {
                font-size: 11px;
                color: #64748b;
                margin-top: 3px;
            }

            /* Summary & Totals Layout */
            .summary-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 35px;
            }
            .summary-table td {
                vertical-align: top;
            }
            .notes-cell {
                width: 55%;
                padding-right: 30px;
            }
            .totals-cell {
                width: 45%;
            }

            .notes-box {
                background-color: #f8fafc;
                border-left: 3px solid #6366f1;
                padding: 12px 15px;
                border-radius: 0 6px 6px 0;
            }
            .notes-title {
                font-size: 11px;
                font-weight: 700;
                color: #1e1b4b;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            .notes-content {
                font-size: 11px;
                color: #475569;
                font-style: italic;
            }

            .totals-subtable {
                width: 100%;
                border-collapse: collapse;
            }
            .totals-subtable td {
                padding: 6px 0;
                font-size: 12px;
                color: #475569;
            }
            .totals-subtable td.val {
                text-align: right;
                font-weight: 600;
                color: #1e293b;
            }
            
            .total-banner {
                background-color: #1e1b4b;
                color: #ffffff;
                border-radius: 6px;
                padding: 12px 16px;
                margin-top: 10px;
            }
            .total-banner table {
                width: 100%;
                border-collapse: collapse;
            }
            .total-banner td {
                padding: 0;
                color: #ffffff;
            }
            .total-label {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            .total-amount {
                font-size: 18px;
                font-weight: 800;
                text-align: right;
            }

            /* Footer */
            .invoice-footer {
                margin-top: 40px;
                border-top: 1px solid #e2e8f0;
                padding-top: 20px;
                text-align: center;
                font-size: 11px;
                color: #94a3b8;
            }
            .footer-thanks {
                font-weight: 700;
                color: #475569;
                margin-bottom: 4px;
            }
        </style>
    </head>
    <body>
        <div class="top-bar"></div>
        <div class="wrapper-invoice">
            
            <!-- Header -->
            <table class="header-table">
                <tr>
                    {$logo_html}
                    <td style="vertical-align: middle;">
                        <div class="brand-title">{$cfg_brand_title}</div>
                        <div class="brand-subtitle">{$cfg_brand_subtitle}</div>
                    </td>
                    <td class="invoice-tag" style="vertical-align: middle;">
                        <div class="invoice-title">FACTURE</div>
                        <div class="invoice-number">N° #{$res['id_reservation']}</div>
                    </td>
                </tr>
            </table>

            <!-- Meta Information Card -->
            <table class="meta-card">
                <tr>
                    <td>
                        <div class="meta-label">Date d'émission</div>
                        <div class="meta-value">{$today}</div>
                    </td>
                    <td>
                        <div class="meta-label">Mode de réservation</div>
                        <div class="meta-value" style="text-transform: capitalize;">{$plateforme}</div>
                    </td>
                    <td>
                        <div class="meta-label">Statut du paiement</div>
                        <div><span class="status-badge" style="color: {$status_color}; background-color: {$status_bg};">{$statut}</span></div>
                    </td>
                </tr>
            </table>

            <!-- Parties (Émetteur & Client) -->
            <table class="parties-table">
                <tr>
                    <td class="party-box-left">
                        <div class="party-title">Émetteur</div>
                        <div class="party-name">{$cfg_emitter_name}</div>
                        <div class="party-details">
                            {$emitter_address_html}<br />
                            <span style="color: #64748b;">{$cfg_emitter_siret}</span>
                        </div>
                    </td>
                    <td class="party-box-right">
                        <div class="party-title">Facturé à</div>
                        <div class="party-name">{$nom_client}</div>
                        <div class="party-details">
                            <span style="color: #64748b;">Client ID :</span> #{$res['id_client']}<br />
                            <span style="color: #64748b;">Réservation :</span> {$cfg_invoice_label}<br />
                            <span style="color: #64748b;">Période :</span> {$days} nuits
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Prestations -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Désignation de la prestation</th>
                        <th class="amount-col">Montant HT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-desc">{$cfg_main_service_name} (Séjour de {$days} nuits)</div>
                            <div class="item-subtext">Période du {$date_debut} au {$date_fin}</div>
                        </td>
                        <td class="amount-col">{$prix_formate} €</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="item-desc">{$cfg_tax_name}</div>
                            <div class="item-subtext">{$cfg_tax_details}</div>
                        </td>
                        <td class="amount-col">0,00 €</td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals & Notes -->
            <table class="summary-table">
                <tr>
                    <td class="notes-cell">
                        <div class="notes-box">
                            <div class="notes-title">{$cfg_notes_title}</div>
                            <div class="notes-content">"{$notes_content}"</div>
                        </div>
                    </td>
                    <td class="totals-cell">
                        <table class="totals-subtable">
                            <tr>
                                <td>Sous-total HT</td>
                                <td class="val">{$prix_formate} €</td>
                            </tr>
                            <tr>
                                <td>{$cfg_tva_label}</td>
                                <td class="val">0,00 €</td>
                            </tr>
                            <tr>
                                <td>{$cfg_fees_label}</td>
                                <td class="val">0,00 €</td>
                            </tr>
                        </table>

                        <div class="total-banner">
                            <table>
                                <tr>
                                    <td class="total-label">Total TTC</td>
                                    <td class="total-amount">{$prix_formate} €</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Footer -->
            <div class="invoice-footer">
                <div class="footer-thanks">{$cfg_footer_thanks}</div>
                <div>{$cfg_footer_legal}</div>
            </div>

        </div>
    </body>
</html>
HTML;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('facture_'.$res['id_reservation'].'.pdf', ['Attachment' => false]);
?>
