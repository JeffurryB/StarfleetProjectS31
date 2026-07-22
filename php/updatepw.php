<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Database Connection
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

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username']);
    $cCode = trim($_POST['cCode']);
    $newPassword = $_POST['new_password'];

    if (!empty($user) && !empty($cCode) && !empty($newPassword)) {
        
        // 3. Verify username and cCode, and retrieve the UUID
        $checkStmt = $pdo->prepare("SELECT UUID FROM accounts WHERE username = :username AND cCode = :cCode LIMIT 1");
        $checkStmt->execute([
            'username' => $user,
            'cCode'    => $cCode
        ]);
        $account = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            // 4. Generate the 128-character SHA-512 Hash using the fetched UUID
            $uuid = $account['UUID'];
            $pepper = "#%$^#$^#%$#GGSGDSKJJOS%4W"; // Must match your registration script
            $hashInput = $newPassword . $uuid . $pepper;
            $sha512Hash = hash('sha512', $hashInput);

            // 5. Update password and clear the cCode so it cannot be reused
            $updateStmt = $pdo->prepare("UPDATE accounts SET password = :password, cCode = NULL WHERE username = :username");
            $updateStmt->execute([
                'password' => $sha512Hash,
                'username' => $user
            ]);

            echo "<p style='color: green;'>Password successfully updated for " . htmlspecialchars($user) . "!</p>";
        } else {
            echo "<p style='color: red;'>Error: Invalid username or authorization code (cCode).</p>";
        }
    } else {
        echo "<p style='color: red;'>Please fill out all fields.</p>";
    }
}
?>

<!-- 6. Password Reset HTML Form -->
<form method="POST">
    <p>
        <label>Username:</label><br>
        <input type="text" name="username" required>
    </p>
    <p>
        <label>Authorization Code (cCode):</label><br>
        <input type="text" name="cCode" required>
    </p>
    <p>
        <label>New Password:</label><br>
        <input type="password" name="new_password" required>
    </p>
    <button type="submit">Update Password</button>
</form>
