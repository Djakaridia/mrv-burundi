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
              <div class="row mt-1 mx-0 px-0">
                <div class="col-lg-6 mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'actionPrio_code', 'actionPrio_codeFeedback', 'actions')" class="form-control" type="text" name="code" id="actionPrio_code" placeholder="Entrer le code"
                    required />
                  <div id="actionPrio_codeFeedback" class="invalid-feedback"></div>
                </div>

                <div class="col-lg-6">
                  <label class="form-label">Sous Secteur*</label>
                  <select class="form-select" name="secteur_id" id="actionPrio_secteur_id">
                    <option value="0" selected disabled>Selectionnez le sous secteur</option>
                    <?php if (!empty($sous_secteurs)) : ?>
                      <?php foreach ($sous_secteurs as $sous_secteur): ?>
                        <option value="<?php echo $sous_secteur['id']??"" ?>"><?php echo $sous_secteur['name']??"" ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitulé*</label>
                  <input class="form-control" type="text" name="name" id="actionPrio_name" placeholder="Entrer le nom"
                    required />
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Objectifs</label>
                  <textarea class="form-control" name="objectif" id="actionPrio_objectif" placeholder="Entrer les objectifs"></textarea>
                </div>
              </div>
              
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="actionPrio_description" placeholder="Entrer une description" style="height: 60px"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Fermer">Annuler</button>
              <button type="submit" id="actionPrio_modbtn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formActionPrioID = null;
  $(document).ready(function() {
    $('#addActionPrioModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const parent = $(event.relatedTarget).data('parent');
      const form = document.getElementById('FormActionPrio');
      $('#actionPrioLoadingScreen').show();
      $('#actionPrioContentContainer').hide();

      if(parent) form.secteur_id.value = parent;

      if (dataId) {
        formActionPrioID = dataId;
        $('#actionsPrio_modtitle').text('Modifier l\'action prioritaire');
        $('#actionPrio_modbtn').text('Modifier');
        $('#actionPrioLoadingText').text("Chargement des données action prioritaire...");

        try {
          const response = await fetch(`./apis/actions.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const data = await response.json();
          const actionPrioData = data.data;
          form.code.value = actionPrioData.code;
          form.name.value = actionPrioData.name;
          form.objectif.value = actionPrioData.objectif;
          form.description.value = actionPrioData.description;
          form.secteur_id.value = actionPrioData.secteur_id;
        } catch (error) {
          console.error(error);
          errorAction('Impossible de charger les données.');
        } finally {
          $('#actionPrioLoadingScreen').hide();
          $('#actionPrioContentContainer').show();
        }
      } else {
        formActionPrioID = null;
        document.getElementById('actionsPrio_modtitle').innerText = 'Ajouter une action prioritaire';
        document.getElementById('actionPrio_modbtn').innerText = 'Ajouter';
        $('#actionPrioLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#actionPrioLoadingScreen').hide();
          $('#actionPrioContentContainer').show();
        }, 200);
      }
    });

    $('#addActionPrioModal').on('hide.bs.modal', function() {
      setTimeout(() => {
        $('#actionPrioLoadingScreen').show();
        $('#actionPrioContentContainer').hide();
      }, 200);
      $('#FormActionPrio')[0].reset();
    });

    $('#FormActionPrio').on('submit', async function(event) {
      event.preventDefault();
      const form = $(this);
      const formData = new FormData(this);
      const url = formActionPrioID ? `./apis/actions.routes.php?id=${formActionPrioID}` : './apis/actions.routes.php';
      const submitBtn = $('#actionPrio_modbtn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

      try {
        const response = await fetch(url, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: "POST",
          body: formData
        });

        const result = await response.json();
        if (result.status === 'success') {
          successAction(result.message);
          $('#addActionPrioModal').modal('hide');
        } else {
          errorAction(result.message);
        }
      } catch (error) {
        errorAction('Erreur lors de la soumission du formulaire.');
      } finally {
        submitBtn.prop('disabled', false);
        submitBtn.text('Enregistrer');
      }
    })
  });
</script>