<div class="modal fade" id="coutTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body-highlight p-4">
            <!-- Header avec bouton fermer et titre -->
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0 fw-bold text-primary"> <span class="fas fa-tasks me-2"></span>Cout des tâches </h3>
                <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal"
                    aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="coutLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="coutLoadingText">Chargement en cours</h4>
                </div>

                <div id="coutContentContainer" style="display: none;">
                    <?php if (isset($conventions_project) && count($conventions_project) > 0) { ?>
                        <form id="coutTaskForm">
                            <input type="hidden" id="cout_tache_id" name="tache_id">

                            <div class="card rounded-1 mb-3" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover table-striped fs-12 table-bordered border-emphasis" align="center">
                                    <thead class="bg-primary-subtle">
                                        <tr>
                                            <th scope="col" class="fs-12 px-2 text-center" width="40%">Convention</th>
                                            <th scope="col" class="fs-12 px-2 text-center" width="60%">Montant (USD)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($conventions_project as $convention) { ?>
                                            <tr>
                                                <td class="align-middle text-start px-2">
                                                    <?= $convention['name'] ?>
                                                </td>
                                                <td class="align-middle text-center px-2">
                                                    <input type="text" class="form-control py-2"
                                                        name="cout_tache[<?= $convention['id'] ?>]"
                                                        id="cout_tache-<?= $convention['id'] ?>"
                                                        value="">
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Boutons -->
                            <div class="modal-footer d-flex justify-content-between border-0 p-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="cout_tache_modbtn">Enregistrer</button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="col-12">
                            <div class="card text-center p-10 mb-3">
                                <h4 class="mb-5 text-dark">
                                    <span class="fas fa-history me-2"></span>Aucune convention disponible
                                </h4>
                                <?php if (isset($project_id)) { ?>
                                    <a href="<?php echo $_SERVER['PHP_SELF'] . '?id=' . $project_id . '&tab=finance'; ?>" class="btn btn-primary btn-sm px-3 my-0">Ajouter une convention</a>
                                <?php } ?>
                            </div>
                            <div class="modal-footer d-flex justify-content-between border-0 p-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#coutTaskModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            $('#cout_tache_id').val(dataId);

            $('#coutLoadingScreen').show();
            $('#coutContentContainer').hide();
            if (dataId) {
                $('#coutLoadingText').text("Chargement des données cibles...");

                try {
                    const response = await fetch(`./apis/tache_cout.routes.php?tache_id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success' && result.data?.length) {
                        result.data.forEach(item => {
                            $(`#cout_tache-${item.convention}`).val(item.montant);
                        });
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#coutLoadingScreen').hide();
                    $('#coutContentContainer').show();
                }
            } else {
                $('#coutLoadingText').text("Préparation du formulaire...");
                setTimeout(() => {
                    $('#coutLoadingScreen').hide();
                    $('#coutContentContainer').show();
                }, 200);
            }
        });

        $('#coutTaskModal').on('hidden.bs.modal', function() {
            $('#coutTaskForm')[0].reset();
            $('#coutLoadingScreen').show();
            $('#coutContentContainer').hide();
        });

        // Soumission du formulaire
        $('#coutTaskForm').on('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const couts = {};

            // Récupération structurée des données
            $('[name^="cout_tache["]').each(function() {
                const matches = this.name.match(/cout_tache\[(\d+)\]/);
                if (matches) {
                    const conventionId = matches[1];
                    if (!couts[conventionId]) couts[conventionId] = {};
                    couts[conventionId] = {
                        convention: conventionId,
                        montant: this.value
                    };
                }
            });

            formData.append('valeur_couts', JSON.stringify(couts));
            const submitBtn = $('#cout_tache_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const response = await fetch('./apis/tache_cout.routes.php', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données enregistrées avec succès');
                    $('#coutTaskModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données');
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>