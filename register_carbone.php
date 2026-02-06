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

    $inventory = new Inventory($db);
    $inventories = $inventory->read();
    $inventories_afficher = array_filter($inventories, function ($inventory) {
        return $inventory['afficher'] == 'oui';
    });
    $active_inventory = array_pop($inventories_afficher);

    $register = new Register($db);
    $registers = $register->read();
    $registers = array_filter($registers, function ($register) use ($active_inventory) {
        return $register['inventaire_id'] == $active_inventory['id'];
    });

    $registers_secteurs = [];
    foreach ($registers as $register) $registers_secteurs[$register['secteur_id']][] = $register;

    $registers_gaz = [];
    foreach ($registers as $register) $registers_gaz[$register['gaz']][] = $register;

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
        return $secteur['parent'] == 0;
    });

    $gaz = new Gaz($db);
    $gazs = $gaz->read();
    $gaz_colors = [];
    foreach ($gazs as $g) $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);

    $currFilSecteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
    $currFilGaz = isset($_GET['gaz']) ? $_GET['gaz'] : '';
    $global_mode = (empty($currFilSecteur) && empty($currFilGaz)) && !empty($registers);
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
            $secteur_id = $row['secteur_id'];
            $annee = $row['annee'];
            $gaz_name = strtoupper(trim($row['gaz']));

            $normalized_gaz = $gaz_name;
            foreach ($gazs as $system_gaz) {
                if ($gaz_name === strtoupper($system_gaz['name'])) {
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
                    $sector_data[] = isset($sector_annual_totals[$secteur_id][$year]) ? round($sector_annual_totals[$secteur_id][$year], 3) : 0;
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
                    if ($row['secteur_id'] == $secteur_id) {
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
    } elseif ($currFilSecteur && !empty($registers_secteurs[$currFilSecteur])) {
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

        foreach ($registers_secteurs[$currFilSecteur] as $row) {
            $category = $row['categorie'];
            $annee = $row['annee'];
            $gaz_name = strtoupper(trim($row['gaz']));
            $normalized_gaz = $gaz_name;

            foreach ($gazs as $system_gaz) {
                if ($gaz_name === strtoupper($system_gaz['name'])) {
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
        foreach ($registers_secteurs[$currFilSecteur] as $row) {
            $gaz_name = strtoupper(trim($row['gaz']));
            $normalized_gaz = $gaz_name;

            foreach ($gazs as $system_gaz) {
                if ($gaz_name === strtoupper($system_gaz['name'])) {
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
            foreach ($registers_secteurs[$currFilSecteur] as $row) {
                if ($row['categorie'] == $category) {
                    $niveau_total += $row['emission_niveau'];
                    $count++;
                }
            }
            $niveau_moyen = $count > 0 ? $niveau_total / $count : 0;

            $sector_performance[] = [
                'name' => $category,
                'emissions' => round($total, 3),
                'niveau' => round($niveau_moyen, 3)
            ];
        }

        $stacked_gaz_series = [];
        foreach ($gaz_annual_data as $gaz => $annual_values) {
            $series_data = [];
            ksort($annual_values);
            foreach ($annual_values as $annee => $value) {
                $series_data[] = round($value ?? 0, 3);
            }

            $stacked_gaz_series[] = [
                'name' => $gaz,
                'data' => $series_data,
                'color' => $gaz_colors[$gaz] ?? '#' . substr(md5($gaz), 0, 6)
            ];
        }

        $stacked_years = array_keys($annual_totals);
        sort($stacked_years);
    } elseif ($currFilGaz && !empty($registers_gaz[$currFilGaz])) {
        $gaz_name = $currFilGaz;
        $filtered_registers = $registers_gaz[$currFilGaz];

        $gaz_info = array_filter($gazs, function ($g) use ($gaz_name) {
            return strtoupper($g['name']) === strtoupper($gaz_name);
        });
        $gaz_info = !empty($gaz_info) ? reset($gaz_info) : ['name' => $gaz_name, 'couleur' => '#' . substr(md5($gaz_name), 0, 6)];

        $column_categories = [];
        $column_data = [];
        $ges_data = [];
        $trend_data = [];
        $sector_data = [];
        $annual_data = [];
        $cumulative_data = [];
        $gaz_annual_data = [];
        $sector_performance = [];

        $sector_totals = [];
        $annual_totals = [];
        $cumulative_totals = [];
        $category_totals = [];

        // Agrégation des données par secteur
        foreach ($filtered_registers as $row) {
            $secteur_id = $row['secteur_id'];
            $annee = $row['annee'];
            $category = $row['categorie'];

            if (!isset($sector_totals[$secteur_id])) $sector_totals[$secteur_id] = 0;
            $sector_totals[$secteur_id] += $row['emission_absolue'];

            if (!isset($annual_totals[$annee])) $annual_totals[$annee] = 0;
            $annual_totals[$annee] += $row['emission_absolue'];

            if (!isset($cumulative_totals[$annee])) $cumulative_totals[$annee] = 0;
            $cumulative_totals[$annee] += $row['emission_cumulee'];

            if (!isset($category_totals[$category])) $category_totals[$category] = 0;
            $category_totals[$category] += $row['emission_absolue'];
        }

        // 1. Graphique secteur (colonnes)
        foreach ($sector_totals as $secteur_id => $total) {
            $secteur_name = '';
            foreach ($data_secteurs as $s) {
                if ($s['id'] == $secteur_id) {
                    $secteur_name = $s['name'];
                    break;
                }
            }
            if ($secteur_name) {
                $column_categories[] = $secteur_name;
                $column_data[] = $total;
            }
        }

        // 2. Graphique répartition (camembert) - Pour filtre par gaz, on montre la répartition par secteur
        $gaz_color = $gaz_info['couleur'] ?? '#' . substr(md5($gaz_name), 0, 6);
        foreach ($sector_totals as $secteur_id => $total) {
            if ($total > 0) {
                $secteur_name = '';
                foreach ($data_secteurs as $s) {
                    if ($s['id'] == $secteur_id) {
                        $secteur_name = $s['name'];
                        break;
                    }
                }
                if ($secteur_name) {
                    $ges_data[] = [
                        'name' => $secteur_name,
                        'y' => $total,
                        // 'color' => $secteur_color
                    ];
                }
            }
        }

        // 3. Graphique tendance (ligne)
        ksort($annual_totals);
        foreach ($annual_totals as $annee => $total) {
            $trend_data[] = ['annee' => $annee, 'total' => $total];
            $annual_data[] = ['name' => $annee, 'y' => $total];
        }

        // 4. Données cumulées
        ksort($cumulative_totals);
        foreach ($cumulative_totals as $annee => $total) {
            $cumulative_data[] = ['name' => $annee, 'y' => $total];
        }


        // 6. Performance par secteur (pour graphique combo colonnes/ligne)
        foreach ($sector_totals as $secteur_id => $total) {
            $secteur_name = '';
            foreach ($data_secteurs as $s) {
                if ($s['id'] == $secteur_id) {
                    $secteur_name = $s['name'];
                    break;
                }
            }

            if ($secteur_name) {
                $niveau_total = 0;
                $count = 0;
                foreach ($filtered_registers as $row) {
                    if ($row['secteur_id'] == $secteur_id) {
                        $niveau_total += $row['emission_niveau'];
                        $count++;
                    }
                }
                $niveau_moyen = $count > 0 ? $niveau_total / $count : 0;

                $sector_performance[] = [
                    'name' => $secteur_name,
                    'emissions' => round($total, 3),
                    'niveau' => round($niveau_moyen, 3)
                ];
            }
        }

        usort($sector_performance, function ($a, $b) {
            return $b['emissions'] <=> $a['emissions'];
        });

        // 7. Données annuelles par secteur pour graphique stacked
        $stacked_sector_series = [];
        $stacked_years = array_keys($annual_totals);
        sort($stacked_years);

        foreach ($sector_totals as $secteur_id => $total) {
            $secteur_name = '';
            foreach ($data_secteurs as $s) {
                if ($s['id'] == $secteur_id) {
                    $secteur_name = $s['name'];
                    break;
                }
            }

            if ($secteur_name) {
                $sector_annual_values = [];
                foreach ($filtered_registers as $row) {
                    if ($row['secteur_id'] == $secteur_id) {
                        $annee = $row['annee'];
                        if (!isset($sector_annual_values[$annee])) {
                            $sector_annual_values[$annee] = 0;
                        }
                        $sector_annual_values[$annee] += $row['emission_absolue'];
                    }
                }

                $series_data = [];
                foreach ($stacked_years as $year) {
                    $series_data[] = round($sector_annual_values[$year] ?? 0, 3);
                }

                $stacked_sector_series[] = [
                    'name' => $secteur_name,
                    'data' => $series_data,
                    // 'color' => $secteur_color
                ];
            }
        }

        // 8. Données pour tableau récapitulatif
        $table_data = [];
        foreach ($sector_totals as $secteur_id => $total) {
            $secteur_name = '';
            foreach ($data_secteurs as $s) {
                if ($s['id'] == $secteur_id) {
                    $secteur_name = $s['name'];
                    break;
                }
            }

            if ($secteur_name) {
                // Données annuelles pour ce secteur
                $sector_annual = [];
                foreach ($filtered_registers as $row) {
                    if ($row['secteur_id'] == $secteur_id) {
                        $annee = $row['annee'];
                        if (!isset($sector_annual[$annee])) {
                            $sector_annual[$annee] = 0;
                        }
                        $sector_annual[$annee] += $row['emission_absolue'];
                    }
                }

                // Catégories pour ce secteur
                $sector_categories = [];
                foreach ($filtered_registers as $row) {
                    if ($row['secteur_id'] == $secteur_id) {
                        $category = $row['categorie'];
                        if (!isset($sector_categories[$category])) {
                            $sector_categories[$category] = 0;
                        }
                        $sector_categories[$category] += $row['emission_absolue'];
                    }
                }

                $categories_html = [];
                foreach ($sector_categories as $cat => $val) {
                    $categories_html[] = '<span class="badge bg-info-subtle text-dark rounded-0">' . $cat . '</span> : ' . number_format($val, 2);
                }

                $table_data[] = [
                    'secteur_id' => $secteur_id,
                    'secteur_nom' => $secteur_name,
                    'total_emissions' => $total,
                    'annee_data' => $sector_annual,
                    'categories_repartition' => implode(' | ', $categories_html)
                ];
            }
        }

        usort($table_data, function ($a, $b) {
            return $b['total_emissions'] <=> $a['total_emissions'];
        });
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
                        <h4 class="my-1 fw-black fs-8">
                            Registre des émissions (<a href="./documents/Registre_Carbone.xlsx" download class="fs-8 text-decoration-none"> <span class="fa fa-file-excel"></span> Canevas </a>)
                        </h4>
                    </div>

                    <div class="col-lg-8">
                        <div class="d-flex justify-content-md-end gap-3">
                            <div class="d-flex gap-1 align-items-center">
                                <span class="form-label">Filtrer : </span>
                                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                    <form class="position-relative">
                                        <select class="form-select form-select-sm bg-warning-subtle px-2 rounded-1" id="secteurFilter"
                                            onchange="pagesFilters([{ id: 'secteurFilter', param: 'secteur' }])">
                                            <option value="">Tous secteurs</option>
                                            <?php if (isset($secteurs) && !empty($secteurs)): ?>
                                                <?php foreach ($secteurs as $secteur): ?>
                                                    <option value="<?= $secteur['id'] ?>" <?= ($currFilSecteur == $secteur['id']) ? 'selected' : '' ?>>
                                                        <?= $secteur['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </form>
                                </div>
                                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                    <form class="position-relative">
                                        <select class="form-select form-select-sm bg-warning-subtle px-2 rounded-1" id="gazFilter"
                                            onchange="pagesFilters([{ id: 'gazFilter', param: 'gaz' }])">
                                            <option value="">Tous gaz</option>
                                            <?php if (isset($gazs) && !empty($gazs)): ?>
                                                <?php foreach ($gazs as $gaz): ?>
                                                    <option value="<?= $gaz['name'] ?>" <?= ($currFilGaz == $gaz['name']) ? 'selected' : '' ?>>
                                                        <?= $gaz['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <?php if (empty($currFilSecteur) && empty($currFilGaz)) { ?>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#importRegisterModal" class="btn btn-subtle-primary btn-sm"
                                    data-inventory="<?php echo $active_inventory['id']; ?>">
                                    <span class="fa fa-database fs-9 me-2"></span>Importer données
                                </button>
                            <?php } else { ?>
                                <button type="button" onclick="window.location.href=`<?= $_SERVER['PHP_SELF'] ?>`" class="btn btn-subtle-primary btn-sm">
                                    <span class="fa fa-arrow-left fs-9 me-2"></span>Vue globale des données
                                </button>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 px-1 pb-3 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if ($global_mode || (!empty($currFilSecteur) && !empty($registers_secteurs[$currFilSecteur])) || (!empty($currFilGaz) && !empty($registers_gaz[$currFilGaz]))) { ?>
                            <h5 class="m-2 text-semibold"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h5>

                            <?php if ($global_mode) { ?>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-pie me-2"></i>Répartition globale par type de gaz</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalGaz" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Émissions par secteur et année</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalSecteurAnnee" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-column me-2"></i>Performance globale par secteur</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGlobalPerformance" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-2 text-semibold mt-4"><i class="fas fa-table me-2"></i>Synthèse par secteur</h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-primary-subtle">
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

                            <?php } else if (!empty($currFilSecteur)) { ?>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-pie me-2"></i>Répartition par type de gaz</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartRegistreGaz" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Émissions par catégorie</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartRegistreSecteur" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-line me-2"></i>Tendance annuelle</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartTendanceAnnuelle" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-area me-2"></i>Émissions cumulées</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartCumulative" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-column me-2"></i>Performance par catégorie</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartPerformance" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-2 text-semibold mt-4"><i class="fas fa-table me-2"></i>Liste des émissions</h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                        <thead class="bg-primary-subtle">
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
                                            <?php foreach ($registers_secteurs[$currFilSecteur] as $row) { ?>
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
                            <?php } else if (!empty($currFilGaz)) { ?>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-pie me-2"></i>Répartition par secteur</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGazSecteur" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-bar me-2"></i>Émissions par secteur</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGazEmissions" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-line me-2"></i>Tendance annuelle</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGazTendance" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12 mb-1">
                                        <div class="card rounded-1 shadow-sm border h-100">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-area me-2"></i>Évolution par année</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGazEvolution" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border">
                                            <div class="card-header bg-light p-2">
                                                <h6 class="mb-0 text-dark"><i class="fas fa-chart-column me-2"></i>Performance par secteur</h6>
                                            </div>
                                            <div class="card-body p-2" id="chartGazPerformance" style="min-height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-2 text-semibold mt-4"><i class="fas fa-table me-2"></i>Émissions du gaz <?php echo htmlspecialchars($currFilGaz); ?></h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                                        <thead class="bg-primary-subtle">
                                            <tr>
                                                <th class="sort align-middle text-uppercase">Année</th>
                                                <th class="sort align-middle text-uppercase">Secteur</th>
                                                <th class="sort align-middle text-uppercase">Catégorie</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Année</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Absolue</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Niveau Emission</th>
                                                <th class="sort align-middle text-uppercase" style="width: 8%;">Emission Cumulée</th>
                                                <th class="sort align-middle text-uppercase" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-gaz-body">
                                            <?php
                                            if (!empty($table_data)) {
                                                foreach ($table_data as $sector_data) {
                                                    $secteur_id = $sector_data['secteur_id'];
                                                    $sector_rows = array_filter($registers_gaz[$currFilGaz], function ($row) use ($secteur_id) {
                                                        return $row['secteur_id'] == $secteur_id;
                                                    });

                                                    foreach ($sector_rows as $row) {
                                            ?>
                                                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                            <td class="align-middle px-2"><?php echo $row['annee']; ?></td>
                                                            <td class="align-middle px-2">
                                                                <strong><?php
                                                                        $secteur_name = '';
                                                                        foreach ($data_secteurs as $s) {
                                                                            if ($s['id'] == $row['secteur_id']) {
                                                                                $secteur_name = $s['name'];
                                                                                break;
                                                                            }
                                                                        }
                                                                        echo htmlspecialchars($secteur_name);
                                                                        ?></strong>
                                                            </td>
                                                            <td class="align-middle px-2">
                                                                <strong><?php echo $row['code']; ?></strong> - <?php echo $row['categorie']; ?>
                                                            </td>
                                                            <td class="align-middle px-2"><?php echo number_format($row['emission_annee'], 2); ?></td>
                                                            <td class="align-middle px-2"><?php echo number_format($row['emission_absolue'], 2); ?></td>
                                                            <td class="align-middle px-2"><?php echo number_format($row['emission_niveau'], 2); ?></td>
                                                            <td class="align-middle px-2"><?php echo number_format($row['emission_cumulee'], 2); ?></td>
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
                                                    <?php
                                                    }
                                                }
                                            } else {
                                                // Si $table_data n'est pas défini, afficher toutes les lignes filtrées
                                                foreach ($registers_gaz[$currFilGaz] as $row) {
                                                    ?>
                                                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                        <td class="align-middle px-2"><?php echo $row['annee']; ?></td>
                                                        <td class="align-middle px-2">
                                                            <strong><?php
                                                                    $secteur_name = '';
                                                                    foreach ($data_secteurs as $s) {
                                                                        if ($s['id'] == $row['secteur_id']) {
                                                                            $secteur_name = $s['name'];
                                                                            break;
                                                                        }
                                                                    }
                                                                    echo htmlspecialchars($secteur_name);
                                                                    ?></strong>
                                                        </td>
                                                        <td class="align-middle px-2">
                                                            <strong><?php echo $row['code']; ?></strong> - <?php echo $row['categorie']; ?>
                                                        </td>
                                                        <td class="align-middle px-2"><?php echo number_format($row['emission_annee'], 2); ?></td>
                                                        <td class="align-middle px-2"><?php echo number_format($row['emission_absolue'], 2); ?></td>
                                                        <td class="align-middle px-2"><?php echo number_format($row['emission_niveau'], 2); ?></td>
                                                        <td class="align-middle px-2"><?php echo number_format($row['emission_cumulee'], 2); ?></td>
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
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="text-center py-5 my-3" style="min-height: 350px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="#6c757d" class="mb-5" viewBox="0 0 16 16">
                                    <path d="M0 0h1v16H0V0zm1 15h15v1H1v-1zm1-1h13V1H2v13zm1-1V2h11v11H3zm1-2h2v-2H4v2zm3 0h2V5H7v6zm3 0h2V8h-2v3z" />
                                </svg>
                                <h5 class="text-muted">Aucune visualisation disponible</h5>
                                <p class="text-secondary">Aucun graphique n’a pu être généré à partir des données actuelles.</p>
                                <p class="text-600 text-warning my-5">
                                    <?php if (empty($registers)) { ?>
                                        Aucun registre n'a été importé. Veuillez sélectionner un secteur et importer des données.
                                    <?php } else { ?>
                                        Sélectionnez un secteur ou un gaz pour voir ses données détaillées.
                                    <?php } ?>
                                </p>
                                <?php if (!empty($registers)) { ?>
                                    <p class="text-600">
                                        Vous pouvez aussi voir <a href="register_carbone.php" class="fw-bold">la vue globale</a>
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
                yAxis: 0
            }, {
                name: 'Niveau moyen d\'émission',
                type: 'line',
                data: <?= json_encode(array_column($global_sector_performance, 'niveau') ?? []) ?>,
                yAxis: 1
            }],
            yAxisTitles: ['Gg eq. CO₂', 'Niveau']
        });

    <?php } elseif (!empty($currFilSecteur) && !empty($registers_secteurs[$currFilSecteur])) { ?>
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
                data: <?= json_encode(array_column($cumulative_data, 'y') ?? []) ?>
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
                yAxis: 0
            }, {
                name: 'Niveau moyen d\'émission',
                type: 'line',
                data: <?= json_encode(array_column($sector_performance, 'niveau') ?? []) ?>,
                yAxis: 1
            }],
            yAxisTitles: ['Gg eq. CO₂', 'Niveau']
        });
    <?php } elseif (!empty($currFilGaz) && !empty($registers_gaz[$currFilGaz])) { ?>
        mrvDonutChart({
            id: 'chartGazSecteur',
            title: 'Répartition des émissions de <?php echo htmlspecialchars($currFilGaz); ?> par secteur',
            unite: 'Gg eq. CO₂',
            data: <?= json_encode($ges_data ?? []) ?>,
        });

        mrvBarChart({
            id: 'chartGazEmissions',
            title: 'Émissions de <?php echo htmlspecialchars($currFilGaz); ?> par secteur',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode($column_categories ?? []) ?>,
            data: <?= json_encode($column_data ?? []) ?>,
        });

        mrvLineChart({
            id: 'chartGazTendance',
            title: 'Évolution des émissions de <?php echo htmlspecialchars($currFilGaz); ?>',
            unite: 'Gg eq. CO₂',
            categories: <?= json_encode(array_column($trend_data ?? [], 'annee') ?? []) ?>,
            data: <?= json_encode(array_column($trend_data ?? [], 'total') ?? []) ?>,
        });

        <?php if (!empty($stacked_sector_series)): ?>
            mrvStackGroupChart({
                id: 'chartGazEvolution',
                title: 'Évolution annuelle par secteur',
                unite: 'Gg eq. CO₂',
                categories: <?= json_encode($stacked_years ?? []) ?>,
                series: <?= json_encode($stacked_sector_series ?? []) ?>,
            });
        <?php else: ?>
            mrvAreaChart({
                id: 'chartGazEvolution',
                title: 'Évolution des émissions cumulées',
                unite: 'Gg eq. CO₂',
                categories: <?= json_encode(array_column($cumulative_data ?? [], 'name') ?? []) ?>,
                series: [{
                    name: 'Émissions cumulées',
                    data: <?= json_encode(array_column($cumulative_data ?? [], 'y') ?? []) ?>
                }]
            });
        <?php endif; ?>

        mrvDualAxisChart({
            id: 'chartGazPerformance',
            title: 'Performance des secteurs pour <?php echo htmlspecialchars($currFilGaz); ?>',
            categories: <?= json_encode(array_column($sector_performance ?? [], 'name') ?? []) ?>,
            series: [{
                name: 'Émissions totales (Gg eq. CO₂)',
                type: 'column',
                data: <?= json_encode(array_column($sector_performance ?? [], 'emissions') ?? []) ?>,
                yAxis: 0
            }, {
                name: 'Niveau moyen d\'émission',
                type: 'line',
                data: <?= json_encode(array_column($sector_performance ?? [], 'niveau') ?? []) ?>,
                yAxis: 1
            }],
            yAxisTitles: ['Gg eq. CO₂', 'Niveau']
        });
    <?php } ?>
</script>

</html>