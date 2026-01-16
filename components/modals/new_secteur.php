<!-- modal -->
<div class="modal fade" id="addSecteurModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addSecteurModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="secteur_modtitle">Ajouter un secteur</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Fermer">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="secteurLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="secteurLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="secteurContentContainer" style="display: none;">
          <form action="" name="FormSecteur" id="FormSecteur" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="row mt-1 mx-0 px-0">
                <div class="col mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'secteur_code', 'secteur_codeFeedback', 'secteurs')" class="form-control" type="text" name="code" id="secteur_code" placeholder="Entrer le code"
                    required />
                  <div id="secteur_codeFeedback" class="invalid-feedback"></div>
                </div>

                <div id="sec_parent" class="col d-none">
                  <label class="form-label">Secteur*</label>
                  <select class="form-select" name="parent_id" id="parent_id">
                    <option value="0" selected>Selectionnez le secteur</option>
                    <?php if ($secteurs ?? []) : ?>
                      <?php foreach ($secteurs as $secteur): ?>
                        <option value="<?php echo $secteur['id'] ?>"><?php echo $secteur['name'] ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitulé*</label>
                  <input class="form-control" type="text" name="name" id="secteur_name" placeholder="Entrer le nom"
                    required />
                </div>
              </div>

              <div id="sec_organism" class="col-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Organisme</label>
                  <input class="form-control" type="text" name="organisme" id="secteur_organism" placeholder="Entrer l'organisme"/>
                </div>
              </div>

              <!-- <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Domaine*</label>
                  <input class="form-control" type="text" name="domaine" id="secteur_domaine" placeholder="Entrer le domaine"
                     />
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Source de données</label>
                  <input class="form-control" type="text" name="source" id="secteur_source" placeholder="Entrer la source"
                     />
                </div>
              </div> -->

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="secteur_description"
                    placeholder="Entrer une description" style="height: 60px"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Fermer">Annuler</button>
              <button type="submit" id="secteur_modbtn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formSecteurID = null;
  $(document).ready(function() {
    $('#addSecteurModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const secteur_id = $(event.relatedTarget).data('parent_id');
      const form = document.getElementById('FormSecteur');
      // Show loading screen and hide content
      $('#secteurLoadingScreen').show();
      $('#secteurContentContainer').hide();

      if (secteur_id) {
        form.parent_id.value = secteur_id;
        $('#sec_parent').removeClass('d-none');
        $('#sec_organism').addClass('d-none');
      } else {
        $('#sec_parent').addClass('d-none');
        $('#sec_organism').removeClass('d-none');
      }

      if (dataId) {
        formSecteurID = dataId;
        $('#secteur_modtitle').text('Modifier le secteur');
        $('#secteur_modbtn').text('Modifier');
        $('#secteurLoadingText').text("Chargement des données secteur...");

        try {
          const response = await fetch(`./apis/secteurs.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const data = await response.json();
          const secteurData = data.data;
          form.code.value = secteurData.code;
          form.name.value = secteurData.name;
          form.organisme.value = secteurData.organisme;
          // form.domaine.value = secteurData.domaine;
          // form.source.value = secteurData.source;
          form.description.value = secteurData.description;
          form.parent_id.value = secteurData.parent_id;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#secteurLoadingScreen').hide();
          $('#secteurContentContainer').show();
        }
      } else {
        formSecteurID = null;
        document.getElementById('secteur_modtitle').innerText = 'Ajouter un secteur';
        document.getElementById('secteur_modbtn').innerText = 'Ajouter';
        $('#secteurLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#secteurLoadingScreen').hide();
          $('#secteurContentContainer').show();
        }, 200);
      }
    });

    $('#addSecteurModal').on('hide.bs.modal', function() {
      setTimeout(() => {
        $('#secteurLoadingScreen').show();
        $('#secteurContentContainer').hide();
      }, 200);
      $('#FormSecteur')[0].reset();
    });

    $('#FormSecteur').on('submit', async function(event) {
      event.preventDefault();
      const form = $(this);
      const formData = new FormData(this);
      const url = formSecteurID ? `./apis/secteurs.routes.php?id=${formSecteurID}` : './apis/secteurs.routes.php';
      const submitBtn = $('#secteur_modbtn');
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
          $('#addSecteurModal').modal('hide');
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