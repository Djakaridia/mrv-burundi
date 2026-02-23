<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Résultats obtenus | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    $referentiel = new Referentiel($db);
    $referentiels = $referentiel->read();
    $referentiels = array_filter($referentiels, function ($referentiel) {
        return $referentiel['state'] == 'actif';
    });
    if (isset($_GET['secteur']) && !empty($_GET['secteur'])) {
        $secteur = (int) $_GET['secteur'];
        $referentiels = array_filter($referentiels, function ($referentiel) use ($secteur) {
            return $referentiel['secteur_id'] == $secteur;
        });
    }

    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $action = $_GET['action'];
        $referentiels = array_filter($referentiels, function ($referentiel) use ($action) {
            return $referentiel['action_type'] == $action;
        });
    }

    if (isset($_GET['categorie']) && !empty($_GET['categorie'])) {
        $categorie = $_GET['categorie'];
        $referentiels = array_filter($referentiels, function ($referentiel) use ($categorie) {
            return $referentiel['categorie'] == $categorie;
        });
    }

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });
    $sous_secteurs = array_filter($data_secteurs, function ($secteur) {
        return $secteur['parent'] > 0 && $secteur['state'] == 'actif';
    });
    ?>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">
            <div class="mt-n5 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="row mx-n1 py-1 align-items-center">
                    <div class="col-md-4">
                        <h3 class="h5 mb-0 fw-bold">Résultats obtenus sur les indicateurs</h3>
                        <p class="text-muted small mb-0">Tableau récapitulatif des suivis des indicateurs de la CDN</p>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-md-end gap-3">
                            <div class="d-flex gap-1 align-items-center">
                                <?php $currFilSecteur = isset($_GET['secteur']) ? $_GET['secteur'] : '';
                                $currFilAction = isset($_GET['action']) ? $_GET['action'] : '';
                                $currFilCategorie = isset($_GET['categorie']) ? $_GET['categorie'] : ''; ?>

                                <span class="form-label">Filtrer : </span>
                                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                    <form class="position-relative">
                                        <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="actionFilter"
                                            onchange="pagesFilters([{ id: 'actionFilter', param: 'action' }])">
                                            <option value="">Toutes actions</option>
                                            <?php foreach (listTypeAction() as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= ($currFilAction == $key) ? 'selected' : '' ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                    <form class="position-relative">
                                        <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="secteurFilter"
                                            onchange="pagesFilters([{ id: 'secteurFilter', param: 'secteur' }])">
                                            <option value="">Tous secteurs</option>
                                            <?php if (isset($secteurs) && !empty($secteurs)): ?>
                                                <?php foreach ($secteurs as $secteur): ?>
                                                    <option value="<?= $secteur['id'] ?>" <?= ($currFilSecteur == $secteur['id']) ? 'selected' : '' ?>>
                                                        <?= $secteur['name'] ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </form>
                                </div>
                                <div class="search-box d-none d-lg-block my-lg-0" style="width: 8rem !important;">
                                    <form class="position-relative">
                                        <select class="form-select form-select-sm bg-warning-subtle text-warning px-2 rounded-1" id="catgorieFilter"
                                            onchange="pagesFilters([{ id: 'catgorieFilter', param: 'categorie' }])">
                                            <option value="">Tous catégorie</option>
                                            <?php foreach (listTypeCategorie() as $key => $value): ?>
                                                <option value="<?= $key ?>" <?= ($currFilCategorie == $key) ? 'selected' : '' ?>>
                                                    <?= $value ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </div>
                            </div>

                            <a href="referentiels.php" class="btn btn-subtle-info btn-sm">
                                <span class="fa-solid fa-eye fs-9 me-2"></span>Voir les indicateurs
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 p-1 mx-lg-n6 bg-body-emphasis border-y">
                <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                    <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                        <thead class="bg-primary-subtle">
                            <tr>
                                <th class="sort align-middle px-2" scope="col">Code</th>
                                <th class="sort align-middle px-2" scope="col">Intitulé</th>
                                <th class="sort align-middle px-2" scope="col">Unité</th>
                                <!-- <th class="sort align-middle px-2" scope="col">Catégorie</th>
                                <th class="sort align-middle px-2" scope="col">Responsables</th> -->
                                <th class="sort align-middle px-2 text-nowrap" scope="col">Mesures & actions</th>
                                <th class="sort align-middle px-2 text-nowrap" scope="col">Indicateur projet</th>
                                <th class="sort align-middle px-2 text-center" scope="col">Cible</th>
                                <th class="sort align-middle px-2 text-center" scope="col">Réalisé</th>
                                <th class="sort align-middle px-2 text-center" scope="col">Taux réalisation</th>
                                <th class="sort align-middle px-2" scope="col">Suivis</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            <?php foreach ($referentiels as $referentiel):
                                $cibles_total = 0;
                                $suivis_total = 0;
                                $taux_progress = 0;
                                $nbre_cmr = 0;
                                $nbre_mesure = 0;

                                if ($referentiel['categorie'] == 'produit') {
                                    $indicateur = new Indicateur($db);
                                    $indicateur->referentiel_id = $referentiel['id'];
                                    $indicateurs = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');
                                    $nbre_cmr = count($indicateurs);

                                    $cibles_total = $suivis_total = 0;
                                    if (!empty($indicateurs)) {
                                        foreach ($indicateurs as $cmr) {
                                            $cible_cmr = new Cible($db);
                                            $cible_cmr->cmr_id = $cmr['id'];
                                            $cibles_raw = $cible_cmr->readByCMR();

                                            $suivi_cmr = new Suivi($db);
                                            $suivi_cmr->cmr_id = $cmr['id'];
                                            $suivis_raw = $suivi_cmr->readByCMR();

                                            if (!empty($cibles_raw)) {
                                                usort($cibles_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                                foreach ($cibles_raw as $item) {
                                                    $value = (float)$item['valeur'];
                                                    $cibles_total += $value;
                                                }
                                            }

                                            if (!empty($suivis_raw)) {
                                                usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                                foreach ($suivis_raw as $item) {
                                                    $value = (float)$item['valeur'];
                                                    $suivis_total += $value;
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $mesure = new Mesure($db);
                                    $mesure->referentiel_id = $referentiel['id'];
                                    $mesures = $mesure->readByIndicateur();
                                    $nbre_mesure = count($mesures);

                                    $cible = new Cible($db);
                                    $cible->indicateur_id = $referentiel['id'];
                                    $cibles_raw = $cible->readByIndicateur();

                                    $suivi_referentiel = new Suivi($db);
                                    $suivi_referentiel->indicateur_id = $referentiel['id'];
                                    $suivis_raw = $suivi_referentiel->readByIndicateur();

                                    if (!empty($cibles_raw)) {
                                        usort($cibles_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                        foreach ($cibles_raw as $item) {
                                            $value = (float)$item['valeur'];
                                            $cibles_total += $value;
                                        }
                                    }

                                    if (!empty($suivis_raw)) {
                                        usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                        foreach ($suivis_raw as $item) {
                                            $value = (float)$item['valeur'];
                                            $suivis_total += $value;
                                        }
                                    }
                                }

                                $taux_progress = $cibles_total > 0 ? ($suivis_total / $cibles_total) * 100 : 0;
                                $taux_progress = round($taux_progress, 2, true)
                            ?>
                                <tr class="hover-actions-trigger btn-reveal-trigger position-relative">
                                    <td class="align-middle px-3 py-2">
                                        <span class="fw-medium badge bg-primary-subtle text-primary-emphasis">
                                            <?= htmlspecialchars($referentiel['code']) ?>
                                        </span>
                                    </td>

                                    <td class="align-middle px-3">
                                        <div class="d-flex align-items-center">
                                            <span class="text-wrap"><?= htmlspecialchars($referentiel['intitule']) ?></span>
                                        </div>
                                    </td>

                                    <td class="align-middle px-3">
                                        <?= htmlspecialchars($referentiel['unite'] ?? 'N/A') ?>
                                    </td>

                                    <!-- <td class="align-middle px-3">
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill">
                                            <?php if ($referentiel['categorie'] == 'impact'): ?>
                                                <i class="fas fa-bullseye text-secondary me-1" title="Impact"></i>
                                            <?php elseif ($referentiel['categorie'] == 'effet'): ?>
                                                <i class="fas fa-chart-line text-secondary me-1" title="Effet"></i>
                                            <?php else: ?>
                                                <i class="fas fa-tag text-secondary me-1"></i>
                                            <?php endif; ?>
                                            <?= htmlspecialchars(listTypeCategorie()[$referentiel['categorie']] ?? 'N/A') ?>
                                        </span>
                                    </td> -->

                                    <!-- <td class="align-middle px-3">
                                        <?php
                                        $responsable_sigle = '';
                                        foreach ($structures as $structure):
                                            if ($structure['id'] == $referentiel['responsable']):
                                                $responsable_sigle = $structure['sigle'];
                                                break;
                                            endif;
                                        endforeach;
                                        ?>
                                        <?php if (!empty($responsable_sigle)): ?>
                                            <span class="d-inline-flex align-items-center">
                                                <?= htmlspecialchars($responsable_sigle) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-body-secondary fst-italic">
                                                <i class="fas fa-minus me-1"></i>Non assigné
                                            </span>
                                        <?php endif; ?>
                                    </td> -->

                                    <td class="align-middle text-center px-3">
                                        <button type="button" class="btn btn-link text-decoration-none p-0 border-0"
                                            data-bs-toggle="modal" data-bs-target="#viewRefMesureModal"
                                            data-id="<?= $referentiel['id'] ?>"
                                            title="Voir les mesures">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php if ($nbre_mesure > 0): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">
                                                        <?= $nbre_mesure ?> mesure<?= $nbre_mesure > 1 ? 's' : '' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                        <i class="fas fa-times me-1"></i>Aucune
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    </td>

                                    <td class="align-middle text-center px-3">
                                        <button type="button" class="btn btn-link text-decoration-none p-0 border-0"
                                            data-bs-toggle="modal" data-bs-target="#viewRefCMRModal"
                                            data-id="<?= $referentiel['id'] ?>"
                                            title="Voir les indicateurs">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php if ($nbre_cmr > 0): ?>
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">
                                                        <?= $nbre_cmr ?> indicateur<?= $nbre_cmr > 1 ? 's' : '' ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                                        <i class="fas fa-times me-1"></i>Aucun
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </button>
                                    </td>

                                    <!-- Cibles (fond gris) -->
                                    <td class="align-middle bg-secondary-subtle px-3 py-2">
                                        <?php if (!empty($cibles_total) && $cibles_total !== '-'): ?>
                                            <span class="fw-bold">
                                                <?= htmlspecialchars(strtoupper($cibles_total)) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-body-secondary">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Suivis (fond gris) -->
                                    <td class="align-middle bg-secondary-subtle px-3 py-2">
                                        <?php if (!empty($suivis_total) && $suivis_total !== '-'): ?>
                                            <span class="fw-bold">
                                                <?= htmlspecialchars(strtoupper($suivis_total)) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-body-secondary">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Taux de progression (fond gris) -->
                                    <td class="align-middle bg-secondary-subtle p-2">
                                        <?php
                                        if ($taux_progress < 39)
                                            $color = "danger";
                                        elseif ($taux_progress < 69)
                                            $color = "warning";
                                        elseif ($taux_progress >= 70)
                                            $color = "success"; ?>
                                        <div class="progress progress-xl fs-10 rounded-0 p-0 m-0" style="height: 1.3rem; width: 150px">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated fs-14 fw-bold bg-<?php echo $color; ?> " aria-valuenow="70" style="width: 100%;">
                                                <?php echo (isset($taux_progress) && $taux_progress > 0) ? $taux_progress . " %" : "Non entamé"; ?>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Action Suivre -->
                                    <td class="align-middle px-3 py-2">
                                        <?php if (in_array($referentiel['categorie'], ['impact', 'effet'])): ?>
                                            <button type="button"
                                                class="btn btn-subtle-warning fs-10 py-1 rounded-pill btn-sm fw-bold fs-9 px-2 py-1"
                                                onclick="window.location.href = 'referentiel_view.php?id=<?= $referentiel['id'] ?>'"
                                                title="Suivre cet indicateur">
                                                Suivre
                                            </button>
                                        <?php else: ?>
                                            <span class="text-body-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>