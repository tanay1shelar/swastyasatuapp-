<?php
/**
 * Healthcare & Medical Camp Management System (HMCMS)
 * Central Queries & SQL Repository (queries.php)
 * 
 * Declares reusable queries and statement executions.
 */

// Prevent direct access to config files
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access denied.");
}

// =========================================================================
// 1. PATIENTS QUERIES
// =========================================================================
const QUERY_SELECT_ALL_PATIENTS = "
    SELECT p.*, c.camp_name, att.triage_priority
    FROM patients p 
    LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
    LEFT JOIN (
        SELECT patient_id, triage_priority 
        FROM patient_attendance 
        WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
    ) att ON p.patient_id = att.patient_id
    ORDER BY p.created_at DESC
";

const QUERY_SELECT_PATIENT_BY_ID = "
    SELECT p.*, c.camp_name, att.triage_priority
    FROM patients p 
    LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
    LEFT JOIN (
        SELECT patient_id, triage_priority 
        FROM patient_attendance 
        WHERE attendance_id IN (SELECT MAX(attendance_id) FROM patient_attendance GROUP BY patient_id)
    ) att ON p.patient_id = att.patient_id
    WHERE p.patient_id = :patient_id
";

const QUERY_INSERT_PATIENT = "
    INSERT INTO patients (
        patient_id, registration_number, token_number, first_name, middle_name, last_name,
        gender, dob, age, blood_group, aadhaar, phone, alternate_phone, email, occupation,
        address, village, taluka, district, state, pincode, height, weight, bmi,
        blood_pressure, pulse, temperature, medical_history, allergies, current_medication,
        guardian_name, guardian_phone, camp_id, status, photo, document_path
    ) VALUES (
        :patient_id, :registration_number, :token_number, :first_name, :middle_name, :last_name,
        :gender, :dob, :age, :blood_group, :aadhaar, :phone, :alternate_phone, :email, :occupation,
        :address, :village, :taluka, :district, :state, :pincode, :height, :weight, :bmi,
        :blood_pressure, :pulse, :temperature, :medical_history, :allergies, :current_medication,
        :guardian_name, :guardian_phone, :camp_id, :status, :photo, :document_path
    )
";

const QUERY_UPDATE_PATIENT = "
    UPDATE patients SET
        first_name = :first_name, last_name = :last_name, gender = :gender, dob = :dob, age = :age,
        blood_group = :blood_group, phone = :phone, email = :email, address = :address, emergency_contact = :emergency_contact,
        allergies = :allergies, medical_history = :medical_history, current_medication = :current_medication,
        guardian_name = :guardian_name, guardian_phone = :guardian_phone, camp_id = :camp_id, status = :status
    WHERE patient_id = :patient_id
";

const QUERY_DELETE_PATIENT = "
    DELETE FROM patients WHERE patient_id = :patient_id
";

// =========================================================================
// 2. VERIFICATIONS QUERIES
// =========================================================================
const QUERY_PENDING_VERIFICATIONS = "
    SELECT p.*, c.camp_name 
    FROM patients p 
    LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
    WHERE p.status IN ('Registered', 'Rejected', 'Pending ID')
";

const QUERY_VERIFICATION_HISTORY = "
    SELECT v.*, p.first_name, p.last_name 
    FROM patient_verification v
    JOIN patients p ON v.patient_id = p.patient_id
    ORDER BY v.verification_date DESC
";

// =========================================================================
// 3. ATTENDANCE QUERIES
// =========================================================================
const QUERY_VERIFIED_PATIENTS = "
    SELECT p.*, c.camp_name 
    FROM patients p 
    LEFT JOIN medical_camps c ON p.camp_id = c.camp_id 
    WHERE p.status = 'Verified'
";

const QUERY_ATTENDANCE_ROSTER = "
    SELECT a.*, p.first_name, p.last_name, p.gender, p.age 
    FROM patient_attendance a
    JOIN patients p ON a.patient_id = p.patient_id
    ORDER BY a.attendance_id DESC
";

// =========================================================================
// 4. CAMPS & NOTIFICATIONS & ACTIVITY QUERIES
// =========================================================================
const QUERY_ALL_CAMPS = "
    SELECT * FROM medical_camps ORDER BY date DESC
";

const QUERY_ALL_NOTIFICATIONS = "
    SELECT * FROM notifications ORDER BY created_at DESC
";

const QUERY_INSERT_NOTIFICATION = "
    INSERT INTO notifications (title, message, type, status) 
    VALUES (:title, :message, :type, 'Unread')
";

const QUERY_INSERT_LOG = "
    INSERT INTO activity_logs (user_id, activity, module) 
    VALUES (:user_id, :activity, :module)
";
