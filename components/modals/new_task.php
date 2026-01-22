<div class="modal fade" id="addTacheModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addTacheModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="tache_modtitle">Ajouter une activité</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="taskLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="taskLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="taskContentContainer" style="display: none;">
                    <form action="" method="POST" name="FormTache" id="FormTache">
                        <div class="row g-4">

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code</label>
                                    <input class="form-control" type="text" name="code" id="tache_code"
                                        placeholder="Entrer le code de la tâche" required />
                                    <div id="tache_codeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé</label>
                                    <input class="form-control" type="text" name="name"
                                        placeholder="Entrer le nom de la tâche" required />
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label for="description"
                                        class="text-body-highlight mb-2">Description</label>
                                    <textarea class="form-control" rows="3" name="description"
                                        placeholder="Décriver la tâche"></textarea>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Début prévu</label>
                                    <input class="form-control datetimepicker" placeholder="Date de début"
                                        type="datetime-local" name="debut_prevu" />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Fin prévue</label>
                                    <input class="form-control datetimepicker" placeholder="Date de fin"
                                        type="datetime-local" name="fin_prevue" />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Assigné à*</label>
                                    <select class="form-select" name="assigned_id" required>
                                        <option value="">Sélectionner un utilisateur</option>
                                        <?php if ($users ?? []) : ?>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>"><?= $user['nom'] . ' ' . $user['prenom'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Priorité*</label>
                                    <select class="form-select" name="priorite" required>
                                        <option value="" selected disabled>Sélectionner une priorité</option>
                                        <option value="urgent"> Urgent </option>
                                        <option value="normal"> Normal </option>
                                        <option value="faible"> Faible </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <input type="hidden" name="projet_id" value="<?= $project_curr['id'] ?? '' ?>">
                            <input type="hidden" name="status" id="taskStatus" value="planifiée">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="tache_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formTacheID = null;
    $(document).ready(function() {
        $('#addTacheModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormTache');
            // Show loading screen and hide content
            $('#taskLoadingScreen').show();
            $('#taskContentContainer').hide();
            if (dataId) {
                formTacheID = dataId;
                $('#tache_modtitle').text('Modifier l\'activité');
                $('#tache_modbtn').text('Modifier');
                $('#taskLoadingText').text("Chargement des données activité...");

                try {
                    const response = await fetch(`./apis/taches.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.code.value = result.data.code;
                    form.description.value = result.data.description;
                    form.status.value = result.data.status;
                    form.priorite.value = result.data.priorite;
                    form.debut_prevu.value = result.data.debut_prevu;
                    form.fin_prevue.value = result.data.fin_prevue;
                    form.assigned_id.value = result.data.assigned_id;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    // Hide loading screen and show content
                    $('#taskLoadingScreen').hide();
                    $('#taskContentContainer').show();
                }
            } else {
                formTacheID = null;
                $('#tache_modtitle').text('Ajouter une activité');
                $('#tache_modbtn').text('Ajouter');
                $('#taskLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#taskLoadingScreen').hide();
                    $('#taskContentContainer').show();
                }, 200);
            }
        });

        $('#addTacheModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#taskLoadingScreen').show();
                $('#taskContentContainer').hide();
            }, 200);
            $('#FormTache')[0].reset();
        });


        $('#FormTache').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formTacheID ? `./apis/taches.routes.php?id=${formTacheID}` : './apis/taches.routes.php';
            const submitBtn = $('#tache_modbtn');
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
                    $('#addTacheModal').modal('hide');
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