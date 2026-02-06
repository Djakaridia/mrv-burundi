<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tableau de bord | MRV - Burundi</title>

  <?php
  include './components/navbar & footer/head.php';

  $sections_dash = new SectionDash($db);
  $sections_dash = $sections_dash->read();
  $sections_dash = array_filter($sections_dash, function ($section_dash) {
    return $section_dash['state'] == 'actif';
  });
  usort($sections_dash, function ($a, $b) {
    return $a['position'] - $b['position'];
  });

  $structure = new Structure($db);
  $structures = $structure->read();
  $structures = array_filter($structures, function ($structure) {
    return $structure['state'] == 'actif';
  });

  $secteur = new Secteur($db);
  $secteurs = $secteur->read();
  $secteurs = array_filter($secteurs, function ($secteur) {
    return $secteur['state'] == 'actif' && $secteur['parent'] == 0;
  });

  $projet = new Projet($db);
  $projets = $projet->read();
  $projets = array_filter($projets, function ($projet) {
    return $projet['state'] == 'actif';
  });

  $user = new User($db);
  $users = $user->read();
  $users = array_filter($users, function ($user) {
    return $user['state'] == 'actif';
  });

  $group = new GroupeTravail($db);
  $groups_travail = $group->read();
  $groups_travail = array_filter($groups_travail, function ($group) {
    return $group['state'] == 'actif';
  });

  $reunion = new Reunion($db);
  $reunions = $reunion->read();
  $reunions = array_filter($reunions, function ($reunion) {
    return $reunion['state'] == 'actif';
  });

  $document = new Documents($db);
  $documents = $document->read();
  $documents = array_filter($documents, function ($document) {
    return $document['state'] == 'actif';
  });

  $tache = new Tache($db);
  $taches = $tache->read();
  $taches = array_filter($taches, function ($tache) {
    return $tache['state'] == 'actif';
  });

  $referentiel = new Referentiel($db);
  $referentiels = $referentiel->read();
  $referentiels_dash = array_filter($referentiels, function ($referentiel) {
    return ($referentiel['state'] == 'actif' && $referentiel['in_dashboard'] == 1);
  });
  sort($referentiels_dash);

  // Actions & Mesures
  $mesure = new Mesure($db);
  $mesures = $mesure->read();
  $actionsWithEmission = [];
  foreach ($mesures as $mesure_curr) {
    $suivi_mesure = new Suivi($db);
    $suivi_mesure->mesure_id = $mesure_curr['id'];
    $suivis_raw = $suivi_mesure->readByMesure();
    $emissionEvitee = 0;

    foreach ($suivis_raw as $suivi) {
      if (!empty($suivi['valeur'])) {
        $emissionEvitee += floatval(str_replace(',', '.', $suivi['valeur']));
      }
    }

    $mesure_curr['emission_evitee'] = $emissionEvitee;
    $actionsWithEmission[] = $mesure_curr;
  }

  // Inventaires GES
  $inventory = new Inventory($db);
  $current_inventory = $inventory->read();
  $current_inventory = array_filter($current_inventory, function ($inventory) {
    return $inventory['afficher'] == 'oui';
  });
  $current_inventory = array_pop($current_inventory);
  $current_inventory_data = json_decode(!empty($current_inventory) ? $inventory->readData($current_inventory['viewtable']) : '', true);

  if (!empty($current_inventory_data['data'])) {
    // ==================> Graphisme Evolution nette
    $g2_years = [];
    $g2_net_emissions_by_year = [];
    foreach ($current_inventory_data['data'] as $row) {
      $year = (int) ($row['annee'] ?? 0);
      if (!$year) continue;

      $net = normalizeNumber($row['emissions_nettes'] ?? (($row['total_emissions'] ?? 0) - ($row['total_absorptions'] ?? 0)));
      if (!isset($g2_net_emissions_by_year[$year])) $g2_net_emissions_by_year[$year] = 0;
      $g2_net_emissions_by_year[$year] += $net;
    }

    ksort($g2_net_emissions_by_year);
    $g2_years = array_keys($g2_net_emissions_by_year);
    $g2_values = array_map(fn($v) => round($v, 3), array_values($g2_net_emissions_by_year));

    // ==================> Graphisme Evolution vs Absorption
    $g3_years = [];
    $g3_emissions = [];
    $g3_absorptions = [];

    foreach ($current_inventory_data['data'] as $row) {
      $year = (int)$row['annee'];
      if (!$year) continue;

      $g3_years[] = $year;
      $g3_emissions[] = round(normalizeNumber($row['total_emissions'] ?? 0), 3);
      $g3_absorptions[] = round(abs(normalizeNumber($row['total_absorptions'] ?? 0)), 3);
    }
  }


  // Registre GES
  $register = new Register($db);
  $registers = $register->read();
  $registre_inventory = [];
  if ($current_inventory) {
    $registre_inventory = array_filter($registers, function ($register) use ($current_inventory) {
      return $register['inventaire_id'] == $current_inventory['id'];
    });
  }

  $grouped_registers = [];
  foreach ($registre_inventory as $register) {
    $grouped_registers[$register['secteur_id']][] = $register;
  }

  $gaz = new Gaz($db);
  $gazs = $gaz->read();
  $gaz_colors = [];
  foreach ($gazs as $g) {
    $gaz_colors[strtoupper($g['name'])] = $g['couleur'] ?: '#' . substr(md5($g['name']), 0, 6);
  }

  $secteur = new Secteur($db);
  $data_secteurs = $secteur->read();
  $secteurs = array_filter(array_reverse($data_secteurs), function ($secteur) {
    return $secteur['parent'] == 0;
  });

  if (!empty($registre_inventory)) {
    $global_ges_data = [];

    // 1. Préparation des données par secteur
    $sector_totals = [];
    $sector_gaz_totals = [];
    $sector_annual_totals = [];
    $sector_names = [];

    foreach ($data_secteurs as $s) {
      if ($s['parent'] == 0) {
        $sector_names[$s['id']] = $s['name'];
      }
    }

    // 2. Agrégation des données globales
    foreach ($registre_inventory as $row) {
      $secteur_id = $row['secteur_id'];
      $annee = $row['annee'];
      $gaz_name = strtoupper(trim($row['gaz']));

      $normalized_gaz = $gaz_name;
      foreach ($gazs as $system_gaz) {
        if ($gaz_name === strtoupper($system_gaz['name'])) {
          $normalized_gaz = strtoupper($system_gaz['name']);
          break;
        }
      }

      if (!isset($sector_totals[$secteur_id])) {
        $sector_totals[$secteur_id] = 0;
      }
      $sector_totals[$secteur_id] += $row['emission_absolue'];

      if (!isset($sector_gaz_totals[$secteur_id][$normalized_gaz])) {
        $sector_gaz_totals[$secteur_id][$normalized_gaz] = 0;
      }
      $sector_gaz_totals[$secteur_id][$normalized_gaz] += $row['emission_absolue'];

      if (!isset($sector_annual_totals[$secteur_id][$annee])) {
        $sector_annual_totals[$secteur_id][$annee] = 0;
      }
      $sector_annual_totals[$secteur_id][$annee] += $row['emission_absolue'];
    }

    // a) Graphique secteur/année (colonnes groupées)
    $global_column_categories = [];
    $global_series_data = [];
    $years = [];

    foreach ($registre_inventory as $row) {
      if (!in_array($row['annee'], $years)) {
        $years[] = $row['annee'];
      }
    }
    sort($years);
    $global_column_categories = $years;

    foreach ($sector_totals as $secteur_id => $total) {
      if (isset($sector_names[$secteur_id])) {
        $sector_data = [];
        foreach ($years as $year) {
          $sector_data[] = isset($sector_annual_totals[$secteur_id][$year]) ?
            $sector_annual_totals[$secteur_id][$year] : 0;
        }

        $global_series_data[] = [
          'name' => $sector_names[$secteur_id],
          'data' => $sector_data
        ];
      }
    }

    // b) Graphique secteur/gaz (donut/stacked)
    $gaz_global_totals = [];
    foreach ($sector_gaz_totals as $secteur_gazs) {
      foreach ($secteur_gazs as $gaz => $value) {
        if (!isset($gaz_global_totals[$gaz])) {
          $gaz_global_totals[$gaz] = 0;
        }
        $gaz_global_totals[$gaz] += $value;
      }
    }

    foreach ($gaz_global_totals as $gaz => $total) {
      if ($total > 0) {
        $color = $gaz_colors[$gaz] ?? '#' . substr(md5($gaz), 0, 6);
        $global_ges_data[] = [
          'name' => $gaz,
          'y' => $total,
          'color' => $color
        ];
      }
    }
  }

  ?>
