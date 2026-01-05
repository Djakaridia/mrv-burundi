<?php
http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
?>

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
    <title>404 | MRV - Burundi</title>
    
    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/favicon/favicon.png" />
    <link rel="manifest" href="assets/favicon/manifest.json" />
    <meta name="theme-color" content="#ffffff" />

    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
    <link href="assets/css/theme-rtl.css" type="text/css" rel="stylesheet" id="style-rtl" />
    <link href="assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default" />
</head>

<body class="light">
    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main">
        <div class="content">
            <div class="row flex-center">
                <div class="col-12 col-xl-10 col-xxl-8">
                    <div class="row justify-content-center align-items-center g-5">
                        <div class="col-12 col-lg-7 text-center order-lg-1 bg-white dark__bg-light p-5">
                            <img class="img-fluid w-md-50 w-lg-100 d-dark-none" src="./assets/images/logo-full.png" alt="" width="600" />
                            <img class="img-fluid w-md-50 w-lg-100 d-light-none" src="./assets/images/logo-full.png" alt="" width="600" />
                        </div>
                        <div class="col-12 col-lg-5 text-center p-5">
                            <img class="img-fluid mb-6 w-50 w-lg-70 d-dark-none" src="./assets/img/spot-illustrations/404.png" alt="" />
                            <img class="img-fluid mb-6 w-50 w-lg-70 d-light-none" src="./assets/img/spot-illustrations/dark_404.png" alt="" />

                            <h2 class="text-body-secondary fw-bolder mb-3">Page introuvable!</h2>
                            <p class="text-body mb-5">Désolé, la page que vous cherchiez n'a pas été trouvée.
                                <br class="d-none d-sm-block" />Veuillez retourner à l'accueil.
                            </p>
                            <a class="btn btn-sm btn-primary mb-3" href="accueil.php">Aller à l'accueil</a>
                            <?php if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] !== $_SERVER['REQUEST_URI']): ?>
                                <p><a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($_SERVER['HTTP_REFERER']) ?>">Retour à la page précédente</a></p>
                            <?php endif; ?>
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
</body>

</html>