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
          <form action="" class="row-border" enctype="multipart/form-data" name="FormCibleCMR" id="FormCibleCMR">
            <input type="hidden" name="indicateur_id" id="cible_indicateur_id">
            <input type="hidden" name="projet_id" id="cible_projet_id">

            <div class="overflow-auto" style="min-height: 300px; max-height: 400px;">
              <table class="table table-sm table-hover table-striped fs-12 table-bordered border-emphasis small" align="center">
               
              </table>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" id="cible_modbtn" class="btn btn-primary btn-sm my-0">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let cibleDataID = null;
  let cibleIndicID = null;
  let cibleProjetID = null;
  const cibleScenarios = <?= json_encode(listTypeScenario() ?? []); ?>;

  $(document).ready(function() {
    $('#newIndicateurCibleModal').on('shown.bs.modal', async function(event) {
      const indicateurId = $(event.relatedTarget).data('indicateur_id');
      const mesureId = $(event.relatedTarget).data('mesure_id');
      const projetId = $(event.relatedTarget).data('projet_id');
      const form = document.getElementById('FormCibleCMR');

      form.indicateur_id = indicateurId || "";
      form.projet_id = projetId || "";
      cibleIndicID = indicateurId || "";
      cibleProjetID = projetId || "";

      await loadDataCible(indicateurId);
      await loadProjetCible(projetId);
      await loadReferentielCible(indicateurId);
    });

    $('#newIndicateurCibleModal').on('hidden.bs.modal', function() {
      $('#FormCibleCMR')[0].reset();
      $('#cibleLoadingScreen').show();
      $('#cibleContentContainer').hide();
    });

    $('#FormCibleCMR').on('submit', async function(e) {
      e.preventDefault();

      const cibles = {};
      let hasData = false;

      $('#FormCibleCMR').find('[name^="cible["]').each(function() {
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

        cibles[scenarioKey][year] = { valeur: numValue, annee: year};
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
      formData.append('valeur_cibles', JSON.stringify(cibles));
      formData.append('indicateur_id', cibleIndicID);
      formData.append('projet_id', cibleProjetID);
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
          $('#newIndicateurCibleModal').modal('hide');
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

  async function loadDataCible(indicateurId) {
    $('#cibleLoadingScreen').show();
    $('#cibleContentContainer').hide();

    if (indicateurId) {
      $('#newIndicateurCibleModalLabel').text('Modifier les valeurs cibles');
      $('#cibleLoadingText').text("Chargement des données cibles...");

      try {
        const response = await fetch(`./apis/cibles.routes.php?indicateur_id=${indicateurId}`, {
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

      setTimeout(() => {
        $('#cibleLoadingScreen').hide();
        $('#cibleContentContainer').show();
      }, 200);
    }
  }

  async function loadProjetCible(projetId) {
    const selectAnnee = document.getElementById('suivi_annee');
    const tableBody = document.querySelector('table tbody');

    selectAnnee.innerHTML = '';
    tableBody.innerHTML = '';
    try {
      const response = await fetch(`./apis/projets.routes.php?id=${projetId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: 'GET',
      });

      const result = await response.json();
      if (result.status === 'success') {
        const projet = result.data;
        const startYear = new Date(projet.start_date).getFullYear();
        const endYear = new Date(projet.end_date).getFullYear();

        for (let year = startYear; year <= endYear; year++) {
          const option = document.createElement('option');
          option.value = year;
          option.textContent = year;
          selectAnnee.appendChild(option);
        }

        rebuildTable(startYear, endYear);
      }
    } catch (error) {
      console.error('Erreur lors du chargement du projet:', error);
      selectAnnee.innerHTML = '<option value="" disabled selected>Impossible de charger les données.</option>';
      tableBody.innerHTML = '<tr><td colspan="100%" class="text-center text-muted">Erreur de chargement</td></tr>';
    }
  }

  async function loadReferentielCible(referentielId) {
    const selectAnnee = document.getElementById('suivi_annee');
    const tableBody = document.querySelector('table tbody');
    
    selectAnnee.innerHTML = '';
    tableBody.innerHTML = '';
    try {
        const response = await fetch(`./apis/referentiels.routes.php?id=${referentielId}`, {
            headers: {
                'Authorization': `Bearer ${token}`
            },
            method: 'GET',
        });

        const result = await response.json();
        if (result.status === 'success') {
            const referentiel = result.data;
            let startYear, endYear;
            
            if (referentiel.annee_debut && referentiel.annee_fin) {
                startYear = parseInt(referentiel.annee_debut);
                endYear = parseInt(referentiel.annee_fin);
            } else {
                const currentYear = new Date().getFullYear();
                startYear = currentYear;
                endYear = currentYear + 4;
            }
            
            if (isNaN(startYear) || isNaN(endYear) || startYear > endYear) {
                console.warn('Années invalides, utilisation des valeurs par défaut');
                const currentYear = new Date().getFullYear();
                startYear = currentYear;
                endYear = currentYear + 4;
            }
            
            for (let year = startYear; year <= endYear; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                selectAnnee.appendChild(option);
            }
            
            rebuildTable(startYear, endYear);
        }
    } catch (error) {
        console.error('Erreur lors du chargement du référentiel:', error);
        selectAnnee.innerHTML = '<option value="" disabled selected>Impossible de charger les données.</option>';
        tableBody.innerHTML = '<tr><td colspan="100%" class="text-center text-muted">Erreur de chargement</td></tr>';
    }
}


  function rebuildTable(startYear, endYear) {
    const table = document.querySelector('.overflow-auto table');
    table.innerHTML = `
      <thead class="bg-primary-subtle">
          <tr>
              <th scope="col" class="fs-12 px-2 text-center" width="15%">Scénario</th>
              ${Array.from({length: endYear - startYear + 1}, (_, i) => `<th scope="col" class="fs-12 px-2 text-center">${startYear + i}</th>`).join('')}
          </tr>
      </thead>
      <tbody>
        ${Object.entries(cibleScenarios)
        .map(([key, scenario]) => `<tr>
        <td class="align-middle text-start px-2 text-nowrap fw-semibold" width="15%">${scenario}</td>
        ${Array.from({length: endYear - startYear + 1}, (_, i) => `<td class="align-middle text-center p-2">
        <input type="text" class="form-control py-2 px-1 rounded-1" name="cible[${key}][${startYear + i}]" style="min-width: 100px" id="cible-${key}-${startYear + i}" placeholder= "—">
        </td>`).join('')}</tr>`).join('')}
      </tbody>`;
  }
</script>