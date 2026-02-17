<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Projets | MRV - Burundi</title>
  <style>
    #tableView,
    #cardView {
      display: none;
    }

    [data-view="table"] #tableView {
      display: block;
    }

    [data-view="card"] #cardView {
      display: block;
    }
  </style>

  <script>
    const viewMode = localStorage.getItem('viewMode') || 'table';
    document.documentElement.setAttribute('data-view', viewMode);
    document.addEventListener('DOMContentLoaded', function() {

      const button = document.getElementById('toggleView');
      const icon = document.getElementById('toggleIcon');

      if (!button || !icon) return;
      icon.className = viewMode === 'table' ? 'fs-7 uil-list-ul' : 'fs-7 uil-postcard';
      button.addEventListener('click', function() {
        let current = document.documentElement.getAttribute('data-view');
        let newView = current === 'table' ? 'card' : 'table';

        document.documentElement.setAttribute('data-view', newView);
        localStorage.setItem('viewMode', newView);
        icon.className = newView === 'table' ? 'fs-7 uil-list-ul' : 'fs-7 uil-postcard';
      });
    });
  </script>

  <?php
  include './components/navbar & footer/head.php';

  $projet = new Projet($db);
  $projets = $projet->read();

  if (isset($_GET['secteur']) && !empty($_GET['secteur'])) {
    $secteur = (int) $_GET['secteur'];
    $projets = array_filter($projets, function ($projet) use ($secteur) {
      return $projet['secteur_id'] == $secteur;
    });
  }

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    $projets = array_filter($projets, function ($projet) use ($action) {
      return $projet['action_type'] == $action;
    });
  }

  if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    $projets = array_filter($projets, function ($projet) use ($status) {
      return $projet['status'] == $status;
    });
  }

  $programme = new Programme($db);
  $programmes = $programme->read();
  $programmes = array_filter($programmes, function ($programme) {
    return $programme['state'] == 'actif';
  });

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

  $mesure = new Mesure($db);
  $mesures = $mesure->read();

  $gaz = new Gaz($db);
  $gazs = $gaz->read();

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
  });
  $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] > 0 && $secteur['state'] == 'actif';
  });

  $groupe_travail = new GroupeTravail($db);
  $groupes_travail = $groupe_travail->read();

  $user = new User($db);
  $users = $user->read();

  $conventions_par_projet = [];
  $couts_par_tache = [];

  if (!empty($projets)) {
    $convention = new Convention($db);
    $tache_cout = new TacheCout($db);
    $tache_couts = $tache_cout->read();

    foreach ($tache_couts as $cout) {
      $couts_par_tache[$cout['tache_id']][] = $cout;
    }

    foreach ($projets as $projet) {
      $convention->projet_id = $projet['id'];
      $conventions_par_projet[$projet['id']] = $convention->readByProjet();
    }
  }
  ?>
</head>

