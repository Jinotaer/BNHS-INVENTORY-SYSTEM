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
                  $ret = "SELECT ics.ics_no, ics.end_user_name, ics.end_user_position, ics.end_user_date,
                          ics.custodian_name, ics.custodian_position, ics.custodian_date,
                          e.entity_name, e.fund_cluster,
                          i.item_description, i.unit, i.unit_cost, i.estimated_useful_life,
                          ii.quantity, ii.inventory_item_no,
                          (ii.quantity * i.unit_cost) as total_cost
                          FROM inventory_custodian_slips ics
                          LEFT JOIN entities e ON ics.entity_id = e.entity_id
                          LEFT JOIN ics_items ii ON ics.ics_id = ii.ics_id
                          LEFT JOIN items i ON ii.item_id = i.item_id
                          WHERE i.item_id NOT IN (SELECT item_id FROM iar_items)
                          ORDER BY ics.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  if ($stmt) {
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($ics = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td class="text-primary" scope="rows"><?php echo isset($ics->entity_name) ? $ics->entity_name : 'N/A'; ?></td>
                      <td><?php echo isset($ics->fund_cluster) ? $ics->fund_cluster : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo $ics->ics_no; ?></td>
                      <td><?php echo isset($ics->quantity) ? $ics->quantity : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo isset($ics->unit) ? $ics->unit : 'N/A'; ?></td>
                      <td><?php echo isset($ics->unit_cost) ? $ics->unit_cost : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo isset($ics->total_cost) ? $ics->total_cost : 'N/A'; ?></td>
                      <td><?php echo isset($ics->item_description) ? $ics->item_description : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo isset($ics->inventory_item_no) ? $ics->inventory_item_no : 'N/A'; ?></td>
                      <td><?php echo isset($ics->estimated_useful_life) ? $ics->estimated_useful_life : 'N/A'; ?></td>
                      <td class="text-primary"><?php echo $ics->end_user_name; ?></td>
                      <td><?php echo $ics->end_user_position; ?></td>
                      <td class="text-primary"><?php echo $ics->end_user_date; ?></td>
                      <td><?php echo $ics->custodian_name; ?></td>
                      <td class="text-primary"><?php echo $ics->custodian_position; ?></td>
                      <td><?php echo $ics->custodian_date; ?></td>
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
</body>

</html>