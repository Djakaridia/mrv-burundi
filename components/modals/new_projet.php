<?php
$array_typeLogo = ".jpg, .jpeg, .png, .gif, .webp";
?>
<div class="modal fade" id="addProjetModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProjetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0" id="projet_modtitle">Ajouter un projet</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times text-danger"></span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="projetLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="projetLoadingText">Chargement en cours</h4>
                </div>

                <div id="projetContentContainer" style="display: none;">
                    <div class="card theme-wizard">
                        <ul class="nav mx-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold active" href="#projet-wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" aria-selected="true" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="nav-item-title fs-9">Etape 1</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#projet-wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="nav-item-title fs-9">Etape 2</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link fw-semibold" href="#projet-wizard-tab3" data-bs-toggle="tab" data-wizard-step="3" aria-selected="false" tabindex="-1" role="tab">
                                    <div class="text-center d-inline-block">
                                        <span class="nav-item-circle-parent">
                                            <span class="nav-item-circle"><span class="fas fa-step-forward"></span></span>
                                        </span>
                                        <span class="nav-item-title fs-9">Etape 3</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                        <div class="card-body p-3 border-top">
                            <div id="FormProjet" class="tab-content">
                                <div class="tab-pane active" role="tabpanel" aria-labelledby="projet-wizard-tab1" id="projet-wizard-tab1">
                                    <form id="wizProjetForm1" novalidate="novalidate" class="needs-validation" data-wizard-form="1">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-3">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="avatar avatar-4xl">
                                                        <img id="projetLoadImage" class="rounded-1 border border-light shadow-sm rounded-1 w-100 avatar-placeholder d-none" src="" alt="no-image">
                                                        <i id="projetLoadImageIcon" class="far fa-image text-body-tertiary" style="width: 100%; font-size:100px"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <label for="projetImage" class="border border-dashed rounded-1 px-2 py-3 w-100 btn">
                                                    <input type="file" name="file" id="projetImage" accept="<?php echo $array_typeLogo ?>" class="form-control d-none w-100" />
                                                    <input type="hidden" name="allow_files" id="allow_file_projet" value="<?php echo $array_typeLogo; ?>">
                                                    <input type="hidden" name="logo" id="logo" value="">
                                                    <div class="text-center text-body-emphasis">
                                                        <h5 class="mb-3"> <span class="fa-solid fa-upload me-2"></span> Télécharger un logo pour le projet</h5>
                                                        <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Télécharger une image dans les formats suivants : <br> <?php echo $array_typeLogo ?></p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-0">
                                            <div class="col-md-3 mb-2">
                                                <div class="form-floating">
                                                    <input oninput="checkColumns('code', 'projetCode', 'projetCodeFeedback', 'projets')" class="form-control" name="code" id="projetCode" type="text" placeholder="Code" required>
                                                    <label for="projetCode">Code*</label>
                                                    <div id="projetCodeFeedback" class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-9 mb-2">
                                                <div class="form-floating">
                                                    <input class="form-control" name="name" id="projetName" type="text" placeholder="Intitulé" required>
                                                    <label for="projetName">Intitulé*</label>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="secteur_id" id="projetSecteur" required>
                                                        <option value="" selected disabled>Sélectionner un secteur</option>
                                                        <?php if (isset($secteurs) && !empty($secteurs)) : ?>
                                                            <?php foreach ($secteurs as $secteur) : ?>
                                                                <option value="<?= $secteur['id'] ?>"><?= $secteur['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <label for="projetSecteur">Secteur concerné*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2 mt-0">
                                                <div class="form-group">
                                                    <label for="projetManager" class="form-label">Entités de mise en œuvre *</label>
                                                    <select class="form-select" name="structure_id" id="projetStructure" required>
                                                        <option value="" selected disabled>Sélectionner une structure</option>
                                                        <?php if (!empty($structures)) : ?>
                                                            <?php foreach ($structures as $structure) : ?>
                                                                <option value="<?= $structure['id'] ?>">
                                                                    <?= $structure['description'] ? $structure['description'] . ' (' . $structure['sigle'] . ')' : $structure['sigle']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <input class="form-control" name="budget" id="projetBudget" type="number" step="0.01" min="0" placeholder="Budget" required>
                                                    <label for="projetBudget">Budget (USD)*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="programme_id" id="projetProgramme">
                                                        <option value="" selected disabled>Sélectionner un programme</option>
                                                        <?php if (!empty($programmes)) : ?>
                                                            <?php foreach ($programmes as $programme) : ?>
                                                                <option value="<?= $programme['id'] ?>"><?= $programme['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <label for="projetProgramme">Programmes concernés</label>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="projet-wizard-tab2" id="projet-wizard-tab2">
                                    <form id="wizProjetForm2" novalidate="novalidate" class="needs-validation" data-wizard-form="2">
                                        <div class="row g-3 mb-0">
                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="action_type" id="projetAction" required>
                                                        <option value="" selected disabled>Sélectionner une action</option>
                                                        <?php foreach (listTypeAction() as $key => $value) : ?>
                                                            <option value="<?= $key ?>"><?= $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="projetAction">Action type*</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <div class="form-floating">
                                                    <select class="form-select" name="status" id="projetStatus" required>
                                                        <option value="" selected disabled>Sélectionner un statut</option>
                                                        <?php foreach (listStatus() as $key => $value) : ?>
                                                            <option value="<?= $key ?>"><?= $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="projetStatus">Statut*</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-0 mb-1">
                                                <div class="form-group">
                                                    <label for="projetMesure" class="form-label">Mesure concernée</label>
                                                    <select class="form-select" id="projetMesure" name="mesure_id">
                                                        <option value="" selected disabled>Sélectionner la mesure</option>
                                                        <?php if (!empty($mesures)) : ?>
                                                            <?php foreach ($mesures as $mesure): ?>
                                                                <option value="<?= $mesure['id'] ?>"><?= $mesure['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12 mt-0 mb-1">
                                                <div class="form-group">
                                                    <label for="MultipleProjetGaz" class="form-label">Types de gaz</label>
                                                    <select class="form-select" id="MultipleProjetGaz" name="gaz[]" multiple="multiple">
                                                        <?php if (!empty($gazs)) : ?>
                                                            <?php foreach ($gazs as $gaz): ?>
                                                                <option value="<?= $gaz['name'] ?>"><?= $gaz['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-1">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control datetimepicker" name="start_date" id="projetStartDate" value="<?= date('Y-m-d') ?>" type="text" placeholder="Date de début">
                                                        <label class="ps-6" for="projetStartDate">Date de début</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control datetimepicker" name="end_date" id="projetEndDate" value="<?= date('Y-m-d') ?>" type="text" placeholder="Date de clôture">
                                                        <label class="ps-6" for="projetEndDate">Date de clôture</label>
                                                        <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6 mb-1">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control datetimepicker" name="signature_date" id="projetSignatureDate" value="<?= date('Y-m-d') ?>" type="text" placeholder="Date de signature">
                                                        <label class="ps-6" for="projetSignatureDate">Date de signature</label>
                                                        <span class="uil uil-pen flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-1">
                                                <div class="flatpickr-input-container">
                                                    <div class="form-floating">
                                                        <input class="form-control datetimepicker" name="miparcours_date" id="projetMiparcours" value="<?= date('Y-m-d') ?>" type="text" placeholder="Date de mi-parcours">
                                                        <label class="ps-6" for="projetMiparcours">Date de mi-parcours</label>
                                                        <span class="uil uil-clock flatpickr-icon text-body-tertiary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="tab-pane" role="tabpanel" aria-labelledby="projet-wizard-tab3" id="projet-wizard-tab3">
                                    <form id="wizProjetForm3" novalidate="novalidate" class="needs-validation" data-wizard-form="3">
                                        <div class="row">
                                            <div class="col-md-12 mt-0 mb-2">
                                                <div class="form-group">
                                                    <label for="MultipleProjetGroupe" class="form-label">Groupes de travail</label>
                                                    <select class="form-select" id="MultipleProjetGroupe" name="groupes[]" multiple="multiple">
                                                        <?php if (!empty($groupes_travail)) : ?>
                                                            <?php foreach ($groupes_travail as $groupe) : ?>
                                                                <option value="<?= $groupe['id'] ?>"><?= $groupe['name'] ?></option>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-12 mb-2">
                                                <label for="projetDescription" class="form-label">Description du projet</label>
                                                <textarea class="form-control" name="description" id="projetDescription" rows="4"></textarea>
                                            </div>

                                            <div class="col-12 mb-2">
                                                <label for="projetObjectif" class="form-label">Objectif du projet</label>
                                                <textarea class="form-control" name="objectif" id="projetObjectif" rows="4"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-top border-light p-2" data-wizard-footer="data-wizard-footer">
                            <div class="d-flex pager justify-content-between wizard list-inline mb-0">
                                <button class="d-none btn btn-sm btn-secondary px-3" type="button" projet-wizard-prev-btn="projet-wizard-prev-btn">
                                    <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span>
                                    Précédent
                                </button>
                                <button class="btn btn-sm btn-primary px-3" type="button" projet-wizard-next-btn="projet-wizard-next-btn">
                                    Suivant <span class="fas fa-chevron-right ms-1" data-fa-transform="shrink-3"></span>
                                </button>
                                <button class="btn btn-sm btn-success px-3 d-none" type="button" id="projetModbtn">
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
    let formProjetID = null;

    $(document).ready(function() {
        initSelect2("#addProjetModal", "projetMesure");
        initSelect2("#addProjetModal", "projetStructure");
        initSelect2("#addProjetModal", "MultipleProjetGaz");
        initSelect2("#addProjetModal", "MultipleProjetGroupe");

        if (typeof flatpickr !== 'undefined') {
            $(".datetimepicker").flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true
            });
        }

        $('#addProjetModal').on('shown.bs.modal', async function(event) {
            const button = $(event.relatedTarget);
            const dataId = button.data('id');

            $('#projetLoadingScreen').show();
            $('#projetContentContainer').hide();
            resetProjetWizard();

            if (dataId) {
                formProjetID = dataId;
                $('#projet_modtitle').text('Modifier le projet');
                $('#projetModbtn').text('Modifier');
                $('#projetLoadingText').text("Chargement des données projet...");

                try {
                    const response = await fetch(`./apis/projets.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json'
                        },
                        method: 'GET',
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();

                    if (result.status !== 'success') {
                        throw new Error(result.message || 'Erreur de chargement des données');
                    }

                    fillProjetForm(result.data);
                } catch (error) {
                    console.error('Erreur:', error);
                    if (typeof errorAction === 'function') {
                        errorAction('Erreur lors du chargement des données: ' + error.message);
                    } else {
                        alert('Erreur: ' + error.message);
                    }
                } finally {
                    $('#projetLoadingScreen').hide();
                    $('#projetContentContainer').show();
                }
            } else {
                $('#projet_modtitle').text('Ajouter un projet');
                $('#projetModbtn').text('Enregistrer');
                $('#projetLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#projetLoadingScreen').hide();
                    $('#projetContentContainer').show();
                }, 200);
            }
        });

        $('#addProjetModal').on('hidden.bs.modal', function() {
            resetProjetWizard();
        });

        $('#projetImage').on('change', function() {
            const file = this.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    if (typeof errorAction === 'function') {
                        errorAction('Type de fichier non autorisé');
                    } else {
                        alert('Type de fichier non autorisé');
                    }
                    this.value = '';
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    if (typeof errorAction === 'function') {
                        errorAction('Le fichier ne doit pas dépasser 5MB');
                    } else {
                        alert('Le fichier ne doit pas dépasser 5MB');
                    }
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#projetLoadImage').attr('src', e.target.result).removeClass('d-none');
                    $('#projetLoadImageIcon').addClass('d-none');
                };
                reader.onerror = function() {
                    console.error('Erreur de lecture du fichier');
                };
                reader.readAsDataURL(file);
            } else {
                $('#projetLoadImage').attr('src', '').addClass('d-none');
                $('#projetLoadImageIcon').removeClass('d-none');
            }
        });

        $('#projetModbtn').on('click', async function() {
            const submitBtn = $(this);

            if (!validateProjetForms()) return;

            submitBtn.prop('disabled', true);
            const originalText = submitBtn.text();
            submitBtn.html('<span class="spinner-border fs-8 spinner-border-sm me-2"></span>Envoi...');

            try {
                const formData = new FormData();
                const forms = [
                    document.getElementById('wizProjetForm1'),
                    document.getElementById('wizProjetForm2'),
                    document.getElementById('wizProjetForm3'),
                ];

                forms.forEach(form => {
                    if (form) {
                        $(form).find(':input').each(function() {
                            const input = $(this);
                            const name = input.attr('name');
                            if (name && !name.endsWith('[]')) {
                                formData.append(name, input.val() || '');
                            }
                        });
                    }
                });

                const gazValues = $('#MultipleProjetGaz').val();
                if (gazValues && gazValues.length > 0) {
                    formData.append('gaz', gazValues.join(','));
                }

                const groupesValues = $('#MultipleProjetGroupe').val();
                if (groupesValues && groupesValues.length > 0) {
                    formData.append('groupes', groupesValues.join(','));
                }

                const fileInput = document.getElementById('projetImage');
                if (fileInput.files.length > 0) {
                    formData.append('file', fileInput.files[0]);
                }

                const url = formProjetID ? `./apis/projets.routes.php?id=${formProjetID}` : './apis/projets.routes.php';
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    if (typeof successAction === 'function') {
                        successAction('Projet enregistré avec succès!');
                    } else {
                        alert('Succès: Projet enregistré!');
                    }
                    $('#addProjetModal').modal('hide');

                    if (typeof loadProjets === 'function') loadProjets();
                } else {
                    throw new Error(result.message || 'Erreur lors de l\'enregistrement');
                }

            } catch (error) {
                console.error('Erreur:', error);
                if (typeof errorAction === 'function') {
                    errorAction('Erreur: ' + error.message);
                } else {
                    alert('Erreur: ' + error.message);
                }
            } finally {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });

        $('[projet-wizard-next-btn]').on('click', function() {
            const currentTab = $('.tab-pane.active');
            const currentForm = currentTab.find('form');

            if (currentForm.length && currentForm[0].checkValidity) {
                if (!currentForm[0].checkValidity()) {
                    currentForm[0].reportValidity();
                    return;
                }
            }

            const nextTab = currentTab.next('.tab-pane');
            if (nextTab.length) {
                const tabId = nextTab.attr('id');
                $(`a[href="#${tabId}"]`).tab('show');
                updateWizardProjet();
            }
        });

        $('[projet-wizard-prev-btn]').on('click', function() {
            const currentTab = $('.tab-pane.active');
            const prevTab = currentTab.prev('.tab-pane');

            if (prevTab.length) {
                const tabId = prevTab.attr('id');
                $(`a[href="#${tabId}"]`).tab('show');
                updateWizardProjet();
            }
        });
    });

    function updateWizardProjet() {
        const currentTab = $('#FormProjet .tab-pane.active');
        const tabIndex = $('#FormProjet .tab-pane').index(currentTab);
        const totalTabs = $('#FormProjet .tab-pane').length;
        const prevBtn = $('[projet-wizard-prev-btn]');
        const nextBtn = $('[projet-wizard-next-btn]');
        const saveBtn = $('#projetModbtn');

        if (tabIndex === 0) {
            prevBtn.addClass('d-none');
            nextBtn.removeClass('d-none');
            saveBtn.addClass('d-none');
        } else if (tabIndex === totalTabs - 1) {
            prevBtn.removeClass('d-none');
            nextBtn.addClass('d-none');
            saveBtn.removeClass('d-none');
        } else {
            prevBtn.removeClass('d-none');
            nextBtn.removeClass('d-none');
            saveBtn.addClass('d-none');
        }
    }

    function fillProjetForm(data) {
        try {
            $('#wizProjetForm1 input[name="code"]').val(data.code || '');
            $('#wizProjetForm1 input[name="name"]').val(data.name || '');
            $('#wizProjetForm1 input[name="budget"]').val(data.budget || 0);
            $('#wizProjetForm1 select[name="secteur_id"]').val(data.secteur_id || '');
            $('#wizProjetForm1 select[name="programme_id"]').val(data.programme_id || '');

            if (data.logo) {
                const logoPath = data.logo.replace(/^\.\.\//, '');
                $('#projetLoadImage').attr('src', logoPath).removeClass('d-none');
                $('#projetLoadImageIcon').addClass('d-none');
                $('#logo').val(data.logo);
            }

            $('#wizProjetForm2 input[name="start_date"]').val(data.start_date || '');
            $('#wizProjetForm2 input[name="end_date"]').val(data.end_date || '');
            $('#wizProjetForm2 input[name="signature_date"]').val(data.signature_date || '');
            $('#wizProjetForm2 input[name="miparcours_date"]').val(data.miparcours_date || '');
            $('#wizProjetForm2 select[name="action_type"]').val(data.action_type || '');
            $('#wizProjetForm2 select[name="status"]').val(data.status || '');

            if (data.mesure_id) $('#projetMesure').val(data.mesure_id).trigger('change');
            if (data.structure_id) $('#projetStructure').val(data.structure_id).trigger('change');

            if (data.gaz) {
                const gazArray = data.gaz.split(',').map(g => g.trim());
                $('#MultipleProjetGaz').val(gazArray).trigger('change');
            }

            if (data.groupes) {
                const groupesArray = data.groupes.split(',').map(g => g.trim());
                $('#MultipleProjetGroupe').val(groupesArray).trigger('change');
            }

            if (typeof tinymce !== 'undefined') {
                if (tinymce.get('projetDescription')) {
                    tinymce.get('projetDescription').setContent(data.description || '');
                }
                if (tinymce.get('projetObjectif')) {
                    tinymce.get('projetObjectif').setContent(data.objectif || '');
                }
            } else {
                $('#projetDescription').val(data.description || '');
                $('#projetObjectif').val(data.objectif || '');
            }

        } catch (error) {
            console.error('Erreur lors du remplissage du formulaire:', error);
        }
    }

    function validateProjetForms() {
        const forms = [
            document.getElementById('wizProjetForm1'),
            document.getElementById('wizProjetForm2'),
            document.getElementById('wizProjetForm3')
        ];

        for (let form of forms) {
            if (form && !form.checkValidity()) {
                const tabId = $(form).closest('.tab-pane').attr('id');
                if (tabId) $(`a[href="#${tabId}"]`).tab('show');
                form.reportValidity();
                return false;
            }
        }

        return true;
    }

    function resetProjetWizard() {
        $('#projetLoadImage').attr('src', '').addClass('d-none');
        $('#projetLoadImageIcon').removeClass('d-none');
        $('#projetImage').val('');
        $('#logo').val('');

        $('#wizProjetForm1')[0]?.reset();
        $('#wizProjetForm2')[0]?.reset();
        $('#wizProjetForm3')[0]?.reset();

        $('#projetMesure').val('').trigger('change');
        $('#projetStructure').val('').trigger('change');
        $('#MultipleProjetGaz').val([]).trigger('change');
        $('#MultipleProjetGroupe').val([]).trigger('change');

        if (typeof tinymce !== 'undefined') {
            if (tinymce.get('projetObjectif')) {
                tinymce.get('projetObjectif').setContent('');
            }
            if (tinymce.get('projetDescription')) {
                tinymce.get('projetDescription').setContent('');
            }
        }

        $('.nav-wizard .nav-link').removeClass('active');
        $('.nav-wizard .nav-item:first-child .nav-link').addClass('active');
        $('.tab-pane').removeClass('active show');
        $('#projet-wizard-tab1').addClass('active show');
        $('[projet-wizard-prev-btn]').addClass('d-none');
        $('[projet-wizard-next-btn]').removeClass('d-none');
        $('#projetModbtn').addClass('d-none');

        formProjetID = null;
    }

    $(document).on('focus', '.datetimepicker', function() {
        if (typeof flatpickr !== 'undefined' && !$(this).data('flatpickr')) {
            $(this).flatpickr({
                dateFormat: "Y-m-d",
                allowInput: true
            });
        }
    });
</script>