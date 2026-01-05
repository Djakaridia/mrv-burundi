<!-- modal -->
<div class="modal fade" id="addNiveauModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="viewNiveauModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="niveau_modtitle">Ajouter un niveau</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="niveauLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="niveauLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="niveauContentContainer" style="display: none;">
                    <form niveau="" name="FormNiveau" id="FormNiveau" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Niveau*</label>
                                    <input class="form-control form-control-sm rounded-1" type="text" name="name"
                                        id="niveau_name" placeholder="Entrer le niveau" required />
                                </div>
                            </div>
                            <div class="col-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Type*</label>
                                    <select class="form-select form-select-sm rounded-1" name="type" id="niveau_type"
                                        required>
                                        <option value="">Sélectionner le type</option>
                                        <option value="effet">Effet</option>
                                        <option value="impact">Impact</option>
                                        <option value="produit">Produit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-1 mt-1">
                                <input type="hidden" name="level" id="niveau_level" value="0" />
                                <input type="hidden" name="programme" id="niveau_programme" value="" />
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="niveau_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formNiveauID = null;
    $(document).ready(function() {
        $('#addNiveauModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const nextLevel = $(event.relatedTarget).data('next_level');
            const programme = $(event.relatedTarget).data('programme');
            const form = document.getElementById('FormNiveau');

            $('#niveauLoadingScreen').show();
            $('#niveauContentContainer').hide();

            if(nextLevel){
                form.level.value = nextLevel;
            }

            if(programme){
                form.programme.value = programme;
            }

            if (dataId) {
                formNiveauID = dataId;
                $('#niveau_modtitle').text('Modifier un niveau');
                $('#niveau_modbtn').text('Modifier');
                $('#niveauLoadingText').text("Chargement des données niveau...");

                try {
                    const response = await fetch(`./apis/niveaux.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.level.value = result.data.level;
                    form.type.value = result.data.type;
                    form.programme.value = result.data.programme;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    // Hide loading screen and show content
                    $('#niveauLoadingScreen').hide();
                    $('#niveauContentContainer').show();
                }
            } else {
                formNiveauID = null;
                $('#niveau_modtitle').text('Ajouter un niveau');
                $('#niveau_modbtn').text('Ajouter');
                $('#niveauLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#niveauLoadingScreen').hide();
                    $('#niveauContentContainer').show();
                }, 200);
            }

        });

        $('#addNiveauModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#niveauLoadingScreen').show();
                $('#niveauContentContainer').hide();
            }, 200);
            $('#FormNiveau')[0].reset();
        });

        $('#FormNiveau').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formNiveauID ? `./apis/niveaux.routes.php?id=${formNiveauID}` : './apis/niveaux.routes.php';
            const submitBtn = $('#niveau_modbtn');
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
                    $('#addNiveauModal').modal('hide');
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