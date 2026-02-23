<div class="modal fade" id="addFacteurEmiModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addFacteurEmiModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="facteurEmi_modtitle">Ajouter un facteur</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div id="facteurLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="facteurLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="facteurContentContainer" style="display: none;">
                    <form action="" method="POST" name="FormFacteurEmi" id="FormFacteurEmi">
                        <div class="row g-4">
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name"
                                        placeholder="Entrer le nom de la tâche" required />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Type*</label>
                                    <select class="form-select" name="type" required>
                                        <option value="" selected disabled>Sélectionner un type</option>
                                        <?php foreach (listTypeFacteur() as $key => $value): ?>
                                            <option value="<?= $key ?>"><?= $value ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Gaz*</label>
                                    <select class="form-select" name="gaz" required>
                                        <option value="">Sélectionner un gaz</option>
                                        <?php if ($gazs ?? []) : ?>
                                            <?php foreach ($gazs as $gaz): ?>
                                                <option value="<?= $gaz['name'] ?>"><?= $gaz['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Valeur*</label>
                                    <input class="form-control" placeholder="Valeur" type="text" name="valeur" />
                                </div>
                            </div>

                            <div class="col-lg-6 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Unité*</label>
                                    <select class="form-select" name="unite" required>
                                        <option value="">Sélectionner une unité</option>
                                        <?php if ($unites ?? []) : ?>
                                            <?php foreach ($unites as $unite): ?>
                                                <option value="<?= $unite['name'] ?>"><?= $unite['description'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <input type="hidden" name="referentiel_id" id="facteurEmi_referentiel">
                            <input type="hidden" name="mesure_id" id="facteurEmi_mesure">
                            <input type="hidden" name="projet_id" id="facteurEmi_projet">

                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="facteurEmi_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formFacteurEmiID = null;
    $(document).ready(function() {
        $('#addFacteurEmiModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const referentielId = $(event.relatedTarget).data('referentiel_id');
            const mesureId = $(event.relatedTarget).data('mesure_id');
            const projetId = $(event.relatedTarget).data('projet_id');
            const form = document.getElementById('FormFacteurEmi');

            form.referentiel_id.value = referentielId ? referentielId : '';
            form.mesure_id.value = mesureId ? mesureId : '';
            form.projet_id.value = projetId ? projetId : '';

            $('#facteurLoadingScreen').show();
            $('#facteurContentContainer').hide();
            if (dataId) {
                formFacteurEmiID = dataId;
                $('#facteurEmi_modtitle').text('Modifier le facteur');
                $('#facteurEmi_modbtn').text('Modifier');
                $('#facteurLoadingText').text("Chargement des données activité...");

                try {
                    const response = await fetch(`./apis/facteurs.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.unite.value = result.data.unite;
                    form.type.value = result.data.type;
                    form.gaz.value = result.data.gaz;
                    form.valeur.value = result.data.valeur;
                    form.referentiel_id.value = result.data.referentiel_id;
                    form.mesure_id.value = result.data.mesure_id;
                    form.projet_id.value = result.data.projet_id;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#facteurLoadingScreen').hide();
                    $('#facteurContentContainer').show();
                }
            } else {
                formFacteurEmiID = null;
                $('#facteurEmi_modtitle').text('Ajouter un facteur');
                $('#facteurEmi_modbtn').text('Ajouter');
                $('#facteurLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#facteurLoadingScreen').hide();
                    $('#facteurContentContainer').show();
                }, 200);
            }
        });

        $('#addFacteurEmiModal').on('hide.bs.modal', function() {
            setTimeout(() => {
                $('#facteurLoadingScreen').show();
                $('#facteurContentContainer').hide();
            }, 200);
            $('#FormFacteurEmi')[0].reset();
        });


        $('#FormFacteurEmi').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const url = formFacteurEmiID ? `./apis/facteurs.routes.php?id=${formFacteurEmiID}` : './apis/facteurs.routes.php';
            const submitBtn = $('#facteurEmi_modbtn');
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
                    $('#addFacteurEmiModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données.');
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>