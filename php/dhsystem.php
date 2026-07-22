<?php
// 1. INCLUDE EXISTING SESSION MANAGEMENT
include("session.php");
if (isset($_GET['ack']) && $_GET['ack'] == '1') {
    $_SESSION['security_acknowledged'] = true;
}

// Check if they have already cleared it earlier in this session
$show_security_modal = true;
if (isset($_SESSION['security_acknowledged']) && $_SESSION['security_acknowledged'] === true) {
    $show_security_modal = false;
}
// Assumes session.php populates $_SESSION['username'] or $login_session. 
if (!isset($login_session)) {
    header("Location: notauthorized.php");
    exit;
}

// 2. DATABASE CONFIGURATION
$host = 'YOUR INFO';
$db   = 'YOUR DB NAME';
$user = 'YOUR DB USERNAME';
$pass = 'YOUR DB PW';
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Terminal Connection Failure: " . $conn->connect_error);
}

// 3. SECURE AUTHORIZATION CHECK
$stmt_auth = $conn->prepare("SELECT dh FROM accounts WHERE username = ?");
$stmt_auth->bind_param("s", $login_session);
$stmt_auth->execute();
$res_auth = $stmt_auth->get_result();

if ($res_auth && $res_auth->num_rows > 0) {
    $current_user = $res_auth->fetch_assoc();
    if ((int)$current_user['dh'] !== 1) {
        $stmt_auth->close();
        $conn->close();
        header("Location: notauthorized.php?error=access_denied");
        exit;
    }
} else {
    $stmt_auth->close();
    $conn->close();
    header("Location: notauthorized.php");
    exit;
}
$stmt_auth->close();

// 4. FETCH ALL ACCOUNTS FOR THE DROPDOWN LIST
$sql_all = "SELECT ID, DisplayName, username FROM accounts ORDER BY DisplayName ASC";
$result_all = $conn->query($sql_all);

$all_members = [];
if ($result_all && $result_all->num_rows > 0) {
    while($row = $result_all->fetch_assoc()) {
        $all_members[] = $row;
    }
}

// 5. IF A MEMBER IS SELECTED, FETCH THEIR BASE ACCOUNT & SJ_INFO DETAILS
$selected_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;
$member_data = [];
$sj_data = [];

// Define clean empty keys for SJ_info fields so the HTML forms don't trigger notices
$fields = ['languages', 'religion', 'height_cm', 'weight_kg', 'hair', 'eyes', 'blood_type', 'medical_restrictions', 'other_info', 'other_id_marks_features', 'marital_status', 'spouse', 'children', 'mother', 'father', 'siblings', 'security_clearance'];
foreach ($fields as $f) { $sj_data[$f] = ''; }

