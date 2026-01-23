<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Bailleurs | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $structure = new Structure($db);
  $structures = $structure->read();

  $convention = new Convention($db);
  $conventions = $convention->read();

  $projet = new Projet($db);
  $projets = $projet->read();
  $projets = array_filter($projets, function ($projet) {return $projet['state'] == 'actif';});
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
            <h4 class="my-1 fw-black">Liste des conventions</h4>
          </div>

          <div class="ms-lg-2">
            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
              data-bs-target="#addConvenModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter une convention</button>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
            <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-primary-subtle">
                  <tr>
                    <th class="sort align-middle" scope="col" data-sort="product">Logo</th>
                    <th class="sort align-middle" scope="col" data-sort="product">Code</th>
                    <th class="sort align-middle" scope="col" data-sort="customer" style="min-width:200px;">Nom</th>
                    <th class="sort align-middle" scope="col" data-sort="rating">Sigle</th>
                    <th class="sort align-middle" scope="col" data-sort="review">Acteur</th>
                    <th class="sort align-middle" scope="col" data-sort="review">Projet</th>
                    <th class="sort align-middle" scope="col" data-sort="rating" style="min-width:110px;">Montant</th>
                    <th class="sort align-middle" scope="col" data-sort="rating" style="min-width:110px;">Date d'acord
                    </th>
                    <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($conventions as $convention) { ?>
                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle product py-0">
                        <span class="d-block rounded-2 border border-translucent text-center text-primary">
                          <?php foreach ($structures as $structure) { ?>
                            <?php if ($structure['id'] == $convention['structure_id']) { ?>
                              <?php if ($structure['logo']) { ?>
                                <img src="<?php echo $structure['logo']; ?>" alt="Logo" width="45" />
                              <?php } else { ?>
                                <i class="fas fa-users fs-8 p-2"></i>
                              <?php } ?>
                            <?php } ?>
                          <?php } ?>
                        </span>
                      </td>
                      <td class="align-middle product"><?php echo $convention['code']; ?></td>
                      <td class="align-middle customer"><?php echo $convention['name']; ?></td>
                      <td class="align-middle rating"><?php echo $convention['montant']; ?></td>
                      <td class="align-middle review">
                        <?php foreach ($structures as $structure) { ?>
                          <?php if ($structure['id'] == $convention['structure_id']) { ?>
                            <?php echo $structure['sigle']; ?>
                          <?php } ?>
                        <?php } ?>
                      </td>
                      <td class="align-middle review">
                        <?php foreach ($projets as $projet) { ?>
                          <?php if ($projet['id'] == $convention['projet_id']) { ?>
                            <?php echo $projet['name']; ?>
                          <?php } ?>
                        <?php } ?>
                      </td>
                      <td class="align-middle rating" style="min-width:200px;">
                        <span class="badge bg-primary p-2 fs-10"><?php echo number_format($convention['montant'], 0, 0); ?></span>
                      </td>
                      <td class="align-middle date">
                        <?php echo date('Y-m-d', strtotime($convention['date_accord'])); ?>
                      </td>
                      <td class="align-middle">
                        <div class="position-relative">
                          <?php if (checkPermis($db, 'update')) : ?>
                          <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                            data-bs-target="#addConvenModal" data-id="<?php echo $convention['id']; ?>">
                            <span class="uil-pen fs-8"></span>
                          </button>
                          <?php endif; ?>

                          <?php if (checkPermis($db, 'delete')) : ?>
                          <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                            onclick="deleteData(<?php echo $convention['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cette convention ?', 'conventions')">
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
</body>

</html>