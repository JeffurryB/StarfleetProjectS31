<?php
// 1. Session, Config, and Layout Linkage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'session.php'; 
include_once 'config.php'; // 🗄️ Loads your global database configuration connection
include_once 'functions.php'; // Ensure security logging modules are in scope

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the local file script maps cleanly to your config's database variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

// Extract the authenticated username identifier using your exact 'login_user' session key
$current_session_user = isset($_SESSION['login_user']) ? trim($_SESSION['login_user']) : 'Cadet_Kirk'; 

// 🔒 CSRF INITIALIZATION MATRIX: Seed authorization verification signatures
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$update_success = "";

// Handle state changing actions via POST securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 🔒 CSRF SECURITY FIREWALL: Drop cross-site automated matrix forge attempts instantly
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("HTTP/1.1 403 Forbidden");
        die("CRITICAL SECURITY ERROR: CSRF MATRIX MALFUNCTION. PROFILE METRICS OPERATION TERMINATED.");
    }

    // ROUTE A: Handle incoming bio updates securely
    if ($_POST['action'] === 'update_bio') {
        $new_bio = isset($_POST['bio_text']) ? trim($_POST['bio_text']) : '';
        
        $updateSql = "UPDATE accounts SET bio = ? WHERE username = ?";
        if ($updateStmt = $conn->prepare($updateSql)) {
            $updateStmt->bind_param("ss", $new_bio, $current_session_user);
            if ($updateStmt->execute()) {
                $update_success = "SUB-ROUTINE SUCCESSFUL: BIOGRAPHY METRICS UPDATED.";
            } else {
                $update_success = "SYS_ERR: BIOGRAPHY SUB-ROUTINE CRITICAL FAILURE.";
            }
            $updateStmt->close();
        } else {
            $update_success = "SYS_ERR: BIOGRAPHY SUB-ROUTINE CRITICAL FAILURE.";
        }
    }

    // ROUTE B: HANDLE INCOMING PROFILE PHOTO UPLOADS SECURELY
    if ($_POST['action'] === 'upload_photo') {
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_img']['tmp_name'];
            $file_orig_name = basename($_FILES['profile_img']['name']);
            
            // 🔒 LAYER 1: Extract and strictly validate file extension structure
            $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($file_ext, $allowed_extensions, true)) {
                
                // 🔒 LAYER 2: Server-Side MIME-Type Signature Verification (Bypasses polyglot image tricks)
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $real_mime = $finfo->file($file_tmp);
                $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (in_array($real_mime, $allowed_mimes, true)) {
                    $target_dir = "ProfilePics/";
                    
                    if (!is_dir($target_dir)) { 
                        mkdir($target_dir, 0755, true); 
                    }
                    
                    // 🔒 LAYER 3: Completely randomize the filename using high-entropy hashes
                    // This systematically breaks predictable execution strings like AVATAR_username_timestamp
                    $secure_filename = "avatar_" . bin2hex(random_bytes(16)) . "." . $file_ext;
                    $full_target_path = $target_dir . $secure_filename;
                    
                    // Fetch existing profile photo so we can purge old entries from physical disk safely
                    $old_pic_sql = "SELECT profile_img FROM accounts WHERE username = ? LIMIT 1";
                    if ($stmt_old_pic = $conn->prepare($old_pic_sql)) {
                        $stmt_old_pic->bind_param("s", $current_session_user);
                        $stmt_old_pic->execute();
                        $res_old_pic = $stmt_old_pic->get_result();
                        if ($res_old_pic && $row_pic = $res_old_pic->fetch_assoc()) {
                            $old_path = $row_pic['profile_img'];
                            if (file_exists($old_path) && $old_path !== 'ProfilePics/Default_Profile_Pic.png' && $old_path !== 'ProfilePics/logo.png') {
                                @unlink($old_path);
                            }
                        }
                        $stmt_old_pic->close();
                    }

                    if (move_uploaded_file($file_tmp, $full_target_path)) {
                        $photoSql = "UPDATE accounts SET profile_img = ? WHERE username = ?";
                        if ($photoStmt = $conn->prepare($photoSql)) {
                            $photoStmt->bind_param("ss", $full_target_path, $current_session_user);
                            if ($photoStmt->execute()) {
                                $update_success = "DATA STREAM OVERLAY COMPLETE: NEW AVATAR RENDERED SUCCESSFULLY.";
                            } else {
                                $update_success = "SYS_ERR: DATABASE CORRUPTION OR MATRIX WRITE EXCEPTION.";
                            }
                            $photoStmt->close();
                        } else {
                            $update_success = "SYS_ERR: DATABASE CORRUPTION OR MATRIX WRITE EXCEPTION.";
                        }
                    } else {
                        $update_success = "CRITICAL EXCEPTION: FILE SYSTEM WRITE ROUTINE FAILURE.";
                    }
                } else {
                    $update_success = "CRITICAL EXCEPTION: SECURITY SCAN BLOCKED MALFORMED DATA SPECIFICATION.";
                }
            } else {
                $update_success = "CRITICAL EXCEPTION: SECURITY SCAN BLOCKED REJECTED FILE SPECIFICATION.";
            }
        } else {
            $update_success = "CRITICAL EXCEPTION: CHRONOMETER CORE DISCONNECTED SUB-STREAM DATA.";
        }
    }

    // ROUTE C: PICTURE RESTORATION ROUTE
    if ($_POST['action'] === 'reset_photo') {
        // Fetch and purge old image assets from disk right before restoring defaults
        $old_pic_sql = "SELECT profile_img FROM accounts WHERE username = ? LIMIT 1";
        if ($stmt_old_pic = $conn->prepare($old_pic_sql)) {
            $stmt_old_pic->bind_param("s", $current_session_user);
            $stmt_old_pic->execute();
            $res_old_pic = $stmt_old_pic->get_result();
            if ($res_old_pic && $row_pic = $res_old_pic->fetch_assoc()) {
                $old_path = $row_pic['profile_img'];
                if (file_exists($old_path) && $old_path !== 'ProfilePics/Default_Profile_Pic.png' && $old_path !== 'ProfilePics/logo.png') {
                    @unlink($old_path);
                }
            }
            $stmt_old_pic->close();
        }

        $resetSql = "UPDATE accounts SET profile_img = 'ProfilePics/Default_Profile_Pic.png' WHERE username = ?";
        if ($resetStmt = $conn->prepare($resetSql)) {
            $resetStmt->bind_param("s", $current_session_user);
            if ($resetStmt->execute()) {
                $update_success = "MATRIX RESET: PROFILE IMAGE RESTORED TO DEFAULT SPECIFICATION.";
            } else {
                $update_success = "SYS_ERR: PROFILE IMAGE RESET EXCEPTION LOGGED.";
            }
            $resetStmt->close();
        } else {
            $update_success = "SYS_ERR: PROFILE IMAGE RESET EXCEPTION LOGGED.";
        }
    }
}

