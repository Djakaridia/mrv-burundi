<div class="modal fade" id="addRapportPeriodeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addRapportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="rapport_modtitle">Nouveau Rapport Périodique</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="rapportPerLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="rapportPerLoadingText">Chargement en cours</h4>
        </div>

        <div id="rapportPerContentContainer" style="display: none;">
          <form class="row g-3" action="" method="post" id="FormRapportPeriode">
            <!-- Code du rapport -->
            <div class="col-md-4">
              <div class="form-floating">
                <input oninput="checkColumns('code', 'rapportCode', 'rapportCodeFeedback', 'rapports_periode')" class="form-control" name="code" id="rapportCode" type="text" placeholder="Code du rapport" required>
                <label for="rapportCode">Code du rapport*</label>
                <div id="rapportCodeFeedback" class="invalid-feedback"></div>
              </div>
            </div>
            <!-- Intitule du rapport -->
            <div class="col-md-8">
              <div class="form-floating">
                <input class="form-control" name="intitule" id="rapportIntitule" type="text" placeholder="Intitule du rapport" required>
                <label for="rapportIntitule">Intitule du rapport*</label>
              </div>
            </div>

            <!-- Projet -->
            <div class="col-md-12">
              <div class="form-floating">
                <select class="form-select" name="projet_id" id="rapportProjet" required>
                  <option value="" selected disabled>Sélectionner un projet</option>
                  <?php if ($projets ?? []) : ?>
                    <?php foreach ($projets as $projet) : ?>
                      <option value="<?= $projet['id'] ?>"><?= $projet['name'] ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="rapportProjet">Projet*</label>
              </div>
            </div>
            <!-- Périodicité -->
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="periode" id="rapportPeriode" required>
                  <option value="" selected disabled>Sélectionner une periodicité</option>
                  <?php foreach (listPeriodicite() as $key => $value): ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="rapportPeriode">Périodicité*</label>
              </div>
            </div>
            <!-- Mois de référence -->
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="mois_ref" id="rapportObjectif" required>
                  <option value="" selected disabled>Sélectionner un mois</option>
                  <?php foreach (listMois() as $key => $m): ?>
                    <option value="<?= $key ?>"><?= $m ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="rapportObjectif">Mois de référence*</label>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="annee_ref" id="rapportRefAnnee">
                  <option value="" selected disabled>Sélectionner une année</option>
                  <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                  <?php endfor; ?>
                </select>
                <label for="rapportRefAnnee">Année de référence*</label>
              </div>
            </div>

            <!-- Description -->
            <div class="col-md-12">
              <div class="form-floating">
                <textarea class="form-control" name="description" id="rapportDescription" placeholder="Description de l'rapport" style="height: 70px"></textarea>
                <label for="rapportDescription">Description</label>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 px-0 pb-0">
              <input type="hidden" name="status" id="rapportStatus" value="Planifié">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="rapport_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let formrapportID = null;
  $(document).ready(function() {
    $('#addRapportPeriodeModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormRapportPeriode');

      $('#rapportPerLoadingScreen').show();
      $('#rapportPerContentContainer').hide();

      if (dataId) {
        formrapportID = dataId;
        $('#rapport_modtitle').text('Modifier le rapport');
        $('#rapport_modbtn').text('Modifier');
        $('#rapportPerLoadingText').text("Chargement des données rapport...");

        try {
          const response = await fetch(`./apis/rapports_periode.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.intitule.value = result.data.intitule;
          form.annee_ref.value = result.data.annee_ref;
          form.mois_ref.value = result.data.mois_ref;
          form.periode.value = result.data.periode;
          form.projet_id.value = result.data.projet_id;
          form.description.value = result.data.description;
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          $('#rapportPerLoadingScreen').hide();
          $('#rapportPerContentContainer').show();
        }
      } else {
        formrapportID = null;
        $('#rapport_modtitle').text('Ajouter un rapport');
        $('#rapport_modbtn').text('Ajouter');
        $('#rapportPerLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#rapportPerLoadingScreen').hide();
          $('#rapportPerContentContainer').show();
        }, 200);
      }
    });

    $('#addRapportPeriodeModal').on('hide.bs.modal', function() {
      $('#FormRapportPeriode')[0].reset();
      setTimeout(()=> {
        $('#rapportPerLoadingScreen').show();
        $('#rapportPerContentContainer').hide();
      }, 200);
    });


    $('#FormRapportPeriode').on('submit', async function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formrapportID ? `./apis/rapports_periode.routes.php?id=${formrapportID}` : './apis/rapports_periode.routes.php';
      const submitBtn = $('#rapport_modbtn');
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
          $('#addRapportPeriodeModal').modal('hide');
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