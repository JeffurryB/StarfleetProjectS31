<?php
// purge_chat.php
require_once 'session.php'; 

header('Content-Type: application/json');

// --- 1. RE-VERIFY STRICT LEVEL 9 ADMINISTRATIVE PRIVILEGES ---
// Check against $login_session which your session.php file creates
$auth_user = isset($login_session) ? $login_session : '';
$is_admin = false;

if (!empty($auth_user)) {
    $sql_auth = "SELECT ID FROM accounts WHERE username = ? LIMIT 1";
    if ($stmt_auth = $conn->prepare($sql_auth)) {
        $stmt_auth->bind_param("s", $auth_user);
        $stmt_auth->execute();
        $res_auth = $stmt_auth->get_result();
        
        if ($res_auth && $res_auth->num_rows == 1) {
            $auth_data = $res_auth->fetch_assoc();
            if ((int)$auth_data['ID'] === 1) {
                $is_admin = true;
            }
        }
        $stmt_auth->close();
    }
}

// --- 2. HARD LOCKDOWN SECURITY CHECK ---
if (!$is_admin) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'INSUFFICIENT CLEARANCE. Active Identity: ' . htmlspecialchars($auth_user)
    ]);
    exit;
}

// --- 3. EXECUTE CHAT ROOM WIPE ---
$wipe_query = "TRUNCATE TABLE lcars_chat";

if (mysqli_query($conn, $wipe_query)) {
    // Drop a system log entry into the chat room stating who ran the purge
    $sys_msg = "Database tables cleared manually by Chief Administrator (" . $auth_user . ").";
    $log_stmt = mysqli_prepare($conn, "INSERT INTO lcars_chat (username, message) VALUES ('SYSTEM', ?)");
    
    if ($log_stmt) {
        mysqli_stmt_bind_param($log_stmt, "s", $sys_msg);
        mysqli_stmt_execute($log_stmt);
        mysqli_stmt_close($log_stmt);
    }

    echo json_encode(['status' => 'success']);
} else {
    // Send exact SQL compilation errors back to the frontend loop
    echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . mysqli_error($conn)]);
}
?>
