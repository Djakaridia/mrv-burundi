<div class="modal fade" id="addGroupeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addGroupeModal" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="groupe_modtitle">Ajouter un groupe</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div class="row g-4">
          <div id="groupeLoadingScreen" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
            <h4 class="mt-3 fw-bold text-primary" id="groupeLoadingText">Chargement en cours</h4>
          </div>

          <!-- Content Container (initially hidden) -->
          <div id="groupeContentContainer" style="display: none;">
            <form action="" name="FormGroupe" id="FormGroupe" method="POST" enctype="multipart/form-data">

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'groupe_code', 'groupe_code_feedback', 'groupes')" class="form-control" type="text" name="code" id="groupe_code" placeholder="Entrer le code"
                    required />
                  <div id="groupe_code_feedback" class="invalid-feedback"></div>
                </div>
              </div>
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Libellé*</label>
                  <input class="form-control" type="text" name="name" id="groupe_name" placeholder="Entrer le libellé"
                    required />
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Acteur Moniteur*</label>
                  <select class="form-select" name="monitor" id="groupe_monitor" required>
                    <option value="">Sélectionner un acteur</option>
                    <?php if ($structures ?? []) : ?>
                    <?php foreach ($structures as $structure) { ?>
                      <option value="<?php echo $structure['id']; ?>"><?php echo $structure['sigle']; ?></option>
                    <?php } ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="groupe_description"
                    placeholder="Entrer une description"></textarea>
                </div>
              </div>

              <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                  aria-label="Close">Annuler</button>
                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="groupe_modbtn">Ajouter </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formGroupeID = null;
  $(document).ready(function () {
    $('#addGroupeModal').on('shown.bs.modal', async function (event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormGroupe');
      // Show loading screen and hide content
      $('#groupeLoadingScreen').show();
      $('#groupeContentContainer').hide();
      if (dataId) {
        formGroupeID = dataId;
        $('#groupe_modtitle').text('Modifier l\'action');
        $('#groupe_modbtn').text('Modifier');
        $('#groupeLoadingText').text("Chargement des données groupe...");
        
        try {
          const response = await fetch(`./apis/groupes.routes.php?id=${dataId}`, {
            headers: { 'Authorization': `Bearer ${token}` },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.name.value = result.data.name;
          form.monitor.value = result.data.monitor;
          form.description.value = result.data.description;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        }
        finally {
          // Hide loading screen and show content
          $('#groupeLoadingScreen').hide();
          $('#groupeContentContainer').show();
        }
      } else {
        formGroupeID = null;
        $('#groupe_modtitle').text('Ajouter un groupe');
        $('#groupe_modbtn').text('Ajouter');
        $('#groupeLoadingText').text("Préparation du formulaire...");
        
        setTimeout(() => {
          $('#groupeLoadingScreen').hide();
          $('#groupeContentContainer').show();
        }, 200);
      }
    });

    $('#addGroupeModal').on('hide.bs.modal', function () {
      setTimeout(()=> {
        $('#groupeLoadingScreen').show();
        $('#groupeContentContainer').hide();
      }, 200);
      $('#FormGroupe')[0].reset();
    });

    $('#FormGroupe').on('submit', async function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formGroupeID ? `./apis/groupes.routes.php?id=${formGroupeID}` : './apis/groupes.routes.php';
      const submitBtn = $('#groupe_modbtn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

      try {
        const response = await fetch(url, {
          headers: { 'Authorization': `Bearer ${token}` },
          method: "POST",
          body: formData
        });

        const data = await response.json();

        if (data.status === 'success') {
          successAction('Données envoyées avec succès.');
          $('#addGroupeModal').modal('hide');
        } else {
          errorAction(data.message || 'Erreur lors de l\'envoi des données.');
        }
      } catch (error) {
        errorAction('Erreur lors de l\'envoi des données.');
      } finally {
        submitBtn.prop('disabled', false);
        submitBtn.text('Enregistrer');
      }
    });
  });
</script>