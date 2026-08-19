<?php
// Include your existing security and database configuration layers
include 'session.php';
include 'config.php';

// ==========================================================
// 1. GATEKEEPER DEFENSE OVERRIDE (ANTI-DIRECT ACCESS SENSOR)
// ==========================================================
// Blocks users immediately if they try to access this processor file without a POST form submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: notauthorized.php");
    exit;
}

// Verify the active profile username variable exists from your session.php framework
$logged_username = isset($_SESSION['login_user']) ? $_SESSION['login_user'] : null;

if (!$logged_username) {
    header("Location: notauthorized.php");
    exit;
}

// Query the user accounts validation vector matrix
$auth = $conn->prepare("SELECT dh FROM accounts WHERE username = ? LIMIT 1");
$auth->bind_param("s", $logged_username);
$auth->execute();
$user_profile = $auth->get_result()->fetch_assoc();
$auth->close();

// Terminate execution and bounce user if profile flag does not match administrative status (dh !== 1)
if (!$user_profile || (int)$user_profile['dh'] !== 1) {
    header("Location: notauthorized.php");
    exit;
}


// ==========================================================
// 2. TRANSACTION PROCESSING SECTION A: RESERVED ASSET LAUNCH
// ==========================================================
if (isset($_POST['deploy_stored_ship'])) {
    $ship_to_activate = filter_input(INPUT_POST, 'activate_ship_id', FILTER_VALIDATE_INT);
    
    if ($ship_to_activate) {
        // Look up the assigned quadrant first to keep the ship inside its legal boundaries on wakeup
        $quad_chk = $conn->prepare("SELECT quadrant FROM `starships` WHERE id = ? LIMIT 1");
        $quad_chk->bind_param("i", $ship_to_activate);
        $quad_chk->execute();
        $quad_res = $quad_chk->get_result()->fetch_assoc();
        $quad_chk->close();
        
        $assigned_quad = ($quad_res && !empty($quad_res['quadrant'])) ? strtoupper($quad_res['quadrant']) : 'ALPHA';
        
        // Calculate bounded coordinates dynamically based on the current sector configuration rules
        $rand_x = rand(40, 360);
        $rand_y = rand(40, 210);
        
        if ($assigned_quad === 'BETA') {
            $rand_x = rand(440, 760); $rand_y = rand(40, 210);
        } elseif ($assigned_quad === 'GAMMA') {
            $rand_x = rand(40, 360); $rand_y = rand(290, 460);
        } elseif ($assigned_quad === 'DELTA') {
            $rand_x = rand(440, 760); $rand_y = rand(290, 460);
        }
        
        // Target structural database index row and shift status parameters to 'Active Duty'
        $upd = $conn->prepare("UPDATE `starships` SET status = 'Active Duty', pos_x = ?, pos_y = ? WHERE id = ?");
        $upd->bind_param("iii", $rand_x, $rand_y, $ship_to_activate);
        $upd->execute();
        $upd->close();
        
        // Return browser frame mapping loops to the grid panel interface
        header("Location: tactical_map.php");
        $conn->close();
        exit;
    }
}


// ==========================================================
// 3. TRANSACTION PROCESSING SECTION B: COMMISSION NEW REGISTRY
// ==========================================================
if (isset($_POST['deploy_ship'])) {
    $s_name = filter_input(INPUT_POST, 'ship_name', FILTER_DEFAULT);
    $s_ncc  = filter_input(INPUT_POST, 'ncc_number', FILTER_DEFAULT);
    $s_capt = filter_input(INPUT_POST, 'captain_name', FILTER_DEFAULT);
    $s_quad = filter_input(INPUT_POST, 'quadrant', FILTER_DEFAULT);
    
    // Fallback filter check to guarantee validation strings are safe
    $assigned_quad = $s_quad ? strtoupper(trim($s_quad)) : 'ALPHA';
    
    if ($s_name && $s_ncc && $s_capt) {
        // Calculate correct positioning variables using sector layout targets
        $rand_x = rand(40, 360);
        $rand_y = rand(40, 210);
        
        if ($assigned_quad === 'BETA') {
            $rand_x = rand(440, 760); $rand_y = rand(40, 210);
        } elseif ($assigned_quad === 'GAMMA') {
            $rand_x = rand(40, 360); $rand_y = rand(290, 460);
        } elseif ($assigned_quad === 'DELTA') {
            $rand_x = rand(440, 760); $rand_y = rand(290, 460);
        }
        
        // Commit new hull profile directly to active grid duty logs including the quadrant configuration
        $ins = $conn->prepare("INSERT INTO `starships` (ship_name, ncc_number, captain_name, is_enemy, status, quadrant, pos_x, pos_y) VALUES (?, ?, ?, 0, 'Active Duty', ?, ?, ?)");
        $ins->bind_param("ssssii", $s_name, $s_ncc, $s_capt, $assigned_quad, $rand_x, $rand_y);
        $ins->execute();
        $ins->close();
        
        header("Location: tactical_map.php");
        $conn->close();
        exit;
    }
}

// Catch-all protection redirect map sequence back to the tactical screen
header("Location: tactical_map.php");
$conn->close();
exit;
?>
