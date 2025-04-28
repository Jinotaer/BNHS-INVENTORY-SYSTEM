<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete ICS Record
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  
  // First delete related items in ics_items
  $adn = "DELETE FROM ics_items WHERE ics_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  
  // Then delete the main ICS record
  $adn = "DELETE FROM inventory_custodian_slips WHERE ics_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=display_ics.php");
  } else {
    $err = "Try Again Later";
  }
}
require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
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
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
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
              <div class="col">
                <h2 class="text-center mb-3 pt-3 text-uppercase">Inventory Custodian Slip</h2>
              </div>
              <div class="col text-right">
                <i></i>
                <a href="print_ics_files.php" class="btn btn-sm btn-primary" target="_blank">
                  <i class="material-icons-sharp text-primary"></i>
                  Print files</a>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <th scope="col">ICS No.</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Total Amount</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Inventory Item No.</th>
                    <th scope="col">Estimated Useful Life</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date Received(by User)</th>
                    <th scope="col">Property Custodian</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date Received(by Custodian)</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT ics.ics_id, e.entity_name, e.fund_cluster, ics.ics_no,
                           ii.quantity, i.unit, i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
                           i.item_description, ii.inventory_item_no, i.estimated_useful_life,
                           ics.end_user_name, ics.end_user_position, ics.end_user_date,
                           ics.custodian_name, ics.custodian_position, ics.custodian_date
                         FROM inventory_custodian_slips ics
                         JOIN entities e ON ics.entity_id = e.entity_id
                         JOIN ics_items ii ON ics.ics_id = ii.ics_id
                         JOIN items i ON ii.item_id = i.item_id
                         ORDER BY ics.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($ics = $res->fetch_object()) {
                    ?>
                    <tr>
                      <td><?php echo $ics->entity_name; ?></td>
                      <td><?php echo $ics->fund_cluster; ?></td>
                      <td><?php echo $ics->ics_no; ?></td>
                      <td><?php echo $ics->quantity; ?></td>
                      <td><?php echo $ics->unit; ?></td>
                      <td><?php echo $ics->unit_cost; ?></td>
                      <td><?php echo $ics->total_amount; ?></td>
                      <td><?php echo $ics->item_description; ?></td>
                      <td><?php echo $ics->inventory_item_no; ?></td>
                      <td><?php echo $ics->estimated_useful_life; ?></td>
                      <td><?php echo $ics->end_user_name; ?></td>
                      <td><?php echo $ics->end_user_position; ?></td>
                      <td><?php echo $ics->end_user_date; ?></td>
                      <td><?php echo $ics->custodian_name; ?></td>
                      <td><?php echo $ics->custodian_position; ?></td>
                      <td><?php echo $ics->custodian_date; ?></td>
                      <td>
                        <a href="display_ics.php?delete=<?php echo $ics->ics_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="ics_update.php?update=<?php echo $ics->ics_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
                      </td>
                    </tr>
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