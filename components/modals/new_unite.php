<!-- modal -->
<div class="modal fade" id="addUniteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addUniteModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="unite_modtitle">Ajouter une unité</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="uniteLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="uniteLoadingText">Chargement en cours</h4>
        </div>
        
        <!-- Content Container (initially hidden) -->
        <div id="uniteContentContainer" style="display: none;">
          <form action="" name="FormUnite" id="FormUnite" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Sigle*</label>
                  <input class="form-control" type="text" name="name" id="name_unite" placeholder="Entrer le sigle" required />
                </div>
              </div>
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="description_unite" placeholder="Entrer une description"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="unite_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formUniteID = null;
  $(document).ready(function() {
    $('#addUniteModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormUnite');
      
      $('#uniteLoadingScreen').show();
      $('#uniteContentContainer').hide();
      form.reset();

      if (dataId) {
        formUniteID = dataId;
        $('#unite_modtitle').text('Modifier l\'unité');
        $('#unite_modbtn').text('Modifier');
        $('#uniteLoadingText').text("Chargement des données de l'unité...");

        try {
          const response = await fetch(`./apis/unites.routes.php?id=${dataId}`, {
            headers: { 'Authorization': `Bearer ${token}` },
            method: 'GET',
          });

          const result = await response.json();
          if (result.status === 'success') {
            form.name.value = result.data.name;
            form.description.value = result.data.description;
          } else {
            throw new Error('Données invalides');
          }
        } catch (error) {
          console.error('Error loading unite data:', error);
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#uniteLoadingScreen').hide();
          $('#uniteContentContainer').show();
        }
      } else {
        formUniteID = null;
        $('#unite_modtitle').text('Ajouter une unité');
        $('#unite_modbtn').text('Ajouter');
        $('#uniteLoadingText').text("Préparation du formulaire...");
        
        // Hide loading screen and show content faster for add mode
        setTimeout(() => {
          $('#uniteLoadingScreen').hide();
          $('#uniteContentContainer').show();
        }, 200);
      }
    });

    $('#addUniteModal').on('hide.bs.modal', function() {
      $('#FormUnite')[0].reset();
      setTimeout(()=> {
        $('#uniteLoadingScreen').show();
        $('#uniteContentContainer').hide();
      }, 200);
    });

    $('#FormUnite').on('submit', async function(event) {
      event.preventDefault();
      const form = this;
      const formData = new FormData(form);
      const url = formUniteID ? `./apis/unites.routes.php?id=${formUniteID}` : './apis/unites.routes.php';
      const submitBtn = $('#unite_modbtn');
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
          successAction(formUniteID ? 'Unité modifiée avec succès.' : 'Unité ajoutée avec succès.');
          $('#addUniteModal').modal('hide');
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