<!-- modal -->
<div class="modal fade" id="addRoleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="addRoleModal" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="role_modtitle">Ajouter un role</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span
                        class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <form action="" method="POST" name="FormRole" id="FormRole">
                    <div id="roleLoadingScreen" class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <h4 class="mt-3 fw-bold text-primary" id="roleLoadingText">Chargement en cours</h4>
                    </div>

                    <!-- Content Container (initially hidden) -->
                    <div id="roleContentContainer" style="display: none;">
                        <div class="row g-4">
                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Libellé*</label>
                                    <input class="form-control" type="text" name="name" placeholder="Entrer le libellé"
                                        required />
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label class="form-label">Niveau*</label>
                                    <select class="form-select" name="niveau" id="niveau" required>
                                        <option value="" disabled selected>Sélectionner le niveau</option>
                                        <option value="1">Super Administateur</option>
                                        <option value="2">Administateur</option>
                                        <option value="3">Editeur</option>
                                        <option value="4">Visiteur</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-1">
                                <div class="mb-1">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" rows="3" name="description"
                                        placeholder="Entrer une description"></textarea>
                                </div>
                            </div>

                            <input type="hidden" name="page_edit[]" id="page_edit">
                            <input type="hidden" name="page_delete[]" id="page_delete">
                            <input type="hidden" name="page_interdite[]" id="page_interdite">
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                                aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0"
                                id="role_modbtn">Ajouter</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    let formRoleID = null;
    $(document).ready(function () {
        $('#addRoleModal').on('shown.bs.modal', async function (event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormRole');
            // Show loading screen and hide content
            $('#roleLoadingScreen').show();
            $('#roleContentContainer').hide();

            if (dataId) {
                formRoleID = dataId;
                $('#role_modtitle').text('Modifier le role');
                $('#role_modbtn').text('Modifier');
                $('#roleLoadingText').text("Chargement des données role...");

                try {
                    const response = await fetch(`./apis/roles.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    const role = result.data;
                    form.name.value = role.name;
                    form.niveau.value = role.niveau;
                    form.description.value = role.description;
                    form.page_edit.value = role.page_edit;
                    form.page_delete.value = role.page_delete;
                    form.page_interdite.value = role.page_interdite;
                } catch (error) {
                    console.error(error);
                    errorAction('Une erreur est survenue lors du chargement des données.');

                }
                finally {
                    $('#roleLoadingScreen').hide();
                    $('#roleContentContainer').show();
                }
            } else {
                formRoleID = null;
                $('#role_modbtn').text(dataId ? 'Modifier' : 'Ajouter');
                $('#role_modtitle').text(dataId ? 'Modifier un role' : 'Ajouter un role');
                $('#roleLoadingText').text("Préparation du formulaire...");
                
                setTimeout(() => {
                    $('#roleLoadingScreen').hide();
                    $('#roleContentContainer').show();
                }, 200);
            }
        });

        $('#addRoleModal').on('hide.bs.modal', function () {
            setTimeout(()=> {
              $('#roleLoadingScreen').show();
              $('#roleContentContainer').hide();
            }, 200);
            $('#FormRole')[0].reset();
        });

        $('#FormRole').on('submit', async function (e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            const url = formRoleID ? `./apis/roles.routes.php?id=${formRoleID}` : './apis/roles.routes.php';
            const submitBtn = $('#role_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.status === 'success') {
                    form.reset();
                    $('#addRoleModal').modal('hide');
                    successAction(result.message);
                } else {
                    errorAction(result.message);
                }
            } catch (error) {
                console.error(error);
                errorAction('Une erreur est survenue. Veuillez réessayer.');
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>