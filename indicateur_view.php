<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Détails de l'indicateur | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $refId = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$refId || !is_numeric($refId)) {
        echo "<script>window.location.href = 'suivi_indicateurs.php';</script>";
        exit;
    }

    $referentiel = new Referentiel($db);
    $referentiel->id = $refId;
    $ref_curr = $referentiel->readById();

    if (!$ref_curr) {
        echo "<script>window.location.href = 'suivi_indicateurs.php';</script>";
        exit;
    }

    $unite = new Unite($db);
    $unites = $unite->read();
    $unite->id = $ref_curr['unite'] ?? null;
    $unite_ref = $unite->readById();

    $indicateur = new Indicateur($db);
    $indicateur->referentiel_id = $ref_curr['id'] ?? null;
    $indicateur_cmr = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

    // ==========================================================>
    $user = new User($db);
    $users = $user->read();

    $programme = new Programme($db);
    $programmes = $programme->read();

    $sens_evolutions = array('asc' => 'Ascendant', 'desc' => 'Descendant');
    $echelles = array('nationale' => 'Nationale', 'provincial' => 'Provincial');

    $province = new Province($db);
    $provinces = $province->read();

    $zone_type = new ZoneType($db);
    $zone_types = $zone_type->read();

    $zone = new Zone($db);
    $zones = $zone->read();

    $typologie = new Typologie($db);
    $typologies = $typologie->read();

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent_id'] == 0 && $secteur['state'] == 'actif';
    });

    // ==========================================================>
    if (!empty($indicateur_cmr)) {
        $first_cmr = reset($indicateur_cmr);

        $project = new Projet($db);
        $project->id = $first_cmr['projet_id'];
        $project_cmr = $project->readById();

        // #######################################################
        // Construction des données pour le graphique par annee
        $annees = [];
        for ($year = date('Y', strtotime($project_cmr['start_date'])); $year <= date('Y', strtotime($project_cmr['end_date'])); $year++) {
            $annees[] = $year;
        }

        $cible = new Cible($db);
        $cible->cmr_id = $first_cmr['id'];
        $cibles_raw = $cible->readByCMR();
        usort($cibles_raw, fn($a, $b) => $a['annee'] - $b['annee']);
        $cibles_map = [];
        foreach ($cibles_raw as $item) {
            $year = $item['annee'];
            $value = (float)$item['valeur'];
            if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
            $cibles_map[$year] += $value;
        }
        ksort($cibles_map, SORT_NUMERIC);
        $cibles = array_map(fn($y) => (float)($cibles_map[$y] ?? 0), $annees);
        $cibles_sum = array_sum($cibles_map);

        $suivi = new Suivi($db);
        $suivi->cmr_id = $first_cmr['id'];
        $suivis_raw = $suivi->readByCMR();
        usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);
        $suivis_map = [];
        foreach ($suivis_raw as $item) {
            $year = $item['annee'];
            $value = (float)$item['valeur'];
            if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
            $suivis_map[$year] += $value;
        }
        ksort($suivis_map, SORT_NUMERIC);
        $suivis = array_map(fn($y) => (float)($suivis_map[$y] ?? 0), $annees);
        $suivis_sum = array_sum($suivis_map);

        // #######################################################
        // Construction des données pour le graphique par secteur
        $secteurs_project = array_filter($secteurs, function ($s) use ($project_cmr) {
            return in_array($s['id'], explode(',', str_replace('"', '', $project_cmr['secteurs'])));
        });

        $suivis_par_secteur = [];
        foreach ($suivis_raw as $suivi) {
            if (!isset($suivis_par_secteur[$suivi['secteur_id']])) {
                $suivis_par_secteur[$suivi['secteur_id']] = 0;
            }
            $suivis_par_secteur[$suivi['secteur_id']] += (float)$suivi['valeur'];
        }

        $chart_data = [];
        foreach ($secteurs_project as $secteur) {
            $secteur_id = $secteur['id'];
            $valeur = $suivis_par_secteur[$secteur_id] ?? 0;
            $chart_data[] = [
                'name' => $secteur['name'],
                'y' => $valeur,
            ];
        }
    }
    ?>
</head>

