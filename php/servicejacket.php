<?php
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

// Extract target username dynamically via standard URL routing parameter
$target_user = isset($_GET['username']) ? trim($_GET['username']) : ''; 

if (empty($target_user)) {
    die("<div style='background:#cc3333;color:#fff;padding:20px;font-family:monospace;font-weight:bold;'>SYS_ERR: CRITICAL ROUTING FAILURE // NO TARGET PERSONNEL LOG SPECIFIED.</div>");
}

try {
    // Dynamic public-facing lookup statement matching via parameterized query
    $accountSql = "SELECT a.ID, a.username, a.induction_date, a.species, a.promotions_count, a.profile_img, a.bio, a.gender, d.dname, r.rname 
                   FROM accounts a 
                   LEFT JOIN divisions d ON a.DivID = d.did 
                   LEFT JOIN Rank r ON a.RankID = r.RankID 
                   WHERE a.username = :user";
    
    $accountStmt = $pdo->prepare($accountSql);
    $accountStmt->execute(['user' => $target_user]);
    $user = $accountStmt->fetch(PDO::FETCH_ASSOC);

    $exams = [];
    $sj_data = [];

    // Initialize all blank fields for safety in case SJ_info entry does not exist yet
    $fields = [
    'languages', 'religion', 'height_cm', 'weight_kg', 'hair', 'eyes', 
    'blood_type', 'medical_restrictions', 'other_info', 
    'other_id_marks_features', 'marital_status', 'spouse', 
    'children', 'mother', 'father', 'siblings', 'security_clearance'
];
    foreach ($fields as $f) { $sj_data[$f] = 'N/A'; }

    // Query historical qualification matrix rows matching target personnel identifier
    if ($user) {
        // Double-check validation matching constraint
        if ($user['username'] !== $target_user) {
            die("<div style='background:#cc3333;color:#fff;padding:20px;font-family:monospace;font-weight:bold;'>CRITICAL ERROR: IDENTITY MATRIX MUTATION DETECTED // SECURITY LOCKDOWN ACTIVE.</div>");
        }

        // Fetch Exam history
        $gradebookSql = "SELECT courses, date_completed FROM gradebook WHERE username = :username ORDER BY date_completed DESC";
        $gradebookStmt = $pdo->prepare($gradebookSql);
        $gradebookStmt->execute(['username' => $user['username']]);
        $exams = $gradebookStmt->fetchAll(PDO::FETCH_ASSOC);

               // Fetch matching specialized metrics from SJ_info using verified username link
        $sjSql = "SELECT * FROM SJ_info WHERE username = :username LIMIT 1";
        $sjStmt = $pdo->prepare($sjSql);
        $sjStmt->execute(['username' => $user['username']]);
        $fetched_sj = $sjStmt->fetch(PDO::FETCH_ASSOC);
        
        // Define fallback array explicitly
        $sj_data = [];
        
        // Directly map columns. If empty or null in the database row, fall back to 'N/A'
        $sj_data['security_clearance']    = (!empty($fetched_sj['security_clearance']))    ? $fetched_sj['security_clearance']    : 'N/A';
        $sj_data['languages']             = (!empty($fetched_sj['languages']))             ? $fetched_sj['languages']             : 'N/A';
        $sj_data['religion']              = (!empty($fetched_sj['religion']))              ? $fetched_sj['religion']              : 'N/A';
        $sj_data['hair']                  = (!empty($fetched_sj['hair']))                  ? $fetched_sj['hair']                  : 'N/A';
        $sj_data['eyes']                  = (!empty($fetched_sj['eyes']))                  ? $fetched_sj['eyes']                  : 'N/A';
        $sj_data['blood_type']            = (!empty($fetched_sj['blood_type']))            ? $fetched_sj['blood_type']            : 'N/A';
        $sj_data['marital_status']        = (!empty($fetched_sj['marital_status']))        ? $fetched_sj['marital_status']        : 'N/A';
        $sj_data['spouse']                = (!empty($fetched_sj['spouse']))                ? $fetched_sj['spouse']                : 'N/A';
        $sj_data['mother']                = (!empty($fetched_sj['mother']))                ? $fetched_sj['mother']                : 'N/A';
        $sj_data['father']                = (!empty($fetched_sj['father']))                ? $fetched_sj['father']                : 'N/A';
        $sj_data['children']              = (!empty($fetched_sj['children']))              ? $fetched_sj['children']              : 'N/A';
        $sj_data['siblings']              = (!empty($fetched_sj['siblings']))              ? $fetched_sj['siblings']              : 'N/A';
        $sj_data['medical_restrictions']   = (!empty($fetched_sj['medical_restrictions']))   ? $fetched_sj['medical_restrictions']   : 'N/A';
        $sj_data['other_id_marks_features'] = (!empty($fetched_sj['other_id_marks_features'])) ? $fetched_sj['other_id_marks_features'] : 'N/A';
        $sj_data['other_info']            = (!empty($fetched_sj['other_info']))            ? $fetched_sj['other_info']            : 'N/A';

        // Custom Imperial Height Conversion
        $display_height = "N/A";
        if (!empty($fetched_sj['height_cm']) && is_numeric($fetched_sj['height_cm'])) {
            $total_inches = $fetched_sj['height_cm'] / 2.54;
            $feet = floor($total_inches / 12);
            $inches = round($total_inches % 12, 1);
            $display_height = $feet . "' " . $inches . "\"";
        }

        // Custom Imperial Weight Conversion
        $display_weight = "N/A";
        if (!empty($fetched_sj['weight_kg']) && is_numeric($fetched_sj['weight_kg'])) {
            $display_weight = round($fetched_sj['weight_kg'] * 2.20462, 1) . " LBS";
        }

    } else {
        die("<div style='background:#cc3333;color:#fff;padding:20px;font-family:monospace;font-weight:bold;'>SYS_ERR: RECOVERY FAILED // REQUESTED PERSONNEL RECORD NOT FOUND IN ACTIVE REGISTRY.</div>");
    }
} catch (\PDOException $e) {
    die("Data extraction error: " . $e->getMessage());
}

