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

    $dash_state = array(
        array(
            'name' => 'Renforcement des Capacités',
            'indicateur' => 'Évaluation des Projets de Formation',
            'query' => 'SELECT * FROM state_rdc',
            'path' => 'state_rdc'
        ),
        array(
            'name' => 'Emissions des Entreprises',
            'indicateur' => 'Analyse des Emissions des Entreprises',
            'query' => 'SELECT * FROM state_ee',
            'path' => 'state_ee'
        ),
        array(
            'name' => 'Gestion des Plaintes',
            'indicateur' => 'Analyse des Plaintes',
            'query' => 'SELECT * FROM state_gp',
            'path' => 'state_gp'
        ),
        array(
            'name' => 'Bilan du Décaissement',
            'indicateur' => 'Analyse du Bilan du Décaissement',
            'query' => 'SELECT * FROM state_bd',
            'path' => 'state_bd'
        ),
    );
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
                        <h4 class="my-1 fw-black fs-8">Tableaux de bord des états</h4>
                    </div>

                    <div class="d-lg-flex flex-row">
                        <div class="ms-lg-2">
                            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addDashEtatModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                <i class="fas fa-plus"></i> Ajouter un état</button>
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
                        <div class="row g-3 mb-5">
                            <?php if (empty($dash_state)) { ?>
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
                                        <i class="fas fa-plus"></i> Ajouter un card</button>
                                </div>
                            <?php } else { ?>
                                <?php foreach ($dash_state as $state): ?>
                                    <div class="col-md-6 col-lg-3 rapport-item">
                                        <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="icon-wrapper-sm bg-primary bg-opacity-10 rounded-1 p-2">
                                                        <span class="fas fa-chart-area text-primary fs-6"></span>
                                                    </div>
                                                    <div class="ms-2">
                                                        <h5 class="mb-1 fw-bold"><?= $state['name'] ?></h5>
                                                        <small class="mb-0 text-body-tertiary"><?= $state['indicateur'] ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent p-3 pt-0 border-0">
                                                <button onclick="window.location.href = './<?= $state['path'] ?>.php'" class="btn btn-sm btn-subtle-primary rounded-1 w-100">
                                                    Consulter <i class="fas fa-arrow-right ms-1"></i>
                                                </button>
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