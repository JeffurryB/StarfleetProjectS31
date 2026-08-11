<?php
include_once 'session.php';
include_once 'functions.php';

// 1. COMPATIBLE SECURITY AUTHORIZATION GUARD
$auth_user = mysqli_real_escape_string($db, $login_session);
$res_auth = mysqli_query($db, "SELECT dh FROM accounts WHERE username = '$auth_user' LIMIT 1");

if ($res_auth && $auth_data = mysqli_fetch_assoc($res_auth)) {
    if ((int)$auth_data['dh'] !== 1) { 
        header("Location: notauthorized.php"); 
        exit(); 
    }
} else { 
    header("Location: index.php"); 
    exit(); 
}

// 2. TRANSACTION PROCESSING WITH DUPLICATE DETECTOR
$alert_message = "";
$is_error_state = false; // Flag to switch banner to LCARS Red Alert style

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        $target_user = mysqli_real_escape_string($db, $_POST['target_username'] ?? '');
        $course_file = mysqli_real_escape_string($db, $_POST['courses'] ?? '');
        $platform    = mysqli_real_escape_string($db, $_POST['platform'] ?? '');
        $grade       = mysqli_real_escape_string($db, $_POST['Grade'] ?? '');

        if (!empty($target_user) && !empty($course_file) && !empty($platform) && !empty($grade)) {
            $course_title = pathinfo($course_file, PATHINFO_FILENAME);
            
            $insert_query = "INSERT INTO gradebook (username, courses, Grade, platform, date_completed, attempts) 
                             VALUES ('$target_user', '$course_title', '$grade', '$platform', NOW(), 1)";
            
            // Try block catches the duplicate key database exception safely
            try {
                if (mysqli_query($db, $insert_query)) {
                    $log_summary = "DH granted virtual academy credentials to user [".$target_user."]. Course telemetry: [".$course_title."] via [".$platform."]. Score: [".$grade."].";
                    record_security_log($db, $login_session, 'INSERT', 'ACADEMY_CREDIT', 'CREDIT_GRANT', $log_summary);
                    $alert_message = "TRANSMISSION SUCCESSFUL: COURSE MATRIX REPLICATED.";
                } else {
                    $is_error_state = true;
                    $alert_message = "CRITICAL ERROR: CORE DATABASE LOG FAILED.";
                }
            } catch (mysqli_sql_exception $e) {
                // Check if MySQL error code matches 1062 (Duplicate Entry)
                if ($e->getCode() === 1062) {
                    $is_error_state = true;
                    $alert_message = "CRITICAL ALERT: EXAM CORRELATION DUPLICATE. PERSONNEL ALREADY HAS THAT EXAM ON FILE.";
                } else {
                    // Re-throw if it's an entirely different database issue
                    throw $e;
                }
            }
        } else {
            $is_error_state = true;
            $alert_message = "TRANSMISSION INVALID: MISSING QUANTUM PARAMETERS.";
        }
    } else {
        $log_summary = "CSRF Token mismatch validation breakdown from operator [".$login_session."]. Trace initiated.";
        record_security_log($db, $login_session, 'SECURITY_BREACH', 'CSRF_GUARD', 'TOKEN_MISMATCH', $log_summary);
        header("Location: notauthorized.php");
        exit();
    }
}

// 3. PERSONNEL INFRASTRUCTURE FETCH
$users_list = [];
if ($user_query = mysqli_query($db, "SELECT username FROM accounts ORDER BY username ASC")) {
    while ($row = mysqli_fetch_assoc($user_query)) { 
        $users_list[] = $row['username']; 
    }
}

