<div class="mb-9 mt-2">
    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="fw-black">Liste des sources de financement</h4>
        </div>

        <div class="col-auto">
            <button title="Ajouter une convention" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addConvenModal"
                aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter une convention</button>
        </div>
    </div>

    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1">
        <div class="table-responsive scrollbar-overlay p-1">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable3">
                <thead class="bg-secondary-subtle">
                    <tr>
                        <th class="sort align-middle">Logo</th>
                        <th class="sort align-middle">Code</th>
                        <th class="sort align-middle">Intitulé</th>
                        <th class="sort align-middle">Bailleur</th>
                        <th class="sort align-middle" style="min-width:110px;">Montant (USD)</th>
                        <th class="sort align-middle">Date d'acord</th>
                        <th class="sort align-middle" style="min-width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                    <?php foreach ($conventions_project as $convention) { ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                            <td class="align-middle product py-0">
                                <?php foreach ($structures as $structure) {
                                    $logoStruc = explode("../", $structure['logo'] ?? ''); ?>
                                    <?php if ($structure['id'] == $convention['structure_id']) { ?>
                                        <?php if ($structure['logo']) { ?>
                                            <img class="d-block rounded-1 w-100 object-fit-contain" src="<?php echo end($logoStruc) ?>" alt="Logo" height="35" />
                                        <?php } else { ?>
                                            <div class="d-block rounded-1 border border-translucent text-center p-1 text-primary">
                                                <i class="fas fa-users fs-8 p-1"></i>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td class="align-middle product"><?php echo $convention['code']; ?></td>
                            <td class="align-middle customer"><?php echo $convention['name']; ?></td>
                            <td class="align-middle review">
                                <?php foreach ($structures as $structure) { ?>
                                    <?php if ($structure['id'] == $convention['structure_id']) { ?>
                                        <?php echo $structure['sigle']; ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td class="align-middle rating" style="min-width:200px;">
                                <span class="badge bg-info-subtle text-info p-2 fs-10"><?php echo number_format($convention['montant'], 0, ',', ' '); ?></span>
                            </td>
                            <td class="align-middle date">
                                <?php echo date('Y-m-d', strtotime($convention['date_accord'])); ?>
                            </td>
                            <td class="align-middle">
                                <div class="position-relative">
                                    <?php if (checkPermis($db, 'update')) : ?>
                                        <button title="Modifier" class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" data-bs-toggle="modal"
                                            data-bs-target="#addConvenModal" data-id="<?php echo $convention['id']; ?>">
                                            <span class="uil-pen fs-8"></span>
                                        </button>
                                    <?php endif; ?>
                                    <?php if (checkPermis($db, 'delete')) : ?>
                                        <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                            onclick="deleteData(<?php echo $convention['id']; ?>, 'Êtes-vous sûr de vouloir supprimer cette convention ?', 'conventions')">
                                            <span class="uil-trash-alt fs-8"></span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>