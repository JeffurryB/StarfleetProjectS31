<?php
// 1. Session, Security, and Configuration Management Links
include("session.php");
include("config.php");
include("functions.php"); // 🔒 Loads security logging tools

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure file queries link seamlessly with your config's connection variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

// Barrier 1: Authenticated session check
if (!isset($login_session)) {
    header("Location: notauthorized.php");
    exit;
}

// 🔒 CSRF TOKEN INITIALIZATION CHECK
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// PREPARED STATEMENT RULE
$user_check = $login_session;
$is_owner_validated = false;

// Barrier 2 & 3: The Owner Validation Matrix (ID = 1, dh = 1, security_clearance = 10)
$owner_sql = "SELECT a.ID, a.dh, s.security_clearance 
              FROM accounts a 
              LEFT JOIN SJ_info s ON a.username = s.username 
              WHERE a.username = ? LIMIT 1";

if ($stmt_auth = $conn->prepare($owner_sql)) {
    $stmt_auth->bind_param("s", $user_check);
    $stmt_auth->execute();
    $res_auth = $stmt_auth->get_result();
    
    if ($res_auth && $res_auth->num_rows > 0) {
        $owner_data = $res_auth->fetch_assoc();
        
        // Strict evaluation of your exact constraint criteria rules
        if ((int)$owner_data['ID'] === 1 && (int)$owner_data['dh'] === 1 && (int)$owner_data['security_clearance'] === 10) {
            $is_owner_validated = true;
        }
    }
    $stmt_auth->close();
}

// Hard eject route if the logging profile accounts mismatch
if (!$is_owner_validated) {
    header("Location: notauthorized.php?error=owner_privileges_required");
    exit;
}

// Pull the values directly from memory
$current_group_name = defined('GROUP_NAME') ? GROUP_NAME : "";
$current_group_abbr = defined('GROUP_ABBR') ? GROUP_ABBR : "";

$message = "";

