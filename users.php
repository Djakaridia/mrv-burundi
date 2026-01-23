<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Utilisateur | MRV - Burundi</title>
  <?php
  include './components/navbar & footer/head.php';

  $user = new User($db);
  $users = $user->read();

  $role = new Role($db);
  $roles = $role->read();

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

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
          <div class="col-auto">
            <h4 class="my-1 fw-black">Liste des utilisateurs</h4>
          </div>
          <div class="ms-lg-2">
            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
              data-bs-target="#addUserModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter utilisateur</button>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
            <div class="table-responsive scrollbar" style="min-height: 432px;">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-primary-subtle">
                  <tr>
                    <th class="sort align-middle" scope="col" data-sort="name"> Nom & Prenoms</th>
                    <th class="sort align-middle" scope="col" data-sort="email"> Email</th>
                    <th class="sort align-middle" scope="col" data-sort="contact"> Contact</th>
                    <th class="sort align-middle" scope="col" data-sort="role"> Role</th>
                    <th class="sort align-middle" scope="col" data-sort="structure"> Acteur</th>
                    <th class="sort align-middle" scope="col" data-sort="fonction"> Fonction</th>
                    <th class="sort align-middle" scope="col" data-sort="status"> Status</th>
                    <th class="sort align-middle" scope="col" data-sort="time" style="min-width:100px;"> Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($users as $user): ?>
                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle customer px-2">
                        <div class="d-flex align-items-center text-body gap-2">
                          <div class="avatar avatar-l">
                            <img class="rounded-circle border border-light" src="assets/img/team/avatar.webp" alt="" />
                          </div>
                          <span class="text-body text-capitalize"><?php echo $user['nom'] . ' ' . $user['prenom']; ?></span>
                        </div>
                      </td>
                      <td class="align-middle product px-2"><?php echo $user['email']; ?></td>
                      <td class="align-middle product px-2"><?php echo $user['phone']; ?></td>
                      <td class="align-middle product px-2 py-0">
                        <?php foreach ($roles as $role): ?>
                          <?php if ($user['role_id'] == $role['id']): ?>
                            <?php echo $role['name']; ?>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </td>
                      <td class="align-middle product px-2 py-0">
                        <?php foreach ($structures as $structure): ?>
                          <?php if ($user['structure_id'] == $structure['id']): ?>
                            <?php echo $structure['sigle']; ?>
                          <?php endif; ?>
                        <?php endforeach; ?>
                      </td>
                      <td class="align-middle product px-2 py-0">
                        <?php echo $user['fonction'] == 'point_focal' ? 'Point Focal' : 'Simple'; ?>
                      </td>
                      <td class="align-middle text-start status">
                        <span class="badge rounded-pill badge-phoenix fs-10 badge-phoenix-<?php echo $user['state'] == 'actif' ? 'success' : 'danger'; ?>">
                          <span class="badge-label"><?php echo $user['state'] == 'actif' ? 'Actif' : 'Inactif'; ?></span>
                        </span>
                      </td>

                      <td class="align-middle review">
                        <div class="position-relative d-flex gap-1">
                          <?php if ((checkPermis($db, 'update', 2) && $user['username'] != 'admin') || ($user['id'] == $_SESSION['user-data']['user-id'])) : ?>
                            <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                              data-bs-target="#addUserModal" data-id="<?php echo $user['id']; ?>">
                              <span class="uil-pen fs-8"></span>
                            </button>
                          <?php endif; ?>

                          <?php if ((checkPermis($db, 'update', 2) && $user['username'] != 'admin') || ($user['id'] == $_SESSION['user-data']['user-id'])) : ?>
                            <button title="Activer/Désactiver" onclick="updateState(<?php echo $user['id']; ?>, '<?php echo $user['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $user['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cet acteur ?', 'users')"
                              type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                              <span class="uil-<?php echo $user['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                            </button>
                          <?php endif; ?>

                          <?php if ((checkPermis($db, 'delete', 2) && $user['username'] != 'admin') || ($user['id'] == $_SESSION['user-data']['user-id'])) : ?>
                            <button title="Supprimer" onclick="deleteData(<?php echo $user['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet utilisateur ?', 'users')"
                              type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                              <span class="uil-trash-alt fs-8"></span>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>

                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>