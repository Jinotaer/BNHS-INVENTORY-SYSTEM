<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Initialize all form variables with default values
$entity_name = '';
$fund_cluster = '';
$supplier_name = '';
$po_no_date = '';
$req_office = '';
$responsibility_center = '';
$iar_no = '';
$iar_date = '';
$invoice_no_date = '';
$receiver_name = '';
$teacher_id = '';
$position = '';
$date_inspected = '';
$inspectors = '';
$barangay_councilor = '';
$pta_observer = '';
$date_received = '';
$property_custodian = '';

// Initialize item arrays with at least one empty element
$stock_nos = [''];
$item_descriptions = [''];
$units = [''];
$quantities = [''];
$unit_prices = [''];
$remarks_array = [''];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  // Sanitize and collect form data
  function sanitize($data)
  {
    return htmlspecialchars(trim($data));
  }

  // Update variables with submitted values
  $entity_name = isset($_POST['entity_name']) ? sanitize($_POST['entity_name']) : '';
  $fund_cluster = isset($_POST['fund_cluster']) ? sanitize($_POST['fund_cluster']) : '';
  $supplier_name = isset($_POST['supplier']) ? sanitize($_POST['supplier']) : '';
  $po_no_date = isset($_POST['po_no_date']) ? sanitize($_POST['po_no_date']) : '';
  $req_office = isset($_POST['req_office']) ? sanitize($_POST['req_office']) : '';
  $responsibility_center = isset($_POST['responsibility_center']) ? sanitize($_POST['responsibility_center']) : '';
  $iar_no = isset($_POST['iar_no']) ? sanitize($_POST['iar_no']) : '';
  $iar_date = isset($_POST['iar_date']) ? sanitize($_POST['iar_date']) : '';
  $invoice_no_date = isset($_POST['invoice_no_date']) ? sanitize($_POST['invoice_no_date']) : '';
  $receiver_name = isset($_POST['receiver_name']) ? sanitize($_POST['receiver_name']) : '';
  $teacher_id = isset($_POST['teacher_id']) ? sanitize($_POST['teacher_id']) : '';
  $position = isset($_POST['position']) ? sanitize($_POST['position']) : '';
  $date_inspected = isset($_POST['date_inspected']) ? sanitize($_POST['date_inspected']) : '';
  $inspectors = isset($_POST['inspectors']) ? sanitize($_POST['inspectors']) : '';
  $barangay_councilor = isset($_POST['barangay_councilor']) ? sanitize($_POST['barangay_councilor']) : '';
  $pta_observer = isset($_POST['pta_observer']) ? sanitize($_POST['pta_observer']) : '';
  $date_received = isset($_POST['date_received']) ? sanitize($_POST['date_received']) : '';
  $property_custodian = isset($_POST['property_custodian']) ? sanitize($_POST['property_custodian']) : '';

  // Update item arrays with submitted values
  $stock_nos = isset($_POST['stock_no']) ? $_POST['stock_no'] : [''];
  $item_descriptions = isset($_POST['item_description']) ? $_POST['item_description'] : [''];
  $units = isset($_POST['unit']) ? $_POST['unit'] : [''];
  $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [''];
  $unit_prices = isset($_POST['unit_price']) ? $_POST['unit_price'] : [''];
  $remarks_array = isset($_POST['remarks']) ? $_POST['remarks'] : [''];

  $errors = [];

  // Filter out empty remarks and convert to plain string
  $remarks_array = array_filter($remarks_array, function($v) { return trim($v) !== ''; });
  $remarks_string = implode(', ', $remarks_array);
  // Debug: Uncomment to check what is being stored
  // echo '<pre>'; print_r($remarks_array); echo '</pre>'; echo $remarks_string; exit;
  
  // Validation
  if (empty($entity_name)) $errors['entity_name'] = 'Entity Name is required.';
  if (empty($fund_cluster)) $errors['fund_cluster'] = 'Fund Cluster is required.';
  if (empty($supplier_name)) $errors['supplier'] = 'Supplier is required.';
  if (empty($po_no_date)) $errors['po_no_date'] = 'PO No. / Date is required.';
  if (empty($req_office)) $errors['req_office'] = 'Requisitioning Office/Dept. is required.';
  if (empty($responsibility_center)) $errors['responsibility_center'] = 'Responsibility Center is required.';
  // Add more validation as needed
  // Example: at least one item description
  $has_item = false;
  foreach ($item_descriptions as $desc) {
    if (trim($desc) !== '') $has_item = true;
  }
  if (!$has_item) $errors['item_description'] = 'At least one item description is required.';

  if (count($errors) === 0) {
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

      // Insert into inspection_acceptance_reports
      $stmt = $mysqli->prepare("INSERT INTO inspection_acceptance_reports (
        entity_id, supplier_id, iar_no, po_no_date, req_office, responsibility_center,
        iar_date, invoice_no_date, receiver_name, teacher_id, position, date_inspected,
        inspectors, barangay_councilor, pta_observer, date_received, property_custodian
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

      if ($stmt === false) {
        throw new Exception("MySQL prepare failed: " . $mysqli->error);
      }

      $stmt->bind_param(
        "iisssssssssssssss",
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
        $property_custodian
      );

      if (!$stmt->execute()) {
        throw new Exception("Error inserting IAR: " . $stmt->error);
      }

      $iar_id = $mysqli->insert_id;

      // Process all items
      $item_count = count($item_descriptions);
      
      // For debugging
      // error_log("Item count: " . $item_count);
      // error_log("Item descriptions: " . print_r($item_descriptions, true));
      
      for ($i = 0; $i < $item_count; $i++) {
        if (empty($item_descriptions[$i])) continue; // Skip empty entries
        
        $stock_no = isset($stock_nos[$i]) ? sanitize($stock_nos[$i]) : '';
        $item_description = sanitize($item_descriptions[$i]);
        $unit = isset($units[$i]) ? sanitize($units[$i]) : '';
        $quantity = isset($quantities[$i]) ? (int)$quantities[$i] : 0;
        $unit_price = isset($unit_prices[$i]) ? (float)$unit_prices[$i] : 0;
        $total_price = $quantity * $unit_price;
        
        // Insert into items table
        $item_stmt = $mysqli->prepare("INSERT INTO items (
          stock_no, item_description, unit, unit_cost, estimated_useful_life
        ) VALUES (?, ?, ?, ?, ?)");
        
        if ($item_stmt === false) {
          throw new Exception("MySQL prepare failed for items: " . $mysqli->error);
        }

        $estimated_life = 5; // Default value, can be adjusted
        $item_stmt->bind_param("sssdi", $stock_no, $item_description, $unit, $unit_price, $estimated_life);

        if (!$item_stmt->execute()) {
          throw new Exception("Error inserting item: " . $item_stmt->error);
        }

        $item_id = $mysqli->insert_id;
        
        // For debugging
        // error_log("Inserted item ID: $item_id");

        // Insert into iar_items (with remarks)
        $iar_items_stmt = $mysqli->prepare("INSERT INTO iar_items (
          iar_id, item_id, quantity, unit_price, total_price, remarks
        ) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($iar_items_stmt === false) {
          throw new Exception("MySQL prepare failed for iar_items: " . $mysqli->error);
        }

        $remark = isset($remarks_array[$i]) ? sanitize($remarks_array[$i]) : '';
        $iar_items_stmt->bind_param("iiidds", $iar_id, $item_id, $quantity, $unit_price, $total_price, $remark);

        if (!$iar_items_stmt->execute()) {
          throw new Exception("Error inserting IAR items: " . $iar_items_stmt->error);
        }
        
        // Close prepared statements
        $item_stmt->close();
        $iar_items_stmt->close();
      }

      // Commit transaction
      $mysqli->commit();
      $success = "Inspection and Acceptance Report Created Successfully";
      header("refresh:1; url=inventory_management.php");
    } catch (Exception $e) {
      // Rollback transaction on error
      $mysqli->rollback();
      $err = "Error: " . $e->getMessage();
      // header("refresh:1; url=inventory_management.php");
    }
  }
}

