<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

//Delete individual item
if (isset($_GET['delete_item'])) {
  $iar_item_id = $_GET['delete_item'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // First get the item_id from iar_items
    $stmt = $mysqli->prepare("SELECT item_id FROM iar_items WHERE iar_item_id = ?");
    $stmt->bind_param('i', $iar_item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item_id = $result->fetch_object()->item_id;
    
    // Delete from iar_items
    $stmt = $mysqli->prepare("DELETE FROM iar_items WHERE iar_item_id = ?");
    $stmt->bind_param('i', $iar_item_id);
    $stmt->execute();
    
    // Delete from items
    $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Item Deleted Successfully";
    header("refresh:1; url=display_iar.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_iar.php");
  }
}

//Delete IAR
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  
  // Start transaction
  $mysqli->begin_transaction();
  
  try {
    // Get all item_ids from iar_items for this IAR
    $stmt = $mysqli->prepare("SELECT item_id FROM iar_items WHERE iar_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Delete from iar_items first
    $stmt = $mysqli->prepare("DELETE FROM iar_items WHERE iar_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Delete corresponding items
    while ($row = $result->fetch_object()) {
      $stmt = $mysqli->prepare("DELETE FROM items WHERE item_id = ?");
      $stmt->bind_param('i', $row->item_id);
      $stmt->execute();
    }
    
    // Delete from inspection_acceptance_reports
    $stmt = $mysqli->prepare("DELETE FROM inspection_acceptance_reports WHERE iar_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Commit transaction
    $mysqli->commit();
    $success = "Record Deleted Successfully";
    header("refresh:1; url=display_iar.php");
  } catch (Exception $e) {
    // Rollback transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_iar.php");
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
                <h2 class="text-center mb-3 pt-3 text-uppercase">Inspection and Acceptance Report</h2>
              </div>
              <!-- <div class="col text-right">
                <a target="_blank" href="print_iar_files.php" class="btn btn-sm btn-primary">
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
                    <th scope="col">Supplier</th>
                    <th scope="col">PO No. / Date</th>
                    <th scope="col">Requisitioning Office/Dept.</th>
                    <th scope="col">Responsibility Center</th>
                    <th scope="col">IAR No.</th>
                    <th scope="col">IAR Date</th>
                    <th scope="col">Invoice No. / Date</th>
                    <!-- <th scope="col">Stock No.</th>
                    <th scope="col">Remarks</th> -->
                    <!-- <th scope="col">Item Description</th> -->
                    <!-- <th scope="col">Unit</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Unit Price</th>
                    <th scope="col">Total Price</th> -->
                    <th scope="col">Receiver Name</th>
                    <th scope="col">Teacher's ID</th>
                    <th scope="col">Position</th>
                    <th scope="col">Date Inspected</th>
                    <th scope="col">Inspection Team</th>
                    <th scope="col">Barangay Councilor</th>
                    <th scope="col">PTA Observer</th>
                    <th scope="col">Date Received</th>
                    <th scope="col">Property Custodian</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT 
                    iar.iar_id,
                    iar.entity_id,
                    iar.supplier_id,
                    iar.po_no_date,
                    iar.req_office,
                    iar.responsibility_center,
                    iar.iar_no,
                    iar.iar_date,
                    iar.invoice_no_date,
                    iar.receiver_name,
                    iar.teacher_id,
                    iar.position,
                    iar.date_inspected,
                    iar.inspectors,
                    iar.barangay_councilor,
                    iar.pta_observer,
                    iar.date_received,
                    iar.property_custodian,
                    e.entity_name, 
                    e.fund_cluster, 
                    s.supplier_name as supplier
                  FROM inspection_acceptance_reports iar
                  JOIN entities e ON iar.entity_id = e.entity_id
                  JOIN suppliers s ON iar.supplier_id = s.supplier_id
                  GROUP BY iar.iar_no
                  ORDER BY iar.created_at DESC";
                  
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  
                  while ($iar = $res->fetch_object()) {
                    // Get one item from this IAR to display as a sample
                    $item_query = "SELECT 
                      i.stock_no,
                      i.unit,
                      i.item_id,
                      ii.iar_item_id,
                      ii.quantity,
                      ii.unit_price,
                      ii.total_price,
                      ii.remarks
                    FROM iar_items ii
                    JOIN items i ON ii.item_id = i.item_id
                    WHERE ii.iar_id = ?
                    LIMIT 1";
                    
                    $item_stmt = $mysqli->prepare($item_query);
                    $item_stmt->bind_param('i', $iar->iar_id);
                    $item_stmt->execute();
                    $item_res = $item_stmt->get_result();
                    $item = $item_res->fetch_object();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($iar->entity_name); ?></td>
                      <td><?php echo htmlspecialchars($iar->fund_cluster); ?></td>
                      <td><?php echo htmlspecialchars($iar->supplier); ?></td>
                      <td><?php echo htmlspecialchars($iar->po_no_date); ?></td>
                      <td><?php echo htmlspecialchars($iar->req_office); ?></td>
                      <td><?php echo htmlspecialchars($iar->responsibility_center); ?></td>
                      <td><?php echo htmlspecialchars($iar->iar_no); ?></td>
                      <td><?php echo date('M d, Y', strtotime($iar->iar_date)); ?></td>
                      <td><?php echo htmlspecialchars($iar->invoice_no_date); ?></td>
                      <!-- <td><?php echo htmlspecialchars($item->stock_no); ?></td> -->
                      <!-- <td><?php echo htmlspecialchars($item->remarks); ?></td> -->
                      <!-- <td><?php // echo htmlspecialchars($item->item_description); ?></td> -->
                      <!-- <td><?php echo htmlspecialchars($item->unit); ?></td>
                      <td><?php echo number_format($item->quantity); ?></td>
                      <td>₱<?php echo number_format($item->unit_price, 2); ?></td>
                      <td>₱<?php echo number_format($item->total_price, 2); ?></td> -->
                      <td><?php echo htmlspecialchars($iar->receiver_name); ?></td>
                      <td><?php echo htmlspecialchars($iar->teacher_id); ?></td>
                      <td><?php echo htmlspecialchars($iar->position); ?></td>
                      <td><?php echo date('M d, Y', strtotime($iar->date_inspected)); ?></td>
                      <td><?php echo htmlspecialchars($iar->inspectors); ?></td>
                      <td><?php echo htmlspecialchars($iar->barangay_councilor); ?></td>
                      <td><?php echo htmlspecialchars($iar->pta_observer); ?></td>
                      <td><?php echo date('M d, Y', strtotime($iar->date_received)); ?></td>
                      <td><?php echo htmlspecialchars($iar->property_custodian); ?></td>
                      <td>
                        <!-- <a href="display_iar.php?delete=<?php echo $iar->iar_id; ?>">
                          <button class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                            Delete All
                          </button>
                        </a> -->

                        <!-- <a href="iar_update.php?update=<?php echo $iar->iar_id; ?>">
                          <button class="btn btn-sm btn-primary">
                            <i class="fas fa-user-edit"></i>
                            Update
                          </button>
                        </a> -->
                        <a href="print_iar_files.php?iar_id=<?php echo $iar->iar_id; ?>" target="_blank">
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
    /* .table-responsive {
      max-height: 500px;
      overflow-y: auto;
    } */
    .btn-group {
      display: flex;
      gap: 5px;
    }
    tbody tr:hover {
      background-color: #f8f9fa;
    }
    
  </style>
</body>
</html>