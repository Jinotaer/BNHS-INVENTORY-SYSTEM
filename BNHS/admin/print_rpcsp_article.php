<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once __DIR__ . '/assets/vendor/autoload.php'; // Ensure this path is correct

// Check if article is provided
if (!isset($_GET['article']) || empty($_GET['article'])) {
    die("No article specified. Please provide an article to print.");
}

$article = $_GET['article'];

// Query to get entity information for header
$entity_query = "SELECT DISTINCT e.entity_name, e.fund_cluster 
                FROM inventory_custodian_slips ics
                JOIN entities e ON ics.entity_id = e.entity_id
                JOIN ics_items ii ON ics.ics_id = ii.ics_id
                WHERE ii.article = ?
                LIMIT 1";
$entity_stmt = $mysqli->prepare($entity_query);
$entity_stmt->bind_param("s", $article);
$entity_stmt->execute();
$entity_result = $entity_stmt->get_result();
$entity_data = $entity_result->fetch_object();
$fund_cluster = isset($entity_data->fund_cluster) ? $entity_data->fund_cluster : '';

// Setup mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // Landscape format for wider table
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 16,
    'margin_bottom' => 16,
    'margin_header' => 9,
    'margin_footer' => 9,
    'default_font' => 'Arial'
]);

$mpdf->SetTitle('RPCSP - ' . $article);
$mpdf->SetAuthor('BNHS Inventory System');

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RPCSP - <?php echo htmlspecialchars($article); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            text-align: center;
            margin: 5px 0;
            font-family: Arial, sans-serif;
        }

        h3 {
            font-size: 14pt;
            font-weight: bold;
        }

        h4 {
            font-size: 12pt;
            font-weight: bold;
        }

        h5 {
            font-size: 11pt;
            font-style: italic;
            margin-bottom: 15px;
        }

        h6 {
            font-size: 10pt;
            font-style: italic;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .logo {
            max-width: 70px;
            display: block;
            margin: 0 auto 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-table {
            margin-bottom: 10px;
            box-shadow: none;
        }

        .info-table td {
            border: none;
            padding: 2px;
            font-size: 10pt;
            vertical-align: top;
            text-align: left;
        }

        th,
        .tds {
            border: 1px solid #000;
            padding: 6px;
            font-size: 9pt;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: transparent;
        }

        tr:hover {
            background-color: transparent;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            display: flex;
            justify-content: space-around;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            margin-bottom: 10px;
            padding: 10px;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            margin-bottom: 5px;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .timestamp {
            text-align: center;
            margin-top: 30px;
            font-size: 8pt;
            color: #666;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }

        .title {
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .text-center {
            text-align: center;
        }

        .mt-6 {
            margin-top: 30px;
        }

        .ml-8 {
            margin-left: 40px;
        }

        .signature-table {
            border-collapse: separate;
            border-spacing: 10px;
            margin-top: 30px;
            box-shadow: none;
        }

        .signature-table td {
            border: none;
            padding: 10px;
            font-size: 10pt;
            vertical-align: top;
            background-color: transparent;
        }

        .signature-table tr {
            background-color: transparent;
        }

        .signature-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            width: 200px;
            margin: 5px auto;
        }

        .fund-cluster-line {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-left: 10px;
            display: inline-block;
            width: 300px;
        }

        .accountability-line {
            font-size: 9pt;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        img {
            width: auto;
            height: 120px;
            display: block;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="assets/img/brand/bnhs.png" alt="BNHS Logo" class="logo">
        <h4 class="title">REPORT ON THE PHYSICAL COUNT OF SEMI-EXPENDABLE PROPERTY</h4>
        <h5><?php echo htmlspecialchars($article); ?></h5>
        <h6>(Type of Property, Plant and Equipment)</h6>
        <h5>As of <?php echo date('F Y'); ?></h5>
    </div>

    <div>
        <p><strong>Fund Cluster : </strong><span class="fund-cluster-line"><?php echo htmlspecialchars($fund_cluster); ?></span></p>
        <!-- <p class="accountability-line">For which : STEPANY JANE B. LABADAN, ADMINISTRATIVE OFFICER II, CAPITAN BAYONG NHS- is accountable, having assumed such accountability on SEPTEMBER 2024</p> -->
    </div>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th>Item Description</th>
                <th>Inventory Item No.</th>
                <th>Unit</th>
                <th>Unit Value</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Estimated Useful Life</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to get items filtered by article
            $ret = "SELECT ics.ics_id as id, ics.ics_no,
                    i.item_description, i.unit_cost, i.unit, i.estimated_useful_life as estimated_life,
                    ii.quantity, ii.inventory_item_no, ii.remarks, ii.article,
                    (ii.quantity * i.unit_cost) as total_amount
                    FROM inventory_custodian_slips ics
                    LEFT JOIN ics_items ii ON ics.ics_id = ii.ics_id
                    LEFT JOIN items i ON ii.item_id = i.item_id
                    LEFT JOIN entities e ON ics.entity_id = e.entity_id
                    WHERE ii.article = ?
                    ORDER BY i.item_description ASC";

            $stmt = $mysqli->prepare($ret);

            if ($stmt === false) {
                echo "Error preparing statement: " . $mysqli->error;
            } else {
                $stmt->bind_param("s", $article);
                $stmt->execute();
                $res = $stmt->get_result();

                $totalQuantity = 0;
                $totalAmount = 0;

                while ($ics = $res->fetch_object()) {
                    // Calculate running totals
                    $totalQuantity += isset($ics->quantity) ? $ics->quantity : 0;
                    $totalAmount += isset($ics->total_amount) ? $ics->total_amount : 0;
            ?>
                    <tr>
                        <td class="tds"><?php echo isset($ics->article) ? htmlspecialchars($ics->article) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->item_description) ? htmlspecialchars($ics->item_description) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->inventory_item_no) ? htmlspecialchars($ics->inventory_item_no) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->unit) ? htmlspecialchars($ics->unit) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->unit_cost) ? htmlspecialchars(number_format($ics->unit_cost, 2)) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->quantity) ? htmlspecialchars($ics->quantity) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->total_amount) ? htmlspecialchars(number_format($ics->total_amount, 2)) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->estimated_life) ? htmlspecialchars($ics->estimated_life) : ''; ?></td>
                        <td class="tds"><?php echo isset($ics->remarks) ? htmlspecialchars($ics->remarks) : ''; ?></td>
                    </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>

    <div class="text-center mt-6 ml-8">
        <table width="100%" class="signature-table">
            <tr>
                <td class="text-center">
                    <p><strong>Certified Correct by:</strong></p><br><br>
                    <strong>____________________________________</strong><br>
                    <em>Signature over Printed Name of</em><br>
                    <em>Inventory Committee Chair</em>
                </td>
                <td class="text-center">
                    <p><strong>Approved by:</strong></p><br><br>
                    <strong>_____________________________</strong><br>
                    <em>Signature over Printed</em><br>
                    <em>Name of School Head</em>
                </td>
                <td class="text-center">
                    <p><strong>Verified by:</strong></p><br><br>
                    <strong>_____________________________</strong><br>
                    <em>Signature over Printed Name </em><br>
                    <em>of COA Representative</em>  
                </td>
            </tr>
        </table>
    </div>

    <!-- <div class="timestamp">
         <p>Generated on: <?php echo date('F d, Y h:i A'); ?> | BNHS Inventory Management System</p>
    </div> -->
</body>

</html>

<?php
// Get the HTML content from the buffer
$html = ob_get_clean();

// Write the HTML content to the PDF
$mpdf->WriteHTML($html);

// Output the PDF with a specific filename
$filename = 'RPCSP_' . str_replace(' ', '_', $article) . '_' . date('Y-m-d') . '.pdf';
$mpdf->Output($filename, 'I');
exit;
?>