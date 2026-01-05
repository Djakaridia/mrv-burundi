<!-- modal -->
<div class="modal fade" id="addMetadataModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addMetadataModal" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="metadata_modtitle">Ajouter les méta-données</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="metadataLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="metadataLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="metadataContentContainer" style="display: none;">
                    <form action="" name="FormMetadata" id="FormMetadata" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="referentiel_id" id="metadata_referentiel_id" />
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Source*</label>
                                    <input class="form-control" type="text" name="source" id="metadata_source" placeholder="Entrer la source" required />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Date Edition*</label>
                                    <input class="form-control datetimepicker" type="text" name="date_ref" id="metadata_date" placeholder="Date d'édition"
                                        data-options='{"disableMobile":true,"dateFormat":"Y-m-d"}' required />
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <textarea class="tinymce-editor" name="description" id="metadata_description" data-tinymce="{}"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="metadata_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formMetadataID = null;
    $(document).ready(function() {
        $('#addMetadataModal').on('shown.bs.modal', async function(event) {
            const referentielId = $(event.relatedTarget).data('referentiel_id');
            const form = document.getElementById('FormMetadata');
            
            if(referentielId){
                form.referentiel_id.value = referentielId;
            }

            $('#metadataLoadingScreen').show();
            $('#metadataContentContainer').hide();
            if (referentielId) {
                $('#metadata_modtitle').text('Modifier les méta-données');
                $('#metadata_modbtn').text('Modifier');
                $('#metadataLoadingText').text("Chargement des données des méta-données...");

                try {
                    const response = await fetch(`./apis/metadata.routes.php?referentiel_id=${referentielId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        formMetadataID = result.data.id;
                        form.source.value = result.data.source;
                        form.date_ref.value = result.data.date_ref;
                        form.referentiel_id.value = result.data.referentiel_id;
                        tinymce.get('metadata_description')?.setContent(result.data.description || '');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#metadataLoadingScreen').hide();
                    $('#metadataContentContainer').show();
                }
            } else {
                $('#metadataLoadingText').text("Préparation du formulaire...");
                $('#metadata_modtitle').text('Ajouter les méta-données');
                $('#metadata_modbtn').text('Ajouter');

                setTimeout(() => {
                    $('#metadataLoadingScreen').hide();
                    $('#metadataContentContainer').show();
                }, 200);
            }
        });

        $('#addMetadataModal').on('hide.bs.modal', function() {
            $('#FormMetadata')[0].reset();
            setTimeout(() => {
                $('#metadataLoadingScreen').show();
                $('#metadataContentContainer').hide();
            }, 200);
        });

        $('#FormMetadata').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = formMetadataID ? `./apis/metadata.routes.php?id=${formMetadataID}` : './apis/metadata.routes.php';
            const submitBtn = $('#metadata_modbtn');
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
                    successAction(formMetadataID ? 'Metadata modifiée avec succès.' : 'Metadata ajoutée avec succès.');
                    $('#addMetadataModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>