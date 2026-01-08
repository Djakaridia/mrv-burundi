<script>
    let map, markerCluster, allMarkers = [],
        regionsLayer, zonesLayers, searchResultsLayer, currentBasemap;

    let regionTabLayers = [];
    let zoneTabLayers = [];
    const listRegions = [];
    const listZones = [];
    const regionStats = <?= json_encode($projets_par_region) ?>;
    const progressIndicator = $('#progressIndicator');

    const animationConfig = {
        duration: 400,
        markerFlyDuration: 1500,
        fadeDuration: 300,
        bounceDuration: 800
    };

    async function initMap() {
        var map = L.map("mapContainer", {
            zoomControl: false,
            fadeAnimation: true,
            markerZoomAnimation: true,
            maxBounds: [ [-4.5, 29.0], [-2.3, 30.9] ],
            maxBoundsViscosity: 1.0
        }).setView([-3.3731, 29.9189], 8);

        progressIndicator.css({
            'width': '20%',
            'transition': 'width 0.5s ease-in-out'
        });
        
        currentBasemap = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        progressIndicator.css('width', '40%');

        // =================== Charger les régions du Burundi ===================
        const shapefileUrl = "../documents/none.zip";
        const regionsList = $('#regionsList');
        const regionsCount = $('#regionsCount');
        shp(shapefileUrl).then(function(geojson) {
            loadedCouche = 0;
            regionsLayer = L.geoJSON(geojson, {
                style: {
                    color: "#3388ff",
                    weight: 2,
                    fillColor: "#3388ff",
                    fillOpacity: 0.1,
                },
                onEachFeature: function(feature, layer) {
                    if (feature.properties) {
                        const id = feature.properties.ID_1 || '';
                        const type1 = feature.properties.TYPE_1 || '';
                        const name0 = feature.properties.NAME_0 || '';
                        const name1 = feature.properties.NAME_1 || '';

                        const popupContent = `
                        <div class="p-1">
                            <div class="project-title">${name1}</div>
                            <div class="project-info">
                                <span><strong>Nom pays:</strong> ${name0}</span><br/>
                                <span><strong>Nom région:</strong> ${name1}</span>
                            </div>
                        </div>`;

                        const checkContent = `
                        <div class="list-group-item list-group-item-action">
                            <div class="input-group">
                                <input type="checkbox" class="region-checkbox" id="reg-${id}" name="regions[]" value="${name1}">
                                <label for="reg-${id}" class="text-capitalize link-primary cursor-pointer mx-1 w-75">${name1}</label>
                            </div>
                        </div>`;

                        layer.bindPopup(popupContent);
                        layer.on('click', function() {
                            layer.setStyle({
                                fillColor: '#ff6b6b',
                                fillOpacity: 0.3,
                                weight: 3
                            });

                            setTimeout(() => layer.setStyle({
                                fillColor: '#3388ff',
                                fillOpacity: 0.1,
                                weight: 2
                            }), 1000);
                            updateZoneStats(name1);
                            $(`.region-item[data-region="${name1}"]`).addClass('highlight-item');
                            setTimeout(() => $(`.region-item[data-region="${name1}"]`).removeClass('highlight-item'), 2000);
                        });

                        layer.on('mouseover', function() {
                            layer.setStyle({
                                weight: 3,
                                fillOpacity: 0.2
                            });
                            layer.bringToFront();
                        });

                        layer.on('mouseout', function() {
                            layer.setStyle({
                                weight: 2,
                                fillOpacity: 0.1
                            });
                        });

                        loadedCouche++;
                        layer.regionName = name1;
                        regionsList.append(checkContent);
                        listRegions.push(checkContent);
                        setTimeout(() => $(`#reg-${id}`).parent().parent().hide().fadeIn(500), loadedCouche * 100);
                    }
                }
            });

            regionTabLayers.push(regionsLayer);
            regionsCount.text(loadedCouche);
            map.flyToBounds(regionsLayer.getBounds(), {
                duration: 1,
                padding: [20, 20]
            });
            progressIndicator.css('width', '80%');
        });

        // =================== zones de collecte ===================
        <?php if (!empty($zones)): ?>
            const zones = <?= json_encode($zones) ?>;
            const zonesList = $('#zonesList');
            const zonesCount = $('#zonesCount');
            let loadedZones = 0;
            let loadedLayers = 0;
            let zoneLayersMap = new Map();
            let allZoneLayers = [];

            zones.forEach((zone, index) => {
                const zoneCode = zone.code;
                const zoneName = zone.name;
                const zoneUrl = zone.couches;

                if (!zoneUrl) {
                    updateCounters();
                    return;
                }

                const checkContent = `
                <div class="list-group-item list-group-item-action zone-item" data-zone="${zoneName}">
                    <div class="input-group">
                        <input type="checkbox" class="zone-checkbox" id="${zoneCode}" name="zones[]" value="${zoneName}"> <!-- CORRECTION: value="${zoneName}" au lieu de value="${zoneUrl}" -->
                        <label for="${zoneCode}" class="text-capitalize link-primary cursor-pointer mx-1 w-75">${zoneName}</label>
                    </div>
                </div>`;

                zonesList.append(checkContent);
                listZones.push(checkContent);
                loadedZones++;
                updateCounters();

                shp(zoneUrl).then(function(geojson) {
                    const zoneLayer = L.geoJSON(geojson, {
                        style: {
                            color: "#79822f",
                            weight: 2,
                            fillColor: "#79822f",
                            fillOpacity: 0.05
                        },
                        onEachFeature: function(feature, layer) {
                            if (feature.properties) {
                                let name1 = feature.properties['adm1nm'] ?? 'NA';
                                let code2 = feature.properties['adm2cd'] ?? 'NA';
                                let name2 = feature.properties['adm2nm'] ?? (feature.properties['NAME_2'] ?? 'NA');

                                let popupContent = `
                                <div class="p-1">
                                    <div class="project-title">${zoneName}</div>
                                    <div class="project-info">
                                        <span><strong>Région:</strong> ${name1}</span><br/>
                                        <span><strong>Zone Code:</strong> ${code2}</span><br/>
                                        <span><strong>Zone Name:</strong> ${name2}</span><br/>
                                    </div>
                                </div>`;

                                layer.bindPopup(popupContent);
                                if (!zoneLayersMap.has(zoneName)) zoneLayersMap.set(zoneName, []);
                                zoneLayersMap.get(zoneName).push(layer);

                                layer.on('click', function(e) {
                                    layer.setStyle({
                                        fillColor: '#ffa726',
                                        fillOpacity: 0.3,
                                        weight: 3
                                    });

                                    setTimeout(() => layer.setStyle({
                                        fillColor: '#79822f',
                                        fillOpacity: 0.05,
                                        weight: 2
                                    }), 1000);

                                    $(`.zone-item[data-zone="${zoneName}"]`).addClass('highlight-item');
                                    setTimeout(() => $(`.zone-item[data-zone="${zoneName}"]`).removeClass('highlight-item'), 2000);
                                });

                                layer.on('mouseover', function() {
                                    layer.setStyle({
                                        weight: 3,
                                        fillOpacity: 0.15
                                    });
                                    layer.bringToFront();
                                });

                                layer.on('mouseout', function() {
                                    layer.setStyle({
                                        weight: 2,
                                        fillOpacity: 0.05
                                    });
                                });

                                loadedLayers++;
                                layer.zoneName = zoneName;
                                allZoneLayers.push(layer);
                                setTimeout(() => $(`#${zoneCode}`).parent().parent().hide().fadeIn(500), index * 150);
                                updateCounters();
                            }
                        },
                    });

                    zoneTabLayers.push(zoneLayer);
                    if (loadedLayers > 0) {
                        const group = new L.featureGroup(allZoneLayers);
                        map.fitBounds(group.getBounds(), {
                            padding: [20, 20],
                            maxZoom: 10
                        });
                    }
                }).catch(function(error) {
                    loadedLayers++;
                    updateCounters();
                });

            });

            function updateCounters() {
                zonesCount.text(loadedZones);
                progressIndicator.css('width', `${70 + (loadedLayers / zones.length) * 20}%`);
            }

        <?php endif; ?>

        // =================== Cluster de marqueurs ===================
        markerCluster = L.markerClusterGroup({
            chunkedLoading: true,
            animate: true,
            animateAddingMarkers: true,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: true,
            zoomToBoundsOnClick: true,
            disableClusteringAtZoom: 16
        });
        searchResultsLayer = L.layerGroup().addTo(map);

        <?php foreach ($indicateur_cmr as $indicateur):
            if (!empty($indicateur['latitude']) && !empty($indicateur['longitude']) && isset($projets_par_id[$indicateur['projet_id']])):
                $lat = (float)$indicateur['latitude'];
                $lng = (float)$indicateur['longitude'];

                // Data projet
                $projet_info = $projets_par_id[$indicateur['projet_id']];
                $start_date = !empty($projet_info['start_date']) ? date('d/m/Y', strtotime($projet_info['start_date'])) : 'Non définie';
                $end_date = !empty($projet_info['end_date']) ? date('d/m/Y', strtotime($projet_info['end_date'])) : 'Non définie';
                $budget = !empty($projet_info['budget']) ? number_format($projet_info['budget'], 0, ',', ' ') . ' FCFA' : 'Non défini';
                $sectors = explode(',', str_replace('"', '', $projet_info['secteurs'] ?? ""));
                $sectors_names = array_map(function ($sector_id) use ($secteurs_assoc) {
                    return $secteurs_assoc[$sector_id] ?? 'Non défini';
                }, $sectors);
                $sectors_names = implode(', ', $sectors_names);
                $sector_ids_js = json_encode(array_map('trim', $sectors));

                // Data indicateur
                $valeur_cible = floatval($indicateur['valeur_cible']);
                $valeur_realisee = floatval($suivis_assoc[$indicateur['id']]);
                $pourcentage = $valeur_cible > 0 ? min(100, ($valeur_realisee / $valeur_cible) * 100) : 0;
                $progress_color = $pourcentage >= 80 ? '#4CAF50' : ($pourcentage >= 50 ? '#FFC107' : '#F44336');

                $popup = "
                    <div class='popup-card p-1'>
                        <div class='project-title'>" . $projet_info['name'] . "</div>
                        <div class='project-info d-flex flex-column gap-1'>
                            <span><strong>Code:</strong> " . $projet_info['code'] . "</span>
                            <span><strong>Statut:</strong> <span class='badge bg-" . ($projet_info['status'] == 'actif' ? 'success' : 'warning') . "'>" . $projet_info['status'] . "</span></span>
                            <span><strong>Action:</strong> " . $projet_info['action_type'] . "</span>
                            <span><strong>Budget:</strong> " . $budget . "</span>
                            <span><strong>Période:</strong> " . $start_date . " - " . $end_date . "</span>
                            <span><strong>Secteurs:</strong> " . $sectors_names . "</span>
                        </div>
                        <hr>
                        <div class='project-info d-flex flex-column gap-1'>
                            <span><strong>Indicateur:</strong> " . $indicateur['intitule'] . "</span>
                            <span><strong>Code:</strong> " . $indicateur['code'] . "</span>
                            <span><strong>Unité:</strong> " . $unites_assoc[$indicateur['unite']] . "</span>
                            <span><strong>Responsable:</strong> " . $structures_assoc[$indicateur['responsable']] . "</span>
                            <span><strong>Valeur cible:</strong> " . $indicateur['valeur_cible'] . "</span>
                            <span><strong>Valeur réalisée:</strong> " . $suivis_assoc[$indicateur['id']] . "</span>
                            <div>
                            <strong>Progression:</strong> " . $pourcentage . "%
                            <div class='progress-bar-container'>
                                <div class='progress-bar' style='width: " . $pourcentage . "%; background-color: " . $progress_color . ";'></div>
                            </div>
                            </div>
                        </div>
                        <div class='text-center mt-2'>
                            <a target='_blank' href='../project_view.php?id=" . $projet_info['id'] . "' class='btn btn-sm btn-primary text-white'>Voir détail du projet</a>
                        </div>
                    </div>"; ?>
                    (function() {
                        const m = L.marker([<?= $lat ?>, <?= $lng ?>], {
                            indicateur: {
                                projet_id: "<?= $indicateur['projet_id'] ?>",
                                intitule: "<?= $indicateur['intitule'] ?>",
                                code: "<?= $indicateur['code'] ?>",
                                region: "<?= $indicateur['region'] ?? '' ?>",
                                zone_id: "<?= $indicateur['zone_id'] ?? '' ?>"
                            },
                            projet: {
                                id: "<?= $projet_info['id'] ?>",
                                name: "<?= $projet_info['name'] ?>",
                                code: "<?= $projet_info['code'] ?>",
                                action: "<?= $projet_info['action_type'] ?>",
                                secteurs: <?= $sector_ids_js ?>,
                                budget: <?= $projet_info['budget'] ?? 0 ?>
                            }
                        }).bindPopup(`<?= $popup ?>`);

                        const customIcon = L.divIcon({
                            className: 'stable-marker-container animated-marker',
                            html: `<div class="stable-marker d-flex justify-content-center align-items-center p-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div class="pulse-ring"></div>
                                </div>`,
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });

                        m.setIcon(customIcon);
                        markerCluster.addLayer(m);
                        allMarkers.push(m);

                        setTimeout(() => $(`#proj-<?= $projet_info['id'] ?>`).parent().parent().hide().fadeIn(400), Math.random() * 1000);
                    })();
        <?php endif;
        endforeach; ?>

        map.addLayer(markerCluster);
        progressIndicator.css({
            'width': '100%',
            'transition': 'width 0.3s ease-out'
        });
        setTimeout(() => progressIndicator.css('display', 'none'), 500);

        // =================== Légende ===================
        const legend = L.control({
            position: "bottomright"
        });
        legend.onAdd = function() {
            const div = L.DomUtil.create("div", "legend animated-legend");
            div.innerHTML = `
            <h6>Légende</h6>
            <i style="background: #3388ff"></i> Limites régionales <br>
            <i style="background: #79822f"></i> Zones de collecte <br>
            <i style="background: #f7ae17"></i> Indicateurs <br>
            `;
            return div;
        };
        legend.addTo(map);

        // =================== Contrôles de carte ===================
        $('#zoomIn').on('click', () => {
            animateButton('#zoomIn');
            map.zoomIn();
        });

        $('#zoomOut').on('click', () => {
            animateButton('#zoomOut');
            map.zoomOut();
        });

        $('#locateUser').on('click', () => {
            animateButton('#locateUser');
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const {
                            latitude,
                            longitude
                        } = position.coords;

                        map.flyTo([latitude, longitude], 13, {
                            duration: 1.5
                        });

                        const userMarker = L.marker([latitude, longitude], {
                                icon: L.divIcon({
                                    className: 'user-location-marker',
                                    html: '<div class="user-marker"><i class="fas fa-user"></i></div>',
                                    iconSize: [30, 30],
                                    iconAnchor: [15, 15]
                                })
                            }).addTo(map)
                            .bindPopup(
                                `<div class="p-1">
                                <div class="text-primary text-center mb-1">Votre position actuelle</div>
                                <div class="project-info">
                                    <strong>Latitude:</strong> <span class="text-muted">${latitude}</span><br/>
                                    <strong>Longitude:</strong> <span class="text-muted">${longitude}</span>
                                </div>
                            </div>`
                            )
                            .openPopup();

                        setTimeout(() => userMarker.getElement()?.classList.add('user-marker-bounce'), 1500);
                    },
                    (error) => {
                        showNotification("Impossible de récupérer votre position: " + error.message, 'error');
                    }
                );
            } else {
                showNotification("La géolocalisation n'est pas supportée par votre navigateur", 'error');
            }
        });

        $('#toggleFullscreen').on('click', () => {
            const mapContainer = $('#mapContainer').get(0);

            if (!document.fullscreenElement) {
                if (mapContainer.requestFullscreen) {
                    mapContainer.requestFullscreen();
                } else if (mapContainer.webkitRequestFullscreen) {
                    mapContainer.webkitRequestFullscreen();
                } else if (mapContainer.msRequestFullscreen) {
                    mapContainer.msRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        });

        // =================== Recherche ===================
        $('#searchBtn').on('click', function() {
            animateButton('#searchBtn');
            performSearch();
        });
        $('#searchInput').on('input', function() {
            if ($(this).val().length >= 1) {
                performSearch();
            } else {
                resetFilters();
            }
        });
        $('#searchResults').on('click', (e) => {
            if (!e.target.closest('.search-container')) {
                $('#searchResults').addClass('d-none');
            }
        });

        // =================== Filtres ===================
        $('#filterMapBtn').on('click', function() {
            animateButton('#filterMapBtn');
            applyFilters();
        });

        $('#resetMapBtn').on('click', function() {
            animateButton('#resetMapBtn');
            resetFilters();
        });

        // =================== Mise à jour des statistiques de zone ===================
        $('#closeStatsPanel').on('click', () => {
            $('#statsPanel').addClass('d-none');
        });

        // =================== Changement de fond de carte ===================
        $('#basemapSelect').on('change', function() {
            const basemapType = $(this).val();

            if (currentBasemap) {
                currentBasemap.setOpacity(0.7);
                setTimeout(() => {
                    map.removeLayer(currentBasemap);
                    addNewBasemap(basemapType);
                }, 200);
            } else {
                addNewBasemap(basemapType);
            }
        });

        // =================== Card display ===================
        $('#all-regions').on('change', function() {
            const checked = $(this).is(':checked');
            animateCheckboxGroup('.region-checkbox', checked);
            $('.region-checkbox').prop('checked', checked);
            updateRegionMap();
        });

        $('#all-zones').on('change', function() {
            const checked = $(this).is(':checked');
            animateCheckboxGroup('.zone-checkbox', checked);
            $('.zone-checkbox').prop('checked', checked);
            updateZoneMap();
        });

        $('#all-projets').on('change', function() {
            const checked = $(this).is(':checked');
            animateCheckboxGroup('.proj-checkbox', checked);
            $('.proj-checkbox').prop('checked', checked);
            updateProjMap();
        });

        $(document).on('change', '.region-checkbox', function() {
            animateCheckbox(this);
            updateRegionMap();
        });

        $(document).on('change', '.zone-checkbox', function() {
            animateCheckbox(this);
            updateZoneMap();
        });

        $(document).on('change', '.proj-checkbox', function() {
            animateCheckbox(this);
            updateProjMap();
        });
    }

    // =================== Changement de fond de carte ===================
    function addNewBasemap(basemapType) {
        switch (basemapType) {
            case 'satellite':
                currentBasemap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
                });
                break;
            case 'dark':
                currentBasemap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                    subdomains: 'abcd',
                    maxZoom: 20
                });
                break;
            case 'terrain':
                currentBasemap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxZoom: 17,
                    attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a>'
                });
                break;
            default:
                currentBasemap = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                });
        }

        currentBasemap.addTo(map);
        currentBasemap.setOpacity(0);

        let opacity = 0;
        const fadeIn = setInterval(() => {
            opacity += 0.1;
            currentBasemap.setOpacity(opacity);
            if (opacity >= 1) clearInterval(fadeIn);
        }, 50);
    }

    // =================== FONCTIONS D'ANIMATION ===================
    function animateButton(selector) {
        const button = $(selector);
        button.addClass('button-pulse');
        setTimeout(() => button.removeClass('button-pulse'), 500);
    }

    function animateCheckbox(checkbox) {
        const label = $(checkbox).next('label');
        label.addClass('label-highlight');
        setTimeout(() => label.removeClass('label-highlight'), 1000);
    }

    function animateCheckboxGroup(selector, checked) {
        $(selector).each(function(index) {
            setTimeout(() => animateCheckbox(this), index * 50);
        });
    }

    function animateEndProgress() {
        setTimeout(() => {
            progressIndicator.css('opacity', '0');
            setTimeout(() => progressIndicator.css('display', 'none'), 300);
        }, 500);
    }

    // =================== Notifications ===================
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification notification-${type}">
                <div class="notification-content">
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
                    ${message}
                </div>
            </div>
        `);

        $('body').append(notification);
        notification.hide().fadeIn(300);
        setTimeout(() => notification.fadeOut(300, () => notification.remove()), 3000);
    }

    // =================== Recherche ===================
    function performSearch() {
        const query = $('#searchInput').val().toLowerCase().trim();
        const resultsContainer = $('#searchResults');

        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });
        if (!query) {
            resultsContainer.addClass('d-none');
            searchResultsLayer.clearLayers();
            progressIndicator.css('width', '0%');
            return;
        }

        progressIndicator.css('width', '40%');
        const results = allMarkers.filter(marker => {
            const data = marker.options;
            return (
                data.projet.name.toLowerCase().includes(query) ||
                data.projet.code.toLowerCase().includes(query) ||
                data.indicateur.intitule.toLowerCase().includes(query) ||
                data.indicateur.code.toLowerCase().includes(query)
            );
        });

        if (results.length > 0) {
            let html = '';
            results.forEach((marker, index) => {
                const data = marker.options;
                html += `
                    <div class="search-result-item" data-index="${index}">
                        <strong>${data.projet.name}</strong> (${data.projet.code})<br>
                        <small>${data.indicateur.intitule}</small>
                    </div>
                    `;
            });

            resultsContainer.html(html);
            resultsContainer.removeClass('d-none');
            resultsContainer.find('.search-result-item').each(function(index) {
                $(this).hide().delay(index * 100).fadeIn(300);
            });

            searchResultsLayer.clearLayers();
            resultsContainer.find('.search-result-item').on('click', (e) => {
                e.preventDefault();
                const index = $(e.currentTarget).data('index');
                const marker = results[index];

                map.flyTo(marker.getLatLng(), 15, {
                    duration: 1.5
                });

                setTimeout(() => {
                    marker.openPopup();
                    const markerElement = marker.getElement();
                    if (markerElement) {
                        markerElement.style.transform = 'scale(1.5)';
                        setTimeout(() => markerElement.style.transform = 'scale(1)', 500);
                    }
                }, 1500);

                resultsContainer.slideUp(300);
                $('#searchInput').val('');
            });

            searchResultsLayer.clearLayers();
            results.forEach((marker, index) => {
                setTimeout(() => {
                    const highlightMarker = L.marker(marker.getLatLng(), {
                        icon: L.divIcon({
                            className: 'stable-marker-container animated-marker',
                            html: `<div class="stable-marker bg-danger d-flex justify-content-center align-items-center p-2">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div class="pulse-ring"></div>
                                </div>`,
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        })
                    }).addTo(searchResultsLayer);
                }, index * 100);
            });
            progressIndicator.css('width', '80%');
        } else {
            resultsContainer.html('<div class="search-result-item">Aucun résultat trouvé</div>').slideDown(300);
            resultsContainer.removeClass('d-none');
            searchResultsLayer.clearLayers();
            progressIndicator.css('width', '0%');
        }
        animateEndProgress();
    }

    function updateZoneStats(regionName) {
        const stats = regionStats[regionName] || {
            projets: [],
            budget_total: 0,
            indicateurs_count: 0
        };

        $('#statsRegionName').text(`Statistiques - ${regionName}`);
        $('#zoneProjects').text(stats.projets.length);
        $('#zoneBudget').text(stats.budget_total.toLocaleString() + ' FCFA');
        $('#zoneIndicators').text(stats.indicateurs_count);
        $('#statsPanel').removeClass('d-none').hide().slideDown(400);
    }

    function applyFilters() {
        const actionVal = $('#filterAction').val();
        const secteurVal = $('#filterSecteur').val();
        const scenarioVal = $('#filterScenario').val();

        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });
        markerCluster.clearLayers();

        setTimeout(() => {
            allMarkers.forEach(m => {
                const data = m.options;
                const matchAction = actionVal === "" || data.projet.action.toUpperCase() === actionVal.toUpperCase();
                const matchSecteur = secteurVal === "" || data.projet.secteurs.map(String).includes(secteurVal);
                const matchScenario = scenarioVal === "" || true;

                if (matchAction && matchSecteur && matchScenario) {
                    markerCluster.addLayer(m);
                }
            });

            progressIndicator.css('width', '80%');
            animateEndProgress();
        }, 300);
    }

    function resetFilters() {
        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });

        markerCluster.clearLayers();
        searchResultsLayer.clearLayers();
        allMarkers.forEach((m, index) => setTimeout(() => markerCluster.addLayer(m), index * 20));

        progressIndicator.css('width', '80%');
        animateEndProgress();

        $('#filterAction').val('');
        $('#filterSecteur').val('');
        $('#filterScenario').val('');
    }

    function updateRegionMap() {
        const selectedRegions = $('.region-checkbox:checked').map(function() {
            return $(this).val().toLowerCase();
        }).get();

        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });

        regionsLayer.eachLayer(function(layer) {
            if (map.hasLayer(layer)) {
                layer.setStyle({
                    fillOpacity: 0
                });
                setTimeout(() => map.removeLayer(layer), 300);
            }
        });

        setTimeout(() => {
            if (regionsLayer && selectedRegions.length > 0) {
                regionsLayer.eachLayer(function(layer) {
                    if (layer.regionName && selectedRegions.includes(layer.regionName.toLowerCase())) {
                        layer.setStyle({
                            fillOpacity: 0
                        });
                        layer.addTo(map);

                        let opacity = 0;
                        const fadeInterval = setInterval(() => {
                            opacity += 0.05;
                            layer.setStyle({
                                fillOpacity: opacity * 0.1
                            });
                            if (opacity >= 1) clearInterval(fadeInterval);
                        }, 30);
                    }
                });
            }
            progressIndicator.css('width', '80%');
            animateEndProgress();
            // updateAllMarkers();
        }, 350);
    }

    function updateZoneMap() {
        const selectedZones = $('.zone-checkbox:checked').map(function() {
            return $(this).val().toString().toLowerCase();
        }).get();

        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });

        if (!zoneTabLayers || zoneTabLayers.length === 0) return;

        zoneTabLayers.forEach(zoneLayer => {
            zoneLayer.eachLayer(layer => {
                if (map.hasLayer(layer)) {
                    layer.setStyle({
                        fillOpacity: 0
                    });
                    setTimeout(() => map.removeLayer(layer), 300);
                }
            });
        });

        setTimeout(() => {
            if (selectedZones.length > 0) {
                zoneTabLayers.forEach(zoneLayer => {
                    zoneLayer.eachLayer(layer => {
                        if (layer.zoneName && selectedZones.includes(layer.zoneName.toLowerCase())) {
                            layer.setStyle({
                                fillOpacity: 0
                            });
                            layer.addTo(map);

                            let opacity = 0;
                            const fadeInterval = setInterval(() => {
                                opacity += 0.05;
                                layer.setStyle({
                                    fillOpacity: opacity * 0.05
                                });
                                if (opacity >= 1) clearInterval(fadeInterval);
                            }, 30);
                        }
                    });
                });
            }

            progressIndicator.css('width', '80%');
            animateEndProgress();
            // updateAllMarkers();
        }, 350);
    }

    function updateProjMap() {
        progressIndicator.css({
            'width': '20%',
            'display': 'block',
            'opacity': '1'
        });

        setTimeout(() => {
            updateAllMarkers();
            progressIndicator.css('width', '80%');
            animateEndProgress();
        }, 150);
    }

    function updateAllMarkers() {
        const selectedRegions = $('.region-checkbox:checked').map(function() {
            return $(this).val().toLowerCase();
        }).get();

        const selectedZones = $('.zone-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        const selectedProjets = $('.proj-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        progressIndicator.css('width', '40%');
        markerCluster.clearLayers();
        setTimeout(() => {
            let addedMarkers = 0;
            allMarkers.forEach(m => {
                const markerData = m.options;
                const markerRegion = (markerData.indicateur.region || '').toLowerCase();
                const markerZone = (markerData.indicateur.zone_id || '').toString();
                const markerProjet = markerData.projet.id.toString();

                const showByRegion = selectedRegions.length === 0 || selectedRegions.includes(markerRegion);
                const showByZone = selectedZones.length === 0 || selectedZones.includes(markerZone);
                const showByProjet = selectedProjets.length === 0 || selectedProjets.includes(markerProjet);

                if (showByRegion && showByZone && showByProjet) {
                    setTimeout(() => markerCluster.addLayer(m), addedMarkers * 30);
                    addedMarkers++;
                }
            });

            progressIndicator.css('width', '60%');

            if (selectedRegions.length === 0 && selectedZones.length === 0 && selectedProjets.length === 0) {
                markerCluster.clearLayers();
            }

            progressIndicator.css('width', '80%');
            animateEndProgress();
        }, 300);
    }

    document.addEventListener("DOMContentLoaded", initMap);
</script>