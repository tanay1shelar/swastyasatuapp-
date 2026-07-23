<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

jsonResponse(true, ['report' => 'System operational report generated'], 'Report generated successfully');
?>
