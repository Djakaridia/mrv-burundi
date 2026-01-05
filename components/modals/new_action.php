<!-- modal -->
<div class="modal fade" id="addActionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addActionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="action_modtitle">Ajouter une action</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="actionLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="actionLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="actionContentContainer" style="display: none;">
                    <form action="" name="FormAction" id="FormAction" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'action_code', 'action_code_feedback', 'actions')" class="form-control" type="text" name="code" id="action_code" placeholder="Entrer le code" required />
                                    <div class="invalid-feedback" id="action_code_feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name" id="action_name" placeholder="Entrer le libellé" required />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Objectif</label>
                                    <input class="form-control" type="text" name="objectif" id="action_objectif" placeholder="Entrer l'objectif" />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Secteur*</label>
                                    <select class="form-select" name="secteur_id" id="action_secteur_id" required>
                                        <option value="">Sélectionner un secteur</option>
                                        <?php if ($secteurs ?? []) : ?>
                                            <?php foreach ($secteurs as $secteur) : ?>
                                                <option value="<?php echo $secteur['id'] ?>"><?php echo $secteur['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="action_description" placeholder="Entrer la description" style="height: 60px"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="action_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formActionID = null;
    $(document).ready(function() {
        $('#addActionModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormAction');

            $('#actionLoadingScreen').show();
            $('#actionContentContainer').hide();
            form.reset();

            if (dataId) {
                formActionID = dataId;
                $('#action_modtitle').text('Modifier l\'action');
                $('#action_modbtn').text('Modifier');
                $('#actionLoadingText').text("Chargement des données de l'action...");

                try {
                    const response = await fetch(`./apis/actions.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        form.secteur_id.value = result.data.secteur_id;
                        form.name.value = result.data.name;
                        form.code.value = result.data.code;
                        form.objectif.value = result.data.objectif;
                        form.description.value = result.data.description;
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#actionLoadingScreen').hide();
                    $('#actionContentContainer').show();
                }
            } else {
                $('#actionLoadingText').text("Préparation du formulaire...");
                $('#action_modtitle').text('Ajouter une action');
                $('#action_modbtn').text('Ajouter');
                formActionID = null;

                setTimeout(() => {
                    $('#actionLoadingScreen').hide();
                    $('#actionContentContainer').show();
                }, 200);
            }
        });

        $('#addActionModal').on('hide.bs.modal', function() {
            $('#FormAction')[0].reset();
            setTimeout(() => {
                $('#actionLoadingScreen').show();
                $('#actionContentContainer').hide();
            }, 200);
        });

        $('#FormAction').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = formActionID ? `./apis/actions.routes.php?id=${formActionID}` : './apis/actions.routes.php';
            const submitBtn = $('#action_modbtn');
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
                    successAction(formActionID ? 'Action modifiée avec succès.' : 'Action ajoutée avec succès.');
                    $('#addActionModal').modal('hide');
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