<body class="bg-light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content px-2 mt-n4">
            <div class="card rounded-1 mb-9 overflow-hidden">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 class="mb-1"><?= $ref_curr['intitule'] ?></h3>
                            <div class="text-muted">
                                <span class="me-3">Créé le: <?= date('d/m/Y', strtotime($ref_curr['created_at'])) ?></span>
                                <span class="me-3">Créé par: <?= $users[$ref_curr['add_by']]['nom'] ?></span>
                            </div>
                        </div>
                        <div class="btn-reveal-trigger d-flex gap-2">
                            <button title="Voir les résultats" type="button" class="btn btn-sm btn-phoenix-primary fs-10 px-2 py-1" onclick="window.location.href = 'suivi_indicateurs.php?id=<?php echo $ref_curr['id']; ?>';">
                                <span class="uil-arrow-left"></span> Voir les résultats
                            </button>

                            <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Activer/Désactiver" onclick="updateState(<?php echo $ref_curr['id']; ?>, '<?php echo $ref_curr['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $ref_curr['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce referentiel ?', 'referentiels')"
                                    type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                    <span class="uil-<?php echo $ref_curr['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'delete')) : ?>
                                <button title="Supprimer" onclick="deleteData(<?php echo $ref_curr['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce referentiel ?', 'referentiels')"
                                    type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                    <span class="uil-trash-alt fs-8"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Métadonnées du rapport -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Informations de base</h5>

                                    <?php if (checkPermis($db, 'update')) : ?>
                                        <button title="Modifier" class="btn btn-sm btn-phoenix-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                            data-bs-target="#addReferentielModal" data-id="<?php echo $ref_curr['id']; ?>">
                                            <span class="uil-edit"></span> Modifier
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <dt class="col-sm-4 text-muted fs-9">Code :</dt>
                                        <dd class="col-sm-8 fs-9"><?= $ref_curr['code'] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Indicateur :</dt>
                                        <dd class="col-sm-8 fs-9"><?= $ref_curr['intitule'] ?> (<?= "<strong>" . $unite_ref['name'] . "</strong>" ?>)</dd>

                                        <dt class="col-sm-4 text-muted fs-9">Echelle :</dt>
                                        <dd class="col-sm-8 fs-9 text-capitalize">
                                            <?php if (in_array($ref_curr['echelle'], array('nationale', 'provincial'))) : ?>
                                                <?= $echelles[$ref_curr['echelle']] ?>
                                            <?php else : ?>
                                                <?php foreach ($zone_types as $zone_type): ?>
                                                    <?php if ($zone_type['id'] == $ref_curr['echelle']): ?>
                                                        <?= $zone_type['name'] ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </dd>

                                        <dt class="col-sm-4 text-muted fs-9">Modele :</dt>
                                        <dd class="col-sm-8 fs-9 text-capitalize"><?= listModeleTypologie()[$ref_curr['modele']] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Responsables :</dt>
                                        <dd class="col-sm-8 fs-9">
                                            <?php foreach ($structures as $structure): ?>
                                                <?php if ($structure['id'] == $ref_curr['responsable']): ?>
                                                    <?= "<strong>" . $structure['sigle'] . "</strong>"; ?> (Principal)
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php foreach ($structures as $structure): ?>
                                                <?php if (in_array($structure['id'], explode(',', str_replace('"', '', $ref_curr['autre_responsable'] ?? ""))) && $structure['id'] != $ref_curr['responsable']): ?>
                                                    <?= "/ " . $structure['sigle']; ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </dd>

                                        <dt class="col-sm-4 text-muted fs-9">Fonction d'agrégration :</dt>
                                        <dd class="col-sm-8 fs-9"><?= listModeAggregation()[$ref_curr['fonction_agregation']] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Seuil minimum :</dt>
                                        <dd class="col-sm-8 fs-9"><?= $ref_curr['seuil_min'] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Seuil maximum :</dt>
                                        <dd class="col-sm-8 fs-9"><?= $ref_curr['seuil_max'] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Sens d'évolution :</dt>
                                        <dd class="col-sm-8 fs-9"><?= $sens_evolutions[$ref_curr['sens_evolution']] ?></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Etat :</dt>
                                        <dd class="col-sm-8 fs-9"><span class="badge bg-<?= $ref_curr['state'] == 'actif' ? 'success' : 'warning' ?>"><?= $ref_curr['state'] == 'actif' ? 'Actif' : 'Inactif' ?></span></dd>

                                        <dt class="col-sm-4 text-muted fs-9">Dernière mise à jour :</dt>
                                        <dd class="col-sm-8 fs-9"><?= date('d/m/Y H:i', strtotime($ref_curr['updated_at'])) ?></dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-header d-flex justify-content-between align-items-center bg-light dark__bg-dark rounded-0 py-1 px-2">
                                    <h5 class="mb-0">Suivis de l'indicateur</h5>

                                    <button title="Nouvelle valeur" type="button" class="btn btn-sm btn-phoenix-primary fs-9 p-2 rounded-1" data-bs-toggle="modal"
                                        data-bs-target="#newIndicateurSuiviModal" aria-haspopup="true" aria-expanded="false"
                                        data-cmr_id="<?php echo $first_cmr['id']; ?>" data-projet_id="<?php echo $project_cmr['id']; ?>" data-referentiel_id="<?php echo $ref_curr['id']; ?>">
                                        <span class="uil-plus"></span> Nouvelle valeur
                                    </button>
                                </div>
                                <div class="card-body p-2">
                                    <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" id="id-datatable">
                                        <thead class="bg-light dark__bg-dark">
                                            <tr>
                                                <th scope="col" class="px-2">Secteur</th>
                                                <th scope="col" class="px-2">Année</th>
                                                <th scope="col" class="px-2">Valeur</th>
                                                <th scope="col" class="px-2">Date d'ajout</th>
                                                <th scope="col" class="px-2">Observation</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($suivis_raw as $suivi): ?>
                                                <tr class="align-middle">
                                                    <td class="px-2">
                                                        <?php foreach ($secteurs_project as $secteur) : ?>
                                                            <?php if ($secteur['id'] == $suivi['secteur_id']) : ?>
                                                                <?= $secteur['name'] ?>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <td class="px-2"><?= $suivi['annee'] ?></td>
                                                    <td class="px-2"><?= $suivi['valeur'] ?></td>
                                                    <td class="px-2"><?= $suivi['date_suivie'] ?></td>
                                                    <td class="px-2"><?= $suivi['observation'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Synthese des données de suivis -->
                    <h4 class="mb-3"><i class="fas fa-database me-2"></i>Synthèse des données de suivis</h4>
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-body p-0" id="chartSynthese<?= $ref_curr['id'] ?>">
                                    <?php if (!empty($suivis_raw)) : ?>
                                        <?php $annees_suivis = array_unique(array_column($suivis_raw, 'annee')); ?>
                                        <ul class="nav nav-underline fs-9 bg-light dark__bg-dark border-bottom px-3" id="myTab<?= $ref_curr['id'] ?>" role="tablist">
                                            <?php foreach ($annees_suivis as $index => $annee) : ?>
                                                <li class="nav-item" role="presentation"><a class="nav-link <?= $index == 0 ? 'active' : '' ?>" id="tab-<?= $annee ?>"
                                                        data-bs-toggle="tab" href="#tab-content-<?= $annee ?>" role="tab" aria-controls="tab-content-<?= $annee ?>" aria-selected="true"><?= $annee ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="tab-content p-3" id="myTabContent<?= $ref_curr['id'] ?>">
                                            <?php foreach ($annees_suivis as $index => $annee) :
                                                $suivis_par_annee = array_filter($suivis_raw, function ($suivi) use ($annee) {
                                                    return $suivi['annee'] == $annee;
                                                }); ?>
                                                <div class="tab-pane fade <?= $index == 0 ? 'active show' : '' ?>" id="tab-content-<?= $annee ?>" role="tabpanel" aria-labelledby="tab-<?= $annee ?>">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card rounded-1 h-100">
                                                                <div class="card-header d-flex justify-content-between align-items-center rounded-0 py-2 px-2">
                                                                    <h5 class="mb-0">Données désagrégées</h5>
                                                                </div>
                                                                <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis">
                                                                    <thead class="bg-light dark__bg-dark">
                                                                        <tr>
                                                                            <?php if (!empty($ref_curr['echelle']) && $ref_curr['echelle'] !== 'nationale') : ?>
                                                                                <th scope="col" class="px-2 text-capitalize">
                                                                                    <?php if ($ref_curr['echelle'] == 'provincial') : ?>
                                                                                        Province
                                                                                    <?php elseif (is_numeric($ref_curr['echelle'])) : ?>
                                                                                        <?php foreach ($zone_types as $type) : ?>
                                                                                            <?php if ($type['id'] == $ref_curr['echelle']) : ?>
                                                                                                <?= $type['name'] ?>
                                                                                            <?php endif; ?>
                                                                                        <?php endforeach; ?>
                                                                                    <?php endif; ?>
                                                                                </th>
                                                                            <?php endif; ?>
                                                                            <?php if (!empty($ref_curr['modele']) && $ref_curr['modele'] !== "valeur_absolue") : ?>
                                                                                <th scope="col" class="px-2">Classe</th>
                                                                            <?php endif; ?>
                                                                            <th scope="col" class="px-2">Valeur</th>
                                                                            <th scope="col" class="px-2">Date suivie</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($suivis_par_annee as $suivi): ?>
                                                                            <tr class="align-middle">
                                                                                <?php if (!empty($ref_curr['echelle']) && $ref_curr['echelle'] !== 'nationale') : ?>
                                                                                    <td class="px-2 text-capitalize">
                                                                                        <?php if ($ref_curr['echelle'] == 'provincial') : ?>
                                                                                            <?php foreach ($provinces as $province) : ?>
                                                                                                <?php if ($province['code'] == $suivi['echelle']) : ?>
                                                                                                    <?= $province['name'] ?>
                                                                                                <?php endif; ?>
                                                                                            <?php endforeach; ?>
                                                                                        <?php elseif (is_numeric($ref_curr['echelle'])) : ?>
                                                                                            <?php foreach ($zones as $zone) : ?>
                                                                                                <?php if ($zone['name'] == $suivi['echelle']) : ?>
                                                                                                    <?= $zone['name'] ?>
                                                                                                <?php endif; ?>
                                                                                            <?php endforeach; ?>
                                                                                        <?php endif; ?>
                                                                                    </td>
                                                                                <?php endif; ?>
                                                                                <?php if (!empty($ref_curr['modele']) && $ref_curr['modele'] !== "valeur_absolue") : ?>
                                                                                    <td class="px-2"><?= $suivi['classe'] ?></td>
                                                                                <?php endif; ?>
                                                                                <td class="px-2"><?= $suivi['valeur'] ?></td>
                                                                                <td class="px-2"><?= $suivi['date_suivie'] ?></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="card rounded-1 shadow-sm border h-100">
                                                                <div class="card-header bg-light dark__bg-dark rounded-0 p-2">
                                                                    <h5 class="mb-0">Évolution désagrégée par classe</h5>
                                                                </div>
                                                                <div class="card-body p-2" id="chartClasse<?= $index ?>" style="height: 400px;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <div class="text-center py-5">
                                            <h4 class="mt-3 fw-bold text-primary" id="suiviCMRLoadingText">Aucun suivi trouvé</h4>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <h4 class="mb-3"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h4>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-body p-2" id="chartAnnee<?= $ref_curr['id'] ?>"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-body p-2" id="chartSecteur<?= $ref_curr['id'] ?>"></div>
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
    <?php foreach ($annees_suivis as $index => $annee) :
        $suivis_classe = array_filter($suivis_raw, fn($suivi) => $suivi['annee'] == $annee); ?>
        mrvStackGroupChart({
            id: 'chartClasse<?= $index ?>',
            title: '<?= htmlspecialchars($ref_curr['intitule']) . ' (' . $unite_ref['name'] . ')' ?>',
            unite: '<?= $unite_ref['name'] ?>',
            categories: [<?= $annee ?>],
            series: [
                ...[{
                    name: 'Cibles',
                    data: <?= json_encode(array_map(fn($y) => (float)($cibles_map[$y] ?? 0), [$annee])) ?>,
                    stack: 'cible'
                }],
                ...[<?php if ($ref_curr['modele'] !== "valeur_absolue") : ?>
                        <?php foreach ($suivis_classe as $suivi) : ?> {
                                name: '<?= $suivi['classe'] ?>',
                                data: [<?= (float)($suivi['valeur'] ?? 0) ?>],
                                stack: 'suivi'
                            },
                        <?php endforeach; ?>
                    <?php else : ?> {
                            name: 'Réalisations',
                            data: <?= json_encode(array_map(fn($y) => (float)($suivis_map[$y] ?? 0), [$annee])) ?>,
                            stack: 'suivi'
                        }
                    <?php endif; ?>
                ]
            ]
        });
    <?php endforeach; ?>

    mrvColumnChart({
        id: 'chartAnnee<?= $ref_curr['id'] ?>',
        title: '<?= htmlspecialchars($ref_curr['intitule']) . ' (' . $unite_ref['name'] . ')' ?>',
        unite: '<?= $unite_ref['name'] ?>',
        categories: <?= json_encode($annees) ?>,
        cibles: <?= json_encode($cibles) ?>,
        suivis: <?= json_encode($suivis) ?>
    });

    mrvPieChart({
        id: 'chartSecteur<?= $ref_curr['id'] ?>',
        title: '<?= htmlspecialchars($ref_curr['intitule']) . ' (' . $unite_ref['name'] . ')' ?>',
        unite: '<?= $unite_ref['name'] ?>',
        data: <?= json_encode($chart_data) ?>,
    });
</script>

</html>