<div class="mb-9 mt-2">
    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="fw-black">Rapport de synthèse des données </h4>
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
                                <img class="p-1" src="<?php echo (is_file("./assets/images/logo-full.png")) ? './assets/images/logo-full.png' : './images/image_none.png'; ?>" alt="" style="height: 60px" />
                                <div>
                                    <div class="fw-bold fs-16">MRV - Burundi</div>
                                    <div class="fs-12">
                                        <?php echo $project_curr['name'] . " (" . $project_curr['code'] . ")" ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6 d-flex align-items-center justify-content-end">
                                <p class=" fs-12 text-end">Rapport sur l'état d'avancement et les
                                    résultats de la mise en œuvre</p>
                            </div>
                        </div>
                        <hr class="border border-primary mb-3 w-100">

                        <div class="p-3">
                            <h2 class="text-primary text-center mb-3 fw-bold">
                                <?php echo $project_curr['name'] . " (" . $project_curr['code'] . ")" ?>
                            </h2>
                            <table border="1" class="table w-100">
                                <tr class="border">
                                    <td class="p-2 text-center">
                                        <?php echo $project_curr['name'] ?> | FY
                                        <?php echo date("Y") ?> | Seq No: 1 | Archived on
                                        <?php echo date("Y-m-d") ?></td>
                                </tr>
                            </table>
                        </div>
                    </header>

                    <div class="p-3">
                        <div class="accordion" id="accordionFlushExample">
                            <div class="accordion-item">
                                <button type="button" id="litem_lib_1"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_1"
                                    aria-expanded="false">
                                    Chapitre 1 : IDENTIFICATION DU PROJET
                                </button>
                                <div id="litem_1" class="accordion-collapse collapse show">
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Intitulé du projet : </strong></td>
                                            <td><?php echo $project_curr['name'] . " (" . $project_curr['code'] . ")" ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Code IATI : </strong></td>
                                            <td><?php echo $project_curr['code'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Partenaire de mise en œuvre : </strong></td>
                                            <td>
                                                <?php
                                                if (count($structures) > 0) {
                                                    foreach ($structures as $structure) {
                                                        if ($structure['id'] == $project_curr['structure_id']) {
                                                            echo html_entity_decode($structure['description']) . ' <b>(' . $structure["sigle"] . ')</b>';
                                                        }
                                                    }
                                                } else {
                                                    echo 'Aucune structure trouvée';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Date
                                                    démarage : </strong></td>
                                            <td> <?php echo $project_curr['start_date'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Date
                                                    clôture : </strong></td>
                                            <td> <?php echo $project_curr['end_date'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light text-nowrap text-end"><strong>Durée du
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


                            <div class="accordion-item">
                                <button type="button" id="litem_lib_2"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_2"
                                    aria-expanded="false">
                                    Chapitre 2 : DETAILS FINANCIERS
                                </button>
                                <div id="litem_2" class="" style="padding: 10px;">
                                    <?php if (isset($pr_conventions)) { ?>

                                        <div class="border-top mb-3 pt-1">
                                            <strong>Source de Financement :</strong>
                                            <ul>
                                                <?php foreach ($pr_conventions as $convention) { ?>
                                                    <li class="my-1"><?php echo $convention["libelle"] ?> :
                                                        <strong><?php echo number_format($convention["montant"], 0, ',', ' ') . " USD"; ?></strong>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <div class="border-top mb-3 pt-1"><strong>Budget Total : </strong>
                                            <?php echo number_format($project_curr["budget"], 0, ',', ' ')." USD"; ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" id="litem_lib_3"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_3"
                                    aria-expanded="false">
                                    Chapitre 3 : DESCRIPTION DU PROJET
                                </button>
                                <div id="litem_3" class="accordion-collapse collapse show">
                                    <div class="text-primary fw-bold fs-16 mb-1 border-bottom">Objectifs</div>
                                    <div>
                                        
                                    </div>

                                    <div class="text-primary fw-bold fs-16 mb-1 border-bottom">Résultats</div>
                                    <?php if (isset($pr_resultats) && count($pr_resultats) > 0) { ?>
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
                                    <?php } else { ?>
                                        <p class="text-start mx-3">Aucun objectif ou résultat trouvé</p>
                                    <?php } ?>
                                </div>
                            </div>


                            <div class="accordion-item">
                                <button type="button" id="litem_lib_4"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_4"
                                    aria-expanded="false">
                                    Chapitre 4 : SUIVI DU CADRE DE RÉSULTATS DES INDICATEURS POUR <?= date("Y") ?>
                                </button>
                                <div id="litem_4" class="accordion-collapse collapse show">
                                    <table
                                        class="table table-hover table-bordered table-striped-columns small"
                                        border="1" style="width:100%; table-layout: fixed;">
                                        <thead class="bg-secondary-subtle text-nowrap">
                                            <!-- <tr>
                                                                <th colspan="8" class="bg-warning text-white text-center fs-16">Suivi des Indicateurs pour l’année <?php echo date("Y") ?></th>
                                                            </tr> -->
                                            <tr class="text-center">
                                                <th style="width: 30%;" class="align-items-center"
                                                    rowspan="2">Nom de l’indicateur</th>
                                                <th style="width: 20%;" colspan="2">Référence</th>
                                                <th style="width: 20%;" colspan="2">Cible</th>
                                                <th style="width: 30%;" colspan="3">Actuelle</th>
                                            </tr>
                                            <tr class="text-center">
                                                <th>Valeur</th>
                                                <th>Mois/Année</th>
                                                <th>Valeur</th>
                                                <th>Mois/Année</th>
                                                <th>Valeur</th>
                                                <th>Progression (%)</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>

                                        <?php if (isset($data_Indicateur) && count($data_Indicateur) > 0) { ?>
                                            <tbody>
                                                <?php foreach ($data_Indicateur as $indic) { ?>
                                                    <tr>
                                                        <td><?php echo $indic["indic_name"] ?></td>
                                                        <td class="text-center">
                                                            <?php echo $indic["val_refer"] ?></td>
                                                        <td class="text-center">
                                                            <?php echo $indic["date_refer"] ?></td>
                                                        <td class="text-center">
                                                            <?php echo $indic["val_cible"] ?></td>
                                                        <td class="text-center">
                                                            <?php echo $indic["date_cible"] ?></td>
                                                        <td class="text-center"><?php echo $indic["val_curr"] ?>
                                                        </td>
                                                        <td class="text-center bg-warning-subtle">
                                                            <?php echo $indic["val_progr"] ?></td>
                                                        <td class="text-center">
                                                            <?php echo $indic["date_curr"] ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" aria-expanded="false"
                                    style="width:100%; text-align: start" aria-controls="litem_5"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_5">
                                    Chapitre 5 : SUIVI DES ACTIVITÉS DU PTBA <?php echo date("Y") ?>
                                </button>
                                <div id="litem_5" class="accordion-collapse collapse show">
                                    <table
                                        class="table table-hover table-bordered table-striped-columns small"
                                        border="1" style="width:100%; table-layout: fixed;">
                                        <thead class="bg-secondary-subtle text-nowrap">
                                            <tr class="text-center">
                                                <th class="text-center" style="width: 8%;">Code</th>
                                                <th class="text-center" style="width: 25%;">Activités
                                                </th>
                                                <th class="text-center" style="width: 8%;">Responsables
                                                </th>
                                                <th class="text-center" style="width: 10%;">Acteurs</th>
                                                <th class="text-center" style="width: 8%;">Période</th>
                                                <th class="text-center" style="width: 8%;">Tache</th>
                                                <th class="text-center" style="width: 8%;">Indicateur
                                                </th>
                                                <th class="text-center" style="width: 10%;">Statut</th>
                                                <th class="text-center" style="width: 10%;">Observation
                                                </th>
                                            </tr>
                                        </thead>

                                        <?php if (isset($data_Suivi_PTBA) && count($data_Suivi_PTBA) > 0) { ?>
                                            <tbody>
                                                <?php foreach ($data_Suivi_PTBA as $suivi) { ?>
                                                    <tr>
                                                        <td><?php echo $suivi["code"] ?></td>
                                                        <td><?php echo $suivi["activ"] ?></td>
                                                        <td class="text-center"><?php echo $suivi["respon"] ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $suivi["actor"] ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $suivi["period"] ?>
                                                        </td>
                                                        <td class="text-center bg-success-subtle">
                                                            <?php echo $suivi["tache"] ?></td>
                                                        <td class="text-center bg-danger-subtle">
                                                            <?php echo $suivi["indic"] ?></td>
                                                        <td class="text-center bg-warning-subtle">
                                                            <?php echo $suivi["statu"] ?></td>
                                                        <td class="text-center"><?php echo $suivi["commen"] ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <button type="button" aria-expanded="false"
                                    style="width:100%; text-align: start" aria-controls="litem_6"
                                    class="btn-sm bg-primary rounded-0 text-white text-start accordion-button collapsed fw-bold btn fs-16 py-2 my-1"
                                    data-bs-toggle="collapse" data-bs-target="#litem_6">
                                    Chapitre 6 : ETAT D'EXÉCUTION DES ACTIVITÉS DU PTBA
                                    <?php echo date("Y") ?> PAR COMPOSANTE
                                </button>
                                <div id="litem_6" class="accordion-collapse collapse show">
                                    <table
                                        class="table table-hover table-bordered table-striped-columns small"
                                        border="1" style="width:100%; table-layout: fixed;">
                                        <thead class="bg-secondary-subtle text-nowrap">
                                            <tr class="text-center">
                                                <th style="width: 15%;" class="text-center">Code</th>
                                                <th style="width: 30%;" class="text-center">Intitulés
                                                </th>
                                                <th style="width: 10%;" class="text-center">Activités
                                                    Planifiées</th>
                                                <th style="width: 10%;" class="text-center">Activités
                                                    Réalisées</th>
                                                <th style="width: 10%;" class="text-center">Avancement
                                                    Technique</th>
                                            </tr>
                                        </thead>

                                        <?php if (isset($data_Exec_PTBA) && count($data_Exec_PTBA) > 0) {
                                            $total_plan = $total_real = $taux_glob = 0
                                        ?>
                                            <tbody>
                                                <?php foreach ($data_Exec_PTBA as $exec) {
                                                    if ($exec["niveau"] == 1) {
                                                        $total_plan = $total_plan + $exec["planifie"];
                                                        $total_real = $total_real + $exec["realise"];
                                                        $taux_glob = $total_plan > 0 ? round($total_real / $total_plan, 2) * 100 : 0;
                                                    }
                                                ?>
                                                    <tr
                                                        class="<?php echo $exec["niveau"] == 1 ? "bg-success-subtle" : ($exec["niveau"] == 2 ? "bg-danger-subtle" : "") ?>">
                                                        <td
                                                            class="<?php echo ($exec["niveau"] == '0') ? 'text-primary' : '' ?> ">
                                                            <?php for ($k = 0; $k < $exec["niveau"]; $k++)
                                                                echo "<span class='text-light'>&nbsp;| </span>";
                                                            echo $exec["code"] ?>
                                                        </td>
                                                        <td><?php echo $exec["intitule"] ?></td>
                                                        <td class="text-center"><?php echo $exec["planifie"] ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $exec["realise"] ?>
                                                        </td>
                                                        <td class="text-center"><?php echo $exec["taux"] ?></td>
                                                    </tr>
                                                <?php } ?>
                                                <tr class="bg-light fw-bold">
                                                    <td colspan="2" class="text-center">Réalisation globale
                                                        des activités</td>
                                                    <td class="text-center"><?= $total_plan ?></td>
                                                    <td class="text-center"><?= $total_real ?></td>
                                                    <td class="text-center"><?= $taux_glob ?> %</td>
                                                </tr>
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