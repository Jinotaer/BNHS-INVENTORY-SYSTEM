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
              <strong>REPORT ON THE PHYSICAL COUNT OF SEMI- EXPENDABLE PROPERTY</strong>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                  <th class="text-primary" scope="col">Article</th>
                    <th scope="col">Item Description</th>
                    <th class="text-primary" scope="col">Inventory Item No.</th>
                    <th scope="col">Unit</th>
                    <th class="text-primary" scope="col">Unit Value</th>
                    <th scope="col">Quantity</th>
                    <th class="text-primary" scope="col">Total Amount</th>
                    <th scope="col">Estimated Useful Life</th>
                    <th class="text-primary" scope="col">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT ics.ics_id as id, ics.ics_no, ics.end_user_name, ics.end_user_position, 
                  ics.end_user_date as date_received_user, ics.custodian_name, ics.custodian_position, 
                  ics.custodian_date as date_received_custodian, ics.created_at,
                  i.item_description, i.unit_cost, i.unit, i.estimated_useful_life as estimated_life,
                  e.entity_name, e.fund_cluster as entity_fund_cluster,
                  ii.quantity, ii.inventory_item_no, ii.remarks, ii.article,
                  (ii.quantity * i.unit_cost) as total_amount
                  FROM inventory_custodian_slips ics
                  LEFT JOIN ics_items ii ON ics.ics_id = ii.ics_id
                  LEFT JOIN items i ON ii.item_id = i.item_id
                  LEFT JOIN entities e ON ics.entity_id = e.entity_id
                          ORDER BY ics.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  if ($stmt) {
                    $stmt->execute();
                    $res = $stmt->get_result();
                    while ($ics = $res->fetch_object()) {
                  ?>
                      <tr>
                      <td class="text-primary"><?php echo isset($ics->article) ? $ics->article : ''; ?></td>
                        <td><?php echo isset($ics->item_description) ? $ics->item_description : ''; ?></td>
                        <td class="text-primary"><?php echo isset($ics->inventory_item_no) ? $ics->inventory_item_no : ''; ?></td>
                        <td><?php echo isset($ics->unit) ? $ics->unit : ''; ?></td>
                        <td class="text-primary"><?php echo isset($ics->unit_cost) ? $ics->unit_cost : ''; ?></td>
                        <td><?php echo isset($ics->quantity) ? $ics->quantity : ''; ?></td>
                        <td class="text-primary"><?php echo isset($ics->total_amount) ? $ics->total_amount : ''; ?></td>
                        <td><?php echo isset($ics->estimated_life) ? $ics->estimated_life : ''; ?></td>
                        <td class="text-primary"><?php echo isset($ics->remarks) ? $ics->remarks : ''; ?></td>
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