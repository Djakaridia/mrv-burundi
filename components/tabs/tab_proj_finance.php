<div class="mb-9 mt-2">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card rounded-1 bg-primary-subtle border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-hand-holding-dollar fs-3 text-primary"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total financements</h6>
                            <?php $total_financements = array_sum(array_column($conventions_project, 'montant')) ?>
                            <h4 class="mb-0"><?php echo number_format($total_financements, 0, ',', ' '); ?> USD</h4>
                            <small class="text-primary"><?php echo count($conventions_project); ?> conventions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card rounded-1 bg-success-subtle border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-circle-check fs-3 text-success"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Montant décaissé</h6>
                            <?php
                            $total_decaisse = 0;
                            foreach ($taches_project as $tache) {
                                if (isset($grouped_tache_couts[$tache['id']])) {
                                    $total_decaisse += array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant'));
                                }
                            }
                            $taux_execution = $total_financements > 0 ? round(($total_decaisse / $total_financements) * 100, 1) : 0;
                            ?>
                            <h4 class="mb-0"><?php echo number_format($total_decaisse, 0, ',', ' '); ?> USD</h4>
                            <small class="text-success"><?php echo $taux_execution; ?>% du total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card rounded-1 bg-warning-subtle border-0">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-clock fs-3 text-warning"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Solde disponible</h6>
                            <?php $solde = $total_financements - $total_decaisse; ?>
                            <h4 class="mb-0"><?php echo number_format($solde, 0, ',', ' '); ?> USD</h4>
                            <small class="text-warning">Reste à décaisser</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">
                <i class="fas fa-file-contract me-2 text-primary"></i>
                Sources de financement
            </h4>
        </div>

        <div class="col-auto">
            <?php if (checkPermis($db, 'create')) : ?>
                <button title="Ajouter une convention" class="btn btn-subtle-primary btn-sm" id="addBtn"
                    data-bs-toggle="modal" data-bs-target="#addConvenModal" data-projet="<?= $project_curr['id'] ?>" data-action="<?= $project_curr['action_type'] ?>"
                    aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                    <i class="fas fa-plus me-1"></i> Nouvelle convention
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative mb-3">
        <div class="table-responsive p-1">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="conventions-table">
                <thead class="bg-primary-subtle">
                    <tr>
                        <th class="sort align-middle">Code</th>
                        <th class="sort align-middle">Convention</th>
                        <th class="sort align-middle">Bailleur</th>
                        <th class="sort align-middle text-end">Montant (USD)</th>
                        <th class="sort align-middle">Type de soutien</th>
                        <th class="sort align-middle">Date d'accord</th>
                        <th class="sort align-middle text-center">Statut</th>
                        <th class="sort align-middle text-center" style="min-width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                    <?php if (!empty($conventions_project)): ?>
                        <?php foreach ($conventions_project as $convention):
                            $decaisse_convention = 0;
                            foreach ($taches_project as $tache) {
                                if (isset($grouped_tache_couts[$tache['id']])) {
                                    foreach ($grouped_tache_couts[$tache['id']] as $cout) {
                                        if (isset($cout['convention_id']) && $cout['convention_id'] == $convention['id']) {
                                            $decaisse_convention += floatval($cout['montant']);
                                        }
                                    }
                                }
                            }

                            $now = time();
                            $date_fin = strtotime($convention['date_fin'] ?? '');
                            $date_debut = strtotime($convention['date_debut'] ?? '');
                            if ($date_fin && $now > $date_fin) {
                                $statut = 'expiree';
                                $statut_label = 'Expirée';
                                $statut_class = 'danger';
                            } elseif ($date_debut && $now < $date_debut) {
                                $statut = 'future';
                                $statut_label = 'À venir';
                                $statut_class = 'info';
                            } else {
                                $statut = 'active';
                                $statut_label = 'Active';
                                $statut_class = 'success';
                            }

                            $partenaire_nom = array_column($partenaires, 'description', 'id')[$convention['partenaire_id']] ?? 'Non défini';
                        ?>
                            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                <td class="align-middle">
                                    <span class="fw-semibold"><?php echo htmlspecialchars($convention['code'] ?? ''); ?></span>
                                </td>
                                <td class="align-middle">
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                        title="<?php echo htmlspecialchars($convention['name'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($convention['name'] ?? ''); ?>
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        <span class="fa-regular fa-building me-2 text-body-tertiary"></span>
                                        <?php echo htmlspecialchars($partenaire_nom); ?>
                                    </div>
                                </td>
                                <td class="align-middle text-end">
                                    <span class="badge bg-info-subtle text-info p-2 fs-9 fw-semibold">
                                        <?php echo number_format($convention['montant'] ?? 0, 0, ',', ' '); ?> USD
                                    </span>
                                </td>

                                <td class="align-middle">
                                    <?php echo listTypeAction()[$convention['action_type']] ?? "N/A" ?>
                                </td>
                                <td class="align-middle">
                                    <?php echo !empty($convention['date_accord']) ? date('d/m/Y', strtotime($convention['date_accord'])) : '-'; ?>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="badge badge-phoenix rounded-pill px-2 py-1 badge-phoenix-<?php echo $statut_class; ?>">
                                        <?php echo $statut_label; ?>
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Décaissé: <?php echo number_format($decaisse_convention, 0, ',', ' '); ?> USD
                                    </small>
                                </td>
                                <td class="align-middle text-center">
                                    <?php if (checkPermis($db, 'update')) : ?>
                                        <button title="Modifier" class="btn btn-sm btn-phoenix-info p-2 mx-1"
                                            data-bs-toggle="modal" data-bs-target="#addConvenModal"
                                            data-id="<?php echo $convention['id']; ?>">
                                            <span class="fa-regular fa-pen-to-square"></span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (checkPermis($db, 'delete')) : ?>
                                        <button title="Supprimer" class="btn btn-sm btn-phoenix-danger p-2 mx-1" type="button"
                                            onclick="deleteData(<?php echo $convention['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cette convention ?', 'conventions')">
                                            <span class="fa-regular fa-trash-can"></span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fa-regular fa-file-lines fa-3x text-muted mb-3"></span>
                                    <p class="text-muted mb-2">Aucune source de financement enregistrée</p>
                                    <?php if (checkPermis($db, 'create')) : ?>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addConvenModal">
                                            <i class="fas fa-plus me-1"></i>Ajouter une convention
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($conventions_project)): ?>
                    <tfoot class="bg-light">
                        <tr class="fw-bold">
                            <th colspan="3" class="text-end text-uppercase">Financement total</th>
                            <th class="text-end"><?php echo number_format($total_financements, 0, ',', ' '); ?> USD</th>
                            <th colspan="5"></th>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center mt-3">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">
                <i class="fas fa-chart-pie me-2 text-success"></i>
                Répartition des décaissements par activité
            </h4>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card rounded-1 border shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Détail des activités</h6>
                    <span class="badge bg-primary-subtle text-primary"><?php echo count($taches_project); ?> activités</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered fs-9 table-hover mb-0" id="activites-finance-table">
                            <thead class="bg-primary-subtle">
                                <tr>
                                    <th class="align-middle" width="5%">Code</th>
                                    <th class="align-middle" width="40%">Activité</th>
                                    <th class="align-middle text-end" width="20%">Montant prévu</th>
                                    <th class="align-middle text-end" width="20%" class="text-nowrap">Montant décaissé</th>
                                    <th class="align-middle text-center" width="20%">Taux exécution</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <?php
                                $total_prev = 0;
                                $total_decaisse_activites = 0;
                                foreach ($taches_project as $tache):
                                    $montant_prev = isset($grouped_tache_couts[$tache['id']]) ? array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant')) : 0;
                                    $total_prev += $montant_prev;
                                    $montant_decaisse = $montant_prev;
                                    $total_decaisse_activites += $montant_decaisse;
                                    $taux_exec = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td class="align-middle">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($tache['code'] ?? ''); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-truncate d-inline-block" style="max-width: 300px;"
                                                title="<?php echo htmlspecialchars($tache['name'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($tache['name'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <?php if ($montant_prev > 0): ?>
                                                <span class="fw-semibold"><?php echo number_format($montant_prev, 0, ',', ' '); ?> USD</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-end">
                                            <?php if ($montant_decaisse > 0): ?>
                                                <span class="text-success fw-semibold"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?> USD</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($montant_prev > 0): ?>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress w-100" style="height: 6px;">
                                                        <div class="progress-bar bg-<?php echo $taux_exec >= 80 ? 'success' : ($taux_exec >= 50 ? 'warning' : 'danger'); ?>"
                                                            style="width: <?php echo $taux_exec; ?>%"></div>
                                                    </div>
                                                    <span class="ms-2 small fw-semibold"><?php echo $taux_exec; ?>%</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="2" class="text-end text-uppercase">Décaissement total</th>
                                    <th class="text-end"><?php echo number_format($total_prev, 0, ',', ' '); ?> USD</th>
                                    <th class="text-end"><?php echo number_format($total_decaisse_activites, 0, ',', ' '); ?> USD</th>
                                    <th class="text-center">
                                        <?php
                                        $taux_global = $total_prev > 0 ? round(($total_decaisse_activites / $total_prev) * 100, 1) : 0;
                                        ?>
                                        <span class="badge bg-<?php echo $taux_global >= 80 ? 'success' : ($taux_global >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo $taux_global; ?>%
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card rounded-1 border shadow-sm h-100">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Répartition des financements</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($conventions_project)): ?>
                        <canvas id="financePieChart" style="max-height: 250px;"></canvas>

                        <div class="mt-3">
                            <?php
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                            $i = 0;
                            foreach ($conventions_project as $convention):
                                $partenaire_nom = array_column($partenaires, 'description', 'id')[$convention['partenaire_id']] ?? 'Non défini';
                                $pourcentage = $total_financements > 0 ? round(($convention['montant'] / $total_financements) * 100, 1) : 0;
                            ?>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        <span class="dot bg-<?php echo $colors[$i % count($colors)]; ?> me-2"></span>
                                        <span class="small text-truncate" style="max-width: 150px;"
                                            title="<?php echo htmlspecialchars($convention['name'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($convention['code'] ?? ''); ?> - <?php echo $partenaire_nom; ?>
                                        </span>
                                    </div>
                                    <span class="small fw-semibold"><?php echo $pourcentage; ?>%</span>
                                </div>
                            <?php
                                $i++;
                            endforeach;
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <span class="fa-regular fa-chart-bar fa-3x text-muted mb-3"></span>
                            <p class="text-muted">Ajoutez des conventions pour voir la répartition</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pieCtx = document.getElementById('financePieChart');
        if (pieCtx) {
            const conventions = <?php echo json_encode($conventions_project ?? []); ?>;
            const partenaires = <?php echo json_encode($partenaires ?? []); ?>;

            if (conventions.length > 0) {
                const labels = conventions.map(c => {
                    const partenaire = partenaires.find(p => p.id == c.partenaire_id);
                    return (c.code || 'N/A') + ' - ' + (partenaire?.description || 'N/A');
                });
                const data = conventions.map(c => parseFloat(c.montant) || 0);
                const colors = ['#0d6efd', '#198754', '#0dcaf0', '#ffc107', '#dc3545', '#6c757d', '#20c997', '#6610f2'];

                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors.slice(0, data.length),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return context.label + ': ' + new Intl.NumberFormat('fr-FR').format(value) + ' USD (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>