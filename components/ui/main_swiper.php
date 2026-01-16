<!-- Recuperation des indicateurs [referentiels_dash] dans la dashboard -->
<div class="swiper-section3-container">
    <div class="swiper section3-slider swiper-initialized swiper-horizontal swiper-backface-hidden" data-swiper="{&quot;autoplay&quot;:true,&quot;spaceBetween&quot;:5,&quot;loop&quot;:false,&quot;slideToClickedSlide&quot;:true}">
        <div class="swiper-wrapper" id="swiper-wrapper-dashboard" aria-live="off" style="max-height: 435px;">
            <?php if (!empty($referentiels_dash)) { ?>
                <?php foreach ($referentiels_dash as $referentiel) { ?>
                    <div class="swiper-slide" role="group" data-swiper-slide-index="<?= $referentiel['id'] ?>">
                        <div id="suiviRefEvoChart<?= $referentiel['id'] ?>" style="height: 400px;"></div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="swiper-slide" role="group" data-swiper-slide-index="0">
                    <div class="text-center py-lg-10 py-5 rounded border border-light" style="height: 435px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="#6c757d" class="mb-5" viewBox="0 0 16 16">
                            <path d="M0 0h1v16H0V0zm1 15h15v1H1v-1zm1-1h13V1H2v13zm1-1V2h11v11H3zm1-2h2v-2H4v2zm3 0h2V5H7v6zm3 0h2V8h-2v3z" />
                        </svg>
                        <h5 class="text-muted">Aucune visualisation disponible</h5>
                        <p class="text-secondary">Aucun graphique n’a pu être généré à partir des données actuelles.</p>
                        <p class="text-secondary mt-5">Veuillez vérifier les données et les paramètres de visualisation des indicateurs.</p>
                        <button onclick="window.location.href = 'referentiels.php';" class="btn btn-subtle-primary">Configurer les indicateurs</button>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mt-2 d-flex justify-content-between align-items-start">
        <div class="slider-section3-prev bg-body text-center p-1 rounded-circle shadow border border-light"
            style="width: 30px; height: 30px;"> <span class="fas fa-chevron-left nav-icon text-info"></span>
        </div>
        <div class="swiper-pagination swiper-main-pagination"></div>
        <div class="slider-section3-next bg-body text-center p-1 rounded-circle shadow border border-light"
            style="width: 30px; height: 30px;"> <span class="fas fa-chevron-right nav-icon text-info"></span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartsConfig = [
            <?php if (!empty($referentiels_dash)): ?>
                <?php foreach ($referentiels_dash as $referentiel):
                    $id_chart = "suiviRefEvoChart" . $referentiel['id'];

                    $indicateur = new Indicateur($db);
                    $indicateur->referentiel_id = $referentiel['id'];
                    $indicateurs = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

                    if ($indicateurs) {
                        $projet = new Projet($db);
                        $projet->id = $indicateurs[0]['projet_id'];
                        $projet_ref = $projet->readById();
                        $annees = [];
                        for ($year = date('Y', strtotime($projet_ref['start_date'])); $year <= date('Y', strtotime($projet_ref['end_date'])); $year++) {
                            $annees[] = $year;
                        }

                        $cible = new Cible($db);
                        $cible->cmr_id = $indicateurs[0]['id'];
                        $cibles_raw = $cible->readByCMR();
                        $cibles_map = [];
                        if (count($cibles_raw) > 0) {
                            foreach ($cibles_raw as $item) {
                                $year = $item['annee'];
                                $value = (float)$item['valeur'];
                                if (!isset($cibles_map[$year])) $cibles_map[$year] = 0;
                                $cibles_map[$year] += $value;
                            }
                        }
                        $cibles = array_map(fn($y) => (float)($cibles_map[$y] ?? 0), $annees);

                        $suivi = new Suivi($db);
                        $suivi->cmr_id = $indicateurs[0]['id'];
                        $suivis_raw = $suivi->readByCMR();
                        $suivis_map = [];
                        if (count($suivis_raw) > 0) {
                            foreach ($suivis_raw as $item) {
                                $year = $item['annee'];
                                $value = (float)$item['valeur'];
                                if (!isset($suivis_map[$year])) $suivis_map[$year] = 0;
                                $suivis_map[$year] += $value;
                            }
                        }
                        $suivis = array_map(fn($y) => (float)($suivis_map[$y] ?? 0), $annees);
                    }

                ?> {
                        id: "<?= $id_chart ?>",
                        categories: <?= json_encode($annees ?? []) ?>,
                        cibles: <?= json_encode($cibles ?? []) ?>,
                        suivis: <?= json_encode($suivis ?? []) ?>,
                        unite: "<?= $unite_grouped[$referentiel['id']] ?? 'Unité' ?>",
                        title: "<?= $referentiel['intitule'] . " (" . $unite_grouped[$referentiel['id']] . ")" ?>"
                    },
                <?php endforeach; ?>
            <?php endif; ?>
        ];

        const swiper = new Swiper('.section3-slider', {
            spaceBetween: 5,
            loop: false,
            autoplay: {
                delay: 10000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.swiper-main-pagination',
            },
            navigation: {
                nextEl: '.slider-section3-next',
                prevEl: '.slider-section3-prev',
            },
            on: {
                init: function() {
                    mrvColumnChart(chartsConfig[this.activeIndex]);
                },
                slideChange: function() {
                    mrvColumnChart(chartsConfig[this.activeIndex]);
                }
            }
        });

        if (chartsConfig.length > 0) {
            mrvColumnChart(chartsConfig[0]);
        }
    });
</script>