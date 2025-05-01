<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
//Delete Staff
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $adn = "DELETE FROM  property_acknowledgment_receipts  WHERE  par_id = ?";
  $stmt = $mysqli->prepare($adn);
  $stmt->bind_param('s', $id);
  $result = $stmt->execute();
  $stmt->close();
  if ($result) {
    $success = "Deleted";
    header("refresh:1; url=rpcpar.php");
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
                <h2 class="text-center mb-pt-3 text-uppercase">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h2>
              </div>
              
              <!-- Add filter form -->
              <div class="row mb-4 mt-3">
                <div class="col-md-6">
                  <form method="GET" action="rpcppe.php" class="d-flex align-items-center">
                    <div class="input-group">
                      <select name="article" class="form-control">
                        <option value="">All Articles</option>
                        <?php
                          // Get unique articles
                          $article_query = "SELECT DISTINCT pi.article FROM par_items pi 
                                           WHERE pi.article IS NOT NULL AND pi.article != '' 
                                           ORDER BY pi.article ASC";
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
                  <a href="print_rpcppe_article.php?article=<?php echo urlencode($_GET['article']); ?>" class="btn btn-success" target="_blank">
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
                    <th scope="col">Property No.</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Unit Value</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Total Amount</th>
                    <th scope="col">Date Acquired</th>
                    <th scope="col">Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Build the WHERE clause for entity filtering
                    $entity_filter = "";
                    if (isset($_GET['entity_id']) && !empty($_GET['entity_id'])) {
                      $entity_id = $_GET['entity_id'];
                      $entity_filter = " WHERE par.entity_id = '$entity_id' ";
                    } else {
                      $entity_filter = " WHERE 1=1 ";
                    }
                    
                    // Add article filtering if set
                    if (isset($_GET['article']) && !empty($_GET['article'])) {
                      $article = $_GET['article'];
                      $entity_filter .= " AND pi.article = ? ";
                    }
                    
                    $ret = "SELECT par.par_id as id, par.par_no, par.end_user_name, par.receiver_position, 
                            par.receiver_date, par.date_acquired,
                            i.item_description, i.unit_cost, i.unit,
                            e.entity_name, e.fund_cluster as entity_fund_cluster,
                            pi.quantity, pi.property_number, pi.remarks, pi.article,
                            (pi.quantity * i.unit_cost) as total_amount
                            FROM property_acknowledgment_receipts par
                            LEFT JOIN par_items pi ON par.par_id = pi.par_id
                            LEFT JOIN items i ON pi.item_id = i.item_id
                            LEFT JOIN entities e ON par.entity_id = e.entity_id
                            $entity_filter
                            ORDER BY par.created_at DESC";
                    
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
                    
                    while ($par = $res->fetch_object()) {
                  ?>
                    <tr>
                      <td><?php echo isset($par->article) ? $par->article : ''; ?></td>
                      <td><?php echo isset($par->item_description) ? $par->item_description : ''; ?></td>
                      <td><?php echo isset($par->property_number) ? $par->property_number : ''; ?></td>
                      <td><?php echo isset($par->unit) ? $par->unit : ''; ?></td>
                      <td><?php echo isset($par->unit_cost) ? $par->unit_cost : ''; ?></td>
                      <td><?php echo isset($par->quantity) ? $par->quantity : ''; ?></td>
                      <td><?php echo isset($par->total_amount) ? $par->total_amount : ''; ?></td>
                      <td><?php echo isset($par->date_acquired) ? $par->date_acquired : ''; ?></td>
                      <td><?php echo isset($par->remarks) ? $par->remarks : ''; ?></td>
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
</body>

</html> 