<div class="modal fade" id="programmeCardViewModal" tabindex="-1" aria-labelledby="programmeCardViewModal"
    aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content overflow-hidden">
            <div class="modal-header bg-body d-flex justify-content-between align-items-center border-bottom border-light p-2">
                <h4 class="modal-title fw-bolder"> Information du programme</h4>
                <button class="btn btn-circle border border-light project-modal-btn bg-body-emphasis"
                    data-bs-dismiss="modal">
                    <span class="fa-solid fa-xmark text-body dark__text-gray-100"></span>
                </button>
            </div>

            <div class="modal-body p-5 px-md-6">
                <!-- Loading Screen -->
                <div id="programmeLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement des données...</h4>
                    <p class="text-muted">Veuillez patienter pendant que nous préparons les informations</p>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="programmeContentContainer" style="display: none;">
                    <div class="row g-4">
                        <!-- Header Section -->
                        <div class="col-12">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                                <div>
                                    <h2 class="fw-bolder mb-1" id="programmeNameView"></h2>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-primary rounded-pill" id="programmeCodeView"></span>
                                        <span class="badge rounded-pill" id="programmeStatusView"></span>
                                        <span class="badge bg-info text-dark rounded-pill" id="programmeSigleView"></span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                        <span id="programmeDatesView" class="fw-semibold"></span>
                                    </div>
                                    <small class="text-muted">Période d'exécution</small>
                                </div>
                            </div>

                            <!-- Description Card -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-align-left text-primary me-2"></i>Description
                                    </h5>
                                    <p id="programmeDescriptionView" class="mb-0"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Section -->
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-chart-pie text-primary me-2"></i>Statistiques
                                    </h5>

                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="mb-0" id="projetsCount">0</h3>
                                                <small class="text-muted">Projets</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="p-3 bg-light rounded">
                                                <h3 class="mb-0" id="daysLeft">0</h3>
                                                <small class="text-muted">Jours restants</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Avancement global</small>
                                            <small id="progressPercentage">0%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div id="programmeBudgetProgressView"
                                                class="progress-bar bg-success progress-bar-striped" role="progressbar">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Projects Section -->
                        <div class="col-12 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-project-diagram text-primary me-2"></i>Projets associés
                                        </h5>
                                        <span class="badge bg-primary" id="projetsCountBadge">0</span>
                                    </div>

                                    <div id="projetsList" class="list-group list-group-flush" style="max-height: 255px; overflow-y: auto;">
                                        <!-- Projets will be inserted here -->
                                    </div>

                                    <div id="noProjectsMessage" class="text-center py-4 d-none">
                                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Aucun projet associé à ce programme</p>
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
    let ViewProgrammeID = null;

    $(document).ready(function() {
        $('#programmeCardViewModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            if (!dataId) return;

            ViewProgrammeID = dataId;

            // Show loading screen and hide content
            $('#programmeLoadingScreen').show();
            $('#programmeContentContainer').hide();

            try {
                const response = await fetch(`./apis/programmes.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });
                const result = await response.json();
                const data = result.data;

                // Format dates
                const programme_end_date = new Date(data.end_date).toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
                const programme_start_date = new Date(data.start_date).toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });

                // Calculate days left
                const today = new Date();
                const endDate = new Date(data.end_date);
                const daysLeft = Math.ceil((endDate - today) / (1000 * 60 * 60 * 24));

                // Set status color
                const status = data.status;
                let statusColor = 'bg-secondary';
                if (status === 'En cours') statusColor = 'bg-warning text-dark';
                if (status === 'Terminé') statusColor = 'bg-success';

                // Set basic info
                $('#programmeNameView').text(data.name);
                $('#programmeCodeView').text(data.code);
                $('#programmeSigleView').text(data.sigle);
                $('#programmeStatusView').text(data.status).addClass(statusColor);
                $('#programmeDescriptionView').text(data.description || 'Aucune description disponible');
                $('#programmeDatesView').text(`${data.start_date} → ${data.end_date}`);
                $('#daysLeft').text(daysLeft);

                // Set progress (assuming budget represents progress)
                const progressPercentage = Math.min(data.budget || 0, 100);
                $('#programmeBudgetProgressView').css('width', `${progressPercentage}%`);
                $('#progressPercentage').text(`${progressPercentage}%`);

                // Load projects
                const projetsResponse = await fetch('./apis/projets.routes.php', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });
                const projetsResult = await projetsResponse.json();
                const projetsList = $('#projetsList');
                const noProjectsMessage = $('#noProjectsMessage');
                projetsList.empty();

                const projetsFiltres = projetsResult.data.filter(projet => projet.programme_id == dataId);
                const projetsCount = projetsFiltres.length;

                $('#projetsCount').text(projetsCount);
                $('#projetsCountBadge').text(projetsCount);

                if (projetsCount > 0) {
                    noProjectsMessage.addClass('d-none');
                    projetsFiltres.forEach(projet => {
                        const startDate = new Date(projet.start_date).toLocaleDateString('fr-FR');
                        const endDate = new Date(projet.end_date).toLocaleDateString('fr-FR');

                        // Set project status color
                        let projectStatusColor = 'badge-secondary';
                        if (projet.status === 'En cours') projectStatusColor = 'badge-warning text-dark';
                        if (projet.status === 'Terminé') projectStatusColor = 'badge-success';

                        projetsList.append(`
                            <div class="list-group-item border-0 px-0 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg bg-light rounded-circle me-3">
                                        <i class="fas fa-project-diagram text-primary fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${projet.name}</h6>
                                        <p class="mb-1 small text-muted">${projet.description || 'Aucune description'}</p>
                                        <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                                            <span class="badge ${projectStatusColor}">${projet.status}</span>
                                            <span class="badge bg-light text-dark">${projet.code}</span>
                                            <small class="text-muted"><i class="far fa-calendar me-1"></i>${startDate} → ${endDate}</small>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted"></i>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    noProjectsMessage.removeClass('d-none');
                }

                // Hide loading screen and show content when all data is loaded
                $('#programmeLoadingScreen').hide();
                $('#programmeContentContainer').show();

            } catch (error) {
                console.error('Error loading programme data:', error);
                errorAction('Erreur lors du chargement des données du programme.');

                // Hide loading screen even if there's an error
                $('#programmeLoadingScreen').hide();
                $('#programmeContentContainer').show();
            }
        });

        $('#programmeCardViewModal').on('hidden.bs.modal', function() {
            ViewProgrammeID = null;

            // Reset loading state for next open
            $('#programmeLoadingScreen').show();
            $('#programmeContentContainer').hide();
        });
    });
</script>