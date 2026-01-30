<!-- modal -->
<div class="modal fade" id="addInventoryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addInventoryModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="inventory_modtitle">Ajouter un inventaire</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="inventoryLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="inventoryLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="inventoryContentContainer" style="display: none;">
                    <div id="inventoryTableContainer" class="card rounded-1 bg-white dark__bg-dark">
                        <div class="card-header d-flex justify-content-between align-items-center p-2">
                            <h5 class="mb-0">Liste des inventaires</h5>
                            <button class="btn btn-sm btn-subtle-primary" onclick="showInventoryForm()">
                                <span class="fas fa-plus me-2"></span>Nouvel inventaire
                            </button>
                        </div>
                        <div class="card-body p-2" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" align="center">
                                <thead class="bg-primary-subtle">
                                    <tr>
                                        <th scope="col" class="text-center">Année</th>
                                        <th scope="col" class="text-center">Intitulé</th>
                                        <th scope="col" class="text-center">Unité</th>
                                        <th scope="col" class="text-center">Méthode IPCC</th>
                                        <th scope="col" class="text-center">Statut</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="viewInventoryBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="inventoryFormContainer" style="display: none;">
                        <form action="" name="FormInventory" id="FormInventory" method="POST" enctype="multipart/form-data">
                            <div class="row g-4">
                                <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Année*</label>
                                        <input oninput="checkColumns('annee', 'inventory_annee', 'inventory_annee_feedback', 'inventories')"
                                            class="form-control" type="number" min="2000" max="2100" name="annee" id="inventory_annee" value="<?= date('Y') ?>" placeholder="Entrer l'année" required />
                                        <div class="invalid-feedback" id="inventory_annee_feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Unité*</label>
                                        <select class="form-select" name="unite" id="inventory_unite" required>
                                            <option value="" selected disabled>Sélectionner une unité</option>
                                            <?php if ($unites ?? []) : ?>
                                                <?php foreach ($unites as $unite) : ?>
                                                    <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Intitulé*</label>
                                        <input class="form-control" type="text" name="name" id="inventory_name" placeholder="Entrer le libellé" required />
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Méthode IPCC*</label>
                                        <input class="form-control" type="text" name="methode_ipcc" id="inventory_methode_ipcc" placeholder="Entrer le libellé" required />
                                    </div>
                                </div>
                                <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Source des données*</label>
                                        <input class="form-control" type="text" name="source_donnees" id="inventory_source_donnees" placeholder="Entrer le libellé" required />
                                    </div>
                                </div>

                                <!-- <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Statut *</label>
                                        <select class="form-select" name="status" id="inventory_status" required>
                                            <option value="" selected disabled>Sélectionner un statut</option>
                                            <option value="importe">Importer</option>
                                            <option value="approuve">Approuver</option>
                                            <option value="valide">Valider</option>
                                            <option value="publie">Publier</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Publication*</label>
                                        <select class="form-select" name="afficher" id="inventory_publication" required>
                                            <option value="" selected disabled>Sélectionner une publication</option>
                                            <option value="publie">Publier</option>
                                            <option value="non_publie">Non publier</option>
                                        </select>
                                    </div>
                                </div> -->

                                <div class="col-lg-12 mt-1">
                                    <div class="mb-1">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" id="inventory_description" placeholder="Entrer la description"></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelInventoryForm()">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="inventory_modbtn">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formInventoryID = null;
    $(document).ready(function() {
        $('#addInventoryModal').on('shown.bs.modal', async function(event) {
            $('#inventoryLoadingScreen').show();
            $('#inventoryContentContainer').hide();

            await loadInventories();
            cancelInventoryForm();
        });

        $('#addInventoryModal').on('hide.bs.modal', function() {
            $('#FormInventory')[0].reset();
            setTimeout(() => {
                $('#inventoryLoadingScreen').show();
                $('#inventoryContentContainer').hide();
            }, 200);
        });

        $('#FormInventory').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            await saveInventory(formInventoryID, formData, 'no-reload');
        });
    });

    async function loadInventories() {
        const tbody = $('#viewInventoryBody');
        tbody.html('');

        try {
            const response = await fetch(`./apis/inventories.routes.php`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            if (result.status === 'success' && result.data.length > 0) {
                result.data
                    .sort((a, b) => b.annee - a.annee)
                    .forEach(element => {
                        tbody.append(`
                                <tr class="align-middle">
                                <td class="text-center px-2">${element.annee}</td>
                                <td class="text-center px-2">${element.name}</td>
                                <td class="text-center px-2">${element.unite}</td>
                                <td class="text-center px-2">${element.methode_ipcc}</td>
                                <td class="text-center px-2">
                                    <span class="badge rounded-pill badge-phoenix fs-10 py-1 badge-phoenix-${ element.afficher == 'oui' ? 'success' : 'danger' }">
                                    <span class="badge-label">${element.afficher == 'oui' ? 'Actif' : 'Inactif'}</span>
                                    </span>
                                </td>
                                <td class="text-center px-2 d-flex justify-content-center align-items-center gap-2">
                                <?php if (checkPermis($db, 'update', 2)) : ?>
                                    <button title="${element.afficher == 'oui' ? 'Désactiver' : 'Activer'}" onclick="updateState('${element.id}', '${element.afficher == 'oui' ? 'non' : 'oui'}', 'Êtes-vous sûr de vouloir ${element.afficher == 'oui' ? 'désactiver' : 'activer'} cet inventaire ?', 'inventories')"
                                        type="button" class="btn btn-sm btn-phoenix-warning fs-10 px-2 py-1">
                                        <span class="uil-${element.afficher == 'oui' ? 'check-circle text-success' : 'ban text-warning'} fs-8"></span>
                                    </button>
                                <?php endif; ?>
                                <?php if (checkPermis($db, 'update')) : ?>
                                    <button type="button" onclick="editInventory('${element.id}')" 
                                    class="btn btn-icon btn-phoenix-primary btn-sm fs-9">
                                    <i class="fas fa-edit"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if (checkPermis($db, 'delete')) : ?>
                                    <button type="button" onclick="deleteInventory('${element.id}')" 
                                    class="btn btn-icon btn-phoenix-danger btn-sm fs-9">
                                    <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                                </td>
                            </tr>`);
                    });
            } else {
                tbody.html('<tr><td colspan="5" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
            }
        } catch (error) {
            tbody.html('<tr><td colspan="5" class="text-center py-5">Aucune donnée trouvée.</td></tr>');
        } finally {
            $('#inventoryLoadingScreen').hide();
            $('#inventoryContentContainer').show();
        }
    }

    async function saveInventory(inventoryId, formData, action) {
        const submitBtn = $('#inventory_modbtn');
        submitBtn.prop('disabled', true);
        submitBtn.text('Envoi en cours...');
        const url = inventoryId ? './apis/inventories.routes.php?id=' + inventoryId : './apis/inventories.routes.php';

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
                successAction('Données envoyées avec succès.', action);
            } else {
                errorAction(result.message || 'Erreur lors de l\'envoi des données.');
            }
        } catch (error) {
            errorAction('Erreur lors de l\'envoi des données: ' + error.message);
        } finally {
            cancelInventoryForm();
        }
    }

    async function editInventory(dataId) {
        $('#inventoryLoadingScreen').show();
        $('#inventoryContentContainer').hide();
        const form = document.getElementById('FormInventory');
        formInventoryID = dataId;

        if (dataId) {
            $('#inventory_modtitle').text('Modifier les valeurs suivies annuelles');
            $('#inventory_modbtn').text('Modifier');
            $('#inventoryLoadingText').text("Chargement des données suivies...");
            showInventoryForm();

            try {
                const response = await fetch(`./apis/inventories.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                if (result.status === 'success') {
                    form.annee.value = result.data.annee;
                    form.unite.value = result.data.unite;
                    form.name.value = result.data.name;
                    form.methode_ipcc.value = result.data.methode_ipcc;
                    form.source_donnees.value = result.data.source_donnees;
                    form.description.value = result.data.description;
                }
            } catch (error) {
                errorAction('Impossible de charger les données.');
            } finally {
                $('#inventoryLoadingScreen').hide();
                $('#inventoryContentContainer').show();
            }
        } else {
            cancelInventoryForm();
        }
    }

    async function deleteInventory(id) {
        deleteData(id, 'Êtes-vous sûr de vouloir supprimer ce suivi ?', 'inventories', 'none')
            .then(() => {
                loadInventories();
            })
            .catch(error => {
                errorAction('Erreur lors de la suppression');
            })
    }

    function showInventoryForm() {
        $('#inventoryTableContainer').hide();
        $('#inventoryFormContainer').show();
    }

    function cancelInventoryForm() {
        $('#FormInventory')[0].reset();
        $('#inventoryFormContainer').hide();
        $('#inventoryTableContainer').show();
        $('#inventory_modtitle').text('Liste des inventaires');
        $('#inventory_modbtn').text('Ajouter');
        $('#inventory_modbtn').prop('disabled', false);
        loadInventories();
    }
</script>