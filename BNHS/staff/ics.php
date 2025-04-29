<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Initialize all form variables with default values
$entity_name = '';
$fund_cluster = '';
$ics_no = '';
$end_user_name = '';
$end_user_position = '';
$date_received_user = '';
$custodian_name = '';
$custodian_position = '';
$date_received_custodian = '';

// Initialize item arrays with at least one empty element
$inventory_item_nos = [''];
$item_descriptions = [''];
$units = [''];
$quantities = [''];
$unit_prices = [''];
$total_prices = [''];
$estimated_life = [''];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data)
  {
    return htmlspecialchars(trim($data));
  }

  // Update variables with submitted values
  $entity_name = isset($_POST['entity_name']) ? sanitize($_POST['entity_name']) : '';
  $fund_cluster = isset($_POST['fund_cluster']) ? sanitize($_POST['fund_cluster']) : '';
  $ics_no = isset($_POST['ics_no']) ? sanitize($_POST['ics_no']) : '';
  $end_user_name = isset($_POST['end_user_name']) ? sanitize($_POST['end_user_name']) : '';
  $end_user_position = isset($_POST['end_user_position']) ? sanitize($_POST['end_user_position']) : '';
  $date_received_user = isset($_POST['date_received_user']) ? sanitize($_POST['date_received_user']) : '';
  $custodian_name = isset($_POST['custodian_name']) ? sanitize($_POST['custodian_name']) : '';
  $custodian_position = isset($_POST['custodian_position']) ? sanitize($_POST['custodian_position']) : '';
  $date_received_custodian = isset($_POST['date_received_custodian']) ? sanitize($_POST['date_received_custodian']) : '';

  // Update item arrays with submitted values
  $inventory_item_nos = isset($_POST['inventory_item_no']) ? $_POST['inventory_item_no'] : [''];
  $item_descriptions = isset($_POST['item_description']) ? $_POST['item_description'] : [''];
  $units = isset($_POST['unit']) ? $_POST['unit'] : [''];
  $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [''];
  $unit_prices = isset($_POST['unit_price']) ? $_POST['unit_price'] : [''];
  $total_prices = isset($_POST['total_price']) ? $_POST['total_price'] : [''];
  $estimated_life = isset($_POST['estimated_life']) ? $_POST['estimated_life'] : [''];

  // First, get or create entity
  $stmt = $mysqli->prepare("SELECT entity_id FROM entities WHERE entity_name = ? AND fund_cluster = ?");
  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }
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

  $stmt = $mysqli->prepare("INSERT INTO inventory_custodian_slips (
    entity_id, ics_no, end_user_name, end_user_position, end_user_date,
    custodian_name, custodian_position, custodian_date
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "isssssss",
    $entity_id,
    $ics_no,
    $end_user_name,
    $end_user_position,
    $date_received_user,
    $custodian_name,
    $custodian_position,
    $date_received_custodian
  );

  if ($stmt->execute()) {
    $ics_id = $stmt->insert_id;

    // Process all items
    $item_count = count($_POST['item_description']);

    for ($i = 0; $i < $item_count; $i++) {
      if (empty($_POST['item_description'][$i])) continue; // Skip empty entries

      $item_description = sanitize($_POST['item_description'][$i]);
      $inventory_item_no = sanitize($_POST['inventory_item_no'][$i]);
      $unit = sanitize($_POST['unit'][$i]);
      $quantity = (int) sanitize($_POST['quantity'][$i]);
      $unit_cost = (float) sanitize($_POST['unit_price'][$i]);
      $estimated_life = sanitize($_POST['estimated_life'][$i]);

      // Get or create item
      $stmt_item = $mysqli->prepare("SELECT item_id FROM items WHERE item_description = ? AND unit = ? AND unit_cost = ?");
      $stmt_item->bind_param("ssd", $item_description, $unit, $unit_cost);
      $stmt_item->execute();
      $result_item = $stmt_item->get_result();

      if ($result_item->num_rows > 0) {
        $item_id = $result_item->fetch_object()->item_id;
      } else {
        $stmt_item = $mysqli->prepare("INSERT INTO items (item_description, unit, unit_cost, estimated_useful_life) VALUES (?, ?, ?, ?)");
        $stmt_item->bind_param("ssdi", $item_description, $unit, $unit_cost, $estimated_life);
        $stmt_item->execute();
        $item_id = $mysqli->insert_id;
      }

      // Insert into ics_items table
      $stmt_ics_items = $mysqli->prepare("INSERT INTO ics_items (
        ics_id, item_id, quantity, inventory_item_no
      ) VALUES (?, ?, ?, ?)");

      if ($stmt_ics_items === false) {
        die("MySQL prepare failed: " . $mysqli->error);
      }

      $stmt_ics_items->bind_param("iiis", $ics_id, $item_id, $quantity, $inventory_item_no);

      if (!$stmt_ics_items->execute()) {
        $err = "Error creating item details: " . $stmt_ics_items->error;
        // Don't redirect on error
        break;
      }
    }

     // Commit transaction
    $mysqli->commit();
    $success = "Inventory Custodian Slip Created Successfully";
    header("refresh:1; url=ics.php");
   
  } else {
    $err = "Error: " . $stmt->error;
  }

  $stmt->close();
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
    ?>
    <style>
      /* Custom styles for inventory management tabs */
      .custom-tabs {
        margin-bottom: 15px;
      }

      .custom-tab-link {
        font-weight: bold;
        color: #495057;
        padding: 15px 30px;
        transition: color 0.3s, background-color 0.3s;
      }

      .custom-tab-link:hover {
        color: #0056b3;
        background-color: #f8f9fa;
      }
    </style>
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
      class="header pb-8 pt-5 pt-md-8">
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
            <!-- <?php if (isset($success)) { ?>
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            <?php } ?>
            <?php if (isset($err)) { ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $err; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            <?php } ?> -->
            <div class="dropdown" style="padding: 20px; margin: 10px;">
              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" style="width: 300px; height: 45px;">
                Inventory Custodian Slip
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
                <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
                <li><a class="dropdown-item" href="par.php">Purchase Acceptance Report</a></li>
              </ul>
            </div>

            <style>
              .form-section {
                margin-top: 20px;
                margin-bottom: 30px;
              }

              .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
              }

              .items-table th {
                background-color: #5e72e4;
                color: white;
                padding: 12px 10px;
                text-align: center;
                border: 1px solid #4e61c8;
                font-weight: 600;
                font-size: 14px;
              }

              .items-table td {
                padding: 10px 8px;
                border: 1px solid #dee2e6;
                vertical-align: middle;
              }

              .items-table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
              }

              .items-table tbody tr:hover {
                background-color: #f0f2f5;
              }

              .underline-input {
                width: 100%;
                padding: 8px;
                border: none;
                border-bottom: 1px solid #ced4da;
                background-color: transparent;
                transition: all 0.2s ease-in-out;
              }

              .underline-input:focus {
                outline: none;
                border-bottom: 2px solid #5e72e4;
                box-shadow: 0 4px 6px -1px rgba(94, 114, 228, 0.1);
              }

              .btn-add-row {
                background-color: #2dce89;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 5px;
                margin-top: 10px;
                cursor: pointer;
                transition: background-color 0.2s;
              }

              .btn-add-row:hover {
                background-color: #26af74;
              }

              .btn-danger {
                padding: 5px 10px;
                font-size: 12px;
              }

              input[readonly] {
                background-color: #f8f9fa;
              }

              .is-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, .25);
              }

              .invalid-feedback {
                color: #dc3545;
                font-size: 0.875em;
                margin-top: 0.25rem;
              }
            </style>

            <div class="card-body ">
              <form method="POST" role="form" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control<?php if (isset($errors['entity_name'])) echo ' is-invalid'; ?>" name="entity_name" required value="<?php echo isset($entity_name) ? htmlspecialchars($entity_name) : ''; ?>">
                      <?php if (isset($errors['entity_name'])): ?><div class="invalid-feedback"><?php echo $errors['entity_name']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <select style="color: #000000;" class="form-control<?php if (isset($errors['fund_cluster'])) echo ' is-invalid'; ?>" name="fund_cluster" required>
                        <option value="">Select Fund Cluster</option>
                        <option value="Division" <?php if (isset($fund_cluster) && $fund_cluster == 'Division') echo 'selected'; ?>>Division</option>
                        <option value="MCE" <?php if (isset($fund_cluster) && $fund_cluster == 'MCE') echo 'selected'; ?>>MCE</option>
                        <option value="MOE" <?php if (isset($fund_cluster) && $fund_cluster == 'MOE') echo 'selected'; ?>>MOE</option>
                      </select>
                      <?php if (isset($errors['fund_cluster'])): ?><div class="invalid-feedback"><?php echo $errors['fund_cluster']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">ICS No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="ics_no" required value="<?php echo isset($ics_no) ? htmlspecialchars($ics_no) : ''; ?>">
                    </div>
                  </div>


                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name" required value="<?php echo isset($end_user_name) ? htmlspecialchars($end_user_name) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position / Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_position" required value="<?php echo isset($end_user_position) ? htmlspecialchars($end_user_position) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by End User)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received_user" required value="<?php echo isset($date_received_user) ? htmlspecialchars($date_received_user) : ''; ?>">
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name" required value="<?php echo isset($custodian_name) ? htmlspecialchars($custodian_name) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position / Office (Custodian)</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position" required value="<?php echo isset($custodian_position) ? htmlspecialchars($custodian_position) : ''; ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received (by Custodian)</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received_custodian" required value="<?php echo isset($date_received_custodian) ? htmlspecialchars($date_received_custodian) : ''; ?>">
                    </div>
                  </div>

                  <div class="form-section" style="margin-top: 20px; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h4 class="mb-3">Item Details</h4>
                    <table class="items-table">
                      <thead>
                        <tr>
                          <th>Inventory Item No.</th>
                          <th>Item Description</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          <th>Unit Price</th>
                          <th>Total Price</th>
                          <th>Estimated Life</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="itemsTableBody">
                        <?php
                        $row_count = max(count($inventory_item_nos), 1);
                        for ($i = 0; $i < $row_count; $i++):
                        ?>
                          <tr>
                            <td><input type="text" name="inventory_item_no[]" class="underline-input" value="<?php echo isset($inventory_item_nos[$i]) ? htmlspecialchars($inventory_item_nos[$i]) : ''; ?>"></td>
                            <td><input type="text" name="item_description[]" class="underline-input<?php if (isset($errors['item_description'])) echo ' is-invalid'; ?>" value="<?php echo isset($item_descriptions[$i]) ? htmlspecialchars($item_descriptions[$i]) : ''; ?>"></td>
                            <td>
                              <select name="unit[]" class="underline-input">
                                <option value="">Select Unit</option>
                                <option value="box" <?php if (isset($units[$i]) && $units[$i] == 'box') echo 'selected'; ?>>box</option>
                                <option value="pieces" <?php if (isset($units[$i]) && $units[$i] == 'pieces') echo 'selected'; ?>>pieces</option>
                              </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="underline-input" min="1" value="<?php echo isset($quantities[$i]) ? htmlspecialchars($quantities[$i]) : ''; ?>"></td>
                            <td><input type="number" name="unit_price[]" class="underline-input" min="0" step="0.01" value="<?php echo isset($unit_prices[$i]) ? htmlspecialchars($unit_prices[$i]) : ''; ?>"></td>
                            <td><input type="number" name="total_price[]" class="underline-input" min="0" step="0.01" readonly value="<?php echo isset($total_prices[$i]) ? htmlspecialchars($total_prices[$i]) : ''; ?>"></td>
                            <td><input type="text" name="estimated_life[]" class="underline-input" value="<?php echo isset($estimated_life[$i]) ? htmlspecialchars($estimated_life[$i]) : ''; ?>"></td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                          </tr>
                        <?php endfor; ?>
                        <?php if (isset($errors['item_description'])): ?>
                          <tr>
                            <td colspan="8">
                              <div class="invalid-feedback d-block"><?php echo $errors['item_description']; ?></div>
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                    <button type="button" class="btn btn-add-row" onclick="addItemRow()" style="margin-top: 15px; float: right;"><i class="fas fa-plus mr-2"></i> Add Item</button>
                    <div style="clear: both;"></div>
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
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php'); ?>

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
      }

      // Initialize calculation for any existing rows
      const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');
      const priceInputs = document.querySelectorAll('input[name="unit_price[]"]');

      for (let i = 0; i < quantityInputs.length; i++) {
        if (quantityInputs[i] && priceInputs[i]) {
          quantityInputs[i].addEventListener('input', function() {
            calculateRowTotal(priceInputs[i]);
          });
          priceInputs[i].addEventListener('input', function() {
            calculateRowTotal(this);
          });
        }
      }
    });
  </script>

  <script>
    function addItemRow() {
      const tbody = document.getElementById('itemsTableBody');
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td><input type="text" name="inventory_item_no[]" class="underline-input"></td>
        <td><input type="text" name="item_description[]" class="underline-input"></td>
        <td>
          <select name="unit[]" class="underline-input">
            <option value="">Select Unit</option>
            <option value="box">box</option>
            <option value="pieces">pieces</option>
          </select>
        </td>
        <td><input type="number" name="quantity[]" class="underline-input" min="1"></td>
        <td><input type="number" name="unit_price[]" class="underline-input" min="0" step="0.01"></td>
        <td><input type="number" name="total_price[]" class="underline-input" min="0" step="0.01" readonly></td>
        <td><input type="text" name="estimated_life[]" class="underline-input"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
      `;
      tbody.appendChild(newRow);

      // Add event listeners for the new row
      const quantityInput = newRow.querySelector('input[name="quantity[]"]');
      const unitPriceInput = newRow.querySelector('input[name="unit_price[]"]');
      quantityInput.addEventListener('input', function() {
        calculateRowTotal(unitPriceInput);
      });
      unitPriceInput.addEventListener('input', function() {
        calculateRowTotal(unitPriceInput);
      });
    }

    function removeRow(button) {
      const row = button.closest('tr');
      if (document.querySelectorAll('#itemsTableBody tr').length > 1) {
        row.remove();
      } else {
        alert('Cannot remove the last row');
      }
    }

    function calculateRowTotal(input) {
      const row = input.closest('tr');
      const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
      const unitPrice = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
      row.querySelector('input[name="total_price[]"]').value = (quantity * unitPrice).toFixed(2);
    }
  </script>
</body>

</html>