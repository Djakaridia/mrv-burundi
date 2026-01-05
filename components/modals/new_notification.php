<!-- modal -->
<div class="modal fade" id="addNotificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addNotificationModal" aria-hidden="true">
    <div class="modal-dialog modal-lg  modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0">Ajouter une notification </h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
            </div>
            <div class="modal-body px-0">
                <div class="email-content">
                    <div class="">
                        <form class="d-flex flex-column h-100">
                            <div class="row g-3 mb-2">
                                <div class="col-6">
                                    <input class="form-control" type="email" placeholder="To">
                                </div>
                                <div class="col-6">
                                    <input class="form-control" type="email" placeholder="CC">
                                </div>
                                <div class="col-12">
                                    <input class="form-control" type="text" placeholder="Subject">
                                </div>
                            </div>
                            <div class="row g-3 mb-2">
                                <div class="col-12">
                                    <label for="notificationDescription" class="fs-9 fw-semibold">Message</label>
                                    <textarea class="tinymce" name="description" data-tinymce="{}" id="notificationDescription"></textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex">
                                    <label class="btn btn-link py-0 px-2 text-body fs-9" for="emailAttachment">
                                        <span class="fa-solid fa-paperclip"></span>
                                    </label>
                                    <input class="d-none" id="emailAttachment" type="file">
                                    <label class="btn btn-link py-0 px-2 text-body fs-9" for="emailPhotos">
                                        <span class="fa-solid fa-image"></span>
                                    </label>
                                    <input class="d-none" id="emailPhotos" type="file" accept="image/*">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
                <button class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                <button class="btn btn-primary btn-sm px-3 my-0">Envoyer</button>
            </div>
        </div>
    </div>

</div>