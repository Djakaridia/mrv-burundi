<!-- modal -->
<div class="modal fade" id="SuiviTAskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body-highlight p-4">
            <!-- Header avec bouton fermer et titre -->
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0 fw-bold text-primary"> <span class="fas fa-tasks me-2"></span>Suivi des tâches </h3>
                <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal"
                    aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body px-2">
                <!-- Formulaire (caché par défaut) -->
                <div id="suiviTaskIndicFormContainer" class="d-none">
                    <form id="suiviTaskIndicForm" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                        <input type="hidden" id="suiviId">

                        <table class="table table-sm table-hover table-striped fs-12 table-bordered border-emphasis" align="center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="align-middle px-2" width="70%">Indicateur</th>
                                    <th class="align-middle px-2" width="30%">Valeur réalisée</th>
                                </tr>
                            </thead>

                            <tbody id="rowSuiviTacheForm">
                            </tbody>
                        </table>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelSuiviTaskForm()">Annuler</button>
                            <button type="submit" id="suiviTaskIndic_modbtn" class="btn btn-primary btn-sm my-0">Enregistrer</button>
                        </div>
                    </form>
                </div>

                <!-- Contenu principal (liste des suivis) -->
                <div id="suiviTacheContentContainer">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h4 class="mb-0 text-dark">
                            <span class="fas fa-history me-2"></span>Historique des suivis
                        </h4>
                        <button class="btn btn-sm btn-primary" onclick="showSuiviTaskAddForm()">
                            <span class="fas fa-plus me-2"></span>Nouveau suivi
                        </button>
                    </div>

                    <div class="card border rounded-0 overflow-hidden">
                        <div class="card-body p-1 table-responsive scrollbar" style="height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis"
                                align="center">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="align-middle px-2" width="70%">Indicateur</th>
                                        <th class="align-middle px-2" width="30%">Valeur réalisée</th>
                                    </tr>
                                </thead>
                                <tbody id="rowSuiviTacheTab" class="list">
                                    <!-- Les données seront insérées ici -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Message vide -->
                    <div id="emptyState" class="text-center py-6 d-none">
                        <div class="fas fa-clipboard-list fs-5 text-300 mb-3"></div>
                        <h4 class="fw-bold text-400">Aucun suivi enregistré</h4>
                        <p class="text-600">Commencez par ajouter un nouveau suivi</p>
                        <button class="btn btn-sm btn-primary px-4" onclick="showSuiviTaskAddForm()">
                            <span class="fas fa-plus me-2"></span>Ajouter un suivi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let suiviTaskID = null;
    let currentSuiviId = null;

    $(document).ready(function() {
        $('#SuiviTAskModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const tacheId = $(event.relatedTarget).data('tache_id');
            suiviTaskID = dataId;
            loadSuivisIndicateur();
        });

        $('#suiviTaskIndicForm').submit(async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const suivis = {};

            $('[name^="suivi_indic_task["]').each(function() {
                const matches = this.name.match(/suivi_indic_task\[(\d+)\]\[(\w+)\]/);
                if (matches) {
                    const indicateurId = matches[1];
                    const field = matches[2];

                    if (!suivis[indicateurId]) suivis[indicateurId] = {};
                    suivis[indicateurId][field] = $(this).val();
                    suivis[indicateurId].indicateur_id = indicateurId;
                }
            });

            formData.append('valeur_indicateurs', JSON.stringify(suivis));
            formData.append('tache_id', suiviTaskID);

            const submitBtn = $('#suiviTaskIndic_modbtn');
            submitBtn.prop('disabled', true).text('Envoi en cours...');
            try {
                const response = await fetch('./apis/tache_indicateur_suivi.routes.php', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données enregistrées avec succès', 'none');
                    cancelSuiviTaskForm();
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

    function showSuiviTaskAddForm() {
        $('#suiviTaskIndicFormContainer').removeClass('d-none');
        $('#suiviTacheContentContainer').addClass('d-none');
        $('#suiviTaskIndicForm')[0].reset();
        $('#suiviId').val('');
        editSuiviIndicateur();
    }

    function cancelSuiviTaskForm() {
        $('#suiviTaskIndicFormContainer').addClass('d-none');
        $('#suiviTacheContentContainer').removeClass('d-none');
        loadSuivisIndicateur();
    }

    async function loadSuivisIndicateur() {
        $('#rowSuiviTacheTab').empty();
        try {
            const response = await fetch(`./apis/tache_indicateur_suivi.routes.php?tache_id=${suiviTaskID}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    const row = `<tr>
                            <td class="align-middle text-start px-2">${item.name}</td>
                            <td class="align-middle text-start px-2">${item.valeur_suivi}</td>
                        </tr>`;
                    $('#rowSuiviTacheTab').append(row);
                });
            } else {
                $('#rowSuiviTacheTab').append('<tr><td colspan="2" class="text-center p-10">Aucun suivi trouvé.</td></tr>');
            }
        } catch (error) {
            errorAction('Erreur lors du chargement des suivis.');
        }
    }

    async function editSuiviIndicateur() {
        $('#rowSuiviTacheForm').empty();
        try {
            const response = await fetch(`./apis/tache_indicateur.routes.php?tache_id=${suiviTaskID}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            if (result.data) {
                result.data.forEach(item => {
                    const row = `<tr>
                            <td class="align-middle text-start px-2">${item.name}</td>
                            <td class="align-middle text-center px-2">
                                <input type="hidden" name="suivi_indic_task[${item.id}][name]" value="${item.name}">
                                <input type="text" class="form-control py-2" name="suivi_indic_task[${item.id}][valeur]" id="suivi_indic_task-${item.id}" value="">
                            </td>
                        </tr>`;
                    $('#rowSuiviTacheForm').append(row);
                });

                $('#suiviTaskIndicFormContainer').removeClass('d-none');
                $('#suiviTacheContentContainer').addClass('d-none');
            } else {
                $('#rowSuiviTacheForm').append('<tr><td colspan="2" class="text-center p-10">Aucun indicateur trouvé.</td></tr>');
            }
        } catch (error) {
            errorAction('Erreur lors du chargement du suivi.');
        }
    }
</script>