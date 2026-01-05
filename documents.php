<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Documents | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $dossier = new Dossier($db);
    $dossiers = $dossier->read();

    $user = new User($db);
    $users = $user->read();

    // Regrouper les dossiers par type
    $dossiersGrouped = [];
    foreach ($dossiers as $dossier) {
        if (!isset($dossiersGrouped[$dossier['type']])) {
            $dossiersGrouped[$dossier['type']] = [];
        }
        $dossiersGrouped[$dossier['type']][] = $dossier;
    }

    $document = new Documents($db);
    $documents = $document->read();

    //==================================
    $data = array_map('count', $dossiersGrouped);
    if (isset($dossiersGrouped[''])) {
        $data['non_classé'] = count($dossiersGrouped['']);
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

            <div class="mx-n4 mt-n5 px-2 mx-lg-n6 bg-body-emphasis border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
                    <h4 class="my-1 fw-black d-flex align-items-center gap-2">
                        Liste des dossiers
                        <button title="Aide" class="btn btn-icon btn-subtle-primary btn-sm rounded-circle" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvaDocumentd" aria-controls="offcanvaDocumentd">
                            <i class="far fa-question-circle align-bottom fs-8"></i>
                        </button>
                    </h4>

                    <div class="d-flex flex-row justify-content-end">
                        <div class="search-box">
                            <form class="position-relative">
                                <input
                                    id="searchDossiers"
                                    class="form-control form-control-sm search-input search"
                                    type="search"
                                    placeholder="Search products"
                                    aria-label="Search" />
                                <span class="fas fa-search search-box-icon"></span>
                            </form>
                        </div>

                        <div class="ms-lg-2">
                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addDossierModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                <i class="fas fa-plus"></i> Ajouter un dossier</button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2 " style="max-height: 420px; overflow-x: hidden; overflow-y: auto;">
                    <div class="row g-3 mb-3">
                        <?php foreach ($dossiers as $dossier) : ?>
                            <div class="col-lg-2 col-md-3 col-sm-4 col-6 dossier-item">
                                <div class="card shadow-sm rounded-1" id="dossier-<?= $dossier['id'] ?>">
                                    <div class="card-body p-2">
                                        <?php if (!in_array($dossier['type'], ['acteur', 'programme', 'projet', 'indicateur', 'groups', 'reunion'])): ?>
                                            <div class="d-flex align-items-center justify-content-end">
                                                <div class="flex-shrink-0 dropdown">
                                                    <button title="Actions" class="btn btn-icon btn-phoenix-primary dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa-solid fa-ellipsis-vertical align-bottom"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-start">
                                                        <?php if (checkPermis($db, 'update')) : ?>
                                                            <li><a class="dropdown-item edit-folder-list" href="#addDossierModal" data-bs-toggle="modal" data-bs-target="#addDossierModal" data-id="<?= $dossier['id'] ?>" data-parent-id="<?= $dossier['parent_id'] ?>" role="button">Modifier</a></li>
                                                        <?php endif; ?>

                                                        <?php if (checkPermis($db, 'delete')) : ?>
                                                            <li><a onclick="deleteData(<?= $dossier['id'] ?>, 'Voulez-vous vraiment supprimer ce dossier ?', 'dossiers')" class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" role="button">Supprimer</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <a href="dossier_view.php?id=<?= $dossier['id'] ?>" style="text-decoration: none;" title="<?= $dossier['description'] ?>">
                                            <div class="text-center">
                                                <div class="mb-2"> <i class="fas fa-folder-open align-bottom text-warning opacity-75 fs-4"></i> </div>
                                                <h6 class="fs-15 dossier-name text-primary"><?= $dossier['name'] ?></h6>
                                            </div>
                                            <div class="hstack mt-3 text-muted fs-9">
                                                <small class="me-auto">
                                                    <b><?php
                                                        $document_count = new Documents($db);
                                                        $document_count->dossier_id = $dossier['id'];
                                                        $documents_count = $document_count->readByDossier();
                                                        echo count($documents_count) ?? 0;
                                                        ?>
                                                    </b> Fichiers
                                                </small>

                                                <small>
                                                    <b><?php
                                                        $document_size = new Documents($db);
                                                        $document_size->dossier_id = $dossier['id'];
                                                        $documents_size = $document_size->readByDossier();
                                                        $convert_size = array_sum(array_column($documents_size, 'file_size')) ?? 0;
                                                        echo round($convert_size / 1024 / 1024, 2);
                                                        ?>
                                                    </b> MB
                                                </small>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card-body p-2 border-top border-top-1 border-top-light mb-9">
                    <div class="d-flex align-items-center mb-2">
                        <h4 class="my-1 fw-black flex-grow-1 fs-16 mb-0" id="filetype-title">Fichiers récents</h4>
                        <div class="d-flex flex-row justify-content-end gap-2">
                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDocumentModal"><i class="fas fa-plus align-bottom me-1"></i> Ajouter un fichier</button>
                        </div>
                    </div>
                    <div class="table-responsive scrollbar" style="min-height: 432px;">
                        <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                            <thead class="bg-secondary-subtle">
                                <tr>
                                    <th class="sort align-middle" scope="col">Nom</th>
                                    <th class="sort align-middle" scope="col">Dossier</th>
                                    <th class="sort align-middle" scope="col">Date d'ajout</th>
                                    <th class="sort align-middle" scope="col">Ajouté par</th>
                                    <th class="sort align-middle" scope="col">Taille</th>
                                    <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list" id="table-latest-review-body">
                                <?php foreach ($documents as $document) { ?>
                                    <tr
                                        class="hover-actions-trigger btn-reveal-trigger position-static">
                                        <td class="align-middle white-space-nowrap px-2">
                                            <div class="d-flex align-items-center text-body">
                                                <i class="fas fa-file fs-8 me-2"></i><?= $document['name'] ?>
                                            </div>
                                        </td>
                                        <td class="align-middle white-space-nowrap px-2">
                                            <a class="d-flex align-items-center text-body" href="dossier_view.php?id=<?= $document['dossier_id'] ?>">
                                                <i class="fas fa-folder fs-8 me-2"></i>
                                                <div class="mb-0 text-body">
                                                    <?php foreach ($dossiers as $dossier) { ?>
                                                        <?php if ($dossier['id'] == $document['dossier_id']) { ?>
                                                            <?= $dossier['name'] ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="align-middle white-space-nowrap px-2">
                                            <?= date('d/m/Y', strtotime($document['created_at'])) ?>
                                        </td>
                                        <td class="align-middle white-space-nowrap px-2">
                                            <?php foreach ($users as $user) {
                                                if ($user['id'] == $document['add_by']) echo $user['nom'];
                                            } ?>
                                        </td>
                                        <td class="align-middle white-space-nowrap px-2">
                                            <?= round($document['file_size'] / 1024 / 1024, 2) ?> MB
                                        </td>
                                        <td class="align-middle review">
                                            <div class="position-relative">
                                                <div class="">
                                                    <button title="Télécharger" onclick="downloadFiles('MRV', '<?= $document['name'] ?>', '<?= $document['file_path'] ?>')"
                                                        class="btn btn-sm btn-phoenix-success me-1 fs-10 px-2 py-1">
                                                        <span class="uil-cloud-download fs-8"></span>
                                                    </button>

                                                    <?php if (checkPermis($db, 'delete')) : ?>
                                                        <button title="Supprimer" onclick="deleteData(<?php echo $document['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce document ?', 'documents')"
                                                            class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
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

                <div class="offcanvas offcanvas-end" id="offcanvaDocumentd" tabindex="-1" aria-labelledby="offcanvaDocumentdLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 id="offcanvaDocumentdLabel">Aperçu de la documentation</h5>
                        <button title="Fermer" class="btn-close text-reset" type="button" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="file-detail-content-scroll simplebar-scrollable-y" data-simplebar="init">
                            <div style="width: 100%; margin: 0; padding: 0">
                                <div id="dossiersChart" style="height: 330px;"></div>
                            </div>

                            <div class="mt-3">
                                <ul class="list-unstyled gap-3 border-top border-top-light py-3">
                                    <?php foreach ($dossiersGrouped as $type => $dossierGroup) { ?>
                                        <li>
                                            <div class="d-flex align-items-top mb-1">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-folder text-warning opacity-75 fs-6"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h6 class="mb-0 text-capitalize"><?= $type ?></h6>
                                                    <small class="text-body-emphasis"><?= count($dossierGroup) ?> Dossiers</small>
                                                </div>
                                                <div class="ms-2">
                                                    <small class="text-body-emphasis">
                                                        <?php $totalFiles = 0;
                                                        foreach ($dossierGroup as $dossier) {
                                                            foreach ($documents as $document) {
                                                                if ($document['dossier_id'] == $dossier['id']) {
                                                                    $totalFiles++;
                                                                }
                                                            }
                                                        }
                                                        echo $totalFiles; ?>
                                                        Fichiers
                                                    </small>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
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
    <script>
        const data = <?php echo json_encode($data); ?>;
        const chartData = Object.keys(data).map((label, index) => ({
            name: label, y: data[label],
            color: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8AC24A', '#FF5733', '#33FF57', '#3357FF', '#F033FF'][index % 11]
        }));

        mrvPieChart({
            id: 'dossiersChart',
            title: 'Répartition des dossiers par type',
            unite: 'Dossiers',
            data: chartData,
        });
    </script>
</body>

</html>