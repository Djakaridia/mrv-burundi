<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Acteurs | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $partenaire = new Partenaire($db);
  $partenaires = $partenaire->read();
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
          <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0 border-start-0">
            <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
              <div class="col-auto">
                <h4 class="my-1 fw-black fs-8">Liste des partenaires</h4>
              </div>

              <button title="Ajouter un partenaire" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addPartenaireModal" aria-haspopup="true" aria-expanded="false"
                data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un partenaire</button>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                  <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                    <thead class="bg-primary-subtle">
                      <tr>
                        <th class="sort align-middle" scope="col" data-sort="product">Code</th>
                        <th class="sort align-middle" scope="col" data-sort="rating">Sigle</th>
                        <th class="sort align-middle" scope="col" data-sort="rating">Email</th>
                        <th class="sort align-middle" scope="col" data-sort="review">Périmètre</th>
                        <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="list">
                      <?php foreach ($partenaires as $partenaire) {
                        $logoStruc = explode("../", $partenaire['logo'] ?? ''); ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                          <td class="align-middle"><?php echo $partenaire['code']; ?></td>
                          <td class="align-middle"><?php echo $partenaire['description']? $partenaire['description'].' ('.$partenaire['sigle'].')': $partenaire['sigle']; ?></td>
                          <td class="align-middle"><?php echo $partenaire['email']; ?></td>
                          <td class="align-middle text-capitalize"><?php echo $partenaire['perimetre']; ?></td>
                          <td class="align-middle">
                            <div class="position-relative d-flex gap-1">
                              <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                  data-bs-target="#addPartenaireModal" data-id="<?php echo $partenaire['id']; ?>">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete', 2)) : ?>
                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                  onclick="deleteData(<?php echo $partenaire['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cet partenaire ?', 'partenaires')">
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
    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>