$profile_pic = (!empty($user['profile_img'])) ? $user['profile_img'] : 'ProfilePics/Default_Profile_Pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS - Service Jacket</title>
    <style>@import url('https://googleapis.com');body{background-color:#000;color:#ff9900;font-family:'Antonio',sans-serif;letter-spacing:.05em;margin:20px;text-transform:uppercase}.lcars-container{display:flex;max-width:1350px;margin:0 auto}.lcars-left-bar{width:140px;border-right:15px solid #ffcc00;border-radius:40px 0 0 40px;padding-right:15px;display:flex;flex-direction:column;gap:10px;align-items:flex-end}.lcars-pill{background-color:#ff9900;color:#000;width:90px;height:30px;border-radius:15px;text-align:center;line-height:30px;font-weight:bold;font-size:13px}.lcars-pill.blue{background-color:#5599cc}.lcars-pill.purple{background-color:#cc99cc}.lcars-main-content{flex-grow:1;padding-left:35px;min-width:0}.lcars-header{font-size:38px;color:#ffcc00;margin-bottom:20px;border-bottom:4px solid #ff9900;padding-bottom:5px;display:flex;justify-content:space-between}.lcars-header-index{color:#5599cc;font-size:18px;align-self:flex-end}.jacket-grid{display:grid;grid-template-columns:220px 25px minmax(340px,1fr) 25px minmax(340px,1fr);gap:20px;align-items:start;margin-top:30px}.profile-side-column{display:flex;flex-direction:column;gap:15px;width:220px}.profile-wrapper{border:3px solid #cc99cc;padding:8px;border-radius:10px;background-color:#050505;display:flex;justify-content:center;align-items:center;box-sizing:border-box}.profile-image{width:200px;height:200px;object-fit:cover;border-radius:4px;border:1px solid #5599cc}.lcars-btn-pill{display:block;background-color:#ffcc00;color:#000;text-decoration:none;width:100%;padding:10px 0;font-family:'Antonio',sans-serif;font-weight:bold;font-size:18px;letter-spacing:.05em;border-radius:20px;text-align:center;text-transform:uppercase;box-sizing:border-box;transition:background .15s ease-in-out}.lcars-btn-pill:hover{background-color:#ff9900}.spacer-line-container{display:flex;flex-direction:column;align-items:center;height:100%;min-height:250px}.spacer-cap-top,.spacer-cap-bottom{width:16px;height:8px;background-color:#5599cc;border-radius:4px 4px 0 0}.spacer-cap-bottom{border-radius:0 0 4px 4px}.spacer-line-vertical{width:4px;flex-grow:1;background-color:#5599cc;margin:4px 0}.data-panel{display:flex;flex-direction:column;gap:14px;min-width:0}.data-row{display:grid;grid-template-columns:150px 1fr;border-bottom:1px dashed #333;padding-bottom:6px;font-size:19px;align-items:baseline;word-break:break-word}.data-label{color:#cc99cc;font-weight:bold;line-weight:1.2;padding-right:10px}.data-value{color:#fff;line-height:1.2;letter-spacing:.03em}.exams-log-container{display:flex;flex-direction:column;gap:5px}.exam-item{display:flex;justify-content:space-between;background-color:#111;padding:4px 10px;border-left:5px solid #5599cc;font-size:15px}.exam-date{color:#5599cc}.no-exams{color:#666;font-size:16px;font-style:italic}.lcars-section-header{font-size:30px;color:#ffcc00;margin-top:40px;margin-bottom:25px;border-bottom:4px solid #cc99cc;padding-bottom:5px;display:flex;justify-content:space-between}.lcars-section-index{color:#cc99cc;font-size:16px;align-self:flex-end}.bio-section{background-color:#050505;border-left:6px solid #ffcc00;padding:15px;border-radius:0 10px 10px 0}.bio-body-text{color:#ddd;font-size:18px;line-height:1.5;letter-spacing:.03em;text-transform:none}@media(max-width:1100px){.jacket-grid{grid-template-columns:1fr;gap:25px}.spacer-line-container{display:none}.profile-side-column{width:100%}}</style>
</head>
<body>

<div class="lcars-container">
    <div class="lcars-left-bar">
        <div class="lcars-pill purple">JKT-A1</div>
        <div class="lcars-pill blue">SEC-24</div>
        <div class="lcars-pill">MED-09</div>
    </div>

    <div class="lcars-main-content">
        <div class="lcars-header">
            <span>Personnel Service Jacket</span>
            <span class="lcars-header-index">STATUS // ACTIVE</span>
        </div>

        <div class="jacket-grid">
            <div class="profile-side-column">
                <div class="profile-wrapper">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Personnel Record" class="profile-image">
                </div>
                <a href="welcome.php" class="lcars-btn-pill">MAIN TERM</a>
            </div>

            <!-- FIRST SPLITTER -->
            <div class="spacer-line-container">
                <div class="spacer-cap-top"></div>
                <div class="spacer-line-vertical"></div>
                <div class="spacer-cap-bottom"></div>
            </div>

                        <!-- LEFT COL DATA: BASIC DOSSIER -->
            <div class="data-panel">
                <div class="data-row"><div class="data-label">Officer Name:</div><div class="data-value" style="color:#ffcc00;font-weight:bold;"><?php echo htmlspecialchars($user['username'] ?? 'UNASSIGNED'); ?></div></div>
                <div class="data-row"><div class="data-label">Species:</div><div class="data-value"><?php echo htmlspecialchars($user['species'] ?? 'UNKNOWN'); ?></div></div>
                <div class="data-row"><div class="data-label">Gender:</div><div class="data-value"><?php $gender_map = [1=>'MALE', 2=>'FEMALE', 3=>'NON-BINARY']; echo htmlspecialchars($gender_map[(int)($user['gender'] ?? 0)] ?? 'UNKNOWN'); ?></div></div>
                <div class="data-row"><div class="data-label">Current Rank:</div><div class="data-value" style="color: #ffcc00;"><?php echo htmlspecialchars($user['rname'] ?? 'PENDING COMMISSION'); ?></div></div>
                <div class="data-row"><div class="data-label">Fleet Division:</div><div class="data-value"><?php echo htmlspecialchars($user['dname'] ?? 'PENDING ASSIGNMENT'); ?></div></div>
                <div class="data-row"><div class="data-label">Registry Date:</div><div class="data-value"><?php echo htmlspecialchars($user['induction_date'] ?? 'UNKNOWN'); ?></div></div>
                <div class="data-row"><div class="data-label">Security Clrnc:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['security_clearance']); ?></div></div>
                <div class="data-row"><div class="data-label">Languages:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['languages']); ?></div></div>
                <div class="data-row"><div class="data-label">Religion:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['religion']); ?></div></div>
                <div class="data-row"><div class="data-label">Hght/Wght:</div><div class="data-value"><?php echo htmlspecialchars($display_height); ?> // <?php echo htmlspecialchars($display_weight); ?></div></div>
                <div class="data-row"><div class="data-label">Hair / Eyes:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['hair']); ?> // <?php echo htmlspecialchars($sj_data['eyes']); ?></div></div>
            </div>

            <!-- SECOND SPLITTER (DIVIDES IN HALF) -->
            <div class="spacer-line-container">
                <div class="spacer-cap-top"></div>
                <div class="spacer-line-vertical"></div>
                <div class="spacer-cap-bottom"></div>
            </div>

            <!-- RIGHT COL DATA: BIOMETRIC / LINEAGE (STARTS AT BLOOD) -->
            <div class="data-panel">
                <div class="data-row"><div class="data-label">Blood Metric:</div><div class="data-value" style="color:#ff5555;font-weight:bold;"><?php echo htmlspecialchars($sj_data['blood_type']); ?></div></div>
                <div class="data-row"><div class="data-label">Marital Status:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['marital_status']); ?></div></div>
                <div class="data-row"><div class="data-label">Spouse Name:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['spouse']); ?></div></div>
                <div class="data-row"><div class="data-label">Mother:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['mother']); ?></div></div>
                <div class="data-row"><div class="data-label">Father:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['father']); ?></div></div>
                <div class="data-row"><div class="data-label">Children:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['children']); ?></div></div>
                <div class="data-row"><div class="data-label">Siblings:</div><div class="data-value"><?php echo htmlspecialchars($sj_data['siblings']); ?></div></div>
                <div class="data-row"><div class="data-label">Prmtns:</div><div class="data-value"><?php echo htmlspecialchars($user['promotions_count'] ?? '0'); ?></div></div>
                <div class="data-row">
                    <div class="data-label">Exams Passed:</div>
                    <div class="data-value">
                        <?php if (!empty($exams)): ?>
                            <div class="exams-log-container">
                                <?php foreach ($exams as $exam): ?>
                                    <div class="exam-item"><span><?php echo htmlspecialchars($exam['courses']); ?></span><span class="exam-date">[<?php echo htmlspecialchars($exam['date_completed']); ?>]</span></div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="no-exams">No Academy Examination Records Found.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="lcars-section-header"><span>Personnel Bio Log</span><span class="lcars-section-index">INDEX // 085</span></div>
        <div class="bio-section"><div class="bio-body-text"><?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'NO PERSONAL LOG ENTRY SUBMITTED IN REGISTRY SYSTEM FILES.'; ?></div></div>
        
        <div class="lcars-section-header"><span>Medical & Physiologic Constraints</span><span class="lcars-section-index">INDEX // 086</span></div>
        <div class="bio-section" style="border-left-color: var(--lcars-pink);"><div class="bio-body-text"><?php echo !empty($sj_data['medical_restrictions']) ? nl2br(htmlspecialchars($sj_data['medical_restrictions'])) : 'NO RECORDED PHYSIOLOGIC CONSTRAINTS.'; ?></div></div>

        <div class="lcars-section-header"><span>Distinguishing Anatomical Features</span><span class="lcars-section-index">INDEX // 087</span></div>
        <div class="bio-section" style="border-left-color: var(--lcars-blue);"><div class="bio-body-text"><?php echo !empty($sj_data['other_id_marks_features']) ? nl2br(htmlspecialchars($sj_data['other_id_marks_features'])) : 'NO MARKINGS LOGGED IN ACTIVE ARCHIVES.'; ?></div></div>

        <div class="lcars-section-header"><span>Sub-System Miscellaneous Archives</span><span class="lcars-section-index">INDEX // 088</span></div>
        <div class="bio-section" style="border-left-color: var(--lcars-purple); margin-bottom: 40px;"><div class="bio-body-text"><?php echo !empty($sj_data['other_info']) ? nl2br(htmlspecialchars($sj_data['other_info'])) : 'NO ADDITIONAL ARCHIVAL TELEMETRY FOUND.'; ?></div></div>
    </div>
</div>
</body>
</html>
