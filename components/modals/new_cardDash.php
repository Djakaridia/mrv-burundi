<!-- modal -->
<div class="modal fade" id="addCardDashModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addCardDashModal" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="Card_modtitle">Ajouter une card</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="CardLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="CardLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden) -->
                <div id="cardContentContainer" style="display: none;">
                    <form name="FormCard" id="FormCard" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-12 mb-1">
                                <label for="intitule" class="form-label">Titre</label>
                                <input type="text" class="form-control" id="intitule" name="intitule" required>
                            </div>

                            <div class="col-4 mb-1">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control" id="position" name="position" required>
                            </div>
                            <div class="col-4 mb-1">
                                <label for="entity_type" class="form-label">Catégorie</label>
                                <select class="form-select" id="entity_type" name="entity_type" required>
                                    <option value="" selected disabled>Sélectionner une catégorie</option>
                                    <option value="indicateur">Indicateur</option>
                                    <option value="projet">Projet</option>
                                </select>
                            </div>
                            <div class="col-4 mb-1">
                                <label for="icone" class="form-label">Icône</label>
                                <select class="form-select icon-font" id="icone" name="icone" required>
                                    <option value="" selected disabled>Sélectionner une icône</option>
                                    <?php foreach (listIcones() as $key => $value) { ?>
                                        <option class="<?= in_array($value['icon'], $iconeSelected ?? []) ? 'text-light' : '' ?>"
                                            value="<?= $value['icon'] ?>"><?= $value['unicode'] ?> <?= $key ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-12 mb-1">
                                <label for="entity_id" class="form-label">Indicateur / Projet</label>
                                <select class="form-select mb-1" id="entity_id" name="entity_id" required>
                                    <option value="" selected disabled>Sélectionner une valeur</option>
                                </select>
                            </div>

                            <div class="col-12 mb-1">
                                <label for="couleur" class="form-label">Couleur</label>
                                <input type="hidden" id="couleur" name="couleur" required>
                                <div class="d-flex flex-wrap gap-3 mb-2">
                                    <?php foreach (listCouleur() as $key => $value) { ?>
                                        <div key="<?= $key ?>" title="<?= $key ?>" class="card bg-<?= $value ?> text-white text-center cursor-pointer color-dash-card rounded-1" data-color="<?= $value ?>" style="width: 50px; height: 30px;"></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="Card_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const entity_idSelect = document.getElementById('entity_id');
    const entityTypeSelect = document.getElementById('entity_type');
    const indicateurs = <?= json_encode($indicateurs ?? []) ?>;
    const projets = <?= json_encode($projects ?? []) ?>;

    entityTypeSelect.addEventListener('change', function() {
        const selectedCategory = this.value;
        entity_idSelect.innerHTML = '<option value="" selected disabled>Sélectionner une valeur</option>';

        if (selectedCategory === 'indicateur') {
            indicateurs.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.intitule;
                entity_idSelect.appendChild(option);
            });
        } else if (selectedCategory === 'projet') {
            projets.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                entity_idSelect.appendChild(option);
            });
        }
    });

    document.querySelectorAll('.color-dash-card').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.color-dash-card').forEach(function(card) {
                card.classList.remove('border', 'border-dark', 'border-3');
                card.innerHTML = '';
            });

            this.classList.add('border', 'border-dark', 'border-3');
            this.innerHTML = '✔️';
            document.getElementById('couleur').value = this.dataset.color;
        });
    });


    let FormCardID = null;
    $(document).ready(function() {
        $('#addCardDashModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormCard');

            $('#CardLoadingScreen').show();
            $('#cardContentContainer').hide();
            form.reset();

            if (dataId) {
                FormCardID = dataId;
                $('#Card_modtitle').text('Modifier la carte');
                $('#Card_modbtn').text('Modifier');
                $('#CardLoadingText').text("Chargement des données de la carte...");

                try {
                    const response = await fetch(`./apis/sections_dash.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        form.intitule.value = result.data.intitule;
                        form.position.value = result.data.position;
                        form.icone.value = result.data.icone;
                        form.entity_type.value = result.data.entity_type;
                        form.couleur.value = result.data.couleur;

                        entity_idSelect.innerHTML = '<option value="">Sélectionner une valeur</option>';
                        if (result.data.entity_type === 'indicateur') {
                            indicateurs.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = item.intitule;
                                entity_idSelect.appendChild(option);
                            });
                        } else if (result.data.entity_type === 'projet') {
                            projets.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.id;
                                option.textContent = item.name;
                                entity_idSelect.appendChild(option);
                            });
                        }

                        entity_idSelect.value = result.data.entity_id;
                        document.querySelectorAll('.color-dash-card').forEach(function(card) {
                            card.classList.remove('border', 'border-dark', 'border-3');
                            card.innerHTML = '';
                        });

                        const selectedCard = document.querySelector(`.color-dash-card[data-color="${result.data.couleur}"]`);
                        if (selectedCard) {
                            selectedCard.classList.add('border', 'border-dark', 'border-3');
                            selectedCard.innerHTML = '✔️';
                        }
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {                    
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#CardLoadingScreen').hide();
                    $('#cardContentContainer').show();
                }
            } else {
                $('#CardLoadingText').text("Préparation du formulaire...");
                $('#Card_modtitle').text('Ajouter une carte');
                $('#Card_modbtn').text('Ajouter');
                FormCardID = null;

                setTimeout(() => {
                    $('#CardLoadingScreen').hide();
                    $('#cardContentContainer').show();
                }, 200);
            }
        });

        $('#addCardDashModal').on('hide.bs.modal', function() {
            $('#FormCard')[0].reset();
            setTimeout(() => {
                $('#CardLoadingScreen').show();
                $('#cardContentContainer').hide();
            }, 200);
        });

        $('#FormCard').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = FormCardID ? `./apis/sections_dash.routes.php?id=${FormCardID}` : './apis/sections_dash.routes.php';
            const submitBtn = $('#Card_modbtn');
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
                    successAction(FormCardID ? 'Card modifiée avec succès.' : 'Card ajoutée avec succès.');
                    $('#addCardDashModal').modal('hide');
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