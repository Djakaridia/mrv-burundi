<?php
// exporter.php - Gère l'export des rapports MRV

// 1. Configuration et sécurité
require_once 'config.php'; // Inclure la configuration de la DB
session_start();

// Vérifier l'authentification (à adapter)
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Accès non autorisé");
}

// 2. Récupération des données
$rapportId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING);

if (!$rapportId || !$format) {
    header("HTTP/1.1 400 Bad Request");
    exit("Paramètres invalides");
}

// 3. Connexion à la base de données
try {
    $pdo = new PDO("pgsql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);

    // Récupération du rapport avec jointure
    $query = "SELECT r.*, p.nom as projet_nom, p.code as projet_code 
              FROM t_rapports_periodiques r
              LEFT JOIN t_projets p ON r.projet_id = p.id
              WHERE r.id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $rapportId]);
    $rapport = $stmt->fetch(PDO::ATTR_DEFAULT_FETCH_MODE);

    if (!$rapport) {
        header("HTTP/1.1 404 Not Found");
        exit("Rapport non trouvé");
    }

    // 4. Simulation de données complémentaires (à remplacer par vos vraies données)
    $indicateurs = [
        'Émissions CO2' => ['valeur' => '1245 t', 'evolution' => '-5% vs période précédente'],
        'Consommation énergie' => ['valeur' => '5.2 GWh', 'evolution' => '+2% vs période précédente'],
        'Intensité carbone' => ['valeur' => '0.45 tCO2/MWh', 'evolution' => '-8% vs période précédente']
    ];

    $emissions_par_secteur = [
        'Énergie' => 45,
        'Transport' => 30,
        'Procédés industriels' => 15,
        'Déchets' => 10
    ];

    // 5. Génération du contenu selon le format demandé
    switch (strtolower($format)) {
        case 'pdf':
            exportPDF($rapport, $indicateurs, $emissions_par_secteur);
            break;
            
        case 'excel':
            exportExcel($rapport, $indicateurs, $emissions_par_secteur);
            break;
            
        case 'word':
            exportWord($rapport, $indicateurs, $emissions_par_secteur);
            break;
            
        default:
            header("HTTP/1.1 400 Bad Request");
            exit("Format non supporté");
    }

} catch (PDOException $e) {
    header("HTTP/1.1 500 Internal Server Error");
    exit("Erreur de base de données: " . $e->getMessage());
}

