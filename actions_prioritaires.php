<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Actions prioritaires | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
        return $secteur['parent'] == 0;
    });
    usort($secteurs, function ($a, $b) {
        return (int)$a['code'] <=> (int)$b['code'];
    });

    $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] > 0;
    });
    usort($sous_secteurs, function ($a, $b) {
        return (int)$a['code'] <=> (int)$b['code'];
    });

    $actions_prioritaire = new ActionPrioritaire($db);
    $actions_prioritaires = $actions_prioritaire->read();
    usort($actions_prioritaires, function ($a, $b) {
        return (int)$a['code'] <=> (int)$b['code'];
    });
    $actions_prioritaires = array_reverse($actions_prioritaires, true);
    $grouped_actions_prioritaires = [];
    foreach ($actions_prioritaires as $action) {
        $grouped_actions_prioritaires[$action['secteur_id']][] = $action;
    }

    $structure = new Structure($db);
    $structures = $structure->read();
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
                    <div class="col-auto">
                        <h4 class="my-1 fw-black fs-8">Liste des actions prioritaires</h4>
                    </div>

                    <div class="ms-lg-2 d-flex gap-2">
                        <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                            data-bs-target="#addActionPrioModal" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-plus"></i> Ajouter une action</button>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="table-responsive p-1 scrollbar" style="min-height: 432px;">
                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent">
                                <thead class="bg-primary-subtle">
                                    <tr class="text-center text-nowrap">
                                        <th class="text-start align-middle" style="width:300px"> Intitulé</th>
                                        <th class="text-start align-middle"> Actions Prioritaires</th>
                                        <th class="text-start align-middle" style="width:150px"> Type action</th>
                                        <th class="text-start align-middle" style="width:150px"> Objectif inconditionnel</th>
                                        <th class="text-start align-middle" style="width:150px"> Objectif conditionnel</th>
                                        <th class="text-start align-middle" style="width:100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($secteurs as $secteur):
                                        $sous_secteur_items = array_filter($data_secteurs, function ($sous_secteur_item) use ($secteur) {
                                            return $sous_secteur_item['parent'] == $secteur['id'];
                                        });
                                        sort($sous_secteur_items)
                                    ?>
                                        <tr class="bg-light fw-semibold">
                                            <td class="align-middle px-2" style="width:300px">
                                                <strong><?= $secteur['code'] . "-" . $secteur['name'] ?></strong>
                                                <span class="badge bg-primary px-1 text-nowrap"><?= count($sous_secteur_items) ?></span>
                                            </td>
                                            <td class="align-middle px-2 text-start" colspan="5">
                                                Structure responsable :
                                                <?php foreach ($structures as $structure): ?>
                                                    <?php if ($structure['id'] == $secteur['structure_id']): ?>
                                                        <?= $structure['description'] ?  $structure['description'] . '(' . $structure['sigle'] . ')' : $structure['sigle'] ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="align-middle p-0"></td>
                                            <td class="align-middle p-0" colspan="5">
                                                <?php if (isset($grouped_actions_prioritaires[$secteur['id']])): ?>
                                                    <table class="table table-hover fs-9 m-0" style="width: 100%;">
                                                        <?php foreach ($grouped_actions_prioritaires[$secteur['id']]  as $action) { ?>
                                                            <tr>
                                                                <td class="border-end align-middle px-2"><?= $action['code'] . "-" . $action['name'] ?></td>
                                                                <td class="border-end align-middle px-2" style="width: 150px;"><?= listTypeAction()[$action['action_type']] ?? "N/A" ?></td>
                                                                <td class="border-end align-middle px-2" style="width: 150px;"><?= $action['objectif_wem'] ?? "N/A" ?></td>
                                                                <td class="border-end align-middle px-2" style="width: 150px;"><?= $action['objectif_wam'] ?? "N/A" ?></td>
                                                                <td class="border-end align-middle px-2" style="width: 100px;">
                                                                    <div class="d-flex gap-1">
                                                                        <?php if (checkPermis($db, 'update')) : ?>
                                                                            <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addActionPrioModal"
                                                                                data-parent="<?php echo $action['secteur_id'] ?>" data-id="<?php echo $action['id'] ?>"
                                                                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                                                <span class="uil-pen fs-8"></span>
                                                                            </button>
                                                                        <?php endif; ?>

                                                                        <?php if (checkPermis($db, 'delete')) : ?>
                                                                            <button title="Supprimer" onclick="deleteData(<?php echo $action['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette action ?', 'actions')"
                                                                                type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                                                                <span class="uil-trash-alt fs-8"></span>
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                <?php endif ?>
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

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>