<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// Importation des fichies de configuration
require_once 'config/connexion.php';
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'services/user.mailer.php';

// Vérification de l'authentification
checkAuth();
loadVarEnv();

$database = new Database();
$db = $database->getConnection();

// Vérification des permissions
if (checkPermis($db, 'interdite')) {
    header("Location: interdite.php");
    exit();
}

//Importation de tous les models
$modelsDir = 'models';
foreach (glob("$modelsDir/*.php") as $modelFile) {
    require_once $modelFile;
}
?>

<!-- C:\xampp\htdocs\Workspace PHP\mrv-burundi\assets\css\theme.min.css ===> Couleur link-->

<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png" />
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png" />
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png" />
<link rel="shortcut icon" type="image/x-icon" href="assets/favicon/favicon.png" />
<link rel="manifest" href="assets/favicon/manifest.json" />
<meta name="theme-color" content="#ffffff" />

<script src="vendors/simplebar/simplebar.min.js"></script>
<script src="assets/js/config.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/data.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<!-- ===============================================-->
<!--    Stylesheets-->
<!-- ===============================================-->
<link href="vendors/swiper/swiper-bundle.min.css" rel="stylesheet">
<link href="vendors/flatpickr/flatpickr.min.css" rel="stylesheet">
<link href="vendors/choices/choices.min.css" rel="stylesheet">
<link href="vendors/prism/prism-okaidia.css" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet" />

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.1/dist/MarkerCluster.Default.css" />

<!-- datatable css -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.3/css/dataTables.jqueryui.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.4/css/buttons.jqueryui.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />

<link href="assets/css/theme-rtl.css" type="text/css" rel="stylesheet" id="style-rtl" />
<link href="assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default" />
<link href="assets/css/user-rtl.min.css" type="text/css" rel="stylesheet" id="user-style-rtl" />
<link href="assets/css/user.min.css" type="text/css" rel="stylesheet" id="user-style-default" />
<link href="assets/unicons/css/line.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    var phoenixIsRTL = window.config.config.phoenixIsRTL;
    const token = localStorage.getItem('authtkmrv');

    if (!token) {
        window.location.href = 'index.php';
    }

    if (phoenixIsRTL) {
        var linkDefault = document.getElementById("style-default");
        var userLinkDefault = document.getElementById("user-style-default");
        linkDefault.setAttribute("disabled", true);
        userLinkDefault.setAttribute("disabled", true);
        document.querySelector("html").setAttribute("dir", "rtl");
    } else {
        var linkRTL = document.getElementById("style-rtl");
        var userLinkRTL = document.getElementById("user-style-rtl");
        linkRTL.setAttribute("disabled", true);
        userLinkRTL.setAttribute("disabled", true);
    }
</script>

<link href="vendors/leaflet/leaflet.css" rel="stylesheet" />
<link href="vendors/leaflet.markercluster/MarkerCluster.css" rel="stylesheet" />
<link href="vendors/leaflet.markercluster/MarkerCluster.Default.css" rel="stylesheet" />
<link href="vendors/dropzone/dropzone.css" rel="stylesheet">


<style>
    :root {
        --bd-green: #0a7f5a;
        --bd-green-light: #2fa07a;
        --bd-green-dark: #055f43;
        --bd-red: #b3263a;
        --bd-white: #f6f8f7;
        --bd-gray: #dddddd;
    }

    #sidebarDefault {
        background: linear-gradient(90deg, var(--bd-green), var(--bd-green-dark)) !important;
        border-right: 5px solid var(--bd-green-light) !important;
    }

    #sidebarDefault .nav-link {
        color: var(--bd-white) !important;
        transition: all 0.25s ease;
    }

    #sidebarDefault .dropdown-indicator {
        padding-top: 4px;
        padding-bottom: 4px;
    }

    #sidebarDefault .nav-link .dropdown-indicator-icon {
        color: var(--bd-white) !important;
    }

    #sidebarDefault .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        color: var(--bd-white) !important;
    }

    #sidebarDefault .nav-link.active {
        background: var(--bd-green-light) !important;
        color: var(--bd-white) !important;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
    }

    #sidebarDefault .nav-link.active .nav-link-icon,
    #sidebarDefault .nav-link.active .dropdown-indicator-icon {
        color: var(--bd-white) !important;
    }

    #sidebarDefault .nav.parent .nav-link {
        color: var(--bd-white) !important;
    }

    #sidebarDefault .nav.parent .nav-link.active {
        background: var(--bd-green-dark) !important;
        color: var(--bd-green-dark) !important;
    }

    #sidebarDefault .navbar-vertical-label {
        background: var(--bd-gray) !important;
        color: var(--bd-red) !important;
        border-left: 3px solid var(--bd-red);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 3px;
    }

    #sidebarDefault .navbar-vertical-footer {
        background: var(--bd-green-dark) !important;
        border-top: 1px solid var(--bd-green-light);
        border-right: 5px solid var(--bd-green-light) !important;
    }

    #sidebarDefault .navbar-vertical-footer button {
        color: var(--bd-green-light);
    }

    #sidebarDefault .navbar-vertical-footer button:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    #sidebarDefault .parent {
        background: var(--bd-green) !important;
        overflow: hidden;
        border: 0px;
    }

    #sidebarDefault .parent .collapsed-nav-item-title {
        color: var(--bd-white) !important;
        background: var(--bd-green-dark);
    }

    #sidebarDefault .parent .nav-link.active {
        background: var(--bd-green-light) !important;
        color: var(--bd-green-dark) !important;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
    }

    #sidebarDefault .nav-link-text,
    #sidebarDefault .nav-link-icon {
        color: var(--bd-white) !important;
    }

    #sidebarDefault .nav-item-wrapper:hover .nav-link-icon {
        color: var(--bd-green-dark) !important;
    }
    .select2-selection--single,
    .select2-selection--multiple {
        min-height: 37px !important;
        font-size: 13px !important;
        padding: 3px 6px !important;
        border: 1px solid #ccd1de !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
    }

    .notification-card {
      transition: all 0.2s ease-in-out;
      cursor: pointer;
    }

    .notification-card:hover {
      background-color: rgba(var(--bs-primary-rgb), 0.05);
      transform: translateX(4px);
    }

    .notification-card.unread {
      background-color: rgba(var(--bs-primary-rgb), 0.02);
      border-left: 3px solid var(--bs-primary);
    }

    .notification-card.read {
      opacity: 0.9;
    }

    .notification-card.read h4 {
      font-weight: 400;
    }

    .nav-link.active {
      background-color: rgba(var(--bs-primary-rgb), 0.1);
      border-right: 3px solid var(--bs-primary);
    }

    .badge-count {
      background-color: var(--bs-primary);
      color: white;
      padding: 0.25rem 0.5rem;
      border-radius: 2rem;
      font-size: 0.75rem;
      min-width: 1.5rem;
      text-align: center;
    }

    .btn-icon {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .btn-icon:hover {
      transform: scale(1.1);
    }

    .simplebar-scrollable-y {
      max-height: calc(100vh - 200px);
      overflow-y: auto;
    }
  </style>