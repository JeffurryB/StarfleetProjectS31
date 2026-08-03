<?php
    include('session.php');

// Safely define the variable from session or fallback to prevent undefined variable warnings
$login_session = $login_session ?? '';

// If the session identifier is missing completely, send them back to the landing page
if (empty($login_session)) {
    header("Location: index.php");
    exit();
}

if (!isset($db)) {
    include("config.php"); 
}
// Clean the session variable to protect against SQL injections
$auth_username = mysqli_real_escape_string($db, $login_session);

// Query database to check for System Admin clearance level (ID 1 or 2)
$sql_admin_check = "SELECT id FROM accounts WHERE username = '$auth_username' LIMIT 1";
$res_admin_check = mysqli_query($db, $sql_admin_check);

if ($res_admin_check && mysqli_num_rows($res_admin_check) == 1) {
    $user_data = mysqli_fetch_assoc($res_admin_check);
    $user_id = (int)$user_data['id'];
    
    // Strict enforcement: Only allow account IDs 1 and 2 access to the engineering switches
    if ($user_id !== 1 && $user_id !== 2) {
        header("Location: notauthorized.php?error=clearance_insufficient");
        exit();
    }
} else {
    // If user record disappears from the mapping array completely
    header("Location: index.php");
    exit();
}
// 1. DATA CORE CONTROL LOGIC
$status_file = __DIR__ . '/maintenance_status.txt';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_maintenance'])) {
    $current_status = file_exists($status_file) ? trim(file_get_contents($status_file)) : '0';
    $new_status = ($current_status === '1') ? '0' : '1';
    file_put_contents($status_file, $new_status);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$maintenance_is_on = false;
