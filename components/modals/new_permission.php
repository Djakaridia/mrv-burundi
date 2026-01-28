<?php
include './config/menus.php';
require_once 'config/database.php';
require_once 'models/Role.php';

// Create a database connection
$database = new Database();
$db = $database->getConnection();


function show_page($MENU_ITEMS, $MENU_TITLE, $page_interdite, $page_edit, $page_delete, $page_validate, $i)
{
  $resultat = '';
  $page_edit = explode('|', $page_edit);
  $page_delete = explode('|', $page_delete);
  $page_validate = explode('|', $page_validate);
  $page_interdite = explode('|', $page_interdite);
  $index = 0;

  if (is_array($MENU_ITEMS)) {
    $resultat .= '<tr><td class="bg-secondary-subtle"><a href="javascript:void(0);" class="mx-2 small my-0" > <i class="' . ((is_array($MENU_TITLE)) ? $MENU_TITLE[1] : '') . '"></i> <b>' . ((is_array($MENU_TITLE)) ? $MENU_TITLE[0] : '') . '</b> </a></td>';
    $resultat .= '
    <td class="bg-secondary-subtle" align="center"><input id="checkId_edit' . $i . '" type="checkbox" class="btn" /></td>
    <td class="bg-secondary-subtle" align="center"><input id="checkId_del' . $i . '" type="checkbox" class="btn" /></td>
    <td class="bg-secondary-subtle" align="center"><input id="checkId_valid' . $i . '" type="checkbox" class="btn" /></td>
    <td class="bg-secondary-subtle" align="center"><input id="checkId_inter' . $i . '" type="checkbox" class="btn" /></td>
    ';
    foreach ($MENU_ITEMS as $key => $value) {
      if (is_array($value)) {
        foreach ($value as $i => $k1) {
          $k2 = $k1;
          break;
        }
        unset($value);
        $value = $k2;
      }
      $resultat .= '<tr id="' . substr($key, 0, 5) . '"><td class="fs-10"><i class="mx-2"></i> ' . ((!is_array($value)) ? $value : "ND") . '</td>
      <td align="center" class="edit_' . $i . ' bg-info-subtle"><input name="page_edit[]" id="edit_' . $i . $index . '" type="checkbox" ' . ((is_array($page_edit) && in_array($key, $page_edit) ? "checked='checked'" : "")) . ' class="btn small" value="' . $key . '" /></td>
      <td align="center" class="dele_' . $i . ' bg-warning-subtle"><input name="page_delete[]" id="dele_' . $i . $index . '" type="checkbox" ' . ((is_array($page_delete) && in_array($key, $page_delete) ? "checked='checked'" : "")) . ' class="btn small" value="' . $key . '" /></td>
      <td align="center" class="valid_' . $i . ' bg-success-subtle"><input name="page_validate[]" id="valid_' . $i . $index . '" type="checkbox" ' . ((is_array($page_validate) && in_array($key, $page_validate) ? "checked='checked'" : "")) . ' class="btn small" value="' . $key . '" /></td>
      <td align="center" class="inter_' . $i . ' bg-danger-subtle"><input name="page_interdite[]" id="inter_' . $i . $index . '" type="checkbox" ' . ((is_array($page_interdite) && in_array($key, $page_interdite) ? "checked='checked'" : "")) . ' class="btn small" value="' . $key . '" /></td>
      </tr>';
      $index++;
    }
  }
  return $resultat;
}
?>


