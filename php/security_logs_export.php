<?php
// 1. Session and Database Architecture Links
include('session.php');
include('config.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure file script maps cleanly onto your configuration database instance
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

$user_check = mysqli_real_escape_string($conn, $login_session);
$system_message = "";

// 2. ENFORCE ADMINISTRATIVE DH PRIVILEGE BARRIER
$auth_sql = "SELECT dh FROM accounts WHERE username = ? LIMIT 1";
if ($auth_stmt = $conn->prepare($auth_sql)) {
    $auth_stmt->bind_param("s", $user_check);
    $auth_stmt->execute();
    $auth_res = $auth_stmt->get_result();
    
    if ($auth_res && $auth_res->num_rows === 1) {
        $user_row = $auth_res->fetch_assoc();
        if ((int)$user_row['dh'] !== 1) {
            $auth_stmt->close();
            header("Location: notauthorized.php?error=insufficient_clearance");
            exit();
        }
    } else {
        $auth_stmt->close();
        header("Location: notauthorized.php");
        exit();
    }
    $auth_stmt->close();
} else {
    die("Matrix core subsystem error. Authorization scanner offline.");
}

// 3. SUB-ROUTINE A: GENERATED SECURE RAW TEXT LOG EXPORT (Strict Column Match)
if (isset($_POST['action']) && $_POST['action'] === 'export_logs') {
    if (ob_get_level()) ob_end_clean();
    
    $filename = "SECURITY_TELEMETRY_BACKUP_" . date('Ymd_His') . ".txt";
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Mapped strictly to your column names ordered sequentially
    $export_sql = "SELECT log_id, operator_username, action_type, target_module, target_identifier, change_telemetry, log_timestamp FROM security_logs ORDER BY log_id ASC";
    $export_result = $conn->query($export_sql);

    if ($export_result && $export_result->num_rows > 0) {
        while ($log = $export_result->fetch_assoc()) {
            // Write rows using safe custom parsing blocks
            echo "[ID:"  . $log['log_id'] . 
                 "] [OP:" . $log['operator_username'] . 
                 "] [ACT:" . $log['action_type'] . 
                 "] [MDL:" . $log['target_module'] . 
                 "] [IDF:" . $log['target_identifier'] . 
                 "] [TEL:" . $log['change_telemetry'] . 
                 "] [TS:"  . $log['log_timestamp'] . "]\n";
        }
    } else {
        echo "## SYSTEM TELEMETRY EMPTY // NO LOG RECORDS DETECTED ##\n";
    }
    exit();
}

// 4. SUB-ROUTINE B: TAMPER-PROOF RESTORATION PARSER (Fixed Array Indexes & Column Fields)
if (isset($_POST['action']) && $_POST['action'] === 'import_logs') {
    if (isset($_FILES['log_file']) && $_FILES['log_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['log_file']['tmp_name'];
        $imported_lines = file($file_tmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $inserted_count = 0;
        $duplicate_count = 0;

        // Structured insertion updating data metrics if duplicate ID conflicts trigger
        $import_sql = "INSERT INTO security_logs (log_id, operator_username, action_type, target_module, target_identifier, change_telemetry, log_timestamp) 
                       VALUES (?, ?, ?, ?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE change_telemetry=VALUES(change_telemetry)";
        
        if ($import_stmt = $conn->prepare($import_sql)) {
            foreach ($imported_lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, "##") === 0) continue;

                // Regex parser built exactly around your layout mapping structure
                preg_match('/\[ID:(.*?)\] \[OP:(.*?)\] \[ACT:(.*?)\] \[MDL:(.*?)\] \[IDF:(.*?)\] \[TEL:(.*?)\] \[TS:(.*?)\]/', $line, $matches);
                
                if (count($matches) === 8) {
                    $log_id        = intval($matches[1]);
                    $log_operator  = trim($matches[2]);
                    $log_action    = trim($matches[3]);
                    $log_module    = trim($matches[4]);
                    $log_id_mark   = trim($matches[5]);
                    $log_telemetry = trim($matches[6]);
                    $log_timestamp = trim($matches[7]);

                    // Bind pattern: 1 integer, 6 strings ("issssss")
                    $import_stmt->bind_param("issssss", $log_id, $log_operator, $log_action, $log_module, $log_id_mark, $log_telemetry, $log_timestamp);
                    $import_stmt->execute();
                    
                    if ($import_stmt->affected_rows > 0) {
                        $inserted_count++;
                    } else {
                        $duplicate_count++;
                    }
                }
            }
            $import_stmt->close();
            $system_message = "<div class='lcars-alert success'>RESTORATION COMPLETE // Node reconstructed safely. Rows recovered: [".$inserted_count."]. Unaltered elements: [".$duplicate_count."].</div>";
        } else {
            $system_message = "<div class='lcars-alert error'>SYS_ERR: Database pipeline preparation failed during reconstruct cycle.</div>";
        }
    } else {
        $system_message = "<div class='lcars-alert error'>SYS_ERR: Text import vector file transmission broken or corrupted.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Core Terminal Security Archive</title>
    <style>
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900;
            --lcars-pink: #cc6699; --lcars-blue: #33ccff;
            --lcars-bg: #000000; --lcars-green: #33cc33;
            --lcars-red: #cc3333;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff;
            font-family: Arial, sans-serif; margin: 0; padding: 15px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 25px; }
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SEC-LOG-SYS-99"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-purple); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }
        .lcars-container { display: flex; min-height: 70vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-btn { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn.btn-back { background-color: var(--lcars-blue); }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-panel-block { background-color: #111116; border-left: 6px solid var(--lcars-blue); padding: 25px; margin-bottom: 25px; border-radius: 0 10px 10px 0; }
        .lcars-panel-title { color: var(--lcars-orange); font-size: 20px; margin-bottom: 15px; font-weight: bold; border-bottom: 1px solid var(--lcars-blue); padding-bottom: 5px;}
        .lcars-engage-btn { background-color: var(--lcars-pink); color: #000000; border: none; padding: 12px 24px; font-size: 15px; font-weight: bold; cursor: pointer; border-radius: 5px; letter-spacing: 1px; width: auto; display: inline-block; margin-top: 10px;}
        .lcars-engage-btn.export { background-color: var(--lcars-green); }
        .lcars-file-input { background: #000000; color: var(--lcars-orange); border: 2px solid var(--lcars-blue); padding: 10px; font-size: 14px; border-radius: 5px; cursor: pointer; width: 100%; max-width: 400px; }
        .lcars-alert { padding: 15px; border-radius: 0 5px 5px 0; font-weight: bold; margin-bottom: 20px; }
        .lcars-alert.success { background-color: #113311; border-left: 6px solid var(--lcars-green); color: #55ff55; }
        .lcars-alert.error { background-color: #331111; border-left: 6px solid var(--lcars-pink); color: #ff5555; }
    </style>
</head>
<body>
    <header class="lcars-header"><div class="lcars-bar-top"></div><h2 class="lcars-title">SECURITY INTELLIGENCE LOG SYSTEM</h2></header>
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="dhpanel.php" class="lcars-btn btn-back">ADMIN</a>
        </nav>
        <main class="lcars-main-panel">
            <h2>CENTRAL ARCHIVE MATRIX UTILITY</h2>
            <p style="text-transform: none; color: #aaa;">Current Console Operator: <?php echo htmlspecialchars($login_session); ?> // Class: Administrative DH Node</p>
            
            <?php echo $system_message; ?>

            <!-- DOWNLOAD TELEMETRY EXPORT NODE -->
            <div class="lcars-panel-block">
                <div class="lcars-panel-title">1. RUN SECURE DATA EXTRACTION EXPORT</div>
                <p style="text-transform: none; color: #ccc;">Generates a clean text file mapping of all historic events stored within the active security ledger. Save this file to secure localized physical storage media.</p>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="export_logs">
                    <button type="submit" class="lcars-engage-btn export">EXECUTE LOG EXPORT</button>
                </form>
            </div>

            <!-- RESTORE LOG EXTRACT FROM FILE NODE -->
            <div class="lcars-panel-block" style="border-left: 6px solid var(--lcars-orange);">
                <div class="lcars-panel-title" style="color: var(--lcars-pink);">2. RESTORE RECORD FROM TELEMETRY FILE</div>
                <p style="text-transform: none; color: #ccc;">Select an authentic security log backup text layout stream configuration. The compiler automatically parses, runs missing tracking indexes, and updates elements without dropping entries.</p>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_logs">
                    <input type="file" name="log_file" class="lcars-file-input" required><br>
                    <button type="submit" class="lcars-engage-btn">ENGAGE RESTORATION MATRIX</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
