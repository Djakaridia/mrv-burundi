<div class="modal fade" id="newNiveauIndicModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newNiveauIndicModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0">
        <h3 class="mb-0" id="niv_indic_modtitle">Indicateurs de résultats</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close"><span class="fas fa-times text-danger"></span></button>
      </div>

      <div class="modal-body px-0">
        <!-- Loading Screen -->
        <div id="niveauIndicLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="niveauIndicLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="niveauIndicContentContainer" style="display: none;">
          <div id="niveauIndicTableContent" class="card rounded-1 bg-white dark__bg-dark">
            <div class="card-header d-flex justify-content-between align-items-center p-2">
              <h5 class="mb-0">Liste des indicateurs de niveau de résultat</h5>
              <button class="btn btn-sm btn-primary" onclick="showNivIndicForm()">
                <span class="fas fa-plus me-2"></span>Nouveau indicateur
              </button>
            </div>

            <div class="card-body p-2" style="min-height: 300px; max-height: 400px; overflow-y: auto;">
              <table class="table table-sm table-hover table-striped fs-9 table-bordered border-emphasis" align="center">
                <thead class="bg-light">
                  <tr>
                    <th scope="col" class="text-start px-2">Intitulé</th>
                    <th scope="col" class="text-center">Unité</th>
                    <th scope="col" class="text-center">Type</th>
                    <?php for ($i = 2020; $i < 2028; $i++) { ?>
                      <th scope="col" class="text-center"><?= $i ?></th>
                    <?php } ?>
                    <th scope="col" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody id="viewNiveauIndicBody"></tbody>
              </table>
            </div>
          </div>

          <div id="niveauIndicFormContent" class="d-none">
            <form action="" class="row" enctype="multipart/form-data" name="FormNiveauIndic" id="FormNiveauIndic">
              <!-- Intitulé de l'indicateur -->
              <div class="col-md-12 mb-3">
                <div class="form-group">
                  <label class="form-label" for="niveau_indic_intitule">Intitulé de l'indicateur*</label>
                  <input class="form-control" name="intitule" id="niveau_indic_intitule" type="text" placeholder="Intitulé de l'indicateur" required>
                </div>
              </div>

              <!-- Unité -->
              <div class="col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label" for="indicateurUnite">Unité*</label>
                  <select class="form-select" name="unite" id="niveauIndicateurUnite" required>
                    <option value="" selected disabled>Sélectionner une unité</option>
                    <?php if ($unites ?? []) : ?>
                      <?php foreach ($unites as $unite) : ?>
                        <option value="<?= $unite['id'] ?>"><?= $unite['name'] ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label" for="niveau_indic_resultat">Type d'indicateur*</label>
                  <select class="form-select" name="type" id="niveau_indic_resultat" required>
                    <option value="" selected disabled>Sélectionner un type</option>
                    <option value="gestion">Gestion</option>
                    <option value="qualite">Qualité</option>
                    <option value="performance">Performance</option>
                  </select>
                </div>
              </div>

              <div class="col-md-12 mb-3">
                <div class="row g-1">
                  <label class="form-label" for="niv_periode_1">Valeurs cibles annuelles (seront combinées en JSON)</label>
                  <div class="col-12 d-flex flex-wrap gap-2">
                    <?php for ($i = 2020; $i < 2028; $i++) { ?>
                      <div style="flex: 0 0 auto; width: 80px;">
                        <label class="form-label small" for="niv_cible_<?= $i ?>"><?= $i ?></label>
                        <input type="number" name="cible_<?= $i ?>" id="niv_cible_<?= $i ?>" class="form-control form-control-sm" placeholder="0">
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>

              <div class="modal-footer d-flex justify-content-between border-0 pt-3 pb-0">
                <input type="hidden" name="resultat" id="niveau_indic_niveau_id">
                <button type="button" class="btn btn-secondary btn-sm px-3 my-0" onclick="cancelNivIndicForm()">Annuler</button>
                <button name="submit" type="submit" id="niv_indic_modbtn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const unites = <?php echo json_encode($unites); ?>;
  let currentIndicId = null;
  let isEditing = false;

  $(document).ready(function() {
    $('#newNiveauIndicModal').on('shown.bs.modal', async function(event) {
      const niveauId = $(event.relatedTarget).data('id');

      if (niveauId) {
        $('#niveau_indic_niveau_id').val(niveauId);
        await loadNiveauIndicateurs();
      }
    });

    $('#newNiveauIndicModal').on('hidden.bs.modal', function() {
      $('#FormNiveauIndic')[0].reset();
      $('#viewNiveauIndicBody').html('');
      $('#niveauIndicLoadingScreen').show();
      $('#niveauIndicContentContainer').hide();
      currentIndicId = null;
      isEditing = false;
      cancelNivIndicForm();
    });

    $('#FormNiveauIndic').on('submit', async function(e) {
      e.preventDefault();

      const ciblesData = {};
      $('input[name^="cible_"]').each(function() {
        const year = this.id.replace('niv_cible_', '');
        const value = $(this).val();
        if (value !== '') {
          ciblesData[year] = parseFloat(value);
        }
      });

      const formData = new FormData(this);
      if (Object.keys(ciblesData).length > 0) {
        formData.set('cibles', JSON.stringify(ciblesData));
      }

      await saveNiveauIndicateur(currentIndicId, formData);
    });
  });

  async function loadNiveauIndicateurs() {
    const tbody = $('#viewNiveauIndicBody');
    const resultatId = $('#niveau_indic_niveau_id').val();
    tbody.html('');

    $('#niveauIndicLoadingScreen').show();
    $('#niveauIndicContentContainer').hide();
    $('#niveauIndicLoadingText').text("Chargement des indicateurs...");

    try {
      const response = await fetch(`./apis/niveaux_indicateur.routes.php?resultat=${resultatId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: 'GET',
      });

      const result = await response.json();
      if (result.status === 'success' && result.data.length > 0) {
        result.data.forEach(element => {
          let ciblesText = ``;
          let cibles = [];

          if (element.cibles) {
            cibles = JSON.parse(element.cibles);
            for (let i = 2020; i < 2028; i++) {
              ciblesText += `<td class="text-center">${cibles[i] || '-'}</td>`;
            }
          }

          tbody.append(`
            <tr class="align-middle">
              <td class="text-start px-2">${element.intitule}</td>
              <td class="text-center">${unites.find(u => u.id == element.unite)?.name || '-'}</td>
              <td class="text-start text-capitalize">${element.type}</td>
              ${ciblesText}
              <td class="text-center d-flex justify-content-center align-items-center gap-2">
                <?php if (checkPermis($db, 'update')) : ?>
                  <button type="button" onclick="editNiveauIndicateur('${element.id}')" 
                    class="btn btn-icon btn-phoenix-primary btn-sm fs-9">
                    <i class="fas fa-edit"></i>
                  </button>
                <?php endif; ?>
                <?php if (checkPermis($db, 'delete')) : ?>
                  <button type="button" onclick="deleteNiveauIndicateur('${element.id}')" 
                    class="btn btn-icon btn-phoenix-danger btn-sm fs-9">
                    <i class="fas fa-trash"></i>
                  </button>
                <?php endif; ?>
              </td>
            </tr>`);
        });
      } else {
        tbody.html('<tr><td colspan="20" class="text-center py-5">Aucun indicateur trouvé.</td></tr>');
      }
    } catch (error) {
      tbody.html('<tr><td colspan="20" class="text-center py-5">Erreur de chargement.</td></tr>');
    } finally {
      $('#niveauIndicLoadingScreen').hide();
      $('#niveauIndicContentContainer').show();
    }
  }

  async function editNiveauIndicateur(id) {
    $('#niveauIndicLoadingScreen').show();
    $('#niveauIndicContentContainer').hide();
    const form = $('#FormNiveauIndic')[0];
    currentIndicId = id;
    isEditing = true;

    if (id) {
      $('#niv_indic_modtitle').text('Modifier l\'indicateur');
      $('#niv_indic_modbtn').text('Modifier');
      $('#niveauIndicLoadingText').text("Chargement des données...");
      showNivIndicForm();

      try {
        const response = await fetch(`./apis/niveaux_indicateur.routes.php?id=${id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: 'GET',
        });

        const result = await response.json();
        if (result.status === 'success') {
          const data = result.data;
          form.intitule.value = data.intitule;
          form.unite.value = data.unite;
          form.type.value = data.type;
          form.resultat.value = data.resultat;

          if (data.cibles) {
            const cibles = JSON.parse(data.cibles);
            Object.entries(cibles).forEach(([year, value]) => {
              $(`#niv_cible_${year}`).val(value);
            });
          }
        }
      } catch (error) {
        errorAction('Impossible de charger les données.');
      } finally {
        $('#niveauIndicLoadingScreen').hide();
        $('#niveauIndicContentContainer').show();
      }
    }
  }

  async function saveNiveauIndicateur(id, formData) {
    const url = id ? `./apis/niveaux_indicateur.routes.php?id=${id}` : './apis/niveaux_indicateur.routes.php';

    try {
      const response = await fetch(url, {
        headers: {
          'Authorization': `Bearer ${token}`
        },
        method: "POST",
        body: formData
      });

      const result = await response.json();
      if (result.status === 'success') {
        successAction(isEditing ? 'Indicateur modifié avec succès.' : 'Indicateur ajouté avec succès.', 'none');
        cancelNivIndicForm();
        await loadNiveauIndicateurs();
      } else {
        errorAction(result.message || 'Erreur lors de l\'enregistrement.');
      }
    } catch (error) {
      errorAction('Erreur lors de l\'enregistrement: ' + error.message);
    }
  }

  async function deleteNiveauIndicateur(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet indicateur ?')) {
      try {
        const response = await fetch(`./apis/niveaux_indicateur.routes.php?id=${id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          },
          method: 'DELETE',
        });

        const result = await response.json();
        if (result.status === 'success') {
          successAction('Indicateur supprimé avec succès.', 'none');
          await loadNiveauIndicateurs();
        } else {
          errorAction(result.message || 'Erreur lors de la suppression.');
        }
      } catch (error) {
        errorAction('Erreur lors de la suppression: ' + error.message);
      }
    }
  }

  function cancelNivIndicForm() {
    $('#FormNiveauIndic')[0].reset();
    $('input[name^="cible_"]').val('');
    $('#niveauIndicFormContent').addClass('d-none');
    $('#niveauIndicTableContent').removeClass('d-none');
    $('#niv_indic_modtitle').text('Indicateurs de résultats');
    $('#niv_indic_modbtn').text('Ajouter');
    currentIndicId = null;
    isEditing = false;
  }

  function showNivIndicForm() {
    $('#niveauIndicTableContent').addClass('d-none');
    $('#niveauIndicFormContent').removeClass('d-none');
  }
</script>
</script>