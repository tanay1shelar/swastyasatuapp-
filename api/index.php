<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Unified Backend AJAX API Endpoint
 * 
 * Directs incoming XMLHttpRequests to DB operations.
 */

// Define context and include configuration
if (!defined('APP_NAME')) {
    define('APP_NAME', 'HMCMS API');
}
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/session.php';

// Enforce authentication check
if (!isLoggedIn()) {
    responseJson(false, 'Unauthorized session.');
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    // -------------------------------------------------------------------------
    // Medical Stock Inventory Endpoints
    // -------------------------------------------------------------------------
    case 'get_inventory':
        $campId = intval($_GET['camp_id'] ?? 0);
        if ($campId <= 0) {
            responseJson(false, 'Valid Camp ID is required.');
        }
        try {
            $db = db_connect();
            $stmt = $db->prepare("SELECT * FROM medical_stock WHERE camp_id = :camp_id ORDER BY medicine_name ASC");
            $stmt->execute([':camp_id' => $campId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            responseJson(true, 'Inventory list fetched successfully.', $rows);
        } catch (PDOException $e) {
            responseJson(false, 'Query failed: ' . $e->getMessage());
        }
        break;

    case 'save_medicine':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }
        $invId = intval($_POST['inventory_id'] ?? 0);
        $campId = intval($_POST['camp_id'] ?? 0);
        $medicineName = trim($_POST['medicine_name'] ?? '');
        $genericName = trim($_POST['generic_name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $batchNumber = trim($_POST['batch_number'] ?? '');
        $supplier = trim($_POST['supplier'] ?? '');
        $purchaseDate = trim($_POST['purchase_date'] ?? '');
        $expiryDate = trim($_POST['expiry_date'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 0);
        $minQty = intval($_POST['minimum_quantity'] ?? 0);
        $unit = trim($_POST['unit'] ?? '');
        $price = floatval($_POST['price'] ?? 0.00);
        $remarks = trim($_POST['remarks'] ?? '');

        if ($campId <= 0 || empty($medicineName) || empty($category) || empty($unit)) {
            responseJson(false, 'Required parameters (Camp, Medicine Name, Category, Unit) are missing.');
        }

        try {
            $db = db_connect();
            if ($invId > 0) {
                // Update
                $stmt = $db->prepare("
                    UPDATE medical_stock SET
                        medicine_name = :medicine_name,
                        generic_name = :generic_name,
                        category = :category,
                        batch_number = :batch_number,
                        supplier = :supplier,
                        purchase_date = :purchase_date,
                        expiry_date = :expiry_date,
                        quantity = :quantity,
                        minimum_quantity = :minimum_quantity,
                        unit = :unit,
                        price = :price,
                        remarks = :remarks
                    WHERE inventory_id = :inventory_id AND camp_id = :camp_id
                ");
                $stmt->execute([
                    ':medicine_name' => $medicineName,
                    ':generic_name' => $genericName,
                    ':category' => $category,
                    ':batch_number' => $batchNumber,
                    ':supplier' => $supplier,
                    ':purchase_date' => $purchaseDate,
                    ':expiry_date' => $expiryDate,
                    ':quantity' => $quantity,
                    ':minimum_quantity' => $minQty,
                    ':unit' => $unit,
                    ':price' => $price,
                    ':remarks' => $remarks,
                    ':inventory_id' => $invId,
                    ':camp_id' => $campId
                ]);
                responseJson(true, 'Medicine inventory record updated successfully.');
            } else {
                // Insert
                $stmt = $db->prepare("
                    INSERT INTO medical_stock (
                        camp_id, medicine_name, generic_name, category, batch_number,
                        supplier, purchase_date, expiry_date, quantity, minimum_quantity,
                        unit, price, remarks
                    ) VALUES (
                        :camp_id, :medicine_name, :generic_name, :category, :batch_number,
                        :supplier, :purchase_date, :expiry_date, :quantity, :minimum_quantity,
                        :unit, :price, :remarks
                    )
                ");
                $stmt->execute([
                    ':camp_id' => $campId,
                    ':medicine_name' => $medicineName,
                    ':generic_name' => $genericName,
                    ':category' => $category,
                    ':batch_number' => $batchNumber,
                    ':supplier' => $supplier,
                    ':purchase_date' => $purchaseDate,
                    ':expiry_date' => $expiryDate,
                    ':quantity' => $quantity,
                    ':minimum_quantity' => $minQty,
                    ':unit' => $unit,
                    ':price' => $price,
                    ':remarks' => $remarks
                ]);
                responseJson(true, 'Medicine inventory record created successfully.');
            }
        } catch (PDOException $e) {
            responseJson(false, 'Transaction failed: ' . $e->getMessage());
        }
        break;

    case 'delete_medicine':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }
        $invId = intval($_POST['inventory_id'] ?? 0);
        if ($invId <= 0) {
            responseJson(false, 'Valid Inventory ID is required.');
        }
        try {
            $db = db_connect();
            $stmt = $db->prepare("DELETE FROM medical_stock WHERE inventory_id = :inventory_id");
            $stmt->execute([':inventory_id' => $invId]);
            responseJson(true, 'Medicine deleted from inventory successfully.');
        } catch (PDOException $e) {
            responseJson(false, 'Delete failed: ' . $e->getMessage());
        }
        break;

    case 'export_inventory_csv':
        if (ob_get_length()) {
            ob_clean();
        }
        $campId = intval($_GET['camp_id'] ?? 0);
        if ($campId <= 0) {
            exit('Valid Camp ID is required.');
        }
        try {
            $db = db_connect();
            // Fetch camp name
            $stmtCamp = $db->prepare("SELECT camp_name FROM medical_camps WHERE camp_id = :camp_id");
            $stmtCamp->execute([':camp_id' => $campId]);
            $campName = $stmtCamp->fetchColumn() ?: 'Camp_' . $campId;
            
            // Fetch stock
            $stmt = $db->prepare("SELECT * FROM medical_stock WHERE camp_id = :camp_id ORDER BY medicine_name ASC");
            $stmt->execute([':camp_id' => $campId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $sanitizedCampName = preg_replace('/[^a-zA-Z0-9_]/', '_', $campName);
            $filename = 'Medical_Stock_' . $sanitizedCampName . '_' . date('Y-m-d') . '.csv';
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Medicine Name',
                'Generic Name',
                'Category',
                'Batch Number',
                'Current Stock',
                'Minimum Stock',
                'Unit',
                'Expiry Date',
                'Supplier',
                'Price (INR)',
                'Status',
                'Remarks'
            ]);
            
            foreach ($rows as $row) {
                $status = 'In Stock';
                if ($row['quantity'] <= 0) {
                    $status = 'Out of Stock';
                } elseif ($row['quantity'] <= $row['minimum_quantity']) {
                    $status = 'Low Stock';
                }
                
                fputcsv($output, [
                    $row['medicine_name'],
                    $row['generic_name'],
                    $row['category'],
                    $row['batch_number'],
                    $row['quantity'],
                    $row['minimum_quantity'],
                    $row['unit'],
                    $row['expiry_date'],
                    $row['supplier'],
                    $row['price'],
                    $status,
                    $row['remarks']
                ]);
            }
            fclose($output);
            exit;
        } catch (Exception $e) {
            exit('Export failed: ' . $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Search Autocomplete
    // -------------------------------------------------------------------------
    case 'search_patients':
        $query = trim($_GET['query'] ?? '');
        if (empty($query)) {
            try {
                $db = db_connect();
                $stmt = $db->query(QUERY_SELECT_ALL_PATIENTS);
                $results = $stmt->fetchAll();
            } catch (PDOException $e) {
                responseJson(false, $e->getMessage());
            }
        } else {
            if (strlen($query) < 2) {
                responseJson(true, 'Query too short', []);
            }
            $results = db_search_patient($query);
        }
        // Map keys to match the frontend expectations
        $mapped = [];
        foreach ($results as $p) {
            $mapped[] = [
                'id' => $p['patient_id'],
                'name' => $p['first_name'] . ' ' . $p['last_name'],
                'phone' => $p['phone'],
                'email' => $p['email'] ?? 'n/a',
                'aadhaar' => $p['aadhaar'],
                'registration_number' => $p['registration_number'],
                'token' => $p['token_number'],
                'photo' => $p['photo'],
                'gender' => $p['gender'],
                'age' => $p['age'],
                'dob' => $p['dob'],
                'blood' => $p['blood_group'],
                'address' => $p['address'],
                'allergies' => $p['allergies'],
                'chronic' => $p['medical_history'],
                'medications' => $p['current_medication'],
                'emergencyName' => $p['guardian_name'],
                'emergencyPhone' => $p['guardian_phone'],
                'emergencyRelation' => 'Guardian',
                'camp_id' => $p['camp_id'],
                'camp' => $p['camp_name'] ?? 'General',
                'status' => $p['status'] === 'Registered' ? 'Waiting' : $p['status'],
                'doctor' => 'Dr. Rajesh Verma',
                'registrationDate' => date('Y-m-d', strtotime($p['created_at'] ?? 'now')),
                'priority' => $p['triage_priority'] ?? ''
            ];
        }
        responseJson(true, 'Search results', $mapped);
        break;
    case 'export_patients_csv':
        if (ob_get_length()) {
            ob_clean();
        }
        $idsStr = trim($_GET['ids'] ?? '');
        $results = [];
        if (!empty($idsStr)) {
            $ids = explode(',', $idsStr);
            $inClause = str_repeat('?,', count($ids) - 1) . '?';
            
            $db = db_connect();
            $stmt = $db->prepare("
                SELECT 
                    p.*, 
                    c.camp_name,
                    v.verification_status,
                    att.attendance_status,
                    att.triage_priority
                FROM patients p 
                LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
                LEFT JOIN patient_verification v ON p.patient_id = v.patient_id
                LEFT JOIN (
                    SELECT patient_id, attendance_status, triage_priority
                    FROM patient_attendance
                    WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
                ) att ON p.patient_id = att.patient_id
                WHERE p.patient_id IN ($inClause)
            ");
            $stmt->execute($ids);
            $fetched = $stmt->fetchAll();
            
            $idMap = [];
            foreach ($fetched as $row) {
                $idMap[$row['patient_id']] = $row;
            }
            foreach ($ids as $id) {
                if (isset($idMap[$id])) {
                    $results[] = $idMap[$id];
                }
            }
        }

        $filename = 'Patient_List_' . date('Y-m-d_H-i') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\u{FEFF}"); // UTF-8 BOM

        fputcsv($output, [
            'Patient ID',
            'Patient Name',
            'Age',
            'Gender',
            'Blood Group',
            'Phone Number',
            'Camp Location',
            'Registration Date',
            'Status',
            'Priority'
        ]);

        foreach ($results as $row) {
            $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            if (empty($fullName)) {
                $fullName = $row['name'] ?? '';
            }

            $status = $row['status'] ?? 'Waiting';
            if ($status === 'Registered') {
                $status = 'Waiting';
            }

            $prio = $row['triage_priority'] ?? '';
            if (empty($prio)) {
                $prio = '--';
            }

            fputcsv($output, [
                $row['patient_id'] ?? '',
                $fullName,
                (int)($row['age'] ?? 0),
                $row['gender'] ?? '',
                $row['blood_group'] ?? '',
                $row['phone'] ?? '',
                $row['camp_name'] ?? 'General',
                date('Y-m-d', strtotime($row['created_at'] ?? 'now')),
                $status,
                $prio
            ]);
        }
        fclose($output);
        exit;

    // -------------------------------------------------------------------------
    // Patient Verification Export Endpoints
    // -------------------------------------------------------------------------
    case 'export_verifications_csv':
        if (ob_get_length()) {
            ob_clean();
        }
        $patientIdsStr = trim($_GET['patient_ids'] ?? '');
        $params = [];
        $whereClause = "";

        if (!empty($patientIdsStr)) {
            $ids = explode(',', $patientIdsStr);
            $inClause = str_repeat('?,', count($ids) - 1) . '?';
            $whereClause = " WHERE v.patient_id IN ($inClause) ";
            $params = $ids;
        }

        try {
            $db = db_connect();
            $sql = "
                SELECT 
                    v.patient_id,
                    p.registration_number,
                    p.first_name,
                    p.last_name,
                    p.aadhaar,
                    p.age,
                    p.gender,
                    p.blood_group,
                    p.phone,
                    c.camp_name,
                    v.verification_status,
                    v.verification_date,
                    v.remarks,
                    u.full_name AS verified_by,
                    att.attendance_status
                FROM patient_verification v
                JOIN patients p ON v.patient_id = p.patient_id
                LEFT JOIN medical_camps c ON p.camp_id = c.camp_id
                LEFT JOIN users u ON v.verified_by = u.id
                LEFT JOIN (
                    SELECT patient_id, attendance_status
                    FROM patient_attendance
                    WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
                ) att ON p.patient_id = att.patient_id
                $whereClause
                ORDER BY v.verification_date DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = 'Verification_Report_' . date('Y-m-d_H-i') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fwrite($output, "\u{FEFF}");

            fputcsv($output, [
                'Patient Name',
                'Patient ID',
                'Method Logged',
                'Verification Remarks',
                'Timestamp',
                'Status'
            ]);

            foreach ($results as $row) {
                $patientName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (empty($patientName)) {
                    $patientName = $row['name'] ?? '';
                }

                $timestamp = '';
                if (!empty($row['verification_date'])) {
                    $timestamp = date('Y-m-d H:i:s', strtotime($row['verification_date']));
                }

                fputcsv($output, [
                    $patientName,
                    $row['patient_id'] ?? '',
                    'Aadhaar Verification',
                    $row['remarks'] ?? 'Identity confirmed via Aadhaar match',
                    $timestamp,
                    $row['verification_status'] ?? ''
                ]);
            }
            fclose($output);
            exit;
        } catch (Exception $e) {
            exit('Export failed: ' . $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Medical Stock Inventory Export Endpoints
    // -------------------------------------------------------------------------
    case 'export_stock_csv':
        if (ob_get_length()) {
            ob_clean();
        }
        $idsStr = trim($_GET['ids'] ?? '');
        $results = [];
        if (!empty($idsStr)) {
            $ids = explode(',', $idsStr);
            $inClause = str_repeat('?,', count($ids) - 1) . '?';
            
            $db = db_connect();
            $stmt = $db->prepare("
                SELECT s.*, c.camp_name 
                FROM medical_stock s
                LEFT JOIN medical_camps c ON s.camp_id = c.camp_id
                WHERE s.inventory_id IN ($inClause)
            ");
            $stmt->execute($ids);
            $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $idMap = [];
            foreach ($fetched as $row) {
                $idMap[$row['inventory_id']] = $row;
            }
            foreach ($ids as $id) {
                if (isset($idMap[$id])) {
                    $results[] = $idMap[$id];
                }
            }
        }

        $filename = 'Medical_Inventory_' . date('Y-m-d_H-i') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\u{FEFF}"); // UTF-8 BOM

        fputcsv($output, [
            'Medicine Name',
            'Generic Name',
            'Category',
            'Current Stock',
            'Min Stock',
            'Unit',
            'Expiry Date',
            'Supplier',
            'Status'
        ]);

        $today = date('Y-m-d');
        foreach ($results as $row) {
            $status = 'Available';
            if (!empty($row['expiry_date']) && $row['expiry_date'] < $today) {
                $status = 'Expired';
            } else if ($row['quantity'] <= 0) {
                $status = 'Out of Stock';
            } else if ($row['quantity'] <= $row['minimum_quantity']) {
                $status = 'Low Stock';
            }

            fputcsv($output, [
                $row['medicine_name'] ?? '',
                $row['generic_name'] ?? '',
                $row['category'] ?? '',
                (int)($row['quantity'] ?? 0),
                (int)($row['minimum_quantity'] ?? 0),
                $row['unit'] ?? '',
                $row['expiry_date'] ?? '',
                $row['supplier'] ?? '',
                $status
            ]);
        }
        fclose($output);
        exit;

    // -------------------------------------------------------------------------
    // Retrieve Single Patient Details
    // -------------------------------------------------------------------------
    case 'get_patient':
        $patientId = trim($_GET['id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }
        
        $db = db_connect();
        $stmt = $db->prepare(QUERY_SELECT_PATIENT_BY_ID);
        $stmt->execute([':patient_id' => $patientId]);
        $p = $stmt->fetch();
        
        if ($p) {
            $data = [
                'id' => $p['patient_id'],
                'name' => $p['first_name'] . ' ' . $p['last_name'],
                'first_name' => $p['first_name'],
                'last_name' => $p['last_name'],
                'phone' => $p['phone'],
                'email' => $p['email'] ?? 'n/a',
                'aadhaar' => $p['aadhaar'],
                'registration_number' => $p['registration_number'],
                'token' => $p['token_number'],
                'photo' => $p['photo'],
                'gender' => $p['gender'],
                'age' => $p['age'],
                'dob' => $p['dob'],
                'blood' => $p['blood_group'],
                'address' => $p['address'],
                'allergies' => $p['allergies'],
                'chronic' => $p['medical_history'],
                'medications' => $p['current_medication'],
                'emergencyName' => $p['guardian_name'],
                'emergencyPhone' => $p['guardian_phone'],
                'emergencyRelation' => 'Guardian',
                'camp_id' => $p['camp_id'],
                'camp' => $p['camp_name'] ?? 'General',
                'status' => $p['status'] === 'Registered' ? 'Waiting' : $p['status'],
                'doctor' => 'Dr. Rajesh Verma',
                'registrationDate' => date('Y-m-d', strtotime($p['created_at'] ?? 'now')),
                'priority' => $p['triage_priority'] ?? ''
            ];
            responseJson(true, 'Patient located', $data);
        } else {
            responseJson(false, 'Patient record not found.');
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Registration Form Submission
    // -------------------------------------------------------------------------
    case 'register_patient':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }
        
        $dobVal = $_POST['dob'] ?? '';
        $age = 0;
        if (!empty($dobVal)) {
            $dobDate = new DateTime($dobVal);
            $today = new DateTime('today');
            $age = $dobDate->diff($today)->y;
        }

        // Split Full Name into First and Last names
        $fullName = trim($_POST['name'] ?? '');
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        // Resolve Camp Name or Camp ID
        $campVal = $_POST['camp'] ?? '';
        $campId = null;
        if (!empty($campVal)) {
            if (is_numeric($campVal)) {
                $campId = intval($campVal);
            } else {
                $db = db_connect();
                $stmtCamp = $db->prepare("SELECT camp_id FROM medical_camps WHERE camp_name = :name LIMIT 1");
                $stmtCamp->execute([':name' => $campVal]);
                $campId = $stmtCamp->fetchColumn() ?: null;
            }
        }

        // Validate duplicate Aadhaar numbers
        $aadhaar = sanitizeInput($_POST['aadhaar'] ?? '');
        if (!empty($aadhaar)) {
            $db = db_connect();
            $stmtAadhaar = $db->prepare("SELECT COUNT(*) FROM patients WHERE REPLACE(aadhaar, ' ', '') = :aadhaar");
            $cleanAadhaar = str_replace(' ', '', $aadhaar);
            $stmtAadhaar->execute([':aadhaar' => $cleanAadhaar]);
            if ($stmtAadhaar->fetchColumn() > 0) {
                responseJson(false, 'Duplicate Aadhaar Number: This patient is already registered.');
            }
        }

        // Handle Patient Photo Upload
        $photoPath = 'assets/img/avatars/patient-default.png'; // default fallback
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $photoDir = __DIR__ . '/uploads/patient_photos/';
            if (!is_dir($photoDir)) {
                mkdir($photoDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowedPhotos = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowedPhotos)) {
                responseJson(false, 'Invalid Photo Type: Only JPG, JPEG, and PNG are allowed.');
            }
            $filename = uniqid('photo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoDir . $filename)) {
                $photoPath = 'uploads/patient_photos/' . $filename;
            }
        }

        // Handle Document Upload
        $documentPath = null;
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
            $allowedDocs = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array($ext, $allowedDocs)) {
                responseJson(false, 'Invalid Document Type: Only JPG, JPEG, PNG, and PDF are allowed.');
            }
            
            $docDir = __DIR__ . '/uploads/aadhaar/';
            if (!is_dir($docDir)) {
                mkdir($docDir, 0777, true);
            }
            $filename = uniqid('aadhaar_') . '.' . $ext;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $docDir . $filename)) {
                $documentPath = 'uploads/aadhaar/' . $filename;
            }
        }

        $data = [
            'first_name' => sanitizeInput($firstName),
            'middle_name' => '',
            'last_name' => sanitizeInput($lastName),
            'gender' => $_POST['gender'] ?? 'Male',
            'dob' => $dobVal,
            'age' => $age,
            'blood_group' => $_POST['blood'] ?? 'O+',
            'aadhaar' => sanitizeInput($_POST['aadhaar'] ?? ''),
            'phone' => sanitizeInput($_POST['phone'] ?? ''),
            'address' => sanitizeInput($_POST['address'] ?? ''),
            'medical_history' => sanitizeInput($_POST['chronic'] ?? 'None'),
            'allergies' => sanitizeInput($_POST['allergies'] ?? 'None'),
            'current_medication' => sanitizeInput($_POST['medications'] ?? 'None'),
            'guardian_name' => sanitizeInput($_POST['emergencyName'] ?? 'n/a'),
            'guardian_phone' => sanitizeInput($_POST['emergencyPhone'] ?? 'n/a'),
            'camp_id' => $campId,
            'photo' => $photoPath,
            'document_path' => $documentPath
        ];

        $res = db_insert_patient($data);
        if ($res['success']) {
            responseJson(true, 'Patient registered successfully.', $res);
        } else {
            responseJson(false, 'Registration query failed: ' . $res['message']);
        }
        break;

    case 'import_patient':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }
        
        $patientId = trim($_POST['patient_id'] ?? '');
        $regNumber = trim($_POST['registration_number'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $gender = trim($_POST['gender'] ?? 'Male');
        $age = intval($_POST['age'] ?? 0);
        $dob = trim($_POST['dob'] ?? '');
        $blood = trim($_POST['blood'] ?? 'O+');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $aadhaar = trim($_POST['aadhaar'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $village = trim($_POST['village'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $emergencyName = trim($_POST['emergency_contact'] ?? '');
        $emergencyPhone = trim($_POST['emergency_phone'] ?? '');
        $campName = trim($_POST['camp_name'] ?? '');
        $regDate = trim($_POST['registration_date'] ?? '');
        $verStatus = trim($_POST['verification_status'] ?? '');
        $attStatus = trim($_POST['attendance_status'] ?? '');
        $priority = trim($_POST['priority'] ?? 'Low');
        $remarks = trim($_POST['medical_notes'] ?? 'None');

        if (empty($name) || empty($phone) || empty($aadhaar)) {
            responseJson(false, 'Required fields (Name, Phone, Aadhaar) are missing.');
        }

        $db = db_connect();
        
        // Aadhaar duplicate check
        $stmtAadhaar = $db->prepare("SELECT COUNT(*) FROM patients WHERE REPLACE(aadhaar, ' ', '') = REPLACE(:aadhaar, ' ', '')");
        $stmtAadhaar->execute([':aadhaar' => $aadhaar]);
        if ($stmtAadhaar->fetchColumn() > 0) {
            responseJson(false, 'Duplicate Aadhaar');
        }

        // Phone duplicate check
        $stmtPhone = $db->prepare("SELECT COUNT(*) FROM patients WHERE phone = :phone");
        $stmtPhone->execute([':phone' => $phone]);
        if ($stmtPhone->fetchColumn() > 0) {
            responseJson(false, 'Duplicate Phone Number');
        }

        if (!empty($patientId)) {
            $stmtId = $db->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = :id");
            $stmtId->execute([':id' => $patientId]);
            if ($stmtId->fetchColumn() > 0) {
                $patientId = '';
            }
        }

        if (empty($patientId)) {
            $patientId = db_generate_patient_id();
        }
        if (empty($regNumber)) {
            $regNumber = db_generate_reg_number();
        }
        if (empty($regDate)) {
            $regDate = date('Y-m-d');
        }

        // Map status based on Verification and Attendance inputs
        $finalStatus = 'Registered';
        if ($verStatus === 'Verified') {
            $finalStatus = 'Verified';
        }
        if ($attStatus === 'Completed') {
            $finalStatus = 'Completed';
        } else if ($attStatus === 'Present' || $attStatus === 'In Triage' || $attStatus === 'In Consultation') {
            $finalStatus = ($attStatus === 'In Consultation') ? 'In Consultation' : 'In Triage';
        }

        $normalizedCampName = preg_replace('/\s+/', ' ', strtolower(trim($campName)));
        $stmtCamp = $db->query("SELECT camp_id, camp_name FROM medical_camps");
        $allCamps = $stmtCamp->fetchAll(PDO::FETCH_ASSOC);
        $campId = null;
        foreach ($allCamps as $c) {
            if (preg_replace('/\s+/', ' ', strtolower(trim($c['camp_name']))) === $normalizedCampName) {
                $campId = $c['camp_id'];
                break;
            }
        }

        $parts = explode(' ', $name, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        try {
            $db->beginTransaction();

            $stmtInsert = $db->prepare("
                INSERT INTO patients (
                    patient_id, registration_number, token_number, first_name, middle_name, last_name,
                    gender, dob, age, blood_group, aadhaar, phone, alternate_phone, email, occupation,
                    address, village, taluka, district, state, pincode, height, weight, bmi,
                    blood_pressure, pulse, temperature, medical_history, allergies, current_medication,
                    guardian_name, guardian_phone, camp_id, status, photo, document_path, created_at
                ) VALUES (
                    :patient_id, :registration_number, :token_number, :first_name, '', :last_name,
                    :gender, :dob, :age, :blood_group, :aadhaar, :phone, '', :email, '',
                    :address, :village, '', :district, :state, '', 0, 0, 0,
                    '120/80', 72, 98.4, :remarks, 'None', 'None',
                    :emergency_name, :emergency_phone, :camp_id, :status, 'assets/img/avatars/patient-default.png', null, :created_at
                )
            ");

            $token = db_generate_token_number();

            $stmtInsert->execute([
                ':patient_id' => $patientId,
                ':registration_number' => $regNumber,
                ':token_number' => $token,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':gender' => $gender,
                ':dob' => $dob,
                ':age' => $age,
                ':blood_group' => $blood,
                ':aadhaar' => $aadhaar,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':village' => $village,
                ':district' => $district,
                ':state' => $state,
                ':emergency_name' => $emergencyName,
                ':emergency_phone' => $emergencyPhone,
                ':camp_id' => $campId,
                ':status' => $finalStatus,
                ':remarks' => $remarks,
                ':created_at' => $regDate . ' ' . date('H:i:s')
            ]);

            // Verification Record Sync
            if ($verStatus === 'Verified') {
                $stmtVer = $db->prepare("
                    INSERT INTO patient_verification (patient_id, verification_status, verified_by, verification_date, remarks)
                    VALUES (:patient_id, 'Verified', :verified_by, NOW(), 'Imported')
                    ON DUPLICATE KEY UPDATE verification_status = 'Verified'
                ");
                $stmtVer->execute([
                    ':patient_id' => $patientId,
                    ':verified_by' => $_SESSION['user_id'] ?? null
                ]);
            }

            // Attendance Record Sync
            if ($attStatus === 'Present' || $attStatus === 'Completed' || $attStatus === 'In Triage' || $attStatus === 'In Consultation') {
                $checkOutVal = ($attStatus === 'Completed') ? date('h:i A') : '--';
                $stmtAtt = $db->prepare("
                    INSERT INTO patient_attendance (
                        patient_id, check_in, check_out, attendance_status, token_number, triage_priority
                    ) VALUES (
                        :patient_id, :check_in, :check_out, 'Present', :token, :priority
                    )
                    ON DUPLICATE KEY UPDATE attendance_status = 'Present', triage_priority = :priority2, check_out = :check_out2
                ");
                $stmtAtt->execute([
                    ':patient_id' => $patientId,
                    ':check_in' => date('h:i A'),
                    ':check_out' => $checkOutVal,
                    ':token' => $token,
                    ':priority' => $priority,
                    ':priority2' => $priority,
                    ':check_out2' => $checkOutVal
                ]);
            }

            $db->commit();

            // Log activity
            db_log_activity($_SESSION['user_id'] ?? null, "Imported patient $patientId ($name)", "Patients");

            responseJson(true, 'Patient imported successfully.', ['patient_id' => $patientId]);
        } catch (PDOException $e) {
            $db->rollBack();
            responseJson(false, 'Database insert failed: ' . $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Profile Update Form Submission
    // -------------------------------------------------------------------------
    case 'update_patient':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }

        $patientId = trim($_POST['patient_id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }

        // Split Full Name
        $fullName = trim($_POST['name'] ?? '');
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        // Resolve Camp Name or Camp ID
        $campVal = $_POST['camp'] ?? '';
        $campId = null;
        if (!empty($campVal)) {
            if (is_numeric($campVal)) {
                $campId = intval($campVal);
            } else {
                $db = db_connect();
                $stmtCamp = $db->prepare("SELECT camp_id FROM medical_camps WHERE camp_name = :name LIMIT 1");
                $stmtCamp->execute([':name' => $campVal]);
                $campId = $stmtCamp->fetchColumn() ?: null;
            }
        }

        $data = [
            'first_name' => sanitizeInput($firstName),
            'last_name' => sanitizeInput($lastName),
            'gender' => $_POST['gender'] ?? 'Male',
            'dob' => $_POST['dob'] ?? '',
            'blood_group' => $_POST['blood'] ?? 'O+',
            'phone' => sanitizeInput($_POST['phone'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? 'n/a'),
            'address' => sanitizeInput($_POST['address'] ?? ''),
            'emergency_contact' => sanitizeInput($_POST['emergencyPhone'] ?? 'n/a'),
            'allergies' => sanitizeInput($_POST['allergies'] ?? 'None'),
            'medical_history' => sanitizeInput($_POST['chronic'] ?? 'None'),
            'current_medication' => sanitizeInput($_POST['medications'] ?? 'None'),
            'guardian_name' => sanitizeInput($_POST['emergencyName'] ?? 'n/a'),
            'guardian_phone' => sanitizeInput($_POST['emergencyPhone'] ?? 'n/a'),
            'camp_id' => $campId,
            'status' => (($_POST['status'] ?? 'Registered') === 'Waiting' ? 'Registered' : ($_POST['status'] ?? 'Registered'))
        ];

        $res = db_update_patient($patientId, $data);
        if ($res['success']) {
            responseJson(true, 'Patient profile updated successfully.');
        } else {
            responseJson(false, 'Update query failed: ' . $res['message']);
        }
        break;

    case 'search_patient_by_aadhaar':
        $aadhaar = trim($_GET['aadhaar'] ?? '');
        if (empty($aadhaar)) {
            responseJson(false, 'Aadhaar number required.');
        }
        $cleanAadhaar = str_replace(' ', '', $aadhaar);
        
        try {
            $db = db_connect();
            $stmt = $db->prepare("
                SELECT p.*, c.camp_name, 
                       att.triage_priority, att.attendance_status,
                       v.verification_id, v.verification_status, v.verification_date,
                       u.full_name as verifier_name
                FROM patients p
                LEFT JOIN medical_camps c ON p.camp_id = c.camp_id
                LEFT JOIN (
                    SELECT patient_id, triage_priority, attendance_status
                    FROM patient_attendance
                    WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
                ) att ON p.patient_id = att.patient_id
                LEFT JOIN (
                    SELECT patient_id, verification_id, verification_status, verification_date, verified_by
                    FROM patient_verification
                    WHERE verification_id IN (SELECT MAX(verification_id) FROM patient_verification GROUP BY patient_id)
                ) v ON p.patient_id = v.patient_id
                LEFT JOIN users u ON v.verified_by = u.id
                WHERE REPLACE(p.aadhaar, ' ', '') = :aadhaar
            ");
            $stmt->execute([':aadhaar' => $cleanAadhaar]);
            $p = $stmt->fetch();
            
            if ($p) {
                // Map Verification Status
                $verStatus = $p['verification_status'] ?? '';
                if (empty($verStatus)) {
                    if (in_array($p['status'], ['Verified', 'In Triage', 'In Consultation', 'Completed'])) {
                        $verStatus = 'Verified';
                    } elseif ($p['status'] === 'Rejected') {
                        $verStatus = 'Rejected';
                    } else {
                        $verStatus = 'Pending';
                    }
                }

                // Map Attendance Status
                $attStatus = $p['attendance_status'] ?? '';
                if (empty($attStatus)) {
                    if ($p['status'] === 'Completed') {
                        $attStatus = 'Completed';
                    } else if (in_array($p['status'], ['In Triage', 'In Consultation'])) {
                        $attStatus = 'Present';
                    } else {
                        $attStatus = '--';
                    }
                }

                $data = [
                    'id' => $p['patient_id'],
                    'name' => $p['first_name'] . ' ' . $p['last_name'],
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'phone' => $p['phone'],
                    'email' => $p['email'] ?? 'n/a',
                    'aadhaar' => $p['aadhaar'],
                    'registration_number' => $p['registration_number'],
                    'token' => $p['token_number'],
                    'photo' => $p['photo'],
                    'gender' => $p['gender'],
                    'age' => $p['age'],
                    'dob' => $p['dob'],
                    'blood' => $p['blood_group'],
                    'address' => $p['address'],
                    'camp' => $p['camp_name'] ?? 'General',
                    'status' => $p['status'] === 'Registered' ? 'Waiting' : $p['status'],
                    'registrationDate' => date('Y-m-d', strtotime($p['created_at'] ?? 'now')),
                    'priority' => $p['triage_priority'] ?? '',
                    'guardian_name' => $p['guardian_name'] ?? 'n/a',
                    'guardian_phone' => $p['guardian_phone'] ?? 'n/a',
                    // Verification info
                    'verification_id' => $p['verification_id'] ?? null,
                    'verification_status' => $verStatus,
                    'verification_date' => $p['verification_date'] ? date('Y-m-d H:i:s', strtotime($p['verification_date'])) : null,
                    'verifier_name' => $p['verifier_name'] ?? null,
                    'attendance_status' => $attStatus
                ];
                responseJson(true, 'Patient located.', $data);
            } else {
                responseJson(false, 'No patient found');
            }
        } catch (PDOException $e) {
            responseJson(false, 'Query failed: ' . $e->getMessage());
        }
        break;

    case 'verify_patient':
        $patientId = trim($_POST['id'] ?? '');
        $status = $_POST['status'] ?? 'Verified';
        $remarks = sanitizeInput($_POST['remarks'] ?? 'Aadhaar matched successfully');
        $userId = $_SESSION['user_id'] ?? 1;

        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }

        $res = db_verify_patient($patientId, $status, $userId, $remarks);
        if ($res['success']) {
            responseJson(true, "Patient status updated to $status.");
        } else {
            responseJson(false, 'Verification update query failed.');
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Check-In
    // -------------------------------------------------------------------------
    case 'checkin_patient':
        $patientId = trim($_POST['id'] ?? '');
        $status = $_POST['status'] ?? 'Present';
        $token = trim($_POST['token'] ?? '');
        $triagePriority = trim($_POST['triage_priority'] ?? $_POST['triagePriority'] ?? 'Low');

        if (empty($patientId) || empty($token)) {
            responseJson(false, 'Patient ID and Token required.');
        }

        $res = db_mark_attendance($patientId, $status, $token, $triagePriority);
        if ($res['success']) {
            responseJson(true, 'Patient checked in successfully.');
        } else {
            responseJson(false, 'Check-in update query failed.');
        }
        break;

    case 'update_attendance':
        $patientId = trim($_POST['id'] ?? '');
        $status = $_POST['status'] ?? 'Present';
        $triagePriority = trim($_POST['triage_priority'] ?? $_POST['triagePriority'] ?? 'Low');
        $checkIn = trim($_POST['check_in'] ?? '');
        $checkOut = trim($_POST['check_out'] ?? '--');

        if (empty($patientId) || empty($checkIn)) {
            responseJson(false, 'Patient ID and Check-in Time required.');
        }

        $res = db_update_attendance($patientId, $status, $triagePriority, $checkIn, $checkOut);
        if ($res['success']) {
            responseJson(true, 'Attendance updated successfully.');
        } else {
            responseJson(false, $res['message'] ?? 'Attendance update query failed.');
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Check-Out
    // -------------------------------------------------------------------------
    case 'checkout_patient':
        $patientId = trim($_POST['id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }

        $res = db_checkout_attendance($patientId);
        if ($res['success']) {
            responseJson(true, 'Patient checked out successfully.');
        } else {
            responseJson(false, 'Check-out query failed.');
        }
        break;

    // -------------------------------------------------------------------------
    // Camp Assistance Triage Queue Updates
    // -------------------------------------------------------------------------
    case 'call_patient':
        $patientId = trim($_POST['id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }

        try {
            $db = db_connect();
            $db->beginTransaction();
            // Reset any other In Consultation back to In Triage
            $db->query("UPDATE patients SET status = 'In Triage' WHERE status = 'In Consultation'");
            
            $stmt = $db->prepare("UPDATE patients SET status = 'In Consultation' WHERE patient_id = :id");
            $stmt->execute([':id' => $patientId]);
            $db->commit();

            // Fetch patient name
            $stmtP = $db->prepare("SELECT first_name, last_name, token_number FROM patients WHERE patient_id = :id");
            $stmtP->execute([':id' => $patientId]);
            $p = $stmtP->fetch();

            db_log_activity($_SESSION['user_id'] ?? null, "Called patient $patientId to consultation", "Camp");
            db_add_notification(
                "Patient Called to consultation",
                "Token {$p['token_number']} ({$p['first_name']} {$p['last_name']}) called to consulting room.",
                "warning"
            );
            responseJson(true, 'Patient called.');
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            responseJson(false, $e->getMessage());
        }
        break;

    case 'complete_patient':
        $patientId = trim($_POST['id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }

        $res = db_checkout_attendance($patientId);
        if ($res['success']) {
            responseJson(true, 'Consultation completed.');
        } else {
            responseJson(false, 'Completion query failed.');
        }
        break;

    // -------------------------------------------------------------------------
    // Retrieve All System Alerts Notifications
    // -------------------------------------------------------------------------
    case 'get_notifications':
        $alerts = db_load_notifications();
        $mapped = [];
        foreach ($alerts as $a) {
            $mapped[] = [
                'id' => 'ALERT-' . $a['notification_id'],
                'title' => $a['title'],
                'message' => $a['message'],
                'type' => $a['type'],
                'category' => 'System',
                'priority' => ($a['type'] === 'danger' ? 'High' : ($a['type'] === 'warning' ? 'Medium' : 'Low')),
                'icon' => ($a['type'] === 'danger' ? 'bi-exclamation-triangle-fill' : ($a['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-bell-fill')),
                'time' => date('d M, h:i A', strtotime($a['created_at'])),
                'section' => 'Today',
                'unread' => ($a['status'] === 'Unread')
            ];
        }
        responseJson(true, 'System notifications', $mapped);
        break;

    case 'mark_notifications_read':
        try {
            $db = db_connect();
            $db->query("UPDATE notifications SET status = 'Read' WHERE status = 'Unread'");
            responseJson(true, 'Notifications marked read.');
        } catch (PDOException $e) {
            responseJson(false, $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Retrieve All Camps
    // -------------------------------------------------------------------------
    case 'get_camps':
        try {
            $db = db_connect();
            $stmt = $db->query(QUERY_ALL_CAMPS);
            $camps = $stmt->fetchAll();
            $mapped = [];
            foreach ($camps as $c) {
                $mapped[] = [
                    'id' => $c['camp_id'],
                    'name' => $c['camp_name'],
                    'region' => $c['location'],
                    'coordinator' => $c['coordinator'],
                    'doctor' => $c['doctor_name'],
                    'date' => date('Y-m-d', strtotime($c['date'])),
                    'startTime' => $c['start_time'],
                    'endTime' => $c['end_time'],
                    'status' => $c['status'],
                    'expectedPatients' => 200,
                    'currentPatients' => 120
                ];
            }
            responseJson(true, 'Camps list', $mapped);
        } catch (PDOException $e) {
            responseJson(false, $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Retrieve Today's Attendance Roster
    // -------------------------------------------------------------------------
    case 'get_attendance':
        try {
            $db = db_connect();
            $stmt = $db->query(QUERY_ATTENDANCE_ROSTER);
            $attendance = $stmt->fetchAll();
            $mapped = [];
            foreach ($attendance as $a) {
                $mapped[] = [
                    'patientId' => $a['patient_id'],
                    'patientName' => $a['first_name'] . ' ' . $a['last_name'],
                    'checkin' => $a['check_in'],
                    'checkout' => $a['check_out'],
                    'triagePriority' => $a['triage_priority'] ?? 'Low',
                    'vitalStatus' => $a['attendance_status']
                ];
            }
            responseJson(true, 'Attendance list', $mapped);
        } catch (PDOException $e) {
            responseJson(false, $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Retrieve Recent Verification Logs
    // -------------------------------------------------------------------------
    case 'get_verifications':
        try {
            $db = db_connect();
            $stmt = $db->query(QUERY_VERIFICATION_HISTORY);
            $verifications = $stmt->fetchAll();
            $mapped = [];
            foreach ($verifications as $v) {
                $mapped[] = [
                    'patientId' => $v['patient_id'],
                    'patientName' => $v['first_name'] . ' ' . $v['last_name'],
                    'method' => 'Aadhaar Biometric Scan',
                    'status' => $v['verification_status'],
                    'timestamp' => $v['verification_date'],
                    'remarks' => $v['remarks']
                ];
            }
            responseJson(true, 'Verifications list', $mapped);
        } catch (PDOException $e) {
            responseJson(false, $e->getMessage());
        }
        break;

    // -------------------------------------------------------------------------
    // Patient Record Deletion
    // -------------------------------------------------------------------------
    case 'delete_patient':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }
        $patientId = trim($_POST['id'] ?? '');
        if (empty($patientId)) {
            responseJson(false, 'Patient ID required.');
        }
        $res = db_delete_patient($patientId);
        if ($res['success']) {
            responseJson(true, 'Patient record terminated.');
        } else {
            responseJson(false, 'Deletion failed: ' . $res['message']);
        }
        break;

    // -------------------------------------------------------------------------
    // User Profile Settings Update
    // -------------------------------------------------------------------------
    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            responseJson(false, 'POST request required.');
        }

        $userId = $_SESSION['user_id'] ?? 1;
        $employeeId = $_SESSION['username'] ?? '';

        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? 'Male');
        $dob = sanitizeInput($_POST['dob'] ?? '');
        $assignedCamp = sanitizeInput($_POST['assignedCamp'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $department = sanitizeInput($_POST['department'] ?? '');
        $skills = sanitizeInput($_POST['skills'] ?? '');
        
        $expRaw = $_POST['experience'] ?? '12';
        preg_match('/\d+/', $expRaw, $matches);
        $experienceYears = intval($matches[0] ?? 12);

        try {
            $db = db_connect();
            $db->beginTransaction();

            // 1. Update users table
            $stmtUser = $db->prepare("
                UPDATE users 
                SET full_name = :name, email = :email 
                WHERE id = :id
            ");
            $stmtUser->execute([
                ':name' => $name,
                ':email' => $email,
                ':id' => $userId
            ]);

            // Update session values
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;

            // 2. Update health_workers table
            $stmtHw = $db->prepare("
                UPDATE health_workers 
                SET phone = :phone, dob = :dob, gender = :gender, 
                    address = :address, specialization = :spec, 
                    qualification = :qual, experience_years = :exp,
                    assigned_camp = :camp
                WHERE employee_id = :empid
            ");
            $stmtHw->execute([
                ':phone' => $phone,
                ':dob' => $dob,
                ':gender' => $gender,
                ':address' => $address,
                ':spec' => $department,
                ':qual' => $skills,
                ':exp' => $experienceYears,
                ':camp' => $assignedCamp,
                ':empid' => $employeeId
            ]);

            $db->commit();

            db_log_activity($userId, "Updated profile demographic card.", "Profile");
            responseJson(true, 'Profile saved successfully.');
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            responseJson(false, 'Update failed: ' . $e->getMessage());
        }
        break;

    default:
        responseJson(false, 'Unsupported API endpoint action: ' . $action);
        break;
}
