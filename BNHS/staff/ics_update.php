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
  $end_user_name = sanitize($_POST['end_user_name']);
  $end_user_position = sanitize($_POST['end_user_position']);
  $end_user_date = sanitize($_POST['end_user_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);

  // Check if we're updating multiple items
  if (isset($_POST['items']) && is_array($_POST['items'])) {
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

      // Update inventory_custodian_slips for all related ICS records with the same ICS number
      $stmt = $mysqli->prepare("UPDATE inventory_custodian_slips SET 
        entity_id = ?, end_user_name = ?, end_user_position = ?, 
        end_user_date = ?, custodian_name = ?, custodian_position = ?, custodian_date = ?
        WHERE ics_no = ?");

      if ($stmt === false) {
        throw new Exception("MySQL prepare failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "issssss",
        $entity_id,
        $end_user_name,
        $end_user_position,
        $end_user_date,
        $custodian_name,
        $custodian_position,
        $custodian_date,
        $ics_no
      );

      if (!$stmt->execute()) {
        throw new Exception("Error updating ICS: " . $stmt->error);
      }

      // Process each item
      foreach ($_POST['items'] as $index => $itemData) {
        $ics_item_id = (int) $itemData['ics_item_id'];
        $item_id = (int) $itemData['item_id'];
        $inventory_item_no = sanitize($itemData['inventory_item_no']);
        $item_description = sanitize($itemData['item_description']);
        $unit = sanitize($itemData['unit']);
        $quantity = (int) $itemData['quantity'];
        $unit_cost = (float) $itemData['unit_cost'];
        $estimated_useful_life = sanitize($itemData['estimated_useful_life']);
        $article = sanitize($itemData['article']);
        $remarks = isset($itemData['remarks']) ? sanitize($itemData['remarks']) : '';

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
          quantity = ?, inventory_item_no = ?, article = ?, remarks = ?
          WHERE ics_item_id = ?");

        $stmt->bind_param("isssi", $quantity, $inventory_item_no, $article, $remarks, $ics_item_id);

        if (!$stmt->execute()) {
          throw new Exception("Error updating ICS items: " . $stmt->error);
        }
      }

      // Commit transaction
      $mysqli->commit();
      $success = "All Items Updated Successfully";
      header("refresh:1; url=display_ics.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      header("refresh:1; url=display_ics.php");
    }
  } else {
    // Original code for single item update
    $quantity = (int) $_POST['quantity'];
    $unit = sanitize($_POST['unit']);
    $unit_cost = (float) $_POST['unit_cost'];
    $total_amount = $quantity * $unit_cost;
    $item_description = sanitize($_POST['item_description']);
    $inventory_item_no = sanitize($_POST['inventory_item_no']);
    $estimated_useful_life = sanitize($_POST['estimated_useful_life']);
    $article = sanitize($_POST['article']);
    $remarks = isset($_POST['remarks']) ? sanitize($_POST['remarks']) : '';

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
          quantity = ?, inventory_item_no = ?, article = ?, remarks = ?
          WHERE ics_id = ? AND item_id = ?");

        $stmt->bind_param("isssii", $quantity, $inventory_item_no, $article, $remarks, $ics_id, $item_id);

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
            quantity = ?, inventory_item_no = ?, article = ?, remarks = ?
            WHERE ics_id = ? AND item_id = ?");

          $stmt->bind_param("isssii", $quantity, $inventory_item_no, $article, $remarks, $ics_id, $item_id);

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
        ii.inventory_item_no,
        ii.article,
        ii.remarks
      FROM inventory_custodian_slips ics
      JOIN entities e ON ics.entity_id = e.entity_id
      JOIN ics_items ii ON ics.ics_id = ii.ics_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE ics.ics_id = ? AND i.item_id = ?";

      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("ii", $ics_id, $item_id);
    } else {
      $ics_id = $_GET['update'];
      
      // First get the ICS number to find all related items
      $getIcsNoQuery = "SELECT ics.ics_no 
        FROM inventory_custodian_slips ics
        WHERE ics.ics_id = ?";
        
      $stmtIcsNo = $mysqli->prepare($getIcsNoQuery);
      $stmtIcsNo->bind_param("i", $ics_id);
      $stmtIcsNo->execute();
      $resultIcsNo = $stmtIcsNo->get_result();
      $icsNoRow = $resultIcsNo->fetch_object();
      $ics_no = $icsNoRow->ics_no;
      
      // Now get all ICS items with this ICS number
      $ret = "SELECT 
        ics.*, 
        e.entity_name, 
        e.fund_cluster,
        i.item_description,
        i.unit,
        i.unit_cost,
        i.estimated_useful_life,
        ii.quantity,
        ii.inventory_item_no,
        ii.article,
        ii.remarks,
        ii.ics_item_id,
        i.item_id
      FROM inventory_custodian_slips ics
      JOIN entities e ON ics.entity_id = e.entity_id
      JOIN ics_items ii ON ics.ics_id = ii.ics_id
      JOIN items i ON ii.item_id = i.item_id
      WHERE ics.ics_no = ?";

      $stmt = $mysqli->prepare($ret);
      $stmt->bind_param("s", $ics_no);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    
    // If there are multiple items, we'll store them in an array
    $icsItems = [];
    while ($item = $res->fetch_object()) {
        $icsItems[] = $item;
    }
    
    // Use the first item for the ICS details
    $ics = $icsItems[0] ?? null;
    
    if (!$ics) {
        echo "No ICS found with the specified ID.";
        exit;
    }
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

                  <div style="margin-bottom: 20px;"><strong>Items with ICS No. <?php echo htmlspecialchars($ics->ics_no); ?>:</strong></div>
                  
                  <?php foreach ($icsItems as $index => $item): ?>
                  <div class="card mb-3">
                    <div class="card-header bg-light">
                      <h5 class="mb-0">Item #<?php echo $index + 1; ?></h5>
                    </div>
                    <div class="card-body">
                      <div class="row mb-3">
                        <div class="col-md-4">
                          <label class="form-label">Inventory Item No.</label>
                          <input style="color: #000000;" type="text" class="form-control" name="items[<?php echo $index; ?>][inventory_item_no]" value="<?php echo htmlspecialchars($item->inventory_item_no); ?>" required>
                          <input type="hidden" name="items[<?php echo $index; ?>][ics_item_id]" value="<?php echo $item->ics_item_id; ?>">
                          <input type="hidden" name="items[<?php echo $index; ?>][item_id]" value="<?php echo $item->item_id; ?>">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Item Description</label>
                          <input style="color: #000000;" type="text" class="form-control" name="items[<?php echo $index; ?>][item_description]" value="<?php echo htmlspecialchars($item->item_description); ?>" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label">Unit</label>
                          <input style="color: #000000;" type="text" class="form-control" name="items[<?php echo $index; ?>][unit]" value="<?php echo htmlspecialchars($item->unit); ?>" required>
                        </div>
                        <div class="col-md-2">
                          <label class="form-label">Quantity</label>
                          <input style="color: #000000;" type="number" class="form-control quantity-input" name="items[<?php echo $index; ?>][quantity]" value="<?php echo htmlspecialchars($item->quantity); ?>" required data-index="<?php echo $index; ?>">
                        </div>
                      </div>
                      
                      <div class="row mb-3">
                        <div class="col-md-4">
                          <label class="form-label">Unit Cost</label>
                          <input style="color: #000000;" type="number" step="0.01" class="form-control unit-cost-input" name="items[<?php echo $index; ?>][unit_cost]" value="<?php echo htmlspecialchars($item->unit_cost); ?>" required data-index="<?php echo $index; ?>">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Total Price</label>
                          <input style="color: #000000;" type="number" step="0.01" class="form-control total-price" name="items[<?php echo $index; ?>][total_price]" value="<?php echo htmlspecialchars($item->quantity * $item->unit_cost); ?>" readonly data-index="<?php echo $index; ?>">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Estimated Useful Life</label>
                          <input style="color: #000000;" type="text" class="form-control" name="items[<?php echo $index; ?>][estimated_useful_life]" value="<?php echo htmlspecialchars($item->estimated_useful_life); ?>" required>
                        </div>
                      </div>
                      
                      <div class="row mb-3">
                        <div class="col-md-6">  
                          <label class="form-label">Article</label>
                          <select class="form-control" name="items[<?php echo $index; ?>][article]" style="color: #000000;">
                            <option value="">Select Article</option>
                            <option value="SEMI- EXPENDABLE SCIENCE AND MATH EQUIPMENT" <?php if (isset($item->article) && $item->article == 'SEMI- EXPENDABLE SCIENCE AND MATH EQUIPMENT') echo 'selected'; ?>>SEMI- EXPENDABLE SCIENCE AND MATH EQUIPMENT</option>
                            <option value="SEMI-EXPENDABLE FURNITURE AND FIXTURES" <?php if (isset($item->article) && $item->article == 'SEMI-EXPENDABLE FURNITURE AND FIXTURES') echo 'selected'; ?>>SEMI-EXPENDABLE FURNITURE AND FIXTURES</option>
                            <option value="SEMI- EXPENDABLE IT EQUIPMENT" <?php if (isset($item->article) && $item->article == 'SEMI- EXPENDABLE IT EQUIPMENT') echo 'selected'; ?>>SEMI- EXPENDABLE IT EQUIPMENT</option>
                            <option value="BOOK,MANUAL,LM" <?php if (isset($item->article) && $item->article == 'BOOK,MANUAL,LM') echo 'selected'; ?>>BOOK,MANUAL,LM</option>
                            <option value="SEMI- EXPENDABLE OFFICE PROPERTY" <?php if (isset($item->article) && $item->article == 'SEMI- EXPENDABLE OFFICE PROPERTY') echo 'selected'; ?>>SEMI- EXPENDABLE OFFICE PROPERTY</option>
                          </select>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Remarks</label>
                          <input type="text" class="form-control" name="items[<?php echo $index; ?>][remarks]" style="color: #000000;" value="<?php echo isset($item->remarks) ? htmlspecialchars($item->remarks) : ''; ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                  
                  <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary">Update All Items</button>
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
      // Update total price calculation for all items
      const quantityInputs = document.querySelectorAll('.quantity-input');
      const costInputs = document.querySelectorAll('.unit-cost-input');
      
      function updateTotal(index) {
        const qtyInput = document.querySelector(`[name="items[${index}][quantity]"]`);
        const costInput = document.querySelector(`[name="items[${index}][unit_cost]"]`);
        const totalInput = document.querySelector(`[name="items[${index}][total_price]"]`);
        
        if (qtyInput && costInput && totalInput) {
          const qty = parseFloat(qtyInput.value) || 0;
          const cost = parseFloat(costInput.value) || 0;
          totalInput.value = (qty * cost).toFixed(2);
        }
      }
      
      // Add event listeners to all quantity and cost inputs
      quantityInputs.forEach(input => {
        const index = input.getAttribute('data-index');
        input.addEventListener('input', () => updateTotal(index));
      });
      
      costInputs.forEach(input => {
        const index = input.getAttribute('data-index');
        input.addEventListener('input', () => updateTotal(index));
      });
      
      // Initialize all totals
      quantityInputs.forEach(input => {
        const index = input.getAttribute('data-index');
        updateTotal(index);
      });
    });
  </script>
</body>

</html>