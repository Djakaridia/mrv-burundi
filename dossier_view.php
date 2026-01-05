<!DOCTYPE html>
<html
  lang="fr"
  dir="ltr"
  data-navigation-type="default"
  data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Documents | MRV - Burundi</title>

  <?php
  $dossierId = $_GET['id'];
  if (!isset($dossierId) || !is_numeric($dossierId)) {
    header("Location: documents.php");
    exit;
  }

  include './components/navbar & footer/head.php';

  $dossier = new Dossier($db);
  $dossiers = $dossier->read();

  $user = new User($db);
  $users = $user->read();

  $dossier->id = $dossierId;
  $dossier_curr = $dossier->readById();

  if (empty($dossier_curr)) {
    echo "<script>window.location.href = 'documents.php';</script>";
    exit;
  }

  $document = new Documents($db);
  $document->dossier_id = $dossier_curr['id'];
  $documents = $document->readByDossier();
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
        <div class="card-body p-2 d-lg-flex flex-row justify-content-between g-3">
          <div class="col-auto">
            <h4 class="my-1 fw-black">
              Liste des documents
              <?= $dossier_curr['name'] ?> - </h4>
          </div>


          <div class="ms-lg-2">
            <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addDocumentModal" data-dossier-id="<?= $dossier_curr['id'] ?>" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter un document</button>
          </div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-12">
          <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
            <div class="table-responsive scrollbar" style="min-height: 432px;">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-secondary-subtle">
                  <tr>
                    <th class="sort align-middle" scope="col">Nom</th>
                    <th class="sort align-middle" scope="col">Date d'ajout</th>
                    <th class="sort align-middle" scope="col">Ajouté par</th>
                    <th class="sort align-middle" scope="col">Taille</th>
                    <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                  <?php foreach ($documents as $document) { ?>
                    <tr
                      class="hover-actions-trigger btn-reveal-trigger position-static">
                      <td class="align-middle px-2">
                        <div class="d-flex align-items-center text-body">
                          <i class="fas fa-file fs-8 me-2"></i><?= $document['name'] ?>
                        </div>
                      </td>
                      <td class="align-middle px-2">
                        <?= date('d/m/Y', strtotime($document['created_at'])) ?>
                      </td>
                      <td class="align-middle px-2">
                        <?php foreach ($users as $user) {
                          if ($user['id'] == $document['add_by']) echo $user['nom'];
                        } ?>
                      </td>
                      <td class="align-middle px-2">
                        <?= round($document['file_size'] / 1024 / 1024, 2) ?> MB
                      </td>
                      <td class="align-middle review">
                        <div class="position-relative">
                          <div class="">
                            <button title="Télécharger" onclick="downloadFiles('MRV', '<?= $document['name'] ?>', '<?= $document['file_path'] ?>')"
                              class="btn btn-sm btn-phoenix-success me-1 fs-10 px-2 py-1">
                              <span class="uil-cloud-download fs-8"></span>
                            </button>
                            <!-- <button type="button" data-bs-toggle="modal" data-bs-target="#addDocumentModal" data-id="<?= $document['id'] ?>"
                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                <span class="uil-pen fs-8"></span>
                              </button> -->

                            <?php if (checkPermis($db, 'delete')) : ?>
                              <button title="Supprimer" onclick="deleteData(<?php echo $document['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce document ?', 'documents')"
                                class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                <span class="uil-trash-alt fs-8"></span>
                              </button>
                            <?php endif; ?>
                          </div>
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