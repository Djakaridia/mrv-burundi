<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Autres paramètres | MRV - Burundi</title>
    <?php
    include './components/navbar & footer/head.php';


    // ####################################
    $unite = new Unite($db);
    $unites = $unite->read();

    // ####################################
    $priorite = new Priorite($db);
    $priorites = $priorite->read();

    // ####################################
    $action = new Action($db);
    $actions = $action->read();

    // ####################################
    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent_id'] == 0;
    });
    $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent_id'] > 0;
    });

    // ####################################
    $section_dash = new SectionDash($db);
    $section_dash = $section_dash->read();
    usort($section_dash, function ($a, $b) {
        return $a['position'] - $b['position'];
    });
    $iconeSelected = array_map(function ($item) {
        return $item['icone'];
    }, $section_dash);

    // ####################################
    $indicateur = new Referentiel($db);
    $indicateurs = $indicateur->read();

    $project = new Projet($db);
    $projects = $project->read();
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
                        <h4 class="my-1 fw-black">Autres paramètres</h4>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 p-3 mx-lg-n6 border-y">
                        <div class="row gy-3">
                            <!-- Unités des indicateurs -->
                            <div class="col-12 col-lg-6">
                                <div class="card rounded-bottom-sm rounded-top-0 border-0 border-top border-4 border-primary h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-body-emphasis px-3 py-2">
                                        <h5 class="card-title m-0 p-0">Unités des indicateurs</h5>

                                        <div class="ms-lg-2">
                                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUniteModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                                <i class="fas fa-plus"></i> Ajouter une unité</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-1">
                                        <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                                <thead class="bg-secondary-subtle">
                                                    <tr>
                                                        <th class="sort align-middle" scope="col">Sigle</th>
                                                        <th class="sort align-middle" scope="col">Description</th>
                                                        <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list" id="table-latest-review-body">

                                                    <?php foreach ($unites as $unite) { ?>
                                                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                            <td class="align-middle customer white-space-nowrap"><?= $unite['name'] ?></td>
                                                            <td class="align-middle product white-space-nowrap"><?= $unite['description'] ?></td>
                                                            <td class="align-middle review">
                                                                <div class="position-relative">
                                                                    <?php if (checkPermis($db, 'update')) : ?>
                                                                        <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addUniteModal" data-id="<?= $unite['id'] ?>"
                                                                            class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                                            <span class="uil-pen fs-8"></span>
                                                                        </button>
                                                                    <?php endif; ?>

                                                                    <?php if (checkPermis($db, 'delete')) : ?>
                                                                        <button title="Supprimer" onclick="deleteData(<?php echo $unite['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette unité ?', 'unites')" type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                                                            <span class="uil-trash-alt fs-8"></span>
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Priorités -->
                            <div class="col-12 col-lg-6">
                                <div class="card rounded-bottom-sm rounded-top-0 border-0 border-top border-4 border-primary h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-body-emphasis px-3 py-2">
                                        <h5 class="card-title m-0 p-0">Liste des priorités</h5>

                                        <div class="ms-lg-2">
                                            <button class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPrioriteModal"
                                                aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                                <i class="fas fa-plus"></i> Ajouter une priorité</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-1">
                                        <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                                <thead class="bg-secondary-subtle">
                                                    <tr>
                                                        <th class="sort align-middle" scope="col" data-sort="product"> Libellé</th>
                                                        <th class="sort align-middle" scope="col" data-sort="customer"> Couleur</th>
                                                        <th class="sort align-middle" scope="col" data-sort="description"> Description</th>
                                                        <th class="sort align-middle" scope="col" data-sort="time" style="min-width:100px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list" id="table-latest-review-body">

                                                    <?php foreach ($priorites as $priorite) { ?>
                                                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                            <td class="align-middle customer white-space-nowrap"><?= $priorite['name'] ?></td>
                                                            <td class="align-middle product white-space-nowrap"> <input type="color" disabled value="<?= $priorite['couleur'] ?>"></td>
                                                            <td class="align-middle product white-space-nowrap"><?= $priorite['description'] ?></td>
                                                            <td class="align-middle review">
                                                                <div class="position-relative">
                                                                    <div class="">
                                                                        <?php if (checkPermis($db, 'update')) : ?>
                                                                            <button type="button" data-bs-toggle="modal" data-bs-target="#addPrioriteModal"
                                                                                data-id="<?= $priorite['id'] ?>" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                                                <span class="uil-pen fs-8"></span>
                                                                            </button>
                                                                        <?php endif; ?>

                                                                        <?php if (checkPermis($db, 'delete')) : ?>
                                                                            <button
                                                                                onclick="deleteData(<?php echo $priorite['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette priorité ?', 'priorites')"
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
                                    </div>
                                </div>
                            </div>


                            <!-- Actions -->
                            <div class="col-12">
                                <div class="card rounded-bottom-sm rounded-top-0 border-0 border-top border-4 border-primary h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-body-emphasis px-3 py-2">
                                        <h5 class="card-title m-0 p-0">Types d'actions</h5>

                                        <div class="ms-lg-2">
                                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addActionModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                                <i class="fas fa-plus"></i> Ajouter un type d'action</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-1">
                                        <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                                                <thead class="bg-secondary-subtle">
                                                    <tr>
                                                        <th class="sort align-middle" scope="col" data-sort="product"> Code</th>
                                                        <th class="sort align-middle" scope="col" data-sort="product"> Nom</th>
                                                        <th class="sort align-middle" scope="col" data-sort="customer"> Objectif</th>
                                                        <th class="sort align-middle" scope="col" data-sort="rating"> Secteur</th>
                                                        <th class="sort align-middle" scope="col" data-sort="time" style="min-width:100px;"> Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="list" id="table-latest-review-body">
                                                    <?php foreach ($actions as $action) { ?>
                                                        <tr
                                                            class="hover-actions-trigger btn-reveal-trigger position-static">
                                                            <td class="align-middle customer white-space-nowrap"><?= $action['code'] ?></td>
                                                            <td class="align-middle product white-space-nowrap"><?= $action['name'] ?></td>
                                                            <td class="align-middle product white-space-nowrap"><?= $action['objectif'] ?></td>
                                                            <td class="align-middle product white-space-nowrap py-0">
                                                                <?php foreach ($secteurs as $secteur) : ?>
                                                                    <?php if ($action['secteur_id'] == $secteur['id']) : ?>
                                                                        <?php echo $secteur['name']; ?>
                                                                    <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            </td>

                                                            <td class="align-middle review">
                                                                <div class="position-relative">
                                                                    <div class="">
                                                                        <?php if (checkPermis($db, 'update')) : ?>
                                                                            <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addActionModal" data-id="<?= $action['id'] ?>"
                                                                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                                                <span class="uil-pen fs-8"></span>
                                                                            </button>
                                                                        <?php endif; ?>

                                                                        <?php if (checkPermis($db, 'delete')) : ?>
                                                                            <button title="Supprimer" onclick="deleteData(<?php echo $action['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette action ?', 'actions')" type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
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
                                    </div>
                                </div>
                            </div>


                            <!-- Configuration Tableau de bord -->
                            <div class="col-12">
                                <div class="card rounded-bottom-sm rounded-top-0 border-0 border-top border-4 border-primary h-100 shadow-sm">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-body-emphasis px-3 py-2">
                                        <h5 class="card-title m-0 py-2">Configuration du Tableau de bord <span class="text-muted fs-9"> [<?= count($section_dash) ?>/4]</span></h5>

                                        <?php if (count($section_dash) < 4) : ?>
                                            <div class="ms-lg-2">
                                                <button title="Ajouter" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCardDashModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                                    <i class="fas fa-plus"></i> Ajouter une card</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-body px-3">
                                        <?php foreach ($section_dash as $section) { ?>
                                            <div class="d-flex lh-1 align-items-center p-2 hover-actions-trigger btn-reveal-trigger shadow rounded-1 border mt-1">
                                                <div class="col-3 border-end me-2 d-flex align-items-center gap-2">
                                                    <i class="<?= !empty($section['icone']) ? $section['icone'] . ' text-' . $section['couleur'] : 'fas fa-question-circle' ?> fs-9 text-red"></i>
                                                    <span class="fs-9"><?= $section['intitule'] ?></span>
                                                </div>

                                                <div class="col-4 border-end me-2">
                                                    <?php if ($section['entity_type'] == 'indicateur') : ?>
                                                        <span class="fs-9 text-muted">Indicateur : </span>
                                                        <a class="fw-semibold fs-9" href="referentiels.php?id=<?= $section['entity_id'] ?>">
                                                            <?php foreach ($indicateurs as $indicateur) { ?>
                                                                <?php if ($indicateur['id'] == $section['entity_id']) { ?>
                                                                    <?= $indicateur['intitule'] ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </a>
                                                    <?php elseif ($section['entity_type'] == 'projet') : ?>
                                                        <span class="fs-9 text-muted">Projet : </span>
                                                        <a class="fw-semibold fs-9" href="project_view.php?id=<?= $section['entity_id'] ?>">
                                                            <?php foreach ($projects as $project) { ?>
                                                                <?php if ($project['id'] == $section['entity_id']) { ?>
                                                                    <?= $project['name'] ?>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col border-end d-flex align-items-center gap-2 me-2">
                                                    <span class="fs-9 text-muted">Couleur : </span>
                                                    <div key="<?= $key ?>" class="card fw-semibold bg-<?= $section['couleur'] ?> text-center border-3 border-light" style="width: 45px; height: 25px; border-radius: 2px;"></div>
                                                </div>

                                                <div class="col border-end d-flex align-items-center gap-2 me-2">
                                                    <span class="fs-9 text-muted">Position : </span>
                                                    <span class="fs-9 fw-semibold"><?= $section['position'] ?></span>
                                                </div>

                                                <div class="col me-2">
                                                    <div class="position-relative">
                                                        <?php if (checkPermis($db, 'update')) : ?>
                                                            <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addCardDashModal" data-id="<?= $section['id'] ?>"
                                                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                                <span class="uil-pen fs-8"></span>
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if (checkPermis($db, 'delete')) : ?>
                                                            <button title="Supprimer" onclick="deleteData(<?php echo $section['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette section ?', 'sections_dash')" type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                                                <span class="uil-trash-alt fs-8"></span>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>

                                    </div>
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

</html>