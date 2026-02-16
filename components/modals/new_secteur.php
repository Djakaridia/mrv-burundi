<!-- modal -->
<div class="modal fade" id="addSecteurModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
  aria-labelledby="addSecteurModal" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Changé de modal-md à modal-lg -->
    <div class="modal-content bg-body-highlight p-4">
      <div class="modal-header justify-content-between border-0 p-0 mb-3">
        <h3 class="mb-0" id="secteur_modtitle">Ajouter un secteur</h3>
        <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Fermer">
          <span class="fas fa-times text-danger"></span>
        </button>
      </div>
      <div class="modal-body px-0">
        <div id="secteurLoadingScreen" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Chargement...</span>
          </div>
          <h4 class="mt-3 fw-bold text-primary" id="secteurLoadingText">Chargement en cours</h4>
        </div>

        <!-- Content Container (initially hidden) -->
        <div id="secteurContentContainer" style="display: none;">
          <form action="" name="FormSecteur" id="FormSecteur" method="POST" enctype="multipart/form-data">
            <div class="row g-4">
              <div class="row g-3 m-0 px-1">
                <div class="col mb-1">
                  <label class="form-label">Code*</label>
                  <input oninput="checkColumns('code', 'secteur_code', 'secteur_codeFeedback', 'secteurs')" class="form-control" type="text" name="code" id="secteur_code" placeholder="Entrer le code"
                    required />
                  <div id="secteur_codeFeedback" class="invalid-feedback"></div>
                </div>

                <div id="sec_parent" class="col d-none">
                  <label class="form-label">Secteur*</label>
                  <select class="form-select" name="parent" id="parent">
                    <option value="0" selected disabled>Selectionnez le secteur</option>
                    <?php if (!empty($secteurs)) : ?>
                      <?php foreach ($secteurs as $secteur): ?>
                        <option value="<?php echo $secteur['id'] ?? "" ?>"><?php echo $secteur['name'] ?? "" ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Intitulé*</label>
                  <input class="form-control" type="text" name="name" id="secteur_name" placeholder="Entrer le nom"
                    required />
                </div>
              </div>

              <div id="sec_organism" class="col-12 d-none mt-1">
                <div class="mb-1">
                  <label class="form-label">Structure responsable</label>
                  <select class="form-select" name="structure_id" id="secteur_organism" required>
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

              <div class="col-lg-12 mt-1">
                <div class="mb-1">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" id="secteur_description"
                    placeholder="Entrer une description"></textarea>
                </div>
              </div>

              <div id="sub_data_container" class="my-2">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-1">
                  <h5 class="mb-0 text-primary">Informations sur les données</h5>
                  <button type="button" id="add_sub_data" class="btn btn-sm btn-subtle-primary">
                    <span class="fas fa-plus"></span>
                  </button>
                </div>

                <div id="sub_data_template" class="row g-3 m-n2 p-0 sub-data-item" style="display: none;">
                  <div class="col-lg-5">
                    <label class="form-label">Nature des données</label>
                    <textarea class="form-control sub-data-nature" placeholder="Entrer la nature des données"></textarea>
                  </div>

                  <div class="col-lg-5">
                    <label class="form-label">Source des données</label>
                    <textarea class="form-control sub-data-source" placeholder="Entrer la source des données"></textarea>
                  </div>

                  <div class="col-lg-1 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-subtle-danger remove-sub-data w-100">
                      <span class="fas fa-trash"></span>
                    </button>
                  </div>
                </div>

                <div id="sub_data_items"></div>
                <input type="hidden" name="nature" id="combined_nature">
                <input type="hidden" name="source" id="combined_source">
              </div>
            </div>

            <div class="modal-footer d-flex justify-content-between border-0 pt-4 px-0 pb-0">
              <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal"
                aria-label="Fermer">Annuler</button>
              <button type="submit" id="secteur_modbtn" class="btn btn-primary btn-sm px-3 my-0">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let formSecteurID = null;

  $(document).ready(function() {
    initSelect2("#addSecteurModal", "secteur_organism");

    function combineSubData() {
      const natures = [];
      const sources = [];

      $('.sub-data-item').each(function() {
        const nature = $(this).find('.sub-data-nature').val().trim();
        const source = $(this).find('.sub-data-source').val().trim();

        if (nature || source) {
          natures.push(nature);
          sources.push(source);
        }
      });

      const natureStr = natures.join(' | ');
      const sourceStr = sources.join(' | ');
      $('#combined_nature').val(natureStr || 'N/A');
      $('#combined_source').val(sourceStr || 'N/A');

      return {
        natures,
        sources
      };
    }

    function addSubDataItem(nature = '', source = '') {
      const template = $('#sub_data_template').clone();
      template.removeAttr('id').removeAttr('style');
      template.find('.sub-data-nature').val(nature);
      template.find('.sub-data-source').val(source);

      template.find('.remove-sub-data').on('click', function() {
        $(this).closest('.sub-data-item').remove();
        combineSubData();
      });

      template.find('.sub-data-nature, .sub-data-source').on('input', function() {
        combineSubData();
      });

      $('#sub_data_items').append(template);
      template.show();
      combineSubData();
    }

    $('#add_sub_data').on('click', function() {
      addSubDataItem();
    });

    $('#addSecteurModal').on('shown.bs.modal', async function(event) {
      const dataId = $(event.relatedTarget).data('id');
      const parent = $(event.relatedTarget).data('parent');
      const form = document.getElementById('FormSecteur');

      $('#sub_data_items').empty();
      $('#secteurLoadingScreen').show();
      $('#secteurContentContainer').hide();

      if (parent) {
        $('#sec_parent').removeClass('d-none');
        $('#sub_data_container').removeClass('d-none');
        $('#sec_organism').addClass('d-none');
        $('#secteur_organism').removeAttr('required');
      } else {
        $('#sec_parent').addClass('d-none');
        $('#sub_data_container').addClass('d-none');
        $('#sec_organism').removeClass('d-none');
      }

      if (dataId) {
        formSecteurID = dataId;
        $('#secteur_modtitle').text('Modifier le secteur');
        $('#secteur_modbtn').text('Modifier');
        $('#secteurLoadingText').text("Chargement des données secteur...");

        try {
          const response = await fetch(`./apis/secteurs.routes.php?id=${dataId}`, {
            headers: {
              'Authorization': `Bearer ${token}`
            },
            method: 'GET',
          });

          const result = await response.json();
          if (result.data) {
            form.code.value = result.data.code;
            form.name.value = result.data.name;
            form.description.value = result.data.description;
            form.parent.value = result.data.parent;

            $('#secteur_organism').val(result.data.structure_id);
            $('#secteur_organism').trigger('change');

            if (result.data.nature && result.data.nature !== 'N/A') {
              const natures = result.data.nature.split(' | ');
              const sources = result.data.source && result.data.source !== 'N/A' ? result.data.source.split(' | ') : [];
              const maxLength = Math.max(natures.length, sources.length);

              for (let i = 0; i < maxLength; i++) {
                addSubDataItem(
                  natures[i] || '',
                  sources[i] || ''
                );
              }
            }
          }
        } catch (error) {
          errorAction('Impossible de charger les données.');
        } finally {
          $('#secteurLoadingScreen').hide();
          $('#secteurContentContainer').show();
        }
      } else {
        formSecteurID = null;
        document.getElementById('secteur_modtitle').innerText = 'Ajouter un secteur';
        document.getElementById('secteur_modbtn').innerText = 'Ajouter';
        $('#secteurLoadingText').text("Préparation du formulaire...");

        setTimeout(() => {
          $('#secteurLoadingScreen').hide();
          $('#secteurContentContainer').show();
        }, 200);
      }
    });

    $('#addSecteurModal').on('hide.bs.modal', function() {
      setTimeout(() => {
        $('#secteurLoadingScreen').show();
        $('#secteurContentContainer').hide();
      }, 200);
      $('#FormSecteur')[0].reset();
      $('#sub_data_items').empty();
      $('#combined_nature').val('');
      $('#combined_source').val('');
    });

    $('#FormSecteur').on('submit', async function(event) {
      event.preventDefault();
      const form = $(this);
      combineSubData();
      const formData = new FormData(this);
      const url = formSecteurID ? `./apis/secteurs.routes.php?id=${formSecteurID}` : './apis/secteurs.routes.php';
      const submitBtn = $('#secteur_modbtn');
      submitBtn.prop('disabled', true);
      submitBtn.text('Envoi en cours...');

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
          successAction(result.message);
          $('#addSecteurModal').modal('hide');
        } else {
          errorAction(result.message);
        }
      } catch (error) {
        console.log(error);
        
        errorAction('Erreur lors de la soumission du formulaire.');
      } finally {
        submitBtn.prop('disabled', false);
        submitBtn.text(formSecteurID ? 'Modifier' : 'Ajouter');
      }
    });
    combineSubData();
  });
</script>