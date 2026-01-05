<!-- modal -->
<div class="modal fade" id="addConventionRioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addConventionRioModal" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="conventionRIOmodtitle">Ajouter une convention RIO</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="conventionRIOLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="conventionRIOLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="conventionRIOContentContainer" style="display: none;">
                    <div id="conventionRIOTableContainer" class="card rounded-1 bg-white dark__bg-dark">
                        <div class="card-header d-flex justify-content-between align-items-center p-2">
                            <h5 class="mb-0">Liste des convention RIO</h5>
                            <button class="btn btn-sm btn-primary" onclick="showConventionRioFrom()">
                                <span class="fas fa-plus me-2"></span>Ajouter
                            </button>
                        </div>
                        <div class="card-body p-0" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis mb-0">
                                <thead class="bg-secondary-subtle">
                                    <tr>
                                        <th class="align-middle px-2">Code</th>
                                        <th class="align-middle px-2">Programme</th>
                                        <th class="align-middle px-2">Niveau de résultat</th>
                                        <th class="align-middle px-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="conventionRIOTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <form id="FormConventionRio" class="d-none" action="" name="FormConventionRio" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <input type="hidden" name="referentiel_id">

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input oninput="checkColumns('code', 'conventionRIOcode', 'code_convenRIO_feedback', 'convention_rio')" type="text" class="form-control" name="code" id="conventionRIOcode" required>
                                    <div class="invalid-feedback" id="code_convenRIO_feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Programme*</label>
                                    <select class="form-select" name="programme" id="conventionRIOprogramme" required>
                                        <option value="" selected disabled>Selectionner un programme</option>
                                        <?php if ($programmes ?? []) : ?>
                                            <?php foreach ($programmes as $programme) : ?>
                                                <option value="<?= $programme['id'] ?>"><?= $programme['sigle'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Niveau de résultat</label>
                                    <select class="form-select" name="niveau" id="conventionRIOniveau_resultat" required>
                                        <option value="" selected disabled>Selectionner un niveau de résultat</option>
                                        <?php if ($niveaux ?? []) : ?>
                                            <?php foreach ($niveaux as $niveau) : ?>
                                                <option value="<?= $niveau['id'] ?>"><?= $niveau['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelConventionRioFrom()">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="conventionRIOmodbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formConventionRioID = null;
    let conventionRIOReferentielId = null;
    const programmes = Object.values(<?php echo json_encode($programmes ?? []); ?>);
    const niveaux = Object.values(<?php echo json_encode($niveaux ?? []); ?>);

    $(document).ready(function() {
        $('#addConventionRioModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('referentiel_id');
            const form = document.getElementById('FormConventionRio');

            form.referentiel_id.value = dataId;
            conventionRIOReferentielId = dataId;
            await loadConventionRioTable();
        });

        $('#addConventionRioModal').on('hidden.bs.modal', function() {
            $('#FormConventionRio')[0].reset();
            $('#conventionRIOTableBody').html('');
            $('#conventionRIOLoadingScreen').show();
            $('#conventionRIOContentContainer').hide();
            conventionRIOReferentielId = null;
            cancelConventionRioFrom()
        });

        $('#conventionRIOprogramme').on('change', function() {
            const programmeId = $(this).val();
            loadNiveauResultat(programmeId);
        });

        $('#FormConventionRio').submit(function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = formConventionRioID ? './apis/convention_rio.routes.php?id=' + formConventionRioID : './apis/convention_rio.routes.php';
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                data: formData,
                processData: false,
                contentType: false,
                success: async function(result) {
                    if (result.status === 'success') {
                        successAction('Convention RIO ajoutée avec succès', 'no-reload');
                    } else {
                        errorAction(result.message || 'Erreur lors de l\'ajout de la convention RIO');
                    }
                },
                error: function(xhr, status, error) {
                    errorAction('Impossible d\'ajouter la convention RIO: ' + error.message);
                },
                complete: function() {
                    cancelConventionRioFrom();
                }
            });
        })
    });

    async function loadConventionRioTable() {
        const tableBody = $('#conventionRIOTableBody');
        tableBody.html('');

        if (conventionRIOReferentielId) {
            try {
                $('#conventionRIOLoadingScreen').show();
                $('#conventionRIOContentContainer').hide();
                $('#conventionRIOLoadingText').text("Chargement des conventionRIO...");

                const response = await fetch(`./apis/convention_rio.routes.php?referentiel_id=${conventionRIOReferentielId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                if (result.status === 'success') {
                    result.data.forEach(conventionRIO => {
                        tableBody.append(`
                    <tr>
                        <td class="align-middle">${conventionRIO.code}</td>
                        <td class="align-middle">${programmes.find(programme => programme.id == conventionRIO.programme).sigle}</td>
                        <td class="align-middle">${niveaux.find(niveau => niveau.id == conventionRIO.niveau).name}</td>
                        <td>
                            <?php if (checkPermis($db, 'update')) : ?>
                              <button class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" onclick="editConventionRio(${conventionRIO.id})">
                                <span class="uil-pen fs-8"></span>
                              </button>
                            <?php endif; ?>
                            <button onclick="deleteConventionRio(${conventionRIO.id})"
                                type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                <span class="uil-trash-alt fs-8"></span>
                            </button>
                        </td>
                    </tr>
                `);
                    });
                }
            } catch (error) {
                warningAction('Aucune données trouvées', 'Impossible de charger les conventionRIO');
            } finally {
                $('#conventionRIOLoadingScreen').hide();
                $('#conventionRIOContentContainer').show();
            }
        }
    }

    function loadNiveauResultat(programmeId) {
        const conventionRIOniveau = document.getElementById('conventionRIOniveau_resultat');
        conventionRIOniveau.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Selectionner un niveau de résultat';
        conventionRIOniveau.appendChild(option);

        niveaux.forEach(niveau => {
            if (niveau.programme == programmeId) {
                const option = document.createElement('option');
                option.value = niveau.id;
                option.textContent = niveau.name;
                conventionRIOniveau.appendChild(option);
            }
        });
    }

    async function editConventionRio(dataId) {
        $('#conventionRIOLoadingScreen').show();
        $('#conventionRIOContentContainer').hide();
        const form = document.getElementById('FormConventionRio');
        formConventionRioID = dataId;

        if (dataId) {
            $('#conventionRIOmodtitle').text('Modifier la conventionRIO');
            $('#conventionRIOmodbtn').text('Modifier');
            $('#conventionRIOLoadingText').text("Chargement des données de la conventionRIO...");
            showConventionRioFrom();

            try {
                const response = await fetch(`./apis/convention_rio.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                if (result.status === 'success') {
                    form.code.value = result.data.code;
                    form.programme.value = result.data.programme;
                    form.niveau.value = result.data.niveau;
                    form.referentiel_id.value = result.data.referentiel_id;
                } else {
                    throw new Error('Données invalides');
                }
            } catch (error) {
                errorAction('Impossible de charger les données.');
            } finally {
                $('#conventionRIOLoadingScreen').hide();
                $('#conventionRIOContentContainer').show();
            }
        } else {
            cancelConventionRioFrom();
        }
    }

    function deleteConventionRio(id) {
        deleteData(id, 'Êtes-vous sûr de vouloir supprimer cette conventionRIO ?', 'convention_rio', 'no-reload')
            .then(() => {
                loadConventionRioTable();
            })
            .catch(error => {
                errorAction('Erreur lors de la suppression');
            })
    }

    function cancelConventionRioFrom() {
        $('#FormConventionRio').addClass('d-none');
        $('#conventionRIOTableContainer').removeClass('d-none');
        $('#conventionRIOmodtitle').text('Ajouter une conventionRIO');
        $('#conventionRIOmodbtn').text('Ajouter');
        $('#FormConventionRio')[0].reset();
        formConventionRioID = null;
        loadConventionRioTable();
    }

    function showConventionRioFrom() {
        $('#FormConventionRio').removeClass('d-none');
        $('#conventionRIOTableContainer').addClass('d-none');
    }
</script>