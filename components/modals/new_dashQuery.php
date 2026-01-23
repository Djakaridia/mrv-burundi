<div class="modal fade" id="addDashEtatModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addDashModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-body-highlight p-4">
            <div class="modal-header justify-content-between border-0 p-0 mb-3">
                <h3 class="mb-0" id="dash_modtitle">Nouvel état</h3>
                <button class="btn btn-sm btn-phoenix-secondary" data-bs-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times text-danger"></span>
                </button>
            </div>
            <div class="modal-body px-0">
                <div id="dashPerLoadingScreen" class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <h4 class="mt-3 fw-bold text-primary" id="dashPerLoadingText">Chargement en cours</h4>
                </div>

                <div id="dashPerContentContainer" style="display: none;">
                    <form class="row g-3" action="" method="post" id="FormDashEtat">
                        <!-- Intitule du dash -->
                        <div class="col-md-9">
                            <div class="form-floating">
                                <input class="form-control" name="intitule" id="dashIntitule" type="text" placeholder="Nom de la requête" required>
                                <label for="dashIntitule">Nom de la requête*</label>
                            </div>
                        </div>
                        <!-- Code du dash -->
                        <div class="col-md-3">
                            <button type="button" id="btnAddCritere" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Ajouter un critère
                            </button>
                        </div>

                        <!-- Projet -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <select class="form-select" name="projet_id" id="dash_projet" required>
                                    <option value="" selected disabled>Sélectionnez un projet</option>
                                    <?php if ($projets ?? []) : ?>
                                        <?php foreach ($projets as $projet) : ?>
                                            <option value="<?= $projet['id'] ?>"><?= $projet['name'] ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <label for="dash_projet">Projet*</label>
                            </div>
                        </div>


                        <!-- Classeur -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" name="classeur" id="dash_classeur" required>
                                    <option value="" selected disabled>Sélectionnez un classeur</option>
                                </select>
                                <label for="dash_classeur">Classeur*</label>
                            </div>
                        </div>
                        <!-- Feuille -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" name="feuille" id="dash_feuille" required>
                                    <option value="" selected disabled>Sélectionnez une feuille</option>
                                </select>
                                <label for="dash_feuille">Feuille*</label>
                            </div>
                        </div>


                        <!-- Colonnes -->
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" name="col_value" id="dash_colonne_valeur" required>
                                    <option value="" selected disabled>Sélectionnez une colonne</option>
                                </select>
                                <label for="dash_colonne_valeur">Colonne de valeur*</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" name="col_group" id="dash_colonne_group" required>
                                    <option value="" selected disabled>Sélectionnez une colonne</option>
                                </select>
                                <label for="dash_colonne_group">Régrouper par*</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select class="form-select" name="operator" id="dash_operator" required>
                                    <option value="" selected disabled>Sélectionnez l'opération</option>
                                    <option value="count">Nombre</option>
                                    <option value="sum">Somme</option>
                                    <option value="avg">Moyenne</option>
                                    <option value="max">Maximum</option>
                                    <option value="min">Minimum</option>
                                </select>
                                <label for="dash_operator">Opération*</label>
                            </div>
                        </div>

                        <!-- Criteres -->
                        <div class="col-md-12">
                            <div class="bg-success-subtle border border-primary rounded-1 px-3" id="containerCritere">
                                <h5 class="text-center py-2">Critères Appliqués</h5>
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-between border-0 px-0 pb-0">
                            <input type="hidden" name="status" id="dashStatus" value="planifie">
                            <button type="button" class="btn btn-secondary btn-sm px-3 my-0" data-bs-dismiss="modal" aria-label="Close">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3 my-0" id="dash_modbtn">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    let formdashID = null;
    let feuilles = [];
    let optionsColonne = [];
    let index = 0;

    $(document).ready(function() {
        $('#addDashEtatModal').on('shown.bs.modal', async function(event) {
            const dataId = $(event.relatedTarget).data('id');
            const form = document.getElementById('FormDashEtat');

            $('#dashPerLoadingScreen').show();
            $('#dashPerContentContainer').hide();

            if (dataId) {
                formdashID = dataId;
                $('#dash_modtitle').text('Modifier la requête');
                $('#dash_modbtn').text('Modifier');
                $('#dashPerLoadingText').text("Chargement des données requête...");

                try {
                    const response = await fetch(`./apis/dashs_etat.routes.php?id=${dataId}`, {
                        headers: {
                            'Authorization': `Bearer ${token}`
                        },
                        method: 'GET',
                    });

                    const result = await response.json();
                    form.code.value = result.data.code;
                    form.intitule.value = result.data.intitule;
                    form.annee_ref.value = result.data.annee_ref;
                    form.mois_ref.value = result.data.mois_ref;
                    form.etat.value = result.data.etat;
                    form.projet_id.value = result.data.projet_id;
                    form.description.value = result.data.description;
                } catch (error) {
                    errorAction('Impossible de charger les données.');
                } finally {
                    $('#dashPerLoadingScreen').hide();
                    $('#dashPerContentContainer').show();
                }
            } else {
                formdashID = null;
                $('#dash_modtitle').text('Ajouter une requête');
                $('#dash_modbtn').text('Ajouter');
                $('#dashPerLoadingText').text("Préparation du formulaire...");

                setTimeout(() => {
                    $('#dashPerLoadingScreen').hide();
                    $('#dashPerContentContainer').show();
                }, 200);
            }
        });

        $('#addDashEtatModal').on('hide.bs.modal', function() {
            $('#FormDashEtat')[0].reset();
            $('#containerCritere').html('<h5 class="text-center py-2">Critères Appliqués</h5>');
            $('#dashPerLoadingScreen').show();
            $('#dashPerContentContainer').hide();
        });

        $('#dash_projet').on('change', function() {
            onGetClasseur($(this).val());
        });

        $('#dash_classeur').on('change', function() {
            onGetFeuille($(this).val());
        });

        $('#dash_feuille').on('change', function() {
            onGetColonne($(this).val());
        });

        $('#btnAddCritere').on('click', function() {
            index++;
            const critereHtml = `
            <div id="critere_${index}">
                <div class="row mb-1 g-2">
                    <div class="col-lg-2" align="center">
                        <select name="et_ou_criteres[]" class="form-select form-select-sm" required>
                            <option value="AND">ET</option>
                            <option value="OR">OU</option>
                        </select>
                    </div>

                    <div class="col-lg-3" align="center">
                        <select name="champ_criteres[]" class="form-select form-select-sm colonne_critere" required>
                            ${optionsColonne}
                        </select>
                    </div>
                    <div class="col-lg-3" align="center">
                        <select name="condition_criteres[]" class="form-select form-select-sm" required>
                            <option value="=">Egal (=)</option>
                            <option value=">">Supérieur (&gt;)</option>
                            <option value="<">Inférieur (&lt;)</option>
                            <option value=">=">Supérieur ou égal (&gt;=)</option>
                            <option value="<=">Inférieur ou égal (&lt;=)</option>
                            <option value="<>">Différent (!= / &lt;&gt;)</option>
                            <option value="%x%">Contenant (%x%)</option>
                            <option value="x%">Commençant par (x%)</option>
                            <option value="%x">Terminant par (%x)</option>
                        </select>
                    </div>
                    <div class="col-lg-3" align="center">
                        <input type="text" name="valeur_criteres[]" placeholder="Valeur" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-lg-1" align="center">
                        <button type="button" class="btn btn-sm btn-phoenix-danger fs-10 px-2 py-1" onclick="$('#critere_${index}').remove()">
                            <i class="uil-trash-alt fs-8"></i>
                        </button>
                    </div>
                </div>
            </div>`;
            $("#containerCritere").append(critereHtml);
        });

        $('#FormDashEtat').on('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            const sqlQuery = onCreateDashQuery(formData);
            const url = formdashID ? `./apis/requete_fiche.routes.php?id=${formdashID}` : './apis/requete_fiche.routes.php';
            formData.append("query_sql", sqlQuery);

            /*
            $('#dash_modbtn').prop('disabled', true);
            $('#dash_modbtn').text('Envoi en cours...');

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
                    successAction('Données envoyées avec succès.');
                    $('#addDashEtatModal').modal('hide');
                } else {
                    errorAction(result.message || 'Erreur lors de l\'envoi des données.');
                }
            } catch (error) {
                errorAction('Erreur lors de l\'envoi des données.');
            } finally {
                $('#dash_modbtn').prop('disabled', false);
                $('#dash_modbtn').text('Enregistrer');
            }
            */
        });

    });

    async function onGetClasseur(projet) {
        if (projet) {
            const response = await fetch(`https://fiche.mrv-burundi.com/public/api/classeurs`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            const select = document.getElementById('dash_classeur');
            const options = [];

            options.push('<option value="" selected disabled>Sélectionnez</option>');
            result.data.forEach((item) => {
                if (item.id_projet == projet) {
                    const optionHtml = `<option value="${item.code_classeur}">${item.libelle_classeur}</option>`;
                    options.push(optionHtml);
                }
            });

            select.innerHTML = options.join('');
        }
    }

    async function onGetFeuille(classeur) {
        if (classeur) {
            const response = await fetch(`https://fiche.mrv-burundi.com/public/api/feuilles`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            const select = document.getElementById('dash_feuille');
            const options = [];

            options.push('<option value="" selected disabled>Sélectionnez</option>');
            result.data.forEach((item) => {
                if (item.code_classeur == classeur) {
                    const optionHtml = `<option value="${item.code_feuille}">${item.libelle_feuille}</option>`;
                    options.push(optionHtml);
                }
            });

            select.innerHTML = options.join('');
            feuilles = result.data;
        }
    }

    async function onGetColonne(feuille) {
        if (feuille) {
            optionsColonne = [];
            const response = await fetch(`https://fiche.mrv-burundi.com/public/api/lignes`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                method: 'GET',
            });

            const result = await response.json();
            const selectCol_value = document.getElementById('dash_colonne_valeur');
            const selectCol_group = document.getElementById('dash_colonne_group');

            optionsColonne.push('<option value="" selected disabled>Sélectionnez</option>');
            result.data.forEach((item) => {
                if (item.code_feuille == feuille) {
                    const optionHtml = `<option value="${item.nom_ligne}">${item.libelle_ligne}</option>`;
                    optionsColonne.push(optionHtml);
                }
            });

            selectCol_value.innerHTML = optionsColonne.join('');
            selectCol_group.innerHTML = optionsColonne.join('');
        }
    }

    function onCreateDashQuery(formData) {
        const operator = formData.get('operator');
        const colValue = formData.get('col_value');
        const colGroup = formData.get('col_group');
        const feuille = formData.get('feuille');

        let champs = formData.getAll("champ_criteres[]");
        let conds = formData.getAll("condition_criteres[]");
        let valeurs = formData.getAll("valeur_criteres[]");
        let logics = formData.getAll("et_ou_criteres[]");

        let table = feuilles.find(item => item.code_feuille == feuille).table_feuille;
        let select = operator === "count" ? `COUNT(${colValue}) AS result` : `${operator.toUpperCase()}(${colValue}) AS result`;
        let sql = `SELECT ${colGroup}, ${select} FROM ${table}`;
        let where = [];

        for (let i = 0; i < champs.length; i++) {
            let champ = champs[i];
            let cond = conds[i];
            let val = valeurs[i];
            let part = "";

            switch (cond) {
                case "%x%":
                    part = `${champ} LIKE '%${val}%'`;
                    break;
                case "x%":
                    part = `${champ} LIKE '${val}%'`;
                    break;
                case "%x":
                    part = `${champ} LIKE '%${val}'`;
                    break;
                default:
                    part = `${champ} ${cond} '${val}'`;
            }

            where.push((i === 0 ? "" : logics[i]) + " " + part);
        }

        if (where.length > 0) sql += " WHERE " + where.join(" ");

        sql += ` GROUP BY ${colGroup}`;

        return sql;
    }
</script>