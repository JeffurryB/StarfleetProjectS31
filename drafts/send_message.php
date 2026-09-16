<?php
// send_message.php
require_once 'session.php'; // Automatically handles security validation layers

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rely exclusively on your verified session variable array
    $user = strip_tags(trim($login_session));
    $msg  = strip_tags(trim($_POST['message'] ?? ''));

    if (!empty($user) && !empty($msg)) {
        // Targets your exact aliased '$conn' database hook
        $stmt = mysqli_prepare($conn, "INSERT INTO lcars_chat (username, message) VALUES (?, ?)");
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $user, $msg);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($success) {
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Transmission corrupted.']);
?>