if (file_exists($status_file)) {
    $maintenance_is_on = (trim(file_get_contents($status_file)) === '1');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS - ENGINEERING UTILITY</title>
    <style>
        /* Shared LCARS Design Token Foundations */
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900; --lcars-pink: #cc6699;
            --lcars-blue: #33ccff; --lcars-dark-blue: #5588ff; --lcars-bg: #000000;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff; margin: 0; padding: 15px;
            font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif;
            text-transform: uppercase; letter-spacing: 1px; overflow-x: hidden;
        }
        
        /* Layout Structure Headers */
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top {
            background-color: var(--lcars-purple); height: 40px; flex-grow: 1;
            border-bottom-left-radius: 20px; margin-right: 15px; position: relative;
        }
        .lcars-bar-top::before {
            content: "SYS-MNG-402"; position: absolute; left: 25px; bottom: 3px;
            color: #000000; font-weight: bold; font-size: 14px;
        }
        .lcars-title { color: var(--lcars-orange); font-size: 28px; font-weight: 300; margin: 0; line-height: 1; white-space: nowrap; }
        .lcars-container { display: flex; min-height: calc(100vh - 120px); }
        
        /* Structural Side Panel Elements */
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow {
            background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px;
            border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative;
        }
        .lcars-elbow::after {
            content: ""; position: absolute; background-color: var(--lcars-bg);
            width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px;
        }
        .lcars-side-block {
            background-color: var(--lcars-blue); flex-grow: 1; min-height: 200px;
            border-bottom-left-radius: 20px; border-top: 10px solid var(--lcars-bg);
            position: relative;
        }
        .lcars-side-block::after {
            content: "SEC-GRID 09"; position: absolute; bottom: 15px; right: 10px;
            font-size: 11px; color: #000000; font-weight: bold;
        }

        /* Functional Content Panels */
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-user-banner { border-bottom: 4px solid var(--lcars-blue); padding-bottom: 10px; margin-bottom: 25px; }
        .lcars-user-banner h1 { margin: 0; font-size: 22px; color: var(--lcars-blue); font-weight: normal; }
        
        /* Master Console Display Elements */
        .console-box {
            background-color: #111116; border-left: 6px solid var(--lcars-pink);
            border-radius: 0 8px 8px 0; padding: 25px; margin-bottom: 25px;
        }
        .console-box h2 { margin: 0 0 15px 0; font-size: 18px; color: var(--lcars-pink); font-weight: normal; }
        .status-pill {
            display: inline-block; padding: 5px 15px; border-radius: 4px; 
            font-weight: bold; font-size: 14px; color: #000000; margin-bottom: 20px;
        }
        
        /* Controls & Form Interactions */
        .lcars-submit-btn {
            color: #000000; border: none; padding: 15px 30px; font-weight: bold;
            font-size: 13px; border-radius: 8px; cursor: pointer; text-transform: uppercase;
            letter-spacing: 1px; transition: background-color 0.2s, transform 0.2s;
            display: inline-block;
        }
        .lcars-submit-btn:hover { transform: scale(1.02); }
        
        /* Supplemental Readout Data Lists */
        .system-readout { margin: 25px 0 0 0; padding: 0; list-style: none; font-size: 12px; color: #aaaaaa; }
        .system-readout li { margin-bottom: 8px; display: flex; justify-content: space-between; max-width: 450px; border-bottom: 1px dashed #333; padding-bottom: 4px; }
        .system-readout span { color: var(--lcars-orange); }

        /* Dynamic Status Utility Styling */
        .state-alert { background-color: #cc3333; animation: systemPulse 2s infinite; }
        .state-nominal { background-color: #00cc66; }
        @keyframes systemPulse { 50% { background-color: #ff5555; } }
    </style>
</head>
<body>

    <!-- Header Frame Segment -->
    <div class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h1 class="lcars-title">CORE MANAGEMENT TERMINAL</h1>
    </div>

    <!-- Main Workspace Section -->
    <div class="lcars-container">
        
        <!-- Left Side Structural Array -->
        <div class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-side-block"></div>
        </div>

        <!-- Right Main Operation Deck -->
        <div class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>SYSTEM MATRIX OPERATING MATRIX</h1>
            </div>

            <div class="console-box">
                <h2>COMPUTER NETWORK INTERDICTION</h2>
                <p style="margin: 0 0 15px 0; font-size: 13px; color: #ffffff; text-transform: none;">
                    Use this override interface component to sever the external subspace transceiver pipeline. Enabling maintenance mode will block all non-authorized personnel from viewing operational index layouts.
                </p>

                <!-- Current Matrix State Feedback Indicator -->
                <div>
                    <span class="status-pill <?php echo $maintenance_is_on ? 'state-alert' : 'state-nominal'; ?>">
                        GRID STATUS: <?php echo $maintenance_is_on ? 'OFFLINE / INTERCEPT PATTERN ACTIVE' : 'ONLINE / FULL CLEARANCE NOMINAL'; ?>
                    </span>
                </div>

                <!-- Executable Action Interface Form -->
                <form method="POST">
                    <button type="submit" name="toggle_maintenance" class="lcars-submit-btn" style="background-color: <?php echo $maintenance_is_on ? 'var(--lcars-blue)' : '#cc3333'; ?>; color: <?php echo $maintenance_is_on ? '#000000' : '#ffffff'; ?>;">
                        <?php echo $maintenance_is_on ? 'Deactivate Lockout Mode (Go Live)' : 'Isolate Matrix Core Network'; ?>
                    </button>
                </form>

                <!-- Dynamic Telemetry Readout Fields -->
                <ul class="system-readout">
                    <li>NETWORK CORE NODE: <span>INFINITYFREE_CLUSTER_06</span></li>
                    <li>ROUTING INTERCEPT METHOD: <span>FILE_STREAM (CAPACITOR_TEXT)</span></li>
                    <li>ADMINISTRATOR BYPASS IDENTIFIER: <span><?php echo $_SERVER['REMOTE_ADDR']; ?></span></li>
                    <li>TELEMETRY TIMESTAMP VALUE: <span>STARDATE <?php echo sprintf("%.1f", (time() / 86400) + 47600); ?></span></li>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>
