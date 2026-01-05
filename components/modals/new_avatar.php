<?php
$array_type = ".jpg, .jpeg, .png, .gif, .webp, .tiff, .ico";
?>

<div class="modal fade" id="addAvatarModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addAvatarModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="avatar_modtitle">Ajouter un avatar</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <form action="" name="FormAvatar" id="FormAvatar" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-12 mb-3">
                            <div class="card-title text-center">Importation des fichiers </div>
                            <div class="text-center">(<?php echo $array_type ?>)</div>
                        </div>

                        <div class="col-lg-12 mb-3">
                            <label for="file" class="card btn btn-ghost-secondary border border-secondary-subtle p-3 w-100 bg-white dark__bg-dark">
                                <input type="file" name="file" id="file" accept="<?php echo $array_type ?>" class="form-control text-center form-control-sm border-0 w-100" required />
                                <img class="m-3" src="assets/img/icons/cloud-upload.svg" width="45" alt="" />
                            </label>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 px-3 pb-0">
                            <input type="hidden" name="allow_files" id="allow_file_avatar" value="<?php echo $array_type; ?>">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="avatar_modbtn">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let formAvatarID = null;
    $(document).ready(function() {
        $('#addAvatarModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const dossierId = $(event.relatedTarget).data('dossier-id');
            const form = document.getElementById('FormAvatar');

            if (dossierId) {
                form.dossier_id.value = dossierId;
                $('#dossier_id').attr('readonly', true);
                $('#dossier_id').addClass('bg-light');
                $('#dossier_id').css('pointerEvents', 'none');
            }

            if (dataId) {
                formAvatarID = dataId;
                $('#avatar_modtitle').text('Modifier le avatar');
                $('#avatar_modbtn').text('Modifier');
                try {
                    const response = await fetch(`./apis/avatars.routes.php?id=${dataId}`, {
                        headers: { 'Authorization': `Bearer ${token}` },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.name.value = result.data.name;
                    form.dossier_id.value = result.data.dossier_id;
                    form.description.value = result.data.description;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                }
            } else {
                formAvatarID = null;
                $('#avatar_modtitle').text('Ajouter un avatar');
                $('#avatar_modbtn').text('Ajouter');
            }
        });

        $('#addAvatarModal').on('hide.bs.modal', function() {
            $('#FormAvatar')[0].reset();
        });

        $('#FormAvatar').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);

            try {
                const response = await fetch("./apis/avatars.routes.php", {
                    headers: { 'Authorization': `Bearer ${token}` },
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données envoyées avec succès.');
                    $('#addAvatarModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données.');
            }
        });
    });
</script>