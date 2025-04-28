<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
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
  $stock_no = sanitize($_POST['stock_no']);
  $unit = sanitize($_POST['unit']);
  $item_description = sanitize($_POST['item_description']);
  $requested_qty = (int)sanitize($_POST['requested_qty']);
  $stock_available = sanitize($_POST['stock_available']);
  $issued_qty = (int)sanitize($_POST['issued_qty']);
  $remarks = sanitize($_POST['remarks']);
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
  $received_by_name= sanitize($_POST['received_by_name']);
  $received_by_designation = sanitize($_POST['received_by_designation']);
  $received_by_date = sanitize($_POST['received_by_date']);
  $update = $_GET['update'];
  
  // Begin transaction
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
      $entity_id, $division, $office, $responsibility_code, $ris_no, $purpose,
      $requested_by_name, $requested_by_designation, $requested_by_date,
      $approved_by_name, $approved_by_designation, $approved_by_date,
      $issued_by_name, $issued_by_designation, $issued_by_date,
      $received_by_name, $received_by_designation, $received_by_date,
      $update
    );
    $ris_stmt->execute();
    
    // Get or update item
    $item_stmt = $mysqli->prepare("SELECT item_id FROM items WHERE stock_no = ? LIMIT 1");
    $item_stmt->bind_param("s", $stock_no);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    
    if ($item_result->num_rows > 0) {
      $item_row = $item_result->fetch_assoc();
      $item_id = $item_row['item_id'];
      
      // Update item details
      $update_item = $mysqli->prepare("UPDATE items SET item_description = ?, unit = ? WHERE item_id = ?");
      $update_item->bind_param("ssi", $item_description, $unit, $item_id);
      $update_item->execute();
    } else {
      // Create new item
      $create_item = $mysqli->prepare("INSERT INTO items (stock_no, item_description, unit, unit_cost) VALUES (?, ?, ?, 0)");
      $create_item->bind_param("sss", $stock_no, $item_description, $unit);
      $create_item->execute();
      $item_id = $mysqli->insert_id;
    }
    
    // Check if ris_item exists and update it
    $check_item = $mysqli->prepare("SELECT ris_item_id FROM ris_items WHERE ris_id = ? LIMIT 1");
    $check_item->bind_param("i", $update);
    $check_item->execute();
    $check_result = $check_item->get_result();
    
    if ($check_result->num_rows > 0) {
      $item_row = $check_result->fetch_assoc();
      $ris_item_id = $item_row['ris_item_id'];
      
      // Update ris_item - use string directly in bind_param
      $update_ris_item = $mysqli->prepare("UPDATE ris_items SET 
        item_id = ?, requested_qty = ?, stock_available = ?, issued_qty = ?, remarks = ?
        WHERE ris_item_id = ?");
      $update_ris_item->bind_param("iisiss", $item_id, $requested_qty, $stock_available, $issued_qty, $remarks, $ris_item_id);
      $update_ris_item->execute();
    } else {
      // Insert new ris_item if somehow missing - use string directly in bind_param
      $insert_ris_item = $mysqli->prepare("INSERT INTO ris_items (ris_id, item_id, requested_qty, stock_available, issued_qty, remarks)
        VALUES (?, ?, ?, ?, ?, ?)");
      $insert_ris_item->bind_param("iiisis", $update, $item_id, $requested_qty, $stock_available, $issued_qty, $remarks);
      $insert_ris_item->execute();
    }
    
    // Commit transaction
    $mysqli->commit();
    $success = "Requisition and Issue Slip Updated Successfully";
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
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    $update = $_GET['update'];
    $ret = "SELECT r.ris_id, e.entity_name, e.fund_cluster, 
            r.division, r.office, r.responsibility_code, r.ris_no, 
            i.stock_no, i.unit, i.item_description,
            ri.requested_qty, ri.stock_available, ri.issued_qty, ri.remarks,
            r.purpose, r.requested_by_name, r.requested_by_designation, r.requested_by_date,
            r.approved_by_name, r.approved_by_designation, r.approved_by_date,
            r.issued_by_name, r.issued_by_designation, r.issued_by_date,
            r.received_by_name, r.received_by_designation, r.received_by_date
            FROM requisition_and_issue_slips r
            JOIN entities e ON r.entity_id = e.entity_id
            JOIN ris_items ri ON r.ris_id = ri.ris_id
            JOIN items i ON ri.item_id = i.item_id
            WHERE r.ris_id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('i', $update);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($ris = $res->fetch_object()) {
      // Convert numeric stock_available to Yes/No for select dropdown
      $stock_available_text = ($ris->stock_available == 1) ? 'Yes' : 'No';
      ?>
      <!-- Header -->
      <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
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

              <div class="card-body ">
                <form method="POST" role="form" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>

                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Entity Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Fund Cluster</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Division</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->division; ?>" name="division" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Office</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->office; ?>" name="office" required>
                      </div>

                    </div>

                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Responsibility Center Code</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->responsibility_code; ?>" name="responsibility_code" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">RIS No.</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->ris_no; ?>" name="ris_no" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Stock No.</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->stock_no; ?>" name="stock_no" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Unit</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->unit; ?>" name="unit" required>
                      </div>

                    </div>

                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Item Description</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->item_description; ?>" name="item_description" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Requested Quantity</label>
                        <input type="number" style="color: #000000;" class="form-control" value="<?php echo $ris->requested_qty; ?>" name="requested_qty" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Stock Available (Yes/No)</label>
                        <select style="color: #000000;" class="form-control" name="stock_available" required>
                          <option value="Yes" <?php echo ($stock_available_text == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                          <option value="No" <?php echo ($stock_available_text == 'No') ? 'selected' : ''; ?>>No</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Issued Quantity</label>
                        <input type="number" style="color: #000000;" class="form-control" value="<?php echo $ris->issued_qty; ?>" name="issued_qty" required>
                      </div>

                    </div>

                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->remarks; ?>" name="remarks">
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Purpose</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->purpose; ?>" name="purpose" required>
                      </div>
                    </div>

                    <h5 class="mt-4">Requested By</h5>
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->requested_by_name; ?>" name="requested_by_name" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->requested_by_designation; ?>" name="requested_by_designation" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris->requested_by_date; ?>" name="requested_by_date" required>
                      </div>
                    </div>

                    <h5>Approved By</h5>
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->approved_by_name; ?>" name="approved_by_name" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->approved_by_designation; ?>" name="approved_by_designation" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris->approved_by_date; ?>" name="approved_by_date" required>
                      </div>
                    </div>

                    <h5>Issued By</h5>
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->issued_by_name; ?>" name="issued_by_name" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->issued_by_designation; ?>" name="issued_by_designation" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris->issued_by_date; ?>" name="issued_by_date" required>
                      </div>
                    </div>

                    <h5>Received By</h5>
                    <div class="row mb-3">
                      <div class="col-md-3">
                        <label class="form-label">Name</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->received_by_name; ?>" name="received_by_name" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Designation</label>
                        <input type="text" style="color: #000000;" class="form-control" value="<?php echo $ris->received_by_designation; ?>" name="received_by_designation" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" style="color: #000000;" class="form-control" value="<?php echo $ris->received_by_date; ?>" name="received_by_date" required>
                      </div>
                    </div>

                    <div class="text-end mt-4">
                      <button type="submit" class="btn btn-primary" name="updateris" value="Update ris">Submit</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <?php
        require_once('partials/_mainfooter.php');
        ?>
      </div>
    <?php } ?>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>