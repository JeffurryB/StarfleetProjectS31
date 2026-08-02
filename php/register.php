<?php
// 1. INCLUDE EXISTING DEPENDENCIES AND CONFIGURATION CHECK
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once("config.php"); // 🗄️ Loads your global database configuration connection

// Ensure the local file script maps cleanly to your config's database variable
if (isset($db) && !isset($conn)) {
    $conn = $db;
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch columns from your divisions table using MySQLi
$divisions = [];
$sql_div = "SELECT did, dname FROM divisions ORDER BY dname ASC";
$result_div = $conn->query($sql_div);

if ($result_div && $result_div->num_rows > 0) {
    while ($row = $result_div->fetch_assoc()) {
        $divisions[] = $row;
    }
}
$message = ""; 

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
    $divisionId = isset($_POST['DivID']) ? trim($_POST['DivID']) : ''; 
    
    // 🔒 DATA INTEGRITY FIX: Safely extract and normalize the user-supplied avatar UUID string
    $user_uuid = isset($_POST['uuid']) ? strtolower(trim($_POST['uuid'])) : '';

    // Explicitly verify no variable is empty before proceeding
    if ($user !== '' && $email !== '' && $rawPassword !== '' && $divisionId !== '' && $user_uuid !== '') {
        
        // 🔒 LAYER 1: Structural UUID Check (Strictly validates 8-4-4-4-12 hex layout format arrays)
        // Matches e.g., f81d4fae-7dec-11d0-a765-00a0c91e6bf6
        $uuid_regex = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        if (!preg_match($uuid_regex, $user_uuid)) {
            $message = "<p class='lcars-text-error'>SYS_ERR: AVATAR ID STRING FAILS STRUCTURAL VALIDATION CHECKS.</p>";
        } else {
            
            // 🔒 LAYER 2: Duplicate Intercept Verification (Ensures UUID isn't already assigned)
            $check_uuid_sql = "SELECT ID FROM accounts WHERE UUID = ? LIMIT 1";
            $uuid_exists = false;
            
            if ($stmt_uuid_check = $conn->prepare($check_uuid_sql)) {
                $stmt_uuid_check->bind_param("s", $user_uuid);
                $stmt_uuid_check->execute();
                $res_uuid = $stmt_uuid_check->get_result();
                if ($res_uuid && $res_uuid->num_rows > 0) {
                    $uuid_exists = true;
                }
                $stmt_uuid_check->close();
            }

            if ($uuid_exists) {
                $message = "<p class='lcars-text-error'>SYS_ERR: SPECIFIED AVATAR DATA TARGET IS ALREADY ASSIGNED TO ANOTHER TERMINAL.</p>";
            } else {
                
                // 🔒 LAYER 3: Cryptographic Protection (Slow Bcrypt key stretching)
                $secure_bcrypt_hash = password_hash($rawPassword, PASSWORD_BCRYPT, ['cost' => 12]);

                // 4. INSERT into accounts table (Using secure Parameterized Statements with clean verified data)
                $sql = "INSERT INTO accounts (username, gender, email, password, UUID, DisplayName, DivID) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = $conn->prepare($sql)) {
                    // Passed parameters are structurally clean, safe, and parameterized
                    $stmt->bind_param("ssssssi", $user, $gender, $email, $secure_bcrypt_hash, $user_uuid, $user, $divisionId);
                    
                    if ($stmt->execute()) {
                        $stmt->close();
                        header("Location: welcome.php");
                        exit();
                    } else {
                        // Check for MySQL duplicate entry error code (1062)
                        if ($conn->errno == 1062) {
                            $message = "<p class='lcars-text-error'>SYS_ERR: USERNAME OR SUBSPACE EMAIL ACCOUNT ARCHIVE ENTRY COLLISION.</p>";
                        } else {
                            $message = "<p class='lcars-text-error'>SYS_ERR: " . htmlspecialchars($conn->error) . "</p>";
                        }
                    }
                    $stmt->close();
                } else {
                    $message = "<p class='lcars-text-error'>SYS_ERR: STATEMENT RECOVERY COMPILATION TIMED OUT.</p>";
                }
            }
        }
    } else {
        $message = "<p class='lcars-text-error'>SYS_ERR: ALL CORE REPOSITORY REGISTRY ATTRIBUTES MANDATORY.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>[<?php echo GROUP_ABBR; ?>] - Personnel Registration</title>
    <style>
        @import url('https://googleapis.com');
        
        body {
            background-color: #000000;
            color: #ff9900;
            font-family: 'Antonio', sans-serif;
            letter-spacing: 0.05em;
            margin: 20px;
            text-transform: uppercase;
        }
        .lcars-container {
            display: flex;
            max-width: 900px;
            margin: 0 auto;
        }
        .lcars-left-bar {
            width: 150px;
            border-right: 15px solid #cc99cc;
            border-radius: 40px 0 0 40px;
            padding-right: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: flex-end;
        }
        .lcars-pill {
            background-color: #ff9900;
            color: #000;
            width: 100px;
            height: 35px;
            border-radius: 20px;
            text-align: center;
            line-height: 35px;
            font-weight: bold;
            font-size: 14px;
        }
        .lcars-pill.blue { background-color: #5599cc; }
        .lcars-pill.purple { background-color: #cc99cc; }
        
        .lcars-main-content {
            flex-grow: 1;
            padding-left: 30px;
        }
        .lcars-header {
            font-size: 38px;
            color: #ffcc00;
            margin-bottom: 20px;
            border-bottom: 4px solid #ff9900;
            padding-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }
        .lcars-header-index {
            color: #5599cc;
            font-size: 18px;
            align-self: flex-end;
        }
        .lcars-form-group {
            margin-bottom: 20px;
        }
        .lcars-form-group label {
            display: block;
            font-size: 18px;
            color: #cc99cc;
            margin-bottom: 5px;
        }
        .lcars-input, .lcars-select {
            width: 100%;
            max-width: 400px;
            background-color: #000;
            border: 2px solid #ff9900;
            color: #fff;
            padding: 8px;
            font-family: 'Antonio', sans-serif;
            font-size: 18px;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .lcars-input:focus, .lcars-select:focus {
            outline: none;
            border-color: #5599cc;
            box-shadow: 0 0 8px rgba(85, 153, 204, 0.5);
        }
        .lcars-btn {
            background-color: #ffcc00;
            color: #000;
            border: none;
            padding: 10px 30px;
            font-family: 'Antonio', sans-serif;
            font-weight: bold;
            font-size: 20px;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            transition: background 0.2s;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .lcars-btn:hover {
            background-color: #ff9900;
        }
        .lcars-text-success { color: #00ff00; font-size: 18px; font-weight: bold; }
        .lcars-text-error { color: #ff3333; font-size: 18px; font-weight: bold; }
    </style>
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LCARS Personnel Registration Console</title>
    <style>
        :root {
            --lcars-purple: #9966cc; --lcars-orange: #ff9900;
            --lcars-pink: #cc6699; --lcars-blue: #33ccff;
            --lcars-bg: #000000; --lcars-green: #33cc33;
        }
        body {
            background-color: var(--lcars-bg); color: #ffffff;
            font-family: Arial, sans-serif; margin: 0; padding: 15px;
            text-transform: uppercase; letter-spacing: 1px;
            overflow-x: hidden;
        }
        .lcars-container { display: flex; min-height: calc(100vh - 60px); }
        .lcars-left-bar { width: 150px; display: flex; flex-direction: column; gap: 8px; margin-right: 20px; }
        .lcars-pill { background-color: var(--lcars-orange); color: #000000; padding: 10px; font-weight: bold; font-size: 13px; text-align: right; border-radius: 5px 0 0 5px; }
        .lcars-pill.blue { background-color: var(--lcars-blue); }
        .lcars-pill.purple { background-color: var(--lcars-purple); }
        .lcars-main-content { flex-grow: 1; display: flex; flex-direction: column; }
        .lcars-header { border-bottom: 4px solid var(--lcars-blue); padding-bottom: 10px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .lcars-header span { font-size: 22px; color: var(--lcars-blue); }
        .lcars-header-index { font-size: 12px !important; color: var(--lcars-purple) !important; font-weight: bold; }
        .lcars-form-group { display: flex; flex-direction: column; margin-bottom: 20px; max-width: 500px; }
        .lcars-form-group label { color: var(--lcars-purple); font-weight: bold; margin-bottom: 8px; font-size: 14px; }
        .lcars-input, .lcars-select { background-color: #000000; color: var(--lcars-orange); border: 2px solid var(--lcars-purple); padding: 10px; font-size: 16px; text-transform: uppercase; border-radius: 5px; width: 100%; box-sizing: border-box; }
        .lcars-input:focus, .lcars-select:focus { outline: none; border-color: var(--lcars-blue); }
        .lcars-input[type="email"] { text-transform: none; }
        .lcars-select { appearance: none; cursor: pointer; }
        .lcars-btn { background-color: var(--lcars-pink); color: #000000; border: none; padding: 15px 30px; font-size: 18px; font-weight: bold; cursor: pointer; border-radius: 10px; letter-spacing: 2px; width: 100%; max-width: 500px; margin-top: 10px; text-transform: uppercase; }
        .lcars-text-error { color: #ff5555; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="lcars-container">
    <div class="lcars-left-bar">
        <div class="lcars-pill">REG-01</div>
        <div class="lcars-pill blue">SYS-42</div>
        <div class="lcars-pill purple">SEC-88</div>
    </div>

    <div class="lcars-main-content">
        <div class="lcars-header">
            <span>Personnel Registration</span>
            <span class="lcars-header-index">INDEX // 402</span>
        </div>

        <?php if (!empty($message)) { echo $message; } ?>

        <form method="POST" action="">
            <div class="lcars-form-group">
                <label for="reg_username">Username / Sub-ID Token</label>
                <input type="text" id="reg_username" name="username" class="lcars-input" required autocomplete="off">
            </div>
            
            <div class="lcars-form-group">
                <label for="reg_gender">Gender</label>
                <select id="reg_gender" name="gender" class="lcars-select" required>
                    <option value="">-- SELECT CLASSIFICATION REFERENCE --</option>
                    <option value="1">MALE</option>
                    <option value="2">FEMALE</option>
                    <option value="3">NON-BINARY</option>
                </select>
            </div>
            
            <div class="lcars-form-group">
                <label for="reg_uuid">Avatar UUID</label>
                <input type="text" id="reg_uuid" name="uuid" class="lcars-input" required autocomplete="off">
            </div>
            
            <div class="lcars-form-group">
                <label for="reg_email">Subspace Email Destination</label>
                <input type="email" id="reg_email" name="email" class="lcars-input" required autocomplete="off">
            </div>

            <div class="lcars-form-group">
                <label for="reg_password">Security Access Cipher (Password)</label>
                <input type="password" id="reg_password" name="password" class="lcars-input" required>
            </div>

            <div class="lcars-form-group">
                <label for="reg_div">Assigned Starfleet Division</label>
                <select id="reg_div" name="DivID" class="lcars-select" required>
                    <option value="" disabled selected hidden>Select Fleet Division...</option>
                    <?php if (!empty($divisions)): ?>
                        <?php foreach ($divisions as $div): ?>
                            <option value="<?php echo htmlspecialchars($div['did']); ?>">
                                <?php echo htmlspecialchars($div['dname']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="lcars-btn">Initialize Account</button>
        </form>
    </div>
</div>

</body>
</html>
