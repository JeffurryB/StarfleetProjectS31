<?php
// Initialize session memory immediately so config.php doesn't crash or throw errors
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Enforce standard dynamic API output headers
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['username'])) { 
    echo json_encode(['error' => 'USERNAME PARAMETER MISSING']); 
    exit; 
}

include 'config.php';
if (!isset($db)) { 
    echo json_encode(['error' => 'CRITICAL DATABASE SYSTEM CONNECTION DETACHED']); 
    exit; 
}

$user = mysqli_real_escape_string($db, $_GET['username']);
$result = mysqli_query($db, "SELECT id, log_name FROM Plogs WHERE username = '$user' ORDER BY created_at DESC");

if (!$result) {
    echo json_encode(['error' => 'DATABASE QUERY CONSOLE FAILURE']);
} else {
    $logs = [];
    while ($row = mysqli_fetch_assoc($result)) { $logs[] = $row; }
    echo json_encode($logs);
}
?>
