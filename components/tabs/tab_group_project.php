<div class="row">
    <div class="col-12">
        <!-- Titre ajouté ici -->
        <div class="d-flex justify-content-between align-items-center mb-3 px-3">
            <h4 class="my-1 fw-black fs-8">Liste des Projets du groupe </h4>
            <div class="my-1">
                <span class="badge bg-primary rounded-pill fs-9"><?php echo count($projets); ?> projet(s)</span>
            </div>
        </div>
        
        <div class="row bg-body-emphasis p-3 border-top g-2">
            <?php if (empty($projets)) { ?>
                <div class="text-center py-5 my-5" style="min-height: 300px;">
                    <div class="d-flex justify-content-center mb-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                            class="text-warning">
                            <path
                                d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                    <p class="text-muted">Créez votre premier projet pour commencer</p>
                </div>
            <?php } else { ?>
                <?php foreach ($projets as $projet):
                    $logoParts = explode("../", $projet['logo'] ?? '');

                    $tache_projet = new Tache($db);
                    $tache_projet->projet_id = $projet['id'];
                    $taches_projet = $tache_projet->readByProjet();
                    $taches_projet = array_filter($taches_projet, function ($tache) { return $tache['state'] == 'actif'; });

                    $totalTacheCount = count($taches_projet);
                    $finishedTacheCount = count(array_filter($taches_projet, function ($tache) {
                        return strtolower($tache['status']) === 'terminée';
                    }));
                    $progress = $totalTacheCount > 0 ? (round(($finishedTacheCount / $totalTacheCount), 2) * 100) : 0;
                    ?>
                    <div class="col-12 col-lg-6 col-xl-4 mb-4">
                        <div class="card border border-light h-100 hover-shadow-lg overflow-hidden">
                            <!-- Bandeau d'état -->
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge rounded-pill fs-11 bg-<?= $projet['state'] == 'actif' ? 'success' : 'danger'; ?>">
                                    <?= $projet['state'] == 'actif' ? 'Actif' : 'Archivé'; ?>
                                    <i class="fas fa-<?= $projet['state'] == 'actif' ? 'check-circle' : 'archive'; ?> ms-1 fs-10"></i>
                                </span>
                            </div>
                            
                            <div class="card-body p-3">
                                <!-- En-tête avec logo et titre -->
                                <div class="d-flex align-items-start mb-3 me-3">
                                    <?php if (!empty($projet['logo'])): ?>
                                        <img class="rounded-1 me-3 border border-light shadow-sm"
                                            src="<?php echo end($logoParts); ?>" alt="no-image"
                                            style="width: 60px; height: 60px; object-fit: contain;" />
                                    <?php else: ?>
                                        <div class="bg-light rounded-1 me-3 d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="far fa-image fs-4 text-body-tertiary"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <a href="project_view.php?id=<?= $projet['id'] ?>" class="text-decoration-none">
                                            <h4 class="mb-1 text-primary"><?= $projet['name'] ?></h4>
                                        </a>
                                        <div class="d-flex align-items-center">
                                            <small class="text-muted me-2"><?= $projet['code'] ?? "NA"; ?></small>
                                            <span class="badge bg-<?= $projet['status'] == 'En cours' ? 'warning' : ($projet['status'] == 'Terminé' ? 'success' : 'secondary'); ?> text-dark bg-opacity-10 border border-opacity-10 border-dark rounded-pill fs-10">
                                                <?= $projet['status'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations du projet -->
                                <div class="mb-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-user-shield text-muted me-2 fs-10"></i>
                                        <span class="text-body">Responsable :</span>
                                        <span class="fw-semibold ms-2"><?= $projet['structure_sigle'] ?></span>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-calendar-alt text-muted me-2 fs-10"></i>
                                        <span class="text-body">Période :</span>
                                        <span class="fw-semibold ms-2">
                                            <?= date('d/m/Y', strtotime($projet['start_date'])) ?> - <?= date('d/m/Y', strtotime($projet['end_date'])) ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Barre de progression -->
                                <div class="mb-1">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted fs-9">Progression</span>
                                        <span class="fw-semibold fs-9"><?= $progress ?>%</span>
                                    </div>
                                    <div class="progress bg-soft-warning" style="height: 6px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                            style="width: <?= $progress ?>%;" 
                                            aria-valuenow="<?= $progress ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
</div>