<!-- Recuperation des indicateurs [referentiels_dash] dans la dashboard -->
<div class="swiper-section4-left-container">
    <div class="swiper section4-left-slider swiper-initialized swiper-horizontal swiper-backface-hidden" data-swiper="{&quot;autoplay&quot;:true,&quot;spaceBetween&quot;:5,&quot;loop&quot;:false,&quot;slideToClickedSlide&quot;:true}">
        <div class="swiper-wrapper" id="swiper-wrapper-dashboard" aria-live="off" style="max-height: 430px;">
            <?php foreach ($referentiels_dash as $referentiel) { ?>
                <div class="swiper-slide" role="group" data-swiper-slide-index="<?= $referentiel['id'] ?>">
                    <div id="sectorRefReparChart<?= $referentiel['id'] ?>" style="height: 400px;"></div>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mt-2 d-flex justify-content-between align-items-start">
        <div class="slider-section4-left-prev bg-body text-center p-1 rounded-circle shadow border border-light" 
            style="width: 30px; height: 30px;"> <span class="fas fa-chevron-left nav-icon text-info"></span>
        </div>
        <div class="swiper-pagination section4-left-pagination"></div>
        <div class="slider-section4-left-next bg-body text-center p-1 rounded-circle shadow border border-light" 
            style="width: 30px; height: 30px;"> <span class="fas fa-chevron-right nav-icon text-info"></span>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartsConfig = [
            <?php foreach ($referentiels_dash as $referentiel):
                $id_chart = "sectorRefReparChart" . $referentiel['id'];
                $indicateur = new Indicateur($db);
                $indicateur->referentiel_id = $referentiel['id'];
                $indicateurs = array_filter($indicateur->readByReferentiel(), fn($i) => $i['state'] == 'actif');

                if (empty($indicateurs)) continue;

                $projet = new Projet($db);
                $projet->id = $indicateurs[0]['projet_id'];
                $projet_ref = $projet->readById();

                $secteur_ids = [];
                $secteur_ids = array_map('intval', explode(',', str_replace('"', '', $projet_ref['secteurs'] ?? '')));
                $projet_secteurs = array_filter($secteurs, function ($s) use ($secteur_ids) {
                    return in_array($s['id'], $secteur_ids);
                });

                $suivi = new Suivi($db);
                $suivi->indicateur_id = $indicateurs[0]['id'];
                $suivis_raw = $suivi->readByIndicateur();
                $suivis_par_secteur = [];
                foreach ($suivis_raw as $suivi) {
                    if (!isset($suivis_par_secteur[$suivi['secteur_id']??""])) {
                        $suivis_par_secteur[$suivi['secteur_id']??""] = 0;
                    }
                    $suivis_par_secteur[$suivi['secteur_id']??""] += (float)$suivi['valeur'];
                }

                $chart_data = [];
                foreach ($projet_secteurs as $secteur) {
                    $secteur_id = $secteur['id'];
                    $valeur = $suivis_par_secteur[$secteur_id] ?? 0;
                    $chart_data[] = [
                        'name' => $secteur['name'],
                        'y' => $valeur,
                    ];
                }
            ?> {
                    id: "<?= $id_chart ?>",
                    data: <?= json_encode($chart_data) ?>,
                    unite: "<?= $unite_grouped[$referentiel['id']] ?? 'Unité' ?>",
                    title: "<?= ($referentiel['intitule']) ?>"
                },
            <?php endforeach; ?>
        ];

        const swiper = new Swiper('.section4-left-slider', {
            spaceBetween: 5,
            loop: false,
            autoplay: {
                delay: 10000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.section4-left-pagination',
            },
            navigation: {
                nextEl: '.slider-section4-left-next',
                prevEl: '.slider-section4-left-prev',
            },
            on: {
                init: function() {
                    mrvPieChart(chartsConfig[this.activeIndex]);
                },
                slideChange: function() {
                    mrvPieChart(chartsConfig[this.activeIndex]);
                }
            }
        });

        if (chartsConfig.length > 0) {
            mrvPieChart(chartsConfig[0]);
        }
    });
</script>