<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Rapports Périodiques | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $projet = new Projet($db);
    $projets = $projet->read();
    $projets = array_filter($projets, function ($projet) { return $projet['state'] == 'actif'; });

    $rapport_periodique = new RapportPeriode($db);
    $rapports_periodiques = $rapport_periodique->read();
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
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis dark__bg-dark border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
                    <div class="col-auto">
                        <h4 class="my-1 fw-black fs-8">Rapports Périodiques</h4>
                    </div>

                    <div class="d-lg-flex flex-row">
                        <div class="search-box my-lg-0 my-2">
                            <form class="position-relative">
                                <input
                                    id="searchRapportPeriodique"
                                    class="form-control form-control-sm search-input search"
                                    type="search"
                                    placeholder="Rechercher un rapport"
                                    aria-label="Search" />
                                <span class="fas fa-search search-box-icon"></span>
                            </form>
                        </div>

                        <div class="ms-lg-2">
                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addRapportPeriodeModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                <i class="fas fa-plus"></i> Ajouter un rapport</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <!-- Filtres -->
                <!-- <div class="col-12 w-100">
                    <div class="card mb-3">
                        <div class="card-body">
                            <form class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Année de référence</label>
                                    <select class="form-select">
                                        <option>Toutes</option>
                                        <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                                            <option><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Projet</label>
                                    <select class="form-select">
                                        <option>Tous</option>
                                        <?php foreach ($projets as $projet): ?>
                                            <option value="<?= $projet['id'] ?>"><?= $projet['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Période</label>
                                    <select class="form-select">
                                        <option>Toutes</option>
                                        <option>Mensuelle</option>
                                        <option>Trimestrielle</option>
                                        <option>Annuelle</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button class="btn btn-outline-primary w-100 filter-btn">
                                        <i class="fas fa-filter me-2"></i>Filtrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> -->

                <!-- Liste des rapports -->
                <div class="col-12 w-100">
                    <div class="mx-n4 p-3 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="row">
                            <?php if (empty($rapports_periodiques)) { ?>
                                <div class="text-center py-5 my-5" style="min-height: 350px;">
                                    <div class="d-flex justify-content-center mb-3">
                                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <h4 class="text-800 mb-3">Aucun rapport trouvé</h4>
                                    <p class="text-600 mb-5">Il semble que vous n'ayez pas encore de rapports. Commencez par en créer un.</p>
                                    <button title="Ajouter" class="btn btn-primary px-5 fs-8" id="addBtn" data-bs-toggle="modal"
                                        data-bs-target="#addRapportPeriodeModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                        <i class="fas fa-plus"></i> Ajouter un rapport</button>
                                </div>
                            <?php } else { ?>
                                <?php foreach ($rapports_periodiques as $rapport):
                                    $projet = new Projet($db);
                                    $projet->id = $rapport['projet_id'];
                                    $projet_rapport = $projet->readById();
                                ?>
                                    <div class="col-md-6 col-lg-4 mb-4 rapport-item">
                                        <div class="card rounded-1 shadow-sm card-float h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center p-2">
                                                <h5 class="mb-0"><?= $rapport['intitule'] ?></h5>
                                                <span class="badge fs-10 rounded-pill text-bg-<?php echo $rapport['state'] == 'actif' ? 'warning' : 'success' ?>">
                                                    <?php echo $rapport['state'] == 'actif' ? 'Actif' : 'Validé' ?>
                                                </span>
                                            </div>

                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <small class="text-muted">Code:</small>
                                                    <strong><?= $rapport['code'] ?></strong>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Projet:</small>
                                                    <strong><?= $projet_rapport['name'] ?? 'Non attribué' ?></strong>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Périodicité:</small>
                                                    <span class="badge fs-10 bg-primary"><?= listPeriodicite()[$rapport['periode']] ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Période de référence:</small>
                                                    <strong>
                                                        <?= listMois()[$rapport['mois_ref']] ?> <?= $rapport['annee_ref'] ?> -
                                                        <?= listMois()[$rapport['mois_ref'] + $rapport['periode'] > 12 ? $rapport['mois_ref'] + $rapport['periode'] - 12 : $rapport['mois_ref'] + $rapport['periode']] ?>
                                                        <?= $rapport['periode'] + $rapport['mois_ref'] >= 12 ? $rapport['annee_ref'] + 1 : $rapport['annee_ref'] ?>
                                                    </strong>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-white dark__bg-dark p-2">
                                                <div class="d-flex justify-content-between">
                                                    <a href="rapport_periode_view.php?id=<?= $rapport['id'] ?>" class="btn btn-sm btn-phoenix-primary">
                                                        <i class="fas fa-eye me-1"></i> Consulter
                                                    </a>
                                                    <div class="dropdown">
                                                        <button title="Exporter" disabled class="btn btn-sm btn-phoenix-secondary dropdown-toggle" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="fas fa-download me-1"></i> Exporter
                                                        </button>
                                                        <ul class="dropdown-menu p-0">
                                                            <li><a class="dropdown-item" href="exporter.php?id=<?= $rapport['id'] ?>&format=pdf">
                                                                    <i class="fas fa-file-pdf text-danger me-2"></i>PDF
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="exporter.php?id=<?= $rapport['id'] ?>&format=excel">
                                                                    <i class="fas fa-file-excel text-success me-2"></i>Excel
                                                                </a></li>
                                                            <li><a class="dropdown-item" href="exporter.php?id=<?= $rapport['id'] ?>&format=word">
                                                                    <i class="fas fa-file-word text-primary me-2"></i>Word
                                                                </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>