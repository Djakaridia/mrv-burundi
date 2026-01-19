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

    $sel_annee = isset($_GET['annee']) ? $_GET['annee'] : date('Y');
    $inventory = new Inventory($db);
    $inventories = $inventory->read();
    $inventories = array_filter($inventories, function ($inventory) {
        return $inventory['state'] == 'actif';
    });

    $annee_inventories = array_map(function ($inventory) {
        return $inventory['annee'];
    }, $inventories);
    $annee_inventories = array_unique($annee_inventories);
    sort($annee_inventories);


    if (!empty($inventories) && !$sel_annee) {
        $sel_annee = $inventories[0]['annee'];
    }

    if ($sel_annee) {
        $inventory->annee = $sel_annee;
        $current_inventory = $inventory->readByAnnee();
        if ($current_inventory) {
            $current_inventory_data = json_decode($inventory->readData($current_inventory['viewtable']), true);

            if (!empty($current_inventory_data['data'])) {

                // ##############################################################
                $secteur_categories = [];
                $secteur_emissions = [];
                foreach ($current_inventory_data['data'] as $entreprise) {
                    $secteur = $entreprise['secteur'] ?? 'Non spécifié';
                    $ges_totaux = floatval($entreprise['ges_totaux_kt_eq_co2'] ?? $entreprise['ges_totaux'] ?? 0);

                    if (!isset($secteur_emissions[$secteur])) {
                        $secteur_emissions[$secteur] = 0;
                        $secteur_categories[] = $secteur;
                    }
                    $secteur_emissions[$secteur] += $ges_totaux;
                }

                $column_categories = $secteur_categories;
                $column_data = array_values($secteur_emissions);

                // ##############################################################
                $ges_co2 = 0;
                $ges_ch4 = 0;
                $ges_n2o = 0;

                foreach ($current_inventory_data['data'] as $entreprise) {
                    $ges_co2 += floatval($entreprise['co2_t_eq_co2'] ?? $entreprise['co2'] ?? 0);
                    $ges_ch4 += floatval($entreprise['ch4_t_eq_co2'] ?? $entreprise['ch4'] ?? 0);
                    $ges_n2o += floatval($entreprise['n2o_t_eq_co2'] ?? $entreprise['n2o'] ?? 0);
                }

                $ges_data = [
                    ['name' => 'CO₂', 'y' => $ges_co2],
                    ['name' => 'CH₄', 'y' => $ges_ch4],
                    ['name' => 'N₂O', 'y' => $ges_n2o]
                ];

                // ##############################################################
                $detail_data = [];
                $js_detail_data = [];
                foreach ($current_inventory_data['data'] as $entreprise) {
                    $secteur = $entreprise['secteur'] ?? 'Non spécifié';
                    $co2 = floatval($entreprise['co2_t_eq_co2'] ?? $entreprise['co2'] ?? 0);
                    $ch4 = floatval($entreprise['ch4_t_eq_co2'] ?? $entreprise['ch4'] ?? 0);
                    $n2o = floatval($entreprise['n2o_t_eq_co2'] ?? $entreprise['n2o'] ?? 0);

                    if (!isset($detail_data[$secteur])) {
                        $detail_data[$secteur] = ['CO₂' => 0, 'CH₄' => 0, 'N₂O' => 0];
                    }

                    $detail_data[$secteur]['CO₂'] += $co2;
                    $detail_data[$secteur]['CH₄'] += $ch4;
                    $detail_data[$secteur]['N₂O'] += $n2o;
                }

                foreach ($detail_data as $secteur => $gaz) {
                    $js_detail_data[$secteur] = [
                        ['name' => 'CO₂', 'y' => $gaz['CO₂']],
                        ['name' => 'CH₄', 'y' => $gaz['CH₄']],
                        ['name' => 'N₂O', 'y' => $gaz['N₂O']],
                    ];
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
                            Registre des émissions (<a href="./documents/Registre_Carbone.xlsx" download class="fs-8 text-decoration-none"> <span class="fa fa-file-excel"></span> Canevas </a>)
                        </h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1" name="result" id="resultID" onchange="window.location.href = 'inventory.php?annee=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner une année---</option>
                                <?php foreach ($annee_inventories as $annee) { ?>
                                    <option value="<?php echo $annee; ?>" <?php if ($sel_annee == $annee) echo 'selected'; ?>>Registre <?php echo $annee; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 d-flex gap-2 justify-content-lg-end">
                        <?php if (!empty($current_inventory)) { ?>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#addDataInventoryModal" data-annee="<?php echo $sel_annee; ?>" class="btn btn-subtle-primary btn-sm">
                                <span class="fa fa-database fs-9 me-2"></span>Données
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
                            <!-- Tableau -->
                            <h5 class="m-2 text-semibold"><i class="fas fa-table me-2"></i>Liste des Inventaires</h5>
                            <?php if (!empty($current_inventory_data)) { ?>
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <?php foreach ($current_inventory_data['columns'] as $column) { ?>
                                                    <th class="sort align-middle text-uppercase" scope="col" style="width: 10%"><?php echo $column; ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($current_inventory_data['data'] as $row) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <?php foreach ($row as $value) { ?>
                                                        <td class="align-middle px-2"> <?php echo $value; ?> </td>
                                                    <?php } ?>
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
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#addDataInventoryModal" data-annee="<?php echo $sel_annee; ?>" class="btn btn-subtle-primary btn-sm">
                                            <span class="fa fa-database fs-9 me-2"></span>Ajouter des données
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
        id: 'chartRegistreSecteur',
        title: 'Répartition par secteur',
        unite: 'kt eq. CO₂',
        categories: <?= json_encode($column_categories ?? []) ?>,
        data: <?= json_encode($column_data ?? []) ?>,

        desaggregate: true,
        name: 'Secteur',
        name2: 'Gaz',
        title2: 'Répartition par gaz',
        detailData: <?= json_encode($js_detail_data ?? []) ?>
    });

    mrvPieChart({
        id: 'chartRegistreGaz',
        title: 'Répartition par type de GES',
        unite: 'kt eq. CO₂',
        data: <?= json_encode($ges_data ?? []) ?>,
    });
</script>

</html>