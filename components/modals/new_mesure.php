<div class="modal fade" id="addMesureModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addMesureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0" id="mesure_modtitle">Ajouter une mesure</h3>
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
                    <div class="card theme-wizard">
                        <ul class="nav justify-content-between nav-wizard nav-wizard-primary mx-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold active" href="#mesure-wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" aria-selected="true" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Etape 1</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#mesure-wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="d-none d-md-block mt-1 fs-9">Etape 2</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#mesure-wizard-tab3" data-bs-toggle="tab" data-wizard-step="3" aria-selected="false" tabindex="-1" role="tab">
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
                        </ul>
                        <div class="card-body p-3 border-top">
                            <div id="FormMesure" class="tab-content">
                                <div class="tab-pane active" role="tabpanel" aria-labelledby="mesure-wizard-tab1" id="mesure-wizard-tab1">
                                    <form id="wizMesureForm1" novalidate="novalidate" class="needs-validation" data-wizard-form="1">
                                        <div class="row g-3">
                                            <div class="col-md-3 mb-2">
                                                <div class="form-floating">
                                                    <input oninput="checkColumns('code', 'mesureCode', 'mesureCodeFeedback', 'mesures')" class="form-control" name="code" id="mesureCode" type="text" placeholder="Code" required>
                                                    <label for="mesureCode">Code*</label>
                                                    <div id="mesureCodeFeedback" class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-9 mb-2">
                                                <div class="form-floating">
                                                    <input class="form-control" name="name" id="mesureName" type="text" placeholder="Intitulé" required>
                                                    <label for="mesureName">Intitulé*</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="secteur_id" id="mesureSecteur" required>
                                                        <option value="" selected disabled>Sélectionner un secteur</option>
                                                        <?php if ($secteurs_mesure ?? []) : ?>
                                                            <?php foreach ($secteurs_mesure as $secteur) : ?>
                                                                <option value="<?= $secteur['id'] ?>"><?= htmlspecialchars($secteur['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <label for="mesureSecteur">Secteur concerné*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="action_type" id="mesureAction" required>
                                                        <option value="" selected disabled>Sélectionner un type</option>
                                                        <?php foreach (listTypeAction() as $key => $value) : ?>
                                                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureAction">Action type*</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-2 mt-0">
                                                <div class="form-group">
                                                    <label for="mesureReferentiel" class="form-label">Indicateur Référentiel*</label>
                                                    <select class="form-select" name="referentiel_id" id="mesureReferentiel" required>
                                                        <option value="" selected disabled>Sélectionner un indicateur</option>
                                                        <?php foreach ($referentiels_mesure ?? [] as $ref): ?>
                                                            <?php if ($ref['categorie'] == 'impact' || $ref['categorie'] == 'effet' || true) : ?>
                                                                <option value="<?= htmlspecialchars($ref['id']) ?>"><?= htmlspecialchars($ref['intitule'] ?? $ref['name'] ?? '') ?></option>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mb-2 mt-0">
                                                <div class="form-group">
                                                    <label for="mesureStructure" class="form-label">Entités de mise en œuvre*</label>
                                                    <select class="form-select" name="structure_id" id="mesureStructure" required>
                                                        <option value="" selected disabled>Sélectionner un acteur</option>
                                                        <?php if ($structures ?? []) : ?>
                                                            <?php foreach ($structures as $structure) : ?>
                                                                <option value="<?= htmlspecialchars($structure['id']) ?>">
                                                                    <?= htmlspecialchars($structure['description'] ? $structure['description'] . ' (' . $structure['sigle'] . ')' : $structure['sigle']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="mesure-wizard-tab2" id="mesure-wizard-tab2">
                                    <form id="wizMesureForm2" novalidate="novalidate" class="needs-validation" data-wizard-form="2">
                                        <div class="row g-3">
                                            <div class="col-md-4 mb-1">
                                                <div class="form-floating">
                                                    <select class="form-select" name="instrument" id="mesureInstrument" required>
                                                        <option value="" selected disabled>Sélectionner un instrument</option>
                                                        <?php foreach (listTypeInstrument() as $key => $value) : ?>
                                                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureInstrument">Instrument*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="form-floating">
                                                    <select class="form-select" name="unite" id="mesureUnite" required>
                                                        <option value="" selected disabled>Sélectionner une unité</option>
                                                        <?php if ($unites ?? []) : ?>
                                                            <?php foreach ($unites as $unite) : ?>
                                                                <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <label for="mesureUnite">Unité*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-1">
                                                <div class="form-floating">
                                                    <select class="form-select" name="status" id="mesureStatus" required>
                                                        <option value="" selected disabled>Sélectionner un statut</option>
                                                        <?php foreach (listStatus() as $key => $value) : ?>
                                                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($value) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="mesureStatus">Statut*</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-0 mb-2">
                                                <div class="form-group">
                                                    <label for="MultipleProjetGaz" class="form-label">Types de gaz</label>
                                                    <select class="form-select" style="padding-left: 10px;" id="MultipleMesureGaz" name="gaz[]" multiple="multiple">
                                                        <?php if ($gazs ?? []) : ?>
                                                            <?php foreach ($gazs as $gaz): ?>
                                                                <option value="<?= htmlspecialchars($gaz['name']) ?>"><?= htmlspecialchars($gaz['name']) ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="annee_debut" id="mesureStartDate" value="<?= date('Y') ?>" type="number" min="1900" max="2100" placeholder="Année de début" required>
                                                        <label class="ps-6" for="mesureStartDate">Année de début*</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="annee_fin" id="mesureEndDate" value="<?= date('Y') ?>" type="number" min="1900" max="2100" placeholder="Année de clôture" required>
                                                        <label class="ps-6" for="mesureEndDate">Année de clôture*</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="latitude" id="mesureLatitude" type="text" placeholder="Latitude" pattern="^-?\d+\.?\d*$">
                                                        <label class="ps-6" for="mesureLatitude">Latitude</label>
                                                        <span class="uil uil-map-marker flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control" name="longitude" id="mesureLongitude" type="text" placeholder="Longitude" pattern="^-?\d+\.?\d*$">
                                                        <label class="ps-6" for="mesureLongitude">Longitude</label>
                                                        <span class="uil uil-map-marker flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="mesure-wizard-tab3" id="mesure-wizard-tab3">
                                    <form id="wizMesureForm3" novalidate="novalidate" class="needs-validation" data-wizard-form="3">
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <label for="mesureDescription" class="form-label">Description de la mesure</label>
                                                <textarea class="form-control" name="description" id="mesureDescription" rows="4"></textarea>
                                            </div>

                                            <div class="col-12 mb-2">
                                                <label for="mesureObjectif" class="form-label">Objectif de la mesure</label>
                                                <textarea class="form-control" name="objectif" id="mesureObjectif" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-top border-light p-3" data-wizard-footer="data-wizard-footer">
                            <div class="d-flex pager justify-content-between wizard list-inline mb-0">
                                <button class="d-none btn btn-sm btn-secondary px-3" type="button" mesure-wizard-prev-btn="mesure-wizard-prev-btn">
                                    <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span>
                                    Précédent
                                </button>
                                <button class="btn btn-sm btn-primary px-3" type="button" mesure-wizard-next-btn="mesure-wizard-next-btn">
                                    Suivant <span class="fas fa-chevron-right ms-1" data-fa-transform="shrink-3"></span>
                                </button>
                                <button class="btn btn-sm btn-success px-3 d-none" type="button" id="mesure_modbtn">
                                    Enregistrer
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
        initSelect2("#addMesureModal", "MultipleMesureGaz");
        initSelect2("#addMesureModal", "mesureStructure");
        initSelect2("#addMesureModal", "mesureReferentiel");

        updateWizardMesure();
        $('#addMesureModal').on('shown.bs.modal', async function(event) {
            const button = $(event.relatedTarget);
            const dataId = button.data('id');

            resetMesureWizard();
            clearValidationErrors();

            $('#mesureLoadingScreen').show();
            $('#mesureContentContainer').hide();

            if (dataId) {
                formMesureID = dataId;
                $('#mesure_modtitle').text('Modifier la mesure');
                $('#mesure_modbtn').text('Modifier');
                $('#mesureLoadingText').text("Chargement des données mesure...");

                try {
                    const response = await fetch(`./apis/mesures.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        },
                        method: 'GET',
                    });

                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.status !== 'success') {
                        throw new Error(result.message || 'Erreur de chargement des données');
                    }

                    populateMesureData(result.data);
                } catch (error) {
                    errorAction('Erreur lors du chargement des données: ' + error.message);
                    $('#addMesureModal').modal('hide');
                } finally {
                    $('#mesureLoadingScreen').hide();
                    $('#mesureContentContainer').show();
                    setTimeout(updateWizardMesure, 100);
                }
            } else {
                formMesureID = null;
                $('#mesure_modtitle').text('Ajouter une mesure');
                $('#mesure_modbtn').text('Enregistrer');
                $('#mesureLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#mesureLoadingScreen').hide();
                    $('#mesureContentContainer').show();
                    updateWizardMesure();
                }, 200);
            }
        });

        $('#addMesureModal').on('hidden.bs.modal', function() {
            resetMesureWizard();
            formMesureID = null;
        });

        $('#mesure_modbtn').on('click', async function() {
            if (!validateAllForms()) return;

            const submitBtn = $('#mesure_modbtn');
            submitBtn.prop('disabled', true);
            const originalText = submitBtn.text();
            submitBtn.html('<span class="spinner-border fs-8 spinner-border-sm me-2"></span>Envoi en cours...');

            try {
                const formData = new FormData();
                collectMesureData(formData);

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
                    successAction(formMesureID ? 'Mesure modifiée avec succès!' : 'Mesure ajoutée avec succès!');
                    $('#addMesureModal').modal('hide');

                    if (typeof loadMesures === 'function') loadMesures();
                } else {
                    errorAction(result.message || 'Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorAction('Erreur de connexion: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text(originalText);
            }
        });

        $('[mesure-wizard-next-btn]').on('click', function() {
            const currentTab = $('.tab-pane.active');
            const currentForm = currentTab.find('form');

            if (currentForm.length) {
                if (!validateForm(currentForm[0])) return;
            }

            const nextTab = currentTab.next('.tab-pane');
            if (nextTab.length) {
                const tabId = nextTab.attr('id');
                $(`a[href="#${tabId}"]`).tab('show');
                updateWizardMesure();
            }
        });

        $('[mesure-wizard-prev-btn]').on('click', function() {
            const currentTab = $('.tab-pane.active');
            const prevTab = currentTab.prev('.tab-pane');

            if (prevTab.length) {
                const tabId = prevTab.attr('id');
                $(`a[href="#${tabId}"]`).tab('show');
                updateWizardMesure();
            }
        });

        $('#mesureEndDate, #mesureStartDate').on('change', function() {
            validateYears();
        });

        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
            updateWizardMesure();
        });
    });

    function populateMesureData(data) {
        const form1 = document.forms['wizMesureForm1'];
        if (form1) {
            form1.code.value = data.code || '';
            form1.name.value = data.name || '';
            form1.secteur_id.value = data.secteur_id || '';
            form1.action_type.value = data.action_type || '';
        }

        const form2 = document.forms['wizMesureForm2'];
        if (form2) {
            form2.instrument.value = data.instrument || '';
            form2.unite.value = data.unite || '';
            form2.status.value = data.status || '';
            form2.annee_debut.value = data.annee_debut || new Date().getFullYear();
            form2.annee_fin.value = data.annee_fin || new Date().getFullYear();
            form2.latitude.value = data.latitude || '';
            form2.longitude.value = data.longitude || '';
        }

        if (data.referentiel_id) {
            $('#mesureReferentiel').val(data.referentiel_id).trigger('change');
        }

        if (data.structure_id) {
            $('#mesureStructure').val(data.structure_id).trigger('change');
        }

        if (data.gaz) {
            const gazArray = typeof data.gaz === 'string' ? data.gaz.split(',') : (data.gaz || []);
            $('#MultipleMesureGaz').val(gazArray).trigger('change');
        }

        const form3 = document.forms['wizMesureForm3'];
        if (form3) {
            form3.objectif.value = data.objectif || '';
            form3.description.value = data.description || '';
        }
    }

    function collectMesureData(formData) {
        const forms = [
            document.getElementById('wizMesureForm1'),
            document.getElementById('wizMesureForm2'),
            document.getElementById('wizMesureForm3'),
        ];

        forms.forEach(form => {
            if (form) {
                const formElements = form.elements;
                for (let element of formElements) {
                    if (element.name && element.type !== 'select-multiple') {
                        formData.append(element.name, element.value);
                    }
                }
            }
        });

        const gazValues = $('#MultipleMesureGaz').val();
        if (gazValues && gazValues.length > 0) {
            formData.append('gaz', gazValues.join(','));
        } else {
            formData.append('gaz', '');
        }
    }

    function validateForm(form) {
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }
        return true;
    }

    function validateAllForms() {
        const forms = [
            document.getElementById('wizMesureForm1'),
            document.getElementById('wizMesureForm2'),
            document.getElementById('wizMesureForm3'),
        ];

        for (let form of forms) {
            if (form && !form.checkValidity()) {
                const tabId = $(form).closest('.tab-pane').attr('id');
                if (tabId) {
                    $(`a[href="#${tabId}"]`).tab('show');
                    updateWizardMesure();
                }
                form.reportValidity();
                return false;
            }
        }
        return true;
    }

    function validateYears() {
        const startYear = parseInt($('#mesureStartDate').val());
        const endYear = parseInt($('#mesureEndDate').val());

        if (startYear && endYear && endYear < startYear) {
            $('#mesureEndDate')[0].setCustomValidity('L\'année de fin doit être supérieure ou égale à l\'année de début');
        } else {
            $('#mesureEndDate')[0].setCustomValidity('');
        }
    }

    function updateWizardMesure() {
        const currentTab = $('#FormMesure .tab-pane.active');
        const tabIndex = $('#FormMesure .tab-pane').index(currentTab);
        const totalTabs = $('#FormMesure .tab-pane').length;
        const $prevBtn = $('[mesure-wizard-prev-btn]');
        const $nextBtn = $('[mesure-wizard-next-btn]');
        const $saveBtn = $('#mesure_modbtn');

        if (tabIndex === -1) return;
        $prevBtn.removeClass('d-none');
        $nextBtn.removeClass('d-none');
        $saveBtn.addClass('d-none');

        if (tabIndex === 0) {
            $prevBtn.addClass('d-none');
        }

        if (tabIndex === totalTabs - 1) {
            $nextBtn.addClass('d-none');
            $saveBtn.removeClass('d-none');
        }
    }


    function resetMesureWizard() {
        $('#wizMesureForm1')[0]?.reset();
        $('#wizMesureForm2')[0]?.reset();
        $('#wizMesureForm3')[0]?.reset();
        $('#mesureStartDate').val(new Date().getFullYear());
        $('#mesureEndDate').val(new Date().getFullYear());
        $('#mesureReferentiel').val('').trigger('change');
        $('#mesureStructure').val('').trigger('change');
        $('#MultipleMesureGaz').val([]).trigger('change');
        $('a[href="#mesure-wizard-tab1"]').tab('show');

        setTimeout(updateWizardMesure, 50);
    }

    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').empty();
    }
</script>