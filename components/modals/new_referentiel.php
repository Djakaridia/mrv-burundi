<div class="modal fade" id="addReferentielModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addReferentielModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-1">
        <h3 class="mb-0" id="referentiel_modtitle">Ajouter un referentiel</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="referentielLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="referentielLoadingText">Chargement en cours</h4>
        </div>

        <div id="referentielContentContainer" style="display: none;">
          <form class="row g-3" action="" method="post" id="FormReferentiel">
            <div class="col-md-4">
              <div class="form-floating">
                <input oninput="checkColumns('code', 'referentielCode', 'referentielCodeFeedback', 'referentiels')" class="form-control" name="code" id="referentielCode" type="text"
                  placeholder="Code du réferentiel" required>
                <label for="referentielCode">Code*</label>
                <div id="referentielCodeFeedback" class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-8">
              <div class="form-floating">
                <input class="form-control" name="intitule" id="referentielIntitule" type="text"
                  placeholder="Intitule du réferentiel" required>
                <label for="referentielIntitule">Intitule*</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" name="description" id="referentielDescription"
                  placeholder="Description du réferentiel" style="height: 50px"></textarea>
                <label for="referentielDescription">Description</label>
              </div>
            </div>

            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="categorie" id="referentielCategorie" required>
                  <option value="" selected disabled>Sélectionner une catégorie</option>
                  <?php foreach (listTypeCategorie() as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="referentielCategorie">Catégorie*</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="sens_evolution" id="referentielSensEvolution" required>
                  <option value="" selected disabled>Sélectionner un sens d'évolution</option>
                  <option value="asc">Ascendant</option>
                  <option value="desc">Descendant</option>
                </select>
                <label for="referentielSensEvolution">Sens d'évolution*</label>
              </div>
            </div>
            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="unite" id="referentielUnite" required>
                  <option value="" selected disabled>Sélectionner une unité</option>
                  <?php if ($unites ?? []) : ?>
                    <?php foreach ($unites as $unite): ?>
                      <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="referentielUnite">Unite*</label>
              </div>
            </div>

            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="echelle" id="referentielEchelle" required>
                  <option value="" selected disabled>Sélectionner une échelle</option>
                  <option value="nationale">Nationale</option>
                  <option value="provincial">Provincial</option>
                  <?php if ($zone_types ?? []) : ?>
                    <?php foreach ($zone_types as $zone_type): ?>
                      <option value="<?= $zone_type['id'] ?>"><?= $zone_type['name'] ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="referentielEchelle">Echelle*</label>
              </div>
            </div>
            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="modele" id="referentielModele" required>
                  <option value="" selected disabled>Sélectionner un modèle</option>
                  <?php foreach (listModeleTypologie() as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="referentielModele">Modèle*</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="fonction_agregation" id="referentielFonctionAgregation" required>
                  <option value="" selected disabled>Sélectionner une fonction</option>
                  <?php foreach (listModeAggregation() as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="referentielFonctionAgregation">Fonction d'agregation*</label>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="secteur_id" id="referentielSecteur" required>
                  <option value="" selected disabled>Sélectionner un secteur</option>
                  <?php if (!empty($secteurs)) : ?>
                    <?php foreach ($secteurs as $secteur): ?>
                      <option value="<?= $secteur['id'] ?? '' ?>"><?= $secteur['name'] ?? '' ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="referentielSecteur">Secteur*</label>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-floating">
                <select class="form-select" name="action" id="referentielAction">
                  <option value="" selected disabled>Sélectionner un sous secteur</option>
                  <?php if ($sous_secteurs ?? []) : ?>
                    <?php foreach ($sous_secteurs as $sous_sec): ?>
                      <option value="<?= $sous_sec['id'] ?>" data-parent="<?= $sous_sec['parent'] ?>">
                        <?= $sous_sec['name'] ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="referentielAction">Sous secteur</label>
              </div>
            </div>

            <div class="col-md-4 mb-2">
              <div class="form-floating">
                <select class="form-select" name="action_type" id="referentielActionType" required>
                  <option value="" selected disabled>Sélectionner une action</option>
                  <?php foreach (listTypeAction() as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="projetAction">Action type *</label>
              </div>
            </div>
            <div class="col-md-12 mt-0">
              <div class="form-group">
                <label for="referentielResponsable" class="form-label">Entité responsable*</label>
                <select class="form-select" name="responsable" id="referentielResponsable" required>
                  <option value="" selected disabled>Sélectionner un responsable</option>
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

            <div class="col-md-3">
              <div class="form-floating">
                <input class="form-control" name="annee_debut" id="referentielAnneeDebut" type="number"
                  placeholder="annee de debut">
                <label for="referentielAnneeDebut">Année de début</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating">
                <input class="form-control" name="annee_fin" id="referentielAnneeFin" type="number"
                  placeholder="annee de fin">
                <label for="referentielAnneeFin">Année de fin</label>
              </div>
            </div>

            <div class="col-md-3">
              <div class="form-floating">
                <input class="form-control" name="seuil_min" id="referentielSeuilMin" type="number"
                  placeholder="seuil minimum">
                <label for="referentielSeuilMin">Seuil minimum</label>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-floating">
                <input class="form-control" name="seuil_max" id="referentielSeuilMax" type="number"
                  placeholder="seuil maximum">
                <label for="referentielSeuilMax">Seuil maximum</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input class="form-control" name="norme" id="referentielNorme" type="text"
                  placeholder="Norme du referentiel">
                <label for="referentielNorme">Norme</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input" name="in_dashboard" type="checkbox" id="referentielInDashboard">
                <label class="form-check-label" for="referentielInDashboard">Afficher l'indicateur sur l'accueil</label>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="referentiel_modbtn">Créer le referentiel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let formReferentielID = null;
  var allSubSectors = [];

  $(document).ready(function() {
    initSelect2("#addReferentielModal", "referentielResponsable");

    $('#addReferentielModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormReferentiel');

      $('#referentielLoadingScreen').show();
      $('#referentielContentContainer').hide();

      if (dataId) {
        formReferentielID = dataId;
        $('#referentiel_modtitle').text('Modifier le referentiel');
        $('#referentiel_modbtn').text('Modifier');
        $('#referentielLoadingText').text("Chargement des données referentiel...");

        try {
          const response = await fetch(`./apis/referentiels.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.intitule.value = result.data.intitule;
          form.description.value = result.data.description;
          form.categorie.value = result.data.categorie;
          form.sens_evolution.value = result.data.sens_evolution;
          form.unite.value = result.data.unite;
          form.echelle.value = result.data.echelle;
          form.modele.value = result.data.modele;
          form.secteur_id.value = result.data.secteur_id;
          form.action.value = result.data.action;
          // form.responsable.value = result.data.responsable;
          form.action_type.value = result.data.action_type;
          form.fonction_agregation.value = result.data.fonction_agregation;
          form.seuil_min.value = result.data.seuil_min;
          form.seuil_max.value = result.data.seuil_max;
          form.annee_debut.value = result.data.annee_debut;
          form.annee_fin.value = result.data.annee_fin;
          form.norme.value = result.data.norme;
          form.in_dashboard.checked = result.data.in_dashboard == 1 ? true : false;

          $('#referentielResponsable').val(result.data.responsable).trigger('change');
          $('#referentielAction').attr('disabled', false);
        } catch (error) {
          errorAction('Erreur lors du chargement des données.');
        } finally {
          $('#referentielLoadingScreen').hide();
          $('#referentielContentContainer').show();
        }
      } else {
        formReferentielID = null;
        $('#referentiel_modtitle').text('Ajouter un referentiel');
        $('#referentiel_modbtn').text('Ajouter');
        $('#referentielLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#referentielLoadingScreen').hide();
          $('#referentielContentContainer').show();
        }, 200);
      }
    });

    $('#addReferentielModal').on('hide.bs.modal', function() {
      $('#FormReferentiel')[0].reset();
      $('#referentielResponsable').val("").trigger('change');
      setTimeout(() => {
        $('#referentielLoadingScreen').show();
        $('#referentielContentContainer').hide();
      }, 200);
    });

    $('#referentielAction option').each(function() {
      if ($(this).val()) {
        allSubSectors.push({
          value: $(this).val(),
          text: $(this).text(),
          parent: $(this).data('parent')
        });
      }
    });

    $('#referentielSecteur').on('change', function() {
      var selectedSectorId = $(this).val();
      var $subSectorSelect = $('#referentielAction');
      $subSectorSelect.val('');
      $subSectorSelect.find('option').not(':first').remove();

      if (selectedSectorId) {
        var filteredSubSectors = allSubSectors.filter(function(subSector) {
          return subSector.parent == selectedSectorId;
        });

        $.each(filteredSubSectors, function(index, subSector) {
          $subSectorSelect.append($('<option>', {
            value: subSector.value,
            text: subSector.text
          }));
        });
        $subSectorSelect.attr('disabled', false);
      } else {
        $subSectorSelect.attr('disabled', true);
      }
      $subSectorSelect.trigger('change');
    });
    $('#referentielAction').attr('disabled', true);

    $('#FormReferentiel').on('submit', async function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formReferentielID ? `./apis/referentiels.routes.php?id=${formReferentielID}` : './apis/referentiels.routes.php';
      const submitBtn = $('#referentiel_modbtn');

      formData.set('in_dashboard', $('#referentielInDashboard').is(':checked') ? 1 : 0);
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
          $('#addReferentielModal').modal('hide');
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