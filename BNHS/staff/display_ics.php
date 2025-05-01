<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

//Delete individual item
if (isset($_GET['delete_item'])) {
  $ics_item_id = $_GET['delete_item'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // First get the item_id from ics_items
    $stmt = $mysqli->prepare("SELECT item_id FROM ics_items WHERE ics_item_id = ?");
    $stmt->bind_param('i', $ics_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item_id = $result->fetch_object()->item_id;
    
    // Delete from ics_items
    $stmt = $mysqli->prepare("DELETE FROM ics_items WHERE ics_item_id = ?");
    $stmt->bind_param('i', $ics_item_id);
    $stmt->execute();
    
    // Delete from items
    $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Item Deleted Successfully";
    header("refresh:1; url=display_ics.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_ics.php");
  }
}

//Delete ICS
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // Get all item_ids from ics_items for this ICS
    $stmt = $mysqli->prepare("SELECT item_id FROM ics_items WHERE ics_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Delete from ics_items first
    $stmt = $mysqli->prepare("DELETE FROM ics_items WHERE ics_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Delete corresponding items
    while ($row = $result->fetch_object()) {
      $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
      $stmt->bind_param('i', $row->item_id);
      $stmt->execute();
    }
    
    // Delete from inventory_custodian_slips
    $stmt = $mysqli->prepare("DELETE FROM inventory_custodian_slips WHERE ics_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Record Deleted Successfully";
    header("refresh:1; url=display_ics.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_ics.php");
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidenav -->
  <?php require_once('partials/_sidebar.php'); ?>
  
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>
    
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
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
              <!-- <div class="col text-right">
                <a href="print_ics_files.php" class="btn btn-sm btn-primary" target="_blank">
                  <i class="fas fa-print"></i> Print files
                </a>
              </div> -->
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Entity Name</th>
                    <th scope="col">Fund Cluster</th>
                    <!-- <th scope="col">Article</th> -->
                    <th scope="col">ICS No.</th>
                    <!-- <th scope="col">Quantity</th> -->
                    <!-- <th scope="col">Unit</th> -->
                    <!-- <th scope="col">Unit Cost</th> -->
                    <!-- <th scope="col">Total Amount</th> -->
                    <!-- <th scope="col">Item Description</th> -->
                    <!-- <th scope="col">Inventory Item No.</th> -->
                    <!-- <th scope="col">Estimated Useful Life</th> -->
                    <th scope="col">User Name</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date Received(by User)</th>
                    <th scope="col">Property Custodian</th>
                    <th scope="col">Position/Office</th>
                    <th scope="col">Date Received(by Custodian)</th>
                    <!-- <th scope="col">Remarks</th> -->
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT ics.ics_id, e.entity_name, e.fund_cluster, ics.ics_no,
                           ics.end_user_name, ics.end_user_position, ics.end_user_date,
                           ics.custodian_name, ics.custodian_position, ics.custodian_date
                         FROM inventory_custodian_slips ics
                         JOIN entities e ON ics.entity_id = e.entity_id
                         GROUP BY ics.ics_no
                         ORDER BY ics.created_at DESC";
                  $stmt = $mysqli->prepare($ret);
                  if ($stmt === false) {
                    die("Error preparing statement: " . $mysqli->error);
                  }
                  if (!$stmt->execute()) {
                    die("Error executing statement: " . $stmt->error);
                  }
                  $res = $stmt->get_result();
                  while ($ics = $res->fetch_object()) {
                    // Get one item from this ICS to display as an example
                    $item_query = "SELECT ii.ics_item_id, i.item_id, i.article, ii.quantity, i.unit, 
                                    i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
                                    i.item_description, ii.inventory_item_no, i.estimated_useful_life,
                                    ii.remarks
                                  FROM ics_items ii
                                  JOIN items i ON ii.item_id = i.item_id
                                  WHERE ii.ics_id = ?
                                  LIMIT 1";
                    $item_stmt = $mysqli->prepare($item_query);
                    $item_stmt->bind_param('i', $ics->ics_id);
                    $item_stmt->execute();
                    $item_res = $item_stmt->get_result();
                    $item = $item_res->fetch_object();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($ics->entity_name); ?></td>
                      <td><?php echo htmlspecialchars($ics->fund_cluster); ?></td>
                      <!-- <td><?php echo htmlspecialchars($item->article); ?></td> -->
                      <td><?php echo htmlspecialchars($ics->ics_no); ?></td>
                      <!-- <td><?php echo number_format($item->quantity); ?></td> -->
                      <!-- <td><?php echo htmlspecialchars($item->unit); ?></td>
                      <td>₱<?php echo number_format($item->unit_cost, 2); ?></td>
                      <td>₱<?php echo number_format($item->total_amount, 2); ?></td>
                      <td><?php echo htmlspecialchars($item->item_description); ?></td>
                      <td><?php echo htmlspecialchars($item->inventory_item_no); ?></td>
                      <td><?php echo htmlspecialchars($item->estimated_useful_life); ?></td> -->
                      <td><?php echo htmlspecialchars($ics->end_user_name); ?></td>
                      <td><?php echo htmlspecialchars($ics->end_user_position); ?></td>
                      <td><?php echo date('M d, Y', strtotime($ics->end_user_date)); ?></td>
                      <td><?php echo htmlspecialchars($ics->custodian_name); ?></td>
                      <td><?php echo htmlspecialchars($ics->custodian_position); ?></td>
                      <td><?php echo date('M d, Y', strtotime($ics->custodian_date)); ?></td>
                      <!-- <td><?php echo htmlspecialchars($item->remarks); ?></td> -->
                      <td>
                        <!-- <a href="display_ics.php?delete=<?php echo $ics->ics_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete All
                          </button>
                        </a> -->

                        <a href="ics_update.php?update=<?php echo $ics->ics_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a>
                        <a href="print_ics_files.php?ics_id=<?php echo $ics->ics_id; ?>" target="_blank">
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
      <?php require_once('partials/_mainfooter.php'); ?>
    </div>
  </div>
  
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
  
  <style>
    .table-responsive {
      max-height: 500px;
      overflow-y: auto;
    }
    .btn-group {
      display: flex;
      gap: 5px;
    }
  </style>
</body>
</html>