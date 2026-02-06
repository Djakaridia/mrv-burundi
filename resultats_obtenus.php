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
                                <th class="sort align-middle px-2" scope="col">Responsables</th>
                                <th class="sort align-middle px-2 text-nowrap" scope="col">Mesures & actions</th>
                                <th class="sort align-middle px-2 text-nowrap" scope="col">Indicateur projet</th>
                                <th class="sort align-middle px-2" scope="col">Total prévu</th>
                                <th class="sort align-middle px-2" scope="col">Total réalisé</th>
                                <th class="sort align-middle px-2" scope="col">Taux réalisation</th>
                                <th class="sort align-middle px-2" scope="col">Suivis</th>
                            </tr>
                        </thead>
                        <tbody class="list" id="table-latest-review-body">
                            <?php foreach ($referentiels as $referentiel):
                                $cibles_total = 0;
                                $suivis_total = 0;
                                $taux_progress = 0;
                                $nbre_cmr = 0;
                                $nbre_mesure = 0;

                                if ($referentiel['categorie'] != 'impact') {
                                    $indicateur = new Indicateur($db);
                                    $indicateur->referentiel_id = $referentiel['id'];
                                    $indicateurs = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

                                    if (!empty($indicateurs)) {
                                        $nbre_cmr = count($indicateurs);
                                        $indicateur_cmr = array_pop($indicateurs);

                                        $projet = new Projet($db);
                                        $projet->id = $indicateur_cmr['projet_id']??"";
                                        $projet_ref = $projet->readById();
                                        $annees = [];
                                        for ($year = date('Y', strtotime($projet_ref['start_date'])); $year <= date('Y', strtotime($projet_ref['end_date'])); $year++) {
                                            $annees[] = $year;
                                        }

                                        $cible = new Cible($db);
                                        $cible->indicateur_id = $indicateur_cmr['id']??"";
                                        $cibles_raw = $cible->readByIndicateur();
                                        $cibles_map = [];
                                        if (count($cibles_raw) > 0) {
                                            foreach ($cibles_raw as $item) {
                                                $year = $item['annee'];
                                                $value = (float)$item['valeur'];
                                                if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
                                                $cibles_map[$year] += $value;
                                            }
                                        }
                                        $cibles = array_map(fn($y) => (float)($cibles_map[$y] ?? 0), $annees);

                                        $suivi = new Suivi($db);
                                        $suivi->indicateur_id = $indicateur_cmr['id'];
                                        $suivis_raw = $suivi->readByIndicateur();
                                        $suivis_map = [];
                                        if (count($suivis_raw) > 0) {
                                            foreach ($suivis_raw as $item) {
                                                $year = $item['annee'];
                                                $value = (float)$item['valeur'];
                                                if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
                                                $suivis_map[$year] += $value;
                                            }
                                        }
                                        $suivis = array_map(fn($y) => (float)($suivis_map[$y] ?? 0), $annees);
                                    }
                                } else {
                                    $mesure = new Mesure($db);
                                    $mesure->referentiel_id = $referentiel['id'];
                                    $mesures = $mesure->readByIndicateur();
                                    $nbre_mesure = count($mesures);

                                    $cible_referentiel = new Cible($db);
                                    $cible_referentiel->indicateur_id = $referentiel['id'];
                                    $cibles_raw = $cible_referentiel->readByIndicateur();

                                    $suivi_referentiel = new Suivi($db);
                                    $suivi_referentiel->indicateur_id = $referentiel['id'];
                                    $suivis_raw = $suivi_referentiel->readByIndicateur();

                                    $annees = array();
                                    if (!empty($cibles_raw) || !empty($suivis_raw)) {
                                        $all_years = array();
                                        if (!empty($cibles_raw)) {
                                            $cible_years = array_column($cibles_raw, 'annee');
                                            $all_years = array_merge($all_years, $cible_years);
                                        }

                                        if (!empty($suivis_raw)) {
                                            $suivi_years = array_column($suivis_raw, 'annee');
                                            $all_years = array_merge($all_years, $suivi_years);
                                        }

                                        if (!empty($all_years)) {
                                            $min_year = min($all_years);
                                            $max_year = max($all_years);

                                            for ($year = $min_year; $year <= $max_year; $year++) {
                                                $annees[] = $year;
                                            }
                                        }
                                    }

                                    $cibles_map = array();
                                    if (!empty($cibles_raw)) {
                                        usort($cibles_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                        foreach ($cibles_raw as $item) {
                                            $year = $item['annee'];
                                            $value = (float)$item['valeur'];
                                            if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
                                            $cibles_map[$year] += $value;
                                            $cibles_total += $value;
                                        }
                                        ksort($cibles_map, SORT_NUMERIC);
                                    }

                                    $suivis_map = array();
                                    if (!empty($suivis_raw)) {
                                        usort($suivis_raw, fn($a, $b) => $a['annee'] - $b['annee']);
                                        foreach ($suivis_raw as $item) {
                                            $year = $item['annee'];
                                            $value = (float)$item['valeur'];
                                            if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
                                            $suivis_map[$year] += $value;
                                            $suivis_total += $value;
                                        }
                                        ksort($suivis_map, SORT_NUMERIC);
                                    }

                                    $cibles = array();
                                    $suivis = array();
                                    if (!empty($annees)) {
                                        foreach ($annees as $year) {
                                            $cibles[] = (float)($cibles_map[$year] ?? 0);
                                            $suivis[] = (float)($suivis_map[$year] ?? 0);
                                        }
                                    }
                                }

                                $taux_progress = $cibles_total > 0 ? ($suivis_total / $cibles_total) * 100 : 0;
                            ?>
                                <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                    <td class="align-middle px-2 py-0"> <?php echo $referentiel['code']; ?> </td>
                                    <td class="align-middle px-2"> <?php echo $referentiel['intitule']; ?> </td>
                                    <td class="align-middle px-2 py-0 text-nowrap"><?php echo $referentiel['unite']; ?></td>

                                    <td class="align-middle px-2 py-0">
                                        <?php foreach ($structures as $structure): ?>
                                            <?php if ($structure['id'] == $referentiel['responsable']): ?>
                                                <?php echo $structure['sigle']; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="align-middle text-center px-2 py-0">
                                        <a class="btn btn-link text-decoration-none fw-bold p-0 m-0" data-bs-toggle="modal" data-bs-target="#viewRefCMRModal" aria-haspopup="true" aria-expanded="false"
                                            data-id="<?php echo $referentiel['id']; ?>">
                                            <?php if ($nbre_mesure > 0): ?>
                                                (<?php echo $nbre_mesure; ?>) mesure
                                            <?php else: ?>
                                                Aucune mesure
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="align-middle text-center px-2 py-0">
                                        <a class="btn btn-link text-decoration-none fw-bold p-0 m-0" data-bs-toggle="modal" data-bs-target="#viewRefCMRModal" aria-haspopup="true" aria-expanded="false"
                                            data-id="<?php echo $referentiel['id']; ?>">
                                            <?php if ($nbre_cmr > 0): ?>
                                                (<?php echo $nbre_cmr; ?>) indicateur
                                            <?php else: ?>
                                                Aucun indicateur
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo strtoupper($cibles_total ?? '-'); ?> </td>
                                    <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo strtoupper($suivis_total ?? '-'); ?> </td>
                                    <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo round($taux_progress, 2) . ' %' ?? '-'; ?> </td>
                                    <td class="align-middle px-2 py-0">
                                        <?php if ($referentiel['categorie'] == 'impact') { ?>
                                            <button title="Suivre" type="button" class="btn btn-subtle-warning rounded-pill btn-sm fw-bold fs-9 px-2 py-1"
                                                onclick="window.location.href = 'referentiel_view.php?id=<?php echo $referentiel['id']; ?>';">Suivre
                                            </button>
                                        <?php } else {
                                            echo '-';
                                        } ?>
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