<div class="mx-n4 p-3 mx-lg-n6 bg-body-emphasis border-y">
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm rounded-1 h-100">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Évolution mensuelle des décaissements
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($timeline_labels)): ?>
                        <div class="chart-container">
                            <div class="card-body p-2" id="timelineMultiChart" style="min-height: 350px;"></div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune donnée de décaissement disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm rounded-1 h-100">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-primary"></i>
                        Répartition des financements par convention
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($conventions)): ?>
                        <div class="chart-container">
                            <div class="card-body p-2" id="chartFinanceConvention" style="min-height: 350px;"></div>
                        </div>
                        <div class="mt-3 border-top">
                            <h6 class="text-muted m-3">Détail par convention</h6>
                            <div class="px-3" style="max-height: 300px; overflow-y: auto;">
                                <?php
                                $top_conventions = array_slice($conventions, 0, 5);
                                foreach ($top_conventions as $index => $conv):
                                    $pourcentage = $total_conventions > 0 ? round(($conv['montant'] / $total_conventions) * 100, 1) : 0;
                                ?>
                                    <div class="d-flex align-items-center justify-content-between fs-9 mb-2">
                                        <span class="dot p-1 bg-<?php echo listCouleur()[$index % count(listCouleur())]; ?> me-2"></span>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <?php echo $conv['name']; ?>
                                                <span class="fw-semibold"><?php echo $pourcentage; ?>%</span>
                                            </div>
                                            <small class="text-muted"><?php echo number_format($conv['montant'], 0, ',', ' '); ?> USD</small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune convention</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>