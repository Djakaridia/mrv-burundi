<?php
$notification = new Notification($db);
$notification->user_id = $_SESSION['user-data']['user-id'];
$notifications = $notification->readByUser();
$notification_unread = array_filter($notifications, function ($notif) {
    return $notif['is_read'] === false;
});

// Projets
$nav_projet = new Projet($db);
$nav_projets = $nav_projet->read();
$nav_projets = array_filter($nav_projets, function ($projet) {
    return $projet['state'] == 'actif';
});

// Groupes de travail
$nav_groupe = new GroupeTravail($db);
$nav_groupes = $nav_groupe->read();
$nav_groupes = array_filter($nav_groupes, function ($groupe) {
    return $groupe['state'] == 'actif';
});

// Rapports
$nav_rapport = new RapportPeriode($db);
$nav_rapports = $nav_rapport->read();
$nav_rapports = array_filter($nav_rapports, function ($rapport) {
    return $rapport['state'] == 'actif';
});

// Documents
$nav_document = new Documents($db);
$nav_documents = $nav_document->read();
$nav_documents = array_filter($nav_documents, function ($document) {
    return $document['state'] == 'actif';
});

// Count resultats
$nav_resultats = count($nav_projets) + count($nav_groupes) + count($nav_rapports) + count($nav_documents);
?>


