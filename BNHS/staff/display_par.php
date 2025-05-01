<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

//Delete individual item
if (isset($_GET['delete_item'])) {
  $par_item_id = $_GET['delete_item'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // First get the item_id from par_items
    $stmt = $mysqli->prepare("SELECT item_id FROM par_items WHERE par_item_id = ?");
    $stmt->bind_param('i', $par_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item_id = $result->fetch_object()->item_id;
    
    // Delete from par_items
    $stmt = $mysqli->prepare("DELETE FROM par_items WHERE par_item_id = ?");
    $stmt->bind_param('i', $par_item_id);
    $stmt->execute();
    
    // Delete from items
    $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Item Deleted Successfully";
    header("refresh:1; url=display_par.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_par.php");
  }
}

//Delete par
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // Get all item_ids from par_items for this par
    $stmt = $mysqli->prepare("SELECT item_id FROM par_items WHERE par_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Delete from par_items first
    $stmt = $mysqli->prepare("DELETE FROM par_items WHERE par_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Delete corresponding items
    while ($row = $result->fetch_object()) {
      $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
      $stmt->bind_param('i', $row->item_id);
      $stmt->execute();
    }
    
    // Delete from inspection_acceptance_reports
    $stmt = $mysqli->prepare("DELETE FROM property_acknowledgment_receipts WHERE par_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Record Deleted Successfully";
    header("refresh:1; url=display_par.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_par.php");
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
                <h2 class="text-center mb-3 pt-3 text-uppercase">Purchase Acceptance Report</h2>
              </div>
              <!-- <div class="col text-right">
                <a href="print_par_files.php" class="btn btn-sm btn-primary" target="_blank">
                  <i class="material-icons-sharp text-primary"></i>
                  Print files</a>
              </div> -->
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <!-- <th scope="col">Article</th> -->
                    <th scope="col">PAR No.</th>
                    <!-- <th scope="col">Quantity</th>
                    <th scope="col">Unit</th> -->
                    <!-- <th scope="col">Description</th> -->
                    <!-- <th scope="col">Property Number</th> -->
                    <th scope="col">Date Acquired</th>
                    <!-- <th scope="col">Unit Cost</th> -->
                    <th scope="col">Total Cost</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                    <th scope="col">Property Custodian Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date</th>
                    <!-- <th scope="col">Remarks</th> -->
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT p.*, e.entity_name, e.fund_cluster 
                          FROM property_acknowledgment_receipts p 
                          JOIN entities e ON p.entity_id = e.entity_id
                          GROUP BY p.par_no
                          ORDER BY p.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($par = $res->fetch_object()) {
                    // Get one item from this PAR to display as an example
                    $item_query = "SELECT pi.quantity, pi.property_number, pi.article, pi.par_item_id, pi.remarks, 
                                          i.item_id, 
                                          -- i.item_description,
                                           i.unit, 
                                           i.unit_cost, 
                                          (pi.quantity * i.unit_cost) as total_amount
                                   FROM par_items pi 
                                   JOIN items i ON pi.item_id = i.item_id
                                   WHERE pi.par_id = ?
                                   LIMIT 1";
                    $item_stmt = $mysqli->prepare($item_query);
                    $item_stmt->bind_param('i', $par->par_id);
                    $item_stmt->execute();
                    $item_res = $item_stmt->get_result();
                    $item = $item_res->fetch_object();
                  ?>
                    <tr>
                      <td><?php echo $par->entity_name; ?></td>
                      <td><?php echo $par->fund_cluster; ?></td>
                      <!-- <td><?php echo $item->article; ?></td> -->
                      <td><?php echo $par->par_no; ?></td>
                      <!-- <td><?php echo $item->quantity; ?></td>
                      <td><?php echo $item->unit; ?></td> -->
                      <!-- <td><?php echo $item->item_description; ?></td> -->
                      <!-- <td><?php echo $item->property_number; ?></td> -->
                      <td><?php echo $par->date_acquired; ?></td>
                      <!-- <td><?php echo $item->unit_cost; ?></td> -->
                      <td><?php echo $item->total_amount; ?></td>
                      <td><?php echo $par->end_user_name; ?></td>
                      <td><?php echo $par->receiver_position; ?></td>
                      <td><?php echo $par->receiver_date; ?></td>
                      <td><?php echo $par->custodian_name; ?></td>
                      <td><?php echo $par->custodian_position; ?></td>
                      <td><?php echo $par->custodian_date; ?></td>
                      <!-- <td><?php echo $item->remarks; ?></td> -->
                      <td>
                        <!-- <a href="display_par.php?delete=<?php echo $par->par_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete All
                          </button>
                        </a> -->

                        <a href="par_update.php?update=<?php echo $par->par_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
                        <a href="print_par_files.php?par_id=<?php echo $par->par_id; ?>" target="_blank">
                          <button class="btn btn-sm btn-info">
                            <i class="fas fa-print"></i>
                            Print File
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