<?php
// 1. SYSTEM CONTEXT INITIALIZATION
include("config.php");   
include("session.php");  
include("functions.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

mysqli_set_charset($db, "utf8");

// 2. ENFORCE STRICT LEVEL 9 ADMINISTRATIVE PRIVILEGES
$auth_username = mysqli_real_escape_string($db, $login_session);
$sql_auth = "SELECT dh FROM accounts WHERE username = '$auth_username' LIMIT 1";
$res_auth = mysqli_query($db, $sql_auth);

if ($res_auth && mysqli_num_rows($res_auth) == 1) {
    $auth_data = mysqli_fetch_assoc($res_auth);
    if ((int)$auth_data['dh'] !== 1) {
        header("Location: notauthorized.php?error=clearance_insufficient");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

$status_msg = "";
$status_type = "";

// 3. EXECUTE EMERGENCY SYSTEM-WIDE REPOSITORY TRUNCATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'purge_all_transmissions') {
    $auth_confirmation = trim($_POST['auth_confirmation']);
    
    // Safety check matching their active logged-in administrative user handle
    if ($auth_confirmation !== $login_session) {
        $status_msg = "CRITICAL EXCEPTION: ADMINISTRATIVE SIGNATURE MISMATCH // PURGE SEQUENCE REJECTED.";
        $status_type = "failure";
    } else {
        // TRUNCATE instantly drops all table contents and resets the auto-increment keys to 0
        $purge_sql = "TRUNCATE TABLE `messages`";
        
        if (mysqli_query($db, $purge_sql)) {
            $status_msg = "SUCCESS | SYSTEM-WIDE MESSAGE ARCHIVE HAS BEEN FULLY PURGED TO BASE SECTORS.";
            $status_type = "success";
        } else {
            $status_msg = "CRITICAL EXCEPTION: HARDWARE INTERFACE FAULT // PURGE SEQUENCE TIMED OUT.";
            $status_type = "failure";
        }
    }
}

// 4. METRIC TELEMETRY LOOKUP: CALCULATE THE SIZE OF CURRENT OVERLOAD
$count_sql = "SELECT COUNT(*) as current_total FROM `messages`";
$count_res = mysqli_query($db, $count_sql);
$total_rows = 0;

if ($count_res) {
    $count_row = mysqli_fetch_assoc($count_res);
    $total_rows = (int)$count_row['current_total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RP GROUP - Emergency Archive Depletion Terminal</title>
    <style>
        :root { --lcars-red: #cc3333; --lcars-dark-red: #551111; --lcars-orange: #ff9900; --lcars-pink: #cc6699; --lcars-blue: #33ccff; --lcars-bg: #000000; }
        body { background-color: var(--lcars-bg); color: #ffffff; font-family: "Arial Custom", "Helvetica Neue", Arial, sans-serif; margin: 0; padding: 15px; text-transform: uppercase; letter-spacing: 1px; overflow-x: hidden; }
        
        /* FLASHING RED ALERT CORE INTERFACE SPECIFICATION */
        @keyframes redAlertPulse {
            0% { background-color: var(--lcars-red); box-shadow: 0 0 10px var(--lcars-red); }
            50% { background-color: var(--lcars-dark-red); box-shadow: none; }
            100% { background-color: var(--lcars-red); box-shadow: 0 0 10px var(--lcars-red); }
        }
        @keyframes textWarningFlash {
            0% { color: #ffffff; opacity: 1; }
            50% { color: #ff5555; opacity: 0.3; }
            100% { color: #ffffff; opacity: 1; }
        }

        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { animation: redAlertPulse 1.5s infinite ease-in-out; height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-PURGE-911"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { animation: textWarningFlash 1.5s infinite ease-in-out; font-size: 28px; font-weight: bold; margin: 0; line-height: 1; white-space: nowrap; }
        
        .lcars-container { display: flex; min-height: calc(100vh - 120px); }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { animation: redAlertPulse 1.5s infinite ease-in-out; height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-menu { display: flex; flex-direction: column; gap: 8px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px 15px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; border-radius: 5px 0 0 5px; border: none; cursor: pointer; text-transform: uppercase; }
        .lcars-btn:hover { background-color: #ffcc00; }
        .btn-blue { background-color: var(--lcars-blue); } .btn-blue:hover { background-color: #88e2ff; }
        .btn-alert { background-color: var(--lcars-red); color: #ffffff; } .btn-alert:hover { background-color: #ff5555; }
        
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-user-banner { border-bottom: 4px solid var(--lcars-red); padding-bottom: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .lcars-user-banner h1 { margin: 0; font-size: 22px; color: var(--lcars-red); font-weight: normal; }
        .system-status { font-size: 12px; color: var(--lcars-red); font-weight: bold; }
        
        .lcars-critical-panel { background-color: #1a0505; border: 2px solid var(--lcars-red); border-left-width: 8px; padding: 25px; border-radius: 0 8px 8px 0; margin-bottom: 25px; box-shadow: inset 0 0 15px rgba(204,51,51,0.15); }
        .lcars-critical-panel h3 { margin: 0 0 15px 0; font-size: 20px; color: #ff5555; letter-spacing: 2px; }
        .lcars-critical-panel p { font-size: 13px; line-height: 1.6; color: #ffcccc; text-transform: none; margin: 0 0 20px 0; }
        
        .telemetry-row { display: flex; gap: 20px; margin-bottom: 25px; }
        .telemetry-node { background: #000; border: 1px solid var(--lcars-red); padding: 15px; border-radius: 4px; flex-grow: 1; text-align: center; }
        .telemetry-label { color: var(--lcars-blue); font-size: 11px; margin-bottom: 5px; }
        .telemetry-value { font-size: 24px; font-weight: bold; color: #ffffff; font-family: monospace; }
        
        .lcars-input { background-color: #000000; border: 2px solid var(--lcars-red); color: #ffffff; padding: 12px; font-size: 14px; border-radius: 4px; font-family: inherit; width: 100%; box-sizing: border-box; text-transform: none; margin-bottom: 20px; text-align: center; font-weight: bold; letter-spacing: 2px; }
        .lcars-input:focus { outline: none; border-color: var(--lcars-orange); }
        
        .telemetry-banner { padding: 15px; font-weight: bold; font-size: 13px; margin-bottom: 25px; border-radius: 4px; border-left: 6px solid; text-transform: none; }
        .telemetry-success { background-color: #112211; color: #55ff55; border-left-color: var(--lcars-orange); }
        .telemetry-failure { background-color: #331111; color: #ff5555; border-left-color: var(--lcars-red); }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">⚠️ SYSTEM REPOSITORY PURGE WARNING ⚠️</h2>
    </header>

    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="dhpanel.php" class="lcars-btn">DH PANEL</a>
                <a href="welcome.php" class="lcars-btn btn-blue">MAIN TERM</a>
                <a href="dh_delete.php" class="lcars-btn btn-alert">WIPE MATRIX</a>
            </div>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <h1>CORE STORAGE OVERLOAD INTERACTION LAYER</h1>
                <div class="system-status">ALARM: CLASS-ONE RED EXCEPTION PROMPT</div>
            </div>

            <?php if (!empty($status_msg)): ?>
                <div class="telemetry-banner <?php echo ($status_type === 'success') ? 'telemetry-success' : 'telemetry-failure'; ?>">
                    EMERGENCY RECOVERY RESPONSE MATRIX: <?php echo $status_msg; ?>
                </div>
            <?php endif; ?>

            <div class="telemetry-row">
                <div class="telemetry-node">
                    <div class="telemetry-label">TARGET CLUSTER REGISTER</div>
                    <div class="telemetry-value" style="color:var(--lcars-orange);">`messages`</div>
                </div>
                <div class="telemetry-node">
                    <div class="telemetry-label">LOGGED DATABASES LOAD ROWS</div>
                    <div class="telemetry-value" style="color:<?php echo ($total_rows > 500) ? '#ff3333' : '#55ff55'; ?>;"><?php echo $total_rows; ?> CELLS</div>
                </div>
            </div>

            <section class="lcars-critical-panel">
                <h3>CRITICAL WARNING // RESTRICTED INSTRUCTION SEQUENCE</h3>
                <p>
                    Engaging this database directive executes an irreversible, system-wide <strong>`TRUNCATE`</strong> command sequence directly upon the communications logging arrays. This operations matrix bypasses user visibility flags, immediately deleting every message stored across all user inboxes, outboxes, and server history paths.
                </p>
                <p>
                    This console should only be deployed if database allocation storage tables exceed safe telemetry constraints, or if member personnel fail to regularly purge their personal terminal histories.
                </p>

                <!-- Double Validation Security Check Configuration Form -->
                <form method="POST" action="dh_delete_messages.php" onsubmit="return confirm('CRITICAL CONFIRMATION OVERRIDE REQUIRED:\n\nARE YOU COMPLETELY CERTAIN YOU WANT TO PERMANENTLY ERASE ALL MESSAGES ON THE ENTIRE SERVER?\n\nTHIS ACTION CANNOT BE UNDONE.');">
                    <input type="hidden" name="action" value="purge_all_transmissions">
                    
                    <div style="text-align: center;">
                        <label style="display:block; font-size:11px; margin-bottom:10px; font-weight:bold; color:var(--lcars-blue);">TO ENGAGE INSTRUCTION MATRIX, INPUT YOUR EXACT ADMIN USERNAME CHARACTER STRING SIGNATURE:</label>
                        <input type="text" name="auth_confirmation" class="lcars-input" required placeholder="CONFIRM CONSOLE ADMIN HANDLE" autocomplete="off">
                    </div>

                    <button type="submit" class="lcars-btn btn-alert" style="width:100%; border-radius:4px; text-align:center; padding:15px; font-size:14px; letter-spacing:2px; box-shadow:0 0 15px rgba(204,51,51,0.4);">EXECUTE SYSTEM-WIDE REPOSITORY WIPEOUT</button>
                </form>
            </section>
        </main>
    </div>

</body>
</html>
