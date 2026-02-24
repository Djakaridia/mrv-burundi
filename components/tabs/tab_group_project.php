<?php
$conventions_par_projet = [];
$couts_par_tache = [];

if (!empty($projets)) {
    $convention = new Convention($db);
    $tache_cout = new TacheCout($db);
    $tache_couts = $tache_cout->read();

    foreach ($tache_couts as $cout) {
        $couts_par_tache[$cout['tache_id']][] = $cout;
    }

    foreach ($projets as $projet) {
        $convention->projet_id = $projet['id'];
        $conventions_par_projet[$projet['id']] = $convention->readByProjet();
    }
}
?>

<div class="row mx-0">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3 px-3">
            <h4 class="my-1 fw-black fs-8">
                Liste des Projets du groupe
            </h4>
            <div class="my-1">
                <span class="badge bg-primary rounded-pill fs-9 px-3 py-2">
                    <i class="fas fa-layer-group me-1"></i>
                    <?php echo count($projets); ?> projet(s)
                </span>
            </div>
        </div>

        <div class="row g-3">
            <?php if (empty($projets)) { ?>
                <div class="text-center py-5 my-5">
                    <div class="d-flex justify-content-center mb-4">
                        <div class="bg-light rounded-circle p-4">
                            <i class="fas fa-tasks fa-3x text-primary opacity-50"></i>
                        </div>
                    </div>
                    <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                    <p class="text-muted mb-4">Commencez par créer votre premier projet pour visualiser les données</p>
                    <a href="project_add.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Créer un projet
                    </a>
                </div>
            <?php } else { ?>
                <?php foreach ($projets as $projet):
                    $logoParts = explode("../", $projet['logo'] ?? '');
                    $conventions_projet = $conventions_par_projet[$projet['id']] ?? [];
                    $budget_conventions = array_sum(array_column($conventions_projet, 'montant'));

                    $tache_projet = new Tache($db);
                    $tache_projet->projet_id = $projet['id'];
                    $taches_projet = $tache_projet->readByProjet();
                    $taches_actives = array_filter($taches_projet, function ($tache) {
                        return $tache['state'] == 'actif';
                    });

                    $totalTacheCount = count($taches_actives);
                    $finishedTacheCount = count(array_filter($taches_actives, function ($tache) {
                        return strtolower($tache['status'] ?? '') === 'terminée';
                    }));

                    $taux_physique = $totalTacheCount > 0 ? round(($finishedTacheCount / $totalTacheCount) * 100, 1) : 0;
                    $montant_total_depense = 0;
                    foreach ($taches_actives as $tache) {
                        if (isset($couts_par_tache[$tache['id']])) {
                            $montant_total_depense += array_sum(array_column($couts_par_tache[$tache['id']], 'montant'));
                        }
                    }

                    $budget_reference = $budget_conventions > 0 ? $budget_conventions : ($projet['budget'] ?? 0);
                    $taux_financier = $budget_reference > 0 ? round(($montant_total_depense / $budget_reference) * 100, 1) : 0;
                    $budget_formate = ($projet['budget'] ?? 0) > 0 ? number_format($projet['budget'], 0, ',', ' ') . ' USD' : 'Non défini';
                    $budget_conventions_formate = $budget_conventions > 0 ? number_format($budget_conventions, 0, ',', ' ') . ' USD' : 'Aucune convention';
                    $montant_depense_formate = $montant_total_depense > 0 ? number_format($montant_total_depense, 0, ',', ' ') . ' USD' : '0 USD';
                ?>
                    <div class="col-12 col-lg-6 col-xl-4">
                        <div class="card card-float border border-primary-subtle h-100 overflow-hidden">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start mb-2 gap-2">
                                    <?php if (!empty($projet['logo'])): ?>
                                        <img class="rounded-2 border border-light shadow-sm object-fit-cover"
                                            src="<?php echo end($logoParts); ?>" alt="Logo projet"
                                            style="width: 60px; height: 60px; object-fit: cover;" />
                                    <?php else: ?>
                                        <div class="bg-light rounded-2 d-flex align-items-center justify-content-center border border-light"
                                            style="width: 60px; height: 60px;">
                                            <i class="fas fa-image fa-4x text-body-tertiary opacity-50"></i>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex gap-1">
                                                <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-secondary">
                                                    <?php foreach ($secteurs as $secteur) {
                                                        if ($secteur['id'] == $projet['secteur_id']) echo $secteur['name'];
                                                    } ?>
                                                </span>
                                                <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-<?= listClassAction()[$projet['action_type']]['badge'] ?> rounded-pill">
                                                    <?= htmlspecialchars(listTypeAction()[$projet['action_type']]) ?>
                                                </span>
                                            </div>

                                            <div class="d-flex gap-3 align-items-center justify-content-end">
                                                <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-<?= $projet['state'] == "actif" ? "success" : "secondary"; ?>">
                                                    <?= $projet['state'] ?? 'N/A'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <h5 title="<?= html_entity_decode($projet['name']) ?>" class="mb-0 text-primary fw-bold fs-8" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; word-break: break-word;">
                                            <?= html_entity_decode($projet['name']) ?>
                                        </h5>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-primary bg-opacity-10 rounded-1 px-1 me-2">
                                            <i class="fas fa-user-tie text-primary fs-10"></i>
                                        </div>
                                        <span class="text-muted small">Responsable :</span>
                                        <span class="fw-semibold ms-2 small"><?= $projet['structure_sigle'] ?? 'N/A' ?></span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-warning bg-opacity-10 rounded-1 px-1 me-2">
                                            <i class="fas fa-calendar-alt text-warning fs-10"></i>
                                        </div>
                                        <span class="text-muted small">Période :</span>
                                        <span class="fw-semibold ms-2 small">
                                            <?= date('d/m/Y', strtotime($projet['start_date'])) ?> -
                                            <?= date('d/m/Y', strtotime($projet['end_date'])) ?>
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-success bg-opacity-10 rounded-1 px-1 me-2">
                                            <i class="fas fa-coins text-success fs-10"></i>
                                        </div>
                                        <span class="text-muted small">Budget :</span>
                                        <span class="fw-bold ms-2 text-success small"><?= $budget_formate ?></span>
                                    </div>

                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bg-info bg-opacity-10 rounded-1 px-1 me-2">
                                            <i class="fas fa-hand-holding-usd text-info fs-10"></i>
                                        </div>
                                        <span class="text-muted small">Conventions :</span>
                                        <span class="fw-semibold ms-2 small">
                                            <?= count($conventions_projet) ?> convention(s) - <?= $budget_conventions_formate ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-light rounded-1 p-3">
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">
                                                <i class="fas fa-chart-line me-1 text-primary"></i>
                                                Exécution physique
                                            </span>
                                            <span class="fw-bold small <?= $taux_physique >= 75 ? 'text-success' : ($taux_physique >= 40 ? 'text-warning' : 'text-danger') ?>">
                                                <?= $taux_physique ?>%
                                            </span>
                                        </div>
                                        <div class="progress bg-soft-secondary" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $taux_physique >= 75 ? 'success' : ($taux_physique >= 40 ? 'warning' : 'danger') ?>"
                                                role="progressbar" style="width: <?= $taux_physique ?>%;" aria-valuenow="<?= $taux_physique ?>" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <span class="small text-muted"><?= $finishedTacheCount ?>/<?= $totalTacheCount ?> tâches</span>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="mb-0">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">
                                                <i class="fas fa-percent me-1 text-info"></i>
                                                Exécution financière
                                            </span>
                                            <span class="fw-bold small <?= $taux_financier >= 75 ? 'text-success' : ($taux_financier >= 40 ? 'text-warning' : 'text-danger') ?>">
                                                <?= $taux_financier ?>%
                                            </span>
                                        </div>
                                        <div class="progress bg-soft-secondary" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $taux_financier >= 75 ? 'success' : ($taux_financier >= 40 ? 'warning' : 'danger') ?>"
                                                role="progressbar" style="width: <?= $taux_financier ?>%;" aria-valuenow="<?= $taux_financier ?>" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <span class="small text-muted" title="Dépensé / Budget total">
                                                <?= $montant_depense_formate ?> / <?= $budget_conventions_formate ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent border-0 p-3 pt-0">
                                <div class="d-flex align-items-center gap-3">
                                    <button title="Voir" class="btn btn-sm btn-phoenix-info rounded-1 w-100" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>">
                                        <i class="fas fa-eye me-1"></i> Aperçu
                                    </button>

                                    <a href="project_view.php?id=<?= $projet['id'] ?>" class="btn btn-sm btn-phoenix-warning rounded-1 w-100">
                                        <i class="fas fa-list me-1"></i>Détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php } ?>
        </div>
    </div>
</div>