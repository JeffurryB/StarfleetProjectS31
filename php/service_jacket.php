<?php
include 'session.php'; 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database Connection Configuration
$host = 'YOUR INFO'; 
$dbname = 'DB NAME';
$username = 'DB USERNAME';
$password = 'DB PW'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Extract the authenticated username identifier using your exact 'login_user' session key
$current_session_user = isset($_SESSION['login_user']) ? trim($_SESSION['login_user']) : 'Cadet_Kirk'; 

$update_success = "";

// Handle incoming bio updates securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_bio') {
    $new_bio = isset($_POST['bio_text']) ? trim($_POST['bio_text']) : '';
    try {
        $updateSql = "UPDATE accounts SET bio = :bio WHERE username = :user";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute(['bio' => $new_bio, 'user' => $current_session_user]);
        $update_success = "SUB-ROUTINE SUCCESSFUL: BIOGRAPHY METRICS UPDATED.";
    } catch (\PDOException $e) {
        $update_success = "SYS_ERR: BIOGRAPHY SUB-ROUTINE CRITICAL FAILURE.";
    }
}

// HANDLE INCOMING PROFILE PHOTO UPLOADS SECURELY
// ROUTE B: HANDLE INCOMING PROFILE PHOTO UPLOADS SECURELY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_img']['tmp_name'];
        $file_orig_name = basename($_FILES['profile_img']['name']);
        $file_ext = strtolower(pathinfo($file_orig_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            $target_dir = "ProfilePics/";
            
            if (!is_dir($target_dir)) { 
                mkdir($target_dir, 0755, true); 
            }
            
            $clean_session_name = preg_replace("/[^a-zA-Z0-9_\-]/", "", $current_session_user);
            $new_filename = "AVATAR_" . $clean_session_name . "_" . time() . "." . $file_ext;
            $full_target_path = $target_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $full_target_path)) {
                try {
                    $photoSql = "UPDATE accounts SET profile_img = :img WHERE username = :user";
                    $photoStmt = $pdo->prepare($photoSql);
                    $photoStmt->execute(['img' => $full_target_path, 'user' => $current_session_user]);
                    $update_success = "DATA STREAM OVERLAY COMPLETE: NEW AVATAR RENDERED SUCCESSFULLY.";
                } catch (\PDOException $e) {
                    $update_success = "SYS_ERR: DATABASE CORRUPTION OR MATRIX WRITE EXCEPTION.";
                }
            } else {
                $update_success = "CRITICAL EXCEPTION: FILE SYSTEM WRITE ROUTINE FAILURE.";
            }
        } else {
            $update_success = "CRITICAL EXCEPTION: SECURITY SCAN BLOCKED REJECTED FILE SPECIFICATION.";
        }
    } else {
        $update_success = "CRITICAL EXCEPTION: CHRONOMETER CORE DISCONNECTED SUB-STREAM DATA.";
    }
}

// ROUTE C: SIMPLIFIED DEFAULT PICTURE RESTORATION ROUTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_photo') {
    try {
        // Simply overwrite database cell with your explicit default graphic asset path string
        $resetSql = "UPDATE accounts SET profile_img = 'ProfilePics/Default_Profile_Pic.png' WHERE username = :user";
        $resetStmt = $pdo->prepare($resetSql);
        $resetStmt->execute(['user' => $current_session_user]);
        $update_success = "MATRIX RESET: PROFILE IMAGE RESTORED TO DEFAULT SPECIFICATION.";
    } catch (\PDOException $e) {
        $update_success = "SYS_ERR: PROFILE IMAGE RESET EXCEPTION LOGGED.";
    }
}

