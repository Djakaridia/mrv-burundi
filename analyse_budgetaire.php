<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Analyse budgétaire | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $sel_id = isset($_GET['proj']) ? $_GET['proj'] : '';

    $projet = new Projet($db);
    $projets = $projet->read();
    $projets = array_filter($projets, function ($projet) {
        return $projet['state'] == 'actif';
    });

    if (!empty($projets) && $sel_id == '') {
        $sel_id = $projets[0]['id'];
    }

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'decaisse';

    if (!in_array($tab, ['decaisse', 'actor', 'import', 'activity'])) {
        $tab = 'decaisse';
    }

    $convention = new Convention($db);
    $convention->projet_id = $sel_id;
    $conventions = $convention->readByProjet();

    $structure = new Structure($db);
    $structures = $structure->read();
    $grouped_structure = [];
    foreach ($structures as $structure) {
        $grouped_structure[$structure['id']] = $structure;
    }

    $data_bailleur = [];
    $data_montant = [];
    foreach ($conventions as $convention) {
        $data_bailleur[] = $grouped_structure[$convention['structure_id']]['sigle'];
        $data_montant[] = floatval($convention['montant']);
    }
    $column_categories = array_values($data_bailleur);
    $column_data = array_values($data_montant);

    $tache = new Tache($db);
    $tache->projet_id = $sel_id;
    $taches = $tache->readByProjet();

    $tache_cout = new TacheCout($db);
    $tache_couts = $tache_cout->read();
    $grouped_tache_couts = [];
    foreach ($tache_couts as $tache_cout) {
        $grouped_tache_couts[$tache_cout['tache_id']][] = $tache_cout;
    }

    $tache_imports = []

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
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
                    <div class="col-lg-4 mb-2 mb-lg-0">
                        <h4 class="my-1 fw-black">Analyse budgétaire</h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="result" id="resultID" onchange="window.location.href = 'analyse_budgetaire.php?proj=' + this.value">
                                <option class="text-center" value="" selected disabled>---Sélectionner un projet---</option>
                                <?php foreach ($projets as $projet) { ?>
                                    <option value="<?php echo $projet['id']; ?>" <?php if ($sel_id == $projet['id']) echo 'selected'; ?>><?php echo $projet['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                        <?php if ($tab == 'import') { ?>
                            <button type="button" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <i class="fas fa-upload"></i> Importer décaissement
                            </button>
                        <?php } ?>

                        <?php if (in_array($tab, ['decaisse', 'actor'])) { ?>
                            <a title="Ajouter" class="btn btn-subtle-primary btn-sm" href="project_view.php?id=<?php echo $sel_id; ?>&tab=finance">
                                <i class="fas fa-eye"></i> Voir convention</a>
                        <?php } ?>

                        <?php if (in_array($tab, ['activity'])) { ?>
                            <a title="Ajouter" class="btn btn-subtle-primary btn-sm" href="project_view.php?id=<?php echo $sel_id; ?>&tab=task">
                                <i class="fas fa-eye"></i> Voir activités</a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="mx-n4 px-3 mx-lg-n6 bg-body-emphasis border border-top-0">
                <div class="card-body d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <ul class="nav nav-underline fs-9" id="myTab" role="tablist">
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'decaisse' ? 'active' : ''; ?>"
                                id="decaisse-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?proj=' . $sel_id . '&tab=decaisse'; ?>">Taux de décaissement</a></li>
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'actor' ? 'active' : ''; ?>"
                                id="actor-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?proj=' . $sel_id . '&tab=actor'; ?>">Situation par acteur</a></li>
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'activity' ? 'active' : ''; ?>"
                                id="activity-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?proj=' . $sel_id . '&tab=activity'; ?>">Situation par activités</a></li>
                        <!-- <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'import' ? 'active' : ''; ?>"
                                id="import-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?proj=' . $sel_id . '&tab=import'; ?>">Situation par activités importées</a></li> -->
                    </ul>
                </div>
            </div>

            <div class="tab-content mt-3" id="myTabContent">
                <div class="tab-pane fade <?php echo $tab == 'decaisse' ? 'active show' : ''; ?>" id="tab-decaisse" role="tabpanel" aria-labelledby="decaisse-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-3 mx-lg-n6 bg-body-emphasis border-y">
                                <?php if (!empty($conventions)) { ?>
                                    <div id="tauxDecaissementChart" style="height: 400px;"></div>
                                <?php } else { ?>
                                    <div class="text-center py-5 my-3" style="min-height: 400px;">
                                        <div class="d-flex justify-content-center mb-3">
                                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                                <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <h4 class="text-800 mb-3">Aucune données trouvée</h4>
                                        <p class="text-600 mb-5">Veuillez ajouter des conventions pour afficher ses graphiques</p>
                                        <a href="project_view.php?id=<?php echo $sel_id; ?>&tab=finance" class="btn btn-subtle-primary rounded-1 btn-sm px-5">
                                            <span class="fa fa-plus fs-9 me-2"></span>Ajouter une convention
                                        </a>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'actor' ? 'active show' : ''; ?>" id="tab-actor" role="tabpanel" aria-labelledby="actor-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="sort align-middle" scope="col"> Code</th>
                                                <th class="sort align-middle" scope="col" width="30%"> Convention</th>
                                                <th class="sort align-middle" scope="col" width="30%"> Bailleur</th>
                                                <th class="sort align-middle" scope="col"> Prévu</th>
                                                <th class="sort align-middle" scope="col"> Décaissé</th>
                                                <th class="sort align-middle" scope="col"> % Exécuté</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($conventions as $convention): ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $convention['code'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $convention['name'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $grouped_structure[$convention['structure_id']]['sigle'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php if (isset($convention['montant'])) { ?>
                                                            <?php echo number_format($convention['montant'], 0, ',', ' ') ?>
                                                        <?php } else { ?>
                                                            -
                                                        <?php } ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'activity' ? 'active show' : ''; ?>" id="tab-activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="sort align-middle" scope="col"> Code</th>
                                                <th class="sort align-middle" scope="col" width="50%"> Activités</th>
                                                <th class="sort align-middle" scope="col"> Prévu</th>
                                                <th class="sort align-middle" scope="col"> Décaissé</th>
                                                <th class="sort align-middle" scope="col"> % Exécuté</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($taches as $tache): ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $tache['code'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $tache['name'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php if (isset($grouped_tache_couts[$tache['id']])) {
                                                            $montant = array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant'));
                                                            echo number_format($montant, 0, ',', ' ');
                                                        }else{
                                                            echo '-';
                                                        } ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- <div class="tab-pane fade <?php echo $tab == 'import' ? 'active show' : ''; ?>" id="tab-import" role="tabpanel" aria-labelledby="import-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="sort align-middle" scope="col"> Code</th>
                                                <th class="sort align-middle" scope="col" width="50%"> Activités</th>
                                                <th class="sort align-middle" scope="col"> Prévu</th>
                                                <th class="sort align-middle" scope="col"> Décaissé</th>
                                                <th class="sort align-middle" scope="col"> % Exécuté</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($tache_imports as $tache_import): ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $tache_import['code'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php echo $tache_import['name'] ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        <?php if (isset($grouped_tache_couts[$tache_import['id']])) {
                                                            $montant = array_sum(array_column($grouped_tache_couts[$tache_import['id']], 'montant'));
                                                            echo number_format($montant, 0, ',', ' ');
                                                        }else{
                                                            echo '-';
                                                        } ?>
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>

                                                    <td class="align-middle customer white-space-nowrap">
                                                        -
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
    mrvBarChart({
        id: 'tauxDecaissementChart',
        title: 'Taux de décaissement',
        unite: 'FCFA',
        categories: <?= json_encode($column_categories ?? []) ?>,
        data: <?= json_encode($column_data ?? []) ?>
    });
</script>

</html>