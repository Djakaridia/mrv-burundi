<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Unités de mesure | MRV - Burundi</title>
  <?php
  include './components/navbar & footer/head.php';

  $unite = new Unite($db);
  $unites = $unite->read();

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
            <h4 class="my-1 fw-black">Liste des unités</h4>
          </div>
          <div class="ms-lg-2">
            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUniteModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter une unité</button>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div
            class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
            <div class="table-responsive mx-n1 px-1 scrollbar">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-secondary-subtle">
                  <tr>
                    <th class="sort align-middle" scope="col"> Sigle</th>
                    <th class="sort align-middle" scope="col"> Description</th>
                    <th class="sort align-middle" scope="col" style="min-width:100px;"> Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($unites as $unite) { ?>
                    <tr
                      class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle customer white-space-nowrap"> <?= $unite['name'] ?> </td>
                      <td class="align-middle product white-space-nowrap"> <?= $unite['description'] ?> </td>
                      <td class="align-middle review">
                        <div class="position-relative">
                          <?php if (checkPermis($db, 'update')) : ?>
                            <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addUniteModal" data-id="<?= $unite['id'] ?>"
                              class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                              <span class="uil-pen fs-8"></span>
                            </button>
                          <?php endif; ?>

                          <?php if (checkPermis($db, 'delete')) : ?>
                            <button title="Supprimer" onclick="deleteData(<?php echo $unite['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette unité ?', 'unites')" type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                              <span class="uil-trash-alt fs-8"></span>
                            </button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
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

</html>