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
            <div class="card-header border-0">
              REPORT ON THE PHYSICAL COUNT OF SEMI- EXPENDABLE PROPERTY
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th class="text-primary" scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th class="text-primary" scope="col">ICS No.</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Unit</th>
                    <th scope="col">Unit Cost</th>
                    <th class="text-primary" scope="col">Total Amount</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Inventory Item No.</th>
                    <th scope="col">Estimated Useful Life</th>
                    <th class="text-primary" scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th class="text-primary" scope="col">Date Received(by User)</th>
                    <th scope="col">Property Custodian</th>
                    <th class="text-primary" scope="col">Position/Office</th>
                    <th scope="col">Date Received(by Custodian)</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                  $ret = "SELECT * FROM  inventory_custodian_slip  ORDER BY `inventory_custodian_slip`.`created_at` DESC ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($ics = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td class="text-primary" scope="rows"><?php echo $ics->entity_name; ?></td>
                      <td><?php echo $ics->fund_cluster; ?></td>
                      <td class="text-primary"><?php echo $ics->ics_no; ?></td>
                      <td><?php echo $ics->quantity; ?></td>
                      <td class="text-primary"><?php echo $ics->unit; ?></td>
                      <td><?php echo $ics->unit_cost; ?></td>
                      <td class="text-primary"><?php echo $ics->total_amount; ?></td>
                      <td><?php echo $ics->item_description; ?></td>
                      <td class="text-primary"><?php echo $ics->inventory_item_no; ?></td>
                      <td><?php echo $ics->estimated_life; ?></td>
                      <td><?php echo $ics->end_user_name; ?></td>
                      <td class="text-primary"><?php echo $ics->end_user_position; ?></td>
                      <td><?php echo $ics->date_received_user; ?></td>
                      <td><?php echo $ics->custodian_name; ?></td>
                      <td class="text-primary"><?php echo $ics->custodian_position; ?></td>
                      <td><?php echo $ics->date_received_custodian; ?></td>
                  <?php } ?>
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
</body>

</html>