<?php $array_typeLogo = ".jpg, .jpeg, .png, .gif, .webp"; ?>

<div class="modal fade" id="addStructureModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addStructureModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="structure_modtitle">Ajouter un acteur</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="structureLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="structureLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="structureContentContainer" style="display: none;">
                    <form action="" name="FormStructure" id="FormStructure" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-4 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'structure_code', 'structure_codeFeedback', 'structures')" class="form-control" type="text" name="code" id="structure_code" placeholder="Entrer le code"
                                        required />
                                    <div id="structure_codeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-4 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Sigle*</label>
                                    <input class="form-control" type="text" name="sigle" placeholder="Entrer le sigle"
                                        required />
                                </div>
                            </div>
                            <div class="col-lg-4 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Type*</label>
                                    <select class="form-select" name="type_id" id="structure_type_id" required>
                                        <option value="">Sélectionner le type</option>
                                        <?php if ($type_structures ?? []) : ?>
                                            <?php foreach ($type_structures as $type_structure) { ?>
                                                <option value="<?php echo $type_structure['id']; ?>">
                                                    <?php echo $type_structure['name']; ?>
                                                </option>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="description_structure"
                                        placeholder="Entrer une description"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Email*</label>
                                    <input class="form-control" type="email" name="email" placeholder="Entrer l'email"
                                        required />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Contact*</label>
                                    <input class="form-control" type="text" name="phone" placeholder="Entrer le contact"
                                        required />
                                </div>
                            </div>


                            <div class="col-lg-2 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Logo</label>
                                    <div class="avatar avatar-4xl px-0 mx-0">
                                        <label for="file_structure">
                                            <img id="structureLoadImage" class="rounded-1 border border-light shadow-sm d-none cursor-pointer" src="" style="width: 100%; height:70px" alt="no-image">
                                            <i id="structureLoadImageIcon" class="far fa-image text-body-tertiary rounded-1 cursor-pointer mt-n1" style="width: 100%; font-size:80px"></i>
                                            <input type="file" name="file" id="file_structure" accept="<?php echo $array_typeLogo; ?>" class="form-control d-none">
                                            <input type="hidden" name="allow_files" id="allow_file_structure" value="<?php echo $array_typeLogo; ?>">
                                            <input type="hidden" name="logo" id="logo_structure" value="">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-10 mt-1">
                                <label class="form-label">Adresse</label>
                                <textarea class="form-control" name="address" id="address_structure"
                                    placeholder="Entrer l'adresse" style="height: 70px"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="structure_modbtn">Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    let formStructureID = null;
    $(document).ready(function() {
        $('#addStructureModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormStructure');

            $('#structureLoadingScreen').show();
            $('#structureContentContainer').hide();
            if (dataId) {
                formStructureID = dataId;
                $('#structure_modtitle').text('Modifier l\'acteur');
                $('#structure_modbtn').text('Modifier');
                $('#structureLoadingText').text("Chargement des données acteur...");

                try {
                    const response = await fetch(`./apis/structures.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.code.value = result.data.code;
                    form.logo.value = result.data.logo;
                    form.sigle.value = result.data.sigle;
                    form.email.value = result.data.email;
                    form.phone.value = result.data.phone;
                    form.address.value = result.data.address;
                    form.description.value = result.data.description;
                    form.type_id.value = result.data.type_id;

                    if (result.data.logo) {
                        $('#structureLoadImage').attr('src', result.data.logo.split("../").pop());
                        $('#structureLoadImage').removeClass('d-none');
                        $('#structureLoadImageIcon').addClass('d-none');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#structureLoadingScreen').hide();
                    $('#structureContentContainer').show();
                }
            } else {
                formStructureID = null;
                $('#structure_modtitle').text('Ajouter un acteur');
                $('#structure_modbtn').text('Ajouter');
                $('#structureLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#structureLoadingScreen').hide();
                    $('#structureContentContainer').show();
                }, 200);
            }
        });

        $('#addStructureModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#structureLoadingScreen').show();
                $('#structureContentContainer').hide();
            }, 200);
            $('#FormStructure')[0].reset();
        });

        $('#file_structure').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#structureLoadImage').attr('src', e.target.result);
                    $('#structureLoadImage').removeClass('d-none');
                    $('#structureLoadImageIcon').addClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#structureLoadImage').addClass('d-none');
                $('#structureLoadImageIcon').removeClass('d-none');
            }
        });

        $('#FormStructure').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formStructureID ? `./apis/structures.routes.php?id=${formStructureID}` : './apis/structures.routes.php';
            const submitBtn = $('#structure_modbtn');
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
                    $('#addStructureModal').modal('hide');
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