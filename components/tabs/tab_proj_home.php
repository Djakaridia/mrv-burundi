<?php
$task_planifiee = $task_en_cours = $task_en_attente = $task_terminee = $task_annulee = $task_autre = 0;

foreach ($taches_project as $tache) {
    if (strtolower($tache['status']) === 'planifiée') {
        $task_planifiee++;
    } elseif (strtolower($tache['status']) === 'annulée') {
        $task_annulee++;
    } elseif (strtolower($tache['status']) === 'autre') {
        $task_autre++;
    } elseif (strtolower($tache['status']) === 'en cours') {
        $task_en_cours++;
    } elseif (strtolower($tache['status']) === 'en attente') {
        $task_en_attente++;
    } elseif (strtolower($tache['status']) === 'terminée') {
        $task_terminee++;
    }
}

$task_total = $task_planifiee + $task_en_cours + $task_en_attente + $task_terminee + $task_annulee + $task_autre;
$progress = $task_total > 0 ? (round(($task_terminee / $task_total), 2) * 100) : 0;
$logoParts = explode("../", $project_curr['logo'] ?? '');
?>

<div class="content px-0 py-0 pt-navbar">
    <div class="row g-0">
        <div class="col-12 col-xxl-8 px-0 pb-9">
            <h4 class="text-body-emphasis fw-bolder mb-2"><?php echo html_entity_decode($project_curr['name']); ?></h4>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge badge-phoenix badge-phoenix-<?php echo $project_curr['state'] == 'actif' ? 'success' : 'danger'; ?>">
                    <?php echo $project_curr['state'] == 'actif' ? 'Actif' : 'Archivé'; ?>
                    <span class="ms-1 uil <?php echo $project_curr['state'] == 'actif' ? 'uil-check-circle' : 'uil-archive-alt'; ?> fs-10"></span>
                </span>

                <div class="btn-reveal-trigger gap-1">
                    <?php if (checkPermis($db, 'update')) : ?>
                        <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal" data-bs-target="#addProjetModal" data-id="<?php echo $project_curr['id']; ?>"><span class="uil-pen fs-8"></span></button>
                    <?php endif; ?>

                    <?php if (checkPermis($db, 'update', 2)) : ?>
                        <button title="<?php echo $project_curr['state'] == 'actif' ? 'Archiver' : 'Désarchiver'; ?>" class="btn btn-sm btn-phoenix-warning me-1 fs-10 px-2 py-1" onclick="updateState(<?php echo $project_curr['id']; ?>, '<?php echo $project_curr['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $project_curr['state'] == 'actif' ? 'archiver' : 'désarchiver'; ?> ce projet ?', 'projets')"><span class="uil-<?php echo $project_curr['state'] == 'actif' ? 'archive' : 'archive-alt'; ?> fs-8"></span></button>
                    <?php endif; ?>

                    <?php if (checkPermis($db, 'delete', 2)) : ?>
                        <button title="Supprimer" class="btn btn-sm btn-phoenix-danger me-1 fs-10 px-2 py-1" onclick="deleteData(<?php echo $project_curr['id']; ?>,'Voulez-vous vraiment supprimer ce projet ?', 'projets', 'redirect', 'projects.php')"><span class="uil-trash-alt fs-8"></span></button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-3 mb-3 mt-1">
                <div class="col-12 col-xl-5 col-xxl-5">
                    <div class="mb-4">
                        <div class="row gx-0 gx-sm-7">
                            <div class="col-12">
                                <table class="lh-sm mb-3 mb-sm-0 mb-xl-3">
                                    <tbody>
                                        <tr>
                                            <td class="align-top py-1">
                                                <div class="d-flex">
                                                    <span class="fa-solid fas fa-chalkboard me-2 text-body-tertiary fs-9"></span>
                                                    <h5 class="text-body">Code</h5>
                                                </div>
                                            </td>
                                            <td class="ps-1 py-1">
                                                <span class="fw-semibold d-block lh-sm">
                                                    <?php echo $project_curr['code'] ?? "NA"; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="align-top py-1">
                                                <div class="d-flex">
                                                    <span class="fa-regular fa-credit-card me-2 text-body-tertiary fs-9"></span>
                                                    <h5 class="text-body mb-0 text-nowrap">Budget : </h5>
                                                </div>
                                            </td>
                                            <td class="ps-1 py-1 text-body-highlight">
                                                <span class="fw-semibold d-block lh-sm"><?php echo number_format($project_curr['budget'], 0, ',', ' '); ?></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="col-12">
                                <table class="lh-sm">
                                    <tbody>
                                        <tr>
                                            <td class="align-top py-1 text-body text-nowrap fw-bold"><span class="far fa-calendar me-2 text-body-tertiary fs-9"></span> Début : </td>
                                            <td class="text-body-tertiary text-opacity-85 fw-semibold ps-3"><?php echo date('Y-m-d', strtotime($project_curr['start_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="align-top py-1 text-body text-nowrap fw-bold"><span class="far fa-calendar me-2 text-body-tertiary fs-9"></span> Cloture :</td>
                                            <td class="text-body-tertiary text-opacity-85 fw-semibold ps-3"><?php echo date('Y-m-d', strtotime($project_curr['end_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="align-top py-1 text-body text-nowrap fw-bold"><span class="fas fa-percentage me-2 text-body-tertiary fs-9"></span> Progression :</td>
                                            <td class="text-warning fw-semibold ps-3"><?php echo $progress; ?>%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <span class="fa-solid fa-list-check me-2 text-body-tertiary fs-9"></span>
                        <h5 class="text-body-emphasis mb-0 me-2"><?php echo count($taches_project) ?? "0"; ?> <span class="text-body fw-normal ms-2">Activités</span></h5>
                    </div>
                </div>

                <div class="col-12 col-xl-7 col-xxl-7">
                    <div class="row flex-between-center">
                        <div class="col-sm-7 col-md-8 col-xxl-8 bg-warning-lighter p-3 rounded-1">
                            <h4 class="text-body-emphasis">Bilan des activités au fil du temps</h4>
                            <p class="text-body-tertiary">Activités par progression au fil du temps</p>

                            <div class="row g-0">
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100 border-1 border-bottom border-end border-translucent">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-primary" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">Planifée</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_planifiee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100 border-1 border-bottom border-end-md-0 border-end-xl border-translucent">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-success" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">En cours</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_en_cours; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100 border-1 border-bottom border-end border-end-md border-end-xl-0 border-translucent">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-warning" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">En attente</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_en_attente; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100 border-1 border-end-xl border-bottom border-bottom-xl-0 border-translucent">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-info-light" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">Terminée</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_terminee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100 border-1 border-end border-translucent">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-danger" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">Annulée</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_annulee; ?></h3>
                                    </div>
                                </div>
                                <div class="col-6 col-xl-4">
                                    <div class="d-flex flex-column flex-center align-items-sm-start flex-md-row justify-content-md-between flex-xxl-column p-3 ps-sm-3 ps-md-4 p-md-3 h-100">
                                        <div class="d-flex align-items-center mb-1"><span class="fa-solid fa-square fs-11 me-2 text-secondary-light" data-fa-transform="up-2"></span><span class="mb-0 fs-9 text-body">Autres</span></div>
                                        <h3 class="fw-semibold ms-xl-3 ms-xxl-0 pe-md-2 pe-xxl-0 mb-0 mb-sm-3"><?php echo $task_autre; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-5 col-md-4 col-xxl-4 my-3 my-sm-0">
                            <div class="chart-pie-container position-relative d-flex flex-center mb-sm-4 mb-xl-0 mt-sm-7 mt-lg-4 mt-xl-0">
                                <div class="chart-pie-source" style="min-height: 245px; width: 100%"></div>
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

                <div class="col-12 col-sm-3 col-lg-3 col-xl-3 col-xxl-3 d-none d-lg-block">
                    <?php if (!empty($project_curr['logo'])) : ?>
                        <img class="rounded-1 w-100 border border-light shadow-sm" src="<?php echo end($logoParts); ?>"
                            alt="no-image" style="min-height: 65px; object-fit: contain; object-position: center;" />
                    <?php else : ?>
                        <i class="far fa-image text-body-tertiary" style="width: 100%; font-size:200px"></i>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-sm-9 col-lg-9 col-xl-9 col-xxl-9">
                    <div class="d-flex flex-column border-top border-top-light p-2 mb-1">
                        <h5 class="text-body-emphasis mb-2">Acteurs <span class="text-muted fs-9">(<?php echo count($structures_project); ?>)</span></h5>
                        <div class="d-flex gap-2">
                            <?php foreach ($structures_project as $structure) { 
                                $logoStruc = explode("../", $structure['logo'] ?? '');
                                ?>
                                <a title="<?= $structure['sigle'] ?>" class="dropdown-toggle dropdown-caret-none d-inline-block" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <div class="avatar avatar-l rounded-1 border border-light shadow-sm">
                                        <div class="avatar avatar-l me-1">
                                            <?php if ($structure['logo']) { ?>
                                                <img class="rounded-1 p-1 object-fit-contain" src="<?php echo end($logoStruc); ?>" alt="Logo" width="45" />
                                            <?php } else { ?>
                                                <span class="avatar avatar-l d-flex justify-content-center align-items-center rounded-1">
                                                    <i class="fas fa-building fs-7"></i>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </a>
                                <div class="dropdown-menu avatar-dropdown-menu p-0 overflow-hidden" style="width: 320px;">
                                    <div class="position-relative">
                                        <div class="row px-3 my-3 g-0">
                                            <div class="col-3">
                                                <?php if ($structure['logo']) { ?>
                                                    <img class="rounded-sm border border-light-subtle object-fit-contain" src="<?php echo end($logoStruc); ?>" alt="" width="60" />
                                                <?php } else { ?>
                                                    <span class="avatar avatar-xl d-flex justify-content-center align-items-center rounded-sm border border-light">
                                                        <i class="fas fa-building fs-5 text-primary"></i>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                            <div class="col-9">
                                                <div>Code : <span class="fw-semibold fs-9"><?php echo $structure['code']; ?></span></div>
                                                <div>Sigle : <span class="fw-semibold fs-9"><?php echo $structure['sigle']; ?></span></div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>Email : <span class="fw-semibold fs-9"><?php echo $structure['email']; ?></span></span>
                                                    <a href="javascript:void(0);" onclick="navigator.clipboard.writeText('<?php echo $structure['email']; ?>')"><span class="far fa-copy fs-9"></span></a>
                                                </div>
                                                <div>Tel : <span class="fw-semibold fs-9"><?php echo $structure['phone']; ?></span></div>
                                                <div>Adresse : <span class="fw-semibold fs-9"><?php echo $structure['address']; ?></span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="d-flex flex-column border-top border-top-light p-2 mb-1">
                        <h5 class="text-body-emphasis mb-2">Secteurs d'activité <span class="text-muted fs-9">(<?php echo count($secteurs_project); ?>)</span></h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($secteurs_project as $secteur) { ?>
                                <a href="sectors.php?id=<?php echo $secteur['id']; ?>">
                                    <span class="badge badge-phoenix badge-phoenix-primary"><?php echo $secteur['name']; ?></span>
                                </a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="d-flex flex-column border-top border-top-light p-2 mb-1">
                        <h5 class="text-body-emphasis mb-2">Groupes de travail <span class="text-muted fs-9">(<?php echo count($groupes_travail_project); ?>)</span></h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($groupes_travail_project as $groupe) { ?>
                                <a href="group_view.php?id=<?php echo $groupe['id']; ?>"><span class="badge badge-phoenix badge-phoenix-primary">
                                    <?php echo $groupe['name']; ?>
                                </span></a>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="d-flex flex-column border-top border-top-light p-2 mb-1">
                        <h5 class="text-body-emphasis mb-2">Programmes <span class="text-muted fs-9">(<?php echo count($programmes_project); ?>)</span></h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($programmes_project as $programme) { ?>
                                <span class="badge badge-phoenix badge-phoenix-primary"><?php echo $programme['name']; ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="d-flex flex-column border-top border-top-light p-2 mb-1">
                        <h5 class="text-body-emphasis mb-2">Type de gaz <span class="text-muted fs-9">(<?php echo count($projet_gaz); ?>)</span></h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($projet_gaz as $gaz) { ?>
                                <span class="badge badge-phoenix badge-phoenix-primary"><?php echo $gaz; ?></span>
                            <?php } ?>
                        </div>
                    </div> 
                </div>
            </div>

            <div class="d-flex flex-column border-top border-top-light p-3 mb-3">
                <h4 class="text-body-emphasis mb-1">Objectifs</h4>
                <p class="text-body-secondary mb-3"><?php echo $project_curr['objectif']; ?></p>
            </div>

            <div class="d-flex flex-column border-top border-top-light p-3 mb-3">
                <h4 class="text-body-emphasis mb-1">Description</h4>
                <p class="text-body-secondary mb-3"><?php echo $project_curr['description']; ?></p>
            </div>
        </div>
    </div>
</div>