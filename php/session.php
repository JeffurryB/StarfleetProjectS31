<?php
// 1. INCLUDE DATA CONTEXT USING UNIQUE ONCE PROTOCOLS
include_once('config.php');

// Ensure local file commands interface flawlessly with your configuration resource variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

// 2. INITIALIZE SECURE COOKIE FIREWALL COMPLIANCE
// Must be called BEFORE session_start to establish strict client-side data margins
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,          // Expires immediately when the user exits the browser
        'cookie_secure'   => true,       // Enforces strict transmission over verified HTTPS channels only
        'cookie_httponly' => true,       // Blocks client-side scripts/XSS from extracting active cookie text logs
        'cookie_samesite' => 'Strict'    // 🔒 Upgraded from Lax to permanently neutralize ambient cross-site leaks
    ]);
}

// 3. IMMEDIATE AUTHENTICATION CHECK
// Stops processing pipelines instantly if the matching tracking matrix row is uninitialized
if (!isset($_SESSION['login_user'])) {
    header("Location: login.php");
    exit(); 
}

$user_check = $_SESSION['login_user'];

// 4. DEPLOY USER-AGENT BINDING ARCHIVE FOOTPRINT
// Extract the current browser context telemetry to intercept stolen session keys
$current_agent_string = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UNKNOWN_ENV';
$computed_fingerprint = hash('sha256', $current_agent_string);

if (!isset($_SESSION['session_fingerprint'])) {
    // First-touch handshake initialization route
    $_SESSION['session_fingerprint'] = $computed_fingerprint;
    $_SESSION['session_created_time'] = time();
} else {
    // 🔒 THE SESSION HIJACK FIREWALL: Instantly drop traffic if environmental footprints mismatch
    if (!hash_equals($_SESSION['session_fingerprint'], $computed_fingerprint)) {
        
        // Destructive Core Clear Protocol: Vaporize session metadata immediately from server memory
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $cookie_params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $cookie_params["path"], $cookie_params["domain"],
                $cookie_params["secure"], $cookie_params["httponly"]
            );
        }
        session_destroy();
        
        header("Location: login.php?error=session_tampering_detected");
        exit();
    }
}

// 5. PERIODIC SECURITY ROTATION AND IDENTIFIER REGENERATION
// Automatically cycle the active session token every 5 minutes to disable old captured packet traces
if (time() - $_SESSION['session_created_time'] > 300) {
    session_regenerate_id(true); // Deletes the historic index record row completely from the server
    $_SESSION['session_created_time'] = time();
}

// 6. EXECUTE PARAMETERIZED TELEMETRY VERIFICATION LOOKUP
$stmt = mysqli_prepare($conn, "SELECT username FROM accounts WHERE username = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $user_check);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $login_session = $row['username'];
    } else {
        // If the username layer exists inside active memory cache but is missing from DB, purge state
        mysqli_stmt_close($stmt);
        session_destroy();
        header("Location: login.php");
        exit();
    }
    mysqli_stmt_close($stmt);
} else {
    die("CRITICAL MATRIX FAULT: DATA INTERFACE ACCESS TIMED OUT.");
}

// 7. CSRF SECURITY MATRIX SEED VALUE
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
