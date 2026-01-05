<!DOCTYPE html>
<html
  lang="fr"
  dir="ltr"
  data-navigation-type="default"
  data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- ===============================================-->
  <title>Réinitialiser le mot de passe - MRV Burundi</title>

  <!-- ===============================================-->
  <!--    Favicons-->
  <!-- ===============================================-->
  <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png" />
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png" />
  <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png" />
  <link rel="shortcut icon" type="image/x-icon" href="assets/favicon/favicon.png" />
  <link rel="manifest" href="assets/favicon/manifest.json" />
  <meta name="theme-color" content="#ffffff" />
  <script src="vendors/simplebar/simplebar.min.js"></script>
  <script src="assets/js/config.js"></script>

  <!-- ===============================================-->
  <!--    Stylesheets-->
  <!-- ===============================================-->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&amp;display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" />
  <link href="assets/css/theme-rtl.css" type="text/css" rel="stylesheet" id="style-rtl" />
  <link href="assets/css/theme.min.css" type="text/css" rel="stylesheet" id="style-default" />
  <link href="assets/css/user-rtl.min.css" type="text/css" rel="stylesheet" id="user-style-rtl" />
  <link href="assets/css/user.min.css" type="text/css" rel="stylesheet" id="user-style-default" />
  <style>
    .alert-message {
      position: absolute;
      z-index: 101;
      top: 0;
      left: 0;
      right: 0;
      text-align: center;
      line-height: 2.5;
      overflow: hidden;
      -webkit-box-shadow: 0 0 5px gray;
      -moz-box-shadow: 0 0 5px gray;
      box-shadow: 0 0 5px gray;
    }
    @-webkit-keyframes slideDown {
      0%, 100% { -webkit-transform: translateY(-50px); }
      10%, 90% { -webkit-transform: translateY(0px); }
    }
    @-moz-keyframes slideDown {
      0%, 100% { -moz-transform: translateY(-50px); }
      10%, 90% { -moz-transform: translateY(0px); }
    }
    .cssanimations.csstransforms .alert-message {
      -webkit-transform: translateY(-50px);
      -webkit-animation: slideDown 2.5s 1.0s 1 ease forwards;
      -moz-transform:    translateY(-50px);
      -moz-animation:    slideDown 2.5s 1.0s 1 ease forwards;
    }
  </style>
  <?php
  session_start();
  include_once './config/database.php';
  include_once './models/User.php';

  $database = new Database();
  $db = $database->getConnection();
  $user = new User($db);

  // Vérification de l'existence du token et de l'id dans la base de données
  $username = $_SESSION['tk-username'] ?? null;
  $code = $_SESSION['tk-code'] ?? null;
  $email = $_SESSION['tk-email'] ?? null;

  if (empty($username) || empty($code) || empty($email)) {
    header('Location: forget_password.php?token=no');
    exit;
  }

  // Recuperation du code de verification
  $user->email = $email;
  $code_db = $user->readCodeVerify($code);

  // Modification du mot de passe de l'utilisateur avec le formutlaire
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST['code'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    if ($code !== $code_db['code']) {
      $error_message = "Le code de vérification est invalide.";
    } elseif ($new_password !== $confirm_password) {
      $error_message = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($new_password) < 4) {
      $error_message = "Le mot de passe doit contenir au moins 4 caractères.";
    } else {
      session_destroy();
      $user->deleteCodeVerify();
      $user->username = $username;
      $user->password = $new_password;

      if ($user->resetPassword($username, $new_password)) {
        header('Location: index.php?status=ok');
        exit;
      } else {
        $error_message = "Une erreur est survenue lors de la mise à jour du mot de passe.";
      }
    }
  }
  ?>

  <script>
    var phoenixIsRTL = window.config.config.phoenixIsRTL;
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
</head>

