<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- ===============================================-->
  <!--    Document Title-->
  <!-- ===============================================-->
  <title>Indicateurs de la CDN | MRV - Burundi</title>
  <?php
  include './components/navbar & footer/head.php';

  $referentiel = new Referentiel($db);
  $referentiels = $referentiel->read();
  if (isset($_GET['secteur']) && !empty($_GET['secteur'])) {
    $secteur = (int) $_GET['secteur'];
    $referentiels = array_filter($referentiels, function ($referentiel) use ($secteur) {
      return $referentiel['secteur_id'] == $secteur;
    });
  }

  if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
    $referentiels = array_filter($referentiels, function ($referentiel) use ($action) {
      return $referentiel['action_type'] == $action;
    });
  }

  if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $categorie = $_GET['categorie'];
    $referentiels = array_filter($referentiels, function ($referentiel) use ($categorie) {
      return $referentiel['categorie'] == $categorie;
    });
  }

  $programme = new Programme($db);
  $programmes = $programme->read();

  $niveau = new Niveau($db);
  $niveaux = $niveau->read();

  $zone_types = new ZoneType($db);
  $zone_types = $zone_types->read();

  $province = new Province($db);
  $provinces = $province->read();

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

  $unite = new Unite($db);
  $unites = $unite->read();

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
  });
  $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
    return $secteur['parent'] > 0 && $secteur['state'] == 'actif';
  });

  $modeles_typologie = array('valeur_relative', 'typo_quantitative', 'typo_qualitative')
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
      <div class="mt-n5 p-1 mx-lg-n6 bg-body-emphasis border-y">
        <div class="row mx-n1 py-1 align-items-center">
          <div class="col-md-4">
            <h3 class="h5 mb-0 fw-bold">Listes des indicateurs réferentiels</h3>
            <p class="text-muted small mb-0">Tableau récapitulatif des indicateurs de la CDN</p>
          </div>
          <div class="col-md-8">
            <div class="d-flex justify-content-md-end gap-3">
              <div class="d-flex gap-1 align-items-center">
                <?php $currFilSecteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
                $currFilAction = isset($_GET['action']) ? $_GET['action'] : '';
                $currFilCategorie = isset($_GET['categorie']) ? $_GET['categorie'] : ''; ?>

                <span class="form-label">Filtrer : </span>
                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="actionFilter"
                      onchange="pagesFilters([{ id: 'actionFilter', param: 'action' }])">
                      <option value="">Toutes actions</option>
                      <?php foreach (listTypeAction() as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($currFilAction == $key) ? 'selected' : '' ?>>
                          <?= $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>
                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="secteurFilter"
                      onchange="pagesFilters([{ id: 'secteurFilter', param: 'secteur' }])">
                      <option value="">Tous secteurs</option>
                      <?php if (isset($secteurs) && !empty($secteurs)): ?>
                        <?php foreach ($secteurs as $secteur): ?>
                          <option value="<?= $secteur['id'] ?>" <?= ($currFilSecteur == $secteur['id']) ? 'selected' : '' ?>>
                            <?= $secteur['name'] ?>
                          </option>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </select>
                  </form>
                </div>
                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                  <form class="position-relative">
                    <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="catgorieFilter"
                      onchange="pagesFilters([{ id: 'catgorieFilter', param: 'categorie' }])">
                      <option value="">Tous catégorie</option>
                      <?php foreach (listTypeCategorie() as $key => $value): ?>
                        <option value="<?= $key ?>" <?= ($currFilCategorie == $key) ? 'selected' : '' ?>>
                          <?= $value ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </div>
              </div>

              <button title="Ajouter" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addReferentielModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un indicateur
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3 p-1 mx-lg-n6 bg-body-emphasis border-y">
        <div class="table-responsive mx-n1 p-1 scrollbar" style="min-height: 432px;">
          <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
            <thead class="bg-primary-subtle">
              <tr>
                <th class="sort align-middle" scope="col"> Code</th>
                <th class="sort align-middle" scope="col"> Intitulé</th>
                <th class="sort align-middle" scope="col"> Unité</th>
                <th class="sort align-middle" scope="col"> Catégorie</th>
                <th class="sort align-middle" scope="col"> Responsables</th>
                <th class="sort align-middle" scope="col"> Métadonnées</th>
                <th class="sort align-middle" scope="col"> Suivis</th>
                <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
              </tr>
            </thead>
            <tbody class="list" id="table-latest-referentiel-body">
              <?php foreach ($referentiels as $referentiel): ?>
                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                  <td class="align-middle px-2">
                    <div class="d-flex align-items-start justify-content-end gap-2">
                      <?php if ($referentiel['in_dashboard'] == 1): ?>
                        <span class="badge bg-light p-1" title="Indicateur affiché sur le tableau de bord"><i class="fas fa-home text-primary"></i></span>
                      <?php endif; ?>
                      <span class="fw-semibold"><?php echo $referentiel['code']; ?></span>
                    </div>
                  </td>

                  <td class="align-middle px-2">
                    <?php echo html_entity_decode($referentiel['intitule']); ?>
                  </td>

                  <td class="align-middle px-2 text-nowrap"><?php echo $referentiel['unite']; ?></td>

                  <td class="align-middle text-center mx-auto px-2">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                      <?php if ($referentiel['categorie'] == 'impact'): ?>
                        <i class="fas fa-bullseye text-secondary me-1" title="Impact"></i>
                      <?php elseif ($referentiel['categorie'] == 'effet'): ?>
                        <i class="fas fa-chart-line text-secondary me-1" title="Effet"></i>
                      <?php else: ?>
                        <i class="fas fa-tag text-secondary me-1"></i>
                      <?php endif; ?>
                      <?= htmlspecialchars(listTypeCategorie()[$referentiel['categorie']] ?? 'N/A') ?>
                    </span>
                    <br>
                    <?php if (in_array($referentiel['modele'], $modeles_typologie)) : ?>
                      <button title="Typologie" type="button" class="btn btn-sm btn-link text-primary fs-10 p-0 m-0" data-bs-toggle="modal" data-bs-target="#addTypologieModal"
                        data-referentiel_id="<?php echo $referentiel['id']; ?>" data-echelle="<?php echo $referentiel['echelle']; ?>">
                        (Typologie)
                      </button>
                    <?php endif; ?>
                  </td>

                  <td class="align-middle px-2">
                    <?php foreach ($structures as $structure): ?>
                      <?php if ($structure['id'] == $referentiel['responsable']): ?>
                        <?php echo $structure['sigle']; ?>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  </td>

                  <td class="align-middle px-2">
                    <button title="Métadonnées" type="button" class="btn btn-subtle-info fs-10 py-1 rounded-pill btn-sm fw-bold fs-9 px-2 py-1" data-bs-toggle="modal" data-bs-target="#addMetadataModal"
                      data-referentiel_id="<?php echo $referentiel['id']; ?>">
                      Métadonnées
                    </button>
                  </td>

                  <td class="align-middle px-2 py-0 text-center">
                    <?php if ($referentiel['categorie'] !== 'produit') { ?>
                      <button title="Suivre" type="button" class="btn btn-subtle-warning fs-10 py-1 rounded-pill btn-sm fw-bold fs-9 px-2 py-1"
                        onclick="window.location.href = 'referentiel_view.php?id=<?php echo $referentiel['id']; ?>';">Suivre
                      </button>
                    <?php } else {
                      echo '-';
                    } ?>
                  </td>

                  <td class="align-middle review px-2">
                    <div class="position-relative">
                      <div class="d-flex gap-1">
                        <?php if (checkPermis($db, 'update')) : ?>
                          <button title="Modifier" class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" data-bs-toggle="modal"
                            data-bs-target="#addReferentielModal" data-id="<?php echo $referentiel['id']; ?>">
                            <span class="uil-pen fs-8"></span>
                          </button>
                        <?php endif; ?>

                        <?php if (checkPermis($db, 'update', 2)) : ?>
                          <button title="Activer/Désactiver" onclick="updateState(<?php echo $referentiel['id']; ?>, '<?php echo $referentiel['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $referentiel['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce referentiel ?', 'referentiels')"
                            type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                            <span class="uil-<?php echo $referentiel['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                          </button>
                        <?php endif; ?>

                        <?php if (checkPermis($db, 'delete')) : ?>
                          <button title="Supprimer" onclick="deleteData(<?php echo $referentiel['id']; ?>, 'Êtes-vous sûr de vouloir supprimer ce referentiel ?', 'referentiels')"
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

    <?php include 'components/navbar & footer/footer.php'; ?>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>