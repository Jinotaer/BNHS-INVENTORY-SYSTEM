<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data)
  {
    if (is_array($data)) {
      return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data));
  }

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $par_no = sanitize($_POST['par_no']);
  $property_number = sanitize($_POST['property_number']);
  $article = sanitize($_POST['article']);
  $item_description = sanitize($_POST['item_description']);
  $unit = sanitize($_POST['unit']);
  $quantity = sanitize($_POST['quantity']);
  $unit_cost = sanitize($_POST['unit_cost']);
  $total_amount = isset($_POST['total_amount']) ? sanitize($_POST['total_amount']) : null;
  $remarks = sanitize($_POST['remarks']);
  $date_acquired = sanitize($_POST['date_acquired']);
  $end_user_name = sanitize($_POST['end_user_name']);
  $receiver_position = sanitize($_POST['receiver_position']);
  $receiver_date = sanitize($_POST['receiver_date']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['custodian_date']);

  // First get or create entity_id
  $entity_stmt = $mysqli->prepare("SELECT entity_id FROM entities WHERE entity_name = ?");
  if ($entity_stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $entity_stmt->bind_param("s", $entity_name);
  $entity_stmt->execute();
  $entity_result = $entity_stmt->get_result();

  if ($entity_result->num_rows > 0) {
    $row = $entity_result->fetch_assoc();
    $entity_id = $row['entity_id'];
  } else {
    // Create new entity if not exists
    $create_entity = $mysqli->prepare("INSERT INTO entities (entity_name, fund_cluster) VALUES (?, ?)");
    if ($create_entity === false) {
      die("MySQL prepare failed: " . $mysqli->error);
    }
    $create_entity->bind_param("ss", $entity_name, $fund_cluster);
    $create_entity->execute();
    $entity_id = $mysqli->insert_id;
    $create_entity->close();
  }
  $entity_stmt->close();

  // Insert into property_acknowledgment_receipts
  $stmt = $mysqli->prepare("INSERT INTO property_acknowledgment_receipts (
    entity_id, par_no, date_acquired, end_user_name, receiver_position, receiver_date, custodian_name, custodian_position, custodian_date
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
  }

  $stmt->bind_param(
    "issssssss",
    $entity_id,
    $par_no,
    $date_acquired,
    $end_user_name,
    $receiver_position,
    $receiver_date,
    $custodian_name,
    $custodian_position,
    $custodian_date
  );

  if ($stmt->execute()) {
    $par_id = $mysqli->insert_id;

    // Process all items in the PAR
    for ($i = 0; $i < count($item_description); $i++) {
      // Get or create item_id for the item
      $item_stmt = $mysqli->prepare("SELECT item_id FROM items WHERE item_description = ?");
      if ($item_stmt === false) {
        die("MySQL prepare failed: " . $mysqli->error);
      }

      $item_stmt->bind_param("s", $item_description[$i]);
      $item_stmt->execute();
      $item_result = $item_stmt->get_result();

      if ($item_result->num_rows > 0) {
        $row = $item_result->fetch_assoc();
        $item_id = $row['item_id'];
      } else {
        // Create new item if not exists
        $create_item = $mysqli->prepare("INSERT INTO items (item_description, unit, unit_cost) VALUES (?, ?, ?)");
        if ($create_item === false) {
          die("MySQL prepare failed: " . $mysqli->error);
        }
        $unit_cost_value = (float) $unit_cost[$i];
        $create_item->bind_param("ssd", $item_description[$i], $unit[$i], $unit_cost_value);
        $create_item->execute();
        $item_id = $mysqli->insert_id;
        $create_item->close();
      }
      $item_stmt->close();

      // Insert into par_items
      $par_item_stmt = $mysqli->prepare("INSERT INTO par_items (
        par_id, item_id, quantity, property_number, article, remarks
      ) VALUES (?, ?, ?, ?, ?, ?)");

      if ($par_item_stmt === false) {
        die("MySQL prepare failed: " . $mysqli->error);
      }

      $quantity_value = (int) $quantity[$i];
      $par_item_stmt->bind_param(
        "iiisss",
        $par_id,
        $item_id,
        $quantity_value,
        $property_number[$i],
        $article[$i],
        $remarks[$i]
      );

      if (!$par_item_stmt->execute()) {
        $err = "Error: " . $par_item_stmt->error;
        break;
      }

      $par_item_stmt->close();
    }
    
    if (!isset($err)) {
      $success = "Property Acknowledgment Receipt Created Successfully";
    }
  } else {
    $err = "Error: " . $stmt->error;
  }

  $stmt->close();
  header("refresh:1; url=par.php");
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
            <div class="dropdown" style="padding: 20px; margin: 10px;">
              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" style="width: 300px; height: 45px;">
                Purchase Acceptance Report
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
                <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
                <li><a class="dropdown-item" href="ics.php">Inventory Custodian Slip</a></li>
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
                  <h2 class="text-center mb-4 text-uppercase"> Purchase Acceptance Report</h2>
                  <!-- Entity Info -->
                  <div class="row mt-3 mb-3">
                    <div class="col-md-3">
                      <label>Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Fund Cluster</label>
                      <select style="color: #000000;" class="form-control<?php if (isset($errors['fund_cluster'])) echo ' is-invalid'; ?>" name="fund_cluster" required>
                        <option value="">Select Fund Cluster</option>
                        <option value="Division">Division</option>
                        <option value="MCE">MCE</option>
                        <option value="MOE">MOE</option>
                      </select>
                      <?php if (isset($errors['fund_cluster'])): ?><div class="invalid-feedback"><?php echo $errors['fund_cluster']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                      <label>PAR No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="par_no" required>
                    </div>
                    <div class="col-md-3">
                      <label>Date Acquired</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_acquired" required>
                    </div>
                  </div>

                  <!-- Receiver Section -->
                  <div class="sub-section receiver-section">Receiver</div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label>End User Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="end_user_name" required>
                    </div>
                    <div class="col-md-4">
                      <label>Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_position" required>
                    </div>
                    <div class="col-md-4">
                      <label>Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="receiver_date" required>
                    </div>
                  </div>

                  <!-- Issue Section -->
                  <div class="sub-section issue-section">Issue</div>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label>Property Custodian Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_name" required>
                    </div>
                    <div class="col-md-4">
                      <label>Position/Office</label>
                      <input style="color: #000000;" type="text" class="form-control" name="custodian_position" required>
                    </div>
                    <div class="col-md-4">
                      <label>Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="custodian_date" required>
                    </div>
                  </div>
                  <div class="form-section" style="margin-top: 20px; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h4 class="mb-3">Item Details</h4>
                    <table class="items-table">
                      <thead>
                        <tr>
                          <th>Stock / Property No.</th>
                          <th>Article</th>
                          <th>Item Description</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          <th>Unit Cost</th>
                          <th>Total Amount</th>
                          <th>Remarks</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="itemsTableBody">
                        <tr>
                          <td><input type="text" name="property_number[]" class="underline-input" required></td>
                          <td>
                            <select name="article[]" class="underline-input" required>
                              <option value="">Select Article</option>
                              <!-- <option value="BUILDING">BUILDING</option>
                              <option value="LAND">LAND</option> -->
                              <option value="IT EQUIPMENT">IT EQUIPMENT</option>
                              <!-- <option value="SCHOOL BUILDING">SCHOOL BUILDING</option> -->
                            </select>
                          </td>
                          <td><input type="text" name="item_description[]" class="underline-input<?php if (isset($errors['item_description'])) echo ' is-invalid'; ?>" required></td>
                          <td>
                            <select name="unit[]" class="underline-input" required>
                              <option value="">Select Unit</option>
                              <option value="box">box</option>
                              <option value="pack">pack</option>
                              <option value="pieces">pieces</option>
                              <option value="set">set</option>
                              <option value="unit">unit</option>
                            </select>
                          </td>
                          <td><input type="number" name="quantity[]" class="underline-input" min="1" oninput="calculateRowTotal(this.parentNode.nextElementSibling.querySelector('input'))" required></td>
                          <td><input type="number" name="unit_cost[]" class="underline-input" min="0" step="0.01" oninput="calculateRowTotal(this)" required></td>
                          <td><input type="number" name="total_amount[]" class="underline-input" min="0" step="0.01" readonly></td>
                          <td><input type="text" name="remarks[]" class="underline-input" required></td>
                          <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                        </tr>
                        <?php if (isset($errors['item_description'])): ?><tr>
                            <td colspan="8">
                              <div class="invalid-feedback d-block"><?php echo $errors['item_description']; ?></div>
                            </td>
                          </tr><?php endif; ?>
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
  require_once('partials/_scripts.php');
  ?>
</body>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Initialize calculation for any existing rows
    const quantityInputs = document.querySelectorAll('input[name="quantity[]"]');
    const unitCostInputs = document.querySelectorAll('input[name="unit_cost[]"]');

    for (let i = 0; i < quantityInputs.length; i++) {
      if (quantityInputs[i] && unitCostInputs[i]) {
        quantityInputs[i].addEventListener('input', function() {
          calculateRowTotal(unitCostInputs[i]);
        });
        unitCostInputs[i].addEventListener('input', function() {
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
      <td><input type="text" name="property_number[]" class="underline-input" required></td>
      <td>
        <select name="article[]" class="underline-input" required>
          <option value="">Select Article</option>
          <option value="IT EQUIPMENT">IT EQUIPMENT</option>
       
        </select>
      </td>
      <td><input type="text" name="item_description[]" class="underline-input" required></td>
      <td>
        <select name="unit[]" class="underline-input" required>
          <option value="">Select Unit</option>
          <option value="box">box</option>
          <option value="pack">pack</option>
          <option value="pieces">pieces</option>
          <option value="set">set</option>
          <option value="unit">unit</option>
        </select>
      </td>
      <td><input type="number" name="quantity[]" class="underline-input" min="1" required></td>
      <td><input type="number" name="unit_cost[]" class="underline-input" min="0" step="0.01" required></td>
      <td><input type="number" name="total_amount[]" class="underline-input" min="0" step="0.01" readonly></td>
      <td><input type="text" name="remarks[]" class="underline-input" required></td>
      <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;
    tbody.appendChild(newRow);

    // Add event listeners for the new row
    const quantityInput = newRow.querySelector('input[name="quantity[]"]');
    const unitCostInput = newRow.querySelector('input[name="unit_cost[]"]');
    quantityInput.addEventListener('input', function() {
      calculateRowTotal(unitCostInput);
    });
    unitCostInput.addEventListener('input', function() {
      calculateRowTotal(unitCostInput);
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
    const unitCost = parseFloat(row.querySelector('input[name="unit_cost[]"]').value) || 0;
    row.querySelector('input[name="total_amount[]"]').value = (quantity * unitCost).toFixed(2);
  }
</script>

</html>