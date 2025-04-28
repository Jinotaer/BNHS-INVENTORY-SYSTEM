<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php';

$mpdf = new \Mpdf\Mpdf([
  'mode' => 'utf-8',
  'format' => 'A4',
  'margin_left' => 10,
  'margin_right' => 10,
  'margin_top' => 10,
  'margin_bottom' => 10,
  'margin_header' => 5,
  'margin_footer' => 5
]);

ob_start();
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

    .half {
      width: 50%;
      padding: 5px;
    }

    .signature-line {
      border-top: 1px solid black;
      width: 200px;
      margin: 0 auto;
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
      $ret = "SELECT 
        iar.*, 
        e.entity_name, 
        e.fund_cluster, 
        s.supplier_name as supplier,
        i.stock_no,
        i.item_description,
        i.unit,
        i.unit_cost as unit_price,
        ii.quantity,
        ii.total_price
      FROM inspection_acceptance_reports iar
      JOIN entities e ON iar.entity_id = e.entity_id
      JOIN suppliers s ON iar.supplier_id = s.supplier_id
      JOIN iar_items ii ON iar.iar_id = ii.iar_id
      JOIN items i ON ii.item_id = i.item_id
      ORDER BY iar.created_at DESC";
      
      $stmt = $mysqli->prepare($ret);
      $stmt->execute();
      $res = $stmt->get_result();
      $header_data = $res->fetch_object();
      ?>

      <table>
        <tr>
          <td class="half">
            <p><strong>Entity Name : </strong><?php echo htmlspecialchars($header_data->entity_name ?? ''); ?></p>
          </td>
          <td class="half">
            <p><strong>Fund Cluster : </strong><?php echo htmlspecialchars($header_data->fund_cluster ?? ''); ?></p>
          </td>
        </tr>
        <tr>
          <td class="half" style="border: 1px solid black; padding: 5px;">
            <p><strong>Supplier :</strong> <?php echo htmlspecialchars($header_data->supplier ?? ''); ?></p>
            <p><strong>PO No./Date :</strong> <?php echo htmlspecialchars($header_data->po_no_date ?? ''); ?></p>
            <p><strong>Requisitioning Office/Dept. :</strong> <?php echo htmlspecialchars($header_data->req_office ?? ''); ?></p>
            <p><strong>Responsibility Center Code :</strong> <?php echo htmlspecialchars($header_data->responsibility_center ?? ''); ?></p>
          </td>
          <td class="half" style="border: 1px solid black; padding: 5px;">
            <p><strong>IAR No. :</strong> <?php echo htmlspecialchars($header_data->iar_no ?? ''); ?></p>
            <p><strong>Date :</strong> <?php echo date('M d, Y', strtotime($header_data->iar_date ?? '')); ?></p>
            <p><strong>Invoice No. :</strong> <?php echo htmlspecialchars($header_data->invoice_no_date ?? ''); ?></p>
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
                  Qty: <?php echo number_format($item->quantity ?? 0); ?><br>
                  Cost: ₱<?php echo number_format($item->unit_price ?? 0, 2); ?><br>
                  Total: ₱<?php echo number_format($item->total_price ?? 0, 2); ?>
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
            <p><strong>Date Inspected:</strong> <?php echo date('M d, Y', strtotime($header_data->date_inspected ?? '')); ?></p>
            <br>
            <p style="margin: 20px 0 0;">
              <input type="checkbox" style="width: 15px; height: 15px; vertical-align: middle; margin-right: 8px;">
              Inspected, verified, and found in order as to quantity and specifications
            </p>
            <br>
            <p style="margin-top: 30px;"><strong>Inspection Officer/Inspection Committee</strong></p>
            <p><?php echo htmlspecialchars($header_data->inspectors ?? ''); ?></p>
            <div class="signature-line"></div>
            <p class="text-center">Inspection Officer/Inspection Committee</p>
          </td>

          <!-- Right: Receiving Section -->
          <td style="border: 1px solid black; padding: 10px; vertical-align: top; width: 50%;">
            <p><strong>Date Received:</strong> <?php echo date('M d, Y', strtotime($header_data->date_received ?? '')); ?></p>
            <br>
            <p style="margin-top: 20px;">
              <input type="checkbox" style="width: 15px; height: 15px; vertical-align: middle; margin-right: 8px;">
              Complete
            </p>
            <p style="margin-top: 10px;">
              <input type="checkbox" style="width: 15px; height: 15px; vertical-align: middle; margin-right: 8px;">
              Partial (pls. specify quantity)
            </p>
            <br>
            <p style="text-align: center;"><?php echo htmlspecialchars($header_data->property_custodian ?? ''); ?></p>
            <div class="signature-line"></div>
            <p class="text-center">Supply & Property Custodian</p>
          </td>
        </tr>
      </table>

      <div class="text-center mt-5">
        <table width="100%">
          <tr>
            <td class="text-center">
              <p>Certified Correct:</p>
              <div class="signature-line"></div>
              <p>Inventory Committee Chair</p>
            </td>
            <td class="text-center">
              <p>Approved by:</p>
              <div class="signature-line"></div>
              <p>School Head / Admin Officer</p>
            </td>
            <td class="text-center">
              <p>Verified by:</p>
              <div class="signature-line"></div>
              <p>COA Representative</p>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("IAR_Report_" . date("Y_m_d") . ".pdf", 'I');
?>