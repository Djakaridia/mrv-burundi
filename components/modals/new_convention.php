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

              <div class="col-lg-3 mt-1">
                <div class="mb-1">
                  <label class="form-label">Code*</label>
                  <input class="form-control" type="text" name="code" id="code_conven" placeholder="Entrer le code" required />
                  <div class="invalid-feedback" id="code_conven_feedback"></div>
                </div>
              </div>
              <div class="col-lg-9 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitulé*</label>
                  <input class="form-control" type="text" name="name" id="name_conven" placeholder="Entrer l'intitulé"
                    required />
                </div>
              </div>

              <div class="col-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Montant (USD)*</label>
                  <input class="form-control" type="number" name="montant" id="montant_conven"
                    placeholder="Entrer le montant" required />
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Bailleur*</label>
                  <select class="form-select" name="partenaire_id" id="partenaire_id_conven" required>
                    <option value="">Sélectionner un bailleur</option>
                    <?php if ($partenaires ?? []) : ?>
                      <?php foreach ($partenaires as $partenaire): ?>
                        <option value="<?= $partenaire['id'] ?>">
                          <?= $partenaire['description'] ? $partenaire['description'] . ' (' . $partenaire['sigle'] . ')' : $partenaire['sigle']; ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-4 mt-1">
                <div class="mb-1">
                  <label class="form-label">Secteur*</label>
                  <select class="form-select" name="secteur_id" id="secteur_id_conven" required>
                    <option value="">Sélectionner un secteur</option>
                    <?php if ($secteurs ?? []) : ?>
                      <?php foreach ($secteurs as $secteur): ?>
                        <option value="<?= $secteur['id'] ?>">
                          <?= $secteur['name']; ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-4 mt-1">
                <div class="mb-1">
                  <label class="form-label">Date d'accord</label>
                  <input class="form-control datetimepicker" type="text" name="date_accord" id="date_accord_conven"
                    placeholder="Entrer la date d'accord" data-options='{"dateFormat":"Y-m-d"}' />
                </div>
              </div>
              
              <div class="col-lg-4 mt-1">
                <div class="mb-1">
                  <label class="form-label">Instrument*</label>
                  <select class="form-select" name="instrument" id="instrument_conven" required>
                    <option value="">Sélectionner un instrument</option>
                    <?php foreach (listTypeFinancement() as $key => $instrument): ?>
                      <option value="<?= $key ?>"><?= $instrument; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-4 mt-1">
                <div class="mb-1">
                  <label class="form-label">Type de soutien*</label>
                  <select class="form-select" name="action_type" id="action_type_conven" required>
                    <option value="">Sélectionner un type</option>
                    <?php foreach (listTypeAction() as $key => $action): ?>
                      <option value="<?= $key ?>"><?= $action; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-8 mt-1">
                <div class="mb-1">
                  <label class="form-label">Projets / Actions*</label>
                  <select class="form-select" name="projet_id" id="projet_id_conven" required>
                    <option value="">Sélectionner un projet / action</option>
                    <?php if ($projets ?? []) : ?>
                      <?php foreach ($projets as $projet): ?>
                        <option value="<?= $projet['id'] ?>" data-action-type="<?= $projet['action_type'] ?>">
                          <?= $projet['code'] . " - " . $projet['name']; ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-4 px-0 pb-0">
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
  let allProjetSelect = [];

  $(document).ready(function() {
    initSelect2("#addConvenModal", "instrument_conven");
    initSelect2("#addConvenModal", "partenaire_id_conven");
    initSelect2("#addConvenModal", "projet_id_conven");

    $('#addConvenModal').on('shown.bs.modal', handleModalOpen);
    $('#addConvenModal').on('hide.bs.modal', handleModalClose);
    $('#action_type_conven').on('change', handleActionTypeChange);
    $('#FormConven').on('submit', handleFormSubmit);
    initializeProjetList();
  });

  async function handleModalOpen(event) {
    const button = $(event.relatedTarget);
    const dataId = button.data('id');
    const secteurId = button.data('secteur');
    const actionType = button.data('action');
    const projetId = button.data('projet');
    const form = document.getElementById('FormConven');

    if (actionType) form.action_type.value = actionType;
    if (secteurId) form.secteur_id.value = secteurId;
    if (projetId) $('#projet_id_conven').val(projetId).trigger('change');

    showLoadingScreen();

    if (dataId) {
      await loadConventionData(dataId, form);
    } else {
      resetFormConvention(form);
    }
  }

  async function loadConventionData(dataId, form) {
    formConvenID = dataId;
    updateModalTitleAndButton('Modifier');
    $('#conventionLoadingText').text("Chargement des données conventions...");

    try {
      const response = await fetch(`./apis/conventions.routes.php?id=${dataId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: 'GET',
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();

      if (result.status === 'success' && result.data) {
        populateConventionData(form, result.data);
      } else {
        throw new Error(result.message || 'Données invalides');
      }
    } catch (error) {
      console.error('Erreur de chargement:', error);
      errorAction('Impossible de charger les données.');
    } finally {
      hideLoadingScreen();
    }
  }

  function populateConventionData(form, data) {
    form.code.value = data.code || '';
    form.name.value = data.name || '';
    form.secteur_id.value = data.secteur_id || '';
    form.montant.value = data.montant || '';
    form.action_type.value = data.action_type || '';
    form.date_accord.value = data.date_accord || '';

    $('#instrument_conven').val(data.instrument || '').trigger('change');
    $('#partenaire_id_conven').val(data.partenaire_id || '').trigger('change');
    $('#projet_id_conven').val(data.projet_id || '').trigger('change');
  }

  function resetFormConvention(form) {
    formConvenID = null;
    updateModalTitleAndButton('Ajouter');
    $('#conventionLoadingText').text("Préparation du formulaire...");

    setTimeout(() => {
      hideLoadingScreen();
    }, 200);
  }

  function handleModalClose() {
    const form = $('#FormConven')[0];
    if (form) form.reset();

    $('#partenaire_id_conven, #projet_id_conven, #instrument_conven').val('').trigger('change');

    setTimeout(() => {
      showLoadingScreen();
    }, 200);
  }

  function handleActionTypeChange() {
    const selectedAction = $(this).val();
    const $projetSelect = $('#projet_id_conven');

    $projetSelect.val('').empty().append('<option value="">Sélectionner un projet / action</option>');

    if (selectedAction) {
      const filteredProjects = allProjetSelect.filter(projet =>
        projet.action_type == selectedAction
      );

      filteredProjects.forEach(projet => {
        $projetSelect.append($('<option>', {
          value: projet.value,
          text: projet.text
        }));
      });

      $projetSelect.prop('disabled', false);
    } else {
      $projetSelect.prop('disabled', true);
    }

    $projetSelect.trigger('change');
  }

  async function handleFormSubmit(event) {
    event.preventDefault();

    const formData = new FormData(this);
    const submitBtn = $('#conven_modbtn');
    const url = formConvenID ?
      `./apis/conventions.routes.php?id=${formConvenID}` :
      './apis/conventions.routes.php';

    setSubmitButtonState(submitBtn, true, 'Envoi en cours...');

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: "POST",
        body: formData
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();

      if (result.status === 'success') {
        successAction('Données envoyées avec succès.');
        $('#addConvenModal').modal('hide');
      } else {
        throw new Error(result.message || 'Erreur lors de l\'envoi des données.');
      }
    } catch (error) {
      console.error('Erreur de soumission:', error);
      errorAction(error.message || 'Erreur lors de l\'envoi des données.');
    } finally {
      setSubmitButtonState(submitBtn, false, 'Enregistrer');
    }
  }

  function initializeProjetList() {
    allProjetSelect = [];

    $('#projet_id_conven option').each(function() {
      const $this = $(this);
      const value = $this.val();

      if (value) {
        allProjetSelect.push({
          value: value,
          text: $this.text(),
          action_type: $this.data('action-type')
        });
      }
    });
  }

  function updateModalTitleAndButton(action) {
    $('#conven_modtitle').text(`${action} la convention`);
    $('#conven_modbtn').text(action);
  }

  function showLoadingScreen() {
    $('#conventionLoadingScreen').show();
    $('#conventionContentContainer').hide();
  }

  function hideLoadingScreen() {
    $('#conventionLoadingScreen').hide();
    $('#conventionContentContainer').show();
  }

  function setSubmitButtonState($button, disabled, text) {
    $button.prop('disabled', disabled);
    $button.text(text);
  }
</script>