<body>
  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <main class="main" id="top">
    <?php include './components/navbar & footer/sidebar.php'; ?>
    <?php include './components/navbar & footer/navbar.php'; ?>

    <div class="content">
      <div class="mx-0 mt-n5">
        <div class="row g-3 mx-n5 mb-3">
          <div class="col-md-6 col-lg-3">
            <div class="card card-float h-100 rounded-1">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="text-muted text-uppercase small fw-bold">Total Projets</span>
                  <i class="fa fa-project-diagram text-primary fs-5"></i>
                </div>
                <?php
                $total_projets = count($projets);
                $projets_actifs = array_filter($projets, function ($p) {
                  return $p['state'] == 'actif';
                });
                $projets_archives = array_filter($projets, function ($p) {
                  return $p['state'] == 'archivé';
                });
                ?>
                <div class="d-flex align-items-baseline gap-2">
                  <span class="stat-value"><?= $total_projets ?></span>
                  <span class="text-primary fw-medium">projets</span>
                </div>
                <div class="mt-1">
                  <span class="badge bg-success-subtle text-success"><?= count($projets_actifs) ?> actifs</span>
                  <span class="badge bg-secondary-subtle text-secondary ms-1"><?= count($projets_archives) ?> archivés</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="card card-float h-100 rounded-1">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="text-muted text-uppercase small fw-bold">Budget Total (USD)</span>
                  <i class="fa fa-money-bill-wave text-warning fs-5"></i>
                </div>
                <?php
                $total_budget = 0;
                $budgets_valides = array_filter($projets, function ($p) {
                  return !empty($p['budget']) && $p['budget'] > 0;
                });
                foreach ($budgets_valides as $projet) {
                  $total_budget += floatval($projet['budget']);
                }
                ?>
                <div class="stat-value"><?= number_format($total_budget, 2, ',', ' ') ?></div>
                <div class="mt-2 text-muted small">
                  <?= count($budgets_valides) ?> projets budgetisés
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="card card-float h-100 rounded-1">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="text-muted text-uppercase small fw-bold">Projets en Cours</span>
                  <i class="fa fa-spinner text-info fs-5"></i>
                </div>
                <?php
                $now = new DateTime();
                $projets_en_cours = array_filter($projets, function ($p) use ($now) {
                  if (empty($p['start_date']) || empty($p['end_date'])) {
                    return false;
                  }

                  $start = new DateTime($p['start_date']);
                  $end = new DateTime($p['end_date']);

                  return $now >= $start && $now <= $end && $p['state'] == 'actif';
                });

                $projets_termines = array_filter($projets, function ($p) use ($now) {
                  if (empty($p['end_date'])) return false;
                  $end = new DateTime($p['end_date']);
                  return $now > $end && $p['state'] == 'actif';
                });
                ?>
                <div class="d-flex align-items-baseline gap-2">
                  <span class="stat-value"><?= count($projets_en_cours) ?></span>
                  <span class="text-info fw-medium">En cours</span>
                </div>
                <div class="mt-1">
                  <div class="progress" style="height: 5px;">
                    <?php
                    $pourcentage_termines = $total_projets > 0 ? round((count($projets_termines) / $total_projets) * 100) : 0;
                    $pourcentage_en_cours = $total_projets > 0 ? round((count($projets_en_cours) / $total_projets) * 100) : 0;
                    $pourcentage_futurs = 100 - $pourcentage_termines - $pourcentage_en_cours;
                    ?>
                    <div class="progress-bar bg-success" style="width: <?= $pourcentage_termines ?>%"
                      title="Terminés: <?= count($projets_termines) ?>"></div>
                    <div class="progress-bar bg-info" style="width: <?= $pourcentage_en_cours ?>%"
                      title="En cours: <?= count($projets_en_cours) ?>"></div>
                    <div class="progress-bar bg-light" style="width: <?= $pourcentage_futurs ?>%"
                      title="À venir"></div>
                  </div>
                  <small class="text-muted"><?= count($projets_termines) ?> terminés</small>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-lg-3">
            <div class="card card-float h-100 rounded-1">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                  <span class="text-muted text-uppercase small fw-bold">Gaz Concernés</span>
                  <i class="fa fa-smog text-success fs-5"></i>
                </div>
                <?php
                $gaz_unique_projets = [];
                foreach ($projets as $projet) {
                  if (!empty($projet['gaz'])) {
                    $gaz_list = explode(',', $projet['gaz']);
                    foreach ($gaz_list as $gaz) {
                      $gaz_clean = trim($gaz);
                      if ($gaz_clean) {
                        $gaz_unique_projets[$gaz_clean] = true;
                      }
                    }
                  }
                }
                $nombre_gaz = count($gaz_unique_projets);
                ?>
                <div class="stat-value"><?= $nombre_gaz ?></div>
                <div class="mt-1 text-muted small">
                  <?php if ($nombre_gaz > 0): ?>
                    <?= implode(', ', array_slice(array_keys($gaz_unique_projets), 0, 3)) ?>
                    <?php if ($nombre_gaz > 3): ?>...<?php endif; ?>
                  <?php else: ?>
                    Aucun gaz défini
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
        <div class="row mx-n1 py-1 align-items-center border-bottom">
          <div class="col-md-4">
            <h3 class="h5 mb-0 fw-bold">Liste des Projets Climatiques</h3>
            <p class="text-muted small mb-0">Tableau récapitulatif des projets</p>
          </div>

          <div class="col-md-8">
            <div class="d-flex gap-2 align-items-center justify-content-end">
              <div class="d-flex gap-1 align-items-center">
                <?php $currFilSecteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
                $currFilAction = isset($_GET['action']) ? $_GET['action'] : '';
                $currFilStatus = isset($_GET['status']) ? $_GET['status'] : ''; ?>
                <span class="form-label">Filtrer : </span>

                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="actionFilter"
                      onchange="pagesFilters([{ id: 'actionFilter', param: 'action' }])">
                      <option value="">Toutes actions</option>
                      <?php foreach (listTypeAction() as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($currFilAction == $key) ? 'selected' : '' ?>>
                          <?= $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>

                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="secteurFilter"
                      onchange="pagesFilters([{ id: 'secteurFilter', param: 'secteur' }])">
                      <option value="">Tous secteurs</option>
                      <?php if (isset($secteurs) && !empty($secteurs)): ?>
                        <?php foreach ($secteurs as $secteur): ?>
                          <option value="<?= $secteur['id'] ?>" <?= ($currFilSecteur == $secteur['id']) ? 'selected' : '' ?>>
                            <?= $secteur['name'] ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </form>
                </div>

                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="statusFilter"
                      onchange="pagesFilters([{ id: 'statusFilter', param: 'status' }])">
                      <option value="">Tous status</option>
                      <?php foreach (listStatus() as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($currFilStatus == $key) ? 'selected' : '' ?>>
                          <?= $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>
              </div>

              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addProjetModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un projet
              </button>

              <button type="button" id="toggleView" title="Changer l'affichage" class="btn btn-sm btn-subtle-primary px-2 py-1">
                <span id="toggleIcon" class="fs-7 uil-list-ul"></span>
              </button>
            </div>
          </div>
        </div>

        <div class="table-responsive mx-n1 p-1 scrollbar" style="min-height: 432px;">
          <div id="tableView">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
              <thead class="table-light">
                <tr>
                  <th>Intitulé du projet</th>
                  <th>Responsable</th>
                  <th class="text-center">Secteur</th>
                  <th class="text-center">Période</th>
                  <th class="text-center">Statut</th>
                  <th class="text-center">Progression</th>
                  <th class="text-center" style="width: 100px;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($projets as $projet):
                  $logoParts = explode("../", $projet['logo'] ?? '');

                  $tache_projet = new Tache($db);
                  $tache_projet->projet_id = $projet['id'];
                  $taches_projet = $tache_projet->readByProjet();
                  $taches_projet = array_filter($taches_projet, function ($tache) {
                    return $tache['state'] == 'actif';
                  });

                  $totalTacheCount = count($taches_projet);
                  $finishedTacheCount = count(array_filter($taches_projet, function ($tache) {
                    return strtolower($tache['status']) === 'terminée';
                  }));
                  $progress = $totalTacheCount > 0 ? (round(($finishedTacheCount / $totalTacheCount), 2) * 100) : 0;
                ?>

                  <tr>
                    <td><a href="project_view.php?id=<?= $projet['id'] ?>" class="text-muted fw-bold"><?= html_entity_decode($projet['name']) ?></a></td>
                    <td><span class="fw-semibold"><?= $projet['structure_sigle'] ?></span></td>
                    <td class="text-center">
                      <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-light rounded-pill">
                        <?php foreach ($secteurs as $secteur) {
                          if ($secteur['id'] == $projet['secteur_id']) {
                            echo $secteur['name'];
                            break;
                          }
                        } ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-warning rounded-pill">
                        <?= date('Y', strtotime($projet['start_date'])) ?> - <?= date('Y', strtotime($projet['end_date'])) ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-<?= getBadgeClass($projet['status']); ?>">
                        <?= listStatus()[$projet['status']]; ?>
                      </span>
                    </td>
                    <td class="text-center p-0">
                      <a onclick="window.location.href='suivi_activites.php?proj=<?= $projet['id'] ?>'" class="btn btn-link text-decoration-none fw-bold py-1 px-0 m-0">
                        <?php
                        if ($progress < 39)
                          $color = "danger";
                        elseif ($progress < 69)
                          $color = "warning";
                        elseif ($progress >= 70)
                          $color = "success"; ?>
                        <span id="tauxProj_<?php echo $projet['id']; ?>">
                          <div class="progress progress-xl rounded-0 p-0 m-0" style="height: 1.5rem; width: 200px">
                            <div class="progress-bar progress-bar-striped progress-bar-animated fs-14 fw-bold bg-<?php echo $color; ?> " aria-valuenow="70" style="width: 100%;">
                              <?php echo (isset($progress) && $progress > 0) ? $progress . " %" : "Non entamé"; ?>
                            </div>
                          </div>
                        </span>
                      </a>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <?php if (checkPermis($db, 'update')) : ?>
                          <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal" data-bs-target="#addProjetModal" data-id="<?= $projet['id']; ?>"><span class="uil-pen fs-8"></span></button>
                        <?php endif; ?>

                        <?php if (checkPermis($db, 'delete', 2)) : ?>
                          <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" onclick="deleteData(<?= $projet['id']; ?>,'Voulez-vous vraiment supprimer ce projet ?', 'projets', 'redirect', 'projects.php')"><span class="uil-trash-alt fs-8"></span></button>
                        <?php endif; ?>

                        <button title="Voir" class="btn btn-sm btn-phoenix-primary fs-10 px-2 py-1" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>"><span class="uil-eye fs-8"></span></button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div id="cardView">
            <div class="row g-3 m-0 bg-body-emphasis">
              <?php if (empty($projets)) { ?>
                <div class="text-center py-5 my-5">
                  <div class="d-flex justify-content-center mb-4">
                    <div class="bg-light rounded-circle p-4">
                      <i class="fas fa-tasks fa-3x text-primary opacity-50"></i>
                    </div>
                  </div>
                  <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                  <p class="text-muted mb-4">Commencez par créer votre premier projet pour visualiser les données</p>
                  <a href="project_add.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Créer un projet
                  </a>
                </div>
              <?php } else { ?>
                <?php foreach ($projets as $projet):
                  $logoParts = explode("../", $projet['logo'] ?? '');
                  $conventions_projet = $conventions_par_projet[$projet['id']] ?? [];
                  $budget_conventions = array_sum(array_column($conventions_projet, 'montant'));

                  $tache_projet = new Tache($db);
                  $tache_projet->projet_id = $projet['id'];
                  $taches_projet = $tache_projet->readByProjet();
                  $taches_actives = array_filter($taches_projet, function ($tache) {
                    return $tache['state'] == 'actif';
                  });

                  $totalTacheCount = count($taches_actives);
                  $finishedTacheCount = count(array_filter($taches_actives, function ($tache) {
                    return strtolower($tache['status'] ?? '') === 'terminée';
                  }));

                  $taux_physique = $totalTacheCount > 0 ? round(($finishedTacheCount / $totalTacheCount) * 100, 1) : 0;
                  $montant_total_depense = 0;
                  foreach ($taches_actives as $tache) {
                    if (isset($couts_par_tache[$tache['id']])) {
                      $montant_total_depense += array_sum(array_column($couts_par_tache[$tache['id']], 'montant'));
                    }
                  }

                  $budget_reference = $budget_conventions > 0 ? $budget_conventions : ($projet['budget'] ?? 0);
                  $taux_financier = $budget_reference > 0 ? round(($montant_total_depense / $budget_reference) * 100, 1) : 0;
                  $budget_formate = ($projet['budget'] ?? 0) > 0 ? number_format($projet['budget'], 0, ',', ' ') . ' USD' : 'Non défini';
                  $budget_conventions_formate = $budget_conventions > 0 ? number_format($budget_conventions, 0, ',', ' ') . ' USD' : 'Aucune convention';
                  $montant_depense_formate = $montant_total_depense > 0 ? number_format($montant_total_depense, 0, ',', ' ') . ' USD' : '0 USD';
                ?>
                  <div class="col-12 col-lg-6 col-xl-4">
                    <div class="card card-float border border-primary-subtle h-100 overflow-hidden">
                      <div class="card-body p-3">
                        <div class="d-flex align-items-start mb-2 gap-2">
                          <?php if (!empty($projet['logo'])): ?>
                            <img class="rounded-2 border border-light shadow-sm object-fit-cover"
                              src="<?php echo end($logoParts); ?>" alt="Logo projet"
                              style="width: 60px; height: 60px; object-fit: cover;" />
                          <?php else: ?>
                            <div class="bg-light rounded-2 d-flex align-items-center justify-content-center border border-light"
                              style="width: 60px; height: 60px;">
                              <i class="fas fa-image fa-4x text-body-tertiary opacity-50"></i>
                            </div>
                          <?php endif; ?>

                          <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-secondary">
                                <?php foreach ($secteurs as $secteur) {
                                  if ($secteur['id'] == $projet['secteur_id']) echo $secteur['name'];
                                } ?>
                              </span>

                              <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-<?= $projet['status'] ? getBadgeClass($projet['status']) : "secondary"; ?>">
                                <?= listStatus()[$projet['status']] ?? 'N/A'; ?>
                              </span>
                            </div>
                            <h5 title="<?= html_entity_decode($projet['name']) ?>" class="mb-0 text-primary fw-bold fs-8" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word;">
                              <?= html_entity_decode($projet['name']) ?>
                            </h5>
                          </div>
                        </div>

                        <div class="mb-3">
                          <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary bg-opacity-10 rounded-1 px-1 me-2">
                              <i class="fas fa-user-tie text-primary fs-10"></i>
                            </div>
                            <span class="text-muted small">Responsable :</span>
                            <span class="fw-semibold ms-2 small"><?= $projet['structure_sigle'] ?? 'N/A' ?></span>
                          </div>

                          <div class="d-flex align-items-center mb-2">
                            <div class="bg-warning bg-opacity-10 rounded-1 px-1 me-2">
                              <i class="fas fa-calendar-alt text-warning fs-10"></i>
                            </div>
                            <span class="text-muted small">Période :</span>
                            <span class="fw-semibold ms-2 small">
                              <?= date('d/m/Y', strtotime($projet['start_date'])) ?> -
                              <?= date('d/m/Y', strtotime($projet['end_date'])) ?>
                            </span>
                          </div>

                          <div class="d-flex align-items-center mb-2">
                            <div class="bg-success bg-opacity-10 rounded-1 px-1 me-2">
                              <i class="fas fa-coins text-success fs-10"></i>
                            </div>
                            <span class="text-muted small">Budget :</span>
                            <span class="fw-bold ms-2 text-success small"><?= $budget_formate ?></span>
                          </div>

                          <div class="d-flex align-items-center mb-2">
                            <div class="bg-info bg-opacity-10 rounded-1 px-1 me-2">
                              <i class="fas fa-hand-holding-usd text-info fs-10"></i>
                            </div>
                            <span class="text-muted small">Conventions :</span>
                            <span class="fw-semibold ms-2 small">
                              <?= count($conventions_projet) ?> convention(s) - <?= $budget_conventions_formate ?>
                            </span>
                          </div>
                        </div>

                        <div class="bg-light rounded-1 p-3">
                          <div class="mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="small text-muted">
                                <i class="fas fa-chart-line me-1 text-primary"></i>
                                Exécution physique
                              </span>
                              <span class="fw-bold small <?= $taux_physique >= 75 ? 'text-success' : ($taux_physique >= 40 ? 'text-warning' : 'text-danger') ?>">
                                <?= $taux_physique ?>%
                              </span>
                            </div>
                            <div class="progress bg-soft-secondary" style="height: 8px;">
                              <div class="progress-bar bg-<?= $taux_physique >= 75 ? 'success' : ($taux_physique >= 40 ? 'warning' : 'danger') ?>"
                                role="progressbar" style="width: <?= $taux_physique ?>%;" aria-valuenow="<?= $taux_physique ?>" aria-valuemin="0" aria-valuemax="100">
                              </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                              <span class="small text-muted"><?= $finishedTacheCount ?>/<?= $totalTacheCount ?> tâches</span>
                            </div>
                          </div>

                          <hr>
                          <div class="mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                              <span class="small text-muted">
                                <i class="fas fa-percent me-1 text-info"></i>
                                Exécution financière
                              </span>
                              <span class="fw-bold small <?= $taux_financier >= 75 ? 'text-success' : ($taux_financier >= 40 ? 'text-warning' : 'text-danger') ?>">
                                <?= $taux_financier ?>%
                              </span>
                            </div>
                            <div class="progress bg-soft-secondary" style="height: 8px;">
                              <div class="progress-bar bg-<?= $taux_financier >= 75 ? 'success' : ($taux_financier >= 40 ? 'warning' : 'danger') ?>"
                                role="progressbar" style="width: <?= $taux_financier ?>%;" aria-valuenow="<?= $taux_financier ?>" aria-valuemin="0" aria-valuemax="100">
                              </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                              <span class="small text-muted" title="Dépensé / Budget total">
                                <?= $montant_depense_formate ?> / <?= $budget_conventions_formate ?>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="card-footer bg-transparent border-0 p-3 pt-0">
                        <div class="d-flex align-items-center gap-3">
                          <button title="Voir" class="btn btn-sm btn-phoenix-info rounded-1 w-100" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>">
                            <i class="fas fa-eye me-1"></i> Aperçu
                          </button>

                          <a href="project_view.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-phoenix-warning rounded-1 w-100">
                            <i class="fas fa-list me-1"></i>Détails
                          </a>
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
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>