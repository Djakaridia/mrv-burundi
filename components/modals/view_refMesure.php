<!-- modal -->
<div class="modal fade" id="viewRefMesureModal" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body-highlight p-4">
            <!-- Header avec bouton fermer et titre -->
            <div class="modal-header justify-content-between border-0 p-0 mb-2">
                <h3 class="mb-0 fw-bold text-primary"> <span class="fas fa-tasks me-2"></span>Liste des actions liés au référentiel</h3>
                <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal"
                    aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>

            <!-- Corps du modal -->
            <div class="modal-body px-2">
                <div id="refMesureLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement des données...</h4>
                    <p class="text-muted">Veuillez patienter pendant que nous préparons les informations</p>
                </div>

                <div id="refMesureContainer" style="display: none;">
                    <div class="card border rounded-0 overflow-hidden">
                        <div class="card-body p-1 table-responsive scrollbar" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" align="center">
                                <thead class="bg-primary-subtle">
                                    <tr>
                                        <th class="sort align-middle" scope="col"> Code </th>
                                        <th class="sort align-middle" scope="col"> Intitule </th>
                                        <th class="sort align-middle" scope="col"> Unité </th>
                                        <th class="sort align-middle" scope="col"> Type </th>
                                        <th class="sort align-middle" scope="col"> Instrument </th>
                                        <th class="sort align-middle" scope="col"> Status </th>
                                    </tr>
                                </thead>
                                <tbody id="rowRefIndicateurMesure" class="list">
                                    <!-- Les données seront insérées ici -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Message vide -->
                    <div id="emptyIndicateurState" class="text-center py-6 d-none">
                        <div class="fas fa-clipboard-list fs-5 text-300 mb-3"></div>
                        <h4 class="fw-bold text-400">Aucune action lié au référentiel</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const tabTypeAction = <?= json_encode(listTypeAction()) ?>;
    const tabTypeInstrument = <?= json_encode(listTypeInstrument()) ?>;
    $(document).ready(function() {
        $('#viewRefMesureModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            $('#refMesureLoadingScreen').show();
            $('#refMesureContainer').hide();

            loadRefMesureIndicateur(dataId);
        });

        $('#viewRefMesureModal').on('hidden.bs.modal', function() {
            $('#refMesureLoadingScreen').show();
            $('#refMesureContainer').hide();
        });
    });

    async function loadRefMesureIndicateur(ref) {
        $('#rowRefIndicateurMesure').empty();

        try {
            const response = await fetch(`./apis/mesures.routes.php`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            const filteredMesures = result.data.filter(item => item.referentiel_id === ref);

            if (filteredMesures.length > 0) {
                filteredMesures.forEach(item => {
                    const row = `
                        <tr>
                            <td class="align-middle px-1">${item.code || ''}</td>
                            <td class="align-middle px-1"><a href="./mesure_view.php?id=${item.id}">${item.name || ''}</a></td>
                            <td class="align-middle px-1">${item.unite || ''}</td>
                            <td class="align-middle px-1">${tabTypeAction[item.action_type] || ''}</td>
                            <td class="align-middle px-1">${tabTypeInstrument[item.instrument] || ''}</td>
                            <td class="align-middle px-1">
                            <span class="badge rounded-pill badge-phoenix fs-10 badge-phoenix-${item.state == 'actif' ? 'success' : 'danger'}">
                            <span class="badge-label">${item.state == 'actif' ? 'Actif' : 'Inactif'}</span></span>
                            </td>
                        </tr>`;
                    $('#rowRefIndicateurMesure').append(row);
                });
            } else {
                $('#rowRefIndicateurMesure').append('<tr><td colspan="7" class="text-center">Aucune action trouvé.</td></tr>');
            }
        } catch (error) {
            errorAction('Erreur lors du chargement des actions.');
        } finally {
            $('#refMesureLoadingScreen').hide();
            $('#refMesureContainer').show();
        }
    }
</script>