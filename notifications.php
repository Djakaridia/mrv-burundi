<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notifications | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $nt = (!isset($_GET['nt']) || !in_array($_GET['nt'], ['inbox', 'project', 'group', 'indicator', 'support', 'starred', 'archive'])) ? 'inbox' : $_GET['nt'];

  $notification = new Notification($db);
  $notification->user_id = $_SESSION['user-data']['user-id'];
  $notifications = $notification->readByUser();

  $filtered_notifications = array_filter($notifications, function ($notif) use ($nt) {
    switch ($nt) {
      case 'inbox':
        return $notif['is_archived'] == 0;
      case 'starred':
        return $notif['is_starred'] == 1;
      case 'archive':
        return $notif['is_archived'] == 1;
      default:
        return $notif['entity_type'] === $nt && $notif['is_archived'] == 0;
    }
  });

  $filtered_notifications = array_map(function ($notif) {
    switch ($notif['entity_type']) {
      case 'project':
        $notif['entity_type_URL'] = ($notif['entity_id'] > 0) ? 'project_view.php?id=' . $notif['entity_id'] : 'projects.php';
        break;
      case 'group':
        $notif['entity_type_URL'] = ($notif['entity_id'] > 0) ? 'group_view.php?id=' . $notif['entity_id'] : 'groups.php';
        break;
      default:
        $notif['entity_type_URL'] = '';
        break;
    }
    return $notif;
  }, $filtered_notifications);

  $unread_counts = [
    'inbox' => count(array_filter($notifications, fn($n) => ($n['is_read'] == 0) && $n['is_archived'] == 0)),
    'group' => count(array_filter($notifications, fn($n) => $n['entity_type'] === 'group' && $n['is_read'] == 0)),
    'project' => count(array_filter($notifications, fn($n) => $n['entity_type'] === 'project' && $n['is_read'] == 0)),
    'starred' => count(array_filter($notifications, fn($n) => $n['is_starred'] == 1)),
    'archive' => count(array_filter($notifications, fn($n) => $n['is_archived'] == 1)),
  ];
  ?>
</head>

