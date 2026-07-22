<?php
// 1. INCLUDE EXISTING SESSION MANAGEMENT
include("session.php");
include("functions.php");

// Verify that an active administrator is initiating this action
if (!isset($login_session)) {
    header("Location: notauthorized.php");
    exit;
}

// 2. DATABASE CONFIGURATION
$servername = "YOUR INFO";
$username = "DB USERNAME";
$password = "DB PW";
$dbname = "DB NAME";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Terminal Connection Failure: " . $conn->connect_error);
}

// 3. SECURE AUTHORIZATION CHECK (Verify 'dh' column is 1)
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

// 4. PROCESS FORM DATA VIA POST REQUEST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Clean and validate target record ID
    $target_member_id = isset($_POST['target_member_id']) ? intval($_POST['target_member_id']) : 0;
    
    if ($target_member_id <= 0) {
        header("Location: dhsystem.php?error=invalid_id");
        exit;
    }

    // Capture, trim, and sanitize standard form inputs
    $displayName = trim($_POST['DisplayName']);
    $userLogon   = trim($_POST['username']);
    $emailAddr   = trim($_POST['email']);
    $rankID      = trim($_POST['RankID']);
    $titleID     = trim($_POST['TitleID']);
    $divID       = trim($_POST['DivID']);
    $speciesType = trim($_POST['species']);
    $birthPlace  = trim($_POST['birth_place']);
    $inductiondate = trim($_POST['induction_date']);
    $activeState = intval($_POST['active']); // Converts explicitly to 1 or 0
    $dh = intval($_POST['dh']);
    $bioNotes    = trim($_POST['bio']);
    
    // NEW: Capture and sanitize SJ_info service jacket fields
    $languages    = isset($_POST['languages']) ? trim($_POST['languages']) : '';
    $religion     = isset($_POST['religion']) ? trim($_POST['religion']) : '';
    $height_cm    = (!empty($_POST['height_cm'])) ? floatval($_POST['height_cm']) : null;
    $weight_kg    = (!empty($_POST['weight_kg'])) ? floatval($_POST['weight_kg']) : null;
    $hair         = isset($_POST['hair']) ? trim($_POST['hair']) : '';
    $eyes         = isset($_POST['eyes']) ? trim($_POST['eyes']) : '';
    $blood_type   = isset($_POST['blood_type']) ? trim($_POST['blood_type']) : '';
    $clearance    = isset($_POST['security_clearance']) ? trim($_POST['security_clearance']) : '';
    $marital      = isset($_POST['marital_status']) ? trim($_POST['marital_status']) : '';
    $spouse       = isset($_POST['spouse']) ? trim($_POST['spouse']) : '';
    $mother       = isset($_POST['mother']) ? trim($_POST['mother']) : '';
    $father       = isset($_POST['father']) ? trim($_POST['father']) : '';
    $children     = isset($_POST['children']) ? trim($_POST['children']) : '';
    $siblings     = isset($_POST['siblings']) ? trim($_POST['siblings']) : '';
    $id_marks     = isset($_POST['other_id_marks_features']) ? trim($_POST['other_id_marks_features']) : '';
    $med_restrict = isset($_POST['medical_restrictions']) ? trim($_POST['medical_restrictions']) : '';
    $other_info   = isset($_POST['other_info']) ? trim($_POST['other_info']) : '';

    // 🔍 AUTOMATED DELTA COMPARISON MATRIX (Accounts Table Only)
    $stmt_old = $conn->prepare("SELECT * FROM accounts WHERE ID = ? LIMIT 1");
    $stmt_old->bind_param("i", $target_member_id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    
    $changes = []; 
    
    if ($res_old && $old = $res_old->fetch_assoc()) {
        if ($old['DisplayName'] !== $displayName)   $changes[] = "DisplayName changed to [".$displayName."]";
        if ($old['username'] !== $userLogon)       $changes[] = "Username changed to [".$userLogon."]";
        if ($old['email'] !== $emailAddr)           $changes[] = "Email changed to [".$emailAddr."]";
        if ($old['RankID'] != $rankID)             $changes[] = "RankID changed to [".$rankID."]";
        if ($old['TitleID'] != $titleID)           $changes[] = "TitleID changed to [".$titleID."]";
        if ($old['DivID'] != $divID)               $changes[] = "DivID changed to [".$divID."]";
        if ($old['species'] !== $speciesType)       $changes[] = "Species changed to [".$speciesType."]";
        if ($old['birth_place'] !== $birthPlace)   $changes[] = "Birth Place changed to [".$birthPlace."]";
        if ($old['induction_date'] !== $inductiondate) $changes[] = "Induction Date changed to [".$inductiondate."]";
        if ((int)$old['active'] !== $activeState)   $changes[] = "Active status changed to [".$activeState."]";
        if ((int)$old['dh'] !== $dh)                 $changes[] = "Clearance 'dh' changed to [".$dh."]";
        if ($old['bio'] !== $bioNotes)               $changes[] = "Dossier Bio notes modified.";
    }
    $stmt_old->close();

    // 🔍 AUTOMATED DELTA COMPARISON MATRIX (SJ_info Table Only)
    $stmt_sj_old = $conn->prepare("SELECT * FROM SJ_info WHERE username = ? LIMIT 1");
    $stmt_sj_old->bind_param("s", $userLogon);
    $stmt_sj_old->execute();
    $res_sj_old = $stmt_sj_old->get_result();

    if ($res_sj_old && $sj_old = $res_sj_old->fetch_assoc()) {
        if ($sj_old['languages'] !== $languages)   $changes[] = "Languages updated";
        if ($sj_old['religion'] !== $religion)     $changes[] = "Philosophy metadata updated";
        if ($sj_old['height_cm'] != $height_cm)    $changes[] = "Height array mutated";
        if ($sj_old['weight_kg'] != $weight_kg)    $changes[] = "Weight array mutated";
        if ($sj_old['hair'] !== $hair)             $changes[] = "Hair data mutated";
        if ($sj_old['eyes'] !== $eyes)             $changes[] = "Eye data mutated";
        if ($sj_old['blood_type'] !== $blood_type) $changes[] = "Metabolic composition updated";
        if ($sj_old['security_clearance'] !== $clearance) $changes[] = "Security clearance state updated";
        if ($sj_old['marital_status'] !== $marital) $changes[] = "Marital matrix updated";
    } else {
        $changes[] = "Initialized new Service Jacket profile row.";
    }
    $stmt_sj_old->close();

    // Compile tracked changes down to a clean format
    if (!empty($changes)) {
        $log_summary = implode(" // ", $changes);
    } else {
        $log_summary = "Dossier opened and re-saved with zero modified attributes.";
    }

    // 5. SECURE PARAMETERIZED DATABASE UPDATE (Table 1: accounts)
    $update_sql = "UPDATE accounts SET 
                    DisplayName = ?, 
                    username = ?, 
                    email = ?, 
                    RankID = ?, 
                    TitleID = ?, 
                    DivID = ?, 
                    species = ?, 
                    birth_place = ?, 
                    induction_date = ?,
                    active = ?,
                    dh = ?,
                    bio = ? 
                   WHERE ID = ?";

    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param(
        "sssssssssiisi", 
        $displayName, 
        $userLogon, 
        $emailAddr, 
        $rankID, 
        $titleID, 
        $divID, 
        $speciesType, 
        $birthPlace, 
        $inductiondate,
        $activeState,
        $dh,
        $bioNotes, 
        $target_member_id
    );

    $accounts_updated = $stmt_update->execute();
    $stmt_update->close();

    // 6. SECURE PARAMETERIZED UPSERT DATA WRITE (Table 2: SJ_info)
    $sj_upsert_sql = "INSERT INTO SJ_info (username, languages, religion, height_cm, weight_kg, hair, eyes, blood_type, medical_restrictions, other_info, other_id_marks_features, marital_status, spouse, children, mother, father, siblings, security_clearance) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE 
                        languages=VALUES(languages), religion=VALUES(religion), height_cm=VALUES(height_cm), weight_kg=VALUES(weight_kg), hair=VALUES(hair), eyes=VALUES(eyes), blood_type=VALUES(blood_type), medical_restrictions=VALUES(medical_restrictions), other_info=VALUES(other_info), other_id_marks_features=VALUES(other_id_marks_features), marital_status=VALUES(marital_status), spouse=VALUES(spouse), children=VALUES(children), mother=VALUES(mother), father=VALUES(father), siblings=VALUES(siblings), security_clearance=VALUES(security_clearance)";
    
    $stmt_sj_upsert = $conn->prepare($sj_upsert_sql);
    $stmt_sj_upsert->bind_param(
        "sssddsssssssssssss", 
        $userLogon, $languages, $religion, $height_cm, $weight_kg, $hair, $eyes, $blood_type, 
        $med_restrict, $other_info, $id_marks, $marital, $spouse, $children, $mother, $father, $siblings, $clearance
    );
    
    $sj_updated = $stmt_sj_upsert->execute();
    $stmt_sj_upsert->close();

    // 7. FINALIZE ACTION & LOG TERMINAL MATRIX
    if ($accounts_updated && $sj_updated) {
        
        // Execute the global logging helper function using our smart telemetry string
        record_security_log(
            $conn, 
            $login_session,      // The active administrator username logged in
            'UPDATE',            // The database operation flag type
            'PERSONNEL',         // The specific module name targeted
            $userLogon,          // The profile username string being modified
            $log_summary         // The dynamically compiled telemetry text detailing changes only
        );
        
        $db = $conn;
        include("sync_tags.php");
        
// Redirection route confirming synchronization success
        header("Location: dhsystem.php?member_id=" . $target_member_id . "&status=synchronized");
    } 
    else 
    {// Fallback error protocol redirection route
        header("Location: dhsystem.php?member_id=" . $target_member_id . "&status=failure");
    }
} 
else 
{// If someone tries to load this file directly via a browser, reject the request
    header("Location: dhsystem.php");}$conn->close();
?>
