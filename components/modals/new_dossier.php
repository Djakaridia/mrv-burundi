<!-- modal -->
<div class="modal fade" id="addDossierModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addDossierModal" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="dossier_modtitle">Ajouter un dossier</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div id="dossierLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="dossierLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="dossierContentContainer" style="display: none;">
          <form action="" name="FormDossier" id="FormDossier" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Libellé*</label>
                  <input class="form-control" type="text" name="name" id="name_dossier" placeholder="Entrer le libellé"
                    required />
                </div>
              </div>
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="description_dossier"
                    placeholder="Entrer une description"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <input type="hidden" name="parent" id="parent_id_dossier" value="0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="dossier_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let formDossierID = null;
  let formDossierParentId = null;

  $(document).ready(function () {
    $('#addDossierModal').on('shown.bs.modal', async function (event) {
      const dataId = $(event.relatedTarget).data('id');
      const dataParentId = $(event.relatedTarget).data('parent-id');
      const form = document.getElementById('FormDossier');
      form.parent.value = dataParentId ? dataParentId : 0;
      // Show loading screen and hide content
      $('#dossierLoadingScreen').show();
      $('#dossierContentContainer').hide();
      if (dataId) {
        formDossierID = dataId;
        $('#dossier_modtitle').text('Modifier le dossier');
        $('#dossier_modbtn').text('Modifier');
        $('#dossierLoadingText').text("Chargement des données dossier...");
        
        try {
          const response = await fetch(`./apis/dossiers.routes.php?id=${dataId}`, {
            headers: { 'Authorization': `Bearer ${token}` },
            method: 'GET',
          });

          const result = await response.json();
          form.name.value = result.data.name;
          form.description.value = result.data.description;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        }
        finally {
          // Hide loading screen and show content
          $('#dossierLoadingScreen').hide();
          $('#dossierContentContainer').show();
        }
      } else {
        formDossierID = null;
        $('#dossier_modtitle').text('Ajouter un dossier');
        $('#dossier_modbtn').text('Ajouter');
        $('#dossierLoadingText').text("Préparation du formulaire...");
        
        setTimeout(() => {
          $('#dossierLoadingScreen').hide();
          $('#dossierContentContainer').show();
        }, 200);
      }
    });

    $('#addDossierModal').on('hide.bs.modal', function () {
      setTimeout(()=> {
        $('#dossierLoadingScreen').show();
        $('#dossierContentContainer').hide();
      }, 200);
      $('#FormDossier')[0].reset();
    });

    $('#FormDossier').on('submit', async function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formDossierID ? `./apis/dossiers.routes.php?id=${formDossierID}` : './apis/dossiers.routes.php';
      const submitBtn = $('#dossier_modbtn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

      try {
        const response = await fetch(url, {
          headers: { 'Authorization': `Bearer ${token}` },
          method: "POST",
          body: formData
        });

        const result = await response.json();
        if (result.status === 'success') {
          successAction('Données envoyées avec succès.');
          $('#addDossierModal').modal('hide');
        } else {
          errorAction(result.message || 'Erreur lors de l\'envoi des données.');
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