if ($selected_id > 0) {
    $stmt_user = $conn->prepare("SELECT * FROM accounts WHERE ID = ?");
    $stmt_user->bind_param("i", $selected_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user && $result_user->num_rows > 0) {
        $member_data = $result_user->fetch_assoc();
        
        // Fetch matching specialized metrics from SJ_info using username link
        $stmt_sj = $conn->prepare("SELECT * FROM SJ_info WHERE username = ?");
        $stmt_sj->bind_param("s", $member_data['username']);
        $stmt_sj->execute();
        $res_sj = $stmt_sj->get_result();
        if ($res_sj && $res_sj->num_rows > 0) {
            $sj_data = $res_sj->fetch_assoc();
        }
        $stmt_sj->close();
    }
    $stmt_user->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RP GROUP - Admin Terminal</title><style>:root{--lcars-purple:#9966cc;--lcars-orange:#ff9900;--lcars-pink:#cc6699;--lcars-blue:#33ccff;--lcars-dark-blue:#5588ff;--lcars-bg:#000000}body{background-color:var(--lcars-bg);color:#fff;font-family:"Arial Custom","Helvetica Neue",Arial,sans-serif;margin:0;padding:15px;text-transform:uppercase;letter-spacing:1px;overflow-x:hidden}.lcars-header{display:flex;align-items:flex-end;margin-bottom:15px}.lcars-bar-top{background-color:var(--lcars-purple);height:40px;flex-grow:1;border-bottom-left-radius:20px;margin-right:15px;position:relative}.lcars-bar-top::before{content:"SD-2026";position:absolute;left:25px;bottom:3px;color:#000;font-weight:bold;font-size:14px}.lcars-title{color:var(--lcars-orange);font-size:28px;font-weight:300;margin:0;line-height:1;white-space:nowrap}.lcars-container{display:flex;min-height:calc(100vh - 120px)}.lcars-left-bracket{width:150px;display:flex;flex-direction:column;margin-right:20px}.lcars-elbow{background-color:var(--lcars-purple);height:60px;border-top-left-radius:20px;border-bottom-left-radius:20px;margin-bottom:15px;position:relative}.lcars-elbow::after{content:"";position:absolute;background-color:var(--lcars-bg);width:110px;height:35px;bottom:0;right:0;border-top-left-radius:15px}.lcars-menu{display:flex;flex-direction:column;gap:8px}.lcars-btn{background-color:var(--lcars-orange);color:#000;padding:10px 15px;text-decoration:none;font-weight:bold;font-size:13px;text-align:right;border-radius:5px 0 0 5px;transition:background .2s;border:none;cursor:pointer;font-family:inherit;letter-spacing:inherit}.lcars-btn:hover{background-color:#fcc}.btn-blue{background-color:var(--lcars-blue)}.btn-blue:hover{background-color:#88e2ff}.btn-pink{background-color:var(--lcars-pink)}.btn-pink:hover{background-color:#ff99cc}.btn-logout{background-color:#c33;color:#fff;margin-top:20px}.btn-logout:hover{background-color:#f55}.lcars-main-panel{flex-grow:1;display:flex;flex-direction:column}.lcars-user-banner{border-bottom:4px solid var(--lcars-blue);padding-bottom:10px;margin-bottom:25px;display:flex;justify-content:space-between;align-items:center}.lcars-user-banner h1{margin:0;font-size:22px;color:var(--lcars-blue);font-weight:normal}.system-status{font-size:12px;color:var(--lcars-dark-blue)}.lcars-admin-section{background-color:#111116;border-left:6px solid var(--lcars-purple);padding:20px;border-radius:0 8px 8px 0;margin-bottom:25px}.lcars-admin-section h3{margin:0 0 15px 0;color:var(--lcars-purple);font-size:18px}.form-row{display:flex;flex-direction:column;margin-bottom:15px}.form-row label{color:var(--lcars-blue);font-size:12px;margin-bottom:5px;font-weight:bold}.lcars-input,.lcars-select{background-color:#000;border:2px solid var(--lcars-dark-blue);color:#fff;padding:10px;font-size:14px;border-radius:4px;font-family:inherit;letter-spacing:1px;text-transform:uppercase;width:100%;box-sizing:border-box}.lcars-input:focus,.lcars-select:focus{outline:none;border-color:var(--lcars-blue)}.lcars-select{appearance:none;background-image:url("data:image/svg+xml;utf8,<svg xmlns='http://w3.org' width='100' height='50'><polygon points='0,0 100,0 50,50' style='fill:%2333ccff;'/></svg>");background-repeat:no-repeat;background-size:12px 6px;background-position:right 15px center;padding-right:30px}.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}.form-actions{display:flex;gap:15px;margin-top:10px}.form-actions .lcars-btn{border-radius:4px;text-align:center;min-width:120px}.field-tip{font-size:11px;color:#aaa;text-transform:none;margin-top:5px}
    .lcars-modal-overlay{display:flex;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center;padding:20px;box-sizing:border-box}
.lcars-modal-box{background:#050505;border:4px solid #cc3333;border-radius:15px;max-width:550px;width:100%;padding:25px;box-sizing:border-box;box-shadow:0 0 25px rgba(204,51,51,0.4)}
.lcars-modal-header{color:#cc3333;font-size:24px;font-weight:bold;border-bottom:3px solid #cc3333;padding-bottom:5px;margin-bottom:15px;display:flex;justify-content:space-between}
.lcars-modal-body{color:#ddd;font-size:16px;line-height:1.4;text-transform:none;margin-bottom:20px}
.lcars-modal-body strong{color:#ff9900;text-transform:uppercase}
.lcars-warn-text{color:#cc3333;font-weight:bold;text-transform:uppercase;margin-top:15px;font-size:15px;letter-spacing:1px}
.lcars-modal-actions{display:flex;justify-content:flex-end}
</style></head>

<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">STARFLEET COMPUTER TERMINAL</h2>
    </header>

    <div class="lcars-container">
        
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-menu">
                <a href="welcome.php" class="lcars-btn">MAIN TERM</a>
                <a href="staff_list.php" class="lcars-btn btn-blue">ROSTER</a>
                <a href="asset.php" class="lcars-btn btn-pink">ASSET MGT</a>
                <a href="logout.php" class="lcars-btn btn-logout">DISENGAGE</a>
            </div>
        </nav>

        <main class="lcars-main-panel">
            <div class="lcars-user-banner">
                <?php if (isset($_GET['status']) && $_GET['status'] == 'synchronized'): ?>
                    <div style="background-color: #33cc66; color: #000000; padding: 10px; font-weight: bold; margin-bottom: 20px; border-radius: 4px;">
                        DATA STREAM SYNCHRONIZATION: COMPLETED SUCCESSFULLY
                    </div>
                <?php elseif (isset($_GET['status']) && $_GET['status'] == 'failure'): ?>
                    <div style="background-color: #cc3333; color: #ffffff; padding: 10px; font-weight: bold; margin-bottom: 20px; border-radius: 4px;">
                        CRITICAL ERROR: CORE DATA MUTATION WRITE FAILURE
                    </div>
                <?php endif; ?>
                <h1>OVERRIDE: MEMBER REGISTRY MANAGEMENT</h1>
                <div class="system-status">SYS STATUS: OVERRIDE // AUTH_LEVEL: ADMIN_01</div>
            </div>

            <!-- Member Selection Sub-Array -->
            <section class="lcars-admin-section">
                <h3>SELECT PERSONNEL RECORD</h3>
                <form id="lcars_selector_form" method="GET" action="dhsystem.php">
                    <div style="display: flex; gap: 15px; align-items: flex-end;">
                        <div class="form-row" style="flex-grow: 1; margin-bottom: 0;">
                            <label for="member_select">ACTIVE FILES INDEX</label>
                            <select name="member_id" id="member_select" class="lcars-select" onchange="document.getElementById('lcars_selector_form').submit();">
                                <option value="">-- SELECT PERSONNEL ID / NAME --</option>
                                <?php foreach ($all_members as $member): ?>
                                    <option value="<?php echo $member['ID']; ?>" <?php if ($selected_id == $member['ID']) echo 'selected'; ?>>
                                        ID: <?php echo $member['ID']; ?> // <?php echo htmlspecialchars($member['DisplayName'] ?: $member['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="lcars-btn btn-blue" style="border-radius: 4px; height: 42px; min-width: 140px; padding: 0 15px; text-align: center;">FETCH RECORD</button>
                    </div>
                </form>
            </section>
            <?php if ($selected_id > 0 && !empty($member_data)): ?>
                <section class="lcars-admin-section" style="border-left-color: var(--lcars-blue); margin-top: 25px;">
                    <h3 style="color: var(--lcars-blue);">EDIT SERVICE JACKET METADATA</h3>
                    <form method="POST" action="process_member_edit.php">
                        <input type="hidden" name="target_member_id" value="<?php echo htmlspecialchars($selected_id); ?>">
                        <div class="form-grid">
                            <div class="form-row"><label for="input_display_name">DISPLAY NAME</label><input type="text" id="input_display_name" name="DisplayName" class="lcars-input" value="<?php echo htmlspecialchars($member_data['DisplayName'] ?? ''); ?>" placeholder="SURNAME, FIRSTNAME"></div>
                            <div class="form-row"><label for="input_username">USERNAME</label><input type="text" id="input_username" name="username" class="lcars-input" value="<?php echo htmlspecialchars($member_data['username'] ?? ''); ?>" placeholder="SYS_LOGON_ID"></div>
                            <div class="form-row"><label for="input_email">EMAIL ACCESS IDENTIFIER</label><input type="email" id="input_email" name="email" class="lcars-input" style="text-transform: none;" value="<?php echo htmlspecialchars($member_data['email'] ?? ''); ?>" placeholder="name@domain.com"></div>
                            <div class="form-row"><label for="input_rank">RANK DESIGNATOR ID</label><input type="text" id="input_rank" name="RankID" class="lcars-input" value="<?php echo htmlspecialchars($member_data['RankID'] ?? ''); ?>" placeholder="RANK_INDEX_CODE"></div>
                            <div class="form-row"><label for="input_title">TITLE ID</label><input type="text" id="input_title" name="TitleID" class="lcars-input" value="<?php echo htmlspecialchars($member_data['TitleID'] ?? ''); ?>" placeholder="TITLE_INDEX_CODE"></div>
                            <div class="form-row"><label for="input_div">DIVISION ID</label><input type="text" id="input_div" name="DivID" class="lcars-input" value="<?php echo htmlspecialchars($member_data['DivID'] ?? ''); ?>" placeholder="DIVISION_CODE"></div>
                            <div class="form-row"><label for="input_species">SPECIES TYPE</label><input type="text" id="input_species" name="species" class="lcars-input" value="<?php echo htmlspecialchars($member_data['species'] ?? ''); ?>" placeholder="HUMAN / VULCAN"></div>
                            <div class="form-row"><label for="input_birth_place">PLACE OF BIRTH</label><input type="text" id="input_birth_place" name="birth_place" class="lcars-input" value="<?php echo htmlspecialchars($member_data['birth_place'] ?? ''); ?>" placeholder="SECTOR_OR_CITY"></div>
                            <div class="form-row"><label for="input_induction_date">JOIN DATE</label><input type="text" id="input_induction_date" name="induction_date" class="lcars-input" value="<?php echo htmlspecialchars($member_data['induction_date'] ?? ''); ?>" placeholder="Unknown"></div>
                            <div class="form-row"><label for="input_active">DUTY STATE (ACTIVE)</label><select id="input_active" name="active" class="lcars-select"><option value="1" <?php if (($member_data['active'] ?? '') == '1') echo 'selected'; ?>>1 - ONLINE ACTIVE</option><option value="0" <?php if (($member_data['active'] ?? '') == '0') echo 'selected'; ?>>0 - STANDBY DISENGAGED</option></select></div>
                            <div class="form-row"><label for="input_dh">DIVISION HEAD (ADMIN)</label><select id="input_dh" name="dh" class="lcars-select"><option value="1" <?php if (($member_data['dh'] ?? '') == '1') echo 'selected'; ?>>1 - DH YES</option><option value="0" <?php if (($member_data['dh'] ?? '') == '0') echo 'selected'; ?>>0 - DH NO</option></select></div>
                        </div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_bio">BIOGRAPHICAL SERVICE MEMORY NOTES</label><input type="text" id="input_bio" name="bio" class="lcars-input" value="<?php echo htmlspecialchars($member_data['bio'] ?? ''); ?>" placeholder="ADD LOG ENTRY ANNOTATIONS"></div>

                        <h3 style="color: var(--lcars-blue); margin-top: 35px; border-top: 2px solid var(--lcars-blue); padding-top: 20px;">BIOMETRIC & PERSONAL SPECIFICATIONS</h3>
                        <div class="form-grid">
                            <div class="form-row"><label for="input_languages">SPOKEN / WRITTEN LANGUAGES</label><input type="text" id="input_languages" name="languages" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['languages'] ?? ''); ?>" placeholder="FEDERATION STANDARD, VULCAN, KLINGON"></div>
                            <div class="form-row"><label for="input_religion">RELIGION / PHILOSOPHY</label><input type="text" id="input_religion" name="religion" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['religion'] ?? ''); ?>" placeholder="IDIC / SPIRITUAL PATH"></div>
                            <div class="form-row"><label for="input_height">HEIGHT (CM)</label><input type="number" step="0.01" id="input_height" name="height_cm" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['height_cm'] ?? ''); ?>" placeholder="0.00"></div>
                            <div class="form-row"><label for="input_weight">WEIGHT (KG)</label><input type="number" step="0.01" id="input_weight" name="weight_kg" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['weight_kg'] ?? ''); ?>" placeholder="0.00"></div>
                            <div class="form-row"><label for="input_hair">HAIR VECTOR STYLE/COLOR</label><input type="text" id="input_hair" name="hair" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['hair'] ?? ''); ?>" placeholder="BLACK / BROWN / BALD"></div>
                            <div class="form-row"><label for="input_eyes">EYE COLORATION</label><input type="text" id="input_eyes" name="eyes" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['eyes'] ?? ''); ?>" placeholder="BLUE / GREEN / HAZEL"></div>
                            <div class="form-row"><label for="input_blood">BLOOD TYPE / METABOLIC COMPOSITION</label><input type="text" id="input_blood" name="blood_type" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['blood_type'] ?? ''); ?>" placeholder="O+, AB-, T-NEGATIVE"></div>
                            <div class="form-row"><label for="input_clearance">SECURITY CLEARANCE STATUS</label><input type="text" id="input_clearance" name="security_clearance" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['security_clearance'] ?? ''); ?>" placeholder="LEVEL 1 TO LEVEL 5"></div>
                            <div class="form-row"><label for="input_marital">MARITAL STATUS</label><input type="text" id="input_marital" name="marital_status" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['marital_status'] ?? ''); ?>" placeholder="SINGLE / MARRIED / BONDED"></div>
                            <div class="form-row"><label for="input_spouse">SPOUSE REGISTRY NAME</label><input type="text" id="input_spouse" name="spouse" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['spouse'] ?? ''); ?>" placeholder="SURNAME, FIRSTNAME"></div>
                            <div class="form-row"><label for="input_mother">MOTHER REGISTRY NAME</label><input type="text" id="input_mother" name="mother" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['mother'] ?? ''); ?>" placeholder="SURNAME, FIRSTNAME"></div>
                            <div class="form-row"><label for="input_father">FATHER REGISTRY NAME</label><input type="text" id="input_father" name="father" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['father'] ?? ''); ?>" placeholder="SURNAME, FIRSTNAME"></div>
                        </div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_children">CHILDREN / DESCENDANT RECORDS</label><input type="text" id="input_children" name="children" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['children'] ?? ''); ?>" placeholder="LIST COMPREHENSIVE DESCENDANTS"></div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_siblings">SIBLING REGISTRY LINKS</label><input type="text" id="input_siblings" name="siblings" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['siblings'] ?? ''); ?>" placeholder="LIST SIBLING INDEXES"></div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_marks">DISTINGUISHING MARKS / PHYSICAL FEATURES</label><input type="text" id="input_marks" name="other_id_marks_features" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['other_id_marks_features'] ?? ''); ?>" placeholder="SCARS, CYBERNETIC IMPLANTS, TATTOOS"></div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_medical">MEDICAL RESTRICTIONS / BIO-FILTERS PROTOCOLS</label><input type="text" id="input_medical" name="medical_restrictions" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['medical_restrictions'] ?? ''); ?>" placeholder="ALLERGIES, PHOBIAS, CHRONIC CONDITIONS"></div>
                        <div class="form-row" style="margin-top: 15px;"><label for="input_other">OTHER SUB-SYSTEM SPECIFIC DATA</label><input type="text" id="input_other" name="other_info" class="lcars-input" value="<?php echo htmlspecialchars($sj_data['other_info'] ?? ''); ?>" placeholder="MISCELLANEOUS ARCHIVE LOGS"></div>

                             <div class="form-actions" style="margin-top: 20px;">
                            <button type="submit" class="lcars-btn">SAVE CHANGES</button>
                            <a href="dhsystem.php?member_id=<?php echo htmlspecialchars($selected_id); ?>" class="lcars-btn btn-pink" style="text-decoration:none;">RESET FORM</a>
                            <a href="dhsystem.php" class="lcars-btn btn-blue" style="text-decoration:none;">ABORT</a>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <!-- SYSTEM AUDIT MODAL OVERLAY -->
    <?php if ($show_security_modal): ?>
