<!-- modal -->
<div class="modal fade" id="addProjectionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProjectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-1">
                <h3 class="mb-0" id="Projection_modtitle">Ajouter une projection</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="ProjectionLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="ProjectionLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="projectionContentContainer" style="display: none;">
                    <form name="FormProjection" id="FormProjection" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="projection_id" name="id">

                        <div class="row">
                            <!-- Secteur -->
                            <div class="col-md-6 mb-1">
                                <label for="projecttionSecteur" class="form-label">Secteur <span class="text-danger">*</span></label>
                                <select class="form-select" id="projecttionSecteur" name="secteur_id" required>
                                    <option value="" selected disabled>Sélectionner un secteur</option>
                                    <?php foreach ($secteurs_projection ?? [] as $secteur): ?>
                                        <option value="<?= $secteur['id'] ?>"><?= htmlspecialchars($secteur['nom'] ?? $secteur['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Scénario -->
                            <div class="col-md-6 mb-1">
                                <label for="projectionScenario" class="form-label">Scénario <span class="text-danger">*</span></label>
                                <select class="form-select" id="projectionScenario" name="scenario" required>
                                    <option value="" selected disabled>Sélectionner un scénario</option>
                                    <?php foreach (listTypeScenario() as $key => $scenario): ?>
                                        <option value="<?= $key ?>"><?= $scenario ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Indicateur -->
                            <div class="col-md-12 mb-1">
                                <label for="projectionReferentiel" class="form-label">Indicateur Référentiel <span class="text-danger">*</span></label>
                                <select class="form-select" id="projectionReferentiel" name="referentiel_id" required>
                                    <option value="" selected disabled>Sélectionner un indicateur</option>
                                    <?php foreach ($referentiels_projection ?? [] as $ref): ?>
                                        <?php if ($ref['categorie'] == 'impact' || $ref['categorie'] == 'effet') : ?>
                                            <option value="<?= $ref['id'] ?>"><?= htmlspecialchars($ref['intitule'] ?? $ref['name'] ?? '') ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Année -->
                            <div class="col-md-4 mb-1">
                                <label for="projectionAnnee" class="form-label">Année <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="projectionAnnee" name="annee"
                                    min="2000" max="2100" step="1"
                                    value="<?= date('Y') ?>" required>
                            </div>
                            <!-- Valeur -->
                            <div class="col-md-4 mb-1">
                                <label for="projectionValeur" class="form-label">Valeur <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="projectionValeur" name="valeur"
                                    placeholder="Ex: 1234.56" required>
                            </div>
                            <!-- Unité -->
                            <div class="col-md-4 mb-1">
                                <label for="projectionUnite" class="form-label">Unité</label>
                                <select class="form-select" id="projectionUnite" name="unite">
                                    <option value="">Sélectionner une unité</option>
                                    <?php foreach ($unites as $unite): ?>
                                        <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Source -->
                            <div class="col-12 mb-1">
                                <label for="projectionSource" class="form-label">Source</label>
                                <input type="text" class="form-control" id="projectionSource" name="source" placeholder="Ex: Rapport XYZ, Étude ABC...">
                            </div>
                            <!-- Description -->
                            <div class="col-12 mb-1">
                                <label for="projectionDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="projectionDescription" name="description" rows="3" placeholder="Détails supplémentaires sur la projection..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-danger btn-sm px-3 my-0 d-none" id="Projection_delbtn">Supprimer</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="Projection_modbtn">Modifier</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let projectionId = null;
    let currentSecteurId = null;
    let currentScenario = null;
    let currentAnnee = null;

    $(document).ready(function() {
        initSelect2("#addProjectionModal", "projectionReferentiel");

        $('#addProjectionModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const dataSecteur = $(event.relatedTarget).data('secteur');
            const dataScenario = $(event.relatedTarget).data('scenario');
            const dataAnnee = $(event.relatedTarget).data('annee');
            const form = document.getElementById('FormProjection');

            $('#ProjectionLoadingScreen').show();
            $('#projectionContentContainer').hide();
            $('#projection_id').val('');
            form.reset();

            form.annee.value = dataAnnee ?? <?= date('Y') ?>;
            form.secteur_id.value = dataSecteur ?? '';
            form.scenario.value = dataScenario ?? '';

            if (dataId) {
                projectionId = dataId;
                $('#Projection_modtitle').text('Modifier la projection');
                $('#Projection_modbtn').text('Modifier');
                $('#Projection_delbtn').removeClass('d-none');
                $('#ProjectionLoadingText').text("Chargement des données de la projection...");

                try {
                    const response = await fetch(`./apis/projections.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        const data = result.data;
                        form.projection_id.value = data.id;
                        form.secteur_id.value = data.secteur_id;
                        form.scenario.value = data.scenario;
                        form.annee.value = data.annee;
                        form.valeur.value = data.valeur;
                        form.unite.value = data.unite || '';
                        form.source.value = data.source || '';
                        form.description.value = data.description || '';

                        $('#projectionReferentiel').val(result.data.referentiel_id).trigger('change');
                        currentSecteurId = data.secteur_id;
                        currentScenario = data.scenario;
                        currentAnnee = data.annee;
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    errorAction('Impossible de charger les données de la projection.');
                } finally {
                    $('#ProjectionLoadingScreen').hide();
                    $('#projectionContentContainer').show();
                }
            } else {
                $('#ProjectionLoadingText').text("Préparation du formulaire...");
                $('#Projection_modtitle').text('Ajouter une projection');
                $('#Projection_modbtn').text('Ajouter');
                projectionId = null;
                currentSecteurId = null;
                currentScenario = null;
                currentAnnee = null;

                setTimeout(() => {
                    $('#ProjectionLoadingScreen').hide();
                    $('#projectionContentContainer').show();
                }, 200);
            }
        });

        $('#addProjectionModal').on('hide.bs.modal', function() {
            $('#FormProjection')[0].reset();
            $('#projectionReferentiel').val("").trigger('change');
            setTimeout(() => {
                $('#ProjectionLoadingScreen').show();
                $('#projectionContentContainer').hide();
            }, 200);
        });

        $('#FormProjection').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = projectionId ? `./apis/projections.routes.php?id=${projectionId}` : './apis/projections.routes.php';
            const submitBtn = $('#Projection_modbtn');

            if (!form.secteur_id.value || !form.scenario.value || !form.annee.value || !form.valeur.value) {
                errorAction('Veuillez remplir tous les champs obligatoires (*).');
                return;
            }

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
                    successAction(projectionId ? 'Projection modifiée avec succès.' : 'Projection ajoutée avec succès.');
                    $('#addProjectionModal').modal('hide');
                } else if (result.status === 'warning' && result.existing_id) {
                    errorAction('Cette projection existe déjà. Voulez-vous la modifier à la place?');
                    $('#addProjectionModal').modal('hide');
                    setTimeout(() => {
                        const editButton = document.createElement('button');
                        editButton.dataset.id = result.existing_id;
                        editButton.dataset.bsToggle = 'modal';
                        editButton.dataset.bsTarget = '#addProjectionModal';
                        editButton.click();
                    }, 300);
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text(projectionId ? 'Modifier' : 'Ajouter');
            }
        });

        $('#secteur_id, #scenario, #annee').on('change', function() {
            if (projectionId) {
                const secteurId = $('#secteur_id').val();
                const scenario = $('#scenario').val();
                const annee = $('#annee').val();

                if ((secteurId !== currentSecteurId) ||
                    (scenario !== currentScenario) ||
                    (annee !== currentAnnee)) {
                    $('#Projection_modbtn').prop('disabled', false).text('Modifier');
                    $('#Projection_modbtn').off('click');
                }
            }
        });

        $('#Projection_delbtn').on('click', function() {
            deleteData(projectionId, 'Êtes-vous sûr de vouloir supprimer cette projection ?', 'projections');
        });
    });
</script>