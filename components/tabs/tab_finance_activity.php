<div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
    <div class="row mx-0 g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm rounded-1">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2 text-primary"></i>
                        Budget des activités
                    </h5>
                    <span class="badge bg-primary"><?php echo count($taches_actives); ?> activités</span>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive" style="min-height: 432px;">
                        <table class="table small table-bordered fs-9 table-hover mb-0" id="id-datatable2">
                            <thead class="bg-primary-subtle">
                                <tr>
                                    <th class="align-middle">Code</th>
                                    <th class="align-middle">Activité</th>
                                    <th class="align-middle">Projet</th>
                                    <th class="align-middle text-end text-nowrap">Prévu (USD)</th>
                                    <th class="align-middle text-end text-nowrap">Décaissé (USD)</th>
                                    <th class="align-middle text-center">Exécution</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <?php
                                $total_prev_activites = 0;
                                $total_decaisse_activites = 0;

                                foreach ($taches_actives as $tache):
                                    $montant_prev = isset($grouped_tache_couts[$tache['id']]) ? array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant')) : 0;
                                    $montant_decaisse = $montant_prev;

                                    $total_prev_activites += $montant_prev;
                                    $total_decaisse_activites += $montant_decaisse;
                                    $taux_activite = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;

                                    $projet_associe = array_filter($projets_actifs, function ($p) use ($tache) {
                                        return $p['id'] == $tache['projet_id'];
                                    });
                                    $projet_nom = !empty($projet_associe) ? reset($projet_associe)['code'] : 'N/A';
                                ?>
                                    <tr>
                                        <td class="align-middle">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($tache['code'] ?? ''); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <span title="<?php echo htmlspecialchars($tache['name'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($tache['name'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle"><?php echo $projet_nom; ?></td>
                                        <td class="align-middle text-end">
                                            <?php if ($montant_prev > 0): ?>
                                                <span class="fw-semibold"><?php echo number_format($montant_prev, 0, ',', ' '); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-end">
                                            <?php if ($montant_decaisse > 0): ?>
                                                <span class="text-success"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle text-center">
                                            <?php if ($montant_prev > 0): ?>
                                                <span class="ms-2 fw-semibold"><?php echo $taux_activite; ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="text-nowrap">
                                    <th colspan="3" class="text-end">TOTAUX</th>
                                    <th class="text-end"><?php echo number_format($total_prev_activites, 0, ',', ' '); ?></th>
                                    <th class="text-end"><?php echo number_format($total_decaisse_activites, 0, ',', ' '); ?></th>
                                    <th class="text-center">
                                        <?php
                                        $taux_global_activites = $total_prev_activites > 0 ? round(($total_decaisse_activites / $total_prev_activites) * 100, 1) : 0;
                                        ?>
                                        <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux_global_activites >= 80 ? 'success' : ($taux_global_activites >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo $taux_global_activites; ?>%
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm rounded-1 h-100">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>
                        Répartition par projet
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php
                    $budget_par_projet = [];
                    $budget_chart_projet = [];
                    foreach ($projets_actifs as $proj) {
                        $budget_par_projet[$proj['id']] = [
                            'code' => $proj['code'],
                            'name' => $proj['code'] . ' - ' . $proj['name'],
                            'montant' => 0
                        ];
                    }

                    foreach ($taches_actives as $tache) {
                        $projet_id = $tache['projet_id'];
                        if (isset($grouped_tache_couts[$tache['id']])) {
                            $montant = array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant'));
                            if (isset($budget_par_projet[$projet_id])) {
                                $budget_par_projet[$projet_id]['montant'] += $montant;
                            }
                        }
                    }

                    $budget_par_projet = array_filter($budget_par_projet, function ($p) {
                        return $p['montant'] > 0;
                    });

                    foreach ($budget_par_projet as $projet) {
                        $budget_chart_projet[] = [
                            'name' => $projet['code'],
                            'y' => $projet['montant'],
                        ];
                    }
                    ?>

                    <?php if (!empty($budget_par_projet)): ?>
                        <div class="chart-container">
                            <div class="card-body p-2" id="chartFinanceProjet" style="min-height: 350px;"></div>
                        </div>

                        <div class="mt-3 border-top">
                            <h6 class="text-muted m-3">Budget par projet</h6>

                            <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                <?php
                                $total_budget_projets = array_sum(array_column($budget_par_projet, 'montant'));
                                $i = 0;
                                foreach ($budget_par_projet as $projet):
                                    $pourcentage = $total_budget_projets > 0 ? round(($projet['montant'] / $total_budget_projets) * 100, 1) : 0;
                                ?>
                                    <div class="d-flex align-items-center fs-9 mb-2">
                                        <span class="dot p-1 bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?> me-2"></span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between small">
                                                <span class="text-truncate" style="max-width: 300px;"><?php echo html_entity_decode($projet['name']); ?></span>
                                                <span class="fw-semibold"><?php echo number_format($projet['montant'], 0, ',', ' '); ?> USD</span>
                                            </div>
                                            <div class="progress progress-sm my-1">
                                                <div class="progress-bar bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?>"
                                                    style="width: <?php echo $pourcentage; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune donnée budgétaire par projet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>