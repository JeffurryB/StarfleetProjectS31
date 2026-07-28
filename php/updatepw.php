<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. INCLUDE GLOBAL CONFIGURATION MANAGEMENT
include("config.php"); 

// Ensure the local file script maps cleanly to your config's database variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $cCode = isset($_POST['cCode']) ? trim($_POST['cCode']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';

    if (!empty($user) && !empty($cCode) && !empty($newPassword)) {
        
        // 3. Verify username and cCode, and retrieve the UUID via MySQLi
        $account = null;
        $checkSql = "SELECT UUID FROM accounts WHERE username = ? AND cCode = ? LIMIT 1";
        
        if ($checkStmt = $conn->prepare($checkSql)) {
            $checkStmt->bind_param("ss", $user, $cCode);
            $checkStmt->execute();
            $res = $checkStmt->get_result();
            if ($res && $res->num_rows > 0) {
                $account = $res->fetch_assoc();
            }
            $checkStmt->close();
        }

        if ($account) {
            // 4. Generate the 128-character SHA-512 Hash using the fetched UUID
            $uuid = $account['UUID'];
            $pepper = "*&&^%$DFHDSKJ*%*%&#WE&#@"; // Must match your registration script
            $hashInput = $newPassword . $uuid . $pepper;
            $sha512Hash = hash('sha512', $hashInput);

            // 5. Update password and clear the cCode so it cannot be reused
            $updateSql = "UPDATE accounts SET password = ?, cCode = NULL WHERE username = ?";
            if ($updateStmt = $conn->prepare($updateSql)) {
                $updateStmt->bind_param("ss", $sha512Hash, $user);
                
                if ($updateStmt->execute()) {
                    echo "<p style='color: green;'>Password successfully updated for " . htmlspecialchars($user) . "!</p>";
                } else {
                    echo "<p style='color: red;'>Error: System write error during password generation sequence.</p>";
                }
                $updateStmt->close();
            } else {
                echo "<p style='color: red;'>Error: Encryption subsystem error.</p>";
            }
        } else {
            echo "<p style='color: red;'>Error: Invalid username or authorization code (cCode).</p>";
        }
    } else {
        echo "<p style='color: red;'>Please fill out all fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Security Terminal // Password Override Matrix</title>
    <style>
        :root {
            --lcars-purple: #9966cc;
            --lcars-orange: #ff9900;
            --lcars-blue: #33ccff;
            --lcars-bg: #000000;
            --lcars-red: #cc3333;
            --lcars-green: #33cc33;
        }

        body {
            background-color: var(--lcars-bg);
            color: #ffffff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* LCARS Header Layout Frame */
        .lcars-header { display: flex; align-items: flex-end; margin-bottom: 20px; }
        .lcars-bar-top { background-color: var(--lcars-red); height: 40px; flex-grow: 1; border-bottom-left-radius: 20px; margin-right: 15px; position: relative; }
        .lcars-bar-top::before { content: "SEC-OVERRIDE-404"; position: absolute; left: 25px; bottom: 3px; color: #000000; font-weight: bold; font-size: 14px; }
        .lcars-title { color: var(--lcars-red); font-size: 26px; font-weight: 300; margin: 0; white-space: nowrap; }

        /* Structural Container Display Layout */
        .lcars-container { display: flex; min-height: 75vh; }
        .lcars-left-bracket { width: 140px; display: flex; flex-direction: column; margin-right: 20px; }
        .lcars-elbow { background-color: var(--lcars-red); height: 60px; border-top-left-radius: 20px; border-bottom-left-radius: 20px; margin-bottom: 15px; position: relative; }
        .lcars-elbow::after { content: ""; position: absolute; background-color: var(--lcars-bg); width: 100px; height: 35px; bottom: 0; right: 0; border-top-left-radius: 15px; }
        
        .lcars-side-block { background-color: var(--lcars-purple); height: 50px; margin-bottom: 5px; border-radius: 5px 0 0 5px; }
        .lcars-btn-back { background-color: var(--lcars-orange); color: #000000; padding: 10px; text-decoration: none; font-weight: bold; font-size: 13px; text-align: right; margin-bottom: 5px; border-radius: 5px 0 0 5px; display: block; }

        /* Main Workspace Interface Input Panel */
        .lcars-main-panel { flex-grow: 1; display: flex; flex-direction: column; }
        .security-form-box {
            background: #111116;
            border-left: 6px solid var(--lcars-purple);
            border-radius: 0 10px 10px 0;
            padding: 25px;
            max-width: 550px;
        }

        .input-group { margin-bottom: 20px; }
        .lcars-label { color: var(--lcars-orange); display: block; margin-bottom: 8px; font-weight: bold; font-size: 14px; }
        
        .lcars-input {
            background-color: #000000;
            color: var(--lcars-blue);
            border: 2px solid var(--lcars-blue);
            padding: 12px;
            font-size: 16px;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
            letter-spacing: 1px;
        }
        /* Prevents your password and text tokens from automatically stripping case formatting visibility */
        .lcars-input[type="password"], .lcars-input.raw-token { text-transform: none; }
        .lcars-input:focus { outline: none; border-color: #ffffff; box-shadow: 0 0 10px rgba(51, 204, 255, 0.3); }

        .engage-btn {
            background-color: var(--lcars-green);
            color: #000000;
            border: none;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            letter-spacing: 2px;
            margin-top: 10px;
        }
        .engage-btn:hover { background-color: #29a329; }
    </style>
</head>
<body>

    <header class="lcars-header">
        <div class="lcars-bar-top"></div>
        <h2 class="lcars-title">PASSWORD OVERRIDE ARRAY</h2>
    </header>

    <div class="lcars-container">
        <!-- LCARS Navigation Sidebar Layout Frame -->
        <nav class="lcars-left-bracket">
            <div class="lcars-elbow"></div>
            <div class="lcars-side-block" style="background-color: var(--lcars-blue);"></div>
            <div class="lcars-side-block"></div>
            <a href="login.php" class="lcars-btn-back">INDEX TERM</a>
        </nav>

        <!-- Main Form Terminal Interface Panel -->
        <main class="lcars-main-panel">
            <div class="security-form-box">
                <p style="color: var(--lcars-red); margin-top: 0; font-weight: bold; font-size: 13px;">
                    ALERT // SECURE IDENTITY VERIFICATION REQ SYSTEM LINK 02
                </p>
                <p style="font-size: 12px; color: #aaa; text-transform: none; margin-bottom: 25px;">
                    Enter your assigned alphanumeric authorization command code signature sequence to overwrite encrypted account password vectors.
                </p>

                <!-- 6. Password Reset HTML Form -->
                <form method="POST" action="">
                    <div class="input-group">
                        <label class="lcars-label">Username Vector:</label>
                        <input type="text" name="username" class="lcars-input raw-token" required autocomplete="off">
                    </div>
                    
                    <div class="input-group">
                        <label class="lcars-label">Authorization Code (cCode):</label>
                        <input type="text" name="cCode" class="lcars-input raw-token" placeholder="BravoAlpha558C..." required autocomplete="off">
                    </div>
                    
                    <div class="input-group">
                        <label class="lcars-label">New Password Payload:</label>
                        <input type="password" name="new_password" class="lcars-input" required>
                    </div>
                    
                    <button type="submit" class="engage-btn">UPDATE PASSWORD MATRIX</button>
                </form>
            </div>
        </main>
    </div>

</body>
</html>

