<?php
$pageCurr = basename($_SERVER['PHP_SELF'], ".php");

$database = new Database();
$db = $database->getConnection();

$secteur = new Secteur($db);
$secteurs_nav = $secteur->read();
$secteurs_nav = array_filter(array_reverse($secteurs_nav), function ($secteur) {
    return $secteur['parent_id'] == 0 && $secteur['state'] == 'actif';
});

?>

<nav class="navbar navbar-vertical navbar-expand-lg" id="sidebarDefault">
    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content py-0">
            <ul class="navbar-nav flex-column" id="navbarVerticalNav">
                <li class="nav-item">
                    <div class="nav-item-wrapper cursor-pointer" onclick="window.location.href = 'accueil.php'">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0 <?php echo $pageCurr === 'accueil' ? 'active' : '' ?>" href="accueil.php" role="button" aria-expanded="<?php echo $pageCurr === 'accueil' ? 'true' : 'false' ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="home"></span></span>
                                    <span class="nav-link-text">Accueil</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-users" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['users', 'roles', 'acteurs']) ? 'true' : 'false'; ?>" aria-controls="nv-users">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="users"></span></span>
                                    <span class="nav-link-text">Utilisateurs</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['users', 'roles', 'acteurs']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-users">
                                <li class="collapsed-nav-item-title d-none">Utilisateurs</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'users' ? 'active' : '' ?>" href="users.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Aperçu</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'roles' ? 'active' : '' ?>" href="roles.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Rôles & Permissions</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'acteurs' ? 'active' : '' ?>" href="acteurs.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Acteurs</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-parametrage" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['localites', 'groups', 'group_view', 'sectors', 'referentiels', 'autres_parametres']) ? 'true' : 'false'; ?>" aria-controls="nv-parametrage">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="settings"></span></span>
                                    <span class="nav-link-text">Paramétrages</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['localites', 'groups', 'sectors', 'group_view', 'referentiels', 'autres_parametres']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-parametrage">
                                <li class="collapsed-nav-item-title d-none">Paramètres</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'localites' ? 'active' : '' ?>" href="localites.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Localités</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'sectors' || $pageCurr === 'group_view' ? 'active' : '' ?>" href="sectors.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Secteurs</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'groups' || $pageCurr === 'group_view' ? 'active' : '' ?>" href="groups.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Groupe de travail</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'autres_parametres' ? 'active' : '' ?>" href="autres_parametres.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Autres paramètres</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'referentiels' ? 'active' : '' ?>" href="referentiels.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Dictionnaire d'indicateurs</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="navbar-vertical-label bg-light-subtle m-0 px-2 py-1">
                        <i class="fas fa-list me-1"></i> Inventaires
                    </div>

                    <!-- <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-secteurs" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['sectors', 'synchronisation']) ? 'true' : 'false'; ?>" aria-controls="nv-secteurs">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="twitch"></span></span>
                                    <span class="nav-link-text">Secteurs</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['sectors', 'synchronisation']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-secteurs">
                                <li class="collapsed-nav-item-title d-none">Secteurs</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo in_array($pageCurr, ['sectors', 'synchronisation']) && !isset($_GET['id']) ? 'active' : '' ?>" href="sectors.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Aperçu</span>
                                        </div>
                                    </a>
                                </li>
                                <?php foreach ($secteurs_nav as $secteur) { ?>
                                    <li class="nav-item overflow-hidden">
                                        <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'sectors' && isset($_GET['id']) && $_GET['id'] == $secteur['id'] ? 'active' : '' ?>" href="sectors.php?id=<?= $secteur['id'] ?>">
                                            <div class="d-flex align-items-center">
                                                <span class="fas fa-chevron-right fs-11"></span>
                                                <span class="nav-link-text ms-lg-0 ms-1"><?= $secteur['name'] ?></span>
                                            </div>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div> -->

                    <div class="nav-item-wrapper cursor-pointer" onclick="window.location.href = 'inventory.php'">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0 <?php echo $pageCurr === 'inventory' ? 'active' : '' ?>" href="inventory.php" role="button" aria-expanded="<?= $pageCurr === 'inventory' ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="server"></span></span>
                                    <span class="nav-link-text">Inventaires</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="navbar-vertical-label bg-light-subtle m-0 px-2 py-1">
                        <i class="fas fa-list me-1"></i> Atténuation & Adaptation
                    </div>

                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-projects" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['projects', 'project_view', 'cadre_resultat_cr', 'fiches_dynamiques']) ? 'true' : 'false'; ?>" aria-controls="nv-projects">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="airplay"></span></span>
                                    <span class="nav-link-text">Projets</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['projects', 'project_view', 'cadre_resultat_cr', 'fiches_dynamiques']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-projects">
                                <li class="collapsed-nav-item-title d-none">Projets</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo in_array($pageCurr, ['projects', 'project_view']) ? 'active' : '' ?>" href="projects.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Aperçu</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'cadre_resultat_cr' ? 'active' : '' ?>" href="cadre_resultat_cr.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Cadre de résultat</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'fiches_dynamiques' ? 'active' : '' ?>" href="fiches_dynamiques.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Fiches dynamiques</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-data" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['suivi_indicateurs', 'indicateur_view', 'suivi_activites', 'resultats_obtenus']) ? 'true' : 'false'; ?>" aria-controls="nv-data">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="database"></span></span>
                                    <span class="nav-link-text">Suivi des résultats</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['suivi_activites', 'suivi_indicateurs', 'indicateur_view', 'resultats_obtenus']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-data">
                                <li class="collapsed-nav-item-title d-none">Suivi des résultats</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'suivi_activites' ? 'active' : '' ?>" href="suivi_activites.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Suivi activités</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'suivi_indicateurs' ? 'active' : '' ?>" href="suivi_indicateurs.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Suivi indicateurs</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'resultats_obtenus' ? 'active' : '' ?>" href="resultats_obtenus.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Résultats obtenus</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-carto" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['cartographie', 'zones_collecte']) ? 'true' : 'false'; ?>" aria-controls="nv-reports">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="map"></span></span>
                                    <span class="nav-link-text">Cartographie</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>

                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['cartographie', 'zones_collecte']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-carto">
                                <li class="collapsed-nav-item-title d-none">Cartographie</li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'cartographie' ? 'active' : '' ?>" href="cartographie.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Cartographie</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'zones_collecte' ? 'active' : '' ?>" href="zones_collecte.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Zones de collecte</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="navbar-vertical-label bg-light-subtle m-0 px-2 py-1">
                        <i class="fas fa-list me-1"></i> Financements
                    </div>

                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0 <?php echo in_array($pageCurr, ['analyse_budgetaire']) ? 'active' : '' ?>" href="analyse_budgetaire.php" role="button" aria-expanded="<?= in_array($pageCurr, ['analyse_budgetaire']) ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="package"></span></span>
                                    <span class="nav-link-text">Analyse budgétaire</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <div class="navbar-vertical-label bg-light-subtle m-0 px-2 py-1">
                        <i class="fas fa-list me-1"></i> Ressources
                    </div>

                    <div class="nav-item-wrapper">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0" href="#nv-reports" role="button" data-bs-toggle="collapse" aria-expanded="<?= in_array($pageCurr, ['rapport_dynamique', 'rapport_periodique', 'rperiode_view', 'dashboard', 'rapport_sectoriel']) ? 'true' : 'false'; ?>" aria-controls="nv-reports">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="pie-chart"></span></span>
                                    <span class="nav-link-text">Rapports</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                        <div class="parent-wrapper label-1">
                            <ul class="nav collapse parent rounded-1 ms-1 <?= in_array($pageCurr, ['rapport_dynamique', 'rapport_periodique', 'rperiode_view', 'dashboard', 'rapport_sectoriel']) ? 'show' : ''; ?>" data-bs-parent="#navbarVerticalCollapse" id="nv-reports">
                                <li class="collapsed-nav-item-title d-none">Rapports</li>
                                <!-- <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'rapport_dynamique' ? 'active' : '' ?>" href="rapport_dynamique.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Rapports dynamiques</span>
                                        </div>
                                    </a>
                                </li> -->
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'rapport_sectoriel' ? 'active' : '' ?>" href="rapport_sectoriel.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Synthèse sectorielle</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo in_array($pageCurr, ['rapport_periodique', 'rperiode_view']) ? 'active' : '' ?>" href="rapport_periodique.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Rapports périodiques</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="nav-item overflow-hidden">
                                    <a class="nav-link rounded-0 py-1 ms-n2 me-0 <?php echo $pageCurr === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">
                                        <div class="d-flex align-items-center">
                                            <span class="fas fa-chevron-right fs-11"></span>
                                            <span class="nav-link-text ms-lg-0 ms-1">Tableaux de bord</span>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="nav-item-wrapper cursor-pointer" onclick="window.location.href = 'documents.php'">
                        <a class="nav-link dropdown-indicator label-1 rounded-0 py-2 m-0 <?php echo in_array($pageCurr, ['documents', 'dossier_view']) ? 'active' : '' ?>" href="documents.php" role="button" aria-expanded="<?= in_array($pageCurr, ['documents', 'dossier_view']) ? 'true' : 'false'; ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon ms-2 my-1"><span data-feather="folder"></span></span>
                                    <span class="nav-link-text">Documentation</span>
                                </div>
                                <div class="dropdown-indicator-icon-wrapper">
                                    <span class="fas fa-chevron-right dropdown-indicator-icon"></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <div class="navbar-vertical-footer p-0">
        <div class="w-100">
            <button class="btn btn-lg navbar-vertical-toggle border-0 fw-semibold w-100 d-flex align-items-center rounded-0">
                <span class="uil uil-left-arrow-to-left fs-8"></span>
                <span class="uil uil-arrow-from-right fs-8"></span>
                <span class="navbar-vertical-footer-text ms-2 fs-9">Vue réduite</span>
            </button>
        </div>
    </div>
</nav>