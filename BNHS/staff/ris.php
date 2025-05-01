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

  // Store form values in session
  $_SESSION['form_values'] = [
    'entity_name' => $_POST['entity_name'],
    'fund_cluster' => $_POST['fund_cluster'],
    'division' => $_POST['division'],
    'office' => $_POST['office'],
    'responsibility_code' => $_POST['responsibility_code'],
    'ris_no' => $_POST['ris_no'],
    'purpose' => $_POST['purpose'],
    'requested_by_name' => $_POST['requested_by_name'],
    'requested_by_designation' => $_POST['requested_by_designation'],
    'requested_by_date' => $_POST['requested_by_date'],
    'approved_by_name' => $_POST['approved_by_name'],
    'approved_by_designation' => $_POST['approved_by_designation'],
    'approved_by_date' => $_POST['approved_by_date'],
    'issued_by_name' => $_POST['issued_by_name'],
    'issued_by_designation' => $_POST['issued_by_designation'],
    'issued_by_date' => $_POST['issued_by_date'],
    'received_by_name' => $_POST['received_by_name'],
    'received_by_designation' => $_POST['received_by_designation'],
    'received_by_date' => $_POST['received_by_date'],
    'stock_no' => $_POST['stock_no'],
    'item_description' => $_POST['item_description'],
    'unit' => $_POST['unit'],
    'requested_qty' => $_POST['requested_qty'],
    'stock_available' => $_POST['stock_available'],
    'issue_qty' => $_POST['issue_qty'],
    'remarks' => $_POST['remarks']
  ];

  $entity_name = sanitize($_POST['entity_name']);
  $fund_cluster = sanitize($_POST['fund_cluster']);
  $division = sanitize($_POST['division']);
  $office = sanitize($_POST['office']);
  $responsibility_code = sanitize($_POST['responsibility_code']);
  $ris_no = sanitize($_POST['ris_no']);
  $purpose = sanitize($_POST['purpose']);
  $requested_by_name = sanitize($_POST['requested_by_name']);
  $requested_by_designation = sanitize($_POST['requested_by_designation']);
  $requested_by_date = sanitize($_POST['requested_by_date']);
  $approved_by_name = sanitize($_POST['approved_by_name']);
  $approved_by_designation = sanitize($_POST['approved_by_designation']);
  $approved_by_date = sanitize($_POST['approved_by_date']);
  $issued_by_name = sanitize($_POST['issued_by_name']);
  $issued_by_designation = sanitize($_POST['issued_by_designation']);
  $issued_by_date = sanitize($_POST['issued_by_date']);
  $received_by_name = sanitize($_POST['received_by_name']);
  $received_by_designation = sanitize($_POST['received_by_designation']);
  $received_by_date = sanitize($_POST['received_by_date']);

  // Start transaction for related tables
  $mysqli->begin_transaction();

  try {
    // First get or create entity_id
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

      if ($entity_id <= 0) {
        throw new Exception("Failed to create entity. " . $mysqli->error);
      }
    }

    // Insert RIS header
    $stmt = $mysqli->prepare("INSERT INTO requisition_and_issue_slips (
      entity_id, division, office, responsibility_code, ris_no, purpose, 
      requested_by_name, requested_by_designation, requested_by_date, 
      approved_by_name, approved_by_designation, approved_by_date, 
      issued_by_name, issued_by_designation, issued_by_date, 
      received_by_name, received_by_designation, received_by_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
      throw new Exception("MySQL prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param(
      "isssssssssssssssss",
      $entity_id,
      $division,
      $office,
      $responsibility_code,
      $ris_no,
      $purpose,
      $requested_by_name,
      $requested_by_designation,
      $requested_by_date,
      $approved_by_name,
      $approved_by_designation,
      $approved_by_date,
      $issued_by_name,
      $issued_by_designation,
      $issued_by_date,
      $received_by_name,
      $received_by_designation,
      $received_by_date
    );

    if (!$stmt->execute()) {
      throw new Exception("Failed to insert RIS header: " . $stmt->error);
    }

    $ris_id = $mysqli->insert_id;

    if ($ris_id <= 0) {
      throw new Exception("Failed to get RIS ID after insertion. " . $mysqli->error);
    }

    // Process multiple items
    if (isset($_POST['stock_no']) && is_array($_POST['stock_no'])) {
      foreach ($_POST['stock_no'] as $index => $stock_no) {
        $stock_no = sanitize($stock_no);
        $item_description = sanitize($_POST['item_description'][$index]);
        $unit = sanitize($_POST['unit'][$index]);
        $requested_qty = (int)sanitize($_POST['requested_qty'][$index]);
        $stock_available = sanitize($_POST['stock_available'][$index]);
        $issued_qty = (int)sanitize($_POST['issue_qty'][$index]);
        $remarks = sanitize($_POST['remarks'][$index]);

        // Get or create item
        $item_stmt = $mysqli->prepare("SELECT item_id FROM items WHERE stock_no = ? LIMIT 1");
        $item_stmt->bind_param("s", $stock_no);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();

        if ($item_result->num_rows > 0) {
          $item_row = $item_result->fetch_assoc();
          $item_id = $item_row['item_id'];
        } else {
          // Create new item if not found
          $create_item = $mysqli->prepare("INSERT INTO items (stock_no, item_description, unit, unit_cost) VALUES (?, ?, ?, 0)");
          $create_item->bind_param("sss", $stock_no, $item_description, $unit);
          if (!$create_item->execute()) {
            throw new Exception("Failed to create item: " . $create_item->error);
          }
          $item_id = $mysqli->insert_id;

          if ($item_id <= 0) {
            throw new Exception("Failed to get Item ID after insertion. " . $mysqli->error);
          }
        }

        // Insert RIS item details
        $ris_item_stmt = $mysqli->prepare("INSERT INTO ris_items (
      ris_id, item_id, requested_qty, stock_available, issued_qty, remarks
    ) VALUES (?, ?, ?, ?, ?, ?)");

        $ris_item_stmt->bind_param(
          "iissss",
          $ris_id,
          $item_id,
          $requested_qty,
          $stock_available,
          $issued_qty,
          $remarks
        );

        if (!$ris_item_stmt->execute()) {
          throw new Exception("Failed to insert RIS item: " . $ris_item_stmt->error);
        }
      }
    }

    // Commit transaction
    $mysqli->commit();
    $success = "Requisition and Issue Slip Created Successfully";
    // Clear session values on success
    unset($_SESSION['form_values']);
    header("refresh:1; url=ris.php");
  } catch (Exception $e) {
    // Roll back transaction on error
    $mysqli->rollback();
    $err = "Error: " . $e->getMessage();
    header("refresh:1; url=ris.php");
  }
}

