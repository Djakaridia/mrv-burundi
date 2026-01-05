<div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
  <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
    <div class="col-auto">
      <h4 class="my-1 fw-black">Liste des réunions</h4>
    </div>

    <button title="Ajouter une réunion" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addEventModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
      <i class="fas fa-plus"></i> Ajouter une réunion
    </button>
  </div>
</div>

<div class="row mt-3">
  <div class="col-12">
    <div class="mx-n4 px-0 mx-lg-n6 bg-body-emphasis pt-3 border-y">
      <div class="chat d-flex phoenix-offcanvas-container pt-1 mt-n1 mb-5 px-3" style="height: 555px;">
        <!-- <div class="card p-0 chat-sidebar me-1">
          <div class="search-box mx-auto w-100 px-lg-2 mb-3">
            <form class="position-relative">
              <input
                class="form-control form-control-sm search-input search"
                type="search"
                placeholder="Rechercher une réunion"
                aria-label="Search" />
              <span class="fas fa-search search-box-icon"></span>
            </form>
          </div>

          <div class="scrollbar border border-light rounded-0 mx-2 shadow-sm h-100">
            <?php if (!empty($reunions)) { ?>
              <?php foreach ($reunions as $reunion) { ?>
                <div class="card mb-1 rounded-0 shadow-sm border-0 border-end border-2 border-<?php echo explode('-', $reunion['couleur'])[1] ?> hover-actions-trigger">
                  <div class="card-body p-2 d-flex flex-column flex-md-row justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <h6 class="mb-1 fs-8"><?= $reunion['name'] ?></h6>
                      <p class="mb-1 text-muted small">
                        <strong>Groupe :</strong> <?= $reunion['groupe_nom'] ?> <br>
                        <strong>Code :</strong> <?= $reunion['code'] ?> <br>
                        <strong>Lieu :</strong> <?= $reunion['lieu'] ?> <br>
                        <strong>Date :</strong> <?= date('d/m/Y', strtotime($reunion['horaire'])) ?> <span class="mx-2">|</span>
                        <strong>Heure :</strong> <?= date('H:i', strtotime($reunion['horaire'])) ?>
                      </p>
                    </div>

                    <div class="text-end d-flex flex-column align-items-end">
                      <span class="badge badge-phoenix fs-10 badge-phoenix-<?= getBadgeClass($reunion['status']) ?> mb-3">
                        <i class="fa <?= getBadgeFaIcon($reunion['status']) ?> me-1" aria-hidden="true"></i>
                        <span class="badge-label"><?= ucfirst($reunion['status']) ?></span>
                      </span>
                      <button title="Voir les détails" type="button" data-bs-toggle="modal" data-bs-target="#eventDetailsModal" data-id="<?= $reunion['id'] ?>" aria-haspopup="true" aria-expanded="false"
                        class="hover-actions bottom-0 end-0 btn btn-sm btn-subtle-info mb-3 me-2 fs-10 px-2 py-1">
                        <span class="uil-eye fs-8"></span>
                      </button>
                    </div>
                  </div>
                </div>
              <?php } ?>
            <?php } else { ?>
              <div class="alert alert-subtle-info m-1 p-5 text-center" role="alert">
                <h4>Aucun événement trouvé</h4>
              </div>
            <?php } ?>
          </div>
        </div> -->

        <div class="card tab-content flex-1 phoenix-offcanvas-container">
          <div class="row p-3 gy-3 gx-0">
            <div class="col-6 col-md-4 order-1 d-flex align-items-center">
              <button title="Aujourd'hui" class="btn btn-sm btn-phoenix-primary px-4" id="todayBtn" data-event="today">Today</button>
            </div>

            <div class="col-12 col-md-4 order-md-1 d-flex align-items-center justify-content-center">
              <button title="Precedent" class="btn icon-item icon-item-sm shadow-none text-body-emphasis p-0" type="button" id="prevBtn" data-event="prev" title="Previous">
                <span class="fas fa-chevron-left"></span>
              </button>
              <h3 class="px-3 text-body-emphasis fw-semibold calendar-title mb-0" id="calendarTitle"></h3>
              <button title="Suivant" class="btn icon-item icon-item-sm shadow-none text-body-emphasis p-0" type="button" id="nextBtn" data-event="next" title="Next">
                <span class="fas fa-chevron-right"></span>
              </button>
            </div>

            <div class="col-6 col-md-4 ms-auto order-1 d-flex justify-content-end">
              <div class="btn-group btn-group-sm" role="group">
                <button title="Mois" class="btn btn-phoenix-secondary active-view" id="monthView" data-fc-view="dayGridMonth">Month</button>
                <button title="Semaine" class="btn btn-phoenix-secondary" id="weekView" data-fc-view="timeGridWeek">Week</button>
              </div>
            </div>
          </div>

          <div class="calendar-outline mb-3" id="appCalendar"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="eventDetailsModalLabel">Détail de l'événement</h3>
        <button title="Fermer" type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Fermer"><span class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div id="reunionDetailContent">
          <div class="text-center">
            <div class="spinner-border text-primary" role="status"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>