<script>
  let formPermisID = null;

  $(document).ready(function() {
    const form = document.getElementById('formPermission');

    const checkAllEdit = document.querySelectorAll('[id^="checkId_edit"]');
    const checkAllDel = document.querySelectorAll('[id^="checkId_del"]');
    const checkAllInter = document.querySelectorAll('[id^="checkId_inter"]');
    const checkAllValid = document.querySelectorAll('[id^="checkId_valid"]');

    checkAllEdit.forEach((checkbox, index) => {
      checkbox.addEventListener('click', function() {
        toggleCheckboxes(this, `edit_${index + 2}`);
      });
    });

    checkAllDel.forEach((checkbox, index) => {
      checkbox.addEventListener('click', function() {
        toggleCheckboxes(this, `dele_${index + 2}`);
      });
    });

    checkAllInter.forEach((checkbox, index) => {
      checkbox.addEventListener('click', function() {
        toggleCheckboxes(this, `inter_${index + 2}`);
      });
    });

    checkAllValid.forEach((checkbox, index) => {
      checkbox.addEventListener('click', function() {
        toggleCheckboxes(this, `valid_${index + 2}`);
      });
    });

    const adminPermission = document.getElementById('checkAdminPermi');
    const allCheckboxes = document.querySelectorAll('#formPermission input[type="checkbox"]');
    adminPermission.addEventListener('click', function() {
      allCheckboxes.forEach(checkbox => {
        if (checkbox !== adminPermission) {
          checkbox.checked = adminPermission.checked;
        }
      });
    });

    $('#newPermisModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      $('#permissionLoadingScreen').show();
      $('#permissionContentContainer').hide();

      if (dataId) {
        formPermisID = dataId;
        $('#permissionLoadingText').text("Chargement des données permissions...");

        try {
          const response = await fetch(`./apis/roles.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();

          form.name.value = result.data.name;
          form.niveau.value = result.data.niveau;
          form.description.value = result.data.description;

          const pageEdit = result.data.page_edit ? result.data.page_edit.split('|') : [];
          const pageDelete = result.data.page_delete ? result.data.page_delete.split('|') : [];
          const pageValidate = result.data.page_validate ? result.data.page_validate.split('|') : [];
          const pageInterdite = result.data.page_interdite ? result.data.page_interdite.split('|') : [];

          pageEdit.forEach(pageId => {
            const editCheckbox = document.querySelector(`input[name="page_edit[]"][value="${pageId}"]`);
            if (editCheckbox) editCheckbox.checked = true;
          });

          pageDelete.forEach(pageId => {
            const deleteCheckbox = document.querySelector(`input[name="page_delete[]"][value="${pageId}"]`);
            if (deleteCheckbox) deleteCheckbox.checked = true;
          });

          pageValidate.forEach(pageId => {
            const validateCheckbox = document.querySelector(`input[name="page_validate[]"][value="${pageId}"]`);
            if (validateCheckbox) validateCheckbox.checked = true;
          });

          pageInterdite.forEach(pageId => {
            const interditeCheckbox = document.querySelector(`input[name="page_interdite[]"][value="${pageId}"]`);
            if (interditeCheckbox) interditeCheckbox.checked = true;
          });
        } catch (error) {
          errorAction('Une erreur est survenue. Veuillez réessayer.');
        } finally {
          $('#permissionLoadingScreen').hide();
          $('#permissionContentContainer').show();
        }
      } else {
        errorAction('Permissions non trouvées.');
      }
    });

    $('#newPermisModal').on('hidden.bs.modal', function() {
      formPermisID = null;
      form.reset();
      const checkboxes = document.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(checkbox => {
        checkbox.checked = false;
      });

      $('#permissionLoadingScreen').show();
      $('#permissionContentContainer').hide();
    });

    $('#formPermission').on('submit', async function(event) {
      event.preventDefault();
      const form = this;
      const formData = new FormData(this);
      const submitBtn = $('#permission_modbtn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

      try {
        const response = await fetch(`./apis/roles.routes.php?id=${formPermisID}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: 'POST',
          body: formData,
        });

        const result = await response.json();

        if (result.status === 'success') {
          $('#newPermisModal').modal('hide');
          successAction(result.message);
        } else {
          errorAction(result.message);
        }
      } catch (error) {
        errorAction('Une erreur est survenue. Veuillez réessayer.');
      } finally {
        submitBtn.prop('disabled', false);
        submitBtn.text('Enregistrer');
      }
    });
  });

  function toggleCheckboxes(checkbox, className) {
    const checkboxes = document.querySelectorAll(`.${className} input[type="checkbox"]`);
    checkboxes.forEach(cb => {
      cb.checked = checkbox.checked;
    });
  }
</script>

<div class="modal fade" id="newPermisModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newPermisModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="newPermisModalLabel">Nouvelle permission du role</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>

      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="permissionLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="permissionLoadingText">Chargement en cours</h4>
        </div>

        <div id="permissionContentContainer" style="display: none;">
          <form action="" class="row-border" enctype="multipart/form-data" name="formPermission" id="formPermission" novalidate="novalidate">
            <input type="hidden" name="name" value="" />
            <input type="hidden" name="niveau" value="" />
            <input type="hidden" name="description" value="" />

            <div class="form-check form-switch pb-1">
              <input class="form-check-input" id="checkAdminPermi" type="checkbox" />
              <label class="form-check-label" for="checkAdminPermi">Permission Administrateur</label>
            </div>

            <div class="overflow-auto bg-body-emphasis border" style="min-height: 300px; max-height: 400px;">
              <table class="table table-sm fs-12 bordered table-striped-columns small" align="center">
                <thead class="bg-primary-subtle">
                  <tr class="small fw-bold">
                    <td style="width:60%" class="fs-12 px-2">Pages</td>
                    <td style="width:10%" align="center" class="fs-12 px-2">Edition</td>
                    <td style="width:10%" align="center" class="fs-12 px-2">Suppression</td>
                    <td style="width:10%" align="center" class="fs-12 px-2">Validation</td>
                    <td style="width:10%" align="center" class="fs-12 px-2">Interdiction</td>
                  </tr>
                </thead>

                <tbody>
                  <?php if (is_array($MENU_ITEMS)) foreach ($MENU_ITEMS as $key => $value) echo show_page($value, $MENU_TITLE[$key], "|", "|", "|", "|", $key); ?>
                </tbody>
              </table>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-3 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
              <button type="submit" class="btn btn-primary btn-sm  my-0" id="permission_modbtn"> Enregistrer</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>