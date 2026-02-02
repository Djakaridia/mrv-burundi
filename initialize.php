<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Initialisation du mot de passe | MRV - Burundi</title>
  <?php
  include './components/navbar & footer/head.php';

  $error = '';
  $success = '';
  $init_pwd = $_SESSION['init_pwd'] ?? null;

  if (!$init_pwd) {
    echo '<script> window.location.href = "accueil.php"; </script>';
    exit();
  }

  $id = $_SESSION['user-data']['user-id'] ?? null;
  $user = new User($db);
  $user_info = $user->profilUser($id);
  $userMailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);

  $updatedAt = new DateTime($user_info['updated_at']);
  $now = new DateTime();
  $interval = $now->diff($updatedAt);
  $last_connexion = abs($interval->format('%r%a'));

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePasswordForm'])) {
    $password = $_POST['password-current'];
    $new_password = $_POST['password-new'];
    $confirm_password = $_POST['password-confirm'];

    if ($user->authenticate($user_info['username'], $password)) {
      if ($new_password === $confirm_password) {
        if (strlen($new_password) < 5 || !preg_match('/[0-9]/', $new_password) || !preg_match('/[a-zA-Z]/', $new_password)) {
          $error = 'Le mot de passe doit contenir au moins 5 caractères avec des chiffres et des lettres.';
        } else {
          $user->resetPassword($user_info['username'], $new_password);
          $_SESSION['user-data']['user-password'] = $new_password;

          $userMailer->sendAccountUpdate($user_info['email'], $user_info['username']);
          $success = 'Votre mot de passe a été mis à jour avec succès.';
          unset($_SESSION['init_pwd']);
        }
      } else {
        $error = 'Les mots de passe ne correspondent pas.';
      }
    } else {
      $error = 'Le mot de passe actuel est incorrect.';
    }
  }
  ?>
</head>


<body>

  <!-- ===============================================-->
  <!--    Main Content-->
  <!-- ===============================================-->
  <main class="main" id="top">
    <?php include './components/navbar & footer/sidebar.php'; ?>
    <?php include './components/navbar & footer/navbar.php'; ?>

    <div class="content">
      <div class="mx-n4 mt-n4 pb-9">
        <?php if ($error): ?>
          <div class="alert alert-subtle-danger d-flex align-items-center py-2 px-3 rounded-1" role="alert">
            <span class="fas fa-times-circle text-danger fs-5 me-3"></span>
            <p class="mb-0 flex-1"><strong>Erreur :</strong> <?php echo ($error); ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-subtle-success d-flex align-items-center py-2 px-3 rounded-1" role="alert">
            <span class="fas fa-check-circle text-success fs-5 me-3"></span>
            <p class="mb-0 flex-1"><strong>Succès :</strong> <?php echo ($success); ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="row d-flex justify-content-center g-3">
          <div class="col-md-12 col-lg-6">
            <div class="card mb-3 rounded-1 shadow-sm">
              <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-top">
                  <h4 class="mb-0">Modifier le mot de passe</h4>
                  <div class="d-flex align-items-center">
                    <span class="me-2 uil uil-shield-check fs-6 text-primary"></span>
                    <div>
                      <h6 class="text-body-highlight mb-0">Dernière modification</h6>
                      <p class="mb-0 text-body-secondary small">Il y a <?php echo $last_connexion; ?> jour(s)</p>
                    </div>
                  </div>
                </div>

                <div class="my-3">
                  <form method="POST" class="needs-validation" id="changePasswordForm" novalidate>
                    <div class="mb-3">
                      <label class="form-label">Mot de passe actuel</label>
                      <div class="input-group">
                        <input type="password" name="password-current" class="form-control form-control-sm rounded-start-1"
                          placeholder="Entrez votre mot de passe actuel" required>
                        <button class="btn btn-sm rounded-end-1 btn-light border toggle-password" type="button">
                          <span class="uil uil-eye"></span>
                        </button>
                      </div>
                      <div class="invalid-feedback">
                        Veuillez saisir votre mot de passe actuel.
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Nouveau mot de passe</label>
                      <div class="input-group">
                        <input type="password" name="password-new" class="form-control form-control-sm rounded-start-1"
                          placeholder="Entrez votre nouveau mot de passe" required
                          pattern="^(?=.*[A-Za-z])(?=.*\d).{6,}$">
                        <button class="btn btn-sm rounded-end-1 btn-light border toggle-password" type="button">
                          <span class="uil uil-eye"></span>
                        </button>
                      </div>
                      <div class="form-text text-warning">Minimum 6 caractères avec chiffres et lettres</div>
                      <div class="invalid-feedback">
                        Le mot de passe doit contenir au moins 6 caractères avec des chiffres et des lettres.
                      </div>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Confirmer le nouveau mot de passe</label>
                      <div class="input-group">
                        <input type="password" name="password-confirm" class="form-control form-control-sm rounded-start-1"
                          placeholder="Confirmez votre nouveau mot de passe" required>
                        <button class="btn btn-sm rounded-end-1 btn-light border toggle-password" type="button">
                          <span class="uil uil-eye"></span>
                        </button>
                      </div>
                      <div class="invalid-feedback">
                        Veuillez confirmer votre nouveau mot de passe.
                      </div>
                    </div>

                    <div class="d-flex justify-content-between gap-2 mt-4">
                      <button type="button" class="btn btn-sm btn-phoenix-secondary rounded-1" onclick="window.location.href = 'accueil.php';">
                        Annuler
                      </button>
                      <button type="submit" name="changePasswordForm" class="btn btn-sm btn-primary rounded-1">
                        Enregistrer
                      </button>
                    </div>
                  </form>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include './components/navbar & footer/footer.php'; ?>
  </main>

  <?php include './components/navbar & footer/foot.php'; ?>
</body>
<script src="assets/myjs/profil.js"></script>

</html>