<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Notifications | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $nt = (!isset($_GET['nt']) || !in_array($_GET['nt'], ['inbox', 'project', 'group', 'indicator', 'support', 'starred', 'archive'])) ? 'inbox' : $_GET['nt'];

  $notification = new Notification($db);
  $notification->user_id = $_SESSION['user-data']['user-id'];
  $notifications = $notification->readByUser();
  $notification_tab = array_filter($notifications, function ($notif) use ($nt) {
    return $notif['entity_type'] === $nt && $notif['is_archived'] === false;
  });

  if ($nt === 'inbox') {
    $notifications =  array_filter($notifications, function ($notif) {
      return $notif['is_archived'] === false;
    });
    $notification_tab = $notifications;
  }
  if ($nt === 'starred') {
    $notification_tab = array_filter($notifications, function ($notif) {
      return $notif['is_starred'] === true;
    });
  }
  if ($nt === 'archive') {
    $notification_tab = array_filter($notifications, function ($notif) {
      return $notif['is_archived'] === true;
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
      <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
        <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center">
          <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">Liste des notifications</h4>
          </div>

          <div class="d-lg-flex flex-row">
            <div class="search-box my-lg-0 my-2">
              <form class="position-relative">
                <input
                  id="searchNotification"
                  class="form-control form-control-sm search-input search"
                  type="search"
                  placeholder="Rechercher une notification"
                  aria-label="Search" />
                <span class="fas fa-search search-box-icon"></span>
              </form>
            </div>

            <!-- <div class="ms-lg-2">
              <button class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addNotificationModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter une notification</button>
            </div> -->
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="mx-n6 p-2 bg-body-emphasis border-y">
            <div class="row g-3 mb-3">
              <div class="col-lg-3 col-12">
                <div class="simplebar-mask py-2">
                  <div class="simplebar-offset" style="right: 0px; bottom: 0px;">
                    <div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: 100%; overflow: hidden scroll;">
                      <div class="simplebar-content" style="padding: 0px;">
                        <div class="d-flex justify-content-between align-items-center">
                          <p class="text-uppercase fs-10 text-body-tertiary text-opacity-85 mb-2 fw-bold">Boite de notifications</p>
                        </div>

                        <ul class="nav flex-column border-top border-translucent rounded-end-1 fs-9 vertical-nav mb-3 list-group">
                          <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=inbox">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-inbox"></span>
                                <span class="flex-1 <?php echo $nt === 'inbox' ? 'fw-bold' : '' ?>">Boite de réception</span>
                                <span class="nav-item-count"> <?php echo count($notifications) > 0 ? count($notifications) : '' ?></span>
                              </div>
                            </a>
                          </li>
                          <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=group">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-users-alt"></span>
                                <span class="flex-1 <?php echo $nt === 'group' ? 'fw-bold' : '' ?>">Groupes</span>
                                <span class="nav-item-count">
                                  <?php
                                  $notify_group = array_filter($notifications, function ($notif) {
                                    return $notif['entity_type'] === 'group' && $notif['is_read'] === false;
                                  });
                                  echo count($notify_group) > 0 ? count($notify_group) : '';
                                  ?>
                                </span>
                              </div>
                            </a>
                          </li>
                          <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=project">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-briefcase"></span>
                                <span class="flex-1 <?php echo $nt === 'project' ? 'fw-bold' : '' ?>">Projets</span>
                                <span class="nav-item-count">
                                  <?php
                                  $notify_project = array_filter($notifications, function ($notif) {
                                    return $notif['entity_type'] === 'project' && $notif['is_read'] === false;
                                  });
                                  echo count($notify_project) > 0 ? count($notify_project) : '';
                                  ?>
                                </span>
                              </div>
                            </a>
                          </li>
                          <!-- <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=indicator">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-graph-bar"></span>
                                <span class="flex-1 <?php echo $nt === 'indicator' ? 'fw-bold' : '' ?>">Indicateurs</span>
                                <span class="nav-item-count">
                                  <?php
                                  $notify_indicator = array_filter($notifications, function ($notif) {
                                    return $notif['entity_type'] === 'indicator' && $notif['is_read'] === false;
                                  });
                                  echo count($notify_indicator) > 0 ? count($notify_indicator) : '';
                                  ?>
                                </span>
                              </div>
                            </a>
                          </li> -->

                          <!-- <?php if ($_SESSION['user-data']['user-role'] === 1) { ?>
                            <li class="nav-item list-group-item-action">
                              <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=support">
                                <div class="d-flex align-items-center">
                                  <span class="me-2 nav-icons uil uil-exclamation-circle"></span>
                                  <span class="flex-1 <?php echo $nt === 'support' ? 'fw-bold' : '' ?>">Support</span>
                                  <span class="nav-item-count">
                                    <?php
                                    $notify_support = array_filter($notifications, function ($notif) {
                                      return $notif['entity_type'] === 'support' && $notif['is_read'] === false;
                                    });
                                    echo count($notify_support) > 0 ? count($notify_support) : '';
                                    ?>
                                  </span>
                                </div>
                              </a>
                            </li>
                          <?php } ?> -->
                        </ul>

                        <div class="d-flex justify-content-between">
                          <p class="text-uppercase fs-10 text-body-tertiary text-opacity-85 mb-2 fw-bold">Marqués</p>
                        </div>

                        <ul class="nav flex-column border-top border-translucent rounded-end-1 fs-9 vertical-nav mb-3 list-group">
                          <!-- <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none active" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=sent">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-location-arrow"></span>
                                <span class="flex-1 <?php echo $nt === 'sent' ? 'fw-bold' : '' ?>">Envoyés</span>
                              </div>
                            </a>
                          </li> -->
                          <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=starred">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-star"></span>
                                <span class="flex-1 <?php echo $nt === 'starred' ? 'fw-bold' : '' ?>">Importants</span>
                              </div>
                            </a>
                          </li>
                          <li class="nav-item list-group-item-action">
                            <a class="nav-link link-body py-2 ps-1 pe-2 border-end border-bottom border-translucent rounded-end-1 text-start outline-none" aria-current="page" href="<?php echo $_SERVER['PHP_SELF'] ?>?nt=archive">
                              <div class="d-flex align-items-center">
                                <span class="me-2 nav-icons uil uil-archive"></span>
                                <span class="flex-1 <?php echo $nt === 'archive' ? 'fw-bold' : '' ?>">Archives</span>
                              </div>
                            </a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-9 col-12">
                <div class="card border shadow-sm rounded-1">
                  <div class="card-header p-2 bg-primary rounded-top-1">
                    <h5 class="card-title fs-9 mb-0 text-white fw-semibold">Notifications</h5>
                  </div>
                  <div class="card-body p-0 overflow-auto" style="min-height: 440px; max-height: 540px;">
                    <?php if (count($notification_tab) === 0) : ?>
                      <div class="text-center py-5">
                        <div class="d-flex justify-content-center mb-3 mt-5">
                          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                        <h4 class="mb-2 mb-3">Aucune notification</h4>
                        <p class="text-body-tertiary">Vous n'avez pas de notifications.</p>
                      </div>
                    <?php else : ?>
                      <?php foreach ($notification_tab as $notif) : ?>
                        <div id="notification-<?php echo $notif['id'] ?>" onclick="markNotificationAsRead(<?php echo $notif['id'] ?>)" class="p-2 notification-card border-bottom <?php echo $notif['is_read'] === true ? 'read' : 'unread' ?>">
                          <div class="d-flex align-items-start">
                            <div class="avatar avatar-xl me-3">
                              <span class="<?php echo getNotifyIcon($notif['type']) ?> fs-4 rounded-1 p-2" style="width: 35px; height: 35px;"></span>
                            </div>
                            <div class="flex-1">
                              <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-column">
                                  <h4 id="notification-title-<?php echo $notif['id'] ?>" class="fs-8 text-body-emphasis <?php echo $notif['is_read'] === true ? 'fw-normal' : 'fw-bold' ?>"><?php echo $notif['titre'] ?></h4>
                                  <p class="text-body-secondary fs-10 mb-0">
                                    <span class="fas fa-calendar text-primary"></span>
                                    <span class="text-body-tertiary text-opacity-85 me-3"><?php echo date('d/m/Y', strtotime($notif['created_at'])) ?></span>
                                    <span class="fas fa-clock text-primary"></span>
                                    <span class="fw-semibold me-3"><?php echo date('H:i', strtotime($notif['created_at'])) ?></span>
                                  </p>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                  <button title="Marquer comme favori" id="btnStar-<?php echo $notif['id'] ?>" onclick="markNotificationAsStarred(<?php echo $notif['id'] ?>, <?php echo $notif['is_starred'] === true ? 'false' : 'true' ?>)"
                                    type="button" class="btn btn-sm btn-icon <?php echo isset($notif['is_starred']) && $notif['is_starred'] === true ? 'btn-warning' : 'btn-phoenix-warning' ?>">
                                    <i class="fas fa-star"></i>
                                  </button>
                                  <button title="Archiver" id="btnArchive-<?php echo $notif['id'] ?>" onclick="markNotificationAsArchived(<?php echo $notif['id'] ?>, <?php echo $notif['is_archived'] === true ? 'false' : 'true' ?>)"
                                    type="button" class="btn btn-sm btn-icon <?php echo isset($notif['is_archived']) && $notif['is_archived'] === true ? 'btn-primary' : 'btn-phoenix-primary' ?>">
                                    <i class="fas fa-archive"></i>
                                  </button>
                                  <button title="Supprimer" onclick="deleteData(<?php echo $notif['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette notification ?', 'notifications')"
                                    type="button" class="btn btn-sm btn-icon btn-phoenix-danger">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </div>
                              </div>
                              <p class="fs-9 text-body-highlight my-1 border-top border-light pt-2"> <span class="text-break"><?php echo $notif['message'] ?></span></p>
                            </div>
                          </div>
                        </div>
                      <?php endforeach ?>
                    <?php endif ?>
                  </div>
                </div>
              </div>
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