require_once('partials/_head.php');
?>

<body>
  <?php require_once('partials/_sidebar.php'); ?>

  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
    </div>

    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="dropdown" style="padding: 20px; margin: 10px;">
              <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" style="width: 300px; height: 45px;">
                Inspection and Acceptance Report
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="ris.php">Requisition and Issue Slip</a></li>
                <li><a class="dropdown-item" href="ics.php">Inventory Custodian Slip</a></li>
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
                box-shadow: 0 4px 6px -1px rgba(94,114,228,0.1);
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
                box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25);
              }
              .invalid-feedback {
                color: #dc3545;
                font-size: 0.875em;
                margin-top: 0.25rem;
              }
            </style>

            <div class="card-body">
             
              <form method="POST" class="border border-light p-4 rounded">
                <div class="container mt-4">
                  <h2 class="text-center mb-4 text-uppercase">Inspection and Acceptance Report</h2>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control<?php if(isset($errors['entity_name'])) echo ' is-invalid'; ?>" name="entity_name" required value="<?php echo $entity_name; ?>">
                      <?php if(isset($errors['entity_name'])): ?><div class="invalid-feedback"><?php echo $errors['entity_name']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <select style="color: #000000;" class="form-control<?php if(isset($errors['fund_cluster'])) echo ' is-invalid'; ?>" name="fund_cluster" required>
                        <option value="">Select Fund Cluster</option>
                        <option value="Division" <?php if($fund_cluster == 'Division') echo 'selected'; ?>>Division</option>
                        <option value="MCE" <?php if($fund_cluster == 'MCE') echo 'selected'; ?>>MCE</option>
                        <option value="MOE" <?php if($fund_cluster == 'MOE') echo 'selected'; ?>>MOE</option>
                      </select>
                      <?php if(isset($errors['fund_cluster'])): ?><div class="invalid-feedback"><?php echo $errors['fund_cluster']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Supplier</label>
                      <input style="color: #000000;" type="text" class="form-control<?php if(isset($errors['supplier'])) echo ' is-invalid'; ?>" name="supplier" required value="<?php echo $supplier_name; ?>">
                      <?php if(isset($errors['supplier'])): ?><div class="invalid-feedback"><?php echo $errors['supplier']; ?></div><?php endif; ?>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PO No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control<?php if(isset($errors['po_no_date'])) echo ' is-invalid'; ?>" name="po_no_date" required value="<?php echo $po_no_date; ?>">
                      <?php if(isset($errors['po_no_date'])): ?><div class="invalid-feedback"><?php echo $errors['po_no_date']; ?></div><?php endif; ?>
                    </div>
                  
                    <div class="col-md-4">
                      <label class="form-label">Requisitioning Office/Dept.</label>
                      <select style="color: #000000;" class="form-control<?php if(isset($errors['req_office'])) echo ' is-invalid'; ?>" name="req_office" required>
                        <option value="">Select Requisitioning Office/Dept</option>
                        <!-- Administrative Offices -->
                        <optgroup label="Administrative Offices">
                          <option value="Office of the Principal" <?php echo ($req_office == 'Office of the Principal') ? 'selected' : ''; ?>>Office of the Principal</option>
                          <option value="Office of the Assistant Principal" <?php echo ($req_office == 'Office of the Assistant Principal') ? 'selected' : ''; ?>>Office of the Assistant Principal</option>
                          <option value="Guidance Office" <?php echo ($req_office == 'Guidance Office') ? 'selected' : ''; ?>>Guidance Office</option>
                          <option value="Registrar's Office" <?php echo ($req_office == 'Registrar\'s Office') ? 'selected' : ''; ?>>Registrar's Office</option>
                          <option value="Accounting Office" <?php echo ($req_office == 'Accounting Office') ? 'selected' : ''; ?>>Accounting Office</option>
                          <option value="Cashier's Office" <?php echo ($req_office == 'Cashier\'s Office') ? 'selected' : ''; ?>>Cashier's Office</option>
                          <option value="IT Department" <?php echo ($req_office == 'IT Department') ? 'selected' : ''; ?>>IT Department</option>
                        </optgroup>

                        <!-- Academic Departments -->
                        <optgroup label="Academic Departments">
                          <option value="High School Department" <?php echo ($req_office == 'High School Department') ? 'selected' : ''; ?>>High School Department</option>
                          <option value="HUMSS Department" <?php echo ($req_office == 'HUMSS Department') ? 'selected' : ''; ?>>HUMSS Department</option>
                          <option value="GAS Department" <?php echo ($req_office == 'GAS Department') ? 'selected' : ''; ?>>GAS Department</option>
                          <option value="TVL Department" <?php echo ($req_office == 'TVL Department') ? 'selected' : ''; ?>>TVL Department</option>
                          <option value="STEM Department" <?php echo ($req_office == 'STEM Department') ? 'selected' : ''; ?>>STEM Department</option>
                          <option value="ABM Department" <?php echo ($req_office == 'ABM Department') ? 'selected' : ''; ?>>ABM Department</option>
                          <option value="ICT Department" <?php echo ($req_office == 'ICT Department') ? 'selected' : ''; ?>>ICT Department</option>
                        </optgroup>

                        <!-- Facilities -->
                        <optgroup label="Facilities">
                          <option value="Library" <?php echo ($req_office == 'Library') ? 'selected' : ''; ?>>Library</option>
                          <option value="Science Laboratory" <?php echo ($req_office == 'Science Laboratory') ? 'selected' : ''; ?>>Science Laboratory</option>
                          <option value="Computer Laboratory" <?php echo ($req_office == 'Computer Laboratory') ? 'selected' : ''; ?>>Computer Laboratory</option>
                          <option value="Home Economics Room" <?php echo ($req_office == 'Home Economics Room') ? 'selected' : ''; ?>>Home Economics Room</option>
                          <option value="Industrial Arts Room" <?php echo ($req_office == 'Industrial Arts Room') ? 'selected' : ''; ?>>Industrial Arts Room</option>
                          <option value="Faculty Room" <?php echo ($req_office == 'Faculty Room') ? 'selected' : ''; ?>>Faculty Room</option>
                          <option value="Clinic" <?php echo ($req_office == 'Clinic') ? 'selected' : ''; ?>>Clinic</option>
                          <option value="Canteen" <?php echo ($req_office == 'Canteen') ? 'selected' : ''; ?>>Canteen</option>
                          <option value="Security Office" <?php echo ($req_office == 'Security Office') ? 'selected' : ''; ?>>Security Office</option>
                          <option value="Maintenance Office" <?php echo ($req_office == 'Maintenance Office') ? 'selected' : ''; ?>>Maintenance Office</option>
                        </optgroup>
                      </select>
                      <?php if(isset($errors['req_office'])): ?><div class="invalid-feedback"><?php echo $errors['req_office']; ?></div><?php endif; ?>
                    </div>
                    
                    <div class="col-md-4">
                      <label class="form-label">Responsibility Center</label>
                      <input style="color: #000000;" type="text" class="form-control" name="responsibility_center" value="<?php echo $responsibility_center; ?>" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">IAR No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="iar_no" value="<?php echo $iar_no; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">IAR Date</label>
                      <input style="color: #000000;" type="date" class="form-control" name="iar_date" value="<?php echo $iar_date; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Invoice No. / Date</label>
                      <input style="color: #000000;" type="text" class="form-control" name="invoice_no_date" value="<?php echo $invoice_no_date; ?>" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Receiver Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="receiver_name" value="<?php echo $receiver_name; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Teacher's ID</label>
                      <input style="color: #000000;" type="text" class="form-control" name="teacher_id" value="<?php echo $teacher_id; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Position</label>
                      <input style="color: #000000;" type="text" class="form-control" name="position" value="<?php echo $position; ?>" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Date Inspected</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_inspected" value="<?php echo $date_inspected; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Inspection Team (comma separated)</label>
                      <input style="color: #000000;" type="text" class="form-control" name="inspectors" placeholder="e.g., Joan Savage, Nelson British, Bles Sings" value="<?php echo $inspectors; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Barangay Councilor</label>
                      <input style="color: #000000;" type="text" class="form-control" name="barangay_councilor" value="<?php echo $barangay_councilor; ?>" required>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">PTA Observer</label>
                      <input style="color: #000000;" type="text" class="form-control" name="pta_observer" value="<?php echo $pta_observer; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date Received</label>
                      <input style="color: #000000;" type="date" class="form-control" name="date_received" value="<?php echo $date_received; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Property Custodian</label>
                      <input style="color: #000000;" type="text" class="form-control" name="property_custodian" value="<?php echo $property_custodian; ?>" required>
                    </div>
                  </div>
                  <div class="form-section" style="margin-top: 20px; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h4 class="mb-3">Item Details</h4>
                    <table class="items-table">
                      <thead>
                        <tr>
                          <th>Stock / Property No.</th>
                          <th>Item Description</th>
                          <th>Unit</th>
                          <th>Quantity</th>
                          <th>Unit Price</th>
                          <th>Total Price</th>
                          <th>Remarks</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="itemsTableBody">
                        <?php for ($i = 0; $i < count($item_descriptions); $i++): ?>
                        <tr>
                          <td><input type="text" name="stock_no[]" class="underline-input" value="<?php echo isset($stock_nos[$i]) ? htmlspecialchars($stock_nos[$i]) : ''; ?>" required></td>
                          <td><input type="text" name="item_description[]" class="underline-input<?php if(isset($errors['item_description'])) echo ' is-invalid'; ?>" value="<?php echo isset($item_descriptions[$i]) ? htmlspecialchars($item_descriptions[$i]) : ''; ?>" required></td>
                          <td>
                            <select name="unit[]" class="underline-input" required>
                              <option value="">Select Unit</option>
                              <option value="box" <?php if(isset($units[$i]) && $units[$i] == 'box') echo 'selected'; ?>>box</option>
                              <option value="pieces" <?php if(isset($units[$i]) && $units[$i] == 'pack') echo 'selected'; ?>>pack</option>
                              <option value="pieces" <?php if(isset($units[$i]) && $units[$i] == 'pieces') echo 'selected'; ?>>pieces</option>
                              <option value="pieces" <?php if(isset($units[$i]) && $units[$i] == 'set') echo 'selected'; ?>>set</option>
                            </select>
                          </td>
                          <td><input type="number" name="quantity[]" class="underline-input" min="1" oninput="calculateRowTotal(this.parentNode.nextElementSibling.querySelector('input'))" value="<?php echo isset($quantities[$i]) ? htmlspecialchars($quantities[$i]) : ''; ?>" required></td>
                          <td><input type="number" name="unit_price[]" class="underline-input" min="0" step="0.01" oninput="calculateRowTotal(this)" value="<?php echo isset($unit_prices[$i]) ? htmlspecialchars($unit_prices[$i]) : ''; ?>" required></td>
                          <td><input type="number" name="total_price[]" class="underline-input" min="0" step="0.01" readonly value="<?php echo (isset($quantities[$i]) && isset($unit_prices[$i])) ? htmlspecialchars((float)$quantities[$i] * (float)$unit_prices[$i]) : ''; ?>"></td>
                          <td>
                            <select name="remarks[]" class="underline-input" required>
                              <option value="">Select Remarks</option>
                              <option value="Consumable" <?php if(isset($remarks_array[$i]) && $remarks_array[$i] == 'Consumable') echo 'selected'; ?>>Consumable</option>
                              <option value="Non-Consumable" <?php if(isset($remarks_array[$i]) && $remarks_array[$i] == 'Non-Consumable') echo 'selected'; ?>>Non-Consumable</option>
                            </select>
                          </td>
                          <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                        </tr>
                        <?php endfor; ?>
                        <?php if(isset($errors['item_description'])): ?><tr><td colspan="8"><div class="invalid-feedback d-block"><?php echo $errors['item_description']; ?></div></td></tr><?php endif; ?>
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

      <?php require_once('partials/_mainfooter.php'); ?>
    </div>
  </div>

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
        <td><input type="text" name="stock_no[]" class="underline-input" required></td>
        <td><input type="text" name="item_description[]" class="underline-input" required></td>
        <td>
          <select name="unit[]" class="underline-input" required>
            <option value="">Select Unit</option>
            <option value="box">box</option>
            <option value="pieces">pieces</option>
          </select>
        </td>
        <td><input type="number" name="quantity[]" class="underline-input" min="1" required></td>
        <td><input type="number" name="unit_price[]" class="underline-input" min="0" step="0.01" required></td>
        <td><input type="number" name="total_price[]" class="underline-input" min="0" step="0.01" readonly></td>
        <td>
          <select name="remarks[]" class="underline-input" required>
            <option value="">Select Remarks</option>
            <option value="Consumable">Consumable</option>
            <option value="Non-Consumable">Non-Consumable</option>
          </select>
        </td>
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