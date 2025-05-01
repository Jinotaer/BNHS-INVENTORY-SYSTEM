<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data) {
    return htmlspecialchars(trim($data));
  }

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $supplier_name = sanitize($_POST['supplier']);
  $po_no_date = sanitize($_POST['po_no_date']);
  $req_office = sanitize($_POST['req_office']);
  $responsibility_center = sanitize($_POST['responsibility_center']);
  $iar_no = sanitize($_POST['iar_no']);
  $iar_date = sanitize($_POST['iar_date']);
  $invoice_no_date = sanitize($_POST['invoice_no_date']);
  $stock_no = sanitize($_POST['stock_no']);
  $remarks = sanitize($_POST['remarks']);
  $item_description = sanitize($_POST['item_description']);
  $unit = sanitize($_POST['unit']);
  $quantity = (int) $_POST['quantity'];
  $unit_price = (float) $_POST['unit_price'];
  $total_price = $quantity * $unit_price;
  $receiver_name = sanitize($_POST['receiver_name']);
  $teacher_id = sanitize($_POST['teacher_id']);
  $position = sanitize($_POST['position']);
  $date_inspected = sanitize($_POST['date_inspected']);
  $inspectors = sanitize($_POST['inspectors']);
  $barangay_councilor = sanitize($_POST['barangay_councilor']);
  $pta_observer = sanitize($_POST['pta_observer']);
  $date_received = sanitize($_POST['date_received']);
  $property_custodian = sanitize($_POST['property_custodian']);
  // $update = $_GET['update']; // Get the IAR ID from the URL parameter
  
  // Check if we're updating a specific item
  if (isset($_GET['update_item']) && isset($_GET['item_id'])) {
    $iar_id = $_GET['update_item'];
    $item_id = $_GET['item_id'];
    $iar_item_id = isset($_GET['iar_item_id']) ? $_GET['iar_item_id'] : null;
    
    // Start transaction
    $mysqli->begin_transaction();
    
    try {
      // Update items table
      $stmt = $mysqli->prepare("UPDATE items SET 
        stock_no = ?, item_description = ?, unit = ?, unit_cost = ?
        WHERE item_id = ?");
      
      $stmt->bind_param("sssdi", $stock_no, $item_description, $unit, $unit_price, $item_id);
      
      if (!$stmt->execute()) {
        throw new Exception("Error updating item: " . $stmt->error);
      }
      
      // Update iar_items
      if ($iar_item_id) {
        $stmt = $mysqli->prepare("UPDATE iar_items SET 
          quantity = ?, unit_price = ?, total_price = ?, remarks = ?
          WHERE iar_item_id = ?");
        
        $stmt->bind_param("iddsi", $quantity, $unit_price, $total_price, $remarks, $iar_item_id);
      } else {
        // Fallback to get the iar_item_id if not provided
        $stmt = $mysqli->prepare("SELECT iar_item_id FROM iar_items WHERE iar_id = ? AND item_id = ?");
        $stmt->bind_param("ii", $iar_id, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
          $iar_item_id = $result->fetch_object()->iar_item_id;
          
          // Update iar_items
          $stmt = $mysqli->prepare("UPDATE iar_items SET 
            quantity = ?, unit_price = ?, total_price = ?, remarks = ?
            WHERE iar_item_id = ?");
          
          $stmt->bind_param("iddsi", $quantity, $unit_price, $total_price, $remarks, $iar_item_id);
        } else {
          throw new Exception("IAR item not found");
        }
      }
      
      if (!$stmt->execute()) {
        throw new Exception("Error updating IAR items: " . $stmt->error);
      }
      
      // Commit transaction
      $mysqli->commit();
      $success = "Item Updated Successfully";
      header("refresh:1; url=display_iar.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_iar.php");
    }
  } else {
    // Original IAR update code
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

      // Get or create supplier
      $stmt = $mysqli->prepare("SELECT supplier_id FROM suppliers WHERE supplier_name = ?");
      $stmt->bind_param("s", $supplier_name);
      $stmt->execute();
      $result = $stmt->get_result();
      
      if ($result->num_rows > 0) {
        $supplier_id = $result->fetch_object()->supplier_id;
      } else {
        $stmt = $mysqli->prepare("INSERT INTO suppliers (supplier_name) VALUES (?)");
        $stmt->bind_param("s", $supplier_name);
        $stmt->execute();
        $supplier_id = $mysqli->insert_id;
      }

      // Update inspection_acceptance_reports
      $stmt = $mysqli->prepare("UPDATE inspection_acceptance_reports SET 
        entity_id = ?, supplier_id = ?, iar_no = ?, po_no_date = ?, req_office = ?, 
        responsibility_center = ?, iar_date = ?, invoice_no_date = ?, 
        receiver_name = ?, teacher_id = ?, position = ?, date_inspected = ?, 
        inspectors = ?, barangay_councilor = ?, pta_observer = ?, date_received = ?, 
        property_custodian = ?
        WHERE iar_id = ?");

      if ($stmt === false) {
        throw new Exception("MySQL prepare failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "iisssssssssssssssi",
        $entity_id, 
        $supplier_id, 
        $iar_no, 
        $po_no_date, 
        $req_office, 
        $responsibility_center, 
        $iar_date, 
        $invoice_no_date, 
        $receiver_name, 
        $teacher_id, 
        $position, 
        $date_inspected, 
        $inspectors, 
        $barangay_councilor, 
        $pta_observer, 
        $date_received, 
        $property_custodian,
        $update
      );

      if (!$stmt->execute()) {
        throw new Exception("Error updating IAR: " . $stmt->error);
      }

      // Get all item_ids from iar_items for this IAR
      $stmt = $mysqli->prepare("SELECT item_id FROM iar_items WHERE iar_id = ?");
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $result = $stmt->get_result();
      
      // Update each item
      while ($row = $result->fetch_object()) {
        $item_id = $row->item_id;
        
        // Update items table
        $stmt = $mysqli->prepare("UPDATE items SET 
          stock_no = ?, item_description = ?, unit = ?, unit_cost = ?
          WHERE item_id = ?");

        $stmt->bind_param("sssdi", $stock_no, $item_description, $unit, $unit_price, $item_id);
        
        if (!$stmt->execute()) {
          throw new Exception("Error updating item: " . $stmt->error);
        }

        // Update iar_items
        $stmt = $mysqli->prepare("UPDATE iar_items SET 
          quantity = ?, unit_price = ?, total_price = ?, remarks = ?
          WHERE iar_id = ? AND item_id = ?");

        $stmt->bind_param("iddsii", $quantity, $unit_price, $total_price, $remarks, $update, $item_id);
        
        if (!$stmt->execute()) {
          throw new Exception("Error updating IAR items: " . $stmt->error);
        }
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Inspection and Acceptance Report Updated Successfully";
      header("refresh:1; url=display_iar.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_iar.php");
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
    // Check if we're updating a specific item
    if (isset($_GET['update_item']) && isset($_GET['item_id'])) {
      $iar_id = $_GET['update_item'];
      $item_id = $_GET['item_id'];
      
      $ret = "SELECT 
        iar.*, 
        e.entity_name, 
        e.fund_cluster, 
        s.supplier_name as supplier,
        i.stock_no,
        i.item_description,
        i.unit,
        i.unit_cost as unit_price,
        ii.quantity,
        ii.total_price,
        ii.remarks,
        ii.iar_item_id
      FROM inspection_acceptance_reports iar
      JOIN entities e ON iar.entity_id = e.entity_id
      JOIN suppliers s ON iar.supplier_id = s.supplier_id
      JOIN iar_items ii ON iar.iar_id = ii.iar_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE iar.iar_id = ? AND i.item_id = ?";
      
      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("ii", $iar_id, $item_id);
    } else {
      $update = $_GET['update'];
      $ret = "SELECT 
        iar.*, 
        e.entity_name, 
        e.fund_cluster, 
        s.supplier_name as supplier,
        i.stock_no,
        i.item_description,
        i.unit,
        i.unit_cost as unit_price,
        ii.quantity,
        ii.total_price,
        ii.remarks,
        ii.iar_item_id
      FROM inspection_acceptance_reports iar
      JOIN entities e ON iar.entity_id = e.entity_id
      JOIN suppliers s ON iar.supplier_id = s.supplier_id
      JOIN iar_items ii ON iar.iar_id = ii.iar_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE iar.iar_id = ?";
      
      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("i", $update);
    }
    
    $stmt->execute();
    $res = $stmt->get_result();
    $iar = $res->fetch_object();
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
                  <h2 class="text-center mb-4 text-uppercase"> Update Inspection and Acceptance Report</h2>
                  
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo htmlspecialchars($iar->entity_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo htmlspecialchars($iar->fund_cluster); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Supplier</label>
                      <input style="color: #000000;" type="text" class="form-control" name="supplier" value="<?php echo htmlspecialchars($iar->supplier); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PO No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control" name="po_no_date" value="<?php echo htmlspecialchars($iar->po_no_date); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Requisitioning Office/Dept.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="req_office" value="<?php echo htmlspecialchars($iar->req_office); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Responsibility Center</label>
                      <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo htmlspecialchars($iar->responsibility_center); ?>" readonly>
                    </div>
                    
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">IAR No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo htmlspecialchars($iar->iar_no); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">IAR Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo $iar->iar_date; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Invoice No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo htmlspecialchars($iar->invoice_no_date); ?>" readonly>
                    </div>
                  </div>

                

                

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Receiver Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo htmlspecialchars($iar->receiver_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Teacher's ID</label>
                      <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo htmlspecialchars($iar->teacher_id); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position</label>
                      <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo htmlspecialchars($iar->position); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Date Inspected</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo $iar->date_inspected; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Inspection Team (comma separated)</label>
                      <input style="color: #000000;" type="text" class="form-control" name="inspectors" value="<?php echo htmlspecialchars($iar->inspectors); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Barangay Councilor</label>
                      <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo htmlspecialchars($iar->barangay_councilor); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PTA Observer</label>
                      <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo htmlspecialchars($iar->pta_observer); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo $iar->date_received; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian</label>
                      <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo htmlspecialchars($iar->property_custodian); ?>" readonly>
                    </div>
                  </div>

                  <div style="margin-bottom: 20px;"><strong>Edit Item:</strong></div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Stock / Property No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="stock_no" value="<?php echo htmlspecialchars($iar->stock_no); ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Item Description</label>
                      <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo htmlspecialchars($iar->item_description); ?>">
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Unit</label>
                      <select style="color: #000000;" class="form-control" name="unit">
                        <option value="">Select Unit</option>
                        <option value="box" <?php echo ($iar->unit == 'box') ? 'selected' : ''; ?>>box</option>
                        <option value="pieces" <?php echo ($iar->unit == 'pieces') ? 'selected' : ''; ?>>pieces</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Qty</label>
                      <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo $iar->quantity; ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Unit Price</label>
                      <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_price" value="<?php echo $iar->unit_price; ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Total Price</label>
                      <input style="color: #000000;" type="text" class="form-control" name="total_price" value="<?php echo number_format($iar->total_price, 2); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Remarks</label>
                      <select style="color: #000000;" class="form-control" name="remarks">
                        <option value="">Select Remarks</option>
                        <option value="Consumable" <?php echo ($iar->remarks == 'Consumable') ? 'selected' : ''; ?>>Consumable</option>
                        <option value="Non-consumable" <?php echo ($iar->remarks == 'Non-consumable') ? 'selected' : ''; ?>>Non-consumable</option>
                      </select>
                    </div>
                  </div>

                  <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
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
      const qtyInput = document.querySelector('[name="quantity"]');
      const priceInput = document.querySelector('[name="unit_price"]');
      const totalInput = document.querySelector('[name="total_price"]');

      function updateTotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalInput.value = (qty * price).toFixed(2);
      }

      if (qtyInput && priceInput && totalInput) {
        qtyInput.addEventListener("input", updateTotal);
        priceInput.addEventListener("input", updateTotal);
        // Initial calculation
        updateTotal();
      }
    });
  </script>
</body>
</html>