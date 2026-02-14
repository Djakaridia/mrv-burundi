<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Rôles & Permissions | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $role = new Role($db);
  $row_roles = $role->read();
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
            <h4 class="my-1 fw-black fs-8">Liste des rôles</h4>
          </div>
          <div class="ms-lg-2">
            <?php if (checkPermis($db, 'update', 1)) : ?>
              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addRoleModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un rôle</button>
            <?php endif; ?>
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
                    <th class="align-middle" scope="col" data-sort="name">Nom</th>
                    <th class="align-middle" scope="col" data-sort="date">Date d'ajout</th>
                    <th class="align-middle" scope="col">Niveau d'accès</th>
                    <?php if (checkPermis($db, 'update', 1)) : ?>
                      <th class="align-middle" scope="col">Permissions</th>
                    <?php endif; ?>
                    <th class="align-middle" scope="col" style="min-width:100px;">Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($row_roles as $role): ?>
                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle px-2">
                        <?php echo $role['name']; ?>
                      </td>
                      <td class="align-middle px-2">
                        <?php echo date('d/m/Y', strtotime($role['created_at'])); ?>
                      </td>
                      <td class="align-middle px-2">
                        <?php
                        if ($role['niveau'] == 1) echo 'Administateur';
                        elseif ($role['niveau'] == 2) echo 'Editeur';
                        elseif ($role['niveau'] == 3) echo 'Visiteur';
                        else echo 'Inconnu';
                        ?>
                      </td>
                      <?php if (checkPermis($db, 'update', 1)) : ?>
                        <td class="align-middle px-2">
                          <?php if ($role['niveau'] < 3) : ?>
                            <button title="Modifier" type="button" class="btn btn-subtle-primary rounded-pill btn-sm fw-bold fs-9 px-2 py-1" data-bs-toggle="modal"
                              data-bs-target="#newPermisModal" data-id="<?php echo $role['id']; ?>" aria-haspopup="true" aria-expanded="false">Modifier
                            </button>
                          <?php endif; ?>
                        </td>
                      <?php endif; ?>
                      <td class="align-middle review px-2">
                        <div class="position-relative">
                          <?php if (checkPermis($db, 'update', 1)) : ?>
                            <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                              data-bs-target="#addRoleModal" data-id="<?php echo $role['id']; ?>">
                              <span class="uil-pen fs-8"></span>
                            </button>
                          <?php endif; ?>

                          <?php if (checkPermis($db, 'delete', 1)) : ?>
                            <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                              onclick="deleteData(<?php echo $role['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce rôle ?', 'roles')">
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


    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>