// 4. DIRECTORY ASSET INGESTION
$course_files = [];
if (is_dir($dir_path = __DIR__ . '/doc/sdq_classes')) {
    foreach (scandir($dir_path) as $file) {
        if ($file !== '.' && $file !== '..') { 
            $course_files[] = $file; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo GROUP_ABBR; ?> Academic Terminal</title>
    <style>
        @import url('https://googleapis.com');
        :root { --lcars-orange: #ff9900; --lcars-purple: #cc99cc; --lcars-blue: #99ccff; --lcars-red: #ff3333; --lcars-tan: #ffcc99; --lcars-bg: #000; }
        body { background: var(--lcars-bg); color: #fff; font-family: 'Antonio', sans-serif; text-transform: uppercase; margin: 0; padding: 20px; overflow-x: hidden; }
        .lcars-grid { display: grid; grid-template-columns: 150px 1fr; gap: 15px; height: 95vh; }
        .lcars-sidebar { display: flex; flex-direction: column; gap: 10px; }
        .lcars-arch { background: var(--lcars-purple); height: 60px; border-radius: 30px 0 0 0; position: relative; }
        .lcars-arch::after { content: "AUTH-01"; position: absolute; bottom: 5px; right: 10px; font-size: 14px; color: #000; font-weight: bold; }
        .lcars-btn { background: var(--lcars-orange); color: #000; border: none; padding: 12px; font-family: 'Antonio', sans-serif; font-size: 18px; font-weight: bold; text-align: right; border-radius: 20px 0 0 20px; cursor: pointer; }
        .lcars-btn.red { background: var(--lcars-red); }
        .lcars-main { display: flex; flex-direction: column; gap: 20px; }
        .lcars-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid var(--lcars-blue); padding-bottom: 5px; }
        .lcars-title { font-size: 38px; color: var(--lcars-orange); letter-spacing: 2px; }
        .lcars-box { border: 2px solid var(--lcars-orange); border-radius: 15px; padding: 25px; background: rgba(0, 0, 0, 0.6); max-width: 600px; }
        .lcars-box h2 { color: var(--lcars-blue); margin-top: 0; font-size: 24px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; color: var(--lcars-tan); margin-bottom: 5px; font-size: 16px; }
        input, select { width: 100%; background: #111; border: 2px solid var(--lcars-blue); color: #fff; padding: 8px; font-family: 'Antonio', sans-serif; font-size: 16px; box-sizing: border-box; border-radius: 5px; }
        input:focus, select:focus { outline: none; border-color: var(--lcars-orange); }
        
        /* Dynamic Banner Configurations */
        .system-alert { color: var(--lcars-orange); border: 1px dashed var(--lcars-orange); padding: 10px; margin-bottom: 15px; border-radius: 5px; font-weight: bold; }
        .system-alert.red-alert { color: #fff; background: var(--lcars-red); border: 2px solid #fff; letter-spacing: 1px; text-shadow: 1px 1px #000; }
    </style>
</head>
<body>
<div class="lcars-grid">
    <div class="lcars-sidebar">
        <div class="lcars-arch"></div>
        <a href="dhpanel.php" style="text-decoration: none;">
    <button class="lcars-btn">ADMIN TERM</button>
</a>
        <div style="flex-grow: 1; background: var(--lcars-tan); border-radius: 20px 0 0 20px; margin-top: 10px;"></div>
    </div>
    <div class="lcars-main">
        <div class="lcars-header">
            <div class="lcars-title"><?php echo GROUP_ABBR; ?> // SECURE ACADEMIC INGESTION TERMINAL</div>
            <div style="color: var(--lcars-tan); font-size: 18px;">OPERATOR: <?= htmlspecialchars($login_session) ?></div>
        </div>
        <div class="lcars-box">
            <h2>CREDIT UPDATE INTERFACE</h2>
            
            <!-- Conditional evaluation swaps out baseline styling for the Red Alert color profile -->
            <?php if (!empty($alert_message)): ?>
                <div class="system-alert <?= $is_error_state ? 'red-alert' : '' ?>">
                    <?= htmlspecialchars($alert_message) ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                
                <div class="form-group">
                    <label>Target User Identifier</label>
                    <select name="target_username" required>
                        <option value="">-- SELECT PERSONNEL MATRIX --</option>
                        <?php foreach ($users_list as $username): ?><option value="<?= htmlspecialchars($username) ?>"><?= htmlspecialchars($username) ?></option><?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Virtual Grid Infrastructure Vector</label>
                    <select name="platform" required>
                        <option value="Second Life">Second Life (Main Grid)</option>
                        <option value="OpenSim">OpenSim (Hypergrid Node)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Target Academy Curriculum Core</label>
                    <select name="courses" required>
                        <option value="">-- SELECT INGESTED TELEMETRY FILE --</option>
                        <?php foreach ($course_files as $file): ?><option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars($file) ?></option><?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Assigned Competency Matrix Grade</label>
                    <input type="text" name="Grade" placeholder="e.g. A, 94%, PASS" required>
                </div>
                
                <button type="submit" class="lcars-btn red" style="border-radius: 10px; width: 100%; text-align: center;">TRANSMIT ACADEMY LOGS</button>
            </form>
        </div>
    </div>
</div>
    
</body>
</html>
