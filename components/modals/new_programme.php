<div class="modal fade" id="addProgrammeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addProgrammeModal" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="programme_modtitle">Ajouter un programme</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="programmeLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="programmeLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="programmeContentContainer" style="display: none;">
                    <form action="" name="addProgrammeForm" id="addProgrammeForm" method="POST"
                        enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="programmeCode" class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'programmeCode', 'programmeCodeFeedback', 'programmes')" class="form-control" type="text" name="code" id="programmeCode"
                                        placeholder="Entrer le code" required />
                                    <div id="programmeCodeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="programmeSigle" class="form-label">Sigle*</label>
                                    <input class="form-control" type="text" name="sigle" id="programmeSigle"
                                        placeholder="Entrer le sigle" required />
                                </div>
                            </div>
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label for="programmeTitle"
                                        class="form-label">Intitulé*</label>
                                    <input class="form-control" type="text" name="name" id="programmeTitle"
                                        placeholder="Entrer le nom" required />
                                </div>
                            </div>

                            <!-- <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="programmeStartDate" class="form-label">Date de
                                        début</label>
                                    <input class="form-control datetimepicker" id="programmeStartDate" type="text"
                                        name="start_date" placeholder="YYYY-MM-DD"
                                        data-options="{&quot;disableMobile&quot;:true}" required />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="programmeEndDate" class="form-label">Date de
                                        fin</label>
                                    <input class="form-control datetimepicker" id="programmeEndDate" type="text"
                                        name="end_date" placeholder="YYYY-MM-DD"
                                        data-options="{&quot;disableMobile&quot;:true}" required />
                                </div>
                            </div> -->
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label for="programmeDescription"
                                        class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="programmeDescription"
                                        placeholder="Entrer la description" style="height: 60px"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <input type="hidden" name="status" id="programmeStatus" value="Planifié">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="programme_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    let formProgrammeID = null;
    $(document).ready(function () {
        $('#addProgrammeModal').on('shown.bs.modal', async function (event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('addProgrammeForm');
            
            // Show loading screen and hide content
            $('#programmeLoadingScreen').show();
            $('#programmeContentContainer').hide();
            
            if (dataId) {
                formProgrammeID = dataId;
                $('#programme_modtitle').text('Modifier le programme');
                $('#programme_modbtn').text('Modifier');
                $('#programmeLoadingText').text("Chargement des données programme...");

                try {
                    const response = await fetch(`./apis/programmes.routes.php?id=${dataId}`, {
                        headers: { 'Authorization': `Bearer ${token}` },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.sigle.value = result.data.sigle;
                    form.code.value = result.data.code;
                    // form.start_date.value = result.data.start_date;
                    // form.end_date.value = result.data.end_date;
                    form.description.value = result.data.description;
                    form.status.value = result.data.status;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                }
                finally {
                    $('#programmeLoadingScreen').hide();
                    $('#programmeContentContainer').show();
                }
            } else {
                formProgrammeID = null;
                $('#programme_modtitle').text('Ajouter un programme');
                $('#programme_modbtn').text('Ajouter');
                $('#programmeLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#programmeLoadingScreen').hide();
                    $('#programmeContentContainer').show();
                }, 200);
            }
        });

        $('#addProgrammeModal').on('hide.bs.modal', function () {
            setTimeout(()=> {
              $('#programmeLoadingScreen').show();
              $('#programmeContentContainer').hide();
            }, 200);
            $('#addProgrammeForm')[0].reset();
        });

        $('#addProgrammeForm').on('submit', async function (event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formProgrammeID ? `./apis/programmes.routes.php?id=${formProgrammeID}` : './apis/programmes.routes.php';
            const submitBtn = $('#programme_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const response = await fetch(url, {
                    headers: { 'Authorization': `Bearer ${token}` },
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données envoyées avec succès.');
                    $('#addProgrammeModal').modal('hide');
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