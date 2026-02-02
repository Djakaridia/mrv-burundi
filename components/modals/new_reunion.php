<div class="modal fade" id="addEventModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addEventModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="reunion_modtitle">Ajouter une réunion</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="reunionLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="reunionLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="reunionContentContainer" style="display: none;">
                    <form action="" name="addEventForm" id="addEventForm" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventCode" class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'eventCode', 'eventCodeFeedback', 'reunions')" class="form-control" type="text" name="code" id="eventCode"
                                        placeholder="Entrer le code" required />
                                    <div id="eventCodeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventTitle" class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name" id="eventTitle"
                                        placeholder="Entrer le libellé" required />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventStartDate" class="form-label">Date*</label>
                                    <input class="form-control datetimepicker" id="eventStartDate" type="text"
                                        name="horaire" placeholder="YYYY-MM-DD HH:MM"
                                        data-options='{"enableTime":true,"disableMobile":true,"dateFormat":"Y-m-d H:i"}'
                                        required />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventLieu" class="form-label">Lieu*</label>
                                    <input class="form-control" type="text" name="lieu" id="eventLieu"
                                        placeholder="Entrer le lieu" required />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventGroupe" class="form-label">Groupe de
                                        travail*</label>
                                    <select class="form-select" name="groupe_id" id="eventGroupe" required>
                                        <option value="">Sélectionner un groupe</option>
                                        <?php if ($groupes ?? []) : ?>
                                            <?php foreach ($groupes as $groupe) { ?>
                                                <option value="<?php echo $groupe['id']; ?>"><?php echo $groupe['name']; ?>
                                                </option>
                                            <?php } ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label for="eventColor" class="form-label">Couleur</label>
                                    <input class="form-control" style="height: 36px;" type="color" name="couleur" id="couleur-reunion"
                                        placeholder="Entrer la couleur" />

                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label for="eventDescription"
                                        class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="eventDescription"
                                        placeholder="Entrer une description"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <input type="hidden" name="status" value="planifiée">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="reunion_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


