<!-- ===============================================-->
<!--    JavaScripts-->
<!-- ===============================================-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js'></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<!-- datatable js -->
<!-- <script src="https://code.jquery.com/jquery-3.7.1.js"></script> -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/2.3.3/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.3/js/dataTables.jqueryui.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.4/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.jqueryui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.2.4/js/buttons.colVis.min.js"></script>

<script src="vendors/popper/popper.min.js"></script>
<script src="vendors/bootstrap/bootstrap.min.js"></script>
<script src="vendors/anchorjs/anchor.min.js"></script>
<script src="vendors/is/is.min.js"></script>
<script src="vendors/fontawesome/all.min.js"></script>
<script src="vendors/lodash/lodash.min.js"></script>
<script src="vendors/list.js/list.min.js"></script>
<script src="vendors/feather-icons/feather.min.js"></script>
<script src="vendors/dayjs/dayjs.min.js"></script>
<script src="vendors/leaflet/leaflet.js"></script>
<script src="vendors/leaflet.markercluster/leaflet.markercluster.js"></script>
<script src="vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js"></script>
<script src="vendors/echarts/echarts.min.js"></script>
<script src="vendors/tinymce/tinymce.min.js"></script>
<script src="vendors/choices/choices.min.js"></script>
<script src="vendors/dropzone/dropzone-min.js"></script>
<script src="vendors/fullcalendar/index.global.min.js"></script>
<script src="vendors/flatpickr/flatpickr.min.js"></script>
<script src="vendors/prism/prism.js"></script>
<script src="vendors/swiper/swiper-bundle.min.js"></script>

<script src="assets/js/phoenix.js"></script>
<script src="assets/js/ecommerce-dashboard.js"></script>
<script src="assets/js/project-details.js"></script>

<!-- ===============================================-->
<!--    Import JavaScripts-->
<!-- ===============================================-->
<script src="assets/scripts/datatables.js"></script>
<script src="assets/scripts/tiny-editor.js"></script>
<script src="assets/scripts/sweet-alerts.js"></script>
<script src="assets/scripts/func-action.js"></script>
<script src="assets/scripts/chart-config.js"></script>

<!-- ===============================================-->
<!--    Import Modals-->
<!-- ===============================================-->
<?php
$modalsDir = 'components/modals';
foreach (glob("$modalsDir/*.php") as $modalFile) {
  echo "<!-- Loading modal: " . basename($modalFile) . " -->\n";
  include $modalFile;
}
?>

<!-- ===============================================-->
<!--    Search Filter Data-->
<!-- ===============================================-->
<script>
  function updateFilters() {
    const secteurId = document.getElementById('secteurFilter').value;
    const actionId = document.getElementById('actionFilter').value;
    const statusId = document.getElementById('statusFilter').value;
    const params = new URLSearchParams();

    if (secteurId) params.set('secteur', secteurId);
    if (actionId) params.set('action', actionId);
    if (statusId) params.set('status', statusId);

    const queryString = params.toString();
    const newUrl = queryString ? '?' + queryString : '?';
    window.location.href = newUrl;
  }

  document.getElementById('secteurFilter').addEventListener('change', updateFilters);
  document.getElementById('actionFilter').addEventListener('change', updateFilters);
  document.getElementById('statusFilter').addEventListener('change', updateFilters);
</script>
<script>
  searchFilterData('searchInput', '.navbar-item');
  searchFilterData('searchProjet', '.projet-item');
  searchFilterData('searchDossiers', '.dossier-item');
  searchFilterData('searchProfilTask', '.profile-task');
  searchFilterData('searchNotification', '.notification-card');
  searchFilterData('searchRapportPeriodique', '.rapport-item');
</script>