<?php
session_start();
include('config/config.php');
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

// Check if RIS ID is provided
$ris_id = isset($_GET['ris_id']) ? intval($_GET['ris_id']) : 0;
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

if (!$ris_id) {
    echo "No RIS ID provided. Please specify a RIS to print.";
    exit;
}

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

ob_start(); // Start output buffering

// Get specific RIS record
$ret = "SELECT r.*, e.entity_name, e.fund_cluster 
      FROM requisition_and_issue_slips r
      JOIN entities e ON r.entity_id = e.entity_id
      WHERE r.ris_id = ?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param("i", $ris_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo "No RIS found with ID: " . $ris_id;
    exit;
}

$ris = $res->fetch_object();
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

        tbody .tdnormal {
            font-weight: normal;
            font-size: 10px;
        }

        tbody .tds {
            font-weight: normal;
            font-size: 10px;
        }

        .table-light {
            background-color: #f8f9fa;
        }

        .page-break {
            page-break-after: always;
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
                <h4 class="fw-bold">REQUISITION AND ISSUE SLIP</h4>
            </div>

            <table>
                <tr>
                    <td class="half">
                        <p><strong>Entity Name : </strong><?php echo htmlspecialchars($ris->entity_name ?? ''); ?></p>
                    </td>
                    <td class="half">
                        <p><strong>Fund Cluster : </strong><?php echo htmlspecialchars($ris->fund_cluster ?? ''); ?></p>
                    </td>
                </tr>
                <tr>
                    <td class="half" style="border: 1px solid black; padding: 5px;">
                        <p><strong>Division :</strong> <?php echo htmlspecialchars($ris->division ?? ''); ?></p> <br>
                        <p><strong>Office :</strong> <?php echo htmlspecialchars($ris->office ?? ''); ?></p> <br>
                    </td>
                    <td class="half" style="border: 1px solid black; padding: 5px;">
                        <p><strong> Responsibility Center Code : </strong><?php echo htmlspecialchars($ris->responsibility_code ?? ''); ?></p> <br>
                        <p> <strong>RIS No. :</strong> <?php echo htmlspecialchars($ris->ris_no ?? ''); ?></p> <br>
                    </td>
                </tr>
            </table>

            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="tds" colspan="4" scope="col">Requisition</th>
                            <th class="tds" colspan="2" scope="col">Stock Available?</th>
                            <th class="tds" colspan="3" scope="col">Issue</th>
                        </tr>

                        <tr>
                            <th class="tds">Stock No.</th>
                            <th class="tds">Unit</th>
                            <th class="tds" style="width: 30%;">Description</th>
                            <th class="tds">REQ Quantity</th>
                            <th class="tds" scope="col">Yes</th>
                            <th class="tds" >No</th>
                            <th class="tds">ISS Quantity</th>
                            <th class="tds">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get the RIS items with optional item filter
                        $items_query = "SELECT ri.*, i.stock_no, i.unit, i.item_description FROM ris_items ri 
                                      JOIN items i ON ri.item_id = i.item_id
                                      WHERE ri.ris_id = ?";

                        $params = [$ris_id];

                        // If item_id is provided, filter by it as well
                        if ($item_id) {
                            $items_query .= " AND ri.item_id = ?";
                            $params[] = $item_id;
                        }

                        $items_stmt = $mysqli->prepare($items_query);

                        if (count($params) === 1) {
                            $items_stmt->bind_param("i", $params[0]);
                        } else {
                            $items_stmt->bind_param("ii", $params[0], $params[1]);
                        }

                        $items_stmt->execute();
                        $items_res = $items_stmt->get_result();

                        while ($item = $items_res->fetch_object()) {
                            // Determine Yes/No for stock available
                            $yes_mark = ($item->stock_available > 0) ? "✓" : "";
                            $no_mark = ($item->stock_available <= 0) ? "✓" : "";
                        ?>
                            <tr>
                                <td class="tds"><?php echo htmlspecialchars($item->stock_no ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->unit ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->item_description ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->requested_qty ?? ''); ?></td>
                                <td class="tds" style="width: 10%;"><?php echo $yes_mark; ?></td>
                                <td class="tds" style="width: 10%;"><?php echo $no_mark; ?></td>
                                <td class="tds" style="width: 10%;"><?php echo htmlspecialchars($item->issued_qty ?? ''); ?></td>
                                <td class="tds"><?php echo htmlspecialchars($item->remarks ?? ''); ?></td>
                            </tr>
                           
                        <?php } ?>
                        <tr>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                                <td class="tds"></td>
                            </tr>   
                    </tbody>
                </table>
            </div>

            <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">
                <tr>
                    <td style="width: 15%; vertical-align: top; height: 30px;"><strong>Purpose:</strong></td>
                    <td style="height: 30px;"><?php echo htmlspecialchars($ris->purpose ?? ''); ?></td>
                    <br>
                    <br>
                    <br>
                </tr>
            </table>

            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="tds" style="width: 20%" scope="col"></th>
                        <th class="tds" scope="col">Requested by:</th>
                        <th class="tds" scope="col">Approved by:</th>
                        <th class="tds" scope="col">Issued by:</th>
                        <th class="tds" scope="col">Received by:</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: left;">Signature :</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr>
                        <th style="text-align: left;">Print Name:</th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->requested_by_name ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->approved_by_name ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->issued_by_name ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->received_by_name ?? ''); ?></th>
                    </tr>
                    <tr>
                        <th style="text-align: left;">Designation :</th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->requested_by_designation ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->approved_by_designation ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->issued_by_designation ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->received_by_designation ?? ''); ?></th>
                    </tr>
                    <tr>
                        <th style="text-align: left;">Date :</th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->requested_by_date ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->approved_by_date ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->issued_by_date ?? ''); ?></th>
                        <th class="tdnormal"><?php echo htmlspecialchars($ris->received_by_date ?? ''); ?></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>

<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output("RIS_Report_" . $ris->ris_no . "_" . date("Y_m_d") . ".pdf", 'I'); // 'I' to display inline
?>