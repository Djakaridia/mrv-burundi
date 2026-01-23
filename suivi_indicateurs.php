<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Suivi des indicateurs | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $sel_id = isset($_GET['proj']) ? $_GET['proj'] : '';

    $project = new Projet($db);
    $all_projects = $project->read();
    $all_projects = array_filter($all_projects, function ($projet) {
        return $projet['state'] == 'actif';
    });

    if (!empty($all_projects) && $sel_id == '') {
        $sel_id = $all_projects[0]['id'];
    }

    if ($sel_id != '') {
        $project->id = $sel_id;
        $project_curr = $project->readById();

        $indicateur = new Indicateur($db);
        $indicateur->projet_id = $project_curr['id'];
        $indicateurs_project = $indicateur->readByProjet();

        $secteur = new Secteur($db);
        $secteurs = $secteur->read();
        $secteurs = array_filter($secteurs, function ($structure) {
            return $structure['state'] == 'actif' && $structure['parent'] == 0;
        });

        $secteurs_project = array_filter($secteurs, function ($s) use ($project_curr) {
            return in_array($s['id'], explode(',', str_replace('"', '', $project_curr['secteurs'])));
        });
    }

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $referentiel = new Referentiel($db);
    $referentiels = $referentiel->read();
    $referentiels = array_filter($referentiels, function ($referentiel) {
        return $referentiel['state'] == 'actif';
    });

    $unite = new Unite($db);
    $unites = $unite->read();

    $province = new Province($db);
    $provinces = $province->read();

    $zone_type = new ZoneType($db);
    $zone_types = $zone_type->read();

    $zone = new Zone($db);
    $zones = $zone->read();

    $typologie = new Typologie($db);
    $typologies = $typologie->read();
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
                        <h4 class="my-1 fw-black">Suivi des indicateurs</h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="result" id="resultID" onchange="window.location.href = 'suivi_indicateurs.php?proj=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner un projet---</option>
                                <?php foreach ($all_projects as $project) { ?>
                                    <option value="<?php echo $project['id']; ?>" <?php if ($sel_id == $project['id']) echo 'selected'; ?>><?php echo $project['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                        <?php if ($sel_id && $sel_id != '') { ?>
                            <a href="project_view.php?id=<?php echo $sel_id; ?>&tab=indicator" class="btn btn-phoenix-info rounded-pill btn-sm">
                                <span class="fa-solid fa-eye fs-9 me-2"></span>Voir les indicateurs
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if (!empty($all_projects) && $sel_id != '') { ?>
                            <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                    <thead class="bg-primary-subtle">
                                        <tr>
                                            <th class="sort align-middle" scope="col">Code</th>
                                            <th class="sort align-middle" scope="col">Intitule</th>
                                            <th class="sort align-middle" scope="col">Unité</th>
                                            <th class="sort align-middle" scope="col">Type</th>
                                            <th class="sort align-middle" scope="col">Calcul</th>
                                            <?php for ($year = date('Y', strtotime($project_curr['start_date'])); $year <= date('Y', strtotime($project_curr['end_date'])); $year++) : ?>
                                                <th class="sort align-middle bg-light dark__bg-secondary border text-center" scope="col"><?php echo $year; ?></th>
                                            <?php endfor; ?>
                                            <th class="sort align-middle" scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list" id="table-latest-review-body">
                                        <?php foreach ($indicateurs_project as $indicateur) {
                                            $referentiel = new Referentiel($db);
                                            $referentiel->id = $indicateur['referentiel_id'];
                                            $referentiel_curr = $referentiel->readById();

                                            // Recupérer les données des suivis
                                            $suivi = new Suivi($db);
                                            $suivi->cmr_id = $indicateur['id'];
                                            $suivis_cmr = $suivi->readByCMR();

                                            // Regrouper les données des suivis par année
                                            $suivis_cmr_grouped = array();
                                            foreach ($suivis_cmr as $suivi) {
                                                $suivis_cmr_grouped[$suivi['annee']][] = $suivi;
                                            }
                                        ?>

                                            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                <td class="align-middle px-2 py-0"> <?php echo $indicateur['code']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $indicateur['intitule']; ?> </td>
                                                <td class="align-middle px-2 py-0"><?php echo $indicateur['unite']; ?></td>

                                                <td class="align-middle px-2 py-0"> <?php echo strtoupper($referentiel_curr['categorie'] ?? '-'); ?> </td>
                                                <td class="align-middle px-2 py-0"> <?php echo listModeCalcul()[$indicateur['mode_calcul'] ?? '-']; ?> </td>

                                                <?php for ($year = date('Y', strtotime($project_curr['start_date'])); $year <= date('Y', strtotime($project_curr['end_date'])); $year++): ?>
                                                    <td class="align-middle bg-light dark__bg-secondary px-2 py-0 border text-center">
                                                        <?= calculSuiviData($suivis_cmr_grouped[$year] ?? [], $indicateur['mode_calcul']) ?>
                                                    </td>
                                                <?php endfor; ?>

                                                <td class="align-middle review d-flex gap-2">
                                                    <button title="Suivre" type="button" class="btn btn-subtle-primary rounded-pill btn-sm fw-bold fs-9 px-2 py-1" data-bs-toggle="modal"
                                                        data-bs-target="#newIndicateurSuiviModal" aria-haspopup="true" aria-expanded="false"
                                                        data-cmr_id="<?php echo $indicateur['id']; ?>" data-projet_id="<?php echo $project_curr['id']; ?>" data-referentiel_id="<?php echo $indicateur['referentiel_id']; ?>">
                                                        Suivre
                                                    </button>
                                                    <button title="Voir" type="button" class="btn btn-sm fw-bold fs-9 px-2 py-1 btn-phoenix-success" onclick="window.location.href = 'indicateur_view.php?id=<?= $indicateur['referentiel_id'] ?>';">
                                                        <span class="uil-arrow-right fs-8"></span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-5 my-5" style="min-height: 350px;">
                                <div class="d-flex justify-content-center mb-3">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                        <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                                <p class="text-600 mb-5">Veuillez ajouter un projet pour afficher ses indicateurs</p>
                                <a href="projects.php" class="btn btn-primary px-5 fs-8" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-arrow-right"></i> Aller vers les projets</a>
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

</html>