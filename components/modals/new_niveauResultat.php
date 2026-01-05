<div class="modal fade" id="addObjNiveauModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addObjNiveauModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="obj_objectif_modtitle">Ajouter un résultat</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="niveauResultatLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="niveauResultatLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="niveauResultatContentContainer" style="display: none;">
          <form action="" name="FormObjNiveau" id="FormObjNiveau" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Niveau de résultat*</label>
                  <select class="form-select form-select-sm rounded-1" name="niveau" id="obj_niveau_niveau" required>
                    <option value="">Sélectionner le niveau</option>
                    <?php if ($niveaux ?? []) : ?>
                      <?php foreach ($niveaux as $niveau) { ?>
                        <option value="<?php echo $niveau['id']; ?>"><?php echo $niveau['name']; ?></option>
                      <?php } ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'obj_niveau_code', 'obj_niveau_codeFeedback', 'niveaux_resultats')" class="form-control" type="text" name="code" id="obj_niveau_code" placeholder="Entrer le code"
                    required />
                  <div id="obj_niveau_codeFeedback" class="invalid-feedback"></div>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitule*</label>
                  <textarea class="form-control" name="name" id="obj_niveau_name" placeholder="Entrer l'intitule"
                    style="height: 60px" required></textarea>
                </div>
              </div>
            </div>
            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <input type="hidden" name="programme" id="obj_objectif_programme" value="" />
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="obj_objectif_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formObjNiveauID = null;
  $(document).ready(function() {
    $('#addObjNiveauModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const programme = $(event.relatedTarget).data('programme');
      const form = document.getElementById('FormObjNiveau');

      // Show loading screen and hide content
      $('#niveauResultatLoadingScreen').show();
      $('#niveauResultatContentContainer').hide();

      if (programme) {
        form.programme.value = programme;
      }

      if (dataId) {
        formObjNiveauID = dataId;
        $('#obj_objectif_modtitle').text('Modifier un résultat');
        $('#obj_objectif_modbtn').text('Modifier');
        $('#niveauResultatLoadingText').text("Chargement des données niveau...");

        try {
          const response = await fetch(`./apis/niveaux_resultats.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.name.value = result.data.name;
          form.niveau.value = result.data.niveau;
          form.programme.value = result.data.programme;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#niveauResultatLoadingScreen').hide();
          $('#niveauResultatContentContainer').show();
        }
      } else {
        formObjNiveauID = null;
        $('#obj_objectif_modtitle').text('Ajouter un résultat');
        $('#obj_objectif_modbtn').text('Ajouter');
        $('#niveauResultatLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#niveauResultatLoadingScreen').hide();
          $('#niveauResultatContentContainer').show();
        }, 200);
      }
    });

    $('#addObjNiveauModal').on('hide.bs.modal', function() {
      setTimeout(() => {
        $('#niveauResultatLoadingScreen').show();
        $('#niveauResultatContentContainer').hide();
      }, 200);
      $('#FormObjNiveau')[0].reset();
    });

    $('#FormObjNiveau').on('submit', async function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formObjNiveauID ? `./apis/niveaux_resultats.routes.php?id=${formObjNiveauID}` : './apis/niveaux_resultats.routes.php';
      const submitBtn = $('#obj_objectif_modbtn');
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
          successAction('Données envoyées avec succès.');
          $('#addObjNiveauModal').modal('hide');
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