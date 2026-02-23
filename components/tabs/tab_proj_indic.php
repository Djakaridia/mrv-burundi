<div class="mb-9">
    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">Liste des indicateurs</h4>
        </div>

        <div class="col-auto d-flex gap-2">
            <a href="suivi_indicateurs.php?proj=<?= $project_curr['id'] ?>" class="btn btn-phoenix-info rounded-pill btn-sm"><span class="fa-solid fa-bar-chart fs-9 me-2"></span>Suivre</a>
            <button title="Ajouter un indicateur" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-projet_id="<?php echo $project_curr['id'] ?>" data-bs-target="#addIndicateurModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un indicateur</button>
        </div>
    </div>

    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1">
        <div class="table-responsive p-1 scrollbar">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                <thead class="bg-primary-subtle text-nowrap">
                    <tr>
                        <th class="align-middle" scope="col">Code</th>
                        <th class="align-middle" scope="col">Intitule</th>
                        <th class="align-middle" scope="col">Unité</th>
                        <th class="align-middle text-center">Facteur d'émission</th>
                        <th class="align-middle text-center">Référence</th>
                        <?php for ($year = date('Y', strtotime($project_curr['start_date'])); $year <= date('Y', strtotime($project_curr['end_date'])); $year++) : ?>
                            <th class="sort align-middle bg-light dark__bg-secondary border text-center" scope="col"><?php echo $year; ?></th>
                        <?php endfor; ?>
                        <th class="sort align-middle text-center" scope="col">Cible</th>
                        <th class="sort align-middle text-center" scope="col">Annuelles</th>
                        <th class="sort align-middle text-center" scope="col" style="min-width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    <?php foreach ($indicateurs_project as $cmr) {
                        $cible = new Cible($db);
                        $cible->cmr_id = $cmr['id'];
                        $cibles_cmr = $cible->readByCMR();

                        $ciblesGroupedAnnee = $ciblesAnneeSomme = [];
                        if ($cibles_cmr) {
                            foreach ($cibles_cmr as $cible) $ciblesGroupedAnnee[$cible['annee']][] = $cible;
                            foreach ($ciblesGroupedAnnee as $annee => $cibles) $ciblesAnneeSomme[$annee] = array_sum(array_column($cibles, 'valeur'));
                        }

                        $indic_facteur = isset($grouped_facteurs[$cmr['facteur_id']]) ? $grouped_facteurs[$cmr['facteur_id']] : [];
                    ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                            <td class="align-middle px-2 py-0"><?php echo $cmr['code']; ?></td>
                            <td class="align-middle px-2"><?php echo $cmr['intitule']; ?></td>
                            <td class="align-middle px-2 py-0"><?php echo $cmr['unite']; ?></td>
                            <td class="align-middle px-2 py-0"><?php echo $indic_facteur['name'] ?? '-'; ?></td>
                            <td class="align-middle px-2 py-0 text-center"><?php echo $cmr['valeur_reference']; ?></td>

                            <?php for ($year = date('Y', strtotime($project_curr['start_date'])); $year <= date('Y', strtotime($project_curr['end_date'])); $year++) : ?>
                                <td class="align-middle bg-light dark__bg-secondary px-2 py-0 border text-center">
                                    <?php if (empty($cibles_cmr)) { ?>
                                        <span class="text-muted">-</span>
                                    <?php } else { ?>
                                        <?php echo $ciblesAnneeSomme[$year] ?? "-"; ?>
                                    <?php } ?>
                                </td>
                            <?php endfor; ?>
                            <td class="align-middle px-2 py-0 text-center"><?php echo $cmr['valeur_cible']; ?></td>

                            <td class="align-middle px-2 py-0 text-center">
                                <button title="Modifier" type="button" class="btn btn-subtle-primary rounded-pill btn-sm fw-bold fs-9 px-2 py-1" data-bs-toggle="modal"
                                    data-bs-target="#newIndicateurCibleModal" aria-haspopup="true" aria-expanded="false"
                                    data-cmr_id="<?php echo $cmr['id']; ?>" data-projet_id="<?php echo $project_curr['id']; ?>">Modifier
                                </button>
                            </td>

                            <td class="align-middle text-center">
                                <div class="position-relative">
                                    <div class="">
                                        <?php if (checkPermis($db, 'update')) : ?>
                                            <button title="Modifier" data-bs-toggle="modal" data-bs-target="#addIndicateurModal" data-id="<?php echo $cmr['id']; ?>" data-projet_id="<?php echo $cmr['projet_id']; ?>" aria-haspopup="true" aria-expanded="false"
                                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                <span class="uil-pen fs-8"></span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (checkPermis($db, 'delete')) : ?>
                                            <button title="Supprimer" onclick="deleteData(<?= $cmr['id'] ?>, 'Voulez-vous vraiment supprimer cet indicateur ?', 'indicateurs')"
                                                class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1">
                                                <span class="uil-trash-alt fs-8"></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>