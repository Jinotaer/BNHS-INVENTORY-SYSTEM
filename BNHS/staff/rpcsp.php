<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  inventory_custodian_slip  WHERE  id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $result = $stmt->execute();
  $stmt->close();
  if ($result) {
    $success = "Deleted";
    header("refresh:1; url=rpcsp.php");
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
                <h2 class="text-center mb-3 pt-3 text-uppercase">REPORT ON THE PHYSICAL COUNT OF SEMI- EXPENDABLE PROPERTY</h2>
              </div>
              <div class="col text-right">
                <i></i>
                <a href="print_ics_files.php" class="btn btn-sm btn-primary">
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
                    <!-- <th scope="col">Actions</th> -->
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Build the WHERE clause for entity filtering
                    $entity_filter = "";
                    if (isset($_GET['entity_id']) && !empty($_GET['entity_id'])) {
                      $entity_id = $_GET['entity_id'];
                      $entity_filter = " WHERE ics.entity_id = '$entity_id' ";
                    }
                    
                    $ret = "SELECT ics.*, i.item_description, i.unit_cost, i.unit, 
                            e.entity_name, e.fund_cluster as entity_fund_cluster,
                            ii.quantity, ii.inventory_item_no
                            FROM inventory_custodian_slip ics
                            LEFT JOIN ics_items ii ON ics.id = ii.ics_id
                            LEFT JOIN items i ON ii.item_id = i.item_id
                            LEFT JOIN entities e ON ics.entity_id = e.entity_id
                            $entity_filter
                            ORDER BY ics.created_at DESC";
                    
                    $stmt = $mysqli->prepare($ret);
                    
                    if ($stmt === false) {
                      echo "Error preparing statement: " . $mysqli->error;
                    } else {
                      $stmt->execute();
                      $res = $stmt->get_result();
                    
                    while ($ics = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo isset($ics->entity_name) ? $ics->entity_name : ''; ?></td>
                      <td><?php echo isset($ics->entity_fund_cluster) ? $ics->entity_fund_cluster : ''; ?></td>
                      <td><?php echo $ics->ics_no; ?></td>
                      <td><?php echo isset($ics->quantity) ? $ics->quantity : ''; ?></td>
                      <td><?php echo isset($ics->unit) ? $ics->unit : ''; ?></td>
                      <td><?php echo isset($ics->unit_cost) ? $ics->unit_cost : ''; ?></td>
                      <td><?php echo $ics->total_amount; ?></td>
                      <td><?php echo isset($ics->item_description) ? $ics->item_description : ''; ?></td>
                      <td><?php echo isset($ics->inventory_item_no) ? $ics->inventory_item_no : ''; ?></td>
                      <td><?php echo $ics->estimated_life; ?></td>
                      <td><?php echo $ics->end_user_name; ?></td>
                      <td><?php echo $ics->end_user_position; ?></td>
                      <td><?php echo $ics->date_received_user; ?></td>
                      <td><?php echo $ics->custodian_name; ?></td>
                      <td><?php echo $ics->custodian_position; ?></td>
                      <td><?php echo $ics->date_received_custodian; ?></td>
                      <td>
                        <!-- <a href="rpcsp.php?delete=<?php echo $ics->id; ?>" 
                           onclick="return confirm('Are you sure you want to delete this record?')">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete
                          </button>
                        </a>

                        <a href="ics_update.php?update=<?php echo $ics->id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a> -->
                      </td>
                    </tr>
                  <?php }
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