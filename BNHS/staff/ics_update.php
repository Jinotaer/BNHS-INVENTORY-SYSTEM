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
  $ics_no = sanitize($_POST['ics_no']);
  $quantity = (int) $_POST['quantity'];
  $unit = sanitize($_POST['unit']);
  $unit_cost = (float) $_POST['unit_cost'];
  $total_amount = $quantity * $unit_cost;
  $item_description = sanitize($_POST['item_description']);
  $inventory_item_no = sanitize($_POST['inventory_item_no']);
  $estimated_useful_life = sanitize($_POST['estimated_useful_life']);
  $end_user_name = sanitize($_POST['end_user_name']);
  $end_user_position = sanitize($_POST['end_user_position']);
  $end_user_date = sanitize($_POST['end_user_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);

  // Check if we're updating a specific item
  if (isset($_GET['update_item']) && isset($_GET['item_id'])) {
    $ics_id = $_GET['update_item'];
    $item_id = $_GET['item_id'];

    // Start transaction
    $mysqli->begin_transaction();

    try {
      // Update items table
      $stmt = $mysqli->prepare("UPDATE items SET 
        item_description = ?, unit = ?, unit_cost = ?, estimated_useful_life = ?
        WHERE item_id = ?");

      $stmt->bind_param("ssdsi", $item_description, $unit, $unit_cost, $estimated_useful_life, $item_id);

      if (!$stmt->execute()) {
        throw new Exception("Error updating item: " . $stmt->error);
      }

      // Update ics_items
      $stmt = $mysqli->prepare("UPDATE ics_items SET 
        quantity = ?, inventory_item_no = ?
        WHERE ics_id = ? AND item_id = ?");

      $stmt->bind_param("isii", $quantity, $inventory_item_no, $ics_id, $item_id);

      if (!$stmt->execute()) {
        throw new Exception("Error updating ICS items: " . $stmt->error);
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Item Updated Successfully";
      header("refresh:1; url=display_ics.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_ics.php");
    }
  } else {
    // Original ICS update code
    $ics_id = $_GET['update'];

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

      // Update inventory_custodian_slips
      $stmt = $mysqli->prepare("UPDATE inventory_custodian_slips SET 
        entity_id = ?, ics_no = ?, end_user_name = ?, end_user_position = ?, 
        end_user_date = ?, custodian_name = ?, custodian_position = ?, custodian_date = ?
        WHERE ics_id = ?");

      if ($stmt === false) {
        throw new Exception("MySQL prepare failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "isssssssi",
        $entity_id,
        $ics_no,
        $end_user_name,
        $end_user_position,
        $end_user_date,
        $custodian_name,
        $custodian_position,
        $custodian_date,
        $ics_id
      );

      if (!$stmt->execute()) {
        throw new Exception("Error updating ICS: " . $stmt->error);
      }

      // Get all item_ids from ics_items for this ICS
      $stmt = $mysqli->prepare("SELECT item_id FROM ics_items WHERE ics_id = ?");
      $stmt->bind_param("i", $ics_id);
      $stmt->execute();
      $result = $stmt->get_result();

      // Update each item
      while ($row = $result->fetch_object()) {
        $item_id = $row->item_id;

        // Update items table
        $stmt = $mysqli->prepare("UPDATE items SET 
          item_description = ?, unit = ?, unit_cost = ?, estimated_useful_life = ?
          WHERE item_id = ?");

        $stmt->bind_param("ssdsi", $item_description, $unit, $unit_cost, $estimated_useful_life, $item_id);

        if (!$stmt->execute()) {
          throw new Exception("Error updating item: " . $stmt->error);
        }

        // Update ics_items
        $stmt = $mysqli->prepare("UPDATE ics_items SET 
          quantity = ?, inventory_item_no = ?
          WHERE ics_id = ? AND item_id = ?");

        $stmt->bind_param("isii", $quantity, $inventory_item_no, $ics_id, $item_id);

        if (!$stmt->execute()) {
          throw new Exception("Error updating ICS items: " . $stmt->error);
        }
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Inventory Custodian Slip Updated Successfully";
      header("refresh:1; url=display_ics.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_ics.php");
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
      $ics_id = $_GET['update_item'];
      $item_id = $_GET['item_id'];

      $ret = "SELECT 
        ics.*, 
        e.entity_name, 
        e.fund_cluster,
        i.item_description,
        i.unit,
        i.unit_cost,
        i.estimated_useful_life,
        ii.quantity,
        ii.inventory_item_no
      FROM inventory_custodian_slips ics
      JOIN entities e ON ics.entity_id = e.entity_id
      JOIN ics_items ii ON ics.ics_id = ii.ics_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE ics.ics_id = ? AND i.item_id = ?";

      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("ii", $ics_id, $item_id);
    } else {
      $ics_id = $_GET['update'];
      $ret = "SELECT 
        ics.*, 
        e.entity_name, 
        e.fund_cluster,
        i.item_description,
        i.unit,
        i.unit_cost,
        i.estimated_useful_life,
        ii.quantity,
        ii.inventory_item_no
      FROM inventory_custodian_slips ics
      JOIN entities e ON ics.entity_id = e.entity_id
      JOIN ics_items ii ON ics.ics_id = ii.ics_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE ics.ics_id = ?";

      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("i", $ics_id);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    $ics = $res->fetch_object();
    ?>

    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <!-- <div class="card-header border-0">
              <div class="col">
                <h2 class="text-center mb-3 pt-3 text-uppercase">Update Inventory Custodian Slip</h2>
              </div>
            </div> -->
            <div class="card-body">
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" class="border border-light p-4 rounded">

                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Update Inventory Custodian Slip</h2>
                
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo htmlspecialchars($ics->entity_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <input style="color: #000000;" type="text" class="form-control" name="fund_cluster" value="<?php echo htmlspecialchars($ics->fund_cluster); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">ICS No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="ics_no" value="<?php echo htmlspecialchars($ics->ics_no); ?>" readonly>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name" value="<?php echo htmlspecialchars($ics->end_user_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">End User Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_position" value="<?php echo htmlspecialchars($ics->end_user_position); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by User)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="end_user_date" value="<?php echo htmlspecialchars($ics->end_user_date); ?>" readonly>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name" value="<?php echo htmlspecialchars($ics->custodian_name); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Custodian Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position" value="<?php echo htmlspecialchars($ics->custodian_position); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by Custodian)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="custodian_date" value="<?php echo htmlspecialchars($ics->custodian_date); ?>" readonly>
                    </div>
                  </div>

                  <div style="margin-bottom: 20px;"><strong>Edit Item:</strong></div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Inventory Item No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="inventory_item_no" value="<?php echo htmlspecialchars($ics->inventory_item_no); ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Item Description</label>
                      <input style="color: #000000;" type="text" class="form-control" name="item_description" value="<?php echo htmlspecialchars($ics->item_description); ?>" required>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Unit</label>
                      <input style="color: #000000;" type="text" class="form-control" name="unit" value="<?php echo htmlspecialchars($ics->unit); ?>" required>
                    </div>
                    <div class="col-md-2">
                      <label class="form-label">Quantity</label>
                      <input style="color: #000000;" type="number" class="form-control" name="quantity" value="<?php echo htmlspecialchars($ics->quantity); ?>" required>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Unit Cost</label>
                      <input style="color: #000000;" type="number" step="0.01" class="form-control" name="unit_cost" value="<?php echo htmlspecialchars($ics->unit_cost); ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Total Price</label>
                      <input style="color: #000000;" type="number" step="0.01" class="form-control" name="total_price" value="<?php echo htmlspecialchars($ics->quantity * $ics->unit_cost); ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Estimated Useful Life</label>
                      <input style="color: #000000;" type="text" class="form-control" name="estimated_useful_life" value="<?php echo htmlspecialchars($ics->estimated_useful_life); ?>" required>
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

  <!-- Argon Sc style="color: #000000;"ripts -->
  <?php require_once('partials/_scripts.php'); ?>
  
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const qtyInput = document.querySelector('[name="quantity"]');
      const costInput = document.querySelector('[name="unit_cost"]');
      const totalInput = document.querySelector('[name="total_price"]');

      function updateTotal() {
        const qty = parseFloat(qtyInput.value) || 0;
        const cost = parseFloat(costInput.value) || 0;
        totalInput.value = (qty * cost).toFixed(2);
      }

      if (qtyInput && costInput && totalInput) {
        qtyInput.addEventListener("input", updateTotal);
        costInput.addEventListener("input", updateTotal);
        // Initial calculation
        updateTotal();
      }
    });
  </script>
</body>

</html>