<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-action_type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Mesures & Actions | MRV - Burundi</title>
    <?php include './components/navbar & footer/head.php'; ?>

    <?php
    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $gaz = new Gaz($db);
    $gazs = $gaz->read();
    $gaz_colors = [];
    foreach ($gazs as $g) {
        $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);
    }

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs_mesure = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });

    $referentiel = new Referentiel($db);
    $referentiels = $referentiel->read();
    $referentiels_mesure = array_filter($referentiels, function ($referentiel) {
        return $referentiel['state'] == 'actif';
    });

    $mesure = new Mesure($db);
    $mesures = $mesure->read();
    if (isset($_GET['secteur']) && !empty($_GET['secteur'])) {
        $secteur = (int) $_GET['secteur'];
        $mesures = array_filter($mesures, function ($mesure) use ($secteur) {
            return $mesure['secteur_id'] == $secteur;
        });
    }

    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $action = $_GET['action'];
        $mesures = array_filter($mesures, function ($mesure) use ($action) {
            return $mesure['action_type'] == $action;
        });
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = $_GET['status'];
        $mesures = array_filter($mesures, function ($mesure) use ($status) {
            return $mesure['status'] == $status;
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
            <div class="mx-0 mt-n5">
                <!-- Stats Cards -->
                <div class="row g-3 mx-n5 pb-3">
                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Total Mesures GES</span>
                                    <i class="fa fa-clipboard-list text-success fs-5"></i>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="stat-value"><?= count($mesures) ?></span>
                                    <span class="text-primary fw-medium">mesures</span>
                                </div>
                                <div class="mt-2">
                                    <span class="text-success small fw-bold">+5%</span>
                                    <span class="text-muted small"> vs année dernière</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Émissions évitées totales</span>
                                    <i class="fa fa-leaf text-warning fs-5"></i>
                                </div>
                                <?php $total_evitees = 0;
                                foreach ($mesures as $mesure) {
                                    $total_evitees += floatval(str_replace(',', '.', $mesure['valeur_estime'] ?? ""));
                                } ?>
                                <div class="stat-value"><?= number_format($total_evitees, 3, ',', ' ') ?></div>
                                <div class="mt-2 text-muted small">Gg Eq.CO2</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Secteur concerné</span>
                                    <i class="fa fa-industry text-primary fs-5"></i>
                                </div>
                                <?php $secteurs_mas = [];
                                foreach ($secteurs_mesure as $secteur) {
                                    $secteurs_mas[$secteur['name']] = true;
                                } ?>
                                <div class="stat-value"><?= count($secteurs_mas) ?></div>
                                <div class="mt-2 text-muted small"><?= implode(', ', array_keys($secteurs_mas)) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card card-float h-100 rounded-1">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="text-muted text-uppercase small fw-bold">Gaz concernés</span>
                                    <i class="fa fa-smog text-info fs-5"></i>
                                </div>
                                <?php
                                $gaz_unique = [];
                                foreach ($mesures as $mesure) {
                                    $gaz_list = explode(',', $mesure['gaz']);
                                    foreach ($gaz_list as $gaz) {
                                        $gaz_clean = trim($gaz);
                                        if ($gaz_clean) {
                                            $gaz_unique[$gaz_clean] = true;
                                        }
                                    }
                                }
                                ?>
                                <div class="stat-value"><?= count($gaz_unique) ?></div>
                                <div class="mt-2 text-muted small"><?= implode(', ', array_keys($gaz_unique)) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row mx-n1 py-1 align-items-center border-bottom">
                            <div class="col-md-4">
                                <h3 class="h5 mb-0 fw-bold">Mesures et Actions de contrôle des émissions GES</h3>
                                <p class="text-muted small mb-0">Tableau récapitulatif des mesures d'atténuation et d'adaptation</p>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-md-end gap-1">
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php
                                        $currMesSecteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
                                        $currMesAction = isset($_GET['action']) ? $_GET['action'] : '';
                                        $currMesStatus = isset($_GET['status']) ? $_GET['status'] : '';
                                        ?>
                                        <span class="form-label">Filtrer : </span> 
                                        <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                            <form class="position-relative">
                                                <select class="form-select form-select-sm bg-secondary-subtle px-2 rounded-1" id="secteurFilter">
                                                    <option value="">Tous secteurs</option>
                                                    <?php if (isset($secteurs_mesure) && !empty($secteurs_mesure)): ?>
                                                        <?php foreach ($secteurs_mesure as $secteur): ?>
                                                            <option value="<?= $secteur['id'] ?>" <?= ($currMesSecteur == $secteur['id']) ? 'selected' : '' ?>>
                                                                <?= $secteur['name'] ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                            <form class="position-relative">
                                                <select class="form-select form-select-sm bg-secondary-subtle px-2 rounded-1" id="actionFilter">
                                                    <option value="">Toutes actions</option>
                                                    <?php foreach (listTypeAction() as $key => $value): ?>
                                                        <option value="<?= $key ?>" <?= ($currMesAction == $key) ? 'selected' : '' ?>>
                                                            <?= $value ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                            <form class="position-relative">
                                                <select class="form-select form-select-sm bg-secondary-subtle px-2 rounded-1" id="statusFilter">
                                                    <option value="">Tous status</option>
                                                    <?php foreach (listStatus() as $key => $value): ?>
                                                        <option value="<?= $key ?>" <?= ($currMesStatus == $key) ? 'selected' : '' ?>>
                                                            <?= $value ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </div>
                                    </div>

                                    <button title="Ajouter une action" class="btn btn-subtle-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addMesureModal">
                                        <i class="fas fa-plus"></i> Ajouter Action
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mx-n1 p-1 scrollbar" style="min-height: 432px;">
                            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                                <thead class="bg-primary-subtle">
                                    <tr>
                                        <th style="width: 35%;">Intitulé</th>
                                        <th>Secteur</th>
                                        <th>Periode</th>
                                        <th>Statut</th>
                                        <th class="text-center">Estimation d'émissions évitées (Gg Eq.CO2)</th>
                                        <th class="text-center">Réalisations</th>
                                        <th class="text-center">Prévisions <?= date('Y') ?></th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mesures as $mesure): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?= $mesure['name'] ?></div>
                                                <div class="text-muted small mt-1">Gaz concerné :
                                                    <?php foreach (explode(',', $mesure['gaz']) as $gaz) : ?>
                                                        <span class="badge rounded-0" style="background-color: <?php echo $gaz_colors[strtoupper($gaz)] ?? '#6c757d'; ?>; color: white;">
                                                            <?php echo $gaz; ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-info rounded-pill">
                                                    <?php foreach ($secteurs_mesure as $secteur) {
                                                        if ($secteur['id'] == $mesure['secteur_id']) {
                                                            echo $secteur['name'];
                                                            break;
                                                        }
                                                    } ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-warning rounded-pill">
                                                    <?= $mesure['annee_debut'] ?> - <?= $mesure['annee_fin'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-primary rounded-pill">
                                                    <?= $mesure['status'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold text-success">
                                                <?= $mesure['valeur_estime'] ?? "-" ?>
                                            </td>
                                            <td class="text-center fw-bold text-primary">
                                                <?= $mesure['valeur_realise'] ?? "-" ?>
                                            </td>
                                            <td class="text-center fw-bold text-warning">
                                                <?= $mesure['valeur_cible'] ?? "-" ?>
                                            </td>
                                            <td>
                                                <div class="position-relative d-flex gap-1">
                                                    <?php if (checkPermis($db, 'update')) : ?>
                                                        <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                                            data-bs-target="#addMesureModal" data-id="<?= $mesure['id'] ?>">
                                                            <span class="uil-pen fs-8"></span>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if (checkPermis($db, 'delete', 2)) : ?>
                                                        <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" action_type="button"
                                                            onclick="deleteData(<?= $mesure['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette mesure ?', 'mesures')">
                                                            <span class="uil-trash-alt fs-8"></span>
                                                        </button>
                                                    <?php endif; ?>

                                                    <button title="Détails" onclick="window.location.href='mesure_view.php?id=<?= $mesure['id'] ?>'" class="btn btn-sm btn-phoenix-primary fs-10 px-2 py-1" action_type="button">
                                                        <span class="uil-eye fs-8"></span>
                                                    </button>
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

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>
<script>
    document.getElementById('secteurFilter').addEventListener('change', updateFilters);
    document.getElementById('actionFilter').addEventListener('change', updateFilters);
    document.getElementById('statusFilter').addEventListener('change', updateFilters);
</script>

</html>