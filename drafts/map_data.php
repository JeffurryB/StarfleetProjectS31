<?php
// Prevent PHP from dumping raw HTML error text down the stream
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'session.php';
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        "error" => "Database link identification failed.",
        "details" => isset($conn) ? $conn->connect_error : "Variable \$conn is not initialized."
    ]);
    exit;
}

function getQuadrantCoordinates($quadrant) {
    $quad = strtoupper(trim($quadrant));
    switch ($quad) {
        case 'BETA':  return ['x' => rand(440, 760), 'y' => rand(40, 210)];
        case 'GAMMA': return ['x' => rand(40, 360), 'y' => rand(290, 460)];
        case 'DELTA': return ['x' => rand(440, 760), 'y' => rand(290, 460)];
        case 'ALPHA':
        default:      return ['x' => rand(40, 360), 'y' => rand(40, 210)];
    }
}

try {
    // 1. CHRONOMETER ANALYSIS (Movement ticks every 10 minutes)
    $time_check_query = "SELECT MAX(last_update) AS last_tick FROM `starships` WHERE status LIKE '%Active Duty%'";
    $time_res = $conn->query($time_check_query);
    $check_time = $time_res->fetch_assoc();
    $minutes_passed = $check_time['last_tick'] ? (time() - strtotime($check_time['last_tick'])) / 60 : 0;

    if ($minutes_passed >= 10 || !$check_time['last_tick']) {
        $ship_res = $conn->query("SELECT id, quadrant FROM `starships` WHERE status LIKE '%Active Duty%'");
        $upd_stmt = $conn->prepare("UPDATE `starships` SET pos_x = ?, pos_y = ?, last_update = NOW() WHERE id = ?");
        while ($ship_row = $ship_res->fetch_assoc()) {
            $coords = getQuadrantCoordinates($ship_row['quadrant']);
            $upd_stmt->bind_param("iii", $coords['x'], $coords['y'], $ship_row['id']);
            $upd_stmt->execute();
        }
        $upd_stmt->close();
        
        // Spawn standard background intruder if zero hostile profiles match active criteria
        $enemy_check = $conn->query("SELECT id FROM `starships` WHERE is_enemy = 1 AND status LIKE '%Active Duty%' LIMIT 1");
        if ($enemy_check && $enemy_check->num_rows === 0) {
            $enemy_name = "UNKNOWN INTRUDER";
            $enemy_ncc  = "NCC-UNKNOWN";
            $quadrants  = ['ALPHA', 'BETA', 'GAMMA', 'DELTA'];
            $assigned_quad = $quadrants[array_rand($quadrants)];
            $coords = getQuadrantCoordinates($assigned_quad);
            
            $stmt = $conn->prepare("INSERT INTO `starships` (ship_name, ncc_number, captain_name, is_enemy, status, quadrant, pos_x, pos_y) VALUES (?, ?, 'UNKNOWN', 1, 'Active Duty', ?, ?, ?)");
            $stmt->bind_param("sssii", $enemy_name, $enemy_ncc, $assigned_quad, $coords['x'], $coords['y']);
            $stmt->execute();
            $stmt->close();
        }
    }

    // 2. DATA QUERY FEED
    $result = $conn->query("SELECT id, ship_name, ncc_number, captain_name, is_enemy, quadrant, pos_x, pos_y FROM `starships` WHERE status LIKE '%Active Duty%'");
    $ships = [];
    while ($row = $result->fetch_assoc()) {
        $ships[] = [
            'id'           => (int)$row['id'],
            'ship_name'    => htmlspecialchars($row['ship_name']),
            'ncc_number'   => htmlspecialchars($row['ncc_number']),
            'captain_name' => htmlspecialchars($row['captain_name']),
            'is_enemy'     => (int)$row['is_enemy'],
            'quadrant'     => htmlspecialchars($row['quadrant']),
            'x'            => (int)$row['pos_x'],
            'y'            => (int)$row['pos_y']
        ];
    }
    echo json_encode($ships);
} catch (Exception $e) {
    echo json_encode(["error" => "Internal tactical system error.", "details" => $e->getMessage()]);
}
$conn->close();
?>
