<div class="modal fade" id="addRegisterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addRegisterModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="registre_modtitle">Ajouter des données de registre</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="registerLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="registerContentContainer" style="display: none;">
                    <form action="" name="FormAddRegister" id="FormAddRegister" method="POST" enctype="multipart/form-data">
                        <div class="row gx-3">
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Année*</label>
                                <input class="form-control" type="number" min="2000" max="2100" name="annee" id="addRegister_annee" value="<?= date('Y') ?>" placeholder="Entrer l'année" required />
                            </div>
                            <div class="col-lg-9 mb-2">
                                <label class="form-label">Inventaire*</label>
                                <select class="form-select" name="inventaire_id" required>
                                    <option value="" disabled>Sélectionner un inventaire</option>
                                    <?php if ($inventories ?? []) : ?>
                                        <?php foreach ($inventories as $inventaire) : ?>
                                            <option value="<?= $inventaire['id'] ?>"><?= $inventaire['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label class="form-label">Secteur*</label>
                                <select class="form-select" name="secteur_id">
                                    <option value="0" selected disabled>Selectionnez le secteur</option>
                                    <?php if (!empty($secteurs)) : ?>
                                        <?php foreach ($secteurs as $secteur): ?>
                                            <option value="<?php echo $secteur['id'] ?? "" ?>"><?php echo $secteur['name'] ?? "" ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Gaz*</label>
                                <select class="form-select" name="gaz" required>
                                    <option value="" selected disabled>Sélectionner un gaz</option>
                                    <?php if ($gazs ?? []) : ?>
                                        <?php foreach ($gazs as $gaz): ?>
                                            <option value="<?= $gaz['name'] ?>"><?= $gaz['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label class="form-label">Code*</label>
                                <input class="form-control" type="text" name="code" placeholder="Entrer le code" required />
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Catégorie*</label>
                                <input class="form-control" type="text" name="categorie" placeholder="Entrer le catégorie"
                                    required />
                            </div>

                            <div class="col-md-3 mb-2">
                                <div class="mb-1">
                                    <label class="form-label">Emission année*</label>
                                    <input class="form-control" type="text" name="emission_annee" placeholder="Entrer la valeur " required />
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Emission Absolue*</label>
                                <input class="form-control" type="text" name="emission_absolue" placeholder="Entrer la valeur " required />
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Niveau émission*</label>
                                <input class="form-control" type="text" name="emission_niveau" placeholder="Entrer la valeur " required />
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label">Emission cummulée*</label>
                                <input class="form-control" type="text" name="emission_cumulee" placeholder="Entrer la valeur " required />
                            </div>

                            <div class="modal-footer d-flex justify-content-between border-0 pt-2 px-2 pb-0">
                                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                    aria-label="Close">Annuler</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="registre_modbtn">Ajouter </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formRegisterID = null;
    $('#addRegisterModal').on('shown.bs.modal', async function(event) {
        const dataId = $(event.relatedTarget).data('id');
        const inventaireId = $(event.relatedTarget).data('inventory');
        const form = document.getElementById('FormAddRegister');
        form.inventaire_id.value = inventaireId || "";

        if (dataId) {
            formRegisterID = dataId;
            $('#registre_modtitle').text('Modifier la registre');
            $('#registre_modbtn').text('Modifier');
            try {
                const response = await fetch(`./apis/registers.routes.php?id=${dataId}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'GET',
                });

                const result = await response.json();
                form.annee.value = result.data.annee;
                form.inventaire_id.value = result.data.inventaire_id;
                form.secteur_id.value = result.data.secteur_id;
                form.gaz.value = result.data.gaz;
                form.code.value = result.data.code;
                form.categorie.value = result.data.categorie;
                form.emission_annee.value = result.data.emission_annee;
                form.emission_absolue.value = result.data.emission_absolue;
                form.emission_niveau.value = result.data.emission_niveau;
                form.emission_cumulee.value = result.data.emission_cumulee;
            } catch (error) {
                errorAction('Impossible de charger les données.');
            } finally {
                $('#registerLoadingScreen').hide();
                $('#registerContentContainer').show();
            }
        } else {
            formRegisterID = null;
            $('#registre_modtitle').text('Ajouter un registre');
            $('#registre_modbtn').text('Ajouter');

            setTimeout(() => {
                $('#registerLoadingScreen').hide();
                $('#registerContentContainer').show();
            }, 200);
        }
    });

    $('#addRegisterModal').on('hide.bs.modal', function() {
        $('#FormAddRegister')[0].reset();
        $('#registerLoadingScreen').show();
        $('#registerContentContainer').hide();
    });

    $('#FormAddRegister').on('submit', async function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        const submitBtn = $('#addRegister_modbtn');
        submitBtn.prop('disabled', true).text('Envoi en cours...');
        try {
            const response = await fetch(`./apis/registers.routes.php${formRegisterID ? '?id=' + formRegisterID : ''}`, {
                method: "POST",
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            });

            const result = await response.json();
            if (result.status === 'success') {
                successAction('Données envoyées avec succès.');
                $('#addRegisterModal').modal('hide');
            } else {
                errorAction(result.message || 'Erreur lors de l\'envoi des données.');
            }

        } catch (error) {
            errorAction('Erreur lors de l\'envoi des données.');
        }
    });
</script>