// FETCH ACCOUNT METRICS FOR PROFILE PAGE CONTENT
$user = null;
$exams = [];

$accountSql = "SELECT a.ID, a.username, a.induction_date, a.species, a.promotions_count, a.profile_img, a.bio, a.gender, d.dname, r.rname 
               FROM accounts a 
               LEFT JOIN divisions d ON a.DivID = d.did 
               LEFT JOIN Rank r ON a.RankID = r.RankID 
               WHERE a.username = ?";

if ($accountStmt = $conn->prepare($accountSql)) {
    $accountStmt->bind_param("s", $current_session_user);
    $accountStmt->execute();
    $res = $accountStmt->get_result();
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
    }
    $accountStmt->close();
} else {
    die("Data extraction error: Account selection statement failure.");
}

// Fetch dynamic exam lists from the gradebook matching by username string
if ($user) {
    $gradebookSql = "SELECT courses, date_completed 
                     FROM gradebook 
                     WHERE username = ? 
                     ORDER BY date_completed DESC";
    
    if ($gradebookStmt = $conn->prepare($gradebookSql)) {
        $gradebookStmt->bind_param("s", $user['username']);
        $gradebookStmt->execute();
        $res_grade = $gradebookStmt->get_result();
        if ($res_grade) {
            while ($row = $res_grade->fetch_assoc()) {
                $exams[] = $row;
            }
        }
        $gradebookStmt->close();
    }
} else {
    $user = [
        'username' => 'Cadet_Kirk',
        'rname' => 'Cadet First Class',
        'dname' => 'Command & Operations',
        'induction_date' => '2265-03-14',
        'species' => 'Human',
        'gender' => 'Male',
        'promotions_count' => 0,
        'profile_img' => '',
        'bio' => 'PENDING STARFLEET SECURITY PROTOCOL CLEARANCE INTERFACE ASSIGNMENT...'
    ];
    $exams = [
        ['courses' => 'Starship Helm Operations', 'date_completed' => '2265-01-12']
    ];
}

