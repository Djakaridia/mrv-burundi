<div class="modal fade" id="addGroupMenbre" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addGroupMenbre" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="groupe_menbre_modtitle">Ajouter un menbre</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div class="row g-4">
          <div id="groupeMenbreLoadingScreen" class="text-center py-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
              <span class="visually-hidden">Chargement...</span>
            </div>
            <h4 class="mt-3 fw-bold text-primary" id="groupeLoadingText">Chargement en cours</h4>
          </div>

          <!-- Content Container (initially hidden) -->
          <div id="groupeMenbreContentContainer" style="display: none;">
            <form action="" name="FormGroupeMenbre" id="FormGroupeMenbre" method="POST" enctype="multipart/form-data">
              <input type="hidden" value="<?= $group_id??''; ?>" name="groupe_id">
              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Responsable*</label>
                  <select class="form-select" name="user_id" id="user_id" required>
                    <option value="">Sélectionner un responsable</option>
                    <?php if ($users_no_in_group ?? []) : ?>
                      <?php foreach ($users_no_in_group as $user) { ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['nom']." ".$user['prenom']; ?></option>
                      <?php } ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
              <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                  aria-label="Close">Annuler</button>
                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="groupe_menbre_modbtn">Ajouter </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formGroupeMenbreID = null;
  $(document).ready(function () {
    $('#addGroupMenbre').on('shown.bs.modal', async function (event) {
      const form = document.getElementById('FormGroupeMenbre');
      // Show loading screen and hide content
      $('#groupeMenbreLoadingScreen').show();
      $('#groupeMenbreContentContainer').hide();
      formGroupeMenbreID = null;
        
        setTimeout(() => {
          $('#groupeMenbreLoadingScreen').hide();
          $('#groupeMenbreContentContainer').show();
        }, 200);
    });

    $('#addGroupMenbre').on('hide.bs.modal', function () {
      setTimeout(()=> {
        $('#groupeMenbreLoadingScreen').show();
        $('#groupeMenbreContentContainer').hide();
      }, 200);
      $('#FormGroupeMenbre')[0].reset();
    });

    $('#FormGroupeMenbre').on('submit', async function (event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = './apis/groupe_users.routes.php';
      const submitBtn = $('#groupe_menbre_modbtn');
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
          $('#addGroupMenbre').modal('hide');
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