<div class="modal fade" id="addPartenaireModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addPartenaireModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="partenaire_modtitle">Ajouter un acteur</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="partenaireLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="partenaireLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="partenaireContentContainer" style="display: none;">
                    <form action="" name="FormPartenaire" id="FormPartenaire" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-4 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'partenaire_code', 'partenaire_codeFeedback', 'partenaires')" class="form-control" type="text" name="code" id="partenaire_code" placeholder="Entrer le code"
                                        required />
                                    <div id="partenaire_codeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-8 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Sigle*</label>
                                    <input class="form-control" type="text" name="sigle" placeholder="Entrer le sigle"
                                        required />
                                </div>
                            </div>
                            

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="description_partenaire"
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
                                    <label class="form-label">Périmetre*</label>
                                    <select class="form-select" name="perimetre" id="partenaire_perimetre" required>
                                        <option value="" selected disabled>Sélectionner le périmetre</option>
                                        <option value="national">National</option>
                                        <option value="international">International</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="partenaire_modbtn">Ajouter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    let formPartenaireID = null;
    $(document).ready(function() {
        $('#addPartenaireModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormPartenaire');

            $('#partenaireLoadingScreen').show();
            $('#partenaireContentContainer').hide();
            if (dataId) {
                formPartenaireID = dataId;
                $('#partenaire_modtitle').text('Modifier l\'acteur');
                $('#partenaire_modbtn').text('Modifier');
                $('#partenaireLoadingText').text("Chargement des données acteur...");

                try {
                    const response = await fetch(`./apis/partenaires.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.code.value = result.data.code;
                    form.sigle.value = result.data.sigle;
                    form.email.value = result.data.email;
                    form.description.value = result.data.description;
                    form.perimetre.value = result.data.perimetre;

                    if (result.data.logo) {
                        $('#partenaireLoadImage').attr('src', result.data.logo.split("../").pop());
                        $('#partenaireLoadImage').removeClass('d-none');
                        $('#partenaireLoadImageIcon').addClass('d-none');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#partenaireLoadingScreen').hide();
                    $('#partenaireContentContainer').show();
                }
            } else {
                formPartenaireID = null;
                $('#partenaire_modtitle').text('Ajouter un acteur');
                $('#partenaire_modbtn').text('Ajouter');
                $('#partenaireLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#partenaireLoadingScreen').hide();
                    $('#partenaireContentContainer').show();
                }, 200);
            }
        });

        $('#addPartenaireModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#partenaireLoadingScreen').show();
                $('#partenaireContentContainer').hide();
            }, 200);
            $('#FormPartenaire')[0].reset();
        });

        $('#FormPartenaire').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formPartenaireID ? `./apis/partenaires.routes.php?id=${formPartenaireID}` : './apis/partenaires.routes.php';
            const submitBtn = $('#partenaire_modbtn');
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
                    $('#addPartenaireModal').modal('hide');
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