// SECURE ADMINISTRATIVE NAVIGATION OVERLAY DETECTOR (🛡️ PREPARED STATEMENT UPGRADE)
$is_admin = false;
if (isset($login_session)) {
    $nav_user = $login_session;
$sql_nav = "SELECT dh FROM accounts WHERE username = ? LIMIT 1";if ($stmt_nav = $conn->prepare($sql_nav)) {$stmt_nav->bind_param("s", $nav_user);$stmt_nav->execute();$res_nav = $stmt_nav->get_result();if ($res_nav && $res_nav->num_rows === 1) {$nav_row = $res_nav->fetch_assoc();if ((int)$nav_row['dh'] === 1) {$is_admin = true;}}$stmt_nav->close();}}
// Set up the default placeholder image path logic
$profile_pic = (!empty($user['profile_img'])) ? $user['profile_img'] : 'ProfilePics/logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> <?php echo GROUP_ABBR; ?> - Service Jacket</title>
    <style>
    body { background: #000; color: #f90; font-family: 'Antonio', sans-serif; letter-spacing: .05em; margin: 20px; text-transform: uppercase; }
    .lcars-container { display: flex; max-width: 950px; margin: 0 auto; }
    .lcars-left-bar { width: 140px; border-right: 15px solid #fc0; border-radius: 40px 0 0 40px; padding-right: 15px; display: flex; flex-direction: column; gap: 10px; align-items: flex-end; }
    .lcars-pill { background: #f90; color: #000; width: 90px; height: 30px; border-radius: 15px; text-align: center; line-height: 30px; font-weight: bold; font-size: 13px; }
    .lcars-pill.blue { background: #59c; }
    .lcars-pill.purple { background: #c9c; }
    
    .lcars-main-content { flex-grow: 1; padding-left: 30px; }
    .lcars-header { font-size: 38px; color: #fc0; margin-bottom: 20px; border-bottom: 4px solid #f90; padding-bottom: 5px; display: flex; justify-content: space-between; }
    .lcars-header-index { color: #59c; font-size: 18px; align-self: flex-end; }

    .jacket-grid { display: grid; grid-template-columns: 220px 25px 1fr; gap: 15px; align-items: start; margin-top: 30px; }
    .profile-side-column { display: flex; flex-direction: column; gap: 15px; width: 220px; }
    .profile-wrapper { border: 3px solid #c9c; padding: 8px; border-radius: 10px; background: #050505; display: flex; justify-content: center; align-items: center; box-sizing: border-box; }
    .profile-image { width: 200px; height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #59c; }

    .lcars-btn-pill { background: #fc0; color: #000; border: none; width: 100%; padding: 10px 0; font: bold 18px 'Antonio', sans-serif; letter-spacing: .05em; border-radius: 20px; cursor: pointer; text-align: center; text-transform: uppercase; transition: background .15s ease-in-out; }
    .lcars-btn-pill:hover { background: #f90; }

    .spacer-line-container { display: flex; flex-direction: column; align-items: center; height: 100%; min-height: 250px; }
    .spacer-cap-top, .spacer-cap-bottom { width: 16px; height: 8px; background: #59c; border-radius: 4px 4px 0 0; }
    .spacer-cap-bottom { border-radius: 0 0 4px 4px; }
    .spacer-line-vertical { width: 4px; flex-grow: 1; background: #59c; margin: 4px 0; }
    
    .data-panel { display: flex; flex-direction: column; gap: 12px; }
    .data-row { display: grid; grid-template-columns: 160px 1fr; border-bottom: 1px dashed #333; padding-bottom: 6px; font-size: 20px; }
    .data-label { color: #c9c; font-weight: bold; }
    .data-value { color: #fff; }

    .exams-log-container { display: flex; flex-direction: column; gap: 5px; }
    .exam-item { display: flex; justify-content: space-between; background: #111; padding: 4px 10px; border-left: 5px solid #59c; font-size: 16px; }
    .exam-date { color: #59c; }
    .no-exams { color: #666; font-size: 16px; font-style: italic; }

    .lcars-section-header { font-size: 30px; color: #fc0; margin-top: 40px; margin-bottom: 25px; border-bottom: 4px solid #c9c; padding-bottom: 5px; display: flex; justify-content: space-between; }
    .lcars-section-index { color: #c9c; font-size: 16px; align-self: flex-end; }
    .bio-section { background: #050505; border-left: 6px solid #fc0; padding: 15px; border-radius: 0 10px 10px 0; }
    .bio-body-text { color: #ddd; font-size: 18px; line-height: 1.5; letter-spacing: .03em; text-transform: none; }

    .lcars-action-btn { background: #59c; color: #000; border: none; padding: 5px 15px; font: bold 14px 'Antonio', sans-serif; border-radius: 12px; cursor: pointer; text-transform: uppercase; }
    .lcars-action-btn:hover { background: #f90; }

    .lcars-modal { position: fixed !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(0,0,0,.85) !important; display: flex !important; justify-content: center !important; align-items: center !important; z-index: 99999 !important; }
    .modal-content { background: #000; border: 3px solid #f90; padding: 25px; width: 100%; max-width: 500px; border-radius: 15px; box-shadow: 0 0 20px rgba(255,153,0,.4); }
    .modal-header { font-size: 26px; color: #fc0; margin-bottom: 15px; border-bottom: 2px solid #f90; padding-bottom: 5px; }
    .lcars-textarea { width: 100%; height: 150px; background: #111; border: 2px solid #59c; color: #fff; padding: 10px; font-size: 16px; font-family: sans-serif; box-sizing: border-box; border-radius: 5px; resize: none; margin-bottom: 15px; }
    .modal-buttons { display: flex; gap: 10px; justify-content: flex-end; }
    .lcars-success-banner { color: #0f0; font-weight: bold; font-size: 16px; margin-bottom: 15px; }
</style>
</head>
<body>

<div class="lcars-container">
    <div class="lcars-left-bar">
        <div class="lcars-pill purple">JKT-A1</div>
        <div class="lcars-pill blue">SEC-24</div>
        <div class="lcars-pill">MED-09</div>
        <?php if (isset($is_admin) && $is_admin === true): ?>
        <a href="dhpanel.php" class="lcars-pill purple" style="margin-top: 10px; border-left: 4px solid var(--lcars-orange);">ADMIN</a>
    <?php endif; ?>
    </div>

    <div class="lcars-main-content">
        <div class="lcars-header">
            <span>Personnel Service Jacket</span>
            <span class="lcars-header-index">STATUS // ACTIVE</span>
        </div>

        <?php if (!empty($update_success)): ?>
            <div class="lcars-success-banner"><?php echo htmlspecialchars($update_success); ?></div>
        <?php endif; ?>

        <div class="jacket-grid">
            <!-- NEW ARRANGEMENT: Left side profile stack folder -->
            <div class="profile-side-column">
                <div class="profile-wrapper">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Personnel Record" class="profile-image">
                </div>
                <!-- MOVED: The update trigger sits directly beneath the avatar box frame now -->
                <button type="button" class="lcars-btn-pill" onclick="openBioEditor()">Modify Bio</button>
                 <button type="button" class="lcars-btn-pill" onclick="openPhotoEditor()">Modify Photo</button>
                <button type="button" class="lcars-btn-pill" onclick="window.location.href='welcome.php'">MAIN TERM</button>
            </div>

            <div class="spacer-line-container">
                <div class="spacer-cap-top"></div>
                <div class="spacer-line-vertical"></div>
                <div class="spacer-cap-bottom"></div>
            </div>

            <div class="data-panel">
                <div class="data-row">
                    <div class="data-label">Identity Name:</div>
                    <div class="data-value"><?php echo htmlspecialchars($user['username'] ?? 'UNASSIGNED'); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Species:</div>
                    <div class="data-value"><?php echo htmlspecialchars($user['species'] ?? 'UNKNOWN'); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Gender:</div>
                    <div class="data-value"><?php $gender_map = [ 1 => 'MALE', 2 => 'FEMALE', 3 => 'NON-BINARY'];
                        $raw_gender = isset($user['gender']) ? (int)$user['gender'] : null;
                        echo htmlspecialchars($gender_map[$raw_gender] ?? 'UNKNOWN'); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Current Rank:</div>
                    <div class="data-value" style="color: #ffcc00;"><?php echo htmlspecialchars($user['rname'] ?? 'PENDING COMMISSION'); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Fleet Division:</div>
                    <div class="data-value"><?php echo htmlspecialchars($user['dname'] ?? 'PENDING ASSIGNMENT'); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">Registry Date:</div>
                    <div class="data-value"><?php echo htmlspecialchars($user['induction_date'] ?? 'UNKNOWN'); ?></div>
                </div>
                
                <div class="data-row">
                    <div class="data-label">Exams Passed:</div>
                    <div class="data-value">
                        <?php if (!empty($exams)): ?>
                            <div class="exams-log-container">
                                <?php foreach ($exams as $exam): ?>
                                    <div class="exam-item">
                                        <span><?php echo htmlspecialchars($exam['courses']); ?></span>
                                        <span class="exam-date">[<?php echo htmlspecialchars($exam['date_completed']); ?>]</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="no-exams">No Academy Examination Records Found.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="data-row">
                    <div class="data-label">Promotions Logged:</div>
                    <div class="data-value"><?php echo htmlspecialchars($user['promotions_count'] ?? '0'); ?></div>
                </div>
            </div>
        </div>

        <!-- Section Split Header -->
        <div class="lcars-section-header">
            <span>Personnel Bio</span>
            <span class="lcars-section-index">INDEX // 084</span>
        </div>
                <!-- Biography Presentation Segment -->
        <div class="bio-section">
            <div class="bio-body-text">
                <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'NO PERSONAL LOG ENTRY SUBMITTED IN REGISTRY SYSTEM FILES.'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Overlay Dialog Interface Container -->
<!-- Modal Overlay Dialog Interface Container -->
<div id="bioModal" class="lcars-modal" style="display: none !important;">
    <div class="modal-content">
        <div class="modal-header">Update Personnel Bio</div>
        <form method="POST">
            <!-- CSRF Token Core Synchronization Field -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="hidden" name="action" value="update_bio">
            <textarea name="bio_text" class="lcars-textarea" placeholder="Input biography log entries here..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            <div class="modal-buttons">
                <button type="button" class="lcars-action-btn" style="background-color:#ff3333;" onclick="closeBioEditor()">Abort</button>
                <button type="submit" class="lcars-action-btn" style="background-color:#00ff00;">Commit Logs</button>
            </div>
        </form>
    </div>
</div>

<!-- Specialized Profile Image Matrix Upload Modal -->
<!-- Specialized Profile Image Matrix Upload Modal -->
<div id="photoModal" class="lcars-modal" style="display: none !important;">
    <div class="modal-content" style="border-color: var(--lcars-orange, #ff9900);">
        <div class="modal-header" style="color: var(--lcars-orange, #ff9900);">Update Profile Image</div>
        
        <!-- Standard upload sub-stream form -->
        <form method="POST" enctype="multipart/form-data" id="photoUploadForm">
            <!-- CSRF Token Core Synchronization Field -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <input type="hidden" name="action" value="upload_photo">
            
            <div class="form-row" style="margin: 20px 0; text-align: left; display: flex; flex-direction: column; gap: 8px;">
                <label style="color: var(--lcars-blue, #33ccff); font-size: 12px; font-weight: bold;">SELECT IMAGE SOURCE MATRIX:</label>
                <input type="file" id="input_avatar" name="profile_img" class="lcars-input" accept="image/*" required style="background:#000; border:2px solid var(--lcars-dark-blue, #5588ff); color:#fff; padding:12px; font-size:14px; border-radius:4px; box-sizing:border-box; width:100%;">
            </div>

            <div class="modal-buttons" style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="lcars-action-btn" style="background-color:#ff3333; margin-right: auto;" onclick="closePhotoEditor()">Abort</button>
                
                <!-- Quick restoration engine toggle -->
                <button type="button" class="lcars-action-btn" style="background-color: var(--lcars-purple, #9966cc); color: #000; font-weight: bold;" onclick="document.getElementById('photoResetForm').submit();">Restore Default</button>
                
                <button type="submit" class="lcars-action-btn" style="background-color:#00ff00;">Synchronize</button>
            </div>
        </form>
    </div>
</div>

<!-- NOTE: If you have a separate invisible reset form for restoring defaults anywhere in your file, ensure it looks like this: -->
<form method="POST" id="photoResetForm" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="action" value="reset_photo">
</form>

<!-- Active JavaScript Controller Triggers -->
<script>
    function openBioEditor() {
        document.getElementById('bioModal').style.setProperty('display', 'flex', 'important');
    }
    function closeBioEditor() {
        document.getElementById('bioModal').style.setProperty('display', 'none', 'important');
    }
    function openPhotoEditor() {
        document.getElementById('photoModal').style.setProperty('display', 'flex', 'important');
    }
    function closePhotoEditor() {
        document.getElementById('photoModal').style.setProperty('display', 'none', 'important');
    }
</script>
<!-- Open Source Matrix Footer Verification -->
    <?php include('footer.php'); ?>
</body>
</html>
