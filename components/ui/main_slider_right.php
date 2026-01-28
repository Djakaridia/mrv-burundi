<!-- Récupération des indicateurs [referentiels_dash] dans le dashboard -->
<div class="swiper-section4-container">
    <div class="swiper section4-right-slider swiper-initialized swiper-horizontal swiper-backface-hidden" 
         data-swiper='{"autoplay":true,"spaceBetween":5,"loop":false,"slideToClickedSlide":true}'>
        <div class="swiper-wrapper" id="swiper-wrapper-dashboard" aria-live="off" style="max-height: 430px;">
            <?php foreach ($referentiels_dash as $referentiel) { ?>
                <div class="swiper-slide" role="group" data-swiper-slide-index="<?= $referentiel['id'] ?>">
                    <div id="sectorRefEvoChart<?= $referentiel['id'] ?>" style="height: 400px;"></div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mt-2 d-flex justify-content-between">
        <button class="slider-section4-right-prev bg-body text-center p-1 rounded-circle shadow border border-light" 
                style="width: 30px; height: 30px;"> <span class="fas fa-chevron-left nav-icon text-info"></span>
        </button>
        <div class="swiper-pagination section4-right-pagination"></div>
        <button class="slider-section4-right-next bg-body text-center p-1 rounded-circle shadow border border-light" 
                style="width: 30px; height: 30px;"> <span class="fas fa-chevron-right nav-icon text-info"></span>
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartsConfig = [
            <?php foreach ($referentiels_dash as $referentiel):
                $id_chart = "sectorRefEvoChart" . $referentiel['id'];
                $indicateur = new Indicateur($db);
                $indicateur->referentiel_id = $referentiel['id'];
                $indicateurs = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

                if (empty($indicateurs)) continue;

                $projet = new Projet($db);
                $projet->id = $indicateurs[0]['projet_id'];
                $projet_ref = $projet->readById();

                $annees = [];
                if (!empty($projet_ref['start_date']) && !empty($projet_ref['end_date'])) {
                    $start_year = date('Y', strtotime($projet_ref['start_date']));
                    $end_year = date('Y', strtotime($projet_ref['end_date']));
                    for ($year = $start_year; $year <= $end_year; $year++) {
                        $annees[] = $year;
                    }
                }

                $secteur_ids = [];
                if (!empty($projet_ref['secteurs'])) {
                    $secteur_ids = array_map('intval', explode(',', str_replace('"', '', $projet_ref['secteurs'])));
                }
                $projet_secteurs = array_filter($secteurs, function ($s) use ($secteur_ids) {
                    return in_array($s['id'], $secteur_ids);
                });

                $suivi = new Suivi($db);
                $suivi->indicateur_id = $indicateurs[0]['id'];
                $suivis_raw = $suivi->readByIndicateur();
                
                $series_data = [];
                foreach ($projet_secteurs as $secteur) {
                    $secteur_data = [
                        'name' => $secteur['name'],
                        'data' => []
                    ];
                    foreach ($annees as $annee) {
                        $valeur = 0;
                        foreach ($suivis_raw as $suivi) {
                            if ($suivi['secteur_id']??'' == $secteur['id'] && $suivi['annee'] == $annee) {
                                $valeur += (float)$suivi['valeur'];
                            }
                        }
                        $secteur_data['data'][] = $valeur;
                    }
                    $series_data[] = $secteur_data;
                }
            ?>{
                id: "<?= $id_chart ?>",
                categories: <?= json_encode($annees) ?>,
                series: <?= json_encode($series_data) ?>,
                unite: "<?= ($unite_grouped[$referentiel['id']] ?? 'Unité') ?>",
                title: "<?= ($referentiel['intitule']) ?>"
            },
            <?php endforeach; ?>
        ];

        const swiper = new Swiper('.section4-right-slider', {
            spaceBetween: 5,
            loop: false,
            autoplay: {
                delay: 10000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.section4-right-pagination',
            },
            navigation: {
                nextEl: '.slider-section4-right-next',
                prevEl: '.slider-section4-right-prev',
            },
            on: {
                init: function() {
                    mrvSplineChart(chartsConfig[this.activeIndex]);
                },
                slideChange: function() {
                    mrvSplineChart(chartsConfig[this.activeIndex]);
                }
            }
        });

        const sliderContainer = document.querySelector('.section4-right-slider');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => swiper.autoplay.stop());
            sliderContainer.addEventListener('mouseleave', () => swiper.autoplay.start());
        }

        if (chartsConfig.length > 0) {
            mrvSplineChart(chartsConfig[0]);
        }
    });
</script>