<!-- footer -->
<footer class="footer position-absolute justify-content-end align-items-center d-flex w-100 px-3" style="border-top: 1px solid #2fa07a; box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);">
    <div class="row gx-5 justify-content-between align-items-center h-100" style="width: 85%;">
        <div class="col-12 col-md text-center text-md-start mb-2 mb-md-0">
            <small class="text-body-secondary">
                © <?php echo date('Y'); ?> <strong>MRV Burundi</strong>.
                Tous droits réservés.
            </small>
            <br>
            <small class="text-body-secondary">
                Développé avec le soutien de :
                <a href="https://atmost.ca/" target="_blank" class="fw-semibold text-dark text-decoration-none">ATMOST</a>,
                <a href="javascript:void(0);" target="_blank" class="fw-semibold text-dark text-decoration-none">CIDEX</a> et
                <a href="https://cosit-mali.com/" target="_blank" class="fw-semibold text-dark text-decoration-none">COSIT</a>
            </small>
        </div>
        <div class="col-12 col-sm-auto text-center text-sm-end mt-2 mt-sm-0">
            <ul class="list-inline mb-0 fs-9">
                <li class="list-inline-item">
                    <a href="privacy-policy.php" target="_blank" class="text-body">Politique de confidentialité</a>
                </li>|
                <li class="list-inline-item">
                    <a href="securite-policy.php" target="_blank" class="text-body">Politique de sécurité</a>
                </li>|
                <li class="list-inline-item">
                    <a data-bs-toggle="modal" data-bs-target="#cookieModal" class="text-body cursor-pointer">Paramètres des cookies</a>
                </li>
            </ul>
        </div>
    </div>
</footer>

<div class="offcanvas offcanvas-end shadow-lg offcanvaMethodeCalcul" tabindex="-1">
    <div class="offcanvas-header border-bottom">
        <h5 class="fw-bold mb-0">
            <i class="fas fa-calculator text-primary me-2"></i>
            Méthodes de calcul MRV
        </h5>
        <button class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-3">
        <div class="mb-3">
            <div class="bg-primary-subtle px-3 py-2 rounded">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-calculator me-2"></i>Formules de calcul</h6>
            </div>

            <div class="card border-0 bg-light mt-2">
                <div class="card-body py-2 px-3 row">
                    <div class="col-6">
                        <small class="text-muted fw-semibold">Émissions Activités</small>
                        <p class="mb-1 small">
                            <span class="font-monospace bg-white px-2 py-1 rounded d-inline-block">E = DA × FE</span>
                        </p>
                        <small class="text-muted fw-semibold mt-2 d-block">Absorption Activités</small>
                        <p class="mb-0 small">
                            <span class="font-monospace bg-white px-2 py-1 rounded d-inline-block">A = DA × FA</span>
                        </p>
                    </div>

                    <div class="col-6 border-start d-flex flex-column justify-content-center">
                        <small class="text-muted d-block mt-2">DA = données d'activités</small>
                        <small class="text-muted d-block mt-2">FE = facteur d'émission</small>
                        <small class="text-muted d-block mt-2">FA = facteur d'absorption</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="bg-primary-subtle px-3 py-2 rounded">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-layer-group me-2"></i>Niveau sectoriel</h6>
            </div>

            <div class="mt-2">
                <small class="text-muted fw-semibold">Émissions évitées par secteur</small>
                <div class="row g-1 mt-1">
                    <div class="col-6">
                        <div class="bg-white border rounded p-2">
                            <span class="font-monospace small d-block">EE_WEM = BAU − WEM</span>
                            <small class="text-muted">Inconditionnelle</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white border rounded p-2">
                            <span class="font-monospace small d-block">EE_WAM = BAU − WAM</span>
                            <small class="text-muted">Conditionnelle</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted fw-semibold">Absorptions additionnelles</small>
                <div class="row g-1 mt-1">
                    <div class="col-6">
                        <div class="bg-white border rounded p-2">
                            <span class="font-monospace small d-block">AA_WEM = WEM − BAU</span>
                            <small class="text-muted">Inconditionnelle</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-white border rounded p-2">
                            <span class="font-monospace small d-block">AA_WAM = WAM − BAU</span>
                            <small class="text-muted">Conditionnelle</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <small class="text-muted fw-semibold">Taux de réduction</small>
                <div class="row g-1 mt-1">
                    <div class="col-12">
                        <div class="bg-white border rounded p-2">
                            <span class="font-monospace small d-block">%Reduction = (BAU − WEM|WAM) / BAU × 100</span>
                            <small class="text-muted">Inconditionnelle / Conditionnelle</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="bg-primary-subtle px-3 py-2 rounded">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-globe me-2"></i>Niveau national</h6>
            </div>

            <div class="mt-2">
                <div class="bg-white border rounded p-2 mb-2">
                    <small class="text-muted fw-semibold d-block">Émissions nationales</small>
                    <span class="font-monospace small">EE = Σ(BAU − WEM|WAM)</span>
                </div>

                <div class="bg-white border rounded p-2 mb-2">
                    <small class="text-muted fw-semibold d-block">Absorptions nationales</small>
                    <span class="font-monospace small">AA = Σ(WEM|WAM − BAU)</span>
                </div>

                <div class="bg-white border rounded p-2 mb-2">
                    <small class="text-muted fw-semibold d-block">Atténuation totale nette</small>
                    <span class="font-monospace small d-block">AT = (BAU − WEM|WAM) + (FAT_WEM|WAM − FAT_BAU)</span>
                    <small class="text-muted">Inconditionnelle / Conditionnelle</small>
                </div>

                <div class="bg-white border rounded p-2">
                    <small class="text-muted fw-semibold d-block">Indicateur clé CDN</small>
                    <span class="font-monospace small">%Réduction(CDN) = AT / BAU × 100</span>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary py-2 px-3 small mb-3 d-flex flex-column gap-1">
            <span><i class="fas fa-check-circle me-1"></i><strong>BAU</strong> = Business As Usual</span>
            <span><i class="fas fa-check-circle me-1"></i><strong>WEM</strong> = With Existing Measures</span>
            <span><i class="fas fa-check-circle me-1"></i><strong>WAM</strong> = With Additional Measures</span>
            <span><i class="fas fa-check-circle me-1"></i><strong>FAT</strong> = Facteur d'Absorption Totale</span>
        </div>
    </div>
</div>