<body class="light">
  <main class="main" id="top">
    <?php include './components/navbar & footer/sidebar.php'; ?>
    <?php include './components/navbar & footer/navbar.php'; ?>

    <div class="content">
      <div class="mx-0 mt-n5">
        <div class="row g-3 mx-n5 pb-3">
          <div class="col-12">
            <div class="bg-body-emphasis border-bottom py-3 px-3 mb-3 rounded-1 shadow-sm">
              <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                  <h4 class="fw-black fs-8 mb-1">
                    <i class="fas fa-bell me-2 text-primary"></i>
                    Liste des notifications
                  </h4>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto">
                  <div class="search-box position-relative flex-grow-1">
                    <form class="position-relative">
                      <input
                        id="searchNotification"
                        class="form-control form-control-sm ps-5"
                        type="search"
                        placeholder="Rechercher une notification..."
                        aria-label="Search" />
                      <span class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-primary"></span>
                    </form>
                  </div>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-lg-3">
                <div class="card border shadow-sm rounded-1 sticky-lg-top" style="top: 50px; min-height: 400px;">
                  <div class="card-body py-3 px-0">
                    <div class="d-flex justify-content-between align-items-center px-2 mb-3 border-bottom">
                      <h6 class="text-uppercase fs-10 fw-bold text-primary mb-2">
                        <i class="fas fa-inbox me-2"></i>Boîte de réception
                      </h6>
                      <?php if ($unread_counts['inbox'] > 0): ?>
                        <span class="badge-count"><?= $unread_counts['inbox'] ?></span>
                      <?php endif; ?>
                    </div>

                    <ul class="nav flex-column nav-pills gap-1 px-2 mb-3">
                      <?php
                      $menu_items = [
                        'inbox' => ['icon' => 'inbox', 'label' => 'Boîte principale', 'count' => $unread_counts['inbox']],
                        'group' => ['icon' => 'users-alt', 'label' => 'Groupes', 'count' => $unread_counts['group']],
                        'project' => ['icon' => 'briefcase', 'label' => 'Projets', 'count' => $unread_counts['project']],
                      ];

                      foreach ($menu_items as $key => $item):
                        $active = $nt === $key ? 'active' : '';
                      ?>
                        <li class="nav-item w-100">
                          <a class="nav-link <?= $active ?> d-flex align-items-center py-2 px-3 rounded-2"
                            href="<?= $_SERVER['PHP_SELF'] ?>?nt=<?= $key ?>">
                            <i class="uil uil-<?= $item['icon'] ?> me-2 fs-8"></i>
                            <span class="flex-grow-1 fs-9"><?= $item['label'] ?></span>
                            <?php if ($item['count'] > 0): ?>
                              <span class="badge badge-phoenix badge-phoenix-primary fs-10 rounded-pill"><?= $item['count'] ?></span>
                            <?php endif; ?>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between align-items-center px-2 mb-3 border-bottom">
                      <h6 class="text-uppercase fs-10 fw-bold text-primary mb-2">
                        <i class="fas fa-bookmark me-2"></i>Marqués
                      </h6>
                    </div>

                    <ul class="nav flex-column nav-pills gap-1 px-2 mb-3">
                      <?php
                      $mark_items = [
                        'starred' => ['icon' => 'star', 'label' => 'Importants', 'count' => $unread_counts['starred']],
                        'archive' => ['icon' => 'archive', 'label' => 'Archives', 'count' => $unread_counts['archive']],
                      ];

                      foreach ($mark_items as $key => $item):
                        $active = $nt === $key ? 'active' : '';
                      ?>
                        <li class="nav-item w-100">
                          <a class="nav-link <?= $active ?> d-flex align-items-center py-2 px-3 rounded-2"
                            href="<?= $_SERVER['PHP_SELF'] ?>?nt=<?= $key ?>">
                            <i class="uil uil-<?= $item['icon'] ?> me-2 fs-8"></i>
                            <span class="flex-grow-1 fs-9"><?= $item['label'] ?></span>
                            <?php if ($item['count'] > 0): ?>
                              <span class="badge badge-phoenix badge-phoenix-primary fs-10 rounded-pill"><?= $item['count'] ?></span>
                            <?php endif; ?>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="col-lg-9">
                <div class="card border shadow-sm rounded-1 overflow-hidden" style="min-height: 400px;">
                  <div class="card-header bg-white py-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fs-9 my-3">
                      <i class="fas fa-bell me-2 text-primary"></i>
                      Notifications
                      <?php if (count($filtered_notifications) > 0): ?>
                        <span class="badge bg-primary ms-2"><?= count($filtered_notifications) ?></span>
                      <?php endif; ?>
                    </h5>

                    <?php if (count($filtered_notifications) > 0 && !in_array($nt, ['inbox', 'starred', 'archive'])): ?>
                      <div class="dropdown">
                        <button class="btn btn-sm btn-phoenix-secondary" type="button" data-bs-toggle="dropdown">
                          <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end p-0">
                          <li>
                            <button class="dropdown-item" onclick="markAllNotifications('read', '<?= $nt ?>', '<?= implode(',', array_column($filtered_notifications, 'id')) ?>')">
                              <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                            </button>
                          </li>
                          <li>
                            <button class="dropdown-item" onclick="markAllNotifications('star', '<?= $nt ?>', '<?= implode(',', array_column($filtered_notifications, 'id')) ?>')">
                              <i class="fas fa-star me-2"></i>Tout marquer comme important
                            </button>
                          </li>
                          <li>
                            <button class="dropdown-item" onclick="markAllNotifications('archive', '<?= $nt ?>', '<?= implode(',', array_column($filtered_notifications, 'id')) ?>')">
                              <i class="fas fa-archive me-2"></i>Tout archiver
                            </button>
                          </li>
                          <li>
                            <hr class="dropdown-divider">
                          </li>
                          <li>
                            <button class="dropdown-item text-danger" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer toutes ces notifications ?')) markAllNotifications('delete', '<?= $nt ?>', '<?= implode(',', array_column($filtered_notifications, 'id')) ?>')">
                              <i class="fas fa-trash me-2"></i>Tout supprimer
                            </button>
                          </li>
                        </ul>
                      </div>
                    <?php endif; ?>
                  </div>

                  <div class="card-body p-1">
                    <?php if (count($filtered_notifications) === 0) : ?>
                      <div class="text-center py-5">
                        <div class="mb-3">
                          <div class="bg-light d-inline-flex p-3 rounded-circle">
                            <i class="fas fa-bell-slash fs-1 text-primary opacity-50"></i>
                          </div>
                        </div>
                        <h5 class="mb-2">Aucune notification</h5>
                        <p class="text-body-tertiary mb-3">Vous n'avez pas de notifications dans cette catégorie.</p>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>?nt=inbox" class="btn btn-primary btn-sm">
                          <i class="fas fa-inbox me-2"></i>Voir la boîte de réception
                        </a>
                      </div>
                    <?php else : ?>
                      <table id="id-datatable1" class="table table-hover table-responsive-sm" style="width:100%">
                        <thead class="d-none">
                          <tr>
                            <th>Notification</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($filtered_notifications as $notif) : ?>
                            <tr id="notification-<?= $notif['id'] ?>"
                              onclick="markNotificationAsRead(<?= $notif['id'] ?>)"
                              class="notification-card <?= $notif['is_read'] == 1 ? 'read' : 'unread' ?>"
                              data-id="<?= $notif['id'] ?>"
                              data-read="<?= $notif['is_read'] ?>"
                              data-starred="<?= $notif['is_starred'] ?>"
                              data-archived="<?= $notif['is_archived'] ?>">

                              <td class="align-top" style="width: 70%">
                                <div class="d-flex gap-3 align-items-start">
                                  <div class="flex-shrink-0">
                                    <div class="avatar avatar-md bg-light rounded-circle d-flex align-items-center justify-content-center"
                                      style="width: 40px; height: 40px;">
                                      <i class="<?= getNotifyIcon($notif['type']) ?> fs-6 text-primary"></i>
                                    </div>
                                  </div>

                                  <div class="flex-grow-1">
                                    <h6 class="mb-1 <?= $notif['is_read'] == 1 ? 'fw-semibold' : 'fw-bold' ?>">
                                      <?= html_entity_decode($notif['titre']) ?>
                                      <?php if (isset($notif['entity_type_URL']) && !empty($notif['entity_type_URL'])): ?>
                                        <a href="<?= html_entity_decode($notif['entity_type_URL']) ?>"
                                          onclick="event.stopPropagation()"
                                          class="text-primary ms-2"
                                          target="_blank">
                                          <i class="fas fa-external-link-alt"></i>
                                        </a>
                                      <?php endif; ?>
                                    </h6>
                                    <p class="text-body-secondary small mb-0"><?= html_entity_decode($notif['message']) ?></p>
                                  </div>
                                </div>
                              </td>

                              <td class="align-top text-center text-nowrap" style="width: 10%">
                                <div class="d-flex flex-column align-items-start">
                                  <span class="small">
                                    <i class="far fa-calendar-alt me-1 text-primary"></i>
                                    <?= date('d/m/Y', strtotime($notif['created_at'])) ?>
                                  </span>
                                  <span class="small mt-1">
                                    <i class="far fa-clock me-1 text-primary"></i>
                                    <?= date('H:i', strtotime($notif['created_at'])) ?>
                                  </span>
                                </div>
                              </td>

                              <td class="align-top text-center" style="width: 10%">
                                <?php if ($notif['is_read'] == 0): ?>
                                  <span class="badge badge-phoenix badge-phoenix-primary py-1 px-2 fs-10 rounded-pill">Nouveau</span>
                                <?php else: ?>
                                  <span class="badge badge-phoenix badge-phoenix-secondary py-1 px-2 fs-10 rounded-pill">Lu</span>
                                <?php endif; ?>
                              </td>

                              <td class="align-top text-center" style="width: 10%">
                                <div class="d-flex gap-2 justify-content-end">
                                  <button title="Favori"
                                    id="btnStar-<?= $notif['id'] ?>"
                                    onclick="event.stopPropagation(); markNotificationAsStarred(<?= $notif['id'] ?>, <?= $notif['is_starred'] ? 'false' : 'true' ?>)"
                                    class="btn btn-sm btn-icon <?= $notif['is_starred'] ? 'btn-warning' : 'btn-phoenix-warning' ?>">
                                    <i class="fas fa-star"></i>
                                  </button>

                                  <button title="Archiver"
                                    id="btnArchive-<?= $notif['id'] ?>"
                                    onclick="event.stopPropagation(); markNotificationAsArchived(<?= $notif['id'] ?>, <?= $notif['is_archived'] ? 'false' : 'true' ?>)"
                                    class="btn btn-sm btn-icon <?= $notif['is_archived'] ? 'btn-primary' : 'btn-phoenix-primary' ?>">
                                    <i class="fas fa-archive"></i>
                                  </button>

                                  <button title="Supprimer"
                                    onclick="event.stopPropagation(); deleteData(<?= $notif['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette notification ?', 'notifications')"
                                    class="btn btn-sm btn-icon btn-phoenix-danger">
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          <?php endforeach ?>
                        </tbody>
                      </table>
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

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

<script>

</script>

</html>