// 6. Fonctions d'export spécifiques
function exportPDF($rapport, $indicateurs, $emissions_par_secteur) {
    require_once 'vendor/autoload.php'; // Pour mPDF ou TCPDF

    // Création du PDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font_size' => 10,
        'default_font' => 'helvetica'
    ]);

    // Entête du document
    $html = '
    <style>
        .header { border-bottom: 2px solid #007bff; margin-bottom: 20px; }
        .title { color: #007bff; font-size: 18pt; }
        .subtitle { color: #6c757d; font-size: 12pt; }
        .indicator-card { border: 1px solid #dee2e6; border-radius: 5px; padding: 10px; margin-bottom: 15px; }
        .badge { background-color: #007bff; color: white; padding: 3px 8px; border-radius: 3px; font-size: 0.8em; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8f9fa; text-align: left; padding: 8px; border: 1px solid #dee2e6; }
        td { padding: 8px; border: 1px solid #dee2e6; }
    </style>

    <div class="header">
        <div class="title">Rapport MRV: ' . htmlspecialchars($rapport['intitule']) . '</div>
        <div class="subtitle">Période: ' . htmlspecialchars($rapport['periode']) . ' | ' 
                          . htmlspecialchars($rapport['mois_ref']) . ' ' . htmlspecialchars($rapport['annee_ref']) . '</div>
    </div>';

    // Métadonnées
    $html .= '
    <table>
        <tr>
            <th width="30%">Code rapport</th>
            <td>' . htmlspecialchars($rapport['code']) . '</td>
        </tr>
        <tr>
            <th>Projet associé</th>
            <td>' . htmlspecialchars($rapport['projet_nom'] ?? 'Non spécifié') . ' (' 
                 . htmlspecialchars($rapport['projet_code'] ?? '') . ')</td>
        </tr>
        <tr>
            <th>Date de création</th>
            <td>' . date('d/m/Y H:i', strtotime($rapport['created_at'])) . '</td>
        </tr>
    </table>';

    // Indicateurs clés
    $html .= '<h4 style="color: #007bff; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">Indicateurs Clés</h4>';
    foreach ($indicateurs as $nom => $data) {
        $evolutionClass = strpos($data['evolution'], '+') !== false ? 'color: #dc3545;' : 'color: #28a745;';
        $html .= '
        <div class="indicator-card">
            <div style="font-weight: bold;">' . htmlspecialchars($nom) . '</div>
            <div style="font-size: 14pt;">' . htmlspecialchars($data['valeur']) . '</div>
            <div style="' . $evolutionClass . '">' . htmlspecialchars($data['evolution']) . '</div>
        </div>';
    }

    // Émissions par secteur
    $html .= '<h4 style="color: #007bff; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; margin-top: 20px;">Émissions par secteur</h4>';
    $html .= '<table>';
    $html .= '<tr><th>Secteur</th><th>Part des émissions (%)</th></tr>';
    foreach ($emissions_par_secteur as $secteur => $pourcentage) {
        $html .= '<tr><td>' . htmlspecialchars($secteur) . '</td><td>' . $pourcentage . '%</td></tr>';
    }
    $html .= '</table>';

    // Description si elle existe
    if (!empty($rapport['description'])) {
        $html .= '<h4 style="color: #007bff; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; margin-top: 20px;">Description</h4>';
        $html .= '<p>' . nl2br(htmlspecialchars($rapport['description'])) . '</p>';
    }

    // Pied de page
    $html .= '<div style="margin-top: 30px; font-size: 0.8em; color: #6c757d; text-align: center;">
                Généré le ' . date('d/m/Y H:i') . ' par le Système MRV Sectoriel
              </div>';

    // Génération du PDF
    $mpdf->WriteHTML($html);
    
    // Envoi au navigateur
    $filename = 'MRV_Rapport_' . preg_replace('/[^a-z0-9]/i', '_', $rapport['code']) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $mpdf->Output($filename, 'D');
    exit;
}

function exportExcel($rapport, $indicateurs, $emissions_par_secteur) {
    require_once 'vendor/autoload.php'; // Pour PhpSpreadsheet

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Titre du document
    $sheet->setCellValue('A1', 'Rapport MRV - ' . $rapport['intitule']);
    $sheet->mergeCells('A1:D1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    // Métadonnées
    $sheet->setCellValue('A3', 'Code rapport:');
    $sheet->setCellValue('B3', $rapport['code']);
    $sheet->setCellValue('A4', 'Projet:');
    $sheet->setCellValue('B4', $rapport['projet_nom'] . ' (' . $rapport['projet_code'] . ')');
    $sheet->setCellValue('A5', 'Période:');
    $sheet->setCellValue('B5', $rapport['periode'] . ' - ' . $rapport['mois_ref'] . ' ' . $rapport['annee_ref']);

    // Indicateurs clés
    $sheet->setCellValue('A7', 'Indicateurs Clés');
    $sheet->getStyle('A7')->getFont()->setBold(true);
    $row = 8;
    foreach ($indicateurs as $nom => $data) {
        $sheet->setCellValue('A' . $row, $nom);
        $sheet->setCellValue('B' . $row, $data['valeur']);
        $sheet->setCellValue('C' . $row, $data['evolution']);
        $row++;
    }

    // Émissions par secteur
    $sheet->setCellValue('A' . ($row + 1), 'Émissions par secteur');
    $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
    $row += 2;
    $sheet->setCellValue('A' . $row, 'Secteur');
    $sheet->setCellValue('B' . $row, 'Part (%)');
    $sheet->getStyle('A' . $row . ':B' . $row)->getFont()->setBold(true);
    $row++;
    foreach ($emissions_par_secteur as $secteur => $pourcentage) {
        $sheet->setCellValue('A' . $row, $secteur);
        $sheet->setCellValue('B' . $row, $pourcentage);
        $row++;
    }

    // Description
    if (!empty($rapport['description'])) {
        $sheet->setCellValue('A' . ($row + 1), 'Description');
        $sheet->getStyle('A' . ($row + 1))->getFont()->setBold(true);
        $sheet->setCellValue('A' . ($row + 2), $rapport['description']);
        $sheet->mergeCells('A' . ($row + 2) . ':D' . ($row + 5));
    }

    // Formatage automatique des colonnes
    foreach (range('A', 'D') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Génération du fichier
    $filename = 'MRV_Rapport_' . preg_replace('/[^a-z0-9]/i', '_', $rapport['code']) . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function exportWord($rapport, $indicateurs, $emissions_par_secteur) {
    require_once 'vendor/autoload.php'; // Pour PHPWord

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    // Titre du document
    $section->addText(
        'Rapport MRV - ' . $rapport['intitule'],
        ['name' => 'Arial', 'size' => 16, 'bold' => true],
        ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
    );

    // Métadonnées
    $section->addTextBreak(1);
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(2000)->addText('Code rapport:', ['bold' => true]);
    $table->addCell(4000)->addText($rapport['code']);
    $table->addRow();
    $table->addCell(2000)->addText('Projet:', ['bold' => true]);
    $table->addCell(4000)->addText($rapport['projet_nom'] . ' (' . $rapport['projet_code'] . ')');
    $table->addRow();
    $table->addCell(2000)->addText('Période:', ['bold' => true]);
    $table->addCell(4000)->addText($rapport['periode'] . ' - ' . $rapport['mois_ref'] . ' ' . $rapport['annee_ref']);

    // Indicateurs clés
    $section->addTextBreak(1);
    $section->addText('Indicateurs Clés', ['bold' => true, 'size' => 12]);
    foreach ($indicateurs as $nom => $data) {
        $section->addText($nom . ': ' . $data['valeur'] . ' (' . $data['evolution'] . ')');
    }

    // Émissions par secteur
    $section->addTextBreak(1);
    $section->addText('Émissions par secteur', ['bold' => true, 'size' => 12]);
    $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
    $table->addRow();
    $table->addCell(3000)->addText('Secteur', ['bold' => true]);
    $table->addCell(2000)->addText('Part (%)', ['bold' => true]);
    foreach ($emissions_par_secteur as $secteur => $pourcentage) {
        $table->addRow();
        $table->addCell(3000)->addText($secteur);
        $table->addCell(2000)->addText($pourcentage);
    }

    // Description
    if (!empty($rapport['description'])) {
        $section->addTextBreak(1);
        $section->addText('Description', ['bold' => true, 'size' => 12]);
        $section->addText($rapport['description']);
    }

    // Pied de page
    $section->addTextBreak(2);
    $section->addText(
        'Généré le ' . date('d/m/Y H:i') . ' par le Système MRV Sectoriel',
        ['size' => 9, 'color' => '999999'],
        ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT]
    );

    // Génération du fichier
    $filename = 'MRV_Rapport_' . preg_replace('/[^a-z0-9]/i', '_', $rapport['code']) . '.docx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save('php://output');
    exit;
}