<div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
    <div class="row mx-0 g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm rounded-1">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2 text-primary"></i>
                        Détail des conventions par bailleur
                    </h5>
                    <span class="badge bg-primary"><?php echo count($conventions); ?> conventions</span>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive" style="min-height: 432px;">
                        <table class="table small table-bordered fs-9 table-hover mb-0" id="id-datatable1">
                            <thead class="bg-primary-subtle">
                                <tr>
                                    <th class="align-middle">Code</th>
                                    <th class="align-middle">Convention</th>
                                    <th class="align-middle">Bailleur</th>
                                    <th class="align-middle text-end text-nowrap">Prévu (USD)</th>
                                    <th class="align-middle text-end text-nowrap">Décaissé (USD)</th>
                                    <th class="align-middle text-center">Taux</th>
                                    <th class="align-middle text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                <?php foreach ($conventions as $convention):
                                    $montant_prevu = floatval($convention['montant'] ?? 0);
                                    $montant_decaisse = $decaisse_par_convention[$convention['id']] ?? 0;
                                    $taux = $montant_prevu > 0 ? round(($montant_decaisse / $montant_prevu) * 100, 1) : 0;

                                    $now = time();
                                    $date_fin = strtotime($convention['date_fin'] ?? '');
                                    if ($date_fin && $now > $date_fin) {
                                        $statut = 'Expirée';
                                        $statut_class = 'danger';
                                    } elseif ($taux >= 100) {
                                        $statut = 'Soldée';
                                        $statut_class = 'success';
                                    } elseif ($taux > 0) {
                                        $statut = 'En cours';
                                        $statut_class = 'warning';
                                    } else {
                                        $statut = 'Non démarrée';
                                        $statut_class = 'secondary';
                                    }
                                ?>
                                    <tr>
                                        <td class="align-middle">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($convention['code'] ?? ''); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <span title="<?php echo htmlspecialchars($convention['name'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($convention['name'] ?? ''); ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <?php echo $grouped_partenaire[$convention['partenaire_id']]['sigle'] ?? 'N/A'; ?>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="fw-semibold"><?php echo number_format($montant_prevu, 0, ',', ' '); ?></span>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="text-success"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux >= 80 ? 'success' : ($taux >= 50 ? 'warning' : 'light'); ?> text-<?php echo $taux >= 80 ? 'white' : ($taux >= 50 ? 'dark' : 'secondary'); ?>">
                                                <?php echo $taux; ?>%
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $statut_class; ?>">
                                                <?php echo $statut; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr class="text-nowrap">
                                    <th colspan="3" class="text-end">TOTAUX</th>
                                    <th class="text-end"><?php echo number_format($total_conventions, 0, ',', ' '); ?></th>
                                    <th class="text-end"><?php echo number_format($total_decaisse, 0, ',', ' '); ?></th>
                                    <th class="text-center"><?php echo $taux_execution_global; ?>%</th>
                                    <th></th>
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
                        <i class="fas fa-chart-bar me-2 text-primary"></i>
                        Répartition des financements par bailleur
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($conventions_par_bailleur)): ?>
                        <div class="chart-container">
                            <div class="card-body p-2" id="chartFinanceBailleur" style="min-height: 350px;"></div>
                        </div>

                        <div class="mt-3 border-top">
                            <h6 class="text-muted m-3">Détail par bailleur</h6>
                            <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                <?php
                                $i = 0;
                                foreach ($conventions_par_bailleur as $bailleur_id => $data):
                                    $pourcentage = $total_conventions > 0 ? round(($data['montant_total'] / $total_conventions) * 100, 1) : 0;
                                ?>
                                    <div class="d-flex align-items-center fs-9 mb-2">
                                        <span class="dot p-1 bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?> me-2"></span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-semibold"><?php echo $data['name']; ?></span>
                                                <span class="text-muted"><?php echo number_format($data['montant_total'], 0, ',', ' '); ?> USD</span>
                                            </div>
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-<?php echo listCouleur()[$i % count(listCouleur())]; ?>"
                                                    style="width: <?php echo $pourcentage; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo count($data['conventions']); ?> conventions • <?php echo $pourcentage; ?>%</small>
                                        </div>
                                    </div>
                                <?php
                                    $i++;
                                endforeach;
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 my-3">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune donnée disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>