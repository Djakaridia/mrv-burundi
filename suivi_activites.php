<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Suivi des activités | MRV - Burundi</title>

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

    $project->id = $sel_id;
    $project_curr = $project->readById();

    if (empty($project_curr)) {
        echo "<script>window.location.href = 'suivi_activites.php';</script>";
        exit;
    }

    $tache = new Tache($db);
    $tache->projet_id = $project_curr['id'];
    $taches_project = $tache->readByProjet();

    $tache_indicateur = new TacheIndicateur($db);
    $tache_indicateurs = $tache_indicateur->read();
    $grouped_tache_indicateurs = [];
    foreach ($tache_indicateurs as $tache_indicateur) {
        $grouped_tache_indicateurs[$tache_indicateur['tache_id']][] = $tache_indicateur;
    }

    $tache_suivi_indicateur = new TacheSuiviIndicateur($db);
    $tache_suivi_indicateurs = $tache_suivi_indicateur->read();
    $grouped_tache_suivi_indicateurs = [];
    foreach ($tache_suivi_indicateurs as $tache_suivi_indicateur) {
        $grouped_tache_suivi_indicateurs[$tache_suivi_indicateur['tache_id']][] = $tache_suivi_indicateur;
    }

    $priorite = new Priorite($db);
    $priorites = $priorite->read();

    $user = new User($db);
    $users = $user->read();
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
                        <h4 class="my-1 fw-black">Suivi des activités</h4>
                    </div>

                    <div class="col-lg-3 mb-2 mb-lg-0 text-center">
                        <form action="formNiveauResultat" method="post">
                            <select class="btn btn-phoenix-primary rounded-pill btn-sm form-select form-select-sm rounded-1 text-start" name="result" id="resultID" onchange="window.location.href = 'suivi_activites.php?proj=' + this.value">
                                <option value="" class="text-center" selected disabled>---Sélectionner un projet---</option>
                                <?php foreach ($all_projects as $project) { ?>
                                    <option value="<?php echo $project['id']; ?>" <?php if ($sel_id == $project['id']) echo 'selected'; ?>><?php echo $project['name']; ?></option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="col-lg-4 mb-2 mb-lg-0 text-lg-end">
                        <?php if ($sel_id && $sel_id != '') { ?>
                            <a href="project_view.php?id=<?php echo $sel_id; ?>&tab=task" class="btn btn-phoenix-info rounded-pill btn-sm">
                                <span class="fa-solid fa-eye fs-9 me-2"></span>Voir les activités
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if (!empty($all_projects)) { ?>
                            <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                    <thead class="bg-secondary-subtle">
                                        <tr>
                                            <th class="align-middle">Code</th>
                                            <th class="align-middle" style="min-width:300px;">Libellé</th>
                                            <th class="align-middle">Responsable</th>
                                            <th class="align-middle text-center">Priorité</th>
                                            <th class="align-middle text-center">Indicateur</th>
                                            <th class="align-middle text-center">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($taches_project as $tache) {
                                            $indicateurs = $grouped_tache_indicateurs[$tache['id']] ?? [];
                                            $total_task_cible = array_sum(array_map('floatval', array_column($indicateurs, 'valeur_cible')));

                                            $suivis_indic = $grouped_tache_suivi_indicateurs[$tache['id']] ?? [];
                                            $total_task_suivi = array_sum(array_map('floatval', array_column($suivis_indic, 'valeur_suivi')));

                                            unset($taux_progress);
                                            $taux_progress = 0;
                                            if (isset($total_task_cible) && $total_task_cible > 0) {
                                                if ($total_task_suivi >= $total_task_cible) {
                                                    $taux_progress = 100;
                                                } else {
                                                    $taux_progress = round(($total_task_suivi / $total_task_cible) * 100, 2);
                                                }
                                            } else {
                                                $taux_progress = 0;
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $tache['code'] ?></td>
                                                <td>
                                                    <div data-todo-offcanvas-toogle="data-todo-offcanvas-toogle" data-todo-offcanvas-target="suiviOffcanvas<?= $tache['id'] ?>">
                                                        <a class="mb-0 fw-bold line-clamp-1 flex-grow-1 flex-md-grow-0 cursor-pointer"><?= $tache['name'] ?></a>
                                                    </div>
                                                </td>

                                                <td>
                                                    <?php foreach ($users as $user) {
                                                        if ($user['id'] == $tache['assigned_id']) {
                                                            echo $user['nom'] . ' ' . $user['prenom'];
                                                        }
                                                    } ?>
                                                </td>

                                                <td class="text-center">
                                                    <span class="text-body-highlight">
                                                        <?php foreach ($priorites as $priorite) {
                                                            if ($priorite['id'] == $tache['priorites_id']) {
                                                                echo '<span class="badge fs-10" style="background-color: gray">' . $priorite['name'] . '</span>';
                                                            }
                                                        } ?>
                                                    </span>
                                                </td>

                                                <td class="text-center p-0">
                                                    <a class="btn btn-link text-decoration-none fw-bold py-1 px-0 m-0" data-bs-toggle="modal"
                                                        data-bs-target="#SuiviTAskModal" aria-haspopup="true" aria-expanded="false" data-id="<?php echo $tache['id']; ?>">
                                                        <?php
                                                        if ($taux_progress < 39)
                                                            $color = "danger";
                                                        elseif ($taux_progress < 69)
                                                            $color = "warning";
                                                        elseif ($taux_progress >= 70)
                                                            $color = "success"; ?>
                                                        <span id="tauxIndic_<?php echo $tache['id']; ?>">
                                                            <div class="progress progress-xl rounded-0 p-0 m-0" style="height: 1.5rem; width: 200px">
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated fs-14 fw-bold bg-<?php echo $color; ?> " aria-valuenow="70" style="width: 100%;">
                                                                    <?php echo (isset($taux_progress) && $taux_progress > 0) ? $taux_progress . " %" : "Suivre"; ?>
                                                                </div>
                                                            </div>
                                                        </span>
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="col text-nowrap badge badge-phoenix fs-10 
                                                        badge-phoenix-<?php echo $taux_progress > 0 ? ($taux_progress >= 100 ? "success" : "warning") : "danger" ?>">
                                                        <?php echo $taux_progress > 0 ? ($taux_progress >= 100 ? "Terminé" : "En cours") : "Non suivi" ?>
                                                    </span>
                                                </td>
                                            </tr>


                                            <div class="offcanvas offcanvas-end content-offcanvas offcanvas-backdrop-transparent border-start shadow-none bg-body" tabindex="-1" data-todo-content-offcanvas="data-todo-content-offcanvas-<?= $tache['id'] ?>" id="suiviOffcanvas<?= $tache['id'] ?>">
                                                <div class="offcanvas-body p-0">
                                                    <div class="px-5 py-3">
                                                        <div class="d-flex flex-between-center align-items-start gap-5 mb-4">
                                                            <h2 class="fw-bold fs-6 mb-0 text-body-highlight"><?= htmlspecialchars($tache['name']) ?></h2>
                                                            <button title="Fermer" class="btn btn-phoenix-secondary shadow-sm btn-icon px-2" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
                                                                <span class="fa-solid fa-xmark"></span>
                                                            </button>
                                                        </div>

                                                        <div class="mb-4">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-body">Code:</span>
                                                                <span class="text-body-highlight fw-bold"><?= htmlspecialchars($tache['code']) ?></span>
                                                            </div>

                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-body">Statut:</span>
                                                                <span class="badge bg-<?= getBadgeClass($tache['status']) ?>"><?= htmlspecialchars($tache['status']) ?></span>
                                                            </div>

                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-body">Priorité:</span>
                                                                <span class="text-body-highlight">
                                                                    <?php foreach ($priorites as $priorite) {
                                                                        if ($priorite['id'] == $tache['priorites_id']) {
                                                                            echo '<span class="badge" style="background-color: ' . $priorite['couleur'] . '">' . $priorite['name'] . '</span>';
                                                                        }
                                                                    } ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="mb-5">
                                                            <h4 class="text-body me-3">Description</h4>
                                                            <p class="text-body-highlight mb-0"><?= nl2br(htmlspecialchars($tache['description'])) ?></p>
                                                        </div>

                                                        <div class="row mb-5">
                                                            <div class="col-md-6">
                                                                <h5 class="mb-2">Dates prévues</h5>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-body">Début:</span>
                                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['debut_prevu'])) ?></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-body">Fin:</span>
                                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['fin_prevue'])) ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h5 class="mb-2">Dates réelles</h5>
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="text-body">Début:</span>
                                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['debut_reel'] ?? '0000-00-00')) ?></span>
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-body">Fin:</span>
                                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['fin_reelle'] ?? '0000-00-00')) ?></span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-4">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-body">Assigné à:</span>
                                                                <span class="text-body-highlight">
                                                                    <?php
                                                                    foreach ($users as $user) {
                                                                        if ($user['id'] == $tache['assigned_id']) {
                                                                            echo $user['nom'] . ' ' . $user['prenom'];
                                                                        }
                                                                    } ?>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-body">Créé par:</span>
                                                                <span class="text-body-highlight">
                                                                    <?php
                                                                    foreach ($users as $user) {
                                                                        if ($user['id'] == $tache['add_by']) {
                                                                            echo $user['nom'] . ' ' . $user['prenom'];
                                                                        }
                                                                    } ?>
                                                                </span>
                                                            </div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="text-body">Date création:</span>
                                                                <span class="text-body-highlight"><?= date('Y-m-d H:i:s', strtotime($tache['created_at'])) ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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