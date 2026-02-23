<div class="modal fade" id="addIndicateurModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addIndicateurModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="indicateur_modtitle">Ajouter un indicateur</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body p-0">
        <!-- Loading Screen -->
        <div id="cmrLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="cmrLoadingText">Chargement en cours</h4>
        </div>

        <div id="cmrContentContainer" style="display: none;">
          <form class="row g-3" action="" method="post" id="FormIndicateur">
            <!-- Code de l'indicateur -->
            <div class="col-md-4">
              <div class="form-floating">
                <input class="form-control" name="code" id="indicateurCode" type="text" placeholder="Code de l'indicateur" required>
                <label for="indicateurCode">Code de l'indicateur*</label>
                <div class="invalid-feedback" id="indicateurCodeFeedback"></div>
              </div>
            </div>
            <!-- Intitule de l'indicateur -->
            <div class="col-md-8">
              <div class="form-floating">
                <input class="form-control" name="intitule" id="indicateurIntitule" type="text" placeholder="Intitule de l'indicateur" required>
                <label for="indicateurIntitule">Intitule de l'indicateur*</label>
              </div>
            </div>

            <!-- Referentiel -->
            <div class="col-12 mt-0">
              <div class="form-group">
                <label for="indicateurReferentiel" class="form-label">Indicateur Referentiel*</label>
                <select class="form-select" name="referentiel_id" id="indicateurReferentiel" required>
                  <option value="" selected disabled>Sélectionner un referentiel</option>
                  <?php if ($referentiels ?? []) : ?>
                    <?php foreach ($referentiels as $ref) : ?>
                      <?php if ($ref['categorie'] == 'produit') : ?>
                        <option value="<?= $ref['id'] ?>"><?= html_entity_decode($ref['intitule']) ?></option>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <!-- Objectif -->
            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="resultat_id" id="indicateurObjectif" required>
                  <option value="" selected disabled>Sélectionner un niveau</option>
                  <?php if ($niveau_resultats ?? []) : ?>
                    <?php foreach ($niveau_resultats as $niveau_result) : ?>
                      <option value="<?= $niveau_result['id'] ?>"><?= html_entity_decode($niveau_result['name']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="indicateurObjectif">Niveau de résultat*</label>
              </div>
            </div>

            <!-- Unite -->
            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="unite" id="indicateurUnite" required>
                  <option value="" selected disabled>Sélectionner une unité</option>
                  <?php if ($unites ?? []) : ?>
                    <?php foreach ($unites as $unite) : ?>
                      <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <label for="indicateurUnite">Unite*</label>
              </div>
            </div>
            <!-- Mode de calcul -->
            <div class="col-4">
              <div class="form-floating">
                <select class="form-select" name="mode_calcul" id="indicateurModeCalcul" required>
                  <option value="" selected disabled>Sélectionner un mode de calcul</option>
                  <?php foreach (listModeCalcul() as $key => $value) : ?>
                    <option value="<?= $key ?>"><?= $value ?></option>
                  <?php endforeach; ?>
                </select>
                <label for="indicateurModeCalcul">Mode de calcul*</label>
              </div>
            </div>

            <div class="col-12 mt-1">
              <div class="form-group">
                <label for="indicSelectProjet" class="form-label">Projet*</label>
                <select class="form-select" name="projet_id" id="indicSelectProjet">
                  <option value="" selected disabled>Sélectionner un projet</option>
                  <?php if ($projets ?? []) : ?>
                    <?php foreach ($projets as $projet) : ?>
                      <option value="<?= $projet['id'] ?>"><?= html_entity_decode($projet['name']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="col-6 mt-1">
              <div class="form-group">
                <label for="indicateurResponsable" class="form-label">Responsable*</label>
                <select class="form-select" name="responsable" id="indicateurResponsable" required>
                  <option value="" selected disabled>Sélectionner un responsable</option>
                  <?php if ($structures ?? []) : ?>
                    <?php foreach ($structures as $structure) : ?>
                      <option value="<?= $structure['id'] ?>">
                        <?= $structure['description'] ? $structure['description'] . ' (' . $structure['sigle'] . ')' : $structure['sigle']; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>
            <div class="col-6 mt-1">
              <div class="form-group">
                <label for="indicSelectFacteur" class="form-label">Facteur d'émission*</label>
                <select class="form-select" name="facteur_id" id="indicSelectFacteur" required>
                  <option value="" selected disabled>Sélectionner un facteur d'émission</option>
                  <?php if ($facteurs_project ?? []) : ?>
                    <?php foreach ($facteurs_project as $facteur) : ?>
                      <option value="<?= $facteur['id'] ?>"><?= html_entity_decode($facteur['name']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <!-- Annee de reference -->
            <div class="col-md-4">
              <div class="form-floating">
                <input class="form-control" name="annee_reference" id="indicateurAnneeReference" type="number" placeholder="Année de référence" required>
                <label for="indicateurAnneeReference">Année de référence*</label>
              </div>
            </div>
            <!-- Valeur de base -->
            <div class="col-md-4">
              <div class="form-floating">
                <input class="form-control" name="valeur_reference" id="indicateurValeurReference" type="text" placeholder="Valeur de base" required>
                <label for="indicateurValeurReference">Valeur de référence*</label>
              </div>
            </div>
            <!-- Valeur cible -->
            <div class="col-md-4">
              <div class="form-floating">
                <input class="form-control" name="valeur_cible" id="indicateurValeurCible" type="text" placeholder="Valeur cible" required>
                <label for="indicateurValeurCible">Valeur cible*</label>
              </div>
            </div>

            <!-- <div class="col-md-6">
              <div class="form-floating">
                <input class="form-control" name="latitude" id="indicateurLatitude" type="text" placeholder="Latitude">
                <label for="indicateurLatitude">Latitude</label>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-floating">
                <input class="form-control" name="longitude" id="indicateurLongitude" type="text" placeholder="Longitude">
                <label for="indicateurLongitude">Longitude</label>
              </div>
            </div> -->

            <!-- Description -->
            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" name="description" id="indicateurDescription" placeholder="Description de l'indicateur" style="height: 70px"></textarea>
                <label for="indicateurDescription">Description</label>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 px-0 pb-0">
              <input type="hidden" name="status" id="indicateurStatus" value="planifie">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="indicateur_modbtn">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formIndicateurID = null;
  $(document).ready(function() {
    initSelect2("#addIndicateurModal", "indicateurReferentiel");
    initSelect2("#addIndicateurModal", "indicateurResponsable");
    initSelect2("#addIndicateurModal", "indicSelectFacteur");
    initSelect2("#addIndicateurModal", "indicSelectProjet");

    $('#addIndicateurModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const projet_id = $(event.relatedTarget).data('projet_id');
      const form = document.getElementById('FormIndicateur');

      if (projet_id) {
        $('#indicSelectProjet').val(projet_id).trigger('change');
        $('#indicSelectProjet').attr('readonly', true);
        $('#indicSelectProjet').addClass('bg-light');
        $('#indicSelectProjet').css('pointerEvents', 'none');
      }

      // Show loading screen and hide content
      $('#cmrLoadingScreen').show();
      $('#cmrContentContainer').hide();

      if (dataId) {
        formIndicateurID = dataId;
        $('#cmrLoadingText').text("Chargement des données de l'indicateur...");
        $('#indicateur_modtitle').text('Modifier le indicateur');
        $('#indicateur_modbtn').text('Modifier');
        try {
          const response = await fetch(`./apis/indicateurs.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          form.code.value = result.data.code;
          form.intitule.value = result.data.intitule;
          form.description.value = result.data.description;
          form.annee_reference.value = result.data.annee_reference;
          form.unite.value = result.data.unite;
          form.mode_calcul.value = result.data.mode_calcul;
          // form.latitude.value = result.data.latitude;
          // form.longitude.value = result.data.longitude;
          form.valeur_reference.value = result.data.valeur_reference;
          form.valeur_cible.value = result.data.valeur_cible;
          form.resultat_id.value = result.data.resultat_id;

          $('#indicateurResponsable').val(result.data.responsable).trigger('change');
          $('#indicateurReferentiel').val(result.data.referentiel_id).trigger('change');
          $('#indicSelectFacteur').val(result.data.facteur_id).trigger('change');
          $('#indicSelectProjet').val(result.data.projet_id).trigger('change');
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          // Hide loading screen and show content
          $('#cmrLoadingScreen').hide();
          $('#cmrContentContainer').show();
        }
      } else {
        formIndicateurID = null;
        $('#indicateur_modtitle').text('Ajouter un indicateur');
        $('#indicateur_modbtn').text('Ajouter');
        $('#cmrLoadingText').text("Préparation du formulaire...");

        // Hide loading screen and show content faster for add mode
        setTimeout(() => {
          $('#cmrLoadingScreen').hide();
          $('#cmrContentContainer').show();
        }, 200);
      }
    });

    $('#addIndicateurModal').on('hide.bs.modal', function() {
      setTimeout(() => {
        $('#cmrLoadingScreen').show();
        $('#cmrContentContainer').hide();
      }, 200);
      $('#FormIndicateur')[0].reset();
      $('#indicSelectProjet').val("").trigger('change');
      $('#indicSelectFacteur').val("").trigger('change');
      $('#indicateurResponsable').val("").trigger('change');
      $('#indicateurReferentiel').val("").trigger('change');
    });


    $('#FormIndicateur').on('submit', async function(event) {
      event.preventDefault();
      const formData = new FormData(this);
      const url = formIndicateurID ? `./apis/indicateurs.routes.php?id=${formIndicateurID}` : './apis/indicateurs.routes.php';
      const submitBtn = $('#indicateur_modbtn');
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
          $('#addIndicateurModal').modal('hide');
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