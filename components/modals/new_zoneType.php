<!-- modal -->
<div class="modal fade" id="addZoneTypeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addZoneTypeModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="typeZone_modtitle">Ajouter un type de zone</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <!-- Loading Screen -->
                <div id="typeZoneLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="typeZoneLoadingText">Chargement en cours</h4>
                </div>

                <!-- Content Container (initially hidden)-->
                <div id="typeZoneContentContainer" style="display: none;">
                    <form action="" name="FormTypeZone" id="FormTypeZone" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name" id="typeZone_name" placeholder="Entrer le libellé" required />
                                </div>
                            </div>
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="typeZone_description" placeholder="Entrer la description" style="height: 60px"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="typeZone_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let formZoneTypeID = null;
    $(document).ready(function() {
        $('#addZoneTypeModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormTypeZone');

            $('#typeZoneLoadingScreen').show();
            $('#typeZoneContentContainer').hide();
            form.reset();

            if (dataId) {
                formZoneTypeID = dataId;
                $('#typeZone_modtitle').text('Modifier le type de zone');
                $('#typeZone_modbtn').text('Modifier');
                $('#typeZoneLoadingText').text("Chargement des données du type de zone...");

                try {
                    const response = await fetch(`./apis/zone_types.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        form.name.value = result.data.name;
                        form.description.value = result.data.description;
                    } else {
                        throw new Error('Données invalides');
                    }
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#typeZoneLoadingScreen').hide();
                    $('#typeZoneContentContainer').show();
                }
            } else {
                $('#typeZoneLoadingText').text("Préparation du formulaire...");
                $('#typeZone_modtitle').text('Ajouter un type de zone');
                $('#typeZone_modbtn').text('Ajouter');
                formZoneTypeID = null;

                setTimeout(() => {
                    $('#typeZoneLoadingScreen').hide();
                    $('#typeZoneContentContainer').show();
                }, 200);
            }
        });

        $('#addZoneTypeModal').on('hide.bs.modal', function() {
            $('#FormTypeZone')[0].reset();
            setTimeout(() => {
                $('#typeZoneLoadingScreen').show();
                $('#typeZoneContentContainer').hide();
            }, 200);
        });

        $('#FormTypeZone').on('submit', async function(event) {
            event.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = formZoneTypeID ? `./apis/zone_types.routes.php?id=${formZoneTypeID}` : './apis/zone_types.routes.php';
            const submitBtn = $('#typeZone_modbtn');
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
                    successAction(formZoneTypeID ? 'Type de zone modifiée avec succès.' : 'Type de zone ajoutée avec succès.');
                    $('#addZoneTypeModal').modal('hide');
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