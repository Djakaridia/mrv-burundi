<!DOCTYPE html>
<html lang="fr" dir="ltr" data-navigation-type="default" data-navbar-horizontal-shape="default">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Profil | MRV - Burundi</title>
  <?php
  include './components/navbar & footer/head.php';

  $error = '';
  $success = '';

  $id = $_SESSION['user-data']['user-id'] ?? null;
  $user = new User($db);
  $user_info = $user->profilUser($id);
  $userMailer = new UserMailer($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_USERNAME'], $_ENV['MAIL_PASSWORD'], $_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);

  $updatedAt = new DateTime($user_info['updated_at']);
  $now = new DateTime();
  $interval = $now->diff($updatedAt);
  $last_connexion = (int) $interval->format('%r%a');

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
            <p class="mb-0 flex-1"><strong>Succès :</strong>  <?php echo ($success); ?></p>
            <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="row g-3">
          <div class="col-md-4 col-lg-4 col-xl-4">
            <div class="sticky-leads-sidebar">
              <div class="lead-details-offcanvas bg-body scrollbar phoenix-offcanvas phoenix-offcanvas-fixed"
                id="productFilterColumn">
                <div class="d-flex justify-content-between align-items-center mb-2 d-md-none">
                  <h3 class="mb-0">Profil Détails</h3>
                  <button class="btn p-0" data-phoenix-dismiss="offcanvas"><span
                      class="uil uil-times fs-7"></span></button>
                </div>

                <!-- Carte Profil -->
                <div class="card mb-3 rounded-1 shadow-sm">
                  <div class="card-body">
                    <div class="row align-items-center g-3 text-center text-xxl-start">
                      <div class="col-12 col-xxl-auto">
                        <div class="avatar avatar-5xl position-relative">
                          <img class="rounded-circle border border-3 border-primary" src="./assets/img/team/avatar.webp"
                            alt="" />
                          <button
                            class="btn btn-sm btn-icon btn-primary rounded-circle position-absolute bottom-0 end-0">
                            <span class="uil uil-camera"></span>
                          </button>
                        </div>
                      </div>
                      <div class="col-12 col-sm-auto flex-1">
                        <h3 class="fw-bolder mb-1"><?php echo ($user_info['nom']); ?></h3>
                        <p class="mb-1 text-700 text-capitalize"><?php echo ($user_info['fonction']); ?></p>
                        <span class="badge bg-soft-primary text-primary">
                          <?php echo ($user_info['sigle']); ?>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Carte Informations -->
                <div class="card mb-3 rounded-1 shadow-sm">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h5 class="mb-0">Informations personnelles</h5>
                      <button class="btn btn-sm btn-outline-primary rounded-1 d-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#editProfileInfo">
                        <span class="uil uil-edit me-1"></span> Modifier
                      </button>
                    </div>

                    <!-- Formulaire d'édition (caché par défaut) -->
                    <div class="collapse mb-3" id="editProfileInfo">
                      <form class="needs-validation" novalidate>
                        <div class="mb-1">
                          <label class="form-label">Nom complet</label>
                          <input type="text" class="form-control form-control-sm rounded-1"
                            value="<?php echo ($user_info['nom']); ?>" required>
                        </div>

                        <div class="mb-1">
                          <label class="form-label">Fonction</label>
                          <input type="text" class="form-control form-control-sm rounded-1"
                            value="<?php echo ($user_info['fonction']); ?>">
                        </div>

                        <div class="mb-1">
                          <label class="form-label">Téléphone</label>
                          <input type="tel" class="form-control form-control-sm rounded-1"
                            value="<?php echo ($user_info['phone']); ?>">
                        </div>

                        <div class="d-flex justify-content-between gap-2 mt-3">
                          <button type="button" class="btn btn-sm btn-phoenix-secondary rounded-1" data-bs-toggle="collapse"
                            data-bs-target="#editProfileInfo">
                            Annuler
                          </button>
                          <button type="submit" class="btn btn-sm btn-phoenix-primary rounded-1">
                            Enregistrer
                          </button>
                        </div>
                      </form>
                      <hr class="my-2">
                    </div>

                    <!-- Affichage des informations -->
                    <div class="mb-2 pt-2 border-top border-light">
                      <div class="d-flex align-items-center mb-2">
                        <span class="me-2 uil uil-user fs-6 text-primary"></span>
                        <div>
                          <h6 class="text-body-highlight mb-0">Username</h6>
                          <p class="mb-0 text-body-secondary small text-capitalize"><?php echo ($user_info['username']); ?>
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="mb-2 pt-2 border-top border-light">
                      <div class="d-flex align-items-center mb-2">
                        <span class="me-2 uil uil-envelope-alt fs-6 text-primary"></span>
                        <div>
                          <h6 class="text-body-highlight mb-0">Email</h6>
                          <a href="mailto:<?php echo ($user_info['email']); ?>"
                            class="text-decoration-none small">
                            <?php echo ($user_info['email']); ?>
                          </a>
                        </div>
                      </div>
                    </div>

                    <div class="mb-2 pt-2 border-top border-light">
                      <div class="d-flex align-items-center mb-2">
                        <span class="me-2 uil uil-phone fs-6 text-primary"></span>
                        <div>
                          <h6 class="text-body-highlight mb-0">Téléphone</h6>
                          <a href="tel:<?php echo ($user_info['phone']); ?>"
                            class="text-decoration-none small">
                            <?php echo ($user_info['phone']); ?>
                          </a>
                        </div>
                      </div>
                    </div>

                    <div class="mb-2 pt-2 border-top border-light">
                      <div class="d-flex align-items-center mb-2">
                        <span class="me-2 uil uil-briefcase fs-6 text-primary"></span>
                        <div>
                          <h6 class="text-body-highlight mb-0">Fonction</h6>
                          <p class="mb-0 text-body-secondary small text-capitalize"><?php echo ($user_info['fonction']); ?>
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="mb-2 pt-2 border-top border-light">
                      <div class="d-flex align-items-center mb-2">
                        <span class="me-2 uil uil-check-circle fs-6 text-primary"></span>
                        <div>
                          <h6 class="text-body-highlight mb-0">Statut</h6>
                          <span class="badge badge-phoenix fs-10 badge-phoenix-<?= $user_info['state'] == 'actif' ? 'success' : 'danger' ?>"><?= $user_info['state'] ?></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Carte Modification du mot de passe -->
                <div class="card mb-3 rounded-1 shadow-sm">
                  <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">Sécurité</h5>
                      <button class="btn btn-sm btn-subtle-primary rounded-1" type="button" data-bs-toggle="collapse"
                        data-bs-target="#changePassword">
                        <span class="uil uil-lock me-1"></span> Modifier
                      </button>
                    </div>

                    <div class="collapse mb-3" id="changePassword">
                      <form method="POST" class="needs-validation" id="changePasswordForm" novalidate>
                        <div class="mb-2">
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

                        <div class="mb-2">
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

                        <div class="mb-2">
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

                        <div class="d-flex justify-content-between gap-2 mt-3">
                          <button type="button" class="btn btn-sm btn-phoenix-secondary rounded-1" data-bs-toggle="collapse" data-bs-target="#changePassword">
                            Annuler
                          </button>
                          <button type="submit" name="changePasswordForm" class="btn btn-sm btn-primary rounded-1">
                            Mettre à jour
                          </button>
                        </div>
                      </form>
                    </div>
                    <hr class="my-2">

                    <div class="d-flex align-items-center">
                      <span class="me-2 uil uil-shield-check fs-6 text-primary"></span>
                      <div>
                        <h6 class="text-body-highlight mb-0">Dernière modification</h6>
                        <p class="mb-0 text-body-secondary small">
                          Il y a <?php echo $last_connexion; ?> jour(s)</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-8 col-lg-8 col-xl-8">
            <div class="lead-details-container">
              <nav class="navbar sticky-top bg-body nav-underline-scrollspy mb-3 pt-1" id="navbar-deals-detail">
                <ul class="nav nav-underline fs-9">
                  <li class="nav-item"><a class="nav-link me-2" href="#scrollspyDeals">Projets</a></li>
                  <li class="nav-item"><a class="nav-link me-2" href="#scrollspyTask">Activités</a></li>
                  <li class="nav-item"><a class="nav-link me-2" href="#scrollspyGroup">Groupes de travail</a></li>
                  <li class="nav-item"><a class="nav-link me-2" href="#scrollspyDocs">Documents </a></li>
                </ul>
              </nav>

              <div class="scrollspy-example rounded-2" data-bs-spy="scroll" data-bs-offset="0"
                data-bs-target="#navbar-deals-detail" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true"
                tabindex="0">

                <div class="mb-3">
                  <h4 class="mb-0 w-100 px-2" id="scrollspyDeals">Projets associés</h4>
                  <?php include 'components/tabs/tab_profil_project.php'; ?>
                </div>

                <div class="mb-3">
                  <h4 class="mb-0 w-100 px-2" id="scrollspyTask">Activités assignées</h4>
                  <?php include 'components/tabs/tab_profil_task.php'; ?>
                </div>

                <div class="mb-3">
                  <h4 class="mb-0 w-100 px-2" id="scrollspyGroup">Groupes de travail</h4>
                  <?php include 'components/tabs/tab_profil_group.php'; ?>
                </div>

                <div class="mb-3">
                  <h4 class="mb-0 w-100 px-2" id="scrollspyDocs">Documents ajoutés</h4>
                  <?php include 'components/tabs/tab_profil_docs.php'; ?>
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