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

  <?php
  include './components/navbar & footer/head.php';

  $projet = new Projet($db);
  $projets = $projet->read();

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

  $priorite = new Priorite($db);
  $priorites = $priorite->read();

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent_id'] == 0 && $secteur['state'] == 'actif';
  });
  $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent_id'] > 0 && $secteur['state'] == 'actif';
  });

  $groupe_travail = new GroupeTravail($db);
  $groupes_travail = $groupe_travail->read();

  $user = new User($db);
  $users = $user->read();
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

      <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
          <div class="col-auto">
            <h4 class="my-1 fw-black">Liste des projets</h4>
          </div>

          <div class="d-lg-flex flex-row">
            <div class="search-box my-lg-0 my-2">
              <form class="position-relative">
                <input
                  id="searchProjet"
                  class="form-control form-control-sm search-input search"
                  type="search"
                  placeholder="Rechercher un projet"
                  aria-label="Search" />
                <span class="fas fa-search search-box-icon"></span>
              </form>
            </div>

            <div class="ms-lg-2">
              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addProjetModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un projet</button>
            </div>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="row g-3 mx-n4 pb-5 mx-lg-n6 bg-body-emphasis border-y">
            <?php if (empty($projets)) { ?>
              <div class="text-center py-5 my-5" style="min-height: 350px;">
                <div class="d-flex justify-content-center mb-3">
                  <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                    <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                </div>
                <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                <p class="text-600 mb-5">Il semble que vous n'ayez pas encore de projets. Commencez par en créer un.</p>
                <button title="Ajouter" class="btn btn-primary px-5 fs-8" id="addBtn" data-bs-toggle="modal" data-bs-target="#addProjetModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                  <i class="fas fa-plus"></i> Ajouter un projet</button>
              </div>
            <?php } else { ?>
              <?php foreach ($projets as $projet) :
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
                <div class="col-12 col-lg-4 col-xl-4 projet-item">
                  <div class="card h-100 hover-actions-trigger rounded-top-0 border-0 border-top border-4 border-primary shadow-sm">
                    <div class="card-header px-2 py-0 bg-primary-subtle rounded-0 border border-bottom-0 border-primary-subtle">
                      <div class="d-flex align-items-center justify-content-between">
                        <a href="project_view.php?id=<?= $projet['id'] ?>" class="text-decoration-none text-body-emphasis">
                          <h5 class="mb-1 line-clamp-1 lh-sm flex-1 me-3 text-primary"><?= html_entity_decode($projet['name']) ?></h5>
                        </a>
                        <div class="top-0 end-0 gap-1">
                          <button title="Voir" class="btn btn-subtle-primary btn-icon flex-shrink-0 rounded-circle" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>">
                            <span class="fa-solid fa-eye"></span>
                          </button>
                        </div>
                      </div>
                    </div>

                    <div class="card-body p-3 border border-primary-subtle border-top-0 rounded-bottom-sm">
                      <span class="badge badge-phoenix fs-10 mb-2 rounded-pill badge-phoenix-<?= $projet['state'] == 'actif' ? 'success' : 'danger'; ?>"><?= $projet['state'] == 'actif' ? 'Actif' : 'Archivé'; ?></span>

                      <div class="row g-3 d-flex flex-row align-items-center">
                        <!-- <div class="col-lg-2 col-12 mb-3">
                          <?php if (!empty($projet['logo'])) : ?>
                            <img class="rounded-1 w-100 border border-light shadow-sm" src="<?php echo end($logoParts); ?>"
                              alt="no-image" style="min-height: 65px; max-height: 150px; object-fit: contain; object-position: center;" />
                          <?php else : ?>
                            <i class="far fa-image fs-1 text-body-tertiary"></i>
                          <?php endif; ?>
                        </div> -->
                        <div class="col-lg-10 col-12 mb-1 fs-9">
                          <div class="d-flex align-items-center mb-1">
                            <span class="fa-solid fa-chalkboard me-2 text-body-tertiary fs-10 fw-extra-bold"></span>
                            <p class="mb-0 text-truncate">Code : <span class="fw-semibold ms-1"> <?= $projet['code'] ?? "NA"; ?></span></p>
                            <span class="mx-3">|</span>
                            <p class="mb-0 text-truncate">Status : <span class="badge badge-phoenix fs-10 badge-phoenix-warning"><?= $projet['status'] ?></span></p>
                          </div>

                          <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-user-shield me-2 text-body-tertiary fs-10 fw-extra-bold"></span>
                            <p class="mb-0 text-truncate">Responsable : <span class="fw-semibold ms-1"><?= $projet['structure_sigle'] ?></span></p>
                          </div>

                          <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-rocket me-2 text-body-tertiary fs-10 fw-extra-bold"></span>
                            <p class="mb-0 text-truncate">Type action :
                              <span class="fw-semibold ms-1">
                                <?= $projet['action_type'] == 'adaptation' ? 'Adaptation' : 'Atténuation' ?>
                              </span>
                            </p>
                          </div>
                        </div>
                      </div>

                      <div class="d-flex justify-content-between text-body-tertiary fw-semibold">
                        <p class="mb-1 fs-9">Progression</p>
                        <p class="mb-1 fs-9 text-body-emphasis">
                          <?= $progress ?>%
                        </p>
                      </div>

                      <div class="progress bg-warning-subtle">
                        <div class="progress-bar rounded bg-warning" role="progressbar" aria-label="progression" style="width: <?= $progress ?>%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>

                      <div class="d-flex align-items-center justify-content-between mt-2">
                        <p class="mb-0 fs-9"><span class="far fa-calendar"></span> Début :<span class="fw-semibold text-body-tertiary text-opactity-85 ms-1"> <?= date('Y-m-d', strtotime($projet['start_date'])) ?></span></p>
                        <p class="mb-0 fs-9"><span class="far fa-calendar"></span> Clôture : <span class="fw-semibold text-body-tertiary text-opactity-85 ms-1"> <?= date('Y-m-d', strtotime($projet['end_date'])) ?></span></p>
                      </div>

                      <div class="d-flex d-lg-block d-xl-flex justify-content-between align-items-center mt-2 fs-9">
                        <div class="avatar-group">
                          <?php
                          $group = new GroupeUsers($db);
                          $group_users = $group->read();
                          $group_users = array_filter($group_users, function ($group) use ($projet) {
                            return in_array($group['groupe_id'], explode(',', str_replace('"', '', $projet['groupes'])));
                          });

                          $users_ids = array_map(function ($group) {
                            return $group['user_id'];
                          }, $group_users);

                          $user = new User($db);
                          $users_project = $user->read();
                          $users_project = array_filter($users_project, function ($user) use ($users_ids) {
                            return in_array($user['id'], $users_ids);
                          }); ?>

                          <?php if (!empty($users_project)) : ?>
                            <?php foreach (array_slice($users_project, 0, 4) as $user) : ?>
                              <a class="dropdown-toggle dropdown-caret-none d-inline-block" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                <div class="avatar avatar-m">
                                  <div class="avatar-name rounded-circle bg-soft-info shadow-sm border-light">
                                    <span class="text-body-tertiary fs-9"><?= substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1) ?></span>
                                  </div>
                                </div>
                              </a>
                              <div class="dropdown-menu shadow-sm p-0">
                                <div class="row g-0 p-2">
                                  <div class="col-lg-3 col-12">
                                    <div class="avatar avatar-xl">
                                      <div class="avatar-name rounded-1 bg-soft-info shadow-sm border-light">
                                        <span class="text-body-tertiary fs-6"><?= substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1) ?></span>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-lg-9 col-12 list-group list-group-flush">
                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Nom: </small><?php echo $user['nom']; ?></div>
                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Prenom: </small><?php echo $user['prenom']; ?></div>
                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Email: </small><?php echo $user['email']; ?></div>
                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Contact: </small><?php echo $user['phone']; ?></div>
                                  </div>
                                </div>
                              </div>
                            <?php endforeach; ?>

                            <?php if (count($users_project) > 4) : ?>
                              <div class="avatar avatar-m">
                                <div class="avatar-name rounded-circle bg-soft-info shadow-sm border-light">
                                  <span class="text-body-tertiary fs-9">+<?= count($users_project) - 4 ?></span>
                                </div>
                              </div>
                            <?php endif; ?>
                          <?php else : ?>
                            <div class="mt-lg-3 mt-xl-0 text-body-tertiary">
                              <i class="fa-solid fa-users me-1"></i><span class="fw-normal"> 0 Membres</span>
                            </div>
                          <?php endif; ?>
                        </div>

                        <div class="mt-lg-3 mt-xl-0 text-body-tertiary">
                          <i class="fa-solid fa-list-check me-1"></i>
                          <p class="d-inline-block mb-0">
                            <?php
                            $tache = new Tache($db);
                            $tache->projet_id = $projet['id'];
                            $taches_project = $tache->readByProjet();
                            $taches_project = array_filter($taches_project, function ($tache) {
                              return $tache['state'] == 'actif';
                            });

                            echo count($taches_project);
                            ?>
                            <span class="fw-normal"> Activités</span>
                          </p>
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

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>