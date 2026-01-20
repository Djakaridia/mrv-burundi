<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Registre des émissions | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $register = new Register($db);
    $registers = $register->read();
    $grouped_registers = [];
    foreach ($registers as $register) {
        $grouped_registers[$register['secteur']][] = $register;
    }

    $unite = new Unite($db);
    $unites = $unite->read();

    $sel_secteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
        return $secteur['parent'] == 0;
    });
    $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] > 0;
    });

    if (!empty($registers) && !$sel_secteur) {
        $sel_secteur = array_pop($secteurs)['id'];
    }

    $column_categories = [];
    $column_data = [];
    $ges_data = [];
    $js_detail_data = [];

    if ($sel_secteur && !empty($grouped_registers[$sel_secteur])) {
        $category_totals = [];
        
        foreach ($grouped_registers[$sel_secteur] as $row) {
            $category = $row['categorie'];
            
            if (!isset($category_totals[$category])) {
                $category_totals[$category] = 0;
                $column_categories[] = $category;
            }
            
            // Utiliser soit emission_absolue soit emission_annee selon votre besoin
            $category_totals[$category] += $row['emission_absolue'];
        }
        
        $column_data = array_values($category_totals);
        
        // CORRECTION ICI : Meilleur regroupement par type de gaz
        $gaz_totals = [];
        $detail_data = [];
        
        foreach ($grouped_registers[$sel_secteur] as $row) {
            $category = $row['categorie'];
            $gaz = trim($row['gaz']);
            // Utiliser emission_absolue ou emission_annee selon votre choix
            $emission = $row['emission_absolue'];
            
            // Normaliser les noms de gaz (plus simple et plus efficace)
            $gaz_upper = strtoupper($gaz);
            
            // Déterminer la clé de gaz normalisée
            if (preg_match('/CO2|CO₂/', $gaz_upper)) {
                $gaz_key = 'CO₂';
            } elseif (preg_match('/CH4|CH₄/', $gaz_upper)) {
                $gaz_key = 'CH₄';
            } elseif (preg_match('/N2O|N₂O/', $gaz_upper)) {
                $gaz_key = 'N₂O';
            } elseif (preg_match('/SF6|SF₆/', $gaz_upper)) {
                $gaz_key = 'SF₆';
            } elseif (strpos($gaz_upper, 'HFC') !== false) {
                $gaz_key = 'HFCs';
            } elseif (strpos($gaz_upper, 'PFC') !== false) {
                $gaz_key = 'PFCs';
            } elseif (strpos($gaz_upper, 'NF3') !== false || strpos($gaz_upper, 'NF₃') !== false) {
                $gaz_key = 'NF₃';
            } else {
                $gaz_key = $gaz; // Garder le nom original si non reconnu
            }
            
            // Ajouter aux totaux par gaz
            if (!isset($gaz_totals[$gaz_key])) {
                $gaz_totals[$gaz_key] = 0;
            }
            $gaz_totals[$gaz_key] += $emission;
            
            // Préparer les données détaillées par catégorie
            if (!isset($detail_data[$category])) {
                $detail_data[$category] = [];
            }
            
            if (!isset($detail_data[$category][$gaz_key])) {
                $detail_data[$category][$gaz_key] = 0;
            }
            $detail_data[$category][$gaz_key] += $emission;
        }
        
        // DEBUG: Afficher les totaux par gaz pour vérification
        // echo "<!-- DEBUG - Totaux par gaz: " . print_r($gaz_totals, true) . " -->";
        
        // Filtrer les gaz avec des émissions > 0
        $ges_data = [];
        foreach ($gaz_totals as $gaz => $total) {
            if ($total > 0) {
                $ges_data[] = ['name' => $gaz, 'y' => $total];
            }
        }
        
        // Préparer les données détaillées pour JS
        foreach ($detail_data as $category => $gaz_data) {
            $category_data = [];
            foreach ($gaz_data as $gaz => $value) {
                if ($value > 0) {
                    $category_data[] = ['name' => $gaz, 'y' => $value];
                }
            }
            if (!empty($category_data)) {
                $js_detail_data[$category] = $category_data;
            }
        }
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
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <div class="col-lg-4 mb-2 mb-lg-0">
                        <h4 class="my-1 fw-black">
                            Registre des émissions (<a href="./documents/Registre_Carbone.xlsx" download class="fs-8 text-decoration-none"> <span class="fa fa-file-excel"></span> Canevas </a>)
                        </h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1" name="result" id="resultID" onchange="window.location.href = 'register_carbone.php?secteur=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner un secteur---</option>
                                <?php foreach ($secteurs as $secteur) { ?>
                                    <option value="<?php echo $secteur['id']; ?>" <?php if ($sel_secteur == $secteur['id']) echo 'selected'; ?>><?php echo $secteur['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 d-flex gap-2 justify-content-lg-end">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#importRegisterModal" data-secteur="<?php echo $sel_secteur; ?>" class="btn btn-subtle-primary btn-sm">
                            <span class="fa fa-database fs-9 me-2"></span>Importer Données
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 px-1 pb-3 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if (!empty($grouped_registers[$sel_secteur])) { ?>
                            <!-- Tableau -->
                            <h5 class="m-2 text-semibold"><i class="fas fa-table me-2"></i>Liste des Inventaires</h5>
                            <div class="mx-n1 mb-3 px-1 scrollbar">
                                <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                    <thead class="bg-secondary-subtle">
                                        <tr>
                                            <th class="sort align-middle text-uppercase" style="width: 5%">Année</th>
                                            <th class="sort align-middle text-uppercase" style="width: 40%">Catégorie</th>
                                            <th class="sort align-middle text-uppercase" style="width: 5%">Gaz</th>
                                            <th class="sort align-middle text-uppercase" style="width: 10%">Emission Année</th>
                                            <th class="sort align-middle text-uppercase" style="width: 10%">Emission Absolue</th>
                                            <th class="sort align-middle text-uppercase" style="width: 10%">Niveau Emission</th>
                                            <th class="sort align-middle text-uppercase" style="width: 10%">Emission Cumulée</th>
                                            <th class="sort align-middle text-uppercase" style="width: 10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list" id="table-latest-review-body">
                                        <?php 
                                        // Afficher un résumé des gaz disponibles pour debug
                                        $gaz_summary = [];
                                        foreach ($grouped_registers[$sel_secteur] as $row) {
                                            $gaz_summary[$row['gaz']] = ($gaz_summary[$row['gaz']] ?? 0) + 1;
                                        }
                                        ?>
                                        <!-- DEBUG: Résumé des gaz -->
                                        <!-- <div style="display:none;">
                                            <pre><?php echo print_r($gaz_summary, true); ?></pre>
                                            <pre>Ges Data: <?php echo print_r($ges_data, true); ?></pre>
                                        </div> -->
                                        
                                        <?php foreach ($grouped_registers[$sel_secteur] as $row) { ?>
                                            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                <td class="align-middle px-2"> <?php echo $row['annee']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $row['categorie']; ?> </td>
                                                <td class="align-middle px-2"> 
                                                    <?php echo $row['gaz']; ?>
                                                    <!-- <small class="text-muted">(abs: <?php echo $row['emission_absolue']; ?>)</small> -->
                                                </td>
                                                <td class="align-middle px-2"> <?php echo $row['emission_annee']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $row['emission_absolue']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $row['emission_niveau']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $row['emission_cumulee']; ?> </td>
                                                <td class="align-middle review px-2">
                                                    <div class="position-relative">
                                                        <div class="d-flex gap-1">
                                                            <?php if (checkPermis($db, 'update')) : ?>
                                                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                                                    data-bs-target="#addRegisterModal" data-id="<?php echo $row['id']; ?>">
                                                                    <span class="uil-pen fs-8"></span>
                                                                </button>
                                                            <?php endif; ?>

                                                            <?php if (checkPermis($db, 'update', 2)) : ?>
                                                                <button title="Approuver/Désapprouver" onclick="updateState(<?php echo $row['id']; ?>, '<?php echo $row['status'] == 'approuve' ? 'non_approuve' : 'approuve'; ?>', 'Êtes-vous sûr de vouloir <?php echo $row['status'] == 'approuve' ? 'désapprouver' : 'approuver'; ?> ce register ?', 'registers')"
                                                                    type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                                                    <span class="uil-<?php echo $row['status'] == 'approuve' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                                                                </button>
                                                            <?php endif; ?>

                                                            <?php if (checkPermis($db, 'delete')) : ?>
                                                                <button title="Supprimer" onclick="deleteData(<?php echo $row['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce register ?', 'registers')"
                                                                    type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                                                    <span class="uil-trash-alt fs-8"></span>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Graphisme  -->
                            <h5 class="m-2 text-semibold"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h5>
                            <div class="row mx-0 mb-3">
                                <div class="col-lg-6 col-12">
                                    <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                        <div class="card-body p-2" id="chartRegistreSecteur"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12">
                                    <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                        <div class="card-body p-2" id="chartRegistreGaz"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Graphique détaillé par catégorie -->
                            <?php if (!empty($js_detail_data) && count($ges_data) > 1) { ?>
                            <div class="row mx-0 mb-3">
                                <div class="col-12">
                                    <div class="card rounded-1 shadow-sm border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Détail des émissions par catégorie et type de gaz</h6>
                                        </div>
                                        <div class="card-body p-2" id="chartRegistreDetail"></div>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="text-center py-5 my-3" style="min-height: 350px;">
                                <div class="d-flex justify-content-center mb-3">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                        <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h4 class="text-800 mb-3">Aucune données trouvée</h4>
                                <p class="text-600 mb-5">Veuillez ajouter des données pour afficher ses graphiques</p>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#importRegisterModal" data-secteur="<?php echo $sel_secteur; ?>" class="btn btn-subtle-primary btn-sm">
                                    <span class="fa fa-database fs-9 me-2"></span>Importer des données
                                </button>
                            </div>
                        <?php } ?>
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
    <?php if (!empty($grouped_registers[$sel_secteur])) { ?>
        // Graphique des émissions par catégorie
        mrvBarChart({
            id: 'chartRegistreSecteur',
            title: 'Émissions totales par catégorie (sous-secteur)',
            unite: 'kt eq. CO₂',
            categories: <?= json_encode($column_categories ?? []) ?>,
            data: <?= json_encode($column_data ?? []) ?>,
            desaggregate: true,
            name: 'Catégorie',
            name2: 'Gaz',
            title2: 'Répartition par type de gaz',
            detailData: <?= json_encode($js_detail_data ?? []) ?>
        });

        // Graphique camembert par type de gaz
        mrvPieChart({
            id: 'chartRegistreGaz',
            title: 'Répartition des émissions par type de gaz',
            unite: 'kt eq. CO₂',
            data: <?= json_encode($ges_data ?? []) ?>,
        });
        
        // Graphique détaillé par catégorie (si disponible et si plus d'un type de gaz)
        <?php if (!empty($js_detail_data) && count($ges_data) > 1) { ?>
        setTimeout(function() {
            mrvStackedBarChart({
                id: 'chartRegistreDetail',
                title: 'Détail des émissions par catégorie',
                unite: 'kt eq. CO₂',
                categories: <?= json_encode(array_keys($js_detail_data)) ?>,
                series: [
                    <?php 
                    // Préparer les séries par type de gaz
                    $gaz_series = [];
                    foreach ($ges_data as $gaz_item) {
                        $gaz_name = $gaz_item['name'];
                        $series_data = [];
                        foreach ($js_detail_data as $category => $data) {
                            $value = 0;
                            foreach ($data as $item) {
                                if ($item['name'] === $gaz_name) {
                                    $value = $item['y'];
                                    break;
                                }
                            }
                            $series_data[] = $value;
                        }
                        $gaz_series[] = [
                            'name' => $gaz_name,
                            'data' => $series_data
                        ];
                    }
                    echo json_encode($gaz_series);
                    ?>
                ]
            });
        }, 500);
        <?php } ?>
    <?php } ?>

    // Fonction pour les graphiques à barres empilées
    function mrvStackedBarChart(options) {
        Highcharts.chart(options.id, {
            chart: {
                type: 'column'
            },
            title: {
                text: options.title
            },
            xAxis: {
                categories: options.categories,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: options.unite
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold'
                    }
                }
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: false
                    }
                }
            },
            series: options.series
        });
    }
</script>

</html>