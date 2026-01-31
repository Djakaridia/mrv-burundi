<div class="modal fade" id="addMesureModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addMesureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="mesure_modtitle">Ajouter un mesure</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times text-danger"></span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="mesureLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="mesureLoadingText">Chargement en cours</h4>
                </div>

                <div id="mesureContentContainer" style="display: none;">
                    <div class="card theme-wizard" data-theme-wizard="data-theme-wizard">
                        <ul class="nav justify-content-between nav-wizard nav-wizard-primary mx-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active fw-semibold" href="#phoenix-wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" aria-selected="true" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Etape 1</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#phoenix-wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Etape 2</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#phoenix-wizard-tab3" data-bs-toggle="tab" data-wizard-step="3" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle">
                                                <span class="fas fa-step-forward"></span>
                                            </span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Etape 3</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#phoenix-wizard-tab4" data-bs-toggle="tab" data-wizard-step="4" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle">
                                                <span class="fas fa-check"></span>
                                            </span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Validation</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                        <div class="card-body p-3">
                            <div id="FormMesure" class="tab-content">
                                <div class="tab-pane active" role="tabpanel" aria-labelledby="phoenix-wizard-tab1" id="phoenix-wizard-tab1">
                                    <form id="wizMesureForm1" novalidate="novalidate" class="needs-validation" data-wizard-form="1">
                                        <div class="row g-3">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-floating">
                                                    <input oninput="checkColumns('code', 'mesureCode', 'mesureCodeFeedback', 'mesures')" class="form-control" name="code" id="mesureCode" type="text" placeholder="Code" required>
                                                    <label for="mesureCode">Code*</label>
                                                    <div id="mesureCodeFeedback" class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-9 mb-3">
                                                <div class="form-floating">
                                                    <input class="form-control" name="name" id="mesureName" type="text" placeholder="Intitulé" required>
                                                    <label for="mesureName">Intitulé*</label>
                                                </div>
                                            </div>


                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating form-floating-advance-select">
                                                    <label for="mesureSecteur">Secteur concerné*</label>
                                                    <select class="form-select" name="secteur_id" id="mesureSecteur" required>
                                                        <option value="" selected disabled>Sélectionner un secteur</option>
                                                        <?php if ($secteurs_mesure ?? []) : ?>
                                                            <?php foreach ($secteurs_mesure as $secteur) : ?>
                                                                <option value="<?= $secteur['id'] ?>"><?= $secteur['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" name="structure_id" id="mesureStructure" required>
                                                        <option value="" selected disabled>Sélectionner un acteur</option>
                                                        <?php if ($structures ?? []) : ?>
                                                            <?php foreach ($structures as $structure) : ?>
                                                                <option value="<?= $structure['id'] ?>"><?= $structure['sigle'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <label for="mesureStructure">Entités de mise en œuvre *</label>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" name="action_type" id="mesureAction" required>
                                                        <option value="" selected disabled>Sélectionner un type</option>
                                                        <?php foreach (listTypeAction() as $key => $value) : ?>
                                                            <option value="<?= $key ?>"><?= $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureAction">Action type *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" name="instrument" id="mesureInstrument" required>
                                                        <option value="" selected disabled>Sélectionner d'instrument</option>
                                                        <?php foreach (listTypeInstrument() as $key => $value) : ?>
                                                            <option value="<?= $key ?>"><?= $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureInstrument">Instrument *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-floating">
                                                    <select class="form-select" name="status" id="mesureStatus" required>
                                                        <?php foreach (listStatus() as $key => $value) : ?>
                                                            <option value="<?= $key ?>"><?= $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureStatus">Status *</label>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab2" id="phoenix-wizard-tab2">
                                    <form id="wizMesureForm2" novalidate="novalidate" class="needs-validation" data-wizard-form="2">
                                        <div class="row g-3">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-floating form-floating-advance-select">
                                                    <label for="MultipleMesureGaz">Type de gaz*</label>
                                                    <select class="form-select" name="gaz" id="MultipleMesureGaz" data-choices="data-choices" multiple="multiple" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                                        <option value="" disabled>Sélectionner un type de gaz</option>
                                                        <?php if ($gazs ?? []) : ?>
                                                            <?php foreach ($gazs as $gaze) : ?>
                                                                <?php if (in_array($gaze['name'], explode(',', str_replace('"', '', $mesure_curr['gaz'] ?? '')))) : ?>
                                                                    <option value="<?= $gaze['name'] ?>" selected><?= $gaze['name'] ?></option>
                                                                <?php else : ?>
                                                                    <option value="<?= $gaze['name'] ?>"><?= $gaze['name'] ?></option>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="annee_debut" id="mesureStartDate" value="<?= date('Y') ?>" type="number" placeholder="Date de début">
                                                        <label class="ps-6" for="mesureStartDate">Année de début</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="annee_fin" id="mesureEndDate" value="<?= date('Y') ?>" type="number" placeholder="Date de clôture">
                                                        <label class="ps-6" for="mesureEndDate">Année de clôture</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="valeur_realise" id="mesureValueRealise" type="text" placeholder="Valeur réalise">
                                                        <label class="ps-6" for="mesureValueRealise">Valeur réalise</label>
                                                        <span class="uil uil-calculator-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="valeur_cible" id="mesureValueCible" type="text" placeholder="Valeur cible">
                                                        <label class="ps-6" for="mesureValueCible">Valeur cible</label>
                                                        <span class="uil uil-calculator-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab3" id="phoenix-wizard-tab3">
                                    <form id="wizMesureForm3" novalidate="novalidate" class="needs-validation" data-wizard-form="3">
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <label for="mesureDescription" class="fs-9 fw-semibold">Description de la mesure</label>
                                                <textarea class="form-control" name="description" id="mesureDescription"></textarea>
                                            </div>

                                            <div class="col-12 mb-3">
                                                <label for="mesureObjectif" class="fs-9 fw-semibold">Objectif de la mesure</label>
                                                <textarea class="form-control" name="objectif" id="mesureObjectif"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab4" id="phoenix-wizard-tab4">
                                    <form id="wizMesureForm4" novalidate="novalidate" novalidate="novalidate" class="needs-validation" data-wizard-form="5">
                                        <div class="text-center py-5 my-5">
                                            <h5 class="mb-3">Validation des informations de la mesure</h5>
                                            <p class="text-body-emphasis fs-9">Veuillez vous assurer de la véracité des informations de la mesure</p>
                                            <div class="d-flex justify-content-center border-0 px-0 pb-0">
                                                <button type="button" class="btn btn-primary my-0 px-5" id="mesure_modbtn">Valider les données</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-top border-light p-3" data-wizard-footer="data-wizard-footer">
                            <div class="d-flex pager justify-content-between wizard list-inline mb-0">
                                <button class="d-none btn btn-sm btn-secondary px-3" type="button" data-wizard-prev-btn="data-wizard-prev-btn">
                                    <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span>
                                    Précédent
                                </button>
                                <button class="btn btn-sm btn-primary px-3" type="submit" data-wizard-next-btn="data-wizard-next-btn">
                                    Suivant <span class="fas fa-chevron-right ms-1" data-fa-transform="shrink-3"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formMesureID = null;
    $(document).ready(function() {
        $('#addMesureModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');

            $('#mesureLoadingScreen').show();
            $('#mesureContentContainer').hide();

            if (dataId) {
                formMesureID = dataId;
                $('#mesure_modtitle').text('Modifier le mesure');
                $('#mesure_modbtn').text('Modifier les données');
                $('#mesureLoadingText').text("Chargement des données mesure...");

                try {
                    const response = await fetch(`./apis/mesures.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status !== 'success') errorAction(result.message || 'Erreur de données');

                    const form = document.forms['wizMesureForm1'];
                    form.code.value = result.data.code || '';
                    form.name.value = result.data.name || '';
                    form.secteur_id.value = result.data.secteur_id || '';
                    form.structure_id.value = result.data.structure_id || '';
                    form.action_type.value = result.data.action_type || '';
                    form.status.value = result.data.status || '';

                    const form2 = document.forms['wizMesureForm2'];
                    form2.gaz.value = result.data.gaz || '';
                    form2.annee_debut.value = result.data.annee_debut || '';
                    form2.annee_fin.value = result.data.annee_fin || '';
                    form2.valeur_realise.value = result.data.valeur_realise || '';
                    form2.valeur_cible.value = result.data.valeur_cible || '';

                    const form3 = document.forms['wizMesureForm3'];
                    form3.objectif.value = result.data.objectif || '';
                    form3.description.value = result.data.description || '';
                } catch (error) {
                    errorAction('Erreur lors du chargement des données: ' + error.message);
                } finally {
                    $('#mesureLoadingScreen').hide();
                    $('#mesureContentContainer').show();
                }
            } else {
                $('#mesure_modtitle').text('Ajouter un mesure');
                $('#mesure_modbtn').text('Valider les données');
                $('#mesureLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#mesureLoadingScreen').hide();
                    $('#mesureContentContainer').show();
                }, 200);
            }
        });

        $('#addMesureModal').on('hide.bs.modal', function() {
            $('#wizMesureForm1')[0].reset();
            $('#wizMesureForm2')[0].reset();
            $('#wizMesureForm3')[0].reset();
            $('#MultipleMesureGaz').val('');

            resetWizard();
            setTimeout(() => {
                $('#mesureLoadingScreen').show();
                $('#mesureContentContainer').hide();
            }, 200);
        });

        $('#mesure_modbtn').on('click', async function() {
            const submitBtn = $('#mesure_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const formData = new FormData();
                const forms = [
                    document.getElementById('wizMesureForm1'),
                    document.getElementById('wizMesureForm2'),
                    document.getElementById('wizMesureForm3'),
                ];

                forms.forEach(form => {
                    if (form) {
                        const formElements = form.elements;
                        for (let element of formElements) {
                            if (element.name) formData.append(element.name, element.value);
                        }
                    }
                });
                formData.append('gaz', $('#MultipleMesureGaz').val());

                const url = formMesureID ? `./apis/mesures.routes.php?id=${formMesureID}` : './apis/mesures.routes.php';
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Mesure enregistré avec succès!');
                    $('#addMesureModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                errorAction('Erreur: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });

    function resetWizard() {
        $('#wizMesureForm1')[0].reset();
        $('#wizMesureForm2')[0].reset();
        $('#wizMesureForm3')[0].reset();
        $('#MultipleMesureGaz').val('');

        $('.nav-link').removeClass('active');
        $('.nav-link[href="#phoenix-wizard-tab1"]').addClass('active');
        $('.tab-pane').removeClass('active show');
        $('#phoenix-wizard-tab1').addClass('active show');
    }
</script>