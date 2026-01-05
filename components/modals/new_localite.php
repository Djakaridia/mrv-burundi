<!-- modal -->
<div class="modal fade" id="addLocaliteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addLocaliteModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="localite_modtitle">Ajouter une localité</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="localiteLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="localiteLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="localiteContentContainer" style="display: none;">
                    <form action="" name="FormLocalite" id="FormLocalite" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Code*</label>
                                    <input class="form-control form-control-sm" type="text" name="code" id="localite_code" placeholder="Entrer le code" required />
                                    <div class="invalid-feedback" id="localite_code_feedback"></div>
                                </div>
                            </div>
                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control form-control-sm" type="text" name="name" id="localite_name" placeholder="Entrer le libellé" required />
                                </div>
                            </div>

                            <!-- Selection du parent -->
                            <div class="col-lg-12 mt-1">
                                <div id="parent_niv_1" class="mb-1 d-none">
                                    <label class="form-label">Région*</label>
                                    <select class="form-select form-select-sm" name="parent" id="loc_parent_1">
                                        <option value="">Sélectionner un région</option>
                                        <?php if ($regions ?? []) : ?>
                                            <?php foreach ($regions as $region) : ?>
                                                <option value="<?php echo $region['code'] ?>"><?php echo $region['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div id="parent_niv_2" class="mb-1 d-none">
                                    <label class="form-label">Département*</label>
                                    <select class="form-select form-select-sm" name="parent" id="loc_parent_2">
                                        <option value="">Sélectionner un département</option>
                                        <?php if ($departements ?? []) : ?>
                                            <?php foreach ($departements as $departement) : ?>
                                                <option value="<?php echo $departement['code'] ?>"><?php echo $departement['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div id="parent_niv_3" class="mb-1 d-none">
                                    <label class="form-label">Arrondissement*</label>
                                    <select class="form-select form-select-sm" name="parent" id="loc_parent_3">
                                        <option value="">Sélectionner un arrondissement</option>
                                        <?php if ($arrondissements ?? []) : ?>
                                            <?php foreach ($arrondissements as $arrondissement) : ?>
                                                <option value="<?php echo $arrondissement['code'] ?>"><?php echo $arrondissement['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div id="parent_niv_4" class="mb-1 d-none">
                                    <label class="form-label">Commune*</label>
                                    <select class="form-select form-select-sm" name="parent" id="loc_parent_4">
                                        <option value="">Sélectionner un commune</option>
                                        <?php if ($communes ?? []) : ?>
                                            <?php foreach ($communes as $commune) : ?>
                                                <option value="<?php echo $commune['code'] ?>"><?php echo $commune['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="region_detail" class="col-12 mt-1 d-none">
                                <div class="row">
                                    <div class="col-lg-6 mt-1">
                                        <label class="form-label">Abbréviation</label>
                                        <input class="form-control form-control-sm" type="text" name="sigle" id="localite_abbrev" placeholder="Entrer l'abbréviation" />
                                    </div>
                                    <div class="col-lg-6 mt-1">
                                        <label class="form-label">Couleur</label>
                                        <input class="form-control form-control-sm" type="color" name="couleur" id="localite_couleur" />
                                    </div>
                                </div>
                            </div>

                            <div id="village_detail" class="col-12 mt-1 d-none">
                                <div class="row">
                                    <div class="col-6 mb-1 form-group">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="text" name="latitude" id="latitude" />
                                    </div>
                                    <div class="col-6 mb-1 form-group">
                                        <label for="contact" class="form-label">Longitude </label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="text" name="longitude" id="longitude" />
                                    </div>

                                    <div class="col-6 mb-1 form-group">
                                        <label for="hommes" class="form-label">Nombre Homme </label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="number" name="hommes" id="localite_hommes" />
                                    </div>
                                    <div class="col-6 mb-1 form-group">
                                        <label for="femmes" class="form-label">Nombre Femme </label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="number" name="femmes" id="localite_femmes" />
                                    </div>

                                    <div class="col-6 mb-1 form-group">
                                        <label for="jeunes" class="form-label">Nombre Jeune </label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="number" name="jeunes" id="localite_jeunes" />
                                    </div>
                                    <div class="col-6 mb-1 form-group">
                                        <label for="adultes" class="form-label">Nombre Adulte </label>
                                        <input class="form-control form-control-sm form-control form-control-sm-sm" type="number" name="adultes" id="localite_adultes" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-5 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="localite_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const libelle = [{
        api: 'region',
        title: "une région"
    }, {
        api: 'departement',
        title: "un département"
    }, {
        api: 'arrondissement',
        title: "un arrondissement"
    }, {
        api: 'commune',
        title: "une commune"
    }, {
        api: 'village',
        title: "une localité"
    }];
    let formLocID = null;
    let formLocNiveau = null;

    $(document).ready(function() {
        $('#addLocaliteModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const niveau = $(event.relatedTarget).data('niveau');
            const form = document.getElementById('FormLocalite');

            $('#localiteLoadingScreen').show();
            $('#localiteContentContainer').hide();
            $('#FormLocalite')[0].reset();

            if (niveau == 0) $('#region_detail').removeClass('d-none');
            if (niveau > 0) $('#parent_niv_' + niveau).removeClass('d-none');
            if (niveau > 0) $('#loc_parent_' + niveau).attr('required', true);
            if (niveau == 4) $('#village_detail').removeClass('d-none');

            if (dataId) {
                formLocID = dataId;
                formLocNiveau = niveau;

                $('#localite_modtitle').text('Modifier ' + libelle[niveau].title);
                $('#localite_modbtn').text('Modifier');
                $('#localiteLoadingText').text("Chargement des données de la localité...");

                try {
                    const response = await fetch(`./apis/${libelle[niveau].api}.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        form.code.value = result.data.code;
                        form.name.value = result.data.name;

                        if (niveau == 0) {
                            form.sigle.value = result.data.sigle;
                            form.couleur.value = result.data.couleur;
                        }
                        if (niveau == 1) {
                            $('#loc_parent_' + niveau).val(result.data.region)
                        }
                        if (niveau == 2) {
                            $('#loc_parent_' + niveau).val(result.data.departement)
                        }
                        if (niveau == 3) {
                            $('#loc_parent_' + niveau).val(result.data.arrondissement)
                        }
                        if (niveau == 4) {
                            $('#loc_parent_' + niveau).val(result.data.commune)
                            form.latitude.value = result.data.latitude;
                            form.longitude.value = result.data.longitude;
                            form.hommes.value = result.data.hommes;
                            form.femmes.value = result.data.femmes;
                            form.jeunes.value = result.data.jeunes;
                            form.adultes.value = result.data.adultes;
                        }
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#localiteLoadingScreen').hide();
                    $('#localiteContentContainer').show();
                }

            } else {
                formLocID = null;
                formLocNiveau = niveau;

                $('#localite_modtitle').text('Ajouter ' + libelle[niveau].title);
                $('#localite_modbtn').text('Ajouter');
                $('#localiteLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#localiteLoadingScreen').hide();
                    $('#localiteContentContainer').show();
                }, 200);
            }
        });

        $('#addLocaliteModal').on('hide.bs.modal', function() {
            $('#FormLocalite')[0].reset();

            setTimeout(() => {
                $('#localiteLoadingScreen').show();
                $('#localiteContentContainer').hide();
            }, 200);
        });

        $('#FormLocalite').on('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const action = formLocID ? `./apis/${libelle[formLocNiveau].api}.routes.php?id=${formLocID}` : `./apis/${libelle[formLocNiveau].api}.routes.php`;
            formData.append('parent', $('#loc_parent_' + formLocNiveau).val());

            try {
                const response = await fetch(action, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Localité ajoutée avec succès.');
                    $('#addLocaliteModal').modal('hide');
                } else {
                    throw new Error('Données invalides');
                }
            } catch (error) {
                errorAction('Impossible d\'ajouter la localité.');
            } finally {
                $('#localiteLoadingScreen').hide();
                $('#localiteContentContainer').show();
            }
        });

        $('#localite_code').on('input', async function() {
            await checkColumns('code', 'localite_code', 'localite_code_feedback', libelle[formLocNiveau].api);
        });
    });
</script>