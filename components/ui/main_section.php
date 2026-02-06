<?php if (isset($sections_dash) && is_array($sections_dash) && count($sections_dash) > 0) { ?>
    <div class="row g-4 mx-n6 mt-n5">
        <?php foreach ($sections_dash as $section): ?>
            <?php
            $value = 'N/A';
            $unit = '';
            $badge = '';
            $link = '';

            if (!empty($section['entity_type'])) {
                switch ($section['entity_type']) {
                    case 'indicateur':
                        try {
                            $referentiel = new Referentiel($db);
                            $referentiel->id = $section['entity_id'];
                            $referentiel_ref = $referentiel->readById();

                            if ($referentiel_ref['categorie'] != 'impact') {
                                $indicateur = new Indicateur($db);
                                $indicateur->referentiel_id = $referentiel_ref['id'] ?? null;
                                $indicateur_cmr = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

                                $first_indicateur = reset($indicateur_cmr);

                                $cible = new Cible($db);
                                $cible->indicateur_id = $first_indicateur['id'];
                                $cibles_raw = $cible->readByIndicateur();
                                $cibles_map = [];
                                foreach ($cibles_raw as $item) {
                                    $year = $item['annee'];
                                    $value = (float)$item['valeur'];
                                    if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
                                    $cibles_map[$year] += $value;
                                }
                                $cibles_sum = array_sum($cibles_map);

                                $suivi = new Suivi($db);
                                $suivi->indicateur_id = $first_indicateur['id'];
                                $suivis_raw = $suivi->readByIndicateur();
                                $suivis_map = [];
                                foreach ($suivis_raw as $item) {
                                    $year = $item['annee'];
                                    $value = (float)$item['valeur'];
                                    if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
                                    $suivis_map[$year] += $value;
                                }
                                $suivis_sum = array_sum($suivis_map);

                                $percentage = $cibles_sum != 0 ? (($suivis_sum - $cibles_sum) / $cibles_sum) * 100 : 0;
                                $sens_evolution = $referentiel_ref['sens_evolution'] ?? 'asc';
                                $is_positive = $sens_evolution == 'asc' ? $suivis_sum >= $cibles_sum : $suivis_sum <= $cibles_sum;

                                $value = number_format($suivis_sum);
                                $unit = $referentiel_ref['unite'] ?? 'N/A';
                                $badge = sprintf(
                                    '<span class="badge bg-%s-subtle text-%s"> <i class="fas fa-sort-amount-%s me-1"></i>%s %d%% vs cible</span>',
                                    $is_positive ? 'success' : 'warning',
                                    $is_positive ? 'success' : 'warning',
                                    $sens_evolution == 'asc' ? 'up' : 'down',
                                    $sens_evolution == 'asc' ? 'Augmenté de' : 'Diminué de',
                                    abs(round($percentage))
                                );
                                $link = 'indicateur_view.php?id=' . $section['entity_id'];
                            } else {
                                $cible_referentiel = new Cible($db);
                                $cible_referentiel->indicateur_id = $referentiel_ref['id'];
                                $cibles_raw = $cible_referentiel->readByIndicateur();
                                $cibles_map = [];
                                foreach ($cibles_raw as $item) {
                                    $year = $item['annee'];
                                    $value = (float)$item['valeur'];
                                    if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
                                    $cibles_map[$year] += $value;
                                }
                                $cibles_sum = array_sum($cibles_map);

                                $suivi_referentiel = new Suivi($db);
                                $suivi_referentiel->indicateur_id = $referentiel_ref['id'];
                                $suivis_raw = $suivi_referentiel->readByIndicateur();
                                $suivis_map = [];
                                foreach ($suivis_raw as $item) {
                                    $year = $item['annee'];
                                    $value = (float)$item['valeur'];
                                    if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
                                    $suivis_map[$year] += $value;
                                }
                                $suivis_sum = array_sum($suivis_map);

                                $percentage = $cibles_sum != 0 ? (($suivis_sum - $cibles_sum) / $cibles_sum) * 100 : 0;
                                $sens_evolution = $referentiel_ref['sens_evolution'] ?? 'asc';
                                $is_positive = $sens_evolution == 'asc' ? $suivis_sum >= $cibles_sum : $suivis_sum <= $cibles_sum;

                                $value = number_format($suivis_sum);
                                $unit = $referentiel_ref['unite'] ?? 'N/A';
                                $badge = sprintf(
                                    '<span class="badge bg-%s-subtle text-%s"> <i class="fas fa-sort-amount-%s me-1"></i>%s %d%% vs cible</span>',
                                    $is_positive ? 'success' : 'warning',
                                    $is_positive ? 'success' : 'warning',
                                    $sens_evolution == 'asc' ? 'up' : 'down',
                                    $sens_evolution == 'asc' ? 'Augmenté de' : 'Diminué de',
                                    abs(round($percentage))
                                );
                                $link = 'referentiel_view.php?id=' . $section['entity_id'];
                            }
                        } catch (Exception $e) {
                            error_log("Erreur dans la section indicateur: " . $e->getMessage());
                            $value = 'Erreur';
                            $badge = '<span class="badge bg-warning-subtle text-warning">Données indisponibles</span>';
                        }
                        break;

                    case 'projet':
                        try {
                            $projet = new Projet($db);
                            $projet->id = $section['entity_id'];
                            $projet_ref = $projet->readById();

                            $task = new Tache($db);
                            $task->projet_id = $section['entity_id'];
                            $tasks = $task->readByProjet();

                            $active_tasks = array_filter($tasks, fn($t) => ($t['state'] ?? '') === 'actif');
                            $status_counts = ['realise' => 0, 'annule' => 0];

                            $badge_parts = [];
                            if (!empty($active_tasks)) {
                                foreach ($active_tasks as $task) {
                                    $status = $task['status'] ?? 'planifie';
                                    if (array_key_exists($status, $status_counts)) {
                                        $status_counts[$status]++;
                                    }
                                }

                                if ($status_counts['realise'] > 0 || $status_counts['annule'] > 0) {
                                    if ($status_counts['realise'] > 0) {
                                        $badge_parts[] = sprintf(
                                            '<span class="badge bg-success-subtle text-success">%d Terminées</span>',
                                            $status_counts['realise']
                                        );
                                    }
                                    if ($status_counts['annule'] > 0) {
                                        $badge_parts[] = sprintf(
                                            '<span class="badge bg-danger-subtle text-danger">%d Annulées</span>',
                                            $status_counts['annule']
                                        );
                                    }
                                } else {
                                    $badge_parts[] = '<span class="badge bg-warning-subtle text-warning">Pas de tâche terminée</span>';
                                }
                            } else {
                                $badge_parts[] = '<span class="badge bg-warning-subtle text-warning">Données indisponibles</span>';
                            }

                            $value = count($active_tasks);
                            $unit = 'Tâches';
                            $badge = implode(' ', $badge_parts);
                            $link = 'project_view.php?id=' . $section['entity_id'];
                        } catch (Exception $e) {
                            error_log("Erreur dans la section projet: " . $e->getMessage());
                            $value = 'Erreur';
                            $unit = '';
                            $badge = '<span class="badge bg-warning-subtle text-warning">Données indisponibles</span>';
                        }
                        break;
                }
            }
            ?>

            <div class="col-md-<?= min(12, round(12 / max(1, count($sections_dash)))) ?> mb-3">
                <div class="card h-100 kpi-card shadow-sm border-<?= $section['couleur'] ?> dashboard-card rounded-1 cursor-pointer">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-body-tertiary mb-2"><?= ($section['intitule'] ?? '') ?></h6>
                                <h2 class="text-<?= $section['couleur'] ?> mb-0">
                                    <?= ($value) ?>
                                    <?php if (!empty($unit)): ?>
                                        <small class="fs-9"><?= ($unit) ?></small>
                                    <?php endif; ?>
                                </h2>
                            </div>
                            <div class="icon-wrapper bg-<?= $section['couleur'] ?> bg-opacity-10 rounded-2">
                                <span class="p-2 <?= ($section['icone'] ?? '') ?> text-<?= $section['couleur'] ?> fs-4"></span>
                            </div>
                        </div>
                        <?php if (!empty($badge)): ?>
                            <div class="mt-2"><?= $badge ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($link)): ?>
                        <div onclick="window.location.href='<?= ($link) ?>';" class="stretched-link"></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php } ?>