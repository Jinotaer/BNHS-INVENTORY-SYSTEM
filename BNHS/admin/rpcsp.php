<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  inventory_custodian_slips  WHERE  ics_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $result = $stmt->execute();
  $stmt->close();
  if ($result) {
    $success = "Deleted";
    header("refresh:1; url=rpcsp.php");
  } else {
    $err = "Try Again Later";
  }
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
    <!-- Header -->
    <div style="background-image: url(assets/img/theme/bnhsfront.jpg); background-size: cover;"
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
            <div class="card-header border-0">
              <div class="col" style="padding: 15px;">
                <h2 class="text-center mb-pt-3 text-uppercase">REPORT ON THE PHYSICAL COUNT OF SEMI- EXPENDABLE PROPERTY</h2>
              </div>
              
              <!-- Add filter form -->
              <div class="row mb-4 mt-3">
                <div class="col-md-6">
                  <form method="GET" action="rpcsp.php" class="d-flex align-items-center">
                    <div class="input-group">
                      <select name="article" class="form-control">
                        <option value="">All Articles</option>
                        <?php
                          // Get unique articles
                          $article_query = "SELECT DISTINCT ii.article FROM ics_items ii 
                                           WHERE ii.article IS NOT NULL AND ii.article != '' 
                                           ORDER BY ii.article ASC";
                          $article_stmt = $mysqli->prepare($article_query); 
                          $article_stmt->execute();
                          $article_res = $article_stmt->get_result();
                          
                          while($article = $article_res->fetch_object()) {
                            $selected = (isset($_GET['article']) && $_GET['article'] == $article->article) ? 'selected' : '';
                            echo "<option value='".$article->article."' $selected>".$article->article."</option>";
                          }
                          $article_stmt->close();
                        ?>
                      </select>
                      <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">Filter</button>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="col-md-6 text-right">
                  <?php if(isset($_GET['article']) && !empty($_GET['article'])): ?>
                  <a href="print_rpcsp_article.php?article=<?php echo urlencode($_GET['article']); ?>" class="btn btn-success" target="_blank">
                    Print
                  </a>
                  <?php endif; ?>
                </div>
              </div>
              <!-- End filter form -->
            </div>
            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th scope="col">Article</th>
                    <th scope="col">Item Description</th>
                    <th scope="col">Inventory Item No.</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Unit Value</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total Amount</th>
                    <th scope="col">Estimated Useful Life</th>
                    <th scope="col">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Build the WHERE clause for entity filtering
                    $entity_filter = "";
                    if (isset($_GET['entity_id']) && !empty($_GET['entity_id'])) {
                      $entity_id = $_GET['entity_id'];
                      $entity_filter = " WHERE ics.entity_id = '$entity_id' ";
                    } else {
                      $entity_filter = " WHERE 1=1 ";
                    }
                    
                    // Add article filtering if set
                    if (isset($_GET['article']) && !empty($_GET['article'])) {
                      $article = $_GET['article'];
                      $entity_filter .= " AND ii.article = ? ";
                    }
                    
                    $ret = "SELECT ics.ics_id as id, ics.ics_no, ics.end_user_name, ics.end_user_position, 
                            ics.end_user_date as date_received_user, ics.custodian_name, ics.custodian_position, 
                            ics.custodian_date as date_received_custodian, ics.created_at,
                            i.item_description, i.unit_cost, i.unit, i.estimated_useful_life as estimated_life,
                            e.entity_name, e.fund_cluster as entity_fund_cluster,
                            ii.quantity, ii.inventory_item_no, ii.remarks, ii.article,
                            (ii.quantity * i.unit_cost) as total_amount
                            FROM inventory_custodian_slips ics
                            LEFT JOIN ics_items ii ON ics.ics_id = ii.ics_id
                            LEFT JOIN items i ON ii.item_id = i.item_id
                            LEFT JOIN entities e ON ics.entity_id = e.entity_id
                            $entity_filter
                            ORDER BY ics.created_at DESC";
                    
                    $stmt = $mysqli->prepare($ret);
                    
                    if ($stmt === false) {
                      echo "Error preparing statement: " . $mysqli->error;
                    } else {
                      // Bind parameters if article filter is set
                      if (isset($_GET['article']) && !empty($_GET['article'])) {
                        $stmt->bind_param("s", $article);
                      }
                      
                      $stmt->execute();
                      $res = $stmt->get_result();
                    
                    while ($ics = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo isset($ics->article) ? $ics->article : ''; ?></td>
                      <td><?php echo isset($ics->item_description) ? $ics->item_description : ''; ?></td>
                      <td><?php echo isset($ics->inventory_item_no) ? $ics->inventory_item_no : ''; ?></td>
                      <td><?php echo isset($ics->unit) ? $ics->unit : ''; ?></td>
                      <td><?php echo isset($ics->unit_cost) ? $ics->unit_cost : ''; ?></td>
                      <td><?php echo isset($ics->quantity) ? $ics->quantity : ''; ?></td>
                      <td><?php echo isset($ics->total_amount) ? $ics->total_amount : ''; ?></td>
                      <td><?php echo isset($ics->estimated_life) ? $ics->estimated_life : ''; ?></td>
                      <td><?php echo isset($ics->remarks) ? $ics->remarks : ''; ?></td>
                    </tr>
                  <?php }
                  }
                  ?>
                </tbody>
              </table>
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
  <style>
    /* .table-responsive {
      max-height: 500px;
      overflow-y: auto;
    } */
    .btn-group {
      display: flex;
      gap: 5px;
    }
    tbody tr:hover {
      background-color: #f8f9fa;
    }
  </style>
</body>

</html>