<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data)
  {
    return htmlspecialchars(trim($data));
  }

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $par_no = sanitize($_POST['par_no']);
  $date_acquired = sanitize($_POST['date_acquired']);
  $end_user_name = sanitize($_POST['end_user_name']);
  $receiver_position = sanitize($_POST['receiver_position']);
  $receiver_date = sanitize($_POST['receiver_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);

  // For item details (arrays)
  $quantity = isset($_POST['quantity']) ? (is_array($_POST['quantity']) ? $_POST['quantity'] : array($_POST['quantity'])) : array();
  $unit = isset($_POST['unit']) ? (is_array($_POST['unit']) ? $_POST['unit'] : array($_POST['unit'])) : array();
  $item_description = isset($_POST['item_description']) ? (is_array($_POST['item_description']) ? $_POST['item_description'] : array($_POST['item_description'])) : array();
  $property_number = isset($_POST['property_number']) ? (is_array($_POST['property_number']) ? $_POST['property_number'] : array($_POST['property_number'])) : array();
  $unit_cost = isset($_POST['unit_cost']) ? (is_array($_POST['unit_cost']) ? $_POST['unit_cost'] : array($_POST['unit_cost'])) : array();
  $total_cost = isset($_POST['total_cost']) ? (is_array($_POST['total_cost']) ? $_POST['total_cost'] : array($_POST['total_cost'])) : array();
  $article = isset($_POST['article']) ? (is_array($_POST['article']) ? $_POST['article'] : array($_POST['article'])) : array();
  $remarks = isset($_POST['remarks']) ? (is_array($_POST['remarks']) ? $_POST['remarks'] : array($_POST['remarks'])) : array();
  $item_id = isset($_POST['item_id']) ? (is_array($_POST['item_id']) ? $_POST['item_id'] : array($_POST['item_id'])) : array();
  $par_item_id = isset($_POST['par_item_id']) ? (is_array($_POST['par_item_id']) ? $_POST['par_item_id'] : array($_POST['par_item_id'])) : array();

  // Check if we're updating a PAR
  if (isset($_GET['update'])) {
    $update = $_GET['update'];
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
      // First, get or create entity
      $stmt = $mysqli->prepare("SELECT entity_id FROM entities WHERE entity_name = ? AND fund_cluster = ?");
      $stmt->bind_param("ss", $entity_name, $fund_cluster);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result->num_rows > 0) {
        $entity_id = $result->fetch_object()->entity_id;
      } else {
        $stmt = $mysqli->prepare("INSERT INTO entities (entity_name, fund_cluster) VALUES (?, ?)");
        $stmt->bind_param("ss", $entity_name, $fund_cluster);
        $stmt->execute();
        $entity_id = $mysqli->insert_id;
      }

      // Debug the update query parameter types and values
      error_log("Updating PAR with ID: $update");
      error_log("entity_id: $entity_id (type: " . gettype($entity_id) . ")");
      error_log("par_no: $par_no (type: " . gettype($par_no) . ")");
      error_log("date_acquired: $date_acquired (type: " . gettype($date_acquired) . ")");
      error_log("end_user_name: $end_user_name (type: " . gettype($end_user_name) . ")");
      error_log("receiver_position: $receiver_position (type: " . gettype($receiver_position) . ")");
      error_log("receiver_date: $receiver_date (type: " . gettype($receiver_date) . ")");
      error_log("custodian_name: $custodian_name (type: " . gettype($custodian_name) . ")");
      error_log("custodian_position: $custodian_position (type: " . gettype($custodian_position) . ")");
      error_log("custodian_date: $custodian_date (type: " . gettype($custodian_date) . ")");

      // Convert entity_id to integer to ensure proper type
      $entity_id = (int)$entity_id;
      $update = (int)$update;

      // Update PAR header information - simplify the query
      $stmt = $mysqli->prepare("UPDATE property_acknowledgment_receipts SET 
        entity_id = ?,
        par_no = ?,
        date_acquired = ?,
        end_user_name = ?,
        receiver_position = ?,
        receiver_date = ?,
        custodian_name = ?,
        custodian_position = ?,
        custodian_date = ?
        WHERE par_id = ?");

      if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $mysqli->error);
      }

      $result = $stmt->bind_param(
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

      // Execute the statement and check for errors
      if (!$stmt->execute()) {
        throw new Exception("Error updating PAR: " . $stmt->error);
      }

      // Update each item
      for ($i = 0; $i < count($item_id); $i++) {
        // Calculate total price
        $qty = (int)$quantity[$i];
        $price = (float)$unit_cost[$i];
        $total = $qty * $price;
        
        // Ensure proper types for IDs
        $item_id_val = (int)$item_id[$i];
        $par_item_id_val = (int)$par_item_id[$i];
        
        // Update items table
        $item_stmt = $mysqli->prepare("UPDATE items SET 
          item_description = ?,
          unit = ?,
          unit_cost = ?
          WHERE item_id = ?");

        if ($item_stmt === false) {
          throw new Exception("Failed to prepare item statement: " . $mysqli->error);
        }

        $result = $item_stmt->bind_param("ssdi", 
          $item_description[$i], 
          $unit[$i], 
          $unit_cost[$i], 
          $item_id_val
        );
        
        if (!$item_stmt->execute()) {
          throw new Exception("Error updating item: " . $item_stmt->error);
        }

        // Update par_items
        $par_item_stmt = $mysqli->prepare("UPDATE par_items SET 
          quantity = ?,
          property_number = ?,
          article = ?,
          remarks = ?
          WHERE par_item_id = ?");

        if ($par_item_stmt === false) {
          throw new Exception("Failed to prepare par_item statement: " . $mysqli->error);
        }

        $result = $par_item_stmt->bind_param("isssi", 
          $qty, 
          $property_number[$i], 
          $article[$i], 
          $remarks[$i], 
          $par_item_id_val
        );
        
        if (!$par_item_stmt->execute()) {
          throw new Exception("Error updating PAR items: " . $par_item_stmt->error);
        }
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Property Acknowledgment Receipt Updated Successfully";
      header("refresh:1; url=display_par.php");
    } catch (Exception $e) {
      // Rollback transaction on error
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
  <?php require_once('partials/_sidebar.php'); ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>

    <?php
    if (isset($_GET['update'])) {
      $update = $_GET['update'];

      // First get the PAR number from the specified PAR ID
      $par_no_query = "SELECT par_no FROM property_acknowledgment_receipts WHERE par_id = ?";
      $stmt = $mysqli->prepare($par_no_query);
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows == 0) {
        echo "No PAR found with ID: " . $update;
        exit;
      }

      $par_no = $result->fetch_object()->par_no;

      // Get basic PAR info for the form header
      $par_query = "SELECT 
        par.*, 
        e.entity_name, 
        e.fund_cluster
      FROM property_acknowledgment_receipts par
      JOIN entities e ON par.entity_id = e.entity_id
      WHERE par.par_id = ?";

      $stmt = $mysqli->prepare($par_query);
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $par_result = $stmt->get_result();
      $par_info = $par_result->fetch_object();

      // Then get all items for this PAR
      $items_query = "SELECT 
        i.item_id,
        i.item_description,
        i.unit,
        i.unit_cost,
        pi.par_item_id,
        pi.quantity,
        pi.property_number,
        pi.article,
        pi.remarks
      FROM par_items pi
      JOIN items i ON pi.item_id = i.item_id
      WHERE pi.par_id = ?";

      $stmt = $mysqli->prepare($items_query);
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $items_result = $stmt->get_result();
      $par_items = $items_result->fetch_all(MYSQLI_ASSOC);
    }
    ?>

    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-body">

              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Update Property Acknowledgment Receipt</h2>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo htmlspecialchars($par_info->entity_name); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Fund Cluster</label>
                      <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo htmlspecialchars($par_info->fund_cluster); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label class="form-label">PAR Number</label>
                      <input style="color: #000000;" type="text" class="form-control" name="par_no" value="<?php echo htmlspecialchars($par_info->par_no); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Date Acquired</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_acquired" value="<?php echo htmlspecialchars($par_info->date_acquired); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo htmlspecialchars($par_info->end_user_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_position" value="<?php echo htmlspecialchars($par_info->receiver_position); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="receiver_date" value="<?php echo htmlspecialchars($par_info->receiver_date); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo htmlspecialchars($par_info->custodian_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo htmlspecialchars($par_info->custodian_position); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo htmlspecialchars($par_info->custodian_date); ?>" readonly>
                    </div>
                  </div>

                  <div style="margin-bottom: 20px;"><strong>Edit Items:</strong></div>

                  <?php foreach ($par_items as $index => $item): ?>
                    <div class="card mb-4">
                      <div class="card-header bg-light">
                        <h5>Item #<?php echo $index + 1; ?></h5>
                      </div>
                      <div class="card-body">
                        <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                        <input type="hidden" name="par_item_id[]" value="<?php echo $item['par_item_id']; ?>">

                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input style="color: #000000;" type="text" class="form-control" name="item_description[]" value="<?php echo htmlspecialchars($item['item_description']); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Article</label>
                            <select style="color: #000000;" class="form-control" name="article[]">
                              <option value="">Select Article</option>
                              <option value="BUILDING" <?php echo ($item['article'] == 'BUILDING') ? 'selected' : ''; ?>>BUILDING</option>
                              <option value="LAND" <?php echo ($item['article'] == 'LAND') ? 'selected' : ''; ?>>LAND</option>
                              <option value="IT EQUIPMENT" <?php echo ($item['article'] == 'IT EQUIPMENT') ? 'selected' : ''; ?>>IT EQUIPMENT</option>
                              <option value="SCHOOL BUILDING" <?php echo ($item['article'] == 'SCHOOL BUILDING') ? 'selected' : ''; ?>>SCHOOL BUILDING</option>
                              <option value="EQUIPMENT" <?php echo ($item['article'] == 'EQUIPMENT') ? 'selected' : ''; ?>>EQUIPMENT</option>
                              <option value="FURNITURE" <?php echo ($item['article'] == 'FURNITURE') ? 'selected' : ''; ?>>FURNITURE</option>
                            </select>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Property Number</label>
                            <input style="color: #000000;" type="text" class="form-control" name="property_number[]" value="<?php echo htmlspecialchars($item['property_number']); ?>">
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input style="color: #000000;" type="number" class="form-control quantity" name="quantity[]" value="<?php echo $item['quantity']; ?>">
                          </div>
                          <div class="col-md-3">
                            <label class="form-label">Unit</label>
                            <select style="color: #000000;" class="form-control" name="unit[]">
                              <option value="">Select Unit</option>
                              <option value="box" <?php echo ($item['unit'] == 'box') ? 'selected' : ''; ?>>box</option>
                              <option value="pieces" <?php echo ($item['unit'] == 'pieces') ? 'selected' : ''; ?>>pieces</option>
                              <option value="unit" <?php echo ($item['unit'] == 'unit') ? 'selected' : ''; ?>>unit</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label">Unit Cost</label>
                            <input style="color: #000000;" type="number" step="0.01" class="form-control unit-price" name="unit_cost[]" value="<?php echo $item['unit_cost']; ?>">
                          </div>

                          <div class="col-md-3">
                            <label class="form-label">Total Cost</label>
                            <input style="color: #000000;" type="text" class="form-control total-price" name="total_cost[]" value="<?php echo number_format($item['quantity'] * $item['unit_cost'], 2); ?>" readonly>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <label class="form-label">Remarks</label>
                            <input style="color: #000000;" type="text" class="form-control" name="remarks[]" value="<?php echo htmlspecialchars($item['remarks']); ?>">
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>

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
  </div>

  <!-- Argon Scripts -->
  <?php require_once('partials/_scripts.php'); ?>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Add event listeners for all quantity and unit price inputs
      const forms = document.querySelectorAll('form');

      forms.forEach(form => {
        const qtyInputs = form.querySelectorAll('.quantity');
        const priceInputs = form.querySelectorAll('.unit-price');
        const totalInputs = form.querySelectorAll('.total-price');

        // Function to update total price
        function updateTotal(index) {
          const qty = parseFloat(qtyInputs[index].value) || 0;
          const price = parseFloat(priceInputs[index].value) || 0;
          totalInputs[index].value = (qty * price).toFixed(2);
        }

        // Add event listeners to all quantity and price inputs
        qtyInputs.forEach((input, index) => {
          input.addEventListener('input', () => updateTotal(index));
        });

        priceInputs.forEach((input, index) => {
          input.addEventListener('input', () => updateTotal(index));
        });

        // Initialize all totals
        for (let i = 0; i < qtyInputs.length; i++) {
          updateTotal(i);
        }
      });
    });
  </script>
</body>

</html>