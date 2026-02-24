<div class="row mx-0">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3 px-3">
      <h4 class="my-1 fw-black fs-8">Liste des reunions du groupe</h4>
      <div class="ms-lg-2">
        <?php if ($group_curr['state'] == 'actif'): ?>
          <button title="Ajouter une reunion" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
            data-bs-target="#addEventModal" data-groupe_id="<?= $group_curr['id'] ?>" aria-haspopup="true" aria-expanded="false"
            data-bs-reference="parent">
            <i class="fas fa-plus"></i> Ajouter une reunion
          </button>
        <?php endif; ?>
      </div>
    </div>

    <div class="row bg-body-emphasis p-3 border-top">
      <?php if (!empty($reunions)) { ?>
        <?php foreach ($reunions as $reunion) {
          $document = new Documents($db);
          $document->entity_id = $reunion['id'];
          $documents_reunion = $document->readByEntityId();
        ?>
          <div class="card mb-1 col-lg-4 col-md-6 col-12 m-2 shadow-sm hover-actions-trigger rounded-0" style="border-left: 4px solid <?= $reunion['couleur'] ?>; border-radius: 5px;">
            <div class="card-body p-2 d-flex flex-column flex-md-row justify-content-between align-items-start">
              <div class="flex-grow-1 cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#meetOffCanvas-<?= $reunion['id'] ?>" aria-controls="offcanvasRight">
                <div class="btn-link text-decoration-none">
                  <h6 class="mb-1 fs-8 text-primary"><?= $reunion['name'] ?></h6>
                </div>
                <p class="mb-1 text-muted small">
                  <strong>Code :</strong> <?= $reunion['code'] ?> <br>
                  <strong>Lieu :</strong> <?= $reunion['lieu'] ?> <br>
                  <strong>Date :</strong> <?= date('d/m/Y', strtotime($reunion['horaire'])) ?> <span class="mx-2">|</span>
                  <strong>Heure :</strong> <?= date('H:i', strtotime($reunion['horaire'])) ?>
                </p>
              </div>

              <div class="text-end d-flex justify-content-end flex-column align-items-end">
                <span class="badge badge-phoenix fs-10 badge-phoenix-<?= getBadgeClass($reunion['status']) ?> mb-3">
                  <i class="fa <?= getBadgeFaIcon($reunion['status']) ?> me-1" aria-hidden="true"></i>
                  <span class="badge-label"><?= ucfirst($reunion['status']) ?></span>
                </span>

                <div class="dropdown p-0 mt-3">
                  <button title="Actions" class="btn btn-sm dropdown-toggle dropdown-caret-none mx-0 ps-3 pe-0" type="button"
                    data-bs-toggle="dropdown" data-boundary="window" aria-haspopup="true"
                    aria-expanded="false" data-bs-reference="parent">
                    <span class="fas fa-ellipsis-v fs-8 text-body"></span>
                  </button>
                  <div class="dropdown-menu p-0">
                    <div class="d-flex flex-column">
                      <?php if (checkPermis($db, 'update')): ?>
                        <div class="dropdown-item border-top border-light p-1">
                          <a data-bs-toggle="modal" data-bs-target="#addEventModal" data-niveau="0"
                            data-id="<?php echo $reunion['id'] ?>"
                            class="link-info text-start d-flex align-items-center d-block fs-9 btn p-1">
                            <span class="uil-pen fs-9 me-1"></span> Modifier
                          </a>
                        </div>
                      <?php endif; ?>

                      <?php if (checkPermis($db, 'update', 2)): ?>
                        <div class="dropdown-item border-top border-light p-1">
                          <a onclick="updateState(<?php echo $reunion['id']; ?>, '<?php echo $reunion['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $reunion['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cette réunion ?', 'reunions')"
                            class="link-warning text-start d-flex align-items-center d-block fs-9 btn p-1">
                            <span
                              class="uil-<?php echo $reunion['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-9 me-1"></span>
                            <?php echo $reunion['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>
                          </a>
                        </div>
                      <?php endif; ?>

                      <?php if (checkPermis($db, 'delete')): ?>
                        <div class="dropdown-item border-top border-light p-1">
                          <a onclick="deleteData(<?php echo $reunion['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette réunion ?', 'reunions')"
                            class="link-danger text-start d-flex align-items-center d-block fs-9 btn p-1">
                            <span class="uil-trash-alt fs-9 me-1"></span> Supprimer
                          </a>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- offcanvas -->
          <div class="offcanvas offcanvas-end content-offcanvas offcanvas-backdrop-transparent border-start shadow-none bg-body" id="meetOffCanvas-<?= $reunion['id'] ?>" tabindex="-1" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-body p-0">
              <div class="p-3">
                <div class="d-flex flex-between-center align-items-start gap-5 mb-3">
                  <h2 class="fw-bold fs-6 mb-0 text-body-highlight"><?= $reunion['name'] ?></h2>

                  <div class="ms-auto d-flex align-items-center gap-2">
                    <?php if (checkPermis($db, 'update')): ?>
                      <button title="Modifier" class="btn btn-phoenix-info shadow-sm btn-icon px-2 rounded-circle" type="button" data-bs-toggle="modal" data-bs-target="#addEventModal" data-niveau="0" data-id="<?php echo $reunion['id'] ?>">
                        <span class="uil-pen"></span>
                      </button>
                    <?php endif; ?>

                    <?php if (checkPermis($db, 'delete')): ?>
                      <button title="Supprimer" class="btn btn-phoenix-danger shadow-sm btn-icon px-2 rounded-circle" type="button" onclick="deleteData(<?php echo $reunion['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette réunion ?', 'reunions')">
                        <span class="uil-trash-alt"></span>
                      </button>
                    <?php endif; ?>

                    <button title="Fermer" class="btn btn-phoenix-secondary shadow-sm btn-icon px-2 rounded-circle" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
                      <span class="fa-solid fa-xmark"></span>
                    </button>
                  </div>
                </div>

                <div class="mb-3 border-top pt-2">
                  <h5 class="text-body me-3">Informations</h5>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Code:</span>
                    <span class="text-body-highlight fw-bold"><?= $reunion['code'] ?></span>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Statut:</span>
                    <span class="badge bg-<?= getBadgeClass($reunion['status']) ?>"><?= ucfirst($reunion['status']) ?></span>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Couleur: </span>
                    <span class="me-2"> <input type="color" disabled value="<?= $reunion['couleur'] ?>"> </span>
                  </div>
                </div>

                <div class="mb-3 border-top pt-2">
                  <h5 class="text-body me-3">Description</h5>
                  <p class="text-body-highlight mb-0"><?= nl2br($reunion['description']) ?></p>
                </div>

                <div class="mb-3 border-top pt-2">
                  <h5 class="text-body me-3">Dates et lieu</h5>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Date:</span><?= date('d/m/Y, H:i', strtotime($reunion['horaire'])) ?>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Lieu:</span>
                    <span class="text-body-highlight"><?= $reunion['lieu'] ?></span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Créé par:</span>
                    <span class="text-body-highlight">
                      <?php foreach ($users as $user) {
                        if ($user['id'] == $reunion['add_by']) echo $user['nom'];
                      } ?>
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-body">Date création:</span>
                    <span class="text-body-highlight"><?= date('d/m/Y', strtotime($reunion['created_at'])) ?></span>
                  </div>
                </div>

                <div class="mb-3 border-top pt-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <h5 class="text-body me-3">Fichiers</h5>
                    <button class="btn btn-link text-decoration-none p-0" data-bs-toggle="modal" data-bs-target="#addDocumentModal"
                      data-dossier-id="<?php echo $dossier_reunion['id'] ?>" data-entity-id="<?php echo $reunion['id'] ?>">
                      <span class="fas fa-plus me-1"></span> Ajouter</button>
                  </div>
                  <div class="my-1">
                    <?php foreach ($documents_reunion as $document): ?>
                      <div class="border-top border-bottom border-light p-2">
                        <div class="d-flex flex-between-center">
                          <div>
                            <div class="d-flex align-items-center mb-1 flex-wrap"><span class="fa-solid fa-file-lines me-2 fs-9 text-body-tertiary"></span>
                              <p class="text-body-highlight mb-0 lh-1"><?= $document['name'] ?></p>
                            </div>
                            <div class="d-flex fs-9 text-body-tertiary mb-0 flex-wrap"><span><?= round($document['file_size'] / 1024 / 1024, 2) ?> MB</span>
                              <span class="text-body-quaternary mx-1">| </span>
                              <span class="text-nowrap">Ajouté le <?= date('d/m/Y', strtotime($document['created_at'])) ?></span>
                            </div>
                          </div>

                          <div class="btn-reveal-trigger">
                            <button onclick="downloadFiles('MRV', '<?= $document['name'] ?>', '<?= $document['file_path'] ?>')"
                              class="btn btn-sm btn-phoenix-success me-1 fs-10 px-2 py-1">
                              <span class="uil-cloud-download fs-8"></span>
                            </button>
                            <?php if (checkPermis($db, 'delete')) : ?>
                              <button onclick="deleteData(<?php echo $document['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce document ?', 'documents')"
                                class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                <span class="uil-trash-alt fs-8"></span>
                              </button>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
      <?php } else { ?>
        <div class="text-center py-5 my-5" style="min-height: 300px;">
          <div class="d-flex justify-content-center mb-3">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
              <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <h4 class="text-800 mb-3">Aucune reunion trouvée</h4>
          <p class="text-600 mb-5">Il semble que vous n'ayez pas encore de reunions. Commencez par en créer une.</p>
          <?php if ($group_curr['state'] == 'actif'): ?>
            <button title="Ajouter une reunion" class="btn btn-primary px-5 fs-8" data-groupe_id="<?= $group_curr['id'] ?>" data-bs-toggle="modal" data-bs-target="#addEventModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
              <i class="fas fa-plus"></i> Ajouter une reunion
            </button>
          <?php endif; ?>
        </div>
      <?php } ?>
    </div>
  </div>
</div>