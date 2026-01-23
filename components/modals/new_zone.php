<?php $array_type = ".shp, .shx, .dbf, .prj, .sbn, .sbx, .fbn, .fbx, .ain, .aih, .ixs, .mxs, .atx, .mtx, .zip"; ?>
<div class="modal fade" id="addZoneModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addZoneModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="zone_modtitle">Ajouter une zone</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="zoneLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="zoneLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden)-->
                <div id="zoneContentContainer" style="display: none;">
                    <form action="" name="FormZone" id="FormZone" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input class="form-control" oninput="checkColumns('code', 'zone_code', 'zone_codeFeedback', 'zones')" type="text" name="code" id="zone_code" placeholder="Entrer le code" required />
                                    <div id="zone_codeFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name" id="zone_name" placeholder="Entrer le libellé" required />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Type*</label>
                                    <select class="form-select" name="type_id" id="zone_type_id" required>
                                        <option value="">Sélectionner</option>
                                        <?php if ($type_zones ?? []) : ?>
                                            <?php foreach ($type_zones as $type_zone) : ?>
                                                <option value="<?php echo $type_zone['id']; ?>"><?php echo $type_zone['name']; ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Superficie (km²)</label>
                                    <input class="form-control" type="text" name="superficie" id="zone_superficie" placeholder="Entrer la superficie" />
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Couches (<?php echo $array_type; ?>)</label>
                                    <input class="form-control" name="file" id="file_couche_zone" type="file" accept="<?php echo $array_type; ?>" />
                                    <input type="hidden" name="allow_files" id="allow_file_zone" value="<?php echo $array_type; ?>">
                                    <input type="hidden" name="couches" id="zone_couches">
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Couleur sur la carte</label>
                                    <input class="form-control" name="couleur" id="zone_couleur" type="color" />
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Afficher sur la carte par défaut</label>
                                    <div class="form-check d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" id="zone_oui_afficher" type="radio" name="afficher" checked="" />
                                            <label class="form-check-label" for="zone_oui_afficher">Oui</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" id="zone_non_afficher" type="radio" name="afficher" />
                                            <label class="form-check-label" for="zone_non_afficher">Non</label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="zone_description" placeholder="Entrer la description"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="zone_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formZoneID = null;
    $(document).ready(function() {
        $('#addZoneModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormZone');

            $('#zoneLoadingScreen').show();
            $('#zoneContentContainer').hide();
            form.reset();

            if (dataId) {
                formZoneID = dataId;
                $('#zone_modtitle').text('Modifier la zone');
                $('#zone_modbtn').text('Modifier');
                $('#zoneLoadingText').text("Chargement des données de la zone...");

                try {
                    const response = await fetch(`./apis/zones.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        form.name.value = result.data.name;
                        form.code.value = result.data.code;
                        form.description.value = result.data.description;
                        form.type_id.value = result.data.type_id;
                        form.superficie.value = result.data.superficie;
                        form.couches.value = result.data.couches;
                        form.couleur.value = result.data.couleur;
                        $('#zone_oui_afficher').prop('checked', result.data.afficher == "1" ? true : false);
                        $('#zone_non_afficher').prop('checked', result.data.afficher == "0" ? true : false);
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#zoneLoadingScreen').hide();
                    $('#zoneContentContainer').show();
                }
            } else {
                $('#zoneLoadingText').text("Préparation du formulaire...");
                $('#zone_modtitle').text('Ajouter une zone');
                $('#zone_modbtn').text('Ajouter');
                formZoneID = null;

                setTimeout(() => {
                    $('#zoneLoadingScreen').hide();
                    $('#zoneContentContainer').show();
                }, 200);
            }
        });

        $('#addZoneModal').on('hide.bs.modal', function() {
            $('#FormZone')[0].reset();
            setTimeout(() => {
                $('#zoneLoadingScreen').show();
                $('#zoneContentContainer').hide();
            }, 200);
        });

        $('#FormZone').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = formZoneID ? `./apis/zones.routes.php?id=${formZoneID}` : './apis/zones.routes.php';
            const submitBtn = $('#zone_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');
            formData.append('afficher', $('#zone_oui_afficher').is(':checked') ? 1 : 0);

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
                    successAction(formZoneID ? 'Zone modifiée avec succès.' : 'Zone ajoutée avec succès.');
                    $('#addZoneModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données: ' + error.message);
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>