<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
?>

<body>
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    ?>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/front.png); background-size: cover;"
      class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
        </div>
      </div>
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--8">
      <!-- Table -->
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0" style="text-align: center; padding: 30px;">
            <strong>REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</strong>  
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                  <th class="text-primary" scope="col">Article</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Property No.</th>
                    <th scope="col">Unit</th>
                    <th class="text-primary" scope="col">Unit Value</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Total Amount</th>
                    <th scope="col">Date Acquired</th>
                    <th class="text-primary" scope="col">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $ret = "SELECT par.par_id as id, par.par_no, par.end_user_name, par.receiver_position, 
                            par.receiver_date, par.date_acquired,
                            i.item_description, i.unit_cost, i.unit,
                            e.entity_name, e.fund_cluster as entity_fund_cluster,
                            pi.quantity, pi.property_number, pi.remarks, pi.article,
                            (pi.quantity * i.unit_cost) as total_amount
                            FROM property_acknowledgment_receipts par
                            LEFT JOIN par_items pi ON par.par_id = pi.par_id
                            LEFT JOIN items i ON pi.item_id = i.item_id
                            LEFT JOIN entities e ON par.entity_id = e.entity_id
                          ORDER BY par.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  if ($stmt) {
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($par = $res->fetch_object()) {
                  ?>
                    <tr>
                    <td class="text-primary"><?php echo isset($par->article) ? $par->article : ''; ?></td>
                        <td><?php echo isset($par->item_description) ? $par->item_description : ''; ?></td>
                        <td class="text-primary"><?php echo isset($par->property_number) ? $par->property_number : ''; ?></td>
                        <td><?php echo isset($par->unit) ? $par->unit : ''; ?></td>
                        <td class="text-primary"><?php echo isset($par->unit_cost) ? $par->unit_cost : ''; ?></td>
                        <td><?php echo isset($par->quantity) ? $par->quantity : ''; ?></td>
                        <td class="text-primary"><?php echo isset($par->total_amount) ? $par->total_amount : ''; ?></td>
                        <td><?php echo isset($par->date_acquired) ? $par->date_acquired : ''; ?></td>
                        <td class="text-primary"><?php echo isset($par->remarks) ? $par->remarks : ''; ?></td>
                    </tr>
                  <?php
                      }
                  } else {
                      echo "<tr><td colspan='16' class='text-center'>Error executing query: " . $mysqli->error . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php
      require_once('partials/_mainfooter.php');
      ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
  <script src="//cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#parTable').DataTable({
        "drawCallback": function() {
          // Ensures the positioning is applied after table drawing
          $('.dataTables_wrapper').css('position', 'relative');
        }
      });
    });
  </script>
</body>

</html>