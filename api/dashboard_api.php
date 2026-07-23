<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$stats = [
    'total_camps' => 24,
    'patients_treated' => 343,
    'medicines_issued' => 10,
    'disease_stats' => [
        'general_checkups' => 42,
        'hypertension' => 34,
        'pediatric' => 24
    ]
];

jsonResponse(true, ['stats' => $stats], 'Dashboard metrics retrieved');
?>
