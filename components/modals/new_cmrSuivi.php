<div class="modal fade" id="newIndicateurSuiviModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newIndicateurSuiviModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0">
        <h3 class="mb-0" id="suivi_modtitle">Valeurs suivi annuelles</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>

      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="suiviCMRLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="suiviCMRLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="suiviCMRContentContainer" style="display: none;">
          <div id="suiviCMRTableContent" class="card rounded-1 bg-white dark__bg-dark">
            <div class="card-header d-flex justify-content-between align-items-center p-2">
              <h5 class="mb-0">Liste des valeurs suivies</h5>
              <button class="btn btn-sm btn-primary" onclick="showSuiviForm()">
                <span class="fas fa-plus me-2"></span>Nouvelle valeur
              </button>
            </div>

            <div class="card-body p-2" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
              <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" align="center">
                <thead class="bg-light">
                  <tr>
                    <th scope="col" class="text-start px-2">Scénario</th>
                    <th scope="col" class="text-center">Année</th>
                    <th scope="col" class="text-center">Valeur</th>
                    <th scope="col" class="text-center">Date</th>
                    <th scope="col" class="text-center">Observation</th>
                    <th scope="col" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="viewSuiviCMRBody"></tbody>
              </table>
            </div>
          </div>

          <div id="suiviCMRFormContent" class="d-none">
            <form action="" class="row" enctype="multipart/form-data" name="FormSuiviCRM" id="FormSuiviCRM">
              <input type="hidden" name="projet_id" id="suivi_projet_id" value="">
              <input type="hidden" name="cmr_id" id="suivi_cmr_id" value="">

              <div class="col-12 mt-1">
                <div class="row">
                  <div class="col-md" id="echelleContainerSuivi"></div>
                  <div class="col-md" id="classeContainerSuivi"></div>
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Scénario suivie*</label>
                  <select class="form-select" name="scenario" id="suivi_scenario" required>
                    <option value="" disabled selected>Sélectionner un scénario</option>
                      <?php foreach (listTypeScenario() as $key => $scenario) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $scenario; ?></option>
                      <?php } ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Année suivie*</label>
                  <select class="form-select" name="annee" id="suivi_annee" required>
                    <option value="" disabled selected>Sélectionner une année</option>
                  </select>
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Valeur suivie*</label>
                  <input class="form-control" type="text" name="valeur" id="suivi_valeur"
                    placeholder="Entrer la valeur suivie" required />
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Date suivie*</label>
                  <input class="form-control datetimepicker" id="suivi_date_suivi" type="text"
                    name="date_suivie" placeholder="YYYY-MM-DD"
                    data-options="{&quot;disableMobile&quot;:true}" required />
                </div>
              </div>

              <div class="col-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Observation</label>
                  <textarea class="form-control" name="observation" id="suivi_observation"
                    placeholder="Entrer l'observation"></textarea>
                </div>
              </div>

              <div class="modal-footer d-flex justify-content-between border-0 pt-3 pb-0">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelSuiviForm()">Annuler</button>
                <button name="submit" type="submit" id="suivi_modbtn" class="btn btn-primary btn-sm px-3 my-0"> Ajouter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let suiviCMRId = null;
  let indicCMRId = null;
  const suiviScenarios = <?php echo json_encode(listTypeScenario() ?? []); ?>;
  const suiviProvinces = Object.values(<?php echo json_encode($provinces ?? []); ?>);
  const suiviZones = Object.values(<?php echo json_encode($zones ?? []); ?>);
  const suiviTypologies = Object.values(<?php echo json_encode($typologies ?? []); ?>);

  $(document).ready(function() {
    $('#newIndicateurSuiviModal').on('shown.bs.modal', async function(event) {
      const cmrId = $(event.relatedTarget).data('cmr_id');
      const projetId = $(event.relatedTarget).data('projet_id');
      const referentielId = $(event.relatedTarget).data('referentiel_id');
      const form = document.getElementById('FormSuiviCRM');

      form.cmr_id.value = cmrId || "";
      form.projet_id.value = projetId || "";
      indicCMRId = cmrId || "";

      await loadSuivisCMR();
      await loadProjetCMR(projetId);
      await loadReferentielCMR(referentielId);
    })

    $('#newIndicateurSuiviModal').on('hidden.bs.modal', function() {
      $('#FormSuiviCRM')[0].reset();
      $('#viewSuiviCMRBody').html('');
      $('#suiviCMRLoadingScreen').show();
      $('#suiviCMRContentContainer').hide();
      indicCMRId = null;
      cancelSuiviForm()
    });

    $('#FormSuiviCRM').on('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      await saveCMRSuivi(suiviCMRId, formData, 'no-reload');
    });

    $('.FormEditSuiviCMR').on('submit', async function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const suiviId = $(this).find('input[name="id"]').val();
      await saveCMRSuivi(suiviId, formData, 'reload');
    });
  })

  async function loadSuivisCMR() {
    const tbody = $('#viewSuiviCMRBody');
    tbody.html('');

    if (indicCMRId) {
      $('#suiviCMRLoadingScreen').show();
      $('#suiviCMRContentContainer').hide();
      $('#suiviCMRLoadingText').text("Chargement des typologies...");

      try {
        const response = await fetch(`./apis/suivis.routes.php?crm_id=${indicCMRId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: 'GET',
        });

        const result = await response.json();
        if (result.status === 'success' && result.data.length > 0) {
          result.data
            .sort((a, b) => b.annee - a.annee)
            .forEach(element => {
              tbody.append(`
            <tr class="align-middle">
              <td class="text-start px-2">${suiviScenarios[element.scenario]}</td>
              <td class="text-center">${element.annee}</td>
              <td class="text-center">${element.valeur}</td>
              <td class="text-center">${element.date_suivie}</td>
              <td class="text-center">${element.observation}</td>
              <td class="text-center d-flex justify-content-center align-items-center gap-2">
              <?php if (checkPermis($db, 'update')) : ?>
                <button type="button" onclick="editCMRSuivi('${element.id}')" 
                  class="btn btn-icon btn-phoenix-primary btn-sm fs-9">
                  <i class="fas fa-edit"></i>
                </button>
              <?php endif; ?>
              <?php if (checkPermis($db, 'delete')) : ?>
                <button type="button" onclick="deleteCMRSuivi('${element.id}')" 
                  class="btn btn-icon btn-phoenix-danger btn-sm fs-9">
                  <i class="fas fa-trash"></i>
                </button>
              <?php endif; ?>
              </td>
            </tr>`);
            });
        } else {
          tbody.html('<tr><td colspan="6" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
        }
      } catch (error) {
        tbody.html('<tr><td colspan="6" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
      } finally {
        $('#suiviCMRLoadingScreen').hide();
        $('#suiviCMRContentContainer').show();
      }
    } else {
      tbody.html('<tr><td colspan="6" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
      $('#suiviCMRLoadingScreen').hide();
      $('#suiviCMRContentContainer').show();
    }
  }

  async function loadProjetCMR(projetId) {
    const selectAnnee = document.getElementById('suivi_annee');
    selectAnnee.innerHTML = '';
    try {
      const response = await fetch(`./apis/projets.routes.php?id=${projetId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: 'GET',
      });

      const result = await response.json();
      if (result.status === 'success') {
        const start_date = result.data.start_date;
        const end_date = result.data.end_date;
        const start_year = new Date(start_date).getFullYear();
        const end_year = new Date(end_date).getFullYear();
        for (let i = start_year; i <= end_year; i++) {
          const option = document.createElement('option');
          option.value = i;
          option.textContent = i;
          selectAnnee.appendChild(option);
        }
      }
    } catch (error) {
      selectAnnee.innerHTML = '<option value="" disabled selected>Impossible de charger les données.</option>';
    }
  }

  async function loadReferentielCMR(referentielId) {
    try {
      const response = await fetch(`./apis/referentiels.routes.php?id=${referentielId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: 'GET',
      });

      const result = await response.json();
      if (result.status === 'success') {
        configEchelle(result.data.echelle);
        configTypologie(result.data.modele, result.data.id);
      }
    } catch (error) {
      console.error(error);
      configEchelle('nationale');
      configTypologie('valeur_relative', null);
    }
  }

  async function editCMRSuivi(dataId) {
    $('#suiviCMRLoadingScreen').show();
    $('#suiviCMRContentContainer').hide();
    const form = document.getElementById('FormSuiviCRM');
    suiviCMRId = dataId;

    if (dataId) {
      $('#suivi_modtitle').text('Modifier les valeurs suivies annuelles');
      $('#suivi_modbtn').text('Modifier');
      $('#suiviLoadingText').text("Chargement des données suivies...");
      showSuiviForm();

      try {
        const response = await fetch(`./apis/suivis.routes.php?id=${dataId}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: 'GET',
        });

        const result = await response.json();
        if (result.status === 'success') {
          form.annee.value = result.data.annee;
          form.date_suivie.value = result.data.date_suivie;
          form.valeur.value = result.data.valeur;
          form.observation.value = result.data.observation;
          form.scenario.value = result.data.scenario;
          form.cmr_id.value = result.data.cmr_id;
          form.projet_id.value = result.data.projet_id;
          if (form.echelle) form.echelle.value = result.data.echelle;
          if (form.classe) form.classe.value = result.data.classe;
        }
      } catch (error) {
        console.error(error);
        errorAction('Impossible de charger les données.');
      } finally {
        $('#suiviCMRLoadingScreen').hide();
        $('#suiviCMRContentContainer').show();
      }
    } else {
      cancelSuiviForm();
    }
  }

  async function saveCMRSuivi(suiviId, formData, action) {
    const url = suiviId ? './apis/suivis.routes.php?id=' + suiviId : './apis/suivis.routes.php';

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
        successAction('Données envoyées avec succès.', action);
      } else {
        errorAction(result.message || 'Erreur lors de l\'envoi des données.');
      }
    } catch (error) {
      errorAction('Erreur lors de l\'envoi des données: ' + error.message);
    } finally {
      cancelSuiviForm();
    }
  }

  async function deleteCMRSuivi(id) {
    deleteData(id, 'Êtes-vous sûr de vouloir supprimer ce suivi ?', 'suivis', 'none')
      .then(() => {
        loadSuivisCMR();
      })
      .catch(error => {
        errorAction('Erreur lors de la suppression');
      })
  }

  function configEchelle(echelle) {
    $('#echelleContainerSuivi').html('');
    switch (echelle) {
      case 'provincial':
        $('#echelleContainerSuivi').html(`
          <div class="mb-1">
              <label class="form-label">Province*</label>
              <select class="form-select" name="echelle" id="echelle_province_id" required>
                  <option value="" selected disabled>Selectionner une province</option>
                  ${suiviProvinces.map(province => `<option value="${province.code}">${province.name}</option>`).join('')}
              </select>
          </div>`);
        break;
      case 'nationale':
        $('#echelleContainerSuivi').addClass('d-none');
        break;
      default:
        $('#echelleContainerSuivi').html(`
          <div class="mb-1">
              <label class="form-label">Zone*</label>
              <select class="form-select" name="echelle" id="echelle_zone_id" required>
                  <option value="" selected disabled>Selectionner une zone</option>
                  ${suiviZones
                  .filter(zone => zone.type_id == echelle)
                  .map(zone => `<option value="${zone.name}">${zone.name}</option>`).join('')}
              </select>
          </div>`);
        break;
    }
  }

  function configTypologie(modele, referentielId) {
    $('#classeContainerSuivi').html('');
    switch (modele) {
      case 'valeur_relative' || 'typo_qualitative':
        $('#classeContainerSuivi').html(`
          <div class="mb-1">
              <label class="form-label">Classe*</label>
              <select class="form-select" name="classe" id="classe_qualitative_id" required>
                  <option value="" selected disabled>Selectionner une classe</option>
                  ${suiviTypologies
                  .filter(typologie => typologie.referentiel_id == referentielId)
                  .map(typologie => `<option value="${typologie.name.toLowerCase()}">${typologie.name}</option>`).join('')}
              </select>
          </div>`);
        break;
      case 'typo_quantitative':
        $('#classeContainerSuivi').html(`
          <div class="mb-1">
              <label class="form-label">Classe*</label>
              <select class="form-select" name="classe" id="classe_quantitative_id" required>
                  <option value="" selected disabled>Selectionner une classe</option>
                  <option value="rurale">Rurale</option>
                  <option value="urbaine">Urbaine</option>
              </select>
          </div>`);
        break;
      default:
        $('#classeContainerSuivi').addClass('d-none');
        break;
    }
  }

  function cancelSuiviForm() {
    $('#FormSuiviCRM')[0].reset();
    $('#suiviCMRFormContent').addClass('d-none');
    $('#suiviCMRTableContent').removeClass('d-none');
    $('#suivi_modtitle').text('Valeurs suivies annuelles');
    $('#suivi_modbtn').text('Ajouter');
    suiviCMRId = null;
    loadSuivisCMR();
  }

  function showSuiviForm() {
    $('#suiviCMRTableContent').addClass('d-none');
    $('#suiviCMRFormContent').removeClass('d-none');
  }
</script>
</script>