<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Shared Reusable Functions (functions.php)
 * 
 * Implements high-level interfaces for patients registrations, verifications,
 * attendance, audit logs, and dashboard metrics queries.
 */

// Prevent direct access to config files
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/queries.php';
require_once __DIR__ . '/helpers.php';

// =========================================================================
// 1. GENERATOR FUNCTIONS
// =========================================================================
function db_generate_patient_id() {
    $db = db_connect();
    $stmt = $db->query("SELECT COUNT(*) FROM patients");
    $count = $stmt->fetchColumn();
    return "PAT-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

function db_generate_reg_number() {
    return "REG-" . rand(200000, 299999);
}

function db_generate_token_number() {
    $db = db_connect();
    $stmt = $db->query("SELECT COUNT(*) FROM patients");
    $count = $stmt->fetchColumn();
    return "#" . (201 + $count);
}

function db_generate_queue_number() {
    $db = db_connect();
    $stmt = $db->query("SELECT COUNT(*) FROM patients");
    $count = $stmt->fetchColumn();
    return "Q-" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

// =========================================================================
// 2. PATIENT CRUD FUNCTIONS
// =========================================================================
function db_insert_patient($data) {
    try {
        $db = db_connect();
        $stmt = $db->prepare(QUERY_INSERT_PATIENT);
        
        $pId = db_generate_patient_id();
        $regNum = db_generate_reg_number();
        $token = db_generate_token_number();
        $age = $data['age'] ?? 0;
        $bmi = calculateBmi($data['height'] ?? 0, $data['weight'] ?? 0);

        $params = [
            ':patient_id' => $pId,
            ':registration_number' => $regNum,
            ':token_number' => $token,
            ':first_name' => $data['first_name'],
            ':middle_name' => $data['middle_name'] ?? '',
            ':last_name' => $data['last_name'],
            ':gender' => $data['gender'],
            ':dob' => $data['dob'],
            ':age' => $age,
            ':blood_group' => $data['blood_group'] ?? 'O+',
            ':aadhaar' => $data['aadhaar'],
            ':phone' => $data['phone'],
            ':alternate_phone' => $data['alternate_phone'] ?? '',
            ':email' => $data['email'] ?? '',
            ':occupation' => $data['occupation'] ?? '',
            ':address' => $data['address'],
            ':village' => $data['village'] ?? '',
            ':taluka' => $data['taluka'] ?? '',
            ':district' => $data['district'] ?? '',
            ':state' => $data['state'] ?? '',
            ':pincode' => $data['pincode'] ?? '',
            ':height' => $data['height'] ?? 0,
            ':weight' => $data['weight'] ?? 0,
            ':bmi' => $bmi,
            ':blood_pressure' => $data['blood_pressure'] ?? '120/80',
            ':pulse' => $data['pulse'] ?? 72,
            ':temperature' => $data['temperature'] ?? 98.4,
            ':medical_history' => $data['medical_history'] ?? 'None',
            ':allergies' => $data['allergies'] ?? 'None',
            ':current_medication' => $data['current_medication'] ?? 'None',
            ':guardian_name' => $data['guardian_name'] ?? 'n/a',
            ':guardian_phone' => $data['guardian_phone'] ?? 'n/a',
            ':camp_id' => $data['camp_id'] ?? null,
            ':status' => 'Registered',
            ':photo' => $data['photo'] ?? 'assets/img/avatars/patient-default.png',
            ':document_path' => $data['document_path'] ?? null
        ];

        $stmt->execute($params);

        // Auto-generate consultation queue record
        $stmtQueue = $db->prepare("
            INSERT INTO patient_attendance (
                patient_id, check_in, check_out, attendance_status, token_number, triage_priority
            ) VALUES (
                :patient_id, :check_in, '--', 'Present', :token, 'Low'
            )
        ");
        $stmtQueue->execute([
            ':patient_id' => $pId,
            ':check_in' => date('h:i A'),
            ':token' => $token
        ]);

        // Auto log activity
        db_log_activity($_SESSION['user_id'] ?? null, "Registered patient $pId ({$data['first_name']})", "Patients");
        
        // Auto system notification
        db_add_notification(
            "New Patient Registered",
            "Attendee {$data['first_name']} {$data['last_name']} registered successfully under Token $token.",
            "info"
        );

        return ['success' => true, 'patient_id' => $pId, 'token' => $token];
    } catch (PDOException $e) {
        error_log("Insert Patient Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function db_update_patient($patient_id, $data) {
    try {
        $db = db_connect();
        
        // Calculate age
        $dob = new DateTime($data['dob']);
        $today = new DateTime('today');
        $age = $dob->diff($today)->y;

        $stmt = $db->prepare(QUERY_UPDATE_PATIENT);
        $stmt->execute([
            ':patient_id' => $patient_id,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':gender' => $data['gender'],
            ':dob' => $data['dob'],
            ':age' => $age,
            ':blood_group' => $data['blood_group'] ?? 'O+',
            ':phone' => $data['phone'],
            ':email' => $data['email'] ?? 'n/a',
            ':address' => $data['address'],
            ':emergency_contact' => $data['emergency_contact'] ?? 'n/a',
            ':allergies' => $data['allergies'] ?? 'None',
            ':medical_history' => $data['medical_history'] ?? 'None',
            ':current_medication' => $data['current_medication'] ?? 'None',
            ':guardian_name' => $data['guardian_name'] ?? 'n/a',
            ':guardian_phone' => $data['guardian_phone'] ?? 'n/a',
            ':camp_id' => $data['camp_id'] ?? null,
            ':status' => (($data['status'] ?? 'Registered') === 'Waiting' ? 'Registered' : ($data['status'] ?? 'Registered'))
        ]);

        db_log_activity($_SESSION['user_id'] ?? null, "Updated details for patient $patient_id", "Patients");
        
        db_add_notification(
            "Patient Details Updated",
            "Health records updated successfully for patient ID $patient_id.",
            "info"
        );

        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Update Patient Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function db_delete_patient($patient_id) {
    try {
        $db = db_connect();
        
        // Verify patient exists first
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = :patient_id");
        $stmtCheck->execute([':patient_id' => $patient_id]);
        if ($stmtCheck->fetchColumn() == 0) {
            return ['success' => false, 'message' => 'Patient does not exist.'];
        }

        // Remove from related tables
        $stmt1 = $db->prepare("DELETE FROM patient_attendance WHERE patient_id = :patient_id");
        $stmt1->execute([':patient_id' => $patient_id]);
        
        $stmt2 = $db->prepare("DELETE FROM patient_verification WHERE patient_id = :patient_id");
        $stmt2->execute([':patient_id' => $patient_id]);

        $stmt = $db->prepare(QUERY_DELETE_PATIENT);
        $stmt->execute([':patient_id' => $patient_id]);

        db_log_activity($_SESSION['user_id'] ?? null, "Deleted patient record card $patient_id", "Patients");
        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Delete Patient Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function db_search_patient($query) {
    try {
        $db = db_connect();
        $search = "%" . $query . "%";
        $stmt = $db->prepare("
            SELECT p.*, c.camp_name, att.triage_priority
            FROM patients p 
            LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
            LEFT JOIN (
                SELECT patient_id, triage_priority 
                FROM patient_attendance 
                WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
            ) att ON p.patient_id = att.patient_id
            WHERE p.patient_id LIKE :q1 OR p.first_name LIKE :q2 OR p.last_name LIKE :q3 OR p.phone LIKE :q4 OR p.aadhaar LIKE :q5
            LIMIT 10
        ");
        $stmt->execute([
            ':q1' => $search,
            ':q2' => $search,
            ':q3' => $search,
            ':q4' => $search,
            ':q5' => $search
        ]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Search Patient Error: " . $e->getMessage());
        return [];
    }
}

// =========================================================================
// 3. VERIFICATION FUNCTIONS
// =========================================================================
function db_verify_patient($patient_id, $status, $userId, $remarks) {
    try {
        $db = db_connect();
        $db->beginTransaction();

        // 1. Update patient status
        $stmtPat = $db->prepare("UPDATE patients SET status = :status WHERE patient_id = :patient_id");
        $stmtPat->execute([':status' => $status, ':patient_id' => $patient_id]);

        // 2. Log verification checklist details
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM patient_verification WHERE patient_id = :patient_id");
        $stmtCheck->execute([':patient_id' => $patient_id]);
        $exists = $stmtCheck->fetchColumn() > 0;

        if ($exists) {
            $stmtVer = $db->prepare("
                UPDATE patient_verification 
                SET verification_status = :status, 
                    verified_by = :verified_by, 
                    verification_date = NOW(), 
                    remarks = :remarks
                WHERE patient_id = :patient_id
            ");
        } else {
            $stmtVer = $db->prepare("
                INSERT INTO patient_verification (patient_id, verification_status, verified_by, verification_date, remarks)
                VALUES (:patient_id, :status, :verified_by, NOW(), :remarks)
            ");
        }
        
        $stmtVer->execute([
            ':patient_id' => $patient_id,
            ':status' => $status === 'Verified' ? 'Verified' : 'Rejected',
            ':verified_by' => $userId,
            ':remarks' => $remarks
        ]);

        $db->commit();

        db_log_activity($userId, "Performed identity check on $patient_id. Outcome: $status", "Verifications");
        
        db_add_notification(
            $status === 'Verified' ? "Patient Identity Verified" : "Verification Rejected",
            $status === 'Verified' 
                ? "Biometrics audit trail matched successfully for patient $patient_id." 
                : "Demographics checks rejected for patient $patient_id due to: $remarks.",
            $status === 'Verified' ? "success" : "danger"
        );

        return ['success' => true];
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Verify Patient Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// =========================================================================
// 4. ATTENDANCE FUNCTIONS
// =========================================================================
function db_mark_attendance($patient_id, $status, $token, $triage_priority = 'Low') {
    try {
        $db = db_connect();
        
        // Fetch patient name & details
        $stmtP = $db->prepare("SELECT first_name, last_name, camp_id FROM patients WHERE patient_id = :patient_id");
        $stmtP->execute([':patient_id' => $patient_id]);
        $p = $stmtP->fetch();
        if (!$p) return ['success' => false, 'message' => 'Patient record not found.'];

        $db->beginTransaction();

        // 1. Update status to 'In Triage'
        $stmtPat = $db->prepare("UPDATE patients SET status = 'In Triage' WHERE patient_id = :patient_id");
        $stmtPat->execute([':patient_id' => $patient_id]);

        // Check if attendance record already exists for this patient
        $stmtCheck = $db->prepare("SELECT COUNT(*) FROM patient_attendance WHERE patient_id = :patient_id");
        $stmtCheck->execute([':patient_id' => $patient_id]);
        $exists = $stmtCheck->fetchColumn() > 0;

        $inTime = date('h:i A');

        if ($exists) {
            // 2. Update existing attendance row
            $stmtAtt = $db->prepare("
                UPDATE patient_attendance 
                SET attendance_status = :status, triage_priority = :triage_priority, check_in = :check_in
                WHERE patient_id = :patient_id
            ");
            $stmtAtt->execute([
                ':patient_id' => $patient_id,
                ':status' => $status,
                ':triage_priority' => $triage_priority,
                ':check_in' => $inTime
            ]);
        } else {
            // 2. Insert attendance row
            $stmtAtt = $db->prepare("
                INSERT INTO patient_attendance (patient_id, check_in, check_out, attendance_status, token_number, triage_priority)
                VALUES (:patient_id, :check_in, '--', :status, :token, :triage_priority)
            ");
            $stmtAtt->execute([
                ':patient_id' => $patient_id,
                ':check_in' => $inTime,
                ':status' => $status,
                ':token' => $token,
                ':triage_priority' => $triage_priority
            ]);
        }

        $db->commit();

        db_log_activity($_SESSION['user_id'] ?? null, "Marked Present: Patient $patient_id checked in.", "Attendance");
        
        db_add_notification(
            "Attendance Checked In",
            "Attendee {$p['first_name']} {$p['last_name']} checked in. Priority set to {$triage_priority}.",
            "success"
        );

        return ['success' => true];
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Mark Attendance Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function db_update_attendance($patient_id, $status, $triage_priority, $check_in, $check_out = '--') {
    try {
        $db = db_connect();
        $db->beginTransaction();

        // 1. Fetch patient details
        $stmtP = $db->prepare("SELECT first_name, last_name FROM patients WHERE patient_id = :patient_id");
        $stmtP->execute([':patient_id' => $patient_id]);
        $p = $stmtP->fetch();
        if (!$p) return ['success' => false, 'message' => 'Patient record not found.'];

        // 2. Update patient_attendance table
        $stmtAtt = $db->prepare("
            UPDATE patient_attendance 
            SET attendance_status = :status, triage_priority = :triage_priority, check_in = :check_in, check_out = :check_out
            WHERE patient_id = :patient_id
        ");
        $stmtAtt->execute([
            ':patient_id' => $patient_id,
            ':status' => $status,
            ':triage_priority' => $triage_priority,
            ':check_in' => $check_in,
            ':check_out' => $check_out
        ]);

        $db->commit();

        db_log_activity($_SESSION['user_id'] ?? null, "Updated Attendance: Patient $patient_id check-in updated.", "Attendance");
        
        db_add_notification(
            "Attendance Record Updated",
            "Attendee {$p['first_name']} {$p['last_name']} check-in details updated. Priority set to {$triage_priority}.",
            "info"
        );

        return ['success' => true];
    } catch (PDOException $e) {
        if ($db->inTransaction()) $db->rollBack();
        error_log("Update Attendance Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function db_checkout_attendance($patient_id) {
    try {
        $db = db_connect();
        $db->beginTransaction();

        // 1. Update status to 'Completed'
        $stmtPat = $db->prepare("UPDATE patients SET status = 'Completed' WHERE patient_id = :patient_id");
        $stmtPat->execute([':patient_id' => $patient_id]);

        // 2. Update checkout time
        $stmtAtt = $db->prepare("
            UPDATE patient_attendance 
            SET check_out = :checkout 
            WHERE patient_id = :patient_id AND check_out = '--'
        ");
        $outTime = date('h:i A');
        $stmtAtt->execute([
            ':checkout' => $outTime,
            ':patient_id' => $patient_id
        ]);

        $db->commit();

        db_log_activity($_SESSION['user_id'] ?? null, "Checked out patient $patient_id", "Attendance");
        
        db_add_notification(
            "Attendance Checked Out",
            "Patient $patient_id completed clinic checklist and checked out.",
            "info"
        );

        return ['success' => true];
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Checkout Attendance Error: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// =========================================================================
// 5. DASHBOARD & NOTIFICATION UTILITIES
// =========================================================================
function db_load_dashboard_statistics() {
    try {
        $db = db_connect();
        
        $total = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        $verified = $db->query("SELECT COUNT(*) FROM patients WHERE status IN ('Verified', 'Completed', 'In Consultation')")->fetchColumn();
        $camps = $db->query("SELECT COUNT(*) FROM medical_camps WHERE status = 'Active'")->fetchColumn();
        $checkins = $db->query("SELECT COUNT(*) FROM patient_attendance WHERE check_in != '--'")->fetchColumn();
        
        $pending = $db->query("SELECT COUNT(*) FROM patients WHERE status IN ('Registered', 'Pending ID', 'Waiting')")->fetchColumn();
        $waiting = $db->query("SELECT COUNT(*) FROM patients WHERE status IN ('In Triage', 'In Consultation', 'Registered')")->fetchColumn();

        return [
            'total_patients' => $total,
            'verified_patients' => $verified,
            'active_camps' => $camps,
            'checkins_today' => $checkins,
            'pending_verifications' => $pending,
            'waiting_queue' => $waiting
        ];
    } catch (PDOException $e) {
        error_log("Dashboard Statistics Error: " . $e->getMessage());
        return [
            'total_patients' => 0, 'verified_patients' => 0, 'active_camps' => 0,
            'checkins_today' => 0, 'pending_verifications' => 0, 'waiting_queue' => 0
        ];
    }
}

function db_load_notifications() {
    try {
        $db = db_connect();
        $stmt = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 15");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Load Notifications Error: " . $e->getMessage());
        return [];
    }
}

function db_add_notification($title, $message, $type) {
    try {
        $db = db_connect();
        $stmt = $db->prepare(QUERY_INSERT_NOTIFICATION);
        $stmt->execute([
            ':title' => $title,
            ':message' => $message,
            ':type' => $type
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Add Notification Error: " . $e->getMessage());
        return false;
    }
}

function db_log_activity($userId, $activity, $module) {
    try {
        $db = db_connect();
        $stmt = $db->prepare(QUERY_INSERT_LOG);
        $stmt->execute([
            ':user_id' => $userId,
            ':activity' => $activity,
            ':module' => $module
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Log Activity Error: " . $e->getMessage());
        return false;
    }
}
