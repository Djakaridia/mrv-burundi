<?php $array_type = ".csv, .xlsx, .xls, .ods, .odt"; ?>
<div class="modal fade" id="importRegisterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="importRegisterModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0">Importer des données</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <form action="" name="FormRegister" id="FormRegister" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="mb-1">
                                <label class="form-label">Année*</label>
                                <input class="form-control" type="number" min="2000" max="2100" name="annee" id="register_annee" value="<?= date('Y') ?>" placeholder="Entrer l'année" required />
                                <div class="invalid-feedback" id="register_annee_feedback"></div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="mb-1">
                                <label class="form-label">Inventaire*</label>
                                <select class="form-select" name="inventaire_id" id="register_inventaires" required>
                                    <option value="" disabled>Sélectionner un inventaire</option>
                                    <?php if ($inventaires ?? []) : ?>
                                        <?php foreach ($inventaires as $inventaire) : ?>
                                            <option value="<?= $inventaire['id'] ?>"><?= $inventaire['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label for="file_register" class="bg-white dark__bg-dark btn border border-dashed rounded-1 px-2 py-3 w-100">
                                <input type="file" name="file" id="file_register" accept="<?php echo $array_type ?>"
                                    class="form-control d-none w-100" required />
                                <input type="hidden" name="allow_files" id="allow_file_register" value="<?php echo $array_type; ?>">
                                <div class="text-center text-body-emphasis mb-2">
                                    <h5 class="mb-3"> <span class="fa-solid fa-upload me-2"></span> Télécharger un document</h5>
                                    <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Télécharger un document dans les formats suivants : <br> <?php echo $array_type ?></p>
                                </div>
                                <span id="file_register_name" class="fs-9 text-info lh-sm"></span>
                            </label>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 mt-3 px-3 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="register_modbtn">Importer</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#importRegisterModal').on('shown.bs.modal', async function(event) {
            const inventaireId = $(event.relatedTarget).data('inventory');
            const form = document.getElementById('FormRegister');
            form.inventaire_id = inventaireId || "";
        });

        $('#importRegisterModal').on('hide.bs.modal', function() {
            $('#FormRegister')[0].reset();
        });

        $('#file_register').on('change', function() {
            const fileName = this.files[0]?.name || '';
            $('#file_register_name').text(fileName);
        });

        $('#FormRegister').on('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const submitBtn = $('#register_modbtn');
            submitBtn.prop('disabled', true).text('Envoi en cours...');

            try {
                const response = await fetch("./apis/registers.routes.php?action=import", {
                    method: "POST",
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données envoyées avec succès.');
                    $('#importRegisterModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }

            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données.');
            } finally {
                submitBtn.prop('disabled', false).text('Importer');
            }
        });

    });
</script>