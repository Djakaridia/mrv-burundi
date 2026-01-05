<div class="modal fade" id="groupeCardViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content overflow-hidden">
            <div class="modal-header bg-body d-flex justify-content-between align-items-center border-bottom border-light p-2">
                <h4 class="modal-title fw-bolder"> Information du groupe de travail</h4>
                <button class="btn btn-circle border border-light project-modal-btn bg-body-emphasis"
                    data-bs-dismiss="modal">
                    <span class="fa-solid fa-xmark text-body dark__text-gray-100"></span>
                </button>
            </div>

            <div class="modal-body p-4 p-md-5">
                <!-- Loading Screen -->
                <div id="groupeViewLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement des données...</h4>
                    <p class="text-muted">Veuillez patienter pendant que nous préparons les informations</p>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="groupeViewContentContainer" style="display: none;">
                    <!-- Main Info Section -->
                    <div class="row g-4">
                        <div class="col-12 col-md-8">
                            <!-- Group Header -->
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <h2 class="fw-bolder mb-1" id="groupeNameView"></h2>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-primary rounded-pill" id="groupeCodeView"></span>
                                        <span class="badge bg-secondary rounded-pill">Groupe de travail</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-light rounded-1 p-2">
                                        <i class="fas fa-users fs-5 text-primary"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Card -->
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-align-left text-primary me-2"></i>Description
                                    </h5>
                                    <p id="groupeDescriptionView" class="mb-0"></p>
                                </div>
                            </div>

                            <!-- Meetings Section -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>Réunions
                                        </h5>
                                        <span class="badge bg-primary" id="reunionsCountBadge">0</span>
                                    </div>

                                    <div id="reunionsList" class="list-group list-group-flush">
                                        <!-- Réunions will be inserted here -->
                                    </div>

                                    <div id="noReunionsMessage" class="text-center py-4">
                                        <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Aucune réunion planifiée</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar with Projects and Actions -->
                        <div class="col-12 col-md-4">
                            <!-- Linked Projects -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-briefcase text-primary me-2"></i>Projets liés
                                        </h5>
                                        <span class="badge bg-primary" id="projetsCountBadge">0</span>
                                    </div>

                                    <div id="projetsList" class="list-group list-group-flush">
                                        <!-- Projets will be inserted here -->
                                    </div>

                                    <div id="noProjetsMessage" class="text-center py-4">
                                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Aucun projet associé</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Monitor Info -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-shield text-primary me-2"></i>Responsable
                                    </h5>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="fas fa-user text-primary"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0"><small class="text-muted">Sigle:</small><span class="ms-1 fw-semibold fs-9" id="groupeMonitorView">Non assigné</span></p>
                                            <small class="text-muted">Animateur du groupe</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let ViewGroupID = null;

    $(document).ready(function() {
        $('#groupeCardViewModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            if (!dataId) return;
            ViewGroupID = dataId;

            $('#groupeViewLoadingScreen').show();
            $('#groupeViewContentContainer').hide();
            try {
                const response = await fetch(`./apis/groupes.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });
                const resultGroupe = await response.json();

                if (!resultGroupe.status || resultGroupe.status !== 'success') {
                    throw new Error('Données de groupe invalides');
                }

                $('#groupeNameView').text(resultGroupe.data.name || 'Groupe sans nom');
                $('#groupeCodeView').text(resultGroupe.data.code || '');
                $('#groupeDescriptionView').text(resultGroupe.data.description || 'Aucune description disponible');

                const responseStructure = await fetch(`./apis/structures.routes.php?id=${resultGroupe.data.monitor}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });
                const resultStructure = await responseStructure.json();

                if (!resultStructure.status || resultStructure.status !== 'success') {
                    throw new Error('Données de structure invalides');
                }

                $('#groupeMonitorView').text(resultStructure.data.sigle || 'Responsable non spécifié');

                try {
                    const projetsResponse = await fetch('./apis/projets.routes.php', {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });
                    const projetsResult = await projetsResponse.json();
                    const projetsList = $('#projetsList');
                    const noProjetsMessage = $('#noProjetsMessage');
                    projetsList.empty();
                    noProjetsMessage.addClass('d-none');

                    if (projetsResult.status === 'success' && projetsResult.data && projetsResult.data.length > 0) {
                        const projetsFiltres = projetsResult.data.filter(projet => projet.groupes.includes(dataId));
                        const projetsCount = projetsFiltres.length;

                        $('#projetsCountBadge').text(projetsCount);

                        if (projetsCount > 0) {
                            projetsFiltres.forEach(projet => {
                                projetsList.append(`
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                                <i class="fas fa-briefcase text-primary fs-9"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fs-9">${projet.name || 'Projet sans nom'}</h6>
                                                <small class="text-muted">${projet.code || ''}</small>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            noProjetsMessage.removeClass('d-none');
                        }
                    } else {
                        noProjetsMessage.removeClass('d-none');
                    }
                } catch (error) {
                    console.error('Error loading projects:', error);
                    $('#noProjetsMessage').removeClass('d-none').find('p').text('Erreur de chargement des projets');
                }

                try {
                    const reunionsResponse = await fetch('./apis/reunions.routes.php', {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });
                    const reunionsResult = await reunionsResponse.json();
                    const reunionsList = $('#reunionsList');
                    const noReunionsMessage = $('#noReunionsMessage');
                    reunionsList.empty();

                    // Hide no meetings message by default
                    noReunionsMessage.addClass('d-none');

                    if (reunionsResult.status === 'success' && reunionsResult.data && reunionsResult.data.length > 0) {
                        // Filter meetings for this group
                        const reunionsFiltres = reunionsResult.data.filter(reunion => reunion.groupe_id == dataId);
                        const reunionsCount = reunionsFiltres.length;

                        $('#reunionsCountBadge').text(reunionsCount);

                        if (reunionsCount > 0) {
                            reunionsFiltres.forEach(reunion => {
                                const dateReunion = reunion.horaire ?
                                    new Date(reunion.horaire).toLocaleDateString('fr-FR', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) :
                                    'Date non spécifiée';

                                // Determine status color
                                let statusColor = 'bg-warning';
                                if (reunion.status === 'terminée') statusColor = 'bg-success';
                                if (reunion.status === 'annulée') statusColor = 'bg-danger';

                                reunionsList.append(`
                                    <div class="list-group-item border-0 px-0 py-2">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-light rounded-circle me-2">
                                                <i class="fas fa-calendar-check text-primary fs-9"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fs-9">${reunion.name || 'Réunion sans titre'}</h6>
                                                <small class="text-muted"><i class="far fa-clock me-1"></i>${dateReunion}</small>
                                                ${reunion.lieu ? `<small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i>${reunion.lieu}</small>` : ''}
                                            </div>
                                            <span class="badge ${statusColor} fs-10 text-white text-capitalize">${reunion.status || 'planifiée'}</span>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            noReunionsMessage.removeClass('d-none');
                        }
                    } else {
                        noReunionsMessage.removeClass('d-none');
                    }
                } catch (error) {
                    console.error('Error loading meetings:', error);
                    $('#noReunionsMessage').removeClass('d-none').find('p').text('Erreur de chargement des réunions');
                }
            } catch (error) {
                errorAction('Erreur lors du chargement des données du groupe.');
            } finally {
                $('#groupeViewLoadingScreen').hide();
                $('#groupeViewContentContainer').show();
            }
        });

        $('#groupeCardViewModal').on('hidden.bs.modal', function() {
            ViewGroupID = null;
            $('#groupeViewLoadingScreen').show();
            $('#groupeViewContentContainer').hide();
        });
    });
</script>