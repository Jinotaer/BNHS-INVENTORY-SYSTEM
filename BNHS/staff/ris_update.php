<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debug: Print out the POST data to see what we're getting
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update'])) {
  // Check if the required ris_id is present
  if (!isset($_POST['ris_id'])) {
    $err = "Error: Missing RIS ID";
    header("refresh:1; url=display_ris.php");
    exit;
  }

  // Get the RIS ID from the form
  $ris_id = (int)$_POST['ris_id'];

    // echo "<pre>POST data: ";
    // var_dump($_POST);
    // echo "</pre>";

  // Sanitize and collect form data
  function sanitize($data)
  {
    return htmlspecialchars(trim($data));
  }

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $division = sanitize($_POST['division']);
  $office = sanitize($_POST['office']);
  $responsibility_code = sanitize($_POST['responsibility_code']);
  $ris_no = sanitize($_POST['ris_no']);
  $stock_no = isset($_POST['stock_no']) ? (is_array($_POST['stock_no']) ? $_POST['stock_no'] : array($_POST['stock_no'])) : array();
  $unit = isset($_POST['unit']) ? (is_array($_POST['unit']) ? $_POST['unit'] : array($_POST['unit'])) : array();
  $item_description = isset($_POST['item_description']) ? (is_array($_POST['item_description']) ? $_POST['item_description'] : array($_POST['item_description'])) : array();
  $requested_qty = isset($_POST['requested_qty']) ? (is_array($_POST['requested_qty']) ? $_POST['requested_qty'] : array($_POST['requested_qty'])) : array();
  $stock_available = isset($_POST['stock_available']) ? (is_array($_POST['stock_available']) ? $_POST['stock_available'] : array($_POST['stock_available'])) : array();
  $issued_qty = isset($_POST['issued_qty']) ? (is_array($_POST['issued_qty']) ? $_POST['issued_qty'] : array($_POST['issued_qty'])) : array();
  $remarks = isset($_POST['remarks']) ? (is_array($_POST['remarks']) ? $_POST['remarks'] : array($_POST['remarks'])) : array();
  $item_id = isset($_POST['item_id']) ? (is_array($_POST['item_id']) ? $_POST['item_id'] : array($_POST['item_id'])) : array();
  $ris_item_id = isset($_POST['ris_item_id']) ? (is_array($_POST['ris_item_id']) ? $_POST['ris_item_id'] : array($_POST['ris_item_id'])) : array();
  
  $purpose = sanitize($_POST['purpose']);
  $requested_by_name = sanitize($_POST['requested_by_name']);
  $requested_by_designation =  sanitize($_POST['requested_by_designation']);
  $requested_by_date = sanitize($_POST['requested_by_date']);
  $approved_by_name = sanitize($_POST['approved_by_name']);
  $approved_by_designation = sanitize($_POST['approved_by_designation']);
  $approved_by_date = sanitize($_POST['approved_by_date']);
  $issued_by_name = sanitize($_POST['issued_by_name']);
  $issued_by_designation = sanitize($_POST['issued_by_designation']);
  $issued_by_date = sanitize($_POST['issued_by_date']);
  $received_by_name = sanitize($_POST['received_by_name']);
  $received_by_designation = sanitize($_POST['received_by_designation']);
  $received_by_date = sanitize($_POST['received_by_date']);

  // Start transaction
  $mysqli->begin_transaction();

  try {
    // First get or update entity_id
    $entity_stmt = $mysqli->prepare("SELECT entity_id FROM entities WHERE entity_name = ? AND fund_cluster = ? LIMIT 1");
    $entity_stmt->bind_param("ss", $entity_name, $fund_cluster);
    $entity_stmt->execute();
    $entity_result = $entity_stmt->get_result();

    if ($entity_result->num_rows > 0) {
      $entity_row = $entity_result->fetch_assoc();
      $entity_id = $entity_row['entity_id'];
    } else {
      // Create new entity if not found
      $create_entity = $mysqli->prepare("INSERT INTO entities (entity_name, fund_cluster) VALUES (?, ?)");
      $create_entity->bind_param("ss", $entity_name, $fund_cluster);
      $create_entity->execute();
      $entity_id = $mysqli->insert_id;
    }

    // Update the RIS header
    $ris_stmt = $mysqli->prepare("UPDATE requisition_and_issue_slips SET 
      entity_id = ?, division = ?, office = ?, responsibility_code = ?, ris_no = ?, purpose = ?,
      requested_by_name = ?, requested_by_designation = ?, requested_by_date = ?,
      approved_by_name = ?, approved_by_designation = ?, approved_by_date = ?,
      issued_by_name = ?, issued_by_designation = ?, issued_by_date = ?,
      received_by_name = ?, received_by_designation = ?, received_by_date = ?
      WHERE ris_id = ?");

    if ($ris_stmt === false) {
      throw new Exception("MySQL prepare failed for RIS update: " . $mysqli->error);
    }

    $ris_stmt->bind_param(
      "isssssssssssssssssi",
      $entity_id,
      $division,
      $office,
      $responsibility_code,
      $ris_no,
      $purpose,
      $requested_by_name,
      $requested_by_designation,
      $requested_by_date,
      $approved_by_name,
      $approved_by_designation,
      $approved_by_date,
      $issued_by_name,
      $issued_by_designation,
      $issued_by_date,
      $received_by_name,
      $received_by_designation,
      $received_by_date,
      $ris_id
    );
    $ris_stmt->execute();

    // Update each item
    for ($i = 0; $i < count($item_id); $i++) {
      // Update items table
      $update_item = $mysqli->prepare("UPDATE items SET 
        stock_no = ?, item_description = ?, unit = ? 
        WHERE item_id = ?");
      $update_item->bind_param("sssi", $stock_no[$i], $item_description[$i], $unit[$i], $item_id[$i]);
      $update_item->execute();

      // Update ris_items
      $update_ris_item = $mysqli->prepare("UPDATE ris_items SET 
        requested_qty = ?, stock_available = ?, issued_qty = ?, remarks = ?
        WHERE ris_id = ? AND item_id = ?");
      $update_ris_item->bind_param("isisii", $requested_qty[$i], $stock_available[$i], $issued_qty[$i], $remarks[$i], $ris_id, $item_id[$i]);
      $update_ris_item->execute();
    }

    // Commit transaction
    $mysqli->commit();
    $success = "Requisition and Issue Slip Updated Successfully. RIS ID: " . $ris_id;
    header("refresh:1; url=display_ris.php");
  } catch (Exception $e) {
    // Roll back on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=display_ris.php");
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

    <?php
    if (isset($_GET['update'])) {
      $update = $_GET['update'];
      
      // Get the basic RIS info
      $ris_query = "SELECT 
            r.ris_id, e.entity_name, e.fund_cluster, 
            r.division, r.office, r.responsibility_code, r.ris_no, 
            r.purpose, r.requested_by_name, r.requested_by_designation, r.requested_by_date,
            r.approved_by_name, r.approved_by_designation, r.approved_by_date,
            r.issued_by_name, r.issued_by_designation, r.issued_by_date,
            r.received_by_name, r.received_by_designation, r.received_by_date
            FROM requisition_and_issue_slips r
            JOIN entities e ON r.entity_id = e.entity_id
            WHERE r.ris_id = ?";

      $stmt = $mysqli->prepare($ris_query);
      $stmt->bind_param('i', $update);
      $stmt->execute();
      $ris_result = $stmt->get_result();
      $ris_info = $ris_result->fetch_object();
      
      // Get all items for this RIS
      $items_query = "SELECT 
            i.item_id,
            i.stock_no,
            i.item_description,
            i.unit,
            ri.ris_item_id,
            ri.requested_qty,
            ri.stock_available,
            ri.issued_qty,
            ri.remarks
            FROM ris_items ri
            JOIN items i ON ri.item_id = i.item_id
            WHERE ri.ris_id = ?";
      
      $stmt = $mysqli->prepare($items_query);
      $stmt->bind_param('i', $update);
      $stmt->execute();
      $items_result = $stmt->get_result();
      $ris_items = $items_result->fetch_all(MYSQLI_ASSOC);

      if ($ris_result->num_rows > 0) {
    ?>
      <!-- Header -->
      <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
        class="header  pb-8 pt-5 pt-md-8">
        <span class="mask bg-gradient-dark opacity-8"></span>
      </div>
      <!-- Page content -->
      <div class="container-fluid mt--8">
        <!-- Table -->
        <div class="row">
          <div class="col">
            <div class="card shadow">
              <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase">Update Requisition and Issue Slip</h2>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->entity_name; ?>" name="entity_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->fund_cluster; ?>" name="fund_cluster" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Division</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->division; ?>" name="division" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Office</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->office; ?>" name="office" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Responsibility Center Code</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->responsibility_code; ?>" name="responsibility_code" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">RIS No.</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->ris_no; ?>" name="ris_no" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-12">
                        <label class="form-label">Purpose</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->purpose; ?>" name="purpose" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Requested by: Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->requested_by_name; ?>" name="requested_by_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->requested_by_designation; ?>" name="requested_by_designation" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris_info->requested_by_date; ?>" name="requested_by_date" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Approved by: Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->approved_by_name; ?>" name="approved_by_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->approved_by_designation; ?>" name="approved_by_designation" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris_info->approved_by_date; ?>" name="approved_by_date" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Issued by: Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->issued_by_name; ?>" name="issued_by_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->issued_by_designation; ?>" name="issued_by_designation" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris_info->issued_by_date; ?>" name="issued_by_date" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Received by: Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->received_by_name; ?>" name="received_by_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris_info->received_by_designation; ?>" name="received_by_designation" readonly>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris_info->received_by_date; ?>" name="received_by_date" readonly>
                      </div>
                    </div>

                    <div style="margin-bottom: 20px;"><strong>Edit Items:</strong></div>
                    
                    <?php foreach ($ris_items as $index => $item): ?>
                    <div class="card mb-4">
                      <div class="card-header bg-light">
                        <h5>Item #<?php echo $index + 1; ?></h5>
                      </div>
                      <div class="card-body">
                        <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                        <input type="hidden" name="ris_item_id[]" value="<?php echo $item['ris_item_id']; ?>">
                        
                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label">Stock No.</label>
                            <input type="text" style="color: #000000;" class="form-control" value="<?php echo htmlspecialchars($item['stock_no']); ?>" name="stock_no[]">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input type="text" style="color: #000000;" class="form-control" value="<?php echo htmlspecialchars($item['item_description']); ?>" name="item_description[]">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <select style="color: #000000;" class="form-control" name="unit[]">
                              <option value="">Select Unit</option>
                              <option value="box" <?php echo ($item['unit'] == 'box') ? 'selected' : ''; ?>>box</option>
                              <option value="pieces" <?php echo ($item['unit'] == 'pieces') ? 'selected' : ''; ?>>pieces</option>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label">Requested Qty</label>
                            <input type="number" style="color: #000000;" class="form-control" value="<?php echo $item['requested_qty']; ?>" name="requested_qty[]">
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label">Stock Available</label>
                            <select class="form-control" style="color: #000000;" name="stock_available[]">
                              <option value="Yes" <?php if ($item['stock_available'] == 'Yes') echo 'selected'; ?>>Yes</option>
                              <option value="No" <?php if ($item['stock_available'] == 'No') echo 'selected'; ?>>No</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Issued Quantity</label>
                            <input type="number" style="color: #000000;" class="form-control" value="<?php echo $item['issued_qty']; ?>" name="issued_qty[]">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <select style="color: #000000;" class="form-control" name="remarks[]">
                              <option value="">Select Remarks</option>
                              <option value="Consumable" <?php echo ($item['remarks'] == 'Consumable') ? 'selected' : ''; ?>>Consumable</option>
                              <option value="Non-consumable" <?php echo ($item['remarks'] == 'Non-consumable') ? 'selected' : ''; ?>>Non-consumable</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Hidden input to store the ris_id -->
                    <input type="hidden" name="ris_id" value="<?php echo $_GET['update']; ?>">

                    <div class="text-end mt-3">
                      <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <?php require_once('partials/_mainfooter.php'); ?>
      </div>
    <?php } } ?>
  </div>
  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
</body>

</html>