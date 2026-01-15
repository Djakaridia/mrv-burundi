<div class="mb-9 mt-2">
    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="fw-black">Rapport de synthèse des données</h4>
        </div>

        <div class="col-auto">
            <form action="gen_rappor_projet.php" method="post"
                class=" d-flex justify-content-center">
                <button type="submit" class="btn btn-sm btn-primary waves-effect" disabled>
                    <i class="ri-file-word-2-line me-1"></i>Génerer le DOCX
                </button>
            </form>
        </div>
    </div>

    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1 p-3">
        <div class="card">
            <div class="card-body border border-primary-subtle rounded-1 table-responsive"
                style="min-height: 400px;">

                <?php if ($project_curr['code'] == "") { ?>
                    <div class="alert alert-danger py-5" role="alert">
                        <h4 class="alert-heading text-center mb-3">Aucun projet selectionné</h4>
                        <p class="text-center">Veuillez selectionner un projet pour afficher les données.
                        </p>
                    </div>
                <?php } else { ?>
                    <header class="shadow border border-rounded p-3 bg-primary-subtle">
                        <div class="row g-3">
                            <div class="col-6 d-flex align-items-center">
                                <img class="p-1" src="<?php echo (is_file("./assets/images/logo-full.png")) ? './assets/images/logo-full.png' : './images/image_none.png'; ?>" alt="" style="height: 50px" />
                                <div class="ms-3">
                                    <div class="fw-bold fs-9">MRV - Burundi</div>
                                    <div class="fs-9">Rapport sur l'état d'avancement et les résultats de la mise en œuvre</div>
                                </div>
                            </div>

                            <div class="col-6 d-flex align-items-center justify-content-end">
                                <div class="fw-bold fs-9">Office Burundaise pour la Protection de l'Environnement (OBPE)</div>
                            </div>
                        </div>
                        <hr class="border border-primary mb-3 w-100">

                        <div class="p-3">
                            <h3 class="text-primary text-center mb-3 fw-bold">
                                <?php echo html_entity_decode($project_curr['name']) . " (" . $project_curr['code'] . ")" ?>
                            </h3>
                            <table border="1" class="table w-100">
                                <tr class="border">
                                    <td class="p-2 text-center">
                                        <?php echo html_entity_decode($project_curr['name']) ?> | FY
                                        <?php echo date("Y") ?> | Seq No: 1 | Archived on
                                        <?php echo date("Y-m-d") ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </header>

                    <div class="p-3">
                        <div class="accordion" id="accordionFlushExample">
                            <div class="accordion-item border-0">
                                <button type="button" id="litem_lib_1"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_1"
                                    aria-expanded="false">
                                    Chapitre 1 : IDENTIFICATION DU PROJET
                                </button>
                                <div id="litem_1" class="accordion-collapse collapse show">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Intitulé du projet : </strong></td>
                                            <td><?php echo html_entity_decode($project_curr['name']) . " (" . $project_curr['code'] . ")" ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Code IATI : </strong></td>
                                            <td><?php echo $project_curr['code'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Partenaire de mise en œuvre : </strong></td>
                                            <td>
                                                <?php
                                                if (count($structures) > 0) {
                                                    foreach ($structures as $structure) {
                                                        if ($structure['id'] == $project_curr['structure_id']) echo html_entity_decode($structure['description']) . ' <b>(' . $structure["sigle"] . ')</b>';
                                                    }
                                                } else {
                                                    echo 'Aucune structure trouvée';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Date
                                                    démarage : </strong></td>
                                            <td> <?php echo $project_curr['start_date'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Date
                                                    clôture : </strong></td>
                                            <td> <?php echo $project_curr['end_date'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end text-primary"><strong>Durée du
                                                    Projet : </strong></td>
                                            <td>
                                                <?php
                                                $debut = $project_curr['start_date'];
                                                $fin = $project_curr['end_date'];
                                                $date1 = new DateTime($debut);
                                                $date2 = new DateTime($fin);
                                                $interval = $date1->diff($date2);

                                                $years = $interval->y;
                                                $months = $interval->m;
                                                $days = $interval->d;
                                                $result = "";
                                                if ($years <= 0 && $months > 0) {
                                                    $result = "$months mois et $days jours.";
                                                } else if ($years <= 0 && $months <= 0) {
                                                    $result = $result = "$days jours.";
                                                } else if ($years > 0 && $months == 0 && $days > 0) {
                                                    $result = "$years an et $days jours.";
                                                } else if ($years > 0 && $months > 0 && $days == 0) {
                                                    $result = "$years an et $months mois.";
                                                } else if ($years > 0 && $months == 0 && $days == 0) {
                                                    $result = "$years an.";
                                                } else {
                                                    $result = "$years an, $months mois, et $days jours.";
                                                }
                                                echo "$result";
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="accordion-item border-0">
                                <button type="button" id="litem_lib_2"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_2"
                                    aria-expanded="false">
                                    Chapitre 2 : DETAILS FINANCIERS
                                </button>
                                <div id="litem_2" class="accordion-collapse collapse show" style="padding: 10px;">
                                    <?php if (isset($structures_project)) { ?>
                                        <div class="mb-3">
                                            <div class="text-primary fs-8 fw-bold mb-1 border-bottom">Source de Financement :</div>
                                            <ul>
                                                <?php foreach ($conventions_project as $convention) { ?>
                                                    <li class="my-1"><?php echo $convention["name"] ?> :
                                                        <span class="fw-bold">
                                                            <?php echo number_format($convention["montant"], 0, ',', ' ') . " USD"; ?>
                                                        </span>
                                                        <?php foreach ($structures_project as $structure) { ?>
                                                            <?php if ($structure['id'] == $convention['structure_id']) { ?>
                                                                <i class="fa fa-arrow-right mx-1 text-primary"></i>
                                                                <?php echo html_entity_decode($structure['description']) . ' <b>(' . $structure["sigle"] . ')</b>'; ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <div class="mb-3">
                                            <div class="text-primary fs-8 fw-bold mb-1 border-bottom">Budget Total :</div>
                                            <?php echo number_format($project_curr["budget"], 0, ',', ' ') . " USD"; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="accordion-item border-0">
                                <button type="button" id="litem_lib_3"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_3"
                                    aria-expanded="false">
                                    Chapitre 3 : DESCRIPTION DU PROJET
                                </button>
                                <div id="litem_3" class="accordion-collapse collapse show">
                                    <?php if (!empty($project_curr['objectif'])) { ?>
                                        <div class="text-primary fs-8 fw-bold mb-1 border-bottom">Objectifs</div>
                                        <div class="text-justify"><?= html_entity_decode($project_curr['objectif']) ?></div>
                                    <?php } ?>

                                    <?php if (!empty($project_curr['description'])) { ?>
                                        <div class="text-primary fs-8 fw-bold mb-1 border-bottom">Description</div>
                                        <div class="text-justify"><?= html_entity_decode($project_curr['description']) ?></div>
                                    <?php } ?>

                                    <?php if (isset($pr_resultats) && count($pr_resultats) > 0) { ?>
                                        <div class="text-primary fs-8 fw-bold mb-1 border-bottom">Résultats</div>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($pr_resultats as $resultat) { ?>
                                                <li class="list-group-item"><strong>Niveau
                                                        <?php echo $resultat["niveau"] ?> :
                                                        <?php echo $resultat["libelle"] ?></strong></li>

                                                <?php if (isset($resultat["indicateurs"]) > 0) { ?>
                                                    <ul class="d-flex flex-column gap-1">
                                                        <?php foreach ($resultat["indicateurs"] as $indicateur) { ?>
                                                            <li><?php echo $indicateur ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                <?php } else { ?>
                                                    <p class="text-start mx-3">Aucun indicateur trouvé</p>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                            </div>


                            <div class="accordion-item border-0">
                                <button type="button" id="litem_lib_4"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_4"
                                    aria-expanded="false">
                                    Chapitre 4 : SUIVI DU CADRE DE RÉSULTATS DES INDICATEURS POUR <?= date("Y") ?>
                                </button>
                                <div id="litem_4" class="accordion-collapse collapse show">
                                    <table
                                        class="table table-hover table-bordered small"
                                        border="1" style="width:100%; table-layout: fixed;">
                                        <thead class="bg-secondary-subtle text-nowrap">
                                            <tr class="text-center">
                                                <th style="width: 30%;" class="align-items-center" rowspan="3">Intitulé de l’indicateur</th>
                                                <th style="width: 20%;" colspan="2">Référence</th>
                                                <th style="width: 20%;" colspan="2">Valeur Cible</th>
                                                <th style="width: 30%;" colspan="4">Valeur Réalisée</th>
                                            </tr>
                                            <tr class="text-center">
                                                <th rowspan="2">Valeur</th>
                                                <th rowspan="2">Année</th>
                                                <th colspan="2"><?php echo date("Y") ?></th>
                                                <th colspan="2"><?php echo date("Y") ?></th>
                                                <th colspan="2">Progression (%)</th>
                                            </tr>
                                            <tr>
                                                <th>Incon.</th>
                                                <th>Con.</th>
                                                <th>Incon.</th>
                                                <th>Con.</th>
                                                <th>Incon.</th>
                                                <th>Con.</th>
                                            </tr>
                                        </thead>

                                        <?php if (isset($indicateurs_project) && count($indicateurs_project) > 0) { ?>
                                            <tbody>
                                                <?php foreach ($indicateurs_project as $indic) { ?>
                                                    <tr>
                                                        <td><?php echo $indic["intitule"] ?></td>
                                                        <td class="text-center"><?php echo $indic["valeur_reference"] ?></td>
                                                        <td class="text-center"><?php echo $indic["annee_reference"] ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                        <td class="text-center"><?php echo "-" ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>

                            <div class="accordion-item border-0">
                                <button type="button" aria-expanded="false"
                                    style="width:100%; text-align: start" aria-controls="litem_5"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_5">
                                    Chapitre 5 : SUIVI DES ACTIVITÉS DU PROJET <?php echo date("Y") ?>
                                </button>
                                <div id="litem_5" class="accordion-collapse collapse show">
                                    <table
                                        class="table table-hover table-bordered small"
                                        border="1" style="width:100%; table-layout: fixed;">
                                        <thead class="bg-secondary-subtle text-nowrap">
                                            <tr class="text-center">
                                                <th class="text-center" style="width: 5%;">Code</th>
                                                <th class="text-center" style="width: 35%;">Activités</th>
                                                <th class="text-center" style="width: 15%;">Responsables</th>
                                                <th class="text-center" style="width: 15%;">Indicateur</th>
                                                <th class="text-center" style="width: 10%;">Cout(USD)</th>
                                                <th class="text-center" style="width: 10%;">Statut</th>
                                                <th class="text-center" style="width: 10%;">Observation</th>
                                            </tr>
                                        </thead>

                                        <?php if (isset($taches_project) && count($taches_project) > 0) { ?>
                                            <tbody>
                                                <?php foreach ($taches_project as $tache) {
                                                    $indicateurs = $grouped_tache_indicateurs[$tache['id']] ?? [];

                                                    $nbre_indicateurs = count($indicateurs);
                                                    $couts = $grouped_tache_couts[$tache['id']] ?? [];
                                                    $total_couts = array_sum(array_map('floatval', array_column($couts, 'montant')));

                                                    $total_task_cible = array_sum(array_map('floatval', array_column($indicateurs, 'valeur_cible')));
                                                    $suivis_indic = $grouped_tache_suivi_indicateurs[$tache['id']] ?? [];
                                                    $total_task_suivi = array_sum(array_map('floatval', array_column($suivis_indic, 'valeur_suivi')));

                                                    unset($taux_indicateur);
                                                    $taux_indicateur = 0;
                                                    if (isset($total_task_cible) && $total_task_cible > 0) {
                                                        if ($total_task_suivi >= $total_task_cible) {
                                                            $taux_indicateur = 100;
                                                        } else {
                                                            $taux_indicateur = round(($total_task_suivi / $total_task_cible) * 100, 2);
                                                        }
                                                    } else {
                                                        $taux_indicateur = 0;
                                                    }
                                                ?>
                                                    <tr>
                                                        <td><?php echo $tache["code"] ?></td>
                                                        <td><?php echo $tache["name"] ?></td>
                                                        <td class="text-center"><?php echo $grouped_users[$tache['assigned_id']]['nom'] . ' ' . $grouped_users[$tache['assigned_id']]['prenom'] ?></td>
                                                        <td class="text-center">
                                                            <?php
                                                            if ($taux_indicateur < 39)
                                                                $color = "danger";
                                                            elseif ($taux_indicateur < 69)
                                                                $color = "warning";
                                                            elseif ($taux_indicateur >= 70)
                                                                $color = "success"; ?>
                                                            <span id="tauxIndic_<?php echo $tache['id']; ?>">
                                                                <div class="progress progress-xl rounded-0 p-0 m-0" style="height: 1.5rem; width: 150px">
                                                                    <div class="progress-bar progress-bar-striped progress-bar-animated fs-14 fw-bold bg-<?php echo $color; ?> " aria-valuenow="70" style="width: 100%;">
                                                                        <?php echo (isset($taux_indicateur) && $taux_indicateur > 0) ? $taux_indicateur . " %" : "Non suivie"; ?>
                                                                    </div>
                                                                </div>
                                                            </span>
                                                        </td>
                                                        <td class="text-center"><?= ($total_couts > 0) ? number_format($total_couts, 0, ',', ' ') : "Ajouter" ?></td>
                                                        <td class="text-center">
                                                            <span class="col text-nowrap badge badge-phoenix fs-10 
                                                            badge-phoenix-<?php echo $taux_indicateur > 0 ? ($taux_indicateur >= 100 ? "success" : "warning") : "danger" ?>">
                                                                <?php echo $taux_indicateur > 0 ? ($taux_indicateur >= 100 ? "Terminé" : "En cours") : "Non entamé" ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center text-capitalize"><?php echo isset($tache['status']) ? $tache['status'] : "Suivre" ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</div>