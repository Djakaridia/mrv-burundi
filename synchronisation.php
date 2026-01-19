<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Synchronisation des données sectorielles | MRV - Burundi</title>
    <?php
    include './components/navbar & footer/head.php';

    $secteur = new Secteur($db);
    $data_secteurs = $secteur->read();
    $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
        return $secteur['parent'] == 0 && $secteur['state'] == 'actif';
    });
    ?>
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">
            <div class="mx-n4 mt-n5 px-0 mx-lg-n6 px-lg-0 bg-body-emphasis border border-start-0">
                <div class="card-body p-2 d-lg-flex flex-row justify-content-center align-items-center g-3">
                    <h4 class="text-center text-primary">Synchronisation nationale des données sectorielles</h4>
                </div>
            </div>

            <div class="row mx-n4 g-2 my-3">
                <div class="text-center mb-2">
                    <button class="btn btn-primary px-5" id="syncAllData">Synchroniser tous les secteurs</button>
                </div>

                <?php foreach ($secteurs as $secteur): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card border-0 shadow-sm" style="min-height: 150px;">
                            <div class="card-body p-3">
                                <a href="<?= $secteur['domaine'] ?>" target="_blank" class="card-title mb-2 pb-1 d-flex gap-2 align-items-center text-primary border-3 border-bottom">
                                    <?= $secteur['name'] ?> <i class="fa fa-external-link-alt"></i>
                                </a>
                                <p class="text-muted small mb-1">Domaine: <strong><?= $secteur['domaine'] ?></strong></p>
                                <button class="btn btn-sm btn-success w-100 btn-syncData mt-2" data-sector="<?= $secteur['name'] ?>">Synchroniser</button>
                                <div id="log-<?= $secteur['name'] ?>" class="log-box mt-3"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php include './components/navbar & footer/footer.php'; ?>
    </main>
    <!-- ===============================================-->
    <!--    End of Main Content-->
    <!-- ===============================================-->

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>

<script>
    document.querySelectorAll('.btn-syncData').forEach(btn => {
        btn.addEventListener('click', async () => {
            const sector = btn.dataset.sector;
            const logBox = document.getElementById(`log-${sector}`);
            logBox.innerHTML = `<div class="text-warning text-center small">⏳ Synchronisation en cours...</div>`;
            btn.disabled = true;

            try {
                const res = await fetch('./apis/sync.routes.php?sector=' + encodeURIComponent(sector));
                const result = await res.json();

                if (result.status !== "success") {
                    logBox.innerHTML = `<div class="text-danger">❌ ${result.message}</div>`;
                    btn.disabled = false;
                    return;
                }

                const m = result.data.metadonnees;
                const r = result.data.resume;
                let html = `
                <div class="border rounded bg-light small">
                    <h6 class="text-body text-center border-bottom py-2 mb-1">${result.message}</h6>
                    <p class="text-muted m-2">
                        <strong class="small fw-bold">Secteur :</strong> <small>${m.secteur}</small><br>
                        <strong class="small fw-bold">Domaine :</strong> <small>${m.domaine}</small><br>
                        <strong class="small fw-bold">Source :</strong> <small>${m.source}</small><br>
                        <strong class="small fw-bold">Dernière mise à jour :</strong> <small>${m.date_update}</small>
                    </p>

                    <ul class="list-group m-2 small">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ✅ Actions d’atténuation
                            <span class="badge bg-warning rounded-pill">${r.action_attenuation}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ✅ Actions d’adaptation
                            <span class="badge bg-warning rounded-pill">${r.action_adaptation}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ✅ Inventaires GES
                            <span class="badge bg-warning rounded-pill">${r.inventaire_ges}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ✅ Structures
                            <span class="badge bg-warning rounded-pill">${r.structures}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ✅ Financements
                            <span class="badge bg-warning rounded-pill">${r.financements}</span>
                        </li>
                    </ul>
                </div>
            `;
                logBox.innerHTML = html;
            } catch (err) {
                logBox.innerHTML = `<div class="text-danger small">Erreur : ${err.message}</div>`;
            } finally {
                btn.disabled = false;
            }
        });
    });

    // Synchronisation de tous les secteurs (bouton principal)
    document.getElementById('syncAllData').addEventListener('click', async () => {
        const buttons = document.querySelectorAll('.btn-syncData');
        for (const btn of buttons) {
            btn.click();
            await new Promise(r => setTimeout(r, 1500));
        }
    });
</script>

</script>