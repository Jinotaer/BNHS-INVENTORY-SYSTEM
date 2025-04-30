<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $par_id = $_GET['delete'];
  $adn = "DELETE FROM property_acknowledgment_receipts WHERE par_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $par_id);
  $result = $stmt->execute();
  $stmt->close();
  if ($result) {
    $success = "Deleted";
    header("refresh:1; url=rpcppe.php");
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
                <h2 class="text-center mb-3 pt-3 text-uppercase">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT	</h2>
              </div>
              <div class="col text-right">
                <a href="print_par_files.php" class="btn btn-sm btn-primary">
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
                    <th scope="col">PAR No.</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Property Number</th>
                    <th scope="col">Date Acquired</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Total Cost</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                    <th scope="col">Property Custodian Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                    <!-- <th scope="col">Actions</th> -->
                  </tr>
                </thead>
                <tbody>
                  <?php
                  // Build the WHERE clause for entity filtering
                  $entity_filter = "";
                  if (isset($_GET['entity_id']) && !empty($_GET['entity_id'])) {
                    $entity_id = $_GET['entity_id'];
                    $entity_filter = " WHERE par.entity_id = '$entity_id' ";
                  }
                  
                  $ret = "SELECT par.*, i.item_description, i.unit_cost, i.unit, 
                          e.entity_name, e.fund_cluster as entity_fund_cluster, 
                          pi.quantity, pi.property_number
                          FROM property_acknowledgment_receipts par
                          LEFT JOIN par_items pi ON par.par_id = pi.par_id
                          LEFT JOIN items i ON pi.item_id = i.item_id
                          LEFT JOIN entities e ON par.entity_id = e.entity_id
                          $entity_filter
                          ORDER BY par.created_at DESC";
                  
                  $stmt = $mysqli->prepare($ret);
                  
                  if ($stmt === false) {
                    echo "Error preparing statement: " . $mysqli->error;
                  } else {
                    $stmt->execute();
                    $res = $stmt->get_result();
                    
                    while ($par = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo $par->entity_name; ?></td>
                      <td><?php echo $par->entity_fund_cluster; ?></td>
                      <td><?php echo $par->par_no; ?></td>
                      <td><?php echo $par->quantity; ?></td>
                      <td><?php echo $par->unit; ?></td>
                      <td><?php echo $par->item_description; ?></td>
                      <td><?php echo $par->property_number; ?></td>
                      <td><?php echo $par->date_acquired; ?></td>
                      <td><?php echo $par->unit_cost; ?></td>
                      <td><?php echo number_format($par->unit_cost * $par->quantity, 2); ?></td>
                      <td><?php echo $par->end_user_name; ?></td>
                      <td><?php echo $par->receiver_position; ?></td>
                      <td><?php echo $par->receiver_date; ?></td>
                      <td><?php echo $par->custodian_name; ?></td>
                      <td><?php echo $par->custodian_position; ?></td>
                      <td><?php echo $par->custodian_date; ?></td>
                      <td>
                          <!-- <a href="rpcppe.php?delete=<?php echo $par->par_id; ?>" 
                            onclick="return confirm('Are you sure you want to delete this record?')">
                            <button class="btn btn-sm btn-danger">
                              <i class="fas fa-trash"></i>
                              Delete
                            </button>
                          </a>
                          <a href="par_update.php?update=<?php echo $par->par_id; ?>">
                            <button class="btn btn-sm btn-primary">
                              <i class="fas fa-user-edit"></i>
                              Update
                            </button>
                          </a> -->
                      </td>
                    </tr>
                  <?php 
                    }
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