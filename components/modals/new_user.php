<?php
$role = new Role($db);
$roles = $role->read();
?>

<!-- Create User Modal -->
<div class="modal fade" id="addUserModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addUserModal" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 id="FormUser-title" class="mb-0">Ajouter un utilisateur</h3>
        <button type="button" class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>
      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="userLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="userLoadingText">Chargement des données </h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="userContentContainer" style="display: none;">
          <form action="" method="POST" name="FormUser" id="FormUser">
            <div class="row g-4">
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Nom*</label>
                  <input class="form-control" type="text" name="nom" placeholder="Entrer le nom" required />
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Prénom*</label>
                  <input class="form-control" type="text" name="prenom" placeholder="Entrer le prénom" required />
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Nom d'utilisateur*</label>
                  <input class="form-control" type="text" name="username" id="username_user" placeholder="Entrer le nom d'utilisateur" required
                    oninput="checkColumns('username', 'username_user', 'usernameFeedback', 'users')" />
                  <div id="usernameFeedback" class="invalid-feedback"></div>
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Mot de passe*</label>
                  <div class="form-icon-container" data-password="data-password">
                    <input class="form-control form-icon-input pe-6" autocomplete="new-password" data-password-input="data-password-input" id="password" type="password" name="password" placeholder="Mot de passe" required />
                    <span class="fas fa-key text-body fs-9 form-icon"></span>
                    <button type="button" class="btn px-3 py-0 h-100 position-absolute top-0 end-0 fs-7 text-body-tertiary" data-password-toggle="data-password-toggle">
                      <span class="uil uil-eye show"></span><span class="uil uil-eye-slash hide"></span>
                    </button>
                  </div>
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Adresse e-mail*</label>
                  <input class="form-control" autocomplete="email" type="email" name="email" id="email_user" placeholder="Entrer l'adresse e-mail" required />
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Téléphone (Whatsapp)*</label>
                  <input oninput="checkPhoneNumber(this.value)" class="form-control" type="text" name="phone" placeholder="Entrer le numéro whatsapp" required />
                  <span id="phoneError" class="text-danger"></span>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Structure*</label>
                  <select class="form-select" name="structure_id" id="structure_id" required>
                    <option value="">Sélectionner une structure</option>
                    <?php if ($structures ?? []) : ?>
                      <?php foreach ($structures as $structure) : ?>
                        <option value="<?php echo $structure['id'] ?>">
                          <?php echo $structure['description'] ?  $structure['description'] . "(" . $structure['sigle'] . ")" : $structure['sigle'] ?>
                        </option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Rôle*</label>
                  <select class="form-select" name="role_id" id="role_id" required>
                    <option value="">Sélectionner le rôle</option>
                    <?php if ($roles ?? []) : ?>
                      <?php foreach ($roles as $role) : ?>
                        <option value="<?php echo $role['id'] ?>"><?php echo $role['name'] ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-6 mt-1">
                <div class="mb-1">
                  <label class="form-label">Fonction*</label>
                  <select class="form-select" name="fonction" id="fonction" required>
                    <option value="">Sélectionner une fonction</option>
                    <option value="simple">Simple</option>
                    <option value="point_focal">Point focal</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
              <button type="submit" id="FormUser-btn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formUserID = null;
  $(document).ready(function() {
    initSelect2("#addUserModal", "structure_id");
    $('#addUserModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const form = document.getElementById('FormUser');

      $('#userLoadingScreen').show();
      $('#userContentContainer').hide();
      form.reset();

      if (dataId) {
        formUserID = dataId;
        $('#FormUser-title').text('Modifier un utilisateur');
        $('#FormUser-btn').text('Modifier');
        $('#userLoadingText').text("Chargement des données utilisateur...");
        $('#username_user').attr('readonly', true);
        $('#username_user').attr('required', false);
        $('#username_user').addClass('bg-warning-subtle');
        $('#email_user').attr('readonly', true);
        $('#email_user').attr('required', false);
        $('#email_user').addClass('bg-warning-subtle');

        try {
          const response = await fetch(`./apis/users.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          if (result.status === 'success') {
            form.nom.value = result.data.nom;
            form.prenom.value = result.data.prenom;
            form.username.value = result.data.username;
            form.email.value = result.data.email;
            form.phone.value = result.data.phone;
            form.role_id.value = result.data.role_id;
            form.structure_id.value = result.data.structure_id;
            form.fonction.value = result.data.fonction;

            $('#structure_id').val(result.data.structure_id);
            $('#structure_id').trigger('change');

            $('#username_user').removeAttr('required');
            $('#username_user').closest('.mb-1').hide();
            $('#password').removeAttr('required');
            $('#password').closest('.mb-1').hide();
          } else {
            throw new Error('Données utilisateur invalides');
          }
        } catch (error) {
          console.error('Error loading user data:', error);
          errorAction('Impossible de charger les données utilisateur.');
        } finally {
          $('#userLoadingScreen').hide();
          $('#userContentContainer').show();
        }
      } else {
        formUserID = null;
        $('#FormUser-title').text('Ajouter un utilisateur');
        $('#FormUser-btn').text('Ajouter');
        $('#userLoadingText').text("Préparation du formulaire...");

        $('#username_user').attr('readonly', false);
        $('#username_user').attr('required', true);
        $('#username_user').removeClass('bg-warning-subtle');

        $('#email_user').attr('readonly', false);
        $('#email_user').attr('required', true);
        $('#email_user').removeClass('bg-warning-subtle');

        $('#username_user').attr('required', true);
        $('#username_user').closest('.mb-1').show();
        $('#password').attr('required', true);
        $('#password').closest('.mb-1').show();

        setTimeout(() => {
          $('#userLoadingScreen').hide();
          $('#userContentContainer').show();
        }, 200);
      }
    });

    $('#addUserModal').on('hide.bs.modal', function() {
      $('#FormUser')[0].reset();
      $('#structure_id').val('');
      $('#structure_id').trigger('change');
      setTimeout(() => {
        $('#userLoadingScreen').show();
        $('#userContentContainer').hide();
      }, 200);
    });

    $('#FormUser').on('submit', async function(e) {
      e.preventDefault();
      const form = this;
      const formData = new FormData(form);
      const url = formUserID ? `./apis/users.routes.php?id=${formUserID}` : './apis/users.routes.php';

      const submitBtn = $('#FormUser-btn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`
          },
          body: formData,
        });

        const result = await response.json();
        if (result.status === 'success') {
          successAction(formUserID ? "Utilisateur modifié avec succès." : "Utilisateur ajouté avec succès.");
          $('#addUserModal').modal('hide');
        } else {
          errorAction(result.message || "Une erreur est survenue. Veuillez réessayer.");
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        errorAction('Une erreur est survenue. Veuillez réessayer.');
      } finally {
        submitBtn.prop('disabled', false);
        submitBtn.text('Ajouter');
      }
    });
  });

  function checkPhoneNumber(phoneNumber) {
    const phoneRegex = /^\+?[0-9]{8,15}$/;
    const phoneError = document.getElementById('phoneError');
    if (!phoneRegex.test(phoneNumber)) {
      phoneError.textContent = 'Le numéro de téléphone est invalide.';
      return false;
    } else {
      phoneError.textContent = '';
      return true;
    }
  }
</script>