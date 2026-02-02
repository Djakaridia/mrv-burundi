<div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
    <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">Liste des groupes</h4>
        </div>

        <button title="Ajouter un groupe" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
            data-bs-target="#addGroupeModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
            <i class="fas fa-plus"></i> Ajouter un groupe</button>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="row mx-n4 py-3 mx-lg-n6 bg-body-emphasis border-y">
            <?php if (empty($groupes)) { ?>
                <div class="text-center py-5 my-5" style="min-height: 300px;">
                    <div class="d-flex justify-content-center mb-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="text-warning">
                            <path
                                d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h4 class="text-800 mb-3">Aucun groupe trouvé</h4>
                    <p class="text-600 mb-5">Il semble que vous n'ayez pas encore de groupes. Commencez par en créer un.</p>
                    <button title="Ajouter un groupe" class="btn btn-primary px-5 fs-8" id="addBtn" data-bs-toggle="modal"
                        data-bs-target="#addGroupeModal" aria-haspopup="true" aria-expanded="false"
                        data-bs-reference="parent">
                        <i class="fas fa-plus"></i> Ajouter un groupe</button>
                </div>
            <?php } else { ?>
                <?php foreach ($groupes as $groupe):
                    $notification = new Notification($db);
                    $notification->entity_type = "group";
                    $notification->user_id = $_SESSION['user-data']['user-id'];
                    $group_notifications = $notification->readByEntity();
                    $unread_notifications = array_filter($group_notifications, function ($notif) use ($groupe) {
                        return $notif['is_read'] == false && $notif['entity_id'] == $groupe['id'];
                    });
                    $unread_count = count($unread_notifications);
                    ?>
                    <div class="col-12 col-lg-4 col-xl-4 mb-3">
                        <div
                            class="card h-100 hover-actions-trigger rounded-bottom-sm rounded-top-0 border-0 border-top border-4 border-primary shadow-sm">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <h4 class="mb-2 line-clamp-1 lh-sm flex-1 me-3 text-primary"><?= $groupe['name'] ?></h4>

                                    <?php if ($unread_count > 0): ?>
                                        <a href="notifications.php?nt=group" class="btn-link flex-shrink-0 position-relative">
                                            <span data-feather="bell" style="height:20px;width:20px;"></span>
                                            <span
                                                class="badge bg-warning rounded-circle p-1 position-absolute top-0 start-100 translate-middle fs-11"><?= $unread_count ?></span>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex align-items-center mb-2"><span
                                        class="fa-solid fas fa-chalkboard me-2 text-body-tertiary fs-9 fw-extra-bold"></span>
                                    <p class="mb-0 text-truncate lh-1">Code : <span class="fw-semibold ms-1">
                                            <?= $groupe['code'] ?? "NA"; ?></span></p>

                                    <span class="mx-2">|</span>
                                    <span
                                        class="fw-semibold text-capitalize fs-10 rounded-pill badge bg-<?php echo $groupe['state'] == 'actif' ? 'success' : 'warning' ?>">
                                        <?php echo $groupe['state'] ?></span>
                                </div>

                                <div class="d-flex align-items-center mb-2"><span
                                        class="fa-solid fa-users me-2 text-body-tertiary fs-9 fw-extra-bold"></span>
                                    <p class="mb-0 text-truncate lh-1">Superviseur : <span class="fw-semibold ms-1">
                                            <?php foreach ($structures as $structure) {
                                                if ($structure['id'] == $groupe['monitor']) {
                                                    echo $structure['sigle'];
                                                }
                                            } ?>
                                        </span></p>
                                </div>

                                <div class="d-flex align-items-center mb-2"><span
                                        class="fa-solid far fa-list-alt me-2 text-body-tertiary fs-9 fw-extra-bold"></span>
                                    <p class="mb-0 text-truncate lh-1">Description : <span class="fw-semibold ms-1">
                                            <?= $groupe['description'] ?? "NA"; ?></span></p>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-3">
                                    <a title="Consulter" class="btn btn-sm btn-subtle-primary me-1 fs-10 px-2 py-1"
                                        href="group_view.php?id=<?php echo $groupe['id']; ?>">
                                        <span class="uil-eye fs-8"></span> <span class="fs-9">Consulter</span>
                                    </a>

                                    <div class="dropdown">
                                        <button title="Actions" class="btn btn-sm dropdown-toggle dropdown-caret-none" type="button"
                                            data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true"
                                            aria-expanded="false" data-bs-reference="parent">
                                            <span class="fas fa-ellipsis-v fs-8 text-body"></span>
                                        </button>
                                        <div class="dropdown-menu p-0">
                                            <div class="d-flex flex-column">
                                                <?php if (checkPermis($db, 'update')): ?>
                                                    <div class="dropdown-item border-top border-light p-1">
                                                        <a data-bs-toggle="modal" data-bs-target="#addGroupeModal" data-niveau="0"
                                                            data-id="<?php echo $groupe['id'] ?>"
                                                            class="link-info text-start d-flex align-items-center d-block fs-9 btn p-1">
                                                            <span class="uil-pen fs-9 me-1"></span> Modifier
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (checkPermis($db, 'update', 2)): ?>
                                                    <div class="dropdown-item border-top border-light p-1">
                                                        <a onclick="updateState(<?php echo $groupe['id']; ?>, '<?php echo $groupe['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $groupe['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce groupe ?', 'groupes')"
                                                            class="link-warning text-start d-flex align-items-center d-block fs-9 btn p-1">
                                                            <span
                                                                class="uil-<?php echo $groupe['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-9 me-1"></span>
                                                            <?php echo $groupe['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if (checkPermis($db, 'delete')): ?>
                                                    <div class="dropdown-item border-top border-light p-1">
                                                        <a onclick="deleteData(<?php echo $groupe['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce groupe ?', 'groupes')"
                                                            class="link-danger text-start d-flex align-items-center d-block fs-9 btn p-1">
                                                            <span class="uil-trash-alt fs-9 me-1"></span> Supprimer
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
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