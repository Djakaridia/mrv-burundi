<div class="modal fade" id="addStructureTypeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addStructureTypeModal" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="structure_type_modtitle">Ajouter un type d'acteur</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div id="structureTypeLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="structureTypeLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="structureTypeContentContainer" style="display: none;">
          <form action="" name="FormStructureType" id="FormStructureType" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Libellé*</label>
                  <input class="form-control" type="text" name="name" id="structure_type_name"
                    placeholder="Entrer le libellé" required />
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="structure_type_description"
                    placeholder="Entrer une description"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="structure_type_modbtn">Ajouter
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
  let formStructureTypeID = null;
  $(document).ready(function () {
    $('#addStructureTypeModal').on('shown.bs.modal', async function (event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormStructureType');

      // Show loading screen and hide content
      $('#structureTypeLoadingScreen').show();
      $('#structureTypeContentContainer').hide();
      if (dataId) {
        formStructureTypeID = dataId;
        $('#structure_type_modtitle').text('Modifier le type d\'acteur');
        $('#structure_type_modbtn').text('Modifier');
        $('#structureTypeLoadingText').text("Chargement des données type acteur...");
        
        try {
          const response = await fetch(`./apis/structure_types.routes.php?id=${dataId}`, {
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
          $('#structureTypeLoadingScreen').hide();
          $('#structureTypeContentContainer').show();
        }
      } else {
        formStructureTypeID = null;
        $('#structure_type_modtitle').text('Ajouter un type d\'acteur');
        $('#structure_type_modbtn').text('Ajouter');
        $('#structureTypeLoadingText').text("Préparation du formulaire...");
        
        setTimeout(() => {
          $('#structureTypeLoadingScreen').hide();
          $('#structureTypeContentContainer').show();
        }, 200);
      }
    });

    $('#addStructureTypeModal').on('hide.bs.modal', function () {
      setTimeout(()=> {
        $('#structureTypeLoadingScreen').show();
        $('#structureTypeContentContainer').hide();
      }, 200);
      $('#FormStructureType')[0].reset();
    });

    $('#FormStructureType').on('submit', async function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formStructureTypeID ? `./apis/structure_types.routes.php?id=${formStructureTypeID}` : './apis/structure_types.routes.php';
      const submitBtn = $('#structure_type_modbtn');
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
          $('#addStructureTypeModal').modal('hide');
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