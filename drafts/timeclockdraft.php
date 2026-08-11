<?php
// Include your existing database connection configuration
include 'config.php';

// Must match the SECRET_KEY string in your LSL script
$secret_key = "my_secure_handshake_key"; 

// Check if request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}
// Check security token handshake
if (!isset($_POST['secret']) || $_POST['secret'] !== $secret_key) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

// Get the avatar UUID and name sent from the Second Life object
$sl_uuid   = filter_input(INPUT_POST, 'user_id', FILTER_DEFAULT);
$user_name = filter_input(INPUT_POST, 'user_name', FILTER_DEFAULT);

if (!$sl_uuid) {
    echo "Error: Missing user identity data.";
    exit;
}
// 1. Look up the internal account ID using the SL UUID
// NOTE: Change `uuid_column_name` to whatever your accounts table calls the SL UUID field
$account_query = "SELECT id FROM accounts WHERE UUID = ? LIMIT 1";
$acc_stmt = $conn->prepare($account_query);
$acc_stmt->bind_param("s", $sl_uuid);
$acc_stmt->execute();
$acc_result = $acc_stmt->get_result();

if ($acc_result->num_rows === 0) {
    echo "Error: No website account found for this avatar.";
    $acc_stmt->close();
    $conn->close();
    exit;
}

$account_row = $acc_result->fetch_assoc();
$internal_user_id = $account_row['id']; // This is the ID for your Time Clock table
$acc_stmt->close();

// 2. Look for an active row in Time Clock using that internal ID
$query = "SELECT id FROM `Time Clock` WHERE user_id = ? AND time_out IS NULL LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $internal_user_id); // "i" assuming your internal account ID is an integer
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    // Already clocked in -> Let's clock them out
    $row = $result->fetch_assoc();
    $session_id = $row['id'];
    
    $update_query = "UPDATE `Time Clock` SET time_out = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $session_id);
    $update_stmt->execute();
    
    echo "Goodbye " . $user_name . "! You clocked out at " . date('Y-m-d H:i:s');
} else {
    // Not clocked in -> Let's clock them in using the internal ID
    $insert_query = "INSERT INTO `Time Clock` (user_id, time_in) VALUES (?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("i", $internal_user_id);
    $insert_stmt->execute();
    
    echo "Welcome " . $user_name . "! You clocked in at " . date('Y-m-d H:i:s');
}

// Close database connections cleanly
$stmt->close();
$conn->close();
?>
