<div class="row mt-3">
    <div class="col-12">
        <!-- Titre et bouton d'ajout -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-body-emphasis">Liste des Membres du groupe</h3>
            <div class="ms-lg-2">
                <button title="Ajouter un membre" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                    data-bs-target="#addGroupMenbre" aria-haspopup="true" aria-expanded="false"
                    data-bs-reference="parent">
                    <i class="fas fa-plus"></i> Ajouter un membre
                </button>
            </div>
        </div>

        <!-- Liste des membres -->
        <div class="row g-4 mb-9">
            <?php if (empty($users_group)) { ?>
                <div class="text-center py-5 my-5" style="min-height: 300px;">
                    <div class="d-flex justify-content-center mb-3">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h4 class="text-800 mb-3">Aucune utilisateur trouvée</h4>
                    <p class="text-600 mb-5">Il semble que vous n'ayez pas encore d'utilisateurs. Commencez par en créer une.</p>
                    <button title="Ajouter un membre" class="btn btn-primary px-5 fs-8" id="addBtn" data-bs-toggle="modal" data-bs-target="#addGroupMenbre" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                        <i class="fas fa-plus"></i> Ajouter un membre</button>
                </div>
            <?php } else { ?>
                <?php foreach ($users_group as $member):
                    $user_info = null;
                    foreach ($users as $user) {
                        if ($user['id'] == $member['user_id']) {
                            $user_info = $user;
                            break;
                        }
                    }
                    if ($user_info): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border border-1 shadow-sm hover-actions-trigger rounded-1">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-10 d-flex align-items-center mb-3">
                                            <div class="avatar avatar-xl me-2">
                                                <div class="avatar-name rounded-circle bg-soft-primary text-dark">
                                                    <span><?= substr($user_info['prenom'], 0, 1) . substr($user_info['nom'], 0, 1) ?></span>
                                                </div>
                                            </div>
                                            <div class="w-75">
                                                <h5 class="mb-0"> <?= htmlspecialchars($user_info['prenom'] . ' ' . $user_info['nom']) ?> </h5>
                                                <p class="text-600 mb-0"><?= htmlspecialchars($user_info['fonction']) ?></p>
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <button title="Supprimer" class="btn btn-sm btn-phoenix-danger btn-icon" data-bs-toggle="tooltip"
                                                onclick="deleteData(<?php echo $member['id']; ?>,'Voulez-vous vraiment supprimer ce menbre du groupe ?', 'groupe_users')">
                                                <span class="fas fa-trash"></span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="border-top pt-3">
                                        <div class="row g-1">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center">
                                                    <span class="fas fa-envelope text-700 me-2"></span>
                                                    <span class="text-800"><?= htmlspecialchars($user_info['email']) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-12 ">
                                                <div class="d-flex align-items-center">
                                                    <span class="fas fa-phone text-700 me-2"></span>
                                                    <span class="text-800"><?= htmlspecialchars($user_info['phone']) ?></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center">
                                                    <span class="fas fa-building text-700 me-2"></span>
                                                    <span class="text-800">
                                                        <?php if (isset($user_info['structure_id']) && $user_info['structure_id']): ?>
                                                            <?php
                                                            $structure_name = '';
                                                            foreach ($structures as $structure) {
                                                                if ($structure['id'] == $user_info['structure_id']) {
                                                                    $structure_name = $structure['sigle'];
                                                                    break;
                                                                }
                                                            }
                                                            echo htmlspecialchars($structure_name);
                                                            ?>
                                                        <?php else: ?>
                                                            Non spécifié
                                                        <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                        </div>
                <?php endif;
                endforeach; ?>
            <?php } ?>
        </div>
    </div>
</div>