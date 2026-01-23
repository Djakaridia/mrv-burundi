<div class="modal fade" id="newIndicateurCibleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newIndicateurCibleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="newIndicateurCibleModalLabel">Nouvelle valeur cible annuelle</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>

      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="cibleLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="cibleLoadingText">Chargement en cours</h4>
        </div>

        <div id="cibleContentContainer" style="display: none;">
          <?php if (isset($project_curr)) : ?>
            <form action="" class="row-border" enctype="multipart/form-data" name="FormCible" id="FormCible">
              <input type="hidden" name="cmr_id" id="cible_cmr_id" value="<?= $cmr_curr['id']??"" ?>">
              <input type="hidden" name="projet_id" id="cible_projet_id" value="<?= $project_curr['id'] ?>">

              <div class="overflow-auto" style="min-height: 300px; max-height: 400px;">
                <table class="table table-sm table-hover table-striped fs-12 table-bordered border-emphasis" align="center">
                  <thead class="bg-primary-subtle">
                    <tr>
                      <th scope="col" class="fs-12 px-2 text-center" width="15%">Scénario</th>
                      <?php
                      $startYear = date('Y', strtotime($project_curr['start_date']));
                      $endYear = date('Y', strtotime($project_curr['end_date']));
                      for ($year = $startYear; $year <= $endYear; $year++) : ?>
                        <th scope="col" class="fs-12 px-2 text-center"><?= $year ?></th>
                      <?php endfor; ?>
                    </tr>
                  </thead>

                  <tbody>
                    <?php foreach (listTypeScenario() as $key => $scenario) : ?>
                      <tr>
                        <td class="align-middle text-start px-2" width="15%"><?= $scenario ?></td>
                        <?php for ($year = $startYear; $year <= $endYear; $year++) : ?>
                          <td class="align-middle text-center px-2">
                            <input type="number" step="any" class="form-control py-3" name="cible[<?= $key ?>][<?= $year ?>]" id="cible-<?= $key ?>-<?= $year ?>" placeholder="—">
                          </td>
                        <?php endfor; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" id="cible_modbtn" class="btn btn-primary btn-sm my-0">Enregistrer</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    const modal = $('#newIndicateurCibleModal');
    const form = $('#FormCible');

    modal.on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const cibleCmrid = $('#cible_cmr_id');

      $('#cibleLoadingScreen').show();
      $('#cibleContentContainer').hide();

      if (dataId) {
        cibleCmrid.val(dataId);
        $('#newIndicateurCibleModalLabel').text('Modifier les valeurs cibles');
        $('#cibleLoadingText').text("Chargement des données cibles...");

        try {
          const response = await fetch(`./apis/cibles.routes.php?cmr_id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();

          if (result.status === 'success' && result.data?.length) {
            result.data.forEach(item => {
              $(`#cible-${item.scenario}-${item.annee}`).val(item.valeur);
            });
          }
        } catch (error) {
          console.error('Erreur:', error);
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#cibleLoadingScreen').hide();
          $('#cibleContentContainer').show();
        }
      } else {
        $('#newIndicateurCibleModalLabel').text('Ajouter les valeurs cibles annuelles');
        $('#cibleLoadingText').text("Préparation du formulaire...");

        // Hide loading screen and show content faster for add mode
        setTimeout(() => {
          $('#cibleLoadingScreen').hide();
          $('#cibleContentContainer').show();
        }, 200);
      }
    });

    // Réinitialisation du modal à la fermeture
    modal.on('hidden.bs.modal', function() {
      form[0].reset();
      $('#cibleLoadingScreen').show();
      $('#cibleContentContainer').hide();
    });

    // Soumission du formulaire
    form.on('submit', async function(e) {
      e.preventDefault();

      const cibles = {};
      const inputs = $('#FormCible').find('[name^="cible["]');

      let hasData = false;

      inputs.each(function() {
        const name = this.name;
        const value = $(this).val().trim();

        if (value === '' || value === null || value === undefined) return;

        const numValue = parseFloat(value.replace(',', '.'));
        if (isNaN(numValue)) return;

        const matches = name.match(/cible\[([^\]]+)\]\[(\d{4})\]/);
        if (!matches || matches.length !== 3) return;

        const scenarioKey = matches[1];
        const year = parseInt(matches[2]);

        if (!cibles[scenarioKey]) cibles[scenarioKey] = {};

        cibles[scenarioKey][year] = {
          valeur: numValue,
          annee: year
        };

        hasData = true;
      });

      const totalCibles = Object.values(cibles).reduce((total, annees) => {
        return total + Object.keys(annees).length;
      }, 0);

      if (!hasData || totalCibles === 0) {
        errorAction('Veuillez saisir au moins une valeur.');
        return;
      }

      const formData = new FormData();
      formData.append('cmr_id', $('#cible_cmr_id').val());
      formData.append('projet_id', $('#cible_projet_id').val());
      formData.append('valeur_cibles', JSON.stringify(cibles));
      const submitBtn = $('#cible_modbtn');
      const originalText = submitBtn.text();
      submitBtn.prop('disabled', true).text('Enregistrement...');

      try {
        const response = await fetch('./apis/cibles.routes.php', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`
          },
          body: formData
        });

        const result = await response.json();
        if (result.status === 'success') {
          successAction('Données enregistrées avec succès');
          modal.modal('hide');
        } else {
          errorAction(result.message || 'Erreur lors de l’enregistrement');
        }
      } catch (error) {
        console.error('Erreur fetch :', error);
        errorAction('Erreur lors de l’envoi des données');
      } finally {
        submitBtn.prop('disabled', false).text(originalText);
      }
    });
  });
</script>