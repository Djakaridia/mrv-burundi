<!-- modal -->
<div class="modal fade" id="addTypologieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addTypologieModal" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="typologie_modtitle">Ajouter une typologie</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="typologieLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="typologieLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="typologieContentContainer" style="display: none;">
                    <div id="typologieTableContainer" class="card rounded-1 bg-white dark__bg-dark">
                        <div class="card-header d-flex justify-content-between align-items-center p-2">
                            <h5 class="mb-0">Liste des typologies</h5>
                            <button class="btn btn-sm btn-primary" onclick="showTypologieFrom()">
                                <span class="fas fa-plus me-2"></span>Ajouter
                            </button>
                        </div>
                        <div class="card-body p-0 table-responsive scrollbar" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis mb-0">
                                <thead class="bg-secondary-subtle">
                                    <tr>
                                        <th class="align-middle">Nom de la classe</th>
                                        <th class="align-middle">Couleur</th>
                                        <th class="align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="typologieTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <form id="FormTypologie" class="d-none" action="" name="FormTypologie" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <input type="hidden" name="referentiel_id">
                            <div class="col-lg-12 mt-1" id="classeContainerTypo"></div>
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Couleur</label>
                                    <input class="form-control" type="color" name="couleur" id="typologie_couleur"
                                        placeholder="Entrer la couleur" />
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelTypologieFrom()">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="typologie_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formTypologieID = null;
    let typologieReferentielId = null;
    const regionsTypologie = Object.values(<?php echo json_encode($regions ?? []); ?>);

    $(document).ready(function() {
        $('#addTypologieModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('referentiel_id');
            const echelle = $(event.relatedTarget).data('echelle');
            const form = document.getElementById('FormTypologie');

            form.referentiel_id.value = dataId;
            typologieReferentielId = dataId;
            configTypologieClasse(echelle);
            await loadTypologieTable();
        });

        $('#addTypologieModal').on('hidden.bs.modal', function() {
            $('#FormTypologie')[0].reset();
            $('#typologieTableBody').html('');
            $('#typologieLoadingScreen').show();
            $('#typologieContentContainer').hide();
            typologieReferentielId = null;
            cancelTypologieFrom()
        });

        $('#FormTypologie').submit(function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const url = formTypologieID ? './apis/typologies.routes.php?id=' + formTypologieID : './apis/typologies.routes.php';
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
                        successAction('Typologie ajoutée avec succès', 'no-reload');
                    } else {
                        errorAction(result.message || 'Erreur lors de l\'ajout de la typologie');
                    }
                },
                error: function(xhr, status, error) {
                    errorAction('Impossible d\'ajouter la typologie: ' + error.message);
                },
                complete: function() {
                    cancelTypologieFrom();
                }
            });
        })
    });

    async function loadTypologieTable() {
        const tableBody = $('#typologieTableBody');
        tableBody.html('');

        if (typologieReferentielId) {
            try {
                $('#typologieLoadingScreen').show();
                $('#typologieContentContainer').hide();
                $('#typologieLoadingText').text("Chargement des typologies...");

                const response = await fetch(`./apis/typologies.routes.php?referentiel_id=${typologieReferentielId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                if (result.status === 'success') {


                    result.data.forEach(typologie => {
                        tableBody.append(`
                    <tr>
                        <td>${typologie.name}</td>
                        <td style="min-width:100px;"> <input type="color" class="w-100" disabled value="${typologie.couleur}"> </td>
                        <td>
                            <?php if (checkPermis($db, 'update')) : ?>
                              <button class="btn btn-sm btn-phoenix-info fs-10 px-2 py-1" onclick="editTypologie(${typologie.id})">
                                <span class="uil-pen fs-8"></span>
                              </button>
                            <?php endif; ?>
                            <button onclick="deleteTypologie(${typologie.id})"
                                type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                <span class="uil-trash-alt fs-8"></span>
                            </button>
                        </td>
                    </tr>
                `);
                    });
                } else {
                    tableBody.html('<tr><td colspan="3" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
                }
            } catch (error) {
                tableBody.html('<tr><td colspan="3" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
            } finally {
                $('#typologieLoadingScreen').hide();
                $('#typologieContentContainer').show();
            }
        } else {
            tableBody.html('<tr><td colspan="3" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
            $('#typologieLoadingScreen').hide();
            $('#typologieContentContainer').show();
        }
    }

    async function editTypologie(dataId) {
        $('#typologieLoadingScreen').show();
        $('#typologieContentContainer').hide();
        const form = document.getElementById('FormTypologie');
        formTypologieID = dataId;

        if (dataId) {
            $('#typologie_modtitle').text('Modifier la typologie');
            $('#typologie_modbtn').text('Modifier');
            $('#typologieLoadingText').text("Chargement des données de la typologie...");
            showTypologieFrom();

            try {
                const response = await fetch(`./apis/typologies.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                if (result.status === 'success') {
                    form.name.value = result.data.name;
                    form.couleur.value = result.data.couleur;
                    form.referentiel_id.value = result.data.referentiel_id;
                } else {
                    throw new Error('Données invalides');
                }
            } catch (error) {
                errorAction('Impossible de charger les données.');
            } finally {
                $('#typologieLoadingScreen').hide();
                $('#typologieContentContainer').show();
            }
        } else {
            cancelTypologieFrom();
        }
    }

    async function configTypologieClasse(echelle) {
        $('#classeContainerTypo').html('');
        switch (echelle) {
            case 'nationale':
                $('#classeContainerTypo').html(`
                <div class="mb-1">
                    <label class="form-label">Nom de la classe*</label>
                    <input class="form-control" type="text" name="name" id="typologie_name" placeholder="Entrer le libellé" required />
                </div>`);
                break;
            case 'regionale':
                $('#classeContainerTypo').html(`
                <div class="mb-1">
                    <label class="form-label">Sélection des régions*</label>
                    <select class="form-select" name="name" id="typologie_region_id" required>
                        <option value="" selected disabled>Selectionner une region</option>
                        ${regionsTypologie.map(region => `<option value="${region.name}">${region.name}</option>`).join('')}
                    </select>
                </div>`);
                break;
            default:
                const zones_e = await getTypologieClasse(echelle);
                $('#classeContainerTypo').html(`
                <div class="mb-1">
                    <label class="form-label">Sélection des zones*</label>
                    <select class="form-select" name="name" id="typologie_zone_id" required>
                        <option value="" selected disabled>Selectionner une zone</option>
                        ${zones_e.map(zone => `<option value="${zone.name}">${zone.name}</option>`).join('')}
                    </select>
                </div>`);
                break;
        }
    }

    async function getTypologieClasse(type_id) {
        try {
            const response = await fetch(`./apis/zones.routes.php?type_id=${type_id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            if (result.status === 'success') {
                return result.data;
            } else {
                throw new Error(result.message || 'Erreur lors du chargement des classes');
            }
        } catch (error) {
            errorAction('Impossible de charger les classes');
        }
    }

    function deleteTypologie(id) {
        deleteData(id, 'Êtes-vous sûr de vouloir supprimer cette typologie ?', 'typologies', 'no-reload')
            .then(() => {
                loadTypologieTable();
            })
            .catch(error => {
                errorAction('Erreur lors de la suppression');
            })
    }

    function cancelTypologieFrom() {
        $('#FormTypologie').addClass('d-none');
        $('#typologieTableContainer').removeClass('d-none');
        $('#typologie_modtitle').text('Ajouter une typologie');
        $('#typologie_modbtn').text('Ajouter');
        $('#FormTypologie')[0].reset();
        formTypologieID = null;
        loadTypologieTable();
    }

    function showTypologieFrom() {
        $('#FormTypologie').removeClass('d-none');
        $('#typologieTableContainer').addClass('d-none');
    }
</script>