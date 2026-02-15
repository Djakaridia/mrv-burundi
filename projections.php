<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-action_type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Projections des Émissions | MRV - Burundi</title>
    <?php include './components/navbar & footer/head.php'; ?>

    <?php
    $database = new Database();
    $db = $database->getConnection();

    $projection = new Projection($db);
    $projections_data = $projection->read();

    $referentiel = new Referentiel($db);
    $referentiels = $referentiel->read();
    $referentiels_projection = array_filter($referentiels, function ($referentiel) {
        return $referentiel['state'] == 'actif';
    });

    $unite = new Unite($db);
    $unites = $unite->read();

    $secteur = new Secteur($db);
    $secteurs = $secteur->read();
    $secteurs_projection = array_filter($secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });

    $annees_cibles = [2020, 2025, 2030, 2035];
    if (!empty($projections_data)) {
        $annees_cibles = array_unique(array_column($projections_data, 'annee'));
        sort($annees_cibles);
    }
    
    $projections_par_secteur = [];
    $projections_id_map = [];
    $totaux = [];
    $secteur_fat_id = null;

    foreach ($secteurs_projection as $sect) {
        $projections_par_secteur[$sect['id']] = [
            'name' => $sect['name'],
            'scenarios' => []
        ];

        foreach (listTypeScenario() as $key => $scenario) {
            $projections_par_secteur[$sect['id']]['scenarios'][$key] = [];
        }

        if (stripos($sect['name'], 'forêt') !== false || $sect['name'] == 'FAT') {
            $secteur_fat_id = $sect['id'];
        }
    }
    ksort($projections_par_secteur);

    foreach ($projections_data as $proj) {
        $projection_id = $proj['id'];
        $secteur_id = $proj['secteur_id'];
        $scenario = $proj['scenario'];
        $annee = (int)$proj['annee'];
        $valeur = $proj['valeur'];

        if (isset($projections_par_secteur[$secteur_id]) && in_array($annee, $annees_cibles)) {
            $projections_par_secteur[$secteur_id]['scenarios'][$scenario][$annee] = $valeur;
            $projections_id_map[$secteur_id][$scenario][$annee] = $projection_id;
        }
    }

    foreach ($annees_cibles as $annee) {
        foreach (listTypeScenario() as $key => $scenario) {
            $totaux[$key][$annee] = 0;
            foreach ($projections_par_secteur as $secteur_id => $secteur_data) {
                if (isset($secteur_data['scenarios'][$key][$annee]) && $secteur_data['scenarios'][$key][$annee] !== '') {
                    $valeur = floatval(str_replace(',', '.', $secteur_data['scenarios'][$key][$annee]));
                    $totaux[$key][$annee] += $valeur;
                }
            }
        }
    }

    $totaux_sans_fat = [];
    if ($secteur_fat_id) {
        foreach ($annees_cibles as $annee) {
            foreach (listTypeScenario() as $key => $scenario) {
                $totaux_sans_fat[$key][$annee] = $totaux[$key][$annee] ?? 0;

                if (
                    isset($projections_par_secteur[$secteur_fat_id]['scenarios'][$key][$annee]) &&
                    $projections_par_secteur[$secteur_fat_id]['scenarios'][$key][$annee] !== ''
                ) {
                    $valeur_fat = floatval(str_replace(',', '.', $projections_par_secteur[$secteur_fat_id]['scenarios'][$key][$annee]));
                    $totaux_sans_fat[$key][$annee] -= $valeur_fat;
                }
            }
        }
    } else {
        $totaux_sans_fat = $totaux;
    }
    ?>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">
            <div class="mx-0 mt-n5">
                <div class="row g-3 mx-n5 pb-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Projections</span>
                                    <i class="fa fa-chart-line text-success fs-5"></i>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="stat-value"><?= count($projections_data) ?></span>
                                    <span class="text-primary fw-medium">données</span>
                                </div>
                                <div class="mt-2">
                                    <span class="text-success small fw-bold"><?= count(listTypeScenario()) ?></span>
                                    <span class="text-muted small"> scénarios</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Période</span>
                                    <i class="fa fa-calendar-alt text-warning fs-5"></i>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="stat-value"><?= min($annees_cibles) ?>-<?= max($annees_cibles) ?></span>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <?= count($annees_cibles) ?> années
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Secteurs</span>
                                    <i class="fa fa-industry text-primary fs-5"></i>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="stat-value"><?= count($secteurs_projection) ?></span>
                                    <span class="text-primary fw-medium">secteurs</span>
                                </div>
                                <div class="mt-2 text-muted small">
                                    Émissions et absorptions
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Complétude</span>
                                    <i class="fa fa-check-circle text-info fs-5"></i>
                                </div>
                                <?php
                                $total_possible = count($secteurs_projection) * count(listTypeScenario()) * count($annees_cibles);
                                $existant = count($projections_data);
                                $pourcentage = $total_possible > 0 ? round(($existant / $total_possible) * 100) : 0;
                                ?>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="stat-value"><?= $pourcentage ?>%</span>
                                </div>
                                <div class="mt-2 text-muted small">
                                    <?= $existant ?> / <?= $total_possible ?> données
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row mx-n1 py-1 align-items-center border-bottom">
                            <div class="col-md-5">
                                <h3 class="h5 mb-0 fw-bold">Projections des émissions par secteur</h3>
                                <p class="text-muted small mb-0">Projections 2023-2040 (Gg Eq.CO₂)</p>
                            </div>
                            <div class="col-md-7">
                                <div class="d-flex justify-content-md-end gap-3">
                                    <div class="d-flex gap-1 align-items-center">
                                        <span class="form-label small">Filtrer : </span>
                                        <div style="width: 8rem !important;">
                                            <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="secteurFilter"
                                                onchange="filterProjections()">
                                                <option value="">Tous secteurs</option>
                                                <?php foreach ($secteurs_projection as $secteur): ?>
                                                    <option value="<?= $secteur['id'] ?>">
                                                        <?= htmlspecialchars($secteur['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div style="width: 8rem !important;">
                                            <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="scenarioFilter"
                                                onchange="filterProjections()">
                                                <option value="">Tous scénarios</option>
                                                <?php foreach (listTypeScenario() as $key => $scenario): ?>
                                                    <option value="<?= $key ?>"><?= $scenario ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-1">
                                        <button title="Ajouter une projection" class="btn btn-subtle-primary btn-sm d-flex align-items-center gap-2"
                                            data-bs-toggle="modal" data-bs-target="#addProjectionModal">
                                            <i class="fas fa-plus"></i> Ajouter une projection
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau principal des projections -->
                        <div class="table-responsive mx-n1 p-1 scrollbar" style="max-height: 400px; overflow-y: auto;">
                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatableNONE">
                                <thead class="bg-primary-subtle">
                                    <tr class="text-center">
                                        <th style="width: 20%; min-width: 150px;">Secteur / Scénario</th>
                                        <?php foreach ($annees_cibles as $annee): ?>
                                            <th class="border-start"><?= $annee ?></th>
                                        <?php endforeach; ?>
                                        <th style="width: 80px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projections_par_secteur as $secteur_id => $secteur_data): ?>
                                        <?php $scenarios_avec_donnees = 0;
                                        foreach ($secteur_data['scenarios'] as $scenario => $valeurs) {
                                            if (!empty(array_filter($valeurs))) {
                                                $scenarios_avec_donnees++;
                                            }
                                        }

                                        if ($scenarios_avec_donnees > 0):
                                        ?>
                                            <tr class="bg-light">
                                                <td colspan="<?= count($annees_cibles) + 2 ?>" class="fw-bold bg-light">
                                                    <i class="fas fa-wind me-2"></i> Secteur : <?= htmlspecialchars($secteur_data['name']) ?>
                                                    <span class="badge py-1 bg-info ms-2"><?= $scenarios_avec_donnees ?> scénario(s)</span>
                                                </td>
                                            </tr>

                                            <?php foreach ($secteur_data['scenarios'] as $scenario => $valeurs): ?>
                                                <?php if (!empty(array_filter($valeurs))): ?>
                                                    <tr data-secteur="<?= $secteur_id ?>" data-scenario="<?= $scenario ?>" class="secteur-<?= $secteur_id ?> scenario-<?= $scenario ?>">
                                                        <td class="ps-4">
                                                            <span class="badge py-1 bg-<?= $scenario == 'bau' ? 'warning' : ($scenario == 'wem' ? 'info' : ($scenario == 'wam' ? 'success' : 'secondary')) ?>-subtle text-dark me-1">
                                                                <?= isset(listTypeScenario()[$scenario]) ? listTypeScenario()[$scenario] : $scenario ?>
                                                            </span>
                                                        </td>
                                                        <?php foreach ($annees_cibles as $annee): ?>
                                                            <?php
                                                            $valeur = $valeurs[$annee] ?? '';
                                                            $projection_id = isset($projections_id_map[$secteur_id][$scenario][$annee]) ? $projections_id_map[$secteur_id][$scenario][$annee] : null;
                                                            $classe_valeur = '';
                                                            if ($valeur !== '') {
                                                                $val_num = floatval(str_replace(',', '.', $valeur));
                                                                if ($secteur_id == $secteur_fat_id) {
                                                                    $classe_valeur = $val_num < -1000 ? 'text-success' : ($val_num < 0 ? 'text-info' : 'text-danger');
                                                                } else {
                                                                    $classe_valeur = $val_num < 100 ? 'text-success' : ($val_num < 500 ? 'text-warning' : 'text-danger');
                                                                }
                                                            }
                                                            ?>
                                                            <td class="text-center fw-bold <?= $classe_valeur ?>">
                                                                <?php if ($valeur !== ''): ?>
                                                                    <?= number_format(floatval(str_replace(',', '.', $valeur)), 1, ',', ' ') ?>
                                                                    <?php if (checkPermis($db, 'update')): ?>
                                                                        <?php if ($projection_id): ?>
                                                                            <button class="btn btn-link btn-sm p-0 ms-2 text-decoration-none"
                                                                                title="Modifier"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#addProjectionModal"
                                                                                data-id="<?= $projection_id ?>"
                                                                                data-secteur="<?= $secteur_id ?>"
                                                                                data-scenario="<?= $scenario ?>"
                                                                                data-annee="<?= $annee ?>">
                                                                                <i class="fas fa-edit text-muted"></i>
                                                                            </button>
                                                                        <?php else: ?>
                                                                            <button class="btn btn-link btn-sm p-0 ms-2 text-decoration-none"
                                                                                title="Ajouter"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#addProjectionModal"
                                                                                data-secteur="<?= $secteur_id ?>"
                                                                                data-scenario="<?= $scenario ?>"
                                                                                data-annee="<?= $annee ?>">
                                                                                <i class="fas fa-plus text-success"></i>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <?php if (checkPermis($db, 'update')): ?>
                                                                        <button class="btn btn-link btn-sm p-0 ms-2 text-decoration-none"
                                                                            title="Ajouter"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#addProjectionModal"
                                                                            data-secteur="<?= $secteur_id ?>"
                                                                            data-scenario="<?= $scenario ?>"
                                                                            data-annee="<?= $annee ?>">
                                                                            <i class="fas fa-plus text-success"></i>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm">
                                                                <?php if (checkPermis($db, 'delete', 2)): ?>
                                                                    <button class="btn btn-sm btn-phoenix-danger fs-9 px-2 py-1"
                                                                        title="Supprimer ce scénario"
                                                                        onclick="deleteProjectionScenario(<?= $secteur_id ?>, '<?= $scenario ?>')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Section des totaux -->
                        <div class="mt-4">
                            <h5 class="mx-3 mb-3 fw-bold border-bottom pb-2">Synthèse des projections</h5>

                            <div class="row g-3 mx-0">
                                <!-- Total sans FAT -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 rounded-1 overflow-hidden">
                                        <div class="card-header rounded-0 bg-light py-2">
                                            <h6 class="mb-0 fw-bold">Émissions sans FAT</h6>
                                            <small class="text-muted">Total hors Forêts et Afforestation</small>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <thead>
                                                        <tr class="border-bottom">
                                                            <th class="small">Scénario</th>
                                                            <?php foreach ($annees_cibles as $annee): ?>
                                                                <th class="text-center small"><?= $annee ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (listTypeScenario() as $scenario_key => $scenario_label): ?>
                                                            <tr>
                                                                <td class="small"><?= $scenario_label ?></td>
                                                                <?php foreach ($annees_cibles as $annee): ?>
                                                                    <?php
                                                                    $valeur = $totaux_sans_fat[$scenario_key][$annee] ?? 0;
                                                                    $classe = $valeur > 5000 ? 'text-danger' : ($valeur > 2000 ? 'text-warning' : 'text-success');
                                                                    ?>
                                                                    <td class="text-center fw-bold small <?= $classe ?>">
                                                                        <?= number_format($valeur, 1, ',', ' ') ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Absorptions FAT -->
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 rounded-1 overflow-hidden">
                                        <div class="card-header rounded-0 bg-light py-2">
                                            <h6 class="mb-0 fw-bold">Absorptions FAT</h6>
                                            <small class="text-muted">Forêts et Afforestation</small>
                                        </div>
                                        <div class="card-body p-2">
                                            <?php if ($secteur_fat_id): ?>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <thead>
                                                            <tr class="border-bottom">
                                                                <th class="small">Scénario</th>
                                                                <?php foreach ($annees_cibles as $annee): ?>
                                                                    <th class="text-center small"><?= $annee ?></th>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach (listTypeScenario() as $scenario_key => $scenario_label): ?>
                                                                <tr>
                                                                    <td class="small"><?= $scenario_label ?></td>
                                                                    <?php foreach ($annees_cibles as $annee): ?>
                                                                        <?php
                                                                        $valeur = isset($projections_par_secteur[$secteur_fat_id]['scenarios'][$scenario_key][$annee])
                                                                            ? floatval(str_replace(',', '.', $projections_par_secteur[$secteur_fat_id]['scenarios'][$scenario_key][$annee]))
                                                                            : 0;
                                                                        $classe = $valeur < -2000 ? 'text-success' : ($valeur < 0 ? 'text-info' : 'text-danger');
                                                                        ?>
                                                                        <td class="text-center fw-bold small <?= $classe ?>">
                                                                            <?= number_format($valeur, 1, ',', ' ') ?>
                                                                        </td>
                                                                    <?php endforeach; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center py-3 text-muted">
                                                    <i class="fas fa-tree fa-2x mb-2"></i>
                                                    <p class="small">Secteur FAT non identifié</p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total avec FAT -->
                                <div class="col-md-12 mb-3">
                                    <div class="card h-100 rounded-1 overflow-hidden">
                                        <div class="card-header rounded-0 bg-light py-2">
                                            <h6 class="mb-0 fw-bold">Émissions nettes</h6>
                                            <small class="text-muted">Total avec absorptions</small>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <thead>
                                                        <tr class="border-bottom">
                                                            <th class="small">Scénario</th>
                                                            <?php foreach ($annees_cibles as $annee): ?>
                                                                <th class="text-center small"><?= $annee ?></th>
                                                            <?php endforeach; ?>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach (listTypeScenario() as $scenario_key => $scenario_label): ?>
                                                            <tr>
                                                                <td class="small"><?= $scenario_label ?></td>
                                                                <?php foreach ($annees_cibles as $annee): ?>
                                                                    <?php
                                                                    $valeur = $totaux[$scenario_key][$annee] ?? 0;
                                                                    $classe = $valeur > 3000 ? 'text-danger' : ($valeur > 1000 ? 'text-warning' : ($valeur < 0 ? 'text-success' : 'text-info'));
                                                                    ?>
                                                                    <td class="text-center fw-bold small <?= $classe ?>">
                                                                        <?= number_format($valeur, 1, ',', ' ') ?>
                                                                    </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
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
</body>

<script>
    function filterProjections() {
        const secteurId = document.getElementById('secteurFilter').value;
        const scenario = document.getElementById('scenarioFilter').value;
        const rows = document.querySelectorAll('#id-datatableNONE tbody tr[data-secteur]');

        rows.forEach(row => {
            const rowSecteur = row.dataset.secteur;
            const rowScenario = row.dataset.scenario;

            let showRow = true;

            if (secteurId && rowSecteur !== secteurId) {
                showRow = false;
            }

            if (scenario && rowScenario !== scenario) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });

        const secteurRows = document.querySelectorAll('#id-datatableNONE tbody tr.table-secondary');
        secteurRows.forEach(row => {
            const secteurCells = row.querySelectorAll('td');
            if (secteurCells.length > 0) {
                const secteurText = secteurCells[0].textContent.toLowerCase();
                const secteurIdFromRow = Array.from(row.parentNode.children).indexOf(row);
                const nextRows = Array.from(row.parentNode.children).slice(secteurIdFromRow + 1);

                let hasVisibleChildren = false;
                for (let i = 0; i < nextRows.length; i++) {
                    if (nextRows[i].classList.contains('table-secondary')) break;
                    if (nextRows[i].style.display !== 'none') {
                        hasVisibleChildren = true;
                        break;
                    }
                }
                row.style.display = hasVisibleChildren ? '' : 'none';
            }
        });
    }

    function deleteProjectionScenario(secteurId, scenario) {
        if (!secteurId || !scenario) {
            errorAction('Erreur: Secteur ou scénario non spécifié');
            return;
        }

        confirmAction('Supprimer', `Êtes-vous sûr de vouloir supprimer toutes les projections pour le scénario ${scenario} ?`, 'Supprimer', 'danger')
            .then((confirm) => {
                if (confirm) {
                    fetch(`./apis/projections.routes.php?secteur=${secteurId}&scenario=${scenario}`, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': `Bearer ${token}`
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                successAction('Projections supprimées avec succès');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                errorAction(data.message || 'Erreur lors de la suppression');
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);
                            errorAction('Erreur lors de la suppression');
                        });;
                }
            });
    }
</script>

</html>