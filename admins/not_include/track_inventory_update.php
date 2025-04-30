<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$tables = [
  'inspection_acceptance_report',
  'inventory_custodian_slip',
  'requisition_and_issue_slip',
  'property_acknowledgement_receipt'
];

// Get parameters
$source_table = isset($_GET['table']) ? $_GET['table'] : '';
$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate
if (!in_array($source_table, $tables) || $record_id <= 0) {
    die("Invalid table or ID.");
}

// Fetch existing record
$query = "SELECT * FROM `$source_table` WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Record not found.");
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    // Gather input
    $fields = [
        'entity_name', 'fund_cluster', 'par_no', 'quantity', 'unit', 'item_description',
        'property_number', 'date_acquired', 'unit_cost', 'end_user_name',
        'receiver_position', 'receiver_date', 'custodian_name',
        'custodian_position', 'custodian_date'
    ];

    $updates = [];
    $values = [];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updates[] = "`$field` = ?";
            $values[] = sanitize($_POST[$field]);
        }
    }

    if (!empty($updates)) {
        $setClause = implode(', ', $updates);
        $sql = "UPDATE `$source_table` SET $setClause WHERE id = ?";
        $stmt = $mysqli->prepare($sql);

        $types = str_repeat('s', count($values)) . 'i';
        $values[] = $record_id;

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $success = "Record updated successfully.";
            header("Location: inventory_management.php");
            exit();
        } else {
            $err = "Update failed: " . $stmt->error;
        }
    }
}

require_once('partials/_head.php');
?>

<body>
<?php require_once('partials/_sidebar.php'); ?>
<div class="main-content">
<?php require_once('partials/_topnav.php'); ?>

<div class="container mt-4">
  <h2>Update Record (<?php echo strtoupper($source_table); ?>)</h2>
  <form method="POST" class="border p-4 rounded">

    <div class="row mb-3">
      <div class="col-md-4">
        <label>Entity Name</label>
        <input type="text" name="entity_name" class="form-control" value="<?php echo $item['entity_name'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Fund Cluster</label>
        <input type="text" name="fund_cluster" class="form-control" value="<?php echo $item['fund_cluster'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>PAR No.</label>
        <input type="text" name="par_no" class="form-control" value="<?php echo $item['par_no'] ?? ''; ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-2">
        <label>Quantity</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo $item['quantity'] ?? ''; ?>">
      </div>
      <div class="col-md-2">
        <label>Unit</label>
        <input type="text" name="unit" class="form-control" value="<?php echo $item['unit'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Description</label>
        <input type="text" name="item_description" class="form-control" value="<?php echo $item['item_description'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Property Number</label>
        <input type="text" name="property_number" class="form-control" value="<?php echo $item['property_number'] ?? ''; ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>Date Acquired</label>
        <input type="date" name="date_acquired" class="form-control" value="<?php echo $item['date_acquired'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Unit Cost</label>
        <input type="text" name="unit_cost" class="form-control" value="<?php echo $item['unit_cost'] ?? ''; ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>End User Name</label>
        <input type="text" name="end_user_name" class="form-control" value="<?php echo $item['end_user_name'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Receiver Position</label>
        <input type="text" name="receiver_position" class="form-control" value="<?php echo $item['receiver_position'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Receiver Date</label>
        <input type="date" name="receiver_date" class="form-control" value="<?php echo $item['receiver_date'] ?? ''; ?>">
      </div>
    </div>

    <div class="row mb-3">
      <div class="col-md-4">
        <label>Custodian Name</label>
        <input type="text" name="custodian_name" class="form-control" value="<?php echo $item['custodian_name'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Custodian Position</label>
        <input type="text" name="custodian_position" class="form-control" value="<?php echo $item['custodian_position'] ?? ''; ?>">
      </div>
      <div class="col-md-4">
        <label>Custodian Date</label>
        <input type="date" name="custodian_date" class="form-control" value="<?php echo $item['custodian_date'] ?? ''; ?>">
      </div>
    </div>

    <div class="text-end mt-3">
      <button type="submit" class="btn btn-success">Update</button>
    </div>
  </form>
</div>

<?php require_once('partials/_mainfooter.php'); ?>
</div>
<?php require_once('partials/_scripts.php'); ?>
</body>
</html>
