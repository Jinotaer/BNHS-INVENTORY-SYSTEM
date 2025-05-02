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
  $supplier_name = sanitize($_POST['supplier']);
  $po_no_date = sanitize($_POST['po_no_date']);
  $req_office = sanitize($_POST['req_office']);
  $responsibility_center = sanitize($_POST['responsibility_center']);
  $iar_no = sanitize($_POST['iar_no']);
  $iar_date = sanitize($_POST['iar_date']);
  $invoice_no_date = sanitize($_POST['invoice_no_date']);
  $stock_no = isset($_POST['stock_no']) ? (is_array($_POST['stock_no']) ? $_POST['stock_no'] : array($_POST['stock_no'])) : array();
  $remarks = isset($_POST['remarks']) ? (is_array($_POST['remarks']) ? $_POST['remarks'] : array($_POST['remarks'])) : array();
  $item_description = isset($_POST['item_description']) ? (is_array($_POST['item_description']) ? $_POST['item_description'] : array($_POST['item_description'])) : array();
  $unit = isset($_POST['unit']) ? (is_array($_POST['unit']) ? $_POST['unit'] : array($_POST['unit'])) : array();
  $quantity = isset($_POST['quantity']) ? (is_array($_POST['quantity']) ? $_POST['quantity'] : array($_POST['quantity'])) : array();
  $unit_price = isset($_POST['unit_price']) ? (is_array($_POST['unit_price']) ? $_POST['unit_price'] : array($_POST['unit_price'])) : array();
  $total_price = isset($_POST['total_price']) ? (is_array($_POST['total_price']) ? $_POST['total_price'] : array($_POST['total_price'])) : array();
  $item_id = isset($_POST['item_id']) ? (is_array($_POST['item_id']) ? $_POST['item_id'] : array($_POST['item_id'])) : array();
  $iar_item_id = isset($_POST['iar_item_id']) ? (is_array($_POST['iar_item_id']) ? $_POST['iar_item_id'] : array($_POST['iar_item_id'])) : array();

  $receiver_name = sanitize($_POST['receiver_name']);
  $teacher_id = sanitize($_POST['teacher_id']);
  $position = sanitize($_POST['position']);
  $date_inspected = sanitize($_POST['date_inspected']);
  $inspectors = sanitize($_POST['inspectors']);
  $barangay_councilor = sanitize($_POST['barangay_councilor']);
  $pta_observer = sanitize($_POST['pta_observer']);
  $date_received = sanitize($_POST['date_received']);
  $property_custodian = sanitize($_POST['property_custodian']);

  // Check if we're updating an IAR
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

      // Update each item
      for ($i = 0; $i < count($item_id); $i++) {
        // Calculate total price
        $qty = (int)$quantity[$i];
        $price = (float)$unit_price[$i];
        $total = $qty * $price;

        // Update items table
        $stmt = $mysqli->prepare("UPDATE items SET 
          stock_no = ?, item_description = ?, unit = ?, unit_cost = ?
          WHERE item_id = ?");

        $stmt->bind_param("sssdi", $stock_no[$i], $item_description[$i], $unit[$i], $unit_price[$i], $item_id[$i]);

        if (!$stmt->execute()) {
          throw new Exception("Error updating item: " . $stmt->error);
        }

        // Update iar_items
        $stmt = $mysqli->prepare("UPDATE iar_items SET 
          quantity = ?, unit_price = ?, total_price = ?, remarks = ?
          WHERE iar_item_id = ?");

        $stmt->bind_param("iddsi", $qty, $price, $total, $remarks[$i], $iar_item_id[$i]);

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
    if (isset($_GET['update'])) {
      $update = $_GET['update'];

      // First get the basic IAR info
      $iar_query = "SELECT 
        iar.*, 
        e.entity_name, 
        e.fund_cluster, 
        s.supplier_name as supplier
      FROM inspection_acceptance_reports iar
      JOIN entities e ON iar.entity_id = e.entity_id
      JOIN suppliers s ON iar.supplier_id = s.supplier_id
      WHERE iar.iar_id = ?";

      $stmt = $mysqli->prepare($iar_query);
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $iar_result = $stmt->get_result();
      $iar_info = $iar_result->fetch_object();

      // Then get all items for this IAR
      $items_query = "SELECT 
        i.item_id,
        i.stock_no,
        i.item_description,
        i.unit,
        i.unit_cost as unit_price,
        ii.iar_item_id,
        ii.quantity,
        ii.unit_price as item_unit_price,
        ii.total_price,
        ii.remarks
      FROM iar_items ii
      JOIN items i ON ii.item_id = i.item_id
      WHERE ii.iar_id = ?";

      $stmt = $mysqli->prepare($items_query);
      $stmt->bind_param("i", $update);
      $stmt->execute();
      $items_result = $stmt->get_result();
      $iar_items = $items_result->fetch_all(MYSQLI_ASSOC);
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
                  <h2 class="text-center mb-4 text-uppercase"> Update Inspection and Acceptance Report</h2>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo htmlspecialchars($iar_info->entity_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo htmlspecialchars($iar_info->fund_cluster); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Supplier</label>
                      <input style="color: #000000;" type="text" class="form-control" name="supplier" value="<?php echo htmlspecialchars($iar_info->supplier); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PO No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control" name="po_no_date" value="<?php echo htmlspecialchars($iar_info->po_no_date); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Requisitioning Office/Dept.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="req_office" value="<?php echo htmlspecialchars($iar_info->req_office); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Responsibility Center</label>
                      <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo htmlspecialchars($iar_info->responsibility_center); ?>" readonly>
                    </div>

                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">IAR No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo htmlspecialchars($iar_info->iar_no); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">IAR Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo $iar_info->iar_date; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Invoice No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo htmlspecialchars($iar_info->invoice_no_date); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Receiver Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo htmlspecialchars($iar_info->receiver_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Teacher's ID</label>
                      <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo htmlspecialchars($iar_info->teacher_id); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position</label>
                      <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo htmlspecialchars($iar_info->position); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Date Inspected</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo $iar_info->date_inspected; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Inspection Team (comma separated)</label>
                      <input style="color: #000000;" type="text" class="form-control" name="inspectors" value="<?php echo htmlspecialchars($iar_info->inspectors); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Barangay Councilor</label>
                      <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo htmlspecialchars($iar_info->barangay_councilor); ?>" readonly>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PTA Observer</label>
                      <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo htmlspecialchars($iar_info->pta_observer); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo $iar_info->date_received; ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian</label>
                      <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo htmlspecialchars($iar_info->property_custodian); ?>" readonly>
                    </div>
                  </div>

                  <div style="margin-bottom: 20px;"><strong>Edit Items:</strong></div>

                  <?php foreach ($iar_items as $index => $item): ?>
                    <div class="card mb-4">
                      <div class="card-header bg-light">
                        <h5>Item #<?php echo $index + 1; ?></h5>
                      </div>
                      <div class="card-body">
                        <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                        <input type="hidden" name="iar_item_id[]" value="<?php echo $item['iar_item_id']; ?>">

                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label">Stock / Property No.</label>
                            <input style="color: #000000;" type="text" class="form-control" name="stock_no[]" value="<?php echo htmlspecialchars($item['stock_no']); ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input style="color: #000000;" type="text" class="form-control" name="item_description[]" value="<?php echo htmlspecialchars($item['item_description']); ?>">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <select style="color: #000000;" class="form-control" name="unit[]">
                              <option value="">Select Unit</option>
                              <option value="box" <?php echo ($item['unit'] == 'box') ? 'selected' : ''; ?>>box</option>
                              <option value="pack" <?php echo ($item['unit'] == 'pack') ? 'selected' : ''; ?>>box</option>
                              <option value="pieces" <?php echo ($item['unit'] == 'pieces') ? 'selected' : ''; ?>>pieces</option>
                              <option value="set" <?php echo ($item['unit'] == 'set') ? 'selected' : ''; ?>>set</option>
                            </select>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input style="color: #000000;" type="number" class="form-control quantity" name="quantity[]" value="<?php echo $item['quantity']; ?>">
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-md-4">
                            <label class="form-label">Unit Price</label>
                            <input style="color: #000000;" type="number" step="0.01" class="form-control unit-price" name="unit_price[]" value="<?php echo $item['unit_price']; ?>">
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Total Price</label>
                            <input style="color: #000000;" type="text" class="form-control total-price" name="total_price[]" value="<?php echo number_format($item['total_price'], 2); ?>" readonly>
                          </div>
                          <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <select style="color: #000000;" class="form-control" name="items[<?php echo $index; ?>][remarks]">
                              <option value="">Select Remarks</option>
                              <option value="Consumable" <?php if (isset($item['remarks']) && $item['remarks'] == 'Consumable') echo 'selected'; ?>>Consumable</option>
                              <option value="Non-Consumable" <?php if (isset($item['remarks']) && ($item['remarks'] == 'Non-Consumable' || $item['remarks'] == 'Non-consumable')) echo 'selected'; ?>>Non-Consumable</option>
                              <?php if (isset($item['remarks']) && $item['remarks'] != 'Consumable' && $item['remarks'] != 'Non-Consumable' && $item['remarks'] != 'Non-consumable' && !empty($item['remarks'])): ?>
                              <option value="<?php echo htmlspecialchars($item['remarks']); ?>" selected><?php echo htmlspecialchars($item['remarks']); ?></option>
                              <?php endif; ?>
                            </select>
                          </div>  
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
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