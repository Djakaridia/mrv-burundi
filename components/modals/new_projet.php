<?php $array_typeLogo = ".jpg, .jpeg, .png, .gif, .webp"; ?>
<div class="modal fade" id="addProjetModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProjetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
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
                    <div class="card theme-wizard" data-theme-wizard="data-theme-wizard">
                        <div class="card-body pt-3 pb-0">
                            <div class="row justify-content-between">
                                <div class="col-md-3">
                                    <div class="scrollbar shadow-sm rounded-1 mb-3 px-3" style="height: max-content;">
                                        <ul class="nav justify-content-between flex-nowrap nav-wizard nav-wizard-vertical-md" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link active py-0 py-md-3" href="#phoenix-wizard-tab1" data-bs-toggle="tab" data-wizard-step="1" aria-selected="true" role="tab">
                                                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                                                        <span class="nav-item-circle-parent">
                                                            <span class="nav-item-circle">
                                                                <span class="fa-solid fa-id-card nav-item-icon"></span>
                                                                <span class="fa-solid fa-check check-icon"></span>
                                                            </span>
                                                        </span>
                                                        <span class="nav-item-title fs-9 fs-xl-8">Identification</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link py-0 py-md-3" href="#phoenix-wizard-tab2" data-bs-toggle="tab" data-wizard-step="2" aria-selected="false" tabindex="-1" role="tab">
                                                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                                                        <span class="nav-item-circle-parent">
                                                            <span class="nav-item-circle">
                                                                <span class="fa-solid fa-calendar-alt nav-item-icon"></span>
                                                                <span class="fa-solid fa-check check-icon"></span>
                                                            </span>
                                                        </span>
                                                        <span class="nav-item-title fs-9 fs-xl-8">Délimitation</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link py-0 py-md-3" href="#phoenix-wizard-tab3" data-bs-toggle="tab" data-wizard-step="3" aria-selected="false" tabindex="-1" role="tab">
                                                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                                                        <span class="nav-item-circle-parent">
                                                            <span class="nav-item-circle">
                                                                <span class="fa-solid fa-list-alt nav-item-icon"></span>
                                                                <span class="fa-solid fa-check check-icon"></span>
                                                            </span>
                                                        </span>
                                                        <span class="nav-item-title fs-9 fs-xl-8">Objectifs</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link py-0 py-md-3" href="#phoenix-wizard-tab4" data-bs-toggle="tab" data-wizard-step="4" aria-selected="false" tabindex="-1" role="tab">
                                                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                                                        <span class="nav-item-circle-parent">
                                                            <span class="nav-item-circle">
                                                                <span class="fa-solid fa-newspaper nav-item-icon"></span>
                                                                <span class="fa-solid fa-check check-icon"></span>
                                                            </span>
                                                        </span>
                                                        <span class="nav-item-title fs-9 fs-xl-8">Description</span>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <a class="nav-link py-0 py-md-3" href="#phoenix-wizard-tab5" data-bs-toggle="tab" data-wizard-step="5" aria-selected="false" tabindex="-1" role="tab">
                                                    <div class="text-center d-inline-block d-md-flex align-items-center gap-3">
                                                        <span class="nav-item-circle-parent">
                                                            <span class="nav-item-circle">
                                                                <span class="fa-solid fa-check-double nav-item-icon"></span>
                                                                <span class="fa-solid fa-check check-icon"></span>
                                                            </span>
                                                        </span>
                                                        <span class="nav-item-title fs-9 fs-xl-8">Validation</span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div id="FormProjet" class="tab-content">
                                        <div class="tab-pane active" role="tabpanel" aria-labelledby="phoenix-wizard-tab1" id="phoenix-wizard-tab1">
                                            <form id="wizProjetForm1" novalidate="novalidate" class="needs-validation" data-wizard-form="1">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-2 mb-3">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <div class="avatar avatar-4xl">
                                                                <img id="projetLoadImage" class="rounded-1 border border-light shadow-sm rounded-1 w-100 avatar-placeholder d-none" src="" alt="no-image">
                                                                <i id="projetLoadImageIcon" class="far fa-image text-body-tertiary" style="width: 100%; font-size:100px"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-10 mb-3">
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

                                                <div class="row g-3">
                                                    <!-- Nom du projet -->
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-floating">
                                                            <input class="form-control" name="name" id="projetName" type="text" placeholder="Intitulé" required>
                                                            <label for="projetName">Intitulé*</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-floating">
                                                            <input oninput="checkColumns('code', 'projetCode', 'projetCodeFeedback', 'projets')" class="form-control" name="code" id="projetCode" type="text" placeholder="Code" required>
                                                            <label for="projetCode">Code*</label>
                                                            <div id="projetCodeFeedback" class="invalid-feedback"></div>
                                                        </div>
                                                    </div>

                                                    <!-- Budget -->
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-floating">
                                                            <input class="form-control" name="budget" id="projetBudget" type="number" placeholder="Budget" required>
                                                            <label for="projetBudget">Budget*</label>
                                                        </div>
                                                    </div>
                                                    <!-- Acteur -->
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-floating">
                                                            <select class="form-select" name="structure_id" id="projetStructure" required>
                                                                <option value="" selected disabled>Sélectionner un acteur</option>
                                                                <?php if ($structures ?? []) : ?>
                                                                    <?php foreach ($structures as $structure) : ?>
                                                                        <option value="<?= $structure['id'] ?>"><?= $structure['sigle'] ?></option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                            <label for="projetManager">Gestionnaire*</label>
                                                        </div>
                                                    </div>
                                                    <!-- Action -->
                                                    <div class="col-md-6 mb-3">
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
                                                    <!-- Priorité -->
                                                    <div class="col-md-6 mb-3">
                                                        <div class="form-floating">
                                                            <select class="form-select" name="priorites_id" id="projetPriorite" required>
                                                                <option value="" selected disabled>Sélectionner une priorité</option>
                                                                <?php if ($priorites ?? []) : ?>
                                                                    <?php foreach ($priorites as $priorite) : ?>
                                                                        <option value="<?= $priorite['id'] ?>"><?= $priorite['name'] ?></option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                            <label for="projetPriorite">Priorité*</label>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="status" id="projetStatus" value="Planifié">
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab2" id="phoenix-wizard-tab2">
                                            <form id="wizProjetForm2" novalidate="novalidate" class="needs-validation" data-wizard-form="2">
                                                <div class="row g-3">
                                                    <!-- Dates -->
                                                    <div class="col-md-6 mb-2">
                                                        <div class="flatpickr-input-container">
                                                            <div class="form-floating">
                                                                <input class="form-control datetimepicker" name="start_date" id="projetStartDate" type="text" placeholder="Date de début" data-options="{&quot;disableMobile&quot;:true}" readonly>
                                                                <label class="ps-6" for="projetStartDate">Date de début</label>
                                                                <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-2">
                                                        <div class="flatpickr-input-container">
                                                            <div class="form-floating">
                                                                <input class="form-control datetimepicker" name="end_date" id="projetEndDate" type="text" placeholder="Date de clôture" data-options="{&quot;disableMobile&quot;:true}" readonly>
                                                                <label class="ps-6" for="projetEndDate">Date de clôture</label>
                                                                <span class="uil uil-calendar-alt flatpickr-icon text-body-tertiary"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 mb-2">
                                                        <div class="flatpickr-input-container">
                                                            <div class="form-floating">
                                                                <input class="form-control datetimepicker" name="signature_date" id="projetSignatureDate" type="text" placeholder="Date de signature" data-options="{&quot;disableMobile&quot;:true}" readonly>
                                                                <label class="ps-6" for="projetSignatureDate">Date de signature</label>
                                                                <span class="uil uil-pen flatpickr-icon text-body-tertiary"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="flatpickr-input-container">
                                                            <div class="form-floating">
                                                                <input class="form-control datetimepicker" name="miparcours_date" id="projetMiparcours" type="text" placeholder="Date de mi-parcours" data-options="{&quot;disableMobile&quot;:true}" readonly>
                                                                <label class="ps-6" for="projetMiparcours">Date de mi-parcours</label>
                                                                <span class="uil uil-clock flatpickr-icon text-body-tertiary"></span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 mb-2">
                                                        <div class="form-floating form-floating-advance-select">
                                                            <label for="MultipleSelectSecteur">Secteur concerné*</label>
                                                            <select class="form-select" name="secteurs_listID" id="MultipleSelectSecteur" data-choices="data-choices" multiple="multiple" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                                                <option value="" disabled>Sélectionner un secteur</option>
                                                                <?php if ($secteurs ?? []) : ?>
                                                                    <?php foreach ($secteurs as $secteur) : ?>
                                                                        <?php if (in_array($secteur['id'], explode(',', str_replace('"', '', $project_curr['secteurs'] ?? '')))) : ?>
                                                                            <option value="<?= $secteur['id'] ?>" selected><?= $secteur['name'] ?></option>
                                                                        <?php else : ?>
                                                                            <option value="<?= $secteur['id'] ?>"><?= $secteur['name'] ?></option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 mb-2">
                                                        <div class="form-floating form-floating-advance-select">
                                                            <label for="MultipleSelectGroupe">Groupe de travail concerné*</label>
                                                            <select class="form-select" name="groupes_listID" id="MultipleSelectGroupe" data-choices="data-choices" multiple="multiple" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                                                <option value="" disabled>Sélectionner un groupe</option>
                                                                <?php if ($groupes_travail ?? []) : ?>
                                                                    <?php foreach ($groupes_travail as $groupe) : ?>
                                                                        <?php if (in_array($groupe['id'], explode(',', str_replace('"', '', $project_curr['groupes'] ?? '')))) : ?>
                                                                            <option value="<?= $groupe['id'] ?>" selected><?= $groupe['name'] ?></option>
                                                                        <?php else : ?>
                                                                            <option value="<?= $groupe['id'] ?>"><?= $groupe['name'] ?></option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- <div class="col-md-12 mb-2">
                                                        <div class="form-floating form-floating-advance-select">
                                                            <label for="MultipleSelectProgramme">Programmes concernés*</label>
                                                            <select class="form-select" name="programmes_listID" id="MultipleSelectProgramme" data-choices="data-choices" multiple="multiple" data-options='{"removeItemButton":true,"placeholder":true}' required>
                                                                <option value="" disabled>Sélectionner un programme</option>
                                                                <?php if ($programmes ?? []) : ?>
                                                                    <?php foreach ($programmes as $programme) : ?>
                                                                        <?php if (in_array($programme['id'], explode(',', str_replace('"', '', $project_curr['programmes'] ?? '')))) : ?>
                                                                            <option value="<?= $programme['id'] ?>" selected><?= $programme['name'] ?></option>
                                                                        <?php else : ?>
                                                                            <option value="<?= $programme['id'] ?>"><?= $programme['name'] ?></option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div> -->
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab3" id="phoenix-wizard-tab3">
                                            <form id="wizProjetForm3" novalidate="novalidate" class="needs-validation" data-wizard-form="3">
                                                <div class="row g-3">
                                                    <div class="col-12" style="height: 335px;">
                                                        <label for="projetObjectif" class="fs-9 fw-semibold">Objectif du projet</label>
                                                        <textarea class="tinymce-editor" name="objectif" data-tinymce="{}" id="projetObjectif"></textarea>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab4" id="phoenix-wizard-tab4">
                                            <form id="wizProjetForm4" novalidate="novalidate" class="needs-validation" data-wizard-form="4">
                                                <div class="row g-3">
                                                    <div class="col-12" style="height: 335px;">
                                                        <label for="projetDescription" class="fs-9 fw-semibold">Description du projet</label>
                                                        <textarea class="tinymce-editor" name="description" data-tinymce="{}" id="projetDescription"></textarea>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane" role="tabpanel" aria-labelledby="phoenix-wizard-tab5" id="phoenix-wizard-tab5">
                                            <form id="wizProjetForm5" novalidate="novalidate" class="needs-validation" data-wizard-form="5">
                                                <div class="row flex-center py-5 g-3">
                                                    <div class="col-4">
                                                        <div class="text-center align-items-center">
                                                            <img class="d-dark-none" src="./assets/img/spot-illustrations/23.png" alt="" width="250">
                                                            <img class="d-light-none" src="./assets/img/spot-illustrations/dark_23.png" alt="" width="250">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-center">
                                                            <h5 class="mb-3">Validation des informations du projet</h5>
                                                            <p class="text-body-emphasis fs-9">Veuillez vous assurer de la véracité des informations du projet</p>
                                                            <div class="d-flex justify-content-center border-0 px-0 pb-0">
                                                                <button type="button" class="btn btn-primary my-0 px-5" id="projet_modbtn">Valider les données</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer border-top border-light" data-wizard-footer="data-wizard-footer">
                            <div class="d-flex pager justify-content-between wizard list-inline mb-0">
                                <button class="d-none btn btn-secondary px-3" type="button" data-wizard-prev-btn="data-wizard-prev-btn">
                                    <span class="fas fa-chevron-left me-1" data-fa-transform="shrink-3"></span>
                                    Précédent
                                </button>
                                <button class="btn btn-primary px-3" type="submit" data-wizard-next-btn="data-wizard-next-btn">
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
    let formProjetID = null;
    $(document).ready(function() {
        $('#addProjetModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            $('#projetLoadingScreen').show();
            $('#projetContentContainer').hide();

            if (dataId) {
                formProjetID = dataId;
                $('#projet_modtitle').text('Modifier le projet');
                $('#projet_modbtn').text('Modifier les données');
                $('#projetLoadingText').text("Chargement des données projet...");

                try {
                    const response = await fetch(`./apis/projets.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status !== 'success') throw new Error(result.message || 'Erreur de données');

                    const form = document.forms['wizProjetForm1'];
                    form.file.filename = result.data.logo || '';
                    form.logo.value = result.data.logo || '';
                    form.name.value = result.data.name || '';
                    form.code.value = result.data.code || '';
                    form.budget.value = result.data.budget || 0;
                    form.structure_id.value = result.data.structure_id || '';
                    form.action_type.value = result.data.action_type || '';
                    form.priorites_id.value = result.data.priorites_id || '';
                    form.status.value = result.data.status || '';

                    const form2 = document.forms['wizProjetForm2'];
                    form2.start_date.value = result.data.start_date || '';
                    form2.end_date.value = result.data.end_date || '';
                    form2.signature_date.value = result.data.signature_date || '';
                    form2.miparcours_date.value = result.data.miparcours_date || '';

                    if (result.data.logo) {
                        $('#projetLoadImage').attr('src', result.data.logo.split("../").pop());
                        $('#projetLoadImage').removeClass('d-none');
                        $('#projetLoadImageIcon').addClass('d-none');
                    }
                    tinymce.get('projetObjectif')?.setContent(result.data.objectif || '');
                    tinymce.get('projetDescription')?.setContent(result.data.description || '');
                } catch (error) {
                    errorAction('Erreur lors du chargement des données: ' + error.message);
                } finally {
                    $('#projetLoadingScreen').hide();
                    $('#projetContentContainer').show();
                }
            } else {
                $('#projet_modtitle').text('Ajouter un projet');
                $('#projet_modbtn').text('Valider les données');
                $('#projetLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#projetLoadingScreen').hide();
                    $('#projetContentContainer').show();
                }, 200);
            }
        });

        $('#addProjetModal').on('hide.bs.modal', function() {
            $('#wizProjetForm1')[0].reset();
            $('#projetLoadImage').attr('src', '');
            $('#projetLoadImage').addClass('d-none');
            $('#projetLoadImageIcon').removeClass('d-none');
            $('#projetImage').val('');
            $('#MultipleSelectSecteur').val('');
            $('#MultipleSelectGroupe').val('');
            // $('#MultipleSelectProgramme').val('');

            tinymce.get('projetObjectif')?.setContent('');
            tinymce.get('projetDescription')?.setContent('');

            setTimeout(() => {
                $('#projetLoadingScreen').show();
                $('#projetContentContainer').hide();
            }, 200);
        });

        $('#projetImage').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#projetLoadImage').attr('src', e.target.result);
                    $('#projetLoadImage').removeClass('d-none');
                    $('#projetLoadImageIcon').addClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#projetLoadImage').addClass('d-none');
                $('#projetLoadImageIcon').removeClass('d-none');
            }
        });

        $('#projet_modbtn').on('click', async function() {
            const submitBtn = $('#projet_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const formData = new FormData();
                const forms = [
                    document.getElementById('wizProjetForm1'),
                    document.getElementById('wizProjetForm2'),
                    document.getElementById('wizProjetForm3'),
                    document.getElementById('wizProjetForm4')
                ];

                forms.forEach(form => {
                    if (form) {
                        const formElements = form.elements;
                        for (let element of formElements) {
                            if (element.name && element.type !== 'file') {
                                formData.append(element.name, element.value);
                            }
                        }
                    }
                });

                formData.append('objectif', tinymce.get('projetObjectif')?.getContent() || '');
                formData.append('description', tinymce.get('projetDescription')?.getContent() || '');
                formData.append('status', $('#projetStatus').val());
                formData.append('secteurs', $('#MultipleSelectSecteur').val());
                formData.append('groupes', $('#MultipleSelectGroupe').val());
                //formData.append('programmes', $('#MultipleSelectProgramme').val());

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
                    successAction('Projet enregistré avec succès!');
                    $('#addProjetModal').modal('hide');
                } else {
                    throw new Error(result.message || 'Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                errorAction('Erreur: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>