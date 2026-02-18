<?php
$task_planifiee = $task_en_cours = $task_en_attente = $task_terminee = $task_annulee = $task_autre = 0;
foreach ($taches_project as $tache) {
    if (strtolower($tache['status']) === 'planifie') {
        $task_planifiee++;
    } elseif (strtolower($tache['status']) === 'annule') {
        $task_annulee++;
    } elseif (strtolower($tache['status']) === 'autre') {
        $task_autre++;
    } elseif (strtolower($tache['status']) === 'en_cours') {
        $task_en_cours++;
    } elseif (strtolower($tache['status']) === 'en_attente') {
        $task_en_attente++;
    } elseif (strtolower($tache['status']) === 'realise') {
        $task_terminee++;
    }
}

$logoParts = explode("../", $project_curr['logo'] ?? '');
$task_total = $task_planifiee + $task_en_cours + $task_en_attente + $task_terminee + $task_annulee + $task_autre;
?>

<div class="content px-0 py-0 pt-navbar">
    <div class="row g-0">
        <div class="col-12 col-xxl-12 px-0 pb-9">
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <span class="badge badge-phoenix py-1 px-2 fs-10 rounded-pill badge-phoenix-<?php echo $project_curr['state'] == 'actif' ? 'success' : 'danger'; ?>">
                            <?php echo $project_curr['state'] == 'actif' ? 'Actif' : 'Archivé'; ?>
                            <span class="ms-1 uil <?php echo $project_curr['state'] == 'actif' ? 'uil-check-circle' : 'uil-archive-alt'; ?> fs-10"></span>
                        </span>

                        <span class="badge badge-phoenix py-1 px-2 fs-10 rounded-pill badge-phoenix-warning">
                            <?php echo listStatus()[$project_curr['status']]; ?>
                        </span>

                        <span class="badge badge-phoenix py-1 px-2 fs-10 rounded-pill badge-phoenix-info">
                            <?php echo $project_curr['action_type'] == 'adaptation' ? 'Adaptation' : 'Atténuation'; ?>
                        </span>
                    </div>

                    <div class="btn-reveal-trigger gap-2">
                        <?php if (checkPermis($db, 'update')) : ?>
                            <button title="Modifier" class="btn btn-sm rounded-1 btn-phoenix-info me-1 px-2 py-1"
                                data-bs-toggle="modal" data-bs-target="#addProjetModal"
                                data-id="<?php echo $project_curr['id']; ?>">
                                <span class="uil-pen fs-9"></span> Modifier
                            </button>
                        <?php endif; ?>

                        <?php if (checkPermis($db, 'update', 2)) : ?>
                            <button title="<?php echo $project_curr['state'] == 'actif' ? 'Archiver' : 'Désarchiver'; ?>"
                                class="btn btn-sm rounded-1 btn-phoenix-warning me-1 px-2 py-1"
                                onclick="updateState(<?php echo $project_curr['id']; ?>, '<?php echo $project_curr['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 
                                'Êtes-vous sûr de vouloir <?php echo $project_curr['state'] == 'actif' ? 'archiver' : 'désarchiver'; ?> ce projet ?', 'projets')">
                                <span class="uil-<?php echo $project_curr['state'] == 'actif' ? 'archive' : 'archive-alt'; ?> fs-9"></span>
                                <?php echo $project_curr['state'] == 'actif' ? 'Archiver' : 'Désarchiver'; ?>
                            </button>
                        <?php endif; ?>

                        <?php if (checkPermis($db, 'delete', 2)) : ?>
                            <button title="Supprimer" class="btn btn-sm rounded-1 btn-phoenix-danger me-1 px-2 py-1"
                                onclick="deleteData(<?php echo $project_curr['id']; ?>,'Voulez-vous vraiment supprimer ce projet ?', 'projets', 'redirect', 'projects.php')">
                                <span class="uil-trash-alt fs-9"></span> Supprimer
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <h5 class="text-body-emphasis fw-bolder my-2"><?php echo html_entity_decode($project_curr['name']); ?></h5>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card h-100 rounded-1">
                        <div class="card-body p-3 text-center">
                            <?php if (!empty($project_curr['logo'])) : ?>
                                <img class="img-fluid rounded" src="<?php echo end($logoParts); ?>" alt="Logo projet" style="max-height: 200px; object-fit: contain;">
                            <?php else : ?>
                                <i class="far fa-image text-body-tertiary" style="width: 100%; font-size: 200px"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="card card-float h-100 rounded-1 border-start border-success-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="fa-regular fa-credit-card text-success fs-3"></span>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="text-muted mb-2">Budget (USD)</h6>
                                            <h5 class="mb-0 fw-bold"><?php echo number_format($project_curr['budget'], 0, ',', ' '); ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="card card-float h-100 rounded-1 border-start border-info-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="fa-regular fas fa-city text-info fs-3"></span>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="text-muted mb-2">Responsable</h6>
                                            <h5 class="mb-0 fw-bold text-truncate"><?php echo array_column($structures, 'sigle', 'id')[$project_curr['structure_id']] ?></h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-4">
                            <div class="card card-float h-100 rounded-1 border-start border-secondary-subtle">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="far fa-calendar text-secondary fs-3"></span>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="text-muted mb-2">Période</h6>
                                            <h5 class="mb-0 fw-bold text-truncate">
                                                <?php echo date('d/m/Y', strtotime($project_curr['start_date'])); ?> -
                                                <?php echo date('d/m/Y', strtotime($project_curr['end_date'])); ?>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card rounded-1">
                        <div class="card-body p-0">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#tabProjectobjectif" type="button" role="tab">
                                        <i class="fas fa-bullseye me-2"></i>Objectifs
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#tabProjetdescription" type="button" role="tab">
                                        <i class="fas fa-align-left me-2"></i>Description
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content mt-3 p-3">
                                <div class="tab-pane fade show active" id="tabProjectobjectif" role="tabpanel">
                                    <p class="text-body-secondary mb-0"><?php echo nl2br($project_curr['objectif']); ?></p>
                                </div>
                                <div class="tab-pane fade" id="tabProjetdescription" role="tabpanel">
                                    <p class="text-body-secondary mb-0"><?php echo nl2br($project_curr['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-1 mb-3">
                <div class="card-header rounded-0 px-3 py-2 bg-light overflow-hidden">
                    <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>Bilan des activités (<?php echo count($taches_project) ?? "0"; ?>)</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-4">
                                    <div class="bg-primary bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-primary mb-3">Planifiées</span>
                                        <h3 class="mb-0"><?php echo $task_planifiee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="bg-success bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-success mb-3">En cours</span>
                                        <h3 class="mb-0"><?php echo $task_en_cours; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="bg-warning bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-warning mb-3">En attente</span>
                                        <h3 class="mb-0"><?php echo $task_en_attente; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="bg-info bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-info mb-3">Terminées</span>
                                        <h3 class="mb-0"><?php echo $task_terminee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="bg-danger bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-danger mb-3">Annulées</span>
                                        <h3 class="mb-0"><?php echo $task_annulee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="bg-secondary bg-opacity-10 p-3 rounded-1 text-center">
                                        <span class="badge bg-secondary mb-3">Autres</span>
                                        <h3 class="mb-0"><?php echo $task_autre; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card chart-pie-container position-relative d-flex flex-center mb-sm-4 mb-xl-0 mt-sm-7 mt-lg-4 mt-xl-0">
                                <div class="chart-pie-source" style="min-height: 200px; width: 100%"></div>
                                <table class="d-none" data-source="data-source">
                                    <thead class="bg-primary-subtle">
                                        <tr>
                                            <th data-color="primary">Activités Planifiées</th>
                                            <th data-color="success">Activités En Cours</th>
                                            <th data-color="warning">Activités En Attente</th>
                                            <th data-color="info-light">Activités Terminées</th>
                                            <th data-color="danger">Activités Annulées</th>
                                            <th data-color="secondary-light">Activités Autres</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?= $task_planifiee ?></td>
                                            <td><?= $task_en_cours ?></td>
                                            <td><?= $task_en_attente ?></td>
                                            <td><?= $task_terminee ?></td>
                                            <td><?= $task_annulee ?></td>
                                            <td><?= $task_autre ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="d-none" data-title="data-title">Activités par réalisation</div>
                                <div class="position-absolute rounded-circle bg-primary-subtle top-50 start-50 translate-middle d-flex flex-center" style="height: 100px; width: 100px;">
                                    <h3 class="mb-0 text-primary-dark fw-bolder" data-label="data-label"></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-header bg-light py-2 rounded-0 overflow-hidden">
                            <h6 class="mb-0"><i class="fas fa-industry me-2"></i>Secteurs d'activité <span class="badge bg-primary"><?php echo count($secteurs_project); ?></span></h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($secteurs_project as $secteur) { ?>
                                    <a href="sectors.php?id=<?php echo $secteur['id']; ?>" class="text-decoration-none">
                                        <span class="badge bg-primary-subtle text-primary p-2"><?php echo $secteur['name']; ?></span>
                                    </a>
                                <?php } ?>
                                <?php if (empty($secteurs_project)): ?>
                                    <p class="text-muted mb-0">Aucun secteur défini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Groupes de travail -->
                <div class="col-md-6 col-lg-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-header bg-light py-2 rounded-0 overflow-hidden">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Groupes de travail <span class="badge bg-primary"><?php echo count($groupes_travail_project); ?></span></h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($groupes_travail_project as $groupe) { ?>
                                    <a href="group_view.php?id=<?php echo $groupe['id']; ?>" class="text-decoration-none">
                                        <span class="badge bg-info-subtle text-info p-2"><?php echo $groupe['name']; ?></span>
                                    </a>
                                <?php } ?>
                                <?php if (empty($groupes_travail_project)): ?>
                                    <p class="text-muted mb-0">Aucun groupe défini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programmes -->
                <div class="col-md-6 col-lg-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-header bg-light py-2 rounded-0 overflow-hidden">
                            <h6 class="mb-0"><i class="fas fa-folder me-2"></i>Programmes <span class="badge bg-primary"><?php echo count($programmes_project); ?></span></h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($programmes_project as $programme) { ?>
                                    <span class="badge bg-success-subtle text-success p-2"><?php echo $programme['name']; ?></span>
                                <?php } ?>
                                <?php if (empty($programmes_project)): ?>
                                    <p class="text-muted mb-0">Aucun programme défini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gaz concernés -->
                <div class="col-md-6 col-lg-3">
                    <div class="card rounded-1 h-100">
                        <div class="card-header bg-light py-2 rounded-0 overflow-hidden">
                            <h6 class="mb-0"><i class="fas fa-smog me-2"></i>Gaz concernés <span class="badge bg-primary"><?php echo count($projet_gaz); ?></span></h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($projet_gaz as $gaz) { ?>
                                    <span class="badge bg-warning-subtle text-warning p-2"><?php echo $gaz; ?></span>
                                <?php } ?>
                                <?php if (empty($projet_gaz)): ?>
                                    <p class="text-muted mb-0">Aucun gaz défini</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-1 mt-3">
                <div class="card-header bg-light py-2 rounded-0 overflow-hidden">
                    <h6 class="mb-0"><i class="far fa-calendar-alt me-2"></i>Dates importantes</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Date de début</small>
                            <strong><?php echo date('d/m/Y', strtotime($project_curr['start_date'])); ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Date de clôture</small>
                            <strong><?php echo date('d/m/Y', strtotime($project_curr['end_date'])); ?></strong>
                        </div>
                        <?php if (!empty($project_curr['signature_date'])): ?>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Date de signature</small>
                                <strong><?php echo date('d/m/Y', strtotime($project_curr['signature_date'])); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($project_curr['miparcours_date'])): ?>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Date mi-parcours</small>
                                <strong><?php echo date('d/m/Y', strtotime($project_curr['miparcours_date'])); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>