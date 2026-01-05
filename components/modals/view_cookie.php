<div class="modal fade" id="cookieModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cookieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header justify-content-between border-bottom px-3 py-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-cookie-bite"></i>
                    <h5 class="modal-title" id="cookieModalLabel">Gestion des préférences de cookies</h5>
                </div>
            </div>
            <div class="modal-body p-4">
                <h5 class="text-primary d-flex align-items-center gap-2 my-3">
                    <i class="fas fa-lock"></i> Votre vie privée nous importe
                </h5>

                <p class="mb-0 text-muted">Nous utilisons des cookies pour améliorer votre expérience.</p>
                <p>
                    Ce site utilise des cookies pour vous garantir la meilleure expérience possible. En continuant à utiliser ce site,
                    vous acceptez notre politique de confidentialité et l'utilisation des cookies dans le cadre du système MRV au Burundi.
                </p>

                <h5 class="text-primary d-flex align-items-center gap-2 my-3">
                    <i class="fas fa-cog"></i> Personnaliser les préférences
                </h5>
                <div>
                    <div class="cookie-types d-flex justify-content-between align-items-center gap-3">
                        <div class="cookie-type card p-3 shadow-sm ">
                            <h6><i class="fas fa-shield-alt me-2"></i> Cookies essentiels</h6>
                            <p class="small">Nécessaires au fonctionnement du site. Ils ne peuvent pas être désactivés.</p>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="essentialCookies" checked disabled>
                                <label class="form-check-label small" for="essentialCookies">Toujours activés</label>
                            </div>
                        </div>

                        <div class="cookie-type card p-3 shadow-sm ">
                            <h6><i class="fas fa-chart-line me-2"></i> Cookies analytiques</h6>
                            <p class="small">Nous aident à améliorer notre site en recueillant des informations anonymes.</p>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="analyticalCookies">
                                <label class="form-check-label small" for="analyticalCookies">Activés</label>
                            </div>
                        </div>
                    </div>

                    <!-- Ajout de la case à cocher pour l'acceptation de la politique de confidentialité -->
                    <div class="mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="acceptPrivacyPolicy">
                            <label class="form-check-label small" for="acceptPrivacyPolicy">
                                J'ai lu et j'accepte la <a href="./privacy-policy.php" target="_blank" class="privacy-link">Politique de confidentialité</a>
                            </label>
                        </div>
                        <div id="privacyPolicyError" class="text-danger small mt-1" style="display: none;">
                            Vous devez accepter notre politique de confidentialité pour continuer.
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="small text-muted">
                            Vous pouvez modifier vos préférences à tout moment en cliquant sur le lien "Paramètres des cookies" en bas de page. Pour en savoir plus, consultez notre
                            <a href="./privacy-policy.php" target="_blank" class="privacy-link">Politique de confidentialité</a>.
                        </p>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" id="rejectAll" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Refuser</button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-subtle-primary" id="acceptSelected" disabled>Accepter la sélection</button>
                        <button type="button" class="btn btn-sm btn-primary" id="acceptAll" disabled>Tout accepter</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const essentialCookies = localStorage.getItem('essential-cookies', true);
        const analyticalCookies = localStorage.getItem('analytical-cookies', true);
        const privacyPolicyAccepted = localStorage.getItem('privacy-policy');

        if (essentialCookies === 'true') {
            $('#essentialCookies').prop('checked', true);
        }

        if (analyticalCookies === 'true') {
            $('#analyticalCookies').prop('checked', true);
        }

        if (privacyPolicyAccepted === 'true') {
            $('#acceptPrivacyPolicy').prop('checked', true);
            $('#acceptSelected').prop('disabled', false);
            $('#acceptAll').prop('disabled', false);
        }

        setTimeout(function() {
            const essential = localStorage.getItem('essential-cookies');
            const analytical = localStorage.getItem('analytical-cookies');
            const privacyAccepted = localStorage.getItem('privacy-policy');

            if (essential === 'true' && analytical === 'true' && privacyAccepted === 'true') {
                $('#cookieModal').modal('hide');
            } else if (essential === 'true' && analytical === 'false' && privacyAccepted === 'true') {
                $('#cookieModal').modal('hide');
            } else {
                $('#cookieModal').modal('show');
            }
        }, 1000);

        // Vérification de l'acceptation de la politique de confidentialité
        $('#acceptPrivacyPolicy').change(function() {
            const isChecked = $(this).is(':checked');
            $('#acceptSelected').prop('disabled', !isChecked);
            $('#acceptAll').prop('disabled', !isChecked);

            if (isChecked) {
                $('#privacyPolicyError').hide();
            }
        });

        $('#acceptAll').click(function() {
            if (!$('#acceptPrivacyPolicy').is(':checked')) {
                $('#privacyPolicyError').show();
                return;
            }

            $('#essentialCookies').prop('checked', true);
            $('#analyticalCookies').prop('checked', true);
            $('.modal-content').addClass('animate__animated animate__bounceOut');

            localStorage.setItem('essential-cookies', true);
            localStorage.setItem('analytical-cookies', true);
            localStorage.setItem('privacy-policy', true);

            setTimeout(function() {
                $('#cookieModal').modal('hide');
                $('.modal-content').removeClass('animate__bounceOut');
            }, 200);
        });

        $('#acceptSelected').click(function() {
            if (!$('#acceptPrivacyPolicy').is(':checked')) {
                $('#privacyPolicyError').show();
                return;
            }

            const analytical = $('#analyticalCookies').is(':checked');
            $('.modal-content').addClass('animate__animated animate__bounceOut');

            localStorage.setItem('essential-cookies', true);
            localStorage.setItem('analytical-cookies', analytical);
            localStorage.setItem('privacy-policy', true);

            setTimeout(function() {
                $('#cookieModal').modal('hide');
                $('.modal-content').removeClass('animate__bounceOut');
            }, 200);
        });

        $('#rejectAll').click(function() {
            $('.modal-content').addClass('animate__animated animate__bounceOut');

            localStorage.setItem('essential-cookies', false);
            localStorage.setItem('analytical-cookies', false);
            localStorage.setItem('privacy-policy', false);

            setTimeout(function() {
                $('#cookieModal').modal('hide');
                $('.modal-content').removeClass('animate__bounceOut');
                window.location.href = '/';
            }, 200);
        });
    });
</script>