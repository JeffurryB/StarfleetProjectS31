<?php
// 1. SYSTEM CONTEXT INITIALIZATION
include("config.php");   
include("session.php");  
include("functions.php");

ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_set_charset($db, "utf8");

// 2. STAGE 1 SECURITY CLEARANCE CHECK: Grab the unique row 'ID' of the active logged-in user
$auth_user = mysqli_real_escape_string($db, $login_session);
$sql_auth = "SELECT ID, dh FROM accounts WHERE username = '$auth_user' LIMIT 1";
$res_auth = mysqli_query($db, $sql_auth);

if ($res_auth && mysqli_num_rows($res_auth) == 1) {
    $user_data = mysqli_fetch_assoc($res_auth);
    $current_row_id = (int)$user_data['ID'];
    
    // 🔒 STAGE 2 MASTER ROOT PROTECTION LOCK OUT: Only allow database account IDs 1 and 2
    if ($current_row_id !== 1 && $current_row_id !== 2) {
        header("Location: security_logs.php?status=access_denied_id_unauthorized");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// 3. EXECUTE EXPLICIT DATA OVERWRITE PURGE
$truncate_sql = "TRUNCATE TABLE `security_logs`";

if (mysqli_query($db, $truncate_sql)) {
    // Write an unalterable initial marker log row indicating who triggered the system wipe
    record_security_log(
        $db, 
        $login_session, 
        'PURGE', 
        'SECURITY_LOGS', 
        'MASTER_RESET', 
        'Total history log manifest wiped completely by authorized system administrator.'
    );
    
    header("Location: security_logs.php?status=purge_successful");
} else {
    header("Location: security_logs.php?status=purge_failed_error");
}

mysqli_close($db);
?>
