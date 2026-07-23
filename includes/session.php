<?php
/**
 * System Session Manager
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkUserAuth() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return true;
}
?>