<div id="security_warning_modal" class="lcars-modal-overlay">
    <div class="lcars-modal-box">
        <div class="lcars-modal-header">
            <span>[!] SECURITY AUDIT ALERT</span>
            <span style="color:#ff9900;">SYS_WARN_77</span>
        </div>
        <div class="lcars-modal-body">
            YOU ARE ACCESSING A SECURE REGISTRY DIRECTORY. Under Fleet Security Protocol 101, <strong>ALL DATA CHANGES ARE LOGGED IN THE SECURITY SYSTEM</strong>.
            <br><br>
            <span style="color:#5599cc; font-weight:bold;">TELEMETRY TRACKING PROTOCOL ACTIVE:</span>
            <ul style="margin:8px 0; padding-left:20px; color:#aaa;">
                <li>OPERATOR BINDING ID: <strong><?php echo htmlspecialchars($login_session); ?></strong></li>
                <li>ACTION ARCHIVE: <strong>LOGGING ALL RECORD MODIFICATIONS & MODIFIER IDENTITY</strong></li>
            </ul>
            <div class="lcars-warn-text">CRITICAL NOTICE: DO NOT ABUSE THIS SYSTEM. ALL UNAUTHORIZED MUTATIONS WILL TRIGGER LOGON SUSPENSION PROTOCOLS.</div>
        </div>
        <div class="lcars-modal-actions">
            <button type="button" class="lcars-btn" style="background:#ff9900; color:#000; border-radius:4px; font-weight:bold; padding:10px 20px; cursor:pointer;" onclick="dismissSecurityModal()">ACKNOWLEDGE</button>
        </div>
    </div>
</div>
    <?php endif; ?>
</body>
    <script>
function dismissSecurityModal() {
    // Reload the page with the acknowledgment token to lock it into the session
    window.location.href = window.location.pathname + '?ack=1';
}
</script>
</script>
</html>
