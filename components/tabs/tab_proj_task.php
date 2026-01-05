<div class="mb-9">
    <div class="mb-2 d-lg-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="fw-black">Liste des activités</h4>
        </div>

        <div class="col-auto d-flex gap-2">
            <a href="suivi_activites.php?proj=<?= $project_curr['id'] ?>" class="btn btn-phoenix-info rounded-pill btn-sm"><span class="fa-solid fa-bar-chart fs-9 me-2"></span>Suivre</a>
            <button title="Ajouter une activité" class="btn btn-subtle-primary btn-sm" id="addBtn"
                data-bs-toggle="modal" data-bs-target="#addTacheModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter une activité</button>
        </div>
    </div>

    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1">
        <div class="table-responsive p-1 scrollbar">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                <thead class="bg-secondary-subtle">
                    <tr>
                        <th class="sort align-middle" scope="col">Code</th>
                        <th class="sort align-middle" scope="col" style="min-width:200px;">Libellé</th>
                        <th class="sort align-middle text-center" scope="col">Priorité</th>
                        <th class="sort align-middle text-center" scope="col">Indicateur</th>
                        <th class="sort align-middle text-center" scope="col">Cout (FCFA)</th>
                        <th class="sort align-middle text-center" scope="col">Etat</th>
                        <th class="sort align-middle text-center" scope="col" style="min-width:100px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($taches_project as $tache) {
                        $indicateurs = $grouped_tache_indicateurs[$tache['id']] ?? [];
                        $nbre_indicateurs = count($indicateurs);

                        $couts = $grouped_tache_couts[$tache['id']] ?? [];
                        $total_couts = array_sum(array_map('floatval', array_column($couts, 'montant')));
                    ?>
                        <tr>
                            <td><?= $tache['code'] ?></td>
                            <td>
                                <div data-todo-offcanvas-toogle="data-todo-offcanvas-toogle" data-todo-offcanvas-target="todoOffcanvas<?= $tache['id'] ?>">
                                    <a class="mb-0 fw-bold line-clamp-1 flex-grow-1 flex-md-grow-0 cursor-pointer"><?= $tache['name'] ?></a>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="text-body-highlight">
                                    <?php foreach ($priorites as $priorite) {
                                        if ($priorite['id'] == $tache['priorites_id']) {
                                            echo '<span class="badge" style="background-color: ' . $priorite['couleur'] . '">' . $priorite['name'] . '</span>';
                                        }
                                    } ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-link fw-bold p-0 m-0" data-bs-toggle="modal" data-bs-target="#IndicateurTaskModal" aria-haspopup="true" aria-expanded="false"
                                    data-id="<?php echo $tache['id']; ?>">
                                    <?= ($nbre_indicateurs > 0) ? "(" . $nbre_indicateurs . ") planifiée" : "Ajouter" ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <a class="btn btn-link fw-bold p-0 m-0" data-bs-toggle="modal" data-bs-target="#coutTaskModal" aria-haspopup="true" aria-expanded="false"
                                    data-id="<?php echo $tache['id']; ?>">
                                    <?= ($total_couts > 0) ? number_format($total_couts, 0, ',', ' ') : "Ajouter" ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="col text-nowrap badge badge-phoenix fs-10 badge-phoenix-<?= ($tache['state'] == 'actif') ? 'success' : 'danger' ?>"> <?= $tache['state'] ?> </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <?php if (checkPermis($db, 'update')) : ?>
                                        <button title="Modifier" type="button" class="btn btn-sm btn-phoenix-info btn-icon ms-1" data-bs-toggle="modal" data-bs-target="#addTacheModal" data-id="<?= $tache['id'] ?>" data-event-propagation-prevent="data-event-propagation-prevent">
                                            <span class="fas fa-edit"></span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (checkPermis($db, 'update', 2)) : ?>
                                        <button title="<?php echo $tache['state'] == 'actif' ? 'Désactiver' : 'Activer'; ?>" class="btn btn-sm btn-phoenix-warning btn-icon ms-1" onclick="updateState(<?= $tache['id']; ?>, '<?php echo $tache['state'] == 'actif' ? 'inactif' : 'actif'; ?>', 'Êtes-vous sûr de vouloir <?php echo $tache['state'] == 'actif' ? 'désactiver' : 'activer'; ?> cette activité ?', 'taches')">
                                            <span class="uil-<?php echo $tache['state'] == 'actif' ? 'ban' : 'check-circle'; ?> fs-8"></span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (checkPermis($db, 'delete')) : ?>
                                        <button title="Supprimer" type="button" class="btn btn-sm btn-phoenix-danger btn-icon ms-1" onclick="deleteData(<?= $tache['id'] ?>, 'Voulez-vous vraiment supprimer cette activité ?', 'taches')">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>


                        <div class="offcanvas offcanvas-end content-offcanvas offcanvas-backdrop-transparent border-start shadow-none bg-body" tabindex="-1" data-todo-content-offcanvas="data-todo-content-offcanvas-<?= $tache['id'] ?>" id="todoOffcanvas<?= $tache['id'] ?>">
                            <div class="offcanvas-body p-0">
                                <div class="px-5 py-3">
                                    <div class="d-flex flex-between-center align-items-start gap-5 mb-4">
                                        <h2 class="fw-bold fs-6 mb-0 text-body-highlight"><?= htmlspecialchars($tache['name']) ?></h2>
                                        <button title="Fermer" class="btn btn-phoenix-secondary shadow-sm btn-icon px-2" type="button" data-bs-dismiss="offcanvas" aria-label="Close">
                                            <span class="fa-solid fa-xmark"></span>
                                        </button>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-body">Code:</span>
                                            <span class="text-body-highlight fw-bold"><?= htmlspecialchars($tache['code']) ?></span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-body">Statut:</span>
                                            <span class="badge bg-<?= getBadgeClass($tache['status']) ?>"><?= htmlspecialchars($tache['status']) ?></span>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-body">Priorité:</span>
                                            <span class="text-body-highlight">
                                                <?php foreach ($priorites as $priorite) {
                                                    if ($priorite['id'] == $tache['priorites_id']) {
                                                        echo '<span class="badge" style="background-color: ' . $priorite['couleur'] . '">' . $priorite['name'] . '</span>';
                                                    }
                                                } ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-5">
                                        <h4 class="text-body me-3">Description</h4>
                                        <p class="text-body-highlight mb-0"><?= nl2br(htmlspecialchars($tache['description'])) ?></p>
                                    </div>

                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <h5 class="mb-2">Dates prévues</h5>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Début:</span>
                                                <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['debut_prevu'])) ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-body">Fin:</span>
                                                <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['fin_prevue'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="mb-2">Dates réelles</h5>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-body">Début:</span>
                                                <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['debut_reel'] ?? '0000-00-00')) ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-body">Fin:</span>
                                                <span class="text-body-highlight"><?= date('Y-m-d', strtotime($tache['fin_reelle'] ?? '0000-00-00')) ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-body">Assigné à:</span>
                                            <span class="text-body-highlight">
                                                <?php
                                                foreach ($users as $user) {
                                                    if ($user['id'] == $tache['assigned_id']) {
                                                        echo $user['nom'] . ' ' . $user['prenom'];
                                                    }
                                                } ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-body">Créé par:</span>
                                            <span class="text-body-highlight">
                                                <?php
                                                foreach ($users as $user) {
                                                    if ($user['id'] == $tache['add_by']) {
                                                        echo $user['nom'] . ' ' . $user['prenom'];
                                                    }
                                                } ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-body">Date création:</span>
                                            <span class="text-body-highlight"><?= date('Y-m-d H:i:s', strtotime($tache['created_at'])) ?></span>
                                        </div>
                                    </div>

                                    <!-- <h4 class="my-3">Fichiers</h4>
                                <div class="my-3">
                                    <label class="btn btn-link p-0" for="customFile"> <span class="fas fa-plus me-1"></span>Ajouter un fichier </label>
                                    <input class="d-none" id="customFile" type="file" />
                                </div> -->
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>


    </div>
</div>