<!-- modal -->
<div class="modal fade" id="addPrioriteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addPrioriteModal" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="priorite_modtitle">Ajouter une priorité</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div id="prioriteLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="prioriteLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="prioriteContentContainer" style="display: none;">
          <form action="" name="FormPriorite" id="FormPriorite" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Libellé*</label>
                  <input class="form-control" type="text" name="name" id="name_priorite" placeholder="Entrer le libellé"
                    required />
                </div>
              </div>
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Couleur</label>
                  <input class="form-control" type="color" name="couleur" id="couleur_priorite"
                    placeholder="Entrer la couleur" />
                </div>
              </div>
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="description_priorite"
                    placeholder="Entrer la description"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="priorite_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let formPrioriteID = null;
  $(document).ready(function () {
    $('#addPrioriteModal').on('shown.bs.modal', async function (event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormPriorite');
      // Show loading screen and hide content
      $('#prioriteLoadingScreen').show();
      $('#prioriteContentContainer').hide();
      if (dataId) {
        formPrioriteID = dataId;
        $('#priorite_modtitle').text('Modifier la priorité');
        $('#priorite_modbtn').text('Modifier');
        $('#prioriteLoadingText').text("Chargement des données priorité...");
        try {
          const response = await fetch(`./apis/priorites.routes.php?id=${dataId}`, {
            headers: { 'Authorization': `Bearer ${token}` },
            method: 'GET',
          });

          const result = await response.json();
          form.name.value = result.data.name;
          form.couleur.value = result.data.couleur;
          form.description.value = result.data.description;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        }
        finally {
          // Hide loading screen and show content
          $('#prioriteLoadingScreen').hide();
          $('#prioriteContentContainer').show();
        }
      } else {
        formPrioriteID = null;
        $('#priorite_modtitle').text('Ajouter une priorité');
        $('#priorite_modbtn').text('Ajouter');
        $('#prioriteLoadingText').text("Préparation du formulaire...");
        
        setTimeout(() => {
          $('#prioriteLoadingScreen').hide();
          $('#prioriteContentContainer').show();
        }, 200);
      }
    });

    $('#addPrioriteModal').on('hide.bs.modal', function () {
      setTimeout(()=> {
        $('#prioriteLoadingScreen').show();
        $('#prioriteContentContainer').hide();
      }, 200);
      $('#FormPriorite')[0].reset();
    });


    $('#FormPriorite').on('submit', async function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formPrioriteID ? `./apis/priorites.routes.php?id=${formPrioriteID}` : './apis/priorites.routes.php';
      const submitBtn = $('#priorite_modbtn');
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
          $('#addPrioriteModal').modal('hide');
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