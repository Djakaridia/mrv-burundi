<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php
    $rapportId = isset($_GET['id']) ? $_GET['id'] : null;

    if (!$rapportId || !is_numeric($rapportId)) {
        header("Location: rapport_periodique.php");
        exit;
    }

    include './components/navbar & footer/head.php';

    $rapport = new RapportPeriode($db);
    $rapport->id = $rapportId;
    $rapport_curr = $rapport->readById();

    if (!$rapport_curr) {
        echo "<script>window.location.href = 'rapport_periodique.php';</script>";
        exit;
    }

    $project = new Projet($db);
    $project->id = $rapport_curr['projet_id'];
    $project_rapport = $project->readById();

    $indicateur = new Indicateur($db);
    $indicateur->projet_id = $rapport_curr['projet_id'];
    $indicateurs_project = $indicateur->readByProjet();
    $indicateurs_project = array_filter($indicateurs_project, function ($indicateur) {
        return $indicateur['state'] == 'actif';
    });

    $secteur = new Secteur($db);
    $secteurs = $secteur->read();
    $secteurs_project = array_filter($secteurs, function ($secteur) use ($project_rapport) {
        return $secteur['state'] == 'actif' && $secteur['parent'] == 0 && $project_rapport['secteur_id'] == $secteur['id'];
    });

    //##################################################
    $suivi = new Suivi($db);
    $suivi->projet_id = $rapport_curr['projet_id'];
    $suivis_project = $suivi->readByProjet();

    // Regrouper les données des suivis par secteur et annee
    $suivis_secteur_grouped = array();
    $suivis_annee_grouped = array();
    foreach ($suivis_project as $suivi) {
        $suivis_secteur_grouped[$suivi['secteur_id']][] = $suivi;
        $suivis_annee_grouped[$suivi['annee']][] = $suivi;
    }

    // Calcul des sommes par secteur
    $suivisDataSecteurSomme = array();
    if ($suivis_secteur_grouped) {
        foreach ($suivis_secteur_grouped as $secteur_id => $suivis) {
            $suivisDataSecteurSomme[$secteur_id] = array_sum(array_column($suivis, 'valeur'));
        }
    }

    // Calcul des sommes par année
    $suivisDataAnneeSomme = array();
    if ($suivis_annee_grouped) {
        foreach ($suivis_annee_grouped as $annee => $suivis) {
            $suivisDataAnneeSomme[$annee] = array_sum(array_column($suivis, 'valeur'));
        }
    }

    //##################################################
    $cible = new Cible($db);
    $cible->projet_id = $rapport_curr['projet_id'];
    $cibles_project = $cible->readByProjet();

    // Regrouper les données des cibles par secteur et annee
    $cibles_secteur_grouped = array();
    $cibles_annee_grouped = array();
    foreach ($cibles_project as $cible) {
        $cibles_secteur_grouped[$cible['secteur_id']][] = $cible;
        $cibles_annee_grouped[$cible['annee']][] = $cible;
    }

    // Calcul des sommes par secteur
    $ciblesDataSecteurSomme = array();
    if ($cibles_secteur_grouped) {
        foreach ($cibles_secteur_grouped as $secteur_id => $cibles) {
            $ciblesDataSecteurSomme[$secteur_id] = array_sum(array_column($cibles, 'valeur'));
        }
    }

    // Calcul des sommes par année
    $ciblesDataAnneeSomme = array();
    if ($cibles_annee_grouped) {
        foreach ($cibles_annee_grouped as $annee => $cibles) {
            $ciblesDataAnneeSomme[$annee] = array_sum(array_column($cibles, 'valeur'));
        }
    }

    //##################################################
    $projets = $project->read();
    $projets = array_filter($projets, function ($projet) {
        return $projet['state'] == 'actif';
    });

    $user = new User($db);
    $users = $user->read();
    $users = array_filter($users, function ($user) {
        return $user['state'] == 'actif';
    });

    $unite = new Unite($db);
    $unites = $unite->read();

    $indicateurs_cles = [
        'Émissions CO2' => ['value' => '1245 t', 'evolution' => '-5%'],
        'Consommation énergie' => ['value' => '5.2 GWh', 'evolution' => '+2%'],
        'Intensité carbone' => ['value' => '0.45 tCO2/MWh', 'evolution' => '-8%']
    ];

    $couleurs = [
        1 => '#E6F2FF',  // Très clair, presque blanc bleuté
        2 => '#F7DC6F',  // Jaune doré doux
        3 => '#ABEBC6',  // Vert pastel frais
        4 => '#BB8FCE',  // Violet pastel
        5 => '#F1948A',  // Orange-rose atténué
        6 => '#F5DEB3',  // Beige doux et chaleureux
        7 => '#5DADE2',  // Bleu ciel doux
        8 => '#FADBD8',  // Rose très pâle
        9 => '#3498DB',  // Bleu moyen profond
        10 => '#229954',  // Vert nature alleger
    ];

    ?>

    <title><?= $rapport_curr['intitule'] ?></title>
