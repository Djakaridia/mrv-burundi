<!-- modal -->
<div class="modal fade" id="addConvenModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addConvenModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="conven_modtitle">Ajouter une convention</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
            class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <div id="conventionLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="conventionLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="conventionContentContainer" style="display: none;">
          <form action="" name="FormConven" id="FormConven" method="POST" enctype="multipart/form-data">
            <div class="row g-4">

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Code*</label>
                  <input class="form-control" type="text" name="code" id="code_conven" placeholder="Entrer le code" required />
                  <div class="invalid-feedback" id="code_conven_feedback"></div>
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitulé*</label>
                  <input class="form-control" type="text" name="name" id="name_conven" placeholder="Entrer l'intitulé"
                    required />
                </div>
              </div>

              <div class="col-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Montant*</label>
                  <input class="form-control" type="number" name="montant" id="montant_conven"
                    placeholder="Entrer le montant" required />
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Bailleur*</label>
                  <select class="form-select" name="structure_id" id="structure_id_conven" required>
                    <option value="">Sélectionner un bailleur</option>
                    <?php if ($structures ?? []) : ?>
                      <?php foreach ($structures as $structure): ?>
                        <option value="<?= $structure['id'] ?>">
                          <?= $structure['description'] ? $structure['description'] . ' (' . $structure['sigle'] . ')' : $structure['sigle']; ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Date d'accord</label>
                  <input class="form-control datetimepicker" type="text" name="date_accord" id="date_accord_conven"
                    placeholder="Entrer la date d'accord" data-options='{"dateFormat":"Y-m-d"}' />
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <input type="hidden" name="projet_id" id="projet_id_conven" value="<?php echo $project_curr['id'] ?? ''; ?>">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="conven_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let formConvenID = null;
  $(document).ready(function() {
    initSelect2("#addConvenModal", "structure_id_conven");

    $('#addConvenModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormConven');
      // Show loading screen and hide content
      $('#conventionLoadingScreen').show();
      $('#conventionContentContainer').hide();
      if (dataId) {
        formConvenID = dataId;
        $('#conven_modtitle').text('Modifier la convention');
        $('#conven_modbtn').text('Modifier');
        $('#conventionLoadingText').text("Chargement des données conventions...");

        try {
          const response = await fetch(`./apis/conventions.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.name.value = result.data.name;
          // form.structure_id.value = result.data.structure_id;
          form.projet_id.value = result.data.projet_id;
          form.montant.value = result.data.montant;
          form.date_accord.value = result.data.date_accord;

          $('#structure_id_conven').val(result.data.structure_id).trigger('change');
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#conventionLoadingScreen').hide();
          $('#conventionContentContainer').show();
        }
      } else {
        formConvenID = null;
        $('#conven_modtitle').text('Ajouter une convention');
        $('#conven_modbtn').text('Ajouter');
        $('#conventionLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#conventionLoadingScreen').hide();
          $('#conventionContentContainer').show();
        }, 200);
      }
    });

    $('#addConvenModal').on('hide.bs.modal', function() {
      $('#FormConven')[0].reset();
      $('#structure_id_conven').val("").trigger('change');
      setTimeout(() => {
        $('#conventionLoadingScreen').show();
        $('#conventionContentContainer').hide();
      }, 200);
    });

    $('#FormConven').on('submit', async function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formConvenID ? `./apis/conventions.routes.php?id=${formConvenID}` : './apis/conventions.routes.php';
      const submitBtn = $('#conven_modbtn');
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
          $('#addConvenModal').modal('hide');
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