<script>
    let formReunionID = null;
    $(document).ready(function() {
        $('#addEventModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const groupe_id = $(event.relatedTarget).data('groupe_id');
            const form = document.getElementById('addEventForm');

            if (groupe_id) {
                form.groupe_id.value = groupe_id;
                $('#eventGroupe').attr('readonly', true);
                $('#eventGroupe').addClass('bg-light');
                $('#eventGroupe').css('pointerEvents', 'none');
            }

            // Show loading screen and hide content
            $('#reunionLoadingScreen').show();
            $('#reunionContentContainer').hide();
            if (dataId) {
                formReunionID = dataId;
                $('#reunion_modtitle').text('Modifier la réunion');
                $('#reunion_modbtn').text('Modifier');
                $('#reunionLoadingText').text("Chargement des données réunion...");
                try {
                    const response = await fetch(`./apis/reunions.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.code.value = result.data.code;
                    form.horaire.value = result.data.horaire;
                    form.groupe_id.value = result.data.groupe_id;
                    form.lieu.value = result.data.lieu;
                    form.couleur.value = result.data.couleur;
                    form.description.value = result.data.description;
                    form.status.value = result.data.status;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    // Hide loading screen and show content
                    $('#reunionLoadingScreen').hide();
                    $('#reunionContentContainer').show();
                }
            } else {
                formReunionID = null;
                $('#reunion_modtitle').text('Ajouter une réunion');
                $('#reunion_modbtn').text('Ajouter');
                $('#reunionLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#reunionLoadingScreen').hide();
                    $('#reunionContentContainer').show();
                }, 200);
            }
        });

        $('#addEventModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#reunionLoadingScreen').show();
                $('#reunionContentContainer').hide();
            }, 200);
            $('#addEventForm')[0].reset();
        });

        $('#addEventForm').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formReunionID ? `./apis/reunions.routes.php?id=${formReunionID}` : './apis/reunions.routes.php';
            const submitBtn = $('#reunion_modbtn');
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
                    $('#addEventModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                console.error(error);
                errorAction('Erreur lors de l\'envoi des données.');
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Ajouter');
            }
        });
    });
</script>


<script>
    $(document).ready(function() {
        $('#eventDetailsModal').on('shown.bs.modal', async function(event) {
            let reunionId = null;

            if (event.relatedTarget) {
                reunionId = $(event.relatedTarget).data('id');
            }

            if (!reunionId) {
                reunionId = $(this).data('id');
            }

            if (!reunionId) {
                console.error('Impossible de déterminer l’ID de la réunion.');
                return;
            }

            const $modalContent = $('#reunionDetailContent');
            $modalContent.html('<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>');

            try {
                const response = await fetch(`./apis/reunions.routes.php?id=${reunionId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET'
                });

                const result = await response.json();
                if (result.status === 'success') {
                    const data = result.data;
                    const html = `
                    <div class="reunion-details">
                        <div class="reunion-header d-flex justify-content-between mb-3 bg-body border rounded p-3">
                            <div class="d-flex flex-column align-items-start gap-2">
                                <h5 class="reunion-code mb-1 text-primary">${data.code}</h5>
                                <h4 class="reunion-title mb-0">${data.name}</h4>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="badge bg-${getBadgeClass(data.status)} text-capitalize py-2 px-3">
                                    ${data.status}
                                </span>
                                <span class="text-muted mb-0">
                                    <i class="far fa-clock me-2 text-primary"></i> ${formatDate(data.horaire, 'DD/MM/YYYY [à] HH:mm')}
                                </span>
                            </div>
                        </div>

                        <div class="row gy-4 mb-3">
                            <div class="col-md-6">
                                <div class="info-card bg-body border p-3 rounded h-100">
                                    <h6 class="info-title text-uppercase text-muted mb-2">Groupe de travail</h6>
                                    <p class="info-value mb-0">
                                        <i class="fas fa-users me-2 text-primary"></i> ${data.groupe_nom}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-card bg-body border p-3 rounded h-100">
                                    <h6 class="info-title text-uppercase text-muted mb-2">Lieu</h6>
                                    <p class="info-value mb-0">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i> ${data.lieu}
                                    </p>
                                </div>
                            </div>
                            
                            ${data.description ? `
                            <div class="col-12">
                                <div class="info-card bg-body border p-3 rounded">
                                    <h6 class="info-title text-uppercase text-muted mb-2">Description</h6>
                                    <div class="info-value reunion-description">
                                        ${data.description.replace(/\n/g, '<br>')}
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>

                        <div class="modal-footer d-flex justify-content-between align-items-center border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i> Fermer
                            </button>
                            <div class="d-flex gap-3">
                                <button onclick="deleteData(${reunionId}, 'Êtes-vous sûr de vouloir supprimer cette réunion ?', 'reunions')" 
                                        type="button" class="btn btn-danger px-3">
                                    <i class="far fa-trash-alt me-2"></i> Supprimer
                                </button>
                                <button type="button" class="btn btn-primary px-3" 
                                        data-id="${reunionId}" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#addEventModal">
                                    <i class="far fa-edit me-2"></i> Modifier
                                </button>
                            </div>
                        </div>
                    </div>`;
                    $modalContent.html(html);
                } else {
                    $modalContent.html(`<div class="alert alert-warning">${result.message}</div>`);
                }
            } catch (error) {
                $modalContent.html('<div class="alert alert-danger">Erreur lors du chargement des données.</div>');
            }
        });

        $('#eventDetailsModal').on('hidden.bs.modal', function() {
            const $modalContent = $('#reunionDetailContent');
            $modalContent.html('');
        });
    });

    function formatDate(input) {
        const date = new Date(input);
        return date.toLocaleString('fr-FR');
    }

    function getBadgeClass(status) {
        switch ((status || '').toLowerCase()) {
            case 'planifiée':
                return 'info';
            case 'en cours':
                return 'warning';
            case 'terminée':
                return 'success';
            case 'annulée':
                return 'danger';
            default:
                return 'secondary';
        }
    }
</script>