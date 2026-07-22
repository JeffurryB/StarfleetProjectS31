<?php
include('config.php');

// 1. Secure session cookie attributes (Must be called BEFORE session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,          // Expires when browser closes
        'cookie_secure' => true,         // REQUIRES HTTPS - only transmits over secure connections
        'cookie_httponly' => true,       // Prevents JavaScript/XSS cookie theft
        'cookie_samesite' => 'Lax',   // Blocks third-party sites from using this cookie
    ]);
}

// 2. Immediate Authentication Check (Stops execution if not logged in)
if (!isset($_SESSION['login_user'])) {
    header("Location: login.php");
    exit(); // Crucial to prevent script from running further
}

$user_check = $_SESSION['login_user'];

// 3. Fix SQL Injection using a Prepared Statement
$stmt = mysqli_prepare($db, "SELECT username FROM accounts WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $user_check);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    $login_session = $row['username'];
} else {
    // If user exists in session but not in database, destroy session and boot them
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
