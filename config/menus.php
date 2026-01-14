<?php
/* Menu Titles */
$MENU_TITLE = [
    1 => ["Utilisateurs", "fas fa-user", "users.php"],
    2 => ["Paramétrages", "fas fa-tools", "parametrage.php"],
    3 => ["Inventaire", "fas fa-server", "inventory.php"],
    4 => ["Projets", "fas fa-briefcase", "projects.php"],
    5 => ["Suivi des résultats", "fas fa-database", "data.php"],
    6 => ["Analyse budgétaire", "fas fa-pie-chart", "analyse_budgetaire.php"],
    7 => ["Rapports", "fas fa-pie-chart", "rapports.php"],
    8 => ["Documentation", "fas fa-folder", "documents.php"],
];

/* Menu Definitions */
$MENU_ITEMS = [
    1 => [
        "users.php" => "Utilisateurs",
        "roles.php" => "Rôles & Permissions",
        "acteurs.php" => "Acteurs",
    ],
    2 => [
        "localites.php" => "Localités",
        "groups.php" => "Groupes de travail",
        "referentiels.php" => "Dictionnaire des indicateurs",
        "autres_parametres.php" => "Autres paramètres",
    ],
    3 => [
        "inventory.php" => "Registre des inventaires",
    ],
    4 => [
        "projects.php" => "Projets",
        "cadre_resultat_cr.php" => "Cadre de résultat",
        "cartographie.php" => "Cartographie",
        "zones_collecte.php" => "Zones de collecte",
    ],
    5 => [
        "suivi_activites.php" => "Suivi activités",
        "suivi_indicateurs.php" => "Suivi indicateurs",
        // "fiche_dynamique.php" => "Fiches Dynamiques",
        "resultats_obtenus.php" => "Résultats obtenus",
    ],
    6 => [
        "analyse_budgetaire.php" => "Analyse budgétaire",
    ],
    7 => [
        "rapport_periodique.php" => "Rapports Periodiques",
        "rapport_sectoriel.php" => "Données sectorielles",
        // "rapport_dynamique.php" => "Rapports Dynamiques",
    ],
    8 => [
        "documents.php" => "Documents",
    ],
];