// 2. HANDLE FORM METRICS SUBMISSION VIA POST OVERLAY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_group_config') {
    
    // 🔒 CSRF DEFENSE MATRIX FIREWALL: Intercept rogue third-party submissions
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("HTTP/1.1 403 Forbidden");
        die("CRITICAL SECURITY ERROR: CSRF MATRIX MALFUNCTION. RE-COMPILATION ABORTED.");
    }

    $group_name = trim(filter_input(INPUT_POST, 'group_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $group_abbr = strtoupper(trim(filter_input(INPUT_POST, 'group_abbr', FILTER_SANITIZE_SPECIAL_CHARS)));

    if (empty($group_name) || empty($group_abbr)) {
        $message = "<p class='lcars-text-error'>SYS_ERR: ALL FIELDS ARE MANDATORY FOR CONFIG COMPILATION.</p>";
    } else {
        $upload_ok = true;

        // Process file stream updates
        $file_jobs = [
            'group_logo' => ['dir' => 'images/', 'name' => 'logo.png'],
            'default_avatar' => ['dir' => 'ProfilePics/', 'name' => 'default.png']
        ];

        foreach ($file_jobs as $form_key => $job) {
            if (!empty($_FILES[$form_key]['tmp_name'])) {
                if (!is_dir($job['dir'])) {
                    mkdir($job['dir'], 0755, true);
                }

                $target_path = $job['dir'] . $job['name'];
                $check = getimagesize($_FILES[$form_key]['tmp_name']);
                if ($check === false) {
                    $message .= "<p class='lcars-text-error'>SYS_ERR: INVALID IMAGE ASSET TYPE FOR " . strtoupper($form_key) . ".</p>";
                    $upload_ok = false;
                    break;
                }

                if (!move_uploaded_file($_FILES[$form_key]['tmp_name'], $target_path)) {
                    $message .= "<p class='lcars-text-error'>SYS_ERR: WRITE EXCEPTION IN DIRECTORY " . $job['dir'] . ".</p>";
                    $upload_ok = false;
                    break;
                }
            }
        }

        // Write configuration content down to disk file matrix
        if ($upload_ok) {
            $config_content = "<?php\n";
            $config_content .= "// Generated Automatically via Master Owner Admin Control Panel\n";
            $config_content .= "define('GROUP_NAME', '" . addslashes($group_name) . "');\n";
            $config_content .= "define('GROUP_ABBR', '" . addslashes($group_abbr) . "');\n";
            $config_content .= "define('GROUP_LOGO', 'images/logo.png');\n";
            $config_content .= "define('DEFAULT_AVATAR', 'ProfilePics/default.png');\n";
            $config_content .= "?>";

            if (file_put_contents('group_config.php', $config_content)) {
                $message = "<p class='lcars-text-success'>FLEET CORE SYNCHRONIZED: CONFIGURATION VARIABLES UPDATED.</p>";
                $current_group_name = $group_name;
                $current_group_abbr = $group_abbr;
                
                // Fire security logging metrics
                $log_telemetry = "Modified central fleet variables. Group Name: [".$group_name."] // Abbreviation: [".$group_abbr."].";
                record_security_log($conn, $login_session, 'UPDATE', 'SYSTEM_SETTINGS', 'CORE_CONFIG', $log_telemetry);
            } else {
                $message = "<p class='lcars-text-error'>SYS_ERR: DIRECTORY PERMISSION COLLISION // FILE WRITE BLOCKED.</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Core Fleet Configuration Matrix</title>
    <style>
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900;
            --lcars-pink: #cc6699; --lcars-blue: #33ccff;
            --lcars-bg: #000000; --lcars-green: #33cc33;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff;
            font-family: Arial, sans-serif; margin: 0; padding: 15px;
            text-transform: uppercase; letter-spacing: 1px;
        }
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .lcars-bar-top { background-color: var(--lcars-purple); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SYS-CORE-SETTINGS-99"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-purple); font-size: 28px; font-weight: 300; margin: 0; white-space: nowrap; }
        .lcars-container { display: flex; min-height: 80vh; }
        .lcars-left-bracket { width: 150px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-purple); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 110px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        .lcars-btn { background-color: var(--lcars-blue); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .form-container { background-color: #111116; border-left: 6px solid var(--lcars-orange); padding: 25px; border-radius: 0 10px 10px 0; max-width: 600px; }
        .form-row { margin-bottom: 20px; display: flex; flex-direction: column; }
        label { color: var(--lcars-purple); font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        .lcars-input { background-color: #000000; color: var(--lcars-orange); border: 2px solid var(--lcars-purple); padding: 10px; font-size: 16px; text-transform: uppercase; border-radius: 5px; width: 100%; box-sizing: border-box; }
        .lcars-input::placeholder { color: #554433; }
        .lcars-file-input { background: #111116; color: #fff; padding: 5px; }
        .engage-btn { background-color: var(--lcars-pink); color: #000000; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; cursor: pointer; border-radius: 10px; letter-spacing: 2px; width: 100%; margin-top: 10px; }
        .lcars-text-success { color: var(--lcars-green); font-weight: bold; margin-bottom: 15px; }
        .lcars-text-error { color: #ff5555; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">CENTRAL MANAGEMENT PANEL</h2>
    </header>
    
    <div class="lcars-container">
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <a href="welcome.php" class="lcars-btn">CENTRAL</a>
            <a href="dhpanel.php" class="lcars-btn" style="background-color: var(--lcars-orange);">HQ SYSTEM</a>
        </nav>
        
        <main class="lcars-main-panel">
            <h2>FLEET VARIABLE CONFIGURATION CONTROL</h2>
            <p style="text-transform: none; color: #aaa; margin-bottom: 25px;">
                ACTIVE LOGON NODE: <strong><?php echo htmlspecialchars($login_session); ?></strong> [CLEARANCE CODE: STATUS 10 SEC-JACKET OWNER]
            </p>

            <?php if (!empty($message)) { echo $message; } ?>

            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- 🔒 CSRF SHIELD ACTIVE VALUE ARRAY -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                    <input type="hidden" name="action" value="update_group_config">
                    
                    <div class="form-row">
                        <label for="group_name">FULL FLEET / COMMAND NAME</label>
                        <input type="text" id="group_name" name="group_name" class="lcars-input" value="<?php echo htmlspecialchars($current_group_name); ?>" required placeholder="ENTER GROUP TITLE">
                    </div>

                    <div class="form-row">
                        <label for="group_abbr">GROUP ABBREVIATION (INITIALS)</label>
                        <input type="text" id="group_abbr" name="group_abbr" class="lcars-input" value="<?php echo htmlspecialchars($current_group_abbr); ?>" required placeholder="E.G. STHR">
                    </div>

                    <div class="form-row">
                        <label for="group_logo">UPDATE FLEET OVERLAY LOGO (SAVES TO IMAGES/LOGO.PNG)</label>
                        <input type="file" id="group_logo" name="group_logo" class="lcars-file-input" accept="image/*">
                    </div>

                    <div class="form-row">
                        <label for="default_avatar">UPDATE DEFAULT DOSSIER PHOTO (SAVES TO PROFILEPICS/DEFAULT.PNG)</label>
                        <input type="file" id="default_avatar" name="default_avatar" class="lcars-file-input" accept="image/*">
                    </div>

                    <button type="submit" class="engage-btn">ENGAGE CORE RE-COMPILATION</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
