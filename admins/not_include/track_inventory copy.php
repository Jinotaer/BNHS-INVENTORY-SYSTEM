<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

// Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  inspection_acceptance_reports  WHERE  id = ? UNION DELETE FROM  inventory_custodian_slip  WHERE  id = ? UNION DELETE FROM  requisition_and_issue_slip  WHERE  id = ? UNION DELETE FROM  property_acknowledgement_receipt  WHERE  id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $stmt->execute();
  $stmt->close();
  if ($stmt) {
    $success = "Deleted" && header("refresh:1; url=track_inventory copy.php");
  } else {
    $err = "Try Again Later";
  }
}

// Search item
$searchResults = [];
if (isset($_GET['item']) && !empty(trim($_GET['item']))) {
  $search = $mysqli->real_escape_string(trim($_GET['item']));
  $sql = "SELECT * FROM inspection_acceptance_reports WHERE item_description LIKE CONCAT('%', ?, '%')
          UNION
          SELECT * FROM inventory_custodian_slip WHERE item_description LIKE CONCAT('%', ?, '%')
          UNION
          SELECT * FROM requisition_and_issue_slip WHERE item_description LIKE CONCAT('%', ?, '%')
          UNION
          SELECT * FROM property_acknowledgement_receipt WHERE item_description LIKE CONCAT('%', ?, '%')";

  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param('ssss', $search, $search, $search, $search);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result) {
    while ($row = $result->fetch_object()) {
      $searchResults[] = $row;
    }
  }
  $stmt->close();
}

require_once('partials/_head.php');
?>

<body>
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>

    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body"></div>
      </div>
    </div>

    <div class="container-fluid mt--8">
      <div class="row">
        <div class="col">
          <div class="card shadow">
            <div class="card-header border-0">
              <form class="form-inline" method="GET" style="float: left; margin-top: 20px; margin-bottom: 20px; ">
                <input id="search" class="form-control mr-sm-2" style="width: 500px; color: #000000;" type="search" name="item"
                  placeholder="Search Item by Name" aria-label="Search"
                  value="<?php echo isset($_GET['item']) ? htmlspecialchars($_GET['item']) : ''; ?>">
              </form>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Source</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Item No.</th>
                    <th scope="col">End User</th>
                    <th scope="col">Date</th>
                    <th scope="col">Unit Cost</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total Cost</th>
                    <th scope="col">Custodian</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody class="list" id="userTableBody">
                  <?php
                  if (!empty($searchResults)) {
                    foreach ($searchResults as $item) {
                  ?>
                      <tr>
                        <td><?php echo 'SEARCH'; ?></td>
                        <td><?php echo $item->item_description ?? 'N/A'; ?></td>
                        <td><?php echo $item->iar_no ?? $item->ics_no ?? $item->ris_no ?? $item->par_no ?? 'N/A'; ?></td>
                        <td><?php echo $item->receiver_name ?? $item->end_user_name ?? $item->received_by_name ?? 'N/A'; ?></td>
                        <td><?php echo $item->created_at ?? 'N/A'; ?></td>
                        <td><?php echo $item->unit_price ?? $item->unit_cost ?? $item->unit ?? '0.00'; ?></td>
                        <td><?php echo $item->quantity ?? '0'; ?></td>
                        <td><?php echo $item->total_price ?? $item->total_amount ?? '0.00'; ?></td>
                        <td><?php echo $item->property_custodian ?? $item->custodian_name ?? $item->issued_by_name ?? 'N/A'; ?></td>
                        <td>
                          <a href="view_item.php?id=<?php echo $item->id ?? ''; ?>">
                            <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                          </a>
                          <a href="track_inventory.php?delete=<?php echo $item->id ?? ''; ?>">
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                          </a>
                          <a href="track_inventory_update.php?update=<?php echo $item->id ?? ''; ?>">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                          </a>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    $tables = [
                      'inspection_acceptance_reports',
                      'inventory_custodian_slip',
                      'requisition_and_issue_slip',
                      'property_acknowledgment_receipt'
                    ];

                    foreach ($tables as $table) {
                      $query = "SELECT *, '$table' as source_table FROM `$table` ORDER BY created_at DESC";
                      $result = $mysqli->query($query);
                      if ($result) {
                        while ($item = $result->fetch_assoc()) {
                  ?>
                          <tr>
                            <td><?php echo strtoupper($item['source_table']); ?></td>
                            <td><?php echo $item['item_description'] ?? 'N/A'; ?></td>
                            <td><?php echo $item['iar_no'] ?? $item['ics_no'] ?? $item['ris_no'] ?? $item['par_no'] ?? 'N/A'; ?></td>
                            <td><?php echo $item['receiver_name'] ?? $item['end_user_name'] ?? $item['received_by_name'] ?? $item['end_user_name'] ?? 'N/A'; ?></td>
                            <td><?php echo $item['created_at'] ?? 'N/A'; ?></td>
                            <td><?php echo $item['unit_price'] ?? $item['unit_cost'] ?? $item['unit'] ?? '0.00'; ?></td>
                            <td><?php echo $item['quantity'] ?? '0'; ?></td>
                            <td><?php echo $item['total_price'] ?? $item['total_amount'] ?? '0.00'; ?></td>
                            <td><?php echo $item['property_custodian'] ?? $item['custodian_name'] ?? $item['issued_by_name'] ?? 'N/A'; ?></td>
                            <td>
                              <a href="view_item.php?id=<?php echo $item['id'] ?? ''; ?>">
                                <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                              </a>
                              <a href="user_management.php?delete=<?php echo $item['id'] ?? ''; ?>">
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                              </a>
                              <a href="track_inventory_update.php?update=<?php echo $item['id'] ?? ''; ?>">
                                <button class="btn btn-sm btn-primary"><i class="fas fa-user-edit"></i> Update</button>
                              </a>
                            </td>
                          </tr>
                  <?php
                        }
                      }
                    }
                  }
                  ?>
                  <tr id="noResults" style="display: none;">
                    <td colspan="10" class="text-center">No records found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <?php require_once('partials/_mainfooter.php'); ?>
    </div>
  </div>
  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