<body>
  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <main class="main" id="top">
    <div class="row vh-100 g-0">
      <div class="col-lg-7 position-relative border-end shadow d-none d-lg-block" style="border-bottom: 5px solid; border-image: linear-gradient(to right, #da0025 50%, #00b500 50%) 1;">
        <div class="bg-holder" style="background-image: url(assets/images/bg-mrv.png)"></div>
      </div>

      <div class="col-lg-5 h-100 py-5 d-flex flex-column bg-white dark__bg-dark shadow-sm position-relative">
        <div class="row d-flex justify-content-center mt-3 g-0 px-4 px-sm-0" style="min-height: 80vh;">
          <div class="col col-sm-8 col-lg-8 col-xl-8">
            <a class="d-flex flex-center text-decoration-none mb-5"
              href="./">
              <div class="d-flex align-items-center fw-bolder fs-3 d-inline-block">
                <img src="assets/images/logo-full.png" alt="phoenix" width="350" />
              </div>
            </a>

            <div class="text-center mb-3">
              <h3 class="text-body-highlight">Créer un nouveau mot de passe</h3>
              <p class="text-body-tertiary mt-2">Votre nouveau mot de passe doit être différent de celui précédemment utilisé.</p>
            </div>

            <form class="form-vertical forgot-password-form hide-default" action="" method="post">
              <div class="mb-2">
                <label class="form-label" for="code">Code de vérification</label>
                <div class="form-icon-container" data-password="data-password">
                  <input oninput="this.value = this.value.replace(/[^0-9]/g, '');" class="form-control form-icon-input pe-6" autocomplete="new-password" name="code" id="code" type="text" placeholder="Code de vérification" data-password-input="data-password-input" required />
                  <span class="fas fa-lock text-body fs-9 form-icon"></span>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="form-icon-container" data-password="data-password">
                  <input class="form-control form-icon-input pe-6" autocomplete="new-password" name="password" id="password" type="password" placeholder="Mot de passe" data-password-input="data-password-input" required />
                  <span class="fas fa-key text-body fs-9 form-icon"></span>
                  <button title="Afficher/masquer" type="button" class="btn px-3 py-0 h-100 position-absolute top-0 end-0 fs-7 text-body-tertiary"
                    data-password-toggle="data-password-toggle">
                    <span class="uil uil-eye show"></span><span class="uil uil-eye-slash hide"></span>
                  </button>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label" for="confirm-password">Confirmer le mot de passe</label>
                <div class="form-icon-container" data-password="data-password">
                  <input class="form-control form-icon-input pe-6" onpaste="return false" type="password" placeholder="Confirmez le mot de passe" id="confirm-password" name="confirm-password" data-password-input="data-password-input" required>
                  <span class="fas fa-key text-body fs-9 form-icon"></span>
                  <button title="Afficher/masquer" type="button" class="btn px-3 py-0 h-100 position-absolute top-0 end-0 fs-7 text-body-tertiary"
                    data-password-toggle="data-password-toggle">
                    <span class="uil uil-eye show"></span><span class="uil uil-eye-slash hide"></span>
                  </button>
                </div>
              </div>

              <div class="mt-5">
                <button title="Réinitialiser le mot de passe" class="btn btn-subtle-primary w-100" type="submit">Réinitialiser le mot de passe</button>
              </div>

              <?php if (isset($error_message)): ?>
                <div class="alert alert-message alert-warning d-flex align-items-center text-center m-1 p-1 rounded-1" role="alert">
                  <p class="mb-0 flex-1"><?php echo $error_message; ?></p>
                  <button title="Fermer" class="btn-close btn-close-white fs-10 p-2 rounded-circle bg-light" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
            </form>

            <div class="text-center mt-5">
              <a class="text-body" href="./">Retour à la page de connexion</a>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5">
          <div class="text-center">© <?php echo date('Y'); ?> MRV - Burundi. Tous droits reservés. Powered by <a class="text-danger fw-bolder" href="https://cosit-mali.com/" target="_blank">COSIT</a></div>
        </div>
      </div>
    </div>

    <script>
      var navbarTopStyle = window.config.config.phoenixNavbarTopStyle;
      var navbarTop = document.querySelector(".navbar-top");
      if (navbarTopStyle === "darker") {
        navbarTop.setAttribute("data-navbar-appearance", "darker");
      }

      var navbarVerticalStyle =
        window.config.config.phoenixNavbarVerticalStyle;
      var navbarVertical = document.querySelector(".navbar-vertical");
      if (navbarVertical && navbarVerticalStyle === "darker") {
        navbarVertical.setAttribute("data-navbar-appearance", "darker");
      }
    </script>
  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->

  <!-- ===============================================-->
  <!--    JavaScripts-->
  <!-- ===============================================-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="vendors/popper/popper.min.js"></script>
  <script src="vendors/bootstrap/bootstrap.min.js"></script>
  <script src="vendors/anchorjs/anchor.min.js"></script>
  <script src="vendors/is/is.min.js"></script>
  <script src="vendors/fontawesome/all.min.js"></script>
  <script src="vendors/lodash/lodash.min.js"></script>
  <script src="vendors/list.js/list.min.js"></script>
  <script src="vendors/feather-icons/feather.min.js"></script>
  <script src="vendors/dayjs/dayjs.min.js"></script>
  <script src="assets/js/phoenix.js"></script>
  <script src="assets/scripts/sweet-alerts.js"></script>
</body>

</html>