<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

$mpdf = new \Mpdf\Mpdf();
ob_start(); // Start output buffering
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

  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 10pt;
    }

    strong {
      font-weight: bold;
      font-size: 12px;
    }

    h5,
    h6 {
      text-align: center;
      margin: 0;
      padding: 5px 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9pt;
    }


    th,
    .tds {
      border: 1px solid black;
      padding: 8px;
      vertical-align: middle;
      text-align: center;
    }

    .text-center {
      text-align: center;
    }

    .mt-5 {
      margin-top: 30px;
    }

    .mb-4 {
      margin-bottom: 20px;
    }

    img {
      width: auto;
      height: 120px;
      display: block;
      margin: auto;
    }

    tbody .tds {
      font-weight: normal;
      font-size: 10px;
    }
  </style>
</head>

<body>
  <div class="container mt-5" id="printableArea">
    <div class="text-center mb-4">
      <img src="assets/img/brand/bnhs.png" alt="BNHS Logo" class="img-fluid">
    </div>

    <div id="list" class="mx-auto col-10 col-md-10">
      <div class="text-center mb-4">
        <h4 class="fw-bold">INSPECTION AND ACCEPTANCE REPORT</h4>
      </div>

      <?php
      $ret = "SELECT * FROM inspection_acceptance_reports ORDER BY `inspection_acceptance_reports`.`created_at` DESC";
      $stmt = $mysqli->prepare($ret);
      $stmt->execute();
      $res = $stmt->get_result();
      $header_data = $res->fetch_object(); // Get header data
      ?>

      <table>
        <tr>
          <td class="half">
            <p><strong>Entity Name : </strong><?php echo htmlspecialchars($header_data->entity_name ?? ''); ?></p>
          </td>
          <td class="half">
            <p><strong>Fund Cluster : </strong><?php echo htmlspecialchars($header_data->fund_cluster ?? ''); ?></p>
            <br>
          </td>
        </tr>
        <br>
        <tr>
          <td class="half" style="border: 1px solid black; padding: 5px;">
            <p><strong>Supplier :</strong> <?php echo htmlspecialchars($header_data->supplier ?? ''); ?></p> <br>
            <p><strong>PO No./Date :</strong> <?php echo htmlspecialchars($header_data->po_no_date ?? ''); ?></p> <br>
            <p><strong>Requisitioning Office/Dept. :</strong> <?php echo htmlspecialchars($header_data->req_office ?? ''); ?></p> <br>
            <p><strong>Responsibility Center Code :</strong> <?php echo htmlspecialchars($header_data->responsibility_center ?? ''); ?></p> <br>
          </td>
          <td class="half" style="border: 1px solid black; padding: 5px;">
            <p><strong>IAR No. :</strong> <?php echo htmlspecialchars($header_data->iar_no ?? ''); ?></p> <br>
            <p><strong>Date :</strong> <?php echo htmlspecialchars($header_data->iar_date ?? ''); ?></p> <br>
            <p><strong>Invoice No. :</strong> <?php echo htmlspecialchars($header_data->stock_no ?? ''); ?></p> <br>
          </td>
        </tr>
      </table>

      <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th class="tds" scope="col">Stock/Property No.</th>
              <th class="tds" style="width: 50%;" scope="col">Description</th>
              <th class="tds" scope="col">Unit</th>
              <th class="tds" scope="col">Quantity/Cost/Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Reset the result pointer to show all items
            $res->data_seek(0);
            $total_amount = 0;
            while ($item = $res->fetch_object()) {
              $total_amount += $item->total_price ?? 0;
            ?>
              <tr>
                <td class="tds"><?php echo htmlspecialchars($item->stock_no ?? ''); ?></td>
                <td class="tds"><?php echo htmlspecialchars($item->item_description ?? ''); ?></td>
                <td class="tds"><?php echo htmlspecialchars($item->unit ?? ''); ?></td>
                <td class="tds">
                  Qty: <?php echo htmlspecialchars($item->quantity ?? ''); ?><br>
                  Cost: ₱<?php echo htmlspecialchars($item->unit_price ?? ''); ?><br>
                  Total: ₱<?php echo htmlspecialchars($item->total_price ?? ''); ?>
                </td>
              </tr>
            <?php } ?>
            <tr>
              <td colspan="3" class="text-end tds"><strong>TOTAL AMOUNT</strong></td>
              <td class="tds">₱<?php echo number_format($total_amount, 2); ?></td>
            </tr>
          </tbody>
        </table>
      </div>

      <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
          <!-- Left: Inspection Section -->
          <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%;">
            <p><strong>Date Inspected:</strong> <?php echo htmlspecialchars($header_data->date_inspected ?? ''); ?></p>
            <br>
            <br>
            <p style="margin: 20px 0 0;">
              <input type="checkbox" style="width: 50px; height: 50px; vertical-align: middle; margin-right: 8px;">
              Inspected, verified, and found in order as to quantity and specifications
            </p>
            <br>
            <br>
            <p style="margin-top: 30px;"><strong>Inspection Officer/Inspection Committee</strong></p>
            <br>
            <br>
            <br>
            <p><?php echo htmlspecialchars($header_data->inspectors ?? ''); ?></p>
            <p>_________________________________________</p>
            <p>Inspection Officer/Inspection Committee</p>
          </td>

          <!-- Right: Receiving Section -->
          <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%;">
            <p><strong>Date Received:</strong> <?php echo htmlspecialchars($header_data->date_received ?? ''); ?></p>
            <br>
            <br>
            <p style="margin-top: 40px;">
              <input type="checkbox" style="width: 55px; height: 55px; vertical-align: middle; margin-right: 8px;">
              Complete
            </p>
            <br>
            <p style="margin-top: 20px;">
              <input type="checkbox" style="width: 50px; height: 50px; vertical-align: middle; margin-right: 8px;">
              Partial (pls. specify quantity)
            </p>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <p style="text-align: center;"><?php echo htmlspecialchars($header_data->property_custodian ?? ''); ?></p>
            <p>_____________________________</p>
            <p style="justify-content: center; align-items: center;">Supply & Property Custodian</p>
          </td>
        </tr>
      </table>

      <!-- <div class="text-center mt-5 ml-5">
        <table width="100%">
          <tr>
            <td class="text-center">
              <p>Certified Correct:</p><br><br>
              <strong>__________________________</strong><br>
              <em>Inventory Committee Chair</em>
            </td>
            <td class="text-center">
              <p>Approved by:</p><br><br>
              <strong>__________________________</strong><br>
              <em>School Head / Admin Officer</em>
            </td>
            <td class="text-center">
              <p>Verified by:</p><br><br>
              <strong>__________________________</strong><br>
              <em>COA Representative</em>
            </td>
          </tr>
        </table>
      </div> -->
    </div>
  </div>
</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("IAR_Report_" . date("Y_m_d") . ".pdf", 'I'); // 'I' to display inline
?>