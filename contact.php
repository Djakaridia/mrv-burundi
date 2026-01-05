<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Contact | MRV - Burundi</title>
    <?php include './components/navbar & footer/head.php'; ?>
</head>

<body class="light">
    <main class="main" id="top">
        <?php include './components/navbar & footer/sidebar.php'; ?>
        <?php include './components/navbar & footer/navbar.php'; ?>

        <div class="content">
            <div class="mb-9">
                <div class="mx-n4 mx-lg-n6 mt-n5 position-relative mb-md-9" style="height:223px">
                    <div class="bg-holder bg-card d-dark-none"
                        style="background-image:url(./assets/img/bg/bg-40.png);background-size:cover;"></div>
                    <div class="bg-holder bg-card d-light-none"
                        style="background-image:url(./assets/img/bg/bg-dark-40.png);background-size:cover;"></div>

                    <div class="faq-title-box position-relative bg-body-emphasis border border-translucent p-6 rounded-3 text-center mx-auto" style="width: 70%;">
                        <h1>Contactez-nous</h1>
                        <p class="my-3">Vous avez une question ou besoin d'assistance ? Remplissez le formulaire ci-dessous.</p>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-5">
                                <form action="traitement_contact.php" method="post">
                                    <div class="mb-4">
                                        <label for="nom" class="form-label">Nom complet</label>
                                        <input type="text" name="nom" id="nom" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="email" class="form-label">Adresse e-mail</label>
                                        <input type="email" name="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="sujet" class="form-label">Sujet</label>
                                        <input type="text" name="sujet" id="sujet" class="form-control" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="message" class="form-label">Message</label>
                                        <textarea name="message" id="message" rows="6" class="form-control" required></textarea>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-subtle-primary">Envoyer le message</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="mt-5 text-center text-muted">
                            <p class="mb-1">Office Burundais pour la Protection de l'Environnement (OBPE)</p>
                            <p class="mb-1">Gitega - Burundi</p>
                            <p>Email : <a href="mailto:obpe@environnement.gov.ml">obpe@environnement.gov.ml</a></p>
                            <p>Téléphone : (+257) 00 00 00 00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include './components/navbar & footer/footer.php'; ?>
    </main>

    <?php include './components/navbar & footer/foot.php'; ?>
</body>

</html>
