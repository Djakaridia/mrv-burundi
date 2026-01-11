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

$structure = new Structure($db);
$structures = $structure->read();
?>

<div class="bg-white dark__bg-dark card rounded-1 mt-1" style="min-height: 300px;">
    <div class="card-body p-1 scrollbar">
        <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
            <thead class="bg-secondary-subtle">
                <tr>
                    <th class="sort align-middle px-2 text-uppercase" style="width:3%; min-width:250px">Nom du groupe</th>
                    <th class="sort align-middle px-2 text-uppercase" style="width:30%; min-width:100px">Membrees</th>
                    <th class="sort align-middle px-2 text-uppercase" style="width:10%; min-width:100px">Superviseur</th>
                    <th class="sort align-middle px-2 text-uppercase" style="width:10%; min-width:50px">Date</th>
                    <th class="sort align-middle px-2 text-uppercase" style="width:10%; min-width:100px">Status</th>
                </tr>
            </thead>
            <tbody class="list" id="all-email-table-body">
                <?php foreach ($user_groups as $group) {
                    $groupUser = new GroupeUsers($db);
                    $groupUsers = $groupUser->read();
                    $group_users = array_filter($groupUsers, function ($group_user) use ($group) {
                        return $group_user['groupe_id'] == $group['id'];
                    });

                    $users_ids = array_map(function ($group_user) {
                        return $group_user['user_id'];
                    }, $group_users);

                    $user = new User($db);
                    $users = $user->read();
                    $members = array_filter($users, function ($user) use ($users_ids) {
                        return in_array($user['id'], $users_ids);
                    });
                ?>
                    <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                        <td class="align-middle p-2">
                            <a class="fw-semibold text-primary" href="group_view.php?id=<?= $group['id']; ?>"><?= $group['name']; ?></a>
                            <div class="fs-10 d-block"><?= $group['description']; ?></div>
                        </td>

                        <td class="align-middle text-start fw-bold text-body-tertiary p-2">
                            <div class="avatar-group">
                                <?php if (!empty($members)) : ?>
                                    <?php foreach (array_slice($members, 0, 5) as $user) : ?>
                                        <a class="dropdown-toggle dropdown-caret-none d-inline-block" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                            <div class="avatar avatar-m">
                                                <div class="avatar-name rounded-circle bg-soft-info shadow-sm border-light">
                                                    <span class="text-body-tertiary fs-9"><?= substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1) ?></span>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu shadow-sm p-0">
                                            <div class="row g-0 p-2">
                                                <div class="col-lg-3 col-12">
                                                    <div class="avatar avatar-xl">
                                                        <div class="avatar-name rounded-1 bg-soft-info shadow-sm border-light">
                                                            <span class="text-body-tertiary fs-6"><?= substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-9 col-12 list-group list-group-flush">
                                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Nom: </small><?php echo $user['nom']; ?></div>
                                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Prenom: </small><?php echo $user['prenom']; ?></div>
                                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Email: </small><?php echo $user['email']; ?></div>
                                                    <div class="list-group-item text-nowrap p-1"><small class="fw-bold">Contact: </small><?php echo $user['phone']; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (count($members) > 5) : ?>
                                        <div class="avatar avatar-m">
                                            <div class="avatar-name rounded-circle bg-soft-info shadow-sm border-light">
                                                <span class="text-body-tertiary fs-9">+<?= count($members) - 5 ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="mt-lg-3 mt-xl-0 text-body-tertiary">Pas de membre</div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td class="align-middle text-start fw-bold text-body-tertiary p-2">
                            <?php foreach ($structures as $structure) {
                                if ($structure['id'] == $group['monitor']) {
                                    echo $structure['sigle'];
                                }
                            } ?>
                        </td>

                        <td class="align-middle text-body p-2">
                            <?= date('Y-m-d', strtotime($group['created_at'])); ?>
                        </td>

                        <td class="align-middle fw-semibold text-start p-2">
                            <span class="badge badge-phoenix fs-10 badge-phoenix-<?php echo $group['state'] == 'actif' ? 'success' : 'danger'; ?>">
                                <?php echo ($group['state']); ?>
                                <span class="ms-1 uil <?php echo $group['state'] == 'actif' ? 'uil-check-circle' : 'uil-ban'; ?> fs-10"></span>
                            </span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>