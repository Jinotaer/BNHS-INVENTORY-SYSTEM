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
  
  // First, get or update entity
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

  $ics_no = sanitize($_POST['ics_no']);
  $end_user_name = sanitize($_POST['end_user_name']);
  $end_user_position = sanitize($_POST['end_user_position']);
  $end_user_date = sanitize($_POST['date_received_user']);
  $custodian_name = sanitize($_POST['custodian_name']);
  $custodian_position = sanitize($_POST['custodian_position']);
  $custodian_date = sanitize($_POST['date_received_custodian']);
  $update = $_GET['update'];

  // Now update the ICS main record
  $stmt = $mysqli->prepare("UPDATE inventory_custodian_slips SET 
    entity_id = ?, ics_no = ?, end_user_name = ?, end_user_position = ?, 
    end_user_date = ?, custodian_name = ?, custodian_position = ?, 
    custodian_date = ? WHERE ics_id = ?");

  if ($stmt === false) {
    die("MySQL prepare failed: " . $mysqli->error);
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
    $update
  );
  
  // Get item details for updating ics_items
  $quantity = (int) sanitize($_POST['quantity']);
  $unit = sanitize($_POST['unit']);
  $unit_cost = (float) sanitize($_POST['unit_cost']);
  $item_description = sanitize($_POST['item_description']);
  $inventory_item_no = sanitize($_POST['inventory_item_no']);
  $estimated_useful_life = sanitize($_POST['estimated_life']);
  
  if ($stmt->execute()) {
    // Update or get the item
    $stmt_item = $mysqli->prepare("SELECT item_id FROM items WHERE item_description = ? AND unit = ? AND unit_cost = ?");
    $stmt_item->bind_param("ssd", $item_description, $unit, $unit_cost);
    $stmt_item->execute();
    $result_item = $stmt_item->get_result();
    
    if ($result_item->num_rows > 0) {
      $item_id = $result_item->fetch_object()->item_id;
      
      // Update the item if useful life has changed
      $stmt_update_item = $mysqli->prepare("UPDATE items SET estimated_useful_life = ? WHERE item_id = ?");
      $stmt_update_item->bind_param("ii", $estimated_useful_life, $item_id);
      $stmt_update_item->execute();
    } else {
      // Create new item if doesn't exist
      $stmt_item = $mysqli->prepare("INSERT INTO items (item_description, unit, unit_cost, estimated_useful_life) VALUES (?, ?, ?, ?)");
      $stmt_item->bind_param("ssdi", $item_description, $unit, $unit_cost, $estimated_useful_life);
      $stmt_item->execute();
      $item_id = $mysqli->insert_id;
    }
    
    // Update ics_items entry
    $stmt_ics_item = $mysqli->prepare("UPDATE ics_items SET quantity = ?, inventory_item_no = ?, item_id = ? WHERE ics_id = ?");
    $stmt_ics_item->bind_param("isii", $quantity, $inventory_item_no, $item_id, $update);
    $stmt_ics_item->execute();
    
    $success = "Inventory Custodian Slip Updated Successfully";
    header("refresh:1; url=display_ics.php");
  } else {
    $err = "Error: " . $stmt->error;
    header("refresh:1; url=display_ics.php");
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
    $update = $_GET['update'];
    $ret = "SELECT ics.ics_id, e.entity_name, e.fund_cluster, ics.ics_no,
             ii.quantity, i.unit, i.unit_cost, (ii.quantity * i.unit_cost) as total_amount,
             i.item_description, ii.inventory_item_no, i.estimated_useful_life,
             ics.end_user_name, ics.end_user_position, ics.end_user_date,
             ics.custodian_name, ics.custodian_position, ics.custodian_date
           FROM inventory_custodian_slips ics
           JOIN entities e ON ics.entity_id = e.entity_id
           JOIN ics_items ii ON ics.ics_id = ii.ics_id
           JOIN items i ON ii.item_id = i.item_id
           WHERE ics.ics_id = ?";
    $stmt = $mysqli->prepare($ret);
    $stmt->bind_param('i', $update);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($ics = $res->fetch_object()) {
      ?>
      <!-- Header -->
      <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
        class="header  pb-8 pt-5 pt-md-8">
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
              <div class="card-body ">
                <form method="POST" role="form" class="border border-light p-4 rounded">
                  <div class="container mt-4">
                    <h2 class="text-center mb-4 text-uppercase">Inventory Custodian Slip</h2>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Entity Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->entity_name; ?>" name="entity_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Fund Cluster</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->fund_cluster; ?>" name="fund_cluster" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">ICS No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->ics_no; ?>" name="ics_no" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input style="color: #000000;" type="number" class="form-control" value="<?php echo $ics->quantity; ?>" name="quantity" required>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->unit; ?>" name="unit" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Unit Cost</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->unit_cost; ?>" name="unit_cost" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Total Amount</label>
                        <input style="color: #000000; background-color: white;" type="text" class="form-control"
                          name="total_amount" value="<?php echo $ics->total_amount; ?>" readonly>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label class="form-label">Item Description</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->item_description; ?>" name="item_description" required>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Inventory Item No.</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->inventory_item_no; ?>" name="inventory_item_no" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Estimated Useful Life</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->estimated_useful_life; ?>" name="estimated_life" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">End User Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->end_user_name; ?>" name="end_user_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position / Office</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->end_user_position; ?>" name="end_user_position" required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Date Received (by End User)</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $ics->end_user_date; ?>" name="date_received_user"
                          required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Property Custodian Name</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->custodian_name; ?>" name="custodian_name" required>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label">Position / Office (Custodian)</label>
                        <input style="color: #000000;" type="text" class="form-control" value="<?php echo $ics->custodian_position; ?>" name="custodian_position"
                          required>
                      </div>
                    </div>

                    <div class="row mb-3">
                      <div class="col-md-4">
                        <label class="form-label">Date Received (by Custodian)</label>
                        <input style="color: #000000;" type="date" class="form-control" value="<?php echo $ics->custodian_date; ?>" name="date_received_custodian"
                          required>
                      </div>
                    </div>

                    <div class="text-end mt-3">
                      <button type="submit" class="btn btn-primary" name="updateics" value="Update ics">Submit</button>
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
    }
    ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>

</html>