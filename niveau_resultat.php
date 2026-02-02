<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Niveau de résultats | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
    if (!in_array($tab, ['overview', 'resultat', 'niveau'])) {
        $tab = 'overview';
    }

    $progr_curr = isset($_GET['progr']) && is_numeric($_GET['progr']) ? $_GET['progr'] : 0;
    $programme = new Programme($db);
    $programmes = $programme->read();

    $unite = new Unite($db);
    $unites = $unite->read();

    if (!empty($programmes) && $progr_curr == 0) {
        $progr_curr = $programmes[0]['id'];
    }

    $programme_curr = array_filter($programmes, fn($programme) => $programme['id'] == $progr_curr);
    $programme_curr = !empty($programme_curr) ? reset($programme_curr) : null;

    // #####################################################
    $niveau = new Niveau($db);
    $niveau->programme = $progr_curr;
    $niveaux = $niveau->readByProgramme();
    $niveaux = array_reverse($niveaux);
    $grouped_niveaux = [];
    foreach ($niveaux as $niveau) {
        $grouped_niveaux[$niveau['id']][] = $niveau;
    }

    $niveau_resultat = new NiveauResultat($db);
    $niveau_resultat->programme = $progr_curr;
    $niveau_resultats = $niveau_resultat->readByProgramme();
    $niveau_resultats = array_reverse($niveau_resultats);
    $grouped_resultat_niveau = [];
    foreach ($niveau_resultats as $niveau_resultat) {
        $parentId = $niveau_resultat['parent'] ?? 0;
        $grouped_resultat_niveau[$parentId][] = $niveau_resultat;
    }

    $niveau_indicateur = new NiveauIndicateur($db);
    $niveau_indicateurs = $niveau_indicateur->read();
    $grouped_resultat_indicateurs = [];
    foreach ($niveau_indicateurs as $niveau_indicateur) {
        $grouped_resultat_indicateurs[$niveau_indicateur['resultat']][] = $niveau_indicateur;
    }

    // #####################################################
    $nextLevel = isset($niveaux[count($niveaux) - 1]) ? $niveaux[count($niveaux) - 1]['level'] + 1 : 0;
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
            <ul class="nav nav-underline fs-9 mt-n4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $tab == 'resultat' ? 'active' : ''; ?>" id="resultat-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=resultat&progr=' . $progr_curr; ?>">Résultat de niveau</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $tab == 'niveau' ? 'active' : ''; ?>" id="niveau-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=niveau&progr=' . $progr_curr; ?>">Gestion des niveaux</a>
                </li>
            </ul>


            <div class="tab-content mt-5" id="myTabContent">
                <div class="tab-pane fade <?php echo $tab == 'overview' ? 'active show' : ''; ?>" id="tab-profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                            <div class="col-lg-4 mb-2 mb-lg-0 text-start">
                                <h4 class="my-1 fw-black fs-8">Résultats des niveaux</h4>
                            </div>

                            <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                                <form action="formNiveauResultat" method="post">
                                    <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="programme"
                                        onchange="window.location.href = '<?php echo $_SERVER['PHP_SELF'] . '?tab=overview&progr='; ?>' + this.value">
                                        <option class="text-center" value="" selected disabled>---Sélectionner un programme---</option>
                                        <?php foreach ($programmes as $programme) { ?>
                                            <option value="<?php echo $programme['id']; ?>" <?php if ($progr_curr == $programme['id']) echo 'selected'; ?>><?php echo $programme['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </form>
                            </div>

                            <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent">
                                        <thead class="bg-primary-subtle">
                                            <tr>
                                                <th class="sort align-middle">Résultat de niveau</th>
                                                <th class="sort pe-0 align-middle" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php function renderTreeResultat($parentId, $tree, $level = 0)
                                            {
                                                if (!isset($tree[$parentId])) return;
                                                foreach ($tree[$parentId] as $res) :
                                            ?>
                                                    <tr class="<?php echo $level == 0 ? 'bg-light' : '' ?>">
                                                        <td class="align-middle customer">
                                                            <strong><?= htmlspecialchars($res['code']); ?> :</strong>
                                                            <?= htmlspecialchars($res['name']); ?>
                                                        </td>

                                                        <td class="align-middle pe-0 text-end">
                                                            <div class="position-relative">
                                                                <?php if (checkPermis($GLOBALS['db'], 'update')) : ?>
                                                                    <button class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#addObjNiveauModal"
                                                                        data-id="<?= $res['id']; ?>">
                                                                        <span class="uil-pen fs-8"></span>
                                                                    </button>
                                                                <?php endif; ?>

                                                                <?php if (checkPermis($GLOBALS['db'], 'delete')) : ?>
                                                                    <button class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1"
                                                                        onclick="deleteData(<?= $res['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet résultat ?', 'niveaux_resultats')">
                                                                        <span class="uil-trash-alt fs-8"></span>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <?php renderTreeResultat($res['id'], $tree, $level + 1); ?>
                                            <?php
                                                endforeach;
                                            }

                                            if (count($grouped_resultat_niveau) > 0) {
                                                renderTreeResultat(0, $grouped_resultat_niveau, 0);
                                            } else {
                                                echo '<tr><td colspan="2" class="text-center"><div class="py-5 my-5 text-center fs-8">Aucun résultat trouvé</div></td></tr>';
                                            }
                                            ?>
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade <?php echo $tab == 'resultat' ? 'active show' : ''; ?>" id="tab-home" role="tabpanel" aria-labelledby="home-tab">
                    <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                            <div class="col-lg-4 mb-2 mb-lg-0 text-start">
                                <h4 class="my-1 fw-black fs-8">Résultats de niveaux</h4>
                            </div>

                            <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                                <form action="formNiveauResultat" method="post">
                                    <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="programme"
                                        onchange="window.location.href = '<?php echo $_SERVER['PHP_SELF'] . '?tab=resultat&progr='; ?>' + this.value">
                                        <option class="text-center" value="" selected disabled>---Sélectionner un programme---</option>
                                        <?php foreach ($programmes as $programme) { ?>
                                            <option value="<?php echo $programme['id']; ?>" <?php if ($progr_curr == $programme['id']) echo 'selected'; ?>><?php echo $programme['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </form>
                            </div>

                            <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                                <button title="Retour" class="btn btn-subtle-primary btn-sm" onclick="window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?progr=<?php echo $progr_curr; ?>';">
                                    <i class="fas fa-arrow-left"></i> Retour</button>
                                <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                                    data-bs-target="#addObjNiveauModal" data-programme="<?php echo $progr_curr; ?>" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                    <i class="fas fa-plus"></i> Ajouter un résultat</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                        <thead class="bg-primary-subtle">
                                            <tr>
                                                <th class="sort align-middle" scope="col">Code</th>
                                                <th class="sort align-middle" scope="col" style="min-width:300px;">Résultat de niveau</th>
                                                <th class="sort align-middle" scope="col" style="min-width:300px;">Niveau de résultat</th>
                                                <th class="sort align-middle" scope="col" style="min-width:300px;">Indicateurs</th>
                                                <th class="sort align-middle" scope="col" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($niveau_resultats as $niveau_resultat) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle product"><?php echo $niveau_resultat['code']; ?></td>
                                                    <td class="align-middle customer"><?php echo $niveau_resultat['name']; ?></td>
                                                    <td class="align-middle customer">
                                                        <?php foreach ($niveaux as $niveau) { ?>
                                                            <?php if ($niveau['id'] == $niveau_resultat['niveau']) echo $niveau['name']; ?>
                                                        <?php } ?>
                                                    </td>
                                                    <td class="align-middle customer">
                                                        <button title="Voir indicateur" type="button" class="btn btn-sm btn-link text-primary p-0 m-0" data-bs-toggle="modal"
                                                            data-bs-target="#newNiveauIndicModal" data-id="<?php echo $niveau_resultat['id']; ?>">
                                                            Indicateurs <span class="badge bg-success px-1">
                                                                <?= isset($grouped_resultat_indicateurs[$niveau_resultat['id']]) ? count($grouped_resultat_indicateurs[$niveau_resultat['id']]) : 0 ?>
                                                            </span>
                                                        </button>
                                                    </td>
                                                    <td class="align-middle pe-0">
                                                        <div class="position-relative">
                                                            <?php if (checkPermis($db, 'update')) : ?>
                                                                <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                                                                    data-bs-target="#addObjNiveauModal" data-id="<?php echo $niveau_resultat['id']; ?>">
                                                                    <span class="uil-pen fs-8"></span>
                                                                </button>
                                                            <?php endif; ?>

                                                            <?php if (checkPermis($db, 'delete')) : ?>
                                                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                                                    onclick="deleteData(<?php echo $niveau_resultat['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet resultat ?', 'niveaux_resultats')">
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
                </div>

                <div class="tab-pane fade <?php echo $tab == 'niveau' ? 'active show' : ''; ?>" id="tab-profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                            <div class="col-lg-4 mb-2 mb-lg-0 text-start">
                                <h4 class="my-1 fw-black fs-8">Gestion des niveaux</h4>
                            </div>

                            <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                                <form action="formNiveauResultat" method="post">
                                    <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="programme"
                                        onchange="window.location.href = '<?php echo $_SERVER['PHP_SELF'] . '?tab=niveau&progr='; ?>' + this.value">
                                        <option class="text-center" value="" selected disabled>---Sélectionner un programme---</option>
                                        <?php foreach ($programmes as $programme) { ?>
                                            <option value="<?php echo $programme['id']; ?>" <?php if ($progr_curr == $programme['id']) echo 'selected'; ?>><?php echo $programme['name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </form>
                            </div>

                            <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                                <button title="Retour" class="btn btn-subtle-primary btn-sm" onclick="window.location.href = '<?php echo $_SERVER['PHP_SELF']; ?>?progr=<?php echo $progr_curr; ?>';">
                                    <i class="fas fa-arrow-left"></i> Retour</button>
                                <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                                    data-bs-target="#addNiveauModal" data-next_level="<?php echo $nextLevel ?>" data-programme="<?php echo $progr_curr; ?>" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                    <i class="fas fa-plus"></i> Ajouter un niveau</button>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                        <thead class="bg-primary-subtle">
                                            <tr>
                                                <th class="sort align-middle" scope="col" data-sort="product">Niveau</th>
                                                <th class="sort align-middle" scope="col" data-sort="customer" style="min-width:300px;">Intitulé</th>
                                                <th class="sort align-middle" scope="col" data-sort="customer" style="min-width:300px;">Type</th>
                                                <th class="sort pe-0 align-middle" scope="col" style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list" id="table-latest-review-body">
                                            <?php foreach ($niveaux as $niveau) { ?>
                                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                    <td class="align-middle product"><?php echo $niveau['level']; ?></td>
                                                    <td class="align-middle customer"><?php echo $niveau['name']; ?></td>
                                                    <td class="align-middle customer text-capitalize"><?php echo $niveau['type']; ?></td>
                                                    <td class="align-middle pe-0">
                                                        <div class="position-relative">
                                                            <?php if (checkPermis($db, 'update')) : ?>
                                                                <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                                                                    data-bs-target="#addNiveauModal" data-id="<?php echo $niveau['id']; ?>">
                                                                    <span class="uil-pen fs-8"></span>
                                                                </button>
                                                            <?php endif; ?>

                                                            <?php if (checkPermis($db, 'delete')) : ?>
                                                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                                                    onclick="deleteData(<?php echo $niveau['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce niveau ?', 'niveaux')">
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