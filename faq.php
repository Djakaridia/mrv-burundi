<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FAQ | MRV - Burundi</title>
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
                        <h1>Comment pouvons-nous vous aider ?</h1>
                        <p class="mt-3">Recherchez une question ou <a href="./contact.php">contactez le support</a></p>
                    </div>
                </div>

                <div class="row gx-xl-8 gx-xxl-11 gy-6 faq">
                    <div class="col-md-6 col-xl-5 col-xxl-4">
                        <div class="faq-subcategory-tab nav nav-tabs w-sm-75 w-md-100 mx-auto mb-4" style="width: 90%">
                            <div class="nav-item w-100 mb-3" role="presentation">
                                <button class="category nav-link btn bg-body-emphasis w-100 px-3 pt-4 pb-3 fs-8 active show" id="tab-mrv"
                                    data-bs-toggle="tab" data-bs-target="#mrv" type="button" role="tab" aria-selected="true">
                                    <span class="category-icon text-body-secondary fs-6 fa-solid fa-leaf"></span>
                                    <span class="d-block fs-6 fw-bolder lh-1 text-body mt-3 mb-2">Système MRV</span>
                                    <span class="d-block text-body fw-normal mb-0 fs-9">Questions générales sur l'utilisation du système MRV.</span>
                                </button>
                            </div>
                            <div class="nav-item w-100 mb-3" role="presentation">
                                <button class="category nav-link btn bg-body-emphasis w-100 px-3 pt-4 pb-3 fs-8"
                                    id="tab-notifications" data-bs-toggle="tab" data-bs-target="#notifications" type="button"
                                    role="tab" aria-selected="false">
                                    <span class="category-icon text-body-secondary fs-6 fa-solid fa-bell"></span>
                                    <span class="d-block fs-6 fw-bolder lh-1 text-body mt-3 mb-2">Notifications</span>
                                    <span class="d-block text-body fw-normal mb-0 fs-9">Questions sur les alertes et rappels automatiques.</span>
                                </button>
                            </div>
                            <div class="nav-item w-100 mb-3" role="presentation">
                                <button class="category nav-link btn bg-body-emphasis w-100 px-3 pt-4 pb-3 fs-8"
                                    id="tab-collaboration" data-bs-toggle="tab" data-bs-target="#collaboration" type="button"
                                    role="tab" aria-selected="false">
                                    <span class="category-icon text-body-secondary fs-6 fa-solid fa-users"></span>
                                    <span class="d-block fs-6 fw-bolder lh-1 text-body mt-3 mb-2">Collaboration</span>
                                    <span class="d-block text-body fw-normal mb-0 fs-9">Travailler en équipe sur la plateforme MRV.</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-7 col-xxl-8">
                        <div class="faq-subcategory-content tab-content">

                            <!-- Système MRV -->
                            <div class="tab-pane fade show active" id="mrv">
                                <ul class="list-inline mb-0">
                                    <li class="d-flex gap-2 mb-6"><span class="fa-solid fa-star fs-8 text-primary"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Qu'est-ce que le système MRV ?</h4>
                                            <p class="mb-0 text-body-tertiary">Le système MRV (Mesure, Rapport, Vérification) permet de suivre les émissions de GES et les efforts d'atténuation du Burundi, conformément aux engagements de l'Accord de Paris.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex gap-2 mb-6"><span class="fa-solid fa-star fs-8 text-primary"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Comment accéder au système ?</h4>
                                            <p class="mb-0 text-body-tertiary">Les utilisateurs peuvent demander un accès via l'administration MRV nationale. L'accès est basé sur les rôles (ministères, ONG, collectivités, etc.).</p>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="border-top" />
                                <ul class="faq-list list-inline">
                                    <li class="d-flex mt-6"><span class="fa-solid fa-circle"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Comment les données sont-elles collectées ?</h4>
                                            <p class="mb-0 text-body-tertiary">Les données sont saisies à travers des formulaires standardisés par les points focaux sectoriels, selon les méthodologies approuvées.</p>
                                        </div>
                                    </li>
                                    <li class="d-flex mt-6"><span class="fa-solid fa-circle"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Les données sont-elles vérifiées ?</h4>
                                            <p class="mb-0 text-body-tertiary">Oui, un processus de vérification est mis en œuvre avant l'intégration finale. Des audits externes peuvent également être réalisés.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Notifications -->
                            <div class="tab-pane fade" id="notifications">
                                <ul class="list-inline mb-0">
                                    <li class="d-flex gap-2 mb-6"><span class="fa-solid fa-star fs-8 text-primary"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Comment fonctionne le système de notifications ?</h4>
                                            <p class="mb-0 text-body-tertiary">Le système envoie automatiquement des rappels pour les échéances de rapport, les demandes de validation et les événements importants.</p>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="border-top" />
                                <ul class="faq-list list-inline">
                                    <li class="d-flex mt-6"><span class="fa-solid fa-circle"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Quels sont les canaux de notifications ?</h4>
                                            <p class="mb-0 text-body-tertiary">Le système envoie automatiquement des alerts par email et par whatsapp.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <!-- Collaboration -->
                            <div class="tab-pane fade" id="collaboration">
                                <ul class="list-inline mb-0">
                                    <li class="d-flex gap-2 mb-6"><span class="fa-solid fa-star fs-8 text-primary"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Peut-on collaborer sur les rapports ?</h4>
                                            <p class="mb-0 text-body-tertiary">Oui, plusieurs utilisateurs peuvent travailler simultanément sur un même rapport selon leurs autorisations.</p>
                                        </div>
                                    </li>
                                </ul>
                                <hr class="border-top" />
                                <ul class="faq-list list-inline">
                                    <li class="d-flex mt-6"><span class="fa-solid fa-circle"></span>
                                        <div>
                                            <h4 class="mb-3 text-body-highlight">Comment ajouter un membre à un projet ?</h4>
                                            <p class="mb-0 text-body-tertiary">Depuis le groupe de travail, cliquez sur "Membres" et ajoutez un utilisateur autorisé à contribuer. Pour chaque projet, vous pouvez choisir quel groupe de travail est associé.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>

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