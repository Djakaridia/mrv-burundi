<div class="content px-0 py-0 pt-navbar">
    <div class="row g-0">
        <div class="col-12 col-xxl-12 px-0 pb-9">
            <div class="d-flex justify-content-between align-items-start">
                <h3 class="text-body-emphasis fw-bolder mb-2"><?php echo $group_curr['name']; ?></h3>

                <div class="btn-reveal-trigger gap-1">
                    <?php if (checkPermis($db, 'update')): ?>
                        <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                            data-bs-target="#addGroupeModal" data-id="<?php echo $group_curr['id']; ?>">
                            <span class="uil-pen fs-8"></span>
                        </button>
                    <?php endif; ?>

                    <?php if (checkPermis($db, 'update', 2)): ?>
                        <button title="<?php echo $group_curr['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>" class="btn btn-sm btn-phoenix-warning me-1 fs-10 px-2 py-1"
                            onclick="updateState(<?php echo $group_curr['id']; ?>, '<?php echo $group_curr['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $group_curr['state'] == 'actif' ? 'désactiver' : 'activer'; ?> ce groupe ?', 'groupes')">
                            <span class="uil-<?php echo $group_curr['state'] == 'actif' ? 'ban text-warning' : 'check-circle text-success'; ?> fs-8"></span>
                        </button>
                    <?php endif; ?>

                    <?php if (checkPermis($db, 'delete', 2)): ?>
                        <button title="Supprimer" class="btn btn-sm btn-phoenix-danger me-1 fs-10 px-2 py-1"
                            onclick="deleteData(<?php echo $group_curr['id']; ?>,'Voulez-vous vraiment supprimer ce groupe ?', 'groupes', 'redirect', 'groups.php')">
                            <span class="uil-trash-alt fs-8"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6 col-12">
                    <div class="card rounded-1 shadow-sm h-100">
                        <div class="card-header rounded-top-1 p-2 bg-light">
                            <h5 class="mb-0">Informations du groupe</h5>
                        </div>
                        <div class="card-body p-3">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 py-1">
                                    <i class="fs-10 fas fa-lock me-1 text-muted"></i>
                                    <small class="fw-bold">Code: </small><?php echo $group_curr['code']; ?>
                                </div>
                                <div class="list-group-item px-0 py-1">
                                    <i class="fs-10 fas fa-building me-1 text-muted"></i>
                                    <small class="fw-bold">Libellé: </small><?php echo $group_curr['name']; ?>
                                </div>
                                <div class="list-group-item px-0 py-1">
                                    <i class="fs-10 fas fa-calendar me-1 text-muted"></i>
                                    <small class="fw-bold">Date de création: </small><?php echo date('d/m/Y', strtotime($group_curr['created_at'])); ?>
                                </div>
                                <div class="list-group-item px-0 py-1">
                                    <i class="fs-10 fas fa-toggle-on me-1 text-muted"></i>
                                    <small class="fw-bold">Status: </small>
                                    <span class="badge badge-phoenix badge-phoenix-<?php echo $group_curr['state'] == 'actif' ? 'success' : 'danger'; ?>">
                                        <?php echo strtoupper($group_curr['state'] == 'actif' ? 'Actif' : 'Inactif'); ?>
                                        <span class="ms-1 uil-<?php echo $group_curr['state'] == 'actif' ? 'check-circle text-success' : 'ban text-danger'; ?> fs-10"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12">
                    <div class="card rounded-1 shadow-sm h-100">
                        <div class="card-header rounded-top-1 p-2 bg-light">
                            <h5 class="mb-0">Structure du groupe</h5>
                        </div>
                        <div class="card-body p-3">
                            <?php foreach ($structures as $structure) {
                                if ($structure['id'] == $group_curr['monitor']) {
                                    $logoStruc = explode("../", $structure['logo'] ?? ''); ?>
                                    <div class="row">
                                        <div class="col-lg-3 col-12">
                                            <div class="avatar avatar-xxl rounded-1 border border-white shadow-sm">
                                                <?php if ($structure['logo']) { ?>
                                                    <img class="rounded-2 p-1 object-fit-cover"
                                                        src="<?php echo end($logoStruc); ?>" alt="Logo" width="100" />
                                                <?php } else { ?>
                                                    <div class="avatar avatar-xxl rounded-1 border border-white shadow-sm">
                                                        <i class="fas fa-building fs-1 text-primary"></i>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="col-lg-9 col-12 list-group list-group-flush">
                                            <div class="list-group-item px-0 py-1">
                                                <i class="fs-10 fas fa-lock me-1 text-muted"></i>
                                                <small class="fw-bold">Code: </small><?php echo $structure['code']; ?>
                                            </div>
                                            <div class="list-group-item px-0 py-1">
                                                <i class="fs-10 fas fa-building me-1 text-muted"></i>
                                                <small class="fw-bold">Sigle: </small><?php echo $structure['sigle']; ?>
                                            </div>
                                            <div class="list-group-item px-0 py-1">
                                                <i class="fs-10 fas fa-envelope me-1 text-muted"></i>
                                                <small class="fw-bold">Email: </small><?php echo $structure['email']; ?>
                                            </div>
                                            <div class="list-group-item px-0 py-1">
                                                <i class="fs-10 fas fa-phone me-1 text-muted"></i>
                                                <small class="fw-bold">Contact: </small><?php echo $structure['phone']; ?>
                                            </div>
                                            <div class="list-group-item px-0 py-1">
                                                <i class="fs-10 fas fa-map-marker-alt me-1 text-muted"></i>
                                                <small class="fw-bold">Adresse: </small><?php echo $structure['address']; ?>
                                            </div>
                                        </div>
                                    </div>
                            <?php }
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="card rounded-1 shadow-sm h-100">
                        <div class="card-header rounded-top-1 p-2 bg-light">
                            <h5 class="mb-0">Description</h5>
                        </div>
                        <div class="card-body p-3">
                            <p class="mb-0"><?php echo $group_curr['description']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .avatar {
        transition: all 0.2s ease;
    }

    .avatar:hover {
        transform: scale(1.1);
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
</style>