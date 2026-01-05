<!DOCTYPE html>
<html
    lang="fr"
    dir="ltr"
    data-navigation-type="default"
    data-navbar-horizontal-shape="default">


<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Localités | MRV - Burundi</title>

    <?php
    include './components/navbar & footer/head.php';

    // Initialisation des modèles
    $province = new Province($db);
    $provinces = $province->read();

    $commune = new Commune($db);
    $communes = $commune->read();

    $colline = new Colline($db);
    $collines = $colline->read();

    // Détermination du niveau (0=Provinces, 1=Communes, 2=Collines)
    $niveau = 2;
    if (isset($_GET['niveau']) && is_numeric($_GET['niveau']) && $_GET['niveau'] >= 0 && $_GET['niveau'] <= 2) {
        $niveau = (int)$_GET['niveau'];
    }

    // Configuration des niveaux
    $libelle = ["Provinces", "Communes", "Collines"];
    $loc_tab = ["t_provinces", "t_communes", "t_collines"];
    $loc_api = ["province", "commune", "colline"];

    // Mapping des jointures
    $join_map = [
        "t_communes" => ["fk" => "province", "ref" => "t_provinces.code"],
        "t_collines" => ["fk" => "commune", "ref" => "t_communes.code"]
    ];

    // Construction des requêtes dynamiques
    $select_fields = [];
    $from_tables = [];
    $join_conditions = [];

    for ($i = 0; $i <= $niveau; $i++) {
        $table_alias = "T$i";
        $table_name = $loc_tab[$i];

        if ($i < $niveau) {
            $select_fields[] = "$table_alias.name AS " . str_replace('t_', '', $table_name) . "_name";
            $select_fields[] = "$table_alias.code AS " . str_replace('t_', '', $table_name) . "_code";
        } else {
            $select_fields[] = "$table_alias.*";
        }

        $from_tables[] = "$table_name $table_alias";
        if ($i > 0) {
            $join_info = $join_map[$table_name];
            $join_conditions[] = "$table_alias.{$join_info['fk']} = T" . ($i - 1) . ".code";
        }
    }

    // Construction des clauses SQL
    $select_clause = implode(", ", $select_fields);
    $from_clause = implode(", ", $from_tables);
    $where_clause = $join_conditions ? "WHERE " . implode(" AND ", $join_conditions) : "";

    // Pagination
    $localite_per_page = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 100;
    $pagin = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($pagin - 1) * $localite_per_page;

    // Requête principale
    $query_liste_localites = "SELECT $select_clause FROM $from_clause $where_clause ORDER BY T$niveau.name ASC LIMIT :limit OFFSET :offset";
    try {
        $stmt = $db->prepare($query_liste_localites);
        $stmt->bindValue(':limit', $localite_per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $localites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalRows_liste_localites = $stmt->rowCount();
    } catch (Exception $e) {
        die("Erreur lors de la récupération des localités: " . $e->getMessage());
    }

    // Requête de comptage
    $query_count = "SELECT COUNT(*) AS total_records FROM " . $loc_tab[$niveau] . " T$niveau";
    if ($niveau > 0) {
        $joins = [];
        for ($i = $niveau; $i > 0; $i--) {
            $table = $loc_tab[$i];
            $join_info = $join_map[$table];
            $parent_table = $loc_tab[$i - 1];
            $joins[] = "JOIN $parent_table T" . ($i - 1) . " ON T$i.{$join_info['fk']} = T" . ($i - 1) . ".code";
        }
        $query_count .= " " . implode(" ", $joins);
    }

    // Calcul du total
    try {
        $stmt_count = $db->prepare($query_count);
        $stmt_count->execute();
        $result = $stmt_count->fetch(PDO::FETCH_ASSOC);
        $total_records = (int)$result['total_records'];
        $total_pages = ceil($total_records / $localite_per_page);
    } catch (Exception $e) {
        die("Erreur lors du comptage: " . $e->getMessage());
    }
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
                <div class="card-body p-2 d-lg-flex flex-row justify-content-between align-items-center g-3">
                    <div class="col-auto">
                        <h4 class="my-1 fw-black">Liste des <?php echo "$libelle[$niveau]"; ?></h4>
                    </div>

                    <!-- Navigation par niveaux -->
                    <ul class="nav nav-underline" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $libelle[$niveau] == "Collines" ? "active" : ""; ?>" href="./localites.php?niveau=2">
                                Collines
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $libelle[$niveau] == "Communes" ? "active" : ""; ?>" href="./localites.php?niveau=1">
                                Communes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $libelle[$niveau] == "Provinces" ? "active" : ""; ?>" href="./localites.php?niveau=0">
                                Provinces
                            </a>
                        </li>
                    </ul>

                    <!-- Bouton d'ajout -->
                    <div class="ms-lg-2">
                        <?php if (isset($niveau)) {
                            if ($niveau < count($libelle)) { ?>
                                <button title="Ajouter un <?= "$libelle[$niveau]"; ?>" class="btn btn-subtle-primary btn-sm" id="addBtn" data-bs-toggle="modal"
                                    data-bs-target="#addLocaliteModal" data-niveau="<?= $niveau ?>" aria-haspopup="true" aria-expanded="false" data-bs-reference="parent">
                                    <i class="fas fa-plus"></i> Ajouter une <?php echo strtolower("$libelle[$niveau]"); ?></button>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Contenu principal -->
            <div class="row mt-3">
                <div class="col-12">
                    <div class="mx-n4 p-1 mx-lg-n6 bg-body-emphasis border-y">
                        <div class="table-responsive mx-n1 px-1 pb-3 scrollbar" style=" min-height: 400px">
                            <!-- Pagination -->
                            <?php if ($total_pages > 1) { ?>
                                <nav class="row w-100 bg-success-subtle rounded-0 mx-0 mb-1 py-1">
                                    <div class="col-lg-6 col-12 d-flex justify-content-start align-items-center fs-9">
                                        <span><?php echo $total_records; ?> Résultats trouvés</span>
                                        <span class="mx-2 border-end border-secondary py-2"></span>
                                        <span>Affichage <?php echo $localite_per_page; ?> par page</span>
                                        <span class="mx-2 border-end border-secondary py-2"></span>
                                        <span>Afficher</span>
                                        <select name="limit" class="mx-1" onchange="window.location.href = 'localites.php?niveau=<?php echo $niveau; ?>&limit=' + this.value;">
                                            <option value="100" <?php echo $localite_per_page == 100 ? 'selected' : ''; ?>>100</option>
                                            <option value="500" <?php echo $localite_per_page == 500 ? 'selected' : ''; ?>>500</option>
                                            <option value="1000" <?php echo $localite_per_page == 1000 ? 'selected' : ''; ?>>1000</option>
                                            <option value="<?php echo $total_records; ?>" <?php echo $localite_per_page == $total_records ? 'selected' : ''; ?>>Tout</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-6 col-12 d-flex justify-content-end align-items-center">
                                        <ul class="pagination pagination-separated d-flex justify-content-between align-items-center p-0 m-0">
                                            <?php if ($pagin > 1) : ?>
                                                <li class="page-item me-1">
                                                    <a href="<?php echo $_SERVER['PHP_SELF'] . "?niveau=$niveau&limit=$localite_per_page&page=" . strval($pagin - 1); ?>"
                                                        class="btn btn-sm btn-phoenix-info rounded-1"><i class="fas fa-arrow-left fs-14"></i> Page précédente</a>
                                                </li>
                                            <?php else : ?>
                                                <li class="page-item me-1 disabled">
                                                    <a href="#" class="btn btn-sm btn-phoenix-info rounded-1 disabled">
                                                        <i class="fas fa-arrow-left fs-14"></i> Page précédente</a>
                                                </li>
                                            <?php endif; ?>

                                            <div class="text-muted fs-9 mx-1 btn btn-sm btn-phoenix-info rounded-1 px-2">
                                                <?php echo $pagin; ?> / <?php echo $total_pages; ?>
                                            </div>

                                            <?php if ($pagin < $total_pages) : ?>
                                                <li class="page-item ms-1">
                                                    <a href="<?php echo $_SERVER['PHP_SELF'] . "?niveau=$niveau&limit=$localite_per_page&page=" . strval($pagin + 1); ?>"
                                                        class="btn btn-sm btn-phoenix-info rounded-1">Page suivante <i class="fas fa-arrow-right fs-14"></i></a>
                                                </li>
                                            <?php else : ?>
                                                <li class="page-item ms-1 disabled">
                                                    <a href="#" class="btn btn-sm btn-phoenix-info rounded-1 disabled">
                                                        Page suivante <i class="fas fa-arrow-right fs-14"></i></a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </nav>
                            <?php } ?>

                            <!-- Tableau des données -->
                            <table id="id-datatable" class="table table-bordered fs-9 mb-0 border-top border-translucent" style="width:100%;">
                                <?php if (count($libelle) > 0 && $niveau < count($libelle)) { ?>
                                    <thead class="bg-secondary-subtle">
                                        <tr>
                                            <td class="px-2" style="min-width:100px;"><strong>Code</strong></td>

                                            <!-- Colonnes des niveaux supérieurs -->
                                            <?php for ($i = 0; $i <= $niveau; $i++) { ?>
                                                <td class="px-2"><?php echo ($i == $niveau) ? "<strong>$libelle[$i]</strong>" : $libelle[$i]; ?></td>
                                            <?php } ?>
                                            
                                            <!-- Colonnes spécifiques pour les provinces -->
                                            <?php if ($niveau == 0) { ?>
                                                <td class="px-2"><strong>Abbréviation</strong></td>
                                                <td class="px-2"><strong>Couleur</strong></td>
                                            <?php } ?>

                                            <td class="px-2" width="10%"><strong>Actions</strong></td>
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        <?php if ($totalRows_liste_localites > 0) {
                                            foreach ($localites as $localite) {
                                                $id = $localite["id"];
                                                $code = $localite["code"];
                                                $parent = ($niveau > 0) ? $localite[str_replace('t_', '', $loc_tab[$niveau - 1]) . "_code"] : 0; ?>
                                                <tr>
                                                    <td class="px-2"><?php echo $code; ?></td>
                                                    <?php for ($i = 0; $i < $niveau; $i++) { ?>
                                                        <td class="px-2"><?php echo $localite[str_replace('t_', '', $loc_tab[$i]) . "_name"]; ?></td>
                                                    <?php } ?>
                                                    <td class="px-2"><strong><?php echo $localite["name"]; ?></strong></td>
                                                    <?php if ($niveau == 0) { ?>
                                                        <td class="px-2"><?php echo $localite["sigle"]; ?></td>
                                                        <td class="px-2">
                                                            <div class="progress-bar progress-bar-info" style="width: 100%;background-color: <?php echo $localite["couleur"]; ?>;height: 20px;"></div>
                                                        </td>
                                                    <?php } ?>

                                                    <td class="px-2" align="center">
                                                        <?php if (checkPermis($db, 'update', 2)) : ?>
                                                            <button class="btn btn-sm btn-phoenix-info me-1 fs-10 px-2 py-1" type="button" data-bs-toggle="modal" data-bs-target="#addLocaliteModal"
                                                                data-id="<?= $localite['id'] ?>" data-niveau="<?= $niveau ?>">
                                                                <span class="uil-pen fs-8"></span>
                                                            </button>
                                                        <?php endif; ?>

                                                        <?php if (checkPermis($db, 'delete', 2)) : ?>
                                                            <button title="Supprimer" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" type="button"
                                                                onclick="deleteData(<?php echo $localite['id'] ?>, 'Êtes-vous sûr de vouloir supprimer cette localité ?', '<?= $loc_api[$niveau] ?>')">
                                                                <span class="uil-trash-alt fs-8"></span>
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                        <?php }
                                        } ?>
                                    </tbody>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
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