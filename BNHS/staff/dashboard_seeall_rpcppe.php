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
                    <th class="text-primary" scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th class="text-primary" scope="col">PAR No.</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Unit</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Property Number</th>
                    <th scope="col">Date Acquired</th>
                    <th class="text-primary" scope="col">Unit Cost</th>
                    <th scope="col">Total Cost</th>
                    <th class="text-primary" scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th class="text-primary" scope="col">Date</th>
                    <th scope="col">Property Custodian Name</th>
                    <th class="text-primary" scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT p.par_no, p.date_acquired, p.end_user_name, p.receiver_position, p.receiver_date, 
                          p.custodian_name, p.custodian_position, p.custodian_date,
                          e.entity_name, e.fund_cluster,
                          i.item_description, i.unit, i.unit_cost,
                          pi.quantity, pi.property_number,
                          (pi.quantity * i.unit_cost) as total_cost
                          FROM property_acknowledgment_receipts p 
                          LEFT JOIN entities e ON p.entity_id = e.entity_id
                          LEFT JOIN par_items pi ON p.par_id = pi.par_id
                          LEFT JOIN items i ON pi.item_id = i.item_id
                          ORDER BY p.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  if ($stmt) {
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($par = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td class="text-primary"><?php echo isset($par->entity_name) ? $par->entity_name : 'N/A'; ?></td>
                      <td><?php echo isset($par->fund_cluster) ? $par->fund_cluster : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo $par->par_no; ?></td>
                      <td><?php echo isset($par->quantity) ? $par->quantity : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo isset($par->unit) ? $par->unit : 'N/A'; ?></td>
                      <td><?php echo isset($par->item_description) ? $par->item_description : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo isset($par->property_number) ? $par->property_number : 'N/A'; ?></td>
                      <td><?php echo $par->date_acquired; ?></td>
                      <td class="text-primary"><?php echo isset($par->unit_cost) ? $par->unit_cost : 'N/A'; ?></td>
                      <td><?php echo isset($par->total_cost) ? $par->total_cost : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo $par->end_user_name; ?></td>
                      <td><?php echo $par->receiver_position; ?></td>
                      <td class="text-primary"><?php echo $par->receiver_date; ?></td>
                      <td><?php echo $par->custodian_name; ?></td>
                      <td class="text-primary"><?php echo $par->custodian_position; ?></td>
                      <td><?php echo $par->custodian_date; ?></td>
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