<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Zones de collecte | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $tab = isset($_GET['tab']) ? $_GET['tab'] : 'zone';

  if (!in_array($tab, ['zone', 'type'])) {
    $tab = 'zone';
  }

  $zone = new Zone($db);
  $zones = $zone->read();

  $type_zone = new ZoneType($db);
  $type_zones = $type_zone->read();

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
      <ul class="nav nav-underline fs-9 mt-n4" id="myTab" role="tablist">
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'zone' ? 'active' : ''; ?>" id="zone-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=zone'; ?>">Zones de collecte</a></li>
        <li class="nav-item" role="presentation"><a class="nav-link <?php echo $tab == 'type' ? 'active' : ''; ?>" id="type-tab" href="<?php echo $_SERVER['PHP_SELF'] . '?tab=type'; ?>">Types de zones</a></li>
      </ul>

      <div class="tab-content mt-5" id="myTabContent">
        <div class="tab-pane fade <?php echo $tab == 'zone' ? 'active show' : ''; ?>" id="tab-home" role="tabpanel" aria-labelledby="home-tab">
          <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0 border-start-0">
            <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
              <div class="col-auto">
                <h4 class="my-1 fw-black fs-8">Liste des zones de collecte</h4>
              </div>

              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-bs-target="#addZoneModal" aria-haspopup="true" aria-expanded="false"
                data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter une zone</button>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                  <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                    <thead class="bg-primary-subtle">
                      <tr>
                        <th class="white-space-nowrap align-middle">Code</th>
                        <th class="white-space-nowrap align-middle">Libellé</th>
                        <th class="white-space-nowrap align-middle">Type</th>
                        <th class="white-space-nowrap align-middle">Superficie (km²)</th>
                        <th class="white-space-nowrap align-middle">Couches</th>
                        <th class="white-space-nowrap align-middle" style="min-width:100px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="list">
                      <?php foreach ($zones as $zone) { ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                          <td class="px-2 align-middle"><?php echo $zone['code']; ?></td>
                          <td class="px-2 align-middle"><?php echo $zone['name']; ?></td>
                          <td class="px-2 align-middle">
                            <?php foreach ($type_zones as $type_zone) { ?>
                              <?php if ($type_zone['id'] == $zone['type_id']) { ?>
                                <?php echo $type_zone['name']; ?>
                              <?php } ?>
                            <?php } ?>
                          </td>
                          <td class="px-2 align-middle"><?php echo $zone['superficie']; ?></td>
                          <td class="px-2 align-middle">
                            <?php if ($zone['couches'] != '') { ?>
                              <button title="Télécharger les couches" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1 d-flex align-items-center gap-1 rounded-1"
                                onclick="downloadFiles('Couches', '<?php echo $zone['name']; ?>', '<?php echo $zone['couches']; ?>')">
                                <span class="uil-cloud-download fs-8"></span>
                                <span class="fs-10">Télécharger</span>
                              </button>
                            <?php } ?>
                          </td>

                          <td class="px-2 align-middle">
                            <div class="position-relative d-flex gap-1">
                              <?php if (checkPermis($db, 'update')) : ?>
                                <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                                  data-bs-target="#addZoneModal" data-id="<?php echo $zone['id']; ?>">
                                  <span class="uil-pen fs-8"></span>
                                </button>
                              <?php endif; ?>

                              <?php if (checkPermis($db, 'delete', 2)) : ?>
                                <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                  onclick="deleteData(<?php echo $zone['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cette zone ?', 'zones')">
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


        <!-- Types de zones -->
        <div class="tab-pane fade <?php echo $tab == 'type' ? 'active show' : ''; ?>" id="tab-type" role="tabpanel" aria-labelledby="type-tab">
          <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
            <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
              <div class="col-auto">
                <h4 class="my-1 fw-black fs-8">Liste des types de zones</h4>
              </div>

              <div class="ms-lg-2">
                <button title="Ajouter un type zone" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                  data-bs-target="#addZoneTypeModal" aria-haspopup="true" aria-expanded="false"
                  data-bs-reference="child">
                  <i class="fas fa-plus"></i> Ajouter un type zone</button>
              </div>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-12">
              <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                  <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                    <thead class="bg-primary-subtle">
                      <tr>
                        <th class="sort align-middle">Libellé</th>
                        <th class="sort align-middle">Description</th>
                        <th class="sort align-middle" style="min-width:100px;">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="list">
                      <?php foreach ($type_zones as $type_zone): ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                          <td class="px-2 align-middle"> <?php echo $type_zone['name'] ?> </td>
                          <td class="px-2 align-middle"> <?php echo $type_zone['description'] ?> </td>
                          <td class="px-2 align-middle">
                            <div class="position-relative">
                              <div class="">
                                <?php if (checkPermis($db, 'update')) : ?>
                                  <button title="Modifier" type="button" data-bs-toggle="modal" data-bs-target="#addZoneTypeModal"
                                    data-id="<?php echo $type_zone['id'] ?>"
                                    class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                    <span class="uil-pen fs-8"></span>
                                  </button>
                                <?php endif; ?>

                                <?php if (checkPermis($db, 'delete')) : ?>
                                  <button title="Supprimer" onclick="deleteData(<?php echo $type_zone['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce type zone ?', 'zone_types')"
                                    type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                    <span class="uil-trash-alt fs-8"></span>
                                  </button>
                                <?php endif; ?>
                              </div>
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
    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>