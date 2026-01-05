<div class="modal fade" id="viewNiveauModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewNiveauModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0">Gérer les niveaux</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="niveauViewLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement des données...</h4>
                    <p class="text-muted">Veuillez patienter pendant que nous préparons les informations</p>
                </div>
                
                <!-- Content Container (initially hidden) -->
                <div id="niveauViewContentContainer" style="display: none;">
                    <div class="d-flex justify-content-between pb-3 border-bottom mb-3">
                        <button type="button" id="btnAddNewNiveau" class="btn btn-sm btn-subtle-primary" data-bs-toggle="modal" data-bs-target="#addNiveauModal">
                            <span class="fas fa-plus"></span> Ajouter un niveau
                        </button>
                    </div>

                    <div id="levelContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#viewNiveauModal').on('shown.bs.modal', async function(event) {
            // Show loading screen and hide content
            $('#niveauViewLoadingScreen').show();
            $('#niveauViewContentContainer').hide();
            
            try {
                const response = await fetch(`./apis/niveaux.routes.php`, {
                    method: 'GET',
                    headers: { 'Authorization': `Bearer ${token}` }
                });

                const result = await response.json();
                if (result.status === 'success') {
                    const lastLevel = result.data[0].niveau;
                    $('#btnAddNewNiveau').data('lastLevel', parseInt(lastLevel) + 1);

                    const dataNiveaux = result.data.reverse();
                    const levelContainer = document.getElementById('levelContainer');
                    levelContainer.innerHTML = '';

                    dataNiveaux.forEach(level => {
                        const row = document.createElement('div');
                        row.classList.add('row', 'g-3', 'd-flex', 'justify-content-between', 'mt-3', 'mb-3');
                        row.innerHTML = `
                            <div class="col-6 mt-2">
                                <div class="mb-1 d-flex align-items-center gap-2">
                                    <label class="text-body-highlight fw-semibold text-nowrap">Niveau ${level.niveau}</label>
                                    <input class="form-control form-control-sm rounded-1 text-capitalize" readonly type="text" value="${level.name}" />
                                </div>
                            </div>
                            <div class="col-3 mt-2">
                                <div class="mb-1 d-flex align-items-center gap-2">
                                    <label class="text-body-highlight fw-semibold">Type</label>
                                    <input class="form-control form-control-sm rounded-1 text-capitalize" readonly type="text" value="${level.type}" />
                                </div>
                            </div>
                            <div class="col-2 d-flex gap-1 mt-0 justify-content-end">
                                <?php if (checkPermis($db, 'update')) : ?>
                                <button type="button" class="btn btn-sm btn-icon btn-phoenix-info" data-bs-toggle="modal" data-bs-target="#addNiveauModal" data-id="${level.id}"><span class="fas fa-pencil"></span></button>
                                <?php endif; ?>
                                <?php if (checkPermis($db, 'delete')) : ?>
                                <button type="button" class="btn btn-sm btn-icon btn-phoenix-danger" onclick="deleteData(${level.id}, 'Êtes-vous sûr de vouloir supprimer ce niveau ?', 'niveaux')"><span class="fas fa-trash"></span></button>
                                <?php endif; ?>
                            </div>
                        `;
                        levelContainer.appendChild(row);
                    });
                }
            } catch (error) {
                errorAction('Impossible de charger les données.');
            } finally {
                $('#niveauViewLoadingScreen').hide();
                $('#niveauViewContentContainer').show();
            }
        });

        $('#viewNiveauModal').on('hidden.bs.modal', function() {
            $('#niveauViewLoadingScreen').show();
            $('#niveauViewContentContainer').hide();
        });
    });
</script>