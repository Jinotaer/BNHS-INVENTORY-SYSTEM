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
      $update
    );
    $par_stmt->execute();
    
    // Get or update item
    $item_stmt = $mysqli->prepare("SELECT item_id FROM items WHERE item_description = ? AND unit = ? LIMIT 1");
    $item_stmt->bind_param("ss", $item_description, $unit);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    
    if ($item_result->num_rows > 0) {
      $item_row = $item_result->fetch_assoc();
      $item_id = $item_row['item_id'];
      
      // Update item details
      $update_item = $mysqli->prepare("UPDATE items SET unit_cost = ? WHERE item_id = ?");
      $update_item->bind_param("di", $unit_cost, $item_id);
      $update_item->execute();
    } else {
      // Create new item
      $create_item = $mysqli->prepare("INSERT INTO items (item_description, unit, unit_cost) VALUES (?, ?, ?)");
      $create_item->bind_param("ssd", $item_description, $unit, $unit_cost);
      $create_item->execute();
      $item_id = $mysqli->insert_id;
    }
    
    // Check if par_item exists and update it
    $check_item = $mysqli->prepare("SELECT par_item_id FROM par_items WHERE par_id = ? LIMIT 1");
    $check_item->bind_param("i", $update);
    $check_item->execute();
    $check_result = $check_item->get_result();
    
    if ($check_result->num_rows > 0) {
      $item_row = $check_result->fetch_assoc();
      $par_item_id = $item_row['par_item_id'];
      
      // Update par_item
      $update_par_item = $mysqli->prepare("UPDATE par_items SET 
        item_id = ?, quantity = ?, property_number = ?
        WHERE par_item_id = ?");
      $update_par_item->bind_param("iisi", $item_id, $quantity, $property_number, $par_item_id);
      $update_par_item->execute();
    } else {
      // Insert new par_item if somehow missing
      $insert_par_item = $mysqli->prepare("INSERT INTO par_items (par_id, item_id, quantity, property_number)
        VALUES (?, ?, ?, ?)");
      $insert_par_item->bind_param("iiis", $update, $item_id, $quantity, $property_number);
      $insert_par_item->execute();
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
    $ret = "SELECT p.*, e.entity_name, e.fund_cluster, pi.quantity, pi.property_number, i.item_description, i.unit, i.unit_cost, (pi.quantity * i.unit_cost) as total_amount
            FROM property_acknowledgment_receipts p
            JOIN entities e ON p.entity_id = e.entity_id
            JOIN par_items pi ON p.par_id = pi.par_id
            JOIN items i ON pi.item_id = i.item_id
            WHERE p.par_id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param("i", $update);
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

              <div class="card-body ">
                <form method="POST" role="form" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase"> Purchase Acceptance Report</h2>
                    <!-- Entity Info -->
                    <div class="row mt-3 mb-3">
                      <div class="col-md-4">
                        <label>Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-4">
                        <label>Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-4">
                        <label>PAR No.</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->par_no; ?>" name="par_no" required>
                      </div>
                    </div>

                    <!-- Item Info -->
                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label>Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control"  value="<?php echo $par->quantity; ?>" name="quantity">
                      </div>
                      <div class="col-md-2">
                        <label>Unit</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->unit; ?>" name="unit">
                      </div>
                      <div class="col-md-4">
                        <label>Description</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->item_description; ?>" name="item_description">
                      </div>
                      <div class="col-md-4">
                        <label>Property Number</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->property_number; ?>" name="property_number">
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Date Acquired</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->date_acquired; ?>" name="date_acquired">
                      </div>
                      <div class="col-md-4">
                        <label>Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->unit_cost; ?>" name="unit_cost">
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                           value="<?php echo $par->total_amount; ?>" name="total_amount" readonly>
                      </div>
                    </div>

                    <!-- Receiver Section -->
                    <div class="sub-section receiver-section">Receiver</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $par->end_user_name; ?>"  name="end_user_name">
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->receiver_position; ?>" name="receiver_position">
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->receiver_date; ?>" name="receiver_date">
                      </div>
                    </div>

                    <!-- Issue Section -->
                    <div class="sub-section issue-section">Issue</div>
                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label>Property Custodian  Name</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->custodian_name; ?>" name="custodian_name">
                      </div>
                      <div class="col-md-4">
                        <label>Position/Office</label>
                        <input style="color: #000000;" type="text" class="form-control"  value="<?php echo $par->custodian_position; ?>" name="custodian_position">
                      </div>
                      <div class="col-md-4">
                        <label>Date</label>
                        <input style="color: #000000;" type="date" class="form-control"  value="<?php echo $par->custodian_date; ?>" name="custodian_date">
                      </div>
                    </div>

                    <div class="text-end mt-3">
                      <button type="submit" class="btn btn-primary">Submit</button>
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