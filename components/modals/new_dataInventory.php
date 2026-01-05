<?php $array_type = ".csv, .xlsx, .xls, .ods, .odt"; ?>
<div class="modal fade" id="addDataInventoryModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addInventoryModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="inventory_modtitle">Ajouter un inventaire</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <form action="" name="FormDataInventory" id="FormDataInventory" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-12 mt-1">
                            <div class="card-title text-center">Importation des fichiers </div>
                            <div class="text-center">(<?php echo $array_type ?>)</div>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <label for="file_inventory" class="bg-white dark__bg-dark btn border border-dashed rounded-1 px-2 py-3 w-100">
                                <input type="file" name="file" id="file_inventory" accept="<?php echo $array_type ?>"
                                    class="form-control d-none w-100" required />
                                <input type="hidden" name="allow_files" id="allow_file_inventory" value="<?php echo $array_type; ?>">
                                <div class="text-center text-body-emphasis mb-2">
                                    <h5 class="mb-3"> <span class="fa-solid fa-upload me-2"></span> Télécharger un document</h5>
                                    <p class="mb-0 fs-9 text-body-tertiary text-opacity-85 lh-sm">Télécharger un document dans les formats suivants : <br> <?php echo $array_type ?></p>
                                </div>
                                <span id="file_inventory_name" class="fs-9 text-info lh-sm"></span>
                            </label>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 mt-3 px-3 pb-0">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="inventoryData_modbtn">Ajouter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let formInventoryAnnee = null;
    $(document).ready(function() {
        $('#addDataInventoryModal').on('shown.bs.modal', async function(event) {
            const dataAnnee = $(event.relatedTarget).data('annee');
            const form = document.getElementById('FormDataInventory');
            formInventoryAnnee = dataAnnee; 

            if(!dataAnnee){
                errorAction('Veuillez selectionner une année');
                $('#addDataInventoryModal').modal('hide');
            }
        });

        $('#addDataInventoryModal').on('hide.bs.modal', function() {
            $('#FormDataInventory')[0].reset();
        });

        $('#file_inventory').on('change', function() {
            const fileName = this.files[0].name;
            $('#file_inventory_name').text(fileName);
        });

        $('#FormDataInventory').on('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $('#inventoryData_modbtn');
            submitBtn.prop('disabled', true);
            submitBtn.text('Envoi en cours...');

            try {
                const response = await fetch("./apis/inventories.routes.php?action=data&annee=" + formInventoryAnnee, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    method: "POST",
                    body: formData
                });

                const result = await response.json();
                if (result.status === 'success') {
                    successAction('Données envoyées avec succès.');
                    $('#addDataInventoryModal').modal('hide');
                }
            } catch (error) {
                console.log(error);
                errorAction('Erreur lors de l\'envoi des données.');
            } finally {
                submitBtn.prop('disabled', false);
                submitBtn.text('Enregistrer');
            }
        });
    });
</script>