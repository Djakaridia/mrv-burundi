<div class="mb-9">
    <div class="row g-3 mb-2 d-flex flex-row justify-content-between align-items-center">
        <div class="col-auto">
            <h4 class="my-1 fw-black fs-8">Liste des facteurs d'émissions et absorptions</h4>
        </div>

        <div class="col-auto d-flex gap-2">
            <button title="Ajouter un facteur" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                data-projet_id="<?php echo $project_curr['id'] ?>" data-mesure_id="<?php echo $project_curr['mesure_id'] ?? "" ?>"
                data-bs-target="#addFacteurEmiModal" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                <i class="fas fa-plus"></i> Ajouter un facteur</button>
        </div>
    </div>

    <div class="todo-list mx-n3 bg-body-emphasis border-top border-bottom border-translucent position-relative top-1">
        <div class="table-responsive p-1 scrollbar">
            <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="bg-primary-subtle text-nowrap">
                    <tr>
                        <th class="sort align-middle">Libellé</th>
                        <th class="sort align-middle">Unité</th>
                        <th class="sort align-middle text-center">Type</th>
                        <th class="sort align-middle text-center">Gaz</th>
                        <th class="sort align-middle text-center">Valeur</th>
                        <th class="sort align-middle text-center" style="min-width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="list">
                    <?php foreach ($facteurs_project as $facteur) { ?>
                        <tr class="hover-actions-trigger btn-reveal-trigger position-static">
                            <td class="align-middle px-2 py-0"><?php echo $facteur['name']; ?></td>
                            <td class="align-middle px-2 py-0"><?php echo $facteur['unite']; ?></td>
                            <td class="align-middle px-2 py-0 text-center"><?php echo listTypeFacteur()[$facteur['type']] ?? "N/A"; ?></td>
                            <td class="align-middle px-2 py-0 text-center"><?php echo $facteur['gaz']; ?></td>
                            <td class="align-middle px-2 py-0 text-center"><?php echo $facteur['valeur']; ?></td>
                            <td class="align-middle text-center">
                                <div class="position-relative">
                                    <div class="">
                                        <?php if (checkPermis($db, 'update')) : ?>
                                            <button title="Modifier" data-bs-toggle="modal" data-bs-target="#addFacteurEmiModal"
                                                data-id="<?php echo $facteur['id']; ?>" aria-haspopup="true" aria-expanded="false"
                                                class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1">
                                                <span class="uil-pen fs-8"></span>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (checkPermis($db, 'delete')) : ?>
                                            <button title="Supprimer" onclick="deleteData(<?= $facteur['id'] ?>, 'Voulez-vous vraiment supprimer cet facteur ?', 'facteurs_emissions')"
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