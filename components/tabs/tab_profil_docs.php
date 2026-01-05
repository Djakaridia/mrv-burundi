<?php
$userId = $_SESSION['user-data']['user-id'];

$dossier = new Dossier($db);
$dossiers = $dossier->read();

$document = new Documents($db);
$documents = $document->read();
$user_documents = array_filter($documents, function ($document) use ($userId) {
    return $document['add_by'] == $userId;
});
?>

<div class="bg-white dark__bg-dark card rounded-1 mt-1" style="min-height: 300px;">
    <div class="card-body p-1 scrollbar">
        <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable4">
            <thead class="bg-secondary-subtle">
                <tr>
                    <th class="sort align-middle" scope="col">Nom</th>
                    <th class="sort align-middle" scope="col">Dossier</th>
                    <th class="sort align-middle" scope="col">Date d'ajout</th>
                    <th class="sort align-middle" scope="col">Taille</th>
                    <th class="sort align-middle" scope="col" style="min-width:100px;">Actions</th>
                </tr>
            </thead>
            <tbody class="list" id="table-latest-review-body">
                <?php foreach ($user_documents as $document) { ?>
                    <tr
                        class="hover-actions-trigger btn-reveal-trigger position-static">
                        <td class="align-middle px-2">
                            <div class="d-flex align-items-center text-body">
                                <i class="fas fa-file fs-8 me-2"></i><?= $document['name'] ?>
                            </div>
                        </td>
                        <td class="align-middle px-2">
                            <a class="d-flex align-items-center text-body" href="dossier_view.php?id=<?= $document['dossier_id'] ?>">
                                <i class="fas fa-folder fs-8 me-2"></i>
                                <div class="mb-0 text-body">
                                    <?php foreach ($dossiers as $dossier) { ?>
                                        <?php if ($dossier['id'] == $document['dossier_id']) { ?>
                                            <?= $dossier['name'] ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </a>
                        </td>
                        <td class="align-middle px-2">
                            <?= date('d/m/Y', strtotime($document['created_at'])) ?>
                        </td>
                        <td class="align-middle px-2">
                            <?= round($document['file_size'] / 1024 / 1024, 2) ?> MB
                        </td>
                        <td class="align-middle review">
                            <div class="position-relative">
                                <div class="">
                                    <button title="Télécharger" onclick="downloadFiles('MRV', '<?= $document['name'] ?>', '<?= $document['file_path'] ?>')"
                                        class="btn btn-sm btn-phoenix-success me-1 fs-10 px-2 py-1">
                                        <span class="uil-cloud-download fs-8"></span>
                                    </button>

                                    <?php if (checkPermis($db, 'delete')) : ?>
                                        <button title="Supprimer" onclick="deleteData(<?php echo $document['id'] ?>, 'Êtes-vous sûr de vouloir supprimer ce document ?', 'documents')"
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