<!-- modal -->
<div class="modal fade" id="SuiviTAskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0 fw-bold text-primary"> <span class="fas fa-tasks me-2"></span>Suivis de la tâche </h3>
                <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal"
                    aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>

            <div class="modal-body px-2">
                <div id="suiviTaskFormContainer" class="d-none">
                    <form action="" method="POST" name="suiviTaskForm" id="suiviTaskForm">
                        <div class="row gx-4">
                            <div class="col-md-6 mb-2">
                                <label for="suiviTask_status" class="form-label">Status de la tâche</label>
                                <select class="form-select" name="status" id="suiviTask_status" required>
                                    <option value="" selected disabled>Selectionner un status</option>
                                    <?php foreach (listStatus() as $key => $value) : ?>
                                        <option value="<?= $key ?>"><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="date_suivie" class="form-label">Date de suivi*</label>
                                <input class="form-control datetimepicker" id="suiviTask_date_suivie" type="text" name="date_suivie" value="<?= date('Y-m-d') ?>"
                                    placeholder="YYYY-MM-DD" data-options="{&quot;disableMobile&quot;:true}" required />
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="suiviTask_observation" class="form-label">Observation</label>
                                <textarea class="form-control" name="observation" id="suiviTask_observation" placeholder="Entrer la observation de la tâche" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="suiviTask_difficulte" class="form-label">Difficultés rencontrées</label>
                                <textarea class="form-control" name="difficulte" id="suiviTask_difficulte" placeholder="Entrer les difficultés rencontrées" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="suiviTask_solution" class="form-label">Solutions proposées</label>
                                <textarea class="form-control" name="solution" id="suiviTask_solution" placeholder="Entrer les solutions proposées" rows="2"></textarea>
                            </div>
                            <div class="modal-footer d-flex justify-content-between border-0 p-2 pb-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelSuiviTaskForm()">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="suivi_tache_modbtn">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="suiviTaskTabContainer"  class="card rounded-1 bg-white dark__bg-dark">
                    <div class="d-flex justify-content-between align-items-center p-2">
                        <h4 class="mb-0 text-dark">
                            <span class="fas fa-history me-2"></span>Historique des suivis
                        </h4>
                        <button class="btn btn-sm btn-primary" onclick="showSuiviTaskForm()">
                            <span class="fas fa-plus me-2"></span>Nouveau suivi
                        </button>
                    </div>

                    <div class="card border rounded-0 overflow-hidden mb-1">
                        <div class="card-body p-1 table-responsive scrollbar" style="min-height: 300px;max-height: 400px;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" align="center">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="align-middle px-2">Observation</th>
                                        <th class="align-middle text-center" style="width: 15%;">Date</th>
                                        <th class="align-middle text-center" style="width: 15%;">Status</th>
                                        <th class="align-middle text-center" style="width: 15%;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rowSuiviTache" class="list"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between border-0 m-0 p-1">
                        <button type="button" class="btn btn-subtle-secondary btn-sm px-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-subtle-primary btn-sm px-3" onclick="window.location.reload()"> Actualiser</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formTaskId = null;
    let suiviTaskId = null;
    const tabTaskStatus = <?= json_encode(listStatus()); ?>

    $(document).ready(function() {
        $('#SuiviTAskModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            formTaskId = dataId
            loadSuiviTask(dataId);
        });

        $('#SuiviTAskModal').on('hidden.bs.modal', async function(event) {
            $('#suiviTaskForm')[0].reset();
            cancelSuiviTaskForm();
        })

        $('#suiviTaskForm').submit(async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('tache_id', formTaskId);

            const submitBtn = $('#suivi_tache_modbtn');
            const taskID = $('#suiviTaskId').val();
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');
            try {
                const url = `./apis/tache_suivi.routes.php${suiviTaskId ? '?id=' + suiviTaskId : ''}`;
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction(result.message || 'Suivi enregistré avec succès', 'none');
                    cancelSuiviTaskForm();
                } else {
                    errorAction(result.message || 'Erreur lors de l\'enregistrement');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'enregistrement');
            } finally {
                loadSuiviTask(formTaskId);
            }
        });
    });

    function showSuiviTaskForm() {
        suiviTaskId = null;
        $('#suiviTaskFormContainer').removeClass('d-none');
        $('#suiviTaskTabContainer').addClass('d-none');
        $('#suiviTaskForm')[0].reset();
        $('#suiviTaskId').val('');
        $('#date_suivie').val(new Date().toISOString().slice(0, 16));
    }

    function cancelSuiviTaskForm() {
        $('#suiviTaskFormContainer').addClass('d-none');
        $('#suiviTaskTabContainer').removeClass('d-none');
    }

    async function loadSuiviTask(taskID) {
        try {
            const response = await fetch(`./apis/tache_suivi.routes.php?tache_id=${taskID}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            $('#rowSuiviTache').empty();

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    const row = `
                        <tr>
                            <td class="align-middle px-1">${item.observation || ''}</td>
                            <td class="align-middle text-center">${new Date(item.date_suivie).toLocaleDateString()}</td>
                            <td class="align-middle text-center"><span class="badge badge-phoenix badge-phoenix-${getBadgeClass(item.status)} rounded-pill py-1 px-2 fs-10">${tabTaskStatus[item.status] || ''}</span></td>
                            <td class="align-middle text-center d-flex justify-content-center align-items-center gap-2">
                                <button class="btn btn-icon btn-phoenix-secondary btn-sm fs-9" onclick="editSuiviTask(${item.id})">
                                    <span class="fas fa-edit"></span>
                                </button>
                                <button class="btn btn-icon btn-phoenix-danger btn-sm fs-9" onclick="deleteSuiviTask(${item.id})">
                                    <span class="fas fa-trash"></span>
                                </button>
                            </td>
                        </tr>`;
                    $('#rowSuiviTache').append(row);
                });
            } else {
                $('#rowSuiviTache').append('<tr><td colspan="7" class="text-center py-5">Aucun suivi trouvé.</td></tr>');
            }
        } catch (error) {
            $('#rowSuiviTache').append('<tr><td colspan="7" class="text-center py-5">Aucun suivi trouvé.</td></tr>');
            errorAction('Erreur lors du chargement des suivis.');
        }
    }

    async function editSuiviTask(id) {
        const form = document.getElementById("suiviTaskForm");
        try {
            const response = await fetch(`./apis/tache_suivi.routes.php?id=${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            if (result.data) {
                suiviTaskId = result.data.id;
                form.observation.value = result.data.observation;
                form.date_suivie.value = result.data.date_suivie;
                form.difficulte.value = result.data.difficulte;
                form.solution.value = result.data.solution;
                form.status.value = result.data.status;

                $('#suiviTaskFormContainer').removeClass('d-none');
                $('#suiviTaskTabContainer').addClass('d-none');
            }
        } catch (error) {
            errorAction('Erreur lors du chargement du suivi.');
        }
    }

    async function deleteSuiviTask(id) {
        deleteData(id, 'Êtes-vous sûr de vouloir supprimer ce suivi ?', 'tache_suivi', 'none')
            .then(() => {
                loadSuiviTask(formTaskId);
            })
            .catch(error => {
                errorAction('Erreur lors de la suppression');
            })
    }
</script>