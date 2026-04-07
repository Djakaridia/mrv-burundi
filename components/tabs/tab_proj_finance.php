<div class="mb-9 mt-2">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card rounded-1 bg-primary-subtle border-0">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-hand-holding-dollar fs-3 text-primary"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-2">Total financements</h6>
                            <?php $total_financements = array_sum(array_column($conventions_project, 'montant')) ?>
                            <h5 class="mb-0"><?php echo number_format($total_financements, 0, ',', ' '); ?> USD</h5>
                            <small class="text-primary"><?php echo count($conventions_project); ?> conventions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card rounded-1 bg-success-subtle border-0">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-circle-check fs-3 text-success"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-2">Montant décaissé</h6>
                            <?php
                            $total_decaisse = 0;
                            foreach ($taches_project as $tache) {
                                if (isset($grouped_tache_couts[$tache['id']])) {
                                    $total_decaisse += array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant'));
                                }
                            }
                            $taux_execution = $total_financements > 0 ? round(($total_decaisse / $total_financements) * 100, 1) : 0;
                            ?>
                            <h5 class="mb-0"><?php echo number_format($total_decaisse, 0, ',', ' '); ?> USD</h5>
                            <small class="text-success"><?php echo $taux_execution; ?>% du total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card rounded-1 bg-warning-subtle border-0">
                <div class="card-body p-2">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="fa-solid fa-clock fs-3 text-warning"></span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-2">Solde disponible</h6>
                            <?php $solde = $total_financements - $total_decaisse; ?>
                            <h5 class="mb-0"><?php echo number_format($solde, 0, ',', ' '); ?> USD</h5>
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
                    data-bs-toggle="modal" data-bs-target="#addConvenModal"
                    data-projet="<?= $project_curr['id'] ?>"
                    data-secteur="<?= $project_curr['secteur_id'] ?>"
                    data-action="<?= $project_curr['action_type'] ?>"
                    aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                    <i class="fas fa-plus me-1"></i> Nouvelle convention
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative mb-3">
        <div class="table-responsive py-1 px-2">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable1">
                <thead class="bg-primary-subtle">
                    <tr>
                        <th class="sort align-middle">Code</th>
                        <th class="sort align-middle">Convention</th>
                        <th class="sort align-middle">Bailleur</th>
                        <th class="sort align-middle">Type de soutien</th>
                        <th class="sort align-middle">Instrument</th>
                        <th class="sort align-middle">Date d'accord</th>
                        <th class="sort align-middle text-end">Montant (USD)</th>
                        <th class="sort align-middle text-center">Statut</th>
                        <th class="sort align-middle text-center" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    <?php foreach ($conventions_project as $convention):
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

                        $partenaire_nom = array_column($partenaires, 'sigle', 'id')[$convention['partenaire_id']] ?? 'Non défini';
                    ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                            <td class="align-middle">
                                <span class="fw-semibold"><?php echo $convention['code'] ?? ''; ?></span>
                            </td>
                            <td class="align-middle">
                                <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                    title="<?php echo $convention['name'] ?? ''; ?>">
                                    <?php echo $convention['name'] ?? ''; ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center">
                                    <span class="fa-regular fa-building me-2 text-body-tertiary"></span>
                                    <?php echo $partenaire_nom; ?>
                                </div>
                            </td>
                            <td class="align-middle">
                                <?php echo listTypeAction()[$convention['action_type']] ?? "N/A" ?>
                            </td>
                            <td class="align-middle">
                                <?php echo listTypeFinancement()[$convention['instrument']] ?? "N/A" ?>
                            </td>
                            <td class="align-middle">
                                <?php echo !empty($convention['date_accord']) ? date('d/m/Y', strtotime($convention['date_accord'])) : '-'; ?>
                            </td>
                            <td class="align-middle text-end">
                                <span class="badge bg-info-subtle text-info p-2 fs-9 fw-semibold">
                                    <?php echo number_format($convention['montant'] ?? 0, 0, ',', ' '); ?> USD
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-phoenix rounded-pill px-2 py-1 badge-phoenix-<?php echo $statut_class; ?>">
                                    <?php echo $statut_label; ?>
                                </span>
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

    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">
                <i class="fas fa-chart-pie me-2 text-primary"></i>
                Répartition des décaissements
            </h4>
        </div>
    </div>
    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative mb-3">
        <div class="table-responsive py-1 px-0">
            <div class="row gx-3 mx-0 px-0 mb-3">
                <div class="col-md-6 col-12">
                    <div class="card h-100 p-0 rounded-1 border shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Détail des activités</h6>
                            <span class="badge bg-info-subtle text-info"><?php echo count($taches_project); ?> activités</span>
                        </div>
                        <div class="card-body p-1">
                            <div class="table-responsive">
                                <table class="table small table-bordered table-hover mb-0 border-translucent" id="id-datatable2">
                                    <thead class="bg-primary-subtle">
                                        <tr>
                                            <th class="align-middle" width="5%">Code</th>
                                            <th class="align-middle" width="40%">Activité</th>
                                            <th class="align-middle text-end">Prévu (USD)</th>
                                            <th class="align-middle text-end">Décaissé (USD)</th>
                                            <th class="align-middle text-center">% Exec</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        <?php
                                        $total_prevu_activites = 0;
                                        $total_decaisse_activites = 0;
                                        foreach ($taches_project as $tache):
                                            $montant_prev = isset($grouped_tache_couts[$tache['id']]) ? array_sum(array_column($grouped_tache_couts[$tache['id']], 'montant')) : 0;
                                            $total_prevu_activites += $montant_prev;

                                            $montant_decaisse = isset($grouped_tache_decaisse[$tache['id']]) ? array_sum(array_column($grouped_tache_decaisse[$tache['id']], 'montant')) : 0;;
                                            $total_decaisse_activites += $montant_decaisse;

                                            $taux_exec_activites = $montant_prev > 0 ? round(($montant_decaisse / $montant_prev) * 100, 1) : 0;
                                        ?>
                                            <tr>
                                                <td class="align-middle">
                                                    <span class="fw-semibold"><?php echo $tache['id'] ?? ''; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="text-truncate d-inline-block" title="<?php echo $tache['name'] ?? ''; ?>">
                                                        <span class="fa-regular fa-clipboard me-1 text-body-tertiary"></span>
                                                        <?php echo $tache['name'] ?? ''; ?>
                                                    </span>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <?php if ($montant_prev > 0): ?>
                                                        <span class="text-secondary fw-semibold"><?php echo number_format($montant_prev, 0, ',', ' '); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <?php if ($montant_decaisse > 0): ?>
                                                        <span class="text-success fw-semibold"><?php echo number_format($montant_decaisse, 0, ',', ' '); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <?php if ($montant_prev > 0): ?>
                                                        <span class="ms-2 fw-semibold"><?php echo $taux_exec_activites; ?>%</span>
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
                                            <th class="text-end"><?php echo number_format($total_prevu_activites, 0, ',', ' '); ?> USD</th>
                                            <th class="text-end"><?php echo number_format($total_decaisse_activites, 0, ',', ' '); ?> USD</th>
                                            <th class="text-center">
                                                <?php
                                                $taux_global = $total_prevu_activites > 0 ? round(($total_decaisse_activites / $total_prevu_activites) * 100, 1) : 0;
                                                ?>
                                                <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux_global >= 80 ? 'success' : ($taux_global >= 50 ? 'warning' : 'danger'); ?>">
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

                <div class="col-md-6 col-12">
                    <div class="card h-100 p-0 rounded-1 border shadow-sm">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Détail des bailleurs</h6>
                            <span class="badge bg-info-subtle text-info"><?php echo count($conventions_project); ?> bailleurs</span>
                        </div>
                        <div class="card-body p-1">
                            <div class="table-responsive">
                                <table class="table small table-bordered table-hover mb-0 border-translucent" id="id-datatable3">
                                    <thead class="bg-primary-subtle">
                                        <tr>
                                            <th class="sort align-middle">Code</th>
                                            <th class="sort align-middle">Bailleur</th>
                                            <th class="sort align-middle text-end">Prévu (USD)</th>
                                            <th class="sort align-middle text-end">Décaissé (USD)</th>
                                            <th class="sort align-middle text-center">% Exec</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        <?php
                                        $total_prevu_bailleurs = 0;
                                        $total_decaisse_bailleurs = 0;
                                        foreach ($conventions_project as $convention):
                                            $decaisse_convention = 0;
                                            foreach ($taches_project as $tache) {
                                                if (isset($grouped_tache_decaisse[$tache['id']])) {
                                                    foreach ($grouped_tache_decaisse[$tache['id']] as $cout) {
                                                        if (isset($cout['convention_id']) && $cout['convention_id'] == $convention['id']) {
                                                            $decaisse_convention += floatval($cout['montant']);
                                                        }
                                                    }
                                                }
                                            }

                                            $total_prevu_bailleurs += (float)$convention['montant'];
                                            $total_decaisse_bailleurs += $decaisse_convention;
                                            $taux_exec_bailleurs = (float)$convention['montant'] > 0 ? round(($decaisse_convention / (float)$convention['montant']) * 100, 1) : 0;
                                            $partenaire_nom = array_column($partenaires, 'sigle', 'id')[$convention['partenaire_id']] ?? 'Non défini';
                                        ?>
                                            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                <td class="align-middle">
                                                    <span class="fw-semibold"><?php echo $convention['code'] ?? ''; ?></span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <span class="fa-regular fa-building me-1 text-body-tertiary"></span>
                                                        <?php echo $partenaire_nom; ?>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <?php if ((float)$convention['montant'] > 0): ?>
                                                        <span class="text-secondary fw-semibold"><?php echo number_format((float)$convention['montant'], 0, ',', ' '); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-end">
                                                    <?php if ($decaisse_convention > 0): ?>
                                                        <span class="text-success fw-semibold"><?php echo number_format($decaisse_convention, 0, ',', ' '); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <?php if ((float)$convention['montant'] > 0): ?>
                                                        <span class="ms-2 fw-semibold"><?php echo $taux_exec_bailleurs; ?>%</span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <?php if (!empty($conventions_project)): ?>
                                        <tfoot class="bg-light">
                                            <tr class="fw-bold">
                                                <th colspan="2" class="text-end text-uppercase">Décaissement total</th>
                                                <th class="text-end"><?php echo number_format($total_prevu_bailleurs, 0, ',', ' '); ?> USD</th>
                                                <th class="text-end"><?php echo number_format($total_decaisse_bailleurs, 0, ',', ' '); ?> USD</th>
                                                <th class="text-center">
                                                    <?php $taux_global = $total_prevu_bailleurs > 0 ? round(($total_decaisse_bailleurs / $total_prevu_bailleurs) * 100, 1) : 0; ?>
                                                    <span class="badge badge-phoenix py-1 px-2 badge-phoenix-<?php echo $taux_global >= 80 ? 'success' : ($taux_global >= 50 ? 'warning' : 'danger'); ?>">
                                                        <?php echo $taux_global; ?>%
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>