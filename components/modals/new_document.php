<?php $array_type = ".doc, .docx, .odt, .rtf, .txt, .ppt, .pptx, .odp, .xls, .xlsx, .ods, .csv, .pdf, .jpg, .jpeg, .png, .gif, .webp, .tiff, .ico";?>

<div class="modal fade" id="addDocumentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addDocumentModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="document_modtitle">Ajouter un document</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="documentLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="documentLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="documentContentContainer" style="display: none;">
                    <form action="" name="FormDocument" id="FormDocument" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-12 mt-1">
                                <div class="card-title text-center">Importation des fichiers </div>
                                <div class="text-center">(<?php echo $array_type ?>)</div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé du fichier*</label>
                                    <input class="form-control" type="text" name="name" id="name_file"
                                        placeholder="Entrer le libellé" required />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Dossier*</label>
                                    <select class="form-select" name="dossier_id" id="dossier_id" required>
                                        <option value="">Sélectionner un dossier</option>
                                        <?php if ($dossiers ?? []) : ?>
                                            <?php foreach ($dossiers as $dossier): ?>
                                                <option value="<?= $dossier['id'] ?>"><?= $dossier['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3">
                                <label for="file_document" class="bg-white dark__bg-dark btn border border-dashed rounded-1 px-2 py-3 w-100">
                                    <input type="file" name="file" id="file_document" accept="<?php echo $array_type ?>"
                                        class="form-control d-none w-100" required />
                                    <input type="hidden" name="allow_files" id="allow_file_document"
                                        value="<?php echo $array_type; ?>">
                                    <div class="text-center text-body-emphasis mb-2">
                                        <h5 class="mb-3"> <span class="fa-solid fa-upload me-2"></span> Télécharger un
                                            document</h5>
                                        <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Télécharger un
                                            document dans les formats suivants : <br> <?php echo $array_type ?></p>
                                    </div>
                                    <span id="file_document_name" class="fs-9 text-info lh-sm"></span>
                                </label>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="description_file"
                                        placeholder="Entrer une description"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-between border-0 px-3 pb-0">
                                <input type="hidden" name="entity_id" id="entity_id_document">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                    aria-label="Close">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                    id="document_modbtn">Ajouter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formDocumentID = null;
    $(document).ready(function() {
        $('#addDocumentModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const dossierId = $(event.relatedTarget).data('dossier-id');
            const entityId = $(event.relatedTarget).data('entity-id');
            const form = document.getElementById('FormDocument');

            $('#documentLoadingScreen').show();
            $('#documentContentContainer').hide();
            if (dossierId) {
                form.dossier_id.value = dossierId;
                $('#dossier_id').attr('readonly', true);
                $('#dossier_id').addClass('bg-light');
                $('#dossier_id').css('pointerEvents', 'none');
            }

            if (entityId) {
                form.entity_id.value = entityId;
            }

            if (dataId) {
                formDocumentID = dataId;
                $('#document_modtitle').text('Modifier le document');
                $('#document_modbtn').text('Modifier');
                $('#documentLoadingText').text("Chargement des données documents...");

                try {
                    const response = await fetch(`./apis/documents.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.dossier_id.value = result.data.dossier_id;
                    form.description.value = result.data.description;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    // Hide loading screen and show content
                    $('#documentLoadingScreen').hide();
                    $('#documentContentContainer').show();
                }
            } else {
                formDocumentID = null;
                $('#document_modtitle').text('Ajouter un document');
                $('#document_modbtn').text('Ajouter');
                $('#documentLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#documentLoadingScreen').hide();
                    $('#documentContentContainer').show();
                }, 200);
            }
        });

        $('#addDocumentModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#documentLoadingScreen').show();
                $('#documentContentContainer').hide();
            }, 200);
            $('#FormDocument')[0].reset();
        });

        $('#file_document').on('change', function() {
            const fileName = this.files[0].name;
            $('#file_document_name').text(fileName);
        });

        $('#FormDocument').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $('#document_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const response = await fetch("./apis/documents.routes.php", {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données envoyées avec succès.');
                    $('#addDocumentModal').modal('hide');
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