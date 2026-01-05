<?php
$userId = $_SESSION['user-data']['user-id'];

$groupId = new GroupeUsers($db);
$groupIds = $groupId->read();
$groupUsers = array_filter($groupIds, function ($group_user) use ($userId) {
    return $group_user['user_id'] == $userId;
});
$groupMappedIds = array_map(function ($group_user) {
    return $group_user['groupe_id'];
}, $groupUsers);

$group = new GroupeTravail($db);
$groups = $group->read();
$user_groups = array_filter($groups, function ($group) use ($groupMappedIds) {
    return in_array($group['id'], $groupMappedIds);
});
$user_group_ids = array_map(function ($group) {
    return $group['id'];
}, $user_groups);

$projet = new Projet($db);
$projets = $projet->read();
$user_projets = array_filter($projets, function ($projet) use ($user_group_ids) {
    $projet_group_ids = array_map('trim', explode(',', $projet['groupes']));
    return count(array_intersect($projet_group_ids, $user_group_ids)) > 0;
});
?>


<div class="bg-white dark__bg-dark card rounded-1 mt-1" style="min-height: 300px;">
    <div class="card-body p-1 scrollbar">
        <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
            <thead class="bg-secondary-subtle">
                <tr class="text-nowrap">
                    <th class="align-middle ps-2" scope="col" data-sort="code" style="width:10%;">CODE</th>
                    <th class="align-middle ps-2" scope="col" data-sort="name" style="width:30%;">NOM DU PROJET</th>
                    <th class="align-middle text-start ps-2" scope="col" data-sort="progression" style="width:20%;">PROGRESSION</th>
                    <th class="align-middle ps-2" scope="col" data-sort="deadline" style="width:5%;">DEADLINE</th>
                    <th class="align-middle text-center ps-2" scope="col" data-sort="status" style="width:5%;">#</th>
                </tr>
            </thead>
            <tbody class="list" id="project-summary-table-body">
                <?php foreach ($user_projets as $projet) {
                    $daysDeadline = floor((strtotime($projet['end_date']) - time()) / (60 * 60 * 24));

                    $tache_projet = new Tache($db);
                    $tache_projet->projet_id = $projet['id'];
                    $taches_projet = $tache_projet->readByProjet();
                    $taches_projet = array_filter($taches_projet, function ($tache) {
                        return $tache['state'] == 'actif';
                    });

                    $totalTacheCount = count($taches_projet);
                    $finishedTacheCount = count(array_filter($taches_projet, function ($tache) {
                        return strtolower($tache['status']) === 'terminée';
                    }));
                    $progress = $totalTacheCount > 0 ? (round(($finishedTacheCount / $totalTacheCount), 2) * 100) : 0;
                ?>
                    <tr class="position-static">
                        <td class="align-middle white-space-nowrap ps-2">
                            <p class="mb-0 fs-9 text-body"><?= $projet['code'] ?></p>
                        </td>
                        <td class="align-middle white-space-nowrap ps-2">
                            <a class="mb-0 fs-9 fw-semibold" href="project_view.php?id=<?= $projet['id'] ?>"><?= $projet['name'] ?></a>
                        </td>
                        <td class="align-middle text-start white-space-nowrap ps-2">
                            <p class="text-body-secondary fs-10 mb-0"><?= $progress ?>%</p>
                            <div class="progress bg-primary-subtle" style="height:3px;" role="progressbar">
                                <div class="progress-bar bg-<?= $progress < 30 ? 'danger' : ($progress < 70 ? 'warning' : 'success') ?>" style="width: <?= $progress ?>%"
                                    role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </td>

                        <td class="align-middle white-space-nowrap ps-2">
                            <span class="badge badge-phoenix fs-10 badge-phoenix-<?= $daysDeadline < 30 ? 'danger' : ($daysDeadline < 90 ? 'warning' : 'success') ?>"><?= $daysDeadline ?> jours</span>
                        </td>

                        <td class="align-middle white-space-nowrap px-2">
                            <div class="position-relative">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button title="Voir" class="btn btn-sm px-2 py-1 btn-phoenix-info" data-bs-toggle="modal" data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>">
                                        <span class="uil-eye fs-8"></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>