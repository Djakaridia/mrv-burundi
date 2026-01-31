<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- ===============================================-->
  <title>Accueil - MRV Burundi</title>
  <?php require_once 'config/connexion.php';
  autoAuth(); ?>

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

      <div class="col-lg-5 h-100 py-3 d-flex flex-column bg-white dark__bg-dark shadow-sm">
        <div class="row d-flex justify-content-center mt-3 g-0 px-4 px-sm-0" style="min-height: 80vh;">
          <div class="col col-sm-8 col-lg-8 col-xl-8">
            <a class="d-flex flex-center text-decoration-none mb-3" href="./">
              <div class="d-flex align-items-center fw-bolder fs-3 d-inline-block">
                <img src="assets/images/logo-full.png" alt="phoenix" width="350" />
              </div>
            </a>

            <div class="text-center mb-3">
              <h3 class="text-body-highlight">Se connecter</h3>
              <p class="text-body-tertiary mt-2">Accédez à votre compte</p>
            </div>

            <form action="" method="POST" id="login-form">
              <div class="mb-1 text-start">
                <label class="form-label" for="username">Nom d'utilisateur</label>
                <div class="form-icon-container">
                  <input class="form-control form-icon-input" name="username" id="username" type="text" placeholder="Nom d'utilisateur" /><span class="fas fa-user text-body fs-9 form-icon"></span>
                </div>
              </div>

              <div class="mb-1 text-start">
                <label class="form-label" for="password">Mot de passe</label>
                <div class="form-icon-container" data-password="data-password">
                  <input class="form-control form-icon-input pe-6" autocomplete="new-password" name="password" id="password" type="password" placeholder="Mot de passe" data-password-input="data-password-input" />
                  <span class="fas fa-key text-body fs-9 form-icon"></span>
                  <button title="Afficher/masquer" type="button" class="btn px-3 py-0 h-100 position-absolute top-0 end-0 fs-7 text-body-tertiary"
                    data-password-toggle="data-password-toggle">
                    <span class="uil uil-eye show"></span><span class="uil uil-eye-slash hide"></span>
                  </button>
                </div>
              </div>

              <div class="row flex-between-center mt-3 mb-7">
                <div class="col-auto">
                  <div class="form-check mb-0">
                    <input class="form-check-input" id="basic-checkbox" name="remember" type="checkbox" checked="checked" />
                    <label class="form-check-label mb-0" for="basic-checkbox">Se souvenir de moi</label>
                  </div>
                </div>
                <div class="col-auto"><a class="fs-9 fw-semibold" href="forget_password.php">Mot de passe oublié ?</a></div>
              </div>
              <button title="Se connecter" type="submit" class="btn btn-subtle-primary w-100 mb-3 d-flex align-items-center gap-3 justify-content-center">
                <span class="py-1">Se connecter</span>
                <div id="spinnerLogin" class="spinner-border spinner-border-sm d-none" role="status"></div>
              </button>
            </form>
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
  <script src="assets/myjs/login.js"></script>
  <script src="assets/scripts/sweet-alerts.js"></script>
</body>



</html>