</head>

<body class="light">
  <main class="main" id="top">
    <?php include './components/navbar & footer/sidebar.php'; ?>
    <?php include './components/navbar & footer/navbar.php'; ?>

    <div class="content">
      <!-- Section 1: Principaux -->
      <?php include './components/ui/main_section.php'; ?>

      <!-- Section 2: Résumé Rapide -->
      <div class="row g-4 mx-n6">
        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-info bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fa-solid fa-city text-info fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Acteurs</h6>
                  <h4 class="mb-0 text-info"><?= count($structures) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./acteurs.php" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-success bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fab fa-twitch text-success fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Secteurs</h6>
                  <h4 class="mb-0 text-success"><?= count($secteurs) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./sectors.php" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-warning bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fa-solid fa-briefcase text-warning fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Projets</h6>
                  <h4 class="mb-0 text-warning"><?= count($projets) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./projects.php" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-danger bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fa-solid fa-users text-danger fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Groupes</h6>
                  <h4 class="mb-0 text-danger"><?= count($groups_travail) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./groups.php" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-primary bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fa-solid fa-calendar text-primary fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Réunions</h6>
                  <h4 class="mb-0 text-primary"><?= count($reunions) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./groups.php?tab=meet" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2 mb-3">
          <div class="card card-float rounded-1 shadow-sm border-start border border-body dashboard-card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon-wrapper-sm bg-secondary bg-opacity-10 rounded-2 me-3 p-2">
                  <span class="fa-solid fa-folder text-secondary fs-6"></span>
                </div>
                <div>
                  <h6 class="mb-1 text-body-tertiary">Documents</h6>
                  <h4 class="mb-0 text-secondary"><?= count($documents) ?></h4>
                </div>
              </div>
            </div>
            <div class="card-footer bg-transparent p-2 border-top">
              <a href="./documents.php" class="btn btn-sm btn-link text-muted p-0 fs-10">
                Voir détails <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 3: Alertes et Délais -->
      <div class="row g-4 mx-n6">
        <div class="col-md-8 mb-3">
          <div class="card h-100 rounded-1 shadow-sm">
            <div class="card-header rounded-top-1 py-2 px-3 bg-primary d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-white"><i class="fas fa-chart-area me-2"></i>Comparaison cibles vs réalisations</h5>
            </div>
            <div class="card-body p-3">
              <?php include './components/ui/main_swiper.php'; ?>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-exclamation-triangle me-2"></i>Actions climatiques</h5>
            </div>
            <div class="card-body p-0">
              <?php foreach (array_slice($actionsWithEmission, 0, 5) as $action): ?>
                <div onclick="window.location.href = 'mesure_view.php?id=<?= $action['id'] ?>';" class="list-group-item list-group-item-action cursor-pointer shadow-sm mb-1 py-2 px-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-1 text-truncate">
                      <?= html_entity_decode($action['name']) ?>
                    </h6>
                    <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-primary text-capitalize rounded-pill ms-2">
                      <?= number_format($action['emission_evitee'], 2, ',', ' ') ?> Gg Eq. CO2
                    </span>
                  </div>

                  <p class="mb-0 fs-10 text-body-secondary">
                    Type : <?= listTypeAction()[$action['action_type']] ?> |
                    Statut : <?= ucfirst($action['status']) ?>
                  </p>
                </div>
              <?php endforeach; ?>

            </div>
            <div class="card-footer bg-transparent py-2 text-center">
              <a href="./mesures.php" class="btn btn-sm btn-link text-warning p-0 fs-10">
                Voir toutes les actions <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 4: Analyse Sectorielle -->
      <div class="row g-4 mx-n6 d-none">
        <div class="col-md-6 mb-3">
          <div class="card h-100 rounded-1 shadow-sm">
            <div class="card-header rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-chart-pie me-2"></i>Répartition sectorielle des suivis</h5>
            </div>
            <div class="card-body p-3">
              <?php include './components/ui/main_slider_left.php'; ?>
            </div>
          </div>
        </div>

        <div class="col-md-6 mb-3">
          <div class="card h-100 rounded-1 shadow-sm">
            <div class="card-header rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-chart-line me-2"></i>Évolution par secteur des suivis</h5>
            </div>
            <div class="card-body p-3">
              <?php include './components/ui/main_slider_right.php'; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 5: Emissions des inventaire -->
      <div class="row g-4 mx-n6">
        <div class="col-12 col-md-6 mb-3">
          <div class="card rounded-1 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-end rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-bar-chart me-2"></i> Évolution des émissions nettes</h5>
              <button type="button" onclick="window.location.href='inventory.php'" class="btn btn-sm py-1 px-2 rounded-1 bg-primary-subtle rounded-1 fs-10">Voir inventaires</button>
            </div>
            <div class="card-body p-2">
              <?php if (!empty($current_inventory_data['data'])) { ?>
                <div id="emissionsNettesChart" style="height: 350px;"></div>
              <?php } else { ?>
                <div class="text-center py-5 my-3" style="min-height: 300px;">
                  <div class="d-flex justify-content-center mb-3">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                      <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h4 class="text-800 mb-3">Aucune inventaire actif trouvé</h4>
                  <p class="text-600 mb-5">Veuillez ajouter un inventaire pour afficher ses graphiques</p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="card rounded-1 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-end rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-wind me-2"></i> Évolution des émissions vs absorptions</h5>
              <button type="button" onclick="window.location.href='inventory.php'" class="btn btn-sm py-1 px-2 rounded-1 bg-primary-subtle rounded-1 fs-10">Voir inventaires</button>
            </div>
            <div class="card-body p-2">
              <?php if (!empty($current_inventory_data['data'])) { ?>
                <div id="emissionAbsorptionChart" style="height: 350px;"></div>
              <?php } else { ?>
                <div class="text-center py-5 my-3" style="min-height: 300px;">
                  <div class="d-flex justify-content-center mb-3">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                      <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h4 class="text-800 mb-3">Aucune inventaire actif trouvé</h4>
                  <p class="text-600 mb-5">Veuillez ajouter un inventaire pour afficher ses graphiques</p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 6: Emissions des registres -->
      <div class="row g-4 mx-n6">
        <div class="col-12 col-md-6 mb-3">
          <div class="card rounded-1 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-end rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-bar-chart me-2"></i> Émissions par type de gaz</h5>
              <button type="button" onclick="window.location.href='register_carbone.php'" class="btn btn-sm py-1 px-2 rounded-1 bg-primary-subtle rounded-1 fs-10">Voir registres</button>
            </div>
            <div class="card-body p-2">
              <?php if (!empty($registre_inventory)) { ?>
                <div id="registreGlobalGaz" style="height: 350px;"></div>
              <?php } else { ?>
                <div class="text-center py-5 my-3" style="min-height: 300px;">
                  <div class="d-flex justify-content-center mb-3">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                      <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h4 class="text-800 mb-3">Aucun inventaire actif trouvé</h4>
                  <p class="text-600 mb-5">Veuillez ajouter un inventaire pour afficher ses graphiques</p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="card rounded-1 shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-end rounded-top-1 py-2 px-3 bg-primary">
              <h5 class="mb-0 text-white"><i class="fas fa-bar-chart me-2"></i> Émissions par secteur et année</h5>
              <button type="button" onclick="window.location.href='register_carbone.php'" class="btn btn-sm py-1 px-2 rounded-1 bg-primary-subtle rounded-1 fs-10">Voir registres</button>
            </div>
            <div class="card-body p-2">
              <?php if (!empty($registre_inventory)) { ?>
                <div id="registreGlobalSecteurAnnee" style="height: 350px;"></div>
              <?php } else { ?>
                <div class="text-center py-5 my-3" style="min-height: 300px;">
                  <div class="d-flex justify-content-center mb-3">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-warning">
                      <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </div>
                  <h4 class="text-800 mb-3">Aucun inventaire actif trouvé</h4>
                  <p class="text-600 mb-5">Veuillez ajouter un inventaire pour afficher ses graphiques</p>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 6: Tableau des Projets -->
      <div class="row g-4 mx-n6">
        <div class="col-12 mb-3">
          <div class="card rounded-1 shadow-sm h-100">
            <div class="card-header rounded-top-1 py-2 px-3 bg-primary d-flex justify-content-between align-items-center">
              <h5 class="mb-0 text-white"><i class="fas fa-briefcase me-2"></i>Listes des projets climatiques</h5>
            </div>
            <div class="card-body p-1 table-responsive scrollbar">
              <table class="table fs-9 table-bordered mb-0 border-top border-translucent" id="id-datatable">
                <thead class="table-light">
                  <tr>
                    <th>Intitulé du projet</th>
                    <th>Responsable</th>
                    <th class="text-center">Secteur</th>
                    <th class="text-center">Période</th>
                    <th class="text-center">Statut</th>
                    <th class="text-center">Progression</th>
                    <th class="text-center" style="width: 100px;">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($projets as $projet):
                    $logoParts = explode("../", $projet['logo'] ?? '');

                    $tache_projet = new Tache($db);
                    $tache_projet->projet_id = $projet['id'];
                    $taches_projet = $tache_projet->readByProjet();
                    $taches_projet = array_filter($taches_projet, function ($tache) {
                      return $tache['state'] == 'actif';
                    });

                    $totalTacheCount = count($taches_projet);
                    $finishedTacheCount = count(array_filter($taches_projet, function ($tache) {
                      return strtolower($tache['status']) === 'terminée';
                    }));
                    $progress = $totalTacheCount > 0 ? (round(($finishedTacheCount / $totalTacheCount), 2) * 100) : 0;
                  ?>

                    <tr>
                      <td><a href="project_view.php?id=<?= $projet['id'] ?>" class="text-muted fw-bold"><?= html_entity_decode($projet['name']) ?></a></td>
                      <td><span class="fw-semibold"><?= $projet['structure_sigle'] ?></span></td>
                      <td class="text-center">
                        <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-light rounded-pill">
                          <?php foreach ($secteurs as $secteur) {
                            if ($secteur['id'] == $projet['secteur_id']) {
                              echo $secteur['name'];
                              break;
                            }
                          } ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-phoenix fs-10 py-1 badge-phoenix-warning rounded-pill">
                          <?= date('Y', strtotime($projet['start_date'])) ?> - <?= date('Y', strtotime($projet['end_date'])) ?>
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-phoenix fs-10 py-1 rounded-pill badge-phoenix-<?= getBadgeClass($projet['status']); ?>">
                          <?= listStatus()[$projet['status']]; ?>
                        </span>
                      </td>
                      <td class="text-center p-0">
                        <a onclick="window.location.href='suivi_activites.php?proj=<?= $projet['id'] ?>'" class="btn btn-link text-decoration-none fw-bold py-1 px-0 m-0">
                          <?php
                          if ($progress < 39)
                            $color = "danger";
                          elseif ($progress < 69)
                            $color = "warning";
                          elseif ($progress >= 70)
                            $color = "success"; ?>
                          <span id="tauxProj_<?php echo $projet['id']; ?>">
                            <div class="progress progress-xl rounded-0 p-0 m-0" style="height: 1.5rem; width: 200px">
                              <div class="progress-bar progress-bar-striped progress-bar-animated fs-14 fw-bold bg-<?php echo $color; ?> " aria-valuenow="70" style="width: 100%;">
                                <?php echo (isset($progress) && $progress > 0) ? $progress . " %" : "Non entamé"; ?>
                              </div>
                            </div>
                          </span>
                        </a>
                      </td>
                      <td>
                        <div class="d-flex gap-2">
                          <button title="Voir" class="btn btn-sm btn-phoenix-primary fs-10 px-2 py-1" data-bs-toggle="modal"
                            data-bs-target="#projectsCardViewModal" data-id="<?= $projet['id'] ?>"><span class="uil-eye fs-8"></span></button>
                          <a title="Consulter" href="project_view.php?id=<?= $projet['id'] ?>" class="btn btn-sm px-2 py-1 btn-phoenix-success">
                            <span class="uil-arrow-right fs-8"></span>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>

  <!-- ===============================================-->
  <!--    JavaScripts-->
  <!-- ===============================================-->
  <?php include './components/navbar & footer/foot.php'; ?>

  <!-- Leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
  <script>
    mrvLineChart({
      id: 'emissionsNettesChart',
      title: 'Évolution des émissions nettes',
      unite: <?= json_encode($current_inventory['unite'] ?? 'Gg éq. CO₂') ?>,
      categories: <?= json_encode($g2_years ?? []) ?>,
      data: <?= json_encode($g2_values ?? []) ?>
    });

    mrvGroupedBarChart({
      id: 'emissionAbsorptionChart',
      title: 'Émissions et absorptions par année',
      unite: <?= json_encode($current_inventory['unite'] ?? "") ?>,
      categories: <?= json_encode($g3_years ?? []) ?>,
      series: [{
        name: 'Émissions',
        data: <?= json_encode($g3_emissions ?? []) ?>
      }, {
        name: 'Absorptions',
        data: <?= json_encode($g3_absorptions ?? []) ?>
      }]
    });

    mrvDonutChart({
      id: 'registreGlobalGaz',
      title: 'Répartition globale des émissions par type de gaz',
      unite: 'kt eq. CO₂',
      data: <?= json_encode($global_ges_data ?? []) ?>,
    });

    mrvGroupedBarChart({
      id: 'registreGlobalSecteurAnnee',
      title: 'Émissions par secteur et année',
      unite: 'kt eq. CO₂',
      categories: <?= json_encode($global_column_categories ?? []) ?>,
      series: <?= json_encode($global_series_data ?? []) ?>
    });
  </script>
</body>

</html>