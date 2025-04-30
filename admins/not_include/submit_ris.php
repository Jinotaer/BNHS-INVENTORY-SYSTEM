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
    
    // 2. Insert person records
    $persons = [
        'requested' => [
            'full_name' => $_POST['requested_by_name'],
            'position' => $_POST['requested_by_designation'],
            'signature' => $_POST['requested_by_signature']
        ],
        'approved' => [
            'full_name' => $_POST['approved_by_name'],
            'position' => $_POST['approved_by_designation'],
            'signature' => $_POST['approved_by_signature']
        ],
        'issued' => [
            'full_name' => $_POST['issued_by_name'],
            'position' => $_POST['issued_by_designation'],
            'signature' => $_POST['issued_by_signature']
        ],
        'received' => [
            'full_name' => $_POST['received_by_name'],
            'position' => $_POST['received_by_designation'],
            'signature' => $_POST['received_by_signature']
        ]
    ];
    
    $person_ids = [];
    foreach ($persons as $type => $person) {
        $stmt = $conn->prepare("INSERT INTO persons (full_name, position, signature) VALUES (?, ?, ?)");
        $stmt->execute([$person['full_name'], $person['position'], $person['signature']]);
        $person_ids[$type.'_by_id'] = $conn->lastInsertId();
    }
    
    // 3. Insert main RIS record
    $ris_data = [
        'ris_id' => $_POST['ris_no'],
        'entity_id' => $entity_id,
        'division' => $_POST['division'],
        'office' => $_POST['office'],
        'responsibility_code' => $_POST['responsibility_code'],
        'ris_no' => $_POST['ris_no'],
        'purpose' => $_POST['purpose'],
        'requested_by_id' => $person_ids['requested_by_id'],
        'approved_by_id' => $person_ids['approved_by_id'],
        'issued_by_id' => $person_ids['issued_by_id'],
        'received_by_id' => $person_ids['received_by_id'],
        'requested_date' => $_POST['requested_date'],
        'approved_date' => $_POST['approved_date'],
        'issued_date' => $_POST['issued_date'],
        'received_date' => $_POST['received_date']
    ];
    
    $stmt = $conn->prepare("INSERT INTO requisition_and_issue_slip 
        (ris_id, entity_id, division, office, responsibility_code, ris_no, purpose, 
         requested_by_id, approved_by_id, issued_by_id, received_by_id,
         requested_date, approved_date, issued_date, received_date) 
        VALUES 
        (:ris_id, :entity_id, :division, :office, :responsibility_code, :ris_no, :purpose,
         :requested_by_id, :approved_by_id, :issued_by_id, :received_by_id,
         :requested_date, :approved_date, :issued_date, :received_date)");
    $stmt->execute($ris_data);
    
    // 4. Insert items and requisition_stock_issue records
    for ($i = 0; $i < count($_POST['stock_no']); $i++) {
        if (!empty($_POST['stock_no'][$i])) {
            // Insert item if not exists
            $stmt = $conn->prepare("SELECT item_id FROM items WHERE inventory_item_no = ?");
            $stmt->execute([$_POST['stock_no'][$i]]);
            $item_id = $stmt->fetchColumn();
            
            if (!$item_id) {
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
            
            // Insert requisition_stock_issue record
            $stmt = $conn->prepare("INSERT INTO requisition_stock_issue 
                (ris_id, item_ref_id, stock_no, quantity, stock_available, remarks) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['ris_no'],
                $item_id,
                $_POST['stock_no'][$i],
                $_POST['quantity'][$i],
                $_POST['stock_available'][$i] ?? 0,
                $_POST['remarks'][$i]
            ]);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h1>Requisition Submitted Successfully</h1>";
    echo "<p>RIS Number: " . htmlspecialchars($_POST['ris_no']) . "</p>";
    echo "<a href='ambotlang.php'>Create Another Requisition</a>";
    
} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>