try {
    $accountSql = "SELECT a.ID, a.username, a.induction_date, a.species, a.promotions_count, a.profile_img, a.bio, a.gender, d.dname, r.rname 
                   FROM accounts a 
                   LEFT JOIN divisions d ON a.DivID = d.did 
                   LEFT JOIN Rank r ON a.RankID = r.RankID 
                   WHERE a.username = :user";
    
    $accountStmt = $pdo->prepare($accountSql);
    $accountStmt->execute(['user' => $current_session_user]);
    $user = $accountStmt->fetch(PDO::FETCH_ASSOC);

    // Initialize an empty exams tracking array
    $exams = [];

    // Fetch dynamic exam lists from the gradebook matching by username string
    if ($user) {
        $gradebookSql = "SELECT courses, date_completed 
                         FROM gradebook 
                         WHERE username = :username 
                         ORDER BY date_completed DESC";
        
        $gradebookStmt = $pdo->prepare($gradebookSql);
        $gradebookStmt->execute(['username' => $user['username']]);
        $exams = $gradebookStmt->fetchAll(PDO::FETCH_ASSOC);
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
} catch (\PDOException $e) {
    die("Data extraction error: " . $e->getMessage());
}

$nav_user = mysqli_real_escape_string($db, $login_session);
$sql_nav = "SELECT dh FROM accounts WHERE username = '$nav_user' LIMIT 1";
$res_nav = mysqli_query($db, $sql_nav);
$is_admin = false;

if ($res_nav && mysqli_num_rows($res_nav) == 1) {
    $nav_row = mysqli_fetch_assoc($res_nav);
    if ((int)$nav_row['dh'] === 1) {
        $is_admin = true;
    }
}

// Set up the default placeholder image path logic
$profile_pic = (!empty($user['profile_img'])) ? $user['profile_img'] : 'ProfilePics/Default_Profile_Pic.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS - Service Jacket</title>
    <style>
   
        
        body {
            background-color: #000000;
            color: #ff9900;
            font-family: 'Antonio', sans-serif;
            letter-spacing: 0.05em;
            margin: 20px;
            text-transform: uppercase;
        }
        .lcars-container {
            display: flex;
            max-width: 950px;
            margin: 0 auto;
        }
        .lcars-left-bar {
            width: 140px;
            border-right: 15px solid #ffcc00;
            border-radius: 40px 0 0 40px;
            padding-right: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
        .lcars-pill {
            background-color: #ff9900;
            color: #000;
            width: 90px;
            height: 30px;
            border-radius: 15px;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            font-size: 13px;
        }
        .lcars-pill.blue { background-color: #5599cc; }
        .lcars-pill.purple { background-color: #cc99cc; }
        
        .lcars-main-content {
            flex-grow: 1;
            padding-left: 30px;
        }
        .lcars-header {
            font-size: 38px;
            color: #ffcc00;
            margin-bottom: 20px;
            border-bottom: 4px solid #ff9900;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .lcars-header-index {
            color: #5599cc;
            font-size: 18px;
            align-self: flex-end;
        }

        /* Profile Split Grid Panel Setup */
        .jacket-grid {
            display: grid;
            grid-template-columns: 220px 25px 1fr; 
            gap: 15px;
            align-items: start;
            margin-top: 30px;
        }

        /* NEW: Vertical Left Column container to align Image and Button stack neatly */
        .profile-side-column {
            display: flex;
            flex-direction: column;
            gap: 15px;
            width: 220px;
        }

        .profile-wrapper {
            border: 3px solid #cc99cc;
            padding: 8px;
            border-radius: 10px;
            background-color: #050505;
            display: flex;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #5599cc;
        }

        /* Classic Pill-Shaped Gold LCARS Interactive Button Styling */
        .lcars-btn-pill {
            background-color: #ffcc00;
            color: #000000;
            border: none;
            width: 100%;
            padding: 10px 0;
            font-family: 'Antonio', sans-serif;
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 0.05em;
            border-radius: 20px;
            cursor: pointer;
            text-align: center;
            text-transform: uppercase;
            transition: background 0.15s ease-in-out;
        }
        .lcars-btn-pill:hover {
            background-color: #ff9900;
        }

        .spacer-line-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100%;
            min-height: 250px;
        }
        .spacer-cap-top, .spacer-cap-bottom {
            width: 16px;
            height: 8px;
            background-color: #5599cc;
            border-radius: 4px 4px 0 0;
        }
        .spacer-cap-bottom { border-radius: 0 0 4px 4px; }
        .spacer-line-vertical {
            width: 4px;
            flex-grow: 1;
            background-color: #5599cc;
            margin: 4px 0;
        }
        .data-panel {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .data-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            border-bottom: 1px dashed #333333;
            padding-bottom: 6px;
            font-size: 20px;
        }
        .data-label { color: #cc99cc; font-weight: bold; }
        .data-value { color: #ffffff; }

        .exams-log-container { display: flex; flex-direction: column; gap: 5px; }
        .exam-item {
            display: flex;
            justify-content: space-between;
            background-color: #111111;
            padding: 4px 10px;
            border-left: 5px solid #5599cc;
            font-size: 16px;
        }
        .exam-date { color: #5599cc; }
        .no-exams { color: #666666; font-size: 16px; font-style: italic; }

        .lcars-section-header {
            font-size: 30px;
            color: #ffcc00;
            margin-top: 40px;
            margin-bottom: 25px;
            border-bottom: 4px solid #cc99cc;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .lcars-section-index { color: #cc99cc; font-size: 16px; align-self: flex-end; }
        .bio-section {
            background-color: #050505;
            border-left: 6px solid #ffcc00;
            padding: 15px;
            border-radius: 0 10px 10px 0;
        }
        .bio-body-text { color: #dddddd; font-size: 18px; line-height: 1.5; letter-spacing: 0.03em; text-transform: none; }

        .lcars-action-btn {
            background-color: #5599cc;
            color: #000;
            border: none;
            padding: 5px 15px;
            font-family: 'Antonio', sans-serif;
            font-weight: bold;
            font-size: 14px;
            border-radius: 12px;
            cursor: pointer;
            text-transform: uppercase;
        }
        .lcars-action-btn:hover { background-color: #ff9900; }

        .lcars-modal {
            position: fixed !important;
            top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important;
            background-color: rgba(0,0,0,0.85) !important;
            justify-content: center !important; align-items: center !important;
            z-index: 99999 !important;
        }
        .modal-content {
            background-color: #000; border: 3px solid #ff9900; padding: 25px;
            width: 100%; max-width: 500px; border-radius: 15px;
            box-shadow: 0 0 20px rgba(255, 153, 0, 0.4);
        }
        .modal-header { font-size: 26px; color: #ffcc00; margin-bottom: 15px; border-bottom: 2px solid #ff9900; padding-bottom: 5px;}
        .lcars-textarea {
            width: 100%; height: 150px; background-color: #111; border: 2px solid #5599cc;
            color: #fff; padding: 10px; font-size: 16px; font-family: sans-serif; box-sizing: border-box;
            border-radius: 5px; resize: none; margin-bottom: 15px;
        }
        .modal-buttons { display: flex; gap: 10px; justify-content: flex-end; }
        .lcars-success-banner { color: #00ff00; font-weight: bold; font-size: 16px; margin-bottom: 15px; }
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
<div id="bioModal" class="lcars-modal" style="display: none !important;">
    <div class="modal-content">
        <div class="modal-header">Update Personnel Bio</div>
        <form method="POST">
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
<div id="photoModal" class="lcars-modal" style="display: none !important;">
    <div class="modal-content" style="border-color: var(--lcars-orange, #ff9900);">
        <div class="modal-header" style="color: var(--lcars-orange, #ff9900);">Update Profile Image</div>
        
        <!-- Standard upload sub-stream form -->
        <form method="POST" enctype="multipart/form-data" id="photoUploadForm">
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

        <!-- Hidden secondary structural execution engine channel for resetting back to base graphic strings -->
        <form method="POST" id="photoResetForm" style="display: none;">
            <input type="hidden" name="action" value="reset_photo">
        </form>
    </div>
</div>

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

</body>
</html>