</head>

<body class="bg-light dark__bg-dark">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content px-2 mt-n4">
            <div class="card rounded-1 mb-9 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="mb-2"><?= $rapport_curr['intitule'] ?></h2>
                            <div class="text-muted">
                                <span class="me-3">Code: <?= $rapport_curr['code'] ?></span>
                                <span class="me-3">Créé le: <?= date('d/m/Y', strtotime($rapport_curr['created_at'])) ?></span>
                                <span class="me-3">Créé par: <?= $users[$rapport_curr['add_by']]['nom'] ?></span>
                            </div>
                        </div>
                        <div class="btn-reveal-trigger gap-1">
                            <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal" data-bs-target="#addRapportPeriodeModal" data-id="<?php echo $rapport_curr['id']; ?>">
                                    <span class="uil-pen fs-8"></span>
                                </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'update', 2)) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-<?= $rapport_curr['state'] == 'actif' ? 'success' : 'warning' ?> me-1 fs-10 px-2 py-1" onclick="updateState(<?php echo $rapport_curr['id']; ?>, '<?php echo $rapport_curr['state'] == 'actif' ? 'valided' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $rapport_curr['state'] == 'actif' ? 'valider' : 'activer'; ?> ce rapport ?', 'rapports_periode')">
                                    <span class="uil-<?php echo $rapport_curr['state'] == 'actif' ? 'check-square' : 'times-square'; ?> fs-8"></span>
                                </button>
                            <?php endif; ?>

                            <?php if (checkPermis($db, 'delete', 2)) : ?>
                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger me-1 fs-10 px-2 py-1" onclick="deleteData(<?php echo $rapport_curr['id']; ?>,'Voulez-vous vraiment supprimer ce rapport ?', 'rapports_periode', 'redirect', 'rapport_periodique.php')">
                                    <span class="uil-trash-alt fs-8"></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Métadonnées du rapport -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm h-100">
                                <div class="card-header bg-light dark__bg-dark rounded-0 py-2">
                                    <h5 class="mb-0">Informations de base</h5>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <dt class="col-sm-4">Projet</dt>
                                        <dd class="col-sm-8"><a href="./project_view.php?id=<?php echo $rapport_curr['projet_id']; ?>"><?= $project_rapport['name'] ?? 'Non spécifié' ?></a></dd>

                                        <dt class="col-sm-4">Périodicité</dt>
                                        <dd class="col-sm-8"><span class="badge bg-info"><?= listPeriodicite()[$rapport_curr['periode']] ?></span></dd>

                                        <dt class="col-sm-4">Référence</dt>
                                        <dd class="col-sm-8">
                                            <?= listMois()[$rapport_curr['mois_ref']] ?> <?= $rapport_curr['annee_ref'] ?> -
                                            <?= listMois()[$rapport_curr['mois_ref'] + $rapport_curr['periode'] > 12 ? $rapport_curr['mois_ref'] + $rapport_curr['periode'] - 12 : $rapport_curr['mois_ref'] + $rapport_curr['periode']] ?>
                                            <?= $rapport_curr['periode'] + $rapport_curr['mois_ref'] >= 12 ? $rapport_curr['annee_ref'] + 1 : $rapport_curr['annee_ref'] ?>
                                        </dd>

                                        <dt class="col-sm-4">Etat</dt>
                                        <dd class="col-sm-8"><span class="badge bg-<?= $rapport_curr['state'] == 'actif' ? 'warning' : 'success' ?>"><?= $rapport_curr['state'] == 'actif' ? 'Actif' : 'Validé' ?></span></dd>

                                        <dt class="col-sm-4">Dernière mise à jour</dt>
                                        <dd class="col-sm-8"><?= date('d/m/Y H:i', strtotime($rapport_curr['updated_at'])) ?></dd>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm h-100">
                                <div class="card-header bg-light dark__bg-dark rounded-0 py-2">
                                    <h5 class="mb-0">Description</h5>
                                </div>
                                <div class="card-body p-3">
                                    <?= !empty($rapport_curr['description']) ? nl2br($rapport_curr['description']) : '<p class="text-muted">Aucune description fournie</p>' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Indicateurs clés -->
                    <h4 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Indicateurs Clés</h4>
                    <!-- <div class="row mb-3">
                        <?php foreach ($indicateurs_cles as $name => $data): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card rounded-1 shadow-sm indicator-card h-100 border-start-<?= strpos($data['evolution'], '+') !== false ? 'danger' : 'success' ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= $name ?></h5>
                                        <div class="d-flex justify-content-between align-items-end">
                                            <h2 class="mb-0"><?= $data['value'] ?></h2>
                                            <span class="badge bg-<?= strpos($data['evolution'], '+') !== false ? 'danger' : 'success' ?>">
                                                <?= $data['evolution'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div> -->

                    <!-- Indicateurs -->
                    <div class="card rounded-1 shadow-sm border mb-4">
                        <div class="card-header bg-light dark__bg-dark rounded-0 py-2">
                            <h5 class="mb-0">Bilan de suivi des indicateurs</h5>
                        </div>
                        <div class="card-body p-2 scrollbar" style="max-height: 500px; overflow-y: auto;">
                            <table class="table fs-9 table-bordered mb-0 table-bordered table-striped">
                                <thead class="bg-primary-subtle text-nowrap">
                                    <tr>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col" rowspan="2">Intitulé de l'indicateur</th>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col" rowspan="2">Type</th>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col" rowspan="2">Unité</th>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col" rowspan="2">Référence</th>
                                        <?php $index_i = 1; ?>
                                        <?php for ($year = date('Y', strtotime($project_rapport['start_date'])); $year <= date('Y', strtotime($project_rapport['end_date'])); $year++) : ?>
                                            <th class="sort align-middle text-center px-2" style="background-color: <?= $couleurs[$index_i] ?>" scope="col" colspan="3"><?php echo $year; ?></th>
                                            <?php $index_i++; ?>
                                        <?php endfor; ?>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col" colspan="3">Total</th>
                                    </tr>
                                    <tr>
                                        <?php $index_j = 1; ?>
                                        <?php for ($year = date('Y', strtotime($project_rapport['start_date'])); $year <= date('Y', strtotime($project_rapport['end_date'])); $year++) : ?>
                                            <th class="sort align-middle text-center px-2" style="background-color: <?= $couleurs[$index_j] ?>" scope="col">Prévue</th>
                                            <th class="sort align-middle text-center px-2" style="background-color: <?= $couleurs[$index_j] ?>" scope="col">Réalisé</th>
                                            <th class="sort align-middle text-center px-2" style="background-color: <?= $couleurs[$index_j] ?>" scope="col">Taux</th>
                                            <?php $index_j++; ?>
                                        <?php endfor; ?>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col">Cible totale</th>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col">Totale réalisée</th>
                                        <th class="sort align-middle text-center bg-light dark__bg-dark px-2" scope="col">Taux Global</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($indicateurs_project as $indicateur):
                                        $referentiel = new Referentiel($db);
                                        $referentiel->id = $indicateur['referentiel_id'];
                                        $referentiel_curr = $referentiel->readById();

                                        // ############################################### Valeurs cibles de l'indicateur
                                        $cible = new Cible($db);
                                        $cible->indicateur_id = $indicateur['id'];
                                        $cibles_cmr = $cible->readByIndicateur();

                                        $cibles_grouped = [];
                                        foreach ($cibles_cmr as $cible) {
                                            $cibles_grouped[$cible['annee']][] = $cible;
                                        }

                                        $ciblesAnneeSomme = [];
                                        foreach ($cibles_grouped as $annee => $cibles) {
                                            $ciblesAnneeSomme[$annee] = array_sum(array_column($cibles, 'valeur'));
                                        }

                                        // ############################################### Valeurs réalisées de l'indicateur
                                        $suivi = new Suivi($db);
                                        $suivi->indicateur_id = $indicateur['id'];
                                        $suivis_cmr = $suivi->readByIndicateur();

                                        $suivis_grouped = array();
                                        foreach ($suivis_cmr as $suivi) {
                                            $suivis_grouped[$suivi['annee']][] = $suivi;
                                        }

                                        $suivisAnneeSomme = [];
                                        foreach ($suivis_grouped as $annee => $suivis) {
                                            $suivisAnneeSomme[$annee] = array_sum(array_column($suivis, 'valeur'));
                                        }
                                    ?>
                                        <tr>
                                            <td class="px-2" style="min-width: 200px"><?= $indicateur['intitule'] ?></td>
                                            <td class="text-center text-capitalize"><?= $referentiel_curr['categorie'] ?? '-' ?></td>
                                            <td class="text-center"><?= $indicateur['unite']; ?></td>
                                            <td class="text-center"><?= $indicateur['valeur_reference'] ?></td>

                                            <?php for ($year = date('Y', strtotime($project_rapport['start_date'])); $year <= date('Y', strtotime($project_rapport['end_date'])); $year++) : ?>
                                                <td class="text-center"><?= $ciblesAnneeSomme[$year] ?? '-' ?></td>
                                                <td class="text-center"><?= $suivisAnneeSomme[$year] ?? '-' ?></td>
                                                <td class="text-center bg-light dark__bg-dark">
                                                    <?php if (isset($ciblesAnneeSomme[$year]) && isset($suivisAnneeSomme[$year]) && $ciblesAnneeSomme[$year] > 0) : ?>
                                                        <?= round($suivisAnneeSomme[$year] / $ciblesAnneeSomme[$year] * 100, 0) ?>%
                                                    <?php else : ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>

                                            <td class="text-center"><?= array_sum($ciblesAnneeSomme) ?></td>
                                            <td class="text-center"><?= array_sum($suivisAnneeSomme) ?></td>
                                            <td class="text-center bg-light dark__bg-dark">
                                                <?php if (isset($ciblesAnneeSomme) && isset($suivisAnneeSomme) && array_sum($ciblesAnneeSomme) > 0) : ?>
                                                    <?= round(array_sum($suivisAnneeSomme) / array_sum($ciblesAnneeSomme) * 100, 2) ?> %
                                                <?php else : ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Graphiques -->
                    <h4 class="mb-3"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h4>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-body" id="trendChart"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card rounded-1 shadow-sm border h-100">
                                <div class="card-body" id="sectorChart"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton retour -->
                    <div class="text-center mt-4">
                        <a href="rapport_periodique.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste des rapports
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php include './components/navbar & footer/footer.php'; ?>
    </main>

    <?php include './components/navbar & footer/foot.php'; ?>

    <script>
        // Graphique par secteurs d'activités du projet
        const secteurs_graph = [
            <?php foreach ($secteurs_project as $secteur) { ?> {
                    name: "<?= $secteur['name'] ?>",
                    y: <?= $suivisDataSecteurSomme[$secteur['id']] ?? 0 ?>,
                    color: getSectorColor(<?= $secteur['id'] ?>)
                },
            <?php } ?>
        ];

        mrvPieChart({
            id: 'sectorChart',
            title: 'Répartition par secteur',
            unite: 'TVA',
            data: secteurs_graph,
        });

        // Préparation des données PHP pour Highcharts
        const annees_graph = [
            <?php for (
                $year = date('Y', strtotime($project_rapport['start_date']));
                $year <= date('Y', strtotime($project_rapport['end_date']));
                $year++
            ) {
                echo "'" . $year . "',";
            } ?>
        ];

        const realisations_graph = [
            <?php for (
                $year = date('Y', strtotime($project_rapport['start_date']));
                $year <= date('Y', strtotime($project_rapport['end_date']));
                $year++
            ) {
                echo ($suivisDataAnneeSomme[$year] ?? 0) . ",";
            } ?>
        ];

        const cibles_graph = [
            <?php for (
                $year = date('Y', strtotime($project_rapport['start_date']));
                $year <= date('Y', strtotime($project_rapport['end_date']));
                $year++
            ) {
                echo ($ciblesDataAnneeSomme[$year] ?? 0) . ",";
            } ?>
        ];

        mrvColumnChart({
            id: 'trendChart',
            title: 'Suivi des indicateurs par année',
            unite: 'TVA',
            categories: annees_graph,
            cibles: cibles_graph,
            suivis: realisations_graph,
        });

        // Fonction pour attribuer des couleurs en fonction de l'ID du secteur (modifiable)
        function getSectorColor(sectorId) {
            const colors = [
                '#FF6384', // Rose
                '#36A2EB', // Bleu
                '#FFCE56', // Jaune
                '#4BC0C0', // Turquoise
                '#9966FF', // Violet
                '#FF9F40', // Orange
                '#8AC24A', // Vert
                '#FF5A5F', // Rouge corail
                '#47B8E0', // Bleu clair
                '#7FDBFF' // Bleu très clair
            ];
            return colors[sectorId % colors.length];
        }
    </script>
</body>

</html>