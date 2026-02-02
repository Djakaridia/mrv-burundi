<div class="mb-9 mt-2">
    <div class="row g-3 mb-3 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">Liste des acteurs</h4>
        </div>

        <div class="col-auto">
            <button title="Exporter" class="btn btn-subtle-info btn-sm"><span class="fa-solid fa-file-export fs-9 me-2"></span>Exporter</button>
            <button title="Ajouter un acteur" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal" data-bs-target="#addTacheModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un acteur</button>
        </div>
    </div>

    <div class="todo-list px-3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1">
        <div class="table-responsive scrollbar-overlay mx-n3">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable2">
                <thead class="bg-primary-subtle">
                    <tr>
                        <th class="sort align-middle" scope="col">#</th>
                        <th class="sort align-middle" scope="col" data-sort="product">Code</th>
                        <th class="sort align-middle" scope="col" data-sort="customer" style="min-width:200px;">Nom</th>
                        <th class="sort align-middle" scope="col" data-sort="rating" style="min-width:110px;">Sigle</th>
                        <th class="sort align-middle" scope="col" style="max-width:350px;" data-sort="review">Type</th>
                    </tr>
                </thead>
                <tbody class="list" id="table-latest-review-body">
                    <?php foreach ($structures_project as $structure) { ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                            <td class="align-middle product py-0">
                                <a class="d-block rounded-2 border border-translucent text-center my-1"
                                    href="javascript:void(0)">
                                    <?php if ($structure['logo']) { ?>
                                        <img src="<?php echo $structure['logo']; ?>" alt="Logo" width="45" />
                                    <?php } else { ?>
                                        <i class="fas fa-users fs-8 p-2"></i>
                                    <?php } ?>
                                </a>
                            </td>
                            <td class="align-middle product"><?php echo $structure['code']; ?></td>
                            <td class="align-middle customer"><?php echo $structure['sigle']; ?></td>
                            <td class="align-middle rating"><?php echo $structure['sigle']; ?></td>
                            <td class="align-middle review" style="min-width:350px;">
                                <?php foreach ($type_structures as $type_structure) { ?>
                                    <?php if ($type_structure['id'] == $structure['type_id']) { ?>
                                        <?php echo $type_structure['name']; ?>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>