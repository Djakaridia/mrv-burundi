<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Détails Mesure | MRV - Burundi</title>
    <?php include './components/navbar & footer/head.php'; ?>
    <?php
    $mesure_id = $_GET['id'] ?? 0;
    $mesure = new Mesure($db);
    $mesure->id = $mesure_id;
    $mesure_curr = $mesure->readById();

    if (!$mesure_curr) {
        echo ('<script>window.location.href = "mesures.php"</script>');
        exit();
    }

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $gaz = new Gaz($db);
    $gazs = $gaz->read();
    $gaz_colors = [];
    foreach ($gazs as $g) {
        $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);
    }

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs_mesure = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });

    // $mesure_curr = [
    //     'id' => $mesure_id,
    //     'name' => 'Production de l\'énergie hydroélectrique',
    //     'description' => 'Mises en service des centrales hydroélectriques',
    //     'objectif' => 'Accroitre la capacité de production de l\'énergie hydroélectrique',
    //     'status' => 'Prévu/réalisé',
    //     'secteur_id' => 'Energie',
    //     'gaz' => 'CO2, N2O, CH4',
    //     'start_date' => 2022,
    //     'structure_id' => 'Ministère de l\'Hydraulique, de l\'Energie et des Mines, REGIDESO, Direction Générale de l\'Energie, ABER',
    //     'valeur_estimee' => '97,045',
    //     'valeur_realise' => '198,028',
    //     'valeur_cible' => '',
    //     'progression' => 72,
    //     'realise_co2' => 324,
    //     'prevision_2025_co2' => 450,
    //     'repartition_ges' => [
    //         'CO2' => 85,
    //         'CH4' => 12,
    //         'N2O' => 3
    //     ],
    // ];
    ?>

    <style>
        .gauge-conic {
            background: conic-gradient(from 180deg, #13ec5b 0% <?= $mesure_curr['progression'] ?? 0 ?>%, #1c2e22 <?= $mesure_curr['progression'] ?? 0 ?>% 100%)
        }
    </style>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">

            <div class="mx-n4 mt-n2 mb-5">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge badge-phoenix badge-phoenix-info rounded-pill">
                                <?php foreach ($secteurs_mesure as $secteur) : ?>
                                    <?php if ($secteur['id'] == $mesure_curr['secteur_id']) : ?>
                                        <?= $secteur['name'] ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </span>
                            <span class="badge badge-phoenix badge-phoenix-primary rounded-pill">
                                <?= $mesure_curr['status'] ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-end gap-1">
                            <button type="button" onclick="window.location.href=`<?= $_SERVER['PHP_SELF'] ?>`" class="btn btn-subtle-primary btn-sm">
                                <span class="fa fa-arrow-left fs-9 me-2"></span>Retour
                            </button>

                            <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info" data-bs-toggle="modal"
                                    data-bs-target="#addMesureModal" data-id="<?= $mesure_curr['id'] ?>">
                                    <span class="uil-pen fs-9"></span>
                                </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'delete', 2)) : ?>
                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger" type="button"
                                    onclick="deleteData(<?= $mesure_curr['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette mesure ?', 'mesures')">
                                    <span class="uil-trash-alt fs-9"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h2 class="h3 fw-bold mb-2"><?= $mesure_curr['name'] ?></h2>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card mb-3 rounded-1 overflow-hidden">
                            <div class="card-header bg-primary-subtle border-bottom p-2 rounded-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fa fa-info-circle text-primary"></span>
                                    <h3 class="h6 mb-0 fw-bold">Description</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <p class="text-body mb-0">
                                    <?= $mesure_curr['description'] ?>
                                </p>
                            </div>
                        </div>

                        <div class="card mb-3 rounded-1 overflow-hidden">
                            <div class="card-header bg-primary-subtle border-bottom p-2 rounded-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fa fa-bullseye text-primary"></span>
                                    <h3 class="h6 mb-0 fw-bold">Objectifs</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- <ul class="list-unstyled mb-0">
                                    <php foreach ($mesure_curr['objectifs_details'] as $objectif): ?>
                                        <li class="d-flex align-items-start gap-3 mb-3">
                                            <span class="fa fa-check-circle text-primary mt-1"></span>
                                            <span class="text-body"><?= $objectif ?></span>
                                        </li>
                                    <php endforeach; ?>
                                </ul> -->
                                <p class="text-body mb-0"><?= $mesure_curr['objectif'] ?></p>
                            </div>
                        </div>

                        <div class="card rounded-1 mt-3">
                            <div class="card-header bg-primary-subtle border-bottom p-2 rounded-0">
                                <h3 class="h6 mb-0 fw-bold">Informations techniques</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <p class="text-uppercase text-muted small fw-bold mb-1">Année de début</p>
                                        <p class="fw-semibold mb-0"><?= $mesure_curr['annee_debut'] ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-uppercase text-muted small fw-bold mb-1">Gaz concernés</p>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (explode(',', $mesure_curr['gaz']) as $gaz) : ?>
                                                <span class="badge rounded-1" style="background-color: <?php echo $gaz_colors[strtoupper($gaz)] ?? '#6c757d'; ?>; color: white;">
                                                    <?php echo $gaz; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-uppercase text-muted small fw-bold mb-1">Estimation évitée</p>
                                        <p class="fw-bold text-success mb-0">
                                            <?= $mesure_curr['valeur_estimee'] ?? 0 ?> Gg Eq.CO2
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card mb-3 rounded-1 overflow-hidden">
                            <div class="card-header bg-primary-subtle border-bottom p-2 rounded-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="h6 mb-0 fw-bold">Réalisations vs Prévisions</h3>
                                    <span class="badge bg-primary rounded-pill">Cible <?= date("Y") ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-center py-3">
                                    <div class="position-relative">
                                        <div class="gauge-conic rounded-circle" style="width: 200px; height: 200px;"></div>
                                        <div class="position-absolute top-50 start-50 translate-middle bg-primary-subtle rounded-circle d-flex flex-column align-items-center justify-content-center shadow-inner" style="width: 150px; height: 150px;">
                                            <span class="fs-4 fw-bold text-primary"><?= $mesure_curr['progression'] ?? 0 ?>%</span>
                                            <span class="text-uppercase text-muted small fw-bold">Progression</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4 pt-3 border-top">
                                    <div class="col-6">
                                        <p class="text-uppercase text-muted small fw-bold mb-1">Réalisé</p>
                                        <p class="h5 fw-bold mb-0">
                                            <?= number_format($mesure_curr['realise_co2'] ?? 0, 0, ',', ' ') ?> tCO2e
                                        </p>
                                    </div>
                                    <div class="col-6 text-end">
                                        <p class="text-uppercase text-muted small fw-bold mb-1">Prévision <?= date("Y") ?></p>
                                        <p class="h5 fw-bold text-body-tertiary mb-0">
                                            <?= number_format($mesure_curr['prevision_2025_co2'] ?? 0, 0, ',', ' ') ?> tCO2e
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3 rounded-1 overflow-hidden">
                            <div class="card-header bg-primary-subtle border-bottom p-2 rounded-0">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fa fa-chart-pie text-primary"></span>
                                    <h3 class="h6 mb-0 fw-bold">Répartition des GES</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="space-y-3">
                                    <?php foreach ($mesure_curr['repartition_ges'] ?? [] as $gaz => $pourcentage): ?>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                                <span class="text-body">
                                                    <?= $gaz == 'CO2' ? 'Dioxide de Carbone (CO2)' : ($gaz == 'CH4' ? 'Méthane (CH4)' : 'Protoxyde d\'Azote (N2O)') ?>
                                                </span>
                                                <span><?= $pourcentage ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar 
                                                <?= $gaz == 'CO2' ? 'bg-primary' : ($gaz == 'CH4' ? 'bg-info' : 'bg-warning') ?>"
                                                    role="progressbar"
                                                    style="width: <?= $pourcentage ?>%"
                                                    aria-valuenow="<?= $pourcentage ?>"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>

    <script>
        $(document).ready(function() {
            // Script pour le téléchargement du rapport
            $('.btn-download-report').click(function() {
                // Code pour générer et télécharger le rapport
                alert('Fonction de téléchargement du rapport à implémenter');
            });

            // Script pour la navigation responsive
            if ($(window).width() < 992) {
                // Ajouter des boutons de navigation mobile si nécessaire
            }
        });
    </script>
</body>

</html>