<nav class="navbar navbar-top fixed-top navbar-expand shadow-sm px-0 dark__bg-dark" id="navbarDefault"
    style="border-bottom: 1px solid #00b500; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);">
    <div class="collapse navbar-collapse justify-content-between">
        <!-- <div class="d-flex align-items-center"> -->
        <div class="navbar-logo border-0 d-flex justify-content-center">
            <button class="btn navbar-toggler navbar-toggler-humburger-icon hover-bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false" aria-label="Toggle Navigation">
                <span class="navbar-toggle-icon"><span class="toggle-line"></span></span>
            </button>

            <a class="navbar-brand mx-5" href="accueil.php">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                        <img src="assets/images/logo-full.png" class="img-fluid d-dark-none" alt="MRV Burundi" style="height: 50px;" />
                        <img src="assets/images/logo-full.png" class="img-fluid d-light-none" alt="MRV Burundi" style="height: 50px;" />
                    </div>
                </div>
            </a>
        </div>

        <div class="search-box navbar-top-search-box d-none d-lg-block" data-list='{"valueNames":["title"]}' style="width:25rem;">
            <form id="searchForm" class="position-relative" data-bs-toggle="search" data-bs-display="static">
                <input id="searchInput" class="form-control rounded-pill form-control-sm search-input bg-primary-subtle" type="search" placeholder="Recherche..." aria-label="Recherche" />
                <span class="fas fa-search search-box-icon"></span>
            </form>
            <div onclick="document.getElementById('searchForm').reset();" class="btn-close position-absolute end-0 top-50 translate-middle cursor-pointer shadow-none" data-bs-dismiss="Recherche">
                <button class="btn btn-link p-0" aria-label="Fermer"></button>
            </div>

            <div class="dropdown-menu border start-0 py-0 overflow-hidden w-100">
                <div class="scrollbar" style="max-height: 25rem; overflow-y: auto;">
                    <div class="list">
                        <h6 class="dropdown-header text-body-highlight fs-10 py-2"><?php echo $nav_resultats; ?> <span class="text-body-quaternary">résultats</span></h6>

                        <hr class="my-0" />
                        <h6 class="dropdown-header text-body-highlight fs-9 bg-light border-bottom border-translucent py-2 lh-sm">Projets</h6>
                        <div class="py-2">
                            <?php foreach ($nav_projets as $projet) { ?>
                                <a class="dropdown-item py-2 d-flex align-items-center navbar-item" href="project_view.php?id=<?php echo $projet['id']; ?>">
                                    <div class="file-thumbnail"> <span class="fas fa-briefcase text-primary fs-9"></span></div>
                                    <div class="flex-1">
                                        <h6 class="mb-0 text-primary title"><?php echo $projet['name']; ?></h6>
                                        <p class="fs-10 mb-0 d-flex text-body-tertiary">
                                            <span class="fw-medium text-body-tertiary text-opactity-85">
                                                Ajouté le: <?php echo date('d/m/Y', strtotime($projet['created_at'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>

                        <hr class="my-0" />
                        <h6 class="dropdown-header text-body-highlight fs-9 bg-light border-bottom border-translucent py-2 lh-sm">Groupes de travail</h6>
                        <div class="py-2">
                            <?php foreach ($nav_groupes as $groupe) { ?>
                                <a class="dropdown-item py-2 d-flex align-items-center navbar-item" href="group_view.php?id=<?php echo $groupe['id']; ?>">
                                    <div class="file-thumbnail"> <span class="fas fa-users text-primary fs-9"></span></div>
                                    <div class="flex-1">
                                        <h6 class="mb-0 text-primary title"><?php echo $groupe['name']; ?></h6>
                                        <p class="fs-10 mb-0 d-flex text-body-tertiary">
                                            <span class="fw-medium text-body-tertiary text-opactity-85">
                                                Ajouté le: <?php echo date('d/m/Y', strtotime($groupe['created_at'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>

                        <hr class="my-0" />
                        <h6 class="dropdown-header text-body-highlight fs-9 bg-light border-bottom border-translucent py-2 lh-sm">Rapports</h6>
                        <div class="py-2">
                            <?php foreach ($nav_rapports as $rapport) { ?>
                                <a class="dropdown-item py-2 d-flex align-items-center navbar-item" href="rperiode_view.php?id=<?php echo $rapport['id']; ?>">
                                    <div class="file-thumbnail"> <span class="fas fa-pie-chart text-primary fs-9"></span></div>
                                    <div class="flex-1">
                                        <h6 class="mb-0 text-primary title"><?php echo $rapport['intitule']; ?></h6>
                                        <p class="fs-10 mb-0 d-flex text-body-tertiary">
                                            <span class="fw-medium text-body-tertiary text-opactity-85">
                                                Ajouté le: <?php echo date('d/m/Y', strtotime($rapport['created_at'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>

                        <hr class="my-0" />
                        <h6 class="dropdown-header text-body-highlight fs-9 bg-light border-bottom border-translucent py-2 lh-sm">Fichiers</h6>
                        <div class="py-2">
                            <?php foreach ($nav_documents as $document) { ?>
                                <a class="dropdown-item py-2 d-flex align-items-center navbar-item" href="dossier_view.php?id=<?php echo $document['dossier_id']; ?>">
                                    <div class="file-thumbnail"><span class="fa-solid fa-folder text-primary fs-9"></span></div>
                                    <div class="flex-1">
                                        <h6 class="mb-0 text-primary title"><?php echo $document['name']; ?></h6>
                                        <p class="fs-10 mb-0 d-flex text-body-tertiary">
                                            <span class="fw-medium text-body-tertiary text-opactity-85">
                                                Ajouté le: <?php echo date('d/m/Y', strtotime($document['created_at'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="fallback fw-bold fs-7 d-none">Aucun résultat trouvé.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- </div> -->

        <ul class="navbar-nav navbar-nav-icons flex-row mx-3">
            <li class="nav-item">
                <div class="theme-control-toggle fa-icon-wait px-2">
                    <input class="form-check-input ms-0 theme-control-toggle-input" type="checkbox" data-theme-control="phoenixTheme" value="dark" id="switchModeToggle" />
                    <label class="mb-0 theme-control-toggle-label theme-control-toggle-light" for="switchModeToggle" id="label-mode-dark" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Changer de thème" style="height:32px;width:32px;"><span class="icon" data-feather="moon"></span></label>
                    <label class="mb-0 theme-control-toggle-label theme-control-toggle-dark" for="switchModeToggle" id="label-mode-light" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Changer de thème" style="height:32px;width:32px;"><span class="icon" data-feather="sun"></span></label>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link" href="#" style="min-width: 2.25rem" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-auto-close="outside">
                    <span class="d-block position-relative" style="height:20px;width:20px;">
                        <span data-feather="bell" style="height:20px;width:20px;"></span>
                        <?php if (count($notification_unread) > 0) { ?>
                            <span class="badge bg-danger rounded-circle p-1 position-absolute top-0 start-100 translate-middle">
                                <?php echo count($notification_unread); ?>
                            </span>
                        <?php } ?>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end notification-dropdown-menu py-0 shadow border navbar-dropdown-caret" id="navbarDropdownNotfication" aria-labelledby="navbarDropdownNotfication">
                    <div class="card position-relative border-0">
                        <div class="card-header p-2">
                            <div class="d-flex justify-content-between">
                                <h5 class="text-body-emphasis mb-0">Notifications</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div style="height: 25rem;">
                                <?php if (count($notification_unread) === 0) { ?>
                                    <div class="d-flex flex-column align-items-center text-center justify-content-center py-5">
                                        <h4 class="text-body-emphasis mt-5 mb-2">Aucune notification</h4>
                                        <p class="text-body-tertiary">Vous n'avez pas de notifications.</p>
                                    </div>
                                <?php } else { ?>
                                    <?php foreach ($notification_unread as $notif) { ?>
                                        <div class="p-2 notification-card position-relative border-bottom unread">
                                            <div class="d-flex align-items-center justify-content-between pe-5">
                                                <div class="d-flex">
                                                    <div class="avatar avatar-xl me-2">
                                                        <span class="<?php echo getNotifyIcon($notif['type']) ?> fs-4 rounded-1 p-2" style="width: 25px; height: 25px;"></span>
                                                    </div>
                                                    <div class="flex-1">
                                                        <h4 class="fs-9 text-body-emphasis"><?php echo $notif['titre'] ?></h4>
                                                        <p class="text-body-secondary fs-10 mb-0">
                                                            <span class="fas fa-calendar"></span>
                                                            <span class="text-body-tertiary text-opacity-85"><?php echo date('d/m/Y', strtotime($notif['created_at'])) ?></span>
                                                            <span class="mx-2">|</span>
                                                            <span class="fas fa-clock"></span>
                                                            <span class="fw-semibold"><?php echo date('H:i', strtotime($notif['created_at'])) ?></span>
                                                        </p>
                                                        <p class="fs-9 text-body-highlight mb-0 fw-normal text-break text-truncate" style="max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; "><?php echo $notif['message'] ?></p>
                                                    </div>
                                                </div>

                                                <div class="dropdown notification-dropdown position-absolute top-0 end-0">
                                                    <button class="btn btn-sm  dropdown-toggle dropdown-caret-none transition-none" type="button" data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                                        <span class="fas fa-ellipsis-v fs-8 text-body"></span>
                                                    </button>
                                                    <div class="dropdown-menu py-2">
                                                        <a class="dropdown-item" href="#!" onclick="markNotificationAsRead(<?php echo $notif['id'] ?>)"><?php echo $notif['is_read'] === true ? 'Marquer comme non lu' : 'Marquer comme lu' ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                <?php }
                                } ?>
                            </div>
                        </div>
                        <div class="card-footer p-0 border-top border-translucent border-0">
                            <div class="my-2 text-center fw-bold fs-10 text-body-tertiary text-opactity-85"><a class="fw-bolder" href="notifications.php">Historique des notifications</a></div>
                        </div>
                    </div>
                </div>
            </li>

            <li class="nav-item d-flex align-items-center gap-3 px-3">
                <img src="./assets/images/OBPE.png" alt="logo" height="40" width="40" class="rounded-circle">
                <img src="./assets/images/Blason_du_Burundi.png" alt="armoirie" height="40" width="40" class="rounded-circle">
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link lh-1 px-0" id="navbarDropdownUser" href="#!" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-l ">
                        <img class="rounded-circle border border-light shadow-sm" src="assets/img/team/avatar.webp" alt="" />
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown-caret py-0 dropdown-profile shadow border" aria-labelledby="navbarDropdownUser">
                    <div class="card position-relative border-0">
                        <div class="card-body p-0">
                            <div class="text-center pt-4 pb-3">
                                <div class="avatar avatar-xl ">
                                    <img class="rounded-circle border border-light" src="assets/img/team/avatar.webp" alt="" />
                                </div>
                                <h6 class="mt-2 text-body-emphasis"><?php echo $_SESSION['user-data']['user-nom'] . ' ' . $_SESSION['user-data']['user-prenom']; ?></h6>
                            </div>
                        </div>
                        <div class="border-top border-translucent overflow-auto scrollbar">
                            <ul class="nav d-flex flex-column my-2">
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="./profil.php"> <span class="me-2 align-bottom" data-feather="user"></span><span>Profil & Tableau de bord</span></a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="./faq.php"> <span class="me-2 align-bottom" data-feather="help-circle"></span>Centre d'aide</a></li>
                                <li class="nav-item"><a class="nav-link px-3 d-block" href="./contact.php"> <span class="me-2 align-bottom" data-feather="mail"></span>Contactez-nous</a></li>
                            </ul>
                        </div>
                        <div class="card-footer p-0 border-top border-translucent">
                            <div class="p-2"> <a id="btn-logout" class="btn btn-subtle-primary d-flex flex-center w-100" href="logout.php"> <span class="me-2" data-feather="log-out"> </span>Déconnexion</a></div>
                            <div class="my-2 text-center fw-bold fs-10 text-body-quaternary">
                                <a class="text-body-quaternary me-1" href="./privacy-policy.php" target="_blank">Confidentialité</a>&bull;
                                <a class="text-body-quaternary mx-1" href="./securite-policy.php" target="_blank">Sécurité</a>&bull;
                                <a class="text-body-quaternary ms-1 cursor-pointer" data-bs-toggle="modal" data-bs-target="#cookieModal">Cookies</a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>