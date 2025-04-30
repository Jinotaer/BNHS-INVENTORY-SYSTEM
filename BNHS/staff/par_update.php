<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['update'])) {
  // Check if the required par_id is present
  if (!isset($_POST['par_id'])) {
    $err = "Error: Missing PAR ID";
    header("refresh:1; url=display_par.php");
    exit;
  }

  // Get the PAR ID from the form
  $par_id = (int)$_POST['par_id'];

  // Sanitize and collect form data
  function sanitize($data)
  {
    return htmlspecialchars(trim($data));
  }
  
  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $par_no = sanitize($_POST['par_no']);
  $quantity = (int) $_POST['quantity'];
  $unit = sanitize($_POST['unit']);
  $item_description = sanitize($_POST['item_description']);
  $property_number = sanitize($_POST['property_number']);
  $date_acquired = sanitize($_POST['date_acquired']);
  $unit_cost = (float) $_POST['unit_cost'];
  $total_amount = (float) $_POST['total_amount'];
  $end_user_name = sanitize($_POST['end_user_name']); 
  $receiver_position = sanitize($_POST['receiver_position']);
  $receiver_date = sanitize($_POST['receiver_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);
  
  // Get article and remarks values
  $article = isset($_POST['article']) && is_array($_POST['article']) ? sanitize($_POST['article'][0]) : '';
  $remarks = isset($_POST['remarks']) ? sanitize($_POST['remarks']) : '';

  // Check if we're updating a specific item
  if (isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $par_item_id = isset($_POST['par_item_id']) ? (int)$_POST['par_item_id'] : null;

    // Add debug info
    error_log("Processing item update: item_id=$item_id, par_id=$par_id, par_item_id=$par_item_id");

    // Start transaction
    $mysqli->begin_transaction();

    try {
      // Update items table
      $stmt = $mysqli->prepare("UPDATE items SET 
        item_description = ?, unit = ?, unit_cost = ? 
        WHERE item_id = ?");

      $stmt->bind_param("ssdi", $item_description, $unit, $unit_cost, $item_id);

      if (!$stmt->execute()) {
        throw new Exception("Error updating item: " . $stmt->error);
      }

      // Update par_items - If par_item_id is provided, use it in the WHERE clause
      if ($par_item_id) {
        $stmt = $mysqli->prepare("UPDATE par_items SET 
          quantity = ?, property_number = ?, article = ?, remarks = ?
          WHERE par_item_id = ?");

        $stmt->bind_param("isssi", $quantity, $property_number, $article, $remarks, $par_item_id);
        
        error_log("Updating par_item with par_item_id=$par_item_id");
      } else {
        $stmt = $mysqli->prepare("UPDATE par_items SET 
          quantity = ?, property_number = ?, article = ?, remarks = ?
          WHERE par_id = ? AND item_id = ?");

        $stmt->bind_param("isssii", $quantity, $property_number, $article, $remarks, $par_id, $item_id);
        
        error_log("Updating par_item with par_id=$par_id, item_id=$item_id (no par_item_id)");
      }
      
      if (!$stmt->execute()) {
        throw new Exception("Error updating PAR items: " . $stmt->error);
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Item Updated Successfully. Item ID: " . $item_id . ", PAR ID: " . $par_id;
      header("refresh:1; url=display_par.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      error_log("Update error: " . $e->getMessage());
      header("refresh:1; url=display_par.php");
    }
  } else if (isset($_POST['par_id'])) {
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
      
      // Update the PAR header information
      $par_stmt = $mysqli->prepare("UPDATE property_acknowledgment_receipts SET 
        entity_id = ?, par_no = ?, date_acquired = ?, end_user_name = ?, 
        receiver_position = ?, receiver_date = ?, custodian_name = ?, 
        custodian_position = ?, custodian_date = ?
        WHERE par_id = ?");

      if ($par_stmt === false) {
        throw new Exception("MySQL prepare failed for PAR update: " . $mysqli->error);
      }

      $par_stmt->bind_param(
        "issssssssi",
        $entity_id,
        $par_no,
        $date_acquired,
        $end_user_name,
        $receiver_position,
        $receiver_date,
        $custodian_name,
        $custodian_position,
        $custodian_date,
        $par_id
      );
      $par_stmt->execute();
      
      // Get all item_ids from par_items for this PAR
      $stmt = $mysqli->prepare("SELECT item_id FROM par_items WHERE par_id = ?");
      $stmt->bind_param("i", $par_id);
      $stmt->execute();
      $result = $stmt->get_result();
      
      // Update each item
      while ($row = $result->fetch_object()) {
        $item_id = $row->item_id;
        
        // Update item details
        $update_item = $mysqli->prepare("UPDATE items SET item_description = ?, unit = ?, unit_cost = ? WHERE item_id = ?");
        $update_item->bind_param("ssdi", $item_description, $unit, $unit_cost, $item_id);
        $update_item->execute();
        
        // Update par_item
        $update_par_item = $mysqli->prepare("UPDATE par_items SET 
          quantity = ?, property_number = ?, article = ?, remarks = ?
          WHERE par_id = ? AND item_id = ?");
        $update_par_item->bind_param("isssii", $quantity, $property_number, $article, $remarks, $par_id, $item_id);
        $update_par_item->execute();
      }
      
      // Commit transaction
      $mysqli->commit();
      $success = "Property Acknowledgment Receipt Updated Successfully";
      header("refresh:1; url=display_par.php");
    } catch (Exception $e) {
      // Roll back on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_par.php");
    }
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
    
    // Check if we're updating a specific item
    if (isset($_GET['update_item']) && isset($_GET['item_id'])) {
      $par_id = $_GET['update_item'];
      $item_id = $_GET['item_id'];
      $par_item_id = isset($_GET['par_item_id']) ? $_GET['par_item_id'] : null;

      // Add debug logging
      error_log("Loading item update form: par_id=$par_id, item_id=$item_id, par_item_id=$par_item_id");

      $ret = "SELECT p.*, e.entity_name, e.fund_cluster, pi.quantity, pi.property_number, pi.article, pi.remarks, pi.par_item_id, i.item_id, i.item_description, i.unit, i.unit_cost, (pi.quantity * i.unit_cost) as total_amount
            FROM property_acknowledgment_receipts p
            JOIN entities e ON p.entity_id = e.entity_id
            JOIN par_items pi ON p.par_id = pi.par_id
            JOIN items i ON pi.item_id = i.item_id
            WHERE p.par_id = ? AND i.item_id = ?";
      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("ii", $par_id, $item_id);
    } else {
      $update = $_GET['update'];
      $ret = "SELECT p.*, e.entity_name, e.fund_cluster, pi.quantity, pi.property_number, pi.article, pi.remarks, pi.par_item_id, i.item_id, i.item_description, i.unit, i.unit_cost, (pi.quantity * i.unit_cost) as total_amount
            FROM property_acknowledgment_receipts p
            JOIN entities e ON p.entity_id = e.entity_id
            JOIN par_items pi ON p.par_id = pi.par_id
            JOIN items i ON pi.item_id = i.item_id
            WHERE p.par_id = ?";
      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("i", $update);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    while ($par = $res->fetch_object()) {
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

              <div class="card-body">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <?php if (isset($_GET['update_item']) && isset($_GET['item_id'])): ?>
                      <h2 class="text-center mb-4 text-uppercase">Update Single Item</h2>
                    <?php else: ?>
                      <h2 class="text-center mb-4 text-uppercase">Update Property Acknowledgment Receipt</h2>
                    <?php endif; ?>
                    
                    <!-- Entity Info -->
                    <div class="row mt-3 mb-3">
                      <div class="col-md-4">
                        <label>Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->entity_name; ?>" name="entity_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->fund_cluster; ?>" name="fund_cluster" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->par_no; ?>" name="par_no" readonly>
                      </div>
                    </div>

                    <!-- Receiver Section -->
                    <div class="sub-section receiver-section">Receiver</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->end_user_name; ?>" name="end_user_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->receiver_position; ?>" name="receiver_position" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $par->receiver_date; ?>" name="receiver_date" readonly>
                      </div>
                    </div>

                    <!-- Issue Section -->
                    <div class="sub-section issue-section">Issue</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->custodian_name; ?>" name="custodian_name" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->custodian_position; ?>" name="custodian_position" readonly>
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $par->custodian_date; ?>" name="custodian_date" readonly>
                      </div>
                    </div>

                    <!-- Edit Item Section -->
                    <div style="margin-bottom: 20px;"><strong>Edit Item:</strong></div>
                    <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Stock / Property No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->property_number; ?>" name="property_number">
                      </div>
                      <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->item_description; ?>" name="item_description">
                      </div>
                      <div class="col-md-2">
                        <label>Unit</label>
                        <select style="color: #000000;" class="form-control" name="unit">
                          <option value="">Select Unit</option>
                          <option value="box" <?php echo ($par->unit == 'box') ? 'selected' : ''; ?>>box</option>
                          <option value="pieces" <?php echo ($par->unit == 'pieces') ? 'selected' : ''; ?>>pieces</option>
                        </select>
                      </div>
                      <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" value="<?php echo $par->quantity; ?>" name="quantity">
                      </div>
                    
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $par->date_acquired; ?>" name="date_acquired">
                      </div>
                      <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->unit_cost; ?>" name="unit_cost">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                           value="<?php echo $par->total_amount; ?>" name="total_amount" readonly>
                      </div>
                    </div>
                    <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Article</label>
                      <select name="article[]" class="form-control" style="color: #000000;">
                              <option value="">Select Article</option>
                              <option value="BUILDING" <?php echo (isset($par->article) && $par->article == 'BUILDING') ? 'selected' : ''; ?>>BUILDING</option>
                              <option value="LAND" <?php echo (isset($par->article) && $par->article == 'LAND') ? 'selected' : ''; ?>>LAND</option>
                              <option value="IT EQUIPMENT" <?php echo (isset($par->article) && $par->article == 'IT EQUIPMENT') ? 'selected' : ''; ?>>IT EQUIPMENT</option>
                              <option value="SCHOOL BUILDING" <?php echo (isset($par->article) && $par->article == 'SCHOOL BUILDING') ? 'selected' : ''; ?>>SCHOOL BUILDING</option>
                            </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Remarks</label>
                      <input type="text" class="form-control" name="remarks" style="color: #000000;" value="<?php echo isset($par->remarks) ? htmlspecialchars($par->remarks) : ''; ?>">
                    </div>
                  </div>
                    <!-- Hidden inputs to store the IDs -->
                    <?php if (isset($_GET['update_item']) && isset($_GET['item_id'])): ?>
                      <input type="hidden" name="par_id" value="<?php echo $_GET['update_item']; ?>">
                      <input type="hidden" name="item_id" value="<?php echo $_GET['item_id']; ?>">
                      <?php if (isset($_GET['par_item_id'])): ?>
                      <input type="hidden" name="par_item_id" value="<?php echo $_GET['par_item_id']; ?>">
                      <?php elseif (isset($par->par_item_id)): ?>
                      <input type="hidden" name="par_item_id" value="<?php echo $par->par_item_id; ?>">
                      <?php endif; ?>
                    <?php else: ?>
                      <input type="hidden" name="par_id" value="<?php echo $_GET['update']; ?>">
                    <?php endif; ?>

                    <!-- Debug info hidden field (for development purposes) -->
                    <input type="hidden" name="debug_info" value="par_item_id: <?php echo isset($par->par_item_id) ? $par->par_item_id : 'not set'; ?>">

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