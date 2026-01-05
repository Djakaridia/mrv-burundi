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

    $structure = new Structure($db);
    $structures = $structure->read();
    $structures = array_filter($structures, function ($structure) {
        return $structure['state'] == 'actif';
    });

    $unite = new Unite($db);
    $unites = $unite->read();

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
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <div class="col-lg-6 mb-2 mb-lg-0">
                        <h4 class="my-1 fw-black">Résultats obtenus sur les indicateurs de la CDN</h4>
                    </div>

                    <div class="col-lg-6 mb-2 mb-lg-0 text-lg-end">
                        <a href="referentiels.php" class="btn btn-phoenix-info rounded-pill btn-sm">
                            <span class="fa-solid fa-eye fs-9 me-2"></span>Voir les indicateurs
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <?php if (!empty($referentiels)) { ?>
                            <div class="table-responsive mx-n1 px-1 scrollbar" style="min-height: 432px;">
                                <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                                    <thead class="bg-secondary-subtle">
                                        <tr>
                                            <th class="sort align-middle px-2" scope="col">Code</th>
                                            <th class="sort align-middle px-2" scope="col">Intitulé</th>
                                            <th class="sort align-middle px-2" scope="col">Unité</th>
                                            <th class="sort align-middle px-2" scope="col">Catégorie</th>
                                            <th class="sort align-middle px-2" scope="col">Responsables</th>
                                            <th class="sort align-middle px-2" scope="col">Indicateur CMR</th>
                                            <th class="sort align-middle px-2" scope="col">Total prévu</th>
                                            <th class="sort align-middle px-2" scope="col">Total réalisé</th>
                                            <th class="sort align-middle px-2" scope="col">Taux réalisation</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list" id="table-latest-review-body">
                                        <?php foreach ($referentiels as $referentiel):
                                            $indicateur = new Indicateur($db);
                                            $indicateur->referentiel_id = $referentiel['id'];
                                            $indicateur_cmr = $indicateur->readByReferentiel();
                                            $nbre_cmr = count($indicateur_cmr);

                                            $total_prevu = 0;
                                            $total_realise = 0;
                                            if (isset($indicateur_cmr)) {
                                                $cmr_tab_prevu = [];
                                                $cmr_tab_realise = [];
                                                foreach ($indicateur_cmr as $cmr) {
                                                    $cmr_tab_prevu[$cmr['id']] = $cmr['valeur_cible'];

                                                    $suivi = new Suivi($db);
                                                    $suivi->cmr_id = $cmr['id'];
                                                    $suivis_cmr = $suivi->readByCMR();
                                                    $suivis_cmr_grouped = array();
                                                    $suivis_calcul = 0;
                                                    foreach ($suivis_cmr as $suivi) {
                                                        $suivis_cmr_grouped[$suivi['annee']][] = $suivi;
                                                    }
                                                    foreach ($suivis_cmr_grouped as $annee => $suivis) {
                                                        $suivis_calcul += calculSuiviData($suivis_cmr_grouped[$annee] ?? [], $cmr['mode_calcul']);
                                                    }
                                                    $cmr_tab_realise[$cmr['id']] = $suivis_calcul;
                                                }

                                                $total_prevu += array_sum($cmr_tab_prevu);
                                                $total_realise += array_sum($cmr_tab_realise);

                                                $taux_progress = 0;
                                                if (isset($total_prevu) && $total_prevu > 0) {
                                                    $taux_progress = $total_realise / $total_prevu * 100;
                                                }
                                            }
                                        ?>
                                            <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                                                <td class="align-middle px-2 py-0"> <?php echo $referentiel['code']; ?> </td>
                                                <td class="align-middle px-2"> <?php echo $referentiel['intitule']; ?> </td>
                                                <td class="align-middle px-2 py-0">
                                                    <?php foreach ($unites as $unite) { ?>
                                                        <?php if ($unite['id'] == $referentiel['unite']) { ?>
                                                            <?php echo $unite['name']; ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>

                                                <td class="align-middle px-2 py-0"> <?php echo strtoupper($referentiel['categorie'] ?? '-'); ?> </td>
                                                <td class="align-middle px-2 py-0">
                                                    <?php foreach ($structures as $structure): ?>
                                                        <?php if ($structure['id'] == $referentiel['responsable']): ?>
                                                            <?php echo $structure['sigle']; ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    <?php foreach ($structures as $structure): ?>
                                                        <?php if (in_array($structure['id'], explode(',', str_replace('"', '', $referentiel['autre_responsable'] ?? ""))) && $structure['id'] != $referentiel['responsable']): ?>
                                                            <?php echo "/ " . $structure['sigle']; ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
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
                                                <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo strtoupper($total_prevu ?? '-'); ?> </td>
                                                <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo strtoupper($total_realise ?? '-'); ?> </td>
                                                <td class="align-middle bg-secondary-subtle px-2 py-0"> <?php echo round($taux_progress, 2) . ' %' ?? '-'; ?> </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center py-5 my-5" style="min-height: 350px;">
                                <div class="d-flex justify-content-center mb-3">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                                        <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <h4 class="text-800 mb-3">Aucun projet trouvé</h4>
                                <p class="text-600 mb-5">Veuillez ajouter un projet pour afficher ses indicateurs</p>
                                <a href="projects.php" class="btn btn-primary px-5 fs-8" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-arrow-right"></i> Aller vers les projets</a>
                            </div>
                        <?php } ?>
                    </div>
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