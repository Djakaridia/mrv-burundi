<!-- modal -->
<div class="modal fade" id="IndicateurTaskModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body-highlight p-4">
            <!-- Header avec bouton fermer et titre -->
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0 fw-bold text-primary"> <span class="fas fa-tasks me-2"></span>Indicateur de l'activité </h3>
                <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal"
                    aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body px-2">
                <!-- Formulaire (caché par défaut) -->
                <div id="indicateurFormContainer" class="d-none">
                    <form id="indicateurForm" style="height: 350px; overflow-y: auto; overflow-x: hidden;">
                        <input type="hidden" id="indicateurId">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="indicateur_tache_code" class="form-label">Code*</label>
                                <input type="text" class="form-control" id="indicateur_tache_code" placeholder="Entrer le code" required>
                            </div>
                            <div class="col-md-6">
                                <label for="indicateur_tache_name" class="form-label">Libellé*</label>
                                <input type="text" class="form-control" id="indicateur_tache_name" placeholder="Entrer le libellé" required>
                            </div>

                            <div class="col-md-6">
                                <label for="indicateur_tache_unite" class="form-label">Unite*</label>
                                <select class="form-select" name="unit" id="indicateur_tache_unite" required>
                                    <option value="" selected disabled>Sélectionner une unité</option>
                                    <?php if ($unites ?? []) : ?>
                                        <?php foreach ($unites as $unite): ?>
                                            <option value="<?= $unite['name'] ?>"><?= $unite['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="indicateur_tache_cible" class="form-label">Valeur cible*</label>
                                <input type="text" class="form-control" id="indicateur_tache_cible" placeholder="Entrer la cible" required>
                            </div>

                            <div class="col-12">
                                <label for="indicateur_tache_description" class="form-label">Description</label>
                                <textarea class="form-control" id="indicateur_tache_description" placeholder="Entrer la description"></textarea>
                            </div>

                            <!-- Boutons -->
                            <div class="modal-footer d-flex justify-content-between border-0 p-2 pb-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelTIForm()">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="indicateur_tache_modbtn">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="indicateurTableContainer">
                    <!-- En-tête avec bouton Ajouter -->
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <h4 class="mb-0 text-dark">
                            <span class="fas fa-list me-2"></span>Liste des indicateurs
                        </h4>
                        <button class="btn btn-sm btn-primary" onclick="showTIAddForm()">
                            <span class="fas fa-plus me-2"></span>Nouveau indicateur
                        </button>
                    </div>

                    <div class="card border rounded-0 overflow-hidden">
                        <div id="indicTaskLoadingScreen" class="text-center py-5">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <h4 class="mt-3 fw-bold text-primary" id="indicTaskLoadingText">Chargement en cours</h4>
                        </div>

                        <div id="indicTaskTableContainer" class="card-body p-1 table-responsive scrollbar" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis"
                                align="center">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="align-middle px-2">Code</th>
                                        <th class="align-middle px-2">Libellé</th>
                                        <th class="align-middle px-2">Unité</th>
                                        <th class="align-middle px-2">Cible</th>
                                        <th class="align-middle text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="rowIndicateurTache" class="list">
                                    <!-- Les données seront insérées ici -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Message vide -->
                    <div id="emptyIndicateurState" class="text-center py-6 d-none">
                        <div class="fas fa-clipboard-list fs-5 text-300 mb-3"></div>
                        <h4 class="fw-bold text-400">Aucun indicateur enregistré</h4>
                        <p class="text-600">Commencez par ajouter un nouveau indicateur</p>
                        <button class="btn btn-sm btn-primary px-4" onclick="showTIAddForm()">
                            <span class="fas fa-plus me-2"></span>Ajouter un indicateur
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let indicateurTaskID = null;
    let currIndicateurId = null;

    $(document).ready(function() {
        $('#IndicateurTaskModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            indicateurTaskID = dataId;
            loadTaskIndicateur();
        });

        $('#IndicateurTaskModal').on('hidden.bs.modal', function() {
            indicateurTaskID = null;
            currIndicateurId = null;
            $('#indicateurFormContainer').addClass('d-none');
            $('#indicateurTableContainer').removeClass('d-none');
            $('#indicateurForm')[0].reset();
            $('#indicateurId').val('');

            setTimeout(() => {
                $('#indicTaskLoadingScreen').show();
                $('#indicTaskTableContainer').hide();
            }, 200);
        });

        // Gestion du formulaire
        $('#indicateurForm').submit(async function(e) {
            e.preventDefault();
            await saveTaskIndicateur();
        });
    });

    // Afficher le formulaire d'ajout
    function showTIAddForm() {
        currIndicateurId = null;
        $('#indicateurFormContainer').removeClass('d-none');
        $('#indicateurTableContainer').addClass('d-none');
        $('#indicateurForm')[0].reset();
        $('#indicateurId').val('');
    }

    // Annuler le formulaire
    function cancelTIForm() {
        $('#indicateurFormContainer').addClass('d-none');
        $('#indicateurTableContainer').removeClass('d-none');
    }

    async function loadTaskIndicateur() {
        $('#indicTaskLoadingScreen').show();
        $('#indicTaskTableContainer').hide();
        $('#rowIndicateurTache').empty();
        try {
            const response = await fetch(`./apis/tache_indicateur.routes.php?tache_id=${indicateurTaskID}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();

            if (result.data && result.data.length > 0) {
                result.data.forEach(item => {
                    const row = `
                        <tr>
                            <td class="align-middle px-1">${item.code || ''}</td>
                            <td class="align-middle px-1">${item.name || ''}</td>
                            <td class="align-middle px-1">${item.unite || ''}</td>
                            <td class="align-middle px-1">${item.valeur_cible || ''}</td>
                            <td class="align-middle text-center d-flex justify-content-center align-items-center gap-2">
                                <button class="btn btn-icon btn-phoenix-secondary btn-sm fs-9" onclick="editTaskIndicateur(${item.id})">
                                    <span class="fas fa-edit"></span>
                                </button>
                                <button class="btn btn-icon btn-phoenix-danger btn-sm fs-9" onclick="deleteTaskIndicateur(${item.id})">
                                    <span class="fas fa-trash"></span>
                                </button>
                            </td>
                        </tr>`;
                    $('#rowIndicateurTache').append(row);
                });
            } else {
                $('#rowIndicateurTache').append('<tr><td colspan="5" class="text-center p-10">Aucun indicateur trouvé.</td></tr>');
            }

            $('#indicTaskLoadingScreen').hide();
            $('#indicTaskTableContainer').show();
        } catch (error) {
            errorAction('Erreur lors du chargement des indicateurs.');
        }
    }

    async function editTaskIndicateur(id) {
        try {
            const response = await fetch(`./apis/tache_indicateur.routes.php?id=${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            const indicateur = result.data;

            if (indicateur) {
                currIndicateurId = indicateur.id;
                $('#indicateurId').val(indicateur.id);
                $('#indicateur_tache_code').val(indicateur.code);
                $('#indicateur_tache_name').val(indicateur.name);
                $('#indicateur_tache_description').val(indicateur.description);
                $('#indicateur_tache_unite').val(indicateur.unite);
                $('#indicateur_tache_cible').val(indicateur.valeur_cible);

                $('#indicateurFormContainer').removeClass('d-none');
                $('#indicateurTableContainer').addClass('d-none');
            }
        } catch (error) {
            errorAction('Erreur lors du chargement de l\'indicateur.');
        }
    }

    async function saveTaskIndicateur() {
        const formData = new FormData();
        formData.append('id', $('#indicateurId').val());
        formData.append('code', $('#indicateur_tache_code').val());
        formData.append('name', $('#indicateur_tache_name').val());
        formData.append('description', $('#indicateur_tache_description').val());
        formData.append('unite', $('#indicateur_tache_unite').val());
        formData.append('valeur_cible', $('#indicateur_tache_cible').val());
        formData.append('tache_id', indicateurTaskID);
        try {
            const url = `./apis/tache_indicateur.routes.php${currIndicateurId ? '?id=' + currIndicateurId : ''}`;
            const response = await fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                successAction(result.message || 'Indicateur enregistré avec succès', 'none');
                cancelTIForm();
            } else {
                errorAction(result.message || 'Erreur lors de l\'enregistrement');
            }
        } catch (error) {
            errorAction('Erreur lors de l\'enregistrement');
        } finally {
            loadTaskIndicateur();
        }
    }


    async function deleteTaskIndicateur(id) {
        deleteData(id, 'Êtes-vous sûr de vouloir supprimer ce indicateur ?', 'tache_indicateur', 'none')
            .then(() => {
                loadTaskIndicateur();
            })
            .catch(error => {
                errorAction('Erreur lors de la suppression');
            })
    }
</script>