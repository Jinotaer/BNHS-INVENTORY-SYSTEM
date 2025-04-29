<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ambotlang2";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Start transaction
    $conn->beginTransaction();
    
    // 1. Check if entity exists or create new
    $entity_name = $_POST['entity_name'];
    $fund_cluster = $_POST['fund_cluster'];
    
    $stmt = $conn->prepare("SELECT entity_id FROM entities WHERE entity_name = ?");
    $stmt->execute([$entity_name]);
    $entity_id = $stmt->fetchColumn();
    
    if (!$entity_id) {
        $stmt = $conn->prepare("INSERT INTO entities (entity_name, fund_cluster) VALUES (?, ?)");
        $stmt->execute([$entity_name, $fund_cluster]);
        $entity_id = $conn->lastInsertId();
    }
    
    // 2. Insert person records for inspectors
    $inspectors = [
        [
            'full_name' => $_POST['teacher1_name'],
            'position' => 'Inspection Officer',
            'signature' => $_POST['teacher1_signature']
        ],
        [
            'full_name' => $_POST['teacher2_name'],
            'position' => 'Inspection Officer',
            'signature' => $_POST['teacher2_signature']
        ],
        [
            'full_name' => $_POST['teacher3_name'],
            'position' => 'Inspectorate Officer',
            'signature' => $_POST['teacher3_signature']
        ],
        [
            'full_name' => $_POST['brgy_official_name'],
            'position' => 'Brgy. Councilor',
            'signature' => $_POST['brgy_official_signature']
        ],
        [
            'full_name' => $_POST['gpta_officer_name'],
            'position' => 'PTA Observer',
            'signature' => $_POST['gpta_officer_signature']
        ],
        [
            'full_name' => $_POST['property_custodian_name'],
            'position' => 'Supply and/or Property Custodian',
            'signature' => $_POST['property_custodian_signature']
        ]
    ];
    
    $person_ids = [];
    foreach ($inspectors as $inspector) {
        $stmt = $conn->prepare("INSERT INTO persons (full_name, position, signature) VALUES (?, ?, ?)");
        $stmt->execute([$inspector['full_name'], $inspector['position'], $inspector['signature']]);
        $person_ids[] = $conn->lastInsertId();
    }
    
    // 3. Insert main IAR record
    $iar_data = [
        'iar_id' => $_POST['iar_no'],
        'entity_id' => $entity_id,
        'supplier' => $_POST['supplier'],
        'po_no' => $_POST['po_no'],
        'requisitioning_office' => $_POST['requisitioning_office'],
        'responsibility_code' => $_POST['responsibility_code'],
        'iar_date' => $_POST['iar_date'],
        'invoice_no' => $_POST['invoice_no'],
        'invoice_date' => $_POST['invoice_date'],
        'date_inspected' => $_POST['date_inspected'],
        'date_received' => $_POST['date_received'],
        'property_custodian_id' => $person_ids[5],
        'inspector_1_id' => $person_ids[0],
        'inspector_2_id' => $person_ids[1],
        'inspector_3_id' => $person_ids[2],
        'barangay_councilor_id' => $person_ids[3],
        'pta_observer_id' => $person_ids[4]
    ];
    
    $stmt = $conn->prepare("INSERT INTO inspection_and_acceptance_report 
        (iar_id, entity_id, supplier, po_no, requisitioning_office, responsibility_code, 
         iar_date, invoice_no, invoice_date, date_inspected, date_received,
         property_custodian_id, inspector_1_id, inspector_2_id, inspector_3_id,
         barangay_councilor_id, pta_observer_id) 
        VALUES 
        (:iar_id, :entity_id, :supplier, :po_no, :requisitioning_office, :responsibility_code,
         :iar_date, :invoice_no, :invoice_date, :date_inspected, :date_received,
         :property_custodian_id, :inspector_1_id, :inspector_2_id, :inspector_3_id,
         :barangay_councilor_id, :pta_observer_id)");
    $stmt->execute($iar_data);
    
    // 4. Insert IAR items
    for ($i = 0; $i < count($_POST['stock_no']); $i++) {
        if (!empty($_POST['stock_no'][$i])) {
            // Insert item if not exists
        
            
            // Check if item exists by description
            $stmt = $conn->prepare("SELECT item_id FROM items WHERE inventory_item_no = ?");
            $stmt->execute([$_POST['stock_no'][$i]]);
            $item_id = $stmt->fetchColumn();
            
            if (!$item_id) {
                // Create new item
                $stmt = $conn->prepare("INSERT INTO items 
                    (description, unit, inventory_item_no) 
                    VALUES (?, ?, ?)");
                $stmt->execute([
                    $_POST['description'][$i],
                    $_POST['unit'][$i],
                    $_POST['stock_no'][$i]
                ]);
                $item_id = $conn->lastInsertId();
            } 
            
            // Insert into iar_items
            $stmt = $conn->prepare("INSERT INTO iar_items 
                (iar_id, item_ref_id, quantity) 
                VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['iar_no'],
                $item_id,
                $_POST['quantity'][$i],
            ]);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h1>Inspection and Acceptance Report Submitted Successfully</h1>";
    echo "<p>IAR Number: " . htmlspecialchars($_POST['iar_no']) . "</p>";
    echo "<a href='iar_form.php'>Create Another IAR</a>";
    
} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>