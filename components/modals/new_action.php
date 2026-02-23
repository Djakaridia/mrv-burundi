<!-- modal -->
<div class="modal fade" id="addActionPrioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addActionPrioModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="actionsPrio_modtitle">Ajouter une action prioritaire</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Fermer">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="actionPrioLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="actionPrioLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="actionPrioContentContainer" style="display: none;">
          <form action="" name="FormActionPrio" id="FormActionPrio" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="row m-0 px-0">
                <div class="col-lg-3 mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'actionPrio_code', 'actionPrio_codeFeedback', 'actions')" class="form-control" type="text" name="code" id="actionPrio_code" placeholder="Entrer le code"
                    required />
                  <div id="actionPrio_codeFeedback" class="invalid-feedback"></div>
                </div>

                <div class="col-lg-9 mt-1">
                  <div class="mb-1">
                    <label class="form-label">Intitulé*</label>
                    <input class="form-control" type="text" name="name" id="actionPrio_name" placeholder="Entrer le nom"
                      required />
                  </div>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Secteur*</label>
                  <select class="form-select" name="secteur_id" id="actionPrio_secteur_id">
                    <option value="0" selected disabled>Selectionnez le secteur</option>
                    <?php if (!empty($secteurs)) : ?>
                      <?php foreach ($secteurs as $secteur): ?>
                        <option value="<?php echo $secteur['id'] ?? "" ?>"><?php echo $secteur['name'] ?? "" ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
                <div class="col-lg-6">
                  <label class="form-label">Type d'action*</label>
                  <select class="form-select" name="action_type" id="actionPrio_type">
                    <option value="0" selected disabled>Selectionnez le type d'action</option>
                    <?php foreach (listTypeAction() as $key => $value): ?>
                      <option value="<?= $key ?>"><?= $value ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-lg-6 mt-1">
                  <div class="mb-1">
                    <label class="form-label">Objectif inconditionnel</label>
                    <input class="form-control" type="text" name="objectif_wem" id="actionPrio_objWem">
                  </div>
                </div>
                <div class="col-lg-6 mt-1">
                  <div class="mb-1">
                    <label class="form-label">Objectif conditionnel</label>
                    <input class="form-control" type="text" name="objectif_wam" id="actionPrio_objWam">
                  </div>
                </div>

                <div class="col-lg-12 mt-1">
                  <div class="mb-1">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="actionPrio_description" placeholder="Entrer une description"></textarea>
                  </div>
                </div>
              </div>

              <div class="modal-footer d-flex justify-content-between border-0 py-0 px-3">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                  aria-label="Fermer">Annuler</button>
                <button type="submit" id="actionPrio_modbtn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formActionPrioID = null;

  $(function() {
    const modal = $('#addActionPrioModal');
    const form = $('#FormActionPrio')[0];
    const submitBtn = $('#actionPrio_modbtn');

    modal.on('shown.bs.modal', async function(event) {
      const button = $(event.relatedTarget || []);
      const dataId = button.data('id') || null;

      $('#actionPrioLoadingScreen').show();
      $('#actionPrioContentContainer').hide();

      form.reset();
      formActionPrioID = null;

      if (dataId) {
        formActionPrioID = dataId;

        $('#actionsPrio_modtitle').text("Modifier l'action prioritaire");
        submitBtn.text("Modifier");

        try {
          const response = await fetch(`./apis/actions.routes.php?id=${dataId}`, {
            headers: {
              Authorization: `Bearer ${token}`
            }
          });

          const result = await response.json();
          if (result.status !== 'success') throw new Error();
          form.code.value = result.data.code ?? '';
          form.name.value = result.data.name ?? '';
          form.description.value = result.data.description ?? '';
          form.objectif_wem.value = result.data.objectif_wem ?? '';
          form.objectif_wam.value = result.data.objectif_wam ?? '';
          form.action_type.value = result.data.action_type ?? '';
          form.secteur_id.value = result.data.secteur_id ?? '';

        } catch (e) {
          errorAction("Impossible de charger les données.");
        }
      } else {
        $('#actionsPrio_modtitle').text("Ajouter une action prioritaire");
        submitBtn.text("Ajouter");
      }

      $('#actionPrioLoadingScreen').hide();
      $('#actionPrioContentContainer').show();
    });

    modal.on('hidden.bs.modal', function() {
      form.reset();
      formActionPrioID = null;
    });

    $('#FormActionPrio').on('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const url = formActionPrioID ? `./apis/actions.routes.php?id=${formActionPrioID}` : `./apis/actions.routes.php`;
      submitBtn.prop('disabled', true).text("Envoi...");

      try {

        const response = await fetch(url, {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`
          },
          body: formData
        });

        const result = await response.json();

        if (result.status === "success") {
          successAction(result.message);
          modal.modal('hide');
        } else {
          errorAction(result.message);
        }

      } catch {
        errorAction("Erreur lors de l'envoi.");
      }

      submitBtn.prop('disabled', false).text(formActionPrioID ? "Modifier" : "Ajouter");
    });

  });
</script>