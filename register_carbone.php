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

    $gaz = new Gaz($db);
    $gazs = $gaz->read();
    $gaz_colors = [];
    foreach ($gazs as $g) {
        $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);
    }

    $sel_secteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
        return $secteur['parent'] == 0;
    });

    // Variables pour les données globales
    $global_mode = empty($sel_secteur) && !empty($registers);
    $global_data = [];

    if ($global_mode) {
        $global_column_data = [];
        $global_ges_data = [];
        $global_trend_data = [];
        $global_category_data = [];
        $global_sector_performance = [];
        $global_sector_annual_data = [];

        // 1. Préparation des données par secteur
        $sector_totals = [];
        $sector_gaz_totals = [];
        $sector_annual_totals = [];
        $sector_names = [];

        foreach ($data_secteurs as $s) {
            if ($s['parent'] == 0) {
                $sector_names[$s['id']] = $s['name'];
            }
        }

        // 2. Agrégation des données globales
        foreach ($registers as $row) {
            $secteur_id = $row['secteur'];
            $annee = $row['annee'];
            $gaz_name = strtoupper(trim($row['gaz']));

            $normalized_gaz = $gaz_name;
            foreach ($gazs as $system_gaz) {
                if (strpos($gaz_name, strtoupper($system_gaz['name'])) !== false) {
                    $normalized_gaz = strtoupper($system_gaz['name']);
                    break;
                }
            }

            if (!isset($sector_totals[$secteur_id])) {
                $sector_totals[$secteur_id] = 0;
            }
            $sector_totals[$secteur_id] += $row['emission_absolue'];

            if (!isset($sector_gaz_totals[$secteur_id][$normalized_gaz])) {
                $sector_gaz_totals[$secteur_id][$normalized_gaz] = 0;
            }
            $sector_gaz_totals[$secteur_id][$normalized_gaz] += $row['emission_absolue'];

            if (!isset($sector_annual_totals[$secteur_id][$annee])) {
                $sector_annual_totals[$secteur_id][$annee] = 0;
            }
            $sector_annual_totals[$secteur_id][$annee] += $row['emission_absolue'];
        }

        // 3. Préparation des données pour les graphiques globaux

        // a) Graphique secteur/année (colonnes groupées)
        $global_column_categories = [];
        $global_series_data = [];
        $years = [];

        foreach ($registers as $row) {
            if (!in_array($row['annee'], $years)) {
                $years[] = $row['annee'];
            }
        }
        sort($years);
        $global_column_categories = $years;

        foreach ($sector_totals as $secteur_id => $total) {
            if (isset($sector_names[$secteur_id])) {
                $sector_data = [];
                foreach ($years as $year) {
                    $sector_data[] = isset($sector_annual_totals[$secteur_id][$year]) ?
                        $sector_annual_totals[$secteur_id][$year] : 0;
                }

                $global_series_data[] = [
                    'name' => $sector_names[$secteur_id],
                    'data' => $sector_data
                ];
            }
        }

        // b) Graphique secteur/gaz (donut/stacked)
        $global_gaz_labels = [];
        $global_gaz_series = [];

        $gaz_global_totals = [];
        foreach ($sector_gaz_totals as $secteur_gazs) {
            foreach ($secteur_gazs as $gaz => $value) {
                if (!isset($gaz_global_totals[$gaz])) {
                    $gaz_global_totals[$gaz] = 0;
                }
                $gaz_global_totals[$gaz] += $value;
            }
        }

        foreach ($gaz_global_totals as $gaz => $total) {
            if ($total > 0) {
                $color = $gaz_colors[$gaz] ?? '#' . substr(md5($gaz), 0, 6);
                $global_ges_data[] = [
                    'name' => $gaz,
                    'y' => $total,
                    'color' => $color
                ];
            }
        }

        // c) Performance par secteur (colonnes et ligne)
        foreach ($sector_totals as $secteur_id => $total) {
            if (isset($sector_names[$secteur_id])) {
                $niveau_total = 0;
                $count = 0;
                foreach ($registers as $row) {
                    if ($row['secteur'] == $secteur_id) {
                        $niveau_total += $row['emission_niveau'];
                        $count++;
                    }
                }
                $niveau_moyen = $count > 0 ? $niveau_total / $count : 0;

                $global_sector_performance[] = [
                    'name' => $sector_names[$secteur_id],
                    'emissions' => $total,
                    'niveau' => $niveau_moyen
                ];
            }
        }

        usort($global_sector_performance, function ($a, $b) {
            return $b['emissions'] <=> $a['emissions'];
        });

        // 4. Préparation des données pour le tableau global
        $global_table_data = [];
        foreach ($sector_totals as $secteur_id => $total) {
            if (isset($sector_names[$secteur_id])) {
                $annee_data = [];
                if (isset($sector_annual_totals[$secteur_id])) {
                    foreach ($sector_annual_totals[$secteur_id] as $annee => $emission) {
                        $annee_data[$annee] = $emission;
                    }
                }

                $gaz_repartition = [];
                if (isset($sector_gaz_totals[$secteur_id])) {
                    foreach ($sector_gaz_totals[$secteur_id] as $gaz => $value) {
                        $gaz_repartition[] = '<span class="badge bg-warning-subtle text-dark rounded-0">' . $gaz . '</span> : ' . number_format($value, 2) . ' | ';
                    }
                }

                $global_table_data[] = [
                    'secteur_id' => $secteur_id,
                    'secteur_nom' => $sector_names[$secteur_id],
                    'total_emissions' => $total,
                    'annee_data' => $annee_data,
                    'gaz_repartition' => implode('', $gaz_repartition)
                ];
            }
        }

        usort($global_table_data, function ($a, $b) {
            return $b['total_emissions'] <=> $a['total_emissions'];
        });
    } elseif ($sel_secteur && !empty($grouped_registers[$sel_secteur])) {
        $column_categories = [];
        $column_data = [];
        $ges_data = [];
        $trend_data = [];
        $category_gaz_data = [];
        $annual_data = [];
        $cumulative_data = [];
        $gaz_annual_data = [];
        $sector_performance = [];

        $category_totals = [];
        $annual_totals = [];
        $cumulative_totals = [];

        foreach ($grouped_registers[$sel_secteur] as $row) {
            $category = $row['categorie'];
            $annee = $row['annee'];
            $gaz_name = strtoupper(trim($row['gaz']));
            $normalized_gaz = $gaz_name;

            foreach ($gazs as $system_gaz) {
                if (strpos($gaz_name, strtoupper($system_gaz['name'])) !== false) {
                    $normalized_gaz = strtoupper($system_gaz['name']);
                    break;
                }
            }

            if (!isset($category_totals[$category])) {
                $category_totals[$category] = 0;
                $column_categories[] = $category;
            }
            $category_totals[$category] += $row['emission_absolue'];

            if (!isset($annual_totals[$annee])) {
                $annual_totals[$annee] = 0;
            }
            $annual_totals[$annee] += $row['emission_absolue'];

            if (!isset($cumulative_totals[$annee])) {
                $cumulative_totals[$annee] = 0;
            }
            $cumulative_totals[$annee] += $row['emission_cumulee'];

            if (!isset($gaz_annual_data[$normalized_gaz])) {
                $gaz_annual_data[$normalized_gaz] = [];
            }
            if (!isset($gaz_annual_data[$normalized_gaz][$annee])) {
                $gaz_annual_data[$normalized_gaz][$annee] = 0;
            }
            $gaz_annual_data[$normalized_gaz][$annee] += $row['emission_absolue'];

            if (!isset($category_gaz_data[$category])) {
                $category_gaz_data[$category] = [];
            }
            if (!isset($category_gaz_data[$category][$normalized_gaz])) {
                $category_gaz_data[$category][$normalized_gaz] = 0;
            }
            $category_gaz_data[$category][$normalized_gaz] += $row['emission_absolue'];
        }

        $column_data = array_values($category_totals);
        $gaz_totals = [];
        foreach ($grouped_registers[$sel_secteur] as $row) {
            $gaz_name = strtoupper(trim($row['gaz']));
            $normalized_gaz = $gaz_name;

            foreach ($gazs as $system_gaz) {
                if (strpos($gaz_name, strtoupper($system_gaz['name'])) !== false) {
                    $normalized_gaz = strtoupper($system_gaz['name']);
                    break;
                }
            }

            if (!isset($gaz_totals[$normalized_gaz])) {
                $gaz_totals[$normalized_gaz] = 0;
            }
            $gaz_totals[$normalized_gaz] += $row['emission_absolue'];
        }

        foreach ($gaz_totals as $gaz => $total) {
            if ($total > 0) {
                $color = $gaz_colors[$gaz] ?? '#' . substr(md5($gaz), 0, 6);
                $ges_data[] = [
                    'name' => $gaz,
                    'y' => $total,
                    'color' => $color
                ];
            }
        }

        ksort($annual_totals);
        foreach ($annual_totals as $annee => $total) {
            $trend_data[] = ['annee' => $annee, 'total' => $total];
            $annual_data[] = ['name' => $annee, 'y' => $total];
        }

        ksort($cumulative_totals);
        foreach ($cumulative_totals as $annee => $total) {
            $cumulative_data[] = ['name' => $annee, 'y' => $total];
        }

        foreach ($category_totals as $category => $total) {
            $niveau_total = 0;
            $count = 0;
            foreach ($grouped_registers[$sel_secteur] as $row) {
                if ($row['categorie'] == $category) {
                    $niveau_total += $row['emission_niveau'];
                    $count++;
                }
            }
            $niveau_moyen = $count > 0 ? $niveau_total / $count : 0;

            $sector_performance[] = [
                'name' => $category,
                'emissions' => $total,
                'niveau' => $niveau_moyen
            ];
        }

        $stacked_gaz_series = [];
        foreach ($gaz_annual_data as $gaz => $annual_values) {
            $series_data = [];
            ksort($annual_values);
            foreach ($annual_values as $annee => $value) {
                $series_data[] = $value;
            }

            $stacked_gaz_series[] = [
                'name' => $gaz,
                'data' => $series_data,
                'color' => $gaz_colors[$gaz] ?? '#' . substr(md5($gaz), 0, 6)
            ];
        }

        $stacked_years = array_keys($annual_totals);
        sort($stacked_years);
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
                                <option value="" class="text-center" <?php echo empty($sel_secteur) ? 'selected' : ''; ?>>---Tous les secteurs---</option>
                                <?php foreach ($secteurs as $secteur) { ?>
                                    <option value="<?php echo $secteur['id']; ?>" <?php if ($sel_secteur == $secteur['id']) echo 'selected'; ?>><?php echo $secteur['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 d-flex gap-2 justify-content-lg-end">
                        <?php if (empty($sel_secteur)) { ?>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#importRegisterModal" data-secteur="<?php echo $sel_secteur; ?>" class="btn btn-subtle-primary btn-sm">
                                <span class="fa fa-database fs-9 me-2"></span>Importer Données
                            </button>
                        <?php } else { ?>
                            <button type="button" onclick="window.location.href=`<?= $_SERVER['PHP_SELF'] ?>`" class="btn btn-subtle-primary btn-sm">
                                <span class="fa fa-arrow-left fs-9 me-2"></span>Vue globale
                            </button>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 px-1 pb-3 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if ($global_mode || (!empty($sel_secteur) && !empty($grouped_registers[$sel_secteur]))) { ?>
                            <h5 class="m-2 text-semibold"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h5>

                            <?php if ($global_mode) { ?>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition globale par type de gaz</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalGaz" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Émissions par secteur et année</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalSecteurAnnee" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-column me-2"></i>Performance globale par secteur</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalPerformance" style="min-height: 400px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-2 text-semibold mt-4"><i class="fas fa-table me-2"></i>Synthèse par secteur</h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="sort align-middle text-uppercase">Secteur</th>
                                                <th class="sort align-middle text-uppercase">Émissions Totales (Gg eq. CO₂)</th>
                                                <th class="sort align-middle text-uppercase">Répartition par gaz</th>
                                                <th class="sort align-middle text-uppercase" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($global_table_data as $sector) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle px-2 fw-bold">
                                                        <?php echo $sector['secteur_nom']; ?>
                                                    </td>
                                                    <td class="align-middle px-2">
                                                        <div class="fw-bold"><?php echo number_format($sector['total_emissions'], 2); ?></div>
                                                        <?php if (!empty($sector['annee_data'])) { ?>
                                                            <div class="fs-9 text-muted">
                                                                <?php
                                                                foreach ($sector['annee_data'] as $annee => $emission) {
                                                                    echo '<span class="badge bg-primary-subtle text-dark rounded-0">' . $annee . '</span> : ' . number_format($emission, 2);
                                                                }
                                                                ?>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle px-2">
                                                        <?php echo $sector['gaz_repartition']; ?>
                                                    </td>
                                                    <td class="align-middle review px-2">
                                                        <div class="position-relative">
                                                            <div class="d-flex gap-1">
                                                                <a href="register_carbone.php?secteur=<?php echo $sector['secteur_id']; ?>"
                                                                    class="btn btn-sm btn-phoenix-primary px-2 py-1"
                                                                    title="Voir les détails">
                                                                    <span class="uil-eye fs-8"></span> Détails
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                            <?php } else { ?>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par type de gaz</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartRegistreGaz" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Émissions par catégorie</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartRegistreSecteur" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Tendance annuelle</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartTendanceAnnuelle" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>Émissions cumulées</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartCumulative" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0"><i class="fas fa-chart-column me-2"></i>Performance par catégorie</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartPerformance" style="min-height: 400px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-2 text-semibold mt-4"><i class="fas fa-table me-2"></i>Liste des émissions</h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="sort align-middle text-uppercase">Année</th>
                                                <th class="sort align-middle text-uppercase">Catégorie</th>
                                                <th class="sort align-middle text-uppercase" style="width: 5%;">Gaz</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Année</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Absolue</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Niveau Emission</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Cumulée</th>
                                                <th class="sort align-middle text-uppercase" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($grouped_registers[$sel_secteur] as $row) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle px-2"> <?php echo $row['annee']; ?> </td>
                                                    <td class="align-middle px-2"> <strong><?php echo $row['code']; ?></strong> - <?php echo $row['categorie']; ?> </td>
                                                    <td class="align-middle px-2">
                                                        <span class="badge" style="background-color: <?php echo $gaz_colors[strtoupper($row['gaz'])] ?? '#6c757d'; ?>; color: white;">
                                                            <?php echo $row['gaz']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle px-2"> <?php echo number_format($row['emission_annee'], 2); ?> </td>
                                                    <td class="align-middle px-2"> <?php echo number_format($row['emission_absolue'], 2); ?> </td>
                                                    <td class="align-middle px-2"> <?php echo number_format($row['emission_niveau'], 2); ?> </td>
                                                    <td class="align-middle px-2"> <?php echo number_format($row['emission_cumulee'], 2); ?> </td>
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
                            <?php } ?>

                        <?php } else { ?>
                            <div class="text-center py-5 my-3" style="min-height: 350px;">
                                <div class="d-flex justify-content-center mb-3">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                        <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h4 class="text-800 mb-3">Aucune donnée trouvée</h4>
                                <p class="text-600 mb-5">
                                    <?php if (empty($registers)) { ?>
                                        Aucun registre n'a été importé. Veuillez sélectionner un secteur et importer des données.
                                    <?php } else { ?>
                                        Sélectionnez un secteur pour voir ses données détaillées.
                                    <?php } ?>
                                </p>
                                <?php if (!empty($registers)) { ?>
                                    <p class="text-600">
                                        Vous pouvez aussi voir <a href="register_carbone.php" class="fw-bold">la vue globale de tous les secteurs</a>
                                    </p>
                                <?php } ?>
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
    <?php if ($global_mode) { ?>
        mrvDonutChart({
            id: 'chartGlobalGaz',
            title: 'Répartition globale des émissions par type de gaz',
            unite: 'Gg eq. CO₂',
            data: <?= json_encode($global_ges_data ?? []) ?>,
        });

        mrvGroupedBarChart({
            id: 'chartGlobalSecteurAnnee',
            title: 'Émissions par secteur et année',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode($global_column_categories ?? []) ?>,
            series: <?= json_encode($global_series_data ?? []) ?>
        });

        mrvDualAxisChart({
            id: 'chartGlobalPerformance',
            title: 'Performance globale par secteur',
            categories: <?= json_encode(array_column($global_sector_performance, 'name') ?? []) ?>,
            series: [{
                name: 'Émissions totales (Gg eq. CO₂)',
                type: 'column',
                data: <?= json_encode(array_column($global_sector_performance, 'emissions') ?? []) ?>,
                yAxis: 0,
                color: '#3498db'
            }, {
                name: 'Niveau moyen d\'émission',
                type: 'line',
                data: <?= json_encode(array_column($global_sector_performance, 'niveau') ?? []) ?>,
                yAxis: 1,
                color: '#e74c3c'
            }],
            yAxisTitles: ['Gg eq. CO₂', 'Niveau']
        });

    <?php } elseif (!empty($sel_secteur) && !empty($grouped_registers[$sel_secteur])) { ?>
        mrvDonutChart({
            id: 'chartRegistreGaz',
            title: 'Répartition des émissions par type de gaz',
            unite: 'Gg eq. CO₂',
            data: <?= json_encode($ges_data ?? []) ?>,
        });

        mrvBarChart({
            id: 'chartRegistreSecteur',
            title: 'Émissions par catégorie (sous-secteur)',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode($column_categories ?? []) ?>,
            data: <?= json_encode($column_data ?? []) ?>,
        });

        mrvLineChart({
            id: 'chartTendanceAnnuelle',
            title: 'Évolution des émissions annuelles',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode(array_column($trend_data, 'annee') ?? []) ?>,
            data: <?= json_encode(array_column($trend_data, 'total') ?? []) ?>,
        });

        mrvAreaChart({
            id: 'chartCumulative',
            title: 'Émissions cumulées au fil du temps',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode(array_column($cumulative_data, 'name') ?? []) ?>,
            series: [{
                name: 'Émissions cumulées',
                data: <?= json_encode(array_column($cumulative_data, 'y') ?? []) ?>,
                color: '#27ae60'
            }]
        });

        mrvDualAxisChart({
            id: 'chartPerformance',
            title: 'Performance par catégorie',
            categories: <?= json_encode(array_column($sector_performance, 'name') ?? []) ?>,
            series: [{
                name: 'Émissions totales (Gg eq. CO₂)',
                type: 'column',
                data: <?= json_encode(array_column($sector_performance, 'emissions') ?? []) ?>,
                yAxis: 0,
                color: '#3498db'
            }, {
                name: 'Niveau moyen d\'émission',
                type: 'line',
                data: <?= json_encode(array_column($sector_performance, 'niveau') ?? []) ?>,
                yAxis: 1,
                color: '#e74c3c'
            }],
            yAxisTitles: ['Gg eq. CO₂', 'Niveau']
        });
    <?php } ?>
</script>

</html>