<?php
$userId = $_SESSION['user-data']['user-id'];
$task = new Tache($db);
$tasks = $task->read();
$user_tasks = array_filter($tasks, function ($task) use ($userId) {
    return $task['assigned_id'] == $userId;
});
?>

<div class="bg-white dark__bg-dark card rounded-1 mt-1" style="min-height: 300px;">
    <div class="card-body p-1 scrollbar">
        <div class="row align-items-center g-0 justify-content-between mb-3">
            <div class="col-6 col-sm-auto d-flex align-items-center">
                <div class="search-box w-100 mb-2 mb-sm-0" style="max-width:30rem;">
                    <form class="position-relative">
                        <input id="searchProfilTask" class="form-control form-control-sm rounded-1 search-input search" type="search" placeholder="Rechercher une activité"
                            aria-label="Rechercher une activité" />
                        <span class="fas fa-search search-box-icon"></span>
                    </form>
                </div>
            </div>
        </div>

        <div class="todo-list bg-body-emphasis position-relative top-1" style="min-height: 200px;">
            <div class="todo-list">
                <?php if (empty($user_tasks)) { ?>
                    <div class="text-center py-5 my-5" style="min-height: 200px;">
                        <div class="d-flex justify-content-center mb-3">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h4 class="text-800 mb-3">Aucune activité trouvée</h4>
                        <p class="text-600 mb-5">Il semble que vous n'ayez pas encore d'activités. Commencez par en créer une.</p>
                    </div>
                <?php } else { ?>
                    <?php foreach ($user_tasks as $task) { ?>
                        <div class="row profile-task hover-actions-trigger btn-reveal-trigger p-3 gx-0 cursor-pointer shadow rounded-1 border border-light">
                            <div class="col-12 col-md-auto flex-1">
                                <div class="mb-1 mb-md-0 d-flex align-items-center lh-1">
                                    <button title="Suivre" type="button" class="btn btn-subtle-primary rounded-pill btn-sm fw-bold fs-9 px-2 py-1" data-bs-toggle="modal"
                                        data-bs-target="#SuiviTAskModal" aria-haspopup="true" aria-expanded="false"
                                        data-id="<?php echo $task['id']; ?>">Suivre
                                    </button>

                                    <div data-todo-offcanvas-toogle="data-todo-offcanvas-toogle" data-todo-offcanvas-target="todoOffcanvas<?= $task['id'] ?>">
                                        <a class="mb-0 fs-8 ms-2 line-clamp-1 flex-grow-1 flex-md-grow-0 cursor-pointer"><?= $task['name'] ?></a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-auto">
                                <div class="d-flex ms-4 lh-1 align-items-center">
                                    <span class="col text-nowrap badge badge-phoenix fs-10 badge-phoenix-<?= getBadgeClass($task['status']) ?>"> <?= $task['status'] ?> </span>
                                    <span class="mx-2 text-light">|</span>
                                    <span class="col text-nowrap text-body-tertiary fs-10 mb-0"><?= date('Y-m-d', strtotime($task['debut_prevu'])) ?></span>
                                    <span class="mx-2 text-light">|</span>
                                    <span class="col text-nowrap btn btn-link p-0 text-warning fs-10"><span class="fas fa-paperclip me-1"></span><?= $task['task_files_count'] ?? 0 ?></span>
                                    <span class="mx-2 text-light">|</span>
                                    <span class="col text-nowrap btn btn-link p-0 text-info fs-10"><span class="fas fa-tasks me-1"></span><?= $task['subtasks_count'] ?? 0 ?></span>
                                    <span class="mx-2 text-light">|</span>

                                    <div class="hover-md-hide hover-lg-show hover-xl-hide me-3">
                                        <span class="col badge badge-phoenix fs-10 badge-phoenix-<?php echo $task['state'] == 'actif' ? 'success' : 'danger'; ?>">
                                            <?php echo $task['state'] == 'actif' ? 'Actif' : 'Inactif'; ?>
                                            <span class="ms-1 uil <?php echo $task['state'] == 'actif' ? 'uil-check-circle' : 'uil-ban'; ?> fs-10"></span>
                                        </span>
                                    </div>

                                    <div class="d-none d-md-block end-0 position-absolute mx-2" style="top: 23%;">
                                        <div class="hover-actions end-0">
                                            <?php if (checkPermis($db, 'update')) : ?>
                                                <button title="Modifier" type="button" class="btn btn-subtle-info btn-icon fs-9 rounded-1 ms-1" data-bs-toggle="modal" data-bs-target="#addTacheModal" data-id="<?= $task['id'] ?>" data-event-propagation-prevent="data-event-propagation-prevent">
                                                    <span class="fas fa-edit"></span>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (checkPermis($db, 'update', 2)) : ?>
                                                <button title="<?php echo $task['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>" class="btn btn-subtle-warning btn-icon fs-9 rounded-1 ms-1" onclick="updateState(<?= $task['id']; ?>, '<?php echo $task['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $task['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cette activité ?', 'taches')">
                                                    <span class="uil-<?php echo $task['state'] == 'actif' ? 'ban' : 'check-circle'; ?>"></span>
                                                </button>
                                            <?php endif; ?>

                                            <?php if (checkPermis($db, 'delete')) : ?>
                                                <button title="Supprimer" type="button" class="btn btn-subtle-danger btn-icon fs-9 rounded-1 ms-1" onclick="deleteData(<?= $task['id'] ?>, 'Voulez-vous vraiment supprimer cette activité ?', 'taches')">
                                                    <span class="fas fa-trash"></span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="offcanvas offcanvas-end content-offcanvas offcanvas-backdrop-transparent border-start shadow-none bg-body" tabindex="-1" data-todo-content-offcanvas="data-todo-content-offcanvas-<?= $task['id'] ?>" id="todoOffcanvas<?= $task['id'] ?>">
                                <div class="offcanvas-body p-0">
                                    <div class="px-5 py-3">
                                        <div class="d-flex flex-between-center align-items-start gap-5 mb-4">
                                            <h2 class="fw-bold fs-6 mb-0 text-body-highlight"><?= ($task['name']) ?></h2>
                                            <button title="Fermer" class="btn btn-phoenix-secondary shadow-sm btn-icon px-2" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
                                                <span class="fa-solid fa-xmark"></span>
                                            </button>
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Code:</span>
                                                <span class="text-body-highlight fw-bold"><?= ($task['code']) ?></span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Statut:</span>
                                                <span class="badge bg-<?= getBadgeClass($task['status']) ?> text-capitalize"><?= ($task['status']) ?></span>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Priorité:</span>
                                                <span class="text-body-highlight">
                                                    <span class="badge bg-primary text-capitalize"><?= ($task['priorite']) ?></span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-5">
                                            <h4 class="text-body me-3">Description</h4>
                                            <p class="text-body-highlight mb-0"><?= nl2br(($task['description'])) ?></p>
                                        </div>

                                        <div class="row mb-5">
                                            <div class="col-md-6">
                                                <h5 class="mb-2">Dates prévues</h5>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-body">Début:</span>
                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($task['debut_prevu'])) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-body">Fin:</span>
                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($task['fin_prevue'])) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h5 class="mb-2">Dates réelles</h5>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-body">Début:</span>
                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($task['debut_reel'] ?? '0000-00-00')) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-body">Fin:</span>
                                                    <span class="text-body-highlight"><?= date('Y-m-d', strtotime($task['fin_reelle'] ?? '0000-00-00')) ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Assigné à:</span>
                                                <span class="text-body-highlight">
                                                    <?php
                                                    foreach ($users as $user) {
                                                        if ($user['id'] == $task['assigned_id']) {
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
                                                        if ($user['id'] == $task['add_by']) {
                                                            echo $user['nom'] . ' ' . $user['prenom'];
                                                        }
                                                    } ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-body">Date création:</span>
                                                <span class="text-body-highlight"><?= date('Y-m-d H:i:s', strtotime($task['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php }
                } ?>
            </div>
        </div>
    </div>
</div>