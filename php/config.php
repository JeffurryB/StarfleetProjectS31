<?php
$DB_SERVER = "YOUR SERVER NAME";
$DB_USERNAME = "DB USERNAME";
$DB_PASSWORD = "DB PASSWORD";
$DB_DATABASE = "DB NAME";
$DB_PORT = "DB PORT NUMBER";
$db = mysqli_connect($DB_SERVER, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE, $DB_PORT);
if(mysqli_connect_errno()) { die('Databse Connection Error - ' . mysqli_connect_error());}
// SECURITY FIX: Check for and auto-inject dynamic secure JSON configuration attributes
// This removes the Remote Code Execution vulnerability by handling settings strictly as safe data values.
$json_config_source = __DIR__ . '/group_config.json';

if (file_exists($json_config_source)) {
    $matrix_data = json_decode(file_get_contents($json_config_source), true) ?? [];
    
    // Dynamically define your original constants in system memory so no other files break
    if (!defined('GROUP_NAME'))     define('GROUP_NAME',     $matrix_data['GROUP_NAME'] ?? 'Starfleet Simulation');
    if (!defined('GROUP_ABBR'))     define('GROUP_ABBR',     $matrix_data['GROUP_ABBR'] ?? 'SFS');
    if (!defined('GROUP_LOGO'))     define('GROUP_LOGO',     $matrix_data['GROUP_LOGO'] ?? 'images/logo.png');
    if (!defined('DEFAULT_AVATAR')) define('DEFAULT_AVATAR', $matrix_data['DEFAULT_AVATAR'] ?? 'ProfilePics/default.png');
} else {
    // Fallback default variables just in case they haven't run setup yet - DO NOT CHANGE THESE SETTINGS BELOW!!!
    if (!defined('GROUP_NAME'))     define('GROUP_NAME',     'Starfleet Simulation');
    if (!defined('GROUP_ABBR'))     define('GROUP_ABBR',     'SFS');
    if (!defined('GROUP_LOGO'))     define('GROUP_LOGO',     'images/logo.png');
    if (!defined('DEFAULT_AVATAR')) define('DEFAULT_AVATAR', 'ProfilePics/default.png');
}
if (!defined('SYSTEM_DEMO_MODE')) {
    define('SYSTEM_DEMO_MODE', true);
}
// --- CENTRAL SYSTEM MAINTENANCE CHECK ---
$status_file = __DIR__ . '/maintenance_status.txt';
$maintenance_active = false;

if (file_exists($status_file)) {
    $maintenance_active = (trim(file_get_contents($status_file)) === '1');
}

// --- SECURE SESSION-BASED MAINTENANCE CHECK ---
//  SECURITY FIX: Replaced the broken REMOTE_ADDR IP array check with an adaptive session privilege gate.
// This allows you to preview the live site during maintenance regardless of proxy IP changes.
$is_admin = false;

// Pull the user tracking handle from active browser memory safely
$check_handle = $_SESSION['login_user'] ?? $login_session ?? '';

if (!empty($check_handle)) {
    // Cross-check the database to verify if this active user has Level 9 Administrative rights (dh = 1)
    //  REFACTOR FIX: Using a secure prepared statement to insulate the database query loop
    $gate_sql = "SELECT dh FROM accounts WHERE username = ? LIMIT 1";
    if ($stmt_gate = $db->prepare($gate_sql)) {
        $stmt_gate->bind_param("s", $check_handle);
        $stmt_gate->execute();
        $res_gate = $stmt_gate->get_result();
        
        if ($res_gate && $row_gate = $res_gate->fetch_assoc()) {
            if ((int)$row_gate['dh'] === 1) {
                $is_admin = true; // Admin status securely validated via session architecture!
            }
        }
        $stmt_gate->close();
    }
}

if ($maintenance_active && !$is_admin) {
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>LCARS - System Maintenance</title>
        <style>
            body { background-color: #000000; color: #ff9900; font-family: "Arial Custom", sans-serif; padding: 20px; text-transform: uppercase; }
            .lcars-frame { border-left: 25px solid #ff9900; border-top: 40px solid #cc6699; border-radius: 20px 0 0 0; padding: 20px; margin-top: 20px; }
            .alert-text { color: #cc0000; font-size: 2rem; font-weight: bold; animation: blink 1.5s infinite; }
            @keyframes blink { 50% { opacity: 0.3; } }
        </style>
    </head>
    <body>
        <div class="lcars-frame">
            <h1 class="alert-text">ALERT: PRIMARY COMPUTER CORE OFFLINE</h1>
            <p>The main computer network is undergoing scheduled diagnostic routines.</p>
            <p>Access restricted to Starfleet Engineering.</p>
        </div>
    </body>
    </html>
    <?php
    exit();
}
// --- END MAINTENANCE CHECK ---
?>
