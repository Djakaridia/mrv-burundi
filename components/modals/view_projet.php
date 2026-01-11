<div class="modal fade" id="projectsCardViewModal" tabindex="-1" aria-labelledby="projectsCardViewModal"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden">
            <div
                class="modal-header bg-body d-flex justify-content-between align-items-center border-bottom border-light p-2">
                <h4 class="modal-title fw-bolder"> Information du projet</h4>
                <button class="btn btn-circle border border-light project-modal-btn bg-body-emphasis"
                    data-bs-dismiss="modal">
                    <span class="fa-solid fa-xmark text-body dark__text-gray-100"></span>
                </button>
            </div>
        
            <div class="modal-body p-4">
                <!-- Loading Screen -->
                <div id="projectLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement des données...</h4>
                    <p class="text-muted">Veuillez patienter pendant que nous préparons les informations</p>
                </div>
                
                <!-- Content Container (initially hidden) -->
                <div id="projectContentContainer" style="display: none;">
                    <div class="row g-3">
                        <!-- Header Section -->
                        <div class="col-12 col-md-3">
                            <img id="projectCoverView" class="rounded-1 w-100 border border-light shadow-sm d-none" src="" alt="" style="max-height: 150px;min-height: 100px;" />
                            <i id="projectCoverIcon" class="far fa-image text-body-tertiary" style="width: 100%; font-size:150px"></i>
                        </div>
                        
                        <div class="col-12 col-md-9">
                            <div class="align-items-start align-items-md-center">
                                <h3 class="fw-bolder mb-2" id="projetNameView"></h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary rounded-pill" id="projetCodeView"></span>
                                    <span class="badge bg-secondary rounded-pill" id="projetStatusView"></span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Progression</small>
                                    <small id="budgetProgressText">0%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="projetBudgetProgressView" class="progress-bar bg-success progress-bar-striped"
                                        role="progressbar"></div>
                                </div>
                            </div>

                            <div class="mt-2 d-flex justify-content-between align-items-start">
                                <div class="mb-2">
                                    <small class="mb-2">Période</small>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fa-regular fa-calendar text-primary"></span>
                                        <small id="projetDatesView" class="fw-semibold"></small>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <small class="mb-2">Budget alloué (USD)</small>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fa-regular fa-credit-card rounded-1 text-primary"></span>
                                        <h4 class="text-red mb-0" id="projetBudgetTextView"></h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card rounded-1 border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-link text-primary me-2"></i>Informations liées
                                    </h5>
                                    <div id="projetInfosSupplementaires" class="row g-3"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div class="card rounded-1 border-0 shadow-sm h-100">
                                <div class="card-body p-3">
                                    <h5 class="card-title border-bottom pb-2 mb-3">
                                        <i class="fas fa-info-circle text-primary me-2"></i>Informations du projet
                                    </h5>

                                    <div class="mb-4">
                                        <h6 class="text-muted mb-1">Objectif</h6>
                                        <p id="projetObjectifView" class="fw-semibold bg-body p-3 rounded-1"
                                            style="max-height: 255px; overflow-y: auto;"></p>
                                    </div>

                                    <div class="mb-4">
                                        <h6 class="text-muted mb-1">Description</h6>
                                        <p id="projetDescriptionView" class="fw-light bg-body p-3 rounded-1"
                                            style="max-height: 255px; overflow-y: auto;"></p>
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
    let ViewProjectID = null;
    let listActions = <?php echo json_encode(listTypeAction()); ?>;

    $(document).ready(function() {
        $('#projectsCardViewModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            if (!dataId) return;

            ViewProjectID = dataId;
            
            $('#projectLoadingScreen').show();
            $('#projectContentContainer').hide();

            try {
                const response = await fetch(`./apis/projets.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });
                const result = await response.json();
                const data = result.data;

                // Set basic info
                if(data.logo){
                    $('#projectCoverView').attr('src', data.logo.split("../").pop());
                    $('#projectCoverIcon').addClass('d-none');
                    $('#projectCoverView').removeClass('d-none');
                }
                $('#projetNameView').text(data.name);
                $('#projetCodeView').text(data.code);
                $('#projetStatusView').text(data.status);
                $('#projetBudgetTextView').text(`${data.budget}`);
                $('#projetDescriptionView').html(data.description);
                $('#projetObjectifView').html(data.objectif);
                $('#projetDatesView').text(`${data.start_date} → ${data.end_date}`);

                // Fetch related data
                const [projetPrioriteRes, projetTasksRes] = await Promise.all([
                    fetch(`./apis/priorites.routes.php?id=${data.priorites_id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    }),
                    fetch(`./apis/taches.routes.php?projet_id=${data.id}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    }),
                ]);

                const projetPriorite = await projetPrioriteRes.json();
                const projetTasks = await projetTasksRes.json();

                // Calculate progress percentage
                let progressPercentage = 0;
                if (Array.isArray(projetTasks.data) && projetTasks.data.length > 0) {
                    const finichedTasks = projetTasks.data.filter((task) => task.status.toLowerCase() == "terminée");
                    const countTotalTask = projetTasks.data.length;
                    const countFinichedTasks = finichedTasks.length;
                    progressPercentage = Math.round((countFinichedTasks / countTotalTask) * 100);
                }
                $('#projetBudgetProgressView').css('width', `${progressPercentage}%`);
                $('#budgetProgressText').text(`${progressPercentage}%`);


                // Display related data
                const ProjetInfosSupplementaires = $('#projetInfosSupplementaires');
                ProjetInfosSupplementaires.empty().append(`
                    <div class="col-12">
                        <div class="card rounded-1 border border-primary border-opacity-10 hover-shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="avatar avatar-lg bg-primary p-2 bg-opacity-10 rounded-1 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-users text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-primary">Responsable</h6>
                                        <p class="mb-0 fw-semibold">${data.structure_sigle}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="col-12">
                        <div class="card rounded-1 border border-primary border-opacity-10 hover-shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="avatar avatar-lg bg-primary p-2 bg-opacity-10 rounded-1 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-tasks text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-primary">Type action</h6>
                                        <p class="mb-0 fw-semibold">${listActions[data.action_type]}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Priorité -->
                    <div class="col-12">
                        <div class="card rounded-1 border border-warning border-opacity-10 hover-shadow-sm h-100">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-2">
                                    <div class="avatar avatar-lg bg-primary p-2 bg-opacity-10 rounded-1 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-exclamation-triangle text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 text-primary">Priorité</h6>
                                        <p class="mb-0 fw-semibold">${projetPriorite.data.name}</p>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge" style="background-color: ${projetPriorite.data.couleur}; width: 12px; height: 12px; border-radius: 50%;"></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-2 mb-0 small text-muted">${projetPriorite.data.description}</p>
                            </div>
                        </div>
                    </div>
                `);
            } catch (error) {
                errorAction('Erreur lors du chargement des données du projet.');
            } finally {
                $('#projectLoadingScreen').hide();
                $('#projectContentContainer').show();
            }
        });

        $('#projectsCardViewModal').on('hidden.bs.modal', function () {
            ViewProjectID = null;
            $('#projectCoverView').addClass('d-none');
            $('#projectCoverIcon').removeClass('d-none');
            
            $('#projectLoadingScreen').show();
            $('#projectContentContainer').hide();
        });
    });
</script>