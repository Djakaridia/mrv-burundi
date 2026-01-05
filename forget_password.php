<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- ===============================================-->
  <title>Mot de passe oublié - MRV Burundi</title>

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
  include_once './config/functions.php';
  include_once './models/User.php';
  include_once __DIR__ . '/services/user.mailer.php';

  $database = new Database();
  $db = $database->getConnection();
  $user = new User($db);
  
  loadVarEnv();
  $sendmailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["email"])) {
      $mail = $_POST["email"];
      $user->email = $mail;
      $data_user = $user->readByEmail();

      if ($data_user['id'] == 0) {
        header(sprintf("Location: %s", "./forget_password.php?mail_exits=no"));
        exit;
      }

      // Generer un code de verification
      $code = rand(100000, 999999);
      $user->createCodeVerify($code);
      $sendmailer->sendPasswordReset($data_user['email'], $data_user['username'], $code);

      // Stocker les informations de l'utilisateur dans la session
      $_SESSION["tk-username"] = $data_user['username'];
      $_SESSION["tk-code"] = $code;
      $_SESSION["tk-email"] = $data_user['email'];

      // Rediriger vers la page de changement de mot de passe
      header(sprintf("Location: %s", "./change_password.php"));
      exit;
    } else {
      header(sprintf("Location: %s", "./forget_password.php?mail_exits=no"));
      exit;
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
              <h3 class="text-body-highlight">Mot de passe oublié?</h3>
              <p class="text-body-tertiary mt-2">Suivez les instructions ci-dessous pour réinitialiser votre mot de passe</p>
            </div>

            <form form class="form-vertical forgot-password-form hide-default" action="" method="post">
              <div class="mb-4">
                <label class="form-label">Email</label>
                <div class="form-icon-container">
                  <input class="form-control form-icon-input" name="email" id="email" type="email" placeholder="Entrez l'email" required />
                  <span class="fas fa-envelope text-body fs-9 form-icon"></span>
                </div>
              </div>

              <div class="text-center mt-3">
                <button title="Envoyer le code de réinitialisation" class="btn btn-subtle-primary w-100" type="submit" onclick="this.disabled = true; this.form.submit();">Envoyer le code de réinitialisation</button>
              </div>
            </form>

            <?php if (isset($_GET["mail_exits"]) && $_GET["mail_exits"] == "no") { ?>
              <div class="alert alert-message alert-warning d-flex align-items-center text-center m-1 p-1 rounded-1" role="alert">
                <p class="mb-0 flex-1">L'adresse mail est introuvable. Veuillez vérifier et réessayer.</p>
                <button title="Fermer" class="btn-close btn-close-white fs-10 p-2 rounded-circle bg-light" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php }; ?>

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