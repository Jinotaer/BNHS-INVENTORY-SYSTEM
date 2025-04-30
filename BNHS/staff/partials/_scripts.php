<!-- Core -->
<script src="assets\jb\jquery\jquery.min.js"></script>
<script src="assets\jb\bootstrap\js\bootstrap.bundle.min.js"></script>
<script src="assets/js/argon.js?v=1.0.0"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<!-- <script src="assets/vendor/chart.js/dist/Chart.min.js"></script>
<script src="assets/vendor/chart.js/dist/Chart.extension.js"></script> -->

<script>
  $(document).ready(function () {
    $("#search").on("keyup", function () {
      let value = $(this).val().toLowerCase();
      let hasVisible = false;

      $("#userTableBody tr").each(function () {
        if ($(this).is("#noResults")) return;

        let isVisible = $(this).text().toLowerCase().indexOf(value) > -1;
        $(this).toggle(isVisible);

        if (isVisible) hasVisible = true;
      });

      $("#noResults").toggle(!hasVisible);
    });
  });
</script>

