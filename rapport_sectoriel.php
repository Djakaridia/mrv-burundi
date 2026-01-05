<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Données des secteurs | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';


    $sel_id = isset($_GET['sector']) ? $_GET['sector'] : '';
    $secteur = new Secteur($db);
    $secteurs = $secteur->read();
    $secteurs = array_filter($secteurs, function ($secteur) {
        return $secteur['state'] == 'actif' && $secteur['parent_id'] == 0;
    });
    sort($secteurs);

    if ($sel_id !== '') {
        $secteur->id = $sel_id;
        $secteur_curr = $secteur->readById();
    } else {
        $secteur_curr = $secteurs[0];
    };

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'action';
    if (!in_array($tab, ['action', 'inventory', 'structure', 'finance'])) $tab = 'action';

    // Liste des actions
    $projet = new Projet($db);
    $projets = $projet->read();
    $projets = array_filter($projets, function ($projet) use ($secteur_curr) {
        return $projet['state'] == 'actif' && in_array($secteur_curr['id'], explode(',', str_replace('"', '', $projet['secteurs'])));
    });

    // Inventaires
    $inventory = new Inventory($db);
    $inventory->annee = date('Y');
    $inventory_curr = $inventory->readByAnnee();
    $inventory_data = json_decode($inventory->readData($inventory_curr['viewtable']), true);
    $columns_vw = $inventory_data['columns'];
    $data_vw = array_filter($inventory_data['data'], function ($data) use ($secteur_curr) {
        return strtoupper(removeAccents($data['secteur'])) === strtoupper(removeAccents($secteur_curr['name']));
    });

    // Structures
    $type_structure = new StructureType($db);
    $type_structures = $type_structure->read();

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) use ($secteur_curr) {
        return $structure['state'] == 'actif' && $structure['secteur_id'] == $secteur_curr['id'];
    });

    // Conventions
    $structures_ids = array_map(function ($structure) {
        return $structure['id'];
    }, $structures);

    $convention = new Convention($db);
    $conventions = $convention->read();
    $conventions = array_filter($conventions, function ($convention) use ($structures_ids) {
        return in_array($convention['structure_id'], $structures_ids);
    });
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
                        <h4 class="my-1 fw-black">Synthèse des données - <?php echo $secteur_curr['name']; ?></h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="rapport_sectoriel.php" method="get">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-center" name="sector" id="rapport_sectorID" onchange="window.location.href = 'rapport_sectoriel.php?sector=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner un secteur---</option>
                                <?php foreach ($secteurs as $secteur) { ?>
                                    <option value="<?php echo $secteur['id']; ?>" <?php if ($secteur_curr['id'] == $secteur['id']) echo 'selected'; ?>><?php echo $secteur['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                        <a title="Ajouter" class="btn btn-subtle-primary btn-sm" href="synchronisation.php">
                            <i class="fas fa-sync"></i> Synchroniser</a>
                    </div>
                </div>
            </div>

            <div class="mx-n4 px-3 mx-lg-n6 bg-body-emphasis border border-top-0">
                <div class="card-body d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <ul class="nav nav-underline fs-9" id="myTab" role="tablist">
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'action' ? 'active' : ''; ?>"
                                id="action-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?sector=' . $sel_id . '&tab=action'; ?>">Actions</a></li>
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'inventory' ? 'active' : ''; ?>"
                                id="inventory-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?sector=' . $sel_id . '&tab=inventory'; ?>">Inventaires GES</a></li>
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'structure' ? 'active' : ''; ?>"
                                id="structure-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?sector=' . $sel_id . '&tab=structure'; ?>">Structures</a></li>
                        <li class="nav-item" role="tab"><a class="nav-link <?php echo $tab == 'finance' ? 'active' : ''; ?>"
                                id="finance-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?sector=' . $sel_id . '&tab=finance'; ?>">Financements</a></li>
                    </ul>
                </div>
            </div>

            <div class="tab-content mt-3" id="myTabContent">
                <div class="tab-pane fade <?php echo $tab == 'action' ? 'active show' : ''; ?>" id="tab-action" role="tabpanel" aria-labelledby="action-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="align-middle px-2" scope="col"> Code</th>
                                                <th class="align-middle px-2" scope="col" width="30%"> Intitulé</th>
                                                <th class="align-middle px-2" scope="col"> Actions</th>
                                                <th class="align-middle px-2" scope="col"> Budget (FCFA)</th>
                                                <th class="align-middle px-2" scope="col"> Structure</th>
                                                <th class="align-middle px-2" scope="col" width="10%"> Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($projets as $projet) : ?>
                                                <tr>
                                                    <td class="align-middle"><?= $projet['code'] ?></td>
                                                    <td class="align-middle"><?= $projet['name'] ?></td>
                                                    <td class="align-middle"><?= $projet['action_name'] ?></td>
                                                    <td class="align-middle rating white-space-nowrap" style="min-width:200px;">
                                                        <span class="badge bg-warning-subtle text-warning p-2 fs-10"><?php echo number_format($projet['budget'], 0, ',', ' '); ?></span>
                                                    </td>
                                                    <td class="align-middle"><?= $projet['structure_sigle'] ?></td>
                                                    <td class="align-middle">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button title="Voir" class="btn btn-sm px-2 py-1 btn-phoenix-info" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>">
                                                                <span class="uil-eye fs-8"></span>
                                                            </button>
                                                            <a title="Consulter" href="project_view.php?id=<?= $projet['id'] ?>" class="btn btn-sm px-2 py-1 btn-phoenix-success">
                                                                <span class="uil-arrow-right fs-8"></span>
                                                            </a>
                                                        </div>
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

                <div class="tab-pane fade <?php echo $tab == 'inventory' ? 'active show' : ''; ?>" id="tab-inventory" role="tabpanel" aria-labelledby="inventory-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 px-1 pb-3 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="mx-n1 mb-3 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <?php foreach ($columns_vw as $column) { ?>
                                                    <th class="align-middle text-capitalize px-2" scope="col" style="width: 10%"><?php echo $column; ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($data_vw as $row) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <?php foreach ($row as $value) { ?>
                                                        <td class="align-middle white-space-nowrap px-2"> <?php echo $value; ?> </td>
                                                    <?php } ?>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'structure' ? 'active show' : ''; ?>" id="tab-structure" role="tabpanel" aria-labelledby="structure-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="align-middle px-2" scope="col">Logo</th>
                                                <th class="align-middle px-2" scope="col">Code</th>
                                                <th class="align-middle px-2" scope="col">Sigle</th>
                                                <th class="align-middle px-2" scope="col">Email</th>
                                                <th class="align-middle px-2" scope="col">Contact</th>
                                                <th class="align-middle px-2" scope="col">Type</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($structures as $structure) {
                                                $logoStruc = explode("../", $structure['logo'] ?? ''); ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle product white-space-nowrap py-1">
                                                        <?php if ($structure['logo']) { ?>
                                                            <img class="d-block rounded-1 w-100 object-fit-contain" src="<?php echo end($logoStruc) ?>" alt="Logo" height="35" />
                                                        <?php } else { ?>
                                                            <div class="d-block rounded-1 border border-translucent text-center p-1 text-primary">
                                                                <i class="fas fa-users fs-8 p-1"></i>
                                                            </div>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle product white-space-nowrap"><?php echo $structure['code']; ?></td>
                                                    <td class="align-middle rating white-space-nowrap"><?php echo $structure['sigle']; ?></td>
                                                    <td class="align-middle rating white-space-nowrap"><?php echo $structure['email']; ?></td>
                                                    <td class="align-middle rating white-space-nowrap"><?php echo $structure['phone']; ?></td>
                                                    <td class="align-middle review">
                                                        <?php foreach ($type_structures as $type_structure) { ?>
                                                            <?php if ($type_structure['id'] == $structure['type_id']) { ?>
                                                                <?php echo $type_structure['name']; ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'finance' ? 'active show' : ''; ?>" id="tab-finance" role="tabpanel" aria-labelledby="finance-tab">
                    <div class="row">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable4">
                                        <thead class="bg-secondary-subtle">
                                            <tr>
                                                <th class="align-middle px-2">Logo</th>
                                                <th class="align-middle px-2">Code</th>
                                                <th class="align-middle px-2">Intitulé</th>
                                                <th class="align-middle px-2">Bailleur</th>
                                                <th class="align-middle px-2" style="min-width:110px;">Montant (FCFA)</th>
                                                <th class="align-middle px-2">Date d'acord</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($conventions as $convention) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle product white-space-nowrap py-0">
                                                        <?php foreach ($structures as $structure) {
                                                            $logoStruc = explode("../", $structure['logo'] ?? ''); ?>
                                                            <?php if ($structure['id'] == $convention['structure_id']) { ?>
                                                                <?php if ($structure['logo']) { ?>
                                                                    <img class="d-block rounded-1 w-100 object-fit-contain" src="<?php echo end($logoStruc) ?>" alt="Logo" height="35" />
                                                                <?php } else { ?>
                                                                    <div class="d-block rounded-1 border border-translucent text-center p-1 text-primary">
                                                                        <i class="fas fa-users fs-8 p-1"></i>
                                                                    </div>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle product white-space-nowrap"><?php echo $convention['code']; ?></td>
                                                    <td class="align-middle customer white-space-nowrap"><?php echo $convention['name']; ?></td>
                                                    <td class="align-middle review">
                                                        <?php foreach ($structures as $structure) { ?>
                                                            <?php if ($structure['id'] == $convention['structure_id']) { ?>
                                                                <?php echo $structure['sigle']; ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle rating white-space-nowrap" style="min-width:200px;">
                                                        <span class="badge bg-info-subtle text-info p-2 fs-10"><?php echo number_format($convention['montant'], 0, ',', ' '); ?></span>
                                                    </td>
                                                    <td class="align-middle date white-space-nowrap">
                                                        <?php echo date('Y-m-d', strtotime($convention['date_accord'])); ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
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