<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Inventaires GES | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $sel_inventory = isset($_GET['inventory']) ? $_GET['inventory'] : null;
    $inventory = new Inventory($db);
    $inventories = $inventory->read();
    $active_inventory = array_filter($inventories, function ($inventory) {
        return $inventory['afficher'] == 'oui';
    });

    $secteur = new Secteur($db);
    $secteurs = $secteur->read();
    $secteurs = array_filter($secteurs, function ($secteur) {
        return $secteur['parent'] == 0;
    });

    $unite = new Unite($db);
    $unites = $unite->read();

    if (!empty($inventories) && !$sel_inventory) {
        $sel_inventory = array_pop($active_inventory)['id'];
    }

    if ($sel_inventory) {
        $inventory->id = $sel_inventory;
        $current_inventory = $inventory->readById();

        if (isset($current_inventory['viewtable'])) {
            $current_inventory_data = json_decode($inventory->readData($current_inventory['viewtable']), true);
            $secteurs_inventory = ['agriculture' => 'Agriculture', 'fat' => 'FAT', 'energie' => 'Énergie', 'piup' => 'PIUP', 'dechets' => 'Déchets'];

            if (!empty($current_inventory_data['data'])) {
                // ==================> Graphisme Par secteur
                try {
                    $g1_column_categories = [];
                    $g1_column_data = [];
                    foreach ($secteurs_inventory as $col => $label) {
                        if ($col === 'piup') continue;

                        $total = 0;
                        foreach ($current_inventory_data['data'] as $row) {
                            $total += normalizeNumber($row[$col] ?? 0);
                        }

                        $g1_column_categories[] = $label;
                        $g1_column_data[] = round($total, 3);
                    }
                } catch (\Throwable $th) {
                    var_dump($th);
                    die();
                }

                // ==================> Graphisme Evolution nette
                try {
                    $g2_years = [];
                    $g2_net_emissions_by_year = [];
                    foreach ($current_inventory_data['data'] as $row) {
                        $year = (int) ($row['annee'] ?? 0);
                        if (!$year) continue;

                        $net = normalizeNumber($row['emissions_nettes'] ?? (($row['total_emissions'] ?? 0) - ($row['total_absorptions'] ?? 0)));
                        if (!isset($g2_net_emissions_by_year[$year])) $g2_net_emissions_by_year[$year] = 0;
                        $g2_net_emissions_by_year[$year] += $net;
                    }

                    ksort($g2_net_emissions_by_year);
                    $g2_years = array_keys($g2_net_emissions_by_year);
                    $g2_values = array_map(fn($v) => round($v, 3), array_values($g2_net_emissions_by_year));
                } catch (\Throwable $th) {
                    var_dump($th);
                    die();
                }

                // ==================> Graphisme Evolution vs Absorption
                try {
                    $g3_years = [];
                    $g3_emissions = [];
                    $g3_absorptions = [];

                    foreach ($current_inventory_data['data'] as $row) {
                        $year = (int)$row['annee'];
                        if (!$year) continue;

                        $g3_years[] = $year;
                        $g3_emissions[] = round(normalizeNumber($row['total_emissions'] ?? 0), 3);
                        $g3_absorptions[] = round(abs(normalizeNumber($row['total_absorptions'] ?? 0)), 3);
                    }
                } catch (\Throwable $th) {
                    var_dump($th);
                    die();
                }

                // ==================> Contribution relative des secteurs
                try {
                    $g4_sector_share = [];
                    foreach ($secteurs_inventory as $col => $label) {
                        $total = 0;
                        foreach ($current_inventory_data['data'] as $row) {
                            $total += normalizeNumber($row[$col] ?? 0);
                        }
                        if ($total > 0) {
                            $g4_sector_share[] = [
                                'name' => $label,
                                'y' => round($total, 3)
                            ];
                        }
                    }
                } catch (\Throwable $th) {
                    var_dump($th);
                    die();
                }
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
                            Inventaires GES (<a href="./documents/Inventaire_GES.xlsx" download class="fs-8 text-decoration-none"> <span class="fa fa-file-excel"></span> Canevas </a>)
                        </h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1" name="result" id="resultID" onchange="window.location.href = 'inventory.php?inventory=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner un inventaire---</option>
                                <?php foreach ($inventories as $inventory) { ?>
                                    <option value="<?php echo $inventory['id']; ?>" <?php if ($sel_inventory == $inventory['id']) echo 'selected'; ?>><?php echo $inventory['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 d-flex gap-2 justify-content-lg-end">
                        <?php if (!empty($current_inventory)) { ?>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addDataInventoryModal" data-inventory="<?php echo $sel_inventory; ?>" class="btn btn-subtle-primary btn-sm">
                                <span class="fa fa-database fs-9 me-2"></span>Importer données
                            </button>
                        <?php } ?>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#addInventoryModal" class="btn btn-subtle-primary btn-sm">
                            <span class="fa fa-table fs-9 me-2"></span>Inventaire
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 px-1 pb-3 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if (!empty($inventories)) { ?>
                            <?php if (!empty($current_inventory_data)) { ?>
                                <!-- <h5 class="m-2 text-semibold"><i class="fas fa-info me-2"></i>Description</h5>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-12">
                                        <div class="card rounded-1 shadow-sm border h-100 p-3">
                                            <p>Conformément à ses engagements au titre de l’article 13 de l’Accord de Paris sur le climat,
                                                qui établit le cadre d’une transparence renforcée, le Burundi produit son 5ème rapport
                                                d’inventaire des gaz à effet de serre (GES) dans les cinq (5) secteurs : </p>
                                            <ul class="list-unstyled mb-0">
                                                <?php foreach ($secteurs as $secteur): ?>
                                                    <li class="d-flex align-items-start gap-3 pb-1">
                                                        <span class="fa fa-check-circle text-primary mt-1"></span>
                                                        <span class="text-body"><?= $secteur['description']? $secteur['description']." (".$secteur['name'].")": $secteur['name'] ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div> -->

                                <!-- Graphisme  -->
                                <h5 class="m-2 text-semibold"><i class="fas fa-chart-line me-2"></i>Visualisation des Données</h5>
                                <div class="row mx-0 mb-3 g-3">
                                    <div class="col-lg-6 col-12">
                                        <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                            <div class="card-body p-2" id="chartInventorySecteur"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-12">
                                        <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                            <div class="card-body p-2" id="chartInventorySectorShare"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-12">
                                        <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                            <div class="card-body p-2" id="chartInventoryNetEvolution"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-12">
                                        <div class="card rounded-1 shadow-sm border h-100" style="min-height: 400px;">
                                            <div class="card-body p-2" id="chartInventoryEmiVSAbsorp"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tableau des données -->
                                <h5 class="m-2 text-semibold"><i class="fas fa-table me-2"></i>Liste des Inventaires</h5>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-primary-subtle">
                                            <tr>
                                                <?php foreach ($current_inventory_data['columns'] as $column): ?>
                                                    <th class="sort align-middle text-uppercase">
                                                        <?= htmlspecialchars($column) ?>
                                                    </th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>

                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($current_inventory_data['data'] as $row): ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <?php foreach ($row as $key => $value): ?>
                                                        <td class="align-middle px-2 <?= !in_array($key, ["id", "annee", "imported_at"]) ? 'text-end' : '' ?>">
                                                            <?= (is_numeric($value) && !in_array($key, ["id", "annee", "imported_at"])) ? number_format($value, 4, '.', ' ') : htmlspecialchars($value) ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="text-center py-5 my-3" style="min-height: 350px;">
                                    <div class="d-flex justify-content-center mb-3">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <h4 class="text-800 mb-3">Aucune données trouvée</h4>
                                    <p class="text-600 mb-5">Veuillez ajouter des données pour afficher ses graphiques</p>
                                    <?php if (!empty($current_inventory)) { ?>
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#addDataInventoryModal" data-inventory="<?php echo $sel_inventory; ?>" class="btn btn-subtle-primary btn-sm">
                                            <span class="fa fa-database fs-9 me-2"></span>Importer des données
                                        </button>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="text-center py-5 my-3" style="min-height: 350px;">
                                <div class="d-flex justify-content-center mb-3">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                        <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h4 class="text-800 mb-3">Aucun inventaire trouvé</h4>
                                <p class="text-600 mb-5">Veuillez ajouter un inventaire pour afficher ses indicateurs</p>
                                <button type="button" data-bs-toggle="modal" data-bs-target="#addInventoryModal" class="btn btn-subtle-primary rounded-1 btn-sm px-5">
                                    <span class="fa fa-plus fs-9 me-2"></span>Ajouter un inventaire
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
    mrvBarChart({
        id: 'chartInventorySecteur',
        title: 'Émissions par secteur (Sans absorptions)',
        unite: <?= json_encode($current_inventory['unite'] ?? "") ?>,
        categories: <?= json_encode($g1_column_categories ?? []) ?>,
        data: <?= json_encode($g1_column_data ?? []) ?>,
        desaggregate: false,
    });

    mrvLineChart({
        id: 'chartInventoryNetEvolution',
        title: 'Évolution des émissions nettes',
        unite: <?= json_encode($current_inventory['unite'] ?? 'Gg éq. CO₂') ?>,
        categories: <?= json_encode($g2_years ?? []) ?>,
        data: <?= json_encode($g2_values ?? []) ?>
    });

    mrvGroupedBarChart({
        id: 'chartInventoryEmiVSAbsorp',
        title: 'Émissions et absorptions par année',
        unite: <?= json_encode($current_inventory['unite'] ?? "") ?>,
        categories: <?= json_encode($g3_years ?? []) ?>,
        series: [{
            name: 'Émissions',
            data: <?= json_encode($g3_emissions ?? []) ?>
        }, {
            name: 'Absorptions',
            data: <?= json_encode($g3_absorptions ?? []) ?>
        }]
    });

    mrvDonutChart({
        id: 'chartInventorySectorShare',
        title: 'Part relative des émissions par secteur',
        unite: <?= json_encode($current_inventory['unite'] ?? "") ?>,
        data: <?= json_encode($g4_sector_share ?? []) ?>
    });
</script>

</html>