// Get stored form values if they exist
$form_values = $_SESSION['form_values'] ?? [];

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
                Requisition and Issue Slip
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="inventory_management.php">Inspection and Acceptance Report</a></li>
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
                  <h2 class="text-center mb-4 text-uppercase">Requisition and Issue Slip</h2>

                  <div class="row mb-4">
                    <div class="col-md-4">
                      <label class="form-label">Entity Name</label>
                      <input style="color: #000000;" type="text" class="form-control" name="entity_name" value="<?php echo isset($form_values['entity_name']) ? htmlspecialchars($form_values['entity_name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Fund Cluster</label>
                      <select style="color: #000000;" class="form-control<?php if (isset($errors['fund_cluster'])) echo ' is-invalid'; ?>" name="fund_cluster" required>
                        <option value="">Select Fund Cluster</option>
                        <option value="Division" <?php echo (isset($form_values['fund_cluster']) && $form_values['fund_cluster'] == 'Division') ? 'selected' : ''; ?>>Division</option>
                        <option value="MCE" <?php echo (isset($form_values['fund_cluster']) && $form_values['fund_cluster'] == 'MCE') ? 'selected' : ''; ?>>MCE</option>
                        <option value="MOE" <?php echo (isset($form_values['fund_cluster']) && $form_values['fund_cluster'] == 'MOE') ? 'selected' : ''; ?>>MOE</option>
                      </select>
                      <?php if (isset($errors['fund_cluster'])): ?><div class="invalid-feedback"><?php echo $errors['fund_cluster']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Division</label>
                      <input style="color: #000000;" type="text" class="form-control" name="division" value="<?php echo isset($form_values['division']) ? htmlspecialchars($form_values['division']) : ''; ?>" required>
                    </div>

                  </div>

                  <div class="row mb-4">
                    <div class="col-md-4">
                      <label class="form-label">Office</label>
                      <select style="color: #000000;" class="form-control<?php if (isset($errors['office'])) echo ' is-invalid'; ?>" name="office" required>
                        <option value="">Select Office</option>
                        <!-- Administrative Offices -->
                        <optgroup label="Administrative Offices">
                          <option value="Office of the Principal" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Office of the Principal') ? 'selected' : ''; ?>>Office of the Principal</option>
                          <option value="Office of the Assistant Principal" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Office of the Assistant Principal') ? 'selected' : ''; ?>>Office of the Assistant Principal</option>
                          <option value="Guidance Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Guidance Office') ? 'selected' : ''; ?>>Guidance Office</option>
                          <option value="Registrar's Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Registrar\'s Office') ? 'selected' : ''; ?>>Registrar's Office</option>
                          <option value="Accounting Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Accounting Office') ? 'selected' : ''; ?>>Accounting Office</option>
                          <option value="Cashier's Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Cashier\'s Office') ? 'selected' : ''; ?>>Cashier's Office</option>
                          <option value="IT Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'IT Department') ? 'selected' : ''; ?>>IT Department</option>
                        </optgroup>

                        <!-- Academic Departments -->
                        <optgroup label="Academic Departments">
                          <option value="High School Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'High School Department') ? 'selected' : ''; ?>>High School Department</option>
                          <option value="HUMSS Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'HUMSS Department') ? 'selected' : ''; ?>>HUMSS Department</option>
                          <option value="GAS Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'GAS Department') ? 'selected' : ''; ?>>GAS Department</option>
                          <option value="TVL Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'TVL Department') ? 'selected' : ''; ?>>TVL Department</option>
                          <option value="STEM Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'STEM Department') ? 'selected' : ''; ?>>STEM Department</option>
                          <option value="ABM Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'ABM Department') ? 'selected' : ''; ?>>ABM Department</option>
                          <option value="ICT Department" <?php echo (isset($form_values['office']) && $form_values['office'] == 'ICT Department') ? 'selected' : ''; ?>>ICT Department</option>
                        </optgroup>

                        <!-- Facilities -->
                        <optgroup label="Facilities">
                          <option value="Library" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Library') ? 'selected' : ''; ?>>Library</option>
                          <option value="Science Laboratory" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Science Laboratory') ? 'selected' : ''; ?>>Science Laboratory</option>
                          <option value="Computer Laboratory" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Computer Laboratory') ? 'selected' : ''; ?>>Computer Laboratory</option>
                          <option value="Home Economics Room" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Home Economics Room') ? 'selected' : ''; ?>>Home Economics Room</option>
                          <option value="Industrial Arts Room" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Industrial Arts Room') ? 'selected' : ''; ?>>Industrial Arts Room</option>
                          <option value="Faculty Room" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Faculty Room') ? 'selected' : ''; ?>>Faculty Room</option>
                          <option value="Clinic" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Clinic') ? 'selected' : ''; ?>>Clinic</option>
                          <option value="Canteen" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Canteen') ? 'selected' : ''; ?>>Canteen</option>
                          <option value="Security Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Security Office') ? 'selected' : ''; ?>>Security Office</option>
                          <option value="Maintenance Office" <?php echo (isset($form_values['office']) && $form_values['office'] == 'Maintenance Office') ? 'selected' : ''; ?>>Maintenance Office</option>
                        </optgroup>
                      </select>
                      <?php if (isset($errors['office'])): ?><div class="invalid-feedback"><?php echo $errors['office']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Responsibility Center Code</label>
                      <input style="color: #000000;" type="text" class="form-control" name="responsibility_code" value="<?php echo isset($form_values['responsibility_code']) ? htmlspecialchars($form_values['responsibility_code']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">RIS No.</label>
                      <input style="color: #000000;" type="text" class="form-control" name="ris_no" value="<?php echo isset($form_values['ris_no']) ? htmlspecialchars($form_values['ris_no']) : ''; ?>" required>
                    </div>


                  </div>
                  <h5 class="mt-4">Requested By</h5>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="requested_by_name" value="<?php echo isset($form_values['requested_by_name']) ? htmlspecialchars($form_values['requested_by_name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="requested_by_designation" value="<?php echo isset($form_values['requested_by_designation']) ? htmlspecialchars($form_values['requested_by_designation']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="requested_by_date" value="<?php echo isset($form_values['requested_by_date']) ? htmlspecialchars($form_values['requested_by_date']) : ''; ?>" required>
                    </div>
                  </div>

                  <h5>Approved By</h5>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="approved_by_name" value="<?php echo isset($form_values['approved_by_name']) ? htmlspecialchars($form_values['approved_by_name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="approved_by_designation" value="<?php echo isset($form_values['approved_by_designation']) ? htmlspecialchars($form_values['approved_by_designation']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="approved_by_date" value="<?php echo isset($form_values['approved_by_date']) ? htmlspecialchars($form_values['approved_by_date']) : ''; ?>" required>
                    </div>
                  </div>

                  <h5>Issued By</h5>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="issued_by_name" value="<?php echo isset($form_values['issued_by_name']) ? htmlspecialchars($form_values['issued_by_name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="issued_by_designation" value="<?php echo isset($form_values['issued_by_designation']) ? htmlspecialchars($form_values['issued_by_designation']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="issued_by_date" value="<?php echo isset($form_values['issued_by_date']) ? htmlspecialchars($form_values['issued_by_date']) : ''; ?>" required>
                    </div>
                  </div>

                  <h5>Received By</h5>
                  <div class="row mb-3">
                    <div class="col-md-4">
                      <label class="form-label">Name</label>
                      <input type="text" style="color: #000000;" class="form-control" name="received_by_name" value="<?php echo isset($form_values['received_by_name']) ? htmlspecialchars($form_values['received_by_name']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Designation</label>
                      <input type="text" style="color: #000000;" class="form-control" name="received_by_designation" value="<?php echo isset($form_values['received_by_designation']) ? htmlspecialchars($form_values['received_by_designation']) : ''; ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label">Date</label>
                      <input type="date" style="color: #000000;" class="form-control" name="received_by_date" value="<?php echo isset($form_values['received_by_date']) ? htmlspecialchars($form_values['received_by_date']) : ''; ?>" required>
                    </div>
                  </div>
                  <div class="form-section" style="margin-top: 20px; border: 1px solid #dee2e6; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
                    <h4 class="mb-3">Item Details</h4>
                    <table class="items-table">
                      <thead>
                        <tr>
                          <th>Stock No.</th>
                          <th>Item Description</th>
                          <th>Unit</th>
                          <th>Requested Quantity</th>
                          <th>Stock Available?</th>
                          <th>Issue Quantity</th>
                          <th>Remarks</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="itemsTableBody">
                        <?php
                        $item_count = isset($form_values['stock_no']) ? count($form_values['stock_no']) : 1;
                        for ($i = 0; $i < $item_count; $i++):
                        ?>
                          <tr>
                            <td><input type="text" name="stock_no[]" class="underline-input" value="<?php echo isset($form_values['stock_no'][$i]) ? htmlspecialchars($form_values['stock_no'][$i]) : ''; ?>" required></td>
                            <td><input type="text" name="item_description[]" class="underline-input" value="<?php echo isset($form_values['item_description'][$i]) ? htmlspecialchars($form_values['item_description'][$i]) : ''; ?>" required></td>
                            <td>
                              <select name="unit[]" class="underline-input" required>
                                <option value="">Select Unit</option>
                                <option value="box" <?php if (isset($units[$i]) && $units[$i] == 'box') echo 'selected'; ?>>box</option>
                                <option value="pack" <?php if (isset($units[$i]) && $units[$i] == 'pack') echo 'selected'; ?>>pack</option>
                                <option value="pieces" <?php if (isset($units[$i]) && $units[$i] == 'pieces') echo 'selected'; ?>>pieces</option>
                                <option value="set" <?php if (isset($units[$i]) && $units[$i] == 'set') echo 'selected'; ?>>set</option>
                              </select>
                            </td>

                            <td><input type="number" name="requested_qty[]" class="underline-input" min="0" step="0.01" value="<?php echo isset($form_values['requested_qty'][$i]) ? htmlspecialchars($form_values['requested_qty'][$i]) : ''; ?>" required></td>
                            <td>
                              <div class="radio-group">
                                <label><input type="radio" name="stock_available[<?php echo $i; ?>]" value="yes" <?php echo (isset($form_values['stock_available'][$i]) && $form_values['stock_available'][$i] == 'yes') ? 'checked' : ''; ?> required> Yes</label>
                                <label><input type="radio" name="stock_available[<?php echo $i; ?>]" value="no" <?php echo (isset($form_values['stock_available'][$i]) && $form_values['stock_available'][$i] == 'no') ? 'checked' : ''; ?>> No</label>
                              </div>
                            </td>
                            <td><input type="number" name="issue_qty[]" class="underline-input" min="0" step="0.01" value="<?php echo isset($form_values['issue_qty'][$i]) ? htmlspecialchars($form_values['issue_qty'][$i]) : ''; ?>" required></td>
                            <td>
                              <select name="remarks[]" class="underline-input" required>
                                <option value="">Select Remarks</option>
                                <option value="Consumable" <?php echo (isset($form_values['remarks'][$i]) && $form_values['remarks'][$i] == 'Consumable') ? 'selected' : ''; ?>>Consumable</option>
                                <option value="Non-Consumable" <?php echo (isset($form_values['remarks'][$i]) && $form_values['remarks'][$i] == 'Non-Consumable') ? 'selected' : ''; ?>>Non-Consumable</option>
                              </select>
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                          </tr>
                        <?php endfor; ?>
                      </tbody>
                    </table>
                    <button type="button" class="btn btn-add-row" onclick="addItemRow()" style="margin-top: 15px; float: right;"><i class="fas fa-plus mr-2"></i> Add Item</button>
                    <div style="clear: both;"></div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-md-8">
                      <label class="form-label">Purpose</label>
                      <input type="text" style="color: #000000;" class="form-control" name="purpose" value="<?php echo isset($form_values['purpose']) ? htmlspecialchars($form_values['purpose']) : ''; ?>" required>
                    </div>
                  </div>
                  <div class="text-end mt-4">
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
  <script>
    function addItemRow() {
      const tbody = document.getElementById('itemsTableBody');
      const rowCount = tbody.rows.length;
      const newRow = tbody.insertRow();

      newRow.innerHTML = `
        <td><input type="text" name="stock_no[]" class="underline-input" required></td>
        <td><input type="text" name="item_description[]" class="underline-input" required></td>
        <td>
          <select name="unit[]" class="underline-input" required>
            <option value="">Select Unit</option>
            <option value="box">box</option>
            <option value="pack">pack</option>
            <option value="pieces">pieces</option>
            <option value="set">set</option>
          </select>
        </td>
        <td><input type="number" name="requested_qty[]" class="underline-input" min="0" step="0.01" required></td>
        <td>
          <div class="radio-group">
            <label><input type="radio" name="stock_available[${rowCount}]" value="yes" required> Yes</label>
            <label><input type="radio" name="stock_available[${rowCount}]" value="no"> No</label>
          </div>
        </td>
        <td><input type="number" name="issue_qty[]" class="underline-input" min="0" step="0.01" required></td>
        <td>
          <select name="remarks[]" class="underline-input" required>
            <option value="">Select Remarks</option>
            <option value="Consumable">Consumable</option>
            <option value="Non-Consumable">Non-Consumable</option>
          </select>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
      `;
    }

    function removeRow(button) {
      const row = button.closest('tr');
      const tbody = document.getElementById('itemsTableBody');

      if (tbody.rows.length > 1) {
        row.remove();
      } else {
        alert('Cannot remove the last row');
      }
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const requiredFields = document.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          isValid = false;
        } else {
          field.classList.remove('is-invalid');
        }
      });

      if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields');
      }
    });
  </script>
</body>

</html>