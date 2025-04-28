<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bukidnon National High School Inventory System</title>
  <link rel="apple-touch-icon" sizes="180x180" href="assets/img/brand/bnhs.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/brand/bnhs.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/img/brand/bnhs.png">
  <meta name="theme-color" content="#ffffff">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap 5 JavaScript Bundle (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- jQuery (still used for your print function) -->
  <script src="assets/js/jquery.js"></script>

  <style>
  body {
    margin-top: 10px;
    font-family: Arial, sans-serif;
    font-size: 12px;
  }

  @media print {
    @page {
      size: A4 portrait;
      margin: 20mm;
    }

    body {
      margin: 0;
    }

    #printBtn {
      display: none;  
    }

    table {
      page-break-inside: avoid;
    }

    th, td {
      padding: 5px !important;
      font-size: 10pt;
    }
  }
</style>

</head>

<body>
  <div class="container mt-5" id="printableArea">
    <div id="list" class="mx-auto col-10 col-md-10">
      <div class="text-center mb-4">
        <h5 class="fw-bold">REPORT ON THE PHYSICAL COUNT OF PROPERTY, PLANT AND EQUIPMENT</h5>
        <h6 class="text-muted">IT EQUIPMENT</h6>
        <p>As of <strong><?php echo strtoupper(date('F Y')); ?></strong></p>
      </div>

      <p><strong>Fund Cluster:</strong> __________________________</p>
      <p>
        <strong>For which:</strong> CodeAstro Lounge &nbsp;&nbsp;
        <strong>Administrative Officer:</strong> John Doe &nbsp;&nbsp;
        <strong>As of:</strong> <?php echo date('F d, Y'); ?>
      </p>

      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th scope="col">Entity Name</th>
              <th scope="col">Fund Cluster</th>
              <th scope="col">Supplier</th>
              <th scope="col">PO No. / Date</th>
              <th scope="col">Requisitioning Office/Dept.</th>
              <th scope="col">Responsibility Center</th>
              <th scope="col">IAR No.</th>
              <th scope="col">IAR Date</th>
              <th scope="col">Unit Cost</th>
              <th scope="col">Invoice No. / Date</th>
              <th scope="col">Stock / Property No.</th>
              <th scope="col">Remarks</th>
              <th scope="col">Unit</th>
              <th scope="col">Quantity</th>
              <th scope="col">Unit Price</th>
              <th scope="col">Total Price</th>
              <th scope="col">Receiver Name</th>
              <th scope="col">Teacher's ID</th>
              <th scope="col">Position</th>
              <th scope="col">Date Inspected</th>
              <th scope="col">Inspection Team</th>
              <th scope="col">PTA Observer</th>
              <th scope="col">Date Received</th>
              <th scope="col">Property Custodian</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $ret = "SELECT * FROM inspection_acceptance_reports ORDER BY `inspection_acceptance_reports`.`created_at` DESC";  
            $stmt = $mysqli->prepare($ret);
            $stmt->execute();
            $res = $stmt->get_result();
    
            while ($iar = $res->fetch_object()) {
              ?>
              <tr>
              <td><?php echo htmlspecialchars($iar->entity_name ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->fund_cluster ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->supplier ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->po_no ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->requisitioning_office ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->responsibility_center ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->iar_no ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->iar_date ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->unit_cost ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->invoice_no ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->stock_property_no ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->remarks ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->unit ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->quantity ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->unit_price ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->total_price ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->receiver_name ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->teachers_id ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->position ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->date_inspected ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->inspection_team ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->pta_observer ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->date_received ?? ''); ?></td>
                <td><?php echo htmlspecialchars($iar->property_custodian ?? ''); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="row text-center mt-5">
        <div class="col-md-4 mb-4">
          <p>Certified Correct:</p><br><br>
          <strong>__________________________</strong><br>
          <em>Inventory Committee Chair</em>
        </div>
        <div class="col-md-4 mb-4">
          <p>Approved by:</p><br><br>
          <strong>__________________________</strong><br>
          <em>School Head / Admin Officer</em>
        </div>
        <div class="col-md-4 mb-4">
          <p>Verified by:</p><br><br>
          <strong>__________________________</strong><br>
          <em>COA Representative</em>
        </div>
      </div>
    </div>
  </div>

  <!-- Print Button -->
  <div id="printBtn" class="text-center my-4">
    <button onclick="printContent('list');" class="btn btn-primary btn-lg">
      <i class="bi bi-printer"></i> Print
    </button>
  </div>

  <script>
    function printContent(el) {
      var restorepage = $('body').html();
      var printcontent = $('#' + el).clone();
      $('body').empty().html(printcontent);
      window.print();
      $('body').html(restorepage);
    }
  </script>
</body>

</html>